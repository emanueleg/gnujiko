<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-02-2017
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: CommercialDocs parser
 #VERSION: 2.58beta
 #CHANGELOG: 28-02-2017 : Bugfix break on if statement using do.
			 20-02-2017 : Integrazione con stampa massiva.
			 16-02-2016 : Aggiunto COD IPA cliente per fatture PA.
			 10-09-2016 : Aggiunto chiavi TOT_IMP_RACC e TOT_IMP_CCP
			 17-06-2016 : Aggiunte chiavi totale servizi.
			 03-05-2016 : Aggiunta chiave IVA_ND.
			 12-02-2016 : Aggiunto numero di tracking.
			 22-01-2016 : Bug fix chiavi esenzioni.
			 19-01-2016 : Aggiunto chiavi per banca di appoggio che ricava in base alla mod. di pagamento, RiBa o Bonifico.
			 06-10-2015 : Aggiunto chiavi sede legale.
			 01-07-2015 : Aggiunto totale costo acquisto.
			 26-03-2015 : Sistemazioni varie.
			 24-03-2015 : Aggiunta chiave REST_TO_PAY_UNFORMAT da inserire su URL per pagamenti PayPal.
			 12-03-2015 : Aggiunto campi per rif. x PA
			 18-02-2015 : Aggiunto campo data consegna.
			 17-02-2015 : Bug fix.
			 02-02-2015 : Aggiunto campi cartage_vr e packing_charges_vr.
			 26-01-2015 : Aggiunto campo location.
			 04-12-2014 : Aggiunta causale documento.
			 10-11-2014 : Aggiunto chiave NET_WEIGHT, che mostra il peso totale netto calcolato della merce.
			 27-10-2014 : Bug fix. Uscivano rit_acconto,cassa_prev,ecc.. non dal documento ma dalla config. globale.
			 26-10-2014 : Aggiunto abbuoni (rebate)
			 13-10-2014 : Aggiunto tot_expenses.
			 27-06-2014 : Aggiunto note cliente.
			 11-06-2014 : Bug fix su ric.fisc non usciva fuori dati autofficina
			 21-05-2014 : Aggiunto banca d'appoggio aziendale.
			 19-05-2014 : Aggiunto DOC_REF_EXT (documento di riferimento esterno)
			 07-04-2014 : Aggiunto PCBIntervRef.
			 03-04-2014 : Aggiunto tot. pagato e restante da pagare.
			 27-03-2014 : Integrazione con autofficina
			 10-02-2014 : Aggiunto chiave doc-pgc (doc page / page count)
			 05-02-2014 : Aggiunto chiavi per problemi con decimali.
			 03-02-2014 : Aggiunto le esenzioni.
			 08-01-2014 : Aggiunto documento di riferimento, agente, order-ref e ddt-ref.
 #TODO:
 
*/

