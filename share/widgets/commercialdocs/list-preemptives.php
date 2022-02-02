<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-01-2013
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Preemptive list
 #VERSION: 2.2beta
 #CHANGELOG: 13-01-2013 : Bug fix in assign group at every new items.
 #TODO: Internazionalizzare (i18n).
 
*/

include_once($_BASE_PATH."var/objects/htmlgutility/menu.php");
include_once($_BASE_PATH."var/objects/editsearch/index.php");
include_once($_BASE_PATH."include/layers.php");

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/common.css" type="text/css" />

<table width='100%' border='0' cellspacing='0' cellpadding='0'>
<tr><td valign='middle' class='title' width='200'><?php if($_REQUEST['show'] == "trash") echo "Preventivi cestinati"; else echo "Elenco dei preventivi"; ?></td>
	<td valign='middle' width='240'><ul class='basicbuttons'>
		 <?php
		 if($_REQUEST['show'] == "trash")
		  echo "<li><span href='#' onclick='emptyTrash()'><img src='".$_ABSOLUTE_URL."share/widgets/commercialdocs/img/emptytrash.png' border='0'/> Svuota il cestino</span></li>";
		 else
		  echo "<li><span href='#' onclick='newDoc()'><img src='".$_ABSOLUTE_URL."share/widgets/commercialdocs/img/add.gif' border='0'/> Crea un nuovo preventivo</span></li>";
		 ?>
	</ul></td>
	<td>	 
	<ul class='basicmenu' id='mainmenu'>
	  <li class='blue' id='selectionmenu' style='visibility:hidden;'><span><img src="<?php echo $_ABSOLUTE_URL; ?>var/templates/basicapp/img/checkbox.png" border='0'/>Selezionati</span>
		<ul class="submenu">
		 <?php
		 if($_REQUEST['show'] == "trash")
		  echo "<li onclick='restoreSelected()'>Ripristina selezionati</li>";
		 else
		  echo "<li onclick='unselectAll(true)'>Annulla selezione</li>";
		 ?>
		 <li class='separator'></li>
		 <?php
		 if($_REQUEST['show'] == "trash")
		  echo "<li onclick='deleteSelectedDocuments(true)'><img src='".$_ABSOLUTE_URL."share/icons/16x16/delete.gif'/>Elimina dal cestino</li>";
		 else
		  echo "<li onclick='deleteSelectedDocuments()'><img src='".$_ABSOLUTE_URL."share/icons/16x16/delete.gif'/>Elimina selezionati</li>";
		 ?>
		</ul>
	  </li>
	 </ul>
	</td>	
</tr>
</table>

<?php
$ret = GShell("dynarc cat-info -ap `commercialdocs` -tag preemptives",$_REQUEST['sessid'],$_REQUEST['shellid']);
$catInfo = $ret['outarr'];

$ret = GShell("dynarc item-list -ap `commercialdocs` -ct preemptives -limit 1",$_REQUEST['sessid'],$_REQUEST['shellid']);
$numOfDocuments = $ret['outarr']['count'];

$ret = GShell("dynarc item-list -ap `commercialdocs` -ct preemptives -where `status=0` -limit 1",$_REQUEST['sessid'],$_REQUEST['shellid']);
$numOfDocsToSend = $ret['outarr']['count'];

$ret = GShell("dynarc trash list -ap commercialdocs -where cat_id=".$catInfo['id'],$_REQUEST['sessid'], $_REQUEST['shellid']);
$trashInfo = $ret['outarr'];
?>

<table width='100%' border='0' cellspacing='0' cellpadding='0' style="margin-top: 10px;">
<tr><td valign='top'>
	<ul class='maintab' id='maintab'>
	 <li class="<?php echo !$_REQUEST['show'] ? 'selected' : 'first'; ?>"><div><span class='title' onclick="showPage()">Tutti i preventivi</span><br/><em>Tot. doc:</em><span class="gray right"><?php echo $numOfDocuments; ?></span></div></li>
	 <li class="<?php echo ($_REQUEST['show'] == 'tosend') ? 'selected' : ''; ?>"><div><span class='title' onclick="showPage('tosend')">Da inviare</span><br/><em>N.</em><span class="gray right"><?php echo $numOfDocsToSend; ?></span></div></li>
	 <li class="<?php echo ($_REQUEST['show'] == 'trash') ? 'selected' : 'last'; ?>"><div><span class='title' onclick="showPage('trash')">Cestinati</span><br/><em>N.</em><span class="gray right"><?php echo count($trashInfo['items']); ?></span></div></li>
	</ul>
	</td><td valign='middle' align='right' style="border-bottom: 1px solid #dedede;border-collapse:collapse;">
	<input type='text' class='searchinput' id='subject' emptyvalue="Cerca per cliente" style="width:200px;margin-right:5px;" value="<?php echo $_REQUEST['subjectname']; ?>"/>
	</td><td width='90' style="border-bottom: 1px solid #dedede;border-collapse:collapse;">
	<ul class='basicbuttons'>
		 <li><span href='#' onclick="updateSearch()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/img/search.gif" border='0'/> Cerca</span></li>
	</ul>
	</td></tr>

