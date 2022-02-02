<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 25-06-2012
 #PACKAGE: gmart
 #DESCRIPTION: Edit clipboard form.
 #VERSION: 2.0
 #CHANGELOG: 
 #DEPENDS: 
 #TODO:
 
*/

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Append clipboard elements to document.</title>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>"; var USER_HOME = "<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR']; ?>/";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/edit-clipboard.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/clipboard-appendtodoc.css" type="text/css" />
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include($_BASE_PATH."var/objects/htmlgutility/menu.php");
include_once($_BASE_PATH."var/objects/dynrubricaedit/index.php");
?>
</head><body>

<table width="800" height="520" cellspacing="0" cellpadding="0" border="0" class="edit-clipboard-form">
<tr><td class="header-left" width='250'><span style="margin-left:20px;">Riferimento appunti:</span></td>
	<td class="header-top"><div class="title" id="title-outer"><span id="title" onclick="rename()"><?php echo html_entity_decode(stripslashes($cbInfo['name']),ENT_QUOTES,'UTF-8'); ?></span></div></td>
	<td class="header-right"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/widget-close.png" onclick="gframe_close()" class="close-btn"/></td></tr>

<tr><td colspan="3" valign="top" class="contents">
	<div class="contents">
	<!-- CONTENTS -->
	<br/>
	<table width='420' align='center' border='0' class='form' cellspacing='0' cellpadding='0'>
	<tr><th><i>Inserisci il nome del cliente</i></th></tr>
	<tr><td class='customer'><span>Cliente</span> <input type='text' style='width:336px' id='subject' onchange='subjectChanged()'/></td></tr>

	<tr><td><hr/></td></tr>

	<tr><th><i>Scegli il tipo di documento dove inserire gli articoli di questo appunto.</i></th></tr>
	<tr><td valign='top'><span class='bluebtn' style='width:220px;'><span class='bluebtn-title' id="document-name">Preventivo</span>
		 <ul class="submenu" id="document-types">
	 	  <li onclick="selectDocType('preemptives')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/printmodels/preemptives.png"/>Preventivo</li>
	 	  <li onclick="selectDocType('orders')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/printmodels/orders.png"/>Ordine</li>
	 	  <li onclick="selectDocType('ddt')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/printmodels/ddt.png"/>D.D.T.</li>
	 	  <li onclick="selectDocType('vendororders')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/printmodels/vendororders.png"/>Ordine fornitore</li>
	 	  <li onclick="selectDocType('invoices')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/printmodels/invoices.png"/>Fattura</li>
		 </ul></span>
		 <div id='docid_choice' style='display:none;'>
		  <br/><input type='radio' name='includetype' id='docid_choice_new' checked='true'/><span id='docid_choice_newtit'>&nbsp;</span>
		  <br/><input type='radio' name='includetype' id='docid_choice_append'/><span id='docid_choice_apptit'>&nbsp;</span>
		 </div>
		</td></tr>

	<tr><td><hr/></td></tr>

	<tr><td><input type='checkbox' checked='true' id='include_clipboard_title'/>Includi il titolo degli appunti</td></tr>

	<tr><td><hr/></td></tr>

	<tr><td><i style='color:#666666;'>I prezzi degli articoli mostrati nella videata precedente si riferiscono al listino generale quindi in base al cliente e alla associazione cliente/listino i prezzi degli articoli possono variare. Premi il tasto &quot;Avanti&quot; per vedere il riepilogo con i prezzi corretti.</i></td></tr>

	</table>
	<!-- EOF - CONTENTS -->
	</div>
	</td></tr>

<tr><td class="footer-left" valign="top">
	 <ul class='basicbuttons' style="margin-left:15px;margin-top:4px;float:left;">
	  <li><span onclick='comeBack()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/back.png" border='0'/>Torna indietro</span></li>
	 </ul>
	&nbsp;
	</td>
	<td class="footer-right" colspan="2" valign="top">
	 <ul class='basicbuttons' style="float:right;margin-top:4px;margin-right:5px;">
	  <li><span onclick="showSummary()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/summary.gif" border='0'/> Avanti</span></li>
	 </ul>
	&nbsp;
	</td></tr>

</table>

<script>
var DECIMALS = <?php echo $_DECIMALS ? $_DECIMALS : "2"; ?>;
var RubricaEdit = null;
var DOC_TYPE = "preemptives";
var DOC_ID = 0;

