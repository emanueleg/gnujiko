<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 31-01-2014
 #PACKAGE: powershell
 #DESCRIPTION: Run a list of customized shell commands from a table.
 #VERSION: 2.1beta
 #CHANGELOG: 24-12-2013 : Bug fix generali.
 #DEPENDS: 
 #TODO: 31-01-2014 : Aggiunto ID nelle tipologie di campi.
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_USERS_HOMES, $_PARSER_INFO;
$_BASE_PATH = "../../../";
$_PARSER_INFO = null;

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Run shell commands from table</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/powershell/runfromtable.css" type="text/css" />
</head><body>

<div class="excel-widget">
 <div class='excel-header'>
  <span class='excel-title'>Lancia comandi GShell da una tabella</span>
  <a href='#' onclick='gframe_close()' class='button-close'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/powershell/img/close.png"/></a>
 </div>
 <table width="775" cellspacing="0" cellpadding="0" border="0" style="margin-left:15px;margin-top:15px">
 <tr><td><span class='bluetitle'>Seleziona le colonne che desideri utilizzare</span></td>
	 <td width='280' style="font-size:12px"><input type='checkbox' checked='true' onclick='useFirstRowAsHeader(this)' id='userfirstrowasheader'/> Escludi la prima riga</td>
	 <td align='center' width='90' style="font-size:10px">seleziona<br/><a href='#' onclick='selectAllColumns()'>tutte</a> | <a href='#' onclick='unselectAllColumns()'>nessuna</a></td></tr>
 </table>

 <!-- EXCEL TABLE -->
 <div class="excel-table-container">
 <table class="excel-table" cellspacing="0" cellpadding="0" border="0" id="excel-table">
 <?php
 echo "<tr>";
 for($c=0; $c < count($fields); $c++)
  echo "<th".($c==0 ? " style='border-left: 1px solid #0197fd;'" : "")."><input type='checkbox' checked='true' onclick='selectColumn(".$c.",this)'/>".$fields[$c]['letter']."</th>";
 echo "</tr>";

 echo "<tr>";
 for($c=0; $c < count($fields); $c++)
  echo "<td class='head'".($c==0 ? " style='border-left: 1px solid #dadada'" : "").">".$fields[$c]['value']."</td>";
 echo "</tr>";

 ?>
 </table>
 </div>
 <!-- EOF - EXCEL TABLE -->

 <div class="command-line"><b>Comando:</b> <input type='text' id='gshell-command' style='width:600px'/> <span class="cmdpreview-button" title="Mostra un'anteprima del comando utilizzando la prima riga" onclick="commandPreview()">Anteprima</span></div>

 <div class="excel-keys-container">
 <table width="730" cellspacing="0" cellpadding="0" border="0" class="excel-keys-table" id="excel-keys-table" style="margin-left:15px;margin-top:15px">
 <tr><th width='80'>COLONNA</th>
	 <th width='160' style="text-align:left">FORMATO</th>
	 <th style="text-align:left;padding-left:10px">CHIAVE</th>
 </tr>

 </table>
 </div>


 <div class="excel-footer">
  <a href='#' onclick='gframe_close()' class='graybutton' style='float:left;'>Annulla</a>
  <a href='#' onclick='submit()' class='bluebutton' style='float:right;'>Procedi &raquo;</a>
 </div>

</div>

<script>
var ARCHIVE_PREFIX = "<?php echo $_REQUEST['ap']; ?>";
var CAT_ID = "<?php echo $_REQUEST['cat']; ?>";
var CAT_TAG = "<?php echo $_REQUEST['ct']; ?>";

function bodyOnLoad()
{

}

function gframe_cachecontentsload(contents)
{
 var div = document.createElement('DIV');
 div.innerHTML = contents;
 div.style.display = "none";
 div.style.position = "absolute";
 div.style.top = 0;
 div.style.left = 0;
 document.body.appendChild(div);

 var letters = new Array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
"AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ",
"BA","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP","BQ","BR","BS","BT","BU","BV","BW","BX","BY","BZ");

 var tb = div.getElementsByTagName('TABLE')[0];
 var xtb = document.getElementById('excel-table');
 var xktb = document.getElementById('excel-keys-table');

 var str = "";
 var str2 = "";
 var idx = 0;

 var excludeFirstColumn = document.getElementById('userfirstrowasheader').checked ? 1 : 0;

 var formatSelectOptions = "";
 var formats = {"text":"Testo","date":"Data","currency":"Valuta","percentage":"Percentuale","number":"Numero","id":"ID"};
 for(k in formats)
  formatSelectOptions+="<option value='"+k+"'>"+formats[k]+"</option>";

 for(var c=excludeFirstColumn; c < tb.rows[0].cells.length; c++)
 {
  str+= "<th"+(idx==0 ? " style='border-left: 1px solid #0197fd;'" : "")+"><input type='checkbox' checked='true' onclick='selectColumn("+idx+",this)'/"+">"+letters[idx]+"</th>";
  str2+= "<td class='head'"+(idx==0 ? " style='border-left: 1px solid #dadada'" : "")+">"+tb.rows[0].cells[c].innerHTML+"</td>";

  var r = xktb.insertRow(-1);
  r.setAttribute("fieldletter",letters[idx]);
  r.insertCell(-1).innerHTML = "<div class='column'>"+letters[idx]+"</div>";
  if(tb.rows[0].cells[c].getAttribute('format'))
  {
   var tmp = "";
   for(k in formats)
	tmp+= "<option value='"+k+"'"+(k == tb.rows[0].cells[c].getAttribute('format') ? " selected='selected'>" : ">")+formats[k]+"</option>";
   r.insertCell(-1).innerHTML = "<select>"+tmp+"</select>";
  }
  else
   r.insertCell(-1).innerHTML = "<select>"+formatSelectOptions+"</select>";
  r.insertCell(-1).innerHTML = "&nbsp;";

  idx++;
 }
 xtb.rows[0].innerHTML = str;
 xtb.rows[1].innerHTML = str2;


 /* IMPORT ELEMENTS */
 for(var c=1; c < tb.rows.length; c++)
 {
  var r = xtb.insertRow(-1);
  for(var i=excludeFirstColumn; i < tb.rows[c].cells.length; i++)
  {
   var cell = r.insertCell(-1);
   var value = "";
   if(tb.rows[c].cells[i].getElementsByTagName('A').length)
	value = tb.rows[c].cells[i].getElementsByTagName('A')[0].innerHTML;
   else if(tb.rows[c].cells[i].getElementsByTagName('SPAN').length)
	value = tb.rows[c].cells[i].getElementsByTagName('SPAN')[0].innerHTML;
   else
	value = tb.rows[c].cells[i].innerHTML;

   cell.innerHTML = value ? value : "&nbsp;";
  }

 }

}

