<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 
 #PACKAGE: 
 #DESCRIPTION: 
 #VERSION: 
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

$template->Begin("BackOffice");

$dateFrom = $_REQUEST['from'] ? $_REQUEST['from'] : date("Y-m")."-01";
$dateTo = $_REQUEST['to'] ? $_REQUEST['to'] : date("Y-m-d",strtotime("+1 month",strtotime($dateFrom)));

$centerContents = "<input type='text' class='contact' style='width:390px;float:left' placeholder='Cerca per cliente...' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\" modal='extended' fields='code_str,name' contactfields='phone,phone2,cell,email'/><input type='button' class='button-search' id='searchbtn' connect='search'/>";
$centerContents.= "<input type='text' class='calendar' value='".date('d/m/Y',strtotime($dateFrom))."' id='datefrom' style='margin-left:30px'/>";
$centerContents.= "<span class='smalltext'> al </span> <input type='text' class='calendar' value='".date('d/m/Y',strtotime($dateTo))."' id='dateto'/>";

$template->Header("search", $centerContents, "BTN_EXIT");

$_ORDER_BY = $_REQUEST['sortby'] ? $_REQUEST['sortby'] : "name";
$_ORDER_METHOD = $_REQUEST['sortmethod'] ? strtoupper($_REQUEST['sortmethod']) : "ASC";
$_RPP = $_REQUEST['rpp'] ? $_REQUEST['rpp'] : 5;
$_PG = $_REQUEST['pg'] ? $_REQUEST['pg'] : 1;

$_SERP = new SERP();
$_SERP->setOrderBy($_ORDER_BY);
$_SERP->setOrderMethod($_ORDER_METHOD);
$_SERP->setResultsPerPage($_RPP);
$_SERP->setCurrentPage($_PG);

if(!$_REQUEST['show'])
 $_REQUEST['show'] = "thismonth";

$template->SubHeaderBegin();
/*?>
 &nbsp;</td>
 <td width='480'><ul class='toggles'><?php
	  $show = array("thismonth"=>"Questo mese", "thisyear"=>"mesi precedenti", "lastyear"=>"Anno scorso", "fromstart"=>"Dall'inizio attività");
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
 	 ?></ul>
 </td><td>
	<?php $_SERP->DrawSerpButtons(true);
 
//---------------------------------------------//*/
$template->SubHeaderEnd();

$template->Body("default",700);

/*-------------------------------------------------------------------------------------------------------------------*/
$ret = GShell("backoffice get-sales -from '".$dateFrom."' -to '".$dateTo."'");
$results = $ret['outarr'];
/*-------------------------------------------------------------------------------------------------------------------*/
?>
<h3>Analisi sul fatturato dal <?php echo date('d.m.Y',strtotime($dateFrom)); ?> al <?php echo date('d.m.Y',strtotime($dateTo)); ?></h3>
<table style='width:100%' cellspacing='0' cellpadding='0' border='0' class='collapsetable' id='dailylist'>
<?php
while(list($k,$v) = each($results['dates']))
{
 $date = substr($k,0,4)."-".substr($k,4,2)."-".substr($k,6,2);
 $documents = $v['documents'];
 $totals = $v['totals'];

 $dt = strtotime($date);

 echo "<tr id='date-".$date."' class='collapsed".((date('N',$dt) == 7) ? " sunday" : "")."'><td class='icon'><img src='img/document.png'/></td>";
 echo "<td class='title'><b>".date('d',$dt)." ".i18n('DAY-'.date('N',$dt))."</b></td>";
 echo "<td class='currency'>".number_format($totals['netpay'],2,",",".")." &euro;</td></tr>";
 echo "<tr class='container'><td colspan='3'>&nbsp;</td></tr>";
}
?>
</table>

<div class="totals-footer">
 <table width="100%" cellspacing="0" cellpadding="0" border="0">
  <tr><td rowspan='2' valign='middle'><input type='button' class='button-blue' value='Stampa'/></td>
	  <td align='center'><span class='smalltext'>tot. imponibile</span></td>
	  <td align='center'><span class='smalltext'>tot. IVA</span></td>
	  <td align='right'><span class='smalltext'>Totale fatturato</span></td></tr>
  <tr><td align='center'><span class='smalltext'><?php echo number_format($results['tot_amount'],2,',','.'); ?> &euro;</span></td>
	  <td align='center'><span class='smalltext'><?php echo number_format($results['tot_vat'],2,',','.'); ?> &euro;</span></td>
	  <td align='right'><span class='bigtext'><b><?php echo number_format($results['tot_netpay'],2,',','.'); ?> &euro;</b></span></td></tr>
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
	this.SERP = new SERP("<?php echo $_SERP->OrderBy; ?>", "<?php echo $_SERP->OrderMethod; ?>", "<?php echo $_SERP->RPP; ?>", "<?php echo $_SERP->PG; ?>");
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
	this.initBtn(document.getElementById("searchbtn"));

	/* DAILY LIST */
	var DailyList = this.initCollapseTable(document.getElementById('dailylist'));
	DailyList.OnExpand = function(r){
	 if(!r.layer)
	 {
	  r.layer = new Layer();
	  r.layer.load("glight/dailysales","date="+r.id.substr(5),r.container,true);
	 }
	}

	DailyList.OnCollapse = function(r){}

	for(var c=0; c < DailyList.rows.length; c++)
	{
	 var r = DailyList.rows[c];
	 if(r.className == "lastrow")
	  return;
	 Template.initCollapseTableRow(r,DailyList);
	 c++;
	}
	/* EOF - DAILY LIST */
}

function setShow(value)
{
 Template.SERP.setVar("show",value);
 Template.SERP.reload(0);
}
</script>
<?php

$template->End();

?>
