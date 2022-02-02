<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 12-01-2013
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Navigator tool
 #VERSION: 2.2beta
 #CHANGELOG: 12-01-2013 : Aggiunto actionPerms.
			 19-11-2012 : Bug fix.
			 27-01-2012 : Bug fix on i18n array when remove category.
			 21-01-2012 : Removed "Map View".
 #TODO: ImportFromArchive and ExportToArchive
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_ARCHIVE_INFO, $_SELECTED_CAT;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("dynarc");

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
 $_ARCHIVE_INFO = $archiveInfo;
}
else
{
 echo "<h4 style='color:#f31903;'>Invalid archive</h4>";
 return;
}

if($_REQUEST['cat'] || $_REQUEST['ct'])
{
 $ret = GShell("dynarc cat-info -ap `".$archiveInfo['prefix']."`".($_REQUEST['cat'] ? " -id `".$_REQUEST['cat']."`" : " -tag `".$_REQUEST['ct']."`"));
 if(!$ret['error'])
  $_SELECTED_CAT = $ret['outarr'];
}

/* CHECK TRASH STATUS */
$ret = GShell("dynarc trash list -ap ".$archiveInfo['prefix']);
$trash = $ret['outarr'];

//-------------------------------------------------------------------------------------------------------------------//
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
//-------------------------------------------------------------------------------------------------------------------//
?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Gnujiko - <?php echo $archiveInfo['name']; ?></title>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/default.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/dynarc/navigator.css" type="text/css" />
<?php
include_once($_BASE_PATH."include/layers.php");
include_once($_BASE_PATH."var/objects/fckeditor/index.php");
include_once($_BASE_PATH."var/objects/jstree/index.php");
include_once($_BASE_PATH."var/objects/gserppagenav/index.php");
include_once($_BASE_PATH."var/objects/htmlgutility/menu.php");
include_once($_BASE_PATH."var/objects/dyntable/dyntable.php");
?>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; </script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/gshell.js" type="text/javascript"></script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<script>
var ORDERBY = "ctime DESC";
var CATID = <?php echo $_SELECTED_CAT['id'] ? $_SELECTED_CAT['id'] : 0; ?>;
</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/dynarc/js/navigator.js" type="text/javascript"></script>

<?php
/* LOAD PLUGINS */
if(isset($_REQUEST['fullextensions']))
{
 /* item info */
 if(file_exists($_BASE_PATH."etc/dynarc/plugins/item-informations/dynarc.navigator.php"))
  include_once($_BASE_PATH."etc/dynarc/plugins/item-informations/dynarc.navigator.php");
 /* get extensions */
 $archiveInfo['extensions'] = array();
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT extension_name FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' ORDER BY id ASC");
 while($db->Read())
 {
  // load extension plugin //
  if(file_exists($_BASE_PATH."etc/dynarc/plugins/".$db->record['extension_name']."/dynarc.navigator.php"))
  {
   include_once($_BASE_PATH."etc/dynarc/plugins/".$db->record['extension_name']."/dynarc.navigator.php");
   $archiveInfo['extensions'][] = $db->record['extension_name'];
  }
 }
 $db->Close();
}
?>
</head><body>

