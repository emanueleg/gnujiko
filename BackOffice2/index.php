<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 27-02-2014
 #PACKAGE: backoffice
 #DESCRIPTION: BackOffice 2 - Dashboard
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../";

/*if(!$_REQUEST['show'])
 $_REQUEST['show'] = "calendar";

switch($_REQUEST['show'])
{
 case 'calendar' : include("dash-calendarview.php"); break;
 default : include("dash-listview.php"); break;
}*/

header("Location:schedule.php");

?>
