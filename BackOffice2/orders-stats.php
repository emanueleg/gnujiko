<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 16-03-2017
 #PACKAGE: backoffice2
 #DESCRIPTION: BackOffice 2 - Statistiche vendite su ordini evasi
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

$template->Begin("Statistiche ordini evasi");

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


$centerContents = "<div class='titleblock' style='width:550px;float:left'>".i18n('Sales stats')."</div>";

$centerContents.= "<input type='text' class='calendar' value='".date('d/m/Y',$dateFrom)."' id='datefrom' style='margin-left:30px'/>";
$centerContents.= "<span class='smalltext'> ".i18n('to')." </span> <input type='text' class='calendar' value='".date('d/m/Y',$dateTo)."' id='dateto'/>";

$template->Header("search", $centerContents, "BTN_EXIT", 800);

$template->SubHeaderBegin(10);

/* MAIN CHART */
if(!$_REQUEST['rvf'])
 $_REQUEST['rvf'] = "amount";
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

 <td width='400'><span class='smalltext'><?php echo i18n('show results:'); ?></span>
	<input type='radio' class='radio' name='rvf' value='qty' <?php if($_REQUEST['rvf'] == "qty") echo "checked='true'"; ?> onclick="rvfChange(this)"/><span class='smalltext'><?php echo i18n('of quantity'); ?></span>
	<input type='radio' class='radio' name='rvf' value='amount' <?php if($_REQUEST['rvf'] == "amount") echo "checked='true'"; ?> onclick="rvfChange(this)"/><span class='smalltext'><?php echo i18n('of amount'); ?></span>
	</td>

 <td width='200'><span class='smalltext'><?php echo i18n('chart type:'); ?></span>
	<input type='radio' name='cht' value='bg' <?php if($_REQUEST['cht'] == "bg") echo "checked='true'"; ?> onclick="chtChange(this)"/>
  	 <img src="img/chart_bar.png"/>
  	<input type='radio' name='cht' value='cc' <?php if($_REQUEST['cht'] == "cc") echo "checked='true'"; ?> onclick="chtChange(this)"/>
  	 <img src="img/chart_line.png"/>
 </td>

 <td>&nbsp;
	<?php //$_SERP->DrawSerpButtons(true);
 
//---------------------------------------------//
$template->SubHeaderEnd();

$template->Body("default",800);

/*-------------------------------------------------------------------------------------------------------------------*/
include_once($_BASE_PATH."var/objects/gchart/gchart.php");

$chart = new GChart(800,280,$_REQUEST['cht']);
$chart->HideBackground();

$ret = GShell("stats get -service orders -from ".date('Y-m-d',$dateFrom)." -to ".date('Y-m-d',$dateTo)." -return '".$_REQUEST['rvf']."'");
$sections = $ret['outarr']['sections'];
$columns = $ret['outarr']['columns'];
for($c=0; $c < count($columns); $c++)
 $chart->AddLabel($columns[$c]['title']);
$totValues = 0;
for($c=0; $c < count($sections); $c++)
{
 $chart->AddSection($sections[$c]['name']);
 for($i=0; $i < count($sections[$c]['values']); $i++)
 {
  $chart->AddValue($sections[$c]['values'][$i], $sections[$c]['name']);
  $totValues+= $sections[$c]['values'][$i];
 }
}
/* FINE */

if(count($sections) && $totValues)
 echo "<div class='chart-container' id='chart-background' style=\"background: url('".$chart->Paint(true)."') top left no-repeat;\"></div>";
else
 echo "<div class='chart-container' id='chart-background' style=\"background: url('img/chart-error.png') center center no-repeat;\"><br/><br/><br/><br/>Nessun dato da visualizzare</div>";

?>

<div class="totals-footer">
 <div class="title"><?php echo i18n('Total'); ?> <?php echo ($_REQUEST['rvf'] == "qty") ? i18n("of quantity sales") : i18n('sales'); ?> 
	<?php echo i18n('from'); ?> <?php echo date('d/m/Y',$dateFrom); ?> <?php echo i18n('to'); ?> <?php echo date('d/m/Y',$dateTo); ?></div>
 <div class="contents">
  <table width="100%" cellspacing="0" cellpadding="0" border="0" class="macrosectrend">
  <?php
  $rows = ceil(count($sections)/4);
  $idx = 0;
  for($r=0; $r < $rows; $r++)
  {
   if($r > 0)
    echo "<tr><td colspan='4'>&nbsp;</td></tr>";

   echo "<tr>";
   $startIdx = $idx;
   for($c=0; $c < 4; $c++)
   {
	if(!isset($sections[$idx]))
	 break;
    echo "<th>".$sections[$idx]['name']."</th>";
    $idx++;
   }
   echo "</tr>";
   echo "<tr>";
   $idx = $startIdx;
   for($c=0; $c < 4; $c++)
   {
	if(!isset($sections[$idx]))
	 break;
    $ap = $sections[$idx]['ap'];
    $sec = $sections[$idx]['id'];
    $value = $sections[$idx]['totals'];
    $trend = round($sections[$idx]['trend']);
    echo "<td><span class='bigvalue'>".(($_REQUEST['rvf'] == "qty") ? $value : number_format($value,2,",",".")." &euro;")."</span>";
    if($trend < 0)
	 echo "<em class='ratedown'>".(-$trend)."%</em>";
    else
     echo "<em class='rateup'>".$trend."%</em>";
    echo "</td>";
    $idx++;
   }
   echo "</tr>";
  }
  ?>
  </table>
 </div>
</div>

<div class="panel" style="margin:3px">
 <div class="title"><?php echo i18n('Summary of sales')." ".i18n('from')." ".date('d/m/Y',$dateFrom)." ".i18n('to')." ".date('d/m/Y',$dateTo); ?></div>
 <div class="contents" style="width:750px;overflow:auto;">
  <table cellspacing="0" cellpadding="0" border="0" class="standardtable" width="100%">
  <tr><th style='text-align:left;'><?php echo i18n('Section'); ?></th>
	  <?php
	  for($c=0; $c < count($columns); $c++)
	   echo "<th style='text-align:center'>".$columns[$c]['title']."</th>";
	  ?>
  </tr>
  <?php
  for($c=0; $c < count($sections); $c++)
  {
   $ap = $sections[$c]['ap'];
   $sec = $sections[$c]['id'];
   echo "<tr><td>".$sections[$c]['name']."</td>";
   for($i=0; $i < count($sections[$c]['values']); $i++)
   {
	$value = $sections[$c]['values'][$i] ? $sections[$c]['values'][$i] : 0;
	echo "<td align='right'>".(($_REQUEST['rvf'] == "qty") ? $value : number_format($value,2,",",".")." &euro;")."</td>";
   }
   echo "</tr>";
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
