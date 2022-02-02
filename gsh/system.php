<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 04-12-2016
 #PACKAGE: gnujiko-base
 #DESCRIPTION: System Utility
 #VERSION: 2.27beta
 #CHANGELOG: 04-12-2016 : Aggiornata funzone checkup con cURL.
			 24-10-2016 : MySQLi integration.
			 15-03-2016 : Bug fix in function showVariables.
			 07-03-2016 : Aggiornata funzione system backup. Aggiunto preoutput ad ogni tabella salvata.
			 05-02-2016 : Aggiunto comando debug ed aggiornata funzione cache-clean.
			 04-07-2015 : Aggiunto info su system checkup
			 14-03-2015 : Aggiunto comando check-for-updates.
			 17-12-2014 : Creato comando cacheclean
			 11-11-2014 : Bug fix a fine file, il tag di chiusura PHP ?> dava fastidio ad alcune configurazioni server.
			 05-11-2014 : Bug fix su funzione edit-app.
			 01-07-2014 : Aggiunto i servizi.
			 13-03-2014 : Aggiunta funzione dbimport
			 08-02-2014 : Aggiunto altre info su system checkup
			 05-02-2014 : Aggiunta funzione fix-vatnumbers
			 12-12-2013 : Aggiunto dbinfo
			 11-12-2013 : Aggiunto backup file di configurazione e funzione hotfix.
			 25-07-2013 : Aggiunto funzione system checkup.
			 11-04-2013 : Sistemato i permessi ai files.
			 25-03-2013 : Aggiunto funzioni backup e restore.
			 15-01-2013 : Bug fix.
			 13-01-2013 : Completate funzioni gnujiko8import paymentmodes & rubrica
			 13-12-2012 : Aggiunta funzione shell-log.
			 10-01-2012 : Functions app-serialize and edit-app added.

 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_system($args, $sessid, $shellid=0)
{
 $output = "";
 $outArr = array();

 if(count($args) == 0)
  return system_invalidArguments();

 switch($args[0])
 {
  // ACTION //
  case 'register-app' : return system_registerApp($args, $sessid, $shellid); break;
  case 'edit-app' : return system_editApp($args, $sessid, $shellid); break;
  case 'unregister-app' : return system_unregisterApp($args, $sessid, $shellid); break;
  case 'app-list' : return system_appList($args, $sessid, $shellid); break;
  case 'app-serialize' : return system_appSerialize($args, $sessid, $shellid); break;

  // CONFIGURATION //
  case 'cfg-sec-add' : return system_cfgSectionAdd($args, $sessid, $shellid); break;
  case 'cfg-sec-edit' : return system_cfgSectionEdit($args, $sessid, $shellid); break;
  case 'cfg-sec-del' : return system_cfgSectionDelete($args, $sessid, $shellid); break;
  case 'cfg-sec-list' : return system_cfgSectionList($args, $sessid, $shellid); break;
  case 'cfg-add-element' : return system_cfgAddElement($args, $sessid, $shellid); break;
  case 'cfg-edit-element' : return system_cfgEditElement($args, $sessid, $shellid); break;
  case 'cfg-delete-element' : return system_cfgDeleteElement($args, $sessid, $shellid); break;
  case 'cfg-elements' : case 'cfg-element-list' : return system_cfgElementList($args, $sessid, $shellid); break;

  // IMPORT DATA FROM OLD GNUJIKO VERSION 8.01 //
  case 'gnujiko8import' : return system_gnujiko8import($args, $sessid, $shellid); break;

  // BACKUP AND RESTORE //
  case 'backup' : return system_backup($args, $sessid, $shellid); break;
  case 'restore' : return system_restore($args, $sessid, $shellid); break;

  // SERVICES //
  case 'service' : return system_service($args, $sessid, $shellid); break;
  case 'register-service' : case 'add-service' : case 'new-service' : return system_registerService($args, $sessid, $shellid); break;
  case 'edit-service' : return system_editService($args, $sessid, $shellid); break;
  case 'delete-service' : return system_deleteService($args, $sessid, $shellid); break;
  case 'run-services' : return system_runServices($args, $sessid, $shellid); break;

  // OTHER //
  case 'session-info' : return system_sessionInfo($args, $sessid, $shellid); break;
  case 'shell-log' : return system_shellLog($args, $sessid, $shellid); break;
  case 'debug' : return system_debug($args, $sessid, $shellid); break;
  case 'checkup' : case 'check' : case 'check-up' : return system_checkUp($args, $sessid, $shellid); break;
  case 'hotfix' : return system_hotfix($args, $sessid, $shellid); break;
  case 'fix-vatnumbers' : return system_fixVatNumbers($args, $sessid, $shellid); break;
  case 'cacheclean' : case 'cache-clean' : return system_cacheClean($args, $sessid, $shellid); break;
  case 'check-for-updates' : return system_checkForUpdates($args, $sessid, $shellid); break;

  // DATABASE //
  case 'dbinfo' : return system_dbinfo($args, $sessid, $shellid); break;
  case 'dbimport' : return system_dbimport($args, $sessid, $shellid); break;
 

  default : return system_invalidArguments(); break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function system_invalidArguments()
{
 return array("message"=>"Invalid arguments.", "error"=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function system_registerApp($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $published=1;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-desc' : {$desc=$args[$c+1]; $c++;} break;
   case '-url' : {$url=$args[$c+1]; $c++;} break;
   case '-widget-file' : {$widgetFile=$args[$c+1]; $c++;} break;
   case '-widget-params' : {$widgetParams=$args[$c+1]; $c++;} break;
   case '-icon' : {$icon=$args[$c+1]; $c++;} break;
   case '-large-icon' : {$largeIcon=$args[$c+1]; $c++;} break;
   case '-ordering' : {$ordering=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
   case '-published' : {$published=$args[$c+1]; $c++;} break;
  }

 if(!$name) return array('message'=>"You must specify a valid name","error"=>"INVALID_NAME");
 
 if(!$group && !$perms) // se non si specifica alcun gruppo, automaticamente imposta i permessi in lettura a tutti //
  $perms = 444; 

 $uid = $sessInfo['uid'];
 $gid = $group ? _getGID($group) : $sessInfo['gid'];
 if($perms)
 {
  $mod = new GMOD($perms);
  $mod = $mod->MOD;
 }
 else
  $mod = 440;


 $db = new AlpaDatabase();
 if(!$ordering)
 {
  $db->RunQuery("SELECT ordering FROM gnujiko_applications WHERE 1 ORDER BY ordering DESC LIMIT 1");
  if($db->Read())
   $ordering = $db->record['ordering']+1;
  else
  $ordering = 1;
 }
 $db->RunQuery("INSERT INTO gnujiko_applications(uid,gid,_mod,name,description,icon,large_icon,url,widget_file,widget_params,ordering,published) VALUES('"
	.$uid."','$gid','$mod','$name','$desc','$icon','$largeIcon','$url','$widgetFile','$widgetParams','$ordering','$published')");
 $id = $db->GetInsertId();
 $db->Close();
 $outArr = array('id'=>$id,'name'=>$name,'desc'=>$desc,'url'=>$url,'icon'=>$icon,'largeicon'=>$largeIcon,'ordering'=>$ordering,'widget_file'=>$widgetFile,
	'widget_params'=>$widgetParams,'published'=>$published);
 $out = "done!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_editApp($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-desc' : {$desc=$args[$c+1]; $c++;} break;
   case '-url' : {$url=$args[$c+1]; $c++;} break;
   case '-widget-file' : {$widgetFile=$args[$c+1]; $c++;} break;
   case '-widget-params' : {$widgetParams=$args[$c+1]; $c++;} break;
   case '-icon' : {$icon=$args[$c+1]; $c++;} break;
   case '-large-icon' : {$largeIcon=$args[$c+1]; $c++;} break;
   case '-ordering' : {$ordering=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
   case '-published' : {$published=$args[$c+1]; $c++;} break;
  }

 if(!$id && !$name && !$url) return array('message'=>"You must specify a valid application ID","error"=>"INVALID_APP_ID");

 /* Verify if application exists */
 $db = new AlpaDatabase();
 $query = "SELECT id FROM gnujiko_applications WHERE";
 if($id)			$query.= " id='".$id."'";
 else if($name)		$query.= " name='".$db->Purify($name)."'";
 else if($url)		$query.= " url='".$db->Purify($url)."'";
 $db->RunQuery($query);
 if(!$db->Read())
 {
  $db->Close();
  return array('message'=>"Application not found.", "error"=>"APPLICATION_NOT_FOUND"); 
 }
 $id = $db->record['id'];
 $db->Close();

 /* Update */
 $db = new AlpaDatabase();
 $q = "";
 if($name)
  $q.= ",name='".$db->Purify($name)."'";
 if($desc)
  $q.= ",description='".$db->Purify($desc)."'";
 if(isset($url))
  $q.= ",url='".$url."'";
 if(isset($widgetFile))
  $q.= ",widget_file='".$widgetFile."'";
 if(isset($widgetParams))
  $q.= ",widget_params='".$widgetParams."'";
 if(isset($icon))
  $q.= ",icon='".$icon."'";
 if(isset($largeIcon))
  $q.= ",large_icon='".$largeIcon."'";
 if(isset($published))
  $q.= ",published='$published'";

 if($group)
  $q.= ",gid='"._getGID($group)."'";
 if($perms)
 {
  $mod = new GMOD($perms);
  $q.= ",_mod='".$mod->MOD."'";
 }
 $db->Close();
 
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE gnujiko_applications SET ".ltrim($q,",")." WHERE id='$id'");
 $db->Close();
 $out = "Done! The informations and access settings for this application have been updated!";

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_unregisterApp($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("SELECT * FROM gnujiko_applications WHERE id='$id'");
 else if($name)
  $db->RunQuery("SELECT * FROM gnujiko_applications WHERE name='$name'");
 else
  return array('message'=>"You must specify item id. (with -id ITEM_ID || -name ITEM_NAME)","error"=>"INVALID_ITEM");
 if(!$db->Read())
  return array("message"=>"Item ".($id ? "#$id" : $name)." does not exists", "error"=>"ITEM_DOES_NOT_EXISTS");
 $id = $db->record['id'];
 $db->RunQuery("DELETE FROM gnujiko_applications WHERE id='$id'");
 $db->Close();
 $out.= "Item ".($id ? "#$id" : $name)." has been removed from applications.";
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_appList($args, $sessid, $shellid)
{
 $orderBy = "ordering ASC";
 $sessInfo = sessionInfo($sessid);
 $includeUnpublished = false;
 $verbose=false;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--include-unpublished' : $includeUnpublished=true; break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
  }

 $out = "";
 $outArr = array();

 $mod = new GMOD();
 $uQry = $mod->userQuery($sessid);

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_applications WHERE ($uQry)".(!$includeUnpublished ? " AND published='1'" : "")." ORDER BY $orderBy");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id'],'name'=>$db->record['name'],'desc'=>$db->record['description'],'url'=>$db->record['url'],
	'widget_file'=>$db->record['widget_file'], 'widget_params'=>$db->record['widget_params'], 'icon'=>$db->record['icon'],
	'largeicon'=>$db->record['large_icon'],'published'=>$db->record['published']);
  $mod = new GMOD();
  $mod->set($db->record['_mod'],$db->record['uid'],$db->record['gid']);
  $a['modinfo'] = $mod->toArray($sessInfo['uid']);

  if($verbose)
  {
   $out.= "Application info: ".$db->record['name']."\n";
   $out.= "ID: ".$db->record['id']."\n";
   $out.= "Name: ".$db->record['name']."\n";
   $out.= "Description: ".$db->record['description']."\n";
   $out.= "URL: ".$db->record['url']."\n";
   if($db->record['widget_file'])
    $out.= "Widget file: ".$db->record['widget_file']."\n";
   $out.= "Owner: "._getUserName($a['modinfo']['uid'])."\n";
   $out.= "Group: ".($db->record['gid'] ? _getGroupName($a['modinfo']['gid']) : "")."\n";
   $out.= "Permissions: ".$mod->toString()."\n";
   if($db->record['icon'])
   {
    $out.= "Icon: ";
	if(file_exists($_ABSOLUTE_URL.$db->record['icon']))
	 $out.= "<img src='".$_ABSOLUTE_URL.$db->record['icon']."' width='16'/>\n";
	else
	 $out.= "<i>unavailable</i> <span style='color:red;'>File ".$db->record['icon']." does not exists!</span>\n";
   }
   $out.= "Published: ".($db->record['published'] ? "yes" : "no")."\n\n";
  }
  else
   $out.= "#".$db->record['id']." - ".$db->record['name']."\n";
  $outArr[] = $a;
 }
 $db->Close();
 
 $out.= "\n".count($outArr)." applications found.\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_appSerialize($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   default: $serialize=$args[$c]; break;
  }
 
 $list = explode(",",$serialize);

 $db = new AlpaDatabase();
 for($c=0; $c < count($list); $c++)
  $db->RunQuery("UPDATE gnujiko_applications SET ordering='".$c."' WHERE id='".$list[$c]."'");
 $db->Close();
 return array("message"=>"Done!");
}
//-------------------------------------------------------------------------------------------------------------------//
//--CONFIGURATION SECTIONS-------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function system_cfgSectionAdd($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-tag' : {$tag=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
  }

 if(!$name) return array('message'=>"You must specify a valid name","error"=>"INVALID_NAME");
 
 if(!$group && !$perms) // se non si specifica alcun gruppo, automaticamente imposta i permessi in lettura a tutti //
  $perms = 444; 

 $uid = $sessInfo['uid'];
 $gid = $group ? _getGID($group) : $sessInfo['gid'];
 if($perms)
 {
  $mod = new GMOD($perms);
  $mod = $mod->MOD;
 }
 else
  $mod = 440;

 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO gnujiko_config_sections(uid,gid,_mod,name,tag) VALUES('"
	.$uid."','$gid','$mod','$name','$tag')");
 $id = $db->GetInsertId();
 $db->Close();
 $outArr = array('id'=>$id,'name'=>$name,'tag'=>$tag);
 $out = "done!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_cfgSectionEdit($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-tag' : {$tag=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify section. (with -id SECTION_ID)","error"=>"INVALID_SECTION_ID");

 $db = new AlpaDatabase();

 $q = "";
 if($name)
  $q.= ",name='".$db->Purify($name)."'";
 if($tag)
  $q.= ",tag='$tag'";
 if($group)
  $q.= ",gid='"._getGID($group)."'";
 if($perms)
  $q.= ",_mod='$perms'";

 $db->RunQuery("UPDATE gnujiko_config_sections SET ".ltrim($q,",")." WHERE id='$id'");
 $db->Close();
 $outArr = array('id'=>$id,'name'=>$name,'tag'=>$tag);
 $out = "Section has been updated!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_cfgSectionDelete($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
  }
 if(!$id) return array('message'=>"You must specify section. (with -id SECTION_ID)","error"=>"INVALID_SECTION_ID");
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_config_sections WHERE id='$id'");
 $db->Close();
 $out = "done!";
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_cfgSectionList($args, $sessid, $shellid)
{
 $orderBy = "id ASC";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
  }

 $out = "";
 $outArr = array();

 $m = new GMOD();
 $uQry = $m->userQuery($sessid);

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_config_sections WHERE ($uQry) ORDER BY $orderBy");
 while($db->Read())
 {
  $outArr[] = array('id'=>$db->record['id'],'name'=>$db->record['name'],'tag'=>$db->record['tag']);
  $out.= "#".$db->record['id']." - ".$db->record['name']." <i>(".$db->record['tag'].")</i>\n";
 }
 $db->Close();
 $out.= "\n".count($outArr)." configuration sections found.\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
