<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2010 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 06-05-2010
 #PACKAGE: zip-lib
 #DESCRIPTION: Compress file .zip
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/


function shell_zip($args, $sessid, $shellid=0)
{
 $out = "";
 $outArr = array();
 global $_BASE_PATH, $_USERS_HOMES;

 $sessInfo = sessionInfo($sessid);

 if($sessInfo['uname'] == "root")
  $_USER_PATH = $_BASE_PATH;
 else if($sessInfo['uid'])
 {
  /* Per sicurezza un utente normale non può zippare file al di fuori della sua cartella home. Altrimenti chiunque potrebbe comodamente zipparsi ad esempio il file di configurazione e leggerselo in tutta tranquillità */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $_USER_PATH = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
  $_USER_PATH = $_BASE_PATH."tmp/";


 if(count($args) == 0)
  return array("message"=>"Usage: zip file-or-dir dest_file.zip\n","error"=>"INVALID_ARGUMENTS");

 $src=array();

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-i' : {$src[]=$args[$c+1]; $c++;} break;
   case '-o' : {$dst=$args[$c+1]; $c++;} break;
   default: {if(!count($src))$src[]=$args[$c]; else $dst=$args[$c];} break;
  }

 if(!count($src))
  return array("message"=>"Invalid source file\n","error"=>"INVALID_SOURCE_FILE");

 if(!$dst)
  return array("message"=>"Invalid destination file\n","error"=>"INVALID_DEST_FILE");

 if(!file_exists($_BASE_PATH.'var/lib/zip/zip.lib.php'))
  return array("message"=>"Library zip does not exists","error"=>"LIBRARY_DOES_NOT_EXISTS");
 include_once($_BASE_PATH.'var/lib/zip/zip.lib.php');

 $dst = rtrim($dst, "/");

 $z = new zipfile;

 for($c=0; $c < count($src); $c++)
 {
  $src[$c] = str_replace(array("../", "./"),"",$src[$c]);
  if(!file_exists($_USER_PATH.$src[$c]))
   return array("message"=>"File ".$src[$c]." does not exists\n", "error"=>"FILE_DOES_NOT_EXISTS");
  if(is_dir($_USER_PATH.$src[$c]))
  {
   if(!zipAddRecursive($src[$c],$src[$c],$z,$_USER_PATH, $sessid, $shellid))
   {
    gshPreOutput($shellid, "Error while insert ".$src[$c]."\n");
    sleep(2);
   }
  }
  else if(file_exists($_USER_PATH.$src[$c]))
   $z->addFile(implode("", file($_USER_PATH.$src[$c])),$src[$c]);
  else
   return array("message"=>"File ".$src[$c]." does not exists!\n","error"=>"INVALID_FILE_NAME");
 }

 $h = fwopen($dst,"w",$_USER_PATH, $sessid, $shellid);
 fwrite($h,$z->file());
 fclose($h);
 $out.= "done!\n";
 $outArr['filename'] = $dst;
 $outArr['fullpath'] = $_USER_PATH.$dst;

 return array("message"=>$out, "outarr"=>$outArr);
}

function zipAddRecursive($root, $dir, $z, $_USER_PATH="",$sessid=0,$shellid=0)
{
 if(substr($dir,-1) != "/") 
  $dir.= "/";
 if(!is_dir($_USER_PATH.$dir))
 {
  gshPreOutput($shellid, "Zip.Error: $dir is not a valid directory.\n");
  sleep(2);
  return false;
 }
 if(($dh = opendir($_USER_PATH.$dir)) !== false) 
 {
  while (($entry = readdir($dh)) !== false) 
  {
   if ($entry != "." && $entry != "..") 
   {
    if(is_file($_USER_PATH.$dir.$entry) || is_link($_USER_PATH.$dir.$entry)) 
     $z->addFile(implode("",file($_USER_PATH.$dir.$entry)),str_replace($root, "", $dir.$entry));
    else if (is_dir($_USER_PATH.$dir.$entry)) 
     zipAddRecursive($root,$dir.$entry,$z,$_USER_PATH,$sessid,$shellid);
   }
  }
  closedir($dh);
  return true;
 }
 return false;
}

