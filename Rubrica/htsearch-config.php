<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-04-2013
 #PACKAGE: rubrica
 #DESCRIPTION: HackTVSearch dictionary file for Rubrica.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_search($query="", $sessid=0, $shellid=0)
{
 $outArr = array('sections'=>array());
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
	 'action'=>array('title'=>"vedi anagrafica &raquo;", "command"=>"gframe -f rubrica.edit -params `id=".$db->record['id']."`")
	);
 }
 $db->Close();

 return array('message'=>"done",'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_varsearch($varName, $query="", $sessid=0, $shellid=0)
{
 $retInfo = array("result"=>"", "suggested"=>array(), "query"=>$query);
 return $retInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_info($sessid=0, $shellid=0)
{
 $retInfo = array("commands"=>array(), "functions"=>array(), "variables"=>array());

 $retInfo['commands'][] = array(
	 "name" => "Mostra doppioni",
	 "exp" => "[cerca|mostra|visualizza|lista] doppioni",
	 "callfunc" => "rubrica_getDuplicate"
	);

 $retInfo['commands'][] = array(
	 "name" => "Verifica se ci sono contatti con il numero di partita iva mancante o non valido",
	 "exp" => "[verifica|controlla] partite iva di tutti i nominativi",
	 "callfunc" => "rubrica_validateVatNumbers"
	);

 return $retInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_rubrica_getDuplicate($keys, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;

 $out = "";
 $title = "";

 $title = "Lista dei doppioni in rubrica";

 $outArr = array();
 $outArr['layer'] = "emptytable";
 $outArr['title'] = $title;
 $outArr['width'] = 800;
 $outArr['height'] = 600;
 $outArr['fields'] = array(
	 0 => array('id'=>'id','title'=>"ID",'width'=>60),
	 1 => array('id'=>'code','title'=>"COD.", 'width'=>60),
	 2 => array('id'=>'name','title'=>"NOME E COGNOME / RAG. SOCIALE", 'width'=>200, 'autolink'=>'rubrica'),
	 3 => array('id'=>'doclinks','title'=>"DOC. INTESTATI", 'width'=>120),
	 4 => array('id'=>'contacts','title'=>"RECAPITI", 'width'=>120)
	);
 
 $lastName = "";
 $lastItem = array();
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_rubrica_items WHERE trash='0' ORDER BY name ASC");
 while($db->Read())
 {
  $a = array("id"=>$db->record['id'], "uid"=>$db->record['uid'], "gid"=>$db->record['gid'], "_mod"=>$db->record['mod'], "cat_id"=>$db->record['cat_id'],
	'name'=>$db->record['name'], 'ctime'=>$db->record['ctime'], 'mtime'=>$db->record['mtime'], 'code'=>$db->record['code_str'], 
	'taxcode'=>$db->record['taxcode'], 'vatnumber'=>$db->record['vatnumber'], 'fidelitycard'=>$db->record['fidelitycard'], 
	'agent_id'=>$db->record['agent_id'], 'user_id'=>$db->record['user_id'], 'login'=>$db->record['login']);
  if(!$a['name'])
   $a['name'] = "senza nome";

  if(trim($db->record['name']) == $lastName)
  {
   if(!in_array($outArr['results'], $lastItem))
   {
	$lastItem = gnujikohtsearch_rubrica_getAddress($lastItem, $sessid, $shellid);
	$lastItem = gnujikohtsearch_rubrica_getDocuments($lastItem, $sessid, $shellid);
	for($c=0; $c < count($lastItem['documents']); $c++)
	 $lastItem['doclinks'].= "<a href='".$_ABSOLUTE_URL."GCommercialDocs/docinfo.php?id=".$lastItem['documents'][$c]['id']."' target='GCD-"
		.$lastItem['documents'][$c]['id']."'>".($lastItem['documents'][$c]['name'] ? $lastItem['documents'][$c]['name'] : 'senza nome')."</a><br/>";
    $outArr['results'][] = $lastItem;
   }
   $a = gnujikohtsearch_rubrica_getAddress($a, $sessid, $shellid);
   $a = gnujikohtsearch_rubrica_getDocuments($a, $sessid, $shellid);
   for($c=0; $c < count($a['documents']); $c++)
	$a['doclinks'].= "<a href='".$_ABSOLUTE_URL."GCommercialDocs/docinfo.php?id=".$a['documents'][$c]['id']."' target='GCD-"
		.$a['documents'][$c]['id']."'>".($a['documents'][$c]['name'] ? $a['documents'][$c]['name'] : 'senza nome')."</a><br/>";
   $outArr['results'][] = $a;
  }

  $lastName = trim($db->record['name']);
  $lastItem = $a;
 }
 $db->Close();

 /* FILTER BY CUSTOMER */
 //$where = "subject_id='0' AND subject_name!=''";
 //$outArr['query'] = "dynarc item-list -ap commercialdocs -ct `".$catInfo['tag']."` -where `".$where."` -extget cdinfo";

 return array('message'=>$out,'outarr'=>$outArr);

}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_rubrica_getAddress($a, $sessid, $shellid)
{
 $db2 = new AlpaDatabase();
 $db2->RunQuery("SELECT address,city,zipcode,province,phone,phone2,fax,cell,email FROM dynarc_rubrica_contacts WHERE item_id='".$a['id']."' ORDER BY isdefault DESC LIMIT 1");
 $db2->Read();
 $a['address'] = $db2->record['address'];
 $a['city'] = $db2->record['city'];
 $a['zipcode'] = $db2->record['zipcode'];
 $a['province'] = $db2->record['province'];
 $a['phone'] = $db2->record['phone'];
 $a['phone2'] = $db2->record['phone2'];
 $a['fax'] = $db2->record['fax'];
 $a['cell'] = $db2->record['cell'];
 $a['email'] = $db2->record['email'];
 $a['contacts'] = $a['address']." ".$a['city'].($a['province'] ? " (".$a['province'].")" : "");
 if($a['phone'])
  $a['contacts'].= " - tel.: ".$a['phone'];
 else if($a['cell'])
  $a['contacts'].= " - cell.: ".$a['cell'];
 $db2->Close();
 return $a;
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_rubrica_getDocuments($a, $sessid, $shellid)
{
 $a['documents'] = array();
 $db2 = new AlpaDatabase();
 $db2->RunQuery("SELECT id,name FROM dynarc_commercialdocs_items WHERE subject_id='".$a['id']."' AND trash='0'");
 while($db2->Read())
 {
  $a['documents'][] = array("id"=>$db2->record['id'], "name"=>$db2->record['name']);
 }
 $db2->Close();
 return $a;
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_rubrica_validateVatNumbers($keys, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;
 include_once($_BASE_PATH."include/taxcodevalidator.php");
 include_once($_BASE_PATH."include/vatnumbervalidator.php");

 $out = "";
 $title = "";

 $title = "Lista contatti con partita iva mancante o non valida";

 $outArr = array();
 $outArr['layer'] = "emptytable";
 $outArr['title'] = $title;
 $outArr['width'] = 800;
 $outArr['height'] = 600;
 $outArr['fields'] = array(
	 0 => array('id'=>'id','title'=>"ID",'width'=>60),
	 1 => array('id'=>'code','title'=>"COD.", 'width'=>60),
	 2 => array('id'=>'name','title'=>"NOME E COGNOME / RAG. SOCIALE", 'width'=>200, 'autolink'=>'rubrica'),
	 3 => array('id'=>'vatnumber','title'=>"PARTITA IVA", 'width'=>120),
	 4 => array('id'=>'taxcode','title'=>"CODICE FISCALE", 'width'=>120)
	);
 
 /* First get items without vatnumber and taxcode */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id,code_str,name FROM dynarc_rubrica_items WHERE trash='0' AND taxcode='' AND vatnumber='' ORDER BY name ASC");
 while($db->Read())
 {
  $a = array("id"=>$db->record['id'], "name"=>$db->record['name'], "code"=>$db->record['code_str'], "taxcode"=>"", "vatnumber"=>"");
  if(!$a['name'])
   $a['name'] = "senza nome";
  $outArr['results'][] = $a;
 }
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id,code_str,name,taxcode,vatnumber FROM dynarc_rubrica_items WHERE trash='0' AND (taxcode!='' OR vatnumber!='') ORDER BY name ASC");
 while($db->Read())
 {
  $a = array("id"=>$db->record['id'], 'name'=>$db->record['name'], 'code'=>$db->record['code_str'], 'taxcode'=>$db->record['taxcode'], 
	'vatnumber'=>$db->record['vatnumber']);
  if(!$a['name'])
   $a['name'] = "senza nome";
  if($db->record['taxcode'])
  {
   // verifica del codice fiscale
   if(!validateTaxCode($db->record['taxcode']) && !validateVatNumber($db->record['taxcode']))
	$outArr['results'][] = $a;
  }
  if($db->record['vatnumber'])
  {
   // verifica della partita iva
   if(!validateVatNumber($db->record['vatnumber']) && !in_array($outArr['results'], $a))
	$outArr['results'][] = $a;
  }
 }
 $db->Close();

 return array('message'=>$out,'outarr'=>$outArr);

}
//-------------------------------------------------------------------------------------------------------------------//


