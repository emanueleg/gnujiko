<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-02-2016
 #PACKAGE: printmodels-config
 #DESCRIPTION: Print Model basic info extension for Dynarc.
 #VERSION: 2.4beta
 #CHANGELOG: 23-02-2016 : Aggiunto campo orientation.
			 01-05-2015 : Aggiunta prima e ultima pagina.
			 23-03-2015 : Aggiunto campo format.
			 03-12-2012 : Completamento delle funzioni principali.
 #TODO:Rifare funzione import & export e completare funzioni syncimport & syncexport.
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` ADD `thumbdata` MEDIUMTEXT NOT NULL, 
	ADD `format` VARCHAR(10) NOT NULL,
	ADD `firstpage_content` LONGTEXT NOT NULL,
	ADD `lastpage_content` LONGTEXT NOT NULL,
	ADD `orientation` VARCHAR(1) NOT NULL");
 $db->Close();
 return array("message"=>"PrintModelInfo extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` DROP `thumbdata`, DROP `format`, 
	DROP `firstpage_content`, DROP `lastpage_content`, DROP `orientation`");
 $db->Close();

 return array("message"=>"PrintModelInfo extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_catset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_printmodelinfo_catset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'thumbdata' : {$thumbData=$args[$c+1]; $c++;} break;
   case 'format' : {$format=$args[$c+1]; $c++;} break;
   case 'firstpagecontent' : {$firstPageContent=$args[$c+1]; $c++;} break;
   case 'lastpagecontent' : {$lastPageContent=$args[$c+1]; $c++;} break;
   case 'orientation' : {$orientation=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 $q="";
 if(isset($thumbData))			$q.= ",thumbdata='".$thumbData."'";
 if(isset($format))				$q.= ",format='".$format."'";
 if(isset($firstPageContent))	$q.= ",firstpage_content='".$db->Purify($firstPageContent)."'";
 if(isset($lastPageContent))	$q.= ",lastpage_content='".$db->Purify($lastPageContent)."'";
 if(isset($orientation))		$q.= ",orientation='".$orientation."'";

 if($q)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");
 $db->Close();


 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_catget($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_printmodelinfo_catget($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'thumbdata' : $thumbData=true; break;
   case 'format' : $format=true; break;
   case 'firstpagecontent' : $firstPageContent=true; break;
   case 'lastpagecontent' : $lastPageContent=true; break;
   case 'orientation' : $orientation=true; break;
  }

 if(!count($args))
  $all=true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT thumbdata,format,firstpage_content,lastpage_content,orientation FROM dynarc_"
	.$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 if($thumbData || $all)			$itemInfo['thumbdata'] = $db->record['thumbdata'];
 if($format || $all)			$itemInfo['format'] = $db->record['format'];
 if($firstPageContent || $all)	$itemInfo['firstpage_content'] = $db->record['firstpage_content'];
 if($lastPageContent || $all)	$itemInfo['lastpage_content'] = $db->record['lastpage_content'];
 if($orientation || $all)		$itemInfo['orientation'] = $db->record['orientation'];
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT thumbdata,format,firstpage_content,lastpage_content,orientation FROM dynarc_"
	.$archiveInfo['prefix']."_items WHERE id='".$srcInfo['id']."'");
 $db->Read();
 $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET thumbdata='".$db->record['thumbdata']."',format='"
	.$db->record['format']."',firstpage_content='".$db2->Purify($db->record['firstpage_content'])."',lastpage_content='"
	.$db2->Purify($db->record['lastpage_content'])."',orientation='".$db->record['orientation']."' WHERE id='".$cloneInfo['id']."'");
 $db2->Close();
 $db->Close();

 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_export($sessid, $shellid, $archiveInfo, $itemInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT thumbdata,format,firstpage_content,lastpage_content,orientation FROM dynarc_"
	.$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 $xml = "<printmodelinfo thumbdata=\"".$db->record['thumbdata']."\" format=\""
	.$db->record['format']."\" orientation=\"".$db->record['orientation']."\" firstpagecontent=\""
	.sanitize($db->record['firstpage_content'])."\" lastpagecontent=\"".sanitize($db->record['lastpage_content'])."\"/>";
 $db->Close();
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_import($sessid, $shellid, $archiveInfo, $itemInfo, $node)
{
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET thumbdata='".$node->getString('thumbdata')."',format='"
	.$node->getString('format')."',orientation='".$node->getString('orientation')."',firstpage_content='"
	.$db->Purify($node->getString('firstpagecontent'))."',lastpage_content='"
	.$db->Purify($node->getString('lastpagecontent'))."' WHERE id='".$itemInfo['id']."'");
 if($db->Error)
  return array('message'=>"MySQL Error:".$db->Error, 'error'=>'MYSQL_ERROR');
 $db->Close();
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_printmodelinfo_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

