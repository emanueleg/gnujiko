<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-02-2013
 #PACKAGE: gserv
 #DESCRIPTION: GServ basic info extension for Dynarc.
 #VERSION: 2.3beta
 #CHANGELOG: 11-02-2013 : Aggiunti units e qty_sold.
			 29-01-2013 : Aggiunto service-type.
			 03-12-2012 : Completamento delle funzioni principali.
 #TODO:Rifare funzione import & export e completare funzioni syncimport & syncexport.
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` ADD `pricemode` TINYINT( 1 ) NOT NULL ,
ADD `estimated_timelength` INT( 11 ) NOT NULL, ADD `service_type` VARCHAR( 32 ) NOT NULL, ADD `qty_sold` FLOAT NOT NULL , ADD `units` VARCHAR( 16 ) NOT NULL");

 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_categories` ADD `subcat_count` INT( 4 ) NOT NULL ,
ADD `items_count` INT( 4 ) NOT NULL ,
ADD `totitems_count` INT( 4 ) NOT NULL");

 $db->Close();
 return array("message"=>"GServ extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` DROP `pricemode`, DROP `estimated_timelength`, DROP `service_type`, DROP `units`, DROP `qty_sold`");
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_categories` DROP `subcat_count`,  DROP `items_count`,  DROP `totitems_count`");
 $db->Close();

 return array("message"=>"GServ extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_catset($args, $sessid, $shellid, $archiveInfo, $catInfo)
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
function dynarcextension_gserv_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_gserv_catset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'pricemode' : {$priceMode=$args[$c+1]; $c++;} break;
   case 'tl' : case 'timelength' : case 'estimated-timelength' : {$estimatedTimeLength=$args[$c+1]; $c++;} break;
   case 'type' : {$serviceType=$args[$c+1]; $c++;} break;
   case 'sold' : case 'qty-sold' : case 'qtysold' : {$qtySold=$args[$c+1]; $c++;} break;
   case 'units' : case 'umis' : {$units=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 $q="";
 if(isset($priceMode))
  $q.=",pricemode='".$priceMode."'";
 if(isset($estimatedTimeLength))
  $q.=",estimated_timelength='".$estimatedTimeLength."'";
 if(isset($serviceType))
  $q.=",service_type='".$serviceType."'";
 if(isset($qtySold))
  $q.= ",qty_sold='".$qtySold."'";
 if(isset($units))
  $q.= ",units='".$units."'";

 if($q)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");
 $db->Close();


 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_catget($args, $sessid, $shellid, $archiveInfo, $catInfo)
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
function dynarcextension_gserv_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_gserv_catget($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'pricemode' : $priceMode=true; break;
   case 'tl' : case 'timelength' : case 'estimated-timelength' : $estimatedTimeLength=true; break;
   case 'type' : $serviceType=true; break;
   case 'sold' : case 'qty-sold' : case 'qtysold' : $qtySold=true; break;
   case 'units' : case 'umis' : $units=true; break;
  }

 if(!count($args))
  $all=true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT pricemode,estimated_timelength,service_type,qty_sold,units FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 if($priceMode || $all)
  $itemInfo['pricemode'] = $db->record['pricemode'];
 if($estimatedTimeLength || $all)
  $itemInfo['estimated_timelength'] = $db->record['estimated_timelength'];
 if($serviceType || $all)
  $itemInfo['type'] = $db->record['service_type'];
 if($qtySold || $all)
  $itemInfo['sold'] = $db->record['qty_sold'];
 if($units || $all)
  $itemInfo['units'] = $db->record['units'];
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT pricemode,estimated_timelength,units FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$srcInfo['id']."'");
 $db->Read();
 $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET pricemode='".$db->record['pricemode']."',estimated_timelength='"
	.$db->record['estimated_timelength']."',service_type='".$db->record['service_type']."',units='".$db->record['units']."' WHERE id='"
	.$cloneInfo['id']."'");
 $db2->Close();
 $db->Close();

 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_export($sessid, $shellid, $archiveInfo, $itemInfo)
{
 $xml = "";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_import($sessid, $shellid, $archiveInfo, $itemInfo, $node)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_gserv_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//


