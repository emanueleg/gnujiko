<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-11-2012
 #PACKAGE: filemanager
 #DESCRIPTION: Official Gnujiko File Manager
 #VERSION: 2.0beta
 #CHANGELOG: 05-11-2012 : Some bug fix.
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/errors.php");
include_once($_BASE_PATH."include/i18n.php");
LoadLanguage("filemanager");

$sessInfo = sessionInfo($_REQUEST['sessid']);

if($_POST['action'] == "upload")
{
 if((isset($_FILES['filename'])) and (is_array($_FILES['filename'])))
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
  $dest_path = $_BASE_PATH.($sessInfo['uname'] == "root" ? "" : $_USERS_HOMES.$_SESSION['HOMEDIR'])."/".($_POST['path'] ? rtrim($_POST['path'],"/")."/" : "").$file['name'];
  if(@move_uploaded_file($file['tmp_name'], $dest_path))
   @chmod($dest_path, 0755);
  else
   return gform_error("<h4 style='color:#f31903;'>Upload problem!</h4><p>Unable to move the uploaded file ".$file['name']." into folder "
	.($sessInfo['uname'] == "root" ? "" : $_USERS_HOMES.$_SESSION['HOMEDIR'])."/".($_POST['path'] ? rtrim($_POST['path'],"/")."/" : "")."<br/>Please contact the System Administrator.</p>");
 }
 else
  return gform_error("<h4 style='color:#f31903;'>Invalid file!</h4>");
}
//-------------------------------------------------------------------------------------------------------------------//
function gform_error($msg)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_ARCHIVE_INFO;
 ?>
 <html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>File Manager - Upload</title>
 <?php
 include_once($_BASE_PATH."var/objects/gform/index.php");
 $form = new GForm(i18n('Error loading file'), "MB_CLOSE", "simpleform", "default", "orange", 420, 200);
 $form->Begin($_ABSOLUTE_URL."share/widgets/dynarc/themes/default/img/error.gif");
 echo $msg;
 $form->End();
 ?>
 <script>
 function CloseWidget(o,a)
 {
  history.go(-1);
 }
 </script>
 </body></html>
 <?php
}
//-------------------------------------------------------------------------------------------------------------------//

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Gnujiko - File Manager</title>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/filemanager/list.css" type="text/css" />
</head><body>
<?php
include($_BASE_PATH."var/objects/htmlgutility/menu.php");
include($_BASE_PATH."var/objects/dyntable/dyntable.php");

function fullescape($in)
{
 /*Thanks to omid@omidsakhi.com that his code gave me an idea. */
 /* Full escape function without % sign */
  $out = '';
  for ($i=0;$i<strlen($in);$i++)
  {
    $hex = dechex(ord($in[$i]));
    if ($hex=='')
       $out = $out.urlencode($in[$i]);
    else
       $out = $out.((strlen($hex)==1) ? ('0'.strtoupper($hex)):(strtoupper($hex)));
  }
  $out = str_replace('+','20',$out);
  $out = str_replace('_','5F',$out);
  $out = str_replace('.','2E',$out);
  $out = str_replace('-','2D',$out);
  return $out;
}

$ret = GShell("ls".($_REQUEST['path'] ? " `".$_REQUEST['path']."`" : ""), $_REQUEST['sessid'], $_REQUEST['shellid']);
$dirs = $ret['outarr']['dirs'];
$files = $ret['outarr']['files'];

?>
<div id='buttonbar'><table width='100%' border='0' cellspacing='0' cellpadding='0'><tr>
		<td>
		<ul class="menu" id="mainmenu">
		 <li><?php echo i18n('Actions'); ?>
			<ul class="submenu">
			 <li onclick="_newDir()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/new_folder.png"/><?php echo i18n('New folder'); ?></li>
			</ul></li>
		 <li><?php echo i18n('Edit'); ?>
			<ul class="submenu">
			 <li onclick="_actionCopy()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/copy.png"/><?php echo i18n('Copy'); ?></li>
			 <li onclick="_actionCut()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/cut.gif"/><?php echo i18n('Cut'); ?></li>
			 <li onclick="_actionPaste()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/paste.gif"/><?php echo i18n('Paste'); ?></li>
			 <li class="separator">&nbsp;</li>
			 <li onclick="_deleteSelected()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/><?php echo i18n('Delete selected'); ?></li>
			</ul></li>
		</ul></td>
		<td width='300'>
		<form method="post" enctype="multipart/form-data">
		<input type='hidden' name='sessid' value="<?php echo $_REQUEST['sessid']; ?>"/>
		<input type='hidden' name='shellid' value="<?php echo $_REQUEST['shellid']; ?>"/>
		<input type='hidden' name='action' value='upload'/>
		<input type='hidden' name='path' value="<?php echo $_REQUEST['path']; ?>"/>
		<small><?php echo i18n('Upload'); ?>:</small> <input type='file' size='10' name='filename'/ onchange='_autoUpload(this)'>
		</form>
		</td></tr></table>
