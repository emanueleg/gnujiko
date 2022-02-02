<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 19-06-2013
 #PACKAGE: apm
 #DESCRIPTION: Alpatech Package Manager. Graphical management of Gnujiko applications packages.
 #VERSION: 2.1beta
 #CHANGELOG: 19-06-2013 : Bug fix in apm update. Aggiunta funzione apm_http_get per scaricare l'xml dei pacchetti.
			 21-11-2012 : Bug fix in function apm_invalidArguments().
			 24-01-2012 : Aggiunta funzione section-list e creata tabella gnujiko_package_sections.
			 02-01-2012 : Bug fix in essential package and version compare.
 #TODO:
 
*/

function shell_apm($args, $sessid, $shellid=0)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $output = "";
 $outArr = array();

 if(count($args) == 0)
  return apm_invalidArguments();

 switch($args[0])
 {
  // ACTION //
  case 'update' : return apm_update($args, $sessid, $shellid); break;
  case 'clean' : return apm_clean($args, $sessid, $shellid); break;

  // REPOSITORY //
  case 'add-repository' : return apm_addRepository($args, $sessid, $shellid); break;
  case 'delete-repository' : return apm_deleteRepository($args, $sessid, $shellid); break;
  case 'repository-list' : return apm_repositoryList($args, $sessid, $shellid); break;

  // SECTIONS //
  case 'section-list' : return apm_sectionList($args, $sessid, $shellid); break;

  // OTHER //
  case 'edit-account' : return apm_editAccount($args, $sessid, $shellid); break;

  default : return apm_invalidArguments(); break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function apm_invalidArguments()
{
 return array("message"=>"Invalid arguments.", "error"=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function apm_update($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();
 $outArr['packages'] = array();
 $outArr['sections'] = array();
 $outArr['outdated'] = array();

 apm_clean($args, $sessid, $shellid);
 $ret = GShell("apm repository-list",$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $repositoryList = $ret['outarr'];
 for($c=0; $c < count($repositoryList); $c++)
 {
  $xml = new GXML();
  $rep = $repositoryList[$c];

  //$xmlBuffer = apm_http_get($rep['url']."/dists/".$rep['ver']."/".$rep['section']."/info/index.php");

  //if(($rep['type'] == 'url') && (!$xml->LoadFromFile($rep['url']."/dists/".$rep['ver']."/".$rep['section']."/info/index.php")) )
  if(($rep['type'] == 'url') && (!$xml->LoadFromString(apm_http_get($rep['url']."/dists/".$rep['ver']."/".$rep['section']."/info/index.php"))))
   return array("message"=>"Unable to retrieve package list from repository ".$rep['url']." ".$rep['ver']." ".$rep['section'],"error"=>"RETRIEVE_PACKAGE_LIST_FAILED");
  /*else if ($rep['type'] == 'media')
   return _getPackageListFromPath($rep);*/
  $repurl = $rep['url']."/dists/".$rep['ver']."/".$rep['section'];
  $n = $xml->GetElementsByTagName('package');
  for($i=0; $i < count($n); $i++)
  {
   $p = $n[$i];
   if(!$p->getString('name',''))
	continue;
   $essential = $p->getString('essential','0');
   if((strtolower($essential) == "yes") || ($essential == "1"))
	$essential = 1;
   else
	$essential = 0;
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT * FROM gnujiko_packages WHERE name='".$p->getString('name','')."'");
   if(!$db->Read())
   {
    $db->RunQuery("INSERT INTO gnujiko_packages(name,version,essential,depends,replaces,conflicts,pre_depends,section,
	maintainer,description,repository) VALUES('".$p->getString('name','')."','".$p->getString('version','')."','"
	.$essential."','".$p->getString('depends','')."','".$p->getString('replaces','')."','"
	.$p->getString('conflicts','')."','".$p->getString('pre_depends','')."','".$p->getString('section','')."','"
	.$p->getString('maintainer','')."','".$p->getString('description','')."','".$repurl."')");
   }
   else
   {
	if(version_compare($p->getString('version',''),$db->record['installed_version']) > 0)
	 $outArr['outdated'][] = $p->getString('name','');
    $db->RunQuery("UPDATE gnujiko_packages SET version='".$p->getString('version','')."',essential='"
	.$essential."',depends='".$p->getString('depends','')."',replaces='"
	.$p->getString('replaces','')."',conflicts='".$p->getString('conflicts','')."',pre_depends='"
	.$p->getString('pre_depends','')."',section='".$p->getString('section','')."',maintainer='"
	.$p->getString('maintainer','')."',description='".$p->getString('description','')."',repository='"
	.$repurl."' WHERE id='".$db->record['id']."'");
   }

   if($p->getString('section','') && !in_array($p->getString('section',''),$outArr['sections']))
	$outArr['sections'][] = $p->getString('section','other');
   $outArr['packages'][] = $p->getString('name','');
   $db->Close();
  }
 }

 sort($outArr['sections']);

 /* update sections table */
 $db = new AlpaDatabase();
 for($c=0; $c < count($outArr['sections']); $c++)
  $db->RunQuery("INSERT INTO gnujiko_package_sections (name) VALUES('".$outArr['sections'][$c]."')");
 $db->Close();

 $out.= count($outArr['packages'])." packages and ".count($outArr['sections'])." sections found.";
 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function apm_clean($args, $sessid, $shellid)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM gnujiko_packages WHERE installed_version=''");
 $db->RunQuery("TRUNCATE TABLE gnujiko_package_sections");
 $db->Close();
 return array('message'=>"Done!");
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//--------------- R E P O S I T O R Y -------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function apm_addRepository($args, $sessid, $shellid)
{
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-url' : {$url=$args[$c+1]; $c++;} break;
   case '-ver' : {$ver=$args[$c+1]; $c++;} break;
   case '-sec' : {$sec=$args[$c+1]; $c++;} break;
  }
 if(!$url || !$ver || !$sec)
  return array("message"=>"You must specify all params (-url, -ver and -sec)", "error"=>"INVALID_PARAMS");
 
 // verify if already exists //
 $ret = GShell("apm repository-list",$sessid, $shellid);
 if($ret['error'])
  return $ret;
 $list = $ret['outarr'];

 for($c=0; $c < count($list); $c++)
 {
  $rep = array();
  $rep = $list[$c];
  if(($rep['url'] == $url) && ($rep['ver'] == $ver) && ($rep['section'] == $sec))
   return array('message'=>"Repository already exists", "error"=>"REPOSITORY_ALREADY_EXISTS");
 }

 global $_BASE_PATH;
 $h = @fopen($_BASE_PATH."etc/apm/sources.list","a");
 if(!$h)
  return array('message'=>"Unable to write to etc/apm/sources.list", "error"=>"UNABLE_TO_WRITE");
 @fwrite($h,rtrim($url,"/")." ".$ver." ".$sec."\r\n");
 @fclose($h);
 $outArr = array('url'=>$url,'ver'=>$ver,'section'=>$sec);
 $out.= "done!\n";
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function apm_deleteRepository($args, $sessid, $shellid)
{
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-url' : {$url=$args[$c+1]; $c++;} break;
   case '-ver' : {$ver=$args[$c+1]; $c++;} break;
   case '-sec' : {$sec=$args[$c+1]; $c++;} break;
  }
 if(!$url || !$ver || !$sec)
  return array('message'=>"You must specify all params (-url, -ver and -sec)", "error"=>"INVALID_PARAMS");
 
 global $_BASE_PATH;
 $h = @fopen($_BASE_PATH."etc/apm/sources.list", "r");
 if(!$h)
  return array('message'=>"Unable to read etc/apm/sources.list","error"=>"UNABLE_TO_READ_FILE");
 
 $copyLines = array();
 while (!feof($h)) 
 {
  $line = fgets($h, 4096);
  $line = ltrim($line);
  if( ($line[0] == "#") || ($line == "") || ($line == " ") )
  {
   $copyLines[] = $line;
   continue;
  }
  $x = explode(" ",$line);
  $rep = array('url'=>rtrim(trim($x[0]), "/"), 'ver'=>trim($x[1]), 'archive'=>trim($x[2]));
  if(($rep['url'] == $url) && ($rep['ver'] == $ver) && ($rep['archive'] == $sec))
  {
   $found = true;
   continue;
  }
  $copyLines[] = rtrim($line);
 }
 fclose($h);

 if(!$found)
  return array('message'=>"Repository not found.","error"=>"REPOSITORY_NOT_FOUND");

 // write to file //
 $h = @fopen($_BASE_PATH."etc/apm/sources.list", "w");
 if(!$h)
  return array('message'=>"Unable to write to etc/apm/sources.list","error"=>"UNABLE_TO_WRITE");

 for($c=0; $c < count($copyLines); $c++)
  @fwrite($h,$copyLines[$c]."\r\n");
 @fclose($h);

 $out.= "Repository has been removed.";

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function apm_repositoryList($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--verbose' : $verbose=true; break;
  }

 $repoFile = $_BASE_PATH."etc/apm/sources.list";
 $handle = @fopen($repoFile, "r");
 if(!$handle)
  return array("message"=>"Unable to read repository list","error"=>"REPOSITORY_LIST_ERROR");
 
 while(!feof($handle)) 
 {
  $line = fgets($handle, 4096);
  $line = ltrim($line);
  if( ($line[0] == "#") || ($line == "") || ($line == " ") )
   continue;
  $x = explode(" ",$line);
  $url = rtrim(trim($x[0]), "/");
  // detect type //
  if(substr($url,0,7) == "http://")
   $type = "url";
  else if(substr($url,0,6) == "ftp://")
   $type = "ftp";
  else
   $type = "media";

  $a = array('type'=>$type, 'url'=>$url, 'ver'=>trim($x[1]), 'section'=>trim($x[2]));
  if($verbose)
   $out.= "URL: ".$a['url']." , VERSION: ".$a['ver']." , SECTION: ".$a['section']."\n";
  $outArr[] = $a;
 }
 @fclose($handle);
 $out.= count($outArr)." repository found.";
 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function apm_sectionList($args, $sessid, $shellid)
{
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--verbose' : $verbose=true; break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT name FROM gnujiko_package_sections WHERE 1 ORDER BY name ASC");
 while($db->Read())
 {
  $outArr[] = $db->record['name'];
  if($verbose)
   $out.= $db->record['name']."\n";
 }
 $db->Close();

 $out.= count($outArr)." sections found.";
 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function apm_http_get($url,$shellid=0,$msgType="",$msgRef="", $mode="")
{
 $url_stuff = parse_url($url);
 $port = isset($url_stuff['port']) ? $url_stuff['port'] : 80;

 $fp = fsockopen($url_stuff['host'], $port);

 $query  = 'GET ' . $url_stuff['path'] . " HTTP/1.0\n";
 $query .= 'Host: ' . $url_stuff['host'];
 $query .= "\n\n";

 fwrite($fp, $query);

 $fileSize = 0;
 $needPreOutput = false;
 $nextStep = 0;

 while ($tmp = fread($fp, 1024))
 {
  $buffer .= $tmp;
  $buffLen = strlen($buffer);
  if(!$fileSize)
  { // try to detect file size //
   if(preg_match('/Content-Length: (\d+)/', $buffer, $matches))
   {
	$fileSize = (int)$matches[1];
	if($fileSize > 71680)
	 $needPreOutput = true;
	$perc = floor((100/$fileSize)*$buffLen);
	gshPreOutput($shellid, "Downloading: ".$perc."%", $msgType, $msgRef, $mode, array('size'=>$fileSize,'current'=>$buffLen,'percentage'=>$perc));
	$nextStep+= 71680;
   }
  }
  if($needPreOutput && (strlen($buffer) >= $nextStep))
  {
   $perc = floor((100/$fileSize)*$buffLen);
   gshPreOutput($shellid, "Downloading: ".$perc."%", $msgType, $msgRef, $mode, array('size'=>$fileSize,'current'=>$buffLen,'percentage'=>$perc));
   $nextStep+= 71680;
  }
 }

 preg_match('/Content-Length: ([0-9]+)/', $buffer, $parts);
 $buffer = substr($buffer, - $parts[1]);
 $p = strpos($buffer, "<?xml");
 if($p !== false)
  $buffer = substr($buffer,$p);
 return $buffer;
}
//-------------------------------------------------------------------------------------------------------------------//
function apm_replaceConfValue($strCfgFile,$strCfgVar,$strCfgVal,$backup=false,$mod=null)
{
 $strOldContent = file ($strCfgFile);
 $strNewContent = "";

 $varFounds = array();
 while (list ($intLineNum, $strLine) = each ($strOldContent)) 
 {
  if(is_array($strCfgVar))
  {
   for($c=0; $c < count($strCfgVar); $c++)
   {
	if(preg_match("/^\\$".$strCfgVar[$c]."( |\t)*=/i",$strLine))	// show any line beginning with a $
    {
     $strLineParts=explode("=",$strLine);
     // we should determine type of value here! (BOOL, INT or String)
     if("$".$strCfgVar[$c] == trim($strLineParts[0])) 
     {
	  $strLineParts[1] = "\t\"".$strCfgVal[$c]."\"";
	  $strLine = implode("=",$strLineParts).";\r\n";
	  if(!in_array($strCfgVar[$c],$varFounds))
	   $varFounds[] = $strCfgVar[$c];
     }  
    }
   }
  }
  else if(preg_match("/^\\$".$strCfgVar."( |\t)*=/i",$strLine))	// show any line beginning with a $
  {
   $strLineParts=explode("=",$strLine);
   // we should determine type of value here! (BOOL, INT or String)
   if("$".$strCfgVar == trim($strLineParts[0])) 
   {
	$strLineParts[1] = "\t\"".$strCfgVal."\"";
	$strLine = implode("=",$strLineParts).";\r\n";
	$varFounds[$strCfgVar] = true;
   }
  }
  $strNewContent .= $strLine;
  if($backup)
   $fp = fopen($strCfgFile."_new", "w");
  else
   $fp = fopen($strCfgFile,"w");
  fputs($fp,$strNewContent);
  fclose($fp);
  if($mod)
  {
   if($backup)
    @chmod($strCfgFile."_new",$mod);
   else
    @chmod($strCfgFile,$mod);
  }
 }

 if(is_array($strCfgVar))
 {
  for($c=0; $c < count($strCfgVar); $c++)
  {
   if(!in_array($strCfgVar[$c], $varFounds))
   {
    $fp = fopen($strCfgFile,"a");
    $strLine = "$".$strCfgVar[$c]." = \t\"".$strCfgVal[$c]."\";\r\n";
    fputs($fp,$strLine);
    fclose($fp);
   }
  }
 }
 else
 {
  if(!in_array($strCfgVar, $varFounds))
  {
   $fp = fopen($strCfgFile,"a");
   $strLine = "$".$strCfgVar." = \t\"".$strCfgVal."\";\r\n";
   fputs($fp,$strLine);
   fclose($fp);
  }
 }

 if($backup)
 {
  if(!rename($strCfgFile,$strCfgFile.".bak")) echo "<pre><b>Error: Could not rename old file!</b></pre>";
  if(!rename($strCfgFile."_new",$strCfgFile)) echo "<pre><b>Failed to copy File!</b></pre>";
 }
}
//----------------------------------------------------------------------------------------------------------------------//
function apm_editAccount($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-token' : {$token=$args[$c+1]; $c++;} break;
  }

 $var = array("_GNUJIKO_ACCOUNT", "_GNUJIKO_TOKEN");
 $val = array($name, $token);
 apm_replaceConfValue($_BASE_PATH."config.php",$var,$val);

 $out.= "done!";

 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//

