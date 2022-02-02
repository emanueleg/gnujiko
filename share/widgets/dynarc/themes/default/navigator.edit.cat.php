<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 03-11-2012
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Default theme for dynarc.navigator - Edit item form
 #VERSION: 2.0beta
 #CHANGELOG: 03-11-2012 : Bug fix.
			 20-04-2012 : Bug fix with special chars.
			 04-12-2011 : Inserito form per l'ordinamento predefinito
			 06-09-2011 : Sistemazioni varie.
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_ARCHIVE_PREFIX, $_ARCHIVE_INFO, $_CAT_INFO, $_PARENT_INFO, $_PATHWAY;

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/layers.php");
include($_BASE_PATH."var/objects/fckeditor/index.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("dynarc");


$archiveInfo = $_ARCHIVE_INFO;
$catInfo = $_CAT_INFO;

/* get archive icon */
if(file_exists($_BASE_PATH."share/widgets/dynarc/img/archive_icons/".$archiveInfo['prefix'].".png"))
 $archiveIcon = "share/widgets/dynarc/img/archive_icons/".$archiveInfo['prefix'].".png";
else
 $archiveIcon = "share/widgets/dynarc/img/archive_icons/default.png";

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Gnujiko - <?php echo $archiveInfo['name']; ?></title>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/dynarc/themes/default/css/navigator.edit.cat.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/dynarc/themes/default/js/navigator.edit.cat.js" type="text/javascript"></script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."var/objects/gtabmenu/index.php");

/* load config */
if(file_exists($_BASE_PATH."share/widgets/dynarc/themes/default/config/__".$archiveInfo['prefix']."/navigator.edit.cat.php"))
 include_once($_BASE_PATH."share/widgets/dynarc/themes/default/config/__".$archiveInfo['prefix']."/navigator.edit.cat.php");
else if(file_exists($_BASE_PATH."share/widgets/dynarc/themes/default/config/default/navigator.edit.cat.php"))
 include_once($_BASE_PATH."share/widgets/dynarc/themes/default/config/default/navigator.edit.cat.php");

/* load plugins */
$plugins = array();
for($c=0; $c < count($archiveInfo['extensions']); $c++)
{
 if(file_exists($_BASE_PATH."etc/dynarc/plugins/".$archiveInfo['extensions'][$c]."/dynarc.navigator.editcat.sheet.php"))
 {
  include_once($_BASE_PATH."etc/dynarc/plugins/".$archiveInfo['extensions'][$c]."/dynarc.navigator.editcat.sheet.php");
  $plugins[] = $archiveInfo['extensions'][$c];
 }
}

/* cat informations */
if(file_exists($_BASE_PATH."etc/dynarc/plugins/cat-informations/dynarc.navigator.editcat.sheet.php"))
 include_once($_BASE_PATH."etc/dynarc/plugins/cat-informations/dynarc.navigator.editcat.sheet.php");

?>
</head><body>
<?php
$form = new GForm(i18n('Category properties'), "MB_OK|MB_ABORT", "simpleform", "default", "blue", "680", "580");
$form->Begin($_ABSOLUTE_URL.$archiveIcon);
?>
<div class='tabbar'>
<table width='100%' border='0' cellspacing='0' cellpadding='0'>
<tr><td>&nbsp;</td><td align='right'>
<ul class='simple-blue' id='menu' style='margin-top:5px;margin-right:3px;position:relative;float:right;'>
		<li class='selected'><span onclick="Navigator.showPage('generality')"><?php echo i18n('Generality'); ?></span></li>
		<li class='next'><span onclick="Navigator.showPage('description')"><?php echo i18n('Description'); ?></span></li>
		<?php
		/* extra (from config) */
		if(is_callable("dynarc_editcat_extra_catinfo_injectTab",false))
		{
		 echo "<li class='next'>";
		 echo call_user_func("dynarc_editcat_extra_catinfo_injectTab");
		 echo "</li>";
		}
		for($c=0; $c < count($plugins); $c++)
		{
		 $ext = $plugins[$c];
		 if(is_callable("dynarc_editcat_plugin_".$ext."_injectTab",false))
		 {
		  echo "<li class='next'>";
		  echo call_user_func("dynarc_editcat_plugin_".$ext."_injectTab");
		  echo "</li>";
		 }
		}
		/* item informations */
		if(is_callable("dynarc_editcat_plugin_catinfo_injectTab",false))
		{
		 echo "<li class='last'>";
		 echo call_user_func("dynarc_editcat_plugin_catinfo_injectTab");
		 echo "</li>";
		}
		?>
		</ul></td></tr></table></div>

<div class='contents' style='height:360px;'>
<!-- GENERALITY PAGE -->
<div id='generality'>
 <table width='100%' border='0' class='contents-table'>
 <tr><td align='right' width='150'><b><?php echo i18n('Title'); ?>: </b></td>
	 <td align='left'><input type='text' size='20' id='title' value="<?php echo $catInfo['name']; ?>"/></td></tr>
 <?php
 if(isset($catInfo['code']))
  echo "<tr><td align='right'><b>".i18n('Code').": </b></td><td align='left'><input type='text' size='15' id='code' value='".$catInfo['code']."'/></td></tr>";
 ?>
 <tr><td align='right'><b><?php echo i18n('Parent cat.'); ?> </b></td>
	 <td align='left'><input type="text" id="catpath" size='20' readonly value="<?php echo $parentInfo['name']; ?>" title="<?php echo $_PATHWAY; ?>"/> <input type='button' value='Seleziona' onclick='selectCategory()'/><input type='hidden' name='catid' id='catid' value="<?php echo $parentInfo['id']; ?>"/></td></tr>
 <tr><td align='right'><b><?php echo i18n('Tag'); ?>: </b></td>
	 <td align='left'><input type='text' size='20' id='tag' value="<?php echo $catInfo['tag']; ?>"/></td></tr>
 <tr><td><br/></td><td><br/></td></tr>
 <tr><td align='right' valign='top'><b><?php echo i18n('Default ordering'); ?></b></td><td align='left' valign='top'><small><i>(<?php echo i18n('for all elements and sub​​-categories'); ?>)</i></small><br/>
	  <div id='deforderinglist'>
	  <input type='radio' name='defordering' value='' <?php if(!$catInfo['def_order_field']) echo "checked='true'"; ?>/> <?php echo i18n('Manual'); ?><br/>
	  <?php
	  $orderings = array('id'=>"ID",'name'=>"Nome",'ctime'=>i18n('Creation date'),'mtime'=>i18n('Last modified'));
	  while(list($k,$v) = each($orderings))
	   echo "<input type='radio' name='defordering' value='".$k."' "
		.(($catInfo['def_order_field'] == $k) ? "checked='true'/> " : "/> ").$v."<br/>";
	  ?>
	  </div>
	  <select id='defordermethod'>
		<option value='ASC' <?php if($catInfo['def_order_method'] == "ASC") echo "selected='selected'"; ?>>A-Z</option>
		<option value='DESC' <?php if($catInfo['def_order_method'] != "ASC") echo "selected='selected'"; ?>>Z-A</option>
	  </select>
	 </td></tr>
 </table>
</div>

<!-- DESCRIPTION PAGE -->
<div id='description' style='display:none;'>
<small id="modifybuttons"><a href="#" onclick="editContents();"><?php echo i18n('edit'); ?></a> | <a href="#" onclick="editContents(true);"><?php echo i18n('modify with advanced editor'); ?></a><br/></small>
<div id="contents-html" style="height:360px;width:588px;overflow:auto;" class="commoncontents"><?php echo $catInfo['desc']; ?></div>
<textarea style="width:100%;height:360px;display:none;" id="contents" class="commoncontents"><?php echo $catInfo['desc']; ?></textarea>
</div>

<?php
/* extra page */
if(is_callable("dynarc_editcat_extra_catinfo_pageContents",false))
 echo call_user_func("dynarc_editcat_extra_catinfo_pageContents");
/* plugins */
for($c=0; $c < count($plugins); $c++)
{
 $ext = $plugins[$c];
 if(is_callable("dynarc_editcat_plugin_".$ext."_pageContents",false))
  echo call_user_func("dynarc_editcat_plugin_".$ext."_pageContents");
}
/* category informations page */
if(is_callable("dynarc_editcat_plugin_catinfo_pageContents",false))
 echo call_user_func("dynarc_editcat_plugin_catinfo_pageContents");

?>
</div>

<?php
$form->End();
?>
<script>
var editorIsLoaded=true;
var editorMode=0;
var ARCH_EXTENSIONS = "<?php echo implode(',',$archiveInfo['extensions']); ?>";

var sSkinPath = "<?php echo $_ABSOLUTE_URL; ?>var/objects/fckeditor/editor/skins/office2003/";
var oFCKeditor = new FCKeditor('contents') ;
oFCKeditor.BasePath	= "<?php echo $_BASE_PATH; ?>var/objects/fckeditor/";
oFCKeditor.Config['SkinPath'] = sSkinPath ;
oFCKeditor.Config['PreloadImages'] =
				sSkinPath + 'images/toolbar.start.gif' + ';' +
				sSkinPath + 'images/toolbar.end.gif' + ';' +
				sSkinPath + 'images/toolbar.bg.gif' + ';' +
				sSkinPath + 'images/toolbar.buttonarrow.gif' ;
oFCKeditor.Height = 360;

var TabMenu = new GTabMenu(document.getElementById('menu'));

function editContents(bool)
{
 document.getElementById('contents-html').style.display='none';
 document.getElementById('contents').style.display='';

 if(bool)
 {
  document.getElementById('modifybuttons').parentNode.removeChild(document.getElementById('modifybuttons'));
  oFCKeditor.ReplaceTextarea();
  editorIsLoaded=false;
  editorMode = 2;
 }
 else
  editorMode = 1;
}

function FCKeditor_OnComplete( editorInstance )
{
 if(editorInstance.Name == "contents")
 {
  editorIsLoaded=true;
  editorInstance.SetHTML(document.getElementById('contents-html').innerHTML);
 }
}

function selectCategory()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a)
	  return;
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){
		 if(!a)
		  return;
		 document.getElementById('catpath').value = a['name'];
		 document.getElementById('catid').value = a['id'];
		 var path = "";
		 if(a['pathway'])
		 {
		  for(var c=0; c < a['pathway'].length; c++)
		   path+= a['pathway'][c]['name']+"/";
		 }
		 document.getElementById('catpath').title = path+a['name'];
		}
	 sh2.sendCommand("dynarc cat-info -ap `<?php echo $archiveInfo['prefix']; ?>` --include-path -id "+a);
	}
 sh.sendCommand("gframe -f dynarc.categorySelect -params ap=`<?php echo $archiveInfo['prefix']; ?>`");
}

