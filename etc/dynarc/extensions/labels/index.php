<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-10-2016
 #PACKAGE: dynarc-label-extension
 #DESCRIPTION: Label extension for Dynarc
 #VERSION: 2.2beta
 #CHANGELOG: 24-10-2016 : MySQLi integration.
			 10-06-2014 : Aggiunta funzione onarchiveempty
 #TODO:Rifare funzione import & export e completare funzioni syncimport & syncexport.
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` ADD `user_labels` TEXT NOT NULL , ADD `system_labels` TEXT NOT NULL");
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_labels` (
`id` INT(11) NOT NULL AUTO_INCREMENT,
`uid` INT(11) NOT NULL ,
`gid` INT(11) NOT NULL ,
`_mod` VARCHAR(3) NOT NULL ,
`tag` VARCHAR(64) NOT NULL ,
`name` VARCHAR(64) NOT NULL ,
`bgcolor` VARCHAR(7) NOT NULL , 
`color` VARCHAR(7) NOT NULL ,
PRIMARY KEY (`id`) , INDEX (`uid`,`gid`,`_mod`,`tag`))");
 $db->Close();
 return array("message"=>"Labels extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` DROP `user_labels`, DROP `system_labels`");
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_labels`");
 $db->Close();

 return array("message"=>"Labels extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_list($params,$sessid,$shellid=0)
{
 $out = "";
 $outArr = array();

 /* ARRAYZE PARAMS */
 $tmp = explode("&",$params);
 $params = array();
 for($c=0; $c < count($tmp); $c++)
 {
  $x = explode("=",$tmp[$c]);
  $params[$x[0]] = $x[1];
 }
 /* EOF - ARRAYZE PARAMS */

 /* CHECK FOR ARCHIVE */
 if($params['archiveprefix'])
  $ret = GShell("dynarc archive-info -prefix '".$params['archiveprefix']."'",$sessid,$shellid);
 else if($params['archiveid'])
  $ret = GShell("dynarc archive-info -id '".$params['archiveid']."'",$sessid,$shellid);
 else
  return array("message"=>"You must specify the archive into arguments!","error"=>"INVALID_ARCHIVE");

 if($ret['error'])
  return $ret;
 $archiveInfo = $ret['outarr'];

 if($params['itemid'])
 {
  $sessInfo = sessionInfo($sessid);
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT uid,gid,_mod,user_labels FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$params['itemid']."'");
  if(!$db->Read())
   return array("message"=>"Item ".$params['itemid']." does not exists!\n","error"=>"ITEM_DOES_NOT_EXISTS");
  $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid']);
  if(!$m->canRead($sessInfo['uid']))
   return array("message"=>"Unable to read item ".$params['itemid']." : Permission denied!\n","error"=>"ITEM_PERMISSION_DENIED");
  $itemUserLabels = $db->record['user_labels'];
  $db->Close();
  
  $userLabels = array();

  $tmp = ltrim(rtrim($itemUserLabels,","),",");
  $x = explode(",",$tmp);
  for($c=0; $c < count($x); $c++)
   $userLabels[$x[$c]] = true;
 }
 
 /* CHECK IF EXTENSION IS INSTALLED */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='labels' LIMIT 1");
 if(!$db->Read())
  return array("message"=>"Extension $extension is not installed into archive ".$archiveInfo['name']."!\nYou can install labels extension by typing: dynarc install-extension labels -ap ".$archiveInfo['prefix']."\nRemember: Only the owner of archive (or root) can install/uninstall extensions!");
 $db->Close();
 
 /* SHOW LIST */
 $m = new GMOD();
 $uQry = $m->userQuery($sessid);

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_labels WHERE ($uQry) ORDER BY name ASC");
 while($db->Read())
 {
  $isSelected = false;
  if($userLabels[$db->record['id']])
   $isSelected = true;
  if($params['verbose'] == 'true')
   $out.= $db->record['id'].". ".$db->record['name'].($isSelected ? "[selected]" : "")."\n";
  $outArr[] = array('id'=>$db->record['id'],'tag'=>$db->record['tag'],'name'=>$db->record['name'],'selected'=>$isSelected,'bgcolor'=>$db->record['bgcolor'],'color'=>$db->record['color']);
 }
 $db->Close();
 $out.= count($outArr)." items found!";
 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_new($params, $sessid, $shellid=0)
{
 $out = "";
 $outArr = array();

 /* ARRAYZE PARAMS */
 $tmp = explode("&",$params);
 $params = array();
 for($c=0; $c < count($tmp); $c++)
 {
  $x = explode("=",$tmp[$c]);
  $params[$x[0]] = $x[1];
 }
 /* EOF - ARRAYZE PARAMS */

 /* CHECK FOR ARCHIVE */
 if($params['ap']) 
  $params['archiveprefix'] = $params['ap'];
 if($params['archiveprefix'])
  $ret = GShell("dynarc archive-info -prefix '".$params['archiveprefix']."'",$sessid,$shellid);
 else if($params['archiveid'])
  $ret = GShell("dynarc archive-info -id '".$params['archiveid']."'",$sessid,$shellid);
 else
  return array("message"=>"You must specify the archive into arguments!","error"=>"INVALID_ARCHIVE");

 if($ret['error'])
  return $ret;
 $archiveInfo = $ret['outarr'];
 
 /* CHECK IF EXTENSION IS INSTALLED */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='labels' LIMIT 1");
 if(!$db->Read())
  return array("message"=>"Extension labels is not installed into archive ".$archiveInfo['name']."!\nYou can install labels extension by typing: dynarc install-extension labels -ap ".$archiveInfo['prefix']."\nRemember: Only the owner of archive (or root) can install/uninstall extensions!");
 $db->Close();

 /* INSERT LABEL */
 $sessInfo = sessionInfo($sessid);
 $uid = $sessInfo['uid'];
 $gid = $groupId ? $groupId : $sessInfo['gid'];
 $mod = $params['perms'] ? $params['perms'] : 644;
 $name = $params['name'];
 $tag = $params['tag'];
 $bgcolor = $params['bgcolor'] ? "#".str_replace('#','',$params['bgcolor']) : "#ffffff";
 $color = $params['color'] ? "#".str_replace('#','',$params['color']) : "#000000";

 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_labels(uid,gid,_mod,tag,name,bgcolor,color) VALUES('$uid','$gid','$mod','$tag','$name','$bgcolor','$color')");
 $id = $db->GetInsertId();
 $db->Close();
 $out.= "New label has been inserted!\n ID: $id";
 $outArr = array('id'=>$id, 'tag'=>$tag, 'name'=>$name,'bgcolor'=>$bgcolor,'color'=>$color);
 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_edit($params, $sessid, $shellid=0)
{
 $out = "";
 $outArr = array();

 /* ARRAYZE PARAMS */
 $tmp = explode("&",$params);
 $params = array();
 for($c=0; $c < count($tmp); $c++)
 {
  $x = explode("=",$tmp[$c]);
  $params[$x[0]] = $x[1];
 }
 /* EOF - ARRAYZE PARAMS */

 /* CHECK FOR ARCHIVE */
 if($params['ap']) 
  $params['archiveprefix'] = $params['ap'];
 if($params['archiveprefix'])
  $ret = GShell("dynarc archive-info -prefix '".$params['archiveprefix']."'",$sessid,$shellid);
 else if($params['archiveid'])
  $ret = GShell("dynarc archive-info -id '".$params['archiveid']."'",$sessid,$shellid);
 else
  return array("message"=>"You must specify the archive into arguments!","error"=>"INVALID_ARCHIVE");

 if($ret['error'])
  return $ret;
 $archiveInfo = $ret['outarr'];
 
 /* CHECK IF EXTENSION IS INSTALLED */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='labels' LIMIT 1");
 if(!$db->Read())
  return array("message"=>"Extension labels is not installed into archive ".$archiveInfo['name']."!\nYou can install labels extension by typing: dynarc install-extension labels -ap ".$archiveInfo['prefix']."\nRemember: Only the owner of archive (or root) can install/uninstall extensions!");
 $db->Close();

 /* EDIT LABEL */
 $sessInfo = sessionInfo($sessid);
 $uid = $sessInfo['uid'];
 $gid = $groupId ? $groupId : $sessInfo['gid'];
 $mod = $params['perms'] ? $params['perms'] : 644;
 $id = $params['id'];

 $q = "";
 if($params['name']) $q.= ",name='".$params['name']."'";
 if($params['bgcolor']) $q.= ",bgcolor='#".str_replace('#','',$params['bgcolor'])."'";
 if($params['color']) $q.= ",color='#".str_replace('#','',$params['color'])."'";
 if(isset($params['tag'])) $q.= ",tag='".$params['tag']."'";

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_labels SET ".ltrim($q,",")." WHERE id='$id'");
 $out.= "Label has been updated!";
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_delete($params, $sessid, $shellid=0)
{
 $out = "";
 $outArr = array();

 /* ARRAYZE PARAMS */
 $tmp = explode("&",$params);
 $params = array();
 for($c=0; $c < count($tmp); $c++)
 {
  $x = explode("=",$tmp[$c]);
  $params[$x[0]] = $x[1];
 }
 /* EOF - ARRAYZE PARAMS */

 /* CHECK FOR ARCHIVE */
 if($params['ap']) 
  $params['archiveprefix'] = $params['ap'];
 if($params['archiveprefix'])
  $ret = GShell("dynarc archive-info -prefix '".$params['archiveprefix']."'",$sessid,$shellid);
 else if($params['archiveid'])
  $ret = GShell("dynarc archive-info -id '".$params['archiveid']."'",$sessid,$shellid);
 else
  return array("message"=>"You must specify the archive into arguments!","error"=>"INVALID_ARCHIVE");

 if($ret['error'])
  return $ret;
 $archiveInfo = $ret['outarr'];
 
 /* CHECK IF EXTENSION IS INSTALLED */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='labels' LIMIT 1");
 if(!$db->Read())
  return array("message"=>"Extension labels is not installed into archive ".$archiveInfo['name']."!\nYou can install labels extension by typing: dynarc install-extension labels -ap ".$archiveInfo['prefix']."\nRemember: Only the owner of archive (or root) can install/uninstall extensions!");
 $db->Close();

 /* EDIT LABEL */
 $sessInfo = sessionInfo($sessid);
 $uid = $sessInfo['uid'];
 $gid = $groupId ? $groupId : $sessInfo['gid'];

 $id = $params['id'];

 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_labels WHERE id='$id'");
 $out.= "Label has been removed!";
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_set($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'userlabels' : {$userLabels=$args[$c+1]; $c++;} break;
   case 'systemlabels' : {$systemLabels=$args[$c+1]; $c++;} break;
  }

 $q = "";
 if(isset($userLabels))
  $q.= ",user_labels='".($userLabels ? ",".$userLabels."," : "")."'";
 if(isset($systemLabels))
  $q.= ",system_labels='".($systemLabels ? ",".$systemLabels."," : "")."'";
 if(!$q)
  return array("message"=>"You must specify userlabels or systemlabels\n","error"=>"INVALID_ARGUMENTS");
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".substr($q,1)." WHERE id='".$itemInfo['id']."'");
 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_get($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'userlabels' : $userLabels = true; break;
   case 'systemlabels' : $systemLabels = true; break;
  }

 if(!count($args))
  $all = true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT user_labels,system_labels FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 if($userLabels || $all)
  $itemInfo['user_labels'] = explode(",",rtrim(ltrim($db->record['user_labels'],","),","));
 if($systemLabels || $all)
  $itemInfo['system_labels'] = explode(",",rtrim(ltrim($db->record['system_labels'],","),","));
 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET user_labels='".$srcInfo['user_labels']."',system_labels='"
	.$srcInfo['system_labels']."' WHERE id='".$cloneInfo['id']."'");
 $db->Close();
 $cloneInfo['user_labels'] = explode(",",rtrim(ltrim($srcInfo['user_labels'],","),","));
 $cloneInfo['system_labels'] = explode(",",rtrim(ltrim($srcInfo['system_labels'],","),","));
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_export($sessid, $shellid, $archiveInfo, $itemInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT user_labels,system_labels FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 if((strlen($db->record['user_labels']) < 2) && (strlen($db->record['system_labels']) < 2))
  return array("xml"=>"");
 $xml = "<labels";
 if(strlen($db->record['user_labels']) > 1)
  $xml.= " user=\"".$db->record['user_labels']."\"";
 if(strlen($db->record['system_labels']) > 1)
  $xml.= " system=\"".$db->record['system_labels']."\"";
 $xml.= "/>\n";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_import($sessid, $shellid, $archiveInfo, $itemInfo, $node)
{
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET user_labels='"
	.$node->getString('user',',')."',system_labels='".$node->getString('system',',')."' WHERE id='".$itemInfo['id']."'");
 $db->Close();
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_onarchiveempty($args, $sessid, $shellid, $archiveInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("TRUNCATE TABLE `dynarc_".$archiveInfo['prefix']."_labels`");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_labels_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

