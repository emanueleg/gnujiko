/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-05-2016
 #PACKAGE: gmutable
 #DESCRIPTION: Gnujiko Multi Utility table
 #VERSION: 2.29beta
 #CHANGELOG: 24-05-2016 : Bug fix.
			 18-03-2016 : Aggiunta funzione autocalc.
			 15-03-2016 : Abilitata funzione sortable.
			 04-03-2016 : Bug fix dropdown e dimensioni celle.
			 24-11-2015 : Sost. classe input.dropdown con gmutdropdown perchè interferisce con gnujiko-template.
			 02-06-2015 : Bug fix in ExportToExcel.
			 09-04-2015 : Aggiunta opzione orderable.
			 31-03-2015 : Aggiunta funzione GetRowById.
			 18-03-2015 : Bug fix su celle con colspan > 1.
			 06-12-2014 : Aggiunto format checkbox.
			 03-11-2014 : Aggiunto format dropdown per colonne tipo menu a tendina.
			 27-10-2014 : Aggiunto minwidth alla riga 768
			 08-10-2014 : Bug fix su date.
			 26-05-2014 : Bug fix vari.
			 05-03-2014 : Bug fix su set real value.
			 19-02-2014 : Bug fix su setValue currency.
			 14-02-2014 : Bug fix su setValue.
			 12-02-2014 : Bug fix su arrotondamenti. Aggiunto realvalue a tale scopo.
			 05-02-2014 : Bug fix on function injectRow
			 19-12-2013 : Aggiunta funzione EmptyTable
			 03-10-2013 : Bug fix vari. Aggiunto ACTIVE_GMUTABLE per problemi con più tabelle.
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
 obj.gmutable = this;
 this.Fields = new Array();
 this.FieldByName = new Array();
 this.SUMFields = new Array();

 /* OPTIONS */
 this.Options = options ? options : {
	 autoresize: true,
	 autoaddrows : true,
	 orderable : true
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
 this.OnBeforeCellEdit = null; 	// function(rowObj, cellObj, value)
 this.OnCellEdit = null; 		// function(rowObj, cellObj, value)
 this.OnRowMove = null;
 this.OnExport = null; 			// function(data)
 this.OnSort = null;			// function(string fieldId, string method[ASC/DESC])

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
	 sortable: cell.getAttribute('sortable') ? true : false, 
	 editable: cell.getAttribute('editable') ? true : false,
	 format: cell.getAttribute('format') ? cell.getAttribute('format') : '',
	 sum: cell.getAttribute('sum') ? cell.getAttribute('sum') : '',
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
	};
   this.Fields.push(field);
   this.FieldByName[cell.id] = field;
   if(field.sum && (field.sum != ""))
	this.SUMFields.push(field);

   cell.fieldH = field;
   cell.GMUTable = this;

   if(field.sortable)
   {
	cell.style.cursor = "pointer";
	cell.onclick = function(){
		 if(!this.GMUTable.OnSort) return;
		 if(!this.fieldH.sortable) return;
		 if((this.GMUTable._lastSortField == this.fieldH.name) && (this.GMUTable._lastSortMethod == "ASC"))
		 {
		  this.className = this.className.replace(" sortdesc", "") + " sortdesc";
		  this.GMUTable._lastSortMethod = "DESC";
		 }
		 else
		 {
		  if(this.GMUTable._lastSortField)
		  {
		   var lastField = this.GMUTable.FieldByName[this.GMUTable._lastSortField];
		   lastField.O.className = lastField.O.className.replace(" sortasc", "");
		   lastField.O.className = lastField.O.className.replace(" sortdesc", "");
		  }
		  this.className = this.className.replace(" sortasc", "") + " sortasc";
		  this.GMUTable._lastSortMethod = "ASC";
		 }
		 this.GMUTable._lastSortField = this.fieldH.name;
		 this.GMUTable.OnSort(this.fieldH.name, this.GMUTable._lastSortMethod);
		}

   }

  }

  if(this.Options.autoresize)
  {
   if(cell.getAttribute('minwidth'))
   {
    var minWidth = parseFloat(cell.getAttribute('minwidth'));
	cell.style.minWidth = minWidth+"px";
    /*if(cell.offsetWidth < minWidth)
    {
	 cell.style.width = minWidth;
    }*/
   }
   else if(cell.getAttribute('width'))
	cell.style.minWidth = cell.getAttribute('width')+"px";

   if(cell.getAttribute('width') && (cell.colSpan==1))
   {
    var w = parseFloat(cell.getAttribute('width'));
    if(cell.offsetWidth > w)
    {
	 for(var j=1; j < this.O.rows.length; j++)
	 {
	  if(!this.O.rows[j].cells[c])
	   continue;
	  var span = this.O.rows[j].cells[c].getElementsByTagName('SPAN');
	  if(span.length)
	   span[0].style.width = w-22;
	  else
	  {
	   var div = this.O.rows[j].cells[c].getElementsByTagName('DIV');
	   if(div.length)
		div[0].style.width = w-22;
	  }
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

 /* Per prima cosa nascondo tutte le righe di nota sennò possono far sbordare la tabella */
 for(var i=1; i < this.O.rows.length; i++)
 {
  var r = this.O.rows[i];
  for(var c=0; c < r.cells.length; c++)
  {
   if(r.cells[c].colSpan > 1)
   {
    var spanlist = r.cells[c].getElementsByTagName('SPAN');
    if(spanlist.length) spanlist[0].style.display = "none";
   }
  }
 }


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
GMUTable.prototype.setActiveSortField = function(fieldName, sortMethod)
{
 var field = this.FieldByName[fieldName];
 if(!field) return;
 if(!field.sortable) return;
 if(!sortMethod) sortMethod = "ASC";
 
 field.O.className = field.O.className.replace(" sortasc", "");
 field.O.className = field.O.className.replace(" sortdesc", "");

 if(sortMethod.toUpperCase() == "DESC")
  field.O.className = field.O.className + " sortdesc";
 else
  field.O.className = field.O.className + " sortasc";

 this._lastSortMethod = sortMethod.toUpperCase();
 this._lastSortField = fieldName;
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

  r.cells[c].setValue = function(value, realvalue){
	 this.oldValue = this.getValue();
	 if(typeof(value) == 'undefined')
	  value = "";
	 if(value && (typeof(value.replace) == "function"))
	  value = value.replace(/\xA0/,"")
	 if(value && (typeof(value.replace) == "function"))
	  value = value.replace(/\s+/," ");

	 switch(oThis.O.rows[0].cells[this.cellIndex].getAttribute('format'))
	 {
	  case 'currency percentage' : {
		 if(value && (typeof(value.replace) == "function"))
		  value = value.replace(/\u20ac/g, "");
		 if(value && (typeof(value.replace) == "function"))
		  value = value.replace(". ","");
		 if(value == "&nbsp;")
		 {
		  value = "";
		  this.realvalue = value;
		  this.setAttribute('realvalue',value);
		 }
		 else if(!isNaN(value) && ((typeof(value) == "number") || (value.indexOf("%") < 0)))
		 {
		  this.realvalue = parseCurrency(value);
		  this.setAttribute('realvalue',this.realvalue);
		  value = formatCurrency(parseCurrency(value), oThis.O.rows[0].cells[this.cellIndex].getAttribute('decimals') ? parseCurrency(oThis.O.rows[0].cells[this.cellIndex].getAttribute('decimals')) : 2);
		 }
		 else
		 {
		  value = parseFloat(value)+"%";
		  this.realvalue = value;
		  this.setAttribute('realvalue',value);
		 }
		} break;
	  case 'currency' : {
		var val = (value && (typeof(value.replace) == "function")) ? value.replace(/\u20ac/g, "") : "";
		val = val.replace("<em>&euro;</em>","");
		this.realvalue = parseCurrency(val);
		this.setAttribute('realvalue',this.realvalue);
		value = formatCurrency(parseCurrency(val), oThis.O.rows[0].cells[this.cellIndex].getAttribute('decimals') ? parseCurrency(oThis.O.rows[0].cells[this.cellIndex].getAttribute('decimals')) : 2); 
		} break;
	  case 'number' : value = formatNumber(parseCurrency(value)); break;
	  case 'percentage' : value = value ? parseCurrency(value)+"%" : "0%"; break;
	  case 'date' : {
		 if(value)
		 {
		  var tmpDate = new Date();
		  if(value.length < 3)
		   tmpDate.setDate(value);
		  else
		   tmpDate.setFromISO(strdatetime_to_iso(value));
		  value = tmpDate.printf('d/m/Y');
		 }
		} break;
	  case 'time' : {
		 value = timelength_to_str(parse_timelength(value));
		} break;

	  case 'dropdown' : {
		 /* TODO: da fare... */
		} break;

	  case 'checkbox' : {
		 this.getElementsByTagName('INPUT')[0].checked = value ? true : false;
		} break;
	 } /* EOF - SWITCH */

	 if(!isNaN(realvalue))
	 {
	  this.realvalue = realvalue;
	  this.setAttribute('realvalue',realvalue);
	 }	 

	 if(oThis.O.rows[0].cells[this.cellIndex].getAttribute('autolink'))
	  value = "<a href='#' onclick=\"GMUTableDynlaunch('"+oThis.O.rows[0].cells[this.cellIndex].getAttribute('autolink')+"','"+r.id+"')\">"+value+"</a>";

	 if(this.getElementsByTagName('SPAN').length)
	 {
	  if(this.colSpan > 1)
	   this.getElementsByTagName('SPAN')[0].style.width = (this.offsetWidth-20)+"px";
	  this.getElementsByTagName('SPAN')[0].innerHTML = value;
	 }
	 else if(this.getElementsByTagName('INPUT').length)
	  this.getElementsByTagName('INPUT')[0].checked = value ? true : false;
	 else
	  this.innerHTML = value;
	 if((oThis._lastEditCell == this) && oThis._lastEditCellEDObj)
	  oThis._lastEditCellEDObj.value = value;
	} /* EOF - r.cells[c].setValue */

  r.cells[c].getValue = function(){
	 if(this.getElementsByTagName('INPUT').length && (this.getElementsByTagName('INPUT')[0].type == "checkbox"))
	  return (this.getElementsByTagName('INPUT')[0].checked == true) ? true : false;
	 if(!isNaN(this.realvalue) || (typeof(this.realvalue) != "undefined"))
	  return this.realvalue.toString();
	 else if(this.getAttribute('realvalue'))
	  return this.getAttribute('realvalue');
	 else if(this.getElementsByTagName('SPAN').length)
	  return (this.getElementsByTagName('SPAN')[0].innerHTML != "&nbsp;") ? this.getElementsByTagName('SPAN')[0].innerHTML : "";
	 else
	  return (this.innerHTML != "&nbsp;") ? this.innerHTML : "";
	}

  r.cells[c].restoreOldValue = function(){this.setValue(this.oldValue);}

  if(this.O.rows[0].cells[c].getAttribute('format') == "dropdown")
  {
   r.cells[c].setOptions = function(options){
	 if(this.popupmenu && this.popupmenu.parentNode)
	  this.popupmenu.parentNode.removeChild(this.popupmenu);
	 this.popupmenu = document.createElement('UL');
	 this.popupmenu.className = "gmut-popupmenu";
	 this.popupmenu.onmouseover = function(){this.mouseover=true;}
	 this.popupmenu.onmouseout = function(){this.mouseover=false;}

	 switch(typeof(options))
	 {
	  case 'string' : {
		 var list = options.split(",");
		 for(var i=0; i < list.length; i++)
		 {
		  var opt = document.createElement('LI');
		  opt.value = list[i];
		  opt.innerHTML = list[i];
		  this.popupmenu.appendChild(opt);
		 }
		} break;

	  case 'array' : case 'object' : {
		 for(var i=0; i < options.length; i++)
		 {
		  if((typeof(options[i]) == 'array') || (typeof(options[i]) == 'object'))
		  {
		   if(i > 0)
		   {
			// add separator
			var opt = document.createElement('LI');
			opt.className = "separator";
			opt.innerHTML = "&nbsp;";
			this.popupmenu.appendChild(opt);
		   }
		   var list = options[i];
		   for(var j=0; j < list.length; j++)
		   {
		    var opt = document.createElement('LI');
		    opt.value = list[j];
		    opt.innerHTML = list[j];
		    this.popupmenu.appendChild(opt);
		   }
		  }
		  else
		  {
		   var opt = document.createElement('LI');
		   opt.value = options[i];
		   opt.innerHTML = options[i];
		   this.popupmenu.appendChild(opt);
		  }
		 }
		} break;

	  default : {
		 this.popupmenu = null;
		} break;
	 }
	}
  }

  if(this.O.rows[0].cells[c].getAttribute('format') == "checkbox")
  {
   var cb = r.cells[c].getElementsByTagName('INPUT')[0];
   if(cb)
   {
	cb.gmH = oThis;
	cb.cellObj = r.cells[c];
	cb.onchange = function(){
	 	if(this.gmH.OnCellEdit)
		{
	  	 this.gmH.OnCellEdit(this.cellObj.parentNode, this.cellObj, this.checked);
		 this.gmH.autocalc(this.cellObj.parentNode);
		}
	   }
   }
  }

  if(this.O.rows[0].cells[c].id)
   r.cell[this.O.rows[0].cells[c].id] = r.cells[c];

  if(r.cells[c].colSpan > 1)
  {
   /* Ripristino le righe di nota precedentemente nascoste */
   if(r.cells[c].getElementsByTagName('SPAN').length > 0)
   {
    r.cells[c].getElementsByTagName('SPAN')[0].style.width = (r.cells[c].offsetWidth-20)+"px";
	r.cells[c].getElementsByTagName('SPAN')[0].style.display = "";
   }
  }

 } /* EOF - CYCLE C */

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

 r.moveUp = function(){
	 if(this.rowIndex > 1)
	  this.parentNode.insertBefore(this, this.previousSibling);
	}

 r.moveDown = function(){
	 if(this.rowIndex < (this.parentNode.rows.length-1))
	  this.parentNode.insertBefore(this, this.nextSibling);
	}

 r.onmousedown = function(){
	 this.className = this.className+" move";
	 oThis.__moveableRow = this;
	}

 r.onmouseup = function(){
	 this.className = this.className.replace(" move","");
	}

 if(r.className == "selected")
  r.selected = true;
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.OnKeyEvent = function(metakey,keyup,event)
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

 if(!this.Options.orderable)
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
 this.O.style.width = tableWidth < this.O.parentNode.offsetWidth ? "100%" : tableWidth+"px";
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.cellIsEditable = function(cellObj)
{
 if(!cellObj)
  return false;
 if(cellObj.style.display == "none")
  return false;
 if(this.O.rows[0].cells[cellObj.cellIndex].getAttribute('autolink'))
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

 var cellValue = 0;
 if(!isNaN(cellObj.realvalue))
  cellValue = cellObj.realvalue;
 else if(cellObj.getAttribute('realvalue'))
  cellValue = cellObj.getAttribute('realvalue');
 else
  cellValue = cellObj.getElementsByTagName('SPAN').length ? cellObj.getElementsByTagName('SPAN')[0].innerHTML : cellObj.innerHTML; 

 if(this.OnBeforeCellEdit)
  this.OnBeforeCellEdit(cellObj.parentNode, cellObj, cellValue);

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
 inp.value = cellValue;
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
 if(field.format != 'dropdown')
  inp.select();
 this.O.focused = true;
 this.O.editmode = true;
 ACTIVE_GMUTABLE = this;

 if((field.format == 'dropdown') && cellObj.popupmenu)
 {
  inp.className = "gmutdropdown";
  inp.popupmenu = cellObj.popupmenu;
  inp.readonly = true;
  if(!cellObj.popupmenu.parentNode)
   document.body.appendChild(cellObj.popupmenu);
  cellObj.popupmenu.ed = inp;
  cellObj.popupmenu.show = function(){
		 var list = this.getElementsByTagName('LI');
		 if(!list.length) return this.hide();
		 var pos = oThis.getAbsPos(this.ed);
		 var left = pos.x;
		 var top = pos.y+this.ed.offsetHeight;
		 var screenWidth = window.innerWidth ? window.innerWidth : document.body.clientWidth;
		 var screenHeight = window.innerHeight ? window.innerHeight : document.body.clientHeight;

		 var pn = oThis.O.parentNode;
		 if(pn && pn.scrollLeft)
		  left-= pn.scrollLeft;

		 if(this.offsetWidth < this.ed.offsetWidth)
		  this.style.width = (this.ed.offsetWidth-2)+"px";

	     if((left+this.offsetWidth) > screenWidth)
	      left = screenWidth - this.offsetWidth;
		 if((top+this.offsetHeight) > screenHeight)
		  top = pos.y-this.offsetHeight;

		 this.style.left = left+"px";
		 this.style.top = top+"px";
		 this.style.visibility = "visible";

		 for(var c=0; c < list.length; c++)
		 {
		  var li = list[c];
		  if(li.className == "separator")
		   continue;
		  li.ed = this.ed;
		  if(this.ed.getAttribute('retval') == (li.getAttribute('retval') ? li.getAttribute('retval') : li.getAttribute('value')))
		   li.className = "selected";
		  else
		   li.className = "";
		  if(!li.onclick)
		   li.onclick = function(){
			 this.ed.oldValue = this.ed.value;
			 this.ed.oldRetVal = this.ed.getAttribute('retval');
			 this.ed.value = this.textContent;
			 this.ed.setAttribute('retval',this.getAttribute('retval') ? this.getAttribute('retval') : this.getAttribute('value'));
			 this.ed.selectedItem = this;
			 this.ed.close(true);
			 this.ed.popupmenu.hide();
			}
		 }
		}

  cellObj.popupmenu.hide = function(){
		 this.style.visibility="hidden";
		 this.style.left = "0px";
		 this.style.top = "0px";
		}

  inp.onclick = function(){this.popupmenu.show();}
  inp.onkeydown = function(){this.popupmenu.hide();}
  inp.popupmenu.show();
  inp.close = function(save){
	 oThis.O.editmode = false;
	 if(this.closed) return;
	 var value = this.value;
	 var defaultValue = this.defaultValue;
	 var m = this.parentNode.parentNode; 
	 if(m && m.parentNode)
	  m.parentNode.removeChild(m);
	 this.closed = true;
	 if(save == false)
	  return;
	 this.cellObj.setValue(value);
	 if(oThis.OnCellEdit && (value != defaultValue))
	  oThis.OnCellEdit(this.cellObj.parentNode, this.cellObj, value, this.data);
	 oThis.autocalc(this.cellObj.parentNode);
	}
  inp.onblur = function(event){
	 if(!this.popupmenu.mouseover)
	 {
	  this.close(true);
	  this.popupmenu.hide();
	 }
	}
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
 }
 else
 {
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
	 if(m && m.parentNode)
	  m.parentNode.removeChild(m);
	 this.closed = true;
	 if(save == false)
	  return;
	 this.cellObj.setValue(value);
	 if(oThis.OnCellEdit && (value != defaultValue))
	  oThis.OnCellEdit(this.cellObj.parentNode, this.cellObj, value, this.data);
	 oThis.autocalc(this.cellObj.parentNode);
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
 }

 if(field.searchEnabled)
 {
  EditSearch.init(inp,field.spSQ,field.spEQ,field.spRVF,field.spRTF,field.spRAN,field.spFoc,field.spOVF,field.spOQRC);
  inp.OnShowResults = function(resO, O, xy){
	 var screenWidth = window.innerWidth ? window.innerWidth : document.body.clientWidth;
	 var screenHeight = window.innerHeight ? window.innerHeight : document.body.clientHeight;
	 var left = parseFloat(resO.style.left);
	 var top = parseFloat(resO.style.top);
	 var oldTop = top;
	 var pn = oThis.O.parentNode;
	 if(pn && pn.scrollLeft)
	  left-= pn.scrollLeft;

	 if((left+resO.offsetWidth) > screenWidth)
	  left = screenWidth - resO.offsetWidth;
	 if((top+resO.offsetHeight) > screenHeight)
	  top = top-resO.offsetHeight;

	 if(top < 0)
	  top = xy['y']+O.offsetHeight;

     resO.style.left = left+"px";
     resO.style.top = top+"px";	 	 
	}
  this._lockUpDown = true;
 }
 else
  this._lockUpDown = false;

 this._lastEditCell = cellObj;
 this._lastEditCellEDObj = inp;
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.autocalc = function(r)
{
 if(!this.SUMFields.length) return;
 for(var c=0; c < this.SUMFields.length; c++)
 {
  var field = this.SUMFields[c];
  var value = this.makecalc(r, field.sum);
  r.cell[field.name].setValue(value);
 }
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.makecalc = function(r, sum)
{
 var str = sum;
 for(var c=0; c < this.Fields.length; c++)
  str = str.replace(new RegExp(this.Fields[c].name, 'g'), r.cell[this.Fields[c].name].getValue());

 if(!this.FieldByName['sqrt']) 	str = str.replace(new RegExp('sqrt', 'ig'), 'Math.sqrt');
 if(!this.FieldByName['floor']) str = str.replace(new RegExp("floor", 'ig'), "Math.floor");
 if(!this.FieldByName['ceil']) 	str = str.replace(new RegExp("ceil", 'ig'), "Math.ceil");
 if(!this.FieldByName['round']) str = str.replace(new RegExp("round", 'ig'), "roundup");

 return eval(str);
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
GMUTable.prototype.unselectAll = function()
{
 for(var c=1; c < this.O.rows.length; c++)
 {
  this.O.rows[c].select(false);
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
GMUTable.prototype.GetRowById = function(id)
{
 for(var c=1; c < this.O.rows.length; c++)
 {
  if(this.O.rows[c].id == id)
   return this.O.rows[c];
 }
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
GMUTable.prototype.DeleteSelectedRows = function()
{
 var list = this.GetSelectedRows();
 if(!list.length)
  return;
 for(var c=0; c < list.length; c++)
  this.DeleteRow(list[c].rowIndex);
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
 if(fieldOptions.minwidth)
  TH.style.minWidth = fieldOptions.minwidth+"px";
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
GMUTable.prototype.EmptyTable = function()
{
 while(this.O.rows.length > 1)
 {
  this.DeleteRow(1);
 }
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.getAbsPos = function(e)
{
 var left = e.offsetLeft;
 var top  = e.offsetTop;
 var obj = e;
 while(e = e.offsetParent)
 {
  left+= e.offsetLeft-e.scrollLeft;
  top+= e.offsetTop-e.scrollTop;
 }

 while(obj = obj.parentNode)
 {
  left+= obj.scrollLeft ? obj.scrollLeft : 0;
  top+= obj.scrollTop ? obj.scrollTop : 0;
 }

 return {x:left, y:top};
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.saveAsHTMLTable = function()
{
 var html = "<table><tr>";
 for(var c=0; c < this.O.rows[0].cells.length; c++)
 {
  var cell = this.O.rows[0].cells[c];
  html+= "<th"+(cell.getAttribute('width') ? " width="+cell.getAttribute('width') : '')+">";
  html+= (cell.textContent ? cell.textContent : "&nbsp;")+"</th>";
 }
 html+= "</tr>";

 for(var c=1; c < this.O.rows.length; c++)
 {
  var r = this.O.rows[c];
  html+= "<tr>";
  for(var i=0; i < r.cells.length; i++)
  {
   var cell = r.cells[i];
   var value = cell.getValue();
   html+= "<td"+((cell.colSpan > 1) ? " colspan='"+cell.colSpan+"'>" : ">");
   html+= value ? value : "&nbsp;";
   html+= "</td>";
  }
  html+= "</tr>";
 }
 html+= "</table>";
 return html;
}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.loadFromHTMLTable = function(html)
{
 this.EmptyTable();
 while(this.Fields.length)
  this.DeleteField(this.Fields[0].name);

 var div = document.createElement('DIV');
 div.style.position = "absolute";
 div.style.width = "300px";
 div.style.height = "200px";
 div.style.overflow = "hidden";
 div.style.visibility = "hidden";
 div.style.left = "1px";
 div.style.top = "1px";

 document.body.appendChild(div);
 div.innerHTML = html;

 var tblist = div.getElementsByTagName('TABLE');
 if(!tblist[0]) return;

 var tb = tblist[0];
 if(!tb.rows.length) return;

 for(var c=0; c < tb.rows[0].cells.length; c++)
 {
  var cell = tb.rows[0].cells[c];
  this.AddField("column-"+c, cell.textContent, {width:cell.getAttribute('width')});
 }

 for(var c=1; c < tb.rows.length; c++)
 {
  var r = tb.rows[c];
  var _r = this.AddRow();
  for(var i=0; i < r.cells.length; i++)
  {
   var cell = r.cells[i];
   if(cell.colSpan > 1)
	_r.cells[i].colSpan = cell.colSpan;
   _r.cells[i].setValue(cell.textContent);
  }
 }

}
//-------------------------------------------------------------------------------------------------------------------//
GMUTable.prototype.ExportToExcel = function(title, fields, callback)
{
 var oThis = this;
 var fileName = "";
 for(var c=0; c < title.length; c++)
 {
  switch(title.charAt(c))
  {
   case "/" : case "\\" : case " " : case "." : case "?" : case "#" : case "'" : case '"' : case "`" : case "~" : case ">" : case "<" : case "@" : case "&" : case "%" : case "+" : case "*" : fileName+= "-"; break;
   default : fileName+= title.charAt(c); break;
  }
  if(fileName)
   fileName = fileName.replace("--","");
 }
 fileName = fileName.toLowerCase();
 //---------------------------------------------//
 if(!fields)
 {
  var fields = "";
  for(var c=0; c < this.O.rows[0].cells.length; c++)
  {
   var cell = this.O.rows[0].cells[c];
   if(cell.hasAttribute('xlsexport') && (cell.getAttribute('xlsexport') == "false"))
	continue;
   if(cell.id)
	fields+= ","+cell.id;
  }
  if(fields)
   fields = fields.substr(1);
 }
 //----------------------------------------------//
 var fieldList = fields.split(",");
 
 var htmlTable = "<table>";
 htmlTable+= "<tr>";
 for(var c=0; c < fieldList.length; c++)
 {
  var th = this.FieldByName[fieldList[c]].O;
  htmlTable+= "<th";
  switch(th.format)
  {
   case 'currency' : case 'number' : case 'percentage' : case 'date' : htmlTable+= " format='"+th.format+"'";
  }
  htmlTable+= ">"+th.textContent+"</th>";
 }
 htmlTable+= "</tr>";

 for(var c=1; c < this.O.rows.length; c++)
 {
  htmlTable+= "<tr>";
  for(var i=0; i < fieldList.length; i++)
   htmlTable+= "<td>"+this.O.rows[c].cell[fieldList[i]].getValue()+"</td>";
  htmlTable+= "</tr>";
 }
 
 htmlTable+= "</table>";
 //------------------------------------------------//
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(callback)
	  callback(a);
	 else if(oThis.OnExport)
	  oThis.OnExport(a);
	 //document.location.href = ABSOLUTE_URL+"getfile.php?file="+a['filename'];
	}
 sh.sendCommand("gframe -f excel/export -title `"+title+"` -params `file="+fileName+".xlsx` --use-cache-contents -contents `"+htmlTable+"`");

}
//-------------------------------------------------------------------------------------------------------------------//

