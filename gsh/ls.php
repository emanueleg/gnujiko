<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 01-02-2012
 #PACKAGE: gnujiko-base
 #DESCRIPTION: List of files and directory
 #VERSION: 2.1beta
 #CHANGELOG: 01-02-2012 : Bug fix sui filtri.
			 30-01-2012 : Aggiunto funzionalità quali: filtri, limit, visualizzare solo directory o solo files.
			 28-01-2012 : Aggiunto argomento -list e bug fix vari.
 #TODO:
 
*/

function shell_ls($args, $sessid, $shellid=0)
{
 global $_BASE_PATH, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_USERS_HOMES;
 $sessInfo = sessionInfo($sessid);
 
 if($sessInfo['uname'] == "root")
  $basepath = $_BASE_PATH ? $_BASE_PATH : "./";
 else if($sessInfo['uid'])
 {
  /* Check if user is able for move files and folders */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM gnujiko_user_privileges WHERE uid='".$sessInfo['uid']."'");
  $db->Read();
  if(!$db->record['mkdir_enable'])
   return array("message"=>"Unable to show a list of files/directories: Your account has not privileges to list files or folders!","error"=>"MKDIR_DISABLED");
  $db->Close();

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $basepath = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
  return array("message"=>"Unable to retrieve your home directory: you don't have a valid account!","error"=>"INVALID_USER");

 $out = "";
 $outArr = array();
 $orderBy = "name";

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--order-by' : {$orderBy = $args[$c+1]; $c++;} break;
   case '-tree' : $tree=true; break;
   case '-list' : $asList=true; break;
   case '-d' : $onlyDirs=true; break;
   case '-f' : $onlyFiles=true; break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '-filter' : {$filter=$args[$c+1]; $c++;} break;
   default : $dir = ltrim($args[$c],"/"); break;
  }

 if($dir && (substr($dir,-1) != "/"))
  $dir.= "/";

 if(!is_dir($basepath.$dir))
  return array("message"=>"Directory $dir does not exists.","error"=>"INVALID_DIRECTORY");

 if($limit)
 {
  if(strpos($limit,",") !== false)
  {
   $x = explode(",",$limit);
   $limitFrom = $x[0];
   $limit = $x[1];
   $limitTo = $limitFrom+$limit;
  }
  else
  {
   $limitFrom = 0;
   $limitTo = $limit;
  }
 }

 if($filter)
 {
  $filters = explode(",",$filter);
 }

 if($tree)
 {
  $outArr = ls_recursiveIncludeDirs($basepath, $dir,$orderBy);
  $out.= "Found ".count($outArr)." directory.";
  return array('message'=>$out,'outarr'=>$outArr);
 }
 else if($asList)
 {
  $outArr = ls_recursiveIncludeFiles($basepath, $dir,$orderBy,$filters);
  for($c=0; $c < count($outArr); $c++)
   $out.= $outArr[$c]."\n";
  $out.= "\nFound a total of ".count($outArr)." files.";
  return array('message'=>$out,'outarr'=>$outArr);
 }

 /* List of files and directory */
 $dirs = array();
 $files = array();

 $d = dir($basepath.$dir);
 while(FALSE !== ($entry = $d->read()))
 {
  if($entry == '.' || $entry == '..')
   continue;
  if(substr($entry, -1) == "~")
   continue;
  $Entry = rtrim($dir,'/').'/'.ltrim($entry,'/');
  if(is_dir($basepath.$Entry)) // is a directory //
   $dirs[] = $Entry;
  else // is a file //
  {
   if($filter)
   {
	$pos = strrpos($entry,".");
	if($pos === false)
	 continue;
	$ext = substr($entry,$pos+1);
	if(!in_array($ext,$filters))
	 continue;
   }
   $files[] = $Entry;
  }
 }
 switch($orderBy)
 {
  case 'name' : case 'name ASC' : {
	 if($dirs) sort($dirs); 
	 if($files) sort($files);
	} break;
  case 'name DESC' : {
	 if($dirs) rsort($dirs); 
	 if($files) rsort($files);
	} break;
 }

 $idx = 0;
 if($limit)
  $outArr['count'] = count($dirs)+count($files);

 if(count($dirs) && !$onlyFiles)
 {
  foreach($dirs as $k => $v)
  {
   if($limit)
   {
	if($idx < $limitFrom)
	{
	 $idx++;
	 continue;
	}
	if(count($outArr['dirs']) == $limit)
	 break;
   }
   $out.= $v."\n";
   $a = array('name'=>basename($v), 'path'=>ltrim($v,"/"));
   $outArr['dirs'][] = $a;
   $idx++;
  }
 }
 if(count($files) && !$onlyDirs)
 {
  foreach($files as $k => $v)
  {
   if($limit)
   {
	if($idx < $limitFrom)
	{
	 $idx++;
	 continue;
	}
	if(count($outArr['dirs'])+count($outArr['files']) == $limit)
	 break;
   }

   $out.= $v."\n";
   $siz = $fileSize = filesize($basepath.$v);
   $sar = array('bytes','kB','MB','GB','TB');
   $sx = 0;
   while($siz > 1024)
   {
    $siz = $siz/1024;
	$sx++;
    if($sx == 4)
	 break;
   }
   $humanSize = str_replace('.00','',sprintf("%.2f",$siz))." ".$sar[$sx];
   $a = array('name'=>basename($v), 'path'=>ltrim($v,"/"), 'size'=>$siz, 'humansize'=>$humanSize, 'mtime'=>filemtime($basepath.$v));
   $outArr['files'][] = $a;
   $idx++;
  }
 }

 if(!count($dirs) && !count($files))
  $out.= "No files found.";
 else
  $out.= (count($dirs) ? count($dirs)." directory" : "")
	.(count($files) ? (count($dirs) ? " and " : "").count($files)." files" : "")." found.";

 return array("message"=>$out, "outarr"=>$outArr);
}

