<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 08-10-2014
 #PACKAGE: gmart
 #DESCRIPTION: Clipboard Summary for GMart
 #VERSION: 2.3beta
 #CHANGELOG: 08-10-2014 : Bug fix su generazione ordini fornitore.
			 10-10-2013 : Bug fix sui prezzi. Ora li calcola giusti.
			 13-01-2013 : Bug fix in assign group at new documents.
 #TODO: La clipboard inserisce solo nell'archivio gmart. Modificare in modo che si possa usare anche con gli altri archivi.
 
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

if($_REQUEST['custid'])
{
 $ret = GShell("dynarc item-info -ap `rubrica` -id `".$_REQUEST['custid']."` -extget rubricainfo",$_REQUEST['sessid'],$_REQUEST['shellid']);
 $customerInfo = $ret['outarr'];
}
else
 $customerName = $_REQUEST['custname'];

$ret = GShell("pricelists list",$_REQUEST['sessid'],$_REQUEST['shellid']);
$_PRICELISTS = $ret['outarr'];
$_PLID = 0;
$_PLGET = "";
$_PLINFO = array();
if($customerInfo['pricelist_id'])
{
 for($c=0; $c < count($_PRICELISTS); $c++)
 {
  if($_PRICELISTS[$c]['id'] == $customerInfo['pricelist_id'])
  {
   $_PLINFO = $_PRICELISTS[$c];
   $_PLID = $_PRICELISTS[$c]['id'];
   $_PLGET = "pricelist_".$_PLID."_baseprice,pricelist_".$_PLID."_mrate,pricelist_".$_PLID."_vat";
   break;   
  }
 }
}
else if(count($_PRICELISTS))
{
 $_PLINFO = $_PRICELISTS[0];
 $_PLID = $_PRICELISTS[0]['id'];
 $_PLGET = "pricelist_".$_PLID."_baseprice,pricelist_".$_PLID."_mrate,pricelist_".$_PLID."_vat";
}

