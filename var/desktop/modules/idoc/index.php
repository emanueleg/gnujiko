<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-05-2013
 #PACKAGE: idoc-module
 #DESCRIPTION: Interactive content module for Gnujiko Desktop.
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

$imgFolder = $_ABSOLUTE_URL."var/desktop/modules/idoc/img/";

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/idoc/idoc.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/idoc/idoc.js" type="text/javascript"></script>

<!-- FRONT PANEL -->
<div id="<?php echo $_MODULE_INFO['front']; ?>" class="gnujiko-desktop-module-front-panel idocmod-frontpanel" onload="idocmodule_load('<?php echo $_MODULE_INFO['id']; ?>')">
<div class="idocmod-header">
<table width="100%" height='28' cellspacing="0" cellpadding="0" border="0">
<tr><td align='right' valign='middle' width='34'>&nbsp;</td>
	<td align='center' valign='middle' class='idocmod-handle' id="<?php echo $_MODULE_INFO['handle']; ?>"><?php echo ($_MODULE_INFO['title'] != "idoc") ? $_MODULE_INFO['title'] : "Contenuto interattivo"; ?></td>
	<td align='left' valign='middle' width='34'>&nbsp;</td></tr>
</table>
<table width="100%" height='6' cellspacing='0' cellpadding='0' border='0' class="idocmod-refdate">
<tr><td>Inserisci un documento o un contenuto interattivo</td></tr>
</table>
</div>

<div class="idocmod-container" id="<?php echo $_MODULE_INFO['id'].'-container'; ?>" modid="<?php echo $_MODULE_INFO['id']; ?>">
<?php echo $_MODULE_INFO['htmlcontents']; ?>
</div>

<div class="idocmod-footer">
 <img src="<?php echo $imgFolder; ?>edit.png" class="idocadd" onclick="idocmodule_edit('<?php echo $_MODULE_INFO['id']; ?>')" title="Modifica"/>
</div>

</div>
<!-- EOF - FRONT PANEL -->

<!-- BACK PANEL -->
<div id="<?php echo $_MODULE_INFO['back']; ?>" class="gnujiko-desktop-module-back-panel idocmod-backpanel" style="display:none">
<table width='100%' cellspacing='0' cellpadding='0' border='0' height='100%'>
<tr><td class='header'><?php echo $_MODULE_INFO['title']; ?></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td height='32'>
	 &nbsp;
	</td></tr>
</table>
</div>
<!-- EOF - BACK PANEL -->
<?php
if($_MODULE_INFO['javascript'])
{
 echo "<script>\n".$_MODULE_INFO['javascript']."\n</script>\n";
}
?>



