<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-01-2012
 #PACKAGE: system-config-gui
 #DESCRIPTION: New group form
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

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

$db = new AlpaDatabase();
$db->RunQuery("SELECT * FROM gnujiko_groups WHERE id='".$_REQUEST['gid']."'");
$db->Read();
$groupInfo = $db->record;
$db->Close();
//----------------------------------------------------------------------------------------------------------------------//
?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>New Group</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
?>
<style type='text/css'>
div.memberslist {
	height: 220px;
	overflow: auto;
	border: 1px solid #aaccee;
	background: #ffffff;
}
</style>
</head><body>
<?php
$form = new GForm(i18n("Edit group")." - ".$groupInfo['name'], "MB_OK|MB_ABORT", "simpleform", "default", "blue", 480, 404);
$form->Begin($_ABSOLUTE_URL."share/widgets/config/icons/group-edit.png");
echo "<div id='contents'>";
?>
<table width='90%' border='0'>
<tr><td align='right' width='130'><?php echo i18n('Group name'); ?></td>
	<td><input type='text' size='20' id='groupname' value=""/></td></tr>
<tr><td align='right'><?php echo i18n('Group ID'); ?></td>
	<td><input type='radio' name='groupid' checked='true' id='autogroupid' onchange='autogroupidChange()'/><?php echo i18n("automatic"); ?>
<input type='radio' name='groupid' onchange='autogroupidChange()'/><?php echo i18n("specify"); ?>: <input type='text' size='3' id='groupid' value="" disabled="disabled"/></td></tr>
<tr><td align='right' valign='top'><?php echo i18n('Members'); ?></td>
	<td><div class='memberslist' id='members'>
	<?php
	$db = new AlpaDatabase();
	$db->RunQuery("SELECT id,group_id,username FROM gnujiko_users WHERE username!='root' ORDER BY username ASC");
	while($db->Read())
	{
	 echo "<input type='checkbox' id='".$db->record['username']."'/>".$db->record['username']."<br/>";
	}
	$db->Close();
	?>
	</div></td></tr>
</table>
<?php
echo "</div>";
$form->End();
?>
<script>
function autogroupidChange()
{
 document.getElementById('groupid').disabled = document.getElementById('autogroupid').checked;
}

function OnFormSubmit()
{
 var GroupName = document.getElementById('groupname').value;
 var GroupID = document.getElementById('groupid').value;
 var autoGroupId = document.getElementById('autogroupid').checked;

 if(!GroupName)
 {
  alert("<?php echo i18n('You must specify the group name'); ?>");
  return false;
 }

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 var div = document.getElementById('members');
	 var list = div.getElementsByTagName('INPUT');
	 if(!list.length) return gframe_close(o,a);
	 var selected = new Array();
	 for(var c=0; c < list.length; c++)
	  if(list[c].checked)
	   selected.push(list[c].id);
	 if(!selected.length)
	  return gframe_close(o,a);

	 var sh2 = new GShell();
	 sh2.OnFinish = function(){gframe_close();}
	 for(var c=0; c < selected.length; c++)
	  sh2.sendCommand("groupadd `"+GroupName+"` "+selected[c]);
	}
 sh.sendCommand("groupadd `"+GroupName+"`"+(!autoGroupId ? " -setid "+GroupID : ""));
 return false;
}
</script>
</body></html>
<?php

