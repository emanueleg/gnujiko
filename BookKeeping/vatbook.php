<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 06-07-2016
 #PACKAGE: bookkeeping
 #DESCRIPTION: VAT Book
 #VERSION: 2.3beta
 #CHANGELOG: 06-07-2016 : Bug fix iva a credito - iva a debito.
			 04-05-2016 : Aggiunto campo IVA non dovuta.
			 08-04-2014 : Bug fix vari
 #TODO: 
 
*/

include_once($_BASE_PATH."var/objects/htmlgutility/menu.php");
include_once($_BASE_PATH."var/objects/editsearch/index.php");

$_MONTHS = array('Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre');

if(!$_REQUEST['from'])
{
 $_REQUEST['from'] = date('Y-m-01');
 $_REQUEST['to'] = date('Y-m-01',strtotime("+1 month"));
}

$from = strtotime($_REQUEST['from']);

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/vatbook.css" type="text/css" />
<table width='100%' cellspacing='8' cellpadding='0' border='0'>
<tr><td valign='top' width='110'>
	 <div class='monthmenu' id='monthmenu'><span><?php echo $_MONTHS[date('n',$from)-1]; ?></span><img src="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/img/tiptop-dnarr.png"/></div>
	  <ul class="submenu" id='monthmenulist'>
	   <?php
		for($c=0; $c < count($_MONTHS); $c++)
		 echo "<li onclick='selectMonth(".$c.",this)'>".$_MONTHS[$c]."</li>";
	   ?>
	  </ul>
	</td>

	<td>
	 <div class='yearmenu' id='yearmenu'><span><?php echo date('Y',$from); ?></span><img src="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/img/tiptop-dnarr.png"/></div>
	  <ul class="submenu" id='yearmenulist'>
	   <?php
		$ret = GShell("vatregister register-list");
		for($c=0; $c < count($ret['outarr']); $c++)
		 echo "<li onclick='selectYear(".$ret['outarr'][$c]['year'].")'>".$ret['outarr'][$c]['year']."</li>";
	   ?>
	  </ul>
	</td>

	<td width='130' style='font-size:12px;'>
	Dal: <input type='text' class='searchinput' style='width:100px' id='from' value="<?php if($_REQUEST['from']) echo date('d/m/Y',strtotime($_REQUEST['from'])); ?>"/>
	</td>
	<td width='130' style='font-size:12px;'>
	 al: <input type='text' class='searchinput' style='width:100px' id='to' value="<?php if($_REQUEST['to']) echo date('d/m/Y',strtotime($_REQUEST['to'])); ?>"/>
	</td>

	<td width='90'>
	<ul class='basicbuttons'>
		 <li><span onclick='updateQry()'><img src="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/img/search.gif" border='0'/> Cerca</span></li>
	</ul>
	</td>


	<td width='120' valign='top'>
	 <span onclick='printPreview()' class='toolbtn'><img src="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/img/print.png"/> <span>Stampa</span></span>
	</td>
</tr>
</table>

<div style='margin-left:8px;'>
<table width='100%' class='itemlist' cellspacing='0' cellpadding='0' border='0'>
	<tr><th width='32'><input type='checkbox' onchange="selectAllRows(this)" id="tbselectall"/></th>
		<th width='70'>DATA</th>
		<th style='text-align:left'>RIFERIMENTO</th>
		<th width='70'>TOTALE DOC.</th>
		<th width='80' style="border-left: 1px solid #dadada"><small>IMPONIBILE</small></th>
		<th width='60' style="border-left: 1px solid #eeeeee"><small>COD. IVA</small></th>
		<th width='80' style="border-left: 1px solid #dadada"><small>IMP. OMAGGI</small></th>
		<th width='80' style="border-left: 1px solid #eeeeee"><small>
		<?php
		if($_REQUEST['show'] == "purchasesregister")
		 echo "IVA A CREDITO";
		else
		 echo "IVA A DEBITO";
		?>
		</small></th>
		<th width='80' style="border-left: 1px solid #eeeeee"><small>IVA NON DOVUTA</small></th>
		<th width='10'>&nbsp;</th>
	</tr>
</table>
</div>

