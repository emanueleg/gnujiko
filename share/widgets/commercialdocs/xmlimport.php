<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
HackTVT Project
copyright(C) 2017 Alpatech mediaware - www.alpatech.it
license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
Gnujiko 10.1 is free software released under GNU/GPL license
developed by D. L. Alessandro (alessandro@alpatech.it)

#DATE: 11-02-2017
#PACKAGE: gcommercialdocs
#DESCRIPTION: Import documents from XML file.
#VERSION: 2.0beta
#CHANGELOG: 
#TODO: 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = '../../../';

include($_BASE_PATH.'var/templates/glight/index.php');

$template = new GLightTemplate('widget');

$_FILE_NAME = (isset($_REQUEST['file']) && $_REQUEST['file']) ? $_REQUEST['file'] : "";
$_DOC_TYPE = (isset($_REQUEST['type']) && $_REQUEST['type']) ? $_REQUEST['type'] : "";

$template->Begin('Importazione documenti da file XML');
?>
<div class="glight-widget-header bg-blue"><h3>Importazione documenti da file XML</h3></div>
<?php
$template->Body('widget',640);

//-------------------------------------------------------------------------------------------------------------------//
?>
<div class='glight-widget-body' style='width:624px;height:360px'>
 <table width='616' cellspacing='0' cellpadding='0' border='0' class='standardform' id='doclist'>
  <thead>
   <tr>
	<th width='32'><input type='checkbox' checked='true' onchange='selectAllDocs(this)'/></th>
	<th width='80' title="Data documento" align='left'><span class='smalltext'>DATA</span></th>
	<th width='50' title="Numero del documento"><span class='smalltext'>N. DOC</span></th>
	<th align='left'><span class='smalltext'>CLIENTE / INTESTATARIO DOC.</span></th>
	<th width='50' title="Numero totale articoli"><span class='tinytext'>N. ART</span></th>
	<th width='70' title="Totale importo IVA inclusa" align='right'><span class='smalltext'>TOTALE</span></th>
   </tr>
  </thead>
  <tbody>
   <tr style='display:none' id='schema'>
	<td align='center'><input type='checkbox' checked='true'/></td>
	<td>{DATE}</td>
	<td align='center'>{NUM}</td>
	<td>{SUBJECT}</td>
	<td align='center'>{NUM_ART}</td>
	<td align='right'>{TOTAL}</td>
   </tr>
  </tbody>
 </table>
</div>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$footer = "<input type='button' class='button-blue' value='Importa' style='float:left' onclick='SubmitAction()'/>";
$footer.= "<input type='button' class='button-gray' value='Annulla' style='float:left;margin-left:10px' onclick='abort()'/>";
$template->Footer($footer,true);
//-------------------------------------------------------------------------------------------------------------------//
?>
<script>
var FILE_NAME = "<?php echo $_FILE_NAME; ?>";
var DOC_TYPE = "<?php echo $_DOC_TYPE; ?>";

function abort(){Template.Exit();}
function selectAllDocs(cb)
{
 var tb = document.getElementById('doclist');
 for(var c=2; c < tb.rows.length; c++)
  tb.rows[c].cells[0].getElementsByTagName('INPUT')[0].checked = cb.checked;
}

Template.OnInit = function()
{
 var sh = new GShell();
 sh.showProcessMessage("Caricamento in corso...", "Attendere prego, &egrave; in corso il caricamento del file XML");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(o,a){
	 this.hideProcessMessage();
	 for(var c=0; c < a.length; c++)
	 {
	  var docInfo = a[c];
	  var date = new Date();
	  date.setFromISO(docInfo['date']);
	  var r = document.getElementById('schema').cloneNode(true);

	  var html = r.innerHTML;
	  html = html.replace("{DATE}", date.printf('d/m/Y'));
	  html = html.replace("{NUM}", docInfo['num']);
	  html = html.replace("{SUBJECT}", docInfo['subject']['name']);
	  html = html.replace("{NUM_ART}", docInfo['num_art']);
	  html = html.replace("{TOTAL}", formatCurrency(docInfo['total'],2));
	  r.innerHTML = html;

	  r.style.display = "";
	  document.getElementById('doclist').tBodies[0].appendChild(r);
	 }
	}

 sh.sendCommand("commercialdocs import-from-xml -f `"+FILE_NAME+"` -type '"+DOC_TYPE+"' --preview");
}

function SubmitAction()
{
 var EXCLUDE = "";
 var tb = document.getElementById('doclist');
 for(var c=2; c < tb.rows.length; c++)
 {
  if(tb.rows[c].cells[0].getElementsByTagName('INPUT')[0].checked != true)
   EXCLUDE+= ","+(c-1);	// first=1, second=2, ...
 }
 if(EXCLUDE) EXCLUDE = EXCLUDE.substr(1);

 var sh = new GShell();
 sh.showProcessMessage("Importazione in corso...", "Attendere prego, &egrave; in corso l&lsquo;importazione dei documenti da file XML");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(o,a){
	 this.hideProcessMessage();
	 gframe_close(o,a);
	}

 sh.sendCommand("commercialdocs import-from-xml -f `"+FILE_NAME+"` -type '"+DOC_TYPE+"' --exclude-idx '"+EXCLUDE+"'");
}

</script>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$template->End();
//-------------------------------------------------------------------------------------------------------------------//
?>
