<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2015 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 12-03-2015
 #PACKAGE: companyprofile-config
 #DESCRIPTION: Company profile accounting panel.
 #VERSION: 2.6beta
 #CHANGELOG: 12-03-2015 : Aggiunto decimali a 5, prima però bisogna modificare tutte le tabelle a decimal 10,5
			 03-03-2015 : Aggiunto regime fiscale per fatture PA.
			 18-10-2013 : Aggiunto i bolli
			 04-10-2013 : Aggiunta l'aliquota IVA su Contr. cassa prev.
			 05-07-2013 : Aggiunta riv.inps,rit.acconto,rit.enasarco,contr.cassa.prev, ecc..
			 10-04-2013 : Predisposto per le colonne extra.
			 07-11-2012 : Rimosso temporaneamente la possibilità di scegliere il regime contabile.
 #TODO: Modificare tutti i simboli € con la valuta impostata nella configurazione.
 
*/

global $_COMPANY_PROFILE;

$_ACCOUNTING = $_COMPANY_PROFILE['accounting'];

?>
<style type='text/css'>
body {
	font-family: Arial;
	font-size: 14px;
	color: #000000;
}

hr {
	background: #dadada;
	height: 1px;
	border: 0px;
}

span.title {
	font-family: Trebuchet, Arial;
	font-size: 16px;
	color: #013397;
	margin-bottom: 12px;
}

table.section td.small {
	font-size: 12px;
	color: #333333;
}
</style>

<!-- <span class='title'><?php echo i18n('Accounting regime:'); ?> </span> 
 <select id='accounting_type' onchange="accountingTypeChanged(this)">
  <option value='simplified' <?php if($_ACCOUNTING['type'] == "simplified") echo "selected='selected'"; ?>><?php echo i18n('simplified'); ?></option>
  <option value='ordinary' <?php if($_ACCOUNTING['type'] == "ordinary") echo "selected='selected'"; ?>><?php echo i18n('ordinary'); ?></option>
 </select>

<hr/> -->

<table class='section' border='0'>
<tr><td><?php echo i18n('VAT payment frequency:'); ?></td>
	<td><select id='vat_payment_freq' onchange="vatPaymentFreqChanged(this)">
		 <option value='1' <?php if($_ACCOUNTING['vat_payment_freq'] == "1") echo "selected='selected'"; ?>><?php echo i18n('monthly'); ?></option>
		 <option value='3' <?php if($_ACCOUNTING['vat_payment_freq'] == "3") echo "selected='selected'"; ?>><?php echo i18n('quarterly'); ?></option>
		</select>
		&nbsp;&nbsp;<span id='irvatquar' <?php if($_ACCOUNTING['vat_payment_freq'] != "3") echo "style='display:none;'"; ?>><?php echo i18n('Interest on quarterly VAT:'); ?> <input type='text' class='text' size='5' id='ir_vat_quarterly' value="<?php echo $_ACCOUNTING['ir_vat_quarterly']; ?>"/> %</span></td></tr>

<tr><td><?php echo i18n('N. decimal pricing:'); ?></td>
	<td><select id='decimals_pricing'>
		 <option value='2' <?php if($_ACCOUNTING['decimals_pricing'] == "2") echo "selected='selected'"; ?>>2</option>
		 <option value='3' <?php if($_ACCOUNTING['decimals_pricing'] == "3") echo "selected='selected'"; ?>>3</option>
		 <option value='4' <?php if($_ACCOUNTING['decimals_pricing'] == "4") echo "selected='selected'"; ?>>4</option>
		 <!-- <option value='5' <?php if($_ACCOUNTING['decimals_pricing'] == "5") echo "selected='selected'"; ?>>5</option> -->
		</select></td></tr>

<tr><td><?php echo i18n('VAT rate most frequently used:'); ?></td>
	<td><select id='freq_vat_used'>
		<?php
		$ret = GShell("dynarc item-list -ap vatrates",$_REQUEST['sessid'], $_REQUEST['shellid']);
		$vatlist = $ret['outarr']['items'];
		for($c=0; $c < count($vatlist); $c++)
		 echo "<option value='".$vatlist[$c]['id']."'".($_ACCOUNTING['freq_vat_used'] == $vatlist[$c]['id'] ? " selected='selected'>" : ">")
			.$vatlist[$c]['name']."</option>";
		?>
		</select></td></tr>

