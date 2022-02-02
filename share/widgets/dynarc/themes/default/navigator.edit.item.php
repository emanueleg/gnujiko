<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 14-01-2012
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Default theme for dynarc.navigator - Edit item form
 #VERSION: 2.0beta
 #CHANGELOG: 06-09-2011 : Sistemazioni varie
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_ARCHIVE_PREFIX, $_ARCHIVE_INFO, $_ITEM_INFO, $_PARENT_INFO, $_PATHWAY;

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/layers.php");
include($_BASE_PATH."var/objects/fckeditor/index.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("dynarc");

$archiveInfo = $_ARCHIVE_INFO;
$itemInfo = $_ITEM_INFO;
$parentInfo = $_PARENT_INFO;

/* get archive icon */
if(file_exists($_BASE_PATH."share/widgets/dynarc/img/archive_icons/".$archiveInfo['prefix'].".png"))
 $archiveIcon = "share/widgets/dynarc/img/archive_icons/".$archiveInfo['prefix'].".png";
else
 $archiveIcon = "share/widgets/dynarc/img/archive_icons/default.png";

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Gnujiko - <?php echo $archiveInfo['name']; ?></title>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/dynarc/themes/default/css/navigator.edit.item.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/objects/gtabmenu/simple-blue.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/objects/gtabmenu/gtabmenu.js" type="text/javascript"></script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/dynarc/themes/default/js/navigator.edit.item.js" type="text/javascript"></script>
<?php
/* load config */
if(file_exists($_BASE_PATH."share/widgets/dynarc/themes/default/config/__".$archiveInfo['prefix']."/navigator.edit.item.php"))
 include_once($_BASE_PATH."share/widgets/dynarc/themes/default/config/__".$archiveInfo['prefix']."/navigator.edit.item.php");
else if(file_exists($_BASE_PATH."share/widgets/dynarc/themes/default/config/default/navigator.edit.item.php"))
 include_once($_BASE_PATH."share/widgets/dynarc/themes/default/config/default/navigator.edit.item.php");

/* load plugins */
$plugins = array();
for($c=0; $c < count($archiveInfo['extensions']); $c++)
{
 if(file_exists($_BASE_PATH."etc/dynarc/plugins/".$archiveInfo['extensions'][$c]."/dynarc.navigator.edit.sheet.php"))
 {
  include_once($_BASE_PATH."etc/dynarc/plugins/".$archiveInfo['extensions'][$c]."/dynarc.navigator.edit.sheet.php");
  $plugins[] = $archiveInfo['extensions'][$c];
 }
}

/* item informations */
if(file_exists($_BASE_PATH."etc/dynarc/plugins/item-informations/dynarc.navigator.edit.sheet.php"))
 include_once($_BASE_PATH."etc/dynarc/plugins/item-informations/dynarc.navigator.edit.sheet.php");

?>
</head><body>

<div class="widget">
<table class='header' width='100%' border='0' cellspacing='0' cellpadding='0'>
<tr><td width='46' valign='middle' rowspan='2'><img src="<?php echo $_ABSOLUTE_URL.$archiveIcon; ?>"/></td>
	<td valign='middle'><span id='title' onclick='_rename()'><?php echo $itemInfo['name']; ?></span></td>
	<td valign='middle' align='right'><?php
	echo isset($itemInfo['code_num']) ? "<span id='code' onclick='_editCode()'>".($itemInfo['code_str'] ? $itemInfo['code_str'] : "---")."</span>" : "&nbsp;";
	?></td></tr>
<tr><td colspan='2'><?php echo i18n('Category'); ?>: <input type="text" id="catpath" size='20' readonly value="<?php echo $parentInfo['name']; ?>" title="<?php echo $_PATHWAY; ?>"/> <input type='button' value="<?php echo i18n('Select'); ?>" onclick='selectCategory()'/><input type='hidden' name='catid' id='catid' value="<?php echo $parentInfo['id']; ?>"/></td></tr>
<tr><td colspan='3' align='right'><ul class='simple-blue' id='menu'>
		<li class='selected'><span onclick="Navigator.showPage('generality')"><?php echo i18n('Generality'); ?></span></li>
		<?php
		/* extra (from config) */
		if(is_callable("dynarc_edititem_extra_iteminfo_injectTab",false))
		{
		 echo "<li class='next'>";
		 echo call_user_func("dynarc_edititem_extra_iteminfo_injectTab");
		 echo "</li>";
		}
		for($c=0; $c < count($plugins); $c++)
		{
		 $ext = $plugins[$c];
		 if(is_callable("dynarc_edititem_plugin_".$ext."_injectTab",true))
		 {
		  echo "<li class='next'>";
		  echo call_user_func("dynarc_edititem_plugin_".$ext."_injectTab");
		  echo "</li>";
		 }
		}
		/* item informations */
		if(is_callable("dynarc_edititem_plugin_iteminfo_injectTab",true))
		{
		 echo "<li class='last'>";
		 echo call_user_func("dynarc_edititem_plugin_iteminfo_injectTab");
		 echo "</li>";
		}
		?>
		</ul>
	</td></tr>
</table>
<div class='contents'>

<!-- GENERALITY PAGE -->
<div id='generality'><small id="modifybuttons"><a href="#" onclick="editContents();"><?php echo i18n('edit'); ?></a> | <a href="#" onclick="editContents(true);"><?php echo i18n('modify with advanced editor'); ?></a><br/></small>
<div id="contents-html" style="height:480px;width:794px;overflow:auto;" class="commoncontents"><?php echo $itemInfo['desc']; ?></div>
<textarea style="width:100%;height:480px;display:none;" id="contents" class="commoncontents"></textarea>
</div>

<?php
/* extra page */
if(is_callable("dynarc_edititem_extra_iteminfo_pageContents",false))
 echo call_user_func("dynarc_edititem_extra_iteminfo_pageContents");
/* plugins */
for($c=0; $c < count($plugins); $c++)
{
 $ext = $plugins[$c];
 if(is_callable("dynarc_edititem_plugin_".$ext."_pageContents",true))
  echo call_user_func("dynarc_edititem_plugin_".$ext."_pageContents");
}
/* item informations page */
if(is_callable("dynarc_edititem_plugin_iteminfo_pageContents",true))
 echo call_user_func("dynarc_edititem_plugin_iteminfo_pageContents");

?>
</div>

</div>
<div class='widget-footer'>
<table width='100%' border='0'><tr><td style="font-family: Arial;font-size: 12px;">
<?php echo i18n('Keywords'); ?>: <input type='text' size='20' id='keywords' name='keywords' value="<?php echo $itemInfo['keywords']; ?>"/></td><td>
<ul class='simple-blue-buttons'>
	<li><span onclick="widget_documents_submit()"><?php echo i18n('Save'); ?></span></li>
	<li><span onclick="widget_documents_submit(true)"><?php echo i18n('Save as model'); ?></span></li>
	<li><span onclick="widget_documents_close()"><?php echo i18n('Abort'); ?></span></li>
	<li><span onclick="widget_documents_delete()" style="color:#f31903"><?php echo i18n('Delete'); ?></span></li>
</ul></td></tr></table>
</div>

<script>
var editorIsLoaded=true;
var editorMode=0;
var ARCH_EXTENSIONS = "<?php echo implode(',',$archiveInfo['extensions']); ?>";

var sSkinPath = "<?php echo $_ABSOLUTE_URL; ?>var/objects/fckeditor/editor/skins/office2003/";
var oFCKeditor = null;
oFCKeditor = new FCKeditor('contents') ;
oFCKeditor.BasePath	= "<?php echo $_BASE_PATH; ?>var/objects/fckeditor/";
oFCKeditor.Config['SkinPath'] = sSkinPath ;
oFCKeditor.Config['PreloadImages'] =
				sSkinPath + 'images/toolbar.start.gif' + ';' +
				sSkinPath + 'images/toolbar.end.gif' + ';' +
				sSkinPath + 'images/toolbar.bg.gif' + ';' +
				sSkinPath + 'images/toolbar.buttonarrow.gif' ;
oFCKeditor.Height = 500;

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

function widget_documents_submit(saveAsModel)
{
 if(!editorIsLoaded)
  return alert("<?php echo i18n('You have to wait for the editor to finish loading'); ?>");
 var title = document.getElementById('title').innerHTML;
 var catId = document.getElementById('catid').value;
 var keywords = document.getElementById('keywords').value;

 switch(editorMode)
 {
  case 0 : var contents = document.getElementById('contents-html').innerHTML; break;
  case 1 : var contents = document.getElementById('contents').value; break;
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
 if(PLUGINS_FUNCTIONS['iteminfo'] && PLUGINS_FUNCTIONS['iteminfo'].save)
  PLUGINS_FUNCTIONS['iteminfo'].save(xsArgs,args);


 var xArgs = "";
 for(var c=0; c < xsArgs.length; c++)
  xArgs+= ","+xsArgs[c];

 var argsS = "";
 if(document.getElementById('code') && (document.getElementById('code').innerHTML != "---"))
  argsS = " -code-str `"+document.getElementById('code').innerHTML+"`";
 for(var c=0; c < args.length; c++)
  argsS+= " "+args[c];

 var sh = new GShell();
 if(saveAsModel)
 {
  var nm = prompt("<?php echo i18n('Specify the name for the model'); ?>",title);
  if(!nm)
   return;
  sh.OnOutput = function(o,a){gframe_close(o,a);}
  sh.OnPreOutput = function(){} /* Enable pre-output for some interfaces */
  sh.OnError = function(e,s){alert(s);}
  sh.sendCommand("dynarc new-item -ap documentmodels -name `"+nm+"` -desc `"+contents+"` -keywords `"+htmlentities(keywords,"ENT_QUOTES")+"`"); 
 }
 else
 {
  sh.OnError = function(e,s){
	 var sh2 = new GShell();
	 sh2.sendCommand("gframe -f error -t `<?php echo i18n('Unable to save the document.'); ?>` -c '"+s+"'");
	}
  sh.OnOutput = function(o,a) {gframe_close(o,a);}
  sh.OnPreOutput = function(){} /* Enable pre-output for some interfaces */
  sh.sendCommand("dynarc edit-item -ap `<?php echo $archiveInfo['prefix']; ?>` -id <?php echo $itemInfo['id']; ?> -name `"+title+"`"+(editorMode ? " -desc `"+contents+"`" : "")+(catId ? " -cat "+catId : "")+" -keywords `"+keywords+"`"+(argsS ? argsS.substr(1) : "")+(xArgs ? " -extset `"+xArgs.substr(1)+"`" : ""));
 }
}

function widget_documents_close()
{
 gframe_close();
}

function _rename()
{
 var t = document.getElementById('title');
 var nm = prompt("<?php echo i18n('Rename document'); ?>",t.innerHTML);
 if(!nm)
  return;
 t.innerHTML = nm;
}

function widget_documents_delete()
{
 if(!confirm("<?php echo i18n('Are you sure you want to discard this document?'); ?>"))
  return;
 var sh = new GShell();
 sh.OnOutput = function(o,list){
	 if(!list) return;
	 var a = list['trashed'][0];
	 a['trashed'] = true;
	 gframe_close(o,a);
	}
 sh.sendCommand("dynarc delete-item -ap `<?php echo $archiveInfo['prefix']; ?>` -id <?php echo $itemInfo['id']; ?> --return-item-info");
}

function _editCode()
{
 var code = document.getElementById('code');
 var nm = prompt("<?php echo i18n('Assign a code to this article'); ?>",(code.innerHTML != "---") ? code.innerHTML : "");
 if(!nm)
  return;
 code.innerHTML = nm;
}

</script>
</body></html>
<?php

