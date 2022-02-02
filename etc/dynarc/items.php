<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-05-2017
 #PACKAGE: dynarc
 #DESCRIPTION: Items functions for Dynarc
 #VERSION: 2.30beta
 #CHANGELOG: 23-05-2017 : Bug fix sharing.
			 15-05-2017 : Aggiunto parametro extraVar su tutte le funzioni.
			 24-10-2016 : MySQLi integration.
			 15-07-2016 : Aggiunto parametro -groupby su funzione item-list [da completare, vedere TODO]
			 16-01-2015 : Aggiunto parametro -gettotals su funzione item-list.
			 25-07-2014 : Bug fix vari	
			 12-07-2014 : Bug fix catId su funzione edit-item
			 02-07-2014 : Aggiunta funzione getItemsCount
			 27-05-2014 : Bug fix alias.
			 16-01-2014 : Aggiunta opzione --get-cat-name nella funzione dynarc_itemInfo.
 
 #TODO: su itemlist ho aggiunto parametro groupby (es: mysql GROUP BY name) però bisogna escogitare sistema per il count.
	    PS: GROUP BY serve per filtrare i risultati rimuovendo i doppioni.
 
*/

global $_BASE_PATH;
include_once($_BASE_PATH."etc/dynarc/archives.php");
include_once($_BASE_PATH."etc/dynarc/categories.php");