<tr><td colspan='3' style="border-left:1px solid #dedede;border-bottom:1px solid #dedede;border-right:1px solid #dedede;border-collapse:collapse;">
	<table width='100%' class='itemlist' cellspacing='0' cellpadding='0' border='0'>
	<tr><th width='32'><input type='checkbox' onchange="selectAllRows(this)" id="tbselectall"/></th>
		<th width='190' style='text-align:left'>PREVENTIVO</th>
		<th style='text-align:left'>CLIENTE</th>
		<th width='100'>&nbsp;</th>
		<th width='100'>STATUS</th>
		<th width='106' style="text-align:left;">IMPORTO</th>
	</tr>
	</table>
	</td></tr>
</table>

<div id="itemlist-container" class="itemlist-container" style="height:<?php echo $_REQUEST['frameheight']-198; ?>px">
	<table width='100%' class='itemlist' id='itemlist' cellspacing='0' cellpadding='0' border='0'>
	<?php
	$subtot = 0;
	$subtotVI = 0;
	$where = "";
	if($_REQUEST['from'])
	 $where.= " AND ctime>='".$_REQUEST['from']."'";
	if($_REQUEST['to'])
	 $where.= " AND ctime<'".$_REQUEST['to']."'";
	if($_REQUEST['show'] == "tosend")
	 $where.= " AND status=0";
	else if($_REQUEST['show'] == "trash")
	 $where.= " AND trash=1";
	if($_REQUEST['year'] && !$_REQUEST['from'] && !$_REQUEST['to'])
	 $where.= " AND ctime>='".$_REQUEST['year']."-01-01' AND ctime<'".(($_REQUEST['year']+1)."-01-01")."'";
	if($_REQUEST['subjectid'])
	 $where.= " AND subject_id='".$_REQUEST['subjectid']."'";
	else if($_REQUEST['subjectname'])
	 $where.= " AND subject_name='".$_REQUEST['subjectname']."'";


	$rpp = $_REQUEST['limit'] ? $_REQUEST['limit'] : 20;
	$from = $_REQUEST['pg'] ? ($rpp*($_REQUEST['pg']-1)) : 0;
	
	$qry = "dynarc item-list -ap `commercialdocs` -ct preemptives -extget cdinfo".($where ? " -where `".ltrim($where," AND ")."`" : "")." --order-by `ctime ".($_REQUEST['sort']=='asc' ? "ASC" : "DESC").",code_num ".($_REQUEST['sort']=='asc' ? "ASC" : "DESC")."` -limit ".($from ? $from : "0").",".$rpp." --return-serp-info".($_REQUEST['show'] == "trash" ? " --include-trash" : "");

	//echo $qry;

	$ret = GShell("dynarc item-list -ap `commercialdocs` -ct preemptives -extget cdinfo".($where ? " -where `".ltrim($where," AND ")."`" : "")." --order-by `ctime ".($_REQUEST['sort']=='asc' ? "ASC" : "DESC").",code_num ".($_REQUEST['sort']=='asc' ? "ASC" : "DESC")."` -limit ".($from ? $from : "0").",".$rpp." --return-serp-info".($_REQUEST['show'] == "trash" ? " --include-trash" : ""),$_REQUEST['sessid'],$_REQUEST['shellid']);
	if(!$ret['error'])
	{
	 $count = $ret['outarr']['count'];
	 $list = $ret['outarr']['items'];
	 $serpInfo = $ret['outarr']['serpinfo'];
	}

	$months = array('Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre');

	$lastMonth = date('n');
	$lastYear = date('y');

	for($c=0; $c < count($list); $c++)
	{
	 $itm = $list[$c];
	 if(($lastMonth != date('n',$itm['ctime'])) || ($lastYear != date('Y',$itm['ctime'])))
	 {
	  $lastMonth = date('n',$itm['ctime']);
	  $lastYear = date('Y',$itm['ctime']);
	  echo "<tr class='label'><td colspan='100'><i>".$months[$lastMonth-1]." ".date('Y',$itm['ctime'])."</i></td></tr>";
	 }

	 echo "<tr id='".$itm['id']."'><td width='32' align='center'><input type='checkbox' onclick='selectRow(this)'/></td>";
	 echo "<td width='190'><a class='link' href='".$_ABSOLUTE_URL."GCommercialDocs/docinfo.php?id=".$itm['id']."' target='GCD-".$itm['id']."'>".html_entity_decode($itm['name'],ENT_QUOTES,"UTF-8")."</a></td>";
	 echo "<td><span class='subject'>".$itm['subject_name']."</span></td>";
	 echo "<td width='100'>";
	 if($_REQUEST['show'] == "trash")
	  echo "<span class='smallroundbtn' onclick='restoreDocument(".$itm['id'].")'>ripristina</span>";
	 else
	  echo "<span class='smallroundbtn' onclick='showDocumentOptions(".$itm['id'].",this)'>opzioni</span>";
	 echo "</td>";
	 echo "<td width='100'>";
	 switch($itm['status'])
	 {
	  case 1 : echo "<img src='".$_ABSOLUTE_URL."share/widgets/commercialdocs/img/status-print.png' class='status-icon'/><span class='status-small'>stampato il<br/>".date('d/m/Y',strtotime($itm['print_date']))."</span>"; break;

	  case 2 : echo "<img src='".$_ABSOLUTE_URL."share/widgets/commercialdocs/img/status-send.gif' class='status-icon'/><span class='status-small'>inviato il<br/>".date('d/m/Y',strtotime($itm['send_date']))."</span>"; break;

	  case 3 : echo "<img src='".$_ABSOLUTE_URL."share/widgets/commercialdocs/img/status-pending.gif' class='status-icon'/><span class='status-normal'><b>in attesa</b></span>"; break;

	  case 4 : echo "<img src='".$_ABSOLUTE_URL."share/widgets/commercialdocs/img/status-working.gif' class='status-icon'/><span class='status-normal'><b style='color:#013397'>in lavorazione</b></span>"; break;

	  case 5 : echo "<img src='".$_ABSOLUTE_URL."share/widgets/commercialdocs/img/status-suspended.gif' class='status-icon'/><span class='status-normal'><b style='color:#f44800'>sospeso</b></span>"; break;

	  case 6 : echo "<img src='".$_ABSOLUTE_URL."share/widgets/commercialdocs/img/status-failed.gif' class='status-icon'/><span class='status-normal'><b style='color:#d40000'>fallito</b></span>"; break;

	  case 7 : echo "<img src='".$_ABSOLUTE_URL."share/widgets/commercialdocs/img/status-completed.png' class='status-icon'/><span class='status-normal'><b style='color:#015a01'>completato</b></span>"; break;

	  case 8 : {
		 echo "<img src='".$_ABSOLUTE_URL."share/widgets/commercialdocs/img/status-converted.png' class='status-icon'/>";
		 if($itm['conv_doc_id'] && $itm['conv_doc_name'])
		  echo "<span class='status-xsmall'>convertito in<br/><a href='#' onclick='openDocument(".$itm['conv_doc_id'].")'>".$itm['conv_doc_name']."</a></span>";
		 else
		  echo "<span class='status-xsmall'>convertito in<br/>documento sconosciuto</span>";
		} break;

	  case 9 : {
		 echo "<img src='".$_ABSOLUTE_URL."share/widgets/commercialdocs/img/status-groupped.png' class='status-icon'/>";
		 if($itm['group_doc_id'] && $itm['group_doc_name'])
		  echo "<span class='status-xsmall'>raggruppato in<br/><a href='#' onclick='openDocument(".$itm['group_doc_id'].")'>".$itm['group_doc_name']."</a></span>";
		 else
		  echo "<span class='status-xsmall'>raggruppato in<br/>documento sconosciuto</span>";
		} break;

	  case 10 : echo "<span class='status-green'>pagato</span>"; break;
	  default : echo "<span class='status-open'><i>aperto</i></span>"; break;
	 }
	 echo "</td>";
	 echo "<td width='90' align='right'><b><em>&euro;</em>".number_format($itm['total'],2,',','.')."</b></td></tr>";
	 $subtot+= $itm['amount'];
	 $subtotVI+= $itm['total'];
	}
	?>
	</table>

	<div class="loading" style="display:none;" id="loading">&nbsp;</div>

	<div class="otherresultsbtn-div" align="center" id="otherresults" style="display:none;">
     <span class="otherresultsbtn" onclick="moreResults()">Mostra altri risultati</span>
    </div>

	<div class="footerresults" id="footerresults" style="display:none;">
    <?php
 	$rpp = 100;
 	$from = ($serpInfo['resultsperpage']*($serpInfo['currentpage']-1))+1;
 	$to = ($from+$rpp-1) > $count ? $count : ($from+$rpp-1);
 	?>
  	<span class='green'>Risultati: da </span><b id='resultsfrom'><?php echo $from; ?></b>
  	<span class='green'>a</span> <b id='resultsto'><?php echo $to; ?></b>
  	<span class='green'>su</span> <b><?php echo $count; ?></b>

  	<select style="float:right;margin-top:8px;" id="footerpagesel" onchange="pageChange(this.value)">
  	<?php
  	$pages = ceil($count/$serpInfo['resultsperpage']);
  	for($c=0; $c < $pages; $c++)
  	{
   	$from = ($serpInfo['resultsperpage']*$c)+1;
   	$to = ($from+$serpInfo['resultsperpage']-1) > $count ? $count : ($from+$serpInfo['resultsperpage']-1);
   	echo "<option value='".$c."'>Pg. ".($c+1)." - da ".$from." a ".$to."</option>";
  	}
  	?>
  	</select>
 	</div>

