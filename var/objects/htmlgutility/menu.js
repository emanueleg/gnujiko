/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-05-2012
 #PACKAGE: htmlgutility
 #DESCRIPTION: Simple menu
 #VERSION: 2.0beta
 #CHANGELOG: 24-05-2012 : Some bug fix.
 #TODO:
 
*/

var GMENU_ACTIVE_POPUPS = new Array();
var GMENU_BODYLOAD = false;
var GMENU_LASTACT = null;
var GMENU_ZINDEX = 65535;

function GPopupMenu(_parent, _ul, _options)
{
 this.parent = _parent;
 this.UL = _ul;
 this._options = _options ? _options : {showmethod : "underparent"};
 this._state = "closed";
 this.oldULparentNode = _ul.parentNode;

 var nodes = _gmenu_getULnodes(this.UL);
 this.Items = new Array();
 for(var c=0; c < nodes.length; c++)
 {
  if(nodes[c].getElementsByTagName('UL').length)
   this.Items.push(new GPopupMenu(this,nodes[c].getElementsByTagName('UL')[0],{showmethod : "rightofparent", issubitem : true}));
 }

 this.O = document.createElement('TABLE');
 this.O.cellSpacing=0; this.O.cellPadding=0; this.O.border=0;
 this.O.style.position = "absolute";
 this.O.style.left = 0;
 this.O.style.top = 0;
 this.O.style.visibility = "hidden";
 this.O.className = "popupmenu-container";
 
 var cll = this.O.insertRow(-1).insertCell(-1);
 cll.className = "container";
 cll.appendChild(this.UL);

 // SHADOW //
 var cll = this.O.rows[0].insertCell(-1);
 cll.className = "shadowright";
 cll.innerHTML = "<img src='"+ABSOLUTE_URL+"var/objects/htmlgutility/themes/default-menu/img/void.gif' width='4' height='4'/>";

 var cll = this.O.insertRow(-1).insertCell(-1);
 cll.className = "shadowbottomleft";
 cll.innerHTML = "<img src='"+ABSOLUTE_URL+"var/objects/htmlgutility/themes/default-menu/img/void.gif' width='4' height='4'/>";

 var cll = this.O.rows[1].insertCell(-1);
 cll.className = "shadowbottomright";
 cll.innerHTML = "<img src='"+ABSOLUTE_URL+"var/objects/htmlgutility/themes/default-menu/img/void.gif' width='4' height='4'/>";

 document.body.appendChild(this.O);
 var oThis = this;
 if(this._options.issubitem)
 {
  this.oldULparentNode.className = "closed";
  this.oldULparentNode.onmousedown = function(){
	 if(GMENU_ACTIVE_POPUPS.length)
	 {
	  while(GMENU_ACTIVE_POPUPS[GMENU_ACTIVE_POPUPS.length-1] != oThis.parent)
	   GMENU_ACTIVE_POPUPS[GMENU_ACTIVE_POPUPS.length-1].hide();
	  GMENU_LASTACT = "opensubnodes";
	 }
	}
 }
 this.oldULparentNode.onclick = function(){oThis.show();}
}

GPopupMenu.prototype.show = function()
{

  var p = getObjectPosition(this.oldULparentNode);
  switch(this._options.showmethod)
  {
   case "rightofparent" : {
	 this.O.style.left = (this.oldULparentNode.offsetWidth+p.x)+"px";
  	 this.O.style.top = p.y+"px";
	} break;
   default : {
	 this.O.style.left = p.x+"px";
  	 this.O.style.top = (this.oldULparentNode.offsetHeight+p.y)+"px";
	} break;
  }

  var posX = parseFloat(this.O.style.left);
  var posY = parseFloat(this.O.style.top);


 this.O.style.zIndex = GMENU_ZINDEX;
 GMENU_ZINDEX++;

 this.O.style.left = 0;
 this.O.style.top = 0;
 this.O.style.visibility = "hidden";
 this.UL.style.display = "block";
 
 var SW = window.innerHeight ? window.innerHeight : document.body.clientHeight;
 if((posY+this.UL.offsetHeight) > SW)
  posY = SW-this.UL.offsetHeight-20;

 this.O.style.top = posY;
 this.O.style.left = posX;

 this.O.style.visibility = "";
 this.UL.style.display = "block";

 if(!GMENU_BODYLOAD)
  document.addEventListener ? document.addEventListener("mouseup",_gmenu_popupClose,false) : document.attachEvent("onmouseup",_gmenu_popupClose);
 GMENU_ACTIVE_POPUPS.push(this);
 if(this.oldULparentNode)
 {
  var cn = this.oldULparentNode.className;
  if(!cn || (cn == "") || (cn == "closed"))
   this.oldULparentNode.className = "opened";
 }
}

GPopupMenu.prototype.hide = function()
{
 this.O.style.visibility = "hidden";
 if(this.oldULparentNode.className == "opened")
  this.oldULparentNode.className = "closed";
 GMENU_ACTIVE_POPUPS.splice(GMENU_ACTIVE_POPUPS.indexOf(this,1));
}

//-------------------------------------------------------------------------------------------------------------------//
function GMenu(_ul)
{
 this.UL = _ul;
 var nodes = _gmenu_getULnodes(this.UL);
 this.Items = new Array();
 for(var c=0; c < nodes.length; c++)
 {
  if(nodes[c].getElementsByTagName('UL').length)
   this.Items.push(new GPopupMenu(this,nodes[c].getElementsByTagName('UL')[0]));
 }
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function _gmenu_getULnodes(ul)
{
 var ret = new Array();
 for(var c=0; c < ul.childNodes.length; c++)
 {
  if((ul.childNodes[c].nodeType == 1) && (ul.childNodes[c].nodeName == "LI"))
   ret.push(ul.childNodes[c]);
 }
 return ret;
}

function _gmenu_getULorLIname(obj)
{
 if(obj.firstChild.nodeType == 3)
  return obj.firstChild.nodeValue;
 return "";
}
//-------------------------------------------------------------------------------------------------------------------//
function _gmenu_popupClose()
{
 if(GMENU_LASTACT == "opensubnodes")
 {
  GMENU_LASTACT = null;
  return;
 }
 while(GMENU_ACTIVE_POPUPS.length)
  GMENU_ACTIVE_POPUPS[GMENU_ACTIVE_POPUPS.length-1].hide();
}
//-------------------------------------------------------------------------------------------------------------------//

