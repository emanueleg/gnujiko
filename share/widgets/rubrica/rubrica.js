/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-12-2016
 #PACKAGE: rubrica
 #DESCRIPTION: Rubrica edit form
 #VERSION: 2.29beta
 #CHANGELOG: 17-12-2016 : Bug fix in function _showPage, aggiunto sconto predefinito su archivi.
			 17-05-2016 : Aggiunto collegamento con account Gnujiko.
			 30-04-2016 : Aggiunta aliquota iva predefinita.
			 03-04-2015 : Aggiunta funzione checkIPAcode x verificare se il cod. IPA è di 6 caratteri.
			 20-03-2015 : Aggiunto monte ore.
			 12-03-2015 : Aggiunto valori di ritorno RET_OUTARR.
			 04-03-2015 : Aggiunto pacode.
			 19-01-2015 : Aggiunto bic/swift.
			 28-10-2014 : Check su P.IVA e cod. fisc.
			 23-10-2014 : Ridimensionati campi CAP a 8 caratteri e IBAN a 31.
			 14-10-2014 : Aggiunta email predefinita.
			 29-09-2014 : Aggiunto punti fidelity card.
			 03-07-2014 : Aggiunto causali predefinite documenti.
			 26-05-2014 : Aggiunta banca d'appoggio.
			 04-02-2014 : Aggiunta funzione validateVatNumber e validateTaxCode
			 31-01-2014 : Bug fix on function _bankSave
			 28-01-2014 : Bug fix on function _bankSave
			 10-01-2014 : Bug fix on function _contactSave
			 05-11-2013 : Aggiunto note extra
			 07-09-2013 : Aggiunto fidelity card.
			 08-05-2013 : Aggiunto i riferimenti
			 25-03-2013 : Aggiunto gli allegati
			 11-02-2013 : Integrato con iDoc.
			 04-02-2013 : Bug fix with save contacts.
			 31-01-2013 : Aggiunto campo 'distance'
			 21-06-2012 - Pricelist added.
			 18-01-2012 - Integration with gframe.
			 26-02-2011 - Bug fix with special chars
 #TODO:
 
*/

var SELECTED_CONTACT_ROW = null;
var SELECTED_BANK_ROW = null;
var extendedLayerLoaded = false;
var extendedLayerOFrame = null;
var oFCKeditor = null;
var RET_OUTARR = null;

function bodyOnLoad()
{
 var sSkinPath = ABSOLUTE_URL+"var/objects/fckeditor/editor/skins/office2003/";
 oFCKeditor = new FCKeditor('extranotes') ;
 oFCKeditor.ToolbarSet = "Optimized";
 oFCKeditor.BasePath	= ABSOLUTE_URL+"var/objects/fckeditor/";
 oFCKeditor.Config['SkinPath'] = sSkinPath ;
 oFCKeditor.Config['PreloadImages'] =
				sSkinPath + 'images/toolbar.start.gif' + ';' +
				sSkinPath + 'images/toolbar.end.gif' + ';' +
				sSkinPath + 'images/toolbar.bg.gif' + ';' +
				sSkinPath + 'images/toolbar.buttonarrow.gif' ;
 oFCKeditor.Height = 450;
 oFCKeditor.ReplaceTextarea();



 document.getElementById('name').onchange = function(){document.getElementById('subtitle').innerHTML = this.value;}

 var page = document.getElementById('startuppage').value;
 var focus = document.getElementById('startupfocus').value;

 /* ATTACHMENTS */
 attUpld = new GUploader(null,null,"rubrica/");
 document.getElementById('gupldspace').appendChild(attUpld.O);
 attUpld.OnUpload = function(file){
	 var _ap = document.getElementById('archiveprefix').value;
	 var _id = document.getElementById('itemid').value;
	 var sh = new GShell();
	 sh.OnOutput = function(o,a){
		 var div = document.createElement('DIV');
		 div.className = "attachment";
		 div.id = "attachment-"+a['id'];
		 var ih = "<a href='#' class='btnedit' onclick='editAttachment("+a['id']+")' title='Modifica'><img src='"+ABSOLUTE_URL+"share/widgets/rubrica/img/edit_small.png' border='0'/ ></a> <a href='#' class='btndel' onclick='deleteAttachment("+a['id']+")' title='Rimuovi'><img src='"+ABSOLUTE_URL+"share/widgets/rubrica/img/delete_small.png' border='0'/ ></a><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank'>";
		 if(a['icons'])
	   	 {
		  if(a['icons']['size48x48'])
		   ih+= "<img src='"+ABSOLUTE_URL+a['icons']['size48x48']+"' border='0' title=\""+a['name']+"\"/ >";
	     }
	     else
		  ih+= "<img src='"+ABSOLUTE_URL+"share/mimetypes/48x48/file.png' border='0' title=\""+a['name']+"\"/ >";
	     ih+= "</a><br/ ><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank' title=\""+a['name']+"\">"+a['name']+"</a>";
		 div.innerHTML = ih;
		 document.getElementById('attachments-explore').appendChild(div);
		}
	 sh.sendCommand("dynattachments add -ap `"+_ap+"` -refid `"+_id+"` -name '"+file['name']+"' -url '"+file['fullname']+"'");
	}

 EditSearch.init(document.getElementById('agent'),
	"dynarc item-find -ap `rubrica` -ct agents -field name `","` -limit 10 --order-by 'name ASC'",
	"id","name","items",true);
 document.getElementById('agent').onchange = function(){
	 if(this.value && this.data)
	  this.setAttribute('agentid',this.data['id']);
	}

 EditSearch.init(document.getElementById('countrycode'),
	"dynarc cat-find -ap countries -field name `","` -limit 10",
	"id","name","",true);
 document.getElementById('countrycode').onchange = function(){
	 if(this.value && this.data)
	  this.setAttribute('retval',this.data['code']);
	}
 document.getElementById('countrycode').setValue = function(code){
	 var oThis = this;
	 if(!code)
	 {
	  this.setAttribute('retval','');
	  this.value = "";
	  return;
	 }
	 this.setAttribute('retval',code);
	 var sh = new GShell();
	 sh.OnOutput = function(o,a){oThis.value = a['name'];}
	 sh.sendCommand("dynarc cat-info -ap countries -code '"+code+"'");
	}
 document.getElementById('countrycode').getValue = function(){return this.getAttribute('retval');}


 if(!document.getElementById("rubrica-"+page+"-page"))
  return;

 _showPage(page);
 var foc = document.getElementById(focus);
 if(foc)
  window.setTimeout(function(){foc.focus();},1000);
}

