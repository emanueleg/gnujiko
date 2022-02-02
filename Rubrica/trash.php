<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-02-2016
 #PACKAGE: rubrica
 #DESCRIPTION: 
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_CMD, $_RESTRICTED_ACCESS;

$_BASE_PATH = "../";
$_RESTRICTED_ACCESS = "rubrica";

include($_BASE_PATH."var/templates/glight/index.php");

$template = new GLightTemplate();
$template->includeInternalObject("serp");
$template->includeCSS("rubrica.css");

$template->Begin("Contatti nel cestino");

$centerContents = "<input type='text' class='contact' style='width:390px;float:left' placeholder='Cerca un contatto' id='search' value=\""
	.(!$_REQUEST['filter'] ? htmlspecialchars($_REQUEST['search'],ENT_QUOTES) : '')."\" modal='extended' fields='code_str,name,fidelitycard' contactfields='phone,phone2,cell,email' />";
$centerContents.= "<input type='button' class='button-search' id='searchbtn'/>";

$template->Header("search", "<span class='glight-template-hdrtitle'>Elenco di tutti i contatti nel cestino</span>", "BTN_EXIT", 700);

$_ORDER_BY = $_REQUEST['sortby'] ? $_REQUEST['sortby'] : "name";
$_ORDER_METHOD = $_REQUEST['sortmethod'] ? strtoupper($_REQUEST['sortmethod']) : "ASC";
$_RPP = $_REQUEST['rpp'] ? $_REQUEST['rpp'] : 25;
$_PG = $_REQUEST['pg'] ? $_REQUEST['pg'] : 1;

$_SERP = new SERP();
$_SERP->setOrderBy($_ORDER_BY);
$_SERP->setOrderMethod($_ORDER_METHOD);
$_SERP->setResultsPerPage($_RPP);
$_SERP->setCurrentPage($_PG);

$cmd = "dynarc item-list -ap rubrica --all-cat --include-trash -where 'trash=1'";

$_CMD = $cmd;
$ret = $_SERP->SendCommand($cmd);
$list = $_SERP->Results['items'];

$template->SubHeaderBegin(10);
?>
 <input type='button' class="button-blue" value="Svuota cestino" onclick="EmptyTrash()"/>
 </td>
 <td>
 <input type='button' class="button-blue menuwhite" value="Menu" connect='mainmenu' id='menubutton'/>
 <ul class='popupmenu' id='mainmenu'>
  <li onclick="RestoreSelected()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/export2.png"/>Ripristina selezionati</li>
  <li onclick="DeleteSelected()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/>Elimina selezionati</li>
  <li class='separator'>&nbsp;</li>
  <li onclick="EmptyTrash()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/trash.gif"/>Svuota cestino</li>
 </ul>
 </td>
 <td width='150'>
	<span class='smalltext'>Mostra</span>
	<input type='text' class='dropdown' id='rpp' value="<?php echo $_RPP; ?> righe" retval="<?php echo $_RPP; ?>" readonly='true' connect='rpplist' style='width:80px'/>
	<ul class='popupmenu' id='rpplist'>
	 <li value='10'>10 righe</li>
	 <li value='25'>25 righe</li>
	 <li value='50'>50 righe</li>
	 <li value='100'>100 righe</li>
	 <li value='250'>250 righe</li>
	 <li value='500'>500 righe</li>
	</ul>
 </td>
 <td width='200' style='padding-right:20px'>
	<?php $_SERP->DrawSerpButtons(true);
 
//---------------------------------------------//
$template->SubHeaderEnd();

$template->Body("bisection");
?>

<table width='100%' cellspacing='0' cellpadding='0' border='0' class='sortable-table' id='contactlist'>
<tr><th width='16'><input type='checkbox'/></th>
	<th width='32' field='id' sortable='true'>ID</th>
	<th width='60' field='code_str' sortable='true'><?php echo i18n('Code'); ?></th>
	<th field='name' sortable='true'><?php echo i18n('Name and surname / Company name'); ?></th>
	<th width='32'>&nbsp;</th>
