<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 10-07-2012
 #PACKAGE: gserv
 #DESCRIPTION: Small thumbnails view for GServ
 #VERSION: 2.0beta
 #CHANGELOG:
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

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gserv/smallthumb-view.css" type="text/css" />
<?php 
$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : "gserv";
$_PLID = 0;
$_PLGET = "";
$_PLINFO = array();
if(count($_PRICELISTS))
{
 $_PLINFO = $_PRICELISTS[0];
 $_PLID = $_PRICELISTS[0]['id'];
 $_PLGET = "pricelist_".$_PLID."_baseprice,pricelist_".$_PLID."_mrate,pricelist_".$_PLID."_vat";
}

$rpp = $_REQUEST['limit'] ? $_REQUEST['limit'] : 50;
$from = $_REQUEST['pg'] ? ($rpp*($_REQUEST['pg']-1)) : 0;
$ret = GShell("dynarc item-list -ap `".$_AP."`".($_REQUEST['catid'] ? " -cat `".$_REQUEST['catid']."`" : "")." -extget `gserv,thumbnails,coding,pricing`"
	.($_PLGET ? " -get `".$_PLGET."`" : "")." -limit ".($from ? $from : "0").",".$rpp." --return-serp-info",$_REQUEST['sessid'],$_REQUEST['shellid']);

$list = $ret['outarr']['items'];
$count = $ret['outarr']['count'];
$serpInfo = $ret['outarr']['serpinfo'];

for($c=0; $c < count($list); $c++)
{
 $item = $list[$c];
 echo "<div class='itemblock' id='".$item['id']."'";
 if($_SELECTED_KIDS[$item['id']]) 
  echo " style='background-image:url(img/item-block-smallthumb-selected.png)'";
 echo ">";
 if($item['thumbnails'][0])
  echo "<div class='thumbnail' style='background-image: url(".$_ABSOLUTE_URL.$item['thumbnails'][0].");'>";
 else
  echo "<div class='thumbnail' style='background-image: url(".$_ABSOLUTE_URL."share/widgets/gserv/img/photo.png);'>";
 echo "<input type='checkbox' class='checkbox' onchange='itemSelect(this)'";
 if($_SELECTED_KIDS[$item['id']]) 
  echo " checked='true'";
 echo "/><span class='title' onclick=\"editItem('".$item['id']."',this)\">".$item['name']."</span></div>";
 
 $baseprice = $item["pricelist_".$_PLID."_baseprice"] ? $item["pricelist_".$_PLID."_baseprice"] : $item['baseprice'];
 $markuprate = $item["pricelist_".$_PLID."_mrate"] ? $item["pricelist_".$_PLID."_mrate"] : $_PLINFO['markuprate'];
 $vat = $item["pricelist_".$_PLID."_vat"] ? $item["pricelist_".$_PLID."_vat"] : $_PLINFO['vat'];
 $finalPrice = $baseprice ? $baseprice + (($baseprice/100)*$markuprate) : 0;
 $finalPriceVI = $finalPrice ? $finalPrice + (($finalPrice/100)*$vat) : 0;

 echo "<div class='footer'> <span><i>Prezzo</i></span> <em>".number_format($finalPriceVI,$_DECIMALS,",",".")." €</em> </div>";
 echo "</div>";
}