function _abort()
{
 gframe_close();
}

function showPMCfg()
{
 var sh = new GShell();
 sh.OnOutput = function(){
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){
		 var sel = document.getElementById('paymentmode');
		 while(sel.options.length)
		  sel.removeChild(sel.options[0]);
		 if(!a) return;
		 for(var c=0; c < a.length; c++)
		 {
		  var opt = document.createElement('OPTION');
		  opt.innerHTML = a[c]['name'];
		  opt.value = a[c]['id'];
		  sel.appendChild(opt);
		 }
		}
	 sh2.sendCommand("paymentmodes list");
	}
 sh.sendSudoCommand("gframe -f config.paymentmodes");
}

function codecheck(ed)
{
 if(!ed.value) return;

 var id = document.getElementById('itemid').value;
 var sh = new GShell();
 sh.OnError = function(){return;}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(a['id'] != id)
	 {
	  alert(i18n['There is already a contact with this code. Assign a different code.']);
	  ed.focus();
	 }
	}
 sh.sendCommand("dynarc item-info -ap rubrica -code `"+ed.value+"`");
}

function fidelitycardcheck(ed)
{
 if(!ed.value) return;

 var id = document.getElementById('itemid').value;
 var sh = new GShell();
 sh.OnError = function(){return;}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(a['id'] != id)
	 {
	  alert(i18n['There is already a contact with this fidelity card. Assign a different code.']);
	  ed.focus();
	 }
	}
 sh.sendCommand("dynarc item-info -ap rubrica -where `fidelitycard='"+ed.value+"'`");
}

function _validateVatNumber(ed)
{
 if(!ed.value)
  return;

 ed.className = validateVatNumber(ed.value) ? "vatnumberedit" : "vatnumberedit error";

 var _ap = document.getElementById('archiveprefix').value;
 var _id = document.getElementById('itemid').value;

 // verifica che questa partita iva non sia gia stata utilizzata.
 var sh = new GShell();
 sh.OnError = function(err){}
 sh.OnOutput = function(o,a){if(a) return alert("Attenzione!, esiste già un altro contatto con questa Partita IVA.");}
 sh.sendCommand("dynarc item-info -ap rubrica -where `vatnumber='"+ed.value+"' AND id!='"+_id+"'`");

}

function _validateTaxCode(ed)
{
 if(!ed.value)
  return;

 ed.className = (validateTaxCode(ed.value) || validateVatNumber(ed.value)) ? "taxcodeedit" : "taxcodeedit error";

 var _ap = document.getElementById('archiveprefix').value;
 var _id = document.getElementById('itemid').value;

 // verifica che questo codice fiscale non sia gia stato utilizzato.
 var sh = new GShell();
 sh.OnError = function(err){}
 sh.OnOutput = function(o,a){if(a) return alert("Attenzione!, esiste già un altro contatto con questo codice fiscale.");}
 sh.sendCommand("dynarc item-info -ap rubrica -where `taxcode='"+ed.value+"' AND id!='"+_id+"'`");
}

function submit()
{
 if(extendedLayerLoaded)
 {
  for(var c=0; c < IDOCS.length; c++)
  {
   if(IDOCS[c].frame && !IDOCS[c].frame.saved)
   {
    if(typeof(IDOCS[c].frame.idocAutoSave) == "function")
	{
	 var oFrame = IDOCS[c].frame;
	 IDOCS[c].frame.idocAutoSave(function(){oFrame.saved=true; submit();});
	 return;
	}
   }
  }
 } 
 return saveAndClose();
}

