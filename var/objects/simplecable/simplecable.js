/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 26-02-2013
 #PACKAGE: simplecable
 #DESCRIPTION: Connecting graphical objects by means of cables.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/


//-------------------------------------------------------------------------------------------------------------------//
function SimpleCableHandler()
{
 this.Plugs = new Array();
 this.Cables = new Array();
 this.ActiveCable = null; // ActiveCable è riferito solo ai nuovi cavi, in creazione, quindi ancora disconnessi.
 this.SelectedCable = null; // Questo invece si riferisce al cavo selezionato già collegato.
 this.cablesParent = document.body;

 /* EVENTS */
 this.OnDeleteCable = null; /* function (cable) */

 /* PRIVATE */
 this.mousepos = null;
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCableHandler.prototype.init = function(cablesParent)
{
 var oThis = this;
 if(cablesParent)
  this.cablesParent = cablesParent;
 document.addEventListener ? document.addEventListener("mousemove",SIMPLE_CABLE_HANDLER_MOUSE_MOVE,false) : document.attachEvent("onmousemove",SIMPLE_CABLE_HANDLER_MOUSE_MOVE);
 document.addEventListener ? document.addEventListener("mousedown",SIMPLE_CABLE_HANDLER_MOUSE_DOWN,false) : document.attachEvent("onmousedown",SIMPLE_CABLE_HANDLER_MOUSE_DOWN);
 document.addEventListener ? document.addEventListener("keydown",SIMPLE_CABLE_HANDLER_KEY_DOWN,false) : document.attachEvent("onkeydown",SIMPLE_CABLE_HANDLER_KEY_DOWN);
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCableHandler.prototype.registerPlug = function(plug)
{
 this.Plugs.push(plug);
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCableHandler.prototype.showCables = function()
{
 for(var c=0; c < this.Cables.length; c++)
  this.Cables[c].show();
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCableHandler.prototype.hideCables = function()
{
 for(var c=0; c < this.Cables.length; c++)
  this.Cables[c].hide();
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCableHandler.prototype.selectCable = function(cable)
{
 if(this.SelectedCable)
  this.SelectedCable.unselect();
 this.SelectedCable = null;

 if(!cable) return;

 cable.select();
 this.SelectedCable = cable;
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCableHandler.prototype._onkeydown = function(event)
{
 if(window.event) // IE
  keynum = event.keyCode
 else if(event.which) // Netscape/Firefox/Opera
  keynum = event.which;
 keychar = String.fromCharCode(keynum);
 switch(keynum)
 {
  case 46 : {
	 if(this.SelectedCable)
	 {
      if(this.OnDeleteCable)
       this.OnDeleteCable(this.SelectedCable);
      this.SelectedCable.free();
      this.SelectedCable = null;
	  this.ActiveCable = null;
	 }
	} break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCableHandler.prototype._onmousemove = function(ev)
{
 this.mousepos = this.getMouseCoords(ev);
 
 if(this.ActiveCable && !this.ActiveCable.plugDest)
  this.ActiveCable.update();
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCableHandler.prototype._onmousedown = function(ev)
{
 /* IF ACTIVE CABLE */
 if(this.ActiveCable)
 {
  if(this.ActiveCable.plugSrc && !this.ActiveCable.plugDest)
  {
   var plug = this.getPlugOverMouse();
   if(plug)
   {
	plug.onclick();
	this.ActiveCable = null;
   }
   else
   {
    if(this.OnDeleteCable)
     this.OnDeleteCable(this.ActiveCable);
    this.ActiveCable.free();
    this.ActiveCable = null;
   }
  }
 }
 else /* IF NOT ACTIVE CABLE */
 {
  if(this.SelectedCable) // se c'è un cavo selezionato, lo deseleziona.
  {
   this.selectCable(null);
  }
 }
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCableHandler.prototype.getMouseCoords = function(ev)
{
 if(ev.pageX || ev.pageY)
  var ret = {x:ev.pageX, y:ev.pageY}; 
 else
  var ret = {x:ev.clientX + document.body.scrollLeft - document.body.clientLeft, y:ev.clientY + document.body.scrollTop  - document.body.clientTop};

 var pos = this.getObjectPosition(this.cablesParent,true);
 ret.x-= pos.x;
 ret.y-= pos.y;
 ret.x+= this.cablesParent.scrollLeft;
 ret.y+= this.cablesParent.scrollTop;
 return ret;
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCableHandler.prototype.getPlugOverMouse = function()
{
 for(var c=0; c < this.Plugs.length; c++)
 {
  var plug = this.Plugs[c];
  var pos = this.getObjectPosition(plug);
  if((this.mousepos.x >= pos.x) && (this.mousepos.y >= pos.y) && (this.mousepos.x < pos.x+plug.offsetWidth) && (this.mousepos.y < pos.y+plug.offsetHeight))
   return plug;
 }
 return null;
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCableHandler.prototype.getObjectPosition = function(obj, absolute)
{
 /* Ritorna la posizione assoluta dell'oggetto a livello del <body>, quindi senza contare lo scroll verticale del documento, ma solamente quelli di eventuali div concatenati con overflow: auto.*/

 if(!obj)
  return null;

 var left = obj.offsetLeft;
 var top = obj.offsetTop;

 var e = obj.offsetParent;
 while(e != null)
 {
  if((e.tagName == "BODY") || ((e == this.cablesParent) && !absolute))
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
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//---- S I M P L E   C A B L E --------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function SimpleCable(plugSrc, objSrc, plugDest, objDest, color, plugTmp, corrX, corrY)
{
 this.plugSrc = plugSrc;
 this.objSrc = objSrc;
 this.objSrcPos = null;
 this.plugDest = plugDest;
 this.objDest = objDest;
 this.objDestPos = null;
 this.corrX = corrX;
 this.corrY = corrY;
 this.plugTmp = plugTmp; // è il plug temporaneo fissato alla fine del cavo quando questo è disconnesso

 this.segments = new Array();
 this.color = color ? color : "blue";
 this.className = "simplecable-"+this.color;
 this.thickness = 4;

 SIMPLE_CABLE_HANDLER.Cables.push(this);
 SIMPLE_CABLE_HANDLER.ActiveCable = this;

 if(this.plugTmp)
  this.plugTmp.style.position = "absolute";

 if(objSrc) // get source object position
  this.objSrcPos = SIMPLE_CABLE_HANDLER.getObjectPosition(objSrc);
 if(objDest) // get destination object position
  this.objDestPos = SIMPLE_CABLE_HANDLER.getObjectPosition(objDest);


 this.update();
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCable.prototype.update = function(_idx)
{
 var oThis = this;
 var idx = _idx ? _idx : 0;
 var lastdir = this.plugSrc.getAttribute('plugdir') ? this.plugSrc.getAttribute('plugdir').toLowerCase() : "down";

 if(this.segments.length && this.segments[idx])
 {
  var div = this.segments[idx];
  div.style.display = "block";
 }
 else
 {
  var div = document.createElement('DIV');
  div.className = this.className;
  div.style.display = "block";
  div.style.position = "absolute";
  div.onclick = function(){SIMPLE_CABLE_HANDLER.selectCable(oThis);}
  div.style.cursor = "pointer";
  this.segments.push(div);
 }

 var startpos = SIMPLE_CABLE_HANDLER.getObjectPosition(this.plugSrc);
 startpos.x+= Math.floor(this.plugSrc.offsetWidth/2);
 startpos.y+= this.plugSrc.offsetHeight;
 if(this.corrX) startpos.x+=this.corrX;
 if(this.corrY) startpos.y+=this.corrY;

 if(this.plugDest)
 {
  var endpos = SIMPLE_CABLE_HANDLER.getObjectPosition(this.plugDest);
  endpos.x+= Math.floor(this.plugDest.offsetWidth/2);
  endpos.y+= Math.floor(this.plugDest.offsetHeight/2);
  if(this.corrX) endpos.x+=this.corrX;
  if(this.corrY) endpos.y+=this.corrY;
 }
 else
 {
  var endpos = SIMPLE_CABLE_HANDLER.mousepos;
  /* use temp plug */
  if(this.plugTmp)
  {
   this.plugTmp.style.left = endpos.x - Math.floor(this.plugTmp.offsetWidth/2);
   this.plugTmp.style.top = endpos.y - Math.floor(this.plugTmp.offsetHeight/2);
   SIMPLE_CABLE_HANDLER.cablesParent.appendChild(this.plugTmp);
   this.plugTmp.style.display = "";
  }
 }
 
 var midth = Math.floor(this.thickness/2);

 switch(lastdir)
 {
  case 'up' : {
	} break;

  case 'down' : {
	 div.style.left = startpos.x - midth;
	 div.style.top = startpos.y - midth;
	 div.style.width = this.thickness;

	 if(this.objSrcPos)
	 {
	  height = (this.objSrcPos.y + this.objSrc.offsetHeight)-(startpos.y-Math.floor(this.plugSrc.offsetHeight/2));
	 }
	 else
	 {
	  if((endpos.y <= startpos.y) || ((endpos.y-startpos.y) < 20))
	   var height = 20+midth;
	  else
	   var height = (endpos.y-startpos.y)+this.thickness;
	 }
	 div.style.height = height;
	 if(!div.parentNode)
	  SIMPLE_CABLE_HANDLER.cablesParent.appendChild(div);
	 var point = {x:startpos.x, y:startpos.y+height-this.thickness}
	 this.nextStep(point, endpos, idx+1, "down");
	} break;

  case 'left' : {
	} break;

  case 'right' : {
	} break;

 }
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCable.prototype.show = function()
{
 this.update();
 for(var c=0; c < this.segments.length; c++)
  this.segments[c].style.visibility = "visible";
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCable.prototype.hide = function()
{
 for(var c=0; c < this.segments.length; c++)
  this.segments[c].style.visibility = "hidden";
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCable.prototype.select = function()
{
 for(var c=0; c < this.segments.length; c++)
  this.segments[c].className = "simplecable-selected";
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCable.prototype.unselect = function()
{
 for(var c=0; c < this.segments.length; c++)
  this.segments[c].className = "simplecable-"+this.color;
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCable.prototype.nextStep = function(lastpos, destpos, idx, lastdir)
{
 var oThis = this;
 if((lastpos.x == destpos.x) && (lastpos.y == destpos.y))
  return this.finish(idx);

 /* TODO: da eliminare questa parte */
 if(idx >= 10)
 {
  return this.finish(idx);
 }

 if(this.segments.length && this.segments[idx])
 {
  var div = this.segments[idx];
  div.style.display = "block";
 }
 else
 {
  var div = document.createElement('DIV');
  div.className = this.className;
  div.style.display = "block";
  div.style.position = "absolute";
  div.onclick = function(){SIMPLE_CABLE_HANDLER.selectCable(oThis);}
  div.style.cursor = "pointer";
  this.segments.push(div);
 }

 var midth = Math.floor(this.thickness/2);

 switch(lastdir)
 {
  case 'up' : case 'down' : {
	 /* può andare solo a sx o a dx */
	 if(destpos.x <= lastpos.x) // vado a sinistra
	 {
	  var width = lastpos.x - destpos.x;
	  if((width < 20) && (lastpos.y != destpos.y))
	   width = 20;

	  div.style.left = lastpos.x - width - midth;
	  div.style.width = width+this.thickness;
	  div.style.top = lastpos.y-midth;
	  div.style.height = this.thickness;
	  if(!div.parentNode)
	   SIMPLE_CABLE_HANDLER.cablesParent.appendChild(div);
	  var point = {x:lastpos.x - width, y:lastpos.y}
	  this.nextStep(point, destpos, idx+1, "left");
	 }
	 else // vado a destra
	 {
	  var width = destpos.x - lastpos.x;
	  if((width < 20) && (lastpos.y != destpos.y))
	   width = 20;
	  
	  div.style.left = lastpos.x - midth;
	  div.style.width = width+this.thickness;
	  div.style.top = lastpos.y-midth;
	  div.style.height = this.thickness;
	  if(!div.parentNode)
	   SIMPLE_CABLE_HANDLER.cablesParent.appendChild(div);
	  var point = {x:lastpos.x + width, y:lastpos.y}
	  this.nextStep(point, destpos, idx+1, "right");  
	 }
	} break;

  case 'left' : case 'right' : {
	 /* può andare solo su o giu */
	 if(destpos.y <= lastpos.y) // vado su
	 {
	  var height = lastpos.y - destpos.y;
	  if((height < 20) && (lastpos.x != destpos.x))
	   height = 20;
	  
	  div.style.top = lastpos.y - height - midth;
	  div.style.height = height+this.thickness;
	  div.style.left = lastpos.x-midth;
	  div.style.width = this.thickness;
	  if(!div.parentNode)
	   SIMPLE_CABLE_HANDLER.cablesParent.appendChild(div);
	  var point = {x:lastpos.x, y:lastpos.y - height}
	  this.nextStep(point, destpos, idx+1, "up");
	 }
	 else // vado giu
	 {
	  var height = destpos.y - lastpos.y;
	  if((height < 20) && (lastpos.x != destpos.x))
	   height = 20;

	  /*if(this.objDestPos)
	  {
	   height = this.thickness + (this.objDestPos.y + this.objDest.offsetHeight) - lastpos.y;
	  }*/

	  div.style.top = lastpos.y - midth;
	  div.style.height = height+this.thickness;
	  div.style.left = lastpos.x-midth;
	  div.style.width = this.thickness;
	  if(!div.parentNode)
	   SIMPLE_CABLE_HANDLER.cablesParent.appendChild(div);
	  var point = {x:lastpos.x, y:lastpos.y + height}
	  this.nextStep(point, destpos, idx+1, "down");
	 }
	} break;

 }

}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCable.prototype.finish = function(idx)
{
 if(!idx) return;
 for(var c=idx; c < this.segments.length; c++)
  this.segments[c].style.display = "none";
}
//-------------------------------------------------------------------------------------------------------------------//
SimpleCable.prototype.free = function()
{
 for(var c=0; c < this.segments.length; c++)
 {
  if(this.segments[c].parentNode)
   this.segments[c].parentNode.removeChild(this.segments[c]);
 }
 if(this.plugTmp && this.plugTmp.parentNode)
  this.plugTmp.parentNode.removeChild(this.plugTmp);

 SIMPLE_CABLE_HANDLER.Cables.splice(SIMPLE_CABLE_HANDLER.Cables.indexOf(this),1);
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function SIMPLE_CABLE_HANDLER_MOUSE_MOVE(ev){ ev = ev || window.event; SIMPLE_CABLE_HANDLER._onmousemove(ev); }
function SIMPLE_CABLE_HANDLER_MOUSE_DOWN(ev){ ev = ev || window.event; SIMPLE_CABLE_HANDLER._onmousedown(ev); }
function SIMPLE_CABLE_HANDLER_KEY_DOWN(ev){ ev = ev || window.event; SIMPLE_CABLE_HANDLER._onkeydown(ev); }
//-------------------------------------------------------------------------------------------------------------------//

var SIMPLE_CABLE_HANDLER = new SimpleCableHandler();

