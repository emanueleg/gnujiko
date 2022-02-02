<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2011 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-08-2011
 #PACKAGE: gnujiko-base
 #DESCRIPTION: INIT-0 - Database access
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

include_once($_BASE_PATH."config.php");

if(!$_DATABASE_NAME && !$_DATABASE_USER && !$_DATABASE_PASSWORD)
{
 header("Location:".$_BASE_PATH."installation/index.php");
 exit;
}

include_once($_BASE_PATH."var/lib/database.php"); // enable and load database access //

include_once($_BASE_PATH."include/i18n.php"); // enable language support //

