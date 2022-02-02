<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-01-2012
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Official Gnujiko Shell
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DESKTOP_SHOW_TOOLBAR, $_DESKTOP_TITLE, $_SOFTWARE_NAME;

include_once($_BASE_PATH."config.php");
include_once($_BASE_PATH."var/lib/database.php"); // enable and load database access //
include_once($_BASE_PATH."include/i18n.php"); // enable language support //
LoadLanguage("gshell");

$_DESKTOP_SHOW_TOOLBAR = false;
$_DESKTOP_TITLE = i18n("Command line terminal");

include('include/session.php');
include('include/gshell.php');

if($_POST['request'])
{
 define("VALID-GNUJIKO-SHELLREQUEST",1);
 include('gshell_httprequest.php');
 exit();
}

if(!isLogged())
{
 header("Location:accounts/Login.php?continue=".$_ABSOLUTE_URL."gshell.php");
 return;
}

$db = new AlpaDatabase();
$db->RunQuery("SELECT enableshell FROM gnujiko_users WHERE id='".$_SESSION['UID']."'");
$db->Read();
if(!$db->record['enableshell'])
{
 $db->Close();
 header('Location:./');
 exit;
}
$db->Close();

?>
<html><head><link rel='shortcut icon' href='share/images/favicon.png' /><title><?php echo $_SOFTWARE_NAME; ?> - Shell</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<?php
if(file_exists($_BASE_PATH."include/desktop.php"))
{
 include($_BASE_PATH.'include/headings/desktop.php');
}
else
{
 echo "<body>";
 include($_BASE_PATH.'include/headings/default.php');
}
?>

<style type='text/css'>
div#gshell {
	margin:0px; padding: 6px;
	height: 100%;
	overflow: auto;
}
</style>

<div id='gshell' class='console'>
 <font color='gray'><b>GShell 2.0</b></font><br/><br/>
 <font color='green'><?php echo i18n('Welcome to GShell - the official Gnujiko shell.'); ?></font><br/><br/>
<?php echo i18n('This interface behaves similar to a unix-shell.'); ?><br/><?php echo i18n('You type commands and the results are shown on this page.'); ?><br/><br/>
</div>
<br/>
<?php
include('var/objects/gterminal/index.php');

if(file_exists($_BASE_PATH."include/desktop.php"))
 include($_BASE_PATH.'include/footers/desktop.php');
else
 include($_BASE_PATH.'include/footers/default.php');
?>
<script>
 var ter = new GTerminal(document.getElementById('gshell'));
 ter.OnClose = function(){document.location.href='./';};
</script>

</body></html>
<?php

