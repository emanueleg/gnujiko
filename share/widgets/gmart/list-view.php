<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 04-10-2013
 #PACKAGE: gmart
 #DESCRIPTION: List view for GMart
 #VERSION: 2.4beta
 #CHANGELOG: 04-10-2013 : Bug fix, non calcolava lo sconto
			 24-07-2013 : Modifiche varie.
			 04-02-2013 : Bug fix.
 #TODO:
 
*/

$_SELECTED_IDS = array();
$_SELECTED_KIDS = array();

if($_REQUEST['selected'])
{
 $_SELECTED_IDS = explode(",",$_REQUEST['selected']);
 for($c=0; $c < count($_SELECTED_IDS); $c++)
  $_SELECTED_KIDS[$_SELECTED_IDS[$c]] = true;
}

$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : "gmart";
$_PLID = 0;
$_PLGET = "";
$_PLINFO = array();
if(count($_PRICELISTS))
{
 $_PLINFO = $_PRICELISTS[0];
 $_PLID = $_PRICELISTS[0]['id'];
 $_PLGET = "pricelist_".$_PLID."_baseprice,pricelist_".$_PLID."_mrate,pricelist_".$_PLID."_vat,pricelist_".$_PLID."_discount";
}

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/list-view.css" type="text/css" />
<table width='100%' class='itemlist' id="itemlist-<?php echo $_REQUEST['pg'] ? ($_REQUEST['pg']-1) : '0'; ?>" cellspacing='0' cellpadding='0' border='0'>
<tr><th width='40'><input type='checkbox' onchange='selectAll(this.checked,this)'/></th>
	<th width='180' class='sortasc'>ARTICOLO</th>
	<th>DESCRIZIONE</th>
	<th width='80' style='text-align:right;'>PREZZO</th>
	<th width='30'>DISP.</th>
	<th width='30'>ORD.</th>
</tr>
<?php 
$rpp = $_REQUEST['limit'] ? $_REQUEST['limit'] : 20;
$from = $_REQUEST['pg'] ? ($rpp*($_REQUEST['pg']-1)) : 0;
$ret = GShell("dynarc item-list -ap `".$_AP."`".($_REQUEST['catid'] ? " -cat `".$_REQUEST['catid']."`" : "")." -extget `gmart,thumbnails,coding,storeinfo,pricing`".($_PLGET ? " -get `".$_PLGET."`" : "")." -limit ".($from ? $from : "0").",".$rpp." --return-serp-info",$_REQUEST['sessid'],$_REQUEST['shellid']);

$list = $ret['outarr']['items'];
$count = $ret['outarr']['count'];
$serpInfo = $ret['outarr']['serpinfo'];

for($c=0; $c < count($list); $c++)
{
 $item = $list[$c];
 echo "<tr id='".$item['id']."'".($_SELECTED_KIDS[$item['id']] ? " class='selected'>" : ">");
 echo "<td align='center'>";
 if($item['thumbnails'][0] && ($_REQUEST['thumbmode'] != "never"))
  echo "<img src='".$_ABSOLUTE_URL.$item['thumbnails'][0]."' width='32'/>";
 else if(($_REQUEST['thumbmode'] != "notall") && ($_REQUEST['thumbmode'] != "never"))
  echo "<img src='".$_ABSOLUTE_URL."share/widgets/gmart/img/photo.png' width='32'/>";
 else
  echo "&nbsp;";
 echo "<input type='checkbox' class='checkbox' onchange='itemSelect(this)'".($_SELECTED_KIDS[$item['id']] ? " checked='true'/>" : "/>");
 echo "</td><td>";
 echo "<div class='title'><span onclick=\"editItem('".$item['id']."',this)\">".$item['name']."</span></div>";
 echo "<div class='info'><i>code:</i> <b>".$item['code_str']."</b></div></td>";
 echo "<td><div class='description'>".$item['desc']."</div></td>";

 $baseprice = $item["pricelist_".$_PLID."_baseprice"] ? $item["pricelist_".$_PLID."_baseprice"] : $item['baseprice'];
 $markuprate = $item["pricelist_".$_PLID."_mrate"] ? $item["pricelist_".$_PLID."_mrate"] : $_PLINFO['markuprate'];
 $discount = $item["pricelist_".$_PLID."_discount"] ? $item["pricelist_".$_PLID."_discount"] : 0;
 $vat = $item["pricelist_".$_PLID."_vat"] ? $item["pricelist_".$_PLID."_vat"] : $_PLINFO['vat'];
 $finalPrice = $baseprice ? $baseprice + (($baseprice/100)*$markuprate) : 0;
 $finalPrice = $finalPrice ? $finalPrice - (($finalPrice/100)*$discount) : 0;
 $finalPriceVI = $finalPrice ? $finalPrice + (($finalPrice/100)*$vat) : 0;

 echo "<td style='text-align:right;'><span class='green-block'>€. ".number_format($finalPriceVI,$_DECIMALS,",",".")."</span></td>";
 echo "<td align='center'>";
 $dis = $item['storeqty']-$item['booked'];
 if($dis <= 0)
  echo "<span class='red'><b>0</b></span></td>";
 else
  echo "<span class='black'>".$dis."</span>";
 echo "</td><td align='center'><span class='black'>".$item['incoming']."</span></td>";
 echo "</tr>";
}
?>
</table>
<?php
if($count)
{
 ?>
 <div class="loading" style="visibility:hidden;" id="loading">&nbsp;</div>

 <div class="otherresultsbtn-div" align="center" id="otherresults" style="display:none;">
  <span class="otherresultsbtn" onclick="pageChange(<?php echo (($serpInfo['currentpage']-1)+5); ?>)">Mostra altri risultati</span>
 </div>

 <div class="footerresults" id="footerresults" style="display:none;">
 <?php
 $rpp = 100;
 $from = ($serpInfo['resultsperpage']*($serpInfo['currentpage']-1))+1;
 $to = ($from+$rpp-1) > $count ? $count : ($from+$rpp-1);
 ?>
  <span class='green'>Risultati: da </span><b><?php echo $from; ?></b>
  <span class='green'>a</span> <b><?php echo $to; ?></b>
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
 <?php
}
?>

