/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 16-02-2014
 #PACKAGE: cron
 #DESCRIPTION: 
 #VERSION: 2.1beta
 #CHANGELOG: 16-02-2014 : Bug fix on function _save()
			 16-08-2013 : Bug fix nella funzione _saveNew()
			 23-08-2012 : Bug fix in function _saveNew()
			 23-03-2012 : Modificato gbox con gframe.
			 14-03-2012 : Bug fix with gframe.
			 20-11-2011 : Aggiustamenti vari
 #TODO:
 
*/

var IS_RECURRENCE = false;

function bodyOnLoad()
{
 document.getElementById('title').onchange = function(){
	 document.getElementById('subtitle').innerHTML = this.value;
	}
 if(document.getElementById('isrecurrence').value == "1")
  IS_RECURRENCE = true;
 _repChange(document.getElementById('imode'));
}

function _abort()
{
 gframe_close();
}

function _showPage(page)
{
 var currentPage = null;
 var ul = document.getElementById('cron-tabs');
 var list = ul.getElementsByTagName('LI');
 for(var c=0; c < list.length; c++)
 {
  if(list[c].id == "cron-"+page+"-tab")
   list[c].className = "selected";
  else if(list[c].className == "selected")
  {
   currentPage = list[c].id.substr(8);
   currentPage = currentPage.substr(0,currentPage.length-4);
   list[c].className = "";
  }
 }
 document.getElementById("cron-"+currentPage+"-page").style.display='none';
 document.getElementById("cron-"+page+"-page").style.display='';
}

function _deleteEvent()
{
 var ap = document.getElementById('ap').value;
 var id = document.getElementById('id').value;
 var from = document.getElementById('from').value;

 if(IS_RECURRENCE)
 {
  var sh = new GShell();
  sh.OnOutput = function(o,a){
	 if(!a) return;
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){
		 
		 gframe_close("",true);
		}
	 switch(a)
	 {
	  case 'this' : sh2.sendCommand("cron delete-recurrence -ap `"+ap+"` -id `"+id+"` -from `"+from+"`"); break;
	  case 'all' : sh2.sendCommand("cron delete-recurrence -ap `"+ap+"` -id `"+id+"` -all -from `"+from+"`"); break;
      case 'subsequent' : sh2.sendCommand("cron delete-recurrence -ap `"+ap+"` -id `"+id+"` -subsequent -from `"+from+"`"); break;
	 }
	}
  sh.sendCommand("gframe -f cronrecurrence.deleteask");
 }
 else
 {
  if(!confirm("Sei sicuro di voler eliminare questo evento?"))
   return;
  var sh = new GShell();
  sh.OnOutput = function(o,a){
	 var a = new Array();
	 a['action'] = "delete-event";
	 a['id'] = id;
	 a['ap'] = ap;
	 gframe_close("",a);
	}
  sh.sendCommand("cron delete-event -ap `"+ap+"` -id `"+id+"`");
 }
}

function _repChange(sel)
{
 switch(sel.value)
 {
  case '0' : {
	 document.getElementById('p0').style.display='none';
	 document.getElementById('p1').style.display='none';
	 document.getElementById('p2').style.display='none';
	 document.getElementById('p3').style.display='none';
	 document.getElementById('p4').style.display='none';
	 document.getElementById('notes').style.width = "100%";
	} break;
  case '1' : {
	 document.getElementById('p0').style.display='';
	 document.getElementById('repspan').innerHTML = "giorni";
	 document.getElementById('p1').style.display='none';
	 document.getElementById('p2').style.display='none';
	 document.getElementById('p3').style.display='';
	 document.getElementById('p4').style.display='';
	 document.getElementById('notes').style.width = 360;
	} break;
  case '2' : {
	 document.getElementById('p0').style.display='';
	 document.getElementById('repspan').innerHTML = "settimane";
	 document.getElementById('p1').style.display='';
	 document.getElementById('p2').style.display='none';
	 document.getElementById('p3').style.display='';
	 document.getElementById('p4').style.display='';
	 document.getElementById('notes').style.width = 360;
	} break;
  case '3' : {
	 document.getElementById('p0').style.display='';
	 document.getElementById('repspan').innerHTML = "mesi";
	 document.getElementById('p1').style.display='none';
	 document.getElementById('p2').style.display='';
	 document.getElementById('p3').style.display='';
	 document.getElementById('p4').style.display='';
	 document.getElementById('notes').style.width = 360;
	} break;
  case '4' : {
	 document.getElementById('p0').style.display='';
	 document.getElementById('repspan').innerHTML = "anni";
	 document.getElementById('p1').style.display='none';
	 document.getElementById('p2').style.display='none';
	 document.getElementById('p3').style.display='';
	 document.getElementById('p4').style.display='';
	 document.getElementById('notes').style.width = 360;
	} break;
 }
}

