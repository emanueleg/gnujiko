<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 20-04-2017
 #PACKAGE: backoffice2
 #DESCRIPTION: BackOffice 2 - Riepilogo vendite da ordini evasi
 #VERSION: 2.1beta
 #CHANGELOG: 20-04-2017 : Aggiunta colonna guadagno.
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
$template->includeInternalObject("productsearch");
$template->includeInternalObject("contactsearch");
$template->includeObject("printmanager");

$template->Begin("Riepilogo vendite da ordini evasi");

$_FILTERS = array("catalog"=>i18n('catalog'), "product"=>i18n('product'), "subject"=>i18n('customer'), "user"=>i18n('user'));
$_FILTER = $_REQUEST['filter'] ? $_REQUEST['filter'] : "catalog";

$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : "";
$_ID = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
$_SUBJID = $_REQUEST['subjid'] ? $_REQUEST['subjid'] : 0;
$_SUBJNAME = $_REQUEST['subjname'] ? $_REQUEST['subjname'] : "";
$_USRID = $_REQUEST['usrid'] ? $_REQUEST['usrid'] : 0;

$archiveInfo = null;
if($_AP)
{
 $ret = GShell("dynarc archive-info -prefix '".$_AP."'");
 if(!$ret['error'])
  $archiveInfo = $ret['outarr'];
}
if($_REQUEST['id'])

if($_REQUEST['from'] && $_REQUEST['to'])
 $_REQUEST['range'] = "custom";

if(!$_REQUEST['range'])
 $_REQUEST['range'] = "thismonth";

switch($_REQUEST['range'])
{
 case "thisweek" : {
	 $_REQUEST['from'] = date("Y-m-d",strtotime("-6 days"));
	 $_REQUEST['to'] = date("Y-m-d");
	} break;

 case "thismonth" : {
	 $_REQUEST['from'] = date("Y-m")."-01";
	 $_REQUEST['to'] = date("Y-m-d");
	} break;

 case "lastmonth" : {
	 $_REQUEST['from'] = date("Y-m",strtotime("-1 months"))."-01";
	 $_REQUEST['to'] = date("Y-m")."-01";
	} break;

 case "lastquarter" : {
	 $_REQUEST['from'] = date("Y-m",strtotime("-2 months"))."-01";
	 $_REQUEST['to'] = date("Y-m-d");
	} break;

 case "lastfour" : {
	 $_REQUEST['from'] = date("Y-m",strtotime("-3 months"))."-01";
	 $_REQUEST['to'] = date("Y-m-d");
	} break;

 case "lastsemester" : {
	 $_REQUEST['from'] = date("Y-m",strtotime("-5 months"))."-01";
	 $_REQUEST['to'] = date("Y-m-d");
	} break;

 case "last12months" : {
	 $_REQUEST['from'] = date("Y-m",strtotime("-11 months"))."-01";
	 $_REQUEST['to'] = date("Y-m-d");
	} break;

 case "thisyear" : {
	 $_REQUEST['from'] = date("Y")."-01-01";
	 $_REQUEST['to'] = date("Y-m-d");
	} break;

 case "lastyear" : {
	 $lastYear = date("Y")-1;
	 $_REQUEST['from'] = $lastYear."-01-01";
	 $_REQUEST['to'] = $lastYear."-12-31";
	} break;

}

$dateFrom = strtotime($_REQUEST['from']);
$dateTo = strtotime($_REQUEST['to']);

$dateFromStr = date('d',$dateFrom).'/'.strtolower(i18n('MONTHABB-'.date('n',$dateFrom))).'/'.date('Y',$dateFrom);
$dateToStr = date('d',$dateTo).'/'.strtolower(i18n('MONTHABB-'.date('n',$dateTo))).'/'.date('Y',$dateTo);

