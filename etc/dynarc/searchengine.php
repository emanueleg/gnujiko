<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 16-01-2012
 #PACKAGE: dynarc
 #DESCRIPTION: Basic search engine for dynarc archives. (needs coolmindtools)
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

class DynSearchEngine
{
 var $sessid, $shellid;
 var $results;

 function DynSearchEngine($sessid=0, $shellid=0)
 {
  $this->sessid = $sessid;
  $this->shellid = $shellid;
  $this->results = array();
  $this->archives = array();
 }
 //----------------------------------------------------------------------------------------------//
 function init()
 {
  $ret = GShell("dynarc archive-list", $this->sessid, $this->shellid);
  if($ret['error'])
   return $ret;
  $this->archives = $ret['outarr'];
 }
 //----------------------------------------------------------------------------------------------//
 function query($query, $queryinfo=null)
 {
  $this->results = array();
   /* DO ALGORITHM 0 */
   $ret = $this->_search_algorithm_0($this->archives, array('name','keywords','description'), $query, $queryinfo);
   if(count($ret))
	$this->results = array_merge($this->results, $ret['results']);

   if(count($ret) < 10) /* DO ALGORITHM 1 */
   {
	$ret = $this->_search_algorithm_1($this->archives, array('name','keywords','description'), $query, $queryinfo);
	if(count($ret))
	 $this->results = array_merge($this->results, $ret['results']);
   }

   if(count($ret) < 10) /* DO ALGORITHM 2 */
   {
	$ret = $this->_search_algorithm_2($this->archives, array('name','keywords','description'), $query, $queryinfo);
	if(count($ret))
	 $this->results = array_merge($this->results, $ret['results']);
   }

   if(count($ret) < 10) /* DO ALGORITHM 3 */
   {
	$ret = $this->_search_algorithm_3($this->archives, array('name','keywords','description'), $query, $queryinfo);
	if(count($ret))
	 $this->results = array_merge($this->results, $ret['results']);
   }
 }
 //----------------------------------------------------------------------------------------------//
 function _search_algorithm_0($archive, $fields, $query, $queryinfo=null)
 {
  $outArr = array();
  /* Trova l'intera frase nell'archivio */
  $archives = is_array($archive) ? $archive : array(0=>$archive);
  for($c=0; $c < count($archives); $c++)
  {
	$cmd = "";
	  switch($archives[$c]['prefix'])
	  {
	   case 'documentmodels' : $cmd = "gbox -f documentmodel.edit"; break;
	   case 'documents' : $cmd = "gbox -f document.edit"; break;
	   case 'howto' : $cmd = "gbox -f howto.edit"; break;
	   case 'idea' : $cmd = "gbox -f idea.edit"; break;
	   case 'jobtypes' : $cmd = "gbox -f jobtype.edit"; break;
	   case 'notations' : $cmd = "gbox -f notation.edit"; break;
	   case 'problemsolving' : $cmd = "gbox -f problemsolving.edit"; break;
	   case 'procedure' : $cmd = "gbox -f procedure.edit"; break;
	   case 'tasks' : $cmd = "gbox -f taks.edit"; break;
	   case 'todo' : $cmd = "gbox -f todo.edit"; break;
	  }

   $ret = array();
   $q = "dynarc item-list -aid ".$archives[$c]['id']." -where (\"";
   for($f=0; $f < count($fields); $f++)
    $q.= "(".$fields[$f]." LIKE '%".$query."%') OR ";
   $q = rtrim($q, " OR ").")\" --order-by 'ctime DESC'";
   $ret = GShell($q, $this->sessid, $this->shellid);
   if(!$ret['error'])
   {
	for($i=0; $i < count($ret['outarr']['items']); $i++)
	{
	 $itm = $ret['outarr']['items'][$i];
	 $result = array();
	 $result['title'] = $itm['name']." [ap:".$archives[$c]['prefix'].",id:".$itm['id']."]";
	 $result['contents'] = $this->_prepareSearchContent($itm['desc'], 200, $query);
	 $result['link'] = array('onclick'=>"javascript:var sh=new GShell(); sh.sendCommand('".$cmd." -params id=".$itm['id']." --fullspace');");
	 $outArr['results'][] = $result;
	}
   }
  }
  return $outArr;
 }
 //----------------------------------------------------------------------------------------------//
 function _search_algorithm_1($archive, $fields, $query, $queryinfo=null)
 {
  $archives = is_array($archive) ? $archive : array(0=>$archive);
 }
 //----------------------------------------------------------------------------------------------//
 function _search_algorithm_2($archive, $fields, $query, $queryinfo=null)
 {
  $archives = is_array($archive) ? $archive : array(0=>$archive);
 }
 //----------------------------------------------------------------------------------------------//
 function _search_algorithm_3($archive, $fields, $query, $queryinfo=null)
 {
  $archives = is_array($archive) ? $archive : array(0=>$archive);
 }
 //----------------------------------------------------------------------------------------------//
 function _prepareSearchContent($text, $length=200, $searchword ) 
 {
  // strips tags won't remove the actual jscript
  $text = preg_replace( "'<script[^>]*>.*?</script>'si", "", $text );
  $text = preg_replace( '/{.+?}/', '', $text);
  return $this->_smartSubstr(strip_tags( $text ), $length, $searchword );
 }
 //----------------------------------------------------------------------------------------------//
 function _smartSubstr($text, $length=200, $searchword) 
 {
  $wordpos = strpos(strtolower($text), strtolower($searchword));
  $halfside = intval($wordpos - $length/2 - strlen($searchword));
  $retVal = "";
  if($wordpos && $halfside > 0) 
  {
   $retVal = '...' . substr($text, $halfside, $length);
   $rv = explode(" ", $retVal);
   for($c=0; $c < count($rv); $c++)
    if(strtolower($rv[$c]) == strtolower($searchword))
      $rv[$c] = "<strong>".$rv[$c]."</strong>";
   $retVal = implode(" ", $rv);
   return $retVal;
  } 
  else 
  {
   $retVal = substr( $text, 0, $length);
   $rv = explode(" ", $retVal);
   for($c=0; $c < count($rv); $c++)
    if(strtolower($rv[$c]) == strtolower($searchword))
     $rv[$c] = "<strong>".$rv[$c]."</strong>";
   $retVal = implode(" ", $rv);
   return $retVal;
  }
 }
 //----------------------------------------------------------------------------------------------//
 function searchIntoArchive($archive, $fields, $query, $queryinfo=null)
 {
  $cmd = "";
  switch($archive['prefix'])
  {
   case 'documentmodels' : $cmd = "gbox -f documentmodel.edit"; break;
   case 'documents' : $cmd = "gbox -f document.edit"; break;
   case 'howto' : $cmd = "gbox -f howto.edit"; break;
   case 'idea' : $cmd = "gbox -f idea.edit"; break;
   case 'jobtypes' : $cmd = "gbox -f jobtype.edit"; break;
   case 'notations' : $cmd = "gbox -f notation.edit"; break;
   case 'problemsolving' : $cmd = "gbox -f problemsolving.edit"; break;
   case 'procedure' : $cmd = "gbox -f procedure.edit"; break;
   case 'tasks' : $cmd = "gbox -f taks.edit"; break;
   case 'todo' : $cmd = "gbox -f todo.edit"; break;
  }

  $out = array();
  $q = "dynarc item-list -aid ".$archive['id']." -where (\"";
  for($c=0; $c < count($fields); $c++)
   $q.= $fields[$c]." LIKE '%".$query."%' OR ";
  $q = rtrim($q, " OR ").")\" --return-serp-info --order-by 'ctime DESC'";

  $ret = GShell($q, $this->sessid, $this->shellid);
  if(!$ret['error'])
  {
   $results = $ret['outarr'];
   for($c=0; $c < count($results['items']); $c++)
   {
	$itm = $results['items'][$c];
	$out[] = array('onclick'=>"javascript:var sh=new GShell(); sh.sendCommand('".$cmd." -params id=".$itm['id']." --fullspace');",
	'title'=>$itm['name']);
   }
  }
  return $out;
 }

}

