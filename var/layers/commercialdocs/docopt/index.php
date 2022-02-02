<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 29-11-2016
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Document list options layer.
 #VERSION: 2.10beta
 #CHANGELOG: 29-11-2016 : Aggiunto scarica magazzino su ordini.
			 02-10-2016 : Aggiustato permessi per convertire in altri documenti.
			 25-09-2015 : Aggiornata funzione stampa.
			 31-03-2015 : Semplice bug fix testuale sui rapp. di intervento.
			 05-12-2014 : Aggiunta opzione converti in altro documento.
			 27-01-2014 : Aggiunto opzione "Chiudi DDT"
			 24-10-2013 : Aggiunto extra options
			 15-03-2013 : Bug fix.
			 06-03-2013 : Aggiunto tasto stampa.
			 13-02-2013 : Some bug fix.
 #TODO: Da mettere tasto esporta in pdf e invia per email
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_COMMERCIALDOCS_CONFIG;

$_BASE_PATH = "../../../../";

define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."etc/commercialdocs/config.php");

if($_REQUEST['id'])
{
 $ret = GShell("dynarc item-info -ap `commercialdocs` -id `".$_REQUEST['id']."` -extget cdinfo");
 if(!$ret['error'])
  $docInfo = $ret['outarr'];
}

$_SUBJECT_TYPE = 0;
if($docInfo['subject_id'])
{
 /* GET SUBJECT TYPE */
 $ret = GShell("dynarc item-info -ap rubrica -id '".$docInfo['subject_id']."' -get iscompany");
 if(!$ret['error'])
  $_SUBJECT_TYPE = $ret['outarr']['iscompany'];
}

/* DETECT DOC TYPE */
if($docInfo && $docInfo['cat_id'])
{
 /* GET CAT TAG */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT tag,parent_id FROM dynarc_commercialdocs_categories WHERE id='".$docInfo['cat_id']."'");
 if($db->Read())
 {
  if($db->record['parent_id'])
  {
   $db->RunQuery("SELECT tag FROM dynarc_commercialdocs_categories WHERE id='".$db->record['parent_id']."'");
   $db->Read();
   $_CAT_TAG = $db->record['tag']; 
  }
  else
   $_CAT_TAG = $db->record['tag'];
 }
 $db->Close();
}

/* GET STORE LIST */
$ret = GShell("store list");
$storeList = $ret['outarr'];

?>
<script type="text/javascript" src="<?php echo $_ABSOLUTE_URL; ?>var/layers/commercialdocs/docopt/docopt.js"></script>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/layers/commercialdocs/docopt/docopt.css" type="text/css" />

