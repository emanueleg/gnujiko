<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 20-01-2012
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Dynarc category select form
 #VERSION: 2.0beta
 #CHANGELOG: 20-01-2012 : Language support.
			 18-01-2012 : Interaction with gframe.
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("dynarc");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo i18n("Dynarc - Select archive"); ?></title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."var/objects/jstree/index.php");
?></head><body><?php

$form = new GForm(i18n("Select category"), null, "simpleform", "default", "orange", 600, 350);
$form->Begin();

if($_REQUEST['ap'] || $_REQUEST['aid'] || $_REQUEST['archive'])
{
 $q = "";
 if($_REQUEST['ap']) $q = " -prefix '".$_REQUEST['ap']."'";
 else if($_REQUEST['aid']) $q = " -id '".$_REQUEST['aid']."'";
 $ret = GShell("dynarc archive-info".$q);
 if($ret['error'])
 {
  echo $ret['message'];
  return;
 }
 $archiveInfo = $ret['outarr'];
}
else
{
 echo "<h4 style='color:#f31903;'>Invalid archive</h4>";
 return;
}

/* CHECK TRASH STATUS */
$ret = GShell("dynarc trash list -ap ".$archiveInfo['prefix']);
$trash = $ret['outarr'];

?>

<style type='text/css'>
.demo {
	height:180px; 
	overflow: auto;
	margin:0; 
	font-family:Verdana; 
	font-size:10px; 
	background:white; 
}
input.minibutton {
	font-size: 10px;
	color: #3364C3;
}
span#trash img {
 vertical-align: middle;
}
div#categoryselectbar, div#categorytrashbar {
	font-family: Verdana;
	font-size:10px;
}
div#trash-list {
 height: 160px;
 overflow: auto;
 font-family:Verdana; 
 font-size:10px; 
}
div#trash-list td {
 font-size: 12px;
 font-family: Arial;
 border-bottom: 1px solid #aaccee;
 padding: 2px;
}
</style>

<script type="text/javascript" class="source">
$(function () { 
	$("#basic_html").tree({
	 callback : {
	  onmove : function(node,refnode,type,treeobj,rb){
		 var catId = $(node).attr('id');
		 var refId = $(refnode).attr('id');
		 var sh = new GShell();
		 sh.OnError = function(e,s){alert(s);}
		 switch(type)
		 {
		  case 'inside' : sh.sendCommand("dynarc cat-move -ap <?php echo $archiveInfo['prefix']; ?> -id "+catId+" -into "+refId); break;
		  case 'before' : sh.sendCommand("dynarc cat-move -ap <?php echo $archiveInfo['prefix']; ?> -id "+catId+" -before "+refId); break;
		  case 'after' : sh.sendCommand("dynarc cat-move -ap <?php echo $archiveInfo['prefix']; ?> -id "+catId+" -after "+refId); break;
		 }
		}
	 }
	});
});
</script>

<div id="categoryselectbar" style="width:540px;"><span style="background:#ffffff;color:#2e598d;font-size:14px;"><?php echo i18n('Categories'); ?> <?php echo $archiveInfo['name']; ?></span> <span style="margin-left:20px;<?php if(!count($trash['categories'])) echo 'display:none;'; ?>" id="trash"><img src='icons/orange-trash-icon.png'/> <a href='#' onclick='widget_categorySelect_trash()'><?php echo i18n('There are'); ?> <b id='trashed_cat_count'><?php echo count($trash['categories']); ?></b> <?php echo i18n('folders into trash'); ?></a></span></div>
<div id="categorytrashbar" style="display:none;"><span style="background:#ffffff;color:#2e598d;font-size:14px;"><img src='icons/orange-trash-icon.png' style='vertical-align:middle;'/> <?php echo i18n('Trash categories'); ?> <?php echo $archiveInfo['name']; ?></span> &nbsp;&nbsp;&nbsp;<a href='#' onclick='widget_categorySelect_list()'>&laquo; <?php echo i18n('back to list'); ?></a></div>


<div class="demo source" id="basic_html">
<ul>
<?php
$ret = GShell("dynarc cat-tree -ap ".$archiveInfo['prefix']);
$nodes = $ret['outarr'];
for($c=0; $c < count($nodes); $c++)
 jstree_recursiveInsert($nodes[$c]);
