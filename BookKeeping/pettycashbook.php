<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-10-2016
 #PACKAGE: bookkeeping
 #DESCRIPTION: Official Gnujiko Petty Cash Book.
 #VERSION: 2.7beta
 #CHANGELOG: 21-10-2016 : Bug fix sui totali quando si filtra per risorsa.
			 19-08-2016 : Bug fix sui totali.
			 25-06-2016 : Possibilita di ordinare i risultati per data e soggetto.
			 30-01-2015 : Aggiunta funzione esporta in excel.
			 11-04-2014 : Bug fix vari.
			 10-10-2013 : Possibilità di filtrare anche per risorsa.
			 12-08-2013 : Aggiunto i totali da inviare al modello di stampa tramite parametro parser.
 #TODO:
 
*/

include_once($_BASE_PATH."var/objects/htmlgutility/menu.php");
include_once($_BASE_PATH."var/objects/editsearch/index.php");

$_ORDERBY_FIELD = $_REQUEST['orderby'] ? $_REQUEST['orderby'] : "ctime";
$_ORDERBY_METHOD = $_REQUEST['ordermethod'] ? $_REQUEST['ordermethod'] : "DESC";

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/pettycashbook.css" type="text/css" />
<table width='100%' cellspacing='8' cellpadding='0' border='0'>
<tr><td valign='top' width='120'>
	 <div class='mainmenu' id='mainmenu'><span>Menu</span><img src="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/img/tiptop-dnarr.png"/></div>
	  <ul class="submenu" id='mainmenulist'>
	   <li onclick='newIN()'><img src="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/img/incomes.png" width='12'/>Nuova entrata</li>
	   <li onclick='newOUT()'><img src="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/img/expenses.png" width='12'/>Nuova uscita</li>
	   <li onclick='newTransfer()'><img src="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/img/transfers.png" width='12'/>Nuovo giroconto</li>
	   <li class='separator'>&nbsp;</li>
	   <li onclick='deleteSelected()'><img src="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/img/delete.gif" width='12'/>Elimina selezionati</li>
	   <li class='separator'>&nbsp;</li>
	   <li onclick='ExportToExcel()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/excel.png" width='12'/>Esporta su file Excel</li>
	   <li onclick='printPreview()'><img src="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/img/print.png" width='12'/>Stampa</li>
	  </ul>
	</td>
	<td>
	 <span class='toolbtn' onclick='newIN()'><img src="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/img/incomes.png"/> <span class='small'>Nuova<br/>entrata</span></span>
	 <span class='toolbtn' onclick='newOUT()'><img src="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/img/expenses.png"/> <span class='small'>Nuova<br/>uscita</span></span>
	 <span class='toolbtn' onclick='newTransfer()'><img src="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/img/transfers.png"/> <span class='small'>Nuovo<br/>giroconto</span></span>
	</td>
	<td width='120' valign='top'>
	 <span class='toolbtn' onclick='printPreview()'><img src="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/img/print.png"/> <span>Stampa</span></span>
	</td>
</tr>
</table>


<table width='100%' cellspacing='8' cellpadding='0' border='0' style="border-top:1px solid #dadada">
<tr><td style='font-size:12px;'>
	Filtra per: <select id='filter' onchange='filterChanged(this)'>
		<option value='subject' <?php if($_REQUEST['subject'] || $_REQUEST['subjectid']) echo "selected='selected'"; ?>>Soggetto</option>
		<option value='description' <?php if($_REQUEST['description']) echo "selected='selected'"; ?>>Descrizione</option>
		<option value='category' <?php if($_REQUEST['catid'] || $_REQUEST['catname']) echo "selected='selected'"; ?>>Categoria</option>
		<option value='resource' <?php if($_REQUEST['resid']) echo "selected='selected'"; ?>>Risorsa</option>
		</select>
	<input type='text' id='search' class='searchinput' style="width:200px;<?php if($_REQUEST['resid']) echo 'display:none'; ?>" value="<?php echo $_REQUEST['search'] ? $_REQUEST['search'] : ''; ?>" defaultvalue="<?php echo $_REQUEST['search'] ? $_REQUEST['search'] : ''; ?>" onchange="updateQry()"/>
	<select id='resource' style="width:200px;<?php if(!$_REQUEST['resid']) echo 'display:none'; ?>">
	<?php
	$ret = GShell("cashresources list");
	$list = $ret['outarr'];
	for($c=0; $c < count($list); $c++)
	 echo "<option value='".$list[$c]['id']."'".($_REQUEST['resid'] == $list[$c]['id'] ? " selected='selected'>" : ">").$list[$c]['name']."</option>";
	?>
	</select>
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
</tr>
</table>

