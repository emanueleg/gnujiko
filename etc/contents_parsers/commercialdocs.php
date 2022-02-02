<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-10-2013
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: CommercialDocs parser
 #VERSION: 2.23beta
 #CHANGELOG: 18-10-2013 : Aggiunto i bolli.
			 08-10-2013 : Sistemati tutti i conteggi.
			 30-09-2013 : Aggiunto sconto incondizionato.
			 24-09-2013 : Aggiunto telefono, cellulare, fax, email, ecc...
			 12-07-2013 : Aggiunte varie
			 08-07-2013 : Aggiunto le ricevute fiscali.
			 03-07-2013 : Aggiunto chiavi per Rit. d'acconto,cassa prev, rivalsa inps e rit. enasarco.
			 29-05-2013 : Sistemato data e ora del trasporto.
			 08-05-2013 : Aggiunto campi: data di validità e date noleggio.
			 04-05-2013 : Sistemato il campo note.
			 30-04-2013 : Ripristinato DOC_NUM_PLUS_YEAR in accordo con le nuove normative italiane del 2013.
			 19-04-2013 : Bug fix sul tipo di documento.
			 12-04-2013 : Aggiunto le 3 colonne degli sconti.
			 11-04-2013 : Aggiunto colonne extra_qty e price_adjust.
			 02-03-2013 : Aggiunta chiave "Cod. cliente"
			 06-02-2013 : Bug fix on emails.
			 05-02-2013 : Bug fix nelle banche.
			 29-01-2013 : Sistemato iva separate e aggiunto chiave per esenzioni.
			 28-01-2013 : Aggiunto avviso di pagamento.
			 23-01-2013 : Aggiunto chiavi: COMPANY_NAME, CO_ADDR, ...
			 22-01-2013 : Aggiunto altre chiavi.
			 14-01-2013 : Aggiunto chiave DOC_NUM_PLUS_YEAR in accordo con le nuove normative italiane del 2013.
 #TODO:
 
*/

