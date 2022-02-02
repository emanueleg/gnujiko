<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 27-08-2014
 #PACKAGE: backoffice2
 #DESCRIPTION: BackOffice 2 - Scadenziario passivi
 #VERSION: 2.1beta
 #CHANGELOG: 27-08-2014 : restricted access integration.
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
$template->includeObject("printmanager");
$template->includeInternalObject("serp");
$template->includeInternalObject("contactsearch");

$template->Begin(i18n("Expenses"));

$dateFrom = $_REQUEST['from'] ? $_REQUEST['from'] : date("Y-m")."-01";
if($_REQUEST['to'])
 $dateTo = $_REQUEST['to'];
else
{
 $to = strtotime("+1 month",strtotime($dateFrom));
 $to = strtotime("-1 day",$to);
 $dateTo = date("Y-m-d",$to);
}

$centerContents = "<input type='text' class='contact' style='width:400px;float:left' placeholder='".i18n('Filter by vendor...')."' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\" modal='extended' fields='code_str,name' contactfields='phone,phone2,cell,email'/><input type='button' class='button-search' id='searchbtn'/>";

$filter = array("all"=>i18n("All"), "expired"=>i18n("Expired"), "expiring"=>i18n("Expiring"), "paid"=>i18n("Paid"));
$centerContents.= "<ul class='toggles' style='margin-left:30px;float:left'>";
$idx = 0;
while(list($k,$v)=each($filter))
{
 $class = "";
 if($idx == 0)
  $class = "first";
 else if($idx == (count($filter)-1))
  $class = "last";
 if($k == $_REQUEST['filter'])
  $class.= " selected";
 $centerContents.= "<li".($class ? " class='".$class."'" : "")." onclick=\"setFilter('".$k."')\">".$v."</li>";
 $idx++;
}
$centerContents.= "</ul>";

$template->Header("search", $centerContents, "BTN_EXIT", 800);

$_ORDER_BY = $_REQUEST['sortby'] ? $_REQUEST['sortby'] : "expire_date";
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

$template->SubHeaderBegin(10);
?>
 &nbsp;</td>
 <td width='120'><input type='button' class="button-blue menuwhite" value='Menu' connect='mainmenu' id='menubutton'/>
		<ul class='popupmenu' id='mainmenu'>
		 <li onclick='printSchedule()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/printer.gif"/> <?php echo i18n("Print"); ?></li>
		 <li onclick='exportToExcel()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/excel.png"/> <?php echo i18n("Export to Excel"); ?></li>
		</ul></td>
 <td width='300'><ul class='toggles'><?php
	  $show = array("all"=>i18n("All"), "transfers"=>i18n("Transfers"), "riba"=>i18n("RiBa"), "cash"=>i18n("Cash"));
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
 <td width='260'>
	<input type='text' class='calendar' value="<?php echo date('d/m/Y',strtotime($dateFrom)); ?>" id='datefrom' style='margin-left:30px'/>
	<span class='smalltext'> <?php echo i18n("to"); ?> </span> 
	<input type='text' class='calendar' value="<?php echo date('d/m/Y',strtotime($dateTo)); ?>" id='dateto'/>
			</td>

 <td width='120' valign='middle' style='font-size:10px;font-family:arial,sans-serif;line-height:10px'>
 <input type='radio' name='filterbydatedoc' onclick="setFilterByDocDate(0)" <?php if(!$_REQUEST['filterbydocdate']) echo "checked='true'"; ?>/><?php echo i18n("filter by expire date"); ?></input><br/>
 <input type='radio' name='filterbydatedoc' onclick="setFilterByDocDate(1)" <?php if($_REQUEST['filterbydocdate']) echo "checked='true'"; ?>/><?php echo i18n("filter by doc. date"); ?></input><br/>
 </td>

 <td>&nbsp;
	<?php //$_SERP->DrawSerpButtons(true);
 
//---------------------------------------------//
$template->SubHeaderEnd();

$template->Body("default",800);

/*-------------------------------------------------------------------------------------------------------------------*/
$dateTo = date('Y-m-d',strtotime("+1 day",strtotime($dateTo)));
$cmd = "backoffice get-expenses-schedule -from '".$dateFrom."' -to '".$dateTo."' --only-invoices";
if($_REQUEST['filterbydocdate'])
 $cmd.= " --filter-by-docdate";
