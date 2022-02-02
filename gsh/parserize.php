<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2015 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-03-2015
 #PACKAGE: parserize
 #DESCRIPTION: Gnujiko parserizer.
 #VERSION: 2.1beta
 #CHANGELOG: 13-03-2015 : Aggiunto parametri -ap e -id
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

function shell_parserize($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'parserlist' : return shell_parserize_parserList($args, $sessid, $shellid); break;
  case 'parserinfo' : return shell_parserize_parserInfo($args, $sessid, $shellid); break;
 }

 global $_BASE_PATH, $_ABSOLUTE_URL;

 $out = "";
 $outArr = array();
 $parsers = array();

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-p' : {$parsers[] = $args[$c+1]; $c++;} break;
   case '-params' : {$params = $args[$c+1]; $c++;} break;

   case '-c' : case '-contents' : {$contents=$args[$c+1]; $c++;} break;
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;

   default : $contents = $args[$c]; break;
  }
 
 if(!count($parsers))
  return array('message'=>"You must specify parser filename","error"=>"INVALID_PARSER");
 
 if($_AP && $_ID)
 {
  $ret = GShell("dynarc item-info -ap '".$_AP."' -id '".$_ID."'",$sessid,$shellid);
  if(!$ret['error'])
   $contents = $ret['outarr']['desc'];
 }

 /* ARRAYZE PARAMS */
 $tmp = explode("&",$params);
 $params = array();
 for($c=0; $c < count($tmp); $c++)
 {
  $x = explode("=",$tmp[$c]);
  $params[$x[0]] = $x[1];
 }
 /* EOF - ARRAYZE PARAMS */

 $_PARAMS = $params;
 $_CONTENTS = $contents;

 for($c=0; $c < count($parsers); $c++)
 {
  $parser = $parsers[$c];
  if(!file_exists($_BASE_PATH."etc/contents_parsers/".$parser.".php"))
   return array('message'=>"Parser $parser does not exists","error"=>"PARSER_DOES_NOT_EXISTS");
  include_once($_BASE_PATH."etc/contents_parsers/".$parser.".php");
  if(is_callable("gnujikocontentparser_".$parser."_parse",true))
  {
   $_CONTENTS = call_user_func("gnujikocontentparser_".$parser."_parse", $_CONTENTS, $_PARAMS, $sessid, $shellid);
  }
 }

 $out = $_CONTENTS;

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function strbipos($haystack="", $needle="", $offset=0) 
{
 // Search backwards in $haystack for $needle starting from $offset and return the position found or false
 $len = strlen($haystack);
 $pos = stripos(strrev($haystack), strrev($needle), $len - $offset - 1);
 return ( ($pos === false) ? false : $len - strlen($needle) - $pos );
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_parserize_parserList($args, $sessid, $shellid=null)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;

 $out = "";
 $outArr = array();

 $files = array();
 $dir = "etc/contents_parsers/";

 $d = dir($_BASE_PATH.$dir);
 while(FALSE !== ($entry = $d->read()))
 {
  if($entry == '.' || $entry == '..')
   continue;
  if(substr($entry, -1) == "~")
   continue;
  $Entry = rtrim($dir,'/').'/'.ltrim($entry,'/');
  if(is_dir($_BASE_PATH.$Entry)) // is a directory //
   continue;
  else // is a file //
  {
   $pos = strrpos($entry,".");
   if($pos === false)
    continue;
   $ext = substr($entry,$pos+1);
   if(strtolower($ext) != "php")
    continue;
   $files[] = substr($entry,0,strlen($entry)-strlen($ext)-1);
  }
 }

 if($files) 
  sort($files);

 for($c=0; $c < count($files); $c++)
 {
  $ret = GShell("parserize parserinfo `".$files[$c]."`",$sessid,$shellid);
  $outArr[] = $ret['outarr'];
  $out.= $ret['outarr']['info']['name']."\n"; 
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_parserize_parserInfo($args, $sessid, $shellid=null)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-p' : {$parser=$args[$c+1]; $c++;} break;
   default : {if(!$parser) $parser=$args[$c]; } break;
  }

 if(!$parser)
  return array('message'=>'You must specify the parser', 'error'=>'INVALID_PARSER');
 
 if(!file_exists($_BASE_PATH."etc/contents_parsers/".$parser.".php"))
  return array('message'=>"Parser $parser does not exists","error"=>"PARSER_DOES_NOT_EXISTS");

 include_once($_BASE_PATH."etc/contents_parsers/".$parser.".php");
 if(is_callable("gnujikocontentparser_".$parser."_info",true))
 {
  $outArr = call_user_func("gnujikocontentparser_".$parser."_info", $sessid, $shellid);
  $outArr['name'] = $parser;
  $out.= "Parser name: ".$outArr['info']['name']."\n";
  $out.= "Num. of keys: ".count($outArr['keys']);
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