</div>

<table class="docfooter-results" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:10px;">
  <tr><th class="blue" rowspan="2" style="text-align: left;">
	   <div class="btn-filter" id="btn-filter" filterval="<?php echo $_REQUEST['ft'] ? $_REQUEST['ft'] : 'all'; ?>"><span><?php
		switch($_REQUEST['ft'])
		{
		 case 1 : case 2 : case 3 : case 4 : case 5 : case 6 : case 7 : case 8 : case 9 : case 10 : case 11 : case 12 : echo $months[$_REQUEST['ft']-1]; break;
		 case 'custom' : echo "Personalizzata..."; break;
		 default : echo "Tutti"; break;
		}
		?></span>
		<ul class='submenu' id="filter-list">
	 	 <li onclick="filterChange('all',this)">Tutti</li>
		 <li class='separator'>&nbsp;</li>
	 	 <li onclick="filterChange('1',this)">Gennaio</li>
	 	 <li onclick="filterChange('2',this)">Febbraio</li>
	 	 <li onclick="filterChange('3',this)">Marzo</li>
	 	 <li onclick="filterChange('4',this)">Aprile</li>
	 	 <li onclick="filterChange('5',this)">Maggio</li>
	 	 <li onclick="filterChange('6',this)">Giugno</li>
	 	 <li onclick="filterChange('7',this)">Luglio</li>
	 	 <li onclick="filterChange('8',this)">Agosto</li>
	 	 <li onclick="filterChange('9',this)">Settembre</li>
	 	 <li onclick="filterChange('10',this)">Ottobre</li>
	 	 <li onclick="filterChange('11',this)">Novembre</li>
	 	 <li onclick="filterChange('12',this)">Dicembre</li>
		 <li class='separator'>&nbsp;</li>
	 	 <li onclick="filterChange('custom',this)">Personalizzata...</li>
		</ul>
	   </div>

	   <div id="filter-year" <?php if($_REQUEST['ft'] == "custom") echo "style='display:none'"; ?>>anno: <input type='text' class='text' id='year' value="<?php echo $_REQUEST['from'] ? date('Y',strtotime($_REQUEST['from'])) : ($_REQUEST['year'] ? $_REQUEST['year'] : date('Y')); ?>" style="width:48px" onchange="updateSearch()"/></div>

	   <div id="filter-custom" <?php if($_REQUEST['ft'] != "custom") echo "style='display:none'"; ?>>
		dal: <input type='text' class='text' id='datefrom' value="<?php echo $_REQUEST['from'] ? date('d/m/Y',strtotime($_REQUEST['from'])) : ''; ?>" style="width:80px"/>&nbsp;&nbsp;
		al: <input type='text' class='text' id='dateto' value="<?php echo $_REQUEST['to'] ? date('d/m/Y',strtotime($_REQUEST['to'])) : ''; ?>" style="width:80px" onchange="updateSearch()"/>
	   </div>

	   <div id="filter-sort" sortval="<?php echo ($_REQUEST['sort']=='asc') ? 'asc' : 'desc'; ?>" onclick='sortChange(this)'>
		<?php
		if($_REQUEST['sort'] == "asc")
		 echo "<img src='".$_ABSOLUTE_URL."share/widgets/commercialdocs/img/sort-asc.png'/>";
		else
		 echo "<img src='".$_ABSOLUTE_URL."share/widgets/commercialdocs/img/sort-desc.png'/>";
	    ?>
	   </div>

	  </th>
	  <th class="blue" width="110">IMPONIBILE</th>
	  <th class="blue" width="110">I.V.A.</th>
	  <th class="green" width="110">TOTALE</th>
  </tr>
  <tr>
	  <td class="blue" id="doctot-amount"><em>&euro;</em><?php echo number_format($subtot,2,',','.'); ?></td>
	  <td class="blue" id="doctot-vat"><em>&euro;</em><?php echo number_format($subtotVI-$subtot,2,',','.'); ?></td>
	  <td class="green" id="doctot-total"><em>&euro;</em><?php echo number_format($subtotVI,2,',','.'); ?></td>
  </tr>
