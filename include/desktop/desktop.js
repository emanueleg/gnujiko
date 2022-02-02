/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 26-02-2013
 #PACKAGE: gnujiko-desktop-base
 #DESCRIPTION: Gnujiko Desktop handler.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

//-------------------------------------------------------------------------------------------------------------------//
function GnujikoDesktopHandler()
{
 this.Plugs = new Array();

 this.SelectedPlug = null;
 this.NewCable = null;
 this.SelectedModule = null;

 var oThis = this;

 /* INITIALIZE CABLE HANDLER */
 SIMPLE_CABLE_HANDLER.init(document.getElementById('desktop-page-container'));
 SIMPLE_CABLE_HANDLER.OnDeleteCable = function(cable){
	 if((cable.plugSrc == oThis.SelectedPlug) || (cable.plugDest == oThis.SelectedPlug))
	  oThis.SelectedPlug = null;
	 if(cable.plugSrc && cable.plugDest)
	 {
	  var sh = new GShell();
	  sh.sendCommand("desktop disconnect -page `"+GNUJIKO_DESKTOP_PAGEID+"` -src `"+cable.plugSrc.module.id.substr(10)+"` -src-port `"+cable.plugSrc.getAttribute('plugname')+"` -dest `"+cable.plugDest.module.id.substr(10)+"` -dest-port `"+cable.plugDest.getAttribute('plugname')+"`");
	 }
	 if(cable.plugSrc) cable.plugSrc.disconnect(cable);
	 if(cable.plugDest) cable.plugDest.disconnect(cable);
	}

 /* CREATE MODULE MENU */
 this._moduleMenuButton = document.getElementById('gnujiko-desktop-module-menu-button');
 this.ModuleMenu = document.getElementById('gnujiko-desktop-module-menu');
 this.ModuleMenu.onclick = function(){this.style.visibility="hidden";}
 this.ModuleMenu.onmouseover = function(){this.mouseinto=true;}
 this.ModuleMenu.onmouseout = function(){this.mouseinto=false;}
 this._moduleMenuButton.menu = this.ModuleMenu;
 this._moduleMenuButton.onclick = function(){
	 var module = SDD_HANDLER.hintedObject;
	 var pos = SDD_HANDLER._getABSobjPos(module,document.getElementById('desktop-page-container'));
	 pos.x+= module.offsetWidth;
	 pos.x-= (this.menu.offsetWidth-2);
	 this.menu.style.left = pos.x;
	 this.menu.style.top = pos.y-2;
	 this.menu.style.visibility = "visible";
	 oThis.SelectedModule = module;
	}

 var addModBtn = document.getElementById('desktop-add-module-button');
 addModBtn.style.display = "";
 addModBtn.onclick = function(){
	 var sh = new GShell();
	 sh.OnOutput = function(o,a){
		 if(!a) return;
		 var sh2 = new GShell();
		 sh2.OnOutput = function(){document.location.reload();}
		 sh2.sendCommand("desktop add-module -name `"+a+"` -page `"+GNUJIKO_DESKTOP_PAGEID+"`");
		}
	 sh.sendCommand("gframe -f desktop/insert");
	}
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoDesktopHandler.prototype.registerModule = function(moduleObj, moduleInfo)
{
 var oThis = this;
 moduleObj.plugs = new Array();
 moduleObj.plugByName = new Array();

 SDD_HANDLER.setDraggableObject(moduleObj, moduleInfo.handle ? document.getElementById(moduleInfo.handle) : null);
 if(moduleInfo.plugs && moduleInfo.plugs.length) // register module plugs
 {
  for(var i=0; i < moduleInfo.plugs.length; i++)
   this.registerPlug(document.getElementById(moduleInfo.plugs[i]), moduleObj);
 }

 moduleObj.ondrop = function(){
	 var droparea = this.parentNode;
	 if(!droparea) return;
	 if(typeof(droparea.sdd_serialize) != "function") return;
	 var list = droparea.sdd_serialize();
	 var q = "";
	 for(var c=0; c < list.length; c++)
	  q+= ","+list[c].id.substr(10); /* remove the gjkdskmod- prefix */
	 var sh = new GShell();
	 sh.sendCommand("desktop edit-module -id `"+this.id.substr(10)+"` -sec `"+droparea.id+"` && desktop serialize-module "+q.substr(1));
	}

 moduleObj.ondrag = function(){
	 oThis._moduleMenuButton.style.display = "none";
	 oThis.ModuleMenu.style.visibility = "hidden";
	}

 moduleObj.output = function(port, data){
	 var plug = this.plugByName[port];
	 if(!plug)
	  return;
	 plug.sendData(data);
	}

 moduleObj.onhint = function(){
	 if(SDD_HANDLER.draginprogress)
	  return;
	 var pos = SDD_HANDLER._getABSobjPos(this,document.getElementById('desktop-page-container'));

	 /* show menu button */
	 var img = oThis._moduleMenuButton;
	 img.style.visibility = "hidden";
	 img.style.display = "block";
	 img.style.left = ((pos.x+this.offsetWidth)-img.offsetWidth)+2;
	 img.style.top = pos.y-2;
	 img.style.visibility = "visible";
	}

 moduleObj.onunhint = function(){
	 if(oThis.ModuleMenu.mouseinto)
	  return;
	 oThis._moduleMenuButton.style.display = "none";
	 oThis.ModuleMenu.style.visibility = "hidden";
	}

 /* LOAD MODULE */
 if(moduleInfo.front)
 {
  var tmp = document.getElementById(moduleInfo.front);
  if(tmp && tmp.getAttribute('onload'))
   callFunction(tmp.getAttribute('onload'));
 }
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoDesktopHandler.prototype.renameModule = function(moduleInfo)
{
 if(!moduleInfo.front)
  moduleInfo.front = moduleInfo.id+"-front";
 if(moduleInfo.front)
 {
  var tmp = document.getElementById(moduleInfo.front);
  if(tmp && tmp.getAttribute('onrename'))
   callFunction(tmp.getAttribute('onrename'));
  else
   alert("Spiacente, non è possibile rinominare questo modulo");
 }
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoDesktopHandler.prototype.unregisterModule = function(module)
{
 /* Disconnect all cables */
 for(var i=0; i < module.plugs.length; i++)
 {
  var plug = module.plugs[i];
  for(var c=0; c < plug.connectedTo.length; c++)
  {
   var destMod = plug.connectedTo[c].module;
   var destPlug = plug.connectedTo[c].plugobj;

   for(var j=0; j < destPlug.connectedTo.length; j++)
   {
    if(destPlug.connectedTo[j].module == module)
	{
	 destPlug.connectedTo.splice(j,1);
	 break;
    }
   }
  }

  // free all cables
  for(var c=0; c < plug.cables.length; c++)
   plug.cables[c].free();
 }

 this._moduleMenuButton.style.display = "none";
 this.ModuleMenu.style.visibility = "hidden";

 SDD_HANDLER.unsetDraggableObject(module);
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoDesktopHandler.prototype.registerPlug = function(plugObj, moduleObj)
{
 if(!plugObj)
  return;

 if(!plugObj.getAttribute('plugname') || !plugObj.getAttribute('plugtype')) // invalid plug
  return;

 if(moduleObj.plugByName[plugObj.getAttribute('plugname')]) // plug name already exists
  return;

 var oThis = this;

 plugObj.style.cursor = "pointer";
 plugObj.module = moduleObj;
 plugObj.cables = new Array();
 plugObj.connectedTo = new Array();

 plugObj.onclick = function(){oThis._plugOnClick(this);}

 plugObj.startNewCable = function(){
	 // create new cable
	 var img = document.createElement('IMG');
	 img.src = ABSOLUTE_URL+"include/desktop/img/plug-blue.png";
	 var cable = new SimpleCable(this,this.module,null,null,"blue",img,1,-2);
	 this.src = ABSOLUTE_URL+"include/desktop/img/plug-blue.png";
	 this.cables.push(cable);
	 return cable;
	}

 plugObj.connectTo = function(plug, cable){
	 if(!plug  || (plug == this)) return;
	 if(cable)
	 {
	  cable.plugDest = plug;
	  cable.objDest = plug.module;
	  if(cable.plugTmp)
	   cable.plugTmp.style.display = "none";
	  plug.cables.push(cable);
	  cable.update();
	  var sh = new GShell();
	  sh.sendCommand("desktop connect -page "+GNUJIKO_DESKTOP_PAGEID+" -src `"+this.module.id.substr(10)+"` -src-port `"+this.getAttribute('plugname')+"` -dest `"+plug.module.id.substr(10)+"` -dest-port `"+plug.getAttribute('plugname')+"`");
	 }
	 else
	  this.cables.push(new SimpleCable(this,this.module,plug,plug.module,"blue",null,1,-2));
	 plug.src = ABSOLUTE_URL+"include/desktop/img/plug-blue.png";
	 this.connectedTo.push({module:plug.module, plugobj:plug, port:plug.getAttribute('plugname')});
	 plug.connectedTo.push({module:this.module, plugobj:this, port:this.getAttribute('plugname')});
	}

 plugObj.disconnect = function(cable){
	 if(cable.plugSrc == this)
	 {
	  if(cable.plugDest)
	  {
	   for(var c=0; c < this.connectedTo.length; c++)
	   {
	    if((this.connectedTo[c].module == cable.plugDest.module) && (this.connectedTo[c].port == cable.plugDest.getAttribute('plugname')))
		{
		 this.connectedTo.splice(c,1);
		 break;
		}
	   }
	   for(var c=0; c < cable.plugDest.connectedTo.length; c++)
	   {
	    if((cable.plugDest.connectedTo[c].module == this.module) && (cable.plugDest.connectedTo[c].port == this.getAttribute('plugname')))
		{
		 cable.plugDest.connectedTo.splice(c,1);
		 break;
		}
	   }
	  }
	  cable.plugSrc = null;
	 }
	 else if(cable.plugDest == this)
	 {
	  if(cable.plugSrc)
	  {
	   for(var c=0; c < this.connectedTo.length; c++)
	   {
	    if((this.connectedTo[c].module == cable.plugSrc.module) && (this.connectedTo[c].port == cable.plugSrc.getAttribute('plugname')))
		{
		 this.connectedTo.splice(c,1);
		 break;
		}
	   }
	   for(var c=0; c < cable.plugSrc.connectedTo.length; c++)
	   {
	    if((cable.plugSrc.connectedTo[c].module == this.module) && (cable.plugSrc.connectedTo[c].port == this.getAttribute('plugname')))
		{
		 cable.plugSrc.connectedTo.splice(c,1);
		 break;
		}
	   }
	  }
	  cable.plugDest = null;
	 }
	 if(this.cables.indexOf(cable) > -1)
	  this.cables.splice(this.cables.indexOf(cable),1);
	 if(!this.cables.length)
	  this.src = ABSOLUTE_URL+"include/desktop/img/plug.png";
	 else // get the color of the last cable
	  this.src = ABSOLUTE_URL+"include/desktop/img/plug-"+this.cables[this.cables.length-1].color+".png";
	}

 plugObj.sendData = function(data){
	 for(var c=0; c < this.connectedTo.length; c++)
	 {
	  var plug = this.connectedTo[c].plugobj;
	  if(typeof(plug.oninput) == "function")
	   plug.oninput(data, this.module, this);
	 }
	}

 moduleObj.plugs.push(plugObj);
 moduleObj.plugByName[plugObj.getAttribute('plugname')] = plugObj;
 this.Plugs.push(plugObj);
 SIMPLE_CABLE_HANDLER.registerPlug(plugObj);
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoDesktopHandler.prototype.deleteModule = function(module)
{
 var oThis = this;
 var sh = new GShell();
 sh.OnOutput = function(){
	 var pn = module.parentNode;
	 oThis.unregisterModule(module);
	 module.parentNode.removeChild(module);
	 if(pn.innerHTML == "")
	  pn.innerHTML = "&nbsp;";
	}
 sh.sendCommand("desktop delete-module -id `"+module.id.substr(10)+"`");
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoDesktopHandler.prototype.moveModule = function(module)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.href = ABSOLUTE_URL+"index.php?desktop="+a['pageid'];
	}
 sh.sendCommand("gframe -f desktop/move -params `moduleid="+module.id.substr(10)+"&pageid="+GNUJIKO_DESKTOP_PAGEID+"`");
}
//-------------------------------------------------------------------------------------------------------------------//
GnujikoDesktopHandler.prototype.configureModule = function(module)
{
}
//-------------------------------------------------------------------------------------------------------------------//
//--- P R I V A T E -------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
GnujikoDesktopHandler.prototype._plugOnClick = function(plugObj)
{
 if(!this.SelectedPlug)
 {
  /* START NEW CONNECTION */
  this.SelectedPlug = plugObj;
  this.NewCable = this.SelectedPlug.startNewCable();
 }
 else
 {
  /* CONNECT ACTIVE CABLE TO THIS PLUG */
  this.SelectedPlug.connectTo(plugObj, this.NewCable);
  this.NewCable = null;
  this.SelectedPlug = null;
 }
}
//-------------------------------------------------------------------------------------------------------------------//