$ret = GShell("dynarc clipboard-info -id `".$id."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
if(!$ret['error'])
 $cbInfo = $ret['outarr'];

$_IS_VENDOR = false;

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Summary</title>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>"; var USER_HOME = "<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR']; ?>/";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/edit-clipboard.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/clipboard-appendtodoc-summary.css" type="text/css" />
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gmutable/index.php");
include($_BASE_PATH."var/objects/htmlgutility/menu.php");
include_once($_BASE_PATH."var/objects/dynrubricaedit/index.php");
?>
</head><body>

<table width="800" height="520" cellspacing="0" cellpadding="0" border="0" class="edit-clipboard-form">
<tr><td class="header-left" width='280'><span style="margin-left:20px;">Riepilogo dell&lsquo;appunto:</span></td>
	<td class="header-top"><div class="title" id="title-outer"><span id="title" onclick="rename()"><?php echo html_entity_decode(stripslashes($cbInfo['name']),ENT_QUOTES,'UTF-8'); ?></span></div></td>
	<td class="header-right"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/widget-close.png" onclick="gframe_close()" class="close-btn"/></td></tr>

<tr><td colspan="3" valign="top" class="contents"><div class="contents">
	<!-- CONTENTS -->
	 <div class="doc-info">
	  <span class='bluebtn' style='width:220px;'><span class='bluebtn-title' id="document-name"><?php
			switch(strtolower($_REQUEST['doctype']))
			{
			 case 'preemptives' : echo "Preventivo"; break;
			 case 'orders' : echo "Ordine"; break;
			 case 'ddt' : echo "D.D.T."; break;
			 case 'vendororders' : echo "Ordine fornitore"; break;
			 case 'invoices' : echo "Fattura"; break;
			 default : echo "&nbsp;"; break;
			}
			?></span>
		 <ul class="submenu" id="document-types">
	 	  <li onclick="selectDocType('preemptives')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/printmodels/preemptives.png"/>Preventivo</li>
	 	  <li onclick="selectDocType('orders')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/printmodels/orders.png"/>Ordine</li>
	 	  <li onclick="selectDocType('ddt')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/printmodels/ddt.png"/>D.D.T.</li>
	 	  <li onclick="selectDocType('vendororders')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/printmodels/vendororders.png"/>Ordine fornitore</li>
	 	  <li onclick="selectDocType('invoices')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/printmodels/invoices.png"/>Fattura</li>
		 </ul></span>

	  <div class='customer-outer'>Cliente: <input type='text' id='customer' value="<?php echo $customerInfo ? html_entity_decode($customerInfo['name'],ENT_QUOTES,'UTF-8') : html_entity_decode($customerName,ENT_QUOTES,'UTF-8'); ?>" onchange="subjectChanged()"/></div>

	  <span class='bluebtn' style='width:240px;'><span class='bluebtn-title' id="docref-name" style="font-size:11px;"><?php
			 $newTit = "";
			 switch(strtolower($_REQUEST['doctype']))
			 {
			  case 'preemptives' : $newTit = "Genera nuovo preventivo"; break;
			  case 'orders' : $newTit = "Genera nuovo ordine"; break;
			  case 'ddt' : $newTit = "Genera nuovo D.D.T."; break;
			  case 'vendororders' : {
				 $newTit = "Genera nuovo ordine fornitore"; 
				 $_IS_VENDOR = true;
				}break;
			  case 'invoices' : $newTit = "Genera nuova fattura"; break;
			  default : $newTit = "Genera nuovo documento"; break;
			 }

			if(!$_REQUEST['docid'])
			 echo $newTit;
			else
			{
			 $ret = GShell("dynarc item-info -ap `commercialdocs` -id `".$_REQUEST['docid']."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
			 $docInfo = $ret['outarr'];
			 echo $docInfo['name'];
			}
			?></span>

		  <ul class="submenu" id="docref-list">
		   <?php
		   if($_REQUEST['docid'] && $docInfo)
		   {
		    echo "<li onclick='selectDocRef(".$docInfo['id'].",this)'>".$docInfo['name']."</li>";
		    echo "<li class='separator'>&nbsp;</li>";
		   }
		   ?>
	 	   <li onclick="selectDocRef(0,this)"><?php echo $newTit; ?></li>
		  </ul>
		 </span>

	 </div>



     <div class="gmutable" style="height:315px;margin-right:12px;margin-left:2px;clear:both;">
	 <table id='doctable' class="gmutable" width="100%" cellspacing="0" cellpadding="0" border="0">
	 <tr><th width='40'><input type="checkbox" onchange="tb.selectAll(this.checked)"/></th>
		 <th width='60' id='code'>CODICE</th>
		 <th id='description' minwidth='250'>ARTICOLO / DESCRIZIONE</th>
		 <th width='40' id='qty' style='text-align:center;' editable='true'>QTA'</th>
		 <th width='60' id='vendorprice' format='currency' decimals="<?php echo $_DECIMALS; ?>">PR. ACQ.</th>
		 <th width='60' <?php if($_IS_VENDOR) echo "style='display:none'"; ?> id='unitprice' format='currency' decimals="<?php echo $_DECIMALS; ?>">PR. UNIT.</th>
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
  	 $ret = GShell("commercialdocs getfullinfo -ap `".$el['ap']."` -id `".$el['id']."` "
		.($_IS_VENDOR ? "-vendorid `".$customerInfo['id']."`" : "-subjectid `".$customerInfo['id']."`"),$_REQUEST['sessid'],$_REQUEST['shellid']);
	 if($ret['error'])
	  continue;
	 $itm = $ret['outarr'];

	 $qty = $el['qty'] ? $el['qty'] : 1;

	 $finalPrice = $_IS_VENDOR ? $itm['vendor_price'] : $itm['finalprice'];
	 $total = $finalPrice*$qty;
	 if($_IS_VENDOR)
	  $totalVI = $itm['vendor_price'] ? $itm['vendor_price'] + (($itm['vendor_price']/100)*$itm['vendor_vatrate']) : 0;
	 else
	  $totalVI = $itm['finalpricevatincluded']*$qty;

	 $subtot+= $total;
	 $subtotVI+= $totalVI;

	 echo "<tr refap='".$el['ap']."' refid='".$itm['id']."' vatid='".$itm['vatid']."' vattype='".$itm['vattype']."'><td><input type='checkbox'/></td>";
	 echo "<td><span class='graybold'>".$itm['code_str']."</span></td>";
	 echo "<td><span class='graybold doubleline'>".$itm['name']."</span></td>";
	 echo "<td><span class='graybold 13 center'>".$el['qty']."</span></td>";
	 echo "<td><span class='graybold'>".number_format($itm['vendor_price'],$_DECIMALS,",",".")."</span></td>";
	 echo "<td ".($_IS_VENDOR ? 'style=\"display:none\"' : '')."><span class='graybold'>".number_format($finalPrice,$_DECIMALS,",",".")."</span></td>";
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

	<!-- EOF - CONTENTS -->
	</div>
	</td></tr>

<tr><td class="footer-left" valign="top">
	 <ul class='basicbuttons' style="margin-left:15px;margin-top:4px;float:left;">
	  <li><span onclick='comeBack()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/back.png" border='0'/>Torna indietro</span></li>
	 </ul>
	&nbsp;
	</td>
	<td class="footer-right" colspan="2" valign="top">
	 <ul class='basicbuttons' style="float:right;margin-top:4px;margin-right:5px;">
	  <li><span onclick='submit()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/confirm.png" border='0'/>Conferma</span></li>
	 </ul>
	&nbsp;
	</td></tr>

</table>

<script>
var tb = null;
var DECIMALS = <?php echo $_DECIMALS ? $_DECIMALS : "2"; ?>;
var RubricaEdit = null;
var DOC_TYPE = "<?php echo $_REQUEST['doctype'] ? $_REQUEST['doctype'] : 'preemptives'; ?>";
var DOC_ID = <?php echo $_REQUEST['docid'] ? $_REQUEST['docid'] : '0'; ?>;
var PLID = <?php echo $_PLID ? $_PLID : "0"; ?>;
var INCLUDE_TITLE = <?php echo $_REQUEST['includecbtitle'] ? "true" : "false"; ?>;
var IS_VENDOR = <?php echo $_IS_VENDOR ? 'true' : 'false'; ?>;

function bodyOnLoad()
{
 tb = new GMUTable(document.getElementById('doctable'), {autoresize:false, autoaddrow:false});
 tb.OnCellEdit = function(r,cell,value){
	 switch(cell.tag)
	 {
	  case 'qty' : updateTotals(r); break;
	 }
	}

 new GPopupMenu(document.getElementById('document-name'), document.getElementById('document-types'));
 new GPopupMenu(document.getElementById('docref-name'), document.getElementById('docref-list'));
 RubricaEdit = new DynRubricaEdit(document.getElementById('customer'));
}

function rename()
{
 var titO = document.getElementById('title');
 var nm = prompt("Rinomina appunto",titO.innerHTML);
 if(!nm)
  return;

 titO.innerHTML = nm;
}

function submit()
{
 var title = document.getElementById('title').innerHTML;
 var CUST_ID = 0;
 var CUST_NAME = RubricaEdit.value;

 if(RubricaEdit.data)
  CUST_ID = RubricaEdit.data['id'];

 if(!DOC_ID)
 {
  var sh = new GShell();
  sh.OnOutput = function(o,a){
	 DOC_ID = a['id'];
	 return submit();
	}
  sh.sendCommand("dynarc new-item -ap `commercialdocs` -ct `"+DOC_TYPE+"` -group commdocs-"+DOC_TYPE.toLowerCase()+" -extset `cdinfo."+(CUST_ID ? "subjectid="+CUST_ID : "subject='''"+CUST_NAME+"'''")+",pricelist='"+PLID+"'`");
  return;
 }
 else
 {
  var sh = new GShell();
  sh.OnFinish = function(){
	 gframe_shotmessage("Done!",DOC_ID,"APPEND-TO-DOCUMENT");
	 gframe_close();
	}

  if(INCLUDE_TITLE)
   sh.sendCommand("dynarc edit-item -ap `commercialdocs` -id `"+DOC_ID+"` -extset `cdelements.type=note,desc='''"+title+"'''`");

  for(var c=1; c < tb.O.rows.length; c++)
  {
   var r = tb.O.rows[c];
   var code = r.cell['code'].getValue();
   var desc = r.cell['description'].getValue();
   var qty = parseFloat(r.cell['qty'].getValue());
   var unitprice = parseCurrency(r.cell['unitprice'].getValue());
   var vendorprice = parseCurrency(r.cell['vendorprice'].getValue());
   var vat = parseFloat(r.cell['vat'].getValue());

   var refap = r.getAttribute('refap');
   var refid = r.getAttribute('refid');
   var type = "";
   switch(refap)
   {
    case 'gmart' : type='article'; break;
    case 'gserv' : type='service'; break;
   }

   sh.sendCommand("dynarc edit-item -ap `commercialdocs` -id `"+DOC_ID+"` -extset `cdelements.type='"+type+"',refap='"+refap+"',refid='"+refid+"',code='"+code+"',name='''"+desc+"''',qty='"+qty+"',saleprice='"+unitprice+"',vendorprice='"+vendorprice+"',price='"+(IS_VENDOR ? vendorprice : unitprice)+"',vat='"+vat+"',vatid='"+r.getAttribute('vatid')+"',vattype='"+r.getAttribute('vattype')+"',pricelist='"+PLID+"'`");
  }
  sh.sendCommand("commercialdocs updatetotals -id '"+DOC_ID+"'");
 }
}

function comeBack()
{
 history.back();
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

function selectDocType(docTag)
{
 DOC_TYPE = docTag.toUpperCase();
 switch(docTag)
 {
  case 'preemptives' : document.getElementById('document-name').innerHTML = "Preventivo"; break;
  case 'orders' : document.getElementById('document-name').innerHTML = "Ordine"; break;
  case 'ddt' : document.getElementById('document-name').innerHTML = "D.D.T."; break;
  case 'vendororders' : document.getElementById('document-name').innerHTML = "Ordine fornitore"; break;
  case 'invoices' : document.getElementById('document-name').innerHTML = "Fattura"; break;
 }
 checkForOpenDocuments();
}

function selectDocRef(id, li)
{
 document.getElementById('docref-name').innerHTML = li.innerHTML;
 DOC_ID = id;
}

function subjectChanged()
{
 checkForOpenDocuments();
}

function checkForOpenDocuments()
{
 if(!RubricaEdit)
  return;

 var newTit = "";
 switch(DOC_TYPE.toLowerCase())
 {
  case 'preemptives' : newTit="Genera nuovo preventivo"; break;
  case 'orders' : newTit="Genera nuovo ordine"; break;
  case 'ddt' : newTit="Genera nuovo D.D.T."; break;
  case 'invoices' : newTit="Genera nuova fattura"; break;
  case 'vendororders' : newTit="Genera nuovo ordine fornitore"; break;
  case 'purchaseinvoices' : newTit="Genera nuova fattura d&lsquo;acquisto"; break;
  default : newTit="Genera nuovo documento"; break;
 }

 if(!RubricaEdit.value)
 {
  document.getElementById('docref-name').innerHTML = newTit;
  var ul = document.getElementById('docref-list');
  var list = ul.getElementsByTagName('LI');
  if(list.length > 1)
  {
   while(list.length > 1)
	ul.removeChild(ul.getElementsByTagName('LI')[0]);
  }
  ul.getElementsByTagName('LI')[0].innerHTML = newTit;

  DOC_ID = 0;
  return;
 }

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a['items'] || !a['items'].length)
	 {
	  document.getElementById('docref-name').innerHTML = newTit;
	  var ul = document.getElementById('docref-list');
	  var list = ul.getElementsByTagName('LI');
	  if(list.length > 1)
	  {
	   while(list.length > 1)
		ul.removeChild(ul.getElementsByTagName('LI')[0]);
	  }
	  ul.getElementsByTagName('LI')[0].innerHTML = newTit;
	 }
	 else
	 {
	  var ul = document.getElementById('docref-list');
	  var list = ul.getElementsByTagName('LI');
	  if(list.length > 1)
	  {
	   while(list.length > 1)
		ul.removeChild(ul.getElementsByTagName('LI')[0]);
	  }
	  ul.getElementsByTagName('LI')[0].innerHTML = newTit;

	  var li = document.createElement('LI');
	  li.innerHTML = a['items'][0]['name'];
	  li.onclick = function(){selectDocRef(this.docId,this);}
	  ul.insertBefore(li,ul.getElementsByTagName('LI')[0]);

	  var sep = document.createElement('LI');
	  sep.className = "separator";
	  sep.innerHTML = "&nbsp;";
	  ul.insertBefore(li,ul.getElementsByTagName('LI')[1]);

	  document.getElementById('docref-name').innerHTML = a['items'][0]['name'];

	  DOC_ID = a['items'][0]['id'];
	 }
	}
 sh.sendCommand("dynarc item-list -ap `commercialdocs` -ct `"+DOC_TYPE+"` -where `"+(RubricaEdit.data ? "subject_id="+RubricaEdit.data['id'] : "subject_name='"+RubricaEdit.value+"'")+" AND status=0` --order-by `ctime DESC` -limit 1");
}
</script>
</body></html>
<?php