?>
</ul>
</div>

 <div id="trash-list" style="display:none;">
 <table border='0' cellspacing='0' cellpadding='0' width='90%' id='trash-table'>
 <tr><td width='20'>&nbsp;</td><td>&nbsp;</td></tr>
 <?php
 $nodes = $trash['categories'];
 for($c=0; $c < count($trash['categories']); $c++)
  echo "<tr><td><input type='checkbox' id='".$trash['categories'][$c]['id']."'/></td><td>".$trash['categories'][$c]['name']."</td></tr>";
 ?>
 </table>
 </div>

<div style="border-top: 1px solid #3364C3;padding-top:12px;" id="footer_bar">
<table width="100%" border="0"><tr><td><input type="button" class="minibutton" value="Crea..." onclick="widget_categorySelect_create()"/> <input type="button" class="minibutton" value="<?php echo i18n('Rename'); ?>" onclick="widget_categorySelect_rename()"/> <input type="button" class="minibutton" value="<?php echo i18n('Delete'); ?>" onclick="widget_categorySelect_delete()"/></td><td align="right"><input type="button" value="<?php echo i18n('Select'); ?>" onclick="selectCat()"/>&nbsp;<input type="button" value="<?php echo i18n('Abort'); ?>" onclick="widget_categorySelect_close()"/></td></tr>
</table>
</div>

<div style="border-top: 1px solid #3364C3;padding-top:12px;display:none;" id="footer_trashbar">
<input type="button" class="minibutton" value="<?php echo i18n('Restore'); ?>" onclick="widget_categorySelect_trashRestore()"/>&nbsp;
<input type="button" class="minibutton" value="<?php echo i18n('Delete'); ?>" onclick="widget_categorySelect_trashDelete()"/>&nbsp;
<input type="button" class="minibutton" value="<?php echo i18n('Empty trash'); ?>" onclick="widget_categorySelect_trashEmpty()"/>&nbsp;&nbsp;
</div>

<?php
$form->End();
?>
<script>
function selectCat()
{
 var mytree = jQuery.tree.reference("#basic_html");
 var catId = 0;
 if(mytree.selected)
  catId = $(mytree.selected).attr('id');
 gframe_close("done!",catId);
}

function widget_categorySelect_close()
{
 gframe_close();
}

function widget_categorySelect_create()
{
 var nm = prompt("<?php echo i18n('Enter the name of the new category'); ?>");
 if(!nm)
  return;
 nm = htmlentities(nm,"E_QUOT");
 var mytree = jQuery.tree.reference("#basic_html");
 var catId = 0;
 if(mytree.selected)
  catId = $(mytree.selected).attr('id');

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(mytree.selected)
	  mytree.create({ data: nm, attributes : { id : a['id'] }});
	 else
	  mytree.create({ data: nm, attributes : { id : a['id'] }},-1);
	}
 sh.sendCommand("dynarc new-cat -ap <?php echo $archiveInfo['prefix']; ?> -name '"+htmlentities(nm,"E_QUOT")+"'"+(catId ? " -parent "+catId : ""));
}

function widget_categorySelect_rename()
{
 var mytree = jQuery.tree.reference("#basic_html");
 if(!mytree.selected)
 {
  alert("<?php echo i18n('You must select a category'); ?>");
  return;
 }
 var nm = prompt("<?php echo i18n('Rename this category'); ?>", mytree.get_text($(mytree.selected)));
 if(!nm)
  return;

 var sh = new GShell();
 sh.OnOutput = function(o,a){$.tree.focused().rename(null,nm);}
 sh.OnError = function(e,s){alert(s);}
 sh.sendCommand("dynarc edit-cat -ap <?php echo $archiveInfo['prefix']; ?> -id "+$(mytree.selected).attr('id')+" -name '"+htmlentities(nm,"E_QUOT")+"'");
}

function widget_categorySelect_delete()
{
 var mytree = jQuery.tree.reference("#basic_html");
 if(!mytree.selected)
 {
  alert("<?php echo i18n('You must select a category'); ?>");
  return;
 }
 var msg = "<?php echo i18n('Are you sure you want to delete the category %s ?'); ?>";
 if(!confirm(msg.replace("%s",mytree.get_text($(mytree.selected)))))
  return;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 var tb = document.getElementById('trash-table');
	 var r = tb.insertRow(-1);
	 r.insertCell(-1).innerHTML = "<input type='checkbox' id='"+$(mytree.selected).attr('id')+"' checked='false'/ >";
	 r.insertCell(-1).innerHTML = mytree.get_text($(mytree.selected));

	 $.tree.focused().remove();
	 widget_categorySelect_updateTrashInfo();
	}
 sh.OnError = function(e,s){alert(s);}
 sh.sendCommand("dynarc delete-cat -ap <?php echo $archiveInfo['prefix']; ?> -id "+$(mytree.selected).attr('id'));
}

