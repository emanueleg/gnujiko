<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 04-04-2012
 #PACKAGE: system-config-gui
 #DESCRIPTION: User and Group Manager configuration form
 #VERSION: 2.1beta
 #CHANGELOG: 04-04-2012 : Icon field added into application menu table.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("config-menumanager");

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
?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Menu Manager</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");

include($_BASE_PATH."var/objects/gextendedtable/index.php");
include($_BASE_PATH."var/objects/superdraganddrop/index.php");

?>
<style type='text/css'>
span {
	font-family: Arial;
	font-size: 12px;
	color: #666666;
}

hr.separator {
	background: #cccccf;
	border: 0px;
	height: 1px;
}

table.configugman th {
	font-size: 12px;
	color: #666666;
	text-align: left;
	border-bottom: 1px solid #cccccf;
}

table.configugman td {
	font-size: 12px;
	color: #0169c9;
	border-bottom: 1px solid #cccccf;
}

ul.toolbar {
	margin: 0px;
	padding: 0px;
	list-style: none;
	padding-left: 20px;
	padding-top: 6px;
}

ul.toolbar li {
	float: left;
}

ul.toolbar li.item {
	float: left;
	height: 20px;
	background: #6699cc;
	cursor: pointer;
	font-family: Arial;
	font-size: 12px;
	color: #ffffff;
	margin-left: 1px;
	margin-right: 1px;
	margin-top: 1px;
	padding-left: 4px;
	padding-right: 4px;
	line-height: 1.5em;
	font-weight: bold;
}

ul.toolbar li.separator {
	background: transparent;
	width: 10px;
}

ul.toolbar li.roundleft-btn {
	background: #aaccee url(config/img/roundbtn_left.png) top left no-repeat;
	height: 22px;
	font-family: Arial;
	font-size: 12px;
	color: #ffffff;
	padding-left: 8px;
	padding-right: 8px;
	margin-right: 1px;
	margin-top: 0px;
}

ul.toolbar li.roundleft-btn a {
	font-family: Arial;
	font-size: 12px;
	color: #ffffff;
	text-decoration: none;
	line-height: 1.7em;
	font-weight: bold;
}

ul.toolbar li.roundleft-btn-active {
	background: #0169c9 url(config/img/roundbtn_left.png) top left no-repeat;
	height: 22px;
	font-family: Arial;
	font-size: 14px;
	color: #ffffff;
	padding-left: 8px;
	padding-right: 8px;
	margin-right: 1px;
	margin-top: 0px;
}

ul.toolbar li.roundleft-btn-active span {
	font-family: Arial;
	font-size: 14px;
	color: #ffffff;
	line-height: 1.5em;
	font-weight: bold;
}

ul.toolbar li.roundright-btn {
	background: #aaccee url(config/img/roundbtn_right.png) top right no-repeat;
	height: 22px;
	font-family: Arial;
	font-size: 12px;
	color: #ffffff;
	padding-left: 8px;
	padding-right: 8px;
	margin-top: 0px;
}

ul.toolbar li.roundright-btn a {
	font-family: Arial;
	font-size: 12px;
	color: #ffffff;
	text-decoration: none;
	line-height: 1.7em;
	font-weight: bold;
}

ul.toolbar li.roundright-btn-active {
	background: #0169c9 url(config/img/roundbtn_right.png) top right no-repeat;
	height: 22px;
	font-family: Arial;
	font-size: 14px;
	color: #ffffff;
	padding-left: 8px;
	padding-right: 8px;
	margin-top: 0px;
}

ul.toolbar li.roundright-btn-active span {
	font-family: Arial;
	font-size: 14px;
	color: #ffffff;
	line-height: 1.5em;
	font-weight: bold;
}

a.disabled {
	color: #f31903;
}

div#contents {
	background: url(config/img/edit-menu-bg.png) bottom left no-repeat;
	height: 372px;
}

ul.usermenu {
	margin: 0px;
	padding: 0px;
	list-style: none;
	padding-left: 15px;
	padding-top: 15px;
	width: 190px;
	float: left;
}

