<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 08-01-2012
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Login Authorization check
 #VERSION: 2.0beta
 #CHANGELOG: 08-01-2012 : Bug fix in login function for user with disabled password.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../";

include($_BASE_PATH."init/init1.php");

if(isset($_POST['signIn']))
{
 if(!$_POST['Username'] || !login())
  header("Location:".$_SERVER['HTTP_REFERER'].(strpos($_SERVER['HTTP_REFERER'],"?") ? "&" : "?")."err=badlogin&username=".$_POST['Username']);
 else
  header("Location:".($_POST['continue'] ? urldecode($_POST['continue']) : $_ABSOLUTE_URL));
}

function login()
{
 $username = mysql_escape_string(trim($_POST['Username']));
 $password = mysql_escape_string(trim($_POST['Password']));
 
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_users WHERE username='$username'");
 if(!$db->Read())
 {
  $db->Close();
  $err = "USER_DOES_NOT_EXISTS";
  return false;
 }
 if($db->record['disabled'])
 {
  $err = "USER_DISABLED";
  return false;
 }
 if($db->record['password'] == "!")
  $cryptpass = "!";
 else
  $cryptpass = md5($password.$db->record['regtime']);
 if($db->record['password'] != $cryptpass)
 {
  $db->Close();
  $err = "BAD_PASSWORD";
  return false;
 }

 $time = time();
 $uid = $db->record['id'];
 $gid = $db->record['group_id'];
 $uname = $db->record['username'];
 $sessid = md5($uid.$uname.$time);
 $host = $_SERVER['REMOTE_ADDR'];
 $fullname = $db->record['fullname'];
 $email = $db->record['email'];
 $homedir = $db->record['homedir'];
 $lastTimeAccess = $db->record['last_time_access'];

 $db->RunQuery("INSERT INTO gnujiko_session(uname,login_time,time,session_id,uid,gid,dev,devid) VALUES('"
	.$uname."','$time','$time','$sessid','$uid','$gid','0','0')");
 $db->Close();

 //--- UPDATE SESSION ---//
 global $_BASE_PATH, $_DATABASE_NAME;
 session_name("Gnujiko-$_DATABASE_NAME");
 session_start();
  
 $_SESSION['SESSID'] = $sessid;
 $_SESSION['UID'] = $uid;
 $_SESSION['GID'] = $gid;
 $_SESSION['UNAME'] = $uname;
 $_SESSION['LOGINTIME'] = $time;
 $_SESSION['FULLNAME'] = $fullname;
 $_SESSION['EMAIL'] = $email;
 $_SESSION['HOMEDIR'] = $homedir;
 $ip = getenv("REMOTE_ADDR");
 $_SESSION['LOGINIP'] = $ip;

 session_write_close();

 //--- set cookie ---//
 if($_POST['StayConnected'] == 'on')
 {
  setcookie("username",$username,strtotime("+1 week"));
  setcookie("password",$password,strtotime("+1 week"));
  setcookie("session_id",$sessid,strtotime("+1 week"));
  setcookie("user_logout",false);
  setcookie("stayconnected",true,strtotime("+1 week"));
 }
 else
 {
  setcookie("username",null,strtotime("+1 week"));
  setcookie("password",null,strtotime("+1 week"));
  setcookie("session_id",null,strtotime("+1 week"));
  setcookie("stayconnected",false,strtotime("+1 week"));
 }
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

