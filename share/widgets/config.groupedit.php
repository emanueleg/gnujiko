<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-01-2012
 #PACKAGE: system-config-gui
 #DESCRIPTION: Group edit form
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
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Edit Group</title>
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
	<td><input type='text' size='20' id='groupname' value="<?php echo $groupInfo['name']; ?>"/></td></tr>
<tr><td align='right'><?php echo i18n('Group ID'); ?></td>
	<td><input type='text' size='3' id='groupid' value="<?php echo $groupInfo['id']; ?>"/></td></tr>
<tr><td align='right' valign='top'><?php echo i18n('Members'); ?></td>
	<td><div class='memberslist'>
	<?php
	$db = new AlpaDatabase();
	$db->RunQuery("SELECT id,group_id,username FROM gnujiko_users WHERE username!='root' ORDER BY username ASC");
	while($db->Read())
	{
	 if($db->record['group_id'] == $groupInfo['id'])
	  echo "<input type='checkbox' id='".$db->record['username']."' checked='true' onchange='usercheck(this)' disabled='disabled'/>".$db->record['username']."<br/>";
	 else
	 {
	  $db2 = new AlpaDatabase();
	  $db2->RunQuery("SELECT uid,gid FROM gnujiko_usergroups WHERE uid='".$db->record['id']."' AND gid='".$groupInfo['id']."' LIMIT 1");
	  if($db2->Read())
	   echo "<input type='checkbox' id='".$db->record['username']."' checked='true' onchange='usercheck(this)'/>".$db->record['username']."<br/>";
	  else
	   echo "<input type='checkbox' id='".$db->record['username']."' onchange='usercheck(this)'/>".$db->record['username']."<br/>";
	  $db2->Close();
	 }
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
function usercheck(cb)
{
 var sh = new GShell();
 if(cb.checked)
  sh.sendCommand("groupadd <?php echo $groupInfo['name']; ?> "+cb.id);
 else
  sh.sendCommand("userdel "+cb.id+" <?php echo $groupInfo['name']; ?>");
}

function OnFormSubmit()
{
 var GroupName = document.getElementById('groupname').value;
 var GroupID = document.getElementById('groupid').value;

 var sh = new GShell();
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 sh.sendCommand("groupmod -id `<?php echo $groupInfo['id']; ?>` -name `"+GroupName+"`"+(GroupID != "<?php echo $groupInfo['id']; ?>" ? " -newid "+GroupID : ""));
 return false;
}
</script>
</body></html>
<?php