ul.usermenu li {
	height: 34px;
	background: transparent;
	padding-left: 12px;
	width: 174px;
}

ul.usermenu li a {
	font-family: Arial;
	font-size: 14px;
	color: #013397;
	line-height: 2.3em;
}

ul.usermenu li.active {
	background: url(config/img/edit-menu-tab.png) top left no-repeat;
}

ul.usermenu li.active a {
	font-family: Arial;
	font-size: 14px;
	color: #013397;
	font-weight: bold;
	text-decoration: none;
	line-height: 2.3em;
}

div.page {
	float: left;
}

h4 {
	font-size: 14px;
	margin-top: 12px;
	margin-bottom: 4px;
}

</style>
</head><body>
<?php

$activePage = $_REQUEST['page'] ? $_REQUEST['page'] : "mainmenu";

$form = new GForm(i18n("Menu Manager"), "MB_CLOSE", "simpleform", "default", "blue", 800, 480);
$form->Begin($_ABSOLUTE_URL."share/widgets/config/icons/menumanager-icon.png");
echo "<div id='contents'>";
?>
<div style='height:32px;'>
 <ul class='toolbar' style='margin-left:200px;'>
  <li onclick='addItem()' class='item'><?php echo i18n("Add new item"); ?></li>
  <li onclick="deleteSelectedItems()" class='item'><?php echo i18n("Delete item/s"); ?></li>
  <li class='separator'>&nbsp;</li>
  <li onclick="activateSelectedItems()" class='item'><?php echo i18n("Activate item/s"); ?></li>
  <li onclick="deactivateSelectedItems()" class='item'><?php echo i18n("Deactivate item/s"); ?></li>
 </ul>
</div>

<ul class='usermenu'>
 <li id='mainmenu-tab' <?php if($activePage == "mainmenu") echo "class='active'"; ?>><a href='#' onclick="showPage('mainmenu')"><?php echo i18n("Main menu"); ?></a></li>
 <li id='dockbar-tab' <?php if($activePage == "dockbar") echo "class='active'"; ?>><a href='#' onclick="showPage('dockbar')"><?php echo i18n("Dockbar"); ?></a></li>
 <li id='applications-tab' <?php if($activePage == "applications") echo "class='active'"; ?>><a href='#' onclick="showPage('applications')"><?php echo i18n("Applications"); ?></a></li>
</ul>


<!-- MAINMENU - PAGE -->
<div class='page' id='mainmenu-page' style="padding-top:12px;<?php if($activePage != 'mainmenu') echo 'display:none;'; ?>">
 <table width='540' cellspacing='0' cellpadding='0' border='0' class='gextendedtable' id='mainmenu-table'>
 <tr><th width='150'><?php echo i18n('Title'); ?></th><th><?php echo i18n('URL'); ?></th><th width='40'><?php echo i18n('Active'); ?></th></tr>
 <?php
 $ret = GShell("mainmenu list --include-unpublished",$_REQUEST['sessid'], $_REQUEST['shellid']);
 $list = $ret['outarr'];
 for($c=0; $c < count($list); $c++)
 {
  $itm = $list[$c];
  echo "<tr id='".$itm['id']."'><td>".$itm['name']."</td><td>".$itm['url']."</td>";
  if($itm['published'])
   echo "<td published='1'><a href='#' onclick='publish(this)'><img src='".$_ABSOLUTE_URL."share/widgets/config/img/check_OK.gif' border='0'/></a></td>";
  else
   echo "<td><a href='#' onclick='publish(this)'><img src='".$_ABSOLUTE_URL."share/widgets/config/img/check_KO.gif' border='0'/></a></td></tr>";
  echo "</tr>";
 }
 ?>
 </table>
</div>

