/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2015 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 06-03-2015
 #PACKAGE: gaplayer
 #DESCRIPTION: Gap Layer object.
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS:
 #TODO:
 
*/

function GapLayer(width, height)
{
 this.O = document.createElement('DIV');
 this.O.className = "gaplayer";

 /* GAP UP */
 this.gapDiv = document.createElement('DIV');
 this.gapDiv.className = "gaplayer-gapup";
 this.O.appendChild(this.gapDiv);

 this.C = document.createElement('DIV');
 this.C.className = "gaplayer-container";
 this.C.style.width = (width ? width : 320)+"px";
 this.C.style.height = (height ? height : 240)+"px";

 this.O.appendChild(this.C);

 document.body.appendChild(this.O);
}
//-------------------------------------------------------------------------------------------------------------------//
GapLayer.prototype.getAbsPos = function(e)
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
GapLayer.prototype.setContent = function(html)
{
 this.C.innerHTML = html;
}
//-------------------------------------------------------------------------------------------------------------------//
GapLayer.prototype.resize = function(width, height)
{
 this.C.style.width = width+"px";
 this.C.style.height = height+"px";
}
//-------------------------------------------------------------------------------------------------------------------//
GapLayer.prototype.setPadding = function(paddingStyle)
{
 this.C.style.padding = paddingStyle;
}
//-------------------------------------------------------------------------------------------------------------------//
GapLayer.prototype.show = function(btnObj, corrX, corrY)
{
 var pos = this.getAbsPos(btnObj);

 var posX = pos.x - Math.floor(this.O.offsetWidth/2);
 var posY = pos.y + btnObj.offsetHeight;

 if(!isNaN(corrX)) posX+= corrX;
 if(!isNaN(corrY)) posY+= corrY;

 this.O.style.left = posX+"px";
 this.O.style.top = posY+"px";

 var screenWidth = window.innerWidth ? window.innerWidth : document.body.clientWidth;
 var screenHeight = window.innerHeight ? window.innerHeight : document.body.clientHeight;

 if((parseFloat(this.O.style.left)+this.O.offsetWidth) > screenWidth)
  this.O.style.left = screenWidth - this.O.offsetWidth - 20;

 if((parseFloat(this.O.style.top)+this.O.offsetHeight) > screenHeight)
  this.O.style.top = (pos.y-this.O.offsetHeight)+"px";

 // GAP POS 
 var gapW = 16;
 var btnRelCenterX = (pos.x + Math.floor(btnObj.offsetWidth/2)) - parseFloat(this.O.style.left);
 this.gapDiv.style.backgroundPosition = (btnRelCenterX-(Math.floor(gapW/2)))+"px top";

 this.O.style.visibility = "visible";
}
//-------------------------------------------------------------------------------------------------------------------//
GapLayer.prototype.load = function(layerName, args, btnObj, corrX, corrY)
{
 var oThis = this;

 if(!this.layerH)
 {
  this.layerH = new Layer();
  this.layerH.OnLoad = function(){oThis.show(btnObj, corrX, corrY);}
 }

 // verifica se è cambiato qualcosa
 if((this.layerH.LayerName == layerName) && (this.layerH.Arguments == args))
  return this.show(btnObj, corrX, corrY);

 this.layerH.load(layerName, args, this.C, true);
}
//-------------------------------------------------------------------------------------------------------------------//
GapLayer.prototype.hide = function()
{
 this.O.style.visibility = "hidden";
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//

