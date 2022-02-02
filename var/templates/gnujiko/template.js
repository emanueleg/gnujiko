/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-05-2017
 #PACKAGE: gnujiko-template
 #DESCRIPTION: Ultimate default template for all applications, widgets and popup-message.
 #VERSION: 2.6beta
 #CHANGELOG: 13-05-2017 : Bug fix getCellByField on initSortableTable.
			 07-05-2017 : Integrato con Header, aggiunto event OnSearch.
			 23-04-2017 : Bug-fix ed aggiunta funzione getRows.
			 19-03-2017 : Aggiornata funzione initSortableTable
			 16-09-2016 : Bug fix initEd dropdown.
			 12-09-2016 : Possibilita di ridimensionare fckeditor.
 #TODO:
 
*/

function GnujikoTemplate()
{
 this.AppBasePath = "";
 this.Modal = "APPLICATION";

 this.URLVARS = this.getVars();

 this.activePopupMenu = null;
 this.overPopupMenu = false;

 this.MouseHandlers = new Array();

 /* EVENTS */
 this.OnInit = null;
 this.OnLoad = null;
 this.OnExit = null;
 this.OnSave = null;
 this.OnResize = function(){}; 		// RE-WRITABLE EVENT
 this.OnScroll = function(){}; 		// RE-WRITABLE EVENT
 this.OnSearch = function(ed){};	// RE-WRITABLE EVENT
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.init = function()
{
 var oThis = this;
 if(this.OnInit) this.OnInit();
 if(this.OnLoad) this.OnLoad();

 document.body.addEventListener ? document.body.addEventListener("resize",gnujikoTemplateBodyResize,false) : document.body.attachEvent("onresize",gnujikoTemplateBodyResize);
 document.addEventListener ? document.addEventListener("scroll",gnujikoTemplateScroll,false) : document.attachEvent("onscroll",gnujikoTemplateScroll);
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.registerMouseHandler = function()
{
 var h = new GnujikoTemplateMouseHandler();
 this.MouseHandlers.push(h);
 return h;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype._onmouseup = function(ev)
{
 if(this.activePopupMenu && !this.overPopupMenu) this.activePopupMenu.hide();
 if(this.glApplicationMenu) this.glApplicationMenu.hide();

 for(var c=0; c < this.MouseHandlers.length; c++)
  this.MouseHandlers[c]._onmouseup(ev, ev.clientX, ev.clientY);
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype._onmousedown = function(ev)
{
 for(var c=0; c < this.MouseHandlers.length; c++)
  this.MouseHandlers[c]._onmousedown(ev, ev.clientX, ev.clientY);
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype._onmousemove = function(ev)
{
 for(var c=0; c < this.MouseHandlers.length; c++)
  this.MouseHandlers[c]._onmousemove(ev, ev.clientX, ev.clientY);
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype._onwindowresize = function(ev)
{
 var scrW = window.innerWidth ? window.innerWidth : (document.all ? document.body.clientWidth : 0);
 var scrH = window.innerHeight ? window.innerHeight : (document.all ? document.body.clientHeight : 0);
 this.OnResize(scrW,scrH);
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype._onwindowscroll = function(ev)
{
 this.OnScroll(window.scrollX, window.scrollY);
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.getAbsPos = function(e)
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
GnujikoTemplate.prototype.initEd = function(ed, type, extraParams)
{
 if(typeof(ed) == "string")
  var ed = document.getElementById(ed);
 var oThis = this;
 switch(type)
 {
  case 'dropdown' : {
	 var popupmenu = ed.getAttribute("connect") ? document.getElementById(ed.getAttribute("connect")) : null;
	 if(!popupmenu) return alert("GnujikoTemplate Error: Button #"+ed.id+" is not connected to any menu.");
	 this.initPopupMenu(popupmenu, ed).OnSelect = function(li){
		 var span = li.getElementsByTagName('SPAN');
		 ed.value = span[0] ? span[0].textContent : li.textContent;
		 ed.setAttribute('retval',li.getAttribute('value'));
		 if(typeof(ed.onchange) == "function")
		  ed.onchange();
		}
	 ed.getValue = function(){return this.getAttribute('retval') ? this.getAttribute('retval') : "";}
	 ed.setValue = function(title, value){this.value = title; this.setAttribute('retval',value);}
	 ed.getItemAttribute = function(attr){
		 if(!this.popupmenu.selectedItem)
		  return null;
		 return this.popupmenu.selectedItem.getAttribute(attr);
		}
	 if((!ed.value || (ed.value == "")) && ed.getAttribute('retval'))
	 {
	  var li = ed.popupmenu.getItemByValue(ed.getAttribute('retval'));
	  if(li)
	   ed.popupmenu.setSelectedItem(li);
	 }
	 ed.oldValue = ed.value;
	 ed.oldRetVal = ed.getAttribute('retval');
	 ed.readOnly = true;

	 // Add item
	 ed.addItem = function(title, value, icon, id, selected, insertBefore){
		 var li = document.createElement('LI');
		 var html = "";
		 if(icon)		html = "<img src='"+icon+"' class='icon'/>";
		 html+= "<span>"+title+"</span>";
		 if(id)		li.id = id;
		 if(value)	li.setAttribute('value',value);
		 li.innerHTML = html;
		 if(insertBefore)
		  this.popupmenu.insertBefore(li,insertBefore);
		 else
		  this.popupmenu.appendChild(li);
		 if(selected)
		  this.popupmenu.setSelectedItem(li);
		 return li;
		}

	 // Add separator
	 ed.addSeparator = function(){
		}

	} break;

  //-------------------------------------------------------------------------------------------------------//

  case "date" : case "gcal" : {
	 if(!this.GCal)
	  this.GCal = new GCal();
	 ed.GCal = this.GCal;
	 ed.onclick = function(){oThis.showCal(this);}
	 ed.setDate = function(datestr){
		 this.isodatetime = datestr ? datestr : "";
		 this.isodate = this.isodatetime ? this.isodatetime.substr(0,10) : "";
		 if(this.isodate)
		 {
		  var date = new Date();
		  date.setFromISO(this.isodate);
		  this.value = date.printf(this.getAttribute('format') ? this.getAttribute('format') : 'd/m/Y');
		 }
		 else
		  this.value = "";
		}
	 ed.setDateTime = function(datetimestr){
		 this.isodatetime = datetimestr ? datetimestr : "";
		 this.isodate = this.isodatetime ? this.isodatetime.substr(0,10) : "";
		 if(this.isodate)
		 {
		  var date = new Date();
		  date.setFromISO(this.isodatetime);
		  this.value = date.printf(this.getAttribute('format') ? this.getAttribute('format') : 'd/m/Y H:i');
		 }
		 else
		  this.value = "";
		}
	 ed.onchange = function(){
		 this.isodate = this.value ? strdatetime_to_iso(this.value.substr(0,10)) : "";
		 if(this.isodate) this.isodate = this.isodate.substr(0,10);
		 this.isodatetime = this.value ? strdatetime_to_iso(this.value) : "";
		 if(typeof(this.OnDateChange) == "function")
		  this.OnDateChange(this.isodate);
		}
	 ed.isodate = ed.value ? strdatetime_to_iso(ed.value.substr(0,10)) : "";
	 if(ed.isodate) ed.isodate = ed.isodate.substr(0,10);
	 ed.isodatetime = ed.value ? strdatetime_to_iso(ed.value) : "";
	} break;

  //-------------------------------------------------------------------------------------------------------//

  case "time" : {
	 ed.onchange = function(){
	  var tmp = parseTime(this.value);
	  this.value = tmp.hh+":"+tmp.mm;
	 }
	} break;

  //-------------------------------------------------------------------------------------------------------//

  case "timelength" : {
	 ed.onchange = function(){
		 this.value = timelength_to_str(parse_timelength(this.value));
		 if(typeof(this.OnChange) == "function")
		  this.OnChange();
		}

	 ed.getValue = function(){return parse_timelength(this.value)/60;}
	} break;

  //-------------------------------------------------------------------------------------------------------//

  case "currency" : {
	 ed.onchange = function(){
		 this.value = formatCurrency(parseCurrency(this.value));
		 if(typeof(ed.OnChange) == "function")
		  ed.OnChange(parseCurrency(this.value));
		}
	 ed.getValue = function(){return parseCurrency(this.value);}
	 ed.setValue = function(val){this.value = formatCurrency(parseCurrency(val));}
	} break;

  //-------------------------------------------------------------------------------------------------------//

  case "contactextended" : {
	 var glCS = new GLContactSearch(ed,ed.getAttribute('modal'),ed.getAttribute('fields'),ed.getAttribute('contactfields'));
	 ed.oldvalue = ed.value;

	 ed.onmousedown = function(ev){
		 if(this.getAttribute('disablerightbtn') == 'true')
		  return;
		 var mouseX = ev.pageX ? ev.pageX : ev.clientX;
		 var mouseY = ev.pageY ? ev.pageY : ev.clientY;
		 var pos = _getObjectPosition(this);
		 var mX = mouseX - pos['x'];
		 var mY = mouseY - pos['y'];
		 if(mX > (this.offsetWidth-22))
		 {
		  var sh = new GShell();
		  sh.ed = this;
		  sh.OnError = function(err){alert(err);}
		  sh.OnOutput = function(o,a){
			 if(!a) return;
			 this.ed.value = a['name'];
			 this.ed.data = a;
			 if(this.ed.onchange)
			  this.ed.onchange();
			}
		  if(this.value && this.getId())
		   sh.sendCommand("gframe -f rubrica.edit -params `id="+this.getId()+"`");
		  else if(this.value)
		   sh.sendCommand("gframe -f rubrica.new -c `"+this.value+"`"+(this.getAttribute('ct') ? " -params `ct="+this.getAttribute('ct')+"`" : ""));
		 }
		}

	 ed.getId = function(){
		 if(this.data)
		  return this.data['id'];
		 else if((this.value == this.oldvalue) && this.getAttribute('refid'))
		  return this.getAttribute('refid');
		 return 0;
		}

	 ed.OnSearch = function(){ /* REWRITABLE EVENT */ }
 
	} break;

  //-------------------------------------------------------------------------------------------------------//

  case "fckeditor" : {
	 var sSkinPath = ABSOLUTE_URL+"var/objects/fckeditor/editor/skins/office2003/";
	 var oFCKeditor = new FCKeditor(ed.id) ;
	 oFCKeditor.ToolbarSet = extraParams ? extraParams : "Default";
	 oFCKeditor.BasePath	= BASE_PATH+"var/objects/fckeditor/";
	 oFCKeditor.Config['SkinPath'] = sSkinPath ;
	 oFCKeditor.Config['PreloadImages'] =
				sSkinPath + 'images/toolbar.start.gif' + ';' +
				sSkinPath + 'images/toolbar.end.gif' + ';' +
				sSkinPath + 'images/toolbar.bg.gif' + ';' +
				sSkinPath + 'images/toolbar.buttonarrow.gif' ;
	 if(ed.getAttribute('extracss'))
	  oFCKeditor.Config['EditorAreaStyles'] = ed.getAttribute('extracss');
	 oFCKeditor.Height = ed.offsetHeight;
	 oFCKeditor.ReplaceTextarea();
	 ed.getValue = function(){
		 var oEditor = FCKeditorAPI.GetInstance(this.id);
		 return oEditor.GetXHTML();
		}
	 ed.setValue = function(html){
		 var oEditor = FCKeditorAPI.GetInstance(this.id);
		 return oEditor.SetHTML(html);
		}
	 ed.insertHTML = function(html){
		 var oEditor = FCKeditorAPI.GetInstance(this.id);
		 return oEditor.InsertHtml(html);
		}
	 ed.setSize = function(width, height){
		 var iframe = document.getElementById(this.id+'___Frame');
		 if(width) iframe.style.width = width;
		 if(height) iframe.style.height = height;
		}
	 ed.getEditor = function(){return FCKeditorAPI.GetInstance(this.id);}

	 ed.initialized = true;
	} break;

  //-------------------------------------------------------------------------------------------------------//

  case "archivefind" : {
	 var sCmd = "dynarc archive-find";
	 if(ed.getAttribute('at'))	  sCmd+= " -type '"+ed.getAttribute('at')+"'";

	 sCmd+= " -search `";

	 var eCmd = "` -limit "+(ed.getAttribute('limit') ? ed.getAttribute('limit') : '10')+" --order-by 'name ASC'";
	 if(extraParams)
	  eCmd+= " "+extraParams

	 EditSearch.init(ed, sCmd, eCmd, "id", "name", "archives", true);

	 ed.onclick = function(){
		 var cmd = "dynarc archive-find";
		 if(ed.getAttribute('at'))	  cmd+= " -type '"+ed.getAttribute('at')+"'";
		 cmd+= " -limit "+(ed.getAttribute('limit') ? ed.getAttribute('limit') : '10');
		 EditSearch._execQry(this,cmd);
		}

	 ed.getAP = function(){
		 if(this.data)
		  return this.data['prefix'];
		 else if(this.getAttribute('ap'))
		  return this.getAttribute('ap');
		 return "";
		}

	 ed.reset = function(){
		 this.data = null;
		 this.value = "";
		 this.setAttribute('ap','');
		}

	} break;

  //-------------------------------------------------------------------------------------------------------//

  case "catfind" : {
	 var sCmd = "dynarc cat-find -ap '"+ed.getAttribute('ap')+"'";
	 if(ed.getAttribute('ct') || ed.getAttribute('pt'))
	  sCmd+= " -pt '"+(ed.getAttribute('ct') ? ed.getAttribute('ct') : ed.getAttribute('pt'))+"'";
	 else if(ed.getAttribute('cat'))
	  sCmd+= " -parent '"+ed.getAttribute('cat')+"'";
	 sClickCmd = sCmd.replace("cat-find","cat-list");
	 sCmd+= " -field name `";
	 var eCmd = "` -limit "+(ed.getAttribute('limit') ? ed.getAttribute('limit') : '10')+" --order-by 'name ASC'";
	 if(extraParams)
	  eCmd+= " "+extraParams
	 EditSearch.init(ed, sCmd, eCmd, "id", "name", null, true);
	 if(!ed.getAttribute('disableonclick'))
	  ed.onclick = function(){EditSearch._execQry(this,sClickCmd+" -limit "+(this.getAttribute('limit') ? this.getAttribute('limit') : '10'));}
	 ed.getId = function(){
		 if(this.data)
		  return this.data['id'];
		 else if(this.getAttribute('catid'))
		  return this.getAttribute('catid');
		 return 0;
		}
	 ed.reset = function(){
		 this.data = null;
		 this.value = "";
		 this.setAttribute('catid',0);
		}
	} break;

  //-------------------------------------------------------------------------------------------------------//

  case "itemfind" : {
	 var sCmd = "dynarc item-find -ap '"+ed.getAttribute('ap')+"'";
	 if(ed.getAttribute('ct'))	  sCmd+= " -ct '"+ed.getAttribute('ct')+"'";
	 if(ed.getAttribute('into'))  sCmd+= " -into '"+ed.getAttribute('into')+"'";

	 sCmd+= " -field name `";

	 var limit = ed.getAttribute('limit') ? ed.getAttribute('limit') : 10;

	 var eCmd = "` -limit "+limit+" --order-by 'name ASC'";
	 if(extraParams)
	  eCmd+= " "+extraParams

	 EditSearch.init(ed, sCmd, eCmd, "id", "name", "items", true);

	 ed.oldvalue = ed.value;
	 ed.onclick = function(){
		 var cmd = "dynarc item-list -ap '"+this.getAttribute('ap')+"'";
		 if(this.getAttribute('ct'))		cmd+= " -ct '"+this.getAttribute('ct')+"'";
		 else if(this.getAttribute('into')) cmd+= " -into '"+this.getAttribute('into')+"'";
		 else cmd+= " --all-cat";

		 cmd+= " -limit "+(this.getAttribute('limit') ? this.getAttribute('limit') : '10');
		 
		 EditSearch._execQry(this,cmd);
		}

	 ed.getId = function(){
		 if(this.data)
		  return this.data['id'];
		 else if((this.value == this.oldvalue) && this.getAttribute('refid'))
		  return this.getAttribute('refid');
		 return 0;
		}

	 ed.reset = function(){
		 this.data = null;
		 this.value = "";
		 this.setAttribute('refid',0);
		}

	} break;

  //-------------------------------------------------------------------------------------------------------//

  case "search" : {
	 var sCmd = "dynarc search -ap '"+ed.getAttribute('ap')+"'";
	 if(ed.getAttribute('ct'))
	  sCmd+= " -ct '"+ed.getAttribute('ct')+"'";
	 if(ed.getAttribute('into'))
	  sCmd+= " -into '"+ed.getAttribute('into')+"'";
	 if(ed.getAttribute('field'))
	  sCmd+= " -field `"+ed.getAttribute('field')+"`";
	 else if(ed.getAttribute('fields'))
	  sCmd+= " -fields `"+ed.getAttribute('fields')+"`";
	 else
	  sCmd+= " -field name";
	 sCmd+= " `";
	 var limit = ed.getAttribute('limit') ? ed.getAttribute('limit') : 10;
	 var orderBy = ed.getAttribute('orderby') ? ed.getAttribute('orderby') : 'name ASC';
	 var eCmd = "` -limit "+limit+" --order-by '"+orderBy+"'";
	 if(ed.getAttribute('get'))
	  eCmd+= " -get `"+ed.getAttribute('get')+"`";
	 if(ed.getAttribute('extget'))
	  eCmd+= " -extget `"+ed.getAttribute('extget')+"`";
	 if(extraParams)
	  eCmd+= " "+extraParams

	 EditSearch.init(ed, sCmd, eCmd, "id", "name", "items", true);
	 ed.oldvalue = ed.value;
	 ed.onclick = function(){
		 if(this.getAttribute('emptyonclick'))
		 {
		  this.data = null;
		  this.value = "";
		 }
		}
	 ed.getId = function(){
		 if(this.data)
		  return this.data['id'];
		 else if((this.value == this.oldvalue) && this.getAttribute('refid'))
		  return this.getAttribute('refid');
		 return 0;
		}

	 ed.reset = function(){
		 this.data = null;
		 this.value = "";
		 this.setAttribute('refid',0);
		}


	} break;
  //-------------------------------------------------------------------------------------------------------//

  //-------------------------------------------------------------------------------------------------------//
 }
 return ed;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.initBtn = function(btn, type)
{
 var oThis = this;
 switch(type)
 {
  case 'menu' : {
	 var popupmenu = btn.getAttribute("connect") ? document.getElementById(btn.getAttribute("connect")) : null;
	 if(!popupmenu) return alert("GnujikoTemplate Error: Button #"+btn.id+" is not connected to any menu.");
	 this.initPopupMenu(popupmenu, btn);
	} break;

  //-------------------------------------------------------------------------------------------------------//

  case "attachupld" : {
	 var attachmentContainer = btn.getAttribute("connect") ? document.getElementById(btn.getAttribute("connect")) : null;
	 if(attachmentContainer)
	 {
	  btn.attacHinst = new GLAttachments(btn.getAttribute('refap'), btn.getAttribute('refid'), btn.getAttribute('refcat'), attachmentContainer);
	  btn.attacHinst.btnH = btn;
	  btn.attacHinst.OnUpload = function(){this.btnH.OnUpload();}
	 }
	 btn.onclick = function(){this.attacHinst.upload(this.getAttribute('destpath'));}
	 btn.OnUpload = function(){}
	 btn.getAttachments = function(retAsXML){return this.attacHinst.getAttachments(retAsXML);}
	} break;

  //-------------------------------------------------------------------------------------------------------//

  //-------------------------------------------------------------------------------------------------------//
 }
 return btn;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.initPopupMenu = function(ul, btn)
{
 var oThis = this;
 ul.submenu = new Array();
 ul.selectedItem = null;

 /* RE-WRITABLE EVENTS */
 ul.OnSelect = function(selLI){};


 /* PRIVATE */
 ul.onmouseover = function(){oThis.overPopupMenu=true;}
 ul.onmouseout = function(){oThis.overPopupMenu=false;}
 if(btn)
 {
  ul.btn = btn;
  btn.popupmenu = ul;
  btn.onclick = function(){
	 var corrX = this.getAttribute('corrx') ? parseFloat(this.getAttribute('corrx')) : 0;
	 var corrY = this.getAttribute('corry') ? parseFloat(this.getAttribute('corry')) : 0;
	 this.popupmenu.show(this, corrX, corrY);
	}
 }
 

 ul.show = function(btnObj, corrX, corrY){
	 if(btnObj && btnObj.getAttribute('absposx') && btnObj.getAttribute('absposy'))
	  var pos = {x:parseFloat(btnObj.getAttribute('absposx')), y:parseFloat(btnObj.getAttribute('absposy'))};
	 else
	  var pos = oThis.getAbsPos(btnObj);
	 if(!isNaN(corrX)) pos.x+= corrX;
	 if(!isNaN(corrY)) pos.y+= corrY;
	 this.style.left = pos.x+"px";
	 this.style.top = (pos.y+btnObj.offsetHeight)+"px";

	 var screenWidth = window.innerWidth ? window.innerWidth : document.body.clientWidth;
	 var screenHeight = window.innerHeight ? window.innerHeight : document.body.clientHeight;

	 if(this.btn && (this.btn.type.toUpperCase() == "TEXT"))
	 {
	  if(this.offsetWidth < this.btn.offsetWidth)
	   this.style.width = (this.btn.offsetWidth-2)+"px";
	 }

     if((parseFloat(this.style.left)+this.offsetWidth) > screenWidth)
      this.style.left = screenWidth - this.offsetWidth - 20;

	 if((parseFloat(this.style.top)+this.offsetHeight) > screenHeight)
	  this.style.top = (pos.y-this.offsetHeight)+"px";

	 this.style.visibility = "visible";
	 oThis.activePopupMenu = this;
	}

 ul.hide = function(){
	 this.style.visibility="hidden";
	 for(var c=0; c < this.submenu.length; c++)
	  this.submenu[c].hide();
	 oThis.activePopupMenu = null;
	}

 ul.setSelectedItem = function(li){
	 if(this.selectedItem) this.selectedItem.className = this.selectedItem.className.replace(" selected","");
	 this.selectedItem = li;
	 li.className = li.className+" selected";
	 if(ul.OnSelect) ul.OnSelect(li);
	}

 ul.getItemByValue = function(value){
	 var list = this.getElementsByTagName('LI');
	 for(var c=0; c < list.length; c++)
	 {
	  if(list[c].getAttribute('value') == value)
	   return list[c];
	 }
	}

 var list = new Array();
 var tmp = ul.getElementsByTagName('LI');
 for(var c=0; c < tmp.length; c++)
 {
  if(tmp[c].parentNode == ul)
   list.push(tmp[c]);
 }
 
 for(var c=0; c < list.length; c++)
 {
  var li = list[c];
  li.onmouseover = function(){
	 if(oThis.lastSubmenuActive && (oThis.lastSubmenuActive != this.subMenu))
	  oThis.lastSubmenuActive.hide();
	 if(this.parentNode.subMenuT)
	  window.clearTimeout(this.parentNode.subMenuT);
	}

  li.hideAll = function(){
	 var pLI = this.parentNode.parentNode;
	 if((pLI.tagName == "LI") && pLI.hideAll)
	  pLI.hideAll();
	 else
	  this.parentNode.hide();
	}

  li.rename = function(title){
	 var span = this.getElementsByTagName('SPAN')[0];
	 if(span)
	  span.innerHTML = title;
	}

  var tmp = li.getElementsByTagName('UL')[0];
  if(tmp)
  {
   this.initPopupSubMenu(tmp, li);
   ul.submenu.push(tmp);
  }
 }

 return ul;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.initPopupSubMenu = function(ul, parent)
{
 var oThis = this;
 ul.opened = false;
 ul.menuWidth = ul.offsetWidth;
 ul.menuHeight = ul.offsetHeight;
 ul.style.display = "none";

 parent.subMenu = ul;
 parent.subMenu.subMenuT = null;

 parent.onmouseover = function(){
	 if(this.subMenu.subMenuT)
	  window.clearTimeout(this.subMenu.subMenuT);
	 var oLI = this; 
	 if(oThis.lastSubmenuActive && (oThis.lastSubmenuActive != this.subMenu))
	  oThis.lastSubmenuActive.hide();

	 if(!this.subMenu.opened)
	  this.subMenu.subMenuT = window.setTimeout(function(){oLI.showSubMenu();},500);

	 this.mouseover = true;
	}

 parent.onmouseout = function(){this.mouseover=false;}

 parent.showSubMenu = function(){
	 if(!this.mouseover) return;
	 this.subMenu.show();
	}

 ul.parentLI = parent;
 ul.parentUL = parent.parentNode;

 ul.onmouseover = function(){oThis.overPopupMenu=true;}
 ul.onmouseout = function(){oThis.overPopupMenu=false;}

 ul.show = function(){
	 if(this.opened) return;
	 var x = this.parentUL.offsetWidth-2;
	 var y = this.parentLI.offsetTop;
	 this.style.left = x+"px";
	 this.style.top = y+"px";

	 var screenWidth = window.innerWidth ? window.innerWidth : document.body.clientWidth;
	 var screenHeight = window.innerHeight ? window.innerHeight : document.body.clientHeight;

     if((parseFloat(this.parentUL.style.left)+parseFloat(this.style.left)+this.menuWidth) > screenWidth)
      this.style.left = (-this.menuWidth)+"px";

	 if((parseFloat(this.parentUL.style.top)+parseFloat(this.style.top)+this.menuHeight+20) > screenHeight)
	  this.style.top = (this.parentLI.offsetTop+this.parentLI.offsetHeight-this.menuHeight)+"px";


	 this.style.display = "";
	 this.style.visibility = "visible";
	 oThis.lastSubmenuActive = this;
	 this.opened = true;
	}

 ul.hide = function(){
	 this.style.visibility = "hidden";
	 oThis.lastSubmenuActive = false;
	 this.opened = false;
	}

 ul.setSelectedItem = function(li){
	 this.parentUL.setSelectedItem(li);
	}

 var list = new Array();
 var tmp = ul.getElementsByTagName('LI');
 for(var c=0; c < tmp.length; c++)
 {
  if(tmp[c].parentNode == ul)
   list.push(tmp[c]);
 }
 
 for(var c=0; c < list.length; c++)
 {
  var li = list[c];

  li.hideAll = function(){
	 var pLI = this.parentNode.parentNode;
	 if((pLI.tagName == "LI") && pLI.hideAll)
	  pLI.hideAll();
	 else
	  this.parentNode.hide();
	}

  if(typeof(li.onclick) != "function")
   li.onclick = function(ev){
	 ev.stopPropagation();
	 this.parentNode.setSelectedItem(this);
	}
 }

}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.initTabMenu = function(ul)
{
 ul.selectedItem = null;
 ul.selectedPage = null;
 ul.OnChange = null;

 var list = ul.getElementsByTagName('LI');
 for(var c=0; c < list.length; c++)
 {
  var li = list[c];
  if(li.className.indexOf('selected') > -1)
  {
   ul.selectedItem = li;
   var page = document.getElementById(li.getAttribute('connect'));
   if(page) ul.selectedPage = page;
  }

  if(li.getAttribute('connect'))
   li.onclick = function(){this.parentNode.selectItem(this);}
 }

 ul.selectItem = function(li){
	 if(this.selectedPage) this.selectedPage.style.display = "none";
	 switch(this.className)
	 {
	  case 'horizontal-menu' : case 'vertical-menu' : {
		 var color = this.selectedItem.className.replace("selected ","");
		 color = color.replace("-selected","");
		 this.selectedItem.className = "item "+color;

		 var color = li.className.replace("item ","");
		 li.className = "selected "+color+"-selected";
		} break;

	 }
	 this.selectedItem = li;
	 if(li.getAttribute('connect'))
	 {
	  var page = document.getElementById(li.getAttribute('connect'));
	  if(page) 
	  {
	   page.style.display = "";
	   this.selectedPage = page;
	  }
	  else alert("GnujikoTemplate.initTabMenu error: page "+li.getAttribute('connect')+" does not exists.");
	 }
	 if(this.OnChange)
	  this.OnChange(li);
	}

 return ul;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.initSortableTable = function(tb, defField, defSort, hasSchema)
{
 tb.activeFieldA = null;
 tb.hasSchema = hasSchema ? true : false;

 if(tb.getAttribute('data-hasschema') || tb.getAttribute('hasschema'))
  tb.hasSchema = true;

 
 /* EVENTS */
 tb.OnSort = function(field, method){};
 tb.OnSelect = function(){};
 tb.fields = new Array();
 tb.colByField = new Array();

 /* INJECT FIELDS */
 for(var c=0; c < tb.rows[0].cells.length; c++)
 {
  var th = tb.rows[0].cells[c];
  th.tb = tb;

  if(th.getAttribute('field'))
  {
   tb.colByField[th.getAttribute('field')] = th;
   tb.fields.push(th);
  }
  
  // inject functions
  th.show = function(){
	 this.style.display = "";
	 for(var c=1; c < this.tb.rows.length; c++)
	 {
	  var cell = this.tb.rows[c].cells[this.cellIndex];
	  if(cell)
	   cell.style.display = "";
	 }
	}

  th.hide = function(){
	 this.style.display = "none";
	 for(var c=1; c < this.tb.rows.length; c++)
	 {
	  var cell = this.tb.rows[c].cells[this.cellIndex];
	  if(cell)
	   cell.style.display = "none";
	 }
	}

  if(th.getAttribute('sortable') == 'true')
  {
   var a = document.createElement('A');
   a.innerHTML = th.innerHTML;
   a.href = "#";
   a.th = th;
   a.tb = tb;
   a.onclick = function(){
	 if(this.tb.activeFieldA == this)
	 {
	  this.className = (this.className == "sortasc") ? "sortdesc" : "sortasc";
	 }
	 else
	 {
	  if(this.tb.activeFieldA)
	   this.tb.activeFieldA.className = "";
	  this.className = "sortasc";
	  this.tb.activeFieldA = this;
	 }
     // shot event
	 tb.OnSort(this.th.getAttribute('field'), (this.className == "sortasc") ? "ASC" : "DESC");
	}
   
   if(defField == th.getAttribute('field'))
   {
	a.className = (defSort.toUpperCase() == "ASC") ? "sortasc" : "sortdesc";
    tb.activeFieldA = a;
   }

   th.innerHTML = "";
   th.appendChild(a);
  }

 }

 /* INJECT CHECKBOX FOR THE FIRST ROW */
 if(tb.rows[0])
 {
  var cb = tb.rows[0].cells[0].getElementsByTagName('INPUT')[0];
  if(cb)
  {
   cb.tb = tb;
   cb.onclick = function(){
	 if(this.checked)
	  this.tb.selectAll();
	 else
	  this.tb.unselectAll();
	}
  }
 }

 /* INJECT OTHER FUNCTIONS */
 tb.getSelectedRows = function(){
	 var ret = new Array();
	 var from = this.hasSchema ? 2 : 1;
	 for(var c=from; c < this.rows.length; c++)
	 {
	  var cb = this.rows[c].cells[0].getElementsByTagName('INPUT')[0];
	  if(!cb)
	   continue;
	  if(cb.checked)
	   ret.push(this.rows[c]);
	 }
	 return ret;
	};

 tb.getRows = function(){
	 var list = new Array();
	 var from = this.hasSchema ? 2 : 1;
	 for(var c=from; c < this.rows.length; c++)
	 {
	  list.push(this.rows[c]);
	 }
	 return list;
	};

 tb.selectAll = function(){
	 if(!this.rows.length)
	  return;
	 for(var c=1; c < this.rows.length; c++)
	  this.rows[c].select();
	 var cb = this.rows[0].cells[0].getElementsByTagName('INPUT')[0];
	 if(!cb)
	  return;
	 cb.checked = true;

	 if(this.OnSelect)
	  this.OnSelect(this.getSelectedRows());
	};

 tb.unselectAll = function(){
	 if(!this.rows.length)
	  return;
	 for(var c=1; c < this.rows.length; c++)
	  this.rows[c].unselect();
	 var cb = this.rows[0].cells[0].getElementsByTagName('INPUT')[0];
	 if(!cb)
	  return;
	 cb.checked = false;

	 if(this.OnSelect)
	  this.OnSelect(this.getSelectedRows());
	};

 tb.showColumn = function(field){
	 var th = this.colByField[field];
	 if(!th) return;
	 th.show();
	}

 tb.hideColumn = function(field){
	 var th = this.colByField[field];
	 if(!th) return;
	 th.hide();
	}

 tb.getRowById = function(id){
	 if(!this.rows.length)
	  return null;
	 for(var c=1; c < this.rows.length; c++)
	 {
	  if(this.rows[c].id == id)
	   return this.rows[c];
	 }
	}

 tb.empty = function(){
	 var from = this.hasSchema ? 1 : 0;
	 while(this.rows.length > (from+1) )
	 {
	  this.deleteRow(from+1);
	 }
	}

 tb.addRow = function(id){
	 if(this.hasSchema && (this.rows.length > 1))
	 {
	  var r = this.rows[1].cloneNode(true);
	  if(this.tBodies.length > 0)
	   this.tBodies[0].appendChild(r);
	  else
	   this.appendChild(r);
	 }
	 else
	  var r = this.insertRow(-1);
	 this.injectRow(r);
	 r.style.display = "";
	 if(id) r.id = id;
	 return r;
	}

 tb.injectRows = function(){
	 var start = this.hasSchema ? 2 : 1;
	 for(var c=start; c < this.rows.length; c++)
	 {
	  var r = this.rows[c];
	  this.injectRow(r);
	 } 
	}

 tb.injectRow = function(r){
  	 r.tb = this;
	 var cb = null;
	 var frcb = this.rows[0].cells[0].getElementsByTagName('INPUT')[0];
	 if(frcb)
	 {
	  if(r.cells.length == 0)
	  {
	   var cb = document.createElement('INPUT');
	   cb.type = "checkbox";
	   r.insertCell(-1).appendChild(cb);
	  }
	  else
	  {
	   var cb = r.cells[0].getElementsByTagName('INPUT')[0];
	   if(!cb || (cb.type != "checkbox"))
	    cb = null;
	  }
	  if(cb)
	  {
	   cb.r = r;
	   cb.onclick = function(){this.r.toggleSelect();}
	  }
	 }

	 r.setValue = function(key, value, emitOnChange){
		 if(this.tb.colByField[key] && this.cells[this.tb.colByField[key].cellIndex])
		  this.cells[this.tb.colByField[key].cellIndex].innerHTML = value;
		 else
		 {
		  var html = this.innerHTML;
		  while(html.indexOf(key) > -1)
		   html = html.replace(key, value);
		  this.innerHTML = html;
		 }
		 if(emitOnChange && typeof(this.tb.onchange == 'function'))
		  this.tb.onchange(this);
		}

	 r.getValue = function(field){
		 var colIdx = this.tb.colByField[field].cellIndex;
		 return this.cells[colIdx].textContent;
		}

  	 r.select = function(shotEvent){
	 	 var cb = this.cells[0].getElementsByTagName('INPUT')[0];
	 	 if(!cb) return;
		 if(this.className && (this.className != "selected"))
		  this.className = this.className.replace("-selected", "")+"-selected";
		 else
		  this.className = "selected";
	 	 cb.checked = true;
	 	 if(shotEvent && this.tb.OnSelect)
	 	  this.tb.OnSelect(this.tb.getSelectedRows());
		};
  
	 r.unselect = function(shotEvent){
		 var cb = this.cells[0].getElementsByTagName('INPUT')[0];
		 if(!cb) return;
		 if(this.className && (this.className != "selected"))
		  this.className = this.className.replace("-selected", "");
		 else
		  this.className = "";
		 cb.checked = false;
		 if(shotEvent && this.tb.OnSelect)
		  this.tb.OnSelect(this.tb.getSelectedRows());
		};

	 r.toggleSelect = function(){
		 var cb = this.cells[0].getElementsByTagName('INPUT')[0];
		 if(!cb)
		  return;
		 if(this.className && (this.className != "selected"))
		  this.className = this.className.replace("-selected", "") + (cb.checked ? "-selected" : "");
		 else
		  this.className = cb.checked ? "selected" : "";
		 if(this.tb.OnSelect)
		  this.tb.OnSelect(this.tb.getSelectedRows());
		}

	 r.getCellByField = function(field){
		 var th = this.tb.colByField[field];
		 if(!th) return false;
		 return this.cells[th.cellIndex];
		};

	  r.remove = function(){this.tb.deleteRow(this.rowIndex);}
	}


 tb.injectRows();

 return tb;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.initCustomTabMenu = function(ul, selectedItemClassName)
{
 // inizializza una UL come se fosse una tab-menu con le sole funzioni principali
 ul.selectedItem = null;
 ul.options = {selItmClassName: selectedItemClassName ? selectedItemClassName : "selected"}

 /* CUSTOMIZABLE EVENTS */
 ul.OnSelect = function(value){}
 ul.OnUnselect = function(oldValue){}

 ul.select = function(li){
	 if(this.selectedItem == li) return;
	 if(this.selectedItem)
	 {
	  if(this.selectedItem.className == this.options.selItmClassName)
	   this.selectedItem.className = "";
	  else
	   this.selectedItem.className = this.selectedItem.className.replace(" "+this.options.selItmClassName, "");
	  this.OnUnselect(this.selectedItem.getAttribute('value'));
	 }
	 li.className = li.className+" "+this.options.selItmClassName;
	 this.selectedItem = li;
	 this.OnSelect(this.selectedItem.getAttribute('value'));
	}


 // inject elements
 var ulList = ul.getElementsByTagName('LI');
 for(var c=0; c < ulList.length; c++)
 {
  ulList[c].ulH = ul;
  ulList[c].onclick = function(){this.ulH.select(this);}
  if(!ul.selectedItem && (ulList[c].className == ul.options.selItmClassName))
   ul.selectedItem = ulList[c];
 }

 return ul;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.initToggles = function(ul)
{
 ul.select = function(li){
	 var list = this.getElementsByTagName('LI');
	 for(var c=0; c < list.length; c++)
	  list[c].className = (list[c] == li) ? list[c].className.replace(" selected","")+" selected" : list[c].className.replace(" selected","");

	 var value = li.getAttribute('value') ? li.getAttribute('value') : "";

	 if(typeof(this.onchange) == "function")
	  this.onchange(value);

	 this.selectedItem = li;
	 this.value = value;
	}

 list = ul.getElementsByTagName('LI');
 for(var c=0; c < list.length; c++)
 {
  var li = list[c];
  li.ul = ul;
  li.onclick = function(){this.ul.select(this);}
 }
 return ul;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.showCal = function(ed)
{
 var date = new Date();
 if(ed.value)
  date.setFromISO(strdatetime_to_iso(ed.value));

 ed.GCal.Show(ed, date);
 ed.GCal.OnChange = function(newdate){
	 ed.value = newdate.printf("d/m/Y");
	 ed.isodate = newdate.printf("Y-m-d");
	 ed.isodatetime = newdate.printf("Y-m-d H:i");
	 if(ed.onchange)
	  ed.onchange();
	}

 ed.onblur = function(){this.GCal.Hide();}
 ed.onkeydown = function(){this.GCal.Hide(true);}
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.loadLayer = function(layName, arguments, destObj, clearObj, callbackFunction, callbackTimer)
{
 var layer = new Layer(layName, arguments, destObj, clearObj, callbackFunction, callbackTimer);
 destObj.layer = layer;
 return layer;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.setVar = function(variable, value)
{
 this.URLVARS[variable] = value;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.unsetVar = function(variable)
{
 delete this.URLVARS[variable];
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.getVar = function(variable)
{
 return this.URLVARS[variable];
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.getVars = function() 
{
 var vars = {};
 var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value){vars[key] = value;});
 return vars;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.reload = function(setPg)
{
 var href = document.location.href;
 var startHREF = href;
 var x = href.indexOf("?");
 if(x > -1)
 {
  startHREF = href.substr(0,x);
 }
 var endHREF = "";
 for(var k in this.URLVARS)
  endHREF+= "&"+k+"="+this.URLVARS[k];

 document.location.href = startHREF+"?"+endHREF.substr(1);
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.Exit = function(o,a)
{
 if(this.Modal.toUpperCase() == "WIDGET")
  return gframe_close(o,a);
 if(this.OnExit)
 {
  if(this.OnExit() == false)
   return;
 }
 if(window.opener && window.opener.document)
 {
  if(o || a)
   window.opener.document.location.reload();
  window.close();
 }
 else
  document.location.href = ABSOLUTE_URL+this.AppBasePath;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.SaveAndExit = function(o,a)
{
 if(this.Modal.toUpperCase() == "WIDGET")
  return gframe_close(o,a);
 if(this.OnSave)
 {
  if(this.OnSave() == false)
   return;
 }
 document.location.href = ABSOLUTE_URL+this.AppBasePath;
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplate.prototype.ShowApplicationMenu = function(btn)
{
 if(!this.glApplicationMenu)
 {
  this.glApplicationMenu = new GapLayer(300,400);
  this.glApplicationMenu.setPadding("20px 0px 20px 20px");
 }
 this.glApplicationMenu.load("applicationmenu", "", btn, 0, 12);
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//

/* CLASS: MouseHandler */ 
function GnujikoTemplateMouseHandler()
{
 this.mdown = false;
 this.downX = 0;
 this.downY = 0;

 /* RE-WRITABLE EVENTS */
 this.OnMouseDown = function(ev,x,y){};
 this.OnMouseUp = function(ev,x,y){};
 this.OnMouseMove = function(ev,x,y){};
 this.OnMouseDrag = function(ev,x,y){};
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplateMouseHandler.prototype._onmousedown = function(ev,x,y)
{
 this.mdown = true;
 this.downX = x;
 this.downY = y;
 this.OnMouseDown(ev,x,y);
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplateMouseHandler.prototype._onmouseup = function(ev,x,y)
{
 this.mdown = false;
 this.OnMouseUp(ev,x,y);
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoTemplateMouseHandler.prototype._onmousemove = function(ev,x,y)
{
 if(this.mdown)
  this.OnMouseDrag(ev,x,y);
 else
  this.OnMouseMove(ev,x,y);
}
//-------------------------------------------------------------------------------------------------------------------//


//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//

var Template = new GnujikoTemplate();
function gnujikoTemplateMouseDown(ev){Template._onmousedown(ev);}
function gnujikoTemplateMouseUp(ev){Template._onmouseup(ev);}
function gnujikoTemplateMouseMove(ev){Template._onmousemove(ev);}
function gnujikoTemplateBodyResize(ev){Template._onwindowresize(ev);}
function gnujikoTemplateScroll(ev){Template._onwindowscroll(ev);}
document.addEventListener ? document.addEventListener("mouseup",gnujikoTemplateMouseUp,false) : document.attachEvent("onmouseup",gnujikoTemplateMouseUp);
document.addEventListener ? document.addEventListener("mousedown",gnujikoTemplateMouseDown,false) : document.attachEvent("onmousedown",gnujikoTemplateMouseDown);
document.addEventListener ? document.addEventListener("mousemove",gnujikoTemplateMouseMove,false) : document.attachEvent("onmousemove",gnujikoTemplateMouseMove);
//-------------------------------------------------------------------------------------------------------------------//

