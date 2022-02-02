<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 27-10-2016
 #PACKAGE: makedist
 #DESCRIPTION: Database import.
 #VERSION: 2.2beta
 #CHANGELOG: 27-10-2016 : MySQLi integration.
			 23-04-2013 : Bug fix su default file perms.
 #TODO:
 
*/

global $_BASE_PATH, $_ERR, $_ERR_MSG, $_ERR_PHASE, $_DATABASE_USER, $_DATABASE_PASSWORD, $_DATABASE_NAME, $_DATABASE_HOST;
//-------------------------------------------------------------------------------------------------------------------//
if($_REQUEST['action'] != "importdb")
{
 $params = "step=5&lang=".$_REQUEST['lang'];
 $params.= "&database-host=".$_REQUEST['database-host'];
 $params.= "&database-name=".$_REQUEST['database-name'];
 $params.= "&database-user=".$_REQUEST['database-user'];
 $params.= "&database-passwd=".$_REQUEST['database-passwd'];
 $params.= "&ftp-server=".$_REQUEST['ftp-server'];
 $params.= "&ftp-path=".$_REQUEST['ftp-path'];
 $params.= "&ftp-user=".$_REQUEST['ftp-user'];
 $params.= "&ftp-passwd=".$_REQUEST['ftp-passwd'];
 $params.= "&def-file-perms=".$_REQUEST['def-file-perms'];
 $params.= "&root-password=".$_REQUEST['root-password'];
 $params.= "&primary-user=".$_REQUEST['primary-user'];
 $params.= "&primary-password=".$_REQUEST['primary-password'];
 $params.= "&action=importdb";
 header("refresh:2;url=".$_ABSOLUTE_URL."installation/index.php?".$params);
}
else
{
 $_ERR = "";
 $_ERR_PHASE = "";
 $_ERR_MSG = "";

 $dbhost = trim($_REQUEST['database-host']);
 $dbname = trim($_REQUEST['database-name']);
 $dbuser = trim($_REQUEST['database-user']);
 $dbpass = trim($_REQUEST['database-passwd']);

 $_DATABASE_NAME =	$dbname;
 $_DATABASE_USER =	$dbuser;
 $_DATABASE_PASSWORD =	$dbpass;
 $_DATABASE_HOST =	$dbhost;

 $ftpServer = trim($_REQUEST['ftp-server']);
 $ftpUser = trim($_REQUEST['ftp-user']);
 $ftpPasswd = trim($_REQUEST['ftp-passwd']);
 $ftpPath = trim($_REQUEST['ftp-path']);

 $_FTP_SERVER =	$ftpServer;
 $_FTP_USERNAME = $ftpUser;
 $_FTP_PASSWORD = $ftpPasswd;
 $_FTP_PATH = $ftpPath;
 $_DEFAULT_FILE_PERMS = $_REQUEST['def-file-perms'];

 /* IMPORT DATABASE */
 $db = new AlpaDatabase($dbhost, $dbuser, $dbpass, $dbname);
 $db->RunQueryFromFile($_BASE_PATH."installation/install.sql");
 $db->Close();

 /* UPDATE CONFIG.PHP */
 $var = array("_DATABASE_HOST","_DATABASE_USER","_DATABASE_PASSWORD","_DATABASE_NAME", "_FTP_SERVER", "_FTP_USERNAME", "_FTP_PASSWORD", "_FTP_PATH", "_DEFAULT_FILE_PERMS");
 $val = array($dbhost,$dbuser,$dbpass,$dbname,$ftpServer,$ftpUser,$ftpPasswd,$ftpPath,$_DEFAULT_FILE_PERMS);
 $ret = ReplaceConfValue($_BASE_PATH."config.php",$var,$val,intval($_DEFAULT_FILE_PERMS,8),$ftpServer,$ftpUser,$ftpPasswd,$ftpPath);

 if($ret['error'])
 {
  $_ERR_PHASE = i18n('Updating config.php');
  $_ERR = $ret['error'];
  $_ERR_MSG = $ret['message'];
 }

 /* CREATE ROOT USER */
 $rootPassword = trim($_REQUEST['root-password']);
 $now = time();
 $cryptpassword = md5($rootPassword.$now);

 $db = new AlpaDatabase($dbhost, $dbuser, $dbpass, $dbname);
 $db->RunQuery("SELECT id FROM gnujiko_users WHERE username='root' LIMIT 1");
 if($db->Read())
 {
  $id = $db->record['id'];
  $db->RunQuery("UPDATE gnujiko_users SET password='".$cryptpassword."',regtime='".$now."',enableshell='1',disabled='0' WHERE id='".$id."'");
 }
 else
 {
  $db->RunQuery("INSERT INTO gnujiko_users (username,password,regtime,enableshell) VALUES('root','".$cryptpassword."','$now','1')");
  $id = $db->GetInsertId();
 }
 $db->Close();

 if(!$id)
 {
  $_ERR = "UNABLE_TO_CREATE_ROOT";
  $_ERR_MSG = "Unable to create root. I can't write to table gnujiko_users into database ".$dbname;
  $_ERR_PHASE = i18n('Creating root user');
 }

 /* LOGIN AS ROOT */
 $sessid = md5($id."root".$now);
 $db = new AlpaDatabase($dbhost, $dbuser, $dbpass, $dbname);
 $db->RunQuery("INSERT INTO gnujiko_session(uname,login_time,time,session_id,uid) VALUES('root','".$now."','".$now."','".$sessid."','".$id."')");
 $db->Close();


 /* CREATE FIRST USER */
 $username = trim($_REQUEST['primary-user']);
 $password = trim($_REQUEST['primary-password']);
 $cryptpassword = md5($password.$now);

 $db = new AlpaDatabase($dbhost, $dbuser, $dbpass, $dbname);
 $db->RunQuery("SELECT * FROM gnujiko_users WHERE username!='root' ORDER BY id ASC LIMIT 1");
 if($db->Read())
 {
  // aggiorna solo nome utente e password, mantenendo la directory //
  $db->RunQuery("UPDATE gnujiko_users SET username='".$username."',fullname='".$username."',password='".$cryptpassword."',regtime='"
	.$now."',enableshell='1',disabled='0',last_time_access='' WHERE id='".$db->record['id']."'");
 }
 else
 {
  // crea un nuovo utente //
  $ret = GShell("useradd -name `".$username."` -password `".$password."` --enable-shell -privileges 'mkdir_enable=1,edit_account_info=1,run_sudo_commands=1'",$sessid);
  if($ret['error'])
  {
   $_ERR = $ret['error'];
   $_ERR_MSG = $ret['message'];
   $_ERR_PHASE = i18n('Creating first user');
  }

 }
 $db->Close();

 if(!$_ERR)
 {
  $params = "step=6&lang=".$_REQUEST['lang'];
  header("Location: ".$_ABSOLUTE_URL."installation/index.php?".$params);
  exit();
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function ReplaceConfValue($strCfgFile,$strCfgVar,$strCfgVal,$mod=null,$ftpServer="",$ftpUser="",$ftpPasswd="",$ftpPath="")
{
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
installer_begin(i18n("Install &raquo; Database import"), sprintf(i18n("step <b>%d</b> of <b>%d</b>"),4,4));
?>
<style type='text/css'>

</style>
<?php
installer_startContents();

?>
<form action="index.php" method="POST" id='mainform'>
<input type='hidden' name='step' value="5"/>
<input type='hidden' name='lang' value="<?php echo $_REQUEST['lang']; ?>"/>
<input type='hidden' name='database-host' value="<?php echo $_REQUEST['database-host']; ?>"/>
<input type='hidden' name='database-name' value="<?php echo $_REQUEST['database-name']; ?>"/>
<input type='hidden' name='database-user' value="<?php echo $_REQUEST['database-user']; ?>"/>
<input type='hidden' name='database-passwd' value="<?php echo $_REQUEST['database-passwd']; ?>"/>
<input type='hidden' name='ftp-server' value="<?php echo $_REQUEST['ftp-server']; ?>"/>
<input type='hidden' name='ftp-path' value="<?php echo $_REQUEST['ftp-path']; ?>"/>
<input type='hidden' name='ftp-user' value="<?php echo $_REQUEST['ftp-user']; ?>"/>
<input type='hidden' name='ftp-passwd' value="<?php echo $_REQUEST['ftp-passwd']; ?>"/>
<input type='hidden' name='def-file-perms' value="<?php echo $_REQUEST['def-file-perms']; ?>"/>
<input type='hidden' name='root-password' value="<?php echo $_REQUEST['root-password']; ?>"/>
<input type='hidden' name='primary-user' value="<?php echo $_REQUEST['primary-user']; ?>"/>
<input type='hidden' name='primary-password' value="<?php echo $_REQUEST['primary-password']; ?>"/>

<?php
if($_REQUEST['action'] == "importdb")
{
 // SHOW ERROR
 ?>
 <div style="font-family:Arial;font-size:18px;padding-bottom:10px;color:#b50000;"><i><?php echo i18n("Error. Can not continue!"); ?></i></div>
 <hr/>
 <span style='color:#005c94;font-size:12px'><?php echo i18n("The problem occurred in the step:"); ?> <b style='text-transform:uppercase'><?php echo $_ERR_PHASE; ?></b></span><br/><br/>
 <span style='color:#005c94;font-size:12px'><?php echo i18n("Error code:"); ?> <b style='color:#b50000;font-size:12px;'><?php echo $_ERR; ?></b></span><br/><br/>
 <span style='color:#005c94;font-size:10px;color:#333333;'><i><?php echo i18n('message'); ?></i></span>
 <hr/>
 <div style="font-family:Arial;font-size:14px;color:#b50000;padding-top:50px;text-align:center;">
 <?php echo $_ERR_MSG; ?> 
 </div>
 <?php
}
else
{
 ?>
 <div style="font-family:Arial;font-size:13px;padding-bottom:10px;color:#005c94;"><i><?php echo i18n("Wait until the system import the main database, depending on the size this may take several minutes."); ?></i></div>
 <hr/>
 <div style="font-family:Arial;font-size:18px;color:#000000;padding-top:50px;text-align:center;">
 <?php echo i18n("Importing the database..."); ?><br/>
 <img src="<?php echo $_ABSOLUTE_URL; ?>installation/img/loadingbar.gif"/>
 </div>
 <?php
}
?>
</form>
<?php
installer_endContents();
?>
<div class="footer">
 <?php
 if(($_REQUEST['action'] == "importdb") && $_ERR)
 {
  echo "<a href='http://gnujiko.alpatech.it?page_id=354' target='gnujikohelp' class='right-button'><span>".i18n("Investigate the incident")." &raquo;</span></a>";
  echo "<a href='#' id='submit-button' class='right-button' onclick='retry()'><span>".i18n("Try again")." &raquo;</span></a>";
 }
 else
  echo "&nbsp;";
 ?>
</div>
<script>
function retry()
{
 document.location.reload();
}
</script>
<?php
installer_end();

