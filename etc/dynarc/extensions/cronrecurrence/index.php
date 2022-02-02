<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-10-2016
 #PACKAGE: cron
 #DESCRIPTION: Cron recurrence extension for Dynarc
 #VERSION: 2.5beta
 #CHANGELOG: 24-10-2016 : MySQLi integration.
			 21-03-2014 : Auto retrieve time to by time length.
			 03-12-2012 : Completamento delle funzioni di base.
			 26-09-2011 : Function cronrecurrence_set update for enabled to edit recurrence.
			 25-09-2011 : Bug fix into cronrecurrence_get function
 #TODO:Rifare funzione import & export e completare funzioni syncimport & syncexport.
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_cronrecurrence` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `item_id` int(11) NOT NULL,
 `tag` varchar(64) NOT NULL,
 `name` varchar(80) NOT NULL,
 `notes` varchar(255) NOT NULL,
 `start_date` date NOT NULL,
 `end_date` date NOT NULL,
 `time_from` time NOT NULL,
 `time_to` time NOT NULL,
 `all_day` tinyint(1) NOT NULL,
 `interval_mode` tinyint(1) NOT NULL,
 `frequency` smallint(6) NOT NULL,
 `last_occurrence` date NOT NULL,
 `day_flag` smallint(1) NOT NULL,
 `day_num` tinyint(1) NOT NULL,
 `day_pos` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`,`tag`,`interval_mode`,`last_occurrence`)
 )");
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_cronrecurrence_exception` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
  `rec_id` int(11) NOT NULL,
  `date_from` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_from` (`date_from`),
  KEY `rec_id` (`rec_id`)
 )");
 $db->Close();

 return array("message"=>"cronrecurrence extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_cronrecurrence`");
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_cronrecurrence_exception`");
 $db->Close();

 return array("message"=>"cronrecurrence extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 /* REMOVE ALL ITEM EVENTS */
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_cronrecurrence WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_set($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'from' : {$from=$args[$c+1]; $c++;} break;
   case 'to' : {$to=$args[$c+1]; $c++;} break;
   case 'tl' : case 'timelength' : {$timeLength=$args[$c+1]; $c++;} break; /* optional - auto retrieve time to by time length */
   case 'allday' : case 'all_day' : {$allDay=$args[$c+1]; $c++;} break;
   case 'tag' : {$tag=$args[$c+1]; $c++;} break;
   case 'name' : {$name=$args[$c+1]; $c++;} break;
   case 'notes' : {$notes=$args[$c+1]; $c++;} break;
   case 'freq' : {$frequency=$args[$c+1]; $c++;} break;
   case 'imode' : {$intervalMode=$args[$c+1]; $c++;} break; /* 0=unrepeatable, 1=daily, 2=weekly, 3=monthly, 4=yearly */
   case 'dayflag' : {$dayFlag=$args[$c+1]; $c++;} break;
   case 'daynum' : {$dayNum=$args[$c+1]; $c++;} break;
   case 'daypos' : {$dayPos=$args[$c+1]; $c++;} break;
   case 'startdate' : {$startDate=$args[$c+1]; $c++;} break;
   case 'enddate' : {$endDate=$args[$c+1]; $c++;} break;
   case 'bypassgetnextoccurrence' : {$bypassGetNextOccurrence=$args[$c+1]; $c++;} break;
  }

 if($from)
 {
  /* PARSE FROM */ 
  if(strlen($from) < 10)
  {
   $dateFrom = date('Y-m-d');
   $timeFrom = $from;
  }
  else
  {
   $x = explode(" ",$from);
   if(count($x) == 2)
   {
    $dateFrom = $x[0];
    $timeFrom = $x[1];
   }
   else
   {
    $dateFrom = $from;
    $allDay = true;
   }
  }
 }

 if($to)
 {
  /* PARSE TO */
  if(strlen($to) < 10)
  {
   $dateTo = date('Y-m-d');
   $timeTo = $to;
  }
  else
  {
   $x = explode(" ",$to);
   if(count($x) == 2)
   {
    $dateTo = $x[0];
    $timeTo = $x[1];
   }
   else
    $dateTo = $to;
  }
 }
 else if($timeLength)
 {
  $tmpT = strtotime("+ ".$timeLength." minutes",strtotime($dateFrom." ".$timeFrom));
  $dateTo = date('Y-m-d',$tmpT);
  $timeTo = date('H:i',$tmpT);
 }

 if(!$startDate && !$id)
  $startDate = $dateFrom;
 

 if($allDay)
 {
  if(!$timeFrom)
   $timeFrom = "00:00:00";
  $timeTo = "23:59:59";
 }

 if(!$timeTo)
  $timeTo = "23:59:59";

 $db = new AlpaDatabase();
 if(!$id)
 {
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_cronrecurrence(item_id,tag,start_date,end_date,time_from,time_to,all_day,
interval_mode,frequency,last_occurrence,day_flag,day_num,day_pos,name,notes) VALUES('"
	.$itemInfo['id']."','".$tag."','".$startDate."','".$endDate."','".$timeFrom."','".$timeTo."','"
	.($allDay ? "1" : "0")."','".$intervalMode."','".$frequency."','".$lastOccurrence."','".$dayFlag."','".$dayNum."','".$dayPos."','"
	.($name ? $db->Purify($name) : $db->Purify($itemInfo['name']))."','".$db->Purify($notes)."')");
  $id = $db->GetInsertId();
 }
 else
 {
  $q = "";
  if(isset($startDate))
   $q.= ",start_date='$startDate'";
  if(isset($endDate))
   $q.= ",end_date='$endDate'";
  if(isset($timeFrom))
   $q.= ",time_from='$timeFrom'";
  if(isset($timeTo))
   $q.= ",time_to='$timeTo'";
  if(isset($allDay))
   $q.= ",all_day='$allDay'";
  if(isset($tag))
   $q.= ",tag='$tag'";
  if($name)
   $q.= ",name='".$db->Purify($name)."'";
  if(isset($notes))
   $q.= ",notes='".$db->Purify($notes)."'";
  if(isset($frequency))
   $q.= ",frequency='$frequency'";
  if(isset($intervalMode))
   $q.= ",interval_mode='$intervalMode'";
  if(isset($dayFlag))
   $q.= ",day_flag='$dayFlag'";
  if(isset($dayNum))
   $q.= ",day_num='$dayNum'";
  if(isset($dayPos))
   $q.= ",day_pos='$dayPos'";
   
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_cronrecurrence SET ".ltrim($q,',')." WHERE id='$id'");
 }
 $db->Close();

 // update next occurrence
 if(!$bypassGetNextOccurrence)
 {
  $ret = GShell("cron get-next-occurrence -ap '".$archiveInfo['prefix']."' -id '".$id."' --auto-update",$sessid,$shellid);
  $nextOccurrence = $ret['outarr']['date'];
 }

 $itemInfo['last_cronrecurrence'] = array('id'=>$id,'tag'=>$tag,'frequency'=>$frequency,
	'interval_mode'=>$intervalMode,'start_date'=>$startDate,'end_date'=>$endDate,'time_from'=>$timeFrom,
	'time_to'=>$timeTo,'all_day'=>$allDay,'day_flag'=>$dayFlag,'day_num'=>$dayNum,'day_pos'=>$dayPos,'name'=>$name,
	'notes'=>$notes, 'next_occurrence'=>$nextOccurrence);

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'all' : $all=true; break;
  }
 
 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_cronrecurrence WHERE id='$id'");
 else
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_cronrecurrence WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_get($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '*' : $all=true; break;
   case 'startdate' : $startDate=true; break;
   case 'enddate' : $endDate=true; break;
   case 'timefrom' : $from=true; break;
   case 'timeto' : $to=true; break;
   case 'tag' : $tag=true; break;
   case 'name' : $name=true; break;
   case 'notes' : $notes=true; break;
   case 'all_day' : $allDay=true; break;
   case 'imode' : $imode=true; break;
   case 'links' : $links=true; break;
  }

 if(!count($args))
  $all = true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_cronrecurrence WHERE item_id='".$itemInfo['id']."' ORDER BY start_date,time_from ASC");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id']);
  if($startDate || $all)
   $a['startdate'] = $db->record['start_date'];
  if($endDate || $all)
   $a['enddate'] = $db->record['end_date'];
  if($from || $all)
   $a['timefrom'] = $db->record['time_from'];
  if($to || $all)
   $a['timeto'] = $db->record['time_to'];
  if($tag || $all)
   $a['tag'] = $db->record['tag'];
  if($name || $all)
   $a['name'] = $db->record['name'];
  if($notes || $all)
   $a['notes'] = $db->record['notes'];
  if($allDay || $all)
   $a['allday'] = $db->record['all_day'];
  if($imode || $all)
  {
   $a['imode'] = $db->record['interval_mode'];
   $a['freq'] = $db->record['frequency'];
   $a['dayflag'] = $db->record['day_flag'];
   $a['daynum'] = $db->record['day_num'];
   $a['daypos'] = $db->record['day_pos'];
  }
  $a['next_occurrence'] = $db->record['last_occurrence'];
  /* LINKS */
  if($links)
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_links WHERE ref_id='".$itemInfo['id']."' AND ext_ref_name='cronrecurrence' AND ext_ref_id='".$db->record['id']."' ORDER BY ordering ASC");
   while($db2->Read())
   {
	if($db2->record['lnk_cat'])
     $ret = GShell("dynarc cat-info -aid '".$db2->record['lnk_arch']."' -id '".$db2->record['lnk_cat']."'",$sessid,$shellid);
    else if($db2->record['lnk_id'])
     $ret = GShell("dynarc item-info -aid '".$db2->record['lnk_arch']."' -id '".$db2->record['lnk_id']."'",$sessid,$shellid);
    else
     continue;
    if(!$ret['error'])
    {
     // Detect icon //
     if(file_exists($_BASE_PATH."share/icons/archives/".$db2->record['type'].".png"))
	  $ret['outarr']['icon'] = "share/icons/archives/".$db2->record['type'].".png";
     else
	  $ret['outarr']['icon'] = "share/icons/archives/other.png";
     $a['links'][] = array('id'=>$db2->record['id'],'type'=>$db2->record['type'],'info'=>$ret['outarr']);
	}
   }
   $db2->Close();
  }
  /* EOF - LINKS */
  $itemInfo['cronrecurrence'][] = $a;
 }
 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_list($params,$sessid,$shellid=0)
{
 
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_edit($params, $sessid, $shellid)
{
 
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_delete($params, $sessid, $shellid)
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
 $db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='cronrecurrence' LIMIT 1");
 if(!$db->Read())
  return array("message"=>"Extension cronrecurrence is not installed into archive ".$archiveInfo['name']."!\nYou can install cronrecurrence extension by typing: dynarc install-extension cronrecurrence -ap ".$archiveInfo['prefix']."\nRemember: Only the owner of archive (or root) can install/uninstall extensions!");
 $db->Close();

 /* CHECK FOR ITEM */
 if(!$params['id'])
  return array("message"=>"You must specify the id of recurrence record into params","error"=>"INVALID_ITEM_ID");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_cronrecurrence WHERE id='".$params['id']."'");
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
   return array("message"=>"Permission denied!, you have not permission to remove recurrence for this item.","error"=>"ITEM_PERMISSION_DENIED");
 }

 $eventId = $params['id'];
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_cronrecurrence WHERE id='$eventId'");
 $db->Close();

 $out.= "Recurrence has been removed!";
 $outArr = array('id'=>$eventId,'removed'=>true);
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_info($params, $sessid, $shellid)
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
 $db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='cronrecurrence' LIMIT 1");
 if(!$db->Read())
  return array("message"=>"Extension cronrecurrence is not installed into archive ".$archiveInfo['name']."!\nYou can install cronrecurrence extension by typing: dynarc install-extension cronrecurrence -ap ".$archiveInfo['prefix']."\nRemember: Only the owner of archive (or root) can install/uninstall extensions!");
 $db->Close();

 /* CHECK FOR ITEM */
 if(!$params['id'])
  return array("message"=>"You must specify the id of event record into params","error"=>"INVALID_ITEM_ID");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_cronrecurrence WHERE id='".$params['id']."'");
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
  if(!$ret['outarr']['modinfo']['can_read'])
   return array("message"=>"Permission denied!, you have not permission to read recurrence.","error"=>"ITEM_PERMISSION_DENIED");
 }

 $outArr = array('id'=>$a['id'],'tag'=>$a['tag'],'frequency'=>$a['frequency'],
	'interval_mode'=>$a['interval_mode'],'last_occurrence'=>$a['last_occurrence'],'start_date'=>$a['start_date'],'end_date'=>$a['end_date'],
	'time_from'=>$a['time_from'],'time_to'=>$a['time_to'],'all_day'=>$a['all_day'],'day_flag'=>$a['day_flag'],'day_num'=>$a['day_num'],
	'day_pos'=>$a['day_pos'],'name'=>$a['name'],'notes'=>$a['notes']);
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_export($sessid, $shellid, $archiveInfo, $itemInfo)
{
 $xml = "";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_import($sessid, $shellid, $archiveInfo, $itemInfo, $node)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_cronrecurrence_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