function gnujikocontentparser_commercialdocs_info($sessid, $shellid)
{
 $info = array('name' => "Documenti commerciali");
 $keys = array(
	 "DOC_TYPE" => "Tipo di documento", 
	 "DOC_CAUSAL" => "Causale documento",
	 "DOC_DATE" => "Data documento", 
	 "DOC_NUM" => "Numero di documento", 
	 "DOC_YEAR" => "Anno documento",
	 "DOC_NUM_PLUS_YEAR" => "Num. doc / Anno",
	 "DOC_PG" => "Numero di pagina",
	 "PG_COUNT" => "Totale pagine",
	 "DOC_PGC" => "N.pg/tot pg.",
	 "VALIDITY_DATE" => "Data di validità",
	 "CHARTER_DF" => "Data inizio noleggio",
	 "CHARTER_DT" => "Data fine noleggio",
	 "LOCATION" => "Location",
	 "DELIVERY_DATE" => "Data consegna",
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
	 "SUBJECT_NOTES" => "Note cliente",
	 "PAYMENT_METHOD" => "Metodo di pagamento",

	 "BS_NAME" => "Banca d'appoggio: Nome",
	 "BS_IBAN" => "Banca d'appoggio: IBAN",
	 "BS_IBAN_CD" => "IBAN con dicitura IBAN:",
	 "BS_ABI" => "Banca d'appoggio: ABI",
	 "BS_CAB" => "Banca d'appoggio: CAB",
	 "BS_CIN" => "Banca d'appoggio: CIN",
	 "BS_BS" => "Banca d'appoggio: BICSWIFT",
	 "BS_CC" => "Banca d'appoggio: Conto Corrente",

	 "BANK_NAME" => "Banca cliente: Nome",
	 "BANK_IBAN" => "Banca cliente: IBAN",
	 "SB_ABI" => "Banca cliente: ABI",
	 "SB_CAB" => "Banca cliente: CAB",
	 "SB_CIN" => "Banca cliente: CIN",
	 "SB_BICSWIFT" => "Banca cliente: BIC/SWIFT",
	 "SB_CC" => "Banca cliente: Conto Corrente",
	 
	 "COBNK_NAME" => "Ns. Banca: Nome della banca ",
	 "COBNK_IBAN" => "Ns. Banca: IBAN",
	 "CB_ABI" => "Ns. Banca: ABI",
	 "CB_CAB" => "Ns. Banca: CAB",
	 "CB_CIN" => "Ns. Banca: CIN",
	 "CB_BICSWIFT" => "Ns. Banca: Bic/Swift",
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
	 "TRACK_NUM" => "Numero di tracking",
	 "TRANS_CAUSAL" => "Causale del trasporto",
	 "TRANS_DATE" => "Data trasporto",
	 "TRANS_TIME" => "Ora trasporto",
	 "TRANS_DATETIME" => "Data e ora trasporto",
	 "TRANS_ASPECT" => "Aspetto dei beni",
	 "TRANS_NUM" => "Numero di colli",
     "NET_WEIGHT" => "Peso netto merce",
	 "TRANS_WEIGHT"  => "Peso merce (bancale incluso)",
	 "TRANS_FREIGHT"  => "Porto",
	 "CARTAGE" => "Spese di trasporto",
	 "CARTAGE_VR" => "Sp. trasp. %IVA",
	 "PACKING_CHARGES" => "Spese di imballaggio",
	 "PACKING_CHARGES_VR" => "Sp. imball. %IVA",
	 "COLL_CHARGES" => "Spese di incasso",
	 "TOT_EXPENSES" => "Totale spese",

	 "TOT_SERV_AMOUNT" => "Totale servizi - Imponibile",
	 "TOT_SERV_VAT" => "Totale servizi - IVA",
	 "TOT_SERV_TOTAL" => "Totale servizi - Totale",
	 "TOT_GOODS" => "Totale merce",
	 "DISC_GOODS" => "Totale merce scontata",
	 "DOC_STAMP" => "Bolli",
	 "REBATE" => "Abbuoni",
	 "DOC_AMOUNT"  => "Totale imponibile",
	 "DOC_VAT"  => "Totale IVA",
	 "DOC_VAT_ND"  => "Totale IVA a carico del cess./committ.",
	 "DOC_TOTAL" => "Totale IVA inc. EUR",
	 "DOC_TOTAL_USD" => "Totale IVA inc. USD",
	 "DOC_DISCOUNT"  => "Totale sconti",
	 "TOT_COSTS" => "Totale costi",
	 "DOC_NOTES" => "Note",

	 "RAP" => "% Rit. d'acconto",
	 "RAPI" => "Rit. d'acconto % sull'imponibile",
	 "TOT_RACC" => "Totale rit. d'acconto",
	 "TOT_IMP_RACC" => "Imponibile su cui &egrave; calcolata la rit.acc",
	 "CCPP" => "% contrib. Cassa Previdenza",
	 "TOT_CCP" => "Tot. contrib. Cassa Previdenza",
	 "TOT_IMP_CCP" => "Imponibile su cui &egrave; calcolata la cassa prev.",
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

	 "TOT_EXEMPTIONS" => "Totale esenzioni",

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

	 "RO_ADDR" => "Indirizzo sede legale (della vs azienda)",
	 "RO_ZIP" => "C.A.P. sede legale (della vs azienda)",
	 "RO_CITY" => "Città sede legale (della vs azienda)",
	 "RO_PROV" => "Provincia sede legale (della vs azienda)",
	 "RO_CC" => "Paese sede legale (della vs azienda)",
	 "RO_PHONE" => "Telefono principale sede legale (della vs azienda)",
	 "RO_PHONE2" => "Telefono secondario sede legale (della vs azienda)",
	 "RO_FAX" => "Fax principale sede legale (della vs azienda)",
	 "RO_FAX2" => "Fax secondario sede legale (della vs azienda)",
	 "RO_CELL" => "Cellulare principale sede legale (della vs azienda)",
	 "RO_CELL2" => "Cellulare secondario sede legale (della vs azienda)",
	 "RO_EMAIL" => "Email principale sede legale (della vs azienda)",
	 "RO_EMAIL2" => "Email secondaria sede legale (della vs azienda)",

	 "REF_NAME" => "Riferimento (nome e cognome)",
	 "REF_TYPE" => "Rif. (tipo)",
	 "REF_PHONE" => "Rif. (telefono)",
	 "REF_EMAIL" => "Rif. (email)",

	 "DOC_REF_EXT" => "Documento di riferimento (esterno)",
	 "DOC_REF" => "Doc. di riferimento interno",
	 "ORDER_REF" => "Ordine di riferimento interno",
	 "DDT_REF" => "DDT di riferimento interno",
	 "CONV_DOC_REF" => "Rif. a doc. convertito",
	 "AGENT_NAME" => "Nome agente",

	 "TOT_PAID" => "Totale pagato",
	 "REST_TO_PAY" => "Restante da pagare",
	 "USD_RATIO" => "Rata cambio EUR - USD",

	 "PA_DOCNUM" => "Num doc. di rif. x la PA",
	 "PA_CIG" => "Codice CIG",
	 "PA_CUP" => "Codice CUP",
	 "PA_IPA" => "Codice IPA"

	);
 return array('info'=>$info, 'keys'=>$keys);
}

