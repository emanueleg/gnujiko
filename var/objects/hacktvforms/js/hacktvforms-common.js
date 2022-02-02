/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 04-06-2013
 #PACKAGE: hacktvsearch-common
 #DESCRIPTION: 
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/
//-------------------------------------------------------------------------------------------------------------------//
function HackTVFormHandler()
{
 this.Forms = new Array();
 this.FormById = new Array();
 this.mousepos = null;

 this.ClipBoard = new Array();

 document.addEventListener ? document.addEventListener("mousemove",HACKTVFORMHANDLER_MOUSEMOVE,false) : document.attachEvent("onmousemove",HACKTVFORMHANDLER_MOUSEMOVE);
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVFormHandler.prototype._onmousemove = function(ev)
{
 ev = ev || window.event;
 if(ev.pageX || ev.pageY)
  var mousepos = {x:ev.pageX, y:ev.pageY}; 
 else
  var mousepos = {x:ev.clientX + document.body.scrollLeft - document.body.clientLeft, y:ev.clientY + document.body.scrollTop  - document.body.clientTop};

 this.mousepos = mousepos;

 for(var c=0; c < this.Forms.length; c++)
  this.Forms[c]._onmousemove(mousepos);
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVFormHandler.prototype.saveToClipboard = function(refer, data)
{
 this.ClipBoard[refer] = data
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVFormHandler.prototype.readFromClipboard = function(refer)
{
 return this.ClipBoard[refer];
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVFormHandler.prototype.makeScreenShot = function(formId, callback)
{
 var form = this.FormById[formId];
 if(!form)
  return;

 ScreenShot(form.bodyO, callback);
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
var HACKTVFORM_HANDLER = new HackTVFormHandler();

function HACKTVFORMHANDLER_MOUSEMOVE(ev){HACKTVFORM_HANDLER._onmousemove(ev);}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function HackTVForm(theme, width, height)
{
 this.O = document.createElement('DIV');
 this.theme = theme ? theme : "bluewidget";
 var date = new Date();
 this.ID = "HTF-"+date.getTime()+"-"+date.getMilliseconds();
 this.O.id = this.ID;
 this.Width = width;
 this.Height = height;
 this.O.style.zIndex = 9900+HACKTVFORM_HANDLER.Forms.length;

 /* PRIVATE */
 this._csslist = new Array();
 this._jslist = new Array();

 this._layerName = "";
 this._layerArgs = "";
 this._layerData = null;

 /* EVENTS */

 this._init();

 HACKTVFORM_HANDLER.Forms.push(this);
 HACKTVFORM_HANDLER.FormById[this.ID] = this;
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVForm.prototype.Show = function(title, posLeft, posTop)
{
 
 if(title)
  this.SetTitle(title);
 if(posLeft)
  this.O.style.left = posLeft;
 else
  this.O.style.left = Math.floor(this._getScreenWidth()/2) - Math.floor(this.Width/2);
 if(posTop)
  this.O.style.top = posTop;
 else
  this.O.style.top = Math.floor(this._getScreenHeight()/2) - Math.floor(this.Height/2);

 this.O.style.visibility = "visible";
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVForm.prototype.SetTitle = function(newTitle)
{
 this.titleO.innerHTML = newTitle;
 this.traytitleO.innerHTML = newTitle;
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVForm.prototype.SetContent = function(newContent)
{
 this.bodyO.innerHTML = newContent;
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVForm.prototype.Resize = function(w,h)
{
 this.Width = w ? w : this.Width;
 this.Height = h ? h : this.Height;

 this.bodyO.style.width = this.Width ? this.Width : "100%";
 this.bodyO.style.height = this.Height ? this.Height : "";
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVForm.prototype.Close = function()
{
 if(this.O.parentNode)
  this.O.parentNode.removeChild(this.O);
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVForm.prototype.OpenTray = function()
{
 var oThis = this;
 var r = document.getElementById(this.ID+"-hdrrow");
 r.className = "header-open";
 this.mainmenubuttonO.onclick = function(){oThis.CloseTray();}
 document.getElementById(this.ID+"-tray").style.display = "";
 this.titleO.style.color = "transparent";
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVForm.prototype.CloseTray = function()
{
 var oThis = this;
 var r = document.getElementById(this.ID+"-hdrrow");
 r.className = "header-closed";
 this.mainmenubuttonO.onclick = function(){oThis.OpenTray();}
 document.getElementById(this.ID+"-tray").style.display = "none";
 this.titleO.style.color = "";
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVForm.prototype.LoadLayer = function(layerName, arguments, data)
{
 if(!arguments)
  var arguments = "hacktvformid="+this.O.id;
 else
  arguments+= "&hacktvformid="+this.O.id;

 this._layerName = layerName;
 this._layerArgs = arguments;
 this._layerData = data;

 this.Layer = new Layer();
 this.Layer.OnError = function(err){
	 alert(err);
	};
 this.Layer.OnLoad = function(){
	 if(typeof(HackTVFormOnLoad) == "function")
	  HackTVFormOnLoad(this,data);
	}
 this.Layer.load("hacktvforms/"+layerName,arguments,this.bodyO,true);

 var oThis = this;

 this.TLayer = new Layer();
 this.TLayer.id = this.Layer.id;
 this.TLayer.OnError = function(err){alert(err);}
 this.TLayer.OnLoad = function(){
	 var tmp = document.getElementById(oThis.O.id+"-tray");
	 tmp.style.visibility = "hidden"; tmp.style.display = "";
	 var h = oThis.trayO.offsetHeight+10;
	 tmp.style.height = h+"px";
	 tmp.style.marginTop = "-"+h+"px";
	 tmp.style.display = "none"; tmp.style.visibility = "visible";

	 if(typeof(HackTVFormTrayOnLoad) == "function")
	  HackTVFormTrayOnLoad(oThis.Layer,data);
	}
 this.TLayer.load("hacktvforms/"+layerName+"/tray",arguments,this.trayO,true);
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//---- P R I V A T E ------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
HackTVForm.prototype._init = function()
{
 var oThis = this;
 this._loadCSS(ABSOLUTE_URL+"var/objects/hacktvforms/css/"+this.theme+".css");

 this.O.className = "hacktvform";
 this.O.style.left = 0;
 this.O.style.top = 0;
 this.O.style.visibility = "hidden";

 var imgFolder = ABSOLUTE_URL+"var/objects/hacktvforms/img/"+this.theme+"/";

 /* TRAY */
 var html = "<div class='hacktvform-"+this.theme+"-tray' id='"+this.O.id+"-tray' style='display:none'>";
 html+= "<table width='100%' height='100%' class='hacktvform-"+this.theme+"-tray' cellspacing='0' cellpadding='0' border='0'>";
 html+= "<tr><td class='trayleftcorner'>&nbsp;</td>";
 html+= "<td class='traytitle' id='"+this.O.id+"-traytitle'>&nbsp;</td>";
 html+= "<td class='trayrightcorner'>&nbsp;</td></tr>";
 html+= "<tr><td class='trayleft'>&nbsp;</td><td class='traybody' id='"+this.O.id+"-traybody'>&nbsp;</td><td class='trayright'>&nbsp;</td></tr>";
 html+= "</table>";
 html+= "</div>";

 /* HEADER */
 html+= "<table width='100%' class='hacktvform-"+this.theme+"' cellspacing='0' cellpadding='0' border='0'>";
 html+= "<tr class='header-closed' id='"+this.O.id+"-hdrrow'><td class='topleftcorner'><img src='"+imgFolder+"mainmenubutton.png' id='"+this.O.id+"-mainmenubutton'/></td>";
 html+= "<td class='headertitle' id='"+this.O.id+"-title'>&nbsp;</td>";
 html+= "<td class='toprightcorner'><img src='"+imgFolder+"closebutton.png' id='"+this.O.id+"-closebutton'/></td></tr>";

 /* BODY */
 html+= "<tr><td class='border-left'>&nbsp;</td>";
 html+= "<td valign='top'><div class='hacktvform-"+this.theme+"-body' id='"+this.O.id+"-body'></div></td>";
 html+= "<td class='border-right'>&nbsp;</td></tr>";

 /* FOOTER */
 html+= "<tr class='footer'><td class='bottomleftcorner'>&nbsp;</td>";
 html+= "<td class='footer-content' id='"+this.O.id+"-footer'>&nbsp;</td>";
 html+= "<td class='bottomrightcorner'>&nbsp;</td></tr>";

 html+= "</table>";

 this.O.innerHTML = html;

 document.body.appendChild(this.O);

 this.traytitleO = document.getElementById(this.ID+"-traytitle");
 this.titleO = document.getElementById(this.ID+"-title");
 this.mainmenubuttonO = document.getElementById(this.ID+"-mainmenubutton");
 this.closebuttonO = document.getElementById(this.ID+"-closebutton");
 this.trayO = document.getElementById(this.ID+"-traybody");
 this.bodyO = document.getElementById(this.ID+"-body");
 this.footerO = document.getElementById(this.ID+"-footer");
 
 this.titleO.onmousedown = function(){oThis._onmousedown();}
 this.titleO.onmouseup = function(){oThis._onmouseup();}
 this.mainmenubuttonO.onclick = function(){oThis.OpenTray();}
 this.mainmenubuttonO.style.cursor = "pointer";
 this.closebuttonO.onclick = function(){oThis.Close();}
 this.closebuttonO.style.cursor = "pointer";

 if(this.Width)
  this.bodyO.style.width = this.Width;
 if(this.Height)
  this.bodyO.style.height = this.Height;
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVForm.prototype._loadCSS = function(file)
{
 var ls = document.getElementsByTagName('LINK');
 for(var c=0; c < ls.length; c++)
 {
  if(ls[c].href == file)
   return true;
 }
 var css  = document.createElement('LINK');
 css.rel = 'stylesheet';
 css.href = file;
 css.type = 'text/css';
 document.getElementsByTagName('HEAD').item(0).appendChild(css);
 this._csslist.push(css);
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVForm.prototype._loadJS = function(file)
{
 var ls = document.getElementsByTagName('SCRIPT');
 for(var c=0; c < ls.length; c++)
 {
  if(ls[c].src == file)
   return true;
 }
 var script  = document.createElement('SCRIPT');
 script.src  = file;
 script.type = 'text/javascript';
 document.getElementsByTagName('HEAD').item(0).appendChild(script);
 this._jslist.push(script);
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVForm.prototype._getScreenWidth = function()
{
 if(window.innerWidth)
  return window.innerWidth;
 else if(document.all)
  return document.body.clientWidth;
 return 0;
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVForm.prototype._getScreenHeight = function()
{
 if(window.innerHeight)
  return window.innerHeight;
 else if(document.all)
  return document.body.clientHeight;
 return 0;
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVForm.prototype._onmousedown = function()
{
 var mousepos = HACKTVFORM_HANDLER.mousepos;
 this.mousedown = true;
 this.diffX = mousepos.x - parseFloat(this.O.style.left);
 this.diffY = mousepos.y - parseFloat(this.O.style.top);
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVForm.prototype._onmouseup = function()
{
 this.mousedown = false;
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVForm.prototype._onmousemove = function(mousepos)
{
 if(!this.mousedown)
  return;

 this.O.style.left = mousepos.x - this.diffX;
 this.O.style.top = mousepos.y - this.diffY;
}
//-------------------------------------------------------------------------------------------------------------------//

