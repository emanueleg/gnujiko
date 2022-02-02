/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 31-01-2014
 #PACKAGE: glight-template
 #DESCRIPTION: GLight Template - Product search object
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

function GLProductSearch(ed, modal, fields)
{
 var oThis = this;
 this.ed = ed;
 this.ed.defaultValue = this.ed.value;
 this.modal = modal ? modal : "default";
 /* MODALS:
    default - default modality
	barcode - barcode modality
 */
 this.fields = fields ? fields : "code_str,name,barcode";
 this.at = ed.getAttribute('at') ? ed.getAttribute('at') : "gmart";
 this.ap = ed.getAttribute('ap') ? ed.getAttribute('ap') : "";
 this.qryTimer = null;
 this.scrollTimer = null;
 this.limit = 10;
 this.qryInProgress = false;

 this.results = new Array();
 this.liElements = new Array();
 this.lastLIhinted = null;
 this.selectedIndex = -1;

 this.O = document.createElement('DIV');
 this.O.className = "glproductsearch-container";

 this.ULDIV = document.createElement('DIV');
 this.ULDIV.className = "glproductsearch-menu-div"
 this.UL = document.createElement('UL');
 this.UL.className = "glproductsearch-menu";

 this.Optbox = document.createElement('DIV');
 this.Optbox.className = "glproductsearch-optbox";

 this.ULDIV.appendChild(this.UL);
 this.O.appendChild(this.ULDIV);
 //this.O.appendChild(this.Optbox);
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
GLProductSearch.prototype.init = function()
{
 //this.initOptbox();
}
//--------------------------------------------------------------------------------------------------------------------//
GLProductSearch.prototype.initOptbox = function()
{
 var html = "<div class='glproductsearch-optbox-header'>";
 html+= "<span class='optbox-link'>Opzioni di ricerca &raquo;</span>";
 html+= "<span class='optbox-caption'>seleziona su quali campi effettuare la ricerca</span>";
 html+= "</div>";

 html+= "<div class='glproductsearch-optbox-body' style='display:none'>";
 html+= "<table width='100%' cellspacing='0' cellpadding='0' border='0'>";
 html+= "<tr><td width='70' align='center' valign='middle'><img src='"+ABSOLUTE_URL+"var/templates/glight/objects/productsearch/img/search-opt.png'/></td>";
 html+= "<td valign='top'><div class='optbox-cbcontainer' style='width:"+(this.ed.offsetWidth-170)+"px'>";
 var options = [
	 ["code_str","codice"],
	 ["name","descrizione"],
	 ["barcode","barcode"],
	 ["manufacturer_code","cod. produttore"]
	];

 for(var c=0; c < options.length; c++)
 {
  html+= "<span class='checkbox'><input type='checkbox' class='checkbox' fields='"+options[c][0]+"'";
  if(this.fields.indexOf(options[c][0]) > -1)
   html+= " checked='true'";
  html+= "/>"+options[c][1]+"</span>";
 }
 
 html+= "</div></td>";
 html+= "<td width='70' valign='middle' align='center' style='border-left:1px solid #e6e6e6'><img src='"+ABSOLUTE_URL+"var/templates/glight/objects/productsearch/img/repeat-query.png'/><br/><span class='optbox-repeatqry-link'>Ripeti la ricerca</span></td></tr>";
 html+= "</table>";
 html+= "</div>"; // eof - glproductsearch-optbox-body

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
GLProductSearch.prototype.toggleExpandOptbox = function()
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
GLProductSearch.prototype.execQry = function(autoselect)
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
	  oThis.empty("nessun prodotto trovato");
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
 sh.sendCommand("fastfind products -at '"+this.at+"'"+(this.ap ? " -ap '"+this.ap+"'" : "")+" -fields '"+this.fields+"' `"+this.ed.value+"` -limit "+this.limit);
}
//--------------------------------------------------------------------------------------------------------------------//
GLProductSearch.prototype.repeatQry = function()
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
GLProductSearch.prototype.empty = function(msg)
{
 this.UL.innerHTML = "";
 this.liElements = new Array();
 if(msg)
  this.UL.innerHTML = "<li class='emptymessage'><i>"+msg+"</i></li>";
}
//--------------------------------------------------------------------------------------------------------------------//
GLProductSearch.prototype.addResult = function(data)
{
 var oThis = this;
 var li = document.createElement('LI');
 li.className = "extended";
 li.data = data;
 li.glcsHinst = this;
 li.onclick = function(){this.glcsHinst.select(this);}

 var html = "<div class='glproductinfo' style='width:"+(this.O.offsetWidth-20)+"px'>";
  html+= "<div class='glproduct-name'><span class='glproduct-code'>"+data['code_str']+"</span> - "+data['name']+"</div>";
  html+= "<div class='glproduct-coords'>barcode:"+data['barcode']+"</div>";
 html+= "</div>";

 li.innerHTML = html;
 this.UL.appendChild(li);
 this.liElements.push(li);
 return li;
}
//--------------------------------------------------------------------------------------------------------------------//
GLProductSearch.prototype.select = function(li)
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
GLProductSearch.prototype.hint = function(li)
{
 if(this.lastLIhinted)
  this.lastLIhinted.className = this.lastLIhinted.className.replace(' selected','');
 li.className = li.className+" selected";
 this.lastLIhinted = li;
}
//--------------------------------------------------------------------------------------------------------------------//
GLProductSearch.prototype.show = function()
{
 ACTIVE_GLPRODUCTSEARCH = this;
 var pos = this.getObjectPosition(this.ed);
 this.O.style.left = pos.x+"px";
 this.O.style.top = (pos.y+this.ed.offsetHeight)+"px";
 this.O.style.visibility = "visible";
}
//--------------------------------------------------------------------------------------------------------------------//
GLProductSearch.prototype.hide = function()
{
 this.O.style.visibility = "hidden";
}
//--------------------------------------------------------------------------------------------------------------------//
GLProductSearch.prototype.getObjectPosition = function(e)
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
GLProductSearch.prototype.onKeyDown = function(evt,ed)
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
GLProductSearch.prototype.onKeyUp = function(evt,ed)
{
 if(this.scrollTimer)
  clearInterval(this.scrollTimer);
}
//--------------------------------------------------------------------------------------------------------------------//
GLProductSearch.prototype.onKeyPress = function(evt,ed)
{
 if((this.lastKeyNum==38) || (this.lastKeyNum==40))
  this.moveIndex(this.lastMoveIndex, ed);
 if(typeof(evt.stopPropagation) != "undefined")
  evt.stopPropagation();
 else
  evt.cancelBubble=true;
}
//--------------------------------------------------------------------------------------------------------------------//
GLProductSearch.prototype.moveIndex = function(pos,ed)
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
var ACTIVE_GLPRODUCTSEARCH = null;
function hideActiveGLProductSearch()
{
 if(ACTIVE_GLPRODUCTSEARCH)
  ACTIVE_GLPRODUCTSEARCH.hide();
}
document.addEventListener ? document.addEventListener("mouseup",hideActiveGLProductSearch,false) : document.attachEvent("onmouseup",hideActiveGLProductSearch);

