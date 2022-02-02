<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 08-04-2013
 #PACKAGE: excel-importexport-gui
 #DESCRIPTION: Import from Excel
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
 $_REQUEST['file'] = ltrim($_REQUEST['file'], $_USERS_HOMES."/".$_SESSION['HOMEDIR']."/");
 $ret = GShell("excel read -f `".$_REQUEST['file']."` -limit 5",$_REQUEST['sessid'], $_REQUEST['shellid']);
 $fields = $ret['outarr']['fields'];
 $list = $ret['outarr']['items'];
}

if(!$_REQUEST['parser'] && $_REQUEST['ap'])
 $_REQUEST['parser'] = $_REQUEST['ap'];

$ret = GShell("excel parser-info -p `".$_REQUEST['parser']."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
if(!$ret['error'])
 $_PARSER_INFO = $ret['outarr'];

function autocheckField($keyName, $keyValue, $fieldName, $fieldValue)
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
}

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Import from Excel</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/excel/import.css" type="text/css" />
</head><body>

<div class="excel-widget">
 <div class='excel-header'>
  <span class='excel-title'>Importazione dati da file Excel</span>
  <a href='#' onclick='gframe_close()' class='button-close'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/excel/img/close.png"/></a>
 </div>
 <table width="775" cellspacing="0" cellpadding="0" border="0" style="margin-left:15px;margin-top:15px">
 <tr><td><span class='bluetitle'>Seleziona le colonne che desideri importare</span></td>
	 <td width='280' style="font-size:12px"><input type='checkbox' checked='true' onclick='useFirstRowAsHeader(this)' id='userfirstrowasheader'/> Usa la prima riga come intestazione</td>
	 <td align='center' width='90' style="font-size:10px">seleziona<br/><a href='#' onclick='selectAllColumns()'>tutte</a> | <a href='#' onclick='unselectAllColumns()'>nessuna</a></td></tr>
 </table>

 <!-- EXCEL TABLE -->
 <div class="excel-table-container">
 <table class="excel-table" cellspacing="0" cellpadding="0" border="0" id="excel-table">
 <?php
 echo "<tr>";
 for($c=0; $c < count($fields); $c++)
  echo "<th".($c==0 ? " style='border-left: 1px solid #0197fd;'" : "")."><input type='checkbox' checked='true' onclick='selectColumn(this)'/>".$fields[$c]['letter']."</th>";
 echo "</tr>";

 echo "<tr>";
 for($c=0; $c < count($fields); $c++)
  echo "<td class='head'".($c==0 ? " style='border-left: 1px solid #dadada'" : "").">".$fields[$c]['value']."</td>";
 echo "</tr>";


 for($r=1; $r < count($list); $r++)
 {
  echo "<tr>";
  for($c=0; $c < count($fields); $c++)
  {
   $field = $fields[$c]['name'];
   $value = $list[$r][$field];
   echo "<td".($c==0 ? " style='border-left: 1px solid #dadada'" : "").">".($value ? $value : "&nbsp;")."</td>";
  }
  echo "</tr>";
 }
 ?>
 </table>
 </div>
 <!-- EOF - EXCEL TABLE -->

 <span class='bluetitle' style="margin-left:15px">Per ciascuna colonna attribuisci la relativa chiave.</span>
 <br/>
 <div class="excel-keys-container">
 <table width="730" cellspacing="0" cellpadding="0" border="0" class="excel-keys-table" id="excel-keys-table" style="margin-left:15px;margin-top:15px">
 <tr><th width='80'>COLONNA</th><th style="text-align:left;padding-left:10px">CHIAVE</th></tr>
 <?php
 for($c=0; $c < count($fields); $c++)
 {
  echo "<tr fieldname='".$fields[$c]['name']."' fieldletter='".$fields[$c]['letter']."'><td><div class='column'>".$fields[$c]['letter']."</div></td>";
  echo "<td><select onchange='fieldControlCheck(this)'><option value=''> </option>";
  if($_PARSER_INFO)
  {
   reset($_PARSER_INFO['keys']);
   while(list($k,$v) = each($_PARSER_INFO['keys']))
   {
	$checkOK = autocheckField($k, $v, $fields[$c]['name'], $fields[$c]['value']);
	echo "<option value='".$k."'".($checkOK ? " selected='selected'>" : ">").$v."</option>";
   }
  }
  echo "</select></td></tr>";
 }
 ?>
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
var PARSER = "<?php echo $_REQUEST['parser']; ?>";

function bodyOnLoad()
{

}

function submit()
{
 var ufrah = document.getElementById('userfirstrowasheader').checked;
 var tb = document.getElementById('excel-keys-table');
 var fieldscount = 0;
 var keys = "";
 var columns = "";

 for(var c=1; c < tb.rows.length; c++)
 {
  var r = tb.rows[c];
  if(r.style.display == "none")
   continue;
  var key = r.cells[1].getElementsByTagName('SELECT')[0].value;
  if(!key)
   continue;
  keys+=","+key;
  columns+= ","+r.getAttribute('fieldletter');
  fieldscount++;
 }
 
 var sh = new GShell();
 sh.OnPreOutput = function(){}
 sh.OnFinish = function(){gframe_close("done!");}

 var cmd = "excel import -f `<?php echo $_REQUEST['file']; ?>` -p `"+PARSER+"` -keys `"+keys.substr(1)+"` -columns `"+columns.substr(1)+"`";
 if(ARCHIVE_PREFIX) cmd+= " -ap '"+ARCHIVE_PREFIX+"'";
 if(CAT_ID) cmd+= " -cat '"+CAT_ID+"'";
 else if(CAT_TAG) cmd+= " -ct `"+CAT_TAG+"`";
 if(ufrah) cmd+= " -from 2";

 sh.sendCommand(cmd);
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
  selectColumn(cb);
 }
}

function unselectAllColumns()
{
 var tb = document.getElementById('excel-table');
 for(var c=0; c < tb.rows[0].cells.length; c++)
 {
  var cb = tb.rows[0].cells[c].getElementsByTagName('INPUT')[0];
  cb.checked = false;
  selectColumn(cb);
 }
}

function selectColumn(cb)
{
 var tb = document.getElementById("excel-keys-table");
 tb.rows[cb.parentNode.cellIndex+1].style.display = cb.checked ? "" : "none";
}

function fieldControlCheck(sel)
{
 var r = sel.parentNode.parentNode;
 var tb = document.getElementById("excel-keys-table");
 for(var c=1; c < tb.rows.length; c++)
 {
  if(tb.rows[c] == r)
   continue;
  if(tb.rows[c].style.display == "none")
   continue;
  if(tb.rows[c].cells[1].getElementsByTagName('SELECT')[0].value == sel.value)
   return alert("Questa chiave è già stata selezionata");
 }
}
</script>
</body></html>
<?php


