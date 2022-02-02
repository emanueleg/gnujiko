
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 25-05-2013
 #PACKAGE: gterminal
 #DESCRIPTION: Official Gnujiko terminal shell.
 #VERSION: 2.1beta
 #CHANGELOG: 25-05-2013 : Completato gli allacciamenti con gli eventi
 #TODO:
 
*/
//-------------------------------------------------------------------------------------------------------------------//
Function.prototype.bindEvent = function(object)
{
 var method = this;
 return function(evt) {
	 var e = evt ? evt : window.event;
	 method.call(object, e);
	 e.cancelBubble = true;
	 if(e.stopPropagation)
	  e.stopPropagation();
	}
}
//-------------------------------------------------------------------------------------------------------------------//
function GTerminal(_obj)
{
 this.O = _obj ? _obj : document.createElement("DIV");
 if(!_obj)
  this.O.className = "console";

 this.lineinput = document.createElement('INPUT');
 this.lineinput.className = 'consoleinput';

 this.Prompt = "";

 // private //
 var oThis = this;
 this.__deviceID = 0;
 this.Shell = new GShell();
 this.Shell.OnNewSession = function(){oThis._shellOnNewSession();}
 this.Shell.OnSessionChange = function(){oThis._shellOnSessionChange();}
 this.Shell.OnSessionClose = function(){oThis._shellOnSessionClose();}
 this.Shell.OnPreOutput = function(o,a,mT,mR,m){oThis._shellOnPreOutput(o,a,mT,mR,m);}
 this.Shell.OnOutput = function(o,a,ra,htmlO){oThis._shellOnOutput(o,a,ra,htmlO);}
 this.Shell.OnFinish = function(o,a,ra,htmlO){oThis._shellOnOutput(o,a,ra,htmlO);}
 this.Shell.OnInput = function(m,c,t){oThis._shellOnInput(m,c,t);}
 this.Shell.OnPreError = function(o,e){oThis._shellOnPreError(o,e);}
 this.Shell.OnError = function(o,e){oThis._shellOnError(e,o);}

 /* EVENTS */
 this.OnNewSession = null;
 this.OnSessionChange = null;
 this.OnSessionClose = null;
 this.OnPreOutput = null;
 this.OnOutput = null;
 this.OnFinish = null;
 this.OnInput = null;
 this.OnPreError = null;
 this.OnError = null;
 
 this.commands = new Array();
 this.cmdptr = -1;

 this.lines = new Array();
 this.init();
}

GTerminal.prototype.init = function()
{
 this.lineinput.onkeyup = this.lineInputKeyUp.bindEvent(this);
 this.lineinput.onkeydown = this.lineInputKeyDown.bindEvent(this);

 var oThis = this;
 this.hHR = new GHTTPRequest();
 this.hHR.OnHttpRequest = function(reqObj){
	  var response = reqObj.responseXML.documentElement;
	  var requestType = response.getElementsByTagName('request')[0].getAttribute('type');
	  switch(requestType)
	  {
	   case 'searchtabcompletion' : {
		 if(response.getElementsByTagName('request')[0].getAttribute('result') == "false")
		  return;
		 var results = response.getElementsByTagName('result');
		 var chunkparsed = response.getElementsByTagName('request')[0].getAttribute('chunkparsed');
		 var spc = " ";
		 if(results.length == 1)
		 {
		  if(results[0].getAttribute('type') == "directory")
		   spc = "";
		  oThis.lineinput.value = oThis.lineinput.value.substr(0,oThis.lineinput.value.length-chunkparsed.length)+results[0].getAttribute('name')+spc;
		 }
		 else if(oThis.secondTabPress && (results.length > 1))
		 {
		  var out = "";
		  var tmp = oThis.lineinput.value;
		  for(var c=0; c < results.length; c++)
		   out+= results[c].getAttribute('name')+"	";
		  oThis.addLine(out);
		  oThis.updatePrompt();
		  oThis.lineinput.value = tmp;
		 }
		 else if(results.length > 1)
		 {
		  var ok = false;
		  var part = chunkparsed;
		  var parttype = "file";
		  var cicle = results[0].getAttribute('name').length;
		  for(var i=0; i < results[0].getAttribute('name').length; i++)
		  {
		   for(var c=0; c < results.length; c++)
		   {
		    var file = results[c].getAttribute('name');
		    if(c==0) part+= file.charAt(part.length);
		    if(file.substr(0,part.length) != part)
		    {
		     ok = true;
		     part = part.substr(0,part.length-1);
		     parttype = results[c].getAttribute('type');
		     break;
		    }
		   }
		  }
		  if(parttype == "directory")
		   spc = "";
		  oThis.lineinput.value = oThis.lineinput.value.substr(0,oThis.lineinput.value.length-chunkparsed.length)+part+spc;
		 }
		} break;
	  }
	}

 this.Shell.NewSession();
}

