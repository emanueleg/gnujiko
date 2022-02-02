/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2010 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 02-01-2010
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Perform user login
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function shell_login(args, sessid, sh)
{
 for(var c=0; c < args.length; c++)
  switch(args[c])
  {
   default : var userName = args[c]; break;
  }
 if(userName)
 {
  sh.input("Type password for "+userName,function(pW){shell_login_auth(sh,userName,pW ? pW : " ");},'PASSWORD');
 }
 else
  sh.input("Login",function(uN){shell_login_auth(sh,uN);});
}

function shell_login_auth(sh, userName, password)
{
 if(!password)
  return sh.input("Type password for "+userName,function(pW){shell_login_auth(sh,userName,pW ? pW : " ");},'PASSWORD');
 sh.login(userName,password);
}
