<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-12-2013
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Dynarc category select form
 #VERSION: 2.1beta
 #CHANGELOG: 
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("dynarc");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo i18n("Dynarc - Select category"); ?></title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."var/objects/jstree/index.php");
include_once($_BASE_PATH."var/objects/jstree/checkbox.php");
?></head><body><?php

$form = new GForm(i18n("Select one or more categories"), null, "simpleform", "default", "orange", 600, 450);
$form->Begin();

if($_REQUEST['ap'] || $_REQUEST['aid'] || $_REQUEST['archive'])
{
 $q = "";
 if($_REQUEST['ap']) $q = " -prefix '".$_REQUEST['ap']."'";
 else if($_REQUEST['aid']) $q = " -id '".$_REQUEST['aid']."'";
 $ret = GShell("dynarc archive-info".$q,$_REQUEST['sessid'],$_REQUEST['shellid']);
 if($ret['error'])
  echo $ret['message'];
 $archiveInfo = $ret['outarr'];
}
else
{
 echo "<h4 style='color:#f31903;'>Invalid archive</h4>";
 return;
}

?>
<style type='text/css'>
.demo {
	height:300px; 
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
</style>

<script type="text/javascript" class="source">
$(function () { 
	$("#basic_html").tree({
	 ui : {
		 theme_name: "checkbox"
		},

	 plugins: {
		 checkbox : {}
		},

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

<div style="padding:5px;border-top:1px solid #dadada">
 <input type="button" class="button-blue" value="<?php echo i18n('Select'); ?>" onclick="selectCat()"/>&nbsp;
 <input type="button" class="button-gray" value="<?php echo i18n('Abort'); ?>" onclick="widget_categorySelect_close()"/>

 <input type="button" id='selectallbtn' class="button-gray" style="float:right" value="<?php echo i18n('Select all'); ?>" onclick="selectAllCat()"/>
 <input type="button" id='unselectallbtn' class="button-gray" style="float:right;display:none" value="<?php echo i18n('Unselect all'); ?>" onclick="unselectAllCat()"/>
</div>

<?php
$form->End();
?>
<script>
function selectCat()
{
 var mytree = jQuery.tree.reference("#basic_html");
 mytree.focus();
 var list = $.tree.plugins.checkbox.get_checked();
 var selected = new Array();

 for(var c=0; c < list.length; c++)
 {
  if(mytree.get_text(list[c]) == "")
   continue;

  var data = new Array();
  data['id'] = list[c].id;
  data['name'] = mytree.get_text(list[c]);
  selected.push(data);
 }

 gframe_close(selected.length+" categories has been selected.", selected);
}

function selectAllCat()
{
 var mytree = jQuery.tree.reference("#basic_html");
 mytree.focus();
 var list = $.tree.plugins.checkbox.get_unchecked();

 for(var c=0; c < list.length; c++)
  $.tree.plugins.checkbox.check(list[c]);

 document.getElementById('selectallbtn').style.display = "none";
 document.getElementById('unselectallbtn').style.display = "";
}

function unselectAllCat()
{
 var mytree = jQuery.tree.reference("#basic_html");
 mytree.focus();
 var list = $.tree.plugins.checkbox.get_checked();

 for(var c=0; c < list.length; c++)
  $.tree.plugins.checkbox.uncheck(list[c]);

 document.getElementById('selectallbtn').style.display = "";
 document.getElementById('unselectallbtn').style.display = "none";
}

function widget_categorySelect_close()
{
 gframe_close();
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