function bodyOnLoad()
{
 new GPopupMenu(document.getElementById('document-name'), document.getElementById('document-types'));
 RubricaEdit = new DynRubricaEdit(document.getElementById('subject'));
}

function rename()
{
 var titO = document.getElementById('title');
 var nm = prompt("Rinomina appunto",titO.innerHTML);
 if(!nm)
  return;

 titO.innerHTML = nm;
}

function comeBack()
{
 history.back();
}

function showSummary()
{
 var title = document.getElementById('title').innerHTML;
 var includeCBTitle = (document.getElementById('include_clipboard_title').checked == true) ? true : false;
 var CUST_ID = 0;
 var CUST_NAME = RubricaEdit.value;

 if(RubricaEdit.data)
  CUST_ID = RubricaEdit.data['id'];

 if(document.getElementById('docid_choice_new').checked == true)
  DOC_ID=0;

 document.location.href=ABSOLUTE_URL+"share/widgets/gmart/clipboard-appendtodoc-summary.php?id=<?php echo $_REQUEST['id']; ?>&sessid=<?php echo $_REQUEST['sessid']; ?>&shellid=<?php echo $_REQUEST['shellid']; ?>&doctype="+DOC_TYPE+"&docid="+DOC_ID+(CUST_ID ? "&custid="+CUST_ID : "&custname="+CUST_NAME)+(includeCBTitle ? "&includecbtitle=true" : "");
}

function selectDocType(docTag)
{
 DOC_TYPE = docTag.toUpperCase();

 switch(docTag)
 {
  case 'preemptives' : document.getElementById('document-name').innerHTML = "Preventivo"; break;
  case 'orders' : document.getElementById('document-name').innerHTML = "Ordine"; break;
  case 'ddt' : document.getElementById('document-name').innerHTML = "D.D.T."; break;
  case 'vendororders' : document.getElementById('document-name').innerHTML = "Ordine fornitore"; break;
  case 'invoices' : document.getElementById('document-name').innerHTML = "Fattura"; break;
 }
 checkForOpenDocuments();
}

function subjectChanged()
{
 checkForOpenDocuments();
}

function checkForOpenDocuments()
{
 if(!RubricaEdit.value)
 {
  document.getElementById('docid_choice_new').checked = true;
  document.getElementById('docid_choice').style.display='none';
  DOC_ID = 0;
  return;
 }

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a['items'] || !a['items'].length)
	 {
	  document.getElementById('docid_choice_new').checked = true;
	  document.getElementById('docid_choice').style.display='none';
	  DOC_ID = 0;
	 }
	 else
	 {
	  var newTit = "";
	  var appTit = "";
	  switch(DOC_TYPE.toLowerCase())
	  {
	   case 'preemptives' : {newTit="Genera un nuovo preventivo"; appTit="Includi nell&lsquo;ultimo preventivo aperto";} break;
	   case 'orders' : {newTit="Genera un nuovo ordine"; appTit="Includi nell&lsquo;ultimo ordine aperto";} break;
	   case 'ddt' : {newTit="Genera un nuovo D.D.T."; appTit="Includi nell&lsquo;ultimo D.D.T. aperto";} break;
	   case 'invoices' : {newTit="Genera una nuova fattura"; appTit="Includi nell&lsquo;ultima fattura aperta";} break;
	   case 'vendororders' : {newTit="Genera un nuovo ordine fornitore"; appTit="Includi nell&lsquo;ultimo ordine fornitore aperto";} break;
	   case 'purchaseinvoices' : {newTit="Genera una nuova fattura d&lsquo;acquisto"; appTit="Includi nell&lsquo;ultima fattura d&lsquo;acquisto aperta";} break;
	   default : {newTit="Genera un nuovo documento"; appTit="Includi nell'ultimo documento aperto"; } break;
	  }
	  document.getElementById('docid_choice_newtit').innerHTML = newTit;
	  document.getElementById('docid_choice_apptit').innerHTML = appTit+": <a href='#'>"+a['items'][0]['name']+"</a>";
	  document.getElementById('docid_choice_append').checked = true;
	  document.getElementById('docid_choice').style.display='';
	  DOC_ID = a['items'][0]['id'];
	 }
	}
 sh.sendCommand("dynarc item-list -ap `commercialdocs` -ct `"+DOC_TYPE+"` -where `"+(RubricaEdit.data ? "subject_id="+RubricaEdit.data['id'] : "subject_name='"+RubricaEdit.value+"'")+" AND status=0` --order-by `ctime DESC` -limit 1");
}
</script>
</body></html>
<?php


