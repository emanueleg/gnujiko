<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2010 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-01-2010
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Utility for manual page
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function shell_man($args, $sessid)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case "-browser" : case "-b" : $showIntoBrowser = true; break;
   case "-lang" : {$lang=$args[$c+1]; $c++;} break;
   default : $manualName = $args[$c]; break;
  }

 if(!$lang)
  $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

 if(!$manualName)
  return array("message"=>"You must specify manual name","error"=>"INVALID_MANUAL_NAME");

 if(file_exists($_BASE_PATH."etc/man/".$manualName.".".$lang.".html"))
  $file = $_BASE_PATH."etc/man/".$manualName.".".$lang.".html";
 else if(file_exists($_BASE_PATH."etc/man/".$manualName.".html"))
  $file = $_BASE_PATH."etc/man/".$manualName.".html";
 if($file)
 {
  $cnts = file_get_contents($file);
  return array("message"=>str_replace("\n", "", $cnts));
 }
 else
  return array("message"=>"No manual found for $manualName","error"=>"MANUAL_NOT_FOUND");
}
