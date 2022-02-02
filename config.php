<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-04-2013
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Configuration file
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

error_reporting(E_ALL & ~E_NOTICE & ~(E_DEPRECATED | E_STRICT));

$_SOFTWARE_NAME = "Gnujiko 10";
$_SOFTWARE_VERSION =	"10.1";
$_DISTRO_NAME =	"Gestione Impresa";
$_DATABASE_NAME =	"";
$_DATABASE_USER =	"";
$_DATABASE_PASSWORD =	"";
$_DATABASE_HOST =	"localhost";

$_FTP_SERVER =	"";
$_FTP_USERNAME =	"";
$_FTP_PASSWORD =	"";
$_FTP_PATH =	"";

$_SMTP_SENDMAIL =	"/usr/sbin/sendmail";
$_SMTP_AUTH =	"";
$_SMTP_HOST =	"";
$_SMTP_USERNAME =	"";
$_SMTP_PASSWORD =	"";

$_ABSOLUTE_URL = gnujiko_autoretrieve_absoluteurl();
$_DEFAULT_FILE_PERMS = "0777";

$_SHELL_CMD_PATH = "gsh/";
$_USERS_HOMES = "home/";

$_LANGUAGE =	"it-IT";

$_GOOGLE_KEY = "";
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function gnujiko_autoretrieve_absoluteurl()
{
 global $_BASE_PATH;
 /* RETRIEVE ABSOLUTE URL */
 $pos = strpos($_SERVER['REQUEST_URI'],"?");
 if($pos !== FALSE)
  $requri = substr($_SERVER['REQUEST_URI'],0,$pos);
 else
  $requri = $_SERVER['REQUEST_URI'];

 $_bpl = strlen($_BASE_PATH) > 2 ? strlen($_BASE_PATH)/3 : 0;
 $_rux = explode("/",ltrim($requri,"/"));
 $ret = "http://".$_SERVER['HTTP_HOST']."/".implode("/",array_slice($_rux,0,(count($_rux)-1)-$_bpl))."/";
 $ret = rtrim($ret,"//");
 $ret = rtrim($ret,"/")."/";

 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//

