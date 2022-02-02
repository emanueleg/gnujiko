<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 09-01-2012
 #PACKAGE: system-config-gui
 #DESCRIPTION: Gnujiko Menu Manager form
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_ABSOLUTE_URL, $_BASE_PATH;

LoadLanguage("config-menumanager");

?>
<table class='item' border='0' cellspacing='0' cellpadding='0'>
  <tr><td valign='top' width='60'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/icons/menumanager.png" height='48'/></td>
 <td valign='top'><a href='#' class='item-title' onclick='runMenuManager()'><?php echo i18n("Menu Manager"); ?></a><br/>
 <span class='item-desc'><?php echo i18n("Customize menu links."); ?></span>
 </td></tr>
</table>

<script>
function runMenuManager()
{
 var sh = new GShell();
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("gframe -f config.menu");
}
</script>
<?php