function gnujikocontentparser_commercialdocs_parse($_CONTENTS, $_PARAMS, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_COMPANY_PROFILE, $_USDRATIO, $_COMMERCIALDOCS_CONFIG;
 include_once($_BASE_PATH."include/company-profile.php");
 include_once($_BASE_PATH."etc/commercialdocs/config.php");
 $_BANKS = $_COMPANY_PROFILE['banks'];
 $_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'];

 $_IS_VENDOR = false;

 $contents = $_CONTENTS;

 /* GET DOC INFO */
 $ret = GShell("dynarc item-info -ap `".($_PARAMS['ap'] ? $_PARAMS['ap'] : "commercialdocs")."` -id `".$_PARAMS['id']."` -extget `cdinfo,cdelements`",$sessid,$shellid);
 if(!$ret['error'])
  $itemInfo = $ret['outarr'];

 // GET RIT.ACC and CCP amount
 $_TOT_IMP_RITACC = 0;
 $_TOT_IMP_CCP = 0;
 for($c=0; $c < count($itemInfo['elements']); $c++)
 {
  $el = $itemInfo['elements'][$c];
  if($el['ritaccapply'])
   $_TOT_IMP_RITACC+= $el['amount'];
  if($el['ccpapply'])
   $_TOT_IMP_CCP+= $el['amount'];
 }

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

 /* GET CAUSAL */
 $extraDocTypes = array();
 if($_COMMERCIALDOCS_CONFIG['DOCTYPE'] && $_COMMERCIALDOCS_CONFIG['DOCTYPE'][$_CAT_TAG])
  $extraDocTypes = $_COMMERCIALDOCS_CONFIG['DOCTYPE'][$_CAT_TAG];

 $ret = GShell("dynarc item-list -ap 'gcdcausal' -ct '".$_CAT_TAG."'",$sessid, $shellid);
 if(!$ret['error'] && count($ret['outarr']['items']))
  $_CAUSALS = $ret['outarr']['items'];

 $docCausal = "";
 if($itemInfo['tag'])
 {
  if(is_numeric($itemInfo['tag']))
  {
   for($c=0; $c < count($_CAUSALS); $c++)
   {
	if($itemInfo['tag'] == $_CAUSALS[$c]['id'])
	{
	 $docCausal = $_CAUSALS[$c]['name'];
	 break;
	}
   }
  }
  else if($extraDocTypes[$itemInfo['tag']])
   $docCausal = $extraDocTypes[$itemInfo['tag']];
 }
 else
  $docCausal = $_CAUSALS[0]['name'];

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

 /* GET DOC REF */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_".($_PARAMS['ap'] ? $_PARAMS['ap'] : "commercialdocs")."_items WHERE conv_doc_id='".$itemInfo['id']."' LIMIT 1");
 if($db->Read())
 {
  $ret = GShell("dynarc item-info -ap '".($_PARAMS['ap'] ? $_PARAMS['ap'] : "commercialdocs")."' -id '".$db->record['id']."'",$sessid,$shellid);
  if(!$ret['error'])
   $itemInfo['doc_ref'] = $ret['outarr'];
 }
 $db->Close();

 if($itemInfo['doc_ref'])
 {
  /* GET ORDER CATID AND DDT CATID */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".($_PARAMS['ap'] ? $_PARAMS['ap'] : "commercialdocs")."_categories WHERE tag='ORDERS' AND trash='0' LIMIT 1");
  $db->Read();
  $_ORDER_CATID = $db->record['id'];
  $db->RunQuery("SELECT id FROM dynarc_".($_PARAMS['ap'] ? $_PARAMS['ap'] : "commercialdocs")."_categories WHERE tag='DDT' AND trash='0' LIMIT 1");
  $db->Read();
  $_DDT_CATID = $db->record['id'];
  $db->Close();

  /* GET ORDER REF AND DDT REF */
  if(($itemInfo['doc_ref']['cat_id'] == $_DDT_CATID) || ($itemInfo['doc_ref']['hierarchy'] == ",".$_DDT_CATID.","))
  {
   $itemInfo['ddt_ref'] = $itemInfo['doc_ref'];
   // get order //
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT id FROM dynarc_".($_PARAMS['ap'] ? $_PARAMS['ap'] : "commercialdocs")."_items WHERE conv_doc_id='"
	.$itemInfo['ddt_ref']['id']."' LIMIT 1");
   if($db->Read())
   {
	$ret = GShell("dynarc item-info -ap '".($_PARAMS['ap'] ? $_PARAMS['ap'] : "commercialdocs")."' -id '".$db->record['id']."'",$sessid,$shellid);
    if(!$ret['error'])
     $itemInfo['order_ref'] = $ret['outarr'];
   }
   $db->Close();
  }
  else if(($itemInfo['doc_ref']['cat_id'] == $_ORDER_CATID) || ($itemInfo['doc_ref']['hierarchy'] == ",".$_ORDER_CATID.","))
  {
   $itemInfo['order_ref'] = $itemInfo['doc_ref'];
  }

 }


