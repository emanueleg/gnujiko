<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 22-02-2013
 #PACKAGE: gnujiko-desktop-base
 #DESCRIPTION: Default Desktop section multi-columns
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_INTERNAL_LOAD, $_SECTION_CONTAINERS;

//-- PRELIMINARY ----------------------------------------------------------------------------------------------------//
if(!$_INTERNAL_LOAD) // this script is loaded into a layer
{
 define("VALID-GNUJIKO",1);
 $_BASE_PATH = "../../../../";
 include_once($_BASE_PATH."include/gshell.php");
 include_once($_BASE_PATH."include/js/gshell.php");
}
//-------------------------------------------------------------------------------------------------------------------//

$_SECTION_CONTAINERS[] = "gjkdsksec-1";
$_SECTION_CONTAINERS[] = "gjkdsksec-2";
$_SECTION_CONTAINERS[] = "gjkdsksec-3";
$_SECTION_CONTAINERS[] = "gjkdsksec-4";

?>
<style type="text/css">
td.gnujiko-desktop-default-section {
 background: #ffffff;
 border: 1px solid #dadada;
}
</style>

<table width='100%' height='100%' cellspacing='20' cellpadding='0' border='0'>
<tr><td valign='top' id='gjkdsksec-1' class="gnujiko-desktop-default-section" width='25%'>&nbsp;</td>
	<td valign='top' id='gjkdsksec-2' class="gnujiko-desktop-default-section" width='25%'>&nbsp;</td>
	<td valign='top' id='gjkdsksec-3' class="gnujiko-desktop-default-section" width='25%'>&nbsp;</td>
	<td valign='top' id='gjkdsksec-4' class="gnujiko-desktop-default-section" width='25%'>&nbsp;</td></tr>
</table>