$centerContents = "<span class='smalltext' style='float:left;height:30px;line-height:30px;margin-right:5px'>".i18n('Filter by:')."</span> ";
$centerContents.= "<input type='text' class='dropdown' style='width:100px;float:left' readonly='true' connect='filterlist' id='filterselect' retval='"
	.$_FILTER."' value='".$_FILTERS[$_FILTER]."'/>";
$centerContents.= "<ul class='popupmenu' id='filterlist'>";
while(list($k,$v) = each($_FILTERS))
{
 $centerContents.= "<li value='".$k."'>".$v."</li>";
}
$centerContents.= "</ul>";

switch($_FILTER)
{
 case 'catalog' : $centerContents.= "<input type='text' class='dropdown' style='width:300px;float:left' placeholder='".i18n('Select a catalog')."' id='search' value=\"".($_AP ? htmlspecialchars($archiveInfo['name'],ENT_QUOTES) : '')."\" at='gmart' ap='".$_AP."'/>"; break;
 case 'product' : $centerContents.= "<input type='text' class='edit' style='width:300px;float:left' placeholder='".i18n('Find a product')."' id='search' value=\""
	.$_REQUEST['search']."\" emptyonclick='true'/>"; break;
 case 'subject' : $centerContents.= "<input type='text' class='contact' style='width:300px;float:left' placeholder='".i18n('Find a customer')."' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\" modal='extended' fields='code_str,name' contactfields='phone,phone2,cell,email'/>"; break;
 case 'user' : $centerContents.= "<input type='text' class='contact' style='width:300px;float:left' placeholder='".i18n('Find a user')."' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\"/>"; break;
}
$centerContents.= "<input type='button' class='button-search' id='searchbtn'/>";

$centerContents.= "<input type='text' class='calendar' value='".date('d/m/Y',$dateFrom)."' id='datefrom' style='margin-left:30px'/>";
$centerContents.= "<span class='smalltext'> ".i18n('to')." </span> <input type='text' class='calendar' value='".date('d/m/Y',$dateTo)."' id='dateto'/>";

$template->Header("search", $centerContents, "BTN_EXIT", 800);

$_ORDER_BY = $_REQUEST['sortby'] ? $_REQUEST['sortby'] : "ctime";
$_ORDER_METHOD = $_REQUEST['sortmethod'] ? strtoupper($_REQUEST['sortmethod']) : "ASC";
$_RPP = $_REQUEST['rpp'] ? $_REQUEST['rpp'] : 10;
$_PG = $_REQUEST['pg'] ? $_REQUEST['pg'] : 1;

$_SERP = new SERP();
$_SERP->setOrderBy($_ORDER_BY);
$_SERP->setOrderMethod($_ORDER_METHOD);
$_SERP->setResultsPerPage($_RPP);
$_SERP->setCurrentPage($_PG);

$cmd = "backoffice orders-summary -from '".date('Y-m-d',$dateFrom)."' -to '".date('Y-m-d',$dateTo)." 23:59:59'";
if($_AP) $cmd.= " -ap '".$_AP."'";
if($_ID) $cmd.= " -id '".$_ID."'";
if($_SUBJID) $cmd.= " -subjectid '".$_SUBJID."'";
else if($_SUBJNAME) $cmd.= " -subject `".$_SUBJNAME."`";
if($_USRID) $cmd.= " -uid '".$_USRID."'";

$_CMD = $cmd;
$ret = $_SERP->SendCommand($cmd);

$_TOT_QTY = $_SERP->Return['tot_qty'];
$_TOT_AMOUNT = $_SERP->Return['tot_amount'];
$_TOT_PROFIT = $_SERP->Return['tot_profit'];
$list = $ret['items'];

$template->SubHeaderBegin(10);

$ranges = array(
 "thisweek"=>i18n("This week"),
 "thismonth"=>i18n("This month"),
 "lastmonth"=>i18n("Last month"),
 "lastquarter"=>i18n("Last quarter"), 
 "lastfour"=>i18n("Last four"), 
 "lastsemester"=>i18n("Last semester"),
 "last12months"=>i18n("Last 12 months"),
 "thisyear"=>i18n("This year"),
 "lastyear"=>i18n("Year")." ".(date("Y")-1),
 "custom"=>i18n("Customized")
);

