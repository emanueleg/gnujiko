<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-10-2016
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: 
 #VERSION: 2.34beta
 #CHANGELOG: 24-10-2016 : MySQLi integration.
			 24-05-2016 : Aggiunto campo ccpApply.
			 07-03-2016 : Aggiunto campi row_ref_docap, row_ref_docid, row_ref_id
			 01-02-2016 : Aggiunto campi qty_sent e qty_downloaded.
			 29-01-2016 : Bug fix sugli sconti quando vengono azzerati.
			 31-03-2015 : Sostituito DECIMAL(10,4) con DECIMAL(10,5).
			 03-03-2015 : Bug fix sui decimali (price, discount_inc e price_adjust).
			 04-02-2015 : Aggiunto campi metric.
			 13-12-2014 : Aggiunto campo xml_data
			 06-12-2014 : Aggiunto campo rit_acc_apply.
			 03-11-2014 : Aggiunto variant_coltint e variant_sizmis.
			 29-10-2014 : Aggiunto mancode su funzione get.
			 01-10-2014 : Aggiunto vendor-id
			 16-09-2014 : Aggiunto vendor-price e sale-price.
			 12-07-2014 : Aggiunto docrefap e docrefid per prenotazione articoli su ordine,commessa,ecc...
			 28-05-2014 : Integrato sistema di gestione marche
			 26-05-2014 : Aggiunto markup-rate
			 23-05-2014 : Aggiunto vencode - codice articolo fornitore
			 12-03-2014 : Aggiunto unitprice, unitprice_vi, total su funzione get.
			 26-02-2014 : Aggiunto thumb_img su funzione get.
			 15-01-2014 : Aggiunta prezzo base e prezzo di listino (baseprice e listbaseprice) su funzione dynarcextension_cdelements_get
			 07-10-2013 : Rimosso updateTotals and updateVatRegister.
			 30-09-2013 : Aggiunto sconto incondizionato.
			 21-09-2013 : Bug fix in function updateTotals
			 31-07-2013 : Aggiunto il lotto di produzione
			 15-07-2013 : Bug fix on rit. enasarco.
			 03-07-2013 : Aggiunto Rit. d'acconto,cassa prev, rivalsa inps e rit. enasarco.
			 07-05-2013 : Aggiunto peso articolo
			 04-05-2013 : Fatto in modo che in stampa esca l'importo dell'iva per ogni rigo. Su funz. get() aggiunto 'vat'.
			 12-04-2013 : Aggiunto le 3 colonne degli sconti.
			 11-04-2013 : Aggiunto 2 colonne extra_qty e price_adjust.
			 10-04-2013 : Predisposto per le colonne extra.
			 04-02-2013 : Bug fix vari.
			 23-01-2013 : Bug fix in updateVatRegister
			 03-12-2012 : Completamento delle funzioni principali.
 #TODO: Rifare funzione import & export e completare funzioni syncimport & syncexport.
 #TODO: Sarebbe da completare la funzione per gli UNLINKED_ROWS su funzione set: setallsent
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_elements` (
  `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `uid` INT( 11 ) NOT NULL ,
  `item_id` INT( 11 ) NOT NULL ,
  `elem_type` VARCHAR( 64 ) NOT NULL ,
  `ref_ap` VARCHAR( 64 ) NOT NULL ,
  `ref_id` INT( 11 ) NOT NULL ,
  `ordering` INT( 11 ) NOT NULL ,
  `code` VARCHAR( 64 ) NOT NULL ,
  `serial_number` VARCHAR( 64 ) NOT NULL ,
  `lot` VARCHAR( 64 ) NOT NULL ,
  `account_id` INT( 11 ) NOT NULL ,
  `name` VARCHAR( 255 ) NOT NULL ,
  `description` TEXT NOT NULL ,
  `qty` FLOAT NOT NULL ,
  `extra_qty` FLOAT NOT NULL ,
  `qty_sent` FLOAT UNSIGNED NOT NULL ,
  `qty_downloaded` FLOAT UNSIGNED NOT NULL ,
  `units` VARCHAR( 16 ) NOT NULL ,
  `price` DECIMAL(10,5) NOT NULL ,
  `price_adjust` DECIMAL(10,5) NOT NULL ,
  `discount_perc` FLOAT NOT NULL ,
  `discount_inc` DECIMAL(10,5) NOT NULL ,
  `discount2` FLOAT NOT NULL,
  `discount3` FLOAT NOT NULL,
  `vat_rate` FLOAT NOT NULL ,
  `vat_id` INT ( 11 ) NOT NULL ,
  `vat_type` VARCHAR ( 32 ) NOT NULL ,
  `pricelist_id` INT( 11 ) NOT NULL ,
  `vencode` VARCHAR(64) NOT NULL,
  `plbaseprice` DECIMAL(10,5) NOT NULL,
  `plmrate` FLOAT NOT NULL,
  `pldiscperc` FLOAT NOT NULL,
  `brand_id` INT(11) NOT NULL,
  `doc_ref_ap` VARCHAR(64) NOT NULL,
  `doc_ref_id` INT(11) NOT NULL,
  `vendor_price` DECIMAL(10,5) NOT NULL,
  `sale_price` DECIMAL(10,5) NOT NULL,
  `vendor_id` INT(11) NOT NULL,
  `variant_coltint` VARCHAR(64) NOT NULL,
  `variant_sizmis` VARCHAR(64) NOT NULL,
  `rit_acc_apply` TINYINT(1) NOT NULL,
  `ccp_apply` TINYINT(1) NOT NULL,
  `xml_data` TEXT NOT NULL,
  `metric_length` FLOAT NOT NULL,
  `metric_width` FLOAT NOT NULL,
  `metric_hw` FLOAT NOT NULL,
  `metric_eqp` FLOAT NOT NULL,
  `row_ref_docap` VARCHAR(32) NOT NULL ,
  `row_ref_docid` INT(11) NOT NULL ,
  `row_ref_id` INT(11) NOT NULL,
  INDEX ( `item_id` , `ordering` , `lot`, `doc_ref_ap`, `doc_ref_id`) 
)");
 $db->Close();

 return array("message"=>"GCommercialDocs:Elements extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_elements`");
 $db->Close();

 return array("message"=>"GCommercialDocs:Elements extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 /* Aggiusta il magazzino */
 GShell("commercialdocs fix-book-inc-arts -docid '".$itemInfo['id']."' -action delete",$sessid,$shellid);

 /* REMOVE ALL ITEM EVENTS */
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_elements WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 /* REMOVE FROM VAT REGISTER */
 GShell("vatregister delete -year `".date('Y',$itemInfo['ctime'])."` -docap `".$archiveInfo['prefix']."` -docid `".$itemInfo['id']."`",$sessid,$shellid);

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_set($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_COMPANY_PROFILE;
 include_once($_BASE_PATH."include/company-profile.php");

 $qty = 1;
 $oldQty = 0;
 $extraQry = "";
 $extraColumns = array();

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'type' : {$type=$args[$c+1]; $c++;} break;
   case 'refap' : {$refAP=$args[$c+1]; $c++;} break;
   case 'refid' : {$refID=$args[$c+1]; $c++;} break;
   case 'ordering' : {$ordering=$args[$c+1]; $c++;} break;
   case 'code' : {$code=$args[$c+1]; $c++;} break;
   case 'vencode' : {$vencode=$args[$c+1]; $c++;} break;
   case 'sn' : case 'serialnumber' : {$serialNumber=$args[$c+1]; $c++;} break;
   case 'lot' : {$lot=$args[$c+1]; $c++;} break;
   case 'account' : case 'accountid' : {$accountId=$args[$c+1]; $c++;} break;
   case 'name' : {$name=$args[$c+1]; $c++;} break;
   case 'desc' : case 'description' : {$description=$args[$c+1]; $c++;} break;
   case 'qty' : {$qty=$args[$c+1]; $c++;} break;
   case 'extraqty' : {$extraQty=$args[$c+1]; $c++;} break;
   case 'qtysent' : {$qtySent=$args[$c+1]; $c++;} break;
   case 'qtydownloaded' : case 'qtydl' : {$qtyDownloaded=$args[$c+1]; $c++;} break;
   case 'vendorprice' : {$vendorPrice=$args[$c+1]; $c++;} break;
   case 'saleprice' : {$salePrice=$args[$c+1]; $c++;} break;
   case 'price' : {$price=$args[$c+1]; $c++;} break;
   case 'priceadjust' : {$priceAdjust=$args[$c+1]; $c++;} break;
   case 'discount' : {$discount=$args[$c+1]; $c++;} break;
   case 'discount2' : {$discount2=$args[$c+1]; $c++;} break;
   case 'discount3' : {$discount3=$args[$c+1]; $c++;} break;
   case 'vat' : case 'vatrate' : {$vatRate=$args[$c+1]; $c++;} break;
   case 'vatid' : {$vatId=$args[$c+1]; $c++;} break;
   case 'vattype' : {$vatType=$args[$c+1]; $c++;} break;
   case 'units' : case 'umis' : {$units=$args[$c+1]; $c++;} break;
   case 'pricelist' : case 'pricelistid' : {$pricelistId=$args[$c+1]; $c++;} break;
   case 'plbaseprice' : {$plbaseprice=$args[$c+1]; $c++;} break;
   case 'plmrate' : {$plmrate=$args[$c+1]; $c++;} break;
   case 'pldiscperc' : {$pldiscperc=$args[$c+1]; $c++;} break;
   case 'brand' : {$brand=$args[$c+1]; $c++;} break;
   case 'brandid' : {$brandId=$args[$c+1]; $c++;} break;
   case 'docrefap' : case 'docap' : {$docRefAP=$args[$c+1]; $c++;} break;
   case 'docrefid' : case 'docid' : {$docRefID=$args[$c+1]; $c++;} break;
   case 'vendorid' : {$vendorId=$args[$c+1]; $c++;} break;
   case 'coltint' : case 'color' : case 'tint' : {$coltint=$args[$c+1]; $c++;} break;
   case 'sizmis' : case 'size' : case 'misure' : {$sizmis=$args[$c+1]; $c++;} break;
   case 'ritaccapply' : {$ritAccApply=$args[$c+1]; $c++;} break;
   case 'ccpapply' : {$ccpApply=$args[$c+1]; $c++;} break;
   case 'xmldata' : {$xmlData=$args[$c+1]; $c++;} break;
   
   case 'metriclength' : {$metricLength=$args[$c+1]; $c++;} break;
   case 'metricwidth' : {$metricWidth=$args[$c+1]; $c++;} break;
   case 'metrichw' : {$metricHW=$args[$c+1]; $c++;} break;
   case 'metriceqp' : {$metricEqP=$args[$c+1]; $c++;} break;

   case 'rowrefdocap' : case 'rowdocap' : case 'rowap' : {$rowRefDocAP=$args[$c+1]; $c++;} break;
   case 'rowrefdocid' : case 'rowdocid' : {$rowRefDocID=$args[$c+1]; $c++;} break;
   case 'rowrefid' : {$rowRefID=$args[$c+1]; $c++;} break;

   case 'serialize' : {$serialize=$args[$c+1]; $c++;} break;

   case 'bypass-vatregister-update' : {$bypassVatRegisterUpdate=$args[$c+1]; $c++;} break;

   case 'setalldownloaded' : case 'set-all-downloaded' : {$setAllDownloaded=$args[$c+1]; $c++;} break;
   case 'setallsent' : {$setAllSent=$args[$c+1]; $c++;} break;

   default : {
	 for($i=0; $i < count($_COMPANY_PROFILE['extracolumns']); $i++)
	 {
	  if($args[$c] == $_COMPANY_PROFILE['extracolumns'][$i]['tag'])
	  {
	   $extraColumns[$args[$c]] = $args[$c+1];
	   $c++;
	   break;
	  }
	 }
	} break;
  }

 $sessInfo = sessionInfo($sessid);

 if($serialize)
 {
  $ser = explode(",",$serialize);
  $db = new AlpaDatabase();
  for($c=0; $c < count($ser); $c++)
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_elements SET ordering='".($c+1)."' WHERE id='".$ser[$c]."' AND item_id='".$itemInfo['id']."'");
  $db->Close();
  return $itemInfo;
 }

 // SET ALL DOWNLOADED AND RETURN
 if($setAllDownloaded)
 {
  $types = array("article","finalproduct","component","material","custom");

  $q = "elem_type='".$types[0]."'";
  for($c=1; $c < count($types); $c++)
   $q.= " OR elem_type='".$types[$c]."'";

  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_elements SET qty_downloaded=qty WHERE item_id='".$itemInfo['id']."' AND (".$q.")");
  $db->Close();
 
  return $itemInfo;
 }

 // SET ALL SENT AND RETURN
 if($setAllSent)
 {
  $types = array("article","finalproduct","component","material","custom");

  $q = "elem_type='".$types[0]."'";
  for($c=1; $c < count($types); $c++)
   $q.= " OR elem_type='".$types[$c]."'";

  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_elements SET qty_sent=qty WHERE item_id='".$itemInfo['id']."' AND (".$q.")");
  $db->Close();
  
  $rows = array();
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT ref_ap,ref_id,code,name,qty,lot,vencode,row_ref_docap,row_ref_docid,row_ref_id FROM dynarc_"
	.$archiveInfo['prefix']."_elements WHERE item_id='".$itemInfo['id']."' ORDER BY id ASC");
  while($db->Read())
  {
   $rows[] = $db->record;
  }
  $db->Close();

  $unlinkedRows = array();
  $docs = array();
  $db = new AlpaDatabase();
  for($c=0; $c < count($rows); $c++)
  {
   $row = $rows[$c];
   if($row['row_ref_docap'] && $row['row_ref_docid'] && $row['row_ref_id'])
   {
    if(!$docs[$row['row_ref_docap']])
	 $docs[$row['row_ref_docap']] = array();
    if(!in_array($row['row_ref_docid'], $docs[$row['row_ref_docap']]))
	 $docs[$row['row_ref_docap']][] = $row['row_ref_docid'];

	$db->RunQuery("UPDATE dynarc_".$row['row_ref_docap']."_elements SET qty_sent=qty_sent+".$row['qty']." WHERE id='"
		.$row['row_ref_id']."'");
	if($db->Error) return array('message'=>"MySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
   }
   else
	$unlinkedRows[] = $row;
  }
  $db->Close();

  reset($docs);
  while(list($ap, $list) = each($docs))
  {
   $db = new AlpaDatabase();
   for($c=0; $c < count($list); $c++)
   {
    $docid = $list[$c];
    $db->RunQuery("SELECT SUM(qty), SUM(qty_sent) FROM dynarc_".$ap."_elements WHERE item_id='".$docid."' AND (".$q.")");
	$db->Read();
	if($db->record['SUM(qty)'] == $db->record['SUM(qty_sent)'])
	 $db->RunQuery("UPDATE dynarc_".$ap."_items SET entirely_proc='1',partial_proc='0' WHERE id='".$docid."'");
	else if($db->record['SUM(qty_sent)'] && ($db->record['SUM(qty_sent)'] < $db->record['SUM(qty)']))
	 $db->RunQuery("UPDATE dynarc_".$ap."_items SET entirely_proc='0',partial_proc='1' WHERE id='".$docid."'");
   }
   $db->Close();
  }

  /* TODO: IF UNLINKED ROWS
  $docrefList = array();
  if(count($unlinkedRows))
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT docref_ap,docref_id FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
   $db->Read();
   if($db->record['docref_ap'] && $db->record['docref_id'])
	$docrefList[] = array('ap'=>$db->record['docref_ap'], 'id'=>$db->record['docref_id']);

   $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_items WHERE conv_doc_id='".$itemInfo['id']."' OR group_doc_id='"
	.$itemInfo['id']."' AND trash='0'");
   while($db->Read())
	$docrefList[] = array('ap'=>'commercialdocs', 'id'=>$db->record['id']);
   $db->Close();

   // TODO: continuare da qui...
  } */


  return $itemInfo;
 }

 if(isset($vatRate) && !$vatId)
 {// detect vat rate info //
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id,vat_type FROM dynarc_vatrates_items WHERE percentage='".$vatRate."' AND trash='0' ORDER BY ordering ASC LIMIT 1");
  if($db->Read())
  {
   $vatId = $db->record['id'];
   $vatType = $db->record['vat_type'];
  }
  $db->Close();
 }

 if($brand && !$brandId)
 {
  // get brand id
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_brands_items WHERE name='".$db->Purify($brand)."' AND trash='0' LIMIT 1");
  if($db->Read())
   $brandId = $db->record['id'];
  else
   $brandId = 0;
  $db->Close();
 }
 else if(isset($brand) && !isset($brandId))
  $brandId = 0;
 

 if($id && isset($qty))
 {
  // get old qty
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT qty FROM dynarc_".$archiveInfo['prefix']."_elements WHERE id='".$id."'");
  $db->Read();
  $oldQty = $db->record['qty'];
  $db->Close();

  if($oldQty != $qty)
  {
   /* adjust store */
   GShell("commercialdocs fix-book-inc-arts -elid '".$id."' -action delete",$sessid,$shellid);
  }
 }
 

 if($id)
 {
  $db = new AlpaDatabase();
  $q = "";
  
  if($type)						$q.= ",elem_type='".$type."'";
  if(isset($refAP))				$q.= ",ref_ap='".$refAP."'";
  if(isset($refID))				$q.= ",ref_id='".$refID."'";
  if(isset($ordering))			$q.= ",ordering='".$ordering."'";
  if(isset($code))				$q.= ",code='".$code."'";
  if(isset($vencode))			$q.= ",vencode='".$vencode."'";
  if(isset($serialNumber))		$q.= ",serial_number='".$serialNumber."'";
  if(isset($lot))				$q.= ",lot='".$lot."'";
  if(isset($accountId))			$q.= ",account_id='".$accountId."'";
  if(isset($name))				$q.= ",name='".$db->Purify($name)."'";
  if(isset($description))		$q.= ",description='".$db->Purify($description)."'";
  if(isset($qty))				$q.= ",qty='".$qty."'";
  if(isset($extraQty))			$q.= ",extra_qty='".$extraQty."'";
  if(isset($qtySent))			$q.= ",qty_sent='".$qtySent."'";
  if(isset($qtyDownloaded))		$q.= ",qty_downloaded='".$qtyDownloaded."'";
  if(isset($units))				$q.= ",units='".$units."'";
  if(isset($vendorPrice))		$q.= ",vendor_price='".$vendorPrice."'";
  if(isset($salePrice))			$q.= ",sale_price='".$salePrice."'";
  if(isset($price))				$q.= ",price='".$price."'";
  if(isset($priceAdjust))		$q.= ",price_adjust='".$priceAdjust."'";
  if(isset($discount))
  {
   if(!$discount)
	$q.= ",discount_perc='0',discount_inc='0'";
   else if(strpos($discount,"%") !== false)
	$q.= ",discount_perc='".rtrim($discount,"%")."',discount_inc='0'";
   else
	$q.= ",discount_inc='".$discount."',discount_perc='0'";
  }
  if(isset($discount2))			$q.= ",discount2='".$discount2."'";
  if(isset($discount3))			$q.= ",discount3='".$discount3."'";
  if(isset($vatRate))			$q.= ",vat_rate='".$vatRate."'";
  if(isset($vatId))				$q.= ",vat_id='".$vatId."'";
  if(isset($vatType))			$q.= ",vat_type='".$vatType."'";
  if(isset($pricelistId))		$q.= ",pricelist_id='".$pricelistId."'";
  if(isset($plbaseprice))		$q.= ",plbaseprice='".$plbaseprice."'";
  if(isset($plmrate))			$q.= ",plmrate='".$plmrate."'";
  if(isset($pldiscperc))		$q.= ",pldiscperc='".$pldiscperc."'";
  if(isset($brandId))			$q.= ",brand_id='".$brandId."'";
  if(isset($docRefAP))			$q.= ",doc_ref_ap='".$docRefAP."'";
  if(isset($docRefID))			$q.= ",doc_ref_id='".$docRefID."'";
  if(isset($vendorId))			$q.= ",vendor_id='".$vendorId."'";
  if(isset($coltint))			$q.= ",variant_coltint='".$db->Purify($coltint)."'";
  if(isset($sizmis))			$q.= ",variant_sizmis='".$db->Purify($sizmis)."'";
  if(isset($ritAccApply))		$q.= ",rit_acc_apply='".$ritAccApply."'";
  if(isset($ccpApply))			$q.= ",ccp_apply='".$ccpApply."'";
  if(isset($xmlData))			$q.= ",xml_data='".$db->Purify($xmlData)."'";

  if(isset($metricLength))		$q.= ",metric_length='".$metricLength."'";
  if(isset($metricWidth))		$q.= ",metric_width='".$metricWidth."'";
  if(isset($metricHW))			$q.= ",metric_hw='".$metricHW."'";
  if(isset($metricEqP))			$q.= ",metric_eqp='".$metricEqP."'";

  if(isset($rowRefDocAP))		$q.= ",row_ref_docap='".$rowRefDocAP."'";
  if(isset($rowRefDocID))		$q.= ",row_ref_docid='".$rowRefDocID."'";
  if(isset($rowRefID))			$q.= ",row_ref_id='".$rowRefID."'";

  reset($extraColumns);
  while(list($k,$v) = each($extraColumns))
  {
   $q.= ",".$k."='".$db->Purify($v)."'";
  }

  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_elements SET ".ltrim($q,',')." WHERE id='$id'");
  $db->Close();
 }
 else
 {
  $db = new AlpaDatabase();
  if(!isset($ordering))
  {
   $db->RunQuery("SELECT ordering FROM dynarc_".$archiveInfo['prefix']."_elements WHERE item_id='".$itemInfo['id']."' ORDER BY ordering DESC LIMIT 1");
   if($db->Read())
    $ordering = $db->record['ordering']+1;
   else
    $ordering=1;
  }

  $qry = "INSERT INTO dynarc_".$archiveInfo['prefix']."_elements(uid,item_id,elem_type,ref_ap,ref_id,ordering,code,vencode,serial_number,lot,
account_id,name,description,qty,extra_qty,qty_sent,qty_downloaded,vendor_price,price,sale_price,price_adjust,discount_perc,discount_inc,discount2,discount3,vat_rate,vat_id,vat_type,pricelist_id,units,
plbaseprice,plmrate,pldiscperc,brand_id,doc_ref_ap,doc_ref_id,vendor_id,variant_coltint,variant_sizmis,rit_acc_apply,ccp_apply,xml_data";
  $qry.= ",metric_length,metric_width,metric_hw,metric_eqp,row_ref_docap,row_ref_docid,row_ref_id";
  reset($extraColumns);
  while(list($k,$v) = each($extraColumns))
  {
   $qry.= ",".$k;
  }
  $qry.= ") VALUES('".$sessInfo['uid']."','".$itemInfo['id']."','".$type."','".$refAP."','".$refID."','".$ordering."','".$code."','".$vencode."','"
	.$serialNumber."','".$lot."','".$accountId."','".$db->Purify($name)."','".$db->Purify($description)."','".$qty."','".$extraQty."','"
	.$qtySent."','".$qtyDownloaded."','"
	.$vendorPrice."','".$price."','".$salePrice."','".$priceAdjust."','".(strpos($discount,"%") !== false ? rtrim($discount,"%") : 0)."','"
	.(strpos($discount,"%") !== false ? 0 : $discount)."','".$discount2."','".$discount3."','".$vatRate."','".$vatId."','".$vatType."','"
	.$pricelistId."','".$units."','".$plbaseprice."','".$plmrate."','".$pldiscperc."','".$brandId."','".$docRefAP."','".$docRefID."','"
	.$vendorId."','".$db->Purify($coltint)."','".$db->Purify($sizmis)."','".$ritAccApply."','".$ccpApply."','".$db->Purify($xmlData)."','"
	.$metricLength."','".$metricWidth."','".$metricHW."','".$metricEqP."','".$rowRefDocAP."','".$rowRefDocID."','".$rowRefID."'";
  reset($extraColumns);
  while(list($k,$v) = each($extraColumns))
  {
   $qry.= ",'".$db->Purify($v)."'";
  }
  $qry.= ")";

  $db->RunQuery($qry);
  if($db->Error)
   return array('message'=>'MySQL Error: '.$db->Error.'\nQRY: '.$qry, 'error'=>'MYSQL_ERROR');

  $id = $db->GetInsertId();
  $db->Close();
 }

 $itemInfo['last_element'] = array('id'=>$id, 'uid'=>$sessInfo['uid'], 'item_id'=>$itemInfo['id'],'type'=>$type,'refap'=>$refAP,'refid'=>$refID,
	'ordering'=>$ordering,'code'=>$code,'vencode'=>$vencode,'serialnumber'=>$serialNumber,'lot'=>$lot,'account_id'=>$accountId,'name'=>$name,
	'desc'=>$description,'qty'=>$qty,'extraqty'=>$extraQty,'qty_sent'=>$qtySent,'qty_downloaded'=>$qtyDownloaded,'vendor_price'=>$vendorPrice,
	'price'=>$price,'sale_price'=>$salePrice,
	'priceadjust'=>$priceAdjust,'discount'=>$discount,'discount2'=>$discount2,'discount3'=>$discount3,'vat_rate'=>$vatRate,'vat_id'=>$vatId,
	'vat_type'=>$vatType,'pricelist_id'=>$pricelistId,'units'=>$units,'plbaseprice'=>$plbaseprice,'plmrate'=>$plmrate,
	'pldiscperc'=>$pldiscperc,'brand_id'=>$brandId,'doc_ref_ap'=>$docRefAP, 'doc_ref_id'=>$docRefID, 'vendor_id'=>$vendorId, 
	'variant_coltint'=>$coltint, 'variant_sizmis'=>$sizmis, 'ritaccapply'=>$ritAccApply, 'ccpapply'=>$ccpApply, 'xmldata'=>$xmlData, 
	'metric_length'=>$metricLength, 'metric_width'=>$metricWidth, 'metric_hw'=>$metricHW, 'metric_eqp'=>$metricEqP, 
	'row_ref_docap'=>$rowRefDocAP, 'row_ref_docid'=>$rowRefDocID,'row_ref_id'=>$rowRefID);

 if($oldQty != $qty)
 {
  /* adjust store */
  GShell("commercialdocs fix-book-inc-arts -elid '".$id."' -action new",$sessid,$shellid);
 }

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'all' : $all=true; break;
   case 'bypass-vatregister-update' : {$bypassVatRegisterUpdate=$args[$c+1]; $c++;} break;
  }
 
 $isANote=false;
 $db = new AlpaDatabase();
 if($id)
 {
  $db->RunQuery("SELECT elem_type FROM dynarc_".$archiveInfo['prefix']."_elements WHERE id='$id'");
  $db->Read();
  if($db->record['elem_type'] == "note")
   $isANote=true;
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_elements WHERE id='$id'");
  /* Aggiusta il magazzino */
  GShell("commercialdocs fix-book-inc-arts -elid '".$id."' -action delete",$sessid,$shellid);
 }
 else if($all)
 {
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_elements WHERE item_id='".$itemInfo['id']."'");
  /* Aggiusta il magazzino */
  GShell("commercialdocs fix-book-inc-arts -docid '".$itemInfo['id']."' -action delete",$sessid,$shellid);
 }
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_get($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_COMPANY_PROFILE;
 include_once($_BASE_PATH."include/company-profile.php");

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'type' : $type=true; break;
   case 'refap' : $refAP=true; break;
   case 'refid' : $refID=true; break;
   case 'ordering' : $ordering=true; break;
   case 'code' : $code=true; break;
   case 'vencode' : $vencode=true; break;
   case 'sn' : case 'serialnumber' : $serialNumber=true; break;
   case 'lot' : $lot=true; break;
   case 'account' : case 'accountid' : $accountId=true; break;
   case 'name' : $name=true; break;
   case 'desc' : case 'description' : $description=true; break;
   case 'qty' : $qty=true; break;
   case 'extraqty' : $extraQty=true; break;
   case 'qtysent' : $qtySent=true; break;
   case 'qtydownloaded' : case 'qtydl' : $qtyDownloaded=true; break;
   case 'vendorprice' : $vendorPrice=true; break;
   case 'saleprice' : $salePrice=true; break;
   case 'price' : $price=true; break;
   case 'priceadjust' : $priceAdjust=true; break;
   case 'discount' : $discount=true; break;
   case 'vat' : case 'vatrate' : $vatRate=true; break;
   case 'vatid' : $vatId=true; break;
   case 'vattype' : $vatType=true; break;
   case 'units' : case 'umis' : $units=true; break;
   case 'pricelist' : case 'pricelistid' : $pricelistId=true; break;
   case 'weight' : $weight=true; break;
   case 'plbaseprice' : $plbaseprice=true; break;
   case 'plmrate' : $plmrate=true; break;
   case 'pldiscperc' : $pldiscperc=true; break;
   case 'brand' : $brand=true; break;
   case 'docref' : $docRef=true; break;
   case 'vendor' : $vendor=true; break;
   case 'coltint' : $coltint=true; break;
   case 'sizmis' : $sizmis=true; break;
   case 'ritaccapply' : $ritAccApply=true; break;
   case 'ccpapply' : $ccpApply=true; break;
   case 'xmldata' : $xmlData=true; break;
   case 'metric' : $metric=true; break;
  }

 if(!count($args))
  $all = true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_elements WHERE item_id='".$itemInfo['id']."' ORDER BY ordering ASC");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id']);
  $a['row_ref_docap'] = $db->record['row_ref_docap'];
  $a['row_ref_docid'] = $db->record['row_ref_docid'];
  $a['row_ref_id'] = $db->record['row_ref_id'];

  if($type || $all) $a['type'] = $db->record['elem_type'];
  if($refAP || $all) $a['ref_ap'] = $db->record['ref_ap'];
  if($refID || $all) $a['ref_id'] = $db->record['ref_id'];
  if($ordering || $all) $a['ordering'] = $db->record['ordering'];
  if($code || $all) $a['code'] = $db->record['code'];
  if($vencode || $all) $a['vencode'] = $db->record['vencode'];
  if($serialNumber || $all) $a['serialnumber'] = $db->record['serial_number'];
  if($lot || $all) $a['lot'] = $db->record['lot'];
  if($accountId || $all) $a['account_id'] = $db->record['account_id'];
  if($name || $all) $a['name'] = $db->record['name'];
  if($description || $all) $a['desc'] = $db->record['description'];
  if($qty || $all) $a['qty'] = $db->record['qty'];
  if($extraQty || $all) $a['extraqty'] = $db->record['extra_qty'];
  if($qtySent || $all) $a['qty_sent'] = $db->record['qty_sent'];
  if($qtyDownloaded || $all) $a['qty_downloaded'] = $db->record['qty_downloaded'];
  if($vendorPrice || $all) $a['vendor_price'] = $db->record['vendor_price'];
  if($salePrice || $all) $a['sale_price'] = $db->record['sale_price'];
  if($price || $all) $a['price'] = $db->record['price'];
  if($priceAdjust || $all) $a['priceadjust'] = $db->record['price_adjust'];
  if($ritAccApply || $all) $a['ritaccapply'] = $db->record['rit_acc_apply'];
  if($ccpApply || $all) $a['ccpapply'] = $db->record['ccp_apply'];
  if($xmlData || $all) $a['xmldata'] = $db->record['xml_data'];
  if($metric || $all)
  {
   $a['metric_length'] = $db->record['metric_length'];
   $a['metric_width'] = $db->record['metric_width'];
   $a['metric_hw'] = $db->record['metric_hw'];
   $a['metric_eqp'] = $db->record['metric_eqp'];
  }
  if($discount || $all) 
  {
   $a['discount_perc'] = $db->record['discount_perc'];
   $a['discount_inc'] = $db->record['discount_inc'];
   if($a['discount_perc'])
    $a['discount'] = $a['discount_perc']."%";
   else
	$a['discount'] = $a['discount_inc'];
   $a['discount2'] = $db->record['discount2'];
   $a['discount3'] = $db->record['discount3'];
  }
  if($vatRate || $all) $a['vatrate'] = $db->record['vat_rate'];
  if($vatId || $all) $a['vatid'] = $db->record['vat_id'];
  if($vatType || $all) $a['vattype'] = $db->record['vat_type'];
  if($units || $all) $a['units'] = $db->record['units'];
  if($pricelistId || $all) $a['pricelist_id'] = $db->record['pricelist_id'];
  if($plbaseprice || $all) $a['plbaseprice'] = $db->record['plbaseprice'];
  if($plmrate || $all) $a['plmrate'] = $db->record['plmrate'];
  if($pldiscperc || $all) $a['pldiscperc'] = $db->record['pldiscperc'];
  if($brand || $all) $a['brand_id'] = $db->record['brand_id'];
  if($docRef || $all)
  {
   $a['doc_ref_ap'] = $db->record['doc_ref_ap'];
   $a['doc_ref_id'] = $db->record['doc_ref_id'];
  }
  if($vendor || $all)
  {
   $a['vendor_id'] = $db->record['vendor_id'];
   if($a['vendor_id'])
   {
	// get vendor name
	$db2 = new AlpaDatabase();
	$db2->RunQuery("SELECT name FROM dynarc_rubrica_items WHERE id='".$a['vendor_id']."'");
	$db2->Read();
	$a['vendor_name'] = $db2->record['name'];
	$db2->Close();
   }
  }
  if($coltint || $all)	$a['variant_coltint'] = $db->record['variant_coltint'];
  if($sizmis || $all)	$a['variant_sizmis'] = $db->record['variant_sizmis'];

  if($db->record['ref_ap'] && $db->record['ref_id'])
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT * FROM dynarc_".$db->record['ref_ap']."_items WHERE id='".$db->record['ref_id']."'");
   $db2->Read();

   $a['mancode'] = $db2->record['manufacturer_code'];
   $a['weight'] = $db2->record['weight'];
   $a['weightunits'] = $db2->record['weightunits'];
   $a['baseprice'] = $db2->record['baseprice'];
   $a['listbaseprice'] = $a['pricelist_id'] ? $db2->record['pricelist_'.$a['pricelist_id'].'_baseprice'] : $a['baseprice'];
   $a['listdiscperc'] = $a['pricelist_id'] ? $db2->record['pricelist_'.$a['pricelist_id'].'_discount'] : 0;
   $a['listmrate'] = $a['pricelist_id'] ? $db2->record['pricelist_'.$a['pricelist_id'].'_mrate'] : 0;
   $discountedPrice = $a['listbaseprice'];
   if($discountedPrice && $a['listmrate'])
    $discountedPrice = $discountedPrice + (($discountedPrice/100)*$a['listmrate']);
   if($discountedPrice && $a['listdiscperc'])
    $discountedPrice = $discountedPrice - (($discountedPrice/100)*$a['listdiscperc']);
   $a['discountedprice'] = $discountedPrice;
   $a['thumb_img'] = $db2->record['thumb_img'];
   if(!$a['desc'])
	$a['desc'] = $db2->record['description'];
   $db2->Close();
  }

  $amount = $db->record['price'] ? $db->record['price'] : 0;
  if($amount)
  {
   if($db->record['discount_perc'])
    $amount-= (($amount/100)*$db->record['discount_perc']);
   else if($db->record['discount_inc'])
	$amount-= $db->record['discount_inc'];
   if($db->record['discount2'])
	$amount-= (($amount/100)*$db->record['discount2']);
   if($db->record['discount2'] && $db->record['discount3'])
	$amount-= (($amount/100)*$db->record['discount3']);
  }
  $a['unitprice'] = $amount;
  $a['unitprice_vi'] = $amount + ($amount ? (($amount/100) * $a['vatrate']) : 0);
  $amount*= ($db->record['qty'] * ($db->record['extra_qty'] ? $db->record['extra_qty'] : 1));
  $a['amount'] = $amount;
  $a['vat'] = $amount ? (($amount/100) * $a['vatrate']) : 0;
  $a['total'] = $a['amount']+$a['vat'];

  if($a['amount'])
  {
   $a['profit'] = $a['amount'] - ($a['vendor_price'] * ($db->record['qty'] * ($db->record['extra_qty'] ? $db->record['extra_qty'] : 1)));
   $a['margin'] = ($a['profit'] / $a['amount']) * 100;
  }

  /* EXTRA COLUMNS */
  for($c=0; $c < count($_COMPANY_PROFILE['extracolumns']); $c++)
   $a[$_COMPANY_PROFILE['extracolumns'][$c]['tag']] = $db->record[$_COMPANY_PROFILE['extracolumns'][$c]['tag']];

  if($a['brand_id'])
  {
   // get brand name
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT name FROM dynarc_brands_items WHERE id='".$a['brand_id']."'");
   if($db2->Read())
    $a['brand_name'] = $db2->record['name'];
   $db2->Close();
  }

  $itemInfo['elements'][] = $a;
 }
 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 /* Aggiusta il magazzino */
 GShell("commercialdocs fix-book-inc-arts -docid '".$itemInfo['id']."' -action delete",$sessid,$shellid);

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 /* Aggiusta il magazzino */
 GShell("commercialdocs fix-book-inc-arts -docid '".$itemInfo['id']."' -action restore",$sessid,$shellid);

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_export($sessid, $shellid, $archiveInfo, $itemInfo)
{
 $xml = "";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_import($sessid, $shellid, $archiveInfo, $itemInfo, $node)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cdelements_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