<table width='1000' height='100%' border='0' cellspacing='0' cellpadding='4' style='background:#ffffff;'>
<tr><td valign='top' width='200' id='menuspace'><div style='padding:0px;'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/dynarc/img/home.png" style='vertical-align:middle;'/> <a id='hometitle' href='#' onclick='_selectCat(0)'>Categorie</span> <a href='#' onclick='_newCat()' style='margin-left:20px;'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/dynarc/img/mini-add.gif" title="<?php echo i18n('Create new category'); ?>" alt='new' border='0'/></a>&nbsp;&nbsp;<a href='#' onclick='_editCat()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/dynarc/img/mini-edit.gif" title="<?php echo i18n('Rename category'); ?>" alt='rename' border='0'/></a>&nbsp;&nbsp;<a href='#' onclick='_permsCat()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/dynarc/img/mini-user.gif" title="<?php echo i18n('Category permissions'); ?>" alt='perms' border='0'/></a>&nbsp;&nbsp;<a href='#' onclick='_deleteCat()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/dynarc/img/mini-delete.gif" title="<?php echo i18n('Delete category'); ?>" alt='delete' border='0'/></a> </div>
	<div class='treediv'><div class="demo source" id="tree_div">
	<ul>
	<?php
	$ret = GShell("dynarc cat-tree -ap ".$archiveInfo['prefix']);
	$nodes = $ret['outarr'];
	for($c=0; $c < count($nodes); $c++)
	 jstree_recursiveInsert($nodes[$c]);
	?>
	</ul>
	</div></div>
	</td>
	<td valign='top'>
		<div id='pathway'>&nbsp;</div>
		<div id='title'><?php echo $archiveInfo['name']; ?></div>
		<div id='catname'><?php echo $_SELECTED_CAT ? $_SELECTED_CAT['name'] : i18n('Elements out of folders'); ?></div>
		<?php
		/* ITEM INFO */
		$ret = GShell("dynarc item-list -ap '".$archiveInfo['prefix']."'".($_SELECTED_CAT['id'] ? " -cat `".$_SELECTED_CAT['id']."`" : "")
		." -extget labels".(count($archiveInfo['extensions']) ? ",".implode(",",$archiveInfo['extensions']) : "")
		." -limit 10 --order-by 'ctime DESC'");
		if($ret['error'])
		 echo "<h4 style='color:#f31903'>".$ret['message']."</h4>";
		else
		 $list = $ret['outarr']['items'];
		$count = $ret['outarr']['count'];
		$from = $count ? 1 : 0;
		$to = $count > 10 ? 10 : $count;
		?>
		<div id='results'><table width='100%' border='0'><tr>
			<td><span id='pagenum'><?php echo $from; ?>-<?php echo $to; ?></span> su <span id='pagetot'><?php echo $count; ?></span></td>
			<td><div id='ordering'><b><?php echo i18n('In order of'); ?>:</b> [ <a href='#' id='orderby_ctime' onclick='_orderby(this)'><?php echo i18n('date'); ?><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/dynarc/img/darrow.png" border='0' style='margin-left:4px;' id='orderby_arrow'/></a> ][ <a href='#' id='orderby_name' onclick='_orderby(this)'><?php echo i18n('alphabetical'); ?></a> ][ <a href='#' id='orderby_published' onclick='_orderby(this)'><?php echo i18n('pub.'); ?></a> ]</div></td>
			<td><div id='GSERPPAGENAVSPACE'></div></td>
			<td><?php echo i18n('Show'); ?>: <select id='visualmode' onchange='_visualmodeChange()'>
			 <option value='list'><?php echo i18n('List'); ?></option>
			 <!-- <option value='map'><?php echo i18n('Map'); ?></option> -->
			</select></td></tr></table></div>
		<!-- LIST VIEW -->
		<div id='listview'>
		<div id='buttonbar'><table border='0' cellspacing='0' cellpadding='0'><tr>
			<td width="50%">
			<ul class="menu" id="mainmenu">
			 <li><?php echo i18n('Actions'); ?>
				<ul class="submenu">
				 <li onclick="_new()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/new_file.png"/><?php echo i18n('New'); ?></li>
				 <li onclick="_newCat(true)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/new_folder.png"/><?php echo i18n('New subcategory'); ?></li>
				 <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/import_orange.gif"/><?php echo i18n('Import'); ?>
					<ul class="submenu">
					 <li onclick="_importFromFile()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/xml.png"/><?php echo i18n('from file (.xml)'); ?></li>
					 <?php
					 /* Verifica se esiste il parser excel per questo archivio */
					 if(file_exists($_BASE_PATH."share/widgets/excel/import.php") && file_exists($_BASE_PATH."etc/excel_parsers/".$_ARCHIVE_INFO['prefix'].".php"))
					  echo "<li onclick='_importFromExcel()'><img src='".$_ABSOLUTE_URL."share/icons/16x16/excel.png'/>".i18n('from Excel')."</li>";
					 ?>
					 <!-- <li onclick="_importFromArchive()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/archive.png"/><?php echo i18n('from archive'); ?></li> -->
					</ul></li>
				 <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/export2.png"/><?php echo i18n('Export'); ?>
					<ul class="submenu">
					 <li onclick="_exportToFile()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/xml.png"/><?php echo i18n('into file (.xml)'); ?></li>
					 <!-- <li onclick="_exportToArchive()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/archive.png"/><?php echo i18n('into another archive'); ?></li> -->
					</ul></li>
				 <li class="separator">&nbsp;</li>
				 <li onclick="_editCat()"><?php echo i18n('Edit folder properties'); ?></li>
				</ul></li>
			 <li><?php echo i18n('Edit'); ?>
				<ul class="submenu">
				 <li onclick="_actionCopy()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/copy.png"/><?php echo i18n('Copy'); ?></li>
				 <li onclick="_actionCut()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/cut.gif"/><?php echo i18n('Cut'); ?></li>
				 <li onclick="_actionPaste()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/paste.gif"/><?php echo i18n('Paste'); ?></li>
				 <li class="separator">&nbsp;</li>
				 <li onclick="_actionPerms()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/lock.png"/><?php echo i18n('Imposta permessi'); ?></li>
				 <li onclick="_deleteSelected()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/><?php echo i18n('Delete selected'); ?></li>
				</ul></li>
			 <li><?php echo i18n('View'); ?>
				<ul class="submenu">
				 <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/info.gif"/> <?php echo i18n('Informations'); ?>
					<ul class="submenu">
					 <?php
					 if(is_callable("dynarc_navigator_plugin_iteminfo_injectMenu",false))
					  echo call_user_func("dynarc_navigator_plugin_iteminfo_injectMenu", $archiveInfo, "info");
					 for($c=0; $c < count($archiveInfo['extensions']); $c++)
					 {
					  $ext = $archiveInfo['extensions'][$c];
					  if(is_callable("dynarc_navigator_plugin_".$ext."_injectMenu",false))
					   echo call_user_func("dynarc_navigator_plugin_".$ext."_injectMenu", $archiveInfo, "info");
					 }
					 ?>
					</ul></li>
				 <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/button.png"/> <?php echo i18n('Buttons'); ?>
					<ul class="submenu">
					 <li><input type="checkbox" checked="true" onchange="_shButtons(this,'publish')"/><?php echo i18n('publish'); ?></li>
					 <li><input type="checkbox" checked="true" onchange="_shButtons(this,'edit')"/><?php echo i18n('edit/delete'); ?></li>
					 <?php
					 for($c=0; $c < count($archiveInfo['extensions']); $c++)
					 {
					  $ext = $archiveInfo['extensions'][$c];
					  if(is_callable("dynarc_navigator_plugin_".$ext."_injectMenu",false))
					   echo call_user_func("dynarc_navigator_plugin_".$ext."_injectMenu", $archiveInfo, "buttons");
					 }
					 ?>
					</ul></li>
				 <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/column.gif"/><?php echo i18n('Columns'); ?>
					<ul class="submenu">
					 <li><input type="checkbox" checked="true" onchange="_shColumns(this,'column-ctime')"/><?php echo i18n('creation date'); ?></li>
					 <?php
					 for($c=0; $c < count($archiveInfo['extensions']); $c++)
					 {
					  $ext = $archiveInfo['extensions'][$c];
					  if(is_callable("dynarc_navigator_plugin_".$ext."_injectMenu",false))
					   echo call_user_func("dynarc_navigator_plugin_".$ext."_injectMenu", $archiveInfo, "columns");
					 }
					 ?>
					</ul></li>
				</ul></li>
			 <?php
			 for($c=0; $c < count($archiveInfo['extensions']); $c++)
			 {
			  if(is_callable("dynarc_navigator_plugin_".$ext."_injectMenu",false))
			   echo call_user_func("dynarc_navigator_plugin_".$ext."_injectMenu", $archiveInfo, "mainmenu");
			 }
			 ?>
			</ul>
			</td>
			<td><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/dynarc/img/search_icon.png"/></td>
			<td><input type='text' id='search' value='' onchange='_search()'/></td>
			<td><input type='button' id='searchbtn' value="<?php echo i18n('Search'); ?>" onclick='_search()'/></td></tr></table></div>
		<div id='resultlist'><table width='100%' id='resultstable' class='dyntable' cellspacing='0' cellpadding='0' border='0'>
			<tr>
				<th id='column-name'><?php echo i18n('Title'); ?></th>
				<?php
				/* extended columns */
				if(is_callable("dynarc_navigator_plugin_iteminfo_injectTH",false))
				 echo call_user_func("dynarc_navigator_plugin_iteminfo_injectTH", $archiveInfo);
				for($i=0; $i < count($archiveInfo['extensions']); $i++)
				{
				 $ext = $archiveInfo['extensions'][$i];
				 if(is_callable("dynarc_navigator_plugin_".$ext."_injectTH",false))
				  echo call_user_func("dynarc_navigator_plugin_".$ext."_injectTH", $archiveInfo);
				}
				?>
				<th width='90' id='column-ctime'><?php echo i18n('Date'); ?></th>
				<th width='1%' id='column-publish'><?php echo i18n('Pub.'); ?></th>
				<th width='50' id='column-buttons-edit'>&nbsp;</th>
			</tr>
			<?php
			for($c=0; $c < count($list); $c++)
			{
			 $itm = $list[$c];
			 echo "<tr id='".$itm['id']."' title='ID: ".$itm['id']."'>";
			 echo "<td><a href='#' onclick='_show(".$itm['id'].")'>".$itm['name']."</a></td>";

			 /* extended columns */
			 if(is_callable("dynarc_navigator_plugin_iteminfo_injectRow",false))
			  echo call_user_func("dynarc_navigator_plugin_iteminfo_injectRow", $archiveInfo, $itm);
			 for($i=0; $i < count($archiveInfo['extensions']); $i++)
			 {
			  $ext = $archiveInfo['extensions'][$i];
			  if(is_callable("dynarc_navigator_plugin_".$ext."_injectRow",false))
			   echo call_user_func("dynarc_navigator_plugin_".$ext."_injectRow", $archiveInfo, $itm);
			 }

			 echo "<td>".date('d/m/Y',$itm['ctime'])."</td>";
			 echo "<td><a href='#' onclick='_publish(this,".$itm['id'].",".($itm['published'] ? "false" : "true").")'><img src='"
				.$_ABSOLUTE_URL."share/widgets/dynarc/img/".($itm['published'] ? "published.gif" : "unpublished.gif")."' border='0'/></a></td>";
			 echo "<td><a href='#' onclick='_edit(".$itm['id'].")'><img src='".$_ABSOLUTE_URL."share/widgets/dynarc/img/edit.gif' border='0'/></a> <a href='#' onclick='_delete(".$itm['id'].")'><img src='".$_ABSOLUTE_URL."share/widgets/dynarc/img/delete.gif' border='0'/></a></td>";
			 echo "</tr>";
			}
			?>
			</table>
		<div id='resultsfooter'>&nbsp;</div>
		</div>
		<div id='trashlist' style='display:none;'><table width='100%' id='trashtable' class='dyntable' cellspacing='0' cellpadding='0' border='0'>
			 <tr>
				 <th width='32'>&nbsp;</th>
				 <th><?php echo i18n('Name'); ?></th>
				 <th width='90'><?php echo i18n('Date'); ?></th></tr>
			</table>
		<div id='trashfooter'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/dynarc/img/dwarrow.png"/> <?php echo i18n('If selected'); ?>: <input type='button' style='color:#f31903;' onclick='_deleteSelected(true)' value="<?php echo i18n('Delete'); ?>"/>  <input type='button' onclick='_restoreSelected()' value="<?php echo i18n('Restore'); ?>"/>  <input type='button' onclick='_emptyTrash()' value="<?php echo i18n('Empty trash'); ?>"/></div>
		</div>
		</div> <!-- EOF LIST VIEW -->
		<!-- MAP VIEW -->
		<div id='mapview' style='display:none;width:730px;height:340px;overflow:auto;' align='center'>

		</div>
		<!-- EOF MAP VIEW -->
	</td></tr>
	<tr><td valign='top' class='leftbtns' height='32'><div><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/dynarc/img/trash.png" style='vertical-align:middle;'/> <a href='#' id='trashtitle' onclick='_showTrash()'><?php echo i18n('Trash'); ?></a></div></td>
		<td valign='top'><div align="right" style="padding:7px;"><input type='button' onclick='_returnSelected()' value="<?php echo i18n('Select'); ?>"/> <input type='button' onclick='_close()' value="<?php echo i18n('Close'); ?>"/></div></td></tr>
</table>

<script>
var MainMenu = new GMenu(document.getElementById('mainmenu'));
var CATNAME = "<?php echo $_SELECTED_CAT['name']; ?>";
var IN_TRASH = false;
var ARCHIVE_PREFIX = "<?php echo $archiveInfo['prefix']; ?>";
var TB = new DynTable(document.getElementById('resultstable'),{selectable:true});
var TBTRASH = new DynTable(document.getElementById('trashtable'),{selectable:true});
var ARCH_EXTENSIONS = "<?php if(count($archiveInfo['extensions'])) echo implode(',',$archiveInfo['extensions']); ?>";
var SERP = new GSERPPageNav(<?php echo $count ? $count : 0; ?>,10);
document.getElementById('GSERPPAGENAVSPACE').appendChild(SERP.O);
SERP.autoupdate = false;
SERP.OnChange = function(_currPage, _start, _rpp){
	 _update();
	}

/* Language */
var i18n = new Array();
i18n['Category'] = "<?php echo i18n('Category'); ?>";
i18n['Elements out of folders'] = "<?php echo i18n('Elements out of folders'); ?>";
i18n['Are you sure you want to delete this document?'] = "<?php echo i18n('Are you sure you want to delete this document?'); ?>";
i18n['You must select at least one element'] = "<?php echo i18n('You must select at least one element'); ?>";
i18n['Are you sure you want to delete selected items?'] = "<?php echo i18n('Are you sure you want to delete selected items?'); ?>";
i18n['Are you sure you want to delete the category %s ?'] = "<?php echo i18n('Are you sure you want to delete the category %s ?'); ?>";
i18n['Search for %s into all documents'] = "<?php echo i18n('Search for %s into all documents'); ?>";
i18n['Enter the name of the new category'] = "<?php echo i18n('Enter the name of the new category'); ?>";
i18n['You must select a category'] = "<?php echo i18n('You must select a category'); ?>";
i18n['Rename this category'] = "<?php echo i18n('Rename this category'); ?>";
i18n['Trash'] = "<?php echo i18n('Trash'); ?>";
i18n['Are you sure you want to empty the trash?'] = "<?php echo i18n('Are you sure you want to empty the trash?'); ?>";
i18n['You have not selected any documents'] = "<?php echo i18n('You have not selected any documents'); ?>";
i18n['Function to implement'] = "<?php echo i18n('Function to implement'); ?>";
i18n['Want to export the entire archive?'] = "<?php echo i18n('Want to export the entire archive?'); ?>";
i18n['Want to export the entire folder %s ?'] = "<?php echo i18n('Want to export the entire folder %s ?'); ?>";
i18n['Nothing to be pasted'] = "<?php echo i18n('Nothing to be pasted'); ?>";


function _new(catId, callback)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return; 
	  _update();
	 if(callback)
	  callback(a);
	 else
	  _edit(a['id']);
	}
 sh.sendCommand("gframe -f dynarc.newitem -params `ap="+ARCHIVE_PREFIX+"&catid="+(catId ? catId : CATID)+"`");
}

function _edit(id)
{
 var sh = new GShell();
 sh.OnOutput = function(){_update();}
 sh.sendCommand("gframe -f dynarc.edititem -params `ap="+ARCHIVE_PREFIX+"&id="+id+"`");
}

function _show(id)
{
 gframe_close("Item #"+id+" selected.",id);
}

function _editCat()
{
 if(!CATID)
 {
  alert("<?php echo i18n('You must select a folder'); ?>");
  return;
 }
 var sh = new GShell();
 sh.OnOutput = function(){_update();}
 sh.sendCommand("gframe -f dynarc.editcat -params `ap="+ARCHIVE_PREFIX+"&id="+CATID+"`");
}
</script>
</body></html>
<?php



