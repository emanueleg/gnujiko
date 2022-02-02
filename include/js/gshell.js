/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 22-02-2013
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Gnujiko official shell
 #VERSION: 2.2beta
 #CHANGELOG: 22-02-2013 : Buf fix on errors
 #TODO:
 
*/

if(!SHELLS)
 var SHELLS = new Array();

function GShell()
{
 SHELLS.push(this);

 var d = new Date();
 this.ShellID = d.getTime();

 //--- PROPERTIES //
 this.SessionID = 0;
 if(typeof(SESSION_ID) == "string")
  this.SessionID = SESSION_ID;
 this.LastSessionID = 0;

 	/* DETECT SESSID FROM URL */
 	var hash = null;
 	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
 	for(var i = 0; i < hashes.length; i++){hash = hashes[i].split('=');if(hash[0] == "sessid"){this.SessionID = hash[1];break;}}
 	/* EOF DETECT SESSID FROM URL */

 this.Username = "";
 this.Commands = new Array();
 this.__cmdIndex = 0;
 this.debugmode = false;

 //--- EVENTS ---//
 this.OnInput = null;
 this.OnPreOutput = null;
 this.OnOutput = null;
 this.OnPreError = null;
 this.OnError = null;
 this.OnNewSession = null;
 this.OnSessionChange = null;
 this.OnFinish = null;

 //--- PRIVATE ---//
 this.preoutT = null;
 this.iFace = null;
 var oThis = this;

 this.hHR = new GHTTPRequest();
 this.hHR.OnHttpRequest = function(reqObj){
	 if(!reqObj || !reqObj.responseXML)
	 {
	  if(oThis.preoutT)
	   clearInterval(oThis.preoutT);
	  oThis.error("Shell request problem","SHELL_REQUEST_FAILED");
	  return alert("Shell request problem!");
	 }
	 var response = reqObj.responseXML.documentElement;
	 if(!response.getElementsByTagName('request').length)
	 {
	  oThis.error("Shell request problem","SHELL_REQUEST_FAILED");
	  return alert("Shell request problem!");
	 }
	 var requestType = response.getElementsByTagName('request')[0].getAttribute('type');

	 switch(requestType)
	 {
	  case 'test' : alert(response.getElementsByTagName('request')[0].getAttribute('message')); break;
	  case 'newsession' : {
		 var req = response.getElementsByTagName('request')[0];
		 var success = (req.getAttribute('result') == "true") ? true : false;
		 if(!success)
		  alert("Unable to create new shell session!");
		 else
		 {
		  oThis.lastSudoSessionID = 0;
		  oThis.SessionID = req.getAttribute('sessid');
		  oThis.Username = req.getAttribute('uname');
		  oThis.Hostname = req.getAttribute('hostname');
		  if(oThis.OnNewSession)
		   oThis.OnNewSession(oThis.Username,oThis.Hostname,oThis.SessionID);
		 }
		} break;
	  case 'closesession' : {
		 var req = response.getElementsByTagName('request')[0];
		 var success = (req.getAttribute('result') == "true") ? true : false;
		 if(!success)
		  alert("Unable to close shell session!");
		 else
		 {
		  oThis.SessionID = req.getAttribute('sessid');
		  oThis.Username = req.getAttribute('uname');
		  oThis.Hostname = req.getAttribute('hostname');
		  if(oThis.OnSessionClose)
		   oThis.OnSessionClose(oThis.Username,oThis.Hostname,oThis.SessionID);
		 }
		} break;
	  case 'login' : {
		 var req = response.getElementsByTagName('request')[0];
		 var success = (req.getAttribute('result') == "true") ? true : false;
		 if(!success)
		 {
		  oThis.print(req.getAttribute('message'));
		 }
		 else
		 {
		  oThis.lastSudoSessionID = 0;
		  oThis.LastSessionID = oThis.SessionID;
		  oThis.SessionID = req.getAttribute('sessid');
		  oThis.Username = req.getAttribute('uname');
		  oThis.Hostname = req.getAttribute('hostname');
		  oThis.print("Welcome "+oThis.Username);
		  if(oThis.OnNewSession)
		   oThis.OnNewSession(oThis.Username,oThis.Hostname,oThis.SessionID);
		 }
		} break;
	  case 'includejssh' : {
		 var req = response.getElementsByTagName('rootsession')[0];
		 if(req)
		  oThis.lastSudoSessionID = req.getAttribute('sessid');

		 var req = response.getElementsByTagName('request')[0];
		 var file = req.getAttribute('file');
		 var comm = req.getAttribute('command');
		 var sessid = req.getAttribute('sessid');
		 var retsessid = req.getAttribute('retsessid');
		 var retuname = req.getAttribute('retuname');

		 if(sessid != oThis.SessionID) // session was change //
		 {
		  oThis.SessionID = sessid;
		  oThis.LastSessionID = oThis.SessionID;
		  oThis.Username = req.getAttribute('uname');
		 }

		 var arguments = response.getElementsByTagName('arguments')[0];
		 if(arguments)
		 {
		  var items = arguments.getElementsByTagName('item'); 
		  var args = xml_to_array(arguments);
		 }
		 else
		  var args = new Array();
		 // include js file //
		 var ls = document.getElementsByTagName('SCRIPT');
		 for(var c=0; c < ls.length; c++)
		 {
		  if(ls[c].src == (BASE_PATH+file))
		   return true;
		 }

		 var headID = document.getElementsByTagName("HEAD")[0];
		 var newScript = document.createElement('SCRIPT');
		 newScript.type = 'text/javascript';
		 document.cacheArguments = args;
		 newScript.onload = function(){
			 callFunction("shell_"+comm+"(document.cacheArguments,'"+sessid+"',SHELLS["+SHELLS.indexOf(oThis)+"]);");
			 // restore old sessid //
			 sessid = retsessid;
			 if(sessid != oThis.SessionID) // session was change //
		 	 {
		  	  oThis.SessionID = sessid;
		  	  oThis.LastSessionID = oThis.SessionID;
		  	  oThis.Username = retuname;
		 	 }
			}
		 newScript.src = BASE_PATH+file;
		 headID.appendChild(newScript);
		} break;
	  case 'command' : {
		 var req = response.getElementsByTagName('rootsession')[0];
		 if(req)
		  oThis.lastSudoSessionID = req.getAttribute('sessid');
		 var req = response.getElementsByTagName('request')[0];
		 var msg = req.getAttribute('message');
		 msg = msg.replace(/&amp;/g,"&");
		 msg = msg.replace(/&#10;/g,"\n");
		 msg = html_entity_decode(msg);

		 var htmlMsg = req.getAttribute('htmloutput');
		 if(htmlMsg)
		 {
		  htmlMsg = htmlMsg.replace(/&amp;/g,"&");
		  htmlMsg = htmlMsg.replace(/&#10;/g,"\n");
		  htmlMsg = html_entity_decode(htmlMsg);
		 }
		 var sessid = req.getAttribute('sessid');
		 if(sessid != oThis.SessionID) // session was change //
		 {
		  if(!sessid || (sessid == "0"))
		   oThis._showSEF();
		  else
		  {
		   oThis.SessionID = req.getAttribute('sessid');
		   oThis.LastSessionID = oThis.SessionID;
		   oThis.Username = req.getAttribute('uname');
		   if(oThis.OnSessionChange)
		    oThis.OnSessionChange(oThis.Username,oThis.Hostname,oThis.SessionID);
		  }
		 }
		 var success = (req.getAttribute('result') == "true") ? true : false;
		 var xmlArr = response.getElementsByTagName('output_array')[0];
		 var xmlRedArr = response.getElementsByTagName('redirected_outarr').length ? response.getElementsByTagName('redirected_outarr')[0] : null;
		 var outputArr = null;
		 var redirectedOutarr = null;
		 if(xmlArr) //--- re-construct array ---//
		  var outputArr = xml_to_array(xmlArr);
		 if(xmlRedArr) //--- re-construct redirected array ---//
		  var redirectedOutarr = xml_to_array(xmlRedArr);
		 if(success)
		  oThis.finish(msg,outputArr,redirectedOutarr,htmlMsg);
		 else
		 {
		  if(oThis.preoutT)
		   clearInterval(oThis.preoutT);
		  oThis.error(msg,req.getAttribute('error'));
		 }
		 
		} break;
	  case 'sudo' : {
		 var req = response.getElementsByTagName('request')[0];
		 if(req.getAttribute('result') == "waitforpasswd")
		 {
		  oThis.input("Type root password",function(pwd){
			 oThis.hHR.send(BASE_PATH+"./gshell.php?request=sudo&sessid="+oThis.SessionID+"&shellid="+oThis.ShellID+"&passwd="+pwd+"&command="+oThis.lastSudoCmd);
			},'PASSWORD',"SUDO");
		 }
		 else if(req.getAttribute('result') == "false")
		 {
		  if(oThis.preoutT)
		   clearInterval(oThis.preoutT);
		  if(oThis.OnError)
		   oThis.OnError(req.getAttribute('msg'),req.getAttribute('error'));
		  else
		   alert(req.getAttribute('msg'));
		  oThis.__inProgress = false;
		 }
		} break;
	 }
	}
}

GShell.prototype.sendCommand = function(cmd)
{
 var oThis = this;
 // purge special chars //
 cmd = cmd.replace(/&/g,"%26");
 cmd = cmd.replace(/\+/g,"%2B");

 this.Commands.push(cmd);
 if(this.__inProgress)
  return;
 this.__inProgress = true;
 this.hHR.send(BASE_PATH+"./gshell.php?request=command&sessid="+this.SessionID+"&shellid="+this.ShellID+"&command="+cmd);
 this._cpomT = 500;
 if(this.OnPreOutput) // enable pre-output messages //
  this.preoutT = setInterval(function(){oThis.checkPreOutMessages();},500);
}

GShell.prototype.sendSudoCommand = function(cmd)
{
 var oThis = this;
 // purge special chars //
 cmd = cmd.replace(/&/g,"%26");
 cmd = cmd.replace(/\+/g,"%2B");

 this.__inProgress = true;

 this.hHR.send(BASE_PATH+"./gshell.php?request=sudo&sessid="+this.SessionID+(this.lastSudoSessionID ? "&lastsudosessid="+this.lastSudoSessionID : "")+"&shellid="+this.ShellID+"&command="+cmd);
 if(this.OnPreOutput) // enable pre-output messages //
  this.preoutT = setInterval(function(){oThis.checkPreOutMessages();},500);
 this.lastSudoCmd = cmd;
}

GShell.prototype.login = function(username,password)
{
 this.hHR.send(BASE_PATH+"./gshell.php?request=login&sessid="+this.SessionID+"&username="+username+"&password="+password+"&shellid="+this.ShellID);
}

GShell.prototype.print = function(msg)
{
 if(this.OnOutput)
  this.OnOutput(msg);
}

GShell.prototype.preOutput = function(msg ,outArr, msgType, msgRef, mode)
{
 var oThis = this;
 if(mode == "PASSTHRU")
 {
  /* PASS PRE-OUTPUT TO INTERFACE */
  if(!this.iFace)
  {
   this.iFace = new GSHInterface(outArr['name']);
   this.iFace.OnLoad = function(){
	 
	 this.update(msg, outArr['outarr'] ? outArr['outarr'] : outArr, msgType, msgRef);
	}
   this.iFace.OnFree = function(){
	 oThis.iFace = null;
	}
   this.iFace.load();
  }
 }
 else if(this.iFace)
  this.iFace.update(msg, outArr, msgType, msgRef);
 else if(this.OnPreOutput)
  return this.OnPreOutput(msg ? msg : "",outArr, msgType, msgRef, mode);
}

GShell.prototype.preError = function(msg ,err, msgType, msgRef, mode)
{
 if(this.OnPreError)
  return this.OnPreError(msg, err, msgType, msgRef, mode);
}

GShell.prototype.output = function(msg, outarr, redirectedOutarr, htmlOutput)
{
 if(this.OnOutput)
  this.OnOutput(msg,outarr,redirectedOutarr,htmlOutput);
}

GShell.prototype.error = function(msg, error)
{
 this.__inProgress = false;
 if(this.iFace) 
  this.iFace.free();
 this.Commands = new Array();
 this.__cmdIndex=0;

 if(this.OnError)
  this.OnError(msg, error);
}

GShell.prototype.input = function(msg,callback,type,extra)
{
 if(this.OnInput)
  this.OnInput(msg,callback,type);
 else if(extra == "SUDO")
  this._showRPF(callback);
 else
 {
  switch(type)
  {
   default : {
	 var p = prompt(msg,"");
	 if(callback)
	  callback(p);
	}
  }
 }
}

GShell.prototype.finish = function(msg, outarr, redirectedOutarr, htmlOutput)
{
 var oThis = this;

 this.__cmdIndex++;
 if(this.Commands.length > this.__cmdIndex)
 {
  if(this.OnOutput)
   this.OnOutput(msg, outarr, redirectedOutarr, htmlOutput);

  this.hHR.send(BASE_PATH+"./gshell.php?request=command&sessid="+this.SessionID+"&shellid="+this.ShellID+"&command="+this.Commands[this.__cmdIndex]);
 }
 else
 {
  this.__inProgress = false;
  if(this.preoutT)
  {
   // retrieve last messages and exit//
   this.checkPreOutMessages(true,function(){
	 if(oThis.OnFinish)
	 {
	  if(oThis.iFace) oThis.iFace.free();
	  oThis.OnFinish(msg,outarr,redirectedOutarr,htmlOutput);
	 }
	 else if(oThis.OnOutput)
	 {
	  if(oThis.iFace) oThis.iFace.free();
	  oThis.OnOutput(msg,outarr,redirectedOutarr,htmlOutput);
	 }
	});
   return;
  }
  if(this.OnFinish)
  {
   if(oThis.iFace) oThis.iFace.free();
   this.OnFinish(msg,outarr,redirectedOutarr,htmlOutput);
  }
  else if(this.OnOutput)
  {
   if(oThis.iFace) oThis.iFace.free();
   this.OnOutput(msg,outarr,redirectedOutarr,htmlOutput);
  }
 }
}

GShell.prototype.NewSession = function()
{
 this.hHR.send(BASE_PATH+"./gshell.php?request=newsession&sessid="+this.SessionID+"&shellid="+this.ShellID);
}

GShell.prototype.CloseSession = function()
{
 this.hHR.send(BASE_PATH+"./gshell.php?request=closesession&sessid="+this.SessionID+"&lastsessid="+this.LastSessionID+"&shellid="+this.ShellID);
}

GShell.prototype.checkPreOutMessages = function(lastmessage, callback)
{
 if(!this.__inProgress && !lastmessage)
 {
  if(this.preoutT)
   clearInterval(this.preoutT)
  return;
 }

 var oThis = this;
 var msgHR = new GHTTPRequest();
 msgHR.OnHttpRequest = function(reqObj){
	 if(!reqObj || !reqObj.responseXML)
	  return alert("Shell pre-out message request problem!");
	 var response = reqObj.responseXML.documentElement;
	 var requestType = response.getElementsByTagName('request')[0].getAttribute('type');
	 switch(requestType)
	 {
	  case 'checkpreoutmessages' : {
		 var req = response.getElementsByTagName('request')[0];
		 var success = (req.getAttribute('result') == "true") ? true : false;
		 var err = req.getAttribute('error');
		 if(err == "FILE_DOES_NOT_EXISTS")
		 {
		  if(lastmessage && oThis.preoutT)
		   clearInterval(oThis.preoutT);
		  if(callback)
		   callback();
		  if(!lastmessage)
		   oThis._reduceTimer(); // increase timer delay //
		  return;
		 }
		 var outputs = response.getElementsByTagName('output');
		 if(outputs)
		 {
		  for(var c=0; c < outputs.length; c++)
		  {
		   var msg = "";
		   var msgH = outputs[c].getElementsByTagName('message');
		   var mode = null;
		   var msgType = "";
		   var msgRef = "";
		   if(msgH && msgH[0])
		   {
			msg = msgH[0].getAttribute('text');
			mode = msgH[0].getAttribute('mode');
			msgType = msgH[0].getAttribute('type');
			msgRef = msgH[0].getAttribute('ref');
		   }
		   var outputArr = null;
		   if(outputs[c].getElementsByTagName('output_array')[0])
		    outputArr = xml_to_array(outputs[c].getElementsByTagName('output_array')[0]);
		   if(success)
			oThis.preOutput(msg ? msg : "",outputArr, msgType, msgRef, mode);
		   else
		   {
		    if(oThis.preoutT)
		     clearInterval(oThis.preoutT);
			oThis.preError(msg ? msg : "",req.getAttribute('error'), msgType, msgRef, mode);
		   }
		  }
		  if(!lastmessage)
		   oThis._restoreTimer(); // restore timer to 500ms
		 }
		 if(lastmessage && oThis.preoutT)
		  clearInterval(oThis.preoutT);
		 if(callback)
		  callback();
		} break;
	 }
	}
 msgHR.send(BASE_PATH+"./gshell.php?request=checkpreoutmessages&sessid="+this.SessionID+"&shellid="+this.ShellID+(lastmessage ? "&remove=1" : ""));
}

GShell.prototype._restoreSession = function(passwd)
{
 var oThis = this;
 var rsHR = new GHTTPRequest();
 rsHR.OnHttpRequest = function(reqObj){
	 if(!reqObj || !reqObj.responseXML)
	  return alert("Shell pre-out message request problem!");
	 var response = reqObj.responseXML.documentElement;
	 var req = response.getElementsByTagName('request')[0];
	 var type = req.getAttribute('type');
	 var result = req.getAttribute('result');
	 var msg = req.getAttribute('message');
	 if(result != "true")
	 {
	  alert(msg);
	  oThis._showSEF();
	 }
	}
 rsHR.send(BASE_PATH+"./gshell.php?request=restoresession&sessid="+this.SessionID+"&shellid="+this.ShellID+"&user="+this.Username+"&passwd="+passwd);
}

GShell.prototype.secureString = function(str)
{
 var s = str;
 if(!s.replace)
  return s;
 s = s.replace(/\n/g, "<br/>");
 s = s.replace(/\r\n/g, "<br/>");
 return s;
}

GShell.prototype._reduceTimer = function()
{
 var oThis = this;
 if(this.preoutT)
  clearInterval(this.preoutT);
 if(this._cpomT < 10000)
  this._cpomT+=100;
 this.preoutT = setInterval(function(){oThis.checkPreOutMessages();},this._cpomT);
}

GShell.prototype._restoreTimer = function()
{
 var oThis = this;
 if(!this.preoutT)
  return;
 clearInterval(this.preoutT);
 this._cpomT = 500;
 this.preoutT = setInterval(function(){oThis.checkPreOutMessages();},this._cpomT);
}

GShell.prototype._showRPF = function(callback)
{
 /* Access as root message */
 var oThis = this;
 if(!this.rpf)
 {
  this.rpf = document.createElement('DIV');
  this.rpf.style.width = "360px";
  this.rpf.style.height = "180px";
  this.rpf.style.backgroundImage = "url("+BASE_PATH+"share/images/rootpwdform.png)";
  this.rpf.style.backgroundRepeat = "no-repeat";
  this.rpf.style.padding = "5px 5px 5px 70px";
  this.rpf.style.fontFamily = "Arial";
  this.rpf.style.position = "absolute";
  this.rpf.innerHTML = "<div style='width:280px'><span style='font-size:18px;'><b>"+GSHLANG_ACCESS_AS_SU+"</b></span><br/><br/><span style='font-size:12px'>"+GSHLANG_ENTER_PWD+"</span><br/><br/><b>"+GSHLANG_PWD+":</b> <input type='password' style='width:190px'/><br/><br/><div align='right'><input type='button' value='"+GSHLANG_CANC+"'/> <input type='button' value='"+GSHLANG_OK+"'/></div></div>";
  this.rpf.submit = function(){
	 this.callbackFunc(this.getElementsByTagName('INPUT')[0].value);
	 document.body.removeChild(this);
	}
 }
 this.rpf.callbackFunc = callback;

 var _sw = window.innerWidth ? window.innerWidth : document.body.clientWidth;
 var _sh = window.innerHeight ? window.innerHeight : document.body.clientHeight;
 this.rpf.style.top = (Math.floor(_sh/2) - 90)+"px";
 this.rpf.style.left = (Math.floor(_sw/2) - 180)+"px";
 document.body.appendChild(this.rpf);

 this.rpf.getElementsByTagName('INPUT')[0].value = "";
 this.rpf.getElementsByTagName('INPUT')[0].focus();
 this.rpf.getElementsByTagName('INPUT')[0].onkeyup = function(ev){
	 var keynum = window.event ? ev.keyCode : ev.which;
 	 if(keynum == 13) 
	  oThis.rpf.submit();
	}
 this.rpf.getElementsByTagName('INPUT')[1].onclick = function(){document.body.removeChild(oThis.rpf);}
 this.rpf.getElementsByTagName('INPUT')[2].onclick = function(){oThis.rpf.submit();}
}
//-------------------------------------------------------------------------------------------------------------------//
GShell.prototype._showSEF = function()
{
 /* Session expired message */
 var oThis = this;
 if(!this.sef)
 {
  this.sef = document.createElement('DIV');
  this.sef.style.width = "360px";
  this.sef.style.height = "180px";
  this.sef.style.backgroundImage = "url("+BASE_PATH+"share/images/sessionexpiredform.png)";
  this.sef.style.backgroundRepeat = "no-repeat";
  this.sef.style.padding = "5px 5px 5px 70px";
  this.sef.style.fontFamily = "Arial";
  this.sef.style.position = "absolute";
  this.sef.innerHTML = "<div style='width:280px'><span style='font-size:18px;'><b style='color:#f31903;'>Sessione scaduta</b></span><br/><br/><span style='font-size:12px'>Dopo un po di tempo (solitamente 30 minuti) di inattivit&agrave; la sessione scade, ed &egrave; quindi necessario rieffettuare il login. Inserisci la tua password per riattivare la sessione.</span><br/><br/><b>Password:</b> <input type='password' style='width:190px'/><br/><br/><div align='right'><input type='button' value='Annulla'/> <input type='button' value='OK'/></div></div>";
  this.sef.submit = function(){
	 this.callbackFunc(this.getElementsByTagName('INPUT')[0].value);
	 document.body.removeChild(this);
	}
 }

 this.sef.callbackFunc = function(passwd){
	 oThis._restoreSession(passwd);
	};

 var _sw = window.innerWidth ? window.innerWidth : document.body.clientWidth;
 var _sh = window.innerHeight ? window.innerHeight : document.body.clientHeight;
 this.sef.style.top = (Math.floor(_sh/2) - 90)+"px";
 this.sef.style.left = (Math.floor(_sw/2) - 180)+"px";
 document.body.appendChild(this.sef);

 this.sef.getElementsByTagName('INPUT')[0].focus();
 this.sef.getElementsByTagName('INPUT')[0].onkeyup = function(ev){
	 var keynum = window.event ? ev.keyCode : ev.which;
 	 if(keynum == 13) 
	  oThis.sef.submit();
	}
 this.sef.getElementsByTagName('INPUT')[1].onclick = function(){document.body.removeChild(oThis.sef);}
 this.sef.getElementsByTagName('INPUT')[2].onclick = function(){oThis.sef.submit();}
}
//-------------------------------------------------------------------------------------------------------------------//
function xml_to_array(xml,parent)
{
 var ret = new Array;
 if(xml.attributes && (xml.attributes.length > 0))
 {
  for(var c=0; c < xml.attributes.length; c++)
   ret[xml.attributes[c].name] = html_entity_decode(xml.attributes[c].value.replace("&amp;","&"));
 }
 if(xml.childNodes.length > 0)
 {
  if((xml.childNodes.length==1) && (xml.childNodes[0].nodeName != "item"))
   ret[xml.childNodes[0].nodeName] = xml_to_array(xml.childNodes[0],ret);
  else
  {
   var useNum = false;
   if((xml.childNodes.length==1) && (xml.childNodes[0].nodeName=="item"))
    useNum = true;
   else if((xml.childNodes.length > 1) && (xml.childNodes[0].nodeName == xml.childNodes[1].nodeName))
    useNum = true;
   for(var c=0; c < xml.childNodes.length; c++)
   {
    if(useNum == true)
    {
     var cn = xml.childNodes[c];
	 if(!cn.childNodes.length)
	  ret[c] = xml_to_array(xml.childNodes[c]);
	 else if((cn.childNodes.length > 0) && (cn.childNodes[0].nodeType != 3) && (cn.childNodes[0].nodeType != 4))
	  ret[c] = xml_to_array(xml.childNodes[c]);
	 else if(cn.childNodes.length)
	 {
	  var nodeVal = "";
	  for(var i=0; i < cn.childNodes.length; i++)
	  {
	   if((cn.childNodes[i].nodeType == 3) || (cn.childNodes[i].nodeType == 4))
		nodeVal+= cn.childNodes[i].nodeValue;
	  }
	  ret[c] = nodeVal;
	 }
    }
    else
     ret[xml.childNodes[c].nodeName] = xml_to_array(xml.childNodes[c],ret);
   }
  }
 }
 if(parent)
  parent.push(ret);
 return ret;
}
//-------------------------------------------------------------------------------------------------------------------//
function callFunction(func)
{
 if(navigator.appName.indexOf("Explorer") != -1)
  window.execScript(func,"JavaScript");
 else
 {
  var script  = document.createElement('SCRIPT');
  script.type = 'text/javascript';
  script.innerHTML = func;
  document.getElementsByTagName('HEAD').item(0).appendChild(script);
 }
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function GSHInterface(iName, sessid, shellid)
{
  /* PRIVATE */
 this._destObj = null;
 this._csslist = new Array();
 this._jslist = new Array();
 this.loaded = false;
 this.enabled = true;

 /* PUBLIC */
 this.Name = iName;
 this.SessionID = sessid ? sessid : null;
 this.ShellID = shellid ? shellid : null;

 this.Arguments = "";

 /* EVENTS */
 this.OnLoad = null;
 this.OnError = null;
}
//-------------------------------------------------------------------------------------------------------------------//
GSHInterface.prototype.load = function(arguments, destObj)
{
 var oThis = this;
 this.Arguments = arguments;
 this._destObj = destObj;

 /* Create handler */
 var iFaceH = document.createElement('INPUT');
 iFaceH.type = "hidden";
 iFaceH.name = "gnujiko-interface-handler";
 iFaceH.value = "";
 iFaceH.id = "ifaceh-"+oThis.ShellID;
 this.iFaceH = iFaceH;
 document.body.appendChild(iFaceH);


 /* Load interface */
 var HR = new GHTTPRequest();
 HR.OnHttpRequest = function(reqObj){
	 if(!oThis.enabled) return;
	 if(!reqObj || !reqObj.responseText)
	 {
	  if(oThis.OnError)
	   return oThis.OnError("Unable to load iterface "+oThis.Name,"INTERFACE_LOAD_PROBLEM");
	  else
	   return alert("Unable to load interface "+oThis.Name);
	 }
	 
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

	 if(!destObj) /* Create interface container */
	 {
	  var tb = document.createElement('TABLE');
	  tb.cellSpacing=0; tb.cellPadding=0; tb.border=0;
	  destObj = tb.insertRow(-1).insertCell(-1);
	  destObj.innerHTML = "&nbsp;";
	  destObj.masterTB = tb;
	  destObj.style.visibility="hidden";
	  destObj.style.position="absolute";
	  destObj.style.left = 1;
	  destObj.style.top = 1;
	  document.body.appendChild(tb);
	  oThis._destObj = destObj;
	 }
	 destObj.innerHTML = contents;
	 destObj.style.left = Math.floor(oThis.getScreenWidth()/2) - Math.floor(destObj.offsetWidth/2);
	 destObj.style.top = Math.floor(oThis.getScreenHeight()/2) - Math.floor(destObj.offsetHeight/2);
	 destObj.style.visibility = "";

	 if(scripts)
	 {
	  for(var c=0; c < scripts.length; c++)
	  {
	   var m = scripts[c].match(/src=(.+?\.js)/);
	   if(m && m[1])
	    oThis._includeJS(m[1].substr(1,m[1].length),true);
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

	 //if(oThis.OnLoad)
	 // window.setTimeout(function(){oThis.OnLoad();},500);
	 if(oThis.OnLoad)
	  oThis.OnLoad();
	}
 HR.send(BASE_PATH+"etc/dynarc/interfaces/"+this.Name+"/index.php?sessid="+this.SessionID+"&shellid="+this.ShellID+(arguments ? "&"+arguments : ""));
}
//-------------------------------------------------------------------------------------------------------------------//
GSHInterface.prototype.getScreenWidth = function()
{
 if(window.innerWidth)
  return window.innerWidth;
 else if(document.all)
  return document.body.clientWidth;
 return 0;
}
//-------------------------------------------------------------------------------------------------------------------//
GSHInterface.prototype.getScreenHeight = function()
{
 if(window.innerHeight)
  return window.innerHeight;
 else if(document.all)
  return document.body.clientHeight;
 return 0;
}
//-------------------------------------------------------------------------------------------------------------------//
GSHInterface.prototype.update = function(msg, outArr, msgType, msgRef)
{
 if(!this.enabled) return;
 if(this.iFaceH)
 {
  if(this.iFaceH.OnUpdate)
  {
   if(this.iFaceH.missedMessages && this.iFaceH.missedMessages.length)
   {
	for(var c=0; c < this.iFaceH.missedMessages.length; c++)
	 this.iFaceH.OnUpdate(this.iFaceH.missedMessages[c].msg, this.iFaceH.missedMessages[c].outArr, this.iFaceH.missedMessages[c].msgType, this.iFaceH.missedMessages[c].msgRef);
	this.iFaceH.missedMessages = null;
	this.iFaceH.OnUpdate(msg, outArr, msgType, msgRef);
   }
   else
    this.iFaceH.OnUpdate(msg, outArr, msgType, msgRef);
  }
  else
  {
   if(!this.iFaceH.missedMessages)
	this.iFaceH.missedMessages = new Array();
   var a = new Object();
   a.msg = msg;
   a.outArr = outArr;
   a.msgType = msgType;
   a.msgRef = msgRef;
   this.iFaceH.missedMessages.push(a);
  }
 }
}
//-------------------------------------------------------------------------------------------------------------------//
GSHInterface.prototype.free = function()
{
 this.enabled = false;
 if(this._destObj)
 {
  if(this._destObj.masterTB && this._destObj.masterTB.parentNode)
   this._destObj.masterTB.parentNode.removeChild(this._destObj.masterTB);
  else if(this._destObj.parentNode)
   this._destObj.parentNode.removeChild(this._destObj);
 }
 if(this.iFaceH && this.iFaceH.parentNode)
  this.iFaceH.parentNode.removeChild(this.iFaceH);
 if(this.OnFree)
  this.OnFree();
}
//-------------------------------------------------------------------------------------------------------------------//
GSHInterface.prototype._includeCSS = function(file)
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

GSHInterface.prototype._includeJS = function(file,reload)
{
 var ls = document.getElementsByTagName('SCRIPT');
 for(var c=0; c < ls.length; c++)
 {
  if(ls[c].src == file)
  {
   if(reload)
   {
    if(ls[c].parentNode)
	 ls[c].parentNode.removeChild(ls[c]);
   }
   else
    return true;
  }
 }

 var script  = document.createElement('SCRIPT');
 script.src  = file;
 script.type = 'text/javascript';
 document.getElementsByTagName('HEAD').item(0).appendChild(script);
 this._jslist.push(script);
}
