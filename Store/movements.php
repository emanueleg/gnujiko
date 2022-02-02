<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 29-11-2012
 #PACKAGE: gstore
 #DESCRIPTION: Official Gnujiko Store Manager.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

include_once($_BASE_PATH."var/objects/htmlgutility/menu.php");
include_once($_BASE_PATH."var/objects/editsearch/index.php");

$_AP = $_ARCHIVE_INFO ? $_ARCHIVE_INFO['prefix'] : "gmart";

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>Store/movements.css" type="text/css" />
<table width='100%' cellspacing='8' cellpadding='0' border='0'>
<tr><td valign='top' width='200'>
	 <?php
	 if($_REQUEST['storeid'])
	 {
	  $ret = GShell("store info -id `".$_REQUEST['storeid']."`");
	  $storeInfo = $ret['outarr'];
	 }
	 else
	  $storeInfo = array('id'=>0,'name'=>"Tutti i magazzini");
	 ?>
	 <div class='storeselect' id='storeselect' storeid="<?php echo $storeInfo['id']; ?>"><span><?php echo $storeInfo['name']; ?></span><img src="<?php echo $_ABSOLUTE_URL; ?>Store/img/tiptop-dnarr.png"/></div>
	  <ul class="submenu" id='storeselectmenu'>
	   <?php
	   $ret = GShell("store list");
	   $list = $ret['outarr'];
	   for($c=0; $c < count($list); $c++)
		echo "<li onclick='selectStore(".$list[$c]['id'].",this)'>".$list[$c]['name']."</li>";
	   if(count($list))
		echo "<li class='separator'>&nbsp;</li>";
	   ?>
	   <li onclick='selectStore(0,this)'>Tutti i magazzini</li>
	  </ul>

	</td>
	<td width='130' style='font-size:12px;'>
	 Dal: <input type='text' class='searchinput' style='width:100px' id='from' value="<?php if($_REQUEST['from']) echo date('d/m/Y H:i',strtotime($_REQUEST['from'])); ?>"/>
	</td>
	<td width='130' style='font-size:12px;'>
	 al: <input type='text' class='searchinput' style='width:100px' id='to' value="<?php if($_REQUEST['to']) echo date('d/m/Y H:i',strtotime($_REQUEST['to'])); ?>"/>
	</td>

	<td width='30'>&nbsp;</td>

	<td>
	<input type='text' id='search' class='searchinput' emptyvalue="Cerca per articolo" style="width:100%;" value="<?php echo $_REQUEST['code'] ? $_REQUEST['code'] : 'Cerca per articolo'; ?>"/>
	</td>
	<td width='90'>
	<ul class='basicbuttons'>
		 <li><span href='#' onclick='updateQry()'><img src="<?php echo $_ABSOLUTE_URL; ?>Store/img/search.gif" border='0'/> Cerca</span></li>
	</ul>
	</td>
</tr>
</table>

<div style='margin-left:8px;'>
<table width='100%' class='itemlist' cellspacing='0' cellpadding='0' border='0'>
<tr><th width='32'><input type='checkbox' onchange="selectAllRows(this)" id="tbselectall"/></th>
	<th width='70'>DATA</th>
	<th width='50'>ORA</th>
	<th width='90'>OPERAZIONE</th>
	<th width='60'>CODICE</th>
	<th style='text-align:left;'>ARTICOLO</th>
	<th width='50'>QTA&lsquo;</th>
    <th width='200' style='text-align:left;'>DOC. DI RIFERIMENTO</th>
	<th width='10'>&nbsp;</th>
</tr>
</table>
</div>

