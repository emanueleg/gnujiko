<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 22-08-2013
 #PACKAGE: pos
 #DESCRIPTION: pos-receipts parser
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function gnujikocontentparser_posreceipts_info($sessid, $shellid)
{
 $info = array('name' => "POS - Ricevute e scontrini");
 $keys = array(
	 "DOC_TYPE" => "Tipo di documento", 
	 "DOC_DATE" => "Data documento", 
	 "DOC_NUM" => "Numero documento", 
	 "DOC_YEAR" => "Anno documento",
	 "DOC_NUM_PLUS_YEAR" => "Num. doc / Anno",
	 "DATE" => "Data stampa",
	 "TIME" => "Ora stampa",
	 "DOC_AMOUNT" => "Imponibile",
	 "DOC_VAT" => "IVA",
	 "DOC_TOT" => "Totale",
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
	 "CO_WEBSITE" => "Sito web (della vs azienda)"
	);
 return array('info'=>$info, 'keys'=>$keys);
}

function gnujikocontentparser_posreceipts_parse($_CONTENTS, $_PARAMS, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_COMPANY_PROFILE;
 include_once($_BASE_PATH."include/company-profile.php");
 $contents = $_CONTENTS;
 $_CO = $_COMPANY_PROFILE;

 /* GET ORDER INFO */
 $ret = GShell("dynarc item-info -ap `commercialdocs` -id `".$_PARAMS['id']."` -extget `cdinfo,cdelements`",$sessid,$shellid);
 if(!$ret['error'])
  $docInfo = $ret['outarr'];

 /* GET DOCUMENT CAT INFO */
 $ret = GShell("dynarc cat-info -ap commercialdocs -id `".$docInfo['cat_id']."`",$sessid,$shellid);
 if(!$ret['error'])
  $docInfo['catinfo'] = $ret['outarr'];

 /* GET CAT TAG */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT tag,parent_id FROM dynarc_commercialdocs_categories WHERE id='".$itemInfo['cat_id']."'");
 if($db->Read())
 {
  if($db->record['parent_id'])
  {
   $db->RunQuery("SELECT tag FROM dynarc_commercialdocs_categories WHERE id='".$db->record['parent_id']."'");
   $db->Read();
   $_CAT_TAG = $db->record['tag']; 
  }
  else
   $_CAT_TAG = $db->record['tag'];
 }
 $db->Close();

 $singleRowKeys = array("{CODE}","{SN}","{LOT}","{NAME}","{DESCRIPTION}","{QTY}","{UNITS}","{PRICE}","{VATRATE}","{VAT}","{TOTAL}");

 /* Detect first TR for injection phase */
 while(list($i,$k) = each($singleRowKeys))
 {
  $p = strpos($contents,$k);
  if($p !== false)
   break;
 }
 if($p !== false)
 {
  $firstTRsp = strbipos($contents,"<TR",$p);
  $firstTRep = stripos($contents,"TR>",$p)+3;
  $firstTR = substr($contents,$firstTRsp,($firstTRep-$firstTRsp));
  $startInsertPoint = $firstTRsp;
  $endInsertPoint = $firstTRep;
 }

 $cnts = "";

 /* Elements injection */
 for($c=0; $c < count($docInfo['elements']); $c++)
 {
  $el = $docInfo['elements'][$c];
  $vat = $el['price'] ? (($el['price']/100)*$el['vatrate']) : 0;
  $values = array(
	 $el['code'],
	 $el['serialnumber'],
	 $el['lot'],
	 $el['name'],
	 $el['desc'],
	 $el['qty'],
	 $el['units'],
	 number_format($el['price'],2,",","."),
	 $el['vatrate'],
	 number_format($el['vat'],2,",","."),
	 number_format($el['amount']+$el['vat'],2,",",".")
  );

  if($firstTR)
  {
   $rowstr = $firstTR;
   $cnts.= str_replace($singleRowKeys,$values,$rowstr);
  }
 }

 /* Output */
 $sS = substr($contents,0,$startInsertPoint);
 $eS = substr($contents,$endInsertPoint);
 $contents = $sS.$cnts.$eS;



 $keys = array("{DOC_TYPE}", "{DOC_DATE}" , "{DOC_NUM}", "{DOC_YEAR}", "{DOC_NUM_PLUS_YEAR}",
	 "{DATE}", "{TIME}", 
	 "{DOC_AMOUNT}", "{DOC_VAT}", "{DOC_TOTAL}",
	 "{COMPANY-NAME}", "{CO_ADDR}", "{CO_ZIP}", "{CO_CITY}", "{CO_PROV}", "{CO_CC}", "{CO_VATNUMBER}", "{CO_TAXCODE}", "{CO_PHONE}", "{CO_PHONE2}", 
	 "{CO_FAX}", "{CO_FAX2}", "{CO_CELL}", "{CO_CELL2}", "{CO_EMAIL}", "{CO_EMAIL2}", "{CO_WEBSITE}"
	);

switch($_CAT_TAG)
{
 case 'PREEMPTIVES' : {
	 $catName = "Preventivo"; 
	 switch($docInfo['tag'])
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
	 if($docInfo['tag'] != "DEFERRED")
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

 $vals = array(
	 $catName,
	 date('d/m/Y',$docInfo['ctime']), 
	 $docInfo['code_num'].($docInfo['code_ext'] ? "/".$docInfo['code_ext'] : ""),
	 date('Y',$docInfo['ctime']),
	 $docInfo['code_num'].($docInfo['code_ext'] ? "/".$docInfo['code_ext'] : "")."/".date('Y',$docInfo['ctime']),
	 date('d/m/Y'),
	 date('H:i'),
	 number_format($docInfo['amount'],2,",","."),
	 number_format($docInfo['vat'],2,",","."),
	 number_format($docInfo['total'],2,",","."),

	 $_CO['name'],
	 $_CO['addresses']['headquarters']['address'] ? $_CO['addresses']['headquarters']['address'] : $_CO['addresses']['registered_office']['address'],
	 $_CO['addresses']['headquarters']['zip'] ? $_CO['addresses']['headquarters']['zip'] : $_CO['addresses']['registered_office']['zip'],
	 $_CO['addresses']['headquarters']['city'] ? $_CO['addresses']['headquarters']['city'] : $_CO['addresses']['registered_office']['city'],
	 $_CO['addresses']['headquarters']['prov'] ? $_CO['addresses']['headquarters']['prov'] : $_CO['addresses']['registered_office']['prov'],
	 $_CO['addresses']['headquarters']['country'] ? $_CO['addresses']['headquarters']['country'] : $_CO['addresses']['registered_office']['country'],

	 $_CO['vatnumber'], $_CO['taxcode'], 

	 $_CO['addresses']['headquarters']['phones'][0]['number'] ? $_CO['addresses']['headquarters']['phones'][0]['number'] : $_CO['addresses']['registered_office']['phones'][0]['number'],
	 $_CO['addresses']['headquarters']['phones'][1]['number'] ? $_CO['addresses']['headquarters']['phones'][1]['number'] : $_CO['addresses']['registered_office']['phones'][1]['number'],

	 $_CO['addresses']['headquarters']['fax'][0]['number'] ? $_CO['addresses']['headquarters']['fax'][0]['number'] : $_CO['addresses']['registered_office']['fax'][0]['number'],
	 $_CO['addresses']['headquarters']['fax'][1]['number'] ? $_CO['addresses']['headquarters']['fax'][1]['number'] : $_CO['addresses']['registered_office']['fax'][1]['number'],

	 $_CO['addresses']['headquarters']['cells'][0]['number'] ? $_CO['addresses']['headquarters']['cells'][0]['number'] : $_CO['addresses']['registered_office']['cells'][0]['number'],
	 $_CO['addresses']['headquarters']['cells'][1]['number'] ? $_CO['addresses']['headquarters']['cells'][1]['number'] : $_CO['addresses']['registered_office']['cells'][1]['number'],

	 $_CO['addresses']['headquarters']['emails'][0]['email'] ? $_CO['addresses']['headquarters']['emails'][0]['email'] : $_CO['addresses']['registered_office']['emails'][0]['email'],
	 $_CO['addresses']['headquarters']['emails'][1]['email'] ? $_CO['addresses']['headquarters']['emails'][1]['email'] : $_CO['addresses']['registered_office']['emails'][1]['email'],

	 $_CO['website']
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


