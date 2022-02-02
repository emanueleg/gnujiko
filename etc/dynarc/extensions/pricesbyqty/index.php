<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
HackTVT Project
copyright(C) 2016 Alpatech mediaware - www.alpatech.it
license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
Gnujiko 10.1 is free software released under GNU/GPL license
developed by D. L. Alessandro (alessandro@alpatech.it)

#DATE: 24-10-2016
#PACKAGE: dynarc-pricesbyqty-extension
#DESCRIPTION: 
#VERSION: 2.1beta
#CHANGELOG: 24-10-2016 : MySQLi integration.
#TODO: 
*/

global $_BASE_PATH;

function dynarcextension_pricesbyqty_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_pricesbyqty` (
	`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`item_id` INT(11) NOT NULL ,
	`colors` TEXT NOT NULL ,
	`sizes` TEXT NOT NULL ,
	`qty_from` FLOAT NOT NULL ,
	`qty_to` FLOAT NOT NULL ,
	`disc_perc` FLOAT NOT NULL ,
	`final_price` DECIMAL(10,4) NOT NULL ,
	INDEX (`item_id`))");
 $db->Close();

 return array("message"=>"PricesByQty extension has been installed into archive ".$archiveInfo['name']);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DROP TABLE `dynarc_".$archiveInfo['prefix']."_pricesbyqty`");
 $db->Close();

 return array("message"=>"PricesByQty extension has been removed from archive ".$archiveInfo['name']);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_catset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {

  }

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_catunset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {

  }

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_catget($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {

  }

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return dynarcextension_pricesbyqty_catset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'colors' : {$colors=$args[$c+1]; $c++;} break;	// separated by square brackets and comma. eg: [red],[orange],[yellow],... //
   case 'sizes' : {$sizes=$args[$c+1]; $c++;} break;	// separated by square brackets and comma. eg: [320 x 240],[640 x 480],[800 x 600], ... //
   case 'qtyfrom' : {$qtyFrom=$args[$c+1]; $c++;} break;
   case 'qtyto' : {$qtyTo=$args[$c+1]; $c++;} break;
   case 'discperc' : case 'discount' : {$discPerc=$args[$c+1]; $c++;} break;
   case 'finalprice' : {$finalPrice=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 if($id)
 {
  $q = "";
  if(isset($colors))			$q.= ",colors='".$db->Purify($colors)."'";
  if(isset($sizes))				$q.= ",sizes='".$db->Purify($sizes)."'";
  if(isset($qtyFrom))			$q.= ",qty_from='".$qtyFrom."'";
  if(isset($qtyTo))				$q.= ",qty_to='".$qtyTo."'";
  if(isset($discPerc))			$q.= ",disc_perc='".$discPerc."'";
  if(isset($finalPrice))		$q.= ",final_price='".$finalPrice."'";
  
  if($q) $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_pricesbyqty SET ".ltrim($q,",")." WHERE id='".$id."'");
 }
 else
 {
  $fields = "item_id,colors,sizes,qty_from,qty_to,disc_perc,final_price";
  $query = "INSERT INTO dynarc_".$archiveInfo['prefix']."_pricesbyqty(".$fields.") VALUES('".$itemInfo['id']."','"
	.$db->Purify($colors)."','".$db->Purify($sizes)."','".$qtyFrom."','".$qtyTo."','".$discPerc."','".$finalPrice."')";
  $db->RunQuery($query);
  if($db->Error) return array('message'=>'MySQL ERROR:'.$db->Error, 'error'=>'MYSQL_ERROR');
  $id = $db->GetInsertId();
  $itemInfo['last_pricebyqty'] = array('id'=>$id, 'colors'=>$colors, 'sizes'=>$sizes, 'qtyfrom'=>$qtyFrom, 'qtyto'=>$qtyTo, 
	'discperc'=>$discPer, 'finalprice'=>$finalPrice);
 }
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return dynarcextension_pricesbyqty_catunset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
  }

 if($id)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_pricesbyqty WHERE id='".$id."' AND item_id='".$itemInfo['id']."'");
  $db->Close();
 }

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return dynarcextension_pricesbyqty_catget($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'colors' : $colors=true; break;
   case 'sizes' : $sizes=true; break;
  }

 if(!count($args)) $all=true;

 $itemInfo['pricesbyqty'] = array();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_pricesbyqty WHERE item_id='".$itemInfo['id']."' ORDER BY id ASC");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id'], 'qtyfrom'=>$db->record['qty_from'], 'qtyto'=>$db->record['qty_to'], 
	'discperc'=>$db->record['disc_perc'], 'finalprice'=>$db->record['final_price']);
  if($colors || $all)	
  {
   $tmp = $db->record['colors'];
   if($tmp != "")
   {
    $tmp = substr($tmp, 1, -1);
	$a['colors'] = explode("],[",$tmp);
   }
  }
  if($sizes || $all)
  {
   $tmp = $db->record['sizes'];
   if($tmp != "")
   {
    $tmp = substr($tmp, 1, -1);
	$a['sizes'] = explode("],[",$tmp);
   }
  }
  
  $itemInfo['pricesbyqty'][] = $a;
 }
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_export($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return ;

 $xml = "<pricesbyqty />";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_import($sessid, $shellid, $archiveInfo, $itemInfo, $node, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return ;

 if(!$node)
  return ;

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_pricesbyqty WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $fields = "item_id,colors,sizes,qty_from,qty_to,disc_perc,final_price";
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_pricesbyqty WHERE item_id='".$srcInfo['id']."' ORDER BY id ASC");
 while($db->Read())
 {
  $query = "INSERT INTO dynarc_".$archiveInfo['prefix']."_pricesbyqty(".$fields.") VALUES('".$cloneInfo['id']."','"
	.$db2->Purify($db->record['colors'])."','".$db2->Purify($db->record['sizes'])."','".$db->record['qty_from']."','"
	.$db->record['qty_to']."','".$db->record['disc_perc']."','".$db->record['final_price']."')";
  $db2->RunQuery($query);
 }
 $db->Close();
 $db2->Close();

 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricesbyqty_onarchiveempty($args, $sessid, $shellid, $archiveInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("TRUNCATE TABLE `dynarc_".$archiveInfo['prefix']."_pricesbyqty`");
 $db->Close(); 
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
