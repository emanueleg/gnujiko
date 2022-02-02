<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 16-01-2016
 #PACKAGE: commercialdocs
 #DESCRIPTION: Register vendor prices.
 #VERSION: 2.2beta
 #CHANGELOG: 16-01-2016 : Aggiunto campi vencode e vat.
			 11-01-2016 : Bug fix su alert in submit.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$imgPath = $_ABSOLUTE_URL."share/widgets/commercialdocs/img/";

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Register Vendor Prices</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gmutable/index.php");

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/templatedefault.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/register-custom-prices.css" type="text/css" />

</head><body>
<div class="default-widget" style="width:640px;height:480px">
 <h3 class="header">Modifica prezzi fornitore</h3> <img onclick="gframe_close();" src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/img/templatedefault/widgetclose.png" class="default-widget-close"/>

 <div class="default-widget-page" style="height:390px;overflow:auto;padding:10px">
 <!-- CONTENTS -->
 <span style='font-family:arial,sans-serif;font-size:14px;color:#3364C3;'><b>A questi prodotti &egrave; stato modificato il prezzo d&lsquo;acquisto.<br/>Desideri aggiornare i prezzi d'acquisto da questo fornitore?</b></span>
 <div class="gmutable" style="margin-top:20px">
 <table id='doctable' class="gmutable" width="600" cellspacing="0" cellpadding="0" border="0" style="display:none;">
 <tr><th width='20'>&nbsp;</th>
	 <th id='code' width='80'>cod.</th>
	 <th id='vencode' width='80'>cod. forn.</th>
	 <th id='name'>descrizione</th>
	 <th id='baseprice' width='60' editable='true' format='currency' decimals='2'>pr. acq.</th>
	 <th id='vat' width='60' editable='true' format='percentage'>iva</th>
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
	r.setAttribute('refap',elements[c].getAttribute('ap'));
	r.setAttribute('refid',elements[c].getAttribute('id'));
	r.cell['code'].setValue(elements[c].getAttribute('code'));
	r.cell['vencode'].setValue(elements[c].getAttribute('vencode'));
	r.cell['name'].setValue(elements[c].getAttribute('desc'));
	r.cell['baseprice'].setValue(elements[c].getAttribute('baseprice'));
	r.cell['vat'].setValue(elements[c].getAttribute('vat'));
   }

   document.body.removeChild(div);
  }
 }
}

function submit()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnFinish = function(o,a){gframe_close(o,a);}

 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];

   var qry = "dynarc edit-item -ap '"+r.getAttribute('refap')+"' -id '"+r.getAttribute('refid')+"' -extset `vendorprices.vendorid='"+PARAMS['vendorid']+"',vendorname='''"+PARAMS['vendorname']+"''',code='"+r.cell['vencode'].getValue()+"',price='"+parseCurrency(r.cell['baseprice'].getValue())+"',vat='"+parseFloat(r.cell['vat'].getValue())+"'`";
  sh.sendCommand(qry);
 }
}
</script>
</body></html>
<?php

