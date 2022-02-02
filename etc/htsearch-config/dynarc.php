<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 26-05-2013
 #PACKAGE: dynarc
 #DESCRIPTION: Default htsearch-config file for Dynarc.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

function gnujikohtsearch_dynarc_info($sessid=0, $shellid=0)
{
 /* LIST OF DEFAULT COMMANDS,FUNCTIONS AND VARIABLES */

 $retInfo = array("commands"=>array(), "functions"=>array(), "variables"=>array());

 /* COMMANDS */
 $retInfo['commands'][] = array(
	 "name" => "Apre un archivio",
	 "exp" => "[apri|mostra] archivio {ARCHIVE-NAME}",
	 "callfunc" => "dynarc_openarchive"
	);


 /* --- VARIABLES ---------------------------------------------------------------------*/
 $retInfo['variables'][] = array("name"=>"ARCHIVE-NAME", "title"=>"Nome dell'archivio");

 return $retInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_dynarc_varsearch($varName, $query="", $sessid=0, $shellid=0)
{
 $retInfo = array("result"=>"", "suggested"=>array(), "query"=>$query);
 $doclist = null;

 /* RISULTATI DA RITORNARE IN CASO DI QUERY VUOTA */
 if(!$query)
 {
  switch($varName)
  {
   case 'ARCHIVE-NAME' : {
	 $ret = GShell("dynarc archive-list",$sessid,$shellid);
	 $archivelist = $ret['outarr'];
	 for($c=0; $c < count($archivelist); $c++)
	  $retInfo['suggested'][] = $archivelist[$c]['name'];
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
   /* DYNARC */
   case 'ARCHIVE-NAME' : {
	 if(!$archivelist)
	 {
	  $ret = GShell("dynarc archive-list",$sessid,$shellid);
	  $archivelist = $ret['outarr'];
	 }
	 for($c=0; $c < count($archivelist); $c++)
	 {
	  if(strtolower($archivelist[$c]['name']) == strtolower($query))
	   $retInfo['result'] = $archivelist[$c]['name'];
	  else if(stripos($archivelist[$c]['name'],$query) !== false)
	   $retInfo['suggested'][] = $archivelist[$c]['name'];
	 }
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
function gnujikohtsearch_dynarc_openarchive($keys, $sessid, $shellid)
{
 $archiveName = $keys[2]['value'];
 $ret = GShell("dynarc archive-info -name `".$archiveName."`",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 $outArr = array("command"=>"gframe -f dynarc.navigator -params `ap=".$ret['outarr']['prefix']."&fullextensions=true`");
 return array('message'=>"done",'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