?>
 &nbsp;</td>
 <td width='200'><input type='button' class="button-blue menuwhite" value="<?php echo $ranges[$_REQUEST['range']]; ?>" connect='mainmenu' id='menubutton'/>
		<ul class='popupmenu' id='mainmenu'>
		   <li onclick="changeRange('thisweek')"><?php echo i18n('This week'); ?></li>
		   <li onclick="changeRange('thismonth')"><?php echo i18n('This month'); ?></li>
	   	   <li onclick="changeRange('lastmonth')"><?php echo i18n('Last month'); ?></li>
		   <li class='separator'>&nbsp;</li>
		   <li onclick="changeRange('lastquarter')"><?php echo i18n('Last quarter'); ?></li>
		   <li onclick="changeRange('lastfour')"><?php echo i18n('Last four'); ?></li>
		   <li onclick="changeRange('lastsemester')"><?php echo i18n('Last semester'); ?></li>
		   <li onclick="changeRange('last12months')"><?php echo i18n('Last 12 months'); ?></li>
		   <li onclick="changeRange('thisyear')"><?php echo i18n('Show since early this year to today'); ?></li>
		   <li class='separator'>&nbsp;</li>
		   <li onclick="changeRange('lastyear')"><?php echo i18n('Shows the trend of last year'); ?></li>
		</ul></td>

 <td>&nbsp;
	<?php 
 
//---------------------------------------------//
$template->SubHeaderEnd();

$template->Body("default",800);

/*-------------------------------------------------------------------------------------------------------------------*/
?>
<table width='100%' cellspacing='0' cellpadding='0' border='0' class='sortable-table' id='summarylist'>
<tr><th width='70' sortable='true' field='ctime' colwidth='25'><?php echo i18n('Date'); ?></th>
    <th width='80' colwidth='25'><?php echo i18n('Code'); ?></th>
	<th colwidth='90'><?php echo i18n('Description'); ?></th>
	<th width='80' style='text-align:center' colwidth='20'><?php echo i18n('Qty'); ?></th>
	<th width='80' style='text-align:center' colwidth='25'><?php echo i18n('Amount'); ?></th>
	<th width='80' style='text-align:center' colwidth='25'><?php echo i18n('Guadagno'); ?></th>
</tr>
<?php
for($c=0; $c < count($list); $c++)
{
 $item = $list[$c];
 echo "<tr>";
 echo "<td>".date('d/m/Y',strtotime($item['ctime']))."</td>"; 
 echo "<td>".($item['code'] ? $item['code'] : "&nbsp;")."</td>";
 echo "<td>".($item['name'] ? $item['name'] : "&nbsp;")."</td>";
 echo "<td align='center'>".$item['qty']."</td>";
 echo "<td align='right'>".number_format($item['amount'], 2, ",",".")." &euro;</td>";
 echo "<td align='right'>".number_format($item['profit'], 2, ",",".")." &euro;</td></tr>";
}
?>
</table>

<div class="totals-footer">
 <table width="100%" cellspacing="0" cellpadding="0" border="0">
  <tr><td rowspan='2' valign='middle'>
		<input type='button' class='button-blue' value="<?php echo i18n('Print'); ?>" onclick="Print(this)"/>
		<input type='button' class='button-blue' value="<?php echo i18n('Export to Excel'); ?>" onclick="ExportToExcel(this)"/>
	  </td>
	  <td align='center'><span class='smalltext'><?php echo i18n('tot. qty'); ?></span></td>
	  <td align='right'><span class='smalltext'><?php echo i18n('Total amount'); ?></span></td>
	  <td align='right'><span class='smalltext'><?php echo i18n('Guadagno'); ?></span></td>
  </tr>
  <tr><td align='center'><span class='smalltext' id='foot-totqty'><?php echo $_TOT_QTY; ?></span></td>
	  <td align='right'><span class='bigtext' id='foot-totamount'><b><?php echo number_format($_TOT_AMOUNT,2,',','.'); ?> &euro;</b></span></td>
	  <td align='right'><span class='bigtext' id='foot-totprofit'><b><?php echo number_format($_TOT_PROFIT,2,',','.'); ?> &euro;</b></span></td>
  </tr>
 </table>
