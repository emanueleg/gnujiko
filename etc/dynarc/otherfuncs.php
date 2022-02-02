<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-05-2017
 #PACKAGE: dynarc
 #DESCRIPTION: Other functions file
 #VERSION: 2.26beta
 #CHANGELOG: 15-05-2017 : Aggiunto parametro extraVar su tutte le funzioni.
			 24-10-2016 : MySQLi integration.
			 18-05-2016 : Aggiornata funzione cross-search.
			 09-04-2016 : Aggiunta funzione fastSearch.
			 17-03-2016 : Possibilita di installare un estensione su piu archivi contemporaneamente.
			 13-02-2016 : Aggiunto parametro --bypass-errors su funzione trash.
			 04-02-2016 : Bug fix su cross-search, cercava anche nel cestino.
			 18-10-2015 : Aggiunta funzione generateExtensionTLFile
			 18-04-2015 : Piccoli aggiustamenti su funzione crossSearch.
			 16-03-2015 : Aggiornata funzione generate-extension-file.
			 21-11-2014 : Bug fix in function chmod.
			 03-11-2014 : Creata funzione extension-info.
			 13-10-2014 : Aggiornata funzione cross-search.
			 30-09-2014 : Aggiunta funzione cross-search.
			 27-07-2014 : Aggiunta funzione generate-functions-file
			 13-06-2014 : Aggiunta funzione extfind
			 05-06-2014 : Aggiunta funzione chown e chgrp
			 02-06-2014 : Bug fix in function dynarc search.
			 15-05-2014 : Aggiornata funzione chmod
			 02-09-2013 : Bug fix on dynarc_trash empty
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

