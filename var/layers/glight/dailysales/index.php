<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-01-2014
 #PACKAGE: backoffice
 #DESCRIPTION: BackOffice - Daily Sales
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH;

$_BASE_PATH = "../../../../";

define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");
?>
<script type="text/javascript" src="<?php echo $_ABSOLUTE_URL; ?>var/layers/glight/dailysales/dailysales.js"></script>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/layers/glight/dailysales/dailysales.css" type="text/css" />
<?php

$ret = GShell("backoffice get-sales -date '".$_REQUEST['date']."'",$_REQUEST['sessid'],$_REQUEST['shellid']);
if(!$ret['error'])
 $results = $ret['outarr']; 

$imgPath = $_ABSOLUTE_URL."var/layers/glight/dailysales/img/";

/* DOC LIST */
echo "<div class='dailysales-doclist'>";
echo "<table class='doclist' width='100%' cellspacing='0' cellpadding='0' border='0'>";
echo "<tr><th width='200'>Documento</th><th>Cliente</th><th width='70' style='text-align:right'>Totale</th></tr>";
$list = $results['documents'];
for($c=0; $c < count($list); $c++)
{
 $docInfo = $list[$c];
 echo "<tr><td><a href='".$_ABSOLUTE_URL."GCommercialDocs/docinfo.php?id=".$docInfo['id']."' target='GCD-".$docInfo['id']."' class='linkblue'>"
	.$docInfo['name']."</a></td>";
 echo "<td>".$docInfo['subject_name']."</td>";
 echo "<td class='currency'>".number_format($docInfo['tot_netpay'],2,',','.')."</td></tr>";
}
echo "</table>";
echo "</div>";

echo "<div class='doclist-totals-container'><div style='color:#6699cc;font-size:10px;padding-left:10px;height:12px'>TOTALI</div>";
echo "<div class='doclist-totals-inner'>";
echo "<table width='100%' cellspacing='0' cellpadding='0' border='0' style='margin:5px'>";
echo "<tr><td valign='top' width='30%'>";

echo "<table class='doclist-totals' align='center' cellspacing='0' cellpadding='0' border='0'>";
echo "<tr><td width='100'>Imponibile:</td><td width='100' align='right'>".number_format($results['tot_amount'],2,',','.')." &euro;</td></tr>";
echo "<tr><td>IVA:</td><td align='right'>".number_format($results['tot_vat'],2,',','.')." &euro;</td></tr>";
echo "<tr><td>Totale:</td><td align='right'>".number_format($results['tot_total'],2,',','.')." &euro;</td></tr>";
echo "<tr><td>Netto a pagare:</td><td align='right'>".number_format($results['tot_netpay'],2,',','.')." &euro;</td></tr>";
echo "</table>";

echo "</td><td style='border-right:1px solid #0169c9'>&nbsp;</td><td width='30%' valign='top'>";

echo "<table class='doclist-totals' align='center' cellspacing='0' cellpadding='0' border='0'>";
echo "<tr><td width='100'>Rit. d&lsquo;acconto:</td><td width='100' align='right'>".number_format($results['tot_rit_acc'],2,',','.')." &euro;</td></tr>";
echo "<tr><td>Cassa prev.:</td><td align='right'>".number_format($results['tot_ccp'],2,',','.')." &euro;</td></tr>";
echo "<tr><td>Rivalsa INPS:</td><td align='right'>".number_format($results['tot_rinps'],2,',','.')." &euro;</td></tr>";
echo "<tr><td>Enasarco:</td><td align='right'>".number_format($results['tot_enasarco'],2,',','.')." &euro;</td></tr>";
echo "</table>";

echo "</td><td style='border-right:1px solid #0169c9'>&nbsp;</td><td width='30%' valign='top'>";

echo "<table class='doclist-totals' align='center' cellspacing='0' cellpadding='0' border='0'>";
echo "<tr><td width='100'>Bolli:</td><td width='100' align='right'>".number_format($results['tot_stamp'],2,',','.')." &euro;</td></tr>";
echo "<tr><td>Spese trasp.:</td><td align='right'>".number_format($results['tot_cartage'],2,',','.')." &euro;</td></tr>";
echo "<tr><td>Spese imball.:</td><td align='right'>".number_format($results['tot_packing_charges'],2,',','.')." &euro;</td></tr>";
echo "<tr><td>Spese incasso:</td><td align='right'>".number_format($results['tot_collection_charges'],2,',','.')." &euro;</td></tr>";
echo "</table>";

echo "</td></tr></table>";