function ls_recursiveIncludeDirs($basepath, $dir, $orderBy="name")
{
 $ret = array();
 $dirs = array();
 $d = dir($basepath.$dir);
 while(FALSE !== ($entry = $d->read()))
 {
  if($entry == '.' || $entry == '..')
   continue;
  if(substr($entry, -1) == "~")
   continue;
  $Entry = rtrim($dir,'/').'/'.ltrim($entry,'/');
  if(is_dir($basepath.$Entry)) // is a directory //
   $dirs[] = $Entry;
 }
 if(count($dirs))
 {
  switch($orderBy)
  {
   case 'name' : case 'name ASC' : sort($dirs); break;
   case 'name DESC' : rsort($dirs); break;
  }
 }
 foreach($dirs as $k => $v)
 {
  $a = array('name'=>basename($v),'path'=>ltrim($v,"/"));
  $subdirs = ls_recursiveIncludeDirs($basepath,$v,$orderBy);
  if(count($subdirs))
   $a['subdirs'] = $subdirs;
  $ret[] = $a;
 }
 return $ret;
}

function ls_recursiveIncludeFiles($basepath, $dir, $orderBy="name",$filters=null)
{
 $ret = array();
 $dirs = array();
 $files = array();

 $d = dir($basepath.$dir);
 while(FALSE !== ($entry = $d->read()))
 {
  if($entry == '.' || $entry == '..')
   continue;
  if(substr($entry, -1) == "~")
   continue;
  $Entry = rtrim($dir,'/').'/'.ltrim($entry,'/');
  if(is_dir($basepath.$Entry)) // is a directory //
   $dirs[] = $Entry;
  else
  {
   if($filters)
   {
	$pos = strrpos($entry,".");
	if($pos === false)
	 continue;
	$ext = substr($entry,$pos+1);
	if(!in_array($ext,$filters))
	 continue;
   }
   $files[] = $Entry;
  }
 }

 if(count($files))
 {
  switch($orderBy)
  {
   case 'name' : case 'name ASC' : sort($files); break;
   case 'name DESC' : rsort($files); break;
  }
 }
 foreach($files as $k => $v)
 {
  $ret[] = ltrim($v,"/");
 }

 if(count($dirs))
 {
  switch($orderBy)
  {
   case 'name' : case 'name ASC' : sort($dirs); break;
   case 'name DESC' : rsort($dirs); break;
  }
 }
 foreach($dirs as $k => $v)
 {
  $sublist = ls_recursiveIncludeFiles($basepath,$v,$orderBy,$filters);
  for($c=0; $c < count($sublist); $c++)
   $ret[] = $sublist[$c];
 }

 return $ret;
}