function gnujikocontentparser_commercialdocs_info($sessid, $shellid)
{
 $info = array('name' => "Documenti commerciali");
 $keys = array(
	 "DOC_TYPE" => "Tipo di documento", 
	 "DOC_DATE" => "Data documento", 
	 "DOC_NUM" => "Numero di documento", 
	 "DOC_YEAR" => "Anno documento",
	 "DOC_NUM_PLUS_YEAR" => "Num. doc / Anno",
	 "DOC_PG" => "Numero di pagina",
	 "VALIDITY_DATE" => "Data di validità",
	 "CHARTER_DF" => "Data inizio noleggio",
	 "CHARTER_DT" => "Data fine noleggio",
     "SUBJECT_CODE" => "Codice cliente",
	 "SUBJECT_NAME" => "Nome del destinatario",
	 "SUBJECT_TAXCODE" => "Cod. fisc. destinatario",
	 "SUBJ_TAXCODE_WP" => "Cod. fisc. destinatario (+ dicit. CF:)",
	 "SUBJECT_VATNUMBER" => "P.IVA del destinatario",
	 "SUBJ_VATNUM_WP" => "P.IVA del destinatario (+ dicit. P.IVA:)",
	 "SUBJECT_VATNUMBER_AO_TAXCODE" => "P.IVA e/o Cod.Fisc.",
	 "SUBJ_PHONE" => "Tel. fisso cliente",
	 "SUBJ_AV_PHONE" => "Tel. o Cell. cliente",
	 "SUBJ_CELL" => "Cellulare cliente",
	 "SUBJ_FAX" => "Fax cliente",
	 "SUBJ_EMAIL" => "Email cliente",
	 "SUBJ_FIDCARD" => "Fidelity card cliente",
	 "PAYMENT_METHOD" => "Metodo di pagamento",
	 "BANK_NAME" => "Banca d'appoggio: Nome della banca",
	 "BANK_IBAN" => "Banca d'appoggio: IBAN",
	 "SB_ABI" => "Banca d'appoggio: ABI",
	 "SB_CAB" => "Banca d'appoggio: CAB",
	 "SB_CIN" => "Banca d'appoggio: CIN",
	 "SB_CC" => "Banca d'appoggio: Conto Corrente",
	 
	 "COBNK_NAME" => "Ns. Banca: Nome della banca ",
	 "COBNK_IBAN" => "Ns. Banca: IBAN",
	 "CB_ABI" => "Ns. Banca: ABI",
	 "CB_CAB" => "Ns. Banca: CAB",
	 "CB_CIN" => "Ns. Banca: CIN",
	 "CB_CC" => "Ns. Banca: Conto Corrente",

	 "DOC_ADDR" => "Indirizzo destinatario",
	 "DOC_CITY" => "Città destinatario",
	 "DOC_ZIP"  => "C.A.P. destinatario",
	 "DOC_PROV" => "Prov. destinatario",
	 "DOC_CC" => "Paese destinatario",

	 "SHIP_RECP" => "Nome soggetto dest. merci",
	 "SHIP_ADDR" => "Indirizzo destinazione merci",
	 "SHIP_CITY" => "Città destinazione merci",
	 "SHIP_ZIP" => "C.A.P. destinazione merci",
	 "SHIP_PROV" => "Prov. destinazione merci",
	 "SHIP_CC" => "Paese destinazione merci",

	 "TRANS_METHOD" => "Metodo di trasporto",
	 "TRANS_SHIPPER" => "Nome del trasportatore",
	 "TRANS_NUMPLATE" => "Targa veicolo trasportatore",
	 "TRANS_CAUSAL" => "Causale del trasporto",
	 "TRANS_DATE" => "Data trasporto",
	 "TRANS_TIME" => "Ora trasporto",
	 "TRANS_DATETIME" => "Data e ora trasporto",
	 "TRANS_ASPECT" => "Aspetto dei beni",
	 "TRANS_NUM" => "Numero di colli",
	 "TRANS_WEIGHT"  => "Peso",
	 "TRANS_FREIGHT"  => "Porto",

	 "TOT_GOODS" => "Totale merce",
	 "DISC_GOODS" => "Totale merce scontata",
	 "DOC_STAMP" => "Bolli",
	 "DOC_AMOUNT"  => "Totale imponibile",
	 "DOC_VAT"  => "Totale IVA",
	 "DOC_TOTAL" => "Totale IVA inclusa",
	 "DOC_DISCOUNT"  => "Totale sconti",
	 "DOC_NOTES" => "Note",

	 "RAP" => "% Rit. d'acconto",
	 "RAPI" => "Rit. d'acconto % sull'imponibile",
	 "TOT_RACC" => "Totale rit. d'acconto",
	 "CCPP" => "% contrib. Cassa Previdenza",
	 "TOT_CCP" => "Tot. contrib. Cassa Previdenza",
	 "RINPS" => "% rivalsa INPS",
	 "TOT_RINPS" => "Tot. rivalsa INPS",
	 "RENP" => "% rit. Enasarco",
	 "RENPI" => "Rit. Enasarco % sull'imponibile",
	 "TOT_REN" => "Totale rit. Enasarco",
	 "DOC_NETPAY" => "Netto a pagare",

	 "DOC_V1_AMOUNT" => "Imponibile IVA 1",
	 "DOC_V1_COD" => "Codice IVA 1",
	 "DOC_V1_PERC" => "% IVA 1",
	 "DOC_V1_VAT" => "Imposta IVA 1",
	 "DOC_V1_EXEMPTIONS" => "Esenzioni IVA 1",

	 
	 "DOC_V2_AMOUNT" => "Imponibile IVA 2",
	 "DOC_V2_COD" => "Codice IVA 2",
	 "DOC_V2_PERC" => "% IVA 2",
	 "DOC_V2_VAT" => "Imposta IVA 2",
	 "DOC_V2_EXEMPTIONS" => "Esenzioni IVA 2",

	 "DOC_V3_AMOUNT" => "Imponibile IVA 3",
	 "DOC_V3_COD" => "Codice IVA 3",
	 "DOC_V3_PERC" => "% IVA 3",
	 "DOC_V3_VAT" => "Imposta IVA 3",
	 "DOC_V3_EXEMPTIONS" => "Esenzioni IVA 3",

	 "EXPIRY-1DT" => "Data prima scadenza",
	 "EXPIRY-1AM" => "Importo prima scadenza",
	 "EXPIRY-1DTAM" => "Data e importo prima scadenza",
	 "EXPIRY-2DT" => "Data seconda scadenza",
	 "EXPIRY-2AM" => "Importo seconda scadenza",
	 "EXPIRY-2DTAM" => "Data e importo seconda scadenza",
	 "EXPIRY-3DT" => "Data terza scadenza",
	 "EXPIRY-3AM" => "Importo terza scadenza",
	 "EXPIRY-3DTAM" => "Data e importo terza scadenza",
	 "EXPIRY-4DT" => "Data quarta scadenza",
	 "EXPIRY-4AM" => "Importo quarta scadenza",
	 "EXPIRY-4DTAM" => "Data e importo quarta scadenza",

	 "COMPANY-NAME" => "Denominazione azienda (la vostra)",
	 "CO_ADDR" => "Indirizzo (della vs azienda)",
	 "CO_ZIP" => "C.A.P. (della vs azienda)",
	 "CO_CITY" => "Città (della vs azienda)",
	 "CO_PROV" => "Provincia (della vs azienda)",
	 "CO_CC" => "Paese (della vs azienda)",
	 "CO_VATNUMBER" => "Numero della Partita IVA (della vs azienda)",
	 "CO_TAXCODE" => "Codice Fiscale (della vs azienda)",
	 "CO_PHONE" => "Telefono principale (della vs azienda)",
	 "CO_PHONE2" => "Telefono secondario (della vs azienda)",
	 "CO_FAX" => "Fax principale (della vs azienda)",
	 "CO_FAX2" => "Fax secondario (della vs azienda)",
	 "CO_CELL" => "Cellulare principale (della vs azienda)",
	 "CO_CELL2" => "Cellulare secondario (della vs azienda)",
	 "CO_EMAIL" => "Email principale (della vs azienda)",
	 "CO_EMAIL2" => "Email secondaria (della vs azienda)",
	 "CO_WEBSITE" => "Sito web (della vs azienda)",

	 "REF_NAME" => "Riferimento (nome e cognome)",
	 "REF_TYPE" => "Rif. (tipo)",
	 "REF_PHONE" => "Rif. (telefono)",
	 "REF_EMAIL" => "Rif. (email)"
	);
 return array('info'=>$info, 'keys'=>$keys);
}

