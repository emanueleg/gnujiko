<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 03-10-2016 
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Main file
 #VERSION: 2.1beta
 #CHANGELOG: 03-10-2016 : Mobile integration.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DESKTOP_SHOW_TOOLBAR, $_DESKTOP_TITLE, $_SOFTWARE_NAME;

include('init/init1.php');
include('include/session.php');

if(($_COOKIE['gnujiko_ui_devtype'] == "phone") && file_exists($_BASE_PATH."include/desktop-mobi.php"))
 include($_BASE_PATH."include/desktop-mobi.php");
else if(file_exists($_BASE_PATH."include/desktop.php"))
 include($_BASE_PATH."include/desktop.php");
else
 include($_BASE_PATH."include/home.php");

