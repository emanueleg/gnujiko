/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 06-01-2017
 #PACKAGE: pos-module
 #DESCRIPTION: POS - Module for Gnujiko Desktop.
 #VERSION: 2.11beta
 #CHANGELOG: 06-01-2017 : Bug fix ricerca per barcode.
			 12-11-2016 : Aggiunta possibilita di ridimensionare il modulo.
			 08-10-2015 : Aggiunto cod.art.prod. su campo di ricerca.
			 05-09-2014 : Aggiunta la possibilità di scegliere il tipo di documento da emettere e selezione del cliente.
			 19-02-2014 : Bug fix.
			 09-12-2013 : Fatto in modo che salvi su cartella predefinita dell'utente.
			 27-11-2013 : Aggiunta la modalità di stampa su file.
			 18-10-2013 : Bug fix.
			 07-09-2013 : Aggiunto fidelity card e listini ed altre aggiunte e modifiche varie.
 #TODO:
 
*/

function posmodule_load(modId)
{
 var module = document.getElementById(modId);
 module.itemsByBarcode = new Array();
 module.defaultVatRate = document.getElementById(modId+"-defaultvatrate").value;
 module.defaultVatId = document.getElementById(modId+"-defaultvatid").value;
 module.defaultVatType = document.getElementById(modId+"-defaultvattype").value;
 module.subjectInfo = null;

 var tb = new GMUTable(document.getElementById(modId+"-itemlist"));
 tb.modId = modId;
 tb.Options.autoaddrows = false;
 tb.Fields[3].enableSearch("dynarc search -ap `vatrates` -fields code_str,name `", "` -limit 5 --order-by `code_str ASC` -get percentage,vat_type", "percentage","name","items",true,"percentage");

 tb.OnBeforeAddRow = function(r){
	 var oThis = this;
	 r.cells[0].innerHTML = "<span class='graybold'></span>";
	 r.cells[1].innerHTML = "<span class='graybold 13 center'></span>";
	 r.cells[2].innerHTML = "<span class='graybold'></span>";
	 r.cells[3].innerHTML = "<span class='graybold'></span>";
	 r.cells[4].innerHTML = "<span class='graybold'></span>";
	 r.cells[5].innerHTML = "<span class='graybold'></span>";
	 r.cells[6].innerHTML = "<img src='"+ABSOLUTE_URL+"var/desktop/modules/pos/img/delete.png' style='cursor:pointer'/>";
	 r.cells[6].getElementsByTagName('IMG')[0].onclick = function(){oThis.DeleteRow(this.parentNode.parentNode.rowIndex);}
	}

 tb.OnCellEdit = function(r,cell,value,data){
	 switch(cell.tag.replace(this.modId+"-",""))
	 {
	  case 'unitprice' : posmodule_updateTotals(this.modId,r); break;
	  case 'qty' : {
		 if(r.data && r.data['unique'])
		 {
		  cell.setValue(1);
		  alert("Impossibile modificare la quantità per questo tipo di articolo");
		 }
		 else
		  posmodule_updateTotals(this.modId,r);
		} break;
	  case 'vat' : {
		 if(data)
		 {
		  r.setAttribute('vatid',data['id']);
		  r.setAttribute('vattype',data['vat_type']);
		  posmodule_updateTotals(this.modId,r);
		 }
		 else
		 {
		  var sh = new GShell();
		  sh.OnOutput = function(o,a){
			 if(!a)
			 {
		  	  r.setAttribute('vatid',0);
		  	  r.setAttribute('vattype',"");
			  posmodule_updateTotals(this.modId,r);
			  return;
			 }
			 r.setAttribute('vatid',a['items'][0]['id']);
			 r.setAttribute('vattype',a['items'][0]['vat_type']);
			 cell.setValue(a['items'][0]['percentage']);
			 posmodule_updateTotals(this.modId,r)
			}
		  sh.sendCommand("dynarc search -ap `vatrates` -fields code_str,name `"+value+"` -limit 1 --order-by `code_str ASC` -get percentage,vat_type");
		 }
		} break;

	  case 'discount' : {
		 if(value.indexOf("%") < 0)
		  cell.getElementsByTagName('SPAN')[0].innerHTML = "&euro;. "+formatCurrency(parseCurrency(value),2);
		 posmodule_updateTotals(this.modId,r);
		} break;
	  case 'total' : {
		 var totalPlusVat = parseCurrency(value);
		 var vatRate = parseFloat(r.cell[this.modId+"-vat"].getValue());
		 if(!vatRate) vatRate = 0;
		 var amount = (totalPlusVat/(100+vatRate))*100;
		 var qty = parseFloat(r.cell[this.modId+"-qty"].getValue());

		 var discountInc = 0;
		 var discountPerc = 0;
		 var discountStr = r.cell[this.modId+"-discount"].getValue();
		 if(discountStr.indexOf("%") > 0)
		  discountPerc = parseFloat(discountStr);
		 else
		  discountInc = parseCurrency(discountStr);

		 if(discountInc && amount)
		  amount-= discountInc*qty;
		 else if(discountPerc && amount)
		  amount = (amount/(100-discountPerc))*100;

		 var unitPrice = qty ? amount/qty : 0;

		 r.cell[this.modId+"-unitprice"].setValue(formatCurrency(unitPrice),unitPrice);
		 posmodule_updateTotals(this.modId,r);
		}
	 }
	}

 tb.OnDeleteRow = function(){posmodule_updateTotals(this.modId);}

 module.tb = tb;
 
/* var es = EditSearch.init(document.getElementById(modId+"-barcode"), "dynarc search -at 'gmart' -fields barcode,code_str,name `","` -get barcode --order-by `name ASC` -limit 10","id","name","items",true,"name",posmodule_queryresults);*/
 var es = EditSearch.init(document.getElementById(modId+"-barcode"), "pos search -ats 'gmart' -fields code_str,name,manufacturer_code `","` -get barcode --order-by `name ASC` -limit 10","id","name","items",true,"barcode",posmodule_queryresults);
 es.module = module;
 es.uniquebarcodes = new Array();

 es.remakeQuery = function(qry){
	 var oThis = this;
	 var sh = new GShell();
	 sh.OnError = function(err){alert(err);}
	 sh.OnOutput = function(o,a){
		 if(a['result'])
		  return oThis.queryFinish(a['result']);
		 else if(a['aboutbarcode'])
		 {
		  if(a['aboutbarcode']['ap'] == "lottomatica")
		  {
		   if(!confirm("Questo biglietto deriva da un altra ricevitoria, desideri pagare la vincita comunque?"))
			return;
		   var sh2 = new GShell();
		   sh2.OnError = function(err){alert(err);}
		   sh2.OnOutput = function(o,a){
				 if(a) 
				  alert("La vincita è stata registrata!"); 
				 else 
				  alert("La registrazione è stata annullata.");
				 oThis.value = "";
				 oThis.data = null;
				 oThis.focus();
				}
		   sh2.sendCommand("gframe -f lottomatica/register.winnings -params `refap="+a['aboutbarcode']['ap']+"&refid="+a['aboutbarcode']['id']+"&barcode="+qry+"`");
		  }
		 }
		 else if(a['results'] && a['results'].length)
		  return oThis.queryFinish(a['results'][0]);
		 else
		  alert("Articolo sconosciuto");
		}
	 sh.sendCommand("pos search -ats 'gmart' -fields code_str,name,manufacturer_code `"+qry+"` -get barcode --order-by `name ASC` -limit 10");
	}

 es.queryFinish = function(data){
	 var oThis = this;
	 var sh = new GShell();
	 sh.OnError = function(msg,errcode){alert(msg);}
	 sh.OnOutput = function(o,a){
		 if(data && data['pack_id'])
		 {
		  a['unique'] = true;
		  oThis.uniquebarcodes.push(data['fullbarcode'] ? data['fullbarcode'] : data['barcode']);
		  a['pack_id'] = data['pack_id'];
		  a['pack_item_id'] = data['id'];
		 }
		 posmodule_insert(modId,a);
		}

	 if(data && data['pack_id'] && data['refap'] && data['refid'])
	 {
	  if(data['status'] == "0")
	  {
	   switch(data['refap'])
	   {
		case 'lottomatica' : {
			 var sh = new GShell();
			 sh.OnError = function(err){alert(err);}
			 sh.OnOutput = function(o,a){
				 if(a) 
				  alert("La vincita è stata registrata!"); 
				 else 
				  alert("La registrazione è stata annullata.");
				 oThis.value = "";
				 oThis.data = null;
				 oThis.focus();
				}
			 sh.sendCommand("gframe -f lottomatica/register.winnings -params `refap="+data['refap']+"&refid="+data['refid']+"&id="+data['id']+"&barcode="+(data['fullbarcode'] ? data['fullbarcode'] : data['barcode'])+"`");
			} break;
	    default : alert("Articolo già prelevato quindi non più disponibile."); break;
	   }
	  }
	  else if(data['status'] == "2")
	  {
	   switch(data['refap'])
	   {
		case 'lottomatica' : {
			 alert("Questo biglietto risulta già venduto e incassato.");
			} break;
		default : alert("Articolo già prelevato quindi non più disponibile."); break;
	   }
	  }
	  else
	   sh.sendCommand("commercialdocs get-full-info -type gmart -ap `"+data['refap']+"` -id `"+data['refid']+"` -pricelistid `"+document.getElementById(modId+"-pricelist").value+"` -get barcode");
	 }
	 else if(data && data['ap'] && data['id'])
	  sh.sendCommand("commercialdocs get-full-info -type gmart -ap `"+data['ap']+"` -id `"+data['id']+"` -pricelistid `"+document.getElementById(modId+"-pricelist").value+"` -get barcode");
	 else
	  sh.sendCommand("commercialdocs get-full-info -type `gmart` --code-or-barcode `"+this.value+"` -pricelistid `"+document.getElementById(modId+"-pricelist").value+"` -get barcode");

	 this.value = "";
	 this.data = null;
	 this.focus();
	}

 es.onchange = function(){
	 if(!this.value && !this.data) return;
	 if(this.value && (this.uniquebarcodes.indexOf(this.value) > -1))
	  return alert("Questo articolo (barcode: "+this.value+") è già stato inserito.");
	 if(!this.data) return this.remakeQuery(this.value);
	 this.queryFinish(this.data);
	}

 var fc = EditSearch.init(document.getElementById(modId+"-fidelitycardcode"), "dynarc search -ap 'rubrica' -fields fidelitycard,name `","` -get `fidelitycard,pricelist_id` --order-by `name ASC` -limit 10","id","name","items",true,"fidelitycard",posmodule_fidelityqueryresults);
 fc.module = module;
 fc.onchange = function(){
	 if(!this.value) return;
	 var oThis = this;
	 var sh = new GShell();
	 sh.OnError = function(msg,errcode){
		 oThis.module.subjectInfo = null;
		 alert("Non esiste alcun cliente associato a questa fidelity card");
		}
	 sh.OnOutput = function(o,a){
		 if(!a)
		  return;
		 document.getElementById(modId+"-fidelitycardcustomer").innerHTML = a['name'];
		 document.getElementById(modId+"-fidelitycardcustomer-canc").style.display = "";
		 document.getElementById(modId+"-pricelist").value = a['pricelist_id'];
		 oThis.module.subjectInfo = a;
		}

	 if(this.data && this.data['id'])
	  sh.sendCommand("dynarc item-info -ap `rubrica` -id `"+this.data['id']+"` -get `fidelitycard,pricelist_id`");
	 else
	  sh.sendCommand("dynarc item-info -ap `rubrica` -where `fidelitycard='"+this.value+"'` -get `fidelitycard,pricelist_id`");
	 
	 document.getElementById(modId+"-barcode").focus();
	}

 var subjectEdit = EditSearch.init(document.getElementById(modId+"-subject"), "dynarc search -ap 'rubrica' -fields name `","` -get `fidelitycard,pricelist_id` --order-by `name ASC` -limit 10","id","name","items",true,"name");
 subjectEdit.module = module;
 subjectEdit.onchange = function(){
	 if(!this.value) return;
	 if(this.value && this.data)
	 {
	  document.getElementById(modId+"-fidelitycardcustomer").innerHTML = this.data['name'];
	  document.getElementById(modId+"-fidelitycardcustomer-canc").style.display = "";
	  document.getElementById(modId+"-pricelist").value = this.data['pricelist_id'];
	  this.module.subjectInfo = this.data;
	  document.getElementById(modId+"-barcode").focus();
	 }
	}

}