if($count)
{
 ?>
 <div class="loading" style="visibility:hidden;" id="loading">&nbsp;</div>

 <div class="otherresultsbtn-div" align="center" id="otherresults" style="display:none;">
  <span class="otherresultsbtn" onclick="pageChange(<?php echo (($serpInfo['currentpage']-1)+5); ?>)">Mostra altri risultati</span>
 </div>

 <div class="footerresults" id="footerresults" style="display:none;">
 <?php
 $rpp = 250;
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
var AP = "<?php echo $_AP ? $_AP : 'gserv'; ?>";
var CAT_ID = <?php echo $_REQUEST['catid'] ? $_REQUEST['catid'] : "0"; ?>;
var COUNT = <?php echo $count ? $count : "0"; ?>;
var RESULTS_PER_PAGE = <?php echo $serpInfo['resultsperpage'] ? $serpInfo['resultsperpage'] : "20"; ?>;
var CURRENT_PAGE = <?php echo $_REQUEST['pg'] ? $_REQUEST['pg'] : "1"; ?>;
var SELECTED_STR = ",<?php echo $_REQUEST['selected']; ?>,";
var PAGES_COUNT = 1;
var PLID = <?php echo $_PLID ? $_PLID : "0"; ?>;
var PLGET = "<?php echo $_PLGET; ?>";
var DECIMALS = <?php echo $_DECIMALS ? $_DECIMALS : "0"; ?>;

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

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(a && a['items'])
	 {
	  for(var c=0; c < a['items'].length; c++)
	  {
	   var item = a['items'][c];
	   var selected = (SELECTED_STR.indexOf(","+item['id']+",") >= 0) ? true : false;
	   var div = document.createElement('DIV');
	   div.className = "itemblock";
	   div.id = item['id'];
	   if(selected)
		div.style.backgroundImage = "url(img/item-block-smallthumb-selected.png)";

	   var html = "";

	   if(item['thumbnails'] && item['thumbnails'][0])
	    html+= "<div class='thumbnail' style='background-image: url("+ABSOLUTE_URL+item['thumbnails'][0]+");'>";
	   else
		html+= "<div class='thumbnail' style='background-image: url("+ABSOLUTE_URL+"share/widgets/gserv/img/photo.png);'>";
	   
	   html+= "<input type='checkbox' class='checkbox' onchange='itemSelect(this)'"+(selected ? " checked='true'/ >" : "/ >");
	   html+= "<span class='title' onclick=\"editItem('"+item['id']+"',this)\">"+item['name']+"</span></div>";

	   var baseprice = parseFloat(item["pricelist_"+PLID+"_baseprice"] ? item["pricelist_"+PLID+"_baseprice"] : item['baseprice']);
 	   var markuprate = parseFloat(item["pricelist_"+PLID+"_mrate"] ? item["pricelist_"+PLID+"_mrate"] : <?php echo $_PLINFO['markuprate']; ?>);
	   var vat = parseFloat(item["pricelist_"+PLID+"_vat"] ? item["pricelist_"+PLID+"_vat"] : <?php echo $_PLINFO['vat']; ?>);
 	   var finalPrice = baseprice ? baseprice + ((baseprice/100)*markuprate) : 0;
 	   var finalPriceVI = finalPrice ? finalPrice + ((finalPrice/100)*vat) : 0;

	   html+= "<div class='footer'> <span><i>Prezzo</i></span> <em>"+formatCurrency(finalPriceVI,DECIMALS)+" €</em> </div>";

	   div.innerHTML = html;
	   document.body.insertBefore(div,document.getElementById('loading'));
	  }
	 }

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
 sh.sendCommand("dynarc item-list -ap `"+AP+"`"+(CAT_ID ? " -cat `"+CAT_ID+"`" : "")+" -extget `gserv,thumbnails,coding,pricing`"+(PLGET ? " -get `"+PLGET+"`" : "")+" -limit "+(RESULTS_PER_PAGE*CURRENT_PAGE)+","+RESULTS_PER_PAGE);
}

function itemSelect(cb)
{
 var div = cb.parentNode.parentNode;
 if(cb.checked)
 {
  div.style.backgroundImage = "url(img/item-block-smallthumb-selected.png)";
  gframe_shotmessage("Item select", div.id, "SELECT");
 }
 else
 {
  div.style.backgroundImage = "url(img/item-block-smallthumb.png)";
  gframe_shotmessage("Item unselect", div.id, "UNSELECT");
 }
}

function editItem(id,span)
{
 gframe_shotmessage("Edit item", id, "EDIT_ITEM");
 span.className = "title visited";
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