//--CONFIGURATION ITEMS----------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function system_cfgAddElement($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-sec' : {$secTag=$args[$c+1]; $c++;} break;
   case '-icon' : {$icon=$args[$c+1]; $c++;} break;
   case '-file' : {$file=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
  }

 if(!$name) return array('message'=>"You must specify a valid name","error"=>"INVALID_NAME");
 
 if(!$group && !$perms) // se non si specifica alcun gruppo, automaticamente imposta i permessi in lettura a tutti //
  $perms = 444; 

 $uid = $sessInfo['uid'];
 $gid = $group ? _getGID($group) : $sessInfo['gid'];
 if($perms)
 {
  $mod = new GMOD($perms);
  $mod = $mod->MOD;
 }
 else
  $mod = 440;

 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO gnujiko_config_menu(uid,gid,_mod,name,sec_tag,icon,cfg_file) VALUES('"
	.$uid."','$gid','$mod','".$db->Purify($name)."','$secTag','$icon','$file')");
 $id = $db->GetInsertId();
 $db->Close();
 $outArr = array('id'=>$id,'name'=>$name,'sectag'=>$secTag,'icon'=>$icon,'file'=>$file);
 $out = "done!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_cfgEditElement($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-sectag' : {$secTag=$args[$c+1]; $c++;} break;
   case '-icon' : {$icon=$args[$c+1]; $c++;} break;
   case '-file' : {$file=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify element. (with -id ELEMENT_ID)","error"=>"INVALID_ELEMENT_ID");

 $db = new AlpaDatabase();

 $q = "";
 if($name)
  $q.= ",name='".$db->Purify($name)."'";
 if($secTag)
  $q.= ",sec_tag='$secTag'";
 if($icon)
  $q.= ",icon='$icon'";
 if($file)
  $q.= ",file='$file'";
 if($group)
  $q.= ",gid='"._getGID($group)."'";
 if($perms)
  $q.= ",_mod='$perms'";

 $db->RunQuery("UPDATE gnujiko_config_menu SET ".ltrim($q,",")." WHERE id='$id'");
 $db->Close();
 $outArr = array('id'=>$id,'name'=>$name,'sectag'=>$secTag,'icon'=>$icon,'file'=>$file);
 $out = "Element has been updated!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_cfgDeleteElement($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
  }
 if(!$id) return array('message'=>"You must specify element. (with -id ELEMENT_ID)","error"=>"INVALID_ELEMENT_ID");
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM gnujiko_config_menu WHERE id='$id'");
 $db->Close();
 $out = "done!";
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_cfgElementList($args, $sessid, $shellid)
{
 $orderBy = "id ASC";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-sec' : {$sec=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
  }

 $out = "";
 $outArr = array();

 $m = new GMOD();
 $uQry = $m->userQuery($sessid);

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_config_menu WHERE ($uQry)".($sec ? " AND sec_tag='$sec'" : "")." ORDER BY $orderBy");
 while($db->Read())
 {
  $outArr[] = array('id'=>$db->record['id'],'name'=>$db->record['name'],'sectag'=>$db->record['sec_tag'],
	'icon'=>$db->record['icon'],'file'=>$db->record['cfg_file']);
  $out.= "#".$db->record['id']." - ".$db->record['name']." <i>(".$db->record['sec_tag'].")</i>\n";
 }
 $db->Close();
 $out.= "\n".count($outArr)." configuration elements found.\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
