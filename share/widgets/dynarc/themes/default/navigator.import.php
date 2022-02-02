<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2015 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 02-05-2015
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Default theme for dynarc.navigator - Import form
 #VERSION: 2.1beta
 #CHANGELOG: 02-05-2015 : Bug fix.
			 05-11-2012 : Some bug fix.
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_ARCHIVE_PREFIX, $_ARCHIVE_INFO, $_USERS_HOMES, $_SESSION;

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/errors.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("dynarc");

if($_POST['action'] == "import")
{
 if($_POST['importfromserver'])
 {
  $file = array('name'=>$_POST['importfromserver']);
  $fileExt = strtolower(substr($file['name'], strrpos($file['name'], '.')+1));
  if(($fileExt != "xml") && ($fileExt != "zip"))
   return gform_error("<h4 style='color:#f31903;'>Invalid file type!</h4><p>You can upload only .xml or .zip files type.</p>".gnujiko_show_error("3.3-dynarc-1.1.1"));
 }
 else if((isset($_FILES['filename'])) and (is_array($_FILES['filename'])))
 {
  if($_FILES['filename']['error'] != 0)
  {
   $errCode = "";
   switch($_FILES['filename']['error'])
   {
	case UPLOAD_ERR_INI_SIZE: {$errCode = "1.1.1"; $msg = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';} break;
    case UPLOAD_ERR_FORM_SIZE: {$errCode = "1.1.2"; $msg = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';} break;
    case UPLOAD_ERR_PARTIAL: {$errCode = "1.1.3"; $msg = 'The uploaded file was only partially uploaded';} break;
    case UPLOAD_ERR_NO_FILE: {$errCode = "1.1.4"; $msg = 'No file was uploaded';} break;
    case UPLOAD_ERR_NO_TMP_DIR: {$errCode = "1.1.5"; $msg = 'Missing a temporary folder';} break;
    case UPLOAD_ERR_CANT_WRITE: {$errCode = "1.1.6"; $msg = 'Failed to write file to disk';} break;
    case UPLOAD_ERR_EXTENSION: {$errCode = "1.1.7"; $msg = 'File upload stopped by extension';} break;
    default: $msg = 'Unknown upload error'; break;
   }
   return gform_error("<h4 style='color:#f31903;'>Upload problem!</h4><p>$msg</p>".gnujiko_show_error($errCode));
  }
  $file = $_FILES['filename'];
  $fileExt = strtolower(substr($file['name'], strrpos($file['name'], '.')+1));
  if(($fileExt != "xml") && ($fileExt != "zip"))
   return gform_error("<h4 style='color:#f31903;'>Invalid file type!</h4><p>You can upload only .xml or .zip files type.</p>".gnujiko_show_error("3.3-dynarc-1.1.1"));
  $dest_path = $_BASE_PATH.$_USERS_HOMES.$_SESSION['HOMEDIR']."/".$file['name'];
  if(@move_uploaded_file($file['tmp_name'], $dest_path))
  {
   @chmod($dest_path, 0755);
  }
  else
   return gform_error("<h4 style='color:#f31903;'>Upload problem!</h4><p>Unable to move the uploaded file ".$file['name']." into folder "
	.$_USERS_HOMES.$_SESSION['HOMEDIR']."/"."<br/>Please contact the System Administrator.</p>");
 }
 else
  return gform_error("<h4 style='color:#f31903;'>Invalid file!</h4>");

 /* IMPORT PROCESS */
  if($_POST['ap'])
   $_ARCHIVE_PREFIX = $_POST['ap'];
  if($_POST['catid'])
	$q = " -cat `".$_POST['catid']."`";
  ?>
   <html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $_ARCHIVE_INFO['name']; ?> - Import process</title>
   <?php
   include_once($_BASE_PATH."var/objects/gform/index.php");
   include_once($_BASE_PATH."include/js/gshell.php");
   include_once($_BASE_PATH."var/objects/progressbar/index.php");
   $form = new GForm(i18n('Import in progress'), "MB_ABORT", "simpleform", "default", "orange", 420, 180);
   $form->Begin($_ABSOLUTE_URL."share/widgets/dynarc/themes/default/img/import.png");
   ?>
	<table width='100%' border='0'>
	<tr><td valign='top'><b style='font-family:Arial;font-size:18px;color:#333333' id='title'><?php echo i18n('Import in progress...wait!'); ?></b></td>
		<td align='right'><b style='font-family:Arial;font-size:18px;color:#333333' id='percentage'>0%</b></td></tr>
	<tr><td colspan="2"><?php
	$pb = new ProgressBar();
	$pb->Paint();
	?></td></tr>
	<tr><td colspan="2" style="font-family:Arial;font-size:12px;" id='message'>&nbsp;</td></tr>
	</table>
   <?php
   $form->End();
   ?>
   <script>
	var bar = new ProgressBar();
	var steps = 1;
	var step = 0;

	function bodyOnLoad()
	{
	 var sh = new GShell();
	 sh.OnError = function(err){alert(err);}
	 sh.OnPreOutput = function(o,a, msgType, msgRef){
		 switch(msgType)
		 {
		  case 'ESTIMATION' : steps = a['estimated_elements']; break;
		  case 'PROGRESS' : {
			 document.getElementById('message').innerHTML = o;
			 bar.setValue(bar.value + (100/steps));
			 document.getElementById('percentage').innerHTML = bar.value+"%";
			} break;
		 }
		}
	 sh.OnFinish = function(o,a){
		 bar.setValue(100);
		 document.getElementById('percentage').innerHTML = "100%";
		 document.getElementById('title').innerHTML = "<?php echo i18n('Import complete!'); ?>";
		 document.getElementById('message').innerHTML = "<?php echo i18n('OK! You can now close this window.'); ?>";
		 document.getElementById('btn_abort').value = "<?php echo i18n('Close'); ?>";
		}
	 sh.sendCommand("dynarc import -ap `<?php echo $_ARCHIVE_PREFIX; ?>`<?php echo $q; ?> -f `<?php echo $file['name']; ?>`");
	}
   </script>
   </body></html>
 <?php
 return;
}

//-------------------------------------------------------------------------------------------------------------------//
?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $_ARCHIVE_INFO['name']; ?> - Import</title>
<?php
include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."include/js/gshell.php");
?></head><body><?php


$form = new GForm(i18n('Import from file'), "MB_OK|MB_ABORT", "simpleform", "default", "blue", 420, 180);
$form->Begin($_ABSOLUTE_URL."share/widgets/dynarc/themes/default/img/import.png", $_ABSOLUTE_URL."share/widgets/dynarc.import.php", "multipart/form-data");
?>
<input type='hidden' name='sessid' value="<?php echo $_REQUEST['sessid']; ?>"/>
<input type='hidden' name='shellid' value="<?php echo $_REQUEST['shellid']; ?>"/>
<input type='hidden' name='action' value='import'/>
<input type='hidden' name='theme' value='default'/>
<input type='hidden' name='ap' value="<?php echo $_ARCHIVE_PREFIX; ?>"/>
<input type='hidden' name='catid' value="<?php echo $_REQUEST['cat']; ?>"/>
<input id='ifs' type='hidden' name='importfromserver' value=""/>

<p><?php echo i18n('Upload a file'); ?>: <input type='file' size='20' name='filename' accept='text/xml,application/zip'/></p>
<p><?php echo i18n('Or you can select the file directly from the server'); ?>: <input type='button' value="<?php echo i18n('Open...'); ?>" onclick='_selectFromServer()'/></p>
<?php
$form->End();
?>
<script>
function _selectFromServer()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.getElementById('ifs').value = a['url'];
	 document.forms[0].submit();
	}
 sh.sendCommand("gframe -f filemanager");
}

function WidgetOnSubmit(){return true;}
</script>

</body></html>
<?php
//-------------------------------------------------------------------------------------------------------------------//
function gform_error($msg)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_ARCHIVE_INFO;
 ?>
 <html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $_ARCHIVE_INFO['name']; ?> - <?php echo i18n('Import error'); ?></title>
 <?php
 include_once($_BASE_PATH."var/objects/gform/index.php");
 $form = new GForm(i18n('Import error'), "MB_CLOSE", "simpleform", "default", "orange", 420, 300);
 $form->Begin($_ABSOLUTE_URL."share/widgets/dynarc/themes/default/img/error.gif");
 echo $msg;
 $form->End();
 ?>
 <script>
 function bodyOnLoad()
 {
  gframe_resize(420,300);
 }
 </script>
 </body></html>
 <?php
}
//-------------------------------------------------------------------------------------------------------------------//


