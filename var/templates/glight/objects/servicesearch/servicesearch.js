/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-09-2014
 #PACKAGE: glight-template
 #DESCRIPTION: GLight Template - Service search object
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO: 
 
*/

function GLServiceSearch(ed, modal, fields)
{
 var oThis = this;
 this.ed = ed;
 this.ed.defaultValue = this.ed.value;
 this.modal = modal ? modal : "default";
 /* MODALS:
    default - default modality
	barcode - barcode modality
 */
 this.fields = fields ? fields : "code_str,name";
 this.at = ed.getAttribute('at') ? ed.getAttribute('at') : "gserv";
 this.ap = ed.getAttribute('ap') ? ed.getAttribute('ap') : "";
 this.qryTimer = null;
 this.scrollTimer = null;
 this.limit = ed.getAttribute('limit') ? ed.getAttribute('limit') : 10;
 this.qryInProgress = false;

 this.results = new Array();
 this.liElements = new Array();
 this.lastLIhinted = null;
 this.selectedIndex = -1;

 this.O = document.createElement('DIV');
 this.O.className = "glservicesearch-container";

 this.ULDIV = document.createElement('DIV');
 this.ULDIV.className = "glservicesearch-menu-div"
 this.UL = document.createElement('UL');
 this.UL.className = "glservicesearch-menu";


 this.ULDIV.appendChild(this.UL);
 this.O.appendChild(this.ULDIV);

 this.O.style.width = this.ed.offsetWidth+"px";
 document.body.appendChild(this.O);
 
 this.ed.onkeydown = function(evt){oThis.onKeyDown(evt,this);}
 this.ed.onkeyup = function(evt){oThis.onKeyUp(evt,this);}
 this.ed.onkeypress = function(evt){oThis.onKeyPress(evt,this);}
 this.ed.onclick = function(){
	 if(this.getAttribute('emptyonclick'))
	  this.value = "";
	 else
	  oThis.execQry(true);
	}

 this.init();
}
//--------------------------------------------------------------------------------------------------------------------//
GLServiceSearch.prototype.init = function()
{

}
//--------------------------------------------------------------------------------------------------------------------//
GLServiceSearch.prototype.toggleExpandOptbox = function()
{
}
//--------------------------------------------------------------------------------------------------------------------//
GLServiceSearch.prototype.execQry = function(autoselect)
{
 if(!this.ed.value)
 {
  if((this.ed.value != this.ed.defaultValue) && this.ed.OnSearch)
   this.ed.OnSearch();
  return this.hide();
 }

 if(this.qryTimer)
  clearInterval(this.qryTimer);

 var oThis = this;
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 oThis.qryInProgress = false;
	 if(!a || !a['results'])
	 {
	  oThis.empty("nessun servizio trovato");
	  oThis.results = new Array();
	  return;
	 }
	 oThis.empty();
	 oThis.results = a['results'];
	 var lastLI = null;
	 for(var c=0; c < a['results'].length; c++)
	  lastLI = oThis.addResult(a['results'][c]);
	 if((a['results'].length == 1) && autoselect)
	  oThis.select(lastLI);
	 else
	  oThis.show();
	}
 this.qryInProgress = true;
 sh.sendCommand("fastfind services"+(this.ap ? " -ap '"+this.ap+"'" : "")+" -fields '"+this.fields+"' `"+this.ed.value+"` -limit "+this.limit);
}
//--------------------------------------------------------------------------------------------------------------------//
GLServiceSearch.prototype.repeatQry = function()
{
 this.fields = "";

 for(var c=0; c < this.optboxCBList.length; c++)
 {
  var inp = this.optboxCBList[c];
  if(!inp.checked) continue;
  if(inp.getAttribute('fields'))
   this.fields+= ","+inp.getAttribute('fields');
 }
 if(this.fields) this.fields = this.fields.substr(1);
 this.toggleExpandOptbox();
 this.execQry();
}
//--------------------------------------------------------------------------------------------------------------------//
GLServiceSearch.prototype.empty = function(msg)
{
 this.UL.innerHTML = "";
 this.liElements = new Array();
 if(msg)
  this.UL.innerHTML = "<li class='emptymessage'><i>"+msg+"</i></li>";
}
//--------------------------------------------------------------------------------------------------------------------//
GLServiceSearch.prototype.addResult = function(data)
{
 var oThis = this;
 var li = document.createElement('LI');
 li.className = "extended";
 li.data = data;
 li.glcsHinst = this;
 li.onclick = function(){this.glcsHinst.select(this);}

 var html = "<div class='glserviceinfo' style='width:"+(this.O.offsetWidth-20)+"px'>";
  html+= "<div class='glservice-name'><span class='glservice-code'>"+data['code_str']+"</span> - "+data['name']+"</div>";
  html+= "<div class='glservice-coords'>prezzo:"+formatCurrency(data['baseprice'])+"</div>";
 html+= "</div>";

 li.innerHTML = html;
 this.UL.appendChild(li);
 this.liElements.push(li);
 return li;
}
//--------------------------------------------------------------------------------------------------------------------//
GLServiceSearch.prototype.select = function(li)
{
 this.ed.value = li.data['name'];
 this.ed.data = li.data;
 this.hide();
 if(this.ed.OnSearch)
  this.ed.OnSearch();
 if(this.modal == "barcode")
 {
  // auto-empty
  this.ed.value = "";
  this.ed.data = null;
 }
}
//--------------------------------------------------------------------------------------------------------------------//
GLServiceSearch.prototype.hint = function(li)
{
 if(this.lastLIhinted)
  this.lastLIhinted.className = this.lastLIhinted.className.replace(' selected','');
 li.className = li.className+" selected";
 this.lastLIhinted = li;
}
//--------------------------------------------------------------------------------------------------------------------//
GLServiceSearch.prototype.show = function()
{
 ACTIVE_GLSERVICESEARCH = this;
 var pos = this.getObjectPosition(this.ed);
 this.O.style.left = pos.x+"px";
 this.O.style.top = (pos.y+this.ed.offsetHeight)+"px";
 this.O.style.visibility = "visible";
}
//--------------------------------------------------------------------------------------------------------------------//
GLServiceSearch.prototype.hide = function()
{
 this.O.style.visibility = "hidden";
}
//--------------------------------------------------------------------------------------------------------------------//
GLServiceSearch.prototype.getObjectPosition = function(e)
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
//--------------------------------------------------------------------------------------------------------------------//
GLServiceSearch.prototype.onKeyDown = function(evt,ed)
{
 var oThis = this;
 if(this.qryTimer)
  clearInterval(this.qryTimer);
 if(this.scrollTimer)
  clearInterval(this.scrollTimer);

 var keyNum = window.event ? evt.keyCode : evt.which;
 if(keyNum == 9) evt.preventDefault();
 switch(keyNum)
 {
  case 38 : { // UP //
	 if(!this.results.length)
	  return;
	 this.lastMoveIndex = -1;
	} break;

  case 40 : { // DOWN //
	 if(!this.results.length)
	  return;
	 this.lastMoveIndex = 1;
	} break;

  case 13 : { // ENTER //
	 if((this.O.style.visibility == "visible") && this.lastLIhinted)
	  this.select(this.lastLIhinted);
	 else
	  this.execQry(true);
	} break;

  default : {
	 var isprintable=/[^ -~]/;
	 var ok = false;
	 if((keyNum == 8) || (keyNum == 46)) // canc and backspace //
	  ok = true;
     if((!isprintable.test(String.fromCharCode(keyNum))) || ok)
	  this.qryTimer = window.setTimeout(function(){oThis.execQry();},250);
	} break;
 }
 this.lastKeyNum = keyNum;
 if((keyNum == 9) || (keyNum == 27))
 {
  if(keyNum == 27)
   ed.value = ed.defaultValue;
  this.hide();
 }
 else
 {
  if(typeof(evt.stopPropagation) != "undefined")
   evt.stopPropagation();
  else
   evt.cancelBubble=true;
 }
}
//--------------------------------------------------------------------------------------------------------------------//
GLServiceSearch.prototype.onKeyUp = function(evt,ed)
{
 if(this.scrollTimer)
  clearInterval(this.scrollTimer);
}
//--------------------------------------------------------------------------------------------------------------------//
GLServiceSearch.prototype.onKeyPress = function(evt,ed)
{
 if((this.lastKeyNum==38) || (this.lastKeyNum==40))
  this.moveIndex(this.lastMoveIndex, ed);
 if(typeof(evt.stopPropagation) != "undefined")
  evt.stopPropagation();
 else
  evt.cancelBubble=true;
}
//--------------------------------------------------------------------------------------------------------------------//
GLServiceSearch.prototype.moveIndex = function(pos,ed)
{
 this.selectedIndex+=pos;
 if(this.selectedIndex < 0)
  this.selectedIndex = this.results.length-1;
 else if(this.selectedIndex >= this.results.length)
  this.selectedIndex = 0;
 this.hint(this.liElements[this.selectedIndex]);
}
//--------------------------------------------------------------------------------------------------------------------//
//--------------------------------------------------------------------------------------------------------------------//
//--------------------------------------------------------------------------------------------------------------------//
var ACTIVE_GLSERVICESEARCH = null;
function hideActiveGLServiceSearch()
{
 if(ACTIVE_GLSERVICESEARCH)
  ACTIVE_GLSERVICESEARCH.hide();
}
document.addEventListener ? document.addEventListener("mouseup",hideActiveGLServiceSearch,false) : document.attachEvent("onmouseup",hideActiveGLServiceSearch);

