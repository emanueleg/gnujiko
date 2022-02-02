<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-03-2014
 #PACKAGE: commercialdocs
 #DESCRIPTION: Register custom prices.
 #VERSION: 2.1beta
 #CHANGELOG: 18-03-2014 - Bug fix icona chiusura widget.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$imgPath = $_ABSOLUTE_URL."share/widgets/commercialdocs/img/";

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Register Custom Prices</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gmutable/index.php");

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/templatedefault.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/register-custom-prices.css" type="text/css" />

</head><body>
<div class="default-widget" style="width:640px;height:480px">
 <h3 class="header">Registra prezzi imposti</h3> <img onclick="gframe_close();" src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/img/templatedefault/widgetclose.png" class="default-widget-close"/>

 <div class="default-widget-page" style="height:390px;overflow:auto;padding:10px">
 <!-- CONTENTS -->
 <span style='font-family:arial,sans-serif;font-size:14px;color:#3364C3;'><b>A questi prodotti &egrave; stato modificato manualmente il prezzo.<br/><?php 
	if($_REQUEST['vendorid'])
	 echo "Desideri aggiornare i prezzi d'acquisto da questo fornitore?";
	else
	 echo "Desideri impostare questi prezzi come predefiniti per questo cliente?";
	?></b></span>
 <div class="gmutable" style="margin-top:20px">
 <table id='doctable' class="gmutable" width="600" cellspacing="0" cellpadding="0" border="0" style="display:none;">
 <tr><th width='20'>&nbsp;</th>
	 <th id='code' width='80'>cod.</th>
	 <th id='name'>descrizione</th>
	 <th id='baseprice' width='60' editable='true' format='currency' decimals='2'>pr. unit.</th>
	 <th id='discount' width='60' editable='true' format="currency percentage" style='text-align:left'>Sconto</th>
	 <th id='discount2' width='60' editable='true' format='percentage' style='text-align:left'>Sconto2</th>
	 <th id='discount3' width='60' editable='true' format='percentage' style='text-align:left'>Sconto3</th>
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
	r.cell['name'].setValue(elements[c].getAttribute('desc'));
	r.cell['baseprice'].setValue(elements[c].getAttribute('baseprice'));
	r.cell['discount'].setValue(elements[c].getAttribute('discount'));
	r.cell['discount2'].setValue(elements[c].getAttribute('discount2'));
	r.cell['discount3'].setValue(elements[c].getAttribute('discount3'));
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
  var discount = ((r.cell['discount'].getValue().indexOf("%") > 0) ? r.cell['discount'].getValue() : parseCurrency(r.cell['discount'].getValue()));
  var discount2 = r.cell['discount2'].getValue() ? parseFloat(r.cell['discount2'].getValue()) : 0;
  var discount3 = r.cell['discount3'].getValue() ? parseFloat(r.cell['discount3'].getValue()) : 0;
  if(!discount) discount=0;

  if(PARAMS['vendorid'])
  {
   var qry = "dynarc edit-item -ap '"+r.getAttribute('refap')+"' -id '"+r.getAttribute('refid')+"' -extset `vendorprices.vendorid='"+PARAMS['vendorid']+"',vendorname='''"+PARAMS['vendorname']+"''',price='"+parseCurrency(r.cell['baseprice'].getValue())+"'`";
  }
  else
  {
   var qry = "dynarc edit-item -ap '"+r.getAttribute('refap')+"' -id '"+r.getAttribute('refid')+"' -extset `custompricing.subjectid='"+PARAMS['subjectid']+"',subjectname='''"+PARAMS['subjectname']+"'''";
   qry+= ",baseprice='"+parseCurrency(r.cell['baseprice'].getValue())+"'";
   qry+= ",discount='"+discount+"',discount2='"+discount2+"',discount3='"+discount3+"'`";
  }

  sh.sendCommand(qry);
 }
}
</script>
</body></html>
<?php

