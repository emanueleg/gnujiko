<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-04-2012
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Make Directory
 #VERSION: 2.1beta
 #CHANGELOG: 11-04-2013 : Sistemato i permessi ai files.
			 10-02-2012 : Bug fix with FTP.
			 11-09-2011 : Aggiunto argomento forceroot
 #TODO:
 
*/

function shell_mkdir($args, $sessid, $shellid=0,$forceroot=false)
{
 global $_BASE_PATH, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_USERS_HOMES, $_DEFAULT_FILE_PERMS;
 $sessInfo = sessionInfo($sessid);
 
 if($sessInfo['uname'] == "root")
  $basepath = $_BASE_PATH;
 else if($sessInfo['uid'])
 {
  /* Check if user is able for create folders */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM gnujiko_user_privileges WHERE uid='".$sessInfo['uid']."'");
  $db->Read();
  if(!$db->record['mkdir_enable'])
   return array("message"=>"Unable to create folder: Your account has not privileges to create folders!","error"=>"MKDIR_DISABLED");
  $db->Close();

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $basepath = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
  return array("message"=>"Unable to create folder: you don't have a valid account!","error"=>"INVALID_USER");

 if($forceroot)
  $basepath = $_BASE_PATH;

 $outArr = array();

 $dir = $args[0];
 if(substr($dir,-1) != "/")
  $dir.= "/";
 $p = explode("/", $dir);
 $path = $basepath;
 for ($c=0; $c < (count($p)-1); $c++)
 {
  $path.= $p[$c]."/";
  if($path == "/")
   continue;
  if(!is_dir($path))
  {
   if($_FTP_USERNAME)
   {
    $conn = @ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
    if($conn && @ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
    {
     if($_FTP_PATH)
     {
	  if(!@ftp_chdir($conn, $_FTP_PATH))
	  {
	   $out.= "Unable to change path to $_FTP_PATH with FTP\n";
	   return array("message"=>$out,"error"=>"UNABLE_TO_CHANGE_PATH");
	  }
      $fldPath = str_replace("../","",$path);
	  $fldPath = ltrim(rtrim($fldPath,"/"),"/");
     }
     else
	 {
      $fldPath = ltrim(rtrim($path,"/"),"/");
	 }
	 if(@ftp_chdir($conn,$fldPath))
	 {
	  continue;
	 }

     if(@!ftp_mkdir($conn, $fldPath))
     {
      $out.= "unable to create folder $fldPath with FTP into ".ftp_pwd($conn)."\n";
      return array("message"=>$out,"error"=>"UNABLE_TO_CREATE_FOLDER");
     }
     else if(!@ftp_chmod($conn, $_DEFAULT_FILE_PERMS, $fldPath))
     {
      $out.= "unable to change permission to folder $path \n";
      return array("message"=>$out,"error"=>"UNABLE_TO_CHANGE_PERMISSION");
     }
     @ftp_close($conn);
     continue;
    }
    else
	{
	 $out.= "unable to connect with FTP into server $_FTP_SERVER. Please check if FTP service is installed.\n";
	 return array("message"=>$out,"error"=>"FTP_CONNECTION_FAILED");
	}
   }
   if(!@mkdir($path))
   {
    $out.= "unable to create folder $path permission denied!\n";
    return array("message"=>$out,"error"=>"UNABLE_TO_CREATE_FOLDER");
   }
   else if(!@chmod($path, $_DEFAULT_FILE_PERMS))
   {
    $out.= "unable to change permission to folder $path \n";
    return array("message"=>$out,"error"=>"UNABLE_TO_CHANGE_PERMISSION");
   }
  }
 }
 $outArr['path'] = $dir;
 $out.= "directory $dir has been created!\n";
 return array("message"=>$out, "outarr"=>$outArr);
}

