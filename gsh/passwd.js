/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2010 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 06-01-2010
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Change the user's password
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function shell_passwd(args, sessid, sh)
{
 for(var c=0; c < args.length; c++)
  switch(args[c])
  {
   default : var userName = args[c]; break;
  }

 shell_passwd_checkUser(userName ? userName : sh.Username, sh, sessid);
}

function shell_passwd_checkUser(userName, sh, sessid)
{
 var hHR = new GHTTPRequest();
 hHR.OnHttpRequest = function(reqObj){
	 var response = reqObj.responseXML.documentElement;
	 var request = response.getElementsByTagName('request')[0];
	 if(request.getAttribute('result') == "false")
	  return sh.print(request.getAttribute('msg'));
	 if(request.getAttribute('passwd') == "disabled")
	  return sh.print("The password for this user is disabled! You cannot change it");
	 if(request.getAttribute('passwd') != "blank")
	  sh.input("Type old password for "+userName,function(pW){shell_passwd_typeOldPasswd(userName, sh, sessid, pW);},'PASSWORD');
	}
 hHR.send(BASE_PATH+"./etc/passwd_httprequest.php?request=checkuser&sessid="+sessid+"&username="+userName);
}

function shell_passwd_typeOldPasswd(userName, sh, sessid, password)
{
 var hHR = new GHTTPRequest();
 hHR.OnHttpRequest = function(reqObj){
	 var response = reqObj.responseXML.documentElement;
	 var request = response.getElementsByTagName('request')[0];
	 if(request.getAttribute('result') == "false")
	  return sh.print(request.getAttribute('msg'));
	 shell_passwd_typeNewPasswd(userName, sh, sessid);
	}
 hHR.send(BASE_PATH+"./etc/passwd_httprequest.php?request=validateoldpasswd&sessid="+sessid+"&username="+userName+"&passwd="+password);
}

function shell_passwd_typeNewPasswd(userName, sh, sessid)
{
 sh.input("Type new password for "+userName,function(pW){shell_passwd_retypeNewPasswd(userName, sh, sessid, pW);},'PASSWORD');
}

function shell_passwd_retypeNewPasswd(userName, sh, sessid, password)
{
 sh.input("Retype password",function(pW){
	 if(password != pW)
	  return sh.print("Passwords do not match");
	 shell_passwd_storeNewPasswd(userName, sh, sessid, password);
	},'PASSWORD');
}

function shell_passwd_storeNewPasswd(userName, sh, sessid, password)
{
 var hHR = new GHTTPRequest();
 hHR.OnHttpRequest = function(reqObj){
	 var response = reqObj.responseXML.documentElement;
	 var request = response.getElementsByTagName('request')[0];
	 if(request.getAttribute('result') == "false")
	  return sh.print(request.getAttribute('msg'));
	 sh.print("Done!, password has been changed.");
	}
 hHR.send(BASE_PATH+"./etc/passwd_httprequest.php?request=changepasswd&sessid="+sessid+"&username="+userName+"&passwd="+password);
}

