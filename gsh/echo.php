<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2010 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 27-12-2009
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Simple echo
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function shell_echo($args, $sessid)
{
 $output = "";
 $outArr = array();
 for($c=0; $c < count($args); $c++)
 {
  $arg = $args[$c];
  if($arg[0] == "$")
   $arg = $_COOKIE[substr($arg,1)];
  $output.= $arg." ";
  $outArr['elements'][] = $arg;
 }

 return array('message'=>trim($output), 'outarr'=>$outArr);
}