function posmodule_clearAll(modId)
{
 var module = document.getElementById(modId);
 module.itemsByBarcode = new Array();
 var tb = module.tb;

 while(tb.O.rows.length > 1)
  tb.DeleteRow(1);

 posmodule_updateTotals(modId);
 document.getElementById(modId+"-barcode").focus();
 document.getElementById(modId+"-subject").value = "";
 document.getElementById(modId+"-subject").data = null;
 document.getElementById(modId+"-barcode").uniquebarcodes = new Array();
 module.subjectInfo = null;
}

function posmodule_fidelitycanc(modId)
{
 var module = document.getElementById(modId);
 module.subjectInfo = null;
 document.getElementById(modId+"-fidelitycardcustomer").innerHTML = "";
 document.getElementById(modId+"-fidelitycardcustomer-canc").style.display = "none";
 var sel = document.getElementById(modId+"-pricelist");
 sel.value = sel.options[0].value;
 document.getElementById(modId+"-fidelitycardcode").value = "";
}

function posmodule_insert(modId, data)
{
 var module = document.getElementById(modId);
 var tb = module.tb

 if(!data['unique'])
 {
  for(var c=1; c < tb.O.rows.length; c++)
  {
   if(tb.O.rows[c].data && (tb.O.rows[c].data['tb_prefix'] == data['tb_prefix']) && (tb.O.rows[c].data['id'] == data['id']))
   {
    posmodule_incrementRow(tb.O.rows[c],module,modId);
    return;
   }
  }
 }

 var r = tb.AddRow();
 r.data = data;
 r.setAttribute('refap',data['tb_prefix']);
 r.setAttribute('refid',data['id']);
 r.setAttribute('vatid',data['vatid']);
 r.setAttribute('vattype',data['vattype']);

 r.cell[modId+"-description"].setValue(data['name']);
 r.cell[modId+"-qty"].setValue(1);
 r.cell[modId+"-unitprice"].setValue(data['finalprice']);
 r.cell[modId+"-vat"].setValue(data['vat']);

 var amount = parseFloat(data['finalprice']);
 var vatRate = parseFloat(data['vat']);
 var vat = (amount/100)*vatRate;
 var total = amount + vat;

 r.cell[modId+"-total"].setValue(formatCurrency(total),total);
 r.setAttribute('total',total);
 posmodule_updateTotals(modId);
}