function saveAndClose()
{
 var _ap = document.getElementById('archiveprefix').value;
 var _id = document.getElementById('itemid').value;
 var _code = document.getElementById('code').value;
 var _name = document.getElementById('name').value;
 var _notes = document.getElementById('notes').value;
 var oEditor = FCKeditorAPI.GetInstance('extranotes');
 var _extraNotes = oEditor.GetXHTML();
 var _taxcode = document.getElementById('taxcode').value.toUpperCase();
 var _vatnum = document.getElementById('vatnum').value.toUpperCase();
 var _iscompany = document.getElementById('ispa').checked ? 2 : (document.getElementById('iscompany').checked ? 1 : 0);
 var _paymentmode = document.getElementById('paymentmode').value;
 var _bankSupportId = document.getElementById('ourbanksupport').value;
 var _pricelist = document.getElementById('pricelist').value;
 var _distance = document.getElementById('distance').value;
 var _fidelityCard = document.getElementById('fidelitycard').value;
 var _fidelityCardPoints = document.getElementById('fidelitycardpoints').value;
 var _assistAvailHours = document.getElementById('assistavailhours').value;
 var _agentId = document.getElementById('agent').getAttribute('agentid');
 var _defaultEmail = document.getElementById('defaultemail').value;
 var _pacode = (_iscompany == 2) ? document.getElementById('pacode').value : "";
 var _vatId = document.getElementById('predefvat').value;

 if(_agentId == _id)
 {
  alert("Questo agente non può essere il sotto-agente di se stesso!");
  return;
 }

 /* CAUSALI DOCUMENTI */
 var gcdCausalsTB = document.getElementById("gcdcausal-list");
 var gcdcausals = "";
 for(var c=1; c < gcdCausalsTB.rows.length; c++)
 {
  var r = gcdCausalsTB.rows[c];
  var tag = r.getAttribute('doctag');
  var sel = r.cells[1].getElementsByTagName('SELECT')[0];
  gcdcausals+= tag+"="+sel.value+";";
 }

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 /* Parametri da ritornare */
	 a['notes'] = _notes;
	 a['taxcode'] = _taxcode;
	 a['vatnumber'] = _vatnum;
	 a['iscompany'] = _iscompany;
	 a['paymentmode'] = _paymentmode;
	 a['ourbanksupport'] = _bankSupportId;
	 a['pricelist'] = _pricelist;
	 a['distance'] = _distance;
	 a['fidelitycard'] = _fidelityCard;
	 a['fidelitycardpoints'] = _fidelityCardPoints;
	 a['assist_avail_hours'] = _assistAvailHours;
	 a['agent_id'] = _agentId;
	 a['defaultemail'] = _defaultEmail;
	 a['pacode'] = _pacode;
	 a['vat_id'] = _vatId;

	 RET_OUTARR = a;

	 /* save references */
	 var qry = "";
	 var tbref = document.getElementById('referlist');
	 for(var c=1; c < tbref.rows.length; c++)
	 {
	  var r = tbref.rows[c];
	  if(!r.id && !r.cells[0].getElementsByTagName('INPUT')[0].value)
	   continue;
	  else if(r.id && !r.cells[0].getElementsByTagName('INPUT')[0].value)
	  {
	   qry+= " && dynarc edit-item -ap `"+_ap+"` -id `"+_id+"` -extunset `references.id='"+r.id+"'`";
	   continue;
	  }
	  var _name = r.cells[0].getElementsByTagName('INPUT')[0].value;
	  var _type = r.cells[1].getElementsByTagName('INPUT')[0].value;
	  var _phone = r.cells[2].getElementsByTagName('INPUT')[0].value;
	  var _email = r.cells[3].getElementsByTagName('INPUT')[0].value;
	  qry+= " && dynarc edit-item -ap `"+_ap+"` -id `"+_id+"` -extset `references."+(r.id ? "id='"+r.id+"'," : "")+"name='''"+_name+"''',type='"+_type+"',phone='"+_phone+"',email='"+_email+"'`";
	 }
	 if(!qry)
	 {
	  _contactSave(function(){gframe_close(o,RET_OUTARR);});
	 }
	 else
	 {
	  var sh2 = new GShell();
	  sh2.OnFinish = function(){
		 _contactSave(function(){gframe_close(o,RET_OUTARR);});
		}
	  sh2.sendCommand(qry.substr(4));
	 }
	}

 var qry = "dynarc edit-item -ap `"+_ap+"` -id `"+_id+"` -name `"+_name+"` -code-str `"+_code+"` -desc `"+_notes+"`";
 qry+= " -extset `rubricainfo.taxcode='"+_taxcode+"',vatnumber='"+_vatnum+"',iscompany='"+_iscompany+"',paymentmode='"+_paymentmode+"',ourbanksupport='"+_bankSupportId+"',pricelist='"+_pricelist+"',distance='"+_distance+"',fidelitycard='"+_fidelityCard+"',fidelitycardpoints='"+_fidelityCardPoints+"',availhours='"+_assistAvailHours+"',agent='"+_agentId+"',extranotes='''"+_extraNotes+"''',gcdcausals='"+gcdcausals+"',defaultemail='"+_defaultEmail+"',pacode='"+_pacode+"',vatid='"+_vatId+"'";

 var disctb = document.getElementById('predefdiscount-table');
 var list = disctb.getElementsByTagName('INPUT');
 var ap = ""; var perc = "";
 for(var c=0; c < list.length; c++)
 {
  var inp = list[c];
  if(!inp.getAttribute('data-ap')) continue;
  ap+= ","+inp.getAttribute('data-ap');
  perc+= ","+parseFloat(inp.value);
 }
 if(ap != "")
  qry+= ",predefdiscount.ap='"+ap.substr(1)+"',percentage='"+perc.substr(1)+"'";

 qry+= "`";
 
 sh.sendCommand(qry);
}

function _showPage(page)
{
 var currentPage = null;
 var ul = document.getElementById('rubrica-tabs');
 var list = ul.getElementsByTagName('LI');
 for(var c=0; c < list.length; c++)
 {
  if(list[c].id == "rubrica-"+page+"-tab")
   list[c].className = "selected";
  else if(list[c].className == "selected")
  {
   currentPage = list[c].id.substr(8);
   currentPage = currentPage.substr(0,currentPage.length-4);
   list[c].className = "";
  }
 }
 if(document.getElementById("rubrica-"+currentPage+"-page")) document.getElementById("rubrica-"+currentPage+"-page").style.display='none';
 if(document.getElementById("rubrica-"+page+"-page")) 		 document.getElementById("rubrica-"+page+"-page").style.display='';
 if((page == "extended") && !extendedLayerLoaded)
 {
  loadIDOC(IDOCS[0]);
  extendedLayerLoaded=true;
 }
}