<tr><td><?php echo i18n('Tax regime:'); ?></td>
	<td><select id='tax_regime' style='width:500px'>
		<?php
		$_TAX_REGIME = array(
		 "RF01" => "Ordinario",
		 "RF02" => "Contribuenti minimi",
		 "RF03" => "Nuove iniziative produttive",
		 "RF04" => "Agricoltura e attività connesse e pesca",
		 "RF05" => "Vendita sali e tabacchi",
		 "RF06" => "Commercio fiammiferi",
		 "RF07" => "Editoria",
		 "RF08" => "Gestione servizi telefonia pubblica",
		 "RF09" => "Rivendita documenti di trasporto pubblico e di sosta",
		 "RF10" => "Intrattenimenti, giochi e altre attività di cui alla tariffa allegata al DPR 640/72",
		 "RF11" => "Agenzie viaggi e turismo",
		 "RF12" => "Agriturismo",
		 "RF13" => "Vendite a domicilio",
		 "RF14" => "Rivendita beni usati, oggetti d'arte, d'antiquariato o da collezione",
		 "RF15" => "Agenzie di vendite all'asta di oggetti d'arte, antiquariato o da collezione",
		 "RF16" => "IVA per cassa P.A.",
		 "RF17" => "IVA per cassa",
		 "RF18" => "Altro",
		 "RF19" => "Regime forfettario",
		);

		reset($_TAX_REGIME);
		while(list($k,$v) = each($_TAX_REGIME))
		{
		 echo "<option value='".$k."'".($_ACCOUNTING['tax_regime'] == $k ? " selected='selected'>" : ">").$v."</option>";
		}
		?>
		</select></td></tr>

</table>

<hr/>

<!-- SEZIONE RIMOSSA TEMPORANEAMENTE -->

<table class='section' border='0'>
<!--<tr><td><?php echo i18n('Percentage of tax payment:'); ?></td>
	<td><input type='text' class='text' size='5' id="perc_tax_payment" value="<?php echo $_ACCOUNTING['perc_tax_payment']; ?>"/> %</td>
	<td class='small'><i><?php echo i18n('Rate of the VAT payable.'); ?></i></td></tr> -->

<tr><td><?php echo i18n('Amount of stamp duty on receipts:'); ?></td>
	<td><input type='text' class='text' size='5' id="amount_stamp_receipt" value="<?php echo $_ACCOUNTING['amount_stamp_receipt']; ?>"/> €</td>
	<td class='small'><i><?php echo i18n('Amount of stamp duty on receipts.'); ?></i></td></tr>

<!--<tr><td><?php echo i18n('Rate of stamp duty on routes:'); ?></td>
	<td><input type='text' class='text' size='5' id="rate_stamp_routes" value="<?php echo $_ACCOUNTING['rate_stamp_routes']; ?>"/> %</td>
	<td class='small'><i><?php echo i18n('Percentage of stamp duty on the routes.'); ?></i></td></tr>

<tr><td><?php echo i18n('Rounding stamps:'); ?></td>
	<td colspan='2'><select id='rounding_stamps'>
		 <option value='2' <?php if($_ACCOUNTING['rounding_stamps'] == "2") echo "selected='selected'"; ?>><?php echo i18n('tenths'); ?></option>
		 <option value='3' <?php if(($_ACCOUNTING['rounding_stamps'] == "3") || ($_ACCOUNTING['rounding_stamps'] == "")) echo "selected='selected'"; ?>><?php echo i18n('cents'); ?></option>
		 <option value='4' <?php if($_ACCOUNTING['rounding_stamps'] == "4") echo "selected='selected'"; ?>><?php echo i18n('thousandths'); ?></option>
		</select></td></tr>