$keys = array("{DOC_TYPE}", "{DOC_CAUSAL}", "{DOC_DATE}" , "{DOC_NUM}", "{DOC_YEAR}", "{DOC_NUM_PLUS_YEAR}", "{DOC_PG}" , "{PG_COUNT}" , "{DOC_PGC}" ,
	 "{VALIDITY_DATE}", "{CHARTER_DF}", "{CHARTER_DT}", "{LOCATION}", "{DELIVERY_DATE}",
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
	 "{SUBJECT_NOTES}",
	 "{PAYMENT_METHOD}",

	 "{BS_NAME}", "{BS_IBAN}", "{BS_IBAN_CD}", "{BS_ABI}", "{BS_CAB}", "{BS_CIN}", "{BS_BS}", "{BS_CC}", 

	 "{BANK_NAME}", "{BANK_IBAN}", "{SB_ABI}", "{SB_CAB}", "{SB_CIN}", "{SB_BICSWIFT}", "{SB_CC}",
	 
	 "{COBNK_NAME}", "{COBNK_IBAN}", "{CB_ABI}", "{CB_CAB}", "{CB_CIN}", "{CB_BICSWIFT}", "{CB_CC}",

	 "{DOC_ADDR}", "{DOC_CITY}", "{DOC_ZIP}", "{DOC_PROV}", "{DOC_CC}",

	 "{SHIP_RECP}", "{SHIP_ADDR}", "{SHIP_CITY}", "{SHIP_ZIP}", "{SHIP_PROV}", "{SHIP_CC}",

	 "{TRANS_METHOD}", "{TRANS_SHIPPER}", "{TRANS_NUMPLATE}", "{TRACK_NUM}", "{TRANS_CAUSAL}", "{TRANS_DATE}",
	 "{TRANS_TIME}", "{TRANS_DATETIME}", "{TRANS_ASPECT}", "{TRANS_NUM}", "{NET_WEIGHT}", "{TRANS_WEIGHT}",
	 "{TRANS_FREIGHT}", "{CARTAGE}", "{CARTAGE_VR}", "{PACKING_CHARGES}", "{PACKING_CHARGES_VR}",
	 "{COLL_CHARGES}", "{TOT_EXPENSES}",

	 "{TOT_SERV_AMOUNT}", "{TOT_SERV_VAT}", "{TOT_SERV_TOTAL}", "{TOT_GOODS}", "{DISC_GOODS}", "{DOC_STAMP}", "{REBATE}", "{DOC_AMOUNT}", "{DOC_VAT}", "{DOC_VAT_ND}", "{DOC_TOTAL}",
	 "{DOC_TOTAL_USD}", "{DOC_DISCOUNT}", "{TOT_COSTS}", "{DOC_NOTES}",

	 "{RAP}", "{RAPI}", "{TOT_RACC}", "{TOT_IMP_RACC}", "{CCPP}", "{TOT_CCP}", "{TOT_IMP_CCP}", "{RINPS}", "{TOT_RINPS}", "{RENP}", "{RENPI}",
	 "{TOT_REN}", "{DOC_NETPAY}",


	 "{DOC_V1_AMOUNT}", "{DOC_V1_COD}", "{DOC_V1_PERC}", "{DOC_V1_VAT}", "{DOC_V1_EXEMPTIONS}",
	 "{DOC_V2_AMOUNT}", "{DOC_V2_COD}", "{DOC_V2_PERC}", "{DOC_V2_VAT}", "{DOC_V2_EXEMPTIONS}",
	 "{DOC_V3_AMOUNT}", "{DOC_V3_COD}", "{DOC_V3_PERC}", "{DOC_V3_VAT}", "{DOC_V3_EXEMPTIONS}",

	 "{TOT_EXEMPTIONS}",

	 "{EXPIRY-1DT}", "{EXPIRY-1AM}", "{EXPIRY-1DTAM}",
	 "{EXPIRY-2DT}", "{EXPIRY-2AM}", "{EXPIRY-2DTAM}",
	 "{EXPIRY-3DT}", "{EXPIRY-3AM}", "{EXPIRY-3DTAM}",
	 "{EXPIRY-4DT}", "{EXPIRY-4AM}", "{EXPIRY-4DTAM}",

	 "{COMPANY-NAME}", "{CO_ADDR}", "{CO_ZIP}", "{CO_CITY}", "{CO_PROV}", "{CO_CC}", "{CO_VATNUMBER}", "{CO_TAXCODE}", "{CO_PHONE}", "{CO_PHONE2}", 
	 "{CO_FAX}", "{CO_FAX2}", "{CO_CELL}", "{CO_CELL2}", "{CO_EMAIL}", "{CO_EMAIL2}", "{CO_WEBSITE}",

	 "{RO_ADDR}", "{RO_ZIP}", "{RO_CITY}", "{RO_PROV}", "{RO_CC}", "{RO_PHONE}", "{RO_PHONE2}", 
	 "{RO_FAX}", "{RO_FAX2}", "{RO_CELL}", "{RO_CELL2}", "{RO_EMAIL}", "{RO_EMAIL2}",

	 "{REF_NAME}", "{REF_TYPE}", "{REF_PHONE}", "{REF_EMAIL}",
	 "{DOC_REF_EXT}", "{DOC_REF}", "{ORDER_REF}", "{DDT_REF}", "{CONV_DOC_REF}",
	 "{AGENT_NAME}",

	 "{TOT_PAID}", "{REST_TO_PAY}", 

	 /* Riferimenti scheda officina */
	 "{VEHICLE_MODEL}", "{VEHICLE_NUMPLATE}", "{VEHICLE_VIN}", "{SCHEDA_OFFICINA}", "{VEHICLE_KM}",

	 /* Riferimenti PCB */
	 "{PCBINTERV_REF}",
	 "{USD_RATIO}",

	 /* Riferimenti PA */
	 "{PA_DOCNUM}", "{PA_CIG}", "{PA_CUP}", "{PA_IPA}",

	 /* PayPal */
	 "{REST_TO_PAY_UNFORMAT}"
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
 case 'VENDORORDERS' : {
	 $catName = "Ordine fornitore"; 
	 $_IS_VENDOR = true;
	} break;
 case 'PURCHASEINVOICES' : {
	 $catName = "Fattura d'acquisto"; 
	 $_IS_VENDOR = true;
	} break;
 case 'DDTIN' : {
	 $catName = "D.D.T. Fornitore"; 
	 $_IS_VENDOR = true;
	} break;
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
 $bankBicSwift = $db->record['bic_swift'];
 $db->Close();

 if($bankIBAN && (strlen($bankIBAN) >= 27))
  $bankIBAN = substr($bankIBAN,0,4)." ".substr($bankIBAN,4,4)." ".substr($bankIBAN,8,4)." ".substr($bankIBAN,12,4)." ".substr($bankIBAN,16,4)." ".substr($bankIBAN,20,4)." ".substr($bankIBAN,24);
}

