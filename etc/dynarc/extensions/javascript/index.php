<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-03-2013
 #PACKAGE: dynarc-javascript-extension
 #DESCRIPTION: Javascript extension for iDoc.
 #VERSION: 2.2beta
 #CHANGELOG: 30-03-2013 : Aggiornato funzioni import ed export.
			 03-12-2012 : Completamento delle funzioni di base.
 #TODO:
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_javascript` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`item_id` INT( 11 ) NOT NULL ,
`src` VARCHAR( 255 ) NOT NULL ,
`content` LONGTEXT NOT NULL ,
INDEX ( `item_id` ) 
)");
 $db->Close();
 return array("message"=>"Javascript extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_javascript`");
 $db->Close();

 return array("message"=>"Javascript extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;
 $ids = array();
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'src' : {$src=$args[$c+1]; $c++;} break;
   case 'content' : {$content=$args[$c+1]; $c++;} break;
  }

 if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '$archivePrefix'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $archiveId = $ret['outarr']['id'];
 }

 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_javascript SET src='".$src."',content='".$db->Purify($content)."' WHERE id='$id' AND item_id='".$itemInfo['id']."'");
 else
 {
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_javascript(item_id,src,content) VALUES('".$itemInfo['id']."','".$src."','".$db->Purify($content)."')");
  $id = mysql_insert_id();
 }
 $itemInfo['last_javascript_id'] = $id;

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_javascript WHERE id='$id' AND item_id='".$itemInfo['id']."'");
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'src' : $src=true; break;
   case 'content' : $content=true; break;
  }

 if(!count($args))
  $all = true;
 
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_javascript WHERE item_id='".$itemInfo['id']."' ORDER BY id ASC");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id']);
  if($src || $all)
   $a['src'] = $db->record['src'];
  if($content || $all)
   $a['content'] = $db->record['content'];
  $itemInfo['javascript'][] = $a;
 }
 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $db = new AlpaDatabase(); $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_javascript WHERE item_id='".$srcInfo['id']."' ORDER BY id ASC");
 while($db->Read())
 {
  $db2->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_javascript(item_id,src,content) VALUES('"
	.$cloneInfo['id']."','".$db->record['src']."','".$db->Purify($db->record['content'])."')");
 }
 $db2->Close(); $db->Close();
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 /* REMOVE ALL javascript */
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_javascript WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_export($sessid, $shellid, $archiveInfo, $itemInfo,$isCategory=false)
{
 if($isCategory)
  return array("xml"=>"");

 $xml = "<javascript>";
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_javascript WHERE item_id='".$itemInfo['id']."' ORDER BY id ASC");
 while($db->Read())
 {
  if(!$db->record['src'] && !$db->record['content'])
   continue;
  $xml.= "<item src=\"".$db->record['src']."\" content=\"".sanitize($db->record['content'])."\"/>";
 }
 $db->Close();
 $xml.= "</javascript>";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_import($sessid, $shellid, $archiveInfo, $itemInfo, $node, $isCategory=false)
{
 if($isCategory)
  return true;

 $list = $node->GetElementsByTagName('item');
 $db = new AlpaDatabase();
 for($c=0; $c < count($list); $c++)
 {
  $n = $list[$c];
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_javascript(item_id,src,content) VALUES('".$itemInfo['id']."','"
	.$n->getString('src')."','".$db->Purify($n->getString('content'))."')");
 }
 $db->Close();
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_javascript_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

