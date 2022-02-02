/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-06-2013
 #PACKAGE: hacktvsearch
 #DESCRIPTION: 
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/
var ACTIVE_HACKTVSEARCH = null;

function HackTVSearch(editObj, correctX)
{
 var oThis = this;
 this.SearchEngine = new HackTVTSearchEngine();
 this.SearchEngine.OnRunQuery = function(_qry){oThis.__onrunquery(_qry);};
 this.SearchEngine.OnQuery = function(_cmdresults,_funcresults,_searchresults){oThis.__onquery(_cmdresults,_funcresults,_searchresults);};
 this.SearchEngine.OnPressEnter = function(){oThis.__onPressEnter();}

 this.O = editObj;
 this.correctX = correctX;

 this.__createSearchWidget();
 this.__createDebugWidget();

 this.init();

 document.addEventListener ? document.addEventListener("mouseup", function(){oThis.hide();},false) : document.attachEvent("onmouseup",function(){oThis.hide();});
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVSearch.prototype.show = function()
{
 /* get objpos */
 var left = this.O.offsetLeft;
 var top  = this.O.offsetTop;
 var obj = this.O;
 while(obj = obj.offsetParent)
 {
  left+= obj.offsetLeft;
  top+= obj.offsetTop;
 }

 var tb = document.getElementById("desktop-base-table");
 this.searchWidget.style.left = left+this.correctX;
 this.searchWidget.style.top = top-this.searchWidget.offsetHeight-10;
 this.searchWidget.style.visibility = "visible";

}
//-------------------------------------------------------------------------------------------------------------------//
HackTVSearch.prototype.init = function()
{
 ACTIVE_HACKTVSEARCH = this;
 var url = document.location.href;
 if(url.indexOf("?") > 0)
  url = url.substr(0,url.indexOf("?"));

 this.URL = url;

 this.loadingDiv = document.createElement('DIV');
 this.loadingDiv.id = "hacktvsearch-loading";
 this.loadingDiv.innerHTML = "&nbsp;";
 document.body.appendChild(this.loadingDiv);

 this.SearchEngine.init(this.O, url);
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVSearch.prototype.hide = function(force, clearAndFocus)
{
 this.searchWidget.hide(force);
 if(clearAndFocus)
 {
  this.O.value = "";
  this.O.focus();
 }
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVSearch.prototype.showDebug = function(msg,clear)
{
 if(clear)
  this.debugWidget.innerHTML = "";
 var div = document.createElement('DIV');
 div.innerHTML = msg;
 this.debugWidget.appendChild(div);
 this.debugWidget.style.visibility = "visible";
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVSearch.prototype.__onrunquery = function(query)
{

}
//-------------------------------------------------------------------------------------------------------------------//
HackTVSearch.prototype.__onquery = function(_cmdresults,_funcresults,_searchresults)
{
 var secDiv = this.searchWidget.createSection("command", "actions", "COMANDI");
 var funcDiv = this.searchWidget.createSection("function", "funcs", "FUNZIONI");
 var searchSections = new Array();
 if(_searchresults && _searchresults.results.sections.length)
 {
  for(var c=0; c < _searchresults.results.sections.length; c++)
  {
   var sec = _searchresults.results.sections[c];
   var div = this.searchWidget.createSection(sec['type'], sec['tag'], sec['title']);
   div.data = sec;
   _searchresults.results.sections[c]['div'] = div;
   searchSections.push(div);
  }
 }

 var resultsLimit = 10;
 var resultIdx = 0;

 this.searchWidget.emptySections();
 this.onpressenter_callback = null;
 this.onpressenter_testresult = null;

 /* COMMANDS */
 for(var j=0; j < _cmdresults.length; j++)
 {
  if(resultIdx == resultsLimit)
   break;

  var cmdInfo = _cmdresults[j].command;
  var testResult = _cmdresults[j].results;

  if(!testResult.matches)
   continue;

  if(testResult.matches && cmdInfo.chunks.length && (testResult.matches >= cmdInfo.chunks.length))
  {
   /* FULL MATCHED */
   var _div = secDiv.createItemVoid("<span><b>"+this.O.value+"</b></span><a class='rightlink' href='#' onclick='ACTIVE_HACKTVSEARCH.execCommand(this.parentNode.testResult)'>esegui &raquo;</a>");
   this.onpressenter_callback = function(){ACTIVE_HACKTVSEARCH.execCommand(ACTIVE_HACKTVSEARCH.onpressenter_testresult);}
   this.onpressenter_testresult = testResult;
   _div.testResult = testResult;
   resultIdx++;
   if(resultIdx == resultsLimit)
	break;
  }
  else if(testResult.suggested.length)
  {
   if(testResult.suggested.length == 1)
   {
	var firstText = this.O.value.substr(0, testResult.lastpos);
	var lastText = this.O.value.substr(testResult.lastpos);
	var highlightText = testResult.suggested[0].replace(new RegExp( "(" + lastText + ")" , 'gi' ), "<b>$1</b>");
    var _div = secDiv.createItemVoid("<span onclick='ACTIVE_HACKTVSEARCH.useWord(\""+testResult.suggested[0]+"\","+testResult.lastpos+")'><b>"+firstText+"</b> "+highlightText+"</span>"+(testResult.matches == cmdInfo.chunks.length-1 ? "<a class='rightlink' href='#' onclick='ACTIVE_HACKTVSEARCH.execCommand(this.parentNode.testResult,true)'>esegui &raquo;</a>" : ""));
	testResult.retMissedType = testResult.expected[testResult.expected.length-1].type;
	testResult.retMissedValue = testResult.suggested[0];
	this.onpressenter_callback = function(){ACTIVE_HACKTVSEARCH.execCommand(ACTIVE_HACKTVSEARCH.onpressenter_testresult,true)}
    this.onpressenter_testresult = testResult;
	_div.testResult = testResult;
    resultIdx++;
    if(resultIdx == resultsLimit)
	 break;
   }
   else
   {
    for(var c=0; c < testResult.suggested.length; c++)
    {
	 var firstText = this.O.value.substr(0, testResult.lastpos);
	 var lastText = this.O.value.substr(testResult.lastpos);
	 var highlightText = testResult.suggested[c].replace(new RegExp( "(" + lastText + ")" , 'gi' ), "<b>$1</b>");
     var _div = secDiv.createItemVoid("<span onclick='ACTIVE_HACKTVSEARCH.useWord(\""+testResult.suggested[c]+"\","+testResult.lastpos+")'><b>"+firstText+"</b> "+highlightText+"</span>"+(testResult.matches == cmdInfo.chunks.length-1 ? "<a class='rightlink' href='#' onclick='ACTIVE_HACKTVSEARCH.execCommand(this.parentNode.testResult,true)'>esegui &raquo;</a>" : ""));
	 testResult.retMissedType = testResult.expected[testResult.expected.length-1].type;
	 testResult.retMissedValue = testResult.suggested[0];
	 this.onpressenter_callback = function(){ACTIVE_HACKTVSEARCH.execCommand(ACTIVE_HACKTVSEARCH.onpressenter_testresult,true)}
     this.onpressenter_testresult = testResult;
	 _div.testResult = testResult;
     resultIdx++;
     if(resultIdx == resultsLimit)
	  break;
    }
   }
  }
  else if(testResult.expected.length)
  {
   var firstText = this.O.value.substr(0, testResult.lastpos);
   for(var c=0; c < testResult.expected.length; c++)
   {
	switch(testResult.expected[c].type)
	{
	 case "WORD" : secDiv.createItemVoid("<span onclick='ACTIVE_HACKTVSEARCH.useWord(\""+testResult.expected[c].hintmsg+"\","+testResult.lastpos+")'><b>"+firstText+"</b>&nbsp;"+testResult.expected[c].hintmsg+"</span>"); break;
     default : secDiv.createItemVoid("<span><b>"+firstText+"</b>&nbsp;<small>[&nbsp;"+testResult.expected[c].hintmsg+"&nbsp;]</span>"); break;
	}
    resultIdx++;
    if(resultIdx == resultsLimit)
	 break;
   }
  }
 }

 var resultsLimit = 10;
 var resultIdx = 0;

 /* FUNCTIONS */
 var testResult = _funcresults.results;
 if(testResult.result)
 {
  var action = testResult.matched.func.action;
  var onclickhtml = "";
  if(action['command'])
   onclickhtml = " onclick='gnujikodesktopbaseRunCommand(\""+action['command']+"\");ACTIVE_HACKTVSEARCH.hide(true,true);'";
  else if(action['sudocommand'])
   onclickhtml = " onclick='gnujikodesktopbaseRunSudoCommand(\""+action['sudocommand']+"\");ACTIVE_HACKTVSEARCH.hide(true,true);'";
  else if(action['onclick'])
   onclickhtml = " onclick=\""+action['onclick']+";ACTIVE_HACKTVSEARCH.hide(true,true);\"";
  var html = "<span"+onclickhtml+"><b>"+this.O.value+"</b></span><a class='rightlink' href='"+(action['url'] ? action['url'] : '#')+"'"+onclickhtml+">"+action['title']+"</a>";
  funcDiv.createItemVoid(html);
 }
 else if(testResult.suggested.length)
 {
  for(var c=0; c < testResult.suggested.length; c++)
  {
   var action = testResult.suggested[c].func.action;
   var highlightText = testResult.suggested[c].string.replace(new RegExp( "(" + this.O.value + ")" , 'gi' ), "<b>$1</b>");
   var onclickhtml = "";
   if(action['command'])
    onclickhtml = " onclick='gnujikodesktopbaseRunCommand(\""+action['command']+"\");ACTIVE_HACKTVSEARCH.hide(true,true);'";
   else if(action['sudocommand'])
    onclickhtml = " onclick='gnujikodesktopbaseRunSudoCommand(\""+action['sudocommand']+"\");ACTIVE_HACKTVSEARCH.hide(true,true);'";
   else if(action['onclick'])
    onclickhtml = " onclick=\""+action['onclick']+";ACTIVE_HACKTVSEARCH.hide(true,true);\"";
   var html = "<span"+onclickhtml+">"+highlightText+"</span><a class='rightlink' href='"+(action['url'] ? action['url'] : '#')+"'"+onclickhtml+">"+action['title']+"</a>";
   funcDiv.createItemVoid(html);
   resultIdx++;
   if(resultIdx == resultsLimit)
	break;
  }
 }

 var resultsLimit = 10;
 var resultIdx = 0;

 /* SEARCH */
 var testResult = _searchresults.results;
 if(testResult.result)
 {
  var action = testResult.matched.action;
  var onclickhtml = "";
  if(action['command'])
   onclickhtml = " onclick='gnujikodesktopbaseRunCommand(\""+action['command']+"\");ACTIVE_HACKTVSEARCH.hide(true,true);'";
  else if(action['sudocommand'])
   onclickhtml = " onclick='gnujikodesktopbaseRunSudoCommand(\""+action['sudocommand']+"\");ACTIVE_HACKTVSEARCH.hide(true,true);'";
  else if(action['onclick'])
   onclickhtml = " onclick=\""+action['onclick']+";ACTIVE_HACKTVSEARCH.hide(true,true);\"";
  var html = "<span"+onclickhtml+"><b>"+this.O.value+"</b></span><a class='rightlink' href='"+(action['url'] ? action['url'] : '#')+"'"+onclickhtml+">"+action['title']+"</a>"; 
  testResult.matched.section['div'].createItemVoid(html);
 }
 else if(testResult.suggested.length)
 {
  for(var c=0; c < testResult.suggested.length; c++)
  {
   var action = testResult.suggested[c].action;
   var onclickhtml = "";
   var highlightText = testResult.suggested[c].string.replace(new RegExp( "(" + this.O.value + ")" , 'gi' ), "<b>$1</b>");
   if(action['command'])
    onclickhtml = " onclick='gnujikodesktopbaseRunCommand(\""+action['command']+"\");ACTIVE_HACKTVSEARCH.hide(true,true);'";
   else if(action['sudocommand'])
    onclickhtml = " onclick='gnujikodesktopbaseRunSudoCommand(\""+action['sudocommand']+"\");ACTIVE_HACKTVSEARCH.hide(true,true);'";
   else if(action['onclick'])
    onclickhtml = " onclick=\""+action['onclick']+";ACTIVE_HACKTVSEARCH.hide(true,true);\"";
   var html = "<span"+onclickhtml+">"+highlightText+"</span><a class='rightlink' href='"+(action['url'] ? action['url'] : '#')+"'"+onclickhtml+">"+action['title']+"</a>";
   testResult.suggested[c].section['div'].createItemVoid(html);
   resultIdx++;
   if(resultIdx == resultsLimit)
	break;
  }
 }


 this.show();
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVSearch.prototype.__onPressEnter = function()
{
 if(typeof(this.onpressenter_callback) == "function")
  this.onpressenter_callback();
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVSearch.prototype.useWord = function(string, from)
{
 this.O.value = this.O.value.substr(0,from).trim()+" "+string+" ";
 this.O.focus();
 this.SearchEngine.runQuery(this.O.value);
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVSearch.prototype.execCommand = function(testResult,autocompletemissed)
{
 if(autocompletemissed)
 {
  testResult.matches++;
  testResult.spotkeys.push({type:testResult.retMissedType,value:testResult.retMissedValue});
 }

 var q = "-cmdid "+testResult.cmdref.id;
 for(var c=0; c < testResult.spotkeys.length; c++)
  q+= " -kt "+testResult.spotkeys[c].type+" -kv `"+testResult.spotkeys[c].value+"`";

 var oThis = this;
 this.hide(true,true);

 document.body.appendChild(this.loadingDiv);
 this.loadingDiv.style.left = Math.floor(this._getScreenWidth()/2) - 118;
 this.loadingDiv.style.top = Math.floor(this._getScreenHeight()/2) - 118;
 this.loadingDiv.style.visibility = "visible";


 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 oThis.loadingDiv.style.visibility = "hidden";
	 if(!a) return;

	 /* EXEC GSHELL COMMAND */
	 if(a['command'])
	 {
	  var sh2 = new GShell();
	  sh2.sendCommand(a['command']);
	  return;
	 }

	 /* LOAD LAYER */
	 if(a['layer'])
	 {
	  var arguments = "";
	  var fieldargs = "fieldcount="+a['fields'].length;
	  for(var c=0; c < a['fields'].length; c++)
	  {
	   var field = a['fields'][c];
	   fieldargs+= "&f"+c+"id="+field['id'];
	   fieldargs+= "&f"+c+"title="+field['title'];
	   if(field['hidden'])
	    fieldargs+= "&f"+c+"hidden=1";
	   if(field['width'])
	    fieldargs+= "&f"+c+"width="+field['width'];
	   if(field['format'])
		fieldargs+= "&f"+c+"format="+field['format'];
	   if(field['decimals'])
		fieldargs+= "&f"+c+"decimals="+field['decimals'];
	   if(field['minwidth'])
		fieldargs+= "&f"+c+"minwidth="+field['minwidth'];
	   if(field['editable'])
		fieldargs+= "&f"+c+"editable=1";
	   if(field['autolink'])
		fieldargs+= "&f"+c+"autolink="+field['autolink'];
	   if(field['includeintototals'])
		fieldargs+= "&f"+c+"includeintototals=1";
	   if(field['subtitle'])
		fieldargs+= "&f"+c+"subtitle="+field['subtitle'];


	  }
	  arguments+= fieldargs;

	  var form = new HackTVForm("bluewidget", a['width'], a['height']);
	  form.LoadLayer(a['layer'],arguments,a);
	  form.Show(a['title']);
	 }
	}
 sh.sendCommand("hacktvsearch cmdexec -url `"+this.URL+"` "+q);
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVSearch.prototype.__createSearchWidget = function()
{
 this.searchWidget = document.createElement('DIV');
 this.searchWidget.className = "hacktvt-desktop-search-widget";

 this.searchWidget.onmouseover = function(){this.mouseIsOver=true;}
 this.searchWidget.onmouseout = function(){this.mouseIsOver=false;}

 this.searchWidget.innerHTML = "";
 this.searchWidget.style.visibility = "hidden";
 this.searchWidget.style.left = "1px";
 this.searchWidget.style.top = "1px";
 document.body.appendChild(this.searchWidget);

 this.searchWidget.Sections = new Array();
 this.searchWidget.SectionByTag = new Array();

 this.searchWidget.createSection = function(type, tag, title){
	 if(tag && this.SectionByTag[tag])
	  return this.SectionByTag[tag];

	 if(!tag)
	  tag = "sec"+(this.Sections.length+1);

	 var secDiv = document.createElement('DIV');
	 secDiv.className = "hacktvsearch-section";
	 secDiv.id = "hacktvsearch-section-"+tag;
	 var html = "<div class='hacktvsearch-title'>";
     switch(type)
     {
      case 'search' : html+= "<img src='"+ABSOLUTE_URL+"var/objects/hacktvsearch/img/search.png'/>"; break;
      case 'command' : html+= "<img src='"+ABSOLUTE_URL+"var/objects/hacktvsearch/img/gear.png'/>"; break;
	  case 'function' : html+= "<img src='"+ABSOLUTE_URL+"var/objects/hacktvsearch/img/function.png'/>"; break;
     }
     html+= " "+title+"</div>";
	 secDiv.innerHTML = html;
	 secDiv.container = document.createElement('DIV');
	 secDiv.appendChild(secDiv.container);

	 secDiv.empty = function(){
		 this.container.innerHTML = "";
		 this.style.display = "none";
		}

	 secDiv.isEmpty = function(){
		 return (this.container.innerHTML != "") ? false : true;
		}

	 secDiv.createItemVoid = function(html){
		 var div = document.createElement('DIV');
		 div.className = "hacktvsearch-item";
		 if(html)
		  div.innerHTML = html;
		 this.container.appendChild(div);
		 this.style.display = "";
		 return div;
		}

	 this.Sections.push(secDiv);
	 this.SectionByTag[tag] = secDiv;

	 this.appendChild(secDiv);
	 return secDiv;
	}


 this.searchWidget.emptySections = function(){
	 for(var c=0; c < this.Sections.length; c++)
	  this.Sections[c].empty();
	}

 this.searchWidget.emptySection = function(tag){
	 if(this.SectionByTag[tag])
	  this.SectionByTag[tag].empty();
	}

 this.searchWidget.hide = function(force){
	 if(this.mouseIsOver && !force)
	  return;
 
	 if(force)
	 {
	  this.style.visibility = "hidden"; 
	  return;
	 }

	 /*for(var c=0; c < this.Sections.length; c++)
	 {
	  if(!this.Sections[c].isEmpty())
	   return;
	 }*/

	 this.style.visibility = "hidden";
	}

}
//-------------------------------------------------------------------------------------------------------------------//
HackTVSearch.prototype.__createDebugWidget = function()
{
 this.debugWidget = document.createElement('DIV');
 this.debugWidget.className = "hacktvsearch-debug-widget";

 this.debugWidget.onmouseover = function(){this.mouseIsOver=true;}
 this.debugWidget.onmouseout = function(){this.mouseIsOver=false;}

 this.debugWidget.innerHTML = "&nbsp;";
 this.debugWidget.style.visibility = "hidden";
 this.debugWidget.style.left = "300px";
 this.debugWidget.style.top = "1px";
 document.body.appendChild(this.debugWidget);
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVSearch.prototype._getScreenWidth = function()
{
 if(window.innerWidth)
  return window.innerWidth;
 else if(document.all)
  return document.body.clientWidth;
 return 0;
}
//-------------------------------------------------------------------------------------------------------------------//
HackTVSearch.prototype._getScreenHeight = function()
{
 if(window.innerHeight)
  return window.innerHeight;
 else if(document.all)
  return document.body.clientHeight;
 return 0;
}
//-------------------------------------------------------------------------------------------------------------------//

