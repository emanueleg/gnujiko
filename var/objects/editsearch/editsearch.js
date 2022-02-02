/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-11-2012
 #PACKAGE: editsearch
 #DESCRIPTION: Basic edit object with search property
 #VERSION: 2.0beta
 #CHANGELOG: 24-07-2012 - Aggiunto funzione free.
			 18-07-2012 - Bug fix.
 #TODO:
 
*/

var EditSearch = {
	init:function(obj,startQry,endQry,retValField,retTxtField,retArrName,focus,outValField,onQueryResultCallback)
	{
	 var oThis = this;
	 var O = obj ? obj : document.createElement('INPUT');
	 this.C = document.createElement('TABLE');
	 this.C.border=0; this.C.cellSpacing=0; this.C.cellPadding=0;
	 this.C.className = "editsearch";
	 this.C.onmouseover = function(){this.mousein = true;}
	 this.C.onmouseout = function(){this.mousein = false;}
	 this.C.onmouseup = function(){O.focus();}
	 O.esHinst = new Object();
	 O.esHinst.startQry = startQry;
	 O.esHinst.endQry = endQry;
	 O.esHinst.retValField = retValField;
	 O.esHinst.retTxtField = retTxtField;
	 O.esHinst.retArrName = retArrName;
	 O.esHinst.outValField = outValField;
	 if(focus)
	  O.focus();
	 this.sh = new GShell();
	 O.onkeydown = function(evt){oThis._onKeyDown(evt,this);}
	 O.onkeyup = function(){oThis._onKeyUp();}
	 O.onkeypress = function(evt){oThis._onKeyPress(evt,this);}
	 if(O.onblur)
	  O.oldOnBlur = O.onblur;
	 O.onblur = function(e){
		 oThis.hideResults();
		 if(typeof(this.oldOnBlur) == "function")
		  this.oldOnBlur(e);
		}
	 O.defaultValue = O.value;
	 this.qryTimer = null;
	 this.scrollTimer = null;
	 this.elemHinted = null;
	 this.results = new Array();
	 this.selectedIndex = -1;
	 document.body.addEventListener('click',function(){oThis.hideResults();},false);
	 O.hideResults = function(){oThis.hideResults();}

	 /* Create image button */
	 var img = document.createElement('IMG');
	 img.src = BASE_PATH+"share/icons/16x16/rubrica-contact-add.png";
	 img.style.position = "absolute";
	 img.style.cursor = "pointer";
	 img.style.display = "none";
	 O.parentNode.appendChild(img);
	 O.infoButton = img;

	 O.refreshInfoButton = function(){}
	 if(onQueryResultCallback)
	  O.onQueryResults = onQueryResultCallback;
	 return O;
	},

	_onKeyDown:function(e,O)
	{
	 var oThis = this;
	 if(this.qryTimer)
	  clearInterval(this.qryTimer);
	 if(this.scrollTimer)
	  clearInterval(this.scrollTimer);
	 var keyNum;
	 if(window.event)
	  keyNum = e.keyCode;
	 else if(e.which)
	  keyNum = e.which;
	 if(keyNum == 9)
	  e.preventDefault();
	 switch(keyNum)
	 {
	  case 38 : { // UP //
		 if(!this.results.length)
		  return;
		 var oThis = this;
		 this.lastMoveIndex = -1;
		} break;
	  case 40 : { // DOWN //
		 if(!this.results.length)
		  return;
		 var oThis = this;
		 this.lastMoveIndex = 1;
		} break;
	  case 13 : { // ENTER //
		 oThis.hideResults();
		 O.onblur(e);
		} break;
	  default : {
		 var isprintable=/[^ -~]/;
		 var ok = false;
		 if((keyNum == 8) || (keyNum == 46)) // canc and backspace //
		  ok = true;
	     if((!isprintable.test(String.fromCharCode(keyNum))) || ok)
		  this.qryTimer = window.setTimeout(function(){oThis._execQry(O);},250);
		} break;
	 }
	 this.lastKeyNum = keyNum;
	 if((keyNum == 9) || (keyNum == 27))
	 {
	  if(keyNum == 27)
	   O.value = O.defaultValue;
	  oThis.hideResults();
	  O.blur();
	 }
	 else
	 {
      if(!e)
       window.event.cancelBubble = true;
      else
       e.stopPropagation();
	 }
	},

	_onKeyUp:function()
	{
	 if(this.scrollTimer)
	  clearInterval(this.scrollTimer);
	},

	_onKeyPress:function(e,O)
	{
	 if((this.lastKeyNum==38) || (this.lastKeyNum==40))
	  this._moveIndex(this.lastMoveIndex, O);
     if(!e)
      window.event.cancelBubble = true;
     else
      e.stopPropagation();

	},

	_moveIndex:function(pos,O)
	{
	 this.selectedIndex+=pos;
	 if(this.selectedIndex < 0)
	  this.selectedIndex = this.results.length-1;
	 else if(this.selectedIndex >= this.results.length)
	  this.selectedIndex = 0;
	 this._hintElem(this.results[this.selectedIndex],true,O);
	},

	_execQry:function(O)
	{
	 this.selectedIndex = -1;
	 if(!O.value)
	  return this.hideResults();
	 var oThis = this;
	 this.sh.OnError = function(e,s){return false;}
	 this.sh.OnOutput = function(o,a){
		 if(!a)
		 {
		  O.data = null;
		  return oThis.hideResults();
		 }
		 if(O.esHinst.retArrName && !a[O.esHinst.retArrName])
		 {
		  O.data = null;
		  return oThis.hideResults();
		 }
		 var items = O.esHinst.retArrName ? a[O.esHinst.retArrName] : a;
		 oThis.results = new Array();
		 if(items.length)
		 {
		  if(O.onQueryResults) // custom list //
		  {
		   var resArr = new Array();
		   var retVal = new Array();
		   O.onQueryResults(items,resArr,retVal);
		   while(oThis.C.rows.length)
			oThis.C.deleteRow(0);
		   for(var c=0; c < resArr.length; c++)
		   {
			var d = oThis.C.insertRow(-1).insertCell(-1);
			d.className = 'item';
			d.id = items[c][O.esHinst.retValField];
			d.value = retVal[c];
			d.data = items[c];
			d.innerHTML = resArr[c];
			d.onmouseover = function(){oThis._hintElem(this,false,O);}
			d.addEventListener('mousedown',function(e){
				 oThis._hintElem(this,false,O);
				 if(!e) var e = window.event;
				 e.cancelBubble = true;
				 if(e.stopPropagation) e.stopPropagation();
				 if(O.onchange)
				  O.onchangeCallback = O.onchange;
				 if(O.onblur)
				  O.onblurCallback = O.onblur;
				 O.onchange = null;
				 O.onblur = null;
				},false);
			d.addEventListener('click',function(){oThis._selectElem(this,O);},false);
			d.addEventListener('mouseup',function(e){
				 if(O.onchangeCallback)
				  O.onchange = O.onchangeCallback;
				 if(O.onblurCallback)
				  O.onblur = O.onblurCallback;
				},false);
			oThis.results.push(d);
		   }
		  }
		  else // automatic list //
		  {
		   while(oThis.C.rows.length)
			oThis.C.deleteRow(0);
		   for(var c=0; c < items.length; c++)
		   {
			var d = oThis.C.insertRow(-1).insertCell(-1);
			d.className = 'item';
			d.id = items[c][O.esHinst.retValField];
			d.value = items[c][O.esHinst.retTxtField];
			d.data = items[c];
			d.innerHTML = items[c][O.esHinst.retTxtField];
			d.onmouseover = function(){oThis._hintElem(this,false,O);}
			d.addEventListener('mousedown',function(e){
				 oThis._hintElem(this,false,O);
				 if(!e) var e = window.event;
				 e.cancelBubble = true;
				 if(e.stopPropagation) e.stopPropagation();
				 if(O.onchange)
				  O.onchangeCallback = O.onchange;
				 if(O.onblur)
				  O.onblurCallback = O.onblur;
				 O.onchange = null;
				 O.onblur = null;
				},false);
			d.addEventListener('click',function(){oThis._selectElem(this,O);},false);
			d.addEventListener('mouseup',function(e){
				 if(O.onchangeCallback)
				  O.onchange = O.onchangeCallback;
				 if(O.onblurCallback)
				  O.onblur = O.onblurCallback;
				},false);
			oThis.results.push(d);
		   }
		  }
		  var xy = _getObjectPosition(O);
		  oThis.C.style.position = "absolute";
		  oThis.C.style.left = xy['x'];
		  oThis.C.style.top = 0;
		  oThis.C.style.visibility = "hidden";
		  oThis.C.style.display = "";
		  document.body.appendChild(oThis.C);

		  var SH = window.innerHeight ? window.innerHeight : document.body.clientHeight;

		  if((xy['y']+O.offsetHeight+oThis.C.offsetHeight+100) > SH)
		   oThis.C.style.top = xy['y']-oThis.C.offsetHeight;
		  else
		   oThis.C.style.top = xy['y']+O.offsetHeight;
		  
		  oThis.C.style.visibility='visible';
		  oThis.C.style.zIndex = 999999;
		 }
		 else
		 {
		  oThis.hideResults();
		 }
		}
	 this.sh.sendCommand(O.esHinst.startQry+(O.getAttribute('convertspecialchars') == "true" ? real_htmlspecialchars(O.value) : O.value)+O.esHinst.endQry);
	},

	_hintElem:function(elm,select,O)
	{
	 if(this.elemHinted)
	  this.elemHinted.className = 'item';
	 this.elemHinted = elm;
	 elm.className = 'itemselected';
	 if(select)
	 {
	  if(O.esHinst.outValField)
	   O.value = elm.data[O.esHinst.outValField];
	  else
	   O.value = elm.value;
	  O.data = elm.data;
	 }
	 this.selectedIndex = this.results.indexOf(elm);
	},

	_selectElem:function(elm,O)
	{
	 if(O.esHinst.outValField)
	  O.value = elm.data[O.esHinst.outValField];
	 else
	  O.value = elm.value;
	 O.data = elm.data;
	 this.hideResults();
	 if(O.onchange)
	  O.onchange();
	 O.onblur();
	},

	hideResults:function(){
	 this.C.style.display='none';
	},

	free:function(obj){
	 obj.onblur = null;
	 obj.onkeydown = null;
	 obj.onkeyup = null;
	 obj.onkeypress = null;
	}
}
//-------------------------------------------------------------------------------------------------------------------//
function _getObjectPosition(e)
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
  //left+= obj.scrollLeft ? obj.scrollLeft : 0;
  //top+= obj.scrollTop ? obj.scrollTop : 0;
 }

 return {x:left, y:top};
}
//-------------------------------------------------------------------------------------------------------------------//