<!-- DOCKBAR - PAGE -->
<div class='page' id='dockbar-page' style="padding-top:12px;<?php if($activePage != 'dockbar') echo 'display:none;'; ?>">
 <table width='540' cellspacing='0' cellpadding='0' border='0' class='gextendedtable' id='dockbar-table'>
 <tr><th width='150'><?php echo i18n('Title'); ?></th><th><?php echo i18n('File loader'); ?></th><th width='40'><?php echo i18n('Active'); ?></th></tr>
 <?php
 $ret = GShell("dockbar list --include-unpublished",$_REQUEST['sessid'], $_REQUEST['shellid']);
 $list = $ret['outarr'];
 for($c=0; $c < count($list); $c++)
 {
  $itm = $list[$c];
  echo "<tr id='".$itm['id']."'><td>".$itm['name']."</td><td>".$itm['loader']."</td>";
  if($itm['published'])
   echo "<td published='1'><a href='#' onclick='publish(this)'><img src='".$_ABSOLUTE_URL."share/widgets/config/img/check_OK.gif' border='0'/></a></td>";
  else
   echo "<td><a href='#' onclick='publish(this)'><img src='".$_ABSOLUTE_URL."share/widgets/config/img/check_KO.gif' border='0'/></a></td></tr>";
  echo "</tr>";
 }
 ?>
 </table>
</div>

<!-- APPLICATIONS - PAGE -->
<div class='page' id='applications-page' style="padding-top:12px;<?php if($activePage != 'applications') echo 'display:none;'; ?>">
 <table width='540' cellspacing='0' cellpadding='0' border='0' class='gextendedtable' id='applications-table'>
 <tr><th width='150'><?php echo i18n('Title'); ?></th><th><?php echo i18n('URL'); ?></th><th><?php echo i18n('Icon'); ?></th><th width='40'><?php echo i18n('Active'); ?></th></tr>
 <?php
 $ret = GShell("system app-list --include-unpublished",$_REQUEST['sessid'], $_REQUEST['shellid']);
 $list = $ret['outarr'];
 for($c=0; $c < count($list); $c++)
 {
  $itm = $list[$c];
  echo "<tr id='".$itm['id']."'><td>".$itm['name']."</td><td>".$itm['url']."</td><td>".$itm['icon']."</td>";
  if($itm['published'])
   echo "<td published='1'><a href='#' onclick='publish(this)'><img src='".$_ABSOLUTE_URL."share/widgets/config/img/check_OK.gif' border='0'/></a></td>";
  else
   echo "<td><a href='#' onclick='publish(this)'><img src='".$_ABSOLUTE_URL."share/widgets/config/img/check_KO.gif' border='0'/></a></td></tr>";
  echo "</tr>";
 }
 ?>
 </table>
</div>

<?php
echo "</div>";
$form->End();
?>
<script>
var ACTIVE_PAGE_NAME = "<?php echo $activePage; ?>";
var TABLES = new Array();

function bodyOnLoad()
{
 init_table_mainmenu();
 init_table_dockbar();
 init_table_applications();
}

function pageReload()
{
 var href = document.location.href.replace('#','');
 if(href.indexOf("&page=") > 0)
  document.location.href = href.replace("&page=<?php echo $_REQUEST['page']; ?>","&page="+ACTIVE_PAGE_NAME);
 else
  document.location.href = href+"&page="+ACTIVE_PAGE_NAME;
}

