<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 02-01-2012
 #PACKAGE: system-config-gui
 #DESCRIPTION: Terminal configuration form
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("config-terminal");

$sessInfo = sessionInfo($_REQUEST['sessid']);
if($sessInfo['uname'] != "root")
{
 $msg = "You must be root";
 ?>
 <script>
 function bodyOnLoad()
 {
  alert("<?php echo $msg; ?>");
  gframe_close();
 }
 </script>
 <?php
 return;
}
//----------------------------------------------------------------------------------------------------------------------//
?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Config GShell</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
?>
<style type='text/css'>
span {
	font-family: Arial;
	font-size: 12px;
	color: #666666;
}

hr.separator {
	background: #cccccf;
	border: 0px;
	height: 1px;
}

table.configshell th {
	font-size: 12px;
	color: #666666;
	text-align: left;
	border-bottom: 1px solid #cccccf;
}

table.configshell td {
	font-size: 12px;
	color: #0169c9;
	border-bottom: 1px solid #cccccf;
}
</style>
</head><body>
<?php

$form = new GForm(i18n("Configure access to shell"), "MB_OK|MB_ABORT", "simpleform", "default", "orange", 520, 340);
$form->Begin($_ABSOLUTE_URL."share/widgets/config/icons/terminal-icon.png");
echo "<div id='contents'>";
?>
<input type='hidden' name='action' value='setupFTP'/>
<div style='font-size:14px;color:#666666;margin-top:10px;' align='center'><?php echo i18n("Configure the access at the command line shell for the users."); ?></div>
<div style="height:170px;margin-top:20px;overflow:auto;">
<table width='80%' border='0' cellspacing='0' cellpadding='0' align='center' class='configshell'>
<tr><th><?php echo i18n("Username"); ?></th>
	<th><?php echo i18n("Login"); ?></th>
	<th width='40'><?php echo i18n("Enable"); ?></th></tr>
<?php
$db = new AlpaDatabase();
$db->RunQuery("SELECT * FROM gnujiko_users WHERE username!='root' ORDER BY username ASC");
while($db->Read())
{
 echo "<tr><td>&nbsp;".$db->record['fullname']."</td><td>&nbsp;".$db->record['username']."</td><td><input type='checkbox' ".($db->record['enableshell'] ? "checked='true'" : "")." onchange=\"checkUser(this,'".$db->record['username']."')\"/></td></tr>";
}
$db->Close();
?>
</table>
</div>
<?php
echo "</div>";
$form->End();
?>
<script>
function checkUser(cb, username)
{
 var sh = new GShell();
 sh.sendCommand("usermod `"+username+"`"+(cb.checked ? " --enable-shell" : " --disable-shell"));
}
</script>
</body></html>
<?php

