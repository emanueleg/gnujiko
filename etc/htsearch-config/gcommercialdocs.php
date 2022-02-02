<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 26-05-2013
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: HackTVSearch dictionary file for GCommercialDocs.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_gcommercialdocs_info($sessid=0, $shellid=0)
{
 $retInfo = array("commands"=>array(), "functions"=>array(), "variables"=>array());

 /* --- VARIABLES ---------------------------------------------------------------------*/
 $retInfo['variables'][] = array("name"=>"CONTACT-TYPE", "title"=>"Tipo di contatto");
 $retInfo['variables'][] = array("name"=>"CUSTOMER", "title"=>"Cliente");
 $retInfo['variables'][] = array("name"=>"VENDOR", "title"=>"Fornitore");
 $retInfo['variables'][] = array("name"=>"SHIPPER", "title"=>"Vettore / Trasportatore");
 $retInfo['variables'][] = array("name"=>"AGENT", "title"=>"Agente");
 $retInfo['variables'][] = array("name"=>"CONTACT", "title"=>"Contatto");

 $retInfo['variables'][] = array("name"=>"GCD-TYPE", "title"=>"Tipo di documento");
 $retInfo['variables'][] = array("name"=>"PREEMPTIVE", "title"=>"Preventivo");
 $retInfo['variables'][] = array("name"=>"ORDER", "title"=>"Ordine");
 $retInfo['variables'][] = array("name"=>"DDT", "title"=>"D.D.T.");
 $retInfo['variables'][] = array("name"=>"INVOICE", "title"=>"Fattura");
 $retInfo['variables'][] = array("name"=>"PURCHASEINVOICE", "title"=>"Fattura d'acquisto");
 $retInfo['variables'][] = array("name"=>"VENDORORDER", "title"=>"Ordine fornitore");
 $retInfo['variables'][] = array("name"=>"AGENTINVOICE", "title"=>"Fattura agente");
 $retInfo['variables'][] = array("name"=>"INTERVREPORT", "title"=>"Rapp. d'intervento");
 $retInfo['variables'][] = array("name"=>"CREDITSNOTE", "title"=>"Nota di credito");
 $retInfo['variables'][] = array("name"=>"DEBITSNOTE", "title"=>"Nota di debito");
 $retInfo['variables'][] = array("name"=>"PAYMENTNOTICE", "title"=>"Avv. di pagamento");

 /* COMMANDS */
 $retInfo['commands'][] = array(
	 "name" => "Mostra documenti filtrati per clienti/fornitori o per data",
	 "exp" => "[riepilogo|mostra|visualizza|lista] {GCD-TYPE} [di|del] [<DATE>|<MONTH>|<YEAR>|{CUSTOMER}]",
	 "extended" => "[dal|a partire da] <DATE> a [oggi|<DATE>]",
	 "callfunc" => "gcommercialdocs_documentlist"
	);

 /*$retInfo['commands'][] = array(
	 "name" => "Mostra documenti di uno specifico cliente/fornitore per data",
	 "exp" => "[riepilogo di tutte le|riepilogo di tutti i|riepilogo di tutti gli|mostra tutte le|mostra tutti i|mostra tutti gli|visualizza tutte|visualizza tutti i|visualizza tutti gli] {GCD-TYPE} [di|del] {CUSTOMER} [di|del] [<DATE>|<MONTH>|<YEAR>]",
	 "extended" => "[dal|a partire da] <DATE> a [oggi|<DATE>]"
	);*/

 $retInfo['commands'][] = array(
	 "name" => "Mostra documenti di intestatari non registrati in rubrica",
	 "exp" => "[riepilogo|mostra|visualizza|lista] {GCD-TYPE} di [clienti|fornitori|soggetti|intestatari] [sconosciuti|non registrati nella rubrica]",
	 "callfunc" => "gcommercialdocs_documentlist_unregistered"
	);

 /*$retInfo['commands'][] = array(
	 "name" => "Stampa la lista dei contatti",
	 "exp" => "[stampa|mostra] {CONTACT-TYPE}"
	);

 $retInfo['commands'][] = array(
	 "name" => "Esporta la lista dei contatti",
	 "exp" => "esporta {CONTACT-TYPE} su [file xml|file excel]"
	);

 $retInfo['commands'][] = array(
	 "name" => "Esporta la lista dei documenti",
	 "exp" => "esporta {GCD-TYPE} su [file xml|file excel]"
	);

 $retInfo['commands'][] = array(
	 "name" => "Importa la lista dei contatti",
	 "exp" => "importa {CONTACT-TYPE} da [file xml|file excel]"
	);*/

 return $retInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_gcommercialdocs_search($query="", $sessid=0, $shellid=0)
{
 /*$outArr = array('sections'=>array());
 $outArr['sections'][] = array("type"=>"search", "tag"=>"rubricasearch", "title"=>"RICERCA NEI CONTATTI",  "results"=>array());

 $db = new AlpaDatabase();
 $query = $db->Purify($query);

 $qry = "(name LIKE '".$query."%') OR (name LIKE '%".$query."') OR (name LIKE '%".$query."%')";
 $qry.= " OR ((code_str='".$query."') OR (code_str LIKE '%".$query."'))";

 $db->RunQuery("SELECT id,name,code_str FROM dynarc_rubrica_items WHERE trash='0' AND (".$qry.") ORDER BY name ASC LIMIT 5");
 while($db->Read())
 {
  $outArr['sections'][0]['results'][] = array(
	 'id'=>$db->record['id'], 
	 'name'=>$db->record['name'],
	 'link'=>array('title'=>"vedi anagrafica &raquo;", "command"=>"gframe -f rubrica.edit -params `id=".$db->record['id']."`")
	);
 }
 $db->Close();


 return array('message'=>"done",'outarr'=>$outArr);*/
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_gcommercialdocs_varsearch($varName, $query="", $sessid=0, $shellid=0)
{
 $retInfo = array("result"=>"", "suggested"=>array(), "query"=>$query);
 $doclist = null;

 /* RISULTATI DA RITORNARE IN CASO DI QUERY VUOTA */
 if(!$query)
 {
  switch($varName)
  {
   /* COMMERCIAL DOCS */
   case 'GCD-TYPE' : {
	 $ret = GShell("dynarc cat-list -ap `commercialdocs`",$sessid,$shellid);
	 $doclist = $ret['outarr'];
	 for($c=0; $c < count($doclist); $c++)
	  $retInfo['suggested'][] = $doclist[$c]['name'];
	 return $retInfo;
	} break;

   /* RUBRICA */
   case 'CONTACT-TYPE' : {
	 $ret = GShell("dynarc cat-list -ap `rubrica`",$sessid,$shellid);
	 $rublist = $ret['outarr'];
	 for($c=0; $c < count($rublist); $c++)
	  $retInfo['suggested'][] = $rublist[$c]['name'];
	 return $retInfo;
	} break;

  }
 }
 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

 /* Se la query è composta da piu parole (quindi separate da uno spazio) la scompone fino a trovare almeno un risultato */
 while($query)
 {
  $retInfo['suggested'] = array();
  $retInfo['query'] = $query;
  $retInfo['result'] = "";
  switch($varName)
  {
   /* COMMERCIAL DOCS */
   case 'GCD-TYPE' : {
	 if(!$doclist)
	 {
	  $ret = GShell("dynarc cat-list -ap `commercialdocs`",$sessid,$shellid);
	  $doclist = $ret['outarr'];
	 }
	 for($c=0; $c < count($doclist); $c++)
	 {
	  if(strtolower($doclist[$c]['name']) == strtolower($query))
	   $retInfo['result'] = $doclist[$c]['name'];
	  else if(strtolower(substr($doclist[$c]['name'],0,strlen($query))) == strtolower($query))
	   $retInfo['suggested'][] = $doclist[$c]['name'];
	 }
	} break;

   /* RUBRICA */
   case 'CONTACT-TYPE' : {
	 if(!$rublist)
	 {
	  $ret = GShell("dynarc cat-list -ap `rubrica`",$sessid,$shellid);
	  $rublist = $ret['outarr'];
	 }
	 for($c=0; $c < count($rublist); $c++)
	 {
	  if(strtolower($rublist[$c]['name']) == strtolower($query))
	   $retInfo['result'] = $rublist[$c]['name'];
	  else if(strtolower(substr($rublist[$c]['name'],0,strlen($query))) == strtolower($query))
	   $retInfo['suggested'][] = $rublist[$c]['name'];
	 }
	} break;

   case 'CUSTOMER' : case 'VENDOR' : case 'SHIPPER' : case 'AGENT' : case 'CONTACT' : {
	 $db = new AlpaDatabase();
	 $query = $db->Purify($query);

	 $qry = "(name LIKE '".$query."%') OR (name LIKE '%".$query."') OR (name LIKE '%".$query."%')";
	 $qry.= " OR ((code_str='".$query."') OR (code_str LIKE '%".$query."'))";

	 $db->RunQuery("SELECT id,name,code_str FROM dynarc_rubrica_items WHERE trash='0' AND (".$qry.") ORDER BY name ASC LIMIT 5");
	 while($db->Read())
	 {
	  if(strtolower($db->record['name']) == strtolower($query))
	   $retInfo['result'] = $db->record['name'];
	  else if(strtolower($db->record['code_str']) == strtolower($query))
	   $retInfo['result'] = $db->record['name'];
	  else
	   $retInfo['suggested'][] = $db->record['name'];
	 }
	 $db->Close();
	} break;

  }

  if($retInfo['result'] || count($retInfo['suggested']))
   return $retInfo;
  
  if(($p=strrpos($query," ")) !== false)
   $query = substr($query,0,$p);
  else
   return $retInfo;
 }
 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

 return $retInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_gcommercialdocs_documentlist($keys, $sessid, $shellid)
{
 $out = "";
 $title = "";

 $_MONTHS = array("gennaio","febbraio","marzo","aprile","maggio","giugno","luglio","agosto","settembre","ottobre","novembre","dicembre");

 $dateFrom = null;
 $dateTo = null;

 $_doctype = $keys[1]['value'];
 $title = "Lista ".$_doctype;

 /* Get cat info */
 $ret = GShell("dynarc cat-find -ap commercialdocs `".$_doctype."`",$sessid,$shellid);
 if(!$ret['error'])
  $catInfo = $ret['outarr'][0];

 switch($keys[3]['type'])
 {
  case 'DATE' : {
	 $listByDate=true; 
	 $title.= " del ".$keys[3]['value'];
	 $date = str_replace("/","-",$keys[3]['value']);
	 $x = explode("-",$date);
	 $dateFrom = $x[2]."-".$x[1]."-".$x[0];
	 $from = strtotime($dateFrom);
	 $dateTo = date('Y-m-d',strtotime("+1 day",$from));
	} break;
  case 'MONTH' : {
	 $listByDate=true; 
	 $title.= " di ".ucfirst($keys[3]['value']);
	 $month = array_search(strtolower($keys[3]['value']),$_MONTHS);
	 if($month !== false)
	  $month++;
	 $from = strtotime(date("Y")."-".($month < 10 ? "0".$month : $month)."-01");
	 $dateFrom = date('Y-m-d',$from);
	 $dateTo = date('Y-m-d',strtotime("+1 month",$from));
	} break;
  case 'YEAR' : {
	 $listByDate=true;
	 $year = $keys[3]['value'];
	 $title.= " del ".$year;
	 $dateFrom = $year."-01-01";
	 $from = strtotime($dateFrom);
	 $dateTo = date('Y-m-d',strtotime("+1 year",$from));
	} break;
  case 'VAR' : {
	 $listByCustomer=$keys[3]['value'];
	 $title.= " di ".$keys[3]['value'];
	 /* get customer info */
	 $ret = GShell("dynarc item-info -ap rubrica `".$keys[3]['value']."`",$sessid,$shellid);
	 if(!$ret['error'])
	  $customerInfo = $ret['outarr'];
	 else
	  $customerName = $keys[3]['value'];
	} break;
 }

 $outArr = array();
 $outArr['layer'] = "commdocslist";
 $outArr['title'] = $title;
 $outArr['width'] = 600;
 $outArr['height'] = 480;
 $outArr['fields'] = array(
	 0 => array('id'=>'name','title'=>"DOCUMENTO",'width'=>140, 'autolink'=>'commercialdocs'),
	 1 => array('id'=>'subject_name','title'=>"INTESTATARIO"),
	 2 => array('id'=>'amount','title'=>"IMPORTO", 'width'=>70, 'format'=>'currency', 'decimals'=>2, 'hidden'=>true),
	 3 => array('id'=>'discount','title'=>"SCONTO", 'width'=>70, 'format'=>'currency', 'decimals'=>2, 'hidden'=>true),
	 4 => array('id'=>'vat','title'=>"I.V.A.", 'width'=>70, 'format'=>'currency', 'decimals'=>2, 'hidden'=>true),
	 5 => array('id'=>'total','title'=>"TOTALE", 'width'=>70, 'format'=>'currency', 'decimals'=>2)
	);
 
 /* FILTER BY CUSTOMER */
 if($listByCustomer)
 {
  $where = $customerInfo ? "subject_id='".$customerInfo['id']."' OR subject_name='".$customerInfo['name']."'" : "subject_name='".$customerName."'";
  $outArr['query'] = "dynarc item-list -ap commercialdocs -ct `".$catInfo['tag']."` -where `".$where."` -extget cdinfo";
 }
 else if($listByDate)
 {
  $where = "ctime>='".$dateFrom."' AND ctime<'".$dateTo."'";
  $outArr['query'] = "dynarc item-list -ap commercialdocs -ct `".$catInfo['tag']."` -where `".$where."` -extget cdinfo";
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_gcommercialdocs_documentlist_unregistered($keys, $sessid, $shellid)
{
 $out = "";
 $title = "";

 $_doctype = $keys[1]['value'];
 $title = "Lista ".$_doctype." di intestatari non registrati in rubrica";

 /* Get cat info */
 $ret = GShell("dynarc cat-find -ap commercialdocs `".$_doctype."`",$sessid,$shellid);
 if(!$ret['error'])
  $catInfo = $ret['outarr'][0];

 $outArr = array();
 $outArr['layer'] = "commdocslist";
 $outArr['title'] = $title;
 $outArr['width'] = 480;
 $outArr['height'] = 520;
 $outArr['fields'] = array(
	 0 => array('id'=>'name','title'=>"DOCUMENTO",'width'=>140, 'autolink'=>'commercialdocs'),
	 1 => array('id'=>'subject_name','title'=>"INTESTATARIO"),
	 2 => array('id'=>'amount','title'=>"IMPORTO", 'width'=>70, 'format'=>'currency', 'decimals'=>2, 'hidden'=>true),
	 3 => array('id'=>'discount','title'=>"SCONTO", 'width'=>70, 'format'=>'currency', 'decimals'=>2, 'hidden'=>true),
	 4 => array('id'=>'vat','title'=>"I.V.A.", 'width'=>70, 'format'=>'currency', 'decimals'=>2, 'hidden'=>true),
	 5 => array('id'=>'total','title'=>"TOTALE", 'width'=>70, 'format'=>'currency', 'decimals'=>2)
	);
 
 /* FILTER BY CUSTOMER */
 $where = "subject_id='0' AND subject_name!=''";
 $outArr['query'] = "dynarc item-list -ap commercialdocs -ct `".$catInfo['tag']."` -where `".$where."` -extget cdinfo";

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

