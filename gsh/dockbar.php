<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-10-2016
 #PACKAGE: system-config-gui
 #DESCRIPTION: Default Gnujiko Dockbar Menu
 #VERSION: 2.2beta
 #CHANGELOG: 24-10-2016 : MySQLi integration.
			 10-01-2012 : Serialize function added.
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_dockbar($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'insert' : return dockbar_insert($args, $sessid, $shellid); break;
  case 'edit' : return dockbar_edit($args, $sessid, $shellid); break;
  case 'delete' : return dockbar_delete($args, $sessid, $shellid); break;
  case 'list' : return dockbar_list($args, $sessid, $shellid); break;
  case 'serialize' : return dockbar_serialize($args, $sessid, $shellid); break;
  default : return dockbar_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function dockbar_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function dockbar_insert($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-loader' : {$loader=$args[$c+1]; $c++;} break;
   case '-ordering' : {$ordering=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
  }

 if(!$name) return array('message'=>"You must specify a valid name","error"=>"INVALID_NAME");
 if(!$loader) return array('message'=>"You must specify a valid loader file","error"=>"INVALID_LOADER_FILE");
 
 if(!$group && !$perms) // se non si specifica alcun gruppo, automaticamente imposta i permessi in lettura a tutti //
  $perms = 444; 

 $uid = $sessInfo['uid'];
 $gid = $group ? _getGID($group) : $sessInfo['gid'];
 if($perms)
 {
  $mod = new GMOD($perms);
  $mod = $mod->MOD;
 }
 else
  $mod = 440;


 $db = new AlpaDatabase();
 if(!$ordering)
 {
  $db->RunQuery("SELECT ordering FROM gnujiko_dockbar_menu WHERE 1 ORDER BY ordering DESC LIMIT 1");
  if($db->Read())
   $ordering = $db->record['ordering']+1;
  else
  $ordering = 1;
 }
 $db->RunQuery("INSERT INTO gnujiko_dockbar_menu(uid,gid,_mod,name,file_loader,ordering,published) VALUES('"
	.$uid."','$gid','$mod','$name','$loader','$ordering','1')");
 $id = $db->GetInsertId();
 $db->Close();
 $outArr = array('id'=>$id,'name'=>$name,'loader'=>$loader,'ordering'=>$ordering);
 $out = "done!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dockbar_edit($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-loader' : {$loader=$args[$c+1]; $c++;} break;
   case '-ordering' : {$ordering=$args[$c+1]; $c++;} break;
   case '-published' : {$published=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify a valid menu item id","error"=>"INVALID_ITEM");

 $q = "";
 if($name)
  $q.= ",name='$name'";
 if($loader)
  $q.= ",file_loader='$loader'";
 if($ordering)
  $q.= ",ordering='$ordering'";
 if(isset($published))
  $q.= ",published='$published'";

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE gnujiko_dockbar_menu SET ".ltrim($q,",")." WHERE id='$id'");
 $db->Close();
 $out = "Item has been updated!";
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dockbar_delete($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("SELECT * FROM gnujiko_dockbar_menu WHERE id='$id'");
 else if($name)
  $db->RunQuery("SELECT * FROM gnujiko_dockbar_menu WHERE name='$name'");
 else
  return array('message'=>"You must specify item id. (with -id ITEM_ID || -name ITEM_NAME)","error"=>"INVALID_ITEM");
 if(!$db->Read())
  return array("message"=>"Item ".($id ? "#$id" : $name)." does not exists", "error"=>"ITEM_DOES_NOT_EXISTS");
 $id = $db->record['id'];
 $db->RunQuery("DELETE FROM gnujiko_dockbar_menu WHERE id='$id'");
 $db->Close();
 $out.= "Item ".($id ? "#$id" : $name)." has been removed from dockbar.";
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dockbar_list($args, $sessid, $shellid)
{
 $orderBy = "ordering ASC";
 $includeUnpublished=false;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--include-unpublished' : $includeUnpublished=true; break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
  }

 $out = "";
 $outArr = array();

 $m = new GMOD();
 $uQry = $m->userQuery($sessid);

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_dockbar_menu WHERE ($uQry)".(!$includeUnpublished ? " AND published='1'" : "")." ORDER BY $orderBy");
 while($db->Read())
 {
  $outArr[] = array('id'=>$db->record['id'],'name'=>$db->record['name'],'loader'=>$db->record['file_loader'],'published'=>$db->record['published']);
  $out.= "#".$db->record['id']." - ".$db->record['name']."\n";
 }
 $db->Close();
 $out.= "\n".count($outArr)." dockbar items found.\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dockbar_serialize($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   default: $serialize=$args[$c]; break;
  }
 
 $list = explode(",",$serialize);

 $db = new AlpaDatabase();
 for($c=0; $c < count($list); $c++)
  $db->RunQuery("UPDATE gnujiko_dockbar_menu SET ordering='".$c."' WHERE id='".$list[$c]."'");
 $db->Close();
 return array("message"=>"Done!");
}
//-------------------------------------------------------------------------------------------------------------------//

