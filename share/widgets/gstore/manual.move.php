<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 14-09-2013
 #PACKAGE: gstore
 #DESCRIPTION: Official Gnujiko Store Manager.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Manual move</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."var/objects/gmutable/index.php");

?>
</head><body>

<style type='text/css'>
table.footertable td {
	font-size: 14px;
	color: #333333;
}

table.pricelists {
	width: 560px;
}

table.pricelists th {
	background: #eeeeee;
	font-family: Arial;
	font-size: 9px;
	color: #000000;
}

table.pricelists td {
	font-family: Arial;
	font-size: 12px;
	color: #000000;
}

table.pricelists td em {float: left;}

table.pricelists tr.row0 td {background: #ffffcc;}
table.pricelists tr.row1 td {background: #ffff99;}

table.pricelists tr.selected td {background: #fafade;}

table#itemstable td {background: #fafade;}

</style>

<?php

$ret = GShell("store list",$_REQUEST['sessid'],$_REQUEST['shellid']);
$_STORE_LIST = $ret['outarr'];

$form = new GForm("Movimentazione magazzino", "MB_OK|MB_ABORT", "simpleform", "default", "orange", 600, 460);
$form->Begin($_ABSOLUTE_URL."share/widgets/gstore/img/manual.png");
echo "<div id='contents'>";

?>
<h3>Movimenta articoli da <select style='width:160px' id='storefrom'>
	 <?php
	  for($c=0; $c < count($_STORE_LIST); $c++)
	   echo "<option value='".$_STORE_LIST[$c]['id']."'>".$_STORE_LIST[$c]['name']."</option>";
	 ?>
	</select> a <select style='width:160px' id='storeto'>
	 <?php
	  for($c=0; $c < count($_STORE_LIST); $c++)
	   echo "<option value='".$_STORE_LIST[$c]['id']."'>".$_STORE_LIST[$c]['name']."</option>";
	 ?>
	</select>
</h3>
<div class="gmutable" style="width:560px;height:250px;background:#ffffff;border:0px;">
 <table id="itemstable" class="pricelists" cellspacing="2" cellpadding="2" border="0">
 <tr><th width='20'><input type="checkbox" onchange="tb.selectAll(this.checked)"/></th>
	 <th width='60' id='code' editable='true'>CODICE</th>
	 <th id='name' style='text-align:left;'>ARTICOLO</th>
	 <th width='70' id='qty' editable='true'>QTA&lsquo;</th>
	 <th width='20'>&nbsp;</th>
 </tr>
 </table>
</div>

<div style="height:34px;border-top:1px solid #dadada;padding:10px">
<b>Articolo: <input type="text" style="width:400px" placeholder="Digita il codice o le iniziali del nome dell'articolo" id="searchbycode"/>
</div>

<?php
echo "</div>";
$form->End();
?>

<script>
var tb = null;

function bodyOnLoad()
{
 var mE = EditSearch.init(document.getElementById('searchbycode'),
	"dynarc search -at `gmart` -fields barcode,code_str,name `","` -limit 10 --order-by 'code_str,name ASC'",
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

	 if(this.data && this.data['id'])
	  insertRow(this.data);
	 else
	 {
	  var sh = new GShell();
	  sh.OnError = function(msg){alert(msg);}
	  sh.OnOutput = function(o,a){
		 if(!a || !a['items'])
		  return alert("Articolo inesistente");
		 insertRow(a['items'][0]);
		}
	  sh.sendCommand("dynarc search -at `gmart` -fields barcode,code_str `"+this.value+"` -limit 1");
	 }

	 this.value = "";
	 this.data = null;
	}

 mE.focus();

 tb = new GMUTable(document.getElementById('itemstable'), {autoresize:false, autoaddrows:false});
 tb.OnCellEdit = function(r,cell,value){

	}

 tb.OnBeforeAddRow = function(r){
	 r.cells[0].innerHTML = "<input type='checkbox'/ >"; r.cells[0].style.textAlign='center';
	 r.cells[1].style.textAlign='center';
	 r.cells[2].style.textAlign='left';
	 r.cells[3].style.textAlign='center';

	}

 tb.OnDeleteRow = function(r){
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

function insertRow(data)
{
 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];
  if((r.getAttribute('refap') == data['tb_prefix']) && (r.getAttribute('refid') == data['id']))
  {
   var qty = parseFloat(r.cell['qty'].getValue());
   qty++;
   r.cell['qty'].setValue(qty);
   return r;
  }
 }


 var r = tb.AddRow();
 r.data = data;
 r.setAttribute('refap',data['tb_prefix']);
 r.setAttribute('refid',data['id']);
 r.cell['code'].setValue(data['code_str']);
 r.cell['name'].setValue(data['name']);
 r.cell['qty'].setValue("1");
 r.cells[4].innerHTML = "<img src='"+ABSOLUTE_URL+"share/widgets/gstore/img/delete.png' style='cursor:pointer' title='elimina'/"+">";
 r.cells[4].getElementsByTagName("IMG")[0].onclick = function(){this.parentNode.parentNode.remove();}
}

function OnFormSubmit()
{
 var q = "";
 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];
  q+= " -ap `"+r.data['tb_prefix']+"` -id `"+r.data['id']+"` -qty `"+r.cell['qty'].getValue()+"`";
 }

 var storeFrom = document.getElementById("storefrom").value;
 var storeTo = document.getElementById("storeto").value;
 var destStoreName = document.getElementById("storeto").options[document.getElementById("storeto").selectedIndex].innerHTML;
 
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(a && a['ddtinfo'])
	 {
	  alert("E' stata generata la bolla di movimentazione n. "+a['ddtinfo']['code_num']+(a['ddtinfo']['code_ext'] ? "/"+a['ddtinfo']['code_ext'] : ""));
	 }
	 gframe_close(o,a);
	}

 sh.sendCommand("store move -from `"+storeFrom+"` -to `"+storeTo+"` --generate-ddt"+q);
}

</script>
</body></html>
<?php

