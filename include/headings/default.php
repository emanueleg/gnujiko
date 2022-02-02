<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2011 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 14-06-2011
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Default heading for Gnujiko
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$continue = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

?>
<style type='text/css'>
body { font: 62.5% "Trebuchet MS", sans-serif;}

div.gnujiko-default-heading {
	padding: 4px;
	border-bottom: 1px solid #ecf7fe;
	font-size: 13px;
	font-family: Arial;
}
div.gnujiko-default-heading td {
	font-size: 13px;
	font-family: Arial;
}
</style>

<?php
define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");

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

<div class='gnujiko-default-heading'>
<table width='100%' cellspacing='0' cellpadding='0' border='0'>
<tr><td valign='top' align='right'><?php
		
		include_once($_BASE_PATH."include/js/gshell.php");
		if($_SESSION['UID'])
		{
		 /* Load Dockbar */
		 for($c=0; $c < count($dockbarItems); $c++)
		 {
		  if(is_callable("dockbar_".$dockbarItems[$c]['name']."_load",true))
		   call_user_func("dockbar_".$dockbarItems[$c]['name']."_load");
		  echo "&nbsp;";
		 }
		 /* Load Main Menu */
		 $ret = GShell("mainmenu list");
		 $list = $ret['outarr'];
		 echo " <b>".$_SESSION['FULLNAME']."</b> | <a href='".$_ABSOLUTE_URL."'>Home</a>";
		 for($c=0; $c < count($list); $c++)
		  echo " | <a href='".$_ABSOLUTE_URL.($list[$c]['url'])."'>".$list[$c]['name']."</a>";
		 echo " | <a href='".$_ABSOLUTE_URL."accounts/Logout.php?continue=".$_ABSOLUTE_URL."'>Esci</a>";
		}
		else
		 echo "<a href='".$_ABSOLUTE_URL."accounts/Login.php?continue=".urlencode($continue)."'>Accedi</a>";
		?></td></tr>
</table>
</div>

<?php

