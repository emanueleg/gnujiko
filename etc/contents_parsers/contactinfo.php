<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-09-2016
 #PACKAGE: rubrica
 #DESCRIPTION: Rubrica contact info - parser.
 #VERSION: 2.1beta
 #CHANGELOG: 17-09-2016 : Aggiunto nl2br su note.
 #TODO:
 
*/

function gnujikocontentparser_contactinfo_info($sessid, $shellid)
{
 $info = array('name' => "Scheda cliente");
 $keys = array(
	 "SUBJ_CODE" => "Cod. cliente",
	 "SUBJ_NAME" => "Nome Cognome - Rag. sociale",
	 "SUBJ_ADDRESS" => "Indirizzo",
	 "SUBJ_CITY" => "Città",
	 "SUBJ_ZIP" => "C.A.P.",
	 "SUBJ_PROV" => "Provincia",
	 "SUBJ_TAXCODE" => "Cod. Fisc.",
	 "SUBJ_VATNUMBER" => "Partita IVA",
	 "SUBJ_PHONE" => "Telefono",
	 "SUBJ_PHONE2" => "Telefono2",
	 "SUBJ_FAX" => "Fax",
	 "SUBJ_CELL" => "Cellulare",
	 "SUBJ_EMAIL" => "Email",
	 "SUBJ_FIDELITYCARD" => "Cod. Fidelity Card",
	 "SUBJ_AGENTNAME" => "Agente di rif.",

	 "REF_1_NAME" => "Rif #1 - Nome e Cognome",
	 "REF_1_TYPE" => "Rif #1 - Tipologia",
	 "REF_1_PHONE" => "Rif #1 - Telefono",
	 "REF_1_EMAIL" => "Rif #1 - Email",
	 "REF_2_NAME" => "Rif #2 - Nome e Cognome",
	 "REF_2_TYPE" => "Rif #2 - Tipologia",
	 "REF_2_PHONE" => "Rif #2 - Telefono",
	 "REF_2_EMAIL" => "Rif #2 - Email",
	 "REF_3_NAME" => "Rif #3 - Nome e Cognome",
	 "REF_3_TYPE" => "Rif #3 - Tipologia",
	 "REF_3_PHONE" => "Rif #3 - Telefono",
	 "REF_3_EMAIL" => "Rif #3 - Email",
	 "REF_4_NAME" => "Rif #4 - Nome e Cognome",
	 "REF_4_TYPE" => "Rif #4 - Tipologia",
	 "REF_4_PHONE" => "Rif #4 - Telefono",
	 "REF_4_EMAIL" => "Rif #4 - Email",
	 "REF_5_NAME" => "Rif #5 - Nome e Cognome",
	 "REF_5_TYPE" => "Rif #5 - Tipologia",
	 "REF_5_PHONE" => "Rif #5 - Telefono",
	 "REF_5_EMAIL" => "Rif #5 - Email",

	 "SUBJ_NOTE" => "Note",
	 "SUBJ_EXTRANOTES" => "Note extra",
 
	);
 return array('info'=>$info, 'keys'=>$keys);
}

