<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 01-01-2012
 #PACKAGE: gnujiko-language-pack
 #DESCRIPTION: Gnujiko Language configuration form
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_ABSOLUTE_URL, $_BASE_PATH;

LoadLanguage("config-language");

$languages = array("en-GB"=>"English", "it-IT"=>"Italian");

?>
<table class='item' border='0' cellspacing='0' cellpadding='0'>
  <tr><td valign='top' width='60'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/icons/language.png" height='48'/></td>
 <td><a href='#' class='item-title' onclick='runLanguageManager()'><?php echo i18n("Language"); ?></a><br/>
 <span class='item-desc'><?php echo i18n("Configure the default language for the whole system and applications."); ?></span>
 <p><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/img/check_OK.gif" style="float:left; margin:2px 2px;"/> 
 <span class='infotext'><?php echo i18n("The default language is"); ?>:</span> <span class='infovalue'><?php echo i18n($languages[$_LANGUAGE]); ?></span></p>
 </td></tr>
</table>

<script>
function runLanguageManager()
{
 var sh = new GShell();
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("gframe -f config.language");
}
</script>
<?php

