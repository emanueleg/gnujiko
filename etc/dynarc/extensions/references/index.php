<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 08-05-2013
 #PACKAGE: rubrica
 #DESCRIPTION: References extension for Dynarc archives.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

function dynarcextension_references_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_references` (
 `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `item_id` INT( 11 ) NOT NULL ,
 `name` VARCHAR( 64 ) NOT NULL ,
 `reftype` VARCHAR( 32 ) NOT NULL ,
 `phone` VARCHAR( 14 ) NOT NULL ,
 `email` VARCHAR( 40 ) NOT NULL ,
 INDEX ( `item_id` ) )");
 $db->Close();
 return array("message"=>"References extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_references`");
 $db->Close();

 return array("message"=>"References extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_set($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'name' : {$name=$args[$c+1]; $c++;} break;
   case 'type' : {$type=$args[$c+1]; $c++;} break;
   case 'phone' : {$phone=$args[$c+1]; $c++;} break;
   case 'email' : {$email=$args[$c+1]; $c++;} break;
  }

 if($id)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_references WHERE id='$id'");
  if(!$db->Read())
   return array('message'=>"Reference #$id does not exists!","error"=>"REFERENCE_DOES_NOT_EXISTS");

  $q = "";
  if($name) $q.=",name='".$db->Purify($name)."'";
  if(isset($type)) $q.=",reftype='".$type."'";
  if(isset($phone)) $q.= ",phone='".$phone."'";
  if(isset($email)) $q.=",email='".$email."'";

  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_references SET ".ltrim($q,",")." WHERE id='$id'");
  $db->Close();
  return $itemInfo;
 }

 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_references(item_id,name,reftype,phone,email) VALUES('"
	.$itemInfo['id']."','".$db->Purify($name)."','".$type."','".$phone."','".$email."')");
 $recid = mysql_insert_id();
 $itemInfo['last_reference'] = array('id'=>$recid);
 $db->Close();
 return $itemInfo; 
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'all' : $all=true; break;
  }
 
 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_references WHERE id='$id'");
 else
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_references WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_get($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'name' : $name=true; break;
   case 'type' : $type=true; break;
   case 'phone' : $phone=true; break;
   case 'email' : $email=true; break;
  }

 if(!count($args))
  $all = true;

 $itemInfo['references'] = array();
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_references WHERE item_id='".$itemInfo['id']."' ORDER BY id ASC");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id']);
  if($name || $all) $a['name'] = $db->record['name'];
  if($type || $all) $a['type'] = $db->record['reftype'];
  if($phone || $all) $a['phone'] = $db->record['phone'];
  if($email || $all) $a['email'] = $db->record['email'];
  $itemInfo['references'][] = $a;
 }
 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_info($params, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 /* ARRAYZE PARAMS */
 $tmp = explode("&",$params);
 $params = array();
 for($c=0; $c < count($tmp); $c++)
 {
  $x = explode("=",$tmp[$c]);
  $params[$x[0]] = $x[1];
 }
 /* EOF - ARRAYZE PARAMS */

 /* CHECK FOR ARCHIVE */
 if($params['ap'])
  $ret = GShell("dynarc archive-info -prefix '".$params['ap']."'",$sessid,$shellid);
 else if($params['aid'])
  $ret = GShell("dynarc archive-info -id '".$params['aid']."'",$sessid,$shellid);
 else
  return array("message"=>"You must specify the archive into arguments!","error"=>"INVALID_ARCHIVE");

 if($ret['error'])
  return $ret;
 $archiveInfo = $ret['outarr'];
 
 /* CHECK IF EXTENSION IS INSTALLED */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='references' LIMIT 1");
 if(!$db->Read())
  return array("message"=>"Extension references is not installed into archive ".$archiveInfo['name']."!\nYou can install references extension by typing: dynarc install-extension references -ap ".$archiveInfo['prefix']."\nRemember: Only the owner of archive (or root) can install/uninstall extensions!");
 $db->Close();

 /* CHECK FOR ITEM */
 if(!$params['id'])
  return array("message"=>"You must specify the id of reference record into params","error"=>"INVALID_ITEM_ID");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_references WHERE id='".$params['id']."'");
 if(!$db->Read())
  return array("message"=>"Reference #".$params['id']." does not exists into archive ".$archiveInfo['prefix'],"error"=>"REFERENCE_DOES_NOT_EXISTS");
 $a = $db->record;
 $itemId = $db->record['item_id'];
 $db->Close();

 if($itemId)
 {
  $ret = GShell("dynarc item-info -ap '".$archiveInfo['prefix']."' -id '$itemId'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  /* CHECK FOR PERMISSIONS */
  if(!$ret['outarr']['modinfo']['can_read'])
   return array("message"=>"Permission denied!, you have not permission to read this reference.","error"=>"ITEM_PERMISSION_DENIED");
 }

 $outArr = array('id'=>$a['id'],'item_id'=>$a['item_id'],'name'=>$a['name'],'type'=>$a['reftype'],'phone'=>$a['phone'],'email'=>$a['email']);
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_export($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_references WHERE item_id='".$itemInfo['id']."'");
 $db->Read();
 if(!$db->record[0])
 {
  $db->Close();
  return true;
 }
 $db->Close();

 $xml = "<references>\n";

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_references WHERE item_id='".$itemInfo['id']."' ORDER BY id ASC");
 while($db->Read())
 {
  $xml.= '<item name="'.sanitize($db->record['name']).'" reftype="'
	.$db->record['reftype'].'" phone="'.$db->record['phone'].'" email="'.$db->record['email'].'"';
  $xml.="/>\n";
 }
 $db->Close();
 $xml.= "</references>\n";

 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_import($sessid, $shellid, $archiveInfo, $itemInfo, $extNode, $isCategory=false)
{
 if($isCategory)
  return true;

 $list = $extNode->GetElementsByTagName('item');
 $fields = array('name','reftype','phone','email');
 for($c=0; $c < count($list); $c++)
 {
  $n = $list[$c];
  $extQ = "";
  for($i=0; $i < count($fields); $i++)
   $extQ.= ",".$fields[$i]."=\"".$n->getString($fields[$i])."\"";
  GShell("dynarc edit-item -ap `".$archiveInfo['prefix']."` -id `".$itemInfo['id']."` -extset `references.".ltrim($extQ,",")."`",$sessid, $shellid);
 }
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_references WHERE item_id='".$itemInfo['id']."'");
 $db->Close();
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_references WHERE item_id='".$srcInfo['id']."' ORDER BY id ASC");
 while($db->Read())
 {
  $db2->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_references(item_id,name,reftype,phone,email) VALUES('"
	.$cloneInfo['id']."','".$db2->Purify($db->record['name'])."','".$db->record['reftype']."','".$db->record['phone']."','".$db->record['email']."')");
 }
 $db->Close();
 $db2->Close();
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 if($isCategory)
  return;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_references WHERE item_id='".$itemInfo['id']."'");
 $db->Read();
 if(!$db->record[0])
 {
  $db->Close();
  return true;
 }
 $db->Close();

 $xml = "<references>\n";

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_references WHERE item_id='".$itemInfo['id']."' ORDER BY isdefault DESC,id ASC");
 while($db->Read())
 {
  $xml.= '<item name="'.sanitize($db->record['name']).'" reftype="'
	.$db->record['reftype'].'" phone="'.$db->record['phone'].'" email="'.$db->record['email'].'"';
  $xml.="/>\n";
 }
 $db->Close();
 $xml.= "</references>\n";

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_references_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $extNode, $isCategory=false)
{
 global $_USERS_HOMES;

 if($isCategory)
  return true;

 $list = $extNode->GetElementsByTagName('item');
 $fields = array('name','reftype','phone','email');
 for($c=0; $c < count($list); $c++)
 {
  $n = $list[$c];
  $extQ = "";
  for($i=0; $i < count($fields); $i++)
   $extQ.= ",".$fields[$i]."=\"".$n->getString($fields[$i])."\"";
  GShell("dynarc edit-item -ap `".$archiveInfo['prefix']."` -id `".$itemInfo['id']."` -extset `references.".ltrim($extQ,",")."`",$sessid, $shellid);
 }
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