function posmodule_manualinsert(modId)
{
 var module = document.getElementById(modId);
 var tb = module.tb;

 var r = tb.AddRow();
 r.setAttribute('refap',"");
 r.setAttribute('refid',"");
 r.setAttribute('vatid',module.defaultVatId);
 r.setAttribute('vattype',module.defaultVatType);
 r.setAttribute('vat',module.defaultVatRate);

 r.cell[modId+"-qty"].setValue(1);
 r.cell[modId+"-vat"].setValue(module.defaultVatRate);

 r.edit();
}

function posmodule_incrementRow(r,module,modId)
{
 r.cell[modId+"-qty"].setValue(parseFloat(r.cell[modId+"-qty"].getValue())+1);
 posmodule_updateTotals(modId,r);
}

function posmodule_updateTotals(modId, r)
{
 var module = document.getElementById(modId);
 var tb = module.tb;
 if(r)
 {
  var qty = parseFloat(r.cell[modId+"-qty"].getValue());
  var unitprice = parseCurrency(r.cell[modId+"-unitprice"].getValue());
  var discount = 0;
  var discountStr = r.cell[modId+"-discount"].getValue();
  if(discountStr.indexOf("%") > 0)
  {
   var disc = parseFloat(discountStr);
   discount = unitprice ? (unitprice/100)*disc : 0;
  }
  else
   discount = parseCurrency(discountStr);
  if(!discount)
   discount = 0;
  var vat = parseFloat(r.cell[modId+"-vat"].getValue());
  var total = (unitprice-discount) * qty;
  var totalPlusVat = total ? total + ((total/100)*vat) : 0;
  r.cell[modId+"-total"].setValue(formatCurrency(totalPlusVat), totalPlusVat);
 }

 var subtot = 0;
 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];
  var qty = parseFloat(r.cell[modId+"-qty"].getValue());
  var unitprice = parseCurrency(r.cell[modId+"-unitprice"].getValue());
  var discount = 0;
  var discountStr = r.cell[modId+"-discount"].getValue();
  if(discountStr.indexOf("%") > 0)
  {
   var disc = parseFloat(discountStr);
   discount = unitprice ? (unitprice/100)*disc : 0;
  }
  else
   discount = parseCurrency(discountStr);
  if(!discount)
   discount = 0;
  var vat = parseFloat(r.cell[modId+"-vat"].getValue());
  var total = (unitprice-discount) * qty;
  var totalPlusVat = total ? total + ((total/100)*vat) : 0;

  subtot+= totalPlusVat;
 }
 document.getElementById(modId+"-subtot").innerHTML = "&euro;&nbsp;&nbsp;"+formatCurrency(subtot);
 document.getElementById(modId+"-subtot").setAttribute('amount',subtot);
}

