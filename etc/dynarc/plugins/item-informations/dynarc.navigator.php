<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-01-2012
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Item informations Plugin for Dynarc Navigator
 #VERSION: 1.0beta
 #CHANGELOG: 21-01-2012 : Language support.
			 03-09-2011 : Incluso alias name
 #DEPENDS:
 #TODO:
 
*/

global $_ARCHIVE_INFO;
include_once($_BASE_PATH."include/i18n.php");
LoadLanguage("dynarc");

function dynarc_navigator_plugin_iteminfo_injectMenu($archiveInfo,$menu)
{
 switch($menu)
 {
  case 'mainmenu' : {
	} break;
  case 'info' : {
	 $ret = "<li><input type='checkbox' checked='true' onchange=\"_shColumns(this,'column-perms')\"/>".i18n('Permissions')."</li>";
	 $ret.= "<li><input type='checkbox' onchange=\"_shColumns(this,'column-code')\"/>".i18n('Code')."</li>"; 
	 $ret.= "<li><input type='checkbox' onchange=\"_shColumns(this,'column-alias')\"/>".i18n('Alias name')."</li>";
	 return $ret;
	} break;
  case 'buttons' : {
	} break;
  case 'columns' : {
	 
	} break;
 }
}

function dynarc_navigator_plugin_iteminfo_injectTH($archiveInfo)
{
 $ret = "<th id='column-perms' width='80'><small>".i18n('Permissions')."</small></th>";
 $ret.= "<th id='column-code' width='80' style='display:none;'><small>".i18n('Code')."</small></th>";
 $ret.= "<th id='column-alias' width='120' style='display:none;'><small>".i18n('Alias')."</small></th>";
 return $ret;
}

function dynarc_navigator_plugin_iteminfo_injectRow($archiveInfo, $itemInfo)
{
 $ret = "<td><tt>";
 $mod = $itemInfo['modinfo']['mod'];
 for($c=0; $c < strlen($mod); $c++)
   $ret.= ($mod[$c]&4 ? 'r' : '-').($mod[$c]&2 ? 'w' : '-').($mod[$c]&1 ? 'x' : '-');
 $ret.= "</tt></td>";
 $ret.= "<td style='display:none;'>".($itemInfo['code_str'] ? $itemInfo['code_str'] : "&nbsp;")."</td>";
 $ret.= "<td style='display:none;'><tt>".($itemInfo['aliasname'] ? $itemInfo['aliasname'] : "&nbsp;")."</tt></td>";
 return $ret;
}

?>
<script>
PLUGINS_FUNCTIONS['iteminfo'] = {
	injectRow : function(r, itemInfo, archivePrefix)
				{
				 var mod = itemInfo['modinfo']['mod']; var s = "";
				 for(var c=0; c < mod.length; c++)
				  s+= (parseInt(mod.charAt(c))&4 ? "r" : "-")+(parseInt(mod.charAt(c))&2 ? "w" : "-")+(parseInt(mod.charAt(c))&1 ? "x" : "-");
				 r.getCell('column-perms').innerHTML = "<tt>"+s+"</tt>";
				 r.getCell('column-code').innerHTML = itemInfo['code_str'] ? itemInfo['code_str'] : "&nbsp;";
				 r.getCell('column-alias').innerHTML = itemInfo['aliasname'] ? itemInfo['aliasname'] : "&nbsp;";
				}};

</script>
<?php

