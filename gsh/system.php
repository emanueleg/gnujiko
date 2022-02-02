<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 25-07-2013
 #PACKAGE: gnujiko-base
 #DESCRIPTION: System Utility
 #VERSION: 2.8beta
 #CHANGELOG: 25-07-2013 : Aggiunto funzione system checkup.
			 11-04-2013 : Sistemato i permessi ai files.
			 25-03-2013 : Aggiunto funzioni backup e restore.
			 15-01-2013 : Bug fix.
			 13-01-2013 : Completate funzioni gnujiko8import paymentmodes & rubrica
			 13-12-2012 : Aggiunta funzione shell-log.
			 10-01-2012 : Functions app-serialize and edit-app added.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_system($args, $sessid, $shellid=0)
{
 $output = "";
 $outArr = array();

 if(count($args) == 0)
  return system_invalidArguments();

 switch($args[0])
 {
  // ACTION //
  case 'register-app' : return system_registerApp($args, $sessid, $shellid); break;
  case 'edit-app' : return system_editApp($args, $sessid, $shellid); break;
  case 'unregister-app' : return system_unregisterApp($args, $sessid, $shellid); break;
  case 'app-list' : return system_appList($args, $sessid, $shellid); break;
  case 'app-serialize' : return system_appSerialize($args, $sessid, $shellid); break;
  // CONFIGURATION //
  case 'cfg-sec-add' : return system_cfgSectionAdd($args, $sessid, $shellid); break;
  case 'cfg-sec-edit' : return system_cfgSectionEdit($args, $sessid, $shellid); break;
  case 'cfg-sec-del' : return system_cfgSectionDelete($args, $sessid, $shellid); break;
  case 'cfg-sec-list' : return system_cfgSectionList($args, $sessid, $shellid); break;
  case 'cfg-add-element' : return system_cfgAddElement($args, $sessid, $shellid); break;
  case 'cfg-edit-element' : return system_cfgEditElement($args, $sessid, $shellid); break;
  case 'cfg-delete-element' : return system_cfgDeleteElement($args, $sessid, $shellid); break;
  case 'cfg-elements' : case 'cfg-element-list' : return system_cfgElementList($args, $sessid, $shellid); break;
  // IMPORT DATA FROM OLD GNUJIKO VERSION 8.01 //
  case 'gnujiko8import' : return system_gnujiko8import($args, $sessid, $shellid); break;
  // BACKUP AND RESTORE //
  case 'backup' : return system_backup($args, $sessid, $shellid); break;
  case 'restore' : return system_restore($args, $sessid, $shellid); break;

  case 'session-info' : return system_sessionInfo($args, $sessid, $shellid); break;
  case 'shell-log' : return system_shellLog($args, $sessid, $shellid); break;
  case 'checkup' : case 'check' : case 'check-up' : return system_checkUp($args, $sessid, $shellid); break;
 
  default : return system_invalidArguments(); break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function system_invalidArguments()
{
 return array("message"=>"Invalid arguments.", "error"=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function system_registerApp($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $published=1;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-desc' : {$desc=$args[$c+1]; $c++;} break;
   case '-url' : {$url=$args[$c+1]; $c++;} break;
   case '-widget-file' : {$widgetFile=$args[$c+1]; $c++;} break;
   case '-widget-params' : {$widgetParams=$args[$c+1]; $c++;} break;
   case '-icon' : {$icon=$args[$c+1]; $c++;} break;
   case '-large-icon' : {$largeIcon=$args[$c+1]; $c++;} break;
   case '-ordering' : {$ordering=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
   case '-published' : {$published=$args[$c+1]; $c++;} break;
  }

 if(!$name) return array('message'=>"You must specify a valid name","error"=>"INVALID_NAME");
 
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
  $db->RunQuery("SELECT ordering FROM gnujiko_applications WHERE 1 ORDER BY ordering DESC LIMIT 1");
  if($db->Read())
   $ordering = $db->record['ordering']+1;
  else
  $ordering = 1;
 }
 $db->RunQuery("INSERT INTO gnujiko_applications(uid,gid,_mod,name,description,icon,large_icon,url,widget_file,widget_params,ordering,published) VALUES('"
	.$uid."','$gid','$mod','$name','$desc','$icon','$largeIcon','$url','$widgetFile','$widgetParams','$ordering','$published')");
 $id = mysql_insert_id();
 $db->Close();
 $outArr = array('id'=>$id,'name'=>$name,'desc'=>$desc,'url'=>$url,'icon'=>$icon,'largeicon'=>$largeIcon,'ordering'=>$ordering,'widget_file'=>$widgetFile,
	'widget_params'=>$widgetParams,'published'=>$published);
 $out = "done!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_editApp($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-desc' : {$desc=$args[$c+1]; $c++;} break;
   case '-url' : {$url=$args[$c+1]; $c++;} break;
   case '-widget-file' : {$widgetFile=$args[$c+1]; $c++;} break;
   case '-widget-params' : {$widgetParams=$args[$c+1]; $c++;} break;
   case '-icon' : {$icon=$args[$c+1]; $c++;} break;
   case '-large-icon' : {$largeIcon=$args[$c+1]; $c++;} break;
   case '-ordering' : {$ordering=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
   case '-published' : {$published=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify a valid application ID","error"=>"INVALID_APP_ID");

 $db = new AlpaDatabase();
 $q = "";
 if($name)
  $q.= ",name='".$db->Purify($name)."'";
 if($desc)
  $q.= ",description='".$db->Purify($desc)."'";
 if(isset($url))
  $q.= ",url='".$url."'";
 if(isset($widgetFile))
  $q.= ",widget_file='".$widgetFile."'";
 if(isset($widgetParams))
  $q.= ",widget_params='".$widgetParams."'";
 if(isset($icon))
  $q.= ",icon='".$icon."'";
 if(isset($largeIcon))
  $q.= ",large_icon='".$largeIcon."'";
 if(isset($published))
  $q.= ",published='$published'";

 if($group)
  $q.= ",gid='"._getGID($group)."'";
 if($perms)
 {
  $mod = new GMOD($perms);
  $q.= ",_mod='".$mod->MOD."'";
 }
 $db->Close();
 
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE gnujiko_applications SET ".ltrim($q,",")." WHERE id='$id'");
 $db->Close();
 $out = "Item has been updated!";

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_unregisterApp($args, $sessid, $shellid)
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
  $db->RunQuery("SELECT * FROM gnujiko_applications WHERE id='$id'");
 else if($name)
  $db->RunQuery("SELECT * FROM gnujiko_applications WHERE name='$name'");
 else
  return array('message'=>"You must specify item id. (with -id ITEM_ID || -name ITEM_NAME)","error"=>"INVALID_ITEM");
 if(!$db->Read())
  return array("message"=>"Item ".($id ? "#$id" : $name)." does not exists", "error"=>"ITEM_DOES_NOT_EXISTS");
 $id = $db->record['id'];
 $db->RunQuery("DELETE FROM gnujiko_applications WHERE id='$id'");
 $db->Close();
 $out.= "Item ".($id ? "#$id" : $name)." has been removed from applications.";
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_appList($args, $sessid, $shellid)
{
 $orderBy = "ordering ASC";
 $sessInfo = sessionInfo($sessid);
 $includeUnpublished = false;
 $verbose=false;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--include-unpublished' : $includeUnpublished=true; break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
  }

 $out = "";
 $outArr = array();

 $mod = new GMOD();
 $uQry = $mod->userQuery($sessid);

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_applications WHERE ($uQry)".(!$includeUnpublished ? " AND published='1'" : "")." ORDER BY $orderBy");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id'],'name'=>$db->record['name'],'desc'=>$db->record['description'],'url'=>$db->record['url'],
	'widget_file'=>$db->record['widget_file'], 'widget_params'=>$db->record['widget_params'], 'icon'=>$db->record['icon'],
	'largeicon'=>$db->record['large_icon'],'published'=>$db->record['published']);
  $mod = new GMOD();
  $mod->set($db->record['_mod'],$db->record['uid'],$db->record['gid']);
  $a['modinfo'] = $mod->toArray($sessInfo['uid']);

  if($verbose)
  {
   $out.= "Application info: ".$db->record['name']."\n";
   $out.= "ID: ".$db->record['id']."\n";
   $out.= "Name: ".$db->record['name']."\n";
   $out.= "Description: ".$db->record['description']."\n";
   $out.= "URL: ".$db->record['url']."\n";
   if($db->record['widget_file'])
    $out.= "Widget file: ".$db->record['widget_file']."\n";
   $out.= "Owner: "._getUserName($a['modinfo']['uid'])."\n";
   $out.= "Group: ".($db->record['gid'] ? _getGroupName($a['modinfo']['gid']) : "")."\n";
   $out.= "Permissions: ".$mod->toString()."\n";
   if($db->record['icon'])
   {
    $out.= "Icon: ";
	if(file_exists($_ABSOLUTE_URL.$db->record['icon']))
	 $out.= "<img src='".$_ABSOLUTE_URL.$db->record['icon']."' width='16'/>\n";
	else
	 $out.= "<i>unavailable</i> <span style='color:red;'>File ".$db->record['icon']." does not exists!</span>\n";
   }
   $out.= "Published: ".($db->record['published'] ? "yes" : "no")."\n\n";
  }
  else
   $out.= "#".$db->record['id']." - ".$db->record['name']."\n";
  $outArr[] = $a;
 }
 $db->Close();
 
 $out.= "\n".count($outArr)." applications found.\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_appSerialize($args, $sessid, $shellid)
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
  $db->RunQuery("UPDATE gnujiko_applications SET ordering='".$c."' WHERE id='".$list[$c]."'");
 $db->Close();
 return array("message"=>"Done!");
}
//-------------------------------------------------------------------------------------------------------------------//
//--CONFIGURATION SECTIONS-------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function system_cfgSectionAdd($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-tag' : {$tag=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
  }

 if(!$name) return array('message'=>"You must specify a valid name","error"=>"INVALID_NAME");
 
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
 $db->RunQuery("INSERT INTO gnujiko_config_sections(uid,gid,_mod,name,tag) VALUES('"
	.$uid."','$gid','$mod','$name','$tag')");
 $id = mysql_insert_id();
 $db->Close();
 $outArr = array('id'=>$id,'name'=>$name,'tag'=>$tag);
 $out = "done!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_cfgSectionEdit($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-tag' : {$tag=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify section. (with -id SECTION_ID)","error"=>"INVALID_SECTION_ID");

 $db = new AlpaDatabase();

 $q = "";
 if($name)
  $q.= ",name='".$db->Purify($name)."'";
 if($tag)
  $q.= ",tag='$tag'";
 if($group)
  $q.= ",gid='"._getGID($group)."'";
 if($perms)
  $q.= ",_mod='$perms'";

 $db->RunQuery("UPDATE gnujiko_config_sections SET ".ltrim($q,",")." WHERE id='$id'");
 $db->Close();
 $outArr = array('id'=>$id,'name'=>$name,'tag'=>$tag);
 $out = "Section has been updated!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_cfgSectionDelete($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
  }
 if(!$id) return array('message'=>"You must specify section. (with -id SECTION_ID)","error"=>"INVALID_SECTION_ID");
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_config_sections WHERE id='$id'");
 $db->Close();
 $out = "done!";
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_cfgSectionList($args, $sessid, $shellid)
{
 $orderBy = "id ASC";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
  }

 $out = "";
 $outArr = array();

 $m = new GMOD();
 $uQry = $m->userQuery($sessid);

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_config_sections WHERE ($uQry) ORDER BY $orderBy");
 while($db->Read())
 {
  $outArr[] = array('id'=>$db->record['id'],'name'=>$db->record['name'],'tag'=>$db->record['tag']);
  $out.= "#".$db->record['id']." - ".$db->record['name']." <i>(".$db->record['tag'].")</i>\n";
 }
 $db->Close();
 $out.= "\n".count($outArr)." configuration sections found.\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
