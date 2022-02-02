<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-03-2016
 #PACKAGE: stats
 #DESCRIPTION: Official Gnujiko statistics service.
 #VERSION: 2.1beta
 #CHANGELOG: 07-03-2016 : Bug fix dateto
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");
include_once($_BASE_PATH."var/objects/gnujikostats/index.php");

function shell_stats($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'info' : return stats_info($args, $sessid, $shellid); break; // Get info about statistics service status
  case 'enable' : return stats_enable($args, $sessid, $shellid); break; // Enable statistics service on a given archive
  case 'disable' : return stats_disable($args, $sessid, $shellid); break; // Disable statistics service of an archive
  case 'make-index' : return stats_makeIndex($args, $sessid, $shellid); break; // Make index for a specified service 
  case 'get' : return stats_get($args, $sessid, $shellid); break; // Return the statistics for a specified service 

  default : return stats_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function stats_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function stats_info($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 $out.= "List of available services:\n";
 $d = dir($_BASE_PATH."etc/stats/services/");
 while(FALSE !== ($entry = $d->read()))
 {
  if($entry == '.' || $entry == '..')
   continue;
  if(substr($entry, -1) == "~")
   continue;
  if($entry == "index.php")
   continue;
  $serviceName = basename($entry,".php");
  $fileName = $_BASE_PATH."etc/stats/services/".ltrim($entry,'/');
  if(!is_dir($fileName))
  {
   include_once($fileName);
   if(is_callable("gnujikostatservice_".$serviceName."_info",true))
   {
	$serviceInfo = call_user_func("gnujikostatservice_".$serviceName."_info",$sessid, $shellid);
	$out.= " - ".$serviceInfo['name']."\n";
	$outArr['services'][] = $serviceInfo;
   }
  }
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function stats_enable($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-service' : {$serviceName=$args[$c+1]; $c++;} break;
   default : $serviceName=$args[$c]; break;
  }

 if(!$serviceName)
  return array("message"=>"You must specify the service","error"=>"INVALID_SERVICE_NAME");

 if(!file_exists($_BASE_PATH."etc/stats/services/".$serviceName.".php"))
  return array("message"=>"Service not found.","error"=>"SERVICE_NOT_FOUND");

 /* Get service info */
 include_once($_BASE_PATH."etc/stats/services/".$serviceName.".php");
 if(is_callable("gnujikostatservice_".$serviceName."_info",true))
 {
  $serviceInfo = call_user_func("gnujikostatservice_".$serviceName."_info",$sessid, $shellid);
 }

 /* Get if service exists and is enabled */
 $exists = false;
 $enabled = false;
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT enabled FROM gnujiko_stats_services WHERE service='".$serviceName."'");
 if($db->Read())
 {
  $exists = true;
  $enabled = $db->record['enabled'];
 }
 $db->Close();

 if($enabled)
 {
  $out.= "Service ".$serviceName." is already installed!\n";
 }
 else
 {
  $out.= "Enabling the service ".$serviceName." in progress...";
  if(is_callable("gnujikostatservice_".$serviceName."_enable",true))
  {
   $ret = call_user_func("gnujikostatservice_".$serviceName."_enable",$sessid, $shellid);
   if($ret['error'])
   {
	$out.= "failed!\n".$ret['message'];
	return array("message"=>$out, "error"=>$ret['error']);
   }
   $db = new AlpaDatabase();
   if($exists)
	$db->RunQuery("UPDATE gnujiko_stats_services SET enabled='1' WHERE service='".$serviceName."'");
   else
	$db->RunQuery("INSERT INTO gnujiko_stats_services(service,enabled) VALUES('".$serviceName."','1')");
   $db->Close();
   $out.= "done!\n";
  }
  else
  {
   $out.= "failed! The file 'etc/stats/services/".$serviceName.".php' does not contain the function gnujikostatservice_".$serviceName."_enable";
   $db->Close();
   return array("message"=>$out, "error"=>"ENABLING_SERVICE_FAILED");
  }
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function stats_disable($args, $sessid, $shellid)
{
}
//-------------------------------------------------------------------------------------------------------------------//
function stats_makeIndex($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-service' : {$serviceName=$args[$c+1]; $c++;} break;
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;
   default : $serviceName=$args[$c]; break;
  }

 if(!$serviceName)
  return array("message"=>"You must specify the service","error"=>"INVALID_SERVICE_NAME");

 if(!file_exists($_BASE_PATH."etc/stats/services/".$serviceName.".php"))
  return array("message"=>"Service not found.","error"=>"SERVICE_NOT_FOUND");

 // verify if service is enabled //
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT enabled FROM gnujiko_stats_services WHERE service='".$serviceName."'");
 if(!$db->Read() || !$db->record['enabled'])
 {
  $db->Close();
  return array("message"=>"Service ".$serviceName." is not enabled.","error"=>"SERVICE_DISABLED");
 }
 $db->Close();

 /* Get service info */
 include_once($_BASE_PATH."etc/stats/services/".$serviceName.".php");
 if(is_callable("gnujikostatservice_".$serviceName."_makeindex",true))
 {
  return call_user_func("gnujikostatservice_".$serviceName."_makeindex",$args, $sessid, $shellid);
 }
 else
 {
  $out = "Unable to call function 'gnujikostatservice_".$serviceName."_makeindex' into file etc/stats/services/".$serviceName.".php";
  return array("message"=>$out,"error"=>"UNKNOWN_ERROR");
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function stats_get($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-service' : {$serviceName=$args[$c+1]; $c++;} break;
   case '-from' : {$dateFrom=strtotime($args[$c+1]); $c++;} break;
   case '-to' : {$dateTo=strtotime($args[$c+1]); $c++;} break;
   case '-return' : {$retValField=$args[$c+1]; $c++;} break;
   default : $serviceName=$args[$c]; break;
  }

 if(!$serviceName)
  return array("message"=>"You must specify the service","error"=>"INVALID_SERVICE_NAME");

 if(!file_exists($_BASE_PATH."etc/stats/services/".$serviceName.".php"))
  return array("message"=>"Service not found.","error"=>"SERVICE_NOT_FOUND");

 // verify if service is enabled //
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT enabled FROM gnujiko_stats_services WHERE service='".$serviceName."'");
 if(!$db->Read() || !$db->record['enabled'])
 {
  $db->Close();
  return array("message"=>"Service ".$serviceName." is not enabled.","error"=>"SERVICE_DISABLED");
 }
 $db->Close();

 // verify if already indexed //
 $dFrom = date('Y-m-d',$dateFrom);
 $dTo = date('Y-m-d',strtotime("+1 day",$dateTo));
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id,mtime,date_from,date_to FROM stats_".$serviceName."_indexes WHERE date_from<='".$dFrom."' AND date_to>='".$dTo."' LIMIT 1");
 if(!$db->Read())
 {
  // make index //
  $ret = GShell("stats make-index -service '".$serviceName."' -from '".$dFrom."' -to '".$dTo."'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $out.= $ret['message']."\n";
 }
 $db->Close();


 /* Get service info */
 include_once($_BASE_PATH."etc/stats/services/".$serviceName.".php");
 if(is_callable("gnujikostatservice_".$serviceName."_get",true))
  return call_user_func("gnujikostatservice_".$serviceName."_get",$args, $sessid, $shellid);
 else
 {
  $out = "Unable to call function 'gnujikostatservice_".$serviceName."_get' into file etc/stats/services/".$serviceName.".php";
  return array("message"=>$out,"error"=>"UNKNOWN_ERROR");
 }
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//