function gnujikocontentparser_commercialdocs_parse($_CONTENTS, $_PARAMS, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_COMPANY_PROFILE;
 include_once($_BASE_PATH."include/company-profile.php");
 $_BANKS = $_COMPANY_PROFILE['banks'];
 $_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'];

 $contents = $_CONTENTS;

 /* GET DOC INFO */
 $ret = GShell("dynarc item-info -ap `".($_PARAMS['ap'] ? $_PARAMS['ap'] : "commercialdocs")."` -id `".$_PARAMS['id']."` -extget `cdinfo,cdelements`",$sessid,$shellid);
 if(!$ret['error'])
  $itemInfo = $ret['outarr'];

 /* GET CATEGORY INFO */
 $ret = GShell("dynarc cat-info -ap `".($_PARAMS['ap'] ? $_PARAMS['ap'] : "commercialdocs")."` -id `".$itemInfo['cat_id']."`",$sessid,$shellid);
 if(!$ret['error'])
  $catInfo = $ret['outarr'];

 /* GET CAT TAG */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT tag,parent_id FROM dynarc_".($_PARAMS['ap'] ? $_PARAMS['ap'] : "commercialdocs")."_categories WHERE id='".$itemInfo['cat_id']."'");
 if($db->Read())
 {
  if($db->record['parent_id'])
  {
   $db->RunQuery("SELECT tag FROM dynarc_".($_PARAMS['ap'] ? $_PARAMS['ap'] : "commercialdocs")."_categories WHERE id='".$db->record['parent_id']."'");
   $db->Read();
   $_CAT_TAG = $db->record['tag']; 
  }
  else
   $_CAT_TAG = $db->record['tag'];
 }
 $db->Close();


 /* GET PAYMENT METHOD */
 if($itemInfo['paymentmode'])
 {
  $ret = GShell("paymentmodes info -id `".$itemInfo['paymentmode']."`",$sessid,$shellid);
  $paymentMethod = $ret['outarr'];
 }

 /* GET LIST OF VAT RATES */
 $_VAT_BY_ID = array();
 $ret = GShell("dynarc item-list -ap `vatrates` -get `percentage,code_str,vat_type`",$sessid,$shellid);
 $vatRates = $ret['outarr']['items'];
 for($c=0; $c < count($vatRates); $c++)
  $_VAT_BY_ID[$vatRates[$c]['id']] = $vatRates[$c];


$keys = array("{DOC_TYPE}", "{DOC_DATE}" , "{DOC_NUM}", "{DOC_YEAR}", "{DOC_NUM_PLUS_YEAR}", "{DOC_PG}" ,
	 "{VALIDITY_DATE}", "{CHARTER_DF}", "{CHARTER_DT}",
	 "{SUBJECT_CODE}",
	 "{SUBJECT_NAME}",
	 "{SUBJECT_TAXCODE}",
	 "{SUBJ_TAXCODE_WP}",
	 "{SUBJECT_VATNUMBER}",
	 "{SUBJ_VATNUM_WP}",
	 "{SUBJECT_VATNUMBER_AO_TAXCODE}",
	 "{SUBJ_PHONE}",
	 "{SUBJ_AV_PHONE}",
	 "{SUBJ_CELL}",
	 "{SUBJ_FAX}",
	 "{SUBJ_EMAIL}",
	 "{SUBJ_FIDCARD}",
	 "{PAYMENT_METHOD}",
	 "{BANK_NAME}",
	 "{BANK_IBAN}",
	 "{SB_ABI}",
	 "{SB_CAB}",
	 "{SB_CIN}",
	 "{SB_CC}",
	 
	 "{COBNK_NAME}",
	 "{COBNK_IBAN}",
	 "{CB_ABI}",
	 "{CB_CAB}",
	 "{CB_CIN}",
	 "{CB_CC}",

	 "{DOC_ADDR}",
	 "{DOC_CITY}",
	 "{DOC_ZIP}",
	 "{DOC_PROV}",
	 "{DOC_CC}",

	 "{SHIP_RECP}",
	 "{SHIP_ADDR}",
	 "{SHIP_CITY}",
	 "{SHIP_ZIP}",
	 "{SHIP_PROV}",
	 "{SHIP_CC}",

	 "{TRANS_METHOD}",
	 "{TRANS_SHIPPER}",
	 "{TRANS_NUMPLATE}",
	 "{TRANS_CAUSAL}",
	 "{TRANS_DATE}",
	 "{TRANS_TIME}",
	 "{TRANS_DATETIME}",
	 "{TRANS_ASPECT}",
	 "{TRANS_NUM}",
	 "{TRANS_WEIGHT}",
	 "{TRANS_FREIGHT}",

	 "{TOT_GOODS}",
	 "{DISC_GOODS}",
	 "{DOC_STAMP}",
	 "{DOC_AMOUNT}",
	 "{DOC_VAT}",
	 "{DOC_TOTAL}",
	 "{DOC_DISCOUNT}",
	 "{DOC_NOTES}",

	 "{RAP}",
	 "{RAPI}",
	 "{TOT_RACC}",
	 "{CCPP}",
	 "{TOT_CCP}",
	 "{RINPS}",
	 "{TOT_RINPS}",
	 "{RENP}",
	 "{RENPI}",
	 "{TOT_REN}",
	 "{DOC_NETPAY}",


	 "{DOC_V1_AMOUNT}",
	 "{DOC_V1_COD}",
	 "{DOC_V1_PERC}",
	 "{DOC_V1_VAT}",
	 "{DOC_V1_EXEMPTIONS}",
	 
	 "{DOC_V2_AMOUNT}",
	 "{DOC_V2_COD}",
	 "{DOC_V2_PERC}",
	 "{DOC_V2_VAT}",
	 "{DOC_V2_EXEMPTIONS}",

	 "{DOC_V3_AMOUNT}",
	 "{DOC_V3_COD}",
	 "{DOC_V3_PERC}",
	 "{DOC_V3_VAT}",
	 "{DOC_V3_EXEMPTIONS}",

	 "{EXPIRY-1DT}", "{EXPIRY-1AM}", "{EXPIRY-1DTAM}",
	 "{EXPIRY-2DT}", "{EXPIRY-2AM}", "{EXPIRY-2DTAM}",
	 "{EXPIRY-3DT}", "{EXPIRY-3AM}", "{EXPIRY-3DTAM}",
	 "{EXPIRY-4DT}", "{EXPIRY-4AM}", "{EXPIRY-4DTAM}",

	 "{COMPANY-NAME}", "{CO_ADDR}", "{CO_ZIP}", "{CO_CITY}", "{CO_PROV}", "{CO_CC}", "{CO_VATNUMBER}", "{CO_TAXCODE}", "{CO_PHONE}", "{CO_PHONE2}", 
	 "{CO_FAX}", "{CO_FAX2}", "{CO_CELL}", "{CO_CELL2}", "{CO_EMAIL}", "{CO_EMAIL2}", "{CO_WEBSITE}",

	 "{REF_NAME}", "{REF_TYPE}", "{REF_PHONE}", "{REF_EMAIL}",
	);

switch($_CAT_TAG)
{
 case 'PREEMPTIVES' : {
	 $catName = "Preventivo"; 
	 switch($itemInfo['tag'])
	 {
	  case 'SALE' : $catName.= " di vendita"; break;
	  case 'CHARTER' : $catName.= " di noleggio"; break;
	  case 'MOUNTING' : $catName.= " di allestimento"; break;
	  case 'SPENDING' : $catName.= " di spesa"; break;
	  case 'SERVICE' : $catName.= " di prestazione"; break;
	 }
	} break;
 case 'ORDERS' : $catName = "Ordine"; break;
 case 'DDT' : $catName = "Documento di trasporto"; break;
 case 'INVOICES' : {
	 $catName = "Fattura";
	 if($itemInfo['tag'] != "DEFERRED")
	  $catName.= " accompagnatoria";
	} break;
 case 'VENDORORDERS' : $catName = "Ordine fornitore"; break;
 case 'PURCHASEINVOICES' : $catName = "Fattura d'acquisto"; break;
 case 'AGENTINVOICES' : $catName = "Fattura agente"; break;
 case 'INTERVREPORTS' : $catName = "Rapporto d'intervento"; break;
 case 'CREDITSNOTE' : $catName = "Nota di credito"; break;
 case 'DEBITSNOTE' : $catName = "Nota di debito"; break;
 case 'PAYMENTNOTICE' : $catName = "Avviso di pagamento"; break;
 case 'RECEIPTS' : $catName = "Ricevuta Fiscale"; break;
 default : $catName = "Documento";
}

$ret = GShell("dynarc item-info -ap `rubrica` -id `".$itemInfo['subject_id']."` -extget `rubricainfo,contacts,banks`",$sessid,$shellid);
$subjectInfo=$ret['outarr'];

if($itemInfo['banksupport_id'])
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_rubrica_banks WHERE id='".$itemInfo['banksupport_id']."'");
 $db->Read();
 $bankName = $db->record['name'];
 $bankIBAN = $db->record['iban'];
 $bankABI = $db->record['abi'];
 $bankCAB = $db->record['cab'];
 $bankCIN = $db->record['cin'];
 $bankCC = $db->record['cc'];
 $db->Close();

 if($bankIBAN && (strlen($bankIBAN) >= 27))
  $bankIBAN = substr($bankIBAN,0,4)." ".substr($bankIBAN,4,4)." ".substr($bankIBAN,8,4)." ".substr($bankIBAN,12,4)." ".substr($bankIBAN,16,4)." ".substr($bankIBAN,20,4)." ".substr($bankIBAN,24);
}

