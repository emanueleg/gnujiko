<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 12-01-2013
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Item informations sheet for dynarc.navigator edit forms
 #VERSION: 2.1beta
 #CHANGELOG: 12-01-2013 : Bug fixin groups.
			 21-01-2012 : Language support.
 #DEPENDS: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_ARCHIVE_PREFIX, $_ARCHIVE_INFO, $_CAT_INFO, $_PARENT_INFO, $_PATHWAY;

include_once($_BASE_PATH."include/userfunc.php");
include_once($_BASE_PATH."include/i18n.php");
LoadLanguage("dynarc");

?>
<style type='text/css'>
table.iteminfo {background:transparent;}
table.iteminfo td h3 {
	font-family: Arial;
	font-size: 16px;
	color: #339900;
	text-decoration: underline;
}

table.iteminfo td {
	background: transparent;
}

table.iteminfo td span {
	font-family: Arial;
	font-size: 14px;
	font-weight: bold;
	color: #333333;
}

table.iteminfo td span.time {
	font-family: Arial;
	font-size: 12px;
	font-weight: bold;
	color: #013397;
}

table.iteminfo td hr {
	height: 1px;
	border: 0px;
	background: #cccccf;
	width: 100%;
}

table.perms th {
	background: #cccccf;
	height:29px;
	font-family: Arial;
	font-size: 12px;
	font-weight: bold;
	color: #ffffff;
	border-bottom: 1px solid #ffffff;
	padding-left: 4px;
	padding-right: 2px;
}

table.perms th small {
	font-size: 12px;
	color: #000000;
}

table.perms td {
	background: #eeeeee;
	height: 29px;
	font-family: Arial;
	font-size: 12px;
	font-weight: bold;
	color: #000000;
	padding-left: 4px;
	padding-right: 2px;
}

table.perms tr.separator td {
	height: 7px;
	background: #ffffff;
}

table.perms tr.separator td hr {
	margin: 0px;
	height: 1px;
	border: 0px;
	background: #ffffff;
}
div.publish_text {
	font-family: Arial;
	font-size: 10px;
	color: #000000;
	font-weight: normal;
}
</style>

<script>
Navigator.registerPage("cat-informations");

function dynarc_editcat_plugin_catinfo_showPage()
{
  Navigator.showPage("cat-informations");
}

