/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-06-2013
 #PACKAGE: hacktvsearch-common
 #DESCRIPTION: Official Gnujiko HackTVT - Search Engine
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

function HackTVTSearchEngine()
{
 this.Commands = new Array();
 this.Variable = new Array();
 this.Functions = new Array();
 this.URL = "";


 this.cmdResults = new Array();
 this.funcResults = new Array();
 this.searchResults = new Array();
 
 /* CONFIGURATION */
 this.Config = {
	 debug : true	/* enable or disable debug */
	};


 /* EVENTS */
 this.OnInit = null; /* function() */
 this.OnRunQuery = null; /* function(string:query) */
 this.OnQuery = null; /* function(commandResults, functionResults) */
 this.OnPressEnter = null; /* function() */

}
//-------------------------------------------------------------------------------------------------------------------//
HackTVTSearchEngine.prototype.init = function(editorObject,url)
{
 this.editor = editorObject;
 this.editor.handler = this;
 this.editor.querytimer = null;
 this.editor.onkeyup = function(evt){
 	 var keyNum = 0;
	 if(window.event) keyNum = evt.keyCode;
	 else if(evt.which) keyNum = evt.which;
	 switch(keyNum)
	 {
	  case 37 : { /* LEFT */ } break;
	  case 38 : { /* UP */ } break;
	  case 39 : { /* RIGHT */ } break;
	  case 40 : { /* DOWN */ } break;
	  case 13 : { 
		  if(this.handler.OnPressEnter)
		   this.handler.OnPressEnter();
		 } break;
	  case 27 : { /* ESC */ } break;
	  default : {
		 var isprintable=/[^ -~]/;
		 var ok = false;
		 if((keyNum == 8) || (keyNum == 46)) // canc and backspace //
		  ok = true;
	     if((!isprintable.test(String.fromCharCode(keyNum))) || ok)
		 {
		  if(this.querytimer)
		  window.clearTimeout(this.querytimer);
		  var oThis = this;
		  this.querytimer = window.setTimeout(function(){oThis.handler.runQuery(oThis.value);},700);
		 }
		} break;
	 }
	}


 var oThis = this;

 if(!url)
  var url = document.location.href;
 if(url.indexOf("?") > 0)
  url = url.substr(0,url.indexOf("?"));

 this.URL = url;

 if(this.Config.debug)
  this.debugMessage("Loading ht-config");

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a)
	  return;
	 if(a['functions'])
	 {
	  for(var c=0; c < a['functions'].length; c++)
	  {
	   var funcInfo = {id:c, name:a['functions'][c]['name'], keywords:a['functions'][c]['keywords'], action:a['functions'][c]['action']}
	   oThis.Functions.push(funcInfo);
	  }	  
	 }
	 if(a['commands'])
	 {
	  for(var c=0; c < a['commands'].length; c++)
	  {
	   var chunklist = oThis.chunkerizeExp(a['commands'][c]['exp']);
	   var cmdInfo = {id:c, name:a['commands'][c]['name'], chunks:chunklist, currentidx:0}
	   oThis.Commands.push(cmdInfo);
	  }
	 }
	 if(a['variables'])
	 {
	  for(var c=0; c < a['variables'].length; c++)
	   oThis.Variable[a['variables'][c]['name']] = a['variables'][c]['title'];
	 }
	 if(oThis.Config.debug)
	 {
	  var msg = "Configuration file has been loaded!<br/>";
	  msg+= "N. of commands found: "+(a['commands'] ? a['commands'].length : "0")+"<br/>";
	  msg+= "N. of functions found: "+(a['functions'] ? a['functions'].length : "0")+"<br/>";
	  msg+= "N. of variables found: "+(a['variables'] ? a['variables'].length : "0")+"<br/>";
	  oThis.debugMessage(msg);
	 }
	 if(oThis.OnInit)
	  oThis.OnInit();
	}
 sh.sendCommand("hacktvsearch info -currenturl `"+url+"`");
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVTSearchEngine.prototype.chunkerizeExp = function(expression)
{
 var retList = new Array();

 for(var c=0; c < expression.length; c++)
 {
  switch(expression.charAt(c))
  {
   case "[" : {
	 var chunk = {type:"MIXED"};
	 var ret = this.scanIntoSquareBrackets(expression,c);
	 chunk.keys = ret.keys;
	 c = ret.nextidx+1;
	 retList.push(chunk);
	} break;

   case "{" : {
	 var end = expression.indexOf("}",c);
	 var str = expression.substr(c+1, (end-c)-1);
	 var chunk = {type:"VAR",varname:str};
	 c = end;
	 retList.push(chunk);
	} break;

   case "<" : {
	 var end = expression.indexOf("}",c);
	 var str = expression.substr(c+1, (end-c)-1);
	 switch(str.toUpperCase())
	 {
	  case "DATE" : var chunk = {type:"DATE"}; break;
	  case "TIME" : var chunk = {type:"TIME"}; break;
	  case "YEAR" : var chunk = {type:"YEAR"}; break;
	  case "MONTH" : var chunk = {type:"MONTH"}; break;
	  default : var chunk = {type:"UNKNOWN"} ; break;
	 }
	 c = end;
	 retList.push(chunk);
	} break;

   default : {
	 if(expression.charAt(c) == " ")
	  continue;
	 var end = expression.indexOf(" ",c);
	 if(end < c)
	  end = expression.length;
	 var str = expression.substr(c,(end-c));
	 var chunk = {type:"WORD",value:str};
	 c = end;
	 retList.push(chunk);
	} break;
  }
 }
 return retList;
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVTSearchEngine.prototype.scanIntoSquareBrackets = function(query, idx)
{
 var end = query.indexOf("]",idx);
 query = query.substr(idx+1,(end-idx-1));

 var x = new Array();
 if(query.indexOf("|") > 0)
  x = query.split("|");
 else
  x.push(query);

 var retInfo = {keys:new Array(), nextidx:end}

 for(var c=0; c < x.length; c++)
 {
  var key = new Array();
  var str = x[c].trim();
  switch(str)
  {
   case "<DATE>" : key['type'] = "DATE"; break;
   case "<TIME>" : key['type'] = "TIME"; break;
   case "<MONTH>" : key['type'] = "MONTH"; break;
   case "<YEAR>" : key['type'] = "YEAR"; break;
   default : {
	 if((str.indexOf("{") > -1) && (str.indexOf("}") > 0))
	 {
	  key['type'] = "VAR";
	  key['name'] = str.replace("{","").replace("}","");
	 }
	 else
	 {
	  key['type'] = "WORD";
	  key['value'] = str;
	 }
	} break;
  }
  retInfo.keys.push(key);
 }
 return retInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVTSearchEngine.prototype.runQuery = function(query)
{
 if(this.workInProgress)
 {
  this.lastQuery = query;
  return;
 }
 this.lastQuery = "";

 if(this.OnRunQuery)
  this.OnRunQuery(query);

 if(this.Config.debug)
  this.debugMessage("Execute query: "+query+"<hr/>");

 /* RESET RESULTS */
 this.cmdResults = new Array();
 this.funcResults = null;
 this.searchResults = null;

 var oThis = this;

 for(var c=0; c < this.Commands.length; c++)
 {
  this.chunkTest(this.Commands[c], query, function(_cmdInfo,_ret){oThis.onChunkTest(_cmdInfo,_ret);});
 }

 this.funcTest(this.Functions, query, function(_funcList,_ret){oThis.onFuncTest(_funcList,_ret);});
 
 this.searchTest(query, function(_ret){oThis.onSearchTest(_ret);});
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVTSearchEngine.prototype.debugMessage = function(msg)
{
 if(this.OnDebugMessage)
  this.OnDebugMessage(msg);
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVTSearchEngine.prototype.orderResults = function()
{
 if(this.lastQuery)
  return this.runQuery(this.lastQuery);
 /* TODO: ordinare tutti i risultati qui */

 var sortbymatch = new Array();
 var testResults = new Array();
 var retResults = new Array();

 for(var c=0; c < this.cmdResults.length; c++)
 {
  var cmdInfo = this.cmdResults[c].command;
  var testResult = this.cmdResults[c].results;
  sortbymatch.push(testResult.matches);
  if(!testResults[testResult.matches])
   testResults[testResult.matches] = new Array();
  testResults[testResult.matches].push(this.cmdResults[c]);
 }

 sortbymatch.sort();
 sortbymatch.reverse();

 for(var c=0; c < sortbymatch.length; c++)
 {
  var point = sortbymatch[c];
  var cmdInfo = testResults[point][0].command;
  var testResult = testResults[point][0].results;
  retResults.push({command:cmdInfo,results:testResult});
  testResults[point].splice(0,1);
 }


 /*var msg = "";
 for(var c=0; c < retResults.length; c++)
 {
  msg+= "Command #"+retResults[c].command.id+" has "+retResults[c].results.matches+"\n";
 }
 alert(msg);*/

 if(this.OnQuery)
  this.OnQuery(retResults, this.funcResults, this.searchResults);
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVTSearchEngine.prototype.onChunkTest = function(cmdInfo, testResult)
{
 if(this.Config.debug)
 {
  var msg = "ChunkTest Results for command #"+cmdInfo.id+"<br/>";
  msg+= "Last query: "+testResult.lastqry+"<br/>";
  msg+= "Rem. query: "+testResult.remqry+"<br/>";
  msg+= "Last pointer pos: "+testResult.lastpos+"<br/>";
  msg+= "N. of matched: "+testResult.matches+"<br/>";
  msg+= "N. of suggested words: "+testResult.suggested.length+"<br/>";
  if(testResult.suggested.length)
   msg+= "List of suggested words: <small>"+implode(",",testResult.suggested)+"</small><br/>";
  if(testResult.expected.length)
  {
   msg+= "List of expected:<br/>";
   for(var c=0; c < testResult.expected.length; c++)
	msg+= "Type: "+testResult.expected[c].type+" - "+testResult.expected[c].hintmsg+"<br/>";
  }
  if(testResult.matches >= cmdInfo.chunks.length)
   msg+= "This query is full matched! matched "+testResult.matches+" on "+cmdInfo.chunks.length+"<br/>";
  msg+= "<hr/>";

  this.debugMessage(msg);
 }

 this.cmdResults.push({command:cmdInfo,results:testResult});

 if((this.cmdResults.length == this.Commands.length) && this.funcResults && this.searchResults)
  this.orderResults();
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVTSearchEngine.prototype.chunkTest = function(cmdInfo, query, callback, testResult, idx)
{
 if(!testResult)
  testResult = {matches:0, suggested:new Array(), expected:new Array(), spotkeys:new Array(), lastpos:0, lastqry:query, remqry:"", cmdref:cmdInfo}

 testResult.lastqry = query;
 testResult.expected = new Array();

 if(!idx)
  idx = cmdInfo.currentidx;

 var chunk = cmdInfo.chunks[idx];
 if(!chunk)
  return callback(cmdInfo, testResult);

 var oThis = this;

 switch(chunk.type)
 {
  case "DATE" : {
	 var x = query.split(" ");
	 var string = x[0].substr(0,10);
	 if(!this.isValidDate(string))
	 {
	  testResult.expected.push({type:"DATE",hintmsg:"gg/mm/aaaa"});
	  return callback(cmdInfo, testResult);
	 }
	 testResult.matches++;
	 testResult.spotkeys.push({type:'DATE',value:string});
	 testResult.lastpos+= string.length+1;
	 testResult.lastqry = string;
	 idx++;
	 query = query.substr(string.length+1);
	 testResult.remqry = query;
	 this.chunkTest(cmdInfo, query, callback, testResult, idx);
	} break;

  case "TIME" : {
	 var x = query.split(" ");
	 var string = x[0].substr(0,5);
	 if(!this.isValidTime(string))
	 {
	  testResult.expected.push({type:"TIME",hintmsg:"hh:mm"});
	  return callback(cmdInfo, testResult);
	 }
	 testResult.matches++;
	 testResult.spotkeys.push({type:'TIME',value:string});
	 testResult.lastpos+= string.length+1;
	 testResult.lastqry = string;
	 idx++;
	 query = query.substr(string.length+1);
	 testResult.remqry = query;
	 this.chunkTest(cmdInfo, query, callback, testResult, idx);
	} break;

  case "MONTH" : {
	 var x = query.split(" ");
	 var string = x[0];
	 var len = this.isValidMonth(string);
	 if(!len)
	 {
	  testResult.expected.push({type:"MONTH",hintmsg:"gen,feb,mar,..."});
	  return callback(cmdInfo, testResult);
	 }
	 testResult.matches++;
	 testResult.spotkeys.push({type:'MONTH',value:string});
	 testResult.lastpos+= len+1;
	 testResult.lastqry = string;
	 idx++;
	 query = query.substr(len+1);
	 testResult.remqry = query;
	 this.chunkTest(cmdInfo, query, callback, testResult, idx);
	} break;

  case "YEAR" : {
	 var x = query.split(" ");
	 var string = x[0].substr(0,4);
	 if(!this.isValidYear(string))
	 {
	  testResult.expected.push({type:"YEAR",hintmsg:"inserisci l'anno. es: 2013"});
	  return callback(cmdInfo, testResult);
	 }
	 testResult.matches++;
	 testResult.spotkeys.push({type:'YEAR',value:string});
	 testResult.lastpos+= string.length+1;
	 testResult.lastqry = string;
	 idx++;
	 query = query.substr(string.length+1);
	 testResult.remqry = query;
	 this.chunkTest(cmdInfo, query, callback, testResult, idx);
	} break;

  case "WORD" : {
	 var string = query.toLowerCase();
	 if(string.indexOf(chunk.value) < 0)
	 {
	  testResult.expected.push({type:"WORD",hintmsg:chunk.value});
	  return callback(cmdInfo, testResult);
	 }
	 testResult.matches++;
	 testResult.spotkeys.push({type:'WORD',value:chunk.value});
	 testResult.lastpos+= chunk.value.length+1;
	 testResult.lastqry = chunk.value;
	 idx++;
	 query = query.substr(chunk.value.length+1);
	 testResult.remqry = query;
	 this.chunkTest(cmdInfo, query, callback, testResult, idx);
	} break;

  case "MIXED" : {
	 this.chunkTestMixed(chunk, cmdInfo, query, callback, testResult, idx);
	} break;

  case "VAR" : {
	 testResult.expected.push({type:"VAR",hintmsg:this.Variable[chunk.varname]});
	 this.workInProgress = true;
	 var sh = new GShell();
	 sh.OnOutput = function(o,a){
		 oThis.workInProgress = false;
		 if(!a)
		  return callback(cmdInfo, testResult);
		 if(a['result'])
		 {
		  var string = a['result'];
		  testResult.matches++;
	 	  testResult.spotkeys.push({type:'VAR',value:string});
		  testResult.lastpos+= string.length+1;
		  testResult.lastqry = string;
		  idx++;
		  query = query.substr(string.length+1);
		  testResult.remqry = query;
		  return oThis.chunkTest(cmdInfo, query, callback, testResult, idx);
		 }
		 else if(a['suggested'])
		  testResult.suggested = a['suggested'];
		 return callback(cmdInfo, testResult);
		}
	 var tmpqry = query.replace(/&/g, "&amp;");
	 tmpqry = tmpqry.E_QUOT().replace(/&rsquo;/g, "&lsquo;")
	 sh.sendCommand("hacktvsearch varsearch -var '"+chunk.varname+"' -currenturl `"+this.URL+"` `"+tmpqry+"`");
	} break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
HackTVTSearchEngine.prototype.chunkTestMixed = function(chunk, cmdInfo, query, callback, testResult, idx)
{
 var oThis = this;
 var waitingFor = false;

 if(!query)
 {
  for(var c=0; c < chunk.keys.length; c++)
  {
   var key = chunk.keys[c];
   switch(key['type'])
   {
    case "DATE" : {
		 testResult.expected.push({type:"DATE",hintmsg:"gg/mm/aaaa"}); 
		} break;

    case "TIME" : {
		 testResult.expected.push({type:"TIME",hintmsg:"hh:mm"}); 
		} break;

    case "MONTH" : {
		 testResult.expected.push({type:"MONTH",hintmsg:"gen,feb,mar,..."});
		} break;

    case "YEAR" : {
		 testResult.expected.push({type:"YEAR",hintmsg:"inserisci l'anno. es: 2013"});
		} break;

    case "WORD" : {
		 testResult.expected.push({type:"WORD",hintmsg:key['value']});
		} break;

    case "VAR" : {
		 testResult.expected.push({type:"VAR",hintmsg:this.Variable[key['name']]});
		 waitingFor = true;
		 this.workInProgress = true;
		 var sh = new GShell();
	 	 sh.OnOutput = function(o,a){
			 oThis.workInProgress = false;
			 waitingFor = false;
			 if(!a)
			  return callback(cmdInfo, testResult);
			 if(a['suggested'])
			  testResult.suggested = a['suggested'];
			 return callback(cmdInfo, testResult);
			}
		 sh.sendCommand("hacktvsearch varsearch -var '"+key['name']+"' -currenturl `"+this.URL+"`");
		} break;
   }
  }

  if(!waitingFor)
   return callback(cmdInfo, testResult);
  return;
 }

 var waitingFor = false;

 for(var c=0; c < chunk.keys.length; c++)
 {
  var key = chunk.keys[c];
  switch(key['type'])
  {
   case "DATE" : {
	 var x = query.split(" ");
	 var string = x[0].substr(0,10);
	 if(this.isValidDate(string))
	 {
	  testResult.matches++;
	  testResult.spotkeys.push({type:'DATE',value:string});
	  testResult.lastpos+= string.length+1;
	  testResult.lastqry = string;
	  idx++;
	  query = query.substr(string.length+1);
	  testResult.remqry = query;
	  return this.chunkTest(cmdInfo, query, callback, testResult, idx);
	 }
	 else
	  testResult.expected.push({type:"DATE",hintmsg:"gg/mm/aaaa"});
	} break;

  case "TIME" : {
	 var x = query.split(" ");
	 var string = x[0].substr(0,5);
	 if(this.isValidTime(string))
	 {
	  testResult.matches++;
	  testResult.spotkeys.push({type:'TIME',value:string});
	  testResult.lastpos+= string.length+1;
	  testResult.lastqry = string;
	  idx++;
	  query = query.substr(string.length+1);
	  testResult.remqry = query;
	  return this.chunkTest(cmdInfo, query, callback, testResult, idx);
	 }
	 else
	  testResult.expected.push({type:"TIME",hintmsg:"hh:mm"});
	} break;

  case "MONTH" : {
	 var x = query.split(" ");
	 var string = x[0];
	 var len = this.isValidMonth(string);
	 if(len)
	 {
	  testResult.matches++;
	  testResult.spotkeys.push({type:'MONTH',value:string});
	  testResult.lastpos+= len+1;
	  testResult.lastqry = string;
	  idx++;
	  query = query.substr(len+1);
	  testResult.remqry = query;
	  return this.chunkTest(cmdInfo, query, callback, testResult, idx);
	 }
	 else
	  testResult.expected.push({type:"MONTH",hintmsg:"gen,feb,mar,..."});
	} break;

  case "YEAR" : {
	 var x = query.split(" ");
	 var string = x[0].substr(0,4);
	 if(this.isValidYear(string))
	 {
	  testResult.matches++;
	  testResult.spotkeys.push({type:'YEAR',value:string});
	  testResult.lastpos+= string.length+1;
	  testResult.lastqry = string;
	  idx++;
	  query = query.substr(string.length+1);
	  testResult.remqry = query;
	  return this.chunkTest(cmdInfo, query, callback, testResult, idx);
	 }
	 else
	  testResult.expected.push({type:"YEAR",hintmsg:"inserisci l'anno. es: 2013"});
	} break;

  case "WORD" : {
	 var string = query.toLowerCase();
	 if(string.indexOf(key['value']) > -1)
	 {
	  testResult.matches++;
	  testResult.spotkeys.push({type:'WORD',value:key['value']});
	  testResult.lastpos+= key['value'].length+1;
	  testResult.lastqry = string;
	  idx++;
	  query = query.substr(key['value'].length+1);
	  testResult.remqry = query;
	  return this.chunkTest(cmdInfo, query, callback, testResult, idx);
	 }
	 else
	  testResult.expected.push({type:"WORD",hintmsg:key['value']});
	} break;


   case "VAR" : {
	 testResult.expected.push({type:"VAR",hintmsg:this.Variable[key['name']]});
	 waitingFor = true;
	 this.workInProgress = true;
	 var sh = new GShell();
	 sh.OnOutput = function(o,a){
		 oThis.workInProgress = false;
		 if(!a)
		  return callback(cmdInfo, testResult);
		 if(a['result'])
		 {
		  var string = a['result'];
		  testResult.matches++;
	  	  testResult.spotkeys.push({type:'VAR',value:string});
		  testResult.lastpos+= string.length+1;
		  testResult.lastqry = string;
		  idx++;
		  query = query.substr(string.length+1);
		  testResult.remqry = query;
		  return oThis.chunkTest(cmdInfo, query, callback, testResult, idx);
		 }
		 else if(a['suggested'])
		  testResult.suggested = a['suggested'];
		 return callback(cmdInfo, testResult);
		}
	 var tmpqry = query.replace(/&/g, "&amp;");
	 tmpqry = tmpqry.E_QUOT().replace(/&rsquo;/g, "&lsquo;")
	 sh.sendCommand("hacktvsearch varsearch -var '"+key['name']+"' -currenturl `"+this.URL+"` `"+tmpqry+"`");
	} break;

   case "MIXED" : {
	 /* TODO: vedere se possibile */
	} break;

  }
 }

 if(!waitingFor)
  return callback(cmdInfo, testResult);
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVTSearchEngine.prototype.isValidDate = function(query)
{
 var dt = strdatetime_to_iso(query);
 if(dt && (dt.substr(10) != "0000-00-00"))
  return true;
 return false;
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVTSearchEngine.prototype.isValidTime = function(query)
{
 var x = query.split(":");
 if(!x.length)
  x = query.split(".");
 if(!x.length || (x.length > 3))
  return false;
 /* TODO: da continuare */
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVTSearchEngine.prototype.isValidMonth = function(query)
{
 var months = new Array("gennaio","febbraio","marzo","aprile","maggio","giugno","luglio","agosto","settembre","ottobre","novembre","dicembre");
 //var dim = new Array("gen","feb","mar","apr","mag","giu","lug","ago","set","ott","nov","dic");

 for(var c=0; c < months.length; c++) { if(query == months[c]) return query.length; }
 //for(var c=0; c < dim.length; c++) { if(query == dim[c]) return query.length; }

 return false;
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVTSearchEngine.prototype.isValidYear = function(query)
{
 if(query.length != 4)
  return false;
 if(!parseFloat(query))
  return false;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
HackTVTSearchEngine.prototype.onFuncTest = function(funcList, testResult)
{
 if(this.Config.debug)
 {
  if(testResult.result)
  {
   var msg = "Function Test: OK!";
   if(testResult.suggested.length)
   {
	for(var c=0; c < testResult.suggested.length; c++)
	 msg+= "<br/>"+testResult.suggested[c].string;
   }
   else
	msg+= " no results found.<br/>";
   this.debugMessage(msg);
  }
  else
  {
   var msg = "Function Test: Failed!";
   if(testResult.suggested.length)
   {
	msg+= "This is a list of suggested words:<br/>";
	for(var c=0; c < testResult.suggested.length; c++)
	 msg+= "<br/>"+testResult.suggested[c].string;
   }
   else
	msg+= " no suggested found.<br/>";
   this.debugMessage(msg);
  }
 }

 this.funcResults = {functionList:funcList,results:testResult};

 if((this.cmdResults.length == this.Commands.length) && this.funcResults && this.searchResults)
  this.orderResults();
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVTSearchEngine.prototype.funcTest = function(funcList, query, callback)
{
 var testResult = {result:false, matched:null, suggested:new Array()}

 for(var j=0; j < funcList.length; j++)
 {
  var funcInfo = funcList[j];
  for(var c=0; c < funcInfo.keywords.length; c++)
  {
   var word = funcInfo.keywords[c];
   if(query.toLowerCase() == word)
   {
    testResult.result = true;
    testResult.matched = {string:word, func:funcInfo};
    return callback(funcList, testResult);
   }
   else if(word.indexOf(query) > -1)
    testResult.suggested.push({string:word, func:funcInfo});
  }
 }
 return callback(funcList, testResult);
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
HackTVTSearchEngine.prototype.onSearchTest = function(testResult)
{
 if(this.Config.debug)
 {
  if(testResult.result)
  {
   var msg = "Search Test: OK!";
   if(testResult.suggested.length)
   {
	for(var c=0; c < testResult.suggested.length; c++)
	 msg+= "<br/>"+testResult.suggested[c].string;
   }
   else
	msg+= " no results found.<br/>";
   this.debugMessage(msg);
  }
  else
  {
   var msg = "Search Test: Failed!";
   if(testResult.suggested.length)
   {
	msg+= "This is a list of suggested words:<br/>";
	for(var c=0; c < testResult.suggested.length; c++)
	 msg+= "<br/>"+testResult.suggested[c].string;
   }
   else
	msg+= " no suggested found.<br/>";
   this.debugMessage(msg);
  }
 }

 this.searchResults = {results:testResult};

 if((this.cmdResults.length == this.Commands.length) && this.funcResults && this.searchResults)
  this.orderResults();
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVTSearchEngine.prototype.searchTest = function(query, callback)
{
 var testResult = {result:false, matched:null, suggested:new Array(), sections:new Array()}

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a)
	  return callback(testResult);
	 if(!a['sections'])
	  return callback(testResult);
	 for(var c=0; c < a['sections'].length; c++)
	 {
	  var sec = a['sections'][c];
	  var res = sec['results'];
	  if(!res || !res.length)
	   continue;
	  testResult.sections.push(sec);
	  for(var i=0; i < res.length; i++)
	  {
	   if(res[i]['name'].toLowerCase() == query.toLowerCase())
	   {
		testResult.result = true;
		testResult.matched = {string:res[i]['name'], action:res[i]['action'], section:sec};
	   }
	   else
		testResult.suggested.push({string:res[i]['name'], action:res[i]['action'], section:sec});
	  }
	 }
	 return callback(testResult);
	}

 sh.sendCommand("hacktvsearch search -currenturl `"+this.URL+"` `"+query+"`");
}
//-------------------------------------------------------------------------------------------------------------------//

