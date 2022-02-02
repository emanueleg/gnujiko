<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2011 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 10-04-2011
 #PACKAGE: system-config-gui
 #DESCRIPTION: Gnujiko System Backup configuration form
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_ABSOLUTE_URL, $_BASE_PATH;

LoadLanguage("config-systembackup");

?>
<style type="text/css">
a.sync-btn {
 display: block;
 float: left;
 height: 21px;
 background: #4b98de;
 border: 1px solid #3b87d1;
 border-radius: 2px;
 font-family: Arial, sans-serif;
 font-size: 13px;
 color: #ffffff;
 line-height: 21px;
 padding-left: 6px;
 padding-right: 6px;
 margin-left: 3px;
 margin-right: 3px;
 font-weight: bold;
 text-decoration: none;
}

a.sync-btn img {border:0px;}

a.sync-btn img.left {
 float: left;
 margin-right: 10px;
}

a.sync-btn img.right {
 float: right;
 margin-left: 10px;
}

</style>

<table class='item' border='0' cellspacing='0' cellpadding='0'>
  <tr><td valign='top' width='60'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/icons/backup.png" height='48'/></td>
 <td><a href='#' class='item-title'><?php echo i18n("System backup"); ?></a><br/>
 <span class='item-desc'><?php echo i18n("Tools for backup and restore system data"); ?></span>
 <p>
 <a class='sync-btn' href='#' onclick="systemRestore()"><img class="left" src="<?php echo $_BASE_PATH; ?>share/widgets/config/img/sync-import-from-file.png"/>Restore</a>
 <a class='sync-btn' href='#' onclick='systemBackup()'><img class="right" src="<?php echo $_BASE_PATH; ?>share/widgets/config/img/sync-export-to-file.png"/>Backup</a>
 </p>
 </td></tr>
</table>

<script>
function systemRestore()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a)
	 {
	  gframe_show();
	  return;
	 }

	 if(a['files'] && a['files'].length)
	 {
	  var sh2 = new GShell();
	  sh2.OnPreOutput = function(){};
	  sh2.OnFinish = function(o,a){
		 alert("Operazione completata!");
		 gframe_close();
		}
	  sh2.sendSudoCommand("system restore -all -f `"+a['files'][0]['fullname']+"`");
	 }
	}
 sh.sendCommand("gframe -f fileupload");
}

function systemBackup()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!o || !a)
	  gframe_show();
	 else
	  gframe_close();
	}
 sh.sendCommand("gframe -f system-backup");
 gframe_hide();
}
</script>
<?php

