/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 04-05-2017
 #PACKAGE: gnujiko-template
 #DESCRIPTION: SERP Object
 #VERSION: 2.1beta
 #CHANGELOG: 04-05-2017 : refresh message added on function reload.
 #TODO:
 
*/

//-------------------------------------------------------------------------------------------------------------------//
function SERP(orderBy, orderMethod, rpp, pg)
{
 this.OrderBy = orderBy ? orderBy : "";
 this.OrderMethod = orderMethod ? orderMethod : "ASC";
 this.RPP = rpp ? parseInt(rpp) : 0;
 this.PG = pg ? parseInt(pg) : 1;
 this.SH = new GShell();

 this.resultsFrom = 0;
 this.resultsCount = 0;
 this.histName = "";

 this.URLVARS = this.getVars();

 /* EVENTS */
 this.OnChange = null;

 /* INIT */
 // Preload message image
 var img = new Image();
 img.src = ABSOLUTE_URL+"share/images/bigroundloading.gif";
}
//-------------------------------------------------------------------------------------------------------------------//
SERP.prototype.setVar = function(variable, value)
{
 this.URLVARS[variable] = value;
}
//-------------------------------------------------------------------------------------------------------------------//
SERP.prototype.unsetVar = function(variable)
{
 delete this.URLVARS[variable];
}
//-------------------------------------------------------------------------------------------------------------------//
SERP.prototype.getVars = function() 
{
 var vars = {};
 var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value){vars[key] = value;});
 return vars;
}
//-------------------------------------------------------------------------------------------------------------------//
SERP.prototype.setRPP = function(rpp, updateLabel)
{
 this.RPP = parseInt(rpp);

 if(updateLabel)
 {
  var label = document.getElementById(this.histName+"-serpstring");
  var from = this.resultsFrom+1;
  var to = this.resultsFrom + this.RPP;
  var count = label.parentNode.getAttribute('count');
  if(to > count)
  {
   to = count;
   /* Disable next button */
   var tmp = label.parentNode.getElementsByTagName('LI');
   var lastLI = tmp[tmp.length-1];
   lastLI.className = "last disabled";
  }
  else
  {
   /* Enable next button */
   var tmp = label.parentNode.getElementsByTagName('LI');
   var lastLI = tmp[tmp.length-1];
   lastLI.className = "last";
  }
  if(label) label.innerHTML = from+"-"+to+" di "+count;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
SERP.prototype.setCount = function(value, updateLabel)
{
 this.resultsCount = parseInt(value);
 if(updateLabel)
 {
  var label = document.getElementById(this.histName+"-serpstring");
  var from = this.resultsFrom+1;
  var to = this.resultsFrom + this.RPP;
  var count = this.resultsCount;
  label.parentNode.setAttribute('count',count);
  if(to > count)
  {
   to = count;
   /* Disable next button */
   var tmp = label.parentNode.getElementsByTagName('LI');
   var lastLI = tmp[tmp.length-1];
   lastLI.className = "last disabled";
  }
  else
  {
   /* Enable next button */
   var tmp = label.parentNode.getElementsByTagName('LI');
   var lastLI = tmp[tmp.length-1];
   lastLI.className = "last";
  }
  if(label) label.innerHTML = from+"-"+to+" di "+count;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
SERP.prototype.reload = function(setPg, messageTitle, messageContent)
{
 if((typeof(setPg) == "number") && (setPg == 0))
  this.PG = 1;
 else if(setPg == -1)
  this.PG = parseInt(this.PG)-1;
 else if(setPg == 1)
  this.PG = parseInt(this.PG)+1;

 var href = document.location.href;
 var startHREF = href;
 var x = href.indexOf("?");
 if(x > -1)
 {
  startHREF = href.substr(0,x);
 }

 this.URLVARS['sortby'] = this.OrderBy;
 this.URLVARS['sortmethod'] = this.OrderMethod;
 this.URLVARS['rpp'] = this.RPP;
 this.URLVARS['pg'] = this.PG;

 var endHREF = "";
 for(var k in this.URLVARS)
  endHREF+= "&"+k+"="+escape(decodeURIComponent(this.URLVARS[k]));
 if(endHREF) endHREF = endHREF.substr(1);

 if(messageTitle)
 {
  var msgTitle = (typeof(messageTitle) == "string") ? messageTitle : "Aggiornamento in corso...";
  var msgContent = messageContent ? messageContent : "Attendere prego, &egrave; in corso l&lsquo;aggiornamento dei dati.";
  this.SH.showProcessMessage(msgTitle, msgContent);
 }

 document.location.href = startHREF+"?"+endHREF;
}
//-------------------------------------------------------------------------------------------------------------------//
SERP.prototype.onPriorBtnClick = function(li, serpHistName)
{
 if(li.className == "first disabled")
  return;

 var oThis = this;
 if(this.OnChange)
 {
  var ul = li.parentNode;
  this.PG--;
  var retCount = ul.getAttribute('count');
  var retFrom = ((this.PG-1)*this.RPP)+1;
  var retTo = retFrom + this.RPP - 1;
  if(retTo > retCount) retTo = retCount;
  this.resultsFrom = retFrom-1;
  var retLimit = (this.PG > 1) ? (retFrom-1)+","+this.RPP : this.RPP;

  var ret = {from: retFrom, to: retTo, rpp: this.RPP, count: retCount, limit: retLimit, orderby: ul.getAttribute('orderby'), ordermethod: ul.getAttribute('ordermethod')}
  
  this.OnChange(ret);

  var label = document.getElementById(serpHistName+"-serpstring");
  if(label) label.innerHTML = retFrom+"-"+retTo+" di "+retCount;

  if(retCount > retTo)
  {
   /* Enable next btn */
   var tmp = ul.getElementsByTagName('LI');
   var lastLI = tmp[tmp.length-1];
   if(lastLI.className == "last disabled")
   {
    lastLI.className = "last";
    lastLI.onclick = function(){oThis.onNextBtnClick(this, serpHistName);}
   }
  }
  if(retFrom == 1)
  {
   /* Disable prior button */
   li.className = "first disabled";
  }
 }
 else
  this.reload(-1);
}
//-------------------------------------------------------------------------------------------------------------------//
SERP.prototype.onNextBtnClick = function(li, serpHistName)
{
 if(li.className == "last disabled")
  return;

 var oThis = this;
 if(this.OnChange)
 {
  var ul = li.parentNode;
  this.PG++;
  var retCount = ul.getAttribute('count');
  var retFrom = ((this.PG-1)*this.RPP)+1;
  var retTo = retFrom + this.RPP - 1;
  if(retTo > retCount) retTo = retCount;
  this.resultsFrom = retFrom-1;
  var retLimit = (this.PG > 1) ? (retFrom-1)+","+this.RPP : this.RPP;

  var ret = {from: retFrom, to: retTo, rpp: this.RPP, count: retCount, limit: retLimit, orderby: ul.getAttribute('orderby'), ordermethod: ul.getAttribute('ordermethod')}
  
  this.OnChange(ret);

  var label = document.getElementById(serpHistName+"-serpstring");
  if(label) label.innerHTML = retFrom+"-"+retTo+" di "+retCount;

  /* Enable prior btn */
  var tmp = ul.getElementsByTagName('LI');
  var firstLI = tmp[tmp.length-2];
  if(firstLI.className == "first disabled")
  {
   firstLI.className = "first";
   firstLI.onclick = function(){oThis.onPriorBtnClick(this, serpHistName);}
  }

  if(retTo == retCount)
  {
   /* Disable next button */
   li.className = "last disabled";
  }
 }
 else
  this.reload(1);
}
//-------------------------------------------------------------------------------------------------------------------//

