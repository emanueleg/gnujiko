<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-07-2013
 #PACKAGE: gmart
 #DESCRIPTION: GMart basic info extension for Dynarc.
 #VERSION: 2.4beta
 #CHANGELOG: 11-07-2013 : Aggiunto ITEM_LOCATION per la collocazione degli articoli negli scaffali.
			 07-05-2013 : Aggiunto WEIGHT e WEIGHT UNITS
			 11-02-2013 : Bug fix.
			 03-12-2012 : Completamento delle funzioni principali.
 #TODO:Rifare funzione import & export e completare funzioni syncimport & syncexport.
 
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
ADD INDEX ( `barcode` , `brand_id` )");

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
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_categories` DROP `subcat_count`,  DROP `items_count`,  DROP `totitems_count`");
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
  }

 $db = new AlpaDatabase();
 $q="";
 if(isset($brandName))
  $q.=",brand='".$db->Purify($brandName)."'";
 if(isset($brandId))
  $q.=",brand_id='".$brandId."'";
 if(isset($model))
  $q.= ",model='".$db->Purify($model)."'";
 if(isset($barcode))
  $q.= ",barcode='".$barcode."'";
 if(isset($manufacturerCode))
  $q.= ",manufacturer_code='".$manufacturerCode."'";
 if(isset($itemLocation))
  $q.= ",item_location='".$db->Purify($itemLocation)."'";
 if(isset($qtySold))
  $q.= ",qty_sold='".$qtySold."'";
 if(isset($units))
  $q.= ",units='".$units."'";
 if(isset($weight))
  $q.= ",weight='".$weight."'";
 if(isset($weightUnits))
  $q.= ",weightunits='".$weightUnits."'";

 if($q)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");
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
  }

 if(!count($args))
  $all=true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT brand,brand_id,model,barcode,manufacturer_code,item_location,qty_sold,units,weight,weightunits FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 if($brand || $all)
  $itemInfo['brand'] = $db->record['brand'];
 if($model || $all)
  $itemInfo['model'] = $db->record['model'];
 if($barcode || $all)
  $itemInfo['barcode'] = $db->record['barcode'];
 if($manufacturerCode || $all)
  $itemInfo['manufacturer_code'] = $db->record['manufacturer_code'];
 if($itemLocation || $all)
  $itemInfo['item_location'] = $db->record['item_location'];
 if($qtySold || $all)
  $itemInfo['sold'] = $db->record['qty_sold'];
 if($units || $all)
  $itemInfo['units'] = $db->record['units'];
 if($weight || $all)
 {
  $itemInfo['weight'] = $db->record['weight'];
  $itemInfo['weightunits'] = $db->record['weightunits'];
 }
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT brand,brand_id,model,barcode,manufacturer_code,item_location,units FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$srcInfo['id']."'");
 $db->Read();
 $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET brand='".$db2->Purify($db->record['brand'])."',brand_id='"
	.$db->record['brand_id']."',model='".$db2->Purify($db->record['model'])."',barcode='".$db->record['barcode']."',manufacturer_code='"
	.$db->record['manufacturer_code']."',item_location='".$db2->Purify($db->record['item_location'])."',units='".$db->record['units']."',weight='"
	.$db->record['weight']."',weightunits='".$db->record['weightunits']."' WHERE id='".$cloneInfo['id']."'");
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

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_export($sessid, $shellid, $archiveInfo, $itemInfo)
{
 $xml = "";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gmart_import($sessid, $shellid, $archiveInfo, $itemInfo, $node)
{
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