PLUGINS_FUNCTIONS['catinfo'] = {
	save : function(xsArgs, args)
				{
				 var mod = "";
				 mod+= document.getElementById('owner_access').value.toString();
				 if(mod == "4")
				 {
				  if(!confirm("<?php echo i18n("Warning! You're assigning the folder owner only allowed to read, if you confirm it will not be able to modify or change its permissions from this window. You can still edit them by running the command 'chmod dynarc' directly from the command line terminal. Do you want to proceed?"); ?>"))
					return;
				 }
				 mod+= document.getElementById('group_access').value.toString();
				 mod+= document.getElementById('other_access').value.toString();
				 args.push("-perms "+mod);
				 if(document.getElementById('group_id').value)
				  args.push("-groupid "+document.getElementById('group_id').value);
				 if(document.getElementById('publish_on').checked)
				  args.push("--publish")
				 else
				  args.push("--unpublish");
				}};

</script>
<?php


function dynarc_editcat_plugin_catinfo_injectTab()
{
 global $_CAT_INFO;
 return "<span onclick='Navigator.showPage(\"cat-informations\")'>Info</span>";
}

function dynarc_editcat_plugin_catinfo_pageContents()
{
 global $_ABSOLUTE_URL, $_ARCHIVE_INFO, $_CAT_INFO;

 $sessInfo = sessionInfo($_REQUEST['sessid']);

 ?>
 <div id='cat-informations' style='display:none;padding:18px;'>
 <table width='100%' border='0' class='iteminfo'>
 <tr><td valign='top' width='60' style='background:transparent;'><img src="<?php echo $_ABSOLUTE_URL; ?>etc/dynarc/plugins/cat-informations/img/info.png"/></td>
	 <td valign='top' width='200' style='background:transparent;'><h3><?php echo i18n('INFORMATIONS'); ?></h3><span><?php echo i18n('Creation date:'); ?> </span> <span class='time'><?php echo date('d/m/Y H:i',$_CAT_INFO['ctime']); ?></span><br/><span><?php echo i18n('Last edit:'); ?> </span> <span class='time'><?php if($_CAT_INFO['mtime']) echo "<span class='time'>".date('d/m/Y H:i',$_CAT_INFO['mtime']); ?></span></td>
	 <td valign='top' width='60' style='background:transparent;'><img src="<?php echo $_ABSOLUTE_URL; ?>etc/dynarc/plugins/cat-informations/img/publish.png"/></td>
	 <td valign='top' style='background:transparent;'><span style='font-family: Arial;font-size: 16px;color: #339900;text-decoration: underline;'><?php echo i18n('PUBLISH'); ?></span> &nbsp;&nbsp;<span><input type='radio' name='published' id='publish_on' <?php 
		if($_CAT_INFO['published']) 
		 echo "checked='true'"; ?>
		><?php echo i18n('Yes'); ?></input> <input type='radio' name='published' <?php
		if(!$_CAT_INFO['published'])
		 echo "checked='true'"; ?>
		><?php echo i18n('No'); ?></input></span><br/><br/><div class='publish_text'><?php echo i18n('Publish the folder on a server or compile into an e-book.'); ?></div></td></tr></table>
 <table width='100%' border='0' class='iteminfo'>
 <tr><td colspan='2' style='background:transparent;'><hr/></td></tr>
 <tr><td valign='top' width='170' align='right' style='background:transparent;padding-right:20px;'><img src="<?php echo $_ABSOLUTE_URL; ?>etc/dynarc/plugins/cat-informations/img/perms.png"/></td>
	 <td valign='top' style='background:transparent;'><h3><?php echo i18n('FOLDER PERMISSIONS'); ?></h3>
		<table width='330' class='perms' cellspacing='0' cellpadding='0' border='0'>
		<tr><th align='left'><?php echo i18n('OWNER:'); ?></th><th align='right'><small><?php echo $_CAT_INFO['owner']; ?></small></th></tr>
		<tr><td><?php echo i18n('Access:'); ?></td><td align='right'><select id='owner_access'><?php
			$mod = $_CAT_INFO['modinfo']['mod'];
			echo "<option value='4'".($mod[0] == 4 ? " selected='selected'>" : ">").i18n('Read only')."</option>";
			echo "<option value='6'".($mod[0] == 6 ? " selected='selected'>" : ">").i18n('Read and write')."</option>";
			?></select></td></tr>
		<tr class='separator'><td colspan='2'><hr/></td></tr>
		<tr><th align='left'><?php echo i18n('GROUP:'); ?></th><th align='right'><select id='group_id'><?php
			$db = new AlpaDatabase();
			$db->RunQuery("SELECT name FROM gnujiko_groups WHERE id='".$_SESSION['GID']."'");
			$db->Read();
			echo "<option value='".$_SESSION['GID']."'>".$db->record['name']."</option>";
			$db->Close();
			if($sessInfo['uname'] == "root")
			{
			 $userGroups = array();
			 $db = new AlpaDatabase();
			 $db->RunQuery("SELECT id,name FROM gnujiko_groups WHERE 1 ORDER BY name ASC");
			 while($db->Read())
			 {
			  $userGroups[] = array('id'=>$db->record['id'], 'name'=>$db->record['name']);
			 }
			 $db->Close();
			}
			else
			 $userGroups = _userGroups();
			
			for($c=0; $c < count($userGroups); $c++)
	 		 echo "<option value='".$userGroups[$c]['id']."'"
				.($userGroups[$c]['id'] == $_CAT_INFO['modinfo']['gid'] ? " selected='selected'>" : ">")
				.$userGroups[$c]['name']."</option>";
			?></select></td></tr>
		<tr><td><?php echo i18n('Access:'); ?></td><td align='right'><select id='group_access'><?php
			echo "<option value='0'".($mod[1] == 0 ? " selected='selected'>" : ">").i18n('Nobody')."</option>";
			echo "<option value='4'".($mod[1] == 4 ? " selected='selected'>" : ">").i18n('Read only')."</option>";
			echo "<option value='6'".($mod[1] == 6 ? " selected='selected'>" : ">").i18n('Read and write')."</option>";
			?></select></td></tr>
		<tr class='separator'><td colspan='2'><hr/></td></tr>
		<tr><th align='left' colspan='2'><?php echo i18n('ALL OTHER:'); ?></th></tr>
		<tr><td><?php echo i18n('Access:'); ?></td><td align='right'><select id='other_access'><?php
			echo "<option value='0'".($mod[2] == 0 ? " selected='selected'>" : ">").i18n('Nobody')."</option>";
			echo "<option value='4'".($mod[2] == 4 ? " selected='selected'>" : ">").i18n('Read only')."</option>";
			echo "<option value='6'".($mod[2] == 6 ? " selected='selected'>" : ">").i18n('Read and write')."</option>";
			?></select></td></tr>
		</table>
	 </td></tr>
 </table>
 </div>
 <?php
}

