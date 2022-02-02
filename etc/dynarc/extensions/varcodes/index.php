<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
HackTVT Project
copyright(C) 2016 Alpatech mediaware - www.alpatech.it
license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
Gnujiko 10.1 is free software released under GNU/GPL license
developed by D. L. Alessandro (alessandro@alpatech.it)

#DATE: 24-11-2016
#PACKAGE: dynarc-varcodes-extension
#DESCRIPTION: 
#VERSION: 2.3beta
#CHANGELOG: 24-11-2016 : Integrazione con SKU, asin, ean, gcid, gtin ed upc.
			24-10-2016 : MySQLi integration.
			23-03-2016 : Bug fix su funzione find.
#TODO: 
*/

global $_BASE_PATH;

function dynarcextension_varcodes_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_varcodes` (
	`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`item_id` INT(11) NOT NULL ,
	`variant_name` VARCHAR(64) NOT NULL,
	`variant_type` VARCHAR(32) NOT NULL,
	`code` VARCHAR(32) NOT NULL,
	`barcode` VARCHAR(32) NOT NULL,
	`mrate` FLOAT NOT NULL,
	`final_price` DECIMAL(10,4) NOT NULL,
	INDEX (`item_id`,`code`,`barcode`))");
 $db->Close();

 return array("message"=>"Variant Codes extension has been installed into archive ".$archiveInfo['name']);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DROP TABLE `dynarc_".$archiveInfo['prefix']."_varcodes`");
 $db->Close();

 return array("message"=>"Variant Codes extension has been removed from archive ".$archiveInfo['name']);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_catset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {

  }

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_catunset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {

  }

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_catget($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {

  }

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return dynarcextension_varcodes_catset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 $isnew = false;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'variant' : case 'name' : {$variantName=$args[$c+1]; $c++;} break;
   case 'type' : {$variantType=$args[$c+1]; $c++;} break;
   case 'code' : {$code=$args[$c+1]; $c++;} break;
   case 'barcode' : {$barcode=$args[$c+1]; $c++;} break;
   case 'mrate' : {$mrate=$args[$c+1]; $c++;} break;
   case 'price' : case 'finalprice' : {$finalPrice=$args[$c+1]; $c++;} break;

   case 'sku' : 	{$productSKU=$args[$c+1]; $c++;} break;				// SKU code
   case 'skuref' : 	{$productSKUreferrer = $args[$c+1]; $c++;} break;	// SKU referrer (es: Amazon, Goupon, ecc...)
   case 'asin' : 	{$codeASIN=$args[$c+1]; $c++;} break;
   case 'ean' : 	{$codeEAN=$args[$c+1]; $c++;} break;
   case 'gcid' : 	{$codeGCID=$args[$c+1]; $c++;} break;
   case 'gtin' : 	{$codeGTIN=$args[$c+1]; $c++;} break;
   case 'upc' : 	{$codeUPC=$args[$c+1]; $c++;} break;

  }

 $coltint = ""; $sizmis = "";
 switch($variantType)
 {
  case 'color' : case 'tint' : $coltint = $variantName; break;
  case 'size' : case 'dim' : case 'other' : $sizmis = $variantName; break;
 }

 $db = new AlpaDatabase();
 if($id)
 {
  // UPDATE VARIANT
  $q = "";
  if($variantName)				$q.= ",variant_name='".$db->Purify($variantName)."'";
  if($variantType)				$q.= ",variant_type='".$variantType."'";
  if(isset($code))				$q.= ",code='".$code."'";
  if(isset($barcode))			$q.= ",barcode='".$barcode."'";
  if(isset($mrate))				$q.= ",mrate='".$mrate."'";
  if(isset($finalPrice))		$q.= ",final_price='".$finalPrice."'";
  
  if($q) $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_varcodes SET ".ltrim($q,",")." WHERE id='".$id."'");
 }
 else
 {
  // NEW VARIANT
  $fields = "item_id,variant_name,variant_type,code,barcode,mrate,final_price";
  $query = "INSERT INTO dynarc_".$archiveInfo['prefix']."_varcodes(".$fields.") VALUES('".$itemInfo['id']."','"
	.$db->Purify($variantName)."','".$variantType."','".$code."','".$barcode."','".$mrate."','".$finalPrice."')";
  $db->RunQuery($query);
  if($db->Error) return array('message'=>'MySQL ERROR:'.$db->Error, 'error'=>'MYSQL_ERROR');
  $id = $db->GetInsertId();
  $isnew = true;
  $itemInfo['last_varcode'] = array('id'=>$id, 'name'=>$variantName, 'type'=>$variantType, 'code'=>$code, 
	'barcode'=>$barcode, 'mrate'=>$mrate, 'finalprice'=>$finalPrice);

  /*if($productSKU)
   $db->RunQuery("INSERT INTO product_sku (ref_at,ref_ap,ref_id,variant_id,coltint,sizmis,sku,referrer) VALUES('"
	.$archiveInfo['type']."','".$archiveInfo['prefix']."','".$itemInfo['id']."','".$id."','".$coltint."','".$sizmis."','"
	.$productSKU."','".$productSKUreferrer."'");*/
 }

 // UPDATE SKU
 if(isset($productSKU))
 {
  if($isnew && $productSKU)
   $db->RunQuery("INSERT INTO product_sku (sku,ref_at,ref_ap,ref_id,variant_id,coltint,sizmis,referrer) VALUES('".$productSKU."','"
	.$archiveInfo['type']."','".$archiveInfo['prefix']."','".$itemInfo['id']."','".$id."','".$coltint."','".$sizmis."','".$productSKUreferrer."')");

  else if(!$productSKU && !$isnew) // Remove sku of this variant from sku list
   $db->RunQuery("DELETE FROM product_sku WHERE ref_ap='".$archiveInfo['prefix']."' AND ref_id='".$itemInfo['id']."' AND variant_id='".$id."' AND referrer='".$productSKUreferrer."'");
  else if(!$isnew)
  {
   $db->RunQuery("SELECT id,sku FROM product_sku WHERE ref_ap='".$archiveInfo['type']."' AND ref_id='".$itemInfo['id']."' AND variant_id='".$id."' AND referrer='".$productSKUreferrer."'");
   if($db->Read())
   {
	if($db->record['sku'] != $productSKU)
	  $db->RunQuery("UPDATE product_sku SET sku='".$productSKU."' WHERE id='".$db->record['id']."'");
   }
   else
	$db->RunQuery("INSERT INTO product_sku (sku,ref_at,ref_ap,ref_id,variant_id,coltint,sizmis,referrer) VALUES('".$productSKU."','"
		.$archiveInfo['type']."','".$archiveInfo['prefix']."','".$itemInfo['id']."','".$id."','".$coltint."','".$sizmis."','".$productSKUreferrer."')");
  }
 }


 // UPDATE SPID
 if(isset($codeASIN) || isset($codeEAN) || isset($codeGCID) || isset($codeGTIN) || isset($codeUPC))
 {
  if(!$isnew)
  {
   $db->RunQuery("SELECT id FROM product_spid WHERE ref_ap='".$archiveInfo['prefix']."' AND ref_id='".$itemInfo['id']."' AND variant_id='".$id."'");
   if($db->Read())
   {
    $q = "";
    if(isset($codeASIN))		$q.= ",asin='".$codeASIN."'";
    if(isset($codeEAN))			$q.= ",ean='".$codeEAN."'";
    if(isset($codeGCID))		$q.= ",gcid='".$codeGCID."'";
    if(isset($codeGTIN))		$q.= ",gtin='".$codeGTIN."'";
    if(isset($codeUPC))			$q.= ",upc='".$codeUPC."'";
    $db->RunQuery("UPDATE product_spid SET ".ltrim($q, ",")." WHERE id='".$db->record['id']."'");
   }
   else
    $db->RunQuery("INSERT INTO product_spid (ref_at,ref_ap,ref_id,asin,ean,gcid,gtin,upc,variant_id,coltint,sizmis) VALUES('".$archiveInfo['type']."','"
		.$archiveInfo['prefix']."','".$itemInfo['id']."','".$codeASIN."','".$codeEAN."','".$codeGCID."','".$codeGTIN."','".$codeUPC."','"
		.$id."','".$coltint."','".$sizmis."')");
  }
  else
    $db->RunQuery("INSERT INTO product_spid (ref_at,ref_ap,ref_id,asin,ean,gcid,gtin,upc,variant_id,coltint,sizmis) VALUES('".$archiveInfo['type']."','"
		.$archiveInfo['prefix']."','".$itemInfo['id']."','".$codeASIN."','".$codeEAN."','".$codeGCID."','".$codeGTIN."','".$codeUPC."','"
		.$id."','".$coltint."','".$sizmis."')");
 }

 $db->Close();


 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return dynarcextension_varcodes_catunset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
  }

 if($id)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_varcodes WHERE id='".$id."' AND item_id='".$itemInfo['id']."'");
  $db->RunQuery("DELETE FROM product_sku WHERE ref_ap='".$archiveInfo['prefix']."' AND ref_id='".$itemInfo['id']."' AND variant_id='".$id."'");
  $db->RunQuery("DELETE FROM product_spid WHERE ref_ap='".$archiveInfo['prefix']."' AND ref_id='".$itemInfo['id']."' AND variant_id='".$id."'");
  $db->Close();
 }

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return dynarcextension_varcodes_catget($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {

  }

 $itemInfo['varcodes'] = array();
 $db = new AlpaDatabase();

 $qry = "SELECT v.id, v.variant_name, v.variant_type, v.code, v.barcode, v.mrate, v.final_price";
 $qry.= ", s.sku";
 $qry.= ", c.asin, c.ean, c.gcid, c.gtin, c.upc";
 $qry.= " FROM dynarc_".$archiveInfo['prefix']."_varcodes AS v";
 $qry.= " LEFT JOIN `product_sku` AS s ON s.ref_ap='".$archiveInfo['prefix']."' AND s.ref_id='".$itemInfo['id']."' AND s.variant_id=v.id AND s.referrer=''";
 $qry.= " LEFT JOIN `product_spid` AS c ON c.ref_ap='".$archiveInfo['prefix']."' AND c.ref_id='".$itemInfo['id']."' AND c.variant_id=v.id";
 $qry.= " WHERE v.item_id='".$itemInfo['id']."' ORDER BY v.id ASC";

 $db->RunQuery($qry);
 while($db->Read())
 {
  $a = array('id'=>$db->record['id'], 'name'=>$db->record['variant_name'], 'type'=>$db->record['variant_type'], 
	'code'=>$db->record['code'], 'barcode'=>$db->record['barcode'],	'mrate'=>$db->record['mrate'], 'finalprice'=>$db->record['final_price'],
	'sku'=>$db->record['sku'], 'asin'=>$db->record['asin'], 'ean'=>$db->record['ean'], 'gcid'=>$db->record['gcid'], 
	'gtin'=>$db->record['gtin'], 'upc'=>$db->record['upc']);

  $itemInfo['varcodes'][] = $a;
 }

 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_find($args, $sessid, $shellid, $archiveInfo)
{
 $out = "";
 $outArr = array();

 $_TYPES = array();

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-itemid' : {$itemId=$args[$c+1]; $c++;} break;
   case '-type' : {$_TYPES[]=$args[$c+1]; $c++;} break;
   case '-types' : {$_TYPES = explode(",",$args[$c+1]); $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-code' : {$code=$args[$c+1]; $c++;} break;
   case '-barcode' : {$barcode=$args[$c+1]; $c++;} break;
   case '-plid' : case '-pricelistid' : {$_PLID=$args[$c+1]; $c++;} break;

   case '--get-availability' : $getAvailability=true; break;
   case '-coltint' : {$coltint=$args[$c+1]; $c++;} break;
   case '-sizmis' : {$sizmis=$args[$c+1]; $c++;} break;
   case '-storeid' : case '-store' : {$storeId=$args[$c+1]; $c++;} break; 	// se non viene specificato vengono ritornate le giac. di tutti i magazz.

   case '--verbose' : $verbose=true; break;
  }

 $query = "SELECT * FROM dynarc_".$archiveInfo['prefix']."_varcodes WHERE ";
 $where = "";
 $db = new AlpaDatabase();
 if($itemId)		$where.= " AND item_id='".$itemId."'";
 if($name)			$where.= " AND variant_name='".$db->Purify($name)."'";
 if($code)			$where.= " AND code='".$code."'";
 if($barcode)		$where.= " AND barcode='".$barcode."'";
 if(count($_TYPES))
 {
  $tmp.= "";
  for($c=0; $c < count($_TYPES); $c++)
   $tmp.= " OR variant_type='".$_TYPES[$c]."'";
  $where.= " AND (".ltrim($tmp, " OR ").")";
 }
 
 if(!$where) return array('message'=>"Missing the search parameters.", 'error'=>"SEARCH_PARAMETERS_MISSING");
 
 $db->RunQuery($query.ltrim($where, " AND ")." LIMIT 1");
 if($db->Error) return array('message'=>"MySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
 if($db->Read())
  $outArr = array('id'=>$db->record['id'], 'item_id'=>$db->record['item_id'], 'name'=>$db->record['variant_name'], 
	'type'=>$db->record['variant_type'], 'code'=>$db->record['code'], 'barcode'=>$db->record['barcode'],
	'mrate'=>$db->record['mrate'], 'finalprice'=>$db->record['final_price']);
 else
  $out.= "No result found.\n";
 $db->Close();

 if($outArr['mrate'])
 {
  $db = new AlpaDatabase();
  if(!$_PLID)
  {
   $db->RunQuery("SELECT id FROM pricelists WHERE isdefault=1 LIMIT 1");
   if($db->Read())
	$_PLID = $db->record['id'];
   else
   {
	$db->RunQuery("SELECT id FROM pricelists WHERE 1 ORDER BY id ASC LIMIT 1");
	if($db->Read())
	 $_PLID = $db->record['id'];
   }
  }
  $db->RunQuery("SELECT baseprice".($_PLID ? ",pricelist_".$_PLID."_baseprice,pricelist_".$_PLID."_mrate,pricelist_".$_PLID."_discount" : "")
	." FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$outArr['item_id']."'");
  $db->Read();
  if($_PLID)
  {
   $basePrice = $db->record["pricelist_".$_PLID."_baseprice"] ? $db->record["pricelist_".$_PLID."_baseprice"] : $db->record['baseprice'];
   $markupRate = $db->record["pricelist_".$_PLID."_mrate"] ? $db->record["pricelist_".$_PLID."_mrate"] : 0;
   $discount = $db->record["pricelist_".$_PLID."_discount"] ? $db->record["pricelist_".$_PLID."_discount"] : 0;
   $price = $basePrice ? $basePrice + (($basePrice/100)*$markupRate) : 0;
   $price = $price ? $price - (($price/100)*$discount) : 0;
  }
  else
   $price = $db->record['baseprice'];

  $outArr['finalprice'] = $price + (($price/100)*$outArr['mrate']);
 }

 if($getAvailability)
 {
  $avail = 0;
  $db = new AlpaDatabase();
  $query = "SELECT ".($storeId ? "store_".$storeId."_qty" : "*")." FROM dynarc_".$archiveInfo['prefix']."_variantstock WHERE item_id=".$itemId;
  
  if(isset($coltint) && isset($sizmis))
   $query.= " AND coltint='".$db->Purify($coltint)."' AND sizmis='".$db->Purify($sizmis)."'";
  else if(in_array("color",$_TYPES) || in_array("tint",$_TYPES))
   $query.= " AND coltint='".$outArr['name']."'";
  else if(in_array("size",$_TYPES) || in_array("dim",$_TYPES) || in_array("other",$_TYPES))
   $query.= " AND sizmis='".$outArr['name']."'";

  $db->RunQuery($query);
  if($db->Read())
  {
   $rec = $db->record;
   reset($rec);
   while(list($k,$v)=each($rec))
   {
	if(substr($k,0,6) == "store_")
	{
	 $outArr[$k] = $v;
	 $avail+= $v;
	}
   }
  }
  $db->Close();
  $outArr['avail'] = $avail;
 }

 if($verbose && $outArr['id'])
 {
  $out.= "Details about the variant searched:\n";
  $out.= "Rec. ID: ".$outArr['id']."\n";
  $out.= "Item ID: ".$outArr['item_id']."\n";
  $out.= "Name: ".$outArr['name']."\n";
  $out.= "Type: ".$outArr['type']."\n";
  $out.= "Code: ".$outArr['code']."\n";
  $out.= "Barcode: ".$outArr['barcode']."\n";
  $out.= "Markup rate: ".$outArr['mrate']."%\n";
  $out.= "Final price: ".number_format($outArr['finalprice'],2,',','.')."\n";
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_export($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return ;

 $xml = "<varcodes />";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_import($sessid, $shellid, $archiveInfo, $itemInfo, $node, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return ;

 if(!$node)
  return ;

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_varcodes WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_varcodes_onarchiveempty($args, $sessid, $shellid, $archiveInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("TRUNCATE TABLE `dynarc_".$archiveInfo['prefix']."_varcodes`");
 $db->RunQuery("DELETE FROM product_sku WHERE ref_ap='".$archiveInfo['prefix']."'");
 $db->RunQuery("DELETE FROM product_spid WHERE ref_ap='".$archiveInfo['prefix']."'");
 $db->Close(); 

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
