/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-03-2013
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Document list options layer.
 #VERSION: 2.3beta
 #CHANGELOG: 15-03-2013 : Aggiunta funzione docopt_confirm
			 06-03-2013 : Aggiunto tasto stampa.
 #TODO:
 
*/

function docopt_checkAvail(docId)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 // prenota gli articoli //
	 var sh2 = new GShell();
	 sh2.OnOutput = function(){document.location.reload();}
	 sh2.sendCommand("commercialdocs book-articles -id `"+docId+"`");
	}
 sh.sendCommand("gframe -f commercialdocs/checkavail -params `id="+docId+"`");
}

function docopt_confirm(docId)
{
 var sh = new GShell();
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("dynarc edit-item -ap commercialdocs -id `"+docId+"` -extset `cdinfo.status=3`");
}

function docopt_convert(docId, ct)
{
 var status = 0;
 switch(ct.toLowerCase())
 {
  case 'orders' : status=3; break;
 }

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 window.parent.document.location.href = ABSOLUTE_URL+"GCommercialDocs/docinfo.php?id="+a['id'];
	}
 sh.sendCommand("commercialdocs convert -id `"+docId+"` -type `"+ct+"`"+(status ? " -status "+status : ""));
}

function docopt_restoreStatus(docId, ct, status)
{
 if(!confirm("Questa procedura ripristinerà lo status del documento retrocedendo di un gradino alla volta. Procedere?"))
  return;
 switch(ct)
 {
  case 'preemptives' : {
	 switch(status)
	 {
	  case 0 : case 1 : case 2 : case 3 : status = 0; break;
	  default : status = 3; break;
	 }
	} break;

  case 'orders' : {
	 if(status > 7)
	  status = 7;
	 else
	  status = 0;
	} break;
 
  case 'ddt' : case 'invoices' : case 'vendororders' : case 'purchaseinvoices' : case 'agentinvoices' : case 'creditsnote' : case 'debitsnote' : case 'intervreports' : {
	 if(status >= 8)
	  status = 3;
	 else
	  status = 0;
	} break;
 }
 var sh = new GShell();
 sh.OnOutput = function(o,a){document.location.reload();}
 sh.sendCommand("dynarc edit-item -ap commercialdocs -id `"+docId+"` -extset `cdinfo.status="+status+"`");
}

function docopt_statusChange(docId, status, ct)
{
 if(status == 7)
 {
  switch(ct)
  {
   case 'orders' : case 'ddt' : case 'invoices' : case 'intervreports' : {
	 var sh = new GShell();
	 sh.OnOutput = function(){
		 var sh2 = new GShell();
		 sh2.OnOutput = function(o,a){document.location.reload();}
		 sh2.sendCommand("dynarc edit-item -ap commercialdocs -id `"+docId+"` -extset `cdinfo.status="+status+"`");
		}
	 sh.sendCommand("gframe -f commercialdocs/downloadstore -params `id="+docId+"`");
	 return;
	} break;
  }
 }
 
 var sh = new GShell();
 sh.OnOutput = function(o,a){document.location.reload();}
 sh.sendCommand("dynarc edit-item -ap commercialdocs -id `"+docId+"` -extset `cdinfo.status="+status+"`");
}

function docopt_downloadStore(docId,status)
{
 if(!status)
  status = 7;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){document.location.reload();}
	 sh2.sendCommand("dynarc edit-item -ap commercialdocs -id `"+docId+"` -extset `cdinfo.status="+status+"`");
	}
 sh.sendCommand("gframe -f commercialdocs/downloadstore -params `id="+docId+"`");
}

function docopt_paid(docId, isACost)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var date = new Date(parseFloat(a['ctime'])*1000);
	 var sh2 = new GShell();
	 sh2.OnOutput = function(){document.location.reload();}
	 sh2.sendCommand("dynarc edit-item -ap `commercialdocs` -id `"+docId+"` -extset `cdinfo.status=10,payment-date='"+date.printf('Y-m-d')+"'`");
	}
 sh.sendCommand("gframe -f commercialdocs/pay -params `id="+docId+"&desc=Saldo"+(isACost ? "&isdebit=true" : "")+"`");
}

function docopt_goodsDelivered(docId)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.reload();
	}
 sh.sendCommand("gframe -f commercialdocs/goodsdelivered -params `id="+docId+"`");
}

function docopt_printPreview(docId, catTag, docTitle, docStatus)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var status = docStatus;
	 if(status < 3)
	 {
	  switch(a['action'])
	  {
	   case 'PRINT' : {
		 var sh2 = new GShell();
		 sh2.sendCommand("dynarc edit-item -ap `commercialdocs` -id `"+docId+"` -extset `cdinfo.status=1`");
		} break;

	   case 'EXPORT' : case 'EMAIL' : {
		 var sh2 = new GShell();
		 sh2.sendCommand("dynarc edit-item -ap `commercialdocs` -id `"+docId+"` -extset `cdinfo.status=2`");
		} break;
	  }
	 }
	}
 sh.sendCommand("gframe -f print.preview -params `modelap=printmodels&modelct="+catTag+"&parser=commercialdocs&ap=commercialdocs&id="+docId+"` -title `"+docTitle+"`");
}

