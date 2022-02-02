<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 29-11-2012
 #PACKAGE: gstore
 #DESCRIPTION: Official Gnujiko Store Manager.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DESKTOP_SHOW_TOOLBAR, $_DESKTOP_TITLE, $_ARCHIVE_INFO;

$_DESKTOP_SHOW_TOOLBAR = false;
$_DESKTOP_BACKGROUND = "#eeeeee";
$_DESKTOP_TITLE = "Gestione magazzino";

$_BASE_PATH = "../";
define("VALID-GNUJIKO",1);

include($_BASE_PATH.'init/init1.php');
include($_BASE_PATH.'include/session.php');
include_once($_BASE_PATH."include/gshell.php");

if($_REQUEST['aid'])
{
 $ret = GShell("dynarc archive-info -id `".$_REQUEST['aid']."`");
 $_ARCHIVE_INFO = $ret['outarr'];
}
else if($_REQUEST['ap'])
{
 $ret = GShell("dynarc archive-info -ap `".$_REQUEST['ap']."`");
 $_ARCHIVE_INFO = $ret['outarr'];
}

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $_DESKTOP_TITLE; ?></title></head>
<link rel='shortcut icon' href='share/images/favicon.png' />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>Store/common.css" type="text/css" />
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
<tr><td valign='top' width='190'>
	<img src="<?php echo $_ABSOLUTE_URL; ?>Store/img/logo.png" style="margin-top:20px;margin-left:30px;margin-bottom:20px;"/><br/>
	<div class="<?php echo !$_REQUEST['page'] ? 'storetab-selected' : 'storetab'; ?>">
	 <img src="<?php echo $_ABSOLUTE_URL; ?>Store/img/package.png"/>
	 <span onclick="document.location.href='index.php'">Vista articoli a magazzino</span>
	 <div class="storetab-contents">
	  <div class='hr'>&nbsp;</div>
	  <a class="orange" href="index.php">Tutti i cataloghi</a>
	  <div id="catalogs-ul">
	   <ul class='store-ul-small'>
		<?php
		$db = new AlpaDatabase();
		$db->RunQuery("SELECT archive_id FROM dynarc_archive_extensions WHERE extension_name='storeinfo' ORDER BY id ASC");
		while($db->Read())
		{
		 $ret = GShell("dynarc archive-info -id `".$db->record['archive_id']."`");
		 $itm = $ret['outarr'];
		 echo "<li id='catalog-".$itm['id']."'";
		 if($_ARCHIVE_INFO && ($_ARCHIVE_INFO['id'] == $itm['id']))
		  echo " class='selected'";
		 echo "><a href='?aid=".$itm['id']."'>".$itm['name']."</a></li>";
		}
		$db->Close();
		?>
	   </ul>
	  </div>

	  <div class='hr'>&nbsp;</div>
	  <ul class='linkbuttons'>
	   <li><a href='#' onclick="editSelectedMovements()"><img src="<?php echo $_ABSOLUTE_URL; ?>Store/img/edit.gif"/>Modifica selezionati</a></li>
	   <li><a href='#' onclick='printPreview()'><img src="<?php echo $_ABSOLUTE_URL; ?>Store/img/printer.gif"/>Stampa videata</a></li>
	  </ul>

     </div>
	</div>

	<div class="<?php echo ($_REQUEST['page'] == 'movements') ? 'storetab-selected' : 'storetab'; ?>">
	 <img src="<?php echo $_ABSOLUTE_URL; ?>Store/img/mov.png"/>
	 <span onclick="document.location.href='index.php?page=movements'">Movimenti di magazzino</span>
	 <div class="storetab-contents">
	  <div class='hr'>&nbsp;</div>
		<ul class='linkbuttons'>
	  	 <li><a href='#' onclick='deleteSelectedMovements()'><img src="<?php echo $_ABSOLUTE_URL; ?>Store/img/delete.gif"/>Elimina selezionati</a></li>
	  	 <li><a href='#' onclick='printStoreMovementsPreview()'><img src="<?php echo $_ABSOLUTE_URL; ?>Store/img/printer.gif"/>Stampa videata</a></li>
		</ul>
	 </div>
	</div>

	</td><td valign='top'>
	<!-- CONTENTS -->
	<div class="storepage" id='storepagecontainer' style='height:300px;'>
	<?php
	switch($_REQUEST['page'])
	{
	 case 'movements' : include($_BASE_PATH."Store/movements.php"); break;
	 default : include($_BASE_PATH."Store/home.php"); break;
	}
	?>
	</div>
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
</body></html>
<?php