/* Get company bank info */
$CObankName = $_BANKS[0] ? $_BANKS[0]['name'] : "&nbsp;";
$CObankIBAN = $_BANKS[0] ? $_BANKS[0]['iban'] : "&nbsp;";
$CObankABI = $_BANKS[0] ? $_BANKS[0]['abi'] : "&nbsp;";
$CObankCAB = $_BANKS[0] ? $_BANKS[0]['cab'] : "&nbsp;";
$CObankCIN = $_BANKS[0] ? $_BANKS[0]['cin'] : "&nbsp;";
$CObankCC = $_BANKS[0] ? $_BANKS[0]['cc'] : "&nbsp;";

if($CObankIBAN && (strlen($CObankIBAN) >= 27))
 $CObankIBAN = substr($CObankIBAN,0,4)." ".substr($CObankIBAN,4,4)." ".substr($CObankIBAN,8,4)." ".substr($CObankIBAN,12,4)." ".substr($CObankIBAN,16,4)." ".substr($CObankIBAN,20,4)." ".substr($CObankIBAN,24);


$expiry = array();
$db = new AlpaDatabase();
$db->RunQuery("SELECT * FROM dynarc_".($_PARAMS['ap'] ? $_PARAMS['ap'] : "commercialdocs")."_mmr WHERE item_id='".$itemInfo['id']."' AND payment_date='0000-00-00' ORDER BY expire_date ASC");
while($db->Read())
{
 $expiry[] = array('date'=>strtotime($db->record['expire_date']), 'amount'=>$db->record['incomes']);
}
$db->Close();

