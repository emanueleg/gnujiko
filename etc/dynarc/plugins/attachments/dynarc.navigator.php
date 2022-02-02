<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 20-11-2012
 #PACKAGE: dynarc-attachments-extension
 #DESCRIPTION: Attachments support for categories and items into archives managed by Dynarc. Plugins for Dynarc Navigator.
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS:
 #TODO:
 
*/

global $_ARCHIVE_INFO;

function dynarc_navigator_plugin_attachments_injectMenu($archiveInfo,$menu)
{
 switch($menu)
 {
  case 'mainmenu' : {
	} break;
  case 'info' : {
	} break;
  case 'buttons' : {
	} break;
  case 'columns' : {
	 $ret = "<li>Allegati <ul class='submenu'>";
	 $ret.= "<li><input type='checkbox' onchange=\"_shColumns(this,'column-attachments-count')\"/>n. allegati</li>";
	 $ret.= "</ul></li>";
	 return $ret;
	} break;
 }
}

function dynarc_navigator_plugin_attachments_injectTH($archiveInfo)
{
 return "<th style='display:none;' id='column-attachments-count' width='1%'><small>Allegati</small></th>";
}

function dynarc_navigator_plugin_attachments_injectRow($archiveInfo, $itemInfo)
{
 return "<td style='display:none;'>".count($itemInfo['attachments'])."</td>";
}

?>
<script>
PLUGINS_FUNCTIONS['attachments'] = {
	injectRow : function(r, itemInfo, archivePrefix)
				{
				 r.getCell('column-attachments-count').innerHTML = itemInfo['attachments'] ? itemInfo['attachments'].length : "0";
				}};
</script>
<?php

