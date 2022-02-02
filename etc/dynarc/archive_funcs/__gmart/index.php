<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 03-12-2012
 #PACKAGE: gmart
 #DESCRIPTION: Archive functions for GMart archives
 #VERSION: 2.1beta
 #CHANGELOG: 03-12-2012 : Completamento delle funzioni principali.
 #TODO:
 
*/

function dynarcfunction_gmart_oninheritarchive($args, $sessid, $shellid, $archiveInfo)
{

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 dynarcfunction_gmart_increaseItemCounter($sessid, $shellid, $archiveInfo, $itemInfo);
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 dynarcfunction_gmart_increaseCatCounter($sessid, $shellid, $archiveInfo, $catInfo);
 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 if($itemInfo['old_cat_id'])
 {
  $oldItemInfo = $itemInfo;
  $oldItemInfo['cat_id'] = $itemInfo['old_cat_id'];
  dynarcfunction_gmart_decreaseItemCounter($sessid, $shellid, $archiveInfo, $oldItemInfo);
  dynarcfunction_gmart_increaseItemCounter($sessid, $shellid, $archiveInfo, $itemInfo);
 }
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 if($catInfo['old_parent_id'])
 {
  $oldCatInfo = $catInfo;
  $oldCatInfo['parent_id'] = $catInfo['old_parent_id'];
  dynarcfunction_gmart_decreaseCatCounter($sessid, $shellid, $archiveInfo, $oldCatInfo);
  dynarcfunction_gmart_increaseCatCounter($sessid, $shellid, $archiveInfo, $catInfo);
 }

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 dynarcfunction_gmart_decreaseCatCounter($sessid, $shellid, $archiveInfo, $catInfo);
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 dynarcfunction_gmart_decreaseItemCounter($sessid, $shellid, $archiveInfo, $itemInfo);
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_onmoveitem($sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 dynarcfunction_gmart_decreaseItemCounter($sessid, $shellid, $archiveInfo, $oldItemInfo);
 dynarcfunction_gmart_increaseItemCounter($sessid, $shellid, $archiveInfo, $newItemInfo);
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_onmovecategory($sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 dynarcfunction_gmart_decreaseCatCounter($sessid, $shellid, $archiveInfo, $oldCatInfo);
 dynarcfunction_gmart_increaseCatCounter($sessid, $shellid, $archiveInfo, $newCatInfo);
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_oncopyitem($sessid, $shellid, $archiveInfo, $cloneInfo, $srcInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_oncopycategory($sessid, $shellid, $archiveInfo, $cloneInfo, $srcInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_increaseCatCounter($sessid, $shellid, $archiveInfo, $catInfo)
{
 if(!$catInfo['parent_id'])
  return;
 
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET subcat_count=subcat_count+1 WHERE id='".$catInfo['parent_id']."'");
 $db->Close();
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_decreaseCatCounter($sessid, $shellid, $archiveInfo, $catInfo)
{
 if(!$catInfo['parent_id'])
  return;
 
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET subcat_count=subcat_count-1 WHERE id='".$catInfo['parent_id']."'");
 $db->Close();
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_increaseItemCounter($sessid, $shellid, $archiveInfo, $itemInfo)
{
 $hierarchy = ltrim(rtrim($itemInfo['hierarchy'],","),",");
 if(!$hierarchy)
  return;
 $x = explode(",",$hierarchy);
 $db = new AlpaDatabase();
 for($c=0; $c < count($x); $c++)
 {
  if(!$x[$c]) continue;
  if($x[$c] == $itemInfo['cat_id'])
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET items_count=items_count+1, totitems_count=totitems_count+1 WHERE id='".$x[$c]."'");
  else
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET totitems_count=totitems_count+1 WHERE id='".$x[$c]."'");
 }
 $db->Close();

}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_decreaseItemCounter($sessid, $shellid, $archiveInfo, $itemInfo)
{
 $hierarchy = ltrim(rtrim($itemInfo['hierarchy'],","),",");
 if(!$hierarchy)
  return;
 $x = explode(",",$hierarchy);
 $db = new AlpaDatabase();
 for($c=0; $c < count($x); $c++)
 {
  if(!$x[$c]) continue;
  if($x[$c] == $itemInfo['cat_id'])
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET items_count=items_count-1, totitems_count=totitems_count-1 WHERE id='".$x[$c]."'");
  else
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET totitems_count=totitems_count-1 WHERE id='".$x[$c]."'");
 }
 $db->Close();
}
//-------------------------------------------------------------------------------------------------------------------//


