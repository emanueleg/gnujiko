<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 27-10-2016
 #PACKAGE: makedist
 #DESCRIPTION: FTP settings form.
 #VERSION: 2.1beta
 #CHANGELOG: 27-10-2016 : MySQLi integration.
 #TODO:
 
*/

global $_LANGUAGE, $_ABSOLUTE_URL;

$_ERR = "";

//-------------------------------------------------------------------------------------------------------------------//
if(isset($_POST['action']))
{
 switch($_POST['action'])
 {
  case 'ftp-check' : {
	 $ftpServer = trim($_POST['ftp-server']);
	 $ftpPath = trim($_POST['ftp-path']);
	 $ftpUser = trim($_POST['ftp-user']);
	 $ftpPasswd = trim($_POST['ftp-passwd']);
	 $defFilePerms = $_POST['def-file-perms'] ? $_POST['def-file-perms'] : "0777";
 
	 $params = "step=3&lang=".$_POST['lang'];
	 $params.= "&database-host=".$_POST['database-host'];
	 $params.= "&database-name=".$_POST['database-name'];
	 $params.= "&database-user=".$_POST['database-user'];
	 $params.= "&database-passwd=".$_POST['database-passwd'];
	 $params.= "&ftp-server=".$_POST['ftp-server'];
	 $params.= "&ftp-path=".$_POST['ftp-path'];
	 $params.= "&ftp-user=".$_POST['ftp-user'];
	 $params.= "&ftp-passwd=".$_POST['ftp-passwd'];
	 $params.= "&def-file-perms=".$_POST['def-file-perms'];


	 if(!$ftpPath && !$ftpUser && !$ftpPasswd)
	 {
	  header("Location: ".$_ABSOLUTE_URL."installation/index.php?".$params);
	  exit();
	 }
	 else
	 {
	  /* Test connection */
	  $conn = @ftp_connect($ftpServer);
   	  if(!$conn)
	   $_ERR = "CONNECT_FAILED";
	  else if(!@ftp_login($conn,$ftpUser,$ftpPasswd))
	   $_ERR = "LOGIN_FAILED";
	  else if($ftpPath && !@ftp_chdir($conn, $ftpPath))
	   $_ERR = "CHDIR_FAILED";
	  else
	  {
	   @ftp_close($conn);
	   header("Location: ".$_ABSOLUTE_URL."installation/index.php?".$params);
	   exit();
	  }
	 }
	} break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
installer_begin(i18n("Install &raquo; FTP settings"), sprintf(i18n("step <b>%d</b> of <b>%d</b>"),2,4));
?>
<style type='text/css'>
table.form {background: url(img/ftp.png) top right no-repeat;}
table.form td {
	font-family:Arial;
	font-size:13px;
	color#000000;
	padding-bottom: 10px;
}

</style>
<?php
installer_startContents();

if($_ERR)
{
 echo "<div class='error-box' id='error-box' style='width:180px;height:180px;left:420px;top:150px;'>";
 switch($_ERR)
 {
  case 'CONNECT_FAILED' : echo "<h3>".i18n("Connection failed!")."</h3>".i18n("I can not connect to the FTP server, check you have entered the correct parameters."); break;
  case 'LOGIN_FAILED' : echo "<h3>".i18n("Login failed!")."</h3>".i18n("I can not login to FTP using the credentials that you gave me, check that your user name and password are correct."); break;
  case 'CHDIR_FAILED' : echo "<h3>".i18n("FTP path wrong!")."</h3>".i18n("I can connect to FTP server using the credentials that you provided, but I can not access to the root folder of Gnujiko. <br/><br/>Probably the path you provided is incorrect."); break;
 }
 echo "</div>";
}

?>
<form action="index.php" method="POST" id='mainform'>
<input type='hidden' name='action' value='ftp-check'/>
<input type='hidden' name='step' value="<?php echo $_REQUEST['step']; ?>"/>
<input type='hidden' name='lang' value="<?php echo $_REQUEST['lang']; ?>"/>
<input type='hidden' name='database-host' value="<?php echo $_REQUEST['database-host']; ?>"/>
<input type='hidden' name='database-name' value="<?php echo $_REQUEST['database-name']; ?>"/>
<input type='hidden' name='database-user' value="<?php echo $_REQUEST['database-user']; ?>"/>
<input type='hidden' name='database-passwd' value="<?php echo $_REQUEST['database-passwd']; ?>"/>

<table class='form' width='100%' cellspacing='0' cellpadding='0' border='0'>
<tr><td valign='middle' width='180'><b>FTP SERVER:</b></td>
	<td valign='top'><span class='smallgray'><?php echo i18n("Specify the FTP server"); ?></span><br/>
		<div class='edit'><input type='text' class='text' name='ftp-server' id='ftp-server' value="<?php echo isset($_POST['ftp-server']) ? $_POST['ftp-server'] : $_SERVER['HTTP_HOST']; ?>"/></div>
	</td></tr>

<tr><td valign='middle'><b>FTP USER:</b></td>
	<td valign='top'><span class='smallgray'><?php echo i18n("Enter an FTP user."); ?></span><br/>
		<div class='edit'><input type='text' class='text' name='ftp-user' id='ftp-user' value="<?php echo $_POST['ftp-user']; ?>"/></div>
	</td></tr>

<tr><td valign='middle'><b>FTP PASSWORD:</b></td>
	<td valign='top'><span class='smallgray'><?php echo i18n("Enter the password for FTP access."); ?></span><br/>
		<div class='edit'><input type='text' class='text' name='ftp-passwd' id='ftp-passwd' value="<?php echo $_POST['ftp-passwd']; ?>"/></div>
	</td></tr>

<tr><td valign='middle'><b>FTP PATH:</b></td>
	<td valign='top'><span class='smallgray'><?php echo i18n("Enter the full path to the Gnujiko directory"); ?></span><br/>
		<div class='edit'><input type='text' class='text' name='ftp-path' id='ftp-path' value="<?php echo $_POST['ftp-path']; ?>"/></div>
	</td></tr>

<tr><td valign='middle'><b>FTP CHMOD:</b></td>
	<td valign='top'><span class='smallgray'><?php echo i18n("Set the default file permissions"); ?></span><br/>
		<div class='edit'><input type='text' class='text' name='def-file-perms' id='def-file-perms' value="<?php echo $_POST['def-file-perms'] ? $_POST['def-file-perms'] : '0777'; ?>"/></div>
	</td></tr>

</table>
</form>
<?php
installer_endContents();
?>
<div class="footer">
 <a href='#' id='submit-button' class='right-button' onclick='submit()'><span><?php echo i18n($_ERR ? "Try again" : "Next"); ?> &raquo;</span></a>
</div>

<script>
function submit()
{
 document.getElementById('mainform').submit();
}
</script>
<?php
installer_end();

