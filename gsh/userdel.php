<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-05-2016
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Remove a user, or remove user from a group
 #VERSION: 2.1beta
 #CHANGELOG: 11-05-2016 : Integrato con Rubrica.
			 08-01-2012 : Aggiunto paramentro -group
 #TODO:
 
*/

function shell_userdel($args, $sessid)
{
 global $_BASE_PATH, $_USERS_HOMES;

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"PERMISSION_DENIED");

 $out = "";
 
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-all' : $removeAll = true; break;
   case '-home' : $removeHome = true; break;
   case '-group' : $removeGroup = true; break;
   default : {if($user) $group = $args[$c]; else $user = $args[$c]; } break;
  }
 
 if(!$user)
  return array("message"=>"You must specify the user to delete", "error"=>"INVALID_USER_NAME");
 
 // check if user exists //
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_users WHERE username='".$user."' LIMIT 1");
 if(!$db->Read())
  return array("message"=>"User $user does not exists", "error"=>"USER_DOES_NOT_EXISTS");
 $uid = $db->record['id'];
 $gid = $db->record['group_id'];
 $homedir = $db->record['homedir']; 
 $rubricaId = $db->record['rubrica_id'];

 if($group)
 {
  // check if group exists //
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT * FROM gnujiko_groups WHERE name='".$group."' LIMIT 1");
  if(!$db2->Read())
   return array("message"=>"Group ".$group." does not exists", "error"=>"GROUP_DOES_NOT_EXISTS");
  $gid = $db2->record['id'];
  $db2->Close();
  $db->RunQuery("DELETE FROM gnujiko_usergroups WHERE uid='".$uid."' AND gid='".$gid."'");
  $db->Close();
  return array("message"=>"User ".$user." has been removed from group ".$group);
 }
 else
 {
  $db->RunQuery("DELETE FROM gnujiko_users WHERE id='".$uid."'");
  $db->RunQuery("DELETE FROM gnujiko_usergroups WHERE uid='".$uid."'");
  $db->Close();
  
  // remove group (if empty) //
  if($removeGroup || $removeAll)
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT * FROM gnujiko_usergroups WHERE gid='$gid'");
   if(!$db->Read())
    $db->RunQuery("DELETE FROM gnujiko_groups WHERE id='$gid'");
   $db->Close();
  }
  
  // remove home //
  if($removeHome || $removeAll)
  {
   if($homedir)
    shell_userdel_rmdirr($_BASE_PATH.$_USERS_HOMES.$homedir);
  }

  if($rubricaId) // remove from rubrica
  {
   $ret = GShell("dynarc delete-item -ap rubrica -id '".$rubricaId."'", $sessid, $shellid);
   if($ret['error']) $out.= "Warning: unable to delete user from rubrica.\n".$ret['message']; 
  }
  $out.= "User ".$user." has been removed!\n";

  return array("message"=>$out);
 }
}
//----------------------------------------------------------------------------------------------------------------------//
function shell_userdel_rmdirr($dir) 
{
 global $_FTP_USER, $_FTP_PASSWORD, $_FTP_BASEPATH;

 if (substr($dir,-1) != "/") $dir .= "/";
 if (!is_dir($dir)) return false;

 if (($dh = opendir($dir)) !== false) 
 {
  while (($entry = readdir($dh)) !== false) 
  {
   if ($entry != "." && $entry != "..") 
   {
    if (is_file($dir . $entry) || is_link($dir . $entry))
	{
     if(!@unlink($dir . $entry))
	 {
	  // try with ftp //
      if($_FTP_USER)
      {
       $conn = ftp_connect($_SERVER['SERVER_NAME']);
       if(ftp_login($conn,$_FTP_USER,$_FTP_PASSWORD))
       {
        if($_FTP_BASEPATH)
        {
         $fldPath = str_replace("../","",$dir.$entry);
         $fldPath = $_FTP_BASEPATH.$fldPath;
        }
        else
         $fldPath = $dir.$entry;
        if(!ftp_delete($conn, $fldPath))
         return false;
		ftp_close($conn);
       }
      }
	 }
	}
    else if (is_dir($dir . $entry))
	{
     if(!rmdirr($dir . $entry))
	 { // try with ftp //
      if($_FTP_USER)
      {
       $conn = ftp_connect($_SERVER['SERVER_NAME']);
       if(ftp_login($conn,$_FTP_USER,$_FTP_PASSWORD))
       {
        if($_FTP_BASEPATH)
        {
         $fldPath = str_replace("../","",$dir.$entry);
         $fldPath = $_FTP_BASEPATH.$fldPath;
        }
        else
         $fldPath = $entry;
        if(!ftp_rmdir($conn, $fldPath))
         return false;
		ftp_close($conn);
       }
      }
	 }
	}
   }
  }
  closedir($dh);
  if(!rmdir($dir))
  {
   if($_FTP_USER)
   {
    $conn = ftp_connect($_SERVER['SERVER_NAME']);
    if(ftp_login($conn,$_FTP_USER,$_FTP_PASSWORD))
    {
     if($_FTP_BASEPATH)
     {
      $fldPath = str_replace("../","",$dir);
      $fldPath = $_FTP_BASEPATH.$fldPath;
     }
     else
      $fldPath = $dir;
     if(!ftp_rmdir($conn, $fldPath))
      return false;
	 ftp_close($conn);
    }
   }
   else
	return false;
  }
  return true;
 }
 return false;
}

