<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-05-2017
 #PACKAGE: dynarc
 #DESCRIPTION: Import and Export functions for Dynarc
 #VERSION: 2.9beta
 #CHANGELOG: 15-05-2017 : Aggiunto parametro extraVar su tutte le funzioni.
			 26-12-2014 : Aggiornate funzioni import ed export.
			 19-09-2014 : Bug fix on function dynarc_importItem.
			 18-02-2014 : Bug fix su funzioni import export.
			 11-04-2013 : Sistemato i permessi ai files.
			 06-04-2013 : Completato funzioni overwrite negli articoli (manca nelle categorie) e aggiunto parametro -group su dynarc import
			 14-03-2013 : Bug fix su funzione category import ed export.
 #TODO:  fare funzione overwrite su esporta categorie.
 
*/

global $_BASE_PATH;
include_once($_BASE_PATH."include/userfunc.php");
include_once($_BASE_PATH."etc/dynarc/archives.php");

//-------------------------------------------------------------------------------------------------------------------//
function dynarc_import($args, $sessid, $shellid=0, $extraVar=null)
{
 global $_BASE_PATH, $_USERS_HOMES, $_USER_PATH;
 $out = "";
 $outArr = array("items"=>array(), "categories"=>array());
 $catInfo = null;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-f' : case '-i' : case '-file' : {$fileName=ltrim($args[$c+1],"/"); $c++;} break;
   case '-xml' : {$xmlContent=$args[$c+1]; $c++;} break;
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break; // solo in caso di importazione di un unico documento.

   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : case '--cat-tag' : {$catTag=$args[$c+1]; $c++;} break;
   
   case '-group' : {$group=$args[$c+1]; $c++;} break;

   case '--overwrite' : $overwrite=true; break;
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

 if($catId)
 {
  $ret = GShell("dynarc cat-info -aid '".$archiveInfo['id']."' -id $catId",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
 }
 else if($catTag)
 {
  $ret = GShell("dynarc cat-info -aid '".$archiveInfo['id']."' -tag '$catTag'",$sessid,$shellid,$archiveInfo);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
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

 /* get extensions */
 $archiveInfo['extensions'] = array();
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' ORDER BY id ASC");
 while($db->Read())
  $archiveInfo['extensions'][] = $db->record['extension_name'];
 $db->Close();

 if($fileName)
 {
  /* detect home dir */
  $sessInfo = sessionInfo($sessid);
  if($sessInfo['uname'] == "root")
   $_USER_PATH = "";
  else if($sessInfo['uid'])
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
   $db->Read();
   $_USER_PATH = $_USERS_HOMES.$db->record['homedir']."/";
   $db->Close();
  }
  else
   $_USER_PATH= "tmp/";

  if(!file_exists($_BASE_PATH.$_USER_PATH.$fileName))
   return array("message"=>"File $fileName does not exists!","error"=>"FILE_DOES_NOT_EXISTS");

  /* detect if is a ZIP file */
  if(strtolower(substr($_BASE_PATH.$_USER_PATH.$fileName,-4,4)) == ".zip")
  {
   $ret = GShell("unzip -i `".$fileName."`",$sessid,$shellid, $extraVar);
   if($ret['error'])
	return $ret;
   $out.= "Unzip $fileName... success!\n";
   $out.= "Find first xml file to import...";
   for($c=0; $c < count($ret['outarr']['files']); $c++)
   {
	$f = $ret['outarr']['files'][$c];
	if(strtolower(substr($f,-4,4)) == ".xml")
	{
	 $xmlFile = $f;
	 break;
	}
   }
   if(!$xmlFile)
   {
	$out.= " failed! No xml files found.\n";
    return array('message'=>$out,'error'=>"INVALID_FILE_NAME");
   }
  }
  else
   $xmlFile = $fileName;
 }

 if($xmlFile)
  gshPreOutput($shellid, $out."Import data into archive '".$archiveInfo['name']."' from $xmlFile");
 else if($xmlContent)
  gshPreOutput($shellid, $out."Import data into archive '".$archiveInfo['name']."' from XML");

  $out = "";
  $xml = new GXML();
  if($xmlFile)
   $xml->LoadFromFile($_BASE_PATH.$_USER_PATH.$xmlFile);
  else if($xmlContent)
   $xml->LoadFromString($xmlContent);
  /* Estimate elements */
  $estimate = count($xml->GetElementsByTagName('item'));
  $list = $xml->GetElementsByTagName('category');
  for($c=0; $c < count($list); $c++)
   $estimate+= (1 + dynarc_estimateImport($list[$c]));

  gshPreOutput($shellid, "Estimated elements to import: ".$estimate, "ESTIMATION", null, "DEFAULT", array('estimated_elements'=>$estimate));

  /* Import items */
  $title = "";
  $list = $xml->GetElementsByTagName('item');
  for($c=0; $c < count($list); $c++)
  {
   if($c == 0)
	$title = $name;
   $ret = dynarc_importItem($sessid, $shellid, $archiveInfo, $catInfo, $list[$c], $group, $overwrite, $title);
   if($ret['error'])
	return $ret;
   $outArr['items'][] = $ret;
  }
  /* Import categories */
  $list = $xml->GetElementsByTagName('category');
  for($c=0; $c < count($list); $c++)
  {
   $ret = dynarc_importCategory($sessid, $shellid, $archiveInfo, $catInfo, $list[$c], $group, $overwrite);
   if($ret['error'])
	return $ret;
   $outArr['categories'][] = $ret;
  }

 $out.= "done!";

 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_export($args, $sessid, $shellid=0, $extraVar=null)
{
 global $_BASE_PATH, $_USERS_HOMES, $_DEFAULT_FILE_PERMS;
 $out = "";
 $outArr = array();

 $ids = array();
 $cats = array();
 $catTags = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;

   case '-cat' : {$cats[]=$args[$c+1]; $c++;} break;
   case '-ct' : case '--cat-tag' : {$catTags[]=$args[$c+1]; $c++;} break;

   case '-id' : {$ids[]=$args[$c+1]; $c++;} break;
   case '-all' : $exportAll = true; break;
   case '-f' : case '-o' : case '-file' : {$fileName=$args[$c+1]; $c++;} break;
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

 /* get extensions */
 $archiveInfo['extensions'] = array();
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' ORDER BY id ASC");
 while($db->Read())
  $archiveInfo['extensions'][] = $db->record['extension_name'];
 $db->Close();

 for($c=0; $c < count($catTags); $c++)
 {
  $ret = GShell("dynarc cat-info -aid '".$archiveInfo['id']."' -tag `".$catTags[$c]."`", $sessid, $shellid, $archiveInfo);
  if($ret['error'])
   return $ret;
  $cats[] = $ret['outarr']['id'];
 }

 if($exportAll)
 {
  $ret = GShell("dynarc item-list -aid `".$archiveInfo['id']."`",$sessid, $shellid, $archiveInfo);
  if(!$ret['error'])
  {
   for($c=0; $c < count($ret['outarr']['items']); $c++)
	$ids[] = $ret['outarr']['items'][$c]['id'];
  }
  $ret = GShell("dynarc cat-list -aid `".$archiveInfo['id']."`",$sessid,$shellid,$archiveInfo);
  if(!$ret['error'])
  {
   for($c=0; $c < count($ret['outarr']); $c++)
	$cats[] = $ret['outarr'][$c]['id'];
  }
 }

 gshPreOutput($shellid, "Export data from archive '".$archiveInfo['name']."'");

 /* Estimate elements */
 $estimate = count($ids);
 $m = new GMOD();
 $uQry = $m->userQuery($sessid);
 for($c=0; $c < count($cats); $c++)
 {
  $estimate+= 1;
  $ret = GShell("dynarc cat-tree -aid `".$archiveInfo['id']."` -cat `".$cats[$c]."` --serialize-only",$sessid,$shellid,$archiveInfo);
  if(!$ret['error'])
  {
   if($ret['outarr']['serialize'])
   {
    $db = new AlpaDatabase();
    $x = explode(",",$ret['outarr']['serialize']);
    for($i=0; $i < count($x); $i++)
    {
	 $estimate+= 1;
 	 $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_items WHERE ($uQry) AND cat_id='".$x[$i]."' AND trash='0'");
	 $db->Read();
	 $estimate+= $db->record[0];
    }
    $db->Close();
   }
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_items WHERE ($uQry) AND cat_id='".$cats[$c]."' AND trash='0'");
   $db->Read();
   $estimate+= $db->record[0];
   $db->Close();
  }
 }

 gshPreOutput($shellid, "Estimated elements to export: ".$estimate, "ESTIMATION", null, "DEFAULT", array('estimated_elements'=>$estimate));

 $xml = "<xml archiveprefix='".$archiveInfo['prefix']."'>";
 $attachments = array();
 /* Export items */
 for($c=0; $c < count($ids); $c++)
 {
  $ret = dynarc_exportItem($sessid, $shellid, $archiveInfo, $ids[$c]);
  if($ret['error'])
   return $ret;
  $xml.= $ret['xml'];
  if($ret['attachments'] && count($ret['attachments']))
   $attachments = array_merge($attachments,$ret['attachments']);
 }
 /* Export categories */
 for($c=0; $c < count($cats); $c++)
 {
  $ret = dynarc_exportCat($sessid, $shellid, $archiveInfo, $cats[$c]);
  if($ret['error'])
   return $ret;
  $xml.= $ret['xml'];
  if($ret['attachments'] && count($ret['attachments']))
   $attachments = array_merge($attachments,$ret['attachments']);
 }

 $xml.= "</xml>";

 if($fileName)
 {
  /* detect home dir */
  $sessInfo = sessionInfo($sessid);
  if($sessInfo['uname'] == "root")
   $userpath = "";
  else if($sessInfo['uid'])
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
   $db->Read();
   $userpath = $_USERS_HOMES.$db->record['homedir']."/";
   $db->Close();
  }
  else
   $userpath="tmp/";
  $fileName = $userpath.ltrim($fileName,"/").".xml";
  $f = @fopen($_BASE_PATH.$fileName,"w");
  if(!$f)
   return array('message'=>"Unable to create file $fileName","error"=>"UNKNOWN_ERROR");
  @fwrite($f,$xml);
  @fclose($f);
  @chmod($_BASE_PATH.$fileName,$_DEFAULT_FILE_PERMS);
  $out.= "done!";
  $outArr['filename'] = str_replace($userpath,"",$fileName);

  if(count($attachments))
  {
   $out.= "Export ".count($attachments)." attachments...";
   $q = "";
   $missed = array();
   for($c=0; $c < count($attachments); $c++)
   {
    if(is_array($attachments[$c]) && $attachments[$c]['src'] && $attachments[$c]['dest'])
	{
	 $attSrc = $attachments[$c]['src'];
	 $attDest = $attachments[$c]['dest'];
	 if(file_exists($_BASE_PATH.$attSrc))
	  $q.= " -absi `".$attSrc."` -abso `".$attDest."`";
	 else
	  $missed[] = $attSrc;
	}
	else
	{
	 $att = str_replace($userpath,"",$attachments[$c]);
	 if(file_exists($_BASE_PATH.$userpath.$att))
      $q.= " -i `".$att."`";
	 else
	  $missed[] = $att;
	}
   }
   $ret = GShell("zip -i `".str_replace($userpath,"",$fileName)."`".$q." -o `".substr(str_replace($userpath,"",$fileName),0,-4).".zip`",$sessid,$shellid,array('ALLOW_ABS_PATH'=>true));
   if($ret['error'])
   {
	$ret['message'] = $out.$ret['message'];
	return $ret;
   }
   $outArr['filename'] = $ret['outarr']['filename'];
   $out.= "Warning: ".count($missed)." attachments missed!\n";
   $out.= "done!\n";
  }
 }
 else
 {
  $outArr['xml'] = $xml;
  $out.= "done!";
 }

 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_exportItem($sessid, $shellid, $archiveInfo, $id, $itm=null)
{
 global $_BASE_PATH;
 if(!$itm)
 {
  $ret = GShell("dynarc item-info -aid `".$archiveInfo['id']."` -id `$id`", $sessid, $shellid, $archiveInfo);
  if($ret['error']) return $ret;
  $itm = $ret['outarr'];
 }
 else
 {
  /* get full text description */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT description FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itm['id']."'");
  $db->Read();
  $itm['desc'] = $db->record['description'];
  $db->Close();
 }

 gshPreOutput($shellid, "Esportando: <i>".$itm['name']."</i>","PROGRESS", null, "SINGLE_LINE");

 $fieldToExport = array('name','desc','ctime','mtime','published','aliasname','code_num','code_str','code_ext',
	'contents','keywords','rev_num','hits','lang');
 $xml = "<item";
 $attachments = array();
 for($c=0; $c < count($fieldToExport); $c++)
 {
  if($itm[$fieldToExport[$c]])
   $xml.= " ".$fieldToExport[$c]."=\"".sanitize($itm[$fieldToExport[$c]])."\"";
 }
 if(count($archiveInfo['extensions']))
 {
  $xml.= ">";
  for($c=0; $c < count($archiveInfo['extensions']); $c++)
  {
   $ext = $archiveInfo['extensions'][$c];
   if(file_exists($_BASE_PATH."etc/dynarc/extensions/".$ext."/index.php"))
   {
	include_once($_BASE_PATH."etc/dynarc/extensions/".$ext."/index.php");
	if(is_callable("dynarcextension_".$ext."_export",false))
	{
	 $ret = call_user_func("dynarcextension_".$ext."_export",$sessid,$shellid,$archiveInfo,$itm);
	 if($ret['error'])
	  return $ret;
	 $xml.= $ret['xml'];
	 if($ret['attachments'] && count($ret['attachments']))
	  $attachments = array_merge($attachments,$ret['attachments']);
	}
   }
  }
  $xml.= "</item>\n";
 }
 else
  $xml.= "/>\n";
 return array('xml'=>$xml, 'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_importItem($sessid, $shellid, $archiveInfo, $catInfo, $node, $group=null, $overwrite=false, $title="")
{
 global $_BASE_PATH;

 if($overwrite && $node->getString('code_str'))
 {
  // verifica se esiste un documento con lo stesso codice //
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_items WHERE code_str='".$node->getString("code_str")."' AND trash='0'"
	.($catInfo['id'] ? " AND cat_id='".$catInfo['id']."'" : "")." ORDER BY id DESC LIMIT 1");
  if($db->Read())
   $qry = "dynarc edit-item -aid `".$archiveInfo['id']."` -id '".$db->record['id']."'";
  else
   $qry = "dynarc new-item -aid `".$archiveInfo['id']."`".($catInfo['id'] ? " -cat '".$catInfo['id']."'" : "");
  $db->Close();
 }
 else
  $qry = "dynarc new-item -aid `".$archiveInfo['id']."`".($catInfo['id'] ? " -cat '".$catInfo['id']."'" : "");

 $qry.= " -name `".($title ? $title : $node->getString('name'))."` -desc `".$node->getString('desc')."`".($group ? " -group '".$group."'" : "");
 if($node->getString('ctime')) $qry.= " -ctime `".date('Y-m-d H:i',$node->getString('ctime',time()))."`";
 if($node->getString('code_num')) $qry.= " -code-num '".$node->getString('code_num')."'";
 if($node->getString('code_str')) $qry.= " -code-string `".$node->getString('code_str')."`";
 if($node->getString('code_ext')) $qry.= " -code-ext '".$node->getString('code_ext')."'";
 if($node->getString('aliasname')) $qry.= " -alias `".$node->getString('aliasname')."`";
 if($node->getString('contents')) $qry.= " -contents `".$node->getString('contents')."`";
 if($node->getString('keywords')) $qry.= " -keyw `".$node->getString('keywords')."`";
 if($node->getString('lang')) $qry.= " -lang `".$node->getString('lang')."`";
 
 $qry.= " -set `mtime='".date('Y-m-d H:i',$node->getString('mtime',time()))."'`";

 gshPreOutput($shellid, "Importo: <i>".$node->getString('name')." - cod.".$node->getString('code_str')."</i>","PROGRESS", null, "SINGLE_LINE");

 $ret = GShell($qry,$sessid,$shellid,$archiveInfo);
 if($ret['error'])
  return $ret;
 $itemInfo = $ret['outarr'];
 for($c=0; $c < count($archiveInfo['extensions']); $c++)
 {
  $ext = $archiveInfo['extensions'][$c];
  $list = $node->GetElementsByTagName($ext);
  if(count($list))
  {
   $extNode = $list[0];
   if(file_exists($_BASE_PATH."etc/dynarc/extensions/".$ext."/index.php"))
   {
	include_once($_BASE_PATH."etc/dynarc/extensions/".$ext."/index.php");
	if(is_callable("dynarcextension_".$ext."_import",false))
	{ 
	 $ret = call_user_func("dynarcextension_".$ext."_import",$sessid,$shellid,$archiveInfo,$itemInfo,$extNode);
	 if(is_array($ret) && $ret['error'])
	  return $ret;
	}
   }
  }
 }
 if($ret['error']) return $ret;
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_exportCat($sessid, $shellid, $archiveInfo, $catId, $cat=null)
{
 global $_BASE_PATH;
 if(!$cat)
 {
  $ret = GShell("dynarc cat-info -aid `".$archiveInfo['id']."` -id `$catId`",$sessid,$shellid,$archiveInfo);
  if($ret['error']) return $ret;
  $cat = $ret['outarr'];
 }

 gshPreOutput($shellid, "Esportando cartella: <i>".$cat['name']."</i>","PROGRESS",null,"SINGLE_LINE");

 $fieldToExport = array('name','desc','ctime','tag','code');
 $xml = "<category";
 $attachments = array();
 for($c=0; $c < count($fieldToExport); $c++)
 {
  if($cat[$fieldToExport[$c]])
   $xml.= " ".$fieldToExport[$c]."=\"".sanitize($cat[$fieldToExport[$c]])."\"";
 }
 $xml.= ">";
 /* Export category extensions */
 if(count($archiveInfo['extensions']))
 {
  for($c=0; $c < count($archiveInfo['extensions']); $c++)
  {
   $ext = $archiveInfo['extensions'][$c];
   if(file_exists($_BASE_PATH."etc/dynarc/extensions/".$ext."/index.php"))
   {
	include_once($_BASE_PATH."etc/dynarc/extensions/".$ext."/index.php");
	if(is_callable("dynarcextension_".$ext."_export",false))
	{
	 $ret = call_user_func("dynarcextension_".$ext."_export",$sessid,$shellid,$archiveInfo,$cat,true);
	 if($ret['error'])
	  return $ret;
	 $xml.= "\n".$ret['xml'];
	 if($ret['attachments'] && count($ret['attachments']))
	  $attachments = array_merge($attachments,$ret['attachments']);
	}
   }
  }
 }
 /* Export items */
 $ret = GShell("dynarc item-list -aid `".$archiveInfo['id']."` -cat ".$cat['id'],$sessid,$shellid,$archiveInfo);
 if(!$ret['error'])
 {
  $list = $ret['outarr']['items'];
  for($c=0; $c < count($list); $c++)
  {
   $ret = dynarc_exportItem($sessid, $shellid, $archiveInfo, $list[$c]['id'], $list[$c]);
   if($ret['error'])
	return $ret;
   $xml.= "\n".$ret['xml'];
   if($ret['attachments'] && count($ret['attachments']))
    $attachments = array_merge($attachments,$ret['attachments']);
  }
 }
 
 /* Export sub-categories */
 $ret = GShell("dynarc cat-list -aid `".$archiveInfo['id']."` -parent ".$cat['id'],$sessid,$shellid,$archiveInfo);
 if(!$ret['error'])
 {
  $list = $ret['outarr'];
  for($c=0; $c < count($list); $c++)
  {
   $ret = dynarc_exportCat($sessid, $shellid, $archiveInfo, $list[$c]['id'], $list[$c]);
   if($ret['error'])
	return $ret;
   $xml.= "\n".$ret['xml'];
   if($ret['attachments'] && count($ret['attachments']))
    $attachments = array_merge($attachments,$ret['attachments']);
  }
 }

 $xml.= "</category>\n";
 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_importCategory($sessid, $shellid, $archiveInfo, $catInfo, $node, $group=null, $overwrite=false)
{
 global $_BASE_PATH;

 /* TODO: fare la funzione overwrite */

 $qry = "dynarc new-cat -aid `".$archiveInfo['id']."`".($catInfo['id'] ? " -parent ".$catInfo['id'] : "");
 $qry.= " -name `".$node->getString('name')."` -desc `".$node->getString('desc')."`".($group ? " -group '".$group."'" : "");
 if($node->getString('code'))
  $qry.= " -code `".$node->getString('code')."`";
 if($node->getString('tag'))
  $qry.= " -tag `".$node->getString('tag')."`";

 gshPreOutput($shellid, "Importo cartella: <i>".$node->getString('name')."</i>","PROGRESS", null, "SINGLE_LINE");
 
 $ret = GShell($qry,$sessid,$shellid,$archiveInfo);
 if($ret['error'])
  return $ret;
 $subCatInfo = $ret['outarr'];
 
 /* Import category extensions */
 for($c=0; $c < count($archiveInfo['extensions']); $c++)
 {
  $ext = $archiveInfo['extensions'][$c];
  $list = $node->GetElementsByTagName($ext);
  if(count($list))
  {
   $extNode = $list[0];
   if(file_exists($_BASE_PATH."etc/dynarc/extensions/".$ext."/index.php"))
   {
	include_once($_BASE_PATH."etc/dynarc/extensions/".$ext."/index.php");
	if(is_callable("dynarcextension_".$ext."_import",false))
	{ 
	 $ret = call_user_func("dynarcextension_".$ext."_import",$sessid,$shellid,$archiveInfo,$subCatInfo,$extNode,true);
	}
   }
  }
 }

 /* Import items */
 $list = $node->GetElementsByTagName('item'); 
 for($c=0; $c < count($list); $c++)
 {
  $ret = dynarc_importItem($sessid, $shellid, $archiveInfo, $subCatInfo, $list[$c], $group, $overwrite);
  if($ret['error'])
   return $ret;
 }

 /* Import categories */
 $list = $node->GetElementsByTagName('category');
 for($c=0; $c < count($list); $c++)
 {
  $ret = dynarc_importCategory($sessid, $shellid, $archiveInfo, $subCatInfo, $list[$c], $group, $overwrite);
  if($ret['error'])
   return $ret;
 }

 return $subCatInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function sanitize($str)
{
 return str_replace(array("&","<",">","\"","'"),array("&amp;","&lt;","&gt;","&quot;","&apos;"),$str);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_estimateImport($node)
{
 $ret = count($node->GetElementsByTagName('item'));
 $list = $node->GetElementsByTagName('category');
 for($c=0; $c < count($list); $c++)
  $ret+= (1 + dynarc_estimateImport($list[$c]));
 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//

