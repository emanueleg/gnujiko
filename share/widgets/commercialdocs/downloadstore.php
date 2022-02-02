<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 29-11-2016
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Auto-download articles from store.
 #VERSION: 2.8beta
 #CHANGELOG: 29-11-2016 : ProcessMessage durante scaricamento.
			 29-03-2016 : Bug fix coltint e sizmis.
			 03-02-2016 : Bug fix giac. fisica con valori negativi.
			 01-02-2016 : Aggiornato con qty_downloaded.
			 02-04-2015 : Integrato con taglie e colori.
			 01-04-2015 : Bug fix su avail.
			 02-08-2014 : Bug fix ed integrazione con prodotti finiti, componenti e materiali.
			 13-04-2014 : Bug fix vari
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";
$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : "commercialdocs";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

if($_REQUEST['id'])
{
 $ret = GShell("dynarc item-info -ap `".$_AP."` -id `".$_REQUEST['id']."` -extget `cdelements`",$_REQUEST['sessid'],$_REQUEST['shellid']);
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

$form = new GForm("Scarico articoli semi-automatico", "MB_OK|MB_ABORT", "simpleform", "default", "orange", 800, 480);
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

<div style="height:270px;width:750;overflow:auto;border-bottom:1px solid #dadada;margin-top:20px">
<table class='checkavailtable' id='checkavailtable' width='730' cellspacing='0' cellpadding='0' border='0'>
<tr><th width='32'><input type='checkbox' onchange='checkAll(this)' checked='true'/></th>
	<th style='text-align:left'>ARTICOLO</th>
	<th width='60'>QTA&lsquo;</th>
	<th width='60'>TAGLIA</th>
	<th width='60'>COLORE</th>
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
 if(($itm['type'] != "article") && ($itm['type'] != "finalproduct") && ($itm['type'] != "component") && ($itm['type'] != "material"))
  continue;

 if($itm['qty'] == $itm['qty_downloaded'])
  continue;

 $qty = $itm['qty'] - $itm['qty_downloaded'];

 $db->RunQuery("SELECT * FROM dynarc_".$itm['ref_ap']."_items WHERE id='".$itm['ref_id']."'");
 $db->Read();

 //$avail = ($db->record['storeqty'] - $db->record['booked']) - $db->record['incoming'];

 $storeQty = ($db->record['storeqty'] > 0) ? $db->record['storeqty'] : 0;
 $booked = ($db->record['booked'] > 0) ? $db->record['booked'] : 0;
 $incoming = ($db->record['incoming'] > 0) ? $db->record['incoming'] : 0;

 $avail = $storeQty + $incoming - $booked;

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
 echo "<td align='center'>".($itm['variant_sizmis'] ? $itm['variant_sizmis'] : '&nbsp;')."</td>";
 echo "<td align='center'>".($itm['variant_coltint'] ? $itm['variant_coltint'] : '&nbsp;')."</td>";
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
<div style='margin-top:10px'>
Causale: <select id='causal'><option value=''></option><?php
$ret = GShell("dynarc item-list -ap storemovcausals -ct DOWNLOAD",$_REQUEST['sessid'],$_REQUEST['shellid']);
$list = $ret['outarr']['items'];
for($c=0;$c < count($list); $c++)
 echo "<option value='".$list[$c]['code_str']."'>".$list[$c]['name']."</option>";
?></select>
</div>
<?php
echo "</div>";
$form->End();
?>

<script>
function bodyOnLoad()
{
 var AP = "<?php echo $_AP; ?>";
 if(<?php echo $count; ?> == 0)
 {
  gframe_hide();
  if(AP != "commercialdocs")
   return gframe_close("",true);
  var sh = new GShell();
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,a){gframe_close(o,a);}
  sh.sendCommand("dynarc edit-item -ap commercialdocs -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status=7`");
 }
 else
  document.getElementById('contents').style.visibility = "visible";
}

function OnFormSubmit()
{
 var AP = "<?php echo $_AP; ?>";
 var forceUnbook = <?php echo $_REQUEST['forceunbook'] ? "true" : "false"; ?>;
 var tb = document.getElementById('checkavailtable');
 var causal = document.getElementById('causal').value;
 var q = "";
 for(var c=1; c < tb.rows.length; c++)
 {
  var colTint = tb.rows[c].cells[4].innerHTML;
  var sizMis = tb.rows[c].cells[3].innerHTML;
  if(colTint) 	colTint = colTint.replace("&nbsp;","");
  if(sizMis)	sizMis = sizMis.replace("&nbsp;","");

  if(tb.rows[c].cells[0].getElementsByTagName('INPUT')[0].checked)
   q+= " && store download -ap `"+tb.rows[c].getAttribute('refap')+"` -id `"+tb.rows[c].id+"` -qty `"+parseFloat(tb.rows[c].cells[2].innerHTML)+"` -sizmis `"+sizMis+"` -coltint `"+colTint+"` -store `"+tb.rows[c].cells[5].getElementsByTagName('SELECT')[0].value+"` -docap `"+AP+"` -docid `<?php echo $docInfo['id']; ?>` -causal `"+causal+"`"+(forceUnbook ? " --force-unbook" : " --unbook");
 }

 if(!q || (q==""))
 {
  if(AP != "commercialdocs")
   return gframe_close("",true);
  var sh = new GShell();
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,a){gframe_close(o,a);}
  sh.sendCommand("dynarc edit-item -ap commercialdocs -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status=7`");
 }
 else
 {
  var sh = new GShell();
  sh.showProcessMessage("Scaricamento magazzino", "Attendere prego, &egrave; in corso l&lsquo;aggiornamento delle scorte di magazzino");
  sh.OnError = function(err,errcode){
	 this.hideProcessMessage();
	 switch(errcode)
	 {
	  case "INSUFFICIENT_STORAGE" : alert("Impossibile scaricare dal magazzino perchè la giacenza fisica è minore della quantità richiesta."); break;
	  default : alert(err); break;
	 }
	}

  sh.OnFinish = function(){
	 this.hideProcessMessage();
	 if(AP != "commercialdocs")
	  return gframe_close("",true);
	 var sh2 = new GShell();
	 sh2.OnError = function(err){alert(err);}
	 sh2.OnOutput = function(o,a){gframe_close(o,a);}
	 sh2.sendCommand("dynarc edit-item -ap commercialdocs -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status=7,cdelements.setalldownloaded=1`");
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
  tb.rows[c].cells[5].getElementsByTagName('SELECT')[0].value = sel.value;

}

</script>
</body></html>
<?php

