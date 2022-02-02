<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-09-2010
 #PACKAGE: dynarc
 #DESCRIPTION: Search engine into dynarc archives.
 #VERSION: 1.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

function shell_search($args, $sessid, $shellid=null)
{
 global $_BASE_PATH;

 $out = "";
 $outArr = array();

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-query' : case '-qry' : {$query=$args[$c+1]; $c++;} break;
   case '--showresults' : case '--verbose' : $showResults=true; break;
   default: $query = ltrim($query." ".$args[$c]); break;
  }

 /* PRE-PARSER: CHUNKERIZE QUERY */
 $queryInfo = search_chunkerizeQuery($query);

 /* CARICA MOTORE DI RICERCA DI DYNARC */
 if(file_exists($_BASE_PATH."etc/dynarc/searchengine.php"))
 {
  include_once($_BASE_PATH."etc/dynarc/searchengine.php");
  $DSE = new DynSearchEngine($sessid, $shellid);
  $DSE->init();
  $DSE->query($query, $queryInfo);
  $results = $DSE->results;
 }
 
 if($showResults)
 {
  for($c=0; $c < count($DSE->results); $c++)
  {
   $result = $DSE->results[$c];
   $out.= "<p><a href='".($result['link']['href'] ? $result['link']['href'] : "#")."'".($result['link']['onclick'] ? " onclick=\"".$result['link']['onclick'] : "")."\">".$result['title']."</a><br/>".$result['contents']."</p>";
  }
 }

 $out.= count($DSE->results)." results found.";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function search_chunkerizeQuery($query)
{
 $ret = array();
 $ret['words'] = array();

 $x = explode(" ",$query);
 for($c=0; $c < count($x); $c++)
 {
  $ret['words'][] = $word;
  $index = count($ret['words'])-1;
  $word = $x[$c];
  switch($word)
  {
   case 'il' : case 'lo' : case 'la' : case 'i' : case 'gli' : case 'le' : {
	 /* rimuove gli articoli */
	 $ret['removed_words'][] = $word;
	} break;
   case 'di' : case 'da' : case 'dal' : case 'del' : case 'a' : case 'al' : {
	 /* chiavi di posizionamento */
	 /* In teoria queste chiavi sarebbero da darle in pasto a Concept visto che è il suo mestiere creare un concetto da una frase. */
	 /* Per il momento ci limitiamo a segnalare la parole nell'array keys ma le elimineremo da preserved_word come abbiamo fatto con gli articoli */
	 
	 $ret['removed_words'][] = $word;
	} break;
   default : {
	 $key = null;
	 /* DETECT NUMERIC / FLOAT ELEMENTS */
	 if(is_numeric($word))
	 {
	  if((strpos(".",$word) !== false) || (strpos(",",$word) !== false))
	   $key = array('index'=>$index,'type'=>"float",'value'=>$word);
	  else
	   $key = array('index'=>$index,'type'=>"int",'value'=>$word);
	 }
	 if(!$key)
	 {
	  /* DETECT DATE-TIME ELEMENTS */
	  $months = array("gennaio","febbraio","marzo","aprile","maggio","giugno","luglio","agosto","settembre","ottobre","novembre","dicembre");
	  $monthsAbbr = array("gen","feb","mar","apr","mag","giu","lug","ago","set","ott","nov","dic");
	  if(in_array(strtolower($word),$months))
	   $key = array('index'=>$index,'type'=>"datetime",'month'=>array_search(strtolower($word),$months)+1,'value'=>$word);
	  else if(in_array(strtolower($word),$monthsAbbr))
	   $key = array('index'=>$index,'type'=>"datetime",'month'=>array_search(strtolower($word),$monthsAbbr)+1,'value'=>$word);
	 }

	 if($key)
	  $ret['keys'][] = $key;
	 $ret['preserved_words'][] = $word;
	}
  }
 }
 
 /* SUGGESTED QUERY */
 $ret['suggested_query'] = implode(" ",$ret['preserved_words']); 

 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//

