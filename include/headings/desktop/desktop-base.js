/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-04-2013
 #PACKAGE: gnujiko-desktop-base
 #DESCRIPTION: Official Gnujiko Desktop - base package
 #VERSION: 2.2beta
 #TODO:
 #CHANGELOG: 15-04-2013 : Integrato con HackTVSearch.
			 30-11-2012 : Body onunload function added.
 
*/

function gnujikodesktopbaseOnLoad()
{
 var gdac = document.getElementById('gnujikodesktop-appscontainer');
 gdac.style.width = gdac.parentNode.offsetWidth-100;
 gdac.style.display = "";
 new HackTVSearch(document.getElementById('hacktvsearch'),-26);
 bodyOnLoad();
}

function gnujikodesktopbaseOnUnload()
{
 if(typeof(desktopOnUnload) == "function")
  desktopOnUnload();
}

function gnujikodesktopbaseRunCommand(command)
{
 if(!command)
  return;
 var sh = new GShell();
 sh.OnPreOutput = function(){}
 sh.sendCommand(command);
}

function gnujikodesktopbaseRunSudoCommand(command)
{
 if(!command)
  return;
 var sh = new GShell();
 /* sh.OnPreOutput = function(){} <--- se lo lascio da problemi */
 sh.sendSudoCommand(command);
}

function gnujikodesktopHDRBtnPrior()
{
 var gdac = document.getElementById('gnujikodesktop-appscontainer');
 gdac.scrollLeft = 0;
 var btnNext = document.getElementById('gnujikodesktop-hdrbtnnext');
 btnNext.innerHTML = "&raquo;";
 btnNext.onclick = function(){gnujikodesktopHDRBtnNext();}
}

function gnujikodesktopHDRBtnNext()
{
 var gdac = document.getElementById('gnujikodesktop-appscontainer');
 gdac.scrollLeft = (gdac.scrollWidth - gdac.offsetWidth);
 var btnNext = document.getElementById('gnujikodesktop-hdrbtnnext');
 btnNext.innerHTML = "&laquo;";
 btnNext.onclick = function(){gnujikodesktopHDRBtnPrior();}
}
