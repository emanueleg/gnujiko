<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-05-2013
 #PACKAGE: gnujiko-desktop-base
 #DESCRIPTION: Gnujiko Desktop 
 #VERSION: 2.0beta
 #CHANGELOG: 30-05-2013 : Aggiunto html_contents nei moduli e ovviamente nella tabella gnujiko_desktop_modules
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_desktop($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  /* PAGES */
  case 'new-page' : case 'add-page' : return desktop_newPage($args, $sessid, $shellid); break;
  case 'edit-page' : return desktop_editPage($args, $sessid, $shellid); break;
  case 'delete-page' : return desktop_deletePage($args, $sessid, $shellid); break;
  case 'page-info' : return desktop_pageInfo($args, $sessid, $shellid); break;
  case 'page-list' : return desktop_pageList($args, $sessid, $shellid); break;
  case 'serialize-page' : return desktop_serializePage($args, $sessid, $shellid); break;
  
  /* MODULES */
  case 'new-module' : case 'add-module' : return desktop_addModule($args, $sessid, $shellid); break;
  case 'edit-module' : return desktop_editModule($args, $sessid, $shellid); break;
  case 'delete-module' : return desktop_deleteModule($args, $sessid, $shellid); break;
  case 'module-info' : return desktop_moduleInfo($args, $sessid, $shellid); break;
  case 'module-list' : return desktop_moduleList($args, $sessid, $shellid); break;
  case 'serialize-module' : return desktop_serializeModule($args, $sessid, $shellid); break;

  case 'connect' : return desktop_connect($args, $sessid, $shellid); break;
  case 'disconnect' : return desktop_disconnect($args, $sessid, $shellid); break;
  case 'connections' : case 'connection-list' : return desktop_connectionList($args, $sessid, $shellid); break;

  case 'available-modules' : case 'installed-modules' : return desktop_installedModules($args, $sessid, $shellid); break;

  default : return desktop_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function desktop_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function desktop_newPage($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 $sectionType = "default";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-section' : case '-sec' : {$sectionType=$args[$c+1]; $c++;} break;
   case '-section-params' : case '--section-params' : case '-sec-parms' : case '-sec-params' : {$sectionParams=$args[$c+1]; $c++;} break;
   case '-ordering' : {$ordering=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-gid' : {$groupId=$args[$c+1]; $c++;} break;
  }

 if(!$name) return array('message'=>"You must specify a valid name","error"=>"INVALID_NAME");
 
 $uid = $sessInfo['uid'];
 $gid = $group ? _getGID($group) : ($groupId ? $groupId : $sessInfo['gid']);
 if($perms)
 {
  $mod = new GMOD($perms);
  $mod = $mod->MOD;
 }
 else
  $mod = 640;


 $db = new AlpaDatabase();
 if(!$ordering)
 {
  $db->RunQuery("SELECT ordering FROM gnujiko_desktop_pages WHERE 1 ORDER BY ordering DESC LIMIT 1");
  if($db->Read())
   $ordering = $db->record['ordering']+1;
  else
  $ordering = 1;
 }
 $db->RunQuery("INSERT INTO gnujiko_desktop_pages(uid,gid,_mod,name,section_type,section_xml_params,ordering) VALUES('"
	.$uid."','$gid','$mod','".$db->Purify($name)."','".$sectionType."','".$db->Purify($sectionParams)."','$ordering')");
 $id = mysql_insert_id();
 $db->Close();
 $outArr = array('id'=>$id,'name'=>$name,'ordering'=>$ordering);
 $out = "done!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function desktop_editPage($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-section' : case '-sec' : case '-sec-type' : {$sectionType=$args[$c+1]; $c++;} break;
   case '-section-params' : case '--section-params' : case '-sec-parms' : case '-sec-params' : {$sectionParams=$args[$c+1]; $c++;} break;
   case '-ordering' : {$ordering=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-gid' : {$groupId=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify a valid page id","error"=>"INVALID_PAGE");

 $ret = GShell("desktop page-info -id `".$id."`",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 if(!$ret['outarr']['modinfo']['can_write'])
  return array("message"=>"Permission denied!, you have not permissions for edit this page.","error"=>"PERMISSION_DENIED");

 $db = new AlpaDatabase();

 $q = "";
 if($name)
  $q.= ",name='".$db->Purify($name)."'";
 if($ordering)
  $q.= ",ordering='$ordering'";
 if($group)
  $q.= ",gid='"._getGID($group)."'";
 else if(isset($groupId))
  $q.= ",gid='".$groupId."'";
 if($perms)
  $q.= ",_mod='".$perms."'";
 if(isset($sectionType))
  $q.= ",section_type='".($sectionType ? $sectionType : "default")."'";
 if(isset($sectionParams))
  $q.= ",section_xml_params='".$db->Purify($sectionParams)."'";

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE gnujiko_desktop_pages SET ".ltrim($q,",")." WHERE id='$id'");
 $db->Close();
 $out = "Page has been updated!";
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function desktop_deletePage($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify a valid page id","error"=>"INVALID_PAGE");

 $ret = GShell("desktop page-info -id `".$id."`",$sessid,$shellid);
 if($ret['error'])
  return $ret;

 if(($ret['outarr']['modinfo']['uid'] != $sessInfo['uid']) && ($sessInfo['uname'] != 'root'))
  return array("message"=>"Unable to delete page #".$ret['outarr']['id']." - ".$ret['outarr']['name'].". Permission denied!\n", "error"=>"PERMISSION_DENIED");

 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM gnujiko_desktop_pages WHERE id='".$id."'");
 $db->Close();
 $out.= "Page #".$ret['outarr']['id']." - ".$ret['outarr']['name']." has been removed from desktop.";
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function desktop_pageInfo($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
  }

 if(!$id) return array('message'=>"You must specify a valid page id","error"=>"INVALID_PAGE");
 
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_desktop_pages WHERE id='".$id."'");
 if(!$db->Read())
  return array("message"=>"Page #".$id." does not exists!", "error"=>"PAGE_DOES_NOT_EXISTS");
 
 /* CHECK PERMISSION TO READ */
 $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid']);
 if(!$m->canRead($sessInfo['uid']))
  return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");

 $outArr = array('id'=>$db->record['id'], 'name'=>$db->record['name'], 'section_type'=>$db->record['section_type'],'section_params'=>$db->record['section_xml_params'],'ordering'=>$db->record['ordering']);
 $outArr['modinfo'] = $m->toArray($sessInfo['uid']);

 $db->Close();

 if($verbose)
 {
  $out.= "Informations about desktop page '".$outArr['name']."'\n";
  $out.= "ID: ".$outArr['id']."\n";
  $out.= "Permissions: ".$m->toString()."\n";
  $out.= "Section type: ".$outArr['section_type']."\n";
 }
 else
  $out.= "done!\n";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function desktop_pageList($args, $sessid, $shellid)
{
 $orderBy = "ordering ASC";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
  }

 $out = "";
 $outArr = array();

 $m = new GMOD();
 $uQry = $m->userQuery($sessid);

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM gnujiko_desktop_pages WHERE ($uQry) ORDER BY $orderBy");
 while($db->Read())
 {
  $ret = GShell("desktop page-info -id '".$db->record['id']."'",$sessid,$shellid);
  if($ret['error'])
  {
   $out.= "Warning: ".$ret['message']."\n";
   continue;
  }
  $page = $ret['outarr'];
  $outArr[] = $page;
  if($verbose)
   $out.= "#".$page['id']." - ".$page['name']."\n";
 }
 $db->Close();
 $out.= "\n".count($outArr)." desktop pages found.\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function desktop_serializePage($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   default: $serialize=$args[$c]; break;
  }
 
 $list = explode(",",$serialize);

 $db = new AlpaDatabase();
 for($c=0; $c < count($list); $c++)
  $db->RunQuery("UPDATE gnujiko_desktop_pages SET ordering='".$c."' WHERE id='".$list[$c]."'");
 $db->Close();
 return array("message"=>"Done!");
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//------- M O D U L E S ---------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function desktop_addModule($args, $sessid, $shellid)
{
 global $_BASE_PATH;

 $sessInfo = sessionInfo($sessid);

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-title' : {$title=$args[$c+1]; $c++;} break;
   case '-page' : {$pageId=$args[$c+1]; $c++;} break;
   case '-section' : case '-sec' : {$sectionId=$args[$c+1]; $c++;} break;
   case '-params' : {$params=$args[$c+1]; $c++;} break;
   case '-content' : case '-contents' : case '-html-contents' : {$htmlContents=$args[$c+1]; $c++;} break;
   case '-css' : {$cssContents=$args[$c+1]; $c++;} break;
   case '-js' : case '-javascript' : {$jsContents=$args[$c+1]; $c++;} break;
   case '-ordering' : {$ordering=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-gid' : {$groupId=$args[$c+1]; $c++;} break;

   default : {if(!$name) $name=$args[$c];} break;
  }

 if(!$name) return array('message'=>"You must specify a valid module","error"=>"INVALID_NAME");

 /* Check if module exists */
 if(!file_exists($_BASE_PATH."var/desktop/modules/".$name."/index.php"))
  return array('message'=>"Module ".$name." is not installed.",'error'=>'MODULE_IS_NOT_INSTALLED');

 if(!$pageId)
  return array('message'=>"You must specify a valid page id. (with: -page PAGE_ID)",'error'=>"INVALID_PAGE");

 $ret = GShell("desktop page-info -id `".$pageId."`",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 if(!$ret['outarr']['modinfo']['can_write'])
  return array("message"=>"Permission denied!, you have not permissions for insert modules into this page.","error"=>"PERMISSION_DENIED");

 if(!$title)
  $title = $name;

 $uid = $sessInfo['uid'];
 $gid = $group ? _getGID($group) : ($groupId ? $groupId : $sessInfo['gid']);
 if($perms)
 {
  $mod = new GMOD($perms);
  $mod = $mod->MOD;
 }
 else
  $mod = 640;


 $db = new AlpaDatabase();
 if(!$ordering)
 {
  $db->RunQuery("SELECT ordering FROM gnujiko_desktop_modules WHERE section_id='".$sectionId."' ORDER BY ordering DESC LIMIT 1");
  if($db->Read())
   $ordering = $db->record['ordering']+1;
  else
  $ordering = 1;
 }
 $db->RunQuery("INSERT INTO gnujiko_desktop_modules(uid,gid,_mod,page_id,module_name,module_title,section_id,xml_params,ordering,html_contents,css,javascript) VALUES('"
	.$uid."','".$gid."','".$mod."','".$pageId."','".$name."','".$db->Purify($title)."','".$sectionId."','".$db->Purify($params)."','".$ordering."','"
	.$db->Purify($htmlContents)."','".$db->Purify($cssContents)."','".$db->Purify($jsContents)."')");
 $id = mysql_insert_id();
 $db->Close();
 $outArr = array('id'=>$id,'page_id'=>$pageId,'name'=>$name,'title'=>$title,'section_id'=>$sectionId,'ordering'=>$ordering);
 $out = "done!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function desktop_editModule($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-title' : {$title=$args[$c+1]; $c++;} break;
   case '-page' : {$pageId=$args[$c+1]; $c++;} break;
   case '-section' : case '-sec' : {$sectionId=$args[$c+1]; $c++;} break;
   case '-params' : {$params=$args[$c+1]; $c++;} break;
   case '-content' : case '-contents' : case '-html-contents' : {$htmlContents=$args[$c+1]; $c++;} break;
   case '-css' : {$cssContents=$args[$c+1]; $c++;} break;
   case '-js' : case '-javascript' : {$jsContents=$args[$c+1]; $c++;} break;
   case '-ordering' : {$ordering=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-gid' : {$groupId=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify a valid module id","error"=>"INVALID_MODULE");

 $ret = GShell("desktop module-info -id `".$id."`",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 if(!$ret['outarr']['modinfo']['can_write'])
  return array("message"=>"Permission denied!, you have not permissions for edit this module.","error"=>"PERMISSION_DENIED");

 $db = new AlpaDatabase();

 $q = "";
 if($title)
  $q.= ",module_title='".$db->Purify($title)."'";
 if($pageId)
  $q.= ",page_id='".$pageId."'";
 if(isset($sectionId))
  $q.= ",section_id='".$sectionId."'";
 if($ordering)
  $q.= ",ordering='$ordering'";
 if($group)
  $q.= ",gid='"._getGID($group)."'";
 else if(isset($groupId))
  $q.= ",gid='".$groupId."'";
 if($perms)
  $q.= ",_mod='".$perms."'";
 if(isset($params))
  $q.= ",xml_params='".$db->Purify($params)."'";
 if(isset($htmlContents))
  $q.= ",html_contents='".$db->Purify($htmlContents)."'";
 if(isset($cssContents))
  $q.= ",css='".$db->Purify($cssContents)."'";
 if(isset($jsContents))
  $q.= ",javascript='".$db->Purify($jsContents)."'";

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE gnujiko_desktop_modules SET ".ltrim($q,",")." WHERE id='$id'");
 $db->Close();
 $out = "Module has been updated!";
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function desktop_deleteModule($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify a valid module id","error"=>"INVALID_MODULE");

 $ret = GShell("desktop module-info -id `".$id."`",$sessid,$shellid);
 if($ret['error'])
  return $ret;

 if(($ret['outarr']['modinfo']['uid'] != $sessInfo['uid']) && ($sessInfo['uname'] != 'root'))
  return array("message"=>"Unable to delete module #".$ret['outarr']['id']." - ".$ret['outarr']['name'].". Permission denied!\n", "error"=>"PERMISSION_DENIED");

 /* Disconnect all cables */
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM gnujiko_desktop_connections WHERE mod_src='".$id."' OR mod_dest='".$id."'");
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM gnujiko_desktop_modules WHERE id='".$id."'");
 $db->Close();
 $out.= "Module #".$ret['outarr']['id']." - ".$ret['outarr']['name']." has been removed from desktop.";
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function desktop_moduleInfo($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
  }

 if(!$id) return array('message'=>"You must specify a valid module id","error"=>"INVALID_MODULE");
 
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_desktop_modules WHERE id='".$id."'");
 if(!$db->Read())
  return array("message"=>"Module #".$id." does not exists!", "error"=>"MODULE_DOES_NOT_EXISTS");
 
 /* CHECK PERMISSION TO READ */
 $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid']);
 if(!$m->canRead($sessInfo['uid']))
  return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");

 $htmlContents = str_replace(array("<![CDATA[","]]>"),"",$db->record['html_contents']);
 $jsContents = str_replace(array("<![CDATA[","]]>"),"",$db->record['javascript']);
 $cssContents = str_replace(array("<![CDATA[","]]>"),"",$db->record['css']);
 $xmlParams = str_replace(array("<![CDATA[","]]>"),"",$db->record['xml_params']);
 if($xmlParams)
 {
  $xmlParams = ltrim(rtrim($xmlParams));
  $xml = new GXML();
  if($xml->LoadFromString("<xml>".$xmlParams."</xml>"))
   $_XML_PARAMS = $xml->toArray();
  else
   $_XML_PARAMS = array();
 }



 $outArr = array('id'=>$db->record['id'], 'page_id'=>$db->record['page_id'], 'title'=>$db->record['module_title'], 
	'name'=>$db->record['module_name'], 'section_id'=>$db->record['section_id'],'params'=>$_XML_PARAMS,
	'ordering'=>$db->record['ordering'],'htmlcontents'=>$htmlContents,'css'=>$cssContents,'javascript'=>$jsContents);
 $outArr['modinfo'] = $m->toArray($sessInfo['uid']);

 $db->Close();

 if($verbose)
 {
  $out.= "Informations about module '".$outArr['name']."'\n";
  $out.= "ID: ".$outArr['id']."\n";
  $out.= "Title: ".$outArr['title']."\n";
  $out.= "Permissions: ".$m->toString()."\n";
  $out.= "Section ID: ".$outArr['section_id']."\n";
 }
 else
  $out.= "done!\n";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function desktop_moduleList($args, $sessid, $shellid)
{
 $orderBy = "ordering ASC";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-page' : {$pageId=$args[$c+1]; $c++;} break;
   case '-section' : {$sectionId=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
  }

 $out = "";
 $outArr = array();

 if(!$pageId)
  return array('message'=>'You must specify page id. (with: -page PAGE_ID)','error'=>'INVALID_PAGE_ID');

 $ret = GShell("desktop page-info -id `".$pageId."`",$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $m = new GMOD();
 $uQry = $m->userQuery($sessid);

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM gnujiko_desktop_modules WHERE page_id='".$pageId."'".($sectionId ? " AND section_id='".$sectionId."'" : "")
	." ORDER BY $orderBy");
 while($db->Read())
 {
  $ret = GShell("desktop module-info -id '".$db->record['id']."'",$sessid,$shellid);
  if($ret['error'])
  {
   $out.= "Warning: ".$ret['message']."\n";
   continue;
  }
  $module = $ret['outarr'];
  $outArr[] = $module;
  if($verbose)
   $out.= "#".$module['id']." - ".$module['name']."\n";
 }
 $db->Close();
 $out.= "\n".count($outArr)." modules found.\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function desktop_serializeModule($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   default: $serialize=$args[$c]; break;
  }
 
 $list = explode(",",$serialize);

 $db = new AlpaDatabase();
 for($c=0; $c < count($list); $c++)
  $db->RunQuery("UPDATE gnujiko_desktop_modules SET ordering='".$c."' WHERE id='".$list[$c]."'");
 $db->Close();
 return array("message"=>"Done!");
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function desktop_connect($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-page' : {$pageId=$args[$c+1]; $c++;} break;
   case '-src' : case '-source' : case '-source-module' : {$srcMod=$args[$c+1]; $c++;} break;
   case '-src-port' : case '-source-port' : case '-sp' : {$srcPort=$args[$c+1]; $c++;} break;
   case '-dst' : case '-dest' : case '-dest-module' : {$destMod=$args[$c+1]; $c++;} break;
   case '-dst-port' : case '-dest-port' : case '-dp' : {$destPort=$args[$c+1]; $c++;} break;
  }

 if(!$pageId)
  return array('message'=>"You must specify a valid page id. (with: -page PAGE_ID)",'error'=>"INVALID_PAGE");

 $ret = GShell("desktop page-info -id `".$pageId."`",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 if(!$ret['outarr']['modinfo']['can_write'])
  return array("message"=>"Permission denied!, you have not permissions for connect modules into this page.","error"=>"PERMISSION_DENIED");

 if(!$srcMod || !$srcPort)
  return array('message'=>"You must specify source module and port. (with: -src SOURCE_MODULE -src-port PORT_NAME)",'error'=>(!$srcMod ? "INVALID_SOURCE_MODULE" : "INVALID_SOURCE_PORT"));

 if(!$destMod || !$destPort)
  return array('message'=>"You must specify destination module and port. (with: -dest DESTINATION_MODULE -dest-port PORT_NAME)",'error'=>(!$destMod ? "INVALID_DEST_MODULE" : "INVALID_DEST_PORT"));

 $db = new AlpaDatabase();
 $qry = "page_id='".$pageId."' AND ((mod_src='".$srcMod."' AND port_src='".$srcPort."' AND mod_dest='".$destMod."' AND port_dest='".$destPort."')";
 $qry.= "OR (mod_src='".$destMod."' AND port_src='".$destPort."' AND mod_dest='".$srcMod."' AND port_dest='".$srcPort."'))";
 $db->RunQuery("SELECT id FROM gnujiko_desktop_connections WHERE ".$qry." LIMIT 1");
 if($db->Read())
  return array('message'=>"Already connected!",'outarr'=>array('id'=>$db->record['id']));
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO gnujiko_desktop_connections(page_id,mod_src,port_src,mod_dest,port_dest) VALUES('".$pageId."','"
	.$srcMod."','".$srcPort."','".$destMod."','".$destPort."')");
 $id = mysql_insert_id();
 $db->Close();

 $out.= "done!";
 $outArr = array('id'=>$id, 'src_mod'=>$srcMod, 'src_port'=>$srcPort, 'dest_mod'=>$destMod, 'dest_port'=>$destPort);
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function desktop_disconnect($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-page' : {$pageId=$args[$c+1]; $c++;} break;
   case '-src' : case '-source' : case '-source-module' : {$srcMod=$args[$c+1]; $c++;} break;
   case '-src-port' : case '-source-port' : case '-sp' : {$srcPort=$args[$c+1]; $c++;} break;
   case '-dst' : case '-dest' : case '-dest-module' : {$destMod=$args[$c+1]; $c++;} break;
   case '-dst-port' : case '-dest-port' : case '-dp' : {$destPort=$args[$c+1]; $c++;} break;
  }

 if(!$pageId)
  return array('message'=>"You must specify a valid page id. (with: -page PAGE_ID)",'error'=>"INVALID_PAGE");

 $ret = GShell("desktop page-info -id `".$pageId."`",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 if(!$ret['outarr']['modinfo']['can_write'])
  return array("message"=>"Permission denied!, you have not permissions for disconnect modules into this page.","error"=>"PERMISSION_DENIED");

 if(!$srcMod || !$srcPort)
  return array('message'=>"You must specify source module and port. (with: -src SOURCE_MODULE -src-port PORT_NAME)",'error'=>(!$srcMod ? "INVALID_SOURCE_MODULE" : "INVALID_SOURCE_PORT"));

 if(!$destMod || !$destPort)
  return array('message'=>"You must specify destination module and port. (with: -dest DESTINATION_MODULE -dest-port PORT_NAME)",'error'=>(!$destMod ? "INVALID_DEST_MODULE" : "INVALID_DEST_PORT"));

 $db = new AlpaDatabase();
 $qry = "page_id='".$pageId."' AND ((mod_src='".$srcMod."' AND port_src='".$srcPort."' AND mod_dest='".$destMod."' AND port_dest='".$destPort."')";
 $qry.= "OR (mod_src='".$destMod."' AND port_src='".$destPort."' AND mod_dest='".$srcMod."' AND port_dest='".$srcPort."'))";
 $db->RunQuery("DELETE FROM gnujiko_desktop_connections WHERE ".$qry);
 $db->Close();

 return array('message'=>"done!");
}
//-------------------------------------------------------------------------------------------------------------------//
function desktop_connectionList($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-page' : {$pageId=$args[$c+1]; $c++;} break;
   case '-mod' : case '-module' : {$module=$args[$c+1]; $c++;} break;
   case '-port' : {$port=$args[$c+1]; $c++;} break;
  }

 if(!$pageId)
  return array('message'=>"You must specify a valid page id. (with: -page PAGE_ID)",'error'=>"INVALID_PAGE");

 $ret = GShell("desktop page-info -id `".$pageId."`",$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $db = new AlpaDatabase();
 $qry = "page_id='".$pageId."'";
 if($module && $port)
  $qry.= " AND ((mod_src='".$module."' AND port_src='".$port."') OR (mod_dest='".$module."' AND port_dest='".$port."'))";
 else if($module)
  $qry.= " AND (mod_src='".$module."' OR mod_dest='".$module."')";
 $db->RunQuery("SELECT * FROM gnujiko_desktop_connections WHERE ".$qry);
 while($db->Read())
 {
  $outArr[] = array('id'=>$db->record['id'], 'src_mod'=>$db->record['mod_src'], 'src_port'=>$db->record['port_src'], 
	'dest_mod'=>$db->record['mod_dest'], 'dest_port'=>$db->record['port_dest']);
 }
 $db->Close();

 $out.= count($outArr)." connections found.";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function desktop_installedModules($args, $sessid, $shellid)
{
 global $_BASE_PATH;

 $out = "";
 $outArr = array();
 $modules = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--verbose' : $verbose=true; break;
  }

 include_once($_BASE_PATH."var/lib/xmllib.php");

 $d = dir($_BASE_PATH."var/desktop/modules/");
 while(FALSE !== ($entry = $d->read()))
 {
  if($entry == '.' || $entry == '..')
   continue;
  if(is_dir($_BASE_PATH."var/desktop/modules/".ltrim($entry,"/")))
   $modules[] = $entry;
 }

 for($c=0; $c < count($modules); $c++)
 {
  $module = $modules[$c];
  $fileInfo = $_BASE_PATH."var/desktop/modules/".$module."/info.xml";
  if(!file_exists($fileInfo))
  {
   $out.= "Error: Unable to read file info.xml into module folder for ".$module."\n";
   continue;
  }

  $xml = new GXML($fileInfo);
  /* GET INFO */
  $info = $xml->GetElementsByTagName('module');
  if(!count($info))
   $out.= "Error: no tag module found into xml file info for module ".$module."\n";
  else
  {
   $info = $info[0];
   $title = $info->getAttribute('title','');
   $description = $info->getAttribute('description','');
   $icon = $info->getAttribute('icon','');
   $author = $info->getAttribute('author','');
   $version = $info->getAttribute('version','');
  
   $outArr[] = array('name'=>$module, 'title'=>$title,'description'=>$description,'icon'=>$icon,'author'=>$author,'version'=>$version);
   if($verbose)
    $out.= $module." - ".$title." (developed by: ".$author.", ver. ".$version.")\n";
  }
 }

 $out.= count($modules)." modules found!";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

