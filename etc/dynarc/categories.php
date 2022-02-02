<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-09-2013
 #PACKAGE: dynarc
 #DESCRIPTION: Category functions for Dynarc
 #VERSION: 2.13beta
 #CHANGELOG: 17-09-2013 : Aggiunto parent-id and parent-tag nella funzione cat-info.
			 29-07-2013 : Bug fix on new category.
			 27-03-2013 : Aggiunto parametro -get su funzione cat-tree. Manca da aggiungere parametro -extget.
			 14-12-2012 : Bug fix in new-category with code-num.
			 03-12-2012 : Completamento delle funzioni di base sulle estensioni.
			 22-11-2012 : Bug fix in edit,copy and move.
			 21-11-2012 : Bug fix with DynarcSync.
			 16-11-2012 : Integrazione con DynarcSync.
			 17-04-2012 : Aggiunto parametri --get-items-count e --get-total-items-count su funzione categoryList.
			 21-03-2012 : Aggiunto parametro -before e -after su funzione newCategory
			 03-01-2012 : Aggiunto parametro --if-exists su deleteCategory
 #TODO:
 
*/

global $_BASE_PATH;
include_once($_BASE_PATH."etc/dynarc/archives.php");

function dynarc_newCategory($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-parent' : {$parentId=$args[$c+1]; $c++;} break;
   case '-pt' : case '--parent-tag' : {$parentTag=$args[$c+1]; $c++;} break;
   case '-tag' : {$tag=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-code' : {$code=$args[$c+1]; $c++;} break;
   case '-desc' : {$desc=$args[$c+1]; $c++;} break;
   case '-ordering' : {$ordering=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-groupid' : {$groupId=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
   case '--publish' : case '-publish' : $published=true; break;
   case '-ctime' : {$ctime=strtotime($args[$c+1]); $c++;} break;

   /* Default key ordering */
   case '--def-order-field' : {$setOrderKey=$args[$c+1]; $c++;} break;
   case '--def-order-method' : {$setOrderMethod=strtoupper($args[$c+1]); $c++;} break;

   case '-set' : {$set=$args[$c+1]; $c++;} break;
   case '-extset' : {$extset=$args[$c+1]; $c++;} break;

   case '-before' : {$beforeId=$args[$c+1]; $c++;} break; // <-- Insert category before this node */
   case '-after' : {$afterId=$args[$c+1]; $c++;} break; // <-- Insert category after this node */

   /* PRIVATE */
   case '-syncid' : {$syncid=$args[$c+1]; $c++;} break;
  }

 $out = "";
 $outArr = array();
 $sessInfo = sessionInfo($sessid);

 /* CHECK FOR ARCHIVE */
 if($extraVar)
  $archiveInfo = $extraVar;
 else
 {
  $ret = dynarc_checkForArchive($args,$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }

 if(!$name)
 {
  $out.= "You must specify category name. (with: -name cat_name)\n";
  return array("message"=>$out,"error"=>"INVALID_CAT_NAME");
 }
 if($parentId)
 {
  $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -id $parentId -get hierarchy",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $parentInfo = $ret['outarr'];
 }
 else if($parentTag)
 {
  $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -tag '$parentTag' -get hierarchy",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $parentInfo = $ret['outarr'];
  $parentId = $parentInfo['id'];
 }
 
 if($parentInfo)
 {
  /* CHECK FOR PARENT PERMISSIONS */
  if(!$parentInfo['modinfo']['can_write'])
   return array("message"=>"Permission denied!, you have not permission to insert sub-categories into this category.","error"=>"CATEGORY_PERMISSION_DENIED");
 }

 if(!$ord)
 {
  $db = new AlpaDatabase();
  if($beforeId || $afterId)
  {
   $db->RunQuery("SELECT parent_id,ordering FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".($beforeId ? $beforeId : $afterId)."'");
   if(!$db->Read())
	$ord = 0;
   else
   {
	$ord = $beforeId ? $db->record['ordering'] : $db->record['ordering']+1;
	$parentId = $db->record['parent_id'];
	if(!$parentInfo || ($parentInfo['id'] != $parentId))
	{
	 $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -id $parentId -get hierarchy",$sessid,$shellid,$archiveInfo);
	 if($ret['error'])
	  return $ret;
	 $parentInfo = $ret['outarr'];
	}
   }
  }

  if(!$ord)
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT ordering FROM dynarc_".$archiveInfo['prefix']."_categories WHERE parent_id='".$parentId."' AND trash='0' ORDER BY ordering DESC LIMIT 1");
   if(!$db->Read())
    $ord = 1;
   else
    $ord = $db->record['ordering']+1;
   $db->Close();
  }
 }

 if($group)
  $groupId = _getGID($group);

 if($group)
  $groupId = _getGID($group);

 $now = $ctime ? $ctime : time();
 $uid = $sessInfo['uid'];
 $gid = $groupId ? $groupId : $sessInfo['gid'];
 $mod = $perms ? $perms : ($archiveInfo['def_cat_perms'] ? $archiveInfo['def_cat_perms'] : 640);

 if(!$published)
  $published = $archiveInfo['def_cat_published'];

 if(!isset($code))
 {
  /* detect code */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT code FROM dynarc_".$archiveInfo['prefix']."_categories WHERE parent_id='".$parentId."' AND trash='0' ORDER BY code DESC LIMIT 1");
  if($db->Read())
   $code = increase_catcode($db->record['code']);
  else if($parentInfo)
  {
   if(!$parentInfo['code'])
	$code = 1;
   else if(!is_numeric(substr($parentInfo['code'],-1)))
	$code = $parentInfo['code']."1";
   else
	$code = $parentInfo['code'].".1";
  }
  else
   $code = 1;
  $db->Close();
 }

 $setOrderMethod = ($setOrderMethod == "ASC") ? 1 : 0;

 if($beforeId || $afterId)
 {
  $nextOrd = $ord+1;
  $db = new AlpaDatabase();
  $db2 = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_categories WHERE parent_id='".$parentId."' AND ordering>='".$ord."' AND trash='0' ORDER BY ordering ASC");
  while($db->Read())
  {
   $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET ordering='".$nextOrd."' WHERE id='".$db->record['id']."'");
   $nextOrd++;
  }
  $db2->Close();
  $db->Close();
 }

 $out.= "Creating category...";
 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_categories(uid,gid,_mod,parent_id,tag,code,
	name,description,ordering,ctime,published,hierarchy,defordfield,defordasc) VALUES('$uid','$gid','$mod','"
	.($parentInfo ? $parentInfo['id'] : 0)."','$tag','$code','".$db->Purify($name)."','".$db->Purify($desc)."','$ord','".date('Y-m-d H:i:s',$now)."','$published','"
	.($parentInfo ? $parentInfo['hierarchy'].$parentInfo['id']."," : ",")."','".$setOrderKey."','".$setOrderMethod."')");
 $id = mysql_insert_id();

 if($archiveInfo['sync_enabled'])
 {
  if(!$syncid)
   $syncid = md5($id.$now.rand(1,255));
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET syncid='".$syncid."' WHERE id='".$id."'");
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_synclog(syncid,cat_id,uid,gid,_mod,status,logtime) VALUES('".$syncid."','".$id."','"
	.$uid."','".$gid."','".$mod."','CREATED','".date('Y-m-d H:i:s',$now)."')");
 }
 $db->Close();

 $out.= "done! Category ID is: #$id\n";
 $a = array('id'=>$id,'name'=>$name,'code'=>$code,'desc'=>$desc,'parent_id'=>$parentId,'ordering'=>$ord,
	'def_order_field'=>$setOrderKey,'def_order_method'=>$setOrderMethod,'hierarchy'=>($parentInfo ? $parentInfo['hierarchy'].$parentInfo['id']."," : ""));

 if($set)
  $a = dynarc_parseCatSet("set",$set,$sessid, $shellid, $archiveInfo, $a);
 
 if($extset)
  $a = dynarc_parseCatExtensionSet("set",$extset,$sessid, $shellid, $archiveInfo, $a);

 $outArr = $a;

 // call oncreatecategory function inherited if exists //
 if($archiveInfo['inherit'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
  $db->Read();
  if($db->record['fun_file'] && file_exists($_BASE_PATH.$db->record['fun_file']))
  {
   include_once($_BASE_PATH.$db->record['fun_file']);
   if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_oncreatecategory",true))
   {
    $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_oncreatecategory", $args, $sessid, $shellid, $archiveInfo, $a);
    if($ret['error'])
	 return $ret;
    else if($ret['outarr'])
	 $outArr = $ret['outarr'];
   }
  }
  $db->Close();
 }

 // call oncreatecategory function if exists //
 if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
 {
  include_once($_BASE_PATH.$archiveInfo['functionsfile']);
  if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_oncreatecategory",true))
  {
   $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_oncreatecategory", $args, $sessid, $shellid, $archiveInfo, $a);
   if($ret['error'])
	return $ret;
   else if($ret['outarr'])
	$outArr = $ret['outarr'];
  }
 }

 // call oncreatecategory function from all installed extensions //
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."'");
 while($db->Read())
 {
  /* EXECUTE FUNCTION */
  include_once($_BASE_PATH."etc/dynarc/extensions/".$db->record['extension_name']."/index.php");
  if(is_callable("dynarcextension_".$db->record['extension_name']."_oncreatecategory",true))
  {
   $ret = call_user_func("dynarcextension_".$db->record['extension_name']."_oncreatecategory", $args, $sessid, $shellid, $archiveInfo, $a);
   if($ret['error'])
    return $ret;
  }
 }
 $db->Close();

 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_editCategory($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
   case '-parent' : {$parentId=$args[$c+1]; $c++;} break;
   case '-pt' : case '--parent-tag' : {$parentTag=$args[$c+1]; $c++;} break;
   case '-tag' : {$tag=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-code' : {$code=$args[$c+1]; $c++;} break;
   case '-desc' : {$desc=$args[$c+1]; $c++;} break;
   case '-ordering' : {$ordering=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-groupid' : {$groupId=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
   case '--publish' : case '-publish' : $published=true; break;
   case '--unpublish' : case '-unpublish' : $published=false; break;

   /* Default key ordering */
   case '--def-order-field' : {$setOrderKey=$args[$c+1]; $c++;} break;
   case '--def-order-method' : {$setOrderMethod=strtoupper($args[$c+1]); $c++;} break;

   // variables //
   case '-set' : {$set=$args[$c+1]; $c++;} break;
   case '-extset' : {$extset=$args[$c+1]; $c++;} break;
   case '-extunset' : {$extunset=$args[$c+1]; $c++;} break;
  }
 
 $out = "";
 $outArr = array();

 /* CHECK FOR ARCHIVE */
 if($extraVar)
  $archiveInfo = $extraVar;
 else
 {
  $ret = dynarc_checkForArchive($args,$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }

 /* CHECK CATEGORY FOR INFO */
 $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' ".($catTag ? "-tag '$catTag'" : "-id '$id'"),$sessid,$shellid,$archiveInfo);
 if($ret['error'])
  return $ret;
 $catInfo = $ret['outarr'];

 /* CHECK PERMISSIONS */
 $sessInfo = sessionInfo($sessid);
 if(($catInfo['modinfo']['uid'] != $sessInfo['uid']) && ($sessInfo['uname'] != 'root'))
  return array("message"=>"Only the owner can change category info!\n", "error"=>"PERMISSION_DENIED");

 /* CHECK FOR PARENT */
 if($parentId)
 {
  $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -id $parentId -get hierarchy",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $parentInfo = $ret['outarr'];
 }
 else if($parentTag)
 {
  $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -tag '$parentTag' -get hierarchy",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $parentInfo = $ret['outarr'];
 }
 if($parentInfo)
 {
  /* CHECK FOR PERMISSIONS */
  if(!$parentInfo['modinfo']['can_write'])
   return array("message"=>"Permission denied!, you have not permission to insert sub-categories into this category.","error"=>"CATEGORY_PERMISSION_DENIED");
 }

 $db = new AlpaDatabase();

 $a = $catInfo;
 /* WRITE CHANGES TO DATABASE */
 $q = "";
 if($name){
  $q.= ",name='".$db->Purify($name)."'"; $a['name']=$name;}
 if(isset($code)){
  $q.= ",code='$code'"; $a['code']=$code;}
 if(isset($desc)){
  $q.= ",description='".$db->Purify($desc)."'"; $a['desc']=$desc;}
 if(isset($published)){
  $q.= ",published='$published'"; $a['published']=$published;}
 if($ord){
  $q.= ",ordering='$ord'"; $a['ordering']=$ord;}
 if($parentInfo){
  $q.= ",parent_id='".$parentInfo['id']."',hierarchy='".$parentInfo['hierarchy'].$parentInfo['id'].",'"; $a['parent_id']=$parentInfo['id'];
  if($catInfo['parent_id'] != $parentInfo['id'])
   $a['old_parent_id'] = $catInfo['parent_id'];
 }
 if(isset($tag)){
  $q.= ",tag='$tag'"; $a['tag']=$tag;}
 if(isset($perms))
 {
  if(($catInfo['modinfo']['uid'] == $sessInfo['uid']) || ($sessInfo['uname'] == 'root'))
   $q.= ",_mod='$perms'";
 }
 if(isset($setOrderKey))
  $q.= ",defordfield='".$setOrderKey."'";
 if(isset($setOrderMethod))
  $q.= ",defordasc='".(($setOrderMethod == "ASC") ? 1 : 0)."'";

 if($group)
  $groupId = _getGID($group);

 if($groupId)
 {
  if(($catInfo['modinfo']['uid'] == $sessInfo['uid']) || ($sessInfo['uname'] == 'root'))
   $q.= ",gid='$groupId'";
 }

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET ".ltrim($q,",")." WHERE id='".$catInfo['id']."'");
 $db->Close();
 $out.= "done!\n";

 if($archiveInfo['sync_enabled'])
 {
  $q = ",status='UPDATED',logtime='".date('Y-m-d H:i:s')."'";
  if(isset($perms))
  {
   if(($catInfo['modinfo']['uid'] == $sessInfo['uid']) || ($sessInfo['uname'] == 'root'))
    $q.= ",_mod='$perms'";
  }
  if($groupId)
  {
   if(($catInfo['modinfo']['uid'] == $sessInfo['uid']) || ($sessInfo['uname'] == 'root'))
    $q.= ",gid='$groupId'";
  }
  
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT logtime FROM dynarc_".$archiveInfo['prefix']."_synclog WHERE syncid='".$catInfo['syncid']."' LIMIT 1");
  if($db->Read())
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET ".ltrim($q,",")." WHERE syncid='".$catInfo['syncid']."'");
  else
   $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_synclog(syncid,cat_id,uid,gid,_mod,status,logtime) VALUES('".$catInfo['syncid']."','".$catInfo['id']."','".$catInfo['modinfo']['uid']."','".($groupId ? $groupId : $catInfo['modinfo']['gid'])."','".($perms ? $perms : $catInfo['modinfo']['mod'])."','CREATED','".date('Y-m-d H:i:s')."')");
  $db->Close();
 }

 if($set)
  $a = dynarc_parseCatSet("set",$set,$sessid, $shellid, $archiveInfo, $a);

 if($extset)
 {
  $ret = dynarc_parseCatExtensionSet("set",$extset,$sessid, $shellid, $archiveInfo, $a);
  if($ret)
   $a = $ret;
 }
 if($extunset)
  dynarc_parseCatExtensionSet("unset",$extunset,$sessid, $shellid, $archiveInfo, $a);

 $outArr = $a;

 // call oneditcategory function inherited if exists //
 if($archiveInfo['inherit'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
  $db->Read();
  if($db->record['fun_file'] && file_exists($_BASE_PATH.$db->record['fun_file']))
  {
   include_once($_BASE_PATH.$db->record['fun_file']);
   if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_oneditcategory",true))
   {
    $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_oneditcategory", $args, $sessid, $shellid, $archiveInfo, $a);
    if($ret['error'])
	 return $ret;
    else if($ret['outarr'])
	 $outArr = $ret['outarr'];
   }
  }
  $db->Close();
 }

 // call oneditcategory function if exists //
 if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
 {
  include_once($_BASE_PATH.$archiveInfo['functionsfile']);
  if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_oneditcategory",true))
  {
   $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_oneditcategory", $args, $sessid, $shellid, $archiveInfo, $a);
   if($ret['error'])
	return $ret;
   else if($ret['outarr'])
	$outArr = $ret['outarr'];
  }
 }

 // call oneditcategory function from all installed extensions //
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."'");
 while($db->Read())
 {
  /* EXECUTE FUNCTION */
  include_once($_BASE_PATH."etc/dynarc/extensions/".$db->record['extension_name']."/index.php");
  if(is_callable("dynarcextension_".$db->record['extension_name']."_oneditcategory",true))
  {
   $ret = call_user_func("dynarcextension_".$db->record['extension_name']."_oneditcategory", $args, $sessid, $shellid, $archiveInfo, $a);
   if($ret['error'])
    return $ret;
  }
 }
 $db->Close();


 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_deleteCategory($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;
 $ids = array();
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-id' : case '-cat' : {$ids[]=$args[$c+1]; $c++;} break;
   case '-tag' : {$tag=$args[$c+1]; $c++;} break;
   case '-r' : $delete=true; break;
   case '--return-cat-info' : $returnCatInfo=true; break;
   case '--if-exists' : $forceTrue=true; break; // force for non return error if category does not exists. //
  }

 $out = "";
 $outArr = array();
 $categories = array();

 /* CHECK FOR ARCHIVE */
 if($extraVar)
  $archiveInfo = $extraVar;
 else
 {
  $ret = dynarc_checkForArchive($args,$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }

 if($tag)
 {
  $ret = GShell("dynarc cat-info -tag '$tag' -ap '".$archiveInfo['prefix']."'",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
  {
   if($forceTrue) $ret['error'] = null;
   return $ret;
  }
  $categories[] = $ret['outarr'];
 }
 else
 {
  for($c=0; $c < count($ids); $c++)
  {
   $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -id ".$ids[$c],$sessid,$shellid,$archiveInfo);
   if($ret['error'])
   {
    if($forceTrue) $ret['error'] = null;
    return $ret;
   }
   $categories[] = $ret['outarr'];
  }
 }

 $sessInfo = sessionInfo($sessid);
 for($c=0; $c < count($categories); $c++)
 {
  $catInfo = $categories[$c];

  /* CHECK PERMISSIONS */
  if(($catInfo['modinfo']['uid'] != $sessInfo['uid']) && ($sessInfo['uname'] != 'root'))
   return array("message"=>"Unable to remove category #".$catInfo['id']." - ".$catInfo['name'].". Permission denied!\n", "error"=>"PERMISSION_DENIED");

   // REMOVE ITEMS //
   $q = "";
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_items WHERE cat_id='".$catInfo['id']."'");
   while($db->Read())
    $q.= " -id ".$db->record['id'];
   $db->Close();
   if($q != "")
   {
    $ret = GShell("dynarc delete-item -ap '".$archiveInfo['prefix']."'".($delete ? " -r" : "").$q, $sessid, $shellid, $extraVar);
	if($ret['error'])
	 return $ret;
   }

   // REMOVE SUB-CATEGORIES RECURSIVELY //
   $q = "";
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_categories WHERE parent_id='".$catInfo['id']."'");
   while($db->Read())
	$q.= " -id ".$db->record['id'];
   $db->Close();
   if($q != "")
   {
	$ret = GShell("dynarc delete-cat -ap '".$archiveInfo['prefix']."'".($delete ? " -r" : "").$q, $sessid, $shellid, $extraVar);
	if($ret['error'])
	 return $ret;
   }

  if($delete) // REMOVE ALL SUB-CATEGORIES AND ITEMS //
  {
   // call ondeletecategory function inherited if exists //
   if($archiveInfo['inherit'])
   {
    $db = new AlpaDatabase();
    $db->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
    $db->Read();
    if($db->record['fun_file'] && file_exists($_BASE_PATH.$db->record['fun_file']))
    {
     include_once($_BASE_PATH.$db->record['fun_file']);
     if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_ondeletecategory",true))
     {
      $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_ondeletecategory", $args, $sessid, $shellid, $archiveInfo, $catInfo);
      if($ret['error'])
	   return $ret;
     }
    }
    $db->Close();
   }

   // call ondeletecategory function if exists //
   if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
   {
    include_once($_BASE_PATH.$archiveInfo['functionsfile']);
    if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_ondeletecategory",true))
    {
	 $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_ondeletecategory", $args, $sessid, $shellid, $archiveInfo, $catInfo);
	 if($ret['error'])
	  return $ret;
    }
   }

   // call ondeletecategory function from all installed extensions //
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."'");
   while($db->Read())
   {
	/* EXECUTE FUNCTION */
	include_once($_BASE_PATH."etc/dynarc/extensions/".$db->record['extension_name']."/index.php");
	if(is_callable("dynarcextension_".$db->record['extension_name']."_ondeletecategory",true))
	{
	 $ret = call_user_func("dynarcextension_".$db->record['extension_name']."_ondeletecategory", $args, $sessid, $shellid, $archiveInfo, $catInfo);
	 if($ret['error'])
	  return $ret;
	}
   }
   $db->Close();

   /* REMOVE FROM DATABASE */
   $db = new AlpaDatabase();
   $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$catInfo['id']."'");
   $db->Close();

   if($archiveInfo['sync_enabled'])
   {
    $db = new AlpaDatabase();
    $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET status='REMOVED',logtime='".date('Y-m-d H:i:s')."' WHERE syncid='".$catInfo['syncid']."'");
    $db->Close();
   }

   $out.= "category #".$catInfo['id']." has been removed!\n";
   if($returnCatInfo)
	$outArr['removed'][] = $catInfo;
  }
  else
  {
   // call ontrashcategory function inherited if exists //
   if($archiveInfo['inherit'])
   {
    $db = new AlpaDatabase();
    $db->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
    $db->Read();
    if($db->record['fun_file'] && file_exists($_BASE_PATH.$db->record['fun_file']))
    {
     include_once($_BASE_PATH.$db->record['fun_file']);
     if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_ontrashcategory",true))
     {
      $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_ontrashcategory", $args, $sessid, $shellid, $archiveInfo, $catInfo);
      if($ret['error'])
	   return $ret;
     }
    }
    $db->Close();
   }

   // call ontrashcategory function if exists //
   if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
   {
    include_once($_BASE_PATH.$archiveInfo['functionsfile']);
    if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_ontrashcategory",true))
    {
	 $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_ontrashcategory", $args, $sessid, $shellid, $archiveInfo, $catInfo);
	 if($ret['error'])
	  return $ret;
    }
   }

   // call ontrashcategory function from all installed extensions //
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."'");
   while($db->Read())
   {
	/* EXECUTE FUNCTION */
	include_once($_BASE_PATH."etc/dynarc/extensions/".$db->record['extension_name']."/index.php");
	if(is_callable("dynarcextension_".$db->record['extension_name']."_ontrashcategory",true))
	{
	 $ret = call_user_func("dynarcextension_".$db->record['extension_name']."_ontrashcategory", $args, $sessid, $shellid, $archiveInfo, $catInfo);
	 if($ret['error'])
	  return $ret;
	}
   }
   $db->Close();

   // TRASH ONLY //
   $db = new AlpaDatabase();
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET trash='1' WHERE id='".$catInfo['id']."'");
   $db->Close();

   // update sync status
   if($archiveInfo['sync_enabled'])
   {
    $db = new AlpaDatabase();
    $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET status='TRASHED',logtime='".date('Y-m-d H:i:s')."' WHERE syncid='".$catInfo['syncid']."'");
    $db->Close();
   }

   $out.= "category #".$catInfo['id']." has ben trashed!\n";
   if($returnCatInfo)
	$outArr['trashed'][] = $catInfo;
  }
 }
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_categoryList($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;
 $parentId = 0;
 
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-parent' : {$parentId=$args[$c+1]; $c++;} break;
   case '-pt' : case '--parent-tag' : {$parentTag=$args[$c+1]; $c++;} break;
   case '-tag' : {$tag=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '-get' : {$get=$args[$c+1]; $c++;} break;
   case '-extget' : {$extget=$args[$c+1]; $c++;} break;
   case '--check-if-has-items' : $checkIfHasItems=true; break;
   case '--get-items-count' : $getItemsCount=true; break; // Get count of items into this category.
   case '--get-total-items-count' : $getTotalItemsCount=true; break; // Get count of all items into all tree of this category.
  }

 /* CHECK FOR ARCHIVE */
 if($extraVar)
  $archiveInfo = $extraVar;
 else
 {
  $ret = dynarc_checkForArchive($args,$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }

 /* CHECK FOR PARENT */
 if($parentId)
 {
  $ret = GShell("dynarc cat-info --archive-prefix '".$archiveInfo['prefix']."' -id ".$parentId,$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $parentInfo = $ret['outarr'];
 }
 else if($parentTag)
 {
  $ret = GShell("dynarc cat-info --archive-prefix '".$archiveInfo['prefix']."' -tag '".$parentTag."'",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $parentInfo = $ret['outarr'];
  $parentId = $parentInfo['id'];
 }

 $out = "";
 $outArr = array();

 $m = new GMOD();
 $uQry = $m->userQuery($sessid,null,"dynarc_".$archiveInfo['prefix']."_categories");

 $db = new AlpaDatabase();

 $qry = "SELECT id FROM dynarc_".$archiveInfo['prefix']."_categories WHERE ($uQry)";
 if($parentId)
 {
  $qry.= " AND parent_id='".$parentId."'";
  if($parentInfo['def_order_field'] && !$orderBy)
   $orderBy = $parentInfo['def_order_field']." ".$parentInfo['def_order_method'];
 }
 else if(!$where)
  $qry.= " AND parent_id='0'";
 if($tag)
  $qry.= " AND tag='".$tag."'";
 if($where)
  $qry.= " AND (".$where.")";
 $qry.= " AND trash='0'";

 if(!$orderBy)
  $orderBy = "ordering ASC";
 $qry.= " ORDER BY ".$orderBy;

 if($limit)
  $qry.= " LIMIT ".$limit;


 $db->RunQuery($qry);
 while($db->Read())
 {
  $ret = GShell("dynarc cat-info --archive-prefix '".$archiveInfo['prefix']."' -id ".$db->record['id'].($get ? " -get \"".$get."\"" : "").($extget ? " -extget \"".$extget."\"" : ""),$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   continue;
  // OPT: --check-if-has-items
  if($checkIfHasItems)
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_items WHERE hierarchy LIKE '%,".$ret['outarr']['id'].",%' AND trash='0' LIMIT 1");
   if($db2->Read())
	$ret['outarr']['has_items'] = true;
   $db2->Close();
  }
  // OPT: --get-items-count
  if($getItemsCount)
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_items WHERE cat_id='".$ret['outarr']['id']."' AND trash='0'");
   $db2->Read();
   $ret['outarr']['items_count'] = $db2->record[0];
   $db2->Close();
  }
  // OPT: --get-total-items-count
  if($getTotalItemsCount)
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_items WHERE hierarchy LIKE '%,".$ret['outarr']['id'].",%' AND trash='0'");
   $db2->Read();
   $ret['outarr']['total_items_count'] = $db2->record[0];
   $db2->Close();
  }

  $outArr[] = $ret['outarr'];
  if($shellid != null)
   $out.= "#".$ret['outarr']['id'].". ".$ret['outarr']['name']."\n";
 }
 $db->Close();
 $out.= count($outArr)." categories found.\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_categoryFind($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;
 $field = "name";
 $orderBy = "ordering ASC";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-parent' : {$parentId=$args[$c+1]; $c++;} break;
   case '-pt' : case '--parent-tag' : {$parentTag=$args[$c+1]; $c++;} break;
   case '-tag' : {$tag=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '-get' : {$get=$args[$c+1]; $c++;} break;
   case '-extget' : {$extget=$args[$c+1]; $c++;} break;
   case '--check-if-has-items' : $checkIfHasItems=true; break;
   case '-field' : {$field=$args[$c+1]; $c++;} break;
   case '--return-full-path' : $returnFullPath=true; break;
   default : $searchQry = $args[$c]; break;
  }

 /* CHECK FOR ARCHIVE */
 if($extraVar)
  $archiveInfo = $extraVar;
 else
 {
  $ret = dynarc_checkForArchive($args,$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }

 /* CHECK FOR PARENT */
 if($parentId)
 {
  $ret = GShell("dynarc cat-info --archive-prefix '".$archiveInfo['prefix']."' -id ".$parentId,$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $parentInfo = $ret['outarr'];
 }
 else if($parentTag)
 {
  $ret = GShell("dynarc cat-info --archive-prefix '".$archiveInfo['prefix']."' -tag '$parentTag'",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $parentInfo = $ret['outarr'];
  $parentId = $parentInfo['id'];
 }

 $out = "";
 $outArr = array();

 $m = new GMOD();
 $uQry = $m->userQuery($sessid,null,"dynarc_".$archiveInfo['prefix']."_categories");

 $db = new AlpaDatabase();

 $qry = "SELECT id,hierarchy FROM dynarc_".$archiveInfo['prefix']."_categories WHERE ($uQry)";
 if(isset($parentId))
  $qry.= " AND parent_id='$parentId'";
 if($tag)
  $qry.= " AND tag='$tag'";
 if($where)
  $qry.= " AND (".$where.")";
 $qry.= " AND trash='0'";

 if($searchQry && $field)
 {
  $qry.= " AND ((".$field."=\"".$db->Purify($searchQry)."\") OR (".$field." LIKE \"".$db->Purify($searchQry)."%\") OR (".$field." LIKE \"%"
	.$db->Purify($searchQry)."%\") OR (".$field." LIKE \"%".$db->Purify($searchQry)."\"))";
 }

 $qry.= " ORDER BY ".$orderBy;
 if($limit)
  $qry.= " LIMIT ".$limit;

 if($returnFullPath)
  $pathNames = array();

 $db->RunQuery($qry);
 while($db->Read())
 {
  $ret = GShell("dynarc cat-info --archive-prefix '".$archiveInfo['prefix']."' -id ".$db->record['id'].($get ? " -get \"".$get."\"" : "").($extget ? " -extget \"".$extget."\"" : ""),$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   continue;
  if($returnFullPath)
  {
   if($db->record['hierarchy'] == ",")
	$ret['outarr']['fullpathstring'] = $ret['outarr']['name'];
   else
   {
    $db2 = new AlpaDatabase();
    $pathNames[$db->record['id']] = $ret['outarr']['name'];
    $x = explode(",",ltrim(rtrim($db->record['hierarchy'],","),","));
    $ret['outarr']['fullpathstring'] = "";
    for($c=0; $c < count($x); $c++)
    {
	 if(!$pathNames[$x[$c]])
	 {
	  $db2->RunQuery("SELECT name FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$x[$c]."'");
	  $db2->Read();
	  $pathNames[$x[$c]] = $db2->record['name'];
	 }
	 $ret['outarr']['fullpathstring'].= $pathNames[$x[$c]]." - ";
    }
    $db2->Close();
    $ret['outarr']['fullpathstring'].= $ret['outarr']['name'];
   }
  }
   
  if($checkIfHasItems)
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_items WHERE hierarchy LIKE '%,".$ret['outarr']['id'].",%' AND trash='0' LIMIT 1");
   if($db2->Read())
	$ret['outarr']['has_items'] = true;
   $db2->Close();
  }

  $outArr[] = $ret['outarr'];
  if($shellid != null)
   $out.= "#".$ret['outarr']['id'].". ".$ret['outarr']['name']."\n";
 }
 $db->Close();
 $out.= count($outArr)." categories found.\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_categoryInfo($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;
 $archives = array(); // <--- for links
 
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-id' : {$id = $args[$c+1]; $c++;} break;
   case '-tag' : {$tag = $args[$c+1]; $c++;} break;
   case '-parent' : {$parentId=$args[$c+1]; $c++;} break;
   case '-pt' : case '--parent-tag' : {$parentTag=$args[$c+1]; $c++;} break;
   case '-into' : {$into=$args[$c+1]; $c++;} break;
   case '-get' : {$get=$args[$c+1]; $c++;} break;
   case '-extget' : {$extget=$args[$c+1]; $c++;} break;
   case '--include-path' : $includePathway=true; break;
   case '--verbose' : $verbose=true; break;
   case '--check-if-has-items' : $checkIfHasItems=true; break;
   case '--get-items-count' : $getItemsCount=true; break; // Get count of items into this category.
   case '--get-total-items-count' : $getTotalItemsCount=true; break; // Get count of all items into all tree of this category.
  }
 
 /* CHECK FOR ARCHIVE */
 if($extraVar)
  $archiveInfo = $extraVar;
 else
 {
  $ret = dynarc_checkForArchive($args,$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }

 $archives[$archiveInfo['id']] = $archiveInfo['prefix'];

 /* CHECK FOR PARENT */
 if($parentId)
 {
  $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -id ".$parentId,$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $parentInfo = $ret['outarr'];
 }
 else if($parentTag)
 {
  $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -tag '".$parentTag."'",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $parentInfo = $ret['outarr'];
  $parentId = $parentInfo['id'];
 }
 else if($into)
 {
  if(is_numeric($into))
   $intoId = $into;
  else
  {
   $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -tag '$into'",$sessid,$shellid,$extraVar);
   if($ret['error'])
    return $ret;
   $intoId = $ret['outarr']['id'];
  }
 }

 $db = new AlpaDatabase();
 if($tag) // retrieve the first category in the archive with tag $tag //
  $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_categories WHERE tag='".$tag."'"
	.($parentId ? " AND parent_id='".$parentId."'" : ($intoId ? " AND hierarchy LIKE '%,".$intoId.",%'" : ""))
	." AND trash='0' ORDER BY parent_id ASC LIMIT 1");
 else
  $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='$id'");
 if(!$db->Read())
  return array("message"=>"Category ".($id ? "#$id" : "with tag '$tag'")." does not exists!\n","error"=>"CATEGORY_DOES_NOT_EXISTS");

 /* CHECK PERMISSION TO READ */
 $sessInfo = sessionInfo($sessid);
 $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid']);
 if($sessInfo['uname'] != "root" && !$m->canRead($sessInfo['uid']))
  return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");

 /* OUTPUT */
 $a = array('id'=>$db->record['id'],'parent_id'=>$db->record['parent_id'],'name'=>$db->record['name'],
	'desc'=>$db->record['description'],'ctime'=>strtotime($db->record['ctime']),'ordering'=>$db->record['ordering'],
	'trash'=>$db->record['trash'],'published'=>$db->record['published'],'tag'=>$db->record['tag'],'def_order_field'=>$db->record['defordfield'],'def_order_method'=>($db->record['defordasc'] ? "ASC" : "DESC"),'hierarchy'=>$db->record['hierarchy']);
 $a['modinfo'] = $m->toArray($sessInfo['uid']);
 if(isset($db->record['code'])) $a['code'] = $db->record['code'];
 if(isset($db->record['syncid'])) $a['syncid'] = $db->record['syncid'];
 if($db->record['lnk_id'] != 0)
 {
  $a['link_id'] = $db->record['lnk_id'];
  $a['link_aid'] = $db->record['lnkarc_id'];
  if(!$archives[$db->record['lnkarc_id']])
  {
   /* Get archive prefix */
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE id='".$db->record['lnkarc_id']."'");
   $db2->Read();
   $archives[$db->record['lnkarc_id']] = $db2->record['tb_prefix'];
   $db2->Close();
  }
  $a['link_ap'] = $archives[$db->record['lnkarc_id']];
 }
 $db->Close();

  // OPT: --check-if-has-items
  if($checkIfHasItems)
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_items WHERE hierarchy LIKE '%,".$a['id'].",%' AND trash='0' LIMIT 1");
   if($db2->Read())
	$a['has_items'] = true;
   $db2->Close();
  }
  // OPT: --get-items-count
  if($getItemsCount)
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_items WHERE cat_id='".$a['id']."' AND trash='0'");
   $db2->Read();
   $a['items_count'] = $db2->record[0];
   $db2->Close();
  }
  // OPT: --get-total-items-count
  if($getTotalItemsCount)
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_items WHERE hierarchy LIKE '%,".$a['id'].",%' AND trash='0'");
   $db2->Read();
   $a['total_items_count'] = $db2->record[0];
   $db2->Close();
  }



 if($verbose)
 {
  $out = "Category info:\n";
  $out.= "ID: ".$a['id']."\n";
  if($a['parent_id'])
   $out.= "Parent ID: ".$a['parent_id']."\n";
  $out.= "Name: ".$a['name']."\n";
  $out.= "Description: ".$a['description']."\n";
  if($a['tag'])
   $out.= "Tag: ".$a['tag']."\n";
  $out.= "Published: ".($a['published'] ? "Yes\n" : "No\n");
  $mod = new GMOD($a['modinfo']['mod'],$a['modinfo']['uid'],$a['modinfo']['gid']);
  $out.= "Permissions: ".$mod->toString()."\n";
  if($a['trash'])
   $out.= "Note: this category is into trash\n";
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT fullname FROM gnujiko_users WHERE id='".$a['modinfo']['uid']."'");
  if($db->Read())
   $out.= "Created by ".$db->record['fullname']." at ".date('d/m/Y H:i',$a['ctime'])."\n";
  else
   $out.= "Created by unknown user at ".date('d/m/Y H:i',$a['ctime'])."\n";
  $db->Close();
 }

 // Include pathway //
 if($includePathway)
 {
  $pId = $a['parent_id'];
  $path = array();
  $db = new AlpaDatabase();
  while($pId > 0)
  {
   $db->RunQuery("SELECT id,parent_id,name FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='$pId'");
   $db->Read();
   $path[] = array('id'=>$pId,'name'=>$db->record['name']);
   $pId = $db->record['parent_id'];
  }
  $db->Close();
  $a['pathway'] = array_reverse($path);
 }

 if($get)
  $a = dynarc_parseCatGet($get,$sessid, $shellid, $archiveInfo, $a);

 if($extget)
 {
  $ret = dynarc_parseCatExtensionGet($extget,$sessid, $shellid, $archiveInfo, $a);
  if($ret)
   $a = $ret;
 }

 $outArr = $a;
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_categorySort($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-serialize' : {$serialize=$args[$c+1]; $c++;} break;
  }

 /* CHECK FOR ARCHIVE */
 if($extraVar)
  $archiveInfo = $extraVar;
 else
 {
  $ret = dynarc_checkForArchive($args,$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }

 // SERIALIZE //
 if($serialize)
 {
  $ser = explode(",",$serialize);
  if(!count($ser))
   return false;
  $db = new AlpaDatabase();
  for($c=0; $c < count($ser); $c++)
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET ordering='".($c+1)."' WHERE id='".$ser[$c]."'");
  $db->Close();
  $out.= "done!\n";
  return array("message"=>$out);
 }
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_categoryMove($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;
 $out = "";
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-after' : {$afterNode=$refNode=$args[$c+1]; $c++;} break;
   case '-before' : {$beforeNode=$refNode=$args[$c+1]; $c++;} break;
   case '-into' : case '-inside' : {$intoNode=$refNode=$args[$c+1]; $c++;} break;
  }

 /* CHECK FOR ARCHIVE */
 if($extraVar)
  $archiveInfo = $extraVar;
 else
 {
  $ret = dynarc_checkForArchive($args,$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }

 /* CHECK CATEGORY FOR INFO */
 $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -id $id",$sessid,$shellid,$archiveInfo);
 if($ret['error'])
  return $ret;
 $catInfo = $ret['outarr'];

 /* CHECK PERMISSIONS */
 $sessInfo = sessionInfo($sessid);
 if(($catInfo['modinfo']['uid'] != $sessInfo['uid']) && ($sessInfo['uname'] != 'root'))
  return array("message"=>"Only the owner can change category info!\n", "error"=>"PERMISSION_DENIED");

 /* GET EXTENSIONS */
 $extensions = array();
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."'");
 while($db->Read())
 {
  include_once($_BASE_PATH."etc/dynarc/extensions/".$db->record['extension_name']."/index.php");
  $extensions[] = $db->record['extension_name'];
 }
 $db->Close();

 if($refNode)
 {
  $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -id $refNode -get hierarchy",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $refInfo = $ret['outarr'];

  if($afterNode || $beforeNode)
  {
   if($refInfo['parent_id'] && ($refInfo['parent_id'] != $catInfo['parent_id']))
   {
    $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -id ".$refInfo['parent_id']." -get hierarchy",$sessid,$shellid,$archiveInfo);
    if($ret['error'])
     return $ret;
    /* CHECK FOR PERMISSIONS */
    if(!$ret['outarr']['modinfo']['can_write'])
     return array("message"=>"Permission denied!, you have not permission to insert sub-categories into this category.","error"=>"CATEGORY_PERMISSION_DENIED");
    $parentId = $ret['outarr']['id'];
	$hierarchy = $ret['outarr']['hierarchy'].$parentId.",";
   }
   else if($refInfo['parent_id'] == 0)
   {
	$parentId = 0;
	$hierarchy = ",";
   }
  }
  else
  {
   /* CHECK FOR PERMISSIONS */
   if(!$refInfo['modinfo']['can_write'])
	return array("message"=>"Permission denied!, you have not permission to insert sub-categories into this category.","error"=>"CATEGORY_PERMISSION_DENIED");
   $parentId = $refInfo['id'];
   $hierarchy = $refInfo['hierarchy'].$parentId.",";
  }
  
  /* UPDATE ORDERING */
  if($afterNode)
   $ordering = $refInfo['ordering']+1;
  else if($beforeNode)
   $ordering = $refInfo['ordering'];
  else if($intoNode)
   $ordering = 0;
  $ord = $ordering;

  if($afterNode || $beforeNode)
  {
   $db = new AlpaDatabase(); $db2 = new AlpaDatabase();
   $db->RunQuery("SELECT id,ordering FROM dynarc_".$archiveInfo['prefix']."_categories WHERE ".(isset($parentId) ? "parent_id='$parentId' AND " : "")."ordering>=$ordering ORDER BY ordering ASC");
   while($db->Read())
   {
    $ord++;
    $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET ordering='$ord' WHERE id='".$db->record['id']."'");
   }
   $db2->Close(); $db->Close();
  }

  $qry = "UPDATE dynarc_".$archiveInfo['prefix']."_categories SET ordering='$ordering'";
  if(isset($parentId))
  {
   $qry.= ",parent_id='$parentId'";
   if($archiveInfo['sync_enabled'])
   {
    $db = new AlpaDatabase();
    $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET status='MOVED',logtime='".date('Y-m-d H:i:s')."' WHERE syncid='".$catInfo['syncid']."'");
    $db->Close();
   }
   // call onmovecategory function if exists //
   if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
   {
    $oldCatInfo = $catInfo;
    $newCatInfo = $catInfo;
    $newCatInfo['hierarchy'] = $hierarchy;
    $newCatInfo['parent_id'] = $parentId;

    include_once($_BASE_PATH.$archiveInfo['functionsfile']);
    if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_onmovecategory",true))
     call_user_func("dynarcfunction_".$archiveInfo['prefix']."_onmovecategory", $args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo);
   }
  }
  /* call onmovecategory function for all installed extensions */
  for($i=0; $i < count($extensions); $i++)
  {
   /* EXECUTE FUNCTION */
   if(is_callable("dynarcextension_".$extensions[$i]."_onmovecategory",true))
   {
    $ret = call_user_func("dynarcextension_".$extensions[$i]."_onmovecategory", $args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo);
    if($ret['error'])
     return $ret;
   }
  }

  if($hierarchy)
   $qry.= ",hierarchy='$hierarchy'";
  $qry.= " WHERE id='".$catInfo['id']."'";
  $db = new AlpaDatabase();
  $db->RunQuery($qry);
  $db->Close();
  $out.= "done!\n";
 }
 else
  return array("message"=>"You must specify where move the node. (with: -before CATID | -after CATID | -into CATID)\n","error"=>"INVALID_ARGUMENTS");
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_categoryCopy($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;
 $out = "";
 $ids = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-id' : {$ids[]=$args[$c+1]; $c++;} break;
   case '-parent' : {$parent=$args[$c+1]; $c++;} break;
   case '-pt' : {$parentTag=$args[$c+1]; $c++;} break;
   case '-set' : {$set=$args[$c+1]; $c++;} break;
   case '-extset' : {$extSet=$args[$c+1]; $c++;} break;
  }

 /* CHECK FOR ARCHIVE */
 if($extraVar)
  $archiveInfo = $extraVar;
 else
 {
  $ret = dynarc_checkForArchive($args,$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }

 /* CHECK PARENT FOR INFO */
 if($parent || $parentTag)
 {
  $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."'".($parent ? " -id $parent" : " -tag `$parentTag`")." -get hierarchy",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $parentInfo = $ret['outarr'];
 
  /* CHECK PERMISSIONS */
  $sessInfo = sessionInfo($sessid);
  if(!$parentInfo['modinfo']['can_write'])
   return array("message"=>"Permission denied.Parent category is not writeable. !\n", "error"=>"PERMISSION_DENIED");
 }

 /* GET EXTENSIONS */
 $extensions = array();
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."'");
 while($db->Read())
 {
  include_once($_BASE_PATH."etc/dynarc/extensions/".$db->record['extension_name']."/index.php");
  $extensions[] = $db->record['extension_name'];
 }
 $db->Close();

 $itmCount = 0;
 $catCount = 0;

 for($c=0; $c < count($ids); $c++)
 {
  /* Get source category */
  $ret = GShell("dynarc cat-info -ap `".$archiveInfo['prefix']."` -id `".$ids[$c]."`", $sessid, $shellid);
  if($ret['error']) continue;
  $catInfo = $ret['outarr'];

  /* Create category clone */
  $ret = GShell("dynarc new-cat -ap `".$archiveInfo['prefix']."` -name `".$catInfo['name']."` -code `".$catInfo['code']."` -desc `".$catInfo['desc']."` -tag `"
	.$catInfo['tag']."`".($parentInfo ? " -parent `".$parentInfo['id']."`" : "").($catInfo['published'] ? " --publish" : "")
	.($set ? " -set `".$set."`" : "").($extSet ? " -extset `".$extSet."`" : ""),$sessid, $shellid);
  if($ret['error']) continue;
  $cloneInfo = $ret['outarr'];
  $catCount++;
  $cloneInfo['items'] = array();
  $cloneInfo['categories'] = array();

  /* Copy all extensions */
  for($i=0; $i < count($extensions); $i++)
  {
   /* EXECUTE FUNCTION */
   if(is_callable("dynarcextension_".$extensions[$i]."_oncopycategory",true))
   {
    $ret = call_user_func("dynarcextension_".$extensions[$i]."_oncopycategory", $sessid, $shellid, $archiveInfo, $catInfo, $cloneInfo);
    if($ret['error'])
     return $ret;
   }
  }

  /* Copy all items */
  $ret = GShell("dynarc item-list -ap `".$archiveInfo['prefix']."` -cat `".$catInfo['id']."`",$sessid,$shellid);
  if($ret['error']) continue;
  $list = $ret['outarr']['items'];
  $q = "";
  for($i=0; $i < count($list); $i++)
   $q.= " -id ".$list[$i]['id'];
  if($q != "")
  {
   $ret = GShell("dynarc item-copy -ap `".$archiveInfo['prefix']."` -cat `".$cloneInfo['id']."`".$q,$sessid,$shellid);
   if(!$ret['error'])
   {
	$itmCount++;
	for($x=0; $x < count($ret['outarr']); $x++)
	 $cloneInfo['items'][] = $ret['outarr'][$x];
   }
  }

  /* Copy all subcategories */
  $ret = GShell("dynarc cat-list -ap `".$archiveInfo['prefix']."` -parent `".$catInfo['id']."`",$sessid,$shellid);
  if($ret['error'])
   continue;
  $list = $ret['outarr'];
  $q = "";  
  for($i=0; $i < count($list); $i++)
   $q.= " -id ".$list[$i]['id'];
  if($q != "")
  {
   $ret = GShell("dynarc cat-copy -ap `".$archiveInfo['prefix']."` -parent `".$cloneInfo['id']."`".$q,$sessid,$shellid);
   if(!$ret['error'])
   {
    $catCount++;
	for($x=0; $x < count($ret['outarr']); $x++)
	 $cloneInfo['categories'][] = $ret['outarr'][$x];
   }
  }
 }

 $out.= $itmCount." items and ".$catCount." categories has been copied!";
 return array("message"=>$out, "outarr"=>$cloneInfo);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_categoryTree($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;
 $orderBy = "ordering ASC";
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '--serialize-only' : $serializeOnly = true; break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
   case '-get' : {$get=$args[$c+1]; $c++;} break;
  }

 /* CHECK FOR ARCHIVE */
 if($extraVar)
  $archiveInfo = $extraVar;
 else
 {
  $ret = dynarc_checkForArchive($args,$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 if($catTag)
 {
  $ret = GShell("dynarc cat-info -aid '".$archiveInfo['id']."' -tag '".$catTag."'",$sessid,$shellid,$extraVar);
  if($ret['error'])
   return $ret;
  $catId = $ret['outarr']['id'];
 }
 
 $m = new GMOD();
 $uQry = $m->userQuery($sessid,null,"dynarc_".$archiveInfo['prefix']."_categories");
 $sessInfo = sessionInfo($sessid);

 $db = new AlpaDatabase();
 if($serializeOnly)
  $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_categories WHERE ($uQry) AND parent_id='".$catId."' AND trash='0' ORDER BY ".$orderBy);
 else
  $db->RunQuery("SELECT id,name".($get ? ",".$get : "")." FROM dynarc_".$archiveInfo['prefix']."_categories WHERE ($uQry) AND parent_id='".$catId."' AND trash='0' ORDER BY ".$orderBy);
 $ser = "";
 while($db->Read())
 {
  if($serializeOnly)
  {
   $ser.= ",".$db->record['id'];
   $ser.= _dynarc_getSubCat($sessInfo,$db->record['id'],$archiveInfo['prefix'],$orderBy,true);
  }
  else
  {
   $catInfo = array('id'=>$db->record['id'],'parent_id'=>0,'name'=>$db->record['name']);
   if($get)
   {
	$x = explode(",",$get);
	for($i=0; $i < count($x); $i++)
	 $catInfo[$x[$i]] = $db->record[$x[$i]];
   }
   $catInfo['subcategories'] = _dynarc_getSubCat($sessInfo,$db->record['id'],$archiveInfo['prefix'],$orderBy,false,$get);
   $outArr[] = $catInfo;
  }
 }
 $db->Close();
 if($serializeOnly)
  $outArr['serialize'] = ltrim($ser,",");
 $out.= count($outArr)." categories found.\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function _dynarc_getSubCat($sessInfo,$catId,$prefix,$orderBy,$serializeOnly=false,$get="")
{
 global $_BASE_PATH;
 $ret = array();
 $ser = "";
 $m = new GMOD();

 $db = new AlpaDatabase();
 if($serializeOnly)
  $db->RunQuery("SELECT id,uid,gid,_mod FROM dynarc_".$prefix."_categories WHERE parent_id='$catId' AND trash='0' ORDER BY $orderBy");
 else
  $db->RunQuery("SELECT id,uid,gid,_mod,name,description,published".($get ? ",".$get : "")." FROM dynarc_".$prefix."_categories WHERE parent_id='$catId' AND trash='0' ORDER BY $orderBy");
 while($db->Read())
 {
  $m->set($db->record['_mod'],$db->record['uid'],$db->record['gid']);
  if(!$m->canRead($sessInfo['uid']))
   continue;
  if($serializeOnly)
  {
   $ser.= ",".$db->record['id'];
   $ser.= _dynarc_getSubCat($sessInfo,$db->record['id'],$prefix,$orderBy,true);
  }
  else
  {
   $info = array('id'=>$db->record['id'],'name'=>$db->record['name'],'desc'=>$db->record['description'],'parent_id'=>$catId,'tag'=>$db->record['tag']);
   if($get)
   {
	$x = explode(",",$get);
	for($i=0; $i < count($x); $i++)
	 $info[$x[$i]] = $db->record[$x[$i]];
   }
   $info['subcategories'] = _dynarc_getSubCat($sessInfo,$db->record['id'],$prefix,$orderBy,false,$get);
   $ret[] = $info;
  }
 }
 $db->Close();
 if($serializeOnly)
  return $ser;
 else
  return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_parseCatExtensionSet($action,$set,$sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 //*** PARSER ***//
 $set = rtrim($set,",").",";
 $extensions = array();
 $activeExtension = null;
 $var = "";
 $value = "";
 $s = "";
 $oq = false;
 for($i=0; $i < strlen($set); $i++)
 {
  if(strlen($set) > ($i+3))
  {
   $sss = substr($set,$i,3);
   if($sss == "'''")
   {
    if($oq == $sss)
	{
	 $value = $s;
	 $oq = false;
	 $s = "";
	 $i+=2;
	 continue;
	}
	else if(!$oq)
	{
	 $oq = "'''";
	 $i+=2;
	 continue;
	}
   }
  }

  switch($set[$i])
  {
   case '.' : {
	  if($oq)
	   $s.= $set[$i];
	  else
	  {
	   $activeExtension = $s;
	   $s = "";
	  }
	 } break;
   case '\'' : case '"' : {
	  if($oq == $set[$i])
	  {
	   $value = $s;
	   $oq = false;
	   $s = "";
	  }
	  else if(!$oq)
	   $oq = $set[$i];
	  else
	   $s.= $set[$i];
	 } break;
   case '=' : {
	  if($oq)
	   $s.= $set[$i];
	  else
	  {
	   $var = $s;
	   $value = null;
	   $s = "";
	  }
	 } break;
   case ',' : {
	  if($oq)
	   $s.= $set[$i];
	  else
	  {
	   if(!$value)
	    $value = $s;
	   if(!$extensions[$activeExtension])
		$extensions[$activeExtension] = array();
	   $extensions[$activeExtension][] = array($var,$value);
	   $s = "";
	  }
	 } break;
   default: $s.= $set[$i];
  }
 }
 // EOF PARSER //
 while(list($extension,$arr)=each($extensions))
 {
  if(!file_exists($_BASE_PATH."etc/dynarc/extensions/".$extension."/index.php"))
  {
   $out.= "Extension $extension does not exists!\n";
   continue;
  }

  $args = array();
  for($c=0; $c < count($arr); $c++)
  {
   list($k,$v) = $arr[$c];
   $args[] = $k;
   $args[] = $v;
  }
  $e = $o = "";

  /* EXECUTE FUNCTION */
  include_once($_BASE_PATH."etc/dynarc/extensions/".$extension."/index.php");
  if(is_callable("dynarcextension_".$extension."_".$action,true))
  {
   $ret = call_user_func("dynarcextension_".$extension."_".$action, $args, $sessid, $shellid, $archiveInfo, $catInfo,true);
   if($ret['error'])
	return $ret;
   else if(is_array($ret))
	$catInfo = $ret;
  }
  else
  {
   $out.= "Cannot execute action $action for extension $extension!\n";
   continue;
  }
  $out.= $ret['message'];
 }
 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_parseCatExtensionGet($get,$sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 //*** PARSER ***//
 $x = explode(",",$get);
 $activeExtension = null;
 $extensions = array();
 for($c=0; $c < count($x); $c++)
 {
  $p = strpos(ltrim($x[$c]),'.');
  if($p === false)
  {
   $activeExtension = $x[$c];
   if(!$extensions[$activeExtension])
    $extensions[$activeExtension] = array();
  }
  else if($p == 0)
  {
   if(!$activeExtension)
    continue;
   $extensions[$activeExtension][] = ltrim($x[$c],".");
  }
  else
  {
   $xx = explode(".",$x[$c]);
   $activeExtension = $xx[0];
   if(!$extensions[$activeExtension])
    $extensions[$activeExtension] = array();
   $extensions[$activeExtension][] = $xx[1];
  }
 }
 // EOF PARSER //
 while(list($extension,$arr)=each($extensions))
 {
  if(!file_exists($_BASE_PATH."etc/dynarc/extensions/".$extension."/index.php"))
   continue;
  $args = array();
  for($c=0; $c < count($arr); $c++)
   $args[] = $arr[$c];

  /* EXECUTE FUNCTION */
  include_once($_BASE_PATH."etc/dynarc/extensions/".$extension."/index.php");
  if(is_callable("dynarcextension_".$extension."_get",true))
  {
   $ret = call_user_func("dynarcextension_".$extension."_get", $args, $sessid, $shellid, $archiveInfo, $catInfo, true);
   if($ret)
	$catInfo = $ret;
  }
 }
 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_parseCatSet($action,$set,$sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;
 $db = new AlpaDatabase();
 //*** PARSER ***//
 $set = rtrim($set,",").",";
 $var = "";
 $value = "";
 $s = "";
 $oq = false;
 $q = "";
 for($i=0; $i < strlen($set); $i++)
 {
  if(strlen($set) > ($i+3))
  {
   $sss = substr($set,$i,3);
   if($sss == "'''")
   {
    if($oq == $sss)
	{
	 $value = $s;
	 $oq = false;
	 $s = "";
	 $i+=2;
	 continue;
	}
	else if(!$oq)
	{
	 $oq = "'''";
	 $i+=2;
	 continue;
	}
   }
  }

  switch($set[$i])
  {
   case '\'' : case '"' : {
	  if($oq == $set[$i])
	  {
	   $value = $s;
	   $oq = false;
	   $s = "";
	  }
	  else if(!$oq)
	   $oq = $set[$i];
	  else
	   $s.= $set[$i];
	 } break;
   case '=' : {
	  if($oq)
	   $s.= $set[$i];
	  else
	  {
	   $var = $s;
	   $value = null;
	   $s = "";
	  }
	 } break;
   case ',' : {
	  if($oq)
	   $s.= $set[$i];
	  else
	  {
	   if(!$value)
	    $value = $s;
	   $q.= ",".$var."='".$db->Purify($value)."'";
	   $catInfo[$var] = $value;
	   $s = "";
	  }
	 } break;
   default: $s.= $set[$i];
  }
 }
 // EOF PARSER //
 

 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET ".ltrim($q,",")." WHERE id='".$catInfo['id']."'");
 $db->Close();

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_parseCatGet($get,$sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;
 $x = explode(",",$get);
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT $get FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$catInfo['id']."'");
 if($db->Read())
 {
  for($c=0; $c < count($x); $c++)
   $catInfo[$x[$c]] = $db->record[$x[$c]];
 }
 $db->Close();
 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function increase_catcode($code)
{
 global $_BASE_PATH;
 $numcode = 0;
 for($c=0; $c < strlen($code); $c++)
 {
  if(ctype_digit(substr($code,-($c+1))))
   $numcode = substr($code,-($c+1));
  else
   break;
 }
 $nextcode = (float)$numcode + 1;
 $nextcode = sprintf("%0".$c."s",   (string)$nextcode);
 return substr($code,0, strlen($code)-$c).$nextcode;
}
//-------------------------------------------------------------------------------------------------------------------//

