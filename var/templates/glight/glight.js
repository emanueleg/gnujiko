/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 02-04-2017
 #PACKAGE: glight-template
 #DESCRIPTION: GLight template - Main class.
 #VERSION: 2.27beta
 #CHANGELOG: 02-04-2017 : Bugfix su initSortableTable.
			 26-03-2017 : Aggiunto funzione initSerp.
			 04-03-2017 : Aggiunto initialized su ritorno in funzioni initEd ed initBtn.
			 01-03-2017 : Aggiornata funzione initTabMenu.
			 12-11-2016 : Bug fix on init contactextended on line 314.
			 17-05-2016 : Bug fix su funzione Exit.
			 27-04-2015 : Aggiunto destpath su initEd fileupload
			 07-04-2015 : Aggiunta funzione initTabMenu e loadLayer.
			 28-03-2015 : Bug fix dropdown.
			 12-12-2015 : Bug fix initPopupMenu.
			 10-12-2014 : Bug fix sortable-table.
			 03-12-2014 : Aggiunta funzione empty e addItem su dropdown.
			 30-11-2014 : Bug fix vari.
			 28-11-2014 : Dropdown bug fix.
			 16-11-2014 : Bug fix popupmenu.hide
			 17-10-2014 : Aggiornato initEd (fileupload).
			 14-10-2014 : Aggiunta funzione getRowById su tabella inizializzata con initSortableTable.
			 06-10-2014 : Bug fix vari.
			 21-09-2014 : Aggiunto GServ
			 25-08-2014 : Integrato con GBook.
			 30-07-2014 : Bug fix vari.
			 18-06-2014 : Aggiunto parametro into su initEd( itemfind ).
			 13-06-2014 : Aggiunto extfind su funzione initEd per ricerche su estensioni.
			 09-06-2014 : Aggiunto funzioni getScreenWidth e getScreenHeight
			 13-04-2014 : Aggiunto itemselect
			 10-04-2014 : Aggiornamenti vari
 #TODO:
 
*/

