<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 27-05-2017
 #PACKAGE: backoffice2
 #DESCRIPTION: BackOffice 2 - Statistiche sul venduto diviso per zona.
 #VERSION: 2.0beta
 #CHANGELOG: 
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
$template->includeInternalObject("contactsearch");
$template->includeCSS("stats.css");

$template->Begin(i18n('Sales stats by zone'));

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


$centerContents = "<div class='titleblock' style='width:550px;float:left'>".i18n('Sales stats by zone')."</div>";

$centerContents.= "<input type='text' class='calendar' value='".date('d/m/Y',$dateFrom)."' id='datefrom' style='margin-left:30px'/>";
$centerContents.= "<span class='smalltext'> ".i18n('to')." </span> <input type='text' class='calendar' value='".date('d/m/Y',$dateTo)."' id='dateto'/>";

$template->Header("search", $centerContents, "BTN_EXIT", 800);

$template->SubHeaderBegin(10);

/* MAIN CHART */
if(!$_REQUEST['rvf'])
 $_REQUEST['rvf'] = "qty";
if(!$_REQUEST['cht'])
 $_REQUEST['cht'] = "bg";

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

 <td>&nbsp;</td>

 <td width='400'><span class='smalltext'>mostra grafico:</span>
	<input type='radio' class='radio' name='rvf' value='qty' <?php if($_REQUEST['rvf'] == "qty") echo "checked='true'"; ?> onclick="rvfChange(this)"/><span class='smalltext'><?php echo i18n('of quantity'); ?></span>
	<input type='radio' class='radio' name='rvf' value='amount' <?php if($_REQUEST['rvf'] == "amount") echo "checked='true'"; ?> onclick="rvfChange(this)"/><span class='smalltext'><?php echo i18n('of amount'); ?></span>
	</td>

 <td width='60'>&nbsp;
	<?php //$_SERP->DrawSerpButtons(true);
 
//---------------------------------------------//
$template->SubHeaderEnd();

$template->Body("default",800);

/*-------------------------------------------------------------------------------------------------------------------*/
include_once($_BASE_PATH."var/objects/gchart/gchart.php");

$_CMD = "commercialdocs stats-by-zone -rvf '".$_REQUEST['rvf']."'";
if($dateFrom)	$_CMD.= " -from '".date('Y-m-d',$dateFrom)."'";
if($dateTo)		$_CMD.= " -to '".date('Y-m-d',$dateTo)."'";

$ret = GShell($_CMD);
$items = $ret['outarr']['items'];
$chart = new GChart(800,280, "bpg");
$chart->HideBackground();
$chart->HideLegend();
$maxItems = 9;
$length = (count($items) > $maxItems) ? $maxItems : count($items);
for($c=0; $c < $length; $c++)
{
 $chart->AddLabel($items[$c]['province']);
 switch($_REQUEST['rvf'])
 {
  case 'qty' : $chart->AddValue($items[$c]['qty']); break;
  case 'amount' : $chart->AddValue($items[$c]['amount']); break;
 }
}
if(count($items)>0)
 echo "<div class='chart-container' id='chart-background' style=\"background: url('".$chart->Paint(true)."') top left no-repeat;\"></div>";
else
 echo "<div class='chart-container' id='chart-background' style=\"background: url('img/chart-error.png') center center no-repeat;\"><br/><br/><br/><br/>Nessun dato da visualizzare</div>";
?>

<div class="totals-footer">
 <div class="title"><?php echo i18n('Summary of sales')." ".i18n('from')." ".date('d/m/Y',$dateFrom)." ".i18n('to')." ".date('d/m/Y',$dateTo); ?></div>
 <div class="contents" style="width:750px;overflow:auto;">
  <table cellspacing="0" cellpadding="0" border="0" class="standardtable" width="100%">
   <tr><th width='40'>&nbsp;</th>
	   <th style='text-align:left'>PROVINCIA</th>
	   <th style='text-align:center'>QTA ART. VENDUTI</th>
	   <th style='text-align:right'>TOT. IMPORTO VENDUTO</th>
   </tr>
   <?php
	for($c=0; $c < count($items); $c++)
	{
	 $item = $items[$c];
	 echo "<tr><td align='center'>".($c+1).".</td>";
	 echo "<td>".($item['province'] ? $item['province'] : '<i>non specificata</i>')."</td>";
	 echo "<td align='center'>".$item['qty']."</td>";
	 echo "<td align='right'>".number_format($item['amount'],2,',','.')."</td></tr>";
	}
   ?>
  </table>
 </div>
</div>
<br/><br/><br/><br/><br/><br/><br/><br/>
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
		 changeRange("custom");
		};
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

function chtChange(inp)
{
 var div = document.getElementById("chart-background");
 div.style.backgroundImage = div.style.backgroundImage.replace("cht="+Template.getVar("cht"), "cht="+inp.value);
 Template.setVar("cht",inp.value);
}

function rvfChange(inp)
{
 Template.setVar("rvf",inp.value);
 Template.reload();
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

</script>
<?php

$template->End();

?>
