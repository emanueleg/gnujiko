<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-05-2017
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Archive functions for GCommercialDocs
 #VERSION: 2.15beta
 #CHANGELOG: 28-05-2017 : Aggiunto campo root_ct.
			 09-05-2015 : Integrato con tickets. (ondelete events)
			 20-04-2015 : Integrato con contratti. (ondelete events)
			 02-10-2014 : Integrato con Fatture Socio
			 22-08-2014 : Integrate impostazioni per rit.acconto,riv.inps,enasarco,ecc..
			 23-05-2014 : Aggiunto DDT Fornitore
			 27-01-2014 : Aggiunto parametri di default su scheda trasporto caricabili da file di configurazione etc/commercialdocs/config.php.
			 18-10-2013 : Aggiunto i bolli.
			 07-10-2013 : Rimosso updateVatRegister.
			 08-07-2013 : Aggiunto le ricevute fiscali.
			 03-07-2013 : Aggiunto Rit. d'acconto,cassa prev, rivalsa inps e rit. enasarco.
			 29-05-2013 : Ora imposta in automatico data e ora del trasporto.
			 12-04-2013 : Aggiunto le 3 colonne degli sconti.
			 11-04-2013 : Aggiunto colonne extra_qty e price_adjust.
			 28-01-2013 : Integrato con PaymentNotice.
			 03-12-2012 : Completamento delle funzioni principali.
 #TODO: Modificare le scritte in italiano utilizzando i18n.
 
*/

