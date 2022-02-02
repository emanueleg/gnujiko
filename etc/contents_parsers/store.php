<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-11-2012
 #PACKAGE: gstore
 #DESCRIPTION: Official Gnujiko Store parser.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function gnujikocontentparser_store_info($sessid, $shellid)
{
 $info = array('name' => "Magazzino");
 $keys = array(
	 "DATE_FROM" => "Dal", 
	 "DATE_TO" => "Al",
	 "SEARCH" => "Ricerca"
	);
 return array('info'=>$info, 'keys'=>$keys);
}

function gnujikocontentparser_store_parse($_CONTENTS, $_PARAMS, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_COMPANY_PROFILE;
 include_once($_BASE_PATH."include/company-profile.php");
 $_BANKS = $_COMPANY_PROFILE['banks'];
 $_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'];

 $contents = $_CONTENTS;

 $dateFrom = $_PARAMS['from'];
 $dateTo = $_PARAMS['to'];
 $search = $_PARAMS['search'];

 $keys = array("{DATE_FROM}" , "{DATE_TO}", "{SEARCH}");

 $vals = array($dateFrom, $dateTo, $search);

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