GTerminal.prototype.lineInputKeyUp = function(event)
{
 if(window.event) // IE
  keynum = event.keyCode
 else if(event.which) // Netscape/Firefox/Opera
  keynum = event.which
 keychar = String.fromCharCode(keynum)
 switch(keynum)
 {
  case 13 : {
	     if(this.lineinput.callback)
	     {
	      var l = document.createElement('SPAN');
 	      l.className='prompt';
	      l.innerHTML = (this.lineinput.type == "password") ? "&nbsp;" : this.lineinput.value;
	      l.style.width = this.lineinput.offsetWidth;
	      var val = this.lineinput.value;
	      this.lineinput.value = "";
	      this.O.replaceChild(l, this.lineinput);
	      this.lineinput.callback(val);
	     }
	     else
	      this._process();
	    } break;
  case 38 : {if(!this.callback) this._typePriorCommand(); } break;
  case 40 : {if(!this.callback) this._typeNextCommand(); } break;
 }
 return keychar;
}

GTerminal.prototype.lineInputKeyDown = function(event)
{
 if(window.event) // IE
  keynum = event.keyCode
 else if(event.which) // Netscape/Firefox/Opera
  keynum = event.which
 keychar = String.fromCharCode(keynum)
 if(keynum == 9) // TAB
 {
  this._tabCompletion();
  if(event.preventDefault)
   event.preventDefault();
 }
 else
  return keychar;
}

GTerminal.prototype._typePriorCommand = function()
{
 if(!this.commands.length)
  return;
 this.lineinput.value = this.commands[this.cmdptr];
 if(this.cmdptr > 0)
  this.cmdptr--;
}

GTerminal.prototype._typeNextCommand = function()
{
 if(!this.commands.length)
  return;
 if(this.commands.length > (this.cmdptr+1))
  this.cmdptr++;
 this.lineinput.value = this.commands[this.cmdptr];
}

GTerminal.prototype._tabCompletion = function()
{
 if(this.lastTabCompletionIV && (this.lastTabCompletionIV == this.lineinput.value))
  this.secondTabPress = true;
 else
  this.secondTabPress = false;
 this.lastTabCompletionIV = this.lineinput.value;
 this.hHR.send(BASE_PATH+"./gshell.php?request=searchtabcompletion&sessid="+this.Shell.SessionID+"&input="+this.lineinput.value);
}

GTerminal.prototype._process = function()
{
 var cmd = this.lineinput.value;
 if(!cmd)
  return;
 if(this.commands.length && (this.commands[this.commands.length-1] == cmd))
 {
 }
 else
  this.commands.push(cmd);
 this.cmdptr = this.commands.length-1;

 if(cmd == 'clear')
 {
  this.lineinput.value = "";
  this.O.innerHTML = "";
  this.lines = new Array();
  this.updatePrompt();
  return;
 }
 else if(cmd == 'exit')
  return this.Shell.CloseSession();
 
 var l = document.createElement('SPAN');
 l.className='prompt';
 l.innerHTML = this.lineinput.value;
 l.style.width = this.lineinput.offsetWidth;
 this.lineinput.value = "";
 this.O.replaceChild(l, this.lineinput);

 if(cmd.substr(0,5) == "sudo ")
  this.Shell.sendSudoCommand(cmd.substr(5,cmd.length));
 else
  this.Shell.sendCommand(cmd);
}


GTerminal.prototype._shellOnNewSession = function()
{
 if(this.Shell.Username == "root")
  this.Prompt = this.Shell.Username+"@"+this.Shell.Hostname+":~#&nbsp;";
 else
  this.Prompt = this.Shell.Username+"@"+this.Shell.Hostname+":~$&nbsp;";

 if(this.lineinput.parentNode == this.O)
 {
  this.O.removeChild(this.lineinput.previousSibling);
  this.O.removeChild(this.lineinput);
 }

 this.updatePrompt();

 if(this.OnNewSession)
  this.OnNewSession();
}

GTerminal.prototype._shellOnSessionChange = function()
{
 if(this.Shell.Username == "root")
  this.Prompt = this.Shell.Username+"@"+this.Shell.Hostname+":~#&nbsp;";
 else
  this.Prompt = this.Shell.Username+"@"+this.Shell.Hostname+":~$&nbsp;";
 
 if(this.OnSessionChange)
  this.OnSessionChange();
}

