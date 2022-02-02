<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-04-2013
 #PACKAGE: sendmail-config
 #DESCRIPTION: SendMail configuration form
 #VERSION: 2.1beta
 #CHANGELOG: 11-04-2013 : Sistemato permessi ai files.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DEFAULT_FILE_PERMS;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("config-sendmail");

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
if($_POST['action'] == "setupSendMail")
{
 $var = array("_SMTP_AUTH", "_SMTP_SENDMAIL", "_SMTP_HOST", "_SMTP_USERNAME", "_SMTP_PASSWORD");
 $val = array($_POST['smtp-auth'], $_POST['smtp-sendmail'], $_POST['smtp-host'], $_POST['smtp-username'], $_POST['smtp-passw']);
 ReplaceConfValue($_BASE_PATH."config.php",$var,$val,false,$_DEFAULT_FILE_PERMS);

 ?>
 <html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Config SendMail</title>
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
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Config SendMail</title>
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

$form = new GForm(i18n("Configure SendMail"), "MB_OK|MB_ABORT", "simpleform", "default", "orange", 480, 360);
$form->Begin($_ABSOLUTE_URL."share/widgets/config/icons/sendmail-icon.png",$_ABSOLUTE_URL."share/widgets/config.sendmail.php");
echo "<div id='contents'>";
?>
<input type='hidden' name='action' value='setupSendMail'/>
<input type='hidden' name='sessid' value="<?php echo $_REQUEST['sessid']; ?>"/>
<input type='hidden' name='shellid' value="<?php echo $_REQUEST['shellid']; ?>"/>
<div style='font-size:14px;color:#666666;margin-top:10px;' align='center'><?php echo i18n("Parametri per l'invio della posta elettronica."); ?></div>
<table width='80%' border='0' cellspacing='0' cellpadding='0' align='center' style='margin-top:20px;'>
<tr><td colspan='2'><input type='checkbox' name='smtp-auth' <?php if($_SMTP_AUTH) echo "checked='true'"; ?>/> <span><b><?php echo i18n("SMTP Authorization"); ?></b></span>&nbsp;&nbsp;&nbsp;<small style='color:#666666'>(default is disabled)</small></td></tr>
<tr><td colspan='2'><hr class='separator'/></td></tr>
<tr><td width='120'><?php echo i18n("Sendmail"); ?></td>
	<td><input type='text' size='15' name='smtp-sendmail' value="<?php echo $_SMTP_SENDMAIL; ?>"/><br/><small style='color:#666666'>(default is /usr/sbin/sendmail)</smal></td></tr>
<tr><td colspan='2'><hr class='separator'/></td></tr>
<tr><td width='120'><?php echo i18n("SMPT Host"); ?></td>
	<td><input type='text' size='20' name='smtp-host' value="<?php echo $_SMTP_HOST; ?>"/></td></tr>
<tr><td colspan='2'><hr class='separator'/></td></tr>
<tr><td width='120'><?php echo i18n("SMTP Username"); ?></td>
	<td><input type='text' size='15' name='smtp-username' value="<?php echo $_SMTP_USERNAME; ?>"/></td></tr>
<tr><td colspan='2'><hr class='separator'/></td></tr>
<tr><td width='120'><?php echo i18n("SMTP Password"); ?></td>
	<td><input type='text' size='15' name='smtp-passw' value="<?php echo $_SMTP_PASSWORD; ?>"/></td></tr>
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

