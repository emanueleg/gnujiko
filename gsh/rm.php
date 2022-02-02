<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 08-01-2012
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Remove file and directory
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function shell_rm($args, $sessid, $shellid=0)
{
 global $_BASE_PATH, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_USERS_HOMES;
 $sessInfo = sessionInfo($sessid);
 
 if($sessInfo['uname'] == "root")
  $basepath = $_BASE_PATH;
 else if($sessInfo['uid'])
 {
  /* Check if user is able for create/remove folders */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM gnujiko_user_privileges WHERE uid='".$sessInfo['uid']."'");
  $db->Read();
  if(!$db->record['mkdir_enable'])
   return array("message"=>"Unable to remove folder: Your account has not privileges to create/remove folders!","error"=>"MKDIR_DISABLED");
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
 $files = array();

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   default : $files[] = $args[$c]; break;
  }

 if(!count($files))
  return array('message'=>"You must specify a file or directory","error"=>"INVALID_FILE");

 for($c=0; $c < count($files); $c++)
 {
  $file = rtrim($files[$c],"/");

  if(!file_exists($basepath.$file))
   return array("message"=>"$file does not exists!","error"=>"FILE_DOES_NOT_EXISTS");

  if(!is_dir($basepath.$file))
  {
   @unlink($basepath.$file);
   if(file_exists($basepath.$file))
    return array("message"=>"Unable to delete $file","error"=>"PERMISSION_DENIED");
  }
  else
  {
   $ret = rm_recursiveRemoveDirs($basepath.$file);
   if($ret['error'])
    return $ret;
  }
 }
 return array("message"=>"done!");
}

function rm_recursiveRemoveDirs($dir)
{ 
 if(is_dir($dir))
  $dir_handle = opendir($dir);
 if(!$dir_handle)
  return array("message"=>"Invalid directory handle","error"=>"INVALID_DIRECTORY_HANDLE");
 while($file = readdir($dir_handle)) 
 {
  if($file != "." && $file != "..") 
  {
   if(!is_dir($dir."/".$file))
   {
    @unlink($dir."/".$file);
	if(file_exists($dir."/".$file))
	 return array('message'=>"Unable to remove ".$dir."/".$file,'error'=>"PERMISSION_DENIED");
   }
   else
   {
    $ret = rm_recursiveRemoveDirs($dir.'/'.$file);
	if($ret['error'])
	 return $ret;
   }
  }
 }
 closedir($dir_handle);
 rmdir($dir);
 if(file_exists($dir))
  return array("message"=>"Unable to delete $dir","error"=>"PERMISSION_DENIED");
 return array('message'=>"done!");
}