echo "</div>";
echo "</div>";

echo "<div class='footer'>";
echo "<input type='button' class='button-blue' style='float:left' value='Stampa'/>";
/*echo "<ul class='iconsmenu' style='float:left;margin-top:2px'>";
echo "<li>tot. imponibile: ".number_format($results['tot_amount'],2,',','.')." &euro;</li>";
/*echo "<li id='requestinfo-".$_ID."-attachbtn' refap='".$_AP."' refcat='".$_ID."' destpath='helpdesk/attachments/requests/' connect='requestinfo-".$_ID."-attachments'><img src='".$imgPath."upload.png' title='Carica un allegato'/></li>";
echo "<li class='separator'></li>";
echo "<li onclick='glrequestinfo_addTask(".$_ID.")'><img src='".$imgPath."addtask.png' title='Inserisci un task'/></li>";
echo "</ul>";*/
/*echo "<ul class='iconsmenu' style='float:right;margin-top:2px'>";
echo "<li onclick='glrequestinfo_delete(".$_ID.")'><img src='".$imgPath."trash.png' title='Elimina'/></li>";
echo "<li class='separator'></li>";
echo "<li><img src='".$imgPath."dnarrow.png' title='Opzioni' onclick='glrequestinfo_showMenuOpt(".$_ID.",this)'/></li>";
echo "</ul>";*/
echo "</div>";

  /* REQ. MENU */
  /*echo "<ul class='popupmenu' id='requestinfo-".$_ID."-menu'>";
  echo "<li onclick='glrequestinfo_rename(".$_ID.",\"".$requestInfo['name']."\")'><img src='".$imgPath."rename.png'/> Rinomina</li>";
  echo "</ul>";*/

  /* REQ. DETAILS */
  /*echo "<div class='popupmessage' id='requestinfo-".$_ID."-details' style='width:350px;height:150px'>";
  echo "<table class='popupmsgform'>";
  echo "<tr><td class='field'>priorità:</td>";
  echo "<td><select class='dropdown' style='width:120px' id='requestinfo-".$_ID."-priority'>";
	for($c=0; $c < count($goalPriority); $c++)
	 echo "<option value='".$c."'".($c == $requestInfo['priority'] ? " selected='selected'>" : ">").$goalPriority[$c]."</option>";
  echo "</select></td></tr>";
  echo "<tr><td class='field'>status:</td>";
  echo "<td><select class='dropdown' style='width:120px' id='requestinfo-".$_ID."-status'>";
	for($c=0; $c < count($goalStatus); $c++)
	 echo "<option value='".$c."'".($c == $requestInfo['status'] ? " selected='selected'>" : ">").$goalStatus[$c]."</option>";
  echo "</select></td></tr>";
  echo "<tr><td class='field'>categoria:</td>";
  echo "<td><input type='text' class='dropdown' style='width:200px' placeholder='Seleziona una categoria' id='requestinfo-".$_ID."-section' ap='gnujikorupsec' catid='".$requestInfo['section_id']."' value=\"".htmlspecialchars_decode($requestInfo['section_name'],ENT_QUOTES)."\"/>";
  echo "<input type='button' class='button-folder' ap='gnujikorupsec' connect='requestinfo-".$_ID."-section' id='requestinfo-".$_ID."-btnselcat'/></td></tr>";
  echo "<tr><td class='field'>da fare entro:</td>";
  echo "<td><input type='text' class='calendar' id='requestinfo-".$_ID."-taxdelivery' style='width:120px' value='".($requestInfo['tax_delivery'] ? date('d/m/Y',strtotime($requestInfo['tax_delivery'])) : "")."'/></td></tr>";
  echo "</table>";
  echo "</div>";*/

?>
<script>
function glrequestinfo_init(id)
{
 /*Template.initPopupMenu(document.getElementById("requestinfo-"+id+"-menu"));
 Template.initPopupMessage(document.getElementById("requestinfo-"+id+"-details"));

 Template.initEd(document.getElementById("requestinfo-"+id+"-section"), "catfind");
 Template.initBtn(document.getElementById("requestinfo-"+id+"-btnselcat"), "catselect");

 Template.initEd(document.getElementById("requestinfo-"+id+"-taxdelivery"), "date");
 Template.initBtn(document.getElementById("requestinfo-"+id+"-attachbtn"), "attachupld").OnUpload = function(){
	 document.getElementById("requestinfo-"+id+"-attachments").style.display = "";
	};*/

}
//glrequestinfo_init('<?php echo $_ID; ?>');
</script>