<div style='margin-left:8px;'>
<table width='100%' class='itemlist' cellspacing='0' cellpadding='0' border='0'>
<tr><th width='32'><input type='checkbox' onchange="selectAllRows(this)" id="tbselectall"/></th>
	<th width='80' onclick='orderbyChange("ctime")' style='cursor:pointer'>DATA</th>
	<th width='120' onclick='orderbyChange("subject_name")' style='text-align:left;cursor:pointer'>SOGGETTO</th>
	<th style='text-align:left'>DESCRIZIONE</th>
	<th width='70'>ENTRATE</th>
	<th width='70'>USCITE</th>
	<th width='200'>DOC. DI RIFERIMENTO</th>
	<th width='10'>&nbsp;</th>
</tr>
</table>
</div>

<div class="itemlist-container" style="height:50px;margin-left:8px;">
	<table width='100%' class='itemlist' id='itemlist' cellspacing='0' cellpadding='0' border='0'>
	<?php
	$rpp = $_REQUEST['limit'] ? $_REQUEST['limit'] : 20;
	$from = $_REQUEST['pg'] ? ($rpp*($_REQUEST['pg']-1)) : 0;
	$orderBy = $_ORDERBY_FIELD." ".$_ORDERBY_METHOD;

	$qry = "pettycashbook list";
    switch($_REQUEST['filter'])
	{
	 case 'in' : $qry.= " -filter in"; break;
	 case 'out' : $qry.= " -filter out"; break;
	 case 'transfers' : $qry.= " -filter transfers"; break;
	}
	if($_REQUEST['from'])
	 $qry.= " -from '".date('Y-m-d H:i',strtotime($_REQUEST['from']))."'";
	if($_REQUEST['to'])
	 $qry.= " -to '".date('Y-m-d H:i',strtotime($_REQUEST['to']))."'";
	if($_REQUEST['resid'])
	 $qry.= " -resid '".$_REQUEST['resid']."'";
	if($_REQUEST['subjectid'])
	 $qry.= " -subjectid '".$_REQUEST['subjectid']."'";
	else if($_REQUEST['subject'])
	 $qry.= " -subject `".$_REQUEST['subject']."`";
	else if($_REQUEST['catid'])
	 $qry.= " -cat `".$_REQUEST['catid']."`";
	else if($_REQUEST['description'])
	 $qry.= " -description `".$_REQUEST['description']."`";

	$qry.= " --order-by '".$orderBy."'";
	
	$ret = GShell($qry." -limit ".($from ? $from : "0").",".$rpp." --get-totals"); // Nella prima query ricaviamo i totali //

	if(!$ret['error'])
	{
	 $count = $ret['outarr']['count'];
	 $list = $ret['outarr']['items'];
	 $serpInfo = $ret['outarr']['serpinfo'];
	}

	for($c=0; $c < count($list); $c++)
	{
	 $itm = $list[$c];
	 echo "<tr id='".$itm['id']."'><td width='32' align='center'><input type='checkbox' onclick='selectRow(this)'/></td>";
	 echo "<td width='80' class='date'><a href='#' onclick='editRecord(".$itm['id'].")'>".date('d/m/Y',$itm['ctime'])."</a></td>";
	 echo "<td width='120'><span class='subject'>".($itm['subject_name'] ? $itm['subject_name'] : "&nbsp;")."</span></td>";
	 echo "<td><small>".($itm['name'] ? $itm['name'] : "&nbsp;")."</small></td>";
	 echo "<td width='70' class='incomes'>";
	 if($_REQUEST['resid'])
	 {
	  if($itm['res_in']['id'] == $_REQUEST['resid'])
	   echo $itm['incomes'] ? number_format($itm['incomes'],2,',','.') : "&nbsp;";
	  else
	   echo "&nbsp;";
	 }
	 else
	  echo $itm['incomes'] ? number_format($itm['incomes'],2,',','.') : "&nbsp;";
	 echo "</td>";
	 echo "<td width='70' class='expenses'>";
	 if($_REQUEST['resid'])
	 {
	  if($itm['res_out']['id'] == $_REQUEST['resid'])
	   echo $itm['expenses'] ? number_format($itm['expenses'],2,',','.') : "&nbsp;";
	  else
	   echo "&nbsp;";
	 }
	 else
	  echo $itm['expenses'] ? number_format($itm['expenses'],2,',','.') : "&nbsp;";
	 echo "</td>";
	 echo "<td width='210' class='smalllink'>"
		.($itm['doc_info'] ? "<a href='".$_ABSOLUTE_URL."GCommercialDocs/docinfo.php?id=".$itm['doc_info']['id']."' target='GCD-".$itm['doc_info']['id']."'>".$itm['doc_info']['name']."</a>" : ($itm['doc_ref'] ? $itm['doc_ref'] : "&nbsp;"))."</td></tr>";
	}

	?>
	</table>

	<?php
	//$totIncomes = ($_REQUEST['filter'] == "transfers") ? $ret['outarr']['tot_transfers'] : $ret['outarr']['tot_incomes'];
	//$totExpenses = ($_REQUEST['filter'] == "transfers") ? $ret['outarr']['tot_transfers'] : $ret['outarr']['tot_expenses'];
	$totIncomes = $ret['outarr']['tot_incomes'];
	$totExpenses = $ret['outarr']['tot_expenses'];
	?>

	<table width='100%' cellspacing='0' cellpadding='0' border='0' class='totals'>
	<tr><td align='right' style='font-size:13px;color:#666666'><i>Totali:</i>&nbsp;</td>
		<td width='75' class='result'><?php echo number_format($totIncomes,2,',','.'); ?></td>
		<td width='75' class='result'><?php echo number_format($totExpenses,2,',','.'); ?></td>
		<td width='217'>&nbsp;</td></tr>
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

