<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 01-02-2013
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Simple get file
 #VERSION: 2.1beta
 #CHANGELOG: 01-02-2013 : Bug fix.
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SESSION, $_USERS_HOMES;

include($_BASE_PATH."init/init1.php");
include($_BASE_PATH."include/session.php");

$sessInfo = sessionInfo($_REQUEST['sessid'] ? $_REQUEST['sessid'] : $_SESSION['SESSID']);

$fileName = $_REQUEST['file'];

if($sessInfo['uname'] == "root")
  $_USER_PATH = $_BASE_PATH;
else if($sessInfo['uid'])
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
 $db->Read();
 $_USER_PATH = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
 $db->Close();
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