function WidgetOnSubmit()
{
 if(!editorIsLoaded)
  return alert("<?php echo i18n('You have to wait for the editor to finish loading'); ?>");
 var title = document.getElementById('title').value;
 var catId = document.getElementById('catid').value;
 var tag = document.getElementById('tag').value;

 switch(editorMode)
 {
  case 0 : var contents = gshSecureString(document.getElementById('contents-html').innerHTML); break;
  case 1 : var contents = gshSecureString(document.getElementById('contents').value); break;
  case 2 : {
	 var oEditor = FCKeditorAPI.GetInstance('contents');
	 var contents = oEditor.GetXHTML();
	} break;
 }

 /* SAVE */
 var xsArgs = new Array();
 var args = new Array();

 var extensions = ARCH_EXTENSIONS.split(",");
 for(var c=0; c < extensions.length; c++)
 {
  if(PLUGINS_FUNCTIONS[extensions[c]] && PLUGINS_FUNCTIONS[extensions[c]].save)
	 PLUGINS_FUNCTIONS[extensions[c]].save(xsArgs,args);
 }
 if(PLUGINS_FUNCTIONS['extra'] && PLUGINS_FUNCTIONS['extra'].save)
  PLUGINS_FUNCTIONS['extra'].save(xsArgs,args);
 if(PLUGINS_FUNCTIONS['catinfo'] && PLUGINS_FUNCTIONS['catinfo'].save)
  PLUGINS_FUNCTIONS['catinfo'].save(xsArgs,args);


 var xArgs = "";
 for(var c=0; c < xsArgs.length; c++)
  xArgs+= ","+xsArgs[c];

 var argsS = "";
 if(document.getElementById('code'))
  argsS = " -code `"+document.getElementById('code').value+"`";
 /* SAVE DEFAULT ORDERING SETTINGS */
 var defolist = document.getElementById('deforderinglist');
 var list = defolist.getElementsByTagName('INPUT');
 for(var c=0; c < list.length; c++)
 {
  if(list[c].checked)
  {
   argsS+= " --def-order-field `"+list[c].value+"`";
   c = list.length;
  }
 }
 argsS+= " --def-order-method `"+document.getElementById('defordermethod').value+"`";

 for(var c=0; c < args.length; c++)
  argsS+= " "+args[c];

 var sh = new GShell();
 sh.OnError = function(e,s){
	 var sh2 = new GShell();
	 sh2.sendCommand("gframe -f error -t `<?php echo i18n('Unable to save the folder properties.'); ?>` -c '"+s+"'");
	}
 sh.OnOutput = function(o,a) {gframe_close(o,a);}
 sh.OnPreOutput = function(){} /* Enable pre-output for some interfaces */
 sh.sendCommand("dynarc edit-cat -ap `<?php echo $archiveInfo['prefix']; ?>` -id <?php echo $catInfo['id']; ?> -name `"+title+"` -tag `"+tag+"`"+(editorMode ? " -desc `"+contents+"`" : "")+(catId ? " -parent "+catId : "")+(argsS ? argsS : "")+(xArgs ? " -extset `"+xArgs.substr(1)+"`" : ""));
 return false;
}
</script>
</body></html>
<?php

