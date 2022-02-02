<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-07-2014
 #PACKAGE: dynarc-pricing-extension
 #DESCRIPTION: GMart Pricing extension for Dynarc.
 #VERSION: 2.7beta
 #CHANGELOG: 24-07-2014 : Aggiunto vendorprice e discount sui listini, e sostituito alcuni float con decimal 10,4
			 10-06-2014 : Aggiunta funzione onarchiveempty
			 08-04-2014 : Inserito opzione autosetpricelists su funzione set
			 18-02-2014 : Completate funzioni import export.
			 17-02-2014 : Bug fix su install-extension
			 17-04-2013 : Aggiunto listini extra.
			 03-12-2012 : Completamento delle funzioni principali.
 #TODO:Rifare funzione import & export e completare funzioni syncimport & syncexport.
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` ADD `baseprice` DECIMAL(10,4) NOT NULL , ADD `vat` FLOAT NOT NULL, ADD `pricelists` VARCHAR(255) NOT NULL");
 $db->Close();

 $ret = GShell("pricelists list",$sessid,$shellid);
 $list = $ret['outarr'];
 for($c=0; $c < count($list); $c++)
 {
  $id = $list[$c]['id'];
  $db = new AlpaDatabase();
  $db->RunQuery("ALTER TABLE dynarc_".$archiveInfo['prefix']."_items 
	ADD `pricelist_".$id."_baseprice` DECIMAL(10,4) NOT NULL , 
	ADD `pricelist_".$id."_mrate` FLOAT NOT NULL , 
	ADD `pricelist_".$id."_discount` FLOAT NOT NULL ,
	ADD `pricelist_".$id."_vendorprice` DECIMAL(10,4) NOT NULL , 
	ADD `pricelist_".$id."_cm` FLOAT NOT NULL ,  
	ADD `pricelist_".$id."_vat` FLOAT NOT NULL");
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

 $ret = GShell("pricelists list",$sessid,$shellid);
 $list = $ret['outarr'];
 for($c=0; $c < count($list); $c++)
 {
  $id = $list[$c]['id'];
  $db = new AlpaDatabase();
  $db->RunQuery("ALTER TABLE dynarc_".$archiveInfo['prefix']."_items 
	DROP `pricelist_".$id."_baseprice`, 
	DROP `pricelist_".$id."_mrate`, 
	DROP `pricelist_".$id."_discount`,
	DROP `pricelist_".$id."_vendorprice`, 
	DROP `pricelist_".$id."_cm`,  
	DROP `pricelist_".$id."_vat`");
  $db->Close();
 }

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
   case 'autosetpricelists' : {$autosetPricelists=$args[$c+1]; $c++;} break;
  }

 if($autosetPricelists)
 {
  // get pricelists
  $ret = GShell("pricelists list",$sessid,$shellid);
  $_PRICELISTS = $ret['outarr'];
 }

 $db = new AlpaDatabase();
 $q="";
 if(isset($basePrice))
  $q.=",baseprice='".$basePrice."'";
 if(isset($vat))
  $q.=",vat='".$vat."'";
 if(isset($pricelists))
  $q.= ",pricelists='".$pricelists."'";

 if($autosetPricelists)
 {
  for($c=0; $c < count($_PRICELISTS); $c++)
   $q.= ",pricelist_".$_PRICELISTS[$c]['id']."_baseprice='".$basePrice."',pricelist_".$_PRICELISTS[$c]['id']."_vat='".$vat."'";
  if(!$itemInfo['mtime'])
   $q.= ",mtime='".date('Y-m-d H:i:s')."'";
 }

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
 $ret = GShell("pricelists list",$sessid,$shellid);
 $list = $ret['outarr'];

 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$srcInfo['id']."'");
 $db->Read();

 $qry = "UPDATE dynarc_".$archiveInfo['prefix']."_items SET baseprice='".$db->record['baseprice']."',vat='"
	.$db->record['vat']."',pricelists='".$db->record['pricelists']."'";

 $plfields = array("baseprice","mrate","vat","vendorprice","cm","discount");

 for($c=0; $c < count($list); $c++)
 {
  $plid = $list[$c]['id'];
  for($i=0; $i < count($plfields); $i++)
   $qry.= ",pricelist_".$plid."_".$plfields[$i]."='".$db->record['pricelist_'.$plid.'_'.$plfields[$i]]."'";
 }

 $qry.= " WHERE id='".$cloneInfo['id']."'";

 $db2->RunQuery($qry);
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
function dynarcextension_pricing_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_onarchiveempty($args, $sessid, $shellid, $archiveInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_export($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return;

 $ret = GShell("pricelists list",$sessid,$shellid);
 $list = $ret['outarr'];

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 $xml = "<pricing baseprice='".$db->record['baseprice']."' vat='".$db->record['vat']."' pricelists='".$db->record['pricelists']."'";
 for($c=0; $c < count($list); $c++)
 {
  $plid = $list[$c]['id'];
  $xml.= " pricelist_".$plid."_baseprice='".$db->record['pricelist_'.$plid.'_baseprice']."'";
  $xml.= " pricelist_".$plid."_mrate='".$db->record['pricelist_'.$plid.'_mrate']."'";
  $xml.= " pricelist_".$plid."_discount='".$db->record['pricelist_'.$plid.'_discount']."'";
  $xml.= " pricelist_".$plid."_vendorprice='".$db->record['pricelist_'.$plid.'_vendorprice']."'";
  $xml.= " pricelist_".$plid."_vat='".$db->record['pricelist_'.$plid.'_vat']."'";
 }
 $xml.= "/>";
 $db->Close();

 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pricing_import($sessid, $shellid, $archiveInfo, $itemInfo, $node, $isCategory=false)
{
 if($isCategory)
  return false;

 $ret = GShell("pricelists list",$sessid,$shellid);
 $list = $ret['outarr'];

 $qry = "";
 $db = new AlpaDatabase();
 if($baseprice = $node->getString('baseprice'))
  $qry.= ",baseprice='".$baseprice."'";
 if($vat = $node->getString('vat'))
  $qry.= ",vat='".$vat."'";
 if($pricelists = $node->getString('pricelists'))
  $qry.= ",pricelists='".$pricelists."'";
 for($c=0; $c < count($list); $c++)
 {
  $plid = $list[$c]['id'];
  if($baseprice = $node->getString('pricelist_'.$plid.'_baseprice'))
   $qry.= ",pricelist_".$plid."_baseprice='".$baseprice."'";
  if($mrate = $node->getString('pricelist_'.$plid.'_mrate'))
   $qry.= ",pricelist_".$plid."_mrate='".$mrate."'";
  if($discount = $node->getString('pricelist_'.$plid.'_discount'))
   $qry.= ",pricelist_".$plid."_discount='".$discount."'"; 
  if($vendorprice = $node->getString('pricelist_'.$plid.'_vendorprice'))
   $qry.= ",pricelist_".$plid."_vendorprice='".$vendorprice."'"; 
  if($vat = $node->getString('pricelist_'.$plid.'_vat'))
   $qry.= ",pricelist_".$plid."_vat='".$vat."'"; 
 }

 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($qry,",")." WHERE id='".$itemInfo['id']."'");
 $db->Close();

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

