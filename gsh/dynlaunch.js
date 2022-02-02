/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-11-2012
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Launcher for Dynarc
 #VERSION: 2.1beta
 #CHANGELOG: 05-11-2012 : Bug fix.
			 05-05-2012 : Enabled launch by url.
			 04-02-2012 : Bug fix.
			 
 #TODO: 
 
*/

function shell_dynlaunch(args, sessid, sh)
{
 var useCacheContents = false;
 for(var c=0; c < args.length; c++)
  switch(args[c])
  {
   case '-ap' : { var ap = args[c+1]; c++;} break;
   case '-id' : { var id = args[c+1]; c++;} break;
   case '-params' : { var params = args[c+1]; c++;} break;
   case '-alt' : {var alternative = args[c+1]; c++;} break;
  }

 var sh2 = new GShell(); 
 sh2.OnOutput = function(_o,_a){
	 var sh3 = new GShell();
	 sh3.OnOutput = function(o,a){sh.output(o,a);}
	 sh3.OnError = function(e,s){sh.error(e,s);}
	 sh3.OnFinish = function(o,a){sh.finish(o,a);}
	 if(_a['launcher'])
	 {
	  var launcherCmd = _a['launcher'];
	  if(launcherCmd.substr(0,4) == "url:")
	  {
	   window.open(ABSOLUTE_URL+launcherCmd.substr(4).replace("%d",id), "_dyn-"+ap+"-"+id);
	   sh3.finish();
	  }
	  else
	   sh3.sendCommand(_a['launcher'].replace("%d",id)+(params ? " "+params : ""));
	 }
	 else if(alternative)
	 {
	  switch(alternative)
	  {
	   case 'returnerror' : sh3.error("NO_LAUNCHER_FOUND", "No launcher found for archive "+ap+"."); break;
	   case 'return' : sh3.finish("No launcher found for archive "+ap+". Return by user."); break;
	  }
	 }
	 else
	 {
	  sh3.sendCommand("dynarc item-info -ap "+ap+" -id "+id+" || parserize -p documents -params ap="+ap+"&id="+id+" -c *.desc | gframe -f preview --use-cache-contents -c");
	 }
	}
 sh2.sendCommand("dynarc archive-info -prefix `"+ap+"`");
}

