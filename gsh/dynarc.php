<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 14-05-2017
 #PACKAGE: dynarc
 #DESCRIPTION: Dynarc is a useful tool for manage dynamic archives.
 #VERSION: 2.22beta
 #CHANGELOG: 14-05-2017 : Aggiunto parametro extraVar a tutte le funzioni.
			 09-04-2016 : Aggiunta funzione fast-search.
			 18-10-2015 : Aggiunta funzione generate-extensiontl-file
			 03-11-2014 : Creata funzione extension-info.
			 30-09-2014 : Aggiunta funzione cross-search per effettuare ricerche all'interno delle tabelle delle estensioni.
			 27-07-2014 : Aggiunta funzione generate-functions-file e generate-extension-file
			 02-07-2014 : Aggiunta funzione getItemsCount.
			 13-06-2014 : Aggiunta funzione extfind per ricercare all'interno delle estensioni.
			 10-06-2014 : Aggiunta funzione empty-archive
			 05-06-2014 : Aggiunto chown e chgrp
			 23-05-2014 : Aggiunta funzione getrootcat getRootCategory
			 17-02-2014 : Aggiunto archive-find
			 28-11-2012 : Aggiunto funzione extension-list.
			 13-11-2012 : Aggiunto integrazione con dynarc-sync
			 10-07-2012 : Aggiunto funzione search.
			 22-06-2012 : Aggiunto funzioni per copiare e rimuovere dalla clipboard.
			 26-05-2012 : Modificata funzione enableCodingSystem integrata nell'estensione coding.
			 30-04-2012 : Aggiunto funzione enable-sharing-system per la condivisione di cartelle e documenti.
 #TODO: Sistemare le funzioni import ed export che con grandi dimensioni non funzionano bene.
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

