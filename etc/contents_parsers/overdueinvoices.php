<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-08-2014
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Overdue invoices parser.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

function gnujikocontentparser_overdueinvoices_info($sessid, $shellid)
{
 $info = array('name' => "Fatture scadute");
 $keys = array(
	 "EXPIRY_DATE" => "Data scadenza",
	 "SUBJECT_NAME" => "Nome cliente",
	 "DOC_LINK" => "Link al documento",
	 "DOC_DATE" => "Data documento",
	 "DOC_NUM" => "Numero documento",
	 "DOC_NAME" => "Nome documento",
	 "RATE_AMOUNT" => "Importo rata",
	 "DOC_AMOUNT" => "Imponibile documento",
	 "DOC_VAT" => "IVA documento",
	 "DOC_TOTAL" => "Totale documento IVA inclusa",
	 "DOC_RITACC" => "Importo rit. d'acconto",
	 "DOC_CCP" => "Importo contr. cassa previdenziale",
	 "DOC_RINPS" => "Importo rivalsa INPS",
	 "DOC_ENASARCO" => "Importo enasarco",
	 "TOT_AMOUNT" => "Tot. importi da pagare"
	);
 return array('info'=>$info, 'keys'=>$keys);
}

function gnujikocontentparser_overdueinvoices_parse($contents, $params, $sessid, $shellid)
{
 global $_ABSOLUTE_URL, $_BASE_PATH;

 $ret = GShell("mmr schedule -from lastmonth -to today --only-invoices --only-expired",$sessid,$shellid);
 $list = $ret['outarr']['results'];
 $now = time();
 $_DOCUMENTS = array();
 $_TOT_AMOUNT = 0;
 $db = new AlpaDatabase();
 for($c=0; $c < count($list); $c++)
 {
  $item = $list[$c];
  if(strtotime($item['expire_date']) < $now)
  {
   if($item['payment_date'] && ($item['payment_date'] != "0000-00-00"))
	continue;
   if(!$_DOCUMENTS[$item['doc_id']])
   {
	$a = array('id'=>$item['doc_id'], 'name'=>$item['doc_name'], 'subject_id'=>$item['subject_id'], 'subject_name'=>$item['subject_name'], 'amount'=>$item['incomes'], 'expire_date'=>$item['expire_date'], 'payment_mode'=>$item['payment_mode'], 'payment_mode_name'=>$item['payment_mode_name']);
	// get doc info //
	$db->RunQuery("SELECT * FROM dynarc_commercialdocs_items WHERE id='".$item['doc_id']."'");
	$db->Read();
	$a['doc_date'] = $db->record['ctime'];
	$a['doc_num'] = $db->record['code_num'].($db->record['code_ext'] ? "/".$db->record['code_ext'] : "");
	$a['doc_amount'] = $db->record['amount'];
	$a['doc_vat'] = $db->record['vat'];
	$a['doc_total'] = $db->record['tot_netpay'];
	$a['doc_ritacc'] = $db->record['tot_rit_acc'];
	$a['doc_ccp'] = $db->record['tot_ccp'];
	$a['doc_rinps'] = $db->record['tot_rinps'];
	$a['doc_enasarco'] = $db->record['tot_enasarco'];
	$_DOCUMENTS[$item['doc_id']] = $a;
	$_TOT_AMOUNT+= $a['amount'];
   }
  }
 }
 $db->Close();
 reset($_DOCUMENTS);

 $singleRowKeys = array(
	'{EXPIRY_DATE}',
	'{SUBJECT_NAME}',
	'{DOC_LINK}',
	'{DOC_DATE}',
	'{DOC_NUM}',
	'{DOC_NAME}',
	'{RATE_AMOUNT}',
	'{DOC_AMOUNT}',
	'{DOC_VAT}',
	'{DOC_TOTAL}',
	'{DOC_RITACC}',
	'{DOC_CCP}',
	'{DOC_RINPS}',
	'{DOC_ENASARCO}'
 );
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

 /* Injection phase */
 while(list($k,$v) = each($_DOCUMENTS))
 {
  $docInfo = $v;
  $values = array(
	$docInfo['expire_date'], 
	$docInfo['subject_name'], 
	"<a href='".$_ABSOLUTE_URL."GCommercialDocs/docinfo.php?id=".$docInfo['id']."' target='GCD-".$docInfo['id']."'>".$docInfo['name']."</a>", 
	date('d/m/Y',strtotime($docInfo['doc_date'])),
	$docInfo['doc_num'],
	$docInfo['name'], 
	number_format($docInfo['amount'],2,',','.'),
	number_format($docInfo['doc_amount'],2,',','.'),
	number_format($docInfo['doc_vat'],2,',','.'),
	number_format($docInfo['doc_total'],2,',','.'),
	number_format($docInfo['doc_ritacc'],2,',','.'),
	number_format($docInfo['doc_ccp'],2,',','.'),
	number_format($docInfo['doc_rinps'],2,',','.'),
	number_format($docInfo['doc_enasarco'],2,',','.')
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

 /* REPLACE DEFAULT DOCUMENT KEYS */
 $docKeys = array("{TOT_AMOUNT}");
 $docVals = array(number_format($_TOT_AMOUNT,2,',','.'));
 $contents = str_replace($docKeys,$docVals,$contents);


 return $contents;
}
//-------------------------------------------------------------------------------------------------------------------//



