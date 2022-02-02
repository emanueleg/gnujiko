<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2011 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 29-12-2011 
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Main file
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DESKTOP_SHOW_TOOLBAR, $_DESKTOP_TITLE, $_SOFTWARE_NAME;

include('init/init1.php');
include('include/session.php');

if(file_exists($_BASE_PATH."include/desktop.php"))
 include($_BASE_PATH."include/desktop.php");
else
 include($_BASE_PATH."include/home.php");

