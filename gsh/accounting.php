<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 29-01-2016
 #PACKAGE: gnujiko-accounting-base
 #DESCRIPTION: Collection of functions for accounting.
 #VERSION: 2.7beta
 #CHANGELOG: 29-01-2016 : Bug fix arrotondamenti sulle rate.
			 02-03-2015 : Aggiunto pa_mode.
			 10-02-2014 : Risolto una volta per tutte le date delle scadenze.
			 05-02-2014 : Bug fix sulle scadenze.
			 16-12-2013 : Bug fix sulle scadenze.
			 14-11-2013 : Sistemato le scadenze
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
 global $_BASE_PATH, $_COMPANY_PROFILE;
 include_once($_BASE_PATH."include/company-profile.php");

 $_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'] ? $_COMPANY_PROFILE['accounting']['decimals_pricing'] : 2;

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
 $paMode = "";

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
  $paMode = $pmInfo['pa_mode'];
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

 if(!$paMode)
 {
  switch($pmType)
  {
   case 'RB' : $paMode = "MP12"; break;
   case 'BB' : $paMode = "MP05"; break;
   default : $paMode = "MP01"; break;
  }
 }

 $outArr['type'] = $pmType;
 $outArr['pa_mode'] = $paMode;
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
   $am = round($amount/count($pmTerms), $_DECIMALS);
  else
   $am = 0;
  if($verbose)
   $out.= "Deadlines from ".date('Y-m-d',$dateFrom).":\n";
  if(!count($pmTerms))
  {
   $outArr['deadlines'][] = array('date'=>date('Y-m-d',$dateFrom), 'amount'=>$amount);
   if($verbose)
	$out.= "#1 - ".date('d/m/Y',$dateFrom)." ".($amount ? number_format($amount,$_DECIMALS,",",".") : "")."\n";  
  }
  else
  {
   for($c=0; $c < count($pmTerms); $c++)
   {
    $days = $pmTerms[$c];
    $time = $dateFrom;
    switch($days)
    {
	 case '30' : $time = ($pmFM || $_DD_AFTER || $pmDF) ? strtotime("first day of +1month",$time) : strtotime("+30 days",$time); break;
	 case '60' : $time = ($pmFM || $_DD_AFTER || $pmDF) ? strtotime("first day of +2month",$time) : strtotime("+60 days",$time); break;
	 case '90' : $time = ($pmFM || $_DD_AFTER || $pmDF) ? strtotime("first day of +3month",$time) : strtotime("+90 days",$time); break;
	 case '120' : $time = ($pmFM || $_DD_AFTER || $pmDF) ? strtotime("first day of +4month",$time) : strtotime("+120 days",$time); break;
     default : {
		 $time = strtotime("+".$days." days",$time);
		 $pmDF = false;
		} break;
    }

    if($pmDF)
	{
	 if(strtotime(date("Y-m",$time)."-".date('t',$time)) < strtotime(date("Y-m",$time)."-".date('d',$dateFrom)))
	  $time = strtotime(date("Y-m",$time)."-".date('t',$time));
	 else
	  $time = strtotime(date("Y-m",$time)."-".date('d',$dateFrom));
	}
    else if($pmFM)
     $time = strtotime(date("Y-m",$time)."-".date('t',$time));

    if($_DD_AFTER)
    {
	 if(strtotime(date("Y-m",$time)."-".date('t',$time)) < strtotime(date("Y-m",$time)."-".$_DD_AFTER))
	  $time = strtotime(date("Y-m",$time)."-".date('t',$time));
	 else
	  $time = strtotime(date("Y-m",$time)."-".$_DD_AFTER);
    }

    $outArr['deadlines'][] = array('date'=>date("Y-m-d",$time), 'amount'=>$am);
    if($verbose)
	 $out.= "#".($c+1)." - ".date('d/m/Y',$time)." ".($amount ? number_format($am,$_DECIMALS,",",".") : "")."\n";  
   }
  }

  // verifica problemi di arrotondamento
  $tot = 0;
  for($c=0; $c < count($outArr['deadlines']); $c++)
   $tot+= $outArr['deadlines'][$c]['amount'];
  if($tot < $amount)
   $outArr['deadlines'][0]['amount']+= round($amount-$tot, $_DECIMALS); // se < dell'importo totale aumenta nella prima rata
  else if($tot > $amount)
   $outArr['deadlines'][count($outArr['deadlines'])-1]['amount']-= round($tot-$amount,$_DECIMALS); // se > dell'importo tot. diminuisce sull'ultima rata

 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

