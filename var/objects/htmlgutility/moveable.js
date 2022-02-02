/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2011 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 
 #PACKAGE: 
 #DESCRIPTION: 
 #VERSION: 
 #CHANGELOG:
 #TODO:
 
*/

//-------------------------------------------------------------------------------------------------------------------//
document.addEventListener ? document.addEventListener("mousemove",gjkmp_documentMouseMove,false) : document.attachEvent("onmousemove",gjkmp_documentMouseMove);
document.addEventListener ? document.addEventListener("mousedown",gjkmp_documentMouseDown,false) : document.attachEvent("onmousedown",gjkmp_documentMouseDown);
document.addEventListener ? document.addEventListener("mouseup",gjkmp_documentMouseUp,false) : document.attachEvent("onmouseup",gjkmp_documentMouseUp);

var GJKMP_SELECTED_OBJECT = null;

//-------------------------------------------------------------------------------------------------------------------//
function gjkmp_documentMouseMove(ev)
{
 ev = ev || window.event;
 var target = ev.target || ev.srcElement;
 target = target.O ? target.O : target;

 if(GJKMP_SELECTED_OBJECT) /* MOVE THE OBJECT */
 {
  var mousePos = gjkmp_mouseCoords(ev);
  var obj = GJKMP_SELECTED_OBJECT;
  obj.style.left = (mousePos.x - obj.gjkmpDiffX) + document.body.scrollLeft;
  obj.style.top = (mousePos.y - obj.gjkmpDiffY) + document.body.scrollTop;
  if(parseFloat(obj.style.left) < 0)
   obj.style.left = 0;
  if(parseFloat(obj.style.top) < 0)
   obj.style.top = 0;
  if(obj.OnMove)
   obj.OnMove();
 } /* EOF MOVE THE OBJECT */

}
//-------------------------------------------------------------------------------------------------------------------//
function gjkmp_documentMouseDown(ev)
{
 ev = ev || window.event;
 var target = ev.target || ev.srcElement;
 if(target.getAttribute('moveableobjecthandle') && !GJKMP_SELECTED_OBJECT)
 {
  GJKMP_SELECTED_OBJECT = target.O ? target.O : target;
  //GJKMP_SELECTED_OBJECT.style.zIndex+= 100;
 }

 var object = target.O ? target.O : target;
 
 var mousePos = gjkmp_mouseCoords(ev);
 var objectPos = gjkmp_getObjectPosition(object);

 object.gjkmpDiffX = mousePos.x - objectPos.x;
 object.gjkmpDiffY = mousePos.y - objectPos.y;

}
//-------------------------------------------------------------------------------------------------------------------//
function gjkmp_documentMouseUp(ev)
{
 ev = ev || window.event;
 var target = ev.target || ev.srcElement;
 target = target.O ? target.O : target;

 /* to be continue ... */

 GJKMP_SELECTED_OBJECT = null;
 return;
}
//-------------------------------------------------------------------------------------------------------------------//
function gjkmp_mouseCoords(ev)
{
 if(ev.pageX || ev.pageY)
  return {x:ev.pageX, y:ev.pageY}; 
 return {x:ev.clientX + document.body.scrollLeft - document.body.clientLeft, y:ev.clientY + document.body.scrollTop  - document.body.clientTop};
}
//-------------------------------------------------------------------------------------------------------------------//
function gjkmp_getObjectPosition(e)
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

 left+= document.body.scrollLeft;
 top+= document.body.scrollTop;
 return {x:left, y:top};
}
//-------------------------------------------------------------------------------------------------------------------//
function gjkmp_getStyle(el,styleProp)
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
function setMoveableProperty(obj, handle, options)
{
 if(!handle)
  handle = obj;
 obj._moveableOptions = options ? options : {mode:'normal'};
 handle.setAttribute('moveableobjecthandle',true);
 obj.setAttribute('moveableobject',true);

 handle.style.cursor = "move";
 handle.O = obj;
 obj.handle = handle;

 obj.style.position = "absolute";
 return obj;
}
//-------------------------------------------------------------------------------------------------------------------//

