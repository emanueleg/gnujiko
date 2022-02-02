<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-10-2016
 #PACKAGE: cashresources
 #DESCRIPTION: Manage cash resources.
 #VERSION: 2.1beta
 #CHANGELOG: 24-10-2016 : MySQLi integration.
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_cashresources($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'new' : case 'add' : return cashresources_new($args, $sessid, $shellid); break;
  case 'edit' : return cashresources_edit($args, $sessid, $shellid); break;
  case 'delete' : return cashresources_delete($args, $sessid, $shellid); break;
  case 'list' : return cashresources_list($args, $sessid, $shellid); break;
  case 'info' : return cashresources_info($args, $sessid, $shellid); break;
  default : return cashresources_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function cashresources_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function cashresources_new($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-type' : {$type=$args[$c+1]; $c++;} break;
   case '-current-balance' : case '-balance' : {$currentBalance=$args[$c+1]; $c++;} break;
  }

 if(!$name) return array('message'=>"You must specify a valid name","error"=>"INVALID_NAME");
 
 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO cashresources(name,res_type,current_balance) VALUES('".$db->Purify($name)."','".$type."','".$currentBalance."')");
 $outArr = array('id'=>$db->GetInsertId(),'name'=>$name,'current_balance'=>$currentBalance);
 $db->Close();
 $out = "done!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function cashresources_edit($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-type' : {$type=$args[$c+1]; $c++;} break;
   case '-current-balance' : case '-balance' : {$currentBalance=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify a valid resource id","error"=>"INVALID_RESOURCE");

 $db = new AlpaDatabase();
 $q = "";
 if($name)
  $q.= ",name='".$db->Purify($name)."'";
 if(isset($type))
  $q.= ",res_type='".$type."'";
 if(isset($currentBalance))
  $q.= ",current_balance='".$currentBalance."'";

 $db->RunQuery("UPDATE cashresources SET ".ltrim($q,',')." WHERE id='$id'");
 $db->Close();

 $out = "Resource has been updated!";
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function cashresources_delete($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify a valid resource id","error"=>"INVALID_RESOURCE");

 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM cashresources WHERE id='$id'");
 $db->Close();

 $out.= "Resource #".$id." has been removed.";
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function cashresources_list($args, $sessid, $shellid)
{
 $orderBy = "id ASC";
 $outArr = array();
 $out = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-verbose' : $verbose=true; break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM cashresources WHERE 1 ORDER BY ".$orderBy);
 while($db->Read())
 {
  $a = array('id'=>$db->record['id'],'name'=>$db->record['name'],'type'=>$db->record['res_type'],'current_balance'=>$db->record['current_balance']);
  $outArr[] = $a;
  if($verbose)
   $out.= "#".$a['id']." [".$a['type']."] - ".$a['name']."\n";
 }
 $db->Close();

 $out.= "\n".count($outArr)." resources found.\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function cashresources_info($args, $sessid, $shellid)
{
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify a valid resource id","error"=>"INVALID_RESOURCE");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM cashresources WHERE id='$id'");
 if(!$db->Read())
  return array("message"=>"Resource #".$id." does not exists.","error"=>"RESOURCE_DOES_NOT_EXISTS");

 $outArr = array('id'=>$db->record['id'],'name'=>$db->record['name'],'type'=>$db->record['res_type'],'current_balance'=>$db->record['current_balance']);
 $db->Close();

 return array("message"=>"Done!","outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

