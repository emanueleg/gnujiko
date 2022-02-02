<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 22-10-2013
 #PACKAGE: apm-gui
 #DESCRIPTION: Settings form for APM
 #VERSION: 2.1beta
 #CHANGELOG: 22-10-2013 : Autenticazione.
			 02-01-2012 : Multi language.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_GNUJIKO_ACCOUNT, $_GNUJIKO_TOKEN;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("apm-gui");

$sessInfo = sessionInfo($_REQUEST['sessid']);
if($sessInfo['uname'] != "root")
{
 $msg = "You must be root";
 ?>
 <script>
 function bodyOnLoad()
 {
  alert("<?php echo $msg; ?>");
  gframe_close();
 }
 </script>
 <?php
 return;
}

//-------------------------------------------------------------------------------------------------------------------//
?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>APM - Settings</title>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/objects/gtabmenu/simple-blue.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/objects/gtabmenu/gtabmenu.js" type="text/javascript"></script>
<style type="text/css">
table.account-table td {
	font-family: arial, sans-serif;
	font-size: 13px;	
}
</style>
<?php
include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/dyntable/dyntable.php");

?></head><body><?php
$form = new GForm(i18n("Settings"), "MB_OK|MB_ABORT", "simpleform", "default", "blue", "480", "370");
$form->Begin($_ABSOLUTE_URL."share/widgets/apm/icons/settings.png");
?>
<div style="background:#cccccf;height:10px;">&nbsp;</div>
<div style="background:#cccccf;height:34px;margin:0px;padding:0px;margin-top:-12px;">
<ul class='simple-blue' id='menu'>
<li class='selected'><span onclick="_showRepositoryPage()"><?php echo i18n("Repository"); ?></span></li>
<!-- <li class='next'><span onclick="_showUpdatesPage()">Aggiornamenti</span></li> -->
<li class='last'><span onclick="_showAuthPage()">Autenticazione</span></li>
</ul>
</div>

<!-- REPOSITORY PAGE -->
<div id="repository-page">
	<div style="height:180px;overflow:auto;">
	<table id="repotb" width="100%" border="0" cellspacing="0" cellpadding="0" class="dyntable">
	<tr><th width="240"><small>URI</small></th><th width="80"><small><?php echo i18n("Version"); ?></small></th><th width="80"><small><?php echo i18n("Section"); ?></small></th></tr>
	<?php
	$ret = GShell("apm repository-list", $_REQUEST['sessid'], $_REQUEST['shellid']);
	$list = $ret['outarr'];
	for($c=0; $c < count($list); $c++)
	 echo "<tr><td>".$list[$c]['url']."</td><td>".$list[$c]['ver']."</td><td>".$list[$c]['section']."</td></tr>";
	?>
	</table>
	</div>

	<div style="background:#cccccf;padding:3px;">
	<input type="button" value="<?php echo i18n('Add'); ?>" onclick="_addSource()"/> <input type="button" value="<?php echo i18n('Edit'); ?>" onclick="_editSelectedSource()"/> <input type="button" value="<?php echo i18n('Delete'); ?>" onclick="_deleteSelectedSource()"/>
	</div>
</div>

<!-- REPOSITORY PAGE -->
<div id="auth-page" style="display:none">
	<div style="height:180px;overflow:auto;font-family:arial,times;font-size:13px">
	 <h3>Parametri di autenticazione per l&lsquo;accesso ai canali</h3>
	 <br/><br/>
	 <table border="0" class="account-table" align="center">
	  <tr><td align='right'><b>Account:</b></td><td><input type='text' style='width:260px' id='account' value="<?php echo $_GNUJIKO_ACCOUNT; ?>"/></td></tr>
	  <tr><td align='right'><b>Token:</b></td><td><input type='text' style='width:260px' id='token' value="<?php echo $_GNUJIKO_TOKEN; ?>"/></td></tr>
	 </table>
	</div>
</div>
<?php
$form->End();
?>

<script>
var TabMenu = new GTabMenu(document.getElementById('menu'));
var tb = new DynTable(document.getElementById('repotb'),{selectable:true});

function widget_submit()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(){gframe_close("done!",true);}
 sh.sendSudoCommand("apm edit-account -name `"+document.getElementById("account").value+"` -token `"+document.getElementById("token").value+"`");
 return false;
}

function _addSource()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var r = tb.insertRow(-1);
	 r.cells[1].innerHTML = a['url'];
	 r.cells[2].innerHTML = a['ver'];
	 r.cells[3].innerHTML = a['section'];
	}
 sh.sendCommand("gframe -f apm.addsource --fullspace");
}

function _editSelectedSource()
{
 var sel = tb.getSelectedRows();
 if(!sel.length)
  return alert("<?php echo i18n('You must select the repository to be modified'); ?>");
 if(sel.length > 1)
  return alert("<?php echo i18n('You can change a single repository at a time'); ?>");
 
 var uri = sel[0].cells[1].innerHTML;
 var ver = sel[0].cells[2].innerHTML;
 var sec = sel[0].cells[3].innerHTML;
 
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 sel[0].cells[1].innerHTML = a['url'];
	 sel[0].cells[2].innerHTML = a['ver'];
	 sel[0].cells[3].innerHTML = a['section'];
	}
 sh.sendCommand("gframe -f apm.editsource -params `url="+uri+"&ver="+ver+"&sec="+sec+"` --fullspace");
}

function _deleteSelectedSource()
{
 var sel = tb.getSelectedRows();
 if(!sel.length)
  return alert("<?php echo i18n('You must select at least one repository'); ?>");
 if(!confirm("<?php echo i18n('Are you sure you want to delete the selected repository?'); ?>"))
  return;
 var q = "";
 for(var c=0; c < sel.length; c++)
  q+= " && apm delete-repository -url `"+sel[c].cells[1].innerHTML+"` -ver `"+sel[c].cells[2].innerHTML+"` -sec `"+sel[c].cells[3].innerHTML+"`";
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 for(var c=0; c < sel.length; c++)
	  tb.deleteRow(sel[c].rowIndex);
	}
 sh.sendCommand(q.substr(4));
}

function _showRepositoryPage()
{
 TabMenu.select(0);
 document.getElementById("auth-page").style.display = "none";
 document.getElementById("repository-page").style.display = "";
}

function _showAuthPage()
{
 TabMenu.select(1);
 document.getElementById("repository-page").style.display = "none";
 document.getElementById("auth-page").style.display = "";
}
</script>
</body></html>
<?php

