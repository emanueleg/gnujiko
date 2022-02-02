<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 02-01-2012
 #PACKAGE: system-config-gui
 #DESCRIPTION: Terminal configuration form
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_ABSOLUTE_URL, $_BASE_PATH;

LoadLanguage("config-terminal");

?>
<table class='item' border='0' cellspacing='0' cellpadding='0'>
  <tr><td valign='top' width='60'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/icons/terminal.png" height='48'/></td>
 <td valign='top'><a href='#' onclick='runGShellConfig()' class='item-title'><?php echo i18n("Command line shell"); ?></a><br/>
 <span class='item-desc'><?php echo i18n("Configure the access at the command line shell for the users."); ?></span>
 <p><?php
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM gnujiko_users WHERE username!='root' AND enableshell=1");
 $db->Read();
 $shellUsers = $db->record[0];
 $db->Close();

 if($shellUsers > 2)
 {
  echo "<img src='".$_ABSOLUTE_URL."share/widgets/config/img/warning_shield_grey.png' style='float:left; margin:2px 2px;'/>";
  echo "<span class='warningtext'>".sprintf(i18n("too many users (%d) have access to shell."),$shellUsers)."</span>";
 }
 else
 {
  echo "<img src='".$_ABSOLUTE_URL."share/widgets/config/img/info.jpg' style='float:left; margin:2px 2px;'/>";
  echo "<span class='infotext'>".sprintf(i18n("%d users have access to shell."),$shellUsers)."</span>";
 }

 ?>
 </td></tr>
</table>
<script>
function runGShellConfig()
{
 var sh = new GShell();
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("gframe -f config.terminal");
}
</script>
<?php