function shell_dynarc($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;
 switch($args[0])
 {
  // ARCHIVES //
  case 'new-archive' : {include_once($_BASE_PATH.'etc/dynarc/archives.php'); return dynarc_newArchive($args, $sessid, $shellid, $extraVar); } break;
  case 'edit-archive' : {include_once($_BASE_PATH.'etc/dynarc/archives.php'); return dynarc_editArchive($args, $sessid, $shellid, $extraVar); } break;
  case 'delete-archive' : {include_once($_BASE_PATH.'etc/dynarc/archives.php'); return dynarc_deleteArchive($args, $sessid, $shellid, $extraVar); } break;
  case 'empty-archive' : case 'archive-empty' : {include_once($_BASE_PATH.'etc/dynarc/archives.php'); return dynarc_emptyArchive($args, $sessid, $shellid, $extraVar); } break;
  case 'archive-list' : {include_once($_BASE_PATH.'etc/dynarc/archives.php'); return dynarc_archiveList($args, $sessid, $shellid, $extraVar); } break;
  case 'archive-info' : {include_once($_BASE_PATH.'etc/dynarc/archives.php'); return dynarc_archiveInfo($args, $sessid, $shellid, $extraVar); } break;
  case 'archive-find' : case 'archive-search' : {include_once($_BASE_PATH.'etc/dynarc/archives.php'); return dynarc_archiveFind($args, $sessid, $shellid, $extraVar); } break;
  case 'repair-hierarchy' : return dynarc_repairHierarchy($args, $sessid, $shellid, $extraVar); break;
  case 'enable-coding-system' : return dynarc_enableCodingSystem($args, $sessid, $shellid, $extraVar); break;
  case 'enable-sharing-system' : return dynarc_enableSharingSystem($args, $sessid, $shellid, $extraVar); break;
  case 'enable-sync' : return dynarc_enableSync($args, $sessid, $shellid, $extraVar); break;
  case 'disable-sync' : return dynarc_disableSync($args, $sessid, $shellid, $extraVar); break;

  // CATEGORIES //
  case 'new-cat' : {include_once($_BASE_PATH.'etc/dynarc/categories.php'); return dynarc_newCategory($args, $sessid, $shellid, $extraVar); } break;
  case 'edit-cat' : {include_once($_BASE_PATH.'etc/dynarc/categories.php'); return dynarc_editCategory($args, $sessid, $shellid, $extraVar); } break;
  case 'delete-cat' : {include_once($_BASE_PATH.'etc/dynarc/categories.php'); return dynarc_deleteCategory($args, $sessid, $shellid, $extraVar); } break;
  case 'cat-list' : {include_once($_BASE_PATH.'etc/dynarc/categories.php'); return dynarc_categoryList($args, $sessid, $shellid, $extraVar); } break;
  case 'cat-find' : {include_once($_BASE_PATH.'etc/dynarc/categories.php'); return dynarc_categoryFind($args, $sessid, $shellid, $extraVar); } break;
  case 'cat-info' : {include_once($_BASE_PATH.'etc/dynarc/categories.php'); return dynarc_categoryInfo($args, $sessid, $shellid, $extraVar); } break;
  case 'cat-sort' : {include_once($_BASE_PATH.'etc/dynarc/categories.php'); return dynarc_categorySort($args, $sessid, $shellid, $extraVar); } break;
  case 'cat-tree' : {include_once($_BASE_PATH.'etc/dynarc/categories.php'); return dynarc_categoryTree($args, $sessid, $shellid, $extraVar); } break;
  case 'cat-move' : {include_once($_BASE_PATH.'etc/dynarc/categories.php'); return dynarc_categoryMove($args, $sessid, $shellid, $extraVar); } break;
  case 'cat-copy' : {include_once($_BASE_PATH.'etc/dynarc/categories.php'); return dynarc_categoryCopy($args, $sessid, $shellid, $extraVar); } break;
  case 'getrootcat' : case 'rootcat' : case 'get-root-cat' : {include_once($_BASE_PATH.'etc/dynarc/categories.php'); return dynarc_getRootCategory($args, $sessid, $shellid, $extraVar); } break;

  // ITEMS //
  case 'new-item' : {include_once($_BASE_PATH.'etc/dynarc/items.php'); return dynarc_newItem($args, $sessid, $shellid, $extraVar); } break;
  case 'edit-item' : {include_once($_BASE_PATH.'etc/dynarc/items.php'); return dynarc_editItem($args, $sessid, $shellid, $extraVar); } break;
  case 'delete-item' : {include_once($_BASE_PATH.'etc/dynarc/items.php'); return dynarc_deleteItem($args, $sessid, $shellid, $extraVar); } break;
  case 'item-info' : {include_once($_BASE_PATH.'etc/dynarc/items.php'); return dynarc_itemInfo($args, $sessid, $shellid, $extraVar); } break;
  case 'item-list' : {include_once($_BASE_PATH.'etc/dynarc/items.php'); return dynarc_itemList($args, $sessid, $shellid, $extraVar); } break;
  case 'item-find' : {include_once($_BASE_PATH.'etc/dynarc/items.php'); return dynarc_itemFind($args, $sessid, $shellid, $extraVar); } break;
  case 'item-sort' : {include_once($_BASE_PATH.'etc/dynarc/items.php'); return dynarc_itemSort($args, $sessid, $shellid, $extraVar); } break;
  case 'item-move' : {include_once($_BASE_PATH.'etc/dynarc/items.php'); return dynarc_itemMove($args, $sessid, $shellid, $extraVar); } break;
  case 'item-copy' : {include_once($_BASE_PATH.'etc/dynarc/items.php'); return dynarc_itemCopy($args, $sessid, $shellid, $extraVar); } break;
  case 'item-count' : case 'items-count' : case 'get-item-count' : case 'get-items-count' : {include_once($_BASE_PATH.'etc/dynarc/items.php'); return dynarc_getItemsCount($args, $sessid, $shellid, $extraVar); } break;

  // OTHER FUNCTIONS //
  case 'chmod' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_chmod($args, $sessid, $shellid, $extraVar); } break;
  case 'chown' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_chown($args, $sessid, $shellid, $extraVar); } break;
  case 'chgrp' : case 'chgroup' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_chgrp($args, $sessid, $shellid, $extraVar); } break;
  case 'trash' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_trash($args, $sessid, $shellid, $extraVar); } break;
  case 'install-extension' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_installExtension($args, $sessid, $shellid, $extraVar); } break;
  case 'uninstall-extension' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_uninstallExtension($args, $sessid, $shellid, $extraVar); } break;
  case 'extension-list' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_extensionList($args, $sessid, $shellid, $extraVar); } break;
  case 'extension-info' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_extensionInfo($args, $sessid, $shellid, $extraVar); } break;
  case 'exec-func' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_execFunc($args, $sessid, $shellid, $extraVar); } break;
  case 'build' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_build($args, $sessid, $shellid, $extraVar); } break;
  case 'unbuild' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_unbuild($args, $sessid, $shellid, $extraVar); } break;
  case 'generate-functions-file' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_generateFunctionsFile($args, $sessid, $shellid, $extraVar); } break;
  case 'generate-extension-file' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_generateExtensionFile($args, $sessid, $shellid, $extraVar); } break;
  case 'generate-extensiontl-file' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_generateExtensionTLFile($args, $sessid, $shellid, $extraVar); } break;
  
  case 'copy-to-clipboard' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_copyToClipboard($args, $sessid, $shellid, $extraVar); } break;
  case 'remove-from-clipboard' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_removeFromClipboard($args, $sessid, $shellid, $extraVar); } break;
  case 'clipboard-list' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_clipboardList($args, $sessid, $shellid, $extraVar); } break;
  case 'clipboard-info' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_clipboardInfo($args, $sessid, $shellid, $extraVar); } break;
  case 'clipboard-delete' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_clipboardDelete($args, $sessid, $shellid, $extraVar); } break;

  case 'search' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_search($args, $sessid, $shellid, $extraVar); } break;
  case 'fast-search' : case 'fastsearch' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_fastSearch($args, $sessid, $shellid, $extraVar); } break;
  case 'ext-find' : case 'extfind' : case 'ext-search' : case 'extsearch' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_extfind($args, $sessid, $shellid, $extraVar); } break;
  case 'cross-search' : {include_once($_BASE_PATH.'etc/dynarc/otherfuncs.php'); return dynarc_crossSearch($args, $sessid, $shellid, $extraVar); } break;

  // IMPORT AND EXPORT */
  case 'import' : {include_once($_BASE_PATH.'etc/dynarc/importexport.php'); return dynarc_import($args, $sessid, $shellid, $extraVar); } break;
  case 'export' : {include_once($_BASE_PATH.'etc/dynarc/importexport.php'); return dynarc_export($args, $sessid, $shellid, $extraVar); } break;
  case 'sync' : {include_once($_BASE_PATH.'etc/dynarc/sync.php'); return dynarc_sync($args, $sessid, $shellid, $extraVar); } break;
  
  default : return dynarc_invalidArguments(); break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_repairHierarchy($args, $sessid, $shellid, $extraVar)
{
 global $_BASE_PATH;
 include_once($_BASE_PATH.'etc/dynarc/archives.php');

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
  }

 $out = "";
 $outArr = array();
 $sessInfo = sessionInfo($sessid);

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

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 /* REPAIR CATEGORIES */
 $count = 0;
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_categories WHERE 1 ORDER BY id ASC");
 while($db->Read())
 {
  $ret = GShell("dynarc cat-info -aid ".$archiveInfo['id']." -id ".$db->record['id']." --include-path");
  if(!$ret['error'])
  {
   $path = "";
   for($c=0; $c < count($ret['outarr']['pathway']); $c++)
	$path.= $ret['outarr']['pathway'][$c]['id'].",";
   $db2 = new AlpaDatabase();
   $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET hierarchy=',".$path."' WHERE id='".$ret['outarr']['id']."'");
   $db2->Close();
   $count++;
  }
 }
 $db->Close();
 $out.= "$count categories updated!\n";

 /* REPAIR ITEMS */
 $count = 0;
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id,cat_id FROM dynarc_".$archiveInfo['prefix']."_items WHERE 1 ORDER BY id ASC");
 while($db->Read())
 {
  $ret = GShell("dynarc cat-info -aid ".$archiveInfo['id']." -id ".$db->record['cat_id']." --include-path");
  if(!$ret['error'])
  {
   $path = "";
   for($c=0; $c < count($ret['outarr']['pathway']); $c++)
	$path.= $ret['outarr']['pathway'][$c]['id'].",";
   $db2 = new AlpaDatabase();
   $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET hierarchy=',".$path.$db->record['cat_id'].",' WHERE id='".$db->record['id']."'");
   $db2->Close();
   $count++;
  }
 }
 $db->Close();
 $out.= "$count items updated!\n";

 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_enableCodingSystem($args, $sessid, $shellid, $extraVar)
{
 global $_BASE_PATH;
 include_once($_BASE_PATH.'etc/dynarc/archives.php');

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
  }

 /* CHECK FOR ARCHIVE */
 $ret = dynarc_checkForArchive($args,$sessid,$shellid, $extraVar);
 if($ret['error'])
  return $ret;
 $archiveInfo = $ret['outarr'];
 return GShell("dynarc install-extension coding -aid ".$archiveInfo['id'],$sessid,$shellid, $extraVar);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_enableSharingSystem($args, $sessid, $shellid, $extraVar)
{
 global $_BASE_PATH;
 include_once($_BASE_PATH.'etc/dynarc/archives.php');
 $archives = array();
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '--all' : $allArchives=true; break;
  }

 $out = "";
 $outArr = array();
 $sessInfo = sessionInfo($sessid);

 if($allArchives)
 {
  $ret = GShell("dynarc archive-list -a",$sessid,$shellid, $extraVar);
  $archives = $ret['outarr'];
 }
 else
 {
  /* CHECK FOR ARCHIVE */
  $ret = dynarc_checkForArchive($args,$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archives[] = $ret['outarr'];
 }

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=0; $c < count($archives); $c++)
 {
  $archiveInfo = $archives[$c];
  $db = new AlpaDatabase();
  $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_categories` ADD `shgrps` VARCHAR( 255 ) NOT NULL , ADD `shusrs` VARCHAR(255) NOT NULL");
  $db->Close();

  $db = new AlpaDatabase();
  $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` ADD `shgrps` VARCHAR( 255 ) NOT NULL , ADD `shusrs` VARCHAR(255) NOT NULL");
  $db->Close();

  $out.= "Done! Sharing system has been enabled for archive ".$archiveInfo['name']."\n";
 }

 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_enableSync($args, $sessid, $shellid, $extraVar )
{
 global $_BASE_PATH;
 include_once($_BASE_PATH.'etc/dynarc/archives.php');

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $archives = array();
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '--all' : $allArchives=true; break;
  }

 $out = "";
 $outArr = array();
 $sessInfo = sessionInfo($sessid);

 if($allArchives)
 {
  $ret = GShell("dynarc archive-list -a",$sessid,$shellid, $extraVar);
  $archives = $ret['outarr'];
 }
 else
 {
  /* CHECK FOR ARCHIVE */
  $ret = dynarc_checkForArchive($args,$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archives[] = $ret['outarr'];
 }

 for($c=0; $c < count($archives); $c++)
 {
  $archiveInfo = $archives[$c];
  $db = new AlpaDatabase();
  $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_categories` ADD `syncid` VARCHAR( 32 ) NOT NULL AFTER `id` , ADD INDEX ( `syncid` )");
  $db->Close();

  $db = new AlpaDatabase();
  $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` ADD `syncid` VARCHAR( 32 ) NOT NULL AFTER `id` , ADD INDEX ( `syncid` )");
  $db->Close();

  $db = new AlpaDatabase();
  $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_synclog` (
`syncid` VARCHAR( 32 ) NOT NULL ,
`id` INT( 11 ) NOT NULL ,
`cat_id` INT( 11 ) NOT NULL ,
`uid` INT( 11 ) NOT NULL ,
`gid` INT( 11 ) NOT NULL ,
`_mod` VARCHAR( 3 ) NOT NULL ,
`status` VARCHAR( 32 ) NOT NULL ,
`logtime` DATETIME NOT NULL ,
PRIMARY KEY ( `syncid` ) ,
INDEX ( `id` , `cat_id` , `uid` , `gid` , `_mod` )
)");
  $db->Close();

  /* update archive info */
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_archives SET sync_enabled='1' WHERE id='".$archiveInfo['id']."'");
  $db->Close();

  /* UPDATE SYNCID FOR ALL CATEGORIES AND EXISTING ITEMS */
  $count = 0;
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_categories WHERE syncid=''");
  $db->Read();
  $count = $db->record[0];
  $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_items WHERE syncid=''");
  $db->Read();
  $count+= $db->record[0];
  $db->Close();
 
  if($count)
  {
   $interface = array("name"=>"progressbar","steps"=>$count);
   gshPreOutput($shellid,"Indexing the archive for enabling the synchronization", "ESTIMATION", "", "PASSTHRU", $interface);

   /* Update categories */
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT id,uid,gid,_mod,ctime,name,tag,hierarchy FROM dynarc_".$archiveInfo['prefix']."_categories WHERE syncid=''");
   while($db->Read())
   {
	gshPreOutput($shellid, "Updating index for category: <i>".$db->record['name']."</i>","PROGRESS", "");
	$synctag = $db->record['tag'];
	if($synctag && $db->record['hierarchy'] && ($db->record['hierarchy'] != ","))
    {
	 $h = "*".$db->record['hierarchy']."*";
	 $h = str_replace(array("*,",",*"),"",$h);
	 $x = explode(",",$h);
	 $db2 = new AlpaDatabase();
	 for($i=0; $i < count($x); $i++)
	 {
	  $db2->RunQuery("SELECT tag FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$x[$i]."'");
	  $db2->Read();
	  $synctag.= "-".$db2->record['tag'];
	 }
	 $db2->Close();
	}

    $db2 = new AlpaDatabase();
	$syncid = $synctag ? md5($synctag) : md5($db->record['id'].strtotime($db->record['ctime']).rand(1,255));
    $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET syncid='".$syncid."' WHERE id='".$db->record['id']."'");
	$db2->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_synclog(syncid,cat_id,uid,gid,_mod,status,logtime) VALUES('".$syncid."','"
		.$db->record['id']."','".$db->record['uid']."','".$db->record['gid']."','".$db->record['_mod']."','CREATED','".$db->record['ctime']."')");
    $db2->Close();
   }
   $db->Close();

   /* Update items */
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT id,uid,gid,_mod,ctime,name FROM dynarc_".$archiveInfo['prefix']."_items WHERE syncid=''");
   while($db->Read())
   {
	gshPreOutput($shellid, "Updating index for item: <i>".$db->record['name']."</i>","PROGRESS", "");
    $db2 = new AlpaDatabase();
	$syncid = md5($db->record['id'].strtotime($db->record['ctime']).rand(1,255));
    $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET syncid='".$syncid."' WHERE id='".$db->record['id']."'");
	$db2->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_synclog(syncid,id,uid,gid,_mod,status,logtime) VALUES('".$syncid."','"
		.$db->record['id']."','".$db->record['uid']."','".$db->record['gid']."','".$db->record['_mod']."','CREATED','".$db->record['ctime']."')");
    $db2->Close();
   }
   $db->Close();
  }
  $out.= "Done! The archive ".$archiveInfo['name']." has been enabled for synchronization.\n";
 }
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_disableSync($args, $sessid, $shellid, $extraVar)
{
 global $_BASE_PATH;
 include_once($_BASE_PATH.'etc/dynarc/archives.php');

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $archives = array();
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
   case '--all' : $allArchives=true; break;
  }

 $out = "";
 $outArr = array();
 $sessInfo = sessionInfo($sessid);

 if($allArchives)
 {
  $ret = GShell("dynarc archive-list -a",$sessid,$shellid, $extraVar);
  $archives = $ret['outarr'];
 }
 else
 {
  /* CHECK FOR ARCHIVE */
  $ret = dynarc_checkForArchive($args,$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archives[] = $ret['outarr'];
 }

 for($c=0; $c < count($archives); $c++)
 {
  $archiveInfo = $archives[$c];
  $db = new AlpaDatabase();
  $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_categories` DROP `syncid`");
  $db->Close();

  $db = new AlpaDatabase();
  $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` DROP `syncid`");
  $db->Close();

  $db = new AlpaDatabase();
  $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_synclog`");
  $db->Close();

  /* update archive info */
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_archives SET sync_enabled='0' WHERE id='".$archiveInfo['id']."'");
  $db->Close();

  $out.= "Done! The archive ".$archiveInfo['name']." has been excluded for synchronization.\n";
 }
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//