function _contactSelect(tr)
{
 var ap = document.getElementById('archiveprefix').value;
 var itemId = document.getElementById('itemid').value;
 document.getElementById('selectedcontactid').value = tr.id.substr(8);
 SELECTED_CONTACT_ROW = tr;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.getElementById('rubrica-item-label').innerHTML = a['label'];
	 document.getElementById('rubrica-item-name').value = html_entity_decode(a['name'],'ENT_QUOTES');
	 document.getElementById('rubrica-item-code').value = a['code'];
	 document.getElementById('rubrica-item-isdefault').checked = (a['isdefault'] == "1") ? true : false;
	 document.getElementById('address').value = a['address'];
	 document.getElementById('city').value = a['city'];
	 document.getElementById('zipcode').value = a['zipcode'];
	 document.getElementById('province').value = a['province'];
	 document.getElementById('countrycode').setValue(a['countrycode']);
	 document.getElementById('phone').value = a['phone'];
	 document.getElementById('phone2').value = a['phone2'];
	 document.getElementById('fax').value = a['fax'];
	 document.getElementById('cell').value = a['cell'];
	 document.getElementById('email').value = a['email'];
	 document.getElementById('email2').value = a['email2'];
	 document.getElementById('email3').value = a['email3'];
	 document.getElementById('skype').value = a['skype'];
	}
 if(tr.id.substr(8))
  sh.sendCommand("dynarc exec-func ext:contacts.info -params `ap="+ap+"&id="+tr.id.substr(8)+"`");
 else
 {
  document.getElementById('rubrica-item-label').innerHTML = "sede "+tr.rowIndex;
  document.getElementById('rubrica-item-name').value = document.getElementById('name').value;
  document.getElementById('rubrica-item-code').value = "";
  document.getElementById('rubrica-item-isdefault').checked = false;
  document.getElementById('address').value = "";
  document.getElementById('city').value = "";
  document.getElementById('zipcode').value = "";
  document.getElementById('province').value = "";
  document.getElementById('countrycode').setValue("");
  document.getElementById('phone').value = "";
  document.getElementById('phone2').value = "";
  document.getElementById('fax').value = "";
  document.getElementById('cell').value = "";
  document.getElementById('email').value = "";
  document.getElementById('email2').value = "";
  document.getElementById('email3').value = "";
  document.getElementById('skype').value = "";
 }

 var tb = document.getElementById('rubrica-list');
 for(var c=0; c < tb.rows.length-1; c++)
 {
  if(!tb.rows[c].id || (tb.rows[c].id != tr.id))
   tb.rows[c].className = "row"+(tb.rows[c].rowIndex %2 == 1 ? 1 : 0);
 }
 tr.className = "selected";
}

function _contactAdd()
{
 var ap = document.getElementById('archiveprefix').value;
 var itemId = document.getElementById('itemid').value;
 var id = document.getElementById('selectedcontactid').value;
 var tb = document.getElementById('rubrica-list');
 if(id && document.getElementById('contact-'+id))
 {
  var tr = document.getElementById('contact-'+id);
  tr.className = "row"+(tr.rowIndex %2 == 1 ? 1 : 0);
 }
 var tr = tb.insertRow(tb.rows.length-1);
 tr.className = "selected";
 tr.insertCell(-1).innerHTML = "&nbsp;";
 tr.insertCell(-1).innerHTML = "&nbsp;";
 tr.insertCell(-1).innerHTML = "&nbsp;";
 tr.cells[0].style.width="180px";
 tr.cells[2].style.width="100px";
 tr.onclick = function(){_contactSelect(this);}
 SELECTED_CONTACT_ROW = tr;

 document.getElementById('rubrica-item-isdefault').checked = false;
 document.getElementById('rubrica-item-label').innerHTML = "Sede"+(tr.rowIndex ? " "+tr.rowIndex : "");
 document.getElementById('rubrica-item-name').value = document.getElementById('name').value;
 document.getElementById('rubrica-item-code').value = "";
 document.getElementById('address').value = "";
 document.getElementById('city').value = "";
 document.getElementById('zipcode').value = "";
 document.getElementById('province').value = "";
 document.getElementById('countrycode').setValue("IT");
 document.getElementById('phone').value = "";
 document.getElementById('phone2').value = "";
 document.getElementById('fax').value = "";
 document.getElementById('cell').value = "";
 document.getElementById('email').value = "";
 document.getElementById('email2').value = "";
 document.getElementById('email3').value = "";
 document.getElementById('skype').value = "";

 document.getElementById('rubrica-contact-header').style.visibility="visible";
 document.getElementById('rubrica-contact-form').style.visibility="visible";
}

function _contactSave(callback)
{
 var id = SELECTED_CONTACT_ROW ? SELECTED_CONTACT_ROW.id.substr(8) : document.getElementById('selectedcontactid').value;
 var ap = document.getElementById('archiveprefix').value;
 var itemId = document.getElementById('itemid').value;

 var _label = document.getElementById('rubrica-item-label').innerHTML;
 var _name = document.getElementById('rubrica-item-name').value;
 var _code = document.getElementById('rubrica-item-code').value;
 var _addr = document.getElementById('address').value;
 var _city = document.getElementById('city').value;
 var _zc = document.getElementById('zipcode').value;
 var _prov = document.getElementById('province').value;
 var _cc = document.getElementById('countrycode').getValue();
 var _ph = document.getElementById('phone').value;
 var _ph2 = document.getElementById('phone2').value;
 var _fax = document.getElementById('fax').value;
 var _cell = document.getElementById('cell').value;
 var _em = document.getElementById('email').value;
 var _em2 = document.getElementById('email2').value;
 var _em3 = document.getElementById('email3').value;
 var _skype = document.getElementById('skype').value;
 var _isdefault = document.getElementById('rubrica-item-isdefault').checked ? 1 : 0;

 if(!_addr && !_city && !_zc && !_prov && !_cc && !_ph && !_ph2 && !_fax && !_cell && !_em && !_em2 && !_em3 && !_skype)
 {
  if(callback)
   callback();
  else
   alert("Contatto salvato correttamente!");
  return;
 }

 
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(SELECTED_CONTACT_ROW)
	  var tr = SELECTED_CONTACT_ROW;
	 else
	  var tr = document.getElementById('contact-'+id);
	 if(!tr.id.substr(8))
	  tr.id = "contact-"+a['last_contact']['id'];
	 tr.cells[0].innerHTML = _city;
	 tr.cells[1].innerHTML = _addr;
	 tr.cells[2].innerHTML = _ph;
	 if(callback)
	  callback();
	 else
	  alert("Contatto salvato correttamente!");
	}
 sh.sendCommand("dynarc edit-item -ap `"+ap+"` -id `"+itemId+"` -extset `"+(id ? "contacts.id="+id+"," : "contacts.")+"label=\""+_label+"\",name=\""+_name+"\",code=\""+_code+"\",address='''"+_addr+"''',city=\""+_city+"\",zipcode='"+_zc+"',province='"+_prov+"',countrycode='"+_cc+"',phone='"+_ph+"',phone2='"+_ph2+"',fax='"+_fax+"',cell='"+_cell+"',email='"+_em+"',email2='"+_em2+"',email3='"+_em3+"',skype='"+_skype+"',isdefault='"+_isdefault+"'`");

}