/* Get company bank info */
$obsID = $itemInfo['ourbanksupport_id'];
$CObankName = $_BANKS[$obsID] ? $_BANKS[$obsID]['name'] : "&nbsp;";
$CObankIBAN = $_BANKS[$obsID] ? $_BANKS[$obsID]['iban'] : "&nbsp;";
$CObankABI = $_BANKS[$obsID] ? $_BANKS[$obsID]['abi'] : "&nbsp;";
$CObankCAB = $_BANKS[$obsID] ? $_BANKS[$obsID]['cab'] : "&nbsp;";
$CObankCIN = $_BANKS[$obsID] ? $_BANKS[$obsID]['cin'] : "&nbsp;";
$CObankCC = $_BANKS[$obsID] ? $_BANKS[$obsID]['cc'] : "&nbsp;";
$CObankBicSwift = $_BANKS[$obsID] ? $_BANKS[$obsID]['bicswift'] : "&nbsp;";

if($CObankIBAN && (strlen($CObankIBAN) >= 27))
 $CObankIBAN = substr($CObankIBAN,0,4)." ".substr($CObankIBAN,4,4)." ".substr($CObankIBAN,8,4)." ".substr($CObankIBAN,12,4)." ".substr($CObankIBAN,16,4)." ".substr($CObankIBAN,20,4)." ".substr($CObankIBAN,24);

/* Get bank support. In base alla tipologia di pagamento (RiBa o Bonifico) */
$BS_NAME = $CObankName;
$BS_IBAN = $CObankIBAN;
$BS_IBAN_CD = "";
$BS_ABI = $CObankABI;
$BS_CAB = $CObankCAB;
$BS_CIN = $CObankCIN;
$BS_BS = $CObankBicSwift;
$BS_CC = $CObankCC;

if($paymentMethod && ($paymentMethod['type'] == "RB"))
{
 $BS_NAME = $bankName;
 $BS_IBAN = $bankIBAN;
 $BS_ABI = $bankABI;
 $BS_CAB = $bankCAB;
 $BS_CIN = $bankCIN;
 $BS_BS = $bankBicSwift;
 $BS_CC = $bankCC; 
}

if($BS_NAME && $BS_IBAN && ($BS_NAME != "&nbsp;") && ($BS_IBAN != "&nbsp;"))
 $BS_IBAN_CD = "IBAN: ".$BS_IBAN;


