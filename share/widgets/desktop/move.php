<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 04-06-2013
 #PACKAGE: gnujiko-desktop-base
 #DESCRIPTION: Move module into another sheet.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Gnujiko Desktop - Move Module</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/desktop/css/common.css" type="text/css" />
</head><body>
<div class="default-widget" style="width:320px">
 <h3 class="header">Sposta in un altra scheda</h3> <img onclick="gframe_close();" src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/desktop/img/widgetclose.png" class="default-widget-close"/>

 <div class="default-widget-page">
  <h2 class="section">Seleziona una scheda</h2>
  <p>
   <select id="gnujiko-desktop-sheet-list">
   <?php
	$ret = GShell("desktop page-list",$_REQUEST['sessid'],$_REQUEST['shellid']);
	$list = $ret['outarr'];
	for($c=0; $c < count($list); $c++)
	 echo "<option value='".$list[$c]['id']."'".($_REQUEST['pageid']==$list[$c]['id'] ? " selected='selected'>" : ">").$list[$c]['name']."</option>";
   ?>
   </select>
  </p>

 </div>

 <div class="default-widget-footer" style="clear:both;">
  <span class="left-button blue" onclick="submit()">OK</span> 
  <span class="left-button gray" onclick="gframe_close()">Annulla</span> 
 </div>

</div>

<script>
function submit()
{
 var sel = document.getElementById('gnujiko-desktop-sheet-list');
 
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 var ret = new Array();
	 ret['modid'] = "<?php echo $_REQUEST['moduleid']; ?>";
	 ret['pageid'] = sel.value;
	 return gframe_close(o,ret);
	}
 sh.sendCommand("desktop edit-module -id `<?php echo $_REQUEST['moduleid']; ?>` -page '"+sel.value+"'");
}
</script>
</body></html>
<?php

