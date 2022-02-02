<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-10-2016
 #PACKAGE: cron
 #DESCRIPTION: Cron events extension for Dynarc
 #VERSION: 2.5beta
 #CHANGELOG: 24-10-2016 : MySQLi integration.
			 21-03-2014 : Auto retrieve time to by time length.
			 29-11-2013 : Bug fix vari.
			 03-12-2012 : Completamento delle funzioni principali.
			 05-04-2012 : Bug fix in cronevents_set.
 #TODO:Rifare funzione import & export e completare funzioni syncimport & syncexport.
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_cronevents` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`item_id` INT( 11 ) NOT NULL ,
`datetime_from` DATETIME NOT NULL ,
`datetime_to` DATETIME NOT NULL ,
`tag` VARCHAR( 64 ) NOT NULL ,
`name` VARCHAR( 80 ) NOT NULL ,
`notes` VARCHAR( 255 ) NOT NULL ,
`all_day` TINYINT( 1 ) NOT NULL ,
INDEX ( `item_id` )
)");
 $db->Close();

 return array("message"=>"CronEvents extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_cronevents`");
 $db->Close();

 return array("message"=>"CronEvents extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 /* REMOVE ALL ITEM EVENTS */
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_cronevents WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_set($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'from' : {$from=$args[$c+1]; $c++;} break;
   case 'to' : {$to=$args[$c+1]; $c++;} break;
   case 'tl' : case 'timelength' : {$timeLength=$args[$c+1]; $c++;} break; /* optional - auto retrieve time to by time length */
   case 'tag' : {$tag=$args[$c+1]; $c++;} break;
   case 'name' : {$name=$args[$c+1]; $c++;} break;
   case 'notes' : {$notes=$args[$c+1]; $c++;} break;
   case 'all_day' : case 'allday' : {$allDay=$args[$c+1]; $c++;} break;
  }

 if(!$from && !$to)
  return array('message'=>"Cronevents error: You must specify from and to","error"=>"CRONEVENT_INVALID_ARGUMENTS");
 
 $db = new AlpaDatabase();
 if($id)
 {
  $q = "";
  if($from)
   $q.= ",datetime_from='".date('Y-m-d H:i',strtotime($from))."'";
  if($to)
   $q.= ",datetime_to='".date('Y-m-d H:i',strtotime($to))."'";
  else if($timeLength)
   $q.= ",datetime_to='".date('Y-m-d H:i',strtotime("+ ".$timeLength." minutes",strtotime($from)))."'";
  if(isset($tag))
   $q.= ",tag='".$tag."'";
  if($name)
   $q.= ",name='".$db->Purify($name)."'";
  if(isset($notes))
   $q.= ",notes='".$db->Purify($notes)."'";
  if(isset($allDay))
   $q.= ",all_day='".$allDay."'";

  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_cronevents SET ".ltrim($q,",")." WHERE id='".$id."'");
 }
 else
 {
  if(!$to && $timeLength)
   $to = date('Y-m-d H:i',strtotime("+ ".$timeLength." minutes",strtotime($from)));
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_cronevents(item_id,datetime_from,datetime_to,tag,name,notes,all_day) VALUES('"
	.$itemInfo['id']."','".date('Y-m-d H:i',strtotime($from))."','".date('Y-m-d H:i',strtotime($to))."','$tag','"
	.$db->Purify($name ? $name : $itemInfo['name'])."','".$db->Purify($notes)."','$allDay')");
  $id = $db->GetInsertId();
 }
 $db->Close();

 $itemInfo['last_cronevent'] = array('id'=>$id,'item_id'=>$itemInfo['id'],'archive'=>$archiveInfo['prefix'],'from'=>$from,'to'=>$to,'tag'=>$tag,'name'=>$name,'notes'=>$notes,'all_day'=>$allDay);

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'all' : $all=true; break;
  }
 
 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_cronevents WHERE id='$id'");
 else
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_cronevents WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_get($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'from' : $from=true; break;
   case 'to' : $to=true; break;
   case 'tag' : $tag=true; break;
   case 'name' : $name=true; break;
   case 'notes' : $notes=true; break;
   case 'all_day' : $allDay=true; break;
  }

 if(!count($args))
  $all = true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_cronevents WHERE item_id='".$itemInfo['id']."' ORDER BY datetime_from ASC");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id']);
  if($from || $all)
   $a['from'] = $db->record['datetime_from'];
  if($to || $all)
   $a['to'] = $db->record['datetime_to'];
  if($tag || $all)
   $a['tag'] = $db->record['tag'];
  if($name || $all)
   $a['name'] = $db->record['name'];
  if($notes || $all)
   $a['notes'] = $db->record['notes'];
  if($allDay || $all)
   $a['allday'] = $db->record['all_day'];
  $itemInfo['cronevents'][] = $a;
 }
 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_list($params,$sessid,$shellid=0)
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

 /* CHECK FOR ARCHIVE */
 if($params['ap'])
  $ret = GShell("dynarc archive-info -prefix '".$params['ap']."'",$sessid,$shellid);
 else if($params['aid'])
  $ret = GShell("dynarc archive-info -id '".$params['aid']."'",$sessid,$shellid);
 else
  return array("message"=>"You must specify the archive into arguments!","error"=>"INVALID_ARCHIVE");

 if($ret['error'])
  return $ret;
 $archiveInfo = $ret['outarr'];
 
 /* CHECK IF EXTENSION IS INSTALLED */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='cronevents' LIMIT 1");
 if(!$db->Read())
  return array("message"=>"Extension CronEvents is not installed into archive ".$archiveInfo['name']."!\nYou can install CronEvents extension by typing: dynarc install-extension cronevents -ap ".$archiveInfo['prefix']."\nRemember: Only the owner of archive (or root) can install/uninstall extensions!");
 $db->Close();

 $orderBy = $params['orderby'] ? $params['orderby'] : "datetime_from ASC";

 $q = "";
 if($params['from'])
  $q.= " AND datetime_from >= '".date('Y-m-d H:i',strtotime($params['from']))."'";
 if($params['to'])
  $q.= " AND datetime_from < '".date('Y-m-d H:i',strtotime($params['to']))."'";
 if($params['tag'])
  $q.= " AND tag='".$params['tag']."'";

 if($q == "")
  $q = "1";

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_cronevents WHERE ".ltrim($q," AND ")." ORDER BY $orderBy");
 while($db->Read())
  $outArr[] = array('id'=>$db->record['id'],'item_id'=>$db->record['item_id'],'from'=>$db->record['datetime_from'],'to'=>$db->record['datetime_to'],'tag'=>$db->record['tag'],'name'=>$db->record['name'],'allday'=>$db->record['all_day']);
 $db->Close();
 $out = count($outArr)." items found into archive ".$archiveInfo['name'];
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_edit($params, $sessid, $shellid)
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

 /* CHECK FOR ARCHIVE */
 if($params['ap'])
  $ret = GShell("dynarc archive-info -prefix '".$params['ap']."'",$sessid,$shellid);
 else if($params['aid'])
  $ret = GShell("dynarc archive-info -id '".$params['aid']."'",$sessid,$shellid);
 else
  return array("message"=>"You must specify the archive into arguments!","error"=>"INVALID_ARCHIVE");

 if($ret['error'])
  return $ret;
 $archiveInfo = $ret['outarr'];
 
 /* CHECK IF EXTENSION IS INSTALLED */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='cronevents' LIMIT 1");
 if(!$db->Read())
  return array("message"=>"Extension CronEvents is not installed into archive ".$archiveInfo['name']."!\nYou can install CronEvents extension by typing: dynarc install-extension cronevents -ap ".$archiveInfo['prefix']."\nRemember: Only the owner of archive (or root) can install/uninstall extensions!");
 $db->Close();

 /* CHECK FOR ITEM */
 if(!$params['id'])
  return array("message"=>"You must specify the id of event record into params","error"=>"INVALID_ITEM_ID");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_cronevents WHERE id='".$params['id']."'");
 $db->Read();
 $a = $db->record;
 $itemId = $db->record['item_id'];
 $db->Close();

 if($itemId)
 {
  $ret = GShell("dynarc item-info -ap '".$archiveInfo['prefix']."' -id '$itemId'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  /* CHECK FOR PERMISSIONS */
  if(!$ret['outarr']['modinfo']['can_write'])
   return array("message"=>"Permission denied!, you have not permission to edit event for this item.","error"=>"ITEM_PERMISSION_DENIED");
 }

 /* UPDATE EVENT */
 $q = "";
 $eventId = $params['id'];
 if(isset($params['from']))
  $q.= ",datetime_from='".date('Y-m-d H:i',strtotime($params['from']))."'";
 if(isset($params['to']))
  $q.= ",datetime_to='".date('Y-m-d H:i',strtotime($params['to']))."'";
 if(isset($params['tag']))
  $q.= ",tag='".$params['tag']."'";
 if(isset($params['name']))
  $q.= ",name='".urldecode($params['name'])."'";
 if(isset($params['notes']))
  $q.= ",notes='".urldecode($params['notes'])."'";
 if(isset($params['allday']))
  $q.= ",all_day='".$params['allday']."'";

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_cronevents SET ".ltrim($q,",")." WHERE id='$eventId'");
 $db->Close();

 $out.= "Event has been updated!";
 $outArr = array('id'=>$eventId,'archive'=>$params['ap'],'item_id'=>$itemId,'from'=>$params['from'],'to'=>$params['to'],'tag'=>$params['tag'],'name'=>$params['name'],'notes'=>$params['notes'],'allday'=>$params['allday']);
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_delete($params, $sessid, $shellid)
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

 /* CHECK FOR ARCHIVE */
 if($params['ap'])
  $ret = GShell("dynarc archive-info -prefix '".$params['ap']."'",$sessid,$shellid);
 else if($params['aid'])
  $ret = GShell("dynarc archive-info -id '".$params['aid']."'",$sessid,$shellid);
 else
  return array("message"=>"You must specify the archive into arguments!","error"=>"INVALID_ARCHIVE");

 if($ret['error'])
  return $ret;
 $archiveInfo = $ret['outarr'];
 
 /* CHECK IF EXTENSION IS INSTALLED */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='cronevents' LIMIT 1");
 if(!$db->Read())
  return array("message"=>"Extension CronEvents is not installed into archive ".$archiveInfo['name']."!\nYou can install CronEvents extension by typing: dynarc install-extension cronevents -ap ".$archiveInfo['prefix']."\nRemember: Only the owner of archive (or root) can install/uninstall extensions!");
 $db->Close();

 /* CHECK FOR ITEM */
 if(!$params['id'])
  return array("message"=>"You must specify the id of event record into params","error"=>"INVALID_ITEM_ID");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_cronevents WHERE id='".$params['id']."'");
 $db->Read();
 $itemId = $db->record['item_id'];
 $db->Close();

 if($itemId)
 {
  $ret = GShell("dynarc item-info -ap '".$archiveInfo['prefix']."' -id '$itemId'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  /* CHECK FOR PERMISSIONS */
  if(!$ret['outarr']['modinfo']['can_write'])
   return array("message"=>"Permission denied!, you have not permission to remove event for this item.","error"=>"ITEM_PERMISSION_DENIED");
 }

 $eventId = $params['id'];
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_cronevents WHERE id='$eventId'");
 $db->Close();

 $out.= "Event has been removed!";
 $outArr = array('id'=>$eventId,'removed'=>true);
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_info($params, $sessid, $shellid)
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

 /* CHECK FOR ARCHIVE */
 if($params['ap'])
  $ret = GShell("dynarc archive-info -prefix '".$params['ap']."'",$sessid,$shellid);
 else if($params['aid'])
  $ret = GShell("dynarc archive-info -id '".$params['aid']."'",$sessid,$shellid);
 else
  return array("message"=>"You must specify the archive into arguments!","error"=>"INVALID_ARCHIVE");

 if($ret['error'])
  return $ret;
 $archiveInfo = $ret['outarr'];
 
 /* CHECK IF EXTENSION IS INSTALLED */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='cronevents' LIMIT 1");
 if(!$db->Read())
  return array("message"=>"Extension CronEvents is not installed into archive ".$archiveInfo['name']."!\nYou can install CronEvents extension by typing: dynarc install-extension cronevents -ap ".$archiveInfo['prefix']."\nRemember: Only the owner of archive (or root) can install/uninstall extensions!");
 $db->Close();

 /* CHECK FOR ITEM */
 if(!$params['id'])
  return array("message"=>"You must specify the id of event record into params","error"=>"INVALID_ITEM_ID");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_cronevents WHERE id='".$params['id']."'");
 if(!$db->Read())
  return array("message"=>"Event #".$params['id']." does not exists into archive ".$archiveInfo['prefix'],"error"=>"EVENT_DOES_NOT_EXISTS");
 $a = $db->record;
 $itemId = $db->record['item_id'];
 $db->Close();

 if($itemId)
 {
  $ret = GShell("dynarc item-info -ap '".$archiveInfo['prefix']."' -id '$itemId'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  /* CHECK FOR PERMISSIONS */
  if(!$ret['outarr']['modinfo']['can_read'])
   return array("message"=>"Permission denied!, you have not permission to read event.","error"=>"ITEM_PERMISSION_DENIED");
 }

 $outArr = array('id'=>$a['id'],'item_id'=>$itemId,'from'=>$a['datetime_from'],'to'=>$a['datetime_to'],'tag'=>$a['tag'],'name'=>$a['name'],'notes'=>$a['notes'],'allday'=>$a['all_day']);
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_export($sessid, $shellid, $archiveInfo, $itemInfo)
{
 $xml = "";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_import($sessid, $shellid, $archiveInfo, $itemInfo, $node)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronevents_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