</table>

<div id='nuvcontainer' style="position:absolute;left:0px;top:0px;width:200px;display:block;visibility:hidden;"></div>
<script>
var SELECTED_ROWS = new Array();
var COUNT = <?php echo $count ? $count : "0"; ?>;
var RESULTS_PER_PAGE = <?php echo $serpInfo['resultsperpage'] ? $serpInfo['resultsperpage'] : "20"; ?>;
var CURRENT_PAGE = <?php echo $_REQUEST['pg'] ? $_REQUEST['pg'] : "1"; ?>;
var PAGES_COUNT = 1;
var lastMonth = <?php echo $lastMonth ? $lastMonth : "0"; ?>;
var lastYear = <?php echo $lastYear ? $lastYear : "0"; ?>;;
var MONTHS = new Array();
MONTHS.push("Gennaio");
MONTHS.push("Febbraio");
MONTHS.push("Marzo");
MONTHS.push("Aprile");
MONTHS.push("Maggio");
MONTHS.push("Giugno");
MONTHS.push("Luglio");
MONTHS.push("Agosto");
MONTHS.push("Settembre");
MONTHS.push("Ottobre");
MONTHS.push("Novembre");
MONTHS.push("Dicembre");


function bodyOnLoad()
{
 new GPopupMenu(document.getElementById('btn-filter'), document.getElementById('filter-list'));
 new GMenu(document.getElementById('mainmenu'));
 var mE = EditSearch.init(document.getElementById('subject'),
	"dynarc item-find -ap `rubrica` -field name `","` -limit 10 --order-by 'name ASC'",
	"id","name","items",true);
 if(!mE.value)
  mE.value = mE.getAttribute('emptyvalue');

 mE.onfocus = function(){
	 if(this.value == this.getAttribute('emptyvalue'))
	  this.value = "";
	}

 mE.onchange = function(){
	 if(!this.value)
	 {
	  this.value = this.getAttribute('emptyvalue');
	  return;
	 }
	}

 if(COUNT > (RESULTS_PER_PAGE * CURRENT_PAGE))
 {
  document.getElementById('loading').style.display="";
  window.setTimeout(function(){nextPage();},2000);
 }
 else if(COUNT)
 {
  document.getElementById('loading').style.display='none';
  document.getElementById('footerresults').style.display='';
  document.getElementById('footerresults').style.marginTop = 30;
  document.getElementById('footerpagesel').value = (CURRENT_PAGE-1);  
 }
 document.addEventListener ? document.addEventListener("mouseup",hideDocumentOptions,false) : document.attachEvent("onmouseup",hideDocumentOptions);
}

