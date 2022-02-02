<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-10-2016
 #PACKAGE: cron
 #DESCRIPTION: Gnujiko calendars and schedule events
 #VERSION: 2.6beta
 #CHANGELOG: 24-10-2016 : MySQLi integration.
			 06-09-2014 : Aggiunto blacklist su cron list.
			 16-04-2014 : Aggiunto parametro --update-doc-date su funzione cron_editEvent
			 22-03-2014 : Aggiornato funzioni event2recurrence e recurrence2event
			 19-01-2014 : Aggiunta funzione get-free-time
			 05-12-2013 : Aggiunto opzione --include-working-time nella funzione cron list
			 27-09-2011 : Bug fix into function cron_getReccurence
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

function shell_cron($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'list' : return cron_list($args, $sessid, $shellid); break;
  case 'get-next-occurrence' : return cron_getNextOccurrence($args, $sessid, $shellid); break;
  case 'get-free-time' : case 'freetime' : return cron_getFreeTime($args, $sessid, $shellid); break;

  case 'event-info' : return cron_eventInfo($args, $sessid, $shellid); break;
  case 'edit-event' : return cron_editEvent($args, $sessid, $shellid); break;
  case 'delete-event' : return cron_deleteEvent($args, $sessid, $shellid); break;
  case 'event2recurrence' : return cron_eventToRecurrence($args, $sessid, $shellid); break;
  case 'edit-recurrence' : return cron_editRecurrence($args, $sessid, $shellid); break;
  case 'recurrence-info' : return cron_recurrenceInfo($args, $sessid, $shellid); break;
  case 'delete-recurrence' : return cron_deleteRecurrence($args, $sessid, $shellid); break;
  case 'recurrence2event' : return cron_recurrenceToEvent($args, $sessid, $shellid); break;

  default : return cron_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function cron_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function cron_list($args, $sessid=0, $shellid=0)
{
 $out = "";
 $outArr = array();
 $blacklist = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$from=$args[$c+1]; $c++;} break;
   case '-to' : {$to=$args[$c+1]; $c++;} break;
   case '-today' : $today=true; break;
   case '-tag' : {$tag=$args[$c+1]; $c++;} break;
   
   case '-archive' : case '-ap' : {$archive=$args[$c+1]; $c++;} break;
   case '--divide-by-archive' : $divideByArchive=true; break;
   case '--include-working-time' : $includeWorkingTime=true; break;
   case '-except' : {$except=$args[$c+1]; $c++;} break;
   case '-get' : {$get=$args[$c+1]; $c++;} break;
  }
 
 if($today)
 {
  $from = date('Y-m-d')." 00:00";
  $to = date('Y-m-d H:i',strtotime("+1 day",strtotime($from)));
 }

 $from = $from ? strtotime($from) : time();
 $to = $to ? strtotime($to) : strtotime("+1 week",$from);

 $out = "List of events from ".date('Y-m-d H:i',$from)." to ".date('Y-m-d H:i',$to)."\n";

 // detect archives
 if($archive)
  $archives = explode(",",$archive);
 else
 {
  $archives = array();
  if($except)
   $blacklist = explode(",",$except);
  $db = new AlpaDatabase();
  $db2 = new AlpaDatabase();
  $db->RunQuery("SELECT archive_id FROM dynarc_archive_extensions WHERE (extension_name='cronevents') OR (extension_name='cronrecurrence')");
  while($db->Read())
  {
   $db2->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE id='".$db->record['archive_id']."'");
   $db2->Read();
   if(in_array($db2->record['tb_prefix'], $blacklist))
	continue;
   if(!in_array($db2->record['tb_prefix'],$archives))
    $archives[] = $db2->record['tb_prefix'];
  }
  $db2->Close();
  $db->Close();
 }

 if($includeWorkingTime && (!in_array("working_time",$archives)))
 {
  /* first: get if archive working_time exists */
  $ret = GShell("dynarc archive-info -ap 'working_time'",$sessid,$shellid);
  if(!$ret['error'])
   $archives[] = "working_time";
 }

 // Get events and recurrence//
 $q = "";
 if($tag)
  $q.= " AND tag='$tag'";
 $eventList = array();

 if($get)
  $get = explode(",",$get);

 for($c=0; $c < count($archives); $c++)
 {
  /* GET EVENTS */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM dynarc_".$archives[$c]."_cronevents WHERE datetime_from >= '"
	.date('Y-m-d H:i',$from)."' AND datetime_from < '".date('Y-m-d H:i',$to)."'$q ORDER BY datetime_from ASC");
  while($db->Read())
  {
   $a = array('id'=>$db->record['id'], 'archive'=>$archives[$c],'item_id'=>$db->record['item_id'], 'from'=>strtotime($db->record['datetime_from']),
	'to'=>strtotime($db->record['datetime_to']), 'name'=>$db->record['name'], 'notes'=>$db->record['notes'], 'all_day'=>$db->record['all_day'],
	'tag'=>$db->record['tag']);
   if($get)
   {
    $db2 = new AlpaDatabase();
	$db2->RunQuery("SELECT * FROM dynarc_".$archives[$c]."_items WHERE id='".$db->record['item_id']."'");
	$db2->Read();
	for($t=0; $t < count($get); $t++)
	 $a[$get[$t]] = $db2->record[$get[$t]];
	$db2->Close();
   }

   if(!$eventList[$a['from']])
	$eventList[$a['from']] = array();
   $eventList[$a['from']][] = $a;
  }
  $db->Close();

  /* GET RECURRENCE */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM dynarc_".$archives[$c]."_cronrecurrence WHERE start_date<='".date('Y-m-d',$to)."' AND (end_date='0000-00-00' OR end_date>'".date('Y-m-d',$from)."')$q");
  while($db->Read())
  {
   $a = cron_getReccurence($db->record,$from,$to,$archives[$c]);
   if(count($a))
   {
	for($i=0; $i < count($a); $i++)
	{
	 if($get)
     {
      $db2 = new AlpaDatabase();
	  $db2->RunQuery("SELECT * FROM dynarc_".$a[$i]['archive']."_items WHERE id='".$a[$i]['item_id']."'");
	  $db2->Read();
	  for($t=0; $t < count($get); $t++)
	   $a[$get[$t]] = $db2->record[$get[$t]];
	  $db2->Close();
     }
	 if(!$eventList[$a[$i]['from']])
	  $eventList[$a[$i]['from']] = array();
	 $eventList[$a[$i]['from']][] = $a[$i];
	}
   }
  }
  $db->Close();
 }

 // ordina per data gli eventi //
 ksort($eventList);
 reset($eventList);

 // output //
 while(list($d,$a) = each($eventList))
 {
  for($c=0; $c < count($eventList[$d]); $c++)
  {
   $item = $eventList[$d][$c];
   $item['dtfrom'] = date('Y-m-d H:i',$item['from']);
   $item['dtto'] = date('Y-m-d H:i',$item['to']);
   if($item['is_recurrence'])
	$out.= "R ".date('d/m/Y',$item['from']).",";
   else
	$out.= "E ".date('d/m/Y',$item['from']).",";
   if($item['all_day'])
	$out.= $item['name']." (all day)\n";
   else
   {
	$out.= " ".date('H:i',$item['from'])." - ".date('H:i',$item['to'])." ".$item['name']."\n";
   }
   $outArr[] = $item;
  }
 }

 // <--------------8<-----------------------> //
 /*for($c=0; $c < count($archives); $c++)
 {
  $ret = GShell("dynarc exec-func ext:cronevents.list -params `ap=".$archives[$c]."&from=".date('Y-m-d H:i',$from)."&to=".date('Y-m-d H:i',$to)."`", $sessid, $shellid);
  if(!$ret['error'])
  {
   if(count($ret['outarr']) && $getStatus)
   {
	$db = new AlpaDatabase();
	for($i=0; $i < count($ret['outarr']); $i++)
	{
	 $db->RunQuery("SELECT status FROM dynarc_".$archives[$c]."_items WHERE id='".$ret['outarr'][$i]['item_id']."'");
	 $db->Read();
	 $ret['outarr'][$i]['status'] = $db->record['status'];
	}
	$db->Close();
   }
   $outArr[] = array('archive'=>$archives[$c],'items'=>$ret['outarr']);
  }
  $out.= $ret['message'];
 }*/
 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function cron_getFreeTime($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $limit = 10;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$from=$args[$c+1]; $c++;} break;
   case '-to' : {$to=$args[$c+1]; $c++;} break;
   /* TODO: case '-for' : case '-tl' : case '-timelength' : {$timeLength=$args[$c+1]; $c++;} break; */
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 if(!$from) $from = date('Y-m-d H:i');
 else if($from == "tomorrow") $from = date('Y-m-d',strtotime("+1 day"))." 00:00";

 $idtFrom = strtotime($from);
 $idtTo = $to ? strtotime($to) : strtotime("+1 week",$idtFrom);

 $ret = GShell("cron list -from '".date('Y-m-d H:i',$idtFrom)."' -to '".date('Y-m-d H:i',$idtTo)."'",$sessid, $shellid);
 if($ret['error'])
  return array("Error: unable to get event list.\n".$ret['message'], "error"=>$ret['error']);

 $events = $ret['outarr'];
 $dtP = $idtFrom;
 $segments = array();

 for($c=0; $c < count($events); $c++)
 {
  if($limit && (count($segments) >= $limit))
   break;
  $event = $events[$c];
  switch($event['tag'])
  {
   case 'WORKING_AREA' : {
	 if($dtP >= $event['to'])
	  continue;
	 $segment = array("from"=>$event['from'], "to"=>$event['to']);
	 if($dtP > $segment['from'])
	  $segment['from'] = $dtP;
	 $segments[] = $segment;
	 $lastSegment = $segment;
	} break;

   case 'NONWORKING_AREA' : {
	} break;

   default : {
	 if($event['all_day'])
	  continue;
	 // verifica se l'evento si trova all'interno dell'ultimo segmento
	 if($lastSegment && ($event['from'] < $lastSegment['to']))
     {
      $lsTo = $lastSegment['to'];
	  $segments[count($segments)-1]['to'] = $event['from'];
	  if($lsTo > $event['to'])
	  {
	   $segment = array("from"=>$event['to'], "to"=>$lsTo);
	   $segments[] = $segment;
	   $lastSegment = $segment;
	  }
	  else
	   $dtP = $event['to'];
     }
	 else
	  $dtP = $event['to'];
	} break;
  } /* eof - switch */
 } /* eof - for */

 /* Get time length for all segments */
 for($c=0; $c < count($segments); $c++)
 {
  $tls = $segments[$c]['to'] - $segments[$c]['from'];
  $tlm = floor($tls/60);
  $h = floor($tlm/60);
  $m = $tlm-($h*60);
  $segments[$c]['timelength'] = $tlm;
  $segments[$c]['tlstr'] = $h.":".($m < 10 ? "0".$m : $m);

  $outArr[] = array(
	 'from'=>date('Y-m-d H:i',$segments[$c]['from']),
	 'to'=>date('Y-m-d H:i',$segments[$c]['to']),
	 'tl'=>$segments[$c]['timelength'],
	 'tls'=>$segments[$c]['tlstr']
	);
 }

 if($verbose)
 {
  $out.= "List of results from: ".date('d/m/Y H:i',$idtFrom).", to: ".date('d/m/Y H:i',$idtTo)."\n";
  for($c=0; $c < count($segments); $c++)
  {
   $out.= date('d.m.Y H:i',$segments[$c]['from'])." - ".date('H:i',$segments[$c]['to'])."   about(".$segments[$c]['tlstr'].")\n";
  }
 }
 else
  $out.= "found ".count($segments)." results.";
 
 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function cron_getNextOccurrence($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $imode = 0;
 $freq = 1;
 $dayFlag = 0;
 $dayNum = 0;
 $dayPos = 0;
 $startDate = "";
 $endDate = "";
 $allDay = false;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break; // archive prefix
   case '-id' : {$_ID=$args[$c+1]; $c++;} break; // recurrence id

   // alternatively
   case '-imode' : {$imode=$args[$c+1]; $c++;} break; /* 0 = unrepeatable
														 1 = daily
														 2 = weekly
														 3 = monthly
														 4 = yearly */
   case '-freq' : {$freq=$args[$c+1]; $c++;} break;
   case '-dayflag' : {$dayFlag=$args[$c+1]; $c++;} break;
   case '-daynum' : {$dayNum=$args[$c+1]; $c++;} break;
   case '-daypos' : {$dayPos=$args[$c+1]; $c++;} break;
   case '-startdate' : {$startDate=$args[$c+1]; $c++;} break;
   case '-enddate' : {$endDate=$args[$c+1]; $c++;} break;
   case '-timefrom' : {$timeFrom=$args[$c+1]; $c++;} break;
   case '-timeto' : {$timeTo=$args[$c+1]; $c++;} break;
   case '-allday' : {$allDay=$args[$c+1]; $c++;} break;
   case '--allday' : $allDay=true; break;
   case '--auto-update' : $autoUpdate=true; break;
  }

 if($_AP && $_ID)
 {
  $ret = GShell("cron recurrence-info -ap '".$_AP."' -id '".$_ID."'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $eventInfo = $ret['outarr'];
  $imode = $eventInfo['imode'];
  $freq = $eventInfo['freq'];
  $dayFlag = $eventInfo['dayflag'];
  $dayNum = $eventInfo['daynum'];
  $dayPos = $eventInfo['daypos'];
  $startDate = $eventInfo['startdate'];
  $endDate = $eventInfo['enddate'];
  $timeFrom = $eventInfo['timefrom'];
  $timeTo = $eventInfo['timeto'];
  $allDay = $eventInfo['allday'];
 }

 $now = time();
 $nextOccurrence = 0;

 switch($imode)
 {
  case 1 : { /* DAILY */
	 $diff = ($now - strtotime($startDate))/86400;
	 if($diff >= 0)
	  $p = strtotime("+ ".(ceil($diff/$freq)*$freq).
		" day",strtotime($startDate." ".$timeFrom));
	 else
	  $p = strtotime("- ".(ceil(-$diff/$freq)*$freq).
		" day",strtotime($startDate." ".$timeFrom));
	 $continue = true;
	 $db = new AlpaDatabase();
	 while($continue)
	 {
	  if(($p < $now) || ($p < strtotime($startDate))){$p = strtotime("+ ".$freq." day",$p);continue;}
	  else if($endDate && ($endDate != "0000-00-00") && ($endDate != "1970-01-01") && ($p > strtotime($endDate." ".$timeFrom)))
	  {
	   $continue = false;
	   $out.= "no next occurrence. This event has finished at ".date("d M Y H:i",strtotime($endDate." ".$timeFrom));
	   break;
	  }
	  else
	  {
	   if($_AP && $_ID)
	   {
	    // verifica che non sia nella lista delle eccezzioni
	    $db->RunQuery("SELECT * FROM dynarc_".$_AP."_cronrecurrence_exception WHERE rec_id='".$_ID."' AND date_from='".date('Y-m-d',$p)."'");
		if($db->Read()){$p = strtotime("+ ".$freq." day",$p); continue;}
		else 
		{
		 $continue = false; 
		 $nextOccurrence = $p; 
		 if($autoUpdate)
		  $db->RunQuery("UPDATE dynarc_".$_AP."_cronrecurrence SET last_occurrence='".date('Y-m-d',$nextOccurrence)."' WHERE id='".$_ID."'");
		 break;
		}
	   }
	   else {$continue = false;	$nextOccurrence = $p; break;}
	  }
	 }
	 $db->Close();
	} break;
  //---------------------------------------------------------------------------------------------//
  case 2 : { /* WEEKLY */
	 $diff = ($now - strtotime($startDate))/(86400*7);
	 if($diff >= 0)
	  $p = strtotime("+ ".(ceil($diff/$freq)*$freq).
		" week",strtotime($startDate." ".$timeFrom));
	 else
	  $p = strtotime("- ".(ceil(-$diff/$freq)*$freq).
		" week",strtotime($startDate." ".$timeFrom));
	 //--- porto la data $p alla precedente domenica --//
	 if(date('w',$p) != 0)
	  $p = strtotime("- ".date('w',$p)." day",$p);

	 if($p > $now)
	  $p = strtotime("-1 week",$p);

	 $continue = true;
	 $db = new AlpaDatabase();
	 while($continue)
	 {
	  for($c=0; $c < 7; $c++)
	  {
	   if(($p >= $now) && ($dayFlag & pow(2,$c)))
	   {
	    if($_AP && $_ID)
	    {
	     // verifica che non sia nella lista delle eccezzioni
	     $db->RunQuery("SELECT * FROM dynarc_".$_AP."_cronrecurrence_exception WHERE rec_id='".$_ID."' AND date_from='".date('Y-m-d',$p)."'");
		 if($db->Read()){$p = strtotime("+ 1 day",$p); continue;}
		 else 
		 {
		  $continue = false; 
		  $nextOccurrence = $p; 
		  if($autoUpdate)
		   $db->RunQuery("UPDATE dynarc_".$_AP."_cronrecurrence SET last_occurrence='".date('Y-m-d',$nextOccurrence)."' WHERE id='".$_ID."'");
		  break;
		 }
	    }
	    else {$continue = false; $nextOccurrence = $p; break;}
	   }
	   else if($continue)
	    $p = strtotime("+ 1 day",$p);
	  }
	  if(($freq > 1) && $continue)
	  {
	   //-- porto la data a questa domenica e poi mi sposto --//
	   if(date('w',$p) != 0)
		$p = strtotime("- ".date('w',$p)." day",$p);
	   $p = strtotime("+ ".($freq-1)." week",$p);
	  }
	 } // eof while
     $db->Close();
	} break;
  //---------------------------------------------------------------------------------------------//
  case 3 : { /* MONTHLY */
	 $diff = ($now - strtotime($startDate))/86400;
	 $diff = floor(($diff/30)%12);
	 if($diff >= 0)
	  $p = strtotime("+ ".(floor($diff/$freq)*$freq).
		" month",strtotime($startDate." ".$timeFrom));
	 else
	  $p = strtotime("- ".(floor(-$diff/$freq)*$freq).
		" month",strtotime($startDate." ".$timeFrom));

	 $d = array(1=>'Sun',2=>'Mon',4=>'Tue',8=>'Wed',16=>'Thu',32=>'Fri',64=>'Sat');
	 if($dayPos)
	  $p = strtotime("+".$dayPos." ".$d[$dayFlag],strtotime(date('Y-m-1',$p))-86400);

	 $continue = true;
	 $db = new AlpaDatabase();
	 while($continue)
	 {
	  if(($p <= $now) || ($p < strtotime($startDate)))
	  {
	   $p = strtotime("+ ".$freq." month",$p);
	   if($dayPos)
	    $p = strtotime("+".$dayPos." ".$d[$dayFlag],strtotime(date('Y-m-1',$p))-86400);
	  }
	  else if($endDate && ($endDate != "0000-00-00") && ($endDate != "1970-01-01") && ($p > strtotime($endDate." ".$timeFrom)))
	  {
	   $continue = false;
	   $out.= "no next occurrence. This event has finished at ".date("d M Y H:i",strtotime($endDate." ".$timeFrom));
	   break;
	  }
	  else
	  {
	   if($_AP && $_ID)
	   {
	    // verifica che non sia nella lista delle eccezzioni
	    $db->RunQuery("SELECT * FROM dynarc_".$_AP."_cronrecurrence_exception WHERE rec_id='".$_ID."' AND date_from='".date('Y-m-d',$p)."'");
		if($db->Read())
		{
	     $p = strtotime("+ ".$freq." month",$p);
	     if($dayPos)
	      $p = strtotime("+".$dayPos." ".$d[$dayFlag],strtotime(date('Y-m-1',$p))-86400);
		 continue;
		}
		else 
		{
		 $continue = false; 
		 $nextOccurrence = $p; 
		 if($autoUpdate)
		  $db->RunQuery("UPDATE dynarc_".$_AP."_cronrecurrence SET last_occurrence='".date('Y-m-d',$nextOccurrence)."' WHERE id='".$_ID."'");
		 break;
		}
	   }
	   else {$continue = false;	$nextOccurrence = $p; break;}
	  }
	 } // eof while
	 $db->Close();
	} break;
  //---------------------------------------------------------------------------------------------//
  case 4 : { /* YEARLY */
	 $diff = ($now - strtotime($startDate))/86400;
	 $diff = floor($diff/360);
	 if($diff >= 0)
	  $p = strtotime("+ ".(floor($diff/$freq)*$freq).
		" year",strtotime($startDate." ".$timeFrom));
	 else
	  $p = strtotime("- ".(floor(-$diff/$freq)*$freq).
		" year",strtotime($startDate." ".$timeFrom));

	 $continue = true;
	 $db = new AlpaDatabase();
	 while($continue)
	 {
	  if(($p <= $now) || ($p < strtotime($startDate)))
	  {
	   $p = strtotime("+ ".$freq." year",$p);
	   continue;
	  }
	  else if($endDate && ($endDate != "0000-00-00") && ($endDate != "1970-01-01") && ($p > strtotime($endDate." ".$timeFrom)))
	  {
	   $continue = false;
	   $out.= "no next occurrence. This event has finished at ".date("d M Y H:i",strtotime($endDate." ".$timeFrom));
	   break;
	  }
	  else
	  {
	   if($_AP && $_ID)
	   {
	    // verifica che non sia nella lista delle eccezzioni
	    $db->RunQuery("SELECT * FROM dynarc_".$_AP."_cronrecurrence_exception WHERE rec_id='".$_ID."' AND date_from='".date('Y-m-d',$p)."'");
		if($db->Read()){$p = strtotime("+ ".$freq." year",$p); continue;}
		else 
		{
		 $continue = false; 
		 $nextOccurrence = $p; 
		 if($autoUpdate)
		  $db->RunQuery("UPDATE dynarc_".$_AP."_cronrecurrence SET last_occurrence='".date('Y-m-d',$nextOccurrence)."' WHERE id='".$_ID."'");
		 break;
		}
	   }
	   else {$continue = false;	$nextOccurrence = $p; break;}
	  }
	 } // eof while
	 $db->Close();
	} break;
  //---------------------------------------------------------------------------------------------//
 }

 if($nextOccurrence)
 {
  $out.= "The next occurrence is: ".date('D d M Y H:i',$nextOccurrence);
  $outArr['date'] = date('Y-m-d',$nextOccurrence);
  $outArr['datetime'] = date('Y-m-d H:i',$nextOccurrence);
  $outArr['time'] = date('H:i',$nextOccurrence);
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function cron_getReccurence($dbrecord,$from,$to,$archive)
{
 $ret = array();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archive."_cronrecurrence_exception WHERE rec_id='".$dbrecord['id']
	."' AND date_from='".date('Y-m-d',$from)."'");
 if($db->Read())
  return $ret;
 $db->Close();

 if(strtotime(date('Y-m-d')." ".$dbrecord['time_to']) < strtotime(date('Y-m-d')." ".$dbrecord['time_from']))
  $tl = strtotime(date('Y-m-d',strtotime("+1 day"))." ".$dbrecord['time_to']) - strtotime(date('Y-m-d')." ".$dbrecord['time_from']);
 else
  $tl = strtotime(date('Y-m-d')." ".$dbrecord['time_to']) - strtotime(date('Y-m-d')." ".$dbrecord['time_from']);

 switch($dbrecord['interval_mode'])
 {
  case 1 : { //-- daily --//
	 //--- determina matematicamente la prossima occorrenza da $from --//
	 $diff = ($from - strtotime($dbrecord['start_date']))/86400;
	 if($diff >= 0)
	  $p = strtotime("+ ".(ceil($diff/$dbrecord['frequency'])*$dbrecord['frequency']).
		" day",strtotime($dbrecord['start_date']." ".$dbrecord['time_from']));
	 else
	  $p = strtotime("- ".(ceil(-$diff/$dbrecord['frequency'])*$dbrecord['frequency']).
		" day",strtotime($dbrecord['start_date']." ".$dbrecord['time_from']));
	 $db = new AlpaDatabase();
	 while($p < $to)
	 {
	  if(($p>=$from) && (strtotime($dbrecord['start_date']) <= $p) && (($dbrecord['end_date'] == "0000-00-00") || (strtotime($dbrecord['end_date']." ".$dbrecord['time_from']) >= $p)))
	  {
	   $db->RunQuery("SELECT * FROM dynarc_".$archive."_cronrecurrence_exception WHERE rec_id='".$dbrecord['id']."' AND date_from='"
		.date('Y-m-d',$p)."'");
 	   if(!$db->Read())
	   {
	    $afrom = strtotime(date('Y-m-d',$p)." ".$dbrecord['time_from']);
	    $a = array('id'=>$dbrecord['id'], 'archive'=>$archive,'item_id'=>$dbrecord['item_id'], 
		 'from'=>$afrom,	'to'=>$afrom+$tl, 'name'=>$dbrecord['name'], 
		 'notes'=>$dbrecord['notes'], 'all_day'=>$dbrecord['all_day'], 'tag'=>$dbrecord['tag'], 'is_recurrence'=>true);
	    $ret[] = $a;
	   }
	  }
	  $p = strtotime("+ ".$dbrecord['frequency']." day",$p);
	 }
	 $db->Close();
	} break;
   case 2 : { //-- weekly --//
	 //--- determina matematicamente la prossima occorrenza da $from --//
	 $diff = ($from - strtotime($dbrecord['start_date']))/(86400*7);
	 if($diff >= 0)
	  $p = strtotime("+ ".(floor($diff/$dbrecord['frequency'])*$dbrecord['frequency']).
		" week",strtotime($dbrecord['start_date']." ".$dbrecord['time_from']));
	 else
	  $p = strtotime("- ".(floor(-$diff/$dbrecord['frequency'])*$dbrecord['frequency']).
		" week",strtotime($dbrecord['start_date']." ".$dbrecord['time_from']));
	 //--- porto la data $p alla precedente domenica --//
	 if(date('w',$p) != 0)
	  $p = strtotime("- ".date('w',$p)." day",$p);
	 $db = new AlpaDatabase();
	 while($p < $to)
	 {
	  for($c=0; $c < 7; $c++)
	  {
	   $pend = strtotime(date('Y-m-d',$p)." ".$dbrecord['time_to']);
	   if(($dbrecord['day_flag'] & pow(2,$c)) && ( (($p>= $from) && ($p< $to)) || (($pend>$from) && ($pend <= $to)) ))
	   {
		$db->RunQuery("SELECT * FROM dynarc_".$archive."_cronrecurrence_exception WHERE rec_id='".$dbrecord['id']."' AND date_from='"
		.date('Y-m-d',$p)."'");
 	    if(!$db->Read() && (strtotime($dbrecord['start_date']) <= $p) && (($dbrecord['end_date'] == "0000-00-00") || (strtotime($dbrecord['end_date']." ".$dbrecord['time_from']) >= $p)) )
	    {
	     $afrom = strtotime(date('Y-m-d',$p)." ".$dbrecord['time_from']);
	     $a = array('id'=>$dbrecord['id'], 'archive'=>$archive,'item_id'=>$dbrecord['item_id'], 
		 'from'=>$afrom,	'to'=>$afrom+$tl, 'name'=>$dbrecord['name'], 
		 'notes'=>$dbrecord['notes'], 'all_day'=>$dbrecord['all_day'], 'tag'=>$dbrecord['tag'], 'is_recurrence'=>true,'tl'=>$tl);
	     $ret[] = $a;
	    }
	   }
	   $p = strtotime("+ 1 day",$p);
	  }
	  if($dbrecord['frequency'] > 1)
	  {
	   //-- porto la data a questa domenica e poi mi sposto --//
	   if(date('w',$p) != 0)
		$p = strtotime("- ".date('w',$p)." day",$p);
	   $p = strtotime("+ ".($dbrecord['frequency']-1)." week",$p);
	  }
	 }
	 $db->Close();
	} break;
   case 3 : { //-- monthly --//
	 //--- determina matematicamente la prossima occorrenza da $from --//
	 $diff = ($from - strtotime($dbrecord['start_date']))/86400;
	 $diff = floor(($diff/30)%12);
	 if($diff >= 0)
	  $p = strtotime("+ ".(floor($diff/$dbrecord['frequency'])*$dbrecord['frequency']).
		" month",strtotime($dbrecord['start_date']." ".$dbrecord['time_from']));
	 else
	  $p = strtotime("- ".(floor(-$diff/$dbrecord['frequency'])*$dbrecord['frequency']).
		" month",strtotime($dbrecord['start_date']." ".$dbrecord['time_from']));
	 if($dbrecord['day_pos'])
	 {
	  $d = array(1=>'Sun',2=>'Mon',4=>'Tue',8=>'Wed',16=>'Thu',32=>'Fri',64=>'Sat');
	  $p = strtotime("+".$dbrecord['day_pos']." ".$d[$dbrecord['day_flag']],strtotime(date('Y-m-1',$p))-86400);
	 }
	 $db = new AlpaDatabase();
	 while($p < $to)
	 {
	  if(($p>=$from) && ($p<$to))
	  {
	   $db->RunQuery("SELECT * FROM dynarc_".$archive."_cronrecurrence_exception WHERE rec_id='".$dbrecord['id']."' AND date_from='"
		.date('Y-m-d',$p)."'");
 	   if(!$db->Read() && (strtotime($dbrecord['start_date']) <= $p) && (($dbrecord['end_date'] == "0000-00-00") || (strtotime($dbrecord['end_date']." ".$dbrecord['time_from']) >= $p)) )
	   {
	    $afrom = strtotime(date('Y-m-d',$p)." ".$dbrecord['time_from']);
	    $a = array('id'=>$dbrecord['id'], 'archive'=>$archive,'item_id'=>$dbrecord['item_id'], 
		 'from'=>$afrom, 'to'=>$afrom+$tl, 'name'=>$dbrecord['name'], 
		 'notes'=>$dbrecord['notes'], 'all_day'=>$dbrecord['all_day'], 'tag'=>$dbrecord['tag'], 'is_recurrence'=>true);
	    $ret[] = $a;
	   }
	  }
	  $p = strtotime("+ ".$dbrecord['frequency']." month",$p);
	  if($dbrecord['day_pos'])
	   $p = strtotime("+".$dbrecord['day_pos']." ".$d[$dbrecord['day_flag']],strtotime(date('Y-m-1',$p))-86400);
	 }
	 $db->Close();
	} break;
   case 4 : { //-- yearly --//
	 //--- determina matematicamente la prossima occorrenza da $from --//
	 $diff = ($from - strtotime($dbrecord['start_date']))/86400;
	 $diff = floor($diff/360);
	 if($diff >= 0)
	  $p = strtotime("+ ".(floor($diff/$dbrecord['frequency'])*$dbrecord['frequency']).
		" year",strtotime($dbrecord['start_date']." ".$dbrecord['time_from']));
	 else
	  $p = strtotime("- ".(floor(-$diff/$dbrecord['frequency'])*$dbrecord['frequency']).
		" year",strtotime($dbrecord['start_date']." ".$dbrecord['time_from']));
	 $db = new AlpaDatabase();
	 while($p < $to)
	 {
	  if(($p>=$from) && ($p<$to))
	  {
	   $db->RunQuery("SELECT * FROM dynarc_".$archive."_cronrecurrence_exception WHERE rec_id='".$dbrecord['id']."' AND date_from='"
		.date('Y-m-d',$p)."'");
 	   if(!$db->Read() && (strtotime($dbrecord['start_date']) <= $p) && (($dbrecord['end_date'] == "0000-00-00") || (strtotime($dbrecord['end_date']." ".$dbrecord['time_from']) >= $p)) )
	   {
	    $afrom = strtotime(date('Y-m-d',$p)." ".$dbrecord['time_from']);
	    $a = array('id'=>$dbrecord['id'], 'archive'=>$archive,'item_id'=>$dbrecord['item_id'], 
		 'from'=>$afrom, 'to'=>$afrom+$tl, 'name'=>$dbrecord['name'], 
		 'notes'=>$dbrecord['notes'], 'all_day'=>$dbrecord['all_day'], 'tag'=>$dbrecord['tag'], 'is_recurrence'=>true);
	    $ret[] = $a;
	   }
	  }
	  $p = strtotime("+ ".$dbrecord['frequency']." year",$p);
	 }
	 $db->Close();
	} break;
  }
 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//
function cron_eventInfo($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$ap=$args[$c+1]; $c++;} break;
   case '-id' : {$id=$args[$c+1]; $c++;} break;
  }

 $ret = GShell("dynarc exec-func ext:cronevents.info -params `ap=$ap&id=$id`",$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $eventInfo = $ret['outarr'];
 $outArr = $eventInfo;
 $out = "Event: ".$eventInfo['name']."\n";

 if($eventInfo['item_id'])
 {
  switch($ap)
  {
   case 'tasks' : {
	 $out.= "Event Task: ";
	 $ret = GShell("dynarc item-info -ap `".$ap."` -id ".$eventInfo['item_id']." -get ref_arch,ref_id,status",$sessid,$shellid);
	 if($ret['error'])
	 {
	  $out.= "Unable to get task info: ".$ret['message']."\n";
	  continue;
	 }
	 $taskInfo = $ret['outarr'];
	 $outArr['item_info'] = $taskInfo;
	 $out.= "#".$taskInfo['id']." ".$taskInfo['name']."\n";

	 /* get parent informations */
	 $out.= "Event Task parent: ";
	 $db = new AlpaDatabase();
	 $db->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE id='".$taskInfo['ref_arch']."'");
	 if(!$db->Read())
	 {
	  $out.= "Unable to get task parent info: Archive #".$taskInfo['ref_arch']." does not exists.\n";
	  continue;
	 }
	 $get = "";
	 switch($db->record['tb_prefix'])
	 {
	  case 'projects' : case 'tickets' : {
		 $get = " -get customer,refer,priority,tax_delivery";
		} break;
	 }
	 $db->Close();
	 $ret = GShell("dynarc item-info -aid `".$taskInfo['ref_arch']."` -id ".$taskInfo['ref_id'].$get,$sessid,$shellid);
	 if($ret['error'])
	 {
	  $out.= "Unable to get task parent info: ".$ret['message']."\n";
	  continue;
	 }
	 $outArr['item_info']['parent_info'] = $ret['outarr'];
	 $outArr['item_info']['parent_info']['ap'] = $db->record['tb_prefix'];
	 $out.= "AP=".$db->record['tb_prefix']." ID=".$ret['outarr']['id']." NAME=".$ret['outarr']['name']."\n";
	} break;

   case 'todo' : {
	 $out.= "Todo Task: ";
	 $ret = GShell("dynarc item-info -ap `".$ap."` -id ".$eventInfo['item_id']." -get status",$sessid,$shellid);
	 if($ret['error'])
	 {
	  $out.= "Unable to get todo info: ".$ret['message']."\n";
	  continue;
	 }
	 $todoInfo = $ret['outarr'];
	 $outArr['item_info'] = $todoInfo;
	 $out.= "#".$todoInfo['id']." ".$todoInfo['name']."\n";
	} break;

   default : {
	 $out.= "Item: ";
	 $ret = GShell("dynarc item-info -ap `".$ap."` -id ".$eventInfo['item_id'],$sessid,$shellid);
	 if($ret['error'])
	 {
	  $out.= "Unable to get item info: ".$ret['message']."\n";
	  continue;
	 }
	 $otherInfo = $ret['outarr'];
	 $outArr['item_info'] = $otherInfo;
	 $out.= "#".$otherInfo['id']." ".$otherInfo['name']."\n";
	} break;
  }
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function cron_editEvent($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 include_once($_BASE_PATH."include/userfunc.php");
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-ap' : {$ap=$args[$c+1]; $c++;} break;
   case '-from' : {$from=$args[$c+1]; $c++;} break;
   case '-to' : {$to=$args[$c+1]; $c++;} break;
   case '-allday' : {$allDay=$args[$c+1]; $c++;} break;
   case '-tag' : {$tag=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-notes' : {$notes=$args[$c+1]; $c++;} break;
   case '--update-doc-date' : $updateDocDate=true; break;
  }

 $sessInfo = sessionInfo($sessid);

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$ap."_cronevents WHERE id='$id'");
 if(!$db->Read())
  return array('message'=>"Item #$id does not exists into archive $ap","error"=>"ITEM_DOES_NOT_EXISTS");
 $cronInfo = $db->record;
 $db->RunQuery("SELECT uid,gid,_mod FROM dynarc_".$ap."_items WHERE id='".$db->record['item_id']."'");
 if(!$db->Read())
  return array('message'=>"Item #$id does not exists into archive $ap","error"=>"ITEM_DOES_NOT_EXISTS");
 $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid']);
 if(!$m->canWrite($sessInfo['uid']))
  return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");

 $q = "";
 if($from)
  $q.= ",datetime_from='$from'";
 if($to)
  $q.= ",datetime_to='$to'";
 if(isset($allDay))
  $q.= ",all_day='$allDay'";
 if(isset($tag))
  $q.= ",tag='$tag'";
 if($name)
  $q.= ",name='$name'";
 if(isset($notes))
  $q.= ",notes='$notes'";

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$ap."_cronevents SET ".ltrim($q,',')." WHERE id='".$id."'");
 if($updateDocDate)
  $db->RunQuery("UPDATE dynarc_".$ap."_items SET ctime='".date('Y-m-d H:i',strtotime($from ? $from : $cronInfo['datetime_from']))."' WHERE id='"
	.$cronInfo['item_id']."'");
 $db->Close();


 $outArr = array("id"=>$cronInfo['id'], 'archive'=>$ap, 'item_id'=>$cronInfo['item_id'],'tag'=>isset($tag) ? $tag : $cronInfo['tag'],
	'name'=>$name ? $name : $cronInfo['name'],
	'notes'=>$notes ? $notes : $cronInfo['notes'],
	'from'=>$from ? $from : $cronInfo['datetime_from'],
	'to'=>$to ? $to : $cronInfo['datetime_to'],
	'allday'=>isset($allDay) ? $allDay : $cronInfo['all_day']);

 return array('message'=>"done!", "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function cron_deleteEvent($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 include_once($_BASE_PATH."include/userfunc.php");
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-ap' : {$ap=$args[$c+1]; $c++;} break;
  }

 $sessInfo = sessionInfo($sessid);

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$ap."_cronevents WHERE id='$id'");
 if(!$db->Read())
  return array('message'=>"Item #$id does not exists into archive $ap","error"=>"ITEM_DOES_NOT_EXISTS");
 $cronInfo = $db->record;
 $db->RunQuery("SELECT uid,gid,_mod FROM dynarc_".$ap."_items WHERE id='".$db->record['item_id']."'");
 if(!$db->Read())
  return array('message'=>"Item #$id does not exists into archive $ap","error"=>"ITEM_DOES_NOT_EXISTS");
 $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid']);
 if(!$m->canWrite($sessInfo['uid']))
  return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");

 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$ap."_cronevents WHERE id='$id'");
 $db->Close();

 /* COMPATIBILITY WITH TASKS */
 if($ap == "tasks")
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$ap."_cronevents WHERE item_id='".$cronInfo['item_id']."' LIMIT 1");
  $db->Read();
  if($db->record[0] == 0)
   GShell("dynarc edit-item -ap tasks -id `".$cronInfo['item_id']."` -extset `tasks.status=0,exec-datetime=autocheck`",$sessid, $shellid);
  $db->Close();
 }
 /* EOF - COMPATIBILITY WITH TASKS */

 return array('message'=>"Event #$id has been removed!");
}
//-------------------------------------------------------------------------------------------------------------------//
function cron_eventToRecurrence($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 include_once($_BASE_PATH."include/userfunc.php");
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-ap' : {$ap=$args[$c+1]; $c++;} break;
   case '-from' : {$from=$args[$c+1]; $c++;} break;
   case '-to' : {$to=$args[$c+1]; $c++;} break;
   case '-tl' : case '-timelength' : {$timeLength=$args[$c+1]; $c++;} break; /* optional - auto retrieve time to by time length (in minutes)*/
   case '-allday' : {$allDay=$args[$c+1]; $c++;} break;
   case '-tag' : {$tag=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-notes' : {$notes=$args[$c+1]; $c++;} break;
   case '-start' : {$start=$args[$c+1]; $c++;} break;
   case '-end' : {$end=$args[$c+1]; $c++;} break;
   case '-imode' : {$imode=$args[$c+1]; $c++;} break;
   case '-freq' : {$freq=$args[$c+1]; $c++;} break;
   case '-dayflag' : {$dayFlag=$args[$c+1]; $c++;} break;
   case '-daynum' : {$dayNum=$args[$c+1]; $c++;} break;
   case '-daypos' : {$dayPos=$args[$c+1]; $c++;} break;
  }

 /* CHECK PERMISSION */
 $sessInfo = sessionInfo($sessid);
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$ap."_cronevents WHERE id='$id'");
 if(!$db->Read())
  return array('message'=>"Item #$id does not exists into archive $ap","error"=>"ITEM_DOES_NOT_EXISTS");
 $cronInfo = $db->record;
 $db->RunQuery("SELECT uid,gid,_mod FROM dynarc_".$ap."_items WHERE id='".$db->record['item_id']."'");
 if(!$db->Read())
  return array('message'=>"Item #$id does not exists into archive $ap","error"=>"ITEM_DOES_NOT_EXISTS");
 $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid']);
 if(!$m->canWrite($sessInfo['uid']))
  return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");
 $db->Close();
 /* EOF - CHECK PERMISSION */

 if($imode < 1)
  return array("message"=>"imode must be > 0", "error"=>"INVALID_IMODE");

 $tmp = explode(" ",$from ? $from : $cronInfo['datetime_from']);
 $dateFrom = $tmp[0];
 $timeFrom = $tmp[1];
 if(!$to && $timeLength)
 {
  $tmpT = strtotime("+ ".$timeLength." minutes",strtotime($dateFrom." ".$timeFrom));
  $dateTo = date('Y-m-d',$tmpT);
  $timeTo = date('H:i',$tmpT);
 }
 else
 {
  $tmp = explode(" ",$to ? $to : $cronInfo['datetime_to']);
  $dateTo = $tmp[0];
  $timeTo = $tmp[1];
 }
 if(!isset($allDay)) $allDay = $cronInfo['all_day'];
 if(!isset($tag)) $tag=$cronInfo['tag'];
 if(!isset($name)) $name=$cronInfo['name'];
 if(!isset($notes)) $notes=$cronInfo['notes'];
 if(!$start)
  $start = $dateFrom;
 if($freq)
  $freq = 1; 

 /* Create new recurrence */
 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO dynarc_".$ap."_cronrecurrence(item_id,tag,start_date,end_date,time_from,time_to,all_day,
interval_mode,frequency,day_flag,day_num,day_pos,name,notes) VALUES('".$cronInfo['item_id']."','$tag','$start','$end','$timeFrom','$timeTo','"
	.($allDay ? "1" : "0")."','$imode','$freq','$dayFlag','$dayNum','$dayPos','".$name."','$notes')");
 $id = $db->GetInsertId();
 $db->Close();
 
 /* Remove old event */
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$ap."_cronevents WHERE id='".$cronInfo['id']."'");
 $db->Close();

  // update next occurrence
 $ret = GShell("cron get-next-occurrence -ap '".$ap."' -id '".$id."' --auto-update",$sessid,$shellid);
 $nextOccurrence = $ret['outarr']['date'];


 $outArr = array('id'=>$id,'item_id'=>$cronInfo['item_id'],'archive'=>$ap,'tag'=>$tag, 'name'=>$name, 'notes'=>$notes, 'start'=>$start, 'end'=>$end,
	'from'=>$timeFrom,	'to'=>$timeTo, 'allday'=>$allDay, 'imode'=>$imode, 'freq'=>$freq, 'daynum'=>$dayNum, 'daypos'=>$dayPos, 'is_recurrence'=>true,
	'next_occurrence'=>$nextOccurrence);

 return array('message'=>"done!", "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function cron_editRecurrence($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 include_once($_BASE_PATH."include/userfunc.php");
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-ap' : {$ap=$args[$c+1]; $c++;} break;
   case '-from' : {$from=$args[$c+1]; $c++;} break;
   case '-to' : {$to=$args[$c+1]; $c++;} break;
   case '-allday' : {$allDay=$args[$c+1]; $c++;} break;
   case '-tag' : {$tag=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-notes' : {$notes=$args[$c+1]; $c++;} break;
   case '-start' : {$start=$args[$c+1]; $c++;} break;
   case '-end' : {$end=$args[$c+1]; $c++;} break;
   case '-imode' : {$imode=$args[$c+1]; $c++;} break;
   case '-freq' : {$freq=$args[$c+1]; $c++;} break;
   case '-dayflag' : {$dayFlag=$args[$c+1]; $c++;} break;
   case '-daynum' : {$dayNum=$args[$c+1]; $c++;} break;
   case '-daypos' : {$dayPos=$args[$c+1]; $c++;} break;
  }

 /* CHECK PERMISSION */
 $sessInfo = sessionInfo($sessid);
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$ap."_cronrecurrence WHERE id='$id'");
 if(!$db->Read())
  return array('message'=>"Item #$id does not exists into archive $ap","error"=>"ITEM_DOES_NOT_EXISTS");
 $cronInfo = $db->record;
 $db->RunQuery("SELECT uid,gid,_mod FROM dynarc_".$ap."_items WHERE id='".$db->record['item_id']."'");
 if(!$db->Read())
  return array('message'=>"Item #$id does not exists into archive $ap","error"=>"ITEM_DOES_NOT_EXISTS");
 $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid']);
 if(!$m->canWrite($sessInfo['uid']))
  return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");
 $db->Close();
 /* EOF - CHECK PERMISSION */

 if(isset($imode) && ($imode < 1))
  return array("message"=>"imode must be > 0", "error"=>"INVALID_IMODE");

 $q = "";
 if($from)
  $q.= ",time_from='".date('H:i',strtotime($from))."'";
 if($to)
  $q.= ",time_to='".date('H:i',strtotime($to))."'";
 if($start)
  $q.= ",start_date='$start'";
 if(isset($end))
  $q.= ",end_date='$end'";
 if(isset($allDay))
  $q.= ",all_day='$allDay'";
 if(isset($tag))
  $q.= ",tag='$tag'";
 if($name)
  $q.= ",name='$name'";
 if($notes)
  $q.= ",notes='$notes'";
 if($imode)
  $q.= ",interval_mode='$imode'";
 if($freq)
  $q.= ",frequency='$freq'";
 if(isset($dayFlag))
  $q.= ",day_flag='$dayFlag'";
 if(isset($dayNum))
  $q.= ",day_num='$dayNum'";
 if(isset($dayPos))
  $q.= ",day_pos='$dayPos'";

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$ap."_cronrecurrence SET ".ltrim($q,",")." WHERE id='$id'");
 $db->Close();

 // update next occurrence
 $ret = GShell("cron get-next-occurrence -ap '".$ap."' -id '".$id."' --auto-update",$sessid,$shellid);
 $nextOccurrence = $ret['outarr']['date'];


 $outArr = array("id"=>$cronInfo['id'], 'archive'=>$ap, 'item_id'=>$cronInfo['item_id'],'tag'=>isset($tag) ? $tag : $cronInfo['tag'],
	'name'=>$name ? $name : $cronInfo['name'],
	'notes'=>$notes ? $notes : $cronInfo['notes'],
	'startdate'=>$start ? $start : $cronInfo['start_date'],
	'enddate'=>$end ? $end : $cronInfo['end_date'],
	'timefrom'=>$from ? date('H:i',strtotime($from)) : $cronInfo['time_from'],
	'timeto'=>$to ? date('H:i',strtotime($to)) : $cronInfo['time_to'],
	'allday'=>isset($allDay) ? $allDay : $cronInfo['all_day'],
	'imode'=>$imode ? $imode : $cronInfo['interval_mode'],
	'freq'=>$freq ? $freq : $cronInfo['frequency'],
	'dayflag'=>$dayFlag ? $dayFlag : $cronInfo['day_flag'],
	'daynum'=>$dayNum ? $dayNum : $cronInfo['day_num'],
	'daypos'=>$dayPos ? $dayPos : $cronInfo['day_pos'],
	'next_occurrence'=>$nextOccurrence
 );
 
 return array('message'=>"done!", "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function cron_recurrenceInfo($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 include_once($_BASE_PATH."include/userfunc.php");
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-ap' : {$ap=$args[$c+1]; $c++;} break;
  }
 
 /* CHECK PERMISSION */
 $sessInfo = sessionInfo($sessid);
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$ap."_cronrecurrence WHERE id='$id'");
 if(!$db->Read())
  return array('message'=>"Item #$id does not exists into archive $ap","error"=>"ITEM_DOES_NOT_EXISTS");
 $cronInfo = $db->record;
 $db->RunQuery("SELECT uid,gid,_mod FROM dynarc_".$ap."_items WHERE id='".$db->record['item_id']."'");
 if(!$db->Read())
  return array('message'=>"Item #$id does not exists into archive $ap","error"=>"ITEM_DOES_NOT_EXISTS");
 $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid']);
 if(!$m->canRead($sessInfo['uid']))
  return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");
 $db->Close();
 /* EOF - CHECK PERMISSION */

 $outArr = array("id"=>$cronInfo['id'], 'archive'=>$ap, 'item_id'=>$cronInfo['item_id'],'tag'=>$cronInfo['tag'],'name'=>$cronInfo['name'],
	'notes'=>$cronInfo['notes'],'startdate'=>$cronInfo['start_date'],'enddate'=>$cronInfo['end_date'],'timefrom'=>$cronInfo['time_from'],
	'timeto'=>$cronInfo['time_to'],'allday'=>$cronInfo['all_day'],'imode'=>$cronInfo['interval_mode'],'freq'=>$cronInfo['frequency'],
	'dayflag'=>$cronInfo['day_flag'],'daynum'=>$cronInfo['day_num'],'daypos'=>$cronInfo['day_pos']);

 $out.= "done!";
 
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function cron_deleteRecurrence($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 include_once($_BASE_PATH."include/userfunc.php");

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-ap' : {$ap=$args[$c+1]; $c++;} break;
   case '-all' : $deleteAll = true; break;
   case '-subsequent' : $deleteSubsequent = true; break;
   case '-from' : {$from=$args[$c+1]; $c++;} break;
  }
 if(!$id)
  return array("message"=>"You must specify recurrence (with -id RECURRENCE_ID)","error"=>"INVALID_RECURRENCE");

 /* CHECK PERMISSION */
 $sessInfo = sessionInfo($sessid);
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$ap."_cronrecurrence WHERE id='$id'");
 if(!$db->Read())
  return array('message'=>"Item #$id does not exists into archive $ap","error"=>"ITEM_DOES_NOT_EXISTS");
 $cronInfo = $db->record;
 $db->RunQuery("SELECT uid,gid,_mod FROM dynarc_".$ap."_items WHERE id='".$db->record['item_id']."'");
 if(!$db->Read())
  return array('message'=>"Item #$id does not exists into archive $ap","error"=>"ITEM_DOES_NOT_EXISTS");
 $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid']);
 if(!$m->canWrite($sessInfo['uid']))
  return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");
 $db->Close();
 /* EOF - CHECK PERMISSION */

 $db = new AlpaDatabase();
 if($deleteAll)
 {
  $db->RunQuery("DELETE FROM dynarc_".$ap."_cronrecurrence WHERE id='$id'");
  $db->RunQuery("DELETE FROM dynarc_".$ap."_cronrecurrence_exception WHERE rec_id='$id'");
 }
 else if($deleteSubsequent)
 {
  $db->RunQuery("UPDATE dynarc_".$ap."_cronrecurrence SET end_date='$from' WHERE id='$id'");
  $db->RunQuery("DELETE FROM dynarc_".$ap."_cronrecurrence_exception WHERE rec_id='$id' AND date_from>'".$from."'");
 }
 else
  $db->RunQuery("INSERT INTO dynarc_".$ap."_cronrecurrence_exception(rec_id,date_from) VALUES('$id','$from')");

 // update next occurrence
 $ret = GShell("cron get-next-occurrence -ap '".$ap."' -id '".$id."' --auto-update",$sessid,$shellid);
 if(!$ret['error'])
  $outArr['next_occurrence'] = $ret['outarr']['next_occurrence'];

 $out.= "done!\n";
 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function cron_recurrenceToEvent($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 include_once($_BASE_PATH."include/userfunc.php");
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-ap' : {$ap=$args[$c+1]; $c++;} break;
   case '-from' : {$from=$args[$c+1]; $c++;} break;
   case '-to' : {$to=$args[$c+1]; $c++;} break;
   case '-tl' : case '-timelength' : {$timeLength=$args[$c+1]; $c++;} break; /* optional - auto retrieve time to by time length (in minutes)*/
   case '-allday' : {$allDay=$args[$c+1]; $c++;} break;
   case '-tag' : {$tag=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-notes' : {$notes=$args[$c+1]; $c++;} break;
   case '-exception' : {$exception=$args[$c+1]; $c++;} break;
  }

 /* CHECK PERMISSION */
 $sessInfo = sessionInfo($sessid);
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$ap."_cronrecurrence WHERE id='$id'");
 if(!$db->Read())
  return array('message'=>"Item #$id does not exists into archive $ap","error"=>"ITEM_DOES_NOT_EXISTS");
 $cronInfo = $db->record;
 $db->RunQuery("SELECT uid,gid,_mod FROM dynarc_".$ap."_items WHERE id='".$db->record['item_id']."'");
 if(!$db->Read())
  return array('message'=>"Item #$id does not exists into archive $ap","error"=>"ITEM_DOES_NOT_EXISTS");
 $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid']);
 if(!$m->canWrite($sessInfo['uid']))
  return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");
 $db->Close();
 /* EOF - CHECK PERMISSION */

 if(!$from) $from = $cronInfo['start_date']." ".$cronInfo['time_from'];
 if(!$to) $to = $cronInfo['start_date']." ".$cronInfo['time_to'];
 else if($timeLength)
 {
  $tmpT = strtotime("+ ".$timeLength." minutes",strtotime($from));
  $to = date('Y-m-d H:i',$tmpT);
 }
 if(!isset($allDay)) $allDay = $cronInfo['all_day'];
 if(!isset($tag)) $tag=$cronInfo['tag'];
 if(!isset($name)) $name=$cronInfo['name'];
 if(!isset($notes)) $notes=$cronInfo['notes'];

 /* Create new event */
 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO dynarc_".$ap."_cronevents(item_id,tag,datetime_from,datetime_to,all_day,name,notes) VALUES('"
	.$cronInfo['item_id']."','$tag','$from','$to','".($allDay ? "1" : "0")."','".$name."','$notes')");
 $eventid = $db->GetInsertId();
 $db->Close();
 
 if($exception)
 {
  /* Create recurrence exception */
  $db = new AlpaDatabase();
  $db->RunQuery("INSERT INTO dynarc_".$ap."_cronrecurrence_exception(rec_id,date_from) VALUES('$id','$exception')");
  $db->Close();
 }
 $outArr = array('id'=>$eventid,'archive'=>$ap, 'item_id'=>$cronInfo['item_id'],'tag'=>$tag, 'name'=>$name, 'notes'=>$notes, 
	'from'=>$from,	'to'=>$to, 'allday'=>$allDay);

 return array('message'=>"done!","outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
?>