function posmodule_queryresults(items, resArr, retVal)
{
 for(var c=0; c < items.length; c++)
 {
  resArr.push(items[c]['barcode']+" - "+items[c]['name']);
  retVal.push(items[c]['barcode']);
 }
}

function posmodule_fidelityqueryresults(items, resArr, retVal)
{
 for(var c=0; c < items.length; c++)
 {
  resArr.push(items[c]['fidelitycard']+" - "+items[c]['name']);
  retVal.push(items[c]['fidelitycard']);
 }
}

function posmodule_print(modId)
{
 var amount = parseCurrency(document.getElementById(modId+"-subtot").getAttribute('amount'));
 if(document.getElementById(modId+"-hidecalcrest").checked == true)
 {
  return posmodule_submitOrder(modId,function(docInfo){
	posmodule_printOrder(modId,docInfo,function(){
		 posmodule_clearAll(modId); 
		 posmodule_fidelitycanc(modId);
		});
	});
 }
 
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	if(!a)
	{
	 posmodule_clearAll(modId);
	 posmodule_fidelitycanc(modId);
	 return;
	}
	posmodule_submitOrder(modId,function(docInfo){
	 	 posmodule_printOrder(modId,docInfo,function(){
			 posmodule_clearAll(modId);
			 posmodule_fidelitycanc(modId);
			});
		});
 	}

 sh.sendCommand("gframe -f pos/calcrest -params `amount="+amount+"`");
}

