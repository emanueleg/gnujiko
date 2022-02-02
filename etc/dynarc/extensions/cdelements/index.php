<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-10-2013
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: 
 #VERSION: 2.14beta
 #CHANGELOG: 07-10-2013 : Rimosso updateTotals and updateVatRegister.
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
  `units` VARCHAR( 16 ) NOT NULL ,
  `price` FLOAT NOT NULL ,
  `price_adjust` FLOAT NOT NULL ,
  `discount_perc` FLOAT NOT NULL ,
  `discount_inc` FLOAT NOT NULL ,
  `discount2` FLOAT NOT NULL,
  `discount3` FLOAT NOT NULL,
  `vat_rate` FLOAT NOT NULL ,
  `vat_id` INT ( 11 ) NOT NULL ,
  `vat_type` VARCHAR ( 32 ) NOT NULL ,
  `pricelist_id` INT( 11 ) NOT NULL ,
  INDEX ( `item_id` , `ordering` , `lot`) 
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
   case 'sn' : case 'serialnumber' : {$serialNumber=$args[$c+1]; $c++;} break;
   case 'lot' : {$lot=$args[$c+1]; $c++;} break;
   case 'account' : case 'accountid' : {$accountId=$args[$c+1]; $c++;} break;
   case 'name' : {$name=$args[$c+1]; $c++;} break;
   case 'desc' : case 'description' : {$description=$args[$c+1]; $c++;} break;
   case 'qty' : {$qty=$args[$c+1]; $c++;} break;
   case 'extraqty' : {$extraQty=$args[$c+1]; $c++;} break;
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

   case 'serialize' : {$serialize=$args[$c+1]; $c++;} break;

   case 'bypass-vatregister-update' : {$bypassVatRegisterUpdate=$args[$c+1]; $c++;} break;

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

 if($id)
 {
  $db = new AlpaDatabase();
  $q = "";
  
  if($type)
   $q.= ",elem_type='".$type."'";
  if(isset($refAP))
   $q.= ",ref_ap='".$refAP."'";
  if(isset($refID))
   $q.= ",ref_id='".$refID."'";
  if(isset($ordering))
   $q.= ",ordering='".$ordering."'";
  if(isset($code))
   $q.= ",code='".$code."'";
  if(isset($serialNumber))
   $q.= ",serial_number='".$serialNumber."'";
  if(isset($lot))
   $q.= ",lot='".$lot."'";
  if(isset($accountId))
   $q.= ",account_id='".$accountId."'";
  if(isset($name))
   $q.= ",name='".$db->Purify($name)."'";
  if(isset($description))
   $q.= ",description='".$db->Purify($description)."'";
  if(isset($qty))
   $q.= ",qty='".$qty."'";
  if(isset($extraQty))
   $q.= ",extra_qty='".$extraQty."'";
  if(isset($units))
   $q.= ",units='".$units."'";
  if(isset($price))
   $q.= ",price='".$price."'";
  if(isset($priceAdjust))
   $q.= ",price_adjust='".$priceAdjust."'";
  if(isset($discount))
  {
   if(strpos($discount,"%") !== false)
	$q.= ",discount_perc='".rtrim($discount,"%")."'";
   else
	$q.= ",discount_inc='".$discount."'";
  }
  if(isset($discount2))
   $q.= ",discount2='".$discount2."'";
  if(isset($discount3))
   $q.= ",discount3='".$discount3."'";
  if(isset($vatRate))
   $q.= ",vat_rate='".$vatRate."'";
  if(isset($vatId))
   $q.= ",vat_id='".$vatId."'";
  if(isset($vatType))
   $q.= ",vat_type='".$vatType."'";
  if(isset($pricelistId))
   $q.= ",pricelist_id='".$pricelistId."'";

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

  $qry = "INSERT INTO dynarc_".$archiveInfo['prefix']."_elements(uid,item_id,elem_type,ref_ap,ref_id,ordering,code,serial_number,lot,
account_id,name,description,qty,extra_qty,price,price_adjust,discount_perc,discount_inc,discount2,discount3,vat_rate,vat_id,vat_type,pricelist_id,units";
  reset($extraColumns);
  while(list($k,$v) = each($extraColumns))
  {
   $qry.= ",".$k;
  }
  $qry.= ") VALUES('".$sessInfo['uid']."','".$itemInfo['id']."','".$type."','".$refAP."','".$refID."','".$ordering."','".$code."','"
	.$serialNumber."','".$lot."','".$accountId."','".$db->Purify($name)."','".$db->Purify($description)."','".$qty."','".$extraQty."','".$price."','"
	.$priceAdjust."','".(strpos($discount,"%") !== false ? rtrim($discount,"%") : 0)."','".(strpos($discount,"%") !== false ? 0 : $discount)."','"
	.$discount2."','".$discount3."','".$vatRate."','".$vatId."','".$vatType."','".$pricelistId."','".$units."'";
  reset($extraColumns);
  while(list($k,$v) = each($extraColumns))
  {
   $qry.= ",'".$db->Purify($v)."'";
  }
  $qry.= ")";

  $db->RunQuery($qry);

  $id = mysql_insert_id();
  $db->Close();
 }

 $itemInfo['last_element'] = array('id'=>$id, 'uid'=>$sessInfo['uid'], 'item_id'=>$itemInfo['id'],'type'=>$type,'refap'=>$refAP,'refid'=>$refID,
	'ordering'=>$ordering,'code'=>$code,'serialnumber'=>$serialNumber,'lot'=>$lot,'account_id'=>$accountId,'name'=>$name,'desc'=>$description,'qty'=>$qty,
	'extraqty'=>$extraQty,'price'=>$price,'priceadjust'=>$priceAdjust,'discount'=>$discount,'discount2'=>$discount2,'discount3'=>$discount3,
	'vat_rate'=>$vatRate,'vat_id'=>$vatId,'vat_type'=>$vatType,'pricelist_id'=>$pricelistId,'units'=>$units);

 /*dynarcextension_cdelements_updateTotals($sessid, $shellid, $archiveInfo, $itemInfo);
 if(!$bypassVatRegisterUpdate)
  dynarcextension_cdelements_updateVatRegister($sessid, $shellid, $archiveInfo, $itemInfo);*/

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
 }
 else if($all)
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_elements WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 if(!$isANote)
 {
  /*dynarcextension_cdelements_updateTotals($sessid, $shellid, $archiveInfo, $itemInfo);
  if(!$bypassVatRegisterUpdate)
   dynarcextension_cdelements_updateVatRegister($sessid, $shellid, $archiveInfo, $itemInfo);*/
 }

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
   case 'sn' : case 'serialnumber' : $serialNumber=true; break;
   case 'lot' : $lot=true; break;
   case 'account' : case 'accountid' : $accountId=true; break;
   case 'name' : $name=true; break;
   case 'desc' : case 'description' : $description=true; break;
   case 'qty' : $qty=true; break;
   case 'extraqty' : $extraQty=true; break;
   case 'price' : $price=true; break;
   case 'priceadjust' : $priceAdjust=true; break;
   case 'discount' : $discount=true; break;
   case 'vat' : case 'vatrate' : $vatRate=true; break;
   case 'vatid' : $vatId=true; break;
   case 'vattype' : $vatType=true; break;
   case 'units' : case 'umis' : $units=true; break;
   case 'pricelist' : case 'pricelistid' : $pricelistId=true; break;
   case 'weight' : $weight=true; break;
  }

 if(!count($args))
  $all = true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_elements WHERE item_id='".$itemInfo['id']."' ORDER BY ordering ASC");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id']);

  if($type || $all) $a['type'] = $db->record['elem_type'];
  if($refAP || $all) $a['ref_ap'] = $db->record['ref_ap'];
  if($refID || $all) $a['ref_id'] = $db->record['ref_id'];
  if($ordering || $all) $a['ordering'] = $db->record['ordering'];
  if($code || $all) $a['code'] = $db->record['code'];
  if($serialNumber || $all) $a['serialnumber'] = $db->record['serial_number'];
  if($lot || $all) $a['lot'] = $db->record['lot'];
  if($accountId || $all) $a['account_id'] = $db->record['account_id'];
  if($name || $all) $a['name'] = $db->record['name'];
  if($description || $all) $a['desc'] = $db->record['description'];
  if($qty || $all) $a['qty'] = $db->record['qty'];
  if($extraQty || $all) $a['extraqty'] = $db->record['extra_qty'];
  if($price || $all) $a['price'] = $db->record['price'];
  if($priceAdjust || $all) $a['priceadjust'] = $db->record['price_adjust'];
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

  if($db->record['ref_ap'] && $db->record['ref_id'])
  {
   if($all || $weight) // <-- inserire qui eventuali altri parametri da ricavare direttamente dall'archivio dell'articolo
   {
    $db2 = new AlpaDatabase();
	$db2->RunQuery("SELECT * FROM dynarc_".$db->record['ref_ap']."_items WHERE id='".$db->record['ref_id']."'");
	$db2->Read();
	if($weight || $all)
	{
	 $a['weight'] = $db2->record['weight'];
	 $a['weightunits'] = $db2->record['weightunits'];
	}
	$db2->Close();
   }
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
  $amount*= ($db->record['qty'] * ($db->record['extra_qty'] ? $db->record['extra_qty'] : 1));
  $a['amount'] = $amount;
  $a['vat'] = $amount ? (($amount/100) * $a['vatrate']) : 0;

  /* EXTRA COLUMNS */
  for($c=0; $c < count($_COMPANY_PROFILE['extracolumns']); $c++)
   $a[$_COMPANY_PROFILE['extracolumns'][$c]['tag']] = $db->record[$_COMPANY_PROFILE['extracolumns'][$c]['tag']];

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

