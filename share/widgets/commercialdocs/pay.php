<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-08-2013
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Pay form for GCommercialDocs.
 #VERSION: 2.3beta
 #CHANGELOG: 13-08-2013 : Bug fix nei pagamenti che non teneva conto delle ritenute.
			 12-08-2013 : Aggiunta la categoria
			 13-01-2013 : Bug fix in assign group at every new items.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_CAT_TAG;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

if($_REQUEST['id'])
{
 $ret = GShell("dynarc item-info -ap `commercialdocs` -id `".$_REQUEST['id']."` -extget `cdinfo,mmr`",$_REQUEST['sessid'],$_REQUEST['shellid']);
 $docInfo = $ret['outarr'];

 // detect doc type //
 $ret = GShell("dynarc cat-info -ap `commercialdocs` -id `".$docInfo['cat_id']."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
 $_CAT_TAG = $ret['outarr']['tag'];
}

if(!isset($_REQUEST['isdebit']))
{
 /* detect if is a debit */
 switch(strtolower($_CAT_TAG))
 {
  case 'agentinvoices' : case 'vendororders' : case 'purchaseinvoices' : case 'creditsnote' : $_REQUEST['isdebit']=true; break;
 }
}

if(!$_REQUEST['amount'])
{
 $_REQUEST['amount'] = $docInfo['tot_netpay'];
 /* detect how to pay */
 for($c=0; $c < count($docInfo['mmr']); $c++)
 {
  $item = $docInfo['mmr'][$c];
  if($item['payment_date'] != "0000-00-00")
  {
   if($_REQUEST['isdebit'])
	$_REQUEST['amount']-= $item['expenses'];
   else
    $_REQUEST['amount']-= $item['incomes'];
  }
 }
}

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Pay</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."var/objects/editsearch/index.php");
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

h4 {
	font-family: Arial, sans-serif;
	font-size: 14px;
	color: #3364C3;
	margin-top: 5px;
}
</style>
</head><body>

<?php

$form = new GForm("Salda", "MB_OK|MB_ABORT", "simpleform", "default", "orange", 460, 400);
$form->Begin($_ABSOLUTE_URL."share/widgets/commercialdocs/img/pay-icon.png");
echo "<div id='contents' style='padding:5px'>";
?>
<h4>Specifica la modalit&agrave; con cui viene effettuato il saldo.</h4>
<table class='table' width='100%' border='0' cellspacing='5' cellpadding='0'>
<tr><td width='80' align='right'><b>Data:</b></td>
	<td><input type='text' class='text' style='width:80px' id='date' value="<?php echo date('d/m/Y'); ?>"/></td>
	<td width='160'><b>Importo:</b> <input type='text' class='text' style='width:80px' id='amount' value="<?php echo number_format($_REQUEST['amount'],2,',','.'); ?>"/> <b>&euro;</b></td></tr>
<tr><td align='right'><b>Risorsa:</b></td>
	<td colspan='2'><select id='resource' style='width:180px'><?php
	$ret = GShell("cashresources list",$_REQUEST['sessid'], $_REQUEST['shellid']);
	$list = $ret['outarr'];
	for($c=0; $c < count($list); $c++)
	 echo "<option value='".$list[$c]['id']."'>".$list[$c]['name']."</option>";
	?></select> <a href='#'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/img/edit.gif" border="0" onclick="cashResourcesConfig()"/></a></td></tr>
<tr><td colspan='3'>&nbsp;</td></tr>
<tr><td align='right'><b>Soggetto:</b></td>
	<td colspan='2' id='subject'><?php echo html_entity_decode($docInfo['subject_name'],ENT_QUOTES,'UTF-8'); ?></td></tr>
<tr><td align='right'><b>Rif. doc.:</b></td>
	<td colspan='2'><?php echo $docInfo['name']; ?></td></tr>