$docNotes = $itemInfo['desc'];
$transMethods = array("Mittente", "Destinatario", "Vettore");

$_REFERENCE = array('name'=>'','type'=>'','phone'=>'','email'=>'');
if($itemInfo['reference_id'])
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_rubrica_references WHERE id='".$itemInfo['reference_id']."'");
 $db->Read();
 $_REFERENCE['name'] = $db->record['name'];
 $_REFERENCE['type'] = $db->record['reftype'];
 $_REFERENCE['phone'] = $db->record['phone'];
 $_REFERENCE['email'] = $db->record['email'];
 $db->Close();
}

$_VAT_RATES = array();
for($c=1; $c < 4; $c++)
{
 if(!$itemInfo["vat_".$c."_id"])
  continue;
 $vid = $itemInfo["vat_".$c."_id"];
 $_VAT_RATES[] = array("amount"=>$itemInfo["vat_".$c."_taxable"], "vatinfo"=>$_VAT_BY_ID[$vid], "vat"=>$itemInfo["vat_".$c."_tax"], "exemptions"=>"");
}


$subjectVatNumberAOTaxCode = "";
if($subjectInfo['vatnumber'])
 $subjectVatNumberAOTaxCode = "P.IVA: ".$subjectInfo['vatnumber'].($subjectInfo['taxcode'] ? "&nbsp;&nbsp;&nbsp;C.F. ".$subjectInfo['taxcode'] : "");
