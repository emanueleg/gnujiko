<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-12-2011
 #PACKAGE: system-config-gui
 #DESCRIPTION: Gnujiko Config Panel
 #VERSION: 1.0beta
 #CHANGELOG: 21-12-2011 : Aggiunto bottone per chiudere form integrato con il nuovo comando gframe.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_USERS_HOMES;
$_BASE_PATH = "../../";
include_once($_BASE_PATH."init/init1.php");
include_once($_BASE_PATH."include/session.php");
define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/js/gshell.php");

LoadLanguage("gnujiko-config");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo i18n("Gnujiko - Configuration"); ?></title>
<link rel="stylesheet" href="default.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/config.css" type="text/css" />
</head><body>

<table width='800'>
<tr><td valign='middle' width='300' height='80'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/img/gnujiko-logo.png"/></td>
	<td valign='middle'>
		<div class='doctitle'><?php echo i18n("Configuration panel"); ?></div>
		<div class='doclinks'><?php
			$ret = GShell("system cfg-sec-list");
			$sections = $ret['outarr'];
			$tmp = "";
			for($c=0; $c < count($sections); $c++)
			 $tmp.= " | <a href='#sec-".$sections[$c]['tag']."'>".i18n($sections[$c]['name'])."</a>";
			echo ltrim($tmp," | ");
			?>
		</div>
	</td>
	<td valign='top' align='right'><a href='#' style="text-decoration:none;position:absolute;right:10px;top:10px;" title="<?php echo i18n('Close'); ?>" onclick='widget_default_close()'><b>x</b></a></tr>
</table>

<div style='width:800px;height:500px;overflow:auto;'>
 <?php
 for($c=0; $c < count($sections); $c++)
 {
  echo "<div class='section' id='sec-".$sections[$c]['tag']."'>";
  echo "<div class='title'>".i18n($sections[$c]['name'])."</div>";
  $ret = GShell("system cfg-elements -sec `".$sections[$c]['tag']."`");
  $elements = $ret['outarr'];
  for($i=0; $i < count($elements); $i++)
  {
   if(file_exists($_BASE_PATH.$elements[$i]['file']))
	include_once($_BASE_PATH.$elements[$i]['file']);
  }
  echo "</div>";
  echo "<div class='section' style='height:40px;'>&nbsp;</div>";
 }
 ?>
</div>


<script>
function widget_default_submit()
{
 gframe_close();
}

function widget_default_close()
{
 gframe_close();
}
</script>
</body></html>
<?php

