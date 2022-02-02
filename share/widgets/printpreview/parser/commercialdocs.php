<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 12-07-2013
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: CommercialDocs parser for print preview.
 #VERSION: 2.4beta
 #CHANGELOG: 12-07-2013 : Risolto bug delle celle che sbordavano su testi lunghi.
			 09-07-2013 : Ora è possibile inserire 2 tabelle.
			 12-04-2013 : Aggiunto le 3 colonne degli sconti.
			 11-04-2013 : Bug fix nelle altezze delle celle nascoste.
 #TODO:
 
*/

?>
<script>
function loadPreview()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 commercialdocsInsertRows(a);
	}
 sh.sendCommand("dynarc item-info -ap `commercialdocs` -id `<?php echo $_ID; ?>` -extget `cdinfo,cdelements`");
}

function commercialdocsInsertRows(a) /* this function is small different than the default function autoInsertRows */
{
 var tb = document.getElementById('itemlist');
 var tb2 = document.getElementById('itemlist2');
 if(!tb)
  return;

 if(!a || !a['elements'])
  return;

 // use last row as footer //
 var fR = tb.rows[tb.rows.length-1];
 var cR = null;
 var startAt = 0;
 if(tb.rows[0].cells[0].tagName.toUpperCase() == "TH")
 {
  var fRH = tb.rows[1].offsetHeight;
  startAt++;
  if(fR.rowIndex >= 2)
   fRH+= tb.rows[2].offsetHeight;
  if(tb.rows[1] != fR)
   cR = tb.rows[1];
 }
 else
 {
  var fRH = tb.rows[0].offsetHeight;
  if(fR.rowIndex >= 1)
   fRH+= tb.rows[1].offsetHeight;
  if(tb.rows[0] != fR)
   cR = tb.rows[0];
 }

 var fRHstart = fRH;
 var start = <?php echo $_START ? $_START : "0"; ?>;
 var elidx = 0;

 for(var c=start; c < a['elements'].length; c++)
 {
  var r = tb.insertRow(startAt+c-start);
  for(var i=0; i < tb.rows[0].cells.length; i++)
  {
   var value = "";
   if(a['elements'][c]['type'].toLowerCase() == "note")
   {
	if(tb.rows[0].cells[i].id == "name")
	 value = a['elements'][c]["desc"] ? a['elements'][c]["desc"] : "";
	else
	 value = "";
   }
   else if((tb.rows[0].cells[i].id == "discount") && (a['elements'][c]['discount2'] != "0"))
   {
	value = a['elements'][c]['discount'] ? parseFloat(a['elements'][c]['discount']).toString() : "0";
	if(a['elements'][c]['discount2'])
	 value+= "+"+a['elements'][c]['discount2'];
	if(a['elements'][c]['discount3'])
	 value+= "+"+a['elements'][c]['discount3'];
   }
   else
	value = a['elements'][c][tb.rows[0].cells[i].id] ? a['elements'][c][tb.rows[0].cells[i].id] : "";

   var cell = r.insertCell(-1);

   if(cR)
   {
	cell.className = cR.cells[i].className;
	if((tb.rows[0].cells[i].id == "discount") && (a['elements'][c]['discount2'] != "0"))
	 cell.innerHTML = value;
	else
	{
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
	  default : {
		 if(a['elements'][c]['type'].toLowerCase() == "note")
		  cell.innerHTML = "<div style=\"width:"+getCellWidth(cell)+"mm\">"+(value ? value : "&nbsp;")+"</div>"; 
		 else
		  cell.innerHTML = value ? value : "&nbsp;"; 
		} break;
	 }
	}
   }
   else
    cell.innerHTML = value ? value : "&nbsp;";
   if(tb.rows[0].cells[i].style.display == "none")
	cell.style.display = "none";
  }

  fRH-= r.offsetHeight;

  if(px2mm(fRH) < 5)
  {
    // mette la scritta **SEGUE** nel totale //
    var follows = document.getElementsByName("follow");
	for(var j=0; j < follows.length; j++)
	 follows[j].innerHTML = "**SEGUE**";

   fRH+= r.offsetHeight;
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

 /* EOF - FIRST (or unique) TABLE */

 /* SECOND TABLE (optional) */
 if(tb2)
 {
  // use last row as footer //
  var fR = tb2.rows[tb2.rows.length-1];
  var cR = null;
  var startAt = 0;
  if(tb2.rows[0].cells[0].tagName.toUpperCase() == "TH")
  {
   var fRH = tb2.rows[1].offsetHeight;
   startAt++;
   if(fR.rowIndex >= 2)
    fRH+= tb2.rows[2].offsetHeight;
   if(tb2.rows[1] != fR)
    cR = tb2.rows[1];
  }
  else
  {
   var fRH = tb2.rows[0].offsetHeight;
   if(fR.rowIndex >= 1)
    fRH+= tb2.rows[1].offsetHeight;
   if(tb2.rows[0] != fR)
    cR = tb2.rows[0];
  }

  var fRHstart = fRH;
  var start = <?php echo $_START ? $_START : "0"; ?>;
  var elidx = 0;

  for(var c=start; c < a['elements'].length; c++)
  {
   var r = tb2.insertRow(startAt+c-start);
   for(var i=0; i < tb2.rows[0].cells.length; i++)
   {
    var value = "";
    if(a['elements'][c]['type'].toLowerCase() == "note")
    {
	 if(tb2.rows[0].cells[i].id == "name")
	  value = a['elements'][c]["desc"] ? a['elements'][c]["desc"] : "";
	 else
	  value = "";
    }
    else if((tb2.rows[0].cells[i].id == "discount") && (a['elements'][c]['discount2'] != "0"))
    {
	 value = a['elements'][c]['discount'] ? parseFloat(a['elements'][c]['discount']).toString() : "0";
	 if(a['elements'][c]['discount2'])
	  value+= "+"+a['elements'][c]['discount2'];
	 if(a['elements'][c]['discount3'])
	  value+= "+"+a['elements'][c]['discount3'];
    }
    else
	 value = a['elements'][c][tb2.rows[0].cells[i].id] ? a['elements'][c][tb2.rows[0].cells[i].id] : "";

    var cell = r.insertCell(-1);

    if(cR)
    {
	 cell.className = cR.cells[i].className;
	 if((tb2.rows[0].cells[i].id == "discount") && (a['elements'][c]['discount2'] != "0"))
	  cell.innerHTML = value;
	 else
	 {
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
	  default : {
		 if(a['elements'][c]['type'].toLowerCase() == "note")
		  cell.innerHTML = "<div style=\"width:"+getCellWidth(cell)+"mm\">"+(value ? value : "&nbsp;")+"</div>"; 
		 else
		  cell.innerHTML = value ? value : "&nbsp;"; 
		} break;
	  }
	 }
    }
    else
     cell.innerHTML = value ? value : "&nbsp;";
    if(tb2.rows[0].cells[i].style.display == "none")
	 cell.style.display = "none";
   }

   fRH-= r.offsetHeight;

   if(px2mm(fRH) < 5)
   {
    fRH+= r.offsetHeight;
    tb2.deleteRow(r.rowIndex);
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
  for(var i=0; i < tb2.rows[0].cells.length; i++)
  {
   if(tb2.rows[0].cells[i].style.display == "none")
   {
    for(var c=0; c < tb2.rows.length; c++)
     tb2.rows[c].deleteCell(i);
    if(tb2.getElementsByTagName('COLGROUP').length)
    {
     var col = tb2.getElementsByTagName('COLGROUP')[0].getElementsByTagName('COL')[i];
     col.parentNode.removeChild(col);
    }
    i--;
   }
  }
 }
 /* EOF - SECOND (optional) TABLE */
 
 var contents = "<style type='text/css'>"+document.body.getElementsByTagName('STYLE')[1].innerHTML+"</style>" + document.getElementById("___printpreviewpagecontents").innerHTML;
 var page = {start:start, elements:elidx, freespace:px2mm(fRH), contents:contents};
 if(window.parent && (typeof(window.parent.previewMessage) == "function"))
  window.parent.previewMessage("PAGEINFO",page);
}

function px2mm(px)
{
 return (px*25.4)/72;
}

function getCellWidth(cell)
{
 return Math.floor(px2mm(parseFloat(getStyle(cell,"width"))));
}

function getStyle(el,styleProp)
{
 var x = el;
  if (x.currentStyle)
 var y = x.currentStyle[styleProp];
  else if (window.getComputedStyle)
 var y = document.defaultView.getComputedStyle(x,null).getPropertyValue(styleProp);
 return y;
}

</script>
<?php
