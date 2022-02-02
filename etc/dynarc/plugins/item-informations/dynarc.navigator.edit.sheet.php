<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 19-04-2013
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Item informations sheet for dynarc.navigator edit forms
 #VERSION: 2.1beta
 #CHANGELOG: 19-04-2013 : Bug fix in group list with user root.
			 25-01-2012 : Bug fix.
			 21-01-2012 : Language support.
			 18-09-2011 : Aggiunto campo ID sulle informazioni.
 #DEPENDS: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_ARCHIVE_PREFIX, $_ARCHIVE_INFO, $_ITEM_INFO, $_PARENT_INFO, $_PATHWAY;

include_once($_BASE_PATH."include/userfunc.php");
include_once($_BASE_PATH."include/i18n.php");
LoadLanguage("dynarc");

?>
<style type='text/css'>
table.iteminfo td h3 {
	font-family: Arial;
	font-size: 16px;
	color: #339900;
	text-decoration: underline;
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
Navigator.registerPage("item-informations");

function dynarc_edititem_plugin_iteminfo_showPage()
{
  Navigator.showPage("item-informations");
}

PLUGINS_FUNCTIONS['iteminfo'] = {
	save : function(xsArgs, args)
				{
				 if(document.getElementById('alias'))
				 {
				  var alias = document.getElementById('alias').value;
				  args.push("-alias `"+alias+"`");
				 }
				 var mod = "";
				 mod+= document.getElementById('owner_access').value.toString();
				 if(mod == "4")
				 {
				  if(!confirm("<?php echo i18n("Warning! You're assigning the document owner only allowed to read, if you confirm it will not be able to modify or change its permissions from this window. You can still edit them by running the command 'chmod dynarc' directly from the command line terminal. Do you want to proceed?"); ?>"))
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


function dynarc_edititem_plugin_iteminfo_injectTab()
{
 global $_ITEM_INFO;
 return "<span onclick='Navigator.showPage(\"item-informations\")'>Info</span>";
}

function dynarc_edititem_plugin_iteminfo_pageContents()
{
 global $_ABSOLUTE_URL, $_ARCHIVE_INFO, $_ITEM_INFO;

 ?>
 <div id='item-informations' style='display:none;padding:18px;'>
 <table width='100%' border='0' class='iteminfo'>
 <tr><td valign='top' width='60'><img src="<?php echo $_ABSOLUTE_URL; ?>etc/dynarc/plugins/item-informations/img/info.png"/></td>
	 <td valign='top'><h3><?php echo i18n('INFORMATIONS'); ?></h3>
		<span><?php echo i18n('ID:'); ?> </span><span><?php echo $_ITEM_INFO['id']; ?></span><br/>
		<span><?php echo i18n('Creation date:'); ?> </span> <span class='time'><?php echo date('d/m/Y H:i',$_ITEM_INFO['ctime']); ?></span><br/>
		<span><?php echo i18n('Last edit:'); ?> </span> <span class='time'><?php if($_ITEM_INFO['mtime']) echo date('d/m/Y H:i',$_ITEM_INFO['mtime']); ?></span>
		<?php
		if($_ARCHIVE_INFO['type'] == "document")
		{
		 ?>
		 <br/><span><?php echo i18n('Alias name:'); ?> </span> <input type='text' size='10' id='alias' value="<?php echo $_ITEM_INFO['aliasname']; ?>"/></span>
		 <?php
		}
		?></td></tr>
 <tr><td colspan='2'><hr/></td></tr>
 <tr><td valign='top' width='60'><img src="<?php echo $_ABSOLUTE_URL; ?>etc/dynarc/plugins/item-informations/img/perms.png"/></td>
	 <td valign='top'><h3><?php echo i18n('DOCUMENT PERMISSIONS'); ?></h3>
		<table width='330' class='perms' cellspacing='0' cellpadding='0' border='0'>
		<tr><th align='left'><?php echo i18n('OWNER:'); ?></th><th align='right'><small><?php echo $_ITEM_INFO['owner']; ?></small></th></tr>
		<tr><td><?php echo i18n('Access:'); ?></td><td align='right'><select id='owner_access'><?php
			$mod = $_ITEM_INFO['modinfo']['mod'];
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
			$userGroups = _userGroups();
			for($c=0; $c < count($userGroups); $c++)
	 		 echo "<option value='".$userGroups[$c]['id']."'"
				.($userGroups[$c]['id'] == $_ITEM_INFO['modinfo']['gid'] ? " selected='selected'>" : ">")
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
 <tr><td colspan='2'><hr/></td></tr>
 <tr><td valign='top' width='60'><img src="<?php echo $_ABSOLUTE_URL; ?>etc/dynarc/plugins/item-informations/img/publish.png"/></td>
	 <td valign='top'><span style='font-family: Arial;font-size: 16px;color: #339900;text-decoration: underline;'><?php echo i18n('PUBLISH'); ?></span> &nbsp;&nbsp;<span><input type='radio' name='published' id='publish_on' <?php 
		if($_ITEM_INFO['published']) 
		 echo "checked='true'"; ?>
		><?php echo i18n('Yes'); ?></input> <input type='radio' name='published' <?php
		if(!$_ITEM_INFO['published'])
		 echo "checked='true'"; ?>
		><?php echo i18n('No'); ?></input></span><br/><br/><div class='publish_text'><?php echo i18n('Publish the document on a server or compile into an e-book.'); ?></div></td></tr>
 </table>
 </div>
 <?php
}

