<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-01-2012
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Add groups
 #VERSION: 2.0beta
 #CHANGELOG: 21-01-2012 : Parameter --first-user added.
 #TODO:
 
*/

function shell_groupadd($args, $sessid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"PERMISSION_DENIED");

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-setid' : {$setId=$args[$c+1]; $c++;} break;
   case '--first-user' : $firstUser=true; break;
   default : {if($group) $user = $args[$c]; else $group = $args[$c]; } break;
  }

 if($user)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM gnujiko_users WHERE username='$user' LIMIT 1");
  if(!$db->Read())
   return array("message"=>"User $user does not exists", "error"=>"USER_DOES_NOT_EXISTS");
  $uid = $db->record['id'];
  $db->Close();
 }
 else if($firstUser)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id,username FROM gnujiko_users WHERE username!='root' AND disabled=0 ORDER BY id ASC LIMIT 1");
  if($db->Read())
  {
   $uid = $db->record['id'];
   $user = $db->record['username'];
  }
  $db->Close();
 }

 if($group)
 {
  // check if exists //
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM gnujiko_groups WHERE name='$group' LIMIT 1");
  if(!$db->Read())
  {
   // create group //
   $db2 = new AlpaDatabase();
   if($setId)
   {
	$db3 = new AlpaDatabase();
	$db3->RunQuery("SELECT * FROM gnujiko_groups WHERE id='".$setId."'");
	if($db3->Read())
	 return array("message"=>"Group with ID #".$setId." already exists.","error"=>"GROUPID_ALREADY_EXISTS");
	$db3->Close();
	$db2->RunQuery("INSERT INTO gnujiko_groups(id,name) VALUES('$setId','$group')");
	$gid = $setId;
   }
   else
   {
    $db2->RunQuery("INSERT INTO gnujiko_groups(name) VALUES('$group')");
    $gid = mysql_insert_id();
   }
   $db2->Close();
  }
  else if($user)
   $gid = $db->record['id'];
  else
   return array("message"=>"Group $group already exists","outarr"=>array('gid'=>$gid));
  $db->Close();
  if($user) // insert user into group //
  {
   // check if user is already into group //
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT * FROM gnujiko_usergroups WHERE uid='$uid' AND gid='$gid' LIMIT 1");
   if($db2->Read())
    return array("message"=>"User $user is already into group $group","outarr"=>array("uid"=>$uid,"gid"=>$gid));
   // insert user into group //
   $db2 = new AlpaDatabase();
   $db2->RunQuery("INSERT INTO gnujiko_usergroups(uid,gid) VALUES('$uid','$gid')");
   $db2->Close();
  }
 }
 return array("message"=>$user ? "User $user has been inserted into group $group" : "Group $group has been created.", "outarr"=>array('uid'=>$uid,'gid'=>$gid));
}

