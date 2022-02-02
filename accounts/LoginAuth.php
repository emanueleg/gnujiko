<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-10-2016
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Login Authorization check
 #VERSION: 2.6beta
 #CHANGELOG: 24-10-2016 : Sostituita funzione mysql escape string con funzione db->EscapeString() per integrazione con mysqli.
			 16-03-2015 : Aggiunto check for updates.
			 29-11-2014 : Login bug fix.
			 05-11-2014 : Bug fix che causava pagina bianca.
			 02-09-2014 : Integrato con i servizi.
			 08-01-2012 : Bug fix in login function for user with disabled password.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../";

include($_BASE_PATH."init/init1.php");

if(isset($_POST['signIn']))
{
 if(!$_POST['Username'])
  header("Location:".$_SERVER['HTTP_REFERER'].(strpos($_SERVER['HTTP_REFERER'],"?") ? "&" : "?")."err=badlogin&username=".$_POST['Username']);

 $ret = login();

 if(is_array($ret) && $ret['error'])
   header("Location:".$_SERVER['HTTP_REFERER'].(strpos($_SERVER['HTTP_REFERER'],"?") ? "&" : "?")."err="
	.$ret['error']."&errmsg=".$ret['message']."&username=".$_POST['Username']);
 else if($ret)
  header("Location:".($_POST['continue'] ? urldecode($_POST['continue']) : $_ABSOLUTE_URL));
}
else
 header("Location:".$_ABSOLUTE_URL);


function login()
{
 $db = new AlpaDatabase();
 $username = $db->EscapeString(trim($_POST['Username']));
 $password = $db->EscapeString(trim($_POST['Password']));
 
 
 $db->RunQuery("SELECT * FROM gnujiko_users WHERE username='".$username."'");
 if($db->Error) return array('message'=>'MySQL Error:'.$db->Error, 'error'=>'MYSQL_ERROR');
 if(!$db->Read())
 {
  $db->Close();
  return array('message'=>'User '.$username.' does not exists.', 'error'=>"USER_DOES_NOT_EXISTS");
 }
 if($db->record['disabled'])
 {
  $db->Close();
  return array('message'=>'Can not connect with the user '.$username.' because has been disabled.', 'error'=>'USER_DISABLED');
 }
 if($db->record['password'] == "!")
  $cryptpass = "!";
 else
  $cryptpass = md5($password.$db->record['regtime']);
 if($db->record['password'] != $cryptpass)
 {
  $db->Close();
  return array('message'=>'Wrong password for user '.$username.'.', 'error'=>'WRONG_PASSWORD');
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
	.$uname."','".$time."','".$time."','".$sessid."','".$uid."','".$gid."','0','0')");
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
 if($_POST['StayConnected'])
 {
  setcookie("username",$username,strtotime("+1 week"), "/");
  setcookie("password",$password,strtotime("+1 week"), "/");
  setcookie("session_id",$sessid,strtotime("+1 week"), "/");
  setcookie("user_logout",false,strtotime("+1 week"), "/");
  setcookie("stayconnected",'on',strtotime("+1 week"), "/");
 }
 else
 {
  setcookie("username",null,strtotime("+1 week"), "/");
  setcookie("password",null,strtotime("+1 week"), "/");
  setcookie("session_id",null,strtotime("+1 week"), "/");
  setcookie("stayconnected",'off',strtotime("+1 week"), "/");
 }

 /* RUN SERVICES */
 define("VALID-GNUJIKO",1);
 include_once($_BASE_PATH."include/gshell.php");
 GShell("system run-services");

 /* CHECK FOR UPDATES */
 $ret = GShell("system check-for-updates");

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

