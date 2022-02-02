/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-09-2016
 #PACKAGE: simpledraganddrop
 #DESCRIPTION: Simple Drag and Drop functions
 #VERSION: 2.4beta
 #CHANGELOG: 21-09-2016 : Bug fix in function _mouseIntoObject for deleted objects (without parent-node).
			 12-11-2013 : Cambiato SDD_HANDLER in SIMPDD_HANDLER altrimenti andava in conflitto con GOrganizer.
			 11-05-2013 : Risolto famoso problema con le scrollbars.
 #TODO:
 
*/

//-------------------------------------------------------------------------------------------------------------------//
if(!SIMPDD_HANDLER)
{
 document.addEventListener ? document.addEventListener("mousemove",_SDDdocumentMouseMove,false) : document.attachEvent("onmousemove",_SDDdocumentMouseMove);
 document.addEventListener ? document.addEventListener("mousedown",_SDDdocumentMouseDown,false) : document.attachEvent("onmousedown",_SDDdocumentMouseDown);
 document.addEventListener ? document.addEventListener("mouseup",_SDDdocumentMouseUp,false) : document.attachEvent("onmouseup",_SDDdocumentMouseUp);
}
//-------------------------------------------------------------------------------------------------------------------//
function SimpleDragAndDropHandler()
{
 this.SelectedObject = null;

 this.DragObjects = new Array();
 this.DropAreas = new Array();

 /* PRIVATE */
 this._mousedown = false;
 this.mousepos = {x:0,y:0};
 this.hintedDropArea = null;
 this.hintedObject = null;
 this._shadowObject = null;
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleDragAndDropHandler.prototype.setDraggableObject = function(obj, handle, options)
{
 if(!handle)
  handle = obj;
 handle.O = obj;

 obj.handle = handle;

 obj.sdd_dragoptions = {
	 mode: (options && options.mode) ? options.mode : "normal",
	 useshadow : (options && (options.useshadow == false)) ? options.useshadow : true,
	 shadowclassname : (options && options.shadowclassname) ? options.shadowclassname : "simpledraganddrop-shadow"
	}

 obj.setAttribute('sdd_draggableobject', true);

 /* Prevent user selection */
 obj.style.MozUserSelect = "none";
 obj.style.KhtmlUserSelect = "none";
 handle.style.cursor = "move";

 if(typeof(handle.onmousedown) == "function")
  handle.oldonmousedowncallback = handle.onmousedown;

 handle.onmousedown = function(){
	 SIMPDD_HANDLER.selectObject(this.O);
	}

 this.DragObjects.push(obj);
 return obj;
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleDragAndDropHandler.prototype.unsetDraggableObject = function(obj)
{
 var pos = this.DragObjects.indexOf(obj);
 if(pos > -1)
  this.DragObjects.splice(pos,1);
 obj.sdd_dragoptions = null;
 obj.setAttribute('sdd_draggableobject', null);

 if(obj.handle)
 {
  obj.handle.style.cursor = "";
  if(typeof(obj.handle.oldonmousedowncallback) == "function")
   obj.handle.onmousedown = obj.handle.oldonmousedowncallback;
  else
   obj.handle.onmousedown = null;
 }

 if(this.SelectedObject == obj)
  this.SelectedObject = null;
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleDragAndDropHandler.prototype.setDropableContainer = function(obj,options,extra)
{
 /* OPTIONS VARIABLES:
  - dragmode: "vertical-free" or "horizontal-free" or "free" or "normal"
  - verticalsnap: (int)px
  - horizontalsnap: (int)px
 */

 /* EXTRA VARIABLES:
  - name
  - tag
 */

 obj.setAttribute('sdd_dropablecontainer', true);
 obj.sdd_dropoptions = options ? options : {dragmode : "normal"};
 obj.sdd_extra = extra ? extra : null;

 /* Prevent user selection */
 obj.style.MozUserSelect = "none";
 obj.style.KhtmlUserSelect = "none";
 obj.style.webkitUserSelect = "none";

 obj.sdd_serialize = function(){
	 var list = this.getElementsByTagName("*");
	 var ret = new Array();
	 for(var c=0; c < list.length; c++)
	 {
	  var o = list[c];
	  if(!o.getAttribute('sdd_draggableobject'))
	   continue;
	  ret.push(o);
	 }
	 return ret;
	}

 this.DropAreas.push(obj);
 return obj;
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleDragAndDropHandler.prototype.selectObject = function(obj)
{
 if(this.SelectedObject)
 {
  /* TODO: Detach selected object. */
  this._restoreObjectProperties(this.SelectedObject);
  this.draginprogress = false;
 }
 else
 {
  this.SelectedObject = obj;
  this.SelectedObject.ismoved = false;
  this.draginprogress = false;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
SimpleDragAndDropHandler.prototype._onMouseDown = function(ev)
{
 this._mousedown = true;
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleDragAndDropHandler.prototype._onMouseUp = function(ev)
{
 this._mousedown = false;
 this.draginprogress = false;
 if(!this.SelectedObject)
  return;

 /* TODO: effettuare le operazioni di drop qui... */
 if(this._shadowObject && this._shadowObject.parentNode)
 {
  this._shadowObject.parentNode.replaceChild(this.SelectedObject, this._shadowObject);
 }

 /* sgancio l'oggetto e ripristino le sue proprietà */
 this._restoreObjectProperties(this.SelectedObject);

 if(typeof(this.SelectedObject.ondrop) == "function")
  this.SelectedObject.ondrop();

 var droparea = this.SelectedObject.parentNode;
 if(typeof(droparea.ondrop) == "function")
  droparea.ondrop(this.SelectedObject);

 this.SelectedObject = null;
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleDragAndDropHandler.prototype._onMouseMove = function(ev)
{
 this.mousepos = _SDDmouseCoords(ev);
 if(this._mousedown && this.SelectedObject && this.SelectedObject.ismoved) /* MOVE THE OBJECT */
 {
  var newDropArea = this._detectDropArea();
  if(newDropArea != this.hintedDropArea)
  {
   if(this.hintedDropArea && (typeof(this.hintedDropArea.ondragout) == "function"))
	this.hintedDropArea.ondragout();
   this.hintedDropArea = newDropArea;
  }

  if(this.SelectedObject.sdd_dragoptions.mode != "vertical")
   this.SelectedObject.style.left = (this.mousepos.x-this.SelectedObject.sdd_mousediff.x);
  if(this.SelectedObject.sdd_dragoptions.mode != "horizontal")
   this.SelectedObject.style.top = this.mousepos.y-this.SelectedObject.sdd_mousediff.y;

  if(this.hintedDropArea && this._objOverDropArea(this.hintedDropArea))
  {
   /* move the shadow into new drop area */
   if(this._shadowObject && (this._shadowObject.parentNode != this.hintedDropArea))
   {
	var oldpn = this._shadowObject.parentNode;
	/* TODO: aggiustare le dimensioni dello shadow una volta entrato nella nuova drop area */
	if(this.hintedDropArea.innerHTML == "&nbsp;") this.hintedDropArea.innerHTML = "";
	this.hintedDropArea.appendChild(this._shadowObject);
	if(oldpn.innerHTML == "")
	 oldpn.innerHTML = "&nbsp;";
	if(typeof(this.hintedDropArea.ondropshadow) == "function")
	 this.hintedDropArea.ondropshadow(this._shadowObject);
   }
  }

  /* move the shadow up and down through his brothers */
  var activeDropArea = this._shadowObject ? this._shadowObject.parentNode : null;
  if(activeDropArea && (typeof(activeDropArea.sdd_serialize) == "function"))
  {
   var list = activeDropArea.sdd_serialize();
   for(var c=0; c < list.length; c++)
   {
	var obj = list[c];
	var pos = this._mouseIntoObject(obj);
	if(pos)
	{
	 var diffTop = this.mousepos.y - pos.y;
	 var diffBottom = (pos.y+obj.offsetHeight) - this.mousepos.y;
	 if(diffTop < diffBottom)
	  obj.parentNode.insertBefore(this._shadowObject, obj);
	 else if(obj.nextSibling)
	  obj.parentNode.insertBefore(this._shadowObject, obj.nextSibling);
	 else
	  obj.parentNode.appendChild(this._shadowObject);
	}
   }
  }
 } /* EOF - MOVE THE OBJECT */
 else if(!this._mousedown) /* CHECK FOR HINTED OBJECT */
 {
  for(var c=0; c < this.DragObjects.length; c++)
  {
   var pos = this._mouseIntoObject(this.DragObjects[c]);
   if(pos) // the mouse pointer is over the object //
   {
	if(this.DragObjects[c] == this.hintedObject)
	 return;
    if(this.hintedObject && (typeof(this.hintedObject.onunhint) == "function"))
	 this.hintedObject.onunhint();
	this.hintedObject = this.DragObjects[c];
    if(typeof(this.hintedObject.onhint) == "function")
	 this.hintedObject.onhint(pos);
	return;
   }
  }
  if(this.hintedObject && (typeof(this.hintedObject.onunhint) == "function"))
   this.hintedObject.onunhint();
  this.hintedObject = null;
 } /* EOF - CHECK FOR HINTED OBJECT */
 else if(this.SelectedObject)
 {
  if(typeof(this.SelectedObject.ondrag) == "function")
   this.SelectedObject.ondrag();
  this._prepareObjectForDragging(this.SelectedObject);
  this.SelectedObject.ismoved = true;
  this.draginprogress = true;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
SimpleDragAndDropHandler.prototype._prepareObjectForDragging = function(obj)
{
 /* prepara l'oggetto al movimento */
 var w = obj.offsetWidth;
 var h = obj.offsetHeight;
 var sw = _SDDgetStyle(obj,"width");
 var sh = _SDDgetStyle(obj,"height");

 obj.sdd_propbackup = {w:sw,h:sh};
 obj.style.width = w;
 obj.style.height = h;
 var pos = this._getABSobjPos(obj);
 pos.y-= obj.parentNode.scrollTop;
 pos.x-= obj.parentNode.scrollLeft;
 obj.style.position = "absolute";
 obj.style.left = pos.x;
 obj.style.top = pos.y;
 obj.sdd_mousediff = {x:this.mousepos.x-pos.x, y:this.mousepos.y-pos.y}

 /* prepare the shadow object */
 if(obj.sdd_dragoptions.useshadow)
 {
  this._shadowObject = document.createElement('DIV');
  this._shadowObject.className = "simpledraganddrop-shadow";
  this._shadowObject.style.width = obj.style.width;
  this._shadowObject.style.height = obj.style.height;
  obj.parentNode.replaceChild(this._shadowObject,obj);
  document.body.appendChild(obj);
 }

}
//-------------------------------------------------------------------------------------------------------------------//
SimpleDragAndDropHandler.prototype._restoreObjectProperties = function(obj)
{
 /* ripristina le proprietà una volta droppato l'oggetto */
 if(!obj) return;
 obj.style.position = "";
 obj.style.width = (obj.sdd_propbackup && obj.sdd_propbackup.w) ? obj.sdd_propbackup.w : "";
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleDragAndDropHandler.prototype._getABSobjPos = function(obj, refParent)
{
 /* Ritorna la posizione assoluta dell'oggetto a livello del <body> o di refParent, quindi senza contare lo scroll verticale del documento, ma solamente quelli di eventuali div concatenati con overflow: auto.*/

 if(!obj)
  return null;

 var left = obj.offsetLeft;
 var top = obj.offsetTop;

 var e = obj.offsetParent;
 while(e != null)
 {
  if((e.tagName == "BODY") || (e == refParent))
   return {x:left,y:top};
  left+= e.offsetLeft;
  top+= e.offsetTop;
  if(e.scrollLeft)
   left-= e.scrollLeft;
  if(e.scrollTop)
   top-= e.scrollTop;
  e = e.offsetParent;
 }
 return {x:left,y:top};
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleDragAndDropHandler.prototype._detectDropArea = function()
{
 /* Detect drop areas */
 for(var c=0; c < this.DropAreas.length; c++)
 {
  var obj = this.DropAreas[c];
  var pos = this._getABSobjPos(obj);
  if((this.mousepos.x >= pos.x) && (this.mousepos.y >= pos.y) && (this.mousepos.x < pos.x+obj.offsetWidth) && (this.mousepos.y < pos.y+obj.offsetHeight))
  {
   return obj;
  }
 }
 return null;
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleDragAndDropHandler.prototype._objOverDropArea = function(dropArea,obj)
{
 if(!dropArea) return false; if(!obj) var obj = this.SelectedObject; if(!obj) return false;
 var dapos = this._getABSobjPos(dropArea);
 if((this.mousepos.x >= dapos.x) && (this.mousepos.x < dapos.x+dropArea.offsetWidth) && (this.mousepos.y >= dapos.y) && (this.mousepos.y < dapos.y+dropArea.offsetHeight))
 {
  if(typeof(dropArea.ondragover) == "function")
   return dropArea.ondragover(obj);
  else
   return true;
 }
 return false;
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleDragAndDropHandler.prototype._mouseIntoObject = function(obj)
{
 if(!obj || !obj.parentNode) return false;
 var pos = this._getABSobjPos(obj);
 pos.y-= obj.parentNode.scrollTop;
 pos.x-= obj.parentNode.scrollLeft;

 if((this.mousepos.x >= pos.x) && (this.mousepos.x < pos.x+obj.offsetWidth) && (this.mousepos.y >= pos.y) && (this.mousepos.y < pos.y+obj.offsetHeight))
  return pos;
 return false;
}
//-------------------------------------------------------------------------------------------------------------------//
function _SDDdocumentMouseMove(ev)
{
 ev = ev || window.event;
 SIMPDD_HANDLER._onMouseMove(ev);
}
//-------------------------------------------------------------------------------------------------------------------//
function _SDDdocumentMouseDown(ev)
{
 ev = ev || window.event;
 SIMPDD_HANDLER._onMouseDown(ev);
}
//-------------------------------------------------------------------------------------------------------------------//
function _SDDdocumentMouseUp(ev)
{
 ev = ev || window.event;
 SIMPDD_HANDLER._onMouseUp(ev);
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function _SDDmouseCoords(ev)
{
 if(ev.pageX || ev.pageY)
  return {x:ev.pageX, y:ev.pageY}; 
 return {x:ev.clientX + document.body.scrollLeft - document.body.clientLeft, y:ev.clientY + document.body.scrollTop  - document.body.clientTop};
}
//-------------------------------------------------------------------------------------------------------------------//
function _SDDgetObjectPosition(e)
{
 if(!e)
  return null;
 /* DO NOT TOUCH THIS CODE */
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
function _SDDgetStyle(el,styleProp)
{
 if(el.currentStyle)
  return el.currentStyle[styleProp];
 else if (window.getComputedStyle)
  return document.defaultView.getComputedStyle(el,null).getPropertyValue(styleProp);
 return null;
}

//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
if(!SIMPDD_HANDLER)
 var SIMPDD_HANDLER = new SimpleDragAndDropHandler();
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//

