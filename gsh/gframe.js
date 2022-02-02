/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-03-2013
 #PACKAGE: gframe
 #DESCRIPTION: Utility for shot messages, forms and widgets.
 #VERSION: 2.2beta
 #CHANGELOG: 18-03-2013 : Aggiunto extraParams
			 07-06-2012 : AutoResize function created.
			 01-06-2012 : Bug fix.
			 26-05-2012 : Bug fix in function gframe_hideScreenMask.
			 24-05-2012 : Bug fix with auto height with param --append-to. Now you can use -h 100% for full integration without iframe vertical scrollbar,
						  and bug fix in function gframe_showScreenMask.
			 04-05-2012 : Bug fix with fckeditor.
			 29-02-2012 : Bug fix with zIndex.
 #TODO:
 
*/
if(!window.top.ACTIVE_FRAMES)
 window.top.ACTIVE_FRAMES = new Array();
if(!window.top.SCREEN_MASKS)
 window.top.SCREEN_MASKS = 0;
//-------------------------------------------------------------------------------------------------------------------//
function shell_gframe(args, sessid, sh)
{
 var useCacheContents = false;
 var params = "";
 var addparams = "";
 var paramName = "";
 var paramValue = "";
 var contents = "";
 var extraParams = new Array();

 var rsURL = "";
 var rsLogin = "";
 var rsPasswd = "";
 var rsSessid = "";
 var rsShellid = "";

 for(var c=0; c < args.length; c++)
  switch(args[c])
  {
   case '-title' : case '-t' : { var title = args[c+1]; c++;} break;
   case '-contents' : case '-c' : { contents = args[c+1]; c++;} break;
   case '-f' : { var form = args[c+1]; c++;} break;
   case '-url' : { var url = args[c+1]; c++;} break;
   case '-width' : case '-w' : { var width = args[c+1]; c++;} break;
   case '-height' : case '-h' : { var height = args[c+1]; c++;} break;
   case '-params' : case '-p' : { params = args[c+1]; c++;} break;
   case '-pn' : case '--param-name' : { paramName = args[c+1]; c++;} break;
   case '-pv' : case '--param-value' : {
		 if(paramName)
		  addparams+= "&"+paramName+"="+encodeURI(args[c+1]);
		 c++;
		} break;
   case '--fullspace' : var fullspace = true; break;
   case '--fullscreen' : var fullscreen = true; break;
   case '--into-parent-window' : var intoParentWindow = true; break;
   case '--use-cache-contents' : useCacheContents=true; break;
   case '--append-to' : {var appendToObj=args[c+1]; c++; var fullspace=true; } break;
   case '-xpn' : case '--extra-param-name' : { extraParamName = args[c+1]; c++;} break;
   case '-xpv' : case '--extra-param-value' : {
		 if(extraParamName)
		  extraParams[extraParamName] = args[c+1];
		 c++;
		} break;
   // remote server options //
   case '-rsurl' : case '--remote-server-url' : { rsURL = args[c+1]; c++;} break;
   case '-rslogin' : case '--remote-server-login' : { rsLogin = args[c+1]; c++;} break;
   case '-rspasswd' : case '--remote-server-password' : { rsPasswd = args[c+1]; c++;} break;
   case '-rssessid' : case '--remote-server-sessid' : { rsSessid = args[c+1]; c++;} break;
   case '-rsshellid' : case '--remote-server-shellid' : { rsShellid = args[c+1]; c++;} break;
  }

 /* CHECK-IN */
 /* Check contents data */
 if(contents && (escape(contents).length > 2048))
  useCacheContents = true;

 /* Check params */
 if(params && (params.charAt(0) == "&"))
  params = params.substr(1);
 params+=addparams;

 title = title ? title : "";

 if(!url && !form)
  form = "default";

 /* EOF - CHECK-IN */

 var win = window.top;
 var doc = win.document;

 var scrW = gframe_getScreenWidth(win,doc)-20; // Valid for all browsers (Firefox, Chrome, Opera) //
 var scrH = gframe_getScreenHeight(win,doc)-20; // Valid for all browsers (Firefox, Chrome, Opera) //
 
 if(fullscreen)
 {
  width = scrW;
  height = scrH;
 }

 var iframe = doc.createElement('IFRAME');
 iframe.name = "gframe-"+window.top.ACTIVE_FRAMES.length;
 window.top.ACTIVE_FRAMES[iframe.name.replace('gframe-','')] = iframe;

 if(!fullscreen && !appendToObj)
 {
  gframe_showScreenMask(iframe);
 }

 iframe.style.width = width ? width : 100;
 iframe.style.height = height ? height : 100;
 if(!appendToObj)
  iframe.style.position = "absolute";
 if(width)
  iframe.style.left = doc.body.scrollLeft + Math.floor(scrW/2) - Math.floor(width/2);
 else
  iframe.style.left = 0;
 if(height)
  iframe.style.top = doc.body.scrollTop + Math.floor(scrH/2) - Math.floor(height/2);
 else
  iframe.style.top = 0;
 iframe.frameBorder='no';
 iframe.style.visibility = "hidden";
 if(appendToObj)
 {
  var parentObj = document.getElementById(appendToObj);
  if(!parentObj)
  {
   alert("Object "+appendToObj+" does not exists into document.");
   return sh.error("Cannot append iframe to object "+appendToObj+" because does not exists into document.","OBJECT_DOES_NOT_EXISTS_INTO_DOCUMENT");
  }
  iframe.style.left = 0;
  iframe.style.top = 0;
  iframe.style.width = parentObj.offsetWidth;
  iframe.style.height = "10";
  
  parentObj.appendChild(iframe);
 }
 else
 {
  iframe.style.zIndex = 11000;
  doc.body.appendChild(iframe);
 }

 iframe._getFrameObj = function(){
	 var oFrame = null;
	 for(var c=0; c < win.frames.length; c++)
	 {
	  if(win.frames[c].name == this.name)
	  {
	   oFrame = win.frames[c];
	   return oFrame;
	   break;
	  }
	 }
	 if(!oFrame)
	 {
	  for(var c=0; c < win.frames.length; c++)
	  {
	   var oFrame = gframe_recursiveFrameSearch(win.frames[c],this.name);
	   if(oFrame)
		return oFrame;
	  }
	 }
	}

 iframe._recursiveIframeSearch = function(frame, frameName){
	 
	}

 /* Load iframe */
 iframe.onload = function(){
	 var oFrame = this._getFrameObj();
	 if(!oFrame)
	  return alert("GFRAME Error: No frame found into document.");

	 if(!width)
	 {
	  /* Adjust width */
	  var frameW = oFrame.document.body.scrollWidth;
	  width = frameW ? frameW : 100;
	  if(!appendToObj)
	  {
	   this.style.left = doc.body.scrollLeft + Math.floor(scrW/2) - Math.floor(width/2);
	   this.style.width = width;
	  }
	  else
	  {
	   this.style.width = "100%";
	  }
	 }
	 if(!height)
	 {
	  var frameH = oFrame.document.body.scrollHeight;
	  height = frameH ? frameH : 100;
	  if(!appendToObj)
	  {
	   this.style.top = doc.body.scrollTop + Math.floor(scrH/2) - Math.floor(height/2);
	   this.style.height = height;
	  }
	  else
	  {
	   this.style.height = "100%";
	  }
	 }
	 else if(height == "100%")
	 {
	  var frameH = oFrame.document.body.scrollHeight;
	  this.style.height = frameH;
	 }

	 var script = oFrame.document.createElement('SCRIPT');
	 script.type = 'text/javascript';
	 var IH = "function gframe_close(msg,outarr){window.top.ACTIVE_FRAMES["+this.name.replace('gframe-','')+"].Close(msg,outarr);}\n";
	 IH+= "function gframe_shotmessage(msg,outArr,msgType,msgRef,mode){return window.top.ACTIVE_FRAMES["+this.name.replace('gframe-','')+"].ShotMessage(msg,outArr,msgType,msgRef,mode);}\n";
	 IH+= "function gframe_hide(){window.top.ACTIVE_FRAMES["+this.name.replace('gframe-','')+"].Hide();}\n";
	 IH+= "function gframe_show(){window.top.ACTIVE_FRAMES["+this.name.replace('gframe-','')+"].Show();}\n";
	 IH+= "function gframe_opacity(val){window.top.ACTIVE_FRAMES["+this.name.replace('gframe-','')+"].Opacity(val);}\n";
	 IH+= "function gframe_resize(w,h){window.top.ACTIVE_FRAMES["+this.name.replace('gframe-','')+"].Resize(w,h);}\n";
	 IH+= "function gframe_autoresize(){window.top.ACTIVE_FRAMES["+this.name.replace('gframe-','')+"].AutoResize();}\n";
	 IH+= "function gframe_move(x,y){window.top.ACTIVE_FRAMES["+this.name.replace('gframe-','')+"].Move(x,y);}\n";
	 IH+= "function gframe_fullscreen(){return window.top.ACTIVE_FRAMES["+this.name.replace('gframe-','')+"].FullScreen();}\n";
	 script.innerHTML = IH;
	 oFrame.document.getElementsByTagName('HEAD').item(0).appendChild(script);

	 if(oFrame.bodyOnLoad)
	  oFrame.bodyOnLoad(extraParams);

	 if(useCacheContents)
	 {
	  if(oFrame.gframe_cachecontentsload)
	   oFrame.gframe_cachecontentsload(contents);
	  else
	   alert("Gnujiko Warning (for the programmer): The contents exceded the maximum URL request (2048 byte). In order to get contents you must create a javascript function called gframe_cachecontentsload(cnts); The contents will be send automatically into cnts variable.");
	 }
	 
	 this.style.visibility = "visible";
	 sh.preOutput("Frame loaded",oFrame,"LOADED");
	}

 iframe.Close = function(msg,outArr){
	 var idx = parseInt(this.name.replace('gframe-',''));
	 if(window.top && window.top.ACTIVE_FRAMES)
	 {
	  if((idx+1) < window.top.ACTIVE_FRAMES.length)
	  {
	   while(window.top.ACTIVE_FRAMES.length > (idx+1))
	   {
	    window.top.ACTIVE_FRAMES[window.top.ACTIVE_FRAMES.length-1].Close();
	   }
	  } 
	  gframe_hideScreenMask(this);
	  window.top.ACTIVE_FRAMES.splice(idx,1);
	 }
	 sh.finish(msg ? msg : "done",outArr);
	 this.parentNode.removeChild(this);
	}

 iframe.ShotMessage = function(msg,outArr,msgType,msgRef,mode){
	 return sh.preOutput(msg,outArr,msgType,msgRef,mode);
	}

 iframe.Hide = function(){
	 this.style.visibility = "hidden";
	}
 iframe.Show = function(){
	 this.style.visibility = "visible";
	}

 iframe.Opacity = function(value){
	 this.style.opacity = value / 100;
	}

 iframe.Resize = function(w,h){
	 this.style.width = w;
	 this.style.height = h;
	 if(!appendToObj)
	 {
	  this.style.left = doc.body.scrollLeft + Math.floor(scrW/2) - Math.floor(w/2);
	  this.style.top = doc.body.scrollTop + Math.floor(scrH/2) - Math.floor(h/2);
	 }
	}

 iframe.AutoResize = function(){
	 if(appendToObj)
	 {
	  var oFrame = this._getFrameObj();
	  var frameH = oFrame.document.body.scrollHeight;
	  this.style.height = frameH;
	 }
	}

 iframe.Move = function(x,y){
	 this.style.left = x;
	 this.style.top = y;
	}

 iframe.FullScreen = function(){
	 this.style.left = 0;
	 this.style.top = 0;
	 this.style.width = scrW;
	 this.style.height = scrH;
	 return {width:scrW, height:scrH};
	}


 if(url)
  iframe.src = url;
 else if(rsURL)
 {
  if(rsSessid && rsShellid)
   iframe.src = ((rsURL.substr(rsURL.length-1) == "/") ? rsURL : rsURL+"/")+"share/widgets/"+form+".php?sessid="+rsSessid+"&shellid="+rsShellid+"&gframename="+iframe.name.replace('grame-','')+"&title="+title+(!useCacheContents ? "&contents="+escape(contents) : "&contents=loading...")+(width ? "&width="+width : "")+(height ? "&height="+height : "")+"&screenwidth="+scrW+"&screenheight="+scrH+(params ? "&"+params : "");
  else
  {
   var shtmp = new GShell();
   shtmp.OnError = function(msg,errcode){sh.error(msg,errcode);}
   shtmp.OnOutput = function(o,a){
	 if(!a) return sh.error("Unknown error!","UNKNOWN_ERROR");
	 rsSessid = a['sessid'];
	 rsShellid = a['shellid'];
	 iframe.src = ((rsURL.substr(rsURL.length-1) == "/") ? rsURL : rsURL+"/")+"share/widgets/"+form+".php?sessid="+rsSessid+"&shellid="+rsShellid+"&gframename="+iframe.name.replace('grame-','')+"&title="+title+(!useCacheContents ? "&contents="+escape(contents) : "&contents=loading...")+(width ? "&width="+width : "")+(height ? "&height="+height : "")+"&screenwidth="+scrW+"&screenheight="+scrH+(params ? "&"+params : "");
	}
   shtmp.sendCommand("rsh test -url `"+rsURL+"` -l `"+rsLogin+"` -p `"+rsPasswd+"`");
  }
 }
 else
  iframe.src = url ? url : ABSOLUTE_URL+"share/widgets/"+form+".php?sessid="+sessid+"&shellid="+sh.ShellID+"&gframename="+iframe.name.replace('grame-','')+"&title="+title+(!useCacheContents ? "&contents="+escape(contents) : "&contents=loading...")+(width ? "&width="+width : "")+(height ? "&height="+height : "")+"&screenwidth="+scrW+"&screenheight="+scrH+(params ? "&"+params : "");

}
//-------------------------------------------------------------------------------------------------------------------//
function gframe_getScreenWidth(win,doc)
{
 if(win.innerWidth)
  return win.innerWidth;
 else if(doc.all)
  return doc.body.clientWidth;
}
//-------------------------------------------------------------------------------------------------------------------//
function gframe_getScreenHeight(win,doc)
{
 if(win.innerHeight)
  return win.innerHeight;
 else if(doc.all)
  return doc.body.clientHeight;
}
//-------------------------------------------------------------------------------------------------------------------//
function gframe_showScreenMask(iframe)
{
 if(window.top.mask)
 {
  window.top.SCREEN_MASKS++;
  return;
 }

 var mask = window.top.document.createElement('DIV');
 mask.style.position = 'absolute';
 mask.style.left = 0;
 mask.style.top = 0;
 mask.style.width = window.top.document.body.scrollWidth; //gframe_getScreenWidth(window.top, window.top.document);
 mask.style.height = window.top.document.body.scrollHeight; //gframe_getScreenHeight(window.top, window.top.document);
 mask.style.filter="alpha(opacity=30)";
 mask.style.opacity=0.3;
 mask.style.backgroundColor = '#375fab';
 mask.style.zIndex=10000;

 window.top.document.body.appendChild(mask);

 gframe_disable_scroll(window.top)

 window.top.SCREEN_MASKS++;
 window.top.mask = mask;
}
//-------------------------------------------------------------------------------------------------------------------//
function gframe_hideScreenMask(iframe)
{
 if(window.top.SCREEN_MASKS > 1)
 {
  window.top.SCREEN_MASKS--;
  return;
 }

 if(window.top.mask && window.top.mask.parentNode)
  window.top.mask.parentNode.removeChild(window.top.mask);
 gframe_enable_scroll(window.top);
 window.top.SCREEN_MASKS = 0;
 window.top.mask = null;
}
//-------------------------------------------------------------------------------------------------------------------//
function gframe_recursiveFrameSearch(iframe, _name)
{
 if(iframe.name == _name)
  return iframe;

 for(var c=0; c < iframe.frames.length; c++)
 {
  var oFrame = gframe_recursiveFrameSearch(iframe.frames[c],_name);
  if(oFrame)
   return oFrame;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function gframe_preventDefault(e) 
{
 e = e || window.event;
 if (e.preventDefault)
  e.preventDefault();
 e.returnValue = false;  
}

function gframe_wheel(e) {gframe_preventDefault(e);}

function gframe_disable_scroll(win)
{
 if(win.scrollLock)
  return;

 if(win.addEventListener)
  win.addEventListener('DOMMouseScroll', gframe_wheel, false);
 win.onmousewheel = win.document.onmousewheel = gframe_wheel;
 win.scrollLock=true;
}

function gframe_enable_scroll(win) 
{
 if(win.removeEventListener)
  win.removeEventListener('DOMMouseScroll', gframe_wheel, false);
 win.onmousewheel = win.document.onmousewheel = null;
 win.scrollLock=false;
}

