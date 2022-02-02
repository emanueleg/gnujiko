<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-04-2013
 #PACKAGE: system-config-gui
 #DESCRIPTION: User and Group Manager configuration form
 #VERSION: 2.1beta
 #CHANGELOG: 30-04-2013 : Bug fix grafici.
			 25-01-2012 : Ora anche l'utente root viene mostrato in lista.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

if($_REQUEST['show'] == "groups")
{
 include($_BASE_PATH."share/widgets/config.groups.php");
 exit;
}

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("config-usergroups");

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
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>User and Group Manager</title>
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

table.configugman th {
	font-size: 12px;
	color: #666666;
	text-align: left;
	border-bottom: 1px solid #cccccf;
}

table.configugman td {
	font-size: 12px;
	color: #0169c9;
	border-bottom: 1px solid #cccccf;
}

ul.toolbar {
	margin: 0px;
	padding: 0px;
	list-style: none;
	padding-left: 20px;
	padding-top: 6px;
}

ul.toolbar li {
	float: left;
}

ul.toolbar li.item {
	float: left;
	height: 20px;
	background: #6699cc;
	cursor: pointer;
	font-family: Arial;
	font-size: 12px;
	color: #ffffff;
	margin-left: 1px;
	margin-right: 1px;
	margin-top: 1px;
	padding-left: 4px;
	padding-right: 4px;
	line-height: 1.5em;
	font-weight: bold;
}

ul.toolbar li.separator {
	background: transparent;
	width: 10px;
}

ul.toolbar li.roundleft-btn {
	background: #aaccee url(config/img/roundbtn_left.png) top left no-repeat;
	height: 22px;
	font-family: Arial;
	font-size: 12px;
	color: #ffffff;
	padding-left: 8px;
	padding-right: 8px;
	margin-right: 1px;
	margin-top: 0px;
}

ul.toolbar li.roundleft-btn a {
	font-family: Arial;
	font-size: 12px;
	color: #ffffff;
	text-decoration: none;
	line-height: 1.7em;
	font-weight: bold;
}

ul.toolbar li.roundleft-btn-active {
	background: #0169c9 url(config/img/roundbtn_left.png) top left no-repeat;
	height: 22px;
	font-family: Arial;
	font-size: 14px;
	color: #ffffff;
	padding-left: 8px;
	padding-right: 8px;
	margin-right: 1px;
	margin-top: 0px;
}

ul.toolbar li.roundleft-btn-active span {
	font-family: Arial;
	font-size: 14px;
	color: #ffffff;
	line-height: 1.5em;
	font-weight: bold;
}

ul.toolbar li.roundright-btn {
	background: #aaccee url(config/img/roundbtn_right.png) top right no-repeat;
	height: 22px;
	font-family: Arial;
	font-size: 12px;
	color: #ffffff;
	padding-left: 8px;
	padding-right: 8px;
	margin-top: 0px;
}

ul.toolbar li.roundright-btn a {
	font-family: Arial;
	font-size: 12px;
	color: #ffffff;
	text-decoration: none;
	line-height: 1.7em;
	font-weight: bold;
}

ul.toolbar li.roundright-btn-active {
	background: #0169c9 url(config/img/roundbtn_right.png) top right no-repeat;
	height: 22px;
	font-family: Arial;
	font-size: 14px;
	color: #ffffff;
	padding-left: 8px;
	padding-right: 8px;
	margin-top: 0px;
}

ul.toolbar li.roundright-btn-active span {
	font-family: Arial;
	font-size: 14px;
	color: #ffffff;
	line-height: 1.5em;
	font-weight: bold;
}

a.disabled {
	color: #f31903;
}
</style>
</head><body>
<?php

$form = new GForm(i18n("User and group manager"), "MB_OK|MB_ABORT", "simpleform", "default", "orange", 640, 480);
$form->Begin($_ABSOLUTE_URL."share/widgets/config/icons/usergroups-icon.gif");
echo "<div id='contents'>";
?>
<div style="height: 32px;padding:5px;">
 <ul class='toolbar'>
  <li class='roundleft-btn-active'><span><?php echo i18n("Users"); ?></span></li>
  <li class='roundright-btn'><a href="?sessid=<?php echo $_REQUEST['sessid']; ?>&show=groups"><?php echo i18n("Groups"); ?></a></li>
  <li onclick='addUser()' class='item' style='margin-left:30px;'><?php echo i18n("Add new user"); ?></li>
  <li onclick="deleteSelectedUsers()" class='item'><?php echo i18n("Delete user/s"); ?></li>
  <li class='separator'>&nbsp;</li>
  <li onclick="activateSelectedUsers()" class='item'><?php echo i18n("Activate user/s"); ?></li>
  <li onclick="deactivateSelectedUsers()" class='item'><?php echo i18n("Deactivate user/s"); ?></li>
 </ul>
</div>