<tr><td><?php echo i18n('Riba collection costs. to be charged:'); ?></td>
	<td><input type='text' class='text' size='5' id="riba_costs_tobecharged" value="<?php echo $_ACCOUNTING['riba_costs_tobecharged']; ?>"/> €</td>
	<td class='small'><i><?php echo i18n('These are the costs of collection to be charged for each type of Ri.Ba.'); ?></i></td></tr>-->

</table>

<hr/>

<span class='bluetitle'>Che tipo di ricevute/fatture emetti?</span>
<div style='font-size:12px;margin:10px'>
 <?php
 $_CPA = $_COMPANY_PROFILE['accounting'];
 $invoiceTypes = array("NORMAL"=>"Fatture normali e scontrini", "PARCEL"=>"Parcelle", "PROFESSIONALS"=>"Fatture professionisti", "COMMISSIONS"=>"Fatture Provvigioni (x agenti di commercio)");
 while(list($k,$v)=each($invoiceTypes))
 {
  echo "<input type='radio' name='invoicetype' value='".$k."'"
	.(($k == $_CPA['invoice_type']) ? "checked='true'" : "")
	." onclick='invoiceTypeChanged(this)'/>".$v."&nbsp;"; 
 }
 ?>
</div>

<hr/>

<span class='bluetitle'>Seleziona eventuali contributi e ritenute da applicare</span>
<table class='section' cellspacing='10' cellspacing='0' border='0'>
<tr><td><input type='checkbox' id='riv_inps_enabled' <?php if($_CPA['rivalsa_inps']) echo "checked='true'"; ?>/>Rivalsa INPS</td>
	<td><input type='text' style='width:30px' id='riv_inps' value="<?php echo $_CPA['rivalsa_inps']; ?>"/>%</td></tr>
<tr><td><input type='checkbox' id='cassa_prev_enabled' <?php if($_CPA['contr_cassa_prev']) echo "checked='true'"; ?>/>Contr. cassa prev.</td>
	<td><input type='text' style='width:30px' id='cassa_prev' value="<?php echo $_CPA['contr_cassa_prev']; ?>"/>% &nbsp;
	IVA cassa: <select id='cassa_prev_vat'><?php
		for($c=0; $c < count($vatlist); $c++)
		 echo "<option value='".$vatlist[$c]['id']."'".($_CPA['contr_cassa_prev_vatid'] == $vatlist[$c]['id'] ? " selected='selected'>" : ">")
			.$vatlist[$c]['name']."</option>";
		?></select></td></tr>
<tr><td><input type='checkbox' id='rit_enasarco_enabled' <?php if($_CPA['rit_enasarco']) echo "checked='true'"; ?>/>Rit. Enasarco</td>
	<td><input type='text' style='width:50px' id='rit_enasarco' value="<?php echo $_CPA['rit_enasarco']; ?>"/>% sul <input type='text' style='width:40px;' value="<?php echo $_CPA['rit_enasarco_percimp']; ?>" id='rit_enasarco_percimp'/>% dell&lsquo;imponibile</td></tr>
<tr><td><input type='checkbox' id='rit_acconto_enabled' <?php if($_CPA['rit_acconto']) echo "checked='true'"; ?>/>Rit.d&lsquo;acconto</td>
	<td><input type='text' style='width:30px' id='rit_acconto' value="<?php echo $_CPA['rit_acconto']; ?>"/>% sul <input type='text' style='width:40px;' value="<?php echo $_CPA['rit_acconto_percimp']; ?>" id='rit_acconto_percimp'/>% dell&lsquo;imponibile 
	<input type='radio' name='include_rivinps' <?php if($_CPA['rit_acconto_rivinpsinc']) echo "checked='true'"; ?>/>inclusa Riv. INPS 
	<input type='radio' name='include_rivinps' <?php if(!$_CPA['rit_acconto_rivinpsinc']) echo "checked='true'"; ?>/>solo del netto
 	</td></tr>
</table>

<hr/>

