<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 29-11-2012
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
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Delete movements</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."var/objects/gmutable/index.php");

$_IDS = array();
if($_REQUEST['ids'])
 $_IDS = explode(",",$_REQUEST['ids']);

?>
</head><body>

<style type='text/css'>
table.footertable td {
	font-size: 14px;
	color: #333333;
}

table.movements {
	width: 540px;
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

table.movements td em {float: left;}

table.movements tr.row0 td {background: #ffffcc;}
table.movements tr.row1 td {background: #ffff99;}

table.movements tr.selected td {background: #fafade;}

table#itemstable td {background: #fafade;}

</style>

<?php

$form = new GForm("Elimina movimenti", "MB_OK|MB_ABORT", "simpleform", "default", "orange", 600, 380);
$form->Begin($_ABSOLUTE_URL."share/widgets/gstore/img/warning.png");
echo "<div id='contents'>";

?>
<h3 style='color:#f31903;'>Attenzione! Verranno rimossi i seguenti movimenti di magazzino.</h3>
<div class="gmutable" style="width:560px;height:180px;background:#ffffff;border:0px;">
 <table id="itemstable" class="movements" cellspacing="2" cellpadding="2" border="0">
 <tr><th width='60'>DATA</th>
	 <th width='40'>ORA</th>
	 <th width='70'>OPERAZIONE</th>
	 <th width='60'>CODICE</th>
	 <th style='text-align:left;'>ARTICOLO</th>
	 <th width='50'>QTA&lsquo;</th>
 </tr>
 <?php
 $db = new AlpaDatabase();
 for($c=0; $c < count($_IDS); $c++)
 {
  $db->RunQuery("SELECT * FROM store_movements WHERE id='".$_IDS[$c]."'");
  $db->Read();
  echo "<tr id='".$db->record['id']."'><td>".date('d/m/Y',strtotime($db->record['op_time']))."</td>";
  echo "<td align='center'>".date('H:i',strtotime($db->record['op_time']))."</td>";
  echo "<td align='center'>";
  switch($db->record['mov_act'])
  {
   case 1 : echo "<span class='blue'>CARICO</span>"; break;
   case 2 : echo "<span class='green'>SCARICO</span>"; break;
   case 3 : echo "<span class='darkblue'>MOVIMENTA</span>"; break;
  }
  echo "</td>";
  echo "<td align='center'>".$db->record['ref_code']."</td>";
  echo "<td>".$db->record['ref_name']."</td>";
  echo "<td align='center'><span class='gray'><b>".$db->record['qty']."</b></span></td></tr>";
 }
 ?>
 </table>
</div>

<div style="height:34px;border-top:1px solid #dadada;padding:10px">
<input type='radio' name='storeqty' checked='true'/>Ripristina giacenze</input> 
<input type='radio' name='storeqty' id='norestoreqty'/>Non ripristinare le giacenze</input>
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

 var q = "";
 for(var c=1; c < tmp.rows.length; c++)
  q+= " -id "+tmp.rows[c].id;
 
 if(document.getElementById('norestoreqty').checked)
  q+= " --no-restore-qty";

 var sh = new GShell();
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 sh.sendCommand("store delete-movement"+q);
}

</script>
</body></html>
<?php

