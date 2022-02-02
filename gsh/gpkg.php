<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 22-10-2013
 #PACKAGE: gpkg
 #DESCRIPTION: Gnujiko Package Tool
 #VERSION: 2.4beta
 #CHANGELOG: 22-10-2013 : Autenticazione.
			 15-04-2013 : Bug fix su funzione file-find.
			 11-04-2013 : Sistemato i permessi ai files.
			 05-02-2013 : Aggiunto exec-ordering su funzione check-depends.
			 11-01-2013 : Bug fix in upgrade function.
 #TODO: Completare funzione auto-check-depends
 
*/

function shell_gpkg($args, $sessid, $shellid=0)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $output = "";
 $outArr = array();

 if(count($args) == 0)
  return gpkg_invalidArguments();

 switch($args[0])
 {
  case 'new' : return gpkg_newPackage($args, $sessid, $shellid); break;
  case 'edit' : return gpkg_editPackage($args, $sessid, $shellid); break;
  case 'add-file' : return gpkg_addFile($args, $sessid, $shellid); break;
  case 'build' : return gpkg_build($args, $sessid, $shellid); break;
  case 'download' : return gpkg_download($args, $sessid, $shellid); break;
  case 'install' : return gpkg_install($args, $sessid, $shellid); break;
  case 'upgrade' : return gpkg_upgrade($args, $sessid, $shellid); break;
  case 'reinstall' : return gpkg_reInstall($args, $sessid, $shellid); break;
  case 'remove' : return gpkg_remove($args, $sessid, $shellid); break;
  case 'info' : return gpkg_info($args, $sessid, $shellid); break;
  case 'list' : return gpkg_list($args, $sessid, $shellid); break;
  case 'resolve' : return gpkg_resolve($args, $sessid, $shellid); break;
  // ACTIONS //
  case 'publish' : return gpkg_publish($args, $sessid, $shellid); break;
  // SERVERS //
  case 'add-server' : return gpkg_addServer($args, $sessid, $shellid); break;
  case 'edit-server' : return gpkg_editServer($args, $sessid, $shellid); break;
  case 'delete-server' : return gpkg_deleteServer($args, $sessid, $shellid); break;
  case 'server-info' : return gpkg_serverInfo($args, $sessid, $shellid); break;
  case 'server-list' : return gpkg_serverList($args, $sessid, $shellid); break;
  // OTHER FUNCTIONS //
  case 'file-info' : return gpkg_fileInfo($args, $sessid, $shellid); break;
  case 'file-changed' : return gpkg_fileChanged($args, $sessid, $shellid); break;
  case 'file-find' : return gpkg_fileFind($args, $sessid, $shellid); break;
  case 'auto-check-depends' : return gpkg_autoCheckDepends($args, $sessid, $shellid); break;
  case 'reverse-check-depends' : return gpkg_reverseCheckDepends($args, $sessid, $shellid); break; /*Instead of checking the dependencies of a given package, this command returns a list of all packages that depend on the specified package.*/

  default : return gpkg_invalidArguments(); break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_invalidArguments()
{
 return array("message"=>"Invalid arguments.", "error"=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_newPackage($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_DEFAULT_FILE_PERMS;
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-section' : {$section=$args[$c+1]; $c++;} break;
   case '-depends' : {$depends=$args[$c+1]; $c++;} break;
   case '-replaces' : {$replaces=$args[$c+1]; $c++;} break;
   case '-version' : {$version=$args[$c+1]; $c++;} break;
   case '-maintainer' : {$maintainer=$args[$c+1]; $c++;} break;
   case '-desc' : case '-description' : {$desc=$args[$c+1]; $c++;} break;
   case '--essential' : $essential=1; break;
   case '--pre-depends' : {$predepends=$args[$c+1]; $c++;} break;
   default: {if(!$name)$name=$args[$c];} break;
  }

 // verifica se il pacchetto esiste già //
 if(file_exists($_BASE_PATH."var/packages/$name/packageinfo.xml"))
  return array("message"=>"Package $name already exists.\n", "error"=>"PACKAGE_ALREADY_EXISTS");

 // crea cartella pacchetto//
 $out.= "Creating package folder...";
 $ret = GShell("mkdir var/packages/$name",$sessid, $shellid);
 if($ret['error'])
  return array("message"=>$out.$ret['message'],"error"=>$ret['error']);
 $path = $ret['outarr']['path'];
 $out.= "done! package folder is: $path \n";

 $maintainer = xml_purify($maintainer);
 $desc = xml_purify($desc);

 // crea file packageinfo //
 $out.= "Creating packageinfo file...";
 $packinfo = "<xml><package name='$name' section='$section'"
	.($predepends ? " predepends='$predepends'" : "")
	.($depends ? " depends='$depends'" : "")
	.($replaces ? " replaces='$replaces'" : "")." version='$version' maintainer='$maintainer' description='$desc'"
	.($essential ? " essential='yes'" : "")."/></xml>";

 // write to file //
 $h = fopen($_BASE_PATH.$path."packageinfo.xml","wt");
 if(!$h)
  return array("message"=>"Unable to write packageinfo.xml into folder $path \n","error"=>"BAD_PERMISSION");
 if(fwrite($h,$packinfo) === FALSE)
  return array("message"=>"Unable to create packageinfo.xml","error"=>"BAD_PERMISSION");
 fclose($h);
 @chmod($_BASE_PATH.$path."packageinfo.xml",$_DEFAULT_FILE_PERMS);
 $out.= "done!\n";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_editPackage($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_DEFAULT_FILE_PERMS;
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-section' : {$section=$args[$c+1]; $c++;} break;
   case '-depends' : {$depends=$args[$c+1]; $c++;} break;
   case '-replaces' : {$replaces=$args[$c+1]; $c++;} break;
   case '-version' : {$version=$args[$c+1]; $c++;} break;
   case '-maintainer' : {$maintainer=$args[$c+1]; $c++;} break;
   case '-desc' : case '-description' : {$desc=$args[$c+1]; $c++;} break;
   case '-essential' : {$essential=$args[$c+1]; $c++;} break;
   case '--pre-depends' : {$predepends=$args[$c+1]; $c++;} break;
   case '--add-depends' : {$adddepends=$args[$c+1]; $c++;} break;
   default: {if(!$name)$name=$args[$c]; else return array('message'=>'Invalid argument '.$args[$c],'error'=>'INVALID_ARGUMENT'); } break;
  }

 // verifica se il pacchetto esiste //
 $path = $_BASE_PATH."var/packages/$name/";
 if(!file_exists($path."packageinfo.xml"))
  return array("message"=>"Package $name does not exists.\n", "error"=>"PACKAGE_DOES_NOT_EXISTS");

 include_once($_BASE_PATH."var/lib/xmllib.php");

 $xml = new GXML($path."packageinfo.xml");
 $el = $xml->GetElementsByTagName('package');
 $orig = $el[0];
 $section = $section ? $section : $orig->getString('section');
 $depends = isset($depends) ? $depends : $orig->getString('depends');
 if($adddepends)
  $depends = !$depends ? $adddepends : $depends.",".$adddepends;
 $replaces = $replaces ? $replaces : $orig->getString('replaces');
 $version = $version ? $version : $orig->getString('version');
 $maintainer = $maintainer ? $maintainer : $orig->getString('maintainer');
 $desc = $desc ? $desc : $orig->getString('description');
 $essential = isset($essential) ? $essential : $orig->getString('essential');
 $predepends = $predepends ? $predepends : $orig->getString('predepends');

 $maintainer = xml_purify($maintainer);
 $desc = xml_purify($desc);

 // crea file packageinfo //
 $out.= "Updating packageinfo file...";
 $packinfo = "<xml><package name='$name' section='$section'"
	.($predepends ? " predepends='$predepends'" : "")
	.($depends ? " depends='$depends'" : "")
	.($replaces ? " replaces='$replaces'" : "")." version='$version' maintainer='$maintainer' description='$desc'"
	.($essential ? " essential='yes'" : "")."/></xml>";

 // write to file //
 $h = fopen($path."packageinfo.xml","wt");
 if(!$h)
  return array("message"=>"Unable to write packageinfo.xml into folder $path \n","error"=>"BAD_PERMISSION");
 if(fwrite($h,$packinfo) === FALSE)
  return array("message"=>"Unable to create packageinfo.xml","error"=>"BAD_PERMISSION");
 fclose($h);
 @chmod($_BASE_PATH.$path."packageinfo.xml",$_DEFAULT_FILE_PERMS);
 $out.= "done!\n";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_addFile($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_DEFAULT_FILE_PERMS;
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-package' : {$pkgname=$args[$c+1]; $c++;} break;
   case '-dir' : {$dir=ltrim($args[$c+1],"/"); $c++;} break;
   case '-file' : case '-f' : {$file=ltrim($args[$c+1],"/"); $c++;} break;
   case '-files' : {$files = explode(" ",$args[$c+1]); $c++;} break;
   case '-dest' : {$dest=ltrim($args[$c+1],"/"); $c++;} break;
   default : return array('message'=>"Invalid argument ".$args[$c],'error'=>"INVALID_ARGUMENT"); break;
  }

 if(!$pkgname)
  return array("message"=>"You must specify package. (with -package PACKAGENAME)\n","error"=>"INVALID_PACKAGE_NAME");
 // verifica se il pacchetto esiste //
 if(!file_exists($_BASE_PATH."var/packages/$pkgname/packageinfo.xml"))
  return array("message"=>"Package $pkgname does not exists!.\n","error"=>"PACKAGE_DOES_NOT_EXISTS");

 if(!is_dir($_BASE_PATH."var/packages/$pkgname/__files"))
 {
  $ret = GShell("mkdir var/packages/$pkgname/__files",$sessid,$shellid);
  if($ret['error'])
   return array("message"=>"Unable to create __files into dir var/packages/$pkgname/ \n","error"=>$ret['error']);
  $out.= "Folder __files created automatically.\n";
 }
 
 // copia dei file e directory //
 include_once($_BASE_PATH."include/filesfunc.php");
 $err = "";
 $out = "";
 if(!$dest) $dest = $dir ? $dir : $file;
 if($dir)
 {
  if(!full_copy($_BASE_PATH.$dir, $_BASE_PATH."var/packages/$pkgname/__files/$dest",$_DEFAULT_FILE_PERMS))
   return array("message"=>"Unable to copy $dir to var/packages/$pkgname/__files/$dest","error"=>"DIR_COPY_FAILED");
 }
 else if($file)
 {
  if(!full_copy($_BASE_PATH.$file, $_BASE_PATH."var/packages/$pkgname/__files/$dest",$_DEFAULT_FILE_PERMS))
   return array("message"=>"Unable to copy $file to var/packages/$pkgname/__files/$dest","error"=>"FILE_COPY_FAILED");
 }
 else if(count($files))
 {
  for($c=0; $c < count($files); $c++)
  {
   $file = ltrim($files[$c],"/");
   if(!full_copy($_BASE_PATH.$file, $_BASE_PATH."var/packages/$pkgname/__files/$file",$_DEFAULT_FILE_PERMS))
    return array("message"=>"Unable to copy $file to var/packages/$pkgname/__files/$file","error"=>"FILE_COPY_FAILED");
  }
  $out.= count($files)." files has been copied.\n";
 }

 $out.= "done!\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_build($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $packages = array();
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-package' : {$packages[]=$args[$c+1]; $c++;} break;
   default: $packages[]=$args[$c]; break;
  }

 for($c=0; $c < count($packages); $c++)
 {
  $pkgname = $packages[$c];
  if(!$pkgname)
   return array("message"=>"You must specify package (with -package PACKAGENAME)\n","error"=>"INVALID_PACKAGE_NAME");
  // verifica se il pacchetto esiste //
  if(!file_exists($_BASE_PATH."var/packages/$pkgname/packageinfo.xml"))
   return array("message"=>"Package $pkgname does not exists!.\n","error"=>"PACKAGE_DOES_NOT_EXISTS");
  // comprime l'archivio //
  $ret = GShell("zip var/packages/$pkgname/ var/packages/$pkgname.zip", $sessid, $shellid);
  if($ret['error'])
   return array("message"=>"Compression failed!\n".$ret['message'],"error"=>$ret['error']);
 }
 $out.= "done";
 return array('message'=>$out); 
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_download($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH;

 $out = "";
 $outArr = array();
 $packages = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-package' : {$packages[] = $args[$c+1]; $c++;} break;
   case '-dest' : {$destination=ltrim($args[$c+1],"/"); $c++;} break;
   default : $packages[] = $args[$c]; break;
  }

 if(!$destination)
  $destination = "var/packages/";

 if(!count($packages))
  return array("message"=>"You must specify at least one package. (with -package PACKAGE_NAME)", "error"=>"INVALID_PACKAGE");
 
 for($c=0; $c < count($packages); $c++)
 {
  $ret = GShell("gpkg info '".$packages[$c]."'",$sessid, $shellid);
  if($ret['error'])
   return $ret;
  $pack = $ret['outarr'];
  $rep = $pack['repository'];
  if(!$rep)
   return array("message"=>"Unable to detect repository from package ".$pack['name'],"error"=>"INVALID_PACKAGE_REPOSITORY");
  gshPreOutput($shellid,"Downloading package ".$pack['name']." from ".$rep, "DOWNLOAD_PACKAGE", $pack['name']);
  //$url = rtrim($rep,"/")."/data/".$pack['name'].".zip";
  //$buffer = gpkg_http_get($url,$shellid,"DOWNLOADING",$pack['name'],"SINGLE_LINE");
  $ret = gpkg_get_package(rtrim($rep,"/"), $pack['name'], $shellid,"DOWNLOADING",$pack['name'],"SINGLE_LINE");
  if($ret['error'])
   return $ret;

  $buffer = $ret['message'];

  if($_FTP_USERNAME)
  {
   // TRY WITH FTP //
   if(!@gftpwrite(rtrim($destination,"/")."/".$pack['name'].".zip",$buffer))
    return array("message"=>"Unable to download package into folder $destination. Please check permissions!","error"=>"PERMISSION_DENIED");
  }
  else
  {
   // COPY IN NORMAL MODE //
   $dest = @fopen($_BASE_PATH.rtrim($destination,"/")."/".$pack['name'].".zip","wb");
   if(@fwrite($dest,$buffer)===false)
	return array("message"=>"Unable to download package into folder $destination. Please check permissions!","error"=>"PERMISSION_DENIED");
   @fclose($dest);
  }
  $out.= "Package ".$pack['name']." has been downloaded into folder $destination\n";
  $outArr[] = array('name'=>$pack['name'],'path'=>rtrim($destination,"/")."/");
 }
 $out.= "done!";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_install($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_DEFAULT_FILE_PERMS;
 $out = "";
 $outArr = array();
 $packages = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-package' : {$packages[] = $args[$c+1]; $c++;} break;
   case '-from' : {$from=ltrim($args[$c+1],"/"); $c++;} break;
   case '--configure-only' : $configureOnly=true; break;
   case '--force' : $force=true; break;
   case '--no-resolve' : $noResolve=true; break; // force for no resolving dependences //
   case '--no-download' : $noDownload=true; break; // force for non downloading the package //
   default : $packages[] = $args[$c]; break;
  }

 if(!$from)
 {
  $from = "var/packages/";
  $pkgpath = $_BASE_PATH.$from;
 }
 else
  $pkgpath = rtrim($from,"/")."/";

 if(!count($packages))
  return array("message"=>"You must specify at least one package. (with -package PACKAGE_NAME)","error"=>"INVALID_PACKAGE");

 for($c=0; $c < count($packages); $c++)
 {
  $out = "";
  $ret = GShell("gpkg info '".$packages[$c]."'",$sessid, $shellid);
  if($ret['error'])
   return $ret;
  $packInfo = $ret['outarr'];

  // verify if already installed //
  if($packInfo['installed_version'])
   return array("message"=>"Package ".$packInfo['name']."' is already installed.","error"=>"PACKAGE_ALREADY_INSTALLED");

  // verify if package file exists //
  if(!file_exists($pkgpath.$packInfo['name'].".zip"))
  {
   // download //
   if(!$noDownload)
   {
    $ret = GShell("gpkg download '".$packInfo['name']."'",$sessid,$shellid);
    if($ret['error'])
	 return $ret;
   }
   if(!file_exists($pkgpath.$packInfo['name'].".zip"))
    return array("message"=>"Package ".$packInfo['name']." does not exists into folder $from","error"=>"PACKAGE_IS_NOT_INTO_FOLDER");
  }

  if(!$noResolve)
  {
   $ret = GShell("gpkg resolve -i '".$packInfo['name']."'".($force ? " --force" : ""),$sessid,$shellid);
   if(!$configureOnly)
    continue;
  }

  if(!$configureOnly)
  {
   // decompress package //
   gshPreOutput($shellid,"Decompress package ".$packInfo['name']." ...", "DECOMPRESS_PACKAGE", $packInfo['name']);
   $ret = GShell("unzip ".$from.$packInfo['name'].".zip tmp/packages/".$packInfo['name']."/",$sessid,$shellid);
   if($ret['error'])
	return array("message"=>"Unable to decompress package ".$packInfo['name']." into folder tmp/packages/\n".$ret['message'],'error'=>$ret['error']);
   // copy files //
   gshPreOutput($shellid,"Copy files...", "COPY_FILES", $packInfo['name']);
   if(file_exists($_BASE_PATH."tmp/packages/".$packInfo['name']."/__files/") && !full_copy($_BASE_PATH."tmp/packages/".$packInfo['name']."/__files/",$_BASE_PATH,$_DEFAULT_FILE_PERMS))
	return array("message"=>"Unable to copy files from ".$_BASE_PATH."tmp/packages/".$packInfo['name']."/__files/","error"=>"UNABLE_TO_COPY_FILES");
  }

  // run packageinstall.php (if exists) //
  if(file_exists($_BASE_PATH."tmp/packages/".$packInfo['name']."/packageinstall.php"))
  {
   gshPreOutput($shellid,"Configuring package ".$packInfo['name'], "CONFIGURING_PACKAGE", $packInfo['name']);
   global $_SHELL_OUT, $_SHELL_ERR, $_SESSION_ID, $_SHELL_ID;
   $_SHELL_OUT = "";
   $_SHELL_ERR = "";
   $_SESSION_ID = $sessid;
   $_SHELL_ID = $shellid;
   define("VALID-GNUJIKO",1);
   include_once($_BASE_PATH."tmp/packages/".$packInfo['name']."/packageinstall.php");
   if($_SHELL_ERR)
	return array("message"=>$_SHELL_OUT, "error"=>$_SHELL_ERR);
   $out.= $_SHELL_OUT;
  }

  // remove temporary package dir //
  $out.= "Removing temporary files for ".$packInfo['name']."...";
  @rmdirr($_BASE_PATH."tmp/packages/".$packInfo['name']."/");
  $out.= "done!\n\n";

  // update db info //
  $out.= "Updating package info into database...";
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM gnujiko_packages WHERE name='".$packInfo['name']."' LIMIT 1");
  if(!$db->Read())
   $db->RunQuery("INSERT INTO gnujiko_packages(name,version,installed_version,essential,depends,replaces,conflicts,
	pre_depends,section,maintainer,description) VALUES('".$packInfo['name']."','".$packInfo['version']."','"
	.$packInfo['version']."','".($packInfo['essential'] ? 1 : 0)."','".$packInfo['depends']."','".$packInfo['replaces']."','"
	.$packInfo['conflicts']."','".$packInfo['pre_depends']."','".$packInfo['section']."','".$packInfo['maintainer']."','"
	.$packInfo['description']."')");
  else
   $db->RunQuery("UPDATE gnujiko_packages SET installed_version='".$packInfo['version']."' WHERE id='".$db->record['id']."'");
  $db->Close();
  $out.= "done!\n";
  gshPreOutput($shellid, $out, "PACKAGE_INSTALLED", $packInfo['name']);
 }
 return array("message"=>"Success");
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_upgrade($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_DEFAULT_FILE_PERMS;
 $out = "";
 $outArr = array();
 $packages = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-package' : {$packages[] = $args[$c+1]; $c++;} break;
   case '-from' : {$from=ltrim($args[$c+1],"/"); $c++;} break;
   case '--configure-only' : $configureOnly=true; break;
   case '--force' : $force=true; break;
   case '--no-resolve' : $noResolve=true; break; // force for no resolving dependences //
   default : $packages[] = $args[$c]; break;
  }

 if(!$from)
 {
  $from = "var/packages/";
  $pkgpath = $_BASE_PATH.$from;
 }
 else
  $pkgpath = rtrim($from,"/")."/";

 if(!count($packages))
  return array("message"=>"You must specify at least one package. (with -package PACKAGE_NAME)","error"=>"INVALID_PACKAGE");

 for($c=0; $c < count($packages); $c++)
 {
  $ret = GShell("gpkg info '".$packages[$c]."'",$sessid, $shellid);
  if($ret['error'])
   return $ret;
  $packInfo = $ret['outarr'];

  // verify if already installed //
  if(!$packInfo['installed_version'])
   return array("message"=>"Package ".$packInfo['name']." is not installed.","error"=>"PACKAGE_IS_NOT_INSTALLED");
  if($packInfo['installed_version'] == $packInfo['version'])
   return array("message"=>"Package ".$packInfo['name']." is already the updated version.","error"=>"PACKAGE_ALREADY_UPDATED_VERSION"); 

  // verify if package file exists //
  if(!file_exists($pkgpath.$packInfo['name'].".zip"))
  {
   // download package //
   GShell("gpkg download ".$packInfo['name'],$sessid,$shellid);
   //return array("message"=>"Package ".$packInfo['name']." does not exists into folder $from","error"=>"PACKAGE_IS_NOT_INTO_FOLDER");
  }

  if(!$noResolve)
  {
   $ret = GShell("gpkg resolve -i '".$packInfo['name']."'".($force ? " --force" : ""),$sessid,$shellid);
   continue;
  }

  if(!$configureOnly)
  {
   // decompress package //
   gshPreOutput($shellid,"Decompress package ".$packInfo['name']." ...", "DECOMPRESS_PACKAGE", $packInfo['name']);
   $ret = GShell("unzip ".$from.$packInfo['name'].".zip tmp/packages/".$packInfo['name']."/",$sessid,$shellid);
   if($ret['error'])
	return array("message"=>"Unable to decompress package ".$packInfo['name']." into folder tmp/packages/\n".$ret['message'],'error'=>$ret['error']);
   // copy files //
   gshPreOutput($shellid,"Copy files...", "COPY_FILES", $packInfo['name']);
   $_out = ""; $_err = "";
   if(!full_copy($_BASE_PATH."tmp/packages/".$packInfo['name']."/__files/",$_BASE_PATH,$_DEFAULT_FILE_PERMS,$_out,$_err))
	return array("message"=>"Unable to copy files from ".$_BASE_PATH."tmp/packages/".$packInfo['name']."/__files/\n".$_out,"error"=>"UNABLE_TO_COPY_FILES");
  }

  // run packageupdate.php (if exists) //
  if(file_exists($_BASE_PATH."tmp/packages/".$packInfo['name']."/packageupdate.php"))
  {
   $out.= "Updating package ".$packInfo['name']."\n";
   global $_SHELL_OUT, $_SHELL_ERR, $_SESSION_ID, $_SHELL_ID;
   $_SHELL_OUT = "";
   $_SHELL_ERR = "";
   $_SESSION_ID = $sessid;
   $_SHELL_ID = $shellid;

   define("VALID-GNUJIKO",1);
   include_once($_BASE_PATH."tmp/packages/".$packInfo['name']."/packageupdate.php");
   if($_SHELL_ERR)
	return array("message"=>$_SHELL_OUT, "error"=>$_SHELL_ERR);
   $out.= $_SHELL_OUT;
  }

  // remove temporary package dir //
  $out.= "Removing temporary files...";
  @rmdirr($_BASE_PATH."tmp/packages/".$packInfo['name']."/");
  $out.= "done!\n\n";

  // update db info //
  $out.= "Updating package info into database...";
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM gnujiko_packages WHERE name='".$packInfo['name']."' LIMIT 1");
  $db->Read();
  $db->RunQuery("UPDATE gnujiko_packages SET installed_version='".$packInfo['version']."' WHERE id='".$db->record['id']."'");
  $db->Close();
  $out.= "done!\n";
  gshPreOutput($shellid, $out, "PACKAGE_UPDATED", $packInfo['name']);
 }
 return array("message"=>"Success!");
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_reInstall($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();
 $packages = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-package' : {$packages[] = $args[$c+1]; $c++;} break;
   case '-from' : {$from=ltrim($args[$c+1],"/"); $c++;} break;
   case '--configure-only' : $configureOnly=true; break;
   case '--force' : $force=true; break;
   default : $packages[] = $args[$c]; break;
  }
 
 if(!count($packages))
  return array("message"=>"You must specify at least one package. (with -package PACKAGE_NAME)","error"=>"INVALID_PACKAGE");

 // uninstall packages //
 $ret = GShell("gpkg remove ".implode(" ",$packages).($force ? " --force" : ""),$sessid,$shellid);
 if($ret['error'])
  return $ret;

 // install packages //
 $ret = GShell("gpkg install ".implode(" ",$packages)
	.($force ? " --force" : "")
	.($from ? " -from $from" : "")
	.($configureOnly ? " --configure-only" : ""),$sessid,$shellid);
 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_remove($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH;
 $out = "";
 $outArr = array();
 $packages = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-package' : {$packages[] = $args[$c+1]; $c++;} break;
   case '--force' : $force=true; break;
   case '-R' : case '-r' : $remove=true; break;
   default : $packages[] = $args[$c]; break;
  }

 if(!count($packages))
  return array("message"=>"You must specify at least one package. (with -package PACKAGE_NAME)","error"=>"INVALID_PACKAGE");

 for($c=0; $c < count($packages); $c++)
 {
  $ret = GShell("gpkg info '".$packages[$c]."'",$sessid, $shellid);
  if($ret['error'])
   return $ret;
  $packInfo = $ret['outarr'];

  // verify if package is installed //
  if(!$packInfo['installed_version'] && !$force)
   return array("message"=>"Package ".$packInfo['name']." is not installed.", "error"=>"PACKAGE_IS_NOT_INSTALLED");
  
  // Verify if is not a essential package //
  if($packInfo['essential'] && !$force)
   return array("message"=>$packInfo['name']." is a essential package,if it is removed can compromise the system. You can remove it at your own risk using --force into arguments.", "error"=>"CANNOT_REMOVE_ESSENTIAL_PACKAGE");

  // exec packageremove.php (if exists) //
  $ret = GShell("unzip var/packages/".$packInfo['name'].".zip tmp/packages/".$packInfo['name']."/ -file packageremove.php",$sessid,$shellid);
  if(!$ret['error'])
  {
   gshPreOutput($shellid,"Removing package ".$packInfo['name'], "REMOVING_PACKAGE", $packInfo['name']);
   global $_SHELL_OUT, $_SHELL_ERR, $_SESSION_ID, $_SHELL_ID;
   $_SHELL_OUT = "";
   $_SHELL_ERR = "";
   $_SESSION_ID = $sessid;
   $_SHELL_ID = $shellid;

   
   if($remove && file_exists($_BASE_PATH."tmp/packages/".$packInfo['name']."/packageremove.php"))
   {
    define("VALID-GNUJIKO",1);
    include_once($_BASE_PATH."tmp/packages/".$packInfo['name']."/packageremove.php");
    if($_SHELL_ERR)
	 return array("message"=>$out." failed!\n".$_SHELL_OUT,"error"=>$_SHELL_ERR);
    $out.= $_SHELL_OUT;
   }
   else if(file_exists($_BASE_PATH."tmp/packages/".$packInfo['name']."/packageuninstall.php"))
   {
    define("VALID-GNUJIKO",1);
    include_once($_BASE_PATH."tmp/packages/".$packInfo['name']."/packageuninstall.php");
    if($_SHELL_ERR)
	 return array("message"=>$out." failed!\n".$_SHELL_OUT,"error"=>$_SHELL_ERR);
    $out.= $_SHELL_OUT;
   }
   else if(file_exists($_BASE_PATH."tmp/packages/".$packInfo['name']."/packageremove.php"))
   {
    define("VALID-GNUJIKO",1);
    include_once($_BASE_PATH."tmp/packages/".$packInfo['name']."/packageremove.php");
    if($_SHELL_ERR)
	 return array("message"=>$out." failed!\n".$_SHELL_OUT,"error"=>$_SHELL_ERR);
    $out.= $_SHELL_OUT;
   }
  }

  // remove files //
  $ret = GShell("unzip var/packages/".$packInfo['name'].".zip -list",$sessid,$shellid);
  if($ret['error'])
  {
   if($force)
   {
	$db = new AlpaDatabase();
	$db->RunQuery("UPDATE gnujiko_packages SET installed_version='' WHERE name='".$packInfo['name']."'");
	$db->Close();
	$out.= "done!";
   }
   else
	return $ret;
  }
  $list = $ret['outarr'];
  $dirs = array();
  for($i=0; $i < count($list['files']); $i++)
  {
   if(substr($list['files'][$i],0,8) != "__files/")
    continue;
   if(file_exists($_BASE_PATH.substr($list['files'][$i],8)))
   {
	if($_FTP_USERNAME)
	{
	 $conn = @ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
	 if(@ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
     {
      if($_FTP_PATH)
      {
       $fileName = str_replace("../","",substr($list['files'][$i],8));
       $fileName = $_FTP_PATH.$fileName;
      }
      else
       $fileName = substr($list['files'][$i],8);
      if(!@ftp_delete($conn, $fileName))
       return array("message"=>"Unable to remove ".$fileName,"error"=>"UNABLE_TO_REMOVE_FILE");
	  @ftp_close($conn);
     }
	 else
	  return array("message"=>"Unable to remove ".substr($list['files'][$i],8),"error"=>"UNABLE_TO_REMOVE_FILE");
	}
	else
	{
	 if(!@unlink($_BASE_PATH.substr($list['files'][$i],8)))
	  return array("message"=>"Unable to remove ".substr($list['files'][$i],8),"error"=>"UNABLE_TO_REMOVE_FILE");
	}
   }

   $dx = explode("/",dirname(substr($list['files'][$i],8)));
   $dr = "";
   for($d=0; $d < count($dx); $d++)
   {
    $dr.= $dx[$d]."/";
    $dirs[rtrim($dr,"/")] = true;
   }
  }

  // remove empty folders //
  $dirs = array_reverse($dirs);
  while(list($k,$v) = each($dirs))
   @rmdir($_BASE_PATH.$k);

  // update db //
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE gnujiko_packages SET installed_version='' WHERE name='".$packInfo['name']."'");
  $db->Close();
  $outArr[] = $packInfo['name'];
  gshPreOutput($shellid, "done!", "PACKAGE_REMOVED", $packInfo['name']);
 }
 return array("message"=>"Success!", "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_info($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-repo' : case '-repository' : {$repository=$args[$c+1]; $c++;} break;
   case '--get-local-ver' : $getLocalVer=true; break;
   case '--verbose' : $verbose=true; break;
   case '-local' : $local=true; break;
   default: {if(!$name)$name=$args[$c];} break;
  }

 if(!$local)
 {
  /* Verifica se si trova nel database */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM gnujiko_packages WHERE name='".$name."'");
  if($db->Read())
  {
   // detect status //
   if($db->record['installed_version'])
   {
    if(version_compare($db->record['version'],$db->record['installed_version']) > 0)
	 $status = "outdated";
    else
	 $status = "installed";
   }
   else
    $status = "available";
   $outArr = array('name'=>$db->record['name'],'version'=>$db->record['version'],'installed_version'=>$db->record['installed_version'],
	'section'=>$db->record['section'],'maintainer'=>$db->record['maintainer'],'essential'=>$db->record['essential'],
	'pre-depends'=>$db->record['pre_depends'],'depends'=>$db->record['depends'],'replaces'=>$db->record['replaces'],
	'conflicts'=>$db->record['conflicts'],'description'=>$db->record['description'],'repository'=>$db->record['repository'],'status'=>$status);

   if($getLocalVer)
   {
    // verifica se esiste la versione locale */
    if(file_exists($_BASE_PATH."var/packages/$name/packageinfo.xml"))
    {
	 include_once($_BASE_PATH."var/lib/xmllib.php");
	 $xml = new GXML($_BASE_PATH."var/packages/$name/packageinfo.xml");
	 $el = $xml->GetElementsByTagName('package');
	 $outArr['local_version'] = $el[0]->getString('version');
    }
   }
   $intoDB = true;
  }
  else
   $intoDB = false;
  $db->Close();
 }

 if((!$intoDB || $local) && file_exists($_BASE_PATH."var/packages/$name/packageinfo.xml")) // verifica se esiste la copia locale scompattata //
 {
  include_once($_BASE_PATH."var/lib/xmllib.php");
  $xml = new GXML($_BASE_PATH."var/packages/$name/packageinfo.xml");
  $el = $xml->GetElementsByTagName('package');
  $outArr = array('name'=>$el[0]->getString('name'),'version'=>$el[0]->getString('version'),'section'=>$el[0]->getString('section'),
	'maintainer'=>$el[0]->getString('maintainer'),'essential'=>$el[0]->getString('essential'),'pre-depends'=>$el[0]->getString('predepends'),
	'depends'=>$el[0]->getString('depends'),'replaces'=>$el[0]->getString('replaces'),'conflicts'=>$el[0]->getString('conflicts'),
	'description'=>$el[0]->getString('description'),'local_version'=>$el[0]->getString('version'));
 }
 else if((!$intoDB || $local) && file_exists($_BASE_PATH."var/packages/$name.zip")) // verifica se esiste la copia locale .zip //
 {
  if(!file_exists($_BASE_PATH.'var/lib/zip/unzip.lib.php'))
   return array("message"=>"Library zip does not exists","error"=>"LIBRARY_DOES_NOT_EXISTS");
  include_once($_BASE_PATH.'var/lib/zip/unzip.lib.php');
  $zip = new SimpleUnzip($_BASE_PATH."var/packages/$name.zip");
  for($c=0; $c < $zip->Count(); $c++)
  {
   if(ltrim($zip->GetPath($c)."/".$zip->GetName($c),"/") != "packageinfo.xml")
	continue;
   include_once($_BASE_PATH."var/lib/xmllib.php");
   $xml = new GXML();
   $xml->LoadFromString($zip->GetData($c));
   $el = $xml->GetElementsByTagName('package');
   $outArr = array('name'=>$el[0]->getString('name'),'version'=>$el[0]->getString('version'),'section'=>$el[0]->getString('section'),
	'maintainer'=>$el[0]->getString('maintainer'),'essential'=>$el[0]->getString('essential'),'pre-depends'=>$el[0]->getString('predepends'),
	'depends'=>$el[0]->getString('depends'),'replaces'=>$el[0]->getString('replaces'),'conflicts'=>$el[0]->getString('conflicts'),
	'description'=>$el[0]->getString('description'),'compiled_version'=>$el[0]->getString('version'));
   break;
  }
 }
 else if(!$intoDB)
 {
  return array("message"=>"Package $name does not exists.\n", "error"=>"PACKAGE_DOES_NOT_EXISTS");
 }

 if($verbose)
 {
   $out.= "Package: ".$outArr['name']."\n";
   $out.= "Version: ".$outArr['version']."\n";
   $out.= "Installed version: ".$outArr['installed_version']."\n";
   if($getLocalVer)
	$out.= "Local version: ".$outArr['local_version']."\n";
   $out.= "Section: ".$outArr['section']."\n";
   $out.= "Maintainer: ".$outArr['maintainer']."\n";
   if($outArr['essential'])
    $out.= "Essential: yes\n";
   if($outArr['predepends'])
    $out.= "Pre-depends: ".$outArr['pre_depends']."\n";
   if($outArr['depends'])
    $out.= "Depends: ".$outArr['depends']."\n";
   if($outArr['replaces'])
    $out.= "Replaces: ".$outArr['replaces']."\n";
   if($outArr['conflicts'])
	$out.= "Conflicts: ".$outArr['conflicts']."\n";
   $out.= "Description: ".$outArr['description']."\n";
   $out.= "Repository: ".($outArr['repository'] ? $outArr['repository'] : "this is a local package")."\n";
   $out.= "Status: $status\n";
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_list($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $orderBy = "name ASC";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-section' : {$section=$args[$c+1]; $c++;} break;
   case '-installed' : $installed=true; break;
   case '-local' : $local=true; break;
   case '--verbose' : $verbose=true; break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '--get-local-ver' : $getLocalVer=true; break;
  }

 if($local)
 {
  if($limit)
  {
   if(strpos($limit,",") !== false)
   {
    $x = explode(",",$limit);
    $limitFrom = (int)$x[0];
    $limitSize = (int)$x[1];
    $limitTo = $limitFrom+$limitSize;
   }
   else
   {
    $limitFrom = 0;
    $limitTo = (int)$limit;
	$limitSize = (int)$limit;
   }
  }

  $packages = array();
  /* Detect compiled packages */
  $ret = GShell("ls /var/packages/ -f -filter zip");
  $list = $ret['outarr']['files'];
  for($c=0; $c < count($list); $c++)
   $packages[] = rtrim($list[$c]['name'],".zip");

  /* Detect un-compiled packages */
  $ret = GShell("ls /var/packages/ -d");
  $list = $ret['outarr']['dirs'];
  for($c=0; $c < count($list); $c++)
  {
   if(!in_array($list[$c]['name'],$packages))
	$packages[] = $list[$c]['name'];
  }

  sort($packages);

  $list = array();
  foreach($packages as $k => $package)
  {
   $ret = GShell("gpkg info `".$package."` -local");
   if(!$ret['error'])
   {
	$pkg = $ret['outarr'];
	if($section && ($pkg['section'] != $section))
	 continue;
	$list[] = $pkg;
   }
  }

  $outArr['count'] = count($list);

  if($verbose)
   $out.= "<table><tr><th>Name</th><th>Local version</th><th>Compiled version</th><th>Published version</th></tr>";

  $count = $limit ? ($limitTo > count($list) ? count($list) : $limitTo) : count($list);

  for($i = ($limitFrom ? $limitFrom : 0); $i < $count; $i++)
  {
   $pkg = $list[$i];
    /* Check online version */
    $db = new AlpaDatabase();
    $db->RunQuery("SELECT version,repository FROM gnujiko_packages WHERE name='".$pkg['name']."'");
	if($db->Read())
	{
	 $pkg['online_version'] = $db->record['version'];
	 $pkg['repository'] = $db->record['repository'];
	}
	$db->Close();
    
	if(!$pkg['compiled_version'] && file_exists($_BASE_PATH."var/packages/".$pkg['name'].".zip"))
	{
	 /* Check compiled version */
	 if(!file_exists($_BASE_PATH.'var/lib/zip/unzip.lib.php'))
      return array("message"=>"Library zip does not exists","error"=>"LIBRARY_DOES_NOT_EXISTS");
     include_once($_BASE_PATH.'var/lib/zip/unzip.lib.php');
     $zip = new SimpleUnzip($_BASE_PATH."var/packages/".$pkg['name'].".zip");
     for($c=0; $c < $zip->Count(); $c++)
     {
      if(ltrim($zip->GetPath($c)."/".$zip->GetName($c),"/") != "packageinfo.xml")
	   continue;
      include_once($_BASE_PATH."var/lib/xmllib.php");
      $xml = new GXML();
      $xml->LoadFromString($zip->GetData($c));
      $el = $xml->GetElementsByTagName('package');
	  $pkg['compiled_version'] = $el[0]->getString('version');
      $c = $zip->Count();
     }
	}

    if($verbose)
	{
	 $out.= "<tr><td>".$pkg['name']."</td><td>";
	 $out.= $pkg['local_version'] ? $pkg['local_version'] : "&nbsp;";
	 $out.= "</td><td>";
	 $out.= $pkg['compiled_version'] ? $pkg['compiled_version'] : "&nbsp;";
	 $out.= "</td><td>";
	 $out.= $pkg['online_version'] ? $pkg['online_version'] : "&nbsp;";
	 $out.= "</td></tr>";
	}

    $outArr['packages'][] = $pkg;
  }

  return array('message'=>$out,'outarr'=>$outArr);
 }

 /* Get packages from database */
 if($verbose)
  $out.= "<table><tr><th>Name</th><th>Version</th><th>Installed</th><th>Section</th></tr>";

 $db = new AlpaDatabase();
 $qry = "";
 if($section)
  $qry.= " AND section='$section'";
 if($installed)
  $qry.= " AND installed_version!=''";

 if(!$qry)
  $qry = "1";

 if($limit)
 {
  /* Check count */
  $db->RunQuery("SELECT COUNT(*) FROM gnujiko_packages WHERE ".ltrim($qry," AND "));
  $db->Read();
  $outArr['count'] = $db->record[0];
 }

 $db->RunQuery("SELECT name FROM gnujiko_packages WHERE ".ltrim($qry," AND ")." ORDER BY ".$orderBy.($limit ? " LIMIT ".$limit : ""));
 while($db->Read())
 {
  $ret = GShell("gpkg info ".$db->record['name'].($getLocalVer ? " --get-local-ver" : ""),$sessid,$shellid);
  if(!$ret['error'])
  {
   $pkg = $ret['outarr'];
   if($verbose)
	$out.= "<tr><td>".$pkg['name']."</td><td>".$pkg['version']."</td><td>".$pkg['installed_version']."</td><td>".$pkg['section']."</td></tr>";
   if($limit)
	$outArr['packages'][] = $pkg;
   else
    $outArr[] = $pkg;
  }
  else
   $out.= "<tr><td style='color:#f31903'>".$db->record['name']."</td><td colspan='3'>Unable to retrieve package informations.</td></tr>";
 }
 $db->Close();
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_resolve($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $packages = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-package' : {$packages[] = $args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
   case '--force' : $force=true; break;
   case '-i' : $install=true; break;
   default : {
	 if(strpos($args[$c],",") !== false)
	  $packages = explode(",",$args[$c]);
	 else
	  $packages[] = $args[$c];
	} break;
  }

 if(!count($packages))
  return array("message"=>"You must specify at least one package. (with -package PACKAGE_NAME)","error"=>"INVALID_PACKAGE");

 if($verbose)
  $out.= "Package list: <font color='blue'>blue</font> = outdated, <font color='green'>green</font> = installed, <font color='red'>red</font> = unavailable, black = available, <font color='gray'>gray</font> = to be removed\n\n";

 global $_GPKG_LIST, $_GPKG_MAP;
 $_GPKG_LIST = array(); // list of package status by package
 $_GPKG_MAP = array(); // ordered map of packages (contains only package name)

 for($c=0; $c < count($packages); $c++)
  __gpkg_resolve($packages[$c],$sessid,$shellid);

 for($c=0; $c < count($_GPKG_MAP); $c++)
 {
  if(!$_GPKG_LIST[$_GPKG_MAP[$c]])
   continue;
  $outArr[$_GPKG_LIST[$_GPKG_MAP[$c]]][] = $_GPKG_MAP[$c];
  $outArr['execordering'][] = $_GPKG_MAP[$c];
  if($verbose)
  {
   switch($_GPKG_LIST[$_GPKG_MAP[$c]])
   {
    case 'OUTDATED' : $out.= "<font color='blue'>".$_GPKG_MAP[$c]."</font>\n"; break;
    case 'INSTALLED' : $out.= "<font color='green'>".$_GPKG_MAP[$c]."</font>\n"; break;
    case 'UNAVAILABLE' : $out.= "<font color='red'>".$_GPKG_MAP[$c]."</font>\n"; break;
    case 'TO_BE_REMOVE' : $out.= "<font color='gray'>".$_GPKG_MAP[$c]."</font>\n"; break;
    default : $out.= $_GPKG_MAP[$c]."\n"; break;
   }
  }
 }
 $_GPKG_LIST = null;
 $_GPKG_MAP = null;

 //-----------------------------------------//
 if($install)
 {
  $out = "Resolving dependences...";
  $resolveList = $outArr;
  if(count($resolveList['UNAVAILABLE']))
  {
   $out.= " failed!\n Error: Dependencies do not satisfy.\n";
   $out.= "These required packages are not available:\n";
   for($i=0; $i < count($resolveList['UNAVAILABLE']); $i++)
    $out.= " ".$resolveList['UNAVAILABLE'][$i]."\n";
   return array("message"=>$out, "error"=>"RESOLVE_FAILED");
  }
  if(count($resolveList['TO_BE_REMOVE']))
  {
   $out.= "\nThese packages should be removed:\n";
   for($i=0; $i < count($resolveList['TO_BE_REMOVE']); $i++)
    $out.= " ".$resolveList['TO_BE_REMOVE'][$i]."\n";
   gshPreOutput($shellid,$out);
   $out = "";
   $ret = GShell("gpkg remove ".($force ? "--force" : "").implode(" ",$resolveList['TO_BE_REMOVE']),$sessid,$shellid);
   if($ret['error'])
	return $ret;
   gshPreOutput($shellid,$ret['message']);
  }
  if(count($resolveList['OUTDATED']))
  {
   $out.= "\nThese packages should be updated:\n";
   for($i=0; $i < count($resolveList['OUTDATED']); $i++)
	$out.= " ".$resolveList['OUTDATED'][$i]."\n";
   gshPreOutput($shellid,$out);
   $out = "";
   $ret = GShell("gpkg upgrade --no-resolve ".($force ? "--force" : "").implode(" ",$resolveList['OUTDATED']),$sessid,$shellid);
   if($ret['error'])
	return $ret;
   gshPreOutput($shellid,$ret['message']);
  }
  if(count($resolveList['AVAILABLE']))
  {
   $out.= "\nThese packages should be installed:\n";
   for($i=0; $i < count($resolveList['AVAILABLE']); $i++)
	$out.= " ".$resolveList['AVAILABLE'][$i]."\n";
   gshPreOutput($shellid,$out);
   $out = "";
   $ret = GShell("gpkg install --no-resolve ".($force ? "--force" : "").implode(" ",$resolveList['AVAILABLE']),$sessid,$shellid);
   if($ret['error'])
	return $ret;
   gshPreOutput($shellid,$ret['message']);
  }
  $out.= "done!\n";
 }

 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function __gpkg_resolve($pack,$sessid,$shellid)
{
 global $_GPKG_LIST, $_GPKG_MAP, $_LANGUAGE;

 if($_GPKG_LIST[$pack])
  return;

 if(!$pack)
  return;

 $ret = GShell("gpkg info $pack",$sessid,$shellid);
 if($ret['error'])
 {
  $_GPKG_LIST[$pack] = "UNAVAILABLE";
  $_GPKG_MAP[] = $pack;
  return;
 }
 $pkgInfo = $ret['outarr'];

 if($pkgInfo['status'] == 'outdated')
  $_GPKG_LIST[$pack] = "OUTDATED";
 else if($pkgInfo['status'] == 'installed')
  $_GPKG_LIST[$pack] = "INSTALLED";
 else
  $_GPKG_LIST[$pack] = "AVAILABLE";

 $conflicts = explode(",",$pkgInfo['conflicts']);
 $predepends = explode(",",$pkgInfo['pre_depends']);
 $depends = explode(",",$pkgInfo['depends']);

 for($c=0; $c < count($conflicts); $c++)
 {
  if($conflicts[$c])
  {
   $_GPKG_LIST[$conflicts[$c]] = "TO_BE_REMOVE";
   $_GPKG_MAP[] = $conflicts[$c];
  }
 }
 for($c=0; $c < count($predepends); $c++)
  __gpkg_resolve($predepends[$c],$sessid,$shellid);
 for($c=0; $c < count($depends); $c++)
  __gpkg_resolve($depends[$c],$sessid,$shellid);

 $_GPKG_MAP[] = $pack;

 /* Check if exists language-pack for this package */
 $langpack = $pack."-language-pack-".substr($_LANGUAGE,0,strpos($_LANGUAGE,"-"));
 $ret = GShell("gpkg info $langpack",$sessid,$shellid);
 if(!$ret['error'])
  __gpkg_resolve($langpack,$sessid,$shellid);
 
 return;
}
//-------------------------------------------------------------------------------------------------------------------//
//--------------- A C T I O N S -------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_publish($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $packages = array();
 $servers = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-package' : {$packages[] = $args[$c+1]; $c++;} break;
   case '-server' : {$servers[] = $args[$c+1]; $c++;} break;
   case '-section' : {$section=$args[$c+1]; $c++;} break;
   case '-build' : $build=true; break;
   default : $packages[] = $args[$c]; break;
  }

 if(!$section)
  $section = "main";

 $serverList = array();
 $packageList = array();

 if(!count($packages))
  return array('message'=>"You must specify at least one package. (with: -package PACKAGE_NAME)","error"=>"INVALID_PACKAGE");
 if(!count($servers))
  return array('message'=>"You must specify the server to publish this package(s)","error"=>"INVALID_SERVER");

 /* detect servers */
 for($c=0; $c < count($servers); $c++)
 {
  $ret = GShell("gpkg server-info '".$servers[$c]."'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $serverList[] = $ret['outarr'];
 }

 /* detect local packages */
 for($c=0; $c < count($packages); $c++)
 {
  $ret = GShell("gpkg info ".$packages[$c]." -local",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $packageList[] = $ret['outarr'];
  if($build)
  {
   gshPreOutput($shellid,"Building package...","BUILD_PACKAGE",$packages[$c]);
   $ret = GShell("gpkg build -package '".$packages[$c]."'",$sessid,$shellid);
   if($ret['error'])
	return $ret;
  }
 }

 /* start uploading */
 for($c=0; $c < count($serverList); $c++)
 {
  $serverInfo = $serverList[$c];
  gshPreOutput($shellid,"Connecting to server ".$serverInfo['name']."...", "CONNECTING_TO_SERVER", $serverInfo['name']);
  $conn = ftp_connect($serverInfo['host']);
  if(!$conn)
   return array('message'=>"Unable to connect with server ".$serverInfo['name'],'error'=>"INVALID_CONNECTION");
  if(!ftp_login($conn,$serverInfo['login'],$serverInfo['password']))
   return array('message'=>"Login... failed!",'error'=>"LOGIN_FAILED");
  gshPreOutput($shellid,"Login...OK", "LOGIN_OK", $serverInfo['name']);

  for($p=0; $p < count($packageList); $p++)
  {
   $packageInfo = $packageList[$p];
   $servpath = rtrim($serverInfo['basepath'],"/")."/dists/10.1/".$section."/data/";
   $servpathinfo = rtrim($serverInfo['basepath'],"/")."/dists/10.1/".$section."/info/";
   gshPreOutput($shellid,"Uploading package ".$packageInfo['name']." into section $section ...","UPLOADING_PACKAGE", $packageInfo['name']);
   /* upload zip package */
   if(!@ftp_put($conn,rtrim($servpath,"/")."/".$packageInfo['name'].".zip",$_BASE_PATH."var/packages/".$packageInfo['name'].".zip",FTP_BINARY))
    return array('message'=>"Unable to upload package ".$packageInfo['name']." to server ".$serverInfo['name']." into section $section",'error'=>"UPLOAD_FILE_FAILED");
   if(!@ftp_put($conn,rtrim($servpathinfo,"/")."/".$packageInfo['name'].".xml",$_BASE_PATH."var/packages/".$packageInfo['name']."/packageinfo.xml",FTP_BINARY))
    return array('message'=>"Unable to upload package information ".$packageInfo['name']." to server ".$serverInfo['name'],'error'=>"UPLOAD_FILEINFO__FAILED");
   @ftp_chmod($conn,0644,rtrim($servpath,"/")."/".$packageInfo['name'].".zip");
   @ftp_chmod($conn,0644,rtrim($servpathinfo,"/")."/".$packageInfo['name'].".xml");
   gshPreOutput($shellid,"done!", "PACKAGE_UPLOADED", $packageInfo['name']);
  }
  ftp_close($conn);
 }

 $out.= "Success!";

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
//--------------- S E R V E R ---------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_addServer($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-host' : {$host=$args[$c+1]; $c++;} break;
   case '--base-path' : {$basePath=$args[$c+1]; $c++;} break;
   case '-login' : {$login=$args[$c+1]; $c++;} break;
   case '-passw' : case 'password' : {$passw=$args[$c+1]; $c++;} break;
  }
 if(!$name || !$host)
  return array('message'=>"You must specify at least name and host. (with: -name serverName -host serverHost)","error"=>"INVALID_PARAMS");

 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO gnujiko_gpkg_servers(name,host,basepath,login,password) VALUES('$name','$host','$basePath','$login','$passw')");
 $id = mysql_insert_id();
 $out.= "done!\n";
 $outArr = array('id'=>$id,'name'=>$name,'host'=>$host,'basepath'=>$basePath,'login'=>$login,'password'=>$passw);
 $db->Close();
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_editServer($args, $sessid, $shellid)
{
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-rename' : {$rename=$args[$c+1]; $c++;} break;
   case '-host' : {$host=$args[$c+1]; $c++;} break;
   case '--base-path' : {$basePath=$args[$c+1]; $c++;} break;
   case '-login' : {$login=$args[$c+1]; $c++;} break;
   case '-passw' : case 'password' : {$passw=$args[$c+1]; $c++;} break;
  }
 
 $db = new AlpaDatabase();
 if($name)
  $db->RunQuery("SELECT id FROM gnujiko_gpkg_servers WHERE name='$name'");
 else if($id)
  $db->RunQuery("SELECT name FROM gnujiko_gpkg_servers WHERE id='$id'");
 else
  return array('message'=>"You must specify server. (with: -id serverId OR -name serverName)", 'error'=>"INVALID_SERVER_ID");
 if(!$db->Read())
  return array('message'=>"Server ".($name ? $name : "#$id")." does not exists.","SERVER_DOES_NOT_EXISTS");

 $q = "";
 if($rename)
  $q.= ",name='$rename'";
 if($host)
  $q.= ",host='$host'";
 if($basePath)
  $q.= ",basepath='$basePath'";
 if($login)
  $q.= ",login='$login'";
 if($passw)
  $q.= ",password='$passw'";
 $db->RunQuery("UPDATE gnujiko_gpkg_servers SET ".ltrim($q,",")." WHERE id='$id'");
 $db->Close();
 $out.= "done!\n";
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_deleteServer($args, $sessid, $shellid)
{
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
  }

 if(!$id)
  return array('message'=>"You must specify server. (with -id serverId)","error"=>"INVALID_SERVER_ID");
 
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT name FROM gnujiko_gpkg_servers WHERE id='$id'");
 if(!$db->Read())
  return array('message'=>"Server #$id does not exists", "SERVER_DOES_NOT_EXISTS");
 $db->RunQuery("DELETE FROM gnujiko_gpkg_servers WHERE id='$id'");
 $db->Close();
 $out.= "Server #$id hase been removed!\n";
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_serverInfo($args, $sessid, $shellid)
{
 $out = "";
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-verbose' : $verbose=true; break;
   default : $name = $name ? $name : $args[$c]; break;
  }
 if(!$id && !$name)
  return array('message'=>"You must specify server. (with -id SERVER_ID || -name SERVER_NAME)", "error"=>"INVALID_SERVER_ID");

 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("SELECT * FROM gnujiko_gpkg_servers WHERE id='$id'");
 else if($name)
  $db->RunQuery("SELECT * FROM gnujiko_gpkg_servers WHERE name='$name'");
 if(!$db->Read())
  return array('message'=>"Server ".($id ? "#$id" : $name)." does not exists", "error"=>"SERVER_DOES_NOT_EXISTS");

 $outArr = array('id'=>$db->record['id'],'name'=>$db->record['name'],'host'=>$db->record['host'],
	'basepath'=>$db->record['basepath'],'login'=>$db->record['login'],'password'=>$db->record['password']);
 if($verbose)
 {
  $out.= "Id: ".$db->record['id']."\n";
  $out.= "Name: ".$db->record['name']."\n";
  $out.= "Host: ".$db->record['host']."\n";
  $out.= "Base path: ".$db->record['basepath']."\n";
  $out.= "Login: ".$db->record['login']."\n";
  $out.= "Password: ".$db->record['password']."\n";
 }
 $db->Close();
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_serverList($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_gpkg_servers WHERE 1 ORDER BY id ASC");
 while($db->Read())
 {
  $out.= "#".$db->record['id']." ".$db->record['name']."\n";
  $outArr[] = array('id'=>$db->record['id'],'name'=>$db->record['name'],'host'=>$db->record['host'],'basepath'=>$db->record['basepath']);
 }
 $db->Close();
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_get_package($repository, $package, $shellid=0,$msgType="",$msgRef="", $mode="")
{
 global $_GNUJIKO_ACCOUNT, $_GNUJIKO_TOKEN;

 $url = $repository."/getfile.php?package=".$package."&account=".$_GNUJIKO_ACCOUNT."&token=".$_GNUJIKO_TOKEN;
 $url_stuff = parse_url($url);
 $port = isset($url_stuff['port']) ? $url_stuff['port'] : 80;

 $fp = fsockopen($url_stuff['host'], $port);

 $query  = 'GET ' . $url_stuff['path']."?".$url_stuff['query'] . " HTTP/1.0\n";
 $query .= 'Host: ' . $url_stuff['host'];
 $query .= "\n\n";

 fwrite($fp, $query);

 $fileSize = 0;
 $needPreOutput = false;
 $nextStep = 0;

 while ($tmp = fread($fp, 1024))
 {
  $buffer .= $tmp;
  $buffLen = strlen($buffer);
  if(!$fileSize)
  { 
   // check for errors //
   if(ereg("ERROR-100:",$buffer))
	return array("message"=>"Error: Package ".$package." does not exists!","error"=>"PACKAGE_DOES_NOT_EXISTS");
   else if(ereg("ERROR-201",$buffer))
	return array("message"=>"Error: You have an invalid account!","error"=>"INVALID_ACCOUNT");
   else if(ereg("ERROR-202",$buffer))
	return array("message"=>"Error: Invalid token!","error"=>"INVALID_TOKEN");
   else if(ereg("ERROR-203",$buffer))
	return array("message"=>"Error: There are some restrictions in your account!","error"=>"ACCOUNT_RESTRICTIONS");

   // try to detect file size //
   if(preg_match('/Content-Length: (\d+)/', $buffer, $matches))
   {
	$fileSize = (int)$matches[1];
	if($fileSize > 71680)
	 $needPreOutput = true;
	$perc = floor((100/$fileSize)*$buffLen);
	gshPreOutput($shellid, "Downloading: ".$perc."%", $msgType, $msgRef, $mode, array('size'=>$fileSize,'current'=>$buffLen,'percentage'=>$perc));
	$nextStep+= 71680;
   }
  }
  if($needPreOutput && (strlen($buffer) >= $nextStep))
  {
   $perc = floor((100/$fileSize)*$buffLen);
   gshPreOutput($shellid, "Downloading: ".$perc."%", $msgType, $msgRef, $mode, array('size'=>$fileSize,'current'=>$buffLen,'percentage'=>$perc));
   $nextStep+= 71680;
  }
 }

 preg_match('/Content-Length: ([0-9]+)/', $buffer, $parts);
 $buffer = substr($buffer, - $parts[1]);

 return array("message"=>$buffer);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_fileInfo($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-into' : {$intoZip=ltrim($args[$c+1],"/"); $c++;} break;
   case '--get-tags' : $getTags=true; break;
   case '--verbose' : $verbose=true; break;
   default : $filename = $filename ? $filename : ltrim($args[$c],"/"); break;
  }

 if(!$filename)
  return array('message'=>"You must specify filename.", "error"=>"INVALID_FILE_NAME");

 if($intoZip && !file_exists($_BASE_PATH.$intoZip))
  return array('message'=>"File $intoZip does not exists.","error"=>"ZIP_FILE_DOES_NOT_EXISTS");

 if($intoZip)
 {
  /* Check into zip file */
  if(!file_exists($_BASE_PATH.'var/lib/zip/unzip.lib.php'))
   return array("message"=>"Library zip does not exists","error"=>"UNZIP_LIBRARY_DOES_NOT_EXISTS");
  include_once($_BASE_PATH.'var/lib/zip/unzip.lib.php');

  $zip = new SimpleUnzip($_BASE_PATH.$intoZip);
  $find = false;
  for ($c=0; $c < $zip->Count(); $c++)
  {
   $file = ltrim($zip->GetPath($c)."/".$zip->GetName($c),"/");
   if($file != $filename)
    continue;
   $filesize = $zip->GetSize($c);
   $filemtime = $zip->GetTime($c);
   $md5 = md5($zip->GetData($c));
   $ext = strtolower(substr($filename, strrpos($filename, '.')+1));
   $c = $zip->Count();
   $find = true;
  }
  if(!$find)
   return array("message"=>"File $filename does not exists into archive $intoZip.","error"=>"FILE_DOES_NOT_EXISTS");
  
 }
 else 
 {
  /* Check into local file */
  if(!file_exists($_BASE_PATH.$filename))
   return array('message'=>"File $filename does not exists.","error"=>"FILE_DOES_NOT_EXISTS");

  $h = fopen($_BASE_PATH.$filename,"rb");
  if(!$h)
   return array('message'=>"Unable to read $filename. Check permissions.","error"=>"PERMISSION_DENIED");

  /* Get basic info */
  $filesize = filesize($_BASE_PATH.$filename);
  $filemtime = filemtime($_BASE_PATH.$filename);
  $md5 = md5_file($_BASE_PATH.$filename);
  $ext = strtolower(substr($filename, strrpos($filename, '.')+1));
 }

 $outArr['mtime'] = $filemtime;
 $outArr['size'] = $filesize;
 $outArr['ext'] = $ext;
 // detect human size //
 $sar = array('bytes','kB','MB','GB','TB');
 $sx = 0;
 $siz = $filesize;
 while($siz > 1024)
 {
  $siz = $siz/1024;
  $sx++;
  if($sx == 4)
   break;
 }
 $outArr['humansize'] = str_replace('.00','',sprintf("%.2f",$siz))." ".$sar[$sx];
 
 $outArr['md5'] = $md5;

 if($verbose)
 {
  $out.= "File informations for: ".$filename."\n";
  $out.= "Last mod.: ".date('d/m/Y H:i',$outArr['mtime'])."\n";
  $out.= "Size: ".$outArr['humansize']."\n";
  $out.= "MD5: ".$outArr['md5']."\n";
 }

 /* Get tags */
 if($getTags && !$intoZip)
 {
  /* #TODO: In futuro fare in modo che legga i tags anche da un file all'interno di uno zip */
  if($verbose)
   $out.= "File tags:\n";
  switch($ext)
  {
   case 'php' : case 'js' : case 'inc' : {
	 $headtags = fread($h, 1000);
	 $keys = array('date','package','description','version','changelog','todo');
	 $tags = array("#DATE:", "#PACKAGE:", "#DESCRIPTION:", "#VERSION:", "#CHANGELOG:", "#TODO:");

	 for($c=0; $c < count($tags); $c++)
	 {
	  $pos = strpos($headtags,$tags[$c]);
	  if($pos !== false)
	  {
	   $from = $pos+strlen($tags[$c]);
	   $to = strpos($headtags,"\n",$from);
	   $outArr['tags'][$keys[$c]] = ltrim(rtrim(substr($headtags,$from,($to-$from))));
	   if($verbose)
	    $out.= " ".$tags[$c].$outArr['tags'][$keys[$c]]."\n";
	  }
	 }
	} break;
   default : $out.= "Files with ".$ext." extensions has not tags.\n";
  }
 }

 if($h)
  fclose($h);

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_fileChanged($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();
 $zipFiles = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-package' : {$package=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
   default : $package = $package ? $package : $args[$c]; break;
  }

 if(!$package)
  return array("message"=>"You must specify package","error"=>"INVALID_PACKAGE");

 $ret = GShell("gpkg info `".$package."`");
 if($ret['error'])
  return $ret;

 if(file_exists($_BASE_PATH."var/packages/".$package.".zip"))
 {
  $ret = GShell("unzip var/packages/".$package.".zip -list");
  if($ret['error'])
   return $ret;
  $zipFiles = $ret['outarr']['files'];
 }

 $outArr['new'] = array();
 $outArr['updated'] = array();
 $outArr['removed'] = array();

 /* Detect new,updated and removed files */
 $ret = GShell("ls var/packages/".$package."/__files/ -list");
 if($ret['error'])
  return $ret;

 $pkgFileList = $ret['outarr'];

 for($c=0; $c < count($pkgFileList); $c++)
 {
  $file = str_replace("var/packages/".$package."/__files/", "", $pkgFileList[$c]);

  if(!file_exists($_BASE_PATH.$file))
  {
   $outArr['removed'][] = $file;
   continue;
  }

  if(!count($zipFiles))
  {
   $outArr['new'][] = $file;
   continue;
  }

  /* Get local file info */
  $ret = GShell("gpkg file-info ".$file);
  if($ret['error'])
   return $ret;
  $localFileInfo = $ret['outarr'];
  
  /* Get package file info */
  $ret = GShell("gpkg file-info `".$pkgFileList[$c]."`");
  if($ret['error'])
   return $ret;
  $packageFileInfo = $ret['outarr'];

  if($localFileInfo['md5'] != $packageFileInfo['md5'])
   $outArr['updated'][] = $file;
  else if(!in_array("__files/".$file,$zipFiles))
   $outArr['new'][] = $file;
 }

 if($verbose)
 {
  if(!count($outArr['new']) && !count($outArr['removed']) && !count($outArr['updated']))
   $out.= "no file has been updated, removed or inserted.";
  else
  {
   if(count($outArr['new']))
   {
	$out.= "Latest files added:\n";
	for($c=0; $c < count($outArr['new']); $c++)
	 $out.= $outArr['new'][$c]."\n";
	$out.= "\n";
   }
   if(count($outArr['removed']))
   {
	$out.= "Latest files to be removed from the package folder: \n";
	for($c=0; $c < count($outArr['removed']); $c++)
	 $out.= $outArr['removed'][$c]."\n";
	$out.= "\n";
   }
   if(count($outArr['updated']))
   {
	$out.= "Latest files updated:\n";
	for($c=0; $c < count($outArr['updated']); $c++)
	 $out.= $outArr['updated'][$c]."\n";
	$out.= "\n";
   }
  }
 }

 $out.= count($outArr['new'])." files added, ".count($outArr['removed'])." files removed, ".count($outArr['updated'])." files to be update.";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_autoCheckDepends($args, $sessid, $shellid)
{
 global $_BASE_PATH;

 $out = "";
 $outArr = array();
 $files = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-package' : {$package=$args[$c+1]; $c++;} break;
   case '-file' : {$files[]=ltrim($args[$c+1],"/"); $c++;} break;
   case '--verbose' : $verbose=true; break;
  }

 if($package)
 {
  $ret = GShell("gpkg info `".$package."`",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $ret = GShell("ls /var/packages/$package/__files/ -list -filter php,inc,js,css",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $files = $ret['outarr'];
 }

 $includes = array();
 $depends = array();

 for($c=0; $c < count($files); $c++)
 {
  $filename = $files[$c];
  $pos = strrpos($filename,".");
  if($pos === false)
   continue;
  if(!file_exists($_BASE_PATH.$filename))
   return array('message'=>"File $filename does not exists.", "error"=>"FILE_DOES_NOT_EXISTS");
  $fileext = substr($filename,$pos+1);
  switch($fileext)
  {
   case 'php' : case 'inc' : {
	 $h = fopen($_BASE_PATH.$filename,"rb");
	 if(!$h)
	  return array('message'=>'Unable to read file $filename.','error'=>'PERMISSION_DENIED');
	 $siz = filesize($_BASE_PATH.$filename);
	 if(!$siz)
	  continue;
	 $contents = fread($h,$siz);
	 fclose($h);

	 /* Match all "include(..)" */
	 $ret = array();
	 preg_match_all('#include\([^)]*\)#', $contents, $ret);
	 for($i=0; $i < count($ret[0]); $i++)
	 {
	  $remove = array("include(", "$"."_BASE_PATH.", "../", "./");
	  $f = rtrim(str_replace($remove,"",$ret[0][$i]),")");
	  $f = substr($f,1,strlen($f)-2);
	  if(!in_array($f,$includes))
	   $includes[] = $f;
	 }

	 /* Match all "include_once(...)" */
	 $ret = array();
	 preg_match_all('#include_once\([^)]*\)#', $contents, $ret);
	 for($i=0; $i < count($ret[0]); $i++)
	 {
	  $remove = array("include_once(", "$"."_BASE_PATH.", "../", "./");
	  $f = rtrim(str_replace($remove,"",$ret[0][$i]),")");
	  $f = substr($f,1,strlen($f)-2);
	  if(!in_array($f,$includes))
	   $includes[] = $f;
	 }

	 /* Match all "require(...)" */
	 $ret = array();
	 preg_match_all('#require\([^)]*\)#', $contents, $ret);
	 for($i=0; $i < count($ret[0]); $i++)
	 {
	  $remove = array("require(", "$"."_BASE_PATH.", "../", "./");
	  $f = rtrim(str_replace($remove,"",$ret[0][$i]),")");
	  $f = substr($f,1,strlen($f)-2);
	  if(!in_array($f,$includes))
	   $includes[] = $f;
	 }

	 /* Match all "require_once(...)" */
	 $ret = array();
	 preg_match_all('#require_once\([^)]*\)#', $contents, $ret);
	 for($i=0; $i < count($ret[0]); $i++)
	 {
	  $remove = array("require_once(", "$"."_BASE_PATH.", "../", "./");
	  $f = rtrim(str_replace($remove,"",$ret[0][$i]),")");
	  $f = substr($f,1,strlen($f)-2);
	  if(!in_array($f,$includes))
	   $includes[] = $f;
	 }

	 /*if($verbose)
	 {
	  $out.= "List of files included into $filename:\n";
	  for($i=0; $i < count($includes); $i++)
	   $out.= " - ".$includes[$i]."\n";
	 }*/

	} break;

   case 'js' : {
	 /* da continuare */
	} break;

   case 'css' : {
	 /* da continuare */
	} break;
  }
 
 }

 /* Check depends reading from header of detected files. */
 for($c=0; $c < count($includes); $c++)
 {
  $file = $includes[$c];
  $ret = GShell("gpkg file-info `".$file."` --get-tags",$sessid,$shellid);
  if(!$ret['error'])
  {
   $pkg = $ret['outarr']['tags']['package'];
   if($pkg && !in_array($pkg,$depends))
    $depends[] = $pkg;
  }
 }


 if(count($depends))
 {
  if($verbose)
   $out.= "List of dependences matched:\n";

  for($c=0; $c < count($depends); $c++)
  {
   $ret = GShell("gpkg info `".$depends[$c]."`",$sessid,$shellid);
   if($ret['error'])
	$outArr[] = array('name'=>$depends[$c],'status'=>'unavailable');
   else
	$outArr[] = $ret['outarr'];

   if($verbose)
    $out.= " - ".$depends[$c]."\n";
  }

  $out.= count($depends)." dependences matched on ".count($files)." files scanned!";
 }
 else
  $out.= "No dependences matched on ".count($files)." files scanned!";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_reverseCheckDepends($args, $sessid, $shellid)
{
 global $_BASE_PATH;

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-package' : {$package=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
   default: {if(!$package) $package=$args[$c];} break;
  }

 if(!$package)
  return array('message'=>"You must specify the package. With -package PACKAGE_NAME.","error"=>"INVALID_PACKAGE");

 $ret = GShell("gpkg info `".$package."`",$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $preDependsQry = "pre_depends='".$package."' OR pre_depends LIKE '".$package.",%' OR pre_depends LIKE '%,".$package.",%' OR pre_depends LIKE '%,".$package."'";

 $dependsQry = "depends='".$package."' OR depends LIKE '".$package.",%' OR depends LIKE '%,".$package.",%' OR depends LIKE '%,".$package."'";


 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM gnujiko_packages WHERE (".$preDependsQry.") OR (".$dependsQry.")");
 while($db->Read())
 {
  if($verbose)
   $out.= $db->record['name']."\n";
  $outArr[] = array('name'=>$db->record['name'],'version'=>$db->record['version'],'installed_version'=>$db->record['installed_version'],
	'section'=>$db->record['section'],'maintainer'=>$db->record['maintainer'],'essential'=>$db->record['essential'],
	'pre-depends'=>$db->record['pre_depends'],'depends'=>$db->record['depends'],'replaces'=>$db->record['replaces'],
	'conflicts'=>$db->record['conflicts'],'description'=>$db->record['description'],'repository'=>$db->record['repository']);
 }
 $db->Close();
 $out.= count($outArr)." packages found.";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_fileFind($args, $sessid, $shellid)
{
 global $_BASE_PATH;

 $out = "";
 $outArr = array();
 $files = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-package' : {$package=$args[$c+1]; $c++;} break;
   case '-all-packages' : case '--all-packages' : $allPackages=true; break;
   case '-file' : {$files[]=ltrim($args[$c+1],"/"); $c++;} break;


   case '--verbose' : $verbose=true; break;
  }


 if($allPackages)
 {
  $out.= "Search into all packages...";
  $ret = GShell("ls /var/packages/ -d",$sessid,$shellid);
  $list = $ret['outarr']['dirs'];
  for($c=0; $c < count($list); $c++)
  {
   $pkgName = $list[$c]['name'];
   $ret = GShell("ls /var/packages/".$pkgName."/__files/ -list -filter php,inc,js,css",$sessid,$shellid);
   if($ret['error'])
    return $ret;
   for($i=0; $i < count($ret['outarr']); $i++)
	$files[] = $ret['outarr'][$i];
  }
  if(count($files))
   $out.= "done!\nThere are ".count($files)." packages to be scan, please wait!\n";
  else
   $out.= "failed!\nWarning:No packages found into folder /var/packages/\n";
 }
 else if($package)
 {
  $ret = GShell("gpkg info `".$package."`",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $ret = GShell("ls /var/packages/".$package."/__files/ -list -filter php,inc,js,css",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $files = $ret['outarr'];
 }

 for($c=0; $c < count($files); $c++)
 {
  $filename = $files[$c];
  $pos = strrpos($filename,".");
  if($pos === false)
   continue;
  if(!file_exists($_BASE_PATH.$filename))
   return array('message'=>"File $filename does not exists.", "error"=>"FILE_DOES_NOT_EXISTS");
  $fileext = substr($filename,$pos+1);
  switch($fileext)
  {
   case 'php' : case 'inc' : {
	 $ret = gpkg_fileFind_PHP($filename, $args, $sessid, $shellid);
	 if($ret['error'])
	  return $ret;
	 if($ret['message']) $out.= trim($ret['message'])."\n";

	 /* TODO: continuare da qui... */
	} break;

   case 'js' : {
	 /* TODO: da fare */
	} break;

   case 'css' : {
	 /* TODO: da fare */
	} break;
  }
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gpkg_fileFind_PHP($filename, $args, $sessid, $shellid)
{
 global $_BASE_PATH;
 
 $search = array();
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-search' : {$search[]=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
  }

 $h = fopen($_BASE_PATH.$filename,"rb");
 if(!$h)
  return array('message'=>'Unable to read file $filename.','error'=>'PERMISSION_DENIED');
 $siz = filesize($_BASE_PATH.$filename);
 if(!$siz)
 {
  fclose($h);
  return array('message'=>"File ".$filename." is empty.");
 }

 $contents = fread($h,$siz);
 fclose($h);

 if(count($search))
 {
  for($c=0; $c < count($search); $c++)
  {
   if(strpos($contents, $search[$c]) !== false)
   {
	$outArr['search']['match_results'][$c] = true;
	if($verbose)
	 $out.= "Found '".$search[$c]."' into file ".$filename."\n";
   }
   else
   {
	$outArr['search']['match_results'][$c] = false;
   }
  }
 }

 return array('message'=>$out, 'outarr'=>$outArr); 
}
//-------------------------------------------------------------------------------------------------------------------//

