<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-11-2012
 #PACKAGE: printmodels-config
 #DESCRIPTION: PrintModels configuration form
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_ABSOLUTE_URL, $_BASE_PATH;

LoadLanguage("config-printmodels");

?>
<table class='item' border='0' cellspacing='0' cellpadding='0'>
  <tr><td valign='top' width='60'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/icons/printmodels.png" height='48'/></td>
 <td><a href='#' class='item-title' onclick="runPrintModelsConfig()"><?php echo i18n("Print models editor"); ?></a><br/>
 <span class='item-desc'><?php echo i18n("Customize your own print models for preemptives,invoices,etc..."); ?></span>
 <p> 
 <?php
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_printmodels_items WHERE trash='0'");
 $db->Read();
 $count = $db->record[0];
 $db->Close();

 if(!$count)
 {
  echo "<img src='".$_ABSOLUTE_URL."share/widgets/config/img/warning.png' style='float:left; margin:2px 2px;'/>";
  echo "<span class='warningtext'>".i18n("No print models found.")."</span>";
 }
 else
 {
  echo "<img src='".$_ABSOLUTE_URL."share/widgets/config/img/info.jpg' style='float:left; margin:2px 2px;'/>";
  echo "<span class='infotext'>".sprintf(i18n("%d print models inserted."),$count)."</span>";
 }
 ?>
 </p>
 </td></tr>
</table>

<script>
function runPrintModelsConfig()
{
 /*var sh = new GShell();
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("gframe -f config.paymentmodes");*/
 window.top.location.href = "<?php echo $_ABSOLUTE_URL; ?>PrintModels/index.php";
}
</script>
<?php

