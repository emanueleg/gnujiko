<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 08-01-2012
 #PACKAGE: system-config-gui
 #DESCRIPTION: Gnujiko User and Groups configuration form
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_ABSOLUTE_URL, $_BASE_PATH;

LoadLanguage("config-usergroups");

$db = new AlpaDatabase();
$db->RunQuery("SELECT COUNT(*) FROM gnujiko_users WHERE username!='root'");
$db->Read();
$users = $db->record[0];

$db->RunQuery("SELECT COUNT(*) FROM gnujiko_groups");
$db->Read();
$groups = $db->record[0];
$db->Close();

?>
<table class='item' border='0' cellspacing='0' cellpadding='0'>
 <tr><td valign='top' width='60'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/icons/usergroups.gif" height='48'/></td>
 <td><a href='#' class='item-title' onclick="runUserManager()"><?php echo i18n("User and Groups"); ?></a><br/>
 <span class='item-desc'><?php echo i18n("Manage system users and groups."); ?></span><br/>
 <p><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/img/info.jpg" style="float:left; margin:2px 2px;"/> 
 <span class='infotext'><?php echo $users; ?> <?php echo i18n("registered users"); ?></span><br/>
 <span class='infotext'><?php echo $groups; ?> <?php echo i18n("groups"); ?></span></p>
 </td></tr>
</table>

<script>
function runUserManager()
{
 var sh = new GShell();
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("gframe -f config.users");
}
</script>
<?php

