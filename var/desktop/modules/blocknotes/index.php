<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-05-2013
 #PACKAGE: blocknotes-module
 #DESCRIPTION: BlockNotes module for Gnujiko Desktop.
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_INTERNAL_LOAD, $_MODULE_INFO;

//-- PRELIMINARY ----------------------------------------------------------------------------------------------------//
if(!$_INTERNAL_LOAD) // this script is loaded into a layer
{
 define("VALID-GNUJIKO",1);
 $_BASE_PATH = "../../../../";
 include_once($_BASE_PATH."include/gshell.php");
 include_once($_BASE_PATH."include/js/gshell.php");
}
//-------------------------------------------------------------------------------------------------------------------//
$_MODULE_INFO['handle'] = $_MODULE_INFO['id']."-handle";
$_MODULE_INFO['front'] = $_MODULE_INFO['id']."-front";
$_MODULE_INFO['back'] = $_MODULE_INFO['id']."-back";

$_MODULE_INFO['plugs'][] = $_MODULE_INFO['id']."-plug1";

$from = time();

include_once($_BASE_PATH."include/i18n.php");
LoadLanguage("blocknotes");

include_once($_BASE_PATH."var/objects/editsearch/index.php");

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/blocknotes/blocknotes.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/blocknotes/blocknotes.js" type="text/javascript"></script>

<!-- FRONT PANEL -->
<div id="<?php echo $_MODULE_INFO['front']; ?>" class="gnujiko-desktop-module-front-panel blocknotesmod-frontpanel" onload="blocknotesmodule_load('<?php echo $_MODULE_INFO['id']; ?>')">
<div class="blocknotesmod-header">
<table width="100%" height='28' cellspacing="0" cellpadding="0" border="0">
<tr><td align='right' valign='middle' width='34'>&nbsp;</td>
	<td align='center' valign='middle' class='blocknotesmod-handle' id="<?php echo $_MODULE_INFO['handle']; ?>">APPUNTI</td>
	<td align='left' valign='middle' width='34'>&nbsp;</td></tr>
</table>
<table width="100%" height='6' cellspacing='0' cellpadding='0' border='0' class="blocknotesmod-refdate">
<tr><td>Annota qui i tuoi appunti</td></tr>
</table>
</div>

<div class="blocknotesmod-container" id="<?php echo $_MODULE_INFO['id'].'-container'; ?>">
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="blocknoteslist" id="blocknoteslist">
<?php
 $imgFolder = $_ABSOLUTE_URL."var/desktop/modules/blocknotes/img/";
 $ret = GShell("dynarc item-list -ap 'blocknotes' --all-cat");
 $list = $ret['outarr']['items'];
 for($c=0; $c < count($list); $c++)
 {
  echo "<tr id='".$list[$c]['id']."'><td valign='middle'><a href='#' onclick='blocknotesmodule_edit(this)'>".$list[$c]['name']."</a></td>";
  echo "<td width='32' align='center' valign='middle'><img src='".$imgFolder."delete.png' onclick='blocknotesmodule_delete(this)'/></td></tr>";
 }
?>
</table>
</div>

<div class="blocknotesmod-footer">
 Cerca: <input type='text' class='blocknoteslist-text' style='width:70%' id="blocknoteslist-search"/> <img src="<?php echo $imgFolder; ?>add.png" class="blocknotesadd" onclick="blocknotesmodule_new()" title="Crea un nuovo appunto"/>
</div>

</div>
<!-- EOF - FRONT PANEL -->

<!-- BACK PANEL -->
<div id="<?php echo $_MODULE_INFO['back']; ?>" class="gnujiko-desktop-module-back-panel blocknotesmod-backpanel" style="display:none">
<table width='100%' cellspacing='0' cellpadding='0' border='0' height='100%'>
<tr><td class='header'><?php echo $_MODULE_INFO['title']; ?></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td height='32'>
	 &nbsp;
	</td></tr>
</table>
</div>

<!-- EOF - BACK PANEL -->

