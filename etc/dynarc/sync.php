<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-10-2016
 #PACKAGE: gnujiko-sync
 #DESCRIPTION: Tool for synchronize data of archives managed by Dynarc between multiple devices (computers) on the network or by pendrive.
 #VERSION: 2.4beta
 #CHANGELOG: 24-10-2016 : MySQLi integration.
			 11-04-2013 : Sistemato i permessi ai files.
 #DEPENDS: rsh
 #TODO: 
 
*/

global $_BASE_PATH, $_TMPUSERS, $_TMPGROUPS;
include_once($_BASE_PATH."include/userfunc.php");
include_once($_BASE_PATH."etc/dynarc/archives.php");
$_TMPUSERS = array();
$_TMPGROUPS = array();
$_TMPCATS = array();

//-------------------------------------------------------------------------------------------------------------------//
function dynarc_sync($args, $sessid, $shellid=0, $extraVar=null)
{
 global $_BASE_PATH, $_USERS_HOMES, $_USER_PATH;
 $out = "";
 $outArr = array();

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] == "root")
  $userpath = "";
 else if($sessInfo['uid'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $userpath = $_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
  $userpath="tmp/";

 $fileName = "gnujiko-sync";

 $archivePrefixes = array();
 $archiveIds = array();
 $archives = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'register-device' : return dynarc_sync_registerDevice($args, $sessid, $shellid); break;
   case 'unregister-device' : return dynarc_sync_unregisterDevice($args, $sessid, $shellid); break;
   case 'device-list' : return dynarc_sync_deviceList($args, $sessid, $shellid); break;


   case '-device' : {$deviceName=$args[$c+1]; $c++;} break;
   case '-devid' : {$deviceId=$args[$c+1]; $c++;} break;
   case '-devurl' : {$deviceURL=$args[$c+1]; $c++;} break;
   case '-login' : {$login=$args[$c+1]; $c++;} break;
   case '-password' : {$password=$args[$c+1]; $c++;} break;

   /* EXPORT */
   case '-ap' : {$archivePrefixes[]=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveIds[]=$args[$c+1]; $c++;} break;
   case '-fn' : case '-filename' : {$fileName=$args[$c+1]; $c++;} break;

   /* OTHER INCLUDES */
   case '--company-profile' : {
	 $includeCompanyProfile=true;
	 $includeVatRates=true;
	 $includeCashResources=true;
	 $includePaymentModes=true;
	 $includePriceLists=true;
	 $includeStores=true;
	 $includeVatRegister=true;
	 $includePettyCashbook=true;
	 $includePrinters=true;
	} break;
   case '--cash-resources' : $includeCashResources=true; break;
   case '--vat-rates' : $includeVatRates=true; break;
   case '--payment-modes' : $includePaymentModes=true; break;
   case '--price-lists' : $includePriceLists=true; break;
   case '--stores' : $includeStores=true; break;
   case '--vat-register' : $includeVatRegister=true; break;
   case '--petty-cashbook' : $includePettyCashbook=true; break;

   /* IMPORT */
   case '-i' : {$inputFile=$args[$c+1]; $c++;} break;
  }

 /* Get device info */
 $deviceInfo = array();
 $db = new AlpaDatabase();
 if($deviceId)
 {
  $db->RunQuery("SELECT * FROM dynarcsync_devices WHERE id='".$deviceId."'");
  if(!$db->Read())
   return array('message'=>"Device #".$deviceId." does not exists.",'error'=>"DEVICE_DOES_NOT_EXISTS");
  $deviceInfo = array('id'=>$db->record['id'],'name'=>$db->record['name'],'type'=>$db->record['device_type'],'url'=>$db->record['url'],
	'login'=>$db->record['login'],'password'=>$db->record['password'],'last_sync'=>$db->record['last_sync_time']);
 }
 else if($deviceName)
 {
  $db->RunQuery("SELECT * FROM dynarcsync_devices WHERE name='".$db->Purify($deviceName)."' AND login='".($login ? $login : $sessInfo['uname'])."'");
  if(!$db->Read())
   return array('message'=>"Device ".$deviceName." does not exists.",'error'=>"DEVICE_DOES_NOT_EXISTS");
  $deviceInfo = array('id'=>$db->record['id'],'name'=>$db->record['name'],'type'=>$db->record['device_type'],'url'=>$db->record['url'],
	'login'=>$db->record['login'],'password'=>$db->record['password'],'last_sync'=>$db->record['last_sync_time']);
 }
 else if($deviceURL)
 {
  $deviceInfo['url']=$deviceURL;
  $deviceInfo['login']=$login ? $login : $sessInfo['uname'];
  $deviceInfo['password']=$password;
 }
 //else
 // return array('message'=>"You must specify device. (with: -device OR -devid OR -devurl)",'error'=>"INVALID_DEVICE");
 $db->Close();

 if($inputFile)
  return dynarc_sync_manualSyncImport($inputFile, $sessid, $shellid);

 //-------------------------------------------------------------------------//
 $out.= "Checking archives...";
 // Archive(s) check ...
 for($c=0; $c < count($archivePrefixes); $c++)
 {
  $ret = GShell("dynarc archive-info -prefix `".$archivePrefixes[$c]."`",$sessid,$shellid);
  if($ret['error']) return array('message'=>$out." failed!".$ret['message'],$ret['error']);
  if(!$ret['outarr']['sync_enabled'])
   return array('message'=>"Archive ".$ret['outarr']['name']." is not enabled for synchronization.","error"=>"ARCHIVE_NOT_SYNCHRONIZABLE");
  /* get extensions */
  $ret['outarr']['extensions'] = array();
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$ret['outarr']['id']."' ORDER BY id ASC");
  while($db->Read())
   $ret['outarr']['extensions'][] = $db->record['extension_name'];
  $db->Close();
  $archives[] = $ret['outarr'];
 }
 for($c=0; $c < count($archiveIds); $c++)
 {
  $ret = GShell("dynarc archive-info -id `".$archiveIds[$c]."`",$sessid,$shellid);
  if($ret['error']) return array('message'=>$out." failed!".$ret['message'],$ret['error']);
  if(!$ret['outarr']['sync_enabled'])
   return array('message'=>"Archive ".$ret['outarr']['name']." is not enabled for synchronization.","error"=>"ARCHIVE_NOT_SYNCHRONIZABLE");
  /* get extensions */
  $ret['outarr']['extensions'] = array();
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$ret['outarr']['id']."' ORDER BY id ASC");
  while($db->Read())
   $ret['outarr']['extensions'][] = $db->record['extension_name'];
  $db->Close();
  $archives[] = $ret['outarr'];
 }
 /*if(!count($archives))
  return array('message'=>"You must specify at least 1 archive.","error"=>"INVALID_ARCHIVE");
 $out.= "done!\n";*/

 // Create temporary folder into user home
 $tempFolder = ($sessInfo['uname'] == "root") ? "tmp/".$fileName : $fileName;
 $ret = GShell("mkdir `".$tempFolder."`",$sessid,$shellid);
 if($ret['error'])
  return array('message'=>"Sync failed! Unable to create temporary folder. ".$ret['message'],'error'=>$ret['error']);


 for($z=0; $z < count($archives); $z++)
 {
  $archiveInfo = $archives[$z];

  /* Get last categories and items created or modified after the last sync time */
  $m = new GMOD();
  $uQry = $m->userQuery($sessid,null,"dynarc_".$archiveInfo['prefix']."_synclog");
  $selectQry = "SELECT * FROM dynarc_".$archiveInfo['prefix']."_synclog WHERE ($uQry)";
  if($deviceInfo['last_sync'] && ($deviceInfo['last_sync'] != "0000-00-00 00:00:00"))
   $selectQry.= " AND logtime>'".$deviceInfo['last_sync']."'";

  $categories = array();
  $items = array();
  $xmlSummary = "<xml generator='Gnujiko' dist='10.1' loader='dynarc' type='sync-archive-summary' ap='".$archiveInfo['prefix']."'>";
  $xmlCreated = ""; $xmlUpdated = ""; $xmlMoved = ""; $xmlTrashed = ""; $xmlRemoved = ""; $xmlRestored = "";

  $db = new AlpaDatabase();
  $db2 = new AlpaDatabase();
  $db->RunQuery($selectQry);
  $count = 0;
  while($db->Read())
  {
   if($db->record['id'])
   {
    $db2->RunQuery("SELECT name FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$db->record['id']."'");
	$db2->Read();
    $items[] = array('id'=>$db->record['id'],'syncid'=>$db->record['syncid'],'status'=>$db->record['status'],'name'=>$db2->record['name']);
    switch($db->record['status'])
	{
	 case 'CREATED' : $xmlCreated.= "<item syncid=\"".$db->record['syncid']."\" name=\"".xml_purify($db2->record['name'])."\" logtime=\"".$db->record['logtime']."\"/>\n"; break;
	 case 'UPDATED' : $xmlUpdated.= "<item syncid=\"".$db->record['syncid']."\" name=\"".xml_purify($db2->record['name'])."\" logtime=\"".$db->record['logtime']."\"/>\n"; break;
	 case 'MOVED' : $xmlMoved.= "<item syncid=\"".$db->record['syncid']."\" name=\"".xml_purify($db2->record['name'])."\" logtime=\"".$db->record['logtime']."\"/>\n"; break;
	 case 'TRASHED' : $xmlTrashed.= "<item syncid=\"".$db->record['syncid']."\" name=\"".xml_purify($db2->record['name'])."\" logtime=\"".$db->record['logtime']."\"/>\n"; break;
	 case 'REMOVED' : $xmlRemoved.= "<item syncid=\"".$db->record['syncid']."\" name=\"".xml_purify($db2->record['name'])."\" logtime=\"".$db->record['logtime']."\"/>\n"; break;
	 case 'RESTORED' : $xmlRestored.= "<item syncid=\"".$db->record['syncid']."\" name=\"".xml_purify($db2->record['name'])."\" logtime=\"".$db->record['logtime']."\"/>\n"; break;
	}
   }
   else
   {
    $db2->RunQuery("SELECT name FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$db->record['cat_id']."'");
	$db2->Read();
    $categories[] = array('id'=>$db->record['cat_id'],'syncid'=>$db->record['syncid'],'status'=>$db->record['status'],'name'=>$db2->record['name']);
    switch($db->record['status'])
	{
	 case 'CREATED' : $xmlCreated.= "<category syncid=\"".$db->record['syncid']."\" name=\"".xml_purify($db2->record['name'])."\" logtime=\"".$db->record['logtime']."\"/>\n"; break;
	 case 'UPDATED' : $xmlUpdated.= "<category syncid=\"".$db->record['syncid']."\" name=\"".xml_purify($db2->record['name'])."\" logtime=\"".$db->record['logtime']."\"/>\n"; break;
	 case 'MOVED' : $xmlMoved.= "<category syncid=\"".$db->record['syncid']."\" name=\"".xml_purify($db2->record['name'])."\" logtime=\"".$db->record['logtime']."\"/>\n"; break;
	 case 'TRASHED' : $xmlTrashed.= "<category syncid=\"".$db->record['syncid']."\" name=\"".xml_purify($db2->record['name'])."\" logtime=\"".$db->record['logtime']."\"/>\n"; break;
	 case 'REMOVED' : $xmlRemoved.= "<category syncid=\"".$db->record['syncid']."\" name=\"".xml_purify($db2->record['name'])."\" logtime=\"".$db->record['logtime']."\"/>\n"; break;
	 case 'RESTORED' : $xmlRestored.= "<category syncid=\"".$db->record['syncid']."\" name=\"".xml_purify($db2->record['name'])."\" logtime=\"".$db->record['logtime']."\"/>\n"; break;
	}
   }
   $count++;
  }
  $db->Close();
  $db2->Close();

  if(!$count)
  {
   $out.= "There have been no changes since the last synchronization into archive ".$archiveInfo['name'];
   continue;
  }

  $ret = GShell("mkdir `".$tempFolder."/".$archiveInfo['prefix']."`",$sessid,$shellid); 
  if($ret['error']) 
   return $ret;

  // Exporting
  $interface = array("name"=>"progressbar","steps"=>$count);
  gshPreOutput($shellid,"Export data for synchronization from archive '".$archiveInfo['name']."'", "ESTIMATION", "", "PASSTHRU", $interface);
  $attachments = array();

  /* EXPORT CATEGORIES */
  for($c=0; $c < count($categories); $c++)
  {
   switch($categories[$c]['status'])
   {
    case 'CREATED' : case 'UPDATED' : case 'RESTORED' : case 'MOVED' : {
	 $xml = "<xml generator='Gnujiko' dist='10.1' loader='dynarc' type='sync-category' ap='".$archiveInfo['prefix']."'>";
	 $db = new AlpaDatabase();
	 $fields = $db->FieldsInfo("dynarc_".$archiveInfo['prefix']."_categories");
	 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$categories[$c]['id']."'");
	 $db->Read();
	 $catInfo = $db->record;
	 gshPreOutput($shellid, "Exporting category: <i>".$db->record['name']."</i>","PROGRESS", "");
	 $xml.= "<category";
	 while(list($k,$v) = each($fields))
	 {
	  switch($k)
	  {
	   case 'id' : case 'lnk_id' : case 'lnkarc_id' : case 'hierarchy' : continue; break;
	   case 'uid' : $xml.= " uid=\"".dynarcsync_getUserName($db->record['uid'])."\""; break;
	   case 'gid' : $xml.= " gid=\"".dynarcsync_getGroupName($db->record['gid'])."\""; break;
	   case 'parent_id' : $xml.= " parent_id=\"".dynarcsync_getCatSyncid($archiveInfo['prefix'],$db->record['parent_id'])."\""; break;
	   default : $xml.= " ".$k."=\"".sanitize($db->record[$k])."\""; break;
	  }
	 }
	 $db->Close();
	 if(count($archiveInfo['extensions']))
	 {
	  $xml.= ">";
	  for($i=0; $i < count($archiveInfo['extensions']); $i++)
	  {
	   $ext = $archiveInfo['extensions'][$i];
	   if(file_exists($_BASE_PATH."etc/dynarc/extensions/".$ext."/index.php"))
	   {
		include_once($_BASE_PATH."etc/dynarc/extensions/".$ext."/index.php");
		if(is_callable("dynarcextension_".$ext."_syncexport",false))
		{
		 $ret = call_user_func("dynarcextension_".$ext."_syncexport",$sessid,$shellid,$archiveInfo,$catInfo,true);
		 if($ret['error'])
		  return $ret;
		 $xml.= $ret['xml'];
		 if($ret['attachments'] && count($ret['attachments']))
		  $attachments = array_merge($attachments,$ret['attachments']);
		}
	   }
	  }
	  $xml.= "</category>\n";
	 }
	 else
	  $xml.= "/>";

	 $xml.= "</xml>";
	 $ret = dynarc_sync_saveToFile($xml, $tempFolder."/".$archiveInfo['prefix']."/".$categories[$c]['syncid'].".xml", $sessid, $shellid);
	 if($ret['error'])
	  return array('message'=>"Sync failed!".$ret['message'],"error"=>$ret['error']);
	} break;
   }
  }
  /* EOF - EXPORT CATEGORIES */

  /* EXPORT ITEMS */
  for($c=0; $c < count($items); $c++)
  {
   switch($items[$c]['status'])
   {
    case 'CREATED' : case 'UPDATED' : case 'RESTORED' : case 'MOVED' : {
	 $xml = "<xml generator='Gnujiko' dist='10.1' loader='dynarc' type='sync-category' ap='".$archiveInfo['prefix']."'>";
	 $db = new AlpaDatabase();
	 $fields = $db->FieldsInfo("dynarc_".$archiveInfo['prefix']."_items");
	 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$items[$c]['id']."'");
	 $db->Read();
	 $itemInfo = $db->record;
	 gshPreOutput($shellid, "Exporting item: <i>".$db->record['name']."</i>","PROGRESS", "");
	 $xml.= "<item";
	 while(list($k,$v) = each($fields))
	 {
	  if($k == "id") continue;
	  switch($k)
	  {
	   case 'id' : case 'lnk_id' : case 'lnkarc_id' : case 'hierarchy' : continue; break;
	   case 'uid' : $xml.= " uid=\"".dynarcsync_getUserName($db->record['uid'])."\""; break;
	   case 'gid' : $xml.= " gid=\"".dynarcsync_getGroupName($db->record['gid'])."\""; break;
	   case 'cat_id' : $xml.= " cat_id=\"".dynarcsync_getCatSyncid($archiveInfo['prefix'],$db->record['cat_id'])."\""; break;
	   default : $xml.= " ".$k."=\"".sanitize($db->record[$k])."\""; break;
	  }
	 }
	 $db->Close();
	 if(count($archiveInfo['extensions']))
	 {
	  $xml.= ">";
	  for($i=0; $i < count($archiveInfo['extensions']); $i++)
	  {
	   $ext = $archiveInfo['extensions'][$i];
	   if(file_exists($_BASE_PATH."etc/dynarc/extensions/".$ext."/index.php"))
	   {
		include_once($_BASE_PATH."etc/dynarc/extensions/".$ext."/index.php");
		if(is_callable("dynarcextension_".$ext."_syncexport",false))
		{
		 $ret = call_user_func("dynarcextension_".$ext."_syncexport",$sessid,$shellid,$archiveInfo,$itemInfo);
		 if($ret['error'])
		  return $ret;
		 $xml.= $ret['xml'];
		 if($ret['attachments'] && count($ret['attachments']))
		  $attachments = array_merge($attachments,$ret['attachments']);
		}
	   }
	  }
	  $xml.= "</item>\n";
	 }
	 else
	  $xml.= "/>";

	 $xml.= "</xml>";
	 $ret = dynarc_sync_saveToFile($xml, $tempFolder."/".$archiveInfo['prefix']."/".$items[$c]['syncid'].".xml", $sessid, $shellid);
	 if($ret['error'])
	  return array('message'=>"Sync failed!".$ret['message'],"error"=>$ret['error']);
	} break;
   }
  }
  /* EOF - EXPORT ITEMS */
  if($xmlCreated != "")
   $xmlSummary.= "<created>".$xmlCreated."</created>";
  if($xmlUpdated != "")
   $xmlSummary.= "<updated>".$xmlUpdated."</updated>";
  if($xmlMoved != "")
   $xmlSummary.= "<moved>".$xmlMoved."</moved>";
  if($xmlTrashed != "")
   $xmlSummary.= "<trashed>".$xmlTrashed."</trashed>";
  if($xmlRemoved != "")
   $xmlSummary.= "<removed>".$xmlRemoved."</removed>";
  if($xmlRestored != "")
   $xmlSummary.= "<restored>".$xmlRestored."</restored>";
  $xmlSummary.="</xml>";
 
  $ret = dynarc_sync_saveToFile($xmlSummary, $tempFolder."/".$archiveInfo['prefix']."/summary.xml", $sessid, $shellid);
  if($ret['error'])
   return array('message'=>"Sync failed!".$ret['message'],"error"=>$ret['error']);

  /* Export attachments */
  if(count($attachments))
  {
   $out.= "Export ".count($attachments)." attachments...";
   $q = "";
   $missed = array();
   for($i=0; $i < count($attachments); $i++)
   {
	$att = str_replace($userpath,"",$attachments[$i]);
	if(file_exists($_BASE_PATH.$userpath.$att))
	{ 
     $ret = GShell("cp -s `".$att."` -d `".$tempFolder."/".$archiveInfo['prefix']."/__attachments/".$att."`",$sessid, $shellid);
     if($ret['error']) {$ret['message'] = $out.$ret['message'];	return $ret; }
	}
	else
	 $missed[] = $att;
   }
   if(count($missed))
    $out.= "Warning: ".count($missed)." attachments missed!\n";
   else
    $out.= "done!\n";
  }

  $out.= "Archive ".$archiveInfo['name']." has been exported for synchronization.\n";
 }

 /* OTHER INCLUDES */
 if($sessInfo['uname'] == "root")
 {
  // INCLUDE COMPANY-PROFILE
  if($includeCompanyProfile)
  {
   GShell("cp -s `include/company-profile.php` -d `".$tempFolder."/__files/include/company-profile.php`",$sessid,$shellid);
   include_once($_BASE_PATH."include/company-profile.php");
   if($_COMPANY_PROFILE['logo'] && file_exists($_BASE_PATH.$_COMPANY_PROFILE['logo']))
	GShell("cp -s `".$_COMPANY_PROFILE['logo']."` -d `".$tempFolder."/__files/".$_COMPANY_PROFILE['logo']."`",$sessid,$shellid);
  }
  // INCLUDE CASH-RESOURCES
  if($includeCashResources)
  {
   $xml = "<xml generator='Gnujiko' dist='10.1' type='database-table' table='cashresources'>\n";
   $db = new AlpaDatabase();
   $fields = $db->FieldsInfo("cashresources");
   $db->RunQuery("SELECT * FROM cashresources WHERE 1 ORDER BY id ASC");
   while($db->Read())
   {
    $xml.= "<item";
	reset($fields);
    while(list($k,$v) = each($fields))
    {
     $xml.= " ".$k."=\"".sanitize($db->record[$k])."\"";
    }
	$xml.= "/>\n";
   }
   $xml.= "</xml>";
   $db->Close();
   GShell("mkdir `".$tempFolder."/__config`",$sessid,$shellid); 
   $ret = dynarc_sync_saveToFile($xml, $tempFolder."/__config/cashresources.xml", $sessid, $shellid);
   if($ret['error']) return array('message'=>"Sync failed!".$ret['message'],"error"=>$ret['error']);
  }
  // INCLUDE VAT-RATES
  if($includeVatRates)
  {
   $xml = "<xml generator='Gnujiko' dist='10.1' type='database-table' table='dynarc_vatrates_items'>\n";
   $db = new AlpaDatabase();
   $fields = $db->FieldsInfo("dynarc_vatrates_items");
   $db->RunQuery("SELECT * FROM dynarc_vatrates_items WHERE 1 ORDER BY id ASC");
   while($db->Read())
   {
    $xml.= "<item";
	reset($fields);
    while(list($k,$v) = each($fields))
    {
	 switch($k)
	 {
	  case 'uid' : $xml.= " uid=\"".dynarcsync_getUserName($db->record['uid'])."\""; break;
	  case 'gid' : $xml.= " gid=\"".dynarcsync_getGroupName($db->record['gid'])."\""; break;
	  default : $xml.= " ".$k."=\"".sanitize($db->record[$k])."\""; break;
	 }
    }
	$xml.= "/>\n";
   }
   $xml.= "</xml>";
   $db->Close();
   GShell("mkdir `".$tempFolder."/__config`",$sessid,$shellid); 
   $ret = dynarc_sync_saveToFile($xml, $tempFolder."/__config/dynarc_vatrates_items.xml", $sessid, $shellid);
   if($ret['error']) return array('message'=>"Sync failed!".$ret['message'],"error"=>$ret['error']);
  }
  // INCLUDE PAYMENT-MODES
  if($includePaymentModes)
  {
   $xml = "<xml generator='Gnujiko' dist='10.1' type='database-table' table='payment_modes'>\n";
   $db = new AlpaDatabase();
   $fields = $db->FieldsInfo("payment_modes");
   $db->RunQuery("SELECT * FROM payment_modes WHERE 1 ORDER BY id ASC");
   while($db->Read())
   {
    $xml.= "<item";
	reset($fields);
    while(list($k,$v) = each($fields))
    {
     $xml.= " ".$k."=\"".sanitize($db->record[$k])."\"";
    }
	$xml.= "/>\n";
   }
   $xml.= "</xml>";
   $db->Close();
   GShell("mkdir `".$tempFolder."/__config`",$sessid,$shellid); 
   $ret = dynarc_sync_saveToFile($xml, $tempFolder."/__config/payment_modes.xml", $sessid, $shellid);
   if($ret['error']) return array('message'=>"Sync failed!".$ret['message'],"error"=>$ret['error']);
  }
  // INCLUDE PRICE-LISTS
  if($includePriceLists)
  {
   $xml = "<xml generator='Gnujiko' dist='10.1' type='database-table' table='pricelists'>\n";
   $db = new AlpaDatabase();
   $fields = $db->FieldsInfo("pricelists");
   $db->RunQuery("SELECT * FROM pricelists WHERE 1 ORDER BY id ASC");
   while($db->Read())
   {
    $xml.= "<item";
	reset($fields);
    while(list($k,$v) = each($fields))
    {
     $xml.= " ".$k."=\"".sanitize($db->record[$k])."\"";
    }
	$xml.= "/>\n";
   }
   $xml.= "</xml>";
   $db->Close();
   GShell("mkdir `".$tempFolder."/__config`",$sessid,$shellid); 
   $ret = dynarc_sync_saveToFile($xml, $tempFolder."/__config/pricelists.xml", $sessid, $shellid);
   if($ret['error']) return array('message'=>"Sync failed!".$ret['message'],"error"=>$ret['error']);
  }
  // INCLUDE STORES
  if($includeStores)
  {
   $xml = "<xml generator='Gnujiko' dist='10.1' type='database-table' table='stores'>\n";
   $db = new AlpaDatabase();
   $fields = $db->FieldsInfo("stores");
   $db->RunQuery("SELECT * FROM stores WHERE 1 ORDER BY id ASC");
   while($db->Read())
   {
    $xml.= "<item";
	reset($fields);
    while(list($k,$v) = each($fields))
    {
     $xml.= " ".$k."=\"".sanitize($db->record[$k])."\"";
    }
	$xml.= "/>\n";
   }
   $xml.= "</xml>";
   $db->Close();
   GShell("mkdir `".$tempFolder."/__config`",$sessid,$shellid); 
   $ret = dynarc_sync_saveToFile($xml, $tempFolder."/__config/stores.xml", $sessid, $shellid);
   if($ret['error']) return array('message'=>"Sync failed!".$ret['message'],"error"=>$ret['error']);
  }
  // INCLUDE VAT REGISTER
  if($includeVatRegister)
  {
  }
  // INCLUDE PETTY CASHBOOK
  if($includePettyCashbook)
  {
  }
  
 }

 /* Compress temporary folder */
 $ret = GShell("zip `".$tempFolder."` `".$tempFolder.".zip`",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 $out.= $ret['message'];
 $outArr = $ret['outarr'];

 /* remove temp folder */
 GShell("rm `".$tempFolder."`",$sessid,$shellid);

 $out.= "The sync file has been saved into file: ".$outArr['fullpath'];

 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_sync_registerDevice($args, $sessid, $shellid=0)
{
 global $_BASE_PATH;
 $deviceType = "WEBSERVER";

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=2; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$deviceName=$args[$c+1]; $c++;} break;
   case '-type' : {$deviceType=$args[$c+1]; $c++;} break;
   case '-url' : {$URL=$args[$c+1]; $c++;} break;
   case '-login' : case '-user' : case '-username' : {$deviceLogin=$args[$c+1]; $c++;} break;
   case '-password' : case '-passwd' : case '-pass' : {$devicePassword=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO dynarcsync_devices(name,device_type,url,login,password) VALUES('".$db->Purify($deviceName)."','"
	.$deviceType."','".$URL."','".$deviceLogin."','".$devicePassword."')");
 $id = $db->GetInsertId();
 $db->Close();

 $out = "Device has been registered! ID=".$id;
 $outArr = array('id'=>$id,'name'=>$deviceName,'type'=>$deviceType,'url'=>$URL,'login'=>$deviceLogin,'password'=>$devicePassword);

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_sync_unregisterDevice($args, $sessid, $shellid=0)
{
  global $_BASE_PATH;

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=2; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
  }

 if(!$id)
  return array("message"=>"You must specify device id. (with -id DEVICE_ID)","error"=>"INVALID_DEVICE");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarcsync_devices WHERE id='".$id."'");
 if(!$db->Read())
 {
  $db->Close();
  return array('message'=>"Device #".$id." does not exists.", "error"=>"DEVICE_DOES_NOT_EXISTS");
 }
 $deviceName = $db->record['name'];
 $db->RunQuery("DELETE FROM dynarcsync_devices WHERE id='".$id."'");
 $db->Close();

 $out = "Device #".$id." - ".$deviceName." has been removed!";
 $outArr = array('id'=>$id,'name'=>$deviceName);

 return array('message'=>$out,'outarr'=>$outArr);

}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_sync_deviceList($args, $sessid, $shellid=0)
{
 global $_BASE_PATH;

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarcsync_devices WHERE 1 ORDER BY id ASC");
 while($db->Read())
 {
  $out.= "#".$db->record['id']." - ".$db->record['name'].(($db->record['last_sync_time'] != "0000-00-00 00:00:00") ? " last sync:".date('d/m/Y H:i',$db->record['last_sync_time']) : "")."\n";
  $outArr[] = array('id'=>$db->record['id'],'name'=>$db->record['name'],'type'=>$db->record['device_type'],'url'=>$db->record['url'],'last_sync'=>$db->record['last_sync_time'],'login'=>$db->record['login'],'password'=>$db->record['password']);
 }
 $out.= "\n".count($outArr)." devices found.";
 $db->Close();
 if(!count($outArr))
  return array('message'=>"No device found");
 return array('message'=>$out, 'outarr'=>$outArr);

}
//-------------------------------------------------------------------------------------------------------------------//
function sanitize($str)
{
 return str_replace(array("&","<",">","\"","'"),array("&amp;","&lt;","&gt;","&quot;","&apos;"),$str);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_sync_saveToFile($xml, $fileName, $sessid, $shellid=0)
{
 global $_BASE_PATH, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_USERS_HOMES, $_DEFAULT_FILE_PERMS;
 $sessInfo = sessionInfo($sessid);
 
 if($sessInfo['uname'] == "root")
  $fileName = $_BASE_PATH.$fileName;
 else if($sessInfo['uid'])
 {
  /* Check if user is able for create folders */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM gnujiko_user_privileges WHERE uid='".$sessInfo['uid']."'");
  $db->Read();
  if(!$db->record['mkdir_enable'])
   return array("message"=>"Unable to create folder: Your account has not privileges to create folders!","error"=>"MKDIR_DISABLED");
  $db->Close();

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $fileName = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/".$fileName;
  $db->Close();
 }
 else
  return array("message"=>"Unable to create folder: you don't have a valid account!","error"=>"INVALID_USER");

 $f = @fopen($fileName,"w");
 if($f)
 {
  if(!@fwrite($f,$xml))
  {
   // Try with FTP //
   if($_FTP_USERNAME)
   {
    $conn = @ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
    if($conn && @ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
    {
     if($_FTP_PATH)
     {
	  if(!@ftp_chdir($conn, $_FTP_PATH))
	   return array('message'=>"FTP connection failed! Unable to change directory by FTP","error"=>"FTP_CHDIR_FAILED");
     }
	 $fp = tmpfile();
	 if(!ftp_fput($conn, $fileName, $fp, FTP_BINARY))
	  return array('message'=>"Unable to create file $fileName by FTP","error"=>"FTP_PUT_ERROR");
	 $f = @fopen($fileName,"w");
	 if(!@fwrite($f,$xml))
	  return array('message'=>"Unable to write to file $fileName using FTP","error"=>"UNABLE_TO_WRITE_USING_FTP");
	}
	else
	 return array('message'=>"FTP connection failed!","error"=>"FTP_CONN_FAILED");
   }	
   else
	return array('message'=>"Unable to create file $fileName","error"=>"PERMISSION_DENIED");
  }
  @fclose($f);
  return array('message'=>"File $fileName has been created!");
 }
 else
 {
   // Try with FTP //
   if($_FTP_USERNAME)
   {
    $conn = @ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
    if($conn && @ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
    {
     if($_FTP_PATH)
     {
	  if(!@ftp_chdir($conn, $_FTP_PATH))
	   return array('message'=>"FTP connection failed! Unable to change directory by FTP","error"=>"FTP_CHDIR_FAILED");
     }
	 $fp = tmpfile();
	 if(!ftp_fput($conn, $fileName, $fp, FTP_BINARY))
	  return array('message'=>"Unable to create file $fileName by FTP","error"=>"FTP_PUT_ERROR");
	 @ftp_chmod($conn, $_DEFAULT_FILE_PERMS, $fileName);
	 $f = @fopen($fileName,"w");
	 if(!@fwrite($f,$xml))
	  return array('message'=>"Unable to write to file $fileName using FTP","error"=>"UNABLE_TO_WRITE_USING_FTP");
	}
	else
	 return array('message'=>"FTP connection failed!","error"=>"FTP_CONN_FAILED");
   }	
   else
	return false;
 }
 return array('message'=>"Unable to create file $fileName","error"=>"PERMISSION_DENIED");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcsync_getUserName($uid)
{
 global $_TMPUSERS;
 if($_TMPUSERS[$uid])
  return $_TMPUSERS[$uid];
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT username FROM gnujiko_users WHERE id='".$uid."'");
 $db->Read();
 $_TMPUSERS[$uid] = $db->record['username'];
 $db->Close();
 return $_TMPUSERS[$uid];
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcsync_getGroupName($gid)
{
 global $_TMPGROUPS;
 if(!$gid)
  return "";
 if($_TMPGROUPS[$gid])
  return $_TMPGROUPS[$gid];
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT name FROM gnujiko_groups WHERE id='".$gid."'");
 $db->Read();
 $_TMPGROUPS[$gid] = $db->record['name'];
 $db->Close();
 return $_TMPGROUPS[$gid];
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcsync_getCatSyncid($archivePrefix, $id)
{
 global $_TMPCATS;

 if(!$id)
  return 0;

 if($_TMPCATS[$archivePrefix][$id])
  return $_TMPCATS[$archivePrefix][$id];
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT syncid FROM dynarc_".$archivePrefix."_categories WHERE id='".$id."'");
 if($db->Read())
  $_TMPCATS[$archivePrefix][$id] = $db->record['syncid'];
 else
 {
  $db->RunQuery("SELECT syncid FROM dynarc_".$archivePrefix."_synclog WHERE cat_id='".$id."'");
  if($db->Read())
   $_TMPCATS[$archivePrefix][$id] = $db->record['syncid'];
  else
   $_TMPCATS[$archivePrefix][$id] = 0;
 }
 $db->Close();
 return $_TMPCATS[$archivePrefix][$id];
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_sync_manualSyncImport($inputFile, $sessid, $shellid=0)
{
 global $_BASE_PATH, $_USERS_HOMES, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_DEFAULT_FILE_PERMS;

 $sessInfo = sessionInfo($sessid);

 if($sessInfo['uname'] == "root")
  $_USER_PATH = $_BASE_PATH;
 else if($sessInfo['uid'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $_USER_PATH = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
  $_USER_PATH = $_BASE_PATH."tmp/";

 $out = "";
 $warnings = "";
 $outArr = array();
 $archivePrefixes = array();

 $unzipFolder = substr($inputFile,0,-4);
 /* unzip file */
 $ret = GShell("unzip -i `".$inputFile."` -o `".$unzipFolder."`",$sessid,$shellid); if($ret['error']) return $ret;
 /* list of archives (folders) */
 $ret = GShell("ls -d `".$unzipFolder."`",$sessid,$shellid); if($ret['error']) return $ret;
 
 for($c=0; $c < count($ret['outarr']['dirs']); $c++)
 {
  if(substr($ret['outarr']['dirs'][$c]['name'],0,2) == "__")
   continue;
  $archivePrefixes[] = $ret['outarr']['dirs'][$c];
 }

 /*** GET COUNT ***/
 $steps = 0;
 $stepa = array("created","updated","moved","trashed","removed","restored");
 for($c=0; $c < count($archivePrefixes); $c++)
 {
  /* load summary.xml file */
  if(!file_exists($_USER_PATH.$archivePrefixes[$c]['path']."/summary.xml"))
   return array('message'=>"Sync failed! Cannot load ".$_USER_PATH.$archivePrefixes[$c]['path']."/summary.xml\n",'error'=>"SUMMARY_FILE_NOT_FOUND");
  $xml = new GXML();
  if(!$xml->LoadFromFile($_USER_PATH.$archivePrefixes[$c]['path']."/summary.xml"))
   return array('message'=>"Sync failed! File corrupted. The are errors while parsing ".$_USER_PATH.$archivePrefixes[$c]['path']."/summary.xml\n",'error'=>"SUMMARY_FILE_CORRUPTED");

  for($i=0; $i < count($stepa); $i++)
  {
   $tmp = $xml->GetElementsByTagName($stepa[$i]);
   if(count($tmp))
   {
    $steps+= count($tmp[0]->GetElementsByTagName('category'));
    $steps+= count($tmp[0]->GetElementsByTagName('item'));
   }
  }
 }

 $interface = array("name"=>"progressbar","steps"=>$steps);
 gshPreOutput($shellid,"Sync import.'".$archiveInfo['name']."'", "ESTIMATION", "", "PASSTHRU", $interface);

 /*** EOF - GET COUNT ***/

 for($c=0; $c < count($archivePrefixes); $c++)
 {
  /* first check if archive exists and if is enabled for synchronization */
  $ret = GShell("dynarc archive-info -prefix `".$archivePrefixes[$c]['name']."`",$sessid,$shellid); if($ret['error']) return $ret;
  if(!$ret['outarr']['sync_enabled'])
  {
   $warnings.= "Warning: Archive ".$ret['outarr']['name']." is not enabled for syncronization.Enable it by tiping this command: sudo dynarc enable-sync -ap ".$ret['outarr']['prefix']."\n";
   continue;
  }

  $archiveInfo = $ret['outarr'];
  /* get extensions */
  $archiveInfo['extensions'] = array();
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' ORDER BY id ASC");
  while($db->Read())
   $archiveInfo['extensions'][] = $db->record['extension_name'];
  $db->Close();


  /* load summary.xml file */
  if(!file_exists($_USER_PATH.$archivePrefixes[$c]['path']."/summary.xml"))
   return array('message'=>"Sync failed! Cannot load ".$_USER_PATH.$archivePrefixes[$c]['path']."/summary.xml\n",'error'=>"SUMMARY_FILE_NOT_FOUND");
  $xml = new GXML();
  if(!$xml->LoadFromFile($_USER_PATH.$archivePrefixes[$c]['path']."/summary.xml"))
   return array('message'=>"Sync failed! File corrupted. The are errors while parsing ".$_USER_PATH.$archivePrefixes[$c]['path']."/summary.xml\n",'error'=>"SUMMARY_FILE_CORRUPTED");
  
  /* copy attachments */
  if(file_exists($_USER_PATH.$archivePrefixes[$c]['path']."/__attachments/"))
   full_copy($_USER_PATH.$archivePrefixes[$c]['path']."/__attachments/",$_USER_PATH,$_DEFAULT_FILE_PERMS);

  $created = $xml->GetElementsByTagName('created');
  if(count($created))
  {
   $cats = $created[0]->GetElementsByTagName('category');
   if(count($cats))
   {
    for($i=0; $i < count($cats); $i++)
	{
	 gshPreOutput($shellid, "Import category: <i>".$cats[$i]->getString('name')."</i>","PROGRESS", "");
     $ret = dynarc_sync_importCatFromXML($_USER_PATH.$archivePrefixes[$c]['path']."/".$cats[$i]->getString('syncid').".xml",'CREATED', $archiveInfo, $sessid,$shellid);
	 if($ret['error']) return $ret;
	 //$out.= $ret['message'];
	}
   }
   $items = $created[0]->GetElementsByTagName('item');
   if(count($items))
   {
    for($i=0; $i < count($items); $i++)
	{
	 gshPreOutput($shellid, "Import item: <i>".$items[$i]->getString('name')."</i>","PROGRESS", "");
     $ret = dynarc_sync_importItemFromXML($_USER_PATH.$archivePrefixes[$c]['path']."/".$items[$i]->getString('syncid').".xml",'CREATED', $archiveInfo, $sessid,$shellid);
	 if($ret['error']) return $ret;
	 //$out.= $ret['message'];
	}
   }
  }

  $updated = $xml->GetElementsByTagName('updated');
  if(count($updated))
  {
   $cats = $updated[0]->GetElementsByTagName('category');
   if(count($cats))
   {
    for($i=0; $i < count($cats); $i++)
	{
	 gshPreOutput($shellid, "Import category: <i>".$cats[$i]->getString('name')."</i>","PROGRESS", "");
     $ret = dynarc_sync_importCatFromXML($_USER_PATH.$archivePrefixes[$c]['path']."/".$cats[$i]->getString('syncid').".xml",'UPDATED', $archiveInfo, $sessid,$shellid);
	 if($ret['error']) return $ret;
	 //$out.= $ret['message'];
	}
   }
   $items = $updated[0]->GetElementsByTagName('item');
   if(count($items))
   {
    for($i=0; $i < count($items); $i++)
	{
	 gshPreOutput($shellid, "Import item: <i>".$items[$i]->getString('name')."</i>","PROGRESS", "");
     $ret = dynarc_sync_importItemFromXML($_USER_PATH.$archivePrefixes[$c]['path']."/".$items[$i]->getString('syncid').".xml",'UPDATED', $archiveInfo, $sessid,$shellid);
	 if($ret['error']) return $ret;
	 //$out.= $ret['message'];
	}
   }
  }

  $moved = $xml->GetElementsByTagName('moved');
  if(count($moved))
  {
   $cats = $moved[0]->GetElementsByTagName('category');
   if(count($cats))
   {
    for($i=0; $i < count($cats); $i++)
	{
	 gshPreOutput($shellid, "Import category: <i>".$cats[$i]->getString('name')."</i>","PROGRESS", "");
     $ret = dynarc_sync_importCatFromXML($_USER_PATH.$archivePrefixes[$c]['path']."/".$cats[$i]->getString('syncid').".xml",'MOVED', $archiveInfo, $sessid,$shellid);
	 if($ret['error']) return $ret;
	 //$out.= $ret['message'];
	}
   }
   $items = $moved[0]->GetElementsByTagName('item');
   if(count($items))
   {
    for($i=0; $i < count($items); $i++)
	{
	 gshPreOutput($shellid, "Import item: <i>".$items[$i]->getString('name')."</i>","PROGRESS", "");
     $ret = dynarc_sync_importItemFromXML($_USER_PATH.$archivePrefixes[$c]['path']."/".$items[$i]->getString('syncid').".xml",'MOVED', $archiveInfo, $sessid,$shellid);
	 if($ret['error']) return $ret;
	 //$out.= $ret['message'];
	}
   }
  }

  $trashed = $xml->GetElementsByTagName('trashed');
  if(count($trashed))
  {
   $db = new AlpaDatabase();
   $cats = $trashed[0]->GetElementsByTagName('category');
   for($i=0; $i < count($cats); $i++)
   {
    gshPreOutput($shellid, "Import category: <i>".$cats[$i]->getString('name')."</i>","PROGRESS", "");
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET trash='1' WHERE syncid='".$cats[$i]->getString('syncid')."'");
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET status='TRASHED',logtime='".date('Y-m-d H:i:s')."' WHERE syncid='".$cats[$i]->getString('syncid')."'");
   }
   $items = $trashed[0]->GetElementsByTagName('item');
   for($i=0; $i < count($items); $i++)
   {
	gshPreOutput($shellid, "Import item: <i>".$items[$i]->getString('name')."</i>","PROGRESS", "");
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET trash='1' WHERE syncid='".$items[$i]->getString('syncid')."'");
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET status='TRASHED',logtime='".date('Y-m-d H:i:s')."' WHERE syncid='".$items[$i]->getString('syncid')."'");
   }
   $db->Close();
  }

  $removed = $xml->GetElementsByTagName('removed');
  if(count($removed))
  {
   $db = new AlpaDatabase();
   $cats = $removed[0]->GetElementsByTagName('category');
   for($i=0; $i < count($cats); $i++)
   {
	gshPreOutput($shellid, "Import category: <i>".$cats[$i]->getString('name')."</i>","PROGRESS", "");
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET trash='1' WHERE syncid='".$cats[$i]->getString('syncid')."'");
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET status='REMOVED',logtime='".date('Y-m-d H:i:s')."' WHERE syncid='".$cats[$i]->getString('syncid')."'");
   }
   $items = $removed[0]->GetElementsByTagName('item');
   for($i=0; $i < count($items); $i++)
   {
	gshPreOutput($shellid, "Import item: <i>".$items[$i]->getString('name')."</i>","PROGRESS", "");
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET trash='1' WHERE syncid='".$items[$i]->getString('syncid')."'");
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET status='REMOVED',logtime='".date('Y-m-d H:i:s')."' WHERE syncid='".$items[$i]->getString('syncid')."'");
   }
   $db->Close();
  }

  $restored = $xml->GetElementsByTagName('restored');
  if(count($restored))
  {
   $db = new AlpaDatabase();
   $cats = $restored[0]->GetElementsByTagName('category');
   for($i=0; $i < count($cats); $i++)
   {
	gshPreOutput($shellid, "Import category: <i>".$cats[$i]->getString('name')."</i>","PROGRESS", "");
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET trash='0' WHERE syncid='".$cats[$i]->getString('syncid')."'");
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET status='RESTORED',logtime='".date('Y-m-d H:i:s')."' WHERE syncid='".$cats[$i]->getString('syncid')."'");
   }
   $items = $restored[0]->GetElementsByTagName('item');
   for($i=0; $i < count($items); $i++)
   {
	gshPreOutput($shellid, "Import item: <i>".$items[$i]->getString('name')."</i>","PROGRESS", "");
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET trash='0' WHERE syncid='".$items[$i]->getString('syncid')."'");
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET status='RESTORED',logtime='".date('Y-m-d H:i:s')."' WHERE syncid='".$items[$i]->getString('syncid')."'");
   }
   $db->Close();
  }

 }

 /* remove temp folder */
 GShell("rm `".$unzipFolder."`",$sessid,$shellid);

 return array('message'=>$out.$warnings, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_sync_importCatFromXML($fileName,$action, $archiveInfo, $sessid,$shellid=0)
{
 if(!file_exists($fileName))
  return array('message'=>"Sync failed! Unable to import category from file ".$fileName,'error'=>"FILE_DOES_NOT_EXISTS");
 $xml = new GXML();
 if(!$xml->LoadFromFile($fileName))
  return array('message'=>"Sync failed! The file ".$fileName." is corrupted.","error"=>"XML_FILE_CORRUPTED");

 
 $tmp = $xml->GetElementsByTagName('category');
 $xmlCatInfo = $tmp[0];
 $uid = 0;
 $gid = 0;
 $parentId = 0;
 $parentHierarchy = ",";
 $parentSyncId = $xmlCatInfo->getString('parent_id');
 /* get uid and gid from xml */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM gnujiko_users WHERE username='".$xmlCatInfo->getString('uid')."' LIMIT 1");
 if($db->Read())
  $uid = $db->record['id'];
 $db->RunQuery("SELECT id FROM gnujiko_groups WHERE name='".$xmlCatInfo->getString('gid')."' LIMIT 1");
 if($db->Read())
  $gid = $db->record['id'];
 $db->Close();
 
 if($parentSyncId != "0")
 {
  /* check if parent category exists */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id,hierarchy FROM dynarc_".$archiveInfo['prefix']."_categories WHERE syncid='".$parentSyncId."' LIMIT 1");
  if($db->Read())
  {
   $parentId = $db->record['id'];
   $parentHierarchy = $db->record['hierarchy'];
  }
  else
  {
   $basepath = substr($fileName, 0, strlen($fileName)-strlen(basename($fileName)));
   if(file_exists($basepath.$parentSyncId.".xml"))
   {
    $ret = dynarc_sync_importCatFromXML($basepath.$parentSyncId.".xml", $action, $archiveInfo, $sessid, $shellid);
	if($ret['error']) return $ret;
	$parentId = $ret['outarr']['id'];
    $parentHierarchy = $ret['outarr']['hierarchy'];
   }
   else
	return array('message'=>"Sync failed! Error while recursive import category; Unable to read file ".$basepath.$parentSyncId.".xml","error"=>"SYNC_FAILED");
  }
  $db->Close();
 }


 /* check if category already exists */
 $db = new AlpaDatabase();
 $fields = $db->FieldsInfo("dynarc_".$archiveInfo['prefix']."_categories");
 $db->RunQuery("SELECT id,hierarchy FROM dynarc_".$archiveInfo['prefix']."_categories WHERE syncid='".$xmlCatInfo->getString('syncid')."' LIMIT 1");
 if(!$db->Read())
 {
  $hierarchy = ($parentHierarchy ? $parentHierarchy : "").($parentId ? $parentId."," : "");
  $qry = "INSERT INTO dynarc_".$archiveInfo['prefix']."_categories(";
  $f = "";
  while(list($k,$v) = each($fields))
  {
   if($k == "id")
	continue;  
   $f.= ",".$k;
  }
  $qry.= ltrim($f,",").") VALUES(";
  $vals = "";
  reset($fields);
  while(list($k,$v) = each($fields))
  {
   if($k == "id")
	continue;
   switch($k)
   {
	case 'uid' : $vals.= ",\"".$uid."\""; break;
	case 'gid' : $vals.= ",\"".$gid."\""; break;
	case 'parent_id' : $vals.= ",\"".$parentId."\""; break;
	case 'hierarchy' : $vals.= ",\"".$hierarchy."\""; break;
	default : $vals.= ",\"".$db->Purify($xmlCatInfo->getString($k))."\""; break;
   }
  }
  $qry.= ltrim($vals,",").")";
  $db->RunQuery($qry);
  $id = $db->GetInsertId();
  $outArr = array('id'=>$id, 'uid'=>$uid, 'gid'=>$gid, '_mod'=>$xmlCatInfo->getString('_mod'), 'parent_id'=>$parentId, 'hierarchy'=>$hierarchy,'name'=>$xmlCatInfo->getString('name'));
  $db->Close();
  // update synclog //
  $db = new AlpaDatabase();
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_synclog(syncid,cat_id,uid,gid,_mod,status,logtime) VALUES('"
	.$xmlCatInfo->getString('syncid')."','".$id."','".$uid."','".$gid."','".$xmlCatInfo->getString('_mod')."','CREATED','".date('Y-m-d H:i:s')."')");
  $db->Close();
 }
 else
 {
  $hierarchy = ($parentHierarchy ? $parentHierarchy : "").($parentId ? $parentId."," : "");
  $id = $db->record['id'];
  $q = "";
  while(list($k,$v) = each($fields))
  {
   if($k == "id")
	continue;
   switch($k)
   {
    case 'uid' : $q.= ",uid='".$uid."'"; break;
    case 'gid' : $q.= ",gid='".$gid."'"; break;
    case 'parent_id' : $q.= ",parent_id='".$parentId."'"; break;
	case 'hierarchy' : $q.= ",hierarchy='".$hierarchy."'"; break;
    default : $q.= ",".$k."=\"".$db->Purify($xmlCatInfo->getString($k))."\""; break;
   }
  }
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET ".ltrim($q,",")." WHERE id='".$id."'");
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET status='UPDATED',logtime='".date('Y-m-d H:i:s')."' WHERE syncid='".$xmlCatInfo->getString('syncid')."'");
  $outArr = array('id'=>$id, 'uid'=>$uid, 'gid'=>$gid, '_mod'=>$xmlCatInfo->getString('_mod'), 'parent_id'=>$parentId, 'hierarchy'=>$hierarchy,'name'=>$xmlCatInfo->getString('name'));
  $db->Close();
 }

 /* Import extensions */
 for($c=0; $c < count($archiveInfo['extensions']); $c++)
 {
  $ext = $archiveInfo['extensions'][$c];
  $list = $xmlCatInfo->GetElementsByTagName($ext);
  if(count($list))
  {
   $extNode = $list[0];
   if(file_exists($_BASE_PATH."etc/dynarc/extensions/".$ext."/index.php"))
   {
	include_once($_BASE_PATH."etc/dynarc/extensions/".$ext."/index.php");
	if(is_callable("dynarcextension_".$ext."_syncimport",false))
	{ 
	 $ret = call_user_func("dynarcextension_".$ext."_syncimport",$sessid,$shellid,$archiveInfo,$outArr,$extNode,true);
	}
   }
  }
 }

 return array('message'=>"Category ".$outArr['name']." has been created!\n","outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_sync_importItemFromXML($fileName,$action, $archiveInfo, $sessid,$shellid=0)
{
 if(!file_exists($fileName))
  return array('message'=>"Sync failed! Unable to import item from file ".$fileName,'error'=>"FILE_DOES_NOT_EXISTS");
 $xml = new GXML();
 if(!$xml->LoadFromFile($fileName))
  return array('message'=>"Sync failed! The file ".$fileName." is corrupted.","error"=>"XML_FILE_CORRUPTED");

 
 $tmp = $xml->GetElementsByTagName('item');
 $xmlItemInfo = $tmp[0];
 $uid = 0;
 $gid = 0;
 $parentId = 0;
 $parentHierarchy = ",";
 $parentSyncId = $xmlItemInfo->getString('cat_id');
 /* get uid and gid from xml */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM gnujiko_users WHERE username='".$xmlItemInfo->getString('uid')."' LIMIT 1");
 if($db->Read())
  $uid = $db->record['id'];
 $db->RunQuery("SELECT id FROM gnujiko_groups WHERE name='".$xmlItemInfo->getString('gid')."' LIMIT 1");
 if($db->Read())
  $gid = $db->record['id'];
 $db->Close();
 
 if($parentSyncId != "0")
 {
  /* check if parent category exists */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id,hierarchy FROM dynarc_".$archiveInfo['prefix']."_categories WHERE syncid='".$parentSyncId."' LIMIT 1");
  if($db->Read())
  {
   $parentId = $db->record['id'];
   $parentHierarchy = $db->record['hierarchy'];
  }
  else
  {
   $basepath = substr($fileName, 0, strlen($fileName)-strlen(basename($fileName)));
   if(file_exists($basepath.$parentSyncId.".xml"))
   {
    $ret = dynarc_sync_importCatFromXML($basepath.$parentSyncId.".xml", $action, $archiveInfo, $sessid, $shellid);
	if($ret['error']) return $ret;
	$parentId = $ret['outarr']['id'];
    $parentHierarchy = $ret['outarr']['hierarchy'];
   }
   else
	return array('message'=>"Sync failed! Error while recursive import category; Unable to read file ".$basepath.$parentSyncId.".xml","error"=>"SYNC_FAILED");
  }
  $db->Close();
 }


 /* check if item already exists */
 $db = new AlpaDatabase();
 $fields = $db->FieldsInfo("dynarc_".$archiveInfo['prefix']."_items");
 $db->RunQuery("SELECT id,hierarchy FROM dynarc_".$archiveInfo['prefix']."_items WHERE syncid='".$xmlItemInfo->getString('syncid')."' LIMIT 1");
 if(!$db->Read())
 {
  $hierarchy = ($parentHierarchy ? $parentHierarchy : "").($parentId ? $parentId."," : "");
  $qry = "INSERT INTO dynarc_".$archiveInfo['prefix']."_items(";
  $f = "";
  while(list($k,$v) = each($fields))
  {
   if($k == "id")
	continue;  
   $f.= ",".$k;
  }
  $qry.= ltrim($f,",").") VALUES(";
  $vals = "";
  reset($fields);
  while(list($k,$v) = each($fields))
  {
   if($k == "id")
	continue;
   switch($k)
   {
	case 'uid' : $vals.= ",\"".$uid."\""; break;
	case 'gid' : $vals.= ",\"".$gid."\""; break;
	case 'cat_id' : $vals.= ",\"".$parentId."\""; break;
	case 'hierarchy' : $vals.= ",\"".$hierarchy."\""; break;
	default : $vals.= ",\"".$db->Purify($xmlItemInfo->getString($k))."\""; break;
   }
  }
  $qry.= ltrim($vals,",").")";
  $db->RunQuery($qry);
  $id = $db->GetInsertId();
  $outArr = array('id'=>$id, 'uid'=>$uid, 'gid'=>$gid, '_mod'=>$xmlItemInfo->getString('_mod'), 'cat_id'=>$parentId, 'hierarchy'=>$hierarchy, 'name'=>$xmlItemInfo->getString('name'));
  $db->Close();
  // update synclog //
  $db = new AlpaDatabase();
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_synclog(syncid,id,uid,gid,_mod,status,logtime) VALUES('"
	.$xmlItemInfo->getString('syncid')."','".$id."','".$uid."','".$gid."','".$xmlItemInfo->getString('_mod')."','CREATED','".date('Y-m-d H:i:s')."')");
  $db->Close();
 }
 else
 {
  $hierarchy = ($parentHierarchy ? $parentHierarchy : "").($parentId ? $parentId."," : "");
  $id = $db->record['id'];
  $q = "";
  while(list($k,$v) = each($fields))
  {
   if($k == "id")
	continue;
   switch($k)
   {
    case 'uid' : $q.= ",uid='".$uid."'"; break;
    case 'gid' : $q.= ",gid='".$gid."'"; break;
    case 'cat_id' : $q.= ",cat_id='".$parentId."'"; break;
	case 'hierarchy' : $q.= ",hierarchy='".$hierarchy."'"; break;
    default : $q.= ",".$k."=\"".$db->Purify($xmlItemInfo->getString($k))."\""; break;
   }
  }
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$id."'");
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET status='UPDATED',logtime='".date('Y-m-d H:i:s')."' WHERE syncid='".$xmlItemInfo->getString('syncid')."'");

  $outArr = array('id'=>$id, 'uid'=>$uid, 'gid'=>$gid, '_mod'=>$xmlItemInfo->getString('_mod'), 'cat_id'=>$parentId, 'hierarchy'=>$hierarchy, 'name'=>$xmlItemInfo->getString('name'));
  $db->Close();
 }

 /* Import extensions */
 for($c=0; $c < count($archiveInfo['extensions']); $c++)
 {
  $ext = $archiveInfo['extensions'][$c];
  $list = $xmlItemInfo->GetElementsByTagName($ext);
  if(count($list))
  {
   $extNode = $list[0];
   if(file_exists($_BASE_PATH."etc/dynarc/extensions/".$ext."/index.php"))
   {
	include_once($_BASE_PATH."etc/dynarc/extensions/".$ext."/index.php");
	if(is_callable("dynarcextension_".$ext."_syncimport",false))
	{ 
	 $ret = call_user_func("dynarcextension_".$ext."_syncimport",$sessid,$shellid,$archiveInfo,$outArr,$extNode);
	}
   }
  }
 }

 return array('message'=>"Item ".$outArr['name']." has been updated!\n","outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