function nextPage()
{
 document.getElementById('loading').style.display="";

 var FROM = (RESULTS_PER_PAGE*CURRENT_PAGE)+1;
 var TO = (FROM+RESULTS_PER_PAGE-1) > COUNT ? COUNT : (FROM+RESULTS_PER_PAGE-1);

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 var total = parseCurrency(document.getElementById('doctot-amount').innerHTML.substr(10));
	 var totalVI = parseCurrency(document.getElementById('doctot-total').innerHTML.substr(10));

	 if(a && a['items'])
	 {
	  var tb = document.getElementById('itemlist');
	  var date = new Date();
	  for(var c=0; c < a['items'].length; c++)
	  {
	   var itm = a['items'][c];
	   date.setTime(parseFloat(itm['ctime'])*1000);

	   if((lastMonth != (date.getMonth()+1)) || (lastYear != date.getFullYear()))
	   {
	    lastMonth = date.getMonth()+1;
	    lastYear = date.getFullYear();
		var r = tb.insertRow(-1);
		r.className = "label";
		r.insertCell(-1).innerHTML = "<i>"+MONTHS[lastMonth-1]+" "+lastYear+"</i>";
		r.cells[0].colSpan=100;
	   }

	   var r = tb.insertRow(-1);
	   r.id = itm['id'];
	   r.insertCell(-1).innerHTML = "<input type='checkbox' onclick='selectRow(this)'/ >";
	   r.cells[0].style.width = "32px"; r.cells[0].style.textAlign='center';
	   r.insertCell(-1).innerHTML = "<a class='link' href='"+ABSOLUTE_URL+"GCommercialDocs/docinfo.php?id="+itm['id']+"' target='GCD-"+itm['id']+"'>"+itm['name']+"</a>";
	   r.cells[1].style.width='190px';
	   r.insertCell(-1).innerHTML = "<span class='subject'>"+itm['subject_name']+"</span>";
	   <?php
	   if($_REQUEST['show'] == "trash")
	   {
		?>
		r.insertCell(-1).innerHTML = "<span class='smallroundbtn' onclick='restoreDocument("+itm['id']+")'>ripristina</span>";
		<?php
	   }
	   else
	   {
		?>
	    r.insertCell(-1).innerHTML = "<span class='smallroundbtn' onclick='showDocumentOptions("+itm['id']+",this)'>opzioni</span>";
		<?php
	   }
	   ?>
	   var printDate = new Date(); if(itm['print_date']) printDate.setFromISO(itm['print_date']);
	   var sendDate = new Date(); if(itm['send_date']) sendDate.setFromISO(itm['send_date']);
	   var tmp = "";
	   switch(itm['status'])
	   {
		case '1' : tmp = "<img src='"+ABSOLUTE_URL+"share/widgets/commercialdocs/img/status-print.png' class='status-icon'/ ><span class='status-small'>stampato il<br/ >"+printDate.printf('d/m/Y')+"</span>"; break;

	    case '2' : tmp = "<img src='"+ABSOLUTE_URL+"share/widgets/commercialdocs/img/status-send.gif' class='status-icon'/ ><span class='status-small'>inviato il<br/ >"+printDate.printf('d/m/Y')+"</span>"; break;

	    case '3' : tmp = "<img src='"+ABSOLUTE_URL+"share/widgets/commercialdocs/img/status-pending.gif' class='status-icon'/ ><span class='status-normal'><b>in attesa</b></span>"; break;

	    case '4' : tmp = "<img src='"+ABSOLUTE_URL+"share/widgets/commercialdocs/img/status-working.gif' class='status-icon'/ ><span class='status-normal'><b style='color:#013397'>in lavorazione</b></span>"; break;

	    case '5' : tmp = "<img src='"+ABSOLUTE_URL+"share/widgets/commercialdocs/img/status-suspended.gif' class='status-icon'/ ><span class='status-normal'><b style='color:#f44800'>sospeso</b></span>"; break;

	    case '6' : tmp = "<img src='"+ABSOLUTE_URL+"share/widgets/commercialdocs/img/status-failed.gif' class='status-icon'/ ><span class='status-normal'><b style='color:#d40000'>fallito</b></span>"; break;

	    case '7' : tmp = "<img src='"+ABSOLUTE_URL+"share/widgets/commercialdocs/img/status-completed.png' class='status-icon'/ ><span class='status-normal'><b style='color:#015a01'>completato</b></span>"; break;

	    case '8' : tmp = "<img src='"+ABSOLUTE_URL+"share/widgets/commercialdocs/img/status-converted.png' class='status-icon'/ ><span class='status-xsmall'>convertito in<br/ >"+((itm['conv_doc_id'] && itm['conv_doc_name']) ? "<a href='#' onclick='openDocument("+itm['conv_doc_id']+")'>"+itm['conv_doc_name']+"</a>" : "documento sconosciuto")+"</span>"; break;

	    case '9' : tmp = "<img src='"+ABSOLUTE_URL+"share/widgets/commercialdocs/img/status-groupped.png' class='status-icon'/ ><span class='status-xsmall'>raggruppato in<br/ >"+((itm['group_doc_id'] && itm['group_doc_name']) ? "<a href='#' onclick='openDocument("+itm['group_doc_id']+")'>"+itm['group_doc_name']+"</a>" : "documento sconosciuto")+"</span>"; break;

	    case '10' : tmp = "<span class='status-green'>pagato</span>"; break;
	    
		default : tmp = "<span class='status-open'><i>aperto</i></span>"; break;
	   }
	   r.insertCell(-1).innerHTML = tmp;
	   r.cells[4].style.width='100px';
	   r.insertCell(-1).innerHTML = "<b><em>&euro;</em>"+formatCurrency(itm['total'],2)+"</b>";
	   r.cells[5].style.textAlign='right'; r.cells[4].style.width='90px';
	   total+= parseFloat(itm['amount']);
	   totalVI+= parseFloat(itm['total']);
	  }
	  document.getElementById('doctot-amount').innerHTML = "<em>&euro;</em>"+formatCurrency(total,2);
	  document.getElementById('doctot-vat').innerHTML = "<em>&euro;</em>"+formatCurrency(totalVI-total,2);
	  document.getElementById('doctot-total').innerHTML = "<em>&euro;</em>"+formatCurrency(totalVI,2);
	 }

	 CURRENT_PAGE++;
	 PAGES_COUNT++;
	 if((PAGES_COUNT == 5) && (COUNT > (RESULTS_PER_PAGE*CURRENT_PAGE)))
	 {
	  document.getElementById('loading').style.display='none';
	  document.getElementById('otherresults').style.display='';
	  document.getElementById('footerresults').style.display='';
	  document.getElementById('footerpagesel').value = (CURRENT_PAGE-1);
	  document.getElementById('resultsto').innerHTML = COUNT < (RESULTS_PER_PAGE*CURRENT_PAGE) ? COUNT : (RESULTS_PER_PAGE*CURRENT_PAGE);
	 }
	 else if(COUNT > (RESULTS_PER_PAGE*CURRENT_PAGE))
	  window.setTimeout(function(){nextPage();},2000);
	 else
	 {
	  document.getElementById('loading').style.display='none';
	  document.getElementById('footerresults').style.display='';
	  document.getElementById('footerpagesel').value = (CURRENT_PAGE-1);
	  document.getElementById('resultsto').innerHTML = COUNT < (RESULTS_PER_PAGE*CURRENT_PAGE) ? COUNT : (RESULTS_PER_PAGE*CURRENT_PAGE);
	 }
	}
 sh.sendCommand("dynarc item-list -ap `commercialdocs` -ct preemptives -extget cdinfo<?php echo $where ? ' -where `'.ltrim($where,' AND ').'`' : ''; ?> --order-by `ctime <?php echo ($_REQUEST['sort']=='asc') ? 'ASC' : 'DESC'; ?>,code_num <?php echo ($_REQUEST['sort']=='asc') ? 'ASC' : 'DESC'; ?>` -limit "+(RESULTS_PER_PAGE*CURRENT_PAGE)+","+RESULTS_PER_PAGE);

}

