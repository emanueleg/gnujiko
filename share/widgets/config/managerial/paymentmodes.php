<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 20-01-2012
 #PACKAGE: paymentmodes-config
 #DESCRIPTION: Payment modes configuration form
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_ABSOLUTE_URL, $_BASE_PATH;

LoadLanguage("config-paymentmodes");

?>
<table class='item' border='0' cellspacing='0' cellpadding='0'>
  <tr><td valign='top' width='60'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/icons/payment.png" height='48'/></td>
 <td><a href='#' class='item-title' onclick="runPaymentModeConfig()"><?php echo i18n("Payment modes"); ?></a><br/>
 <span class='item-desc'><?php echo i18n("Configure the mode of payment that your company offers to customers."); ?></span>
 <p> 
 <?php
 $ret = GShell("paymentmodes list");
 $list = $ret['outarr'];
 if(!count($list))
 {
  echo "<img src='".$_ABSOLUTE_URL."share/widgets/config/img/warning.png' style='float:left; margin:2px 2px;'/>";
  echo "<span class='warningtext'>".i18n("No mode was added.")."</span>";
 }
 else
 {
  echo "<img src='".$_ABSOLUTE_URL."share/widgets/config/img/info.jpg' style='float:left; margin:2px 2px;'/>";
  echo "<span class='infotext'>".sprintf(i18n("%d payment modes inserted."),count($list))."</span>";
 }
 ?>
 </p>
 </td></tr>
</table>

<script>
function runPaymentModeConfig()
{
 var sh = new GShell();
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("gframe -f config.paymentmodes");
}
</script>
<?php

