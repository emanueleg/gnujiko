<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Request
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 20-04-2017
 #PACKAGE: backoffice2
 #DESCRIPTION: Gnujiko BackOffice commands
 #VERSION: 2.7beta
 #CHANGELOG: 20-04-2017 : Aggiunto totale profitti su funzioni sales-summary e orders-summary.
			 16-03-2017 : Aggiunta funzione orders-summary.
			 26-02-2017 : Aggiornata funzione get-incomes-schedule integrando avvisi di pagamento.
			 04-01-2017 : Bug fix su funzione get-incomes-schedule che non includeva le sottocategorie delle fatture.
			 28-03-2015 : Aggiornata funzione sales-summary - aggiunto ricevute fiscali.
			 21-03-2015 : Aggiornata funzione sales-summary.
			 12-02-2014 : Bug fix sulle date delle scadenze su funzione export-riba-to-cbi
 #DEPENDS: 
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

function shell_backoffice($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'get-sales' : return backoffice_analysisOfSales($args, $sessid, $shellid); break;
  case 'get-incomes-schedule' : return backoffice_incomesSchedule($args, $sessid, $shellid); break;
  case 'get-expenses-schedule' : return backoffice_expensesSchedule($args, $sessid, $shellid); break;

  /* RIBA */
  case 'generate-riba-summary' : return backoffice_generateRiBaSummary($args, $sessid, $shellid); break;
  case 'generate-riba' : return backoffice_generateRiBa($args, $sessid, $shellid); break;
  case 'get-riba' : case 'riba-list' : return backoffice_ribaList($args, $sessid, $shellid); break;
  case 'riba-info' : return backoffice_ribaInfo($args, $sessid, $shellid); break;
  case 'edit-riba' : return backoffice_editRiBa($args, $sessid, $shellid); break;
  case 'delete-riba' : return backoffice_deleteRiBa($args, $sessid, $shellid); break;
  case 'edit-riba-element' : return backoffice_editRiBaElement($args, $sessid, $shellid); break;
  case 'remove-riba-elements' : return backoffice_removeRiBaElements($args, $sessid, $shellid); break;
  case 'export-riba-to-excel' : return backoffice_exportRibaToExcel($args, $sessid, $shellid); break;
  case 'export-riba-to-cbi' : return backoffice_exportRibaToCBI($args, $sessid, $shellid); break;

  /* LOTTI */
  case 'add-into-lot' : return backoffice_addIntoLot($args, $sessid, $shellid); break;
  case 'get-lots' : case 'lot-list' : return backoffice_lotList($args, $sessid, $shellid); break;

  /* RIEPILOGO VENDITE */
  case 'sales-summary' : return backoffice_salesSummary($args, $sessid, $shellid); break;
  case 'orders-summary' : return backoffice_ordersSummary($args, $sessid, $shellid); break;

  default : return backoffice_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_analysisOfSales($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $_INVOICES_CAT = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   // daily report
   case '-date' : {$dailyDate=$args[$c+1]; $c++;} break;

   // multi report
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;

   case '--verbose' : $verbose=true; break;
  }

 if($dailyDate)
 {
  $dateFrom = $dailyDate;
  $dateTo = date('Y-m-d',strtotime("+1 day",strtotime($dateFrom)));
 }
 if(!$dateFrom)
  $dateFrom = date('Y-m')."-01";
 if(!$dateTo)
  $dateTo = date('Y-m-d',strtotime("+1 month",strtotime($dateFrom)));

 $out.= "Report of sales from ".date('d.m.Y',strtotime($dateFrom))." to ".date('d.m.Y',strtotime($dateTo))."\n";

 /* Prepara l'outarr diviso per date */
 $from = strtotime($dateFrom);
 $to = strtotime($dateTo);
 $date = $from;
 if(!$dailyDate)
 {
  $outArr['dates'] = array();
  while($date < $to)
  {
   $outArr['dates'][date('Ymd',$date)] = array(
	 "documents"=>array(), 
	 "totals"=>array("amount"=>0, "vat"=>0, "netpay"=>0, "rit_acc"=>0, "ccp"=>0, "rinps"=>0, "enasarco"=>0, "rebate"=>0, "goods"=>0, 
					 "discounted_goods"=>0, "expenses"=>0, "discount"=>0, "stamp"=>0, "cartage"=>0, "packing_charges"=>0, "collection_charges"=>0)
	);
   $date = strtotime("+1 day",$date);
  }
 }
 else
  $outArr['documents'] = array();

 /* Get invoice category and all subcategories */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_commercialdocs_categories WHERE tag='INVOICES' AND trash='0' AND parent_id='0' LIMIT 1");
 $db->Read();
 $catId = $db->record['id'];
 $_INVOICES_CAT[] = $catId;
 // select all sub-categories
 $db->RunQuery("SELECT id FROM dynarc_commercialdocs_categories WHERE parent_id='".$catId."' AND trash='0'");
 while($db->Read())
  $_INVOICES_CAT[] = $db->record['id'];
 $db->Close();

 /* EXEC QUERY */
 $db = new AlpaDatabase();

 $defGets = "id,name,ctime,code_num,code_ext,tag,subject_id,subject_name,status,amount,vat,total,tot_rit_acc,tot_ccp,tot_rinps,tot_enasarco,tot_netpay,
	rebate,tot_goods,discounted_goods,tot_expenses,tot_discount,stamp,cartage,packing_charges,collection_charges";
 $defGetsArr = explode(",",$defGets);

 // reset totals
 $outArr['tot_documents'] = 0;
 $outArr['tot_amount'] = 0;
 $outArr['tot_vat'] = 0;
 $outArr['tot_total'] = 0;
 $outArr['tot_netpay'] = 0;
 $outArr['tot_rit_acc'] = 0;
 $outArr['tot_ccp'] = 0;
 $outArr['tot_rinps'] = 0;
 $outArr['tot_enasarco'] = 0;
 $outArr['tot_rebate'] = 0;
 $outArr['tot_goods'] = 0;
 $outArr['tot_discounted_goods'] = 0;
 $outArr['tot_expenses'] = 0;
 $outArr['tot_discount'] = 0;
 $outArr['tot_stamp'] = 0;
 $outArr['tot_cartage'] = 0;
 $outArr['tot_packing_charges'] = 0;
 $outArr['tot_collection_charges'] = 0;

 $qry = "SELECT ".$defGets." FROM dynarc_commercialdocs_items WHERE ctime>='".$dateFrom."' AND ctime<'".$dateTo."' AND trash='0'";
 if(count($_INVOICES_CAT) == 1)
  $qry.= " AND cat_id='".$_INVOICES_CAT[0]."'";
 else
 {
  $qry.= " AND (cat_id='".$_INVOICES_CAT[0]."'";
  for($c=1; $c < count($_INVOICES_CAT); $c++)
   $qry.= " OR cat_id='".$_INVOICES_CAT[$c]."'";
  $qry.= ")";
 }
 $qry.= " ORDER BY ctime,code_num ASC";

 $db->RunQuery($qry);
 while($db->Read())
 {
  $a = array();
  for($c=0; $c < count($defGetsArr); $c++)
   $a[$defGetsArr[$c]] = $db->record[$defGetsArr[$c]];

  if(!$dailyDate)
  {
   $date = substr($a['ctime'],0,10);
   $date = str_replace("-","",$date);
   $outArr['dates'][$date]['documents'][] = $a;
   $outArr['dates'][$date]['totals']['amount']+= $a['amount'];
   $outArr['dates'][$date]['totals']['vat']+= $a['vat'];
   $outArr['dates'][$date]['totals']['total']+= $a['total'];
   $outArr['dates'][$date]['totals']['netpay']+= $a['tot_netpay'];
   $outArr['dates'][$date]['totals']['rit_acc']+= $a['tot_rit_acc'];
   $outArr['dates'][$date]['totals']['ccp']+= $a['tot_ccp'];
   $outArr['dates'][$date]['totals']['rinps']+= $a['tot_rinps'];
   $outArr['dates'][$date]['totals']['enasarco']+= $a['tot_enasarco'];
   $outArr['dates'][$date]['totals']['rebate']+= $a['tot_rebate'];
   $outArr['dates'][$date]['totals']['goods']+= $a['tot_goods'];
   $outArr['dates'][$date]['totals']['discounted_goods']+= $a['tot_discounted_goods'];
   $outArr['dates'][$date]['totals']['expenses']+= $a['tot_expenses'];
   $outArr['dates'][$date]['totals']['discount']+= $a['tot_discount'];
   $outArr['dates'][$date]['totals']['stamp']+= $a['stamp'];
   $outArr['dates'][$date]['totals']['cartage']+= $a['cartage'];
   $outArr['dates'][$date]['totals']['packing_charges']+= $a['packing_charges'];
   $outArr['dates'][$date]['totals']['collection_charges']+= $a['collection_charges'];
  }
  else
   $outArr['documents'][] = $a;
  
  $outArr['tot_documents']++;
  $outArr['tot_amount']+= $a['amount'];
  $outArr['tot_vat']+= $a['vat'];
  $outArr['tot_total']+= $a['total'];
  $outArr['tot_netpay']+= $a['tot_netpay'];
  $outArr['tot_rit_acc']+= $a['tot_rit_acc'];
  $outArr['tot_ccp']+= $a['tot_ccp'];
  $outArr['tot_rinps']+= $a['tot_rinps'];
  $outArr['tot_enasarco']+= $a['tot_enasarco'];
  $outArr['tot_rebate']+= $a['tot_rebate'];
  $outArr['tot_goods']+= $a['tot_goods'];
  $outArr['tot_discounted_goods']+= $a['tot_discounted_goods'];
  $outArr['tot_expenses']+= $a['tot_expenses'];
  $outArr['tot_discount']+= $a['tot_discount'];
  $outArr['tot_stamp']+= $a['tot_stamp'];
  $outArr['tot_cartage']+= $a['cartage'];
  $outArr['tot_packing_charges']+= $a['packing_charges'];
  $outArr['tot_collection_charges']+= $a['collection_charges'];
 }
 $db->Close();

 if($verbose)
 {
  $out.= "Num of documents: ".$outArr['tot_documents']."\n";
  $out.= "Tot amount: ".number_format($outArr['tot_amount'],2,",",".")." &euro;\n";
  $out.= "Tot VAT: ".number_format($outArr['tot_vat'],2,",",".")." &euro;\n";
  $out.= "Tot Pay: ".number_format($outArr['tot_netpay'],2,",",".")." &euro;\n";
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_incomesSchedule($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array("results"=>array());
 $_PAYMENT_MODES = array();
 $orderBy = "expire_date ASC";
 $today = date("Y-m-d");

 $_INVOICE_CATIDS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;

   /* filters */
   case '-subjid' : case '-subjectid' : case '-subject-id' : {$subjectId=$args[$c+1]; $c++;} break;
   case '-subject' : case '-subject-name' : {$subjectName=$args[$c+1]; $c++;} break;
   case '-paymentmode' : {$paymentMode=$args[$c+1]; $c++;} break; /* RB=RiBa, BB=bonifico, RD=Rimessa diretta */
   case '--filter-by-docdate' : $filterByDocDate = true; break;
   case '--get-only-invoices' : case '--only-invoices' : $onlyInvoices=true; break;

   case '--only-expired' : $onlyExpired=true; break;
   case '--only-expiring' : $onlyExpiring=true; break;
   case '--only-paid' : $onlyPaid=true; break;

   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : case '--limit' : {$limit=$args[$c+1]; $c++;} break;

   case '--verbose' : $verbose=true; break;
  }

 if($dateFrom == "always")
  $dateFrom = "1970-01-01";
 else if(!$dateFrom)
  $dateFrom = date('Y-m')."-01";

 if($dateTo == "forever")
  $dateTo = date('Y-m-d',strtotime("+12 year"));
 else if(!$dateTo)
  $dateTo = date('Y-m-d',strtotime("+1 month",strtotime($dateFrom)));

 /* LISTA DI TUTTE LE MODALITA' DI PAGAMENTO */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id,name,type FROM payment_modes WHERE 1 ORDER BY id ASC");
 while($db->Read())
 {
  $_PAYMENT_MODES[$db->record['id']] = array("name"=>$db->record['name'], "type"=>$db->record['type']);
 }
 $db->Close();

 $outArr['tot_amount'] = 0;
 $outArr['tot_expired'] = 0;
 $outArr['tot_expiring'] = 0;
 
 $catQry = "";

 if($onlyInvoices)
 {
  // Get invoices categories
  $ret = GShell("dynarc cat-info -ap commercialdocs -tag INVOICES",$sessid,$shellid);
  if(!$ret['error'])
  {
   $invoiceCatInfo = $ret['outarr'];
   $_INVOICE_CATIDS[] = $invoiceCatInfo['id'];
   $ret = GShell("dynarc cat-list -ap commercialdocs -parent '".$invoiceCatInfo['id']."'",$sessid,$shellid);
   if(!$ret['error'])
   {
    $invoiceCatList = $ret['outarr'];
	for($c=0; $c < count($invoiceCatList); $c++)
	 $_INVOICE_CATIDS[] = $invoiceCatList[$c]['id'];
   }
  }

  // Get paymentnotice categories
  $ret = GShell("dynarc cat-info -ap commercialdocs -tag PAYMENTNOTICE",$sessid,$shellid);
  if(!$ret['error'])
  {
   $_INVOICE_CATIDS[] = $ret['outarr']['id'];
   $ret = GShell("dynarc cat-list -ap commercialdocs -parent '".$ret['outarr']['id']."'",$sessid,$shellid);
   if(!$ret['error'])
   {
    $invoiceCatList = $ret['outarr'];
	for($c=0; $c < count($ret['outarr']); $c++)
	 $_INVOICE_CATIDS[] = $ret['outarr'][$c]['id'];
   }
  }


  if(count($_INVOICE_CATIDS))
  {
   $catQry = " AND (cat_id='".$_INVOICE_CATIDS[0]."'";
   for($c=1; $c < count($_INVOICE_CATIDS); $c++)
	$catQry.= " OR cat_id='".$_INVOICE_CATIDS[$c]."'";
   $catQry.= ")";
  }

 }

 $db = new AlpaDatabase();
 if($filterByDocDate)
  $qry.= "doc_date>='".$dateFrom."' AND doc_date<'".$dateTo."'";
 else
  $qry.= "expire_date>='".$dateFrom."' AND expire_date<'".$dateTo."'";
 // get only incomes
 $qry.= " AND incomes>0 AND expenses=0";
 // filter by category
 /*if($catQry)
  $qry.= $catQry;*/
 // filter by subject
 if($subjectId)
  $qry.= " AND subject_id='".$subjectId."'";
 else if($subjectName)
  $qry.= " AND subject_name='".$db->Purify($subjectName)."'";

 if($onlyPaid)
  $qry.= " AND payment_date!='0000-00-00'";
 else if($onlyExpired)
  $qry.= " AND expire_date<'".date('Y-m-d')."' AND payment_date='0000-00-00'";
 else if($onlyExpiring)
  $qry.= " AND expire_date>='".date('Y-m-d')."' AND payment_date='0000-00-00'";

 /* --- EXEC QUERY --- */
 $db->RunQuery("SELECT * FROM dynarc_commercialdocs_mmr WHERE ".$qry." ORDER BY ".$orderBy.($limit ? " LIMIT ".$limit : ""));
 while($db->Read())
 {
  $a = array("id"=>$db->record['id'], "description"=>$db->record['description'], "amount"=>$db->record['incomes'], 
	"expire_date"=>$db->record['expire_date'], "payment_date"=>$db->record['payment_date'], "paid"=>false, "expired"=>false, "expiring"=>false);
  // check if is paid
  if(($a['payment_date'] != "0000-00-00") && ($a['payment_date'] != "1970-01-01"))
   $a['paid'] = true;
  else
   $a['payment_date'] = "";
  // check if expired
  if(!$a['paid'] && (strtotime($a['expire_date']) < time()))
   $a['expired'] = true;
  else if(!$a['paid'])
   $a['expiring'] = true;
  if($db->record['riba_id'])
  {
   // get riba info
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT name FROM dynarc_riba_items WHERE id='".$db->record['riba_id']."'");
   $db2->Read();
   $a['riba'] = array("id"=>$db->record['riba_id'], "name"=>$db2->record['name']);
   $db2->Close();
  }
  // get doc info
  $docGet = "id,cat_id,name,ctime,code_num,code_ext,subject_id,subject_name,payment_mode,bank_support,trash,conv_doc_id,group_doc_id";
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT ".$docGet." FROM dynarc_commercialdocs_items WHERE id='".$db->record['item_id']."'");
  $db2->Read();
  if($db2->record['trash'])
   continue;
  if($db2->record['conv_doc_id'] || $db2->record['group_doc_id'])
   continue;
  /*if($onlyInvoices && ($db2->record['cat_id'] != $invoiceCatInfo['id'])) //TODO: da rifare, includendo eventuali altre cat. di fatture.
   continue;*/

  if($onlyInvoices && !in_array($db2->record['cat_id'], $_INVOICE_CATIDS))
   continue;

  $a['docinfo'] = $db2->record;
  $a['docinfo']['payment_mode_name'] = $_PAYMENT_MODES[$a['docinfo']['payment_mode']]['name'];
  $a['docinfo']['payment_mode_type'] = $_PAYMENT_MODES[$a['docinfo']['payment_mode']]['type'];
  $db2->Close();
  // filter by payment mode
  if($paymentMode && ($a['docinfo']['payment_mode_type'] != $paymentMode))
   continue;

  $outArr['tot_amount']+= $a['amount'];
  if($a['paid'])
   $outArr['tot_paid']+= $a['amount'];
  else if($a['expired'])
   $outArr['tot_expired']+= $a['amount'];
  else if($a['expiring'])
   $outArr['tot_expiring']+= $a['amount'];

  $outArr['results'][] = $a;
 }
 $db->Close();

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_expensesSchedule($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array("results"=>array());
 $_PAYMENT_MODES = array();
 $orderBy = "expire_date ASC";
 $today = date("Y-m-d");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;

   /* filters */
   case '-subjid' : case '-subjectid' : case '-subject-id' : {$subjectId=$args[$c+1]; $c++;} break;
   case '-subject' : case '-subject-name' : {$subjectName=$args[$c+1]; $c++;} break;
   case '-paymentmode' : {$paymentMode=$args[$c+1]; $c++;} break; /* RB=RiBa, BB=bonifico, RD=Rimessa diretta */
   case '--filter-by-docdate' : $filterByDocDate = true; break;
   case '--get-only-invoices' : case '--only-invoices' : $onlyInvoices=true; break;

   case '--only-expired' : $onlyExpired=true; break;
   case '--only-expiring' : $onlyExpiring=true; break;
   case '--only-paid' : $onlyPaid=true; break;

   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : case '--limit' : {$limit=$args[$c+1]; $c++;} break;

   case '--verbose' : $verbose=true; break;
  }

 if($dateFrom == "always")
  $dateFrom = "1970-01-01";
 else if($dateFrom == "lastmonth")
  $dateFrom = date('Y-m',strtotime("-1 month", time()))."-01";
 else if(!$dateFrom)
  $dateFrom = date('Y-m')."-01";

 if($dateTo == "forever")
  $dateTo = date('Y-m-d',strtotime("+12 year"));
 else if($dateTo == "today")
  $dateTo = $today;
 else if(!$dateTo)
  $dateTo = date('Y-m-d',strtotime("+1 month",strtotime($dateFrom)));

 /* LISTA DI TUTTE LE MODALITA' DI PAGAMENTO */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id,name,type FROM payment_modes WHERE 1 ORDER BY id ASC");
 while($db->Read())
 {
  $_PAYMENT_MODES[$db->record['id']] = array("name"=>$db->record['name'], "type"=>$db->record['type']);
 }
 $db->Close();

 $outArr['tot_amount'] = 0;
 $outArr['tot_expired'] = 0;
 $outArr['tot_expiring'] = 0;
 
 $catQry = "";

 if($onlyInvoices)
 {
  // get invoices categories
  $ret = GShell("dynarc cat-info -ap commercialdocs -tag PURCHASEINVOICES",$sessid,$shellid);
  if(!$ret['error'])
  {
   $invoiceCatInfo = $ret['outarr'];
   $catQry = " AND (cat_id='".$invoiceCatInfo['id']."'";
   $ret = GShell("dynarc cat-list -ap commercialdocs -parent '".$invoiceCatInfo['id']."'",$sessid,$shellid);
   if(!$ret['error'])
   {
    $invoiceCatList = $ret['outarr'];
	for($c=0; $c < count($invoiceCatList); $c++)
	 $catQry.= " OR cat_id='".$invoiceCatList[$c]['id']."'";
   }
   $catQry.= ")";
  }
 }

 $db = new AlpaDatabase();
 if($filterByDocDate)
  $qry.= "doc_date>='".$dateFrom."' AND doc_date<'".$dateTo."'";
 else
  $qry.= "expire_date>='".$dateFrom."' AND expire_date<'".$dateTo."'";
 // get only incomes
 $qry.= " AND expenses>0 AND incomes=0";
 // filter by category
 /*if($catQry)
  $qry.= $catQry;*/
 // filter by subject
 if($subjectId)
  $qry.= " AND subject_id='".$subjectId."'";
 else if($subjectName)
  $qry.= " AND subject_name='".$db->Purify($subjectName)."'";

 if($onlyPaid)
  $qry.= " AND payment_date!='0000-00-00'";
 else if($onlyExpired)
  $qry.= " AND expire_date<'".date('Y-m-d')."' AND payment_date='0000-00-00'";
 else if($onlyExpiring)
  $qry.= " AND expire_date>='".date('Y-m-d')."' AND payment_date='0000-00-00'";

 /* --- EXEC QUERY --- */
 $db->RunQuery("SELECT * FROM dynarc_commercialdocs_mmr WHERE ".$qry." ORDER BY ".$orderBy.($limit ? " LIMIT ".$limit : ""));
 while($db->Read())
 {
  $a = array("id"=>$db->record['id'], "description"=>$db->record['description'], "amount"=>$db->record['expenses'], 
	"expire_date"=>$db->record['expire_date'], "payment_date"=>$db->record['payment_date'], "paid"=>false, "expired"=>false, "expiring"=>false);
  // check if is paid
  if(($a['payment_date'] != "0000-00-00") && ($a['payment_date'] != "1970-01-01"))
   $a['paid'] = true;
  else
   $a['payment_date'] = "";
  // check if expired
  if(!$a['paid'] && (strtotime($a['expire_date']) < time()))
   $a['expired'] = true;
  else if(!$a['paid'])
   $a['expiring'] = true;

  // get doc info
  $docGet = "id,cat_id,name,ctime,code_num,code_ext,subject_id,subject_name,payment_mode,bank_support,trash,conv_doc_id,group_doc_id";
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT ".$docGet." FROM dynarc_commercialdocs_items WHERE id='".$db->record['item_id']."'");
  $db2->Read();
  if($db2->record['trash'])
   continue;
  if($db2->record['conv_doc_id'] || $db2->record['group_doc_id'])
   continue;
  if($onlyInvoices && ($db2->record['cat_id'] != $invoiceCatInfo['id'])) //TODO: da rifare, includendo eventuali altre cat. di fatture.
   continue;
  $a['docinfo'] = $db2->record;
  $a['docinfo']['payment_mode_name'] = $_PAYMENT_MODES[$a['docinfo']['payment_mode']]['name'];
  $a['docinfo']['payment_mode_type'] = $_PAYMENT_MODES[$a['docinfo']['payment_mode']]['type'];
  $db2->Close();
  // filter by payment mode
  if($paymentMode && ($a['docinfo']['payment_mode_type'] != $paymentMode))
   continue;

  $outArr['tot_amount']+= $a['amount'];
  if($a['paid'])
   $outArr['tot_paid']+= $a['amount'];
  else if($a['expired'])
   $outArr['tot_expired']+= $a['amount'];
  else if($a['expiring'])
   $outArr['tot_expiring']+= $a['amount'];

  $outArr['results'][] = $a;
 }
 $db->Close();

 if($verbose)
 {
  $out.= "List of expenses from ".$dateFrom." to ".$dateTo."\n";
  for($c=0; $c < count($outArr['results']); $c++)
  {
   $item = $outArr['results'][$c];
   $out.= $item['expire_date']." - &euro; ".number_format($item['amount'],2,',','.')." ".$item['docinfo']['name']."\n";
  }
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_generateRiBaSummary($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array("results"=>array());
 
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ids' : {$ids=$args[$c+1]; $c++;} break;
  }

 $_IDS = explode(",",$ids);
 for($c=0; $c < count($_IDS); $c++)
 {
  $id = $_IDS[$c];
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM dynarc_commercialdocs_mmr WHERE id='".$id."'");
  $db->Read();
  $a = array("id"=>$db->record['id'], "description"=>$db->record['description'], "amount"=>$db->record['incomes'], 
	"expire_date"=>$db->record['expire_date'], "payment_date"=>$db->record['payment_date'], "paid"=>false, "expired"=>false, "expiring"=>false);
  // check if is paid
  if(($a['payment_date'] != "0000-00-00") && ($a['payment_date'] != "1970-01-01"))
   $a['paid'] = true;
  else
   $a['payment_date'] = "";
  // check if expired
  if(!$a['paid'] && (strtotime($a['expire_date']) < time()))
   $a['expired'] = true;
  else if(!$a['paid'])
   $a['expiring'] = true;
  // get doc info
  $docGet = "id,cat_id,name,ctime,code_num,code_ext,subject_id,subject_name,payment_mode,bank_support,trash";
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT ".$docGet." FROM dynarc_commercialdocs_items WHERE id='".$db->record['item_id']."'");
  $db2->Read();
  $a['docinfo'] = $db2->record;
  $db2->Close();
  // get subject info
  if($a['docinfo']['subject_id'])
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT taxcode,vatnumber FROM dynarc_rubrica_items WHERE id='".$a['docinfo']['subject_id']."'");
   $db2->Read();
   $a['docinfo']['subject_taxcode'] = $db2->record['taxcode'];
   $a['docinfo']['subject_vatnumber'] = $db2->record['vatnumber'];
   $db2->Close();
  }
  // get bank info
  if($a['docinfo']['bank_support'])
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT name,abi,cab,cin,cc,iban FROM dynarc_rubrica_banks WHERE id='".$a['docinfo']['bank_support']."'");
   $db2->Read();
   $a['bankinfo'] = $db2->record;
   $db2->Close();
  }
  $outArr['results'][] = $a;
  $db->Close();
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_generateRiBa($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $_ITEMS = array();
 $totAmount = 0;
 
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ids' : {$ids=$args[$c+1]; $c++;} break;
   case '-name' : case '-title' : {$title=$args[$c+1]; $c++;} break;
   case '-ctime' : case '-date' : {$ctime=$args[$c+1]; $c++;} break;
   case '-availtime' : case '-availdate' : {$availDate=$args[$c+1]; $c++;} break;
   case '-bankid' : {$bankId=$args[$c+1]; $c++;} break;
   case '-bankname' : {$bankName=$args[$c+1]; $c++;} break;
   case '-sia' : {$bankSia=$args[$c+1]; $c++;} break;
   case '-abi' : {$bankAbi=$args[$c+1]; $c++;} break;
   case '-cab' : {$bankCab=$args[$c+1]; $c++;} break;
   case '-cc' : {$bankCC=$args[$c+1]; $c++;} break;
  }

 $_IDS = explode(",",$ids);
 for($c=0; $c < count($_IDS); $c++)
 {
  $id = $_IDS[$c];
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM dynarc_commercialdocs_mmr WHERE id='".$id."'");
  $db->Read();
  $a = array("id"=>$db->record['id'], "description"=>$db->record['description'], "amount"=>$db->record['incomes'], 
	"expire_date"=>$db->record['expire_date'], "payment_date"=>$db->record['payment_date'], "paid"=>false, "expired"=>false, "expiring"=>false);
  if($db->record['riba_id'])
  {
   // get riba info
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT name FROM dynarc_riba_items WHERE id='".$db->record['riba_id']."'");
   $db2->Read();
   $a['riba'] = array("id"=>$db->record['riba_id'], "name"=>$db2->record['name']);
   $db2->Close();
  }
  // get doc info
  $docGet = "id,cat_id,name,ctime,code_num,code_ext,subject_id,subject_name,payment_mode,bank_support,trash";
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT ".$docGet." FROM dynarc_commercialdocs_items WHERE id='".$db->record['item_id']."'");
  $db2->Read();
  $a['docinfo'] = $db2->record;
  $db2->Close();
  // get subject info
  if($a['docinfo']['subject_id'])
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT taxcode,vatnumber FROM dynarc_rubrica_items WHERE id='".$a['docinfo']['subject_id']."'");
   $db2->Read();
   $a['docinfo']['subject_taxcode'] = $db2->record['taxcode'];
   $a['docinfo']['subject_vatnumber'] = $db2->record['vatnumber'];
   $db2->Close();
  }
  // get bank info
  if($a['docinfo']['bank_support'])
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT id,name,abi,cab,cin,cc,iban FROM dynarc_rubrica_banks WHERE id='".$a['docinfo']['bank_support']."'");
   $db2->Read();
   $a['bankinfo'] = $db2->record;
   $db2->Close();
  }
  
  $totAmount+= $a['amount'];
  $_ITEMS[] = $a;
  $db->Close();
 }

 /* GENERATE RIBA */
 $ret = GShell("dynarc new-item -ap 'riba' -name `".$title."` -ctime '".$ctime."' -set `availdate='".$availDate."',bank_id='"
	.$bankId."',bank_name='".$bankName."',bank_sia='".$bankSia."',bank_abi='".$bankAbi."',bank_cab='".$bankCab."',bank_cc='"
	.$bankCC."',elements_count='".count($_ITEMS)."',tot_amount='".$totAmount."'`",$sessid,$shellid);
 if($ret['error'])
  return array("message"=>"Error: unable to generate RiBa.\n".$ret['message'], "error"=>$ret['error']);

 $ribaInfo = $ret['outarr'];
 
 for($c=0; $c < count($_ITEMS); $c++)
 {
  $item = $_ITEMS[$c];
  $db = new AlpaDatabase();
  $db->RunQuery("INSERT INTO dynarc_riba_elements(riba_id,mmr_id,amount,expire_date,docref_id,docref_name,subject_id,subject_name,subject_taxcode,
	subject_vatnumber,cobnk_id,bank_id,bank_name,bank_abi,bank_cab,bank_cc) VALUES('".$ribaInfo['id']."','".$item['id']."','"
	.$item['amount']."','".$item['expire_date']."','".$item['docinfo']['id']."','".$db->Purify($item['docinfo']['name'])."','"
	.$item['docinfo']['subject_id']."','".$db->Purify($item['docinfo']['subject_name'])."','".$item['docinfo']['subject_taxcode']."','"
	.$item['docinfo']['subject_vatnumber']."','".$bankId."','".$item['bankinfo']['id']."','".$db->Purify($item['bankinfo']['name'])."','"
	.$item['bankinfo']['abi']."','".$item['bankinfo']['cab']."','".$item['bankinfo']['cc']."')");

  $db->RunQuery("UPDATE dynarc_commercialdocs_mmr SET riba_id='".$ribaInfo['id']."' WHERE id='".$item['id']."'");
  $db->Close();
 }

 $outArr = $ribaInfo;
 $out.= "Done!\nThe RiBa has been generated! ID=".$ribaInfo['id'];

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_ribaList($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array("count"=>0, "items"=>array());

 $orderBy = "ctime ASC";
 $limit = 0;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;

   // filters
   case '-bankid' : {$bankId=$args[$c+1]; $c++;} break;
   case '-subjid' : {$subjId=$args[$c+1]; $c++;} break;

   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;

   case '--verbose' : case '-verbose' : $verbose=true; break;
  }

 $db = new AlpaDatabase();
 $qry = "trash='0'";
 if($dateFrom)
  $qry.= " AND ctime>='".$dateFrom."'";
 if($dateTo)
  $qry.= " AND ctime<'".$dateTo."'";
 if($bankId)
  $qry.= " AND bank_id='".$bankId."'"; 

 $db->RunQuery("SELECT COUNT(*) FROM dynarc_riba_items WHERE ".$qry);
 $db->Read();
 $outArr['count'] = $db->record[0];
 $get = "id,name,ctime,availdate,bank_id,bank_name,bank_sia,bank_abi,bank_cab,bank_cc,elements_count,tot_amount";
 $getX = explode(",",$get);
 $db->RunQuery("SELECT ".$get." FROM dynarc_riba_items WHERE ".$qry." ORDER BY ".$orderBy.($limit ? " LIMIT ".$limit : ""));
 while($db->Read())
 {
  $a = array();
  for($k=0; $k < count($getX); $k++)
   $a[$getX[$k]] = $db->record[$getX[$k]];
  $outArr['items'][] = $a;
 }
 $db->Close();

 if($verbose)
 {
  $out.= "List of RiBa:\n";
  for($c=0; $c < count($outArr['items']); $c++)
  {
   $item = $outArr['items'][$c];
   $out.= ($c+1).". ".date('d/m/Y',strtotime($item['ctime']))." - ".$item['name']." (amount=".number_format($item['tot_amount'],2,',','.')." &euro;)\n";
  }
 }
 $out.= "\n".$outArr['count']." RiBa found.";

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_ribaInfo($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '--full-info' : $getFullInfo=true; break;
  }

 if(!$id)
  return array("message"=>"Error: you must specify the RiBa id.", "error"=>"INVALID_RIBA");

 $ret = GShell("dynarc item-info -ap riba -id '".$id."' -get `availdate,bank_id,bank_name,bank_sia,bank_abi,bank_cab,bank_cc,elements_count,tot_amount`",$sessid,$shellid);
 if($ret['error'])
  return array("message"=>"Error: unable to get RiBa informations.\n".$ret['message'], "error"=>$ret['error']);
 $ribaInfo = $ret['outarr'];
 $outArr = $ribaInfo;

 // get elements
 $outArr['elements'] = array();
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_riba_elements WHERE riba_id='".$id."' ORDER BY id ASC");
 while($db->Read())
 {
  $a = array("id"=>$db->record['id'], "mmr_id"=>$db->record['mmr_id'], "amount"=>$db->record['amount'], 
	"expire_date"=>$db->record['expire_date'], "docref_id"=>$db->record['docref_id'], "docref_name"=>$db->record['docref_name'],
	"subject_id"=>$db->record['subject_id'], "subject_name"=>$db->record['subject_name'], "subject_taxcode"=>$db->record['subject_taxcode'],
	"subject_vatnumber"=>$db->record['subject_vatnumber'], "cobnk_id"=>$db->record['cobnk_id'], "bank_id"=>$db->record['bank_id'],
	"bank_name"=>$db->record['bank_name'], "bank_abi"=>$db->record['bank_abi'], "bank_cab"=>$db->record['bank_cab'], "bank_cc"=>$db->record['bank_cc']);

  if($getFullInfo)
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT ctime FROM dynarc_commercialdocs_items WHERE id='".$a['docref_id']."'");
   $db2->Read();
   $a['docref_date'] = date('Y-m-d',strtotime($db2->record['ctime']));
   $db2->RunQuery("SELECT code_str FROM dynarc_rubrica_items WHERE id='".$a['subject_id']."'");
   $db2->Read();
   $a['subject_code'] = $db2->record['code_str'];
   $db2->RunQuery("SELECT address,city,zipcode,province FROM dynarc_rubrica_contacts WHERE item_id='".$a['subject_id']."' ORDER BY isdefault DESC LIMIT 1");
   $db2->Read();
   $a['subject_address'] = $db2->record['address'];
   $a['subject_city'] = $db2->record['city'];
   $a['subject_zipcode'] = $db2->record['zipcode'];
   $a['subject_province'] = $db2->record['province'];
   $db2->Close();
  }

  $outArr['elements'][] = $a;
 }
 $db->Close();

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_editRiBa($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : case '-title' : {$title=$args[$c+1]; $c++;} break;
   case '-ctime' : case '-date' : {$ctime=$args[$c+1]; $c++;} break;
   case '-availtime' : case '-availdate' : {$availDate=$args[$c+1]; $c++;} break;
   case '-bankid' : {$bankId=$args[$c+1]; $c++;} break;
   case '-bankname' : {$bankName=$args[$c+1]; $c++;} break;
   case '-sia' : {$bankSia=$args[$c+1]; $c++;} break;
   case '-abi' : {$bankAbi=$args[$c+1]; $c++;} break;
   case '-cab' : {$bankCab=$args[$c+1]; $c++;} break;
   case '-cc' : {$bankCC=$args[$c+1]; $c++;} break;
  }

 if(!$id)
  return array("message"=>"Error: you must specify the RiBa id.", "error"=>"INVALID_RIBA");

 $cmd = "dynarc edit-item -ap riba -id '".$id."'";
 if($title)
  $cmd.= " -name `".$title."`";
 if($ctime)
  $cmd.= " -ctime '".$ctime."'";
 
 $set = "";
 if(isset($availDate))
  $set.= ",availdate='".$availDate."'";
 if(isset($bankId))
  $set.= ",bank_id='".$bankId."'";
 if(isset($bankName))
  $set.= ",bank_name=\"".$bankName."\"";
 if(isset($bankSia))
  $set.= ",bank_sia='".$bankSia."'";
 if(isset($bankAbi))
  $set.= ",bank_abi='".$bankAbi."'";
 if(isset($bankCab))
  $set.= ",bank_cab='".$bankCab."'";
 if(isset($bankCC))
  $set.= ",bank_cc='".$bankCC."'";

 if($set)
  $cmd.= " -set `".ltrim($set,",")."`";

 $ret = GShell($cmd,$sessid,$shellid);
 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_deleteRiBa($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;}
   case '-r' : $forever=true; break;
  }

 $ret = GShell("dynarc delete-item -ap riba -id '".$id."'".($forever ? " -r" : ""),$sessid,$shellid);
 if($ret['error'])
  return array("message"=>"Error: Unable to delete RiBa #".$id."\n".$ret['message'], "error"=>$ret['error']);
 $outArr = $ret['outarr'];

 if($forever)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("DELETE FROM dynarc_riba_elements WHERE riba_id='".$id."'");
  $db->Close();
 }

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_commercialdocs_mmr SET riba_id='0' WHERE riba_id='".$id."'");
 $db->Close();

 if($forever)
  $out.= "RiBa #".$id." has been removed.";
 else
  $out.= "RiBa #".$id." has been trashed.";

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_editRiBaElement($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-bankid' : {$bankId=$args[$c+1]; $c++;} break;
   case '-bankname' : {$bankName=$args[$c+1]; $c++;} break;
   case '-abi' : {$bankAbi=$args[$c+1]; $c++;} break;
   case '-cab' : {$bankCab=$args[$c+1]; $c++;} break;
   case '-cc' : {$bankCC=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 $q = "";
 if(isset($bankId))
  $q.= ",bank_id='".$bankId."'";
 if(isset($bankName))
  $q.= ",bank_name='".$db->Purify($bankName)."'";
 if(isset($bankAbi))
  $q.= ",bank_abi='".$bankAbi."'";
 if(isset($bankCab))
  $q.= ",bank_cab='".$bankCab."'";
 if(isset($bankCC))
  $q.= ",bank_cc='".$bankCC."'";

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_riba_elements SET ".ltrim($q,",")." WHERE id='".$id."'");
 $db->Close();

 $out.= "done!";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_removeRiBaElements($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-riba' : {$ribaId=$args[$c+1]; $c++;} break;
   case '-ids' : {$ids=$args[$c+1]; $c++;} break;
  }

 if(!$ribaId)
  return array("message"=>"Error: you must specify the RiBa id.", "error"=>"INVALID_RIBA");

 $ret = GShell("dynarc item-info -ap riba -id '".$ribaId."' -get `elements_count,tot_amount`",$sessid,$shellid);
 if($ret['error'])
  return array("message"=>"Error: unable to get informations about RiBa #".$ribaId."\n".$ret['message'], "error"=>$ret['error']);
 $ribaInfo = $ret['outarr'];

 $_IDS = explode(",",$ids);
 $amount = 0;
 for($c=0; $c < count($_IDS); $c++)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT mmr_id,amount FROM dynarc_riba_elements WHERE id='".$_IDS[$c]."'");
  $db->Read();
  $mmrId = $db->record['mmr_id'];
  $amount+= $db->record['amount'];
  $db->RunQuery("UPDATE dynarc_commercialdocs_mmr SET riba_id='0' WHERE id='".$mmrId."'");
  $db->RunQuery("DELETE FROM dynarc_riba_elements WHERE id='".$_IDS[$c]."'");
  $db->Close();
 }

 $ribaInfo['tot_amount'] = $ribaInfo['tot_amount']-$amount;
 $ribaInfo['elements_count'] = $ribaInfo['elements_count']-count($_IDS);

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_riba_items SET tot_amount='".$ribaInfo['tot_amount']."',elements_count='"
	.$ribaInfo['elements_count']."' WHERE id='".$ribaId."'");
 $db->Close();

 $outArr = $ribaInfo;

 return array('message'=>$out,'outarr'=>$outArr); 
}
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_exportRibaToExcel($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
 require_once($_BASE_PATH."var/lib/excel.php");

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-riba' : case '-id' : {$ribaId=$args[$c+1]; $c++;} break;
   case '-f' : case '-file' : {$fileName=$args[$c+1]; $c++;} break;
  }

 $ret = GShell("backoffice riba-info -id '".$ribaId."' --full-info",$sessid,$shellid);
 if($ret['error'])
  return array("message"=>"Unable to export RiBa #".$ribaId." to excel.\n".$ret['message'], "error"=>$ret['error']);
 $ribaInfo = $ret['outarr'];

 if(!$fileName)
  $fileName = $ribaInfo['name'].".xlsx";

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] == "root")
 {
  $basepath = $_BASE_PATH."tmp/";
  $fullbasepath = "tmp/";
 }
 else if($sessInfo['uid'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $basepath = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
  $fullbasepath = $_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
 {
  $basepath= $_BASE_PATH."tmp/";
  $fullbasepath = "tmp/";
 }

 
 /* EXPORT TO EXCEL */
 PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());
 $objPHPExcel = new PHPExcel();
 $sheet = $objPHPExcel->setActiveSheetIndex(0);
 $objPHPExcel->getActiveSheet()->setTitle($ribaInfo['name']);

 $_COLUMNS = array(
	 array("title"=>"Cliente-p.iva", "name"=>"subject_vatnumber"),
	 array("title"=>"Cliente-Codice", "name"=>"subject_code"),
	 array("title"=>"Cliente-ABI", "name"=>"bank_abi"),
	 array("title"=>"Cliente-CAB", "name"=>"bank_cab"), 
	 array("title"=>"Fattura-Data emissione", "name"=>"docref_date"),
	 array("title"=>"Fattura-Data scadenza", "name"=>"expire_date"),
	 array("title"=>"Fattura-Importo totale", "name"=>"amount"),
	 array("title"=>"Cliente-Nome", "name"=>"subject_name"),
	 array("title"=>"Cliente-indirizzo", "name"=>"subject_address"),
	 array("title"=>"Cliente-CAP", "name"=>"subject_zipcode"),
	 array("title"=>"Cliente-citta", "name"=>"subject_city"), 
	 array("title"=>"Cliente-banca/sportello", "name"=>"bank_name"),
	 array("title"=>"Fattura-descrizione", "name"=>"docref_name"), 
	 array("title"=>"Switch-doc x debitore", "name"=>"switch_1"), 
	 array("title"=>"Switch-richiesta esito", "name"=>"switch_2"), 
	 array("title"=>"Switch-stampa avviso", "name"=>"switch_3")
	);

 $rowIdx = 1;
 $colIdx = 0;
 for($c=0; $c < count($_COLUMNS); $c++)
 {
  $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, htmlspecialchars_decode($_COLUMNS[$c]['title'],ENT_QUOTES));
  $sheet->getStyleByColumnAndRow($colIdx, $rowIdx)->getFont()->setBold(true);
  $colIdx++;
 }
 
 for($c=0; $c < count($ribaInfo['elements']); $c++)
 {
  $colIdx = 0;
  $rowIdx++;
  $item = $ribaInfo['elements'][$c];
  for($i=0; $i < count($_COLUMNS); $i++)
  {
   $value = "";
   $dataType = "";
   $formatCode = "";
   switch($_COLUMNS[$i]['name'])
   {
    case 'docref_date' : $value = " ".date('d/m/Y',strtotime($item['docref_date'])); break;
    case 'expire_date' : $value = " ".date('d/m/Y',strtotime($item['expire_date'])); break;
    case 'amount' : {
		 $value = $item['amount'];
		 $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		 $formatCode = "€ #,##0.00";
		} break;
	case 'subject_zip' : case 'subject_code' : case 'subject_vatnumber' : case 'bank_abi' : case 'bank_cab' : {
		 $value = $item[$_COLUMNS[$i]['name']];
		 $dataType = PHPExcel_Cell_DataType::TYPE_STRING;
		} break;
    case 'docref_name' : $value = str_replace("n&deg;","n.",$item['docref_name']); break;
	case 'switch_1' : $value = "1"; break;
	case 'switch_2' : $value = "2"; break;
	case 'switch_3' : $value = "0"; break;
    default : $value = $item[$_COLUMNS[$i]['name']]; break;
   }
   if($dataType)
    $sheet->setCellValueExplicitByColumnAndRow($colIdx, $rowIdx, htmlspecialchars_decode($value,ENT_QUOTES), $dataType);
   else
    $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, htmlspecialchars_decode($value,ENT_QUOTES));
   if($formatCode)
	$sheet->getStyleByColumnAndRow($colIdx, $rowIdx)->getNumberFormat()->setFormatCode($formatCode);
   $colIdx++;
  }
 }

 /* WRITE TO FILE */
 $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
 $objWriter->save($basepath.ltrim($fileName,"/"));

 $out = "done!\nRiBa has been exported to Excel file: ".$fileName;
 $outArr = array("filename"=>$fileName, "fullpath"=>$fullbasepath.$fileName);

 return array('message'=>$out,'outarr'=>$outArr); 
}
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_exportRibaToCBI($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES, $_COMPANY_PROFILE;
 include_once($_BASE_PATH."include/company-profile.php");

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-riba' : case '-id' : {$ribaId=$args[$c+1]; $c++;} break;
   case '-f' : case '-file' : {$fileName=$args[$c+1]; $c++;} break;
  }

 $ret = GShell("backoffice riba-info -id '".$ribaId."' --full-info",$sessid,$shellid);
 if($ret['error'])
  return array("message"=>"Unable to export RiBa #".$ribaId." to CBI file.\n".$ret['message'], "error"=>$ret['error']);
 $ribaInfo = $ret['outarr'];

 if(!$fileName)
  $fileName = $ribaInfo['name'].".cbi";

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] == "root")
 {
  $basepath = $_BASE_PATH."tmp/";
  $fullbasepath = "tmp/";
 }
 else if($sessInfo['uid'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $basepath = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
  $fullbasepath = $_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
 {
  $basepath= $_BASE_PATH."tmp/";
  $fullbasepath = "tmp/";
 }

 /* EXPORT TO CBI */
 $_CONAME = $_COMPANY_PROFILE['name'];
 $_COBNK_SIA = $ribaInfo['bank_sia'];
 $_COBNK_ABI = $ribaInfo['bank_abi'];
 $_BUILD_DATE = date('dmY');
 $_DOC_NAME = "RIBA".date('Ymdhis')."00";
 $_RECORD_TERM = "\r\n";
 
 $_VIRTBILL_PROV = "";
 $_VIRTBILL_AUTHCODE = "";
 $_VIRTBILL_AUTHDATE = "";

 $_DEF_SW_DOCDEB = "1";
 $_DEF_SW_OUTCOMEREQ = "2";
 $_DEF_SW_STAMP = "0";

 /* header record */
 $output = chr(32)."IB"
	.str_pad($_COBNK_SIA, 5, "0", STR_PAD_LEFT)
	.str_pad($_COBNK_ABI, 5, "0", STR_PAD_LEFT)
	.$_BUILD_DATE.str_pad($_DOC_NAME, 20).str_pad(" ",6).str_pad(" ",59).str_pad(" ",7).str_pad(" ",2)."E"." ".str_pad(" ",5).$_RECORD_TERM;


 for($c=0; $c < count($ribaInfo['elements']); $c++)
 {
  $iCounter = $c+1;
  $item = $ribaInfo['elements'][$c];
  /* record 14 */
  $output.= chr(32)
	."14"
	.str_pad($iCounter,7,"0",STR_PAD_LEFT)
	.str_pad(" ",12)
	.date('dmy',strtotime($item['expire_date']))
	."30000"
	.str_pad($item['amount']*100, 13, "0", STR_PAD_LEFT)
	."-"
	.str_pad($ribaInfo['bank_abi'],5, "0", STR_PAD_LEFT)
	.str_pad($ribaInfo['bank_cab'],5, "0", STR_PAD_LEFT)
	.str_pad($ribaInfo['bank_cc'],12, "0", STR_PAD_LEFT)
	.str_pad($item['bank_abi'],5, "0", STR_PAD_LEFT)
	.str_pad($item['bank_cab'],5, "0", STR_PAD_LEFT)
	.str_pad(" ",12)
	.str_pad($_COBNK_SIA, 5, "0", STR_PAD_LEFT)
	."4"
	.str_pad($item['subject_code'], 16)
	." "
	.str_pad(" ",5)
	."E"
	.$_RECORD_TERM;

  /* record 20 */
  $output.= chr(32)
	."20"
	.str_pad($iCounter,7,"0",STR_PAD_LEFT)
	.str_pad($_CONAME,96)
	.str_pad(" ",14)
	.$_RECORD_TERM;

  /* record 30 */
  $output.= chr(32)
	."30"
	.str_pad($iCounter,7,"0",STR_PAD_LEFT)
	.str_pad($item['subject_name'],60)
	.str_pad($item['subject_vatnumber'] ? $item['subject_vatnumber'] : $item['subject_taxcode'], 16)
	.str_pad(" ",34)
	.$_RECORD_TERM;

  /* record 40 */
  $output.= chr(32)
	."40"
	.str_pad($iCounter,7,"0",STR_PAD_LEFT)
	.str_pad($item['subject_address'], 30)
	.str_pad($item['subject_zipcode'], 5)
	.str_pad($item['subject_city'], 25)
	.str_pad($item['bank_name'],50)
	.$_RECORD_TERM;

  /* record 50 */
  $output.= chr(32)
	."50"
	.str_pad($iCounter,7,"0",STR_PAD_LEFT)
	.str_pad(str_replace("n&deg;","n.",$item['docref_name']),80)
	.str_pad(" ",10)
	.str_pad($_COMPANY_PROFILE['vatnumber'] ? $_COMPANY_PROFILE['vatnumber'] : $_COMPANY_PROFILE['taxcode'], 16)
	.str_pad(" ",4)
	.$_RECORD_TERM;

  /* record 51 */
  $output.= chr(32)
	."51"
	.str_pad($iCounter,7,"0",STR_PAD_LEFT)
	.str_pad($iCounter,10,"0",STR_PAD_LEFT)
	.str_pad($_CONAME,20)
	.str_pad($_VIRTBILL_PROV, 15)
	.str_pad($_VIRTBILL_AUTHCODE, 10)
	.str_pad($_VIRTBILL_AUTHDATE, 6)
	.str_pad(" ",49)
	.$_RECORD_TERM;

  /* record 70 */
  $output.= chr(32)
	."70"
	.str_pad($iCounter,7,"0",STR_PAD_LEFT)
	.str_pad(" ",78)
	.str_pad(" ",12)
	.$_DEF_SW_DOCDEB
	.$_DEF_SW_OUTCOMEREQ
	.$_DEF_SW_STAMP
	.str_pad(" ",17)
	.$_RECORD_TERM;

 }
 /* footer record */
  $output.= chr(32)
	."EF"
	.str_pad($_COBNK_SIA, 5, "0", STR_PAD_LEFT)
	.str_pad($ribaInfo['bank_abi'],5, "0", STR_PAD_LEFT)
	.$_BUILD_DATE
	.str_pad($_DOC_NAME, 20)
	.str_pad(" ",6)
	.str_pad($iCounter * 7 + 2,7,"0",STR_PAD_LEFT)
	.str_pad(" ",24)
	."E"
	.str_pad(" ",6)
	.$_RECORD_TERM;

 /* WRITE TO FILE */
 $fp = fopen($basepath.$fileName, "w");
 if(!$fp)
  return array("message"=>"Error: unable to open file '".$fullbasepath.$fileName."' in write mode.","error"=>"PERMISSION_DENIED");
 fputs($fp,$output);
 fclose($fp);

 $out = "done!\nRiBa has been exported to CBI file: ".$fileName;
 $outArr = array("filename"=>$fileName, "fullpath"=>$fullbasepath.$fileName);

 return array('message'=>$out,'outarr'=>$outArr); 
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//--- LOTTI ---------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_addIntoLot($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-cat' : case '-lot' : {$catId=$args[$c+1]; $c++;} break;
   case '-refap' : {$refAp=$args[$c+1]; $c++;} break;
   case '-refid' : {$refId=$args[$c+1]; $c++;} break;
   case '-qty' : {$qty=$args[$c+1]; $c++;} break;
   case '-ctime' : case '-regtime' : {$ctime=$args[$c+1]; $c++;} break;
   case '-date' : {$prodDate=$args[$c+1]; $c++;} break;
   case '-expiry' : {$expiryDate=$args[$c+1]; $c++;} break;
   case '-finished' : {$finished=$args[$c+1]; $c++;} break;
  }

 if($catId)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT code FROM dynarc_lots_categories WHERE id='".$catId."'");
  $db->Read();
  $lotCode = $db->record['code'];
  $db->Close();
 }
 $ret = GShell("dynarc new-item -ap lots -cat '".$catId."' -name '".$lotCode."' -ctime '".$ctime."' -set `ref_ap='".$refAp."',ref_id='".$refId."',qty='"
	.$qty."',prod_date='".$prodDate."',expiry_date='".$expiryDate."',finished='".$finished."'`",$sessid,$shellid);
 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_lotList($args,$sessid,$shellid)
{
 $out = "";
 $outArr = array();
 
 $_LOT_BY_ID = array();

 $orderBy = "prod_date,cat_id ASC";

 for($c=1; $c < count($args); $c++) 
  switch($args[$c])
  {
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;
   case '-cat' : case '-lotid' : {$catId=$args[$c+1]; $c++;} break;
   case '-product' : case '-prodid' : {$productId=$args[$c+1]; $c++;} break;
   case '--only-expired' : $onlyExpired=true; break;
   case '--only-unfinished' : $onlyUnfinished=true; break;
   case '--only-finished' : $onlyFinished=true; break;
   case '--only-trashed' : $onlyTrashed=true; break;

   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 if($onlyTrashed)
  $qry = "trash=1";
 else
 {
  $qry = "trash=0";
  if($dateFrom)
   $qry.= " AND prod_date>='".$dateFrom."'";
  if($dateTo)
   $qry.= " AND prod_date<'".$dateTo."'";
  if($catId)
   $qry.= " AND cat_id='".$catId."'";
  if($productId)
   $qry.= " AND ref_id='".$productId."'";
  if($onlyExpired)
   $qry.= " AND expiry_date!='0000-00-00' AND expiry_date<='".date('Y-m-d')."'";
  else if($onlyUnfinished)
   $qry.= " AND finished='0'";
  else if($onlyFinished)
   $qry.= " AND finished='1'";
 }
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_lots_items WHERE ".$qry);
 $db->Read();
 $outArr['count'] = $db->record[0];
 $db->RunQuery("SELECT * FROM dynarc_lots_items WHERE ".$qry." ORDER BY ".$orderBy.($limit ? " LIMIT ".$limit : ""));
 while($db->Read())
 {
  $a = array("id"=>$db->record['id'], "name"=>$db->record['name'], "ctime"=>$db->record['ctime'], "ref_ap"=>$db->record['ref_ap'],
	"ref_id"=>$db->record['ref_id'], "qty"=>$db->record['qty'], "prod_date"=>$db->record['prod_date'], "expiry_date"=>$db->record['expiry_date'],
	"finished"=>$db->record['finished']);
  if(($a['expiry_date'] == "0000-00-00") || ($a['expiry_date'] == "1970-01-01"))
   $a['expiry_date'] = "";
  // get lot info
  /*if(!$_LOT_BY_ID[$db->record['cat_id']])
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT code FROM dynarc_lots_categories WHERE id='".$db->record['cat_id']."'");
   $db2->Read();
   $_LOT_BY_ID[$db->record['cat_id']] = $db2->record['code'];
   $db2->Close();
  }
  $a['lot_code'] = $_LOT_BY_ID[$db->record['cat_id']];*/
  // get product info
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT code_str,name FROM dynarc_".$a['ref_ap']."_items WHERE id='".$a['ref_id']."'");
  $db2->Read();
  $a['ref_code'] = $db2->record['code_str'];
  $a['ref_name'] = $db2->record['name'];
  $db2->Close();
  $outArr['items'][] = $a;
 }
 $db->Close();

 $out.= $outArr['count']." results found.";

 return array("message"=>$out, "outarr"=>$outArr); 
}
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_salesSummary($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array('count'=>0, 'tot_qty'=>0, 'tot_amount'=>0, 'tot_documents'=>0, 'items'=>array());

 $orderBy = "ctime ASC";
 $limit = 0;
 $_MODAL = "extended";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;

   // filters 
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;				// filtra per catalogo
   case '-subject' : {$subjectName=$args[$c+1]; $c++;} break;	// filtra per nome cliente
   case '-subjectid' : {$subjectId=$args[$c+1]; $c++;} break;	// filtra per id cliente
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;				// filtra per prodotto
   case '-uid' : {$uid=$args[$c+1]; $c++;} break;				// filtra per utente

   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;

   case '--group-by-product' : $_MODAL = "groupped"; break;
  }

 $catQry = "";
 $catList = array();

 // get invoices categories
 $ret = GShell("dynarc cat-info -ap commercialdocs -tag INVOICES",$sessid,$shellid);
 if(!$ret['error'])
 {
  $invoiceCatInfo = $ret['outarr'];
  $catList[] = $invoiceCatInfo['id'];

  $ret = GShell("dynarc cat-list -ap commercialdocs -parent '".$invoiceCatInfo['id']."'",$sessid,$shellid);
  if(!$ret['error'])
  {
   $invoiceCatList = $ret['outarr'];
   for($c=0; $c < count($invoiceCatList); $c++)
	$catList[] = $invoiceCatList[$c]['id'];
  }
 }

 // get receipts categories
 $ret = GShell("dynarc cat-info -ap commercialdocs -tag RECEIPTS",$sessid,$shellid);
 if(!$ret['error'])
 {
  $receiptCatInfo = $ret['outarr'];
  $catList[] = $receiptCatInfo['id'];

  $ret = GShell("dynarc cat-list -ap commercialdocs -parent '".$receiptCatInfo['id']."'",$sessid,$shellid);
  if(!$ret['error'])
  {
   $receiptCatList = $ret['outarr'];
   for($c=0; $c < count($receiptCatList); $c++)
	$catList[] = $receiptCatList[$c]['id'];
  }
 }
 
 if(count($catList))
 {
  $q = "";
  for($c=0; $c < count($catList); $c++)
   $q.= " OR cat_id='".$catList[$c]."'";
  $catQry = " AND (".ltrim($q," OR ").")";
 }

 $db = new AlpaDatabase();
 $_WHERE = "trash='0' AND ctime>='".$dateFrom."' AND ctime<'".$dateTo."'".$catQry;
 if($subjectId)
  $_WHERE.= " AND subject_id='".$subjectId."'";
 else if($subjectName)
  $_WHERE.= " AND subject_name='".$db->Purify($subjectName)."'";

 $_QRY = "";
 if($_AP) $_QRY.= " AND ref_ap='".$_AP."'"; else $_QRY.= " AND ref_ap!=''";
 if($_ID) $_QRY.= " AND ref_id='".$_ID."'"; else $_QRY.= " AND ref_id!='0'";
 if($uid)
  $_QRY.= " AND uid='".$uid."'";

 $_FIELDS = "uid,ref_ap,ref_id,code,name,qty,price,discount_perc,discount_inc,vat_rate,vat_id,vat_type,pricelist_id,
extra_qty,price_adjust,discount2,discount3,serial_number,units,lot,vencode,vendor_price,sale_price,variant_coltint,variant_sizmis";

 $db->RunQuery("SELECT id,ctime FROM dynarc_commercialdocs_items WHERE ".$_WHERE." ORDER BY ".$orderBy);
 while($db->Read())
 {
  $db2 = new AlpaDatabase();
  $db3 = new AlpaDatabase();
  $qry = "SELECT ".$_FIELDS." FROM dynarc_commercialdocs_elements WHERE item_id='".$db->record['id']."'".$_QRY;
  $ret = $db2->RunQuery($qry);
  if(!$ret)
   return array("message"=>"MySQL Error: ".$db2->Error, "error"=>"MYSQL_ERROR");
  $outArr['tot_documents']++;
  while($db2->Read())
  {
   $a = array("ctime"=>$db->record['ctime'], "uid"=>$db2->record['uid'], "ref_ap"=>$db2->record['ref_ap'], "ref_id"=>$db2->record['ref_id'],
	"code"=>$db2->record['code'], "name"=>$db2->record['name'], "qty"=>$db2->record['qty'], "price"=>$db2->record['price'], 
	"discount_perc"=>$db2->record['discount_perc'], "discount_inc"=>$db2->record['discount_inc'], "vat_rate"=>$db2->record['vat_rate'],
	"vat_id"=>$db2->record['vat_id'], "vat_type"=>$db2->record['vat_type'], "pricelist_id"=>$db2->record['pricelist_id'], 
	"extra_qty"=>$db2->record['extra_qty'], "price_adjust"=>$db2->record['price_adjust'], "discount2"=>$db2->record['discount2'],
	"discount3"=>$db2->record['discount3'], "serial_number"=>$db2->record['serial_number'], "units"=>$db2->record['units'],
	"vencode"=>$db2->record['vencode'], "vendor_price"=>$db2->record['vendor_price'], "sale_price"=>$db2->record['sale_price'],
	"variant_coltint"=>$db2->record['variant_coltint'], "variant_sizmis"=>$db2->record['variant_sizmis'], "lot"=>$db2->record['lot']);

   // get barcode,brand and model
   $db3->RunQuery("SELECT brand,model,barcode FROM dynarc_".$a['ref_ap']."_items WHERE id='".$a['ref_id']."'");
   $db3->Read();
   $a['brand'] = $db3->record['brand'];
   $a['model'] = $db3->record['model'];
   $a['barcode'] = $db3->record['barcode'];

   $qty = $a['qty'] ? $a['qty'] : 0;
   $price = $a['price'] ? $a['price'] : 0;
   if($price && $a['discount_perc'])
    $price = $price - (($price/100) * $a['discount_perc']);
   else if($price && $a['discount_inc'])
    $price = $price - $a['discount_inc'];
   if($price && $a['discount2'])
    $price = $price - (($price/100) * $a['discount2']);
   if($price && $a['discount3'])
    $price = $price - (($price/100) * $a['discount3']);
   $amount = $price*$qty;

   $a['amount'] = $amount;
   $a['profit'] = $amount - ($a['vendor_price'] * ($a['qty'] * ($a['extra_qty'] ? $a['extra_qty'] : 1)));

   $outArr['items'][] = $a;
   $outArr['count']++;
   $outArr['tot_qty']+= $a['qty'];
   $outArr['tot_amount']+= $a['amount'];
   $outArr['tot_profit']+= $a['profit'];
  }
  $db3->Close();
  $db2->Close();
 }
 $db->Close();

 $out.= count($outArr['items'])." results found.";

 return array("message"=>$out, "outarr"=>$outArr); 
}
//-------------------------------------------------------------------------------------------------------------------//
function backoffice_ordersSummary($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array('count'=>0, 'tot_qty'=>0, 'tot_amount'=>0, 'tot_profit'=>0, 'tot_documents'=>0, 'items'=>array());

 $orderBy = "ctime ASC";
 $limit = 0;
 $_MODAL = "extended";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;

   // filters 
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;				// filtra per catalogo
   case '-subject' : {$subjectName=$args[$c+1]; $c++;} break;	// filtra per nome cliente
   case '-subjectid' : {$subjectId=$args[$c+1]; $c++;} break;	// filtra per id cliente
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;				// filtra per prodotto
   case '-uid' : {$uid=$args[$c+1]; $c++;} break;				// filtra per utente

   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;

   case '--group-by-product' : $_MODAL = "groupped"; break;
  }

 $catQry = "";
 $catList = array();

 // get invoices categories
 $ret = GShell("dynarc cat-info -ap commercialdocs -tag ORDERS",$sessid,$shellid);
 if(!$ret['error'])
 {
  $invoiceCatInfo = $ret['outarr'];
  $catList[] = $invoiceCatInfo['id'];

  $ret = GShell("dynarc cat-list -ap commercialdocs -parent '".$invoiceCatInfo['id']."'",$sessid,$shellid);
  if(!$ret['error'])
  {
   $invoiceCatList = $ret['outarr'];
   for($c=0; $c < count($invoiceCatList); $c++)
	$catList[] = $invoiceCatList[$c]['id'];
  }
 }


 if(count($catList))
 {
  $q = "";
  for($c=0; $c < count($catList); $c++)
   $q.= " OR cat_id='".$catList[$c]."'";
  $catQry = " AND (".ltrim($q," OR ").")";
 }

 $db = new AlpaDatabase();
 $_WHERE = "trash='0' AND status>=7 AND ctime>='".$dateFrom."' AND ctime<'".$dateTo."'".$catQry;
 if($subjectId)
  $_WHERE.= " AND subject_id='".$subjectId."'";
 else if($subjectName)
  $_WHERE.= " AND subject_name='".$db->Purify($subjectName)."'";

 $_QRY = "";
 if($_AP) $_QRY.= " AND ref_ap='".$_AP."'"; else $_QRY.= " AND ref_ap!=''";
 if($_ID) $_QRY.= " AND ref_id='".$_ID."'"; else $_QRY.= " AND ref_id!='0'";
 if($uid)
  $_QRY.= " AND uid='".$uid."'";

 $_FIELDS = "uid,ref_ap,ref_id,code,name,qty,price,discount_perc,discount_inc,vat_rate,vat_id,vat_type,pricelist_id,
