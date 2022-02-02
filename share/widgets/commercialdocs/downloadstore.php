<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-12-2012
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Auto-download articles from store.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

if($_REQUEST['id'])
{
 $ret = GShell("dynarc item-info -ap `commercialdocs` -id `".$_REQUEST['id']."` -extget `cdinfo,cdelements`",$_REQUEST['sessid'],$_REQUEST['shellid']);
 $docInfo = $ret['outarr'];
}

/* Get stores */
$ret = GShell("store list",$_REQUEST['sessid'],$_REQUEST['shellid']);
$storeList = $ret['outarr'];

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Download Store</title>
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

$form = new GForm("Scarico articoli semi-automatico", "MB_OK|MB_ABORT", "simpleform", "default", "orange", 640, 480);
$form->Begin($_ABSOLUTE_URL."share/widgets/commercialdocs/img/checkavail.gif");
echo "<div id='contents' style='padding:5px;visibility:hidden'>";
?>
<p style="font-family:Arial,sans-serif;font-size:13px;color:#3364C3"><b>Scaricare i seguenti articoli dal magazzino?</b>
<select id='storelist' style='margin-left:90px;width:190px' onchange='selectMainStore(this)'><option value='0'>seleziona un magazzino</option>
  <?php
  for($c=0; $c < count($storeList); $c++)
   echo "<option value='".$storeList[$c]['id']."'>".$storeList[$c]['name']."</option>";
  ?></select>
</p>

<div style="height:200px;width:590;overflow:auto;border-bottom:1px solid #dadada;margin-top:20px">
<table class='checkavailtable' id='checkavailtable' width='570' cellspacing='0' cellpadding='0' border='0'>
<tr><th width='32'><input type='checkbox' onchange='checkAll(this)' checked='true'/></th>
	<th style='text-align:left'>ARTICOLO</th>
	<th width='60'>QTA&lsquo;</th>
	<th width='170' style='text-align:left'>MAGAZZINO</th>
	<th width='70'>GIAC.FIS.</th>
	<th width='60'>IMPEGN.</th>
	<th width='60'>DISP.</th></tr>
<?php
$db = new AlpaDatabase();
$count = 0;
for($c=0; $c < count($docInfo['elements']); $c++)
{
 $itm = $docInfo['elements'][$c];
 if($itm['type'] != "article")
  continue;
 $qty = $itm['qty'];

 $db->RunQuery("SELECT * FROM dynarc_".$itm['ref_ap']."_items WHERE id='".$itm['ref_id']."'");
 $db->Read();

 $avail = ($db->record['storeqty'] - $db->record['booked']) - $db->record['incoming'];

 $storeQty = $db->record['storeqty'];
 $booked = $db->record['booked'];

 $storeInfo = null;
 for($i=0; $i < count($storeList); $i++)
 {
  if($db->record['store_'.$storeList[$i]['id'].'_qty'] >= $qty)
  {
   $storeInfo = $storeList[$i];
   break;
  }
 }

 $tobeord = $qty - $avail;

 echo "<tr id='".$itm['ref_id']."' refap='".$itm['ref_ap']."'>";
 echo "<td align='center'><input type='checkbox' checked='true'/></td>";
 echo "<td>".$itm['name']."</td>";
 echo "<td align='center'>".$qty."</td>";
 echo "<td><select style='width:160px'>";
 for($i=0; $i < count($storeList); $i++)
  echo "<option value='".$storeList[$i]['id']."'".($db->record['store_'.$storeList[$i]['id'].'_qty'] >= $qty ? " selected='selected'>" : ">")
	.$storeList[$i]['name']."</option>";
 echo "</select></td>";
 echo "<td align='center'>".($storeQty ? $storeQty : "0")."</td>";
 echo "<td align='center'>".($booked ? $booked : "&nbsp;")."</td>";
 echo "<td align='center'>".($avail ? $avail : "0")."</td></tr>";
 $count++;
}
$db->Close();
?>

</table></div>
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
  sh.sendCommand("dynarc edit-item -ap commercialdocs -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status=7`");
 }
 else
  document.getElementById('contents').style.visibility = "visible";
}

function OnFormSubmit()
{
 var tb = document.getElementById('checkavailtable');
 var q = "";
 for(var c=1; c < tb.rows.length; c++)
 {
  if(tb.rows[c].cells[0].getElementsByTagName('INPUT')[0].checked)
   q+= " && store download -ap `"+tb.rows[c].getAttribute('refap')+"` -id `"+tb.rows[c].id+"` -qty `"+parseFloat(tb.rows[c].cells[2].innerHTML)+"` -store `"+tb.rows[c].cells[3].getElementsByTagName('SELECT')[0].value+"` -docap commercialdocs -docid `<?php echo $docInfo['id']; ?>` --unbook";
 }

 if(!q || (q==""))
 {
  var sh = new GShell();
  sh.OnOutput = function(o,a){gframe_close(o,a);}
  sh.sendCommand("dynarc edit-item -ap commercialdocs -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status=7`");
 }
 else
 { 
  var sh = new GShell();
  sh.OnFinish = function(){
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){gframe_close(o,a);}
	 sh2.sendCommand("dynarc edit-item -ap commercialdocs -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status=7`");
	}
  sh.sendCommand(q.substr(4));
 }
}

function checkAll(cb)
{
 var tb = document.getElementById('checkavailtable');
 for(var c=1; c < tb.rows.length; c++)
  tb.rows[c].cells[0].getElementsByTagName('INPUT')[0].checked = cb.checked;
}

function selectMainStore(sel)
{
 var tb = document.getElementById('checkavailtable');
 for(var c=1; c < tb.rows.length; c++)
  tb.rows[c].cells[3].getElementsByTagName('SELECT')[0].value = sel.value;

}

</script>
</body></html>
<?php