<script>
var SELECTED_ROWS = new Array();
var COUNT = <?php echo $count ? $count : "0"; ?>;
var RESULTS_PER_PAGE = <?php echo $serpInfo['resultsperpage'] ? $serpInfo['resultsperpage'] : "20"; ?>;
var CURRENT_PAGE = <?php echo $_REQUEST['pg'] ? $_REQUEST['pg'] : "1"; ?>;
var PAGES_COUNT = 1;
var SELECTED_CATALOG = "";
var CAT_ID = <?php echo $_REQUEST['catid'] ? $_REQUEST['catid'] : "0"; ?>;
var RES_ID = <?php echo $_REQUEST['resid'] ? $_REQUEST['resid'] : "0"; ?>;

var ORDERBY_FIELD = "<?php echo $_ORDERBY_FIELD; ?>";
var ORDERBY_METHOD = "<?php echo $_ORDERBY_METHOD; ?>";

function orderbyChange(field)
{
 if(field == ORDERBY_FIELD)
  ORDERBY_METHOD = (ORDERBY_METHOD == 'ASC') ? 'DESC' : 'ASC';
 else
 {
  ORDERBY_FIELD = field;
  ORDERBY_METHOD = "ASC";
 }
 updateQry();
}

function desktopOnLoad()
{
 var div = document.getElementById('storepagecontainer');
 div.style.height = div.parentNode.offsetHeight-20;
 var div2 = document.getElementById('itemlist').parentNode;
 div2.style.height = div2.parentNode.offsetHeight-130;

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

 new GPopupMenu(document.getElementById('mainmenu'), document.getElementById('mainmenulist'));
 filterChanged(document.getElementById('filter'),true);
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
	   r.insertCell(-1).innerHTML = "<a href='#' onclick='editRecord("+itm['id']+")'>"+date.printf("d/m/Y")+"</a>";
	   r.insertCell(-1).innerHTML = "<span class='subject'>"+(itm['subject_name'] ? itm['subject_name'] : "&nbsp;")+"</span>";
	   r.insertCell(-1).innerHTML = "<small>"+itm['name']+"</small>";
	   if(RES_ID)
	   {
	    if(itm['res_in'] && (RES_ID == itm['res_in']['id']))
		 r.insertCell(-1).innerHTML = parseFloat(itm['incomes']) ? formatCurrency(itm['incomes'],2) : "&nbsp;";
		else
		 r.insertCell(-1).innerHTML = "&nbsp;";
	    if(itm['res_out'] && (RES_ID == itm['res_out']['id']))
		 r.insertCell(-1).innerHTML = parseFloat(itm['expenses']) ? formatCurrency(itm['expenses'],2) : "&nbsp;";
		else
		 r.insertCell(-1).innerHTML = "&nbsp;";
	   }
	   else
	   {
	    r.insertCell(-1).innerHTML = parseFloat(itm['incomes']) ? formatCurrency(itm['incomes'],2) : "&nbsp;";
	    r.insertCell(-1).innerHTML = parseFloat(itm['expenses']) ? formatCurrency(itm['expenses'],2) : "&nbsp;";
	   }
	   r.insertCell(-1).innerHTML = (itm['doc_info'] ? "<a href='"+ABSOLUTE_URL+"GCommercialDocs/docinfo.php?id="+itm['doc_info']['id']+"' target='GCD-"+itm['doc_info']['id']+"'>"+itm['doc_info']['name']+"</a>" : (itm['doc_ref'] ? itm['doc_ref'] : "&nbsp;"));

	   r.cells[0].style.width = "32px"; r.cells[0].style.textAlign='center';
	   r.cells[1].style.width = "80px"; r.cells[1].className = "date"; 
	   r.cells[2].style.width = "120px"; 

	   r.cells[4].style.width = "70px"; r.cells[4].className = "incomes";
	   r.cells[5].style.width = "70px"; r.cells[5].className = "expenses";
	   r.cells[6].style.width = "200px"; r.cells[6].className = "smalllink";
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
 var href = ABSOLUTE_URL+"BookKeeping/index.php?page=";
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

 /*if(SELECTED_ROWS.length)
  document.getElementById('selectionmenu').style.visibility = "visible";
 else
  document.getElementById('selectionmenu').style.visibility = "hidden";*/
}