switch($_REQUEST['show'])
{
 case 'transfers' : $cmd.= " -paymentmode 'BB'"; break;
 case 'riba' : $cmd.= " -paymentmode 'RB'"; break;
 case 'cash' : $cmd.= " -paymentmode 'RD'"; break;
}
switch($_REQUEST['filter'])
{
 case 'expired' : $cmd.= " --only-expired"; break;
 case 'expiring' : $cmd.= " --only-expiring"; break;
 case 'paid' : $cmd.= " --only-paid"; break;
}
if($_REQUEST['subjid'])
 $cmd.= " -subjid '".$_REQUEST['subjid']."'";
else if($_REQUEST['search'])
 $cmd.= " -subject `".$_REQUEST['search']."`";

$x = explode(",",$_ORDER_BY);
$_ORDER_BY = implode(" ".$_ORDER_METHOD.",",$x);
$cmd.= " --order-by '".$_ORDER_BY." ".$_ORDER_METHOD."'";

/* --- EXEC COMMAND --- */
$ret = GShell($cmd);
$retinfo = $ret['outarr'];
$results = $ret['outarr']['results'];
/*-------------------------------------------------------------------------------------------------------------------*/
?>
<table width='100%' cellspacing='0' cellpadding='0' border='0' class='sortable-table' id='schedulelist'>
<tr><th width='16' noprint='true'><input type='checkbox'/></th>
	<th width='16' noprint='true'>&nbsp;</th>
	<th width='60' sortable='true' field='expire_date' colwidth='20'><?php echo i18n("Expiry"); ?></th>
	<th width='70' sortable='true' field='incomes' colwidth='20'><?php echo i18n("Amount"); ?></th>
	<th width='150' colwidth='35'><?php echo i18n("Document"); ?></th>
	<th width='80' sortable='true' field='doc_date,doc_num' colwidth='20'><?php echo i18n("Doc. date"); ?></th>
	<th width='80' sortable='true' field='description' colwidth='25'><?php echo i18n("Description"); ?></th>
	<th sortable='true' field='subject_name' colwidth='30'><?php echo i18n("Vendor"); ?></th>
	<th width='80' sortable='true' field='payment_type' colwidth='30'><?php echo i18n("Payment type"); ?></th>
</tr>
<?php
for($c=0; $c < count($results); $c++)
{
 $record = $results[$c];
 echo "<tr id='".$record['id']."'><td><input type='checkbox'/></td>";
 /*if($record['riba'])
  echo "<td><a href='editriba.php?id=".$record['riba']['id']."' target='RB-".$record['riba']['id']."'><img src='img/converted.png' title=\"Convertito in RiBa - ".$record['riba']['name']."\"/></td>";
 else*/
  echo "<td>&nbsp;</td>";
 if($record['expired'])
  echo "<td style='color:red'>";
 else if($record['paid'])
  echo "<td style='color:green'>";
 else
  echo "<td>";
 echo date('d.m.Y',strtotime($record['expire_date']))."</td>";
 echo "<td align='right'".($record['paid'] ? " style='color:green'" : "").">".number_format($record['amount'],2,',','.')." &euro;</td>";
 $docName = str_replace(" del ".date('d/m/Y',strtotime($record['docinfo']['ctime'])), "", $record['docinfo']['name']);
 echo "<td><a href='".$_ABSOLUTE_URL."GCommercialDocs/docinfo.php?id=".$record['docinfo']['id']."' target='GCD-".$record['docinfo']['id']."'>"
	.$docName."</a></td>";
 echo "<td>".date('d.m.Y',strtotime($record['docinfo']['ctime']))."</td>";
 echo "<td>".$record['description']."</td>";
 echo "<td>".$record['docinfo']['subject_name']."</td>";
 echo "<td>";
 switch($record['docinfo']['payment_mode_type'])
 {
  case 'RB' : echo i18n("RiBa"); break;
  case 'BB' : echo i18n("Transfer"); break;
  default : echo $record['docinfo']['payment_mode_name']; break;
 }
 echo "</td></tr>";
}
?>
</table>

