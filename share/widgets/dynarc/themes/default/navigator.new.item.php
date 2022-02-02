<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 04-08-2012
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Default theme for dynarc.navigator - New item form
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_ARCHIVE_PREFIX, $_ARCHIVE_INFO, $_CAT_INFO;

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("dynarc");


?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $_ARCHIVE_INFO['name']; ?> - New</title>
<?php
include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."include/js/gshell.php");
?></head><body><?php

if($_POST['action'] == 'newitem')
{
 $title = gshSecureString($_POST['title']);
 $archivePrefix = $_POST['archiveprefix'];
 $catId = $_POST['catid'] ? $_POST['catid'] : 0;
 $model = $_POST['model'] ? $_POST['model'] : 0;
 $alias = $_POST['alias'] ? $_POST['alias'] : "";
 $contents = "";

 if($model)
 {
  $ret = GShell("dynarc item-info -ap documentmodels -id '".$model."'");
  if(!$ret['error'])
   $contents = $ret['outarr']['desc'];
 }

 $ret = GShell("dynarc new-item -ap `".$archivePrefix."`".($catId ? " -cat '$catId'" : "")." -name `".$title."` -desc `$contents`".($alias ? " -alias `".$alias."`" : ""));
 if(!$ret['error'])
 {
  $itm = $ret['outarr'];
  $id = $itm['id'];
  $args = "{\"id\":\"$id\",\"catid\":\"$catId\",\"name\":\"$title\",\"aliasname\":\"$alias\"}";
  ?>
  <script>
  function bodyOnLoad()
  {
   CloseWidget("New item added",<?php echo $args; ?>);
  }
  </script>
  <?php
 }
 else
 {
  $form = new GForm(i18n('Error'), "MB_CLOSE", "simpleform", "default", "orange", 300);
  $form->Begin();
  echo "<h3 style='color:#f31903;'>".i18n('Unable to save the document.')."</h3>".$ret['message'];
  $form->End();
 }
 return;
}

/* get archive icon */
if(file_exists($_BASE_PATH."share/widgets/dynarc/img/archive_icons/".$_ARCHIVE_INFO['prefix'].".png"))
 $archiveIcon = "share/widgets/dynarc/img/archive_icons/".$_ARCHIVE_INFO['prefix'].".png";
else
 $archiveIcon = "share/widgets/dynarc/img/archive_icons/default.png";

$form = new GForm(i18n('New document'), "MB_OK|MB_ABORT", "simpleform", "default", "orange", 480, 250);
$form->Begin($_ABSOLUTE_URL.$archiveIcon, $_ABSOLUTE_URL."share/widgets/dynarc.newitem.php");
?>
<input type='hidden' name='action' value='newitem'/>
<input type='hidden' name='archiveprefix' value="<?php echo $_ARCHIVE_PREFIX; ?>"/>
<input type='hidden' name='catid' value="<?php echo $_CAT_INFO['id']; ?>"/>
<input type='hidden' name='model' id='modelid' value="0"/>
<input type='hidden' name='sessid' value="<?php echo $_REQUEST['sessid']; ?>"/>
<input type='hidden' name='shellid' value="<?php echo $_REQUEST['shellid']; ?>"/>

<h3><?php echo i18n('Create new document.'); ?></h3>
<div style='padding-left:10px;padding-bottom:10px;'>
 <p><?php echo i18n('Title'); ?>: <input style='width:260px;' type='text' size='40' id='title' name='title' /></p>
 <p><?php echo i18n('Copy from model'); ?>: <input type="text" id="model" readonly value=""/> <input type='button' value="<?php echo i18n('Select'); ?>" onclick='selectModel()'/></p>
 <?php 
 if($_ARCHIVE_INFO['type'] == "document")
 {
  ?>
  <p>Alias: <input type='text' size='30' id='alias' name='alias' /></p>
  <?php
 }
 ?>
</div>
<?php
$form->End();

?>
<script>
function bodyOnLoad()
{
 window.setTimeout(function(){document.getElementById('title').focus();},1000);
}

function widget_submit()
{
 if(!document.getElementById('title').value)
 {
  alert("<?php echo i18n('You must specify a valid title'); ?>");
  return false;
 }
 return true;
}

function selectModel()
{
 var sh = new GShell();
 sh.OnOutput = function(o,id){
	 if(!id)
	  return;
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){
		 if(!a) return;
		 document.getElementById('model').value = a['name'];
		}
	 sh2.sendCommand("dynarc item-info -ap documentmodels -id "+id);
	 document.getElementById('modelid').value = id;
	}
 sh.sendCommand("gframe -f dynarc.navigator -params `ap=documentmodels` --fullspace");
}
</script>

</body></html>

