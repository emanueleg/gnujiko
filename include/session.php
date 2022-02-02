<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 03-10-2016
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Gnujiko session manager
 #VERSION: 2.10beta
 #CHANGELOG: 03-10-2016 : Mobile Detect integration.
			 27-08-2014 : Restricted Access integration.
			 21-03-2014 : Aggiunto setlocale e date_default_timezone_set per bug date con ora legale.
			 19-04-2013 : Bug fix in DEFAULT_FILE_PERMS.
			 11-04-2013 : Sistemato i permessi ai files.
			 31-01-2013 : Possibilità di lanciare manualmente il session_write_close()
			 09-02-2012 : Bug fix in loginRequired.
			 22-01-2012 : Bug fix in session restore.
			 09-08-2010 : Aggiunto prevent session expiry che ci viene in aiuto in caso scadesse la sessione ma lo script ha ancora l'ID in mano.
 #TODO: manca il multilingua.
 
*/

global $_DATABASE_NAME, $_SESSION, $_BASE_PATH, $_DEFAULT_FILE_PERMS, $_LANGUAGE;

include_once($_BASE_PATH."include/filesfunc.php");
include_once($_BASE_PATH."var/lib/database.php");

if($_LANGUAGE == "it-IT")
{
 setlocale(LC_TIME, "it_IT");
 date_default_timezone_set("Europe/Rome");
}

/* Imposta i permessi di default ai files nel caso non sia impostata la variabile $_DEFAULT_FILE_PERMS nel file di configurazione. */
if($_DEFAULT_FILE_PERMS)
 $_DEFAULT_FILE_PERMS = intval($_DEFAULT_FILE_PERMS,8);
else 
 $_DEFAULT_FILE_PERMS = 0777;

session_name("Gnujiko-$_DATABASE_NAME");
session_start();

$now = time();
$past = $now - 28800; // aumentato a 8 ore //
 
/* PREVENT SESSION EXPIRY */
if(!$_SESSION['SESSID'] && $_REQUEST['sessid'])
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_session WHERE session_id='".$_REQUEST['sessid']."'");
 if($db->Read())
 {
  $_SESSION['SESSID'] = $_REQUEST['sessid'];
  $_SESSION['UID'] = $db->record['uid'];
  $_SESSION['GID'] = $db->record['gid'];
  $_SESSION['UNAME'] = $db->record['uname'];
  $_SESSION['LOGINTIME'] = $now;

  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT fullname,email,homedir FROM gnujiko_users WHERE id='".$db->record['uid']."'");
  $db2->Read();
  $_SESSION['FULLNAME'] = $db2->record['fullname'];
  $_SESSION['EMAIL'] = $db2->record['email'];
  $_SESSION['HOMEDIR'] = $db2->record['homedir'];
  $db2->Close();
 
  $ip = getenv("REMOTE_ADDR");
  $_SESSION['LOGINIP'] = $ip;
 }
 $db->Close();
}

$db = new AlpaDatabase();
$db->RunQuery("UPDATE gnujiko_session SET time='".$now."' WHERE session_id='".$_SESSION['SESSID']."'");
$db->RunQuery("SELECT * FROM gnujiko_session WHERE time < '".$past."'");
while($db->Read())
{
 rmdirr($_BASE_PATH."tmp/session-".$db->record['session_id']);
 rmdirr($_BASE_PATH."tmp/".$db->record['dev']."-".$db->record['devid']);
}
$db->RunQuery("DELETE FROM gnujiko_session WHERE time < '".$past."'");
$db->Close();

if(!defined("MANUAL-SESSION-WRITE-CLOSE"))
 session_write_close();

// Mobile Detect
if(!$_COOKIE['gnujiko_ui_devtype'] && file_exists($_BASE_PATH."include/mobiledetect.php"))
{
 require_once($_BASE_PATH."include/mobiledetect.php");
 $detect = new Mobile_Detect;
 $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
 setcookie("gnujiko_ui_devtype",$deviceType,strtotime("+1 week"), "/");
}