<div class="totals-footer">
 <table width="100%" cellspacing="0" cellpadding="0" border="0">
  <tr><td rowspan='2' valign='middle'><input type='button' class='button-blue' value="<?php echo i18n('Print'); ?>" onclick="printSchedule()"/></td>
	  <td align='center'><span class='smalltext'><?php echo i18n("tot. expired"); ?></span></td>
	  <td align='center'><span class='smalltext'><?php echo i18n("tot. expiring"); ?></span></td>
	  <td align='center'><span class='smalltext'><?php echo i18n("tot. paid"); ?></span></td>
	  <td align='right'><span class='smalltext'><?php echo i18n("Total amount"); ?></span></td></tr>
  <tr><td align='center'><span class='smalltext' id='foot-totexpired'><?php echo number_format($retinfo['tot_expired'],2,',','.'); ?> &euro;</span></td>
	  <td align='center'><span class='smalltext' id='foot-totexpiring'><?php echo number_format($retinfo['tot_expiring'],2,',','.'); ?> &euro;</span></td>
	  <td align='center'><span class='smalltext' id='foot-totpaid'><?php echo number_format($retinfo['tot_paid'],2,',','.'); ?> &euro;</span></td>
	  <td align='right'><span class='bigtext' id='foot-totamount'><b><?php echo number_format($retinfo['tot_amount'],2,',','.'); ?> &euro;</b></span></td></tr>
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
	this.initEd(document.getElementById("search"), "contactextended").OnSearch = function(){
		 if(this.value && this.data)
		 {
		  Template.SERP.setVar("search",this.value);
		  Template.SERP.setVar("subjid",this.data['id']);
		 }
		 else
		 {
		  Template.SERP.setVar("search",this.value);
		  Template.SERP.setVar("subjid",0);
		 }
		 Template.SERP.reload(0);
		};
	this.initBtn(document.getElementById("searchbtn")).onclick = function(){document.getElementById("search").OnSearch();}

	this.SERP = new SERP("<?php echo $_SERP->OrderBy; ?>", "<?php echo $_SERP->OrderMethod; ?>", "<?php echo $_SERP->RPP; ?>", "<?php echo $_SERP->PG; ?>");
	this.initSortableTable(document.getElementById("schedulelist"), this.SERP.OrderBy, this.SERP.OrderMethod).OnSort = function(field, method){
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

function setFilterByDocDate(value)
{
 Template.SERP.setVar("filterbydocdate",value);
 Template.SERP.reload(0);
}

function printSchedule()
{
 var doc = new GnujikoPrintableDocument("<?php echo i18n('Expenses'); ?>", "A4");

 var dateFrom = new Date();
 var dateTo = new Date();
 dateFrom.setFromISO(document.getElementById("datefrom").isodate);
 dateTo.setFromISO(document.getElementById("dateto").isodate);

 var header = "<div style='width:190mm' class='defaultheader'><h3><?php echo i18n('Expenses'); ?> - <?php echo i18n('from'); ?> "+dateFrom.printf('d/m/Y')+" <?php echo i18n('to'); ?> "+dateTo.printf('d/m/Y')+"</h3></div>";
 doc.setDefaultPageHeader(header);

 var footer = "<div style='width:190mm;margin-top:10mm' class='defaultfooter'>";
 footer+= "<table width='100%' cellspacing='0' cellpadding='0' border='0' class='footertable'>";
 footer+= "<tr><td style='width:60mm'>Pag.</td>";
 footer+= "<td style='width:30mm;text-align:center'><?php echo i18n('tot. expired'); ?></td>";
 footer+= "<td style='width:30mm;text-align:center'><?php echo i18n('tot. expiring'); ?></td>";
 footer+= "<td style='width:30mm;text-align:center'><?php echo i18n('tot. paid'); ?></td>";
 footer+= "<td style='width:30mm;text-align:center'><?php echo i18n('Total amount'); ?></td></tr>";

 footer+= "<tr><td>{PGC}</td>";
 footer+= "<td style='text-align:center'>"+document.getElementById('foot-totexpired').innerHTML+"</td>";
 footer+= "<td style='text-align:center'>"+document.getElementById('foot-totexpiring').innerHTML+"</td>";
 footer+= "<td style='text-align:center'>"+document.getElementById('foot-totpaid').innerHTML+"</td>";
 footer+= "<td style='text-align:center'>"+document.getElementById('foot-totamount').innerHTML+"</td></tr>";

 footer+= "</table></div>";

 doc.setDefaultPageFooter(footer);
 doc.includeCSS("var/objects/printmanager/printabletable.css");

 var gpt = new GnujikoPrintableTable(document.getElementById('schedulelist'),true,true);
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
 var gpt = new GnujikoPrintableTable(document.getElementById('schedulelist'),true,true);
 gpt.exportToExcel();
}
</script>
<?php

$template->End();

?>
