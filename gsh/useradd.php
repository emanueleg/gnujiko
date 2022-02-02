<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-04-2013
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Add user 
 #VERSION: 2.4beta
 #CHANGELOG: 30-04-2013 : Bug fix.
			 10-04-2013 : Bug fix.
			 03-03-2013 : --insert-into-groups parameter added.
			 13-01-2013 : useradd bug fix.
			 07-01-2012 : Aggiunto parametro per impostare i privilegi.
 #TODO:
 
*/

function shell_useradd($args, $sessid)
{
 global $_BASE_PATH, $_USERS_HOMES;
 include_once($_BASE_PATH."include/userfunc.php");

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $output = "";
 $outArr = array();

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-home' : {$home=str_replace(array('../','./','/'),array("","",""),$args[$c+1]); $c++;} break;
   case '--enable-shell' : $enableShell = true; break;
   case '--no-create-home' : $noCreateHome = true; break;
   case '-uid' : {$uid=$args[$c+1]; $c++;} break;
   case '-gid' : {$gid=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '--in-group' : {$inGroup=$args[$c+1]; $c++;} break;
   case '--insert-into-groups' : {$insertIntoGroups=$args[$c+1]; $c++;} break;
   case '--disabled-password' : $disabledPassword = true; break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-password' : {$password=$args[$c+1]; $c++;} break;
   case '-fullname' : {$fullname=$args[$c+1]; $c++;} break;
   case '-email' : {$email=$args[$c+1]; $c++;} break;
   case '-privileges' : {$privileges=$args[$c+1]; $c++; } break;
   default : {if(!$name) $name=$args[$c]; } break;
  }

 if(!$name)
  return array("message"=>"You must specify user. (with -name username)","error"=>"INVALID_USER_NAME");

 /* check if user already exists */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM gnujiko_users WHERE username='".$name."'");
 if($db->Read())
  return array("message"=>"User $name already exists.", "error"=>"USER_ALREADY_EXISTS");
 $db->Close();

 if(!$fullname)
  $fullname = $name;

 if($uid) // check if uid already exists
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT username FROM gnujiko_users WHERE id='$uid'");
  if($db->Read())
   return array("message"=>"UID $uid already exists.", "error"=>"UID_ALREADY_EXISTS");
  $db->Close();
 }
 if($gid) // check if gid exists
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT name FROM gnujiko_groups WHERE id='$gid'");
  if(!$db->Read())
   return array("message"=>"GID $gid does not exists.", "error"=>"GID_DOES_NOT_EXISTS");
  $db->Close();
 }
 if($group) // check if group exists
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM gnujiko_groups WHERE name='$group' LIMIT 1");
  if($db->Read())
   $gid = $db->record['id'];
  else
  {
   $db->RunQuery("INSERT INTO gnujiko_groups(name) VALUES('$group')");
   $gid = mysql_insert_id();
  }
  $db->Close();
 }
 if($inGroup) // check if group exists
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM gnujiko_groups WHERE name='$inGroup' LIMIT 1");
  if($db->Read())
   $inGroupID = $db->record['id'];
  else
  {
   $db->RunQuery("INSERT INTO gnujiko_groups(name) VALUES('$inGroup')");
   $inGroupID = mysql_insert_id();
  }
  $db->Close();
 }

 if(!$gid) // create group with same name of user //
 {
  $db = new AlpaDatabase();
  $db->RunQuery("INSERT INTO gnujiko_groups(name) VALUES('$name')");
  $gid = mysql_insert_id();
  $db->Close();
 }
 
 // create home dir //
 if(!$home && !$noCreateHome)
  $home = $name;
 if($home)
 {
  $homedir = $_BASE_PATH.$_USERS_HOMES.$home;
  if(!file_exists($homedir))
  {
   $ret = GShell("mkdir `".$_USERS_HOMES.$home."`",$sessid,$shellid);
   if($ret['error'])
	return $ret;
   // create temp dir //
   $ret = GShell("mkdir `".$_USERS_HOMES.$home."/tmp`",$sessid,$shellid);
  }
 }

 // create user //
 $now = time();
 $cryptpassword = md5($password.$now);
 if($disabledPassword)
  $cryptpassword = "!";
 $db = new AlpaDatabase();
 $q = "INSERT INTO gnujiko_users(".($uid ? "id," : "")."group_id,username,password,email,fullname,homedir,regtime,enableshell) VALUES(".($uid ? "'$uid'," : "")."'$gid','$name','$cryptpassword','$email','$fullname','$home','$now','$enableShell')";
 $db->RunQuery($q);
 $uid = mysql_insert_id();
 if($privileges)
 {
  $db->RunQuery("INSERT INTO gnujiko_user_privileges(uid) VALUES('".$uid."')");
  $db->RunQuery("UPDATE gnujiko_user_privileges SET ".$privileges." WHERE uid='".$uid."'");
 }
 $db->Close();
 $output.= "User $name has been created!\n";

 if($inGroupID)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("INSERT INTO gnujiko_usergroups(uid,gid) VALUES('$uid','$inGroupID')");
  $db->Close();
  $output.= "$name has been inserted into group ".$group."\n";
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
   if($groupId && ($groupId != $gid) && !_userInGroupId($groupId,$uid))
   {
	$db = new AlpaDatabase();
	$db->RunQuery("INSERT INTO gnujiko_usergroups(uid,gid) VALUES('".$uid."','".$groupId."')");
	$db->Close();
	$output.= "$name has been inserted into group ".$groups[$c]."\n";
   }
   else
   {
    if(!$groupId)
	 $output.= "Warning: Group '".$groups[$c]."' does not exists!\n";
	else
	 $output.= "Warning: The user '".$name."' is already into group '".$groups[$c]."'\n";
   }
  }

 }

 // return array //
 $outArr = array('uid'=>$uid,'gid'=>$gid,'name'=>$name,"email"=>$email,"fullname"=>$fullname,"homedir"=>$home,"regtime"=>$now);

 return array('message'=>trim($output), 'outarr'=>$outArr);
}