function moreResults()
{
 PAGES_COUNT = 0;
 document.getElementById('otherresults').style.display='none';
 document.getElementById('footerresults').style.display='none';
 document.getElementById('loading').style.display='';
 nextPage();
}

function filterChange(filter, li)
{
 var titO = document.getElementById('btn-filter').getElementsByTagName('SPAN')[0];
 titO.innerHTML = li.innerHTML;
 document.getElementById('btn-filter').setAttribute('filterval',filter);

 switch(filter)
 {
  case 'custom' : {
	 document.getElementById('filter-year').style.display='none';
	 document.getElementById('filter-custom').style.display='';
	} break;
  default : {
	 document.getElementById('filter-year').style.display='';
	 document.getElementById('filter-custom').style.display='none';
	 updateSearch();
	} break;
 }
}

function updateSearch()
{
 var fT = document.getElementById('btn-filter').getAttribute('filterval');
 var year = document.getElementById('year').value;
 var subject = document.getElementById('subject');
 var fsort = document.getElementById('filter-sort').getAttribute('sortval');

 var subjectName = subject.value;
 var subjectId = (subject.value && subject.data) ? subject.data['id'] : 0;
 
 if(subject.value == subject.getAttribute('emptyvalue'))
 {
  subjectName = "";
  subjectId = 0;
 }

 var dateFrom = null;
 var dateTo = null;
 
 switch(fT)
 {
  case 'custom' : {
	 if(document.getElementById('datefrom').value)
	 {
	  dateFrom = new Date();
	  dateFrom.setFromISO(strdatetime_to_iso(document.getElementById('datefrom').value));
	 }
	 if(document.getElementById('dateto').value)
	 {
	  dateTo = new Date();
	  dateTo.setFromISO(strdatetime_to_iso(document.getElementById('dateto').value));
	 }
	} break;

  case 'all' : {
	} break;
 
  default : {
	 var m = parseFloat(fT);
	 if(!m)
	  return;
	 dateFrom = new Date();
	 dateFrom.setFromISO(year+"-"+(m<10 ? "0"+m : m)+"-01");
	 dateTo = new Date();
	 dateTo.setTime(dateFrom.getTime());
	 if(dateFrom.getMonth() == 11)
	 {
	  dateTo.setMonth(0);
	  dateTo.setYear(dateFrom.getFullYear()+1);
	 }
	 else
	  dateTo.setMonth(dateFrom.getMonth()+1);
	} break;
 }

 var href = document.location.href.replace('#','');
 if(href.indexOf("&ft=") > 0)
  href = href.replace("&ft=<?php echo $_REQUEST['ft']; ?>","&ft="+fT);
 else
  href = href+"&ft="+fT;

 if(href.indexOf("&year=") > 0)
  href = href.replace("&year=<?php echo $_REQUEST['year']; ?>","&year="+year);
 else
  href = href+"&year="+year;

 if(href.indexOf("&sort=") > 0)
  href = href.replace("&sort=<?php echo $_REQUEST['sort']; ?>","&sort="+fsort);
 else
  href = href+"&sort="+fsort;

 if(dateFrom)
 {
  if(href.indexOf("&from=") > 0)
   href = href.replace("&from=<?php echo $_REQUEST['from']; ?>","&from="+dateFrom.printf('Y-m-d'));
  else
   href = href+"&from="+dateFrom.printf('Y-m-d');
 }
 else if(href.indexOf("&from=") > 0)
  href = href.replace("&from=<?php echo $_REQUEST['from']; ?>","");

 if(dateTo)
 {
  if(href.indexOf("&to=") > 0)
   href = href.replace("&to=<?php echo $_REQUEST['to']; ?>","&to="+dateTo.printf('Y-m-d'));
  else
   href = href+"&to="+dateTo.printf('Y-m-d');
 }
 else if(href.indexOf("&to=") > 0)
  href = href.replace("&to=<?php echo $_REQUEST['to']; ?>","");

 if(subjectId)
 {
  if(href.indexOf("&subjectid=") > 0)
   href = href.replace("&subjectid=<?php echo $_REQUEST['subjectid']; ?>","&subjectid="+subjectId);
  else
   href = href+"&subjectid="+subjectId;
 }
 else if(href.indexOf("&subjectid=") > 0)
  href = href.replace("&subjectid=<?php echo $_REQUEST['subjectid']; ?>","");

 if(subjectName)
 {
  if(href.indexOf("&subjectname=") > 0)
   href = href.replace("&subjectname="+escape("<?php echo $_REQUEST['subjectname']; ?>"),"&subjectname="+escape(subjectName));
  else
   href = href+"&subjectname="+escape(subjectName);
 }
 else if(href.indexOf("&subjectname=") > 0)
  href = href.replace("&subjectname="+escape("<?php echo $_REQUEST['subjectname']; ?>"),"");

 if(href.indexOf("&pg=") > 0)
  href = href.replace("&pg=<?php echo $_REQUEST['pg']; ?>","&pg=1");

 document.location.href = href;
}

