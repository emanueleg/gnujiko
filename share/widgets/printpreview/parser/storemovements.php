<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-09-2012
 #PACKAGE: store
 #DESCRIPTION: Official Gnujiko Store movements parser for print preview.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/


?>
<script>
var STORE_ID = "<?php echo $_REQUEST['storeid'] ? $_REQUEST['storeid'] : '0'; ?>";

function loadPreview()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a || !a['items'])
	  return;
	 storeInsertRows(a['items']);
	}
 sh.sendCommand("<?php echo $_REQUEST['qry']; ?>");
}

function storeInsertRows(itemList)
{
 var tb = document.getElementById('itemlist');
 if(!tb)
  return;

 if(!itemList)
  return;

 // use last row as footer //
 var fR = tb.rows[tb.rows.length-1];
 var cR = null;
 var startAt = 0;
 if(tb.rows[0].cells[0].tagName.toUpperCase() == "TH")
 {
  var fRH = tb.rows[1].cells[0].offsetHeight;
  startAt++;
  if(fR.rowIndex >= 2)
   fRH+= tb.rows[2].cells[0].offsetHeight;
  if(tb.rows[1] != fR)
   cR = tb.rows[1];
 }
 else
 {
  var fRH = tb.rows[0].cells[0].offsetHeight;
  if(fR.rowIndex >= 1)
   fRH+= tb.rows[1].cells[0].offsetHeight;
  if(tb.rows[0] != fR)
   cR = tb.rows[0];
 }

 var fRHstart = fRH;
 var start = <?php echo $_START ? $_START : "0"; ?>;
 var elidx = 0;

 for(var c=start; c < itemList.length; c++)
 {
  var r = tb.insertRow(startAt+c-start);
  for(var i=0; i < tb.rows[0].cells.length; i++)
  {
   var value = itemList[c][tb.rows[0].cells[i].id] ? itemList[c][tb.rows[0].cells[i].id] : "";

   /* Questa è la sola porzione di codice che è stata aggiunta */
 
   switch(tb.rows[0].cells[i].id)
   {
    case 'op' : {
		 switch(itemList[c]['action'])
		 {
		  case '1' : value = "CARICO"; break;
		  case '2' : value = "SCARICO"; break;
		  case '3' : value = "MOVIMENTA"; break;
		 }
		} break; 
	case 'docref' : value = (itemList[c]['doc_ap'] && itemList[c]['doc_id']) ? itemList[c]['doc_name'] : itemList[c]['doc_ref']; break;
   }
   /* Fine della modifica */

   var cell = r.insertCell(-1);
   if(cR)
   {
	cell.className = cR.cells[i].className;
    switch(cR.cells[i].getAttribute('format'))
	{
	 case 'currency' : cell.innerHTML = value ? formatCurrency(value,cR.cells[i].getAttribute('decimals') ? parseInt(cR.cells[i].getAttribute('decimals')) : DECIMALS) : "&nbsp;"; break;
	 case 'percentage' : cell.innerHTML = value ? parseFloat(value)+"%" : "&nbsp;"; break;
	 case 'percentage currency' : {
		 if(!parseFloat(value))
		  cell.innerHTML = "&nbsp;";
		 else if(value.indexOf("%") > 0)
		  cell.innerHTML = value;
		 else
		  cell.innerHTML = formatCurrency(value,cR.cells[i].getAttribute('decimals') ? parseInt(cR.cells[i].getAttribute('decimals')) : DECIMALS);
		} break;
	 case 'date' : case 'datetime' : case 'time' : {
		 if(!value || (value == "")){cell.innerHTML = "&nbsp;";return;}
		 if(parseFloat(value) > 0)
		 {
		  var tDate = new Date(parseFloat(value)*1000);
		 }
		 else
		 {
		  var tDate = new Date();
		  tDate.setFromISO(value);
		 }
		 switch(cR.cells[i].getAttribute('format'))
		 {
		  case 'date' : cell.innerHTML = tDate.printf('d/m/Y'); break;
		  case 'time' : cell.innerHTML = tDate.printf('H:i'); break;
		  case 'datetime' : cell.innerHTML = tDate.printf('d/m/Y H:i'); break;
		 }
		} break;

	 default : cell.innerHTML = value ? value : "&nbsp;"; break;
	}

   }
   else
    cell.innerHTML = value ? value : "&nbsp;";
   if(tb.rows[0].cells[i].style.display == "none")
	cell.style.display = "none";
  }

  fRH-= r.cells[0].offsetHeight;

  if(px2mm(fRH) < 5)
  {
   fRH+= r.cells[0].offsetHeight;
   tb.deleteRow(r.rowIndex);
   var page = {start:start, elements:elidx, freespace:px2mm(fRH)};
   if(window.parent && (typeof(window.parent.previewMessage) == "function"))
	window.parent.previewMessage("PAGEBREAK",page);
   break;
  }
  elidx++;
 }

 for(var c=0; c < fR.cells.length; c++)
  fR.cells[c].style.height = px2mm(fRH)+"mm";

 /* Rimuove le colonne nascoste perchè altrimenti escono fuori quando si esporta in PDF */
 for(var i=0; i < tb.rows[0].cells.length; i++)
 {
  if(tb.rows[0].cells[i].style.display == "none")
  {
   for(var c=0; c < tb.rows.length; c++)
    tb.rows[c].deleteCell(i);
   if(tb.getElementsByTagName('COLGROUP').length)
   {
    var col = tb.getElementsByTagName('COLGROUP')[0].getElementsByTagName('COL')[i];
    col.parentNode.removeChild(col);
   }
   i--;
  }
 }
 
 var contents = "<style type='text/css'>"+document.body.getElementsByTagName('STYLE')[1].innerHTML+"</style>" + document.getElementById("___printpreviewpagecontents").innerHTML;
 var page = {start:start, elements:elidx, freespace:px2mm(fRH), contents:contents};
 if(window.parent && (typeof(window.parent.previewMessage) == "function"))
  window.parent.previewMessage("PAGEINFO",page);
}

</script>
<?php
