<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-09-2016
 #PACKAGE: excel-importexport-gui
 #DESCRIPTION: Import from Excel
 #VERSION: 2.5beta
 #CHANGELOG: 07-09-2016 : Possibilita di nascondere le opzioni
			 02-03-2016 : Rifatta widget ed aggiunto opzioni.
			 29-09-2014 : Aggiunto possibilità di selezionare il foglio.
			 17-05-2014 : Aggiunto parametro fast per importazioni veloci ove possibile.
			 13-05-2014 : Bug fix su chiavi duplicate.
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
 $bp = $_USERS_HOMES.$_SESSION['HOMEDIR'];
 if(substr($_REQUEST['file'],0,strlen($bp)) == $bp)
  $_REQUEST['file'] = ltrim(substr($_REQUEST['file'],strlen($bp)),"/");
 $ret = GShell("excel read -f `".$_REQUEST['file']."` -limit 5".($_REQUEST['sheet'] ? " -s '".$_REQUEST['sheet']."'" : ""),$_REQUEST['sessid'], $_REQUEST['shellid']);
 $fields = $ret['outarr']['fields'];
 $list = $ret['outarr']['items'];
 $excelInfo = $ret['outarr']['info'];
}

if(!$_REQUEST['parser'] && $_REQUEST['ap'])
 $_REQUEST['parser'] = $_REQUEST['ap'];

