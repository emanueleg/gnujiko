<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 14-01-2012
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Default widget for new item form
 #VERSION: 1.0
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_ARCHIVE_PREFIX, $_ARCHIVE_INFO, $_CAT_INFO;
global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$_ARCHIVE_PREFIX = $_REQUEST['ap'];

$ret = GShell("dynarc archive-info -prefix `".$_ARCHIVE_PREFIX."`");
if(!$ret['error'])
 $_ARCHIVE_INFO = $ret['outarr'];

if($_REQUEST['catid'])
{
 $ret = GShell("dynarc cat-info -ap `".$_ARCHIVE_PREFIX."` -id `".$_REQUEST['catid']."`");
 if(!$ret['error'])
  $_CAT_INFO = $ret['outarr'];
}

/* get extensions */
$archiveInfo['extensions'] = array();
$db = new AlpaDatabase();
$db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' ORDER BY id ASC");
while($db->Read())
 $archiveInfo['extensions'][] = $db->record['extension_name'];
$db->Close();

/* LOAD THEMES */
$theme = $_REQUEST['theme'] ? $_REQUEST['theme'] : "default";
if(file_exists($_BASE_PATH."share/widgets/dynarc/themes/$theme/navigator.new.item.php"))
 include_once($_BASE_PATH."share/widgets/dynarc/themes/$theme/navigator.new.item.php");
else
 include_once($_BASE_PATH."share/widgets/dynarc/themes/default/navigator.new.item.php");

