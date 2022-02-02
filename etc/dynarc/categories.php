<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-05-2017
 #PACKAGE: dynarc
 #DESCRIPTION: Category functions for Dynarc
 #VERSION: 2.24beta
 #CHANGELOG: 23-05-2017 : Bug fix sharing.
			 24-10-2016 : MySQLi integration.
			 01-03-2016 : Aggiornata funzione delete category ed aggiornata funzione categoryTree
			 07-12-2015 : Aggiunto parametro --get-count su funzione categoryList.
			 25-07-2014 : Bug fix vari.
			 01-07-2014 : Bug fix e aggiunto paramentro -linkap -linkid su funzione deleteCategory
			 20-06-2014 : Aggiunto parametro --if-not-exists e --update-if-exists su funzione new-cat.
			 16-06-2014 : Aggiunti parametri linkap e linkid su funzione newCategory
			 23-05-2014 : Aggiunta funzione getRootCategory
			 01-05-2014 : Aggiunta la possibilità di ricercare una categoria per codice.
			 30-01-2014 : Aggiunta la possibilità di forzare l'ID su comando new-cat
			 17-09-2013 : Aggiunto parent-id and parent-tag nella funzione cat-info.
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

function dynarc_newCategory($args, $sessid, $shellid, $extraVar=null)
{
 global $_BASE_PATH;

 $_IF_NOT_EXISTS = false;
 $_UPDATE_IF_EXISTS = false;
 $_INHERIT_PARENT_SHARING = false;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-id' : case '--force-id' : {$forceId=$args[$c+1]; $c++;} break;
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
   case '-alias' : case '-aliasname' : {$aliasname=$args[$c+1]; $c++;} break;

   /* Default key ordering */
   case '--def-order-field' : {$setOrderKey=$args[$c+1]; $c++;} break;
   case '--def-order-method' : {$setOrderMethod=strtoupper($args[$c+1]); $c++;} break;

   /* Link arguments */
   case '-linkap' : {$linkAP=$args[$c+1]; $c++;} break;
   case '-linkaid' : {$linkAID=$args[$c+1]; $c++;} break;
   case '-linkid' : {$linkID=$args[$c+1]; $c++;} break;

   case '-set' : {$set=$args[$c+1]; $c++;} break;
   case '-extset' : {$extset=$args[$c+1]; $c++;} break;

   case '-before' : {$beforeId=$args[$c+1]; $c++;} break; // <-- Insert category before this node */
   case '-after' : {$afterId=$args[$c+1]; $c++;} break; // <-- Insert category after this node */

   case '--if-not-exists' : $_IF_NOT_EXISTS=true; break;	// Instead of returning the error returns the details of the category if it already exists.
   case '--update-if-exists' : $_UPDATE_IF_EXISTS=true; break;	// for use with --if-not-exists, update category info if it already exists.

   case '--inherit-parent-sharing' : $_INHERIT_PARENT_SHARING = true; break;

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
  $ret = dynarc_checkForArchive($args,$sessid,$shellid,$extraVar);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }


 if(!isset($name))
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

 if($linkID)
 {
  if($linkAP)
  {
   if($linkAP == $archiveInfo['prefix'])
	$linkAID = $archiveInfo['id'];
   else
   {
	$db = new AlpaDatabase();
	$db->RunQuery("SELECT id FROM dynarc_archives WHERE tb_prefix='".$db->Purify($linkAP)."'");
	if($db->Read())
	 $linkAID = $db->record['id'];
	$db->Close();
   }
  }
  else if(!$linkAID)
   $linkAID = $archiveInfo['id'];
 }

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

 if($_IF_NOT_EXISTS)
 {
  /* CHECK IF CATEGORY ALREADY EXISTS */
  $cmd = "dynarc cat-info -ap '".$archiveInfo['prefix']."'";
  if($parentInfo)	$cmd.= " -parent `".$parentInfo['id']."`";
  if($tag)			$cmd.= " -tag `".$tag."`";
  else if($code)	$cmd.= " -code `".$code."`";
  $ret = GShell($cmd,$sessid,$shellid);
  if(!$ret['error'])
  {
   $out.= "category already exists! ID=".$ret['outarr']['id']."\n";
   $ret['message'] = $out;
   if($_UPDATE_IF_EXISTS)
   {
	$args[] = "-id";
	$args[] = $ret['outarr']['id'];
	return dynarc_editCategory($args, $sessid, $shellid, $extraVar ? $extraVar : $archiveInfo);
   }
   else
    return $ret;
  }
 }


 $out.= "Creating category...";
 $db = new AlpaDatabase();

 $qry = "INSERT INTO dynarc_".$archiveInfo['prefix']."_categories(";
 if($forceId)					$qry.= "id,";
 $qry.= "uid,gid,_mod,parent_id,lnk_id,lnkarc_id,tag,code,name,description,ordering,ctime,published,hierarchy,defordfield,defordasc";
 if(isset($aliasname))			$qry.= ",aliasname";
 if($_INHERIT_PARENT_SHARING && $parentInfo && ($parentInfo['shgrps'] || $parentInfo['shusrs']))
  $qry.= ",shgrps,shusrs";

 $qry.= ") VALUES(";

 if($forceId)					$qry.= "'".$forceId."',";
 $qry.= "'".$uid."','".$gid."','".$mod."','".($parentInfo ? $parentInfo['id'] : 0)."','"
	.$linkID."','".$linkAID."','".$tag."','".$code."','".$db->Purify($name)."','".$db->Purify($desc)."','".$ord."','"
	.date('Y-m-d H:i:s',$now)."','".$published."','".($parentInfo ? $parentInfo['hierarchy'].$parentInfo['id']."," : ",")."','"
	.$setOrderKey."','".$setOrderMethod."'";
 if(isset($aliasname))			$qry.= ",'".$db->Purify($aliasname)."'";
 if($_INHERIT_PARENT_SHARING && $parentInfo && ($parentInfo['shgrps'] || $parentInfo['shusrs']))
  $qry.= ",'".$parentInfo['shgrps']."','".$parentInfo['shusrs']."'";
 $qry.= ")";
 

 /*$db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_categories(".($forceId ? "id," : "")."uid,gid,_mod,parent_id,lnk_id,lnkarc_id,tag,code,
	name,description,ordering,ctime,published,hierarchy,defordfield,defordasc".(isset($aliasname) ? ",aliasname" : "").") VALUES("
	.($forceId ? "'".$forceId."'," : "")."'".$uid."','".$gid."','".$mod."','".($parentInfo ? $parentInfo['id'] : 0)."','"
	.$linkID."','".$linkAID."','".$tag."','".$code."','".$db->Purify($name)."','".$db->Purify($desc)."','".$ord."','"
	.date('Y-m-d H:i:s',$now)."','".$published."','".($parentInfo ? $parentInfo['hierarchy'].$parentInfo['id']."," : ",")."','"
	.$setOrderKey."','".$setOrderMethod."'".(isset($aliasname) ? ",'".$db->Purify($aliasname)."'" : "").")");*/
 $db->RunQuery($qry);
 if($db->Error) return array('message'=>$out."failed!\nMySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, 'error'=>'MYSQL_ERROR');
 $id = $db->GetInsertId();

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

 // call oncreatecategory function if exists //
 if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
 {
  include_once($_BASE_PATH.$archiveInfo['functionsfile']);
  if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_oncreatecategory",true))
  {
   $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_oncreatecategory", $args, $sessid, $shellid, $archiveInfo, $a);
   if(is_array($ret) && $ret['error']) return $ret;
   else if(is_array($ret) && $ret['outarr']) $outArr = $ret['outarr'];
  }
 }
 else if($archiveInfo['inherit'])
 {
  // call oncreatecategory function inherited if exists //
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
  $db->Read();
  if($db->record['fun_file'] && file_exists($_BASE_PATH.$db->record['fun_file']))
  {
   include_once($_BASE_PATH.$db->record['fun_file']);
   if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_oncreatecategory",true))
   {
    $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_oncreatecategory", $args, $sessid, $shellid, $archiveInfo, $a);
    if(is_array($ret) && $ret['error']) return $ret;
    else if(is_array($ret) && $ret['outarr']) $outArr = $ret['outarr'];
   }
  }
  $db->Close();
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
   if(is_array($ret) && $ret['error']) return $ret;
  }
 }
 $db->Close();

 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_editCategory($args, $sessid, $shellid, $extraVar=null)
{
 global $_BASE_PATH;
 $shareSetRecursive=false;

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
   case '-alias' : case '-aliasname' : {$aliasname=$args[$c+1]; $c++;} break;

   /* Default key ordering */
   case '--def-order-field' : {$setOrderKey=$args[$c+1]; $c++;} break;
   case '--def-order-method' : {$setOrderMethod=strtoupper($args[$c+1]); $c++;} break;

   /* SHARING */
   case '--share-add-group' : {$shGroups[]=$args[$c+1]; $c++;} break; // divide group and perms with(:) example: --share-add-group mygroup:6
   case '--share-add-user' : {$shUsers[]=$args[$c+1]; $c++;} break; // divide user and perms with(:) example: --share-add-user alex:4
   case '--unshare-group' : {$unshGroups[]=$args[$c+1]; $c++;} break;
   case '--unshare-user' : {$unshUsers[]=$args[$c+1]; $c++;} break;
   case '--share-set-recursive' : $shareSetRecursive=true; break;

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
  $ret = dynarc_checkForArchive($args,$sessid,$shellid,$extraVar);
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
  if($parentInfo['id'] == $catInfo['id'])
   return array("message"=>"You cannot insert this category in the same category.", "error"=>"INVALID_PARENT_ID");
  /* CHECK FOR PERMISSIONS */
  if(!$parentInfo['modinfo']['can_write'])
   return array("message"=>"Permission denied!, you have not permission to insert sub-categories into this category.","error"=>"CATEGORY_PERMISSION_DENIED");
 }

 /* UPDATE GROUPS SHARE INFO */
 $shareGroups = array();
 $shgrps = "";
 $shusrs = "";
 if(count($shGroups) || count($shUsers) || count($unshGroups) || count($unshUsers))
 {
  // Get category sharing info.
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT shgrps,shusrs FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$catInfo['id']."'");
  if($db->Read())
  {
   $catInfo['shgrps'] = $db->record['shgrps'];
   $catInfo['shusrs'] = $db->record['shusrs'];
  }
  $db->Close();
 }
 if(count($shGroups))
 {
  for($c=0; $c < count($shGroups); $c++)
  {
   $p = strrpos($shGroups[$c],":");
   $shMOD = substr($archiveInfo['def_cat_perms'],1,1);
   if($p === false)	$shGID = is_numeric($shGroups[$c]) ? $shGroups[$c] : _getGID($shGroups[$c]);
   else { $tmp = substr($shGroups[$c],0,$p); $shGID = is_numeric($tmp) ? $tmp : _getGID($tmp); $shMOD = substr($shGroups[$c],$p+1); }
   $shareGroups[$shGID] = $shMOD;
  }
 }
 $unshareGroups = array();
 if(count($unshGroups))
 {
  for($c=0; $c < count($unshGroups); $c++)
   $unshareGroups[] = is_numeric($unshGroups[$c]) ? $unshGroups[$c] : _getGID($unshGroups[$c]);
 }
 if(count($shareGroups) || count($unshareGroups))
 {
  $m = new GMOD(null,null,null,$catInfo['shgrps']);
  for($c=0; $c < count($unshareGroups); $c++)
  {
   if($m->SHGROUPS[$unshareGroups[$c]])
	unset($m->SHGROUPS[$unshareGroups[$c]]);
  }
  reset($shareGroups);
  while(list($k,$v) = each($shareGroups)) { $m->SHGROUPS[$k] = $v; }
  $shgrps = "#,";
  reset($m->SHGROUPS);
  while(list($k,$v) = each($m->SHGROUPS)) { $shgrps.= $k."=".$v.","; }
  $shgrps.= "#";
 }

 /* UPDATE USERS SHARE INFO */
 $shareUsers = array();
 if(count($shUsers))
 {
  for($c=0; $c < count($shUsers); $c++)
  {
   $p = strrpos($shUsers[$c],":");
   $shMOD = substr($archiveInfo['def_cat_perms'],0,1);
   if($p === false) $shUID = is_numeric($shUsers[$c]) ? $shUsers[$c] : _getUID($shUsers[$c]);
   else { $tmp = substr($shUsers[$c],0,$p); $shUID = is_numeric($tmp) ? $tmp : _getUID($tmp); $shMOD = substr($shUsers[$c],$p+1); }
   $shareUsers[$shUID] = $shMOD;
  }
 }
 $unshareUsers = array();
 if(count($unshUsers))
 {
  for($c=0; $c < count($unshUsers); $c++)
   $unshareUsers[] = is_numeric($unshUsers[$c]) ? $unshUsers[$c] : _getUID($unshUsers[$c]);
 }
 if(count($shareUsers) || count($unshareUsers))
 {
  $m = new GMOD(null,null,null,null,$catInfo['shusrs']);
  for($c=0; $c < count($unshareUsers); $c++)
  {
   if($m->SHUSERS[$unshareUsers[$c]])
	unset($m->SHUSERS[$unshareUsers[$c]]);
  }
  reset($shareUsers);
  while(list($k,$v) = each($shareUsers)) { $m->SHUSERS[$k] = $v; }
  $shusrs = "#,";
  reset($m->SHUSERS);
  while(list($k,$v) = each($m->SHUSERS)) { $shusrs.= $k."=".$v.","; }
  $shusrs.= "#";
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
 else if(isset($parentId) && ($parentId == 0))
 {
  $q.= ",parent_id='0',hierarchy=','";
  $a['parent_id'] = 0;
  if($catInfo['parent_id'] != 0)
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
 if(isset($aliasname))
  $q.= ",aliasname='".$db->Purify($aliasname)."'";

 if($group)
  $groupId = _getGID($group);

 if($groupId)
 {
  if(($catInfo['modinfo']['uid'] == $sessInfo['uid']) || ($sessInfo['uname'] == 'root'))
   $q.= ",gid='$groupId'";
 }

 if($shgrps) $q.= ",shgrps='".$shgrps."'";
 if($shusrs) $q.= ",shusrs='".$shusrs."'";

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET ".ltrim($q,",")." WHERE id='".$catInfo['id']."'");
 if($db->Error) return array('message'=>$out."failed!\nMySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, 'error'=>'MYSQL_ERROR');
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

 // call oneditcategory function if exists //
 if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
 {
  include_once($_BASE_PATH.$archiveInfo['functionsfile']);
  if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_oneditcategory",true))
  {
   $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_oneditcategory", $args, $sessid, $shellid, $archiveInfo, $a);
   if(is_array($ret) && $ret['error']) return $ret;
   else if(is_array($ret) && $ret['outarr']) $outArr = $ret['outarr'];
   else if(is_array($ret)) $outArr = $ret;
  }
 }
 else if($archiveInfo['inherit'])
 {
  // call oneditcategory function inherited if exists //
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
  $db->Read();
  if($db->record['fun_file'] && file_exists($_BASE_PATH.$db->record['fun_file']))
  {
   include_once($_BASE_PATH.$db->record['fun_file']);
   if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_oneditcategory",true))
   {
    $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_oneditcategory", $args, $sessid, $shellid, $archiveInfo, $a);
    if(is_array($ret) && $ret['error']) return $ret;
    else if(is_array($ret) && $ret['outarr']) $outArr = $ret['outarr'];
    else if(is_array($ret)) $outArr = $ret;
   }
  }
  $db->Close();
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
   if(is_array($ret) && $ret['error']) return $ret;
  }
 }
 $db->Close();

 // SHARE SET RECURSIVE
 if($shareSetRecursive)
 {
  $ret = GShell("dynarc cat-tree -ap '".$archiveInfo['prefix']."' -cat '".$catInfo['id']."' --serialize-only", $sessid, $shellid);
  if(!$ret['error'] && $ret['outarr']['serialize'])
  {
   $ser = explode(",",$ret['outarr']['serialize']);
   $db = new AlpaDatabase();
   for($c=0; $c < count($ser); $c++)
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET shgrps='".$shgrps."',shusrs='".$shusrs."' WHERE id='".$ser[$c]."'");
   $db->Close();
  }
 }

 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_deleteCategory($args, $sessid, $shellid, $extraVar=null)
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
   case '-linkap' : {$linkAP=$args[$c+1]; $c++;} break;
   case '-linkaid' : {$linkAID=$args[$c+1]; $c++;} break;
   case '-linkid' : {$linkID=$args[$c+1]; $c++;} break;
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
  $ret = dynarc_checkForArchive($args,$sessid,$shellid,$extraVar);
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
 else if($linkID && ($linkAP || $linkAID))
 {
  $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -linkid '".$linkID."'"
	.($linkAP ? " -linkap '".$linkAP."'" : " -linkaid '".$linkAID."'"),$sessid,$shellid,$archiveInfo);
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

   // call ondeletecategory function if exists //
   if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
   {
    include_once($_BASE_PATH.$archiveInfo['functionsfile']);
    if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_ondeletecategory",true))
    {
	 $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_ondeletecategory", $args, $sessid, $shellid, $archiveInfo, $catInfo);
	 if(is_array($ret) && $ret['error']) return $ret;
    }
   }
   else if($archiveInfo['inherit'])
   {
    // call ondeletecategory function inherited if exists //
    $db = new AlpaDatabase();
    $db->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
    $db->Read();
    if($db->record['fun_file'] && file_exists($_BASE_PATH.$db->record['fun_file']))
    {
     include_once($_BASE_PATH.$db->record['fun_file']);
     if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_ondeletecategory",true))
     {
      $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_ondeletecategory", $args, $sessid, $shellid, $archiveInfo, $catInfo);
      if(is_array($ret) && $ret['error']) return $ret;
     }
    }
    $db->Close();
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
	 if(is_array($ret) && $ret['error']) return $ret;
	}
   }
   $db->Close();


   $out.= "category #".$catInfo['id']." has been removed!\n";
   if($returnCatInfo)
	$outArr['removed'][] = $catInfo;
  }
  else
  {
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

   // call ontrashcategory function if exists //
   if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
   {
    include_once($_BASE_PATH.$archiveInfo['functionsfile']);
    if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_ontrashcategory",true))
    {
	 $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_ontrashcategory", $args, $sessid, $shellid, $archiveInfo, $catInfo);
	 if(is_array($ret) && $ret['error']) return $ret;
    }
   }
   else if($archiveInfo['inherit'])
   {
    // call ontrashcategory function inherited if exists //
    $db = new AlpaDatabase();
    $db->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
    $db->Read();
    if($db->record['fun_file'] && file_exists($_BASE_PATH.$db->record['fun_file']))
    {
     include_once($_BASE_PATH.$db->record['fun_file']);
     if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_ontrashcategory",true))
     {
      $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_ontrashcategory", $args, $sessid, $shellid, $archiveInfo, $catInfo);
      if(is_array($ret) && $ret['error']) return $ret;
     }
    }
    $db->Close();
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
	 if(is_array($ret) && $ret['error']) return $ret;
	}
   }
   $db->Close();

   $out.= "category #".$catInfo['id']." has ben trashed!\n";
   if($returnCatInfo)
	$outArr['trashed'][] = $catInfo;
  }
 }
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_categoryList($args, $sessid, $shellid, $extraVar=null)
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
   case '--get-count' : $getCount=true; break;	// Get count of categories
   case '--get-items-count' : $getItemsCount=true; break; // Get count of items into this category.
   case '--get-total-items-count' : $getTotalItemsCount=true; break; // Get count of all items into all tree of this category.
  }

 /* CHECK FOR ARCHIVE */
 if($extraVar)
  $archiveInfo = $extraVar;
 else
 {
  $ret = dynarc_checkForArchive($args,$sessid,$shellid,$extraVar);
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
 if($getCount)
  $outArr = array('count'=>0, 'categories'=>array());

 $m = new GMOD();
 $uQry = $m->userQuery($sessid,null,"dynarc_".$archiveInfo['prefix']."_categories");

 $db = new AlpaDatabase();

 $qry = "(".$uQry.")";
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

 if($getCount)
 {
  $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_categories WHERE ".$qry);
  $db->Read();
  $outArr['count'] = $db->record[0];
 }

 if(!$orderBy)
  $orderBy = "ordering ASC";
 $qry.= " ORDER BY ".$orderBy;

 if($limit)
  $qry.= " LIMIT ".$limit;


 $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_categories WHERE ".$qry);
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

  if($getCount)
   $outArr['categories'][] = $ret['outarr'];
  else
   $outArr[] = $ret['outarr'];

  if($shellid != null)
   $out.= "#".$ret['outarr']['id'].". ".$ret['outarr']['name']."\n";
 }
 $db->Close();
 $out.= ($getCount ? $outArr['count'] : count($outArr))." categories found.\n";

 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_categoryFind($args, $sessid, $shellid, $extraVar=null)
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
  $ret = dynarc_checkForArchive($args,$sessid,$shellid,$extraVar);
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
function dynarc_categoryInfo($args, $sessid, $shellid, $extraVar=null)
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
   case '-code' : {$code=$args[$c+1]; $c++;} break;
   case '-parent' : {$parentId=$args[$c+1]; $c++;} break;
   case '-pt' : case '--parent-tag' : {$parentTag=$args[$c+1]; $c++;} break;
   case '-into' : {$into=$args[$c+1]; $c++;} break;
   case '-linkap' : {$linkAP=$args[$c+1]; $c++;} break;
   case '-linkaid' : {$linkAID=$args[$c+1]; $c++;} break;
   case '-linkid' : {$linkID=$args[$c+1]; $c++;} break;
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
  $ret = dynarc_checkForArchive($args,$sessid,$shellid,$extraVar);
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

 if($linkID && $linkAP)
 {
  // get link archive id //
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_archives WHERE tb_prefix='".$linkAP."' AND trash='0'");
  if($db->Read())
   $linkAID = $db->record['id'];
  $db->Close();
 }

 $db = new AlpaDatabase();
 if($tag) // retrieve the first category in the archive with tag $tag //
  $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_categories WHERE tag='".$tag."'"
	.($parentId ? " AND parent_id='".$parentId."'" : ($intoId ? " AND hierarchy LIKE '%,".$intoId.",%'" : ""))
	." AND trash='0' ORDER BY parent_id ASC LIMIT 1");
 else if($code) // retrieve the first category in the archive with code $code
  $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_categories WHERE code='".$code."'"
	.($parentId ? " AND parent_id='".$parentId."'" : ($intoId ? " AND hierarchy LIKE '%,".$intoId.",%'" : ""))
	." AND trash='0' ORDER BY parent_id ASC LIMIT 1");
 else if($linkAID && $linkID)
  $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_categories WHERE lnk_id='".$linkID."' AND lnkarc_id='".$linkAID."' AND trash='0' LIMIT 1");
 else
  $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='$id'");
 if(!$db->Read())
  return array("message"=>"Category ".($id ? "#$id" : "with tag '$tag'")." does not exists!\n","error"=>"CATEGORY_DOES_NOT_EXISTS");

 /* CHECK PERMISSION TO READ */
 $sessInfo = sessionInfo($sessid);
 $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid'],$db->record['shgrps'],$db->record['shusrs']);
 if($sessInfo['uname'] != "root" && !$m->canRead($sessInfo['uid']))
  return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");

 /* OUTPUT */
 $a = array('id'=>$db->record['id'],'parent_id'=>$db->record['parent_id'],'name'=>$db->record['name'],
	'desc'=>$db->record['description'],'ctime'=>strtotime($db->record['ctime']),'ordering'=>$db->record['ordering'],
	'trash'=>$db->record['trash'],'published'=>$db->record['published'],'tag'=>$db->record['tag'],
	'def_order_field'=>$db->record['defordfield'],'def_order_method'=>($db->record['defordasc'] ? "ASC" : "DESC"),
	'hierarchy'=>$db->record['hierarchy'], 'aliasname'=>$db->record['aliasname'], 'shgrps'=>$db->record['shgrps'], 'shusrs'=>$db->record['shusrs']);
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
  if($a['aliasname'])
   $out.= "Alias: ".$a['aliasname']."\n";
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
function dynarc_getRootCategory($args, $sessid, $shellid, $extraVar=null)
{
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;
  }

  if(!$_AP) return array("message"=>"Error: you must specify the archive prefix. (with: -ap ARCHIVE_PREFIX)", "error"=>"INVALID_ARCHIVE");
  if(!$_ID) return array("message"=>"Error: you must specify the category id. (width: -id CAT_ID)", "error"=>"INVALID_CAT");

  // Get category info
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT uid,gid,_mod,parent_id,hierarchy FROM dynarc_".$_AP."_categories WHERE id='".$_ID."'");
  if(!$db->Read())
   return array("message"=>"Error: Category #".$_ID." does not exists!", "error"=>"CATEGORY_DOES_NOT_EXISTS");
  /* CHECK PERMISSION TO READ */
  $sessInfo = sessionInfo($sessid);
  $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid']);
  if($sessInfo['uname'] != "root" && !$m->canRead($sessInfo['uid']))
   return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");
  $catInfo = array('id'=>$_ID, 'uid'=>$db->record['uid'], 'gid'=>$db->record['gid'], '_mod'=>$db->record['_mod'], 'parent_id'=>$db->record['parent_id'],
	'hierarchy'=>$db->record['hierarchy']);
  $db->Close();

  if($catInfo['hierarchy'] && ($catInfo['hierarchy'] != ",") && ($catInfo['hierarchy'] != ",,"))
  {
   $hierarchy = explode(",",ltrim($catInfo['hierarchy'],','));
   $rootCatId = $hierarchy[0];
  }
  else 
   $rootCatId = $catInfo['parent_id'];

  if($rootCatId)
  {
   // scambio l'ID con rootCatId
   for($c=1; $c < count($args); $c++)
  	switch($args[$c]) { case '-id' : $args[$c+1] = $rootCatId; break; }
   return dynarc_categoryInfo($args, $sessid, $shellid);
  }
  else
   return dynarc_categoryInfo($args, $sessid, $shellid);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_categorySort($args, $sessid, $shellid, $extraVar=null)
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
  $ret = dynarc_checkForArchive($args,$sessid,$shellid,$extraVar);
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
function dynarc_categoryMove($args, $sessid, $shellid, $extraVar=null)
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
  $ret = dynarc_checkForArchive($args,$sessid,$shellid,$extraVar);
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

  $oldCatInfo = $catInfo;
  $oldParentId = $catInfo['parent_id'];
  $newCatInfo = $catInfo;
  $newCatInfo['hierarchy'] = $hierarchy;

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

   $newCatInfo['parent_id'] = $parentId;
   $newCatInfo['old_parent_id'] = $oldParentId;
   // call onmovecategory function if exists //
   if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
   {
    include_once($_BASE_PATH.$archiveInfo['functionsfile']);
    if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_onmovecategory",true))
	{
     $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_onmovecategory", $args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo);
	 if(is_array($ret) && $ret['error']) return $ret;
	}
   }
   else if($archiveInfo['inherit'])
   {
    // call onmovecategory function inherited if exists //
    $dbX = new AlpaDatabase();
    $dbX->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
    $dbX->Read();
    if($dbX->record['fun_file'] && file_exists($_BASE_PATH.$dbX->record['fun_file']))
    {
     include_once($_BASE_PATH.$dbX->record['fun_file']);
     if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_onmovecategory",true))
     {
      $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_onmovecategory", $args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo);
      if(is_array($ret) && $ret['error']) return $ret;
     }
    }
    $dbX->Close();
   }
  }
  /* call onmovecategory function for all installed extensions */
  for($i=0; $i < count($extensions); $i++)
  {
   /* EXECUTE FUNCTION */
   if(is_callable("dynarcextension_".$extensions[$i]."_onmovecategory",true))
   {
    $ret = call_user_func("dynarcextension_".$extensions[$i]."_onmovecategory", $args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo);
    if(is_array($ret) && $ret['error']) return $ret;
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
function dynarc_categoryCopy($args, $sessid, $shellid, $extraVar=null)
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
  $ret = dynarc_checkForArchive($args,$sessid,$shellid,$extraVar);
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

  // call oncopycategory function if exists //
  if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
  {
   include_once($_BASE_PATH.$archiveInfo['functionsfile']);
   if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_oncopycategory",true))
   {
    $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_oncopycategory", $sessid, $shellid, $archiveInfo, $catInfo, $cloneInfo);
    if(is_array($ret) && $ret['error']) return $ret;
   }
  }
  else if($archiveInfo['inherit'])
  {
   // call oncopycategory function inherited if exists //
   $dbX = new AlpaDatabase();
   $dbX->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
   $dbX->Read();
   if($dbX->record['fun_file'] && file_exists($_BASE_PATH.$dbX->record['fun_file']))
   {
    include_once($_BASE_PATH.$dbX->record['fun_file']);
    if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_oncopycategory",true))
    {
     $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_oncopycategory", $sessid, $shellid, $archiveInfo, $catInfo, $cloneInfo);
     if(is_array($ret) && $ret['error']) return $ret;
    }
   }
   $dbX->Close();
  }

  /* Copy all extensions */
  for($i=0; $i < count($extensions); $i++)
  {
   /* EXECUTE FUNCTION */
   if(is_callable("dynarcextension_".$extensions[$i]."_oncopycategory",true))
   {
    $ret = call_user_func("dynarcextension_".$extensions[$i]."_oncopycategory", $sessid, $shellid, $archiveInfo, $catInfo, $cloneInfo);
    if(is_array($ret) && $ret['error']) return $ret;
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
function dynarc_categoryTree($args, $sessid, $shellid, $extraVar=null)
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
   case '-where' : {$where=$args[$c+1]; $c++;} break;
  }

 /* CHECK FOR ARCHIVE */
 if($extraVar)
  $archiveInfo = $extraVar;
 else
 {
  $ret = dynarc_checkForArchive($args,$sessid,$shellid, $extraVar);
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
  $qry = "SELECT id FROM dynarc_".$archiveInfo['prefix']."_categories WHERE (".$uQry.")"
	.($where ? " AND (".$where.")" : "")." AND parent_id='".$catId."' AND trash='0' ORDER BY ".$orderBy;
 else
  $qry = "SELECT id,name".($get ? ",".$get : "")." FROM dynarc_".$archiveInfo['prefix']."_categories WHERE (".$uQry.")"
	.($where ? " AND (".$where.")" : "")." AND parent_id='".$catId."' AND trash='0' ORDER BY ".$orderBy;

 $db->RunQuery($qry);
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
   if(is_array($ret) && $ret['error']) return $ret;
   else if(is_array($ret)) $catInfo = $ret;
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

