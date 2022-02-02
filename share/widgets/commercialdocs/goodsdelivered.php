<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 08-04-2016
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Goods delivered auto-upload into store.
 #VERSION: 2.8beta
 #CHANGELOG: 08-04-2016 : Bugfix, aggiunto vendorid x caricamenti da ddt fornitore.
			 08-03-2016 : Aggiornamenti vari.
			 02-04-2015 : Integrato con taglie e colori.
			 02-08-2014 : Bug fix ed integrazione con prodotti finiti, componenti e materiali.
			 04-09-2013 : Possibilità di caricare lo stesso articolo su più magazzini.
			 31-07-2013 : Aggiunto il lotto di produzione.
			 27-07-2013 : Possibilità di specificare magazzini multipli e di auto-generare bolle di movim. interna.
			 15-03-2013 : Bug fix.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

if($_REQUEST['id'])
{
 $ret = GShell("dynarc item-info -ap `commercialdocs` -id `".$_REQUEST['id']."` -extget `cdinfo,cdelements`");
 $docInfo = $ret['outarr'];
}

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Goods Delivered</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");

?>
<style type='text/css'>
table.table td {
	font-family: Arial;
	font-size: 12px;
}

input.edit {
	background: #ffffff;
	border: 1px solid #6699cc;
	height: 21px;
	font-family: Arial, serif;
	font-size: 12px;
	color: #333333;
	border-radius: 2px;
}


table.checkavailtable th {
	font-family: Arial, sans-serif;
	font-size: 10px;
	color: #333333;
	height: 19px;
	background: #eeeeee;
}

table.checkavailtable td {
	font-family: Arial, sans-serif;
	font-size: 11px;
	border-bottom: 1px solid #dadada;
}

a.button {
 background: #4b98de;
 border: 1px solid #0169c9;
 border-radius: 1px;
 line-height: 26px;
 padding: 3px;
 padding-left: 6px;
 padding-right: 6px;
 font-family: Arial;
 font-size: 13px;
 font-weight: bold;
 color: #ffffff;
 text-decoration: none;
}

</style>
</head><body>

<?php

$ret = GShell("store list",$_REQUEST['sessid'],$_REQUEST['shellid']);
$storeList = $ret['outarr'];

$form = new GForm("Carico merce semi-automatico", "MB_OK|MB_ABORT", "simpleform", "default", "orange", 800, 480);
$form->Begin($_ABSOLUTE_URL."share/widgets/commercialdocs/img/checkavail.gif");
echo "<div id='contents' style='padding:5px;visibility:hidden'>";
?>
<p style="font-family:Arial,sans-serif;font-size:13px;color:#3364C3"><b>Caricare i seguenti articoli a magazzino?</b>
<select id='storelist' style='margin-left:270px;width:190px' onchange='selectMainStore(this)'><option value='0'>seleziona un magazzino</option>
  <?php
  for($c=0; $c < count($storeList); $c++)
   echo "<option value='".$storeList[$c]['id']."'>".$storeList[$c]['name']."</option>";
  ?></select>
</p>

<div style="height:200px;width:750;overflow:auto;border-bottom:1px solid #dadada;margin-top:20px">
<table class='checkavailtable' id='checkavailtable' width='730' cellspacing='0' cellpadding='0' border='0'>
<tr><th width='32'><input type='checkbox' onchange='checkAll(this)' checked='true'/></th>
	<th style='text-align:left'>ARTICOLO</th>
	<th width='60'>QTA&lsquo;</th>
	<th width='100' style='text-align:left'>Lotto di produz.</th>
	<th width='60'>TAGLIA</th>
	<th width='60'>COLORE</th>
	<th width='130' style='text-align:left'>FORNITORE</th>
	<th width='140' style='text-align:left'>MAGAZZINO</th>
	<th width='32'>&nbsp;</th>
</tr>
<?php
$count = 0;
$vendorName = $docInfo['subject_name'];

for($c=0; $c < count($docInfo['elements']); $c++)
{
 $itm = $docInfo['elements'][$c];
 if(($itm['type'] != "article") && ($itm['type'] != "finalproduct") && ($itm['type'] != "component") && ($itm['type'] != "material"))
  continue;
 $qty = $itm['qty'];

 echo "<tr id='".$itm['ref_id']."' refap='".$itm['ref_ap']."'>";
 echo "<td align='center'><input type='checkbox' checked='true'/></td>";
 echo "<td>".$itm['name']."</td>";
 echo "<td align='center'><input type='text' class='edit' style='width:40px' value=\"".$qty."\"/></td>";
 echo "<td><input type='text' class='edit' style='width:90px' value=\"".$itm['lot']."\"/></td>";
 echo "<td align='center'>".($itm['variant_sizmis'] ? $itm['variant_sizmis'] : '&nbsp;')."</td>";
 echo "<td align='center'>".($itm['variant_coltint'] ? $itm['variant_coltint'] : '&nbsp;')."</td>";
 echo "<td>".($vendorName ? $vendorName : "???")."</td>";
 echo "<td><select style='width:130px' onchange='storeChange()'><option value='0'>&nbsp;</option>";
 for($i=0; $i < count($storeList); $i++)
  echo "<option value='".$storeList[$i]['id']."'>".$storeList[$i]['name']."</option>";
 echo "</select></td>";
 echo "<td><a href='#' onclick='duplicateRow(this.parentNode.parentNode)'><img src='"
	.$_ABSOLUTE_URL."share/widgets/commercialdocs/img/add.gif' border='0'/></a></td></tr>";
 $count++;
}
?>