function GLightTemplate()
{

 this.PopupMenuList = new Array();
 this.PopupMessageList = new Array();

 this.AppBasePath = "";
 this.Modal = "APPLICATION";

 this.URLVARS = this.getVars();

 /* EVENTS */
 this.OnInit = null;
 this.OnExit = null;
 this.OnSave = null;
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.init = function()
{
 // Create main menu

 this.MainMenuO = document.createElement('DIV');
 this.MainMenuO.className = "glight-mainmenu";

 this.MainMenuI = document.createElement('DIV');
 this.MainMenuI.className = "glight-mainmenu-inner";

 this.MainMenuO.appendChild(this.MainMenuI);

 var oThis = this;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var html = "<table width='100%' cellspacing='0' cellpadding='0' border='0' class='glight-mainmenu-table'>";
	 for(var c=0; c < a.length; c++)
	 {
	  html+= "<tr><td style='width:32px;text-align:center'><a href='"+ABSOLUTE_URL+a[c]['url']+"' target='GNUAPP-"+a[c]['id']+"'><img src='"+ABSOLUTE_URL+a[c]['icon']+"' class='glight-mainmenu-item-icon'/></a></td>";
	  html+= "<td style='padding-left:5px'><a href='"+ABSOLUTE_URL+a[c]['url']+"' target='GNUAPP-"+a[c]['id']+"'>"+a[c]['name']+"</a></td></tr>";
	 }
	 html+= "</table>";
	 oThis.MainMenuI.innerHTML = html;
	}
 sh.sendCommand("system app-list");

 this.MainMenuO.style.visibility = "hidden";
 document.body.appendChild(this.MainMenuO);

 if(this.OnInit)
  this.OnInit();
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.loadLayer = function(layName, arguments, destObj, clearObj, callbackFunction, callbackTimer)
{
 var layer = new Layer(layName, arguments, destObj, clearObj, callbackFunction, callbackTimer);
 destObj.layer = layer;
 return layer;
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.setVar = function(variable, value)
{
 this.URLVARS[variable] = value;
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.unsetVar = function(variable, value)
{
 delete this.URLVARS[variable];
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.getVar = function(variable)
{
 return this.URLVARS[variable];
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.getVars = function() 
{
 var vars = {};
 var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value){vars[key] = value;});
 return vars;
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.reload = function(setPg)
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
GLightTemplate.prototype.Exit = function(o,a)
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
GLightTemplate.prototype.SaveAndExit = function(o,a)
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
GLightTemplate.prototype.ShowMainMenu = function(button)
{
 var pos = this.getAbsPos(button);
 var left = pos.x-145;
 var top = pos.y+button.offsetHeight;
 var screenWidth = window.innerWidth ? window.innerWidth : document.body.clientWidth;
 var screenHeight = window.innerHeight ? window.innerHeight : document.body.clientHeight;

 this.MainMenuO.style.left = left+"px";
 this.MainMenuO.style.top = top+"px";
 var oThis = this;
 window.setTimeout(function(){oThis.MainMenuO.style.visibility = "visible";}, 150);
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.initEd = function(ed, type, extraParams)
{
 var oThis = this;
 switch(type)
 {
  case "date" : {
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

  case "time" : {
	 ed.onchange = function(){
	  var tmp = parseTime(this.value);
	  this.value = tmp.hh+":"+tmp.mm;
	 }
	} break;

  case "timelength" : {
	 ed.onchange = function(){
		 this.value = timelength_to_str(parse_timelength(this.value));
		 if(typeof(this.OnChange) == "function")
		  this.OnChange();
		}
	} break;

  case "currency" : {
	 ed.onchange = function(){
		 this.value = formatCurrency(parseCurrency(this.value));
		 if(typeof(this.OnChange) == "function")
		  this.OnChange();
		}
	} break;

  case "contact" : {
	 var sCmd = "dynarc item-find -ap '"+(ed.getAttribute('ap') ? ed.getAttribute('ap') : "rubrica")+"'";
	 if(ed.getAttribute('ct'))
	  sCmd+= " -ct '"+ed.getAttribute('ct')+"'";
	 sCmd+= " -field name `";
	 var eCmd = "` -limit 10 --order-by 'name ASC'";
	 EditSearch.init(ed, sCmd, eCmd, "id", "name", "items", true);
	 ed.oldvalue = ed.value;
	 ed.onmousedown = function(ev){
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
		   sh.sendCommand("gframe -f rubrica.new -c `"+this.value+"` -params `ct="+ed.getAttribute('ct')+"`");
		 }
		}
	 ed.getId = function(){
		 if(this.data)
		  return this.data['id'];
		 else if((this.value == this.oldvalue) && this.getAttribute('refid'))
		  return this.getAttribute('refid');
		 return 0;
		}
	} break;

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
		  if(this.value && (parseFloat(this.getId()) > 0))
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
 
	} break;

  case "itemfind" : {
	 var sCmd = "dynarc item-find -ap '"+ed.getAttribute('ap')+"'";
	 if(ed.getAttribute('ct'))
	  sCmd+= " -ct '"+ed.getAttribute('ct')+"'";
	 if(ed.getAttribute('into'))
	  sCmd+= " -into '"+ed.getAttribute('into')+"'";
	 sCmd+= " -field name `";
	 var limit = ed.getAttribute('limit') ? ed.getAttribute('limit') : 10;
	 var eCmd = "` -limit "+limit+" --order-by 'name ASC'";
	 if(extraParams)
	  eCmd+= " "+extraParams
	 EditSearch.init(ed, sCmd, eCmd, "id", "name", "items", true);
	 ed.oldvalue = ed.value;
	 ed.onclick = function(){EditSearch._execQry(this,"dynarc item-list -ap '"+ed.getAttribute('ap')+"'"+(this.getAttribute('ct') ? " -ct '"+this.getAttribute('ct')+"'" : "")+" --all-cat -limit "+(this.getAttribute('limit') ? this.getAttribute('limit') : '10'));}
	 ed.getId = function(){
		 if(this.data)
		  return this.data['id'];
		 else if((this.value == this.oldvalue) && this.getAttribute('refid'))
		  return this.getAttribute('refid');
		 return 0;
		}
	} break;

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
	 //ed.onclick = function(){EditSearch._execQry(this,"dynarc item-list -ap '"+ed.getAttribute('ap')+"'"+(this.getAttribute('ct') ? " -ct '"+this.getAttribute('ct')+"'" : "")+" --all-cat -limit "+(this.getAttribute('limit') ? this.getAttribute('limit') : '10'));}
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
	} break;

  case "catfind" : {
	 var sCmd = "dynarc cat-find -ap '"+ed.getAttribute('ap')+"'";
	 if(ed.getAttribute('ct') || ed.getAttribute('pt'))
	  sCmd+= " -pt '"+(ed.getAttribute('ct') ? ed.getAttribute('ct') : ed.getAttribute('pt'))+"'";
	 else if(ed.getAttribute('cat'))
	  sCmd+= " -parent '"+ed.getAttribute('cat')+"'";
	 sClickCmd = sCmd.replace("cat-find","cat-list");
	 sCmd+= " -field name `";
	 var eCmd = "` -limit 10 --order-by 'name ASC'";
	 if(extraParams)
	  eCmd+= " "+extraParams
	 EditSearch.init(ed, sCmd, eCmd, "id", "name", null, true);
	 ed.onclick = function(){EditSearch._execQry(this,sClickCmd+" -limit 20");}
	 ed.getId = function(){
		 if(this.data)
		  return this.data['id'];
		 else if(this.getAttribute('catid'))
		  return this.getAttribute('catid');
		 return 0;
		}
	} break;

  case "archivefind" : {
	 var sCmd = "dynarc archive-find -type '"+ed.getAttribute('at')+"' -search `";
	 var eCmd = "` -limit 10 --order-by 'name ASC'";
	 if(extraParams)
	  eCmd+= " "+extraParams
	 EditSearch.init(ed, sCmd, eCmd, "id", "name", "archives", true);
	 ed.onclick = function(){EditSearch._execQry(this,"dynarc archive-find -type '"+ed.getAttribute('at')+"' -limit 20");}
	 ed.getAP = function(){
		 if(this.data)
		  return this.data['prefix'];
		 else if(this.getAttribute('ap'))
		  return this.getAttribute('ap');
		 return "";
		}
	} break;

  case "userfind" : {
	 var sCmd = "fastfind users -search `";
	 var eCmd = "` -limit 10 --order-by 'fullname ASC'";
	 if(extraParams)
	  eCmd+= " "+extraParams
	 EditSearch.init(ed, sCmd, eCmd, "id", "name", "items", true);
	 ed.onclick = function(){EditSearch._execQry(this,"fastfind users -limit 20");}
	 ed.getId = function(){
		 if(this.data)
		  return this.data['id'];
		 else if(this.getAttribute('usrid'))
		  return this.getAttribute('usrid');
		 return "";
		}
	} break;

  case "extfind" : {
	 var sCmd = "dynarc ext-find -ap '"+ed.getAttribute('ap')+"' -ext '"+ed.getAttribute('ext')+"'";
	 if(!extraParams) 						return alert("Developer error: devi specificare extraParams come oggetto: {startqry:'...', endqry:'...'}");
	 if(!extraParams.startqry)  			return alert("Developer Error: Manca la variabile startqry su oggetto extraParams");
	 if(!extraParams.endqry)  				return alert("Developer Error: Manca la variabile endqry su oggetto extraParams");
	 //if(!ed.getAttribute('retvalfield'))	return alert("Developer Error: devi specificare l'argomento retvalfield per "+ed.id);
	 if(!ed.getAttribute('rettxtfield'))	return alert("Developer Error: devi specificare l'argomento rettxtfield per "+ed.id);
	 //if(!ed.getAttribute('retarrname'))		return alert("Developer Error: devi specificare l'argomento retarrname per "+ed.id);

	 var retValField = ed.getAttribute('retvalfield') ? ed.getAttribute('retvalfield') : "item_id";
	 var retTxtField = ed.getAttribute('rettxtfield');
	 var retArrName = ed.getAttribute('retarrname') ? ed.getAttribute('retarrname') : "results";

	 sCmd+= " "+extraParams.startqry;
	 var eCmd = extraParams.endqry;

	 EditSearch.init(ed, sCmd, eCmd, retValField, retTxtField, retArrName, true);
	 /*ed.getId = function(){
		 if(this.data)
		  return this.data['id'];
		 else if(this.getAttribute('refid'))
		  return this.getAttribute('refid');
		 return 0;
		}*/
	} break;


  case "fileupload" : {
	 ed.readOnly = true;
	 ed.onmousedown = function(ev){
		 var mouseX = ev.pageX ? ev.pageX : ev.clientX;
		 var mouseY = ev.pageY ? ev.pageY : ev.clientY;
		 var pos = _getObjectPosition(this);
		 var mX = mouseX - pos['x'];
		 var mY = mouseY - pos['y'];
		 if(!this.value || (mX > (this.offsetWidth-22)))
		 {
		  var sh = new GShell();
		  var oEd = this;
		  var destPath = this.getAttribute('destpath') ? this.getAttribute('destpath') : "";
		  sh.OnError = function(err){alert(err);}
		  sh.OnOutput = function(o,a){
			 if(!a || !a['files']) return;
			 var fileInfo = a['files'][0];
			 oEd.value = fileInfo['name']+"."+fileInfo['extension'];
			 oEd.setAttribute('filename',fileInfo['fullname']);
			 if(typeof(oEd.OnUpload) == "function")
			  oEd.OnUpload(fileInfo);
			}
		  sh.sendCommand("gframe -f fileupload"+(destPath ? " -params `destpath="+destPath+"`" : ""));
		 }
		 else if(this.value && this.getAttribute('filename'))
		 {
		  window.open(ABSOLUTE_URL+this.getAttribute('filename'), "_blank");
		 }
		}

	 ed.getValue = function(){
		 return (this.value && this.getAttribute('filename')) ? this.getAttribute('filename') : "";
		}

	} break;

  case "dropdown" : {
	 var popupmenu = ed.getAttribute("connect") ? document.getElementById(ed.getAttribute("connect")) : null;
	 if(popupmenu)
	 {
	  this.PopupMenuList.push(popupmenu);

	  popupmenu.ed = ed;

	  // get selected item
	  var listLI = popupmenu.getElementsByTagName('LI');
	  for(var c=0; c < listLI.length; c++)
	  {
	   var li = listLI[c];
	   if(li.className == "separator")
	    continue;
	   li.ed = ed;
	   if(ed.getAttribute('retval') == (li.getAttribute('retval') ? li.getAttribute('retval') : li.getAttribute('value')))
	   {
		ed.selectedItem = li;
		break;
	   }
	  }

	  popupmenu.show = function(){
		 var pos = oThis.getAbsPos(this.ed);
		 var left = pos.x;
		 var top = pos.y+this.ed.offsetHeight;
		 var screenWidth = window.innerWidth ? window.innerWidth : document.body.clientWidth;
		 var screenHeight = window.innerHeight ? window.innerHeight : document.body.clientHeight;

		 if(this.offsetWidth < this.ed.offsetWidth)
		  this.style.width = (this.ed.offsetWidth-2)+"px";

	     if((left+this.offsetWidth) > screenWidth)
	      left = screenWidth - this.offsetWidth;
		 if((top+this.offsetHeight) > screenHeight)
		  top = pos.y-this.offsetHeight;
		 if(top < 0)
		  top = pos.y+this.ed.offsetHeight;

		 this.style.left = left+"px";
		 this.style.top = top+"px";
		 this.style.visibility = "visible";

		 var list = this.getElementsByTagName('LI');
		 for(var c=0; c < list.length; c++)
		 {
		  var li = list[c];
		  if(li.className == "separator")
		   continue;
		  li.ed = ed;
		  if(ed.getAttribute('retval') == (li.getAttribute('retval') ? li.getAttribute('retval') : li.getAttribute('value')))
		   li.className = "selected";
		  else
		   li.className = "";
		  if(!li.onclick)
		   li.onclick = function(){
			 this.ed.oldValue = this.ed.value;
			 this.ed.oldRetVal = this.ed.getAttribute('retval');
			 this.ed.value = this.textContent;
			 var retval = this.getAttribute('retval') ? this.getAttribute('retval') : this.getAttribute('value');
			 if(!retval) this.ed.value = "";
			 this.ed.setAttribute('retval',retval);
			 this.ed.selectedItem = this;
			 if(typeof(this.ed.onselect) == "function")
			  this.ed.onselect();
			 else if(this.ed.onchange)
			  this.ed.onchange();
			}
		 }
		}

	  popupmenu.hide = function(){
		 this.style.visibility="hidden";
		 this.style.left = "0px";
		 this.style.top = "0px";
		}

	  popupmenu.empty = function(){
		 this.innerHTML = "";
		 this.ed.value = "";
		 this.ed.setAttribute('retval',"");
		}

	  popupmenu.addItem = function(title, value){
		 var li = document.createElement('LI');
		 li.innerHTML = title;
		 li.setAttribute('value',value);
		 li.setAttribute('retval',value);
		 li.ed = this.ed;
		 this.appendChild(li);
		 if(!li.onclick)
		   li.onclick = function(){
			 this.ed.oldValue = this.ed.value;
			 this.ed.oldRetVal = this.ed.getAttribute('retval');
			 this.ed.value = this.textContent;
			 var retval = this.getAttribute('retval') ? this.getAttribute('retval') : this.getAttribute('value');
			 if(!retval) this.ed.value = "";
			 this.ed.setAttribute('retval',retval);
			 this.ed.selectedItem = this;
			 if(typeof(this.ed.onselect) == "function")
			  this.ed.onselect();
			 else if(this.ed.onchange)
			  this.ed.onchange();
			}
		}

	  ed.popupmenu = popupmenu;
	  ed.onclick = function(){this.popupmenu.show();}
	  ed.onkeydown = function(){this.popupmenu.hide();}
	  ed.getValue = function(){
		 return this.getAttribute('retval');
		}
	  ed.setValue = function(title,value){
		 this.value = title;
		 this.setAttribute('retval',value);
		}
	  ed.restoreOldValue = function(){
		 this.setAttribute('retval',this.oldRetVal);
		 this.value = this.oldValue;
		}
	  ed.oldValue = ed.value;
	  ed.oldRetVal = ed.getAttribute('retval');
	 }
	} break;

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
	 oFCKeditor.Height = ed.offsetHeight;
	 oFCKeditor.ReplaceTextarea();
	 ed.getValue = function(){
		 var oEditor = FCKeditorAPI.GetInstance(this.id);
		 return oEditor.GetXHTML();
		}
	 ed.initialized = true;
	} break;


  case "bank" : {
	 var glBS = new GLBankSearch(ed);
	 ed.oldvalue = ed.value;
	 ed.getId = function(){
		 if(this.data)
		  return this.data['id'];
		 else if((this.value == this.oldvalue) && this.getAttribute('refid'))
		  return this.getAttribute('refid');
		 return 0;
		} 
	} break;

  case "gmart" : case "gproducts" : case "gpart" : case "gmaterial" : case "gbook" : case "lottomatica" : {
	 var glPS = new GLProductSearch(ed, extraParams, ed.getAttribute('fields'));
	 ed.oldvalue = ed.value;
	 ed.getId = function(){
		 if(this.data)
		  return this.data['id'];
		 else if((this.value == this.oldvalue) && this.getAttribute('refid'))
		  return this.getAttribute('refid');
		 return 0;
		}
	 ed.glPShist = glPS;
	 ed.setAT = function(at){this.glPShist.at = at;}
	 ed.setAP = function(ap){this.glPShist.ap = ap;}
	} break;

  case "gserv" : {
	 var glSS = new GLServiceSearch(ed, extraParams);
	 ed.oldvalue = ed.value;
	 ed.getId = function(){
		 if(this.data)
		  return this.data['id'];
		 else if((this.value == this.oldvalue) && this.getAttribute('refid'))
		  return this.getAttribute('refid');
		 return 0;
		}
	 ed.getAp = function(){return this.data ? this.data['ap'] : this.getAttribute('refap'); }
	 ed.getValue = function(){return this.data ? this.data['id'] : this.getAttribute('refid');}
	 ed.setValue = function(title,value){this.value = title; this.setAttribute('refid',value);}
	} break;


  case "vehicle" : {
	 var glBS = new GLVehicleSearch(ed, extraParams);
	 ed.oldvalue = ed.value;
	 ed.getId = function(){
		 if(this.data)
		  return this.data['id'];
		 else if((this.value == this.oldvalue) && this.getAttribute('refid'))
		  return this.getAttribute('refid');
		 return 0;
		}
	 ed.onmousedown = function(ev){
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
		   sh.sendCommand("gframe -f autofficina/edit.vehicle -params `id="+this.getId()+"`");
		 }
		}

	} break;

  case "address" : case "addresses" : {
	 var glSS = new GLAddressSearch(ed, extraParams);
	 ed.oldvalue = ed.value;
	 ed.getId = function(){
		 if(this.data)
		  return this.data['id'];
		 else if((this.value == this.oldvalue) && this.getAttribute('refid'))
		  return this.getAttribute('refid');
		 return 0;
		}
	 ed.getValue = function(){return this.data ? this.data['id'] : this.getAttribute('refid');}
	 ed.setValue = function(title,value){this.value = title; this.setAttribute('refid',value);}
	} break;

 }

 ed.initialized = true;

 return ed;
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.initBtn = function(btn, type)
{
 var oThis = this;
 switch(type)
 {
  case "itemselect" : {
	 btn.onclick = function(){
		 var sh = new GShell();
		 sh.OnOutput = function(o,itmId){
			 if(!itmId) return;
			 var sh2 = new GShell();
			 sh2.OnError = function(err){alert(err);}
			 sh2.OnOutput = function(o,a){
				 if(!a) return;
				 var ed = btn.getAttribute("connect") ? document.getElementById(btn.getAttribute("connect")) : null;
				 if(ed)
				 {
				  ed.data = a;
				  ed.value = a['name']; 
				 }
				}
			 sh2.sendCommand("dynarc item-info -ap '"+btn.getAttribute("ap")+"' -id '"+itmId+"'");
			}
		 sh.sendCommand("gframe -f dynarc.navigator -params `ap="+btn.getAttribute("ap")+"`");
		}

	} break;

  case "catselect" : {
	 btn.onclick = function(){
		 var sh = new GShell();
		 sh.OnOutput = function(o,catId){
			 if(!catId) return;
			 var sh2 = new GShell();
			 sh2.OnError = function(err){alert(err);}
			 sh2.OnOutput = function(o,a){
				 if(!a) return;
				 var ed = btn.getAttribute("connect") ? document.getElementById(btn.getAttribute("connect")) : null;
				 if(ed)
				 {
				  ed.data = a;
				  ed.value = a['name'];
				 }
				}
			 sh2.sendCommand("dynarc cat-info -ap '"+btn.getAttribute("ap")+"' -id '"+catId+"'");
			}
		 sh.sendCommand("gframe -f dynarc.categorySelect -params `ap="+btn.getAttribute("ap")+"`");
		}

	} break;

  case "popupmenu" : {
	 var popupmenu = btn.getAttribute("connect") ? document.getElementById(btn.getAttribute("connect")) : null;
	 if(popupmenu)
	 {
	  this.PopupMenuList.push(popupmenu);

	  popupmenu.btn = btn;
	  popupmenu.tmplH = this;
	  popupmenu.show = function(){
		 var pos = oThis.getAbsPos(this.btn);
		 this.style.left = pos.x+"px";
		 this.style.top = (pos.y+this.btn.offsetHeight)+"px";
		 var screenWidth = window.innerWidth ? window.innerWidth : document.body.clientWidth;
	     if((parseFloat(this.style.left)+this.offsetWidth) > screenWidth)
	     this.style.left = screenWidth - this.offsetWidth;
		 this.style.visibility = "visible";
		 if(this.btn.className == "textualmenu")
		  this.btn.className = "textualmenu menuselected";
		}

	  popupmenu.hide = function(){
		 this.style.visibility="hidden";
		 if(this.btn.className.indexOf(" menuselected") > 0)
		  this.btn.className = this.btn.className.replace(" menuselected", "");
		 if(this.tmplH.lastSubmenuActive)
		  this.tmplH.lastSubmenuActive.hide();
		}

	  btn.popupmenu = popupmenu;
	  btn.onclick = function(){this.popupmenu.show();}

	  // initialize elements
	  var list = popupmenu.getElementsByTagName('LI');
	  for(var c=0; c < list.length; c++)
	   this.initMenuItem(list[c]);
	 }
	} break;

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
	 btn.getAttachments = function(){return this.attacHinst.getAttachments();}
	} break;

  case "labels" : {
	 btn.handler = new GLLabels(btn);
	 btn.UpdateLabels = function(id){this.handler.reload(id);}
	} break;

  default : {
	 var ed = btn.getAttribute('connect') ? document.getElementById(btn.getAttribute('connect')) : null;
	 if(ed)
	 {
	  btn.ed = ed;
	  btn.onclick = function(){
		 if(typeof(this.ed.onchange) == "function")
		  this.ed.onchange();
		}
	 }
	} break;
 }

 btn.initialized = true;

 return btn;
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.initPopupMenu = function(ul)
{
 var oThis = this;
 this.PopupMenuList.push(ul);

 ul.show = function(btnObj,corrX, corrY){
	 var pos = oThis.getAbsPos(btnObj);
	 if(!isNaN(corrX)) pos.x+= corrX;
	 if(!isNaN(corrY)) pos.y+= corrY;
	 this.style.left = pos.x+"px";
	 this.style.top = (pos.y+btnObj.offsetHeight)+"px";

	 var screenWidth = window.innerWidth ? window.innerWidth : document.body.clientWidth;
     if((parseFloat(this.style.left)+this.offsetWidth) > screenWidth)
      this.style.left = screenWidth - this.offsetWidth - 5;

	 this.style.visibility = "visible";
	}

 ul.hide = function(){this.style.visibility="hidden";}
 return ul;
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.initPopupMessage = function(div)
{
 var oThis = this;
 this.PopupMessageList.push(div);
 div.onmouseover = function(){this.mouseisover=true;}
 div.onmouseout = function(){this.mouseisover=false;}

 div.show = function(btnObj,corrX, corrY){
	 var pos = oThis.getAbsPos(btnObj);
	 if(!isNaN(corrX)) pos.x+= corrX;
	 if(!isNaN(corrY)) pos.y+= corrY;
	 this.style.left = pos.x+"px";
	 this.style.top = (pos.y+btnObj.offsetHeight)+"px";
	 this.style.visibility = "visible";
	}

 div.hide = function(){
	 if(!this.mouseisover)
	  this.style.visibility="hidden";
	}

 return div;
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.initMenuItem = function(_LI)
{
 var oThis = this;

 var tmp = _LI.getElementsByTagName('UL');
 if(!tmp.length)
 {
  _LI.onmouseover = function(){
	 if(oThis.lastSubmenuActive)
	  oThis.lastSubmenuActive.hide();
	}
  return;
 }

 _LI.className = "subitem";
 _LI.onmouseup = function(ev){
	 ev.stopPropagation();
	 ev.cancelBubble = true;
	 ev.preventDefault();
	}

 var ul = tmp[0];
 ul.style.width = ul.offsetWidth+"px";
 this.initSubMenu(ul,_LI);
 _LI.submenu = ul;
 _LI.onmouseover = function(){
	 if(oThis.lastSubmenuActive)
	  oThis.lastSubmenuActive.hide();
	 this.submenu.show();
	}
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.initSubMenu = function(ul, _LI)
{
 var oThis = this;
 this.PopupMenuList.push(ul);

 ul.parentLI = _LI;
 ul.parentUL = _LI.parentNode;

 ul.show = function(){
	 var x = this.parentUL.offsetWidth-2;
	 var y = this.parentLI.offsetTop;
	 this.style.left = x+"px";
	 this.style.top = y+"px";
	 this.style.visibility = "visible";
	 oThis.lastSubmenuActive = this;
	}

 ul.hide = function(){this.style.visibility="hidden";}
 return ul;
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.initTabMenu = function(ul)
{
 ul.selectedItem = null;
 ul.selectedPage = null;
 ul.OnChange = function(pageId){} // RE-WRITABLE EVENT

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
	  else alert("GLightTemplate.initTabMenu error: page "+li.getAttribute('connect')+" does not exists.");
	 }
	
	 // Emit event
	 if(this.OnChange) this.OnChange(li.getAttribute('connect'));
	}

 return ul;
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.initSerp = function(ul)
{
 // INIT SIMPLE SERP OBJECT

 ul.labelO = null;
 ul.labelSchema = null;
 ul.priorBtn = null;
 ul.nextBtn = null;
 ul.serpCount = ul.getAttribute('data-serpcount') ? parseFloat(ul.getAttribute('data-serpcount')) : 0;
 ul.serpRPP = ul.getAttribute('data-serprpp') ? parseFloat(ul.getAttribute('data-serprpp')) : 0;
 ul.serpFrom = ul.getAttribute('data-serpfrom') ? parseFloat(ul.getAttribute('data-serpfrom')) : 0;

 var list = ul.getElementsByTagName('LI');
 for(var c=0; c < list.length; c++)
 {
  if(list[c].className == "label")
   ul.labelO = list[c];
  else if(list[c].className == "label-schema")
   ul.labelSchema = list[c].innerHTML;
  else if(!ul.priorBtn)
   ul.priorBtn = list[c];
  else if(!ul.nextBtn)
   ul.nextBtn = list[c];
 }
 
 if(ul.priorBtn) ul.priorBtn.onclick = function(){this.parentNode.priorBtnClick();}
 if(ul.nextBtn)  ul.nextBtn.onclick = function(){this.parentNode.nextBtnClick();}

 ul.priorBtnClick = function(){
	 if(this.disabled) return;
	 if(this.serpFrom < 1) return;

	 this.serpFrom-= this.serpRPP;
	 if(this.serpFrom < 0) this.serpFrom = 0;
	 this.update();
	 if(typeof(this.OnChange) == "function") this.OnChange();
	}

 ul.nextBtnClick = function(){
	 if(this.disabled) return;

	 this.serpFrom+= this.serpRPP;
	 this.update();
	 if(typeof(this.OnChange) == "function") this.OnChange();
	}

 ul.update = function(){
	 if(this.priorBtn)
	 {
	  if(this.serpFrom < 1) { this.priorBtn.className="first disabled"; this.priorBtn.disabled=true; }
	  else { this.priorBtn.className="first"; this.priorBtn.disabled=false; }
	 }
	 if(this.nextBtn)
	 {
	  if(this.serpCount <= this.serpFrom+this.serpRPP) { this.nextBtn.className = "last disabled"; this.nextBtn.disabled=true; }
	  else { this.nextBtn.className = "last"; this.nextBtn.disabled=false; }
	 }
	 // update label
	 if(this.labelO && this.labelSchema)
	 {
	  var text = this.labelSchema;
	  text = text.replace("{FROM}", this.serpFrom+1);
	  text = text.replace("{COUNT}", this.serpCount);
	  text = text.replace("{TO}", (this.serpFrom+this.serpRPP) < this.serpCount ? this.serpFrom+this.serpRPP : this.serpCount);
	  this.labelO.innerHTML = text;
	 }
	}
 
 ul.update();
 return ul;
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.registerPopupMenu = function(ul)
{
 this.PopupMenuList.push(ul);
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.initCollapseTable = function(tb)
{
 for(var c=0; c < tb.rows.length; c++)
 {
  var r = tb.rows[c];
  if(r.className == "lastrow")
   return tb;

  this.initCollapseTableRow(r,tb);

  if(r.className == "expanded")
  {
   tb.rows[c+1].style.display = "table-row";
  }
  
  c++;
 }
 return tb;
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.initCollapseTableRow = function(r,tb)
{
 r.tb = tb;

 if(tb.rows[r.rowIndex+1] && (tb.rows[r.rowIndex+1].className == "container"))
 {
  r.cR = tb.rows[r.rowIndex+1];
  if(r.cR.cells.length == 1)
   r.container = r.cR.cells[0];
  else
  {
   for(var c=0; c < r.cR.cells.length; c++)
   {
    if(r.cR.cells[c].colSpan > 1)
    {
	 r.container = r.cR.cells[c];
	 break;
	}
   }
  }
 }

 /* Inject functions */
 r.collapse = function(){
	 this.tb.rows[this.rowIndex+1].style.display = "none";
	 this.className = "collapsed";
	 if(typeof(this.tb.OnCollapse) == "function")
	  this.tb.OnCollapse(this);
	}

 r.expand = function(){
	 this.tb.rows[this.rowIndex+1].style.display = "table-row";
	 this.className = "expanded";
	 if(typeof(this.tb.OnExpand) == "function")
	  this.tb.OnExpand(this);
	}

 r.toggleExpand = function(){
	 if(this.className == "expanded")
	  this.collapse();
	 else
	  this.expand();
	}

 r.remove = function(){
	 this.tb.deleteRow(this.rowIndex+1);
	 this.tb.deleteRow(this.rowIndex);
	}

 /* Check cell by cell for title */
 for(var c=0; c < r.cells.length; c++)
 {
  if(r.cells[c].className == "title")
  {
   r.cells[c].r = r;
   r.cells[c].onclick = function(){this.r.toggleExpand();}
  }
 }

}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.initSortableTable = function(tb, defField, defSort, hasSchema)
{
 tb.activeFieldA = null;
 tb.hasSchema = hasSchema ? true : false;
 tb.orderByField = "";
 tb.orderByMethod = "";	// ASC or DESC

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
	 tb.orderByField = this.th.getAttribute('field');
	 tb.orderByMethod = (this.className == "sortasc") ? "ASC" : "DESC";
	 tb.OnSort(tb.orderByField, tb.orderByMethod);
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
	 for(var c=1; c < this.rows.length; c++)
	 {
	  var cb = this.rows[c].cells[0].getElementsByTagName('INPUT')[0];
	  if(!cb)
	   continue;
	  if(cb.checked)
	   ret.push(this.rows[c]);
	 }
	 return ret;
	};

 tb.selectAll = function(){
	 if(!this.rows.length)
	  return;
	 for(var c=1; c < this.rows.length; c++)
	 {
	  if(typeof(this.rows[c].select) == 'function')
	   this.rows[c].select();
	 }
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
	 {
	  if(typeof(this.rows[c].unselect) == 'function')
	   this.rows[c].unselect();
	 }
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

 tb.addRow = function(){
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
	 var cb = r.cells[0].getElementsByTagName('INPUT')[0];
	 if(cb && (cb.type == "checkbox"))
	 {
	  cb.r = r;
	  cb.onclick = function(){this.r.toggleSelect();}
	 }

	 r.setValue = function(key, value){
		 var html = this.innerHTML;
		 while(html.indexOf(key) > -1)
		  html = html.replace(key, value);
		 this.innerHTML = html;
		}

  	 r.select = function(shotEvent){
	 	 var cb = this.cells[0].getElementsByTagName('INPUT')[0];
	 	 if(!cb) return;
	 	 this.className = "selected";
	 	 cb.checked = true;
	 	 if(shotEvent && this.tb.OnSelect)
	  	 this.tb.OnSelect(this.tb.getSelectedRows());
		};
  
  	 r.unselect = function(shotEvent){
	 	 var cb = this.cells[0].getElementsByTagName('INPUT')[0];
	 	 if(!cb) return;
	 	 this.className = "";
	 	 cb.checked = false;
	 	 if(shotEvent && this.tb.OnSelect)
	  	  this.tb.OnSelect(this.tb.getSelectedRows());
		};

  	 r.toggleSelect = function(){
	 	 var cb = this.cells[0].getElementsByTagName('INPUT')[0];
	 	 if(!cb) return;
	 	 this.className = cb.checked ? "selected" : "";
	 	 if(this.tb.OnSelect)
	  	  this.tb.OnSelect(this.tb.getSelectedRows());
		};

	 r.getCellByField = function(field){
		 var th = this.tb.colByField[field];
		 if(!th) return false;
		 return this.cells[th.cellIndex];
		};

  	 r.remove = function(){this.tb.deleteRow(this.rowIndex);}

	}

 /* INJECT ROWS */
 tb.injectRows();

 return tb;
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.initEditableText = function(obj, width, height, className)
{
 obj.ed = document.createElement('INPUT');
 obj.ed.type = "text";
 obj.ed.className = className ? className : "editabletext";
 if(width) obj.ed.style.width = width;
 if(height) obj.ed.style.height = height;
 obj.ed.style.display = "none";
 obj.parentNode.appendChild(obj.ed);
 obj.ed.obj = obj;
 obj.ed.onchange = function(){this.obj.editexit();}
 obj.ed.onblur = function(){this.obj.editexit();}
 obj.style.cursor = "text";

 obj.onclick = function(ev){
	 if(this.disabled)
	  return;
	 if(!ev) ev = window.event;
	 ev.preventDefault();
	 if(ev.stopPropagation)
      ev.stopPropagation();
     else
      ev.cancelBubble = true;
	 this.edit();
	}
 obj.edit = function(){
	 if(this.editmode)
	  return;
	 this.style.display = "none";
	 this.ed.style.display = "";
	 this.ed.value = this.innerHTML;
	 this.editmode = true;
	 this.ed.focus();
	 this.ed.select();
	}

 obj.editexit = function(){
	 if(!this.editmode)
	  return;
	 this.innerHTML = this.ed.value;
	 this.value = this.ed.value;
	 this.ed.style.display = "none";
	 this.style.display = "";
	 this.editmode = false;
	 if(typeof(this.onchange) == "function")
	  this.onchange();
	}

 return obj;
}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.initToggles = function(ul)
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
GLightTemplate.prototype.showCal = function(ed)
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
GLightTemplate.prototype.getAbsPos = function(e)
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
GLightTemplate.prototype.Print = function()
{

}
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype.getScreenWidth = function(){return window.innerWidth ? window.innerWidth : document.body.clientWidth;}
GLightTemplate.prototype.getScreenHeight = function(){return window.innerHeight ? window.innerHeight : document.body.clientHeight;}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
GLightTemplate.prototype._onMouseUp = function(ev)
{
 for(var c=0; c < this.PopupMenuList.length; c++)
  this.PopupMenuList[c].hide();
 for(var c=0; c < this.PopupMessageList.length; c++)
  this.PopupMessageList[c].hide();
 if(this.MainMenuO)
  this.MainMenuO.style.visibility = "hidden";
}
//-------------------------------------------------------------------------------------------------------------------//
var Template = new GLightTemplate();

function hideAllPopupMenu(ev)
{
 Template._onMouseUp(ev);
}

document.addEventListener ? document.addEventListener("mouseup",hideAllPopupMenu,false) : document.attachEvent("onmouseup",hideAllPopupMenu);


