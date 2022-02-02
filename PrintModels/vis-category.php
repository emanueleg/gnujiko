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

global $_SELECTED_CAT;

include_once($_BASE_PATH."var/objects/htmlgutility/menu.php");

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>PrintModels/vis-category.css" type="text/css" />

<table width='100%' cellspacing='0' cellpadding='4' border='0'>
<tr><td width='50'>&nbsp;</td>
	<td valign='middle'><span class='gray24'>Modelli di stampa: <?php echo $_SELECTED_CAT['name']; ?></span><?php
	if($_REQUEST['copy'])
	 echo "<a href='#' onclick='pasteHere(\"".$_REQUEST['copy']."\")' style='margin-left:30px;font-family:Arial,sans-serif;font-size:16px;'><img src='".$_ABSOLUTE_URL."PrintModels/img/paste.png' style='margin-right:4px;border:0px;'/>Incolla qui</a>";
	?></td>
	<td>
	 <ul class='basicmenu' id='selmenu' style='visibility:hidden'>
	  <li class='blue' id='selectionmenu'><span><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/checkbox.png" border='0'/>Selezionati</span>
		<ul class="submenu">
		 <li onclick="unselectAll(true)">Annulla selezione</li>
		 <li onclick="copySelected()">Crea una copia</li>
		 <li onclick="exportSelected()">Esporta</li>
		 <li class='separator'></li>
		 <li onclick="deleteSelectedModels()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/>Elimina selezionati</li>
		</ul>
	  </li>
	 </ul>
	</td>
	<td align='right'>
	 <a href='#' onclick='newModel()'><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/btn-add.png" border='0' title="Crea un nuovo modello"/></a>
	 <a href='#' onclick='printmodelImport()' title="Importa da file"><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/btn-import.png" border='0'/></a>
	</td></tr>
</table>


<div class="elementstable-container" id="elementstable-container">
 <table width='100%' cellspacing='0' cellpadding='0' border='0' class='elementstable' id='elementstable'>
  <tr><th width='50' style="border-right:1px solid #fcd1bb;">&nbsp;</th>
	 <th style='text-align:left;padding-left:10px'>Modello</th>
	 <th width='80' style='text-align:center;'>Predefinito</th></tr>
  <?php
  $ret = GShell("dynarc item-list -ap `printmodels` -cat `".$_SELECTED_CAT['id']."` -get thumbdata");
  $list = $ret['outarr']['items'];
  for($c=0; $c < count($list); $c++)
  {
   $itm = $list[$c];
   echo "<tr id='".$itm['id']."'><td align='center' valign='top' class='hole'><img src='".$_ABSOLUTE_URL."PrintModels/img/hole.png' onclick='selectRow(this.parentNode.parentNode)' style='cursor:pointer'/></td>";
   echo "<td valign='top'>";
   echo "<div class='modeldiv'><div class='thumbnail'>";
   if($itm['thumbdata'])
   {
	if(strpos($itm['thumbdata'],"data:") !== false)
	 echo "<img src='".$itm['thumbdata']."'/>";
	else
	 echo "<img src='".$_ABSOLUTE_URL.$itm['thumbdata']."'/>";
   }
   else
    echo "&nbsp;";
   echo "</div>";
   echo "<div class='modelinfo'><a href='".$_ABSOLUTE_URL."PrintModels/edit.php?id=".$itm['id']."' target='GJKPM-".$itm['id']."' class='title' id='printmodel-".$itm['id']."-title'>".$itm['name']."</a>";
   echo "<p>data creazione: <b>".date('d/m/Y',$itm['ctime'])."</b>";
   if($itm['mtime'])
	echo "<br/>ultima modifica: <b>".date('d/m/Y',$itm['mtime'])."</b>";
   echo "</p>";
   echo "</div>";
   echo "</div></td>";
   
   if($c == 0)
    echo "<td align='center' valign='top'><img src='".$_ABSOLUTE_URL."PrintModels/img/default.png'/></td></tr>";
   else
    echo "<td align='center' valign='top'><a href='#' onclick='setAsDefault(".$itm['id'].")'><img src='".$_ABSOLUTE_URL."PrintModels/img/default-off.png' border='0'/></a></td></tr>";
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

function newModel()
{
 var nm = prompt("Specifica il nome da assegnare al nuovo modello");
 if(!nm) return;
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.href="edit.php?id="+a['id'];
	}
 sh.sendCommand("dynarc new-item -ap `printmodels` -cat `<?php echo $_SELECTED_CAT['id']; ?>` -name `"+nm+"`"); 
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

function _deleteModel(id)
{
}

function unselectAll()
{
 while(SELECTED_ROWS.length)
 {
  selectRow(SELECTED_ROWS[0]);
 }
}

function deleteSelectedModels()
{
 if(!confirm("Sei sicuro di voler eliminare i modelli selezionati?"))
  return;

 var sh = new GShell();
 sh.OnFinish = function(){document.location.reload();}
 for(var c=0; c < SELECTED_ROWS.length; c++)
  sh.sendCommand("dynarc delete-item -ap `printmodels` -id `"+SELECTED_ROWS[c].id+"`");
}

function setAsDefault(id)
{
 var tb = document.getElementById('elementstable');
 var q = id;
 for(var c=1; c < tb.rows.length; c++)
 {
  if(tb.rows[c].id != id)
   q+= ","+tb.rows[c].id;
 }
 var sh = new GShell();
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("dynarc item-sort -ap `printmodels` -serialize `"+q+"`");
}

function copySelected()
{
 var q = "";
 for(var c=0; c < SELECTED_ROWS.length; c++)
  q+= ","+SELECTED_ROWS[c].id;
 document.location.href = "?cat=<?php echo $_SELECTED_CAT['id']; ?>&copy="+q.substr(1);
}

function pasteHere(str)
{
 var x = str.split(",");
 var sh = new GShell();
 sh.OnFinish = function(){document.location.href="?cat=<?php echo $_SELECTED_CAT['id']; ?>";}
 for(var c=0; c < x.length; c++)
  sh.sendCommand("dynarc item-copy -ap `printmodels` -cat `<?php echo $_SELECTED_CAT['id']; ?>` -id `"+x[c]+"`");
}

function exportSelected()
{
 var title = "Elementi singoli";
 if(SELECTED_ROWS.length == 1)
  title = document.getElementById("printmodel-"+SELECTED_ROWS[0].id+"-title").innerHTML;
 
 var q = "";
 for(var c=0; c < SELECTED_ROWS.length; c++)
  q+= " -id "+SELECTED_ROWS[c].id;

 var sh = new GShell();
 sh.OnPreOutput = function(){}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.href = ABSOLUTE_URL+"getfile.php?file="+a['filename'];
	}
 sh.sendCommand("dynarc export -ap `printmodels` -f `"+title+"`"+q);
}

function printmodelImport()
{
 var sh = new GShell();
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("gframe -f dynarc.import -params `ap=printmodels&cat=<?php echo $_SELECTED_CAT['id']; ?>`");
}

</script>
<?php
