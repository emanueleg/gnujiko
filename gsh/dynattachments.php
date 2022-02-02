<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 20-11-2012
 #PACKAGE: dynarc-attachments-extension
 #DESCRIPTION: Attachments support for categories and items into archives managed by Dynarc.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");


function shell_dynattachments($args, $sessid, $shellid=null)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;
 $output = "";
 $outArr = array();

 switch($args[0])
 {
  case 'add' : case 'new' : return shell_dynattachments_new($args, $sessid, $shellid); break;
  case 'edit' : return shell_dynattachments_edit($args, $sessid, $shellid); break;
  case 'delete' : return shell_dynattachments_delete($args, $sessid, $shellid); break;
  case 'info' : return shell_dynattachments_info($args, $sessid, $shellid); break;
  case 'list' : return shell_dynattachments_list($args, $sessid, $shellid); break;
  default : return shell_dynattachments_invalidArguments(); break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_dynattachments_invalidArguments()
{
 $out = "Invalid option\n";
 $out.= "You must specify an option:\n";
 $out.= "add - Add new attachment\n";
 $out.= "edit - Edit a specified attachment\n";
 $out.= "delete - Remove a specified attachment\n";
 $out.= "info - Info about attachment\n";
 $out.= "list - List of attachments\n";
 return array('message'=>$out,'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_dynattachments_new($args, $sessid=null, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;

   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   // TODO: case '-ct' : case '--cat-tag' : {$catTag=$args[$c+1]; $c++;} break;
   case '-id' : case '-refid' : {$refid=$args[$c+1]; $c++;} break;

   case '-type' : {$type=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-desc' : {$desc=$args[$c+1]; $c++;} break;
   case '-keyw' : {$keywords=$args[$c+1]; $c++;} break;
   case '-url' : {$url=$args[$c+1]; $c++;} break;

   case '-tag' : {$tag=$args[$c+1]; $c++;} break;
   case '-tbtag' : {$tbtag=$args[$c+1]; $c++;} break;
   case '-publish' : $publish=true; break;
  }

 $out = "";
 $outArr = array();
 $sessInfo = sessionInfo($sessid);

 /* CHECK FOR ARCHIVE */
 if($extraVar)
 {
  $archiveInfo = $extraVar;
  $archiveId = $archiveInfo['id'];
 }
 else
 {
  if($archive)
  {
   $ret = GShell("dynarc archive-info '$archive'",$sessid,$shellid);
   if(!$ret['error'])
    $archiveId = $ret['outarr']['id'];
  }
  else if($archivePrefix)
  {
   $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid);
   if(!$ret['error'])
    $archiveId = $ret['outarr']['id'];
  }
  else if($archiveId)
  {
   $ret = GShell("dynarc archive-info -id '$archiveId'",$sessid,$shellid);
   if(!$ret['error'])
    $archiveId = $ret['outarr']['id'];
  }
 }

 if(!$type)
 {
  $ext = strtolower(substr($url, strrpos($url, '.')+1));
  if(file_exists($_BASE_PATH."etc/mimetypes.php"))
  {
   include_once($_BASE_PATH."etc/mimetypes.php");
   if($mimetypes[$ext])
	$type = $mimetypes[$ext];
   else
   {
	// detect if is a external web link //
	if(preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url))
	 $type = "WEB";
   }
  }
 }

 $uid = $sessInfo['uid'];
 $gid = $sessInfo['gid'];
 $mod = "644";
 $now = date('Y-m-d H:i');

 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO dynattachments(uid,gid,_mod,archive_id,item_id,cat_id,tag,tbtag,name,description,keywords,linktype,url,ctime,published) VALUES('".$uid."','$gid','$mod','$archiveId','$refid','$catId','$tag','$tbtag','$name','$desc','$keywords','$type','$url','$now','$publish')");
 $id = mysql_insert_id();
 $db->Close();

 $out.= "Attachment $name has been created! (id=$id)\n";
 $a = array('id'=>$id,'uid'=>$uid,'gid'=>$gid,'mod'=>$mod,'type'=>$type,'refid'=>$refid,'catid'=>$catId,'tag'=>$tag,'tbtag'=>$tbtag,'name'=>$name,'url'=>$url,'ctime'=>strtotime($now),'description'=>$desc,'keywords'=>$keywords,'published'=>$publish);

 if($type != "WEB")
 {
  // detect file size //
  if(file_exists($_BASE_PATH.$url))
  {
   $siz = $fileSize = filesize($_BASE_PATH.$url);
   $sar = array('bytes','kB','MB','GB','TB');
   $sx = 0;
   while($siz > 1024)
   {
    $siz = $siz/1024;
    $sx++;
    if($sx == 4)
     break;
   }
   $humanSize = str_replace('.00','',sprintf("%.2f",$siz))." ".$sar[$sx];
  }
 }
 $a['size'] = $fileSize;
 $a['humansize'] = $humanSize;

 // detect mimetype icon //
 if(file_exists($_BASE_PATH."etc/mimetypes.php"))
 {
   include_once($_BASE_PATH."etc/mimetypes.php");
   $a['icons'] = getMimetypeIcons($type);
 }
 $outArr = $a;
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_dynattachments_edit($args, $sessid=null, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;

   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   // TODO: case '-ct' : case '--cat-tag' : {$catTag=$args[$c+1]; $c++;} break;
   case '-refid' : {$refid=$args[$c+1]; $c++;} break;

   case '-type' : {$type=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-desc' : {$desc=$args[$c+1]; $c++;} break;
   case '-keyw' : {$keywords=$args[$c+1]; $c++;} break;
   case '-url' : {$url=$args[$c+1]; $c++;} break;

   case '-tag' : {$tag=$args[$c+1]; $c++;} break;
   case '-tbtag' : {$tbtag=$args[$c+1]; $c++;} break;
   case '-publish' : $publish=true; break;
  }

 $out = "";
 $outArr = array();
 $sessInfo = sessionInfo($sessid);

 /* CHECK FOR ARCHIVE */
 if($extraVar)
 {
  $archiveInfo = $extraVar;
  $archiveId = $archiveInfo['id'];
 }
 else
 {
  if($archive)
  {
   $ret = GShell("dynarc archive-info '$archive'",$sessid,$shellid);
   if(!$ret['error'])
    $archiveId = $ret['outarr']['id'];
  }
  else if($archivePrefix)
  {
   $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid);
   if(!$ret['error'])
    $archiveId = $ret['outarr']['id'];
  }
  else if($archiveId)
  {
   $ret = GShell("dynarc archive-info -id '$archiveId'",$sessid,$shellid);
   if(!$ret['error'])
    $archiveId = $ret['outarr']['id'];
  }
 }

 /* CHECK ATTACHMENT PERMISSIONS */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id,uid,gid,_mod FROM dynattachments WHERE id='$id'");
 if(!$db->Read())
  return array("message"=>"Attachment #$id does not exists!","error"=>"ATTACHMENT_DOES_NOT_EXISTS");
 $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid']);
 if(!$m->canWrite())
  return array("message"=>"You have not permission for modify this attachment.","error"=>"PERMISSION_DENIED");
 $db->Close();

 $q = "";
 if(isset($archiveId))
  $q.= ",archive_id='$archiveId'";
 if(isset($refid))
  $q.= ",item_id='$refid'";
 if(isset($catId))
  $q.= ",cat_id='$catId'";
 if(isset($tag))
  $q.= ",tag='$tag'";
 if(isset($tbtag))
  $q.= ",tbtag='$tbtag'";
 if(isset($name))
  $q.= ",name='$name'";
 if(isset($desc))
  $q.= ",description='$desc'";
 if(isset($keywords))
  $q.= ",keywords='$keywords'";
 if(isset($type))
  $q.= ",linktype='$type'";
 if(isset($url))
  $q.= ",url='$url'";
 if(isset($publish))
  $q.= ",published='$publish'";

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynattachments SET ".ltrim($q,',')." WHERE id='$id'");
 $db->Close();
 $out.= "Attachment has been updated!\n";
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_dynattachments_delete($args, $sessid=null, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;
 $out = "";
 $ids = array();
 $sessInfo = sessionInfo($sessid);

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$ids[]=$args[$c+1]; $c++;} break;
   case '-r' : $delete=true; break;
  }

 if(count($ids) == 0)
 {
  $err = "INVALID_ATTACHMENT_ID";
  $out = "You must specify attachment (with -id attachment_id)\n";
  return false;
 }

 for($c=0; $c < count($ids); $c++)
 {
  $db = new AlpaDatabase();
  $id = $ids[$c];
  $db->RunQuery("SELECT * FROM dynattachments WHERE id='$id'");
  if(!$db->Read())
   return array('message'=>'Attachment #$id does not exists!','error'=>'ATTACHMENT_DOES_NOT_EXISTS');
  if(($db->record['uid'] != $sessInfo['uid']) && ($sessInfo['uname'] != 'root'))
   return array('message'=>'Only the owner can delete this attachment id:#$id','error'=>'PERMISSION_DENIED');

  // verify if another record link to this url //
  if($delete && ($db->record['linktype'] != 'WEB'))
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT id FROM dynattachments WHERE url='$url' AND id!='$id' LIMIT 1");
   if(!$db2->Read())
    @unlink($_BASE_PATH.$db->record['url']);
   $db2->Close();
  }
  $db->RunQuery("DELETE FROM dynattachments WHERE id='$id'");
  $out.= "Attachment #$id has been removed\n";
  $outArr['removed_attachments'][] = array('id'=>$db->record['id']);
  $db->Close();
 }
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_dynattachments_info($args, $sessid=null, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose = true;
  }

 if(!$id)
  return array('message'=>'You must specify attachment (with -id attachment_id)\n','error'=>"INVALID_ATTACHMENT_ID");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynattachments WHERE id='$id'");
 if(!$db->Read())
  return array('message'=>'Attachment #$id does not exists!','error'=>'ATTACHMENT_DOES_NOT_EXISTS');

 $mod = new GMOD($db->record['_mod'], $db->record['uid'], $db->record['gid']);
 if(!$mod->canRead())
  return array('message'=>'You have not permission for read this attachment','error'=>'PERMISSION_DENIED');

 if($db->record['linktype'] != "WEB")
 {
  // detect file size //
  if(file_exists($_BASE_PATH.$db->record['url']))
  {
   $siz = $fileSize = filesize($_BASE_PATH.$db->record['url']);
   $sar = array('bytes','kB','MB','GB','TB');
   $sx = 0;
   while($siz > 1024)
   {
    $siz = $siz/1024;
    $sx++;
    if($sx == 4)
     break;
   }
   $humanSize = str_replace('.00','',sprintf("%.2f",$siz))." ".$sar[$sx];
  }
 }

 $a = array('id'=>$db->record['id'],'type'=>$db->record['linktype'],'archive_id'=>$db->record['archive_id'],
	'refid'=>$db->record['item_id'],'catid'=>$db->record['cat_id'],'tag'=>$db->record['tag'],'tbtag'=>$db->record['tbtag'],
	'name'=>$db->record['name'],'description'=>$db->record['description'],'keywords'=>$db->record['keywords'],
	'url'=>$db->record['url'],'ctime'=>$db->record['ctime'],'size'=>$fileSize,'humansize'=>$humanSize,'published'=>$db->record['published']);

 // detect mimetype icon //
 if(file_exists($_BASE_PATH."etc/mimetypes.php"))
 {
   include_once($_BASE_PATH."etc/mimetypes.php");
   $a['icons'] = getMimetypeIcons($db->record['linktype']);
 }

 $a['modinfo'] = $mod->toArray();
 $outArr = $a;
 $db->Close();

 if($verbose)
 {
  $out = "Type: ".$a['linktype']."\n";
  $out.= "Tag: ".$a['tag']."\n";
  $out.= "TB Tag: ".$a['tbtag']."\n";
  $out.= "Name: ".$a['name']."\n";
  $out.= "Description: ".$a['description']."\n";
  $out.= "Keywords: ".$a['keywords']."\n";
  $out.= "URL: ".$a['url']."\n";
  $out.= "Creation time: ".date('D d/m/Y H:i',strtotime($a['ctime']))."\n";
  $out.= "Size: $humanSize\n";
  $out.= "Published: ".($a['published'] ? "Yes\n" : "No\n");
 }
 else
  $out = "done!";

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_dynattachments_list($args, $sessid=null, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;

   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   // TODO: case '-ct' : case '--cat-tag' : {$catTag=$args[$c+1]; $c++;} break;
   case '-refid' : {$refid=$args[$c+1]; $c++;} break;
   case '-type' : {$type=$args[$c+1]; $c++;} break;

   case '-tag' : {$tags[]=$args[$c+1]; $c++;} break;
   case '-tbtag' : {$tbtag=$args[$c+1]; $c++;} break;

   case '--verbose' : $verbose = true; break;
   case '-orderby' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
  }

 $out = "";
 $outArr = array();
 $sessInfo = sessionInfo($sessid);

 /* CHECK FOR ARCHIVE */
 if($extraVar)
 {
  $archiveInfo = $extraVar;
  $archiveId = $archiveInfo['id'];
 }
 else
 {
  if($archive)
  {
   $ret = GShell("dynarc archive-info '$archive'",$sessid,$shellid);
   if(!$ret['error'])
   {
	$archiveInfo = $ret['outarr'];
    $archiveId = $ret['outarr']['id'];
   }
  }
  else if($archivePrefix)
  {
   $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid);
   if(!$ret['error'])
   {
	$archiveInfo = $ret['outarr'];
    $archiveId = $ret['outarr']['id'];
   }
  }
  else if($archiveId)
  {
   $ret = GShell("dynarc archive-info -id '$archiveId'",$sessid,$shellid);
   if(!$ret['error'])
   {
	$archiveInfo = $ret['outarr'];
    $archiveId = $ret['outarr']['id'];
   }
  }
 }

 if($sessInfo['uid'])
 {
  $mod = new GMOD();
  $uQry = $mod->userQuery();
  $q = "($uQry)";
 }
 else
  $q = "published='1'";

 if($archiveId)
  $q.= " AND archive_id='$archiveId'";
 if($refid)
  $q.= " AND item_id='$refid'";
 if(isset($catId))
  $q.= " AND cat_id='$catId'";
 if($type)
  $q.= " AND linktype='$type'";
 if(count($tags) == 1)
  $q.= " AND tag='".$tags[0]."'";
 else if(count($tags) > 1)
 {
  $q.= " AND (tag='".$tags[0]."'";
  for($c=1; $c < count($tags); $c++)
   $q.= " OR tag='".$tags[$c]."'";
  $q.= ")";
 }
 if($tbtag)
  $q.= " AND tbtag='$tbtag'";

 if($q == "")
  $q = "1";

 //--- count ---//
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynattachments WHERE $q");
 $db->Read();
 $outArr['count'] = $db->record[0];

 if($orderBy)
  $q.= " ORDER BY $orderBy";
 $db->RunQuery("SELECT id FROM dynattachments WHERE $q".($limit ? " LIMIT $limit" : ""));
 while($db->Read())
 {
  $ret = GShell("dynattachments info -id ".$db->record['id'],$sessid,$shellid,$archiveInfo);
  if(!$ret['error'])
  {
   $outArr['items'][] = $ret['outarr'];
   $out.= "#".$ret['outarr']['id']." - ".$ret['outarr']['name']."\n";
  }
 }
 $db->Close();

 if(!$verbose)
  $out = "";
 $out.= "found ".$outArr['count']." elements.";

 return array('message'=>$out,'outarr'=>$outArr); 
}
//-------------------------------------------------------------------------------------------------------------------//


