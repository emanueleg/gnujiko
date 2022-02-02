<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 14-01-2012
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Default widget for import form
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_ARCHIVE_PREFIX, $_ARCHIVE_INFO;
global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$_ARCHIVE_PREFIX = $_REQUEST['ap'];

$ret = GShell("dynarc archive-info -prefix `".$_ARCHIVE_PREFIX."`");
if(!$ret['error'])
 $_ARCHIVE_INFO = $ret['outarr'];

/* LOAD THEMES */
$theme = $_REQUEST['theme'] ? $_REQUEST['theme'] : "default";
if(file_exists($_BASE_PATH."share/widgets/dynarc/themes/$theme/navigator.import.php"))
 include_once($_BASE_PATH."share/widgets/dynarc/themes/$theme/navigator.import.php");
else
 include_once($_BASE_PATH."share/widgets/dynarc/themes/default/navigator.import.php");

