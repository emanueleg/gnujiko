<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 09-09-2013
 #PACKAGE: pos-module
 #DESCRIPTION: POS Module - Calculates rest
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$amount = $_REQUEST['amount'] ? $_REQUEST['amount'] : 0;

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Demo 1</title>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<?php
include_once($_BASE_PATH."var/templates/standardwidget/index.php");
include_once($_BASE_PATH."include/js/gshell.php");
?>
</head><body>

<div class='standardwidget' style='width:360px;height:200px'>
 <h2>Conferma ordine</h2>
 <hr/>
 <p>
 <table width="300" border="0">
 <tr><td>Totale: </td><td align='right'><span id='amount'><?php echo number_format($amount,2,",","."); ?></span> &euro;</td></tr>
 <tr><td>Contanti: </td><td align='right'><input type='text' class='edit' id='cash' style='width:60px' onchange='calcRest(this)'/>&euro;</td></tr>
 <tr><td colspan='2'><hr/></td></tr>
 <tr><td>Resto: </td><td align='right'><span id='therest' style='font-weight:bold;'>0,00</span> &euro;</td></tr>
 </table>
 </p>
 <hr/>
 <input type='button' class='button-blue' value='Conferma' onclick='submit()'/> 
 <input type='button' class='button-red' value="Annulla operazione" onclick='abort()'/>
</div>

<script>

function bodyOnLoad()
{
 window.setTimeout(function(){document.getElementById('cash').focus();}, 500);
}

function abort()
{
 if(!confirm("Sei sicuro di voler annullare l'operazione?"))
  return;
 gframe_close();
}

function calcRest(ed)
{
 var amount = parseCurrency(document.getElementById('amount').innerHTML);
 var cash = ed.value ? parseCurrency(ed.value) : 0;

 if(cash < amount)
  return ed.className = "edit-error";
 else
  ed.className = "edit";
 
 document.getElementById('therest').innerHTML = formatCurrency(cash-amount);
}

function submit()
{
 gframe_close("done!",1);
}
</script>

</body></html>