</table></div>
<br/>

 <div id='storelistwarning' style="height:60px;<?php if(count($storeList)) echo 'display:none;'; ?>">
  <img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/img/warning.png" style="float:left;vertical-align:top;margin-right:10px;"/> 
  <span style="color:#F31903;font-size:14px;font-family:Arial"><b>Nel tuo profilo aziendale non è stato ancora registrato alcun magazzino.</b></span><br/>
  <span style="color:#333333;font-size:13px;font-family:Arial"><b>Per continuare è necessario averne almeno uno.</b></span>
  <a href='#' onclick='configureStores()' class='button' style="margin-left:20px">Configura &raquo;</a>
 </div>

 <p id='storelistselect'>

 </p>

 

<p><input type='checkbox' id='ispaid'/> <b>Segna come pagato.</b></p>
<?php
echo "</div>";
$form->End();
?>

<script>
var VENDOR_ID = "<?php echo $docInfo['subject_id']; ?>";

function bodyOnLoad()
{
 if(<?php echo $count; ?> == 0)
 {
  gframe_hide();
  var sh = new GShell();
  sh.OnOutput = function(o,a){gframe_close(o,a);}
  sh.sendCommand("dynarc edit-item -ap commercialdocs -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status=7`");
 }
 else
  document.getElementById('contents').style.visibility = "visible";
}

function OnFormSubmit()
{
 var isPaid = document.getElementById('ispaid').checked ? true : false;
 var status = isPaid ? 10 : 7;

 // divide gli articoli per magazzino //
 var tb = document.getElementById('checkavailtable');
 var xml = "";
 var storeList = new Array();

 for(var c=1; c < tb.rows.length; c++)
 {
  if(tb.rows[c].cells[0].getElementsByTagName('INPUT')[0].checked)
  {
   var storeId = tb.rows[c].cells[7].getElementsByTagName('SELECT')[0].value;
   if(storeId)
   {
	var qty = tb.rows[c].cells[2].getElementsByTagName('INPUT')[0].value;
	var q = tb.rows[c].getAttribute('refap')+":"+tb.rows[c].id+"X"+parseFloat(qty);
    var lot = tb.rows[c].cells[3].getElementsByTagName('INPUT')[0].value;
	var sizmis = (tb.rows[c].cells[4].innerHTML != "&nbsp;") ? tb.rows[c].cells[4].innerHTML : "";
	var coltint = (tb.rows[c].cells[5].innerHTML != "&nbsp;") ? tb.rows[c].cells[5].innerHTML : "";

	if(storeList.indexOf(storeId) < 0)
	 storeList.push(storeId);

	xml+= "<item ap='"+tb.rows[c].getAttribute('refap')+"' id='"+tb.rows[c].id+"' qty='"+parseFloat(qty)+"' lot='"+lot+"' sizmis='"+sizmis+"' coltint='"+coltint+"' storeid='"+storeId+"'/"+">";

   }
  }
 }

 if(xml == "")
  return saveFinish(isPaid, status);


 var cmd = "commercialdocs upload-goods-delivered -xml `"+xml+"` -refid `<?php echo $docInfo['id']; ?>` -vendorid '"+VENDOR_ID+"'";
 for(var c=0; c < storeList.length; c++)
 {
  if(document.getElementById("autogenddt-"+storeList[c]).checked == true)
   cmd+= " -gen-ddt-storeid '"+storeList[c]+"'";
 }

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(a && a['ddtlist'])
	  saveFinish(isPaid, status, a['ddtlist']);
	 else
	  saveFinish(isPaid, status);
	}

 sh.sendCommand(cmd);
}

