<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 27-02-2013
 #PACKAGE: schedule-module
 #DESCRIPTION: Schedule module for Gnujiko Desktop.
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS: dynarc-mmr-extension
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
LoadLanguage("schedule");

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/schedule/schedule.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/schedule/schedule.js" type="text/javascript"></script>

<!-- FRONT PANEL -->
<div id="<?php echo $_MODULE_INFO['front']; ?>" class="gnujiko-desktop-module-front-panel schedmod-frontpanel" onload="schedulemodule_load('<?php echo $_MODULE_INFO['id']; ?>')">
<div class="schedmod-header">
<table width="100%" height='28' cellspacing="0" cellpadding="0" border="0">
<tr><td align='right' valign='middle' width='34'><img src="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/schedule/img/prevbtn.png" class="prevbtn" onclick="schedulemodule_prev('<?php echo $_MODULE_INFO['id']; ?>')"/></td>
	<td align='center' valign='middle' class='schedmod-handle' id="<?php echo $_MODULE_INFO['handle']; ?>"><?php echo $_MODULE_INFO['title']; ?></td>
	<td align='left' valign='middle' width='34'><img src="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/schedule/img/nextbtn.png" class="nextbtn" onclick="schedulemodule_next('<?php echo $_MODULE_INFO['id']; ?>')"/></td></tr>
</table>
<table width="100%" height='6' cellspacing='0' cellpadding='0' border='0' class="schedmod-refdate">
<tr><td>Periodo di riferimento</td><td align="right" date="<?php echo date('Y-m-01',$from); ?>" id="<?php echo $_MODULE_INFO['id']; ?>-date"><?php echo i18n("MONTH-".date('n',$from))." ".date('Y',$from); ?></tr>
</table>
</div>

<div class="schedmod-container" id="<?php echo $_MODULE_INFO['id'].'-container'; ?>">
<?php
$ret = GShell("mmr schedule");
$list = $ret['outarr'];
$totIncomes = 0;
for($c=0; $c < count($list); $c++)
{
 $item = $list[$c];
 echo "<div class='schedule-item'>";
 echo "<div class='schedule-item-header'>";
 echo "<span style='color:".(strtotime($item['expire_date']) < time() ? "#b50000" : "#013397")."'>"
	.date('d/m/Y',strtotime($item['expire_date']))."</span>";
 echo "<span style='float:right'>&euro; ".number_format($item['incomes'],2,',','.')."</span>";
 echo "</div>";

 echo "<span class='schedule-smalltext'><i>cliente:</i></span>";
 echo "<div class='schedule-section'><b>".$item['subject_name']."</b></div>";

 echo "<span class='schedule-smalltext'><i>doc. di riferimento:</i></span>";
 echo "<div class='schedule-section'><a href='".$_ABSOLUTE_URL."GCommercialDocs/docinfo.php?id=".$item['doc_id']."' target='GCD-".$item['doc_id']."'>"
	.$item['doc_name']."</a></div>";

 echo "<span class='schedule-smalltext'><i>modalit&agrave; di pagamento:</i></span>";
 echo "<div class='schedule-section'><b>".$item['payment_mode_name']."</b></div>";

 echo "</div>";
 if($item['incomes'])
  $totIncomes+= $item['incomes'];
}
?>
</div>

<div class="schedmod-footer">
 <span class="ndocs">N. doc: <b id="<?php echo $_MODULE_INFO['id'].'-ndocs'; ?>"><?php echo count($list); ?></b></span>
 <span class="totamount">Totale: <b id="<?php echo $_MODULE_INFO['id'].'-totamount'; ?>">&euro; <?php echo number_format($totIncomes,2,',','.'); ?></b></span>
</div>

</div>
<!-- EOF - FRONT PANEL -->

<!-- BACK PANEL -->
<div id="<?php echo $_MODULE_INFO['back']; ?>" class="gnujiko-desktop-module-back-panel schedmod-backpanel" style="display:none">
<table width='100%' cellspacing='0' cellpadding='0' border='0' height='100%'>
<tr><td class='header'><?php echo $_MODULE_INFO['title']; ?></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td height='32'>
	 <div class='plugbar'><i>Input</i>
	  <span class='moduleplug'>SET DATE <img src="<?php echo $_BASE_PATH; ?>include/desktop/img/plug.png" class='plug' id="<?php echo $_MODULE_INFO['plugs'][0]; ?>" plugdir="down" plugname="setdate" plugtype="input"/></span>
	 </div>
	</td></tr>
</table>
</div>

<!-- EOF - BACK PANEL -->
<script>
var schedulemodule_i18n = new Array();
schedulemodule_i18n['MONTH-01'] = "<?php echo i18n('MONTH-1'); ?>";
schedulemodule_i18n['MONTH-02'] = "<?php echo i18n('MONTH-2'); ?>";
schedulemodule_i18n['MONTH-03'] = "<?php echo i18n('MONTH-3'); ?>";
schedulemodule_i18n['MONTH-04'] = "<?php echo i18n('MONTH-4'); ?>";
schedulemodule_i18n['MONTH-05'] = "<?php echo i18n('MONTH-5'); ?>";
schedulemodule_i18n['MONTH-06'] = "<?php echo i18n('MONTH-6'); ?>";
schedulemodule_i18n['MONTH-07'] = "<?php echo i18n('MONTH-7'); ?>";
schedulemodule_i18n['MONTH-08'] = "<?php echo i18n('MONTH-8'); ?>";
schedulemodule_i18n['MONTH-09'] = "<?php echo i18n('MONTH-9'); ?>";
schedulemodule_i18n['MONTH-10'] = "<?php echo i18n('MONTH-10'); ?>";
schedulemodule_i18n['MONTH-11'] = "<?php echo i18n('MONTH-11'); ?>";
schedulemodule_i18n['MONTH-12'] = "<?php echo i18n('MONTH-12'); ?>";

</script>

