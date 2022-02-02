<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Request
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-08-2014
 #PACKAGE: scheduledtasks
 #DESCRIPTION: Gnujiko scheduled tasks
 #VERSION: 2.0beta
 #CHANGELOG: 
 #DEPENDS: 
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

function shell_scheduledtasks($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'add' : case 'new' : return scheduledtasks_add($args, $sessid, $shellid); break;
  case 'edit' : return scheduledtasks_edit($args, $sessid, $shellid); break;
  case 'delete' : return scheduledtasks_delete($args, $sessid, $shellid); break;

  case 'info' : return scheduledtasks_info($args, $sessid, $shellid); break;
  case 'list' : return scheduledtasks_list($args, $sessid, $shellid); break;

  case 'execute' : case 'exec' : return scheduledtasks_execute($args, $sessid, $shellid); break;
  case 'check' : return scheduledtasks_check($args, $sessid, $shellid); break;

  default : return scheduledtasks_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function scheduledtasks_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function scheduledtasks_add($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $_AP = "scheduledtasks";
 $status = 1;
 $freq = 1;
 $imode = 4;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-type' : case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
   case '-title' : case '-name' : {$title=$args[$c+1]; $c++;} break;
   case '-desc' : case '-description' : {$description=$args[$c+1]; $c++;} break;
   case '-alias' : case '-aliasname' : {$aliasName=$args[$c+1]; $c++;} break;
   case '-status' : {$status=$args[$c+1]; $c++;} break;

   case '-freq' : {$freq=$args[$c+1]; $c++;} break;
   case '-imode' : {$imode=$args[$c+1]; $c++;} break;
   case '-dayflag' : {$dayFlag=$args[$c+1]; $c++;} break;
   case '-daynum' : {$dayNum=$args[$c+1]; $c++;} break;
   case '-daypos' : {$dayPos=$args[$c+1]; $c++;} break;

   case '-xmlparams' : {$xmlParams=$args[$c+1]; $c++;} break;
   case '-shellcommand' : case '-command' : {$shellCommand=$args[$c+1]; $c++;} break;
   case '-postcommand' : {$postCommand=$args[$c+1]; $c++;} break;
   case '-executerfile' : case '-exefile' : {$executerFile=$args[$c+1]; $c++;} break;
   case '-executername' : case '-exename' : {$executerName=$args[$c+1]; $c++;} break;
   case '-executeraction' : case '-exeaction' : {$executerAction=$args[$c+1]; $c++;} break;

   case '-from' : case '-datefrom' : case '-start' : {$startDate=$args[$c+1]; $c++;} break;
   case '-to' : case '-dateto' : case '-end' : case '-finish' : {$endDate=$args[$c+1]; $c++;} break;
  }

 if(!$catTag)
  return array("message"=>"Error: You must specify the event type. (with: -type TYPE)", "error"=>"INVALID_TYPE");

 $ret = GShell("dynarc cat-info -ap '".$_AP."' -tag '".$catTag."'",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 $catInfo = $ret['outarr'];

 if(!$title)
  $title = $catInfo['name'];
 if(!$startDate)
  $startDate = date('Y-m-d H:i',time());

 $extset = "scheduledtask.status='".$status."',xmlparams='''".$xmlParams."''',shellcommand='''".$shellCommand."''',postcommand='''".$postCommand."''',exefile='".$executerFile."',exename='".$executerName."',exeaction='".$executerAction."'";
 $extset.= ",cronrecurrence.from='".$startDate."',tag='".$catTag."',name='''".$title."''',allday='1',freq='".$freq."',imode='".$imode."'";
 if(isset($dayFlag))			$extset.= ",dayflag='".$dayFlag."'";
 if(isset($dayNum))				$extset.= ",daynum='".$dayNum."'";
 if(isset($dayPos))				$extset.= ",daypos='".$dayPos."'";
 if(isset($endDate))			$extset.= ",enddate='".$endDate."'";

 $ret = GShell("dynarc new-item -ap '".$_AP."' -cat '".$catInfo['id']."' -name `".$title."` -alias `".$aliasName."` -desc `".$description."` -extset `".$extset."`",$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $itemInfo = $ret['outarr'];
 $itemInfo['repeatable'] = $itemInfo['last_cronrecurrence'] ? true : false;
 if($itemInfo['last_cronrecurrence'])
 {
  $recInfo = $itemInfo['last_cronrecurrence'];
  $itemInfo['recurrence_id'] = $recInfo['id'];
  $itemInfo['imode'] = $recInfo['interval_mode'];
  $itemInfo['freq'] = $recInfo['frequency'];
  $itemInfo['dayflag'] = $recInfo['day_flag'];
  $itemInfo['daynum'] = $recInfo['day_num'];
  $itemInfo['daypos'] = $recInfo['day_pos'];
  $itemInfo['startdate'] = $recInfo['start_date'];
  $itemInfo['enddate'] = $recInfo['end_date'];
  $itemInfo['allday'] = $recInfo['all_day'];
  $itemInfo['timefrom'] = $recInfo['time_from'];
  $itemInfo['timeto'] = $recInfo['time_to'];
  $itemInfo['next_occurrence'] = $recInfo['next_occurrence'];
 }
 else if($itemInfo['last_cronevent'])
 {
  $recInfo = $itemInfo['last_cronevent'];
  $itemInfo['event_id'] = $recInfo['id'];
  $itemInfo['imode'] = 0;
  $itemInfo['freq'] = 1;
  $itemInfo['dayflag'] = 0;
  $itemInfo['daynum'] = 0;
  $itemInfo['daypos'] = 0;
  $itemInfo['startdate'] = date('Y-m-d',strtotime($recInfo['from']));
  $itemInfo['enddate'] = date('Y-m-d',strtotime($recInfo['to']));
  $itemInfo['allday'] = $recInfo['all_day'];
  $itemInfo['timefrom'] = date('H:i',strtotime($recInfo['from']));
  $itemInfo['timeto'] = date('H:i',strtotime($recInfo['to']));
 }

 if($itemInfo['next_occurrence'] && ($itemInfo['next_occurrence'] != "0000-00-00") && ($itemInfo['next_occurrence'] != "1970-01-01"))
 {
  // update next_occurrence
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_".$_AP."_items SET next_occurrence='".$itemInfo['next_occurrence']."' WHERE id='".$itemInfo['id']."'");
  $db->Close();
 }

 $out.= $ret['message'];
 $outArr = $itemInfo;

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function scheduledtasks_edit($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $_AP = "scheduledtasks";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-title' : case '-name' : {$title=$args[$c+1]; $c++;} break;
   case '-alias' : case '-aliasname' : {$aliasName=$args[$c+1]; $c++;} break;
   case '-desc' : case '-description' : {$description=$args[$c+1]; $c++;} break;
   case '-status' : {$status=$args[$c+1]; $c++;} break;
   case '-xmlparams' : {$xmlParams=$args[$c+1]; $c++;} break;
   case '-shcmd' : case '-shellcommand' : case 'command' : {$shellCommand=$args[$c+1]; $c++;} break;
   case '-postcommand' : {$postCommand=$args[$c+1]; $c++;} break;
   case '-executerfile' : case '-exefile' : {$executerFile=$args[$c+1]; $c++;} break;
   case '-executername' : case '-exename' : {$executerName=$args[$c+1]; $c++;} break;
   case '-executeraction' : case '-exeaction' : {$executerAction=$args[$c+1]; $c++;} break;

   case '-imode' : case '-interval-mode' : {$imode=$args[$c+1]; $c++;} break;
   case '-freq' : case '-frequency' : {$freq=$args[$c+1]; $c++;} break;
   case '-dayflag' : {$dayFlag=$args[$c+1]; $c++;} break;
   case '-daynum' : {$dayNum=$args[$c+1]; $c++;} break;
   case '-daypos' : {$dayPos=$args[$c+1]; $c++;} break;
   case '-startdate' : {$startDate=$args[$c+1]; $c++;} break;
   case '-enddate' : {$endDate=$args[$c+1]; $c++;} break;
   case '-allday' : {$allDay=$args[$c+1]; $c++;} break;
   case '-from' : {$dtFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dtTo=$args[$c+1]; $c++;} break;
  }

 if(!$id && $aliasName)
  $ret = GShell("scheduledtasks info -alias '".$aliasName."'",$sessid,$shellid);
 else
  $ret = GShell("scheduledtasks info -id '".$id."'",$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $itemInfo = $ret['outarr'];
 if(!$id)
  $id = $itemInfo['id'];

 $isRecurrence = $itemInfo['repeatable'];
 $cronQry = "";

 /* UPDATE ITEM INFO */
 $db = new AlpaDatabase();
 $qry = "";
 if($title)
 {
  $qry.= ",name='".$db->Purify($title)."'";
  $cronQry.= " -name `".$title."`";
  $itemInfo['name'] = $title;
 }
 if(isset($aliasName))		{$qry.= ",aliasname='".$db->Purify($aliasName)."'";					$itemInfo['aliasname'] = $aliasName;}
 if(isset($description))	{$qry.= ",description='".$db->Purify($description)."'";				$itemInfo['desc'] = $description;}
 if(isset($status))			{$qry.= ",status='".$status."'";									$itemInfo['status'] = $status;}
 if(isset($xmlParams))		{$qry.= ",xmlparams='".$db->Purify($xmlParams)."'";					$itemInfo['xmlparams'] = $xmlParams;}
 if(isset($shellCommand))	{$qry.= ",shellcommand='".$db->Purify($shellCommand)."'";			$itemInfo['shellcommand'] = $shellCommand;}
 if(isset($postCommand))	{$qry.= ",postcommand='".$db->Purify($postCommand)."'";				$itemInfo['postcommand'] = $postCommand;}
 if(isset($executerFile))	{$qry.= ",executer_file='".$db->Purify($executerFile)."'";			$itemInfo['executer_file'] = $executeFile;}
 if(isset($executerName))	{$qry.= ",executer_name='".$db->Purify($executerName)."'";			$itemInfo['executer_name'] = $executeName;}
 if(isset($executerAction))	{$qry.= ",executer_action='".$db->Purify($executerAction)."'";		$itemInfo['executer_action'] = $executeAction;}

 if($qry)
  $db->RunQuery("UPDATE dynarc_".$_AP."_items SET ".ltrim($qry,",")." WHERE id='".$id."'");
 $db->Close();

 /* UPDATE EVENT INFO */
 if(isset($imode))			{$cronQry = " -imode '".$imode."'";					$itemInfo['imode'] = $imode;}
 if(isset($freq))			{$cronQry.= " -freq '".$freq."'";					$itemInfo['freq'] = $freq;}
 if(isset($dayFlag))		{$cronQry.= " -dayflag '".$dayFlag."'";				$itemInfo['dayflag'] = $dayFlag;}
 if(isset($dayNum))			{$cronQry.= " -daynum '".$dayNum."'";				$itemInfo['daynum'] = $dayNum;}
 if(isset($dayPos))			{$cronQry.= " -daypos '".$dayPos."'";				$itemInfo['daypos'] = $dayPos;}
 if(isset($startDate))		{$cronQry.= " -start '".$startDate."'";				$itemInfo['startdate'] = $startDate;}
 if(isset($endDate))		{$cronQry.= " -end '".$endDate."'";					$itemInfo['enddate'] = $endDate;}
 if(isset($dtFrom))			{$cronQry.= " -from '".$dtFrom."'";					$itemInfo['timefrom'] = $dtFrom;}
 if(isset($dtTo))			{$cronQry.= " -to '".$dtTo."'";						$itemInfo['timeto'] = $dtTo;}
 if(isset($allDay))			{$cronQry.= " -allday '".$allDay."'";				$itemInfo['allday'] = $allDay;}

 if($isRecurrence)
 {
  if(isset($imode) && !$imode)
   $ret = GShell("cron recurrence2event -ap '".$_AP."' -id '".$itemInfo['recurrence_id']."'".$cronQry,$sessid,$shellid);
  else
   $ret = GShell("cron edit-recurrence -ap '".$_AP."' -id '".$itemInfo['recurrence_id']."'".$cronQry,$sessid,$shellid);
 }
 else
 {
  if($imode)
   $ret = GShell("cron event2recurrence -ap '".$_AP."' -id '".$itemInfo['event_id']."'".$cronQry,$sessid,$shellid);
  else
   $ret = GShell("cron edit-event -ap '".$_AP."' -id '".$itemInfo['event_id']."'".$cronQry,$sessid,$shellid);
 }
 if($ret['error'])
  return $ret;

 if($ret['outarr']['next_occurrence'])
 {
  // update next occurrence
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_".$_AP."_items SET next_occurrence='".$ret['outarr']['next_occurrence']."' WHERE id='".$id."'");
  $db->Close();
  $itemInfo['next_occurrence'] = $ret['outarr']['next_occurrence'];
 }

 $out.= "done! This scheduled task has been updated!";
 $outArr = $itemInfo;

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function scheduledtasks_delete($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $_AP = "scheduledtasks";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '--subsequent' : case '-subsequent' : $subsequent=true; break;
   case '-r' : $forever=true; break;
  }

 if(!$id)
  return array("message"=>"You must specify the task id. (with: -id EVENT_ID)", "error"=>"INVALID_ID");

 $ret = GShell("dynarc item-info -ap '".$_AP."' -id '".$id."' -get 'status,last_exec_datetime,next_occurrence' -extget 'cronevents,cronrecurrence'",$sessid, $shellid);
 if($ret['error'])
  return $ret;

 $item = $ret['outarr'];

 if($subsequent && $dateFrom)
 {
  $ret = GShell("cron delete-recurrence -ap '".$_AP."' -id '".$item['recurrence_id']."' -from '".$dateFrom."' -subsequent",$sessid,$shellid);
 }
 else if($dateFrom)
 {
  $ret = GShell("cron delete-recurrence -ap '".$_AP."' -id '".$item['recurrence_id']."' -from '".$dateFrom."'",$sessid,$shellid);
 }
 else
 {
  $ret = GShell("cron delete-recurrence -ap '".$_AP."' -id '".$item['recurrence_id']."' -all",$sessid,$shellid);
  $ret = GShell("dynarc delete-item -ap '".$_AP."' -id '".$id."'".($forever ? " -r" : ""),$sessid,$shellid);
  return $ret;
 }

 if($ret['outarr']['next_occurrence'])
 {
  // update next occurrence
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_".$_AP."_items SET next_occurrence='".$ret['outarr']['next_occurrence']."' WHERE id='".$id."'");
  $db->Close();
 }
 
 $out.= "done!";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function scheduledtasks_info($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $_AP = "scheduledtasks";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-alias' : case '-aliasname' : {$aliasName=$args[$c+1]; $c++;} break;
   case '--verbose' : case '-verbose' : $verbose=true; break;
  }

 if(!$id && !$aliasName)
  return array("message"=>"You must specify the task id. (with: -id EVENT_ID)", "error"=>"INVALID_ID");

 $ret = GShell("dynarc item-info -ap '".$_AP."'".($aliasName ? " -alias `".$aliasName."`" : " -id '".$id."'")." -extget 'scheduledtask,cronevents,cronrecurrence'",$sessid, $shellid);
 if($ret['error'])
  return $ret;

 $item = $ret['outarr'];

 $item['repeatable'] = count($item['cronrecurrence']) ? true : false;
 if(count($item['cronrecurrence']))
 {
  $recInfo = $item['cronrecurrence'][0]; 
  $item['recurrence_id'] = $recInfo['id'];
  $item['imode'] = $recInfo['imode'];
  $item['freq'] = $recInfo['freq'];
  $item['dayflag'] = $recInfo['dayflag'];
  $item['daynum'] = $recInfo['daynum'];
  $item['daypos'] = $recInfo['daypos'];
  $item['startdate'] = $recInfo['startdate'];
  $item['enddate'] = $recInfo['enddate'];
  $item['allday'] = $recInfo['allday'];
  $item['timefrom'] = $recInfo['timefrom'];
  $item['timeto'] = $recInfo['timeto'];
  $item['next_occurrence'] = ($recInfo['next_occurrence'] != "0000-00-00") ? $recInfo['next_occurrence'] : "";
 }
 else if(count($item['cronevents']))
 {
  $eventInfo = $item['cronevents'][0];
  $item['event_id'] = $eventInfo['id'];
  $item['imode'] = 0;
  $item['freq'] = 1;
  $item['dayflag'] = 0;
  $item['daynum'] = 0;
  $item['daypos'] = 0;
  $item['startdate'] = date('Y-m-d',strtotime($eventInfo['from']));
  $item['enddate'] = date('Y-m-d',strtotime($eventInfo['to']));
  $item['allday'] = $eventInfo['allday'];
  $item['timefrom'] = date('H:i',strtotime($eventInfo['from']));
  $item['timeto'] = date('H:i',strtotime($eventInfo['to']));
 }

 if($verbose)
 {
  $out.= "ID: ".$item['id']."\n";
  $out.= "Name: ".$item['name']."\n";
  $out.= "Alias: ".$item['aliasname']."\n";
  $out.= "Description: ".$item['desc']."\n";
  $out.= "Status: ".(!$item['status'] ? "off" : "active")."\n";
  $out.= "Next occurrence: ".$item['next_occurrence']."\n";
  $out.= "Executer file: ".$item['executer_file']."\n";
  $out.= "Executer name: ".$item['executer_name']."\n";
  $out.= "Executer action: ".$item['executer_action']."\n";
 }

 $outArr = $item;

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function scheduledtasks_list($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array("count"=>0, "items"=>array());

 $_AP = "scheduledtasks";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-type' : case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;

   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
  }

 $ret = GShell("dynarc item-list -ap '".$_AP."'".($catTag ? " -ct '".$catTag."'" : " --all-cat")." --order-by '".$orderBy."'"
	.($limit ? " -limit '".$limit."'" : "")." -extget 'scheduledtask,cronevents,cronrecurrence'", $sessid, $shellid);
 if($ret['error'])
  return $ret;

 $list = $ret['outarr']['items'];
 $count = $ret['outarr']['count'];

 $outArr['count'] = $count;

 for($c=0; $c < count($list); $c++)
 {
  $item = $list[$c];
  $item['repeatable'] = count($item['cronrecurrence']) ? true : false;
  if(count($item['cronrecurrence']))
  {
   $recInfo = $item['cronrecurrence'][0]; 
   $item['recurrence_id'] = $recInfo['id'];
   $item['imode'] = $recInfo['imode'];
   $item['freq'] = $recInfo['freq'];
   $item['dayflag'] = $recInfo['dayflag'];
   $item['daynum'] = $recInfo['daynum'];
   $item['daypos'] = $recInfo['daypos'];
   $item['startdate'] = $recInfo['startdate'];
   $item['enddate'] = $recInfo['enddate'];
   $item['allday'] = $recInfo['allday'];
   $item['timefrom'] = $recInfo['timefrom'];
   $item['timeto'] = $recInfo['timeto'];
  }
  else if(count($item['cronevents']))
  {
   $eventInfo = $item['cronevents'][0];
   $item['event_id'] = $eventInfo['id'];
   $item['imode'] = 0;
   $item['freq'] = 1;
   $item['dayflag'] = 0;
   $item['daynum'] = 0;
   $item['daypos'] = 0;
   $item['startdate'] = date('Y-m-d',strtotime($eventInfo['from']));
   $item['enddate'] = date('Y-m-d',strtotime($eventInfo['to']));
   $item['allday'] = $eventInfo['allday'];
   $item['timefrom'] = date('H:i',strtotime($eventInfo['from']));
   $item['timeto'] = date('H:i',strtotime($eventInfo['to']));
  }
  $outArr['items'][] = $item;
 }

 $out.= "done!";
 
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function scheduledtasks_execute($args, $sessid, $shellid)
{
 global $_BASE_PATH;

 $out = "";
 $outArr = array();

 $_AP = "scheduledtasks";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
  }

 if(!$id)
  return array("message"=>"You must specify the task id. (with: -id TASK_ID)", "error"=>"INVALID_ID");

 $ret = GShell("dynarc item-info -ap '".$_AP."' -id '".$id."' -extget 'scheduledtask,cronevents,cronrecurrence'",$sessid, $shellid);
 if($ret['error'])
  return $ret;

 $item = $ret['outarr'];

 $item['repeatable'] = count($item['cronrecurrence']) ? true : false;
 if(count($item['cronrecurrence']))
 {
  $recInfo = $item['cronrecurrence'][0]; 
  $item['recurrence_id'] = $recInfo['id'];
  $item['imode'] = $recInfo['imode'];
  $item['freq'] = $recInfo['freq'];
  $item['dayflag'] = $recInfo['dayflag'];
  $item['daynum'] = $recInfo['daynum'];
  $item['daypos'] = $recInfo['daypos'];
  $item['startdate'] = $recInfo['startdate'];
  $item['enddate'] = $recInfo['enddate'];
  $item['allday'] = $recInfo['allday'];
  $item['timefrom'] = $recInfo['timefrom'];
  $item['timeto'] = $recInfo['timeto'];
 }
 else if(count($item['cronevents']))
 {
  $eventInfo = $item['cronevents'][0];
  $item['event_id'] = $eventInfo['id'];
  $item['imode'] = 0;
  $item['freq'] = 1;
  $item['dayflag'] = 0;
  $item['daynum'] = 0;
  $item['daypos'] = 0;
  $item['startdate'] = date('Y-m-d',strtotime($eventInfo['from']));
  $item['enddate'] = date('Y-m-d',strtotime($eventInfo['to']));
  $item['allday'] = $eventInfo['allday'];
  $item['timefrom'] = date('H:i',strtotime($eventInfo['from']));
  $item['timeto'] = date('H:i',strtotime($eventInfo['to']));
 }

 /* EXECUTE */
 if($item['executer_file'] && $item['executer_name'] && $item['executer_action'])
 {
  if(!file_exists($_BASE_PATH.$item['executer_file']))
   return array("message"=>"Error: Unable to execute task. Executer file '".$item['executer_file']."' missing.", "error"=>"INVALID_EXECUTER_FILE");
  include_once($_BASE_PATH.$item['executer_file']);
  if(is_callable("scheduledtask_".$item['executer_name']."_".$item['executer_action'],true))
  {
   $ret = call_user_func("scheduledtask_".$item['executer_name']."_".$item['executer_action'], $sessid, $shellid);
   if(is_array($ret) && $ret['error']) return $ret;
   else if(is_array($ret) && $ret['outarr']) $outArr = $ret['outarr'];
   else if(is_array($ret)) $outArr = $ret;
   if(is_array($ret) && $ret['message']) $out.= $ret['message'];
  }
 }
 else if($item['shellcommand'])
 {
  $ret = GShell($item['shellcommand'], $sessid, $shellid);
  if($ret['error']) return $ret;
  $out.= $ret['message'];
  $outArr = $ret['outarr'];
 }
 else
  $out.= "No executers found";
 if($item['postcommand'])
 {
  $ret = GShell($item['postcommand'], $sessid, $shellid);
  if($ret['error']) return $ret;
  $out.= $ret['message'];
  $outArr = $ret['outarr'];
 }

 // update last exec datetime
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$_AP."_items SET last_exec_datetime='".date('Y-m-d H:i:s')."' WHERE id='".$item['id']."'");
 $db->Close();

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function scheduledtasks_check($args, $sessid, $shellid)
{
 global $_BASE_PATH;

 $out = "";
 $outArr = array();

 $_AP = "scheduledtasks";

 $ret = GShell("cron list -ap '".$_AP."' -today",$sessid,$shellid);
 $list = $ret['outarr'];
 
 for($c=0; $c < count($list); $c++)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT status FROM dynarc_".$_AP."_items WHERE id='".$list[$c]['item_id']."'");
  $db->Read();
  if($db->record['status'] == 1)
  {
   $ret = GShell("scheduledtasks execute -id '".$list[$c]['item_id']."'",$sessid,$shellid);
   if($ret['error'])
	$out.= "Errors during execution of task #".$list[$c]['item_id'].".\n".$ret['message'];
  }
  $db->Close();
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
?>