function selectRow(cb)
{
 var r = cb.parentNode.parentNode;
 r.className = cb.checked ? "selected" : "";
 if(cb.checked && (SELECTED_ROWS.indexOf(r) < 0))
  SELECTED_ROWS.push(r);
 else if(!cb.checked && (SELECTED_ROWS.indexOf(r) > -1))
  SELECTED_ROWS.splice(SELECTED_ROWS.indexOf(r),1);

 /*if(SELECTED_ROWS.length)
  document.getElementById('selectionmenu').style.visibility = "visible";
 else
  document.getElementById('selectionmenu').style.visibility = "hidden";*/
}

function updateQry()
{
 var href = ABSOLUTE_URL+"BookKeeping/index.php?filter=<?php echo $_REQUEST['filter']; ?>";
 var ed = document.getElementById('search');
 href+="&search="+ed.value;
 switch(document.getElementById('filter').value)
 {
  case 'subject' : {
	 if(ed.value && ed.data)
	  href+= "&subjectid="+ed.data['id'];
	 else if(ed.value)
	  href+= "&subject="+ed.value;
	} break;
  case 'category' : {
	 if(ed.value && ed.data)
	  href+= "&catid="+ed.data['id'];
	 else if(CAT_ID && (ed.value == ed.defaultValue))
	  href+= "&catid="+CAT_ID;
	 else
	  href+= "&catname="+ed.value;
	} break;
  case 'resource' : {
	 var resId = document.getElementById("resource").value;
	 href+= "&resid="+resId;
	} break;
  default : {
	 if(ed.value)
	  href+= "&description="+ed.value;
	} break;
 }

 var from = document.getElementById('from');
 var to = document.getElementById('to');
 if(from.value)
  href+= "&from="+strdatetime_to_iso(from.value);
 if(to.value)
  href+= "&to="+strdatetime_to_iso(to.value);

 href+= "&orderby="+ORDERBY_FIELD+"&ordermethod="+ORDERBY_METHOD;

 document.location.href=href;

}

