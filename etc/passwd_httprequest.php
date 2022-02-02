<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2010 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-01-2010
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Http request for passwd command
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../";

include($_BASE_PATH.'init/init1.php');
include($_BASE_PATH.'include/session.php');
include($_BASE_PATH.'include/gshell.php');

if(!$_POST['request'])
 return;

$xmloutput = "<xml>";
switch($_POST['request'])
{
 case 'checkuser' : $xmloutput.= _checkUser($_POST['sessid'], $_POST['username']); break;
 case 'validateoldpasswd' : $xmloutput.= _validateOldPasswd($_POST['sessid'], $_POST['username'], $_POST['passwd']); break;
 case 'changepasswd' : $xmloutput.= _changePasswd($_POST['sessid'], $_POST['username'], $_POST['passwd']); break;
}
$xmloutput.="</xml>";

header("Content-Type: application/xml; charset=UTF-8");
echo $xmloutput;
return;

function _checkUser($sessid, $usrname)
{
 $sessInfo = sessionInfo($sessid);
 $username = mysql_escape_string(trim($usrname));
 if(($sessInfo['uname'] != $username) && ($sessInfo['uname'] != "root"))
  return "<request type='checkuser' result='false' msg='You must be root' error='PERMISSION_DENIED'/>";

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_users WHERE username='$username' LIMIT 1");
 if(!$db->Read())
 {
  $db->Close();
  return "<request type='checkuser' result='false' msg='User $username does not exists' error='USER_DOES_NOT_EXISTS'/>";
 }
 $ret = "<request type='checkuser' result='true' ";
 if($db->record['password'] == "")
  $ret.= "passwd='blank'";
 else if($db->record['password'][0] == "!")
  $ret.= "passwd='disabled'";
 $ret.= "/>";
 $db->Close();
 return $ret;
}

function _validateOldPasswd($sessid, $usrname, $passwd)
{
 $sessInfo = sessionInfo($sessid);
 $username = mysql_escape_string(trim($usrname));
 if(($sessInfo['uname'] != $username) && ($sessInfo['uname'] != "root"))
  return "<request type='validateoldpasswd' result='false' msg='You must be root' error='PERMISSION_DENIED'/>";

 $username = mysql_escape_string(trim($usrname));
 $password = mysql_escape_string(trim($passwd));

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_users WHERE username='$username'");
 if(!$db->Read())
 {
  $db->Close();
  return "<request type='validateoldpasswd' result='false' msg='User $username does not exists!' error='USER_DOES_NOT_EXISTS'/>";
 }
 $cryptpass = md5($password.$db->record['regtime']);
 if($db->record['password'] != $cryptpass)
 {
  $db->Close();
  return "<request type='validateoldpasswd' result='false' msg='Password wrong!' error='PASSWORD_WRONG'/>";
 }
 return "<request type='validateoldpasswd' result='true'/>";
}

function _changePasswd($sessid, $usrname, $passwd)
{
 $sessInfo = sessionInfo($sessid);
 $username = mysql_escape_string(trim($usrname));
 if(($sessInfo['uname'] != $username) && ($sessInfo['uname'] != "root"))
  return "<request type='changepasswd' result='false' msg='You must be root' error='PERMISSION_DENIED'/>";

 $username = mysql_escape_string(trim($usrname));
 $password = mysql_escape_string(trim($passwd));

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_users WHERE username='$username'");
 if(!$db->Read())
 {
  $db->Close();
  return "<request type='changepasswd' result='false' msg='User $username does not exists!' error='USER_DOES_NOT_EXISTS'/>";
 }
 $cryptpass = md5($password.$db->record['regtime']);
 $db->RunQuery("UPDATE gnujiko_users SET password='$cryptpass' WHERE id='".$db->record['id']."'");
 $db->Close();
 return "<request type='changepasswd' result='true'/>";
}