function gnujikocontentparser_contactinfo_parse($_CONTENTS, $_PARAMS, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;

 $contents = $_CONTENTS;

 // CONTACT INFO //
 $subjInfo = array('contacts'=>array(), 'references'=>array());
 $ret = GShell("dynarc item-info -ap rubrica -id '".$_PARAMS['id']."' -extget `rubricainfo,contacts,banks,references`",$sessid,$shellid);
 if(!$ret['error'])
 {
  $subjInfo = $ret['outarr'];
 }

 $keys = array(
	 "{SUBJ_CODE}",
	 "{SUBJ_NAME}", 
	 "{SUBJ_ADDRESS}", 
	 "{SUBJ_CITY}", 
	 "{SUBJ_ZIP}",
	 "{SUBJ_PROV}",
	 "{SUBJ_TAXCODE}",
	 "{SUBJ_VATNUMBER}",
	 "{SUBJ_PHONE}",
	 "{SUBJ_PHONE2}",
	 "{SUBJ_FAX}",
	 "{SUBJ_CELL}",
	 "{SUBJ_EMAIL}",
	 "{SUBJ_FIDELITYCARD}",
	 "{SUBJ_AGENTNAME}",

	 "{REF_1_NAME}",
	 "{REF_1_TYPE}",
	 "{REF_1_PHONE}",
	 "{REF_1_EMAIL}",
	 "{REF_2_NAME}",
	 "{REF_2_TYPE}",
	 "{REF_2_PHONE}",
	 "{REF_2_EMAIL}",
	 "{REF_3_NAME}",
	 "{REF_3_TYPE}",
	 "{REF_3_PHONE}",
	 "{REF_3_EMAIL}",
	 "{REF_4_NAME}",
	 "{REF_4_TYPE}",
	 "{REF_4_PHONE}",
	 "{REF_4_EMAIL}",
	 "{REF_5_NAME}",
	 "{REF_5_TYPE}",
	 "{REF_5_PHONE}",
	 "{REF_5_EMAIL}",

	 "{SUBJ_NOTE}",
	 "{SUBJ_EXTRANOTES}",
	);

 $vals = array(
	 $subjInfo['code_str'],
	 $subjInfo['name'],
	 count($subjInfo['contacts']) ? $subjInfo['contacts'][0]['address'] : "",
	 count($subjInfo['contacts']) ? $subjInfo['contacts'][0]['city'] : "",
	 count($subjInfo['contacts']) ? $subjInfo['contacts'][0]['zipcode'] : "",
	 count($subjInfo['contacts']) ? $subjInfo['contacts'][0]['province'] : "",
	 $subjInfo['taxcode'],
	 $subjInfo['vatnumber'],
	 count($subjInfo['contacts']) ? $subjInfo['contacts'][0]['phone'] : "",
	 count($subjInfo['contacts']) ? $subjInfo['contacts'][0]['phone2'] : "",
	 count($subjInfo['contacts']) ? $subjInfo['contacts'][0]['fax'] : "",
	 count($subjInfo['contacts']) ? $subjInfo['contacts'][0]['cell'] : "",
	 count($subjInfo['contacts']) ? $subjInfo['contacts'][0]['email'] : "",
	 $subjInfo['fidelitycard'],
	 $subjInfo['agent_name'],
	 
	 count($subjInfo['references']) ? $subjInfo['references'][0]['name'] : "",
	 count($subjInfo['references']) ? $subjInfo['references'][0]['type'] : "",
	 count($subjInfo['references']) ? $subjInfo['references'][0]['phone'] : "",
	 count($subjInfo['references']) ? $subjInfo['references'][0]['email'] : "",

	 count($subjInfo['references']) > 1 ? $subjInfo['references'][1]['name'] : "",
	 count($subjInfo['references']) > 1 ? $subjInfo['references'][1]['type'] : "",
	 count($subjInfo['references']) > 1 ? $subjInfo['references'][1]['phone'] : "",
	 count($subjInfo['references']) > 1 ? $subjInfo['references'][1]['email'] : "",

	 count($subjInfo['references']) > 2 ? $subjInfo['references'][2]['name'] : "",
	 count($subjInfo['references']) > 2 ? $subjInfo['references'][2]['type'] : "",
	 count($subjInfo['references']) > 2 ? $subjInfo['references'][2]['phone'] : "",
	 count($subjInfo['references']) > 2 ? $subjInfo['references'][2]['email'] : "",

	 count($subjInfo['references']) > 3 ? $subjInfo['references'][3]['name'] : "",
	 count($subjInfo['references']) > 3 ? $subjInfo['references'][3]['type'] : "",
	 count($subjInfo['references']) > 3 ? $subjInfo['references'][3]['phone'] : "",
	 count($subjInfo['references']) > 3 ? $subjInfo['references'][3]['email'] : "",

	 count($subjInfo['references']) > 4 ? $subjInfo['references'][4]['name'] : "",
	 count($subjInfo['references']) > 4 ? $subjInfo['references'][4]['type'] : "",
	 count($subjInfo['references']) > 4 ? $subjInfo['references'][4]['phone'] : "",
	 count($subjInfo['references']) > 4 ? $subjInfo['references'][4]['email'] : "",

	 nl2br($subjInfo['desc']),
	 $subjInfo['extranotes']

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


