<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 10-06-2014
 #PACKAGE: dynarc-thumbnails-extensions
 #DESCRIPTION: Thumbnails support for categories and items.
 #VERSION: 2.1beta
 #CHANGELOG: 10-06-2014 : Aggiunta funzione onarchiveempty
			 21-11-2012 : Bug fix into thumbnails catget.
 #TODO:Rifare funzione import & export e completare funzioni syncimport & syncexport.
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` ADD `thumb_img` VARCHAR( 255 ) NOT NULL ,
ADD `thumb_img_2` VARCHAR( 255 ) NOT NULL ,
ADD `thumb_img_3` VARCHAR( 255 ) NOT NULL ,
ADD `thumb_img_4` VARCHAR( 255 ) NOT NULL ,
ADD `thumb_img_5` VARCHAR( 255 ) NOT NULL ,
ADD `thumb_img_6` VARCHAR( 255 ) NOT NULL");

 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_categories` ADD `thumb_img` VARCHAR( 255 ) NOT NULL ,
ADD `thumb_mode` TINYINT( 1 ) NOT NULL");

 $db->Close();
 return array("message"=>"Thumbnails extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` DROP `thumb_img`, DROP `thumb_img_2`, DROP `thumb_img_3`, DROP `thumb_img_4`, DROP `thumb_img_5`, DROP `thumb_img_6`,");
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_categories` DROP `thumb_img`,  DROP `thumb_mode`");
 $db->Close();

 return array("message"=>"Thumbnails extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_catset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'img' : case 'src' : {$imgSrc=$args[$c+1]; $c++;} break;
   case 'mode' : {$mode=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 $q = "";
 if(isset($imgSrc))
  $q.= ",thumb_img='$imgSrc'";
 if(isset($mode))
  $q.= ",thumb_mode='$mode'";
 
 if($q)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET ".ltrim($q,",")." WHERE id='".$catInfo['id']."'");
 $db->Close();

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_thumbnails_catset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 $set = array();

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'add' : case 'new' : {$addImg=$args[$c+1]; $c++;} break;
   default : {
	 if(is_numeric($args[$c]))
	  $set[$args[$c]] = $args[$c+1];

	} break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT thumb_img,thumb_img_2,thumb_img_3,thumb_img_4,thumb_img_5,thumb_img_6 FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='"
	.$itemInfo['id']."'");
 $db->Read();
 $thumbs = array();
 for($c=0; $c < 6; $c++)
 {
  $field = "thumb_img".($c > 0 ? "_".($c+1) : "");
  if($db->record[$field] != "")
   $thumbs[] = $set[$c] ? $set[$c] : $db->record[$field];
 }

 if($addImg)
  $thumbs[] = $addImg;
 
 $q = "";
 for($c=0; $c < 6; $c++)
 {
  $q.= ",thumb_img".($c > 0 ? "_".($c+1) : "")."='".($thumbs[$c] ? $thumbs[$c] : "")."'";
 }

 if($q)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 global $_BASE_PATH;

 $unset=array();

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'all' : {
	 for($c=0; $c < 6; $c++)
	  $unset[$c] = true;
	} break;

   default : {
	 if(is_numeric($args[$c]))
	  $unset[$args[$c]] = true;
	} break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT thumb_img,thumb_img_2,thumb_img_3,thumb_img_4,thumb_img_5,thumb_img_6 FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='"
	.$itemInfo['id']."'");
 $db->Read();
 $thumbs = array();
 for($c=0; $c < 6; $c++)
 {
  $field = "thumb_img".($c > 0 ? "_".($c+1) : "");
  if(($db->record[$field] != "") && !$unset[$c])
   $thumbs[] = $db->record[$field];
 }

 $q = "";
 for($c=0; $c < 6; $c++)
 {
  $q.= ",thumb_img".($c > 0 ? "_".($c+1) : "")."='".($thumbs[$c] ? $thumbs[$c] : "")."'";
 }

 if($q)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_catget($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'mode' : $mode=true; break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT thumb_img,thumb_mode FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$catInfo['id']."'");
 $db->Read();
 $catInfo['thumb_img'] = $db->record['thumb_img'];
 if($mode)
  $catInfo['thumb_mode'] = $db->record['thumb_mode'];
 if($db->record['thumb_mode'] == 1)
 {
  $db->RunQuery("SELECT thumb_img FROM dynarc_".$archiveInfo['prefix']."_items WHERE cat_id='".$catInfo['id']."' AND trash='0' AND thumb_img!='' ORDER BY ordering ASC LIMIT 1");
  if($db->Read())
   $catInfo['thumb_img'] = $db->record['thumb_img'];
 }
 $db->Close();

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_thumbnails_catget($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'all' : $all=true; break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT thumb_img,thumb_img_2,thumb_img_3,thumb_img_4,thumb_img_5,thumb_img_6 FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 for($c=0; $c < 6; $c++)
 {
  $f = 'thumb_img'.($c>0 ? "_".($c+1) : "");
  if($db->record[$f])
   $itemInfo['thumbnails'][] = $db->record[$f];
 }
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT thumb_img,thumb_img_2,thumb_img_3,thumb_img_4,thumb_img_5,thumb_img_6 FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$srcInfo['id']."'");
 $db->Read();
 $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET thumb_img='".$db->record['thumb_img']."',thumb_img_2='"
	.$db->record['thumb_img_2']."',thumb_img_3='".$db->record['thumb_img_3']."',thumb_img_4='"
	.$db->record['thumb_img_4']."',thumb_img_5='".$db->record['thumb_img_5']."',thumb_img_6='".$db->record['thumb_img_6']."' WHERE id='"
	.$cloneInfo['id']."'");
 $db->Close();
 $db2->Close();
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_onarchiveempty($args, $sessid, $shellid, $archiveInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_export($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES, $_BASE_PATH;

 $xml = "<thumbnails>";
 $attachments = array();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT thumb_img,thumb_img_2,thumb_img_3,thumb_img_4,thumb_img_5,thumb_img_6 FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 for($c=0; $c < 6; $c++)
 {
  $f = 'thumb_img'.($c>0 ? "_".($c+1) : "");
  if($db->record[$f])
  {
   $srcFile = $db->record[$f];
   $x = explode("/",$srcFile);
   if(is_array($x) && count($x) && (($x[0]."/") == $_USERS_HOMES))
   {
	unset($x[0]); unset($x[1]); $destFile = implode("/",$x);
   }
   else
    $destFile = $srcFile;

   $attachments[] = array("src"=>$srcFile, "dest"=>$destFile);

   $pos = strrpos($srcFile, ".");
   $thumbSrc = ""; $thumbDest = "";
   if($pos !== false)
   {
	$ext = substr($srcFile, $pos+1);
    $thumbSrc = substr($srcFile, 0, $pos)."-thumb.".$ext;
	if(file_exists($_BASE_PATH.$thumbSrc))
	{
     $x = explode("/",$thumbSrc);
     if(is_array($x) && count($x) && (($x[0]."/") == $_USERS_HOMES))
     {
	  unset($x[0]); unset($x[1]); $thumbDest = implode("/",$x);
     }
     else
      $thumbDest = $thumbSrc; 
	 $attachments[] = array("src"=>$thumbSrc, "dest"=>$thumbDest);
	}
   }
   $xml.= "<thumbnail src=\"{HOMEDIR}/".$destFile."\"".($thumbDest ? " thumb=\"{HOMEDIR}/".$thumbDest."\"" : "")."/>";
  }
 }
 $db->Close();
 $xml.= "</thumbnails>";

 return array('xml'=>$xml, 'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_import($sessid, $shellid, $archiveInfo, $itemInfo, $node, $isCategory=false)
{
 global $_BASE_PATH, $_USERS_HOMES;

 if($isCategory)
  return ;

 if(!$node)
  return;

 $sessInfo = sessionInfo($sessid);

 if($sessInfo['uname'] == "root")
  $homedir = "";
 else if($sessInfo['uid'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $homedir = $_USERS_HOMES.$db->record['homedir'];
  $db->Close();
 }
 else
  $homedir = "tmp";

 $qry = "";
 $list = $node->getElementsByTagName('thumbnail');
 for($c=0; $c < count($list); $c++)
 {
  $n = $list[$c];
  $src = $n->getString('src');
  if($c == 0)	$qry.= "thumb_img='";
  else          $qry.= ",thumb_img_".($c+1)."='";
  $qry.= str_replace("{HOMEDIR}",$homedir,$src)."'";
 }
 if($qry)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".$qry." WHERE id='".$itemInfo['id']."'");
  $db->Close();
 }

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_thumbnails_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