function openDocument(id)
{
 window.parent.document.location.href = ABSOLUTE_URL+"GCommercialDocs/docinfo.php?id="+id;
}

function newDoc()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 openDocument(a['id']);
	}
 sh.sendCommand("dynarc new-item -ap `commercialdocs` -ct preemptives -group commdocs-preemptives");
}

function unselectAll()
{
 var cb = document.getElementById('tbselectall');
 cb.checked = false;
 selectAllRows(cb);
}

function selectAllRows(cb)
{
 var tb = document.getElementById('itemlist');
 for(var c=0; c < tb.rows.length; c++)
 {
  var r = tb.rows[c];
  if(r.cells[0].colSpan > 1)
   continue;
  r.className = cb.checked ? "selected" : "";
  r.cells[0].getElementsByTagName('INPUT')[0].checked = cb.checked;

  if(cb.checked && (SELECTED_ROWS.indexOf(r) < 0))
   SELECTED_ROWS.push(r);
  else if(!cb.checked && (SELECTED_ROWS.indexOf(r) > -1))
   SELECTED_ROWS.splice(SELECTED_ROWS.indexOf(r),1);
 }

 if(SELECTED_ROWS.length)
  document.getElementById('selectionmenu').style.visibility = "visible";
 else
  document.getElementById('selectionmenu').style.visibility = "hidden";
}