//--OTHER------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function system_sessionInfo($args, $sessid, $shellid)
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_session WHERE session_id='$sessid'");
 if(!$db->Read())
  return array("message"=>"Invalid session id: $sessid", "error"=>"SESSID_DOES_NOT_EXISTS");
 $out = "Username: ".$db->record['uname']."\n";
 $out.= "Login time: ".date('d/m/Y H:i',$db->record['login_time'])."\n";
 $out.= "Session ID: ".$sessid."\n";
 $out.= "Shell ID: ".$shellid."\n";
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_shellLog($args, $sessid, $shellid)
{
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'on' : case 'enable' : {
	 setcookie("GNUJIKO-ENABLE-SHELL-LOG","1",strtotime("+1 week"));
	 return array('message'=>"Shell log has been enabled!");
	} break;

   case 'off' : case 'disable' : {
	 setcookie("GNUJIKO-ENABLE-SHELL-LOG",false);
	 return array('message'=>"Shell log has been disabled!");
	} break;

   case 'clear' : return system_shellLog_clear(); break;
  }
 
 return array('message'=>"Invalid action!", "error"=>"INVALID_ACTION");
}
//-------------------------------------------------------------------------------------------------------------------//
function system_shellLog_clear()
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_DEFAULT_FILE_PERMS;
 $fileName = $_BASE_PATH."tmp/shell-log.xml";

 $f = @fopen($fileName,"r+");

 if($f !== false) 
 {
  ftruncate($f, 0);
  fclose($f);
  return array('message'=>"Done!");
 }

 if($f)
 {
  if(!@fwrite($f,""))
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
	   return array('message'=>"Unable to chdir by FTP","error"=>"FTP_CHDIR_FAILED");
     }
	 $fp = tmpfile();
	 if(!ftp_fput($conn, $fileName, $fp, FTP_BINARY))
	  return array('message'=>"Unable to chdir by FTP","error"=>"FTP_CHDIR_FAILED");
	 $f = @fopen($fileName,"w");
	 if(!@fwrite($f,""))
	  return array('message'=>"Unable to write data into file by FTP","error"=>"FTP_FWRITE_FAILED");
	}
	else
	 return array('message'=>"FTP connection failed!","error"=>"FTP_CONNECTION_FAILED");
   }	
   else
	return array('message'=>"FTP is not yet configured.","error"=>"FTP_DISABLED");;
  }
  @fclose($f);
  return array('message'=>"Done! Shell-log has been cleaned!");
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
	   return array('message'=>"Unable to chdir by FTP","error"=>"FTP_CHDIR_FAILED");
     }
	 $fp = tmpfile();
	 if(!ftp_fput($conn, $fileName, $fp, FTP_BINARY))
	  return array('message'=>"Unable to chdir by FTP","error"=>"FTP_CHDIR_FAILED");
	 @ftp_chmod($conn, $_DEFAULT_FILE_PERMS, $fileName);
	 $f = @fopen($fileName,"w");
	 if(!@fwrite($f,""))
	  return array('message'=>"Unable to write data into file by FTP","error"=>"FTP_FWRITE_FAILED");
	}
	else
	 return array('message'=>"FTP connection failed!","error"=>"FTP_CONNECTION_FAILED");
   }	
   else
	return array('message'=>"FTP is not yet configured.2","error"=>"FTP_DISABLED");;
 }
 return array('message'=>"Done! Shell-log has been cleaned!");
}
//-------------------------------------------------------------------------------------------------------------------//
function system_gnujiko8import($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'paymentmodes' : return system_gnujiko8import_paymentmodes($args, $sessid, $shellid); break;
   case 'rubrica' : return system_gnujiko8import_rubrica($args, $sessid, $shellid); break;
  }

}
//-------------------------------------------------------------------------------------------------------------------//
function system_gnujiko8import_paymentmodes($args, $sessid, $shellid)
{
 for($c=2; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-host' : {$host=$args[$c+1]; $c++;} break;
   case '-user' : case '-login' : {$user=$args[$c+1]; $c++;} break;
   case '-passw' : case '-pass' : case '-password' : {$passw=$args[$c+1]; $c++;} break;
   case '-db' : {$dbname=$args[$c+1]; $c++;} break;


   case '--clear' : case '--clean' : case '--empty' : case '--overwrite' : $truncateTable=true; break;
  }

 if(!$dbname)
  return array('message'=>"You must specify database name. (with: -db DATABASE_NAME)", "error"=>"INVALID_DB_NAME");

 if($truncateTable)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("TRUNCATE TABLE `payment_modes`");
  $db->Close();
 }

 $out = "Connecting to database ".$dbname."...";
 $db = new AlpaDatabase($host, $user, $passw, $dbname);
 if(!$db)
  return array('message'=> $out.=" failed!\nUnable to connect with database $dbname.", "error"=>"DATABASE_CONNECT_FAIELED");
 $out.= "done\n";

 // get payment mode list //
 $items = array();
 $db->RunQuery("SELECT * FROM payment_mode WHERE 1 ORDER BY name ASC");
 while($db->Read())
 {
  $items[] = $db->record;
 }
 $db->Close();

 $interface = array("name"=>"progressbar","steps"=>count($items));
 gshPreOutput($shellid,"Estimated elements to import: ".count($items), "ESTIMATION", "", "PASSTHRU", $interface);

 for($c=0; $c < count($items); $c++)
 {
  $item = $items[$c];
  $type = "";

  gshPreOutput($shellid, "Import: <i>".$item['name']."</i>","PROGRESS", "");
  // DETECT PAYMENT TYPE //
  if((stripos($item['name'],"RI.BA") !== false) || (stripos($item['name'],"R.B.") !== false) || (stripos($item['name'],"RIBA") !== false))
   $type = "RB";
  else if((stripos($item['name'],"B.B") !== false) || (stripos($item['name'],"BONIFICO") !== false) || (stripos($item['name'],"BANCA") !== false))
   $type = "BB";
  else if((stripos($item['name'],"R.D") !== false) || (stripos($item['name'],"RIMESSA") !== false))
   $type = "RD";
  // EOF - DETECT PAYMENT TYPE //
  $ret = GShell("paymentmodes new -name `".$item['name']."` -type `".$type."` -perc1 '".$item['x_perc1']."' -perc2 '"
	.$item['x_perc2']."' -perc3 '".$item['x_perc3']."' -perc4 '".$item['x_perc4']."' -perc5 '".$item['x_perc5']."' -perc6 '"
	.$item['x_perc6']."' -daynum1 '".$item['x_daynum1']."' -daynum2 '".$item['x_daynum2']."' -daynum3 '".$item['x_daynum3']."' -daynum4 '"
	.$item['x_daynum4']."' -daynum5 '".$item['x_daynum5']."' -daynum6 '".$item['x_daynum6']."' -daydoc1 '".$item['x_daydoc1']."' -daydoc2 '"
	.$item['x_daydoc2']."' -daydoc3 '".$item['x_daydoc3']."' -daydoc4 '".$item['x_daydoc4']."' -daydoc5 '".$item['x_daydoc5']."' -daydoc6 '"
	.$item['x_daydoc6']."'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
 }

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_gnujiko8import_rubrica($args, $sessid, $shellid)
{
 for($c=2; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-host' : {$host=$args[$c+1]; $c++;} break;
   case '-user' : case '-login' : {$user=$args[$c+1]; $c++;} break;
   case '-passw' : case '-pass' : case '-password' : {$passw=$args[$c+1]; $c++;} break;
   case '-db' : {$dbname=$args[$c+1]; $c++;} break;

   /* OPTIONS */
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;

   case '--clear' : case '--clean' : case '--empty' : case '--overwrite' : $truncateTable=true; break;
  }

 if(!$dbname)
  return array('message'=>"You must specify database name. (with: -db DATABASE_NAME)", "error"=>"INVALID_DB_NAME");

 if($truncateTable)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("TRUNCATE TABLE `dynarc_rubrica_items`");
  $db->RunQuery("TRUNCATE TABLE `dynarc_rubrica_contacts`");
  $db->RunQuery("TRUNCATE TABLE `dynarc_rubrica_banks`");
  $db->Close();
 }

 
 $out = "Connecting to database ".$dbname."...";
 $db = new AlpaDatabase($host, $user, $passw, $dbname);
 if(!$db)
  return array('message'=> $out.=" failed!\nUnable to connect with database $dbname.", "error"=>"DATABASE_CONNECT_FAIELED");
 $out.= "done\n";
 $catByID = array();
 $catByTag = array();

 // get categories list //
 $db->RunQuery("SELECT * FROM dynarc_rubrica_categories WHERE trash='0'");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id'], 'parent_id'=>$db->record['parent_id'], 'tag'=>$db->record['tag'], 'name'=>$db->record['name'], 'description'=>$db->record['description'], 'ordering'=>$db->record['ordering'], 'ctime'=>$db->record['ctime'], 'published'=>$db->record['published']);
  $catByID[$db->record['id']] = $a;
  $catByTag[$db->record['tag']] = $a;
 }
 $db->Close();

 // get paymentmodes list //
 $paymentMode = array();
 $db = new AlpaDatabase($host, $user, $passw, $dbname);
 $db->RunQuery("SELECT * FROM payment_mode WHERE 1 ORDER BY id ASC");
 while($db->Read())
  $paymentMode[$db->record['id']] = $db->record;
 $db->Close();

 // IMPORT CUSTOMERS //
 $items = array();
 $db = new AlpaDatabase($host, $user, $passw, $dbname);
 $db->RunQuery("SELECT * FROM dynarc_rubrica_items WHERE cat_id='".$catByTag['customers']['id']."' AND trash='0' ORDER BY name ASC".($limit ? " LIMIT $limit" : ""));
 while($db->Read())
 {
  $a = $db->record;
  // get generality //
  $db2 = new AlpaDatabase($host, $user, $passw, $dbname);
  $db2->RunQuery("SELECT * FROM dynarc_rubrica_generality WHERE item_id='".$a['id']."' LIMIT 1");
  $db2->Read();
  $a['generality'] = array('iscompany'=>$db2->record['iscompany'], 'taxcode'=>$db2->record['taxcode'], 'vatnumber'=>$db2->record['vatnumber'], 'paymentmode'=>$db2->record['paymentmode']);
  $db2->Close();

  // get contacts //
  $a['contacts'] = array();
  $db2 = new AlpaDatabase($host, $user, $passw, $dbname);
  $db2->RunQuery("SELECT * FROM dynarc_rubrica_contacts WHERE item_id='".$a['id']."' ORDER BY isdefault DESC, id ASC");
  while($db2->Read())
  {
   $a['contacts'][] = $db2->record;
  }
  $db2->Close();

  // get banks //
  $a['banks'] = array();
  $db2 = new AlpaDatabase($host, $user, $passw, $dbname);
  $db2->RunQuery("SELECT * FROM dynarc_rubrica_banks WHERE item_id='".$a['id']."' ORDER BY isdefault DESC, id ASC");
  while($db2->Read())
  {
   $a['banks'][] = $db2->record;
  }
  $db2->Close();

  $items[] = $a;
 }
 $db->Close();

 $interface = array("name"=>"progressbar","steps"=>count($items));
 gshPreOutput($shellid,"Estimated elements to import: ".count($items), "ESTIMATION", "", "PASSTHRU", $interface);


 /* Update paymentMode new ids */
 $db = new AlpaDatabase();
 while(list($k,$v) = each($paymentMode))
 {
  $db->RunQuery("SELECT id FROM payment_modes WHERE name='".$paymentMode[$k]['name']."' LIMIT 1");
  $db->Read();
  $paymentMode[$k]['id'] = $db->record['id'];
 }
 $db->Close();


 for($c=0; $c < count($items); $c++)
 {
  $item = $items[$c];
  gshPreOutput($shellid, "Import: <i>".$item['name']."</i>","PROGRESS", "");

  $contactsQry = "";
  if(count($item['contacts']))
  {
   $cc = $item['contacts'][0];
   $contactsQry =  " -extset `contacts.name='''".$item['name']."''',label='''"
	.$cc['label']."''',address='''".$cc['address']."''',city='''".$cc['city']."''',zipcode='".$cc['zipcode']."',province='"
	.$cc['province']."',countrycode='".$cc['countrycode']."',phone='".$cc['phone']."',phone2='".$cc['phone2']."',fax='"
	.$cc['fax']."',cell='".$cc['cell']."',email='".$cc['email']."',skype='".$cc['skype']."'`";
  }

  $ret = GShell("dynarc new-item -ap `rubrica` -ct customers -name `".$item['name']."` -group rubrica -perms 664 -set `iscompany='".$item['generality']['iscompany']."',taxcode='".$item['generality']['taxcode']."',vatnumber='".$item['generality']['vatnumber']."',paymentmode='".$paymentMode[$item['generality']['paymentmode']]['id']."',code_str='".$item['code']."'`".$contactsQry,$sessid,$shellid);
  if($ret['error'])
   return $ret;

  $newItem = $ret['outarr'];
  /* IMPORT CONTACTS */
  if(count($item['contacts']))
  {
   for($i=1; $i < count($item['contacts']); $i++)
   {
    $cc = $item['contacts'][$i];
    GShell("dynarc edit-item -ap `rubrica` -id `".$newItem['id']."` -extset `contacts.name='''".$item['name']."''',label='''"
	.$cc['label']."''',address='''".$cc['address']."''',city='''".$cc['city']."''',zipcode='".$cc['zipcode']."',province='"
	.$cc['province']."',countrycode='".$cc['countrycode']."',phone='".$cc['phone']."',phone2='".$cc['phone2']."',fax='"
	.$cc['fax']."',cell='".$cc['cell']."',email='".$cc['email']."',skype='".$cc['skype']."'`",$sessid,$shellid);
   }
  }
  /* IMPORT BANKS */
  if(count($item['banks']))
  {
   for($i=0; $i < count($item['banks']); $i++)
   {
    $bb = $item['banks'][$i];
    GShell("dynarc edit-item -ap `rubrica` -id `".$newItem['id']."` -extset `banks.holder='''".$bb['owner']."''',name='''"
	.$bb['name']."''',abi='".$bb['abi']."',cab='".$bb['cab']."',cin='".$bb['cin']."',cc='".$bb['cc']."',iban='".$bb['iban']."'`",$sessid,$shellid);
   }
  }

 }

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_backup($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_USERS_HOMES, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-db' : $backupDB=true; break;
   case '-dbname' : {$databaseName=$args[$c+1]; $c++;} break;
   case '-home' : $backupHomeDir=true; break;
   case '-share' : $backupShareFiles=true; break;
   case '-config' : $backupConfigFiles=true; break;
   case '-all' : $backupAll=true; break;
   /* OPTIONS */
   case '--no-zip' : $noZip=true; break;
   case '--no-remove-temp' : $noRemoveTemp=true; break;
  }

 if(!$backupDB && !$backupHomeDir && !$backupConfigFiles)
  $backupAll = true;

 /* Creo la cartella dove andrò a copiare tutti i files e le cartelle di Gnujiko */
 $out = "Creating a directory for backup...";
 $ret = GShell("mkdir tmp/backup",$sessid,$shellid);
 if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
 $out.= "done!\n";

 $steps = 0;
 if($backupAll)
  $steps = 3;
 else
 {
  $steps+= $backupDB ? 1 : 0;
  $steps+= $backupHomeDir ? 1 : 0;
  $steps+= $backupShareFiles ? 1 : 0;
 }
 $steps+= !$noZip ? 1 : 0;
 $steps+= !$noRemoveTemp ? 1 : 0;
 
 if($backupDB || $backupAll)
 {
  $db = new AlpaDatabase(null,null,null,$databaseName ? $databaseName : null);
  $db->RunQuery("SHOW TABLES");
  while($db->Read())
  {
   $steps++;
  }
  $db->Close();
 }

 $interface = array("name"=>"progressbar","steps"=>$steps);
 gshPreOutput($shellid,"Backup Gnujiko. Please wait!", "ESTIMATION", "", "PASSTHRU", $interface);


 if($backupDB || $backupAll)
 {
  gshPreOutput($shellid, "Database backup","PROGRESS");
  /* Exporting database */
  $out.= "Exporting database...";
  $db = new AlpaDatabase(null,null,null,$databaseName ? $databaseName : null);

  $bkoptions = array();
  $bkoptions["gnujiko_session"] = "CREATEONLY";

  $ret = $db->Backup("*",$bkoptions,"tmp/backup/database.sql", array('shellid'=>$shellid, 'msgtype'=>'PROGRESS'));
  if($ret['error'])
   return $ret;

  $db->Close();
 }

 if($backupHomeDir || $backupAll)
 {
  gshPreOutput($shellid, "Home directory backup","PROGRESS", "");
  $ret = GShell("cp `home/` `tmp/backup/__files/home/`",$sessid,$shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
 }

 if($backupShareFiles || $backupAll)
 {
  gshPreOutput($shellid, "Backup shared files","PROGRESS", "");
  $ret = GShell("cp `share/images/` `tmp/backup/__files/share/images/`",$sessid,$shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
 }

 if($backupConfigFiles || $backupAll)
 {
  gshPreOutput($shellid, "Backup configuration files","PROGRESS", "");
  if(file_exists($_BASE_PATH."include/company-profile.php"))
  {
   $ret = GShell("cp `include/company-profile.php` `tmp/backup/__files/include/company-profile.php`",$sessid,$shellid); 
   if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
  }
  if(file_exists($_BASE_PATH."etc/commercialdocs/config.php"))
  {
   $ret = GShell("cp `etc/commercialdocs/config.php` `tmp/backup/__files/etc/commercialdocs/config.php`",$sessid,$shellid); 
   if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
  }
  if(file_exists($_BASE_PATH."etc/commercialdocs/protocols/index.php"))
  {
   $ret = GShell("cp `etc/commercialdocs/protocols/` `tmp/backup/__files/etc/commercialdocs/protocols/`",$sessid,$shellid); 
   if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
  }
 }

 if(!$noZip)
 {
  /* Zipping all */
  gshPreOutput($shellid, "Compress backup","PROGRESS", "");
  $ret = GShell("zip -i `tmp/backup/` -o `tmp/backup.zip`",$sessid,$shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
 }

 if(!$noRemoveTemp)
 {
  /* Remove temporary directory */
  gshPreOutput($shellid, "Removing temporary files","PROGRESS", "");
  $ret = GShell("rm `tmp/backup/`",$sessid,$shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
 }

 $outArr = array('filename'=>'tmp/backup.zip'); 

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_restore($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_USERS_HOMES, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-db' : $restoreDB=true; break;
   case '-dbname' : {$databaseName=$args[$c+1]; $c++;} break;
   case '-files' : $restoreFiles=true; break;
   case '-all' : $restoreAll=true; break;
   case '-f' : {$fileName=$args[$c+1]; $c++;} break;
  }

 if(!$restoreAll && !$restoreDB && !$restoreFiles)
  $restoreAll = true;

 if(!$fileName)
  return array("message"=>"You must specify the backup file name", "error"=>"INVALID_FILE_NAME");
 if(!file_exists($_BASE_PATH.$fileName))
  return array("message"=>"File ".$fileName." does not exists!","error"=>"FILE_DOES_NOT_EXISTS");

 $steps = 2;
 if($restoreAll)
  $steps = 4;
 else
 {
  $steps+= $restoreDB ? 1 : 0;
  $steps+= $restoreFiles ? 1 : 0;
 }
 

 $interface = array("name"=>"progressbar","steps"=>$steps);
 gshPreOutput($shellid,"Restore Gnujiko. Please wait!", "ESTIMATION", "", "PASSTHRU", $interface);

 /* extract backup file */
 gshPreOutput($shellid, "Extract backup file","PROGRESS", "");
 $out.= "Extract backup file...";
 $ret = GShell("unzip -i `".$fileName."` -o `tmp/backup/`",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 $out.= "done!\n";

 /* PRIMA IMPORTIAMO I DATI E POI ALLA FINE IL DATABASE */
 if($restoreFiles || $restoreAll)
 {
  $out.= "Restore files...";
  gshPreOutput($shellid, "Restore files","PROGRESS", "");
  if(!full_copy($_BASE_PATH."tmp/backup/__files/",$_BASE_PATH))
   return array('message'=>"Unable to copy file from tmp/backup/__files/.","error"=>"FILE_PERMISSION_DENIED");
  $out.= "done!\n";
 }

 /* remove temporary directory */
 $out.= "Remove temporary directory...";
 gshPreOutput($shellid, "Remove temporary directory","PROGRESS", "");
 if(file_exists($_BASE_PATH."tmp/backup/__files/"))
 {
  $ret = GShell("rm `tmp/backup/__files/`",$sessid,$shellid);
  if($ret['error']) 
   return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
 }
 $out.= "done!\n";

 if($restoreDB || $restoreAll)
 {
  if(file_exists($_BASE_PATH."tmp/backup/database.sql"))
  {
   $out.= "Restore database...";
   gshPreOutput($shellid, "Restore database","PROGRESS", "");
   $db = new AlpaDatabase();
   $db->RunQueryFromFile($_BASE_PATH."tmp/backup/database.sql");
   $db->Close();
   $out.= "done!\n";
  }
 }

 /* TODO: continuare con il resto dei test... */

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_registerService($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 $enabled = true;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-title' : case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-desc' : case '-description' : {$description=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
   case '-command' : {$command=$args[$c+1]; $c++;} break;
   case '--one-shot' : $oneShot=true; break;
   case '-active' : case '-enabled' : {$enabled=$args[$c+1]; $c++;} break;
  }

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 if($group) $groupId = _getGID($group);
 $uid = $sessInfo['uid'];
 $gid = $groupId ? $groupId : $sessInfo['gid'];
 $mod = $perms ? $perms : "600";

 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO gnujiko_services(uid,gid,_mod,name,description,command,oneshot,enabled) VALUES('"
	.$uid."','".$gid."','".$mod."','".$db->Purify($name)."','".$db->Purify($description)."','".$db->Purify($command)."','"
	.$oneShot."','".$enabled."')");
 if($db->Error)
  return array("message"=>"MySQL error: ".$db->Error, "error"=>"MYSQL_ERROR");
 $id = $db->GetInsertId();
 $db->Close();

 $out.= "Service '".$name."' has been registered. (ID: ".$id.")\n";
 $outArr = array('id'=>$id, 'name'=>$name, 'desc'=>$description, 'command'=>$command, 'oneshot'=>$oneShot, 'enabled'=>$enabled);

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_editService($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-title' : case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-desc' : case '-description' : {$description=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
   case '-command' : {$command=$args[$c+1]; $c++;} break;
   case '-oneshot' : {$oneShot=$args[$c+1]; $c++;} break;
   case '-active' : case '-enabled' : {$enabled=$args[$c+1]; $c++;} break;
  }

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("SELECT * FROM gnujiko_services WHERE id='".$id."'");
 else if($name)
  $db->RunQuery("SELECT * FROM gnujiko_services WHERE name='".$name."'");
 else
  return array("message"=>"You must specify the service. (with: -id SERVICE_ID or -name SERVICE_NAME)", "error"=>"INVALID_SERVICE");
 if(!$db->Read())
  return array("message"=>"Service ".($id ? "#".$id : $name)." does not exists.", "error"=>"SERVICE_DOES_NOT_EXISTS");

 $id = $db->record['id'];
 
 $q = "";
 if(isset($description))	$q.= ",description='".$db->Purify($description)."'";
 if($group)					$q.= ",gid='"._getGID($group)."'";
 if($perms)					$q.= ",_mod='".$perms."'";
 if($command)				$q.= ",command='".$db->Purify($command)."'";
 if(isset($oneShot))		$q.= ",oneshot='".$oneShot."'";
 if(isset($enabled))		$q.= ",enabled='".$enabled."'";

 if($q)
  $db->RunQuery("UPDATE gnujiko_services SET ".ltrim($q,",")." WHERE id='".$id."'");
 if($db->Error)
  return array("message"=>"MySQL error: ".$db->Error, "error"=>"MYSQL_ERROR");
 $db->Close();

 $out.= "done!";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_deleteService($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
  }

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("SELECT * FROM gnujiko_services WHERE id='".$id."'");
 else if($name)
  $db->RunQuery("SELECT * FROM gnujiko_services WHERE name='".$name."'");
 else
  return array("message"=>"You must specify the service. (with: -id SERVICE_ID or -name SERVICE_NAME)", "error"=>"INVALID_SERVICE");
 if(!$db->Read())
  return array("message"=>"Service ".($id ? "#".$id : $name)." does not exists.", "error"=>"SERVICE_DOES_NOT_EXISTS");

 $id = $db->record['id'];

 $db->RunQuery("DELETE FROM gnujiko_services WHERE id='".$id."'");
  return array("message"=>"MySQL error: ".$db->Error, "error"=>"MYSQL_ERROR");
 $db->Close();

 $out = "done! Service ".($name ? $name : "#".$id)." has been removed.";
 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_runServices($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 $ret = GShell("system service list --only-enabled",$sessid,$shellid);
 if($ret['error']) return $ret;
 $list = $ret['outarr'];
 $today = date('Y-m-d');
 $out = "Starting services...\n";
 for($c=0; $c < count($list); $c++)
 {
  $service = $list[$c];
  if($service['oneshot'] && ($service['last_exec'] == $today))
  {
   $out.= "Service ".$service['name']." already started.\n";
   continue;
  }
  else if(!$service['oneshot'] && $_COOKIE['gnujiko-service-'.$service['id']])
  {
   $out.= "Service ".$service['name']." already started.\n";
   continue;
  }

  /* RUN SERVICE */
  $ret = GShell("system service start -id '".$service['id']."'",$sessid,$shellid,$service);
  $out.= $ret['message']."\n";
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_service($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'start' : return system_service_start($args, $sessid, $shellid); break;
   case 'restart' : return system_service_restart($args, $sessid, $shellid); break;
   case 'enable' : return system_service_enable($args, $sessid, $shellid); break;
   case 'disable' : return system_service_disable($args, $sessid, $shellid); break;
   case 'list' : return system_service_list($args, $sessid, $shellid); break;
   case 'info' : return system_service_info($args, $sessid, $shellid); break;

   default : return system_service_invalidArguments($args, $sessid, $shellid); break;
  }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_service_invalidArguments($args, $sessid, $shellid)
{
 $out = "Invalid arguments!\n";
 $out.= "Usage: system service ACTION [...]\n";
 $out.= "List of system service actions:\n";
 $out.= "start - Start a service.\n";
 $out.= "restart - Restart a service.\n";
 $out.= "enable - Enable a service.\n";
 $out.= "disable - Disable a service.\n";
 $out.= "list - List of all services.\n";
 $out.= "info - Get service info.\n";

 return array("message"=>$out, "error"=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function system_service_start($args, $sessid, $shellid, $extra=null)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 $id=0;
 $name = "";

 for($c=2; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   default : {if(!$name) $name=$args[$c];} break;
  }

 if($extra && is_array($extra))
  $serviceInfo = $extra;
 else
 {
  $ret = GShell("system service info".($id ? " -id '".$id."'" : " '".$name."'"),$sessid,$shellid);
  if($ret['error']) return $ret;
  $serviceInfo = $ret['outarr'];
 }

 $today = date('Y-m-d');

 if($serviceInfo['oneshot'] && ($serviceInfo['last_exec'] == $today))
  return array("message"=>"Service ".$serviceInfo['name']." already started.");
 else if(!$serviceInfo['oneshot'] && $_COOKIE['gnujiko-service-'.$serviceInfo['id']])
  return array("message"=>"Service ".$serviceInfo['name']." already started.");

 if($serviceInfo['command'])
 {
  $ret = GShell($serviceInfo['command'],$sessid,$shellid);
  if($ret['error'])
   $out.= "Error during the loading of the service ".$serviceInfo['name'].":\n".$ret['message']."\n";
 }

 if($serviceInfo['oneshot'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE gnujiko_services SET last_exec='".$today."' WHERE id='".$serviceInfo['id']."'");
  $db->Close();
 }
 else
  setcookie("gnujiko-service-".$serviceInfo['id'], 1);

 if(!$ret['error'])
  $out.= "service ".$serviceInfo['name']." has been started.";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_service_restart($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 $id=0;
 $name = "";

 for($c=2; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   default : {if(!$name) $name=$args[$c];} break;
  }

 if($extra && is_array($extra))
  $serviceInfo = $extra;
 else
 {
  $ret = GShell("system service info".($id ? " -id '".$id."'" : " '".$name."'"),$sessid,$shellid);
  if($ret['error']) return $ret;
  $serviceInfo = $ret['outarr'];
 }

 $today = date('Y-m-d');

 if($serviceInfo['command'])
 {
  $ret = GShell($serviceInfo['command'],$sessid,$shellid);
  if($ret['error'])
   $out.= "Error during the loading of the service ".$serviceInfo['name'].":\n".$ret['message']."\n";
 }

 if($serviceInfo['oneshot'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE gnujiko_services SET last_exec='".$today."' WHERE id='".$serviceInfo['id']."'");
  $db->Close();
 }
 else
  setcookie("gnujiko-service-".$serviceInfo['id'], 1);

 if(!$ret['error'])
  $out.= "service ".$serviceInfo['name']." has been restarted.";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_service_enable($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 for($c=2; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
  }

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 /* Verify if service exists */
 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("SELECT * FROM gnujiko_services WHERE id='".$id."'");
 else if($name)
  $db->RunQuery("SELECT * FROM gnujiko_services WHERE name='".$name."'");
 else
  return array("message"=>"You must specify the service. (with: -id SERVICE_ID or -name SERVICE_NAME)", "error"=>"INVALID_SERVICE");
 if(!$db->Read())
  return array("message"=>"Service ".($id ? "#".$id : $name)." does not exists.", "error"=>"SERVICE_DOES_NOT_EXISTS");

 $id = $db->record['id'];

 /* Update service */
 $db->RunQuery("UPDATE gnujiko_services SET enabled='1' WHERE id='".$id."'");
 $db->Close();

 $out.= "Service ".($name ? $name : "#".$id)." has been enabled";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_service_disable($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 for($c=2; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
  }

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 /* Verify if service exists */
 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("SELECT * FROM gnujiko_services WHERE id='".$id."'");
 else if($name)
  $db->RunQuery("SELECT * FROM gnujiko_services WHERE name='".$name."'");
 else
  return array("message"=>"You must specify the service. (with: -id SERVICE_ID or -name SERVICE_NAME)", "error"=>"INVALID_SERVICE");
 if(!$db->Read())
  return array("message"=>"Service ".($id ? "#".$id : $name)." does not exists.", "error"=>"SERVICE_DOES_NOT_EXISTS");

 $id = $db->record['id'];

 /* Update service */
 $db->RunQuery("UPDATE gnujiko_services SET enabled='0' WHERE id='".$id."'");
 $db->Close();

 $out.= "Service ".($name ? $name : "#".$id)." has been disabled";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_service_list($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 $orderBy = "name ASC";

 for($c=2; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '--verbose' : case '-verbose' : $verbose=true; break;
   case '--only-enabled' : $onlyEnabled=true; break;
   case '--only-disabled' : $onlyDisabled=true; break;
  }

 $m = new GMOD();
 $uQry = $m->userQuery($sessid,null,"gnujiko_services");

 $db = new AlpaDatabase();
 $query = "SELECT * FROM gnujiko_services WHERE (".$uQry.")";
 if($onlyEnabled)			$query.= " AND enabled='1'";
 else if($onlyDisabled)		$query.= " AND enabled='0'";
 $query.= " ORDER BY ".$orderBy;
 $db->RunQuery($query);
 while($db->Read())
 {
  $a = array('id'=>$db->record['id'], 'name'=>$db->record['name'], 'desc'=>$db->record['description'], 
	'command'=>$db->record['command'], 'oneshot'=>$db->record['oneshot'], 'enabled'=>$db->record['enabled'],
	'last_exec'=>$db->record['last_exec']);
  $outArr[] = $a;
 }
 $db->Close();

 if($verbose)
 {
  $out.= "List of services:\n";
  for($c=0; $c < count($outArr); $c++)
  {
   $item = $outArr[$c];
   $out.= "#".$item['id']." - ".$item['name']." [".($item['enabled'] ? 'enabled' : 'disabled')."]\n";
  }
 }

 $out.= count($outArr)." services found.";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_service_info($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 for($c=2; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '--verbose' : case '-verbose' : $verbose=true; break;
   default : {if(!$name) $name=$args[$c]; } break;
  }

 if(!$id && !$name)
  return array("message"=>"Error. You must specify the service. (with: -id SERVICE_ID or -name SERVICE_NAME).", "error"=>"INVALID_SERVICE");

 $sessInfo = sessionInfo($sessid);

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_services WHERE ".($id ? "id='".$id."'" : "name='".$name."'"));
 if(!$db->Read())
  return array("message"=>"Service ".($id ? "#".$id : "'".$name."'")." does not exists.", "error"=>"SERVICE_DOES_NOT_EXISTS");
 
 /* CHECK PERMISSION TO READ */
 $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid']);
 if(!$m->canRead($sessInfo['uid']))
  return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");

 $outArr = array('id'=>$db->record['id'], 'name'=>$db->record['name'], 'desc'=>$db->record['description'], 
	'command'=>$db->record['command'], 'oneshot'=>$db->record['oneshot'], 'enabled'=>$db->record['enabled'], 
	'last_exec'=>$db->record['last_exec']);

 if($verbose)
 {
  $out.= "ID: ".$outArr['id']."\n";
  $out.= "Name: ".$outArr['name']."\n";
  $out.= "Description: ".$outArr['desc']."\n";
  $out.= "Oneshot: ".($outArr['oneshot'] ? "true" : "false")."\n";
  $out.= "Enabled: ".($outArr['enabled'] ? "true" : "false")."\n";
  $out.= "Last exec: ".(($outArr['last_exec'] != "0000-00-00") ? $outArr['last_exec'] : "never")."\n";
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function system_checkUp($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_SHELL_CMD_PATH, $_USERS_HOMES, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_DEFAULT_FILE_PERMS;
 $sessInfo = sessionInfo($sessid);

 //if($sessInfo['uname'] != "root")
 // return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $outArr = array();
 $checkUpPHP = false;
 $checkUpDatabase = false;
 $checkUpFilePerms = false;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-php' : $checkUpPHP=true; break;
   case '-database' : $checkUpDatabase=true; break;
   case '-fileperms' : $checkUpFilePerms=true; break;

   case '-all' : $checkUpAll=true; break;
  }

 if(!$checkUpPHP && !$checkUpDatabase && !$checkUpFilePerms)
  $checkUpAll = true;

 if($checkUpPHP || $checkUpAll)
 {
  $out.= "Checking for PHP configuration:\n";
  $out.= "PHP version: ".phpversion()."\n";
  $out.= "Display errors: ".ini_get('display_errors')."\n";
  $out.= "Register globals: ".ini_get('register_globals')."\n";
  $out.= "post_max_size: ".ini_get('post_max_size')."\n";
  $out.= "disable_functions: ".ini_get('disable_functions')."\n";
  $out.= "disable_classes: ".ini_get('disable_classes')."\n";
  $out.= "max_execution_time: ";
  if(ini_get('max_execution_time') < 120)
   $out.= "<b style='color:red'>".ini_get('max_execution_time')."</b> (è troppo basso, impostalo almeno a 240 secondi)\n";
  else
   $out.= "<b style='color:green'>".ini_get('max_execution_time')."</b>\n";
  $out.= "max_input_time: ".ini_get('max_input_time')."\n";
  $out.= "memory_limit: ".ini_get('memory_limit')."\n";
  $out.= "post_max_size: ".ini_get('post_max_size')."\n";
  $out.= "upload_max_filesize: ".ini_get('upload_max_filesize')."\n";
  $out.= "max_file_uploads: ".ini_get('max_file_uploads')."\n";
  $out.= "allow_url_fopen: ";
  if(ini_get('allow_url_fopen'))
   $out.= "<b style='color:green'>attivo</b>\n";
  else
   $out.= "<b style='color:red'>disattivato</b>. (Avrai problemi nelle stampe se non attivi allow_url_fopen dal php.ini)\n";
  $out.= "allow_url_include: ".ini_get('allow_url_include')."\n";

  // CURL
  $out.= "CURL: ";
  if(function_exists('curl_version'))
  {
   $curlRet = curl_version();
   $out.= "<b style='color:green'>Enabled</b> (ver: ".$curlRet['version'].")";
   $out.= " - SSL ver: ".$curlRet['ssl_version'];
   $out.= " - LIBZ (zlib) ver: ".$curlRet['libz_version']."\n";
  }
  else
  {
   if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."amazonmws.php"))
	$out.= "<b style='color:red'>Disabled</b> (avrai problemi con Amazon)\n";
   else
    $out.= "Disabled\n";
  }

  /* Check if mod_security is active */
  ob_start();
  phpinfo(INFO_MODULES);
  $contents = ob_get_clean();
  $moduleSecurityInstalled = strpos($contents, 'mod_security') !== false;
  
  $out.= "mod_security: ".($moduleSecurityInstalled ? "<b style='color:red'>installed</b>" : "<b style='color:green'>not installed</b>")."\n";

  /* FTP info */
  $out.= "FTP server: ".$_FTP_SERVER."\n";
  $out.= "FTP path: ".$_FTP_PATH."\n";

  /* SMTP info */
  $out.= "SMTP: ".ini_get('SMTP')."\n";
  $out.= "smtp_port: ".ini_get('smtp_port')."\n";
  $out.= "\n";
 }

 if($checkUpDatabase || $checkUpAll)
 {
  $db = new AlpaDatabase();
  $mysqlVer = mysqli_get_server_info($db->db);
  $out.= "MySQL version: ".substr($mysqlVer, 0, strpos($mysqlVer, "-"))."\n\n";
  $db->Close();
 }

 if($checkUpFilePerms || $checkUpAll)
 {
  $out.= "Checking for files and folders permissions.\n";
  $out.= "Default file permissions: ".decoct($_DEFAULT_FILE_PERMS)."\n";
  $out.= "Current user home dir: ";
  if($sessInfo['uname'] == "root")
   $out.= "ok! (root has not home directory)\n";
  else
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT * FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
   $db->Read();
   $_HOME_DIR = $db->record['homedir'];
   if(!$_HOME_DIR)
	$out.= "failed! At the user ".$sessInfo['uname']." has not been assigned any directory\n";
   else
   {
	if(!file_exists($_BASE_PATH.$_USERS_HOMES.$_HOME_DIR))
	 $out.= "failed! The folder ".$_HOME_DIR."/ does not exists into directory ".$_USERS_HOMES.". You must create it manually.\n";
	else if(!is_readable($_BASE_PATH.$_USERS_HOMES.$_HOME_DIR))
	 $out.= "failed! The folder ".$_HOME_DIR."/ exists into directory ".$_USERS_HOMES.", but is not readable!. You must set the correct permissions (readable and writable) to that folder.\n";
	else if(!is_writable($_BASE_PATH.$_USERS_HOMES.$_HOME_DIR))
	{
	 // verifica l'accesso tramite FTP //
	 if($_FTP_SERVER && $_FTP_USERNAME)
	 {
	  $conn = @ftp_connect($_FTP_SERVER);
	  if($conn)
	  {
	   if(@ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
	   {
		if($_FTP_PATH && !@ftp_chdir($conn,$_FTP_PATH))
		 $out.= "failed! The folder ".$_HOME_DIR."/ exists but is not writable. You have the FTP enabled, and I can connect with server, but the FTP PATH: '".$_FTP_PATH."' is wrong! Please verify and insert the correct path into the configuration file.\n";
		else
		{
		 // try to write a temporary file into home folder //
		 $tmpFileName = "systemcheckup-".date('Ymdhis').".txt";
		 GShell("echo `Gnujiko 10.1 - System Check-Up` > `".$tmpFileName."`",$sessid,$shellid);
		 if(!file_exists($_BASE_PATH.$_USERS_HOMES.$_HOME_DIR.$tmpFileName))
		  $out.= "failed! The folder ".$_HOME_DIR."/ exists but is not writable. You have the FTP enabled, and I can connect with server, but that directory has a bad permissions. Please make sure that the variable DEFAULT_FILE_PERMS is set correctly into the configuration file! (for example: '0777', or '0755', or '0666'), and change manually by FTP the permissions of that folder.\n";
		 else
		 {
		  // remove the temporary file //
		  $ret = GShell("rm `".$tmpFileName."`",$sessid,$shellid);
		  if($ret['error'])
		   $out.= "warning! The folder ".$_HOME_DIR."/ exists and I can write to by FTP, but an error occur while I try to remove the test temporary file '".$tmpFileName."' inside that folder. Please make sure that the variable DEFAULT_FILE_PERMS is set correctly into the configuration file! (for example: '0777', or '0755', or '0666'), and change manually by FTP the permissions of that folder. \n";
		  else
		   $out.= "done! The folder is ".$_HOME_DIR."/ and is readable and writable by FTP.\n";
		 }
		}
	   }
	   else
		$out.= "failed! The folder ".$_HOME_DIR."/ exists but is not writable. You have the FTP enabled, but I can't connect to the server ".$_FTP_SERVER." with user ".$_FTP_USERNAME.". Login or password are wrong!\n";
	  }
	  else
	   $out.= "failed! The folder ".$_HOME_DIR."/ exists but is not writable. And I cannot connect to the server ".$_FTP_SERVER." by FTP with user ".$_FTP_USERNAME.". Please verify and modify the parameters into the configuration file.\n";
	 }
	 else
	  $out.= "failed! The folder ".$_HOME_DIR."/ exists but is not writable. You must set the correct permissions (readable and writable) to that folder, or enable FTP.\n";
	}
	else
	 $out.= "done! The folder is ".$_HOME_DIR."/ and is readable and writable.\n";
   }
   $db->Close();
  }

 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_hotfix($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $_SESSION_ID = $sessid;
 $_SHELL_ID = $shellid;

 $out = "";
 $outArr = array();

 /* -------------------------------------------------------------------------------------------- */
 /* FIX - COMPANY-PROFILE */
 $out.= "Fix companyprofile-config ...";
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_vatrates_items WHERE 1 ORDER BY id DESC LIMIT 1");
 $db->Read();
 $vatId = $db->record['id'];
 $db->Close();

 $ret = GShell("vatregister register-list",$_SESSION_ID,$_SHELL_ID);
 $db = new AlpaDatabase();
 for($c=0; $c < count($ret['outarr']); $c++)
  $db->RunQuery("ALTER TABLE `vat_register_".$ret['outarr'][$c]['year']."` ADD `vr_".$vatId."_amount` FLOAT NOT NULL , ADD `vr_".$vatId."_vat` FLOAT NOT NULL");
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `totvatreg_purchases` ADD `vr_".$vatId."_amount` FLOAT NOT NULL , ADD `vr_".$vatId."_vat` FLOAT NOT NULL");
 $db->RunQuery("ALTER TABLE `totvatreg_sales` ADD `vr_".$vatId."_amount` FLOAT NOT NULL , ADD `vr_".$vatId."_vat` FLOAT NOT NULL");
 $db->Close();
 $out.= "done!\n";
 /* -------------------------------------------------------------------------------------------- */
 $out.= "Fix dynarc ...";
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_archives` ADD `sync_enabled` TINYINT( 1 ) NOT NULL , ADD INDEX ( `sync_enabled` )");
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarcsync_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `device_type` varchar(64) NOT NULL,
  `url` varchar(255) NOT NULL,
  `login` varchar(40) NOT NULL,
  `password` varchar(32) NOT NULL,
  `last_sync_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
)");
 $db->Close();
 $out.= "done!\n";
 /* -------------------------------------------------------------------------------------------- */
 $out.= "Fix dynarc-custompricing-extension ...";
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db3 = new AlpaDatabase();

 $db->RunQuery("SELECT archive_id FROM dynarc_archive_extensions WHERE extension_name='custompricing'");
 while($db->Read())
 {
  $db2->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE id='".$db->record['archive_id']."'");
  $db2->Read();
  $db3->RunQuery("ALTER TABLE `dynarc_".$db2->record['tb_prefix']."_custompricing` ADD `discount2` FLOAT NOT NULL , ADD `discount3` FLOAT NOT NULL");
 }
 $db3->Close();
 $db2->Close();
 $db->Close();
 $out.= "done!\n";
 /* -------------------------------------------------------------------------------------------- */
 $out.= "Fix dynarc-pricing-extension ...";
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT archive_id FROM dynarc_archive_extensions WHERE extension_name='pricing'");
 while($db->Read())
 {
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE id='".$db->record['archive_id']."'");
  if($db2->Read())
   $db2->RunQuery("ALTER TABLE dynarc_".$db2->record['tb_prefix']."_items ADD `pricelists` VARCHAR(255) NOT NULL");
  $db2->Close();
 }
 $db->Close();
 $out.= "done!\n";
 /* -------------------------------------------------------------------------------------------- */
 $out.= "Fix gcommercialdocs ...";
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_commercialdocs_items` CHANGE `discount` `discount_1` DECIMAL(10,4) NOT NULL");
 $db->RunQuery("ALTER TABLE `dynarc_commercialdocs_items` ADD `reference_id` INT( 11 ) NOT NULL");
 $db->RunQuery("ALTER TABLE `dynarc_commercialdocs_items` ADD `validity_date` DATE NOT NULL, 
 ADD `charter_datefrom` DATE NOT NULL, 
 ADD `charter_dateto` DATE NOT NULL");
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_commercialdocs_items` ADD `tot_rit_acc` DECIMAL(10,4) NOT NULL, 
 ADD `tot_ccp` DECIMAL(10,4) NOT NULL, 
 ADD `tot_rinps` DECIMAL(10,4) NOT NULL, 
 ADD `tot_enasarco` DECIMAL(10,4) NOT NULL, 
 ADD `tot_netpay` DECIMAL(10,4) NOT NULL");
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_commercialdocs_items` ADD `ext_docref` VARCHAR(64) NOT NULL");
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_commercialdocs_items` ADD `discount_2` DECIMAL(10,4) NOT NULL, 
 ADD `unconditional_discount` DECIMAL(10,4) NOT NULL, 
 ADD `rebate` DECIMAL(10,4) NOT NULL, 
 ADD `tot_goods` DECIMAL(10,4) NOT NULL, 
 ADD `discounted_goods` DECIMAL(10,4) NOT NULL, 
 ADD `tot_expenses` DECIMAL(10,4) NOT NULL, 
 ADD `exp_1_name` VARCHAR(64) NOT NULL, 
 ADD `exp_1_vatid` INT(11) NOT NULL, 
 ADD `exp_1_amount` DECIMAL(10,4) NOT NULL, 
 ADD `exp_2_name` VARCHAR(64) NOT NULL, 
 ADD `exp_2_vatid` INT(11) NOT NULL, 
 ADD `exp_2_amount` DECIMAL(10,4) NOT NULL,  
 ADD `exp_3_name` VARCHAR(64) NOT NULL,  
 ADD `exp_3_vatid` INT(11) NOT NULL,  
 ADD `exp_3_amount` DECIMAL(10,4) NOT NULL,  
 ADD `tot_discount` DECIMAL(10,4) NOT NULL,  
 ADD `vat_1_id` INT(11) NOT NULL,  
 ADD `vat_1_taxable` DECIMAL(10,4) NOT NULL,  
 ADD `vat_1_tax` DECIMAL(10,4) NOT NULL,  
 ADD `vat_2_id` INT(11) NOT NULL,  
 ADD `vat_2_taxable` DECIMAL(10,4) NOT NULL,  
 ADD `vat_2_tax` DECIMAL(10,4) NOT NULL,  
 ADD `vat_3_id` INT(11) NOT NULL,  
 ADD `vat_3_taxable` DECIMAL(10,4) NOT NULL,  
 ADD `vat_3_tax` DECIMAL(10,4) NOT NULL");
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_commercialdocs_items` ADD `stamp` DECIMAL(10,4) NOT NULL");
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_commercialdocs_items` ADD `division` VARCHAR(32) NOT NULL , ADD `ship_subject_id` INT(11) NOT NULL, ADD INDEX (`division`), ADD INDEX (`ship_subject_id`)");
 $db->Close();

 if(!file_exists($_BASE_PATH."etc/commercialdocs/config.php"))
  GShell("mv etc/commercialdocs/config-dist.php etc/commercialdocs/config.php",$_SESSION_ID,$_SHELL_ID);

 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_commercialdocs_items` ADD `cartage` DECIMAL(10,4) NOT NULL , ADD `packing_charges` DECIMAL(10,4) NOT NULL");
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_commercialdocs_items` ADD `renewal_date` DATE NOT NULL , ADD `ren_doc_id` INT NOT NULL , ADD INDEX (`ren_doc_id`)");
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_commercialdocs_elements` ADD `lot` VARCHAR(64) NOT NULL , ADD INDEX (`lot`)");
 $db->Close();


 GShell("commercialdocs fix-totals",$_SESSION_ID,$_SHELL_ID);
 $out.= "done!\n";
 /* -------------------------------------------------------------------------------------------- */
 $out.= "Fix gmart ...";
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db3 = new AlpaDatabase();
 $db->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE archive_type='gmart'");
 while($db->Read())
 {
  $db2->RunQuery("ALTER TABLE `dynarc_".$db->record['tb_prefix']."_items` ADD `gebinde` VARCHAR(32) NOT NULL , ADD `gebinde_code` VARCHAR(32) NOT NULL, ADD `division` VARCHAR(32) NOT NULL");
  $db3->RunQuery("ALTER TABLE `dynarc_".$db->record['tb_prefix']."_items` ADD `item_location` VARCHAR(64) NOT NULL");
 }
 $db3->Close();
 $db2->Close();
 $db->Close();
 $out.= "done!\n";
 /* -------------------------------------------------------------------------------------------- */
 $out.= "Fix gnujiko-desktop-base ...";
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `gnujiko_desktop_pages` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT( 11 ) NOT NULL ,
`gid` INT( 11 ) NOT NULL ,
`_mod` VARCHAR( 3 ) NOT NULL ,
`name` VARCHAR( 32 ) NOT NULL ,
`section_type` VARCHAR( 64 ) NOT NULL ,
`section_xml_params` TEXT NOT NULL ,
`ordering` INT( 11 ) NOT NULL ,
INDEX ( `uid` , `gid` , `_mod` , `ordering` )
)");
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `gnujiko_desktop_modules` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT( 11 ) NOT NULL ,
`gid` INT( 11 ) NOT NULL ,
`_mod` VARCHAR( 3 ) NOT NULL ,
`page_id` INT( 11 ) NOT NULL ,
`module_name` VARCHAR( 64 ) NOT NULL ,
`module_title` VARCHAR( 64 ) NOT NULL ,
`section_id` VARCHAR( 64 ) NOT NULL ,
`ordering` INT( 11 ) NOT NULL ,
`xml_params` TEXT NOT NULL ,
INDEX ( `uid` , `gid` , `_mod` , `page_id` , `section_id` , `ordering` )
)");
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `gnujiko_desktop_connections` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`mod_src` INT( 11 ) NOT NULL ,
`port_src` VARCHAR( 64 ) NOT NULL ,
`mod_dest` INT( 11 ) NOT NULL ,
`port_dest` VARCHAR( 64 ) NOT NULL ,
`page_id` INT( 11 ) NOT NULL ,
INDEX ( `mod_src` , `mod_dest` , `page_id` )
)");
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `gnujiko_desktop_modules` ADD `html_contents` LONGTEXT NOT NULL , ADD `css` TEXT NOT NULL , ADD `javascript` TEXT NOT NULL");
 $db->Close();
 $out.= "done!\n";
 /* -------------------------------------------------------------------------------------------- */
 $out.= "Fix gserv ...";
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT archive_id FROM dynarc_archive_extensions WHERE extension_name='gserv'");
 while($db->Read())
 {
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE id='".$db->record['archive_id']."'");
  $db2->Read();
  $db2->RunQuery("ALTER TABLE `dynarc_".$db2->record['tb_prefix']."_items ADD `service_type` VARCHAR( 32 ) NOT NULL"); // old updated query 29-01-2013
  $db2->RunQuery("ALTER TABLE `dynarc_".$db2->record['tb_prefix']."_items ADD `qty_sold` FLOAT NOT NULL, ADD `units` VARCHAR( 16 ) NOT NULL");
  $db2->Close();
 }
 $db->Close();
 
 $ret = GShell("dynarc install-extension custompricing -ap gserv",$_SESSION_ID,$_SHELL_ID);
 $out.= "done!\n";
 /* -------------------------------------------------------------------------------------------- */
 $out.= "Fix gstore ...";
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `stores` ADD `doc_ext` VARCHAR(8) NOT NULL");
 $db->Close();
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `stores` ADD `ext_refcat` INT(11) NOT NULL, ADD `gid` INT(11) NOT NULL");
 $db->Close();
 $out.= "done!\n";
 /* -------------------------------------------------------------------------------------------- */
 $out.= "Fix idoc-config ...";
 GShell("dynarc install-extension idoc -ap idoc",$_SESSION_ID,$_SHELL_ID);
 $out.= "done!\n";
 /* -------------------------------------------------------------------------------------------- */
 $out.= "Fix paymentmodes-config ...";
 $db = new AlpaDatabase();
 $fields = $db->FieldsInfo("payment_modes");
 if(!$fields['date_terms'])
 {
  /* remove old fields */
  $db->RunQuery("ALTER TABLE `payment_modes` DROP `x_perc1`, DROP `x_perc2`, DROP `x_perc3`, DROP `x_perc4`, DROP `x_perc5`, DROP `x_perc6`, DROP `x_daynum1`, DROP `x_daynum2`, DROP `x_daynum3`, DROP `x_daynum4`, DROP `x_daynum5`, DROP `x_daynum6`, DROP `x_daydoc1`, DROP `x_daydoc2`, DROP `x_daydoc3`, DROP `x_daydoc4`, DROP `x_daydoc5`, DROP `x_daydoc6`");
  /* insert new fields */
  $db->RunQuery("ALTER TABLE `payment_modes` ADD `date_terms` TINYINT( 1 ) NOT NULL , ADD `deadlines` TEXT NOT NULL , ADD `day_after` TINYINT( 1 ) NOT NULL ");
  $db->Close();

  /* auto-update payment modes to new standard */
  $db = new AlpaDatabase();
  $db2 = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM payment_modes WHERE 1");
  while($db->Read())
  {
   $ret = GShell("accounting paymentmodeinfo `".$db->record['name']."`", $_SESSION_ID, $_SHELL_ID);
   $info = $ret['outarr'];
   $db2->RunQuery("UPDATE payment_modes SET type='".$info['type']."',date_terms='".$info['date_terms']."',deadlines='"
	.implode(",",$info['terms'])."',day_after='".$info['day_after']."' WHERE id='".$db->record['id']."'");
  }
  $db2->Close();
 }
 $db->Close();
 $out.= "done!\n";
 /* -------------------------------------------------------------------------------------------- */
 $out.= "Fix rubrica ...";
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_rubrica_items` ADD `distance` FLOAT NOT NULL");
 $db->Close();
 GShell("dynarc install-extension references -ap rubrica",$_SESSION_ID,$_SHELL_ID);
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_rubrica_items` ADD `fidelitycard` VARCHAR(32) NOT NULL , ADD INDEX (`fidelitycard`)");
 $db->Close();
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_rubrica_items` ADD `extranotes` TEXT NOT NULL");
 $db->Close();
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_rubrica_items` ADD `agent_id` INT(11) NOT NULL , ADD INDEX (`agent_id`)");
 $db->Close();
 $out.= "done!\n";
 /* -------------------------------------------------------------------------------------------- */
 $out.= "Fix todo-module ...";
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_todo_items` ADD `status` TINYINT(1) NOT NULL, ADD `priority` TINYINT(1) NOT NULL, ADD `date_from` DATETIME NOT NULL, ADD `date_to` DATETIME NOT NULL, ADD `all_day` TINYINT(1) NOT NULL");
 $db->Close();
 GShell("dynarc install-extension cronevents -ap todo", $_SESSION_ID, $_SHELL_ID);
 GShell("dynarc install-extension cronrecurrence -ap todo", $_SESSION_ID, $_SHELL_ID);
 GShell("dynarc edit-archive -ap todo -launcher `gframe -f todo/edit -params 'id=%d&showeditor=true'`", $_SESSION_ID, $_SHELL_ID);
 $out.= "done!\n";
 /* -------------------------------------------------------------------------------------------- */

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_dbinfo($args, $sessid, $shellid)
{
 global $_DATABASE_NAME;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   /* ACTIONS */
   case 'showtables' : return system_dbinfo_showTables($args, $sessid, $shellid); break;
   case 'getfields' : return system_dbinfo_getFields($args, $sessid, $shellid); break;
   case 'showvariables' : case 'showvars' : case 'getvars' : return system_dbinfo_showVariables($args, $sessid, $shellid); break;
   case 'runqry' : return system_dbinfo_runQuery($args, $sessid, $shellid); break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='".$_DATABASE_NAME."'");
 $db->Read();
 $count = $db->record[0];
 $db->Close();

 $out = "Informations about database ".$_DATABASE_NAME."\n";
 $out.= "Num of tables: ".$count."\n";

 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_dbinfo_showTables($args, $sessid, $shellid)
{
 global $_DATABASE_NAME;

 $out = "List of tables into database ".$_DATABASE_NAME."\n";
 $db = new AlpaDatabase();
 $db->RunQuery("SHOW TABLES");
 $idx = 1;
 while($db->Read())
 {
  $out.= $idx.". ".$db->record[0]."\n";
  $idx++;
 }
 $db->Close();
 
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_dbinfo_getFields($args, $sessid, $shellid)
{
 global $_DATABASE_NAME;

 for($c=2; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-tb' : case '-table' : {$tb=$args[$c+1]; $c++;} break;
  }

 if(!$tb)
  return array("message"=>"You must specify a table. (with: -tb TABLE_NAME)", "error"=>"INVALID_TABLE");

 $out = "List of fields into table ".$tb."\n";
 $out.= "<table border='0'>";
 $out.= "<tr><th>FIELD NAME</th><th>TYPE</th><th>LEN</th><th>FLAGS</th></tr>";

 $db = new AlpaDatabase();
 $list = $db->GetFields($tb);
 for($c=0; $c < count($list); $c++)
  $out.= "<tr><td>".$list[$c][name]."</td><td>".$list[$c][type]."</td><td>".$list[$c][len]."</td><td>".$list[$c][flags]."</td></tr>";
 $out.= "</table>";
 $out.= "<br/>Num of columns: ".count($list);
 $db->Close();
 
 return array("htmloutput"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_dbinfo_showVariables($args, $sessid, $shellid)
{
 global $_DATABASE_NAME;

 $out = "List of MySQL variables\n";
 $out.= "<table border='0'>";
 $out.= "<tr><th>Name</th><th>Value</th></tr>";

 $db = new AlpaDatabase();
 $db->RunQuery("SHOW VARIABLES");
 while($db->Read())
 {
  $out.= "<tr><td>".$db->record[0]."</td><td>".htmlspecialchars($db->record[1], ENT_QUOTES, 'UTF-8')."</td></tr>\n";
 }
 $out.= "</table>";
 $db->Close();
 
 return array("htmloutput"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_dbinfo_runQuery($args, $sessid, $shellid)
{
 global $_DATABASE_NAME;

 for($c=2; $c < count($args); $c++)
  switch($args[$c])
  {
   default : $qry = $args[$c]; break;
  }

 $out = "";
 if(!$qry)
  return array("message"=>"You must specify the sql query", "error"=>"INVALID_QRY");

 $db = new AlpaDatabase();
 if(!$db->RunQuery($qry))
  return array("message"=>"Error: ".$db->Error, "error"=>"QUERY_ERROR");
 else
 {
  $out.= "Query success!\n";
  $num_fields = @mysqli_num_fields($db->lastQueryResult);
  $fields = array();

  $out.= "Returned results:\n";
  $out.= "<table border='1' rules='rows' cellspacing='0' cellpadding='2'>";
  $out.= "<tr>";
  for($c=0; $c < $num_fields; $c++)
  {
   $finfo = @mysqli_fetch_field_direct($db->lastQueryResult,$c);
   $fieldName = $finfo->name;
   $out.= "<th style='text-align:left;padding-left:5px;padding-right:5px'>".$fieldName."</th>";
   $fields[] = $fieldName;
  }
  $out.= "</tr>";

  while($db->Read())
  {
   $out.= "<tr>";
   for($c=0; $c < count($fields); $c++)
	$out.= "<td>".($db->record[$fields[$c]] ? $db->record[$fields[$c]] : '&nbsp;')."</td>";
   $out.= "</tr>";
  }

  $out.= "</table>";
 }
 $db->Close();

 return array("htmloutput"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_fixVatNumbers($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;
 include_once($_BASE_PATH."include/vatnumbervalidator.php");

 $out = "";
 $outArr = array("fixed"=>array(), "unfixed"=>array(), "tot_blanks"=>0);

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--verbose' : $verbose=true; break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_rubrica_items WHERE trash='0' AND taxcode='' AND vatnumber=''");
 $db->Read();
 if($db->record[0])
 {
  $outArr['tot_blanks'] = $db->record[0];
  $out.= "There are ".$db->record[0]." contacts without vatnumber and without taxcode.\n";
 }
 $db->Close();

 $out.= "start repair...";
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_rubrica_items WHERE trash='0' AND (taxcode!='' OR vatnumber!='')");
 $db->Read();
 $count = $db->record[0];

 $interface = array("name"=>"progressbar","steps"=>$count);
 gshPreOutput($shellid,"Estimated contacts to scan: ".$count, "ESTIMATION", "", "PASSTHRU", $interface);

 $db->RunQuery("SELECT id,code_str,name,taxcode,vatnumber FROM dynarc_rubrica_items WHERE trash='0' AND (taxcode!='' OR vatnumber!='') ORDER BY name ASC");
 while($db->Read())
 {
  $a = array("id"=>$db->record['id'], "code_str"=>$db->record['code_str'], "name"=>$db->record['name'], "taxcode"=>$db->record['taxcode'],
	"vatnumber"=>$db->record['vatnumber']);

  gshPreOutput($shellid, "Scan for: <i>".($a['name'] ? $a['name'] : 'untitled')."</i>","PROGRESS", "");

  $taxcode = $db->record['taxcode'];
  $vatnumber = $db->record['vatnumber'];
  $equals = false;
  if($taxcode && $vatnumber && ($taxcode == $vatnumber))
   $equals = true;
  if($vatnumber)
  {
   // validate vat number
   if(!validateVatNumber($vatnumber))
   {
	if(substr($vatnumber,0,1) == "-")
	 $vatnumber = str_replace("-",0,$vatnumber);
	if(strlen($vatnumber) == 10)
	 $vatnumber = "0".$vatnumber;
	if(validateVatNumber($vatnumber))
	{
	 if($equals)
	  $db2->RunQuery("UPDATE dynarc_rubrica_items SET taxcode='".$vatnumber."',vatnumber='".$vatnumber."' WHERE id='".$db->record['id']."'");
	 else
	  $db2->RunQuery("UPDATE dynarc_rubrica_items SET vatnumber='".$vatnumber."' WHERE id='".$db->record['id']."'");
	 $outArr['fixed'][] = $a;
	}
	else
	 $outArr['unfixed'][] = $a;
   }
  }
 }
 $db2->Close();
 $db->Close();
 if(count($outArr['fixed']))
  $out.= "done!\n";
 else if(count($outArr['unfixed']))
  $out.= "failed!\n";

 if(count($outArr['fixed']))
  $out.= count($outArr['fixed'])." contacts has been fixed.\n";
 if(count($outArr['unfixed']))
  $out.= count($outArr['unfixed'])." contacts have not been able to correct therefore were wrong.\n"; 

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_dbimport($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SRC_DB_HOST, $_SRC_DB_NAME, $_SRC_DB_USER, $_SRC_DB_PASSWD;

 /*$sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");*/

 $overwrite = false;
 $maintainIndex = false;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-host' : case '-database-host' : case '-db-host' : {$_SRC_DB_HOST=$args[$c+1]; $c++;} break;
   case '-name' : case '-database-name' : case '-db-name' : {$_SRC_DB_NAME=$args[$c+1]; $c++;} break;
   case '-user' : case '-database-user' : case '-db-user' : {$_SRC_DB_USER=$args[$c+1]; $c++;} break;
   case '-pass' : case '-passwd' : case '-password' : case '-database-password' : case '-db-password' : {$_SRC_DB_PASSWD=$args[$c+1]; $c++;} break;

   case '-app' : {$application=$args[$c+1]; $c++;} break;
   //case '--all' : $importAll=true; break;
   case '--clear' : case '--clean' : case '--empty' : case '--overwrite' : $overwrite=true; break;
   case '--maintain-index' : $maintainIndex=true; break;
  }

 /*if($truncateTable)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("TRUNCATE TABLE `payment_modes`");
  $db->Close();
 }*/

 /* DATABASE CONNECTION TEST */
 $out = "Connecting to database ".$_SRC_DB_NAME."...";
 $db = new AlpaDatabase($_SRC_DB_HOST, $_SRC_DB_USER, $_SRC_DB_PASSWD, $_SRC_DB_NAME);
 if($db->Error)
  return array('message'=> $out."failed!\nUnable to connect to database ".$_SRC_DB_NAME."\nMySQL error:".$db->Error, "error"=>"DATABASE_CONNECT_FAIELED");
 $out.= "done\n";
 $db->Close();

 if($application)
 {
  switch($application)
  {
   case 'rubrica' : return system_dbimport_rubrica($sessid, $shellid, $_SRC_DB_HOST, $_SRC_DB_NAME, $_SRC_DB_USER, $_SRC_DB_PASSWD, $overwrite, $maintainIndex); break;

   case 'commercialdocs' : return system_dbimport_commercialdocs($sessid, $shellid, $_SRC_DB_HOST, $_SRC_DB_NAME, $_SRC_DB_USER, $_SRC_DB_PASSWD, $overwrite, $maintainIndex); break;

   default : return array("message"=>"Error: application ".$application." does not exists!", "error"=>"APPLICATION_DOES_NOT_EXISTS");
  }
 }
 else
 {
  /* TODO: import all applications */
 } 
  
 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_dbimport_rubrica($sessid, $shellid, $_SRC_DB_HOST, $_SRC_DB_NAME, $_SRC_DB_USER, $_SRC_DB_PASSWD, $overwrite, $maintainIndex)
{
 $out = "";
 $outArr = array();

 $_CATEGORIES = array();

 $db = new AlpaDatabase($_SRC_DB_HOST, $_SRC_DB_USER, $_SRC_DB_PASSWD, $_SRC_DB_NAME);
 if($db->Error)
  return array('message'=> "Error: unable to connect to database ".$_SRC_DB_NAME."\nMySQL error:".$db->Error, "error"=>"DATABASE_CONNECT_FAIELED");
 
 /* Get all categories */
 $db->RunQuery("SELECT * FROM dynarc_rubrica_categories WHERE trash='0' AND parent_id='0' ORDER BY ordering ASC");
 while($db->Read())
 {
  $_CATEGORIES[] = array("id"=>$db->record['id'], "tag"=>$db->record['tag'], "code"=>$db->record['code'], "name"=>$db->record['name']);
 }
 $out.= "There are ".count($_CATEGORIES)." categories to import.\n";

 /* Get items count */
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_rubrica_items WHERE trash='0'");
 if(!$db->Read() || $db->Error)
  return array("message"=>"MySQL error: ".$db->Error, "error"=>"MYSQL_ERROR");
 $itemsCount = $db->record[0];

 $out.= "There are ".$itemsCount." contacts to import.\n";

 $db->Close();

 /* PREPARING TO IMPORT */
 $interface = array("name"=>"progressbar","steps"=>$itemsCount);
 gshPreOutput($shellid,"Import Rubrica. please wait!", "ESTIMATION", "", "PASSTHRU", $interface);

 if($overwrite)
 {
  $out.= "Empty all from archive Rubrica...\n";
  $tables = array("banks","categories","contacts","items","references","idoccatprop","idocitmprop");
  $db = new AlpaDatabase();
  for($c=0; $c < count($tables); $c++)
   $db->RunQuery("TRUNCATE TABLE `dynarc_rubrica_".$tables[$c]."`");
  $db->Close();
  $out.= "done!\n";
 }

 if($overwrite)
 {
  /* IMPORT CATEGORIES */
  $out.= "Import categories...";
  for($c=0; $c < count($_CATEGORIES); $c++)
  {
   $catInfo = $_CATEGORIES[$c];
   $ret = GShell("dynarc new-cat -ap rubrica".($maintainIndex ? " -id '".$catInfo['id']."'" : "")." -name `".$catInfo['name']."` -tag `"
	.$catInfo['tag']."` -code `".$catInfo['code']."` -group rubrica -perms 666 --publish",$sessid, $shellid);
   if($ret['error'])
	return array("message"=>$out."failed!\n".$ret['message'], "error"=>$ret['error']);
  }
  $out.= "done!\n";
 }

 $out.= "Import items..."; 
 /* IMPORT ITEMS */
 $db = new AlpaDatabase($_SRC_DB_HOST, $_SRC_DB_USER, $_SRC_DB_PASSWD, $_SRC_DB_NAME);
 if($db->Error)
  return array('message'=> "Error: unable to connect to database ".$_SRC_DB_NAME."\nMySQL error:".$db->Error, "error"=>"DATABASE_CONNECT_FAIELED");

 $db->RunQuery("SELECT * FROM dynarc_rubrica_items WHERE trash='0' ORDER BY id ASC");
 while($db->Read())
 {
  gshPreOutput($shellid, "Import: <i>".$db->record['name']."</i>","PROGRESS", "");

  $ret = GShell("dynarc new-item -ap rubrica".($maintainIndex ? " -id '".$db->record['id']."'" : "")." -group rubrica -perms 666 -cat '"
	.$db->record['cat_id']."' -name `".$db->record['name']."` -code-str `".$db->record['code_str']."` -extset `rubricainfo.iscompany='"
	.$db->record['iscompany']."',taxcode='".$db->record['taxcode']."',vatnumber='".$db->record['vatnumber']."',paymentmode='"
	.$db->record['paymentmode']."',pricelist='".$db->record['pricelist_id']."',distance='".$db->record['distance']."',fidelitycard='"
	.$db->record['fidelitycard']."'`",$sessid,$shellid);
  if($ret['error'])
   return $ret;

  $itemInfo = $ret['outarr'];

  /* import contacts */
  $contactDB = new AlpaDatabase($_SRC_DB_HOST, $_SRC_DB_USER, $_SRC_DB_PASSWD, $_SRC_DB_NAME);
  $contactDB->RunQuery("SELECT * FROM dynarc_rubrica_contacts WHERE item_id='".$db->record['id']."' ORDER BY id ASC");
  while($contactDB->Read())
  {
   $rec = $contactDB->record;
   $ret = GShell("dynarc edit-item -ap rubrica -id '".$itemInfo['id']."' -extset `contacts.label=\"".$rec['label']."\",name=\""
	.$rec['name']."\",address=\"".$rec['address']."\",city=\"".$rec['city']."\",zipcode='".$rec['zipcode']."',province='"
	.$rec['province']."',countrycode='".$rec['countrycode']."',phone='".$rec['phone']."',phone2='".$rec['phone2']."',fax='"
	.$rec['fax']."',cell='".$rec['cell']."',email='".$rec['email']."',email2='".$rec['email2']."',email3='".$rec['email3']."',skype='"
	.$rec['skype']."'`",$sessid,$shellid);
   if($ret['error'])
    return $ret;
  }

  /* import banks */
  $bankDB = new AlpaDatabase($_SRC_DB_HOST, $_SRC_DB_USER, $_SRC_DB_PASSWD, $_SRC_DB_NAME);
  $bankDB->RunQuery("SELECT * FROM dynarc_rubrica_banks WHERE item_id='".$db->record['id']."' ORDER BY id ASC");
  while($bankDB->Read())
  {
   $rec = $bankDB->record;
   $ret = GShell("dynarc edit-item -ap rubrica -id '".$itemInfo['id']."' -extset `banks.holder=\"".$rec['holder']."\",name=\""
	.$rec['name']."\",abi='".$rec['abi']."',cab='".$rec['cab']."',cin='".$rec['cin']."',cc='".$rec['cc']."',iban='".$rec['iban']."',isdefault='"
	.$rec['isdefault']."'`",$sessid,$shellid);
   if($ret['error'])
    return $ret;
  }

  /* import references */
  $refDB = new AlpaDatabase($_SRC_DB_HOST, $_SRC_DB_USER, $_SRC_DB_PASSWD, $_SRC_DB_NAME);
  $refDB->RunQuery("SELECT * FROM dynarc_rubrica_references WHERE item_id='".$db->record['id']."' ORDER BY id ASC");
  while($refDB->Read())
  {
   $rec = $refDB->record;
   $ret = GShell("dynarc edit-item -ap rubrica -id '".$itemInfo['id']."' -extset `references.name=\"".$rec['name']."\",type=\""
	.$rec['reftype']."\",phone='".$rec['phone']."',email='".$rec['email']."'`",$sessid,$shellid);
   if($ret['error'])
    return $ret;  
  }
 }
 $db->Close();
 $out.= "done";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_dbimport_commercialdocs($sessid, $shellid, $_SRC_DB_HOST, $_SRC_DB_NAME, $_SRC_DB_USER, $_SRC_DB_PASSWD, $overwrite, $maintainIndex)
{
 $out = "";
 $outArr = array();

 /* List of local categories */
 $ret = GShell("dynarc cat-list -ap commercialdocs",$sessid,$shellid);
 if($ret['error']) return $ret;
 $locCatList = $ret['outarr'];

 $_CAT_BY_TAG = array();
 $db = new AlpaDatabase($_SRC_DB_HOST, $_SRC_DB_USER, $_SRC_DB_PASSWD, $_SRC_DB_NAME);
 if($db->Error)
  return array('message'=> "Error: unable to connect to database ".$_SRC_DB_NAME."\nMySQL error:".$db->Error, "error"=>"DATABASE_CONNECT_FAIELED");
 
 /* Get all categories */
 $db->RunQuery("SELECT * FROM dynarc_commercialdocs_categories WHERE trash='0' AND parent_id='0' AND tag!='' ORDER BY ordering ASC");
 while($db->Read())
 {
  $_CAT_BY_TAG[$db->record['tag']] = array("id"=>$db->record['id'], "tag"=>$db->record['tag'], "code"=>$db->record['code'], "name"=>$db->record['name']);
 }
 
 /* Get count */
 $count = 0;
 $_CATS = array();
 for($c=0; $c < count($locCatList); $c++)
 {
  $catInfo = $locCatList[$c];
  if($_CAT_BY_TAG[$catInfo['tag']])
  {
   $catId = $_CAT_BY_TAG[$catInfo['tag']]['id'];
   $db->RunQuery("SELECT COUNT(*) FROM dynarc_commercialdocs_items WHERE cat_id='".$catId."' AND trash='0' ORDER BY id ASC");
   $db->Read();
   if($db->record[0])
    $_CATS[] = array("tag"=>$catInfo['tag'], "locid"=>$catInfo['id'], "id"=>$catId);
   $count+= $db->record[0];
  }
 }
 $db->Close();

 $out.= "There are ".$count." documents to import.\n";
 //----------------------------------------------------------------------------------------------//
 /* PREPARING TO IMPORT */
 $interface = array("name"=>"progressbar","steps"=>$count);
 gshPreOutput($shellid,"Import CommercialDocs. please wait!", "ESTIMATION", "", "PASSTHRU", $interface);

 if($overwrite)
 {
  $out.= "Empty all from archive commercialdocs...\n";
  $tables = array("elements","items","mmr","predefmsg");
  $db = new AlpaDatabase();
  for($c=0; $c < count($tables); $c++)
   $db->RunQuery("TRUNCATE TABLE `dynarc_commercialdocs_".$tables[$c]."`");
  $db->Close();
  $out.= "done!\n";
 }

 for($c=0; $c < count($_CATS); $c++)
 {
  $catId = $_CATS[$c]['id'];
  $locCatId = $_CATS[$c]['locid'];
  $catTag = $_CATS[$c]['tag'];
  switch($catTag)
  {
   case "PREEMPTIVES" : $group = "commdocs-preemptives"; break;
   case "ORDERS" : $group = "commdocs-orders"; break;
   case "DDT" : $group = "commdocs-ddt"; break;
   case "INVOICES" : $group = "commdocs-invoices"; break;
   case "VENDORORDERS" : $group = "commdocs-vendororders"; break;
   case "PURCHASEINVOICES" : $group = "commdocs-purchaseinvoices"; break;
   case "CREDITSNOTE" : $group = "commdocs-creditsnote"; break;
   case "DEBITSNOTE" : $group = "commdocs-debitsnote"; break;
   case "RECEIPTS" : $group = "commdocs-receipts"; break;
   case "AGENTINVOICES" : $group = "commdocs-agentinvoices"; break;
   case "INTERVREPORTS" : $group = "commdocs-intervreports"; break;
   case "PAYMENTNOTICE" : $group = "commdocs-paymentnotice"; break;
  }

  $db = new AlpaDatabase($_SRC_DB_HOST, $_SRC_DB_USER, $_SRC_DB_PASSWD, $_SRC_DB_NAME);
  $db->RunQuery("SELECT * FROM dynarc_commercialdocs_items WHERE cat_id='".$catId."' AND trash='0' ORDER BY id ASC");
  while($db->Read())
  {
   gshPreOutput($shellid, "Import: <i>".$db->record['name']."</i>","PROGRESS", "");
   $ret = GShell("dynarc new-item -ap commercialdocs".($maintainIndex ? " -id '".$db->record['id']."'" : "")." -group '".$group."' -perms 666 -cat '"
	.$db->record['cat_id']."' -name `".$db->record['name']."` -desc `".$db->record['desc']."` -code-str `".$db->record['code_str']."` -code-ext `"
	.$db->record['code_ext']."` -ctime '".$db->record['ctime']."' -alias ' ' -extset `cdinfo."
	.($db->record['subject_id'] ? "subjectid='".$db->record['subject_id']."'" : "subject=\"".$db->record['subject_name']."\"")
	.",referenceid='".$db->record['reference_id']."',agent_id='".$db->record['agent_id']."',tag='".$db->record['tag']."',division='"
	.$db->record['division']."',pricelist='".$db->record['pricelist_id']."',paymentmode='".$db->record['payment_mode']."',banksupport='"
	.$db->record['bank_support']."',ship-recp=\"".$db->record['ship_recp']."\",ship-addr=\"".$db->record['ship_addr']."\",ship-city=\""
	.$db->record['ship_city']."\",ship-zip='".$db->record['ship_zip']."',ship-prov='".$db->record['ship_prov']."',ship-cc='"
	.$db->record['ship_cc']."',trans-method=\"".$db->record['trans_method']."\",trans-shipper=\"".$db->record['trans_shipper']."\",trans-numplate='"
	.$db->record['trans_numplate']."',trans-causal=\"".$db->record['trans_causal']."\",trans-datetime='"
	.$db->record['trans_datetime']."',trans-aspect=\"".$db->record['trans_aspect']."\",trans-num='".$db->record['trans_num']."',trans-weight='"
	.$db->record['trans_weight']."',trans-freight=\"".$db->record['trans-freight']."\",validity-date='"
	.$db->record['validity_date']."',charter-datefrom='".$db->record['charter_datefrom']."',charter-dateto='"
	.$db->record['charter_dateto']."',extdocref=\"".$db->record['ext_docref']."\",discount1='".$db->record['discount_1']."',discount2='"
	.$db->record['discount_2']."',uncondisc='".$db->record['unconditional_discount']."',rebate='".$db->record['rebate']."',stamp='"
	.$db->record['stamp']."',cartage='".$db->record['cartage']."',packing-charges='".$db->record['packing_charges']."',collection-charges='"
	.$db->record['collection_charges']."',status='".$db->record['status']."',conv-doc='".$db->record['conv_doc_id']."',group-doc='"
	.$db->record['group_doc_id']."',payment-date='".$db->record['payment_date']."'`", $sessid, $shellid);

   if($ret['error'])
    return $ret;

   $docInfo = $ret['outarr'];

   /* import elements */
   $elemDB = new AlpaDatabase($_SRC_DB_HOST, $_SRC_DB_USER, $_SRC_DB_PASSWD, $_SRC_DB_NAME);
   $elemDB->RunQuery("SELECT * FROM dynarc_commercialdocs_elements WHERE item_id='".$db->record['id']."' ORDER BY id ASC");
   while($elemDB->Read())
   {
    $rec = $elemDB->record;
    if($rec['elem_type'] == "note")
	 $qry = ".type='note',desc='''".$rec['description']."''',ordering='".$rec['ordering']."'";
    else
	{
	 $qry = ".type='custom',code='".$rec['code']."',sn=\"".$rec['serial_number']."\",lot=\"".$rec['lot']."\",name=\"".$rec['name']."\",qty='"
		.$rec['qty']."',extraqty='".$rec['extra_qty']."',units='".$rec['units']."',price='".$rec['price']."',priceadjust='"
		.$rec['price_adjust']."',discount='".($rec['discount_perc'] ? $rec['discount_perc']."%" : $rec['discount_inc'])."',discount2='"
		.$rec['discount2']."',discount3='".$rec['discount3']."',vatrate='".$rec['vat_rate']."',vatid='".$rec['vat_id']."',vattype='"
		.$rec['vat_type']."',pricelistid='".$rec['pricelist_id']."',ordering='".$rec['ordering']."'";
	}

    $ret = GShell("dynarc edit-item -ap commercialdocs -id '".$docInfo['id']."' -extset `cdelements".$qry."`", $sessid, $shellid);
    if($ret['error'])
     return $ret;

	$ret = GShell("commercialdocs updatetotals -id '".$docInfo['id']."'",$sessid,$shellid);
   }
   $elemDB->Close();
  }
  $db->Close();
 }

 /* FIX MMR */
 $ret = GShell("commercialdocs fix-mmr --overwrite-all",$sessid,$shellid);

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_cacheClean($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
  }

 $count = 0;
 $errors = 0;

 // REMOVE FILES
 /*$ret = GShell("ls tmp/ -list",$sessid,$shellid);
 if($ret['error']) return $ret;
 $list = $ret['outarr'];

 for($c=0; $c < count($list); $c++)
 {
  $file = $list[$c];
  if($file == "tmp/index.php") continue;
  if($file == "tmp/packages/index.php") continue;
  
  $ret = GShell("rm `".$file."`",$sessid,$shellid);
  if($ret['error'])
  {
   $out.= "Warning: unable to delete file ".$file.".\n".$ret['error'];
   $errors++;
  }
  else
   $count++;
 }*/

 /* REMOVE SHELL DIRECTORIES */
 $ret = GShell("ls tmp/ -d",$sessid,$shellid);
 if($ret['error']) return $ret;
 $list = $ret['outarr']['dirs'];

 for($c=0; $c < count($list); $c++)
 {
  $dir = $list[$c]['path'];
  //if($dir == "tmp/packages") continue;
  if(strpos($dir, "shell-") === false) continue;
  $ret = GShell("rm `".$dir."`",$sessid,$shellid);
  if($ret['error'])
  {
   $out.= "Warning: unable to delete directory ".$dir.".\n".$ret['error'];
   $errors++;
  }
  $count++;
 }

 if($errors)
  $out.= "\nThe cache has not been completely emptied because there are ".$errors." files that I could not remove.";
 else
  $out.= "done! Was removed ".$count." obsolete files.";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_checkForUpdates($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_SHELL_CMD_PATH;

 $out = "";
 $outArr = array('outdated'=>array());
 $_REPOSITORY_LIST = array();
 $_ONLINE_VER = array(); // lista delle versioni online per ciascun pacchetto
 $_INSTALLED_VER = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-force' : $force=true; break;
   case '--set-last-update' : {$setLastUpdate=$args[$c+1]; $c++;} break;

   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 $oneWeek = 604800;
 $now = time();

 /* VERIFY IF ALREADY UPDATED OR NOTIFIED */
 if(!$force)
 {
  /*if($_COOKIE['APM_LAST_UPDATE'])
  {
   $lastUpdateDate = strtotime($_COOKIE['APM_LAST_UPDATE']);
   if(($lastUpdateDate - $now) < $oneWeek)
   {
	$out = "Last updates on: ".date('d/m/Y',$lastUpdateDate)."\n";
	$out.= $_COOKIE['APM_OUTDATES_COUNT'] ? "There are ".$_COOKIE['APM_OUTDATES_COUNT']." packages outdated." : "All packages are already updated to latest version.";
	$outArr = array('last_update'=>$_COOKIE['APM_LAST_UPDATE'], 'outdates_count'=>$_COOKIE['APM_OUTDATES_COUNT']);
	return array('message'=>$out, 'outarr'=>$outArr);
   }
  }
  else if($_COOKIE['APM_LAST_NOTIFY'])
  {
   $lastNotifyDate = strtotime($_COOKIE['APM_LAST_NOTIFY']);
   if(($lastNotifyDate - $now) < $oneWeek)
   {
	$out = "Last notify on: ".date('d/m/Y',$lastNotifyDate)."\n";
	$out.= $_COOKIE['APM_OUTDATES_COUNT'] ? "There are ".$_COOKIE['APM_OUTDATES_COUNT']." packages outdated." : "All packages are already updated to latest version.";
	$outArr = array('last_notify'=>$_COOKIE['APM_LAST_NOTIFY'], 'outdates_count'=>$_COOKIE['APM_OUTDATES_COUNT']);
	return array('message'=>$out, 'outarr'=>$outArr);
   }
  }
  else
  {*/
   $apmCache = array();
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT var,val FROM gnujiko_apm_cache WHERE 1");
   while($db->Read())
	$apmCache[$db->record['var']] = $db->record['val'];
   $db->Close();

   if($apmCache['last_update'])
   {
    $lastUpdateDate = strtotime($apmCache['last_update']);
	//setcookie("APM_LAST_UPDATE",$apmCache['last_update'],strtotime("+1 week"));
	//setcookie("APM_OUTDATES_COUNT",$apmCache['outdates_count'],strtotime("+1 week"));
    if(($now - $lastUpdateDate) < $oneWeek)
    {
	 $out = "Last updates on: ".date('d/m/Y',$lastUpdateDate)."\n";
	 $out.= $apmCache['outdates_count'] ? "There are ".$apmCache['outdates_count']." packages outdated." : "All packages are already updated to latest version.";
	 $outArr = array('last_update'=>$apmCache['last_update'], 'outdates_count'=>$apmCache['outdates_count']);
	 return array('message'=>$out, 'outarr'=>$outArr);
    }
   }
   else if($apmCache['last_notify'])
   {
    $lastNotifyDate = strtotime($apmCache['last_notify']);
	//setcookie("APM_LAST_NOTIFY",$apmCache['last_notify'],strtotime("+1 week"));
	//setcookie("APM_OUTDATES_COUNT",$apmCache['outdates_count'],strtotime("+1 week"));
    if(($now - $lastNotifyDate) < $oneWeek)
    {
	 $out = "Last notify on: ".date('d/m/Y',$lastNotifyDate)."\n";
	 $out.= $apmCache['outdates_count'] ? "There are ".$apmCache['outdates_count']." packages outdated." : "All packages are already updated to latest version.";
	 $outArr = array('last_notify'=>$apmCache['last_notify'], 'outdates_count'=>$apmCache['outdates_count']);
	 return array('message'=>$out, 'outarr'=>$outArr);
    }
   }
  //}
 }
 /* EOF - VERIFY IF ALREADY UPDATED OR NOTIFIED */

 $sessInfo = sessionInfo($sessid);

 /* VERIFY IF THE USER CAN RUN SUDO COMMANDS */
 if($sessInfo['uname'] != 'root')
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT run_sudo_commands FROM gnujiko_user_privileges WHERE uid='".$sessInfo['uid']."'");
  $db->Read();
  if(!$db->record['run_sudo_commands'])
  {
   $db->Close();
   //setcookie("APM_LAST_NOTIFY",date('Y-m-d'),strtotime("+1 week"));
   //setcookie("APM_OUTDATES_COUNT",0,strtotime("+1 week"));
   return array('message'=>'You are not allowed to execute this command.');
  }
  $db->Close(); 
 }

 if($setLastUpdate == "now")
  $setLastUpdate = date('Y-m-d');

 include_once($_BASE_PATH."var/lib/xmllib.php");
 include_once($_BASE_PATH.$_SHELL_CMD_PATH."apm.php");

 /* GET REPOSITORY */
 $repoFile = $_BASE_PATH."etc/apm/sources.list";
 $handle = @fopen($repoFile, "r");
 if(!$handle)
  return array("message"=>"Unable to read repository list","error"=>"REPOSITORY_LIST_ERROR");
 
 while(!feof($handle)) 
 {
  $line = fgets($handle, 4096);
  $line = ltrim($line);
  if( ($line[0] == "#") || ($line == "") || ($line == " ") )
   continue;
  $x = explode(" ",$line);
  $url = rtrim(trim($x[0]), "/");
  // detect type //
  if(substr($url,0,7) == "http://")
   $type = "url";
  else if(substr($url,0,6) == "ftp://")
   $type = "ftp";
  else
   $type = "media";

  $_REPOSITORY_LIST[] = array('type'=>$type, 'url'=>$url, 'ver'=>trim($x[1]), 'section'=>trim($x[2]));
 }
 @fclose($handle);

 /* PER OGNI REPOSITORY PRELEVO IL FILE versionlist.xml */
 for($c=0; $c < count($_REPOSITORY_LIST); $c++)
 {
  $repoInfo = $_REPOSITORY_LIST[$c];
  $xml = new GXML();
  $url = $repoInfo['url']."/dists/".$repoInfo['ver']."/".$repoInfo['section']."/versionlist.xml";
  if(!$xml->LoadFromString(apm_http_get($url)))
   return array('message'=>"Unable to get XML file ".$url, 'error'=>'RETRIEVE_XML_FILE_FAILED');
  
  $packages = $xml->GetElementsByTagName('package');
  for($i=0; $i < count($packages); $i++)
   $_ONLINE_VER[$packages[$i]->getString('name')] = $packages[$i]->getString('version');
 }

 /* LISTA DEI PACCHETTI INSTALLATI */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT name,installed_version FROM gnujiko_packages WHERE installed_version!=''");
 while($db->Read())
 {
  $_INSTALLED_VER[$db->record['name']] = $db->record['installed_version'];
 }
 $db->Close();

 /* CONFRONTO VERSIONI TRA I PACCHETTI INSTALLATI */
 if($verbose)
  $out.= "<table border='0'><tr><th>PACKAGE</th><th>INST. VER.</th><th>ONLINE VER.</th></tr>";
 reset($_INSTALLED_VER);
 while(list($pkg, $installedVer) = each($_INSTALLED_VER))
 {
  $onlineVer = $_ONLINE_VER[$pkg];
  if(version_compare($onlineVer,$installedVer) > 0)
  {
   $outArr['outdated'][] = array('name'=>$pkg, 'installed_version'=>$installedVer, 'online_version'=>$onlineVer);
   if($verbose)
	$out.= "<tr><td>".$pkg."</td><td>".$installedVer."</td><td>".$onlineVer."</td></tr>";
  }
 }
 if($verbose)
  $out.= "</table>";

 if(count($outArr['outdated']))
  $out.= "\nThere are ".count($outArr['outdated'])." packages outdated.";
 else
  $out.= "\nAll packages are already updated to latest version";

 /* SET COOKIES AND UPDATE CACHE */
 //setcookie("APM_LAST_NOTIFY",date('Y-m-d'),strtotime("+1 week"));
 //setcookie("APM_OUTDATES_COUNT",count($outArr['outdated']),strtotime("+1 week"));
 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO gnujiko_apm_cache (var,val) VALUES('last_notify','".date('Y-m-d')."') ON DUPLICATE KEY UPDATE val='"
	.date('Y-m-d')."'");
 $db->RunQuery("INSERT INTO gnujiko_apm_cache (var,val) VALUES('outdates_count','".count($outArr['outdated'])."') ON DUPLICATE KEY UPDATE val='"
	.count($outArr['outdated'])."'");

 if($setLastUpdate)
  $db->RunQuery("INSERT INTO gnujiko_apm_cache (var,val) VALUES('last_update','".$setLastUpdate."') ON DUPLICATE KEY UPDATE val='"
	.$setLastUpdate."'");

 $db->Close();

 $outArr['last_notify'] = date('Y-m-d');
 $outArr['outdates_count'] = count($outArr['outdated']);

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_debug($args, $sessid, $shellid)
{
 switch($args[1])
 {
  case 'on' : case 'enable' : return system_debug_on($args, $sessid, $shellid); break;
  case 'off' : case 'disable' : return system_debug_off($args, $sessid, $shellid); break;
  case 'clear' : case 'empty' : return system_debug_clear($args, $sessid, $shellid); break;
 }

 return array('message'=>"Invalid action!", "error"=>"INVALID_ACTION");
}
//-------------------------------------------------------------------------------------------------------------------//
function system_debug_on($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "Enable system debug...";

 $debugTLM = 5;						// debug time length expire in minutes
 $includeShellCommands = false;		// se abilitato include tutti i comandi anche quelli che vanno a buon fine.

 for($c=2; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--include-shell-commands' : $includeShellCommands=true; break;

   case '-timelength' : case '-expiry' : {$debugTLM=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `system_debug` (
	 `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	 `ctime` DATETIME NOT NULL ,
	 `log_type` VARCHAR(32) NOT NULL ,
	 `ref_id` INT(11) NOT NULL ,
	 `log_query` LONGTEXT NOT NULL ,
	 `ret_message` TEXT NOT NULL ,
	 `ret_errcode` VARCHAR(64) NOT NULL ,
	 `success` TINYINT(1) NOT NULL ,
	 INDEX (`ref_id`)
	)");
 if($db->Error) return array('message'=>$out."failed!\nUnable to create table system_debug.\nMySQL Error:".$db->Error, 'error'=>'MYSQL_ERROR');
 $db->Close();

 // Clean table first
 $db = new AlpaDatabase();
 $db->RunQuery("TRUNCATE TABLE `system_debug`");
 $db->Close();


 $debugStartTime = time();
 $debugEndTime = $debugStartTime+($debugTLM*60);
 setcookie("GNUJIKO-ENABLE-DEBUG","1",$debugEndTime);
 if($includeShellCommands)
  setcookie("GNUJIKO-ENABLE-DEBUG-ALLGSHCMD","1",$debugEndTime);

 $out.= "done!\n";
 $out.= "Debug process started at ".date('H:i:s',$debugStartTime)."\n";
 $out.= "You only have ".$debugTLM." minutes to perform the test, after which the debugging will be disabled!\n";
 $out.= "Debugging will be disabled automatically at ".date('H:i:s',$debugEndTime)."\n";

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_debug_off($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_DEFAULT_FILE_PERMS;

 $out = "";
 setcookie("GNUJIKO-ENABLE-DEBUG",false);

 $fileName = "gnujikodebug";
 $_OUTPUT_TYPE = "text";
 $_FILE_PATH = "";


 for($c=2; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-f' : case '-file' : {$fileName=$args[$c+1]; $c++;} break;
   case '-type' : case '--output-type' : {$_OUTPUT_TYPE=$args[$c+1]; $c++;} break;
  }

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] == "root")
  $_FILE_PATH = "tmp/";
 else if($sessInfo['uid'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $_FILE_PATH = $_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
  $_FILE_PATH = "tmp/";

 $pi = pathinfo($fileName);
 if(!$pi['extension'])
 {
  switch($_OUTPUT_TYPE)
  {
   case 'html' : $fileName.= ".html"; break;
   default : $fileName.= ".txt"; break;
  }
 }


 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM system_debug WHERE 1");
 $db->Read();
 $_COUNT = $db->record[0];
 if(!$_COUNT)
 {
  $out.= "No log message found.\n";
  $db->Close();
  return array('message'=>$out);
 }


 // write to file
 $buffer = "";

 switch($_OUTPUT_TYPE)
 {
  case 'html' : {
	 $buffer.= "<html><head><title>Gnujiko Debug Results</title>";
	 $buffer.= "<style type='text/css'>body, td {font-family:arial,sans-serif;font-size:12px} th {text-align:left}</style></head><body>";
	 $buffer.= "<table border='0' width='100%'><tr><th>DATETIME</th><th>TYPE</th><th>QUERY</th><th>MESSAGE</th><th>ERR CODE</th><th>STATUS</th></tr>";
	} break;
 }

 $db->RunQuery("SELECT * FROM system_debug WHERE 1 ORDER BY id ASC");
 while($db->Read())
 {
  $time = $db->record['ctime'];
  $type = $db->record['log_type'];
  $query = $db->record['log_query'];
  $msg = $db->record['ret_message'];
  $errcode = $db->record['ret_errcode'];
  $success = $db->record['success'];

  switch($_OUTPUT_TYPE)
  {
   case 'html' : {
	 $buffer.= "<tr><td style='font-size:10px'>".$time."</td><td>".$type."</td><td>".$query."</td><td>".$msg."</td><td>".($errcode ? $errcode : '&nbsp;')."</td><td>"
		.($success ? "<span style='color:green'>OK</span>" : "<span style='color:red'>FAILED!</span>")."</td></tr>";
	} break;

   default : {
	 $buffer.= $time." ".($success ? "OK" : "FAILED!")." [".$type."]: ".$msg.($errcode ? "(ERRCODE: ".$errcode.")" : "")."\r\n";
	} break;
  }
 }
 
 switch($_OUTPUT_TYPE)
 {
  case 'html' : $buffer.= "</table></body>"; break;
 }

 $db->Close();

 $destFullPath = $_FILE_PATH.ltrim($fileName,"/");
 $ret = gfwrite($destFullPath, $buffer);
 if(!$ret)
  return array('message'=>"Unable to write log file to ".$destFullPath, 'error'=>'UNKNOWN_ERROR');
 
 $out.= "Log file has been write to ".$destFullPath."\n";
 $out.= "Click here to <a href='".$_ABSOLUTE_URL."getfile.php?file=".$destFullPath."'>get the log file</a>, or here to <a href='"
	.$_ABSOLUTE_URL.$destFullPath."' target='blank'>to view the log file directly into this browser</a>.\n";

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_debug_clear($args, $sessid, $shellid)
{
 $out = "Empty debug...";
 $db = new AlpaDatabase();
 $db->RunQuery("TRUNCATE TABLE `system_debug`");
 $db->Close();
 $out.= "done!\n";

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//

