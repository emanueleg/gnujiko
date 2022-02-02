<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-12-2013
 #PACKAGE: hacktvsearch-common
 #DESCRIPTION: Manage table columns.
 #VERSION: 2.1beta
 #CHANGELOG: 24-12-2013 : Bug fix generali.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>HackTVForms - Manage Table Columns</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gextendedtable/index.php");
?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/hacktvforms/css/templatedefault.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/hacktvforms/css/managetablecolumns.css" type="text/css" />
</head><body>
<div class="graywidget" style="width:640px;height:480px">
 <div class="graywidget-header">Gestisci colonne <img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/hacktvforms/img/widgetclose-gray.png" onclick="gframe_close()" class="widget-close"/>
 </div>

 <div class="graywidget-section" style="height:380px;overflow:auto">
  <table id="columnstable" class="gextendedtable" width="100%" cellspacing="0" cellpadding="0" border="0">
   <tr><th width='64'>TAG</th>
	   <th>TITOLO</th>
	   <th width='110'>FORMAT.</th>
	   <th width='100'>LARGH. (px)</th>
	   <th width='50'>TOTALI</th></tr>

   <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td align='center'><input type='checkbox' title="Se spuntato include questa colonna nella riga dei totali"/></td></tr>

  </table>
 </div>

 <div class="graywidget-footer">
  <span class="left-button blue" onclick="submit()">Salva</span> 
  <span class="right-button gray" onclick="gframe_close()">Chiudi</span> 
 </div>

</div>

<script>
var COLUMNSTABLE = null;
var NEW_COLUMNS = new Array();
var UPDATED_COLUMNS = new Array();
var REMOVED_COLUMNS = new Array();

var FIELD_ID_IDX = 0;

function bodyOnLoad()
{
 COLUMNSTABLE = new GExtendedTable(document.getElementById('columnstable'));
 COLUMNSTABLE.options.autoAddRow = true;
 COLUMNSTABLE.options.leaveLastRowEmpty = true;

 COLUMNSTABLE.Fields[0].options.editable = true;
 COLUMNSTABLE.Fields[1].options.editable = true;
 COLUMNSTABLE.Fields[2].options.editable = true;
 COLUMNSTABLE.Fields[2].options.format.type = "combobox";
 COLUMNSTABLE.Fields[2].options.comboitems.push({'name':"Testo", 'value':'TEXT'});
 COLUMNSTABLE.Fields[2].options.comboitems.push({'name':"Data", 'value':'DATE'});
 COLUMNSTABLE.Fields[2].options.comboitems.push({'name':"Valuta", 'value':'CURRENCY'});
 COLUMNSTABLE.Fields[2].options.comboitems.push({'name':"Percentuale", 'value':'PERCENTAGE'});
 COLUMNSTABLE.Fields[2].options.comboitems.push({'name':"Numero", 'value':'NUMBER'});
 COLUMNSTABLE.Fields[3].options.editable = true;
 COLUMNSTABLE.Fields[3].options.format.type = "number";

 COLUMNSTABLE.OnNewRow = function(r)
 {
  while(document.getElementById("field-"+FIELD_ID_IDX))
   FIELD_ID_IDX++;
  r.id = "field-"+FIELD_ID_IDX;
  r.cells[0].innerHTML = r.id;
  r.cells[4].innerHTML = "<input type='checkbox' title='Se spuntato include questa colonna nella riga dei totali'/"+">"; 
  r.cells[4].style.textAlign='center';
  NEW_COLUMNS.push(r);
  FIELD_ID_IDX++;
 }

 COLUMNSTABLE.OnDeleteRow = function(list)
 {
  for(var c=0; c < list.length; c++)
  {
   if(list[c].id)
    REMOVED_COLUMNS.push(list[c].id);
  }
 }

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
"AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ");

 var tb = div.getElementsByTagName('TABLE')[0];

 COLUMNSTABLE.Clear();

 /* IMPORT FIELDS */
 for(var c=0; c < tb.rows[0].cells.length; c++)
 {
  var _cell = tb.rows[0].cells[c];
  if(_cell.getElementsByTagName('INPUT').length) /* se il titolo contiene una checkbox salta questa cella. */
   continue;

  var r = COLUMNSTABLE.AddRow(true);
  r.id = _cell.id; // <-- da rimuovere
  r.cells[0].innerHTML = _cell.id;
  var value = "";

  if(_cell.getElementsByTagName('A').length)
   value = _cell.getElementsByTagName('A')[0].innerHTML;
  else if(_cell.getElementsByTagName('SPAN').length)
   value = _cell.getElementsByTagName('SPAN')[0].innerHTML;
  else
   value = _cell.innerHTML;

  r.cells[1].innerHTML = value;

  /* format */
  switch(_cell.getAttribute('format'))
  {
   case 'date' : r.cells[2].innerHTML = "Data"; break;
   case 'currency' : r.cells[2].innerHTML = "Valuta"; break;
   case 'percentage' : r.cells[2].innerHTML = "Percentuale"; break;
   case 'number' : r.cells[2].innerHTML = "Numero"; break;
   default : r.cells[2].innerHTML = "Testo"; break;
  }

  /* width */
  if(_cell.getAttribute('width'))
   r.cells[3].innerHTML = _cell.getAttribute('width');
  
  /* include into totals */
  if(_cell.getAttribute('includeintototals'))
   r.cells[4].getElementsByTagName('INPUT')[0].checked = true;
 }
 
 NEW_COLUMNS = new Array();
}

function submit()
{
 var ret = new Array();
 ret['NEW_COLUMNS'] = new Array();
 ret['UPDATED_COLUMNS'] = new Array();
 ret['REMOVED_COLUMNS'] = REMOVED_COLUMNS;

 var formats = new Array();
 formats['Testo'] = "text";
 formats['Data'] = "date";
 formats['Valuta'] = "currency";
 formats['Percentuale'] = "percentage";
 formats['Numero'] = "number";

 for(var c=0; c < NEW_COLUMNS.length; c++)
 {
  var r = NEW_COLUMNS[c];
  if(r.isVoid()) continue;
  var column = new Array();
  column['id'] =  r.cells[0].innerHTML;
  column['title'] = r.cells[1].innerHTML;
  column['format'] = formats[r.cells[2].innerHTML];
  column['width'] = r.cells[3].innerHTML;
  column['includeintototals'] = r.cells[4].getElementsByTagName('INPUT')[0].checked ? true : false;
  ret['NEW_COLUMNS'].push(column);
 }

 for(var c=1; c < COLUMNSTABLE.O.rows.length; c++)
 {
  var r = COLUMNSTABLE.O.rows[c];

  if(r.isVoid()) continue;

  if(NEW_COLUMNS.indexOf(r) > -1)
   continue;

  var column = new Array();
  column['id'] = r.cells[0].innerHTML;
  column['title'] = r.cells[1].innerHTML;
  column['format'] = formats[r.cells[2].innerHTML];
  column['width'] = r.cells[3].innerHTML;
  column['includeintototals'] = r.cells[4].getElementsByTagName('INPUT')[0].checked ? true : false;
  ret['UPDATED_COLUMNS'].push(column);
 }

 gframe_close("done", ret);
}

</script>
</body></html>
<?php