$ret = GShell("excel parser-info -p `".$_REQUEST['parser']."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
if(!$ret['error'])
 $_PARSER_INFO = $ret['outarr'];

$_GROUP = (isset($_REQUEST['group']) && $_REQUEST['group']) ? $_REQUEST['group'] : "";
$_HIDE_OPTIONS = isset($_REQUEST['hideoptions']) ? true : false;

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
 <tr><td><span class='smalltext'>File: <?php echo $excelInfo['filename']; ?></span></td>
	 <td colspan='2'><span class='smalltext'>Foglio: </span><select id='sheetselect' style='width:300px' onchange='SelectSheet(this)'><?php
		 for($c=0; $c < count($excelInfo['sheets']); $c++)
		  echo "<option value='".$c."'".(($_REQUEST['sheet'] == $c) ? " selected='selected'>" : ">").$excelInfo['sheets'][$c]."</option>";
		?></select></td></tr>
 <tr><td><span class='bluetitle'>Seleziona le colonne che desideri importare</span></td>
	 <td width='280' style="font-size:12px"><input type='checkbox' checked='true' onclick='useFirstRowAsHeader(this)' id='userfirstrowasheader'/> Usa la prima riga come intestazione</td>
	 <td align='center' width='90' style="font-size:10px">seleziona<br/><a href='#' onclick='selectAllColumns()'>tutte</a> | <a href='#' onclick='unselectAllColumns()'>nessuna</a></td></tr>
 </table>

 <!-- EXCEL TABLE -->
 <div class="excel-table-container">
 <?php
 if(!count($fields))
 {
  echo "<h3 style='color:red;text-align:center'>Errore nel file: ".$_REQUEST['file']."</h3>";
  echo "<h4 style='text-align:center'>Nessuna colonna trovata".($_REQUEST['sheet'] ? " nel foglio '".$excelInfo['sheets'][$_REQUEST['sheet']]."'" : "")."</h4>";
 }
 else
 {
  ?>
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
  <?php
 }
 ?>
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

 <!-- OPTIONS -->
 <div class="excel-options-container" <?php if($_HIDE_OPTIONS) echo "style='display:none'"; ?>>
  <span class='bluetitle' style="margin-left:5px">Opzioni</span>
  <div <?php if(count($excelInfo['sheets']) < 2) echo "style='display:none'"; ?>>
   <input type='checkbox' id='load-all-sheets'/>Carica tutti i fogli. (<small>E&lsquo;importante che tutti i fogli abbiano le medesime disposizioni delle colonne</small>)
  </div>
  <div <?php if(count($excelInfo['sheets']) > 1) echo "style='display:none'"; ?>>
   <input type='checkbox' id='save-into-newcat'/>Salva dentro nuova sotto-categoria: <input type='text' class='edit' id='catname' style='width:350px' value="<?php echo $excelInfo['sheets'][0]; ?>" placeholder="Digita il nome da attribuire alla nuova categoria"/>
  </div>
 </div>
 <!-- EOF - OPTIONS -->

 <div class="excel-footer">
  <a href='#' onclick='gframe_close()' class='graybutton' style='float:left;'>Annulla</a>
  <a href='#' onclick='submit()' class='bluebutton' style='float:right;'>Procedi &raquo;</a>
 </div>

</div>

<script>
var ARCHIVE_PREFIX = "<?php echo $_REQUEST['ap']; ?>";
var GROUP = "<?php echo $_GROUP; ?>";
var CAT_ID = "<?php echo $_REQUEST['cat']; ?>";
var CAT_TAG = "<?php echo $_REQUEST['ct']; ?>";
var ID = "<?php echo $_REQUEST['id']; ?>";
var PARSER = "<?php echo $_REQUEST['parser']; ?>";
var FAST = <?php echo $_REQUEST['fast'] ? "true" : "false"; ?>;
var HIDE_OPTIONS = <?php echo $_HIDE_OPTIONS ? "true" : "false"; ?>;
var SHEETIDX = "<?php echo $_REQUEST['sheet'] ? $_REQUEST['sheet'] : '0'; ?>";
var IN_PROGRESS = false;
var SHEET_COUNT = <?php echo count($excelInfo['sheets']); ?>;
var SHEET_NAMES = new Array();
var SH = new GShell();


<?php
for($c=0; $c < count($excelInfo['sheets']); $c++)
 echo "SHEET_NAMES.push(\"".$excelInfo['sheets'][$c]."\");\n";
?>

function bodyOnLoad()
{
 gframe_shotmessage("Import frame is loaded",null,"LOADED");
}

function submit()
{
 if(IN_PROGRESS)
  return alert("Attendere, importazione del file in corso...");
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
 
 var loadAllSheet = (document.getElementById('load-all-sheets').checked == true) ? true : false;
 var createSubCat = (document.getElementById('save-into-newcat').checked == true) ? true : false;

 if(!HIDE_OPTIONS)
 {
  if(loadAllSheet)
   var catName = SHEET_NAMES[0];
  else
  {
   var catName = null;
   if(createSubCat)
   {
    var catName = document.getElementById('catname').value;
    if(!catName || (catName == ""))
    {
     var catName = prompt("Digita il nome da attribuire alla nuova categoria");
     if(!catName) return;
    }
   }
  }
 }

 // PREPARE COMMAND
 var cmd = "excel import -f `<?php echo $_REQUEST['file']; ?>` -p `"+PARSER+"` -keys `"+keys.substr(1)+"` -columns `"+columns.substr(1)+"`";
 if(ARCHIVE_PREFIX) 	cmd+= " -ap '"+ARCHIVE_PREFIX+"'";
 if(ID) 				cmd+= " -id '"+ID+"'";
 if(ufrah) 				cmd+= " -from 2";

 if(FAST)				cmd+= " -fast";

 // EXEC 
 var sh = new GShell();
 if(loadAllSheet)
 {
  sh.showProcessMessage("Importazione da Excel", "Attendere prego, &egrave; in corso l&lsquo;importazione da file Excel", 'multi','process',SHEET_NAMES);
 }
 importFromSheet(loadAllSheet ? 0 : SHEETIDX, cmd, sh, catName, loadAllSheet);
 IN_PROGRESS = true;
}

function importFromSheet(idx, cmd, sh, catName, loadAllSheet)
{
 if(!loadAllSheet)
  sh.showProcessMessage("Importazione da Excel","Attendere prego, &egrave; in corso la preparazione per l&lsquo;importazione del foglio "+SHEET_NAMES[idx]+" dal file Excel");
 sh.OnInterfaceUpdate = function(){
	 if(!loadAllSheet)
	  this.hideProcessMessage();
	 else
	  this.processMessage.style.visibility = "hidden";
	}

 sh.OnPreOutput = function(o,a,msgType,msgRef,mode){}
 sh.OnOutput = function(o,a){
	 if(loadAllSheet && (SHEET_COUNT > (idx+1)))
	 {
	  this.processMessage.style.visibility = "";
	  this.processMessage.nextStep();
	  importFromSheet(idx+1, cmd, sh, SHEET_NAMES[idx+1], true);
	 }
	 else
	  gframe_close("done!", loadAllSheet ? SHEET_COUNT : 1);
	}
 sh.OnError = function(err){
	 if(loadAllSheet)
	  this.processMessage.error(err);
	 else
	 {
	  this.hideProcessMessage();
	  alert(err);
	 }
	 IN_PROGRESS=false;
	}

 var command = cmd;
 if(catName && !HIDE_OPTIONS)
 {
  command = "dynarc new-cat -ap '"+ARCHIVE_PREFIX+"' --if-not-exists -name `"+catName+"`"+(GROUP ? " -group '"+GROUP+"'" : "");
  if(CAT_ID) 		command+= " -parent '"+CAT_ID+"'";
  else if(CAT_TAG)	command+= " -pt '"+CAT_TAG+"'";

  command+= " || "+cmd+" -s '"+idx+"' -cat *.id";
 }
 else if(CAT_ID)
  command+= " -s '"+idx+"' -cat '"+CAT_ID+"'";
 else if(CAT_TAG)
  command+= " -s '"+idx+"' -ct '"+CAT_TAG+"'";
 else
  command+= " -s '"+idx+"'";

 sh.sendCommand(command);
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
 if(!sel.value)
  return;
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

function SelectSheet(sel)
{
 SH.showProcessMessage("Caricamento foglio","Attendere prego, sto caricando il foglio dal file Excel");
 var url = document.location.href;
 if(url.indexOf('&sheet=') > 0)
  url = url.replace("&sheet="+SHEETIDX, "&sheet="+sel.value);
 else
  url = url+"&sheet="+sel.value;
 SHEETIDX = sel.value;

 window.setTimeout(function(){document.location.href = url;}, 500);
}
</script>
</body></html>
<?php


