<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 19-04-2013
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Gnujiko session manager
 #VERSION: 2.6beta
 #CHANGELOG: 19-04-2013 : Bug fix in DEFAULT_FILE_PERMS.
			 11-04-2013 : Sistemato i permessi ai files.
			 31-01-2013 : Possibilità di lanciare manualmente il session_write_close()
			 09-02-2012 : Bug fix in loginRequired.
			 22-01-2012 : Bug fix in session restore.
			 09-08-2010 : Aggiunto prevent session expiry che ci viene in aiuto in caso scadesse la sessione ma lo script ha ancora l'ID in mano.
 #TODO: manca il multilingua.
 
*/

global $_DATABASE_NAME, $_SESSION, $_BASE_PATH, $_DEFAULT_FILE_PERMS;

include_once($_BASE_PATH."include/filesfunc.php");
include_once($_BASE_PATH."var/lib/database.php");

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
$db->RunQuery("UPDATE gnujiko_session SET time='$now' WHERE session_id='".$_SESSION['SESSID']."'");
$db->RunQuery("SELECT * FROM gnujiko_session WHERE time < '$past'");
while($db->Read())
{
 rmdirr($_BASE_PATH."tmp/session-".$db->record['session_id']);
 rmdirr($_BASE_PATH."tmp/".$db->record['dev']."-".$db->record['devid']);
}
$db->RunQuery("DELETE FROM gnujiko_session WHERE time < '$past'");
$db->Close();

if(!defined("MANUAL-SESSION-WRITE-CLOSE"))
 session_write_close();

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

