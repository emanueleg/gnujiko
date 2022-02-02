<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-11-2016
 #PACKAGE: gmart
 #DESCRIPTION: GMart basic info extension for Dynarc.
 #VERSION: 2.10beta
 #CHANGELOG: 24-11-2016 : Integrazione con SKU, asin, ean, gcid, gtin ed upc.
			 26-09-2015 : Aggiunto campo nascondi dal magazzino.
			 10-06-2014 : Aggiunta funzione onarchiveempty
			 28-05-2014 : Integrato gestione marche.
			 18-02-2014 : Completate funzioni import export.
			 23-10-2013 : Aggiunto confezionamento e divisione materiale.
			 11-07-2013 : Aggiunto ITEM_LOCATION per la collocazione degli articoli negli scaffali.
			 07-05-2013 : Aggiunto WEIGHT e WEIGHT UNITS
			 11-02-2013 : Bug fix.
			 03-12-2012 : Completamento delle funzioni principali.
 #TODO:
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` ADD `brand` VARCHAR( 32 ) NOT NULL ,
ADD `brand_id` INT( 11 ) NOT NULL ,
ADD `model` VARCHAR( 64 ) NOT NULL ,
ADD `barcode` VARCHAR( 32 ) NOT NULL ,
ADD `manufacturer_code` VARCHAR( 64 ) NOT NULL ,
ADD `item_location` VARCHAR( 64 ) NOT NULL ,
ADD `qty_sold` FLOAT NOT NULL ,
ADD `units` VARCHAR( 16 ) NOT NULL ,
ADD `weight` FLOAT NOT NULL ,
ADD `weightunits` VARCHAR ( 5 ) NOT NULL ,
ADD `gebinde` VARCHAR(32) NOT NULL ,
ADD `gebinde_code` VARCHAR(32) NOT NULL,
ADD `division` VARCHAR(32) NOT NULL,
ADD `hide_in_store` TINYINT(1) NOT NULL,
ADD INDEX (`barcode`,`brand_id`,`hide_in_store`)");

 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_categories` ADD `subcat_count` INT( 4 ) NOT NULL ,
