/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-02-2013
 #PACKAGE: calendar-module
 #DESCRIPTION: Calendar module for Gnujiko Desktop.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function calendarmodule_load(modId)
{
 var module = document.getElementById(modId);
}

function calendarmodule_prev(modId)
{
 var handle = document.getElementById(modId+"-handle");
 var date = new Date();
 date.setFromISO(handle.getAttribute('date'));
 date.PrevMonth();
 calendarmodule_update(date, modId);
}

function calendarmodule_next(modId)
{
 var handle = document.getElementById(modId+"-handle");
 var date = new Date();
 date.setFromISO(handle.getAttribute('date'));
 date.NextMonth();
 calendarmodule_update(date, modId);
}

function calendarmodule_update(date,modId)
{
 var today = new Date();
 var tmpdate = new Date();
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.getElementById(modId+"-handle").innerHTML = calendarmodule_i18n['MONTH-'+date.printf('m')]+" "+date.printf('Y');
	 document.getElementById(modId+"-handle").setAttribute('date',date.printf('Y-m-01'));

	 var tb = document.getElementById(modId+"-grid");
	 for(var r=0; r < a.length; r++)
	 {
	  var week = a[r];
	  for(var c=0; c < 7; c++)
	  {
	   var td = tb.rows[r].cells[c];
	   tmpdate.setFromISO(week['dates'][c]);
	   if(week['dates'][c] == today.printf('Y-m-d'))
		td.className = "today";
	   else if(tmpdate.printf('m') != date.printf('m'))
		td.className = "out";
	   else
		td.className = "";
	   td.innerHTML = week['days'][c];
	  }
	 }

	 document.getElementById(modId).output("onchange",date);

	}
 sh.sendCommand("calendar print -month `"+date.printf('m')+"` -year `"+date.printf('Y')+"`");
}

