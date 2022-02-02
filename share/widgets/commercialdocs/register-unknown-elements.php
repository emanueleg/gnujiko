<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 12-07-2013
 #PACKAGE: commercialdocs
 #DESCRIPTION: Register unknown products,services,and other.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$imgPath = $_ABSOLUTE_URL."share/widgets/commercialdocs/img/";

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Register Unknown Elements</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gmutable/index.php");
?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/templatedefault.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/register-unknown-elements.css" type="text/css" />

</head><body>
<div class="default-widget" style="width:640px;height:480px">
 <h3 class="header">Registra prodotti sconosciuti</h3> <img onclick="gframe_close();" src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/cmslite/img/templatedefault/widgetclose.png" class="default-widget-close"/>

 <div class="default-widget-page" style="height:390px;overflow:auto;padding:10px">
 <!-- CONTENTS -->
 <span style='font-family:arial,sans-serif;font-size:14px;color:#3364C3;'><b>Questo documento contiene dei prodotti che non sono registrati in archivio.<br/>Desideri inserire questi articoli nell&lsquo;archivio dei prodotti?</b></span>
 <div class="gmutable" style="margin-top:20px">
 <table id='doctable' class="gmutable" width="600" cellspacing="0" cellpadding="0" border="0" style="display:none;">
 <tr><th width='20'>&nbsp;</th>
	 <th id='code' width='80' editable='true'>cod.</th>
	 <th id='description' editable='true'>descrizione</th>
	 <th id='qty' width='40' editable='true' style='text-align:center'>qt&agrave;</th>
	 <th id='units' width='40' editable='true' style='text-align:center'>u.m.</th>
	 <th id='unitprice' width='60' editable='true' format='currency' decimals='2'>pr. unit.</th>
	 <th id='vat' width='40' editable='true' format='percentage' style='text-align:left'>% IVA</th>
 </tr>
 </table>
 </div>
 <!-- EOF CONTENTS -->
 </div>


 <div class="default-widget-footer">
  <span class="left-button blue" onclick="submit()">Conferma</span> 
  <span class="left-button gray" onclick="gframe_close()">Annulla</span> 
 </div>

</div>

<script>
var tb = null;
var PARAMS = null;

function bodyOnLoad(extraParams)
{
 PARAMS = extraParams;

 var div = document.getElementById('doctable').parentNode;
 div.style.height = div.parentNode.offsetHeight-80;
 div.style.width = div.offsetWidth;

 document.getElementById('doctable').style.display="";

 tb = new GMUTable(document.getElementById('doctable'));

 if(extraParams)
 {
  if(extraParams['xmlelements'])
  {
   var div = document.createElement('DIV');
   div.style.display = "none";
   div.innerHTML = extraParams['xmlelements'];
   document.body.appendChild(div);
   var elements = div.getElementsByTagName('ITEM');

   for(var c=0; c < elements.length; c++)
   {
    var r = tb.AddRow();
	r.cell['code'].setValue(elements[c].getAttribute('code'));
	r.cell['description'].setValue(elements[c].getAttribute('name'));
	r.cell['qty'].setValue(elements[c].getAttribute('qty'));
	r.cell['units'].setValue(elements[c].getAttribute('units'));
	r.cell['unitprice'].setValue(elements[c].getAttribute('unitprice'));
	r.cell['vat'].setValue(elements[c].getAttribute('vat'));
   }

   document.body.removeChild(div);
  }
 }
}

function submit()
{
 var sh = new GShell();
 var ret = new Array();

 sh.OnError = function(msg,errcode){
	 alert(msg);
	}

 sh.OnOutput = function(o,a){
	 if(a)
	  ret.push(a['id']);
	}

 sh.OnFinish = function(o,a){
	 if(a)
	  ret.push(a['id']);

	 gframe_close(o,ret);
	}

 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];
  var qry = "dynarc new-item -ap `gmart` -code-str `"+r.cell['code'].getValue()+"` -name `"+r.cell['description'].getValue()+"` -extset `gmart.model='''"+r.cell['description'].getValue()+"''',units='"+r.cell['units'].getValue()+"',pricing.baseprice='"+parseCurrency(r.cell['unitprice'].getValue())+"',vat='"+parseFloat(r.cell['vat'].getValue())+"'";
  if(PARAMS['vendorid'])
   qry+= ",vendorprices.vendor='''"+PARAMS['vendorname']+"''',vendorid='"+PARAMS['vendorid']+"',price='"+parseCurrency(r.cell['unitprice'].getValue())+"',vat='"+parseFloat(r.cell['vat'].getValue())+"'";
  qry+= "`"
  sh.sendCommand(qry);
 }

}
</script>
</body></html>
<?php

