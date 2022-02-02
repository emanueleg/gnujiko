<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 02-01-2012
 #PACKAGE: system-config-gui
 #DESCRIPTION: FTP configuration form
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_ABSOLUTE_URL, $_BASE_PATH;

LoadLanguage("config-ftp");

?>
<table class='item' border='0' cellspacing='0' cellpadding='0'>
  <tr><td valign='top' width='60'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/icons/ftp.png" height='48'/></td>
 <td><a href='#' class='item-title' onclick="runFTPConfig()"><?php echo i18n("FTP Access"); ?></a><br/>
 <span class='item-desc'><?php echo i18n("Configures the FTP access on this system."); ?></span>
 <p><img src="<?php echo $_ABSOLUTE_URL.($_FTP_SERVER ? 'share/widgets/config/img/check_OK.gif' : 'share/widgets/config/img/ftp-off.png'); ?>" style="float:left; margin:2px 2px;"/> 
 <span class='infotext'><?php echo i18n("FTP access:"); ?></span> 
 <span class='infovalue'><?php
	if($_FTP_SERVER)
	 echo i18n("Enabled");
	else
	 echo i18n("Disabled");
	?></span></p>
 </td></tr>
</table>

<script>
function runFTPConfig()
{
 var sh = new GShell();
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("gframe -f config.ftp");
}
</script>
<?php

