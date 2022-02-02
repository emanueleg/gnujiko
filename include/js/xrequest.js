/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 02-03-2012
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Provide eXtraRequest
 #VERSION: 2.1beta
 #CHANGELOG: Bug fix on error.
 #TODO:
 
*/

function GHTTPRequest()
{
 this.SessionID = 0;

 // EVENTS //
 this.OnHttpRequest = null;
 this.OnError = null;
}

GHTTPRequest.prototype.send = function(urlanddata)
{
 var url = urlanddata.substr(0,urlanddata.indexOf('?'));
 var data = urlanddata.substr(urlanddata.indexOf('?')+1);
 var oThis = this;
 data = data.replace(/\%/g,"%25");
 data+= "&httprequestsessid="+this.SessionID;
 var request = this.createRequestObject();
 request.onreadystatechange = function() {oThis.httpRequestCallback(request);};
 request.open("POST",url,true);
 request.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
 request.send(data);
}

GHTTPRequest.prototype.createRequestObject = function()
{
 var obj;
 if(window.XMLHttpRequest)
  obj = new XMLHttpRequest();
 else if(window.ActiveXObject)
  obj = new ActiveXObject("MSXML2.XMLHTTP");
 return obj; 
}

GHTTPRequest.prototype.checkReadyState = function(reqObj)
{
 if (reqObj.readyState == 4)
 {
  if(reqObj.status == 200)
   return true;
  else if(this.OnError)
   this.OnError();
 }
 return false;
}

GHTTPRequest.prototype.httpRequestCallback = function(request)
{
 if (this.checkReadyState(request))
 {
  if(this.OnHttpRequest)
   this.OnHttpRequest(request);
 }
}