<script>
var AP = "<?php echo $_AP ? $_AP : 'gmart'; ?>";
var CAT_ID = <?php echo $_REQUEST['catid'] ? $_REQUEST['catid'] : "0"; ?>;
var COUNT = <?php echo $count ? $count : "0"; ?>;
var RESULTS_PER_PAGE = <?php echo $serpInfo['resultsperpage'] ? $serpInfo['resultsperpage'] : "20"; ?>;
var CURRENT_PAGE = <?php echo $_REQUEST['pg'] ? $_REQUEST['pg'] : "1"; ?>;
var SELECTED_STR = ",<?php echo $_REQUEST['selected']; ?>,";
var PAGES_COUNT = 1;
var PLID = <?php echo $_PLID ? $_PLID : "0"; ?>;
var PLGET = "<?php echo $_PLGET; ?>";
var DECIMALS = <?php echo $_DECIMALS ? $_DECIMALS : "0"; ?>;
var THUMBMODE = "<?php echo $_REQUEST['thumbmode']; ?>";

function bodyOnLoad()
{
 if(COUNT > (RESULTS_PER_PAGE * CURRENT_PAGE))
 {
  document.getElementById('loading').style.visibility="visible";
  window.setTimeout(function(){nextPage();},2000);
 }
 else if(COUNT)
 {
  document.getElementById('loading').style.display='none';
  document.getElementById('footerresults').style.display='';
  document.getElementById('footerresults').style.marginTop = 30;
  document.getElementById('footerpagesel').value = (CURRENT_PAGE-1);  
 }
}

