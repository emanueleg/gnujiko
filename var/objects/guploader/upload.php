<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-07-2013
 #PACKAGE: guploader
 #DESCRIPTION: Gnujiko uploader utility
 #VERSION: 2.2beta
 #CHANGELOG: 23-07-2013 : Bug fix.
			 17-05-2013 : Aggiunto allow file types.
 #TODO: 
 
*/

global $_BASE_PATH,$_ABSOLUTE_URL,$_USERS_HOMES;
$_BASE_PATH = "../../../";

include($_BASE_PATH."var/objects/guploader/guploader_startup.php");
include_once($_BASE_PATH."etc/mimetypes.php");

if(!$_POST['frmId'])
{
 ?>
 <script>
 alert("Ci sono dei problemi nel caricamento del file.");
 </script>
 <?php
 return;
}

 ?>
 <script>
 var par = window.parent.document;
 var f = par.getElementById("<?php echo $_POST['frmId']; ?>");
 </script>
 <?php

$bad_files = array('exe', 'php', 'php3', 'php4', 'ph3', 'ph4', 'perl', 'cgi', 'bin', 'scr', 'bat', 'pif', 'aps', 'ssi', 'swf', 'js');

if((isset($_FILES['upldFile'])) and (is_array($_FILES['upldFile'])))
{
 $file = $_FILES['upldFile'];
  if($file['name'] == "")
  {
   ?><script>
   f.uploadResponse(false,'UNKNOWN_ERROR','Invalid filename');
   </script><?php
   return false;
  }
 $fileName = $file['name'];
 $fileSize = $file['size'];
 $fileExtension = strtolower(substr($fileName, strrpos($fileName, '.')+1));
 if($_POST['filename'])
  $fileName = $_POST['filename'].".".$fileExtension;
 else
  $fileName = substr($fileName, 0, strrpos($fileName, '.')+1).$fileExtension;

 $type = "";
 if($mimetypes[$fileExtension])
  $type = $mimetypes[$fileExtension];
 $icon = getMimetypeIcons($type);
 if($icon['size22x22'])
  $icon = $_ABSOLUTE_URL.$icon['size22x22'];
 else if($icon['size48x48'])
  $icon = $_ABSOLUTE_URL.$icon['size48x48'];
 else
  $icon = "";
 //--- fill data ---//
 ?>
 <script>
 f.FileSize = <?php echo $fileSize; ?>;
 f.FileExtension = "<?php echo $fileExtension; ?>";
 f.FileName = "<?php echo ltrim(substr($fileName, 0, strlen($fileName)-strlen($fileExtension)-1)); ?>";
 f.FileIcon = "<?php echo $icon; ?>";
 </script>
 <?php
}
else
{
 ?><script>
 f.uploadResponse(false,'BAD_FILE');
 </script><?php
 return false;
}

//--- check if is a bad file type ---//
if(in_array($fileExtension, $bad_files))
{
 $retFalse = true;
 if($_POST['allowfiletypes'])
 {
  $allowFileTypes = (strpos($_POST['allowfiletypes'],",") !== false) ? explode(",",$_POST['allowfiletypes']) : array($_POST['allowfiletypes']);
  if(in_array($fileExtension, $allowFileTypes))
   $retFalse = false;
 }

 if($retFalse)
 {
  ?><script>f.uploadResponse(false,'BAD_EXTENSION');</script><?php
  return false;
 }
}
if($_POST['basedir'])
 GShell("mkdir ".$_POST['basedir']);
echo "<script>";
if($_POST['abspath'])
{
 $dest_path = $_BASE_PATH.rtrim($_POST['abspath'],"/")."/$fileName";
 echo "f.FilePath = '".$_POST['abspath']."/';";
}
else
{
 $dest_path = $_BASE_PATH.$_USERS_HOMES.$_SESSION['HOMEDIR']."/".($_POST['basedir'] ? rtrim($_POST['basedir'],"/")."/" : "").$fileName;
 echo "f.FilePath = '".$_USERS_HOMES.$_SESSION['HOMEDIR']."/".($_POST['basedir'] ? rtrim($_POST['basedir'],"/")."/" : "")."';";
}
if(@move_uploaded_file($file['tmp_name'], $dest_path))
{
 @chmod($dest_path, 0755);
 echo "f.uploadResponse(true);";
}
else
 echo "f.uploadResponse(false, 'UNKNOWN_ERROR', 'Unable to move temporary file ".$file['tmp_name']." to ".str_replace($_BASE_PATH, "", $dest_path)."');";
echo "</script>";

