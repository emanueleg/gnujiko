<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-12-2014
 #PACKAGE: excel-importexport-gui
 #DESCRIPTION: Export to Excel
 #VERSION: 2.2beta
 #CHANGELOG: 11-12-2014 : Bug fix.
			 23-10-2014 : Completata funzione di esportazione.
 #DEPENDS: 
 #TODO:
 #USAGE: gframe -f excel/export -params `file=filename` -c `<table>....</table>`
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_USERS_HOMES, $_PARSER_INFO;
$_BASE_PATH = "../../../";
$_PARSER_INFO = null;

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$fileName = $_REQUEST['file'] ? $_REQUEST['file'] : ($_REQUEST['title'] ? $_REQUEST['title'] : 'untitled');

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Export to Excel</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/excel/export.css" type="text/css" />
</head><body>

<div class="excel-widget">
 <div class='excel-header'>
  <span class='excel-title'>Esportazione dati su file Excel</span>
  <a href='#' onclick='gframe_close()' class='button-close'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/excel/img/close.png"/></a>
 </div>
 <table width="775" cellspacing="0" cellpadding="0" border="0" style="margin-left:15px;margin-top:15px">
 <tr><td><span class='bluetitle'>Seleziona le colonne che desideri esportare</span></td>
	 <td width='280' style="font-size:12px"><input type='checkbox' checked='true' onclick='useFirstRowAsHeader(this)' id='userfirstrowasheader'/> Mostra la riga delle intestazioni</td>
	 <td align='center' width='90' style="font-size:10px">seleziona<br/><a href='#' onclick='selectAllColumns()'>tutte</a> | <a href='#' onclick='unselectAllColumns()'>nessuna</a></td></tr>
 </table>

 <!-- EXCEL TABLE -->
 <div class="excel-table-container">
  <div id="loading" style="text-align:center"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/excel/img/loading.gif" style="margin-top:100px"/></div>
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


 <div class="excel-footer">
  <a href='#' onclick='gframe_close()' class='graybutton' style='float:left;'>Annulla</a>
  <a href='#' onclick='submit()' class='bluebutton' style='float:right;margin-left:20px'>Procedi &raquo;</a>
  <input type='text' class='edit' style='width:150px;float:right;margin-left:5px' id='filename' value="<?php echo $fileName; ?>"/>
  <span class='smalltext' style='float:right;'>Nome del file: </span>
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
 document.getElementById('loading').style.display="none";
 var div = document.createElement('DIV');
 div.innerHTML = contents;
 div.style.display = "none";
 div.style.position = "absolute";
 div.style.top = 0;
 div.style.left = 0;
 document.body.appendChild(div);

 var letters = new Array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
	"AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ");

 var tb = div.getElementsByTagName('TABLE')[0];
 if(!tb)
  return alert("Error: HTML table not found.");
 var xtb = document.getElementById('excel-table');

 var str = "";
 var str2 = "";
 var idx = 0;
 var excludeFirstColumn = 0;

 if(tb.rows[0].cells[0].getElementsByTagName('INPUT').length)
  excludeFirstColumn = 1;

 for(var c=excludeFirstColumn; c < tb.rows[0].cells.length; c++)
 {
  str+= "<th"+(idx==0 ? " style='border-left: 1px solid #0197fd;'" : "")+"><input type='checkbox' checked='true' onclick='selectColumn("+idx+",this)'/"+">"+letters[idx]+"</th>";
  str2+= "<td class='head'"+(idx==0 ? " style='border-left: 1px solid #dadada'" : "")+" format='"+(tb.rows[0].cells[c].getAttribute('format') ? tb.rows[0].cells[c].getAttribute('format') : 'string')+"'>"+tb.rows[0].cells[c].innerHTML+"</td>";
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
 var filename = document.getElementById('filename').value;
 if(!filename)
  return alert("Devi specificare un nome file valido");

 var ufrah = document.getElementById('userfirstrowasheader').checked ? 0 : 1;
 var tb = document.getElementById('excel-table');
 var htmltable = "<table>";

 // detect formats //
 var formats = "";
 var r = tb.rows[1];
 for(var c=0; c < r.cells.length; c++)
 {
  if((r.cells[c].className == "disabled") || (r.cells[c].className == "head-disabled"))
   continue;
  formats+= ","+(r.cells[c].getAttribute('format') ? r.cells[c].getAttribute('format') : 'string');
 }
 if(formats) formats = formats.substr(1);

 for(var c=(ufrah+1); c < tb.rows.length; c++)
 {
  var r = tb.rows[c];
  if(r.style.display == "none")
   continue;
  htmltable+= "<tr>";
  for(var i=0; i < r.cells.length; i++)
  {
   if((r.cells[i].className == "disabled") || (r.cells[i].className == "head-disabled"))
	continue;
   htmltable+= "<td>"+r.cells[i].innerHTML+"</td>";
  }
  htmltable+= "</tr>";
 }
 
 htmltable+= "</table>";

 var sh = new GShell();
 sh.OnError = function(err,code){alert(err);}
 sh.OnPreOutput = function(){}
 sh.OnOutput = function(o,a){gframe_close(o,a);}

 var cmd = "excel write -f `"+filename+"` -s `Sheet1` -htmltable `"+htmltable+"` -formats '"+formats+"'";
 sh.sendCommand(cmd);
}

function useFirstRowAsHeader(cb)
{
 var tb = document.getElementById('excel-table');
 if(tb.rows.length < 2)
  return;
 var r = tb.rows[1];
 r.style.display = cb.checked ? "" : "none";
 /*for(var c=0; c < r.cells.length; c++)
  r.cells[c].className = cb.checked ? "head" : "";*/
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

</script>
</body></html>
<?php


