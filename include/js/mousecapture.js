/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-07-2012
 #PACKAGE: gnujiko-desktop-base
 #DESCRIPTION: Gnujiko mouse capture utility.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

document.addEventListener ? document.addEventListener("mousedown",__gjkMouseCapture_mousedown,false) : document.attachEvent("onmousedown",__gjkMouseCapture_mousedown);
document.addEventListener ? document.addEventListener("mouseup",__gjkMouseCapture_mouseup,false) : document.attachEvent("onmouseup",__gjkMouseCapture_mouseup);
document.addEventListener ? document.addEventListener("mousemove",__gjkMouseCapture_mousemove,false) : document.attachEvent("onmousemove",__gjkMouseCapture_mousemove);

function __gjkMouseCapture_mousedown(event)
{
 GnujikoMouseCapture.onmousedown(event);
}

function __gjkMouseCapture_mouseup(event)
{
 GnujikoMouseCapture.onmouseup(event);
}

function __gjkMouseCapture_mousemove(event)
{
 GnujikoMouseCapture.onmousemove(event);
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
if(typeof(GnujikoMouseCapture) != "object")
 var GnujikoMouseCapture = {
	//---PRIVATE---------------------------------------------------------------------------------//
	listeners : new Array(),

	onmousedown : function(event)
	{
	 for(var c=0; c < this.listeners.length; c++)
	 {
	  if(typeof(this.listeners[c].OnMouseDown) == "function")
	  {
	   var ret = new Array();
	   var mouse = this.getMouseCoords(event);
	   ret['x'] = mouse.x;
	   ret['y'] = mouse.y;

	   if(this.listeners[c].__gjkMCC)
	   {
		var objPos = this.getObjectPosition(this.listeners[c].__gjkMCC);
		ret['objx'] = objPos.x;
		ret['objy'] = objPos.y;
		ret['mousex'] = mouse.x - objPos.x;
		ret['mousey'] = mouse.y - objPos.y;
	   }
	   if(this.listeners[c].OnMouseDown(event,ret) == false)
	   {
		if(event)
		 event.preventDefault();
	   }

	  }
	 }
	},

	onmouseup : function(event)
	{
	 for(var c=0; c < this.listeners.length; c++)
	 {
	  if(typeof(this.listeners[c].OnMouseUp) == "function")
	  {
	   var ret = new Array();
	   var mouse = this.getMouseCoords(event);
	   ret['x'] = mouse.x;
	   ret['y'] = mouse.y;

	   if(this.listeners[c].__gjkMCC)
	   {
		var objPos = this.getObjectPosition(this.listeners[c].__gjkMCC);
		ret['objx'] = objPos.x;
		ret['objy'] = objPos.y;
		ret['mousex'] = mouse.x - objPos.x;
		ret['mousey'] = mouse.y - objPos.y;
	   }
	   this.listeners[c].OnMouseUp(event,ret);
	  }
	 }
	},

	onmousemove : function(event)
	{
	 for(var c=0; c < this.listeners.length; c++)
	 {
	  if(typeof(this.listeners[c].OnMouseMove) == "function")
	  {
	   var ret = new Array();
	   var mouse = this.getMouseCoords(event);
	   ret['x'] = mouse.x;
	   ret['y'] = mouse.y;

	   if(this.listeners[c].__gjkMCC)
	   {
		var objPos = this.getObjectPosition(this.listeners[c].__gjkMCC);
		ret['objx'] = objPos.x;
		ret['objy'] = objPos.y;
		ret['mousex'] = mouse.x - objPos.x;
		ret['mousey'] = mouse.y - objPos.y;
	   }
	   this.listeners[c].OnMouseMove(event,ret);
	  }
	 }
	},

	getMouseCoords : function(ev)
	{
	 if(ev.pageX || ev.pageY)
	  return {x:ev.pageX, y:ev.pageY}; 
	 return {x:ev.clientX + document.body.scrollLeft - document.body.clientLeft, y:ev.clientY + document.body.scrollTop  - document.body.clientTop};
	},

	getObjectPosition : function(e)
	{
	 if(!e)
	  return null;
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
	  //left-= obj.scrollLeft ? obj.scrollLeft : 0;
	  //top-= obj.scrollTop ? obj.scrollTop : 0;
	 }

	 left+= document.body.scrollLeft;
	 top+= document.body.scrollTop;

	 return {x:left, y:top};
	},


	//---PUBLIC FUNCTIONS------------------------------------------------------------------------//
	registerListener : function(obj,canvas)
	{
	 obj.__gjkMCC = canvas ? canvas : null;
	 this.listeners.push(obj);
	}
	//-------------------------------------------------------------------------------------------//
}
//-------------------------------------------------------------------------------------------------------------------//