function posmodule_submitOrder(modId,callback)
{
 var module = document.getElementById(modId);
 var tb = module.tb;
 var qry = "";
 var xml = "";
 var catId = document.getElementById(modId+"-catid") ? document.getElementById(modId+"-catid").value : 0;
 var subjEd = document.getElementById(modId+"-fidelitycardcode");
 var subjectId = module.subjectInfo ? module.subjectInfo['id'] : 0;
 var storeId = document.getElementById(modId+"-storeid").value;
 var doctype = document.getElementById(modId+"-doctype").value;

 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];
  var ap = r.getAttribute('refap') ? r.getAttribute('refap') : "";
  var id = r.getAttribute('refid') ? r.getAttribute('refid') : "0";
  var vatId = r.getAttribute('vatid');
  var vatType = r.getAttribute('vattype');
  var artname = r.cell[modId+"-description"].getValue();
  var qty = parseFloat(r.cell[modId+"-qty"].getValue());
  var unitprice = parseCurrency(r.cell[modId+"-unitprice"].getValue());
  var discount = ((r.cell[modId+"-discount"].getValue().indexOf("%") > 0) ? r.cell[modId+"-discount"].getValue() : parseCurrency(r.cell[modId+"-discount"].getValue()));
  var vat = parseFloat(r.cell[modId+"-vat"].getValue());
  var packId = (r.data && r.data['pack_id']) ? r.data['pack_id'] : 0;
  var packItemId = (r.data && r.data['pack_item_id']) ? r.data['pack_item_id'] : 0;
  if(!qty)
   continue;
  xml+= "<item ap=\""+ap+"\" id=\""+id+"\" vatid=\""+vatId+"\" vattype=\""+vatType+"\" name=\""+artname+"\" qty=\""+qty+"\" price=\""+unitprice+"\" vat=\""+vat+"\" discount=\""+discount+"\" packid=\""+packId+"\" packitemid=\""+packItemId+"\"/>";
 }
 
 var sh = new GShell(); 
 sh.OnError = function(msg,err){alert(msg);} 
 sh.OnOutput = function(o,a){callback(a);}
 sh.sendCommand("commercialdocs generate-fast-document "+(catId ? "-cat '"+catId+"'" : "-ct '"+doctype+"'")+" -group 'commdocs-"+doctype.toLowerCase()+"' -status 1 -subjectid `"+subjectId+"`"+(storeId ? " --download-from-store `"+storeId+"`" : "")+" -xml `"+xml+"`");
}