else if($subjectInfo['taxcode'])
 $subjectVatNumberAOTaxCode = "C.F. ".$subjectInfo['taxcode'];

$validityDate = ($itemInfo['validity_date'] != "0000-00-00") ? date('d/m/Y',strtotime($itemInfo['validity_date'])) : "";
$charterDateFrom = ($itemInfo['charter_datefrom'] != "0000-00-00") ? date('d/m/Y',strtotime($itemInfo['charter_datefrom'])) : "";
$charterDateTo = ($itemInfo['charter_dateto'] != "0000-00-00") ? date('d/m/Y',strtotime($itemInfo['charter_dateto'])) : "";


$vals = array(
	 $catName, 
	 date('d/m/Y',$itemInfo['ctime']), 
	 $itemInfo['code_num'].($itemInfo['code_ext'] ? "/".$itemInfo['code_ext'] : ""),
	 date('Y',$itemInfo['ctime']),
	 $itemInfo['code_num'].($itemInfo['code_ext'] ? "/".$itemInfo['code_ext'] : "")." / ".date('Y',$itemInfo['ctime']),
	 $_REQUEST['page'] ? $_REQUEST['page'] : 1,

	 $validityDate, $charterDateFrom, $charterDateTo,

	 $subjectInfo['code_str'],
	 $itemInfo['subject_name'],
	 $subjectInfo['taxcode'],
	 $subjectInfo['taxcode'] ? "C.F. ".$subjectInfo['taxcode'] : "&nbsp;",
	 $subjectInfo['vatnumber'],
	 $subjectInfo['vatnumber'] ? "P.IVA: ".$subjectInfo['vatnumber'] : "&nbsp;",
	 $subjectVatNumberAOTaxCode,

	 $subjectInfo['contacts'][0]['phone'],
	 $subjectInfo['contacts'][0]['phone'] ? $subjectInfo['contacts'][0]['phone'] : $subjectInfo['contacts'][0]['cell'],
	 $subjectInfo['contacts'][0]['cell'],
	 $subjectInfo['contacts'][0]['fax'],
	 $subjectInfo['contacts'][0]['email'],
	 $subjectInfo['fidelitycard'],

	 $paymentMethod ? $paymentMethod['name'] : "",
	 $bankName, $bankIBAN, $bankABI, $bankCAB, $bankCIN, $bankCC,
	 $CObankName, $CObankIBAN, $CObankABI, $CObankCAB, $CObankCIN, $CObankCC,
	 

	 $subjectInfo['contacts'][0]['address'] ? $subjectInfo['contacts'][0]['address'] : $itemInfo['ship_addr'],
	 $subjectInfo['contacts'][0]['city'] ? $subjectInfo['contacts'][0]['city'] : $itemInfo['ship_city'],
	 $subjectInfo['contacts'][0]['zipcode'] ? $subjectInfo['contacts'][0]['zipcode'] : $itemInfo['ship_zip'],
	 $subjectInfo['contacts'][0]['province'] ? $subjectInfo['contacts'][0]['province'] : $itemInfo['ship_prov'],
	 $subjectInfo['contacts'][0]['countrycode'] ? $subjectInfo['contacts'][0]['countrycode'] : $itemInfo['ship_cc'],

	 $itemInfo['ship_recp'],
	 $itemInfo['ship_addr'],
	 $itemInfo['ship_city'],
	 $itemInfo['ship_zip'],
	 $itemInfo['ship_prov'],
	 $itemInfo['ship_cc'],

	 $transMethods[$itemInfo['trans_method']],
	 $itemInfo['trans_shipper'],
	 $itemInfo['trans_numplate'],
	 $itemInfo['trans_causal'],
	 $itemInfo['trans_datetime'] ? date('d/m/Y',$itemInfo['trans_datetime']) : date('d/m/Y',$itemInfo['ctime']),
	 $itemInfo['trans_datetime'] ? date('H:i',$itemInfo['trans_datetime']) : date('H:i',$itemInfo['ctime']),
	 $itemInfo['trans_datetime'] ? date('d/m/Y H:i',$itemInfo['trans_datetime']) : date('d/m/Y H:i',$itemInfo['ctime']),
	 $itemInfo['trans_aspect'],
	 $itemInfo['trans_num'],
	 $itemInfo['trans_weight'],
	 $itemInfo['trans_freight'],

	 number_format($itemInfo['tot_goods'],2,",","."),
	 number_format($itemInfo['discounted_goods'],2,",","."),
	 number_format($itemInfo['stamp'],2,",","."),
	 number_format($itemInfo['amount'],2,",","."),
	 number_format($itemInfo['vat'],2,",","."),
	 number_format($itemInfo['total'],2,",","."),
	 $itemInfo['tot_discount'] ? number_format($itemInfo['tot_discount'],2,",",".") : "",
	 $docNotes,

	 $_COMPANY_PROFILE['accounting']['rit_acconto'],			// RAP
	 $_COMPANY_PROFILE['accounting']['rit_acconto_percimp'],	// RAPI
	 number_format($itemInfo['tot_rit_acc'],2,",","."),			// TOT_RACC

	 $_COMPANY_PROFILE['accounting']['contr_cassa_prev'],		// CCPP
	 number_format($itemInfo['tot_ccp'],2,",","."),				// TOT_CCP

	 $_COMPANY_PROFILE['accounting']['rivalsa_inps'],			// RINPS
	 number_format($itemInfo['tot_rinps'],2,",","."),			// TOT_RINPS
	 
	 $_COMPANY_PROFILE['accounting']['rit_enasarco'],			// RENP
	 $_COMPANY_PROFILE['accounting']['rit_enasarco_percimp'],	// RENPI
	 number_format($itemInfo['tot_enasarco'],2,",","."),		// TOT_REN
	 
	 number_format($itemInfo['tot_netpay'],2,",","."),			// DOC_NETPAY

	 $_VAT_RATES[0]['amount'] ? "&euro;  ".number_format($_VAT_RATES[0]['amount'],2,',','.') : "&nbsp;",
	 $_VAT_RATES[0]['vatinfo']['percentage'],
	 $_VAT_RATES[0]['vatinfo']['percentage'] ? $_VAT_RATES[0]['vatinfo']['percentage']."%" : "&nbsp;",
	 $_VAT_RATES[0]['vat'] ? "&euro;  ".number_format($_VAT_RATES[0]['vat'],2,',','.') : "&nbsp;",
	 $_VAT_RATES[0]['exemptions'] ? $_VAT_RATES[0]['exemptions'] : "&nbsp;",

	 $_VAT_RATES[1]['amount'] ? "&euro;  ".number_format($_VAT_RATES[1]['amount'],2,',','.') : "&nbsp;",
	 $_VAT_RATES[1]['vatinfo']['percentage'],
	 $_VAT_RATES[1]['vatinfo']['percentage'] ? $_VAT_RATES[1]['vatinfo']['percentage']."%" : "&nbsp;",
	 $_VAT_RATES[1]['vat'] ? "&euro;  ".number_format($_VAT_RATES[1]['vat'],2,',','.') : "&nbsp;",
	 $_VAT_RATES[1]['exemptions'] ? $_VAT_RATES[1]['exemptions'] : "&nbsp;",

	 $_VAT_RATES[2]['amount'] ? "&euro;  ".number_format($_VAT_RATES[2]['amount'],2,',','.') : "&nbsp;",
	 $_VAT_RATES[2]['vatinfo']['percentage'],
	 $_VAT_RATES[2]['vatinfo']['percentage'] ? $_VAT_RATES[2]['vatinfo']['percentage']."%" : "&nbsp;",
	 $_VAT_RATES[2]['vat'] ? "&euro;  ".number_format($_VAT_RATES[2]['vat'],2,',','.') : "&nbsp;",
	 $_VAT_RATES[2]['exemptions'] ? $_VAT_RATES[2]['exemptions'] : "&nbsp;",

	 $expiry[0]['date'] ? date('d/m/Y',$expiry[0]['date']) : "&nbsp;",
	 $expiry[0]['amount'] ? number_format($expiry[0]['amount'],2,',','.') : "&nbsp;",
	 $expiry[0]['date'] ? date('d/m/Y',$expiry[0]['date'])." : ".number_format($expiry[0]['amount'],2,',','.')." &euro;" : "&nbsp;",

	 $expiry[1]['date'] ? date('d/m/Y',$expiry[1]['date']) : "&nbsp;",
	 $expiry[1]['amount'] ? number_format($expiry[1]['amount'],2,',','.') : "&nbsp;",
	 $expiry[1]['date'] ? date('d/m/Y',$expiry[1]['date'])." : ".number_format($expiry[1]['amount'],2,',','.')." &euro;" : "&nbsp;",

	 $expiry[2]['date'] ? date('d/m/Y',$expiry[2]['date']) : "&nbsp;",
	 $expiry[2]['amount'] ? number_format($expiry[2]['amount'],2,',','.') : "&nbsp;",
	 $expiry[2]['date'] ? date('d/m/Y',$expiry[2]['date'])." : ".number_format($expiry[2]['amount'],2,',','.')." &euro;" : "&nbsp;",

	 $expiry[3]['date'] ? date('d/m/Y',$expiry[3]['date']) : "&nbsp;",
	 $expiry[3]['amount'] ? number_format($expiry[3]['amount'],2,',','.') : "&nbsp;",
	 $expiry[3]['date'] ? date('d/m/Y',$expiry[3]['date'])." : ".number_format($expiry[3]['amount'],2,',','.')." &euro;" : "&nbsp;",

	 /* COMPANY INFO */
	 $_COMPANY_PROFILE['name'],
	 $_COMPANY_PROFILE['addresses']['headquarters']['address'] ? $_COMPANY_PROFILE['addresses']['headquarters']['address'] : $_COMPANY_PROFILE['addresses']['registered_office']['address'],
	 $_COMPANY_PROFILE['addresses']['headquarters']['zip'] ? $_COMPANY_PROFILE['addresses']['headquarters']['zip'] : $_COMPANY_PROFILE['addresses']['registered_office']['zip'],
	 $_COMPANY_PROFILE['addresses']['headquarters']['city'] ? $_COMPANY_PROFILE['addresses']['headquarters']['city'] : $_COMPANY_PROFILE['addresses']['registered_office']['city'],
	 $_COMPANY_PROFILE['addresses']['headquarters']['prov'] ? $_COMPANY_PROFILE['addresses']['headquarters']['prov'] : $_COMPANY_PROFILE['addresses']['registered_office']['prov'],
	 $_COMPANY_PROFILE['addresses']['headquarters']['country'] ? $_COMPANY_PROFILE['addresses']['headquarters']['country'] : $_COMPANY_PROFILE['addresses']['registered_office']['country'],

	 $_COMPANY_PROFILE['vatnumber'], $_COMPANY_PROFILE['taxcode'], 

	 $_COMPANY_PROFILE['addresses']['headquarters']['phones'][0]['number'] ? $_COMPANY_PROFILE['addresses']['headquarters']['phones'][0]['number'] : $_COMPANY_PROFILE['addresses']['registered_office']['phones'][0]['number'],
	 $_COMPANY_PROFILE['addresses']['headquarters']['phones'][1]['number'] ? $_COMPANY_PROFILE['addresses']['headquarters']['phones'][1]['number'] : $_COMPANY_PROFILE['addresses']['registered_office']['phones'][1]['number'],

	 $_COMPANY_PROFILE['addresses']['headquarters']['fax'][0]['number'] ? $_COMPANY_PROFILE['addresses']['headquarters']['fax'][0]['number'] : $_COMPANY_PROFILE['addresses']['registered_office']['fax'][0]['number'],
	 $_COMPANY_PROFILE['addresses']['headquarters']['fax'][1]['number'] ? $_COMPANY_PROFILE['addresses']['headquarters']['fax'][1]['number'] : $_COMPANY_PROFILE['addresses']['registered_office']['fax'][1]['number'],

	 $_COMPANY_PROFILE['addresses']['headquarters']['cells'][0]['number'] ? $_COMPANY_PROFILE['addresses']['headquarters']['cells'][0]['number'] : $_COMPANY_PROFILE['addresses']['registered_office']['cells'][0]['number'],
	 $_COMPANY_PROFILE['addresses']['headquarters']['cells'][1]['number'] ? $_COMPANY_PROFILE['addresses']['headquarters']['cells'][1]['number'] : $_COMPANY_PROFILE['addresses']['registered_office']['cells'][1]['number'],

	 $_COMPANY_PROFILE['addresses']['headquarters']['emails'][0]['email'] ? $_COMPANY_PROFILE['addresses']['headquarters']['emails'][0]['email'] : $_COMPANY_PROFILE['addresses']['registered_office']['emails'][0]['email'],
	 $_COMPANY_PROFILE['addresses']['headquarters']['emails'][1]['email'] ? $_COMPANY_PROFILE['addresses']['headquarters']['emails'][1]['email'] : $_COMPANY_PROFILE['addresses']['registered_office']['emails'][1]['email'],

	 $_COMPANY_PROFILE['website'],

	 $_REFERENCE['name'], $_REFERENCE['type'], $_REFERENCE['phone'], $_REFERENCE['email']
	);

for($c=0; $c < count($keys); $c++)
{
 $key = $keys[$c];
 $val = $vals[$c];

 while($p = stripos($contents,$key,$p))
 {
  $chunk = strtoupper(substr($contents,$p-4,4));
  if(($chunk == "ID='") || ($chunk == 'ID="'))
  {// is inside on html tag //
   $endTag = stripos($contents,">",$p+strlen($key));
   $contents = substr($contents,0,$endTag+1).$val.substr($contents,$endTag+1);
   $p = $endTag+strlen($val);
  }
  else
  {
   $contents = substr($contents,0,$p).$val.substr($contents,$p+strlen($key));
   $p+= strlen($val);
  }
 }

}

$_CONTENTS = $contents;
return $contents;
}


