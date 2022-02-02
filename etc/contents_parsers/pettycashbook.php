<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-08-2013
 #PACKAGE: bookkeeping
 #DESCRIPTION: Pettycashbook parser
 #VERSION: 2.2beta
 #CHANGELOG: 13-08-2013 : Aggiunto i totali.
 #TODO:
 
*/

function gnujikocontentparser_pettycashbook_info($sessid, $shellid)
{
 $info = array('name' => "Prima nota");
 $keys = array(
	 "QRY_SUBJECT" => "Soggetto",
	 "QRY_DESC" => "Descrizione",
	 "DATE_FROM" => "Dal", 
	 "DATE_TO" => "Al",
	 "CAT_NAME" => "Nome categoria",
	 "TOT_IN" => "Tot. entrate",
	 "TOT_OUT" => "Tot. uscite",
	 "DOC_PG" => "Pagina"
	);
 return array('info'=>$info, 'keys'=>$keys);
}

function gnujikocontentparser_pettycashbook_parse($_CONTENTS, $_PARAMS, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_COMPANY_PROFILE;
 include_once($_BASE_PATH."include/company-profile.php");
 $_BANKS = $_COMPANY_PROFILE['banks'];
 $_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'];

 $contents = $_CONTENTS;

 $qrySubject = $_PARAMS['qrysubject'];
 $qryDesc = $_PARAMS['qrydesc'];
 $dateFrom = $_PARAMS['from'];
 $dateTo = $_PARAMS['to'];

 $docRef = "";
 $catName = "";
 $totIn = number_format($_PARAMS['totincomes'],2,",",".");
 $totOut = number_format($_PARAMS['totexpenses'],2,",",".");
 $docPG = $_REQUEST['page'] ? $_REQUEST['page'] : 1;

 if($_REQUEST['catid'])
 {
  $ret = GShell("dynarc cat-info -ap pettycashbook -id `".$_REQUEST['catid']."`",$sessid,$shellid);
  if(!$ret['error'])
   $catName = $ret['outarr']['name'];
 }

 $keys = array("{QRY_SUBJECT}", "{QRY_DESC}", "{DATE_FROM}" , "{DATE_TO}", "{CAT_NAME}", "{TOT_IN}", "{TOT_OUT}", "{DOC_PG}");
 $vals = array($qrySubject, $qryDesc, $dateFrom, $dateTo, $catName, $totIn, $totOut, $docPG);

 for($c=0; $c < count($keys); $c++)
 {
  $key = $keys[$c];
  $val = $vals[$c];

  while($p = stripos($contents,$key,$p))
  {
   $chunk = strtoupper(substr($contents,$p-4,4));
   if(($chunk == "ID='") || ($chunk == 'ID="'))
   {// is inside on html tag //
    $endTag = stripos($contents,">",$p+strlen($key));
    $contents = substr($contents,0,$endTag+1).$val.substr($contents,$endTag+1);
    $p = $endTag+strlen($val);
   }
   else
   {
    $contents = substr($contents,0,$p).$val.substr($contents,$p+strlen($key));
    $p+= strlen($val);
   }
  }
 }

 $_CONTENTS = $contents;
 return $contents;
}


