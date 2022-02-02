<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-11-2012
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Common document parser.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

include_once($_BASE_PATH."var/lib/xmllib.php");

function gnujikocontentparser_documents_info($sessid, $shellid)
{
 $info = array('name' => "Documenti generici");
 $keys = array(
	 "DOC_NAME" => "Titolo del documento",
	 "DOC_REV" => "Numero di revisione",
	 "DOC_ALIASNAME" => "Alias",
	 "DOC_ID" => "ID (identificativo univoco numerico)"
	);
 return array('info'=>$info, 'keys'=>$keys);
}

function gnujikocontentparser_documents_parse($contents, $params, $sessid, $shellid)
{
 global $_ABSOLUTE_URL, $_BASE_PATH;
 $contents = gnujikocontentparser_documents_parseKeys($contents, $params, $sessid, $shellid);

 $ap = $params['ap'] ? $params['ap'] : "documents";

 /* GET DOCUMENT INFO */
 $ret = GShell("dynarc item-info -ap `".$ap."` -id '".$params['id']."'");
 if(!$ret['error'])
  $itemInfo = $ret['outarr'];

 /* REPLACE DEFAULT DOCUMENT KEYS */
 $docKeys = array("{DOC_NAME}","{DOC_REV}","{DOC_ALIASNAME}","{DOC_ID}");
 $docVals = array($itemInfo['name'],$itemInfo['rev_num'],$itemInfo['aliasname'],$itemInfo['id']);
 $contents = str_replace($docKeys,$docVals,$contents);

 return $contents;
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikocontentparser_documents_parseKeys($contents, $params, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;

 $sp = stripos($contents, "<gnujikoparserkeys ");
 $ep = stripos($contents, "</gnujikoparserkeys>");
 if(($sp !== false) && ($ep !== false))
  $ok=true;
 else
  return $contents;

 $chunk = substr($contents, $sp, ($ep+20)-$sp);
 $ss = substr($contents, 0, $sp);
 $es = substr($contents, $ep+20);
 $contents = $ss.$es;

 $xml = new GXML();
 if(!$xml->LoadFromString($chunk))
  return $contents;
 
 for($c=0; $c < count($xml->Nodes); $c++)
 {
  $root = $xml->Nodes[$c];
  $action = $root->getAttribute('action');
  switch($action)
  {
   case "list" : {
	 $contents = gnujikocontentparser_documents_listOfItems($root, $contents, $sessid, $shellid);
	} break;
  }
 }
 
 return $contents;
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikocontentparser_documents_listOfItems($xml, $contents, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;

 $singleRowKeys = array();

 $ap = $xml->getAttribute('ap');
 $ct = $xml->getAttribute('ct');
 $get = $xml->getAttribute('get');
 $extget = $xml->getAttribute('extget');
 
 if(!$ap)
  return $contents;

 $query = "dynarc item-list -ap `".$ap."`";
 if($ct)
  $query.= " -ct `$ct`";
 if($get)
  $query.= " -get `".$get."`";
 if($extget)
  $query.= " -extget `".$extget."`";

 $ret = GShell($query, $sessid, $shellid);
 if($ret['error'])
  return $contents;

 $list = $ret['outarr']['items'];

 /* List of keys */
 $keys = $xml->GetElementsByTagName('key');
 for($c=0; $c < count($keys); $c++)
  $singleRowKeys[] = $keys[$c]->getAttribute('name');

 /* Detect first TR for injection phase */
 while(list($i,$k) = each($singleRowKeys))
 {
  $p = strpos($contents,$k);
  if($p !== false)
   break;
 }
 if($p !== false)
 {
  $firstTRsp = strbipos($contents,"<TR",$p);
  $firstTRep = stripos($contents,"TR>",$p)+3;
  $firstTR = substr($contents,$firstTRsp,($firstTRep-$firstTRsp));
  $startInsertPoint = $firstTRsp;
  $endInsertPoint = $firstTRep;
 }

 $cnts = "";

 /* Injection phase */
 for($c=0; $c < count($list); $c++)
 {
  $values = array();
  for($k=0; $k < count($singleRowKeys); $k++)
   $values[] = gnujikocontentparser_documents_parseKeyValue($keys[$k], $list[$c], $sessid, $shellid);
  if($firstTR)
  {
   $rowstr = $firstTR;
   $cnts.= str_replace($singleRowKeys,$values,$rowstr);
  }
 }

 /* Output */
 $sS = substr($contents,0,$startInsertPoint);
 $eS = substr($contents,$endInsertPoint);
 $contents = $sS.$cnts.$eS;
 
 return $contents; 
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikocontentparser_documents_parseKeyValue($key, $itemInfo, $sessid, $shellid)
{
 /* Value can be multi sub-sheet. ie: contacts[0].address */
 $keyVal = $key->getAttribute('value');
 $tmp = explode(".", $keyVal);

 if(count($tmp))
 {
  $info = $itemInfo;
  for($c=0; $c < count($tmp)-1; $c++)
  {
   $arg = $tmp[$c];

   $p = strpos($arg,"[");
   if($p !== false) // is treat as array */
   {
    $var = substr($arg,0,$p);
    $idx = substr($arg,$p+1,strpos($arg,"]")-($p+1));
	$info = $info[$var][$idx];
   }
   else
    $info = $info[$arg];
  }
  return $info[$tmp[count($tmp)-1]];
 }
 else
  return $itemInfo[$keyVal];
}
//-------------------------------------------------------------------------------------------------------------------//


