<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 27-08-2014
 #PACKAGE: backoffice
 #DESCRIPTION: BackOffice 2 - Ri.Ba.
 #VERSION: 2.2beta
 #CHANGELOG: 27-08-2014 : restricted access integration.
			 27-02-2014 - Fatto le stampe e traduzioni
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../";

include($_BASE_PATH."var/templates/glight/index.php");

if(!restrictedAccess("backoffice"))
 exit();

$template = new GLightTemplate();
$template->includeObject("gcal");
$template->includeObject("editsearch");
$template->includeInternalObject("serp");
$template->includeObject("printmanager");
$template->includeInternalObject("contactsearch");

$template->Begin(i18n("Ri.Ba."));

$dateFrom = $_REQUEST['from'] ? $_REQUEST['from'] : date("Y-m")."-01";
$dateTo = $_REQUEST['to'] ? $_REQUEST['to'] : date("Y-m-d",strtotime("+1 month",strtotime($dateFrom)));

$centerContents = "<input type='text' class='bank' style='width:390px;float:left' placeholder='".i18n('Filter by bank...')."' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\"/><input type='button' class='button-search' id='searchbtn'/>";
$centerContents.= "<input type='text' class='calendar' value='".date('d/m/Y',strtotime($dateFrom))."' id='datefrom' style='margin-left:30px'/>";
$centerContents.= "<span class='smalltext'> ".i18n('to')." </span> <input type='text' class='calendar' value='".date('d/m/Y',strtotime($dateTo))."' id='dateto'/>";

$template->Header("search", $centerContents, "BTN_EXIT");

$_ORDER_BY = $_REQUEST['sortby'] ? $_REQUEST['sortby'] : "ctime";
$_ORDER_METHOD = $_REQUEST['sortmethod'] ? strtoupper($_REQUEST['sortmethod']) : "ASC";
$_RPP = $_REQUEST['rpp'] ? $_REQUEST['rpp'] : 10;
$_PG = $_REQUEST['pg'] ? $_REQUEST['pg'] : 1;

$_SERP = new SERP();
$_SERP->setOrderBy($_ORDER_BY);
$_SERP->setOrderMethod($_ORDER_METHOD);
$_SERP->setResultsPerPage($_RPP);
$_SERP->setCurrentPage($_PG);

if(!$_REQUEST['show'])
 $_REQUEST['show'] = "all";
if(!$_REQUEST['filter'])
 $_REQUEST['filter'] = "all";

$cmd = "backoffice get-riba -from '".$dateFrom."' -to '".$dateTo."'";
$ret = $_SERP->SendCommand($cmd);
$list = $ret['items'];

$template->SubHeaderBegin(10);
?>
 &nbsp;</td>
 <td width='140'><input type='button' class="button-blue menuwhite" value='Menu' connect='mainmenu' id='menubutton'/>
		<ul class='popupmenu' id='mainmenu'>
		 <li onclick='Print()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/printer.gif"/> <?php echo i18n('Print'); ?></li>
		 <li onclick='exportToExcel()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/excel.png"/> <?php echo i18n('Export to Excel'); ?></li>
		 <li class='separator'>&nbsp;</li>
		 <li onclick='deleteSelectedRiBa()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/> <?php echo i18n('Delete selected Ri.Ba.'); ?></li>
		</ul></td>
 <td width='400'><ul class='toggles'><?php
	  $show = array("all"=>i18n('All'), "tosend"=>i18n('To send'), "sent"=>i18n('Sent'), "trash"=>i18n("Trashed"));
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
 <td>
	<?php $_SERP->DrawSerpButtons(true);
 
//---------------------------------------------//
$template->SubHeaderEnd();

$template->Body("default",800);

/*-------------------------------------------------------------------------------------------------------------------*/

/*switch($_REQUEST['show'])
{
 case 'transfers' : $cmd.= " -paymentmode 'BB'"; break;
 case 'riba' : $cmd.= " -paymentmode 'RB'"; break;
 case 'cash' : $cmd.= " -paymentmode 'RD'"; break;
}
if($_REQUEST['subjid'])
 $cmd.= " -subjid '".$_REQUEST['subjid']."'";
else if($_REQUEST['search'])
 $cmd.= " -subject `".$_REQUEST['search']."`";*/
/* --- EXEC COMMAND --- */
//$ret = GShell($cmd);
//$list = $ret['outarr']['items'];
/*-------------------------------------------------------------------------------------------------------------------*/
?>
<table width='100%' cellspacing='0' cellpadding='0' border='0' class='sortable-table' id='ribalist'>
<tr><th width='16' noprint='true'><input type='checkbox'/></th>
	<th width='70' sortable='true' field='ctime' colwidth='20'><?php echo i18n('Rec. date'); ?></th>
	<th width='70' sortable='true' field='availdate' colwidth='20'><?php echo i18n('Avail. date'); ?></th>
	<th width='100' sortable='true' field='name' colwidth='25'><?php echo i18n('Title'); ?></th>
	<th width='60' colwidth='20'><?php echo i18n('Doc. num'); ?></th>
	<th width='60' colwidth='20'><?php echo i18n('Status'); ?></th>
	<th colwidth='60'><?php echo i18n('Bank'); ?></th>
	<th width='70' colwidth='20'><?php echo i18n('Tot. amount'); ?></th>