GTerminal.prototype._shellOnSessionClose = function()
{
 if(this.Shell.Username == "root")
  this.Prompt = this.Shell.Username+"@"+this.Shell.Hostname+":~#&nbsp;";
 else
  this.Prompt = this.Shell.Username+"@"+this.Shell.Hostname+":~$&nbsp;";

 var l = document.createElement('SPAN');
 l.className='prompt';
 l.innerHTML = this.lineinput.value;
 l.style.width = this.lineinput.offsetWidth;
 this.lineinput.value = "";
 this.O.replaceChild(l, this.lineinput);

 this.addLine("");

 this.updatePrompt();

 if(this.OnSessionClose)
  this.OnSessionClose();
}

GTerminal.prototype._shellOnOutput = function(msg,outarr,redirectedOutarr,htmloutput)
{
 this.lastline = null;
 if(this.lineinput.parentNode == this.O)
 {
  this.O.removeChild(this.lineinput.previousSibling);
  this.O.removeChild(this.lineinput);
 }
 if(htmloutput)
  this.addHTMLLine(htmloutput);
 else
  this.addLine(msg);
 
 this.updatePrompt();

 if(this.OnOutput)
  this.OnOutput(msg,outarr,redirectedOutarr,htmloutput);
}

GTerminal.prototype._shellOnPreOutput = function(msg,outarr,msgType,msgRef,mode)
{
 if(!msg && !outarr)
  return;
 switch(mode)
 {
  case 'SINGLE_LINE' : {
	 if(this.lineinput.parentNode == this.O)
 	 {
 	  this.O.removeChild(this.lineinput.previousSibling);
 	  this.O.removeChild(this.lineinput);
 	 }
	 if(!this.lastline)
	  this.lastline = this.addLine(msg);
	 else
	  this.lastline.innerHTML = msg;
	} break;
  default : {
	 if(this.lineinput.parentNode == this.O)
	 {
	  this.O.removeChild(this.lineinput.previousSibling);
	  this.O.removeChild(this.lineinput);
	 }
	 this.addLine(msg);
	} break;
 }

 if(this.OnPreOutput)
  this.OnPreOutput(msg,outarr,msgType,msgRef,mode);
}

GTerminal.prototype._shellOnInput = function(msg,callback,type)
{
 switch(type)
 {
  default : {
	 if(this.lineinput.parentNode == this.O)
	 {
	  this.O.removeChild(this.lineinput.previousSibling);
	  this.O.removeChild(this.lineinput);
	 }
	 this.addLine("");
	 this.showInput(msg+":",callback,type);
	} break;
 }
 
 if(this.OnInput)
  this.OnInput(msg,callback,type);
}

GTerminal.prototype._shellOnError = function(error,msg)
{
 if(this.lineinput.parentNode == this.O)
 {
  this.O.removeChild(this.lineinput.previousSibling);
  this.O.removeChild(this.lineinput);
 }
 this.addLine(msg);

 this.updatePrompt();

 if(this.OnError)
  this.OnError(error,msg);
}

GTerminal.prototype._shellOnPreError = function(msg,error)
{
 if(!msg && !error)
  return;
 this._shellOnError(msg,error);
}

GTerminal.prototype.updatePrompt = function()
{
 var l = document.createElement('SPAN');
 l.className='prompt';
 l.innerHTML = this.Prompt;
 this.O.appendChild(l);
 if(this.O.offsetWidth)
  this.lineinput.style.width = this.O.offsetWidth - l.offsetWidth - 50;
 this.lineinput.type = "text";
 this.lineinput.callback = null;
 this.O.appendChild(this.lineinput);
 this.lineinput.focus();
}

GTerminal.prototype.showInput = function(msg,callback,type)
{
 var l = document.createElement('SPAN');
 l.className='prompt';
 l.innerHTML = msg;
 this.O.appendChild(l);
 if(this.O.offsetWidth)
  this.lineinput.style.width = this.O.offsetWidth - l.offsetWidth - 50;
 this.lineinput.focus();
 if(type == "PASSWORD")
  this.lineinput.type = "password";
 else
  this.lineinput.type = "text";
 this.lineinput.callback = callback;
 this.O.appendChild(this.lineinput);
 this.lineinput.focus();
}

GTerminal.prototype.addLine = function(str)
{
 var l = document.createElement('DIV');
 l.className='terminal_line';
 l.innerHTML = str.replace(/\n/g,'<br/>'); //html_entity_decode(str);
 this.O.appendChild(l);
 this.lines.push(l);
 this.O.scrollTop = this.O.scrollHeight;
 return l;
}

GTerminal.prototype.addHTMLLine = function(str)
{
 var l = document.createElement('DIV');
 l.className='terminal_line';
 l.innerHTML = str;
 this.O.appendChild(l);
 this.lines.push(l);
 this.O.scrollTop = this.O.scrollHeight;
 return l;
}

