<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 10-04-2013
 #PACKAGE: printmodels-config
 #DESCRIPTION: Official Gnujiko editor for print models.
 #VERSION: 2.1beta
 #CHANGELOG: 10-04-2013 : Bug fix vari.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DESKTOP_SHOW_TOOLBAR, $_DESKTOP_TITLE, $_ARCHIVE_INFO, $_SELECTED_CAT;

$_DESKTOP_SHOW_TOOLBAR = false;
$_DESKTOP_BACKGROUND = "#ffffff";
$_DESKTOP_TITLE = "Editor modelli di stampa";

$_BASE_PATH = "../";
define("VALID-GNUJIKO",1);

include($_BASE_PATH.'init/init1.php');
include($_BASE_PATH.'include/session.php');
include_once($_BASE_PATH."include/gshell.php");

$_SELECTED_CAT = null;
if($_REQUEST['cat'])
{
 $ret = GShell("dynarc cat-info -ap `printmodels` -id `".$_REQUEST['cat']."`");
 $_SELECTED_CAT = $ret['outarr'];
}

/* Get trash models */
$ret = GShell("dynarc trash count -ap printmodels");
$trashItemsCount = $ret['outarr']['items'];

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $_DESKTOP_TITLE; ?></title></head>
<link rel='shortcut icon' href='share/images/favicon.png' />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>PrintModels/common.css" type="text/css" />
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
function checkCatIcon($catInfo)
{
 global $_BASE_PATH;

 if(!$catInfo['tag'])
  return "PrintModels/img/cat-default.png";

 $tag = strtolower($catInfo['tag']);
 if(file_exists($_BASE_PATH."share/icons/printmodels/".$tag.".png"))
  return "share/icons/printmodels/".$tag.".png";
 else if(file_exists($_BASE_PATH."share/icons/printmodels/".$tag.".jpg"))
  return "share/icons/printmodels/".$tag.".jpg";
 else if(file_exists($_BASE_PATH."share/icons/printmodels/".$tag.".gif"))
  return "share/icons/printmodels/".$tag.".gif";
 else if(file_exists($_BASE_PATH."share/icons/printmodels/".$tag.".bmp"))
  return "share/icons/printmodels/".$tag.".bmp";

 return "PrintModels/img/cat-default.png";
}
//-------------------------------------------------------------------------------------------------------------------//
?>
<table width='100%' height='100%' cellspacing='0' cellpadding='0' border='0'>
<tr><td valign='top' width='250' style="background:#dee4eb;border-right:1px solid #b0b0b0">
	 <ul class='mainmenu' id='mainmenu'>
	 <?php
	 $ret = GShell("dynarc cat-list -ap printmodels");
	 $mainCats = $ret['outarr'];
	 for($c=0; $c < count($mainCats); $c++)
	 {
	  echo "<li class='title'>".$mainCats[$c]['name']."</li>";
	  $ret = GShell("dynarc cat-list -ap printmodels -parent `".$mainCats[$c]['id']."` --get-items-count");
	  $list = $ret['outarr'];
	  for($i=0; $i < count($list); $i++)
	  {
	   if(!$_SELECTED_CAT)
		$_SELECTED_CAT = $list[$i];
	   $img = checkCatIcon($list[$i]);
	   echo "<li class='item".($_SELECTED_CAT['id'] == $list[$i]['id'] ? ' selected' : '')."' onclick='visCat(".$list[$i]['id'].")'><img src='".$_ABSOLUTE_URL.$img."'/> ".$list[$i]['name']." <em>".$list[$i]['items_count']."</em></li>";
	  }
	  echo "<li>&nbsp;</li>";
	 }
	 ?>
	 <li>&nbsp;</li>

	 <li class='title'>CESTINO</li>
	 <li class="item<?php if($_REQUEST['vis'] == 'trash') echo ' selected'; ?>" onclick="visTrash()"><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/trash.png"/> Modelli cestinati <em><?php echo $trashItemsCount; ?></em> </li>

	 </ul>
	</td><td valign='top'>
	<!-- CONTENTS -->
	<?php
	switch($_REQUEST['vis'])
	{
	 case 'trash' : include($_BASE_PATH."PrintModels/vis-trash.php"); break;
	 default : include($_BASE_PATH."PrintModels/vis-category.php"); break;
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
function visCat(id){document.location.href = "index.php?<?php if($_REQUEST['copy']) echo 'copy='.$_REQUEST['copy'].'&'; ?>cat="+id;}
function visTrash(){document.location.href = "index.php?vis=trash";}
</script>
</body></html>
<?php

