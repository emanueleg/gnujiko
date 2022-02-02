<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-04-2013
 #PACKAGE: gserv
 #DESCRIPTION: HackTVSearch dictionary file for GServ.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_search($query="", $sessid=0, $shellid=0)
{
 $outArr = array('sections'=>array());
 $outArr['sections'][] = array("type"=>"search", "tag"=>"gservsearch", "title"=>"RICERCA NEI SERVIZI",  "results"=>array());

 $ret = GShell("dynarc search -at gserv -fields name,code_str -limit 5 `".$query."`",$sessid,$shellid);
 if(!$ret['error'])
 {
  $list = $ret['outarr']['items'];
  for($c=0; $c < count($list); $c++)
  {
   $item = $list[$c];
   $outArr['sections'][0]['results'][] = array(
	 'id'=>$item['id'], 
	 'name'=>$item['name'],
	 'action'=>array('title'=>"mostra servizio &raquo;", "command"=>"gframe -f gserv/edit.item -params `ap=".$item['tb_prefix']."&id=".$item['id']."`")
	);
  }
 }

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