ADD `items_count` INT( 4 ) NOT NULL ,
ADD `totitems_count` INT( 4 ) NOT NULL");

 $db->Close();
 return array("message"=>"GMart extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` DROP `brand`, DROP `model`, DROP `barcode`, DROP `manufacturer_code`,
  DROP `item_location`, DROP `qty_sold`, DROP `units`, DROP `weight`, DROP `weightunits`, DROP `brand_id`");
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_categories` DROP `subcat_count`,  DROP `items_count`,  DROP `totitems_count`, DROP `gebinde`, DROP `gebinde_code`, DROP `division`, DROP `hide_in_store`");
 $db->Close();

 return array("message"=>"GMart extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_catset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'subcatcount' : {$subcatCount=$args[$c+1]; $c++;} break;
   case 'itemscount' : {$itemsCount=$args[$c+1]; $c++;} break;
   case 'totitemscount' : {$totItemsCount=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 $q = "";
 if(isset($subcatCount))
  $q.= ",subcat_count='$subcatCount'";
 if(isset($itemsCount))
  $q.= ",items_count='$itemsCount'";
 if(isset($totItemsCount))
  $q.= ",totitems_count='$totItemsCount'";
 
 if($q)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET ".ltrim($q,",")." WHERE id='".$catInfo['id']."'");
 $db->Close();

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_gmart_catset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'brand' : {$brandName=$args[$c+1]; $c++;} break;
   case 'brandid' : {$brandId=$args[$c+1]; $c++;} break;
   case 'model' : {$model=$args[$c+1]; $c++;} break;
   case 'barcode' : {$barcode=$args[$c+1]; $c++;} break;
   case 'manufacturer-code' : case 'manufacturer_code' : case 'manufacturercode' : case 'mancode' : {$manufacturerCode=$args[$c+1]; $c++;} break;
   case 'location' : {$itemLocation=$args[$c+1]; $c++;} break;
   case 'sold' : case 'qty-sold' : case 'qtysold' : {$qtySold=$args[$c+1]; $c++;} break;
   case 'units' : case 'umis' : {$units=$args[$c+1]; $c++;} break;
   case 'weight' : {$weight=$args[$c+1]; $c++;} break;
   case 'weightunits' : {$weightUnits=$args[$c+1]; $c++;} break;
   case 'gebinde_code' : {$gebindeCode=$args[$c+1]; $c++;} break;   /* codice confezionamento */
   case 'gebinde' : {$gebinde=$args[$c+1]; $c++;} break; 			/* descrizione confezionamento */
   case 'division' : {$division=$args[$c+1]; $c++;} break; 			/* divisione materiale */
   case 'hideinstore' : {$hideInStore=$args[$c+1]; $c++;} break;	/* nascondi dal magazzino */

   case 'sku' : 	{$productSKU=$args[$c+1]; $c++;} break;				// SKU code
   case 'skuref' : 	{$productSKUreferrer = $args[$c+1]; $c++;} break;	// SKU referrer (es: Amazon, Goupon, ecc...)
   case 'asin' : 	{$codeASIN=$args[$c+1]; $c++;} break;
   case 'ean' : 	{$codeEAN=$args[$c+1]; $c++;} break;
   case 'gcid' : 	{$codeGCID=$args[$c+1]; $c++;} break;
   case 'gtin' : 	{$codeGTIN=$args[$c+1]; $c++;} break;
   case 'upc' : 	{$codeUPC=$args[$c+1]; $c++;} break;

   case 'spidtype' : {$spidType=$args[$c+1]; $c++;} break;
   case 'spidcode' : {$spidCode=$args[$c+1]; $c++;} break;
  }

 if($spidType)
 {
  $codeASIN=""; $codeEAN=""; $codeGCID=""; $codeGTIN=""; $codeUPC="";
  switch(strtoupper($spidType))
  {
   case 'ASIN' : $codeASIN=$spidCode; break;
   case 'EAN' : $codeEAN=$spidCode; break;
   case 'GCID' : $codeGCID=$spidCode; break;
   case 'GTIN' : $codeGTIN=$spidCode; break;
   case 'UPC' : $codeUPC=$spidCode; break;
  }

 }

 if($brandName && !$brandId)
 {
  // check if exists
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_brands_items WHERE name='".$db->Purify($brandName)."' AND trash='0' LIMIT 1");
  if($db->Read())
   $brandId = $db->record['id'];
  else
  {
   $ret = GShell("dynarc new-item -ap brands -ct gmart -name `".$brandName."`",$sessid,$shellid);
   if(!$ret['error'])
	$brandId = $ret['outarr']['id'];
  }
  $db->Close();
 }

 $db = new AlpaDatabase();
 $q="";
 if(isset($brandName))					$q.=",brand='".$db->Purify($brandName)."'";
 if(isset($brandId))					$q.=",brand_id='".$brandId."'";
 if(isset($model))						$q.= ",model='".$db->Purify($model)."'";
 if(isset($barcode))					$q.= ",barcode='".$barcode."'";
 if(isset($manufacturerCode))			$q.= ",manufacturer_code='".$manufacturerCode."'";
 if(isset($itemLocation))				$q.= ",item_location='".$db->Purify($itemLocation)."'";
 if(isset($qtySold))					$q.= ",qty_sold='".$qtySold."'";
 if(isset($units))						$q.= ",units='".$units."'";
 if(isset($weight))						$q.= ",weight='".$weight."'";
 if(isset($weightUnits))				$q.= ",weightunits='".$weightUnits."'";
 if(isset($gebinde))					$q.= ",gebinde='".$db->Purify($gebinde)."'";
 if(isset($gebindeCode))				$q.= ",gebinde_code='".$gebindeCode."'";
 if(isset($division))					$q.= ",division='".$division."'";
 if(isset($hideInStore))				$q.= ",hide_in_store='".$hideInStore."'";

 if($q)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");

 // UPDATE product_sku TABLE
 if(isset($productSKU))
 {
  if(!$productSKU) // remove this product from sku list
   $db->RunQuery("DELETE FROM product_sku WHERE ref_ap='".$archiveInfo['prefix']."' AND ref_id='".$itemInfo['id']."' AND referrer='"
	.$productSKUreferrer."' AND variant_id=0");
  else
  {
   // update or insert into product_sku table
   $db->RunQuery("SELECT id,sku FROM product_sku WHERE ref_ap='".$archiveInfo['prefix']."' AND ref_id='".$itemInfo['id']."' AND referrer='"
	.$productSKUreferrer."'");
   if($db->Read())
   {
	if($db->record['sku'] != $productSKU)
	 $db->RunQuery("UPDATE product_sku SET sku='".$productSKU."' WHERE id='".$db->record['id']."'");
   }
   else
	$db->RunQuery("INSERT INTO product_sku (ref_at,ref_ap,ref_id,sku,referrer) VALUES('".$archiveInfo['type']."','"
		.$archiveInfo['prefix']."','".$itemInfo['id']."','".$productSKU."','".$productSKUreferrer."')");

  }
 }

 // UPDATE product_spid TABLE
 if(isset($codeASIN) || isset($codeEAN) || isset($codeGCID) || isset($codeGTIN) || isset($codeUPC))
 {
  $db->RunQuery("SELECT id FROM product_spid WHERE ref_ap='".$archiveInfo['prefix']."' AND ref_id='".$itemInfo['id']."' AND variant_id=0");
  if($db->Read())
  {
   $q = "";
   if(isset($codeASIN))		$q.= ",asin='".$codeASIN."'";
   if(isset($codeEAN))		$q.= ",ean='".$codeEAN."'";
   if(isset($codeGCID))		$q.= ",gcid='".$codeGCID."'";
   if(isset($codeGTIN))		$q.= ",gtin='".$codeGTIN."'";
   if(isset($codeUPC))		$q.= ",upc='".$codeUPC."'";
   $db->RunQuery("UPDATE product_spid SET ".ltrim($q, ",")." WHERE id='".$db->record['id']."'");
  }
  else
   $db->RunQuery("INSERT INTO product_spid (ref_at,ref_ap,ref_id,asin,ean,gcid,gtin,upc) VALUES('".$archiveInfo['type']."','"
	.$archiveInfo['prefix']."','".$itemInfo['id']."','".$codeASIN."','".$codeEAN."','".$codeGCID."','".$codeGTIN."','".$codeUPC."')");
 }

 $db->Close();


 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_catget($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'subcatcount' : $subcatCount=true; break;
   case 'itemscount' : $itemsCount=true; break;
   case 'totitemscount' : $totItemsCount=true; break;
  }

 if(!count($args))
  $all=true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT subcat_count,items_count,totitems_count FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$catInfo['id']."'");
 $db->Read();
 if($subcatCount || $all)
  $catInfo['subcatcount'] = $db->record['subcat_count'];
 if($itemsCount || $all)
  $catInfo['itemscount'] = $db->record['items_count'];
 if($totItemsCount || $all)
  $catInfo['totitemscount'] = $db->record['totitems_count'];
 $db->Close();

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_gmart_catget($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'brand' : $brand=true; break;
   case 'model' : $model=true; break;
   case 'barcode' : $barcode=true; break;
   case 'manufacturer-code' : case 'manufacturer_code' : case 'manufacturercode' : case 'mancode' : $manufacturerCode=true; break;
   case 'location' : $itemLocation=true; break;
   case 'sold' : case 'qty-sold' : case 'qtysold' : $qtySold=true; break;
   case 'units' : case 'umis' : $units=true; break;
   case 'weight' : $weight=true; break;
   case 'gebinde' : $gebinde=true; break;
   case 'division' : $division=true; break;
   case 'hideinstore' : $hideInStore=true; break;
  }

 if(!count($args))
  $all=true;

 $db = new AlpaDatabase();

 $_QRY = "SELECT i.brand,i.brand_id,i.model,i.barcode,i.manufacturer_code,i.item_location,i.qty_sold,i.units,i.weight,i.weightunits";
 $_QRY.= ",i.gebinde,i.gebinde_code,i.division,i.hide_in_store";
 $_QRY.= ",s.sku, c.asin, c.ean, c.gcid, c.gtin, c.upc";
 $_QRY.= " FROM dynarc_".$archiveInfo['prefix']."_items AS i";
 $_QRY.= " LEFT JOIN `product_sku` AS s ON s.ref_ap='".$archiveInfo['prefix']."' AND s.ref_id='".$itemInfo['id']."' AND s.variant_id='0'";
 $_QRY.= " LEFT JOIN `product_spid` AS c ON c.ref_ap='".$archiveInfo['prefix']."' AND c.ref_id='".$itemInfo['id']."' AND c.variant_id='0'";

 $_QRY.= " WHERE i.id='".$itemInfo['id']."'";


 $db->RunQuery($_QRY);
 $db->Read();
 if($brand || $all)
 {
  $itemInfo['brand'] = $db->record['brand'];
  $itemInfo['brand_id'] = $db->record['brand_id'];
 }
 if($model || $all)					$itemInfo['model'] = $db->record['model'];
 if($barcode || $all)				$itemInfo['barcode'] = $db->record['barcode'];
 if($manufacturerCode || $all)		$itemInfo['manufacturer_code'] = $db->record['manufacturer_code'];
 if($itemLocation || $all)			$itemInfo['item_location'] = $db->record['item_location'];
 if($qtySold || $all)				$itemInfo['sold'] = $db->record['qty_sold'];
 if($units || $all)					$itemInfo['units'] = $db->record['units'];
 if($weight || $all)
 {
  $itemInfo['weight'] = $db->record['weight'];
  $itemInfo['weightunits'] = $db->record['weightunits'];
 }
 if($gebinde || $all)
 {
  $itemInfo['gebinde'] = $db->record['gebinde'];
  $itemInfo['gebinde_code'] = $db->record['gebinde_code'];
 }
 if($division || $all)				$itemInfo['division'] = $db->record['division'];
 if($hideInStore || $all)			$itemInfo['hide_in_store'] = $db->record['hide_in_store'];

 $itemInfo['sku'] = $db->record['sku'];
 $itemInfo['asin'] = $db->record['asin'];
 $itemInfo['ean'] = $db->record['ean'];
 $itemInfo['gcid'] = $db->record['gcid'];
 $itemInfo['gtin'] = $db->record['gtin'];
 $itemInfo['upc'] = $db->record['upc'];

 if($itemInfo['asin']) { $itemInfo['spid_type']='ASIN'; $itemInfo['spid_code']=$itemInfo['asin']; }
 if($itemInfo['ean']) { $itemInfo['spid_type']='EAN'; $itemInfo['spid_code']=$itemInfo['ean']; }
 if($itemInfo['gcid']) { $itemInfo['spid_type']='GCID'; $itemInfo['spid_code']=$itemInfo['gcid']; }
 if($itemInfo['gtin']) { $itemInfo['spid_type']='GTIN'; $itemInfo['spid_code']=$itemInfo['gtin']; }
 if($itemInfo['upc']) { $itemInfo['spid_type']='UPC'; $itemInfo['spid_code']=$itemInfo['upc']; }

 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();

 $_QRY = "SELECT i.brand,i.brand_id,i.model,i.barcode,i.manufacturer_code,i.item_location,i.qty_sold,i.units,i.weight,i.weightunits";
 $_QRY.= ",i.gebinde,i.gebinde_code,i.division,i.hide_in_store";
 $_QRY.= ",s.sku, c.asin, c.ean, c.gcid, c.gtin, c.upc";
 $_QRY.= " FROM dynarc_".$archiveInfo['prefix']."_items AS i";
 $_QRY.= " LEFT JOIN `product_sku` AS s ON s.ref_ap='".$archiveInfo['prefix']."' AND s.ref_id='".$srcInfo['id']."' AND s.variant_id='0'";
 $_QRY.= " LEFT JOIN `product_spid` AS c ON c.ref_ap='".$archiveInfo['prefix']."' AND c.ref_id='".$srcInfo['id']."' AND c.variant_id='0'";

 $_QRY.= " WHERE i.id='".$srcInfo['id']."'";

 $db->RunQuery($_QRY);
 $db->Read();
 $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET brand='".$db2->Purify($db->record['brand'])."',brand_id='"
	.$db->record['brand_id']."',model='".$db2->Purify($db->record['model'])."',barcode='".$db->record['barcode']."',manufacturer_code='"
	.$db->record['manufacturer_code']."',item_location='".$db2->Purify($db->record['item_location'])."',qty_sold='"
	.$db->record['qty_sold']."' units='".$db->record['units']."',weight='".$db->record['weight']."',weightunits='"
	.$db->record['weightunits']."',gebinde='".$db->record['gebinde']."',gebinde_code='".$db->record['gebinde_code']."',division='"
	.$db->record['division']."',hide_in_store='".$db->record['hide_in_store']."' WHERE id='".$cloneInfo['id']."'");

 if($db->record['sku'])
  $db2->RunQuery("INSERT INTO product_sku (sku,ref_at,ref_ap,ref_id) VALUES('".$db->record['sku']."','".$archiveInfo['type']."','"
	.$archiveInfo['prefix']."','".$cloneInfo['id']."')");

 if($db->record['asin'] || $db->record['ean'] || $db->record['gcid'] || $db->record['gtin'] || $db->record['upc'])
  $db2->RunQuery("INSERT INTO product_spid (ref_at,ref_ap,ref_id,asin,ean,gcid,gtin,upc) VALUES('".$archiveInfo['type']."','"
	.$archiveInfo['prefix']."','".$cloneInfo['id']."','".$db->record['asin']."','".$db->record['ean']."','".$db->record['gcid']."','"
	.$db->record['gtin']."','".$db->record['upc']."')");

 $db2->Close();
 $db->Close();

 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM product_sku WHERE ref_ap='".$archiveInfo['prefix']."' AND ref_id='".$itemInfo['id']."'");
 $db->RunQuery("DELETE FROM product_spid WHERE ref_ap='".$archiveInfo['prefix']."' AND ref_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE product_sku SET trash='1' WHERE ref_ap='".$archiveInfo['prefix']."' AND ref_id='".$itemInfo['id']."'");
 $db->RunQuery("UPDATE product_spid SET trash='1' WHERE ref_ap='".$archiveInfo['prefix']."' AND ref_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE product_sku SET trash='0' WHERE ref_ap='".$archiveInfo['prefix']."' AND ref_id='".$itemInfo['id']."'");
 $db->RunQuery("UPDATE product_spid SET trash='0' WHERE ref_ap='".$archiveInfo['prefix']."' AND ref_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_onarchiveempty($args, $sessid, $shellid, $archiveInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_export($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return;

 $db = new AlpaDatabase();

 $_QRY = "SELECT i.brand,i.brand_id,i.model,i.barcode,i.manufacturer_code,i.item_location,i.qty_sold,i.units,i.weight,i.weightunits";
 $_QRY.= ",i.gebinde,i.gebinde_code,i.division,i.hide_in_store";
 $_QRY.= ",s.sku, c.asin, c.ean, c.gcid, c.gtin, c.upc";
 $_QRY.= " FROM dynarc_".$archiveInfo['prefix']."_items AS i";
 $_QRY.= " LEFT JOIN `product_sku` AS s ON s.ref_ap='".$archiveInfo['prefix']."' AND s.ref_id='".$itemInfo['id']."' AND s.variant_id='0'";
 $_QRY.= " LEFT JOIN `product_spid` AS c ON c.ref_ap='".$archiveInfo['prefix']."' AND c.ref_id='".$itemInfo['id']."' AND c.variant_id='0'";

 $_QRY.= " WHERE i.id='".$itemInfo['id']."'";

 $db->RunQuery($_QRY);
 $db->Read();
 $xml = "<gmart brand='".$db->record['brand']."' brand_id='".$db->record['brand_id']."' model='".$db->record['model']."' barcode='"
	.$db->record['barcode']."' manufacturer_code='".$db->record['manufacturer_code']."' item_location='"
	.$db->record['item_location']."' qty_sold='".$db->record['qty_sold']."' units='".$db->record['units']."' weight='"
	.$db->record['weight']."' weightunits='".$db->record['weightunits']."' gebinde='".$db->record['gebinde']."' gebinde_code='"
	.$db->record['gebinde_code']."' division='".$db->record['division']."' hideinstore='".$db->record['hide_in_store']."' sku='"
	.$db->record['sku']."' asin='".$db->record['asin']."' ean='".$db->record['ean']."' gcid='".$db->record['gcid']."' gtin='"
	.$db->record['gtin']."' upc='".$db->record['upc']."'/>";
 $db->Close();
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_import($sessid, $shellid, $archiveInfo, $itemInfo, $node, $isCategory=false)
{
 if($isCategory)
  return;

 if(!$node)
  return;

 $q = "";
 $db = new AlpaDatabase();
 if($brand = $node->getString('brand'))					$q.= ",brand='".$db->Purify($brand)."'";
 if($brandId = $node->getString('brand_id'))			$q.= ",brand_id='".$brandId."'";
 if($model = $node->getString('model'))					$q.= ",model='".$db->Purify($model)."'";
 if($barcode = $node->getString('barcode'))				$q.= ",barcode='".$barcode."'";
 if($mancode = $node->getString('manufacturer_code'))	$q.= ",manufacturer_code='".$mancode."'";
 if($location = $node->getString('item_location'))		$q.= ",item_location='".$location."'";
 if($units = $node->getString('units'))					$q.= ",units='".$units."'";
 if($weight = $node->getString('weight'))				$q.= ",weight='".$weight."'";
 if($weightUnits = $node->getString('weightunits'))		$q.= ",weightunits='".$weightUnits."'";
 if($gebinde = $node->getString('gebinde'))				$q.= ",gebinde='".$gebinde."'";
 if($gebindeCode = $node->getString('gebinde_code'))	$q.= ",gebinde_code='".$gebindeCode."'";
 if($division = $node->getString('division'))			$q.= ",division='".$division."'";
 if($hideInStore = $node->getString('hideinstore'))		$q.= ",hide_in_store='".$hideInStore."'";
 
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");

 if($node->getString('sku'))
  $db->RunQuery("INSERT INTO product_sku (sku,ref_at,ref_ap,ref_id) VALUES('".$node->getString('sku')."','"
	.$archiveInfo['type']."','".$archiveInfo['prefix']."','".$itemInfo['id']."')");

 if($node->getString('asin') || $node->getString('ean') || $node->getString('gcid') || $node->getString('gtin') || $node->getString('upc'))
  $db->RunQuery("INSERT INTO product_spid (ref_at,ref_ap,ref_id,asin,ean,gcid,gtin,upc) VALUES('".$archiveInfo['type']."','"
	.$archiveInfo['prefix']."','".$itemInfo['id']."','".$node->getString('asin')."','".$node->getString('ean')."','"
	.$node->getString('gcid')."','".$node->getString('gtin')."','".$node->getString('upc')."')");

 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

