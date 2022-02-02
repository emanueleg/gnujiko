<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-04-2013
 #PACKAGE: gnujiko-language-pack
 #DESCRIPTION: 
 #VERSION: 2.1beta
 #CHANGELOG: 11-04-2013 : Sistemato i permessi ai files.
			 03-11-2012 : Bug fix in form action.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DEFAULT_FILE_PERMS;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("config-language");

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
if($_POST['action'] == "setupLanguage")
{
 $var = array("_LANGUAGE");
 $val = array($_POST['language']);
 ReplaceConfValue($_BASE_PATH."config.php",$var,$val,false,$_DEFAULT_FILE_PERMS);

 ?>
 <html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Config language</title>
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
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Config language</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
?>
<style type='text/css'>
span.langname {
	font-family: Arial;
	font-size: 18px;
	color: #666666;
}

hr.separator {
	background: #cccccf;
	border: 0px;
	height: 1px;
}

</style>
</head><body>
<?php

$form = new GForm(i18n("Configure language"), "MB_OK|MB_ABORT", "simpleform", "default", "orange", 480, 320);
$form->Begin($_ABSOLUTE_URL."share/widgets/config/icons/language-icon.png",$_ABSOLUTE_URL."share/widgets/config.language.php");
echo "<div id='contents'>";

$languages = array("en-GB"=>"English", "it-IT"=>"Italian");

?>
<input type='hidden' name='sessid' value="<?php echo $_REQUEST['sessid']; ?>"/>
<input type='hidden' name='action' value='setupLanguage'/>
<div style='font-size:14px;color:#666666;margin-top:10px;' align='center'><?php echo i18n("Choose the default language for the whole system and applications."); ?></div>
<table width='80%' border='0' cellspacing='0' cellpadding='0' align='center' style='margin-top:20px;'>
<?php
$c = 0;
while(list($k,$v) = each($languages))
{
 echo "<tr><td width='30' valign='middle'><input type='radio' name='language' value='".$k."'".($_LANGUAGE == $k ? " checked='true'" : "")."/></td>";
 echo "<td width='80'><img src='".$_ABSOLUTE_URL."share/widgets/config/lang-icons/".$k.".png'/></td>";
 echo "<td><span class='langname'>".i18n($v)."</span></td></tr>";
 $c++;
 if($c < count($languages))
  echo "<tr><td colspan='3'><hr class='separator'/></td></tr>";
}
?>
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

