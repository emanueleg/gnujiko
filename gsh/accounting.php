<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-04-2013
 #PACKAGE: gnujiko-accounting-base
 #DESCRIPTION: Collection of functions for accounting.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO: 
 
*/

function shell_accounting($args, $sessid, $shellid=0)
{
 if(count($args) == 0)
  return accounting_invalidArguments();

 switch($args[0])
 {
  case 'paymentmodeinfo' : return accounting_paymentModeInfo($args, $sessid, $shellid); break;

  default : return accounting_invalidArguments(); break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function accounting_invalidArguments()
{
 return array("message"=>"Invalid arguments.", "error"=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function accounting_paymentModeInfo($args, $sessid, $shellid)
{
 global $_BASE_PATH;

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break; 
   case '-from' : {$dateFrom=strtotime($args[$c+1]); $c++;} break;
   case '-amount' : {$amount=$args[$c+1]; $c++;} break;
   case '--get-deadlines' : $getDeadLines=true; break;
   case '--get-id' : $getID=true; break;

   case '-verbose' : case '--verbose' : $verbose=true; break;

   default : $string=$args[$c]; break;
  }

 $pmType = "";
 $pmFM = false; /* pagamento a fine mese */
 $pmDF = false; /* pagamento data fattura */
 $pmMS = false; /* pagamento a partire dal mese successivo */
 $pmTerms = array(); /* lista dei termini. (es: 30,60,90,...) */

 /* ITALIAN DICTIONARY */
 $dictPM = array(
	 "RB" => array("RI.BA", "R.B.", "RIBA"),
	 "BB" => array("B.B", "BONIFICO", "BANCA", "BB"),
	 "RD" => array("R.D", "RIMESSA", "RD")
	);

 if($id)
 {
  $ret = GShell("paymentmodes info -id '".$id."'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $pmInfo = $ret['outarr'];
  $pmType = $pmInfo['type'];
  $pmFM = $pmInfo['date_terms'] ? true : false;
  $pmDF = !$pmInfo['date_terms'] ? true : false;
  if($pmInfo['terms'])
   $pmTerms = explode(",",$pmInfo['terms']);
  if($pmInfo['day_after'])
   $_DD_AFTER = $pmInfo['day_after'];
 }
 else
 {
  /* DETECT PAYMENT TYPE FROM STRING */
  while(list($k,$v)=each($dictPM))
  {
   if($pmType)
    break;
   for($c=0; $c < count($v); $c++)
   {
    if(stripos($string,$v[$c]) !== false)
    {
 	 $pmType = $k;
	 break;
    }
   }
  }

  /* DETECT TERMS */
  if((stripos($string,"F.M") !== false) || (stripos($string,"FM") !== false) || (stripos($string,"FINE MESE")))
   $pmFM = true;
  else if((stripos($string,"D.F") !== false) || 
	(stripos($string,"DF") !== false) ||
	(stripos($string,"VISTA FATTURA") !== false) || 
	(stripos($string,"DATA FATTURA") !== false))
   $pmDF = true;
  else if((stripos($string,"M.S") !== false) || (stripos($string,"MS") !== false))
   $pmMS = true;

  if(strpos($string, "+") !== false)
  {
   $x = explode("+",$string);
   preg_match_all('!\d+\.*\d*!', $x[0],$tmp);
   $pmTerms = $tmp[0];
   preg_match_all('!\d+\.*\d*!', $x[1],$tmp2);
   $_DD_AFTER = $tmp2[0][0];
  }
  else
  {
   preg_match_all('!\d+\.*\d*!', $string,$tmp);
   $pmTerms = $tmp[0];
  }
 }

 $outArr['type'] = $pmType;
 $outArr['terms'] = $pmTerms;
 $outArr['termstring'] = count($pmTerms) ? implode(",",$pmTerms) : "";
 $outArr['day_after'] = $_DD_AFTER;
 $outArr['date_terms'] = $pmFM ? 1 : 0;

 if($getID)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM payment_modes WHERE name LIKE '".$string."'");
  if($db->Read())
   $id = $db->record['id'];
  else
  {
   $db->RunQuery("SELECT id FROM payment_modes WHERE type='".$pmType."' AND date_terms='".($pmFM ? 1 : 0)."' AND deadlines='"
	.$outArr['termstring']."' AND day_after='".$_DD_AFTER."' LIMIT 1");
   if($db->Read())
    $id = $db->record['id'];
  }
  $outArr['id'] = $id;
  $db->Close();
 }

 if($verbose)
 {
  $out.= "Payment informations detected from string:\n";
  if($id)
   $out.= "ID: ".$id."\n";
  $out.= "Type: ".$pmType."\n";
  $out.= "Terms: ".implode(",",$pmTerms)."\n";
  $out.= "Date calc: ".($pmFM ? "end of month" : "date of invoice").($_DD_AFTER ? " at day ".$_DD_AFTER : "")."\n";
 }

 if($getDeadLines)
 {
  if(!$dateFrom) $dateFrom=time();
  $outArr['deadlines'] = array();
  if($amount && count($pmTerms))
   $am = $amount/count($pmTerms);
  else
   $am = 0;
  if($verbose)
   $out.= "Deadlines from ".date('Y-m-d',$dateFrom).":\n";
  for($c=0; $c < count($pmTerms); $c++)
  {
   $days = $pmTerms[$c];
   $time = $dateFrom;
   if($pmDF)
	$time = strtotime(date("Y-m",$time)."-".date('d',$dateFrom));
   else if($pmFM)
	$time = strtotime(date("Y-m",$time)."-".date('t',$time));
   $time = strtotime("+".$days." DAYS",$time);
   if($_DD_AFTER)
   {
	$time = strtotime(date("Y-m",$time)."-".$_DD_AFTER);
	$time = strtotime("+1 MONTH",$time);
   }

   $outArr['deadlines'][] = array('date'=>date("Y-m-d",$time), 'amount'=>$am);
   if($verbose)
	$out.= "#".($c+1)." - ".date('d/m/Y',$time)." ".($amount ? number_format($am,2,",",".") : "")."\n";  
  }
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

