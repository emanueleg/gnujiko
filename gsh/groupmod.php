<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-01-2012
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Edit a group
 #VERSION: 2.0beta
 #CHANGELOG: Bug fix
 #TODO:
 
*/

function shell_groupmod($args, $sessid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"PERMISSION_DENIED");
 
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$newName = $args[$c+1]; $c++;} break;
   case '-id' : {$groupId=$args[$c+1]; $c++;} break;
   case '-newid' : {$newID = $args[$c+1]; $c++;} break;
   default : $group = $args[$c]; break;
  }
 
 if(!$group && !$groupId)
  return array("message"=>"You must specify the group to change", "error"=>"INVALID_GROUP_NAME");
 
 // check if group exists //
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_groups WHERE ".($groupId ? "id='$groupId'" : "name='$group'")." LIMIT 1");
 if(!$db->Read())
  return array("message"=>"Group ".($groupId ? "#$groupId" : $group)." does not exists", "error"=>"GROUP_DOES_NOT_EXISTS");
 $gid = $db->record['id'];
 $oldName = $db->record['name'];
 $db->Close();

 $q = "";
 if($newName)
  $q.= ",name='$newName'";
 if($newID)
  $q.= ",id='$newID'";
 if(!$q)
  return array("message"=>"Nothing has changed","error"=>"INVALID_ARGUMENTS");

 if($newID)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM gnujiko_groups WHERE id='".$newID."'");
  if(!$db->Read())
  {
   $db->RunQuery("UPDATE gnujiko_users SET group_id='".$newID."' WHERE group_id='".$gid."'");
   $db->RunQuery("UPDATE gnujiko_usergroups SET gid='$newID' WHERE gid='$gid'");
  }
  else
   return array('message'=>"Unable to modify the group ID. Group with ID $newID already exists.","error"=>"GROUPID_ALREADY_EXISTS");
  $db->Close();
 }

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE gnujiko_groups SET ".ltrim($q,",")." WHERE id='$gid'");
 $db->Close();

 return array("message"=>"Group ".($groupId ? "#groupId" : $group)." has been changed", "outarr"=>array("gid"=>$gid,"name"=>($newName ? $newName : $oldName)));
}

