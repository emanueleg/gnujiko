<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-05-2013
 #PACKAGE: excel-importexport-gui
 #DESCRIPTION: Export to Excel
 #VERSION: 2.0beta
 #CHANGELOG: 
 #DEPENDS: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_USERS_HOMES, $_PARSER_INFO;
$_BASE_PATH = "../../../";
$_PARSER_INFO = null;

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

if($_REQUEST['file'])
{
 //$_REQUEST['file'] = ltrim($_REQUEST['file'], $_USERS_HOMES."/".$_SESSION['HOMEDIR']."/");
 /*$ret = GShell("excel read -f `".$_REQUEST['file']."` -limit 5",$_REQUEST['sessid'], $_REQUEST['shellid']);
 $fields = $ret['outarr']['fields'];
 $list = $ret['outarr']['items'];*/
}

/*if(!$_REQUEST['parser'] && $_REQUEST['ap'])
 $_REQUEST['parser'] = $_REQUEST['ap'];*/

/*function autocheckField($keyName, $keyValue, $fieldName, $fieldValue)
{
 global $_PARSER_INFO;
 $fieldName = strtolower(trim($fieldName));
 $fieldValue = strtolower(trim($fieldValue));

 if($keyName == $fieldName)
  return true;
 if($keyValue == $fieldValue)
  return true;

 if(!$_PARSER_INFO['keydict'])
  return false;

 $dict = $_PARSER_INFO['keydict'][$keyName];
 if(!$dict || !count($dict))
  return false;

 for($c=0; $c < count($dict); $c++)
 {
  if(($dict[$c] == $fieldName) || ($dict[$c] == $fieldValue))
   return true;
  if(strpos($fieldName,$dict[$c]) !== false)
   return true;
  if(strpos($fieldValue,$dict[$c]) !== false)
   return true;
 }
 
 return false;
}*/

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


 /*for($r=1; $r < count($list); $r++)
 {
  echo "<tr>";
  for($c=0; $c < count($fields); $c++)
  {
   $field = $fields[$c]['name'];
   $value = $list[$r][$field];
   echo "<td".($c==0 ? " style='border-left: 1px solid #dadada'" : "").">".($value ? $value : "&nbsp;")."</td>";
  }
  echo "</tr>";
 }*/
 ?>
 </table>
 </div>
 <!-- EOF - EXCEL TABLE -->


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

 var letters = new Array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");

 var tb = div.getElementsByTagName('TABLE')[0];
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
  str2+= "<td class='head'"+(idx==0 ? " style='border-left: 1px solid #dadada'" : "")+">"+tb.rows[0].cells[c].innerHTML+"</td>";
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

   cell.innerHTML = value;
  }

 }

}

function submit()
{
 var ufrah = document.getElementById('userfirstrowasheader').checked ? 0 : 1;
 var tb = document.getElementById('excel-table');
 var htmltable = "<table>";

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
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.href = ABSOLUTE_URL+"getfile.php?file="+a['filename'];
	 window.setTimeout(function(){gframe_close(o,a);},1000);
	}

 var cmd = "excel write -f `<?php echo $_REQUEST['file']; ?>` -s `Sheet1` -htmltable `"+htmltable+"`";
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


