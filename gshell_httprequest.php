<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 25-03-2013
 #PACKAGE: gnujiko-base
 #DESCRIPTION: GShell HTTP Request file
 #VERSION: 2.3beta
 #CHANGELOG: 25-03-2013 : Bug fix on function restoreSession
			 08-01-2012 : Bug fix in login function with deactivated users.
 #TODO:
 
*/
if(!defined("VALID-GNUJIKO-SHELLREQUEST"))
{
 header('Location:./');
 return;
}

header('Content-Type: application/xml; charset:UTF-8');

$xmloutput = "<xml encoding='utf-8'>";
switch($_POST['request'])
{
 case 'test' : $xmloutput.= test($_POST['sessid'], $_POST['shellid']); break;
 case 'newsession' : $xmloutput.= _newSession($_POST['sessid'], $_POST['shellid']); break;
 case 'closesession' : $xmloutput.= _closeSession($_POST['sessid'],$_POST['lastsessid'], $_POST['shellid']); break;
 case 'login' : $xmloutput.= _login($_POST['sessid'],$_POST['username'],$_POST['password'], $_POST['shellid']); break;
 case 'command' : $xmloutput.= _command($_POST['command'], $_POST['sessid'], $_POST['shellid']); break;
 case 'sudo' : $xmloutput.= _sudo($_POST['command'], $_POST['sessid'], $_POST['shellid'], $_POST['lastsudosessid'], $_POST['passwd']); break;
 case 'searchtabcompletion' : $xmloutput.= _searchTabCompletion($_POST['input'],$_POST['sessid'],$_POST['shellid']); break;
 case 'checkpreoutmessages' : $xmloutput.= _checkPreOutMessages($_POST['sessid'],$_POST['shellid'],$_POST['remove']); break;
 case 'restoresession' : $xmloutput.= _restoreSession($_POST['sessid'],$_POST['shellid'],$_POST['user'],$_POST['passwd']); break;
}
$xmloutput.="</xml>";



echo $xmloutput;
return;

function test($sessid=0, $shellid=0)
{
 /* Questa funzione va mantenuta in quanto il comando rsh la utilizza per verificare la connessione */
 return "<request type='test' message='Gnujiko 10.1 - TEST OK' sessid='".$sessid."' shellid='".$shellid."'/>";
}

function _newSession($sessid, $shellid=0)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SOFTWARE_NAME;
 $time = time();
 $dev = "shell";
 $devid = $shellid;

 if(!$_SESSION['UID'])
 {
  $uid = 0;
  $gid = 0;
  $uname = "www-data";
  $sessid = md5($uid."www-data".$time);
 }
 else
 {
  $uid = $_SESSION['UID'];
  $gid = $_SESSION['GID'];
  $uname = $_SESSION['UNAME'];
  $sessid = md5($_SESSION['UID'].$_SESSION['UNAME'].$time);
 }

 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO gnujiko_session(uname,login_time,time,session_id,uid,gid,dev,devid) VALUES('$uname','$time','$time','$sessid','$uid','$gid','$dev','$devid')");
 $db->Close();
 return "<request type='newsession' result='true' uname='$uname' sessid='$sessid' hostname='$_SOFTWARE_NAME'/>";
}
//-------------------------------------------------------------------------------------------------------------------//
function _closeSession($sessid,$lastsessid,$shellid=0)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SOFTWARE_NAME;
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_session WHERE session_id='$sessid'");
 if($db->Read())
 {
  /* Remove user data shell */
  rmdirr($_BASE_PATH."tmp/session-".$db->record['session_id']);
  rmdirr($_BASE_PATH."tmp/".$db->record['dev']."-".$db->record['devid']);

  if($lastsessid)
  {
   $db->RunQuery("DELETE FROM gnujiko_session WHERE session_id='$sessid'");
   $sessInfo = sessionInfo($lastsessid);
   if($sessInfo['id']) // update session time //
   {
    $db2 = new AlpaDatabase();
    $db2->RunQuery("UPDATE gnujiko_session SET time='".time()."' WHERE session_id='$lastsessid'");
    $db2->Close();
    $ret = "<request type='closesession' result='true' uname='".$sessInfo['uname']."' sessid='$lastsessid' hostname='$_SOFTWARE_NAME'/>";
   }
  }
  else if($db->record['dev'] && $db->record['devid'])
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("DELETE FROM gnujiko_session WHERE dev='".$db->record['dev']."' AND devid='".$db->record['devid']."' AND uname='root'");
   $db2->RunQuery("SELECT * FROM gnujiko_session WHERE dev='".$db->record['dev']."' AND devid='".$db->record['devid']."' AND session_id != '$sessid' ORDER BY login_time DESC LIMIT 1");
   if($db2->Read())
    $ret = "<request type='closesession' result='true' uname='".$db2->record['uname']."' sessid='".$db2->record['session_id']."' hostname='$_SOFTWARE_NAME'/>";
   else
	$ret = "<request type='closesession' result='true' uname='www-data' sessid='0' hostname='$_SOFTWARE_NAME'/>";
  }
  else
   $ret = "<request type='closesession' result='true' uname='www-data' sessid='0' hostname='$_SOFTWARE_NAME'/>";
 }
 else
  $ret = "<request type='closesession' result='true' uname='www-data' sessid='0' hostname='$_SOFTWARE_NAME'/>";
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM gnujiko_session WHERE session_id='$sessid'");
 $db->Close();

 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//
