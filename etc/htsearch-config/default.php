<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 04-06-2013
 #PACKAGE: hacktvsearch-common
 #DESCRIPTION: Default HackTVSearch dictionary file.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_default_info($sessid=0, $shellid=0)
{
 $retInfo = array("commands"=>array(), "functions"=>array(), "variables"=>array());

 /* --- VARIABLES ---------------------------------------------------------------------*/
 /*$retInfo['variables'][] = array("name"=>"CONTACT-TYPE", "title"=>"Tipo di contatto");*/

 /* COMMANDS */
 $retInfo['commands'][] = array(
	 "name" => "Crea una nuova tabella",
	 "exp" => "[crea|nuova] tabella [vuota|tipo anagrafica|tipo elenco documenti|tipo elenco articoli|tipo elenco servizi]",
	 "callfunc" => "default_newemptytable"
	);


 return $retInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_default_search($query="", $sessid=0, $shellid=0)
{
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_default_varsearch($varName, $query="", $sessid=0, $shellid=0)
{
 $retInfo = array("result"=>"", "suggested"=>array(), "query"=>$query);
 return $retInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_default_newemptytable($keys, $sessid, $shellid)
{
 $out = "";
 $title = "Nuova tabella";

 $outArr = array();
 $outArr['layer'] = "emptytable";
 $outArr['title'] = $title;
 $outArr['width'] = 600;
 $outArr['height'] = 480;

 switch($keys[2]['value'])
 {
  case "tipo anagrafica" : {
	  $outArr['fields'] = array(
	 	 0 => array('id'=>'code_str','title'=>"CODICE",'width'=>60),
	 	 1 => array('id'=>'name','title'=>"NOME E COGNOME / RAG. SOCIALE"),
	 	 2 => array('id'=>'address','title'=>"INDIRIZZO", 'width'=>150),
	 	 3 => array('id'=>'phone','title'=>"TELEFONO", 'width'=>90)
		);
	} break;

  case "tipo elenco documenti" : {
	  $outArr['fields'] = array(
	 	 0 => array('id'=>'name','title'=>"DOCUMENTO",'width'=>140),
	 	 1 => array('id'=>'subject_name','title'=>"INTESTATARIO"),
	 	 2 => array('id'=>'amount','title'=>"IMPORTO", 'width'=>70, 'format'=>'currency', 'decimals'=>2, 'includeintototals'=>true, 'subtitle'=>'Imponibile'),
	 	 3 => array('id'=>'discount','title'=>"SCONTO", 'width'=>70, 'format'=>'currency', 'decimals'=>2, 'hidden'=>true),
	 	 4 => array('id'=>'vat','title'=>"I.V.A.", 'width'=>70, 'format'=>'currency', 'decimals'=>2, 'includeintototals'=>true, 'subtitle'=>'IVA'),
	 	 5 => array('id'=>'total','title'=>"TOTALE", 'width'=>70, 'format'=>'currency', 'decimals'=>2, 'includeintototals'=>true, 'subtitle'=>'Totale')
		);
	} break;

  case "tipo elenco articoli" : {
	  $outArr['fields'] = array(
	 	 0 => array('id'=>'code_str','title'=>"CODICE",'width'=>60),
	 	 1 => array('id'=>'name','title'=>"ARTICOLO", 'width'=>200),
	 	 2 => array('id'=>'description','title'=>"DESCRIZIONE"),
	 	 3 => array('id'=>'price','title'=>"PREZZO", 'width'=>70, 'format'=>'currency', 'decimals'=>2)
		);
	} break;

  case "tipo elenco servizi" : {
	  $outArr['fields'] = array(
	 	 0 => array('id'=>'code_str','title'=>"CODICE",'width'=>60),
	 	 1 => array('id'=>'name','title'=>"SERVIZIO", 'width'=>200),
	 	 2 => array('id'=>'description','title'=>"DESCRIZIONE"),
	 	 3 => array('id'=>'price','title'=>"PREZZO", 'width'=>70, 'format'=>'currency', 'decimals'=>2)
		);
	} break;

  default : {
	  $outArr['fields'] = array(
		 0 => array('id'=>'field-1', 'title'=>'A', 'width'=>100),
		 1 => array('id'=>'field-2', 'title'=>'B', 'width'=>100),
		 2 => array('id'=>'field-3', 'title'=>'C', 'width'=>100),
		 3 => array('id'=>'field-4', 'title'=>'D', 'width'=>100),
		 4 => array('id'=>'field-5', 'title'=>'E', 'width'=>100)
		);
	} break;
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

