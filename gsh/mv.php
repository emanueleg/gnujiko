<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2015 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-12-2015
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Move or rename directory and files
 #VERSION: 2.2beta
 #TODO:
 #CHANGELOG: 05-12-2015 : Bug fix.
			 28-11-2012 : Bug fix.
 
*/

function shell_mv($args, $sessid, $shellid=0)
{
 global $_BASE_PATH, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_USERS_HOMES;
 $sessInfo = sessionInfo($sessid);
 
 if($sessInfo['uname'] == "root")
  $basepath = $_BASE_PATH;
 else if($sessInfo['uid'])
 {
  /* Check if user is able for move files and folders */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM gnujiko_user_privileges WHERE uid='".$sessInfo['uid']."'");
  $db->Read();
  if(!$db->record['mkdir_enable'])
   return array("message"=>"Unable to move files/directories: Your account has not privileges to move files or folders!","error"=>"MKDIR_DISABLED");
  $db->Close();

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $basepath = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
  return array("message"=>"Unable to move files or directory: you don't have a valid account!","error"=>"INVALID_USER");

 $out = "";
 $outArr = array();
 $sources = array();
 $dst = "";

 for($c=0; $c < count($args); $c++)
 {
  switch($args[$c])
  {
   case '-s' : case '-source' : {$sources[]=$args[$c+1]; $c++;} break;
   case '-d' : case '-dest' : {$dst=$args[$c+1]; $c++;} break;
   default : {if(count($sources)) $dst=$args[$c]; else $sources[]=$args[$c];} break;
  }
 }

 if(!count($sources))
  return array('message'=>"You must specify source file or dir.",'error'=>"INVALID_SOURCE");
 if(!$dst)
  return array('message'=>"You must specify destination folder",'error'=>"INVALID_DESTINATION");

 // verify if destination folder exists and is writable //
 if(strpos(basename($dst), ".") === false)
  $destDir = rtrim($dst,"/")."/";
 else
  $destDir = substr($dst, 0, -strlen(basename($dst)));
 if(strpos($destDir, $basepath) == 0)
  $destDir = str_replace($basepath, "", $destDir);

 if(!is_dir($basepath.$destDir))
 {
  $ret = GShell("mkdir `".$destDir."`",$sessid,$shellid);
  if($ret['error'])
   return $ret;
 }

 for($c=0; $c < count($sources); $c++)
 {
  $src = $sources[$c];
  if(strpos($src, $basepath) == 0)
   $src = str_replace($basepath, "", $src);
  if(!file_exists($basepath.$src))
   return array('message'=>"Source not found. $src does not exists.",'error'=>"SRC_DOES_NOT_EXISTS");

  $dest = $dst;
  if(strpos($dest, $basepath) == 0)
   $dest = str_replace($basepath, "", $dest);
  if(is_dir($basepath.$dest))
   $dest = rtrim($dest,"/")."/".basename($src);

  @rename($basepath.$src,$basepath.$dest);

  if(!file_exists($basepath.$dest))
   return array('message'=>"Unable to move/rename $src with $dest.",'error'=>"MOVE_FAILED");
 }
 $out.= "done!";
 return array("message"=>$out);
}

