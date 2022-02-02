<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-02-2016
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Logout procedure
 #VERSION: 2.2beta
 #CHANGELOG: 05-02-2016 : resettato cookie username e passwd quando si fa logout.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../";

include($_BASE_PATH."init/init1.php");
include_once($_BASE_PATH."include/filesfunc.php");

$halfHourAgo = time()-1800;

setcookie("username",null,strtotime("+1 week"), "/");
setcookie("password",null,strtotime("+1 week"), "/");

session_name("Gnujiko-$_DATABASE_NAME");
session_start();

$db = new AlpaDatabase();
$db->RunQuery("SELECT dev,devid FROM gnujiko_session WHERE session_id='".$_SESSION['SESSID']."'");
if($db->Read())
{
 rmdirr($_BASE_PATH."tmp/session-".$_SESSION['SESSID']);
 rmdirr($_BASE_PATH."tmp/".$db->record['dev']."-".$db->record['devid']);
 $db->RunQuery("DELETE FROM gnujiko_session WHERE session_id='".$_SESSION['SESSID']."'");
}
$db->Close();

/* Remove some of active shell sessions */
if($_SESSION['UID'])
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_session WHERE uid='".$_SESSION['UID']."' AND time < '".$halfHourAgo."'");
 while($db->Read())
 {
  rmdirr($_BASE_PATH."tmp/session-".$db->record['session_id']);
  rmdirr($_BASE_PATH."tmp/".$db->record['dev']."-".$db->record['devid']);
 }
 $db->RunQuery("DELETE FROM gnujiko_session WHERE uid='".$_SESSION['UID']."' AND time < '".$halfHourAgo."'");
 $db->Close();
}
 
$_SESSION['SESSID'] = 0;
$_SESSION['UID'] = 0;
$_SESSION['GID'] = 0;
$_SESSION['UNAME'] = "";
$_SESSION['LOGINTIME'] = 0;
$_SESSION['UFULLNAME'] = "";
$_SESSION['UEMAIL'] = "";
$_SESSION['UHOMEDIR'] = "";

session_destroy();

header("Location:".($_REQUEST['continue'] ? urldecode($_REQUEST['continue']) : $_ABSOLUTE_URL));

