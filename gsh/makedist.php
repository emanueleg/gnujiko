<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-03-2013
 #PACKAGE: makedist
 #DESCRIPTION: Official Gnujiko Distro Maker.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

function shell_makedist($args, $sessid, $shellid=0)
{
 global $_BASE_PATH, $_SOFTWARE_VERSION, $_DISTRO_NAME;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $outArr = array();

 $distName = $_DISTRO_NAME;
 $distVer = $_SOFTWARE_VERSION;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : case '-title' : {$distName=$args[$c+1]; $c++;} break;
   case '-ver' : case '-version' : {$distVer=$args[$c+1]; $c++;} break;

   /* OPTIONS */
   case '--no-zip' : $noZip=true; break;
   case '--no-remove-temp' : $noRemoveTemp=true; break;
  }


 /* Creo la cartella dove andrò a copiare tutti i files e le cartelle di Gnujiko */
 $out = "Creating a directory for the new disto...";
 $ret = GShell("mkdir tmp/mydist",$sessid,$shellid);
 if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
 $out.= "done!\n";

 /* Copio tutti i files e le cartelle dalla radice */
 $ret = GShell("ls",$sessid,$shellid);
 $files = $ret['outarr']['files'];
 $dirs = $ret['outarr']['dirs'];

 $interface = array("name"=>"progressbar","steps"=>count($files)+count($dirs));
 gshPreOutput($shellid,"Copy files and directories. Please wait!", "ESTIMATION", "", "PASSTHRU", $interface);

 /* Copy all files */
 $out.= "Copy all files into directory tmp/mydist/ ...";
 for($c=0; $c < count($files); $c++)
 {
  $file = $files[$c];
  gshPreOutput($shellid, "Copy file: <i>".$file['name']."</i>","PROGRESS", "");
  $ret = GShell("cp `".$file['name']."` `tmp/mydist/`",$sessid,$shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
 }
 $out.= "done!\n";

 /* Copy all directories */
 $out.= "Copy all directories into directory tmp/mydist/ ...";
 for($c=0; $c < count($dirs); $c++)
 {
  $dir = $dirs[$c];
  if($dir['name'] == "tmp")
   continue;
  gshPreOutput($shellid, "Copy directory: <i>".$dir['name']."</i>","PROGRESS", "");
  $ret = GShell("cp `".$dir['name']."` `tmp/mydist/`",$sessid,$shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
 }
 $out.= "done!\n";

 /* Copy the rest of files */
 $out.= "Creating tmp directory...";
 $ret = GShell("cp `tmp/index.php` `tmp/mydist/tmp/index.php`",$sessid,$shellid);
 if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],$ret['error']);
 $ret = GShell("cp `tmp/packages/index.php` `tmp/mydist/tmp/packages/index.php`",$sessid,$shellid);
 if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
 $out.= "done!\n";

 $out.= "Empty tmp/mydist/var/packages/ directory...";
 $ret = GShell("rm `tmp/mydist/var/packages/`",$sessid,$shellid);
 if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],$ret['error']);
 $ret = GShell("cp `var/packages/index.php` `tmp/mydist/var/packages/index.php`",$sessid,$shellid);
 if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);

 $out.= "done!\n";

 $out.= "Preparing config.php...";
 $var = array("_DATABASE_HOST","_DATABASE_USER","_DATABASE_PASSWORD","_DATABASE_NAME", "_FTP_SERVER", "_FTP_USERNAME", "_FTP_PASSWORD", "_FTP_PATH", "_DISTRO_NAME", "_SOFTWARE_VERSION");
 $val = array("localhost","","","","","","","",$distName,$distVer);
 $ret = ReplaceConfValue($_BASE_PATH."tmp/mydist/config.php",$var,$val,0777);
 if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
 $out.= "done!\n";

 /* Exporting database */
 $out.= "Exporting database...";
 $db = new AlpaDatabase();

 $bkoptions = array();
 $bkoptions["gnujiko_session"] = "CREATEONLY";

 $sql = $db->Backup("*",$bkoptions);
 if(!gfwrite($_BASE_PATH."tmp/mydist/installation/install.sql",$sql))
  return array('message'=>$out."failed!\nUnable to create file tmp/mydist/installation/install.sql",'error'=>'PERMISSION_DENIED');
 $db->Close();

 if(!$noZip)
 {
  /* Zipping all */
  $out.= "Compress new distro...";
  $ret = GShell("zip -i `tmp/mydist/` -o `tmp/mydist.zip`",$sessid,$shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
  $out.= "done!\n";
 }

 if(!$noRemoveTemp)
 {
  /* Remove temporary directory */
  $out.= "Removing temporary directory...";
  $ret = GShell("rm `tmp/mydist/`",$sessid,$shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'],'error'=>$ret['error']);
  $out.= "done!\n";
 }

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function ReplaceConfValue($strCfgFile,$strCfgVar,$strCfgVal,$mod=null)
{
 global $_BASE_PATH, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH;

 $ftpServer = $_FTP_SERVER;
 $ftpUser = $_FTP_USERNAME;
 $ftpPasswd = $_FTP_PASSWORD;
 $ftpPath = $_FTP_PATH;

 $strOldContent = file ($strCfgFile);
 $strNewContent = "";
 while (list ($intLineNum, $strLine) = each ($strOldContent)) 
 {
  if(is_array($strCfgVar))
  {
   for($c=0; $c < count($strCfgVar); $c++)
   {
	if(preg_match("/^\\$".$strCfgVar[$c]."( |\t)*=/i",$strLine))	// show any line beginning with a $
    {
     $strLineParts=explode("=",$strLine);
     // we should determine type of value here! (BOOL, INT or String)
     if("$".$strCfgVar[$c] == trim($strLineParts[0])) 
     {
	  $strLineParts[1] = "\t\"".$strCfgVal[$c]."\"";
	  $strLine = implode("=",$strLineParts).";\r\n";
     }
    }
   }
  }
  else if(preg_match("/^\\$".$strCfgVar."( |\t)*=/i",$strLine))	// show any line beginning with a $
  {
   $strLineParts=explode("=",$strLine);
   // we should determine type of value here! (BOOL, INT or String)
   if("$".$strCfgVar == trim($strLineParts[0])) 
   {
	$strLineParts[1] = "\t\"".$strCfgVal."\"";
	$strLine = implode("=",$strLineParts).";\r\n";
   }
  }
  $strNewContent .= $strLine;

 }

 $fp = @fopen($strCfgFile,"w");
 if(!$fp)
 {
  /* Try with FTP. */
  if($ftpUser)
  {
   $conn = @ftp_connect($ftpServer ? $ftpServer : $_SERVER['SERVER_NAME']);
   if(!$conn)
	return array('message'=>"Unable to open file $strCfgFile with FTP. Server connection failed","error"=>"FTP_SERVER_CONNECTION_FAILED");
   if(!@ftp_login($conn,$ftpUser,$ftpPasswd))
	return array('message'=>"Unable to open file $strCfgFile with FTP. Login failed!","error"=>"FTP_LOGIN_FAILED");
   
   if($ftpPath)
   {
	if(!@ftp_chdir($conn, $ftpPath))
	 return array("message"=>"Unable to change directory to $ftpPath with FTP.","error"=>"FTP_CHDIR_FAILED");
   }

   $strCfgFile = str_replace("../","",$strCfgFile);

   /* create temporary file */
   $tempHandle = tmpfile();
   fwrite($tempHandle, $strNewContent);
   rewind($tempHandle);       
   if(!@ftp_fput($conn, $strCfgFile, $tempHandle, FTP_ASCII))
	return array('message'=>"Unable to write into file $strCfgFile with FTP.","error"=>"FTP_WRITE_FAILED");
   @ftp_close($conn);
  }
  else
   return array('message'=>"Unable to open file $strCfgFile in write mode. Permission denied!",'error'=>"FILE_PERMISSION_DENIED");
 }
 else
 {
  fputs($fp,$strNewContent);
  fclose($fp);
  if($mod)
   @chmod($strCfgFile,$mod);
 }
 return array('message'=>"Done!");
}
//----------------------------------------------------------------------------------------------------------------------//

