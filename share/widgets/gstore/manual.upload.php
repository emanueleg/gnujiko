<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-10-2013
 #PACKAGE: gstore
 #DESCRIPTION: Official Gnujiko Store Manager.
 #VERSION: 2.1beta
 #CHANGELOG: 18-10-2013 : Possibilità di sparare col codice a barre
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Manual upload</title>
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

$form = new GForm("Caricamento manuale del magazzino", "MB_OK|MB_ABORT", "simpleform", "default", "orange", 600, 380);
$form->Begin($_ABSOLUTE_URL."share/widgets/gstore/img/manual.png");
echo "<div id='contents'>";

?>
<h3>Lista degli articoli da caricare a magazzino</h3>
<div class="gmutable" style="width:560px;height:180px;background:#ffffff;border:0px;">
 <table id="itemstable" class="pricelists" cellspacing="2" cellpadding="2" border="0">
 <tr><th width='20'><input type="checkbox" onchange="tb.selectAll(this.checked)"/></th>
	 <th width='60' id='code' editable='true'>CODICE</th>
	 <th id='name' style='text-align:left;'>ARTICOLO</th>
	 <th width='70' id='qty' editable='true'>QTA&lsquo;</th>
 </tr>
 </table>
</div>

<div style="height:34px;border-top:1px solid #dadada;padding:10px">
<table border='0' class='footertable'>
 <tr><td><b>Magazzino:</b></td>
	 <td><select id='store' style='width:160px'><?php
		 $ret = GShell("store list");
		 $list = $ret['outarr'];
		 for($c=0; $c < count($list); $c++)
		  echo "<option value='".$list[$c]['id']."'".($_REQUEST['storeid'] == $list[$c]['id'] ? " selected='selected'>" : ">")
			.$list[$c]['name']."</option>";
		?></select></td>
 	 <td><b>Codice:</b></td>
	 <td><input type='text' style='width:220px' id='searchbycode' value=''/></td></tr>
</table>
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

	 var sh = new GShell();
	 sh.OnError = function(msg){alert(msg);}
	 sh.OnOutput = function(o,a){
		 if(!a)
		  return alert("L'articolo cod. "+this.value+" è inesistente");
		 insertRow(a);
		 mE.value = "";
		 mE.data = null;
		 mE.focus();
		}

	 if(this.data && this.data['id'])
	  sh.sendCommand("commercialdocs get-full-info -type gmart -ap `"+this.data['tb_prefix']+"` -id `"+this.data['id']+"`");
	 else
	  sh.sendCommand("commercialdocs get-full-info -type gmart --code-or-barcode `"+this.value+"`");
	}

 mE.focus();

 tb = new GMUTable(document.getElementById('itemstable'), {autoresize:false, autoaddrows:false});
 tb.OnCellEdit = function(r,cell,value){}
 tb.OnBeforeAddRow = function(r){
	 r.cells[0].innerHTML = "<input type='checkbox'/ >"; r.cells[0].style.textAlign='center';
	 r.cells[1].style.textAlign='center';
	 r.cells[2].style.textAlign='left';
	 r.cells[3].style.textAlign='center';
	}
 tb.OnDeleteRow = function(r){}
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
  if((tb.O.rows[c].data['ap'] == data['ap']) && (tb.O.rows[c].data['id'] == data['id']))
  {
   tb.O.rows[c].cell['qty'].setValue(parseFloat(tb.O.rows[c].cell['qty'].getValue())+1);
   return;
  }
 }

 var r = tb.AddRow();
 r.data = data;
 r.cell['code'].setValue(data['code_str']);
 r.cell['name'].setValue(data['name']);
 r.cell['qty'].setValue("1");
}

function OnFormSubmit()
{
 var q = "";
 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];
  q+= " -ap `"+r.data['tb_prefix']+"` -id `"+r.data['id']+"` -qty `"+r.cell['qty'].getValue()+"`";
 }
 
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 gframe_close(o,a);
	}

 sh.sendCommand("store upload -store `"+document.getElementById('store').value+"`"+q);
}

</script>
</body></html>
<?php