function submit()
{
 var ufrah = document.getElementById('userfirstrowasheader').checked ? 0 : 1;
 var tb = document.getElementById('excel-table');
 var qry = "";

 var commands = getCommands();
 if(!commands.length)
  return;

 for(var c=0; c < commands.length; c++)
  qry+= " -xpn "+c+" -xpv <![CDATA["+commands[c]+"]]>";

 var sh = new GShell();

 sh.sendCommand("gframe -f powershell -params `autoload=true`"+qry);
}

function useFirstRowAsHeader(cb)
{
 var tb = document.getElementById('excel-table');
 if(tb.rows.length < 2)
  return;
 var r = tb.rows[1];
 for(var c=0; c < r.cells.length; c++)
  r.cells[c].className = cb.checked ? "head" : "";
}

function selectAllColumns()
{
 var tb = document.getElementById('excel-table');
 for(var c=0; c < tb.rows[0].cells.length; c++)
 {
  var cb = tb.rows[0].cells[c].getElementsByTagName('INPUT')[0];
  cb.checked = true;
  selectColumn(c,cb);
 }
}

function unselectAllColumns()
{
 var tb = document.getElementById('excel-table');
 for(var c=0; c < tb.rows[0].cells.length; c++)
 {
  var cb = tb.rows[0].cells[c].getElementsByTagName('INPUT')[0];
  cb.checked = false;
  selectColumn(c,cb);
 }
}

function selectColumn(colIdx,cb)
{
 var tb = document.getElementById('excel-table');
 tb.rows[1].cells[colIdx].className = cb.checked ? "head" : "head-disabled";
 for(var c=2; c < tb.rows.length; c++)
  tb.rows[c].cells[colIdx].className = cb.checked ? "" : "disabled";
}

function commandPreview()
{
 var command = document.getElementById("gshell-command").value;

 if(!command)
  return alert("Devi inserire un comando valido");

 var xtb = document.getElementById('excel-table');
 var xktb = document.getElementById('excel-keys-table');
 var tmpDate = new Date();

 var excludeFirstColumn = document.getElementById('userfirstrowasheader').checked ? 2 : 1;

 var r = xtb.rows[excludeFirstColumn];

 for(var c=1; c < xktb.rows.length; c++)
 {
  var kr = xktb.rows[c];
  var fL = kr.getAttribute('fieldletter');
  var regex = new RegExp("{"+fL+"}","g");
  var cell = r.cells[c-1];
  var value = cell.innerHTML.replace("&nbsp;","");
  switch(kr.cells[1].getElementsByTagName('SELECT')[0].value)
  {
   case "date" : {
	 tmpDate.setFromISO(strdatetime_to_iso(value));
	 value = tmpDate.printf("Y-m-d");
	} break;

   case "currency" : value = value ? parseCurrency(value) : ""; break;
   case "percentage" : case "number" : value = value ? parseFloat(value) : "0"; break;
   case "id" : value = parseFloat(value) > 1000000 ? parseFloat(value)-1000000 : value; break;
  }
  command = command.replace(regex,value);
 }
 alert(command);
}

function getCommands()
{
 var commandString = document.getElementById("gshell-command").value;
 var xtb = document.getElementById('excel-table');
 var xktb = document.getElementById('excel-keys-table');
 var tmpDate = new Date();
 var ret = new Array();

 if(!commandString)
 {
  alert("Devi inserire un comando valido");
  return ret;
 }

 var excludeFirstColumn = document.getElementById('userfirstrowasheader').checked ? 2 : 1;

 for(var i=excludeFirstColumn; i < xtb.rows.length; i++)
 {
  var r = xtb.rows[i];
  var command = commandString;
  for(var c=1; c < xktb.rows.length; c++)
  {
   var kr = xktb.rows[c];
   var fL = kr.getAttribute('fieldletter');
   var regex = new RegExp("{"+fL+"}","g");
   var cell = r.cells[c-1];
   var value = cell.innerHTML.replace("&nbsp;","");
   switch(kr.cells[1].getElementsByTagName('SELECT')[0].value)
   {
    case "date" : {
	  tmpDate.setFromISO(strdatetime_to_iso(value));
	  value = tmpDate.printf("Y-m-d");
	 } break;
    case "currency" : value = value ? parseCurrency(value) : ""; break;
    case "percentage" : case "number" : value = value ? parseFloat(value) : "0"; break;
    case "id" : value = parseFloat(value) > 1000000 ? parseFloat(value)-1000000 : value; break;
   }
   command = command.replace(regex,value);
  }
  ret.push(command);
 }
 return ret;
}
</script>
</body></html>
<?php


