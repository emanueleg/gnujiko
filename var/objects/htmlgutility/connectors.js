/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2011 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-07-2011
 #PACKAGE: 
 #DESCRIPTION: 
 #VERSION: 
 #CHANGELOG:
 #TODO:
 
*/
var SELECTED_CONNECTOR_PLUG = null;
var SELECTED_CABLE = null;
var CABLE_CLASSNAME = "defaultcable";
var CONNECTOR_CLASSNAME = "arrow";

function UnselectAllCables()
{
 if(SELECTED_CABLE)
  SELECTED_CABLE.UnSelect();
}

function _cableKeyEvents(event)
{
 if(window.event) // IE
  keynum = event.keyCode
 else if(event.which) // Netscape/Firefox/Opera
  keynum = event.which
 keychar = String.fromCharCode(keynum)
 switch(keynum)
 {
  case 46 : { /* DELETE */
	 if(SELECTED_CABLE)
	 {
	  SELECTED_CABLE.free();
	  SELECTED_CABLE = null;
	  return;
	 }
	} break;
 }
}

function _cableME(ev)
{
 ev = ev || window.event;
 var target = ev.target || ev.srcElement;
 if(target.showTerminalPlugs)
  target.showTerminalPlugs();
}

function _cableMO(ev)
{
 ev = ev || window.event;
 var target = ev.target || ev.srcElement;
 if(target.hideTerminalPlugs)
  target.hideTerminalPlugs();
}

