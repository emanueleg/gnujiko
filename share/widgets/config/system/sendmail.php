<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 01-12-2012
 #PACKAGE: sendmail-config
 #DESCRIPTION: SendMail configuration form
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_ABSOLUTE_URL, $_BASE_PATH;

LoadLanguage("config-sendmail");

?>
<table class='item' border='0' cellspacing='0' cellpadding='0'>
  <tr><td valign='top' width='60'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/icons/sendmail.png" height='48'/></td>
 <td><a href='#' class='item-title' onclick="runSendMailConfig()"><?php echo i18n("Email Manager"); ?></a><br/>
 <span class='item-desc'><?php echo i18n("Set parameters and automated services, for sending e-mail."); ?></span>
 <?php
 if(!$_SMTP_SENDMAIL || !$_SMTP_HOST)
 {
  ?>
  <p><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/img/warning.png" style="float:left; margin:2px 2px;"/> 
  <span class='warningtext'><?php echo i18n("Essential parameters missed."); ?></span></p>
  <?php
 }
 else
  echo "<br/><br/>";
 ?>
 </td></tr>
</table>

<script>
function runSendMailConfig()
{
 var sh = new GShell();
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("gframe -f config.sendmail");
}
</script>

<?php