function dynarcfunction_commercialdocs_oninheritarchive($args, $sessid, $shellid, $archiveInfo)
{

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_commercialdocs_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 global $_COMPANY_PROFILE, $_COMMERCIALDOCS_CONFIG;
 include_once($_BASE_PATH."include/company-profile.php");
 include_once($_BASE_PATH."etc/commercialdocs/config.php");

 $_CPA = $_COMPANY_PROFILE['accounting'];

 $_STAMP = $_COMPANY_PROFILE['accounting']['amount_stamp_receipt'] ? $_COMPANY_PROFILE['accounting']['amount_stamp_receipt'] : 0;

 $prefix = "";
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT tag,parent_id FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$itemInfo['cat_id']."'");
 if($db->Read())
 {
  if($db->record['parent_id'])
  {
   $prefix = $db->record['tag'];
   $db->RunQuery("SELECT tag FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$db->record['parent_id']."'");
   $db->Read();
   $catTag = $db->record['tag']; 
  }
  else
   $catTag = $db->record['tag'];
 }
 $db->Close();

 /* Get last document and adjust document number */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT code_num FROM dynarc_".$archiveInfo['prefix']."_items WHERE cat_id='".$itemInfo['cat_id']."' AND id!='"
	.$itemInfo['id']."' AND trash=0 AND ctime>='".date('Y',$itemInfo['ctime'])."-01-01' AND ctime<'".date('Y',strtotime("+1 Year",$itemInfo['ctime']))."-01-01' ORDER BY code_num DESC LIMIT 1");
 if($db->Read())
  $itemInfo['code_num'] = $db->record['code_num']+1;
 else
  $itemInfo['code_num'] = 1; 
 $db->Close();

 if($prefix)
  $itemInfo['code_ext'] = $prefix;

 $title = "";
 switch($catTag)
 {
  case 'PREEMPTIVES' : $title = "Preventivo"; break;
  case 'INVOICES' : $title = "Fattura"; break;
  case 'ORDERS' : $title = "Ordine"; break;
  case 'VENDORORDERS' : $title = "Ordine fornitore"; break;
  case 'DDT' : $title = "D.D.T."; break;
  case 'DDTIN' : $title = "D.D.T. Fornitore"; break;
  case 'PURCHASEINVOICES' : $title = "Fattura d&lsquo;acquisto"; break;
  case 'AGENTINVOICES' : $title = "Fattura agente"; break;
  case 'MEMBERINVOICES' : $title = "Fattura socio"; break;
  case 'INTERVREPORTS' : $title = "Rapporto d&lsquo;intervento"; break;
  case 'CREDITSNOTE' : $title = "Nota di accredito"; break;
  case 'DEBITSNOTE' : $title = "Nota di debito"; break;
  case 'PAYMENTNOTICE' : $title = "Avv. di pag."; break;
  case 'RECEIPTS' : $title = "Ricevuta Fiscale"; break;

  default : $title = "Documento"; break;
 }

 $transDateTime = "0000-00-00 00:00:00";
 $transMethod = "";
 $transShipper = "";
 $transNumPlate = "";
 $transCausal = "";
 $transAspect = "";
 $transFreight = "";
 $transCartage = 0;
 $transPackingCharges = 0;

 if(($catTag == "INVOICES") || ($catTag == "DDT") || ($catTag == "ORDERS"))
 {
  /* IMPOSTA LA DATA E L'ORA DEL TRASPORTO */
  $transDateTime = date('Y-m-d H:i:s',$itemInfo['ctime']);
  /* IMPOSTA I PARAMETRI DI DEFAULT SUL TRASPORTO */
  $transMethod = $_COMMERCIALDOCS_CONFIG['DEFTRANSDET']['trans_method'];
  $transShipper = $_COMMERCIALDOCS_CONFIG['DEFTRANSDET']['trans_shipper'];
  $transNumPlate = $_COMMERCIALDOCS_CONFIG['DEFTRANSDET']['trans_numplate'];
  $transCausal = $_COMMERCIALDOCS_CONFIG['DEFTRANSDET']['trans_causal'];
  $transAspect = $_COMMERCIALDOCS_CONFIG['DEFTRANSDET']['trans_aspect'];
  $transFreight = $_COMMERCIALDOCS_CONFIG['DEFTRANSDET']['trans_freight'];
  $transCartage = $_COMMERCIALDOCS_CONFIG['DEFTRANSDET']['cartage'];
  $transPackingCharges = $_COMMERCIALDOCS_CONFIG['DEFTRANSDET']['packing_charges'];
 }

 $title.= " n&deg;".$itemInfo['code_num'].($itemInfo['code_ext'] ? "/".$itemInfo['code_ext'] : "")." del ".date('d/m/Y',$itemInfo['ctime']);

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET code_num='".$itemInfo['code_num']."',code_ext='"
	.$itemInfo['code_ext']."',name='".$db->Purify($title)."',ctime='".date('Y-m-d',$itemInfo['ctime'])."',trans_datetime='"
	.$transDateTime."',stamp='".$_STAMP."',trans_method='".$transMethod."',trans_shipper='".$db->Purify($transShipper)."',trans_numplate='"
	.$transNumPlate."',trans_causal='".$db->Purify($transCausal)."',trans_aspect='".$db->Purify($transAspect)."',trans_freight='"
	.$transFreight."',cartage='".$transCartage."',packing_charges='".$transPackingCharges."',rivalsa_inps='"
	.$_CPA['rivalsa_inps']."',contr_cassa_prev='".$_CPA['contr_cassa_prev']."',contr_cassa_prev_vatid='"
	.$_CPA['contr_cassa_prev_vatid']."',rit_enasarco='".$_CPA['rit_enasarco']."',rit_enasarco_percimp='"
	.$_CPA['rit_enasarco_percimp']."',rit_acconto='".$_CPA['rit_acconto']."',rit_acconto_percimp='"
	.$_CPA['rit_acconto_percimp']."',rit_acconto_rivinpsinc='".$_CPA['rit_acconto_rivinpsinc']."',root_ct='".$catTag."' WHERE id='".$itemInfo['id']."'");
 $db->Close();

 $itemInfo['name'] = $title;
 
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_commercialdocs_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_commercialdocs_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 global $_BASE_PATH;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT tag,parent_id FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$itemInfo['cat_id']."'");
 if($db->Read())
 {
  if($db->record['parent_id'])
  {
   $db->RunQuery("SELECT tag FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$db->record['parent_id']."'");
   $db->Read();
   $catTag = $db->record['tag']; 
  }
  else
   $catTag = $db->record['tag'];
 }
 $db->Close();

 $title = "";
 switch($catTag)
 {
  case 'PREEMPTIVES' : $title = "Preventivo"; break;
  case 'INVOICES' : $title = "Fattura"; break;
  case 'ORDERS' : $title = "Ordine"; break;
  case 'VENDORORDERS' : $title = "Ordine fornitore"; break;
  case 'DDT' : $title = "D.D.T."; break;
  case 'DDTIN' : $title = "D.D.T. Fornitore"; break;
  case 'PURCHASEINVOICES' : $title = "Fattura d&lsquo;acquisto"; break;
  case 'AGENTINVOICES' : $title = "Fattura agente"; break;
  case 'MEMBERINVOICES' : $title = "Fattura socio"; break;
  case 'INTERVREPORTS' : $title = "Rapporto d&lsquo;intervento"; break;
  case 'CREDITSNOTE' : $title = "Nota di accredito"; break;
  case 'DEBITSNOTE' : $title = "Nota di debito"; break;
  case 'PAYMENTNOTICE' : $title = "Avv. di pag."; break;
  case 'RECEIPTS' : $title = "Ricevuta Fiscale"; break;

  default : $title = "Documento"; break;
 }

 $title.= " n&deg;".$itemInfo['code_num'].($itemInfo['code_ext'] ? "/".$itemInfo['code_ext'] : "")." del ".date('d/m/Y',$itemInfo['ctime']);

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET name='".$db->Purify($title)."',ctime='".date('Y-m-d',$itemInfo['ctime'])."',root_ct='"
	.$catTag."' WHERE id='".$itemInfo['id']."'");
 $db->Close();

 $itemInfo['name'] = $title;

 // update invoice_name on contract
 if(file_exists($_BASE_PATH."Contracts/index.php"))
 {
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_contracts_schedule SET invoice_name='".$db->Purify($title)."' WHERE invoice_id='".$itemInfo['id']."'");
  $db->Close();
 }

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_commercialdocs_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_commercialdocs_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_commercialdocs_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 global $_BASE_PATH;
 // Remove record from the register //
 GShell("vatregister delete -year `".date('Y',$itemInfo['ctime'])."` -docap `".$archiveInfo['prefix']."` -docid `".$itemInfo['id']."`",$sessid,$shellid);

 // update on contract
 if(file_exists($_BASE_PATH."Contracts/index.php"))
 {
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_contracts_schedule SET invoice_id='0',invoice_name='' WHERE invoice_id='".$itemInfo['id']."'");
  $db->Close();
 }

 // update on tickets
 if(file_exists($_BASE_PATH."Tickets/index.php"))
 {
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_tickets_items SET preemptive_id='0' WHERE preemptive_id='".$itemInfo['id']."'");
  $db->RunQuery("UPDATE dynarc_tickets_items SET invoice_id='0' WHERE invoice_id='".$itemInfo['id']."'");
  $db->Close();
 }
 

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_commercialdocs_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_commercialdocs_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 global $_BASE_PATH;

 // Remove record from the register //
 GShell("vatregister delete -year `".date('Y',$itemInfo['ctime'])."` -docap `".$archiveInfo['prefix']."` -docid `".$itemInfo['id']."`",$sessid,$shellid);

 // update on contract
 if(file_exists($_BASE_PATH."Contracts/index.php"))
 {
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_contracts_schedule SET invoice_id='0',invoice_name='' WHERE invoice_id='".$itemInfo['id']."'");
  $db->Close();
 }

 // update on tickets
 if(file_exists($_BASE_PATH."Tickets/index.php"))
 {
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_tickets_items SET preemptive_id='0' WHERE preemptive_id='".$itemInfo['id']."'");
  $db->RunQuery("UPDATE dynarc_tickets_items SET invoice_id='0' WHERE invoice_id='".$itemInfo['id']."'");
  $db->Close();
 }


 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_commercialdocs_onmoveitem($sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_commercialdocs_onmovecategory($sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_commercialdocs_oncopyitem($sessid, $shellid, $archiveInfo, $cloneInfo, $srcInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_commercialdocs_oncopycategory($sessid, $shellid, $archiveInfo, $cloneInfo, $srcInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_commercialdocs_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_commercialdocs_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 /* TODO: sarebbe da ripristinare il documento sui contratti e sui tickets */
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

