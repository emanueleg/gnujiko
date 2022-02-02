<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-02-2013
 #PACKAGE: idoc-config
 #DESCRIPTION: Archive functions for iDoc
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

function dynarcfunction_idoc_oninheritarchive($args, $sessid, $shellid, $archiveInfo)
{

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_idoc_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_idoc_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_idoc_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_idoc_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_idoc_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_idoc_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_idoc_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_idoc_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_idoc_onmoveitem($sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_idoc_onmovecategory($sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_idoc_oncopyitem($sessid, $shellid, $archiveInfo, $cloneInfo, $srcInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT params,thumbdata FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$srcInfo['id']."'");
 $db->Read();
 $params = $db->record['params'];
 $thumbdata = $db->record['thumbdata'];
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET params='".$params."',thumbdata='".$thumbdata."' WHERE id='".$cloneInfo['id']."'");
 $db->Close();

 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_idoc_oncopycategory($sessid, $shellid, $archiveInfo, $cloneInfo, $srcInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_idoc_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_idoc_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

