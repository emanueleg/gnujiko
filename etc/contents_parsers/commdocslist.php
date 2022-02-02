<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 03-06-2013
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: CommercialDocs parser
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

function gnujikocontentparser_commdocslist_info($sessid, $shellid)
{
 $info = array('name' => "Lista di documenti commerciali");
 $keys = array(
	 "TITLE" => "Titolo",
	 "TOT_AMOUNT" => "Totale importo",
	 "TOT_VAT" => "Totale IVA",
	 "SUB_TOT" => "Sub totale",
	 "CAT_NAME" => "Nome della categoria",
	 "SUBJECT_NAME" => "Nome dell'intestatario", /* <-- in caso di filtri per intestatario */
	 "DATE_FROM" => "Data inizio", /* in caso di filtri per data */
	 "DATE_TO" => "Data fine", /* in caso di filtri per data */
	 "PAYMENT_DATE" => "Data pagamento", /* in caso di filtri per data di pagamento */
	 "PRINT_DATE" => "Data di stampa",
	 "SEND_DATE" => "Data invio documento",
	 "CHARTER_DATEFROM" => "Data inizio noleggio",
	 "CHARTER_DATETO" => "Data fine noleggio"
	);
 return array('info'=>$info, 'keys'=>$keys);
}

function gnujikocontentparser_commdocslist_parse($_CONTENTS, $_PARAMS, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_COMPANY_PROFILE;
 include_once($_BASE_PATH."include/company-profile.php");
 $_BANKS = $_COMPANY_PROFILE['banks'];
 $_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'];

 $contents = $_CONTENTS;


$keys = array(
	 "{TITLE}", "{TOT_AMOUNT}", "{TOT_VAT}", "{SUB_TOT}",
	 "{CAT_NAME}", "{SUBJECT_NAME}", 
	 "{DATE_FROM}", "{DATE_TO}", 
	 "{PAYMENT_DATE}", 
	 "{PRINT_DATE}", 
	 "{SEND_DATE}", 
	 "{CHARTER_DATEFROM}", "{CHARTER_DATETO}"
	);

$vals = array(
	 $_PARAMS['doctitle'], 
	 number_format($_PARAMS['totamount'],2,",","."), 
	 number_format($_PARAMS['totvat'],2,",","."),
	 number_format($_PARAMS['subtot'],2,",","."),
	 $_PARAMS['catname'], $_PARAMS['subjectname'],
	 $_PARAMS['datefrom'], $_PARAMS['dateto'],
	 $_PARAMS['paymentdate'],
	 $_PARAMS['printdate'],
	 $_PARAMS['senddate'],
	 $_PARAMS['charterdatefrom'], $_PARAMS['charterdateto']
	);

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