function dynarc_newItem($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;
 $shGroups = array();
 $shUsers = array();
 $unshGroups = array();
 $unshUsers = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-id' : {$_ID=$args[$c+1]; $c++;} break; // possibilità di scegliere l'ID

   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : case '--cat-tag' : {$catTag=$args[$c+1]; $c++;} break;

   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-description' : case '-desc' : {$desc=$args[$c+1]; $c++;} break;
   case '-ordering' : {$ord=$args[$c+1]; $c++;} break;
   case '--publish' : $published=true; break;
   case '-ctime' : {$ctime=strtotime($args[$c+1]); $c++;} break;
   case '-set' : {$set=$args[$c+1]; $c++;} break;
   case '-extset' : {$extset=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-groupid' : {$groupId=$args[$c+1]; $c++;} break;
   case '--overwrite-if-exists' : $overwriteIfExists=true; break;

   case '-alias' : {$alias=$args[$c+1]; $c++;} break;
   case '-code-num' : {$codeNum = $args[$c+1]; $c++;} break;
   case '-code-string' : case '-code-str' : {$codeStr = $args[$c+1]; $c++;} break;
   case '-code-extension' : case '-code-ext' : {$codeExt = $args[$c+1]; $c++;} break;
   case '-contents' : case '-cnts' : {$contents = $args[$c+1]; $c++;} break;
   case '-keywords' : case '-keyw' : {$keywords = $args[$c+1]; $c++;} break;
   case '-language' : case '-lang' : {$lang = $args[$c+1]; $c++;} break;

   /* Link arguments */
   case '-linkap' : {$linkAP=$args[$c+1]; $c++;} break;
   case '-linkaid' : {$linkAID=$args[$c+1]; $c++;} break;
   case '-linkid' : {$linkID=$args[$c+1]; $c++;} break;

   /* SHARING */
   case '--share-add-group' : {$shGroups[]=$args[$c+1]; $c++;} break; // divide group and perms with(:) example: --share-add-group mygroup:6
   case '--share-add-user' : {$shUsers[]=$args[$c+1]; $c++;} break; // divide user and perms with(:) example: --share-add-user alex:4

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

 if($catId)
 {
  $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -id $catId -get hierarchy",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
 }
 else if($catTag)
 {
  $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -tag '$catTag' -get hierarchy",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
  $catId = $catInfo['id'];
 }
 else
 {
  /* CHECK FOR ARCHIVE PERMISSIONS */
  if(!$archiveInfo['modinfo']['can_write'])
   return array("message"=>"Permission denied!, you have not permission to insert item into this archive.","error"=>"ARCHIVE_PERMISSION_DENIED");
 }

 if($catInfo)
 {
  /* CHECK FOR PERMISSIONS */
  if(!$catInfo['modinfo']['can_write'])
   return array("message"=>"Permission denied!, you have not permission to insert items into this category.","error"=>"CATEGORY_PERMISSION_DENIED");
 }

 if($overwriteIfExists)
 {
  $ret = GShell("dynarc item-info -ap '".$archiveInfo['prefix']."' -name `".$name."`".($catId ? " -into $catId" : ""),$sessid,$shellid,$archiveInfo);
  if(!$ret['error'])
  {
   if(($pos = array_search("-cat",$args)) !== false)
	array_splice($args,$pos,2);
   if(($pos = array_search("-ct",$args)) !== false)
	array_splice($args,$pos,2);
   if(($pos = array_search("--cat-tag",$args)) !== false)
	array_splice($args,$pos,2);
   $args[] = "-id";
   $args[] = $ret['outarr']['id'];
   return dynarc_editItem($args,$sessid,$shellid,$archiveInfo);
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

  if(!$name)
  {
   $ret = GShell("dynarc item-info -aid `".$linkAID."` -id `".$linkID."`",$sessid, $shellid);
   if(!$ret['error'])
	$name = $ret['outarr']['name'];
  }
 }

 if($group)
  $groupId = _getGID($group);

 $db = new AlpaDatabase();
 if(!$ord)
 {
  $db->RunQuery("SELECT ordering FROM dynarc_".$archiveInfo['prefix']."_items WHERE cat_id='".$catInfo['id']."' AND trash='0' ORDER BY ordering DESC LIMIT 1");
  if(!$db->Read())
   $ord = 1;
  else
   $ord = $db->record['ordering']+1;
 }

 $now = $ctime ? $ctime : time();
 $uid = $sessInfo['uid'];
 $gid = $groupId ? $groupId : $sessInfo['gid'];
 $mod = $perms ? $perms : ($archiveInfo['def_item_perms'] ? $archiveInfo['def_item_perms'] : 640);

 if(!$published)
  $published = $archiveInfo['def_item_published'];

 // get fields list for check //
 $fields = $db->FieldsInfo("dynarc_".$archiveInfo['prefix']."_items");
 //$db->Close();

 $q = "INSERT INTO dynarc_".$archiveInfo['prefix']."_items(".($_ID ? "id," : "");
 $q.= "uid,gid,_mod,cat_id,lnk_id,lnkarc_id,name,description,ordering,ctime,published,hierarchy";
 if($fields['code_num'])
  $q.= ",code_num";
 if($fields['code_str'])
  $q.= ",code_str";
 if(isset($codeExt) && $fields['code_ext'])
  $q.= ",code_ext";
 if(isset($contents) && $fields['contents'])
  $q.= ",contents";
 if(isset($keywords) && $fields['keywords'])
  $q.= ",keywords";
 if(isset($lang) && $fields['lang'])
  $q.= ",lang";
 if($fields['aliasname'])
  $q.= ",aliasname";
 if($fields['shgrps'])
  $q.= ",shgrps";
 if($fields['shusrs'])
  $q.= ",shusrs";
 $q.= ") VALUES(".($_ID ? "'".$_ID."'," : "");
 $q.= "'".$uid."','".$gid."','".$mod."','".$catId."','".$linkID."','".$linkAID."','"
	.$db->Purify($name)."','".$db->Purify($desc)."','$ord','".date('Y-m-d H:i:s',$now)."','$published','".($catInfo ? $catInfo['hierarchy'].$catInfo['id'].",'" : ",'");
 if($fields['code_num'])
 {
  if(!isset($codeNum))
  {
   // detect code num //
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT code_num,code_str FROM dynarc_".$archiveInfo['prefix']."_items WHERE cat_id='$catId' AND trash='0' ORDER BY code_num DESC LIMIT 1");
   if(!$db->Read())
	$codeNum = 1;
   else
   {
	$codeNum = $db->record['code_num']+1;
	if(!$codeStr)
	 $codeStr = increase_itemcode($db->record['code_str']);
   }
   $db->Close();
  }
  $q.= ",'".$codeNum."'";
 }
 if($fields['code_str'])
 {
  if(!isset($codeStr))
  {
   // detect code str //
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT code FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='$catId'");
   if(!$db->Read())
	$codeStr = $codeNum;
   else
	$codeStr = $db->record['code'].".".$codeNum;
   $db->Close();
  }
  $q.= ",'".$codeStr."'";
 }
 if(isset($codeExt) && $fields['code_ext'])
  $q.= ",'".$codeExt."'";
 if(isset($contents) && $fields['contents'])
  $q.= ",'".$db->Purify($contents)."'";
 if(isset($keywords) && $fields['keywords'])
  $q.= ",'".$db->Purify($keywords)."'";
 if(isset($lang) && $fields['lang'])
  $q.= ",'".$lang."'";
 if($fields['aliasname'])
 {
  if(!isset($alias))
  {
   $alias = str_replace(" ","_",strtolower(html_entity_decode($name,ENT_QUOTES,"UTF-8")));
   $k = array("?", "#", "'", '"', "&", "/", "\"");
   $v = array("", "-", "_", "_", "and", "-", "_");
   $alias = str_replace($k,$v,$alias);
   $alias = str_replace("__","_",$alias);
  }
  $q.= ",'".$db->Purify($alias)."'";
 }
 if($fields['shgrps'])
 {
  /* UPDATE GROUPS SHARE INFO */
  $shareGroups = array();
  if(count($shGroups))
  {
   for($c=0; $c < count($shGroups); $c++)
   {
    $p = strrpos($shGroups[$c],":");
    $shMOD = substr($archiveInfo['def_item_perms'],1,1);
    if($p === false) $shGID = is_numeric($shGroups[$c]) ? $shGroups[$c] : _getGID($shGroups[$c]);
    else { $tmp = substr($shGroups[$c],0,$p); $shGID = is_numeric($tmp) ? $tmp : _getGID($tmp); $shMOD = substr($shGroups[$c],$p+1); }
    $shareGroups[$shGID] = $shMOD;
   }
  }
  $shgrps = "";
  if(count($shareGroups))
  {
   $shgrps = "#,";
   reset($shareGroups);
   while(list($k,$v) = each($shareGroups)) { $shgrps.= $k."=".$v.","; }
   $shgrps.= "#";
  }
  $q.= ",'".$shgrps."'";
 }
 if($fields['shusrs'])
 {
  /* UPDATE USERS SHARE INFO */
  $shareUsers = array();
  if(count($shUsers))
  {
   for($c=0; $c < count($shUsers); $c++)
   {
    $p = strrpos($shUsers[$c],":");
    $shMOD = substr($archiveInfo['def_item_perms'],0,1);
    if($p === false)
	 $shUID = is_numeric($shUsers[$c]) ? $shUsers[$c] : _getUID($shUsers[$c]);
    else { $tmp = substr($shUsers[$c],0,$p); $shUID = is_numeric($tmp) ? $tmp : _getUID($tmp); $shMOD = substr($shUsers[$c],$p+1); }
    $shareUsers[$shUID] = $shMOD;
   }
  }
  $shusrs = "";
  if(count($shareUsers))
  {
   $shusrs = "#,";
   reset($shareUsers);
   while(list($k,$v) = each($shareUsers)) { $shusrs.= $k."=".$v.","; }
   $shusrs.= "#";
  }
  $q.= ",'".$shusrs."'";  
 }
 $q.= ")";

 $db = new AlpaDatabase();
 $db->RunQuery($q);
 if($db->Error) return array('message'=>$out."failed!\nMySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
 else if($_ID)
  $id = $_ID;
 else
  $id = $db->GetInsertId();
 $db->Close();

 if($archiveInfo['sync_enabled'])
 {
  $db = new AlpaDatabase();
  if(!$syncid)
   $syncid = md5($id.$now.rand(1,255));
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET syncid='".$syncid."' WHERE id='".$id."'");
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_synclog(syncid,id,uid,gid,_mod,status,logtime) VALUES('".$syncid."','".$id."','"
	.$uid."','".$gid."','".$mod."','CREATED','".date('Y-m-d H:i:s',$now)."')");
  $db->Close();
 }


 $a = array('id'=>$id,'cat_id'=>$catId,'name'=>$name,'desc'=>$desc,'ordering'=>$ord,'ctime'=>$now,'published'=>$published,'hierarchy'=>($catInfo ? $catInfo['hierarchy'].$catInfo['id']."," : ""));

 if(isset($alias))
  $a['aliasname'] = $alias;

 if($catInfo)
  $a['cat_tag'] = $catInfo['tag'];

 if($codeNum)
  $a['code_num'] = $codeNum;
 if($codeStr)
  $a['code_str'] = $codeStr;
 if($codeExt)
  $a['code_ext'] = $codeExt;

 if($set)
  $a = dynarc_parseItemSet("set",$set,$sessid, $shellid, $archiveInfo, $a);
 
 if($extset)
  $a = dynarc_parseExtensionSet("set",$extset,$sessid, $shellid, $archiveInfo, $a);

 $out.= "done! ID=$id\n";
 $outArr = $a;

 if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
 {
  // call oncreateitem function if exists //
  include_once($_BASE_PATH.$archiveInfo['functionsfile']);
  if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_oncreateitem",true))
  {
   $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_oncreateitem", $args, $sessid, $shellid, $archiveInfo, $a);
   if(is_array($ret) && $ret['error']) return $ret;
   else if(is_array($ret) && $ret['outarr']) $outArr = $ret['outarr'];
   else if(is_array($ret)) $outArr = $ret;
  }
 }
 else if($archiveInfo['inherit'])
 {
  // call oncreateitem function inherited if exists //
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
  $db->Read();
  if($db->record['fun_file'] && file_exists($_BASE_PATH.$db->record['fun_file']))
  {
   include_once($_BASE_PATH.$db->record['fun_file']);
   if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_oncreateitem",true))
   {
    $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_oncreateitem", $args, $sessid, $shellid, $archiveInfo, $a);
    if(is_array($ret) && $ret['error']) return $ret;
    else if(is_array($ret) && $ret['outarr']) $outArr = $ret['outarr'];
    else if(is_array($ret)) $outArr = $ret;
   }
  }
  $db->Close();
 }


 // call oncreateitem function from all installed extensions //
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."'");
 while($db->Read())
 {
  /* EXECUTE FUNCTION */
  include_once($_BASE_PATH."etc/dynarc/extensions/".$db->record['extension_name']."/index.php");
  if(is_callable("dynarcextension_".$db->record['extension_name']."_oncreateitem",true))
  {
   $ret = call_user_func("dynarcextension_".$db->record['extension_name']."_oncreateitem", $args, $sessid, $shellid, $archiveInfo, $a);
   if(is_array($ret) && $ret['error']) return $ret;
  }
 }
 $db->Close();


 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_editItem($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;

 $shGroups = array();
 $shUsers = array();
 $unshGroups = array();
 $unshUsers = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;

   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : case '--cat-tag' : {$catTag=$args[$c+1]; $c++;} break;

   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-description' : case '-desc' : {$desc=$args[$c+1]; $c++;} break;
   case '-ordering' : {$ord=$args[$c+1]; $c++;} break;
   case '--publish' : $published=true; break;
   case '--unpublish' : $published=false; break;
   case '-ctime' : {$ctime=strtotime($args[$c+1]); $c++;} break;
   case '-set' : {$set=$args[$c+1]; $c++;} break;
   case '-extset' : {$extset=$args[$c+1]; $c++;} break;
   case '-extunset' : {$extunset=$args[$c+1]; $c++;} break;
   case '-perms' : {$perms=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-groupid' : {$groupId=$args[$c+1]; $c++;} break;
   case '--if-exists' : $forceReturnTrue=true; break; /* force return true if element does not exists */

   case '-alias' : {$alias=$args[$c+1]; $c++;} break;
   case '-code-num' : {$codeNum = $args[$c+1]; $c++;} break;
   case '-code-string' : case '-code-str' : {$codeStr = $args[$c+1]; $c++;} break;
   case '-code-extension' : case '-code-ext' : {$codeExt = $args[$c+1]; $c++;} break;
   case '-contents' : case '-cnts' : {$contents = $args[$c+1]; $c++;} break;
   case '-keywords' : case '-keyw' : {$keywords = $args[$c+1]; $c++;} break;
   case '-language' : case '-lang' : {$lang = $args[$c+1]; $c++;} break;

   /* Link arguments */
   case '-linkap' : {$linkAP=$args[$c+1]; $c++;} break;
   case '-linkaid' : {$linkAID=$args[$c+1]; $c++;} break;
   case '-linkid' : {$linkID=$args[$c+1]; $c++;} break;

   /* SHARING */
   case '--share-add-group' : {$shGroups[]=$args[$c+1]; $c++;} break; // divide group and perms with(:) example: --share-add-group mygroup:6
   case '--share-add-user' : {$shUsers[]=$args[$c+1]; $c++;} break; // divide user and perms with(:) example: --share-add-user alex:4
   case '--unshare-group' : {$unshGroups[]=$args[$c+1]; $c++;} break;
   case '--unshare-user' : {$unshUsers[]=$args[$c+1]; $c++;} break;
  }
 
 $out = "";
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
 
 if(!$id && $forceReturnTrue)
  return array('message'=>"No element specified.");

 /* GET ITEM INFO */
 $ret = GShell("dynarc item-info -ap '".$archiveInfo['prefix']."' -id '$id'",$sessid,$shellid,$archiveInfo);
 if($ret['error'])
 {
  if($forceReturnTrue)
   $ret['error'] = null;
  return $ret;
 }
 $itemInfo = $ret['outarr'];

 if(!$itemInfo['modinfo']['can_write'])
 {
  if($itemInfo['cat_id'])
  {
   // check category shared perms
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT shgrps,shusrs FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$itemInfo['cat_id']."'");
   if($db2->Error) return array('message'=>$out."failed!\nMySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
   if($db2->Read())
   {
	$m = new GMOD($itemInfo['modinfo']['mod'],$itemInfo['modinfo']['uid'],$itemInfo['modinfo']['gid'], $db2->record['shgrps'],$db2->record['shusrs']);
	$itemInfo['modinfo'] = $m->toArray($sessInfo['uid']);
   }
   $db2->Close();
  }
 }
 if(!$itemInfo['modinfo']['can_write'])
  return array("message"=>"Permission denied!, you have not permissions for edit this item.", "error"=>"PERMISSION_DENIED"); 

 if($catId && ($catId != $itemInfo['cat_id']))
 {
  $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -id '$catId' -get hierarchy",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
 }
 else if($catTag)
 {
  $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -tag '$catTag' -get hierarchy",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
 }
 if($catInfo && ($catInfo['id'] != $itemInfo['cat_id']))
 {
  /* CHECK FOR PERMISSIONS */
  if(!$catInfo['modinfo']['can_write'])
   return array("message"=>"Permission denied!, you have not permission to move the item into this category.","error"=>"CATEGORY_PERMISSION_DENIED");
 }

 $a = $itemInfo;
 $a['old_cat_id'] = $itemInfo['cat_id'];

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
 }


 if($group)
  $groupId = _getGID($group);

 $db = new AlpaDatabase();
 /* UPDATE BASIC INFO */
 $q = "";
 if($catInfo)
 {
  $q.= ",cat_id='".$catInfo['id']."',hierarchy='".$catInfo['hierarchy'].$catInfo['id'].",'"; $a['cat_id']=$catInfo['id'];
  $a['cat_id'] = $catInfo['id'];
 }
 else if(isset($catId) && ($catId == 0))
 {
  $q.= ",cat_id='0',hierarchy=''";
  $a['cat_id'] = 0;
 }
 if($name){
  $q.= ",name='".$db->Purify($name)."'"; $a['name']=$name;}
 if(isset($desc)){
  $q.= ",description='".$db->Purify($desc)."'"; $a['desc']=$desc;}
 if(isset($ord)){
  $q.= ",ordering='$ord'"; $a['ordering']=$ord;}
 if(isset($published)){
  $q.= ",published='$published'"; $a['published']=$published;}
 if($ctime)
 {
  $q.= ",ctime='".date('Y-m-d H:i:s',$ctime)."'";
  $a['oldctime'] = $itemInfo['ctime'];
  $a['ctime']=$ctime;
 }
 $now = time();
 $q.= ",mtime='".date('Y-m-d H:i:s',$now)."'"; $a['mtime']=$now;
 if(isset($perms))
 {
  if(($itemInfo['modinfo']['uid'] == $sessInfo['uid']) || ($sessInfo['uname'] == 'root'))
   $q.= ",_mod='$perms'";
 }
 if(isset($linkAID) && isset($linkID))
  $q.= ",lnkarc_id='".$linkAID."',lnk_id='".$linkID."'";

 if($groupId)
 {
  if(($itemInfo['modinfo']['uid'] == $sessInfo['uid']) || ($sessInfo['uname'] == 'root'))
   $q.= ",gid='$groupId'";
 }
 $db->Close();

 // get fields list for check //
 $db = new AlpaDatabase();
 $fields = $db->FieldsInfo("dynarc_".$archiveInfo['prefix']."_items");
 
 /* UPDATE EXTENDED INFO */
 if(isset($codeNum) && $fields['code_num']){
  $q.= ",code_num='$codeNum'"; $a['code_num']=$codeNum;}
 if(isset($codeStr) && $fields['code_str']){
  $q.= ",code_str='$codeStr'"; $a['code_str']=$codeStr;}
 if(isset($codeExt) && $fields['code_ext']){
  $q.= ",code_ext='$codeExt'"; $a['code_ext']=$codeExt;}
 if(isset($contents) && $fields['contents']){
  $q.= ",contents='".$db->Purify($contents)."'"; $a['contents']=$contents;}
 if(isset($keywords) && $fields['keywords']){
  $q.= ",keywords='".$db->Purify($keywords)."'"; $a['keywords']=$keywords;}
 if(isset($lang) && $fields['lang']){
  $q.= ",lang='$lang'"; $a['lang']=$lang;}
 if(isset($alias) && $fields['aliasname'])
  $q.= ",aliasname='".$db->Purify($alias)."'";

 $db->Close();

 /* UPDATE GROUPS SHARE INFO */
 $shareGroups = array();
 if(count($shGroups))
 {
  for($c=0; $c < count($shGroups); $c++)
  {
   $p = strrpos($shGroups[$c],":");
   $shMOD = substr($archiveInfo['def_item_perms'],1,1);
   if($p === false)
	$shGID = is_numeric($shGroups[$c]) ? $shGroups[$c] : _getGID($shGroups[$c]);
   else
   {
	$tmp = substr($shGroups[$c],0,$p);
	$shGID = is_numeric($tmp) ? $tmp : _getGID($tmp);
	$shMOD = substr($shGroups[$c],$p+1);
   }
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
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT shgrps FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
  $db->Read();
  $m = new GMOD(null,null,null,$db->record['shgrps']);
  $db->Close();

  for($c=0; $c < count($unshareGroups); $c++)
  {
   if($m->SHGROUPS[$unshareGroups[$c]])
	unset($m->SHGROUPS[$unshareGroups[$c]]);
  }
  reset($shareGroups);
  while(list($k,$v) = each($shareGroups))
  {
   $m->SHGROUPS[$k] = $v;
  }

  $shgrps = "#,";
  reset($m->SHGROUPS);
  while(list($k,$v) = each($m->SHGROUPS))
  {
   $shgrps.= $k."=".$v.",";
  }
  $shgrps.= "#";
  $q.= ",shgrps='".$shgrps."'";
 }
 
 /* UPDATE USERS SHARE INFO */
 $shareUsers = array();
 if(count($shUsers))
 {
  for($c=0; $c < count($shUsers); $c++)
  {
   $p = strrpos($shUsers[$c],":");
   $shMOD = substr($archiveInfo['def_item_perms'],0,1);
   if($p === false)
	$shUID = is_numeric($shUsers[$c]) ? $shUsers[$c] : _getUID($shUsers[$c]);
   else
   {
	$tmp = substr($shUsers[$c],0,$p);
	$shUID = is_numeric($tmp) ? $tmp : _getUID($tmp);
	$shMOD = substr($shUsers[$c],$p+1);
   }
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
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT shusrs FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
  $db->Read();
  $m = new GMOD(null,null,null,null,$db->record['shusrs']);
  $db->Close();

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
  $q.= ",shusrs='".$shusrs."'";
 }

 /* WRITE TO DATABASE */
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='$id'");
 $db->Close();

 if($archiveInfo['sync_enabled'])
 {
  $q = ",status='UPDATED',logtime='".date('Y-m-d H:i:s',$now)."'";
  if(isset($perms))
  {
   if(($itemInfo['modinfo']['uid'] == $sessInfo['uid']) || ($sessInfo['uname'] == 'root'))
    $q.= ",_mod='$perms'";
  }
  if($groupId)
  {
   if(($itemInfo['modinfo']['uid'] == $sessInfo['uid']) || ($sessInfo['uname'] == 'root'))
    $q.= ",gid='$groupId'";
  }
  
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT logtime FROM dynarc_".$archiveInfo['prefix']."_synclog WHERE syncid='".$itemInfo['syncid']."' LIMIT 1");
  if($db->Read())
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET ".ltrim($q,",")." WHERE syncid='".$itemInfo['syncid']."'");
  else
   $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_synclog(syncid,id,uid,gid,_mod,status,logtime) VALUES('".$itemInfo['syncid']."','".$id."','".$itemInfo['modinfo']['uid']."','".($groupId ? $groupId : $itemInfo['modinfo']['gid'])."','".($perms ? $perms : $itemInfo['modinfo']['mod'])."','CREATED','".date('Y-m-d H:i:s',$now)."')");
  $db->Close();
 }


 if($set)
  $a = dynarc_parseItemSet("set",$set,$sessid, $shellid, $archiveInfo, $a);

 if($extset)
 {
  $ret = dynarc_parseExtensionSet("set",$extset,$sessid, $shellid, $archiveInfo, $a);
  if($ret)
   $a = $ret;
 }
 if($extunset)
  dynarc_parseExtensionSet("unset",$extunset,$sessid, $shellid, $archiveInfo, $a);

 $outArr = $a;

 if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
 {
  // call onedititem function if exists //
  include_once($_BASE_PATH.$archiveInfo['functionsfile']);
  if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_onedititem",true))
  {
   $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_onedititem", $args, $sessid, $shellid, $archiveInfo, $a);
   if(is_array($ret) && $ret['error']) return $ret;
   else if(is_array($ret) && $ret['outarr']) $outArr = $ret['outarr'];
   else if(is_array($ret)) $outArr = $ret;
  }
 }
 else if($archiveInfo['inherit'])
 {
  // call onedititem function inherited if exists //
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
  $db->Read();
  if($db->record['fun_file'] && file_exists($_BASE_PATH.$db->record['fun_file']))
  {
   include_once($_BASE_PATH.$db->record['fun_file']);
   if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_onedititem",true))
   {
    $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_onedititem", $args, $sessid, $shellid, $archiveInfo, $a);
    if(is_array($ret) && $ret['error']) return $ret;
    else if(is_array($ret) && $ret['outarr']) $outArr = $ret['outarr'];
    else if(is_array($ret)) $outArr = $ret;
   }
  }
  $db->Close();
 }


 // call onedititem function from all installed extensions //
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."'");
 while($db->Read())
 {
  /* EXECUTE FUNCTION */
  include_once($_BASE_PATH."etc/dynarc/extensions/".$db->record['extension_name']."/index.php");
  if(is_callable("dynarcextension_".$db->record['extension_name']."_onedititem",true))
  {
   $ret = call_user_func("dynarcextension_".$db->record['extension_name']."_onedititem", $args, $sessid, $shellid, $archiveInfo, $a);
   if(is_array($ret) && $ret['error']) return $ret;
  }
 }
 $db->Close();

 $out.= "Item has been updated!\n";

 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_deleteItem($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;

 $ids = array();
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-id' : {$ids[]=$args[$c+1]; $c++;} break;
   case '-r' : $delete=true; break;
   case '--return-item-info' : $returnItemsInfo=true; break;
  }

 $out = "";
 $outArr = array();
 $items = array();

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

 for($c=0; $c < count($ids); $c++)
 {
  $ret = GShell("dynarc item-info -ap '".$archiveInfo['prefix']."' -id ".$ids[$c],$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return array("Unable to delete item. ".$ret['message'],$ret['error']);
  $items[] = $ret['outarr'];
 }

 $sessInfo = sessionInfo($sessid);
 for($c=0; $c < count($items); $c++)
 {
  $itemInfo = $items[$c];

  /* CHECK PERMISSIONS */
  if(($itemInfo['modinfo']['uid'] != $sessInfo['uid']) && ($sessInfo['uname'] != 'root'))
   return array("message"=>"Unable to delete item #".$itemInfo['id']." - ".$itemInfo['name'].". Permission denied!\n", "error"=>"PERMISSION_DENIED");

  if($delete)
  {
   // call ondeleteitem function if exists //
   if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
   {
    include_once($_BASE_PATH.$archiveInfo['functionsfile']);
    if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_ondeleteitem",true))
    {
	 $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_ondeleteitem", $args, $sessid, $shellid, $archiveInfo, $itemInfo);
	 if(is_array($ret) && $ret['error']) return $ret;
    }
   }
   else if($archiveInfo['inherit'])
   {
    // call ondeleteitem function inherited if exists //
    $db = new AlpaDatabase();
    $db->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
    $db->Read();
    if($db->record['fun_file'] && file_exists($_BASE_PATH.$db->record['fun_file']))
    {
     include_once($_BASE_PATH.$db->record['fun_file']);
     if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_ondeleteitem",true))
     {
      $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_ondeleteitem", $args, $sessid, $shellid, $archiveInfo, $itemInfo);
      if(is_array($ret) && $ret['error']) return $ret;
     }
    }
    $db->Close();
   }
   // call ondeleteitem function from all installed extensions //
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."'");
   while($db->Read())
   {
	/* EXECUTE FUNCTION */
	include_once($_BASE_PATH."etc/dynarc/extensions/".$db->record['extension_name']."/index.php");
	if(is_callable("dynarcextension_".$db->record['extension_name']."_ondeleteitem",true))
	{
	 $ret = call_user_func("dynarcextension_".$db->record['extension_name']."_ondeleteitem", $args, $sessid, $shellid, $archiveInfo, $itemInfo);
	 if(is_array($ret) && $ret['error']) return $ret;
	}
   }
   $db->Close();

   /* REMOVE FROM DATABASE */
   $db = new AlpaDatabase();
   $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
   $db->Close();

   if($archiveInfo['sync_enabled'])
   {
    $db = new AlpaDatabase();
    $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET status='REMOVED',logtime='".date('Y-m-d H:i:s')."' WHERE syncid='".$itemInfo['syncid']."'");
    $db->Close();
   }

   $out.= "item #".$itemInfo['id']." has been removed!\n";
   if($returnItemsInfo)
	$outArr['removed'][] = $itemInfo;
  }
  else
  {
   // call ontrashitem function if exists //
   if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
   {
    include_once($_BASE_PATH.$archiveInfo['functionsfile']);
    if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_ontrashitem",true))
    {
	 $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_ontrashitem", $args, $sessid, $shellid, $archiveInfo, $itemInfo);
	 if(is_array($ret) && $ret['error']) return $ret;
    }
   }
   else if($archiveInfo['inherit'])
   {
    // call ontrashitem function inherited if exists //
    $db = new AlpaDatabase();
    $db->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
    $db->Read();
    if($db->record['fun_file'] && file_exists($_BASE_PATH.$db->record['fun_file']))
    {
     include_once($_BASE_PATH.$db->record['fun_file']);
     if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_ontrashitem",true))
     {
      $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_ontrashitem", $args, $sessid, $shellid, $archiveInfo, $itemInfo);
      if(is_array($ret) && $ret['error']) return $ret;
     }
    }
    $db->Close();
   }

   // call ontrashitem function from all installed extensions //
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."'");
   while($db->Read())
   {
	/* EXECUTE FUNCTION */
	include_once($_BASE_PATH."etc/dynarc/extensions/".$db->record['extension_name']."/index.php");
	if(is_callable("dynarcextension_".$db->record['extension_name']."_ontrashitem",true))
	{
	 $ret = call_user_func("dynarcextension_".$db->record['extension_name']."_ontrashitem", $args, $sessid, $shellid, $archiveInfo, $itemInfo);
	 if(is_array($ret) && $ret['error']) return $ret;
	}
   }
   $db->Close();

   // TRASH ONLY //
   $db = new AlpaDatabase();
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET trash='1' WHERE id='".$itemInfo['id']."'");
   $db->Close();

   // update sync status
   if($archiveInfo['sync_enabled'])
   {
    $db = new AlpaDatabase();
    $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET status='TRASHED',logtime='".date('Y-m-d H:i:s')."' WHERE syncid='".$itemInfo['syncid']."'");
    $db->Close();
   }

   $out.= "item #".$itemInfo['id']." has ben trashed!\n";
   if($returnItemsInfo)
	$outArr['trashed'][] = $itemInfo;
  }
 }
 if(!$returnItemsInfo)
  $outArr = $items[0];
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_itemInfo($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;
 $archives = array(); // <--- for links

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-code' : {$code=$args[$c+1]; $c++;} break;
   case '-barcode' : {$barcode=$args[$c+1]; $c++;} break;
   case '-alias' : {$alias=$args[$c+1]; $c++;} break;
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-get' : {$get=$args[$c+1]; $c++;} break;
   case '-extget' : {$extget=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-into' : {$into=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;

   case '--get-short-description' : $getShortDescription=true; break;
   case '--get-cat-name' : $getCatName=true; break;
   case '--verbose' : $verbose=true; break;
   default : $name=$args[$c]; break;
  }
 $out = "";
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

 $archives[$archiveInfo['id']] = $archiveInfo['prefix'];
 
 $db = new AlpaDatabase();

 if($id)
  $qry = "SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='$id'";
 else if($name)
 {
  if($into)
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
   $qry = "SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE name='".$db->Purify($name)."' AND hierarchy LIKE '%,".$intoId.",%' AND trash='0' LIMIT 1";
  }
  else if(isset($catId))
   $qry = "SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE name='".$db->Purify($name)."' AND cat_id='".$catId."' AND trash='0' LIMIT 1";
  else
   $qry = "SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE name='".$db->Purify($name)."' AND trash='0' LIMIT 1";
 }
 else if($alias)
 {
  if($into)
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
   $qry = "SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE aliasname='".$alias."' AND hierarchy LIKE '%,".$intoId.",%' AND trash='0' LIMIT 1";
  }
  else if(isset($catId))
   $qry = "SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE aliasname='".$alias."' AND cat_id='".$catId."' AND trash='0' LIMIT 1";
  else
   $qry = "SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE aliasname='".$alias."' AND trash='0' LIMIT 1";
 }
 else if($code)
 {
  if($into)
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
   $qry = "SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE code_str='".$code."' AND hierarchy LIKE '%,".$intoId.",%' AND trash='0' LIMIT 1";
  }
  else if(isset($catId))
   $qry = "SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE code_str='".$code."' AND cat_id='".$catId."' AND trash='0' LIMIT 1";
  else
   $qry = "SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE code_str='".$code."' AND trash='0' LIMIT 1";
 }
 else if($barcode)
  $qry = "SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE barcode='".$barcode."'".(isset($catId) ? " AND cat_id='".$catId."'" : "")." AND trash='0' LIMIT 1";
 else if($where)
  $qry = "SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE ".$where.(isset($catId) ? " AND cat_id='".$catId."'" : "")." AND trash='0' LIMIT 1";
 else
  return array("message"=>"You must specify item. with(-id ITEM_ID || -alias ITEM_ALIAS || -code ITEM_CODE)","error"=>"INVALID_ITEM");

 $db = new AlpaDatabase();
 $db->RunQuery($qry);
 if(!$db->Read())
 {
  $db->Close();
  if(isset($id))
   $out = "Item #$id does not exists into archive ".$archiveInfo['name'];
  else if(isset($name))
   $out = "Item '$name' not found into archive ".$archiveInfo['name'];
  else if(isset($alias))
   $out = "Item with alias '$alias' does not exists into ".($into ? " category $into (#".$intoId.") of the " : "")."archive ".$archiveInfo['name'];
  else if(isset($code))
   $out = "Item cod. $code not found into archive ".$archiveInfo['name'];
  else if(isset($barcode))
   $out.= "Item with barcode: ".$barcode." does not exists into archive ".$archiveInfo['name'];
  else
   $out = "Item not exists into archive ".$archiveInfo['name'];
  return array("message"=>$out,"error"=>"ITEM_DOES_NOT_EXISTS");
 }
 
 /* CHECK PERMISSION TO READ */
 $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid'],$db->record['shgrps'],$db->record['shusrs']);
 if(!$m->canRead($sessInfo['uid']))
 {
  // get cat info and re-check.
  if($db->record['cat_id'])
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT shgrps,shusrs FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$db->record['cat_id']."'");
   if($db2->Read())
	$m->set($db->record['_mod'],$db->record['uid'],$db->record['gid'],$db2->record['shgrps'],$db2->record['shusrs']);
   $db2->Close();
  }
 }
 if(!$m->canRead($sessInfo['uid']))
  return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");

 /* OUTPUT */
 $description = $getShortDescription ? substr(strip_tags($db->record['description']),0,512) : $db->record['description'];
 $a = array('id'=>$db->record['id'],'cat_id'=>$db->record['cat_id'],'name'=>$db->record['name'],'desc'=>$description,'ordering'=>$db->record['ordering'],'trash'=>$db->record['trash'],'ctime'=>strtotime($db->record['ctime']),'mtime'=>strtotime($db->record['mtime']),'published'=>$db->record['published'],'aliasname'=>$db->record['aliasname'],'hierarchy'=>$db->record['hierarchy']);
 $a['modinfo'] = $m->toArray($sessInfo['uid']);
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
 
 $fields = array('code_num','code_str','code_ext','contents','keywords','rev_num','hits','lang');
 for($c=0; $c < count($fields); $c++)
 {
  if(isset($db->record[$fields[$c]]))
   $a[$fields[$c]] = $db->record[$fields[$c]];
 }

 if($getCatName)
 {
  if($a['cat_id'])
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT name FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$a['cat_id']."'");
   $db2->Read();
   $a['cat_name'] = $db2->record['name'];
   $db2->Close();
  }
  else
   $a['cat_name'] = "";
 }

 if($verbose)
 {
  $out.= "Item info:\n";
  $out.= "ID: ".$a['id']."\n";
  if($a['cat_id'])
   $out.= "Cat ID: ".$a['cat_id']."\n";
  $out.= "Name: ".$a['name']."\n";
  $out.= "Description: ".$a['desc']."\n";
  $out.= "Published: ".($a['published'] ? "Yes" : "No")."\n";
  $mod = new GMOD($a['modinfo']['mod'],$a['modinfo']['uid'],$a['modinfo']['gid']);
  $out.= "Permissions: ".$mod->toString()."\n";
  if($a['trash'])
   $out.= "Note: this item is into trash\n";
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT fullname FROM gnujiko_users WHERE id='".$a['modinfo']['uid']."'");
  if($db->Read())
   $out.= "Created by ".$db->record['fullname']." at ".date('d/m/Y H:i',$a['ctime'])."\n";
  else
   $out.= "Created by unknown user at ".date('d/m/Y H:i',$a['ctime'])."\n";
  if($a['mtime'])
   $out.= "Last modified at ".date('d/m/Y H:i',$a['mtime'])."\n";
 }
 $db->Close();

 if($get)
  $a = dynarc_parseItemGet($get,$sessid, $shellid, $archiveInfo, $a);

 if($extget)
 {
  $ret = dynarc_parseExtensionGet($extget,$sessid, $shellid, $archiveInfo, $a);
  if($ret)
   $a = $ret;
 }

 return array("message"=>$out, "outarr"=>$a);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_getItemsCount($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-c' : case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : case '--cat-tag' : {$catTag=$args[$c+1]; $c++;} break;
   case '-into' : {$into=$args[$c+1]; $c++;} break;
   case '-linkap' : {$linkAP=$args[$c+1]; $c++;} break;
   case '-linkaid' : {$linkAID=$args[$c+1]; $c++;} break;
   case '-linkid' : {$linkID=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
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
 if($catId)
 {
  $ret = GShell("dynarc cat-info --archive-prefix '".$archiveInfo['prefix']."' -id '$catId'",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
 }
 else if($catTag)
 {
  $ret = GShell("dynarc cat-info --archive-prefix '".$archiveInfo['prefix']."' -tag '$catTag'",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
  $catId = $catInfo['id'];
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

 if($catInfo && $returnCatInfo)
  $outArr['catinfo'] = $catInfo;

 $m = new GMOD();
 $uQry = $m->userQuery($sessid,null,"dynarc_".$archiveInfo['prefix']."_items");

 $db = new AlpaDatabase();
 /* COUNT QRY */
 $countQry = "SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_items WHERE ($uQry)";
 if($catId)
  $countQry.= " AND cat_id='$catId'";
 else if($intoId)
  $countQry.= " AND (hierarchy=',$intoId,' OR hierarchy LIKE ',$intoId,%' OR hierarchy LIKE '%,$intoId,' OR hierarchy LIKE '%,$intoId,%')";
 else if(!$where && !$searchIntoAllCategories && !isset($linkAID) && !isset($linkID))
  $countQry.= " AND cat_id='0'";
 if(isset($linkAID) && isset($linkID))
  $countQry.= " AND lnkarc_id='".$linkAID."' AND lnk_id='".$linkID."'";
 if($where)
  $countQry.= " AND (".$where.")";
 if(!$includeTrash)
  $countQry.= " AND trash='0'";
 
 $db->RunQuery($countQry);
 $db->Read();
 $outArr['count'] = $db->record[0];
 $db->Close();

 $out.= $outArr['count']." items found.";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_itemList($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;
 $_SUM_COLUMNS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-c' : case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : case '--cat-tag' : {$catTag=$args[$c+1]; $c++;} break;
   case '-into' : {$into=$args[$c+1]; $c++;} break;

   case '-linkap' : {$linkAP=$args[$c+1]; $c++;} break;
   case '-linkaid' : {$linkAID=$args[$c+1]; $c++;} break;
   case '-linkid' : {$linkID=$args[$c+1]; $c++;} break;

   case '-get' : {$get=$args[$c+1]; $c++;} break;
   case '-extget' : {$extget=$args[$c+1]; $c++;} break;

   case '-gettotals' : case '-getsum' : case '-totals' : {$_SUM_COLUMNS = explode(",",$args[$c+1]); $c++;} break;

   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '--group-by' : {$groupBy=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
   case '--return-cat-info' : $returnCatInfo=true; break;
   case '--return-serp-info' : $returnSERPInfo=true; break;
   case '--return-path' : $returnPath=true; break;
   case '--all-cat' : $searchIntoAllCategories=true; break;
   case '--include-trash' : $includeTrash=true; break;
   case '--get-full-description' : case '--get-full-desc' : $getFullDescription=true; break;
   case '--get-cat-name' : $getCatName=true; break;
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
 if($catId)
 {
  $ret = GShell("dynarc cat-info --archive-prefix '".$archiveInfo['prefix']."' -id '$catId'",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
 }
 else if($catTag)
 {
  $ret = GShell("dynarc cat-info --archive-prefix '".$archiveInfo['prefix']."' -tag '$catTag'",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
  $catId = $catInfo['id'];
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


 $out = "";
 $outArr = array();

 if($catInfo && $returnCatInfo)
  $outArr['catinfo'] = $catInfo;

 $m = new GMOD();
 $uQry = $m->userQuery($sessid,null,"dynarc_".$archiveInfo['prefix']."_items");

 $db = new AlpaDatabase();
 /* COUNT QRY */
 $countQry = "SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_items WHERE ($uQry)";
 if($catId)
  $countQry.= " AND cat_id='$catId'";
 else if($intoId)
  $countQry.= " AND (hierarchy=',$intoId,' OR hierarchy LIKE ',$intoId,%' OR hierarchy LIKE '%,$intoId,' OR hierarchy LIKE '%,$intoId,%')";
 else if(!$where && !$searchIntoAllCategories && !isset($linkAID) && !isset($linkID))
  $countQry.= " AND cat_id='0'";
 if(isset($linkAID) && isset($linkID))
  $countQry.= " AND lnkarc_id='".$linkAID."' AND lnk_id='".$linkID."'";
 if($where)
  $countQry.= " AND (".$where.")";
 if(!$includeTrash)
  $countQry.= " AND trash='0'";
 
 $db->RunQuery($countQry);
 $db->Read();
 $outArr['count'] = $db->record[0];

 /* SUM */
 if(count($_SUM_COLUMNS))
 {
  $sumcolumnsQryStr = "";
  for($c=0; $c < count($_SUM_COLUMNS); $c++)
   $sumcolumnsQryStr.= ",SUM(".$_SUM_COLUMNS[$c].")";
  $sumQry = "SELECT ".ltrim($sumcolumnsQryStr,',').ltrim($countQry, "SELECT COUNT(*)");
  $outArr['totals'] = array();

  $db->RunQuery($sumQry);
  $db->Read();
  for($i=0; $i < count($_SUM_COLUMNS); $i++)
   $outArr['totals'][$_SUM_COLUMNS[$i]] = $db->record['SUM('.$_SUM_COLUMNS[$i].')'];

 }

 // CHECK LIMIT //
 if($limit && $outArr['count'])
 {
  $x = explode(",",$limit);
  if($x[1])
  {
   $serpRPP = $x[1];
   $serpFrom = $x[0];
  }
  else
  {
   $serpRPP = $x[0];
   $serpFrom = 0;
  }
  if($serpFrom >= $outArr['count'])
   $serpFrom-= $serpRPP;
  if($serpFrom < 0)
   $serpFrom = 0;
  $limit = $serpFrom ? "$serpFrom,$serpRPP" : $serpRPP;
  if($returnSERPInfo)
  {
   $outArr['serpinfo']['resultsperpage'] = $serpRPP;
   $outArr['serpinfo']['currentpage'] = $serpFrom ? floor($serpFrom/$serpRPP)+1 : 1;
   $outArr['serpinfo']['datafrom'] = $serpFrom;
  }
 }

 /* SELECT QRY */
 $selectQry = "SELECT id,hierarchy FROM dynarc_".$archiveInfo['prefix']."_items WHERE ($uQry)";
 if($catId)
 {
  $selectQry.= " AND cat_id='$catId'";
  if($catInfo['def_order_field'] && !$orderBy)
   $orderBy = $catInfo['def_order_field']." ".$catInfo['def_order_method'];
 }
 else if($intoId)
  $selectQry.= " AND hierarchy LIKE '%,$intoId,%'";
 else if(!$where && !$searchIntoAllCategories && !isset($linkAID) && !isset($linkID))
  $selectQry.= " AND cat_id='0'";
 if(isset($linkAID) && isset($linkID))
  $selectQry.= " AND lnkarc_id='".$linkAID."' AND lnk_id='".$linkID."'";
 if($where)
  $selectQry.= " AND (".$where.")";
 if(!$includeTrash)
  $selectQry.= " AND trash='0'";

 if($groupBy)	$selectQry.= " GROUP BY ".$groupBy;
 if(!$orderBy)
  $orderBy = "ordering ASC";
 $selectQry.= " ORDER BY ".$orderBy;

 if($limit)
  $selectQry.= " LIMIT $limit";

 if($returnPath)
  $pathNames = array();

 $db->RunQuery($selectQry);
 if($db->Error)
  return array("message"=>"MySQL Error: ".$db->Error, "error"=>"MYSQL_ERROR");
 while($db->Read())
 {
  $cmd = "dynarc item-info -ap '".$archiveInfo['prefix']."' -id '".$db->record['id']."'";
  if(!$getFullDescription)		$cmd.= " --get-short-description";
  if($getCatName)				$cmd.= " --get-cat-name";
  if($extget)					$cmd.= " -extget \"".$extget."\"";
  if($get)						$cmd.= " -get \"".$get."\"";

  $ret = GShell($cmd,$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   continue;
  if($returnPath)
  {
   if($db->record['hierarchy'] == ",")
	$ret['outarr']['fullpathstring'] = "";
   else
   {
    $db2 = new AlpaDatabase();
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
	 $ret['outarr']['fullpathstring'].= " - ".$pathNames[$x[$c]];
    }
    $db2->Close();
    $ret['outarr']['fullpathstring'] = substr($ret['outarr']['fullpathstring'],3);
   }
  }
  $outArr['items'][] = $ret['outarr'];
  if($verbose)
   $out.= "#".$ret['outarr']['id'].". ".$ret['outarr']['name']."\n";
 }
 $db->Close();
 if($verbose)
  $out.= count($outArr['items'])." items found.";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_itemFind($args, $sessid, $shellid=null, $extraVar=null)
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
   case '-c' : case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : case '--cat-tag' : {$catTag=$args[$c+1]; $c++;} break;
   case '-into' : {$into=$args[$c+1]; $c++;} break;
   case '-get' : {$get=$args[$c+1]; $c++;} break;
   case '-extget' : {$extget=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
   case '--return-cat-info' : $returnCatInfo=true; break;
   case '--return-serp-info' : $returnSERPInfo=true; break;
   case '-field' : {$field=$args[$c+1]; $c++;} break;
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
 if($catId)
 {
  $ret = GShell("dynarc cat-info --archive-prefix '".$archiveInfo['prefix']."' -id '$catId'",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
 }
 else if($catTag)
 {
  $ret = GShell("dynarc cat-info --archive-prefix '".$archiveInfo['prefix']."' -tag '$catTag'",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
  $catId = $catInfo['id'];
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

 $out = "";
 $outArr = array();

 if($catInfo && $returnCatInfo)
  $outArr['catinfo'] = $catInfo;

 $m = new GMOD();
 $uQry = $m->userQuery($sessid,null,"dynarc_".$archiveInfo['prefix']."_items");

 $db = new AlpaDatabase();
 /* COUNT QRY */
 $countQry = "SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_items WHERE ($uQry)";
 if($catId)
  $countQry.= " AND cat_id='$catId'";
 else if($intoId)
  $countQry.= " AND hierarchy LIKE '%,$intoId,%'";
 /*else if(!$where)
  $countQry.= " AND cat_id='0'";*/
 if($where)
  $countQry.= " AND (".$where.")";
 $countQry.= " AND trash='0'";

 if($searchQry && $field)
 {
  $countQry.= " AND ((".$field."=\"".$db->Purify($searchQry)."\") OR (".$field." LIKE \"".$db->Purify($searchQry)."%\") OR (".$field." LIKE \"%"
	.$db->Purify($searchQry)."%\") OR (".$field." LIKE \"%".$db->Purify($searchQry)."\"))";
 }
 
 $db->RunQuery($countQry);
 $db->Read();
 $outArr['count'] = $db->record[0];

 // CHECK LIMIT //
 if($limit && $outArr['count'])
 {
  $x = explode(",",$limit);
  if($x[1])
  {
   $serpRPP = $x[1];
   $serpFrom = $x[0];
  }
  else
  {
   $serpRPP = $x[0];
   $serpFrom = 0;
  }
  if($serpFrom >= $outArr['count'])
   $serpFrom-= $serpRPP;
  if($serpFrom < 0)
   $serpFrom = 0;
  $limit = $serpFrom ? "$serpFrom,$serpRPP" : $serpRPP;
  if($returnSERPInfo)
  {
   $outArr['serpinfo']['resultsperpage'] = $serpRPP;
   $outArr['serpinfo']['currentpage'] = $serpFrom ? floor($serpFrom/$serpRPP)+1 : 1;
   $outArr['serpinfo']['datafrom'] = $serpFrom;
  }
 }

 /* SELECT QRY */
 $selectQry = "SELECT id FROM dynarc_".$archiveInfo['prefix']."_items WHERE ($uQry)";
 if($catId)
  $selectQry.= " AND cat_id='$catId'";
 else if($intoId)
  $selectQry.= " AND hierarchy LIKE '%,$intoId,%'";
 /*else if(!$where)
  $selectQry.= " AND cat_id='0'";*/
 if($where)
  $selectQry.= " AND (".$where.")";
 $selectQry.= " AND trash='0'";

 if($searchQry && $field)
 {
  $selectQry.= " AND ((".$field."=\"".$db->Purify($searchQry)."\") OR (".$field." LIKE \"".$db->Purify($searchQry)."%\") OR (".$field." LIKE \"%"
	.$db->Purify($searchQry)."%\") OR (".$field." LIKE \"%".$db->Purify($searchQry)."\"))";
 }

 $selectQry.= " ORDER BY ".$orderBy;
 if($limit)
  $selectQry.= " LIMIT $limit";

 $db->RunQuery($selectQry);
 while($db->Read())
 {
  $ret = GShell("dynarc item-info --archive-prefix '".$archiveInfo['prefix']."' --get-short-description -id ".$db->record['id'].($extget ? " -extget \"".$extget."\"" : "").($get ? " -get \"".$get."\"" : ""),$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   continue;
  $outArr['items'][] = $ret['outarr'];
  if($verbose)
   $out.= "#".$ret['outarr']['id'].". ".$ret['outarr']['name']."\n";
 }
 $db->Close();
 if($verbose)
  $out.= count($outArr['items'])." items found.";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_itemSort($args, $sessid, $shellid=null, $extraVar=null)
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
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ordering='".($c+1)."' WHERE id='".$ser[$c]."'");
  $db->Close();
  $out.= "done!\n";
  return array("message"=>$out);
 }
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_parseExtensionSet($action,$set,$sessid, $shellid, $archiveInfo, $itemInfo)
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
   default: {
	 if(substr($set,$i,9) == "<![CDATA[")
	 {
	  $qp = strpos($set, "]]>", $i+9);
	  $cntnts = substr($set, $i, ($qp+3)-$i);
	  $cntnts = ltrim($cntnts, "<![CDATA[");
	  $cntnts = rtrim($cntnts, "]]>");
	  $s.=$cntnts;
	  $i = $qp+2; 
	 }
	 else
	  $s.= $set[$i];
	} break;
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
   $ret = call_user_func("dynarcextension_".$extension."_".$action, $args, $sessid, $shellid, $archiveInfo, $itemInfo);
   if(is_array($ret) && $ret['error']) return $ret;
   else if(is_array($ret)) $itemInfo = $ret;
  }
  else
  {
   $out.= "Cannot execute action $action for extension $extension!\n";
   continue;
  }
  $out.= $ret['message'];
 }
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_parseExtensionGet($get,$sessid, $shellid, $archiveInfo, $itemInfo)
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
   $ret = call_user_func("dynarcextension_".$extension."_get", $args, $sessid, $shellid, $archiveInfo, $itemInfo);
   if($ret)
	$itemInfo = $ret;
  }
 }
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_parseItemSet($action,$set,$sessid, $shellid, $archiveInfo, $itemInfo)
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
	   $itemInfo[$var] = $value;
	   $s = "";
	  }
	 } break;
   default: {
	 if(substr($set,$i,9) == "<![CDATA[")
	 {
	  $qp = strpos($set, "]]>", $i+9);
	  $cntnts = substr($set, $i, ($qp+3)-$i);
	  $cntnts = ltrim($cntnts, "<![CDATA[");
	  $cntnts = rtrim($cntnts, "]]>");
	  $s.=$cntnts;
	  $i = $qp+2; 
	 }
	 else
	  $s.= $set[$i];
	} break;
  }
 }
 // EOF PARSER //
 
 
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_parseItemGet($get,$sessid, $shellid, $archiveInfo, $itemInfo)
{
 global $_BASE_PATH;
 $x = explode(",",$get);
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT $get FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 if($db->Read())
 {
  for($c=0; $c < count($x); $c++)
   $itemInfo[$x[$c]] = $db->record[$x[$c]];
 }
 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_itemMove($args, $sessid, $shellid=null, $extraVar=null)
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
   case '-cat' : {$cat=$args[$c+1]; $c++;} break;
   case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
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
 if($cat)
 {
  $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -id $cat -get hierarchy",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];

  /* CHECK PERMISSIONS */
  $sessInfo = sessionInfo($sessid);
  if(!$catInfo['modinfo']['can_write'])
   return array("message"=>"Permission denied.Category is not writeable. !\n", "error"=>"PERMISSION_DENIED");
 }
 else if($catTag)
 {
  $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."' -tag '$catTag' -get hierarchy",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
  $cat = $catInfo['id'];
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
 for($c=0; $c < count($ids); $c++)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT uid,gid,_mod,cat_id,hierarchy".($archiveInfo['sync_enabled'] ? ",syncid" : "")." FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$ids[$c]."'");
  if(!$db->Read())
  {
   $db->Close();
   continue;
  }
  $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid']);
  if(!$m->canWrite($sessInfo['uid']))
   continue;
  $oldCatId = $db->record['cat_id'];

  $db2 = new AlpaDatabase();
  $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET cat_id='".$cat."',hierarchy='"
	.($catInfo['hierarchy'].$catInfo['id'].",")."' WHERE id='".$ids[$c]."'");
  if($archiveInfo['sync_enabled'])
   $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_synclog SET status='MOVED',logtime='".date('Y-m-d H:i:s')."' WHERE syncid='".$db->record['syncid']."'");
  $db2->Close();
  $db->Close();

  $oldItemInfo = $db->record;
  $newItemInfo = $db->record;
  $newItemInfo['hierarchy'] = $catInfo['hierarchy'].$catInfo['id'].",";
  $newItemInfo['cat_id'] = $cat;
  $newItemInfo['old_cat_id'] = $oldCatId;
  // call onmoveitem function if exists //
  if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
  {
   include_once($_BASE_PATH.$archiveInfo['functionsfile']);
   if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_onmoveitem",true))
   {
    $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_onmoveitem", $args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo);
	if(is_array($ret) && $ret['error']) return $ret;
   }
  }
  else if($archiveInfo['inherit'])
  {
   // call onmoveitem function inherited if exists //
   $dbX = new AlpaDatabase();
   $dbX->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
   $dbX->Read();
   if($dbX->record['fun_file'] && file_exists($_BASE_PATH.$dbX->record['fun_file']))
   {
    include_once($_BASE_PATH.$dbX->record['fun_file']);
    if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_onmoveitem",true))
    {
     $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_onmoveitem", $args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo);
     if(is_array($ret) && $ret['error']) return $ret;
    }
   }
   $dbX->Close();
  }

  // call onmoveitem function for all installed extensions //
  for($i=0; $i < count($extensions); $i++)
  {
   /* EXECUTE FUNCTION */
   if(is_callable("dynarcextension_".$extensions[$i]."_onmoveitem",true))
   {
    $ret = call_user_func("dynarcextension_".$extensions[$i]."_onmoveitem", $args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo);
    if(is_array($ret) && $ret['error']) return $ret;
   }
  }


  $itmCount++;
 }

 $out.= $itmCount." items has been moved!";
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_itemCopy($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();
 $ids = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-id' : {$ids[]=$args[$c+1]; $c++;} break;
   case '-cat' : {$cat=$args[$c+1]; $c++;} break;
   case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-set' : {$set=$args[$c+1]; $c++;} break;
   case '-extset' : {$extset=$args[$c+1]; $c++;} break;
   case '-extunset' : {$extunset=$args[$c+1]; $c++;} break;
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
 if($cat || $catTag)
 {
  $ret = GShell("dynarc cat-info -ap '".$archiveInfo['prefix']."'".($cat ? " -id $cat" : " -tag `$catTag`")." -get hierarchy",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
 
 /* CHECK PERMISSIONS */
 $sessInfo = sessionInfo($sessid);
 if(!$catInfo['modinfo']['can_write'])
  return array("message"=>"Permission denied.Category is not writeable. !\n", "error"=>"PERMISSION_DENIED");
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
 for($c=0; $c < count($ids); $c++)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$ids[$c]."'");
  if(!$db->Read())
   continue;
  $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid']);
  if(!$m->canWrite($sessInfo['uid']))
   continue;
  $srcInfo = $db->record;
  $ret = GShell("dynarc new-item -ap `".$archiveInfo['prefix']."` -name `".($name ? $db->Purify($name) : $db->record['name'])."` -desc `".$db->record['description']."`"
	.($catInfo ? " -cat ".$catInfo['id'] : "").($db->record['published'] ? " --publish" : "")
	.($db->record['code_num'] ? " -code-num `".$db->record['code_num']."`" : "")
	.($db->record['code_str'] ? " -code-str `".$db->record['code_str']."`" : "")
	.($db->record['code_ext'] ? " -code-ext `".$db->record['code_ext']."`" : "")
	.($db->record['contents'] ? " -contents `".$db->record['contents']."`" : "")
	.($db->record['keywords'] ? " -keyw `".$db->record['keywords']."`" : "")
	.($db->record['lang'] ? " -lang `".$db->record['lang']."`" : ""), $sessid, $shellid, $extraVar);
  if(!$ret['error'])
  {
   $cloneInfo = $ret['outarr'];

   if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
   {
	// call oncopyitem function if exists //
 	include_once($_BASE_PATH.$archiveInfo['functionsfile']);
 	if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_oncopyitem",true))
 	{
 	 $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_oncopyitem", $sessid, $shellid, $archiveInfo, $cloneInfo, $srcInfo);
	 if(is_array($ret) && $ret['error']) return $ret;
	 $cloneInfo = $ret;
 	}
   }
   else	if($archiveInfo['inherit'])
   {
 	// call oncopyitem function inherited if exists //
 	$db = new AlpaDatabase();
 	$db->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
 	$db->Read();
 	if($db->record['fun_file'] && file_exists($_BASE_PATH.$db->record['fun_file']))
 	{
 	 include_once($_BASE_PATH.$db->record['fun_file']);
 	 if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_oncopyitem",true))
 	 {
 	  $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_oncopyitem", $sessid, $shellid, $archiveInfo, $cloneInfo, $srcInfo);
 	  if(is_array($ret) && $ret['error']) return $ret;
	  $cloneInfo = $ret;
 	 }
 	}
 	$db->Close();
   }
   for($i=0; $i < count($extensions); $i++)
   {
    /* EXECUTE FUNCTION */
    if(is_callable("dynarcextension_".$extensions[$i]."_oncopyitem",true))
    {
     $ret = call_user_func("dynarcextension_".$extensions[$i]."_oncopyitem", $sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo);
     if(is_array($ret) && $ret['error']) return $ret;
    }
   }

   if($set)
    $cloneInfo = dynarc_parseItemSet("set",$set,$sessid, $shellid, $archiveInfo, $cloneInfo);
   if($extset)
   {
    $ret = dynarc_parseExtensionSet("set",$extset,$sessid, $shellid, $archiveInfo, $cloneInfo);
    if($ret)
     $cloneInfo = $ret;
   }
   if($extunset)
    dynarc_parseExtensionSet("unset",$extunset,$sessid, $shellid, $archiveInfo, $cloneInfo);

   $outArr[] = $cloneInfo;
  }
  $itmCount++;
  $db->Close();
 }
 $out.= $itmCount." items has been copied!";
 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function increase_itemcode($code)
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