function _save()
{
 var ap = document.getElementById('ap').value;
 var id = document.getElementById('id').value;
 var title = document.getElementById('title').value;
 var notes = document.getElementById('notes').value;
 var dtFrom = new Date();
 var dtTo = new Date();
 dtFrom.setFromISO(strdatetime_to_iso(document.getElementById('datefrom').value+" "+document.getElementById('timefrom').value));
 dtTo.setFromISO(strdatetime_to_iso(document.getElementById('dateto').value+" "+document.getElementById('timeto').value));
 var allDay = document.getElementById('allday').checked ? 1 : 0;
 var imode = document.getElementById('imode').value;
 var freq = document.getElementById('frequency').value;

 var startDate = new Date();
 var endDate = new Date();

 if(!document.getElementById('startdate').value)
  startDate.setTime(dtFrom.getTime());
 else
  startDate.setFromISO(strdatetime_to_iso(document.getElementById('startdate').value));
 if(!document.getElementById('enddate').value)
  endDate.setTime(dtTo.getTime());
 else
  endDate.setFromISO(strdatetime_to_iso(document.getElementById('enddate').value));
 
 var qry = " -from `"+dtFrom.printf('Y-m-d H:i')+"` -to `"+dtTo.printf('Y-m-d H:i')+"` -name `"+title+"` -notes `"+notes+"` -allday `"+allDay+"`";
 var xqry = "";
 if(imode != "0")
 {
  xqry = " -imode `"+imode+"` -freq `"+freq+"` -start `"+startDate.printf('Y-m-d')+"`";
  if(!document.getElementById('infinite').checked)
   xqry+= " -end `"+endDate.printf('Y-m-d')+"`";
  else
   xqry+= " -end 0";
  switch(imode)
  {
   case '2' : {
	 var dayFlag = 0;
	 for(var c=0; c < 7; c++)
	  dayFlag+= (document.getElementById('day_'+c).checked) ? Math.pow(2,c) : 0;
	 xqry+= " -dayflag "+dayFlag;
	} break;
   case '3' : {
	 if(document.getElementById('dayOfMonth').checked)
	  xqry+= " -daynum "+dtFrom.getDate();
	 else
	  xqry+= " -daypos "+Math.floor(dtFrom.getDate()/7)+" -dayflag "+Math.pow(2,dtFrom.getDay());
	} break;
  }
 }


 if(IS_RECURRENCE)
 {
  var sh = new GShell();
  sh.OnOutput = function(o,a){
	 gframe_close("",true);
	}
  if(imode == "0")
   sh.sendCommand("cron recurrence2event -ap `"+ap+"` -id `"+id+"`"+qry);
  else
   sh.sendCommand("cron edit-recurrence -ap `"+ap+"` -id `"+id+"`"+qry+xqry);
 }
 else
 {
  var sh = new GShell();
  sh.OnOutput = function(o,a){
	 gframe_close("",true);
	}
  if(imode == "0")
   sh.sendCommand("cron edit-event -ap `"+ap+"` -id `"+id+"`"+qry);
  else
   sh.sendCommand("cron event2recurrence -ap `"+ap+"` -id `"+id+"`"+qry+xqry);
 }
}

function _saveNew()
{
 var ap = document.getElementById('ap').value;
 var refid = document.getElementById('refid').value;
 var title = document.getElementById('title').value;
 var notes = real_htmlspecialchars(document.getElementById('notes').value, "ENT_QUOTES");
 var dtFrom = new Date();
 var dtTo = new Date();
 dtFrom.setFromISO(strdatetime_to_iso(document.getElementById('datefrom').value+" "+document.getElementById('timefrom').value));
 dtTo.setFromISO(strdatetime_to_iso(document.getElementById('dateto').value+" "+document.getElementById('timeto').value));
 var allDay = document.getElementById('allday').checked ? 1 : 0;
 var imode = document.getElementById('imode').value;
 var freq = document.getElementById('frequency').value;

 var startDate = new Date();
 var endDate = new Date();

 if(!document.getElementById('startdate').value)
  startDate.setTime(dtFrom.getTime());
 else
  startDate.setFromISO(strdatetime_to_iso(document.getElementById('startdate').value));
 if(!document.getElementById('enddate').value)
  endDate.setTime(dtTo.getTime());
 else
  endDate.setFromISO(strdatetime_to_iso(document.getElementById('enddate').value));
 
 if(refid && (refid != "0"))
  var qry = "dynarc edit-item -ap `"+ap+"` -id `"+refid+"`";
 else
  var qry = "dynarc new-item -ap `"+ap+"` -name `"+title+"` -desc `"+notes+"`"; 
 
 if(imode == "0") // Create new event //
  qry+= " -extset `cronevents.from='"+dtFrom.printf('Y-m-d H:i')+"',to='"+dtTo.printf('Y-m-d H:i')+"',notes='"+notes+"',allday='"+allDay+"'`";
 else
 {
  qry+= " -extset `cronrecurrence.from='"+dtFrom.printf('Y-m-d H:i')+"',to='"+dtTo.printf('Y-m-d H:i')+"',notes='"+notes+"',allday='"+allDay+"'";
  qry+= ",imode='"+imode+"',freq='"+freq+"',startdate='"+startDate.printf('Y-m-d')+"'";
  if(!document.getElementById('infinite').checked)
   qry+= ",enddate='"+endDate.printf('Y-m-d')+"'";
  else
   qry+= ",enddate='0'";
  switch(imode)
  {
   case '2' : {
	 var dayFlag = 0;
	 for(var c=0; c < 7; c++)
	  dayFlag+= (document.getElementById('day_'+c).checked) ? Math.pow(2,c) : 0;
	 qry+= ",dayflag='"+dayFlag+"'";
	} break;
   case '3' : {
	 if(document.getElementById('dayOfMonth').checked)
	  qry+= ",daypos='"+Math.floor(dtFrom.getDate()/7)+"',dayflag='"+Math.pow(2,dtFrom.getDay())+"'";
	 else
	  qry+= ",daynum='"+dtFrom.getDate()+"'";
	} break;
  }
  qry+= "`";
 }

 var sh = new GShell();
 sh.OnError = function(errCode,msg){alert(msg);}
 sh.OnOutput = function(o,a){
	 gframe_close(o,a);
	}
 sh.sendCommand(qry);
}

