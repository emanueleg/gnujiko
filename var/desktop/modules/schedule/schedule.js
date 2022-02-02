/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 27-02-2013
 #PACKAGE: schedule-module
 #DESCRIPTION: Schedule module for Gnujiko Desktop.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function schedulemodule_load(modId)
{
 var module = document.getElementById(modId);
 module.plugByName['setdate'].oninput = function(data, mod, plug){
	 schedulemodule_update(data,this.module.id);
	}
}

function schedulemodule_prev(modId)
{
 var dateO = document.getElementById(modId+"-date");
 var date = new Date();
 date.setFromISO(dateO.getAttribute('date'));
 date.PrevMonth();
 schedulemodule_update(date, modId);
}

function schedulemodule_next(modId)
{
 var dateO = document.getElementById(modId+"-date");
 var date = new Date();
 date.setFromISO(dateO.getAttribute('date'));
 date.NextMonth();
 schedulemodule_update(date, modId);
}

function schedulemodule_update(date,modId)
{
 var today = new Date();
 var tmpdate = new Date();
 var totIncomes = 0;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 document.getElementById(modId+"-date").innerHTML = schedulemodule_i18n['MONTH-'+date.printf('m')]+" "+date.printf('Y');
	 document.getElementById(modId+"-date").setAttribute('date',date.printf('Y-m-01'));

	 var container = document.getElementById(modId+"-container");
	 if(!a)
	 {
	  container.innerHTML = "&nbsp;";
	  document.getElementById(modId+"-ndocs").innerHTML = "0";
	  document.getElementById(modId+"-totamount").innerHTML = "&euro; 0,00";
	  return;
	 }

	 
	 var html = "";
	 for(var c=0; c < a.length; c++)
	 {
	  var item = a[c];
	  html+= "<div class='schedule-item'><div class='schedule-item-header'>";
	  tmpdate.setFromISO(item['expire_date'])
	  html+= "<span style='color:"+(tmpdate.getTime() < today.getTime() ? "#b50000" : "#013397")+"'>"+tmpdate.printf('d/m/Y')+"</span>";
	  var amount = item['incomes'] ? parseFloat(item['incomes']) : 0;
	  html+= "<span style='float:right'>&euro; "+formatCurrency(amount,2)+"</span></div>";
	  html+= "<span class='schedule-smalltext'><i>cliente:</i></span>";
	  html+= "<div class='schedule-section'><b>"+item['subject_name']+"</b></div>";
	  html+= "<span class='schedule-smalltext'><i>doc. di riferimento:</i></span>";
	  html+= "<div class='schedule-section'><a href='"+ABSOLUTE_URL+"GCommercialDocs/docinfo.php?id="+item['doc_id']+"' target='GCD-"+item['doc_id']+"'>"+item['doc_name']+"</a></div>";
	  html+= "<span class='schedule-smalltext'><i>modalit&agrave; di pagamento:</i></span>";
	  html+= "<div class='schedule-section'><b>"+item['payment_mode_name']+"</b></div>";
	  html+= "</div>";
	  totIncomes+= amount;
	 }
	 container.innerHTML = html ? html : "&nbsp;";	 
	 document.getElementById(modId+"-ndocs").innerHTML = a.length;
	 document.getElementById(modId+"-totamount").innerHTML = "&euro; "+formatCurrency(totIncomes,2);
	}
 sh.sendCommand("mmr schedule -from `"+date.printf('Y-m')+"-01`");
}