function _contactDelete()
{
 if(!confirm(i18n['Are you sure you want to delete this contact?']))
  return;

 var tb = document.getElementById('rubrica-list');
 var id = SELECTED_CONTACT_ROW ? SELECTED_CONTACT_ROW.id.substr(8) : document.getElementById('selectedcontactid').value;
 var ap = document.getElementById('archiveprefix').value;
 var itemId = document.getElementById('itemid').value;

 var sh = new GShell();
 sh.OnOutput = function(){
	 tb.deleteRow(document.getElementById('contact-'+id).rowIndex);
	 if(tb.rows.length > 1)
	  _contactSelect(tb.rows[0]);
	 else
	 {
	  document.getElementById('rubrica-contact-header').style.visibility="hidden";
	  document.getElementById('rubrica-contact-form').style.visibility="hidden";
	  document.getElementById('selectedcontactid').value = 0;
	 }
	}
 if(id)
  sh.sendCommand("dynarc edit-item -ap `"+ap+"` -id `"+itemId+"` -extunset `contacts.id="+id+"`");
 else
 {
  tb.deleteRow(SELECTED_CONTACT_ROW.rowIndex);
  if(tb.rows.length > 1)
   _contactSelect(tb.rows[0]);
  else
  {
   document.getElementById('rubrica-contact-header').style.visibility="hidden";
   document.getElementById('rubrica-contact-form').style.visibility="hidden";
   document.getElementById('selectedcontactid').value = 0;
  }
 }
}

function _editContactLabel()
{
 var nm = prompt(i18n['Rename contact label'],document.getElementById('rubrica-item-label').innerHTML);
 if(!nm)
  return;
 document.getElementById('rubrica-item-label').innerHTML = nm;
}

//--- BANKS ---//
function _bankSelect(tr)
{
 var ap = document.getElementById('archiveprefix').value;
 var itemId = document.getElementById('itemid').value;
 document.getElementById('selectedbankid').value = tr.id.substr(5);
 SELECTED_BANK_ROW = tr;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.getElementById('rubrica-bank-name').innerHTML = a['name'];
	 document.getElementById('rubrica-bank-holder').value = html_entity_decode(a['holder'],'ENT_QUOTES');
	 document.getElementById('rubrica-bank-isdefault').checked = (a['isdefault'] == "1") ? true : false;
	 document.getElementById('bank_abi').value = a['abi'];
	 document.getElementById('bank_cab').value = a['cab'];
	 document.getElementById('bank_cin').value = a['cin'];
	 document.getElementById('bank_cc').value = a['cc'];
	 document.getElementById('bank_bicswift').value = a['bic_swift'];
	 document.getElementById('bank_iban_1').value = a['iban'].substr(0,4);
	 document.getElementById('bank_iban_2').value = a['iban'].substr(4,4);
	 document.getElementById('bank_iban_3').value = a['iban'].substr(8,4);
	 document.getElementById('bank_iban_4').value = a['iban'].substr(12,4);
	 document.getElementById('bank_iban_5').value = a['iban'].substr(16,4);
	 document.getElementById('bank_iban_6').value = a['iban'].substr(20,4);
	 document.getElementById('bank_iban_7').value = a['iban'].substr(24);
	}
 if(tr.id.substr(5))
  sh.sendCommand("dynarc exec-func ext:banks.info -params `ap="+ap+"&id="+tr.id.substr(5)+"`");
 else
 {
  document.getElementById('rubrica-bank-name').innerHTML = "";
  document.getElementById('rubrica-bank-holder').value = document.getElementById('name').value;
  document.getElementById('rubrica-bank-isdefault').checked = false;
  document.getElementById('bank_abi').value = "";
  document.getElementById('bank_cab').value = "";
  document.getElementById('bank_cin').value = "";
  document.getElementById('bank_cc').value = "";
  document.getElementById('bank_bicswift').value = "";
  document.getElementById('bank_iban_1').value = "";
  document.getElementById('bank_iban_2').value = "";
  document.getElementById('bank_iban_3').value = "";
  document.getElementById('bank_iban_4').value = "";
  document.getElementById('bank_iban_5').value = "";
  document.getElementById('bank_iban_6').value = "";
  document.getElementById('bank_iban_7').value = "";
 }

 var tb = document.getElementById('rubrica-banks-list');
 for(var c=0; c < tb.rows.length-1; c++)
 {
  if(!tb.rows[c].id || (tb.rows[c].id != tr.id))
   tb.rows[c].className = "row"+(tb.rows[c].rowIndex %2 == 1 ? 1 : 0);
 }
 tr.className = "selected";
}

