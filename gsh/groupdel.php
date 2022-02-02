<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2010 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 19-01-2010
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Remove a group
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function shell_groupdel($args, $sessid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"PERMISSION_DENIED");
 
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   default : $group = $args[$c]; break;
  }
 
 if(!$group)
  return array("message"=>"You must specify the group to delete", "error"=>"INVALID_GROUP_NAME");
 
 // check if group exists //
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_groups WHERE name='$group' LIMIT 1");
 if(!$db->Read())
  return array("message"=>"Group $group does not exists", "error"=>"GROUP_DOES_NOT_EXISTS");
 $gid = $db->record['id'];
 $db->RunQuery("DELETE FROM gnujiko_groups WHERE id='$gid'");
 $db->RunQuery("DELETE FROM gnujiko_usergroups WHERE gid='$gid'");
 $db->Close();

 return array("message"=>"Group $group has been removed", "outarr"=>array("gid"=>$gid,"name"=>$group));
}

