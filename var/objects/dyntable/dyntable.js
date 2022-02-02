/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-01-2012
 #PACKAGE: dyntable
 #DESCRIPTION: Dynamic Ajax table
 #VERSION: 2.0
 #CHANGELOG: 18-01-2012 : OnSelect event added.
 #TODO:
 
*/

function DynTable(_obj, _options)
{
 this.O = _obj;
 this.options = _options ? _options : {selectable:false,sortable:false,orderable:false};

 /* INJECTS */
 var oThis = this;
 for(var c=0; c < this.O.rows.length; c++)
 {
  var r = this.O.rows[c];
  r.select = function(bool,shotevent){oThis.selectRow(this.rowIndex,bool,shotevent);}
  r.getCell = function(cellname){return oThis.getCell(this,cellname);}
  if(this.options.selectable)
  {
   if(c==0)
   {
    var th = document.createElement('TH');
    th.innerHTML = "<input type='checkbox' onchange='javascript:this.parentNode.parentNode.select(this.checked);'/>";
    r.replaceChild(th,r.insertCell(0));
   }
   else
   {
	r.insertCell(0).innerHTML = "<input type='checkbox' onchange='javascript:this.parentNode.parentNode.select(this.checked);'/>";
	r.cells[0].style.width="1%";
   }
  }
 }

 /* DETECT TH index */
 this.THbyId = new Array();
 for(var c=0; c < this.O.rows[0].cells.length; c++)
 {
  if(this.O.rows[0].cells[c].id)
   this.THbyId[this.O.rows[0].cells[c].id] = this.O.rows[0].cells[c];
 }

 /* OTHER STUFF */
 var oThis = this;
 if(this.options.orderable)
  this._loadJS("var/objects/draganddrop/draganddrop.js",function(){oThis._initializeDragDropMode();});

 /* EVENTS */
 this.OnSelect = null;
}

DynTable.prototype.selectRow = function(rowIndex, bool, shotevent)
{
 var r = this.O.rows[rowIndex];
 r.className = bool ? "selected" : "";
 if(r.cells[0].getElementsByTagName('INPUT').length && r.cells[0].getElementsByTagName('INPUT')[0].checked != bool)
  r.cells[0].getElementsByTagName('INPUT')[0].checked = bool;
 if(rowIndex == 0)
 {
  for(var c=1; c < this.O.rows.length; c++)
   this.O.rows[c].select(bool,false);
 }
 r.setAttribute('selected',bool ? "true" : "false");
 if((shotevent != false) && this.OnSelect)
  this.OnSelect(this.getSelectedRows());
}

DynTable.prototype.getCell = function(r,cellname)
{
 return r.cells[this.THbyId[cellname].cellIndex];
}

DynTable.prototype.showColumn = function(colIdx,bool)
{
 if(bool == false)
  return this.hideColumn(colIdx);
 for(var c=0; c < this.O.rows.length; c++)
  this.O.rows[c].cells[colIdx].style.display='';
}

DynTable.prototype.hideColumn = function(colIdx)
{
 for(var c=0; c < this.O.rows.length; c++)
  this.O.rows[c].cells[colIdx].style.display='none';
}

DynTable.prototype.insertRow = function(pos)
{
 var oThis = this;
 var r = this.O.insertRow(pos);
 for(var c=0; c < this.O.rows[0].cells.length; c++)
 {
  var cll = r.insertCell(-1); cll.innerHTML = "&nbsp;";
  cll.style.display = this.O.rows[0].cells[c].style.display;
  if(this.options.orderable)
  {
   cll.style.MozUserSelect = "none";
   cll.style.KhtmlUserSelect = "none";
  }
 }

 if(this.options.selectable)
 {
  r.cells[0].innerHTML = "<input type='checkbox' onchange='javascript:this.parentNode.parentNode.select(this.checked);'/>";
  r.cells[0].style.width = "1%";
 }
 if(this.options.orderable)
  _setDraggableObject(r,r.cells[0]);

 r.select = function(bool){oThis.selectRow(this.rowIndex,bool);}
 r.getCell = function(cellname){return oThis.getCell(this,cellname);}

 return r;
}

DynTable.prototype.deleteRow = function(rowIdx)
{
 return this.O.deleteRow(rowIdx);
}

DynTable.prototype.getRowById = function(id)
{
 for(var c=1; c < this.O.rows.length; c++)
 {
  if(this.O.rows[c].id == id)
   return this.O.rows[c];
 }
 return null;
}

DynTable.prototype.getSelectedRows = function()
{
 var ret = new Array();
 for(var c=1; c < this.O.rows.length; c++)
 {
  if(this.O.rows[c].getAttribute('selected') == "true")
   ret.push(this.O.rows[c]);
 }
 return ret;
}

DynTable.prototype.empty = function()
{
 while(this.O.rows.length > 1)
  this.deleteRow(1);
 if(this.options.selectable && (this.O.rows[0].getAttribute('selected') == "true"))
  this.selectRow(0,false);
}

DynTable.prototype.serialize = function()
{
 var ret = new Array();
 for(var c=1; c < this.O.rows.length; c++)
 {
  ret.push(this.O.rows[c]);
 }
 return ret;
}

/*-------------------------------------------------------------------------------------------------------------------*/
/*------ PRIVATE FUNCTIONS ------------------------------------------------------------------------------------------*/
/*-------------------------------------------------------------------------------------------------------------------*/

DynTable.prototype._loadJS = function(file,callback)
{
 var headID = document.getElementsByTagName("HEAD")[0];
 var newScript = document.createElement('SCRIPT');
 newScript.type = 'text/javascript';
 if(callback)
  newScript.onload = function(){callback();}
 newScript.src = ABSOLUTE_URL+file;
 headID.appendChild(newScript);
}

DynTable.prototype._initializeDragDropMode = function()
{
 for(var c=1; c < this.O.rows.length; c++)
 {
  var r = this.O.rows[c];
  for(var i=0; i < r.cells.length; i++)
  {
   r.cells[i].style.MozUserSelect = "none";
   r.cells[i].style.KhtmlUserSelect = "none";
  }
  _setDraggableObject(r,r.cells[0]);
 }
}

