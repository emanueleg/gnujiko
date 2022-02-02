<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 29-01-2014
 #PACKAGE: backoffice
 #DESCRIPTION: BackOffice 2 - Lotti
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../";

include($_BASE_PATH."var/templates/glight/index.php");

$template = new GLightTemplate();
$template->includeObject("gcal");
$template->includeObject("editsearch");
$template->includeInternalObject("serp");
$template->includeInternalObject("contactsearch");
$template->includeInternalObject("productsearch");

$template->Begin("Lotti di produzione");

$dateFrom = $_REQUEST['from'] ? $_REQUEST['from'] : date("Y-m")."-01";
$dateTo = $_REQUEST['to'] ? $_REQUEST['to'] : date("Y-m-d",strtotime("+1 month",strtotime($dateFrom)));

$centerContents = "<input type='text' class='dropdown' style='width:390px;float:left' placeholder='Cerca lotto...' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\" ap='lots' catid='".$_REQUEST['lotid']."'/><input type='button' class='button-search' id='searchbtn'/>";
$centerContents.= "<input type='text' class='calendar' value='".date('d/m/Y',strtotime($dateFrom))."' id='datefrom' style='margin-left:30px'/>";
$centerContents.= "<span class='smalltext'> al </span> <input type='text' class='calendar' value='".date('d/m/Y',strtotime($dateTo))."' id='dateto'/>";

$template->Header("search", $centerContents, "BTN_EXIT");

$_ORDER_BY = $_REQUEST['sortby'] ? $_REQUEST['sortby'] : "ctime";
$_ORDER_METHOD = $_REQUEST['sortmethod'] ? strtoupper($_REQUEST['sortmethod']) : "ASC";
$_RPP = $_REQUEST['rpp'] ? $_REQUEST['rpp'] : 50;
$_PG = $_REQUEST['pg'] ? $_REQUEST['pg'] : 1;

$_SERP = new SERP();
$_SERP->setOrderBy($_ORDER_BY);
$_SERP->setOrderMethod($_ORDER_METHOD);
$_SERP->setResultsPerPage($_RPP);
$_SERP->setCurrentPage($_PG);

if(!$_REQUEST['show'])
 $_REQUEST['show'] = "all";

$cmd = "backoffice get-lots -from '".$dateFrom."' -to '".$dateTo."'";
switch($_REQUEST['show'])
{
 case 'expired' : $cmd.= " --only-expired"; break;
 case 'unfinished' : $cmd.= " --only-unfinished"; break;
 case 'finished' : $cmd.= " --only-finished"; break;
 case 'trash' : $cmd.= " --only-trashed"; break;
}
if($_REQUEST['lotid'])
 $cmd.= " -cat '".$_REQUEST['lotid']."'";
if($_REQUEST['prodid'])
 $cmd.= " -product '".$_REQUEST['prodid']."'";

$ret = $_SERP->SendCommand($cmd);
$list = $ret['items'];
$template->SubHeaderBegin(10);
?>
 &nbsp;</td>
 <td width='140'><input type='button' class="button-blue menuwhite" value='Menu' connect='mainmenu' id='menubutton'/>
		<ul class='popupmenu' id='mainmenu'>
		 <li onclick='deleteSelectedLots()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/> Elimina lotti selezionati</li>
		</ul></td>
 <td width='340'><ul class='toggles'><?php
	  $show = array("all"=>"Tutti", "expired"=>"Scaduti", "unfinished"=>"Aperti", "finished"=>"Esauriti", "trash"=>"Cestinati");
	  $idx = 0;
	  while(list($k,$v)=each($show))
	  {
	   $class = "";
	   if($idx == 0)
		$class = "first";
	   else if($idx == (count($show)-1))
		$class = "last";
	   if($k == $_REQUEST['show'])
		$class.= " selected";
	   echo "<li".($class ? " class='".$class."'" : "")." onclick=\"setShow('".$k."')\">".$v."</li>";
	   $idx++;
	  }
 	 ?></ul></td>
 <td width='216'><input type='text' class='search' style='width:200px' placeholder="Filtra per prodotto" id='searchproduct' refap='gmart' refid="<?php echo $_REQUEST['prodid']; ?>" value="<?php echo $_REQUEST['product']; ?>"/></td>
 <td>
	<?php $_SERP->DrawSerpButtons(true);
 
//---------------------------------------------//
$template->SubHeaderEnd();

