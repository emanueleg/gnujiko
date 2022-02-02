<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-02-2013
 #PACKAGE: calendar-module
 #DESCRIPTION: Calendar module for Gnujiko Desktop.
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS: gcal
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
LoadLanguage("calendar");

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/calendar/calendar.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/calendar/calendar.js" type="text/javascript"></script>

<!-- FRONT PANEL -->
<div id="<?php echo $_MODULE_INFO['front']; ?>" class="gnujiko-desktop-module-front-panel" onload="calendarmodule_load('<?php echo $_MODULE_INFO['id']; ?>')">
<div class="calmod-header">
<table width="100%" height='28' cellspacing="0" cellpadding="0" border="0">
<tr><td align='right' valign='middle' width='34'><img src="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/calendar/img/prevbtn.png" class="prevbtn" onclick="calendarmodule_prev('<?php echo $_MODULE_INFO['id']; ?>')"/></td>
	<td align='center' valign='middle' class='calmod-handle' id="<?php echo $_MODULE_INFO['handle']; ?>" date="<?php echo date('Y-m-01',$from); ?>"><?php echo i18n("MONTH-".date('n',$from))." ".date('Y',$from); ?></td>
	<td align='left' valign='middle' width='34'><img src="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/calendar/img/nextbtn.png" class="nextbtn" onclick="calendarmodule_next('<?php echo $_MODULE_INFO['id']; ?>')"/></td></tr>
</table>
<table width="100%" height='6' cellspacing='0' cellpadding='0' border='0' class="calmod-days">
<tr><td>lun</td><td>mar</td><td>mer</td><td>gio</td><td>ven</td><td>sab</td><td>dom</td></tr>
</table>
</div>

<table width="100%" cellspacing="0" cellpadding="0" border="0" class="calmod-grid" id="<?php echo $_MODULE_INFO['id']; ?>-grid">
<?php
$from = time();
$ret = GShell("calendar print -month ".date('n',$from),$_REQUEST['sessid'],$_REQUEST['shellid']);
for($r=0; $r < count($ret['outarr']); $r++)
{
 $week = $ret['outarr'][$r];
 echo "<tr>";
 for($c=0; $c < 7; $c++)
 {
  echo "<td";
  if($week['dates'][$c] == date('Y-m-d')) // today
   echo " class='today'";
  else if(date('n',strtotime($week['dates'][$c])) != date('n',$from))
   echo " class='out'";
  echo ">".$week['days'][$c]."</td>";
 }
 echo "</tr>";
}
?>
</table>
</div>
<!-- EOF - FRONT PANEL -->

<!-- BACK PANEL -->
<div id="<?php echo $_MODULE_INFO['back']; ?>" class="gnujiko-desktop-module-back-panel calmod-back" style="display:none">
<table width='100%' cellspacing='0' cellpadding='0' border='0' height='100%'>
<tr><td class='header'><?php echo $_MODULE_INFO['title']; ?></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td height='32'>
	 <div class='plugbar'><i>Output</i>
	  <span class='moduleplug'>ON CHANGE <img src="<?php echo $_BASE_PATH; ?>include/desktop/img/plug.png" class='plug' id="<?php echo $_MODULE_INFO['plugs'][0]; ?>" plugdir="down" plugtype="output" plugname="onchange"/></span>
	 </div>
	</td></tr>
</table>
</div>

<!-- EOF - BACK PANEL -->
<script>
var calendarmodule_i18n = new Array();
calendarmodule_i18n['MONTH-01'] = "<?php echo i18n('MONTH-1'); ?>";
calendarmodule_i18n['MONTH-02'] = "<?php echo i18n('MONTH-2'); ?>";
calendarmodule_i18n['MONTH-03'] = "<?php echo i18n('MONTH-3'); ?>";
calendarmodule_i18n['MONTH-04'] = "<?php echo i18n('MONTH-4'); ?>";
calendarmodule_i18n['MONTH-05'] = "<?php echo i18n('MONTH-5'); ?>";
calendarmodule_i18n['MONTH-06'] = "<?php echo i18n('MONTH-6'); ?>";
calendarmodule_i18n['MONTH-07'] = "<?php echo i18n('MONTH-7'); ?>";
calendarmodule_i18n['MONTH-08'] = "<?php echo i18n('MONTH-8'); ?>";
calendarmodule_i18n['MONTH-09'] = "<?php echo i18n('MONTH-9'); ?>";
calendarmodule_i18n['MONTH-10'] = "<?php echo i18n('MONTH-10'); ?>";
calendarmodule_i18n['MONTH-11'] = "<?php echo i18n('MONTH-11'); ?>";
calendarmodule_i18n['MONTH-12'] = "<?php echo i18n('MONTH-12'); ?>";

</script>

