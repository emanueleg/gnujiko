<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-04-2013
 #PACKAGE: system-config-gui
 #DESCRIPTION: FTP configuration form
 #VERSION: 2.2beta
 #CHANGELOG: 11-04-2013 : Sistemato permessi ai files.
			 03-11-2012 - Bug fix.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DEFAULT_FILE_PERMS;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("config-ftp");

$sessInfo = sessionInfo($_REQUEST['sessid']);
if($sessInfo['uname'] != "root")
{
 $msg = "You must be root";
 ?>
 <script>
 function bodyOnLoad()
 {
  alert("<?php echo $msg; ?>");
  gframe_close();
 }
 </script>
 <?php
 return;
}
//----------------------------------------------------------------------------------------------------------------------//
function ReplaceConfValue($strCfgFile,$strCfgVar,$strCfgVal,$backup=false,$mod=null)
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
  if($backup)
   $fp = fopen($strCfgFile."_new", "w");
  else
   $fp = fopen($strCfgFile,"w");
  fputs($fp,$strNewContent);
  fclose($fp);
  if($mod)
  {
   if($backup)
    @chmod($strCfgFile."_new",$mod);
   else
    @chmod($strCfgFile,$mod);
  }
 }
 if($backup)
 {
  if(!rename($strCfgFile,$strCfgFile.".bak")) echo "<pre><b>Error: Could not rename old file!</b></pre>";
  if(!rename($strCfgFile."_new",$strCfgFile)) echo "<pre><b>Failed to copy File!</b></pre>";
 }
}
//----------------------------------------------------------------------------------------------------------------------//
if($_POST['action'] == "setupFTP")
{
 if($_POST['enableftp'] != "on")
 {
  $_POST['server-url'] = "";
  $_POST['server-path'] = "";
  $_POST['ftp-login'] = "";
  $_POST['ftp-passw'] = "";
 }

 $var = array("_FTP_SERVER", "_FTP_PATH", "_FTP_USERNAME", "_FTP_PASSWORD");
 $val = array($_POST['server-url'], $_POST['server-path'], $_POST['ftp-login'], $_POST['ftp-passw']);
 ReplaceConfValue($_BASE_PATH."config.php",$var,$val,false,$_DEFAULT_FILE_PERMS);

 ?>
 <html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Config FTP</title>
 </head><body>
 <script>
 function bodyOnLoad()
 {
  gframe_close("<?php echo $_POST['language']; ?>");
 }
 </script>
 </body></html>
 <?php 
 exit;
}


?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Config FTP</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
?>
<style type='text/css'>
span {
	font-family: Arial;
	font-size: 12px;
	color: #666666;
}

hr.separator {
	background: #cccccf;
	border: 0px;
	height: 1px;
}

td {
	font-size: 12px;
	color: #0169c9;
	font-weight: bold;
}
</style>
</head><body>
<?php

$form = new GForm(i18n("Configure FTP"), "MB_OK|MB_ABORT", "simpleform", "default", "orange", 480, 340);
$form->Begin($_ABSOLUTE_URL."share/widgets/config/icons/ftp-icon.png", $_ABSOLUTE_URL."share/widgets/config.ftp.php");
echo "<div id='contents'>";
?>
<input type='hidden' name='sessid' value="<?php echo $_REQUEST['sessid']; ?>"/>
<input type='hidden' name='action' value='setupFTP'/>
<div style='font-size:14px;color:#666666;margin-top:10px;' align='center'><?php echo i18n("Configures the FTP access on this system."); ?></div>
<table width='80%' border='0' cellspacing='0' cellpadding='0' align='center' style='margin-top:20px;'>
<tr><td colspan='2'><input type='checkbox' name='enableftp' <?php if($_FTP_SERVER) echo "checked='true'"; ?>/> <span><b><?php echo i18n("Enable FTP access"); ?></b></span></td></tr>
<tr><td colspan='2'><hr class='separator'/></td></tr>
<tr><td width='90'><?php echo i18n("Server URL"); ?></td>
	<td><input type='text' size='20' name='server-url' value="<?php echo $_FTP_SERVER; ?>"/></td></tr>
<tr><td colspan='2'><hr class='separator'/></td></tr>
<tr><td width='90'><?php echo i18n("Server Path"); ?></td>
	<td><input type='text' size='20' name='server-path' value="<?php echo $_FTP_PATH; ?>"/></td></tr>
<tr><td colspan='2'><hr class='separator'/></td></tr>
<tr><td width='90'><?php echo i18n("Login"); ?></td>
	<td><input type='text' size='15' name='ftp-login' value="<?php echo $_FTP_USERNAME; ?>"/></td></tr>
<tr><td colspan='2'><hr class='separator'/></td></tr>
<tr><td width='90'><?php echo i18n("Password"); ?></td>
	<td><input type='text' size='15' name='ftp-passw' value="<?php echo $_FTP_PASSWORD; ?>"/></td></tr>
</table>
<?php
echo "</div>";
$form->End();
?>
<script>
function OnFormSubmit()
{
 return true;
}
</script>
</body></html>
<?php

