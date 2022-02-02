<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-11-2012
 #PACKAGE: gnujiko-desktop-base
 #DESCRIPTION: Official Gnujiko Desktop - base package
 #VERSION: 2.1beta
 #CHANGELOG: 30-11-2012 : Body onunload function added.
			 05-11-2012 : Bug fix into applications bar.
			 17-03-2012 : Aggiunto variabile globale $_DESKTOP_BACKGROUND per potersi scegliere il colore o l'immagine di sfondo del desktop.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DESKTOP_TITLE, $_DESKTOP_BACKGROUND;

define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");

if(!loginRequired())
 exit;

include_once($_BASE_PATH."include/i18n.php");
LoadLanguage("desktop");

$ret = GShell("dockbar list");
$list = $ret['outarr'];
$dockbarItems = array();
for($c=0; $c < count($list); $c++)
{
 if(!file_exists($_BASE_PATH.$list[$c]['loader']))
  continue;
 include_once($_BASE_PATH.$list[$c]['loader']);
 $dockbarItems[] = $list[$c];
}

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>include/headings/desktop/desktop-base.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/headings/desktop/desktop-base.js" type="text/javascript"></script>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/hacktvsearch/index.php");
?>
<body onload="gnujikodesktopbaseOnLoad()" onunload="gnujikodesktopbaseOnUnload()">

<table width="100%" height="100%" align="center" cellspacing="0" cellpadding="0" border="0" class="desktop-base" id="desktop-base-table">
<!-- HEADER -->
<tr><td class="header-left" align="left" valign="top" rowspan="2">
	 <a href="<?php echo $_ABSOLUTE_URL; ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>include/headings/desktop/img/logo.png" style="margin-left:15px;margin-top:11px;" border="0"/></a>
	</td> <td class="header-title" align="left" valign="top" width="30%">
	 <?php echo $_DESKTOP_TITLE; ?>
	</td> <td class="header-right" align="right" valign="top">
	 <!-- DOCKBAR -->
	 <ul class="dockbar" style="float: right;">
	  <?php
		if($_SESSION['UID'])
		{
		 /* Load Dockbar */
		 for($c=0; $c < count($dockbarItems); $c++)
		 {
		  if(is_callable("dockbar_".$dockbarItems[$c]['name']."_load",true))
		  {
		   if($c==0) echo "<li class='first'>";
		   else if($c == count($dockbarItems)-1) echo "<li class='last'>";
		   else echo "<li>";
		   call_user_func("dockbar_".$dockbarItems[$c]['name']."_load");
		   echo "</li>";
		  }
		 }
		 /* Load Main Menu */
		 $ret = GShell("mainmenu list");
		 $list = $ret['outarr'];
		 echo "<li class='menu'><b>".$_SESSION['FULLNAME']."</b> | <a href='".$_ABSOLUTE_URL."'>".i18n("Home")."</a>";
		 for($c=0; $c < count($list); $c++)
		  echo " | <a href='".$_ABSOLUTE_URL.($list[$c]['url'])."'>".$list[$c]['name']."</a>";
		 echo " | <a href='".$_ABSOLUTE_URL."accounts/Logout.php?continue=".$_ABSOLUTE_URL."'>".i18n("Exit")."</a>";
		 echo "</li>";
		}
	  ?>
	 </ul>
	 <!-- EOF DOCKBAR -->
	</td></tr>
<tr><td colspan="2" class="header-buttons" valign="top"> <span id='gnujikodesktop-hdrbtnnext' onclick="gnujikodesktopHDRBtnNext()">&raquo;</span>
	 <div id='gnujikodesktop-appscontainer' style="width:500px;display:none">
	 <table border="0" cellspacing="0" cellpadding="0" class="headbuttons">
	  <tr>
		<?php
		$ret = GShell("system app-list");
		for($c=0; $c < count($ret['outarr']); $c++)
		{
		 $itm = $ret['outarr'][$c];
		 $active = (strpos($_SERVER['REQUEST_URI'],$itm['url']) !== FALSE) ? true : false;
		 echo "<td".($active ? " class='active'" : "")." width='4%'><img src='".$_ABSOLUTE_URL.$itm['icon']."' width='36' height='36'/>";
		 echo "<div class='headbutton".($active ? "-active" : "")."'><a href='".$_ABSOLUTE_URL.$itm['url']."'>".$itm['name']."</a></div></td>";
		}
		?>
	  <td>&nbsp;</td>
	  </tr>
	 </table>
	 </div>
	</td></tr>

<!-- TOOLBAR -->
<?php
if($_DESKTOP_SHOW_TOOLBAR)
{
 ?>
 <tr><td colspan="3" align="left" valign="top" height="27">
	  <div class="desktop-toolbar">
	  <?php
	   desktopToolbar();
	  ?>
	  </div>
	 </td>
 </tr>
 <?php
}
?>

<!-- CONTENTS -->
<tr><td colspan="3" class="desktop-contents" <?php echo $_DESKTOP_BACKGROUND ? "style='background:".$_DESKTOP_BACKGROUND.";'" : ""; ?> valign="top" align="left">
<?php

