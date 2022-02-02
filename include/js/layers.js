/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 22-02-2013
 #PACKAGE: gnujiko-layers
 #DESCRIPTION: Gnujiko layers support
 #VERSION: 2.1beta
 #CHANGELOG: 22-02-2013 : Aggiunta la possibilità di specificare la directory dei layers. (default: var/layers/)
			 02-03-2012 : Bug fix in layer load error.
			 26-02-2012 : Aggiunto funzione shotMessage (che deve essere lanciata dal layer per interagire con l'oggetto layer lanciante).
			 06-09-2011 : bug fix in Layer::remove function
			 19-07-2010 : aggiunto argomento callbackFunction
 #TODO:
 
*/

var LAYER_SCREEN_MASK = null;
var Layers = new Array();

function Layer(layName, arguments, destObj, clearObj, callbackFunction, callbackTimer)
{
 /* PRIVATE */
 this._destObj = destObj;
 this._csslist = new Array();
 this._jslist = new Array();
 this._layerPath = "var/layers/";

 /* STATUS */
 this.loaded = false;
 this.loading = false;
 

 /* EVENTS */
 this.OnLoad = callbackFunction ? callbackFunction : null;
 this.OnError = null;
 this.OnMessage = null;

 this.id = Layers.length;
 Layers.push(this);

 if(layName)
  this.load(layName, arguments, destObj, clearObj, callbackFunction, callbackTimer);
}

Layer.prototype.load = function(layName, arguments, destObj, clearObj, callbackFunction, callbackTimer)
{
 if(this.loading) // evita di ricaricare un layer quando ancora deve finire di caricare quello in corso.
  return;

 var oThis = this;
 this.LayerName = layName;
 this.Arguments = arguments;
 this._destObj = destObj;
 var HR = new GHTTPRequest();
 HR.OnError = function(){
	  oThis.loaded = false;
	  oThis.loading = false;
	  if(oThis.OnError)
	   oThis.OnError("Unable to load layer "+layName,"FILE_NOT_FOUND");
	 }
 HR.OnHttpRequest = function(reqObj){
	 if(!reqObj || !reqObj.responseText)
	 {
	  oThis.loading = false;
	  if(oThis.OnError)
	   return oThis.OnError("Unable to load layer "+layName,"LAYER_LOAD_PROBLEM");
	  else
	   return alert("Unable to load layer "+layName);
	 }

	 if(clearObj)
	  destObj.innerHTML = "";
	 var contents = reqObj.responseText;
	 //--- getting javascripts ---//
	 var scriptAdd = "";
	 var addstr = "";
	 var scripts = contents.match(/<script[^>]*>[^<>]*<\/script>/ig);
	 contents = contents.replace(/<script[^>]*>[^<>]*<\/script>/ig,"");
	 //--- getting css ---//
	 var css = contents.match(/<link[^>]*>/ig);
	 if(css)
	 {
	  for(var c=0; c < css.length; c++)
	  {
	   var m = css[c].match(/href=(.+?\.css)/);
	   if(m && m[1])
	    oThis._includeCSS(m[1].substr(1,m[1].length));
	  }
	 }
	 contents = contents.replace(/<link[^>]*>/ig,"");

	 if(!clearObj)
	 {
	  var tmpDiv = document.createElement('DIV');
	  tmpDiv.innerHTML = contents;
	  while(tmpDiv.childNodes.length)
	   destObj.appendChild(tmpDiv.childNodes[0]);
	 }
	 else
	  destObj.innerHTML = contents;

	 if(scripts)
	 {
	  for(var c=0; c < scripts.length; c++)
	  {
	   var m = scripts[c].match(/src=(.+?\.js)/);
	   if(m && m[1])
	    oThis._includeJS(m[1].substr(1,m[1].length));
	   else
	   {
	    var s = scripts[c].replace(/<script[^>]*>/,"");
	    s = s.replace(/<\/script>/,"");
	    scriptAdd+= s;
	   }
	  }
	 }

	 if(scriptAdd)
	 {
	  var script  = document.createElement('SCRIPT');
	  script.type = 'text/javascript';
	  script.innerHTML = scriptAdd;
	  document.getElementsByTagName('HEAD').item(0).appendChild(script);
	  oThis._jslist.push(script);
	 }

	 oThis.loaded = true;
	 oThis.loading = false;

	 if(callbackFunction)
	  window.setTimeout(function(){callbackFunction(oThis);},callbackTimer ? callbackTimer : 500);
	 else if(oThis.OnLoad)
	  window.setTimeout(function(){oThis.OnLoad(oThis);},500);
	}
 oThis.loading=true;
 HR.send(BASE_PATH+this._layerPath+layName+"/index.php?basepath="+BASE_PATH+"&layerid="+this.id+(arguments ? "&"+arguments : ""));
}

Layer.prototype.empty = function()
{
 if(this.loading) // evita di svuotare un layer quando ancora deve finire di caricare. 
  return;

 /* remove all css */
 for(var c=0; c < this._csslist.length; c++)
  this._csslist[c].parentNode.removeChild(this._csslist[c]);
 /* remove all js */
 for(var c=0; c < this._jslist.length; c++)
  this._jslist[c].parentNode.removeChild(this._jslist[c]);
 this._csslist = new Array();
 this._jslist = new Array();
 this._destObj.innerHTML = "";
}

Layer.prototype.reload = function(arguments, callbackFunction, callbackTimer)
{
 this.empty();
 if(typeof(arguments) != "string")
  arguments = this.Arguments;

 this.load(this.LayerName, arguments, this._destObj, false, callbackFunction, callbackTimer);
}

Layer.prototype.remove = function()
{
 this.empty();
 Layers[this.id] = null;
}

Layer.prototype.shotMessage = function(msg,data)
{
 if(this.OnMessage)
  this.OnMessage(msg, data);
}

Layer.prototype._includeCSS = function(file)
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

Layer.prototype._includeJS = function(file)
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
function NewLayer(layName, arguments, destObj, clearObj, callbackFunction, callbackTimer)
{
 /*if(Layers[layName])
  return;*/
 var layerHR = new GHTTPRequest();
 layerHR.OnHttpRequest = function(reqObj){
	 if(!reqObj || !reqObj.responseText)
	  return alert("Unable to load layer "+layName);
	 if(clearObj)
	  destObj.innerHTML = "";
	 var contents = reqObj.responseText;
	 //--- getting javascripts ---//
	 var scriptAdd = "";
	 var addstr = "";
	 var scripts = contents.match(/<script[^>]*>[^<>]*<\/script>/ig);

	 contents = contents.replace(/<script[^>]*>[^<>]*<\/script>/ig,"");

	 //--- getting css ---//
	 var css = contents.match(/<link[^>]*>/ig);
	 if(css)
	 {
	  for(var c=0; c < css.length; c++)
	  {
	   var m = css[c].match(/href=(.+?\.css)/);
	   if(m && m[1])
	    _includeCSS(m[1].substr(1,m[1].length));
	  }
	 }
	 contents = contents.replace(/<link[^>]*>/ig,"");

	 if(!clearObj)
	 {
	  var tmpDiv = document.createElement('DIV');
	  tmpDiv.innerHTML = contents;
	  while(tmpDiv.childNodes.length)
	   destObj.appendChild(tmpDiv.childNodes[0]);
	 }
	 else
	  destObj.innerHTML = contents;

	 if(scripts)
	 {
	  for(var c=0; c < scripts.length; c++)
	  {
	   var m = scripts[c].match(/src=(.+?\.js)/);
	   if(m && m[1])
	    _includeJavaScript(m[1].substr(1,m[1].length));
	   else
	   {
	    var s = scripts[c].replace(/<script[^>]*>/,"");
	    s = s.replace(/<\/script>/,"");
	    scriptAdd+= s;
	   }
	  }
	 }

	 var script  = document.createElement('SCRIPT');
	 script.type = 'text/javascript';
	 script.innerHTML = scriptAdd;
	 document.getElementsByTagName('HEAD').item(0).appendChild(script);

     Layers[layName] = 1;
	 if(callbackFunction)
	  window.setTimeout(function(){callbackFunction(layName);},callbackTimer ? callbackTimer : 500);
	}
 layerHR.send(BASE_PATH+"var/layers/"+layName+"/index.php?basepath="+BASE_PATH+(arguments ? "&"+arguments : ""));
}
//-------------------------------------------------------------------------------------------------------------------//
function _includeJavaScript(file)
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
}
//-------------------------------------------------------------------------------------------------------------------//
function _includeCSS(file)
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
}
//-------------------------------------------------------------------------------------------------------------------//
function _getScreenWidth()
{
 if(window.innerWidth)
  return window.innerWidth;
 else if(document.all)
  return document.body.clientWidth;
 return 0;
}
//-------------------------------------------------------------------------------------------------------------------//
function _getScreenHeight()
{
 if(window.innerHeight)
  return window.innerHeight;
 else if(document.all)
  return document.body.clientHeight;
 return 0;
}
//-------------------------------------------------------------------------------------------------------------------//
function _showScreenMask()
{
 LAYER_SCREEN_MASK = document.createElement('DIV');
 LAYER_SCREEN_MASK.style.position = 'absolute';
 LAYER_SCREEN_MASK.style.left = '0px';
 LAYER_SCREEN_MASK.style.top = '0px';
 LAYER_SCREEN_MASK.style.width = _getScreenWidth()+'px';
 LAYER_SCREEN_MASK.style.height = _getScreenHeight()+'px';
 LAYER_SCREEN_MASK.style.filter="alpha(opacity=30)";
 LAYER_SCREEN_MASK.style.opacity=0.3;
 LAYER_SCREEN_MASK.style.backgroundColor = '#375fab';

 document.body.appendChild(LAYER_SCREEN_MASK);
}
//-------------------------------------------------------------------------------------------------------------------//
function _hideScreenMask()
{
 document.body.removeChild(LAYER_SCREEN_MASK);
 LAYER_SCREEN_MASK = null;
}
//-------------------------------------------------------------------------------------------------------------------//
function _getObjectPosition(obj)
{
 var ret = new Array;
 ret['x'] = obj.offsetLeft;
 ret['y'] = obj.offsetTop;
 while(obj = obj.offsetParent)
 {
  ret['x']+= obj.offsetLeft - obj.scrollLeft;
  ret['y']+= obj.offsetTop - obj.scrollTop;
 }
 ret['x']+= document.body.scrollLeft;
 ret['y']+= document.body.scrollTop;
 return ret;
}
//-------------------------------------------------------------------------------------------------------------------//

