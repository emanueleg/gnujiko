<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-12-2012
 #PACKAGE: pettycashbook
 #DESCRIPTION: New debit form for Petty Cash Book.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>New Debit</title>
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
</style>
</head><body>

<?php

$form = new GForm("Nuova uscita", "MB_OK|MB_ABORT", "simpleform", "default", "orange", 420, 320);
$form->Begin($_ABSOLUTE_URL."share/widgets/pettycashbook/img/expenses.png");
echo "<div id='contents' style='padding:5px'>";
?>
<table class='table' width='100%' border='0' cellspacing='5' cellpadding='0'>
<tr><td width='80' align='right'><b>Data:</b></td>
	<td><input type='text' class='text' style='width:80px' id='date' value="<?php echo date('d/m/Y'); ?>"/></td>
	<td width='160'><b>Importo:</b> <input type='text' class='text' style='width:80px' id='amount'/> <b>&euro;</b></td></tr>
<tr><td align='right'><b>Risorsa:</b></td>
	<td colspan='2'><select id='resource' style='width:180px'><?php
	$ret = GShell("cashresources list",$_REQUEST['sessid'], $_REQUEST['shellid']);
	$list = $ret['outarr'];
	for($c=0; $c < count($list); $c++)
	 echo "<option value='".$list[$c]['id']."'>".$list[$c]['name']."</option>";
	?></select> <a href='#'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/pettycashbook/img/edit.gif" border="0" onclick="cashResourcesConfig()"/></a></td></tr>
<tr><td align='right'><b>Categoria:</b></td>
	<td colspan='2'><input type='text' id='category' class='text' style='width:200px'/> <a href='#' onclick='selectCategory()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/pettycashbook/img/edit.gif" border="0"/></a></td></tr>
<tr><td align='right'><b>Soggetto:</b></td><td colspan='2'><input type='text' id='subject' class='text' style='width:200px'/></td></tr>
<tr><td align='right'><b>Descrizione:</b></td><td  colspan='2'><input type='text' id='description' class='text' style='width:260px'/></td></tr>
<tr><td align='right'><b>Doc. di riferimento:</b></td><td  colspan='2'><input type='text' id='docref' class='text' style='width:260px'/></td></tr>
</table>

<?php
echo "</div>";
$form->End();
?>

<script>
function bodyOnLoad()
{
 var mE = EditSearch.init(document.getElementById('subject'),
	"dynarc search -ap `rubrica` -fields code_str,name `","` -limit 5 --order-by 'code_str,name ASC'","id","name","items",true,"name");
 var dE = EditSearch.init(document.getElementById('docref'),
	"dynarc search -ap `commercialdocs` -fields name `","` -limit 5 --order-by 'name ASC'","id","name","items",true,"name");
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
 var catId = document.getElementById('category').data ? document.getElementById('category').data['id'] : document.getElementById('category').getAttribute('catid');

 var subjectId = 0;
 var subjectName = "";
 var subject = document.getElementById('subject');
 if(subject.value && subject.data)
 {
  subjectId = subject.data['id'];
  subjectName = subject.data['name'];
 }
 else
  subjectName = subject.value;

 var description = document.getElementById('description').value;

 var docAp = "";
 var docId = 0;
 var docRef = "";

 var doc = document.getElementById('docref');
 if(doc.value && doc.data)
 {
  docAp = doc.data['tb_prefix'];
  docId = doc.data['id'];
 }
 else
  docRef = doc.value;

 var sh = new GShell();
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 sh.sendCommand("dynarc new-item -ap `pettycashbook` -ctime `"+date+"`"+(catId ? " -cat `"+catId+"`" : "")+" -name `"+description+"` -extset `pettycashbook.resout='"+resId+"',out='"+amount+"',docap='"+docAp+"',docid='"+docId+"',docref='"+docRef+"',"+(subjectId ? "subjectid='"+subjectId+"'" : "subject='''"+subjectName+"'''")+"`");
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
</script>
</body></html>
<?php

