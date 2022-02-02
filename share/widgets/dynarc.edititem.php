<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 14-01-2012
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Edit item form support for Dynarc
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_ARCHIVE_PREFIX, $_ARCHIVE_INFO, $_ITEM_INFO, $_PARENT_INFO, $_PATHWAY;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$_ARCHIVE_PREFIX = $_REQUEST['ap'];

$ret = GShell("dynarc archive-info -prefix `".$_ARCHIVE_PREFIX."`");
if(!$ret['error'])
 $archiveInfo = $ret['outarr'];

/* get extensions */
$archiveInfo['extensions'] = array();
$db = new AlpaDatabase();
$db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' ORDER BY id ASC");
while($db->Read())
{
 $archiveInfo['extensions'][] = $db->record['extension_name'];
}
$db->Close();

$getExt = implode(",",$archiveInfo['extensions']);

if($_REQUEST['id'])
{
 $ret = GShell("dynarc item-info -ap $_ARCHIVE_PREFIX -id '".$_REQUEST['id']."'".($getExt ? " -extget `".$getExt."`" : ""));
 if($ret['error'])
 {
  echo $ret['message'];
  return;
 }

 $itemInfo = $ret['outarr'];

 /* GET OWNER */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT fullname FROM gnujiko_users WHERE id='".$itemInfo['modinfo']['uid']."'");
 $db->Read();
 $Owner = $db->record['fullname'];
 $db->Close();
 $itemInfo['owner'] = $Owner;

 $path = "";
 if($itemInfo['cat_id'])
 {
  $ret = GShell("dynarc cat-info -ap $_ARCHIVE_PREFIX -id ".$itemInfo['cat_id']." --include-path");
  $parentInfo = $ret['outarr'];
  if($parentInfo['pathway'])
  {
   for($c=0; $c < count($parentInfo['pathway']); $c++)
	$path.= $parentInfo['pathway'][$c]['name']."/";
  }
  $path.= $parentInfo['name'];
 }
}

$_ARCHIVE_INFO = $archiveInfo;
$_ITEM_INFO = $itemInfo;
$_PARENT_INFO = $parentInfo;
$_PATHWAY = $path;

/* LOAD THEMES */
$theme = $_REQUEST['theme'] ? $_REQUEST['theme'] : "default";
if(file_exists($_BASE_PATH."share/widgets/dynarc/themes/$theme/navigator.edit.item.php"))
 include_once($_BASE_PATH."share/widgets/dynarc/themes/$theme/navigator.edit.item.php");
else
 include_once($_BASE_PATH."share/widgets/dynarc/themes/default/navigator.edit.item.php");

