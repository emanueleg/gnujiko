<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-12-2012
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Unlock locked document.
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
 $ret = GShell("dynarc item-info -ap `commercialdocs` -id `".$_REQUEST['id']."` -extget `cdinfo`");
 $docInfo = $ret['outarr'];
}

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Unlock document</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
?>
<style type='text/css'>
h4.red {
	font-family: Arial, sans-serif;
	font-size: 18px;
	color: #980101;
	margin-bottom: 10px;
	margin-left: 3px;
}

p {
	font-family: Arial, sans-serif;
	font-size: 13px;
	color: #333333;
}

span.blue {
	font-family: Arial, sans-serif;
	font-size: 12px;
	color: #013397;
}
</style>
</head><body>

<?php

$form = new GForm("Documento bloccato", "MB_ABORT", "simpleform", "default", "orange", 640, 400);
$form->Begin($_ABSOLUTE_URL."share/widgets/commercialdocs/img/unlock.png");
echo "<div id='contents' style='padding:5px;'>";
?>
<table width="100%" border="0">
<tr><td valign='top'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/img/locked.png"/></td>
	<td valign='top'><h4 class='red'>Questo documento &egrave; bloccato!</h4>
	<p>Quando un documento viene convertito o raggruppato il sistema blocca automaticamente il documento di origine per prevenire inconguenze e doppie voci nei registri contabili come la Prima Nota ed il Registro IVA.</p>
	<p>E&lsquo; sconsigliato sbloccare documenti come gli <b>ordini</b> e i <b>D.D.T.</b> perch&egrave; non avendo pi&ugrave; riferimenti ai documenti convertiti il sistema automaticamente ri-conteggia quei documenti alterando cos&igrave; i dati nei registri contabili.</p>
	<br/>
	<br/>
	<table width='90%' border='0'>
	<tr><td valign='middle' width='180'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/img/unlock-btn.png" style="cursor:pointer;" onclick="unlockSubmit()"/></td>
		<td valign='middle'><span class='blue'>Se desideri sbloccare questo documento ricordati di controllare che il Registro IVA ed il registro della Prima Nota siano apposto.</span></td></tr>
	</table>
	</td></tr>
</table>

<?php
echo "</div>";
$form->End();
?>

<script>
function unlockSubmit()
{
 if(!confirm("Sei sicuro di voler sbloccare questo documento?"))
  return;

 var sh = new GShell();
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 sh.sendCommand("dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status=0`");
}

</script>
</body></html>
<?php