function _bankAdd()
{
 var nm = prompt(i18n['Enter the name of the new bank']);
 if(!nm)
  return;

 var ap = document.getElementById('archiveprefix').value;
 var itemId = document.getElementById('itemid').value;
 var id = document.getElementById('selectedbankid').value;
 var tb = document.getElementById('rubrica-banks-list');
 if(id && document.getElementById('bank-'+id))
 {
  var tr = document.getElementById('bank-'+id);
  tr.className = "row"+(tr.rowIndex %2 == 1 ? 1 : 0);
 }
 var tr = tb.insertRow(tb.rows.length-1);
 tr.className = "selected";
 tr.insertCell(-1).innerHTML = "&nbsp;";
 tr.insertCell(-1).innerHTML = "&nbsp;";
 tr.insertCell(-1).innerHTML = "&nbsp;";
 tr.cells[0].style.width="200px";
 tr.cells[2].style.width="170px";
 tr.onclick = function(){_bankSelect(this);}
 SELECTED_BANK_ROW = tr;

 document.getElementById('rubrica-bank-isdefault').checked = false;
 document.getElementById('rubrica-bank-name').innerHTML = nm;
 document.getElementById('rubrica-bank-holder').value = document.getElementById('name').value;
 document.getElementById('bank_abi').value = "";
 document.getElementById('bank_cab').value = "";
 document.getElementById('bank_cin').value = "";
 document.getElementById('bank_cc').value = "";
 document.getElementById('bank_bicswift').value = "";
 document.getElementById('bank_iban_1').value = "";
 document.getElementById('bank_iban_2').value = "";
 document.getElementById('bank_iban_3').value = "";
 document.getElementById('bank_iban_4').value = "";
 document.getElementById('bank_iban_5').value = "";
 document.getElementById('bank_iban_6').value = "";
 document.getElementById('bank_iban_7').value = "";

 document.getElementById('rubrica-bank-header').style.visibility="visible";
 document.getElementById('rubrica-bank-form').style.visibility="visible";
}

function _bankSave()
{
 var id = SELECTED_BANK_ROW ? SELECTED_BANK_ROW.id.substr(5) : document.getElementById('selectedbankid').value;
 var ap = document.getElementById('archiveprefix').value;
 var itemId = document.getElementById('itemid').value;

 var _name = document.getElementById('rubrica-bank-name').innerHTML.E_QUOT();
 var _holder = document.getElementById('rubrica-bank-holder').value.E_QUOT();
 var _abi = document.getElementById('bank_abi').value;
 var _cab = document.getElementById('bank_cab').value;
 var _cin = document.getElementById('bank_cin').value;
 var _cc = document.getElementById('bank_cc').value;
 var _bicswift = document.getElementById('bank_bicswift').value;
 var _iban = document.getElementById('bank_iban_1').value.toUpperCase();
 _iban+= document.getElementById('bank_iban_2').value.toUpperCase();
 _iban+= document.getElementById('bank_iban_3').value.toUpperCase();
 _iban+= document.getElementById('bank_iban_4').value.toUpperCase();
 _iban+= document.getElementById('bank_iban_5').value.toUpperCase();
 _iban+= document.getElementById('bank_iban_6').value.toUpperCase();
 _iban+= document.getElementById('bank_iban_7').value.toUpperCase();

 var _isdefault = document.getElementById('rubrica-bank-isdefault').checked ? 1 : 0;
 
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(SELECTED_BANK_ROW)
	  var tr = SELECTED_BANK_ROW;
	 else
	  var tr = document.getElementById('bank-'+id);
	 if(!tr.id.substr(5))
	  tr.id = "bank-"+a['last_bank']['id'];
	 tr.cells[0].innerHTML = _name;
	 tr.cells[1].innerHTML = _holder;
	 tr.cells[2].innerHTML = _iban;
	 alert("Dati bancari salvati correttamente");
	}
 sh.sendCommand("dynarc edit-item -ap `"+ap+"` -id `"+itemId+"` -extset `"+(id ? "banks.id="+id+"," : "banks.")+"name=\""+_name+"\",holder=\""+_holder+"\",abi='"+_abi+"',cab='"+_cab+"',cin='"+_cin+"',cc='"+_cc+"',iban='"+_iban+"',bicswift='"+_bicswift+"',isdefault='"+_isdefault+"'`");

}

function _bankDelete()
{
 if(!confirm(i18n["Are you sure you want to delete this bank?"]))
  return;

 var tb = document.getElementById('rubrica-banks-list');
 var id = SELECTED_BANK_ROW ? SELECTED_BANK_ROW.id.substr(5) : document.getElementById('selectedbankid').value;
 var ap = document.getElementById('archiveprefix').value;
 var itemId = document.getElementById('itemid').value;

 var sh = new GShell();
 sh.OnOutput = function(){
	 tb.deleteRow(document.getElementById('bank-'+id).rowIndex);
	 if(tb.rows.length > 1)
	  _bankSelect(tb.rows[0]);
	 else
	 {
	  document.getElementById('rubrica-bank-header').style.visibility="hidden";
	  document.getElementById('rubrica-bank-form').style.visibility="hidden";
	  document.getElementById('selectedbankid').value = 0;
	 }
	}
 if(id)
  sh.sendCommand("dynarc edit-item -ap `"+ap+"` -id `"+itemId+"` -extunset `banks.id="+id+"`");
 else
 {
  tb.deleteRow(SELECTED_BANK_ROW.rowIndex);
  if(tb.rows.length > 1)
   _bankSelect(tb.rows[0]);
  else
  {
   document.getElementById('rubrica-bank-header').style.visibility="hidden";
   document.getElementById('rubrica-bank-form').style.visibility="hidden";
   document.getElementById('selectedbankid').value = 0;
  }
 }
}

function _editBankName()
{
 var nm = prompt(i18n["Rename this bank"],document.getElementById('rubrica-bank-name').innerHTML);
 if(!nm)
  return;
 document.getElementById('rubrica-bank-name').innerHTML = nm;
}

