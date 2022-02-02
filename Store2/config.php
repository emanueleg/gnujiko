<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-09-2014
 #PACKAGE: gstore
 #DESCRIPTION: Official Gnujiko Store Manager. ver.2
 #VERSION: 2.4beta
 #CHANGELOG: 11-09-2014 : Integrato con gestione pacchi.
			 25-08-2014 : Integrato con i libri.
			 30-07-2014 : Integrato con prodotti finiti, componenti e materiali.
			 08-04-2014 : Aggiunto file di linguaggio
 #TODO:
 
*/

global $_BASE_PATH, $_SHELL_CMD_PATH;
LoadLanguage("store2");
include_once($_BASE_PATH."include/userfunc.php");

$_APPLICATION_CONFIG = array(
	"appname"=>"Magazzino",
	"basepath"=>"Store2/",
	"mainmenu"=>array()
);

$archiveTypes = array();
if(_userInGroup("gmart") && file_exists($_BASE_PATH."Products/index.php"))
 $archiveTypes['gmart'] = "articoli";
if(_userInGroup("gproducts") && file_exists($_BASE_PATH."FinalProducts/index.php"))
 $archiveTypes['gproducts'] = "prodotti finiti";
if(_userInGroup("gpart") && file_exists($_BASE_PATH."Parts/index.php"))
 $archiveTypes['gpart'] = "componenti";
if(_userInGroup("gmaterial") && file_exists($_BASE_PATH."Materials/index.php"))
 $archiveTypes['gmaterial'] = "materiali";
if(_userInGroup("gbook") && file_exists($_BASE_PATH."Books/index.php"))
 $archiveTypes['gbook'] = "libri";

$db = new AlpaDatabase();
while(list($k,$v)=each($archiveTypes))
{
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_archives WHERE archive_type='".$k."' AND trash='0'");
 if($db->Read())
  $_APPLICATION_CONFIG['mainmenu'][] = array("title"=>"Situazione ".$v, "url"=>"index.php?at=".$k);
}
$db->Close();

if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."pack.php"))
 $_APPLICATION_CONFIG['mainmenu'][] = array("title"=>"Gestione pacchi", "url"=>"packs.php"); 

$_APPLICATION_CONFIG['mainmenu'][] = array("title"=>"Movimenti", "url"=>"movements.php");