/* MAIN MENU INIT */
function init_table_mainmenu()
{
 var tb = new GExtendedTable(document.getElementById('mainmenu-table'));
 tb.Fields[0].options.editable = true;
 tb.Fields[1].options.editable = true;
 /* Events */
 tb.OnDeleteRow = function(rows){
	 if(!confirm("<?php echo i18n('Are you sure you want to delete selected items?'); ?>"))
	  return false;
	 var sh = new GShell();
	 for(var c=0; c < rows.length; c++)
	  sh.sendCommand("mainmenu delete -id "+rows[c].id);
	};
 tb.OnRowMove = function(r){
	 var tb = r.mastertable;
	 var q = "";
	 for(var c=1; c < tb.O.rows.length; c++)
	  q+= ","+tb.O.rows[c].id;
	 var sh = new GShell();
	 sh.sendCommand("mainmenu serialize `"+q.substr(1)+"`");
	};
 tb.OnNewRow = function(r){
	 SDD_HANDLER.setDraggableObject(r);
	 r.cells[2].setAttribute('published',0);
	 r.cells[2].innerHTML = "<a href='#' onclick='publish(this)'><img src='"+ABSOLUTE_URL+"share/widgets/config/img/check_KO.gif' border='0'/ ></a>";
	};
 tb.OnCellEdit = function(cell){
	 var oThis = this;
	 var r = cell.parentNode;
	 var pub = r.cells[2].getAttribute('published');
	 var sh = new GShell();
	 if(r.id)
	  sh.sendCommand("mainmenu edit -id "+r.id+" -name `"+r.cells[0].innerHTML+"` -url `"+r.cells[1].innerHTML+"` -published "+pub);
	 else
	 {
	  sh.OnOutput = function(o,a){
		 r.id=a['id'];
		 oThis.OnRowMove(r); // serialize //
		}
	  sh.sendCommand("mainmenu insert -name `"+r.cells[0].innerHTML+"` -url `"+r.cells[1].innerHTML+"` -published 0");
	 }
	};

 /* Drag and drop */
 for(var c=1; c < tb.O.rows.length; c++)
 {
  SDD_HANDLER.setDraggableObject(tb.O.rows[c]);
  tb.O.rows[c].OnMove = function(){tb.OnRowMove(this);}
 }

 SDD_HANDLER.setDropableContainer(document.getElementById('mainmenu-table'));

 TABLES['mainmenu'] = tb;
}

/* DOCKBAR INIT */
function init_table_dockbar()
{
 var tb = new GExtendedTable(document.getElementById('dockbar-table'));
 tb.Fields[0].options.editable = true;
 tb.Fields[1].options.editable = true;
 /* Events */
 tb.OnDeleteRow = function(rows){
	 if(!confirm("<?php echo i18n('Are you sure you want to delete selected items?'); ?>"))
	  return false;
	 var sh = new GShell();
	 for(var c=0; c < rows.length; c++)
	  sh.sendCommand("dockbar delete -id "+rows[c].id);
	};
 tb.OnRowMove = function(r){
	 var tb = r.mastertable;
	 var q = "";
	 for(var c=1; c < tb.O.rows.length; c++)
	  q+= ","+tb.O.rows[c].id;
	 var sh = new GShell();
	 sh.sendCommand("dockbar serialize `"+q.substr(1)+"`");
	};
 tb.OnNewRow = function(r){
	 SDD_HANDLER.setDraggableObject(r);
	 r.cells[2].setAttribute('published',0);
	 r.cells[2].innerHTML = "<a href='#' onclick='publish(this)'><img src='"+ABSOLUTE_URL+"share/widgets/config/img/check_KO.gif' border='0'/ ></a>";
	};
 tb.OnCellEdit = function(cell){
	 var oThis = this;
	 var r = cell.parentNode;
	 var pub = r.cells[2].getAttribute('published');
	 var sh = new GShell();
	 if(r.id)
	  sh.sendCommand("dockbar edit -id "+r.id+" -name `"+r.cells[0].innerHTML+"` -loader `"+r.cells[1].innerHTML+"` -published "+pub);
	 else
	 {
	  sh.OnOutput = function(o,a){
		 r.id=a['id'];
		 oThis.OnRowMove(r); // serialize //
		}
	  sh.sendCommand("dockbar insert -name `"+r.cells[0].innerHTML+"` -loader `"+r.cells[1].innerHTML+"` -published 0");
	 }
	};

 /* Drag and drop */
 for(var c=1; c < tb.O.rows.length; c++)
 {
  SDD_HANDLER.setDraggableObject(tb.O.rows[c]);
  tb.O.rows[c].OnMove = function(){tb.OnRowMove(this);}
 }

 SDD_HANDLER.setDropableContainer(document.getElementById('dockbar-table'));

 TABLES['dockbar'] = tb;
}

