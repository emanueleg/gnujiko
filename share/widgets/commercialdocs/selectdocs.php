<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 08-03-2016
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Select documents
 #VERSION: 2.1beta
 #CHANGELOG: 08-03-2016 : Aggiornamenti vari.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
$_AP = (isset($_REQUEST['ap']) && $_REQUEST['ap']) ? $_REQUEST['ap'] : "commercialdocs";
$_IDS = $_REQUEST['ids'];
$_DESTID = isset($_REQUEST['destid']) ? $_REQUEST['destid'] : 0;

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Select documents</title>
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

$form = new GForm($_REQUEST['title'], "MB_OK|MB_ABORT", "simpleform", "default", "orange", 640, 480);
$form->Begin($_ABSOLUTE_URL."share/widgets/commercialdocs/img/checkavail.gif");
echo "<div id='contents' style='padding:5px;'>";
?>
<p style="font-family:Arial,sans-serif;font-size:13px;color:#f31903"><b><?php echo $_REQUEST['contents']; ?></b></p>

<div style="width:590;margin-top:20px">
<table class='checkavailtable' id='checkavailtable' width='570' cellspacing='0' cellpadding='0' border='0'>
<tr><th width='32' align='center'><input type='checkbox' onchange='checkAll(this)' checked='true'/></th>
	<th width='60'>DATA</th>
	<th style='text-align:left'>DOCUMENTO</th>
	<th width='60' align='center'>IMPORTO</th>
</tr>
<?php
$x = explode(",",$_IDS);
for($c=0; $c < count($x); $c++)
{
 $id = $x[$c];

 $ret = GShell("dynarc item-info -ap commercialdocs -id '".$id."' -extget `cdinfo`",$_REQUEST['sessid'], $_REQUEST['shellid']);
 if($ret['error']) continue;
 $docInfo = $ret['outarr'];

 echo "<tr id='".$id."'><td align='center'><input type='checkbox' checked='true'/></td>";
 echo "<td>".date('d/m/Y',$docInfo['ctime'])."</td>";
 echo "<td>".($docInfo['ext_docref'] ? $docInfo['ext_docref'] : $docInfo['name'])."</td>";
 echo "<td align='right'>".number_format($docInfo['tot_netpay'],2,',','.')."</td></tr>";
}
?>
</table></div>
<br/>
<?php
echo "</div>";
$form->End();
?>

<script>
var AP = "<?php echo $_AP; ?>";
var DEST_ID = <?php echo $_DESTID ? $_DESTID : '0'; ?>;

function bodyOnLoad()
{
}

function OnFormSubmit()
{
 var sel = new Array();
 var tb = document.getElementById('checkavailtable');
 for(var c=1; c < tb.rows.length; c++)
 {
  var cb = tb.rows[c].cells[0].getElementsByTagName('INPUT')[0];
  if(cb.checked == true)
   sel.push(tb.rows[c].id);
 }
 if(!sel.length)
 {
  alert("Nessun documento selezionato");
  return;
 }

 if(DEST_ID)
 {
  var q = "";
  for(var c=0; c < sel.length; c++)
   q+= " -id '"+sel[c]+"'";

  var sh = new GShell();
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,a){gframe_close(o,a);}

  sh.sendCommand("commercialdocs ddtin-group -destid '<?php echo $_DESTID; ?>'"+q);
 }
 else
  gframe_close(sel.length+" document selected", sel);
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

