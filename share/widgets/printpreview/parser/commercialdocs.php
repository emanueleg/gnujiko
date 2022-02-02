<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-01-2016
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: CommercialDocs parser for print preview.
 #VERSION: 2.15beta
 #CHANGELOG: 18-01-2016 : Bug fix su reset defaultumis.
			 14-01-2016 : Aggiunto unita di misura predefinita.
			 23-03-2015 : Bug fix sugli sconti.
			 10-03-2015 : Bug fix soolp con aggiunta altezza massima gabbia x multipagine.
			 20-01-2015 : Bug fix.
			 26-05-2014 : Nasconde i messaggi non stampabili
			 04-03-2014 : Aggiunto il nr di riga.
			 26-02-2014 : Aggiunto immagine prodotto e full-description
			 19-02-2014 : Bug fix sugli sconti.
			 12-02-2014 : Fix su percentuali di sconto.
			 10-02-2014 : Aggiunto chiave SOOLP (Shows only on the last page) simile a FOLLOW solo che al posto di scrivere segue lascia in bianco il campo
			 12-07-2013 : Risolto bug delle celle che sbordavano su testi lunghi.
			 09-07-2013 : Ora è possibile inserire 2 tabelle.
			 12-04-2013 : Aggiunto le 3 colonne degli sconti.
			 11-04-2013 : Bug fix nelle altezze delle celle nascoste.
 #TODO:
 
*/

/* GET CONFIG */
$config = array();
$ret = GShell("aboutconfig get-config -app gcommercialdocs -sec interface", $_REQUEST['sessid'], $_REQUEST['shellid']);
if(!$ret['error'])
 $config = $ret['outarr']['config'];

?>
<script>
var DEFAULT_UMIS = new Array();
<?php
if($config && $config['defaultumis'])
{
 reset($config['defaultumis']);
 while(list($k,$v) = each($config['defaultumis']))
  echo "DEFAULT_UMIS['".$k."'] = \"".$v."\";\n";
}
?>