$expiry = array();
$db = new AlpaDatabase();
$db->RunQuery("SELECT * FROM dynarc_".($_PARAMS['ap'] ? $_PARAMS['ap'] : "commercialdocs")."_mmr WHERE item_id='".$itemInfo['id']."' AND payment_date='0000-00-00' ORDER BY expire_date ASC");
while($db->Read())
{
 $expiry[] = array('date'=>strtotime($db->record['expire_date']), 'amount'=> $_IS_VENDOR ? $db->record['expenses'] : $db->record['incomes']);
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

$_TOT_EXEMPTIONS = 0;
$_VAT_RATES = array();

for($c=1; $c < 4; $c++)
{
 if(!$itemInfo["vat_".$c."_id"])
  continue;
 $vid = $itemInfo["vat_".$c."_id"];
 
 $vra = array("amount"=>$itemInfo["vat_".$c."_taxable"], "vatinfo"=>$_VAT_BY_ID[$vid], "vat"=>$itemInfo["vat_".$c."_tax"], "exemptions"=>"");
 if($vra['vatinfo']['vat_type'] != "TAXABLE")
 {
  $vra['exemptions'] = $vra['vatinfo']['name'];
  $_TOT_EXEMPTIONS+= $vra['amount'];
 }

 $_VAT_RATES[] = $vra;
}


$subjectVatNumberAOTaxCode = "";
if($subjectInfo['vatnumber'])
 $subjectVatNumberAOTaxCode = "P.IVA: ".$subjectInfo['vatnumber'].($subjectInfo['taxcode'] ? "<br/>C.F. ".$subjectInfo['taxcode'] : "");
else if($subjectInfo['taxcode'])
 $subjectVatNumberAOTaxCode = "C.F. ".$subjectInfo['taxcode'];

$validityDate = ($itemInfo['validity_date'] != "0000-00-00") ? date('d/m/Y',strtotime($itemInfo['validity_date'])) : "";
$charterDateFrom = ($itemInfo['charter_datefrom'] != "0000-00-00") ? date('d/m/Y',strtotime($itemInfo['charter_datefrom'])) : "";
$charterDateTo = ($itemInfo['charter_dateto'] != "0000-00-00") ? date('d/m/Y',strtotime($itemInfo['charter_dateto'])) : "";
$deliveryDate = ($itemInfo['delivery_date'] != "0000-00-00") ? date('d/m/Y',strtotime($itemInfo['delivery_date'])) : "";

/* Rif. schede officina */
$vehicleModel = "";
$vehicleNumplate = "";
$vehicleVIN = "";
$vehicleKM = "";
$rifSchedaOfficina = "";
if(file_exists($_BASE_PATH."Autofficina/index.php"))
{
 $db = new AlpaDatabase();
 $qry = "SELECT * FROM dynarc_schedeofficina_items WHERE ";
 switch($_CAT_TAG)
 {
  case 'PREEMPTIVES' : $qry.= "preemptive_ref_id='".$itemInfo['id']."'"; break;
  case 'ORDERS' : $qry.= "order_ref_id='".$itemInfo['id']."'"; break;
  case 'DDT' : $qry.= "ddt_ref_id='".$itemInfo['id']."'"; break;
  case 'INVOICES' : $qry.= "invoice_ref_id='".$itemInfo['id']."'"; break;
  case 'RECEIPTS' : $qry.= "receipt_ref_id='".$itemInfo['id']."'"; break;
 }
 $db->RunQuery($qry);
 if($db->Read())
 {
  $rifSchedaOfficina = "Scheda n. ".$db->record['code_num'].($db->record['code_ext'] ? "/".$db->record['code_ext'] : "")." del ".date('d/m/Y',strtotime($db->record['ctime']));
  $vehicleKM = $db->record['vehicle_km'];
  $db->RunQuery("SELECT * FROM dynarc_vehicles_items WHERE id='".$db->record['vehicle_id']."'");
  $db->Read();
  $vehicleModel = $db->record['name'];
  $vehicleNumplate = $db->record['numplate'];
  $vehicleVIN = $db->record['vin'];
 }
 $db->Close();
}

/* Calcolo peso netto merce e costo totale acquisto */
$_TOT_NET_WEIGHT = 0;
$_TOT_PURCHASECOSTS = 0;
$_TOT_SERVICES_AMOUNT = 0;
$_TOT_SERVICES_VAT = 0;
$_TOT_SERVICES_TOTAL = 0;
$weightMul = array('mg'=>0.000001, 'g'=>0.001, 'hg'=>0.1, 'kg'=>1, 'q'=>100, 't'=>1000);
for($c=0; $c < count($itemInfo['elements']); $c++)
{
 $el = $itemInfo['elements'][$c];
 $_TOT_PURCHASECOSTS+= ($el['vendor_price'] * $el['qty']);
 if($el['weight'] && $el['weightunits'])
 {
  $qty = $el['qty'] * ($el['extraqty'] ? $el['extraqty'] : 1);
  $_TOT_NET_WEIGHT+= (($el['weight']*$qty)*$weightMul[$el['weightunits']]);
 }
 if($el['type'] == "service")
 {
  $_TOT_SERVICES_AMOUNT+= $el['amount'];
  $_TOT_SERVICES_VAT+= $el['vat'];
  $_TOT_SERVICES_TOTAL+= $el['total'];
 }
}

$vals = array(
	 $catName, 
	 $docCausal,
	 date('d/m/Y',$itemInfo['ctime']), 
	 $itemInfo['code_num'].($itemInfo['code_ext'] ? "/".$itemInfo['code_ext'] : ""),
	 date('Y',$itemInfo['ctime']),
	 $itemInfo['code_num'].($itemInfo['code_ext'] ? "/".$itemInfo['code_ext'] : "")." / ".date('Y',$itemInfo['ctime']),
	 $_REQUEST['page'] ? $_REQUEST['page'] : 1,
	 $_REQUEST['pagecount'] ? $_REQUEST['pagecount'] : "{PG_COUNT}",
	 ($_REQUEST['page'] && $_REQUEST['pagecount']) ? $_REQUEST['page']."/".$_REQUEST['pagecount'] : "{DOC_PGC}",

	 $validityDate, $charterDateFrom, $charterDateTo, $itemInfo['location'], $deliveryDate,

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
	 $subjectInfo['desc'],

	 $paymentMethod ? $paymentMethod['name'] : "",
	 $BS_NAME, $BS_IBAN, $BS_IBAN_CD, $BS_ABI, $BS_CAB, $BS_CIN, $BS_BS, $BS_CC,
	 $bankName, $bankIBAN, $bankABI, $bankCAB, $bankCIN, $bankBicSwift, $bankCC,
	 $CObankName, $CObankIBAN, $CObankABI, $CObankCAB, $CObankCIN, $CObankBicSwift, $CObankCC,
	 

	 $subjectInfo['contacts'][0]['address'] ? $subjectInfo['contacts'][0]['address'] : $itemInfo['ship_addr'],
	 $subjectInfo['contacts'][0]['city'] ? $subjectInfo['contacts'][0]['city'] : $itemInfo['ship_city'],
	 $subjectInfo['contacts'][0]['zipcode'] ? $subjectInfo['contacts'][0]['zipcode'] : $itemInfo['ship_zip'],
	 $subjectInfo['contacts'][0]['province'] ? strtoupper($subjectInfo['contacts'][0]['province']) : strtoupper($itemInfo['ship_prov']),
	 $subjectInfo['contacts'][0]['countrycode'] ? $subjectInfo['contacts'][0]['countrycode'] : $itemInfo['ship_cc'],

	 $itemInfo['ship_recp'],
	 $itemInfo['ship_addr'],
	 $itemInfo['ship_city'],
	 $itemInfo['ship_zip'],
	 strtoupper($itemInfo['ship_prov']),
	 $itemInfo['ship_cc'],

	 $transMethods[$itemInfo['trans_method']],
	 $itemInfo['trans_shipper'],
	 $itemInfo['trans_numplate'],
	 $itemInfo['tracking_number'],
	 $itemInfo['trans_causal'],
	 $itemInfo['trans_datetime'] ? date('d/m/Y',$itemInfo['trans_datetime']) : date('d/m/Y',$itemInfo['ctime']),
	 $itemInfo['trans_datetime'] ? date('H:i',$itemInfo['trans_datetime']) : date('H:i',$itemInfo['ctime']),
	 $itemInfo['trans_datetime'] ? date('d/m/Y H:i',$itemInfo['trans_datetime']) : date('d/m/Y H:i',$itemInfo['ctime']),
	 $itemInfo['trans_aspect'],
	 $itemInfo['trans_num'],
	 $_TOT_NET_WEIGHT,
	 $itemInfo['trans_weight'],
	 $itemInfo['trans_freight'],
	 ($itemInfo['cartage'] > 0) ? number_format($itemInfo['cartage'],2,",",".") : "",
	 ($itemInfo['cartage'] > 0) ? $_VAT_BY_ID[$itemInfo['cartage_vatid']]['percentage']."%" : "",
	 ($itemInfo['packing_charges'] > 0) ? number_format($itemInfo['packing_charges'],2,",",".") : "",
	 ($itemInfo['packing_charges'] > 0) ? $_VAT_BY_ID[$itemInfo['packing_charges_vatid']]['percentage']."%" : "",
	 ($itemInfo['collection_charges'] > 0) ? number_format($itemInfo['collection_charges'],2,",",".") : "",
	 ($itemInfo['tot_expenses'] > 0) ? number_format($itemInfo['tot_expenses'],2,',','.') : "",

	 number_format($_TOT_SERVICES_AMOUNT,2,",","."),
	 number_format($_TOT_SERVICES_VAT,2,",","."),
	 number_format($_TOT_SERVICES_TOTAL,2,",","."),
	 number_format($itemInfo['tot_goods'],2,",","."),
	 number_format($itemInfo['discounted_goods'],2,",","."),
	 ($itemInfo['stamp'] > 0) ? number_format($itemInfo['stamp'],2,",",".") : "",
	 ($itemInfo['rebate'] > 0) ? number_format($itemInfo['rebate'],2,",",".") : "",
	 number_format($itemInfo['amount'],2,",","."),
	 number_format($itemInfo['vat'],2,",","."),
	 number_format($itemInfo['vat_nd'],2,",","."),
	 number_format($itemInfo['total'],2,",","."),
	 number_format($itemInfo['total']*$_USDRATIO,2,",","."),
	 ($itemInfo['tot_discount'] > 0) ? number_format($itemInfo['tot_discount'],2,",",".") : "",
	 number_format($_TOT_PURCHASECOSTS,2,",","."),
	 $docNotes,

	 $itemInfo['rit_acconto'],									// RAP
	 $itemInfo['rit_acconto_percimp'],							// RAPI
	 number_format($itemInfo['tot_rit_acc'],2,",","."),			// TOT_RACC
	 number_format($_TOT_IMP_RITACC,2,",","."),					// TOT_IMP_RACC

	 $itemInfo['contr_cassa_prev'],								// CCPP
	 number_format($itemInfo['tot_ccp'],2,",","."),				// TOT_CCP
	 number_format($_TOT_IMP_CCP,2,",","."),					// TOT_IMP_CCP

	 $itemInfo['rivalsa_inps'],									// RINPS
	 number_format($itemInfo['tot_rinps'],2,",","."),			// TOT_RINPS
	 
	 $itemInfo['rit_enasarco'],									// RENP
	 $itemInfo['rit_enasarco_percimp'],							// RENPI
	 number_format($itemInfo['tot_enasarco'],2,",","."),		// TOT_REN
	 
	 number_format($itemInfo['tot_netpay'],2,",","."),			// DOC_NETPAY

	 ($_VAT_RATES[0]['amount'] > 0) ? "&euro;  ".number_format($_VAT_RATES[0]['amount'],2,',','.') : "&nbsp;",
	 ($_VAT_RATES[0]['amount'] > 0) ? $_VAT_RATES[0]['vatinfo']['percentage'] : "&nbsp;",
	 ($_VAT_RATES[0]['amount'] > 0) ? $_VAT_RATES[0]['vatinfo']['percentage']."%" : "&nbsp;",
	 ($_VAT_RATES[0]['amount'] > 0) ? "&euro;  ".number_format($_VAT_RATES[0]['vat'],2,',','.') : "&nbsp;",
	 ($_VAT_RATES[0]['amount'] > 0) ? $_VAT_RATES[0]['exemptions'] : "&nbsp;",

	 ($_VAT_RATES[1]['amount'] > 0) ? "&euro;  ".number_format($_VAT_RATES[1]['amount'],2,',','.') : "&nbsp;",
	 ($_VAT_RATES[1]['amount'] > 0) ? $_VAT_RATES[1]['vatinfo']['percentage'] : "&nbsp;",
	 ($_VAT_RATES[1]['amount'] > 0) ? $_VAT_RATES[1]['vatinfo']['percentage']."%" : "&nbsp;",
	 ($_VAT_RATES[1]['amount'] > 0) ? "&euro;  ".number_format($_VAT_RATES[1]['vat'],2,',','.') : "&nbsp;",
	 ($_VAT_RATES[1]['amount'] > 0) ? $_VAT_RATES[1]['exemptions'] : "&nbsp;",

	 ($_VAT_RATES[2]['amount']  > 0) ? "&euro;  ".number_format($_VAT_RATES[2]['amount'],2,',','.') : "&nbsp;",
	 ($_VAT_RATES[2]['amount']  > 0) ? $_VAT_RATES[2]['vatinfo']['percentage'] : "&nbsp;",
	 ($_VAT_RATES[2]['amount']  > 0) ? $_VAT_RATES[2]['vatinfo']['percentage']."%" : "&nbsp;",
	 ($_VAT_RATES[2]['amount']  > 0) ? "&euro;  ".number_format($_VAT_RATES[2]['vat'],2,',','.') : "&nbsp;",
	 ($_VAT_RATES[2]['amount']  > 0) ? $_VAT_RATES[2]['exemptions'] : "&nbsp;",

	 $_TOT_EXEMPTIONS ? number_format($_TOT_EXEMPTIONS,2,',','.') : "&nbsp;",

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


	 $_COMPANY_PROFILE['addresses']['registered_office']['address'],
	 $_COMPANY_PROFILE['addresses']['registered_office']['zip'],
	 $_COMPANY_PROFILE['addresses']['registered_office']['city'],
	 $_COMPANY_PROFILE['addresses']['registered_office']['prov'],
	 $_COMPANY_PROFILE['addresses']['registered_office']['country'],

	 $_COMPANY_PROFILE['addresses']['registered_office']['phones'][0]['number'],
	 $_COMPANY_PROFILE['addresses']['registered_office']['phones'][1]['number'],

	 $_COMPANY_PROFILE['addresses']['registered_office']['fax'][0]['number'],
	 $_COMPANY_PROFILE['addresses']['registered_office']['fax'][1]['number'],

	 $_COMPANY_PROFILE['addresses']['registered_office']['cells'][0]['number'],
	 $_COMPANY_PROFILE['addresses']['registered_office']['cells'][1]['number'],

	 $_COMPANY_PROFILE['addresses']['registered_office']['emails'][0]['email'],
	 $_COMPANY_PROFILE['addresses']['registered_office']['emails'][1]['email'],



	 $_REFERENCE['name'], $_REFERENCE['type'], $_REFERENCE['phone'], $_REFERENCE['email'],
	 $itemInfo['ext_docref'],
	 ($itemInfo['docref_ap'] && $itemInfo['docref_id']) ? $itemInfo['docref_name'] : $itemInfo['aliasname'],
	 $itemInfo['order_ref'] ? str_replace("Ordine ", "", $itemInfo['order_ref']['name']) : "",
	 $itemInfo['ddt_ref'] ? str_replace("D.D.T. ", "", $itemInfo['ddt_ref']['name']) : "",
	 $itemInfo['doc_ref'] ? $itemInfo['doc_ref']['name'] : "",
	 $itemInfo['agent_name'],

	 ($itemInfo['tot_paid']>0) ? number_format($itemInfo['tot_paid'],2,',','.') : "",
	 number_format($itemInfo['rest_to_pay'],2,',','.'),

	 /* Riferimenti scheda officina */
	 $vehicleModel, $vehicleNumplate, $vehicleVIN, $rifSchedaOfficina, $vehicleKM ,

	 /* Rif. PCB */
	 $PCBIntervRef,
	 $_USDRATIO, // rata cambio euro/dollaro 

	 /* Rif. PA */
	 $itemInfo['pa_docnum'], $itemInfo['pa_cig'], $itemInfo['pa_cup'], $subjectInfo['pacode'], 

	 /* PayPal */
	 sprintf('%.2f',$itemInfo['rest_to_pay'])
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

 // PARSE ELEMENTS
 if(isset($_PARAMS['parseelements']) && ($_PARAMS['parseelements']))
 {
  $_KEYS = array('code','vencode','serialnumber','lot','name','desc','qty','extraqty','qty_sent','qty_downloaded',
	'vendor_price','sale_price','price','priceadjust','ritaccapply','ccpapply','metric_length','metric_width','metric_hw','metric_eqp',
	'discount','discount2','discount3','vatrate','vattype','units','plbaseprice','plmrate','pldiscperc','vendor_name',
	'variant_coltint','variant_sizmis','mancode','weight','weightunits','baseprice','listbaseprice','listdiscperc','listmrate',
	'discountedprice','thumb_img','unitprice','unitprice_vi','amount','vat','total','profit','margin','brand_name');

  // Prepare HTML content for DomDocument
  $htmlContent = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd"><html><body>';
  $htmlContent.= $contents;
  $htmlContent.= '</body></html>';

  $_DOC = new DomDocument();
  $_DOC->validateOnParse = true;
  $ret = @$_DOC->loadHTML($htmlContent);
  $_DOC->preserveWhiteSpace = false;

  do {

  $table = $_DOC->getElementById('itemlist');
  if(!$table) break;
  
  // GET ROWS
  $rows = $table->getElementsByTagName('tr');
  if(!$rows->length) break;

  $_FIRST_ROW = $rows->item(0);
  $_SECOND_ROW = ($rows->length > 1) ? $rows->item(1) : null;
  $_LAST_ROW = ($rows->length > 2) ? $rows->item(2) : null;
  
  if(!$_SECOND_ROW || !$_LAST_ROW) break;

  $thcells = $_FIRST_ROW->getElementsByTagName('th');
  

  // INSERT ELEMENTS
  for($i=0; $i < count($itemInfo['elements']); $i++)
  {
   $clone = $_SECOND_ROW->cloneNode(true);
   $cells = $clone->getElementsByTagName('td');
   $data = $itemInfo['elements'][$i];

   for($c=0; $c < $cells->length; $c++)
   {
	$th = $thcells->item($c);
	$td = $cells->item($c);
	$key = $th->getAttribute('id');
	$format = $td->getAttribute('format') ? $td->getAttribute('format') : 'string';
    $decimals = $td->getAttribute('decimals') ? $td->getAttribute('decimals') : 2;

    if(($data['type'] == 'note') || ($data['type'] == 'message'))
    {
     if($key == 'name')
	  $td->nodeValue = strip_tags($data['desc']);
    }
    else
    {
     switch($key)
     {
      default : {
	   $value = $data[$key];
	   switch($format)
	   {
	    case 'currency' : 	$value = $value ? number_format($value, $decimals) : '&nbsp;'; break;
	    case 'percentage' : 	$value = $value ? $value."%" : '&nbsp;'; break;
	    case 'percentage currency' : case 'currency percentage' : {
		   if(strpos($value, "%") == false)
		    $value = $value ? number_format($value, $decimals) : '&nbsp;';
		   else
		    $value = $value ? $value : '&nbsp;';
		  } break;
	   }

	   $td->nodeValue = $value;
	  } break;
     }
    }
   } // EOF - for $c - cells
   $_FIRST_ROW->parentNode->insertBefore($clone, $_LAST_ROW);
  } // EOF - for $i - elements
  
  } while(0); // EOF do

  $contents = $_DOC->saveHTML();
  $contents = substr($contents, strpos($contents, '<body>')+6, -15);
 }
 // EOF - PARSE ELEMENTS

 $_CONTENTS = $contents;
 return $contents;
}


