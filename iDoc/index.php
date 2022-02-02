<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 27-01-2013
 #PACKAGE: idoc
 #DESCRIPTION: 
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DESKTOP_SHOW_TOOLBAR, $_DESKTOP_TITLE, $_ARCHIVE_INFO;

$_DESKTOP_SHOW_TOOLBAR = false;
$_DESKTOP_BACKGROUND = "#ffffff";
$_DESKTOP_TITLE = "iDoc";

$_BASE_PATH = "../";
define("VALID-GNUJIKO",1);

include($_BASE_PATH.'init/init1.php');
include($_BASE_PATH.'include/session.php');
include_once($_BASE_PATH."include/gshell.php");

if(!$_REQUEST['vis'])
 $_REQUEST['vis'] = "tray";

$ret = GShell("dynarc item-list -ap `idoc` --order-by `ctime DESC` -limit 1");
$intoTray = $ret['outarr']['count'];

/* Get trash contracts */
$ret = GShell("dynarc trash count -ap idoc");
$trashItemsCount = $ret['outarr']['items'];

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $_DESKTOP_TITLE; ?></title></head>
<link rel='shortcut icon' href='share/images/favicon.png' />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>iDoc/common.css" type="text/css" />
<?php
if(file_exists($_BASE_PATH."include/headings/desktop.php"))
{
 include($_BASE_PATH.'include/headings/desktop.php');
}
else
{
 echo "<body>";
 include($_BASE_PATH.'include/headings/default.php');
}
//-------------------------------------------------------------------------------------------------------------------//
?>
<table width='100%' height='100%' cellspacing='0' cellpadding='0' border='0'>
<tr><td valign='top' width='250' style="background:#dee4eb;border-right:1px solid #b0b0b0">
	 <ul class='mainmenu' id='mainmenu'>
	  <li class='title'>COLLECT</li>
	  <li class="item<?php if($_REQUEST['vis'] == 'tray') echo ' selected'; ?>" onclick="visTray()"><img src="<?php echo $_ABSOLUTE_URL; ?>iDoc/img/intray.png"/> Fuori dalle categorie <?php echo $intoTray ? "<em>".$intoTray."</em>" : ""; ?></li>

	  <li>&nbsp;</li>

	 <!-- MAIN CATEGORIES -->
	 <?php
	 $ret = GShell("dynarc cat-list -ap idoc");
	 $list = $ret['outarr'];
	 for($c=0; $c < count($list); $c++)
	 {
	  echo "<li class='title'>".strtoupper($list[$c]['name'])."</li>";
	  $ret = GShell("dynarc cat-list -ap idoc -parent `".$list[$c]['id']."`");
	  $sublist = $ret['outarr'];
	  for($i=0; $i < count($sublist); $i++)
	   echo "<li class='item".($_REQUEST['parent'] == $sublist[$i]['id'] ? ' selected' : '')."' onclick='visCategory(".$sublist[$i]['id'].")'><img src='"
		.$_ABSOLUTE_URL."iDoc/img/categories.png'/> ".$sublist[$i]['name']." </li>";
	 }
	 ?>

	 <li>&nbsp;</li>

	 <li class='title'>CESTINO</li>
	 <li class="item<?php if($_REQUEST['vis'] == 'trash') echo ' selected'; ?>" onclick="visTrash()"><img src="<?php echo $_ABSOLUTE_URL; ?>iDoc/img/trash.png"/> Documenti cestinati <em><?php echo $trashItemsCount; ?></em> </li>

	 </ul>
	</td><td valign='top'>
	<!-- CONTENTS -->
	<?php
	switch($_REQUEST['vis'])
	{
	 case 'category' : include($_BASE_PATH."iDoc/vis-category.php"); break;
	 case 'tray' : include($_BASE_PATH."iDoc/vis-tray.php"); break;
	 case 'trash' : include($_BASE_PATH."iDoc/vis-trash.php"); break;
	}
	?>
	<!-- EOF CONTENTS -->
	</td></tr>
</table>
<?php
//-------------------------------------------------------------------------------------------------------------------//
if(file_exists($_BASE_PATH."include/footers/desktop.php"))
 include($_BASE_PATH.'include/footers/desktop.php');
else
 include($_BASE_PATH.'include/footers/default.php');
?>

<script>
function visTray(){document.location.href = "index.php?<?php if($_REQUEST['copy']) echo 'copy='.$_REQUEST['copy'].'&'; ?>vis=tray";}
function visCategory(id){document.location.href = "index.php?<?php if($_REQUEST['copy']) echo 'copy='.$_REQUEST['copy'].'&'; ?>vis=category&parent="+id;}
function visTrash(){document.location.href = "index.php?vis=trash";}

</script>
</body></html>
<?php

