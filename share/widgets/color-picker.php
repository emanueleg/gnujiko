<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2010 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-10-2010
 #PACKAGE: color-picker
 #DESCRIPTION: Official Gnujiko color picker widget
 #VERSION: 1.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."init/init1.php");

$title = $_REQUEST['title'] ? $_REQUEST['title'] : "Personalizza etichetta";
$text = $_REQUEST['previewtext'] ? $_REQUEST['previewtext'] : "Test";

$pickers = $_REQUEST['pickers'] ? explode(";",$_REQUEST['pickers']) : array("Scegli un colore");
$clrs = $_REQUEST['colors'] ? explode(";",$_REQUEST['colors']) : array("#00ff00");
$colors = array();
for($c=0; $c < count($clrs); $c++)
 $colors[] = "#".str_replace('#','',$clrs[$c]);

$palette = array('#000000','#444444','#666666','#999999','#cccccc','#eeeeee','#f3f3f3','#ffffff',
'#ff0000','#ff9900','#ffff00','#00ff00','#00ffff','#0000ff','#9900ff','#ff00ff',
'#f4cccc','#fce5cd','#ff2ccf','#d9ead3','#d0e0e3','#cfe2f3','#d9d2e9','#ead1dc',
'#ea9999','#f9cb9c','#ffe500','#b6d7a8','#a2c4c9','#9fc5e8','#b4a7d6','#d5a6bd',
'#e06666','#f6b26b','#ffd966','#93c47d','#76a5af','#6fa8dc','#8e7cc3','#c27ba0',
'#cc0000','#e69138','#f1c232','#6aa84f','#45818e','#3d85c6','#674ea7','#a64d79',
'#990000','#b45f06','#bf9000','#38761d','#134f5c','#0b5394','#351c75','#741b47');

$jscolors = '"'.implode('","',$colors).'"';

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Gnujiko - Color Picker</title>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/color-picker/color-picker.css" type="text/css" />
</head><body>
<table height="287" cellspacing="0" cellpadding="0" class="default-mask" id="color-picker-widget">
<tr><th colspan="<?php echo count($pickers); ?>"><?php echo $title; ?></th></tr>
<tr><td colspan="<?php echo count($pickers); ?>">Anteprima: <span class='label' id='preview-label' style="background:<?php echo $colors[0]; ?>;color:<?php echo $colors[1]; ?>;"><?php echo $text; ?></span></td></tr>
<tr><?php
	for($i=0; $i < count($pickers); $i++)
	{
	 echo "<td width='".(100/count($pickers))."%' valign='top'><b>".$pickers[$i]."</b><div class='single'>";
	 for($c=0; $c < 8; $c++)
	  echo "<div id='".$palette[$c]."-$i' onclick='color_picker_choose(".$i.",this)' style='background-color:".$palette[$c]."'".($colors[$i] == $palette[$c] ? " class='cb-checked".($c < 4 ? "-white" : "")."'" : " class='cb'")."></div>";
	 echo "</div><div class='single'>";
	 for($c=8; $c < 16; $c++)
	  echo "<div id='".$palette[$c]."-$i' onclick='color_picker_choose(".$i.",this)' style='background-color:".$palette[$c]."'".($colors[$i] == $palette[$c] ? " class='cb-checked".($c < 4 ? "-white" : "")."'" : " class='cb'")."></div>";
	 echo "</div><div class='multi'>";
	 for($c=16; $c < count($palette); $c++)
	  echo "<div id='".$palette[$c]."-$i' onclick='color_picker_choose(".$i.",this)' style='background-color:".$palette[$c]."'".($colors[$i] == $palette[$c] ? " class='cb-checked".($c < 4 ? "-white" : "")."'" : " class='cb'")."></div>";
	 echo "</div></td>";
	}
	?></tr>
<tr><td colspan='2' align='right'><input type='button' value='Applica' onclick='color_picker_apply()'/> <input type='button' value='Annulla' onclick='color_picker_close()'/></td></tr>
</table>

<script>
var COLOR_PICKER_COLORS = new Array(<?php echo $jscolors; ?>);
function color_picker_close(){gframe_close()}
function color_picker_apply()
{
 var msg = "Selected colors:\n";
 for(var c=0; c < COLOR_PICKER_COLORS.length; c++)
  msg+= (c+1)+": "+COLOR_PICKER_COLORS[c]+"\n";
 gframe_close(msg,COLOR_PICKER_COLORS);
}

function color_picker_choose(idx,div)
{
 var selected = document.getElementById(COLOR_PICKER_COLORS[idx]+"-"+idx);
 if(selected)
  selected.className = "cb";
 div.className = "cb-checked";
 if(idx == 0)
  document.getElementById('preview-label').style.backgroundColor = div.style.backgroundColor;
 else
  document.getElementById('preview-label').style.color = div.style.backgroundColor;
 COLOR_PICKER_COLORS[idx] = div.id.substr(0,div.id.indexOf("-"));
}

</script>
</body></html>
<?php

