<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-07-2016
 #PACKAGE: gmart
 #DESCRIPTION: Archive functions for GMart archives
 #VERSION: 2.5beta
 #CHANGELOG: 30-07-2016 : Prima integrazione con Gnujiko Transponder.
			 02-03-2016 : Aggiornato con nuovo comando gmart update-counters.
			 25-07-2014 : Bug fix vari
			 10-06-2014 : Aggiunta funzione onarchiveempty
			 03-12-2012 : Completamento delle funzioni principali.

 #TODO: Manca l'integrazione con le categorie.
 
*/

function dynarcfunction_gmart_oninheritarchive($args, $sessid, $shellid, $archiveInfo)
{

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 global $_BASE_PATH, $_SHELL_CMD_PATH;
 GShell("gmart increment-counter -ap '".$archiveInfo['prefix']."' -cat '".$itemInfo['cat_id']."'", $sessid, $shellid);

 if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."transponder.php"))
 {
  GShell("transponder check-for-autosync -at gmart -ap '".$archiveInfo['prefix']."' -itemid '".$itemInfo['id']."' -action sync-products && export -var GMART_TRANSPONDER_BASKET_COUNT", $sessid, $shellid);
 }

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH, $_SHELL_CMD_PATH;
 if($catInfo['parent_id'])
 {
  GShell("gmart update-counters -ap '".$archiveInfo['prefix']."' -cat '".$catInfo['parent_id']."'", $sessid, $shellid);
 }

 if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."transponder.php"))
 {
  GShell("transponder check-for-autosync -at gmart -ap '".$archiveInfo['prefix']."' -cat '".$catInfo['id']."' -action sync-products && export -var GMART_TRANSPONDER_BASKET_COUNT", $sessid, $shellid);
 }

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 global $_BASE_PATH, $_SHELL_CMD_PATH;

 if($itemInfo['old_cat_id'] != $itemInfo['cat_id'])
 {
  GShell("gmart decrement-counter -ap '".$archiveInfo['prefix']."' -cat '".$itemInfo['old_cat_id']."'", $sessid, $shellid);
  GShell("gmart increment-counter -ap '".$archiveInfo['prefix']."' -cat '".$itemInfo['cat_id']."'", $sessid, $shellid);
 }

 if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."transponder.php"))
 {
  GShell("transponder check-for-autosync -at gmart -ap '".$archiveInfo['prefix']."' -itemid '".$itemInfo['id']."' -action sync-products && export -var GMART_TRANSPONDER_BASKET_COUNT", $sessid, $shellid);
 }

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 if($catInfo['old_parent_id'] != $catInfo['parent_id'])
 {
  GShell("gmart update-counters -ap '".$archiveInfo['prefix']."'", $sessid, $shellid);
 }

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH, $_SHELL_CMD_PATH;

 GShell("gmart update-counters -ap '".$archiveInfo['prefix']."' -cat '".$catInfo['parent_id']."'", $sessid, $shellid);

 if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."transponder.php"))
 {
  GShell("transponder check-for-autosync -at gmart -ap '".$archiveInfo['prefix']."' -cat '".$catInfo['id']."' -action sync-products && export -var GMART_TRANSPONDER_BASKET_COUNT", $sessid, $shellid);
 }

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 global $_BASE_PATH, $_SHELL_CMD_PATH;

 GShell("gmart decrement-counter -ap '".$archiveInfo['prefix']."' -cat '".$itemInfo['cat_id']."'", $sessid, $shellid);

 if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."transponder.php"))
 {
  GShell("transponder check-for-autosync -at gmart -ap '".$archiveInfo['prefix']."' -itemid '".$itemInfo['id']."' -action sync-products && export -var GMART_TRANSPONDER_BASKET_COUNT", $sessid, $shellid);
 }

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH, $_SHELL_CMD_PATH;

 GShell("gmart update-counters -ap '".$archiveInfo['prefix']."' -cat '".$catInfo['parent_id']."'", $sessid, $shellid);

 if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."transponder.php"))
 {
  GShell("transponder check-for-autosync -at gmart -ap '".$archiveInfo['prefix']."' -cat '".$catInfo['id']."' -action sync-products && export -var GMART_TRANSPONDER_BASKET_COUNT", $sessid, $shellid);
 }

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 global $_BASE_PATH, $_SHELL_CMD_PATH;

 GShell("gmart decrement-counter -ap '".$archiveInfo['prefix']."' -cat '".$itemInfo['cat_id']."'", $sessid, $shellid);

 if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."transponder.php"))
 {
  GShell("transponder check-for-autosync -at gmart -ap '".$archiveInfo['prefix']."' -itemid '".$itemInfo['id']."' -action sync-products && export -var GMART_TRANSPONDER_BASKET_COUNT", $sessid, $shellid);
 }

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 if($newItemInfo['old_cat_id'] != $newItemInfo['cat_id'])
 {
  GShell("gmart decrement-counter -ap '".$archiveInfo['prefix']."' -cat '".$newItemInfo['old_cat_id']."'", $sessid, $shellid);
  GShell("gmart increment-counter -ap '".$archiveInfo['prefix']."' -cat '".$newItemInfo['cat_id']."'", $sessid, $shellid);
 }
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 if($newCatInfo['old_parent_id'] != $newCatInfo['parent_id'])
 {
  GShell("gmart update-counters -ap '".$archiveInfo['prefix']."'", $sessid, $shellid);
 }

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_oncopyitem($sessid, $shellid, $archiveInfo, $cloneInfo, $srcInfo)
{
 //dynarcfunction_gmart_updateItemCounter($sessid, $shellid, $archiveInfo, 0, $cloneInfo['cat_id']);
 /* Qui non serve l'updateItemCounter perchè viene automaticamente gia lanciata dalla funzione oncreateitem */
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_oncopycategory($sessid, $shellid, $archiveInfo, $cloneInfo, $srcInfo)
{
 //dynarcfunction_gmart_fixCounters($sessid, $shellid, $archiveInfo, $srcInfo['parent_id']);
 /* Qui non serve l'updateCatCounter perchè viene automaticamente gia lanciata dalla funzione oncreatecategory */
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH, $_SHELL_CMD_PATH;
 if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."transponder.php"))
 {
  GShell("transponder check-for-autosync -at gmart -ap '".$archiveInfo['prefix']."' -cat '".$catInfo['id']."' -action sync-products && export -var GMART_TRANSPONDER_BASKET_COUNT", $sessid, $shellid);
 }

 return GShell("gmart update-counters -ap '".$archiveInfo['prefix']."' -cat '".$catInfo['parent_id']."'", $sessid, $shellid);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 global $_BASE_PATH, $_SHELL_CMD_PATH;
 if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."transponder.php"))
 {
  GShell("transponder check-for-autosync -at gmart -ap '".$archiveInfo['prefix']."' -itemid '".$itemInfo['id']."' -action sync-products && export -var GMART_TRANSPONDER_BASKET_COUNT", $sessid, $shellid);
 }

 return GShell("gmart increment-counter -ap '".$archiveInfo['prefix']."' -cat '".$itemInfo['cat_id']."'", $sessid, $shellid);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcfunction_gmart_onarchiveempty($args, $sessid, $shellid, $archiveInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

