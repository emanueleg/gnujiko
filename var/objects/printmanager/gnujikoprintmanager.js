/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 06-10-2014
 #PACKAGE: gnujiko-print-manager
 #DESCRIPTION: Gnujiko Print manager
 #VERSION: 2.3beta
 #CHANGELOG: 06-10-2014 : Bug fix.
			 17-09-2014 : Aggiunto formats su funzione exportToExcel
 #DEPENDS:
 #TODO: Modificare alla riga 122 su funzione GnujikoPrintableTable la dimensione a 190mm.
 
*/

//-------------------------------------------------------------------------------------------------------------------//
function GnujikoPrintableDocumentPage(docHinst, title, format, orientation)
{
 this.DOC = docHinst;
 this.title = title ? title : "Untitled";
 this.format = format ? format : this.DOC.defaultPageFormat; /* A4, A5, 200x100, ... */
 this.orientation = orientation ? orientation : this.DOC.defaultPageOrientation; /* P=portrait, L=landscape */

 this.header = this.DOC.defaultPageHeader;
 this.contents = "";
 this.footer = this.DOC.defaultPageFooter;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoPrintableDocumentPage.prototype.setHeader = function(contents)
{
 this.header = contents;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoPrintableDocumentPage.prototype.setContents = function(contents)
{
 this.contents = contents;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoPrintableDocumentPage.prototype.setFooter = function(contents)
{
 this.footer = contents;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function GnujikoPrintableDocument(title)
{
 this.Pages = new Array();
 this.title = title ? title : "Untitled";

 this.defaultPageFormat = "A4";
 this.defaultPageOrientation = "P"; /* P=portrait, L=landscape */
 this.defaultPageHeader = "";
 this.defaultPageFooter = "";

 this.CSS = new Array();
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoPrintableDocument.prototype.addPage = function(title, format)
{
 var page = new GnujikoPrintableDocumentPage(this, title, format);

 this.Pages.push(page);
 return page;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoPrintableDocument.prototype.includeCSS = function(filename){this.CSS.push(filename);}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoPrintableDocument.prototype.setDefaultPageHeader = function(contents)
{
 this.defaultPageHeader = contents;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoPrintableDocument.prototype.setDefaultPageFooter = function(contents)
{
 this.defaultPageFooter = contents;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoPrintableDocument.prototype.printAsHTML = function()
{
 /* TODO: da fare... */
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoPrintableDocument.prototype.printAsPDF = function(fileName, getFile)
{
 if(!fileName)
  var fileName = this.title.replace(" ","_")+".pdf";

 var cmd = "pdf export -o `"+fileName+"` -format '"+this.defaultPageFormat+"' -orientation '"+this.defaultPageOrientation+"'";
 for(var c=0; c < this.CSS.length; c++)
  cmd+= " -css '"+this.CSS[c]+"'";
 for(var c=0; c < this.Pages.length; c++)
  cmd+= " -c `"+"<div style='margin:10mm'>"+(this.Pages[c].header + this.Pages[c].contents + this.Pages[c].footer)+"</div>"+"`";

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnPreOutput = function(){}
 sh.OnOutput = function(o,a){
	 if(getFile) // get the file
	  document.location.href = ABSOLUTE_URL+"getfile.php?file="+a['fullpath'];
	 else // open directly
	  window.open(ABSOLUTE_URL+a['fullpath']);
	}
 sh.sendCommand(cmd);
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function GnujikoPrintableTable(tb, autoInsertAllColumns, autoInsertAllRecords)
{
 this.O = document.createElement('DIV');
 this.O.style.background = "#ffffff";
 this.O.style.position = "absolute";
 this.O.style.border = "1px solid #d8d8d8";
 this.O.style.left = 0;
 this.O.style.top = 0;
 this.O.style.visibility = "hidden";
 this.O.style.width = "190mm"; /* TODO: da modificare */

 document.body.appendChild(this.O);

 this.TB = tb;
 this.Columns = new Array();
 this.Items = new Array();
 
 this.printPreviewContents = new Array();

 if(autoInsertAllColumns)
  this.injectColumns();
 if(autoInsertAllRecords)
  this.injectRows();
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoPrintableTable.prototype.injectColumns = function()
{
 for(var c=0; c < this.TB.rows[0].cells.length; c++)
 {
  var th = this.TB.rows[0].cells[c];
  if(th.getElementsByTagName('INPUT')[0]) // if contain a checkbox, continue...
   continue;
  var column = new Array();
  column['idx'] = c;
  column['title'] = th.textContent;
  column['name'] = th.getAttribute('field') ? th.getAttribute('field') : (th.id ? th.id : "");
  column['format'] = th.getAttribute('format') ? th.getAttribute('format') : "";
  column['width'] = th.offsetWidth;
  column['colwidth'] = th.getAttribute('colwidth') ? th.getAttribute('colwidth') : this.px2mm(th.offsetWidth);
  column['align'] = th.getAttribute('align') ? th.getAttribute('align') : this.getStyle(th,"text-align");
  column['valign'] = th.getAttribute('valign') ? th.getAttribute('valign') : this.getStyle(th,"vertical-align");
  column['noprint'] = (th.getAttribute('noprint') == "true") ? true : false;
  if((th.style.display == "none") || (this.getStyle(th,"display") == "none"))
   column['hidden'] = true;
  this.Columns.push(column);
 }

}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoPrintableTable.prototype.injectRows = function()
{
 for(var c=1; c < this.TB.rows.length; c++)
 {
  var r = this.TB.rows[c];
  var item = new Array();
  for(var i=0; i < this.Columns.length; i++)
  {
   var img = r.cells[this.Columns[i]['idx']].getElementsByTagName('IMG')[0];
   if(img && img.alt)
	item[i] = img.alt;
   else
    item[i] = r.cells[this.Columns[i]['idx']].textContent;
  }
  this.Items.push(item);
 }

}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoPrintableTable.prototype.generatePrintPreview = function(maxHeight,start)
{
 if(!start)
 {
  var start = 0;
  this.printPreviewContents = new Array();
 }

 this.O.innerHTML = "";

 var tb = document.createElement('TABLE');
 tb.className = "printabletable";
 tb.cellSpacing=0; tb.cellPadding=0; tb.border=0;
 this.O.appendChild(tb);

 var fRH = 0;

 /* HEADER */
 var hr = tb.insertRow(-1);
 hr.className = "th";
 for(var c=0; c < this.Columns.length; c++)
 {
  if(this.Columns[c]['noprint'] || (this.TB.getAttribute('noprinthidden') && this.Columns[c]['hidden']))
   continue;
  var cell = hr.insertCell(-1);
  if(this.Columns[c]['colwidth'])
   cell.style.width = this.Columns[c]['colwidth']+"mm";
  cell.innerHTML = this.Columns[c]['title'];
  if(this.Columns[c]['align'])
   cell.style.textAlign=this.Columns[c]['align'];
  if(this.Columns[c]['valign'])
   cell.style.verticalAlign=this.Columns[c]['valign'];
 }
 fRH+= hr.offsetHeight;

 /* ITEMS */
 var cidx = 0;
 for(var c=start; c < this.Items.length; c++)
 {
  var item = this.Items[c];
  var r = tb.insertRow(-1);
  for(var i=0; i < this.Columns.length; i++)
  {
   if(this.Columns[i]['noprint'] || (this.TB.getAttribute('noprinthidden') && this.Columns[i]['hidden']))
    continue;
   var cell = r.insertCell(-1);
   cell.innerHTML = item[i];
  }
  fRH+= r.offsetHeight;
  if(maxHeight && cidx && (this.px2mm(fRH) > maxHeight))
  {
   tb.deleteRow(r.rowIndex);
   this.fixCellsWidth(tb);
   this.printPreviewContents.push(this.O.innerHTML);
   return this.generatePrintPreview(maxHeight, c-1);
  }
  cidx++;
 }

 this.fixCellsWidth(tb); 

 this.printPreviewContents.push(this.O.innerHTML);
 return this.printPreviewContents;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoPrintableTable.prototype.exportToExcel = function(filename, title)
{
 if(!filename && !title)
 {
  title = prompt("Digita un titolo per da assegnare al documento excel");
  if(!title)
   return;
  filename = title.replace(" ","_");
 }

 var formats = "";
 for(var c=0; c < this.Columns.length; c++)
  formats+= ","+(this.Columns[c]['format'] ? this.Columns[c]['format'] : 'string');
 if(formats)
  formats = formats.substr(1);
 
 this.generatePrintPreview();
 var tb = this.O.getElementsByTagName('TABLE')[0];
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 document.location.href = ABSOLUTE_URL+"getfile.php?file="+a['filename'];
	}
 sh.sendCommand("excel write -f `"+filename+"` -title `"+title+"` -htmltable `"+this.O.innerHTML+"` -formats `"+formats+"`");
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoPrintableTable.prototype.px2mm = function(px){return (25.4/96)*px;}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoPrintableTable.prototype.getCellWidth = function(cell){return Math.floor(this.px2mm(parseFloat(this.getStyle(cell,"width"))));}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoPrintableTable.prototype.getStyle = function(el,styleProp)
{
 var x = el;
  if (x.currentStyle)
 var y = x.currentStyle[styleProp];
  else if (window.getComputedStyle)
 var y = document.defaultView.getComputedStyle(x,null).getPropertyValue(styleProp);
 return y;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoPrintableTable.prototype.fixCellsWidth = function(tb)
{
 tb.style.width = "100%";
 for(var c=1; c < tb.rows.length; c++)
 {
  var r = tb.rows[c];
  for(var i=0; i < r.cells.length; i++)
  {
   var cell = r.cells[i];
   cell.innerHTML = "<div style='width:"+this.getCellWidth(cell)+"mm;"+(this.Columns[i]['align'] ? "text-align:"+this.Columns[i]['align'] : "")+"'>"+cell.innerHTML+"</div>";
   tb.rows[0].cells[i].style.width = this.getCellWidth(cell)+"mm";
  }
 }
 tb.style.width = "";
}
//-------------------------------------------------------------------------------------------------------------------//