/* APPLICATIONS INIT */
function init_table_applications()
{
 var tb = new GExtendedTable(document.getElementById('applications-table'));
 tb.Fields[0].options.editable = true;
 tb.Fields[1].options.editable = true;
 tb.Fields[2].options.editable = true;

 /* Events */
 tb.OnDeleteRow = function(rows){
	 if(!confirm("<?php echo i18n('Are you sure you want to delete selected items?'); ?>"))
	  return false;
	 var sh = new GShell();
	 for(var c=0; c < rows.length; c++)
	  sh.sendCommand("system unregister-app -id "+rows[c].id);
	};
 tb.OnRowMove = function(r){
	 var tb = r.mastertable;
	 var q = "";
	 for(var c=1; c < tb.O.rows.length; c++)
	  q+= ","+tb.O.rows[c].id;
	 var sh = new GShell();
	 sh.sendCommand("system app-serialize `"+q.substr(1)+"`");
	};
 tb.OnNewRow = function(r){
	 SDD_HANDLER.setDraggableObject(r);
	 r.cells[3].setAttribute('published',0);
	 r.cells[3].innerHTML = "<a href='#' onclick='publish(this)'><img src='"+ABSOLUTE_URL+"share/widgets/config/img/check_KO.gif' border='0'/ ></a>";
	};
 tb.OnCellEdit = function(cell){
	 var oThis = this;
	 var r = cell.parentNode;
	 var pub = r.cells[3].getAttribute('published');
	 var sh = new GShell();
	 if(r.id)
	  sh.sendCommand("system edit-app -id "+r.id+" -name `"+r.cells[0].innerHTML+"` -url `"+r.cells[1].innerHTML+"` -icon `"+r.cells[2].innerHTML+"` -published "+pub);
	 else
	 {
	  sh.OnOutput = function(o,a){
		 r.id=a['id'];
		 oThis.OnRowMove(r); // serialize //
		}
	  sh.sendCommand("system register-app -name `"+r.cells[0].innerHTML+"` -url `"+r.cells[1].innerHTML+"` -icon `"+r.cells[2].innerHTML+"` -published 0");
	 }
	};

 /* Drag and drop */
 for(var c=1; c < tb.O.rows.length; c++)
 {
  SDD_HANDLER.setDraggableObject(tb.O.rows[c]);
  tb.O.rows[c].OnMove = function(){tb.OnRowMove(this);}
 }

 SDD_HANDLER.setDropableContainer(document.getElementById('applications-table'));

 TABLES['applications'] = tb;
}

/* COMMON FUNCTIONS */
function publish(aObj)
{
 var r = aObj.parentNode.parentNode;
 var pub = aObj.parentNode.getAttribute('published');
 var published = (pub=="1") ? "0" : "1";
 aObj.parentNode.setAttribute('published',published);

 switch(r.mastertable.O.id)
 {
  case 'mainmenu-table' : {
	 var sh = new GShell();
	 sh.OnOutput = function(){
		 var imgFile = aObj.getElementsByTagName('IMG')[0].src;
		 if(published == "0")
		  aObj.getElementsByTagName('IMG')[0].src = imgFile.replace('check_OK','check_KO');
		 else
		  aObj.getElementsByTagName('IMG')[0].src = imgFile.replace('check_KO','check_OK');
		}
	 sh.sendCommand("mainmenu edit -id "+r.id+" -published "+published);
	} break;
  case 'dockbar-table' : {
	 var sh = new GShell();
	 sh.OnOutput = function(){
		 var imgFile = aObj.getElementsByTagName('IMG')[0].src;
		 if(published == "0")
		  aObj.getElementsByTagName('IMG')[0].src = imgFile.replace('check_OK','check_KO');
		 else
		  aObj.getElementsByTagName('IMG')[0].src = imgFile.replace('check_KO','check_OK');
		}
	 sh.sendCommand("dockbar edit -id "+r.id+" -published "+published);
	} break;
  case 'applications-table' : {
	 var sh = new GShell();
	 sh.OnOutput = function(){
		 var imgFile = aObj.getElementsByTagName('IMG')[0].src;
		 if(published == "0")
		  aObj.getElementsByTagName('IMG')[0].src = imgFile.replace('check_OK','check_KO');
		 else
		  aObj.getElementsByTagName('IMG')[0].src = imgFile.replace('check_KO','check_OK');
		}
	 sh.sendCommand("system edit-app -id "+r.id+" -published "+published);
	} break;
 }
}

