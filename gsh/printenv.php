<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2010 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 29-12-2009
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Print all or part of environment
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function shell_printenv($args, $sessid)
{
 $variables = array();
 $outArr = array();

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-var' : {$variables[] = strtoupper($args[$c+1]); $c++;} break;
   default: $variables[] = strtoupper($args[$c]); break;
  }

 if(!count($variables))
 {
  // print all variables //
  while(list($k,$v) = each($_COOKIE))
  {
   $outArr[$k]=$v;
   $output.= "$k=$v\n";
  }
 }
 else
 {
  for($c=0; $c < count($variables); $c++)
  {
   $outArr[$variables[$c]] = $_COOKIE[$variables[$c]];
   $output.= $variables[$c]."=".$_COOKIE[$variables[$c]]."\n";
  }
 }

 return array('message'=>$output, 'outarr'=>$outArr);
}