$template->Body("default",700);

/*-------------------------------------------------------------------------------------------------------------------*/
?>
<table width='100%' cellspacing='0' cellpadding='0' border='0' class='sortable-table' id='ribalist'>
<tr><th width='16'><input type='checkbox'/></th>
	<th width='100' sortable='true' field='name'>Lotto</th>
	<th width='80' sortable='true' field='prod_date'>Data prod.</th>
	<th width='80' sortable='true' field='expiry_date'>Data scad.</th>
	<th>Articolo</th>
	<th width='60' sortable='true' field='qty'>Quantit&agrave;</th>
	<th width='60'>Esaurito</th>
</tr>
<?php

for($c=0; $c < count($list); $c++)
{
 $item = $list[$c];
 echo "<tr id='".$item['id']."'><td><input type='checkbox'/></td>";
 echo "<td>".$item['name']."</td>";
 echo "<td>".date('d.m.Y',strtotime($item['prod_date']))."</td>";
 echo "<td>".($item['expiry_date'] ? date('d.m.Y',strtotime($item['expiry_date'])) : "&nbsp;")."</td>";
 $product = $item['ref_code'] ? $item['ref_code']." - " : "";
 $product.= $item['ref_name'] ? $item['ref_name'] : "senza nome";
 echo "<td><span class='linkblue' onclick='showProduct(\"".$item['ref_ap']."\",".$item['ref_id'].")'>".$product."</span></td>";
 echo "<td align='center'>".$item['qty']."</td>";
 echo "<td>".($item['finished'] ? "si" : "&nbsp;")."</td></tr>";
}
?>
</table>
<br/><br/><br/><br/>
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
	this.initEd(document.getElementById("datefrom"), "date");
	this.initEd(document.getElementById("search"), "catfind").onchange = function(){
		 if(this.value && this.data)
		 {
		  Template.SERP.setVar("search",this.value);
		  Template.SERP.setVar("lotid",this.data['id']);
		 }
		 else
		 {
		  Template.SERP.setVar("search",this.value);
		  Template.SERP.setVar("lotid",0);
		 }
		 Template.SERP.reload(0);
		};
	this.initBtn(document.getElementById("searchbtn")).onclick = function(){document.getElementById("search").onchange();}
	this.initEd(document.getElementById("searchproduct"), "gmart").OnSearch = function(){
		 if(this.value && this.data)
		 {
		  Template.SERP.setVar("product",this.value);
		  Template.SERP.setVar("prodid",this.data['id']);
		 }
		 else
		 {
		  Template.SERP.setVar("product",this.value);
		  Template.SERP.setVar("prodid",0);
		 }
		 Template.SERP.reload(0);	 
		};

	this.initEd(document.getElementById("dateto"), "date").OnDateChange = function(date){
		 Template.SERP.setVar("from",document.getElementById("datefrom").isodate);
		 Template.SERP.setVar("to",date);
		 Template.SERP.reload();
		};

	this.SERP = new SERP("<?php echo $_SERP->OrderBy; ?>", "<?php echo $_SERP->OrderMethod; ?>", "<?php echo $_SERP->RPP; ?>", "<?php echo $_SERP->PG; ?>");
	this.initSortableTable(document.getElementById("ribalist"), this.SERP.OrderBy, this.SERP.OrderMethod).OnSort = function(field, method){
		Template.SERP.OrderBy = field;
	    Template.SERP.OrderMethod = method;
		Template.SERP.reload(0);
	}
}

function setShow(value)
{
 Template.SERP.setVar("show",value);
 Template.SERP.reload(0);
}

function setFilter(value)
{
 Template.SERP.setVar("filter",value);
 Template.SERP.reload(0);
}

function deleteSelectedLots()
{
 var tb = document.getElementById("ribalist");
 var sel = tb.getSelectedRows();
 if(!sel.length)
  return alert("Devi selezionare almeno un lotto");

 if(!confirm("Sei sicuro di voler eliminare i lotti selezionati?"))
  return;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnFinish = function(){document.location.reload();}
 for(var c=0; c < sel.length; c++)
  sh.sendCommand("backoffice delete-lot -id "+sel[c].id);
}

function showProduct(refAp, refId)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){}
 sh.sendCommand("gframe -f gmart/edit.item -params `ap="+refAp+"&id="+refId+"`");
}
</script>
<?php

$template->End();

?>
