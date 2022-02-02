<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-12-2013
 #PACKAGE: system-config-gui
 #DESCRIPTION: System Backup
 #VERSION: 2.1beta
 #CHANGELOG: 11-12-2013 : Aggiunto backup file di configurazione.
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");
//-------------------------------------------------------------------------------------------------------------------//
?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>System Backup</title>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/system/system-backup.css" type="text/css" />
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
</head><body>

<div class="sync-form-outer"><div class="sync-form">
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tr><td valign="top">
	<!-- ESPORTAZIONE DATI -->
	<h3 class="sync-title">Backup dati</h3>
	<span class="sync-smallgray"><i>Seleziona gli archivi e/o altri dati da esportare</i></span>
	<div class="sync-archivelist" id="option-list">	
	<input type='checkbox' checked='true' id='backup-database'/> DATABASE<br/>
	<input type='checkbox' checked='true' id='backup-config'/> FILE DI CONFIGURAZIONE<br/>
	<input type='checkbox' checked='true' id='backup-homedir'/> CARTELLA HOME UTENTI<br/>
	<input type='checkbox' checked='true' id='backup-share'/> IMMAGINI E DOCUMENTI CONDIVISI<br/>
	</div>
	<a href='#' onclick="submit()" class="sync-btnblue" id="button-submit">Procedi</a>
	<a href='#' onclick="abort()" class="sync-btngray" id="button-abort" style="margin-left:10px">Annulla</a>
	<span id="pleasewait" style="display:none"><i>Attendere prego...</span>
	<a href='#' class="sync-btnblue" id="button-close" style="display:none">Chiudi</a>
	</td><td valign="top" class="sync-separator">&nbsp;</td><td valign="top" width="270">
	<!-- DESTINAZIONE -->
	<h3 class="sync-title">Destinazione</h3>
	<div class="sync-devicelist">
	<table width='100%' cellspacing='0' cellpadding='0' border='0'>
	<tr><td valign='middle' width='32'><input type='radio' name='device' checked='true'/></td>
		<td valign='middle' width='70'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/system/img/pendrive.png"/></td>
		<td><span class="sync-spanblue">PenDrive</span><br/>
			<span class="sync-smallgray">Esporta su una chiavetta dati oppure direttamente sul tuo computer.</span></td></tr>
	</table>
	</div>
	</td></tr>
</table>
</div></div>
<script>
function submit()
{
 var q = "";
 var list = document.getElementById('option-list').getElementsByTagName('INPUT');
 for(var c=0; c < list.length; c++)
 {
  if(list[c].checked)
  {
   switch(list[c].id)
   {
    case 'backup-database' : q+= " -db"; break;
    case 'backup-config' : q+= " -config"; break;
    case 'backup-homedir' : q+= " -home"; break;
    case 'backup-share' : q+= " -share"; break;
   }
  }
 }

 var sh = new GShell();
 sh.OnPreOutput = function(){};
 sh.OnFinish = function(o,a){
	 if(a['filename'])
	  document.location.href = "<?php echo $_ABSOLUTE_URL; ?>getfile.php?sessid=<?php echo $_REQUEST['sessid']; ?>&file="+encodeURIComponent(a['filename']);
	 document.getElementById('pleasewait').style.display = "none";
	 document.getElementById('button-close').style.display = "";
	 document.getElementById('button-close').onclick = function(){gframe_close(o,a);}
	}
 sh.sendSudoCommand("system backup"+q);

 document.getElementById('button-submit').style.display = "none";
 document.getElementById('button-abort').style.display = "none";
 document.getElementById('pleasewait').style.display = "";
}

function abort()
{
 gframe_close();
}
</script>
</body></html>
<?php