function saveFinish(isPaid, status, ddtGenerated)
{
 var today = new Date();
 if(isPaid)
 {
  var sh2 = new GShell();
  sh2.OnOutput = function(o,a){
	 if(!a) return gframe_close(o,a);
	 var date = new Date(parseFloat(a['ctime'])*1000);
	 var sh3 = new GShell();
	 sh3.OnOutput = function(oo,aa){
		 // if ddt generated //
		 if(ddtGenerated && ddtGenerated.length)
		 {
		  var msg = "Sono state generate le seguenti bolle di movimentazione merci:\n";
		  for(var c=0; c < ddtGenerated.length; c++)
		   msg+= "D.D.T. n. "+ddtGenerated[c]['code_num']+" del "+today.printf('d/m/Y')+"\n";
		  alert(msg);
		 }
		 gframe_close(oo,aa);
		}
	 sh3.sendCommand("dynarc edit-item -ap commercialdocs -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status=10,payment-date='"+date.printf('Y-m-d')+"',cdelements.setallsent=1`");
	}
  sh2.sendCommand("gframe -f commercialdocs/pay -params `id=<?php echo $docInfo['id']; ?>&desc=Saldo&isdebit=true`");
 }
 else
 {
  var sh2 = new GShell();
  sh2.OnOutput = function(o,a){
	 if(ddtGenerated && ddtGenerated.length)
	 {
	  var msg = "Sono state generate le seguenti bolle di movimentazione merci:\n";
	  for(var c=0; c < ddtGenerated.length; c++)
	   msg+= "D.D.T. n. "+ddtGenerated[c]['code_num']+" del "+today.printf('d/m/Y')+"\n";
	  alert(msg);
	 }
	 gframe_close(o,a);
	}
  sh2.sendCommand("dynarc edit-item -ap commercialdocs -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status="+status+",cdelements.setallsent=1`");
 }
}

function checkAll(cb)
{
 var tb = document.getElementById('checkavailtable');
 for(var c=1; c < tb.rows.length; c++)
  tb.rows[c].cells[0].getElementsByTagName('INPUT')[0].checked = cb.checked;
}

function configureStores()
{
 var sh = new GShell();
 sh.OnOutput = function(oo,aa){
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){
		 if(!a) return;
		 var sel = document.getElementById('storeid');
		 for(var c=0; c < a.length; c++)
		 {
		  var opt = document.createElement('OPTION');
		  opt.value = a[c]['id'];
		  opt.innerHTML = a[c]['name'];
		  sel.appendChild(opt);
		 }
		 document.getElementById('storelistwarning').style.display = "none";
		 document.getElementById('storelistselect').style.display = "";
		}
	 sh2.sendCommand("store list");
	}
 sh.sendSudoCommand("gframe -f config.companyprofile -params `show=stores`");
}

function selectMainStore(sel)
{
 var tb = document.getElementById('checkavailtable');
 for(var c=1; c < tb.rows.length; c++)
  tb.rows[c].cells[7].getElementsByTagName('SELECT')[0].value = sel.value;
 storeChange();
}

function storeChange()
{
 var p = document.getElementById('storelistselect');
 p.innerHTML = "";
 var stores = new Array();

 var tb = document.getElementById('checkavailtable');
 for(var c=1; c < tb.rows.length; c++)
 {
  var storeId = tb.rows[c].cells[7].getElementsByTagName('SELECT')[0].value;
  if(stores.indexOf(storeId) < 0)
   stores.push(storeId);
 }

 var sel = document.getElementById('storelist');
 var html = "";
 for(var c=1; c < sel.options.length; c++)
 {
  if(stores.indexOf(sel.options[c].value) > -1)
   html+= "<input type='checkbox' id='autogenddt-"+sel.options[c].value+"'/"+"><b>Genera automaticamente doc. di trasporto per il magazzino: "+sel.options[c].innerHTML+"</b><br/"+">";
 }
 p.innerHTML = html;
}

function duplicateRow(rObj)
{
 var tb = document.getElementById('checkavailtable');
 var r = tb.insertRow(rObj.rowIndex+1);
 r.id = rObj.id;
 r.setAttribute('refap',rObj.getAttribute('refap'));
 
 r.insertCell(-1).innerHTML = "<input type='checkbox' checked='true'/"+">";
 r.cells[0].style.textAlign='center';

 r.insertCell(-1).innerHTML = rObj.cells[1].innerHTML;

 r.insertCell(-1).innerHTML = "<input type='text' class='edit' style='width:40px' value='0'/"+">";
 r.cells[2].style.textAlign='center';

 r.insertCell(-1).innerHTML = "<input type='text' class='edit' style='width:90px' value=''/"+">";

 r.insertCell(-1).innerHTML = rObj.cells[4].innerHTML; r.cells[4].style.textAlign='center';
 r.insertCell(-1).innerHTML = rObj.cells[5].innerHTML; r.cells[5].style.textAlign='center';

 r.insertCell(-1).innerHTML = rObj.cells[6].innerHTML;

 var html = "<select style='width:130px' onchange='storeChange()'>";
 var oSel = rObj.cells[7].getElementsByTagName('SELECT')[0];
 for(var c=0; c < oSel.options.length; c++)
  html+= "<option value='"+oSel.options[c].value+"'>"+oSel.options[c].innerHTML+"</option>";
 html+= "</select>";

 r.insertCell(-1).innerHTML = html;

 r.insertCell(-1).innerHTML = "<a href='#' onclick='removeDuplicatedRow(this.parentNode.parentNode)'><img src='"+ABSOLUTE_URL+"share/widgets/commercialdocs/img/delete.png'/"+"></a>";
}

function removeDuplicatedRow(rObj)
{
 var tb = document.getElementById('checkavailtable');
 tb.deleteRow(rObj.rowIndex);
}
</script>
</body></html>
<?php

