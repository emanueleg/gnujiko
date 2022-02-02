<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 03-12-2012
 #PACKAGE: dynarc-storeinfo-extension
 #DESCRIPTION: GMart store info extension for Dynarc.
 #VERSION: 2.0beta
 #CHANGELOG: 03-12-2012 : Completamento delle funzioni principali.
 #TODO:Rifare funzione import & export e completare funzioni syncimport & syncexport.
 
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
ADD INDEX ( `booked` , `incoming` )");
 $db->Close();

 // TODO: Bisogna modificare la query per includere tutti i magazzini esistenti //

 return array("message"=>"Gmart:StoreInfo extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` DROP `storeqty`, DROP `booked`, DROP `incoming`, DROP `loaded`, DROP `downloaded`");
 $db->Close();

 return array("message"=>"GMart:StoreInfo extension has been removed from archive ".$archiveInfo['name']."\n");
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
  }

 if(!count($args))
  $all=true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT storeqty,booked,incoming,loaded,downloaded FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
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
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
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
function dynarcextension_storeinfo_export($sessid, $shellid, $archiveInfo, $itemInfo)
{
 $xml = "";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_storeinfo_import($sessid, $shellid, $archiveInfo, $itemInfo, $node)
{
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


