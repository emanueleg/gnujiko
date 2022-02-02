<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-11-2014
 #PACKAGE: commercialdocs
 #DESCRIPTION: Register unknown products,services,and other.
 #VERSION: 2.8beta
 #CHANGELOG: 21-11-2014 : Bug fix sui gruppi.
			 10-11-2014 : Aggiunta colonna peso.
			 29-10-2014 : Aggiunta colonna cod. art. produttore.
			 16-09-2014 : Aggiunto vendor-price.
			 02-08-2014 : Integrazione con prodotti finiti, componenti e materiali.
			 14-07-2014 : Ora è possibile assegnare la categoria ai nuovi articoli.
			 08-04-2014 : Bug fix listini prezzi.
			 18-03-2014 : Bug fix icona chiusura widget.
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
<div class="default-widget" style="width:800px;height:480px">
 <h3 class="header">Registra prodotti sconosciuti</h3> <img onclick="gframe_close();" src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/img/templatedefault/widgetclose.png" class="default-widget-close"/>

 <div class="default-widget-page" style="height:390px;overflow:auto;padding:10px">
 <!-- CONTENTS -->
 <span style='font-family:arial,sans-serif;font-size:14px;color:#3364C3;'><b>Questo documento contiene dei prodotti che non sono registrati in archivio.<br/>Desideri inserire questi articoli nell&lsquo;archivio dei prodotti?</b></span>
 <div class="gmutable" style="margin-top:20px">
 <table id='doctable' class="gmutable" width="600" cellspacing="0" cellpadding="0" border="0" style="display:none;">
 <tr><th width='20'><input type="checkbox" checked='true' onchange="tb.selectAll(this.checked)"/></th>
	 <th id='code' width='80' editable='true'>cod.</th>
	 <th id='vencode' width='80' editable='true'>cod. art. forn.</th>
	 <th id='mancode' width='80' editable='true'>cod. art. produttore.</th>
	 <th id='brand' width='80' editable='true'>marca</th>
	 <th id='description' editable='true'>descrizione</th>
	 <th id='qty' width='40' editable='true' style='text-align:center'>qt&agrave;</th>
	 <th id='units' width='40' editable='true' style='text-align:center'>u.m.</th>
	 <th id='weight' width='40' editable='true' style='text-align:center'>peso (kg)</th>
	 <th id='vendorprice' width='60' editable='true' format='currency' decimals='2'>pr. acq.</th>
	 <th id='unitprice' width='60' editable='true' format='currency' decimals='2'>pr. unit.</th>
	 <th id='vat' width='40' editable='true' format='percentage' style='text-align:center'>% IVA</th>
	 <th id='cat' width='150' editable='true'>categoria</th>
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
 tb.FieldByName['cat'].enableSearch("dynarc cat-find -ap gmart -field name `","` -limit 20 --order-by `name ASC`", "id","name",null,true);

 tb.OnBeforeAddRow = function(r)
 {
  r.cells[0].innerHTML = "<input type='checkbox'/"+">";
 }

 tb.OnBeforeCellEdit = function(r,cell,value)
 {
  if(cell.tag == 'cat')
  {
   var ap = "";
   switch(r.getAttribute('type'))
   {
	case 'article' : 		ap="gmart"; break;
	case 'finalproduct' : 	ap="gproducts"; break;
	case 'component' : 		ap="gpart"; break;
	case 'material' : 		ap="gmaterial"; break;
   }

   if(ap)
    tb.FieldByName['cat'].enableSearch("dynarc cat-find -ap `"+ap+"` -field name `","` -limit 20 --order-by `name ASC`", "id","name",null,true);

   return;
  }
 }

 tb.OnCellEdit = function(r,cell,value,data)
 {
  switch(cell.tag)
  {
   case 'cat' : {
	 if(value && data)
	  cell.setAttribute('refid',data['id']);
	 else
	  cell.setAttribute('refid',0);
	} break;
  }
 }

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
	r.setAttribute('type', elements[c].getAttribute('type'));
	r.setAttribute('weightunits', elements[c].getAttribute('weightunits'));
	if(elements[c].getAttribute('code'))
	 r.cell['code'].setValue(elements[c].getAttribute('code'));
	if(elements[c].getAttribute('vencode'))
	 r.cell['vencode'].setValue(elements[c].getAttribute('vencode'));
	if(elements[c].getAttribute('mancode'))
	 r.cell['mancode'].setValue(elements[c].getAttribute('mancode'));
	if(elements[c].getAttribute('brand'))
	 r.cell['brand'].setValue(elements[c].getAttribute('brand'));
	r.cell['brand'].setAttribute('refid',elements[c].getAttribute('brandid'));
	r.cell['description'].setValue(elements[c].getAttribute('name') ? elements[c].getAttribute('name') : 'senza nome');
	r.cell['qty'].setValue(elements[c].getAttribute('qty') ? elements[c].getAttribute('qty') : '1');
	r.cell['qty'].style.textAlign='center';
	if(elements[c].getAttribute('units'))
	 r.cell['units'].setValue(elements[c].getAttribute('units'));			
	r.cell['units'].style.textAlign='center';
	if(elements[c].getAttribute('weight'))
	 r.cell['weight'].setValue(elements[c].getAttribute('weight'));
	r.cell['weight'].style.textAlign='center';
	r.cell['vendorprice'].setValue(elements[c].getAttribute('vendorprice') ? elements[c].getAttribute('vendorprice') : '0,00');
	r.cell['unitprice'].setValue(elements[c].getAttribute('unitprice') ? elements[c].getAttribute('unitprice') : '0,00');
	r.cell['vat'].setValue(elements[c].getAttribute('vat') ? elements[c].getAttribute('vat') : '0%');
	r.cell['vat'].style.textAlign='center';
   }
   document.body.removeChild(div);
   tb.selectAll(true);
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
	 else
	  ret.push(0);
	}

 sh.OnFinish = function(o,a){
	 if(a)
	  ret.push(a['id']);
	 else
	  ret.push(0);

	 var retlist = new Array();
	 var retIdx = 0;
	 for(var c=1; c < tb.O.rows.length; c++)
	 {
	  var r = tb.O.rows[c];
	  if(!r.selected)
	   retlist.push(0);
	  else
	  {
	   if(ret[retIdx])
	    retlist.push(ret[retIdx]);
	   else
		retlist.push(0);
	   retIdx++;
	  }
	 }

	 gframe_close(o,retlist);
	}

 var list = tb.GetSelectedRows();
 for(var c=0; c < list.length; c++)
 {
  var r = list[c];
  var _name = r.cell['description'].getValue();
  var brand = r.cell['brand'].getValue();
  if(_name.indexOf(brand) < 0)
   _name = brand+" "+_name;

  var catId = r.cell['cat'].getAttribute('refid') ? r.cell['cat'].getAttribute('refid') : "";

  var ap = "";
  var group = "";
  switch(r.getAttribute('type'))
  {
   case 'article' : 		{ap="gmart"; 		group="gmart"; } 	 break;
   case 'finalproduct' : 	{ap="gproducts"; 	group="gproducts"; } break;
   case 'component' : 		{ap="gpart"; 		group="gpart"; }	 break;
   case 'material' : 		{ap="gmaterial"; 	group="gmaterial"; } break;
  }

  switch(r.getAttribute('type'))
  {
   case 'article' : case 'component' : case 'material' : {
	 var qry = "dynarc new-item -ap `"+ap+"` -code-str `"+r.cell['code'].getValue()+"` -name `"+_name+"`"+(catId ? " -cat '"+catId+"'" : "");
	 qry+= " -group '"+group+"' -extset `"+ap+".model='''"+r.cell['description'].getValue()+"''',units='"+r.cell['units'].getValue()+"'";
	 qry+= ",weight='"+parseFloat(r.cell['weight'].getValue())+"',weightunits='"+(r.getAttribute('weightunits') ? r.getAttribute('weightunits') : 'kg')+"'";
	 qry+= ",brandid='"+r.cell['brand'].getAttribute('refid')+"',brand='''"+r.cell['brand'].getValue()+"''',mancode='"+r.cell['mancode'].getValue()+"'";
	 if(PARAMS['vendorid'])
	  qry+= ",vendorprices.vendor='''"+PARAMS['vendorname']+"''',vendorid='"+PARAMS['vendorid']+"',code='"+r.cell['vencode'].getValue()+"',price='"+parseCurrency(r.cell['vendorprice'].getValue())+"',vat='"+parseFloat(r.cell['vat'].getValue())+"'";
	 qry+= ",pricing.baseprice='"+parseCurrency(r.cell['unitprice'].getValue())+"',vat='"+parseFloat(r.cell['vat'].getValue())+"',autosetpricelists='1'";
	 qry+= "`";
	 sh.sendCommand(qry);
	} break;

   case 'finalproduct' : {
	 var qry = "dynarc new-item -ap `gproducts` -code-str `"+r.cell['code'].getValue()+"` -name `"+_name+"`"+(catId ? " -cat '"+catId+"'" : "");
	 qry+= " -group '"+group+"' -extset `gproducts.units='"+r.cell['units'].getValue()+"'";
	 qry+= ",weight='"+parseFloat(r.cell['weight'].getValue())+"',weightunits='"+(r.getAttribute('weightunits') ? r.getAttribute('weightunits') : 'kg')+"'";
	 qry+= ",pricing.baseprice='"+parseCurrency(r.cell['unitprice'].getValue())+"',vat='"+parseFloat(r.cell['vat'].getValue())+"',autosetpricelists='1'";
	 qry+= "`";
	 sh.sendCommand(qry);
	} break;
  }

 }

}
</script>
</body></html>
<?php

