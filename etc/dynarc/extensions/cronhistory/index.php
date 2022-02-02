<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-10-2016
 #PACKAGE: cron
 #DESCRIPTION: Cron history extension for Dynarc
 #VERSION: 2.1beta
 #CHANGELOG: 24-10-2016 : MySQLi integration.
 #TODO: 
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_cronhistory` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`item_id` INT( 11 ) NOT NULL ,
`datetime` DATETIME NOT NULL ,
`tag` VARCHAR( 64 ) NOT NULL ,
`name` VARCHAR( 80 ) NOT NULL ,
`notes` VARCHAR( 255 ) NOT NULL ,
INDEX ( `item_id` )
)");
 $db->Close();

 return array("message"=>"CronHistory extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_cronhistory`");
 $db->Close();

 return array("message"=>"CronHistory extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 /* REMOVE ALL ITEM EVENTS */
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_cronhistory WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_set($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'datetime' : {$dateTime=$args[$c+1]; $c++;} break;
   case 'tag' : {$tag=$args[$c+1]; $c++;} break;
   case 'name' : {$name=$args[$c+1]; $c++;} break;
   case 'notes' : {$notes=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 if($id)
 {
  $q = "";
  if($dateTime)
   $q.= ",datetime='".$dateTime."'";
  if(isset($tag))
   $q.= ",tag='".$tag."'";
  if($name)
   $q.= ",name='".$db->Purify($name)."'";
  if(isset($notes))
   $q.= ",notes='".$db->Purify($notes)."'";

  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_cronhistory SET ".ltrim($q,",")." WHERE id='".$id."'");
 }
 else
 {
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_cronhistory(item_id,datetime,tag,name,notes) VALUES('"
	.$itemInfo['id']."','".$dateTime."','$tag','".$db->Purify($name)."','".$db->Purify($notes)."')");
  $id = $db->GetInsertId();
 }
 $db->Close();

 $itemInfo['last_cronhistory'] = array('id'=>$id,'item_id'=>$itemInfo['id'],'archive'=>$archiveInfo['prefix'],'datetime'=>$dateTime,'tag'=>$tag,'name'=>$name,'notes'=>$notes);

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'all' : $all=true; break;
  }
 
 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_cronhistory WHERE id='$id'");
 else
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_cronhistory WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_get($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_cronhistory WHERE item_id='".$itemInfo['id']."' ORDER BY datetime ASC");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id']);
  $a['datetime'] = $db->record['datetime'];
  $a['tag'] = $db->record['tag'];
  $a['name'] = $db->record['name'];
  $a['notes'] = $db->record['notes'];
  $itemInfo['cronhistory'][] = $a;
 }
 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_export($sessid, $shellid, $archiveInfo, $itemInfo)
{
 $xml = "";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_import($sessid, $shellid, $archiveInfo, $itemInfo, $node)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronhistory_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