<div class="itemlist-container" style="height:100px;margin-left:8px;">
	<table width='100%' class='itemlist' id='itemlist' cellspacing='0' cellpadding='0' border='0'>
	<?php
	$rpp = $_REQUEST['limit'] ? $_REQUEST['limit'] : 20;
	$from = $_REQUEST['pg'] ? ($rpp*($_REQUEST['pg']-1)) : 0;

	$qry = "store movements".($_REQUEST['storeid'] ? " -store `".$_REQUEST['storeid']."`" : "");
	if($_REQUEST['code'])
	 $qry.= " -refcode `".$_REQUEST['code']."`";
    if($_REQUEST['from'])
	 $qry.= " -from `".$_REQUEST['from']."`";
	if($_REQUEST['to'])
	 $qry.= " -to `".$_REQUEST['to']."`";

	$qry.= " --order-by `op_time ".($_REQUEST['sort']=='asc' ? "ASC" : "DESC")."` --return-serp-info";

	$ret = GShell($qry." -limit ".($from ? $from : "0").",".$rpp);
	if(!$ret['error'])
	{
	 $count = $ret['outarr']['count'];
	 $list = $ret['outarr']['items'];
	 $serpInfo = $ret['outarr']['serpinfo'];
	}
	for($c=0; $c < count($list); $c++)
	{
	 $itm = $list[$c];

	 echo "<tr id='".$itm['id']."' refap='".$itm['ref_ap']."'><td width='32' align='center'><input type='checkbox' onclick='selectRow(this)'/></td>";
	 echo "<td width='70' align='center'><b>".date('d/m/Y',$itm['ctime'])."</b></td>";
	 echo "<td width='50' align='center'><span class='gray'><b>".date('H:i',$itm['ctime'])."</b></span></td>";
	 echo "<td width='90' align='center'>";
	 switch($itm['action'])
	 {
	  case 1 : echo "<span class='blue'>CARICO</span>"; break;
	  case 2 : echo "<span class='green'>SCARICO</span>"; break;
	  case 3 : echo "<span class='darkblue'>MOVIMENTA</span>"; break;
	 }
	 echo "</td>";
	 echo "<td width='60' align='center'>".$itm['code']."</td>";
	 echo "<td><a href='#' onclick=\"showItemInfo('".$itm['ref_ap']."','".$itm['ref_id']."')\">".$itm['name']."</a></td>";
	 echo "<td width='50' align='center'><span class='gray'><b>".$itm['qty']."</b></span></td>";
	 echo "<td width='200'>".(($itm['doc_ap'] && $itm['doc_id']) ? "<a href='#' onclick=\"showDocInfo('".$itm['doc_ap']."','".$itm['doc_id']."')\">".$itm['doc_name']."</a>" : ($itm['doc_ref'] ? $itm['doc_ref'] : "&nbsp;"))."</td></tr>";
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

<script>
var AP = "<?php echo $_AP; ?>";
var SELECTED_ROWS = new Array();
var COUNT = <?php echo $count ? $count : "0"; ?>;
var RESULTS_PER_PAGE = <?php echo $serpInfo['resultsperpage'] ? $serpInfo['resultsperpage'] : "20"; ?>;
var CURRENT_PAGE = <?php echo $_REQUEST['pg'] ? $_REQUEST['pg'] : "1"; ?>;
var PAGES_COUNT = 1;
var SELECTED_CATALOG = "";

function desktopOnLoad()
{
 var div = document.getElementById('storepagecontainer');
 div.style.height = div.parentNode.offsetHeight-30;
 var div2 = document.getElementById('itemlist').parentNode;
 div2.style.height = div2.parentNode.offsetHeight-80;

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

 new GPopupMenu(document.getElementById('storeselect'), document.getElementById('storeselectmenu'));

 var mE = EditSearch.init(document.getElementById('search'),
	"dynarc search -at `gmart` -fields code_str,name `","` -limit 10 --order-by 'code_str,name ASC'",
	"id","name","items",true,"code_str",onSearchQry);
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
	 var storeid = document.getElementById('storeselect').getAttribute('storeid');
	 if(a && a['items'])
	 {
	  var tb = document.getElementById('itemlist');
	  var date = new Date();
	  for(var c=0; c < a['items'].length; c++)
	  {
	   var itm = a['items'][c];
	   date.setTime(parseFloat(itm['ctime'])*1000);

	   var r = tb.insertRow(-1);
	   r.id = itm['id'];
	   r.insertCell(-1).innerHTML = "<input type='checkbox' onclick='selectRow(this)'/ >";
	   r.insertCell(-1).innerHTML = "<b>"+date.printf('d/m/Y')+"</b>";
	   r.insertCell(-1).innerHTML = "<span class='gray'><b>"+date.printf('H:i')+"</b></span>";
	   switch(itm['action'])
	   {
	    case '1' : r.insertCell(-1).innerHTML = "<span class='blue'>CARICO</span>"; break;
	    case '2' : r.insertCell(-1).innerHTML = "<span class='green'>SCARICO</span>"; break;
	    case '3' : r.insertCell(-1).innerHTML = "<span class='darkblue'>MOVIMENTA</span>"; break;
		default : r.insertCell(-1).innerHTML = "&nbsp;"; break;
	   }
	   r.insertCell(-1).innerHTML = itm['code'];
	   r.insertCell(-1).innerHTML = "<a href='#' onclick=\"showItemInfo('"+itm['ref_ap']+"','"+itm['ref_id']+"')\">"+itm['name']+"</a>";
	   r.insertCell(-1).innerHTML = "<span class='gray'><b>"+itm['qty']+"</b></span>";
	   r.insertCell(-1).innerHTML = ((itm['doc_ap'] && itm['doc_id']) ? "<a href='#' onclick=\"showDocInfo('"+itm['doc_ap']+"','"+itm['doc_id']+"')\">"+itm['doc_name']+"</a>" : (itm['doc_ref'] ? itm['doc_ref'] : "&nbsp;"));

	   r.cells[0].style.width = "32px"; r.cells[0].style.textAlign='center';
	   r.cells[1].style.width = "70px"; r.cells[1].style.textAlign='center';
	   r.cells[2].style.width = "50px"; r.cells[2].style.textAlign='center';
	   r.cells[3].style.width = "90px"; r.cells[3].style.textAlign='center';
	   r.cells[4].style.width = "60px"; r.cells[4].style.textAlign='center';

	   r.cells[6].style.width = "50px"; r.cells[6].style.textAlign='center';
	   r.cells[7].style.width = "200px"; r.cells[7].style.textAlign='center';
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
 var href = document.location.href.replace("#","");
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

function selectStore(id, li)
{
 document.getElementById('storeselect').getElementsByTagName('SPAN')[0].innerHTML = li.innerHTML;
 document.getElementById('storeselect').setAttribute('storeid',id);
 updateQry();
}

function updateQry()
{
 var from = document.getElementById('from').value;
 var to = document.getElementById('to').value;
 var code = "";
 
 var ed = document.getElementById('search');
 if(ed.value && (ed.value != ed.getAttribute('emptyvalue')))
  code = ed.value;

 if(from) from = strdatetime_to_iso(from);
 if(to) to = strdatetime_to_iso(to);

 var href = ABSOLUTE_URL+"Store/index.php?page=movements&aid=<?php echo $_ARCHIVE_INFO ? $_ARCHIVE_INFO['id'] : '0'; ?>";
 href+= "&storeid="+document.getElementById('storeselect').getAttribute('storeid');
 if(from)
  href+= "&from="+from;
 if(to)
  href+= "&to="+to;
 if(code)
  href+= "&code="+code;

 document.location.href=href;
}

function showItemInfo(ap,id)
{
 var sh = new GShell();
 sh.sendCommand("gframe -f gmart/edit.item -params `ap="+ap+"&id="+id+"`");
}

function showDocInfo(ap,id)
{
 var sh = new GShell();
 sh.sendCommand("dynlaunch -ap `"+ap+"` -id `"+id+"`");
}

function printStoreMovementsPreview()
{
 var sh = new GShell();
 sh.sendCommand("gframe -f print.preview -params `modelap=printmodels&modelct=storemovements&parser=storemovements&storeid=<?php echo $_REQUEST['storeid']; ?>&qry="+encodeURI("<?php echo str_replace('`','\'',$qry); ?>")+"` -title `Movimenti di magazzino`");
}

function deleteSelectedMovements()
{
 if(!SELECTED_ROWS.length)
  return alert("Nessun movimento selezionato");
 
 var q = "";
 for(var c=0; c < SELECTED_ROWS.length; c++)
  q+= ","+SELECTED_ROWS[c].id;

 var sh = new GShell();
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("gframe -f gstore/delete.movements -params `ids="+q.substr(1)+"`");
}
</script>
<?php

