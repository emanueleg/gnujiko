/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 03-10-2013
 #PACKAGE: gextendedtable
 #DESCRIPTION: Gnujiko Extended Ajax table
 #VERSION: 2.3beta
 #CHANGELOG: 03-10-2013 : Bug fix in gexttb_getObjectPosition
			 22-04-2012 : Aggiunto variable gexthandle su oggetto tabella.
			 20-02-2012 : Bug fix in function gexttb_getObjectPosition.
			 06-02-2012 : Aggiunta combobox.
			 02-01-2012 : Aggiunta funzione GetRowById.
			 09-01-2012 : Bug fix in Chrome and Opera.
			 09-10-2011 : Aggiunto evento OnBeforeCellEdit
 #TODO:
 
*/
var DOWN_KEYS = new Array();
var ACTIVE_GEXTENDED_TABLE = null;
//-------------------------------------------------------------------------------------------------------------------//
document.addEventListener ? document.addEventListener("keydown",_GExtendedTableKeyDown,false) : document.attachEvent("onkeydown",_GExtendedTableKeyDown);
document.addEventListener ? document.addEventListener("keyup",_GExtendedTableKeyUp,false) : document.attachEvent("onkeyup",_GExtendedTableKeyUp);
if(navigator.userAgent.indexOf("Firefox") > 0)
 document.addEventListener ? document.addEventListener("keypress",_GExtendedTableKeyPress,false) : document.attachEvent("onkeypress",_GExtendedTableKeyPress);