function dynarc_chmod($args, $sessid, $shellid=0, $extraVar=null)
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
  $ret = GShell("dynarc archive-info -id '".$archiveId."'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '".$archivePrefix."'",$sessid,$shellid, $extraVar);
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
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET _mod='".$mode."' WHERE id='".$catId."'");
  $db->Close();
  if($recursive)
  {
   $db = new AlpaDatabase();
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET _mod='".$mode."' WHERE cat_id='".$catId."'");
   $db->Close();
  }
  return array("message"=>"Category #$catId permissions has been changed!\n");
 }
 else
 {
  if(($sessInfo['uname'] != "root") && ($sessInfo['uid'] != $archiveInfo['modinfo']['uid']))
   return array("message"=>"Only the owner can change permissions for this archive","error"=>"PERMISSION_DENIED");
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_archives SET _mod='".$mode."' WHERE id='".$archiveInfo['id']."'");
  if($recursive)
  {
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET _mod='".$mode."' WHERE 1");
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET _mod='".$mode."' WHERE 1");
  }
  $db->Close();
  return array("message"=>"Archive #".$archiveInfo['id']." permissions has been changed!\n");
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_chown($args, $sessid, $shellid=0,$extraVar=null)
{
 $sessInfo = sessionInfo($sessid);
 $itemIds = array();
 $UID = 0;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-itemid' : case '-id' : {$itemIds[]=$args[$c+1]; $c++;} break;
   case '-owner' : {$owner=$args[$c+1]; $c++;} break;
   case '-uid' : {$UID=$args[$c+1]; $c++;} break;
   case '-R' : case '-r' : $recursive=true; break;
  }

 if(!$owner && !$UID)
  return array("message"=>"You must specify the owner. (with: -owner USERNAME)","error"=>"INVALID_OWNER");
 if(!$UID)
  $UID = _getUID($owner);
 if(!$UID)
  return array("message"=>"User ".$owner." does not exists!", "error"=>"INVALID_OWNER");
 
 if($archiveId)
 {
  $ret = GShell("dynarc archive-info -id '".$archiveId."'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '".$archivePrefix."'",$sessid,$shellid, $extraVar);
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
   $db->RunQuery("SELECT uid,gid,_mod FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemId."'");
   if(!$db->Read())
   {
    $out.= "Item #$itemId does not exists into archive ".$archiveInfo['name']."\n";
    continue;
   }
   if(($sessInfo['uname'] != "root") && ($sessInfo['uid'] != $db->record['uid']))
   {
    $out.= "Only the owner can change owner for item #$itemId\n";
    continue;
   }
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET uid='".$UID."' WHERE id='".$itemId."'");
   $db->Close();
   $out.= "Item #$itemId owner has been changed!\n";
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
   return array("message"=>"Only the owner can change owner for category #$catId\n","error"=>"PERMISSION_DENIED");
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET uid='".$UID."' WHERE id='".$catId."'");
  $db->Close();
  if($recursive)
  {
   $db = new AlpaDatabase();
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET uid='".$UID."' WHERE cat_id='".$catId."'");
   $db->Close();
  }
  return array("message"=>"Category #$catId owner has been changed!\n");
 }
 else
 {
  if(($sessInfo['uname'] != "root") && ($sessInfo['uid'] != $archiveInfo['modinfo']['uid']))
   return array("message"=>"Only the owner can change owner for this archive","error"=>"PERMISSION_DENIED");
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_archives SET uid='".$UID."' WHERE id='".$archiveInfo['id']."'");
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET uid='".$UID."' WHERE 1");
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET uid='".$UID."' WHERE 1");
  $db->Close();
  return array("message"=>"Archive #".$archiveInfo['id']." owner has been changed!\n");
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_chgrp($args, $sessid, $shellid=0, $extraVar=null)
{
 $sessInfo = sessionInfo($sessid);
 $itemIds = array();
 $GID = 0;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-itemid' : case '-id' : {$itemIds[]=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-gid' : {$GID=$args[$c+1]; $c++;} break;
   case '-R' : case '-r' : $recursive=true; break;
  }

 if(!$group && !$GID)
  return array("message"=>"You must specify the group. (with: -group GROUPNAME)","error"=>"INVALID_GROUP");
 if(!$GID)
  $GID = _getGID($group);
 if(!$GID)
  return array("message"=>"Group ".$group." does not exists!", "error"=>"INVALID_OWNER");
 
 if($archiveId)
 {
  $ret = GShell("dynarc archive-info -id '".$archiveId."'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '".$archivePrefix."'",$sessid,$shellid, $extraVar);
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
   $db->RunQuery("SELECT uid,gid,_mod FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemId."'");
   if(!$db->Read())
   {
    $out.= "Item #$itemId does not exists into archive ".$archiveInfo['name']."\n";
    continue;
   }
   if(($sessInfo['uname'] != "root") && ($sessInfo['uid'] != $db->record['uid']))
   {
    $out.= "Only the owner can change group for item #$itemId\n";
    continue;
   }
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET gid='".$GID."' WHERE id='".$itemId."'");
   $db->Close();
   $out.= "Item #$itemId group has been changed!\n";
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
   return array("message"=>"Only the owner can change group for category #$catId\n","error"=>"PERMISSION_DENIED");
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET gid='".$GID."' WHERE id='".$catId."'");
  $db->Close();
  if($recursive)
  {
   $db = new AlpaDatabase();
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET gid='".$GID."' WHERE cat_id='".$catId."'");
   $db->Close();
  }
  return array("message"=>"Category #$catId group has been changed!\n");
 }
 else
 {
  if(($sessInfo['uname'] != "root") && ($sessInfo['uid'] != $archiveInfo['modinfo']['uid']))
   return array("message"=>"Only the owner can change group for this archive","error"=>"PERMISSION_DENIED");
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_archives SET gid='".$GID."' WHERE id='".$archiveInfo['id']."'");
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET gid='".$GID."' WHERE 1");
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET gid='".$GID."' WHERE 1");
  $db->Close();
  return array("message"=>"Archive #".$archiveInfo['id']." group has been changed!\n");
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_trash($args, $sessid, $shellid=0, $extraVar=null)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array('errors'=>array());

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
   case '--bypass-errors' : $bypassErrors=true; break;
  }

 if($archiveId)
 {
  $ret = GShell("dynarc archive-info -id '$archiveId'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid, $extraVar);
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
    {
	 $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_onrestorecategory", $args, $sessid, $shellid, $archiveInfo, $catInfo);
	 if(is_array($ret) && $ret['error'])
	 {
	  if($bypassErrors)
	   $outArr['errors'][] = $ret;
	  else
       return $ret;
	 }
	}
   }
   else if($archiveInfo['inherit'])
   {
    // call onrestorecategory function inherited if exists //
    $db4 = new AlpaDatabase();
    $db4->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
    $db4->Read();
    if($db4->record['fun_file'] && file_exists($_BASE_PATH.$db4->record['fun_file']))
    {
     include_once($_BASE_PATH.$db4->record['fun_file']);
     if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_onrestorecategory",true))
     {
      $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_onrestorecategory", $args, $sessid, $shellid, $archiveInfo, $catInfo);
	  if(is_array($ret) && $ret['error'])
	  {
	   if($bypassErrors)
	    $outArr['errors'][] = $ret;
	   else
        return $ret;
	  }
     }
    }
    $db4->Close();
   }

   // call onrestorecategory function for all installed extensions //
   for($i=0; $i < count($extensions); $i++)
   { 
    /* EXECUTE FUNCTION */
	include_once($_BASE_PATH."etc/dynarc/extensions/".$extensions[$i]."/index.php");
    if(is_callable("dynarcextension_".$extensions[$i]."_onrestorecategory",true))
    {
     $ret = call_user_func("dynarcextension_".$extensions[$i]."_onrestorecategory", $args, $sessid, $shellid, $archiveInfo, $catInfo);
	 if(is_array($ret) && $ret['error'])
   	 {
	  if($bypassErrors)
	   $outArr['errors'][] = $ret;
	  else
       return $ret;
     }
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
	{
     $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_onrestoreitem", $args, $sessid, $shellid, $archiveInfo, $itemInfo);
	 if(is_array($ret) && $ret['error'])
	 {
	  if($bypassErrors)
	   $outArr['errors'][] = $ret;
	  else
       return $ret;
	 }
	}
   }
   else if($archiveInfo['inherit'])
   {
    // call onrestoreitem function inherited if exists //
    $db4 = new AlpaDatabase();
    $db4->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
    $db4->Read();
    if($db4->record['fun_file'] && file_exists($_BASE_PATH.$db4->record['fun_file']))
    {
     include_once($_BASE_PATH.$db4->record['fun_file']);
     if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_onrestoreitem",true))
     {
      $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_onrestoreitem", $args, $sessid, $shellid, $archiveInfo, $itemInfo);
	  if(is_array($ret) && $ret['error'])
	  {
	   if($bypassErrors)
	    $outArr['errors'][] = $ret;
	   else
        return $ret;
	  }
     }
    }
    $db4->Close();
   }
   // call onrestoreitem function for all installed extensions //
   for($i=0; $i < count($extensions); $i++)
   { 
    /* EXECUTE FUNCTION */
	include_once($_BASE_PATH."etc/dynarc/extensions/".$extensions[$i]."/index.php");
    if(is_callable("dynarcextension_".$extensions[$i]."_onrestoreitem",true))
    {
     $ret = call_user_func("dynarcextension_".$extensions[$i]."_onrestoreitem", $args, $sessid, $shellid, $archiveInfo, $itemInfo);
     if($ret['error'])
     {
	  if($bypassErrors)
	   $outArr['errors'][] = $ret;
	  else
       return $ret;
     }
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
  $q = "";
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_categories WHERE (".$uQry1.") AND trash='1'".($ctID ? " AND parent_id=".$ctID : ""));
  while($db->Read())
   $q.= " -id ".$db->record['id'];
  $db->Close();

  if($q != "")
  {
   $ret = GShell("dynarc delete-cat -ap ".$archiveInfo['prefix']." -r".$q,$sessid,$shellid, $extraVar);
   if($ret['error'])
   {
	if($bypassErrors)
	 $outArr['errors'][] = $ret;
	else
     return $ret;
   }
  }


  /* EMPTY ELEMENTS */
  $q = "";
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_items WHERE (".$uQry2.") AND trash='1'".($ctID ? " AND cat_id=".$ctID : ""));
  while($db->Read())
   $q.= " -id ".$db->record['id'];
  $db->Close();

  if($q != "")
  {
   $ret = GShell("dynarc delete-item -ap ".$archiveInfo['prefix']." -r".$q,$sessid,$shellid, $extraVar);
   if($ret['error'])
   {
	if($bypassErrors)
	 $outArr['errors'][] = $ret;
	else
     return $ret;
   }
  }

  $out.= "Trash is empty!\n";
 }
 else if($remove) /* REMOVE CATEGORIES AND ELEMENTS FROM TRASH */
 {
  for($c=0; $c < count($cats); $c++)
  {
   $ret = GShell("dynarc delete-cat -ap ".$archiveInfo['prefix']." -id ".$cats[$c]." -r",$sessid,$shellid, $extraVar);
   if(!$ret['error'])
	$outArr['categories'][] = $cats[$c];
   else if($bypassErrors)
    $outArr['errors'][] = $ret;
  }
  for($c=0; $c < count($ids); $c++)
  {
   $ret = GShell("dynarc delete-item -ap ".$archiveInfo['prefix']." -id ".$ids[$c]." -r",$sessid,$shellid, $extraVar);
   if(!$ret['error'])
	$outArr['items'][] = $ids[$c];
   else if($bypassErrors)
    $outArr['errors'][] = $ret;
  }
  $out.= "done!\n";
 }
 
 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_installExtension($args, $sessid, $shellid=0, $extraVar=null)
{
 global $_BASE_PATH;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $archiveList = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-at' : {$archiveType=$args[$c+1]; $c++;} break;
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
  $ret = GShell("dynarc archive-info -id '$archiveId'",$sessid,$shellid, $extraVar);
  if($ret['error']) return $ret;
  $archiveList[] = $ret['outarr'];
 }
 else if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid, $extraVar);
  if($ret['error']) return $ret;
  $archiveList[] = $ret['outarr'];
 }
 else if($archiveType)
 {
  $ret = GShell("dynarc archive-list -type '".$archiveType."' -a", $sessid, $shellid, $extraVar);
  if($ret['error']) return $ret;
  $archiveList = $ret['outarr'];
 }
 if(!count($archiveList))
  return array("message"=>"You must specify the archive. (with -aid ARCHIVE_ID, or -ap ARCHIVE_PREFIX)\n","error"=>"INVALID_ARCHIVE");

 include_once($_BASE_PATH."etc/dynarc/extensions/".$extension."/index.php");
 if(!is_callable("dynarcextension_".$extension."_install",true))
  return array("message"=>"Sorry, cannot install extension $extension!\n", "error"=>"INSTALL_FAILED");

 for($c=0; $c < count($archiveList); $c++)
 {
  $archiveInfo = $archiveList[$c];

  $out.= "Install extension ".$extension." into archive ".$archiveInfo['name']."...";
  /* CHECK IF EXTENSION ALREADY INSTALLED */
  $isAlreadyInstalled=false;
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='".$extension."' LIMIT 1");
  if($db->Read())
  {
   if(!$force) return array("message"=>"done! Extension is already installed!\n");
   $isAlreadyInstalled=true;
  }
  $db->Close();
  /* INSTALL EXTENSION */
 
  $ret = call_user_func("dynarcextension_".$extension."_install", $params, $sessid, $shellid, $archiveInfo);
  if($ret['error']) return array('message'=>"failed!\n".$ret['message'], 'error'=>$ret['error']);
  if(!$isAlreadyInstalled)
  {
   $db = new AlpaDatabase();
   $db->RunQuery("INSERT INTO dynarc_archive_extensions(archive_id,extension_name,params) VALUES('"
	.$archiveInfo['id']."','".$extension."','".$params."')");
   $db->Close();
  }
  $out.= "done!\n";
 }

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_uninstallExtension($args, $sessid, $shellid=0, $extraVar=null)
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
  $ret = GShell("dynarc archive-info -id '$archiveId'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid, $extraVar);
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
function dynarc_extensionList($args, $sessid, $shellid=0, $extraVar=null)
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
  $ret = GShell("dynarc archive-info -id '$archiveId'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid, $extraVar);
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
function dynarc_extensionInfo($args, $sessid, $shellid, $extraVar)
{
 global $_BASE_PATH;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $outArr = array('archives'=>array());
 
 $_EXT = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-verbose' : case '--verbose' : $verbose=true; break;
   default : {if(!$_EXT) $_EXT=$args[$c];} break;
  }

 if(!$_EXT) return array('message'=>'You must specify the extension name', 'error'=>'INVALID_EXTENSION');

 /* Check if extension exists */
 if(!file_exists($_BASE_PATH."etc/dynarc/extensions/".$_EXT."/index.php"))
  return array('message'=>"Extension ".$_EXT." is not installed.", 'error'=>"EXTENSION_DOES_NOT_EXISTS");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT ext.archive_id,arc.id,arc.name,arc.tb_prefix FROM dynarc_archive_extensions AS ext INNER JOIN dynarc_archives AS arc ON ext.archive_id=arc.id WHERE ext.extension_name='".$db->Purify($_EXT)."' ORDER BY arc.name ASC");
 while($db->Read())
 {
  $outArr['archives'][] = array('id'=>$db->record['id'], 'ap'=>$db->record['tb_prefix'], 'name'=>$db->record['name']);
 }
 $db->Close();

 if($verbose)
 {
  $out.= "Informations about extension ".$_EXT.":\n";
  if(!count($outArr['archives']))
   $out.= "This extension is installed but no archives using this extension.\n";
  else
  {
   $out.= "List of archives that use this extension.\n";
   for($c=0; $c < count($outArr['archives']); $c++)
   {
    $a = $outArr['archives'][$c];
    $out.= $a['name']." - [".$a['ap']."]\n";
   }
   $out.= "\n".count($outArr['archives'])." archives using this extension.\n"; 
  }
 }
 else if(count($outArr['archives']))
  $out.= count($outArr['archives'])." archives using this extension.\n";
 else
  $out.= "no archives using this extension.\n";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_execFunc($args, $sessid, $shellid=0, $extraVar=null)
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
function dynarc_extfind($args, $sessid, $shellid=0, $extraVar=null)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();
 $extargs = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-aid' : {$_AID=$args[$c+1]; $c++;} break;
   case '-ext' : case '-extension' : {$extension=$args[$c+1]; $c++;} break;
   default: $extargs[] = $args[$c]; break; /* <-- tutti gli altri parametri da passare all'estensione. */
  }

 if(!$extension) return array("message"=>"Error, you must specify the extension. (with: -ext EXTENSION_NAME)", "error"=>"INVALID_EXTENSION");

 /* CHECK FOR ARCHIVE */
 if($_AID)
 {
  $ret = GShell("dynarc archive-info -id '".$_AID."'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($_AP)
 {
  $ret = GShell("dynarc archive-info -prefix '".$_AP."'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 if(!$archiveInfo)
  return array("message"=>"You must specify the archive. (with -aid ARCHIVE_ID, or -ap ARCHIVE_PREFIX)\n","error"=>"INVALID_ARCHIVE");

 /* CHECK IF EXTENSION EXISTS */
 if(!file_exists($_BASE_PATH."etc/dynarc/extensions/".$extension."/index.php"))
  return array("message"=>"Error: extension '".$extension."' does not exists!\n", "error"=>"EXTENSION_DOES_NOT_EXISTS");

 /* EXECUTE FUNCTION */
 include_once($_BASE_PATH."etc/dynarc/extensions/".$extension."/index.php");
 if(is_callable("dynarcextension_".$extension."_find",true))
  return call_user_func("dynarcextension_".$extension."_find", $extargs, $sessid, $shellid, $archiveInfo);
 else
  return array("message"=>"Failed!, unable to execute function 'find' from extension '".$extension."'\n", "error"=>"EXEC_FUNC_FAILED"); 
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_build($args, $sessid, $shellid=0, $extraVar=null)
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
  $ret = GShell("dynarc archive-info -id '$archiveId'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid, $extraVar);
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
function dynarc_unbuild($args, $sessid, $shellid=0, $extraVar=null)
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
  $ret = GShell("dynarc archive-info -id '$archiveId'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid, $extraVar);
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
function dynarc_copyToClipboard($args, $sessid, $shellid=0, $extraVar=null)
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

 $ret = GShell("printenv",$sessid,$shellid, $extraVar);
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
  GShell("export DYNCB-COUNT=".($cpCount+1)." `DYNCB-".($clipboardID-1)."-NAME=".$clipboardName."` DYNCB-".($clipboardID-1)."-ELEMENTS=1 `DYNCB-".($clipboardID-1)."-TAG=".$clipboardTag."`",$sessid,$shellid, $extraVar);
  $outArr['clipboard'] = array('id'=>$clipboardID,'name'=>$clipboardName,'tag'=>$clipboardTag);
 }

 // Insert item or category to clipboard //
 GShell("export DYNCB-".($clipboardID-1)."-ITM-".$cpElements."-AP=".$archivePrefix." DYNCB-".($clipboardID-1)."-ITM-".$cpElements."-ID=$id DYNCB-".($clipboardID-1)."-ITM-".$cpElements."-CAT=$catId DYNCB-".($clipboardID-1)."-ITM-".$cpElements."-QTY=$qty DYNCB-".($clipboardID-1)."-ELEMENTS=".($cpElements+1),$sessid,$shellid, $extraVar);

 $outArr['element'] = array('ap'=>$archivePrefix,'id'=>$id,'cat'=>$catId,'qty'=>$qty);
 
 return array('message'=>"Done!",'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_clipboardList($args, $sessid, $shellid=0, $extraVar=null)
{
 global $_BASE_PATH;

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-tag' : {$clipboardTag=$args[$c+1]; $c++;} break;
  }

 $ret = GShell("printenv",$sessid,$shellid, $extraVar);
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
function dynarc_clipboardInfo($args, $sessid, $shellid=0, $extraVar=null)
{
 global $_BASE_PATH;

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$clipboardID=$args[$c+1]; $c++;} break;
  }

 $ret = GShell("printenv",$sessid,$shellid, $extraVar);
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
function dynarc_clipboardDelete($args, $sessid, $shellid=0, $extraVar=null)
{
 global $_BASE_PATH;

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$clipboardID=$args[$c+1]; $c++;} break;
  }

 $ret = GShell("printenv",$sessid,$shellid, $extraVar);
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

 GShell($qry,$sessid,$shellid, $extraVar);

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
function dynarc_search($args, $sessid, $shellid=0, $extraVar=null)
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
   case '-aps' : {$_APS=$args[$c+1]; $c++;} break; // archive prefixes separated by comma (,)
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

 if($_APS)
  $archivePrefixes = explode(",",$_APS);

 for($c=0; $c < count($archiveTypes); $c++)
 {
  $ret = GShell("dynarc archive-list -a -type `".$archiveTypes[$c]."`",$sessid,$shellid, $extraVar);
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
  $ret = GShell("dynarc archive-info -ap `".$archivePrefixes[$c]."`",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archives[] = $ret['outarr'];
  $archiveByPrefix[$ret['outarr']['prefix']] = $ret['outarr'];
 }

 for($c=0; $c < count($archiveIds); $c++)
 {
  $ret = GShell("dynarc archive-info -id `".$archiveIds[$c]."`",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archives[] = $ret['outarr'];
  $archiveByPrefix[$ret['outarr']['prefix']] = $ret['outarr'];
 }

 if(count($archives) == 1)
 {
  if($catId || $catTag)
  {
   $ret = GShell("dynarc cat-info -ap '".$archives[0]['prefix']."'".($catId ? " -id '".$catId."'" : " -tag '".$catTag."'"),$sessid,$shellid, $extraVar);
   if($ret['error']) return $ret;
   $catId = $ret['outarr']['id'];
  }
  else if($into)
  {
   if(is_numeric($into))
	$intoId = $into;
   else
   {
    $ret = GShell("dynarc cat-info -ap '".$archives[0]['prefix']."' -tag '".$into."'",$sessid,$shellid, $extraVar);
    if($ret['error']) return $ret;
    $intoId = $ret['outarr']['id'];
   }
  }
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
 if(count($archives) == 1)
 {
  if($catId)
   $qry.= " AND cat_id='".$catId."'";
  else if($intoId)
   $qry.= " AND hierarchy LIKE '%,".$intoId.",%'";
 }
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
  $ret = GShell("dynarc item-info -ap '".$db->record['tb_prefix']."' --get-short-description -id ".$db->record['id'].($extget ? " -extget \"".$extget."\"" : "").($get ? " -get \"".$get."\"" : ""),$sessid,$shellid, $extraVar);
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
function dynarc_fastSearch($args, $sessid, $shellid=0, $extraVar=null)
{
 $out = "";
 $debug = "";
 $outArr = array();
 $catId = 0;
 $intoId = 0;

 $_FIELDS = "id,uid,gid,_mod,cat_id,name";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
   case '-into' : {$into=$args[$c+1]; $c++;} break;

   case '-get' : {$get=$args[$c+1]; $c++;} break;	// extra fields to get

   case '-all' : $_ALL=true; break; // Se si specifica una cat prima fa la ricerca li, se non trova nulla cerca in tutto l'archivio.
   case '-verbose' : case '--verbose' : $verbose=true; break;

   default : $_QRY=$args[$c]; break;
  }

 if(!$_AP)  return array('message'=>"Dynarc fast-search error: You must specify the archive prefix.", 'error'=>'INVALID_ARCHIVE');
 if(!$_QRY) return array('message'=>"Dynarc fast-search error: The query is empty.", 'error'=>'EMPTY_QUERY');

 $sessInfo = sessionInfo($sessid);


 $db = new AlpaDatabase();
 // get if archive exists and check perms 
 $debug.= "Check for archive '".$_AP."'...";
 $db->RunQuery("SELECT id,uid,gid,_mod FROM dynarc_archives WHERE tb_prefix='".$_AP."' LIMIT 1");
 if($db->Error)		return array('message'=>$debug."failed!\nMySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
 if(!$db->Read()) 	return array('message'=>$debug."failed!\nArchive with prefix '".$_AP."' does not exists!", 'error'=>'ARCHIVE_DOES_NOT_EXISTS');
 $archiveInfo = $db->record;

 // check perms
 $debug.= "done!\nCheck for permissions...";
 $mod = new GMOD($archiveInfo['_mod'], $archiveInfo['uid'], $archiveInfo['gid']);
 if(!$mod->canRead($sessInfo['uid']))  return array('message'=>$debug."failed!\nPermission denied!", 'error'=>'PERMISSION_DENIED');
 $debug.= "done!\n";

 // check for category
 if($catId || $catTag || $into)
 {
  $debug.= "Check for category ";
  if($catId)
  {
   $debug.= "#".$catId."...";
   $db->RunQuery("SELECT id,uid,gid,_mod,tag,name FROM dynarc_".$_AP."_categories WHERE id='".$catId."' AND trash='0' LIMIT 1");
  }
  else if($catTag)
  {
   $debug.= "with tag '".$catTag."'...";
   $db->RunQuery("SELECT id,uid,gid,_mod,tag,name FROM dynarc_".$_AP."_categories WHERE tag='".$catTag."' AND trash='0' LIMIT 1");
  }
  else if($into)
  {
   $debug.= is_numeric($into) ? "#".$into."..." : "with tag '".$into."'...";
   if(is_numeric($into))
    $db->RunQuery("SELECT id,uid,gid,_mod,tag,name FROM dynarc_".$_AP."_categories WHERE id='".$into."' AND trash='0' LIMIT 1");
   else
    $db->RunQuery("SELECT id,uid,gid,_mod,tag,name FROM dynarc_".$_AP."_categories WHERE tag='".$into."' AND trash='0' LIMIT 1");
  }

  if($db->Error) return array('message'=>$debug."failed!\nMySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
  if(!$db->Read())	return array('message'=>$debug."failed! Category does not exists.", 'error'=>'CATEGORY_DOES_NOT_EXISTS');

  $catInfo = $db->record;
  $debug.= "done!\nCheck category permissions...";
  $mod = new GMOD($catInfo['_mod'], $catInfo['uid'], $catInfo['gid']);
  if(!$mod->canRead())	return array('message'=>$debug."failed! Permission denied.", 'error'=>'PERMISSION_DENIED');

  if($catTag)		$catId = $catInfo['id'];
  else if($into)	$intoId = $catInfo['id'];
 }
 $db->Close();

 $db = new AlpaDatabase();
 // prepare fields
 $hasCode = false;
 $ret = $db->FieldsInfo("dynarc_".$_AP."_items");
 if($ret['code_str'])	$hasCode=true;
 if($ret['code_num'])	$_FIELDS.= ",code_num";
 if($ret['code_str'])	$_FIELDS.= ",code_str";
 if($ret['code_ext'])	$_FIELDS.= ",code_ext";
 if($get)				$_FIELDS.= ",".$get;


 $debug.= "Search...";
 $_QUERIES = array();
 // 1st step (search entire word by name)
 $qry = "SELECT ".$_FIELDS." FROM dynarc_".$_AP."_items WHERE trash='0' AND name='".$db->Purify($_QRY)."'";
 if($catId)			$qry.= " AND cat_id='".$catId."'";
 else if($intoId)	$qry.= " AND hierarchy LIKE '%,".$intoId.",%'";
 $_QUERIES[] = $qry;

 if($hasCode)
 {
  // 2nd step (search by code)
  $qry = "SELECT ".$_FIELDS." FROM dynarc_".$_AP."_items WHERE trash='0' AND code_str='".$db->Purify($_QRY)."'";
  if($catId)			$qry.= " AND cat_id='".$catId."'";
  else if($intoId)	$qry.= " AND hierarchy LIKE '%,".$intoId.",%'";
  $_QUERIES[] = $qry;
 }
 
 if($catId || $intoId)
 {
  // repeat first two steps into all archives
  $_QUERIES[] = "SELECT ".$_FIELDS." FROM dynarc_".$_AP."_items WHERE trash='0' AND name='".$db->Purify($_QRY)."'";
  if($hasCode)
   $_QUERIES[] = "SELECT ".$_FIELDS." FROM dynarc_".$_AP."_items WHERE trash='0' AND code_str='".$db->Purify($_QRY)."'";
 }

 // 3rd step (search partial word)
 $qry = "SELECT ".$_FIELDS." FROM dynarc_".$_AP."_items WHERE trash='0' AND (";
 $qry.= "name LIKE '".$db->Purify($_QRY)."' OR name LIKE '".$db->Purify($_QRY)."%' OR name LIKE '%".$db->Purify($_QRY)."%' OR name LIKE '%"
	.$db->Purify($_QRY)."')";
 if($catId)			$qry.= " AND cat_id='".$catId."'";
 else if($intoId)	$qry.= " AND hierarchy LIKE '%,".$intoId.",%'";
 $_QUERIES[] = $qry;

 if($catId || $intoId)
 {
  // repeat 3rd step into all archives
  $qry = "SELECT ".$_FIELDS." FROM dynarc_".$_AP."_items WHERE trash='0' AND (";
  $qry.= "name LIKE '".$db->Purify($_QRY)."' OR name LIKE '".$db->Purify($_QRY)."%' OR name LIKE '%".$db->Purify($_QRY)."%' OR name LIKE '%"
	.$db->Purify($_QRY)."')";
  $_QUERIES[] = $qry;
 }


 for($c=0; $c < count($_QUERIES); $c++)
 {
  $qry = $_QUERIES[$c];
  //$out.= $qry."\n";
  $db->RunQuery($qry);
  if($db->Error)		return array('message'=>$debug."failed!\nMySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
  if($db->Read())
  {
   $itemInfo = $db->record;
   $debug.= "done!\n";
   // check item perms
   $mod = new GMOD($itemInfo['_mod'], $itemInfo['uid'], $itemInfo['gid']);
   if($mod->canRead($sessInfo['uid']))
   {
    $db->Close();
    $out.= "Found: ".$itemInfo['name']." [#".$itemInfo['id']."]\n";
    $outArr = $itemInfo;
    return array('message'=>$out, 'outarr'=>$outArr);
   }
   $debug.= "1 result found but you cannot read this. Permission denied!\n";
  }
 }

 $db->Close();
 
 $out.= "no items found.\n";
 if($verbose)
  $out.= $debug;

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_generateFunctionsFile($args, $sessid, $shellid, $extraVar)
{
 global $_BASE_PATH;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $contents = "";
 $package = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-package' : {$package=$args[$c+1]; $c++;} break;
   case '--install' : $install=true; break;
  }

 if(!$_AP) return array("message"=>"You must specify the archive prefix. (with: -ap ARCHIVE_PREFIX)", "error"=>"INVALID_ARCHIVE_PREFIX");

 if(file_exists($_BASE_PATH."etc/dynarc/archive_funcs/__".$_AP."/index.php"))
  return array("message"=>"Error: file etc/dynarc/archive_funcs/__".$_AP."/index.php already exists.", "error"=>"FILE_ALREADY_EXISTS");
 
 /* HEADER */  
 $contents = "<"."?php\n";
 $contents.= "/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\n";
 $contents.= "HackTVT Project\n";
 $contents.= "copyright(C) ".date('Y')." Alpatech mediaware - www.alpatech.it\n";
 $contents.= "license GNU/GPL - http://www.gnu.org/copyleft/gpl.html\n";
 $contents.= "Gnujiko 10.1 is free software released under GNU/GPL license\n";
 $contents.= "developed by D. L. Alessandro (alessandro@alpatech.it)\n\n";
 $contents.= "#DATE: ".date('d-m-Y')."\n";
 $contents.= "#PACKAGE: ".$package."\n";
 $contents.= "#DESCRIPTION: \n";
 $contents.= "#VERSION: 2.0beta\n";
 $contents.= "#CHANGELOG: \n";
 $contents.= "#TODO: \n";
 $contents.= "*/\n\n";

 /* FUNCTIONS */
 $contents.= "function dynarcfunction_".$_AP."_oninheritarchive($"."args, $"."sessid, $"."shellid, $"."archiveInfo)\n";
 $contents.= "{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcfunction_".$_AP."_oncreateitem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo)\n";
 $contents.= "{\n return $"."itemInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcfunction_".$_AP."_oncreatecategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n";
 $contents.= "{\n return $"."catInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcfunction_".$_AP."_onedititem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo)\n";
 $contents.= "{\n return $"."itemInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcfunction_".$_AP."_oneditcategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n";
 $contents.= "{\n return $"."catInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcfunction_".$_AP."_ontrashitem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo)\n";
 $contents.= "{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcfunction_".$_AP."_ontrashcategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n";
 $contents.= "{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcfunction_".$_AP."_ondeleteitem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo)\n";
 $contents.= "{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcfunction_".$_AP."_ondeletecategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n";
 $contents.= "{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcfunction_".$_AP."_onrestoreitem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo)\n";
 $contents.= "{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcfunction_".$_AP."_onrestorecategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n";
 $contents.= "{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcfunction_".$_AP."_oncopyitem($"."sessid, $"."shellid, $"."archiveInfo, $"."cloneInfo, $"."srcInfo)\n";
 $contents.= "{\n return $"."cloneInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcfunction_".$_AP."_oncopycategory($"."sessid, $"."shellid, $"."archiveInfo, $"."cloneInfo, $"."srcInfo)\n";
 $contents.= "{\n return $"."cloneInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcfunction_".$_AP."_onmoveitem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."oldItemInfo, $"."newItemInfo)\n";
 $contents.= "{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcfunction_".$_AP."_onmovecategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."oldCatInfo, $"."newCatInfo)\n";
 $contents.= "{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcfunction_".$_AP."_onarchiveempty($"."args, $"."sessid, $"."shellid, $"."archiveInfo)\n";
 $contents.= "{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 if(!file_exists($_BASE_PATH."etc/dynarc/archive_funcs/__".$_AP."/"))
 {
  $ret = GShell("mkdir `etc/dynarc/archive_funcs/__".$_AP."/`",$sessid,$shellid, $extraVar);
  if($ret['error']) return $ret;
 }

 $ret = GShell("echo <![CDATA[".$contents."]]> > etc/dynarc/archive_funcs/__".$_AP."/index.php",$sessid,$shellid, $extraVar);
 if($ret['error']) return $ret;

 $out.= "done!\nThe functions file etc/dynarc/archive_funcs/__".$_AP."/index.php has been generated.";

 if($install)
 {
  // verify if archive exists.
  $ret = GShell("dynarc archive-info -prefix '".$_AP."'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   $out.= "Warning: unable to install this functions file into archive '".$_AP."'.\n".$ret['message'];
  else
  {
   $archiveInfo = $ret['outarr'];
   $ret = GShell("dynarc edit-archive -ap `".$_AP."` --functions-file `etc/dynarc/archive_funcs/__".$_AP."/index.php`",$sessid,$shellid, $extraVar);
   if($ret['error'])
	$out.= "Warning: unable to install this functions file into archive '".$_AP."'.\n".$ret['message'];
   else
	$out.= "Functions file has been installed into archive '".$archiveInfo['name']."'";
  }
 }

 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_generateExtensionFile($args, $sessid, $shellid, $extraVar)
{
 global $_BASE_PATH;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $contents = "";
 $package = "";
 $_EXT = "";
 
 $_ITEM_FIELDS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$_EXT=$args[$c+1]; $c++;} break;
   case '-package' : {$package=$args[$c+1]; $c++;} break;
   case '-desc' : case '-description' : {$description=$args[$c+1]; $c++;} break;
   case '-xml-itemfields' : {$xmlItemFields = $args[$c+1]; $c++;} break;
   /* TODO: da fare... case '-xml-catfields' : {$xmlCatFields = $args[$c+1]; $c++;} break; */

   default : {if(!$_EXT) $_EXT=$args[$c];} break;
  }

 if(!$_EXT) return array("message"=>"You must specify the extension name. (with: -name EXT_NAME)", "error"=>"INVALID_EXTENSION_NAME");

 if(file_exists($_BASE_PATH."etc/dynarc/extensions/".$_EXT."/index.php"))
  return array("message"=>"Error: file etc/dynarc/extensions/".$_EXT."/index.php already exists.", "error"=>"FILE_ALREADY_EXISTS");

 if($xmlItemFields)
 {
  $xmlData = ltrim(rtrim($xmlItemFields));
  $xml = new GXML();
  if($xml->LoadFromString("<xml>".$xmlData."</xml>"))
  {
   $list = $xml->GetElementsByTagName("field");
   for($c=0; $c < count($list); $c++)
	$_ITEM_FIELDS[] = $list[$c]->toArray();
  }
 }

  /* HEADER */  
 $contents = "<"."?php\n";
 $contents.= "/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\n";
 $contents.= "HackTVT Project\n";
 $contents.= "copyright(C) ".date('Y')." Alpatech mediaware - www.alpatech.it\n";
 $contents.= "license GNU/GPL - http://www.gnu.org/copyleft/gpl.html\n";
 $contents.= "Gnujiko 10.1 is free software released under GNU/GPL license\n";
 $contents.= "developed by D. L. Alessandro (alessandro@alpatech.it)\n\n";
 $contents.= "#DATE: ".date('d-m-Y')."\n";
 $contents.= "#PACKAGE: ".$package."\n";
 $contents.= "#DESCRIPTION: ".$description."\n";
 $contents.= "#VERSION: 2.0beta\n";
 $contents.= "#CHANGELOG: \n";
 $contents.= "#TODO: \n";
 $contents.= "*/\n\n";

 $contents.= "global $"."_BASE_PATH;\n\n";

 /* FUNCTIONS */
 $contents.= "function dynarcextension_".$_EXT."_install($"."params, $"."sessid, $"."shellid=0, $"."archiveInfo=null)\n";
 $contents.= "{\n";
 if(count($_ITEM_FIELDS))
 {
  /* QUERY DI INSTALLAZIONE */
  $q = "";
  $_index = array();
  $_unique = array();
  $_fulltext = array();
  for($c=0; $c < count($_ITEM_FIELDS); $c++)
  {
   $field = $_ITEM_FIELDS[$c];
   $q.= ", ADD `".$field['name']."` ".$field['type'].($field['size'] ? "(".$field['size'].")" : "")." NOT NULL";
   switch($field['index'])
   {
	case 'INDEX' : $_index[] = $field['name']; break;
	case 'UNIQUE' : $_unique[] = $field['name']; break;
	case 'FULLTEXT' : $_fulltext[] = $field['name']; break;
   }
  }

  /* INDEXES */
  if(count($_index))		$q.= ", ADD INDEX (`".implode("`,`",$_index)."`)";
  if(count($_unique))		$q.= ", ADD UNIQUE (`".implode("`,`",$_unique)."`)";
  if(count($_fulltext))		$q.= ", ADD FULLTEXT (`".implode("`,`",$_fulltext)."`)";

  $query = "ALTER TABLE `dynarc_\".$"."archiveInfo['prefix'].\"_items`".ltrim($q,",");

  $contents.= " $"."db = new AlpaDatabase();\n";
  $contents.= " $"."db->RunQuery(\"".$query."\");\n";
  $contents.= " $"."db->Close();\n";
 }
 $contents.= "\n return array(\"message\"=>\"".$_EXT." extension has been installed into archive \".$"."archiveInfo['name']);\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_uninstall($"."params, $"."sessid, $"."shellid=0, $"."archiveInfo=null)\n";
 $contents.= "{\n";
 /* QUERY DI DISINSTALLAZIONE */
 if(count($_ITEM_FIELDS))
 {
  $q = "";
  for($c=0; $c < count($_ITEM_FIELDS); $c++)
  {
   $field = $_ITEM_FIELDS[$c];
   $q.= ", DROP `".$field['name']."`";
  }

  $query = "ALTER TABLE `dynarc_\".$"."archiveInfo['prefix'].\"_items`".ltrim($q,",");

  $contents.= " $"."db = new AlpaDatabase();\n";
  $contents.= " $"."db->RunQuery(\"".$query."\");\n";
  $contents.= " $"."db->Close();\n";
 }

 $contents.= " return array(\"message\"=>\"".$_EXT." extension has been removed from archive \".$"."archiveInfo['name']);\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_catset($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n{\n";
 $contents.= " global $"."_BASE_PATH;\n\n";
 $contents.= " for($"."c=0; $"."c < count($"."args); $"."c++)\n";
 $contents.= "  switch($"."args[$"."c])\n  {\n\n";
 /* TODO: cat-set options */
 $contents.= "  }\n\n";
 $contents.= " return $"."catInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_catunset($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n{\n";
 $contents.= " global $"."_BASE_PATH;\n\n";
 $contents.= " for($"."c=0; $"."c < count($"."args); $"."c++)\n";
 $contents.= "  switch($"."args[$"."c])\n  {\n\n";
 $contents.= "  }\n\n";
 $contents.= " return $"."catInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_catget($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n{\n";
 $contents.= " global $"."_BASE_PATH;\n\n";
 $contents.= " for($"."c=0; $"."c < count($"."args); $"."c++)\n";
 $contents.= "  switch($"."args[$"."c])\n  {\n\n";
 $contents.= "  }\n\n";
 $contents.= " return $"."catInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_set($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo, $"."isCategory=false)\n{\n";
 $contents.= " global $"."_BASE_PATH;\n\n";
 $contents.= " if($"."isCategory)\n  return dynarcextension_".$_EXT."_catset($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo);\n\n";
 $contents.= " for($"."c=0; $"."c < count($"."args); $"."c++)\n";
 $contents.= "  switch($"."args[$"."c])\n  {\n";
 /* SET - ITEM ARGUMENTS */
 if(count($_ITEM_FIELDS))
 {
  for($c=0; $c < count($_ITEM_FIELDS); $c++)
  {
   $field = $_ITEM_FIELDS[$c];
   $varArg = str_replace("-","",$field['argument']);
   $contents.= "   case '".$field['argument']."' : { $".$varArg." = $"."args[$"."c+1]; $"."c++;} break;\n";
  }
 }
 $contents.= "  }\n\n";

 if(count($_ITEM_FIELDS))
 {
  $contents.= " $"."db = new AlpaDatabase();\n";
  $contents.= " $"."q = \"\";\n\n";

  for($c=0; $c < count($_ITEM_FIELDS); $c++)
  {
   $field = $_ITEM_FIELDS[$c];
   $varArg = str_replace("-","",$field['argument']);

   $contents.= " if(isset($".$varArg."))			$"."q.= \",".$field['name']."='\".";
   switch($field['type'])
   {
    case 'VARCHAR' : case 'TEXT' : case 'MEDIUMTEXT' : case 'LONGTEXT' : $contents.= "$"."db->Purify($".$varArg.")"; break;
    default : $contents.= "$".$varArg; break;
   }
   $contents.= ".\"'\";\n";
  }
  
  $contents.= "\n if($"."q)\n {\n  $"."db->RunQuery(\"UPDATE dynarc_\".$"."archiveInfo['prefix'].\"_items SET \".ltrim($"."q,\",\").\" WHERE id='\".$"."itemInfo['id'].\"'\");\n";
  $contents.= "  if($"."db->Error) return array('message'=>\"MySQL Error: \".$"."db->Error, 'error'=>'MYSQL_ERROR');\n";
  $contents.= " }\n $"."db->Close();\n";
 }

 $contents.= "\n return $"."itemInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_unset($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo, $"."isCategory=false)\n{\n";
 $contents.= " global $"."_BASE_PATH;\n\n";
 $contents.= " if($"."isCategory)\n  return dynarcextension_".$_EXT."_catunset($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo);\n\n";
 $contents.= " for($"."c=0; $"."c < count($"."args); $"."c++)\n";
 $contents.= "  switch($"."args[$"."c])\n  {\n\n";
 $contents.= "  }\n\n";
 $contents.= " return $"."itemInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_get($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo, $"."isCategory=false)\n{\n";
 $contents.= " global $"."_BASE_PATH;\n\n";
 $contents.= " if($"."isCategory)\n  return dynarcextension_".$_EXT."_catget($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo);\n\n";
 $contents.= " for($"."c=0; $"."c < count($"."args); $"."c++)\n";
 $contents.= "  switch($"."args[$"."c])\n  {\n\n";
 /* GET - ITEM ARGUMENTS */

 $contents.= "  }\n\n";

 if(count($_ITEM_FIELDS))
 {
  $q = "";
  for($c=0; $c < count($_ITEM_FIELDS); $c++)
   $q.= ",".$_ITEM_FIELDS[$c]['name'];

  $contents.= " $"."_FIELDS = \"".ltrim($q,",")."\";\n";
  $contents.= " $"."db = new AlpaDatabase();\n";
  $contents.= " $"."db->RunQuery(\"SELECT \".$"."_FIELDS.\" FROM dynarc_\".$"."archiveInfo['prefix'].\"_items WHERE id='\".$"."itemInfo['id'].\"'\");\n";
  $contents.= " $"."db->Read();\n\n";

  for($c=0; $c < count($_ITEM_FIELDS); $c++)
  {
   $field = $_ITEM_FIELDS[$c];
   switch($field['type'])
   {
	case 'DATE' : $contents.= " $"."itemInfo['".$field['name']."'] = (($"."db->record['".$field['name']."'] != '0000-00-00') && ($"."db->record['".$field['name']."'] != '1970-01-01')) ? $"."db->record['".$field['name']."'] : '';\n"; break;

	case 'DATETIME' : $contents.= " $"."itemInfo['".$field['name']."'] = (($"."db->record['".$field['name']."'] != '0000-00-00 00:00:00') && ($"."db->record['".$field['name']."'] != '1970-01-01 00:00:00')) ? $"."db->record['".$field['name']."'] : '';\n"; break;

    default : $contents.= " $"."itemInfo['".$field['name']."'] = $"."db->record['".$field['name']."'];\n"; break;
   }
  }

  $contents.= "\n $"."db->Close();\n";
 }

 $contents.= "\n return $"."itemInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_export($"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo, $"."isCategory=false)\n{\n";
 $contents.= " global $"."_BASE_PATH;\n\n";
 $contents.= " if($"."isCategory)\n  return ;\n\n";
 $contents.= " $"."xml = \"<".$_EXT." />\";\n";
 $contents.= " return array('xml'=>$"."xml);\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_import($"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo, $"."node, $"."isCategory=false)\n{\n";
 $contents.= " global $"."_BASE_PATH;\n\n";
 $contents.= " if($"."isCategory)\n  return ;\n\n";
 $contents.= " if(!$"."node)\n  return ;\n\n";
 $contents.= " return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_oncreateitem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_oncreatecategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_onedititem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_oneditcategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_ontrashitem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_ontrashcategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_onrestoreitem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_onrestorecategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_ondeleteitem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_ondeletecategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_onmoveitem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."oldItemInfo, $"."newItemInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_onmovecategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."oldCatInfo, $"."newCatInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_oncopyitem($"."sessid, $"."shellid, $"."archiveInfo, $"."srcInfo, $"."cloneInfo)\n{\n";
 if(count($_ITEM_FIELDS))
 {
  $q = "";
  for($c=0; $c < count($_ITEM_FIELDS); $c++)
   $q.= ",".$_ITEM_FIELDS[$c]['name'];

  $contents.= " $"."_FIELDS = \"".ltrim($q,",")."\";\n";
  $contents.= " $"."db = new AlpaDatabase();\n";
  $contents.= " $"."db2 = new AlpaDatabase();\n";
  $contents.= " $"."db->RunQuery(\"SELECT \".$"."_FIELDS.\" FROM dynarc_\".$"."archiveInfo['prefix'].\"_items WHERE id='\".$"."srcInfo['id'].\"'\");\n";
  $contents.= " $"."db->Read();\n\n";

  $set = "";
  for($c=0; $c < count($_ITEM_FIELDS); $c++)
  {
   $set.= ",".$_ITEM_FIELDS[$c]['name']."='\".";
   switch($_ITEM_FIELDS[$c]['type'])
   {
    case 'VARCHAR' : case 'TEXT' : case 'MEDIUMTEXT' : case 'LONGTEXT' : $set.= "$"."db2->Purify($"."db->record['".$_ITEM_FIELDS[$c]['name']."'])"; break;
    default : $set.= "$"."db->record['".$_ITEM_FIELDS[$c]['name']."']"; break;
   }
   $set.= ".\"'";
  }
  
  $contents.= " $"."db2->RunQuery(\"UPDATE dynarc_\".$"."archiveInfo['prefix'].\"_items SET ".ltrim($set,",")." WHERE id='\".$"."cloneInfo['id'].\"'\");\n";
  $contents.= " $"."db2->Close();\n";
  $contents.= " $"."db->Close();\n";
 }
 $contents.= "\n return $"."cloneInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_oncopycategory($"."sessid, $"."shellid, $"."archiveInfo, $"."srcInfo, $"."cloneInfo)\n{\n return $"."cloneInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_onarchiveempty($"."args, $"."sessid, $"."shellid, $"."archiveInfo)\n{\n";
 $contents.= "\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 if(!file_exists($_BASE_PATH."etc/dynarc/extensions/".$_EXT."/"))
 {
  $ret = GShell("mkdir `etc/dynarc/extensions/".$_EXT."/`",$sessid,$shellid, $extraVar);
  if($ret['error']) return $ret;
 }

 $ret = GShell("echo <![CDATA[".$contents."]]> > etc/dynarc/extensions/".$_EXT."/index.php",$sessid,$shellid, $extraVar);
 if($ret['error']) return $ret;

 $out.= "done!\nThe extension file etc/dynarc/extensions/".$_EXT."/index.php has been generated.";


 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_generateExtensionTLFile($args, $sessid, $shellid, $extraVar)
{
 global $_BASE_PATH;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $contents = "";
 $package = "";
 $_EXT = "";
 
 $_ITEM_FIELDS = array();
 $_FIELDS = "id,item_id,ordering"; // lista dei campi db separati da una virgola

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$_EXT=$args[$c+1]; $c++;} break;
   case '-package' : {$package=$args[$c+1]; $c++;} break;
   case '-desc' : case '-description' : {$description=$args[$c+1]; $c++;} break;
   case '-xml-itemfields' : {$xmlItemFields = $args[$c+1]; $c++;} break;
   /* TODO: da fare... case '-xml-catfields' : {$xmlCatFields = $args[$c+1]; $c++;} break; */

   default : {if(!$_EXT) $_EXT=$args[$c];} break;
  }

 if(!$_EXT) return array("message"=>"You must specify the extension name. (with: -name EXT_NAME)", "error"=>"INVALID_EXTENSION_NAME");

 if(file_exists($_BASE_PATH."etc/dynarc/extensions/".$_EXT."/index.php"))
  return array("message"=>"Error: file etc/dynarc/extensions/".$_EXT."/index.php already exists.", "error"=>"FILE_ALREADY_EXISTS");

 if($xmlItemFields)
 {
  $xmlData = ltrim(rtrim($xmlItemFields));
  $xml = new GXML();
  if($xml->LoadFromString("<xml>".$xmlData."</xml>"))
  {
   $list = $xml->GetElementsByTagName("field");
   for($c=0; $c < count($list); $c++)
   {
	$f = $list[$c]->toArray();
	$f['type'] = strtoupper($f['type']);
	$f['index'] = strtoupper($f['index']);
	$_ITEM_FIELDS[] = $f;
	$_FIELDS.= ",".$f['name'];
   }
  }
 }

  /* HEADER */  
 $contents = "<"."?php\n";
 $contents.= "/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\n";
 $contents.= "HackTVT Project\n";
 $contents.= "copyright(C) ".date('Y')." Alpatech mediaware - www.alpatech.it\n";
 $contents.= "license GNU/GPL - http://www.gnu.org/copyleft/gpl.html\n";
 $contents.= "Gnujiko 10.1 is free software released under GNU/GPL license\n";
 $contents.= "developed by D. L. Alessandro (alessandro@alpatech.it)\n\n";
 $contents.= "#DATE: ".date('d-m-Y')."\n";
 $contents.= "#PACKAGE: ".$package."\n";
 $contents.= "#DESCRIPTION: ".$description."\n";
 $contents.= "#VERSION: 2.0beta\n";
 $contents.= "#CHANGELOG: \n";
 $contents.= "#TODO: \n";
 $contents.= "*/\n\n";

 $contents.= "global $"."_BASE_PATH;\n\n";

 /* FUNCTIONS */
 $contents.= "function dynarcextension_".$_EXT."_install($"."params, $"."sessid, $"."shellid=0, $"."archiveInfo=null)\n";
 $contents.= "{\n";
 if(count($_ITEM_FIELDS))
 {
  /* QUERY DI INSTALLAZIONE */
  $q = "";
  $_index = array("item_id","ordering");
  $_unique = array();
  $_fulltext = array();
  for($c=0; $c < count($_ITEM_FIELDS); $c++)
  {
   $field = $_ITEM_FIELDS[$c];
   $q.= ",\n	`".$field['name']."` ".$field['type'].($field['size'] ? "(".$field['size'].")" : "")." NOT NULL";
   switch($field['index'])
   {
	case 'INDEX' : $_index[] = $field['name']; break;
	case 'UNIQUE' : $_unique[] = $field['name']; break;
	case 'FULLTEXT' : $_fulltext[] = $field['name']; break;
   }
  }

  $q.= ",\n    PRIMARY KEY(`id`)";

  /* INDEXES */
  if(count($_index))		$q.= ",\n	INDEX (`".implode("`,`",$_index)."`)";
  if(count($_unique))		$q.= ",\n	UNIQUE (`".implode("`,`",$_unique)."`)";
  if(count($_fulltext))		$q.= ",\n	FULLTEXT (`".implode("`,`",$_fulltext)."`)";

  $query = "CREATE TABLE IF NOT EXISTS `dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT."` (\n";
  $query.= "	`id` INT(11) NOT NULL AUTO_INCREMENT,\n";
  $query.= "	`item_id` INT(11) NOT NULL,\n";
  $query.= "	`ordering` INT(11) NOT NULL".$q."\n )";

  $contents.= " $"."db = new AlpaDatabase();\n";
  $contents.= " $"."db->RunQuery(\"".$query."\");\n";
  $contents.= " $"."db->Close();\n";
 }
 $contents.= "\n return array(\"message\"=>\"".$_EXT." extension has been installed into archive \".$"."archiveInfo['name']);\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_uninstall($"."params, $"."sessid, $"."shellid=0, $"."archiveInfo=null)\n";
 $contents.= "{\n";
 /* QUERY DI DISINSTALLAZIONE */

 $query = "DROP TABLE IF EXISTS `dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT."`";

 $contents.= " $"."db = new AlpaDatabase();\n";
 $contents.= " $"."db->RunQuery(\"".$query."\");\n";
 $contents.= " $"."db->Close();\n";

 $contents.= " return array(\"message\"=>\"".$_EXT." extension has been removed from archive \".$"."archiveInfo['name']);\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_catset($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n{\n";
 $contents.= " global $"."_BASE_PATH;\n\n";
 $contents.= " for($"."c=0; $"."c < count($"."args); $"."c++)\n";
 $contents.= "  switch($"."args[$"."c])\n  {\n\n";
 /* TODO: cat-set options */
 $contents.= "  }\n\n";
 $contents.= " return $"."catInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_catunset($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n{\n";
 $contents.= " global $"."_BASE_PATH;\n\n";
 $contents.= " for($"."c=0; $"."c < count($"."args); $"."c++)\n";
 $contents.= "  switch($"."args[$"."c])\n  {\n\n";
 $contents.= "  }\n\n";
 $contents.= " return $"."catInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_catget($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n{\n";
 $contents.= " global $"."_BASE_PATH;\n\n";
 $contents.= " for($"."c=0; $"."c < count($"."args); $"."c++)\n";
 $contents.= "  switch($"."args[$"."c])\n  {\n\n";
 $contents.= "  }\n\n";
 $contents.= " return $"."catInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_set($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo, $"."isCategory=false)\n{\n";
 $contents.= " global $"."_BASE_PATH;\n\n";
 $contents.= " if($"."isCategory)\n  return dynarcextension_".$_EXT."_catset($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo);\n\n";
 $contents.= " for($"."c=0; $"."c < count($"."args); $"."c++)\n";
 $contents.= "  switch($"."args[$"."c])\n  {\n";
 /* SET - ITEM ARGUMENTS */
 $contents.= "   case 'id' : { $"."id"." = $"."args[$"."c+1]; $"."c++;} break;\n";

 if(count($_ITEM_FIELDS))
 {
  for($c=0; $c < count($_ITEM_FIELDS); $c++)
  {
   $field = $_ITEM_FIELDS[$c];
   $varArg = str_replace("-","",$field['argument']);
   $contents.= "   case '".$field['argument']."' : { $".$varArg." = $"."args[$"."c+1]; $"."c++;} break;\n";
  }
 }
 $contents.= "   case 'ordering' : { $"."ordering"." = $"."args[$"."c+1]; $"."c++;} break;\n";
 $contents.= "   case 'xml' : { $"."xmldata"." = $"."args[$"."c+1]; $"."c++;} break;\n\n";
 $contents.= "   case 'serialize' : { $"."serialize"." = $"."args[$"."c+1]; $"."c++;} break;\n";
 $contents.= "  }\n\n";

 $contents.= " /* IF SERIALIZE */ \n";
 $contents.= " if($"."serialize)\n";
 $contents.= " {\n";
 $contents.= "  $"."ser = explode(\",\",$"."serialize); \n";
 $contents.= "  $"."db = new AlpaDatabase(); \n";
 $contents.= "  for($"."c=0; $"."c < count($"."ser); $"."c++) \n";
 $contents.= "   $"."db->RunQuery(\"UPDATE dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT." SET ordering='\".($"."c+1).\"' WHERE id='\".$"."ser[$"."c].\"' AND item_id='\".$"."itemInfo['id'].\"'\"); \n";
 $contents.= "  $"."db->Close(); \n";
 $contents.= "  return $"."itemInfo; \n";
 $contents.= " }\n\n";

 /* IF XML DATA */
 $contents.= " if($"."xmldata)\n";
 $contents.= " {\n";
 $contents.= "  /* INSERT OR EDIT RECORDS FROM XML */\n\n";
  
 $contents.= "  $"."db = new AlpaDatabase();\n";
 $contents.= "  // get next ordering\n";
 $contents.= "  $"."db->RunQuery(\"SELECT ordering FROM dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT." WHERE item_id='\".$"."itemInfo['id'].\"' ORDER BY ordering DESC LIMIT 1\"); \n";
 $contents.= "  if($"."db->Read())    $"."ordering = $"."db->record['ordering']+1; \n";
 $contents.= "  else 			  	 $"."ordering=1;\n";
 $contents.= "  $"."db->Close();\n\n";

 $contents.= "  $"."itemInfo['last_".$_EXT."_items'] = array();\n";
 $contents.= "  $"."xmldata = ltrim(rtrim($"."xmldata));\n";
 $contents.= "  $"."xml = new GXML();\n";
 $contents.= "  if(!$"."xml->LoadFromString(\"<xml>\".$"."xmldata.\"</xml>\"))\n";
 $contents.= "   return array('message'=>\"EXT.".$_EXT." error: XML parse error\", 'error'=>\"XML_PARSE_ERROR\");\n";
 $contents.= "  $"."list = $"."xml->GetElementsByTagName(\"item\");\n";
 $contents.= "  for($"."c=0; $"."c < count($"."list); $"."c++)\n";
 $contents.= "  {\n";
 $contents.= "   $"."db = new AlpaDatabase();\n";
 $contents.= "   $"."item = $"."list[$"."c]->toArray();\n";
 $contents.= "   if($"."item['id'])\n";
 $contents.= "   {\n";
 $contents.= "	$"."db->RunQuery(\"UPDATE dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT." SET";

 $q = "";
 for($c=0; $c < count($_ITEM_FIELDS); $c++)
 {
  $field = $_ITEM_FIELDS[$c];
  $varArg = str_replace("-","",$field['argument']);
  switch($field['type'])
  {
   case 'VARCHAR' : case 'TEXT' : case 'MEDIUMTEXT' : case 'LONGTEXT' : $q.= ", ".$field['name']."='\".$"."db->Purify($"."item['".$varArg."']).\"'"; break;
   default : $q.= ", ".$field['name']."='\".$"."item['".$varArg."'].\"'"; break;
  }
 }
 $contents.= ltrim($q,",")."\".($"."item['ordering'] ? \",ordering='\".$"."item['ordering'].\"'\" : \"\").\" WHERE id='\".$"."item['id'].\"'\");\n";

 $contents.= "	if($"."db->Error) return array('message'=>\"MySQL Error: \".$"."db->Error, 'error'=>\"MYSQL_ERROR\"); \n";
 $contents.= "   }\n";
 $contents.= "   else\n";
 $contents.= "   {\n";
 $contents.= "    $"."db->RunQuery(\"INSERT INTO dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT."(".substr($_FIELDS,3).") VALUES('\".$"."itemInfo['id'].\"','\".($"."item['ordering'] ? $"."item['ordering'] : $"."ordering).\"'";
 for($c=0; $c < count($_ITEM_FIELDS); $c++)
 {
  $field = $_ITEM_FIELDS[$c];
  $varArg = str_replace("-","",$field['argument']);
  switch($field['type'])
  {
   case 'VARCHAR' : case 'TEXT' : case 'MEDIUMTEXT' : case 'LONGTEXT' : $contents.= ",'\".$"."db->Purify($"."item['".$varArg."']).\"'"; break;
   default : $contents.= ",'\".$"."item['".$varArg."'].\"'"; break;
  }
 }
 $contents.= ")\");\n";

 $contents.= "    if($"."db->Error) return array('message'=>\"MySQL Error: \".$"."db->Error, 'error'=>\"MYSQL_ERROR\"); \n";
 $contents.= "    $"."recid = $"."db->GetInsertId(); \n";
 $contents.= "    $"."itemInfo['last_".$_EXT."_items'][] = array('id'=>$"."recid, 'ordering'=>$"."ordering";
 for($c=0; $c < count($_ITEM_FIELDS); $c++)
 {
  $field = $_ITEM_FIELDS[$c];
  $varArg = str_replace("-","",$field['argument']);
  $contents.= ", '".$field['name']."'=>$"."item['".$varArg."']";
 }
 $contents.= "); \n";

 $contents.= "    $"."ordering++;\n";
 $contents.= "   }\n";
 $contents.= "   $"."db->Close();\n";
 $contents.= "  }\n";
 $contents.= " }\n";


 $contents.= " else if($"."id) \n";
 $contents.= " {\n";
 $contents.= "  /* EDIT SINGLE RECORD */\n";
 $contents.= "  $"."db = new AlpaDatabase(); \n";
 $contents.= "  $"."db->RunQuery(\"SELECT ".$_FIELDS." FROM dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT." WHERE id='\".$"."id.\"'\"); \n";
 $contents.= "  $"."db->Read(); \n";
 $contents.= "  $"."itemInfo['last_".$_EXT."_item'] = $"."db->record; \n";
 $contents.= "  $"."q = \"\"; \n";
 for($c=0; $c < count($_ITEM_FIELDS); $c++)
 {
  $field = $_ITEM_FIELDS[$c];
  $varArg = str_replace("-","",$field['argument']);
  $contents.= "  if(isset($".$varArg."))			{ $"."q.= \",".$field['name']."='\".";
  switch($field['type'])
  {
   case 'VARCHAR' : case 'TEXT' : case 'MEDIUMTEXT' : case 'LONGTEXT' : $contents.= "$"."db->Purify($".$varArg.")"; break;
   default : $contents.= "$".$varArg; break;
  }
  $contents.= ".\"'\";			$"."itemInfo['last_".$_EXT."_item']['".$field['name']."'] = $".$varArg."; }\n";
 }
 $contents.= " \n";
 $contents.= "  if($"."q) \n";
 $contents.= "   $"."db->RunQuery(\"UPDATE dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT." SET \".ltrim($"."q,\",\").\" WHERE id='\".$"."id.\"'\"); \n";
 $contents.= "  if($"."db->Error) return array('message'=>\"MySQL Error: \".$"."db->Error, 'error'=>\"MYSQL_ERROR\"); \n";
 $contents.= "  $"."db->Close(); \n";
 $contents.= " } \n";
 $contents.= " else\n";
 $contents.= " {\n";
 $contents.= "  /* NEW RECORD */ \n";
 $contents.= "  $"."db = new AlpaDatabase(); \n";
 $contents.= "  if(!isset($"."ordering)) \n";
 $contents.= "  { \n";
 $contents.= "   $"."db->RunQuery(\"SELECT ordering FROM dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT." WHERE item_id='\".$"."itemInfo['id'].\"' ORDER BY ordering DESC LIMIT 1\"); \n";
 $contents.= "   if($"."db->Read())    $"."ordering = $"."db->record['ordering']+1; \n";
 $contents.= "   else 			  $"."ordering=1; \n";
 $contents.= "  } \n\n";
 $contents.= "  $"."db->RunQuery(\"INSERT INTO dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT."(".substr($_FIELDS,3).") VALUES('\".$"."itemInfo['id'].\"','\".$"."ordering.\"'";
 for($c=0; $c < count($_ITEM_FIELDS); $c++)
 {
  $field = $_ITEM_FIELDS[$c];
  $varArg = str_replace("-","",$field['argument']);
  switch($field['type'])
  {
   case 'VARCHAR' : case 'TEXT' : case 'MEDIUMTEXT' : case 'LONGTEXT' : $contents.= ",'\".$"."db->Purify($".$varArg.").\"'"; break;
   default : $contents.= ",'\".$".$varArg.".\"'"; break;
  }
 }
 $contents.= ")\");\n";

 $contents.= "  if($"."db->Error) return array('message'=>\"MySQL Error: \".$"."db->Error, 'error'=>\"MYSQL_ERROR\"); \n";
 $contents.= "  $"."recid = $"."db->GetInsertId(); \n";
 $contents.= "  $"."itemInfo['last_".$_EXT."_item'] = array('id'=>$"."recid, 'ordering'=>$"."ordering";
 for($c=0; $c < count($_ITEM_FIELDS); $c++)
 {
  $field = $_ITEM_FIELDS[$c];
  $varArg = str_replace("-","",$field['argument']);
  $contents.= ", '".$field['name']."'=>$".$varArg;
 }
 $contents.= "); \n";
 $contents.= "  $"."db->Close(); \n";
 $contents.= " }\n\n";
 $contents.= " return $"."itemInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_unset($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo, $"."isCategory=false)\n{\n";
 $contents.= " global $"."_BASE_PATH;\n\n";
 $contents.= " if($"."isCategory)\n  return dynarcextension_".$_EXT."_catunset($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo);\n\n";
 $contents.= " for($"."c=0; $"."c < count($"."args); $"."c++)\n";
 $contents.= "  switch($"."args[$"."c])\n  {\n\n";
 $contents.= "   case 'id' : { $"."id=$"."args[$"."c+1]; $"."c++;} break;\n";
 $contents.= "   case 'ids' : { $"."ids=$"."args[$"."c+1]; $"."c++;} break;\n";
 $contents.= "   case 'all' : $"."all=true; break;\n";
 $contents.= "  }\n\n";

 $contents.= " $"."db = new AlpaDatabase();\n";
 $contents.= " if($"."ids)\n";
 $contents.= " {\n";
 $contents.= "  $"."list = explode(\",\",$"."ids);\n";
 $contents.= "  for($"."c=0; $"."c < count($"."list); $"."c++)\n";
 $contents.= "   $"."db->RunQuery(\"DELETE FROM dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT." WHERE id='\".$"."list[$"."c].\"'\");\n";
 $contents.= " }\n";
 $contents.= " else if($"."id) 		$"."db->RunQuery(\"DELETE FROM dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT." WHERE id='\".$"."id.\"'\");\n";
 $contents.= " else if($"."all)		$"."db->RunQuery(\"DELETE FROM dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT." WHERE item_id='\".$"."itemInfo['id'].\"'\");\n";
 $contents.= " $"."db->Close();\n\n";

 $contents.= " return $"."itemInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_get($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo, $"."isCategory=false)\n{\n";
 $contents.= " global $"."_BASE_PATH;\n\n";
 $contents.= " if($"."isCategory)\n  return dynarcextension_".$_EXT."_catget($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo);\n\n";
 $contents.= " $"."limit = 100;\n";
 $contents.= " $"."_FIELDS = \"id\";\n";

 $contents.= " for($"."c=0; $"."c < count($"."args); $"."c++)\n";
 $contents.= "  switch($"."args[$"."c])\n  {\n\n";
 $contents.= "   case 'limit' : { $"."limit=$"."args[$"."c+1]; $"."c++;} break;\n";
 $contents.= "  }\n\n";

 $contents.= " $"."itemInfo['".$_EXT."'] = array(); \n";
 $contents.= " $"."_FIELDS.= \"";
 for($c=0; $c < count($_ITEM_FIELDS); $c++)
  $contents.= ",".$_ITEM_FIELDS[$c]['name'];
 $contents.= "\"; \n";

 $contents.= " $"."db = new AlpaDatabase();\n";
 $contents.= " $"."db->RunQuery(\"SELECT \".$"."_FIELDS.\" FROM dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT." WHERE item_id='\".$"."itemInfo['id'].\"' ORDER BY ordering ASC LIMIT \".$"."limit);\n";
 $contents.= " while($"."db->Read())\n {\n";
 $contents.= "  $"."a = array('id'=>$"."db->record['id']);\n";

 for($c=0; $c < count($_ITEM_FIELDS); $c++)
 {
  $field = $_ITEM_FIELDS[$c];
  switch($field['type'])
  {
	case 'DATE' : $contents.= "  $"."a['".$field['name']."'] = (($"."db->record['".$field['name']."'] != '0000-00-00') && ($"."db->record['".$field['name']."'] != '1970-01-01')) ? $"."db->record['".$field['name']."'] : '';\n"; break;

	case 'DATETIME' : $contents.= "  $"."a['".$field['name']."'] = (($"."db->record['".$field['name']."'] != '0000-00-00 00:00:00') && ($"."db->record['".$field['name']."'] != '1970-01-01 00:00:00')) ? $"."db->record['".$field['name']."'] : '';\n"; break;

    default : $contents.= "  $"."a['".$field['name']."'] = $"."db->record['".$field['name']."'];\n"; break;
  }
 }

 $contents.= "  $"."itemInfo['".$_EXT."'][] = $"."a;\n";
 $contents.= " }\n";
 $contents.= "\n $"."db->Close();\n";

 $contents.= "\n return $"."itemInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_export($"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo, $"."isCategory=false)\n{\n";
 $contents.= " global $"."_BASE_PATH;\n\n";
 $contents.= " if($"."isCategory)\n  return ;\n\n";
 $contents.= " $"."xml = \"<".$_EXT." />\";\n";
 $contents.= " return array('xml'=>$"."xml);\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_import($"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo, $"."node, $"."isCategory=false)\n{\n";
 $contents.= " global $"."_BASE_PATH;\n\n";
 $contents.= " if($"."isCategory)\n  return ;\n\n";
 $contents.= " if(!$"."node)\n  return ;\n\n";
 $contents.= " return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_oncreateitem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_oncreatecategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_onedititem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_oneditcategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_ontrashitem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_ontrashcategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_onrestoreitem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_onrestorecategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_ondeleteitem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."itemInfo)\n{\n";
 $contents.= " $"."db = new AlpaDatabase();\n";
 $contents.= " $"."db->RunQuery(\"DELETE FROM dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT." WHERE item_id='\".$"."itemInfo['id'].\"'\");\n";
 $contents.= " $"."db->Close();\n";

 $contents.= " return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_ondeletecategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."catInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_onmoveitem($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."oldItemInfo, $"."newItemInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_onmovecategory($"."args, $"."sessid, $"."shellid, $"."archiveInfo, $"."oldCatInfo, $"."newCatInfo)\n{\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_oncopyitem($"."sessid, $"."shellid, $"."archiveInfo, $"."srcInfo, $"."cloneInfo)\n{\n";
 if(count($_ITEM_FIELDS))
 {
  $q = "";
  for($c=0; $c < count($_ITEM_FIELDS); $c++)
   $q.= ",".$_ITEM_FIELDS[$c]['name'];

  $contents.= " $"."_FIELDS = \"".ltrim($q,",")."\";\n";
  $contents.= " $"."ordering=1;\n";
  $contents.= " $"."db = new AlpaDatabase();\n";
  $contents.= " $"."db2 = new AlpaDatabase();\n";
  $contents.= " $"."db->RunQuery(\"SELECT \".$"."_FIELDS.\" FROM dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT." WHERE item_id='\".$"."srcInfo['id'].\"' ORDER BY ordering ASC\");\n";
  $contents.= " while($"."db->Read())\n {\n";

  $contents.= "  $"."db2->RunQuery(\"INSERT INTO dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT."(".substr($_FIELDS,3).") VALUES('\".$"."cloneInfo['id'].\"','\".$"."ordering.\"'";
  for($c=0; $c < count($_ITEM_FIELDS); $c++)
  {
   $field = $_ITEM_FIELDS[$c];
   switch($field['type'])
   {
    case 'VARCHAR' : case 'TEXT' : case 'MEDIUMTEXT' : case 'LONGTEXT' : $contents.= ",'\".$"."db2->Purify($"."db->record['".$field['name']."']).\"'"; break;
    default : $contents.= ",'\".$"."db->record['".$field['name']."'].\"'"; break;
   }
  }
  $contents.= ")\");\n";
  $contents.= "  $"."ordering++;\n";
  $contents.= " }\n";
  $contents.= " $"."db2->Close();\n";
  $contents.= " $"."db->Close();\n";
 }
 $contents.= "\n return $"."cloneInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_oncopycategory($"."sessid, $"."shellid, $"."archiveInfo, $"."srcInfo, $"."cloneInfo)\n{\n return $"."cloneInfo;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 $contents.= "function dynarcextension_".$_EXT."_onarchiveempty($"."args, $"."sessid, $"."shellid, $"."archiveInfo)\n{\n";
 $contents.= " $"."db = new AlpaDatabase();\n";
 $contents.= " $"."db->RunQuery(\"TRUNCATE TABLE dynarc_\".$"."archiveInfo['prefix'].\"_".$_EXT."\");\n";
 $contents.= " $"."db->Close();\n";

 $contents.= "\n return true;\n}\n";
 $contents.= "//-------------------------------------------------------------------------------------------------------------------//\n";

 if(!file_exists($_BASE_PATH."etc/dynarc/extensions/".$_EXT."/"))
 {
  $ret = GShell("mkdir `etc/dynarc/extensions/".$_EXT."/`",$sessid,$shellid, $extraVar);
  if($ret['error']) return $ret;
 }

 $ret = GShell("echo <![CDATA[".$contents."]]> > etc/dynarc/extensions/".$_EXT."/index.php",$sessid,$shellid, $extraVar);
 if($ret['error']) return $ret;

 $out.= "done!\nThe extension file etc/dynarc/extensions/".$_EXT."/index.php has been generated.";


 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_crossSearch($args, $sessid, $shellid=0, $extraVar=null)
{
 global $_BASE_PATH;

 $out = "";
 $outArr = array("count"=>0, "items"=>array());

 $sessInfo = sessionInfo($sessid);
 
 $joinQuery = "ext.item_id = item.id";
 $_ARCHIVE_FIELDS = array('id','uid','gid','_mod','cat_id','lnk_id','lnkarc_id','name','ctime','published','hierarchy'); // Default fields
 $_EXTENSION_FIELDS = array();
 $_SUM_FIELDS = array();
 $_EXT_SUM_FIELDS = array();
 
 $includeTrash=false;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;							// l'archivio dove viene effettuata la ricerca
   case '-ap2' : {$_AP2=$args[$c+1]; $c++;} break;  						// l'archivio di riferimento da ritornare
   case '-af' : case '-get' : {$archFields = $args[$c+1]; $c++;} break;
   case '-onlyget' : case '--only-get' : {
		 $_ARCHIVE_FIELDS = array();
		 $archFields = $args[$c+1]; 
		 $c++;
		} break;
   case '-xf' : {$extFields = $args[$c+1]; $c++;} break;
   case '-extget' : {$extget=$args[$c+1]; $c++;} break;
   case '-ext' : case '-extension' : {$_EXT=$args[$c+1]; $c++;} break;
   case '-where' : {$query=$args[$c+1]; $c++;} break;
   case '-j' : case '-join' : {
	 $x = explode("=",$args[$c+1]);
	 if($x[0] && $x[1])
	  $joinQuery = "ext.".$x[0]." = item.".$x[1];
	 else
	  $joinQuery = "";
	 $c++;
	} break;
   case 'tb' : case 'tb1' : {$table1=$args[$c+1]; $c++;} break;		// è possibile in alternativa specificare la tabella dove effettuare la ricerca.
   case 'tb2' : {$table2=$args[$c+1]; $c++;} break;					// è possibile in alternativa specificare la tabella di rif. da ritornare.

   case '--order-by' : case '-orderby' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;

   case '--get-all-arch-fields' : $getAllArchFields=true; break;
   case '--get-all-ext-fields' : $getAllExtFields=true; break;
   case '--get-all-fields' : {$getAllArchFields=true; $getAllExtFields=true;} break;
   case '--get-sum-fields' : {$sumFields = $args[$c+1]; $c++;} break; // sum fields separated by comma
   case '--get-extsum-fields' : {$extSumFields = $args[$c+1]; $c++;} break; // extension sum fields separated by comma
   case '--no-get-default-fields' : $noGetDefaultFields=true; break;
   case '--verbose' : case '-verbose' : $verbose=true; break;
   case '--include-trash' : $includeTrash=true; break;
  }

 /* ESEMPIO DI QUERY */
 /* dynarc cross-search -ap rubrica -ext contacts -get `taxcode,vatnumber` -extget `address,city` -where `ext.province='PN' AND item.iscompany='0'` --order-by `name ASC` --verbose */

 if(!$joinQuery) return array("message"=>"Invalid join query.", "error"=>"INVALID_JOIN_QUERY");
 if(!$query) return array("message"=>"Invalid query", "error"=>"INVALID_EXT_QUERY");
 if(!$_EXT && !$table1 && !$table2) return array("message"=>"You must specify the extension name.", "error"=>"INVALID_EXTENSION");

 if($sumFields) $_SUM_FIELDS = explode(",",$sumFields);
 if($extSumFields) $_EXT_SUM_FIELDS = explode(",",$extSumFields);

 include_once($_BASE_PATH."etc/dynarc/items.php");

 if(!$includeTrash)
 {
  $query = "item.trash='0'".($query ? " AND ".$query : "");
 }

  /* PREPARE ARCHIVE FIELDS */

 if($noGetDefaultFields)
  $_ARCHIVE_FIELDS = array();

 $db = new AlpaDatabase();
 if(!$table2) $table2 = "dynarc_".($_AP2 ? $_AP2 : $_AP)."_items";
 $fields = $db->FieldsInfo($table2);
 if($getAllArchFields){$_ARCHIVE_FIELDS = array(); while(list($k,$v) = each($fields)) {$_ARCHIVE_FIELDS[] = $k;}}
 else if(isset($archFields))
 {
  if(!$archFields) $_ARCHIVE_FIELDS = array(); // empty default fields
  else
  {
   $x = explode(",",$archFields);
   for($c=0; $c < count($x); $c++)
   {
    $field = trim($x[$c]); if(!$field) continue;
    if(!in_array($field, $_ARCHIVE_FIELDS)) $_ARCHIVE_FIELDS[] = $field;
   }
  }
 }



  /* PREPARE EXTENSION FIELDS */
  $db = new AlpaDatabase();
  if(!$table1) $table1 = "dynarc_".$_AP."_".$_EXT;
  $fields = $db->FieldsInfo($table1);
  if($getAllExtFields)
  {
   $_EXTENSION_FIELDS = array(); while(list($k,$v) = each($fields)){if(!in_array($k, $_ARCHIVE_FIELDS)) $_EXTENSION_FIELDS[] = $k;}
  }
  else if($extFields)
  {
   $x = explode(",",$extFields);
   for($c=0; $c < count($x); $c++)
   {
    $field = trim($x[$c]); if(!$field) continue;
    if(!in_array($field, $_EXTENSION_FIELDS)) $_EXTENSION_FIELDS[] = $field;
   }
  }
  $db->Close();


 $db = new AlpaDatabase();
 /* PREPARE QUERY */
 $qry = "";
 $sumQry = "";
 $q = ""; 
 for($c=0; $c < count($_EXTENSION_FIELDS); $c++) 	$q.= ",ext.".$_EXTENSION_FIELDS[$c];
 for($c=0; $c < count($_ARCHIVE_FIELDS); $c++) 		$q.= ",item.".$_ARCHIVE_FIELDS[$c];
 for($c=0; $c < count($_SUM_FIELDS); $c++)			$sumQry.= ",SUM(item.".$_SUM_FIELDS[$c].") AS ".$_SUM_FIELDS[$c];
 for($c=0; $c < count($_EXT_SUM_FIELDS); $c++)		$sumQry.= ",SUM(ext.".$_EXT_SUM_FIELDS[$c].") AS ".$_EXT_SUM_FIELDS[$c];
 if($q) $qry.= " ".ltrim($q,",");
 if($sumQry)	$sumQry = " ".ltrim($sumQry,",");

 $qry.= " FROM ".$table1." AS ext INNER JOIN ".$table2." AS item ON ".$joinQuery." WHERE ".$query;
 $countQry = " FROM ".$table1." AS ext INNER JOIN ".$table2." AS item ON ".$joinQuery." WHERE ".$query;
 if($sumQry) $sumQry.= " FROM ".$table1." AS ext INNER JOIN ".$table2." AS item ON ".$joinQuery." WHERE ".$query;

 /* COUNT QUERY */
 $db->RunQuery("SELECT DISTINCT COUNT(*)".$countQry);
 $db->Read();
 if($db->Error) return array('message'=>$out."\nMySQL Error:".$db->Error."\nLASTQRY: ".$db->lastQuery, 'error'=>"MYSQL_ERROR");
 $outArr['count'] = $db->record[0];

 /* SUM QUERY */
 if($sumQry)
 {
  $db->RunQuery("SELECT DISTINCT".$sumQry);
  if($db->Error) return array("message"=>$out."\nMySQL error: ".$db->Error."\nLASTQRY: ".$db->lastQuery, "error"=>"MYSQL_ERROR");
  $db->Read();
  $outArr['totals'] = array();
  for($c=0; $c < count($_SUM_FIELDS); $c++)
   $outArr['totals'][$_SUM_FIELDS[$c]] = $db->record[$_SUM_FIELDS[$c]];
  for($c=0; $c < count($_EXT_SUM_FIELDS); $c++)
   $outArr['totals'][$_EXT_SUM_FIELDS[$c]] = $db->record[$_EXT_SUM_FIELDS[$c]];
 }

 $db->Close();

 if($orderBy)		$qry.= " ORDER BY ".$orderBy;
 if($limit)			$qry.= " LIMIT ".$limit;

 /* EXEC QUERY */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT DISTINCT".$qry);
 if($db->Error) return array("message"=>$out."\nMySQL error: ".$db->Error."\nLASTQRY: ".$db->lastQuery, "error"=>"MYSQL_ERROR");
 while($db->Read())
 {
  $item = array();
  for($c=0; $c < count($_ARCHIVE_FIELDS); $c++)
   $item[$_ARCHIVE_FIELDS[$c]] = $db->record[$_ARCHIVE_FIELDS[$c]];
  for($c=0; $c < count($_EXTENSION_FIELDS); $c++)
   $item[$_EXTENSION_FIELDS[$c]] = $db->record[$_EXTENSION_FIELDS[$c]];

  if($extget)
  {
   $archiveInfo = array('prefix'=>($_AP2 ? $_AP2 : $_AP));
   $ret = dynarc_parseExtensionGet($extget,$sessid, $shellid, $archiveInfo, $item);
   if($ret && is_array($ret)) $item = $ret;
  }

  $outArr['items'][] = $item;
 }
 $db->Close();

 if($verbose)
 {
  $out.= "<table cellspacing='0' cellpadding='3' border='0'>";
  $out.= "<tr>";
  for($c=0; $c < count($_ARCHIVE_FIELDS); $c++)
   $out.= "<th>".$_ARCHIVE_FIELDS[$c]."</th>";
  for($c=0; $c < count($_EXTENSION_FIELDS); $c++)
   $out.= "<th>".$_EXTENSION_FIELDS[$c]."</th>";
  $out.= "</tr>";

  for($c=0; $c < count($outArr['items']); $c++)
  {
   $item = $outArr['items'][$c];
   $out.= "<tr>";
   for($i=0; $i < count($_ARCHIVE_FIELDS); $i++)
    $out.= "<td>".(($item[$_ARCHIVE_FIELDS[$i]] != '') ? $item[$_ARCHIVE_FIELDS[$i]] : '&nbsp;')."</td>";
   for($i=0; $i < count($_EXTENSION_FIELDS); $i++)
    $out.= "<td>".(($item[$_EXTENSION_FIELDS[$i]] != '') ? $item[$_EXTENSION_FIELDS[$i]] : '&nbsp;')."</td>";
   $out.= "</tr>";
  }
  $out.= "</table>";
 }
 $out.= $outArr['count']." results found.";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//


