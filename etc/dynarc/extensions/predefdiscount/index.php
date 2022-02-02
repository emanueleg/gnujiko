<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
HackTVT Project
copyright(C) 2016 Alpatech mediaware - www.alpatech.it
license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
Gnujiko 10.1 is free software released under GNU/GPL license
developed by D. L. Alessandro (alessandro@alpatech.it)

#DATE: 17-12-2016
#PACKAGE: dynarc-predefdiscount-extension
#DESCRIPTION: Predefined discount extension
#VERSION: 2.0beta
#CHANGELOG: 
#TODO: 
*/

global $_BASE_PATH;

function dynarcextension_predefdiscount_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_predefdiscount` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`item_id` INT(11) NOT NULL,
	`ap` VARCHAR(32) NOT NULL,
	`cat_id` INT(11) NOT NULL,
	`percentage` FLOAT NOT NULL,
    PRIMARY KEY(`id`),
	INDEX (`item_id`,`ap`,`cat_id`)
 )");
 $db->Close();

 return array("message"=>"PredefDiscount extension has been installed into archive ".$archiveInfo['name']);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_predefdiscount`");
 $db->Close();
 return array("message"=>"PredefDiscount extension has been removed from archive ".$archiveInfo['name']);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_catset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {

  }

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_catunset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {

  }

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_catget($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {

  }

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return dynarcextension_predefdiscount_catset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 $_AP = array();
 $_CAT = array();
 $_PERC = array();

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'ap' : { $_AP = explode(",",$args[$c+1]); $c++;} break;				// Archive prefix separated by comma
   case 'catid' : { $_CAT = explode(",",$args[$c+1]); $c++;} break;			// Categories ID separated by comma
   case 'percentage' : { $_PERC = explode(",",$args[$c+1]); $c++;} break;	// Percentages separated by comma
  }

 $db = new AlpaDatabase();
 for($c=0; $c < count($_AP); $c++)
 {
  $ap = $_AP[$c];
  $catId = $_CAT[$c] ? $_CAT[$c] : 0;
  $perc = $_PERC[$c] ? $_PERC[$c] : 0;

  $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_predefdiscount WHERE item_id='".$itemInfo['id']."' AND ap='"
	.$ap."' AND cat_id='".$catId."' LIMIT 1");
  if($db->Read())
  {
   if(!$perc)
	$db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_predefdiscount WHERE id='".$db->record['id']."'");
   else
    $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_predefdiscount SET percentage='".$perc."' WHERE id='".$db->record['id']."'");
  }
  else if($perc)
   $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_predefdiscount (item_id,ap,cat_id,percentage) VALUES('"
	.$itemInfo['id']."','".$ap."','".$catId."','".$perc."')");
  if($db->Error) return array('message'=>"EXT:predefdiscount set failed!\nMySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
 }
 $db->Close();
 

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return dynarcextension_predefdiscount_catunset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {

   case 'id' : { $id=$args[$c+1]; $c++;} break;
   case 'ids' : { $ids=$args[$c+1]; $c++;} break;
   case 'all' : $all=true; break;
  }

 $db = new AlpaDatabase();
 if($ids)
 {
  $list = explode(",",$ids);
  for($c=0; $c < count($list); $c++)
   $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_predefdiscount WHERE id='".$list[$c]."'");
 }
 else if($id) 		$db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_predefdiscount WHERE id='".$id."'");
 else if($all)		$db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_predefdiscount WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return dynarcextension_predefdiscount_catget($args, $sessid, $shellid, $archiveInfo, $itemInfo);


 $outArr = array();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT ap,cat_id,percentage FROM dynarc_".$archiveInfo['prefix']."_predefdiscount WHERE item_id='".$itemInfo['id']."'");
 while($db->Read())
 {
  $ap = $db->record['ap'];
  $catId = $db->record['cat_id'] ? $db->record['cat_id'] : 0;
  $perc = $db->record['percentage'] ? $db->record['percentage'] : 0;

  if(!$outArr[$ap])		$outArr[$ap] = array();
  $outArr[$ap][$catId] = $perc;
 }
 $db->Close();

 $itemInfo['predefdiscount'] = $outArr;

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_newbycat($params, $sessid=0, $shellid=0)
{
 $out = "";
 $outArr = array();

 /* ARRAYZE PARAMS */
 $tmp = explode("&",$params);
 $params = array();
 for($c=0; $c < count($tmp); $c++)
 {
  $x = explode("=",$tmp[$c]);
  $params[$x[0]] = $x[1];
 }
 /* EOF - ARRAYZE PARAMS */

 $_RUBAP = $params['rubap'] ? $params['rubap'] : "rubrica";
 $_AP = $params['ap'];
 $_CATID = $params['cat'];
 $_SUBJIDS = explode(",",$params['subjid']);
 $_PERCS = explode(",",$params['perc']);

 $out.= "Set new predefined discount by category...";
 $db = new AlpaDatabase(); 
 for($c=0; $c < count($_SUBJIDS); $c++)
 {
  $subjId = $_SUBJIDS[$c];
  $perc = $_PERCS[$c] ? $_PERCS[$c] : 0;
  if(!$subjId) continue;

  $db->RunQuery("SELECT id FROM dynarc_".$_RUBAP."_predefdiscount WHERE item_id='".$subjId."' AND ap='"
	.$_AP."' AND cat_id='".$_CATID."' LIMIT 1");
  if($db->Read())
  {
   $outArr[] = array('id'=>$db->record['id']);
   $db->RunQuery("UPDATE dynarc_".$_RUBAP."_predefdiscount SET percentage='".$perc."' WHERE id='".$db->record['id']."'");
  }
  else
  {
   $db->RunQuery("INSERT INTO dynarc_".$_RUBAP."_predefdiscount (item_id,ap,cat_id,percentage) VALUES('".$subjId."','"
	.$_AP."','".$_CATID."','".$perc."')");
   $outArr[] = array('id'=>$db->GetInsertId());
  }
  if($db->Error) return array('message'=>$out."failed!\nMySQL Error:".$db->Error, 'error'=>'MYSQL_ERROR');
 }
 $db->Close();
 $out.= "done!\n";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_edit($params, $sessid=0, $shellid=0)
{
 $out = "";
 $outArr = array();

 /* ARRAYZE PARAMS */
 $tmp = explode("&",$params);
 $params = array();
 for($c=0; $c < count($tmp); $c++)
 {
  $x = explode("=",$tmp[$c]);
  $params[$x[0]] = $x[1];
 }
 /* EOF - ARRAYZE PARAMS */
 
 $_RUBAP = $params['rubap'] ? $params['rubap'] : "rubrica";
 $_IDS = explode(",",$params['id']);
 $_SUBJIDS = explode(",",$params['subjid']);
 $_PERCS = explode(",",$params['perc']);

 $out.= "Update predefined discount by id...";
 $db = new AlpaDatabase(); 
 for($c=0; $c < count($_IDS); $c++)
 {
  $id = $_IDS[$c];
  $subjId = $_SUBJIDS[$c];
  $perc = $_PERCS[$c] ? $_PERCS[$c] : 0;

  $db->RunQuery("UPDATE dynarc_".$_RUBAP."_predefdiscount SET percentage='".$perc."'".($subjId ? ",item_id='".$subjId."'" : "")." WHERE id='".$id."'");
  if($db->Error) return array('message'=>$out."failed!\nMySQL Error:".$db->Error, 'error'=>'MYSQL_ERROR');
 }
 $db->Close();
 $out.= "done!\n";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_delete($params, $sessid=0, $shellid=0)
{
 $out = "";
 $outArr = array();

 /* ARRAYZE PARAMS */
 $tmp = explode("&",$params);
 $params = array();
 for($c=0; $c < count($tmp); $c++)
 {
  $x = explode("=",$tmp[$c]);
  $params[$x[0]] = $x[1];
 }
 /* EOF - ARRAYZE PARAMS */

 $_RUBAP = $params['rubap'] ? $params['rubap'] : "rubrica";
 $_IDS = explode(",",$params['id']);

 $out.= "Delete predefined discount by id...";
 $db = new AlpaDatabase(); 
 for($c=0; $c < count($_IDS); $c++)
 {
  $id = $_IDS[$c];
  $db->RunQuery("DELETE FROM dynarc_".$_RUBAP."_predefdiscount WHERE id='".$id."'");
  if($db->Error) return array('message'=>$out."failed!\nMySQL Error:".$db->Error, 'error'=>'MYSQL_ERROR');
 }
 $db->Close();
 $out.= "done!\n";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//


//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_export($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 if($isCategory)
  return ;

 $xml = "<predefdiscount />";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_import($sessid, $shellid, $archiveInfo, $itemInfo, $node, $isCategory=false)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_predefdiscount WHERE item_id='".$itemInfo['id']."'");
 $db->Close();
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $_FIELDS = "ap,cat_id,percentage";
 $list = array();
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT ".$_FIELDS." FROM dynarc_".$archiveInfo['prefix']."_predefdiscount WHERE item_id='".$srcInfo['id']."'");
 while($db->Read())
 {
  $list[] = $db->record;
 }
 for($c=0; $c < count($list); $c++)
 {
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_predefdiscount(item_id,ap,cat_id,percentage) VALUES('"
	.$cloneInfo['id']."','".$list[$c]['ap']."','".$list[$c]['cat_id']."','".$list[$c]['percentage']."')");
 }
 $db->Close();

 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_predefdiscount_onarchiveempty($args, $sessid, $shellid, $archiveInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("TRUNCATE TABLE dynarc_".$archiveInfo['prefix']."_predefdiscount");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