/* IDOCS */
function loadIDOC(idoc)
{
 if(!idoc) return;
 var _AP = document.getElementById('archiveprefix').value;
 var _ID = document.getElementById('itemid').value;

 var sh = new GShell();
 var divName = "idocspace-"+idoc.aid+"_"+idoc.id;
 sh.OnPreOutput = function(o,a,msgType){
	 switch(msgType)
	 {
	  case 'LOADED' : {
		 idoc.frame = a; 
		 if(typeof(idoc.frame.idocAutoLoad) == "function")
		  idoc.frame.idocAutoLoad();
		} break;
	 }

	}
 sh.sendCommand("gframe -f idoc.exec -params `idocaid="+idoc.aid+"&idocid="+idoc.id+"&ap="+_AP+"&id="+_ID+"` --append-to `"+divName+"`");
}

function idocShow(li)
{
 var ul = document.getElementById("idoc-bluenuv");
 var list = ul.getElementsByTagName('LI');
 for(var c=0; c < list.length; c++)
 {
  list[c].className = (list[c] == li) ? "selected" : "";
  document.getElementById("idocspace-"+list[c].id.substr(5)).style.display = (list[c] == li) ? "" : "none";
  if(list[c] == li)
  {
   if(!IDOCS[c].frame)
	loadIDOC(IDOCS[c]);
  }

 } 
}

function idocRemove(img)
{
 var _AP = document.getElementById('archiveprefix').value;
 var _ID = document.getElementById('itemid').value;

 var strid = img.parentNode.id.substr(5);
 var sheetName = img.parentNode.getElementsByTagName('SPAN')[0].innerHTML;

 var tmp = strid.split("_");
 var aid = tmp[0];
 var id = tmp[1];

 if(!confirm("Sei sicuro di voler rimuovere la scheda '"+sheetName+"' da questo articolo?"))
  return;

 /* Check for default idoc */
 for(var c=0; c < IDOCS.length; c++)
 {
  if((IDOCS[c].aid == aid) && (IDOCS[c].id == id) && IDOCS[c].isdefault)
   return alert("Questa è una scheda predefinita, è possibile rimuoverla soltanto attraverso il pannello delle proprietà di questa categoria.");
 }

 var sh = new GShell();
 sh.OnOutput = function(){
	 var li = document.getElementById('idoc-'+aid+"_"+id);
	 li.parentNode.removeChild(li);
	 var div = document.getElementById('idocspace-'+aid+"_"+id);
	 div.parentNode.removeChild(div);
	 for(var c=0; c < IDOCS.length; c++)
	 {
	  if((IDOCS[c]['aid'] == aid) && (IDOCS[c]['id'] == id))
	  {
	   IDOCS.splice(c,1);
	   break;
	  }
	 }
	 var ul = document.getElementById('idoc-bluenuv');
	 var li = ul.getElementsByTagName('LI')[0];
	 if(li)
	  idocShow(li);
	}
 sh.sendCommand("dynarc exec-func ext:idoc.remove -params `ap="+_AP+"&id="+_ID+"&idocaid="+aid+"&idocid="+id+"`");
}

function idocAdd()
{
 var _AP = document.getElementById('archiveprefix').value;
 var _ID = document.getElementById('itemid').value;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 /* Check if idoc is already installed */
	 for(var c=0; c < IDOCS.length; c++)
	 {
	  if((IDOCS[c].aid == a['aid']) && (IDOCS[c].id == a['id']))
	   return alert("Scheda già esistente. Non puoi aggiungere due schede dello stesso tipo.");
	 }

	 var sh2 = new GShell();
	 sh2.OnOutput = function(){
		 var ul = document.getElementById('idoc-bluenuv');
		 var li = document.createElement('LI');
		 li.id = "idoc-"+a['aid']+"_"+a['id'];
		 li.innerHTML = "<img src='"+ABSOLUTE_URL+"share/widgets/rubrica/img/delete-btn.gif' title='Elimina scheda' onclick='idocRemove(this)'/ ><span>"+a['name']+"</span>";
		 li.onclick = function(){idocShow(this);}
		 ul.appendChild(li);

		 var div = document.createElement('DIV');
		 div.className = "idocpage";
		 div.id = "idocspace-"+a['aid']+"_"+a['id'];
		 div.style.display='none';
		 document.getElementById('extended-page').appendChild(div);

		 IDOCS.push({aid:a['aid'], id:a['id'], isdefault:false});

		 idocShow(li);
		}
	 sh2.sendCommand("dynarc exec-func ext:idoc.add -params `ap="+_AP+"&id="+_ID+"&idocap=idoc&idocid="+a['id']+"`");
	}
 sh.sendCommand("gframe -f idoc.choice -params `idocct=RUBRICA`");
}

/* ATTACHMENTS */

var activeAttachmentsForm = null;

function editAttachment(id)
{
 var div = document.createElement('DIV');
 div.className = "editform";
 div.style.visibility='hidden';
 _showScreenMask();
 document.body.appendChild(div);
 div.style.left =_getScreenWidth()/2-(div.offsetWidth/2);
 div.style.top = _getScreenHeight()/2-(div.offsetHeight/2);
 div.style.visibility='';

 NewLayer("dyn-attachments/forms","formtype=editatt&id="+id,div);
 activeAttachmentsForm = div;
}

function deleteAttachment(id)
{
 if(!confirm("Sei sicuro di voler eliminare questo allegato?"))
  return;
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 var div = document.getElementById('attachment-'+id);
	 div.parentNode.removeChild(div);
	}
 sh.sendCommand("dynattachments delete -id "+id+" -r");
}