<tr><td align='right'><b>Descrizione:</b></td><td colspan='2'><input type='text' id='description' class='text' style='width:260px' value="<?php echo $_REQUEST['desc']; ?>"/></td></tr>
<tr><td align='right'><b>Categoria:</b></td><td colspan='2'><input type='text' id='category' class='text' style='width:160px'/> <a href='#' onclick='selectCategory()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/img/edit.gif" border="0"/></a></td></tr>
</table>
<p><br/><i>Questo movimento contabile verr&agrave; registrato automaticamente nel registro della Prima Nota.</i></p>

<?php
echo "</div>";
$form->End();
?>

<script>
var IS_DEBIT = <?php echo $_REQUEST['isdebit'] ? "true" : "false"; ?>;
function bodyOnLoad()
{
 var cE = EditSearch.init(document.getElementById('category'),
	"dynarc cat-find -ap `pettycashbook` -field name `","` -limit 5 --order-by 'name ASC'","id","name",null,true,"name");

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

 var resId = document.getElementById('resource').value;

 var subjectId = "<?php echo $docInfo['subject_id']; ?>";
 var subjectName = document.getElementById('subject').innerHTML;

 var description = document.getElementById('description').value;
 var catId = document.getElementById('category').data ? document.getElementById('category').data['id'] : document.getElementById('category').getAttribute('catid');

 var docAp = "commercialdocs";
 var docId = "<?php echo $docInfo['id']; ?>";

 var sh = new GShell();
 sh.OnFinish = function(o,a){gframe_close(o,a);}
 if(IS_DEBIT)
  sh.sendCommand("dynarc new-item -ap `pettycashbook`"+(catId ? " -cat `"+catId+"`" : "")+" -group pettycashbook -ctime `"+date+"` -name `"+description+"` -extset `pettycashbook.resout='"+resId+"',out='"+amount+"',docap='"+docAp+"',docid='"+docId+"',"+(subjectId ? "subjectid='"+subjectId+"'" : "subject='''"+subjectName+"'''")+"` && dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status=10,payment-date='"+date+"',mmr.expenses='"+amount+"',payment='"+date+"',description='Saldo',subject='''"+subjectName+"''',subjectid='"+subjectId+"'`");
 else
  sh.sendCommand("dynarc new-item -ap `pettycashbook`"+(catId ? " -cat `"+catId+"`" : "")+" -group pettycashbook -ctime `"+date+"` -name `"+description+"` -extset `pettycashbook.resin='"+resId+"',in='"+amount+"',docap='"+docAp+"',docid='"+docId+"',"+(subjectId ? "subjectid='"+subjectId+"'" : "subject='''"+subjectName+"'''")+"` && dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status=10,payment-date='"+date+"',mmr.incomes='"+amount+"',payment='"+date+"',description='Saldo',subject='''"+subjectName+"''',subjectid='"+subjectId+"'`");
}

function cashResourcesConfig()
{
 var sh = new GShell();
 sh.OnOutput = function(){
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){
		 var sel = document.getElementById('resource');
		 while(sel.options.length)
		  sel.removeChild(sel.options[0]);
		 if(!a) return;
		 for(var c=0; c < a.length; c++)
		 {
		  var opt = document.createElement('OPTION');
		  opt.value = a[c]['id'];
		  opt.innerHTML = a[c]['name'];
		  sel.appendChild(opt);
		 }
		}
	 sh2.sendCommand("cashresources list");
	}
 sh.sendSudoCommand("gframe -f config.companyprofile -params `show=cashresources`");
}

function selectCategory()
{
 var sh = new GShell();
 sh.OnOutput = function(o,catId){
	 if(!catId) return;
	 document.getElementById('category').setAttribute('catid',catId);
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){document.getElementById('category').value = a['name'];}
	 sh2.sendCommand("dynarc cat-info -ap `pettycashbook` -id `"+catId+"`");
	}
 sh.sendCommand("gframe -f dynarc.categorySelect -params `ap=pettycashbook`");
}

</script>
</body></html>
<?php

