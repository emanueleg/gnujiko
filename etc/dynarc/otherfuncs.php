<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 02-09-2013
 #PACKAGE: dynarc
 #DESCRIPTION: Other functions file
 #VERSION: 2.6beta
 #CHANGELOG: 02-09-2013 : Bug fix on dynarc_trash empty
			 21-02-2013 : Bug fix on dynarc_search
			 12-01-2013 : some changes in function chmod.
			 14-12-2012 - Bug fix in trash.
			 03-12-2012 : Completamento funzioni di base.
			 16-11-2012 - Integrazione con DynarcSync.
			 03-11-2012 - Bug fix in function dynarc_build.
			 19-07-2012 - Aggiunto parametro --force nelle funzioni install-extension & uninstall-extension
			 10-07-2012 - Aggiunto funzione search con possibilità di cercare qualcosa in più archivi
			 22-06-2012 - Aggiunto funzioni per copiare e rimuovere dalla clipboard.
 #TODO: 
 
*/

global $_BASE_PATH;
include_once($_BASE_PATH."include/userfunc.php");

function dynarc_chmod($args, $sessid, $shellid=0)
{
 $sessInfo = sessionInfo($sessid);
 $itemIds = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-itemid' : case '-id' : {$itemIds[]=$args[$c+1]; $c++;} break;
   case '-mode' : {$mode=$args[$c+1]; $c++;} break;
   case '-R' : $recursive=true; break;
   default : $mode=$args[$c]; break;
  }
 
 $mod = new GMOD($mode);
 $mode = $mod->MOD;

 if($archiveId)
 {
  $ret = GShell("dynarc archive-info -id '$archiveId'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 if(!$archiveInfo)
  return array("message"=>"You must specify the archive. (with -aid ARCHIVE_ID, or -ap ARCHIVE_PREFIX)\n","error"=>"INVALID_ARCHIVE");
 if($itemIds)
 {
  for($c=0; $c < count($itemIds); $c++)
  {
   $itemId = $itemIds[$c];
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT uid,gid,_mod FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='$itemId'");
   if(!$db->Read())
   {
    $out.= "Item #$itemId does not exists into archive ".$archiveInfo['name']."\n";
    continue;
   }
   if(($sessInfo['uname'] != "root") && ($sessInfo['uid'] != $db->record['uid']))
   {
    $out.= "Only the owner can change permissions for item #$itemId\n";
    continue;
   }
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET _mod='$mode' WHERE id='$itemId'");
   $db->Close();
   $out.= "Item #$itemId permissions has been changed!\n";
  }
  return array("message"=>$out);
 }
 else if($catId)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT uid,gid,_mod FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='$catId'");
  if(!$db->Read())
   return array("message"=>"Category #$catId does not exists into archive ".$archiveInfo['name']."\n","error"=>"CATEGORY_DOES_NOT_EXISTS");
  if(($sessInfo['uname'] != "root") && ($sessInfo['uid'] != $db->record['uid']))
   return array("message"=>"Only the owner can change permissions for category #$catId\n","error"=>"PERMISSION_DENIED");
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET _mod='$mode' WHERE id='$catId'");
  $db->Close();
  return array("message"=>"Category #$catId permissions has been changed!\n");
 }
 else
 {
  if(($sessInfo['uname'] != "root") && ($sessInfo['uid'] != $archiveInfo['modinfo']['uid']))
   return array("message"=>"Only the owner can change permissions for this archive","error"=>"PERMISSION_DENIED");
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_archives SET _mod='$mode' WHERE id='".$archiveInfo['id']."'");
  $db->Close();
  return array("message"=>"Archive #".$archiveInfo['id']." permissions has been changed!\n");
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_trash($args, $sessid, $shellid=0)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 $ids = array();
 $cats = array();

 $sessInfo = sessionInfo($sessid);

 $orderBy = "name ASC";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   /* ACTIONS */
   case 'list' : $list=true; break;
   case 'restore' : $restore=true; break;
   case 'empty' : $empty=true; break;
   case 'remove' : $remove=true; break;
   case 'count' : $count=true; break;

   /* PARAMS */
   case '-cat' : {$cats[] = $args[$c+1]; $c++;} break;
   case '-id' : {$ids[] = $args[$c+1]; $c++;} break;
   case '-ct' : {$ct=$args[$c+1]; $c++;} break; // <-- valid only for action empty.

   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : {$archivePrefix=$args[$c+1]; $c++;} break;

   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '--return-serp-info' : $returnSERPInfo=true; break;
  }

 if($archiveId)
 {
  $ret = GShell("dynarc archive-info -id '$archiveId'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 if(!$archiveInfo)
  return array("message"=>"You must specify the archive. (with -aid ARCHIVE_ID, or -ap ARCHIVE_PREFIX)\n","error"=>"INVALID_ARCHIVE");


 $m = new GMOD();
 $uQry1 = $m->userQuery($sessid,null,"dynarc_".$archiveInfo['prefix']."_categories");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_categories WHERE ($uQry1) AND trash='1'".($where ? " AND (".$db->Purify($where).")" : ""));
 $db->Read();
 $countCat = $db->record[0];
 $db->Close();

 $m = new GMOD();
 $uQry2 = $m->userQuery($sessid,null,"dynarc_".$archiveInfo['prefix']."_items");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_items WHERE ($uQry2) AND trash='1'".($where ? " AND (".$db->Purify($where).")" : ""));
 $db->Read();
 $countItems = $db->record[0];
 $db->Close();

 if($count)
 {
  $outArr = array('categories'=>$countCat, 'items'=>$countItems);
  return array('message'=>"There are ".$countCat." categories and ".$countItems." items into trash",'outarr'=>$outArr);
 }

 $outArr['count'] = $countCat+$countItems;

 /* GET EXTENSIONS */
 $extensions = array();
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."'");
 while($db->Read())
 {
  include_once($_BASE_PATH."etc/dynarc/extensions/".$db->record['extension_name']."/index.php");
  $extensions[] = $db->record['extension_name'];
 }
 $db->Close();


 /* ACTION */
 if($list) /* LIST OF TRASHED CATEGORIES AND ELEMENTS */
 {
  // CHECK LIMIT //
  if($limit && $outArr['count'])
  {
   $x = explode(",",$limit);
   if($x[1])
   {
    $serpRPP = $x[1];
    $serpFrom = $x[0];
   }
   else
   {
    $serpRPP = $x[0];
    $serpFrom = 0;
   }
   if($serpFrom >= $outArr['count'])
    $serpFrom-= $serpRPP;
   if($serpFrom < 0)
    $serpFrom = 0;
   $limit = $serpFrom ? "$serpFrom,$serpRPP" : $serpRPP;
   if($returnSERPInfo)
   {
    $outArr['serpinfo']['resultsperpage'] = $serpRPP;
    $outArr['serpinfo']['currentpage'] = $serpFrom ? floor($serpFrom/$serpRPP)+1 : 1;
    $outArr['serpinfo']['datafrom'] = $serpFrom;
   }
  }

  /* List of categories */
  if(!$limit || ($serpFrom < $countCat))
  {
   $db = new AlpaDatabase();
   if($limit)
   {
    $catLimit = $serpFrom.",".$serpRPP;
    $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_categories WHERE ($uQry1) AND trash='1'".($where ? " AND (".$db->Purify($where).")" : "")." ORDER BY ".$orderBy." LIMIT ".$catLimit);
   }
   else
    $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_categories WHERE ($uQry1) AND trash='1'".($where ? " AND (".$db->Purify($where).")" : "")." ORDER BY ".$orderBy);
   while($db->Read())
    $outArr['categories'][] = array('id'=>$db->record['id'],'name'=>$db->record['name'],'ctime'=>strtotime($db->record['ctime']));
   if(count($outArr['categories']))
    $out.= "There are ".count($outArr['categories'])." categories into trash.\n";
   $db->Close();
  }

  /* List of items */
  if(!$limit || (count($outArr['categories']) < $serpRPP))
  {
   $db = new AlpaDatabase();
   if($limit)
   {
	$start = $serpFrom - $countCat;
    if($start < 0)
	 $start = 0;
    $itemLimit = $start.",".($serpRPP-count($outArr['categories']));
    $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE ($uQry2) AND trash='1'".($where ? " AND (".$db->Purify($where).")" : "")." ORDER BY ".$orderBy." LIMIT ".$itemLimit);
   }
   else
    $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE ($uQry2) AND trash='1'".($where ? " AND (".$db->Purify($where).")" : "")." ORDER BY ".$orderBy);
   while($db->Read())
    $outArr['items'][] = array('id'=>$db->record['id'],'name'=>$db->record['name'],'ctime'=>strtotime($db->record['ctime']));
   if(count($outArr['items']))
    $out.= "There are ".count($outArr['items'])." items into trash.\n";
   $db->Close();
  }
  if(!count($outArr['items']) && !count($outArr['categories']))
   $out.= "The trash is empty.";
  return array("message"=>$out,"outarr"=>$outArr);
 }
 else if($restore) /* RESTORE CATEGORIES AND ELEMENTS */
 {
  /* RESTORE CATEGORIES */
  for($c=0; $c < count($cats); $c++)
  {
   $db = new AlpaDatabase(); $db2 = new AlpaDatabase(); $db3 = new AlpaDatabase();
   $db->RunQuery("SELECT uid,gid,_mod,parent_id,hierarchy".($archiveInfo['sync_enabled'] ? ",syncid" : "")." FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$cats[$c]."'");
   if(!$db->Read())
   {
	$out.= "Warning: category #".$cats[$c]." does not exists!\n";
	continue;
   }
   if(($db->record['uid'] != $sessInfo['uid']) && ($sessInfo['uname'] != 'root'))
   {
	$out.= "Warning: only owner can restore category #".$cats[$c]."\n";
    continue;
   }

   $catInfo = array('id'=>$cats[$c], 'parent_id'=>$db->record['parent_id'], 'hierarchy'=>$db->record['hierarchy']);

   $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET trash='0' WHERE id='".$cats[$c]."'");
   if($archiveInfo['sync_enabled'])
	$db3->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET status='RESTORED',logtime='".date('Y-m-d H:i:s')."' WHERE syncid='".$db->record['syncid']."'");
   
   // call onrestorecategory function if exists //
   if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
   {
    include_once($_BASE_PATH.$archiveInfo['functionsfile']);
    if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_onrestorecategory",true))
     call_user_func("dynarcfunction_".$archiveInfo['prefix']."_onrestorecategory", $args, $sessid, $shellid, $archiveInfo, $catInfo);
   }
   // call onrestorecategory function for all installed extensions //
   for($i=0; $i < count($extensions); $i++)
   { 
    /* EXECUTE FUNCTION */
    if(is_callable("dynarcextension_".$extensions[$i]."_onrestorecategory",true))
    {
     $ret = call_user_func("dynarcextension_".$extensions[$i]."_onrestorecategory", $args, $sessid, $shellid, $archiveInfo, $catInfo);
     if($ret['error'])
      return $ret;
    }
   }

   $db3->Close(); $db2->Close(); $db->Close();
   $out.= "Category #".$cats[$c]." has been restored\n";
   $outArr['categories'][] = $cats[$c];
  }

  /* RESTORE ELEMENTS */
  for($c=0; $c < count($ids); $c++)
  {
   $db = new AlpaDatabase(); $db2 = new AlpaDatabase(); $db3 = new AlpaDatabase();
   $db->RunQuery("SELECT uid,gid,_mod,cat_id,hierarchy".($archiveInfo['sync_enabled'] ? ",syncid" : "")." FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$ids[$c]."'");
   if(!$db->Read())
   {
	$out.= "Warning: item #".$ids[$c]." does not exists!\n";
	continue;
   }
   if(($db->record['uid'] != $sessInfo['uid']) && ($sessInfo['uname'] != 'root'))
   {
	$out.= "Warning: only owner can restore item #".$ids[$c]."\n";
    continue;
   }
   $itemInfo = array('id'=>$ids[$c], 'cat_id'=>$db->record['cat_id'], 'hierarchy'=>$db->record['hierarchy']);
   $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET trash='0' WHERE id='".$ids[$c]."'");
   if($archiveInfo['sync_enabled'])
	$db3->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET status='RESTORED',logtime='".date('Y-m-d H:i:s')."' WHERE syncid='".$db->record['syncid']."'");

   // call onrestoreitem function if exists //
   if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
   {
    include_once($_BASE_PATH.$archiveInfo['functionsfile']);
    if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_onrestoreitem",true))
     call_user_func("dynarcfunction_".$archiveInfo['prefix']."_onrestoreitem", $args, $sessid, $shellid, $archiveInfo, $itemInfo);
   }
   // call onrestoreitem function for all installed extensions //
   for($i=0; $i < count($extensions); $i++)
   { 
    /* EXECUTE FUNCTION */
    if(is_callable("dynarcextension_".$extensions[$i]."_onrestoreitem",true))
    {
     $ret = call_user_func("dynarcextension_".$extensions[$i]."_onrestoreitem", $args, $sessid, $shellid, $archiveInfo, $itemInfo);
     if($ret['error'])
      return $ret;
    }
   }
   $db3->Close(); $db2->Close(); $db->Close();
   $out.= "Item #".$ids[$c]." has been restored\n";
   $outArr['items'][] = $ids[$c];
  }
 }
 else if($empty) /* EMPTY TRASH */
 {
  if($ct)
  {
   // get cat id //
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_categories WHERE tag='".$ct."' LIMIT 1");
   $db->Read();
   $ctID = $db->record['id'];
   $db->Close();
  }
  else if(count($cats))
   $ctID = $cats[0];

  /* EMPTY CATEGORIES */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_categories WHERE ($uQry1) AND trash='1'".($ctID ? " AND parent_id=".$ctID : ""));
  while($db->Read())
  {
   GShell("dynarc delete-cat -ap ".$archiveInfo['prefix']." -id ".$db->record['id']." -r",$sessid,$shellid);
  }
  $db->Close();

  /* EMPTY ELEMENTS */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE ($uQry2) AND trash='1'".($ctID ? " AND cat_id=".$ctID : ""));
  while($db->Read())
  {
   GShell("dynarc delete-item -ap ".$archiveInfo['prefix']." -id ".$db->record['id']." -r",$sessid,$shellid);
  }
  $db->Close();
  $out.= "Trash is empty!\n";
 }
 else if($remove) /* REMOVE CATEGORIES AND ELEMENTS FROM TRASH */
 {
  for($c=0; $c < count($cats); $c++)
  {
   $ret = GShell("dynarc delete-cat -ap ".$archiveInfo['prefix']." -id ".$cats[$c]." -r",$sessid,$shellid);
   if(!$ret['error'])
	$outArr['categories'][] = $cats[$c];
  }
  for($c=0; $c < count($ids); $c++)
  {
   $ret = GShell("dynarc delete-item -ap ".$archiveInfo['prefix']." -id ".$ids[$c]." -r",$sessid,$shellid);
   if(!$ret['error'])
	$outArr['items'][] = $ids[$c];
  }
  $out.= "done!\n";
 }
 
 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_installExtension($args, $sessid, $shellid=0)
{
 global $_BASE_PATH;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-params' : {$params=$args[$c+1]; $c++;} break;
   case '--force' : $force=true; break;
   default : $extension=$args[$c]; break;
  }

 if(!$extension)
  return array("message"=>"You must specify an extension\n","error"=>"INVALID_EXTENSION");

 /* CHECK IF EXTENSION EXISTS */
 if(!file_exists($_BASE_PATH."etc/dynarc/extensions/".$extension."/index.php"))
  return array("message"=>"Extension $extension does not exists","error"=>"EXTENSION_DOES_NOT_EXISTS");

 /* CHECK FOR ARCHIVE */
 if($archiveId)
 {
  $ret = GShell("dynarc archive-info -id '$archiveId'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 if(!$archiveInfo)
  return array("message"=>"You must specify the archive. (with -aid ARCHIVE_ID, or -ap ARCHIVE_PREFIX)\n","error"=>"INVALID_ARCHIVE");

 /* CHECK IF EXTENSION ALREADY INSTALLED */
 $isAlreadyInstalled=false;
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='$extension' LIMIT 1");
 if($db->Read())
 {
  if(!$force)
   return array("message"=>"Extension $extension is already installed!\n");
  $isAlreadyInstalled=true;
 }
 $db->Close();

 /* INSTALL EXTENSION */
 include_once($_BASE_PATH."etc/dynarc/extensions/".$extension."/index.php");
 if(is_callable("dynarcextension_".$extension."_install",true))
 {
  $ret = call_user_func("dynarcextension_".$extension."_install", $params, $sessid, $shellid, $archiveInfo);
  if(!$ret['error'] && !$isAlreadyInstalled)
  {
   $db = new AlpaDatabase();
   $db->RunQuery("INSERT INTO dynarc_archive_extensions(archive_id,extension_name,params) VALUES('".$archiveInfo['id']."','$extension','$params')");
   $db->Close();
  }
  return $ret;
 }
 else
  return array("message"=>"Sorry, cannot install extension $extension!\n", "error"=>"INSTALL_FAILED");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_uninstallExtension($args, $sessid, $shellid=0)
{
 global $_BASE_PATH;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-params' : {$params=$args[$c+1]; $c++;} break;
   case '--force' : $force=true; break;
   default : $extension=$args[$c]; break;
  }

 if(!$extension)
  return array("message"=>"You must specify an extension\n","error"=>"INVALID_EXTENSION");

 /* CHECK IF EXTENSION EXISTS */
 if(!file_exists($_BASE_PATH."etc/dynarc/extensions/".$extension."/index.php"))
  return array("message"=>"Extension $extension does not exists","error"=>"EXTENSION_DOES_NOT_EXISTS");

 /* CHECK FOR ARCHIVE */
 if($archiveId)
 {
  $ret = GShell("dynarc archive-info -id '$archiveId'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 if(!$archiveInfo)
  return array("message"=>"You must specify the archive. (with -aid ARCHIVE_ID, or -ap ARCHIVE_PREFIX)\n","error"=>"INVALID_ARCHIVE");

 /* CHECK IF EXTENSION IS INSTALLED */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='$extension' LIMIT 1");
 if(!$db->Read() && !$force)
  return array("message"=>"Extension $extension is not installed!\n");
 $db->Close();

 /* UNINSTALL EXTENSION */
 include_once($_BASE_PATH."etc/dynarc/extensions/".$extension."/index.php");
 if(is_callable("dynarcextension_".$extension."_uninstall",true))
 {
  $ret = call_user_func("dynarcextension_".$extension."_uninstall", $params, $sessid, $shellid, $archiveInfo);
  if(!$ret['error'])
  {
   $db = new AlpaDatabase();
   $db->RunQuery("DELETE FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='$extension'");
   $db->Close();
  }
  return $ret;
 }
 else
  return array("message"=>"Sorry, cannot remove extension $extension from archive!\n", "error"=>"UNINSTALL_FAILED");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_extensionList($args, $sessid, $shellid=0)
{
 global $_BASE_PATH;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : {$archivePrefix=$args[$c+1]; $c++;} break;
  }
 
 /* CHECK FOR ARCHIVE */
 if($archiveId)
 {
  $ret = GShell("dynarc archive-info -id '$archiveId'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 if(!$archiveInfo)
  return array("message"=>"You must specify the archive. (with -aid ARCHIVE_ID, or -ap ARCHIVE_PREFIX)\n","error"=>"INVALID_ARCHIVE");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' ORDER BY extension_name ASC");
 while($db->Read())
 {
  $out.= $db->record['extension_name']."\n";
  $outArr[] = array('name'=>$db->record['extension_name']);
 }
 $db->Close();
 if(!count($outArr))
  $out = "No extensions installed on this archive.";
 else
  $out.= "\n".count($outArr)." extensions installed.";
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_execFunc($args, $sessid, $shellid=0)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 $extraParams = array();
 $_lastParam = 0;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-params' : {$params=$args[$c+1]; $c++;} break;
   /* EXTRA PARAMS */
   case '--extra-param' : {
	 $extraParams[$args[$c+1]] = ""; 
	 $_lastParam=$args[$c+1]; 
	 $c++; 
	} break;
   case '--extra-value' : {$extraParams[$_lastParam] = $args[$c+1];$c++;} break;

   default : $function=$args[$c]; break;
  }

 if(!$function)
  return array("message"=>"You must specify the function", "error"=>"INVALID_FUNCTION");

 if(substr($function,0,4) == "ext:") // EXTENSION FUNCTION //
 {
  $function = substr($function,4);
  $x = explode(".",$function);
  $extension = $x[0];
  $function = $x[1];
  
  $out = "Execute function $function from extension $extension...\n";

  /* CHECK IF EXTENSION EXISTS */
  if(!file_exists($_BASE_PATH."etc/dynarc/extensions/".$extension."/index.php"))
   return array("message"=>"Extension $extension does not exists!\n", "error"=>"EXTENSION_DOES_NOT_EXISTS");

  /* EXECUTE FUNCTION */
  include_once($_BASE_PATH."etc/dynarc/extensions/".$extension."/index.php");
  if(is_callable("dynarcextension_".$extension."_".$function,true))
  {
   $ret = call_user_func("dynarcextension_".$extension."_".$function, $params, $sessid, $shellid, $extraParams);
   return $ret;
  }
  else
   return array("message"=>"Failed!, unable to execute function $function from extension $extension\n", "error"=>"EXEC_FUNC_FAILED");
 }
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_build($args, $sessid, $shellid=0)
{
 global $_BASE_PATH;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-params' : {$params=$args[$c+1]; $c++;} break;
   case '-c' : {$compiler=$args[$c+1]; $c++;} break;
  }
 
 /* CHECK IF COMPILER EXISTS */
 if(!file_exists($_BASE_PATH."etc/dynarc/compilers/".$compiler."/index.php"))
  return array("message"=>"Compiler $compiler does not exists","error"=>"COMPILER_DOES_NOT_EXISTS");

 /* CHECK FOR ARCHIVE */
 if($archiveId)
 {
  $ret = GShell("dynarc archive-info -id '$archiveId'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 if(!$archiveInfo)
  return array("message"=>"You must specify the archive. (with -aid ARCHIVE_ID, or -ap ARCHIVE_PREFIX)\n","error"=>"INVALID_ARCHIVE");

 include_once($_BASE_PATH."etc/dynarc/compilers/".$compiler."/index.php");
 if(is_callable("dynarccompiler_".$compiler."_build",true))
 {
  $ret = call_user_func("dynarccompiler_".$compiler."_build", $params, $sessid, $shellid, $archiveInfo, $id ? $id : $catId, $catId ? true : false);
  return $ret;
 }
 else
  return array("message"=>"Sorry, unable to build with $compiler compiler.\n", "error"=>"BUILD_FAILED");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_unbuild($args, $sessid, $shellid=0)
{
 global $_BASE_PATH;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-params' : {$params=$args[$c+1]; $c++;} break;
   case '-c' : {$compiler=$args[$c+1]; $c++;} break;
  }
 
 /* CHECK IF COMPILER EXISTS */
 if(!file_exists($_BASE_PATH."etc/dynarc/compilers/".$compiler."/index.php"))
  return array("message"=>"Compiler $compiler does not exists","error"=>"COMPILER_DOES_NOT_EXISTS");

 /* CHECK FOR ARCHIVE */
 if($archiveId)
 {
  $ret = GShell("dynarc archive-info -id '$archiveId'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 if(!$archiveInfo)
  return array("message"=>"You must specify the archive. (with -aid ARCHIVE_ID, or -ap ARCHIVE_PREFIX)\n","error"=>"INVALID_ARCHIVE");

 include_once($_BASE_PATH."etc/dynarc/compilers/".$compiler."/index.php");
 if(is_callable("dynarccompiler_".$compiler."_unbuild",true))
 {
  $ret = call_user_func("dynarccompiler_".$compiler."_unbuild", $params, $sessid, $shellid, $archiveInfo, $id ? $id : $catId, $catId ? true : false);
  return $ret;
 }
 else
  return array("message"=>"Sorry, unable to un-build with $compiler compiler.\n", "error"=>"UNBUILD_FAILED");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_copyToClipboard($args, $sessid, $shellid=0)
{
 global $_BASE_PATH;

 $outArr = array();

 $clipboardID = 0;
 $qty = 1;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-clipboard' : {$clipboardName=$args[$c+1]; $c++;} break;
   case '-clipboardid' : {$clipboardID=$args[$c+1]; $c++;} break;
   case '--last-clipboard' : $useLastClipboard=true; break;

   case '-tag' : {$clipboardTag=$args[$c+1]; $c++;} break;

   case '-ap' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-qty' : {$qty=$args[$c+1]; $c++;} break;
  }

 $ret = GShell("printenv",$sessid,$shellid);
 $env = $ret['outarr'];

 $cpElements = 0;
 
 $cpCount = ($env['DYNCB-COUNT'] ? $env['DYNCB-COUNT'] : 0);

 if($useLastClipboard)
 {
  $clipboardID = $cpCount;
  $clipboardName = $env["DYNCB-".($clipboardID-1)."-NAME"];
  $clipboardTag = $env["DYNCB-".($clipboardID-1)."-TAG"];
  $cpElements = $env["DYNCB-".($clipboardID-1)."-ELEMENTS"];
  $outArr['clipboard'] = array('id'=>$clipboardID,'name'=>$clipboardName,'tag'=>$clipboardTag);
 }
 else if($clipboardID)
 {
  $clipboardName = $env["DYNCB-".($clipboardID-1)."-NAME"];
  $clipboardTag = $env["DYNCB-".($clipboardID-1)."-TAG"];
  $cpElements = $env["DYNCB-".($clipboardID-1)."-ELEMENTS"];
  $outArr['clipboard'] = array('id'=>$clipboardID,'name'=>$clipboardName,'tag'=>$clipboardTag);
 }
 else if($clipboardName)
 {
  for($c=0; $c < $cpCount; $c++)
  {
   $name = $env["DYNCB-".$c."-NAME"];
   if($name == $clipboardName)
   {
    $clipboardID = ($c+1);
	$clipboardTag = $env["DYNCB-".$c."-TAG"];
    $cpElements = $env["DYNCB-".$c."-ELEMENTS"];
	$outArr['clipboard'] = array('id'=>$clipboardID,'name'=>$clipboardName,'tag'=>$clipboardTag);
    break;
   }
  }
 }
 else
  return array('message'=>'You must specify the clipboard','error'=>"INVALID_CLIPBOARD");

 if(!$clipboardID)
 {
  $clipboardID = $cpCount+1;
  // Create new clipboard //
  GShell("export DYNCB-COUNT=".($cpCount+1)." `DYNCB-".($clipboardID-1)."-NAME=".$clipboardName."` DYNCB-".($clipboardID-1)."-ELEMENTS=1 `DYNCB-".($clipboardID-1)."-TAG=".$clipboardTag."`",$sessid,$shellid);
  $outArr['clipboard'] = array('id'=>$clipboardID,'name'=>$clipboardName,'tag'=>$clipboardTag);
 }

 // Insert item or category to clipboard //
 GShell("export DYNCB-".($clipboardID-1)."-ITM-".$cpElements."-AP=".$archivePrefix." DYNCB-".($clipboardID-1)."-ITM-".$cpElements."-ID=$id DYNCB-".($clipboardID-1)."-ITM-".$cpElements."-CAT=$catId DYNCB-".($clipboardID-1)."-ITM-".$cpElements."-QTY=$qty DYNCB-".($clipboardID-1)."-ELEMENTS=".($cpElements+1),$sessid,$shellid);

 $outArr['element'] = array('ap'=>$archivePrefix,'id'=>$id,'cat'=>$catId,'qty'=>$qty);
 
 return array('message'=>"Done!",'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_clipboardList($args, $sessid, $shellid=0)
{
 global $_BASE_PATH;

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-tag' : {$clipboardTag=$args[$c+1]; $c++;} break;
  }

 $ret = GShell("printenv",$sessid,$shellid);
 $env = $ret['outarr'];

 $cpCount = ($env['DYNCB-COUNT'] ? $env['DYNCB-COUNT'] : 0);

 for($c=0; $c < $cpCount; $c++)
 {
  $tag = $env["DYNCB-".$c."-TAG"];
  $name = $env["DYNCB-".$c."-NAME"];
  if($clipboardTag && ($tag != $clipboardTag))
   continue;
  $cpInfo = array('id'=>($c+1),'tag'=>$tag,'name'=>$name,'elements'=>array());
  $elements = $env["DYNCB-".$c."-ELEMENTS"];
  for($i=0; $i < $elements; $i++)
  {
   $ap = $env["DYNCB-".$c."-ITM-".$i."-AP"];
   $id = $env["DYNCB-".$c."-ITM-".$i."-ID"];
   $cat = $env["DYNCB-".$c."-ITM-".$i."-CAT"];
   $qty = $env["DYNCB-".$c."-ITM-".$i."-QTY"];
   $cpInfo['elements'][] = array('ap'=>$ap,'id'=>$id,'cat'=>$cat,'qty'=>$qty);
  }
  $outArr[] = $cpInfo;
  $out.= "#".$cpInfo['id']." [".$cpInfo['tag']."] - ".$cpInfo['name']." (elements: ".count($cpInfo['elements']).")\n";
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_clipboardInfo($args, $sessid, $shellid=0)
{
 global $_BASE_PATH;

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$clipboardID=$args[$c+1]; $c++;} break;
  }

 $ret = GShell("printenv",$sessid,$shellid);
 $env = $ret['outarr'];
 $cpCount = ($env['DYNCB-COUNT'] ? $env['DYNCB-COUNT'] : 0);

 $c = $clipboardID-1;

 $tag = $env["DYNCB-".$c."-TAG"];
 $name = $env["DYNCB-".$c."-NAME"];
 if(!$name)
  return array('message'=>'Clipboard #'.$clipboardID.' does not exists.', 'error'=>'CLIPBOARD_DOES_NOT_EXISTS');

 $cpInfo = array('id'=>$clipboardID,'tag'=>$tag,'name'=>$name,'elements'=>array());
 $elements = $env["DYNCB-".$c."-ELEMENTS"];

 for($i=0; $i < $elements; $i++)
 {
  $ap = $env["DYNCB-".$c."-ITM-".$i."-AP"];
  $id = $env["DYNCB-".$c."-ITM-".$i."-ID"];
  $cat = $env["DYNCB-".$c."-ITM-".$i."-CAT"];
  $qty = $env["DYNCB-".$c."-ITM-".$i."-QTY"];
  $cpInfo['elements'][] = array('ap'=>$ap,'id'=>$id,'cat'=>$cat,'qty'=>$qty);
 }
 $outArr = $cpInfo;
 $out.= "#".$cpInfo['id']." [".$cpInfo['tag']."] - ".$cpInfo['name']." (elements: ".count($cpInfo['elements']).")\n";

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_clipboardDelete($args, $sessid, $shellid=0)
{
 global $_BASE_PATH;

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$clipboardID=$args[$c+1]; $c++;} break;
  }

 $ret = GShell("printenv",$sessid,$shellid);
 $env = $ret['outarr'];
 $cpCount = ($env['DYNCB-COUNT'] ? $env['DYNCB-COUNT'] : 0);

 $cidx = $clipboardID-1;

 $tag = $env["DYNCB-".$cidx."-TAG"];
 $name = $env["DYNCB-".$cidx."-NAME"];
 $elements = $env["DYNCB-".$cidx."-ELEMENTS"];
 if(!$name)
  return array('message'=>'Clipboard #'.$clipboardID.' does not exists.', 'error'=>'CLIPBOARD_DOES_NOT_EXISTS');

 $cpCount--;

 $qry = "export DYNCB-COUNT=".$cpCount." DYNCB-".$cidx."-NAME= DYNCB-".$cidx."-ELEMENTS= DYNCB-".$cidx."-TAG= ";
 for($c=0; $c < $elements; $c++)
  $qry.= " DYNCB-".$cidx."-ITM-".$c."-AP= DYNCB-".$cidx."-ITM-".$c."-ID= DYNCB-".$cidx."-ITM-".$c."-CAT= DYNCB-".$cidx."-ITM-".$c."-QTY= ";

 GShell($qry,$sessid,$shellid);

 // TODO: da qui bisogna poi sistemare l'id di tutte le clipboard dopo di questa. //
 /*if($cpCount > $clipboardID)
 {
  for($c=$clipboardID; $c < $cpCount; $c++)
  {
   
  }

 }*/

 $out.= "Clipboard #$clipboardID has been removed!";

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_search($args, $sessid, $shellid=0)
{
 $out = "";
 $outArr = array();
 $sessInfo = sessionInfo($sessid);

 $archives = array();
 $archiveIds = array();
 $archivePrefixes = array();
 $archiveByPrefix = array();
 $archiveTypes = array();
 $fields = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-aid' : {$archiveIds[]=$args[$c+1]; $c++;} break;
   case '-ap' : {$archivePrefixes[]=$args[$c+1]; $c++;} break;
   case '-at' : {$archiveTypes[]=$args[$c+1]; $c++;} break;

   case '-field' : {$fields[]=$args[$c+1]; $c++;} break;
   case '-fields' : {
	 $x = explode(",",$args[$c+1]);
	 for($i=0; $i < count($x); $i++)
	 {
	  if($x[$i])
	   $fields[] = $x[$i];
	 }
	 $c++;
	} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;

   case '-c' : case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : case '--cat-tag' : {$catTag=$args[$c+1]; $c++;} break;
   case '-into' : {$into=$args[$c+1]; $c++;} break;

   case '-get' : {$get=$args[$c+1]; $c++;} break;
   case '-extget' : {$extget=$args[$c+1]; $c++;} break;

   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '--return-serp-info' : $returnSERPInfo=true; break;
   case '--verbose' : $verbose=true; break;

   default : $searchQry = $args[$c]; break;
  }

 for($c=0; $c < count($archiveTypes); $c++)
 {
  $ret = GShell("dynarc archive-list -a -type `".$archiveTypes[$c]."`",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  for($i=0; $i < count($ret['outarr']); $i++)
  {
   $archives[] = $ret['outarr'][$i];
   $archiveByPrefix[$ret['outarr'][$i]['prefix']] = $ret['outarr'][$i];
  }
 }

 for($c=0; $c < count($archivePrefixes); $c++)
 {
  $ret = GShell("dynarc archive-info -ap `".$archivePrefixes[$c]."`",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archives[] = $ret['outarr'];
  $archiveByPrefix[$ret['outarr']['prefix']] = $ret['outarr'];
 }

 for($c=0; $c < count($archiveIds); $c++)
 {
  $ret = GShell("dynarc archive-info -id `".$archiveIds[$c]."`",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archives[] = $ret['outarr'];
  $archiveByPrefix[$ret['outarr']['prefix']] = $ret['outarr'];
 }

 //----------------------------------------------------------------------------------------------//
 $db = new AlpaDatabase();
 $qry = "";
 for($c=0; $c < count($fields); $c++)
 {
  $field = $fields[$c];
  $qry.= " OR ((".$field."=\"".$db->Purify($searchQry)."\") OR (".$field." LIKE \"".$db->Purify($searchQry)."%\") OR (".$field." LIKE \"%"
	.$db->Purify($searchQry)."%\") OR (".$field." LIKE \"%".$db->Purify($searchQry)."\"))";
 }
 if($qry)
  $qry = " AND (".ltrim($qry," OR ").")";
 if($where)
  $qry.= " AND (".$where.")";
 $qry.= " AND trash='0'";
 $db->Close();
 //----------------------------------------------------------------------------------------------//
 $m = new GMOD();
 $countQry = "";
 for($c=0; $c < count($archives); $c++)
 {
  $uQry = $m->userQuery($sessid,null,"dynarc_".$archives[$c]['prefix']."_items");
  $countQry.= " UNION SELECT '".$archives[$c]['prefix']."' AS tb_prefix,id FROM dynarc_".$archives[$c]['prefix']."_items WHERE ($uQry)".$qry;
 }
 $countQry = "SELECT COUNT(*) FROM (".ltrim($countQry," UNION ").") AS qryelements";

 $db = new AlpaDatabase();
 $db->RunQuery($countQry);
 $db->Read();
 $outArr['count'] = $db->record[0];
 //----------------------------------------------------------------------------------------------//
 // CHECK LIMIT //
 if($limit && $outArr['count'])
 {
  $x = explode(",",$limit);
  if($x[1])
  {
   $serpRPP = $x[1];
   $serpFrom = $x[0];
  }
  else
  {
   $serpRPP = $x[0];
   $serpFrom = 0;
  }
  if($serpFrom >= $outArr['count'])
   $serpFrom-= $serpRPP;
  if($serpFrom < 0)
   $serpFrom = 0;
  $limit = $serpFrom ? "$serpFrom,$serpRPP" : $serpRPP;
  if($returnSERPInfo)
  {
   $outArr['serpinfo']['resultsperpage'] = $serpRPP;
   $outArr['serpinfo']['currentpage'] = $serpFrom ? floor($serpFrom/$serpRPP)+1 : 1;
   $outArr['serpinfo']['datafrom'] = $serpFrom;
  }
 }
 //----------------------------------------------------------------------------------------------//
 $db->Close();

 $obFS = "";
 if($orderBy)
 {
  // get orderby fields //
  $x = explode(",",$orderBy);
  for($c=0; $c < count($x); $c++)
  {
   $f = $x[$c];
   if(!$f) continue;
   $f = str_replace(array('ASC','DESC','asc','desc'), "", $f);
   $obFS.= ",".$f;
  }
 }

 $db = new AlpaDatabase();
 $finalQry = "";
 for($c=0; $c < count($archives); $c++)
 {
  $uQry = $m->userQuery($sessid,null,"dynarc_".$archives[$c]['prefix']."_items");
  $finalQry.= " UNION SELECT '".$archives[$c]['prefix']."' AS tb_prefix,id".str_replace(",id","",$obFS)." FROM dynarc_".$archives[$c]['prefix']."_items WHERE ($uQry)".$qry;
 }
 $finalQry = "SELECT * FROM (".ltrim($finalQry," UNION ").") AS qryelements";
 if($orderBy)
  $finalQry.= " ORDER BY ".$orderBy;
 if($limit)
  $finalQry.= " LIMIT $limit";
 $db->Close();

 //$out.= "Final qry:\n".$finalQry."\n";

 $db = new AlpaDatabase();
 $db->RunQuery($finalQry);
 while($db->Read())
 {
  $ret = GShell("dynarc item-info -ap '".$db->record['tb_prefix']."' --get-short-description -id ".$db->record['id'].($extget ? " -extget \"".$extget."\"" : "").($get ? " -get \"".$get."\"" : ""),$sessid,$shellid);
  if($ret['error'])
   continue;
  $ret['outarr']['tb_prefix'] = $db->record['tb_prefix'];
  $ret['outarr']['archive_type'] = $archiveByPrefix[$db->record['tb_prefix']]['type'];
  $outArr['items'][] = $ret['outarr'];
  if($verbose)
   $out.= "[".$ret['outarr']['tb_prefix']."] #".$ret['outarr']['id'].". ".$ret['outarr']['name']."\n";
 }
 $db->Close();
 
 $out.= $outArr['count']." items found.";
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

