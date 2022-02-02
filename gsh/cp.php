<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 29-06-2013
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Copy files and directory
 #VERSION: 2.2beta
 #TODO:
 #CHANGELOG: 29-06-2013 : Bug fix. 
			 28-11-2012 : Bug fix.

*/

function shell_cp($args, $sessid, $shellid=0)
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
   return array("message"=>"Unable to copy files/directories: Your account has not privileges to copy files or folders!","error"=>"MKDIR_DISABLED");
  $db->Close();

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $basepath = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
  return array("message"=>"Unable to copy files or directory: you don't have a valid account!","error"=>"INVALID_USER");

 include_once($_BASE_PATH."include/filesfunc.php");

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

 $all=false;
 for($c=0; $c < count($sources); $c++)
 {
  $src = ltrim($sources[$c],"/");
  if(strpos($src, "*") == (strlen($src)-1))
  {
   $all = true;
   $src = substr($src,0,-1);
  }

  if(!file_exists($basepath.rtrim($src,"/")))
   return array('message'=>"Source not found. $src does not exists.",'error'=>"SRC_DOES_NOT_EXISTS");
  $dest = ltrim($dst,"/");
  if(is_dir($basepath.$dst) && !$all)
   $dest = rtrim($dst,"/")."/".basename($src);
  if(!full_copy($basepath.$src,$basepath.$dest))
   return array("message"=>"Unable to copy $src into $dest","error"=>"PERMISSION_DENIED");
 }
 $out.= "done!";
 return array("message"=>$out);
}

