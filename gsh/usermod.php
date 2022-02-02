<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 29-07-2013
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Edit user
 #VERSION: 2.3beta
 #CHANGELOG: 29-07-2013 : Alcuni bug fix.
			 10-04-2013 : Aggiunto parametri --insert-into-groups e --remove-from-groups.
			 05-01-2012 : Aggiunto parametro per abilitare/disabilitare account, e parametro per impostare i privilegi.
 #TODO:
 
*/

function shell_usermod($args, $sessid)
{
 global $_BASE_PATH, $_USERS_HOMES;
 include_once($_BASE_PATH."include/userfunc.php");

 $sessInfo = sessionInfo($sessid);

 $output = "";
 $outArr = array();

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-home' : {$home=str_replace(array('../','./','/'),array("","",""),$args[$c+1]); $c++;} break;
   case '--enable-shell' : $enableShell = true; break;
   case '--disable-shell' : $disableShell = true; break;
   case '-uid' : {$uid=$args[$c+1]; $c++;} break;
   case '-gid' : {$gid=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '--disabled-password' : $disabledPassword = true; break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-password' : {$password=$args[$c+1]; $c++;} break;
   case '-fullname' : {$fullname=$args[$c+1]; $c++;} break;
   case '-email' : {$email=$args[$c+1]; $c++;} break;
   case '--disable-account' : $disableAccount=true; break;
   case '--enable-account' : $enableAccount=true; break;
   case '-privileges' : {$privileges=$args[$c+1]; $c++; } break;
   case '--insert-into-groups' : {$insertIntoGroups=$args[$c+1]; $c++;} break;
   case '--remove-from-groups' : {$removeFromGroups=$args[$c+1]; $c++;} break;
   default : if(!$userName) $userName = $args[$c]; break;
  }

 if($enableShell && ($sessInfo['uname'] != "root"))
  return array("message"=>"Only root can enable shell for this user", "error"=>"PERMISSION_DENIED");
 if($disableShell && ($sessInfo['uname'] != "root"))
  return array("message"=>"Only root can disable shell for this user", "error"=>"PERMISSION_DENIED");
 if($name && ($sessInfo['uname'] != "root"))
  return array("message"=>"Only root can change user name", "error"=>"PERMISSION_DENIED");
 if($uid && ($sessInfo['uname'] != "root"))
  return array("message"=>"Only root can change user by UID", "error"=>"PERMISSION_DENIED");
 if(($gid || $group) && ($sessInfo['uname'] != "root"))
  return array("message"=>"Only root can change group ID","error"=>"PERMISSION_DENIED");
 if(($disableAccount || $enableAccount) && ($sessInfo['uname'] != "root"))
  return array("message"=>"Only root can enable/disable accounts","error"=>"PERMISSION_DENIED");


 if($gid)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM gnujiko_groups WHERE id='".$gid."'");
  if(!$db->Read())
   return array("message"=>"Group ".$gid." does not exists", "error"=>"GROUP_DOES_NOT_EXISTS");
  $db->Close();
 }
 else if($group)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM gnujiko_groups WHERE name='".$group."' LIMIT 1");
  if(!$db->Read())
   return array("message"=>"Group ".$group." does not exists", "error"=>"GROUP_DOES_NOT_EXISTS");
  $gid = $db->record['id'];
  $db->Close();
 }
 if(!$uid && $userName)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM gnujiko_users WHERE username='".$db->Purify($userName)."' LIMIT 1");
  if(!$db->Read())
   return array("message"=>"User ".$userName." does not exists.","error"=>"USER_DOES_NOT_EXISTS");
  $uid = $db->record['id'];
  $db->Close();
 }

 // get user info //
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_users WHERE id='".($uid ? $uid : $sessInfo['uid'])."'");
 if(!$db->Read())
  return array("message"=>"User does not exists", "error"=>"USER_DOES_NOT_EXISTS");
 $userInfo = $db->record;
 $db->Close();

 $q = "";
 if($home && ($userInfo['homedir'] != $home))
 {
  if($sessInfo['uname'] != "root")
   $output.= "Warning: You have changed your home directory, but only root can do it!\n";
  else
  {
   if($userInfo['homedir'] && file_exists($_BASE_PATH.$_USERS_HOMES.$userInfo['homedir']))
    $ret = GShell("mv `".$_USERS_HOMES.$userInfo['homedir']."` `".$_USERS_HOMES.$home."`",$sessid,$shellid);
   else
	$ret = GShell("mkdir `".$_USERS_HOMES.$home."`",$sessid,$shellid);
   if($ret['error'])
	return $ret;
   $q.= ",homedir='".$home."'";
  }
 }
 else if($userInfo['homedir'] && !file_exists($_BASE_PATH.$_USERS_HOMES.$userInfo['homedir']))
 {
  // cerca di ricreare la cartella //
  if($sessInfo['uname'] != "root")
   $output.= "Warning: The home directory for the user ".$userInfo['username']." '".$userInfo['homedir']."/' does not exists! You must retype command with root user, because only root can create and update the home directory for all users.\n";
  else
  {
   $ret = GShell("mkdir `".$_USERS_HOMES.$home."`",$sessid,$shellid);
   if($ret['error'])
	return $ret;
   if(!file_exists($_BASE_PATH.$_USERS_HOMES.$home))
	$output.= "Error! I cannot create directory into '".$_USERS_HOMES."', please verify and correct the file permissions. Must be readable and writable!\n";
   else
    $output.= "Notice: The user's home folder ".$userInfo['username']." did not exist, so I proceeded to recreate it.\n";
  }
 }

 if($enableShell)
  $q.= ",enableshell='1'";
 else if($disableShell)
  $q.= ",enableshell='0'";
 if($gid)
 {
  $q.= ",group_id='".$gid."'";
  $userInfo['group_id'] = $gid;
 }
 if($name)
 {
  $q.= ",username='".$name."'";
  $userInfo['username'] = $name;
 }
 if(isset($password))
  $q.= ",password='".md5($password.$userInfo['regtime'])."'";
 else if($disabledPassword)
  $q.= ",password='!";
 if(isset($fullname))
  $q.= ",fullname='$fullname'";
 if(isset($email))
  $q.= ",email='$email'";
 if($disableAccount)
  $q.= ",disabled='1'";
 else if($enableAccount)
  $q.= ",disabled='0'";
 
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE gnujiko_users SET ".ltrim($q,",")." WHERE id='".$userInfo['id']."'");
 $db->Close();

 if($privileges)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM gnujiko_user_privileges WHERE uid='".$userInfo['id']."'");
  if(!$db->Read())
   $db->RunQuery("INSERT INTO gnujiko_user_privileges(uid) VALUES('".$userInfo['id']."')");
  $db->RunQuery("UPDATE gnujiko_user_privileges SET ".$privileges." WHERE uid='".$userInfo['id']."'");
  $db->Close();
 }

 if($insertIntoGroups)
 {
  if(strpos($insertIntoGroups,",") !== false)
   $groups = explode(",",$insertIntoGroups);
  else
   $groups = array(0=>$insertIntoGroups);
  for($c=0; $c < count($groups); $c++)
  {
   $groupId = _getGID($groups[$c]);
   if($groupId && ($groupId != $userInfo['group_id']) && !_userInGroupId($groupId,$userInfo['id']))
   {
	$db = new AlpaDatabase();
	$db->RunQuery("INSERT INTO gnujiko_usergroups(uid,gid) VALUES('".$userInfo['id']."','".$groupId."')");
	$db->Close();
	$output.= $userInfo['username']." has been inserted into group ".$groups[$c]."\n";
   }
   else
   {
    if(!$groupId)
	 $output.= "Warning: Group '".$groups[$c]."' does not exists!\n";
	else
	 $output.= "Warning: The user '".$userInfo['username']."' is already into group '".$groups[$c]."'\n";
   }
  }
 }

 if($removeFromGroups)
 {
  if(strpos($removeFromGroups,",") !== false)
   $groups = explode(",",$removeFromGroups);
  else
   $groups = array(0=>$removeFromGroups);
  for($c=0; $c < count($groups); $c++)
  {
   $groupId = _getGID($groups[$c]);
   if($groupId && ($groupId != $userInfo['group_id']) && _userInGroupId($groupId,$userInfo['id']))
   {
	$db = new AlpaDatabase();
	$db->RunQuery("DELETE FROM gnujiko_usergroups WHERE uid='".$userInfo['id']."' AND gid='".$groupId."'");
	$db->Close();
	$output.= $userInfo['username']." has been removed from group ".$groups[$c]."\n";
   }
   else
   {
    if(!$groupId)
	 $output.= "Warning: Group '".$groups[$c]."' does not exists!\n";
	else
	 $output.= "Warning: The user '".$userInfo['username']."' is not into group '".$groups[$c]."'\n";
   }
  }
 }

 return array("message"=>$output."\nUser info has been updated!");
}

