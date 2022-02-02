<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 04-06-2013
 #PACKAGE: gnujiko-desktop-base
 #DESCRIPTION: 
 #VERSION: 
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Gnujiko Desktop - Insert Module</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/desktop/css/common.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/desktop/css/insert.css" type="text/css" />
</head><body>
<div class="default-widget" style="width:570px">
 <h3 class="header">Inserisci un nuovo modulo... </h3> <img onclick="gframe_close();" src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/desktop/img/widgetclose.png" class="default-widget-close"/>

 <div class="default-widget-page">
  <h2 class="section">Moduli vari</h2>
  <p>
   <?php
   $ret = GShell("desktop available-modules");
   $list = $ret['outarr'];
   for($c=0; $c < count($list); $c++)
   {
	echo "<div class='item'>";
	echo "<div class='thumbnail' style='background-image:url(".$_ABSOLUTE_URL."var/desktop/modules/".$list[$c]['name']."/".$list[$c]['icon'].")' onclick='select(\"".$list[$c]['name']."\")'/></div>";
	echo "<a href='#' class='item-title' onclick='select(\"".$list[$c]['name']."\")'>".$list[$c]['title']."</a><br/><span class='item-desc'>".$list[$c]['description']."</span>";
	echo "</div>";
   }
   ?>
  </p>

 <!-- <hr class="default-widget-section-separator" style="clear:both;"/>

  <h2 class="section">Contenuti</h2>
  <p>
   <?php
   $ret = GShell("cms content-list");
   $list = $ret['outarr'];
   for($c=0; $c < count($list); $c++)
   {
	echo "<div class='item'>";
	echo "<div class='thumbnail' style='background-image:url(".$_ABSOLUTE_URL."var/cms/contents/".$list[$c]['name']."/".$list[$c]['icon'].")' onclick='select(\"content\",\"".$list[$c]['name']."\")'/></div>";
	echo "<a href='#' class='item-title' onclick='select(\"content\",\"".$list[$c]['name']."\")'>".$list[$c]['title']."</a><br/><span class='item-desc'>".$list[$c]['description']."</span>";
	echo "</div>";
   }
   ?>
  </p> -->

 </div>

 <div class="default-widget-footer" style="clear:both;">
  <span class="left-button gray" onclick="gframe_close()">Annulla</span> 
 </div>

</div>

<script>
function select(moduleName)
{
 gframe_close(moduleName+" selected.",moduleName);
}
</script>
</body></html>
<?php

