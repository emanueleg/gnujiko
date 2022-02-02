<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-05-2014
 #PACKAGE: aboutconfig
 #DESCRIPTION: Gnujiko Aboutconfig
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_ABSOLUTE_URL, $_BASE_PATH;

LoadLanguage("config-aboutconfig");

?>
<table class='item' border='0' cellspacing='0' cellpadding='0'>
  <tr><td valign='top' width='60'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/icons/aboutconfig.png" height='48'/></td>
 <td valign='top'><a href='#' class='item-title' onclick='runAboutConfig()'><?php echo i18n("Applications Settings"); ?></a><br/>
 <span class='item-desc'><?php echo i18n("Advanced configuration panel for setting the parameters for the various applications."); ?></span>
 </td></tr>
</table>

<script>
function runAboutConfig()
{
 window.top.location.href = "<?php echo $_ABSOLUTE_URL; ?>aboutconfig/index.php";
}
</script>
<?php