function widget_categorySelect_trash()
{
 document.getElementById('basic_html').style.display='none';
 document.getElementById('categoryselectbar').style.display='none';
 document.getElementById('footer_bar').style.display='none';

 document.getElementById('trash-list').style.display='';
 document.getElementById('categorytrashbar').style.display='';
 document.getElementById('footer_trashbar').style.display='';
}

function widget_categorySelect_list()
{
 document.getElementById('trash-list').style.display='none';
 document.getElementById('categorytrashbar').style.display='none';
 document.getElementById('footer_trashbar').style.display='none';

 document.getElementById('basic_html').style.display='';
 document.getElementById('categoryselectbar').style.display='';
 document.getElementById('footer_bar').style.display='';
}

function widget_categorySelect_trashRestore()
{
 var tb = document.getElementById('trash-table');
 var sel = new Array();
 var q = "";
 for(var c=1; c < tb.rows.length; c++)
 {
  if(tb.rows[c].getElementsByTagName('INPUT')[0].checked)
  {
   sel.push(tb.rows[c].getElementsByTagName('INPUT')[0].id);
   q+= " -cat "+tb.rows[c].getElementsByTagName('INPUT')[0].id;
  }
 }
 if(!sel.length)
 {
  alert("<?php echo i18n('You must select at least one category'); ?>");
  return;
 }
 
 var sh = new GShell();
 sh.OnError = function(e,s){alert(s);}
 sh.OnOutput = function(o,a){
	 document.location.reload();
	}
 sh.sendCommand("dynarc trash restore -ap <?php echo $archiveInfo['prefix']; ?>"+q);
}

function widget_categorySelect_trashDelete()
{
 var tb = document.getElementById('trash-table');
 var sel = new Array();
 var q = "";
 for(var c=1; c < tb.rows.length; c++)
 {
  if(tb.rows[c].getElementsByTagName('INPUT')[0].checked)
  {
   sel.push(tb.rows[c].getElementsByTagName('INPUT')[0].id);
   q+= " -cat "+tb.rows[c].getElementsByTagName('INPUT')[0].id;
  }
 }
 if(!sel.length)
 {
  alert("<?php echo i18n('You must select at least one category'); ?>");
  return;
 }
 
 var sh = new GShell();
 sh.OnError = function(e,s){alert(s);}
 sh.OnOutput = function(o,a){
	 if(!a || !a['categories'].length)
	  return;
	 for(var c=0; c < a['categories'].length; c++)
	 {
	  var obj = document.getElementById(a['categories']);
	  if(!obj)
	   continue;
	  tb.deleteRow(obj.parentNode.parentNode.rowIndex);
	 }
	 widget_categorySelect_updateTrashInfo();
	}
 sh.sendCommand("dynarc trash remove -ap <?php echo $archiveInfo['prefix']; ?>"+q);
}

function widget_categorySelect_trashEmpty()
{
 if(!confirm("<?php echo i18n('Are you sure you want to empty the trash?'); ?>"))
  return;
 var sh = new GShell();
 sh.OnError = function(e,s){alert(s);}
 sh.OnOutput = function(o,a){
	 document.location.reload();
	}
 sh.sendCommand("dynarc trash empty -ap <?php echo $archiveInfo['prefix']; ?>");
}

function widget_categorySelect_updateTrashInfo()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a)
	 {
	  document.getElementById('trashed_cat_count').innerHTML = "0";
	  document.getElementById('trash').style.display='none';
	  return;
	 }
	 if(a['categories'] && a['categories'].length)
	 {
	  document.getElementById('trashed_cat_count').innerHTML = a['categories'].length;
	  document.getElementById('trash').style.display='';
	 }
	}
 sh.sendCommand("dynarc trash list -ap <?php echo $archiveInfo['prefix']; ?>");
}

</script>
</body></html>
<?php

function jstree_recursiveInsert($node)
{
 echo "<li id='".$node['id']."'><a href='#'><ins>&nbsp;</ins>".html_entity_decode($node['name'],ENT_QUOTES,"UTF-8")."</a>";
 if(count($node['subcategories']))
 {
  echo "<ul>";
  for($c=0; $c < count($node['subcategories']); $c++)
   jstree_recursiveInsert($node['subcategories'][$c]);
  echo "</ul>";
 }
 echo "</li>";
}