</div>

<?php
/*-------------------------------------------------------------------------------------------------------------------*/
$template->Footer();

?>
<script>
var ON_PRINTING = false;
var ON_EXPORT = false;
var FILTER = "<?php echo $_FILTER; ?>";

Template.OnExit = function(){
	document.location.href = ABSOLUTE_URL;
	return false;
}

Template.OnInit = function(){
	this.initBtn(document.getElementById('menubutton'), "popupmenu");
	this.initEd(document.getElementById("datefrom"), "date");
	this.initEd(document.getElementById("dateto"), "date").OnDateChange = function(date){
		 changeRange("custom");
		};
	this.initEd(document.getElementById('filterselect'), "dropdown").onchange = function(){
		 Template.SERP.setVar("filter",this.getValue());
		 Template.SERP.setVar("ap","");
		 Template.SERP.setVar("id",0);
		 Template.SERP.setVar("subjid",0);
		 Template.SERP.setVar("search","");
		 Template.SERP.setVar("usrid",0);
		 Template.SERP.reload(0);
		};
	
	switch(FILTER)
	{
	 case 'catalog' : {
		this.initEd(document.getElementById("search"), "archivefind").onchange = function(){
			 Template.SERP.setVar("subjid",0);
			 var ap = this.value ? this.getAP() : "";
			 Template.SERP.setVar("search", this.value);
			 Template.SERP.setVar("ap",ap ? ap : "");
			 Template.SERP.setVar("id",0);
			 Template.SERP.reload(0);
			};
		} break;
     case 'product' : {
		this.initEd(document.getElementById("search"), "gmart").OnSearch = function(){
			 Template.SERP.setVar("subjid",0);
			 if(this.value && this.data)
			 {
			  Template.SERP.setVar("search", this.data['name']);
			  Template.SERP.setVar("ap",this.data['ap']);
			  Template.SERP.setVar("id",this.data['id']);
			  Template.SERP.reload(0);
			 }
			 else
			 {
			  Template.SERP.setVar("search","");
			  Template.SERP.setVar("ap","");
			  Template.SERP.setVar("id",0);
			  Template.SERP.reload(0);
			 }
			};
		} break;
	 case 'subject' : {
		this.initEd(document.getElementById("search"), "contactextended").OnSearch = function(){
			 Template.SERP.setVar("ap","");
			 Template.SERP.setVar("id",0);
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
		} break;
	 case 'user' : {
		this.initEd(document.getElementById("search"), "userfind").onchange = function(){
			 var uid = this.value ? this.getId() : 0;
			 Template.SERP.setVar("search", this.value);
			 Template.SERP.setVar("ap","");
			 Template.SERP.setVar("id",0);
			 Template.SERP.setVar("usrid",uid);
			 Template.SERP.setVar("subjid",0);
			 Template.SERP.reload(0);
			};
		} break;
	}

	this.initBtn(document.getElementById("searchbtn")).onclick = function(){document.getElementById("search").OnSearch();}

	this.SERP = new SERP("<?php echo $_SERP->OrderBy; ?>", "<?php echo $_SERP->OrderMethod; ?>", "<?php echo $_SERP->RPP; ?>", "<?php echo $_SERP->PG; ?>");
	this.initSortableTable(document.getElementById("summarylist"), this.SERP.OrderBy, this.SERP.OrderMethod).OnSort = function(field, method){
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

function changeRange(range)
{
 if(range == "custom")
 {
  Template.setVar("from",document.getElementById("datefrom").isodate);
  Template.setVar("to",document.getElementById("dateto").isodate);
 }
 else
 {
  Template.unsetVar("from");
  Template.unsetVar("to");
 }

 Template.setVar("range",range);
 Template.reload();
}

function Print(printBtn)
{
 if(ON_PRINTING)
  return alert("<?php echo i18n('Wait until the process of export to PDF has finished.'); ?>");

 printBtn.disabled = true;
 ON_PRINTING = true;

 var dateFrom = new Date();
 var dateTo = new Date();

 dateFrom.setFromISO(document.getElementById("datefrom").isodate);
 dateTo.setFromISO(document.getElementById("dateto").isodate);

 var doc = new GnujikoPrintableDocument("Riepilogo vendite da ordini evasi", "A4");
 var header = "<div style='width:190mm' class='defaultheader'><h3>Riepilogo vendite da ordini evasi - <?php echo i18n('from'); ?> "+dateFrom.printf('d/m/Y')+" <?php echo i18n('to'); ?> "+dateTo.printf('d/m/Y')+"</h3></div>";
 doc.setDefaultPageHeader(header);

 var footer = "<div style='width:190mm;margin-top:10mm' class='defaultfooter'>";
 footer+= "<table width='100%' cellspacing='0' cellpadding='0' border='0' class='footertable'>";
 footer+= "<tr><td style='width:120mm'>Pag.</td>";
 footer+= "<td style='width:30mm;text-align:center'><?php echo i18n('tot. qty'); ?></td>";
 footer+= "<td style='width:30mm;text-align:center'><?php echo i18n('Total amount'); ?></td>";
 footer+= "<td style='width:30mm;text-align:center'><?php echo i18n('Guadagno'); ?></td></tr>";

 footer+= "<tr><td>{PGC}</td>";
 footer+= "<td style='text-align:center'>"+document.getElementById('foot-totqty').innerHTML+"</td>";
 footer+= "<td style='text-align:center'>"+document.getElementById('foot-totamount').innerHTML+"</td>";
 footer+= "<td style='text-align:center'>"+document.getElementById('foot-totprofit').innerHTML+"</td></tr>";

 footer+= "</table></div>";

 doc.setDefaultPageFooter(footer);
 doc.includeCSS("var/objects/printmanager/printabletable.css");
 var gpt = new GnujikoPrintableTable(document.getElementById('summarylist'),true,true);
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

function ExportToExcel(btn)
{
 var cmd = "<?php echo $_CMD; ?> --order-by `<?php echo $_SERP->OrderBy.' '.$_SERP->OrderMethod; ?>`";
 cmd+= " || tableize *.items";
 cmd+= " -k `ctime,code,brand,model,name,barcode,serial_number,lot,qty,units,price,amount,vencode,vendor_price,sale_price,variant_coltint,variant_sizmis`";

 cmd+= " -n `DATA|CODICE|MARCA|MODELLO|DESCRIZIONE|BARCODE|SERIAL NUMBER|LOTTO|QTA|U.M.|PR. UNIT|TOTALE|COD. ART. FORN.|PR. ACQUISTO|PR. VENDITA|COLORE|MISURA`";

 cmd+= " -f `date|string|string|string|string|string|string|string|number|string|currency|currency|string|currency|currency|string|string`";

 var sh = new GShell();
 sh.showProcessMessage("Esportazione in Excel", "Attendere prego, &egrave; in corso l&lsquo;esportazione su file Excel.");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(o,a){
	 var sh2 = new GShell();
	 sh2.OnError = function(err){sh.processMessage.error(err);}
	 sh2.OnOutput = function(o,a){
		 sh.hideProcessMessage();
		 if(!a) return;
		 var fileName = a['filename'];
		 document.location.href = ABSOLUTE_URL+"getfile.php?file="+fileName;
		}
	 sh2.sendCommand("gframe -f excel/export -params `file=riepilogo_vendite_da_ordini_evasi` --use-cache-contents -c `"+o+"`");
	}

 sh.sendCommand(cmd);
}

</script>
<?php

$template->End();

?>