//--CONFIGURATION ITEMS----------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function system_cfgAddElement($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-sec' : {$secTag=$args[$c+1]; $c++;} break;
   case '-icon' : {$icon=$args[$c+1]; $c++;} break;
   case '-file' : {$file=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
  }

 if(!$name) return array('message'=>"You must specify a valid name","error"=>"INVALID_NAME");
 
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
 $db->RunQuery("INSERT INTO gnujiko_config_menu(uid,gid,_mod,name,sec_tag,icon,cfg_file) VALUES('"
	.$uid."','$gid','$mod','".$db->Purify($name)."','$secTag','$icon','$file')");
 $id = mysql_insert_id();
 $db->Close();
 $outArr = array('id'=>$id,'name'=>$name,'sectag'=>$secTag,'icon'=>$icon,'file'=>$file);
 $out = "done!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_cfgEditElement($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-sectag' : {$secTag=$args[$c+1]; $c++;} break;
   case '-icon' : {$icon=$args[$c+1]; $c++;} break;
   case '-file' : {$file=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify element. (with -id ELEMENT_ID)","error"=>"INVALID_ELEMENT_ID");

 $db = new AlpaDatabase();

 $q = "";
 if($name)
  $q.= ",name='".$db->Purify($name)."'";
 if($secTag)
  $q.= ",sec_tag='$secTag'";
 if($icon)
  $q.= ",icon='$icon'";
 if($file)
  $q.= ",file='$file'";
 if($group)
  $q.= ",gid='"._getGID($group)."'";
 if($perms)
  $q.= ",_mod='$perms'";

 $db->RunQuery("UPDATE gnujiko_config_menu SET ".ltrim($q,",")." WHERE id='$id'");
 $db->Close();
 $outArr = array('id'=>$id,'name'=>$name,'sectag'=>$secTag,'icon'=>$icon,'file'=>$file);
 $out = "Element has been updated!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_cfgDeleteElement($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
  }
 if(!$id) return array('message'=>"You must specify element. (with -id ELEMENT_ID)","error"=>"INVALID_ELEMENT_ID");
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM gnujiko_config_menu WHERE id='$id'");
 $db->Close();
 $out = "done!";
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_cfgElementList($args, $sessid, $shellid)
{
 $orderBy = "id ASC";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-sec' : {$sec=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
  }

 $out = "";
 $outArr = array();

 $m = new GMOD();
 $uQry = $m->userQuery($sessid);

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_config_menu WHERE ($uQry)".($sec ? " AND sec_tag='$sec'" : "")." ORDER BY $orderBy");
 while($db->Read())
 {
  $outArr[] = array('id'=>$db->record['id'],'name'=>$db->record['name'],'sectag'=>$db->record['sec_tag'],
	'icon'=>$db->record['icon'],'file'=>$db->record['cfg_file']);
  $out.= "#".$db->record['id']." - ".$db->record['name']." <i>(".$db->record['sec_tag'].")</i>\n";
 }
 $db->Close();
 $out.= "\n".count($outArr)." configuration elements found.\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