<div style="height:300px;margin-top:20px;overflow:auto;">
<table width='90%' border='0' cellspacing='0' cellpadding='0' align='center' class='configugman' id='configtb'>
<tr><th width='24'><input type='checkbox' onchange='checkAll(this)'/></th>
	<th><?php echo i18n('Fullname'); ?></th>
	<th><?php echo i18n('Login'); ?></th>
	<th><?php echo i18n('Email'); ?></th>
	<th width='60'><?php echo i18n('Active'); ?></th>
	<th width='60'><?php echo i18n('Shell'); ?></th></tr>
<?php
$db = new AlpaDatabase();
$db->RunQuery("SELECT * FROM gnujiko_users WHERE 1 ORDER BY username ASC");
while($db->Read())
{
 echo "<tr id='".$db->record['id']."'><td>".($db->record['username'] != "root" ? "<input type='checkbox'/>" : "<input type='checkbox' disabled='true'/>")."</td>";
 echo "<td>".($db->record['fullname'] ? "<a href='#' onclick='editUser(".$db->record['id'].")'".($db->record['disabled'] ? " class='disabled'" : "").">".$db->record['fullname']."</a>" : "&nbsp;")."</td>";
 echo "<td><a href='#' onclick='editUser(".$db->record['id'].")' ".($db->record['disabled'] ? " class='disabled'" : "").">".$db->record['username']."</a></td>";
 echo "<td>".($db->record['email'] ? $db->record['email'] : "&nbsp;")."</td>";
 echo "<td><img src='".$_ABSOLUTE_URL."share/widgets/config/img/".($db->record['disabled'] ? "check_KO.gif" : "check_OK.gif")."'/></td>";
 echo "<td><img src='".$_ABSOLUTE_URL."share/widgets/config/img/".($db->record['enableshell'] ? "check_OK.gif" : "check_KO.gif")."'/></td></tr>";
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
function checkAll(cb)
{
 var tb = document.getElementById('configtb');
 for(var c=1; c < tb.rows.length; c++)
 {
  if(tb.rows[c].cells[0].getElementsByTagName("INPUT")[0].disabled)
   continue;
  tb.rows[c].cells[0].getElementsByTagName("INPUT")[0].checked = cb.checked;
 }
}

function addUser()
{
 var sh = new GShell();
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("gframe -f config.usernew");
}

function editUser(uid)
{
 var sh = new GShell();
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("gframe -f config.useredit -params uid="+uid);
}

function deleteSelectedUsers()
{
 var selected = new Array();
 var tb = document.getElementById('configtb');
 for(var c=1; c < tb.rows.length; c++)
 {
  if(tb.rows[c].cells[0].getElementsByTagName("INPUT")[0].checked)
   selected.push(tb.rows[c].cells[2].getElementsByTagName('A')[0].innerHTML);
 }
 if(!selected.length)
  return alert("<?php echo i18n('You must select at least one user'); ?>");
 if(!confirm("<?php echo i18n('Are you sure you want to delete the selected users?'); ?>"))
  return;

 var sh = new GShell();
 sh.OnFinish = function(){document.location.reload();}
 for(var c=0; c < selected.length; c++)
  sh.sendCommand("userdel `"+selected[c]+"` -all");
}

function activateSelectedUsers()
{
 var selected = new Array();
 var tb = document.getElementById('configtb');
 for(var c=1; c < tb.rows.length; c++)
 {
  if(tb.rows[c].cells[0].getElementsByTagName("INPUT")[0].checked)
   selected.push(tb.rows[c].cells[2].getElementsByTagName('A')[0].innerHTML);
 }
 if(!selected.length)
  return alert("<?php echo i18n('You must select at least one user'); ?>");
 var msg = "<?php echo i18n('%d users will be activated. Proceed?'); ?>";
 if(!confirm(msg.replace('%d',selected.length)))
  return;

 var sh = new GShell();
 sh.OnFinish = function(){document.location.reload();}
 for(var c=0; c < selected.length; c++)
  sh.sendCommand("usermod `"+selected[c]+"` --enable-account");
}

function deactivateSelectedUsers()
{
 var selected = new Array();
 var tb = document.getElementById('configtb');
 for(var c=1; c < tb.rows.length; c++)
 {
  if(tb.rows[c].cells[0].getElementsByTagName("INPUT")[0].checked)
   selected.push(tb.rows[c].cells[2].getElementsByTagName('A')[0].innerHTML);
 }
 if(!selected.length)
  return alert("<?php echo i18n('You must select at least one user'); ?>");
 if(!confirm("<?php echo i18n('Are you sure you want to deactivate the selected users?'); ?>"))
  return;

 var sh = new GShell();
 sh.OnFinish = function(){document.location.reload();}
 for(var c=0; c < selected.length; c++)
  sh.sendCommand("usermod `"+selected[c]+"` --disable-account");
}

</script>
</body></html>
<?php