<span class='bluetitle'>Colonne extra nei documenti commerciali</span>
<div class="extracolumns-container">
 <table id="extracolumns" width="100%" cellspacing="0" cellpadding="0" border="0">
 <tr><th width='110'>Titolo</th>
	 <th width='80'>Tag</th>
	 <th width='120'>Formato</th>
	 <th width='190'>Posizione</th>
	 <th>Formula</th></tr>
 <?php
  $columns = array("code"=>"Codice", "sn"=>"Serial number", "account"=>"Conto", "description"=>"Articolo / Descrizione", "qty"=>"Qta",
	"units"=>"U.M.", "unitprice"=>"Prezzo unitario", "discount"=>"Sconto", "vat"=>"I.V.A.", "price"=>"Totale", "vatprice"=>"Tot. + IVA", 
	"pricelist"=>"Listino");
  $formats = array("number"=>"Numero","currency"=>"Valuta","text"=>"Testo","date"=>"Data","time"=>"Ora","datetime"=>"Data e ora");
  for($c=0; $c < count($_COMPANY_PROFILE['extracolumns']); $c++)
  {
   $itm = $_COMPANY_PROFILE['extracolumns'][$c];
   echo "<tr><td><input type='text' class='text' style='width:100px' value=\"".$itm['title']."\"/></td>";
   echo "<td><input type='text' class='text' style='width:50px' value=\"".$itm['tag']."\"/></td>";
   echo "<td><select>";
   reset($formats);
   while(list($k,$v) = each($formats))
	echo "<option value='".$k."'".($k == $itm['format'] ? " selected='selected'>" : ">").$v."</option>"; 
   echo "</select></td>";
   echo "<td><select style='width:180px'>";
   reset($columns);
   while(list($k,$v) = each($columns))
    echo "<option value='".$k."'".($itm['after'] == $k ? " selected='selected'>" : ">").$v."</option>";
   echo "</select></td>";
   echo "<td><input type='text' class='text' style='width:220px' value=\"".$itm['formula']."\"/></td></tr>";
  }
  /* white row */

   echo "<tr><td><input type='text' class='text' style='width:100px'/></td>";
   echo "<td><input type='text' class='text' style='width:50px'/></td>";
   echo "<td><select>";
   reset($formats);
   while(list($k,$v) = each($formats))
	echo "<option value='".$k."'>".$v."</option>"; 
   echo "</select></td>";
   echo "<td><select style='width:180px'>";
   reset($columns);
   while(list($k,$v) = each($columns))
    echo "<option value='".$k."'>".$v."</option>";
   echo "</select></td>";
   echo "<td><input type='text' class='text' style='width:220px'/></td></tr>";
  
 ?>
 </table>
</div>
<hr/>

<div id="ordinary-accounting" <?php if($_ACCOUNTING['type'] != "ordinary") echo "style='display:none;'"; ?>>
<span class='title'><?php echo i18n('For the ordinary management of the accounting you need to install the package:'); ?> </span> <span class='title' style='color:#333333;'>ordinary-accounting</span>&nbsp;&nbsp;<input type='button' value="<?php echo i18n('Install'); ?> &raquo;"/>
<hr/>
</div>

<div align='right' style='padding-top:5px;'>
<input type='button' value="<?php echo i18n('Abort'); ?>" onclick="gframe_close()"/> <input type='button' value="<?php echo i18n('Apply'); ?>" onclick='formSubmit()'/> <input type='button' value="<?php echo i18n('Save and close'); ?>" onclick="formSubmit(true)"/>
</div>

