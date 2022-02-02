<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 20-05-2017
 #PACKAGE: dynarc
 #DESCRIPTION: Archives functions for Dynarc
 #VERSION: 2.19beta
 #CHANGELOG: 20-05-2017 : Abilitato sharing system.
			 14-05-2017 : Aggiunto parametro extraVar su tutte le funzioni.
			 29-04-2017 : Aggiunto parametro autoprefix su funzione newarchive.
			 24-10-2016 : MySQLi integration.
			 24-06-2015 : Aggiunto errori mysql su funzione newarchive.
			 10-06-2014 : Aggiunta funzione empty-archive
			 17-02-2014 : Aggiunta funzione archive-find
			 19-11-2013 : Aggiornamenti irrilevanti generali.
			 25-05-2013 : Bug fix on dynarc archive-info -name ...
			 13-03-2013 : Bug fix su funzione archive list.
			 01-02-2013 : Added 'where' in archive list.
			 12-01-2013 : Bug fix in edit archive.
			 16-11-2012 : Bug fix ed integrazione con DynarcSync.
			 01-11-2012 : Aggiunto $paramsArr su funzione dynarc inherit archive.
			 06-07-2012 : Filter by archive type added in function archive-list
			 27-05-2012 : Aggiunto parametro hidden.
			 21-03-2012 : Bug fix with long text into items and categories descriptions.
			 14-01-2012 : Aggiunto parametro --get-count su funzione archive-list
 #TODO:
 
*/

global $_BASE_PATH;
include_once($_BASE_PATH."include/userfunc.php");