document.addEventListener ? document.addEventListener("mouseup",UnselectAllCables,false) : document.attachEvent("onmouseup",UnselectAllCables);
document.addEventListener ? document.addEventListener("keydown",_cableKeyEvents,false) : document.attachEvent("onkeydown",_cableKeyEvents);
//-------------------------------------------------------------------------------------------------------------------//
function GCable(startPointX, startPointY, startPointDir, endPointX, endPointY, endPointDir, className, parentObj, plugClassName) 
{
 if(startPointDir == "top")
  startPointDir = "up";
 else if(startPointDir == "bottom")
  startPointDir = "down";

 if(endPointDir == "top")
  endPointDir = "up";
 else if(endPointDir == "bottom")
  endPointDir = "down";

 var d = new Date();
 this.Id = "cable-"+d.getTime();
 this.dbID = 0;

 this.segment = new Array();
 this.startPoint = {x:startPointX, y:startPointY, dir:startPointDir}
 this.endPoint = {x:endPointX, y:endPointY, dir:endPointDir}
 this.className = className ? className : CABLE_CLASSNAME;

 /* OPTIONS */
 this.cm = 20; // connector margin //
 this.terminal = {type: plugClassName ? plugClassName : CONNECTOR_CLASSNAME, visibled: true} // default values //
 this.terminal.O = document.createElement('DIV');
 this.terminal.O.style.position = "absolute";
 this.terminal.O.style.visibility = "hidden";
 this.terminal.O.cable = this;
 this.terminal.O.onclick = function(){this.cable.Select();}
 if(parentObj)
  parentObj.appendChild(this.terminal.O);
 else
  document.body.appendChild(this.terminal.O);

 /* Create 5 segments, sufficient for all positions */
 for(var c=0; c < 5; c++)
 {
  var seg = document.createElement('DIV');
  seg.style.display = 'none';
  seg.style.position = "absolute";
  seg.className = className ? className : CABLE_CLASSNAME;
  seg.ccn = seg.className;
  seg.cable = this;
  if(c==0)
   seg.id = this.Id;
  seg.onclick = function(){this.cable.Select();}
  if(parentObj)
   parentObj.appendChild(seg);
  else
   document.body.appendChild(seg);
  this.segment.push(seg);
 }

 this.pen = {x:0, y:0}

 this.update();
}
//-------------------------------------------------------------------------------------------------------------------//
GCable.prototype.update = function()
{
 this.segIdx = 0;
 this.pen.x = this.startPoint.x;
 this.pen.y = this.startPoint.y;
 

 /* NUOVO */
 // Detect dest cardinal position //
 var cPos = (this.endPoint.y < this.startPoint.y) ? "N" : "S";
 cPos+= (this.endPoint.x < this.startPoint.x) ? "W" : "E";

 switch(this.startPoint.dir)
 {
  case "up" : {
	 switch(cPos)
	 {
	  case "NW" : { /* NW MOVE */
		 switch(this.endPoint.dir)
		 {
		  case "up" : {
			 this.stepUp(this.pen.y - this.endPoint.y + this.cm);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepFinish();
			} break;
		  case "right" : {
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepFinish();
			} break;
		  case "down" : {
			 this.stepUp((this.pen.y - this.endPoint.y)/2);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepFinish();
			} break;
		  case "left" : {
			 this.stepUp((this.pen.y - this.endPoint.y)/2);
			 this.stepLeft(this.pen.x - this.endPoint.x + this.cm);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepFinish();
			} break;
		 }
		} break; /* EOF NW MOVE */
	  case "SW" : { /* SW MOVE */
		 this.stepUp(this.cm);
		 switch(this.endPoint.dir)
		 {
		  case "up" : {
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepFinish();
			} break;
		  case "right" : {
			 this.stepLeft((this.pen.x - this.endPoint.x)/2);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepFinish();
			} break;
		  case "down" : {
			 this.stepLeft((this.pen.x - this.endPoint.x)/2);
			 this.stepDown(this.endPoint.y - this.pen.y + this.cm);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepFinish();
			} break;
		  case "left" : {
			 this.stepLeft(this.pen.x - this.endPoint.x + this.cm);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepFinish();
			} break;
		 }
		} break; /* EOF SW MOVE */
	  case "NE" : { /* NE MOVE */
		 switch(this.endPoint.dir)
		 {
		  case "up" : {
			 this.stepUp(this.pen.y - this.endPoint.y + this.cm);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepFinish();
			} break;
		  case "right" : {
			 this.stepUp((this.pen.y - this.endPoint.y)/2);
			 this.stepRight(this.endPoint.x - this.pen.x + this.cm);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepFinish();
			} break;
		  case "down" : {
			 this.stepUp((this.pen.y - this.endPoint.y)/2);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepFinish();
			} break;
		  case "left" : {
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepFinish();
			} break;
		 }
		} break; /* EOF NE MOVE */
	  case "SE" : { /* SE MOVE */
		 this.stepUp(this.cm);
		 switch(this.endPoint.dir)
		 {
		  case "up" : {
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepFinish();
			} break;
		  case "right" : {
			 this.stepRight(this.endPoint.x - this.pen.x + this.cm);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepFinish();
			} break;
		  case "down" : {
			 this.stepRight((this.endPoint.x - this.pen.x)/2);
			 this.stepDown(this.endPoint.y - this.pen.y + this.cm);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepFinish();
			} break;
		  case "left" : {
			 this.stepRight((this.endPoint.x - this.pen.x)/2);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepFinish();
			} break;
		 }
		} break; /* EOF SE MOVE */
	 }
	} break; /* EOF CASE UP */
  //---------------------------------------------------------------------------------------------//
  case "right" : {
	 switch(cPos)
	 {
	  case "NW" : { /* NW MOVE */
		 switch(this.endPoint.dir)
		 {
		  case "up" : {
			 this.stepRight(this.cm);
			 this.stepUp(this.pen.y - this.endPoint.y + this.cm);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepFinish();
			} break;
		  case "right" : {
			 this.stepRight(this.cm);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepFinish();
			} break;
		  case "down" : {
			 this.stepRight(this.cm);
			 this.stepUp((this.pen.y - this.endPoint.y)/2);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepFinish();
			} break;
		  case "left" : {
			 this.stepRight(this.cm);
			 this.stepUp((this.pen.y - this.endPoint.y)/2);
			 this.stepLeft(this.pen.x - this.endPoint.x + this.cm);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepFinish();
			} break;
		 }
		} break; /* EOF NW MOVE */
	  case "SW" : { /* SW MOVE */
		 this.stepRight(this.cm);
		 switch(this.endPoint.dir)
		 {
		  case "up" : {
			 this.stepDown((this.endPoint.y - this.pen.y)/2);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepFinish();
			} break;
		  case "right" : {
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepFinish();
			} break;
		  case "down" : {
			 this.stepDown(this.endPoint.y - this.pen.y + this.cm);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepFinish();
			} break;
		  case "left" : {
			 this.stepDown((this.endPoint.y - this.pen.y)/2);
			 this.stepLeft(this.pen.x - this.endPoint.x + this.cm);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepFinish();
			} break;
		 }
		} break; /* EOF SW MOVE */
	  case "NE" : { /* NE MOVE */
		 switch(this.endPoint.dir)
		 {
		  case "up" : {
			 this.stepRight(this.cm);
			 this.stepUp(this.pen.y - this.endPoint.y + this.cm);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepFinish();
			} break;
		  case "right" : {
			 this.stepRight(this.endPoint.x - this.pen.x + this.cm);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepFinish();
			} break;
		  case "down" : {
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepFinish();
			} break;
		  case "left" : {
			 this.stepRight((this.endPoint.x - this.pen.x)/2);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepFinish();
			} break;
		 }
		} break; /* EOF NE MOVE */
	  case "SE" : { /* SE MOVE */
		 switch(this.endPoint.dir)
		 {
		  case "up" : {
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepFinish();
			} break;
		  case "right" : {
			 this.stepRight(this.endPoint.x - this.pen.x + this.cm);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepFinish();
			} break;
		  case "down" : {
			 this.stepRight((this.endPoint.x - this.pen.x)/2);
			 this.stepDown(this.endPoint.y - this.pen.y + this.cm);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepFinish();
			} break;
		  case "left" : {
			 this.stepRight((this.endPoint.x - this.pen.x)/2);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepFinish();
			} break;
		 }
		} break; /* EOF SE MOVE */
	 }
	} break; /* EOF CASE RIGHT */
  //---------------------------------------------------------------------------------------------//
  case "down" : {
	 switch(cPos)
	 {
	  case "NW" : { /* NW MOVE */
		 this.stepDown(this.cm);
		 switch(this.endPoint.dir)
		 {
		  case "up" : {
			 this.stepLeft((this.pen.x - this.endPoint.x)/2);
			 this.stepUp(this.pen.y - this.endPoint.y + this.cm);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepFinish();
			} break;
		  case "right" : {
			 this.stepLeft((this.pen.x - this.endPoint.x)/2);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepFinish();
			} break;
		  case "down" : {
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepFinish();
			} break;
		  case "left" : {
			 this.stepLeft(this.pen.x - this.endPoint.x + this.cm);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepFinish();
			} break;
		 }
		} break; /* EOF NW MOVE */
	  case "SW" : { /* SW MOVE */
		 switch(this.endPoint.dir)
		 {
		  case "up" : {
			 this.stepDown((this.endPoint.y - this.pen.y)/2);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepFinish();
			} break;
		  case "right" : {
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepFinish();
			} break;
		  case "down" : {
			 this.stepDown(this.endPoint.y - this.pen.y + this.cm);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepFinish();
			} break;
		  case "left" : {
			 this.stepDown((this.endPoint.y - this.pen.y)/2);
			 this.stepLeft(this.pen.x - this.endPoint.x + this.cm);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepFinish();
			} break;
		 }
		} break; /* EOF SW MOVE */
	  case "NE" : { /* NE MOVE */
		 this.stepDown(this.cm);
		 switch(this.endPoint.dir)
		 {
		  case "up" : {
			 this.stepRight((this.endPoint.x - this.pen.x)/2);
			 this.stepUp(this.pen.y - this.endPoint.y + this.cm);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepFinish();
			} break;
		  case "right" : {
			 this.stepRight(this.endPoint.x - this.pen.x + this.cm);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepFinish();
			} break;
		  case "down" : {
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepFinish();
			} break;
		  case "left" : {
			 this.stepRight((this.endPoint.x - this.pen.x)/2);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepFinish();
			} break;
		 }
		} break; /* EOF NE MOVE */
	  case "SE" : { /* SE MOVE */
		 switch(this.endPoint.dir)
		 {
		  case "up" : {
			 this.stepDown((this.endPoint.y - this.pen.y)/2);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepFinish();
			} break;
		  case "right" : {
			 this.stepDown((this.endPoint.y - this.pen.y)/2);
			 this.stepRight(this.endPoint.x - this.pen.x + this.cm);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepFinish();
			} break;
		  case "down" : {
			 this.stepDown(this.endPoint.y - this.pen.y + this.cm);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepFinish();
			} break;
		  case "left" : {
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepFinish();
			} break;
		 }
		} break; /* EOF SE MOVE */
	 }
	} break; /* EOF CASE DOWN */
  //---------------------------------------------------------------------------------------------//
  case "left" : {
	 switch(cPos)
	 {
	  case "NW" : { /* NW MOVE */
		 switch(this.endPoint.dir)
		 {
		  case "up" : {
			 this.stepLeft((this.pen.x - this.endPoint.x)/2);
			 this.stepUp(this.pen.y - this.endPoint.y + this.cm);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepFinish();
			} break;
		  case "right" : {
			 this.stepLeft((this.pen.x - this.endPoint.x)/2);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepFinish();
			} break;
		  case "down" : {
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepFinish();
			} break;
		  case "left" : {
			 this.stepLeft(this.pen.x - this.endPoint.x + this.cm);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepFinish();
			} break;
		 }
		} break; /* EOF NW MOVE */
	  case "SW" : { /* SW MOVE */
		 switch(this.endPoint.dir)
		 {
		  case "up" : {
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepFinish();
			} break;
		  case "right" : {
			 this.stepLeft((this.pen.x - this.endPoint.x)/2);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepFinish();
			} break;
		  case "down" : {
			 this.stepLeft((this.pen.x - this.endPoint.x)/2);
			 this.stepDown(this.endPoint.y - this.pen.y + this.cm);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepFinish();
			} break;
		  case "left" : {
			 this.stepLeft(this.pen.x - this.endPoint.x + this.cm);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepFinish();
			} break;
		 }
		} break; /* EOF SW MOVE */
	  case "NE" : { /* NE MOVE */
		 this.stepLeft(this.cm);
		 switch(this.endPoint.dir)
		 {
		  case "up" : {
			 this.stepUp(this.pen.y - this.endPoint.y + this.cm);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepFinish();
			} break;
		  case "right" : {
			 this.stepUp((this.pen.y - this.endPoint.y)/2);
			 this.stepRight(this.endPoint.x - this.pen.x + this.cm);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepFinish();
			} break;
		  case "down" : {
			 this.stepUp((this.pen.y - this.endPoint.y)/2);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepFinish();
			} break;
		  case "left" : {
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepFinish();
			} break;
		 }
		} break; /* EOF NE MOVE */
	  case "SE" : { /* SE MOVE */
		 this.stepLeft(this.cm);
		 switch(this.endPoint.dir)
		 {
		  case "up" : {
			 this.stepDown((this.endPoint.y - this.pen.y)/2);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepFinish();
			} break;
		  case "right" : {
			 this.stepDown((this.endPoint.y - this.pen.y)/2);
			 this.stepRight(this.endPoint.x - this.pen.x + this.cm);
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepLeft(this.pen.x - this.endPoint.x);
			 this.stepFinish();
			} break;
		  case "down" : {
			 this.stepDown(this.endPoint.y - this.pen.y + this.cm);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepUp(this.pen.y - this.endPoint.y);
			 this.stepFinish();
			} break;
		  case "left" : {
			 this.stepDown(this.endPoint.y - this.pen.y);
			 this.stepRight(this.endPoint.x - this.pen.x);
			 this.stepFinish();
			} break;
		 }
		} break; /* EOF SE MOVE */
	 }
	} break; /* EOF CASE LEFT */
  //---------------------------------------------------------------------------------------------//
 }
 /* EOF NUOVO */
}
//-------------------------------------------------------------------------------------------------------------------//
GCable.prototype.stepUp = function(px,follow)
{
 var div = this.segment[this.segIdx];
 div.ccndir = "vertical";
 div.className = div.ccn+"-vertical";
 div.style.top = this.pen.y-px;
 div.style.left = this.pen.x; 
 div.style.width=1;
 div.style.height = px;
 div.style.display='';

 this.pen.y-= px;
 this.segIdx++;
 if(follow)
  this.nextStep();
}
//-------------------------------------------------------------------------------------------------------------------//
GCable.prototype.stepDown = function(px,follow)
{
 var div = this.segment[this.segIdx];
 div.ccndir = "vertical";
 div.className = div.ccn+"-vertical";
 div.style.top = this.pen.y;
 div.style.left = this.pen.x; 
 div.style.width=1;
 div.style.height = px;
 div.style.display='';

 this.pen.y+= px;
 this.segIdx++;
 if(follow)
  this.nextStep();
}
//-------------------------------------------------------------------------------------------------------------------//
GCable.prototype.stepLeft = function(px,follow)
{
 var div = this.segment[this.segIdx];
 div.ccndir = "horizontal";
 div.className = div.ccn+"-horizontal";
 div.style.top = this.pen.y;
 div.style.left = this.pen.x-px; 
 div.style.width = px;
 div.style.height = 1;
 div.style.display='';

 this.pen.x-= px;
 this.segIdx++;
 if(follow)
  this.nextStep();
}
//-------------------------------------------------------------------------------------------------------------------//
GCable.prototype.stepRight = function(px,follow)
{
 var div = this.segment[this.segIdx];
 div.ccndir = "horizontal";
 div.className = div.ccn+"-horizontal";
 div.style.top = this.pen.y;
 div.style.left = this.pen.x; 
 div.style.width = px;
 div.style.height = 1;
 div.style.display='';

 this.pen.x+= px;
 this.segIdx++;
 if(follow)
  this.nextStep();
}
//-------------------------------------------------------------------------------------------------------------------//
GCable.prototype.stepFinish = function()
{
 if(this.terminal.visibled)
 {
  var ctcn = "";
  switch(this.terminal.type)
  {
   case 'arrow' : {
	 switch(this.endPoint.dir)
	 {
	  case 'up' : ctcn = "cableTerminalArrowDown"; break;
	  case 'right' : ctcn = "cableTerminalArrowLeft"; break;
	  case 'down' : ctcn = "cableTerminalArrowUp"; break;
	  case 'left' : ctcn = "cableTerminalArrowRight"; break;
	 }
	} break;
   case 'round' : ctcn = "cableTerminalRound"; break;
   case 'roundfill' : ctcn = "cableTerminalRoundFill"; break;
   case 'square' : ctcn = "cableTerminalSquare"; break;
   case 'squarefill' : ctcn = "cableTerminalSquareFill"; break;
  }
  this.terminal.O.className = ctcn;
  switch(this.endPoint.dir)
  {
   case "up" : {
	 this.terminal.O.style.left = this.endPoint.x - Math.floor(this.terminal.O.offsetWidth/2);
	 this.terminal.O.style.top = this.endPoint.y - this.terminal.O.offsetHeight;
	} break;
   case "right" : {
	 this.terminal.O.style.left = this.endPoint.x;
	 this.terminal.O.style.top = this.endPoint.y - Math.floor(this.terminal.O.offsetHeight/2);;
	} break;
   case "down" : {
	 this.terminal.O.style.left = this.endPoint.x - Math.floor(this.terminal.O.offsetWidth/2);
	 this.terminal.O.style.top = this.endPoint.y;
	} break;
   case "left" : {
	 this.terminal.O.style.left = this.endPoint.x - this.terminal.O.offsetWidth;
	 this.terminal.O.style.top = this.endPoint.y - Math.floor(this.terminal.O.offsetHeight/2);;
	} break;
  }
  this.terminal.O.style.visibility="visible";
 }
 else
  this.terminal.O.style.visibility='hidden';

 for(var c=this.segIdx; c < this.segment.length; c++)
  this.segment[c].style.display='none';
}
//-------------------------------------------------------------------------------------------------------------------//
GCable.prototype.Select = function()
{
 if(SELECTED_CABLE == this)
  return;
 if(SELECTED_CABLE)
  SELECTED_CABLE.UnSelect();
 for(var c=0; c < this.segment.length; c++)
  this.segment[c].className = this.segment[c].ccn+"-"+this.segment[c].ccndir+"-selected";
 SELECTED_CABLE = this;
}
//-------------------------------------------------------------------------------------------------------------------//
GCable.prototype.UnSelect = function()
{
 for(var c=0; c < this.segment.length; c++)
  this.segment[c].className = this.segment[c].ccn+"-"+this.segment[c].ccndir;
 SELECTED_CABLE = null;
}
//-------------------------------------------------------------------------------------------------------------------//
GCable.prototype.relativePosition = function()
{
 for(var c=0; c < this.segment.length; c++)
  this.segment[c].style.position='relative';
 this.terminal.O.style.position='relative';
}
//-------------------------------------------------------------------------------------------------------------------//
GCable.prototype.free = function()
{
 if(this.onfree)
  this.onfree();

 for(var c=0; c < this.segment.length; c++)
  this.segment[c].parentNode.removeChild(this.segment[c]);
 this.terminal.O.parentNode.removeChild(this.terminal.O);
 if(this.srcObj)
  this.srcObj.gcables.splice(this.srcObj.gcables.indexOf(this),1);
 if(this.dstObj)
  this.dstObj.gcables.splice(this.dstObj.gcables.indexOf(this),1);
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function GCableConnect(srcObj, srcDir, dstObj, dstDir, cableCN, plugCN)
{
 var srcY = parseFloat(srcObj.style.top ? srcObj.style.top : gjkmp_getStyle(srcObj,"top"));
 var srcX = parseFloat(srcObj.style.left ? srcObj.style.left : gjkmp_getStyle(srcObj,"left"));
 var dstY = parseFloat(dstObj.style.top ? dstObj.style.top : gjkmp_getStyle(dstObj,"top"));
 var dstX = parseFloat(dstObj.style.left ? dstObj.style.left : gjkmp_getStyle(dstObj,"left"));

 var points = new Array();
 var pos = new Array();
 pos.push({x:srcX, y:srcY});
 pos.push({x:dstX, y:dstY});

 for(var c=0; c < 2; c++)
 {
  var obj = c ? dstObj : srcObj;
  switch(c ? dstDir : srcDir)
  {
   case "up" : case "top" : points.push({x:pos[c].x+(obj.offsetWidth/2), y:pos[c].y}); break;
   case "right" : points.push({x:pos[c].x + obj.offsetWidth, y:pos[c].y + (obj.offsetHeight/2)}); break;
   case "down" : case "bottom" : points.push({x:pos[c].x + (obj.offsetWidth/2), y:pos[c].y + obj.offsetHeight}); break;
   case "left" : points.push({x:pos[c].x, y:pos[c].y + (obj.offsetHeight/2)}); break;
  }
 }

 var cable = new GCable(points[0].x, points[0].y, srcDir, points[1].x, points[1].y, dstDir, cableCN, srcObj.parentNode, plugCN);

 if(!srcObj.gcables)
  srcObj.gcables = new Array();
 if(!dstObj.gcables)
  dstObj.gcables = new Array();
 if(!srcObj.gcableplugs)
  srcObj.gcableplugs = new Array();
 if(!dstObj.gcableplugs)
  dstObj.gcableplugs = new Array();
 
 cable.srcObj = srcObj;
 cable.dstObj = dstObj;

 srcObj.gcables.push(cable);
 dstObj.gcables.push(cable);

 if(!srcObj.OnMove)
 {
  srcObj.OnMove = function(){
	 for(var c=0; c < this.gcables.length; c++)
	  _gcableConnectUpdate(this.gcables[c]);
	 for(var c=0; c < this.gcableplugs.length; c++)
	  _gcablePlugUpdate(this.gcableplugs[c]);
	}
 }

 if(!dstObj.OnMove)
 {
  dstObj.OnMove = function(){
	 for(var c=0; c < this.gcables.length; c++)
	  _gcableConnectUpdate(this.gcables[c]);
	 for(var c=0; c < this.gcableplugs.length; c++)
	  _gcablePlugUpdate(this.gcableplugs[c]);
	}
 }

 return cable;
}
//-------------------------------------------------------------------------------------------------------------------//
function _gcableConnectUpdate(cable)
{
 var srcObj = cable.srcObj;
 var dstObj = cable.dstObj;

 var srcY = parseFloat(srcObj.style.top ? srcObj.style.top : gjkmp_getStyle(srcObj,"top"));
 var srcX = parseFloat(srcObj.style.left ? srcObj.style.left : gjkmp_getStyle(srcObj,"left"));
 var dstY = parseFloat(dstObj.style.top ? dstObj.style.top : gjkmp_getStyle(dstObj,"top"));
 var dstX = parseFloat(dstObj.style.left ? dstObj.style.left : gjkmp_getStyle(dstObj,"left"));

 var points = new Array();
 var pos = new Array();
 pos.push({x:srcX, y:srcY});
 pos.push({x:dstX, y:dstY});

 for(var c=0; c < 2; c++)
 {
  var obj = c ? dstObj : srcObj;
  switch(c ? cable.endPoint.dir : cable.startPoint.dir)
  {
   case "up" : case "top" : points.push({x:pos[c].x+(obj.offsetWidth/2), y:pos[c].y}); break;
   case "right" : points.push({x:pos[c].x + obj.offsetWidth, y:pos[c].y + (obj.offsetHeight/2)}); break;
   case "down" : case "bottom" : points.push({x:pos[c].x + (obj.offsetWidth/2), y:pos[c].y + obj.offsetHeight}); break;
   case "left" : points.push({x:pos[c].x, y:pos[c].y + (obj.offsetHeight/2)}); break;
  }
 }
 cable.startPoint.x = points[0].x;
 cable.startPoint.y = points[0].y;
 cable.endPoint.x = points[1].x;
 cable.endPoint.y = points[1].y;
 cable.update();
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function GCableAddPlug(obj, posX, posY, direction)
{
 var objY = parseFloat(obj.style.top ? obj.style.top : gjkmp_getStyle(obj,"top"));
 var objX = parseFloat(obj.style.left ? obj.style.left : gjkmp_getStyle(obj,"left"));
 
 var plug = document.createElement('DIV');
 plug.className = "defaultconnectorplug";
 plug.style.position = "absolute";
 plug.style.visibility = "hidden";
 obj.parentNode.appendChild(plug);
 
 plug.info = {posx: posX, posy: posY, dir:direction};
 plug.O = obj;

 switch(posX)
 {
  case 'left' : posX = objX; break;
  case 'center' : posX = objX + Math.floor(obj.offsetWidth/2); break;
  case 'right' : posX = objX + obj.offsetWidth; break;
  default : posX+= (objX + (posX<0 ? obj.offsetWidth : 0)); break;
 }
 switch(posY)
 {
  case 'top' : posY = objY; break;
  case 'middle' : case 'center' : posY = objY + Math.floor(obj.offsetHeight/2); break;
  case 'bottom' : posY = objY + obj.offsetHeight; break;
  default : posY+= (objY + (posY<0 ? obj.offsetHeight : 0)); break;
 }
 
 plug.style.left = posX - Math.floor(plug.offsetWidth/2);
 plug.style.top = posY - Math.floor(plug.offsetHeight/2);
 
 if(!obj.gcables)
  obj.gcables = new Array();
 if(!obj.gcableplugs)
  obj.gcableplugs = new Array();

 obj.gcableplugs.push(plug);

 if(!obj.OnMove)
 {
  obj.OnMove = function(){
	 for(var c=0; c < this.gcables.length; c++)
	  _gcableConnectUpdate(this.gcables[c]);
	 for(var c=0; c < this.gcableplugs.length; c++)
	  _gcablePlugUpdate(this.gcableplugs[c]);
	}
 }

 if(!obj.showTerminalPlugs)
 {
  obj.showTerminalPlugs = function(){
	 for(var c=0; c < this.gcableplugs.length; c++)
	  this.gcableplugs[c].style.visibility = "visible";
	}
 }
 
 if(!obj.hideTerminalPlugs)
 {
  obj.hideTerminalPlugs = function(now){
	 if(!now)
	 {
	  window.setTimeout(function(){obj.hideTerminalPlugs(true);},3000);
	  return;
	 }
	 for(var c=0; c < this.gcableplugs.length; c++)
	  this.gcableplugs[c].style.visibility = "hidden";
	}
 }

 obj.addEventListener ? obj.addEventListener("mouseover",_cableME,false) : obj.attachEvent("onmouseover",_cableME);
 obj.addEventListener ? obj.addEventListener("mouseout",_cableMO,false) : obj.attachEvent("onmouseout",_cableMO);

 plug.onclick = function(){
	 if(!SELECTED_CONNECTOR_PLUG)
	  SELECTED_CONNECTOR_PLUG = this;
	 else if(SELECTED_CONNECTOR_PLUG != this)
	 {
	  var srcObj = SELECTED_CONNECTOR_PLUG.O;
	  var srcDir = SELECTED_CONNECTOR_PLUG.info.dir;
	  var dstObj = this.O;
	  var dstDir = this.info.dir;
	  var cable = GCableConnect(srcObj, srcDir, dstObj, dstDir);
	  SELECTED_CONNECTOR_PLUG = null;
	 }
	}

 plug.free = function(){
	 this.O.gcableplugs.splice(this.O.gcableplugs.indexOf(this),1);
	 this.parentNode.removeChild(this);
	}

 plug.relativePosition = function(){this.style.position = 'relative';}
}
//-------------------------------------------------------------------------------------------------------------------//
function _gcablePlugUpdate(plug)
{
 var obj = plug.O;
 var objY = parseFloat(obj.style.top ? obj.style.top : gjkmp_getStyle(obj,"top"));
 var objX = parseFloat(obj.style.left ? obj.style.left : gjkmp_getStyle(obj,"left"));
 var posX = plug.info.posx;
 var posY = plug.info.posy;

 switch(posX)
 {
  case 'left' : posX = objX; break;
  case 'center' : posX = objX + Math.floor(obj.offsetWidth/2); break;
  case 'right' : posX = objX + obj.offsetWidth; break;
  default : posX+= (objX + (posX<0 ? obj.offsetWidth : 0)); break;
 }
 switch(posY)
 {
  case 'top' : posY = objY; break;
  case 'middle' : case 'center' : posY = objY + Math.floor(obj.offsetHeight/2); break;
  case 'bottom' : posY = objY + obj.offsetHeight; break;
  default : posY+= (objY + (posY<0 ? obj.offsetHeight : 0)); break;
 }
 
 plug.style.left = posX - Math.floor(plug.offsetWidth/2);
 plug.style.top = posY - Math.floor(plug.offsetHeight/2);
}
//-------------------------------------------------------------------------------------------------------------------//