<script>
function formSubmit(close)
{
 /*var type = document.getElementById('accounting_type').value;*/
 var type = "simplified"; /* TODO: da modificare */
 var vatPaymentFreq = document.getElementById('vat_payment_freq').value;
 var irVatQuarterly = document.getElementById('ir_vat_quarterly').value;
 var decimalsPricing = document.getElementById('decimals_pricing').value;
 var freqVatUsed = document.getElementById('freq_vat_used').value;
 var taxRegime = document.getElementById('tax_regime').value;

 /* disattivati temporaneamente */
 /*var percTaxPayment = document.getElementById('perc_tax_payment').value;*/
 var amountStampReceipt = document.getElementById('amount_stamp_receipt').value;
 /*var rateStampRoutes = document.getElementById('rate_stamp_routes').value;
 var roundingStamps = document.getElementById('rounding_stamps').value;
 var ribaCostsToBeCharged = document.getElementById('riba_costs_tobecharged').value;*/

 var invoiceType = ""; var list = document.getElementsByName('invoicetype');
 for(var c=0; c < list.length; c++){if(list[c].checked) {invoiceType = list[c].value; break;}}

 var rivInpsEnabled = document.getElementById('riv_inps_enabled').checked;
 var cassaPrevEnabled = document.getElementById('cassa_prev_enabled').checked;
 var ritEnasarcoEnabled = document.getElementById('rit_enasarco_enabled').checked;
 var ritAccontoEnabled = document.getElementById('rit_acconto_enabled').checked;

 var rivalsaInps = rivInpsEnabled ? parseFloat(document.getElementById('riv_inps').value.replace(',','.')) : 0;
 var cassaPrev = cassaPrevEnabled ? parseFloat(document.getElementById('cassa_prev').value.replace(',','.')) : 0;
 var cassaPrevVat = cassaPrevEnabled ? document.getElementById('cassa_prev_vat').value : 0;
 var ritEnasarco = ritEnasarcoEnabled ? parseFloat(document.getElementById('rit_enasarco').value.replace(',','.')) : 0;
 var ritEnasarcoPercImp = ritEnasarcoEnabled ? parseFloat(document.getElementById('rit_enasarco_percimp').value.replace(',','.')) : 0;
 var ritAcconto = ritAccontoEnabled ? parseFloat(document.getElementById('rit_acconto').value.replace(',','.')) : 0;
 var ritAccontoPercImp = ritAccontoEnabled ? parseFloat(document.getElementById('rit_acconto_percimp').value.replace(',','.')) : 0;
 var ritAccontoRivInpsInc = ritAccontoEnabled ? (document.getElementsByName('include_rivinps')[0].checked ? 1 : 0) : 0;

 var sh = new GShell();
 sh.OnFinish = function(){
	 if(!close)
	  return alert("<?php echo i18n('Saved!'); ?>");
	 else
	  gframe_close();
	}

 var cmd = "companyprofile edit-accounting -type `"+type+"`";
 cmd+= " -vat-payment-freq `"+vatPaymentFreq+"`";
 cmd+= " -ir-vat-quarterly `"+irVatQuarterly+"`";
 cmd+= " -decimals-pricing `"+decimalsPricing+"`";
 cmd+= " -freq-vat-used `"+freqVatUsed+"`";
 cmd+= " -tax-regime `"+taxRegime+"`";

 /* disattivati temporaneamente */
 /*cmd+= " -perc-tax-payment `"+percTaxPayment+"`";*/
 cmd+= " -amount-stamp-receipt `"+amountStampReceipt+"`";
 /*cmd+= " -rate-stamp-routes `"+rateStampRoutes+"`";
 cmd+= " -rounding-stamps `"+roundingStamps+"`";
 cmd+= " -riba-costs-tobecharged `"+ribaCostsToBeCharged+"`";*/

 cmd+= " -invoice-type "+invoiceType;
 cmd+= " -rivalsa-inps "+rivalsaInps;
 cmd+= " -contr-cassa-prev "+cassaPrev;
 cmd+= " -contr-cassa-prev-vatid "+cassaPrevVat;
 cmd+= " -ritenuta-enasarco "+ritEnasarco;
 cmd+= " -rit-enasarco-percimp "+ritEnasarcoPercImp;
 cmd+= " -ritenuta-acconto "+ritAcconto;
 cmd+= " -rit-acconto-percimp "+ritAccontoPercImp;
 cmd+= " -rit-acconto-rivinpsinc "+ritAccontoRivInpsInc;

 sh.sendCommand(cmd);

 var tb = document.getElementById("extracolumns");
 var qry = "";
 for(var c=1; c < tb.rows.length; c++)
 {
  var title = tb.rows[c].cells[0].getElementsByTagName('INPUT')[0].value;
  var tag = tb.rows[c].cells[1].getElementsByTagName('INPUT')[0].value;
  if(!title || !tag)
   continue;
  var format = tb.rows[c].cells[2].getElementsByTagName('SELECT')[0].value;
  var after = tb.rows[c].cells[3].getElementsByTagName('SELECT')[0].value;
  var formula = tb.rows[c].cells[4].getElementsByTagName('INPUT')[0].value;
  qry+= " -title `"+title+"` -tag '"+tag+"' -format '"+format+"' -after '"+after+"' -formula `"+formula+"`";
 }
 if(qry != "")
  sh.sendCommand("companyprofile edit-extracolumns"+qry);
}