function newIN()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.reload();
	}
 sh.sendCommand("gframe -f pettycashbook/new.credit");
}

function newOUT()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.reload();
	}
 sh.sendCommand("gframe -f pettycashbook/new.debit");
}

function newTransfer()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.reload();
	}
 sh.sendCommand("gframe -f pettycashbook/new.transfer");
}

function filterChanged(sel, noclear)
{
 switch(sel.value)
 {
  case 'subject' : var mE = EditSearch.init(document.getElementById('search'),"dynarc search -ap `rubrica` -fields code_str,name `","` -limit 10 --order-by 'code_str,name ASC'","id","name","items",true,"name"); break;
  case 'category' : var mE = EditSearch.init(document.getElementById('search'),"dynarc cat-find -ap `pettycashbook` -field name `","` -limit 10 --order-by 'name ASC'","id","name",null,true,"name"); break;
  default : EditSearch.free(document.getElementById('search')); break;
 }
 if(!noclear)
  document.getElementById('search').value = "";

 if(sel.value == "resource")
 {
  document.getElementById('search').style.display = "none";
  document.getElementById('resource').style.display = "";
 }
 else
 {
  document.getElementById('search').style.display = "";
  document.getElementById('resource').style.display = "none";
  document.getElementById('search').focus();
 }
}

function editRecord(id)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.reload();
	}
 sh.sendCommand("gframe -f pettycashbook/edit.record -params `id="+id+"`");
}

function printPreview()
{
 var sh = new GShell();
 sh.sendCommand("gframe -f print.preview -params `modelap=printmodels&modelct=pettycashbook&parser=pettycashbook&qry="+encodeURI("<?php echo str_replace('`','\'',$qry); ?>")+"&totincomes=<?php echo $totIncomes; ?>&totexpenses=<?php echo $totExpenses; ?>&catid="+CAT_ID+"&resid="+RES_ID+"&from="+document.getElementById('from').value+"&to="+document.getElementById('to').value+"` -title `Stampa prima nota`");
}

function deleteSelected()
{
 if(!SELECTED_ROWS.length)
  return alert("Nessun movimento selezionato");
 if(!confirm("Sei sicuro di voler eliminare i movimenti selezionati?"))
  return;

 var q = "";
 for(var c=0; c < SELECTED_ROWS.length; c++)
  q+= " -id "+SELECTED_ROWS[c].id;

 var sh = new GShell();
 sh.showProcessMessage("Eliminazione in corso", "Attendere prego, è in corso l'eliminazione dei movimenti selezionati");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(){
	 this.hideProcessMessage();
	 document.location.reload();
	}
 sh.sendCommand("pettycashbook delete"+q);
}

function ExportToExcel()
{
 var qry = "<?php echo $qry; ?>";
 var cmd = "pettycashbook export-to-excel -file 'primanota'"+qry.substr(18);

 var sh = new GShell();
 sh.showProcessMessage("Esportazione in Excel", "Attendere prego, è in corso l'esportazione della prima nota su file Excel.");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(o,a){
	 this.hideProcessMessage();
	 if(!a) return;
	 var fileName = a['filename'];
	 document.location.href = ABSOLUTE_URL+"getfile.php?file="+fileName;
	}
 sh.sendCommand(cmd);
}
</script>
<?php

