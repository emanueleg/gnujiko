<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-05-2013
 #PACKAGE: powershell
 #DESCRIPTION: Official Gnujiko PowerShell
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Gnujiko PowerShell</title>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/powershell/powershell.css" type="text/css" />
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/progressbar/index.php");
include_once($_BASE_PATH."var/objects/gterminal/index.php");

$imgPath = $_ABSOLUTE_URL."share/widgets/powershell/img/";

$ProgressBar = new ProgressBar();

?>
</head><body>
<div class="powershell-widget"><div class="powershell-widget-inner">
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tr><td width="320" height="80">&nbsp;</td>
	<td>&nbsp;</td>
	<td style="padding-right:5px">
	 <div class="powershell-button" title="Mostra la consolle dei messaggi e degli errori" onclick="showPage('logs',this)"><img src="<?php echo $imgPath; ?>logs.png"/></div>
	 <div class="powershell-button" title="Mostra il terminale di comando" onclick="showPage('terminal',this)"><img src="<?php echo $imgPath; ?>terminal.png"/></div>
	 <div class="powershell-button-selected" title="Mostra la lista dei comandi" onclick="showPage('command-list',this)"><img src="<?php echo $imgPath; ?>command-list.png"/></div>
	</td></tr>
</table>

<!-- COMMAND LIST PAGE -->
<div class="powershell-widget-page" id="command-list-page">
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="command-list-table">
<tr><th width="22"><input type='checkbox' checked='true' onclick='selectAllCommands(this)'/></th>
	<th width="32">N.</th>
	<th style="text-align:left;">COMANDO</th>
	<th width="140">STATUS</th></tr>
</table>
<div style="width:640px;height:334px;background:#ffffff;padding:0px;overflow:auto;">
 <table width="100%" cellspacing="0" cellpadding="0" border="0" class="command-list-table" id="command-list-table">

 </table>
</div>

</div>
<!-- EOF - COMMAND LIST PAGE -->

<!-- TERMINAL PAGE -->
<div class="powershell-widget-page" id="terminal-page" style="display:none">
<div id='gshell' class='console'>
 <font color='gray'><b>GShell 2.0</b></font><br/><br/>
 <font color='green'><?php echo i18n('Welcome to GShell - the official Gnujiko shell.'); ?></font><br/><br/>
<?php echo i18n('This interface behaves similar to a unix-shell.'); ?><br/><?php echo i18n('You type commands and the results are shown on this page.'); ?><br/><br/>
</div>
</div>
<!-- EOF - TERMINAL PAGE -->

<!-- LOGS PAGE -->
<div class="powershell-widget-page" id="logs-page" style="display:none">
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="command-list-table">
<tr><th width="32">&nbsp;</th>
	<th width="60">CMD #</th>
	<th style="text-align:left;">MESSAGE</th>
	<th width="120">LOG TIME</th></tr>
</table>
<div style="width:640px;height:334px;background:#ffffff;padding:0px;overflow:auto;">
 <table width="100%" cellspacing="0" cellpadding="0" border="0" class="log-list-table" id="log-list-table">

 </table>
</div>
</div>
<!-- EOF - LOGS PAGE -->

<!-- FOOTER -->
<div class="powershell-widget-footer">
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tr><td width="60" height="40"><img src="<?php echo $imgPath; ?>button-play.png" id="button-play" onclick="runCommands()"/></td>
	<td width="450"><?php $ProgressBar->Paint(); ?></td>
	<td><span class="button-close" onclick="gframe_close()">CHIUDI</span></td></tr>
</table>
</div>
<!-- EOF - FOOTER -->
</div></div>

<script>
var ProgBar = null;
var Terminal = null;

var ACTION = "";
var COMMAND_LIST = new Array();
var COMMAND_IDX = 0;

