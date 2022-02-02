<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-10-2016
 #PACKAGE: dynarc-css-extension
 #DESCRIPTION: CSS extension for Dynarc.
 #VERSION: 2.2beta
 #CHANGELOG: 24-10-2016 : MySQLi integration.
			 30-03-2013 : Aggiornate funzioni import ed export.
			 03-12-2012 : Completamento delle funzioni principali.
 #TODO:
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_css` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`item_id` INT( 11 ) NOT NULL ,
`src` VARCHAR( 255 ) NOT NULL ,
`content` LONGTEXT NOT NULL ,
INDEX ( `item_id` ) 
)");
 $db->Close();
 return array("message"=>"CSS extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_css`");
 $db->Close();

 return array("message"=>"CSS extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
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
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_css SET src='".$src."',content='".$db->Purify($content)."' WHERE id='$id' AND item_id='".$itemInfo['id']."'");
 else
 {
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_css(item_id,src,content) VALUES('".$itemInfo['id']."','".$src."','".$db->Purify($content)."')");
  $id = $db->GetInsertId();
 }
 $itemInfo['last_css_id'] = $id;

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_css WHERE id='$id' AND item_id='".$itemInfo['id']."'");
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
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
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_css WHERE item_id='".$itemInfo['id']."' ORDER BY id ASC");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id']);
  if($src || $all)
   $a['src'] = $db->record['src'];
  if($content || $all)
   $a['content'] = $db->record['content'];
  $itemInfo['css'][] = $a;
 }
 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $db = new AlpaDatabase(); $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_css WHERE item_id='".$srcInfo['id']."' ORDER BY id ASC");
 while($db->Read())
 {
  $db2->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_css(item_id,src,content) VALUES('"
	.$cloneInfo['id']."','".$db->record['src']."','".$db->Purify($db->record['content'])."')");
 }
 $db2->Close(); $db->Close();
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 /* REMOVE ALL css */
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_css WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_export($sessid, $shellid, $archiveInfo, $itemInfo,$isCategory=false)
{
 if($isCategory)
  return array("xml"=>"");

 $xml = "<css>";
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_css WHERE item_id='".$itemInfo['id']."' ORDER BY id ASC");
 while($db->Read())
 {
  if(!$db->record['src'] && !$db->record['content'])
   continue;
  $xml.= "<item src=\"".$db->record['src']."\" content=\"".sanitize($db->record['content'])."\"/>";
 }
 $db->Close();
 $xml.= "</css>";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_import($sessid, $shellid, $archiveInfo, $itemInfo, $node, $isCategory=false)
{
 if($isCategory)
  return true;

 $list = $node->GetElementsByTagName('item');
 $db = new AlpaDatabase();
 for($c=0; $c < count($list); $c++)
 {
  $n = $list[$c];
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_css(item_id,src,content) VALUES('".$itemInfo['id']."','"
	.$n->getString('src')."','".$db->Purify($n->getString('content'))."')");
 }
 $db->Close();
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_css_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//

