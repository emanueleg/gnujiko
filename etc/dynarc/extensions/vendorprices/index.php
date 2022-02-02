<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-10-2016
 #PACKAGE: dynarc-vendorprices-extension
 #DESCRIPTION: 
 #VERSION: 2.5beta
 #CHANGELOG: 24-10-2016 : MySQLi integration.
			 24-07-2014 : Sostituito alcuni float con decimal 10,4
			 10-06-2014 : Aggiunta funzione onarchiveempty
			 23-05-2014 : Bug fix vari
			 18-02-2014 : sviluppate funzioni import export.
 #TODO:
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_vendorprices` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`item_id` INT( 11 ) NOT NULL ,
`code` VARCHAR( 64 ) NOT NULL ,
`vendor_id` INT( 11 ) NOT NULL ,
`vendor_name` VARCHAR( 64 ) NOT NULL ,
`ship_costs` DECIMAL(10,4) NOT NULL ,
`price` DECIMAL(10,4) NOT NULL ,
`vatrate` FLOAT NOT NULL ,
INDEX ( `item_id` , `code` )
)");
 $db->Close();

 return array("message"=>"GMart:VendorPrices extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_vendorprices`");
 $db->Close();

 return array("message"=>"GMart:VendorPrices extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 /* REMOVE ALL ITEM EVENTS */
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_vendorprices WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_set($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;
 $qty = 1;
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'code' : {$code=$args[$c+1]; $c++;} break;
   case 'vendor' : case 'vendorname' : {$vendorName=$args[$c+1]; $c++;} break;
   case 'vendorid' : {$vendorId=$args[$c+1]; $c++;} break;
   case 'shipcosts' : {$shipCosts=$args[$c+1]; $c++;} break;
   case 'price' : {$price=$args[$c+1]; $c++;} break;
   case 'vat' : case 'vatrate' : {$vatRate=$args[$c+1]; $c++;} break;
  }

 $sessInfo = sessionInfo($sessid);

 if($vendorName && !isset($vendorId))
 {
  // check for vendor
  $ret = GShell("dynarc item-info -ap rubrica -name `".$vendorName."`",$sessid,$shellid);
  if(!$ret['error'])
   $vendorId = $ret['outarr']['id'];
  else
  {
   // registra il fornitore in rubrica
   $ret = GShell("dynarc new-item -ap rubrica -ct vendors -name `".$vendorName."`",$sessid,$shellid);
   if(!$ret['error'])
	$vendorId = $ret['outarr']['id'];
  }
 }

 if(!$id && $vendorId)
 {
  // get if already exists
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_vendorprices WHERE item_id='".$itemInfo['id']."' AND vendor_id='".$vendorId."'");
  if($db->Read())
   $id = $db->record['id'];
  $db->Close();
 }

 if($id)
 {
  $db = new AlpaDatabase();
  $q = "";
  
  if(isset($code))
   $q.= ",code='".$code."'";
  if(isset($vendorName))
   $q.= ",vendor_name='".$db->Purify($vendorName)."'";
  if(isset($vendorId))
   $q.= ",vendor_id='".$vendorId."'";
  if(isset($shipCosts))
   $q.= ",ship_costs='".$shipCosts."'";
  if(isset($price))
   $q.= ",price='".$price."'";
  if(isset($vatRate))
   $q.= ",vatrate='".$vatRate."'";

  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_vendorprices SET ".ltrim($q,',')." WHERE id='$id'");
  $db->Close();
 }
 else
 {
  $db = new AlpaDatabase();
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_vendorprices(item_id,code,vendor_id,vendor_name,ship_costs,price,vatrate) VALUES('"
	.$itemInfo['id']."','".$code."','".$vendorId."','".$db->Purify($vendorName)."','".$shipCosts."','".$price."','".$vatRate."')");
  $id = $db->GetInsertId();
  $db->Close();
 }

 $itemInfo['last_element'] = array('id'=>$id, 'item_id'=>$itemInfo['id'], 'code'=>$code, 'vendor_id'=>$vendorId, 'vendor_name'=>$vendorName, 
	'shipcosts'=>$shipCosts, 'price'=>$price,'vat_rate'=>$vatRate);

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'all' : $all=true; break;
  }
 
 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_vendorprices WHERE id='$id'");
 else if($all)
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_vendorprices WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_get($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'code' : $code=true; break;
   case 'vendor' : $vendor=true; break;
   case 'shipcosts' : $shipCosts=true; break;
   case 'price' : $price=true; break;
   case 'vat' : case 'vatrate' : $vatRate=true; break;
  }

 if(!count($args))
  $all = true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_vendorprices WHERE item_id='".$itemInfo['id']."' ORDER BY id ASC");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id']);

  if($code || $all) $a['code'] = $db->record['code'];
  if($vendor || $all) {
	 $a['vendor_name'] = $db->record['vendor_name'];
	 $a['vendor_id'] = $db->record['vendor_id'];
	}
  if($shipCosts || $all) $a['shipcosts'] = $db->record['ship_costs'];
  if($price || $all) $a['price'] = $db->record['price'];
  if($vatRate || $all) $a['vatrate'] = $db->record['vatrate'];

  $itemInfo['vendorprices'][] = $a;
 }
 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_onarchiveempty($args, $sessid, $shellid, $archiveInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("TRUNCATE TABLE `dynarc_".$archiveInfo['prefix']."_vendorprices`");
 $db->Close();
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_export($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return;

 $xml = "<vendorprices>";
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_vendorprices WHERE item_id='".$itemInfo['id']."' ORDER BY id ASC");
 while($db->Read())
 {
  $xml.= "<vendorprice code='".$db->record['code']."' vendor_name='".$db->record['vendor_name']."' vendor_id='"
	.$db->record['vendor_id']."' ship_costs='".$db->record['ship_costs']."' price='".$db->record['price']."' vatrate='".$db->record['vatrate']."'/>";
 }
 $xml.= "</vendorprices>";
 $db->Close();


 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_import($sessid, $shellid, $archiveInfo, $itemInfo, $node, $isCategory=false)
{
 if($isCategory)
  return;

 $list = $node->GetElementsByTagName("vendorprice");
 for($c=0; $c < count($list); $c++)
 {
  $node = $list[$c];
  $code = $node->getString('code');
  $vendorId = $node->getString('vendor_id');
  $vendorName = $node->getString('vendor_name');
  $shipCosts = $node->getString('ship_costs');
  $price = $node->getString('price');
  $vatRate = $node->getString('vatrate');

  $db = new AlpaDatabase();
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_vendorprices(item_id,code,vendor_id,vendor_name,ship_costs,price,vatrate) VALUES('"
	.$itemInfo['id']."','".$code."','".$vendorId."','".$db->Purify($vendorName)."','".$shipCosts."','".$price."','".$vatRate."')");
  $db->Close();
 }

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_vendorprices_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