function posmodule_printOrder(modId,docInfo,callback)
{
 var printer = document.getElementById(modId+"-printer").value;
 var modelId = document.getElementById(modId+"-printmodel").value;
 var doctype = document.getElementById(modId+"-doctype").value;

 // detect print mode
 var printMode = "classic";
 var list = document.getElementsByName(modId+"-printmode");
 for(var c=0; c < list.length; c++)
 {
  if(list[c].checked)
   printMode = list[c].value;
 }
 var protocol = document.getElementById(modId+"-protocol").value;

 switch(printMode)
 {
  case 'direct' : {
	 var sh = new GShell();
 	 sh.OnError = function(errmsg){alert("Problemi durante la stampa!\n"+errmsg);}
 	 sh.OnOutput = function(o,a){
	 	if(callback)
	  	 callback();
	 	else
	  	 alert("Scontrino stampato!");
		}
 	 sh.sendCommand("pos print-order -id `"+docInfo['id']+"` -model `"+modelId+"` -printer `"+printer+"`");
	} break;

  case 'file' : {
	 var sh = new GShell();
 	 sh.OnError = function(errmsg){alert("Problemi durante la stampa!\n"+errmsg);}
 	 sh.OnOutput = function(o,a){
		document.location.href = ABSOLUTE_URL+"getfile.php?file="+a['filename'];
	 	if(callback)
	  	 callback();
	 	else
	  	 alert("Scontrino stampato!");
		}
 	 sh.sendCommand("pos print-order -id `"+docInfo['id']+"` -protocol `"+protocol+"`");
	} break;

  default : {
	 var sh = new GShell();
	 sh.OnOutput = function(o,a){
		 if(callback)
		  callback();
		}
	 sh.sendCommand("gframe -f print.preview -params `modelap=printmodels&modelct="+doctype.toLowerCase()+"&parser=commercialdocs&ap=commercialdocs&id="+docInfo['id']+"` -title `"+docInfo['name']+"`");
	} break;
 }
}

