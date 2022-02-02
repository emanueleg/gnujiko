/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-01-2012
 #PACKAGE: superdraganddrop
 #DESCRIPTION: Basic drag and drop functions
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

//-------------------------------------------------------------------------------------------------------------------//
if(!SDD_HANDLER)
{
 document.addEventListener ? document.addEventListener("mousemove",_SDDdocumentMouseMove,false) : document.attachEvent("onmousemove",_SDDdocumentMouseMove);
 document.addEventListener ? document.addEventListener("mousedown",_SDDdocumentMouseDown,false) : document.attachEvent("onmousedown",_SDDdocumentMouseDown);
 document.addEventListener ? document.addEventListener("mouseup",_SDDdocumentMouseUp,false) : document.attachEvent("onmouseup",_SDDdocumentMouseUp);
}
//-------------------------------------------------------------------------------------------------------------------//
function SuperDragAndDropHandler()
{
 this.selectedObject = null;
 this.selectedObjectClone = null;
 this.selectedObjectDropArea = null;

 this.selectedObjectShadow = null;
 this.selectedObjectShadowDropArea = null;

 this.hintedDragObject = null;
 this.hintedDropContainer = null;
 this.hintedResizeableObject = null;
 
 this.sddHandlers = new Array();

 this.DropAreas = new Array();
 this.DragObjects = new Array();
 this.ResizeableObjects = new Array();

 /* PRIVATE */
 this._oldShadowObjects = new Array();

 /* EVENTS */
 this.OnDragOutFrame = null; 
}
//-------------------------------------------------------------------------------------------------------------------//
SuperDragAndDropHandler.prototype.setDraggableObject = function(obj, handle, options)
{
 if(!handle)
  handle = obj;
 handle.O = obj;
 obj.sdd_dragoptions = options ? options : {mode : "normal"};
 obj.setAttribute('sdd_draggableobject', true);

 /* Prevent user selection */
 obj.style.MozUserSelect = "none";
 obj.style.KhtmlUserSelect = "none";
 handle.style.cursor = "move";

 this.DragObjects.push(obj);
 return obj;
}
//-------------------------------------------------------------------------------------------------------------------//
SuperDragAndDropHandler.prototype.unsetDraggableObject = function(obj)
{
 var pos = this.DragObjects.indexOf(obj);
 if(pos > -1)
  this.DragObjects.splice(pos,1);
 obj.sdd_dragoptions = null;
 obj.setAttribute('sdd_draggableobject', null);
}
//-------------------------------------------------------------------------------------------------------------------//
SuperDragAndDropHandler.prototype.setDropableContainer = function(obj,options,extra)
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
SuperDragAndDropHandler.prototype.interactWith = function(sddHandler)
{
 if(!sddHandler)
  return;
 if(sddHandler == this)
  return;

 if(this.sddHandlers.indexOf(sddHandler) < 0)
 {
  if(this.OnInteract)
   this.OnInteract(sddHandler);
  this.sddHandlers.push(sddHandler);
 }
 if(sddHandler.sddHandlers.indexOf(this) < 0)
 {
  if(sddHandler.OnInteract)
   sddHandler.OnInteract(this);
  sddHandler.sddHandlers.push(this);
 }
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
SuperDragAndDropHandler.prototype.setResizeableObject = function(obj, options)
{
 /* OPTIONS VARIABLES:
  - handles: can be an Array of handles or a single handle. 
			 Accepted values : 'top', 'bottom', 'left', 'right', 'topleft', 'topright', 'bottomleft', 'bottomright', 'all'
  - verticalsnap: (int)px
  - horizontalsnap: (int)px
 */
 obj.sdd_resizeoptions = options ? options : {handles:"all", minwidth: 32, minheight : 32};
 var a = new Array();
 if(typeof(obj.sdd_resizeoptions.handles) == "string")
 {
  if(obj.sdd_resizeoptions.handles == "all")
  {
   a['topleft'] = true;
   a['top'] = true;
   a['topright'] = true;
   a['left'] = true;
   a['right'] = true;
   a['bottomleft'] = true;
   a['bottom'] = true;
   a['bottomright'] = true;
  }
  else
   a[obj.sdd_resizeoptions.handles] = true;
 }
 else
 {
  for(var c=0; c < obj.sdd_resizeoptions.handles.length; c++)
   a[obj.sdd_resizeoptions.handles[c]] = true;
 }
 obj.sdd_resizeoptions.enabledhandles = a;
 obj.setAttribute('sdd_resizeableobject', true);

 this.ResizeableObjects.push(obj);
}
//-------------------------------------------------------------------------------------------------------------------//
SuperDragAndDropHandler.prototype.unsetResizeableObject = function(obj)
{
 obj.sdd_resizeoptions = null;
 obj.setAttribute('sdd_resizeableobject', null);
 if(this.ResizeableObjects.indexOf(obj) > -1)
  this.ResizeableObjects.splice(this.ResizeableObjects.indexOf(obj),1);
}
//-------------------------------------------------------------------------------------------------------------------//
SuperDragAndDropHandler.prototype._onMouseDown = function(ev)
{
 var target = null;
 var mouse = _SDDmouseCoords(ev);

 if(this.hintedResizeableObject)
 {
  var obj = this.hintedResizeableObject;
  var pos = _SDDgetObjectPosition(obj);
  if(this.hintedDropContainer)
  {
   pos.y-= this.hintedDropContainer.scrollTop;
   pos.x-= this.hintedDropContainer.scrollLeft;
   this.hintedDropContainer.sdd_oldScrollTop = this.hintedDropContainer.scrollTop;
   this.hintedDropContainer.sdd_oldScrollLeft = this.hintedDropContainer.scrollLeft;
  }
  if((mouse.x >= pos.x) && (mouse.y >= pos.y) && (mouse.x < pos.x+obj.offsetWidth) && (mouse.y < pos.y+obj.offsetHeight))
   target = obj;
 }
 else if(this.hintedDragObject)
 {
  var obj = this.hintedDragObject;
  var pos = _SDDgetObjectPosition(obj);
  if(this.hintedDropContainer)
  {
   pos.y-= this.hintedDropContainer.scrollTop;
   pos.x-= this.hintedDropContainer.scrollLeft;
   this.hintedDropContainer.sdd_oldScrollTop = this.hintedDropContainer.scrollTop;
   this.hintedDropContainer.sdd_oldScrollLeft = this.hintedDropContainer.scrollLeft;
  }
  if((mouse.x >= pos.x) && (mouse.y >= pos.y) && (mouse.x < pos.x+obj.offsetWidth) && (mouse.y < pos.y+obj.offsetHeight))
   target = obj;
  this.hintedDragObject.sdd_action = "move";
 }

 if(!target)
  return;

 if((target.parentNode != this.hintedDropContainer) && (target.parentNode.parentNode != this.hintedDropContainer))
  return;

 target.sdd_diffX = mouse.x - pos.x;
 target.sdd_diffY = mouse.y - pos.y;
 target.oldpos = _SDDgetObjectPosition(target);
 target.olddim = {w:target.offsetWidth, h:target.offsetHeight};


 this.selectedObject = target;
 this.selectedObjectShadow = target;

 this.selectedObjectClone = null;

 this.selectedObjectDropArea = this.hintedDropContainer;
 this.selectedObjectShadowDropArea = this.hintedDropContainer;
}
//-------------------------------------------------------------------------------------------------------------------//
SuperDragAndDropHandler.prototype._onMouseUp = function(ev)
{
 if(this.selectedObject)
 {
  switch(this.selectedObject.sdd_action)
  {
   case "move" : {
	 if(this.selectedObjectClone)
	  this.selectedObjectClone.parentNode.removeChild(this.selectedObjectClone);
	 this.selectedObjectClone = null;

	 if(this.selectedObjectShadow != this.selectedObject)
	 {
	  if(this.selectedObject.sdd_dragoptions.mode == "normal")
	   this.selectedObject.parentNode.removeChild(this.selectedObject);
	 }
	 this.selectedObjectShadow.style.filter = null;
	 this.selectedObjectShadow.style.opacity = null;

	 for(var c=0; c < this._oldShadowObjects.length; c++)
	 {
	  var obj = this._oldShadowObjects[c];
	  if(obj != this.selectedObjectShadow)
	   continue;
	  if(obj == this.selectedObject)
	   continue;
	  if(obj.parentNode)
	   obj.parentNode.removeChild(obj);
	 }

	 var oldShadow = this.selectedObjectShadow;
	 this.selectedObjectShadow = null;

	 var pos = _SDDgetObjectPosition(this.selectedObject);
	 if(this.selectedObject.oldpos && ((this.selectedObject.oldpos.x != pos.x) || (this.selectedObject.oldpos.y != pos.y)))
	 {
	  if(this.selectedObject.OnMove)
       this.selectedObject.OnMove();
      else if(this.selectedObject.onmove)
       this.selectedObject.onmove();
	 }

	 if(oldShadow && (this.selectedObject != oldShadow))
	 {
	  if(oldShadow.OnMove)
	   oldShadow.OnMove();
	  else if(oldShadow.onmove)
	   oldShadow.onmove();
	 }
	} break;

   case "resize" : {
	 if((this.selectedObject.olddim.w != this.selectedObject.offsetWidth) || (this.selectedObject.olddim.h != this.selectedObject.offsetHeight))
	 {	
	  if(this.selectedObject.OnResize)
       this.selectedObject.OnResize();
      else if(this.selectedObject.onresize)
       this.selectedObject.onresize();
	 }
	} break;
  }
 } /* EOF - if(this.selectedObject) */
 
 if(this.externalDragObject)
 {
  if(this.externalDragObject.parentNode)
   this.externalDragObject.parentNode.removeChild(this.externalDragObject);
  this.externalDragObject = null;
 }
 

 this.selectedObject = null;
 this.selectedObjectShadow = null;
 this.selectedObjectClone = null;

}
//-------------------------------------------------------------------------------------------------------------------//
SuperDragAndDropHandler.prototype._onMouseMove = function(ev)
{
 /* Manage external handlers */
 for(var c=0; c < this.sddHandlers.length; c++)
 {
  var h = this.sddHandlers[c];
  if(h.selectedObject)
  {
   if(h.OnDragOutFrame)
	h.OnDragOutFrame();
   var tmpObj = h.selectedObjectClone.cloneNode(true);
   document.body.appendChild(tmpObj);
   this.externalDragObject = tmpObj;
   if(h.selectedObjectClone.parentNode)
	h.selectedObjectClone.parentNode.removeChild(h.selectedObjectClone);
   h.selectedObject = null;
   h.selectedObjectClone = null;
   h.selectedObjectShadow = null;
  }
  else if(h.externalDragObject)
  {
   if(h.externalDragObject.parentNode)
    h.externalDragObject.parentNode.removeChild(h.externalDragObject);
   h.externalDragObject = null;
  }
 }

 var mousePos = _SDDmouseCoords(ev);
 var hdcWasChange = false;
 var oldHDC = this.hintedDropContainer;
 var oldHDO = this.hintedDragObject;

 this.hintedDropContainer = null;
 this.hintedDragObject = null;
 this.hintedResizeableObject = null;

 /* Detect drop areas */
 for(var c=0; c < this.DropAreas.length; c++)
 {
  var obj = this.DropAreas[c];
  var pos = _SDDgetObjectPosition(obj);
  if((mousePos.x >= pos.x) && (mousePos.y >= pos.y) && (mousePos.x < pos.x+obj.offsetWidth) && (mousePos.y < pos.y+obj.offsetHeight))
  {
   this.hintedDropContainer = obj;
   c = this.DropAreas.length;
  }
 }

 /* Verify if hinted container was change */
 if(this.hintedDropContainer && (oldHDC != this.hintedDropContainer))
 {
  if(oldHDC && oldHDC.OnMouseOut)
   oldHDC.OnMouseOut();
  hdcWasChange = true;
  if(this.hintedDropContainer.OnMouseOver)
   this.hintedDropContainer.OnMouseOver();
 }
 if(!this.hintedDropContainer)
 {
  if(this.selectedObject || this.externalDragObject)
   this.hintedDropContainer = oldHDC;
  else
  {
   if(oldHDC && oldHDC.OnMouseOut)
	oldHDC.OnMouseOut();
  }
 }

 /* Detect object hinted */
 for(var c=0; c < this.DragObjects.length; c++)
 {
  var obj = this.DragObjects[c];
  if(obj == this.selectedObjectShadow)
   continue;
  var pos = _SDDgetObjectPosition(obj);
  if(this.hintedDropContainer)
  {
   pos.y-= this.hintedDropContainer.scrollTop;
   pos.x-= this.hintedDropContainer.scrollLeft;
  }
  if((mousePos.x >= pos.x) && (mousePos.y >= pos.y) && (mousePos.x < pos.x+obj.offsetWidth) && (mousePos.y < pos.y+obj.offsetHeight))
  {
   this.hintedDragObject = obj;
   c = this.DragObjects.length;
  }
 }

 if(this.hintedDragObject != oldHDO)
 {
  if(oldHDO && oldHDO.OnMouseOut)
   oldHDO.OnMouseOut();
  if(this.hintedDragObject && this.hintedDragObject.OnMouseOver)
   this.hintedDragObject.OnMouseOver();
 }

 if(!this.selectedObject)
 {
  /* Detect resizeable object hinted */
  for(var c=0; c < this.ResizeableObjects.length; c++)
  {
   var obj = this.ResizeableObjects[c];
   var pos = _SDDgetObjectPosition(obj);
   if(this.hintedDropContainer)
   {
    pos.y-= this.hintedDropContainer.scrollTop;
    pos.x-= this.hintedDropContainer.scrollLeft;
   }
   if((mousePos.x >= pos.x) && (mousePos.y >= pos.y) && (mousePos.x < pos.x+obj.offsetWidth) && (mousePos.y < pos.y+obj.offsetHeight))
   {
    this.hintedResizeableObject = obj;
    c = this.ResizeableObjects.length;
   }
  }
 }

 if(this.selectedObject)
 {
  switch(this.selectedObject.sdd_action)
  {
   case 'move' : { // MOVE THE CLONE //

	 if(!this.selectedObjectClone && this.selectedObjectShadow) // CREATE CLONE //
  	 {
   	  this.selectedObjectClone = this.selectedObjectShadow.cloneNode(true);
	  this.selectedObjectClone.style.width = this.selectedObjectShadow.olddim.w;
	  this.selectedObjectClone.style.height = this.selectedObjectShadow.olddim.h;
	  if(this.selectedObject.sdd_dragoptions.mode == "normal")
	  {
	   this.selectedObjectShadow.style.filter="alpha(opacity=30)";
	   this.selectedObjectShadow.style.opacity=0.5;
	  }

      if(this.selectedObjectShadow.tagName == "TR")
      {
       for(var c=0; c < this.selectedObjectShadow.cells.length; c++)
        this.selectedObjectClone.cells[c].style.width = this.selectedObjectShadow.cells[c].offsetWidth;
   	  }
   	  this.selectedObjectClone.style.position = "absolute";
   	  this.selectedObjectClone.style.left = mousePos.x - this.selectedObject.sdd_diffX;
   	  this.selectedObjectClone.style.top = mousePos.y - this.selectedObject.sdd_diffY;
   	  this.selectedObjectShadow.parentNode.appendChild(this.selectedObjectClone);
  	 } // EOF - CREATE CLONE //


	 /* MOVE CLONE */
	 this.selectedObjectClone.style.left = mousePos.x - this.selectedObject.sdd_diffX;
	 this.selectedObjectClone.style.top = mousePos.y - this.selectedObject.sdd_diffY;

	 if(this.selectedObjectShadowDropArea != this.hintedDropContainer)
	 {
	  if(this.selectedObject.sdd_dragoptions.mode == "normal")
	   this.selectedObjectShadow.style.display = "none"; // Hide selected object from source container //
	  if(hdcWasChange && this.hintedDropContainer && this.hintedDropContainer.canDrop) // Verify if new container can drop the selected object //
	  {
	   this.hintedDropContainer.sdd_oldScrollLeft = this.hintedDropContainer.scrollLeft;
	   this.hintedDropContainer.sdd_oldScrollTop = this.hintedDropContainer.scrollTop;
	   var ret = this.hintedDropContainer.canDrop(this.selectedObject, this.selectedObjectClone, this.selectedObjectDropArea);
	   if(ret && (ret != true)) // Exchange the object clone //
	   {
		// Re-create clone //
		this.selectedObjectShadowDropArea = this.hintedDropContainer;
		this.selectedObjectClone.parentNode.removeChild(this.selectedObjectClone);

		this._oldShadowObjects.push(this.selectedObjectShadow);

		this.selectedObjectShadow = ret;
   	    this.selectedObjectClone = this.selectedObjectShadow.cloneNode(true);
		this.selectedObjectShadow.style.filter="alpha(opacity=30)";
		this.selectedObjectShadow.style.opacity=0.3;

        if(this.selectedObjectShadow.tagName == "TR")
        {
         for(var c=0; c < this.selectedObjectShadow.cells.length; c++)
          this.selectedObjectClone.cells[c].style.width = this.selectedObjectShadow.cells[c].offsetWidth;
   	    }
   	    this.selectedObjectClone.style.position = "absolute";
	    this.selectedObjectClone.style.left = mousePos.x - this.selectedObject.sdd_diffX;
	    this.selectedObjectClone.style.top = mousePos.y - this.selectedObject.sdd_diffY;
   	    this.selectedObjectShadow.parentNode.appendChild(this.selectedObjectClone);
		this.selectedObject.sdd_diffX = Math.floor(this.selectedObjectClone.offsetWidth/2);
		this.selectedObject.sdd_diffY = Math.floor(this.selectedObjectClone.offsetHeight/2);
		/* Prevent auto-scroll */
		this.hintedDropContainer.scrollLeft = this.hintedDropContainer.sdd_oldScrollLeft;
		this.hintedDropContainer.scrollTop = this.hintedDropContainer.sdd_oldScrollTop;
	   }
	   else if(ret)
	   {
		this.hintedDropContainer.appendChild(this.selectedObjectShadow);
	   }
	  }
	 }
	 else
	 {
	  /* MOVE THE SHADOW */
	  var pos = _SDDgetObjectPosition(this.hintedDragObject);
	  var top = parseFloat(this.selectedObjectClone.style.top);
	  var objH = parseFloat(this.selectedObjectClone.offsetHeight);
	  if(this.hintedDragObject)
	   var hintH = parseFloat(this.hintedDragObject.offsetHeight);
		
	  switch(this.hintedDropContainer.sdd_dropoptions.dragmode)
	  {
		 case "normal" : {
			 if(this.hintedDragObject)
			 {
			  if((this.hintedDragObject.parentNode == this.selectedObjectShadow.parentNode) && (this.hintedDragObject.sdd_dragoptions.mode == "normal"))
			  {
			   this.selectedObjectShadow.style.position = "";
			   if((top + objH) > (pos.y + hintH)) //go down//
			    this.selectedObjectShadow.parentNode.insertBefore(this.hintedDragObject,this.selectedObjectShadow);
			   else if(top < (pos.y + Math.floor(hintH/2))) // go up //
			    this.selectedObjectShadow.parentNode.insertBefore(this.selectedObjectShadow,this.hintedDragObject);
			  }
			 }
			} break;

		 default : {
			 var dropPos = _SDDgetObjectPosition(this.hintedDropContainer);
			 var y = ((mousePos.y-this.selectedObject.sdd_diffY) - dropPos.y)+this.hintedDropContainer.sdd_oldScrollTop;
			 var x = ((mousePos.x-this.selectedObject.sdd_diffX) - dropPos.x)+this.hintedDropContainer.sdd_oldScrollLeft;
			 if((this.hintedDropContainer.sdd_dropoptions.dragmode == "free") || (this.hintedDropContainer.sdd_dropoptions.dragmode == "vertical-free"))
			 {
	 		  var top = this.hintedDropContainer.sdd_dropoptions.verticalsnap ? Math.floor(y/this.hintedDropContainer.sdd_dropoptions.verticalsnap)*this.hintedDropContainer.sdd_dropoptions.verticalsnap : y;
			  //if(this.selectedObject.sdd_dragoptions.mode == "normal")
	 		   this.selectedObjectShadow.style.top = top;
			  this.selectedObjectClone.style.left = x;
			  this.selectedObjectClone.style.top = y;
			 }
			 if((this.hintedDropContainer.sdd_dropoptions.dragmode == "free") || (this.hintedDropContainer.sdd_dropoptions.dragmode == "horizontal-free"))
			 {
			  //if(this.selectedObject.sdd_dragoptions.mode == "normal")
			   this.selectedObjectShadow.style.left = this.hintedDropContainer.sdd_dropoptions.horizontalsnap ? Math.floor(x/this.hintedDropContainer.sdd_dropoptions.horizontalsnap)*this.hintedDropContainer.sdd_dropoptions.horizontalsnap : x;
			 }
			 /* Prevent auto-scroll */
			 if(this.hintedDropContainer.sdd_oldScrollLeft)
			  this.hintedDropContainer.scrollLeft = this.hintedDropContainer.sdd_oldScrollLeft;
			 if(this.hintedDropContainer.sdd_oldScrollTop)
			  this.hintedDropContainer.scrollTop = this.hintedDropContainer.sdd_oldScrollTop;
			 /* Shot event */
			 if(this.selectedObjectShadow.duringmove)
			  this.selectedObjectShadow.duringmove();
			} break;
		} /* EOF - SWITCH */
	  } /* EOF - MOVE THE SHADOW */
	} break;

   case 'resize' : {
	 var pos = _SDDgetObjectPosition(this.selectedObject);
	 if(!this.hintedDropContainer)
	  break;
	 switch(this.hintedDropContainer.sdd_dropoptions.dragmode)
	 {
	  default : {
		 var dropPos = _SDDgetObjectPosition(this.hintedDropContainer);
		 var y = ((mousePos.y-this.selectedObject.sdd_diffY) - dropPos.y)+this.hintedDropContainer.scrollTop;
		 var x = ((mousePos.x-this.selectedObject.sdd_diffX) - dropPos.x)+this.hintedDropContainer.scrollLeft;
		 if((this.hintedDropContainer.sdd_dropoptions.dragmode == "free") || (this.hintedDropContainer.sdd_dropoptions.dragmode == "vertical-free"))
		 {
	 	  var top = this.hintedDropContainer.sdd_dropoptions.verticalsnap ? Math.floor(y/this.hintedDropContainer.sdd_dropoptions.verticalsnap)*this.hintedDropContainer.sdd_dropoptions.verticalsnap : y;
		  var left = this.hintedDropContainer.sdd_dropoptions.horizontalsnap ? Math.floor(x/this.hintedDropContainer.sdd_dropoptions.horizontalsnap)*this.hintedDropContainer.sdd_dropoptions.horizontalsnap : x;

		  switch(this.selectedObject.getAttribute('resizedir'))
		  {
		   case "topleft" : case "top" : case "topright" : {
			 if(top != parseFloat(this.selectedObject.style.top))
			 {
			  var y = (mousePos.y + this.hintedDropContainer.scrollTop);
			  var h = this.selectedObject.style.height ? this.selectedObject.style.height : _SDDgetStyle(this.selectedObject,"height");
			  if(!h) h = this.selectedObject.offsetHeight;
			  var height = parseFloat(h)+(parseFloat(this.selectedObject.style.top)-top);
			  if(height > this.selectedObject.sdd_resizeoptions.minheight)
			  {
	 	  	   this.selectedObject.style.top = top;
   			   this.selectedObject.style.height = height;
			  }
			 }
			} break;

		   case "bottomleft" : case "bottom" : case "bottomright" : {
			 var h = (mousePos.y + this.hintedDropContainer.scrollTop) - pos.y;
			 var height = this.hintedDropContainer.sdd_dropoptions.verticalsnap ? Math.floor(h/this.hintedDropContainer.sdd_dropoptions.verticalsnap)*this.hintedDropContainer.sdd_dropoptions.verticalsnap : h;
   			 this.selectedObject.style.height = height >= this.selectedObject.sdd_resizeoptions.minheight ? height : this.selectedObject.sdd_resizeoptions.minheight;
			} break;
		  }
		 }
		 if((this.hintedDropContainer.sdd_dropoptions.dragmode == "free") || (this.hintedDropContainer.sdd_dropoptions.dragmode == "horizontal-free"))
		 {
		  switch(this.selectedObject.getAttribute('resizedir'))
		  {
		   case "topleft" : case "left" : case "bottomleft" : {
			 if(left != parseFloat(this.selectedObject.style.left))
			 {
			  var x = (mousePos.x + this.hintedDropContainer.scrollLeft);
			  var w = this.selectedObject.style.width ? this.selectedObject.style.width : _SDDgetStyle(this.selectedObject,"width");
			  if(!w) w = this.selectedObject.offsetWidth;
			  var width = parseFloat(w)+(parseFloat(this.selectedObject.style.left)-left);
			  if(width > this.selectedObject.sdd_resizeoptions.minwidth)
			  {
	 	  	   this.selectedObject.style.left = left;
   			   this.selectedObject.style.width = width >= this.selectedObject.sdd_resizeoptions.minwidth ? width : this.selectedObject.sdd_resizeoptions.minwidth;
			  }
			 }
			} break;

		   case "topright" : case "right" : case "bottomright" : {
			 var w = (mousePos.x + this.hintedDropContainer.scrollLeft) - pos.x;
			 var width = this.hintedDropContainer.sdd_dropoptions.horizontalsnap ? Math.floor(w/this.hintedDropContainer.sdd_dropoptions.horizontalsnap)*this.hintedDropContainer.sdd_dropoptions.horizontalsnap : w;
   			 this.selectedObject.style.width = width >= this.selectedObject.sdd_resizeoptions.minwidth ? width : this.selectedObject.sdd_resizeoptions.minwidth;
			} break;
		  }
		 }
		 if(this.selectedObject.duringresize)
		  this.selectedObject.duringresize();
		} break;
	 }
	} break;
  }
 }
 /* EOF - if(this.selectedObject) */
 else if(this.externalDragObject)
 {
  this.externalDragObject.style.position = "absolute";
  this.externalDragObject.style.left = mousePos.x - Math.floor(this.externalDragObject.offsetWidth/2);
  this.externalDragObject.style.top = mousePos.y - Math.floor(this.externalDragObject.offsetHeight/2);
  
  if(this.hintedDropContainer && this.hintedDropContainer.canDrop) // Verify if new container can drop the selected object //
  {
   this.hintedDropContainer.sdd_oldScrollLeft = this.hintedDropContainer.scrollLeft;
   this.hintedDropContainer.sdd_oldScrollTop = this.hintedDropContainer.scrollTop;

   var ret = this.hintedDropContainer.canDropExt(this.externalDragObject);
   if(ret && (ret != true)) // Exchange the object clone //
   {
	// Re-create clone //
	this.selectedObjectShadowDropArea = this.hintedDropContainer;
	if(this.externalDragObject.parentNode)
	 this.externalDragObject.parentNode.removeChild(this.externalDragObject);	

	this.selectedObject = ret;
	this.selectedObject.sdd_diffX = 0;
	this.selectedObject.sdd_diffY = 0;
    this.selectedObject.sdd_action = "move";
	this.selectedObjectShadow = ret;
	this.selectedObjectShadow.style.filter="alpha(opacity=30)";
	this.selectedObjectShadow.style.opacity=0.3;

	this.selectedObjectClone = null;

	this.selectedObjectDropArea = this.hintedDropContainer;
	this.selectedObjectShadowDropArea = this.hintedDropContainer;
	/* Prevent auto-scroll */
	this.hintedDropContainer.scrollLeft = this.hintedDropContainer.sdd_oldScrollLeft;
	this.hintedDropContainer.scrollTop = this.hintedDropContainer.sdd_oldScrollTop;
   }
   else if(ret)
   {
	// to be continue... //
   }
  }
 }
 /* EOF - if(this.externalDragObject) */
 else
 {
  if(this.hintedResizeableObject)
  {
   var target = this.hintedResizeableObject;
   var pos = _SDDgetObjectPosition(target);
   if(this.hintedDropContainer)
   {
    pos.y-= this.hintedDropContainer.scrollTop;
    pos.x-= this.hintedDropContainer.scrollLeft;
   }
   var cursors = {"top":"n-resize", "bottom":"s-resize", "left":"w-resize", "right":"e-resize", "topleft":"nw-resize", "topright":"ne-resize", "bottomleft":"sw-resize", "bottomright":"se-resize"};
 /* ACTION */
  var z = 3;
  if(mousePos.x < (pos.x+z)) // Left //
  {
   if((mousePos.y < (pos.y+z)) && target.sdd_resizeoptions.enabledhandles['topleft']) // top left //
   {
    target.sdd_action = "resize";
    target.setAttribute('resizedir','topleft');
    target.style.cursor = cursors["topleft"];
   }
   else if((mousePos.y > (pos.y + obj.offsetHeight - z)) && target.sdd_resizeoptions.enabledhandles['bottomleft']) // bottom left //
   {
    target.sdd_action = "resize";
    target.setAttribute('resizedir','bottomleft');
    target.style.cursor = cursors["bottomleft"];
   }
   else if(target.sdd_resizeoptions.enabledhandles['left']) // left //
   {
    target.sdd_action = "resize";
    target.setAttribute('resizedir','left');
    target.style.cursor = cursors["left"];
   }
  }
  else if(mousePos.x > (pos.x + obj.offsetWidth - z)) // Right //
  {
   if((mousePos.y < (pos.y+z)) && target.sdd_resizeoptions.enabledhandles['topright']) // top right //
   {
    target.sdd_action = "resize";
    target.setAttribute('resizedir','topright');
    target.style.cursor = cursors["topright"];
   }
   else if((mousePos.y > (pos.y + obj.offsetHeight - z)) && target.sdd_resizeoptions.enabledhandles['bottomright']) // bottom right //
   {
    target.sdd_action = "resize";
    target.setAttribute('resizedir','bottomright');
    target.style.cursor = cursors["bottomright"];
   }
   else if(target.sdd_resizeoptions.enabledhandles['right']) // right //
   {
    target.sdd_action = "resize";
    target.setAttribute('resizedir','right');
    target.style.cursor = cursors["right"];
   }
  }
  else if((mousePos.y < (pos.y + z)) && target.sdd_resizeoptions.enabledhandles['top']) // top //
  {
   target.sdd_action = "resize";
   target.setAttribute('resizedir','top');
   target.style.cursor = cursors["top"];
  }
  else if((mousePos.y > (pos.y + obj.offsetHeight - z)) && target.sdd_resizeoptions.enabledhandles['bottom']) // bottom //
  {
   target.sdd_action = "resize";
   target.setAttribute('resizedir','bottom');
   target.style.cursor = cursors["bottom"];
  }
  else
  {
   target.sdd_action = "move";
   target.style.cursor = "move";
  }
 }
}
 
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function _SDDdocumentMouseMove(ev)
{
 ev = ev || window.event;
 SDD_HANDLER._onMouseMove(ev);
}
//-------------------------------------------------------------------------------------------------------------------//
function _SDDdocumentMouseDown(ev)
{
 ev = ev || window.event;
 SDD_HANDLER._onMouseDown(ev);
}
//-------------------------------------------------------------------------------------------------------------------//
function _SDDdocumentMouseUp(ev)
{
 ev = ev || window.event;
 SDD_HANDLER._onMouseUp(ev);
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
if(!SDD_HANDLER)
 var SDD_HANDLER = new SuperDragAndDropHandler();
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//

