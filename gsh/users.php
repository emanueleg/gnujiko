<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 02-08-2012
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: List of users
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function shell_users($args, $sessid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"PERMISSION_DENIED");
 
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--orderby' : {$orderBy = $args[$c+1]; $c++;} break;
   case '-asc' : $orderByAsc = true; break;
   case '-desc' : $orderByDesc = true; break;
  }
 
 $output = "";
 $outArr = array();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_users WHERE 1".($orderBy ? " ORDER BY $orderBy".($orderByDesc ? " DESC" : " ASC") : ""));
 while($db->Read())
 {
  $output.= $db->record['username']."\n";
  $outArr[] = array('id'=>$db->record['id'],'name'=>$db->record['username'],'fullname'=>$db->record['fullname']);
 }
 $db->Close();

 return array("message"=>$output, "outarr"=>$outArr);

}

