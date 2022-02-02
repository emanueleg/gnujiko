<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2015 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 01-05-2015
 #PACKAGE: gnujiko-accounting-base
 #DESCRIPTION: Strumento per il calcolo delle rate.
 #VERSION: 2.1beta
 #CHANGELOG: 01-05-2015 : Aggiustato l'arrotondamento.
 #TODO: 
 
*/

function shell_calcrate($args, $sessid, $shellid=0)
{
 $out = "";
 $outArr = array("deadlines"=>array());
 $_DECIMALS = 2;
 $_ROUNDDEC = 5;
 
 if(!count($args)) return shell_calcrate_invalidArguments($args, $sessid, $shellid);

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-amount' : {$amount=$args[$c+1]; $c++;} break;		// totale imponibile
   case '-vatrate' : {$vatRate=$args[$c+1]; $c++;} break;	// aliq. iva su imponibile
   case '-commiss' : {$commiss=$args[$c+1]; $c++;} break;	// importo commissione x ogni rata
   case '-commissvatrate' : {$commissVatRate=$args[$c+1]; $c++;} break;	// aliq. iva su commissione
   case '-periodicity' : case '-freq' : {$periodicity=$args[$c+1]; $c++;} break;	// periodicità in mesi
   case '-months' : {$months=$args[$c+1]; $c++;} break;		// durata contratto in mesi (es: 12)
   case '-payday' : {$payDay=$args[$c+1]; $c++;} break;		// giorno pagamento. (può essere un giorno fisso oppure FM (fine mese)).
   case '-from' : case '-datefrom' : {$dateFrom=$args[$c+1]; $c++;} break;	// data inizio scadenze (data inizio contratto).
   case '-decimals' : {$_DECIMALS=$args[$c+1]; $c++;} break;

   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 if(!$amount)		return array('message'=>"E' necessario specificare un'importo.", 'error'=>"AMOUNT_IS_ZERO");
 if(!$periodicity)	return array('message'=>"Devi specificare la periodicità (in mesi).", 'error'=>"INVALID_PERIODICITY");
 if(!$months)		return array('message'=>"Devi specificare la durata del contratto.", 'error'=>"INVALID_MONTHS");
 if(!$dateFrom)		return array('message'=>"Devi specificare la data inizio contratto.", 'error'=>"INVALID_DATEFROM");

 $totRates = $months/$periodicity;

 // calcolo l'importo x ogni rata
 $rateAmount = round($amount / 12 * $periodicity, $_ROUNDDEC);
 $rateCommiss = $commiss;
 $commissVat = ($commiss/100) * $commissVatRate;
 $rateVat = ($rateAmount/100) * $vatRate;
 $rateVat+= $commissVat;

 $totAmount = round($rateAmount + $rateCommiss, $_ROUNDDEC);
 $totVat = round($rateVat,$_ROUNDDEC);
 $totTotal = $totAmount + $totVat;

 // in caso di arrotondamenti carico la maggiorazone sull'ultima rata
 $diff = $amount - ($rateAmount * 12 / $periodicity);
 $finalRateAmount = round($rateAmount+$diff, $_ROUNDDEC);
 $finalRateVat = ($finalRateAmount/100) * $vatRate;
 $finalRateVat+= $commissVat;
 $finalTotAmount = round($finalRateAmount + $rateCommiss, $_ROUNDDEC);
 $finalTotVat = round($finalRateVat,$_ROUNDDEC);
 $finalTotTotal = $finalTotAmount + $finalTotVat;

 $dF = strtotime($dateFrom);
 $currDT = strtotime(date('Y-m',$dF)."-01");
 $day = date('d',$dF);

 for($c=0; $c < $totRates; $c++)
 {
  $currDT = strtotime("+".$periodicity." months",$currDT);

  if($payDay)
  {
   switch($payDay)
   {
    case 'fm' : case 'FM' : $date = date('Y-m',$currDT)."-".date('t',$currDT); break;
    default : {
	 if($payDay > date('t',$currDT))
	  $date = date('Y-m',$currDT)."-".date('t',$currDT);
	 else
	  $date = date('Y-m',$currDT)."-".$payDay;
	} break;
   }
  }
  else
  {
   if($day > date('t',$currDT))
	$date = date('Y-m',$currDT)."-".date('t',$currDT);
   else
    $date = date('Y-m',$currDT)."-".$day;
  }

  // in caso di arrotondamenti la maggiorazione avverrà sull'ultima rata
  if($c == ($totRates-1))
   $a = array('expiry'=>$date, 'amount'=>$finalRateAmount, 'commiss'=>$rateCommiss, 'tot_amount'=>$finalTotAmount, 'tot_vat'=>$finalTotVat, 'tot_total'=>$finalTotTotal);
  else
   $a = array('expiry'=>$date, 'amount'=>$rateAmount, 'commiss'=>$rateCommiss, 'tot_amount'=>$totAmount, 'tot_vat'=>$totVat, 'tot_total'=>$totTotal);
  $outArr['deadlines'][] = $a;
 }

 
 if($verbose)
 {
  $out.= "<table border='0' cellspacing='10'>";
  $out.= "<tr><th>SCADENZA</th><th>CANONE</th><th>COMMISS.</th><th>IVA</th><th>TOTALE</th></tr>";
  for($c=0; $c < count($outArr['deadlines']); $c++)
  {
   $item = $outArr['deadlines'][$c];
   $out.= "<tr><td>".date('d/m/Y',strtotime($item['expiry']))."</td>";
   $out.= "<td align='right'>".number_format($item['amount'],$_DECIMALS,',','.')." &euro;</td>";
   $out.= "<td align='right'>".number_format($item['commiss'],$_DECIMALS,',','.')." &euro;</td>";
   $out.= "<td align='right'>".number_format($item['tot_vat'],$_DECIMALS,',','.')." &euro;</td>";
   $out.= "<td align='right'>".number_format($item['tot_total'],$_DECIMALS,',','.')." &euro;</td></tr>";
  }
  $out.= "</table>";
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_calcrate_invalidArguments($args, $sessid, $shellid)
{
 $out = "USAGE: calcrate [OPTIONS].\n";
 $out.= " -amount [CURRENCY] : Totale imponibile.\n";
 $out.= " -vatrate [PERCENTAGE]: Aliq. iva su imponibile.\n";
 $out.= " -commiss [CURRENCY]: Importo commissione per ogni rata.\n";
 $out.= " -commissvatrate [PERCENTAGE]: Aliq. iva su commissione.\n";
 $out.= " -periodicity | -freq [NUMBER]: Periodicità in mesi.\n";
 $out.= " -months [NUMBER]: Durata contratto in mesi.\n";
 $out.= " -payday [NUMBER or STRING]: Giorno pagamento. Può essere un giorno fisso oppure FM (fine mese).\n";
 $out.= " -from | -datefrom [YYYY-MM-DD]: Data inizio scadenze (data inizio contratto).\n";
 $out.= " -verbose | --verbose : Mostra l'output.\n";

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
