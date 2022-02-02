<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 22-05-2016
 #PACKAGE: gstore
 #DESCRIPTION: Modifica giacenze articoli
 #VERSION: 2.3beta
 #CHANGELOG: 22-05-2016 : Bugfix salvataggio prenotati e ordinati.
			 23-03-2016 : Aggiornata funzione submit.
			 08-04-2014 : Allargata la finestra
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Edit movements</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."var/objects/gmutable/index.php");

$_ITEMS = array();
if($_REQUEST['ids'])
{
 $tmp = explode(",",$_REQUEST['ids']);
 for($c=0; $c < count($tmp); $c++)
 {
  $x = explode(":",$tmp[$c]);
  $_ITEMS[] = array('ap'=>$x[0],'id'=>$x[1]);
 }
}

$ret = GShell("store list",$_REQUEST['sessid'],$_REQUEST['shellid']);
$_STORES = $ret['outarr'];

$db = new AlpaDatabase();
for($c=0; $c < count($_ITEMS); $c++)
{
 $db->RunQuery("SELECT * FROM dynarc_".$_ITEMS[$c]['ap']."_items WHERE id='".$_ITEMS[$c]['id']."'");
 $db->Read();
 $db->record['ap'] = $_ITEMS[$c]['ap'];
 for($i=0; $i < count($_STORES); $i++)
 {
  if(!$_STORES[$i]['items'])
   $_STORES[$i]['items'] = array();
  if($db->record['store_'.$_STORES[$i]['id'].'_qty'] > 0)
   $_STORES[$i]['items'][] = $db->record;
 }
}
$db->Close();

?>
</head><body>

<style type='text/css'>
table.footertable td {
	font-size: 14px;
	color: #333333;
}

table.movements {
	width: 860px;
}

table.movements th {
	background: #eeeeee;
	font-family: Arial;
	font-size: 9px;
	color: #000000;
}

table.movements td {
	font-family: Arial;
	font-size: 12px;
	color: #000000;
}

table.movements tr.label td {
	background: #ffffff;
	color: #3364C3;
	font-weight: bold;
}


table.movements td em {float: left;}

table.movements tr.row0 td {background: #ffffcc;}
table.movements tr.row1 td {background: #ffff99;}

table.movements tr.selected td {background: #fafade;}

table#itemstable td {background: #fafade;}

</style>

<?php

$form = new GForm("Modifica giacenze articoli", "MB_OK|MB_ABORT", "simpleform", "default", "orange", 900, 480);
$form->Begin($_ABSOLUTE_URL."share/widgets/gstore/img/edit.png");
echo "<div id='contents'>";

?>

<div class="gmutable" style="width:860px;height:360px;background:#ffffff;border:0px;">
 <table id="itemstable" class="movements" cellspacing="2" cellpadding="2" border="0">
 <tr><th width='60'>CODICE</th>
	 <th style='text-align:left;'>ARTICOLO</th>
	 <?php
	 for($i=0; $i < count($_STORES); $i++)
	  echo "<th width='70' id='store_".$_STORES[$i]['id']."_qty' editable='true'><small>".$_STORES[$i]['name']."</small></th>";
	 ?>
	 <th width='70' id='booked' editable='true'>PRENOTATI</th>
	 <th width='70' id='incoming' editable='true'>ORDINATI</th>
 </tr>
 <?php
 $db = new AlpaDatabase();
 for($c=0; $c < count($_ITEMS); $c++)
 {
  $db->RunQuery("SELECT * FROM dynarc_".$_ITEMS[$c]['ap']."_items WHERE id='".$_ITEMS[$c]['id']."'");
  $db->Read();
  $db->record['ap'] = $_ITEMS[$c]['ap'];
  $itm = $db->record;
  echo "<tr id='".$itm['id']."' refap='".$itm['ap']."'>";
  echo "<td align='center'>".$itm['code_str']."</td>";
  echo "<td>".$itm['name']."</td>";
  for($i=0; $i < count($_STORES); $i++)
   echo "<td align='center' storeid='".$_STORES[$i]['id']."'>".$itm['store_'.$_STORES[$i]['id'].'_qty']."</td>";
  echo "<td align='center'>".$itm['booked']."</td>";
  echo "<td align='center'>".$itm['incoming']."</td></tr>";

 }
 ?>
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
 tb = new GMUTable(document.getElementById('itemstable'), {autoresize:false, autoaddrows:false});
}

function OnFormSubmit()
{
 var tmp = document.getElementById('itemstable');
 if(tmp.rows.length < 2)
  return gframe_close();

 var cmd = "";
 var ret = new Array();

 for(var c=1; c < tmp.rows.length; c++)
 {
  if(tmp.rows[c].cells[0].colSpan > 1)
   continue;
  var r = tmp.rows[c];

  var a = new Array();
  a['ap'] = r.getAttribute('refap');
  a['id'] = r.id;
  a['tot_qty'] = 0;

  for(var i=2; i < r.cells.length-2; i++)
  {
   var sid = r.cells[i].getAttribute('storeid');
   var qty = parseFloat(r.cells[i].innerHTML);
   cmd+= " && store update-qty -store '"+sid+"' -qty '"+qty+"' -ap '"+r.getAttribute('refap')+"' -id '"+r.id+"'";
   a['store_'+sid+'_qty'] = qty;
   a['tot_qty']+= qty;
  }

  cmd+= " && dynarc edit-item -ap '"+a['ap']+"' -id '"+a['id']+"' -set `booked='"+parseFloat(r.cells[r.cells.length-2].innerHTML)+"',incoming='"+parseFloat(r.cells[r.cells.length-1].innerHTML)+"'`";

  ret.push(a);
 }
 

 var sh = new GShell();
 sh.showProcessMessage("Aggiornamento dati", "Attendere prego, &egrave; in corso l&lsquo;aggiornamento dei magazzini");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(o,a){
	 this.hideProcessMessage();
	 gframe_close(o,ret);
	}
 sh.sendCommand(cmd.substr(4));
}

</script>
</body></html>
<?php

