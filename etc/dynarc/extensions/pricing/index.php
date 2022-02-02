<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-04-2013
 #PACKAGE: dynarc-pricing-extension
 #DESCRIPTION: GMart Pricing extension for Dynarc.
 #VERSION: 2.2beta
 #CHANGELOG: 17-04-2013 : Aggiunto listini extra.
			 03-12-2012 : Completamento delle funzioni principali.
 #TODO:Rifare funzione import & export e completare funzioni syncimport & syncexport.
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` ADD `baseprice` FLOAT NOT NULL , ADD `vat` FLOAT NOT NULL, ADD `pricelists` VARCHAR(255) NOT NULL,");
 $db->Close();

 $ret = GShell("pricelists list",$sessid,$shellid);
 $list = $ret['outarr'];
 for($c=0; $c < count($list); $c++)
 {
  $id = $list[$c]['id'];
  $db = new AlpaDatabase();
  $db->RunQuery("ALTER TABLE dynarc_".$archiveInfo['prefix']."_items ADD `pricelist_".$id."_baseprice` FLOAT NOT NULL , ADD `pricelist_".$id."_mrate` FLOAT NOT NULL , ADD `pricelist_".$id."_vat` FLOAT NOT NULL");
  $db->Close();
 }

 return array("message"=>"GMart:Pricing extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` DROP `baseprice`, DROP `vat`, DROP `pricelists`");
 $db->Close();

 return array("message"=>"GMart:Pricing extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_catset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_pricing_catset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'baseprice' : {$basePrice=$args[$c+1]; $c++;} break;
   case 'vat' : {$vat=$args[$c+1]; $c++;} break;
   case 'pricelists' : {$pricelists=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 $q="";
 if(isset($basePrice))
  $q.=",baseprice='".$basePrice."'";
 if(isset($vat))
  $q.=",vat='".$vat."'";
 if(isset($pricelists))
  $q.= ",pricelists='".$pricelists."'";

 if($q)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");
 $db->Close();


 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_catget($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_pricing_catget($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT baseprice,vat,pricelists FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 $itemInfo['baseprice'] = $db->record['baseprice'];
 $itemInfo['vat'] = $db->record['vat'];
 $itemInfo['pricelists'] = $db->record['pricelists'];
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT baseprice,vat,pricelists FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$srcInfo['id']."'");
 $db->Read();
 $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET baseprice='".$db->record['baseprice']."',vat='"
	.$db->record['vat']."',pricelists='".$db->record['pricelists']."' WHERE id='".$cloneInfo['id']."'");
 $db2->Close();
 $db->Close();

 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_export($sessid, $shellid, $archiveInfo, $itemInfo)
{
 $xml = "";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_import($sessid, $shellid, $archiveInfo, $itemInfo, $node)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

