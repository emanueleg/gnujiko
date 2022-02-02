/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-02-2013
 #PACKAGE: apm-gui
 #DESCRIPTION: Package manager with graphic user interface.
 #VERSION: 2.1beta
 #CHANGELOG: 05-02-2013 : Bug fix on check depends.
 #TODO:
 
*/

//-------------------------------------------------------------------------------------------------------------------//
function shell_apmgui(args, sessid, sh)
{
 for(var c=0; c < args.length; c++)
  switch(args[c])
  {
   case 'resolve' : return shell_apmgui_resolve(args, sessid, sh); break;
  }
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_apmgui_resolve(args, sessid, sh)
{
 var packages = new Array();
 var optionInstall = false;

 for(var c=1; c < args.length; c++)
  switch(args[c])
  {
   case '-package' : {packages.push(args[c+1]); c++;} break;
   case '-i' : optionInstall = true; break;
   default : packages.push(args[c]); break;
  }
 
 if(!packages.length)
  return sh.error("You must specify at least one package","INVALID_PACKAGE");
 
 var p = "";
 for(var c=0; c < packages.length; c++)
  p+= packages[c]+",";
 
 var sh2 = new GShell();
 sh2.SessionID = sessid;
 sh2.OnOutput = function(o,a){
	 if(a && optionInstall)
	 {
	  var params = "";
	  if(a['TO_BE_REMOVE'] && a['TO_BE_REMOVE'].length)
	  {
	   for(var c=0; c < a['TO_BE_REMOVE'].length; c++)
		params+= (c>0 ? "," : "&toberemove=")+a['TO_BE_REMOVE'][c];
	  }
	  if(a['OUTDATED'] && a['OUTDATED'].length)
	  {
	   for(var c=0; c < a['OUTDATED'].length; c++)
		params+= (c>0 ? "," : "&outdated=")+a['OUTDATED'][c];
	  }
	  if(a['AVAILABLE'] && a['AVAILABLE'].length)
	  {
	   for(var c=0; c < a['AVAILABLE'].length; c++)
		params+= (c>0 ? "," : "&available=")+a['AVAILABLE'][c];
	  }
	  if(a['execordering'])
	  {
	   var q = "";
	   for(var c=0; c < a['exec-ordering'].length; c++)
		q+= ","+a['exec-ordering'][c];
	   params+= "&execordering="+q.substr(1);
	  }

	  var sh3 = new GShell();
	  sh3.SessionID = sessid;
	  sh3.OnFinish = function(o,a){sh.finish(o);}
	  sh3.sendCommand("gframe -f apm.process --fullspace -params "+params.substr(1,params.length-1));
	 }
	 else
	  sh.finish(o,a);
	}
 sh2.sendCommand("gframe -f apm.resolve --fullspace -params packages="+(p.substr(0,p.length-1)));
}
//-------------------------------------------------------------------------------------------------------------------//