function addItem()
{
 var tb = TABLES[ACTIVE_PAGE_NAME];
 var r = tb.AddRow(true);
 tb._selectRow(r.rowIndex);
 tb._editCell(r.cells[0],true);
}

function deleteSelectedItems()
{
 var tb = TABLES[ACTIVE_PAGE_NAME]
 if(!tb.SelectedRows.length)
  return alert("<?php echo i18n('You must select at least one item'); ?>");
 if(!confirm("<?php echo i18n('Are you sure you want to delete the selected items?'); ?>"))
  return;

 var sh = new GShell();
 sh.OnFinish = function(){pageReload();}
 for(var c=0; c < tb.SelectedRows.length; c++)
 {
  switch(tb.O.id)
  {
   case 'mainmenu-table' : sh.sendCommand("mainmenu delete -id "+tb.SelectedRows[c].id); break;
   case 'dockbar-table' : sh.sendCommand("dockbar delete -id "+tb.SelectedRows[c].id); break;
   case 'applications-table' : sh.sendCommand("system unregister-app -id "+tb.SelectedRows[c].id); break;
  }
 }
}

function activateSelectedItems()
{
 var tb = TABLES[ACTIVE_PAGE_NAME]
 if(!tb.SelectedRows.length)
  return alert("<?php echo i18n('You must select at least one item'); ?>");

 var sh = new GShell();
 sh.OnFinish = function(){pageReload();}
 for(var c=0; c < tb.SelectedRows.length; c++)
 {
  switch(tb.O.id)
  {
   case 'mainmenu-table' : sh.sendCommand("mainmenu edit -id "+tb.SelectedRows[c].id+" -published 1"); break;
   case 'dockbar-table' : sh.sendCommand("dockbar edit -id "+tb.SelectedRows[c].id+" -published 1"); break;
   case 'applications-table' : sh.sendCommand("system edit-app -id "+tb.SelectedRows[c].id+" -published 1"); break;
  }
 }
}

function deactivateSelectedItems()
{
 var tb = TABLES[ACTIVE_PAGE_NAME]
 if(!tb.SelectedRows.length)
  return alert("<?php echo i18n('You must select at least one item'); ?>");

 var sh = new GShell();
 sh.OnFinish = function(){pageReload();}
 for(var c=0; c < tb.SelectedRows.length; c++)
 {
  switch(tb.O.id)
  {
   case 'mainmenu-table' : sh.sendCommand("mainmenu edit -id "+tb.SelectedRows[c].id+" -published 0"); break;
   case 'dockbar-table' : sh.sendCommand("dockbar edit -id "+tb.SelectedRows[c].id+" -published 0"); break;
   case 'applications-table' : sh.sendCommand("system edit-app -id "+tb.SelectedRows[c].id+" -published 0"); break;
  }
 }
}

function showPage(pagename)
{
 if(pagename == ACTIVE_PAGE_NAME)
  return;
 document.getElementById(ACTIVE_PAGE_NAME+"-tab").className = "";
 document.getElementById(ACTIVE_PAGE_NAME+"-page").style.display='none';
 ACTIVE_PAGE_NAME = pagename;
 document.getElementById(ACTIVE_PAGE_NAME+"-tab").className = "active";
 document.getElementById(ACTIVE_PAGE_NAME+"-page").style.display='';
}
</script>
</body></html>
<?php