function _login($sessid,$usrname,$passwd,$shellid=0)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SOFTWARE_NAME;
 $time = time();
 $dev = "shell";
 $devid = $shellid;

 $username = mysql_escape_string(trim($usrname));
 $password = mysql_escape_string(trim($passwd));
 
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_users WHERE username='$username'");
 if(!$db->Read())
 {
  $db->Close();
  return "<request type='login' result='false' message='User $username does not exists!' error='USER_DOES_NOT_EXISTS'/>";
 }
 if($db->record['disabled'])
 {
  $db->Close();
  return "<request type='login' result='false' message='User disabled!' error='USER_DISABLED'/>";
 }
 if($db->record['password'] == "!")
  $cryptpass = "!";
 else
  $cryptpass = md5($password.$db->record['regtime']);
 if($db->record['password'] != $cryptpass)
 {
  $db->Close();
  return "<request type='login' result='false' message='Password wrong!' error='PASSWORD_WRONG'/>";
 }
 $uid = $db->record['id'];
 $gid = $db->record['group_id'];
 $uname = $db->record['username'];
 $sessid = md5($uid.$uname.$time);
 
 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO gnujiko_session(uname,login_time,time,session_id,uid,gid,dev,devid) VALUES('$uname','$time','$time','$sessid','$uid','$gid','$dev','$devid')");
 $db->Close();
 return "<request type='login' result='true' uname='$uname' sessid='$sessid' hostname='$_SOFTWARE_NAME'/>";
}
//-------------------------------------------------------------------------------------------------------------------//
function _command($cmd,$sessid,$shellid=0,$retsessid=null,$retuname=null)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SOFTWARE_NAME;

 $retoutput = "";
 $Output = GShell(rawurldecode(stripslashes($cmd)),$sessid,$shellid);
 $sessInfo = sessionInfo($sessid);

 if($Output)
 {
  if(isset($Output['includejs']))
  {
   $retoutput = "<request type='includejssh' file='".$Output['includejs']."' command='".$Output['command']."' sessid='".$sessInfo['id']."' uname='".$sessInfo['uname']."' hostname='$_SOFTWARE_NAME' retsessid='".($retsessid ? $retsessid : $sessid)."' retuname='".($retuname ? $retuname : $sessInfo['uname'])."'/>";
   if($Output['arguments'])
    $retoutput.= array_to_xml($Output['arguments'],'arguments');
   return $retoutput;
  }
  $Output['message'] =  xml_purify(str_replace("\n","&#10;",$Output['message']));
  if(isset($Output['htmloutput']))
   $Output['htmloutput'] =  xml_purify(str_replace("\n","&#10;",$Output['htmloutput']));
  $retoutput = "<request type='command' result='".(isset($Output['error']) ? 'false' : 'true')."'";
  if(isset($Output['error']))
   $retoutput.= " error='".$Output['error']."'";
  $retoutput.= " message='".$Output['message']."' ".(isset($Output['htmloutput']) ? "htmloutput='".$Output['htmloutput']."' " : "")
	."sessid='".(isset($retsessid) ? $retsessid : $sessInfo['id'])."' uname='".($retuname ? $retuname : $sessInfo['uname'])."' hostname='$_SOFTWARE_NAME'/>";
 }
 else
 {
  $cmd = xml_purify($cmd);
  $retoutput = "<request type='command' result='false' error='COMMAND_NOT_FOUND' message='Command not found: ".$cmd."' sessid='"
	.(isset($retsessid) ? $retsessid : $sessInfo['id'])."' uname='".($retuname ? $retuname : $sessInfo['uname'])."' hostname='$_SOFTWARE_NAME'/>";
 }
 if(isset($Output['outarr']))
  $retoutput.= array_to_xml($Output['outarr'], 'output_array');
 if(isset($Output['redirected_outarr']))
  $retoutput.= array_to_xml($Output['redirected_outarr'], 'redirected_outarr');
 return $retoutput;
}
//-------------------------------------------------------------------------------------------------------------------//
function _sudo($cmd, $sessid, $shellid=0, $lastsudosessid=0, $sudopasswd="")
{
 $sessInfo = sessionInfo($sessid);
 if($lastsudosessid)
  $lastSudoSessInfo = sessionInfo($lastsudosessid);

 if($sessInfo['uname'] == "root")
  return _command($cmd, $sessid,$shellid);
 if($lastSudoSessInfo['id'])
  return _command($cmd, $lastsudosessid, $shellid, $sessInfo['id'], $sessInfo['uname']);

 /* Check if user is able for run sudo commands */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_user_privileges WHERE uid='".$sessInfo['uid']."'");
 $db->Read();
 if(!$db->record['run_sudo_commands'])
  return "<request type='sudo' result='false' msg='User ".$sessInfo['uname']." is not able to run sudo commands!' error='SUDO_COMMANDS_DISABLED'/>";
 $db->Close();

 if(!$sudopasswd)
  return "<request type='sudo' result='waitforpasswd'/>";
 
 // Create new session for root //
 global $_SOFTWARE_NAME;
 $time = time();
 $dev = "shell";
 $devid = $shellid;

 $username = "root";
 $password = mysql_escape_string(trim($sudopasswd));
 
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_users WHERE username='$username'");
 if(!$db->Read())
 {
  $db->Close();
  return "<request type='sudo' result='false' msg='User $username does not exists!' error='USER_DOES_NOT_EXISTS'/>";
 }
 $cryptpass = md5($password.$db->record['regtime']);
 if($db->record['password'] != $cryptpass)
 {
  $db->Close();
  return "<request type='sudo' result='false' msg='Password wrong!' error='PASSWORD_WRONG'/>";
 }
 $uid = $db->record['id'];
 $gid = $db->record['group_id'];
 $uname = $db->record['username'];
 $sessid = md5($uid.$uname.$time);
 
 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO gnujiko_session(uname,login_time,time,session_id,uid,gid,dev,devid) VALUES('$uname','$time','$time','$sessid','$uid','$gid','$dev','$devid')");
 $db->Close();
 // EOF create new session for root //

 $retoutput = "<rootsession sessid='$sessid'/>";
 $retoutput.= _command($cmd, $sessid, $shellid, $sessInfo['id'], $sessInfo['uname']);
 return $retoutput;
}
//-------------------------------------------------------------------------------------------------------------------//
function _searchTabCompletion($inp,$sessid)
{
 global $_BASE_PATH, $_SHELL_CMD_PATH, $_USERS_HOMES;
 $retoutput = "";
 // get the last word typed from input //
 $i = strrpos($inp," ");
 $word = trim(substr($inp,$i));

 // search for commands //
 $rc = $_BASE_PATH.$_SHELL_CMD_PATH;
 $h = @opendir($rc);

 while(false !== ($filename = readdir($h)))
 {
  if(($filename == ".") || ($filename == ".."))
   continue;
  if(strtolower(substr($filename, -4, 4)) == '.php')
  {
   if(substr($filename,0,strlen($word)) == $word)
    $retoutput.= "<result type='command' name='".xml_purify(substr($filename,0, strlen($filename)-4))."'/>";
  }
  else if(strtolower(substr($filename, -3, 3)) == '.js')
  {
   if(substr($filename,0,strlen($word)) == $word)
    $retoutput.= "<result type='command' name='".xml_purify(substr($filename,0, strlen($filename)-3))."'/>";
  }
 }

 // search for files and directory //
 $sessInfo = sessionInfo($sessid);
 
 if($sessInfo['uname'] == "root")
  $rc = $_BASE_PATH ? $_BASE_PATH : "./";
 else if((strpos($word,"/") !== false) && (strpos($word,"/") == 0))
 {
  $rc = $_BASE_PATH ? $_BASE_PATH : "./";
  $word = substr($word,1);
 }
 else if($sessInfo['uid'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $rc = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
  $rc = null;

 if($rc != null)
 {
  $i = strrpos($word,"/");
  if($i > -1)
  {
   $rc.= substr($word,0,$i)."/";
   $word = substr($word,$i+1,strlen($word));
  }
  $hf = @opendir($rc);
  if($hf)
  {
   while(false !== ($filename = readdir($hf)))
   {
    if(($filename == ".") || ($filename == ".."))
     continue;
    if(substr($filename,0,strlen($word)) == $word)
    {
     if(is_dir($rc.$filename))
      $retoutput.= "<result type='directory' name='".xml_purify($filename)."/'/>";
     else
      $retoutput.= "<result type='file' name='".xml_purify($filename)."'/>";
    }
   } // EOF WHILE
  }
 }

 $retoutput.= "<request type='searchtabcompletion' result='true' chunkparsed='".xml_purify($word)."'/>";
 return $retoutput;
}
//-------------------------------------------------------------------------------------------------------------------//
function _checkPreOutMessages($sessid, $shellid=0, $remove=false)
{
 global $_BASE_PATH;
 $fileName = $_BASE_PATH."tmp/shell-$shellid/preoutput.xml";
 if(!file_exists($fileName))
  return "<request type='checkpreoutmessages' result='false' error='FILE_DOES_NOT_EXISTS'/>";
 if(!($fp = @fopen($fileName, "r")))
  return "<request type='checkpreoutmessages' result='false' error='FILE_PERMISSION_DENIED'/>";
 $retoutput = "<request type='checkpreoutmessages' result='true'/>";
 while (!feof($fp)) 
  $retoutput.= fread($fp, 8192);
 fclose($fp);

 if(!$remove)
 {
  // empty file //
  $fp = @fopen($fileName, "w");
  if(!@fwrite($fp,""))
  {
   /* Try with FTP */
   gfwrite($fileName, "");
  }
  else
   @fclose($fp);
 }
 else
  @unlink($fileName);
 return $retoutput;
}
//-------------------------------------------------------------------------------------------------------------------//
function _restoreSession($sessid, $shellid=0, $usrname="", $passwd="")
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_session WHERE session_id='".$sessid."'");
 if($db->Read())
 {
  $db->Close();
  $retoutput.= "<request type='restoresession' result='true'/>";
  return $retoutput;
 }
 $db->Close();

 global $_BASE_PATH, $_ABSOLUTE_URL, $_SOFTWARE_NAME;
 $time = time();
 $dev = "shell";
 $devid = $shellid;

 if(!$usrname)
  $usrname = $_SESSION['UNAME'];

 $username = mysql_escape_string(trim($usrname));
 $password = mysql_escape_string(trim($passwd));

 if(!$username || ($username == ""))
  $username = $db->record['uname'];
 
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_users WHERE username='$username'");
 if(!$db->Read())
 {
  $db->Close();
  return "<request type='restoresession' result='false' message='User $username does not exists!' error='USER_DOES_NOT_EXISTS'/>";
 }
 if($db->record['password'] == "!")
  $cryptpass = "!";
 else
  $cryptpass = md5($password.$db->record['regtime']);
 if($db->record['password'] != $cryptpass)
 {
  $db->Close();
  return "<request type='restoresession' result='false' message='Password wrong!' error='PASSWORD_WRONG'/>";
 }
 $uid = $db->record['id'];
 $gid = $db->record['group_id'];
 $uname = $db->record['username'];
 
 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO gnujiko_session(uname,login_time,time,session_id,uid,gid,dev,devid) VALUES('$uname','$time','$time','$sessid','$uid','$gid','$dev','$devid')");
 $db->Close();
 return "<request type='restoresession' result='true'/>";
}
//-------------------------------------------------------------------------------------------------------------------//

