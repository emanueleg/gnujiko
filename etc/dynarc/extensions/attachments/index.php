<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 14-03-2013
 #PACKAGE: dynarc-attachments-extension
 #DESCRIPTION: Attachments extension for Dynarc
 #VERSION: 2.2beta
 #CHANGELOG: 14-03-2013 : Completate funzioni sync.
			 03-12-2012 : Completamento delle funzioni principali.
 #TODO:
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 return array("message"=>"attachments extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 return array("message"=>"attachments extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_set($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'type' : {$type=$args[$c+1]; $c++;} break;
   case 'name' : {$name=$args[$c+1]; $c++;} break;
   case 'desc' : {$desc=$args[$c+1]; $c++;} break;
   case 'keyw' : {$keyw=$args[$c+1]; $c++;} break;
   case 'url' : {$url=$args[$c+1]; $c++;} break;
   case 'tag' : {$tag=$args[$c+1]; $c++;} break;
   case 'tbtag' : {$tbTag=$args[$c+1]; $c++;} break;
   case 'publish' : {$publish=$args[$c+1]; $c++;} break;
  }

 $ret = GShell("dynattachments new -aid ".$archiveInfo['id']." -refid ".$itemInfo['id']." -type `$type` -name `$name` -desc `$desc` -keyw `$keyw` -url `$url` -tag `$tag` -tbtag `$tbTag`".($publish ? " -publish" : ""),$sessid,$shellid);
 if(!$ret['error'])
 {
  if(!$itemInfo['attachments'])
   $itemInfo['attachments'] = array();
  $itemInfo['attachments'][] = $ret['outarr'];
 }
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_get($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'type' : $type=true; break;
   case 'name' : $name=true; break;
   case 'desc' : $desc=true; break;
   case 'keyw' : $keyw=true; break;
   case 'tag' : $tag=true; break;
   case 'tbtag' : $tbTag=true; break;
   case 'url' : $url=true; break;
   case 'ctime' : $ctime=true; break;
   case 'size' : $size=true; break;
   case 'humansize' : $humanSize=true; break;
   case 'published' : $published=true; break;
  }

 if(!count($args))
  $all = true;

 $ret = GShell("dynattachments list -aid ".$archiveInfo['id']." -refid ".$itemInfo['id'],$sessid,$shellid,$archiveInfo);
 if(!$ret['error'])
 {
  for($c=0; $c < count($ret['outarr']['items']); $c++)
  {
   $att = $ret['outarr']['items'][$c];
   $a = array("id"=>$att['id']);
   if($type || $all) $a['type'] = $att['type'];
   if($name || $all) $a['name'] = $att['name'];
   if($desc || $all) $a['description'] = $att['description'];
   if($keyw || $all) $a['keywords'] = $att['keywords'];
   if($tag || $all) $a['tag'] = $att['tag'];
   if($tbTag || $all) $a['tbtag'] = $att['tbtag'];
   if($url || $all) $a['url'] = $att['url'];
   if($ctime || $all) $a['ctime'] = $att['ctime'];
   if($size || $all) $a['size'] = $att['size'];
   if($humanSize || $all) $a['humansize'] = $att['humansize'];
   if($published || $all) $a['published'] = $att['published'];
   if(!$itemInfo['attachments']) $itemInfo['attachments'] = array();
   $itemInfo['attachments'][] = $a;
  }
 }

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $ret = GShell("dynattachments list -aid ".$archiveInfo['id']." -refid ".$srcInfo['id'],$sessid,$shellid);
 if(!$ret['error'])
 {
  for($c=0; $c < count($ret['outarr']['items']); $c++)
  {
   $att = $ret['outarr']['items'][$c];
   $retx = GShell("dynattachments new -aid ".$archiveInfo['id']." -refid ".$cloneInfo['id']." -type `"
	.$att['type']."` -name `".$att['name']."` -desc `".$att['description']."` -keyw `".$att['keywords']."` -url `"
	.$att['url']."` -tag `".$att['tag']."` -tbtag `".$att['tbtag']."`".($att['published'] ? " -publish" : ""),$sessid,$shellid);
   if(!$retx['error'])
   {
	if(!$cloneInfo['attachments']) $cloneInfo['attachments'] = array();
	$cloneInfo['attachments'][] = $retx['outarr'];
   }
  }
 }
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_export($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $attachments = array();
 $ret = GShell("dynattachments list -aid ".$archiveInfo['id'].($isCategory ? " -cat ".$itemInfo['id'] : " -refid ".$itemInfo['id']),$sessid,$shellid,$archiveInfo);
 if($ret['error'])
  return $ret;
 if(!count($ret['outarr']['items']))
  return;

 $xml = "<attachments>";
 $fields = array('type','name','description','keywords','tag','tbtag','ctime','size','humansize','published');
 for($c=0; $c < count($ret['outarr']['items']); $c++)
 {
  $xml.= "<item";
  $att = $ret['outarr']['items'][$c];
  for($i=0; $i < count($fields); $i++)
  {
   if($att[$fields[$i]])
	$xml.= " ".$fields[$i]."=\"".sanitize($att[$fields[$i]])."\"";
  }
  $url = sanitize($att['url']);
  if($att['type'] != "WEB")
  {
   $attachments[] = $att['url'];
   if((strpos($url,$_USERS_HOMES) !== false) && (strpos($url,$_USERS_HOMES) == 0))
   {
	$url = substr($url,strlen($_USERS_HOMES)); // removes $_USERS_HOMES from path //
	$url = substr($url,strpos($url,"/")+1); // removes the first directory (which will of course be the user folder) from path //
   }
  }
  $xml.= " url=\"".$url."\"";
  $xml.= "/>\n";
 }
 $xml.= "</attachments>\n";
 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_import($sessid, $shellid, $archiveInfo, $itemInfo, $extNode, $isCategory=false)
{
 global $_USER_PATH;

 $list = $extNode->GetElementsByTagName('item');
 for($c=0; $c < count($list); $c++)
 {
  $n = $list[$c];
  $publish = $n->getString('published') ? true : false;
  if($n->getString('type') != "WEB")
   $url = $_USER_PATH.$n->getString('url');
  else
   $url = $n->getString('url');

  $ret = GShell("dynattachments new -aid ".$archiveInfo['id'].($isCategory ? " -cat ".$itemInfo['id'] : " -refid ".$itemInfo['id'])." -type `"
	.$n->getString('type')."` -name `".$n->getString('name')."` -desc `".$n->getString('description')."` -keyw `"
	.$n->getString('keywords')."` -url `$url` -tag `".$n->getString('tag')."` -tbtag `".$n->getString('tbtag')."`"
	.($publish ? " -publish" : ""),$sessid,$shellid);
 }
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;

 if($isCategory)
  return true;

 $attachments = array();
 $ret = GShell("dynattachments list -aid ".$archiveInfo['id']." ".($isCategory ? "-cat ".$itemInfo['id'] : "-refid ".$itemInfo['id']),$sessid,$shellid,$archiveInfo);
 if($ret['error'])
  return $ret;
 if(!count($ret['outarr']['items']))
  return;

 $xml = "<attachments>";
 $fields = array('type','name','description','keywords','tag','tbtag','ctime','size','humansize','published');
 for($c=0; $c < count($ret['outarr']['items']); $c++)
 {
  $xml.= "<item";
  $att = $ret['outarr']['items'][$c];
  for($i=0; $i < count($fields); $i++)
  {
   if($att[$fields[$i]])
	$xml.= " ".$fields[$i]."=\"".sanitize($att[$fields[$i]])."\"";
  }
  $url = $att['url'];
  if($att['type'] != "WEB")
  {
   $attachments[] = $att['url'];
   if((strpos($url,$_USERS_HOMES) !== false) && (strpos($url,$_USERS_HOMES) == 0))
   {
	$url = substr($url,strlen($_USERS_HOMES)); // removes $_USERS_HOMES from path //
	$url = substr($url,strpos($url,"/")+1); // removes the first directory (which will of course be the user folder) from path //
   }
  }
  $xml.= " url=\"".sanitize($url)."\"";
  $xml.= "/>\n";
 }
 $xml.= "</attachments>\n";
 return array('xml'=>$xml,'attachments'=>$attachments); 
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $extNode, $isCategory=false)
{
 global $_USERS_HOMES;

 $list = $extNode->GetElementsByTagName('item');
 for($c=0; $c < count($list); $c++)
 {
  $n = $list[$c];
  $publish = $n->getString('published') ? true : false;
  if($n->getString('type') != "WEB")
   $url = $_USERS_HOMES.$n->getString('url');
  else
   $url = $n->getString('url');

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynattachments WHERE archive_id='".$archiveInfo['id']."' AND ".($isCategory ? "cat_id='".$itemInfo['id']."'" : "item_id='".$itemInfo['id']."'")." AND url='".$url."' LIMIT 1");
  if($db->Read())
   GShell("dynattachments edit -id `".$db->record['id']."` -type `".$n->getString('type')."` -name `".$n->getString('name')."` -desc `"
	.$n->getString('description')."` -keyw `".$n->getString('keywords')."` -url `$url` -tag `".$n->getString('tag')."` -tbtag `"
	.$n->getString('tbtag')."`".($publish ? " -publish" : ""),$sessid,$shellid);
  else
   GShell("dynattachments new -aid ".$archiveInfo['id']." ".($isCategory ? "-cat ".$itemInfo['id'] : "-refid ".$itemInfo['id'])." -type `"
	.$n->getString('type')."` -name `".$n->getString('name')."` -desc `".$n->getString('description')."` -keyw `"
	.$n->getString('keywords')."` -url `$url` -tag `".$n->getString('tag')."` -tbtag `".$n->getString('tbtag')."`"
	.($publish ? " -publish" : ""),$sessid,$shellid);
  $db->Close();
 }
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_attachments_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

