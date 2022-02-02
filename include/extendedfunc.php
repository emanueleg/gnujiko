<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 25-03-2016
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Extended functions
 #VERSION: 2.3beta
 #CHANGELOG: 25-03-2016 : Aggiunta funzione gnujikoNextPackageVersion.
			 15-02-2016 : Aggiunta funzione get_html_tag
			 27-12-2014 : Aggiunte funzioni parse_timelength e format_timelength.
 #TODO:
 
*/

//-------------------------------------------------------------------------------------------------------------------//
function strdatetime_to_iso($str="")
{
 if(!$str) return "0000-00-00 00:00:00";

 $timeStr = "";
 $x = explode(" ",$str);
 if(count($x) == 2){$dateStr = $x[0]; $timeStr = $x[1];} else $dateStr = $str;

 $sign = null;
 if(strpos($dateStr,"/"))		$sign = "/";
 else if(strpos($dateStr,"-"))	$sign = "-";
 else if(strpos($dateStr,"."))	$sign = ".";
 if(!sign) return "0000-00-00 00:00:00";
 $x = explode($sign, $dateStr);
 $day = $x[0];
 $month = $x[1];
 $year = $x[2];
 return $year."-".$month."-".$day.($timeStr ? " ".$timeStr : "");
}
//-------------------------------------------------------------------------------------------------------------------//
function parse_timelength($str="", $ret='seconds')
{
 if(!$str || ($str == ""))
  return 0;

 $x = explode(":",$str);
 $hh = $x[0] ? $x[0] : 0;
 $mm = $x[1] ? $x[1] : 0;
 $ss = $x[2] ? $x[2] : 0;

 switch($ret)
 {
  case 'seconds' : case 'sec' : case 's' : return $ss + ($mm*60) + ($hh*3600); break;
  case 'minutes' : case 'min' : case 'm' : return $mm + ($hh*60); break;
  case 'hours' : case 'hour' : case 'h' : return $hh; break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function format_timelength($seconds=0, $format='hh:mm:ss')
{
 if(!$seconds)
 {
  $hh = 0;
  $mm = 0;
  $ss = 0;
 }
 else
 {
  $hh = floor($seconds/3600);
  $mm = floor(($seconds-($hh*3600))/60);
  $ss = $seconds - ($mm*60) - ($hh*3600);
 }

 if($mm < 10) $mm = "0".$mm;
 if($ss < 10) $ss = "0".$ss;

 $ret = str_replace(array('hh','mm','ss'), array($hh, $mm, $ss), $format);
 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//
function get_html_tag($string, $tag, $frompos=0, $getInnerContent=false, $removeWhiteSpaces=false)
{
 $ret = array();

 $pos = strpos($string, "<".$tag, $frompos);
 if($pos === false) return false;

 $fp = strpos($string, ">", $pos);
 if($fp === false) return false; 

 $ret['start_tag_pos'] = $pos;
 $ret['start_inner_content'] = $fp+1;

 $etp = strpos($string, "</".$tag.">", $fp);
 if($etp === false)
 {
  // closure tag missing
  $ret['end_inner_content'] = strlen($string);
  $ret['end_tag_pos'] = $ret['end_inner_content'];
  $ret['closure_tag_missing'] = true;
  return $ret;
 }

 $ret['end_inner_content'] = $etp;
 $ret['end_tag_pos'] = $ret['end_inner_content']+strlen("</".$tag.">");

 if($getInnerContent)
 {
  $ret['inner_content'] = substr($string, $ret['start_inner_content'], $ret['end_inner_content'] - $ret['start_inner_content']);
  if($ret['inner_content'] && $removeWhiteSpaces)
   $ret['inner_content'] = str_replace("  ", "", $ret['inner_content']);
 }

 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikoNextPackageVersion($ver="")
{
 $extraVer = "";
 $leftVer = "";

 // Se la versione contiene il carattere " - ", il primo num. è la ver. di Gnujiko gli altri sono ver. extra */ 
 if(strpos($ver, "-") !== false)
 {
  $x = explode("-",$ver);
  $ver = $x[0];
  for($c=1; $c < count($x); $c++)
   $extraVer.= "-".$x[$c];
 }

 $p = strrpos($ver, ".");
 if($p)
 {
  $leftVer = substr($ver, 0, $p).".";
  $ver = substr($ver, $p+1);
  $ver = str_replace(array('alpha','beta','test'), "", $ver);
  if(is_numeric($ver))
   $ver = $ver+1;
 }

 return $leftVer.$ver.$extraVer;
}
//-------------------------------------------------------------------------------------------------------------------//


