/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 29-01-2014
 #PACKAGE: glight-template
 #DESCRIPTION: GLight Template - Bank search object
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

function GLBankSearch(ed, modal)
{
 var oThis = this;
 this.ed = ed;
 this.ed.defaultValue = this.ed.value;
 this.modal = modal ? modal : "default";
 /* MODALS:
    default - list of names
	extended - list of names, address, phone and email
 */
 this.qryTimer = null;
 this.scrollTimer = null;

 this.results = new Array();
 this.liElements = new Array();
 this.lastLIhinted = null;
 this.selectedIndex = -1;

 this.O = document.createElement('DIV');
 this.O.className = "glbanksearch-container";

 this.ULDIV = document.createElement('DIV');
 this.ULDIV.className = "glbanksearch-menu-div"
 this.UL = document.createElement('UL');
 this.UL.className = "glbanksearch-menu";

 //this.Optbox = document.createElement('DIV');
 //this.Optbox.className = "glbanksearch-optbox";

 this.ULDIV.appendChild(this.UL);
 this.O.appendChild(this.ULDIV);
 //this.O.appendChild(this.Optbox);
 this.O.style.width = this.ed.offsetWidth+"px";
 document.body.appendChild(this.O);
 
 this.ed.onkeydown = function(evt){oThis.onKeyDown(evt,this);}
 this.ed.onkeyup = function(evt){oThis.onKeyUp(evt,this);}
 this.ed.onkeypress = function(evt){oThis.onKeyPress(evt,this);}
 this.ed.onclick = function(){oThis.execQry(true);}

 this.init();
}
//--------------------------------------------------------------------------------------------------------------------//
GLBankSearch.prototype.init = function()
{
 //this.initOptbox();
}
//--------------------------------------------------------------------------------------------------------------------//
GLBankSearch.prototype.initOptbox = function()
{
 var html = "<div class='glbanksearch-optbox-header'>";
 html+= "<span class='optbox-link'>Opzioni di ricerca &raquo;</span>";
 html+= "<span class='optbox-caption'>seleziona su quali campi effettuare la ricerca</span>";
 html+= "</div>";

 html+= "<div class='glbanksearch-optbox-body' style='display:none'>";
 html+= "<table width='100%' cellspacing='0' cellpadding='0' border='0'>";
 html+= "<tr><td width='70' align='center' valign='middle'><img src='"+ABSOLUTE_URL+"var/templates/glight/objects/contactsearch/img/search-opt.png'/></td>";
 html+= "<td valign='top'><div class='optbox-cbcontainer' style='width:"+(this.ed.offsetWidth-170)+"px'>";
 var options = [
	 ["code_str","codice"],
	 ["name","nome e cognome"],
	 ["address","indirizzo",true],
	 ["city","città",true],
	 ["phone,phone2,cell","telefono",true],
	 ["email","email",true],
	 ["fidelitycard","fidelity card"]
	];

 for(var c=0; c < options.length; c++)
 {
  html+= "<span class='checkbox'><input type='checkbox' class='checkbox'"+(options[c][2] ? " contactfields='"+options[c][0]+"'" : " fields='"+options[c][0]+"'");
  if(options[c][2])
  {
   if(this.contactFields.indexOf(options[c][0]) > -1)
	html+= " checked='true'";
  }
  else
  {
   if(this.fields.indexOf(options[c][0]) > -1)
	html+= " checked='true'";
  }
  html+= "/>"+options[c][1]+"</span>";
 }
 
 html+= "</div></td>";
 html+= "<td width='70' valign='middle' align='center' style='border-left:1px solid #e6e6e6'><img src='"+ABSOLUTE_URL+"var/templates/glight/objects/contactsearch/img/repeat-query.png'/><br/><span class='optbox-repeatqry-link'>Ripeti la ricerca</span></td></tr>";
 html+= "</table>";
 html+= "</div>"; // eof - glbanksearch-optbox-body

 this.Optbox.innerHTML = html;
 /* INJECT */
 this.optboxHdrDiv = this.Optbox.getElementsByTagName('DIV')[0];
 this.optboxBodyDiv = this.Optbox.getElementsByTagName('DIV')[1];
 this.optboxCBDiv = this.optboxBodyDiv.getElementsByTagName('DIV')[0];
 this.optboxLink = this.optboxHdrDiv.getElementsByTagName('SPAN')[0];
 this.optboxCaption = this.optboxHdrDiv.getElementsByTagName('SPAN')[1];
 this.optboxRepQryLink = this.optboxBodyDiv.getElementsByTagName('SPAN')[this.optboxBodyDiv.getElementsByTagName('SPAN').length-1];

 this.optboxLink.glcsHist = this;
 this.optboxLink.onclick = function(){this.glcsHist.toggleExpandOptbox();}

 this.optboxRepQryLink.glcsHist = this;
 this.optboxRepQryLink.onclick = function(){this.glcsHist.repeatQry();}

 this.optboxExpanded = false;

 this.optboxCBList = this.optboxCBDiv.getElementsByTagName('INPUT');
}
//--------------------------------------------------------------------------------------------------------------------//
GLBankSearch.prototype.toggleExpandOptbox = function()
{
 if(!this.optboxExpanded)
 {
  this.optboxCaption.style.visibility = "visible";
  this.optboxBodyDiv.style.display = "";
  this.optboxExpanded = true;
 }
 else
 {
  this.optboxCaption.style.visibility = "hidden";
  this.optboxBodyDiv.style.display = "none";
  this.optboxExpanded = false;
 }
}
//--------------------------------------------------------------------------------------------------------------------//
GLBankSearch.prototype.execQry = function(forceEmptyValue)
{
 if(!this.ed.value && !forceEmptyValue)
  return this.hide();
 var oThis = this;
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}

 if(this.ed.getAttribute('subjid'))
 {
  sh.OnOutput = function(o,a){
	 if(!a || !a['banks'])
	 {
	  oThis.empty("nessun risultato trovato");
	  oThis.results = new Array();
	  return;
	 }
	 oThis.empty();
	 oThis.results = a['banks'];
	 for(var c=0; c < a['banks'].length; c++)
	  oThis.addResult(a['banks'][c]);
	 oThis.show();
	}
  sh.sendCommand("dynarc item-info -ap rubrica -id '"+this.ed.getAttribute('subjid')+"' -extget banks");
 }
 else
 {
  sh.OnOutput = function(o,a){
	 if(!a)
	 {
	  oThis.empty("nessun risultato trovato");
	  oThis.results = new Array();
	  return;
	 }
	 oThis.empty();
	 oThis.results = a;
	 for(var c=0; c < a.length; c++)
	 {
	  a[c]['id'] = c;
	  oThis.addResult(a[c]);
	 }
	 oThis.show();
	}
  sh.sendCommand("companyprofile get-banks");
 }
}
//--------------------------------------------------------------------------------------------------------------------//
GLBankSearch.prototype.empty = function(msg)
{
 this.UL.innerHTML = "";
 this.liElements = new Array();
 if(msg)
  this.UL.innerHTML = "<li class='emptymessage'><i>"+msg+"</i></li>";
}
//--------------------------------------------------------------------------------------------------------------------//
GLBankSearch.prototype.addResult = function(data)
{
 var oThis = this;
 var li = document.createElement('LI');
 li.className = "extended";
 li.data = data;
 li.glcsHinst = this;
 li.onclick = function(){this.glcsHinst.select(this);}

 var html = "<div class='glbankinfo' style='width:"+(this.O.offsetWidth-50)+"px'>";
  html+= "<div class='glbank-name'>"+data['name']+"</div>";
  html+= "<div class='glbank-coords'>ABI:"+data['abi']+" CAB:"+data['cab']+" CC:"+data['cc']+"</div>";
 html+= "</div>";

 li.innerHTML = html;
 this.UL.appendChild(li);
 this.liElements.push(li);
 return li;
}
//--------------------------------------------------------------------------------------------------------------------//
GLBankSearch.prototype.select = function(li)
{
 this.ed.value = li.data['name'];
 this.ed.data = li.data;
 this.hide();
 if(this.ed.OnSearch)
  this.ed.OnSearch();
}
//--------------------------------------------------------------------------------------------------------------------//
GLBankSearch.prototype.hint = function(li)
{
 if(this.lastLIhinted)
  this.lastLIhinted.className = this.lastLIhinted.className.replace(' selected','');
 li.className = li.className+" selected";
 this.lastLIhinted = li;
}
//--------------------------------------------------------------------------------------------------------------------//
GLBankSearch.prototype.show = function()
{
 ACTIVE_GLBANKSEARCH = this;
 var pos = this.getObjectPosition(this.ed);
 this.O.style.left = pos.x+"px";
 this.O.style.top = (pos.y+this.ed.offsetHeight)+"px";
 this.O.style.visibility = "visible";
}
//--------------------------------------------------------------------------------------------------------------------//
GLBankSearch.prototype.hide = function()
{
 this.O.style.visibility = "hidden";
}
//--------------------------------------------------------------------------------------------------------------------//
GLBankSearch.prototype.getObjectPosition = function(e)
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
GLBankSearch.prototype.onKeyDown = function(evt,ed)
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
	 if(this.lastLIhinted)
	  this.select(this.lastLIhinted);
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
GLBankSearch.prototype.onKeyUp = function(evt,ed)
{
 if(this.scrollTimer)
  clearInterval(this.scrollTimer);
}
//--------------------------------------------------------------------------------------------------------------------//
GLBankSearch.prototype.onKeyPress = function(evt,ed)
{
 if((this.lastKeyNum==38) || (this.lastKeyNum==40))
  this.moveIndex(this.lastMoveIndex, ed);
 if(typeof(evt.stopPropagation) != "undefined")
  evt.stopPropagation();
 else
  evt.cancelBubble=true;
}
//--------------------------------------------------------------------------------------------------------------------//
GLBankSearch.prototype.moveIndex = function(pos,ed)
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
var ACTIVE_GLBANKSEARCH = null;
function hideActiveGLBankSearch()
{
 if(ACTIVE_GLBANKSEARCH)
  ACTIVE_GLBANKSEARCH.hide();
}
document.addEventListener ? document.addEventListener("mouseup",hideActiveGLBankSearch,false) : document.attachEvent("onmouseup",hideActiveGLBankSearch);