</div>

<div id='results'><table width='100%' border='0'><tr>
		<td><span id='pagenum'><?php if(count($dirs)) echo count($dirs)." cartelle"; if(count($files)) echo (count($dirs) ? " e " : "").count($files)." files"?></td>
		<td><div id='ordering'><b><?php echo i18n('In order of'); ?>:</b> 
				[ <a href='#' id='orderby_name' onclick='_orderby(this)'><?php echo i18n('name'); ?><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/filemanager/img/darrow.png" border='0' style='margin-left:4px;' id='orderby_arrow'/></a> ]
				[ <a href='#' id='orderby_date' onclick='_orderby(this)'><?php echo i18n('date'); ?></a> ]
				[ <a href='#' id='orderby_size' onclick='_orderby(this)'><?php echo i18n('size'); ?></a> ]</div></td>
		<td><div id='GSERPPAGENAVSPACE'></div></td></tr></table>
	</div>
<div class='resultcontainer'>
<table width='100%' id='resultstable' class='dyntable' cellspacing='0' cellpadding='0' border='0'>
<tr>
 <th width='32'>&nbsp;</th>
 <th id='column-name'>&nbsp;</th>
 <th width='110' id='column-date'><small><?php echo i18n('Date'); ?></small></th>
 <th width='80' id='column-size'><small><?php echo i18n('Size'); ?></small></th>
 <th width='50' id='column-buttons-edit'>&nbsp;</th>
</tr>

<?php
for($c=0; $c < count($ret['outarr']['dirs']); $c++)
{
 $dir = $ret['outarr']['dirs'][$c];
 echo "<tr id='".fullescape($dir['path'])."' isdir='true'>";
 echo "<td><img src='".$_ABSOLUTE_URL."share/widgets/filemanager/img/folder.png'/></td>";
 echo "<td><a href='#' onclick='_openDir(this)'>".$dir['name']."</a></td>";
 echo "<td>&nbsp;</td>";
 echo "<td>&nbsp;</td>";
 echo "<td><a href='#' onclick='_renameDir(this,\"".fullescape($dir['name'])."\")'><img src='".$_ABSOLUTE_URL."share/widgets/filemanager/img/edit.gif' border='0'/></a> <a href='#' onclick='_deleteDir(this,\"".fullescape($dir['name'])."\")'><img src='".$_ABSOLUTE_URL."share/widgets/filemanager/img/delete.gif' border='0'/></a></td>";
 echo "</tr>";
}

if($_REQUEST['filter'])
{
 $filters = explode(",",strtolower($_REQUEST['filter']));
}

$imageTypes = array('png','jpg','gif','bmp');

for($c=0; $c < count($ret['outarr']['files']); $c++)
{
 $file = $ret['outarr']['files'][$c];
 $type = "";
 /* detect type */
 $ext = strtolower(substr($file['name'], strrpos($file['name'], '.')+1));
 if($filters && !in_array($ext, $filters))
  continue;
 echo "<tr id='".fullescape($file['path'])."'>";
 if(file_exists($_BASE_PATH."etc/mimetypes.php"))
 {
  include_once($_BASE_PATH."etc/mimetypes.php");
  if($mimetypes[$ext])
   $type = $mimetypes[$ext];
 }
 // detect mimetype icon //
 if(file_exists($_BASE_PATH."etc/mimetypes.php"))
 {
  include_once($_BASE_PATH."etc/mimetypes.php");
  $icon = getMimetypeIcons($type);
 }
 // show mimetype icon //
 
 if(in_array($ext,$imageTypes))
  echo "<td><img src='".$_ABSOLUTE_URL.($sessInfo['uname'] == "root" ? "" : $_USERS_HOMES.$_SESSION['HOMEDIR'])."/".$file['path']."' height='22' style='margin-top:2px;' valign='top' align='left'/></td>";
 else if($icon && $icon['size22x22'])
  echo "<td><img src='".$_ABSOLUTE_URL.$icon['size22x22']."' style='margin-top:2px;' valign='top' align='left'/></td>";
 else
  echo "<td><img src='".$_ABSOLUTE_URL."share/mimetypes/22x22/file.png' style='margin-top:2px;' valign='top' align='left'/>";
 echo "<td><a href='#' onclick='_selectFile(this)' style='line-height:1.5em;'>".$file['name']."</a></td>";
 echo "<td>".($file['mtime'] ? date('d/m/Y H:i',$file['mtime']) : "&nbsp;")."</td>";
 echo "<td align='right'>".$file['humansize']."</td>";
 echo "<td><a href='#' onclick='_renameFile(this,\"".fullescape($file['name'])."\")'><img src='".$_ABSOLUTE_URL."share/widgets/filemanager/img/edit.gif' border='0'/></a> <a href='#' onclick='_deleteFile(this,\"".fullescape($file['name'])."\")'><img src='".$_ABSOLUTE_URL."share/widgets/filemanager/img/delete.gif' border='0'/></a></td>";
 echo "</tr>\n";
}
?>
</table>
<?php
if(!count($ret['outarr']['dirs']) && !count($ret['outarr']['files']))
 echo "<h4 align='center' style='font-family:Arial;color:#333333;'>".i18n('This folder is empty')."</h4>";
?>
</div>
<script>
var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";
var FILTER = "<?php echo $_REQUEST['filter']; ?>";
var TB = new DynTable(document.getElementById('resultstable'),{selectable:true});
var MainMenu = new GMenu(document.getElementById('mainmenu'));

function _autoUpload(inp)
{
 if(inp.value)
  document.forms[0].submit();
}

function _newDir()
{
 if(window.parent && window.parent._newDir)
  window.parent._newDir();
}

function _openDir(a)
{
 var fid = a.parentNode.parentNode.id;
 var path = decodeFID(fid);
 if(window.parent && window.parent._selectDir)
  window.parent._selectDir(path,fid);
 else
  document.location.href="?sessid=<?php echo $_REQUEST['sessid']; ?>&shellid=<?php echo $_REQUEST['shellid']; ?>&filter="+FILTER+"&path="+urlencode(path);
}

function _renameDir(a,_name)
{
 var fid = a.parentNode.parentNode.id;
 if(window.parent && window.parent._renameDir)
  window.parent._renameDir(fid,_name);
}

function _deleteDir(a,_name)
{
 var fid = a.parentNode.parentNode.id;
 if(window.parent && window.parent._deleteDir)
  window.parent._deleteDir(fid,_name);
}

function _deleteFile(a,_name)
{
 var fid = a.parentNode.parentNode.id;
 if(window.parent && window.parent._deleteFile)
  window.parent._deleteFile(fid,_name);
}

function _deleteSelected()
{
 if(window.parent && window.parent._deleteSelected)
  window.parent._deleteSelected(TB.getSelectedRows());
}

function _selectFile(a)
{
 var fid = a.parentNode.parentNode.id;
 if(window.parent && window.parent._selectFile)
  window.parent._selectFile(fid,a.innerHTML);
 else
  document.location.href=ABSOLUTE_URL+"getfile.php?file="+decodeFID(fid);
}

function decodeFID(fid)
{
 var str = "";
 var p = 0;
 while(p < fid.length)
 {
  str+= "%"+fid.substr(p,2);
  p+= 2;
 }
 str = unescape(str);
 return str;
}

function urlencode(str) 
{
 str = escape(str);
 str = str.replace('+', '%2B');
 str = str.replace('%20', '+');
 str = str.replace('*', '%2A');
 str = str.replace('/', '%2F');
 str = str.replace('@', '%40');
 return str;
}

function _actionCopy()
{
 if(window.parent && window.parent._actionCopy)
  window.parent._actionCopy(TB.getSelectedRows());
}

function _actionCut()
{
 if(window.parent && window.parent._actionCut)
  window.parent._actionCut(TB.getSelectedRows());
}

function _actionPaste()
{
 if(window.parent && window.parent._actionPaste)
  window.parent._actionPaste();
}
</script>
</body></html>
<?php

