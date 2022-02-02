<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 03-12-2012
 #PACKAGE: dynarc-coding-extension
 #DESCRIPTION: Official Gnujiko coding system. Extension for Dynarc
 #VERSION: 2.1beta
 #CHANGELOG: 03-12-2012 : Completamento delle funzioni principali.
 #TODO:Rifare funzione import & export e completare funzioni syncimport & syncexport.
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` ADD `aliasname` VARCHAR( 255 ) NOT NULL ,
ADD `code_num` INT( 11 ) NOT NULL ,
ADD `code_str` VARCHAR( 64 ) NOT NULL ,
ADD `code_ext` VARCHAR( 8 ) NOT NULL ,
ADD `md5` VARCHAR( 32 ) NOT NULL ,
ADD INDEX ( `aliasname` , `code_num` , `code_str` , `code_ext` , `md5` )");

 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_categories` ADD `aliasname` VARCHAR(64) , ADD INDEX (`aliasname`)");

 $db->Close();
 return array("message"=>"Coding system extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` DROP `aliasname`, DROP `code_num`, DROP `code_str`, DROP `code_ext`, DROP `md5`");
 $db->Close();

 return array("message"=>"Coding system extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;
 $ids = array();
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'alias' : {$aliasName=$args[$c+1]; $c++;} break;
   case 'code_num' : {$codeNum=$args[$c+1]; $c++;} break;
   case 'code_str' : {$codeStr=$args[$c+1]; $c++;} break;
   case 'code_ext' : {$codeExt=$args[$c+1]; $c++;} break;
  }

 if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveId = $ret['outarr']['id'];
 }

 $db = new AlpaDatabase();
 if($aliasName)
 {
  $alias = str_replace(" ","_",strtolower(html_entity_decode($aliasName,ENT_QUOTES,"UTF-8")));
  $k = array("?", "#", "'", '"', "&", "/", "\"");
  $v = array("", "-", "_", "_", "and", "-", "_");
  $alias = str_replace($k,$v,$alias);
  $alias = str_replace("__","_",$alias);
  $q.= ",aliasname='".$alias."'";
 }
 if(isset($codeNum))
  $q.= ",code_num='".$codeNum."'";
 if(isset($codeStr))
  $q.= ",code_str='".$db->Purify($codeStr)."'";
 if(isset($codeExt))
  $q.= ",code_ext='".$db->Purify($codeExt)."'";
  
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'alias' : $aliasName=true; break;
   case 'code_num' : $codeNum=true; break;
   case 'code_str' : $codeStr=true; break;
   case 'code_ext' : $codeExt=true; break;
  }

 if(!count($args))
  $all = true;
 
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT aliasname,code_num,code_str,code_ext FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();

 if($aliasName || $all)
  $itemInfo['alias'] = $db->record['aliasname'];
 if($codeNum || $all)
  $itemInfo['code_num'] = $db->record['code_num'];
 if($codeStr || $all)
  $itemInfo['code_str'] = $db->record['code_str'];
 if($codeExt || $all)
  $itemInfo['code_ext'] = $db->record['code_ext'];

 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $db = new AlpaDatabase(); $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT aliasname,code_num,code_str,code_ext FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$srcInfo['id']."'");
 $db->Read();
 $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET aliasname='".$db->record['aliasname']."',code_num='"
	.$db->record['code_num']."',code_str='".$db2->Purify($db->record['code_str'])."',code_ext='".$db->record['code_ext']."' WHERE id='"
	.$cloneInfo['id']."'");
 $db2->Close(); $db->Close();
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_export($sessid, $shellid, $archiveInfo, $itemInfo)
{
 $xml = "";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_import($sessid, $shellid, $archiveInfo, $itemInfo, $node)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_coding_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

