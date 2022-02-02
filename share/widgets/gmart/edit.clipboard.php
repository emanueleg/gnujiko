<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 10-10-2013
 #PACKAGE: gmart
 #DESCRIPTION: Edit clipboard form.
 #VERSION: 2.1beta
 #CHANGELOG: 10-10-2013 : Bug fix sui prezzi. Ora li calcola giusti.
 #DEPENDS: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES,$_DECIMALS, $_PRICELISTS, $_FREQ_VAT_TYPE, $_FREQ_VAT_PERC;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/company-profile.php");

$id = $_REQUEST['id'];

$_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'];

if($_COMPANY_PROFILE['accounting']['freq_vat_used'])
{
 $ret = GShell("dynarc item-info -ap vatrates -id `".$_COMPANY_PROFILE['accounting']['freq_vat_used']."` -get `vat_type,percentage`",$_REQUEST['sessid'],$_REQUEST['shellid']);
 $_FREQ_VAT_TYPE = $ret['outarr']['vat_type'];
 $_FREQ_VAT_PERC = $ret['outarr']['percentage'];
}

$ret = GShell("pricelists list",$_REQUEST['sessid'],$_REQUEST['shellid']);
$_PRICELISTS = $ret['outarr'];
$_PLID = 0;
$_PLGET = "";
$_PLINFO = array();
if(count($_PRICELISTS))
{
 $_PLINFO = $_PRICELISTS[0];
 $_PLID = $_PRICELISTS[0]['id'];
 $_PLGET = "pricelist_".$_PLID."_baseprice,pricelist_".$_PLID."_mrate,pricelist_".$_PLID."_vat";
}


$ret = GShell("dynarc clipboard-info -id `".$id."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
if(!$ret['error'])
 $cbInfo = $ret['outarr'];

if($_REQUEST['action'])
{
 switch($_REQUEST['action'])
 {
  case 'appendtodoc' : include($_BASE_PATH."share/widgets/gmart/clipboard-appendtodoc.php"); break;
 }
 exit();
}

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Edit clipboard</title>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>"; var USER_HOME = "<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR']; ?>/";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/edit-clipboard.css" type="text/css" />
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gmutable/index.php");
?>
</head><body>

<table width="800" height="520" cellspacing="0" cellpadding="0" border="0" class="edit-clipboard-form">
<tr><td class="header-left" width='280'><span style="margin-left:20px;">Modifica propriet&agrave; appunto:</span></td>
	<td class="header-top"><div class="title" id="title-outer"><span id="title" onclick="rename()"><?php echo html_entity_decode(stripslashes($cbInfo['name']),ENT_QUOTES,'UTF-8'); ?></span></div></td>
	<td class="header-right"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/widget-close.png" onclick="gframe_close()" class="close-btn"/></td></tr>

<tr><td colspan="3" valign="top" class="contents"><div class="contents">
	<!-- CONTENTS -->
     <div class="gmutable" style="height:300px;margin-right:12px;margin-left:2px;">
	 <table id='doctable' class="gmutable" width="100%" cellspacing="0" cellpadding="0" border="0">
	 <tr><th width='40'><input type="checkbox" onchange="tb.selectAll(this.checked)"/></th>
		 <th width='60' id='code'>CODICE</th>
		 <th id='description' minwidth='250'>ARTICOLO / DESCRIZIONE</th>
		 <th width='40' id='qty' style='text-align:center;' editable='true'>QTA'</th>
		 <th width='60' id='unitprice' format='currency' decimals="<?php echo $_DECIMALS; ?>">PR. UNIT.</th>
		 <th width='40' id='vat' style='text-align:center;' format='percentage'>I.V.A.</th>
		 <th width='120' id='price' style='text-align:center;' minwidth='100'>TOTALE</th>
		 <th width='120' id='vatprice' style='text-align:center;' minwidth='100'>TOT. + IVA</th>
 	</tr>
	<?php
	$subtot = 0;
	$subtotVI = 0;
	for($c=0; $c < count($cbInfo['elements']); $c++)
	{
	 $el = $cbInfo['elements'][$c];
  	 $ret = GShell("commercialdocs getfullinfo -ap `".$el['ap']."` -id `".$el['id']."`");
	 if($ret['error'])
	  continue;
	 $itm = $ret['outarr'];

	 $type = "";
	 switch($el['ap'])
	 {
	  case 'gmart' : $type='article'; break;
	  case 'gserv' : $type='service'; break;
	 }


	 $qty = $el['qty'] ? $el['qty'] : 1;
	 $finalPrice = $itm['finalprice'];
	 $total = $finalPrice*$qty;
	 $totalVI = $itm['finalpricevatincluded']*$qty;

	 $subtot+= $total;
	 $subtotVI+= $totalVI;

	 echo "<tr type='".$type."' refap='".$el['ap']."' refid='".$el['id']."'><td><input type='checkbox'/></td>";
	 echo "<td><span class='graybold'>".$itm['code_str']."</span></td>";
	 echo "<td><span class='graybold doubleline'>".$itm['name']."</span></td>";
	 echo "<td><span class='graybold 13 center'>".$el['qty']."</span></td>";
	 echo "<td><span class='graybold'>".number_format($finalPrice,$_DECIMALS,",",".")."</span></td>";
	 echo "<td><span class='graybold center'>".$itm['vat']."%</span></td>";
	 echo "<td><span class='eurogreen'><em>&euro;</em>".number_format($total,$_DECIMALS,",",".")."</span></td>";
	 echo "<td><span class='eurogreen'><em>&euro;</em>".number_format($totalVI,$_DECIMALS,",",".")."</span></td>";
	 echo "</tr>";
	}
	?>

	</table>
	</div>

 <table class="docfooter-results" width="780px" cellspacing="0" cellpadding="0" border="0" style="margin-top:2px;">
  <tr><th class="blue">&nbsp;</th>
	  <th class="blue" width="140">IMPONIBILE</th>
	  <th class="blue" width="140">I.V.A.</th>
	  <th class="green" width="140">TOTALE</th>
  </tr>
  <tr><td class="blue">&nbsp;</th>
	  <td class="blue" id="doctot-amount"><em>&euro;</em><?php echo number_format($subtot,$_DECIMALS,",","."); ?></th>
	  <td class="blue" id="doctot-vat"><em>&euro;</em><?php echo number_format($subtotVI-$subtot,$_DECIMALS,",","."); ?></th>
	  <td class="green" id="doctot-total"><em>&euro;</em><?php echo number_format($subtotVI,$_DECIMALS,",","."); ?></th>
  </tr>
 </table>

	<div class="contents-footer">
	<?php
	$ret = GShell("dynarc archive-info -prefix `commercialdocs`",$_REQUEST['sessid'],$_REQUEST['shellid']);
	if(!$ret['error'])
	{
	?>
	 <div class='bluebtn' style='width:320px;margin-top:15px;float:right;'>
	  <div class='bluebtn-inner'><a href="#" onclick='appendToDocument()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/next.gif" border='0' style="float:right;vertical-align:middle;margin-top:3px;"/>Includi gli articoli di questo appunto<br/>su un ordine, un preventivo o una fattura</a></div>
	 </div>
	<?php
	}
	?>
	</div>
	<!-- EOF - CONTENTS -->
	</div>
	</td></tr>

<tr><td class="footer-left" valign="top">
	 <ul class='basicbuttons' style="margin-left:15px;margin-top:4px;float:left;">
	  <li><span onclick='submit()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/save.gif" border='0'/>Salva</span></li>
	  <li><span onclick='deleteClipboard()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/delete.png" border='0'/>Elimina</span></li>
	 </ul>
	&nbsp;
	</td>
	<td class="footer-right" colspan="2" valign="top">
	 <ul class='basicbuttons' style="float:right;margin-top:4px;margin-right:5px;">
	  <li><span onclick="gframe_close()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/exit.png" border='0'/>Chiudi</span></li>
	 </ul>
	&nbsp;
	</td></tr>

</table>

<script>
var tb = null;
var DECIMALS = <?php echo $_DECIMALS ? $_DECIMALS : "2"; ?>;

function bodyOnLoad()
{
 tb = new GMUTable(document.getElementById('doctable'), {autoresize:false, autoaddrow:false});

 tb.OnCellEdit = function(r,cell,value,data){
	 switch(cell.tag)
	 {
	  case 'qty' : updateTotals(r); break;
	 }
	}

}

function rename()
{
 var titO = document.getElementById('title');
 var nm = prompt("Rinomina appunto",titO.innerHTML);
 if(!nm)
  return;

 titO.innerHTML = nm;
}

function submit(callback)
{
 var title = document.getElementById('title').innerHTML;
 
 var sh = new GShell();
 sh.OnFinish = function(){
	 if(callback)
	  callback();
	 else
	  gframe_close("Done!",<?php echo $cbInfo['id']; ?>);
	}

 sh.sendCommand("export `DYNCB-<?php echo ($cbInfo['id']-1); ?>-NAME="+title+"`");
 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];
  var qty = parseFloat(r.cell['qty'].getValue());
  sh.sendCommand("export DYNCB-<?php echo ($cbInfo['id']-1); ?>-ITM-"+(c-1)+"-QTY="+qty);
 }
}

