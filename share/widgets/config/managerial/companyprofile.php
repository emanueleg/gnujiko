<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 14-11-2012
 #PACKAGE: companyprofile-config
 #DESCRIPTION: Company profile configuration form
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_ABSOLUTE_URL, $_BASE_PATH;

include($_BASE_PATH."include/company-profile.php");

LoadLanguage("config-companyprofile");

?>
<table class='item' border='0' cellspacing='0' cellpadding='0'>
  <tr><td valign='top' width='60'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/icons/company-profile.png" height='48'/></td>
 <td><a href='#' class='item-title' onclick="config_companyProfile()"><?php echo i18n("Company Profile"); ?></a><br/>
 <span class='item-desc'><?php echo i18n("Enter information for your business, custom letterheads, etc."); ?></span>
 <?php
 if(!$_COMPANY_PROFILE['name'])
 {
  ?>
  <p><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/img/warning.png" style="float:left; margin:2px 2px;"/>
  <span class='warningtext'><?php echo i18n("Create a business profile"); ?></span></p>
  <?php
 }
 else
 {
  ?>
  <p><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/img/check_OK.gif" style="float:left; margin:2px 2px;"/>
  <span class='infotext'><?php echo i18n("Profile updated"); ?></span></p>
  <?php
 }
 ?>
 </td></tr>
</table>

<script>
function config_companyProfile()
{
 gframe_hide();
 var sh = new GShell();
 sh.OnOutput = function(){
	 gframe_show();
	}
 sh.sendCommand('gframe -f config.companyprofile')
}
</script>
<?php