//--OTHER------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function system_sessionInfo($args, $sessid, $shellid)
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_session WHERE session_id='$sessid'");
 if(!$db->Read())
  return array("message"=>"Invalid session id: $sessid", "error"=>"SESSID_DOES_NOT_EXISTS");
 $out = "Username: ".$db->record['uname']."\n";
 $out.= "Login time: ".date('d/m/Y H:i',$db->record['login_time'])."\n";
 $out.= "Session ID: ".$sessid."\n";
 $out.= "Shell ID: ".$shellid."\n";
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_shellLog($args, $sessid, $shellid)
{
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'on' : case 'enable' : {
	 setcookie("GNUJIKO-ENABLE-SHELL-LOG","1",strtotime("+1 week"));
	 return array('message'=>"Shell log has been enabled!");
	} break;

   case 'off' : case 'disable' : {
	 setcookie("GNUJIKO-ENABLE-SHELL-LOG",false);
	 return array('message'=>"Shell log has been disabled!");
	} break;

   case 'clear' : return system_shellLog_clear(); break;
  }
 
 return array('message'=>"Invalid action!", "error"=>"INVALID_ACTION");
}
//-------------------------------------------------------------------------------------------------------------------//
function system_shellLog_clear()
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_DEFAULT_FILE_PERMS;
 $fileName = $_BASE_PATH."tmp/shell-log.xml";

 $f = @fopen($fileName,"r+");

 if($f !== false) 
 {
  ftruncate($f, 0);
  fclose($f);
  return array('message'=>"Done!");
 }

 if($f)
 {
  if(!@fwrite($f,""))
  {
   // Try with FTP //
   if($_FTP_USERNAME)
   {
    $conn = @ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
    if($conn && @ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
    {
     if($_FTP_PATH)
     {
	  if(!@ftp_chdir($conn, $_FTP_PATH))
	   return array('message'=>"Unable to chdir by FTP","error"=>"FTP_CHDIR_FAILED");
     }
	 $fp = tmpfile();
	 if(!ftp_fput($conn, $fileName, $fp, FTP_BINARY))
	  return array('message'=>"Unable to chdir by FTP","error"=>"FTP_CHDIR_FAILED");
	 $f = @fopen($fileName,"w");
	 if(!@fwrite($f,""))
	  return array('message'=>"Unable to write data into file by FTP","error"=>"FTP_FWRITE_FAILED");
	}
	else
	 return array('message'=>"FTP connection failed!","error"=>"FTP_CONNECTION_FAILED");
   }	
   else
	return array('message'=>"FTP is not yet configured.","error"=>"FTP_DISABLED");;
  }
  @fclose($f);
  return array('message'=>"Done! Shell-log has been cleaned!");
 }
 else
 {
   // Try with FTP //
   if($_FTP_USERNAME)
   {
    $conn = @ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
    if($conn && @ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
    {
     if($_FTP_PATH)
     {
	  if(!@ftp_chdir($conn, $_FTP_PATH))
	   return array('message'=>"Unable to chdir by FTP","error"=>"FTP_CHDIR_FAILED");
     }
	 $fp = tmpfile();
	 if(!ftp_fput($conn, $fileName, $fp, FTP_BINARY))
	  return array('message'=>"Unable to chdir by FTP","error"=>"FTP_CHDIR_FAILED");
	 @ftp_chmod($conn, $_DEFAULT_FILE_PERMS, $fileName);
	 $f = @fopen($fileName,"w");
	 if(!@fwrite($f,""))
	  return array('message'=>"Unable to write data into file by FTP","error"=>"FTP_FWRITE_FAILED");
	}
	else
	 return array('message'=>"FTP connection failed!","error"=>"FTP_CONNECTION_FAILED");
   }	
   else
	return array('message'=>"FTP is not yet configured.2","error"=>"FTP_DISABLED");;
 }
 return array('message'=>"Done! Shell-log has been cleaned!");
}
//-------------------------------------------------------------------------------------------------------------------//
function system_gnujiko8import($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'paymentmodes' : return system_gnujiko8import_paymentmodes($args, $sessid, $shellid); break;
   case 'rubrica' : return system_gnujiko8import_rubrica($args, $sessid, $shellid); break;
  }

}
//-------------------------------------------------------------------------------------------------------------------//
function system_gnujiko8import_paymentmodes($args, $sessid, $shellid)
{
 for($c=2; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-host' : {$host=$args[$c+1]; $c++;} break;
   case '-user' : case '-login' : {$user=$args[$c+1]; $c++;} break;
   case '-passw' : case '-pass' : case '-password' : {$passw=$args[$c+1]; $c++;} break;
   case '-db' : {$dbname=$args[$c+1]; $c++;} break;


   case '--clear' : case '--clean' : case '--empty' : case '--overwrite' : $truncateTable=true; break;
  }

 if(!$dbname)
  return array('message'=>"You must specify database name. (with: -db DATABASE_NAME)", "error"=>"INVALID_DB_NAME");

 if($truncateTable)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("TRUNCATE TABLE `payment_modes`");
  $db->Close();
 }

 $out = "Connecting to database ".$dbname."...";
 $db = new AlpaDatabase($host, $user, $passw, $dbname);
 if(!$db)
  return array('message'=> $out.=" failed!\nUnable to connect with database $dbname.", "error"=>"DATABASE_CONNECT_FAIELED");
 $out.= "done\n";

 // get payment mode list //
 $items = array();
 $db->RunQuery("SELECT * FROM payment_mode WHERE 1 ORDER BY name ASC");
 while($db->Read())
 {
  $items[] = $db->record;
 }
 $db->Close();

 $interface = array("name"=>"progressbar","steps"=>count($items));
 gshPreOutput($shellid,"Estimated elements to import: ".count($items), "ESTIMATION", "", "PASSTHRU", $interface);

 for($c=0; $c < count($items); $c++)
 {
  $item = $items[$c];
  $type = "";

  gshPreOutput($shellid, "Import: <i>".$item['name']."</i>","PROGRESS", "");
  // DETECT PAYMENT TYPE //
  if((stripos($item['name'],"RI.BA") !== false) || (stripos($item['name'],"R.B.") !== false) || (stripos($item['name'],"RIBA") !== false))
   $type = "RB";
  else if((stripos($item['name'],"B.B") !== false) || (stripos($item['name'],"BONIFICO") !== false) || (stripos($item['name'],"BANCA") !== false))
   $type = "BB";
  else if((stripos($item['name'],"R.D") !== false) || (stripos($item['name'],"RIMESSA") !== false))
   $type = "RD";
  // EOF - DETECT PAYMENT TYPE //
  $ret = GShell("paymentmodes new -name `".$item['name']."` -type `".$type."` -perc1 '".$item['x_perc1']."' -perc2 '"
	.$item['x_perc2']."' -perc3 '".$item['x_perc3']."' -perc4 '".$item['x_perc4']."' -perc5 '".$item['x_perc5']."' -perc6 '"
	.$item['x_perc6']."' -daynum1 '".$item['x_daynum1']."' -daynum2 '".$item['x_daynum2']."' -daynum3 '".$item['x_daynum3']."' -daynum4 '"
	.$item['x_daynum4']."' -daynum5 '".$item['x_daynum5']."' -daynum6 '".$item['x_daynum6']."' -daydoc1 '".$item['x_daydoc1']."' -daydoc2 '"
	.$item['x_daydoc2']."' -daydoc3 '".$item['x_daydoc3']."' -daydoc4 '".$item['x_daydoc4']."' -daydoc5 '".$item['x_daydoc5']."' -daydoc6 '"
	.$item['x_daydoc6']."'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
 }

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_gnujiko8import_rubrica($args, $sessid, $shellid)
{
 for($c=2; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-host' : {$host=$args[$c+1]; $c++;} break;
   case '-user' : case '-login' : {$user=$args[$c+1]; $c++;} break;
   case '-passw' : case '-pass' : case '-password' : {$passw=$args[$c+1]; $c++;} break;
   case '-db' : {$dbname=$args[$c+1]; $c++;} break;

   /* OPTIONS */
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;

   case '--clear' : case '--clean' : case '--empty' : case '--overwrite' : $truncateTable=true; break;
  }

 if(!$dbname)
  return array('message'=>"You must specify database name. (with: -db DATABASE_NAME)", "error"=>"INVALID_DB_NAME");

 if($truncateTable)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("TRUNCATE TABLE `dynarc_rubrica_items`");
  $db->RunQuery("TRUNCATE TABLE `dynarc_rubrica_contacts`");
  $db->RunQuery("TRUNCATE TABLE `dynarc_rubrica_banks`");
  $db->Close();
 }

 
 $out = "Connecting to database ".$dbname."...";
 $db = new AlpaDatabase($host, $user, $passw, $dbname);
 if(!$db)
  return array('message'=> $out.=" failed!\nUnable to connect with database $dbname.", "error"=>"DATABASE_CONNECT_FAIELED");
 $out.= "done\n";
 $catByID = array();
 $catByTag = array();

 // get categories list //
 $db->RunQuery("SELECT * FROM dynarc_rubrica_categories WHERE trash='0'");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id'], 'parent_id'=>$db->record['parent_id'], 'tag'=>$db->record['tag'], 'name'=>$db->record['name'], 'description'=>$db->record['description'], 'ordering'=>$db->record['ordering'], 'ctime'=>$db->record['ctime'], 'published'=>$db->record['published']);
  $catByID[$db->record['id']] = $a;
  $catByTag[$db->record['tag']] = $a;
 }
 $db->Close();

 // get paymentmodes list //
 $paymentMode = array();
 $db = new AlpaDatabase($host, $user, $passw, $dbname);
 $db->RunQuery("SELECT * FROM payment_mode WHERE 1 ORDER BY id ASC");
 while($db->Read())
  $paymentMode[$db->record['id']] = $db->record;
 $db->Close();

 // IMPORT CUSTOMERS //
 $items = array();
 $db = new AlpaDatabase($host, $user, $passw, $dbname);
 $db->RunQuery("SELECT * FROM dynarc_rubrica_items WHERE cat_id='".$catByTag['customers']['id']."' AND trash='0' ORDER BY name ASC".($limit ? " LIMIT $limit" : ""));
 while($db->Read())
 {
  $a = $db->record;
  // get generality //
  $db2 = new AlpaDatabase($host, $user, $passw, $dbname);
  $db2->RunQuery("SELECT * FROM dynarc_rubrica_generality WHERE item_id='".$a['id']."' LIMIT 1");
  $db2->Read();
  $a['generality'] = array('iscompany'=>$db2->record['iscompany'], 'taxcode'=>$db2->record['taxcode'], 'vatnumber'=>$db2->record['vatnumber'], 'paymentmode'=>$db2->record['paymentmode']);
  $db2->Close();

  // get contacts //
  $a['contacts'] = array();
  $db2 = new AlpaDatabase($host, $user, $passw, $dbname);
  $db2->RunQuery("SELECT * FROM dynarc_rubrica_contacts WHERE item_id='".$a['id']."' ORDER BY isdefault DESC, id ASC");
  while($db2->Read())
  {
   $a['contacts'][] = $db2->record;
  }
  $db2->Close();

  // get banks //
  $a['banks'] = array();
  $db2 = new AlpaDatabase($host, $user, $passw, $dbname);
  $db2->RunQuery("SELECT * FROM dynarc_rubrica_banks WHERE item_id='".$a['id']."' ORDER BY isdefault DESC, id ASC");
  while($db2->Read())
  {
   $a['banks'][] = $db2->record;
  }
  $db2->Close();

  $items[] = $a;
 }
 $db->Close();

 $interface = array("name"=>"progressbar","steps"=>count($items));
 gshPreOutput($shellid,"Estimated elements to import: ".count($items), "ESTIMATION", "", "PASSTHRU", $interface);


 /* Update paymentMode new ids */
 $db = new AlpaDatabase();
 while(list($k,$v) = each($paymentMode))
 {
  $db->RunQuery("SELECT id FROM payment_modes WHERE name='".$paymentMode[$k]['name']."' LIMIT 1");
  $db->Read();
  $paymentMode[$k]['id'] = $db->record['id'];
 }
 $db->Close();


 for($c=0; $c < count($items); $c++)
 {
  $item = $items[$c];
  gshPreOutput($shellid, "Import: <i>".$item['name']."</i>","PROGRESS", "");

  $contactsQry = "";
  if(count($item['contacts']))
  {
   $cc = $item['contacts'][0];
   $contactsQry =  " -extset `contacts.name='''".$item['name']."''',label='''"
	.$cc['label']."''',address='''".$cc['address']."''',city='''".$cc['city']."''',zipcode='".$cc['zipcode']."',province='"
	.$cc['province']."',countrycode='".$cc['countrycode']."',phone='".$cc['phone']."',phone2='".$cc['phone2']."',fax='"
	.$cc['fax']."',cell='".$cc['cell']."',email='".$cc['email']."',skype='".$cc['skype']."'`";
  }

  $ret = GShell("dynarc new-item -ap `rubrica` -ct customers -name `".$item['name']."` -group rubrica -perms 664 -set `iscompany='".$item['generality']['iscompany']."',taxcode='".$item['generality']['taxcode']."',vatnumber='".$item['generality']['vatnumber']."',paymentmode='".$paymentMode[$item['generality']['paymentmode']]['id']."',code_str='".$item['code']."'`".$contactsQry,$sessid,$shellid);
  if($ret['error'])
   return $ret;

  $newItem = $ret['outarr'];
  /* IMPORT CONTACTS */
  if(count($item['contacts']))
  {
   for($i=1; $i < count($item['contacts']); $i++)
   {
    $cc = $item['contacts'][$i];
    GShell("dynarc edit-item -ap `rubrica` -id `".$newItem['id']."` -extset `contacts.name='''".$item['name']."''',label='''"
	.$cc['label']."''',address='''".$cc['address']."''',city='''".$cc['city']."''',zipcode='".$cc['zipcode']."',province='"
	.$cc['province']."',countrycode='".$cc['countrycode']."',phone='".$cc['phone']."',phone2='".$cc['phone2']."',fax='"
	.$cc['fax']."',cell='".$cc['cell']."',email='".$cc['email']."',skype='".$cc['skype']."'`",$sessid,$shellid);
   }
  }
  /* IMPORT BANKS */
  if(count($item['banks']))
  {
   for($i=0; $i < count($item['banks']); $i++)
   {
    $bb = $item['banks'][$i];
    GShell("dynarc edit-item -ap `rubrica` -id `".$newItem['id']."` -extset `banks.holder='''".$bb['owner']."''',name='''"
	.$bb['name']."''',abi='".$bb['abi']."',cab='".$bb['cab']."',cin='".$bb['cin']."',cc='".$bb['cc']."',iban='".$bb['iban']."'`",$sessid,$shellid);
   }
  }

 }

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_backup($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_USERS_HOMES, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-db' : $backupDB=true; break;
   case '-dbname' : {$databaseName=$args[$c+1]; $c++;} break;
   case '-home' : $backupHomeDir=true; break;
   case '-share' : $backupShareFiles=true; break;
   case '-all' : $backupAll=true; break;
   /* OPTIONS */
   case '--no-zip' : $noZip=true; break;
   case '--no-remove-temp' : $noRemoveTemp=true; break;
  }

 if(!$backupDB && !$backupHomeDir)
  $backupAll = true;

 /* Creo la cartella dove andrò a copiare tutti i files e le cartelle di Gnujiko */
 $out = "Creating a directory for backup...";
 $ret = GShell("mkdir tmp/backup",$sessid,$shellid);
 if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
 $out.= "done!\n";

 $steps = 0;
 if($backupAll)
  $steps = 3;
 else
 {
  $steps+= $backupDB ? 1 : 0;
  $steps+= $backupHomeDir ? 1 : 0;
  $steps+= $backupShareFiles ? 1 : 0;
 }
 $steps+= !$noZip ? 1 : 0;
 $steps+= !$noRemoveTemp ? 1 : 0;
 


 $interface = array("name"=>"progressbar","steps"=>$steps);
 gshPreOutput($shellid,"Backup Gnujiko. Please wait!", "ESTIMATION", "", "PASSTHRU", $interface);


 if($backupDB || $backupAll)
 {
  gshPreOutput($shellid, "Database backup","PROGRESS", "");
  /* Exporting database */
  $out.= "Exporting database...";
  $db = new AlpaDatabase(null,null,null,$databaseName ? $databaseName : null);

  $bkoptions = array();
  $bkoptions["gnujiko_session"] = "CREATEONLY";

  $ret = $db->Backup("*",$bkoptions,"tmp/backup/database.sql");
  if($ret['error'])
   return $ret;

  $db->Close();
 }

 if($backupHomeDir || $backupAll)
 {
  gshPreOutput($shellid, "Home directory backup","PROGRESS", "");
  $ret = GShell("cp `home/` `tmp/backup/__files/home/`",$sessid,$shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
 }

 if($backupShareFiles || $backupAll)
 {
  gshPreOutput($shellid, "Backup shared files","PROGRESS", "");
  $ret = GShell("cp `share/images/` `tmp/backup/__files/share/images/`",$sessid,$shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
 }

 if(!$noZip)
 {
  /* Zipping all */
  gshPreOutput($shellid, "Compress backup","PROGRESS", "");
  $ret = GShell("zip -i `tmp/backup/` -o `tmp/backup.zip`",$sessid,$shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
 }

 if(!$noRemoveTemp)
 {
  /* Remove temporary directory */
  gshPreOutput($shellid, "Removing temporary files","PROGRESS", "");
  $ret = GShell("rm `tmp/backup/`",$sessid,$shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
 }

 $outArr = array('filename'=>'tmp/backup.zip'); 

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_restore($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_USERS_HOMES, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-db' : $restoreDB=true; break;
   case '-dbname' : {$databaseName=$args[$c+1]; $c++;} break;
   case '-files' : $restoreFiles=true; break;
   case '-all' : $restoreAll=true; break;
   case '-f' : {$fileName=$args[$c+1]; $c++;} break;
  }

 if(!$restoreAll && !$restoreDB && !$restoreFiles)
  $restoreAll = true;

 if(!$fileName)
  return array("message"=>"You must specify the backup file name", "error"=>"INVALID_FILE_NAME");
 if(!file_exists($_BASE_PATH.$fileName))
  return array("message"=>"File ".$fileName." does not exists!","error"=>"FILE_DOES_NOT_EXISTS");

 $steps = 2;
 if($restoreAll)
  $steps = 4;
 else
 {
  $steps+= $restoreDB ? 1 : 0;
  $steps+= $restoreFiles ? 1 : 0;
 }
 

 $interface = array("name"=>"progressbar","steps"=>$steps);
 gshPreOutput($shellid,"Restore Gnujiko. Please wait!", "ESTIMATION", "", "PASSTHRU", $interface);

 /* extract backup file */
 gshPreOutput($shellid, "Extract backup file","PROGRESS", "");
 $out.= "Extract backup file...";
 $ret = GShell("unzip -i `".$fileName."` -o `tmp/backup/`",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 $out.= "done!\n";

 /* PRIMA IMPORTIAMO I DATI E POI ALLA FINE IL DATABASE */
 if($restoreFiles || $restoreAll)
 {
  $out.= "Restore files...";
  gshPreOutput($shellid, "Restore files","PROGRESS", "");
  if(!full_copy($_BASE_PATH."tmp/backup/__files/",$_BASE_PATH))
   return array('message'=>"Unable to copy file from tmp/backup/__files/.","error"=>"FILE_PERMISSION_DENIED");
  $out.= "done!\n";
 }

 /* remove temporary directory */
 $out.= "Remove temporary directory...";
 gshPreOutput($shellid, "Remove temporary directory","PROGRESS", "");
 if(file_exists($_BASE_PATH."tmp/backup/__files/"))
 {
  $ret = GShell("rm `tmp/backup/__files/`",$sessid,$shellid);
  if($ret['error']) 
   return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
 }
 $out.= "done!\n";

 if($restoreDB || $restoreAll)
 {
  if(file_exists($_BASE_PATH."tmp/backup/database.sql"))
  {
   $out.= "Restore database...";
   gshPreOutput($shellid, "Restore database","PROGRESS", "");
   $db = new AlpaDatabase();
   $db->RunQueryFromFile($_BASE_PATH."tmp/backup/database.sql");
   $db->Close();
   $out.= "done!\n";
  }
 }

 /* TODO: continuare con il resto dei test... */

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function system_checkUp($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_USERS_HOMES, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_DEFAULT_FILE_PERMS;
 $sessInfo = sessionInfo($sessid);

 //if($sessInfo['uname'] != "root")
 // return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $outArr = array();
 $checkUpPHP = false;
 $checkUpDatabase = false;
 $checkUpFilePerms = false;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-php' : $checkUpPHP=true; break;
   case '-database' : $checkUpDatabase=true; break;
   case '-fileperms' : $checkUpFilePerms=true; break;

   case '-all' : $checkUpAll=true; break;
  }

 if(!$checkUpPHP && !$checkUpDatabase && !$checkUpFilePerms)
  $checkUpAll = true;

 if($checkUpPHP || $checkUpAll)
 {
  $out.= "Checking for PHP configuration:\n";
  $out.= "PHP version: ".phpversion()."\n";
  $out.= "Display errors: ".ini_get('display_errors')."\n";
  $out.= "Register globals: ".ini_get('register_globals')."\n\n";
 }

 if($checkUpFilePerms || $checkUpAll)
 {
  $out.= "Checking for files and folders permissions.\n";
  $out.= "Default file permissions: ".decoct($_DEFAULT_FILE_PERMS)."\n";
  $out.= "Current user home dir: ";
  if($sessInfo['uname'] == "root")
   $out.= "ok! (root has not home directory)\n";
  else
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT * FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
   $db->Read();
   $_HOME_DIR = $db->record['homedir'];
   if(!$_HOME_DIR)
	$out.= "failed! At the user ".$sessInfo['uname']." has not been assigned any directory\n";
   else
   {
	if(!file_exists($_BASE_PATH.$_USERS_HOMES.$_HOME_DIR))
	 $out.= "failed! The folder ".$_HOME_DIR."/ does not exists into directory ".$_USERS_HOMES.". You must create it manually.\n";
	else if(!is_readable($_BASE_PATH.$_USERS_HOMES.$_HOME_DIR))
	 $out.= "failed! The folder ".$_HOME_DIR."/ exists into directory ".$_USERS_HOMES.", but is not readable!. You must set the correct permissions (readable and writable) to that folder.\n";
	else if(!is_writable($_BASE_PATH.$_USERS_HOMES.$_HOME_DIR))
	{
	 // verifica l'accesso tramite FTP //
	 if($_FTP_SERVER && $_FTP_USERNAME)
	 {
	  $conn = @ftp_connect($_FTP_SERVER);
	  if($conn)
	  {
	   if(@ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
	   {
		if($_FTP_PATH && !@ftp_chdir($conn,$_FTP_PATH))
		 $out.= "failed! The folder ".$_HOME_DIR."/ exists but is not writable. You have the FTP enabled, and I can connect with server, but the FTP PATH: '".$_FTP_PATH."' is wrong! Please verify and insert the correct path into the configuration file.\n";
		else
		{
		 // try to write a temporary file into home folder //
		 $tmpFileName = "systemcheckup-".date('Ymdhis').".txt";
		 GShell("echo `Gnujiko 10.1 - System Check-Up` > `".$tmpFileName."`",$sessid,$shellid);
		 if(!file_exists($_BASE_PATH.$_USERS_HOMES.$_HOME_DIR.$tmpFileName))
		  $out.= "failed! The folder ".$_HOME_DIR."/ exists but is not writable. You have the FTP enabled, and I can connect with server, but that directory has a bad permissions. Please make sure that the variable DEFAULT_FILE_PERMS is set correctly into the configuration file! (for example: '0777', or '0755', or '0666'), and change manually by FTP the permissions of that folder.\n";
		 else
		 {
		  // remove the temporary file //
		  $ret = GShell("rm `".$tmpFileName."`",$sessid,$shellid);
		  if($ret['error'])
		   $out.= "warning! The folder ".$_HOME_DIR."/ exists and I can write to by FTP, but an error occur while I try to remove the test temporary file '".$tmpFileName."' inside that folder. Please make sure that the variable DEFAULT_FILE_PERMS is set correctly into the configuration file! (for example: '0777', or '0755', or '0666'), and change manually by FTP the permissions of that folder. \n";
		  else
		   $out.= "done! The folder is ".$_HOME_DIR."/ and is readable and writable by FTP.\n";
		 }
		}
	   }
	   else
		$out.= "failed! The folder ".$_HOME_DIR."/ exists but is not writable. You have the FTP enabled, but I can't connect to the server ".$_FTP_SERVER." with user ".$_FTP_USERNAME.". Login or password are wrong!\n";
	  }
	  else
	   $out.= "failed! The folder ".$_HOME_DIR."/ exists but is not writable. And I cannot connect to the server ".$_FTP_SERVER." by FTP with user ".$_FTP_USERNAME.". Please verify and modify the parameters into the configuration file.\n";
	 }
	 else
	  $out.= "failed! The folder ".$_HOME_DIR."/ exists but is not writable. You must set the correct permissions (readable and writable) to that folder, or enable FTP.\n";
	}
	else
	 $out.= "done! The folder is ".$_HOME_DIR."/ and is readable and writable.\n";
   }
   $db->Close();
  }

 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

