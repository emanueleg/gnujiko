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


include_once($_BASE_PATH."var/objects/htmlgutility/menu.php");

/* Get all categories icon */
$ret = GShell("dynarc cat-list -ap printmodels");
$list = $ret['outarr'];
$catIcons = array();
for($c=0; $c < count($list); $c++)
{
 if(file_exists($_BASE_PATH."PrintModels/icons/".strtolower($list[$c]['tag']).".png"))
  $catIcons[$list[$c]['id']] = strtolower($list[$c]['tag']).".png";
 else
  $catIcons[$list[$c]['id']] = "other.png";
}

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>PrintModels/vis-trash.css" type="text/css" />

<table width='100%' cellspacing='0' cellpadding='4' border='0'>
<tr><td width='50'>&nbsp;</td>
	<td valign='middle'><span class='gray24'>Cestino</span></td>
	<td>
	 <ul class='basicmenu' id='selmenu' style='visibility:hidden'>
	  <li class='blue' id='selectionmenu'><span><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/checkbox.png" border='0'/>Selezionati</span>
		<ul class="submenu">
		 <li onclick="restoreSelected()">Ripristina selezionati</li>
		 <li class='separator'></li>
		 <li onclick="deleteSelectediDoc()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/>Elimina definitivamente</li>
		</ul>
	  </li>
	 </ul>
	</td>
	<td align='right'><a href='#' onclick='emptyTrash()'><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/btn-trash-empty.png" border='0' title="Svuota cestino"/></a></td></tr>
</table>


<div class="elementstable-container" id="elementstable-container">
 <table width='100%' cellspacing='0' cellpadding='0' border='0' class='elementstable' id='elementstable'>
  <tr><th width='50' style="border-right:1px solid #fcd1bb;">&nbsp;</th>
	 <th width='300'>Anteprima</th>
 	 <th style='text-align:left;padding-left:10px'>Titolo</th>
	 <th width='80' style='text-align:center;'>&nbsp;</th></tr>
  <?php
  $ret = GShell("dynarc item-list -ap `printmodels` --all-cat -where `trash=1` --include-trash -get thumbdata");
  $list = $ret['outarr']['items'];
  for($c=0; $c < count($list); $c++)
  {
   $itm = $list[$c];
   echo "<tr id='".$itm['id']."'><td align='center' valign='top' class='hole'><img src='".$_ABSOLUTE_URL."PrintModels/img/hole.png' onclick='selectRow(this.parentNode.parentNode)' style='cursor:pointer'/></td>";
   echo "<td valign='top'>";
   echo "<div class='thumbnail'>".($itm['thumbdata'] ? "<img src='".$itm['thumbdata']."'/>" : "&nbsp;")."</div>";
   echo "</td><td valign='top'>";
   echo "<div class='modelinfo'><a href='".$_ABSOLUTE_URL."PrintModels/edit.php?id=".$itm['id']."' target='GJKPM-".$itm['id']."' class='title'>".$itm['name']."</a>";
   echo "<p>data creazione: <b>".date('d/m/Y',$itm['ctime'])."</b>";
   if($itm['mtime'])
	echo "<br/>ultima modifica: <b>".date('d/m/Y',$itm['mtime'])."</b>";
   echo "</p>";
   echo "</div>";
   echo "</td>";
   
   echo "<td>&nbsp;</td></tr>";
  }
 ?>
 </table>
</div>

<script>
var SELECTED_ROWS = new Array();

function desktopOnLoad()
{
 var MainMenu = new GMenu(document.getElementById('selmenu'));
}

function selectRow(r)
{
 var img = r.cells[0].getElementsByTagName('IMG')[0];
 if(r.checked)
 {
  r.className = "";
  img.src = ABSOLUTE_URL+"PrintModels/img/hole.png";
  r.checked = false;
  SELECTED_ROWS.splice(SELECTED_ROWS.indexOf(r),1);
  if(!SELECTED_ROWS.length)
   document.getElementById('selmenu').style.visibility = 'hidden';
 }
 else
 {
  r.className = "selected";
  img.src = ABSOLUTE_URL+"PrintModels/img/hole-checked.png";
  r.checked = true;
  SELECTED_ROWS.push(r);
  document.getElementById('selmenu').style.visibility = 'visible';
 }
}

function unselectAll()
{
 while(SELECTED_ROWS.length)
 {
  selectRow(SELECTED_ROWS[0]);
 }
}

function deleteSelectediDoc()
{
 if(!confirm("Sei sicuro di voler eliminare definitivamente i modelli selezionati?"))
  return;

 var sh = new GShell();
 sh.OnFinish = function(){document.location.reload();}
 var q = "";
 for(var c=0; c < SELECTED_ROWS.length; c++)
  q+= " -id `"+SELECTED_ROWS[c].id+"`";
 sh.sendCommand("dynarc trash remove -ap `printmodels`"+q);
}

function restoreSelected()
{
 if(!confirm("Verranno ripristinati i modelli selezionati. Continuare?"))
  return;

 var sh = new GShell();
 sh.OnFinish = function(){document.location.reload();}
 var q = "";
 for(var c=0; c < SELECTED_ROWS.length; c++)
  q+= " -id `"+SELECTED_ROWS[c].id+"`";
 sh.sendCommand("dynarc trash restore -ap `printmodels`"+q);
}

function emptyTrash()
{
 if(!confirm("Sei sicuro di voler svuotare il cestino?"))
  return;
 var sh = new GShell();
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("dynarc trash empty -ap printmodels");
}
</script>
<?php