function loadPreview()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 /* RIMUOVO TUTTI I MESSAGGI NON STAMPABILI */
	 var elements = new Array();
	 if(!a['elements'])
	  return;
	 for(var c=0; c < a['elements'].length; c++)
	 {
	  if(a['elements'][c]['type'].toLowerCase() != "message")
	   elements.push(a['elements'][c]);
	 }
	 a['elements'] = elements;
	 /* EOF - RIMUOVO TUTTI I MESSAGGI NON STAMPABILI */
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
 var completed = true;
 var extraH = 0;
 var autoadapted = false;

 var follows = document.getElementsByName("follow");
 var soolp = document.getElementsByName("soolp");

 if(tb.getAttribute("maxheight"))
 {
  var px = parseFloat(tb.getAttribute("maxheight"));
  extraH = px-fRH;
 }
 else
 {
  // Calcola altezza extra ricavata dagli oggetti nascosti xche da mostrare solo nell'ultima pagina
  for(var j=0; j < soolp.length; j++)
   extraH+= soolp[j].offsetHeight;
 }


 for(var c=start; c < a['elements'].length; c++)
 {
  var r = tb.insertRow(startAt+c-start);
  for(var i=0; i < tb.rows[0].cells.length; i++)
  {
   var value = "";
   if((a['elements'][c]['type'].toLowerCase() == "note") || (a['elements'][c]['type'].toLowerCase() == "message"))
   {
	if(tb.rows[0].cells[i].id == "name")
	 value = a['elements'][c]["desc"] ? a['elements'][c]["desc"] : "";
	else
	 value = "";
   }
   else
   {
	if(tb.rows[0].cells[i].id == "nr")
	 value = r.rowIndex;
    else if(tb.rows[0].cells[i].id == "discount")
	{
	 value = (a['elements'][c]['listdiscperc'] && (a['elements'][c]['listdiscperc'] > 0)) ? "+"+a['elements'][c]['listdiscperc']+"%" : "";
	 if(a['elements'][c]['discount_perc'] && (a['elements'][c]['discount_perc'] > 0))
	  value+= "+"+a['elements'][c]['discount_perc']+"%";
	 else if(a['elements'][c]['discount_inc'] && (a['elements'][c]['discount_inc'] > 0))
	  value+= "+"+formatCurrency(a['elements'][c]['discount_inc']);
	 if(a['elements'][c]['discount2'] && (a['elements'][c]['discount2'] > 0))
	  value+= "+"+a['elements'][c]['discount2'];
	 if(a['elements'][c]['discount3'] && (a['elements'][c]['discount3'] > 0))
	  value+= "+"+a['elements'][c]['discount3'];
	 if(value)
	  value = value.substr(1);
	 if((a['elements'][c]['listdiscperc'] == "100") || (a['elements'][c]['discount_perc'] == "100") || (a['elements'][c]['discount2'] == "100") || (a['elements'][c]['discount3'] == "100"))
	  value = "100%";
	}
	else if(tb.rows[0].cells[i].id == "image")
	{
	 var imgWidth = tb.rows[0].cells[i].getAttribute('imagewidth') ? tb.rows[0].cells[i].getAttribute('imagewidth') : (tb.rows[0].cells[i].offsetWidth-4)+"px";
	 if(a['elements'][c]['thumb_img'])
	  value = "<img src='"+ABSOLUTE_URL+a['elements'][c]['thumb_img']+"' style='width:"+imgWidth+"'/"+">";
	 else
	  value = "&nbsp;";
	}
	else if(tb.rows[0].cells[i].id == "fulldesc")
	{
	 var description = "";
	 if(a['elements'][c]['code'])
	  description = "cod. "+a['elements'][c]['code']+"<br/"+">";
	 description+= a['elements'][c]['name'];
	 if(a['elements'][c]['desc'])
	  description+= "<br/"+">"+a['elements'][c]['desc'];
	 value = description;
	}
    else if(tb.rows[0].cells[i].id == "units")
    {
	 value = a['elements'][c][tb.rows[0].cells[i].id] ? a['elements'][c][tb.rows[0].cells[i].id] : "";
	 if(!value || (value == ""))
	 {
	  var elType = a['elements'][c]['type'].toLowerCase();
	  if(DEFAULT_UMIS[elType])
	   value = DEFAULT_UMIS[elType];
	 }
    }
	else
	 value = a['elements'][c][tb.rows[0].cells[i].id] ? a['elements'][c][tb.rows[0].cells[i].id] : "";
   }

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
		 if(value.indexOf("+") > 0)
		  cell.innerHTML = value;
		 else if(!parseCurrency(value))
		  cell.innerHTML = "&nbsp;";
		 else if(value.indexOf("%") > 0)
		  cell.innerHTML = value;
		 else
		  cell.innerHTML = formatCurrency(parseCurrency(value),cR.cells[i].getAttribute('decimals') ? parseInt(cR.cells[i].getAttribute('decimals')) : DECIMALS);
		} break;
	  default : {
		 if((a['elements'][c]['type'].toLowerCase() == "note") || (a['elements'][c]['type'].toLowerCase() == "message"))
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
	for(var j=0; j < follows.length; j++)
	 follows[j].innerHTML = "**SEGUE**";

   if(!autoadapted && extraH)
   {
    fRH+= extraH;
	autoadapted = true;

   }
   else
   {
	// nasconde invece tutti gli altri dati da mostrare solo nell'ultima pagina //
	for(var j=0; j < soolp.length; j++)
	 soolp[j].innerHTML = "&nbsp;";

    fRH+= r.offsetHeight;
    tb.deleteRow(r.rowIndex);
    var page = {start:start, elements:elidx, freespace:px2mm(fRH)};
    if(window.parent && (typeof(window.parent.previewMessage) == "function"))
	 window.parent.previewMessage("PAGEBREAK",page);
    completed = false;
    break;
   }
  }

  elidx++;
 }

  if(autoadapted && completed)
  {
   // mette la scritta **SEGUE** nel totale //
   for(var j=0; j < follows.length; j++)
	follows[j].innerHTML = "**SEGUE**";

   // e nasconde tutti gli altri dati da mostrare solo nell'ultima pagina //
   for(var j=0; j < soolp.length; j++)
	soolp[j].innerHTML = "&nbsp;";

   completed = false;
    //fRH+= r.offsetHeight;
    //tb.deleteRow(r.rowIndex);
    var page = {start:start, elements:elidx, freespace:px2mm(fRH)};
    if(window.parent && (typeof(window.parent.previewMessage) == "function"))
	 window.parent.previewMessage("PAGEBREAK",page);
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
	else
	{
     if(tb2.rows[0].cells[i].id == "nr")
	  value = r.rowIndex;
     else if(tb2.rows[0].cells[i].id == "discount")
	 {
	  value = (a['elements'][c]['listdiscperc'] && (a['elements'][c]['listdiscperc'] != "0")) ? "+"+a['elements'][c]['listdiscperc']+"%" : "";
	  if(a['elements'][c]['discount_perc'] && (a['elements'][c]['discount_perc'] != "0"))
	   value+= "+"+a['elements'][c]['discount_perc']+"%";
	  else if(a['elements'][c]['discount_inc'] && (a['elements'][c]['discount_inc'] != "0"))
	   value+= "+"+formatCurrency(a['elements'][c]['discount_inc']);
	  if(a['elements'][c]['discount2'] && (a['elements'][c]['discount2'] != "0"))
	   value+= "+"+a['elements'][c]['discount2'];
	  if(a['elements'][c]['discount3'] && (a['elements'][c]['discount3'] != "0"))
	   value+= "+"+a['elements'][c]['discount3'];
	  if(value)
	   value = value.substr(1);
	  if((a['elements'][c]['listdiscperc'] == "100") || (a['elements'][c]['discount_perc'] == "100") || (a['elements'][c]['discount2'] == "100") || (a['elements'][c]['discount3'] == "100"))
	   value = "100%";
	 }
	 else if(tb2.rows[0].cells[i].id == "image")
	 {
	  if(a['elements'][c]['thumb_img'])
	   value = "<img src='"+ABSOLUTE_URL+a['elements'][c]['thumb_img']+"' style='width:"+(tb.rows[0].cells[i].offsetWidth-4)+"px'/"+">";
	  else
	   value = "&nbsp;";
	 }
	 else if(tb2.rows[0].cells[i].id == "fulldesc")
	 {
	  var description = "";
	  if(a['elements'][c]['code'])
	   description = "cod. "+a['elements'][c]['code']+"<br/"+">";
	  description+= a['elements'][c]['name'];
	  if(a['elements'][c]['desc'])
	   description+= "<br/"+">"+a['elements'][c]['desc'];
	  value = description;
	 }
     else if(tb2.rows[0].cells[i].id == "units")
     {
	  value = a['elements'][c][tb2.rows[0].cells[i].id] ? a['elements'][c][tb2.rows[0].cells[i].id] : "";
	  if(!value || (value == ""))
	  {
	   var elType = a['elements'][c]['type'].toLowerCase();
	   if(DEFAULT_UMIS[elType])
	    value = DEFAULT_UMIS[elType];
	  }
     }
	 else
	  value = a['elements'][c][tb2.rows[0].cells[i].id] ? a['elements'][c][tb2.rows[0].cells[i].id] : "";
	}

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
		 if(value.indexOf("+") > 0)
		  cell.innerHTML = value;
		 else if(!parseCurrency(value))
		  cell.innerHTML = "&nbsp;";
		 else if(value.indexOf("%") > 0)
		  cell.innerHTML = value;
		 else
		  cell.innerHTML = formatCurrency(parseCurrency(value),cR.cells[i].getAttribute('decimals') ? parseInt(cR.cells[i].getAttribute('decimals')) : DECIMALS);
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
  window.parent.previewMessage("PAGEINFO",page,completed);
}

function px2mm(px)
{
 return (px*25.4)/72;
}

function mm2px(mm)
{
 return Math.ceil((mm*72)/25.4);
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
