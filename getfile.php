<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 25-02-2016
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Simple get file
 #VERSION: 2.3beta
 #CHANGELOG: 25-02-2016 : Aggiunto etc/mailbox/attachments tra la lista delle directory accessibili.
			 06-03-2015 : Ora è possibile specificare il basedir per prelevare file fuori dalle home utenti ma solo x alcune directory.
			 23-01-2014 : Ora è possibile specificare il file comprensivo di "home/nome-utente/" (se l'utente è loggato) per ottenere file di altri utenti.
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SESSION, $_USERS_HOMES;

include($_BASE_PATH."init/init1.php");
include($_BASE_PATH."include/session.php");

$sessInfo = sessionInfo($_REQUEST['sessid'] ? $_REQUEST['sessid'] : $_SESSION['SESSID']);

$fileName = $_REQUEST['file'];

if($_REQUEST['basedir'])
{
 $_ACCESS_DIR = array("share/", "etc/mailbox/attachments/");			/* Lista directory accessibili */
 $_BLACKLIST = array("share/widgets/");									/* Lista directory non accessibili */
 $_BLACKEXT = array(".php", ".js");										/* Lista estensioni non ammesse */

 for($c=0; $c < count($_BLACKLIST); $c++)
 {
  $dir = $_BLACKLIST[$c];
  $pos = strpos($_REQUEST['basedir'],$dir);
  if(($pos !== false) && ($pos == 0))
   die("PERMISSION DENIED");
 }

 $_OK = false;
 for($c=0; $c < count($_ACCESS_DIR); $c++)
 {
  $dir = $_ACCESS_DIR[$c];
  $pos = strpos($_REQUEST['basedir'],$dir);
  if(($pos !== false) && ($pos == 0))
  {
   $_OK = true;
   break;
  }
 }

 if(!$_OK)
  die("PERMISSION DENIED");

 for($c=0; $c < count($_BLACKEXT); $c++)
 {
  if(strrpos($fileName, $_BLACKEXT[$c]) !== false)
   die("PERMISSION DENIED");
 }

 $_USER_PATH = $_REQUEST['basedir'];
}
else if($sessInfo['uname'] == "root")
 $_USER_PATH = $_BASE_PATH;
else if($sessInfo['uid'])
{
 if(substr($fileName,0,strlen($_USERS_HOMES)) == $_USERS_HOMES)
  $_USER_PATH = $_BASE_PATH;
 else
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $_USER_PATH = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
}
else
 $_USER_PATH = $_BASE_PATH."tmp/";

if(!file_exists($_USER_PATH.$fileName))
 die("File $fileName does not exists!");

$file = $_USER_PATH.$fileName;
$time = date('r');

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
$outname = str_replace(array("+","%3A"),array(" ",":"),urlencode(basename($file)));
header('Content-Disposition: attachment; filename="'.$outname.'"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');
header("Last-Modified: $time");
header('Content-Length: ' . filesize($file));
ob_clean();
flush();
readfile($file);
exit;


