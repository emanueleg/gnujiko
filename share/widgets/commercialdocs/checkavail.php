<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-08-2013
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Check store availability.
 #VERSION: 2.1beta
 #CHANGELOG: 13-08-2013 : Bug fix sulle quantità.
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
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Check Availability</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
?>
<style type='text/css'>
table.table td {
	font-family: Arial;
	font-size: 12px;
}

input.text {
	background: #ffffff;
	border: 1px solid #6699cc;
	height: 25px;
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

</style>
</head><body>

<?php

$form = new GForm("Verifica disponibilit&agrave; a magazzino", "MB_OK|MB_ABORT", "simpleform", "default", "orange", 640, 480);
$form->Begin($_ABSOLUTE_URL."share/widgets/commercialdocs/img/checkavail.gif");
echo "<div id='contents' style='padding:5px;visibility:hidden'>";
?>
<p style="font-family:Arial,sans-serif;font-size:13px;color:#f31903"><b>A magazzino non c&lsquo;&eacute; sufficiente disponibilit&aacute; per i seguenti articoli, pertanto qui di seguito viene riportata la lista dei prodotti da ordinare a fornitore.</b></p>

<div style="height:200px;width:590;overflow:auto;border-bottom:1px solid #dadada;margin-top:20px">
<table class='checkavailtable' id='checkavailtable' width='570' cellspacing='0' cellpadding='0' border='0'>
<tr><th width='32'><input type='checkbox' onchange='checkAll(this)' checked='true'/></th>
	<th style='text-align:left'>ARTICOLO</th>
	<th width='60'>QTA&lsquo;</th>
	<th width='100' style='text-align:left'>FORNITORE</th>
	<th width='70'>GIAC.FIS.</th>
	<th width='60'>IMPEGN.</th>
	<th width='60'>DISP.</th>
	<th width='70'>DA ORD.</th></tr>

<?php
$db = new AlpaDatabase();
$count = 0;
for($c=0; $c < count($docInfo['elements']); $c++)
{
 $itm = $docInfo['elements'][$c];
 if($itm['type'] != "article")
  continue;
 $qty = $itm['qty'];

 $db->RunQuery("SELECT storeqty,booked,incoming FROM dynarc_".$itm['ref_ap']."_items WHERE id='".$itm['ref_id']."'");
 $db->Read();

 $avail = ($db->record['storeqty'] - $db->record['booked']) - $db->record['incoming'];
 if($avail >= $qty)
  continue;

 $storeQty = $db->record['storeqty'];
 $booked = $db->record['booked'];

 /* get vendor info */
 $db->RunQuery("SELECT vendor_name FROM dynarc_".$itm['ref_ap']."_vendorprices WHERE item_id='".$itm['ref_id']."'");
 $db->Read();
 $vendorName = $db->record['vendor_name'];

 $tobeord = $qty - $avail;

 echo "<tr id='".$itm['ref_id']."' refap='".$itm['ref_ap']."'>";
 if($vendorName)
  echo "<td align='center'><input type='checkbox' checked='true'/></td>";
 else
  echo "<td align='center'><input type='checkbox' readonly='true'/></td>";
 echo "<td>".$itm['name']."</td>";
 echo "<td align='center'>".$qty."</td>";
 echo "<td>".($vendorName ? $vendorName : "???")."</td>";
 echo "<td align='center'>".($storeQty ? $storeQty : "0")."</td>";
 echo "<td align='center'>".($booked ? $booked : "&nbsp;")."</td>";
 echo "<td align='center'>".($avail ? $avail : "0")."</td>";
 echo "<td align='center'>".($tobeord ? $tobeord : "&nbsp;")."</td></tr>";
 $count++;
}
$db->Close();
?>

</table></div>
<br/>
<p><input type='checkbox' checked='true' id='autoinsert'/> <b>Inserisci automaticamente questi articoli negli ordini a fornitore.</b></p>
<br/>
<div style="text-align:center;font-family:Arial,sans-serif;font-size:12px;color:#666666">Se vi sono gi&agrave; degli ordini aperti per i suddetti fornitori il sistema provveder&agrave; ad aggiungere questi articoli su quegli ordini da evadere, altrimenti verranno generati automaticamente nuovi ordini.</div>


<?php
echo "</div>";
$form->End();
?>

<script>
function bodyOnLoad()
{
 if(<?php echo $count; ?> == 0)
 {
  gframe_hide();
  var sh = new GShell();
  sh.OnOutput = function(o,a){gframe_close(o,a);}
  sh.sendCommand("dynarc edit-item -ap commercialdocs -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status=3`");
 }
 else
  document.getElementById('contents').style.visibility = "visible";
}

function OnFormSubmit()
{
 if(!document.getElementById('autoinsert').checked)
 {
  var sh = new GShell();
  sh.OnOutput = function(o,a){gframe_close(o,a);}
  sh.sendCommand("dynarc edit-item -ap commercialdocs -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status=3`");
  return;
 }

 var tb = document.getElementById('checkavailtable');
 var q = "";
 for(var c=1; c < tb.rows.length; c++)
 {
  if(tb.rows[c].cells[0].getElementsByTagName('INPUT')[0].checked)
   q+= ","+tb.rows[c].getAttribute('refap')+":"+tb.rows[c].id+"X"+parseFloat(tb.rows[c].cells[7].innerHTML);
 }

 var sh = new GShell();
 sh.OnOutput = function(){
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){gframe_close(o,a);}
	 sh2.sendCommand("dynarc edit-item -ap commercialdocs -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status=3`");
	}
 sh.sendCommand("commercialdocs generate-vendor-orders `"+q.substr(1)+"`");
}

function checkAll(cb)
{
 var tb = document.getElementById('checkavailtable');
 for(var c=1; c < tb.rows.length; c++)
  tb.rows[c].cells[0].getElementsByTagName('INPUT')[0].checked = cb.checked;
}

</script>
</body></html>
<?php