function bodyOnLoad(extraParams)
{
 ProgBar = new ProgressBar();
 Terminal = new GTerminal(document.getElementById('gshell'));

 Terminal.OnOutput = function(o,a){
	 var tb = document.getElementById('command-list-table');
	 var tb2 = document.getElementById('log-list-table');
	 var imgPath = ABSOLUTE_URL+"share/widgets/powershell/img/";
	 ProgBar.setValue( Math.floor(100/COMMAND_LIST.length) * (COMMAND_IDX+1) );
	 tb.rows[COMMAND_IDX].cells[3].innerHTML = "<img src='"+imgPath+"ok.gif'/"+">";

	 var sysDate = new Date();	 
	 var r = tb2.insertRow(-1);
	 r.insertCell(-1).innerHTML = "<img src='"+imgPath+"message.png'/"+">"; r.cells[0].style.width='32px'; r.cells[0].style.textAlign='center';
	 r.insertCell(-1).innerHTML = COMMAND_IDX+1; r.cells[1].className = "cmd-number";
	 r.insertCell(-1).innerHTML = o;
	 r.insertCell(-1).innerHTML = sysDate.printf("H:i:s:u"); r.cells[3].className = "logtime";

	 if(COMMAND_LIST.length > (COMMAND_IDX+1))
	 {
	  COMMAND_IDX++;
	  if(ACTION == "RUNNING")
	   execCommand(COMMAND_IDX);
	 }
	 else
	 {
	  ProgBar.setValue(100);
	  document.getElementById('button-play').src = ABSOLUTE_URL+"share/widgets/powershell/img/button-play.png";
	  ACTION = "FINISH";
	  alert("Operazione completata! Puoi chiudere questa finestra.");
	 }
	 
	}

 Terminal.OnError = function(code, msg){
	 var tb = document.getElementById('command-list-table');
	 var tb2 = document.getElementById('log-list-table');
	 var imgPath = ABSOLUTE_URL+"share/widgets/powershell/img/";
	 tb.rows[COMMAND_IDX].cells[3].innerHTML = "<img src='"+imgPath+"warning.png'/"+">";

	 var sysDate = new Date();	 
	 var r = tb2.insertRow(-1);
	 r.insertCell(-1).innerHTML = "<img src='"+imgPath+"error.png'/"+">"; r.cells[0].style.width='32px'; r.cells[0].style.textAlign='center';
	 r.insertCell(-1).innerHTML = COMMAND_IDX+1; r.cells[1].className = "cmd-number";
	 r.insertCell(-1).innerHTML = msg;
	 r.insertCell(-1).innerHTML = sysDate.printf("H:i:s:u"); r.cells[3].className = "logtime";
	}

 if(extraParams)
 {
  var tb = document.getElementById('command-list-table');
  var imgPath = ABSOLUTE_URL+"share/widgets/powershell/img/";
  for(var c=0; c < extraParams.length; c++)
  {
   var r = tb.insertRow(-1);
   var command = extraParams[c].replace("<![CDATA[","").replace("]]>","");
   r.insertCell(-1).innerHTML = "<input type='checkbox' checked='true'/"+">"; r.cells[0].style.textAlign='center'; r.cells[0].style.width='22px';
   r.insertCell(-1).innerHTML = c+1; r.cells[1].className = "cmd-number"; r.cells[1].style.width='32px';
   r.insertCell(-1).innerHTML = "<textarea class='cmd-textarea'>"+command+"</textarea>";
   r.insertCell(-1).innerHTML = "&nbsp;"; r.cells[3].style.width='140px'; r.cells[3].style.textAlign='center';
  }
 }

}

function selectAllCommands(cb)
{
 var tb = document.getElementById('command-list-table');
 for(var c=0; c < tb.rows.length; c++)
  tb.rows[c].cells[0].getElementsByTagName('INPUT')[0].checked = cb.checked;
}

function showPage(page,div)
{
 switch(page)
 {
  case 'command-list' : {
	 document.getElementById('command-list-page').style.display = "";
	 document.getElementById('terminal-page').style.display = "none";
	 document.getElementById('logs-page').style.display = "none";
	} break;

  case 'terminal' : {
	 document.getElementById('command-list-page').style.display = "none";
	 document.getElementById('terminal-page').style.display = "";
	 document.getElementById('logs-page').style.display = "none";
	} break;

  case 'logs' : {
	 document.getElementById('command-list-page').style.display = "none";
	 document.getElementById('terminal-page').style.display = "none";
	 document.getElementById('logs-page').style.display = "";
	} break;
 }

 var list = div.parentNode.getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
  list[c].className = (list[c] == div) ? "powershell-button-selected" : "powershell-button";
}

function runCommands()
{
 if(ACTION == "FINISH")
 {
  if(!confirm("Sei sicuro di voler lanciare di nuovo la lista dei comandi?"))
   return;
 }

 if(ACTION == "RUNNING")
 {
  document.getElementById('button-play').src = ABSOLUTE_URL+"share/widgets/powershell/img/button-play.png";
  ACTION = "PAUSE";
  return;
 }

 if(ACTION == "PAUSE")
 {
  document.getElementById('button-play').src = ABSOLUTE_URL+"share/widgets/powershell/img/button-pause.png";
  ACTION = "RUNNING";
  return execCommand(COMMAND_IDX);
 }

 COMMAND_LIST = new Array();

 var tb = document.getElementById('command-list-table');
 for(var c=0; c < tb.rows.length; c++)
 {
  var r = tb.rows[c];
  if(!r.cells[0].getElementsByTagName('INPUT')[0].checked)
   continue;
  COMMAND_LIST.push(r.cells[2].getElementsByTagName('TEXTAREA')[0].value);
 }

 execSelectedCommands();
}

function execSelectedCommands()
{
 COMMAND_IDX = 0;
 ProgBar.setValue(0);
 ACTION = "RUNNING";
 document.getElementById('button-play').src = ABSOLUTE_URL+"share/widgets/powershell/img/button-pause.png";
 execCommand(COMMAND_IDX);
}

function execCommand(cmdIdx)
{
 var tb = document.getElementById('command-list-table');
 var imgPath = ABSOLUTE_URL+"share/widgets/powershell/img/";
 tb.rows[COMMAND_IDX].cells[3].innerHTML = "<img src='"+imgPath+"loading.gif'/"+">";
 Terminal.lineinput.value = COMMAND_LIST[cmdIdx];
 Terminal._process();
}
</script>

</body></html>
<?php