//-------------------------------------------------------------------------------------------------------------------//
function _GExtendedTableKeyDown(event)
{
 if(window.event) // IE
  keynum = event.keyCode
 else if(event.which) // Netscape/Firefox/Opera
  keynum = event.which;
 keychar = String.fromCharCode(keynum);
 switch(keynum)
 {
  case 16 : DOWN_KEYS['shift'] = true; break;
  case 17 : DOWN_KEYS['ctrl'] = true; break;
  case 18 : DOWN_KEYS['alt'] = true; break;
  default : {
	 if(ACTIVE_GEXTENDED_TABLE && ACTIVE_GEXTENDED_TABLE.editMode)
	 {
	  if((keynum == 38) || (keynum == 40))
  	   ACTIVE_GEXTENDED_TABLE.parseKeyEvents(keynum, keychar, event);
	 }
	 else if(navigator.userAgent.indexOf("Firefox") < 0)
	  _GExtendedTableKeyPress(event);
	} break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function _GExtendedTableKeyUp(event)
{
 if(window.event) // IE
  keynum = event.keyCode
 else if(event.which) // Netscape/Firefox/Opera
  keynum = event.which;
 keychar = String.fromCharCode(keynum);
 switch(keynum)
 {
  case 16 : DOWN_KEYS['shift'] = false; break;
  case 17 : DOWN_KEYS['ctrl'] = false; break;
  case 18 : DOWN_KEYS['alt'] = false; break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function _GExtendedTableKeyPress(event)
{
 if(window.event) // IE
  keynum = event.keyCode
 else if(event.which) // Netscape/Firefox/Opera
  keynum = event.which;
 keychar = String.fromCharCode(keynum);
 if(ACTIVE_GEXTENDED_TABLE)
  ACTIVE_GEXTENDED_TABLE.parseKeyEvents(keynum, keychar, event);
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function GExtendedTable(_obj, _options)
{
 this.O = _obj;
 if(!_options)
  var _options = new Object();
 this.options = {
	 selectable: _options.selectable ? _options.selectable : true,
	 sortable: _options.sortable ? _options.sortable : false,
	 sortableColumns: _options.sortableColumns ? _options.sortableColumns : new Array(), // You can choice fields that can be sortable (by specify the cellIndex of the field). If this array is void, all fields can be sortable.
	 orderable: _options.orderable ? _options.orderable : false,
	 autoAddRow: _options.autoAddRow ? _options.autoAddRow : false,
	 leaveLastRowEmpty: _options.leaveLastRowEmpty ? _options.leaveLastRowEmpty : false
	};


 this.Fields = new Array();

 this.SelectedRows = new Array();
 this.lastRowSelected = null;
 this.selectedCell = null;
 this.lastFieldSelected = null;

 /* Configuration */
 this.autoupdateQryStart = 0;
 this.autoupdateQryLimit = 10;
 this.plusminusBtnPos = 0;


 /* EVENTS */
 this.OnNewRow = null; // OnNewRow(<tr> rowObj) //
 this.OnDeleteRow = null; // OnDeleteRow(Array removedRows) //
 this.OnRowMove = null; // OnRowMove(<tr> rowObj) //
 this.OnSelectRow = null; // OnSelectRow(Array selectedRows) //
 this.OnCellEdit = null; // OnCellEdit(<td> cellObj) //
 this.OnUpdateRequest = null; // OnUpdateRequest(start,limit,orderBy,callbackFunc) //

 if(this.O)
 {
  this.O.gexthandle = this;
  this._injectTable();
 }
}
//-------------------------------------------------------------------------------------------------------------------//
//-- PRIVATE FUNCTIONS ----------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype._injectTable = function()
{
 if(!this.O.rows.length)
  return;
 
 this.Fields = new Array();

 
 /* INJECT FIELDS */
 for(var c=0; c < this.O.rows[0].cells.length; c++)
 {
  var field = this.O.rows[0].cells[c];
  this._injectField(field);
  this.Fields.push(field);
 }
 /* EOF - INJECT FIELDS */

 /* INJECT ROWS */
 for(var c=1; c < this.O.rows.length; c++)
 {
  var r = this.O.rows[c];
  this._injectRow(r);
  // to be continue ... //

 }
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype._injectField = function(field)
{
 var oThis = this;
 field.options = {
	 editable : false,
	 format : {type: "string", mode: ""},
	 comboitems : new Array(),
	};

 field.onclick = function(){oThis.SortBy(this.cellIndex);}

 /* FIELD EVENTS */
 field.OnBeforeCellEdit = null; // OnBeforeCellEdit(<tr> rowObj, <td> cellObj, <input> editorObj) //
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype._injectRow = function(r)
{
 var oThis = this;
 r.mastertable = this;
 r.parent = null;
 r.levelIdx = 0;
 r.onclick = r.select = function(){oThis._selectRow(this.rowIndex); if(this.isCategory) this.className = "selected category";}
 r.isVoid = function(){
	 for(var c=0; c < this.cells.length; c++)
	 {
	  if(this.cells[c].innerHTML != "&nbsp;")
	  {
	   if(oThis.activeEditor && (oThis.activeEditor.cell == this.cells[c]))
	   {
		if(oThis.activeEditor.value)
		 return false;
	   }
	   else
		return false;
	  }
	 }
	 return true;
	}
 r.setAsCategory = function(){
	 this.isCategory = true;
	 this.className = "category";
	 var rThis = this;
	 var img = document.createElement('IMG');
	 img.src = ABSOLUTE_URL+"var/objects/gextendedtable/themes/default/img/expand.gif";
	 img.className = "expand-button";
	 r.cells[this.mastertable.plusminusBtnPos].insertBefore(img,r.cells[this.mastertable.plusminusBtnPos].firstChild);
	 img.onclick = function(){rThis.preload();}
	 r.expandcollapseButton = img;
	 if(img.parentNode.style.paddingLeft)
	  img.parentNode.style.paddingLeft = parseFloat(img.parentNode.style.paddingLeft)-13;
	 /* Properties */
	 this.rows = new Array();

	 /* Functions */
	 this.preload = function(){
		 var rThis = this;
		 this.expandcollapseButton.src = ABSOLUTE_URL+"var/objects/gextendedtable/themes/default/img/collapse.gif";
		 this.expandcollapseButton.onclick = function(){rThis.collapse();}
		 this.expanded = true;
		 if(this.OnPreload)
		  this.OnPreload();
		}
	 this.expand = function(){
		 var rThis = this;
		 this.expandcollapseButton.src = ABSOLUTE_URL+"var/objects/gextendedtable/themes/default/img/collapse.gif";
		 this.expandcollapseButton.onclick = function(){rThis.collapse();}

		 for(var c=0; c < this.rows.length; c++)
		  this.rows[c].style.display='';

		 this.expanded = true;
		 if(this.OnExpand)
		  this.OnExpand();
		}
	 this.collapse = function(){
		 if(this.expanded && this.rows.length)
		 {
		  for(var c=0; c < this.rows.length; c++)
		  {
		   if(this.rows[c].collapse)
			this.rows[c].collapse();
		   this.rows[c].style.display='none';
		  }
		 }
		 var rThis = this;
		 this.expandcollapseButton.src = ABSOLUTE_URL+"var/objects/gextendedtable/themes/default/img/expand.gif";
		 this.expandcollapseButton.onclick = function(){rThis.expand();}
		 this.expanded = false;
		}
	 this.empty = function(){
		 for(var c=0; c < this.rows.length; c++)
		 {
		  if(this.rows[c].isCategory)
		   this.rows[c].empty();
		  this.mastertable.O.deleteRow(this.rows[c].rowIndex);
		 }
		}
	 this.AddRow = function(emitSignal, data, autoFill){
		 var r = this.mastertable.InsertRow(this.rowIndex+this.rows.length+1, emitSignal, data, autoFill)
		 r.levelIdx = this.levelIdx+1;
		 r.cells[this.mastertable.plusminusBtnPos].style.paddingLeft = (14*r.levelIdx)+(r.isCategory ? 0 : 14);
		 this.rows.push(r);
		 return r;
		}

	 /* Events */
	 this.OnPreload = function(){if(oThis.OnRowPreload) oThis.OnRowPreload(this);}
	 this.OnExpand = function(){if(oThis.OnRowExpand) oThis.OnRowExpand(this);}
	 this.OnCollapse = function(){if(oThis.OnRowCollapse) oThis.OnRowCollapse(this);}
	}

 for(var c=0; c < r.cells.length; c++)
 {
  var cell = r.cells[c];
  if(!cell.innerHTML) cell.innerHTML = "&nbsp;";
  this._injectCell(cell);
 }
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype._injectCell = function(cell)
{
 var oThis = this;
 cell.style.MozUserSelect = "none";
 cell.style.KhtmlUserSelect = "none";
 cell.ondblclick = function(){oThis._editCell(this);}
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype._selectRow = function(rowIndex)
{
 ACTIVE_GEXTENDED_TABLE = this;
 
 var r = this.O.rows[rowIndex];

 // Remove last row if blank //
 if((this.O.rows.length > 1) && (this.O.rows[this.O.rows.length-1] != r) && (this.O.rows[this.O.rows.length-1].isVoid()))
 {
  if(!this.options.leaveLastRowEmpty)
   this.DeleteRow(this.O.rows[this.O.rows.length-1].rowIndex,true);
 }


 if(DOWN_KEYS['shift'] == true)
 {
  if(this.lastRowSelected)
  {
   var from = this.lastRowSelected.rowIndex < r.rowIndex ? this.lastRowSelected : r;
   var to = this.lastRowSelected.rowIndex < r.rowIndex ? r : this.lastRowSelected;
   for(var c = from.rowIndex; c < to.rowIndex; c++)
   {
	var row = this.O.rows[c];
	row.className = row.isCategory ? "categoryselected" : "selected";
	row.selected = true;
	if(this.SelectedRows.indexOf(row) < 0)
	 this.SelectedRows.push(row);
   }
  }
  r.className = r.isCategory ? "categoryselected" : "selected";
  r.selected = true;
  if(this.SelectedRows.indexOf(r) < 0)
   this.SelectedRows.push(r);
  this.lastRowSelected = r;
  if(this.OnSelectRow)
   this.OnSelectRow(this.SelectedRows);
  return;
 }
 else if(!DOWN_KEYS['ctrl'])
 {
  for(var c=0; c < this.SelectedRows.length; c++)
  {
   this.SelectedRows[c].className = this.SelectedRows[c].isCategory ? "category" : "";
   this.SelectedRows[c].selected = false;
  }
  this.SelectedRows = new Array();
  this.lastRowSelected = null;
 }

 if(!r)
  return;

 if(r.selected) // UNSELECT ROW //
 {
  r.className = r.isCategory ? "category" : "";
  r.selected = false;
  this.SelectedRows.splice(this.SelectedRows.indexOf(r),1);
  if(this.SelectedRows.length)
   this.lastRowSelected = this.SelectedRows[this.SelectedRows.length-1];
 }
 else // SELECT ROW //
 {
  r.className = r.isCategory ? "categoryselected" : "selected";
  r.selected = true;
  this.SelectedRows.push(r);
  this.lastRowSelected = r;
 }

 if(this.OnSelectRow)
  this.OnSelectRow(this.SelectedRows);
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype.parseKeyEvents = function(keynum, keychar, event)
{
 if(keynum == 9) // TAB //
  event.preventDefault();
 if(keynum == 33) // PG-UP //
  event.preventDefault();
 if(keynum == 34) // PG-DOWN //
  event.preventDefault();
 if(keynum == 38) // UP //
  event.preventDefault();
 if(keynum == 40) // DOWN //
  event.preventDefault();

 if(this.editMode)
 {
  switch(keynum)
  {
   case 27 : { // ESC //
     this.editMode = false;
     if(this.activeEditor)
	  this.activeEditor.parentNode.removeChild(this.activeEditor);
     this.activeEditor = null;
	} break;
   
   case 9 : { // TAB //
     if(this.selectedCell.cellIndex < (this.lastRowSelected.cells.length-1))
	  this._editCell(this.lastRowSelected.cells[this.selectedCell.cellIndex+1],true);
	 else if(this.lastRowSelected.rowIndex == (this.O.rows.length-1)) // if is last row, add new row //
	 {
	  // Detect if last row is not blank //
	  if(!this.O.rows[this.O.rows.length-1].isVoid())
	  {
	   // Create new row //
	   var r = this.AddRow();
	   this._selectRow(r.rowIndex);
	   this._editCell(r.cells[0],true);
	  }
	 }
	 else if(this.lastRowSelected.rowIndex < this.O.rows.length) // Select next row and edit first cell //
	 {
	  var r = this.O.rows[this.lastRowSelected.rowIndex+1];
	  this._selectRow(r.rowIndex);
	  this._editCell(r.cells[0],true);
	 }
	} break;

   case 38 : { // Arrow UP //
	 var cIdx = this.selectedCell.cellIndex;
	 if(this.lastRowSelected.rowIndex > 1)
	 {
	  this._selectRow(this.lastRowSelected.rowIndex-1);
	  this._editCell(this.lastRowSelected.cells[cIdx]);
	 }
	 // Remove last row if blank //
	 if(this.O.rows[this.O.rows.length-1].isVoid() && !this.options.leaveLastRowEmpty)
	  this.DeleteRow(this.O.rows[this.O.rows.length-1].rowIndex,true);
	} break;

   case 40 : { // Arrow Down //
	 var cIdx = this.selectedCell.cellIndex;
	 if(this.lastRowSelected.rowIndex < (this.O.rows.length-1))
	 {
	  this._selectRow(this.lastRowSelected.rowIndex+1);
	  this._editCell(this.lastRowSelected.cells[cIdx]);
	 }
	 else
	 {
	  // Detect if last row is not blank //
	  if(!this.O.rows[this.O.rows.length-1].isVoid())
	  {
	   // Create new row //
	   var r = this.AddRow(true);
	   this._selectRow(r.rowIndex);
	   this._editCell(r.cells[cIdx]);
	  }
	 }
	} break;
  }
  return;
 }

 if(!this.lastRowSelected)
  return;

 switch(keynum)
 {
  case 38 : { // Arrow UP //
	 if(this.lastRowSelected.rowIndex > 1)
	  this._selectRow(this.lastRowSelected.rowIndex-1);
	 if(this.O.rows[this.O.rows.length-1].isVoid() && !this.options.leaveLastRowEmpty) // Remove last row if blank //
	  this.DeleteRow(this.O.rows[this.O.rows.length-1].rowIndex,true);
	} break;

  case 40 : { // Arrow Down //
	 if(this.lastRowSelected.rowIndex < (this.O.rows.length-1))
	  this._selectRow(this.lastRowSelected.rowIndex+1);
	 else
	 {
	  // Detect if last row is not blank //
	  if(!this.O.rows[this.O.rows.length-1].isVoid())
	  {
	   // Create new row //
	   var r = this.AddRow(true);
	   this._selectRow(r.rowIndex);
	  }
	 }
	} break;

  case 36 : { // Home //
	 if(this.O.rows.length > 1)
	  this._selectRow(1);
	} break;

  case 35 : { // End //
	 if(this.O.rows.length > 1)
	  this._selectRow(this.O.rows.length-1);
	} break;

  case 9 : { // TAB //
	 this._editCell(this.lastRowSelected.cells[0]);
	} break;

  case 45 : { // INS //
	 // Insert new row //
	 var r = this.InsertRow(this.lastRowSelected.rowIndex,true);
	 this._selectRow(r.rowIndex);
	 this._editCell(r.cells[0],true);
	} break;

  case 46 : { // CANC //
	 // Delete row //
	 var idx = this.lastRowSelected.rowIndex;
	 var a = new Array();
	 for(var c=0; c < this.SelectedRows.length; c++)
	 {
	  if(!this.SelectedRows[c].isVoid())
	   a.push(this.SelectedRows[c]);
	 }
	 
	 if(a.length && this.OnDeleteRow && (this.OnDeleteRow(a) == false))
	  return;

	 for(var c=0; c < a.length; c++)
	  this.DeleteRow(a[c].rowIndex);
	 this.lastRowSelected = null;
	 if(this.O.rows[idx])
	  this._selectRow(idx);
	 else if(this.O.rows.length > 1)
	  this._selectRow(this.O.rows[1].rowIndex);
	} break;

  case 33 : { // PG-UP //
	 if(this.lastRowSelected.rowIndex > 1)
	 {
	  this.lastRowSelected.parentNode.insertBefore(this.lastRowSelected,this.O.rows[this.lastRowSelected.rowIndex-1]);
	  if(this.OnRowMove)
	   this.OnRowMove(this.lastRowSelected);
	 }
	} break;

  case 34 : { // PG-DOWN //
	 if(this.lastRowSelected.rowIndex < (this.O.rows.length-1))
	 {
	  this.lastRowSelected.parentNode.insertBefore(this.O.rows[this.lastRowSelected.rowIndex+1], this.lastRowSelected);
	  if(this.OnRowMove)
	   this.OnRowMove(this.lastRowSelected);
	 }
	} break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype._editCell = function(cell,skip)
{
 var oThis = this;

 if(this.editMode && (this.selectedCell == cell))
  return;

 if(!this.Fields[cell.cellIndex].options.editable || (this.O.rows[0].cells[cell.cellIndex].style.display == "none"))
 {
  if(skip) // select next cell //
  if(this.lastRowSelected.cells.length > (cell.cellIndex+1))
   this._editCell(this.lastRowSelected.cells[cell.cellIndex+1],true);
  return;
 }

 if(this.editMode)
 {
  if(this.activeEditor)
  {
   if(this.activeEditor.exit)
	this.activeEditor.exit(true);
   else
    this.activeEditor.parentNode.removeChild(this.activeEditor);
  }
  this.activeEditor = null;
 }

 this.editMode = true;
 this.selectedCell = cell;

 if(this.Fields[cell.cellIndex].options.format.type == "text")
 {
  var ed = document.createElement("TEXTAREA");
  ed.className = "edit";
 }
 else if(this.Fields[cell.cellIndex].options.format.type == "combobox")
 {
  var ed = document.createElement('SELECT');
  for(var c=0; c < this.Fields[cell.cellIndex].options.comboitems.length; c++)
  {
   var opt = document.createElement('OPTION');
   opt.innerHTML = this.Fields[cell.cellIndex].options.comboitems[c]['name'];
   opt.value = this.Fields[cell.cellIndex].options.comboitems[c]['value'];
   ed.appendChild(opt);
   if(cell.getAttribute('value') == opt.value)
	ed.selectedIndex = c;
  }
  ed.className = "edit";
 }
 else
 {
  var ed = document.createElement('INPUT');
  ed.type = "text";
  ed.className = "edit";
 }
 ed.style.width = cell.offsetWidth-2;
 if(this.Fields[cell.cellIndex].options.format.type != "combobox")
  ed.style.height = cell.offsetHeight-1;

 var pos = gexttb_getObjectPosition(cell);
 ed.style.left = pos.x;
 ed.style.top = pos.y;
 ed.style.position = "absolute";

 var fc = cell.firstChild;
 cell.contents = "";
 if(fc.nodeType != 3)
 {
  for(var i=1; i < cell.childNodes.length; i++)
  {
   if(cell.childNodes[i].nodeType == 3)
   {
	cell.contents = cell.childNodes[i].nodeValue;
	cell._setContentsN = cell.childNodes[i];
	cell.setContents = function(cnts){this._setContentsN.nodeValue = cnts;}
	break;
   }
  }
 }
 else
  cell.contents = fc.nodeValue;

 ed.value = ed.oldvalue = cell.innerHTML != "&nbsp;" ? html_entity_decode(cell.contents).replace(/<br>/g,"\n") : "";

 ed.cell = cell;
 ed.onchange = ed.onblur = function(){if(!this.onedit) return; this.exit(true);}
 ed.exit = function(applyChanges){
	 oThis.activeEditor = null;
	 oThis.editMode = false;
	 this.onedit = false;
	 if(!applyChanges || (this.value == this.oldvalue))
	 {
	  this.parentNode.removeChild(this);
	  return;
	 }

	 var value = this.value ? oThis._formatValue(this.value,this.cell) : "&nbsp;";
	 if(oThis.SelectedRows.length > 1)
	 {
	  for(var c=0; c < oThis.SelectedRows.length; c++)
	  {
	   if(this.cell.firstChild.nodeType == 3)
	    oThis.SelectedRows[c].cells[this.cell.cellIndex].innerHTML = value;
	  }
	 }
	 else
	 {
	  if(this.cell.setContents)
	   this.cell.setContents(value);
	  else if(this.cell.firstChild.nodeType == 3)
	   this.cell.innerHTML = value;
	 }

	 if(oThis.OnCellEdit)
	  oThis.OnCellEdit(this.cell);
	 if(this.parentNode)
	  this.parentNode.removeChild(this);
	}

 //cell.appendChild(ed);
 cell.editor = ed;
 document.body.appendChild(ed);
 this.activeEditor = ed;
 ed.focus();
 ed.onedit = true;
 
 if(this.Fields[cell.cellIndex].OnBeforeCellEdit)
  this.Fields[cell.cellIndex].OnBeforeCellEdit(cell.parentNode, cell, ed);
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype._formatValue = function(val, cell)
{
 var field = this.Fields[cell.cellIndex];
 switch(field.options.format.type)
 {
  case 'date' : {
	 var d = new Date();
	 var iso = strdatetime_to_iso(val);
	 if(!iso)
	  return "&nbsp;";
	 d.setFromISO(iso);
	 var opt = field.options.format.mode ? field.options.format.mode : "d/m/Y H:i";
	 return d.printf(opt);
	} break;

  case 'currency' : {
	 var decimals = field.options.format.mode ? field.options.format.mode : 2;
	 return formatCurrency(parseCurrency(val),decimals);
	} break;

  case 'number' : {
	 var decimals = field.options.format.mode ? field.options.format.mode : 0;
	 return formatNumber(parseFloat(val.replace(',','.')),decimals);
	} break;

  case 'combobox' : {
	 //var sel = cell.getElementsByTagName('SELECT')[0];
	 var sel = cell.editor;
	 cell.setAttribute('value',val);
	 return sel.options[sel.selectedIndex].innerHTML;
	} break;

  case 'text' : {
	 return val.replace(/\n/g,"<br/>");
	} break;

  default : return val; break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype._autoFillRow = function(r)
{
 for(var f=0; f < r.cells.length; f++)
 {
  var field = this.O.rows[0].cells[f];
  if(field.getAttribute('name'))
  {
	var fName = field.getAttribute('name');
	if(r.data[fName])
	 r.cells[f].innerHTML = r.data[fName];
  }
 }
}
//-------------------------------------------------------------------------------------------------------------------//
//-- PUBLIC FUNCTIONS -----------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype.AddRow = function(emitSignal, data, autoFill)
{
 var r = this.O.insertRow(-1);
 for(var c=0; c < this.Fields.length; c++)
  r.insertCell(-1).style.display = this.O.rows[0].cells[c].style.display;
 this._injectRow(r);
 r.data = data;

 if(autoFill)
  this._autoFillRow(r);

 if(emitSignal && this.OnNewRow)
  this.OnNewRow(r);

 return r;
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype.DeleteRow = function(rowIndex, emitSignal)
{
 if(rowIndex < 0) return;
 if(rowIndex > (this.O.rows.length-1)) return;

 var r = this.O.rows[rowIndex];

 if(emitSignal && this.OnDeleteRow)
 {
  var a = new Array();
  a.push(r);
  if(!r.isVoid())
  {
   if(this.OnDeleteRow(a) == false)
	return;
  }
 }


 if(this.SelectedRows.indexOf(r) > -1) // Remove from selected list //
  this.SelectedRows.splice(this.SelectedRows.indexOf(r),1);

 if(r.isCategory)
  r.empty();
 this.O.deleteRow(r.rowIndex);

 if((this.O.rows.length < 2) && this.options.leaveLastRowEmpty)
  this.AddRow(true); 
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype.InsertRow = function(rowIndex, emitSignal, data, autoFill)
{
 var r = this.O.insertRow(rowIndex);
 for(var c=0; c < this.Fields.length; c++)
  r.insertCell(-1).style.display = this.O.rows[0].cells[c].style.display;
 this._injectRow(r);
 r.data = data;
 
 if(autoFill)
  this._autoFillRow(r);

 if(emitSignal && this.OnNewRow)
  this.OnNewRow(r);

 return r;
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype.GetRowById = function(id)
{
 if(!id) return null;

 for(var c=0; c < this.O.rows.length; c++)
 {
  if(this.O.rows[c].id == id)
   return this.O.rows[c];
 }
 return null;
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype.Clear = function()
{
 while(this.O.rows.length > 1)
  this.O.deleteRow(1);
 this.SelectedRows = new Array();
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype.SortBy = function(fieldIndex, method)
{
 if(!this.options.sortable)
  return;

 var field = this.O.rows[0].cells[fieldIndex];

 if(this.options.sortableColumns.length && (this.options.sortableColumns.indexOf(fieldIndex) < 0))
  return;

 if(this.lastFieldSelected && (this.lastFieldSelected != field))
  this.lastFieldSelected.className = "";

 if(!method)
 {
  if(field.className == "sortasc")
   method = "DESC";
  else
   method = "ASC";
 }

 this.lastFieldSelected = field;
 switch(method.toUpperCase())
 {
  case 'ASC' : field.className = "sortasc"; break;
  case 'DESC' : field.className = "sortdesc"; break;
 }

 if(this.OnUpdateRequest)
  return this.Update(0);

 var a = new Array();
 var d = new Date();
 for(var c=1; c < this.O.rows.length; c++)
 {
  var _row = this.O.rows[c];
  var _val = this.O.rows[c].cells[fieldIndex].innerHTML;
  if(_val == "&nbsp;")
   _val = "";
  if(_val)
  {
   switch(field.options.format.type)
   {
    case 'number' : case 'currency' : _val = parseFloat(_val.replace(',','.')); break;
    case 'date' : {
	   d.setFromISO(strdatetime_to_iso(_val));
	   _val = d.getTime();
	  } break;
    default : _val = _val.toLowerCase(); break;
   }
   a.push({r:_row, value: _val});
  }
 }

 switch(method.toUpperCase())
 {
  case 'ASC' : {
	 switch(field.options.format.type)
	 {
	  case 'number' : case 'currency' : case 'date' : a.sort(function(a,b){return a.value - b.value}); break; // Sort in numerical order //
	  default : a.sort(function(a,b){return (a.value < b.value) ? -1 : 1}); break; // Sort alphabetically //
	 }
	} break;
  case 'DESC' : {
	 switch(field.options.format.type)
	 {
	  case 'number' : case 'currency' : case 'date' : a.sort(function(a,b){return b.value - a.value}); break; // Sort in numerical order //
	  default : a.sort(function(a,b){return (a.value < b.value) ? 1 : -1}); break; // Sort alphabetically //
	 }
	} break;
 }

 for(var c=0; c < a.length; c++)
 {
  var r = a[c].r;
  r.parentNode.insertBefore(r,this.O.rows[c+1]);
 }
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype.MoveRowUp = function(rowIndex)
{
 var row = this.O.rows[(rowIndex > -1) ? rowIndex : this.lastRowSelected.rowIndex];
 if(row.rowIndex > 1)
 {
  row.parentNode.insertBefore(row,this.O.rows[row.rowIndex-1]);
  if(this.OnRowMove)
   this.OnRowMove(row);
 }
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype.MoveRowDown = function(rowIndex)
{
 var row = this.O.rows[(rowIndex > -1) ? rowIndex : this.lastRowSelected.rowIndex];
 if(row.rowIndex < (this.O.rows.length-1))
 {
  row.parentNode.insertBefore(this.O.rows[row.rowIndex+1], row);
  if(this.OnRowMove)
   this.OnRowMove(row);
 }
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype.Update = function(start,limit)
{
 if(!this.OnUpdateRequest)
  return;

 this.autoupdateQryStart = isNaN(start) ? this.autoupdateQryStart : start;
 this.autoupdateQryLimit = limit ? limit : this.autoupdateQryLimit;
 
 var oThis = this;

 var orderBy = null;
 if(this.lastFieldSelected)
  orderBy = this.lastFieldSelected.getAttribute('name')+" "+(this.lastFieldSelected.className == "sortdesc" ? "DESC" : "ASC");

 this.OnUpdateRequest(this.autoupdateQryStart, this.autoupdateQryLimit, orderBy, function(items,count,doesNotClearList){
	 if(!doesNotClearList)
	  oThis.Clear();
	 for(var c=0; c < items.length; c++)
	 {
	  var itm = items[c];
	  var r = oThis.AddRow(true,itm,true);
	  //var r = oThis.AddRow(false, itm);
	  /*for(var f=0; f < oThis.O.rows[0].cells.length; f++)
	  {
	   var field = oThis.O.rows[0].cells[f];
	   if(field.getAttribute('name'))
	   {
		var fName = field.getAttribute('name');
		if(itm[fName])
		 r.cells[f].innerHTML = itm[fName];
	   }
	  }*/
	  // Emit signal //
	  /*if(oThis.OnNewRow)
	   oThis.OnNewRow(r);*/
	 }
	});
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype.ShowColumn = function(colIdx)
{
 if(isNaN(colIdx)) return;
 if(!this.O.rows[0].cells[colIdx]) return;
 for(var c=0; c < this.O.rows.length; c++)
  this.O.rows[c].cells[colIdx].style.display='';
}
//-------------------------------------------------------------------------------------------------------------------//
GExtendedTable.prototype.HideColumn = function(colIdx)
{
 if(isNaN(colIdx)) return;
 if(!this.O.rows[0].cells[colIdx]) return;
 for(var c=0; c < this.O.rows.length; c++)
  this.O.rows[c].cells[colIdx].style.display='none';
}
//-------------------------------------------------------------------------------------------------------------------//
//-- EXTERNAL PRIVATE FUNCTIONS -------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function gexttb_mouseCoords(ev)
{
 if(ev.pageX || ev.pageY)
  return {x:ev.pageX, y:ev.pageY}; 
 return {x:ev.clientX + document.body.scrollLeft - document.body.clientLeft, y:ev.clientY + document.body.scrollTop  - document.body.clientTop};
}
//-------------------------------------------------------------------------------------------------------------------//
function gexttb_getObjectPosition(e)
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
  left-= obj.scrollLeft ? obj.scrollLeft : 0;
  top-= obj.scrollTop ? obj.scrollTop : 0;
 }

 //left+= document.body.scrollLeft;
 //top+= document.body.scrollTop;
 return {x:left, y:top};
}
//-------------------------------------------------------------------------------------------------------------------//
function gexttb_getStyle(el,styleProp)
{
 if(el.currentStyle)
  return el.currentStyle[styleProp];
 else if (window.getComputedStyle)
  return document.defaultView.getComputedStyle(el,null).getPropertyValue(styleProp);
 return null;
}
//-------------------------------------------------------------------------------------------------------------------//