<div class='docopt-header'>&nbsp;</div>
<div class='docopt-body'>
 <div class="docopt-item" style='border:0px'><a href='#' onclick="docopt_printPreview(<?php echo $docInfo['id']; ?>,'<?php echo $_CAT_TAG; ?>','<?php echo $docInfo['name']; ?>',<?php echo $docInfo['status']; ?>,<?php echo $docInfo['subject_id']; ?>)"><img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/commercialdocs/docopt/img/print.png"/> Stampa</a></div>

 <?php
 $first = false;
 /* EXTRA OPTIONS */
 if(isset($_COMMERCIALDOCS_CONFIG['EXTOPT'][$_CAT_TAG]) && count($_COMMERCIALDOCS_CONFIG['EXTOPT'][$_CAT_TAG]))
 {
  $first = true;
  for($c=0; $c < count($_COMMERCIALDOCS_CONFIG['EXTOPT'][$_CAT_TAG]); $c++)
  {
   $extopt = $_COMMERCIALDOCS_CONFIG['EXTOPT'][$_CAT_TAG][$c];
   $onclick = "";
   $href = "";
   if($extopt['shotevent'])
    $onclick = "docopt_shotEvent(\"".$extopt['shotevent']."\",".$docInfo['id'].",\"".$extopt['params']."\")";
   else if($extopt['onclick'])
	$onclick = $extopt['onclick']."(".$docInfo['id'].",\"".$extopt['params']."\")";
   else if($extopt['url'])
	$href = $extopt['url'];
   echo "<div class='docopt-item'><a href='".($href ? $href : '#')."'".($onclick ? " onclick='".$onclick."'" : "")."><img src='"
	.$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/".$extopt['icon']."'/> ".$extopt['title']."</a></div>";
  }
 }
 /* STANDARD OPTIONS */
 switch(strtolower($_CAT_TAG))
 {
  case 'preemptives' : {
	 if($docInfo['status'] < 3)
	  echo "<div class='docopt-item'><a href='#' ".(count($storeList) ? "onclick='docopt_checkAvail(".$docInfo['id'].")'" : "onclick='docopt_confirm(".$docInfo['id'].")'")."><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/forward.png'/> Conferma preventivo</a></div>";

	 if($docInfo['status'] < 8)
	 {
	  echo "<div class='docopt-item' style='height:70px'><span class='spangray'>Converti in:</span><br/>";
	  if(_userInGroup("commdocs-orders"))
	   echo "<div class='convdocthumb' onclick='docopt_convert(".$docInfo['id'].",\"orders\")'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/doc-orange.png'/><br/><span>Ordine</span></div>";
	  if(_userInGroup("commdocs-ddt"))
	   echo "<div class='convdocthumb' onclick='docopt_convert(".$docInfo['id'].",\"ddt\")'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/doc-violet.png'/><br/><span>D.D.T.</span></div>";
  	  if(_userInGroup("commdocs-invoices"))
	   echo "<div class='convdocthumb' onclick='docopt_convert(".$docInfo['id'].",\"invoices\")'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/doc-green.png'/><br/><span>Fattura</span></div>";
	  echo "<div class='docopt-other' onclick='docopt_convert(".$docInfo['id'].")' title='Altro...'>&raquo;</div>";
	  echo "</div>";
	  if($docInfo['status'] > 0)
	   echo "<div class='docopt-item' style='border:0px'><a href='#' onclick=\"docopt_restoreStatus(".$docInfo['id'].",'"
		.strtolower($_CAT_TAG)."',".($docInfo['status'] ? $docInfo['status'] : '0').")\"><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/restore.png'/> Ripristina status...</a></div>";
	 }
	 else
	 {
	  echo "<div class='docopt-item'><a href='#' onclick=\"docopt_restoreStatus(".$docInfo['id'].",'"
		.strtolower($_CAT_TAG)."',".($docInfo['status'] ? $docInfo['status'] : '0').")\"><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/restore.png'/> Ripristina status...</a></div>";
	 }
	} break;
	/* EOF - PREEMPTIVES ------------------------------------------------------------------------- */

  case 'orders' : {
	 if($docInfo['status'] < 3)
	  echo "<div class='docopt-item'><a href='#' ".(count($storeList) ? "onclick='docopt_checkAvail(".$docInfo['id'].")'" : "onclick='docopt_confirm(".$docInfo['id'].")'")."><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/forward.png'/> Conferma ordine</a></div>";
	 else if($docInfo['status'] < 8)
	  echo "<div class='docopt-item'><a href='#' onclick='docopt_downloadStore(".$docInfo['id'].",7,true)'><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/forward.png'/> Scarica magazzino</a></div>";

	 if($docInfo['status'] != 8)
	 {
	  echo "<div class='docopt-item' style='height:70px;'><span class='spangray'>Converti in:</span><br/>";
	  if(_userInGroup("commdocs-paymentnotice"))
	   echo "<div class='convdocthumb' onclick='docopt_convert(".$docInfo['id'].",\"paymentnotice\")'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/doc-sky.png'/><br/><span>Avv. di pagam.</span></div>";
	  if(_userInGroup("commdocs-ddt"))
	   echo "<div class='convdocthumb' onclick='docopt_convert(".$docInfo['id'].",\"ddt\")'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/doc-violet.png'/><br/><span>D.D.T.</span></div>";
	  if(_userInGroup("commdocs-invoices"))
	   echo "<div class='convdocthumb' onclick='docopt_convert(".$docInfo['id'].",\"invoices\")'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/doc-green.png'/><br/><span>Fattura</span></div>";
	  echo "<div class='docopt-other' onclick='docopt_convert(".$docInfo['id'].")' title='Altro...'>&raquo;</div>";
	  echo "</div>";
	  $first=true;
	 }

	 echo "<div class='docopt-item'><span class='spangray'>Modifica status</span><br/><div class='docopt-status-div'>";
	 $status = array("0"=>"aperto", "3"=>"in attesa", "4"=>"in lavorazione", "5"=>"sospeso", "6"=>"fallito", "7"=>"completato");
	 while(list($k,$v) = each($status))
	 {
	  echo "<input type='radio' name='status'".($docInfo['status'] == $k ? " checked='true'" : "")." onclick='docopt_statusChange("
		.$docInfo['id'].",".$k.",\"orders\")'>".$v."</input><br/>";
	 }
     echo "</div></div>";
	 if($docInfo['status'] < 8)
      echo "<div class='docopt-item' style='border-bottom:0px'><a href='#' onclick='docopt_paid(".$docInfo['id'].")'><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/paid.png'/> Segna come pagato</a></div>";
	} break;
	/* EOF - ORDERS ------------------------------------------------------------------------------ */

  case 'ddt' : {
	 if($docInfo['status'] < 3)
	  echo "<div class='docopt-item'><a href='#' ".(count($storeList) ? "onclick='docopt_checkAvail(".$docInfo['id'].")'" : "onclick='docopt_confirm(".$docInfo['id'].")'")."><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/forward.png'/> Conferma D.D.T.</a></div>";

	 if($docInfo['status'] < 7)
	 {
	  echo "<div class='docopt-item'><a href='#' onclick='docopt_statusChange(".$docInfo['id'].",7,\"ddt\")'><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/forward.png'/> Chiudi D.D.T.</a></div>";
	 }
	 if($docInfo['status'] < 8)
	 {
	  echo "<div class='docopt-item' style='height:70px'><span class='spangray'>Converti in:</span><br/>";
	  if(_userInGroup("commdocs-invoices"))
	   echo "<div class='convdocthumb' onclick='docopt_convert(".$docInfo['id'].",\"invoices\")'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/doc-green.png'/><br/><span>Fattura</span></div>";
	  echo "<div class='docopt-other' onclick='docopt_convert(".$docInfo['id'].")' title='Altro...'>&raquo;</div>";
	  echo "</div>";

      echo "<div class='docopt-item' style='border-bottom:0px'><a href='#' onclick='docopt_paid(".$docInfo['id'].")'><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/paid.png'/> Segna come pagato</a></div>";

	  $first=true;
	 }
	 else
	 {
	  echo "<div class='docopt-item'><a href='#' onclick=\"docopt_restoreStatus(".$docInfo['id'].",'"
		.strtolower($_CAT_TAG)."',".($docInfo['status'] ? $docInfo['status'] : '0').")\"><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/restore.png'/> Ripristina status...</a></div>";
	 }
	} break;
	/* EOF - DDT --------------------------------------------------------------------------------- */

  case 'ddtin' : {
	 if($docInfo['status'] <= 3)
	  echo "<div class='docopt-item'><a href='#' onclick='docopt_goodsDelivered(".$docInfo['id'].")'><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/forward.png'/> Carica a magazzino</a></div>";
	 if($docInfo['status'] < 8)
	 {
	  echo "<div class='docopt-item' style='height:70px'><span class='spangray'>Converti in:</span><br/>";
	  if(_userInGroup("commdocs-purchaseinvoices"))
	   echo "<div class='convdocthumb' onclick='docopt_convert(".$docInfo['id'].",\"purchaseinvoices\")'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/doc-green.png'/><br/><span>Fattura d&lsquo;acquisto</span></div>";
	  echo "<div class='docopt-other' onclick='docopt_convert(".$docInfo['id'].")' title='Altro...'>&raquo;</div>";
	  echo "</div>";

      echo "<div class='docopt-item'><a href='#' onclick='docopt_paid(".$docInfo['id'].",true)'><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/paid.png'/> Segna come pagata</a></div>";
	 }
	 else
	 {
	  echo "<div class='docopt-item'><a href='#' onclick=\"docopt_restoreStatus(".$docInfo['id'].",'"
		.strtolower($_CAT_TAG)."',".($docInfo['status'] ? $docInfo['status'] : '0').")\"><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/restore.png'/> Ripristina status...</a></div>";
	 }
	} break;
	/* EOF - DDTIN --------------------------------------------------------------------------------- */

  case 'invoices' : case 'receipts' : {
	 if($docInfo['status'] <= 3)
	  echo "<div class='docopt-item'><a href='#' onclick='docopt_downloadStore(".$docInfo['id'].")'><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/forward.png'/> Scarica magazzino</a></div>";

	 // verifica se il pacchetto fatturepa è installato
	 if(file_exists($_BASE_PATH."etc/commercialdocs/protocols/paxml.php"))
	 {
	  if($docInfo['fatt_pa_id'])
	  {
	   echo "<div class='docopt-item'><a href='#' onclick='docopt_showFatturaPA(".$docInfo['fatt_pa_id'].")'><img src='".$_ABSOLUTE_URL."share/icons/16x16/xml.gif'/> Mostra fattura elettronica</a></div>";

	   echo "<div class='docopt-item'><a href='#' onclick='docopt_generatePA(".$docInfo['id'].")'><img src='".$_ABSOLUTE_URL."share/icons/16x16/xml.gif'/> Rigenera fattura elettronica</a></div>";
	  }
	  else
	  {
	   if($_SUBJECT_TYPE == 2)
	    echo "<div class='docopt-item'><a href='#' onclick='docopt_generatePA(".$docInfo['id'].")'><img src='".$_ABSOLUTE_URL."share/icons/16x16/xml.gif'/> Genera fattura elettronica</a></div>";
	  }
	 }

	 if($docInfo['status'] < 10)
      echo "<div class='docopt-item'><a href='#' onclick='docopt_paid(".$docInfo['id'].")'><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/paid.png'/> Segna come pagata</a></div>";
	 else
	 {
	  echo "<div class='docopt-item'><a href='#' onclick=\"docopt_restoreStatus(".$docInfo['id'].",'"
		.strtolower($_CAT_TAG)."',".($docInfo['status'] ? $docInfo['status'] : '0').")\"><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/restore.png'/> Ripristina status...</a></div>";
	 }
	} break;
	/* EOF - INVOICES ---------------------------------------------------------------------------- */

  case 'vendororders' : {
	 if($docInfo['status'] <= 3)
	  echo "<div class='docopt-item'><a href='#' onclick='docopt_goodsDelivered(".$docInfo['id'].")'><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/forward.png'/> Merce arrivata</a></div>";
	 else if($docInfo['status'] < 10)
	 {
	  echo "<div class='docopt-item' style='height:70px'><span class='spangray'>Converti in:</span><br/>";
	  if(_userInGroup("commdocs-purchaseinvoices"))
	   echo "<div class='convdocthumb' onclick='docopt_convert(".$docInfo['id'].",\"purchaseinvoices\")'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/doc-green.png'/><br/><span>Fattura d&lsquo;acquisto</span></div>";
	  echo "<div class='docopt-other' onclick='docopt_convert(".$docInfo['id'].")' title='Altro...'>&raquo;</div>";
	  echo "</div>";

      echo "<div class='docopt-item'><a href='#' onclick='docopt_paid(".$docInfo['id'].",true)'><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/paid.png'/> Segna come pagata</a></div>";
	 }
	 else
	 {
	  echo "<div class='docopt-item'><a href='#' onclick=\"docopt_restoreStatus(".$docInfo['id'].",'"
		.strtolower($_CAT_TAG)."',".($docInfo['status'] ? $docInfo['status'] : '0').")\"><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/restore.png'/> Ripristina status...</a></div>";
	 }
	} break;
	/* EOF - VENDORORDERS ------------------------------------------------------------------------ */

  case 'purchaseinvoices' : {
	 if($docInfo['status'] <= 3)
	  echo "<div class='docopt-item'><a href='#' onclick='docopt_goodsDelivered(".$docInfo['id'].")'><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/forward.png'/> Carica a magazzino</a></div>";
	 if($docInfo['status'] < 10)
      echo "<div class='docopt-item'><a href='#' onclick='docopt_paid(".$docInfo['id'].",true)'><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/paid.png'/> Segna come pagata</a></div>";
	 else
	 {
	  echo "<div class='docopt-item'><a href='#' onclick=\"docopt_restoreStatus(".$docInfo['id'].",'"
		.strtolower($_CAT_TAG)."',".($docInfo['status'] ? $docInfo['status'] : '0').")\"><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/restore.png'/> Ripristina status...</a></div>";
	 }
	} break;
	/* EOF - PURCHASEINVOICES -------------------------------------------------------------------- */

  case 'intervreports' : {
	 if($docInfo['status'] < 3)
	  echo "<div class='docopt-item' style='border:0px'><a href='#' ".(count($storeList) ? "onclick='docopt_checkAvail(".$docInfo['id'].")'" : "onclick='docopt_confirm(".$docInfo['id'].")'")."><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/forward.png'/> Conferma</a></div>";

	 if($docInfo['status'] < 8)
	 {
	  echo "<div class='docopt-item' style='height:70px;'><span class='spangray'>Converti in:</span><br/>";
	  if(_userInGroup("commdocs-ddt"))
	   echo "<div class='convdocthumb' onclick='docopt_convert(".$docInfo['id'].",\"ddt\")'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/doc-violet.png'/><br/><span>D.D.T.</span></div>";
	  if(_userInGroup("commdocs-invoices"))
	   echo "<div class='convdocthumb' onclick='docopt_convert(".$docInfo['id'].",\"invoices\")'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/doc-green.png'/><br/><span>Fattura</span></div>";
	  echo "<div class='docopt-other' onclick='docopt_convert(".$docInfo['id'].")' title='Altro...'>&raquo;</div>";
	  echo "</div>";

      echo "<div class='docopt-item' style='border-bottom:0px'><a href='#' onclick='docopt_paid(".$docInfo['id'].")'><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/paid.png'/> Segna come pagato</a></div>";
	 }
	 else
	 {
	  echo "<div class='docopt-item'><a href='#' onclick=\"docopt_restoreStatus(".$docInfo['id'].",'"
		.strtolower($_CAT_TAG)."',".($docInfo['status'] ? $docInfo['status'] : '0').")\"><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/restore.png'/> Ripristina status...</a></div>";
	 }
	} break;
	/* EOF - INTERVREPORTS ----------------------------------------------------------------------- */

  case 'agentinvoices' : {
	 if($docInfo['status'] < 10)
      echo "<div class='docopt-item'><a href='#' onclick='docopt_paid(".$docInfo['id'].",true)'><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/paid.png'/> Segna come pagata</a></div>";
	 else
	 {
	  echo "<div class='docopt-item'><a href='#' onclick=\"docopt_restoreStatus(".$docInfo['id'].",'"
		.strtolower($_CAT_TAG)."',".($docInfo['status'] ? $docInfo['status'] : '0').")\"><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/restore.png'/> Ripristina status...</a></div>";
	 }
	} break;
	/* EOF - AGENTINVOICES ----------------------------------------------------------------------- */

  case 'creditsnote' : {
	 if($docInfo['status'] < 10)
      echo "<div class='docopt-item'><a href='#' onclick='docopt_paid(".$docInfo['id'].",true)'><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/paid.png'/> Segna come pagata</a></div>";
	 else
	 {
	  echo "<div class='docopt-item'><a href='#' onclick=\"docopt_restoreStatus(".$docInfo['id'].",'"
		.strtolower($_CAT_TAG)."',".($docInfo['status'] ? $docInfo['status'] : '0').")\"><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/restore.png'/> Ripristina status...</a></div>";
	 }

	 // verifica se il pacchetto fatturepa è installato
	 if(file_exists($_BASE_PATH."etc/commercialdocs/protocols/paxml.php"))
	 {
	  if($docInfo['fatt_pa_id'])
	  {
	   echo "<div class='docopt-item'><a href='#' onclick='docopt_showFatturaPA(".$docInfo['fatt_pa_id'].")'><img src='".$_ABSOLUTE_URL."share/icons/16x16/xml.gif'/> Mostra fattura elettronica</a></div>";

	   echo "<div class='docopt-item'><a href='#' onclick='docopt_generatePA(".$docInfo['id'].")'><img src='".$_ABSOLUTE_URL."share/icons/16x16/xml.gif'/> Rigenera fattura elettronica</a></div>";
	  }
	  else
	  {
	   if($_SUBJECT_TYPE == 2)
	    echo "<div class='docopt-item'><a href='#' onclick='docopt_generatePA(".$docInfo['id'].")'><img src='".$_ABSOLUTE_URL."share/icons/16x16/xml.gif'/> Genera fattura elettronica</a></div>";
	  }
	 }

	} break;
	/* EOF - CREDITS NOTE------------------------------------------------------------------------- */

  case 'debitsnote' : {
	 if($docInfo['status'] < 10)
      echo "<div class='docopt-item'><a href='#' onclick='docopt_paid(".$docInfo['id'].")'><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/paid.png'/> Segna come pagata</a></div>";
	 else
	 {
	  echo "<div class='docopt-item'><a href='#' onclick=\"docopt_restoreStatus(".$docInfo['id'].",'"
		.strtolower($_CAT_TAG)."',".($docInfo['status'] ? $docInfo['status'] : '0').")\"><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/restore.png'/> Ripristina status...</a></div>";
	 }

	 // verifica se il pacchetto fatturepa è installato
	 if(file_exists($_BASE_PATH."etc/commercialdocs/protocols/paxml.php"))
	 {
	  if($docInfo['fatt_pa_id'])
	  {
	   echo "<div class='docopt-item'><a href='#' onclick='docopt_showFatturaPA(".$docInfo['fatt_pa_id'].")'><img src='".$_ABSOLUTE_URL."share/icons/16x16/xml.gif'/> Mostra fattura elettronica</a></div>";

	   echo "<div class='docopt-item'><a href='#' onclick='docopt_generatePA(".$docInfo['id'].")'><img src='".$_ABSOLUTE_URL."share/icons/16x16/xml.gif'/> Rigenera fattura elettronica</a></div>";
	  }
	  else
	  {
	   if($_SUBJECT_TYPE == 2)
	    echo "<div class='docopt-item'><a href='#' onclick='docopt_generatePA(".$docInfo['id'].")'><img src='".$_ABSOLUTE_URL."share/icons/16x16/xml.gif'/> Genera fattura elettronica</a></div>";
	  }
	 }

	} break;
	/* EOF - DEBITS NOTE ------------------------------------------------------------------------------ */

  case 'paymentnotice' : {
	 echo "<div class='docopt-item' style='height:70px;'><span class='spangray'>Converti in:</span><br/>";
	 if(_userInGroup("commdocs-ddt"))
	  echo "<div class='convdocthumb' onclick='docopt_convert(".$docInfo['id'].",\"ddt\")'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/doc-violet.png'/><br/><span>D.D.T.</span></div>";
	 if(_userInGroup("commdocs-invoices"))
	  echo "<div class='convdocthumb' onclick='docopt_convert(".$docInfo['id'].",\"invoices\")'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/doc-green.png'/><br/><span>Fattura</span></div>";
	 echo "<div class='docopt-other' onclick='docopt_convert(".$docInfo['id'].")' title='Altro...'>&raquo;</div>";
	 echo "</div>";
	 $first=true;

     echo "<div class='docopt-item' style='border-bottom:0px'><a href='#' onclick='docopt_paid(".$docInfo['id'].")'><img src='".$_ABSOLUTE_URL."var/layers/commercialdocs/docopt/img/paid.png'/> Segna come pagato</a></div>";
	} break;
	/* EOF - PAYMENT NOTICE ----------------------------------------------------------------------------- */

 }
 ?>

</div>
<div class='docopt-footer'>&nbsp;</div>