function selectRow(cb)
{
 var r = cb.parentNode.parentNode;
 r.className = cb.checked ? "selected" : "";
 if(cb.checked && (SELECTED_ROWS.indexOf(r) < 0))
  SELECTED_ROWS.push(r);
 else if(!cb.checked && (SELECTED_ROWS.indexOf(r) > -1))
  SELECTED_ROWS.splice(SELECTED_ROWS.indexOf(r),1);

 if(SELECTED_ROWS.length)
  document.getElementById('selectionmenu').style.visibility = "visible";
 else
  document.getElementById('selectionmenu').style.visibility = "hidden";
}

function deleteSelectedDocuments(notrash)
{
 if(!SELECTED_ROWS.length)
  return alert("Devi selezionare almeno un documento");
 if(!confirm("Sei sicuro di voler eliminare i documenti selezionati?"))
  return;

 var sh = new GShell();
 sh.OnFinish = function(){
	 document.location.reload();
	}
 for(var c=0; c < SELECTED_ROWS.length; c++)
  sh.sendCommand("dynarc delete-item -ap `commercialdocs` -id `"+SELECTED_ROWS[c].id+"`"+(notrash ? " -r" : ""));
}

function showPage(page)
{
 if(!page)
  page = "";

 var href = document.location.href.replace('#','');
 if(href.indexOf("&show=") > 0)
  href = href.replace("&show=<?php echo $_REQUEST['show']; ?>","&show="+page);
 else
  href = href+"&show="+page;

 if(href.indexOf("&pg=") > 0)
  href = href.replace("&pg=<?php echo $_REQUEST['pg']; ?>","&pg=1");

 document.location.href=href;
}

function sortChange(div)
{
 if(div.getAttribute('sortval') == 'desc')
 {
  div.getElementsByTagName('IMG')[0].src = ABSOLUTE_URL+"share/widgets/commercialdocs/img/sort-asc.png";
  div.setAttribute('sortval','asc');
 }
 else
 {
  div.getElementsByTagName('IMG')[0].src = ABSOLUTE_URL+"share/widgets/commercialdocs/img/sort-desc.png";
  div.setAttribute('sortval','desc');
 }
 updateSearch();
}

function adjustResize(h)
{
 gframe_resize("100%",h-20);
 document.getElementById('itemlist').parentNode.style.height = h-198;
}

function pageChange(page)
{
 var href = document.location.href.replace('#','');
 if(href.indexOf("&pg=") > 0)
  href = href.replace("&pg=<?php echo $_REQUEST['pg']; ?>","&pg="+(parseInt(page)+1));
 else
  href = href+="&pg="+(parseInt(page)+1);

 if(href.indexOf("&limit=") > 0)
  href = href.replace("&limit=<?php echo $_REQUEST['limit']; ?>","&limit="+RESULTS_PER_PAGE);
 else
  href = href+="&limit="+RESULTS_PER_PAGE;

 document.location.href=href;

}

function showDocumentOptions(id, obj)
{
 var pos = _getObjectPosition(obj);
 pos['y']-= document.getElementById('itemlist-container').scrollTop;
 var nuv = document.getElementById('nuvcontainer');
 var lay = new Layer("commercialdocs/docopt", "id="+id, nuv, true, function(){
	 nuv.style.left = pos['x'] + obj.offsetWidth;
	 nuv.style.top = (pos['y'] + Math.floor(obj.offsetHeight/2)) - Math.floor(nuv.offsetHeight/2);
	 nuv.style.visibility = "visible";
	});
}

function hideDocumentOptions()
{
 if(document.getElementById('nuvcontainer'))
  document.getElementById('nuvcontainer').style.visibility = "hidden";
}

function restoreDocument(docId)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 alert("Il documento è stato ripristinato!");
	 document.location.reload();
	}
 sh.sendCommand("dynarc trash restore -ap commercialdocs -id `"+docId+"`");
}

function restoreSelected()
{
 if(!SELECTED_ROWS.length)
  return alert("Devi selezionare almeno un documento");

 var sh = new GShell();
 sh.OnFinish = function(){
	 alert("I documenti sono stati ripristinati!");
	 document.location.reload();
	}
 for(var c=0; c < SELECTED_ROWS.length; c++)
  sh.sendCommand("dynarc trash restore -ap commercialdocs -id `"+SELECTED_ROWS[c].id+"`");
}

function emptyTrash()
{
 if(!confirm("Una volta svuotato il cestino i documenti saranno rimossi permanentemente pertanto non sarà più possibile recuperarli. Sei sicuro di voler procedere?"))
  return;
 var sh = new GShell();
 sh.OnOutput = function(){
	 alert("Il cestino è stato svuotato!");
	 document.location.reload();
	}
 sh.sendCommand("dynarc trash empty -ap commercialdocs -ct preemptives");
}
</script>
<?php