</tr>
<?php
$totAmount=0;
for($c=0; $c < count($list); $c++)
{
 $item = $list[$c];
 echo "<tr id='".$item['id']."'><td><input type='checkbox'/></td>";
 echo "<td>".date('d.m.Y',strtotime($item['ctime']))."</td>";
 echo "<td>".date('d.m.Y',strtotime($item['availdate']))."</td>";
 echo "<td><a href='editriba.php?id=".$item['id']."' target='RB-".$item['id']."'>".$item['name']."</a></td>";
 echo "<td align='center'>".$item['elements_count']."</td>";
 echo "<td>&nbsp;</td>";
 echo "<td>".$item['bank_name']."</td>";
 echo "<td align='right'>".number_format($item['tot_amount'],2,',','.')." &euro;</td>";
 echo "</tr>";
 $totAmount+= $item['tot_amount'];
}
?>
</table>

<div class="totals-footer">
 <table width="100%" cellspacing="0" cellpadding="0" border="0">
  <tr><td rowspan='2' valign='middle'><input type='button' class='button-blue' value='Stampa' onclick='Print()'/></td>
	  <td align='center'><span class='smalltext'><?php echo i18n('tot. Ri.Ba.'); ?></span></td>
	  <td align='right'><span class='smalltext'><?php echo i18n('Total amount'); ?></span></td></tr>
  <tr><td align='center'><span class='smalltext' id='foot-totriba'><?php echo count($list); ?></span></td>
	  <td align='right'><span class='bigtext' id='foot-totamount'><b><?php echo number_format($totAmount,2,',','.'); ?> &euro;</b></span></td></tr>
 </table>
</div>

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

function deleteSelectedRiBa()
{
 var tb = document.getElementById("ribalist");
 var sel = tb.getSelectedRows();
 if(!sel.length)
  return alert("<?php echo i18n('You must select at least one document'); ?>");

 if(!confirm("<?php echo i18n('Are you sure you want to delete the selected RiBa?'); ?>"))
  return;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnFinish = function(){document.location.reload();}
 for(var c=0; c < sel.length; c++)
  sh.sendCommand("backoffice delete-riba -id "+sel[c].id);
}

function Print()
{
 var doc = new GnujikoPrintableDocument("<?php echo i18n('Ri.Ba.'); ?>", "A4");

 var dateFrom = new Date();
 var dateTo = new Date();
 dateFrom.setFromISO(document.getElementById("datefrom").isodate);
 dateTo.setFromISO(document.getElementById("dateto").isodate);

 var header = "<div style='width:190mm' class='defaultheader'><h3><?php echo i18n('Ri.Ba.'); ?> - <?php echo i18n('from'); ?> "+dateFrom.printf('d/m/Y')+" <?php echo i18n('to'); ?> "+dateTo.printf('d/m/Y')+"</h3></div>";
 doc.setDefaultPageHeader(header);

 var footer = "<div style='width:190mm;margin-top:10mm' class='defaultfooter'>";
 footer+= "<table width='100%' cellspacing='0' cellpadding='0' border='0' class='footertable'>";
 footer+= "<tr><td style='width:120mm'>Pag.</td>";
 footer+= "<td style='width:30mm;text-align:center'><?php echo i18n('tot. RiBa'); ?></td>";
 footer+= "<td style='width:30mm;text-align:center'><?php echo i18n('Total amount'); ?></td></tr>";

 footer+= "<tr><td>{PGC}</td>";
 footer+= "<td style='text-align:center'>"+document.getElementById('foot-totriba').innerHTML+"</td>";
 footer+= "<td style='text-align:center'>"+document.getElementById('foot-totamount').innerHTML+"</td></tr>";

 footer+= "</table></div>";

 doc.setDefaultPageFooter(footer);
 doc.includeCSS("var/objects/printmanager/printabletable.css");

 var gpt = new GnujikoPrintableTable(document.getElementById('ribalist'),true,true);
 var ppc = gpt.generatePrintPreview(190);
 for(var c=0; c < ppc.length; c++)
 {
  var page = doc.addPage();
  page.footer = page.footer.replace("{PGC}", (c+1)+"/"+ppc.length);
  page.setContents(ppc[c]);
 }

 doc.printAsPDF();
 document.location.href = "#search";
}

function exportToExcel()
{
 var gpt = new GnujikoPrintableTable(document.getElementById('ribalist'),true,true);
 gpt.exportToExcel();
}
</script>
<?php

$template->End();

?>