function updateTotals(r)
{
 if(r)
 {
  var qty = parseFloat(r.cell['qty'].getValue());
  var unitprice = parseCurrency(r.cell['unitprice'].getValue());
  var vat = parseFloat(r.cell['vat'].getValue());

  var total = unitprice * qty;
  var totalPlusVat = total ? total + ((total/100)*vat) : 0;

  r.cell['price'].setValue("<em>&euro;</em>"+formatCurrency(total,DECIMALS));
  r.cell['vatprice'].setValue("<em>&euro;</em>"+formatCurrency(totalPlusVat,DECIMALS));
 }

 var totAmount = 0;
 var totVAT = 0;
 var totTotal = 0;

 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];
  var qty = parseFloat(r.cell['qty'].getValue());
  var unitprice = parseCurrency(r.cell['unitprice'].getValue());

  var amount = parseCurrency(r.cell['price'].getValue().substr(10));
  var vat = amount ? (amount/100)*parseFloat(r.cell['vat'].getValue()) : 0;
  
  if(amount)
   totAmount+= amount;
  if(vat)
   totVAT+= vat;
 }

 totTotal = totAmount+totVAT;
 
 document.getElementById("doctot-amount").innerHTML = "<em>&euro;</em>"+formatCurrency(totAmount,DECIMALS);
 document.getElementById("doctot-vat").innerHTML = "<em>&euro;</em>"+formatCurrency(totVAT,DECIMALS);
 document.getElementById("doctot-total").innerHTML = "<em>&euro;</em>"+formatCurrency(totTotal,DECIMALS);
}

function appendToDocument()
{
 submit(function(){document.location.href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/edit.clipboard.php?action=appendtodoc&id=<?php echo $_REQUEST['id']; ?>&sessid=<?php echo $_REQUEST['sessid']; ?>&shellid=<?php echo $_REQUEST['shellid']; ?>";});
}

function deleteClipboard()
{
 if(!confirm("Sei sicuro di voler rimuovere questo appunto?"))
  return;
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 gframe_shotmessage(o,a,"REMOVED");
	 gframe_close();
	}
 sh.sendCommand("dynarc clipboard-delete -id `<?php echo $cbInfo['id']; ?>`");
}

</script>
</body></html>
<?php


