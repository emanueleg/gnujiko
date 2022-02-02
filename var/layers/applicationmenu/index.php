<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-04-2017
 #PACKAGE: gnujiko-template
 #DESCRIPTION: Gnujiko application menu.
 #VERSION: 2.1beta
 #CHANGELOG: 15-04-2017 : Aggiunto logout.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_COMMERCIALDOCS_CONFIG;

$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");

$ret = GShell("system app-list");
$_APPLICATION_LIST = $ret['outarr'];

echo "<link rel='stylesheet' href='".$_ABSOLUTE_URL."var/layers/applicationmenu/applicationmenu.css' type='text/css'/>";

for($c=0; $c < count($_APPLICATION_LIST); $c++)
{
 $item = $_APPLICATION_LIST[$c];
 echo "<div class='gjkappmenu-block'><a href='".$_ABSOLUTE_URL.$item['url']."' target='GNUAPP-".$item['id']."' style='text-decoration:none'>";
  echo "<div class='gjkappmenu-block-icon'><img src='".$_ABSOLUTE_URL.$item['icon']."' height='48'/></div>";
  echo "<div class='gjkappmenu-block-title'>".$item['name']."</div>";
 echo "</a></div>";
}

// LOGOUT
echo "<div class='gjkappmenu-block'><a href='".$_ABSOLUTE_URL."accounts/Logout.php' style='text-decoration:none'>";
 echo "<div class='gjkappmenu-block-icon'><img src='".$_ABSOLUTE_URL."var/layers/applicationmenu/img/logout.png' height='48'/></div>";
 echo "<div class='gjkappmenu-block-title'>Logout</div>";
echo "</a></div>";