extra_qty,price_adjust,discount2,discount3,serial_number,units,lot,vencode,vendor_price,sale_price,variant_coltint,variant_sizmis";

 $db->RunQuery("SELECT id,ctime FROM dynarc_commercialdocs_items WHERE ".$_WHERE." ORDER BY ".$orderBy);
 while($db->Read())
 {
  $db2 = new AlpaDatabase();
  $db3 = new AlpaDatabase();
  $qry = "SELECT ".$_FIELDS." FROM dynarc_commercialdocs_elements WHERE item_id='".$db->record['id']."'".$_QRY;
  $ret = $db2->RunQuery($qry);
  if(!$ret)
   return array("message"=>"MySQL Error: ".$db2->Error, "error"=>"MYSQL_ERROR");
  $outArr['tot_documents']++;
  while($db2->Read())
  {
   $a = array("ctime"=>$db->record['ctime'], "uid"=>$db2->record['uid'], "ref_ap"=>$db2->record['ref_ap'], "ref_id"=>$db2->record['ref_id'],
	"code"=>$db2->record['code'], "name"=>$db2->record['name'], "qty"=>$db2->record['qty'], "price"=>$db2->record['price'], 
	"discount_perc"=>$db2->record['discount_perc'], "discount_inc"=>$db2->record['discount_inc'], "vat_rate"=>$db2->record['vat_rate'],
	"vat_id"=>$db2->record['vat_id'], "vat_type"=>$db2->record['vat_type'], "pricelist_id"=>$db2->record['pricelist_id'], 
	"extra_qty"=>$db2->record['extra_qty'], "price_adjust"=>$db2->record['price_adjust'], "discount2"=>$db2->record['discount2'],
	"discount3"=>$db2->record['discount3'], "serial_number"=>$db2->record['serial_number'], "units"=>$db2->record['units'],
	"vencode"=>$db2->record['vencode'], "vendor_price"=>$db2->record['vendor_price'], "sale_price"=>$db2->record['sale_price'],
	"variant_coltint"=>$db2->record['variant_coltint'], "variant_sizmis"=>$db2->record['variant_sizmis'], "lot"=>$db2->record['lot']);

   // get barcode,brand and model
   $db3->RunQuery("SELECT brand,model,barcode FROM dynarc_".$a['ref_ap']."_items WHERE id='".$a['ref_id']."'");
   $db3->Read();
   $a['brand'] = $db3->record['brand'];
   $a['model'] = $db3->record['model'];
   $a['barcode'] = $db3->record['barcode'];

   $qty = $a['qty'] ? $a['qty'] : 0;
   $price = $a['price'] ? $a['price'] : 0;
   if($price && $a['discount_perc'])
    $price = $price - (($price/100) * $a['discount_perc']);
   else if($price && $a['discount_inc'])
    $price = $price - $a['discount_inc'];
   if($price && $a['discount2'])
    $price = $price - (($price/100) * $a['discount2']);
   if($price && $a['discount3'])
    $price = $price - (($price/100) * $a['discount3']);
   $amount = $price*$qty;

   $a['amount'] = $amount;
   $a['profit'] = $amount - ($a['vendor_price'] * ($a['qty'] * ($a['extra_qty'] ? $a['extra_qty'] : 1)));

   $outArr['items'][] = $a;
   $outArr['count']++;
   $outArr['tot_qty']+= $a['qty'];
   $outArr['tot_amount']+= $a['amount'];
   $outArr['tot_profit']+= $a['profit'];
  }
  $db3->Close();
  $db2->Close();
 }
 $db->Close();

 $out.= count($outArr['items'])." results found.";

 return array("message"=>$out, "outarr"=>$outArr); 
}
//-------------------------------------------------------------------------------------------------------------------//