function posmodule_save(modId)
{
 var moduleId = modId.replace("gjkdskmod-","");
 var printer = document.getElementById(modId+"-printer").value;
 var modelId = document.getElementById(modId+"-printmodel").value;

 // detect print mode
 var list = document.getElementsByName(modId+"-printmode");
 var printMode = "classic";
 for(var c=0; c < list.length; c++)
 {
  if(list[c].checked)
   printMode = list[c].value;
 }

 var catId = document.getElementById(modId+"-catid") ? document.getElementById(modId+"-catid").value : 0;
 var fidelityCardEnabled = document.getElementById(modId+"-fidcardenabled").checked ? "true" : "false";
 var showPricelist = document.getElementById(modId+"-showpricelist").checked ? "true" : "false";
 var showCustomer = document.getElementById(modId+"-showcustomer").checked ? "true" : "false";
 var hideCalcRest = document.getElementById(modId+"-hidecalcrest").checked ? "true" : "false";
 //var setAsPaid = document.getElementById(modId+"-setaspaid").checked ? "true" : "false";
 var setAsPaid = false; /* TODO: da ripristinare in futuro. */
 var storeId = document.getElementById(modId+"-storeid").value;
 var protocol = document.getElementById(modId+"-protocol").value;
 var doctype = document.getElementById(modId+"-doctype").value;
 var modulewidth = document.getElementById(modId+"-modulewidth").value;

 if((printMode == "file") && !protocol)
  return alert("Se vuoi utilizzare il sistema di stampa su file devi specificare un protocollo valido.");

 var params = "<config printer='"+printer+"' doctype='"+doctype+"' printmodel='"+modelId+"' printmode='"+printMode+"' catid='"+catId+"' fidelitycardenabled='"+fidelityCardEnabled+"' showpricelist='"+showPricelist+"' showcustomer='"+showCustomer+"' storeid='"+storeId+"' protocol='"+protocol+"' hidecalcrest='"+hideCalcRest+"' setaspaid='"+setAsPaid+"' modulewidth='"+modulewidth+"'/>";

 var sh = new GShell();
 sh.OnError = function(msg,errcode){alert(msg);}
 sh.OnOutput = function(o,a){
	 document.getElementById(modId+"-fidelitycardcontainer").style.display = (fidelityCardEnabled == "true") ? "" : "none";
	 document.getElementById(modId+"-pricelistcontainer").style.display = (showPricelist == "true") ? "" : "none";
	 document.getElementById(modId+"-subjectcontainer").style.display = (showCustomer == "true") ? "" : "none";
	 alert("Il modulo è stato salvato correttamente!");
	}
 sh.sendCommand("desktop edit-module -id `"+moduleId+"` -params `"+params+"`");
}

function posmodule_rename(modId)
{
 var moduleId = modId.replace("gjkdskmod-","");
 var modTit = document.getElementById(modId+"-handle");
 var title = prompt("Rinomina questo modulo",modTit.innerHTML);
 if(!title)
  return;
 
 var sh = new GShell();
 sh.OnError = function(msg,errcode){alert(msg);}
 sh.OnOutput = function(){modTit.innerHTML = title;}
 sh.sendCommand("desktop edit-module -id `"+moduleId+"` -title `"+title+"`");
}

function posmodule_dailyclosing(modId)
{
 var catId = document.getElementById(modId+"-catid") ? document.getElementById(modId+"-catid").value : 0;
 var sh = new GShell();
 sh.OnError = function(msg){alert(msg);}
 sh.sendCommand("gframe -f pos/dailyclosing -params `catid="+catId+"`");
}

function posmodule_updateCatId(modId, select)
{
 var sel = document.getElementById(modId+"-catid");
 while(sel.options.length)
  sel.removeChild(sel.options[0]);

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 var opt = document.createElement('OPTION');
	 opt.value = select.options[select.selectedIndex].getAttribute('refid');
	 opt.innerHTML = "predefinita";
	 sel.appendChild(opt);
	 if(!a) return;
	 for(var c=0; c < a.length; c++)
	 {
	  var opt = document.createElement('OPTION');
	  opt.value = a[c]['id'];
	  opt.innerHTML = a[c]['name'];
	  sel.appendChild(opt);
	 }
	}
 sh.sendCommand("dynarc cat-list -ap commercialdocs -pt '"+select.value+"'");
}


