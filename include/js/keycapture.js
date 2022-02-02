/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 22-05-2012
 #PACKAGE: gnujiko-desktop-base
 #DESCRIPTION: Gnujiko key capture utility.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

document.addEventListener ? document.addEventListener("keydown",__gjkKeyCapture_keydown,false) : document.attachEvent("onkeydown",__gjkKeyCapture_keydown);
document.addEventListener ? document.addEventListener("keyup",__gjkKeyCapture_keyup,false) : document.attachEvent("onkeyup",__gjkKeyCapture_keyup);
document.addEventListener ? document.addEventListener("keypress",__gjkKeyCapture_keypress,false) : document.attachEvent("onkeypress",__gjkKeyCapture_keypress);

var __gjkKeyCapture_metakeys = {9:"TAB", 27:"ESC", 33:"PG-UP", 34:"PG-DOWN", 35:"END", 36:"HOME", 37:"LEFT", 38:"UP", 39:"RIGHT", 40:"DOWN", 45:"INS", 46:"CANC"};

function __gjkKeyCapture_keydown(event)
{
 if(window.event) // IE
  keynum = event.keyCode
 else if(event.which) // Netscape/Firefox/Opera
  keynum = event.which;
 keychar = String.fromCharCode(keynum);

 GnujikoKeyCapture.ALT = event.altKey;
 GnujikoKeyCapture.CTRL = event.ctrlKey;
 GnujikoKeyCapture.SHIFT = event.shiftKey;

 switch(event.target.tagName)
 {
  case 'INPUT' : case 'TEXTAREA' : {
	 if(__gjkKeyCapture_metakeys[keynum])
	 {
	  switch(__gjkKeyCapture_metakeys[keynum])
	  {
	   /* In textarea or edit object emits a signal only if one of these keys are pressed */
	   case "TAB" : case "ESC" : GnujikoKeyCapture.onkeydown(__gjkKeyCapture_metakeys[keynum],event); break;
	  }
	 }
	} break;
  default : GnujikoKeyCapture.onkeydown(__gjkKeyCapture_metakeys[keynum],event); break;
 }
}

function __gjkKeyCapture_keyup(event)
{
 if(window.event) // IE
  keynum = event.keyCode
 else if(event.which) // Netscape/Firefox/Opera
  keynum = event.which;
 keychar = String.fromCharCode(keynum);

 switch(keynum)
 {
  case 16 : GnujikoKeyCapture.SHIFT = false; break;
  case 17 : GnujikoKeyCapture.CTRL = false; break;
  case 18 : GnujikoKeyCapture.ALT = false; break;
 }

 /*GnujikoKeyCapture.ALT = event.altKey;
 GnujikoKeyCapture.CTRL = event.ctrlKey;
 GnujikoKeyCapture.SHIFT = event.shiftKey;*/

 switch(event.target.tagName)
 {
  case 'INPUT' : case 'TEXTAREA' : {
	 if(__gjkKeyCapture_metakeys[keynum])
	 {
	  switch(__gjkKeyCapture_metakeys[keynum])
	  {
	   /* In textarea or edit object emits a signal only if one of these keys are pressed */
	   case 'LEFT' : case 'RIGHT' : case 'UP' : case 'DOWN' : GnujikoKeyCapture.onkeyup(__gjkKeyCapture_metakeys[keynum],event); break;
	  }
	 }
	} break;
  default : GnujikoKeyCapture.onkeyup(__gjkKeyCapture_metakeys[keynum],event); break;
 }
}

function __gjkKeyCapture_keypress(event)
{
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
if(typeof(GnujikoKeyCapture) != "object")
 var GnujikoKeyCapture = {
	//---PRIVATE---------------------------------------------------------------------------------//
	listeners : new Array(),
	ALT : false,
	SHIFT : false,
	CTRL : false,

	onkeydown : function(metakey,event)
	{
	 /*if(typeof(metakey) == "undefined")
	  return;*/
	 for(var c=0; c < this.listeners.length; c++)
	 {
	  if(typeof(this.listeners[c].OnKeyEvent) == "function")
	  {
	   if(this.listeners[c].OnKeyEvent(metakey,false,event) == false)
	   {
		if(event)
		 event.preventDefault();
	   }

	  }
	 }
	},
	onkeyup : function(metakey,event)
	{
	 for(var c=0; c < this.listeners.length; c++)
	 {
	  if(typeof(this.listeners[c].OnKeyEvent) == "function")
	  {
	   this.listeners[c].OnKeyEvent(metakey,true,event);
	  }
	 }	 
	},
	//---PUBLIC FUNCTIONS------------------------------------------------------------------------//
	registerListener : function(obj)
	{
	 this.listeners.push(obj);
	}
	//-------------------------------------------------------------------------------------------//
}
//-------------------------------------------------------------------------------------------------------------------//


