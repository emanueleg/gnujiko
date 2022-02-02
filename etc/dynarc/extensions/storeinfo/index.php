<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-04-2017
 #PACKAGE: dynarc-storeinfo-extension
 #DESCRIPTION: GMart store info extension for Dynarc.
 #VERSION: 2.8beta
 #CHANGELOG: 30-04-2017 : Aggiornate funzioni import ed export.
			 31-03-2015 : Sostituito DECIMAL(10,4) con DECIMAL(10,5).
			 16-01-2015 : Aggiornata funzione install.
			 20-10-2014 : Aggiunto stock-enhancement.
			 10-06-2014 : Aggiunta funzione onarchiveempty
			 20-02-2014 : Aggiunto scorta minima
			 18-02-2014 : Bug fix su funzione install.
			 18-02-2014 : Completate funzioni import export.
			 03-12-2012 : Completamento delle funzioni principali.
 #TODO:
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` ADD `storeqty` FLOAT NOT NULL ,
ADD `booked` FLOAT NOT NULL ,
ADD `incoming` FLOAT NOT NULL ,
ADD `loaded` FLOAT NOT NULL ,
ADD `downloaded` FLOAT NOT NULL ,
ADD `minimum_stock` FLOAT NOT NULL ,
ADD INDEX (`booked`,`incoming`)");
 $db->Close();

 $qry = "";
 $ret = GShell("store list",$sessid,$shellid);
 $list = $ret['outarr'];
 for($c=0; $c < count($list); $c++)
  $qry.= ", ADD `store_".$list[$c]['id']."_qty` FLOAT NOT NULL";
 if($qry)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items`".ltrim($qry,","));
  $db->Close();
 }

 $out = "";
 // creo tabella stockenhcat //
 $db = new AlpaDatabase();
 $query = "CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_stockenhcat` (`cat_id` INT(11) NOT NULL PRIMARY KEY";
 for($c=0; $c < count($list); $c++)
  $query.= ", `store_".$list[$c]['id']."_amount` DECIMAL(10,5) NOT NULL, `store_"
	.$list[$c]['id']."_vat` DECIMAL(10,5) NOT NULL, `store_".$list[$c]['id']."_total` DECIMAL(10,5) NOT NULL";
 $query.= ")";
 $db->RunQuery($query);
 if($db->Error) $out.= "MySQL Error: ".$db->Error."\nQry: ".$query."\n\n";
 $db->Close();

 // creo tabella stockenhitm //
 $db = new AlpaDatabase();
 $query = "CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_stockenhitm` (`item_id` INT(11) NOT NULL PRIMARY KEY";
 for($c=0; $c < count($list); $c++)
  $query.= ", `store_".$list[$c]['id']."_qty` FLOAT NOT NULL, `store_".$list[$c]['id']."_amount` DECIMAL(10,5) NOT NULL, `store_"
	.$list[$c]['id']."_vat` DECIMAL(10,5) NOT NULL, `store_".$list[$c]['id']."_total` DECIMAL(10,5) NOT NULL";
 $query.= ")";
 $db->RunQuery($query);
 if($db->Error) $out.= "MySQL Error: ".$db->Error."\nQry: ".$query."\n\n";
 $db->Close();
 
 return array("message"=>$out."StoreInfo extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` DROP `storeqty`, DROP `booked`, DROP `incoming`, DROP `loaded`, DROP `downloaded`, DROP `minimum_stock`");
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_stockenhcat`");
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_stockenhitm`");
 $db->Close();

 return array("message"=>"StoreInfo extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_catset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_storeinfo_catset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'storeqty' : {$storeQty=$args[$c+1]; $c++;} break;
   case 'booked' : {$booked=$args[$c+1]; $c++;} break;
   case 'incoming' : {$incoming=$args[$c+1]; $c++;} break;
   case 'loaded' : {$loaded=$args[$c+1]; $c++;} break;
   case 'downloaded' : {$downLoaded=$args[$c+1]; $c++;} break;
   case 'minstock' : {$minStock=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 $q="";
 if(isset($storeQty))
  $q.=",storeqty='".$storeQty."'";
 if(isset($booked))
  $q.= ",booked='".$booked."'";
 if(isset($incoming))
  $q.= ",incoming='".$incoming."'";
 if(isset($loaded))
  $q.= ",loaded='".$loaded."'";
 if(isset($downLoaded))
  $q.= ",downloaded='".$downLoaded."'";
 if(isset($minStock))
  $q.= ",minimum_stock='".$minStock."'";

 if($q)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");
 $db->Close();


 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_catget($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_storeinfo_catget($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'storeqty' : $storeQty=true; break;
   case 'booked' : $booked=true; break;
   case 'incoming' : $incoming=true; break;
   case 'loaded' : $loaded=true; break;
   case 'downloaded' : $downLoaded=true; break;
   case 'minstock' : $minstock=true; break;
  }

 if(!count($args))
  $all=true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT storeqty,booked,incoming,loaded,downloaded,minimum_stock FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='"
	.$itemInfo['id']."'");
 $db->Read();
 if($storeQty || $all)
  $itemInfo['storeqty'] = $db->record['storeqty'];
 if($booked || $all)
  $itemInfo['booked'] = $db->record['booked'];
 if($incoming || $all)
  $itemInfo['incoming'] = $db->record['incoming'];
 if($loaded || $all)
  $itemInfo['loaded'] = $db->record['loaded'];
 if($downLoaded || $all)
  $itemInfo['downloaded'] = $db->record['downloaded'];
 if($minstock || $all)
  $itemInfo['minstock'] = $db->record['minimum_stock'];
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_stockenhitm WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_stockenhcat WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_onarchiveempty($args, $sessid, $shellid, $archiveInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_export($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return;

 $_STORELIST = array();

 $db = new AlpaDatabase();

 // Get store list
 $db->RunQuery("SELECT id FROM stores WHERE 1");
 while($db->Read())
  $_STORELIST[] = $db->record['id'];

 // Export store info
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 $xml = "<storeinfo storeqty='".$db->record['storeqty']."' booked='".$db->record['booked']."' incoming='".$db->record['incoming']."' loaded='"
	.$db->record['loaded']."' downloaded='".$db->record['downloaded']."' minstock='".$db->record['minimum_stock']."'";

 // Export qty for each store.
 for($c=0; $c < count($_STORELIST); $c++)
  $xml.= " store_".$_STORELIST[$c]."_qty='".$db->record['store_'.$_STORELIST[$c].'_qty']."'";

 $xml.= "/>";
 $db->Close();

 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_import($sessid, $shellid, $archiveInfo, $itemInfo, $node, $isCategory=false)
{
 if($isCategory)
  return;

 $db = new AlpaDatabase();

 // Get store list
 $db->RunQuery("SELECT id FROM stores WHERE 1");
 while($db->Read())
  $_STORELIST[] = $db->record['id'];

 // Import storeinfo
 $qry = "";
 if($storeqty = $node->getString('storeqty'))
  $qry.= ",storeqty='".$storeqty."'";
 if($booked = $node->getString('booked'))
  $qry.= ",booked='".$booked."'";
 if($incoming = $node->getString('incoming'))
  $qry.= ",incoming='".$incoming."'";
 if($loaded = $node->getString('loaded'))
  $qry.= ",loaded='".$loaded."'";
 if($downloaded = $node->getString('downloaded'))
  $qry.= ",downloaded='".$downloaded."'";
 if($minstock = $node->getString('minstock'))
  $qry.= ",minimum_stock='".$minstock."'";

 // Import qty for each store
 for($c=0; $c < count($_STORELIST); $c++)
 {
  if($qty = $node->getString('store_'.$_STORELIST[$c].'_qty'))
   $qry.= ",store_".$_STORELIST[$c]."_qty='".$qty."'";
 }

 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($qry,",")." WHERE id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//


