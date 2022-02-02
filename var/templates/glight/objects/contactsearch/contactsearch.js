/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2015 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-02-2015
 #PACKAGE: glight-template
 #DESCRIPTION: GLight Template - Contact search object
 #VERSION: 2.3beta
 #CHANGELOG: 17-02-2015 : Aggiunto cod.fisc e p.iva sui campi di ricerca.
			 16-07-2014 : Bug fix su optbox.
			 15-03-2014 : Bug fix vari.
 #TODO:
 
*/

function GLContactSearch(ed, modal, fields, contactFields)
{
 var oThis = this;
 this.ed = ed;
 this.ed.defaultValue = this.ed.value;
 this.modal = modal ? modal : "default";
 /* MODALS:
    default - list of names
	extended - list of names, address, phone and email
 */
 this.fields = fields ? fields : "code_str,name";
 this.contactFields = contactFields ? contactFields : "email";
 this.catId = ed.getAttribute('catid') ? ed.getAttribute('catid') : 0;
 this.catTag = ed.getAttribute('ct') ? ed.getAttribute('ct') : "";
 this.qryTimer = null;
 this.scrollTimer = null;
 this.limit = this.ed.getAttribute('limit') ? this.ed.getAttribute('limit') : 10;
 this.results = new Array();
 this.liElements = new Array();
 this.lastLIhinted = null;
 this.selectedIndex = -1;

 this.O = document.createElement('DIV');
 this.O.className = "glcontactsearch-container";

 this.ULDIV = document.createElement('DIV');
 this.ULDIV.className = "glcontactsearch-menu-div"
 this.UL = document.createElement('UL');
 this.UL.className = "glcontactsearch-menu";

 this.Optbox = document.createElement('DIV');
 this.Optbox.className = "glcontactsearch-optbox";

 this.ULDIV.appendChild(this.UL);
 this.O.appendChild(this.ULDIV);
 if(!this.ed.getAttribute('nooptions'))
  this.O.appendChild(this.Optbox);
 this.O.style.width = this.ed.offsetWidth+"px";
 document.body.appendChild(this.O);
 
 this.ed.onkeydown = function(evt){oThis.onKeyDown(evt,this);}
 this.ed.onkeyup = function(evt){oThis.onKeyUp(evt,this);}
 this.ed.onkeypress = function(evt){oThis.onKeyPress(evt,this);}

 this.init();
}
//--------------------------------------------------------------------------------------------------------------------//
GLContactSearch.prototype.init = function()
{
 this.initOptbox();
}
//--------------------------------------------------------------------------------------------------------------------//
GLContactSearch.prototype.initOptbox = function()
{
 var oThis = this;

 var html = "<div class='glcontactsearch-optbox-header'>";
 html+= "<span class='optbox-link'>Opzioni di ricerca &raquo;</span>";
 html+= "<span class='optbox-caption'>seleziona su quali campi effettuare la ricerca</span>";
 html+= "</div>";

 html+= "<div class='glcontactsearch-optbox-body' style='display:none'>";
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
	 ["fidelitycard","fidelity card"],
	 ["taxcode,vatnumber","cod. fisc e p.iva"]
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
 html+= "</div>"; // eof - glcontactsearch-optbox-body

 this.Optbox.innerHTML = html;
 this.Optbox.onmouseover = function(){oThis.canHide=false;}
 this.Optbox.onmouseout = function(){oThis.canHide=true;}

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
GLContactSearch.prototype.toggleExpandOptbox = function()
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
GLContactSearch.prototype.execQry = function()
{
 if(!this.ed.value)
  return this.hide();
 var oThis = this;
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a || !a['results'])
	 {
	  oThis.empty("nessun risultato trovato");
	  oThis.results = new Array();
	  return;
	 }
	 oThis.empty();
	 oThis.results = a['results'];
	 for(var c=0; c < a['results'].length; c++)
	  oThis.addResult(a['results'][c]);
	 oThis.show();
	}

 var cmd = "fastfind contacts";
 if(this.catId) cmd+= " -cat '"+this.catId+"'";
 else if(this.catTag) cmd+= " -ct '"+this.catTag+"'";
 cmd+= " -fields '"+this.fields+"' --contact-fields '"+this.contactFields+"' `"+this.ed.value+"` -limit "+this.limit;

 sh.sendCommand(cmd);
}
//--------------------------------------------------------------------------------------------------------------------//
GLContactSearch.prototype.repeatQry = function()
{
 this.fields = "";
 this.contactFields = "";

 for(var c=0; c < this.optboxCBList.length; c++)
 {
  var inp = this.optboxCBList[c];
  if(!inp.checked) continue;
  if(inp.getAttribute('fields'))
   this.fields+= ","+inp.getAttribute('fields');
  else if(inp.getAttribute('contactfields'))
   this.contactFields+= ","+inp.getAttribute('contactfields');
 }
 if(this.fields) this.fields = this.fields.substr(1);
 if(this.contactFields) this.contactFields = this.contactFields.substr(1);
 this.toggleExpandOptbox();
 this.execQry();
}
//--------------------------------------------------------------------------------------------------------------------//
GLContactSearch.prototype.empty = function(msg)
{
 this.UL.innerHTML = "";
 this.liElements = new Array();
 if(msg)
  this.UL.innerHTML = "<li class='emptymessage'><i>"+msg+"</i></li>";
 /*var list = this.UL.getElementsByTagName("LI");
 for(var c=0; c < list.length; c++)
  this.UL.removeChild(list[c]);*/
}
//--------------------------------------------------------------------------------------------------------------------//
GLContactSearch.prototype.addResult = function(data)
{
 var oThis = this;
 var li = document.createElement('LI');
 li.className = "extended";
 li.data = data;
 li.glcsHinst = this;
 li.onclick = function(){this.glcsHinst.select(this);}

 if(this.modal == "extended")
  var html = "<div class='glcontextinfo-left' style='width:"+(this.O.offsetWidth-150)+"px'>";
 else
  var html = "<div class='glcontextinfo-left' style='width:"+(this.O.offsetWidth-50)+"px'>";

  html+= "<div class='glcontact-name'><span class='glcontact-code'>"+data['code_str']+"</span> - "+data['name']+"</div>";
  var addr = "";
  if(data['contacts'])
   addr = data['contacts'][0]['address']+" "+data['contacts'][0]['city'];
  html+= "<div class='glcontact-address'>"+addr+"</div>";
 html+= "</div>";

 if(this.modal == "extended")
 {
  html+= "<div class='glcontextinfo-right'>";
  var phone = "";
  var email = "";
  if(data['contacts'])
  {
   if(data['contacts'][0]['phone'])
	phone = data['contacts'][0]['phone'];
   else if(data['contacts'][0]['phone2'])
	phone = data['contacts'][0]['phone2'];
   else if(data['contacts'][0]['cell'])
	phone = data['contacts'][0]['cell'];
   if(data['contacts'][0]['email'])
	email = data['contacts'][0]['email'];
  }
  html+= "<div class='glcontact-phone'>"+phone+"</div>";
  html+= "<div class='glcontact-email'>"+email+"</div>";
  html+= "</div>";
 }

 li.innerHTML = html;
 this.UL.appendChild(li);
 this.liElements.push(li);
 return li;
}
//--------------------------------------------------------------------------------------------------------------------//
GLContactSearch.prototype.select = function(li)
{
 this.ed.value = li.data['name'];
 this.ed.data = li.data;
 this.hide();
 if(this.ed.OnSearch)
  this.ed.OnSearch();
}
//--------------------------------------------------------------------------------------------------------------------//
GLContactSearch.prototype.hint = function(li)
{
 if(this.lastLIhinted)
  this.lastLIhinted.className = this.lastLIhinted.className.replace(' selected','');
 li.className = li.className+" selected";
 this.lastLIhinted = li;
}
//--------------------------------------------------------------------------------------------------------------------//
GLContactSearch.prototype.show = function()
{
 ACTIVE_GLCONTACTSEARCH = this;
 var pos = this.getObjectPosition(this.ed);
 this.O.style.left = pos.x+"px";
 this.O.style.top = (pos.y+this.ed.offsetHeight)+"px";
 this.O.style.visibility = "visible";
 this.canHide = true;
}
//--------------------------------------------------------------------------------------------------------------------//
GLContactSearch.prototype.hide = function()
{
 if(this.canHide)
  this.O.style.visibility = "hidden";
}
//--------------------------------------------------------------------------------------------------------------------//
GLContactSearch.prototype.getObjectPosition = function(e)
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
GLContactSearch.prototype.onKeyDown = function(evt,ed)
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
GLContactSearch.prototype.onKeyUp = function(evt,ed)
{
 if(this.scrollTimer)
  clearInterval(this.scrollTimer);
}
//--------------------------------------------------------------------------------------------------------------------//
GLContactSearch.prototype.onKeyPress = function(evt,ed)
{
 if((this.lastKeyNum==38) || (this.lastKeyNum==40))
  this.moveIndex(this.lastMoveIndex, ed);
 if(typeof(evt.stopPropagation) != "undefined")
  evt.stopPropagation();
 else
  evt.cancelBubble=true;
}
//--------------------------------------------------------------------------------------------------------------------//
GLContactSearch.prototype.moveIndex = function(pos,ed)
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
var ACTIVE_GLCONTACTSEARCH = null;
function hideActiveGLContactSearch()
{
 if(ACTIVE_GLCONTACTSEARCH)
  ACTIVE_GLCONTACTSEARCH.hide();
}
document.addEventListener ? document.addEventListener("mouseup",hideActiveGLContactSearch,false) : document.attachEvent("onmouseup",hideActiveGLContactSearch);

