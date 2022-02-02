<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-06-2013
 #PACKAGE: hacktvsearch-common
 #DESCRIPTION: Official Gnujiko Search Engine.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

function shell_hacktvsearch($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'info' : return shell_hacktvsearch_info($args, $sessid, $shellid); break;
  case 'search' : return shell_hacktvsearch_search($args, $sessid, $shellid); break;
  case 'varsearch' : return shell_hacktvsearch_varsearch($args, $sessid, $shellid); break;
  case 'varlist' : return shell_hacktvsearch_varlist($args, $sessid, $shellid); break;
  case 'funclist' : case 'functionlist' : return shell_hacktvsearch_funclist($args, $sessid, $shellid); break;
  case 'cmdlist' : case 'commandlist' : return shell_hacktvsearch_cmdlist($args, $sessid, $shellid); break;
  case 'cmdexec' : return shell_hacktvsearch_cmdexec($args, $sessid, $shellid); break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_hacktvsearch_info($args, $sessid, $shellid=null)
{
 global $_ABSOLUTE_URL, $_BASE_PATH;

 $out = "";
 $outArr = array("commands"=>array(), "functions"=>array(), "variables"=>array());

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-currenturl' : case '-url' : {$currentURL=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 /* LOADING DEFAULT HTSEARCH-CONFIG FILE */
 $out.= "Checking for default configuration file into folder etc/htsearch-config/...";
 $d = dir($_BASE_PATH."etc/htsearch-config/");
 while(FALSE !== ($entry = $d->read()))
 {
  if($entry == '.' || $entry == '..')
   continue;
  if(substr($entry, -1) == "~")
   continue;
  if($entry == "index.php")
   continue;
  $configName = basename($entry,".php");
  $fileName = $_BASE_PATH."etc/htsearch-config/".ltrim($entry,'/');
  if(!is_dir($fileName))
  {
   include_once($fileName);
   if(is_callable("gnujikohtsearch_".$configName."_info",true))
   {
	if(!$defConfigInfo)
	 $out.= "done!";
	$out.= "\nLoading configuration file from etc/htsearch-config/".$configName.".php ...";
    $defConfigInfo = call_user_func("gnujikohtsearch_".$configName."_info",$sessid, $shellid);
    $out.= "done!\n";
    $outArr["commands"] = array_merge($outArr['commands'],$defConfigInfo['commands']);
    $outArr["functions"] = array_merge($outArr['functions'],$defConfigInfo['functions']);
    $outArr["variables"] = array_merge($outArr['variables'],$defConfigInfo['variables']);
    if($verbose)
    {
	 $out.= "Information about ".$configName." htsearch-config:\n";
	 $out.= "File: etc/htsearch-config/".$configName.".php\n";
	 $out.= "N. of available commands: ".count($defConfigInfo['commands'])."\n";
	 $out.= "N. of available functions: ".count($defConfigInfo['functions'])."\n";
	 $out.= "N. of variables: ".count($defConfigInfo['variables'])."\n";
    }
   }
   else
    $out.= "failed!\nUnable to call function gnujikohtsearch_".$configName."_info into file etc/htsearch-config/".$configName.".php\n"; 
  }

 }
 if(!$defConfigInfo)
  $out.= "no configuration file found into folder etc/htsearch-config/.\n";


 /* LOADING CURRENT URL HTSEARCH-CONFIG FILE */
 if(!$currentURL)
 {
  $out.= "Current URL is not specified, check for a valid htsearch-config file from current URL:".$_ABSOLUTE_URL."...";
  $currentURL = $_ABSOLUTE_URL."/";
 }
 else
  $out.= "Check for a valid htsearch-config file from URL ".$currentURL."...";

 $pos = strrpos($currentURL,"/");
 if($pos !== false)
  $currentURL = substr($currentURL,0,$pos);
 $currentURL = str_replace($_ABSOLUTE_URL, "", $currentURL);

 $htfile = ($_ABSOLUTE_URL == $currentURL."/" ? $_BASE_PATH."htsearch-config.php" : $_BASE_PATH.$currentURL."/htsearch-config.php");
 if(!file_exists($htfile))
 {
  $out.= "not found!\n";
 }
 else
 {
  $out.= "done!\n";
  include_once($htfile);
  if(is_callable("gnujikohtsearch_info",true))
  {
   $customConfigInfo = call_user_func("gnujikohtsearch_info",$sessid, $shellid);
   $outArr["commands"] = array_merge($outArr['commands'], $customConfigInfo['commands']);
   $outArr["functions"] = array_merge($outArr['functions'], $customConfigInfo['functions']);
   $outArr["variables"] = array_merge($outArr['variables'], $customConfigInfo['variables']);
   $outArr["customconfigfile"] = $htfile;

   if($verbose)
   {
	$out.= "Informations about htsearch-config\n";
	$out.= "File: ".$htfile."\n";
	$out.= "N. of available commands: ".count($customConfigInfo['commands'])."\n";
	$out.= "N. of available functions: ".count($customConfigInfo['functions'])."\n";
	$out.= "N. of variables: ".count($customConfigInfo['variables'])."\n";
   }

  }
  else
   $out.= "Error: unable to call function gnujikohtsearch_info into file ".$htfile."\n";
 }

 if($verbose)
 {
  $out.= "Tot. available commands: ".count($outArr['commands'])."\n";
  $out.= "Tot. available functions: ".count($outArr['functions'])."\n";
  $out.= "Tot. variables: ".count($outArr['variables'])."\n";
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_hacktvsearch_search($args, $sessid, $shellid=null)
{
 global $_ABSOLUTE_URL, $_BASE_PATH;
 $out = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-currenturl' : case '-url' : {$currentURL=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
   default: $query = trim($args[$c]); break;
  }

 if(!$query)
  return array('message'=>$out, 'outarr'=>$outArr);

 $ret = GShell("hacktvsearch info".($currentURL ? " -url `".$currentURL."`" : "").($verbose ? " --verbose" : ""),$sessid,$shellid);
 if($ret['error'])
  return $ret;
 
 $outArr = $ret['outarr'];

 if($outArr['customconfigfile'] && is_callable("gnujikohtsearch_search",true))
 {
  $ret = call_user_func("gnujikohtsearch_search", $query, $sessid, $shellid);
  if($ret['error'])
   return $ret;
  for($c=0; $c < count($ret['outarr']['sections']); $c++)
   $outArr['sections'][] = $ret['outarr']['sections'][$c];
  for($c=0; $c < count($outArr['available_functions']); $c++)
  {
   if(count($outArr['functions']) > 4)
    break;
   $function = $outArr['available_functions'][$c];
   reset($function);
   while(list($k,$v) = each($function['keywords']))
   {
    if(stripos($v,$query) !== false)
    {
     $outArr['functions'][] = array('name'=>$v, 'link'=>$function['link']); /* da controllare e rifare questa funzione */
     break;
    }
   }
  }
 }

 if($verbose)
 {
  $out.= "Match test for query: ".$query."\n";
  if(!count($outArr['sections']))
   $out.= "no results and no sections found.\n";
  for($c=0; $c < count($outArr['sections']); $c++)
  {
   $sec = $outArr['sections'][$c];
   $res = $sec['results'];
   if(!count($res))
    $out.= "no results found for section: ".$sec['title']."\n";
   else
   {
	$out.= count($res)." results found for section: ".$sec['title']."\n";
	for($i=0; $i < (count($res) < 10 ? count($res) : 10); $i++)
	 $out.= ($i+1).") ".$res[$i]['name']."\n";
   }
  }
 }
 
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_hacktvsearch_varsearch($args, $sessid, $shellid=null)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array("result"=>"", "suggested"=>array(), "query"=>"");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-currenturl' : case '-url' : {$currentURL=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
   case '-var' : {$varName=$args[$c+1]; $c++;} break;
   default: $query = trim($args[$c]); break;
  }

 $ret = GShell("hacktvsearch info".($currentURL ? " -url `".$currentURL."`" : ""),$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $suggested = array();

 $d = dir($_BASE_PATH."etc/htsearch-config/");
 while(FALSE !== ($entry = $d->read()))
 {
  if($entry == '.' || $entry == '..')
   continue;
  if(substr($entry, -1) == "~")
   continue;
  if($entry == "index.php")
   continue;
  $configName = basename($entry,".php");
  $fileName = $_BASE_PATH."etc/htsearch-config/".ltrim($entry,'/');
  if(!is_dir($fileName))
  {
   include_once($fileName);
   if(is_callable("gnujikohtsearch_".$configName."_varsearch",true))
   {
    $customSearchRet = call_user_func("gnujikohtsearch_".$configName."_varsearch", $varName, $query, $sessid, $shellid);
    $outArr = $customSearchRet;
	if($outArr['result'])
	 break;
	if(count($outArr['suggested']))
	 $suggested = array_merge($suggested, $outArr['suggested']);
   }
  }
 }

 if(!$outArr['result'] && !count($outArr['suggested']))
 {
  $htfile = ($_ABSOLUTE_URL == $currentURL."/" ? $_BASE_PATH."htsearch-config.php" : $_BASE_PATH.$currentURL."/htsearch-config.php");

  if(file_exists($htfile))
  {
   include_once($htfile);

   /* SEARCH INTO DEFAULT HTSEARCH-CONFIG */
   if(is_callable("gnujikohtsearch_varsearch",true))
   {
    $defSearchRet = call_user_func("gnujikohtsearch_varsearch", $varName, $query, $sessid, $shellid);
    $outArr = $defSearchRet;
	 if(count($outArr['suggested']))
	  $suggested = array_merge($suggested, $outArr['suggested']);
   }
  }
 }

 $outArr['suggested'] = $suggested;

 if($verbose)
 {
  if(!$outArr['result'] && !count($outArr['suggested']))
   $out.= "no matches for variable ".$varName."\n";
  else if($outArr['result'])
   $out.= "match success for variable ".$varName."! Value=".$outArr['result']."\n";
  else
  {
   $out.= "list of suggested words:\n";
   for($c=0; $c < count($outArr['suggested']); $c++)
	$out.= $outArr['suggested'][$c]."\n";
  }
 }
 
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_hacktvsearch_varlist($args, $sessid, $shellid=null)
{
 $out = "";


 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-currenturl' : case '-url' : {$currentURL=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 
 $ret = GShell("hacktvsearch info".($currentURL ? " -url `".$currentURL."`" : ""),$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $outArr = $ret['outarr']['variables'];

 if($verbose)
 {
  $out.= "List of variables:\n";
  for($c=0; $c < count($ret['outarr']['variables']); $c++)
   $out.= "{".$ret['outarr']['variables'][$c]['name']."} - ".$ret['outarr']['variables'][$c]['title']."\n";
 }

 $out.= "\nfound ".count($outArr)." variables.";
 
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_hacktvsearch_funclist($args, $sessid, $shellid=null)
{
 $out = "";


 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-currenturl' : case '-url' : {$currentURL=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 
 $ret = GShell("hacktvsearch info".($currentURL ? " -url `".$currentURL."`" : ""),$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $outArr = $ret['outarr']['functions'];

 if($verbose)
 {
  $out.= "List of functions:\n";
  for($c=0; $c < count($ret['outarr']['functions']); $c++)
  {
   $func = $ret['outarr']['functions'][$c];
   $out.= ($c+1).") - ".$func['name']."\n";
   $out.= "action: type=";
   if($func['action']['command'])
	$out.= "gshell command; cmd='".$func['action']['command']."'\n";
   else if($func['action']['sudocommand'])
	$out.= "gshell command as root; cmd='".$func['action']['sudocommand']."'\n";
   else if($func['action']['url'])
	$out.= "link; URL:".$func['action']['url']."\n";
   else
	$out.= "unknown\n";
   
   $out.= "keywords:\n";
   for($i=0; $i < count($func['keywords']); $i++)
	$out.= $func['keywords'][$i]."\n";
   $out.= "\n";
  }
 }

 $out.= "\nfound ".count($outArr)." functions.";
 
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_hacktvsearch_cmdlist($args, $sessid, $shellid=null)
{
 $out = "";


 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-currenturl' : case '-url' : {$currentURL=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 
 $ret = GShell("hacktvsearch info".($currentURL ? " -url `".$currentURL."`" : ""),$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $outArr = $ret['outarr']['commands'];

 if($verbose)
 {
  $out.= "List of commands:\n";
  for($c=0; $c < count($ret['outarr']['commands']); $c++)
  {
   $cmd = $ret['outarr']['commands'][$c];
   $out.= ($c+1).") - ".$cmd['name']."\n";
   $out.= "EXP: ".str_replace(array("<",">"), array("",""), $cmd['exp'])."\n\n";
  }
 }

 $out.= "\nfound ".count($outArr)." commands.";
 
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_hacktvsearch_cmdexec($args, $sessid, $shellid=null)
{
 $out = "";
 $outArr = array();
 $keys = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-currenturl' : case '-url' : {$currentURL=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
   case '-cmdid' : {$commandID = $args[$c+1]; $c++;} break;
   case '-kt' : {$keys[] = array('type'=>$args[$c+1], 'value'=>''); $c++;} break;
   case '-kv' : {$keys[count($keys)-1]['value'] = $args[$c+1]; $c++;} break;
  }
 
 if(!isset($commandID))
  return array('message'=>'You must specify the command id. (with: -cmdid COMMAND_ID)','error'=>'INVALID_COMMAND');

 $ret = GShell("hacktvsearch info".($currentURL ? " -url `".$currentURL."`" : ""),$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $command = $ret['outarr']['commands'][$commandID];
 if($command['callfunc'] && is_callable("gnujikohtsearch_".$command['callfunc'],true))
 {
  $ret = call_user_func("gnujikohtsearch_".$command['callfunc'], $keys, $sessid, $shellid);
  return $ret;
 }
 else
  $out.= "no callable function found for command #".$commandID;

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//

