/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 03-10-2013
 #PACKAGE: gmutable
 #DESCRIPTION: Gnujiko Multi Utility table
 #VERSION: 2.7beta
 #CHANGELOG: 03-10-2013 : Bug fix vari. Aggiunto ACTIVE_GMUTABLE per problemi con più tabelle.
			 04-09-2013 : Aggiunto funzione disableSearch su field.
			 30-05-2013 : ClipBoard included.
			 21-05-2013 : Aggiunto variabile gmutable all'elemento obj per piu facile integrazione.
			 21-01-2013 : Bug fix in showHideColumn.
			 15-01-2013 : Aggiunto format date.
 #TODO:
 
*/
var ACTIVE_GMUTABLE = null;
//-------------------------------------------------------------------------------------------------------------------//
function GMUTableDynlaunch(ap,id)
{
 var sh = new GShell();
 sh.sendCommand("dynlaunch -ap '"+ap+"' -id '"+id+"'");
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function GMUTable(obj,options)
{
 this.O = obj;
 this.O.gmutable = this;
 this.Fields = new Array();
 this.FieldByName = new Array();

 /* OPTIONS */
 this.Options = options ? options : {
	 autoresize: true,
	 autoaddrows : true
	}

 /* PRIVATE */
 this._lastEditCell = null;
 this._lastEditCellEDObj = null;
 this.__hintedRow = null;
 this.__moveableRow = null;
 

 /* EVENTS */
 this.OnBeforeAddRow = null;
 this.OnAddRow = null;
 this.OnBeforeDeleteRow = null;
 this.OnDeleteRow = null;
 this.OnSelectRow = null;
 this.OnUnselectRow = null;
 this.OnBeforeCellEdit = null; /* OnBeforeCellEdit(rowObj, cellObj, value) */
 this.OnCellEdit = null; /* OnCellEdit(rowObj, cellObj, value) */
 this.OnRowMove = null;

 this.init();
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.init = function()
{
 // PREPARE HEADER //
 var r = this.O.rows[0];
 for(var c=0; c < r.cells.length; c++)
 {
  var cell = r.cells[c];
  if(cell.id)
  {
   var field = {
	 name:cell.id, 
	 orderable: cell.getAttribute('orderable') ? true : false, 
	 editable: cell.getAttribute('editable') ? true : false,
	 O:cell,
	 enableSearch : function(startQry,endQry,retValField,retTxtField,retArrName,focus,outValField,onQueryResultsCallback){
		 this.spSQ = startQry;
		 this.spEQ = endQry;
		 this.spRVF = retValField;
		 this.spRTF = retTxtField;
		 this.spRAN = retArrName;
		 this.spFoc = focus;
		 this.spOVF = outValField;
		 this.spOQRC = onQueryResultsCallback;
		 this.searchEnabled = true;
		},
	 disableSearch : function(){
		 this.searchEnabled = false;
		}
	}
   this.Fields.push(field);
   this.FieldByName[cell.id] = field;
  }

  if(this.Options.autoresize)
  {
   if(cell.getAttribute('minwidth'))
   {
    var minWidth = parseFloat(cell.getAttribute('minwidth'));
    if(cell.offsetWidth < minWidth)
    {
	 cell.style.width = minWidth;
    }
   }

   if(cell.getAttribute('width'))
   {
    var w = parseFloat(cell.getAttribute('width'));
    if(cell.offsetWidth > w)
    {
	 for(var j=1; j < this.O.rows.length; j++)
	 {
	  var span = this.O.rows[j].cells[c].getElementsByTagName('SPAN');
	  if(span.length)
	   span[0].style.width = w-22;
	 }
	 cell.style.width = w;
    }
   }
  }

  if(cell.style.display == "none")
   this.hideColumn(c);
 }
 
 if(this.Options.autoresize)
  this.autoResize();

 GnujikoKeyCapture.registerListener(this);
 GnujikoMouseCapture.registerListener(this,this.O);

 if(this.O.rows.length < 2)
  return;
 
 var oThis = this;

 for(var c=1; c < this.O.rows.length; c++)
  this.injectRow(this.O.rows[c]);

 this.O.onmouseover = function(){this.focused=true;}
 this.O.onmouseout = function(){
	 if(this.editmode)
	  return;
	 this.focused=false;
	 ACTIVE_GMUTABLE = null;
	}
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.injectRow = function(r)
{
 var oThis = this;
 r.cell = new Array();
 for(var c=0; c < r.cells.length; c++)
 {
  r.cells[c].tag = this.O.rows[0].cells[c].id;

  r.cells[c].onclick = function(){oThis.editCell(this);}

  r.cells[c].setValue = function(value){
	 switch(oThis.O.rows[0].cells[this.cellIndex].getAttribute('format'))
	 {
	  case 'currency percentage' : {
		 if((typeof(value) == "number") || (value.indexOf("%") < 0))
		  value = formatCurrency(parseFloat(value), oThis.O.rows[0].cells[this.cellIndex].getAttribute('decimals') ? parseFloat(oThis.O.rows[0].cells[this.cellIndex].getAttribute('decimals')) : 2);
		} break;
	  case 'currency' : value = formatCurrency(parseCurrency(value), oThis.O.rows[0].cells[this.cellIndex].getAttribute('decimals') ? parseFloat(oThis.O.rows[0].cells[this.cellIndex].getAttribute('decimals')) : 2); break;
	  case 'number' : value = formatNumber(parseFloat(value), oThis.O.rows[0].cells[this.cellIndex].getAttribute('decimals') ? parseFloat(oThis.O.rows[0].cells[this.cellIndex].getAttribute('decimals')) : 2); break;
	  case 'percentage' : value = value ? parseFloat(value)+"%" : "0%"; break;
	  case 'date' : {
		 if(value)
		 {
		  var tmpDate = new Date();
		  tmpDate.setFromISO(strdatetime_to_iso(value));
		  value = tmpDate.printf('d/m/Y');
		 }
		} break;
	 }

	 if(oThis.O.rows[0].cells[this.cellIndex].getAttribute('autolink'))
	  value = "<a href='#' onclick=\"GMUTableDynlaunch('"+oThis.O.rows[0].cells[this.cellIndex].getAttribute('autolink')+"','"+r.id+"')\">"+value+"</a>";

	 if(this.getElementsByTagName('SPAN').length)
	  this.getElementsByTagName('SPAN')[0].innerHTML = value;
	 else
	  this.innerHTML = value;
	 if((oThis._lastEditCell == this) && oThis._lastEditCellEDObj)
	  oThis._lastEditCellEDObj.value = value;
	}

  r.cells[c].getValue = function(){
	 if(this.getElementsByTagName('SPAN').length)
	  return this.getElementsByTagName('SPAN')[0].innerHTML;
	 else
	  return this.innerHTML;
	}

  if(this.O.rows[0].cells[c].id)
   r.cell[this.O.rows[0].cells[c].id] = r.cells[c];
 }

 if(r.cells[0].getElementsByTagName('INPUT').length)
  r.cells[0].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.select(this.checked);}

 r.select = function(bool){
	 if(bool == false)
	  this.className = this.className.replace("selected","");
	 else if(!this.selected)
	  this.className = this.className+" selected";

	 this.selected = bool;
	 if(this.cells[0].getElementsByTagName('INPUT').length)
	  this.cells[0].getElementsByTagName('INPUT')[0].checked = (bool == false) ? false : true;
	 if(bool && oThis.OnSelectRow)
	  oThis.OnSelectRow(this);
	 else if(!bool && oThis.OnUnselectRow)
	  oThis.OnUnselectRow(this);
	}

 r.isVoid = function(){
	 for(var c=0; c < this.cells.length; c++)
	 {
	  if((c == 0) && this.cells[c].getElementsByTagName('INPUT').length && (this.cells[c].getElementsByTagName('INPUT')[0].type == "checkbox"))
	   continue;
	  if(this.cells[c].getElementsByTagName('SPAN').length)
	  {
	   var span = this.cells[c].getElementsByTagName('SPAN')[0];
	   if((span.innerHTML == "") || (span.innerHTML == "&nbsp;"))
		continue;
	   else
		return false;
	  }
	  if(this.cells[c].innerHTML == "&nbsp;")
	   continue;
	  else
	   return false;
	 }
	 return true;
	}

 r.remove = function(){oThis.DeleteRow(this.rowIndex);}

 r.edit = function(){
	 /* Edit first editable and visibled cell */
     for(var c=0; c < this.cells.length; c++)
	 {
	  var cell = this.cells[c];
	  if(oThis.cellIsEditable(cell))
	  {
	   oThis.editCell(cell);
	   return true;
	  }
     }
	 return false;
	}

 r.onmousedown = function(){
	 this.className = this.className+" move";
	 oThis.__moveableRow = this;
	}

 r.onmouseup = function(){
	 this.className = this.className.replace(" move","");
	}
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.OnKeyEvent = function(metakey,keyup)
{
 if(ACTIVE_GMUTABLE != this)
  return;

 switch(metakey)
 {
  case 'TAB' : {
	 if(this._lastEditCell)
	 {
	  var r = this._lastEditCell.parentNode;
	  for(var c = this._lastEditCell.cellIndex+1; c < r.cells.length; c++)
	  {
	   var cell = r.cells[c];
	   if(this.cellIsEditable(cell))
	   {
		this.editCell(cell);
		return false;
	   }
	  }
	  if(this.O.rows.length > (r.rowIndex+1))
	  {
	   while(r = this.O.rows[r.rowIndex+1])
	   {
		for(var c=0; c < r.cells.length; c++)
		{
	     var cell = r.cells[c];
	     if(this.cellIsEditable(cell))
	     {
		  this.editCell(cell);
		  return false;
	     }
		}
	   }
	  }
	  else if(this.Options.autoaddrows)
	  {
	   /* Create new row */
	   if(this.O.rows[this.O.rows.length-1].isVoid())
		return false;
	   var r = this.AddRow();
	   r.edit();
	   return false;
	  }
	 }
	} break;

  case 'ESC' : {
	 if(this._lastEditCell && this._lastEditCellEDObj)
	  this._lastEditCellEDObj.close(false);
	 return false;
	} break;

  case 'UP' : {
	 if(this._lockUpDown)
	  return false;
	 if(this._lastEditCell)
	 {
	  var r = this._lastEditCell.parentNode;
	  if(r.rowIndex > 1)
	  {
	   var r = this.O.rows[r.rowIndex-1];
	   this.editCell(r.cells[this._lastEditCell.cellIndex]);
	  }
	  // remove last empty row //
	  if((this.O.rows.length > 2) && this.O.rows[this.O.rows.length-1].isVoid())
	   this.O.rows[this.O.rows.length-1].remove();
	 }
	 return false;
	} break;

  case 'DOWN' : {
	 if(this._lockUpDown)
	  return false;
	 if(this._lastEditCell)
	 {
	  var r = this._lastEditCell.parentNode;
	  if(r.rowIndex < (this.O.rows.length-1))
	  {
	   var r = this.O.rows[r.rowIndex+1];
	   this.editCell(r.cells[this._lastEditCell.cellIndex]);
	  }
	  else
	  {
	   if((this.O.rows.length > 1) && this.O.rows[this.O.rows.length-1].isVoid())
		return false;
	   if(this.Options.autoaddrows)
	   {
	    /* Create new blank row */
	    var r = this.AddRow();
	    for(var c=0; c < r.cells.length; c++)
	    {
	     var cell = r.cells[c];
	     if(this.cellIsEditable(cell))
	     {
		  this.editCell(cell);
		  return false;
	     }
	    }
	   }

	  }
	 }
	 return false;
	} break;

 }
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.OnMouseDown = function(event,ret)
{
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.OnMouseUp = function(event,ret)
{
 if(this.__moveableRow)
 {
  this.__moveableRow.className = this.__moveableRow.className.replace(" move","");
  var retR = this.__moveableRow;
  this.__moveableRow = null;
  if(this.OnRowMove)
   this.OnRowMove(retR);
 }
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.OnMouseMove = function(event,ret)
{
 if(!this.O.rows.length)
  return;

 var y = 0;
 startAt = 0;
 this.__hintedRow = null;

 if(this.O.rows[0].cells[0].tagName == "TH")
 {
  startAt=1;
  y+= this.O.rows[0].offsetHeight;
 }
 

 for(var c=startAt; c < this.O.rows.length; c++)
 {
  if(ret['mousey'] < 0) {this.__hintedRow = startAt ? (this.O.rows[1] ? this.O.rows[1] : null) : this.O.rows[0]; break;}
  if((ret['mousey'] >= y) && (ret['mousey'] < (y+this.O.rows[c].offsetHeight))) {this.__hintedRow = this.O.rows[c]; break;}
  y+= this.O.rows[c].offsetHeight;
 }

 if(this.__moveableRow && this.__hintedRow)
 {
  this.__moveableRow.parentNode.insertBefore(this.__moveableRow, this.__hintedRow);
 }
 
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.hideColumn = function(colIdx)
{
 for(var c=0; c < this.O.rows.length; c++)
 {
  if(this.O.rows[c].cells[colIdx])
  {
   if(this.O.rows[c].cells[colIdx].colSpan > 1) // evita di nascondere le righe di nota.
	continue;
   this.O.rows[c].cells[colIdx].style.display='none';
  }
 }
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.showColumn = function(colIdx, bool)
{
 for(var c=0; c < this.O.rows.length; c++)
 {
  if(this.O.rows[c].cells[colIdx])
   this.O.rows[c].cells[colIdx].style.display= (bool == false) ? "none" : "";
 }
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.showHideColumn = function(fieldName, show)
{
 var colIdx = this.FieldByName[fieldName].O.cellIndex;
 var obj = this.FieldByName[fieldName].O;
 for(var c=0; c < this.O.rows.length; c++)
 {
  if(this.O.rows[c].cells[colIdx])
  {
   if(this.O.rows[c].cells[colIdx].colSpan > 1) // evita di nascondere le righe di nota.
	continue;
   this.O.rows[c].cells[colIdx].style.display = show ? "" : "none";
  }
 }

 this.autoResize();
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.autoResize = function()
{
 var tableWidth=0;
 for(var c=0; c < this.O.rows[0].cells.length; c++)
 {
  var cell = this.O.rows[0].cells[c];
  if(cell.style.display != "none")
  {
   if(cell.getAttribute('minwidth'))
   {
	tableWidth+= parseFloat(cell.getAttribute('minwidth'));
   }
   else if(cell.getAttribute('width'))
   {
	tableWidth+= parseFloat(cell.getAttribute('width'));
   }
  }
 }
 this.O.style.width = tableWidth < this.O.parentNode.offsetWidth ? "100%" : tableWidth;
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.cellIsEditable = function(cellObj)
{
 if(!cellObj)
  return false;
 if(cellObj.style.display == "none")
  return false;
 var fN = this.O.rows[0].cells[cellObj.cellIndex].id;
 if(!fN) return false;
 if(this.FieldByName[fN].editable) return true;
 return false;
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.editCell = function(cellObj)
{
 if(!this.cellIsEditable(cellObj))
  return;

 if(this.OnBeforeCellEdit)
  this.OnBeforeCellEdit(cellObj.parentNode, cellObj, cellObj.getElementsByTagName('SPAN').length ? cellObj.getElementsByTagName('SPAN')[0].innerHTML : cellObj.innerHTML);

 var oThis = this;

 var fN = this.O.rows[0].cells[cellObj.cellIndex].id;
 var field = this.FieldByName[fN];

 var ed = document.createElement('SPAN');
 ed.className = "editinput";
 var edi = document.createElement('SPAN');
 edi.className = "editinput-inner";
 var inp = document.createElement('INPUT');
 inp.type='text';
 inp.style.width = cellObj.offsetWidth-12;
 inp.value = cellObj.getElementsByTagName('SPAN').length ? cellObj.getElementsByTagName('SPAN')[0].innerHTML : cellObj.innerHTML;
 if(inp.value == "&nbsp;")
  inp.value = "";
 inp.defaultValue = inp.value;
 inp.cellObj = cellObj;
 edi.appendChild(inp);
 ed.appendChild(edi);
 ed.style.visibility = "hidden";
 ed.style.left = 1;
 ed.style.top = 1;
 this.O.appendChild(ed);
 ed.style.left = (cellObj.offsetLeft + Math.floor(cellObj.offsetWidth/2)) - Math.floor(ed.offsetWidth/2) - 2;
 ed.style.top = (cellObj.offsetTop + Math.floor(cellObj.offsetHeight/2)) - Math.floor(ed.offsetHeight/2) + 1;
 ed.style.visibility = "visible";
 inp.focus();
 inp.select();
 this.O.focused = true;
 this.O.editmode = true;
 ACTIVE_GMUTABLE = this;

 inp.onchange = function(event){
	 if(this.closed) return;
	 if(!event)
	  return this.close();
	 if(window.event) // IE
	  var keynum = event.keyCode
	 else if(event.which) // Netscape/Firefox/Opera
	  var keynum = event.which;
	 if(keynum == 27)
	  this.close(false);
	 this.close();
	}

 inp.close = function(save){
	 oThis.O.editmode = false;
	 if(this.closed) return;
	 var value = this.value;
	 var defaultValue = this.defaultValue;
	 var m = this.parentNode.parentNode; 
	 m.parentNode.removeChild(m);
	 this.closed = true;
	 if(save == false)
	  return;
	 this.cellObj.setValue(value);
	 if(oThis.OnCellEdit && (value != defaultValue))
	  oThis.OnCellEdit(this.cellObj.parentNode, this.cellObj, value, this.data);
	}

 inp.onblur = function(event){
	 if(this.closed) return;
	 if(!event)
	  this.close();
	 if(window.event) // IE
	  var keynum = event.keyCode
	 else if(event.which) // Netscape/Firefox/Opera
	  var keynum = event.which;
	 if(keynum == 27)
	  this.close(false);
	 this.close();
	}

 if(field.searchEnabled)
 {
  EditSearch.init(inp,field.spSQ,field.spEQ,field.spRVF,field.spRTF,field.spRAN,field.spFoc,field.spOVF,field.spOQRC);
  this._lockUpDown = true;
 }
 else
  this._lockUpDown = false;

 this._lastEditCell = cellObj;
 this._lastEditCellEDObj = inp;
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.selectAll = function(bool)
{
 for(var c=1; c < this.O.rows.length; c++)
 {
  this.O.rows[c].select(bool);
 }
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.GetSelectedRows = function()
{
 var ret = new Array();
 for(var c=1; c < this.O.rows.length; c++)
 {
  if(this.O.rows[c].selected)
   ret.push(this.O.rows[c]);
 }
 return ret;
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.AddRow = function(idx)
{
 var r = this.O.insertRow((idx==null) ? -1 : idx);
 for(var c=0; c < this.O.rows[0].cells.length; c++)
 {
  var cell = r.insertCell(-1);
  if(this.O.rows[0].cells[c].style.display == "none")
   cell.style.display = "none";
  cell.innerHTML = "&nbsp;";
 }

 if(this.OnBeforeAddRow)
  this.OnBeforeAddRow(r);

 this.injectRow(r);

 if(this.OnAddRow)
  this.OnAddRow(r);
 return r;
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.DeleteRow = function(idx)
{
 var r = this.O.rows[idx];
 if(!r)
  return;
 
 if(this.OnBeforeDeleteRow && (this.OnBeforeDeleteRow(r) == false))
  return;

 this.O.deleteRow(r.rowIndex);
 
 if(this.OnDeleteRow)
  this.OnDeleteRow(r);

 return;
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.AddField = function(fieldID, fieldTitle, opt)
{
 var fieldOptions = {
	 orderable: (opt && (opt.orderable == false)) ? false : true,
	 editable: (opt && (opt.editable == false)) ? false : true,
	 format: (opt && opt.format) ? opt.format : "text",
	 decimals: (opt && opt.decimals) ? opt.decimals : 2,
	 width: (opt && opt.width) ? opt.width : 0,
	 minwidth: (opt && opt.minwidth) ? opt.minwidth : 0,
	 autolink: (opt && opt.autolink) ? opt.autolink : "",
	 hidden: (opt && opt.hidden) ? true : false
	}

 var TH = document.createElement('TH');
 TH.innerHTML = fieldTitle;
 TH.id = fieldID;
 TH.setAttribute('orderable',fieldOptions.orderable);
 TH.setAttribute('editable',fieldOptions.editable);
 TH.setAttribute('format',fieldOptions.format);
 TH.setAttribute('decimals',fieldOptions.decimals);
 TH.setAttribute('width',fieldOptions.width);
 TH.setAttribute('minwidth',fieldOptions.minwidth);
 TH.setAttribute('autolink',fieldOptions.autolink);
 if(fieldOptions.hidden)
  TH.style.display = "none";

 this.O.rows[0].appendChild(TH);

 var field = {
	 name:fieldID, 
	 orderable: fieldOptions.orderable, 
	 editable: fieldOptions.editable,
	 O:TH,
	 enableSearch : function(startQry,endQry,retValField,retTxtField,retArrName,focus,outValField,onQueryResultsCallback){
		 this.spSQ = startQry;
		 this.spEQ = endQry;
		 this.spRVF = retValField;
		 this.spRTF = retTxtField;
		 this.spRAN = retArrName;
		 this.spFoc = focus;
		 this.spOVF = outValField;
		 this.spOQRC = onQueryResultsCallback;
		 this.searchEnabled = true;
		}
	}

 this.Fields.push(field);
 this.FieldByName[fieldID] = field;

 for(var c=1; c < this.O.rows.length; c++)
 {
  var cell = this.O.rows[c].insertCell(-1);
  cell.innerHTML = "<span class='graybold'></span>";
  if(fieldOptions.hidden)
   cell.style.display = "none";
  this.injectRow(this.O.rows[c]);
 }

 return field;
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.DeleteField = function(fieldID)
{
 var field = this.FieldByName[fieldID];
 if(!field)
  return;
 
 var idx = field.O.cellIndex;

 for(var c=0; c < this.O.rows.length; c++)
 {
  this.O.rows[c].deleteCell(idx);
  if(c > 0)
   this.injectRow(this.O.rows[c]);
 }

 this.Fields.splice(this.Fields.indexOf(field),1);
 this.FieldByName[fieldID] = null;
}
//-------------------------------------------------------------------------------------------------------------------//


