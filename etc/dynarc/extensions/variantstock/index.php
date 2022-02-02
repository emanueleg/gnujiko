<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
HackTVT Project
copyright(C) 2014 Alpatech mediaware - www.alpatech.it
license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
Gnujiko 10.1 is free software released under GNU/GPL license
developed by D. L. Alessandro (alessandro@alpatech.it)

#DATE: 19-11-2014
#PACKAGE: dynarc-variants-extension
#DESCRIPTION: 
#VERSION: 2.0beta
#CHANGELOG: 
#TODO: 
*/

global $_BASE_PATH;

function dynarcextension_variantstock_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_variantstock` (
	`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`item_id` INT(11) NOT NULL ,
	`coltint` VARCHAR(48) NOT NULL ,
	`sizmis` VARCHAR(48) NOT NULL ,
	INDEX (`item_id`,`coltint`,`sizmis`)
	)");
 $db->Close();

 $qry = "";
 $ret = GShell("store list",$sessid,$shellid);
 $list = $ret['outarr'];
 for($c=0; $c < count($list); $c++)
  $qry.= ", ADD `store_".$list[$c]['id']."_qty` FLOAT NOT NULL";
 if($qry)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_variantstock`".ltrim($qry,","));
  $db->Close();
 }

 return array("message"=>"VariantStock extension has been installed into archive ".$archiveInfo['name']);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DROP TABLE `dynarc_".$archiveInfo['prefix']."_variantstock`");
 $db->Close();

 return array("message"=>"VariantStock extension has been removed from archive ".$archiveInfo['name']);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_catset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {

  }

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_catunset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {

  }

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_catget($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {

  }

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return dynarcextension_variantstock_catset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {

  }

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return dynarcextension_variantstock_catunset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {

  }

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return dynarcextension_variantstock_catget($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {

  }

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_export($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return ;

 $xml = "<variantstock />";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_import($sessid, $shellid, $archiveInfo, $itemInfo, $node, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return ;

 if(!$node)
  return ;

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variantstock_onarchiveempty($args, $sessid, $shellid, $archiveInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
