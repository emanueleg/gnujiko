<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-07-2016
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Common file and directory functions
 #VERSION: 2.6beta
 #CHANGELOG: 23-07-2016 : Creata classe GFTP con funzioni da completare.
			 13-11-2015 : Aggiunta funzione ftp_rmdirr.
			 07-11-2015 : Aggiunta funzione ftp_is_dir.
			 15-04-2013 : Bug fix in function gftpwrite.
			 11-04-2013 : Sistemato i permessi ai files.
			 15-02-2013 : Bug fix in function gfwrite.
			 10-02-2012 : New functions: gfwrite and gftpwrite.
			 09-02-2012 : Bug fix in function rmdirr with FTP.
			 11-09-2011 : Aggiunto argomento forceroot nella funzione fwopen
			 03-09-2011 : Bug fix in fwopen
 #TODO:
 
*/

//----------------------------------------------------------------------------------------------------------------------//
function rmdirr($dir) 
{
 global $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH;

 if (substr($dir,-1) != "/") $dir .= "/";
 if (!is_dir($dir)) return false;

 if (($dh = @opendir($dir)) !== false) 
 {
  while (($entry = readdir($dh)) !== false) 
  {
   if ($entry != "." && $entry != "..") 
   {
    if (is_file($dir . $entry) || is_link($dir . $entry))
	{
     if(!@unlink($dir . $entry))
	 {
	  // try with ftp //
      if($_FTP_USERNAME)
      {
       $conn = ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
       if(ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
       {
        if($_FTP_PATH)
        {
         $fldPath = str_replace("../","",$dir.$entry);
         $fldPath = $_FTP_PATH.$fldPath;
        }
        else
         $fldPath = $dir.$entry;
        if(!ftp_delete($conn, $fldPath))
         return false;
		ftp_close($conn);
       }
      }
	 }
	}
    else if (is_dir($dir . $entry))
	{
     if(!rmdirr($dir . $entry))
	 { // try with ftp //
      if($_FTP_USERNAME)
      {
       $conn = ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
       if(ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
       {
        if($_FTP_PATH)
        {
         $fldPath = str_replace("../","",$dir.$entry);
         $fldPath = $_FTP_PATH.$fldPath;
        }
        else
         $fldPath = $entry;
        if(!ftp_rmdir($conn, $fldPath))
         return false;
		ftp_close($conn);
       }
      }
	 }
	}
   }
  }
  closedir($dh);
  if(!rmdir($dir))
  {
   if($_FTP_USERNAME)
   {
    $conn = ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
    if(ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
    {
     if($_FTP_PATH)
     {
      $fldPath = str_replace("../","",$dir);
      $fldPath = $_FTP_PATH.$fldPath;
     }
     else
      $fldPath = $dir;
     if(!ftp_rmdir($conn, $fldPath))
      return false;
	 ftp_close($conn);
    }
   }
   else
	return false;
  }
  return true;
 }
 return false;
}
//----------------------------------------------------------------------------------------------------------------------//
function make_dir($dir,$chmod=null)
{
 global $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_DEFAULT_FILE_PERMS;
 if(!$chmod)
  $chmod = $_DEFAULT_FILE_PERMS;

 $p = explode("/", $dir);
 $path = "";
 for ($c=0; $c < count($p); $c++)
 {
  $path.= $p[$c]."/";
  if($path == "/")
   continue;
  if(!is_dir($path))
  {
   if(!@mkdir($path))
   {
    /* TRY WITH FTP */
	if($_FTP_USERNAME)
    {
     $conn = @ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
     if($conn && @ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
     {
      if($_FTP_PATH)
      {
	   if(!@ftp_chdir($conn, $_FTP_PATH))
		return false;
       $fldPath = str_replace("../","",$path);
	   $fldPath = ltrim(rtrim($fldPath,"/"),"/");
      }
      else
	  {
       $fldPath = ltrim(rtrim($path,"/"),"/");
	  }
	  if(@ftp_chdir($conn,$fldPath))
	  {
	   continue;
	  }
      if(@!ftp_mkdir($conn, $fldPath))
	   return false;
      else if(!@ftp_chmod($conn, $chmod, $fldPath))
	   return false;
      @ftp_close($conn);
       continue;
     }
     else
	  return false;
    }
	return false;
   }
   else
    @chmod($path,$chmod);
  }
 }
 return true;
}
//----------------------------------------------------------------------------------------------------------------------//
function full_copy($source,$target,$chmod=null,&$out="",&$err="")
{
 global $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_DEFAULT_FILE_PERMS;
 if(!$chmod)
  $chmod = $_DEFAULT_FILE_PERMS;

 $target = rtrim($target,"/");
 if(is_dir($source))
 {
  if(!@make_dir(ltrim($target,"/"),$chmod))
  {
   $out = "Unable to create folder $target";
   $err = "UNABLE_TO_CREATE_FOLDER";
   return false;
  }
  $d = dir( $source );
  while(FALSE !== ($entry = $d->read()))
  {
   if($entry == '.' || $entry == '..')
    continue;
   if(substr($entry, -1) == "~")
	continue;

   $Entry = rtrim($source,'/').'/'.ltrim($entry,'/');
   if(is_dir($Entry))
   {
    if(!full_copy($Entry,rtrim($target,'/').'/'.ltrim($entry,'/'),$chmod,$out,$err))
	 return false;
	continue;
   }
   $Dest = rtrim(ltrim($target,'/'),'/').'/'.ltrim($entry,'/');
   $Dest = ltrim($Dest,"/");

   if($_FTP_USERNAME)
   {
	// TRY WITH FTP. //
    $conn = @ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
	if($conn && @ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
    {
     if($_FTP_PATH)
     {
	  if(!@ftp_chdir($conn, $_FTP_PATH))
	   return false;
      $fldPath = str_replace("../","",$Dest);
	  $fldPath = ltrim(rtrim($fldPath,"/"),"/");
     }
     else
       $fldPath = ltrim(rtrim($path,"/"),"/");
	 $fp = fopen($Entry,"r");
	 if(!ftp_fput($conn, $Dest, $fp, FTP_BINARY))
	 {
	  $out = "Unable to copy $Entry into $Dest using FTP";
	  $err = "UNABLE_TO_COPY_USING_FTP";
	  return false;
	 }
	 @ftp_chmod($conn, intval($chmod,8), $fldPath);
	 @ftp_close($conn);
	 @fclose($fp);
	 continue;
    }
    else
    {
	 $out = "Unable to copy $Entry into $Dest using FTP";
	 $err = "UNABLE_TO_COPY_USING_FTP";
	 return false;
    }
   }
   else
   {
	// COPY IN NORMAL MODE //
    if(!@copy($Entry,$Dest))
    {
	 $out = "Unable to copy file $Entry into $Dest";
	 $err = "UNABLE_TO_COPY_FILE";
	 return false;
    }
	@chmod($target.'/'.$entry,intval($chmod,8));
	continue;
   }
  }
  $d->close();
 }
 else
 {
  if(!@make_dir(dirname($target),$chmod))
  {
   $out = "Unable to create folder $target";
   $err = "UNABLE_TO_CREATE_FOLDER";
   return false;
  }

  if($_FTP_USERNAME)
  {
   // TRY WITH FTP //
   $conn = @ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
   if($conn && @ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
   {
    if($_FTP_PATH)
    {
	 if(!@ftp_chdir($conn, $_FTP_PATH))
	  return false;
     $fldPath = str_replace("../","",$target);
	 $fldPath = ltrim(rtrim($fldPath,"/"),"/");
    }
    else
     $fldPath = ltrim(rtrim($path,"/"),"/");
	$fp = fopen($source,"r");
	if(!ftp_fput($conn, $target, $fp, FTP_BINARY))
	{
	 $out = "Unable to copy $source into $target using FTP";
	 $err = "UNABLE_TO_COPY_USING_FTP";
	 return false;
	}
	@ftp_chmod($conn, intval($chmod,8), $target);
	@ftp_close($conn);
	@fclose($fp);
   }
   else
   {
	$out = "Login failed when connect with FTP";
	$err = "FTP_LOGIN_FAILED";
	return false;
   }
  }
  else
  {
   // COPY IN NORMAL MODE //
   if(!@copy($source,$target))
   {
	$out = "Unable to copy file $source into $target";
	$err = "UNABLE_TO_COPY_FILE";
	return false;
   }
   @chmod($target,intval($chmod,8));
  }
 }
 return true;
}
//----------------------------------------------------------------------------------------------------------------------//
function fwopen($file, $args='w', $_USER_PATH="", $sessid=0, $shellid=0, $forceroot=false)
{
 //- questa funzione serve per aprire file in scrittura che necessitano di creare sotto-directory nel caso non esistano -//

 $p = explode("/", $file);
 $path = "";
 for ($c=0; $c < (count($p)-1); $c++)
 {
  $path.= $p[$c]."/";
  if(!is_dir($_USER_PATH.$path))
  {
   if($forceroot)
    GShell("mkdir `".$_USER_PATH.$path."`",$sessid,$shellid,$forceroot);
   else
    GShell("mkdir `$path`",$sessid,$shellid);
  }
 }
 return fopen($_USER_PATH.$file, $args);
}
//----------------------------------------------------------------------------------------------------------------------//
function gfwrite($fileName, $buffer,$chmod=null,$mode="w")
{
 global $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_DEFAULT_FILE_PERMS;
 if(!$chmod)
  $chmod = $_DEFAULT_FILE_PERMS;

 $h = @fopen($fileName,$mode);
 if(!$h)
 {
  $conn = @ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
  if(!$conn) return false;
  if(!@ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD)) return false;
  if($_FTP_PATH)
  {
   if(!@ftp_chdir($conn, $_FTP_PATH))
	return false;
  }

  $fileName =str_replace("../","",$fileName);

  /* create temporary file */
  $tempHandle = tmpfile();
  fwrite($tempHandle, $buffer);
  rewind($tempHandle);       
  if(!@ftp_fput($conn, $fileName, $tempHandle, FTP_BINARY))
   return false;

  @ftp_chmod($conn, $chmod, $fileName);

  @ftp_close($conn);
  return true;
 }

 $ret = @fwrite($h,$buffer);
 @chmod($fileName,$chmod);
 @fclose($h);
 return $ret;
}
//----------------------------------------------------------------------------------------------------------------------//
function gftpwrite($fileName, $buffer, $chmod=0755, $connpersistent=false, $debug=false)
{
 global $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_FTP_CONN;

 $fileName =str_replace("../","",$fileName);

 if($connpersistent && $_FTP_CONN)
  $conn = $_FTP_CONN;
 else
 {
  $conn = @ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
  if(!$conn)
  {
   if($debug)
    return array('message'=>"Unable to connect to server ".$_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']."\n",'error'=>"FTP_CONNECTION_FAILED");
   else
    return false;
  }
  if(!@ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD)) 
  {
   if($debug)
    return array("message"=>"FTP login failed! Unable to connect with user: ".$_FTP_USERNAME.". User or password are wrong!\n","error"=>"FTP_LOGIN_FAILED");
   else
    return false;
  }
  if($_FTP_PATH)
  {
   if(!@ftp_chdir($conn, $_FTP_PATH))
   {
    if($debug)
     return array("message"=>"Unable to change directory to ".$_FTP_PATH."\n","error"=>"FTP_CHDIR_FAILED");
    else
     return false;
   }
  }
  $_FTP_CONN = $conn;
 }
 
 $p = explode("/", $fileName);
 $path = "";
 for ($c=0; $c < (count($p)-1); $c++)
 {
  $basepath = $path;
  $path.= $p[$c]."/";
  if(@ftp_chdir($conn, $_FTP_PATH.$path))
   @ftp_chdir($conn, $_FTP_PATH.$path);
  else
  {
   if(@ftp_mkdir($conn, $_FTP_PATH.$path))
	@ftp_chdir($conn, $_FTP_PATH.$path);
   else
   {
    if($debug)
     return array("message"=>"Unable to create directory ".$path,"error"=>"FTP_MKDIR_FAILED");
	else
     return false;
   }
   @ftp_chmod($conn, $chmod, $_FTP_PATH.$path);
  }
 }
 
 /* create temporary file */
 $tempHandle = tmpfile();
 @fwrite($tempHandle, $buffer);
 @rewind($tempHandle);    
 if(!@ftp_fput($conn, basename($fileName), $tempHandle, FTP_BINARY))
 {
  if($debug)
   return array("message"=>"Unable to create file ".$fileName,"error"=>"FTP_PUT_FAILED");
  else
   return false;
 }

 @ftp_chmod($conn, $chmod, $fileName);
 
 if(!$connpersistent)
  @ftp_close($conn);
 return true; 
}
//----------------------------------------------------------------------------------------------------------------------//
function ftp_is_dir($ftpConn, $dir) 
{
 $currentDir = ftp_pwd($ftpConn);
 if(@ftp_chdir($ftpConn,$dir))
 {
  ftp_chdir($ftpConn,$currentDir);
  return true;
 }
 return false;
} 
//----------------------------------------------------------------------------------------------------------------------//
function ftp_rmdirr($ftpConn, $dir)
{
 if(!(@ftp_rmdir($ftpConn, $dir) || @ftp_delete($ftpConn, $dir)))
 {
  $list = @ftp_nlist($ftpConn, $dir);
  if(!empty($list))
  {
   foreach($list as $value)
	ftp_rmdirr($ftpConn, $value);
  }
  if(!@ftp_rmdir($ftpConn, $dir))
   return false;
 }

 return true;
}
//----------------------------------------------------------------------------------------------------------------------//
//----------------------------------------------------------------------------------------------------------------------//
//----------------------------------------------------------------------------------------------------------------------//
class GFTP
{
 var $ftpHost, $ftpLogin, $ftpPassword, $ftpBasePath;
 var $defaultChmod, $debug, $error, $conn;

 function GFTP($host="", $login="", $password="", $basepath="")
 {
  $this->ftpHost = $host;
  $this->ftpLogin = $login;
  $this->ftpPassword = $password;
  $this->ftpBasePath = $basepath ? "/".ltrim(dirname($basepath)."/".basename($basepath), "/")."/" : "";

  $this->defaultChmod = 0644;

  $this->debug = "";
  $this->error = "";
  $this->conn = null;

 }
 //-------------------------------------------------------------------------------------------------------------------//
 function connect($host=null, $login=null, $password=null, $basepath=null)
 {
  if(isset($host)) 			$this->ftpHost = $host;
  if(isset($login))			$this->ftpLogin = $login;
  if(isset($password))		$this->ftpPassword = $password;
  if(isset($basepath))		$this->ftpBasePath = $basepath ? "/".ltrim(dirname($basepath)."/".basename($basepath), "/")."/" : "";

  $this->debug.= "Connect to server ".$this->ftpHost."...";
  $this->conn = @ftp_connect($this->ftpHost);
  if(!$this->conn)
   return $this->returnError("failed!\nUnable to connect to host ".$this->ftpHost, "FTP_CONNECTION_FAILED");
   
  if(!@ftp_login($this->conn,$this->ftpLogin,$this->ftpPassword)) 
   return $this->returnError("failed!\nUnable to connect with user ".$this->ftpLogin.". User or password are wrong!", "FTP_LOGIN_FAILED");

  if($this->ftpBasePath)
  {
   if(!@ftp_chdir($this->conn, $this->ftpBasePath))
	return $this->returnError("failed!\nUnable to change directory to ".$this->ftpBasePath, "FTP_CHDIR_FAILED");
  }

  return true;
 }
 //-------------------------------------------------------------------------------------------------------------------//
 function upload($sourceFile, $target="", $chmod=null)
 {
  global $_BASE_PATH;

  if(!file_exists($_BASE_PATH.$sourceFile)) return $this->returnError("File ".$sourceFile." does not exists.", "SOURCE_FILE_DOES_NOT_EXISTS");
  $fp = fopen($_BASE_PATH.$sourceFile,"r");
  if(!$fp) return $this->returnError("FTP error: Unable to read source file ".$sourceFile, 'SOURCE_FILE_READ_FAILED');

  $destFileName = "";
  $destFilePath = "";
  if(!$chmod) $chmod = $this->defaultChmod;

  if($target)
  {
   if(strpos($target, ".") !== false)
   {
	$destFileName = basename($target);
    $destFilePath = dirname($target);
   }
   else
   {
	$destFileName = basename($sourceFile);
	$destFilePath = $target;
   }
  }
  else
   $destFileName = basename($sourceFile);

  if($destFilePath && !@ftp_chdir($this->conn, $this->ftpBasePath.$destFilePath))
  {
   $p = explode("/", $destFilePath);
   $path = "";
   for($c=0; $c < count($p); $c++)
   {
    $basepath = $path;
    $path.= $p[$c]."/";
    if(@ftp_chdir($this->conn, $this->ftpBasePath.$path))
     @ftp_chdir($this->conn, $this->ftpBasePath.$path);
    else
    {
     if(@ftp_mkdir($this->conn, $this->ftpBasePath.$path))
 	  @ftp_chdir($this->conn, $this->ftpBasePath.$path);
     else
	  return $this->returnError("Unable to create directory ".$path, "FTP_MKDIR_FAILED");
     @ftp_chmod($this->conn, $chmod, $this->ftpBasePath.$path);
    }
   }
  }
  
  $target = ($destFilePath ? $destFilePath."/" : "").$destFileName;
  
  if(!@ftp_fput($this->conn, $this->ftpBasePath."/".$target, $fp, FTP_BINARY))
   return $this->returnError("Unable to copy ".$sourceFile." to ".$target." via FTP.", "FTP_COPY_FAILED");
  @ftp_chmod($this->conn, $chmod, $this->ftpBasePath."/".$target);

  $this->debug = "Upload ".$sourceFile." to ".$target." ...done!\n";

  return true;
 }
 //-------------------------------------------------------------------------------------------------------------------//

 //-------------------------------------------------------------------------------------------------------------------//
 function returnError($message="", $error="")
 {
  if($message) $this->debug.= $message;
  if($error) $this->error = $error;
  return false;
 }
 //-------------------------------------------------------------------------------------------------------------------//



}
//----------------------------------------------------------------------------------------------------------------------//




