<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2011 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-12-2011
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Official Gnujiko Shell
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH;

$_BASE_PATH = "../";

include($_BASE_PATH."installation/install.inc");

switch($_REQUEST['step'])
{
 case 1 : include($_BASE_PATH."installation/step1-database-config.php"); break;
 case 2 : include($_BASE_PATH."installation/step2-ftp-settings.php"); break;
 case 3 : include($_BASE_PATH."installation/step3-account-settings.php"); break;
 case 4 : include($_BASE_PATH."installation/step4-permissions-check.php"); break;
 case 5 : include($_BASE_PATH."installation/step5-database-import.php"); break;
 case 6 : include($_BASE_PATH."installation/step6-check-for-updates.php"); break;
 default : include($_BASE_PATH."installation/step0-select-language.php"); break;
}

