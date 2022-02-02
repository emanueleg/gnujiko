<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 06-09-2013
 #PACKAGE: rubrica
 #DESCRIPTION: Rubrica extended informations.
 #VERSION: 2.4beta
 #CHANGELOG: 06-09-2013 : Aggiunto Fidelity Card
			 14-03-2013 : Completate funzioni di sync import & export.
			 31-01-2013 : Aggiunto campo 'distance'
			 03-12-2012 : Completamento delle funzioni principali.
			 21-06-2012 : Pricelist added.
 #TODO:
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` ADD `iscompany` TINYINT( 1 ) NOT NULL , 
	ADD `taxcode` VARCHAR( 16 ) NOT NULL ,
	ADD `vatnumber` VARCHAR( 11 ) NOT NULL ,
	ADD `paymentmode` TINYINT( 1 ) NOT NULL ,
	ADD `pricelist_id` INT( 11 ) NOT NULL ,
	ADD `distance` FLOAT NOT NULL,
	ADD `fidelitycard` VARCHAR( 32 ) NOT NULL ,
	ADD INDEX (`fidelitycard`)");
 $db->Close();

 return array("message"=>"Rubrica main-info extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` DROP `iscompany`, 
	DROP `taxcode`, 
	DROP `vatnumber`, 
	DROP `paymentmode`, 
	DROP `pricelist_id`, 
	DROP `distance` ,
	DROP `fidelitycard`");
 $db->Close();

 return array("message"=>"Rubrica main-info extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_set($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'iscompany' : {$iscompany=$args[$c+1]; $c++;} break;
   case 'taxcode' : {$taxcode=$args[$c+1]; $c++;} break;
   case 'vatnumber' : {$vatnumber=$args[$c+1]; $c++;} break;
   case 'paymentmode' : {$paymentmode=$args[$c+1]; $c++;} break;
   case 'pricelist' : {$pricelist=$args[$c+1]; $c++;} break;
   case 'distance' : {$distance=$args[$c+1]; $c++;} break;
   case 'fidelitycard' : {$fidelityCard=$args[$c+1]; $c++;} break;
  }

 $q = "";
 if(isset($iscompany)){$itemInfo['iscompany'] = $iscompany;
  $q.= ",iscompany='$iscompany'";}
 if(isset($taxcode)){$itemInfo['taxcode'] = $taxcode;
  $q.= ",taxcode='$taxcode'";}
 if(isset($vatnumber)){$itemInfo['vatnumber'] = $vatnumber;
  $q.= ",vatnumber='$vatnumber'";}
 if(isset($paymentmode)){$itemInfo['paymentmode'] = $paymentmode;
  $q.= ",paymentmode='$paymentmode'";}
 if(isset($pricelist)){$itemInfo['pricelist_id'] = $pricelist;
  $q.= ",pricelist_id='$pricelist'";}
 if(isset($distance)){$itemInfo['distance'] = $distance;
  $q.= ",distance='".$distance."'";}
 if(isset($fidelityCard)){$itemInfo['fidelitycard'] = $fidelityCard;
  $q.= ",fidelitycard='".$fidelityCard."'";}

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".substr($q,1)." WHERE id='".$itemInfo['id']."'");
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_get($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'iscompany' : $iscompany=true; break;
   case 'taxcode' : $taxcode=true; break;
   case 'vatnumber' : $vatnumber=true; break;
   case 'paymentmode' : $paymentmode=true; break;
   case 'pricelist' : $pricelist=true; break;
   case 'distance' : $distance=true; break;
   case 'fidelitycard' : $fidelityCard=true; break;
  }

 if(!count($args))
  $all = true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 if($iscompany || $all)
  $itemInfo['iscompany'] = $db->record['iscompany'];
 if($taxcode || $all)
  $itemInfo['taxcode'] = $db->record['taxcode'];
 if($vatnumber || $all)
  $itemInfo['vatnumber'] = $db->record['vatnumber'];
 if($paymentmode || $all)
  $itemInfo['paymentmode'] = $db->record['paymentmode'];
 if($pricelist || $all)
  $itemInfo['pricelist_id'] = $db->record['pricelist_id'];
 if($distance || $all)
  $itemInfo['distance'] = $db->record['distance'];
 if($fidelityCard || $all)
  $itemInfo['fidelitycard'] = $db->record['fidelitycard'];
 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$srcInfo['id']."'");
 $db->Read();

 $cloneInfo['taxcode'] = $db->record['taxcode'];
 $cloneInfo['vatnumber'] = $db->record['vatnumber'];
 $cloneInfo['paymentmode'] = $db->record['paymentmode'];
 $cloneInfo['iscompany'] = $db->record['iscompany'];
 $cloneInfo['pricelist_id'] = $db->record['pricelist_id'];
 $cloneInfo['distance'] = $db->record['distance'];
 $cloneInfo['fidelitycard'] = $db->record['fidelitycard'];

 $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET taxcode='".$db->record['taxcode']."',vatnumber='"
	.$db->record['vatnumber']."',paymentmode='".$db->record['paymentmode']."',iscompany='".$db->record['iscompany']."',pricelist_id='"
	.$db->record['pricelist_id']."',distance='".$db->record['distance']."',fidelitycard='".$db->record['fidelitycard']."' WHERE id='"
	.$cloneInfo['id']."'");
 $db2->Close();
 $db->Close();

 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_export($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 $xml = "<rubricainfo taxcode='".$db->record['taxcode']."' vatnumber='".$db->record['vatnumber']."' paymentmode='"
	.$db->record['paymentmode']."' iscompany='".$db->record['iscompany']."' pricelist_id='".$db->record['pricelist_id']."' distance='"
	.$db->record['distance']."' fidelitycard='".$db->record['fidelitycard']."'/>";
 $db->Close();
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_import($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 if($isCategory)
  return;

 $node = $xmlNode->GetElementsByTagName('rubricainfo');
 $node = $node[0];

 $q = "";
 if($iscompany = $node->getString('iscompany'))
  $q.= ",iscompany='$iscompany'";
 if($taxcode = $node->getString('taxcode'))
  $q.= ",taxcode='$taxcode'";
 if($vatnumber = $node->getString('vatnumber'))
  $q.= ",vatnumber='$vatnumber'";
 if($paymentmode = $node->getString('paymentmode'))
  $q.= ",paymentmode='$paymentmode'";
 if($pricelist = $node->getString('pricelist_id'))
  $q.= ",pricelist_id='$pricelist'";
 if($distance = $node->getString('distance'))
  $q.= ",distance='".$distance."'";
 if($fidelityCard = $node->getString('fidelitycard'))
  $q.= ",fidelitycard='".$fidelityCard."'";
 if($q != "")
 {
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");
  $db->Close();
 }
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 
 if($isCategory)
  return;

 $xml = "";
 $attachments = array();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 $xml = "<rubricainfo taxcode='".$db->record['taxcode']."' vatnumber='".$db->record['vatnumber']."' paymentmode='"
	.$db->record['paymentmode']."' iscompany='".$db->record['iscompany']."' pricelist_id='".$db->record['pricelist_id']."' distance='"
	.$db->record['distance']."' fidelitycard='".$db->record['fidelitycard']."'/>";
 $db->Close();


 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_rubricainfo_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USERS_HOMES;
 if($isCategory)
  return;

 $node = $xmlNode->GetElementsByTagName('rubricainfo');
 $node = $node[0];

 $q = "";
 if($iscompany = $node->getString('iscompany'))
  $q.= ",iscompany='$iscompany'";
 if($taxcode = $node->getString('taxcode'))
  $q.= ",taxcode='$taxcode'";
 if($vatnumber = $node->getString('vatnumber'))
  $q.= ",vatnumber='$vatnumber'";
 if($paymentmode = $node->getString('paymentmode'))
  $q.= ",paymentmode='$paymentmode'";
 if($pricelist = $node->getString('pricelist_id'))
  $q.= ",pricelist_id='$pricelist'";
 if($distance = $node->getString('distance'))
  $q.= ",distance='".$distance."'";
 if($fidelityCard = $node->getString('fidelitycard'))
  $q.= ",fidelitycard='".$fidelityCard."'";
 if($q != "")
 {
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");
  $db->Close();
 }

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

