<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-10-2013
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Commercial document info extension for Dynarc.
 #VERSION: 2.8beta
 #CHANGELOG: 18-10-2013 : Aggiunto i bolli.
			 30-09-2013 : Aggiunto lo sconto incondizionato.
			 06-09-2013 : Bug fix.
			 22-07-2013 : Aggiunto ext-docref (External Document Reference)
			 03-07-2013 : Aggiunto Rit. d'acconto,cassa prev, rivalsa inps e rit. enasarco.
			 08-05-2013 : Aggiunto data di validità e date noleggio.
			 08-05-2013 : Aggiunto references.
			 03-12-2012 : Completamento delle funzioni principali.
 #TODO: 
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` ADD `tag` VARCHAR( 64 ) NOT NULL ,
ADD `subject_id` INT( 11 ) NOT NULL ,
ADD `subject_name` VARCHAR( 80 ) NOT NULL ,
ADD `reference_id` INT ( 11 ) NOT NULL ,
ADD `payment_mode` INT( 4 ) NOT NULL ,
ADD `bank_support` INT( 11 ) NOT NULL ,
ADD `pricelist_id` INT( 11 ) NOT NULL ,
ADD `ship_contact_id` INT( 11 ) NOT NULL ,
ADD `ship_recp` VARCHAR( 80 ) NOT NULL ,
ADD `ship_addr` VARCHAR( 80 ) NOT NULL ,
ADD `ship_city` VARCHAR( 40 ) NOT NULL ,
ADD `ship_zip` VARCHAR( 5 ) NOT NULL ,
ADD `ship_prov` VARCHAR( 2 ) NOT NULL ,
ADD `ship_cc` VARCHAR( 3 ) NOT NULL ,
ADD `trans_method` TINYINT( 1 ) NOT NULL ,
ADD `trans_shipper` VARCHAR( 80 ) NOT NULL ,
ADD `trans_numplate` VARCHAR( 12 ) NOT NULL ,
ADD `trans_causal` VARCHAR( 64 ) NOT NULL ,
ADD `trans_datetime` DATETIME NOT NULL ,
ADD `trans_aspect` VARCHAR( 64 ) NOT NULL ,
ADD `trans_num` FLOAT NOT NULL ,
ADD `trans_weight` FLOAT NOT NULL ,
ADD `trans_freight` VARCHAR( 64 ) NOT NULL ,
ADD `status` TINYINT( 1 ) NOT NULL,
ADD `print_date` DATE NOT NULL ,
ADD `send_date` DATE NOT NULL ,
ADD `pending_date` DATE NOT NULL ,
ADD `start_working_date` DATE NOT NULL ,
ADD `suspension_date` DATE NOT NULL ,
ADD `failure_date` DATE NOT NULL ,
ADD `completion_date` DATE NOT NULL ,
ADD `conversion_date` DATE NOT NULL ,
ADD `grouping_date` DATE NOT NULL ,
ADD `payment_date` DATE NOT NULL ,
ADD `conv_doc_id` INT( 11 ) NOT NULL ,
ADD `group_doc_id` INT( 11 ) NOT NULL ,
ADD `amount` FLOAT NOT NULL ,
ADD `vat` FLOAT NOT NULL ,
ADD `total` FLOAT NOT NULL ,
ADD `tot_rit_acc` FLOAT NOT NULL ,
ADD `tot_ccp` FLOAT NOT NULL ,
ADD `tot_rinps` FLOAT NOT NULL ,
ADD `tot_enasarco` FLOAT NOT NULL ,
ADD `tot_netpay` FLOAT NOT NULL ,
ADD `validity_date` DATE NOT NULL ,
ADD `charter_datefrom` DATE NOT NULL ,
ADD `charter_dateto` DATE NOT NULL ,
ADD `ext_docref` VARCHAR(64) NOT NULL ,
ADD `discount_1` FLOAT NOT NULL ,
ADD `discount_2` FLOAT NOT NULL ,
ADD `unconditional_discount` FLOAT NOT NULL ,
ADD `rebate` FLOAT NOT NULL ,
ADD `stamp` FLOAT NOT NULL ,
ADD `tot_goods` FLOAT NOT NULL ,
ADD `discounted_goods` FLOAT NOT NULL ,
ADD `tot_expenses` FLOAT NOT NULL ,
ADD `exp_1_name` VARCHAR( 64 ) NOT NULL ,
ADD `exp_1_vatid` INT( 11 ) NOT NULL ,
ADD `exp_1_amount` FLOAT NOT NULL ,
ADD `exp_2_name` VARCHAR( 64 ) NOT NULL ,
ADD `exp_2_vatid` INT( 11 ) NOT NULL ,
ADD `exp_2_amount` FLOAT NOT NULL ,
ADD `exp_3_name` VARCHAR( 64 ) NOT NULL ,
ADD `exp_3_vatid` INT( 11 ) NOT NULL ,
ADD `exp_3_amount` FLOAT NOT NULL ,
ADD `tot_discount` FLOAT NOT NULL ,
ADD `vat_1_id` INT( 11 ) NOT NULL ,
ADD `vat_1_taxable` FLOAT NOT NULL ,
ADD `vat_1_tax` FLOAT NOT NULL ,
ADD `vat_2_id` INT( 11 ) NOT NULL ,
ADD `vat_2_taxable` FLOAT NOT NULL ,
ADD `vat_2_tax` FLOAT NOT NULL ,
ADD `vat_3_id` INT( 11 ) NOT NULL ,
ADD `vat_3_taxable` FLOAT NOT NULL ,
ADD `vat_3_tax` FLOAT NOT NULL ,
ADD INDEX ( `subject_id` ) , 
ADD INDEX ( `status` )");

 $db->Close();
 return array("message"=>"GCommercialDocs:Info extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` DROP `tag`, DROP `subject_id`, DROP `subject_name`, DROP `payment_mode`,
  DROP `pricelist_id`, DROP `ship_contact_id`, DROP `ship_recp`, DROP `ship_addr`, DROP `ship_city`, DROP `ship_zip`, DROP `ship_prov`, DROP `ship_cc`,
  DROP `trans_method`, DROP `trans_shipper`, DROP `trans_numplate`, DROP `trans_causal`, DROP `trans_datetime`, DROP `trans_aspect`,
  DROP `trans_num`, DROP `trans_weight`, DROP `trans_freight`, DROP `status`, DROP `amount`, DROP `vat`, DROP `total`, DROP `print_date`,
  DROP `send_date`, DROP `pending_date`, DROP `start_working_date`, DROP `suspension_date`, DROP `failure_date`, DROP `completion_date`,
  DROP `conversion_date`, DROP `grouping_date`, DROP `payment_date`, DROP `conv_doc_id`, DROP `group_doc_id`, DROP `reference_id`, DROP `validity_date`,
  DROP `charter_datefrom`, DROP `charter_dateto`, DROP `tot_rit_acc`, DROP `tot_ccp`, DROP `tot_rinps`, DROP `tot_enasarco`, DROP `tot_netpay`, DROP `ext_docref`, DROP `discount_1`, DROP `discount_2`, DROP `unconditional_discount`, DROP `rebate`, DROP `tot_goods`, DROP `discounted_goods`, DROP `tot_expenses`, DROP `exp_1_name`, DROP `exp_1_vatid`, DROP `exp_1_amount`, DROP `exp_2_name`, DROP `exp_2_vatid`, DROP `exp_2_amount`, DROP `exp_3_name`, DROP `exp_3_vatid`, DROP `exp_3_amount`, DROP `tot_discount`, DROP `vat_1_id`, DROP `vat_1_taxable`, DROP `vat_1_tax`, DROP `vat_2_id`, DROP `vat_2_taxable`, DROP `vat_2_tax`, DROP `vat_3_id`, DROP `vat_3_taxable`, DROP `vat_3_tax`");
 $db->Close();

 return array("message"=>"GCommercialDocs:Info extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_catset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_cdinfo_catset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
    case 'subject' : {$subjectName=$args[$c+1]; $c++;} break;
	case 'subjectid' : {$subjectId=$args[$c+1]; $c++;} break;
    case 'referenceid' : {$referenceId=$args[$c+1]; $c++;} break;
	case 'paymentmode' : {$paymentMode=$args[$c+1]; $c++;} break;
	case 'bank' : case 'banksupport' : {$bankSupportId=$args[$c+1]; $c++;} break;
	case 'pricelist' : case 'pricelistid' : {$pricelistId=$args[$c+1]; $c++;} break;
	case 'status' : {$status=$args[$c+1]; $c++;} break;
	case 'tag' : {$tag=$args[$c+1]; $c++;} break;

	/* DATES */
	case 'print-date' : {$printDate=$args[$c+1]; $c++;} break;
	case 'send-date' : {$sendDate=$args[$c+1]; $c++;} break;
	case 'pending-date' : {$pendingDate=$args[$c+1]; $c++;} break;
	case 'start-working-date' : {$startWorkingDate=$args[$c+1]; $c++;} break;
	case 'suspension-date' : {$suspensionDate=$args[$c+1]; $c++;} break;
	case 'failure-date' : {$failureDate=$args[$c+1]; $c++;} break;
	case 'completion-date' : {$completionDate=$args[$c+1]; $c++;} break;
	case 'conversion-date' : {$conversionDate=$args[$c+1]; $c++;} break;
	case 'grouping-date' : {$groupingDate=$args[$c+1]; $c++;} break;
	case 'payment-date' : {$paymentDate=$args[$c+1]; $c++;} break;
    case 'validity-date' : {$validityDate=$args[$c+1]; $c++;} break;
    case 'charter-datefrom' : {$charterDateFrom=$args[$c+1]; $c++;} break;
    case 'charter-dateto' : {$charterDateTo=$args[$c+1]; $c++;} break;

	case 'conv-id' : case 'conv-doc' : {$convDocId=$args[$c+1]; $c++;} break;
	case 'group-id' : case 'group-doc' : {$groupDocId=$args[$c+1]; $c++;} break;

	case 'extdocref' : case 'ext-docref' : case 'ext-doc-ref' : {$extDocRef=$args[$c+1]; $c++;} break;

    case 'discount' : case 'discount1' : case 'disc1' : {$discount1=$args[$c+1]; $c++;} break;
    case 'discount2' : case 'disc2' : {$discount2=$args[$c+1]; $c++;} break;
    case 'uncondisc' : case 'unconditional-discount' : {$unconditionalDiscount=$args[$c+1]; $c++;} break;
    case 'rebate' : {$rebate=$args[$c+1]; $c++;} break;
    case 'stamp' : {$stamp=$args[$c+1]; $c++;} break;

    case 'exp1-name' : {$exp1name=$args[$c+1]; $c++;} break;
	case 'exp1-vatid' : {$exp1vatid=$args[$c+1]; $c++;} break;
	case 'exp1-amount' : {$exp1amount=$args[$c+1]; $c++;} break;
    case 'exp2-name' : {$exp2name=$args[$c+1]; $c++;} break;
	case 'exp2-vatid' : {$exp2vatid=$args[$c+1]; $c++;} break;
	case 'exp2-amount' : {$exp2amount=$args[$c+1]; $c++;} break;
    case 'exp3-name' : {$exp3name=$args[$c+1]; $c++;} break;
	case 'exp3-vatid' : {$exp3vatid=$args[$c+1]; $c++;} break;
	case 'exp3-amount' : {$exp3amount=$args[$c+1]; $c++;} break;


	/* SHIPPING */
	case 'ship-contact-id' : {$shipContactId=$args[$c+1]; $c++;} break;
	case 'ship-recp' : {$shipRecp=$args[$c+1]; $c++;} break;
	case 'ship-addr' : case 'ship-address' : {$shipAddr=$args[$c+1]; $c++;} break;
	case 'ship-city' : {$shipCity=$args[$c+1]; $c++;} break;
	case 'ship-zip' : case 'ship-zipcode' : {$shipZip=$args[$c+1]; $c++;} break;
	case 'ship-prov' : case 'ship-province' : {$shipProv=$args[$c+1]; $c++;} break;
	case 'ship-cc' : case 'ship-countrycode' : {$shipCC=$args[$c+1]; $c++;} break;

	/* TRANSPORT */
	case 'trans-method' : {$transMethod=$args[$c+1]; $c++;} break;
	case 'trans-shipper' : {$transShipper=$args[$c+1]; $c++;} break;
	case 'trans-numplate' : {$transNumberPlate=$args[$c+1]; $c++;} break;
	case 'trans-causal' : {$transCausal=$args[$c+1]; $c++;} break;
	case 'trans-date' : case 'trans-datetime' : case 'trans-time' : {$transDateTime=strtotime($args[$c+1]); $c++;} break;
	case 'trans-aspect' : {$transAspect=$args[$c+1]; $c++;} break;
	case 'trans-num' : {$transNum=$args[$c+1]; $c++;} break;
	case 'trans-weight' : {$transWeight=$args[$c+1]; $c++;} break;
	case 'trans-freight' : {$transFreight=$args[$c+1]; $c++;} break;

  }

 if($subjectId)
 {
  $ret = GShell("dynarc item-info -ap `rubrica` -id `".$subjectId."` -get `paymentmode,pricelist_id` -extget contacts",$sessid,$shellid);
  if(!$ret['error'])
  {
   $subjectInfo = $ret['outarr'];
   $subjectName = $subjectInfo['name'];
   if(!$paymentMode)
	$paymentMode=$subjectInfo['paymentmode'];
   if(!$pricelistId)
	$pricelistId = $subjectInfo['pricelist_id'];
  }
  else
   $subjectId=0;
 }
 else if($subjectName)
 {
  $ret = GShell("dynarc item-info -ap `rubrica` -name `".$subjectName."` -get `paymentmode,pricelist_id` -extget contacts",$sessid,$shellid);
  if(!$ret['error'])
  {
   $subjectInfo = $ret['outarr'];
   $subjectName = $subjectInfo['name'];
   $subjectId = $subjectInfo['id'];
   if(!$paymentMode)
	$paymentMode=$subjectInfo['paymentmode'];
   if(!$pricelistId)
	$pricelistId = $subjectInfo['pricelist_id'];
  }
 }
 if($subjectInfo && count($subjectInfo['contacts']))
 {
  if(!$shipContactId)
   $shipContactId = $subjectInfo['contacts'][0]['id'];
  if(!$shipRecp && !$shipAddr)
  {
   $shipRecp = $subjectInfo['contacts'][0]['name'];
   $shipAddr = $subjectInfo['contacts'][0]['address'];
   $shipCity = $subjectInfo['contacts'][0]['city'];
   $shipZip = $subjectInfo['contacts'][0]['zipcode'];
   $shipProv = $subjectInfo['contacts'][0]['province'];
   $shipCC = $subjectInfo['contacts'][0]['countrycode'];
  }
 }

 $db = new AlpaDatabase();
 $q="";
 $nowDate = date('Y-m-d');

 if(isset($subjectId))
  $q.= ",subject_id='".$subjectId."'";
 if(isset($subjectName))
  $q.= ",subject_name='".$db->Purify($subjectName)."'";
 if(isset($paymentMode))
  $q.= ",payment_mode='".$paymentMode."'";
 if(isset($bankSupportId))
  $q.= ",bank_support='".$bankSupportId."'";
 if(isset($pricelistId))
  $q.= ",pricelist_id='".$pricelistId."'";
 if(isset($status))
 {
  $q.= ",status='".$status."'";
  switch($status)
  {
   case 1 : {if(!isset($printDate)) $printDate = $nowDate;} break;
   case 2 : {if(!isset($sendDate)) $sendDate = $nowDate;} break;
   case 3 : {if(!isset($pendingDate)) $pendingDate = $nowDate;} break;
   case 4 : {if(!isset($startWorkingDate)) $startWorkingDate = $nowDate;} break;
   case 5 : {if(!isset($suspensionDate)) $suspensionDate = $nowDate;} break;
   case 6 : {if(!isset($failureDate)) $failureDate = $nowDate;} break;
   case 7 : {if(!isset($completionDate)) $completionDate = $nowDate;} break;
   case 8 : {if(!isset($conversionDate)) $conversionDate = $nowDate;} break;
   case 9 : {if(!isset($groupingDate)) $groupingDate = $nowDate;} break;
   case 10 : {if(!isset($paymentDate)) $paymentDate = $nowDate;} break;
  }
  if($status < 8)
  {
   // reset payments, conversion, group, ... //
   $conversionDate = "";
   $groupingDate = "";
   $paymentDate = "";
   $convDocId = 0;
   $groupDocId = 0;
  }

 }
 if(isset($tag))
  $q.= ",tag='".$tag."'";

 /* Dates */
 if(isset($printDate))
  $q.= ",print_date='".$printDate."'";
 if(isset($sendDate))
  $q.= ",send_date='".$sendDate."'";
 if(isset($pendingDate))
  $q.= ",pending_date='".$pendingDate."'";
 if(isset($startWorkingDate))
  $q.= ",start_working_date='".$startWorkingDate."'";
 if(isset($suspensionDate))
  $q.= ",suspension_date='".$suspensionDate."'";
 if(isset($failureDate))
  $q.= ",failure_date='".$failureDate."'";
 if(isset($completionDate))
  $q.= ",completion_date='".$completionDate."'";
 if(isset($conversionDate))
  $q.= ",conversion_date='".$conversionDate."'";
 if(isset($groupingDate))
  $q.= ",grouping_date='".$groupingDate."'";
 if(isset($paymentDate))
  $q.= ",payment_date='".$paymentDate."'";
 if(isset($validityDate))
  $q.= ",validity_date='".$validityDate."'";
 if(isset($charterDateFrom))
  $q.= ",charter_datefrom='".$charterDateFrom."'";
 if(isset($charterDateTo))
  $q.= ",charter_dateto='".$charterDateTo."'";


 if(isset($convDocId))
  $q.= ",conv_doc_id='".$convDocId."'";
 if(isset($groupDocId))
  $q.= ",group_doc_id='".$groupDocId."'";

 if(isset($extDocRef))
  $q.= ",ext_docref='".$db->Purify($extDocRef)."'";

 if(isset($discount1))
  $q.= ",discount_1='".$discount1."'";
 if(isset($discount2))
  $q.= ",discount_2='".$discount2."'";
 if(isset($unconditionalDiscount))
  $q.= ",unconditional_discount='".$unconditionalDiscount."'";
 if(isset($rebate))
  $q.= ",rebate='".$rebate."'";
 if(isset($stamp))
  $q.= ",stamp='".$stamp."'";

 if(isset($exp1name))
  $q.= ",exp_1_name='".$exp1name."'";
 if(isset($exp1vatid))
  $q.= ",exp_1_vatid='".$exp1vatid."'";
 if(isset($exp1amount))
  $q.= ",exp_1_amount='".$exp1amount."'";

 if(isset($exp2name))
  $q.= ",exp_2_name='".$exp2name."'";
 if(isset($exp2vatid))
  $q.= ",exp_2_vatid='".$exp2vatid."'";
 if(isset($exp2amount))
  $q.= ",exp_2_amount='".$exp2amount."'";

 if(isset($exp3name))
  $q.= ",exp_3_name='".$exp3name."'";
 if(isset($exp3vatid))
  $q.= ",exp_3_vatid='".$exp3vatid."'";
 if(isset($exp3amount))
  $q.= ",exp_3_amount='".$exp3amount."'";

 /* Shipping */
 if(isset($shipContactId))
  $q.= ",ship_contact_id='".$shipContactId."'";
 if(isset($shipRecp))
  $q.= ",ship_recp='".$db->Purify($shipRecp)."'";
 if(isset($shipAddr))
  $q.= ",ship_addr='".$db->Purify($shipAddr)."'";
 if(isset($shipCity))
  $q.= ",ship_city='".$db->Purify($shipCity)."'";
 if(isset($shipZip))
  $q.= ",ship_zip='".$shipZip."'";
 if(isset($shipProv))
  $q.= ",ship_prov='".$shipProv."'";
 if(isset($shipCC))
  $q.= ",ship_cc='".$shipCC."'";

 /* Transport */
 if(isset($transMethod))
  $q.= ",trans_method='".$transMethod."'";
 if(isset($transShipper))
  $q.= ",trans_shipper='".$db->Purify($transShipper)."'";
 if(isset($transNumberPlate))
  $q.= ",trans_numplate='".$transNumberPlate."'";
 if(isset($transCausal))
  $q.= ",trans_causal='".$db->Purify($transCausal)."'";
 if(isset($transDateTime))
  $q.= ",trans_datetime='".date('Y:m:d H:i',$transDateTime)."'";
 if(isset($transAspect))
  $q.= ",trans_aspect='".$db->Purify($transAspect)."'";
 if(isset($transNum))
  $q.= ",trans_num='".$transNum."'";
 if(isset($transWeight))
  $q.= ",trans_weight='".$transWeight."'";
 if(isset($transFreight))
  $q.= ",trans_freight='".$transFreight."'";

 /* Other */
 if(isset($referenceId))
  $q.= ",reference_id='".$referenceId."'";

 if($q)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");
 $db->Close();


 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_catget($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_cdinfo_catget($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
    case 'subject' : $subjectName=true; break;
	case 'subjectid' : $subjectId=true; break;
	case 'referenceid' : $referenceId=true; break;
	case 'pricelist' : case 'pricelistid' : $pricelistId=true; break;
	case 'paymentmode' : $paymentMode=true; break;
	case 'bank' : case 'banksupport' : $bankSupportId=true; break;
	case 'status' : $status=true; break;
	case 'tag' : $tag=true; break;

	/* DATES */
	case 'print-date' : $printDate=true; break;
	case 'send-date' : $sendDate=true; break;
	case 'pending-date' : $pendingDate=true; break;
	case 'start-working-date' : $startWorkingDate=true; break;
	case 'suspension-date' : $suspensionDate=true; break;
	case 'failure-date' : $failureDate=true; break;
	case 'completion-date' : $completionDate=true; break;
	case 'conversion-date' : $conversionDate=true; break;
	case 'grouping-date' : $groupingDate=true; break;
	case 'payment-date' : $paymentDate=true; break;
	case 'validity-date' : $validityDate=true; break;
	case 'charter-datefrom' : $charterDateFrom=true; break;
	case 'charter-dateto' : $charterDateTo=true; break;

	case 'conv-doc' : case 'conv-id' : $convDocId=true; break;
	case 'group-doc' : case 'group-id' : $groupDocId=true; break;
	case 'extdocref' : case 'ext-docref' : case 'ext-doc-ref' : $extDocRef=true; break;

	/* SHIPPING */
	case 'ship-contact-id' : $shipContactId=true; break;
	case 'ship-recp' : $shipRecp=true; break;
	case 'ship-addr' : case 'ship-address' : $shipAddr=true; break;
	case 'ship-city' : $shipCity=true; break;
	case 'ship-zip' : case 'ship-zipcode' : $shipZip=true; break;
	case 'ship-prov' : case 'ship-province' : $shipProv=true; break;
	case 'ship-cc' : case 'ship-countrycode' : $shipCC=true; break;

	/* TRANSPORT */
	case 'trans-method' : $transMethod=true; break;
	case 'trans-shipper' : $transShipper=true; break;
	case 'trans-numplate' : $transNumberPlate=true; break;
	case 'trans-causal' : $transCausal=true; break;
	case 'trans-date' : case 'trans-datetime' : case 'trans-time' : $transDateTime=true; break;
	case 'trans-aspect' : $transAspect=true; break;
	case 'trans-num' : $transNum=true; break;
	case 'trans-weight' : $transWeight=true; break;
	case 'trans-freight' : $transFreight=true; break;

	case 'amount' : $amount=true; break;
	case 'vat' : $vat = true; break;
	case 'total' : $total = true; break;
	case 'discount' : $discount = true; break;
    case 'expenses' : $expenses = true; break;
	case 'allvats' : $allVats=true; break;			// Importi divisi per aliquote.

	case 'totritacc' : $totRitAcc=true; break;  	// Totale rit. d'acconto
	case 'totccp' : $totCCP=true; break; 			// Totale contributo Cassa Previdenza
	case 'totrinps' : $totRINPS=true; break;		// Totale rivalsa INPS
	case 'totenasarco' : $totEnasarco=true; break;	// Totale rit. Enasarco
	case 'totnetpay' : $totNetPay=true; break;		// Netto a pagare
  }

 if(!count($args))
  $all=true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();

 if($subjectName || $all) $itemInfo['subject_name'] = $db->record['subject_name'];
 if($subjectId || $all) $itemInfo['subject_id'] = $db->record['subject_id'];
 if($referenceId || $all) $itemInfo['reference_id'] = $db->record['reference_id'];
 if($paymentMode || $all) $itemInfo['paymentmode'] = $db->record['payment_mode'];
 if($bankSupportId || $all) $itemInfo['banksupport_id'] = $db->record['bank_support'];
 if($pricelistId || $all) $itemInfo['pricelist_id'] = $db->record['pricelist_id'];
 if($status || $all) $itemInfo['status'] = $db->record['status'];
 if($tag || $all) $itemInfo['tag'] = $db->record['tag'];

 if($convDocId || $all) {
	 $itemInfo['conv_doc_id'] = $db->record['conv_doc_id'];
	 if($itemInfo['conv_doc_id'])
	 {
	  $db2 = new AlpaDatabase();
	  $db2->RunQuery("SELECT name FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['conv_doc_id']."'");
	  $db2->Read();
	  $itemInfo['conv_doc_name'] = $db2->record['name'];
	  $db2->Close();
	 }
	}
 if($groupDocId || $all) {
	 $itemInfo['group_doc_id'] = $db->record['group_doc_id'];
	 if($itemInfo['group_doc_id'])
	 {
	  $db2 = new AlpaDatabase();
	  $db2->RunQuery("SELECT name FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['group_doc_id']."'");
	  $db2->Read();
	  $itemInfo['group_doc_name'] = $db2->record['name'];
	  $db2->Close();
	 }
	}

 if($extDocRef || $all)
  $itemInfo['ext_docref'] = $db->record['ext_docref'];

 /* Dates */
 if($printDate || $all) $itemInfo['print_date'] = $db->record['print_date'];
 if($sendDate || $all) $itemInfo['send_date'] = $db->record['send_date'];
 if($pendingDate || $all) $itemInfo['pending_date'] = $db->record['pending_date'];
 if($startWorkingDate || $all) $itemInfo['start_working_date'] = $db->record['start_working_date'];
 if($suspensionDate || $all) $itemInfo['suspension_date'] = $db->record['suspension_date'];
 if($failureDate || $all) $itemInfo['failure_date'] = $db->record['failure_date'];
 if($completionDate || $all) $itemInfo['completion_date'] = $db->record['completion_date'];
 if($conversionDate || $all) $itemInfo['conversion_date'] = $db->record['conversion_date'];
 if($groupingDate || $all) $itemInfo['grouping_date'] = $db->record['grouping_date'];
 if($paymentDate || $all) $itemInfo['payment_date'] = $db->record['payment_date'];
 if($validityDate || $all) $itemInfo['validity_date'] = $db->record['validity_date'];
 if($charterDateFrom || $all) $itemInfo['charter_datefrom'] = $db->record['charter_datefrom'];
 if($charterDateTo || $all) $itemInfo['charter_dateto'] = $db->record['charter_dateto'];

 /* Shipping */
 if($shipContactId || $all) $itemInfo['ship_contact_id'] = $db->record['ship_contact_id'];
 if($shipRecp || $all) $itemInfo['ship_recp'] = $db->record['ship_recp'];
 if($shipAddr || $all) $itemInfo['ship_addr'] = $db->record['ship_addr'];
 if($shipCity || $all) $itemInfo['ship_city'] = $db->record['ship_city'];
 if($shipZip || $all) $itemInfo['ship_zip'] = $db->record['ship_zip'];
 if($shipProv || $all) $itemInfo['ship_prov'] = $db->record['ship_prov'];
 if($shipCC || $all) $itemInfo['ship_cc'] = $db->record['ship_cc'];

 /* Transport */
 if($transMethod || $all) $itemInfo['trans_method'] = $db->record['trans_method'];
 if($transShipper || $all) $itemInfo['trans_shipper'] = $db->record['trans_shipper'];
 if($transNumberPlate || $all) $itemInfo['trans_numplate'] = $db->record['trans_numplate'];
 if($transCausal || $all) $itemInfo['trans_causal'] = $db->record['trans_causal'];
 if($transDateTime || $all) $itemInfo['trans_datetime'] = strtotime($db->record['trans_datetime']);
 if($transAspect || $all) $itemInfo['trans_aspect'] = $db->record['trans_aspect'];
 if($transNum || $all) $itemInfo['trans_num'] = $db->record['trans_num'];
 if($transWeight || $all) $itemInfo['trans_weight'] = $db->record['trans_weight'];
 if($transFreight || $all) $itemInfo['trans_freight'] = $db->record['trans_freight'];

 if($amount || $all) $itemInfo['amount'] = $db->record['amount'];
 if($vat || $all) $itemInfo['vat'] = $db->record['vat'];
 if($total || $all) $itemInfo['total'] = $db->record['total'];
 if($discount || $all) 
 {
  $itemInfo['discount'] = $db->record['discount_1'];
  $itemInfo['discount2'] = $db->record['discount_2'];
  $itemInfo['uncondisc'] = $db->record['unconditional_discount'];
  $itemInfo['rebate'] = $db->record['rebate'];  
 }
 if($expenses || $all)
 {
  $itemInfo['exp1name'] = $db->record['exp_1_name'];
  $itemInfo['exp1vatid'] = $db->record['exp_1_vatid'];
  $itemInfo['exp1amount'] = $db->record['exp_1_amount'];
  $itemInfo['exp2name'] = $db->record['exp_2_name'];
  $itemInfo['exp2vatid'] = $db->record['exp_2_vatid'];
  $itemInfo['exp2amount'] = $db->record['exp_2_amount'];
  $itemInfo['exp3name'] = $db->record['exp_3_name'];
  $itemInfo['exp3vatid'] = $db->record['exp_3_vatid'];
  $itemInfo['exp3amount'] = $db->record['exp_3_amount'];
 }
 if($allVats || $all)
 {
  $itemInfo['vat_1_id'] = $db->record['vat_1_id'];
  $itemInfo['vat_1_taxable'] = $db->record['vat_1_taxable'];
  $itemInfo['vat_1_tax'] = $db->record['vat_1_tax'];
  $itemInfo['vat_2_id'] = $db->record['vat_2_id'];
  $itemInfo['vat_2_taxable'] = $db->record['vat_2_taxable'];
  $itemInfo['vat_2_tax'] = $db->record['vat_2_tax'];
  $itemInfo['vat_3_id'] = $db->record['vat_3_id'];
  $itemInfo['vat_3_taxable'] = $db->record['vat_3_taxable'];
  $itemInfo['vat_3_tax'] = $db->record['vat_3_tax'];
 }
 

 if($totRitAcc || $all) $itemInfo['tot_rit_acc'] = $db->record['tot_rit_acc'];
 if($totCCP || $all) $itemInfo['tot_ccp'] = $db->record['tot_ccp'];
 if($totRINPS || $all) $itemInfo['tot_rinps'] = $db->record['tot_rinps'];
 if($totEnasarco || $all) $itemInfo['tot_enasarco'] = $db->record['tot_enasarco'];
 if($totNetPay || $all) $itemInfo['tot_netpay'] = $db->record['tot_netpay'];

 $itemInfo['tot_discount'] = $db->record['tot_discount'];
 $itemInfo['tot_goods'] = $db->record['tot_goods'];
 $itemInfo['discounted_goods'] = $db->record['discounted_goods'];
 $itemInfo['tot_expenses'] = $db->record['tot_expenses'];
 $itemInfo['stamp'] = $db->record['stamp'];

 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_export($sessid, $shellid, $archiveInfo, $itemInfo)
{
 $xml = "";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_import($sessid, $shellid, $archiveInfo, $itemInfo, $node)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdinfo_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

