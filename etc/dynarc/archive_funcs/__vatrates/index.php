<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 03-12-2012
 #PACKAGE: companyprofile-config
 #DESCRIPTION: Archive functions for VatRates
 #VERSION: 2.1beta
 #CHANGELOG: 03-12-2012 : Completamento delle funzioni principali.
 #TODO: 
 
*/

//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_vatrates_oninheritarchive($args, $sessid, $shellid, $archiveInfo)
{

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_vatrates_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 $ret = GShell("vatregister register-list",$sessid,$shellid);
 $db = new AlpaDatabase();
 for($c=0; $c < count($ret['outarr']); $c++)
  $db->RunQuery("ALTER TABLE `vat_register_".$ret['outarr'][$c]['year']."` ADD `vr_".$itemInfo['id']."_amount` FLOAT NOT NULL , ADD `vr_".$itemInfo['id']."_vat` FLOAT NOT NULL");
 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_vatrates_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_vatrates_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_vatrates_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_vatrates_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_vatrates_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_vatrates_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_vatrates_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_vatrates_onmoveitem($sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_vatrates_onmovecategory($sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_vatrates_oncopyitem($sessid, $shellid, $archiveInfo, $cloneInfo, $srcInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_vatrates_oncopycategory($sessid, $shellid, $archiveInfo, $cloneInfo, $srcInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_vatrates_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_vatrates_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