</tr>
<?php
$count = $_SERP->Results['count'];
for($c=0; $c < count($list); $c++)
{
 $item = $list[$c];
 echo "<tr id='".$item['id']."'><td><input type='checkbox'/></td>";
 echo "<td>".$item['id']."</td>";
 echo "<td><span class='link blue' onclick='EditItem(\"".$item['id']."\")'>".$item['code_str']."</span></td>";
 echo "<td><span class='link blue' onclick='EditItem(\"".$item['id']."\")'>".$item['name']."</span></td>";
 echo "<td><img src='".$_ABSOLUTE_URL."share/icons/16x16/export2.png' onclick='RestoreSelected(this.parentNode.parentNode)' style='cursor:pointer' title='Ripristina'/></td>";

 echo "</tr>";
}
?>
</table>
<div style='height:100px'></div>
<?php
/*-------------------------------------------------------------------------------------------------------------------*/
$template->Footer();
?>
<script>
Template.OnExit = function(){
	document.location.href = ABSOLUTE_URL;
	return false;
}

Template.OnInit = function(){
	this.initBtn(document.getElementById('menubutton'), "popupmenu");

	this.SERP = new SERP("<?php echo $_SERP->OrderBy; ?>", "<?php echo $_SERP->OrderMethod; ?>", "<?php echo $_SERP->RPP; ?>", "<?php echo $_SERP->PG; ?>");
	var tb = this.initSortableTable(document.getElementById("contactlist"), this.SERP.OrderBy, this.SERP.OrderMethod);
	tb.OnSort = function(field, method){
		Template.SERP.OrderBy = field;
	    Template.SERP.OrderMethod = method;
		Template.SERP.reload(0);
	}
    
	this.initEd(document.getElementById('rpp'), "dropdown").onchange = function(){
		 Template.SERP.RPP = this.getValue();
		 Template.SERP.reload(0);
		}

}

function EditItem(id)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 Template.SERP.reload();
	}
 sh.sendCommand("gframe -f rubrica.edit -params 'id="+id+"'");
}

function DeleteSelected()
{
 var tb = document.getElementById("contactlist");
 var sel = tb.getSelectedRows();
 if(!sel.length)
  return alert("Nessun contatto è stato selezionato");
 if(!confirm("Sei sicuro di voler rimuovere dal cestino i contatti selezionati?"))
  return;

 var q = "";
 for(var c=0; c < sel.length; c++)
  q+= " -id "+sel[c].id;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 Template.SERP.reload(0);
	}

 sh.sendCommand("dynarc trash remove -ap rubrica"+q);
}

function RestoreSelected(r)
{
 if(!r)
 {
  var tb = document.getElementById("contactlist");
  var sel = tb.getSelectedRows();
  if(!sel.length)
   return alert("Nessun contatto è stato selezionato");
  if(!confirm("I contatti selezionati verranno ripristinati. Continuare?"))
   return;

  var q = "";
  for(var c=0; c < sel.length; c++)
   q+= " -id "+sel[c].id;
 }
 else
 {
  var contactName = r.cells[3].getElementsByTagName('SPAN')[0].innerHTML;
  if(!confirm("Ripristinare "+contactName+" ?"))
   return;
  var q = " -id '"+r.id+"'";
 }

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(a && a['items'])
	 {
	  if(a['items'].length == 1)
	   alert("Un contatto è stato ripristinato!");
	  else
	   alert(a['items'].length+" contatti sono stati ripristinati!");
	 }
	 Template.SERP.reload(0);
	}

 sh.sendCommand("dynarc trash restore -ap rubrica"+q);
}

function EmptyTrash()
{
 if(!confirm("Sei sicuro di voler svuotare il cestino? I contatti verranno eliminati permanentemente"))
  return;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(a && a['errors'])
	 {
	  alert("Alcuni contatti non sono stati eliminati perchè non possiedi probabilmente i privilegi necessari per poterlo fare. Effettua il login tramite utente root per eliminare il resto dei contatti.");
	  document.location.reload();
	  return;
	 }
	 document.location.href = ABSOLUTE_URL+"Rubrica/index.php";
	}

 sh.sendCommand("dynarc trash empty -ap 'rubrica' --bypass-errors");
}
</script>
<?php

$template->End();

?>