function dynarc_newArchive($args, $sessid, $shellid, $extraVar)
{
 global $_BASE_PATH;

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $type = "default";
 $hidden = false;
 $defaultCatPerms = 640;
 $defaultItemPerms = 640;
 $defaultCatPublished = false;
 $defaultItemPublished = false;
 $enableSharing = false;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-prefix' : {$prefix=$args[$c+1]; $c++;} break;
   case '-autoprefix' : {$autoPrefix=$args[$c+1]; $c++;} break;		// Auto prefix. Digitare il prefisso iniziale (es: gmart)
   case '-desc' : {$desc=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '--functions-file' : {$funFile=ltrim($args[$c+1],"/"); $c++;} break;
   case '-overwrite' : $overwrite=true; break;
   case '-mode' : case '-perms' : {$mode=$args[$c+1]; $c++;} break;
   case '-type' : {$type=$args[$c+1]; $c++;} break;
   case '-launcher' : {$launcher=$args[$c+1]; $c++;} break;
   case '-inherit' : {$inherit=$args[$c+1]; $c++;} break;

   /* OPTIONS */
   case '--hidden' : $hidden=true; break;
   case '--enable-cronology' : $enableCronology=true; break;
   case '--enable-stats' : $enableStats=true; break;
   case '--enable-language-support' : $enableLanguageSupport=true; break;
   case '--install-label-extension' : $installLabelExtension=true; break;

   /* SETTINGS */
   case '--default-cat-perms' : case '--def-cat-perms' : {$defaultCatPerms=$args[$c+1]; $c++;} break;
   case '--default-item-perms' : case '--def-item-perms' : {$defaultItemPerms=$args[$c+1]; $c++;} break;
   case '--default-cat-published' : case '--def-cat-pub' : {$defaultCatPublished=$args[$c+1]; $c++;} break;
   case '--default-item-published' : case '--def-item-pub' : {$defaultItemPublished=$args[$c+1]; $c++;} break;

   /* SHARING */
   case '--share-add-group' : {$shGroups[]=$args[$c+1]; $enableSharing=true; $c++;} break; // divide group and perms with(:) example: --share-add-group mygroup:6
   case '--share-add-user' : {$shUsers[]=$args[$c+1]; $enableSharing=true; $c++;} break; // divide user and perms with(:) example: --share-add-user alex:4

   case '-params' : {$paramString=$args[$c+1]; $c++;} break;
   case '-thumb-mode' : {$thumbMode=$args[$c+1]; $c++;} break;
   case '-thumb-img' : {$thumbImg=$args[$c+1]; $c++;} break;

  }
 
 if(!$name)
  return array("message"=>"You must specify archive name. (with -name ARCHIVE_NAME)\n","error"=>"INVALID_ARCHIVE_NAME");

 if($autoPrefix)
 {
  $prefixSeqIdx = 1;

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE ".($type ? "archive_type='".$type."' AND " : "")
	."tb_prefix LIKE '".$autoPrefix."_%' ORDER BY tb_prefix DESC LIMIT 1");
  if($db->Read())
  {
   $x = explode("_", $db->record['tb_prefix']);
   if($x[1] && is_numeric($x[1]))
    $prefixSeqIdx = intval($x[1])+1;
   else
   {
    $db->RunQuery("SELECT COUNT(*) FROM dynarc_archives WHERE ".($type ? "archive_type='".$type."' AND " : "")."tb_prefix LIKE '".$autoPrefix."_%'");
    if($db->Read() && $db->record[0])
	 $prefixSeqIdx = $db->record[0]+1;
   }
  }

  // Check if new prefix already exists.
  $ok = false;
  while(!$ok)
  {
   $db->RunQuery("SELECT id FROM dynarc_archives WHERE tb_prefix='".$autoPrefix."_".$prefixSeqIdx."' LIMIT 1");
   if(!$db->Read())
   {
	$ok = true;
	break;
   }
   else
	$prefixSeqIdx++;
  }
  
  $prefix = $autoPrefix."_".$prefixSeqIdx;

  $db->Close();
 }

 if(!$prefix) // ricava il prefisso dal nome.
  $prefix = str_replace(" ","_",strtolower($name));

 // verifica che il prefisso non contenga caratteri strani.
 if(preg_match("/^[a-zA-Z0-9 _]+$/", $prefix) === 0)
  return array("message"=>"Prefix must contain only chars (A-Z) and digit (0-9) and underscore\n","error"=>"INVALID_PREFIX");

 // verifica che non esista già un altro archivio con questo prefisso
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_archives WHERE tb_prefix='".$prefix."'");
 if($db->Read())
 {
  $db->Close();
  return array("message"=>"There is already another archive that uses this prefix","error"=>"PREFIX_ALREADY_EXISTS");
 }
 $db->Close();


 /* UPDATE GROUPS SHARE INFO */
 $shareGroups = array();
 $shgrps = "";
 $shusrs = "";
 if(count($shGroups))
 {
  for($c=0; $c < count($shGroups); $c++)
  {
   $p = strrpos($shGroups[$c],":"); $shMOD = substr($defaultItemPerms,1,1);
   if($p === false)	$shGID = is_numeric($shGroups[$c]) ? $shGroups[$c] : _getGID($shGroups[$c]);
   else { $tmp = substr($shGroups[$c],0,$p); $shGID = is_numeric($tmp) ? $tmp : _getGID($tmp); $shMOD = substr($shGroups[$c],$p+1); }
   $shareGroups[$shGID] = $shMOD;
  }
  $m = new GMOD();
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
   $shMOD = substr($defaultItemPerms,0,1);
   if($p === false) $shUID = is_numeric($shUsers[$c]) ? $shUsers[$c] : _getUID($shUsers[$c]);
   else { $tmp = substr($shUsers[$c],0,$p);	$shUID = is_numeric($tmp) ? $tmp : _getUID($tmp); $shMOD = substr($shUsers[$c],$p+1); }
   $shareUsers[$shUID] = $shMOD;
  }
  $m = new GMOD();
  reset($shareUsers);
  while(list($k,$v) = each($shareUsers)) { $m->SHUSERS[$k] = $v; }
  $shusrs = "#,";
  reset($m->SHUSERS);
  while(list($k,$v) = each($m->SHUSERS)) { $shusrs.= $k."=".$v.","; }
  $shusrs.= "#";
 }

 /* CREA L'ARCHIVIO */
 $out = "Creating archive '$name' ...\n";
 // verify if table exists //
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_archives WHERE name='".$db->Purify($name)."'");
 if($db->Read())
 {
  if(!$overwrite)
  {
   $out.= "failed! Archive $name already exists!\n";
   return array("message"=>$out,"error"=>"ARCHIVE_ALREADY_EXISTS");
  }
  else
  {
   $ret = GShell("dynarc delete-archive `$name`",$sessid,$shellid, $extraVar);
   if($ret['error'])
   {
    $out.= "failed!\n".$ret["message"];
    return array("message"=>$out,"error"=>$ret["error"]);
   }
  }
 }
 $db->Close();
 $db = new AlpaDatabase();
 // CATEGORIES //
 $qry = "CREATE TABLE `dynarc_".$prefix."_categories` (
  `id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `_mod` varchar(3) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `lnk_id` int(11) NOT NULL,
  `lnkarc_id` int(11) NOT NULL,
  `tag` varchar(64) NOT NULL,
  `code` varchar(64) NOT NULL,
  `name` varchar(80) NOT NULL,
  `description` LONGTEXT NOT NULL,
  `ordering` int(11) NOT NULL,
  `trash` tinyint(1) NOT NULL,
  `ctime` DATETIME NOT NULL,
  `published` tinyint(1) NOT NULL,
  `hierarchy` VARCHAR(255) NOT NULL,
  `defordfield` VARCHAR(32) NOT NULL,
  `defordasc` tinyint(1) NOT NULL, ";
 if($enableLanguageSupport) 				$qry.= "`lang` VARCHAR(5) NOT NULL,";
 if($enableSharing)							$qry.= "`shgrps` VARCHAR(255) NOT NULL, `shusrs` VARCHAR(255) NOT NULL,";

 $qry.= "PRIMARY KEY  (`id`), KEY `uid` (`uid`,`gid`,`_mod`,`parent_id`,`tag`,`code`,`ordering`,`trash`,`published`))";
 $db->RunQuery($qry);
 if($db->Error)
  return array('message'=>"MySQL Error: Unable to create table dynarc_".$prefix."_categories.\n".$db->Error, 'error'=>'MYSQL_ERROR');
 $db->Close();

 if($enableLanguageSupport)
 {
  $out.= "Creating table for category language support...";
  $db = new AlpaDatabase();
  $db->RunQuery("CREATE TABLE `dynarc_".$prefix."_categories_translations` (
`id` INT(11) NOT NULL AUTO_INCREMENT,
`ref_id` INT(11) NOT NULL,
`lang` VARCHAR(5) NOT NULL,
`name` VARCHAR(80) NOT NULL,
`description` LONGTEXT NOT NULL,
`published` TINYINT(1) NOT NULL,
`trash` TINYINT(1) NOT NULL,
PRIMARY KEY (`id`), KEY `ref_id` (`ref_id`,`lang`,`published`,`trash`))");
  $db->Close();
  $out.= "done!\n";
 }

 // ITEMS //
 $qry = "CREATE TABLE `dynarc_".$prefix."_items` (
`id` INT(11) NOT NULL AUTO_INCREMENT,
`uid` INT(11) NOT NULL,
`gid` INT(11) NOT NULL,
`_mod` VARCHAR(3) NOT NULL,
`cat_id` INT(11) NOT NULL,
`lnk_id` INT(11) NOT NULL,
`lnkarc_id` INT(11) NOT NULL,
`name` VARCHAR(255) NOT NULL,
`description` LONGTEXT NOT NULL,
`keywords` VARCHAR(255) NOT NULL,
`ordering` INT(11) NOT NULL,
`trash` TINYINT(1) NOT NULL,
`ctime` DATETIME NOT NULL,
`mtime` DATETIME NOT NULL,
`published` TINYINT(1) NOT NULL,
`hierarchy` VARCHAR(255) NOT NULL,";
 if($type == "document") 				$qry.= "`aliasname` VARCHAR(255) NOT NULL, `code_num` INT(11) NOT NULL, `code_str` VARCHAR(32) NOT NULL, `code_ext` VARCHAR(3) NOT NULL, `md5` VARCHAR(32) NOT NULL, ";
 if($enableCronology)					$qry.= "`rev_num` INT(11) NOT NULL, `prior_rev_id` INT(11) NOT NULL,";
 if($enableStats)						$qry.= "`hits` INT(11) NOT NULL,";
 if($enableLanguageSupport)				$qry.= "`lang` VARCHAR(5) NOT NULL,";
 if($enableSharing)						$qry.= "`shgrps` VARCHAR(255) NOT NULL, `shusrs` VARCHAR(255) NOT NULL,";

 $qry.= "PRIMARY KEY  (`id`), KEY `uid` (`uid`,`gid`,`_mod`,`cat_id`,`ordering`,`trash`,`published`,`keywords`";
 if($type == "document")
  $qry.= ",`aliasname`,`code_num`,`code_str`,`code_ext`";
 if($enableLanguageSupport)
  $qry.= ",`lang`";
 $qry.= "))";
 $db = new AlpaDatabase();
 $db->RunQuery($qry);
 if($db->Error)
  return array('message'=>"MySQL Error: Unable to create table dynarc_".$prefix."_items.\n".$db->Error, 'error'=>'MYSQL_ERROR');
 $db->Close();

 if($enableLanguageSupport)
 {
  $out.= "Creating table for items language support...";
  $qry = "CREATE TABLE `dynarc_".$prefix."_items_translations` (
`id` INT(11) NOT NULL AUTO_INCREMENT,
`ref_id` INT(11) NOT NULL,
`lang` VARCHAR(5) NOT NULL,
`name` VARCHAR(80) NOT NULL,
`description` LONGTEXT NOT NULL,
`keywords` VARCHAR(255) NOT NULL,";
  $qry.= "`published` TINYINT(1) NOT NULL,`trash` TINYINT(1) NOT NULL, PRIMARY KEY (`id`), KEY `ref_id` (`ref_id`,`lang`,`published`,`trash`,`keywords`))";
  $db = new AlpaDatabase();
  $db->RunQuery($qry);
  $db->Close();
  $out.= "done!\n";
 }

 $now = time();
 $uid = $sessInfo['uid'];
 $gid = $group ? _getGID($group) : $sessInfo['gid'];
 if($mode)
 {
  $mod = new GMOD($mode);
  $mod = $mod->MOD;
 }
 else
  $mod = 660;

 $params = ""; /* as string */
 $paramsArr = array(); /* as array */

 if($paramString)
 {
  $tmp = array();
  $x = explode("&",$paramString);
  for($c=0; $c < count($x); $c++)
  {
   if(!$x[$c]) continue;
   $xx = explode("=",$x[$c]);
   $tmp[$xx[0]] = $xx[1];
  }

  while(list($k,$v) = each($tmp))
  {
   $params.= "&".$k."=".$v;
   $paramsArr[$k] = $v;
  }
  if($params)
   $params = ltrim($params,"&");
 }


 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO dynarc_archives(uid, gid, _mod, archive_type, name, description, tb_prefix, fun_file, default_cat_perms, default_item_perms, default_cat_published, default_item_published, launcher, tb_inherit, hidden, params, thumb_mode, thumb_img, shgrps, shusrs) VALUES('"
	.$uid."','".$gid."','".$mod."','".$type."','".$db->Purify($name)."','".$db->Purify($desc)."','".$prefix."','".$funFile."','"
	.$defaultCatPerms."','".$defaultItemPerms."','".$defaultCatPublished."','".$defaultItemPublished."','"
	.$db->Purify($launcher)."','".$inherit."','".$hidden."','".$params."','".$thumbMode."','".$thumbImg."','".$shgrps."','".$shusrs."')");
 $id = $db->GetInsertId();
 $db->Close();
 $outArr = array('id'=>$id,'name'=>$name,'desc'=>$desc,'type'=>$type,'prefix'=>$prefix,'functionsfile'=>$funFile,'inherit'=>$inherit);
 $out.= "archive '$name' has been created! ID=$id\n";

 if($inherit)
 {
  /* Install all extensions */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM dynarc_archives WHERE tb_prefix='$inherit' AND trash='0' LIMIT 1");
  $db->Read();
  $funFile = $db->record['fun_file'];
  $out.= "Install extensions from inherited archive ".$db->record['name']."\n";
  $db->RunQuery("SELECT * FROM dynarc_archive_extensions WHERE archive_id='".$db->record['id']."' ORDER BY id ASC");
  while($db->Read())
  {
   $ret = GShell("dynarc install-extension ".$db->record['extension_name']." -ap `".$prefix."`"
	.($db->record['params'] ? " -params `".$db->record['params']."`" : ""),$sessid,$shellid, $extraVar);
   $out.= $ret['message'];
  }
  $db->Close();

  /* call oninheritarchive function if exists */ 
  if($funFile && file_exists($_BASE_PATH.$funFile))
  {
   include_once($_BASE_PATH.$funFile);
   if(is_callable("dynarcfunction_".$inherit."_oninheritarchive",true))
   {
    $ret = call_user_func("dynarcfunction_".$inherit."_oninheritarchive", $args, $sessid, $shellid, $outArr, $paramsArr);
	if($ret['error'])
	 $out.= "ERROR! \n".$ret['message'];
   }
  }
 }

 if($installLabelExtension)
 {
  $ret = GShell("dynarc install-extension labels -aid ".$id,$sessid, $shellid, $extraVar);
  $out.= $ret['message'];
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_editArchive($args, $sessid, $shellid, $extraVar)
{
 global $_BASE_PATH;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-ap' : {$ap = $args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-groupid' : {$groupId=$args[$c+1]; $c++;} break;
   case '-mode' : case '-perms' : {$perms=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-desc' : {$desc=$args[$c+1]; $c++;} break;
   case '-prefix' : {$prefix=$args[$c+1]; $c++;} break;
   case '-tag' : {$tag=$args[$c+1]; $c++;} break;
   case '--functions-file' : {$funFile=ltrim($args[$c+1],"/"); $c++;} break;
   case '-launcher' : {$launcher=$args[$c+1]; $c++;} break;
   case '-hidden' : {$hidden=$args[$c+1]; $c++;} break;

   case '--default-cat-perms' : case '--def-cat-perms' : {$defaultCatPerms=$args[$c+1]; $c++;} break;
   case '--default-item-perms' : case '--def-item-perms' : {$defaultItemPerms=$args[$c+1]; $c++;} break;
   case '--default-cat-published' : case '--def-cat-pub' : {$defaultCatPublished=$args[$c+1]; $c++;} break;
   case '--default-item-published' : case '--def-item-pub' : {$defaultItemPublished=$args[$c+1]; $c++;} break;

   case '-params' : {$paramString=$args[$c+1]; $c++;} break;
   case '-thumb-mode' : {$thumbMode=$args[$c+1]; $c++;} break;
   case '-thumb-img' : {$thumbImg=$args[$c+1]; $c++;} break;

   /* SHARING */
   case '--share-add-group' : {$shGroups[]=$args[$c+1]; $c++;} break; // divide group and perms with(:) example: --share-add-group mygroup:6
   case '--share-add-user' : {$shUsers[]=$args[$c+1]; $c++;} break; // divide user and perms with(:) example: --share-add-user alex:4
   case '--unshare-group' : {$unshGroups[]=$args[$c+1]; $c++;} break;
   case '--unshare-user' : {$unshUsers[]=$args[$c+1]; $c++;} break;

   default : $archive = $args[$c]; break;
  }

 if(!$archive && !$ap && !$archiveId)
  return array("message"=>"You must specify archive. (with: -archive ARCHIVE_NAME)\n","error"=>"INVALID_ARCHIVE");
 if($archive)
 {
  $ret = GShell("dynarc archive-info '$archive'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
 }
 else if($ap)
 {
  $ret = GShell("dynarc archive-info -prefix '$ap'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
 }
 else if($archiveId)
 {
  $ret = GShell("dynarc archive-info -id '$archiveId'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
 }
 $archiveInfo = $ret['outarr'];

 /* UPDATE GROUPS SHARE INFO */
 $shareGroups = array();
 $shgrps = "";
 $shusrs = "";
 if(count($shGroups))
 {
  for($c=0; $c < count($shGroups); $c++)
  {
   $p = strrpos($shGroups[$c],":"); $shMOD = substr($archiveInfo['def_item_perms'],1,1);
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
  $m = new GMOD(null,null,null,$archiveInfo['shgrps']);
  for($c=0; $c < count($unshareGroups); $c++)
  {
   if($m->SHGROUPS[$unshareGroups[$c]])
	unset($m->SHGROUPS[$unshareGroups[$c]]);
  }
  reset($shareGroups);
  while(list($k,$v) = each($shareGroups)) { $m->SHGROUPS[$k] = $v; }

  $shgrps = "#,";
  reset($m->SHGROUPS);
  while(list($k,$v) = each($m->SHGROUPS))
  {
   $shgrps.= $k."=".$v.",";
  }
  $shgrps.= "#";
 }

 /* UPDATE USERS SHARE INFO */
 $shareUsers = array();
 if(count($shUsers))
 {
  for($c=0; $c < count($shUsers); $c++)
  {
   $p = strrpos($shUsers[$c],":");
   $shMOD = substr($archiveInfo['def_item_perms'],0,1);
   if($p === false) $shUID = is_numeric($shUsers[$c]) ? $shUsers[$c] : _getUID($shUsers[$c]);
   else { $tmp = substr($shUsers[$c],0,$p);	$shUID = is_numeric($tmp) ? $tmp : _getUID($tmp); $shMOD = substr($shUsers[$c],$p+1); }
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
  $m = new GMOD(null,null,null,$archiveInfo['shusrs']);
  for($c=0; $c < count($unshareUsers); $c++)
  {
   if($m->SHUSERS[$unshareUsers[$c]])
	unset($m->SHUSERS[$unshareUsers[$c]]);
  }
  reset($shareUsers);
  while(list($k,$v) = each($shareUsers)) { $m->SHUSERS[$k] = $v; }
  $shusrs = "#,";
  reset($m->SHUSERS);
  while(list($k,$v) = each($m->SHUSERS))
  {
   $shusrs.= $k."=".$v.",";
  }
  $shusrs.= "#";
 }

 $db = new AlpaDatabase();
 /* WRITE CHANGES TO DATABASE */
 $q = "";
 if($name)				$q.= ",name='".$db->Purify($name)."'";
 if($desc)				$q.= ",description='".$db->Purify($desc)."'";
 if($group)				$q.= ",gid='"._getGID($group)."'";
 else if($groupId)		$q.= ",gid='".$groupId."'";
 if(isset($perms))		$q.= ",_mod='$perms'";
 $q.= ",shgrps='".$shgrps."',shusrs='".$shusrs."'";

 if($prefix && ($prefix != $archiveInfo['prefix']))
 {
  if(preg_match("/^[a-zA-Z0-9 _-]+$/", $prefix) === 0)
   return array("message"=>"Prefix must contain only chars (A-Z) and digit (0-9)\n","error"=>"INVALID_PREFIX");
  // verify if prefix already exists //
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT id FROM dynarc_archives WHERE prefix='$prefix' LIMIT 1");
  if($db2->Read())
  {
   $err = "PREFIX_ALREADY_EXISTS";
   $out.= "Prefix '$prefix' already exists!\n";
   $db2->Close();
   return array("message"=>$out,"error"=>$err);
  }
  $db2->RunQuery("RENAME TABLE dynarc_".$archiveInfo['prefix']."_categories TO dynarc_".$prefix."_categories");
  $db2->RunQuery("RENAME TABLE dynarc_".$archiveInfo['prefix']."_items TO dynarc_".$prefix."_items");
  $db2->Close();
  $q.= ",tb_prefix='$prefix'";
 }
 if(isset($funFile))				$q.= ",fun_file='$funFile'";
 if($defaultCatPerms)				$q.= ",default_cat_perms='$defaultCatPerms'";
 if($defaultItemPerms)				$q.= ",default_item_perms='$defaultItemPerms'";
 if(isset($defaultCatPublished))	$q.= ",default_cat_published='$defaultCatPublished'";
 if(isset($defaultItemPublished))	$q.= ",default_item_published='$defaultItemPublished'";
 if(isset($hidden))					$q.= ",hidden='".$hidden."'";
 if($paramString)
 {
  $x = explode("&",$paramString);
  for($c=0; $c < count($x); $c++)
  {
   if(!$x[$c]) continue;
   $xx = explode("=",$x[$c]);
   $archiveInfo['params'][$xx[0]] = $xx[1];
  }
  $params = "";
  while(list($k,$v) = each($archiveInfo['params']))
  {
   $params.= "&".$k."=".$v;
  }
  if($params)
   $q.= ",params='".ltrim($params,"&")."'";
 }
 if(isset($thumbMode))				$q.= ",thumb_mode='".$thumbMode."'";
 if(isset($thumbImg))				$q.= ",thumb_img='".$thumbImg."'";
 if(isset($launcher))				$q.= ",launcher='".$db->Purify($launcher)."'";

 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_archives SET ".ltrim($q,",")." WHERE id='".$archiveInfo['id']."'");
 $db->Close();
 $out.= "done!\n";
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_deleteArchive($args, $sessid, $shellid, $extraVar)
{
 global $_BASE_PATH;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-r' : $deleteAll=true; break;
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : case '-archive' : {$name=$args[$c+1]; $c++;} break;
   case '-prefix' : case '-ap' : {$prefix=$args[$c+1]; $c++;} break;
   default: $name=$args[$c]; break;
  }

 if($id)
 {
  $ret = GShell("dynarc archive-info -id '$id'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($name)
 {
  $ret = GShell("dynarc archive-info '$name'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($prefix)
 {
  $ret = GShell("dynarc archive-info -prefix '$prefix'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else
  return array("message"=>"You must specify archive. (with: -name ARCHIVE_NAME , or -prefix ARCHIVE_PREFIX)\n","error"=>"INVALID_ARCHIVE");

 if($deleteAll)
 {
  // uninstall all extensions
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."'");
  while($db->Read())
  {
   $ret = GShell("dynarc uninstall-extension `".$db->record['extension_name']."` -aid `".$archiveInfo['id']."`",$sessid,$shellid, $extraVar);
   if($ret['error'])
	$out.= "Warning: Can't uninstall extension ".$db->record['extension_name'].". ".$ret['message'];
  }
  $db->Close();

  // remove all known tables
  $db = new AlpaDatabase();
  $db->RunQuery("DELETE FROM dynarc_archives WHERE id='".$archiveInfo['id']."'");
  $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_categories`");
  $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_categories_translations`");
  $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_items`");
  $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_items_translations`");
  $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_synclog`");
  $out.= "Archive ".$archiveInfo['name']." has been removed.";
  $db->Close();
 }
 else
 {
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_archives SET trash='1' WHERE id='".$archiveInfo['id']."'");
  $out.= "Archive ".$archiveInfo['name']." has been trashed.";
  $db->Close();
 }

 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_emptyArchive($args, $sessid, $shellid, $extraVar)
{
 global $_BASE_PATH;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : case '-archive' : {$name=$args[$c+1]; $c++;} break;
   case '-prefix' : case '-ap' : {$prefix=$args[$c+1]; $c++;} break;
   default: $name=$args[$c]; break;
  }

 if($id)
 {
  $ret = GShell("dynarc archive-info -id '".$id."'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($name)
 {
  $ret = GShell("dynarc archive-info '".$name."'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else if($prefix)
 {
  $ret = GShell("dynarc archive-info -prefix '".$prefix."'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
  $archiveInfo = $ret['outarr'];
 }
 else
  return array("message"=>"You must specify archive. (with: -name ARCHIVE_NAME , or -prefix ARCHIVE_PREFIX)\n","error"=>"INVALID_ARCHIVE");

 /* EMPTY CATEGORIES AND ITEMS */
 $db = new AlpaDatabase();
 $db->RunQuery("TRUNCATE TABLE `dynarc_".$archiveInfo['prefix']."_categories`");
 $db->RunQuery("TRUNCATE TABLE `dynarc_".$archiveInfo['prefix']."_items`");
 $db->RunQuery("TRUNCATE TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_categories_translations`");
 $db->RunQuery("TRUNCATE TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_items_translations`");
 $db->RunQuery("TRUNCATE TABLE `dynarc_".$archiveInfo['prefix']."_synclog`");
 $db->Close();
 $out.= "main tables has been empty.\n";


 // call onarchiveempty function inherited if exists //
 if($archiveInfo['inherit'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT fun_file FROM dynarc_archives WHERE tb_prefix='".$archiveInfo['inherit']."' AND trash=0 LIMIT 1");
  $db->Read();
  if($db->record['fun_file'] && file_exists($_BASE_PATH.$db->record['fun_file']))
  {
   $out.= "empty data from inherit function file '".$db->record['fun_file']."'...";
   include_once($_BASE_PATH.$db->record['fun_file']);
   if(is_callable("dynarcfunction_".$archiveInfo['inherit']."_onarchiveempty",true))
   {
    $ret = call_user_func("dynarcfunction_".$archiveInfo['inherit']."_onarchiveempty", $args, $sessid, $shellid, $archiveInfo);
    if($ret['error'])
	 return $ret;
   }
   $out.= "done!\n";
  }
  $db->Close();
 }

 // call onarchiveempty function if exists //
 if($archiveInfo['functionsfile'] && file_exists($_BASE_PATH.$archiveInfo['functionsfile']))
 {
  include_once($_BASE_PATH.$archiveInfo['functionsfile']);
  $out.= "empty data from function file '".$archiveInfo['functionsfile']."'...";
  if(is_callable("dynarcfunction_".$archiveInfo['prefix']."_onarchiveempty",true))
  {
   $ret = call_user_func("dynarcfunction_".$archiveInfo['prefix']."_onarchiveempty", $args, $sessid, $shellid, $archiveInfo);
   if($ret['error'])
    return $ret;
  }
  $out.= "done!\n";
 }

 // call onarchiveempty function from all installed extensions //
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."'");
 while($db->Read())
 {
  /* EXECUTE FUNCTION */
  include_once($_BASE_PATH."etc/dynarc/extensions/".$db->record['extension_name']."/index.php");
  $out.= "empty data from extension '".$db->record['extension_name']."'...";
  if(is_callable("dynarcextension_".$db->record['extension_name']."_onarchiveempty",true))
  {
   $ret = call_user_func("dynarcextension_".$db->record['extension_name']."_onarchiveempty", $args, $sessid, $shellid, $archiveInfo);
   if($ret['error'])
    return $ret;
  }
  $out.= "done!\n";
 }
 $db->Close();

 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_archiveList($args, $sessid, $shellid, $extraVar)
{
 global $_BASE_PATH;
 $orderBy = "id ASC";
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '--get-count' : $getCount=true; break;
   case '-a' : case '--all' : $showAll=true; break;
   case '-type' : {$archiveType=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '--ret-count' : $retCount=true; break;
  }

 $out = "";
 $outArr = array();
 $m = new GMOD();
 $uQry = $m->userQuery($sessid,null,"dynarc_archives");
 $db = new AlpaDatabase();
 if($getCount)
 {
  $db->RunQuery("SELECT COUNT(*) FROM dynarc_archives WHERE ($uQry) AND trash='0'".(!$showAll ? " AND hidden='0'" : ""));
  $db->Read();
  $outArr['count'] = $db->record[0];
  $outArr['items'] = array();
 }

 $qry = "($uQry) AND trash='0'";
 if(!$showAll)
  $qry.= " AND hidden='0'";
 if($archiveType)
  $qry.= " AND archive_type='".$archiveType."'";
 if($where)
  $qry.= " AND (".$where.")";

 if($retCount)
 {
  $db->RunQuery("SELECT COUNT(*) FROM dynarc_archives WHERE ".$qry);
  $db->Read();
  $outArr['count'] = $db->record[0];
  $db->Close();
  $out.= ($outArr['count'] ? $outArr['count'] : "0")." archives found.";
  return array('message'=>$out, 'outarr'=>$outArr);
 }

 $db->RunQuery("SELECT id FROM dynarc_archives WHERE ".$qry." ORDER BY $orderBy".($limit ? " LIMIT $limit" : ""));
 while($db->Read())
 {
  $ret = GShell("dynarc archive-info -id ".$db->record['id'],$sessid,$shellid, $extraVar);
  if($ret['error'])
   continue;
  if($getCount)
   $outArr['items'][] = $ret['outarr'];
  else
   $outArr[] = $ret['outarr'];
  $out.= "#".$ret['outarr']['id'].". [".$ret['outarr']['prefix']."] - ".$ret['outarr']['name']."\n";
 }
 $db->Close();
 $out.= "\n".($getCount ? $outArr['count'] : count($outArr))." archives found.\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_archiveInfo($args, $sessid, $shellid, $extraVar)
{
 global $_BASE_PATH;
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-prefix' : case '-ap' : {$prefix=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '--verbose' : case '-verbose' : $verbose=true; break;
  }

 $mod = new GMOD();
 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("SELECT * FROM dynarc_archives WHERE id='$id'");
 else if($name)
  $db->RunQuery("SELECT * FROM dynarc_archives WHERE name='".$db->Purify($name)."' LIMIT 1");
 else if($prefix)
  $db->RunQuery("SELECT * FROM dynarc_archives WHERE tb_prefix='".$prefix."' LIMIT 1");
 else
  return array("message"=>"You must specify archive. (with: -id ARCHIVE_ID, or simply type archive name: eg: dynarc archive-info myarchive)","error"=>"INVALID_ARCHIVE");
 if(!$db->Read())
 {
  $out.= "Archive ".($id ? "#$id" : ($prefix ? "with prefix '$prefix'" : "'$name'"))." does not exists!\n";
  $db->Close();
  return array("message"=>$out,"error"=>"ARCHIVE_DOES_NOT_EXISTS");
 }

 $sessInfo = sessionInfo($sessid);
 $mod->set($db->record['_mod'],$db->record['uid'],$db->record['gid'],$db->record['shgrps'],$db->record['shusrs']);
 if($sessInfo['uname'] != "root" && !$mod->canRead($sessInfo['uid']))
  return array("message"=>"Unable to get archive info. Permission denied!\n","error"=>"PERMISSION_DENIED");
 $a = array('id'=>$db->record['id'],'name'=>$db->record['name'],'desc'=>$db->record['description'],'type'=>$db->record['archive_type'],
	'prefix'=>$db->record['tb_prefix'],'functionsfile'=>$db->record['fun_file'],'trash'=>$db->record['trash'],
	'def_cat_perms'=>$db->record['default_cat_perms'],'def_item_perms'=>$db->record['default_item_perms'],
	'def_cat_published'=>$db->record['default_cat_published'],'def_item_published'=>$db->record['default_item_published'],
	'launcher'=>$db->record['launcher'],'inherit'=>$db->record['tb_inherit'],'hidden'=>$db->record['hidden'],
	'sync_enabled'=>$db->record['sync_enabled'], 'shgrps'=>$db->record['shgrps'], 'shusrs'=>$db->record['shusrs']);

 // Detect icon //
 if(file_exists($_BASE_PATH."share/icons/archives/".$db->record['tb_prefix'].".png"))
  $a['icon'] = "share/icons/archives/".$db->record['tb_prefix'].".png";
 else if($db->record['tb_inherit'] && file_exists($_BASE_PATH."share/icons/archives/".$db->record['tb_inherit'].".png"))
  $a['icon'] = "share/icons/archives/".$db->record['tb_inherit'].".png";
 else
  $a['icon'] = "share/icons/archives/other.png";

 // Arrayze params //
 $params = $db->record['params'];
 $a['params'] = array();
 $x = explode("&",$params);
 for($c=0; $c < count($x); $c++)
 {
  if(!$x[$c]) continue;
  $xx = explode("=",$x[$c]);
  $a['params'][$xx[0]] = $xx[1];
 }

 $a['thumb_mode'] = $db->record['thumb_mode'];
 $a['thumb_img'] = $db->record['thumb_img'];

 $a['modinfo'] = $mod->toArray($sessInfo['uid']);
 $db->Close();  

 if($verbose)
 {
  $out.= "Archive informations:\n";
  $out.= "ID: ".$a['id']."\n";
  $out.= "Name: ".$a['name']."\n";
  $out.= "Prefix: ".$a['prefix']."\n";
  if($a['inherit'])
   $out.= "Inherit: ".$a['inherit']."\n";
  $out.= "Type: ".($a['type'] ? $a['type'] : "<i>default</i>")."\n";
  $out.= "Owner: "._getUserName($a['modinfo']['uid'])."\n";
  $out.= "Group: "._getGroupName($a['modinfo']['gid'])."\n";
  $out.= "Permissions: ".$mod->toString()."\n";
  if($a['def_cat_perms'])
   $out.= "Default category permissions: ".$a['def_cat_perms']."\n";
  if($a['def_item_perms'])
   $out.= "Default item permissions: ".$a['def_item_perms']."\n";
  if($a['hidden'])
   $out.= "Visibility: This archive is hidden.\n";
 }

 return array("message"=>$out,"outarr"=>$a);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_archiveFind($args, $sessid, $shellid, $extraVar)
{
 global $_BASE_PATH;
 $orderBy = "id ASC";
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-type' : {$archiveType=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '-search' : case '-name' : {$search=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
   default : $search=$args[$c]; break;
  }

 $out = "";
 $outArr = array();
 $m = new GMOD();
 $uQry = $m->userQuery($sessid,null,"dynarc_archives");
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_archives WHERE (".$uQry.") AND trash='0'");
 $db->Read();
 $outArr['totarchives'] = $db->record[0];
 $outArr['archives'] = array();

 $qry = "(".$uQry.") AND trash='0'";
 if($archiveType)
  $qry.= " AND archive_type='".$archiveType."'";
 if($where)
  $qry.= " AND (".$where.")";
 if($search)
 {
  $search = $db->Purify($search);
  $qry.= " AND (name='".$search."' OR name LIKE '".$search."%' OR name LIKE '%".$search."' OR name LIKE '%".$search."%')";
 }

 /* EXEC COUNT QUERY */
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_archives WHERE ".$qry);
 $db->Read();
 $outArr['count'] = $db->record[0];
 $db->Close();

 /* EXEC REAL QUERY */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_archives WHERE ".$qry." ORDER BY ".$orderBy.($limit ? " LIMIT ".$limit : ""));
 while($db->Read())
 {
  $ret = GShell("dynarc archive-info -id ".$db->record['id'],$sessid,$shellid, $extraVar);
  if($ret['error'])
   continue;
  $outArr['archives'][] = $ret['outarr'];
  if($verbose)
   $out.= "#".$ret['outarr']['id'].". [".$ret['outarr']['prefix']."] - ".$ret['outarr']['name']."\n";
 }
 $db->Close();

 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarc_checkForArchive($args,$sessid=0,$shellid=0, $extraVar=null)
{
 global $_BASE_PATH;
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-a' : case '-archive' : {$archive=$args[$c+1]; $c++;} break;
   case '-aid' : {$archiveId=$args[$c+1]; $c++;} break;
   case '-ap' : case '--archive-prefix' : {$archivePrefix=$args[$c+1]; $c++;} break;
  }

 if($archive)
 {
  $ret = GShell("dynarc archive-info '".$archive."'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
 }
 else if($archivePrefix)
 {
  $ret = GShell("dynarc archive-info -prefix '".$archivePrefix."'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
 }
 else if($archiveId)
 {
  $ret = GShell("dynarc archive-info -id '".$archiveId."'",$sessid,$shellid, $extraVar);
  if($ret['error'])
   return $ret;
 }
 else
 {
  $out.= "You must specify archive. (with: -archive ARCHIVE_NAME, or -aid ARCHIVE_ID, or -ap ARCHIVE_PREFIX)\n";
  return array("message"=>$out,"error"=>"INVALID_ARCHIVE");
 }
 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//

