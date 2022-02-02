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

 return $retInfo;
}
//-------------------------------------------------------------------------------------------------------------------//


