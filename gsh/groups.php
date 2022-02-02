<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2010 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 19-01-2010
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: List of groups
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function shell_groups($args, $sessid)
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
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
  }
 
 $output = "";
 $outArr = array();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_groups WHERE 1".($orderBy ? " ORDER BY $orderBy".($orderByDesc ? " DESC" : " ASC") : "").($limit ? " LIMIT ".$limit : ""));
 while($db->Read())
 {
  $output.= $db->record['name']."\n";
  $outArr[] = array('id'=>$db->record['id'],'name'=>$db->record['name']);
 }
 $db->Close();

 return array("message"=>$output, "outarr"=>$outArr);

}