function nextPage()
{
 document.getElementById('loading').style.display="";

 var FROM = (RESULTS_PER_PAGE*CURRENT_PAGE)+1;
 var TO = (FROM+RESULTS_PER_PAGE-1) > COUNT ? COUNT : (FROM+RESULTS_PER_PAGE-1);

 var div = document.createElement('DIV');
 div.className = "pagecontainer";
 var html = "<span class='pagespan-title'>Pagina n. "+(CURRENT_PAGE+1)+"</span> <span class='pagespan-results'>Risultati: da <b>"+FROM+"</b> a <b>"+TO+"</b></span>";
 html+= "<table width='100%' class='itemlist' id='itemlist-"+(CURRENT_PAGE)+"' cellspacing='0' cellpadding='0' border='0'>";
 html+= "<tr><th width='40'><input type='checkbox' onchange='selectAll(this.checked,this)'/ ></th>";
 html+= "<th width='180' class='sortasc'>ARTICOLO</th><th>DESCRIZIONE</th><th width='80' style='text-align:right;'>PREZZO</th><th width='30'>DISP.</th><th width='30'>ORD.</th></tr>";


 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(a && a['items'])
	 {
	  for(var c=0; c < a['items'].length; c++)
	  {
	   var item = a['items'][c];
	   var selected = (SELECTED_STR.indexOf(","+item['id']+",") >= 0) ? true : false;
	   html+= "<tr id='"+item['id']+"'"+(selected ? " class='selected'>" : ">")+"<td align='center'>";
	   if(item['thumbnails'] && item['thumbnails'][0] && (THUMBMODE != "never"))
	    html+= "<img src='"+ABSOLUTE_URL+item['thumbnails'][0]+"' width='32'/ >";
	   else if((THUMBMODE != "notall") && (THUMBMODE != "never"))
		html+= "<img src='"+ABSOLUTE_URL+"share/widgets/gmart/img/photo.png' width='32'/ >";
	   else
		html+= "&nbsp;";
	   html+= "<input type='checkbox' class='checkbox' onchange='itemSelect(this)'"+(selected ? " checked='true'/ >" : "/ >")+"</td><td>";
	   html+= "<div class='title'><span onclick=\"editItem('"+item['id']+"',this)\">"+item['name']+"</span></div>";
	   html+= "<div class='info'><i>code:</i> <b>"+item['code_str']+"</b></div></td>";
	   html+= "<td><div class='description'>"+item['desc']+"</div></td>";

	   var baseprice = parseFloat(item["pricelist_"+PLID+"_baseprice"] ? item["pricelist_"+PLID+"_baseprice"] : item['baseprice']);
 	   var markuprate = parseFloat(item["pricelist_"+PLID+"_mrate"] ? item["pricelist_"+PLID+"_mrate"] : <?php echo $_PLINFO['markuprate']; ?>);
	   var discount = parseFloat(item["pricelist_"+PLID+"_discount"] ? item["pricelist_"+PLID+"_discount"] : 0);
	   var vat = parseFloat(item["pricelist_"+PLID+"_vat"] ? item["pricelist_"+PLID+"_vat"] : <?php echo $_PLINFO['vat']; ?>);
 	   var finalPrice = baseprice ? baseprice + ((baseprice/100)*markuprate) : 0;
 	   var finalPrice = finalPrice ? finalPrice - ((finalPrice/100)*discount) : 0;
 	   var finalPriceVI = finalPrice ? finalPrice + ((finalPrice/100)*vat) : 0;

	   html+= "<td style='text-align:right;'><span class='green-block'>€. "+formatCurrency(finalPriceVI,DECIMALS)+"</span></td>";
	   html+= "<td align='center'>";
	   
	   var dis = item['storeqty']-item['booked'];
	   if(dis <= 0)
		html+= "<span class='red'><b>0</b></span></td>";
	   else
		html+= "<span class='black'>"+dis+"</span>";
	   html+= "</td><td align='center'><span class='black'>"+item['incoming']+"</span></td></tr>";
	  }
	 }
	 div.innerHTML = html;
	 document.body.insertBefore(div,document.getElementById('loading'));
	 gframe_autoresize();
	 CURRENT_PAGE++;
	 PAGES_COUNT++;
	 if((PAGES_COUNT == 5) && (COUNT > (RESULTS_PER_PAGE*CURRENT_PAGE)))
	 {
	  document.getElementById('loading').style.display='none';
	  document.getElementById('otherresults').style.display='';
	  document.getElementById('footerresults').style.display='';
	  document.getElementById('footerpagesel').value = (CURRENT_PAGE-1);
	 }
	 else if(COUNT > (RESULTS_PER_PAGE*CURRENT_PAGE))
	  window.setTimeout(function(){nextPage();},2000);
	 else
	 {
	  document.getElementById('loading').style.display='none';
	  document.getElementById('footerresults').style.display='';
	  document.getElementById('footerpagesel').value = (CURRENT_PAGE-1);  
	 }
	}
 sh.sendCommand("dynarc item-list -ap `"+AP+"`"+(CAT_ID ? " -cat `"+CAT_ID+"`" : "")+" -extget `gmart,thumbnails,coding,storeinfo,pricing`"+(PLGET ? " -get `"+PLGET+"`" : "")+" -limit "+(RESULTS_PER_PAGE*CURRENT_PAGE)+","+RESULTS_PER_PAGE);

}

function selectAll(selected,cbObj)
{
 if(cbObj)
 { 
  var tb = cbObj.parentNode.parentNode.parentNode;
  for(var c=1; c < tb.rows.length; c++)
  {
   var cb = tb.rows[c].cells[0].getElementsByTagName('INPUT')[0];
   if(cb.checked != selected)
   {
    cb.checked = selected;
    itemSelect(cb);
   }
  }
 }

}

function itemSelect(cb)
{
 var tr = cb.parentNode.parentNode;
 if(cb.checked)
 {
  tr.className = "selected";
  gframe_shotmessage("Item select", tr.id, "SELECT");
 }
 else
 {
  tr.className = "";
  gframe_shotmessage("Item unselect", tr.id, "UNSELECT");
 }
}

function editItem(id,span)
{
 gframe_shotmessage("Edit item", id, "EDIT_ITEM");
 span.className = "visited";
}

function pageChange(page)
{
 var ret = new Array();
 ret['page'] = parseInt(page)+1;
 ret['limit'] = RESULTS_PER_PAGE;
 gframe_shotmessage("Jump to page "+ret['page'], ret, "JUMP_TO_PAGE");
}

</script>
<?php