<div class="itemlist-container" style="height:50px;margin-left:8px;">
	<table width='100%' class='itemlist' id='itemlist' cellspacing='0' cellpadding='0' border='0'>
	<?php
	$rpp = $_REQUEST['limit'] ? $_REQUEST['limit'] : 20;
	$from = $_REQUEST['pg'] ? ($rpp*($_REQUEST['pg']-1)) : 0;

	/* Get vat list */
	$_VAT_INFOS = array();
	$_VAT_USED = array();
	$ret = GShell("dynarc item-list -ap `vatrates` -get `vat_type,percentage`");
	for($c=0; $c < count($ret['outarr']['items']); $c++)
	{
	 $itm = $ret['outarr']['items'][$c];
	 $_VAT_INFOS[$itm['id']] = $itm;
	}

	$qry = "vatregister list";
    switch($_REQUEST['show'])
	{
	 case 'purchasesregister' : $qry.= " -type 1 -docct PURCHASEINVOICES"; break;
     case 'salesregister' : $qry.= " -type 2 -docct INVOICES"; break;
     case 'receiptregister' : $qry.= " -type 2 -docct RECEIPTS"; break;
	 default: $qry.= " -type 1 -docct PURCHASEINVOICES"; break;
	}
	if($_REQUEST['from'])
	 $qry.= " -from '".date('Y-m-d',strtotime($_REQUEST['from']))."'";
	if($_REQUEST['to'])
	 $qry.= " -to '".date('Y-m-d',strtotime($_REQUEST['to']))."'";
	if($_REQUEST['subjectid'])
	 $qry.= " -subjectid '".$_REQUEST['subjectid']."'";
	else if($_REQUEST['subject'])
	 $qry.= " -subject `".$_REQUEST['subject']."`";
	
	$ret = GShell($qry." -limit ".($from ? $from : "0").",".$rpp." --get-totals"); 

	if(!$ret['error'])
	{
	 $count = $ret['outarr']['count'];
	 $list = $ret['outarr']['items'];
	 $serpInfo = $ret['outarr']['serpinfo'];
	}

	$totals = ($_REQUEST['show'] == "purchasesregister") ? $ret['outarr']['tot_purchases'] : $ret['outarr']['tot_sales'];

	for($i=0; $i < count($list); $i++)
	{
	 $itm = $list[$i];
	 echo "<tr id='".$itm['id']."'><td width='32' align='center'><input type='checkbox' onclick='selectRow(this)'/></td>";
	 echo "<td width='70' align='center'>".date('d/m/Y',$itm['ctime'])."</td>";
	 echo "<td class='docref'>";
     if($itm['doc_ap'] && $itm['doc_id'])
      echo "<a href='#' onclick=\"showDocInfo('".$itm['doc_ap']."','".$itm['doc_id']."')\">".$itm['doc_info']['name']."</a>";
	 else if($itm['doc_ref'])
	  echo $itm['doc_ref'];
	 else
	  echo "&nbsp;";
	 if($itm['subject_name'])
	  echo "<br/>".$itm['subject_name'];
	 echo "</td>";

	 echo "<td width='70' class='currency'>".number_format($itm['total'],2,',','.')."&nbsp;</td>"; // totale documento
	 echo "<td width='80' class='currency'>";
	 for($c=0; $c < count($itm['vatrates']); $c++)
	 {
	  echo ($c ? "&nbsp;<br/>" : "").number_format($itm['vatrates'][$c]['amount'],2,',','.');
	  if(!$_VAT_USED[$itm['vatrates'][$c]['id']])
	   $_VAT_USED[$itm['vatrates'][$c]['id']] = array('amount'=>0,'vat'=>0);
	  $_VAT_USED[$itm['vatrates'][$c]['id']]['amount']+= $itm['vatrates'][$c]['amount'];
	 }
	 echo "&nbsp;</td>"; // imponibile
	 echo "<td width='60' class='vatrate'>";
	 for($c=0; $c < count($itm['vatrates']); $c++)
	  echo ($c ? "&nbsp;<br/>" : "").$_VAT_INFOS[$itm['vatrates'][$c]['id']]['code_str'];
	 echo "&nbsp;</td>"; // aliquota iva
	 echo "<td width='80'>&nbsp;</td>"; // imponibile omaggi
	 echo "<td width='80' class='currency'>";
	 for($c=0; $c < count($itm['vatrates']); $c++)
	 {
	  echo ($c ? "&nbsp;<br/>" : "").number_format($itm['vatrates'][$c]['vat'],2,',','.');
	  $_VAT_USED[$itm['vatrates'][$c]['id']]['vat']+= $itm['vatrates'][$c]['vat'];
	 }
	 echo "&nbsp;</td>"; // iva detraibile
	 echo "<td width='80' class='currency'>".number_format($itm['vat_nd'],2,',','.')."&nbsp;</td>";

	 echo "</tr>";
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

<?php
$totAmount = 0;
$totGifts = 0;
$totDeductible = 0;
$totND = 0;
for($c=0; $c < count($totals); $c++)
{
 $v = $totals[$c];
 $k = $v['id'];
 if(!$v['amount'])
  continue;
 $totAmount+= $v['amount'];
 $totDeductible+= $v['vat'];
 $totND+= $v['vat_nd'];
}

?>

<div id='vat_summary_collapsed' style='margin-left:8px;margin-right:8px;'>
	<table width='100%' cellspacing='0' cellpadding='2' border='0' class='summary'>
	<tr><td width='120'><span class='link' onclick='showVatSummary(true)'>RIEPILOGO ALIQUOTE</span></td>
		<td width='60'>&nbsp;</td>
		<td>TOTALI</td>
		<td width='80' align='right'><?php echo number_format($totAmount,2,',','.'); ?></td>
		<td width='60'>&nbsp;</td>
		<td width='80' align='right'><?php echo number_format($totGifts,2,',','.'); ?></td>
		<td width='80' align='right'><?php echo number_format($totDeductible,2,',','.'); ?></td>
		<td width='8'>&nbsp;</td></tr>
	</table>
</div>

<div id='vat_summary_expanded' style='margin-left:8px;margin-right:8px;display:none'>
	<!-- SUMMARY TABLE -->
	<table width='100%' cellspacing='0' cellpadding='2' border='0' class='summary'>
	<tr><td width='120'>RIEPILOGO ALIQUOTE</td>
		<td width='60'>Cod. IVA</td>
		<td>Descrizione</td>
		<td width='80' style='text-align:right;'>Imponibile</td>
		<td width='60'>&nbsp;</td>
		<td width='80' style='text-align:right;'>Imp. omaggi</td>
		<td width='80' style='text-align:right;'>
		<?php
		 if($_REQUEST['show'] == "purchasesregister")
		  echo "IVA a debito";
		 else
		  echo "IVA a credito";
		?>
		</td>
		<td width='80' style='text-align:right;'>IVA non dovuta</td>
		<td width='8'>&nbsp;</td></tr>
	
	<?php
	for($c=0; $c < count($totals); $c++)
	{
	 $v = $totals[$c];
	 $k = $v['id'];
	 if(!$v['amount'])
	  continue;
	 echo "<tr><td>&nbsp;</td>";
	 echo "<td>".$_VAT_INFOS[$k]['code_str']."</td>";
	 echo "<td>".$_VAT_INFOS[$k]['name']."</td>";
	 echo "<td align='right'>".number_format($v['amount'],2,',','.')."</td>";
	 echo "<td>&nbsp;</td>";
	 echo "<td>&nbsp;</td>"; // tot omaggi
	 echo "<td align='right'>".number_format($v['vat'],2,',','.')."</td>";
	 echo "<td align='right'>".number_format($v['vat_nd'],2,',','.')."</td>";
	 echo "<td>&nbsp;</td>";
	 echo "</tr>";
	}

	echo "<tr><td align='center'><span class='link' onclick='showVatSummary(false)'>nascondi</span></td><td>&nbsp;</td><td style='border-top:1px solid #dadada'>TOTALI</td>";
	echo "<td style='border-top:1px solid #dadada' align='right'>".number_format($totAmount,2,',','.')."</td>";
	echo "<td style='border-top:1px solid #dadada'>&nbsp;</td>";
	echo "<td style='border-top:1px solid #dadada' align='right'>".number_format($totGifts,2,',','.')."</td>";
	echo "<td style='border-top:1px solid #dadada' align='right'><b>".number_format($totDeductible,2,',','.')."</b></td>";
	echo "<td style='border-top:1px solid #dadada' align='right'><b>".number_format($totND,2,',','.')."</b></td>";
	echo "<td>&nbsp;</td></tr>";
	?>
	<tr><td colspan='8'>&nbsp;</td></tr>
	</table>
</div>

<script>
var SELECTED_ROWS = new Array();
var COUNT = <?php echo $count ? $count : "0"; ?>;
var RESULTS_PER_PAGE = <?php echo $serpInfo['resultsperpage'] ? $serpInfo['resultsperpage'] : "20"; ?>;
var CURRENT_PAGE = <?php echo $_REQUEST['pg'] ? $_REQUEST['pg'] : "1"; ?>;
var PAGES_COUNT = 1;
var SELECTED_CATALOG = "";
var YEAR = "<?php echo date('Y',strtotime($_REQUEST['from'])); ?>";
var MONTH = "<?php echo date('n',strtotime($_REQUEST['from'])); ?>";

var VAT_CODES = new Array();
<?php
reset($_VAT_INFOS);
while(list($k,$v) = each($_VAT_INFOS))
{
 echo "VAT_CODES[".$k."] = \"".$v['code_str']."\";\n";
}
?>

function desktopOnLoad()
{
 var div = document.getElementById('storepagecontainer');
 div.style.height = div.parentNode.offsetHeight-20;
 var div2 = document.getElementById('itemlist').parentNode;
 div2.style.height = div2.parentNode.offsetHeight-110;

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

 new GPopupMenu(document.getElementById('monthmenu'), document.getElementById('monthmenulist'));
 new GPopupMenu(document.getElementById('yearmenu'), document.getElementById('yearmenulist'));
}

function onSearchQry(items,resArr,retVal)
{
 for(var c=0; c < items.length; c++)
 {
  resArr.push(items[c]['code_str']+" - "+items[c]['name']);
  retVal.push(items[c]['id']);
 } 
}

function nextPage()
{
 document.getElementById('loading').style.display="";

 var FROM = (RESULTS_PER_PAGE*CURRENT_PAGE)+1;
 var TO = (FROM+RESULTS_PER_PAGE-1) > COUNT ? COUNT : (FROM+RESULTS_PER_PAGE-1);

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(a && a['items'])
	 {
	  var tb = document.getElementById('itemlist');
	  var date = new Date();
	  for(var c=0; c < a['items'].length; c++)
	  {
	   var itm = a['items'][c];
	   var r = tb.insertRow(-1);
	   r.id = itm['id'];
	   date.setTime(parseFloat(itm['ctime'])*1000);
	   r.insertCell(-1).innerHTML = "<input type='checkbox' onclick='selectRow(this)'/ >";
	   r.insertCell(-1).innerHTML = date.printf("d/m/Y");

	   var html = "";
	   if(itm['doc_ap'] && itm['doc_id'])
        html+= "<a href='#' onclick=\"showDocInfo('"+itm['doc_ap']+"','"+itm['doc_id']+"')\">"+itm['doc_info']['name']+"</a>";
	   else if(itm['doc_ref'])
	    html+= itm['doc_ref'];
	   else
	    html="&nbsp;";
	   if(itm['subject_name'])
	    html+= "<br/ >"+itm['subject_name'];
	   r.insertCell(-1).innerHTML = html;

	   r.insertCell(-1).innerHTML = formatCurrency(itm['total'],2)+"&nbsp;";

	   var html = "";
	   if(itm['vatrates'])
	   {
	    for(var i=0; i < itm['vatrates'].length; i++)
	     html+= (i ? "&nbsp;<br/ >" : "")+formatCurrency(itm['vatrates'][i]['amount'],2);
	   }
	   r.insertCell(-1).innerHTML = html+"&nbsp;";

	   var html = "";
	   if(itm['vatrates'])
	   {
	    for(var i=0; i < itm['vatrates'].length; i++)
	     html+= (i ? "&nbsp;<br/ >" : "")+VAT_CODES[itm['vatrates'][i]['id']];
	   }
	   r.insertCell(-1).innerHTML = html+"&nbsp;";

	   r.insertCell(-1).innerHTML = "&nbsp;"; /* OMAGGI */

	   var html = "";
	   if(itm['vatrates'])
	   {
	    for(var i=0; i < itm['vatrates'].length; i++)
	     html+= (i ? "&nbsp;<br/ >" : "")+formatCurrency(itm['vatrates'][i]['vat'],2);
	   }
	   r.insertCell(-1).innerHTML = html+"&nbsp;";


	   r.cells[0].style.width = "32px"; r.cells[0].style.textAlign='center';
	   r.cells[1].style.width = "70px"; r.cells[1].style.textAlign='center';
	   r.cells[2].className = "docref";
	   r.cells[3].style.width = "70px"; r.cells[3].className = "currency";
	   r.cells[4].style.width = "80px"; r.cells[4].className = "currency";
	   r.cells[5].style.width = "60px"; r.cells[5].className = "vatrate";
	   r.cells[6].style.width = "80px"; r.cells[6].className = "currency";
	   r.cells[7].style.width = "80px"; r.cells[7].className = "currency";
	  }
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
 sh.sendCommand("<?php echo $qry; ?> -limit "+(RESULTS_PER_PAGE*CURRENT_PAGE)+","+RESULTS_PER_PAGE);
}

function moreResults()
{
 PAGES_COUNT = 0;
 document.getElementById('otherresults').style.display='none';
 document.getElementById('footerresults').style.display='none';
 document.getElementById('loading').style.display='';
 nextPage();
}

function pageChange(page)
{
 var href = ABSOLUTE_URL+"BookKeeping/index.php?page=vatbook&show=<?php echo $_REQUEST['show'] ? $_REQUEST['show'] : 'purchasesregister'; ?>";
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
}

function selectRow(cb)
{
 var r = cb.parentNode.parentNode;
 r.className = cb.checked ? "selected" : "";
 if(cb.checked && (SELECTED_ROWS.indexOf(r) < 0))
  SELECTED_ROWS.push(r);
 else if(!cb.checked && (SELECTED_ROWS.indexOf(r) > -1))
  SELECTED_ROWS.splice(SELECTED_ROWS.indexOf(r),1);
}

function updateQry()
{
 var href = ABSOLUTE_URL+"BookKeeping/index.php?page=vatbook&show=<?php echo $_REQUEST['show'] ? $_REQUEST['show'] : 'purchasesregister'; ?>&filter=<?php echo $_REQUEST['filter']; ?>";

 var from = document.getElementById('from');
 var to = document.getElementById('to');
 if(from.value)
  href+= "&from="+strdatetime_to_iso(from.value);
 if(to.value)
  href+= "&to="+strdatetime_to_iso(to.value);

 document.location.href=href;
}

function selectMonth(month, li)
{
 var date = new Date();
 date.setDate(1);
 date.setMonth(month);
 date.setYear(YEAR);
 var dateFrom = date.printf('Y-m-01');
 date.NextMonth();
 var dateTo = date.printf('Y-m-01');

 document.getElementById('monthmenu').getElementsByTagName('SPAN')[0].innerHTML = li.innerHTML;
 var href = ABSOLUTE_URL+"BookKeeping/index.php?page=vatbook&show=<?php echo $_REQUEST['show'] ? $_REQUEST['show'] : 'purchasesregister'; ?>&filter=<?php echo $_REQUEST['filter']; ?>&from="+dateFrom+"&to="+dateTo;
 document.location.href=href;
}

function selectYear(year)
{
 YEAR = year;
 document.getElementById('yearmenu').getElementsByTagName('SPAN')[0].innerHTML = year;

 var date = new Date();
 date.setDate(1);
 date.setMonth(MONTH-1);
 date.setYear(YEAR);
 var dateFrom = date.printf('Y-m-01');
 date.NextMonth();
 var dateTo = date.printf('Y-m-01');

 var href = ABSOLUTE_URL+"BookKeeping/index.php?page=vatbook&show=<?php echo $_REQUEST['show'] ? $_REQUEST['show'] : 'purchasesregister'; ?>&filter=<?php echo $_REQUEST['filter']; ?>&from="+dateFrom+"&to="+dateTo;
 document.location.href=href;
}

function showDocInfo(ap,id)
{
 var sh = new GShell();
 sh.sendCommand("dynlaunch -ap `"+ap+"` -id `"+id+"`");
}

function showVatSummary(bool)
{
 document.getElementById('vat_summary_expanded').style.display = bool ? "" : "none";
 document.getElementById('vat_summary_collapsed').style.display = bool ? "none" : "";
 document.getElementById('storepagecontainer').style.height = "";
 document.body.scrollTop = document.body.scrollHeight;
}

function printPreview()
{
 var sh = new GShell();
 sh.sendCommand("gframe -f print.preview -params `modelap=printmodels&modelct=vatbook&parser=vatbook&qry="+encodeURI("<?php echo str_replace('`','\'',$qry); ?>")+"` -title `Stampa registro IVA`");
}

</script>
<?php

