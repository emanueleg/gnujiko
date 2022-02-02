<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-02-2013
 #PACKAGE: gcal
 #DESCRIPTION: Gnujiko calendar
 #VERSION: 2.1beta
 #CHANGELOG: 24-02-2013 : Bug fix
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

function shell_calendar($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'print' : return calendar_print($args, $sessid, $shellid); break;
  default : return calendar_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function calendar_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function calendar_print($args, $sessid=0, $shellid=0)
{
 $out = "";
 $outArr = array();
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-month' : {$month=$args[$c+1]; $c++;} break;
   case '-year' : {$year=$args[$c+1]; $c++;} break;
  }
 
 if(!$month)
  $month = date('n');
 else if(substr($month,0,1) == "0")
  $month = ltrim($month,"0");
 if(!$year)
  $year = date('Y');


 $start = strtotime($year."-".($month < 10 ? "0".$month : $month)."-01 00:00");
 $dayN = date('w',$start);
 if($dayN == 0)
  $dayN = 7;
 if($dayN != 1)
  $start = strtotime("previous monday",$start);
 
 $date = $start;
 for($c=0; $c < 6; $c++)
 {
  for($i=0; $i < 7; $i++)
  {
   if($i == 0)
	$a = array('week'=>date('W',$date), 'days'=>array(), 'dates'=>array());
   $dn = date('j',$date);
   $a['days'][] = $dn;
   $a['dates'][] = date('Y-m-d',$date);
   $out.= $dn." ";
   $date = strtotime("+1 day",$date);
  }
  $outArr[] = $a;
  $out.= "\n";
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