function vatPaymentFreqChanged(sel)
{
 if(sel.value == 3)
  document.getElementById('irvatquar').style.display='';
 else
  document.getElementById('irvatquar').style.display='none';
}

function accountingTypeChanged(sel)
{
 if(sel.value == "ordinary")
  document.getElementById('ordinary-accounting').style.display='';
 else
  document.getElementById('ordinary-accounting').style.display='none';
}

function invoiceTypeChanged(rb)
{
 switch(rb.value)
 {
  case 'PARCEL' : {
	 document.getElementById('riv_inps_enabled').checked = false;
	 document.getElementById('riv_inps').value = "";

	 document.getElementById('cassa_prev_enabled').checked = true;
	 document.getElementById('cassa_prev').value = "2";

	 document.getElementById('rit_enasarco_enabled').checked = false;
	 document.getElementById('rit_enasarco').value = "";
	 document.getElementById('rit_enasarco_percimp').value = "";

	 document.getElementById('rit_acconto_enabled').checked = true;
	 document.getElementById('rit_acconto').value = "20";
	 document.getElementById('rit_acconto_percimp').value = "100";

	 document.getElementsByName('include_rivinps')[1].checked = true; /* esclusa rivalsa inps */
	} break;

  case 'PROFESSIONALS' : {
	 document.getElementById('riv_inps_enabled').checked = true;
	 document.getElementById('riv_inps').value = "4";

	 document.getElementById('cassa_prev_enabled').checked = false;
	 document.getElementById('cassa_prev').value = "";

	 document.getElementById('rit_enasarco_enabled').checked = false;
	 document.getElementById('rit_enasarco').value = "";
	 document.getElementById('rit_enasarco_percimp').value = "";

	 document.getElementById('rit_acconto_enabled').checked = true;
	 document.getElementById('rit_acconto').value = "20";
	 document.getElementById('rit_acconto_percimp').value = "100";

	 document.getElementsByName('include_rivinps')[0].checked = true; /* inclusa rivalsa inps */
	} break;

  case 'COMMISSIONS' : {
	 document.getElementById('riv_inps_enabled').checked = false;
	 document.getElementById('riv_inps').value = "";

	 document.getElementById('cassa_prev_enabled').checked = false;
	 document.getElementById('cassa_prev').value = "";

	 document.getElementById('rit_enasarco_enabled').checked = true;
	 document.getElementById('rit_enasarco').value = "13.75";
	 document.getElementById('rit_enasarco_percimp').value = "50";

	 document.getElementById('rit_acconto_enabled').checked = true;
	 document.getElementById('rit_acconto').value = "23";
	 document.getElementById('rit_acconto_percimp').value = "50";

	 document.getElementsByName('include_rivinps')[1].checked = true; /* esclusa rivalsa inps */
	} break;

  default : {
	 document.getElementById('riv_inps_enabled').checked = false;
	 document.getElementById('riv_inps').value = "";

	 document.getElementById('cassa_prev_enabled').checked = false;
	 document.getElementById('cassa_prev').value = "";

	 document.getElementById('rit_enasarco_enabled').checked = false;
	 document.getElementById('rit_enasarco').value = "";
	 document.getElementById('rit_enasarco_percimp').value = "";

	 document.getElementById('rit_acconto_enabled').checked = false;
	 document.getElementById('rit_acconto').value = "";
	 document.getElementById('rit_acconto_percimp').value = "";

	 document.getElementsByName('include_rivinps')[1].checked = true; /* esclusa rivalsa inps */
	} break;

 }
}
</script>
<?php

