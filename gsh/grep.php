<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-03-2016
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Simple grep
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function shell_grep($args, $sessid=0, $shellid=0)
{
 $out = "";
 $_QRY = "";
 $_CONTENT = "";
 $removeHighlights = false;

 if(!is_array($args) || !count($args))
  return array('message'=>"Command grep failed!\nUsage: grep QUERY CONTENT", 'error'=>'INVALID_QUERY');

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-h' : $removeHighlights = true; break;
   default : {
	 if(!$_QRY) $_QRY = $args[$c];
	 else if(!$_CONTENT) $_CONTENT = $args[$c];
	} break;
  }

 if(!$_QRY) return array('message'=>"Command grep failed!\nUsage: grep QUERY CONTENT", 'error'=>'INVALID_QUERY');
 if(!$_CONTENT) return array('message'=>'Content is void');
 

 $x = explode("\n", str_replace("</tr>","</tr>\n",$_CONTENT));
 for($c=0; $c < count($x); $c++)
 {
  if(!$x[$c]) continue;
  $ret = shell_grep_search($_QRY, $x[$c], $removeHighlights);
  if($ret) $out.= $ret.(strlen($ret) > 256 ? "\n\n" : "\n");
 }

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_grep_search($_QRY, $_CONTENT, $removeHighlights=false)
{
 if(stripos($_CONTENT, $_QRY) === false)
  return false;

 $qlen = strlen($_QRY);
 $content = strip_tags(str_replace("</td>", " </td>", $_CONTENT));
 if(!$removeHighlights)
  $content = str_replace($_QRY, "<b>".$_QRY."</b>", $content);

 return $content;
}
//-------------------------------------------------------------------------------------------------------------------//

