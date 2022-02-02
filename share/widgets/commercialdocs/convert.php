<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 25-02-2016
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Convert document.
 #VERSION: 2.1beta
 #CHANGELOG: 25-02-2016 : Aggiunta possibilita di selezionare la numerazione
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
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Convert document</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
?>
<style type='text/css'>
h4.blue {
	font-family: Arial, sans-serif;
	font-size: 18px;
	color: #013397;
	margin-bottom: 30px;
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

input.buttonblue {
 height: 29px;
 border: 1px solid #3079ed;
 background: #4c8efa;
 padding-left: 16px;
 padding-right: 16px;
 font-family: arial, sans-serif;
 font-size: 12px;
 font-weight: bold;
 color: #ffffff;
}

small {
 font-family: arial,sans-serif;
 font-size: 10px;
 color: #333;
}

div.checkboxes {
 width: 280px;
 height: 100px;
}

div.checkbox {
 width: 120px;
 height: 20px;
 margin: 3px;
 white-space: nowrap;
 float: left;
 font-family: arial, sans-serif;
 font-size: 12px;
}

input.edit {
	height: 22px;
	border: 1px solid #d8d8d8;
	font-family: arial, sans-serif;
	font-size: 12px;
	background: #ffffff;
	color: #222;
	padding-left: 8px;
	padding-right: 8px;
}

input.edit:focus {
	outline: none;
	border: 1px solid #4486fa;
}

hr {
 height: 1px;
 border: 0px;
 background: #dadada;
}
</style>
</head><body>

<?php

$singW = array("tivi","visi","ture","Note","dini","tini","vute","Soci","Fiscali");
$singR = array("tivo","viso","tura","Nota","dine","tino","vuta","Socio","Fiscale");

$inherit = array(
 0 => array('name'=>'subject', 'title'=>'Intestatario', 'checked'=>true),
 1 => array('name'=>'shipping', 'title'=>'Dest. merci', 'checked'=>true),
 2 => array('name'=>'reference', 'title'=>'Contatto di rif.', 'checked'=>true),
 3 => array('name'=>'agent', 'title'=>'Agente di rif.', 'checked'=>true),
 4 => array('name'=>'intdocref', 'title'=>'Doc. rif. interno', 'checked'=>true),
 5 => array('name'=>'paymentmode', 'title'=>'Modalit&agrave; di pagam.', 'checked'=>true),
 6 => array('name'=>'description', 'title'=>'Annotazioni', 'checked'=>true),
 7 => array('name'=>'attachments', 'title'=>'Allegati', 'checked'=>true),
 8 => array('name'=>'expenses', 'title'=>'Spese', 'checked'=>true),
 9 => array('name'=>'discrebatestamp', 'title'=>'Sconti,bolli,abbuoni', 'checked'=>true)
);


$form = new GForm("Converti documento", "MB_ABORT", "simpleform", "default", "orange", 640, 480);
$form->Begin($_ABSOLUTE_URL."share/widgets/commercialdocs/img/doc32.png");
echo "<div id='contents' style='padding:5px;'>";
?>
<table width="100%" border="0">
<tr><td valign='top'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/commercialdocs/img/doc128.png"/></td>
	<td valign='top'><h4 class='blue'><?php echo $docInfo['name']; ?></h4>
	<p>&nbsp;&nbsp;&nbsp;Converti in: <select id='doctype' onchange='doctypeChanged(this)'><?php
		 $ret = GShell("dynarc cat-list -ap commercialdocs --order-by 'name ASC'",$_REQUEST['sessid'], $_REQUEST['shellid']);
		 $list = $ret['outarr'];
		 $selectedCat = null;
		 for($c=0; $c < count($list); $c++)
		 {
		  if($docInfo['cat_id'] == $list[$c]['id'])
		   continue;
		  if(!$selectedCat) $selectedCat = $list[$c];
		  echo "<option value='".$list[$c]['tag']."'>".str_replace($singW, $singR, $list[$c]['name'])."</option>";
		 }
		?></select>
	</p>
	<?php
	$subcatList = array();
	$ret = GShell("dynarc cat-list -ap commercialdocs -parent '".$selectedCat['id']."'", $_REQUEST['sessid'], $_REQUEST['shellid']);
	if(!$ret['error'] && count($ret['outarr']))
	 $subcatList = $ret['outarr'];
	?>
	<p>
	 &nbsp;&nbsp;&nbsp;Numerazione: <select id='doctypecat' style='width:170px'>
		<option value='0'>predefinita</option>
		<?php
		 for($c=0; $c < count($subcatList); $c++)
		  echo "<option value='".$subcatList[$c]['id']."'>".$subcatList[$c]['name']."</option>";
		?>
	</p>

	<br/>
	<br/>
	<small>Seleziona eventuali campi da mantenere: </small>
	<table width='400' border='0' style="border-top:1px solid #d8d8d8;margin-top:5px">
	<tr><td valign='middle'>
		 <div class='checkboxes' id='checkboxes'>
		  <?php
		   for($c=0; $c < count($inherit); $c++)
		   {
			echo "<div class='checkbox'><input type='checkbox' id='inherit-".$inherit[$c]['name']."'"
				.($inherit[$c]['checked'] ? " checked='true'/>" : "/>").$inherit[$c]['title']."</div>";
		   }
		  ?>
		 </div>
		</td>
		<td valign='middle' width='180'><input type='button' class='buttonblue' value="Converti &raquo;" onclick="convertSubmit()"/></td>
	</tr>
	</table>
	</td></tr>
</table>
<hr/>
Opzioni:<br/>
<input type='checkbox' id='insertref'/>Inserisci dicitura: <input type='text' class='edit' id='reftitle' style='width:200px' value="Rif: <?php echo $docInfo['name']; ?>"/> sul doc. generato.<br/>
<input type='checkbox' id='internaldocref' onchange="check('inherit-intdocref',this, false)"/>Imposta <?php echo $docInfo['name']; ?> come documento di riferimento interno.
<?php
echo "</div>";
$form->End();
?>

<script>
var DOC_ID = "<?php echo $docInfo['id']; ?>";

function convertSubmit()
{
 var docType = document.getElementById('doctype').value;
 var destCatId = document.getElementById('doctypecat').value;
 if(destCatId && (destCatId == "0"))
  destCatId = 0;

 var status = 0;
 var inherit = "";
 var div = document.getElementById('checkboxes');
 var list = div.getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
 {
  var cb = list[c].getElementsByTagName('INPUT')[0];
  var cbid = cb.id.substr(8);
  if(cb.checked == true)
   inherit+= ","+cbid;
 }
 if(inherit)
  inherit = inherit.substr(1);

 if(!confirm("Sei sicuro di voler convertire questo documento?"))
  return;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 var cmd = "commercialdocs convert -id `"+DOC_ID+"` -type `"+docType+"`";
 if(destCatId)	cmd+= " -cat '"+destCatId+"'"; 
 if(status) 	cmd+= " -status '"+status+"'";
 if(inherit) 	cmd+= " -inherit `"+inherit+"`";
 if((document.getElementById('insertref').checked == true) && (document.getElementById('reftitle').value != ""))
  cmd+= " -docreftitle `"+document.getElementById('reftitle').value+"`";
 if(document.getElementById('internaldocref').checked == true)
  cmd+= " -docrefid `"+DOC_ID+"`";
 sh.sendCommand(cmd);
}

function check(cbid, cb, bool)
{
 document.getElementById(cbid).checked = bool ? cb.checked : !cb.checked;
}

function doctypeChanged(sel)
{
 var subsel = document.getElementById('doctypecat');
 while(subsel.options.length > 1)
  subsel.remove(1);

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a || !a.length)
	  return;
	 for(var c=0; c < a.length; c++)
	 {
	  var opt = document.createElement('OPTION');
	  opt.value = a[c]['id'];
	  opt.innerHTML = a[c]['name'];
	  subsel.add(opt);
	 }
	}

 sh.sendCommand("dynarc cat-list -ap commercialdocs -pt '"+sel.value+"'");
}
</script>
</body></html>
<?php

