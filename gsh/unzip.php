<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-04-2013
 #PACKAGE: zip-lib
 #DESCRIPTION: Decompress file .zip
 #VERSION: 2.4beta
 #CHANGELOG: 15-04-2013 : Bug fix with FTP.
			 11-04-2013 : Sistemato i permessi ai files.
			 19-11-2012 : Buf fix.
			 10-02-2012 : Bug fix with FTP.
			 29-01-2012 : Aggiunto parametro -list.
 #TODO:
 
*/
function shell_unzip($args, $sessid, $shellid=0)
{
 $out = "";
 $outArr = array();
 global $_BASE_PATH, $_USERS_HOMES, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_DEFAULT_FILE_PERMS, $_FTP_CONN;

 $sessInfo = sessionInfo($sessid);

 if($sessInfo['uname'] == "root")
  $_USER_PATH = $_BASE_PATH;
 else if($sessInfo['uid'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $_USER_PATH = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
  $_USER_PATH = $_BASE_PATH."tmp/";

 if(count($args) == 0)
  return array("message"=>"Usage: unzip file.zip dest\n","error"=>"INVALID_ARGUMENTS");

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-i' : {$src=$args[$c+1]; $c++;} break;
   case '-o' : {$dst=$args[$c+1]; $c++;} break;
   case '-file' : {$onlyFile=$args[$c+1]; $c++;} break;
   case '-list' : $onlyList=true; break;
   default : !isset($src) ? $src=$args[$c] : $dst=$args[$c]; break;
  }

 if(!file_exists($_BASE_PATH.'var/lib/zip/unzip.lib.php'))
  return array("message"=>"Library zip does not exists","error"=>"LIBRARY_DOES_NOT_EXISTS");
 include_once($_BASE_PATH.'var/lib/zip/unzip.lib.php');

 $src = ltrim($src,"/");

 if(!file_exists($_USER_PATH.$src))
  return array("message"=>"File $src does not exists","error"=>"FILE_DOES_NOT_EXISTS");

 $dst = ltrim($dst, "/");
 $dst = rtrim($dst, "/");

 $zip = new SimpleUnzip($_USER_PATH.$src);
 for ($c=0; $c < $zip->Count(); $c++)
 {
  if($onlyFile && ($onlyFile != ltrim($zip->GetPath($c)."/".$zip->GetName($c),"/")))
   continue;
  if($onlyList)
  {
   $out.= ltrim($zip->GetPath($c)."/".$zip->GetName($c),"/")."\n";
   $outArr['files'][] = ltrim($zip->GetPath($c)."/".$zip->GetName($c),"/");
   continue;
  }

  if($_FTP_USERNAME)
  {
   $gftpret = gftpwrite($_USER_PATH.$dst."/".$zip->GetPath($c)."/".$zip->GetName($c), $zip->GetData($c), $_DEFAULT_FILE_PERMS, true, true);
   if($gftpret['error'])
    return array("message"=>"Unable to extract file into ".$_USER_PATH.$dst."/".$zip->GetPath($c)."/ using FTP\n".$gftpret['message'],"error"=>$gftpret['error']);
  }
  else
  {
   $f = @fwopen($dst."/".$zip->GetPath($c)."/".$zip->GetName($c), "wb",$_USER_PATH,$sessid,$shellid);
   if($f===false)
    return array("message"=>"Unable to extract file into ".$_USER_PATH.$dst."/".$zip->GetPath($c)."/","error"=>"PERMISSION_DENIED");

   if(@fwrite($f,$zip->GetData($c))===false)
    return array("message"=>"Unable to write into ".$dst."/".$zip->GetPath($c)."/".$zip->GetName($c),"error"=>"PERMISSION_DENIED");
   @chmod($_USER_PATH.$dst."/".$zip->GetPath($c)."/".$zip->GetName($c), $_DEFAULT_FILE_PERMS);
   @fclose($f);
  }
  $outArr['files'][] = ltrim($zip->GetPath($c)."/".$zip->GetName($c),"/");
 }

 if($_FTP_CONN)
  @ftp_close($_FTP_CONN);

 $out.= "Done!";
 return array("message"=>$out,"outarr"=>$outArr);
}

