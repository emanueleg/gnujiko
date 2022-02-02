<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-12-2014
 #PACKAGE: aboutconfig
 #DESCRIPTION: Unified system to manage application configurations.
 #VERSION: 2.3beta
 #CHANGELOG: 21-12-2014 : Bug fix su funzione set-config-val.
			 30-08-2014 : Bug fix
			 22-05-2014 : Aggiunto parametro empty su funzione set-user-settings
 #TODO: 
 
*/

function shell_aboutconfig($args, $sessid, $shellid=0)
{
 if(count($args) == 0)
  return aboutconfig_invalidArguments();

 switch($args[0])
 {
  case 'get' : case 'get-config' : return aboutconfig_getConfig($args, $sessid, $shellid); break; // get application config and logged user settings
  
  case 'set-config' : case 'set-app-config' : case 'set-application-config' : return aboutconfig_setApplicationConfig($args, $sessid, $shellid); break;
  case 'set-user-settings' : return aboutconfig_setUserSettings($args, $sessid, $shellid); break;

  case 'set-config-val' : return aboutconfig_setConfigValue($args, $sessid, $shellid); break;

  default : return aboutconfig_invalidArguments(); break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function aboutconfig_invalidArguments()
{
 return array("message"=>"Invalid arguments.", "error"=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function aboutconfig_getConfig($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 include_once($_BASE_PATH."var/lib/xmllib.php");

 $out = "";
 $outArr = array();

 $sessInfo = sessionInfo($sessid);

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-app' : case '-application' : {$appName=$args[$c+1]; $c++;} break;
   case '-sec' : case '-section' : {$appSec=$args[$c+1]; $c++;} break;
   default : {if(!$appName) $appName=$args[$c];} break;
  }

 if(!$appName)
  return array("message"=>"Error: you must specify the application name. (with: -app APPLICATION)", "error"=>"INVALID_APP_NAME");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT xml_config,xml_default_settings FROM aboutconfig_appconfig WHERE app_name='".$appName."' AND app_section='".$appSec."'");
 if(!$db->Read())
  $out.= "no application config found for ".$appName."\n";
 else
 {
  if($db->record['xml_config'])
  {
   $xmlConfig = ltrim(rtrim($db->record['xml_config']));
   $xml = new GXML();
   if($xml->LoadFromString("<xml>".$xmlConfig."</xml>"))
    $outArr['config'] = $xml->toArray();
  }

  if($db->record['xml_default_settings'])
  {
   $xmlSettings = ltrim(rtrim($db->record['xml_default_settings']));
   $xml = new GXML();
   if($xml->LoadFromString("<xml>".$xmlSettings."</xml>"))
    $outArr['defaultsettings'] = $xml->toArray();
  }
 }
 $db->Close();

 // Get user settings
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT xml_settings FROM aboutconfig_usersettings WHERE user_id='".$sessInfo['uid']."' AND app_name='".$appName."' AND app_section='"
	.$appSec."' LIMIT 1");
 if($db->Read() && $db->record['xml_settings'])
 {
  $xmlSettings = ltrim(rtrim($db->record['xml_settings']));
  $xml = new GXML();
  if($xml->LoadFromString("<xml>".$xmlSettings."</xml>"))
   $outArr['usersettings'] = $xml->toArray();
 }
 $db->Close();

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function aboutconfig_setApplicationConfig($args, $sessid, $shellid)
{
 global $_BASE_PATH;

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 include_once($_BASE_PATH."var/lib/xmllib.php");

 $out = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-app' : {$appName=$args[$c+1]; $c++;} break;
   case '-sec' : {$appSec=$args[$c+1]; $c++;} break;
   case '-xml-config' : case '--xml-config' : {$xmlConfig=$args[$c+1]; $c++;} break;
   case '-xml-settings' : case '--xml-settings' : {$xmlSettings=$args[$c+1]; $c++;} break;
  }

 if(!$appName)
  return array("message"=>"Error: you must specify the application name. (with: -app APPLICATION)", "error"=>"INVALID_APP_NAME");

 $id = 0;
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM aboutconfig_appconfig WHERE app_name='".$appName."' AND app_section='".$appSec."' LIMIT 1");
 if($db->Read())
 {
  $id = $db->record['id'];
  $q = "";
  if(isset($xmlConfig))
   $q.= ",xml_config='".$db->Purify($xmlConfig)."'";
  if(isset($xmlSettings))
   $q.= ",xml_default_settings='".$db->Purify($xmlSettings)."'";
  $db->RunQuery("UPDATE aboutconfig_appconfig SET ".ltrim($q,",")." WHERE id='".$id."'");
  if($db->Error)
   return array("message"=>"MySQL Error: ".$db->Error, "error"=>"MYSQL_ERROR");
 }
 else
 {
  $db->RunQuery("INSERT INTO aboutconfig_appconfig(app_name,app_section,xml_config,xml_default_settings) VALUES('"
	.$appName."','".$appSec."','".$db->Purify($xmlConfig)."','".$db->Purify($xmlSettings)."')");
  if($db->Error)
   return array("message"=>"MySQL Error: ".$db->Error, "error"=>"MYSQL_ERROR");
 }
 $db->Close();

 $out.= "done! Configuration has been updated.";

 return array("message"=>$out); 
}
//-------------------------------------------------------------------------------------------------------------------//
function aboutconfig_setUserSettings($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 include_once($_BASE_PATH."var/lib/xmllib.php");

 $out = "";

 $sessInfo = sessionInfo($sessid);

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-app' : {$appName=$args[$c+1]; $c++;} break;
   case '-sec' : {$appSec=$args[$c+1]; $c++;} break;
   case '-xml-settings' : case '--xml-settings' : {$xmlSettings=$args[$c+1]; $c++;} break;
   case '-empty' : case '--empty' : $emptyAll=true; break;
  }

 if(!$appName)
  return array("message"=>"Error: you must specify the application name. (with: -app APPLICATION)", "error"=>"INVALID_APP_NAME");

 if($emptyAll)
 {
  $db = new AlpaDatabase();
  if($sessInfo['uname'] == "root")
   $db->RunQuery("DELETE FROM aboutconfig_usersettings WHERE app_name='".$appName."' AND app_section='".$appSec."'");
  else
   $db->RunQuery("DELETE FROM aboutconfig_usersettings WHERE app_name='".$appName."' AND app_section='".$appSec."' AND user_id='".$sessInfo['uid']."'");
  $db->Close();
  $out.= "User settings has been empty.";
  return array("message"=>$out);
 }

 $id = 0;
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM aboutconfig_usersettings WHERE user_id='".$sessInfo['uid']."' AND app_name='".$appName."' AND app_section='".$appSec."' LIMIT 1");
 if($db->Read())
  $db->RunQuery("UPDATE aboutconfig_usersettings SET xml_settings='".$db->Purify($xmlSettings)."' WHERE id='".$db->record['id']."'");
 else
  $db->RunQuery("INSERT INTO aboutconfig_usersettings(user_id,app_name,app_section,xml_settings) VALUES('"
	.$sessInfo['uid']."','".$appName."','".$appSec."','".$db->Purify($xmlSettings)."')");
 if($db->Error)
  return array("message"=>"MySQL Error: ".$db->Error, "error"=>"MYSQL_ERROR");
 $db->Close();

 $out.= "done! User settings has been updated.";

 return array("message"=>$out); 
}
//-------------------------------------------------------------------------------------------------------------------//
function aboutconfig_setConfigValue($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 include_once($_BASE_PATH."var/lib/xmllib.php");

 $out = "";
 $outArr = array();

 $sessInfo = sessionInfo($sessid);

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-app' : case '-application' : {$appName=$args[$c+1]; $c++;} break;
   case '-sec' : case '-section' : {$appSec=$args[$c+1]; $c++;} break;
   case '-arr' : case '-array' : {$arrName=$args[$c+1]; $c++;} break;

   case '-xml' : {$xmlValue=$args[$c+1]; $c++;} break;

   default : {if(!$appName) $appName=$args[$c];} break;
  }

 if(!$appName)
  return array("message"=>"Error: you must specify the application name. (with: -app APPLICATION)", "error"=>"INVALID_APP_NAME");

 $xmlValueArr = array();
 if($xmlValue)
 {
  $xml = new GXML();
  if(!$xml->LoadFromString("<xml>".$xmlValue."</xml>"))
   return array('message'=>'XML Error: '.$xml->error, 'error'=>'XML_ERROR');
  $xmlValueArr = $xml->toArray();
 }

 $_XML_CONFIG = array();

 $id = 0;
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id,xml_config FROM aboutconfig_appconfig WHERE app_name='".$appName."' AND app_section='".$appSec."'");
 if($db->Read())
 {
  $id = $db->record['id'];
  if($db->record['xml_config'])
  {
   $xmlConfig = ltrim(rtrim($db->record['xml_config']));
   $xml = new GXML();
   if($xml->LoadFromString("<xml>".$xmlConfig."</xml>"))
    $_XML_CONFIG = $xml->toArray();
  }
 }
 $db->Close();

 if($arrName)
  $_XML_CONFIG[$arrName] = $xmlValueArr;
 else
  $_XML_CONFIG = array_merge($_XML_CONFIG, $xmlValueArr);


 $_XML = array_to_xml($_XML_CONFIG);
 $_XML = substr($_XML, 5, -6);

 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("UPDATE aboutconfig_appconfig SET xml_config='".$db->Purify($_XML)."' WHERE id='".$id."'");
 else
  $db->RunQuery("INSERT INTO aboutconfig_appconfig(app_name,app_section,xml_config) VALUES('"
	.$appName."','".$appSec."','".$db->Purify($_XML)."')");
 $db->Close();

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

