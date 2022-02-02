<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-11-2014
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Set item permissions by a graphical user interface.
 #VERSION: 2.1beta
 #CHANGELOG: 21-11-2014 : Bug fix su rilevazione gruppo.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_AP, $_CAT_ID;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");
include_once($_BASE_PATH."include/userfunc.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
LoadLanguage("dynarc");

$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : $_REQUEST['archiveprefix'];
$_ID = $_REQUEST['id'];

if($_AP && $_ID)
{
 $ret = GShell("dynarc item-info -ap '".$_AP."' -id '".$_ID."'",$_REQUEST['sessid'],$_REQUEST['shellid']);
 if(!$ret['error'])
 {
  $itemInfo = $ret['outarr'];
  /* GET OWNER */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id,username,fullname FROM gnujiko_users WHERE id='".$itemInfo['modinfo']['uid']."'");
  $db->Read();
  $OwnerId = $db->record['id'];
  $Owner = $db->record['username'];
  $OwnerName = $db->record['fullname'];

  /* GET GROUP */
  $db->RunQuery("SELECT name FROM gnujiko_groups WHERE id='".$itemInfo['modinfo']['gid']."'");
  $db->Read();
  $GroupId = $itemInfo['modinfo']['gid'];
  $GroupName = $db->record['name'];
  $db->Close();
  $mod = $ret['outarr']['modinfo']['mod'];
 }
 else
 {
  echo "<h4 style='color:#f31903;'>".$ret['message']."</h4>";
 }
}

$_SESSINFO = sessionInfo($_REQUEST['sessid']);

?>
<script>var BASE_PATH = "../../"; </script>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/xrequest.js" type="text/javascript"></script>
<?php
$form = new GForm(i18n("Item permissions"), null, "simpleform", "default", "orange", 600, 380);
$form->Begin();
?>
 <div style='font-family:Arial;font-size:14px;color:#000000;margin-bottom:12px;'><b><?php echo i18n('Item permissions'); ?> <?php echo $itemInfo['name']; ?></b></div>
	<table width='340' border='0' id='permissions-form-layer' style='font-size:12px;'>
	<tr><td><?php echo i18n('Owner'); ?>: </td><td><?php 
		if($_SESSINFO['uname'] == 'root')
		{
		 echo "<select id='owner_id'>";
		 $ret = GShell("users -asc",$_REQUEST['sessid'],$_REQUEST['shellid']);
		 $users = $ret['outarr'];
		 for($c=0; $c < count($users); $c++)
		  echo "<option value='".$users[$c]['id']."'".($users[$c]['name'] == $Owner ? " selected='selected'>" : ">").$users[$c]['name']."</option>";
		 echo "</select>";
		}
		else
		 echo $Owner; 
		?></td></tr>
	<tr><td><?php echo i18n('Access'); ?>: </td><td><select id='owner_access'><?php
		echo "<option value='4'".($mod[0] == 4 ? " selected='selected'>" : ">").i18n('Read only')."</option>";
		echo "<option value='6'".($mod[0] == 6 ? " selected='selected'>" : ">").i18n('Read and write')."</option>";
		?></select></td></tr>
	<tr><td colspan='2'><br/></td></tr>
	<tr><td><?php echo i18n('Group'); ?>: </td><td><select id='group_id'><?php
		echo "<option value='".$GroupId."' selected='selected'>".$GroupName."</option>";
		$userGroups = _userGroups($_SESSINFO['uid']);
		for($c=0; $c < count($userGroups); $c++)
		{
		 if($userGroups[$c]['id'] != $GroupId)
		  echo "<option value='".$userGroups[$c]['id']."'>".$userGroups[$c]['name']."</option>";
		}
		?></select></td></tr>
	<tr><td><?php echo i18n('Access'); ?>: </td><td><select id='group_access'><?php
		echo "<option value='0'".($mod[1] == 0 ? " selected='selected'>" : ">").i18n('Nobody')."</option>";
		echo "<option value='4'".($mod[1] == 4 ? " selected='selected'>" : ">").i18n('Read only')."</option>";
		echo "<option value='6'".($mod[1] == 6 ? " selected='selected'>" : ">").i18n('Read and write')."</option>";
		?></select></td></tr>
	<tr><td colspan='2'><br/></td></tr>
	<tr><td colspan='2'><?php echo i18n('Other'); ?>: </td></tr>
	<tr><td><?php echo i18n('Access'); ?>: </td><td><select id='other_access'><?php
		echo "<option value='0'".($mod[2] == 0 ? " selected='selected'>" : ">").i18n('Nobody')."</option>";
		echo "<option value='4'".($mod[2] == 4 ? " selected='selected'>" : ">").i18n('Read only')."</option>";
		echo "<option value='6'".($mod[2] == 6 ? " selected='selected'>" : ">").i18n('Read and write')."</option>";
		?></select></td></tr>
	</table>
	<hr/>
	<input type='button' onclick='_submit()' value="<?php echo i18n('Apply'); ?>"/> <input type='button' onclick='_abort()' value="<?php echo i18n('Abort'); ?>"/>
<?php
$form->End();
?>
<script>
function _submit()
{
 var mod = "";
 var xArgs = "";
 mod+= document.getElementById('owner_access').value.toString();
 if(mod == "4")
 {
  if(!confirm("<?php echo i18n("Warning! You're assigning the item owner only allowed to read, if you confirm it will not be able to modify or change its permissions from this window. You can still edit them by running the command 'chmod dynarc' directly from the command line terminal. Do you want to proceed?"); ?>"))
  return;
 }
 mod+= document.getElementById('group_access').value.toString();
 mod+= document.getElementById('other_access').value.toString();
 xArgs+= " -perms "+mod;
 if(document.getElementById('group_id').value)
  xArgs+= " -groupid "+document.getElementById('group_id').value;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 a['modinfo'] = new Array();
	 a['modinfo']['uid'] = document.getElementById('owner_id') ? document.getElementById('owner_id').value : "<?php echo $OwnerId; ?>";
	 a['modinfo']['gid'] = document.getElementById('group_id').value;
	 a['modinfo']['mod'] = mod;
	 var modstr = "";
	 for(var c=0; c < mod.length; c++)
	 {
	  switch(mod.charAt(c))
	  {
	   case "4" : modstr+="r--"; break;
	   case "6" : modstr+="rw-"; break;
	   default :  modstr+="---"; break;
	  }
	 }
	 a['modinfo']['modstr'] = modstr;
	 gframe_close(o,a);
	}
 var cmd = "dynarc edit-item -ap '<?php echo $_AP; ?>' -id '<?php echo $itemInfo['id']; ?>'"+xArgs;
 if(document.getElementById('owner_id'))
  cmd = "dynarc chown -ap '<?php echo $_AP; ?>' -id '<?php echo $itemInfo['id']; ?>' -uid '"+document.getElementById('owner_id').value+"' || "+cmd;
 sh.sendCommand(cmd);
}

function _abort()
{
 gframe_close();
}
</script>
<?php

