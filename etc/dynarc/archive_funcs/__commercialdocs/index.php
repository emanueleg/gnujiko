<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-10-2013
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Archive functions for GCommercialDocs
 #VERSION: 2.8beta
 #CHANGELOG: 18-10-2013 : Aggiunto i bolli.
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
 global $_COMPANY_PROFILE;
 include_once($_BASE_PATH."include/company-profile.php");

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
  case 'PURCHASEINVOICES' : $title = "Fattura d&lsquo;acquisto"; break;
  case 'AGENTINVOICES' : $title = "Fattura agente"; break;
  case 'INTERVREPORTS' : $title = "Rapporto d&lsquo;intervento"; break;
  case 'CREDITSNOTE' : $title = "Nota di accredito"; break;
  case 'DEBITSNOTE' : $title = "Nota di debito"; break;
  case 'PAYMENTNOTICE' : $title = "Avv. di pag."; break;
  case 'RECEIPTS' : $title = "Ricevuta Fiscale"; break;

  default : $title = "Documento"; break;
 }

 $transDateTime = "0000-00-00 00:00:00";
 if(($catTag == "INVOICES") || ($catTag == "DDT"))
 {
  /* IMPOSTA LA DATA E L'ORA DEL TRASPORTO */
  $transDateTime = date('Y-m-d H:i:s',$itemInfo['ctime']);
 }

 $title.= " n&deg;".$itemInfo['code_num'].($itemInfo['code_ext'] ? "/".$itemInfo['code_ext'] : "")." del ".date('d/m/Y',$itemInfo['ctime']);

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET code_num='".$itemInfo['code_num']."',code_ext='"
	.$itemInfo['code_ext']."',name='".$db->Purify($title)."',ctime='".date('Y-m-d',$itemInfo['ctime'])."',trans_datetime='"
	.$transDateTime."',stamp='".$_STAMP."' WHERE id='".$itemInfo['id']."'");
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
  case 'PURCHASEINVOICES' : $title = "Fattura d&lsquo;acquisto"; break;
  case 'AGENTINVOICES' : $title = "Fattura agente"; break;
  case 'INTERVREPORTS' : $title = "Rapporto d&lsquo;intervento"; break;
  case 'CREDITSNOTE' : $title = "Nota di accredito"; break;
  case 'DEBITSNOTE' : $title = "Nota di debito"; break;
  case 'PAYMENTNOTICE' : $title = "Avv. di pag."; break;
  case 'RECEIPTS' : $title = "Ricevuta Fiscale"; break;

  default : $title = "Documento"; break;
 }

 $title.= " n&deg;".$itemInfo['code_num'].($itemInfo['code_ext'] ? "/".$itemInfo['code_ext'] : "")." del ".date('d/m/Y',$itemInfo['ctime']);

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET name='".$db->Purify($title)."',ctime='".date('Y-m-d',$itemInfo['ctime'])."' WHERE id='".$itemInfo['id']."'");
 $db->Close();

 $itemInfo['name'] = $title;

 //commercialdocs_updateVatRegister($sessid, $shellid, $archiveInfo, $itemInfo);

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
 // Remove record from the register //
 GShell("vatregister delete -year `".date('Y',$itemInfo['ctime'])."` -docap `".$archiveInfo['prefix']."` -docid `".$itemInfo['id']."`",$sessid,$shellid);
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
 // Remove record from the register //
 GShell("vatregister delete -year `".date('Y',$itemInfo['ctime'])."` -docap `".$archiveInfo['prefix']."` -docid `".$itemInfo['id']."`",$sessid,$shellid);

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
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