function isLogged()
{
 if(!$_SESSION['UID'])
  return false;
 return true;
}

function loginRequired()
{
 global $_BASE_PATH, $_ABSOLUTE_URL;
 if($_SESSION['UID'])
  return true;

 $continue = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
 echo "<html><head><title>Gnujiko Login Required</title>";
 echo '<meta http-equiv="refresh" content="0;url='.$_ABSOLUTE_URL.'accounts/Login.php?continue='.urlencode($continue).'">';
 echo "</head><body>Per accedere a questa pagina devi essere loggato.<br/>Clicca <a href='"
	.$_ABSOLUTE_URL.'accounts/Login.php?continue='.urlencode($continue)."'>qui</a> per effettuare il login.</body></html>";
 return false;
}

function restrictedAccess($group)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;
 include_once($_BASE_PATH."include/userfunc.php");
 if(_userInGroup($group))
  return true;

 ?>
 <html><head><meta http-equiv="content-type" content="text/html;charset=UTF-8"/><title>Restricted Access</title>
  <link rel="shortcut icon" href="<?php echo $_ABSOLUTE_URL; ?>share/images/favicon.png"/>
  <style type="text/css">
  div.access-denied {
	width: 460px;
	height: 220px;
	background: #fff;
	border: 3px solid #f31903;
	border-radius: 3px;
	position: absolute;
	left: 50%;
	top: 50%;
	margin-left: -230px;
	margin-top: -110px;
  }
  div.access-denied h3 {
	font-family: Arial;
	font-size: 20px;
	color: #d40000;
	margin:10px;
  }
  div.access-denied p {
	font-family: Arial;
	font-size: 14px;
	color: #000000;
	margin:10px;
  }
  div.access-denied p small {
	font-family: Arial;
	font-size: 12px;
	color: #333333;
  }
  input.button-gray, input.button-blue, input.button-red {
	height: 30px;
	border-radius: 2px;
	border: 1px solid #d9d9d9;
	font-family: Arial,sans-serif;
	font-size: 12px;
	white-space: nowrap;
	padding-left: 12px;
	padding-right: 12px;
	cursor: pointer;
	background: #f4f4f4;
	font-weight: bold;
	text-align: left;
	overflow: hidden;
  }
  input.button-blue {border-color: #4285f4; background-color: #4583ec; color: #ffffff;}
  input.button-red {border-color: #d40000; background-color: #f44800; color: #ffffff;}
  </style>
  </head><body>
  <div class='access-denied'>
   <h3>ACCESSO NEGATO</h3>
     <p>Spiacente!, non hai i permessi per accedere a quest&lsquo;area.</p>
     <p>
      <img src="<?php echo $_ABSOLUTE_URL; ?>share/images/restricted_access.png" style="vertical-align:top;text-align:left;margin:12px;float:left;"/>
	  <small>Per poter accedere a quest&lsquo;area devi essere membro del gruppo <b><?php echo $group; ?></b>.</small><br/><br/>
	  <input type="button" class="button-blue" value="&laquo; Torna alla homepage" onclick="document.location.href='<?php echo $_ABSOLUTE_URL; ?>'"/>
    </p>
   </form>
  </div>
  </body></html>
 <?php
 return false;
}

function sessionInfo($sessid=null)
{
 if($sessid)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM gnujiko_session WHERE session_id='$sessid'");
  if($db->Read())
   $ret = array('id'=>$sessid,'uid'=>$db->record['uid'],'gid'=>$db->record['gid'],'uname'=>$db->record['uname']);
  else
   $ret = array('id'=>0,'uid'=>0,'gid'=>0,'uname'=>'www-data');
  $db->Close();
 }
 else if($_SESSION['SESSID'])
  $ret = array('id'=>$_SESSION['SESSID'],'uid'=>$_SESSION['UID'],'gid'=>$_SESSION['GID'],'uname'=>$_SESSION['UNAME']);
 else
  $ret = array('id'=>0,'uid'=>0,'gid'=>0,'uname'=>'www-data');
 return $ret;
}

