<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 27-08-2014
 #PACKAGE: backoffice2
 #DESCRIPTION: BackOffice 2 - Classifica prodotti più venduti
 #VERSION: 2.3beta
 #CHANGELOG: 27-08-2014 : restricted access integration.
			 15-04-2014 : Bug fix.
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
$template->includeInternalObject("contactsearch");
$template->includeCSS("topcharts.css");

$template->Begin(i18n('Top charts'));

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

/* MAIN CHART */
if(!$_REQUEST['rvf'])
 $_REQUEST['rvf'] = "amount";


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

$centerContents = "<div class='titleblock' style='width:550px;float:left'>".i18n('Ranking best sellers')."</div>";

$centerContents.= "<input type='text' class='calendar' value='".date('d/m/Y',$dateFrom)."' id='datefrom' style='margin-left:30px'/>";
$centerContents.= "<span class='smalltext'> ".i18n('to')." </span> <input type='text' class='calendar' value='".date('d/m/Y',$dateTo)."' id='dateto'/>";

$template->Header("search", $centerContents, "BTN_EXIT", 800);

$template->SubHeaderBegin(10);


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

 <td width='550' align='right'><span class='smalltext'><?php echo i18n('show chart of:'); ?></span>
	<input type='radio' class='radio' name='rvf' value='qty' <?php if($_REQUEST['rvf'] == "qty") echo "checked='true'"; ?> onclick="rvfChange(this)"/><span class='smalltext'><?php echo i18n('of quantity'); ?></span>
	<input type='radio' class='radio' name='rvf' value='amount' <?php if($_REQUEST['rvf'] == "amount") echo "checked='true'"; ?> onclick="rvfChange(this)"/><span class='smalltext'><?php echo i18n('of amount'); ?></span>
	</td>


 <td>&nbsp;
	<?php //$_SERP->DrawSerpButtons(true);
 
//---------------------------------------------//
$template->SubHeaderEnd();

$template->Body("default",800);

/*-------------------------------------------------------------------------------------------------------------------*/
include_once($_BASE_PATH."var/objects/gchart/gchart.php");

//$chart = new GChart(800,280,$_REQUEST['cht']);
//$chart->HideBackground();

$ret = GShell("stats get -service sales -from ".date('Y-m-d',$dateFrom)." -to ".date('Y-m-d',$dateTo)." -return '".$_REQUEST['rvf']."'");
$sections = $ret['outarr']['sections'];
$columns = $ret['outarr']['columns'];
$results = $ret['outarr']['results'];

?>
<div class="topcharts-container">
<?php

if(!count($sections))
 echo "<h3>Nessun dato da visualizzare</h3>";

$f = $_REQUEST['rvf'];
for($c=0; $c < count($sections); $c++)
{
 $sec = $sections[$c];
 if($sec['ap'] == "UNDEFINED")
  continue;

 $list = $ret['outarr']['itemsbysec'][$sec['ap']][$sec['id']];

 $tmp = array();
 for($i=0; $i < count($list); $i++)
  $tmp[$list[$i]] = $results[$sec['ap']]['itm'][$list[$i]]['totals'][$f];
 arsort($tmp);

 $total = $results[$sec['ap']]['sec'][$sec['id']]['totals'][$f];
 $mul = $total ? (100/$total) : 0;
 $items = array();
 $idx = 0;
 $tot = 0;
 $db = new AlpaDatabase();
 while(list($k,$v) = each($tmp))
 {
  $db->RunQuery("SELECT name FROM dynarc_".$sec['ap']."_items WHERE id='".$k."'");
  $db->Read();
  $items[] = array("id"=>$k, "name"=>($db->record['name'] ? $db->record['name'] : "senza nome"), "value"=>$v, "perc"=>$v*$mul);
  $idx++;
  $tot+= $v;
  if($idx == 4)
   break;
 }
 $db->Close();
 // aggiunge il resto //
 if($tot != $total)
  $items[] = array("id"=>0, "name"=>"Altro...", "value"=>($total-$tot), "perc"=>($total-$tot)*$mul);


 $chart = new GChart(280,280,"bpg");
 $chart->HideBackground();
 $chart->HideLegend();
 $chart->HideLabels();
 for($i=0; $i < count($items); $i++)
 {
  $chart->AddLabel($items[$i]['name']);
  $chart->AddValue($items[$i]['value']);
 }

 ?>
 <div class='topchart-section'>
  <div class='topchart-title'><?php echo $sec['name']; ?></div>
  <div class='topchart-pie' style="background: url('<?php echo $chart->Paint(true); ?>') center center no-repeat;"></div>
  <div class='topchart-list'>
   <table width='100%' cellspacing='0' cellpadding='0' border='0' class='topchart-items'>
	<?php
	for($i=0; $i < count($items); $i++)
	{
	 $itm = $items[$i];
	 echo "<tr><td class='pos'>".($i+1).".</td><td>".$itm['name']."</td><td align='right'>".sprintf("%.2f",$itm['perc'])."%</td></tr>";
	}
	?>
   </table>
  </div>
 </div>
 <?php 
}
?>
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