function saveAttachment(id)
{
 var sh = new GShell();
 var nm = htmlentities(document.getElementById('edatt_'+id+'_name').value,"ENT_QUOT");
 var ty = document.getElementById('edatt_'+id+'_type').value;
 var kw = htmlentities(document.getElementById('edatt_'+id+'_keywords').value,"ENT_QUOT");
 var pu = document.getElementById('edatt_'+id+'_published').checked;
 var de = htmlentities(document.getElementById('edatt_'+id+'_desc').value,"ENT_QUOT");
 var url = document.getElementById('edatt_'+id+'_url');
 if(url)
  url = url.value; 

 sh.OnOutput = function(o,a){
	 attachmentsFormClose();
	 var div = document.getElementById('attachment-'+id);
	 var title = div.getElementsByTagName('A')[3];
	 title.innerHTML = nm;
	 if(url)
	 {
	  title.href = url;
	  div.getElementsByTagName('A')[2].href = url;
	 }
	}
 sh.sendCommand("dynattachments edit -id "+id+" -name '"+nm+"' -type '"+ty+"' -keyw '"+kw+"' -desc '"+de+"'"+(pu ? " -publish" : " -unpublish")+(url ? " -url '"+url+"'" : ""));
}

function attachmentsFormClose()
{
 if(activeAttachmentsForm)
 {
  document.body.removeChild(activeAttachmentsForm);
  _hideScreenMask();
 }
}

function selectFromServer(userpath)
{
 var _ap = document.getElementById('archiveprefix').value;
 var _id = document.getElementById('itemid').value;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a)
	  return;
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){
		 if(!a)
		  return;
		 var div = document.createElement('DIV');
		 div.className = "attachment";
		 div.id = "attachment-"+a['id'];
		 var ih = "<a href='#' class='btnedit' onclick='editAttachment("+a['id']+")' title='Modifica'><img src='"+ABSOLUTE_URL+"share/widgets/rubrica/img/edit_small.png' border='0'/ ></a> <a href='#' class='btndel' onclick='deleteAttachment("+a['id']+")' title='Rimuovi'><img src='"+ABSOLUTE_URL+"share/widgets/rubrica/img/delete_small.png' border='0'/ ></a><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank'>";
		 if(a['icons'])
		 {
		  if(a['icons']['size48x48'])
		   ih+= "<img src='"+ABSOLUTE_URL+a['icons']['size48x48']+"' border='0' title=\""+a['name']+"\"/ >";
		 }
		 else
		  ih+= "<img src='"+ABSOLUTE_URL+"share/mimetypes/48x48/file.png' border='0' title=\""+a['name']+"\"/ >";
		 ih+= "</a><br/ ><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank' title=\""+a['name']+"\">"+a['name']+"</a>";
		 div.innerHTML = ih;
		 document.getElementById('attachments-explore').appendChild(div);
		}
	 sh2.sendCommand("dynattachments add -ap `"+_ap+"` -refid `"+_id+"` -name '"+a['name']+"' -url '"+userpath+a['url']+"'");
	}
 sh.sendCommand("gframe -f filemanager --fullspace");
}

function insertFromURL()
{
 var url = prompt("Inserisci un indirizzo valido");
 if(!url) return;
 url = "http://"+url.replace('http://','');

 var _ap = document.getElementById('archiveprefix').value;
 var _id = document.getElementById('itemid').value;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var div = document.createElement('DIV');
	 div.className = "attachment";
	 div.id = "attachment-"+a['id'];
	 var ih = "<a href='#' class='btnedit' onclick='editAttachment("+a['id']+")' title='Modifica'><img src='"+ABSOLUTE_URL+"share/widgets/rubrica/img/edit_small.png' border='0'/ ></a> <a href='#' class='btndel' onclick='deleteAttachment("+a['id']+")' title='Rimuovi'><img src='"+ABSOLUTE_URL+"share/widgets/rubrica/img/delete_small.png' border='0'/ ></a><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank'>";
	 if(a['icons'])
	 {
	  if(a['icons']['size48x48'])
	   ih+= "<img src='"+ABSOLUTE_URL+a['icons']['size48x48']+"' border='0' title=\""+a['name']+"\"/ >";
	 }
	 else
	  ih+= "<img src='"+ABSOLUTE_URL+"share/mimetypes/48x48/file.png' border='0' title=\""+a['name']+"\"/ >";
	 ih+= "</a><br/ ><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank' title=\""+a['name']+"\">"+a['name']+"</a>";
	 div.innerHTML = ih;
	 document.getElementById('attachments-explore').appendChild(div);
	}
 sh.sendCommand("dynattachments add -ap `"+_ap+"` -refid `"+_id+"` -name '"+url.replace('http://','')+"' -url '"+url+"'");
}

function hidePACode()
{
 document.getElementById('pacode_container').style.display = "none";
 document.getElementById("userlogo").src = ABSOLUTE_URL+"share/widgets/rubrica/img/user.png";
}

function showPACode()
{
 document.getElementById('pacode_container').style.display = "";
 document.getElementById("userlogo").src = ABSOLUTE_URL+"share/widgets/rubrica/img/palogo.jpg";
}

function checkIPAcode(ed)
{
 if(ed.value != "")
 {
  if(ed.value.length != 6)
   alert("Attenzione! Il codice IPA (Indice delle Pubbliche Amministrazioni) deve essere esattamente di 6 caratteri.");
 }
}

function RegisterNewAccount()
{
 var id = document.getElementById('itemid').value;
 var fullname = document.getElementById('name').value;
 var login = document.getElementById('account-login').value;
 var password = document.getElementById('account-password').value;

 var email = document.getElementById('defaultemail').value;

 if(!login) return alert("Devi specificare il nome di login");
 if(!password) return alert("Devi inserire una password valida");

 var GROUP = ROOT_CT;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 document.getElementById('account-unregistered-container').style.display = "none";
	 document.getElementById('account-registered-container').style.display = "";
	 document.getElementById('account-unregistered-span').style.display = "none";
	 document.getElementById('account-registered-span').style.display = "";
	 USER_ID = a['uid'];
	}

 sh.sendSudoCommand("useradd -group '"+GROUP+"' -name `"+login+"` -password `"+password+"` -fullname `"+fullname+"` -email `"+email+"` --set-rubrica-id '"+id+"'");
}

function EditAccount()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	}

 sh.sendSudoCommand("gframe -f config.useredit -params `uid="+USER_ID+"`");
}

