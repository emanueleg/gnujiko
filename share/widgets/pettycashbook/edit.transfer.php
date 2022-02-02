<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-12-2012
 #PACKAGE: pettycashbook
 #DESCRIPTION: Edit transfer form for Petty Cash Book.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$archivePrefix = $_REQUEST['ap'] ? $_REQUEST['ap'] : "pettycashbook";

$ret = GShell("dynarc item-info -ap `".$archivePrefix."` -id `".$_REQUEST['id']."` -extget pettycashbook",$_REQUEST['sessid'],$_REQUEST['shellid']);
$itemInfo = $ret['outarr'];

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Edit Transfer</title>
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
</style>
</head><body>

<?php

$form = new GForm("Modifica giroconto", "MB_OK|MB_ABORT", "simpleform", "default", "orange", 420, 260);
$form->Begin($_ABSOLUTE_URL."share/widgets/pettycashbook/img/transfers.png");
echo "<div id='contents' style='padding:5px'>";
?>
<table class='table' width='100%' border='0' cellspacing='5' cellpadding='0'>
<tr><td width='80' align='right'><b>Data:</b></td>
	<td><input type='text' class='text' style='width:80px' id='date' value="<?php echo date('d/m/Y',$itemInfo['ctime']); ?>"/></td>
	<td width='160'><b>Importo:</b> <input type='text' class='text' style='width:80px' id='amount' value="<?php echo number_format($itemInfo['incomes'],2,',','.'); ?>"/> <b>&euro;</b></td></tr>
<tr><td align='right'><b>Risorsa prelievo:</b></td>
	<td colspan='2'><select id='resourceout' style='width:180px'><?php
	$ret = GShell("cashresources list",$_REQUEST['sessid'], $_REQUEST['shellid']);
	$list = $ret['outarr'];
	for($c=0; $c < count($list); $c++)
	 echo "<option value='".$list[$c]['id']."'".($itemInfo['res_out']['id'] == $list[$c]['id'] ? " selected='selected'>" : ">").$list[$c]['name']."</option>";
	?></select> <a href='#'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/pettycashbook/img/edit.gif" border="0" onclick="cashResourcesConfig()"/></a></td></tr>
<tr><td align='right'><b>Risorsa deposito:</b></td>
	<td colspan='2'><select id='resourcein' style='width:180px'><?php
	for($c=0; $c < count($list); $c++)
	 echo "<option value='".$list[$c]['id']."'".($itemInfo['res_in']['id'] == $list[$c]['id'] ? " selected='selected'>" : ">").$list[$c]['name']."</option>";
	?></select> <a href='#'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/pettycashbook/img/edit.gif" border="0" onclick="cashResourcesConfig()"/></a></td></tr>
<tr><td align='right'><b>Descrizione:</b></td><td  colspan='2'><input type='text' id='description' class='text' style='width:260px' value="<?php echo $itemInfo['name']; ?>"/></td></tr>
</table>

<?php
echo "</div>";
$form->End();
?>

<script>
function bodyOnLoad()
{
}

function OnFormSubmit()
{
 var date = document.getElementById('date').value;
 if(!date)
  return alert("Devi specificare una data valida");
 date = strdatetime_to_iso(date);

 var amount = document.getElementById('amount').value;
 if(!amount)
  return alert("Devi specificare l'importo");
 amount = parseCurrency(amount);

 var resIn = document.getElementById('resourcein').value;
 var resOut = document.getElementById('resourceout').value;

 var description = document.getElementById('description').value;

 var sh = new GShell();
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 sh.sendCommand("dynarc edit-item -ap `<?php echo $archivePrefix; ?>` -id `<?php echo $itemInfo['id']; ?>` -ctime `"+date+"` -name `"+description+"` -extset `pettycashbook.resin='"+resIn+"',resout='"+resOut+"',in='"+amount+"',out='"+amount+"'`");
}

function cashResourcesConfig()
{
 var sh = new GShell();
 sh.OnOutput = function(){
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){
		 var sel1 = document.getElementById('resourceout');
		 var sel2 = document.getElementById('resourcein');
		 while(sel1.options.length)
		  sel1.removeChild(sel1.options[0]);
		 while(sel2.options.length)
		  sel2.removeChild(sel2.options[0]);
		 if(!a) return;
		 for(var c=0; c < a.length; c++)
		 {
		  var opt1 = document.createElement('OPTION');
		  opt1.value = a[c]['id'];
		  opt1.innerHTML = a[c]['name'];
		  sel1.appendChild(opt1);
		  var opt2 = document.createElement('OPTION');
		  opt2.value = a[c]['id'];
		  opt2.innerHTML = a[c]['name'];
		  sel2.appendChild(opt2);
		 }
		}
	 sh2.sendCommand("cashresources list");
	}
 sh.sendSudoCommand("gframe -f config.companyprofile -params `show=cashresources`");
}
</script>
</body></html>
<?php

