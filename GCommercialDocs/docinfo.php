<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-10-2013
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Document Info
 #VERSION: 2.24beta
 #CHANGELOG: 18-10-2013 : Aggiunto i bolli.
			 10-10-2013 : Bug fix sul totale + iva.
			 30-09-2013 : Sistemato lo sconto incondizionato.
			 24-09-2013 : Bug fix su MMR.
			 20-09-2013 : Bug fix vari.
			 31-07-2013 : Aggiunto il lotto di produzione.
			 24-07-2013 : Bug fix vari.
			 08-07-2013 : Aggiunto le ricevute fiscali.
			 05-07-2013 : Aggiunto rit.acconto,riv.inps,rit.enasarco,rit.acconto
			 29-05-2013 : Sistemato data e ora del trasporto.
			 07-05-2013 : Aggiunto il peso.
			 04-05-2013 : Aggiunto allegati, note e messaggi predefiniti
			 30-04-2013 : Risistemato di nuovo i decimali.
			 29-04-2013 : Sistemato i decimali e lunghezze delle caselle di edit che alcune sbordavano.
			 23-04-2013 : Bug fix sui decimali.
			 16-04-2013 : Nuova gestione delle modalità di pagamento.
			 11-04-2013 : Aggiunto colonne extra_qty e price_adjust.
			 10-04-2013 : Predisposto per le colonne extra.
			 27-02-2013 : Sistemato le modalità di pagamento. Ora viene calcolato in base alla data del documento e non a quella odierna.
			 21-02-2013 : Bug fix.
			 11-02-2013 : Aggiunto unità di misura che viene fuori in automatico.
			 05-02-2013 : Bug fix su banca d'appoggio.
			 28-01-2013 : Integrato con PaymentNotice.
			 23-01-2013 : Some bug fixed.
 #TODO: Manca da fare l'integrazione con le varianti dei prodotti.
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DESKTOP_SHOW_TOOLBAR, $_DESKTOP_TITLE, $_COMPANY_PROFILE, $_PRICELISTS, $_DEPOSITS, $_SCHEDULE, $_SHOW_SHIPPINGPAGE, $_SHOW_PAYMENTSPAGE, $_COLUMNS;

$_BASE_PATH = "../";

define("VALID-GNUJIKO",1);

include($_BASE_PATH.'init/init1.php');
include($_BASE_PATH.'include/session.php');
include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/company-profile.php");
include_once($_BASE_PATH."include/countries.php");

if(!loginRequired())
 exit;

$_BANKS = $_COMPANY_PROFILE['banks'];
$_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'];
$ret = GShell("pricelists list");
$_PRICELISTS = $ret['outarr'];
$_PRICELIST_BY_ID = array();
for($c=0; $c < count($_PRICELISTS); $c++)
 $_PRICELIST_BY_ID[$_PRICELISTS[$c]['id']] = $_PRICELISTS[$c];

/* GET LIST OF VAT RATES */
$ret = GShell("dynarc item-list -ap vatrates -get `percentage,vat_type`");
$_VAT_LIST = $ret['outarr']['items'];
$_VAT_BY_ID = array();
for($c=0; $c < count($_VAT_LIST); $c++)
 $_VAT_BY_ID[$_VAT_LIST[$c]['id']] = $_VAT_LIST[$c];

$_DEF_VAT = 0;
$_DEF_VAT_ID = $_COMPANY_PROFILE['accounting']['freq_vat_used'];
$_DEF_VAT_TYPE = "";
$_CASSA_PREV_PERC = $_COMPANY_PROFILE['accounting']['contr_cassa_prev'];
$_CASSA_PREV_VATID = $_COMPANY_PROFILE['accounting']['contr_cassa_prev_vatid'];
$_CASSA_PREV_VAT = 0;
$_CASSA_PREV_VAT_TYPE = "";

for($c=0; $c < count($_VAT_LIST); $c++)
{
 if($_VAT_LIST[$c]['id'] == $_DEF_VAT_ID)
 {
  $_DEF_VAT = $_VAT_LIST[$c]['percentage'];
  $_DEF_VAT_TYPE = $_VAT_LIST[$c]['vat_type'];
 }
 if($_VAT_LIST[$c]['id'] == $_CASSA_PREV_VATID)
 {
  $_CASSA_PREV_VAT = $_VAT_LIST[$c]['percentage'];
  $_CASSA_PREV_VAT_TYPE = $_VAT_LIST[$c]['vat_type'];
 }
}

$ret = GShell("dynarc item-info -ap `commercialdocs` -id `".$_REQUEST['id']."` -extget `cdinfo,cdelements,mmr`");
$docInfo = $ret['outarr'];


$_PLID = $docInfo['pricelist_id'] ? $docInfo['pricelist_id'] : 0;
$_PLINFO = null;
if(count($_PRICELISTS))
{
 if(!$_PLID)
 {
  $_PLINFO = $_PRICELISTS[0];
  $_PLID = $_PRICELISTS[0]['id'];
 }
 else
  $_PLINFO = $_PRICELIST_BY_ID[$_PLID];
}

/* DETECT DOC TYPE */
if($docInfo && $docInfo['cat_id'])
{
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

  switch(strtolower($_CAT_TAG))
  {
   case 'preemptives' : $_DOC_TYPENAME = "Preventivo"; break;

   case 'orders' : {
	 $_DOC_TYPENAME = "Ordine"; 
	 $_SHOW_SHIPPINGPAGE = true;
	 if($docInfo['status'] < 8)
	  $_SHOW_PAYMENTSPAGE = true;
	} break;

   case 'ddt' : {
	 $_DOC_TYPENAME = "D.D.T.";
	 $_SHOW_SHIPPINGPAGE = true;
	 if($docInfo['status'] < 8)
	  $_SHOW_PAYMENTSPAGE = true;
	} break;

   case 'invoices' : {
	 $_DOC_TYPENAME = "Fattura";
	 $_SHOW_SHIPPINGPAGE = true;
	 $_SHOW_PAYMENTSPAGE = true;
	} break;

   case 'purchaseinvoices' : {
	 $_DOC_TYPENAME = "Fattura d&lsquo;acquisto";
	 $_SHOW_PAYMENTSPAGE = true;
	} break;

   case 'vendororders' : {
	 $_DOC_TYPENAME = "Ordine fornitore"; 
	 $_SHOW_PAYMENTSPAGE = true;
	} break;

   case 'agentinvoices' : {
	 $_DOC_TYPENAME = "Fattura agente";
	 $_SHOW_PAYMENTSPAGE = true;
	} break;

   case 'intervreports' : $_DOC_TYPENAME = "Rapporto d&lsquo;intervento"; break;

   case 'creditsnote' : {
	 $_DOC_TYPENAME = "Nota di accredito";
	 $_SHOW_PAYMENTSPAGE = true;
	} break;

   case 'debitsnote' : {
	 $_DOC_TYPENAME = "Nota di debito";
	 $_SHOW_PAYMENTSPAGE = true;
	} break;

   case 'paymentnotice' : {
	 $_DOC_TYPENAME = "Avviso di pagamento";
	 $_SHOW_PAYMENTSPAGE = true;
	} break;

   case 'receipts' : $_DOC_TYPENAME = "Ricevuta Fiscale"; break;

   default : $_DOC_TYPENAME = "Documento generico"; break;
  }

}


/* DETECT SUBJECT INFO */
if($docInfo['subject_id'])
{
 $ret = GShell("dynarc item-info -ap `rubrica` -id `".$docInfo['subject_id']."` -extget `contacts,banks,references` -get distance");
 if(!$ret['error'])
  $subjectInfo = $ret['outarr'];
}


/* DETECT DEPOSITS AND SCHEDULE */
$_DEPOSITS = array();
$_SCHEDULE = array();
for($c=0; $c < count($docInfo['mmr']); $c++)
{
 $item = $docInfo['mmr'][$c];
 if($item['expire_date'] == "0000-00-00")
  $_DEPOSITS[] = $item;
 else
  $_SCHEDULE[] = $item;
}

/* GET ATTACHMENTS */
$ret = GShell("dynattachments list -ap 'commercialdocs' -refid ".$docInfo['id'],$_REQUEST['sessid'],$_REQUEST['shellid']);
$docInfo['attachments'] = $ret['outarr']['items'];

//-------------------------------------------------------------------------------------------------------------------//
function makeExtraColumnEditFunc($column)
{
 global $_COLUMNS;
 $formulas = explode(";",$column['formula']);
 $ret = "";
 $columns = $_COLUMNS;
 $columns["extraqty"] = array("title"=>"XQTY");
 $columns["priceadjust"] = array("title"=>"PRICEADJ");
 
 for($j=0; $j < count($formulas); $j++)
 {
  $formula = $formulas[$j];
  reset($columns);
  while(list($k,$v) = each($columns))
  {
   $replace = "{".$k."}=";
   if(strpos($formula,$replace) !== false)
    $formula = "r.cell['".$k."'].setValue(".substr($formula, strpos($formula,$replace)+strlen($replace)).")";
   $replace = "{".$k."}";
   $formula = str_replace($replace, "r.cell['".$k."'].getValue()", $formula);
   $formula = str_replace("this","value",$formula);
  }
  $ret.= $formula.";\n";
 }
 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//
?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $docInfo['name']; ?></title></head>
<link rel='shortcut icon' href='share/images/favicon.png' />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/docinfo.css" type="text/css" />
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include($_BASE_PATH."var/templates/basicapp/index.php");
include($_BASE_PATH."var/objects/htmlgutility/menu.php");
include($_BASE_PATH."var/objects/gmutable/index.php");
include_once($_BASE_PATH."var/objects/dynrubricaedit/index.php");
include_once($_BASE_PATH."var/objects/guploader/index.php");
include_once($_BASE_PATH."include/layers.php");

?>
<body onload="bodyOnLoad()">
<?php
basicapp_header_begin();
?>
<table width='100%' border='0' cellspacing="0" cellpadding="0">
<tr><td style="padding-left:10px;">
	 <ul class='tiptop' id="doctypemenu">
	  <li><span><?php echo $_DOC_TYPENAME; ?></span></li>
	  <?php
	  if(strtoupper($_CAT_TAG) == "INVOICES")
	  {
	   echo "<li id='doctagname' style='cursor:pointer;' title='Clicca per modificare il tipo di fattura'><span style='font-size:14px;'>";
	   if(strtoupper($docInfo['tag']) == "DEFERRED")
		echo "Differita";
	   else
		echo "Accompagnatoria";
	   echo "<img src='".$_ABSOLUTE_URL."GCommercialDocs/img/tiptop-dnarr.png' class='ddmenu'/></span>";
	   ?>
	   	<ul class="submenu">
	 	 <li onclick="editDocTag()"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/invoice.png"/>Accompagnatoria</li>
	 	 <li onclick="editDocTag('DEFERRED')"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/invoice.png"/>Differita</li>
		</ul>
	   </li>
	   <?php
	  }
	  ?>
	  <li onclick='editDocNum()' style='cursor:pointer;' title="Clicca per modificare il numero di documento"><span>N. <b id='docnum'><?php echo $docInfo['code_num'].($docInfo['code_ext'] ? "/".$docInfo['code_ext'] : ""); ?></b></span></li>
	  <li onclick='editDocDate()' style='cursor:pointer;' title="Clicca per modificare la data del documento"><span><small style='font-size:14px;'>del</small> <b id='docdate'><?php echo date('d/m/Y',$docInfo['ctime']); ?></b></span></li>
	 </ul>
	</td>

	<td align="right">
	 <div class='hdrbtnsec' style="float:right;"><a href='#' onclick="printPreview()"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/print.png"/><br/>Stampa</a></div>
	 <?php if($_SHOW_PAYMENTSPAGE && ($docInfo['status'] < 8)) 
	 { 
	  ?>
	 <div class='hdrbtnsec' style="float:right;"><a href='#' onclick="pay()"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/pay.png"/><br/>Salda</a></div>
	  <?php 
	 }
	 ?></td><td width="340">
	 <div class='hdrbtnsec'>
		<ul class='basicbuttons' style="margin-top: 10px;">
		 <?php
		 if($docInfo['conv_doc_id'] || $docInfo['group_doc_id'])
		  echo "<li><a href='#' onclick='unlockDoc()'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/unlock.png' border='0'/>Sblocca</a></li>";
		 else
		  echo "<li><a href='#' onclick='saveDoc()'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/save.gif' border='0'/>Salva</a></li>";
		 ?>
		 <li><a href='#' onclick="abort()"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/exit.png" border='0'/>Chiudi</a></li>
		 <li style="margin-left:20px;"><a href='#' onclick="deleteDoc()"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/delete.png" border='0'/>Elimina</a></li>
		</ul>
	 </div>
	</td></tr>
</table>
<?php
basicapp_header_end();

basicapp_contents_begin("10px","87%");
//-------------------------------------------------------------------------------------------------------------------//
?>
<table width='100%' height='100%' cellspacing='0' cellpadding='0' border='0'>
<tr><td valign='top' width='160' style="border-right:1px solid #dadada;padding-right:10px;">
<!-- LEFT SPACE -->
<h3 class='rightsec-blue'><span class='title'><?php 
	switch(strtolower($_CAT_TAG))
	{
	 case 'vendororders' : case 'purchaseinvoices' : echo "FORNITORE"; break;
	 case 'agentinvoices' : echo "AGENTE"; break;
	 default : echo "CLIENTE"; break;
	}
	?></span></h3>
<div class="minisecgray"><i>Spett.le</i> <span class="rightlink" onclick="subjectChange()"><i>Cambia</i></span></div>
<div id="subjectname" class="titlename" style="margin-bottom:10px;" onclick="subjectInfo()"><?php echo html_entity_decode($docInfo['subject_name'],ENT_QUOTES,'UTF-8'); ?></div>
<div id="rubricaedit-container" <?php if($docInfo['subject_name']) echo "style='display:none;'"; ?>><input type="text" size="14" id="rubricaedit" value="<?php echo html_entity_decode($docInfo['subject_name'],ENT_QUOTES,'UTF-8'); ?>"/></div>

<i class='smallgray'>Destinazione documento</i><br/>
<div class="box" id="subjdefcontact"><?php 
	if(count($subjectInfo['contacts']))
	 echo $subjectInfo['contacts'][0]['address']."<br/>".$subjectInfo['contacts'][0]['city']." (".$subjectInfo['contacts'][0]['province'].")";
	?>
</div>
<br/>
<br/>
<div <?php if(!$_SHOW_SHIPPINGPAGE) echo "style='display:none;'"; ?>>
<h3 class='rightsec-blue'><span class='title'>DESTINAZ. MERCI</span></h3>
<div class="shipaddr" id="shiptoaddr"><?php 
	if($docInfo['ship_addr'])
	 echo $docInfo['ship_addr']."<br/>".$docInfo['ship_city']." (".$docInfo['ship_prov'].")";
	else
	 echo $subjectInfo['contacts'][0]['address']."<br/>".$subjectInfo['contacts'][0]['city']." (".$subjectInfo['contacts'][0]['province'].")";
	?>
	</div>
<span class="link" onclick="editShipAddr()"><i>Modifica</i></span><br/>

<br/>
<br/>
</div>

<?php
if(($_CAT_TAG == "VENDORORDERS") || ($_CAT_TAG == "PURCHASEINVOICES"))
{
 ?>
 <div>
 <h3 class='rightsec-blue' style='margin-bottom:2px'><span class='title'>RIF. DOC. FORNIT.</span></h3>
 <input type='text' class='edit' id='extdocref' style='width:156px' value="<?php echo $docInfo['ext_docref']; ?>"/>
 <br/><br/>
 </div>
 <?php
}
?>

<div>
<h3 class='rightsec-blue' style='margin-bottom:2px'><span class='title'>RIFERIMENTO</span></h3>
<select id='referencelist' style='width:156px'><option value='0'>&nbsp;</option>
<?php
for($c=0; $c < count($subjectInfo['references']); $c++)
{
 $refInfo = $subjectInfo['references'][$c];
 echo "<option value='".$refInfo['id']."'".($docInfo['reference_id'] == $refInfo['id'] ? " selected='selected'>" : ">")
	.$refInfo['name']." &nbsp;&nbsp;(".$refInfo['type'].")</option>";
}
?>
</select><br/><br/>
</div>

<?php
if($_CAT_TAG == "PREEMPTIVES")
{
 ?>
 <div>
 <h3 class='rightsec-blue' style='margin-bottom:2px'><span class='title'>TIPO DI PREVENTIVO</span></h3>
 <select id='preemptivetype' style='width:156px' onchange="preemptiveTypeChange(this)"><option value=''>Generico</option>
 <?php
 $prTypes = array("SALE"=>"Vendita", "CHARTER"=>"Noleggio", "MOUNTING"=>"Allestimento", "SPENDING"=>"Spesa", "SERVICE"=>"Prestazione");
 while(list($k,$v) = each($prTypes))
 {
  echo "<option value='".$k."'".($k == $docInfo['tag'] ? " selected='selected'>" : ">").$v."</option>";
 }
 ?>
 </select><br/><br/>
 </div>

 <div id='charterdates' <?php if($docInfo['tag'] != "CHARTER") echo "style='display:none'"; ?>>
 <h3 class='rightsec-blue' style='margin-bottom:2px'><span class='title'>DATA NOLEGGIO</span></h3>
  <span style='font-size:12px;'>dal:</span> <input id='charterdatefrom' type='text' class='edit' style='width:70px;margin-bottom:2px' value="<?php if($docInfo['charter_datefrom'] != '0000-00-00') echo date('d/m/Y',strtotime($docInfo['charter_datefrom'])); ?>"/><br/>
  <span style='font-size:12px;'>&nbsp;al:</span> <input id='charterdateto' type='text' class='edit' style='width:70px' value="<?php if($docInfo['charter_dateto'] != '0000-00-00') echo date('d/m/Y',strtotime($docInfo['charter_dateto'])); ?>"/>
  <br/>
  <br/>
 </div>


 <div>
 <h3 class='rightsec-blue' style='margin-bottom:2px'><span class='title'>DATA VALIDITA&lsquo;</span></h3>
 <span style='font-size:12px'>scade il: <input id='validitydate' type='text' class='edit' style='width:70px' value="<?php if($docInfo['validity_date'] != '0000-00-00') echo date('d/m/Y',strtotime($docInfo['validity_date'])); ?>"/>
 </div>

 <?php
}
?>

<div <?php if(!$_SHOW_PAYMENTSPAGE) echo "style='display:none;'"; ?>>
<h3 class='rightsec-blue'><span class='title'>MODALITA&rsquo; E PAGAM.</span></h3>
<div class="paymode" id="paymodemenu"><span><?php
	if($docInfo['paymentmode'])
	{
	 $ret = GShell("paymentmodes info -id `".$docInfo['paymentmode']."`");
	 if(!$ret['error'])
	 {
	  $paymentMode = $ret['outarr'];
	  echo $paymentMode['name'];
	 }
	 else
	  echo "&nbsp;";
	}
	?></span> <img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/blue-dnarr.png"/>
 <ul class="submenu" id="paymodemenu-list">
 <?php
 $ret = GShell("paymentmodes list");
 $list = $ret['outarr'];
 for($c=0; $c < count($list); $c++)
 {
  echo "<li onclick=\"setPaymentMode(".$list[$c]['id'].",this)\"><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/payment-methods.png'/>".$list[$c]['name']."</li>";
 }
 ?>
  <li class="separator">&nbsp;</li>
  <li onclick="configPaymentMode()"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/config.png"/>Configura</li>
 </ul>
</div>

<br/>
<br/>

<div class="minisecgray"><i>Acconti</i></div>
<table width='100%' border='0' cellspacing='0' cellpadding='0' class='smalltable' style="margin-top:3px;">
<?php
for($c=0; $c < count($_DEPOSITS); $c++)
{
 switch(strtolower($_CAT_TAG))
 {
  case 'agentinvoices' : case 'vendororders' : case 'purchaseinvoices' : case 'creditsnote' : echo "<tr><td><span class='gray11'><b>".number_format($_DEPOSITS[$c]['expenses'],2,',','.')." &euro;</b></span>"; break;
  default : echo "<tr><td><span class='gray11'><b>".number_format($_DEPOSITS[$c]['incomes'],2,',','.')." &euro;</b></span>"; break;
 }
 echo "<td align='right'>".date('d/m/Y',strtotime($_DEPOSITS[$c]['payment_date']))."</td></tr>";
}
?>
</table>

<br/>

<div class="minisecgray"><i>Scadenze</i></span></div>
<table id="schedule-table-small" width='100%' border='0' cellspacing='0' cellpadding='0' class='smalltable' style="margin-top:3px;">
<?php
for($c=0; $c < count($_SCHEDULE); $c++)
{
 if($_SCHEDULE[$c]['payment_date'] != "0000-00-00")
  continue;
 echo "<tr><td><span class='gray11'><b>".($c+1)."</b></span></td>";
 echo "<td align='center'>".date('d/m/Y',strtotime($_SCHEDULE[$c]['expire_date']))."</td>";
 echo "<td align='right'><span class='gray11'><b>".number_format($_SCHEDULE[$c]['incomes'],2,',','.')." &euro;</b></span></td></tr>";
}
?>
</table>
</div>
<!-- EOF - LEFT SPACE -->
</td><td valign='top' style="padding-left:10px;">
<?php
if($docInfo['trash'])
{
 ?>
 <div class='trash-warning'>Questo documento si trova nel cestino. Vuoi recuperarlo?&nbsp;&nbsp;<input type='button' value='Recupera' onclick='restoreDocument()'/></div>
 <?php
}
?>
<!-- CONTENTS -->
<ul class='maintab' style="margin-left:8px;">
 <li class='selected'><span class='title' onclick="showPage('home',this)">RIGHE DOCUMENTO</span></li>
 <li <?php if(!$_SHOW_SHIPPINGPAGE) echo "style='display:none;'"; ?>><span class='title' id='shipSpanPageBtn' onclick="showPage('shipping',this)">DESTINAZIONE MERCI</span></li>
 <li <?php if(!$_SHOW_PAYMENTSPAGE) echo "style='display:none;'"; ?>><span class='title' onclick="showPage('payments',this)">MODALITA&rsquo; DI PAGAMENTO</span></li>
 <li><span class='title' onclick="showPage('attachments',this)">ALLEGATI E NOTE</span></li>
</ul>

<!-- PAGE - HOME -->
<div class='tabpage' id="home-page">
 <table width='100%' border='0' cellspacing='0' cellpadding='0'>
 <tr><td style="padding-top:16px;">
 <ul class='basicmenu' id='mainmenu'>
  <li class='gray'><span>Menu</span>
	<ul class="submenu">
	 <?php
	 if(_userInGroup("gmart") && file_exists($_BASE_PATH."Products/index.php"))
	  echo "<li onclick=\"InsertRow('article')\"><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/add-product.png' width='16'/>Aggiungi nuovo articolo</li>";
	 if(_userInGroup("gserv") && file_exists($_BASE_PATH."Services/index.php"))
	  echo "<li onclick=\"InsertRow('service')\"><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/add-service.png' width='16'/>Aggiungi nuovo servizio</li>";
	 if(_userInGroup("gsupplies") && file_exists($_BASE_PATH."Supplies/index.php"))
	  echo "<li onclick=\"InsertRow('supply')\"><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/add-supply.png' width='16'/>Aggiungi altro tipo di fornitura</li>";
	 ?>
	 <li onclick="InsertRow('custom')"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/add-custom.png" width='16'/>Aggiungi riga personalizzata</li>
	 <li class="separator">&nbsp;</li>
	 <li onclick="InsertRow('note')"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/add-note.png" width='16'/>Aggiungi riga di nota</li>
	</ul>
  </li>

  <li class='lightgray'><span>Modifica</span>
	<ul class='submenu'>
	 <li id='cutmenubtn' class='disabled' onclick="cut()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/cut.gif"/><?php echo i18n("cut"); ?></li>
	 <li id='copymenubtn' class='disabled' onclick="copy()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/copy.png"/><?php echo i18n("copy"); ?></li>
	 <li id='pastemenubtn' class='disabled' onclick="paste()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/paste.gif"/><?php echo i18n("paste"); ?></li>
	 <li class='separator'>&nbsp;</li>
	 <li id='deletemenubtn' class='disabled' onclick="deleteSelectedItems()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/><?php echo i18n("Delete selected"); ?></li>
	</ul>
  </li>

  <li class='lightgray'><span>Visualizza</span>
	<ul class="submenu">
	 <?php
	 $_SHOW_COLUMN = array();
	 $defaultColumns = array(
		"code"=>array("title"=>"Codice", "default"=>true, "width"=>60, "sortable"=>true, "editable"=>true),
		"sn"=>array("title"=>"S.N.", "width"=>100, "sortable"=>true, "editable"=>true),
		"lot"=>array("title"=>"Lotto", "width"=>100, "sortable"=>true, "editable"=>true),
		"account"=>array("title"=>"Conto", "width"=>60, "sortable"=>true, "editable"=>true),
		"description"=>array("title"=>"Articolo / Descrizione", "default"=>true, "minwidth"=>250, "sortable"=>true, "editable"=>true),
		"qty"=>array("title"=>"Qta", "default"=>true, "width"=>40, "editable"=>true, "style"=>"text-align:center"),
		"units"=>array("title"=>"U.M.", "default"=>true, "width"=>40, "editable"=>true, "style"=>"text-align:center"),
		"unitprice"=>array("title"=>"Pr. Unit", "default"=>true, "width"=>60, "editable"=>true, "format"=>"currency", "decimals"=>$_DECIMALS),
		"weight"=>array("title"=>"Peso unit.", "width"=>60, "style"=>"text-align:center"),
		"discount"=>array("title"=>"Sconto", "default"=>true, "width"=>60, "editable"=>true, "format"=>"currency percentage"),
		"discount2"=>array("title"=>"Sconto2", "width"=>60, "editable"=>true, "format"=>"percentage"),
		"discount3"=>array("title"=>"Sconto3", "width"=>60, "editable"=>true, "format"=>"percentage"),
		"vat"=>array("title"=>"I.V.A.", "default"=>true, "width"=>40, "editable"=>true, "format"=>"percentage", "style"=>"text-align:left"),
		"price"=>array("title"=>"Totale", "default"=>true, "width"=>120, "minwidth"=>100, "style"=>"text-align:center"),
		"vatprice"=>array("title"=>"Tot. + IVA", "width"=>120, "minwidth"=>100, "style"=>"text-align:center"),
		"pricelist"=>array("title"=>"Listino", "width"=>120, "minwidth"=>100, "style"=>"text-align:center")
		);

	 $extracolumns = $_COMPANY_PROFILE['extracolumns'];
	 $_COLUMNS = array();
	 while(list($k,$v) = each($defaultColumns))
	 {
	  $_COLUMNS[$k] = $v;
	  for($c=0; $c < count($extracolumns); $c++)
	  {
	   $itm = $extracolumns[$c];
	   if($itm['after'] == $k)
	    $_COLUMNS[$itm['tag']] = array("title"=>$itm['title'], "default"=>true, "width"=>60, "editable"=>true);
	  }
	 }
	 reset($_COLUMNS);
	 while(list($k,$v) = each($_COLUMNS))
	 {
	  echo "<li><input type='checkbox' onclick=\"tb.showHideColumn('".$k."',this.checked)\"";
	  if($_COOKIE['GCD-COL-'.strtoupper($k)] == "ON")
	  {
	   echo " checked='true'";
	   $_SHOW_COLUMN[$k] = true;
	  }
	  else if(($_COOKIE['GCD-COL-'.strtoupper($k)] != "OFF") && ($v['default'] == true))
	  {
	   echo " checked='true'";
	   $_SHOW_COLUMN[$k] = true;
	  }
	  echo "/>".$v['title']."</li>";
	 }
	 ?>
	 <li class='separator'>&nbsp;</li>
	 <li onclick="saveColumnsSettings(this.parentNode)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/save.gif"/><?php echo i18n("Save columns settings"); ?></li>
	</ul>
  </li>

  <li class='blue' id='selectionmenu' style='visibility:hidden;'><span><img src="<?php echo $_ABSOLUTE_URL; ?>var/templates/basicapp/img/checkbox.png" border='0'/>Selezionati</span>
	<ul class="submenu">
	 <li>Inverti selezione</li>
	 <li>Annulla selezione</li>
	 <li class='separator'></li>
	 <li onclick="IncludeItemDesc()">Includi descrizione articolo</li>
	 <li class='separator'></li>
	 <li onclick="deleteSelectedItems()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/>Elimina selezionati</li>

	</ul>
  </li>
 </ul>

 </td><td width='50%'>
 <ul class="hotbtns">
  <li class="left-brachet">&nbsp;</li>
  <?php
  if(_userInGroup("gmart") && file_exists($_BASE_PATH."Products/index.php"))
   echo "<li onclick=\"InsertRow('article')\"><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/add-product.png' title='Aggiungi articolo'/></li>";
  if(_userInGroup("gserv") && file_exists($_BASE_PATH."Services/index.php"))
   echo "<li onclick=\"InsertRow('service')\"><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/add-service.png' title='Aggiungi servizio'/></li>";
  if(_userInGroup("gsupplies") && file_exists($_BASE_PATH."Supplies/index.php"))
   echo "<li onclick=\"InsertRow('supply')\"><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/add-supply.png' title='Aggiungi altre forniture'/></li>";
  ?>
  <li onclick="InsertRow('custom')"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/add-custom.png" title="Aggiungi riga personalizzata"/></li>
  <li class="separator">&nbsp;</li>
  <li onclick="InsertRow('note')"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/add-note.png" title="Aggiungi riga di nota"/></li>
  <li onclick="InsertMessage(this)"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/message.png" title="Aggiungi messaggio predefinito"/></li>
  <li class="right-brachet">&nbsp;</li>
 </ul>
 </td></tr></table>

 <div class="gmutable" style="height:90%;margin-top:5px;">
 <table id='doctable' class="gmutable" width="100%" cellspacing="0" cellpadding="0" border="0" style="display:none;">
 <tr><th width='40'><input type="checkbox" onchange="tb.selectAll(this.checked)"/></th>
	<?php
	reset($_COLUMNS);
	while(list($k,$v) = each($_COLUMNS))
	{
	 echo "<th id='".$k."'";
	 if($v['width']) echo " width='".$v['width']."'";
	 if($v['sortable']) echo " sortable='true'";
	 if($v['editable']) echo " editable='true'";
	 if($v['minwidth']) echo " minwidth='".$v['minwidth']."'";
	 if($v['format']) echo " format='".$v['format']."'";
	 if($v['decimals']) echo " decimals='".$v['decimals']."'";
	 if(!$_SHOW_COLUMN[$k])
	  echo " style='display:none;".$v['style']."'";
	 else if($v['style'])
	  echo " style='".$v['style']."'";
	 echo ">".strtoupper($v['title'])."</th>";
	}
    // colonne extra_qty e price_adjust //
	echo "<th id='extraqty' width='40' style='display:none;'>XQTY</th>";
	echo "<th id='priceadjust' width='40' style='display:none;'>PRICEADJ</th>";
	?>
 </tr>

 <?php
 /*$subtot = 0;
 $subtotVI = 0;
 $totAmount = 0;
 $totVAT = 0;
 $totDiscount = 0;
 $uncondiscPerc = $docInfo['uncondisc_perc'];
 $uncondiscAmount = $docInfo['uncondisc_amount'];
 $unconditionalDiscount = 0;*/
 $totWeight = 0;
 $weightMul = array('mg'=>0.000001, 'g'=>0.001, 'hg'=>0.1, 'kg'=>1, 'q'=>100, 't'=>1000);

 $_VAT_RATES = array();

 for($c=0; $c < count($docInfo['elements']); $c++)
 {
  $itm = $docInfo['elements'][$c];
  echo "<tr id='".$itm['id']."' type='".$itm['type']."' refap='".$itm['ref_ap']."' refid='".$itm['ref_id']."' vatid='".$itm['vatid']."' vattype='"
	.$itm['vattype']."'><td><input type='checkbox'/></td>";
  if(strtolower($itm['type']) == "note")
   echo "<td colspan='100'><span class='graybold'>".$itm['desc']."</span></td>";
  else
  {
   $qty = $itm['qty'] * ($itm['extraqty'] ? $itm['extraqty'] : 1);

   $discount = $itm['discount_inc'] ? $itm['discount_inc'] : 0;
   $discount2 = 0;
   $discount3 = 0;

   if($itm['discount_perc'])
    $discount = $itm['price'] ? ($itm['price']/100)*$itm['discount_perc'] : 0;
   if($itm['discount2'] && $itm['price'])
	$discount2 = (($itm['price']-$discount)/100) * $itm['discount2'];
   if($itm['discount2'] && $itm['discount3'] && $itm['price'])
	$discount3 = ((($itm['price']-$discount)-$discount2)/100) * $itm['discount3'];

   $amount = ((($itm['price']-$discount)-$discount2)-$discount3) * $qty;
   $total = $amount;

   /*$vatRate = $itm['vatrate'];
   $VAT = $total ? ($total/100)*$vatRate : 0;
   if($vatRate && !in_array($vatRate, $_VAT_RATES))
	$_VAT_RATES[] = $vatRate;*/

   //$totalVI = $total ? $total + $VAT : 0;

   //$subtot+= $total;
   //$totAmount+= $amount;
   //$totVAT+= $VAT;
   //$subtotVI+= $totalVI;
   //$totDiscount+= (($discount+$discount2+$discount3) * $qty);

   $totWeight+= (($itm['weight']*$qty)*$weightMul[$itm['weightunits']]);

   reset($_COLUMNS);
   while(list($k,$v) = each($_COLUMNS))
   {
	switch($k)
	{
	 case 'code' : echo "<td><span class='graybold'>".$itm['code']."</span></td>"; break;
	 case 'sn' : case 'serialnumber' : echo "<td><span class='graybold'>".$itm['serialnumber']."</span></td>"; break;
	 case 'lot' : echo "<td><span class='graybold'>".$itm['lot']."</span></td>"; break;
	 case 'account' : echo "<td><span class='graybold'>&nbsp;</span></td>"; break; /* TODO: da sistemare */
	 case 'description' : echo "<td><span class='graybold doubleline'>".$itm['name']."</span></td>"; break;
	 case 'qty' : echo "<td><span class='graybold 13 center'>".$itm['qty']."</span></td>"; break;
	 case 'units' : echo "<td><span class='graybold 13 center'>".$itm['units']."</span></td>"; break;
	 case 'unitprice' : echo "<td><span class='graybold'>".number_format($itm['price'],$_DECIMALS,',','.')."</span></td>"; break;
	 case 'discount' : {
	     if($itm['discount_perc'])
	      echo "<td><span class='graybold'>".$itm['discount_perc']."%</span></td>";
	     else if($itm['discount_inc'])
	      echo "<td><span class='graybold'>&euro;. ".number_format($itm['discount_inc'],$_DECIMALS,',','.')."</span></td>";
	     else
	      echo "<td><span class='graybold'>&nbsp;</span></td>";
		} break;
	 case 'discount2' : echo "<td><span class='graybold'>".($itm['discount2'] ? $itm['discount2'] : "0")."%</span></td>"; break;
	 case 'discount3' : echo "<td><span class='graybold'>".($itm['discount3'] ? $itm['discount3'] : "0")."%</span></td>"; break;
	 case 'vat' : echo "<td><span class='graybold center'>".$itm['vatrate']."%</span></td>"; break;
	 case 'price' : echo "<td><span class='eurogreen'><em>&euro;</em>".number_format($amount,2,',','.')."</span></td>"; break;
	 case 'vatprice' : echo "<td><span class='eurogreen'><em>&euro;</em>".number_format($amount+$itm['vat'],2,',','.')."</span></td>"; break;
	 case 'pricelist' : echo "<td pricelistid='".$itm['pricelist_id']."'><span class='graybold'>".($itm['pricelist_id'] ? $_PRICELIST_BY_ID[$itm['pricelist_id']]['name'] : $_PLINFO['name'])."</span></td>"; break;
	 case 'weight' : echo "<td><span class='graybold 13 center'>".$itm['weight']." ".$itm['weightunits']."</span></td>"; break;
	 default : echo "<td><span class='graybold'>".$itm[$k]."</span></td>"; break;
	}
   }
   /* colonne extra_qty e price_adjust */
   echo "<td><span class='graybold 13 center'>".$itm['extraqty']."</span></td>";
   echo "<td><span class='graybold 13 center'>".$itm['priceadjust']."</span></td>";
  }
  echo "</tr>";
 }
 ?>
 </table>
 </div>

 <table class="docfooter-results" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:12px;">
  <tr><th class="blue" style="text-align:left" rowspan="2"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/discount-button.png" style="cursor:pointer;margin-left:3px" onclick="showUnconditionalDiscountCloud(this)"/></th>
	  <th class="blue" width="22" valign="middle" rowspan="2"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/eye.png" style="cursor:pointer" onclick="showColumnsCloud(this)"/></th>
	  <th class="blue" width="22" valign="middle" rowspan="2"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/calc.png" style="cursor:pointer" onclick="showTotalsCloud(this)"/></th>
	  <th class="blue" width="70" id="doctot-weight-th" <?php if($_COOKIE['GCD-DOCTOTCOL-WEIGHT'] != "ON") echo "style='display:none'"; ?>>PESO (kg)</th>
	  <th class="blue" width="110">IMPONIBILE</th>
	  <th class="blue" width="80">TOT. SCONTI</th>
	  <?php
	  if($_CAT_TAG == "INVOICES")
	  {
	   /*if($_COMPANY_PROFILE['accounting']['contr_cassa_prev'])
		echo "<th class='blue' width='110'>CASSA PREV.</th>";*/
	   if($_COMPANY_PROFILE['accounting']['rivalsa_inps'])
		echo "<th class='blue' width='110'>RIV. INPS</th>";
	   /*if($_COMPANY_PROFILE['accounting']['rit_enasarco'])
		echo "<th class='blue' width='110'>RIT. ENASARCO</th>";*/
	  }
	  ?>
	  <th class="blue" width="110">I.V.A.</th>
	  <th class="green" width="110">TOTALE</th>
	  <?php
	  /* include la ritenuta d'acconto */
	  if(($_CAT_TAG == "INVOICES") && $_COMPANY_PROFILE['accounting']['rit_acconto'])
	   echo "<th class='blue' width='110'>RIT. ACCONTO</th>";
	  else
	   echo "<th class='blue' width='110'>BOLLI</th>";
	  echo "<th class='green' width='110'>NETTO A PAGARE</th>";
	  ?>
  </tr>
  <tr>
	  <td class="blue" id="doctot-weight" style="font-size:13px;text-align:center;<?php if($_COOKIE['GCD-DOCTOTCOL-WEIGHT'] != 'ON') echo 'display:none'; ?>"><?php echo sprintf("%.2f",$totWeight); ?></td>
	  <td class="blue" id="doctot-amount"><em>&euro;</em><?php echo number_format($docInfo['amount'],2,',','.'); ?></td>
	  <td class="blue" id="doctot-discount"><em>&euro;</em><?php echo number_format(0,2,',','.'); ?></td>
	  <?php
	  if($_CAT_TAG == "INVOICES")
	  {
	   /*if($_COMPANY_PROFILE['accounting']['contr_cassa_prev'])
		echo "<td class='blue' id='doctot-cassaprev'><em>&euro;</em>".number_format($totCassaPrev,2,',','.')."</td>";*/
	   if($_COMPANY_PROFILE['accounting']['rivalsa_inps'])
		echo "<td class='blue' id='doctot-rivinps'><em>&euro;</em>".number_format($docInfo['tot_rinps'],2,',','.')."</td>";
	   /*if($_COMPANY_PROFILE['accounting']['rit_enasarco'])
		echo "<td class='blue' id='doctot-ritenasarco'><em>&euro;</em>".number_format($totRitEnasarco,2,',','.')."</td>";*/
	  }
	  ?>
	  <td class="blue" id="doctot-vat"><em>&euro;</em><?php echo number_format($docInfo['vat'],2,',','.'); ?></td>
	  <td class="green" id="doctot-total"><em>&euro;</em><?php echo number_format($docInfo['total'],2,',','.'); ?></td>
	  <?php
	  /* include la ritenuta d'acconto */
	  if(($_CAT_TAG == "INVOICES") && $_COMPANY_PROFILE['accounting']['rit_acconto'])
	   echo "<td class='blue' id='doctot-ritacconto'><em>&euro;</em>".number_format($docInfo['tot_rit_acc'],2,',','.')."</td>";
	  else
	   echo "<td class='blue' id='doctot-stamp'><em>&euro;</em>".number_format($docInfo['stamp'],2,',','.')."</td>";
	  echo "<td class='green' id='doctot-netpay'><em>&euro;</em>".number_format($docInfo['tot_netpay'],2,',','.')."</td>";
	  ?>

  </tr>
 </table>

 <!-- PREDEFINED MESSAGES -->

 <div id="predefmsg" class="predefmsg" style="display:none">
  <div class="predefmsg-header">MESSAGGI PREDEFINITI</div>
  <div id="predefmsg-container" class="predefmsg-container">
   <table cellspacing="0" cellpadding="0" border="0" class="predefmsg-list" id="predefmsg-list">
	<?php
	$ret = GShell("commercialdocs predefmsg-list");
	$list = $ret['outarr'];
	for($c=0; $c < count($list); $c++)
	{
	 echo "<tr id='".$list[$c]['id']."'><td onclick='insertPredefMsg(this)'>".$list[$c]['text']."</td><td width='16'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/delete.png' onclick='deletePredefMsg(this)' style='cursor:pointer'/></td></tr>";
	}
	?>
   </table>


  </div>
  <div id="predefmsg-addmsg"><a href='#' class='addmsgbtn' onclick="addNewPredefMsg()">Aggiungi un nuovo messaggio</a></div>
  <div id="predefmsg-newmsg" style="display:none">
   <i>Inserisci qui il testo del nuovo messaggio</i>
   <textarea class="predefmsg-textarea" id="predefmsg-edit"></textarea>
   <div class="predefmsg-newmsg-footer">
   <a href='#' class='predefmsg-btncanc' onclick="predefmsgnew_abort()">Annulla</a>
   <a href='#' class='predefmsg-btnsubmit' onclick="predefmsgnew_submit()">OK</a>
   </div>
  </div>
 </div>


 <!-- SHOWCOLUMNS CLOUD -->
 <div id="showcolumnscloud" class="showcolumnscloud" style="display:none">
  <div class="showcolumnscloud-top"><div class="showcolumnscloud-header">Colonne visualizzabili</div></div>
  <div class="showcolumnscloud-body"><div class="showcolumnscloud-container">
   <input type='checkbox' onclick="showTotCol('weight',this)" <?php if($_COOKIE['GCD-DOCTOTCOL-WEIGHT'] == "ON") echo "checked='true'"; ?>/>Peso<br/>
  </div></div>
  <div class="showcolumnscloud-footer">&nbsp;</div>
 </div>

 <!-- UNCONDITIONAL DISCOUNT CLOUD -->
 <div id="unconditionaldiscountcloud" class="showcolumnscloud" style="display:none">
  <div class="showcolumnscloud-top"><div class="showcolumnscloud-header">Includi spese</div></div>
  <div class="showcolumnscloud-body"><div class="showcolumnscloud-container">
  <div style="overflow:auto;height:150px;width:310px" class="gmutable">
  <table width="100%" cellspacing="0" cellpadding="0" border="0" class="gmutable" id="expensestable" style="margin-left:5px">
  <tr><th width='20'><input type="checkbox" onchange="expTB.selectAll(this.checked)"/></th>
	  <th width='160' id='name' editable='true'>Descrizione</th>
	  <th width='60' id='vat' format='percentage' editable='true'>Aliq. IVA</th>
	  <th width='60' id='amount' format='currency' editable='true'>Importo</th></tr>
  <?php
  $expcount = 0;
  for($c=1; $c < 4; $c++)
  {
   if($docInfo["exp".$c."vatid"] && $docInfo["exp".$c."amount"])
   {
	echo "<tr vatid='".$docInfo["exp".$c."vatid"]."' vattype='".$_VAT_BY_ID[$docInfo["exp".$c."vatid"]]['type']."'><td><input type='checkbox'/></td>";
	echo "<td><span class='graybold'>".$docInfo["exp".$c."name"]."</span></td>";
	echo "<td><span class='graybold 13 center'>".$_VAT_BY_ID[$docInfo["exp".$c."vatid"]]['percentage']."%</span></td>";
	echo "<td><span class='graybold right'>".number_format($docInfo["exp".$c."amount"],2,",",".")."</span></td></tr>";
	$expcount++;
   }
  }
  if(!$expcount) // leave one empty row
   echo "<tr><td><input type='checkbox'/></td><td><span class='graybold'></span></td> <td><span class='graybold 13 center'></span></td> <td><span class='graybold right'></span></td></tr>";
  ?>
  </table>
  </div>
  <a href='#' onclick='addNewExpenses()' class='smallblue' style='padding-left:10px;'>aggiungi nuova spesa</a>
  <a href='#' onclick='deleteSelectedExpenses()' class='smallred' style='padding-right:10px;float:right;'>elimina spese selezionate</a>
  <div class="showcolumnscloud-header" style='margin:0px;margin-top:20px'>Applica sconti e abbuoni</div>
   <table border='0' class='smalltable'>
    <tr><td><b>Sconto:</b> </td><td>
		<input type='text' class='edit' style='width:30px' id='globaldisc_perc1' value="<?php echo $docInfo['discount']; ?>" onfocus="ACTIVE_GMUTABLE=null" onchange='updateTotals()'/> <b>% + </b>
		<input type='text' class='edit' style='width:30px' id='globaldisc_perc2' value="<?php echo $docInfo['discount2']; ?>" onfocus="ACTIVE_GMUTABLE=null" onchange='updateTotals()'/> <b>%</b></td></tr>
	<tr><td><b>Incondizionato:</b> </td>
		<td><input type='text' class='edit' style='width:50px' id='unconditional_discount' value="<?php echo number_format($docInfo['uncondisc'],2,',','.'); ?>" onfocus="ACTIVE_GMUTABLE=null" onchange="this.value=formatCurrency(parseCurrency(this.value));updateTotals()"/> <b>&euro;</b></td></tr>
	<tr><td><b>Abbuono:</b> </td>
		<td><input type='text' class='edit' style='width:50px' id='rebate' value="<?php echo number_format($docInfo['rebate'],2,',','.'); ?>" onfocus="ACTIVE_GMUTABLE=null" onchange="this.value=formatCurrency(parseCurrency(this.value));updateTotals()"/> <b>&euro;</b></td></tr>
	<tr><td><b>Bolli:</b> </td>
		<td><input type='text' class='edit' style='width:50px' id='stamp' value="<?php echo number_format($docInfo['stamp'],2,',','.'); ?>" onfocus="ACTIVE_GMUTABLE=null" onchange="this.value=formatCurrency(parseCurrency(this.value));updateTotals()"/> <b>&euro;</b></td></tr>
   </table>

  </div></div>
  <div class="showcolumnscloud-footer">&nbsp;</div>
 </div>

 <!-- TOTALS CLOUD -->
 <div id="showtotalscloud" class="showcolumnscloud" style="display:none">
  <div class="showcolumnscloud-top"><div class="showcolumnscloud-header">Riepilogo dei conteggi</div></div>
  <div class="showcolumnscloud-body"><div class="showcolumnscloud-container">

   <table width='100%' cellspacing='0' cellpadding='0' border='0' class='totalscloud-vatlist' id='totalscloud-vatlist'>
   <tr><th>ALIQ.</th>
	   <th>IMP. LORDO</th>
	   <th>IMP. SCONTATO</th>
	   <th>IMPOSTA</th></tr>

   </table>

   <table width='100%' cellspacing='0' cellpadding='0' border='0' class='totalscloud-summary' style='margin-top:10px'>
	<tr><td>Totale merce</td>			<td align='right' id='summary-total-goods'>0,00 &euro;</td>
		<td width='20'>&nbsp;</td>
		<td>Totale spese</td>			<td align='right' id='summary-total-expenses'>0,00 &euro;</td></tr>		

	<tr><td>Merce scontata</td>			<td align='right' id='summary-discounted-goods'>0,00 &euro;</td>
		<td width='20'>&nbsp;</td>
		<td>Rit. d&lsquo;acconto</td>	<td align='right' id='summary-ritenuta-acconto'>0,00 &euro;</td></tr>

	<tr><td>Sconto incond.</td>			<td align='right' id='summary-unconditional-discount'>0,00 &euro;</td>
		<td width='20'>&nbsp;</td>
		<td>Enasarco</td>				<td align='right' id='summary-enasarco'>0,00 &euro;</td></tr>

	<tr><td>Rivalsa INPS</td>			<td align='right' id='summary-rivinps'>0,00 &euro;</td>
		<td width='20'>&nbsp;</td>
		<td>Abbuoni</td>				<td align='right' id='summary-rebate'>0,00 &euro;</td></tr>

	<tr><td>Imponibile</td>				<td align='right' id='summary-taxable'>0,00 &euro;</td>
		<td width='20'>&nbsp;</td>
		<td>Cassa prev.</td>			<td align='right' id='summary-cassa-prev'>0,00 &euro;</td></tr>

	<tr><td>Tot. IVA</td>				<td align='right' id='summary-total-vat'>0,00 &euro;</td>
		<td width='20'>&nbsp;</td>
		<td>Bolli</td>					<td align='right' id='summary-stamp'>0,00 &euro;</td></tr>

	<tr><td><b>Totale fattura</b></td>	<td align='right'><b id='summary-total-invoice'>0,00 &euro;</b></td>
		<td width='20'>&nbsp;</td>
		<td><b>Netto a pagare</b></td>	<td align='right'><b id='summary-net-pay'>0,00 &euro;</td></tr>
   </table>

  </div></div>
  <div class="showcolumnscloud-footer">&nbsp;</div>
 </div>


</div>
<!-- EOF - PAGE HOME -->

<!-- PAGE - SHIPPING -->
<div class='tabpage' id="shipping-page" style="display:none;">
<table width="100%" cellspacing="8" cellpadding="0" border="0" class="detailstable">
<tr><th width="40%">INDIRIZZO DI DESTINAZIONE MERCI</th><th>DETTAGLI SUL TRASPORTO</th></tr>
<tr><td valign="top" class="section">
	 <!-- INDIRIZZO DI DESTINAZIONE MERCI -->
	 <table class="sectiontable" width="100%" cellspacing="4" cellpadding="4" border="0">
	 <tr><td colspan="2" style="border-bottom:1px solid #dadada;"><b><i>Seleziona un indirizzo:</i></b> <select id="shipping-contactselect" style="width:160px;" onchange="_updateShippingContact(this.value)">
		 <?php
		 for($c=0; $c < count($subjectInfo['contacts']); $c++)
		 {
		  echo "<option value='".$subjectInfo['contacts'][$c]['id']."'".($subjectInfo['contacts'][$c]['id'] == $docInfo['ship_contact_id'] ? " selected='selected'>" : ">").$subjectInfo['contacts'][$c]['label']."</option>";
		 }
		 ?>
		</select></td></tr>
	 <tr><td colspan="2">
		<i>Destinatario (cognome e nome / ragione sociale)</i><br/>
		<input type="text" id="ship-subject" style="width:90%" value="<?php echo $docInfo['ship_recp'] ? $docInfo['ship_recp'] : $subjectInfo['contacts'][0]['name']; ?>"/></td></tr>
	 <tr><td colspan="2">
		<i>Indirizzo (via, piazza, ...)</i><br/>
		<input type="text" id="ship-address" style="width:70%" value="<?php echo $docInfo['ship_addr'] ? $docInfo['ship_addr'] : $subjectInfo['contacts'][0]['address']; ?>"/></td></tr>
	 <tr><td><i>Citt&agrave;</i><br/><input type="text" id="ship-city" style="width:80%" value="<?php echo $docInfo['ship_city'] ? $docInfo['ship_city'] : $subjectInfo['contacts'][0]['city']; ?>"/></td>
		 <td><i>C.A.P.</i><i style='margin-left:30px;'>Prov.</i><br/>
		<input type="text" style='width:50px' id="ship-zipcode" value="<?php echo $docInfo['ship_zip'] ? $docInfo['ship_zip'] : $subjectInfo['contacts'][0]['zipcode']; ?>"/>&nbsp;&nbsp;<input type="text" style='width:30px' id="ship-prov" value="<?php echo $docInfo['ship_prov'] ? $docInfo['ship_prov'] : $subjectInfo['contacts'][0]['province']; ?>"/></td></tr>
	 <tr><td colspan="2"><i>Paese:</i> 
		<select id="ship-country">
 		 <?php
		 for($c=0; $c < count($_COUNTRIES); $c++)
		 {
		  echo "<option value='".$_COUNTRIES[$c]['code']."'>".$_COUNTRIES[$c]['name']."</option>";
		 }
		 ?>
		</select></td></tr>
	 </table>
	</td><td valign="top" class="section">
	<!-- DETTAGLI SUL TRASPORTO -->
	<table class="sectiontable" width="100%" cellspacing="4" cellpadding="4" border="0">
	<tr><td colspan='3' style="border-bottom:1px solid #dadada;" id="trans-methods"><b><i>Trasporto a mezzo:</i></b>&nbsp;
		<input type='radio' name='transport-method' <?php if($docInfo['trans_method'] == 0) echo "checked='true'"; ?>/>Mittente&nbsp;
		<input type='radio' name='transport-method' <?php if($docInfo['trans_method'] == 1) echo "checked='true'"; ?>/>Destinatario&nbsp;
		<input type='radio' name='transport-method' <?php if($docInfo['trans_method'] == 2) echo "checked='true'"; ?>/>Vettore
		</td></tr>
	<tr><td colspan='2'>
		<i class='blue'>Vettore / Conducente</i><br/>
		<input type='text' id="trans_shipper" style="width:90%" value="<?php echo $docInfo['trans_shipper']; ?>"/></td>
		<td width="100"><i class='blue'>Targa</i><br/><input type='text' style='width:80px' id="trans_numplate" value="<?php echo $docInfo['trans_numplate']; ?>"/></td></tr>
	<tr><td><i class='blue'>Data e ora consegna</i><br/>
		<input type='text' style='width:90px' id="trans_date" value="<?php if($docInfo['trans_datetime']) echo date('d/m/Y',$docInfo['trans_datetime']); ?>"/> <input type='text' style='width:50px' id="trans_time" value="<?php if($docInfo['trans_datetime']) echo date('H:i',$docInfo['trans_datetime']); ?>"/></td>
		<td colspan='2'><i class='blue'>Causale</i><br/><input type='text' id="trans_causal" style='width:90%' value="<?php echo $docInfo['trans_causal']; ?>"/></td></tr>
	<tr><td><i class='blue'>Aspetto esteriore dei beni</i><br/><input type='text' id="trans_aspect" style='width:80%' value="<?php echo $docInfo['trans_aspect']; ?>"/></td>
		<td width="145"><i class='blue'>N. colli</i><i class='blue' style="margin-left:30px;">Peso</i><br/>
		<input type='text' style='width:50px' id="trans_num" value="<?php echo $docInfo['trans_num']; ?>"/> <input type='text' id="trans_weight" style='width:50px' value="<?php echo $docInfo['trans_weight']; ?>"/>Kg</td>
		<td><i class='blue'>Porto</i><br/><input type='text' style='width:80px' id="trans_freight" value="<?php echo $docInfo['trans_freight']; ?>"/></td></tr>
	
	</table>
	</td></tr>
</table>
</div>
<!-- EOF - PAGE SHIPPING -->

<!-- PAGE - PAYMENTS -->
<div class='tabpage' id="payments-page" style="display:none;">
<table width="800" align="center" cellspacing="8" cellpadding="0" border="0" class="detailstable">
<tr><th>MODALITA&rsquo; DI PAGAMENTO</th><th style="text-align:right"><span style="font-size:14px;color:#333333">Totale documento:</span>&nbsp;&nbsp;&nbsp;<span style="font-size:16px;color:#013397;" id="doc_tot"><?php echo number_format(0,2,',','.'); ?> &euro;</span></th></tr>
<tr><td valign="top" class="section" colspan="2">
	<table class="sectiontable" width="100%" cellspacing="4" cellpadding="4" border="0">
	<tr><td colspan="2" style="border-bottom:1px solid #dadada;"><i><b>Seleziona la modalit&agrave; di pagamento:</b></i> 
		<select id="paymentmode-select" style="width:180px;" onchange="_paymentModeSelectChange(this)">
		<?php
		$ret = GShell("paymentmodes list");
		$list = $ret['outarr'];
		for($c=0; $c < count($list); $c++)
		 echo "<option value='".$list[$c]['id']."'".($docInfo['paymentmode'] == $list[$c]['id'] ? " selected='selected'>" : ">").$list[$c]['name']."</option>";
		?>
		</select>&nbsp;&nbsp;&nbsp;<i>Banca d&lsquo;appoggio:</i> <select id="banksupport-select" style="width:250px;">
		<?php
		//for($c=0; $c < count($_BANKS); $c++)
		// echo "<option value='".$c."'".($docInfo['banksupport_id'] == $c ? " selected='selected'>" : ">").$_BANKS[$c]['name']."</option>";
		for($c=0; $c < count($subjectInfo['banks']); $c++)
		 echo "<option value='".$subjectInfo['banks'][$c]['id']."'"
			.($docInfo['banksupport_id'] == $subjectInfo['banks'][$c]['id'] ? " selected='selected'>" : ">")
			.$subjectInfo['banks'][$c]['name']."</option>";
		?>
		</select></td></tr>
	<tr><td width='250' valign="top" style="border-right:1px solid #dadada;">
		<table class="smallblue" width="100%" cellspacing="0" cellpadding="2" border="0" id="deposits-table">
		<tr><th colspan="2">Acconti</th><th width='22'>&nbsp;</th></tr>
		<?php
		$totDeposits = 0;
		for($c=0; $c < count($_DEPOSITS); $c++)
		{
		 switch(strtolower($_CAT_TAG))
		 {
		  case 'agentinvoices' : case 'vendororders' : case 'purchaseinvoices' : case 'creditsnote' : {
			 echo "<tr id='mmr-".$_DEPOSITS[$c]['id']."'><td><i>&euro;</i> <input type='text' size='6' value='".number_format($_DEPOSITS[$c]['expenses'],2,',','.')."' onchange='updateTotDeposits(this.parentNode.parentNode)'/></td>";
			 echo "<td><i>Data</i> <input type='text' size='7' value='".date('d/m/Y',strtotime($_DEPOSITS[$c]['payment_date']))."' onchange='depositDateChanged(this)'/></td>";
			 echo "<td><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/delete.gif' style='cursor:pointer' onclick='deleteDepositItem(this)'/></td></tr>";
			 $totDeposits+= $_DEPOSITS[$c]['expenses'];
			} break;
		  default : {
			 echo "<tr id='mmr-".$_DEPOSITS[$c]['id']."'><td><i>&euro;</i> <input type='text' size='6' value='".number_format($_DEPOSITS[$c]['incomes'],2,',','.')."' onchange='updateTotDeposits(this.parentNode.parentNode)'/></td>"; 
			 echo "<td><i>Data</i> <input type='text' size='7' value='".date('d/m/Y',strtotime($_DEPOSITS[$c]['payment_date']))."' onchange='depositDateChanged(this)'/></td>";
			 echo "<td><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/delete.gif' style='cursor:pointer' onclick='deleteDepositItem(this)'/></td></tr>";
			 $totDeposits+= $_DEPOSITS[$c]['incomes'];
			} break;
		 }
		}
		?>
		</table>

		</td><td valign="top">
		<table class="smallblue" cellspacing="0" cellpadding="2" border="0" width="520" id='schedule-table'>
		<tr><th colspan="3">Scadenze</th><th width='22'>&nbsp;</th></tr>
		<?php
		$totScedule=0;
		$totPaidSchedule=0;
		for($c=0; $c < count($_SCHEDULE); $c++)
		{
		 echo "<tr id='mmr-".$_SCHEDULE[$c]['id']."'><td width='190'><b>".($c+1)."</b> <input type='text' style='width:90px' value='".date('d/m/Y',strtotime($_SCHEDULE[$c]['expire_date']))."' onchange='scheduleDateChange(this)'/> <i style='font-size:10px;'>(gg/mm/aaaa)</i></td>";
		 echo "<td><input type='text' style='width:60px' value='".number_format($_SCHEDULE[$c]['incomes'],2,',','.')."' onchange='scheduleAmountChange(this)'/><i>&euro;</i></td>";
		 if($_SCHEDULE[$c]['payment_date'] != "0000-00-00")
		 {
		  echo "<td width='190'><input type='checkbox' checked='true' onchange='schedulePaidChange(this)'/>Pagato&nbsp;&nbsp;&nbsp;<i>data:</i> <input type='text' size='8' value='".date('d/m/Y',strtotime($_SCHEDULE[$c]['payment_date']))."' onchange='schedulePaidDateChange(this)'/></td>";
		  $totPaidSchedule+= $_SCHEDULE[$c]['incomes'];
		 }
		 else
		  echo "<td width='190'><input type='checkbox' onchange='schedulePaidChange(this)'/>Pagato&nbsp;&nbsp;&nbsp;<i style='visibility:hidden;'>data:</i> <input type='text' style='width:90px' value='' style='visibility:hidden;' onchange='schedulePaidDateChange(this)'/></td>";
		 echo "<td><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/delete.gif' style='cursor:pointer' onclick='deleteScheduleItem(this)'/></td>";
		 echo "</tr>";
		 $totSchedule+= $_SCHEDULE[$c]['incomes'];
		}
		?>
		</table>
		</td></tr>

	<tr><td><span class='link' onclick="addDeposit()">Aggiungi</span></td>
		<td><span class='link' onclick="addRate()">Aggiungi</span></td></tr>

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr><td><div class='bluehead'><i>Totale acconti saldati</i> <span class='whiteright'><b id='tot_deposits'>&euro;.&nbsp;&nbsp;&nbsp;<?php echo number_format($totDeposits,2,',','.'); ?></b></span></div></td>
		<td><div class='bluehead' style="width:275px;float:left;"><i>Totale rateizzato</i> <span class='whiteright'><b id='tot_schedule'>&euro;.&nbsp;&nbsp;&nbsp;<?php echo number_format($totSchedule,2,',','.'); ?></b></span></div>
			<div class='bluehead' style="width:230px;float:left;margin-left:2px;"><i>di cui pagati</i> <span class='whiteright'><b id='tot_paid_schedule'>&euro;.&nbsp;&nbsp;&nbsp;<?php echo number_format($totPaidSchedule,2,',','.'); ?></b></span></div>
		</td></tr>
	</table>

	</td></tr>
</table>
<div style="font-family:Arial,sans-serif;font-size:12px;color:#555555;text-align:center;"><i>Gli acconti e le scadenze saldate non vengono automaticamente registrate,<br/>pertanto devono essere annotate manualmente nel registro della Prima Nota.</i></div>
<br/>

<table width="800" align="center" cellspacing="8" cellpadding="0" border="0" class="detailstable">
<tr><th>DETTAGLI VERSAMENTI</th><th>RIEPILOGO</th></tr>
<tr><td valign="top" width="520">
	<table class="smallgray" width="96%" cellspacing="0" cellpadding="2" border="0">
	<tr><th width='90'>DATA</th>
		<th>CAUSALE</th>
		<th width='90'>IMPORTO</th>
		<th>RISORSA / MODALITA&rsquo;</th></tr>
	<?php
	for($c=0; $c < count($docInfo['mmr']); $c++)
	{
	 $itm = $docInfo['mmr'][$c];
	 if($itm['payment_date'] == "0000-00-00")
	  continue;
	 echo "<tr id='mmr-".$itm['id']."'><td>".date('d/m/Y',strtotime($itm['payment_date']))."</td>";
	 echo "<td>".$itm['description']."</td>";
	 switch(strtolower($_CAT_TAG))
	 {
	  case 'agentinvoices' : case 'vendororders' : case 'purchaseinvoices' : case 'creditsnote' : echo "<td>".number_format($itm['expenses'],2,',','.')." &euro;</td>"; break;
	  default : echo "<td>".number_format($itm['incomes'],2,',','.')." &euro;</td>"; break;
	 }
	 if($itm['res_id'])
	 {
	  echo "<td width='200'><span class='fixed' style='width:200px;'>&nbsp;</span></td></tr>";
	 }
	 else
	  echo "<td width='200'><span class='fixed' style='width:200px;'>&nbsp;</span></td></tr>";
	}

	$summaryResults = ($subtotNetPay ? $subtotNetPay : $subtotVI) - $totDeposits - $totPaidSchedule;

	?>
	</table>
	</td><td valign="top">
	<div class="riepilogodiv">
	 <div class='row'>Totale documento <span class="blueblock" id='summary_doc_tot'><?php echo number_format($subtotNetPay ? $subtotNetPay : $subtotVI,2,',','.'); ?> &euro;</span></div>
	 <div class='row'>Acconti saldati <span class="greenblock" id='summary_deposits_tot'><?php echo number_format($totDeposits,2,',','.'); ?> &euro;</span></div>
	 <div class='row'>Rate saldate <span class="greenblock" id='summary_paid_schedule_tot'><?php echo number_format($totPaidSchedule,2,',','.'); ?> &euro;</span></div>
	</div>
	 <div class='row' style="margin-top:5px;">Restante da pagare <span class="orangeblock" id='summary_results'><?php echo number_format($summaryResults,2,',','.'); ?> &euro;</span></div>
	</td></tr>
</table>
</div>
<!-- EOF - PAGE PAYMENTS -->


<!-- PAGE - ATTACHMENTS -->
<div class='tabpage' id="attachments-page" style="display:none;">
	 	<div class='attachments-toolbar'>
		 <table border='0' cellspacing='0' cellpadding='0' width='780' height='40'>
		  <tr><td width='80' style='padding-left:10px'><span class='smallblue'>Carica un<br/>file dal PC</span></td>
			 <td><div id='gupldspace'></div></td>
			 <td width='40' class='attachments-tb-buttons'><a href='#' onclick='selectFromServer("<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR']."/"; ?>")'><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/load-from-server.png" border="0" title="Carica dal server"/></a></td>
			 <td width='40' class='attachments-tb-buttons'><a href='#' onclick='insertFromURL()'><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/link.png" border="0" title="Inserisci un link da URL"/></a></td>
		  </tr>
		 </table>
	    </div>
		<div id='attachments-explore' class='attachments-explore'>
		 <?php
		 /* LIST OF ATTACHMENTS */
		 if(!$ret['error'])
		 {
		  for($c=0; $c < count($docInfo['attachments']); $c++)
		  {
		   $item = $docInfo['attachments'][$c];
		   echo "<div class='attachment' id='attachment-".$item['id']."' attype='".$item['type']."'>";
		   echo "<a href='#' class='btnedit' onclick='editAttachment(".$item['id'].")' title='Modifica'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/edit_small.png' border='0'/></a>";
		   echo "<a href='#' class='btndel' onclick='deleteAttachment(".$item['id'].")' title='Rimuovi'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/delete_small.png' border='0'/></a>";
		   echo "<a href='".($item['type'] != "WEB" ? $_ABSOLUTE_URL : "").$item['url']."' target='blank'>";
		   if($item['icons'])
		   {
			if($item['icons']['size48x48'])
			 echo "<img src='".$_ABSOLUTE_URL.$item['icons']['size48x48']."' border='0' title=\"".$item['name']."\"/>";
		   }
		   else
			echo "<img src='".$_ABSOLUTE_URL."share/mimetypes/48x48/file.png' border='0' title=\"".$item['name']."\"/>";
		   echo "</a><br/><a href='".($item['type'] != "WEB" ? $_ABSOLUTE_URL : "").$item['url']."' target='blank' title=\"".$item['name']."\">".$item['name']."</a>";
		   echo "</div>";
		  }
		 }
		 ?>
		</div>

	    <div class="docnotes">
		 <h3>ANNOTAZIONI</h3>
		 <textarea id="notes"><?php echo $docInfo['desc']; ?></textarea>
		</div>

</div>
<!-- EOF - PAGE ATTACHMENTS -->

<!-- EOF - CONTENTS -->
</td></tr>
</table>
<?php
//-------------------------------------------------------------------------------------------------------------------//
basicapp_contents_end();
?>
<div class="docinfo-footer">&nbsp;</div>

<script>
var tb = null;
var expTB = null; // expenses table
var MainMenu = null;
var ACTIVE_TAB_PAGE = "home";
var PayModeMenu = null;
var RubricaEdit = null;
var LastRUBID = 0;
var DECIMALS = <?php echo $_DECIMALS ? $_DECIMALS : "2"; ?>;
var PLID = <?php echo $_PLID ? $_PLID : "0"; ?>;
var CUSTPLID = PLID;
var CUSTDISTANCE = <?php echo $subjectInfo['distance'] ? $subjectInfo['distance'] : 0; ?>;
var DEF_MARKUP_RATE = <?php echo $_PLINFO ? $_PLINFO['markuprate'] : "0"; ?>;
var DEF_VAT = <?php echo $_DEF_VAT; ?>;
var DEF_VAT_ID = <?php echo $_DEF_VAT_ID; ?>;
var DEF_VAT_TYPE = "<?php echo $_DEF_VAT_TYPE; ?>";
var DOC_TAG = "<?php echo $docInfo['tag']; ?>";
var CAT_TAG = "<?php echo $_CAT_TAG; ?>";

var NEW_ROWS = new Array();
var UPDATED_ROWS = new Array();
var DELETED_ROWS = new Array();

var NEW_DEPOSITS = new Array();
var UPDATED_DEPOSITS = new Array();
var DELETED_DEPOSITS = new Array();

var NEW_RATES = new Array();
var UPDATED_RATES = new Array();
var DELETED_RATES = new Array();

var RIVALSA_INPS = <?php echo $_COMPANY_PROFILE['accounting']['rivalsa_inps'] ? $_COMPANY_PROFILE['accounting']['rivalsa_inps'] : 0; ?>;
var RIT_ENASARCO = <?php echo $_COMPANY_PROFILE['accounting']['rit_enasarco'] ? $_COMPANY_PROFILE['accounting']['rit_enasarco'] : 0; ?>;
var RIT_ENASARCO_PERCIMP = <?php echo $_COMPANY_PROFILE['accounting']['rit_enasarco_percimp'] ? $_COMPANY_PROFILE['accounting']['rit_enasarco_percimp'] : 0; ?>;
var CASSA_PREV = <?php echo $_CASSA_PREV_PERC ? $_CASSA_PREV_PERC : "0"; ?>;
var CASSA_PREV_VATID = <?php echo $_CASSA_PREV_VATID ? $_CASSA_PREV_VATID : "0"; ?>;
var CASSA_PREV_VAT_TYPE = "<?php echo $_CASSA_PREV_VAT_TYPE; ?>";
var RIT_ACCONTO = <?php echo $_COMPANY_PROFILE['accounting']['rit_acconto'] ? $_COMPANY_PROFILE['accounting']['rit_acconto'] : 0; ?>;
var RIT_ACCONTO_PERCIMP = <?php echo $_COMPANY_PROFILE['accounting']['rit_acconto_percimp'] ? $_COMPANY_PROFILE['accounting']['rit_acconto_percimp'] : 0; ?>;
var RIT_ACCONTO_RIVINPSINC = <?php echo $_COMPANY_PROFILE['accounting']['rit_acconto_rivinpsinc'] ? $_COMPANY_PROFILE['accounting']['rit_acconto_rivinpsinc'] : 0; ?>;

var UNCONDISC_PERC = <?php echo $uncondiscPerc ? $uncondiscPerc : 0; ?>;
var UNCONDISC_AMOUNT = <?php echo $uncondiscAmount ? $uncondiscAmount : 0; ?>;

var attUpld = null;

var UNKNOWN_ELEMENTS = new Array();
var LAST_ELM_TYPE = "";

function bodyOnLoad()
{
 var div = document.getElementById('doctable').parentNode;
 div.style.height = div.parentNode.offsetHeight-120;
 div.style.width = div.offsetWidth;

 document.getElementById('doctable').style.display="";

 tb = new GMUTable(document.getElementById('doctable'));
 tb.FieldByName['code'].enableSearch("dynarc search -at `gmart` -fields code_str,name `", "` -limit 5 --order-by `code_str ASC`", "code_str","code_str","items",true,"code_str",codeQueryResults);
 tb.FieldByName['description'].enableSearch("dynarc search -at `gmart` -fields name,brand,model `", "` -limit 5 --order-by `name ASC`", "name","name","items",true,"name",nameQueryResults);

 tb.FieldByName['vat'].enableSearch("dynarc search -ap `vatrates` -fields code_str,name `", "` -limit 5 --order-by `code_str ASC` -get percentage,vat_type", "percentage","name","items",true,"percentage");

 tb.OnBeforeAddRow = function(r){
	if(!r.getAttribute('type') || (r.getAttribute('type') == "null"))
	 r.setAttribute('type',LAST_ELM_TYPE);
	r.cells[0].innerHTML = "<input type='checkbox'/ >";
	r.setAttribute('vatid',DEF_VAT_ID);
	r.setAttribute('vattype',DEF_VAT_TYPE);

   <?php
   reset($_COLUMNS);
   $idx = 1;
   while(list($k,$v) = each($_COLUMNS))
   {
	switch($k)
	{
	 case 'code' : case 'sn' : case 'serialnumber' : case 'lot' : case 'account' : case 'unitprice' : case 'discount' : case 'discount2' : case 'discount3' : case 'pricelist' : echo "r.cells[".$idx."].innerHTML = \"<span class='graybold'></span>\";\n"; break;
	 case 'description' : echo "r.cells[".$idx."].innerHTML = \"<span class='graybold doubleline'></span>\";\n"; break;
	 case 'qty' : case 'units' : case 'weight' : echo "r.cells[".$idx."].innerHTML = \"<span class='graybold 13 center'></span>\";\n"; break;
	 case 'vat' : echo "r.cells[".$idx."].innerHTML = \"<span class='graybold center'>\"+DEF_VAT+\"%</span>\";\n"; break;
	 case 'price' : case 'vatprice' : echo "r.cells[".$idx."].innerHTML = \"<span class='eurogreen'></span>\";\n"; break;
	 default : echo "r.cells[".$idx."].innerHTML = \"<span class='graybold'></span>\";\n"; break;
	}
    $idx++;
   }
   ?>
	 NEW_ROWS.push(r);
	}

 tb.OnSelectRow = function(r){
	 document.getElementById('selectionmenu').style.visibility = "visible";
	 document.getElementById('cutmenubtn').className = "";
	 document.getElementById('copymenubtn').className = "";
	 document.getElementById('deletemenubtn').className = "";
	}

 tb.OnUnselectRow = function(r){
	 if(!tb.GetSelectedRows().length)
	 {
	  document.getElementById('selectionmenu').style.visibility = "hidden";
	  document.getElementById('cutmenubtn').className = "disabled";
	  document.getElementById('copymenubtn').className = "disabled";
	  document.getElementById('deletemenubtn').className = "disabled";
	 }
	}

 tb.OnDeleteRow = function(r){
	 if(r.id)
	 {
	  if(UPDATED_ROWS.indexOf(r) >= 0)
	   UPDATED_ROWS.splice(UPDATED_ROWS.indexOf(r),1);
	  DELETED_ROWS.push(r);
	 }
	 else
	  NEW_ROWS.splice(NEW_ROWS.indexOf(r),1);
	 if(UNKNOWN_ELEMENTS.indexOf(r) >= 0)
	  UNKNOWN_ELEMENTS.splice(UNKNOWN_ELEMENTS.indexOf(r),1);
	}

 tb.OnBeforeCellEdit = function(r,cell,value){
	 switch(cell.tag)
	 {
	  case 'code' : {
		 switch(r.getAttribute('type'))
		 {
		  case 'article' : this.FieldByName['code'].enableSearch("dynarc search -at `gmart` -fields code_str,name `", "` -limit 5 --order-by `code_str ASC`", "code_str","code_str","items",true,"code_str",codeQueryResults); break;
		  case 'service' : this.FieldByName['code'].enableSearch("dynarc search -at `gserv` -fields code_str,name `", "` -limit 5 --order-by `code_str ASC`", "code_str","code_str","items",true,"code_str",codeQueryResults); break;
		  case 'supply' : this.FieldByName['code'].enableSearch("dynarc search -at `gsupplies` -fields code_str,name `", "` -limit 5 --order-by `code_str ASC`", "code_str","code_str","items",true,"code_str",codeQueryResults); break;
		 }
		} break;

	  case 'description' : {
		 if(!r.cell['code'].getValue())
		 {
		  switch(r.getAttribute('type'))
		  {
		   case 'article' : this.FieldByName['description'].enableSearch("dynarc search -at `gmart` -fields name,brand,model `", "` -limit 5 --order-by `name ASC`", "name","name","items",true,"name",nameQueryResults); break;
		   case 'service' : this.FieldByName['description'].enableSearch("dynarc search -at `gserv` -fields name `", "` -limit 5 --order-by `name ASC`", "name","name","items",true,"name",nameQueryResults); break;
		   case 'supply' : this.FieldByName['description'].enableSearch("dynarc search -at `gsupplies` -fields name `", "` -limit 5 --order-by `name ASC`", "name","name","items",true,"name",nameQueryResults); break;
		  }
		 }
		 else
		  this.FieldByName['description'].disableSearch();
		} break;
	 }

	}

 tb.OnCellEdit = function(r,cell,value,data){
	 switch(cell.tag)
	 {
	  case 'code' : {
		 var sh = new GShell();
		 sh.OnError = function(msg,errcode){
			 if(UNKNOWN_ELEMENTS.indexOf(r) < 0)
			  UNKNOWN_ELEMENTS.push(r);
			}

		 sh.OnOutput = function(o,a){
		  if(!a)
		  {
		   r.setAttribute('refid','');
		   return;
		  }
		  r.setAttribute('vatid',a['vatid']);
		  r.setAttribute('vattype',a['vattype']);
		  r.cell['code'].setValue(a['code_str']);
		  r.setAttribute('refid',a['id']);
		  r.setAttribute('refap',a['tb_prefix']);
		  r.cell['description'].setValue(a['name']);
		  r.cell['qty'].setValue(1);
		  r.cell['weight'].setValue(a['weight'] ? a['weight']+" "+a['weightunits'] : "");

		  if(a['custompricing'])
		  {
		   if(a['custompricing']['discount_perc'])
			r.cell['discount'].setValue(a['custompricing']['discount_perc']+"%");
		   if(a['custompricing']['discount2'])
		    r.cell['discount2'].setValue(a['custompricing']['discount2']+"%");
		   if(a['custompricing']['discount3'])
		    r.cell['discount3'].setValue(a['custompricing']['discount3']+"%");
		  }
		  
		  if(a['service_type'] && CUSTDISTANCE)
		  {
		   switch(a['service_type'])
		   {
		    case 'MILEAGE-REIMBURSEMENT-ONE-TRIP' : case 'MILEAGE-REIMBURSEMENT-ROUND-TRIP' : r.cell['qty'].setValue(CUSTDISTANCE); break;
		   }
		  }

		  r.cell['units'].setValue(a['units'] ? a['units'] : "PZ");
		  r.cell['unitprice'].setValue(a['finalprice']);
		  r.cell['vat'].setValue(a['vat']);
		  r.cell['price'].setValue(a['finalprice']);
		  updateTotals(r);
		 }

		 /* GET SUBJECT */
		 if(!RubricaEdit.value)
		 {
		  var subjectId = 0;
		  var subjectName = "";
		 }
		 else if(RubricaEdit.data)
		 {
		  var subjectId = RubricaEdit.data['id'];
		  var subjectName = RubricaEdit.value;
		 }
		 else
		 {
		  var subjectId = 0;
		  var subjectName = RubricaEdit.value;
		 }

		 if(data)
		  sh.sendCommand("commercialdocs getfullinfo -ap `"+data['tb_prefix']+"` -id `"+data['id']+"` -subjectid `"+subjectId+"` -pricelistid `"+CUSTPLID+"`");
		 else
		 {
		  var _ap = "";
		  switch(r.getAttribute('type'))
		  {
		   case 'article' : _ap = "gmart"; break;
		   case 'service' : _ap = "gserv"; break;
		   case 'supply' : _ap = "gsupplies"; break;
		   default : _ap = "gmart"; break;
		  }
		  sh.sendCommand("commercialdocs getfullinfo -type `"+_ap+"` -code `"+value+"` -subjectid `"+subjectId+"` -pricelistid `"+CUSTPLID+"`");
		 }
		} break;

	  case 'qty' : updateTotals(r); break;

	  case 'description' : {
		 if(!this.FieldByName['description'].searchEnabled)
		  break;
		 var sh = new GShell();
		 sh.OnError = function(msg,errcode){
			 if(UNKNOWN_ELEMENTS.indexOf(r) < 0)
			  UNKNOWN_ELEMENTS.push(r);
			}

		 sh.OnOutput = function(o,a){
		  if(!a)
		  {
		   r.setAttribute('refid','');
		   return;
		  }
		  r.setAttribute('vatid',a['vatid']);
		  r.setAttribute('vattype',a['vattype']);
		  r.cell['code'].setValue(a['code_str']);
		  r.setAttribute('refid',a['id']);
		  r.setAttribute('refap',a['tb_prefix']);
		  r.cell['description'].setValue(a['name']);
		  r.cell['qty'].setValue(1);
		  r.cell['weight'].setValue(a['weight'] ? a['weight']+" "+a['weightunits'] : "");

		  if(a['custompricing'])
		  {
		   if(a['custompricing']['discount_perc'])
			r.cell['discount'].setValue(a['custompricing']['discount_perc']+"%");
		   if(a['custompricing']['discount2'])
		    r.cell['discount2'].setValue(a['custompricing']['discount2']+"%");
		   if(a['custompricing']['discount3'])
		    r.cell['discount3'].setValue(a['custompricing']['discount3']+"%");
		  }
		  
		  if(a['service_type'] && CUSTDISTANCE)
		  {
		   switch(a['service_type'])
		   {
		    case 'MILEAGE-REIMBURSEMENT-ONE-TRIP' : case 'MILEAGE-REIMBURSEMENT-ROUND-TRIP' : r.cell['qty'].setValue(CUSTDISTANCE); break;
		   }
		  }

		  r.cell['units'].setValue(a['units'] ? a['units'] : "PZ");
		  r.cell['unitprice'].setValue(a['finalprice']);
		  r.cell['vat'].setValue(a['vat']);
		  r.cell['price'].setValue(a['finalprice']);
		  updateTotals(r);
		 }

		 /* GET SUBJECT */
		 if(!RubricaEdit.value)
		 {
		  var subjectId = 0;
		  var subjectName = "";
		 }
		 else if(RubricaEdit.data)
		 {
		  var subjectId = RubricaEdit.data['id'];
		  var subjectName = RubricaEdit.value;
		 }
		 else
		 {
		  var subjectId = 0;
		  var subjectName = RubricaEdit.value;
		 }

		 if(data)
		  sh.sendCommand("commercialdocs getfullinfo -ap `"+data['tb_prefix']+"` -id `"+data['id']+"` -subjectid `"+subjectId+"` -pricelistid `"+CUSTPLID+"`");
		 else
		 {
		  var _ap = "";
		  switch(r.getAttribute('type'))
		  {
		   case 'article' : _ap = "gmart"; break;
		   case 'service' : _ap = "gserv"; break;
		   case 'supply' : _ap = "gsupplies"; break;
		   default : _ap = "gmart"; break;
		  }
		  sh.sendCommand("commercialdocs getfullinfo -type `"+_ap+"` -name `"+value+"` -subjectid `"+subjectId+"` -pricelistid `"+CUSTPLID+"`");
		 }
		} break;

	  case 'unitprice' : {
		 r.unitpriceChanged=true;
		 updateTotals(r); 
		} break;

	  case 'discount' : {
		 if(value.indexOf("%") < 0)
		  cell.getElementsByTagName('SPAN')[0].innerHTML = "&euro;. "+formatCurrency(parseCurrency(value),DECIMALS);
		 r.discountChanged=true;
		 updateTotals(r);
		} break;

	  case 'discount2' : {
		 r.discountChanged=true;
		 updateTotals(r);
		} break;

	  case 'discount3' : {
		 r.discountChanged=true;
		 updateTotals(r);
		} break;

	  case 'vat' : {
		 if(data)
		 {
		  r.setAttribute('vatid',data['id']);
		  r.setAttribute('vattype',data['vat_type']);
		  updateTotals(r);
		 }
		 else
		 {
		  var sh = new GShell();
		  sh.OnOutput = function(o,a){
			 if(!a)
			 {
		  	  r.setAttribute('vatid',0);
		  	  r.setAttribute('vattype',"");
			  updateTotals(r);
			  return;
			 }
			 r.setAttribute('vatid',a['items'][0]['id']);
			 r.setAttribute('vattype',a['items'][0]['vat_type']);
			 r.cell['vat'].setValue(a['items'][0]['percentage']);
			 updateTotals(r);
			}
		  sh.sendCommand("dynarc search -ap `vatrates` -fields code_str,name `"+value+"` -limit 1 --order-by `code_str ASC` -get percentage,vat_type");
		 }
		} break;

	  /* EXTRA COLUMNS */
	  <?php
	  for($c=0; $c < count($extracolumns); $c++)
	  {
	   $column = $extracolumns[$c];
	   echo "case '".$column['tag']."' : {\n";
	   echo makeExtraColumnEditFunc($column);
	   echo "updateTotals(r);\n } break; \n";
	  }
	  ?>

	 }
	 if(r.id && (UPDATED_ROWS.indexOf(r) < 0))
	  UPDATED_ROWS.push(r);
	}

 tb.OnRowMove = function(r){
	 if(r.id && (UPDATED_ROWS.indexOf(r) < 0)) UPDATED_ROWS.push(r);
	 else if(!r.id && (NEW_ROWS.indexOf(r) < 0)) NEW_ROWS.push(r);
	}


 /* EXPENSES TABLE */
 expTB = new GMUTable(document.getElementById('expensestable'));
 expTB.FieldByName['vat'].enableSearch("dynarc search -ap `vatrates` -fields code_str,name `", "` -limit 5 --order-by `code_str ASC` -get percentage,vat_type", "percentage","name","items",true,"percentage");

 expTB.OnBeforeAddRow = function(r){
	 r.setAttribute('vatid',DEF_VAT_ID);
	 r.setAttribute('vattype',DEF_VAT_TYPE);
	 r.cells[0].innerHTML = "<input type='checkbox'/ >";
	 r.cells[1].innerHTML = "<span class='graybold'></span>";
	 r.cells[2].innerHTML = "<span class='graybold 13 center'>"+DEF_VAT+"%</span>";
	 r.cells[3].innerHTML = "<span class='graybold right'></span>"; 
	}

 expTB.OnAddRow = function(r){
	 if(expTB.O.rows.length > 4)
	 {
	  alert("Si possono inserire al massimo 3 voci di spesa");
	  r.remove();
	  return;
	 }
	}

 expTB.OnCellEdit = function(r,cell,value,data){
	 switch(cell.tag)
	 {
	  case 'vat' : {
		 if(data)
		 {
		  r.setAttribute('vatid',data['id']);
		  r.setAttribute('vattype',data['vat_type']);
		 }
		 else
		 {
		  var sh = new GShell();
		  sh.OnOutput = function(o,a){
			 if(!a)
			 {
		  	  r.setAttribute('vatid',0);
		  	  r.setAttribute('vattype',"");
			  return;
			 }
			 r.setAttribute('vatid',a['items'][0]['id']);
			 r.setAttribute('vattype',a['items'][0]['vat_type']);
			 r.cell['vat'].setValue(a['items'][0]['percentage']);
			 updateTotals();
			}
		  sh.sendCommand("dynarc search -ap `vatrates` -fields code_str,name `"+value+"` -limit 1 --order-by `code_str ASC` -get percentage,vat_type");
		 } 
		} break;
	 }
	 updateTotals();
	}

 expTB.OnDeleteRow = function(r){updateTotals();}

 /* MAIN MENU */

 MainMenu = new GMenu(document.getElementById('mainmenu'));
 new GMenu(document.getElementById('doctypemenu'));
 PayModeMenu = new GPopupMenu(document.getElementById('paymodemenu'), document.getElementById('paymodemenu-list'));
 document.getElementById('rubricaedit').onblur = function(){hideRubricaEdit();}
 RubricaEdit = new DynRubricaEdit(document.getElementById('rubricaedit'));
 document.getElementById('rubricaedit').onchange = function(){subjectChanged(this);}

 /* ATTACHMENTS */
 attUpld = new GUploader(null,null,"commercialdocs/");
 document.getElementById('gupldspace').appendChild(attUpld.O);
 attUpld.OnUpload = function(file){
	 var sh = new GShell();
	 sh.OnOutput = function(o,a){
		 var div = document.createElement('DIV');
		 div.className = "attachment";
		 div.id = "attachment-"+a['id'];
		 var ih = "<a href='#' class='btnedit' onclick='editAttachment("+a['id']+")' title='Modifica'><img src='"+ABSOLUTE_URL+"GCommercialDocs/img/edit_small.png' border='0'/ ></a> <a href='#' class='btndel' onclick='deleteAttachment("+a['id']+")' title='Rimuovi'><img src='"+ABSOLUTE_URL+"GCommercialDocs/img/delete_small.png' border='0'/ ></a><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank'>";
		 if(a['icons'])
	   	 {
		  if(a['icons']['size48x48'])
		   ih+= "<img src='"+ABSOLUTE_URL+a['icons']['size48x48']+"' border='0' title=\""+a['name']+"\"/ >";
	     }
	     else
		  ih+= "<img src='"+ABSOLUTE_URL+"share/mimetypes/48x48/file.png' border='0' title=\""+a['name']+"\"/ >";
	     ih+= "</a><br/ ><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank' title=\""+a['name']+"\">"+a['name']+"</a>";
		 div.innerHTML = ih;
		 document.getElementById('attachments-explore').appendChild(div);
		 // update attachments counter
		 /*var nc = document.getElementById('attachments-count');
		 nc.innerHTML = parseFloat(nc.innerHTML)+1;*/
		}
	 sh.sendCommand("dynattachments add -ap 'commercialdocs' -refid `<?php echo $docInfo['id']; ?>` -name '"+file['name']+"' -url '"+file['fullname']+"'");
	}


 /* PREDEFINED MESSAGES */
 var predefmsg = document.getElementById("predefmsg");
 predefmsg.onmouseover =  function(){this.mouseisover=true;}
 predefmsg.onmouseout = function(){this.mouseisover=false;}

 /* SHOWCOLUMNS CLOUD */
 var showcolumncloud = document.getElementById('showcolumnscloud');
 showcolumncloud.onmouseover =  function(){this.mouseisover=true;}
 showcolumncloud.onmouseout = function(){this.mouseisover=false;}

 /* SHOWTOTALS CLOUD */
 var showtotalscloud = document.getElementById('showtotalscloud');
 showtotalscloud.onmouseover =  function(){this.mouseisover=true;}
 showtotalscloud.onmouseout = function(){this.mouseisover=false;}

 /* UNCONDITIONALDISCOUNT CLOUD */
 var unconditionaldiscountcloud = document.getElementById('unconditionaldiscountcloud');
 unconditionaldiscountcloud.onmouseover =  function(){this.mouseisover=true;}
 unconditionaldiscountcloud.onmouseout = function(){this.mouseisover=false;}


 var extdocrefEd = document.getElementById('extdocref');
 if(extdocrefEd)
 {
  extdocrefEd.onfocus = function(){
	 if(this.value == "Es: Fattura n. XXX del gg/mm/aaaa")
	  this.value = "";
	 this.className = "edit";
	}

  extdocrefEd.onblur = function(){
	 if(!this.value)
	 {
	  this.value = "Es: Fattura n. XXX del gg/mm/aaaa";
	  this.className = "edit lightgray";
	 }
	 else
	  this.className = "edit";
	} 

  if(!extdocrefEd.value)
   extdocrefEd.onblur();
 }

 document.addEventListener ? document.addEventListener("mouseup", function(){hideAllClouds();},false) : document.attachEvent("onmouseup",function(){hideAllClouds();});

 updateTotals();
}

function hideAllClouds()
{
 /* HIDE PREDEFINED MESSAGES */
 var predefmsg = document.getElementById("predefmsg");
 if(!predefmsg.mouseisover)
  predefmsg.style.display = "none";

 /* HIDE COLUMNS CLOUD */
 var showcolumncloud = document.getElementById('showcolumnscloud');
 if(!showcolumncloud.mouseisover)
  showcolumncloud.style.display = "none";
 
 /* HIDE TOTALS CLOUD */
 var showtotalscloud = document.getElementById('showtotalscloud');
 if(!showtotalscloud.mouseisover)
  showtotalscloud.style.display = "none";

 /* HIDE UNCONDITIONALDISCOUNT CLOUD */
 var unconditionaldiscountcloud = document.getElementById('unconditionaldiscountcloud');
 if(!unconditionaldiscountcloud.mouseisover)
 {
  unconditionaldiscountcloud.style.display = "none";
  ACTIVE_GMUTABLE = null;
 }
}

function codeQueryResults(items, resArr, retVal)
{
 for(var c=0; c < items.length; c++)
 {
  resArr.push(items[c]['code_str']+" - "+items[c]['name']);
  retVal.push(items[c]['code_str']);
 }
}

function nameQueryResults(items, resArr, retVal)
{
 for(var c=0; c < items.length; c++)
 {
  resArr.push(items[c]['code_str']+" - "+items[c]['name']);
  retVal.push(items[c]['name']);
 }
}

function showPage(page,obj)
{
 if(page == ACTIVE_TAB_PAGE)
  return;

 var li = obj.parentNode;
 var ul = li.parentNode;
 var list = ul.getElementsByTagName('LI');
 for(var c=0; c < list.length; c++)
 {
  list[c].className = (list[c] == li) ? "selected" : "";
 }
 document.getElementById(ACTIVE_TAB_PAGE+"-page").style.display = "none";
 document.getElementById(page+"-page").style.display = "";
 ACTIVE_TAB_PAGE = page;
}

function setPaymentMode(id, li)
{
 if(li)
 {
  var txtName = li.innerHTML;
  txtName = txtName.substr(txtName.indexOf(">")+1);
  document.getElementById("paymodemenu").getElementsByTagName('SPAN')[0].innerHTML = txtName;
  document.getElementById('paymentmode-select').value = id;
  _paymentModeSelectChange(document.getElementById('paymentmode-select'));
 }
}

function configPaymentMode()
{
 var sh = new GShell();
 sh.OnOutput = function(){
	 _updatePaymentModeList();
	}
 sh.sendSudoCommand("gframe -f config.paymentmodes");
}

function _updatePaymentModeList()
{
 var ul = document.getElementById('paymodemenu-list');
  
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a)
	  return;

	 /* Empty list */
	 var list = ul.getElementsByTagName('LI');
	 while(list.length > 2)
	  ul.removeChild(list[0]);
	 var separator = list[0];

	 /* Insert items */
	 for(var c=0; c < a.length; c++)
	 {
	  var li = document.createElement('LI');
	  li.id = "pmm-"+a[c]['id'];
	  li.onclick = function(){setPaymentMode(this.id.substr(4),this);}
	  li.innerHTML = "<img src='"+ABSOLUTE_URL+"GCommercialDocs/img/payment-methods.png'/ >"+a[c]['name'];
	  ul.insertBefore(li,separator);
	 }
	 
	}
 sh.sendCommand("paymentmodes list");
}

function subjectChange()
{
 document.getElementById('subjectname').style.display = "none";
 document.getElementById('rubricaedit-container').style.display = "";
 document.getElementById('rubricaedit').focus();
 document.getElementById('rubricaedit').select();
}

function subjectInfo()
{
 if(!RubricaEdit.value)
 {
  var subjectId = 0;
  var subjectName = "";
 }
 else if(RubricaEdit.data)
 {
  var subjectId = RubricaEdit.data['id'];
  var subjectName = RubricaEdit.value;
 }
 else
 {
  var subjectId = 0;
  var subjectName = RubricaEdit.value;
 }

 var sh = new GShell();
 if(subjectId)
 {
  sh.OnOutput = function(){subjectChanged(document.getElementById('rubricaedit'));}
  sh.sendCommand("dynlaunch -ap `rubrica` -id `"+subjectId+"`");
 }
 else
 {
  sh.OnOutput = function(o,a){
	 if(!a) return;
	 RubricaEdit.data = a;
	 var sh2 = new GShell();
	 sh2.OnOutput = function(){subjectChanged(document.getElementById('rubricaedit'));}
	 sh2.sendCommand("dynlaunch -ap `rubrica` -id `"+a['id']+"`");
	}
  sh.sendCommand("gframe -f rubrica.new -title `Vuoi inserire questo contatto in rubrica?` -contents `"+subjectName+"` -params `ap=rubrica`");
 }
}

function hideRubricaEdit()
{
 var ed = document.getElementById('rubricaedit');
 document.getElementById('subjectname').innerHTML = ed.value;
 document.getElementById('subjectname').style.display = "";
 document.getElementById('rubricaedit-container').style.display = "none";
 if(ed.data && (LastRUBID == ed.data['id']))
  return;

 ed.defaultValue = ed.value;

 if(ed.data)
 {
  _updateContacts(ed.data['id']);
  LastRUBID = ed.data['id'];
 }
 else
  _updateContacts(0);
}

function subjectChanged(ed)
{
 if(ed.data)
 {
  _updateContacts(ed.data['id']);
  LastRUBID = ed.data['id'];
 }
 else
 {
  /* RESET PAYMENT MODE */
  document.getElementById('paymentmode-select').value = 0;
  _paymentModeSelectChange(document.getElementById('paymentmode-select'));
  updateReferences();
 }
}

function _updateContacts(id)
{
 /* Clean bank list */
 var sel = document.getElementById('banksupport-select');
 while(sel.options.length > 0)
  sel.removeChild(sel.options[0]);

 if(!id)
 {
  document.getElementById('ship-subject').value = document.getElementById('rubricaedit').value;

  /* Clean shipping page */
  var sel = document.getElementById('shipping-contactselect');
  while(sel.options.length > 0)
   sel.removeChild(sel.options[0]);

  CUSTPLID = PLID;
  CUSTDISTANCE = 0;

  updateReferences();

  return;
 }

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 updateReferences(a);
	 if(!a) 
	  return;

	 CUSTPLID = a['pricelist_id'];
	 CUSTDISTANCE = a['distance'];

	 /* UPDATE PAYMENT MODE */
	 document.getElementById('paymentmode-select').value = a['paymentmode'];
	 _paymentModeSelectChange(document.getElementById('paymentmode-select'));


	 /* Update banks first */
	 if(a['banks'] && a['banks'].length)
	 {
	  var sel = document.getElementById('banksupport-select');
	  for(var c=0; c < a['banks'].length; c++)
	  {
	   var opt = document.createElement('OPTION');
	   opt.value = a['banks'][c]['id'];
	   opt.innerHTML = a['banks'][c]['name'];
	   sel.appendChild(opt);
	  }
	 }

	 if(!a['contacts'] || !a['contacts'].length)
	  return _updateContacts(0);

	 var html = a['contacts'][0]['address']+"<br/ >"+a['contacts'][0]['city'];
	 if(a['contacts'][0]['province'])
	  html+= " ("+a['contacts'][0]['province']+")";
	 document.getElementById('subjdefcontact').innerHTML = html;

	 var html = a['contacts'][0]['name']+"<br/ >"+a['contacts'][0]['address']+"<br/ >"+a['contacts'][0]['city'];
	 if(a['contacts'][0]['province'])
	  html+= " ("+a['contacts'][0]['province']+")";
	 document.getElementById('shiptoaddr').innerHTML = html;

	 /* UPDATE SHIPPING PAGE */
	 var sel = document.getElementById('shipping-contactselect');
	 while(sel.options.length > 0)
	  sel.removeChild(sel.options[0]);
	
	 for(var c=0; c < a['contacts'].length; c++)
	 {
	  var opt = document.createElement('OPTION');
	  opt.value = a['contacts'][c]['id'];
	  opt.innerHTML = a['contacts'][c]['label'];
	  sel.appendChild(opt);
	 }

	 if(a['contacts'].length)
	 {
	  document.getElementById('ship-subject').value = a['contacts'][0]['name'];
	  document.getElementById('ship-address').value = a['contacts'][0]['address'];
	  document.getElementById('ship-city').value = a['contacts'][0]['city'];
	  document.getElementById('ship-zipcode').value = a['contacts'][0]['zipcode'];
	  document.getElementById('ship-prov').value = a['contacts'][0]['province'];
	  document.getElementById('ship-country').value = a['contacts'][0]['countrycode'];
	 }
	}
 sh.sendCommand("dynarc item-info -ap `rubrica` -id `"+id+"` -extget `contacts,banks,references` -get `paymentmode,pricelist_id,distance`");
}

function _updateShippingContact(id)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a)
	 {
	  /*document.getElementById('ship-subject').value = "";
	  document.getElementById('ship-address').value = "";
	  document.getElementById('ship-city').value = "";
	  document.getElementById('ship-zipcode').value = "";
	  document.getElementById('ship-prov').value = "";
	  document.getElementById('ship-country').value = "";
	  document.getElementById('shiptoaddr').innerHTML = "&nbsp;";*/
	 }
	 else
	 {
	  document.getElementById('ship-subject').value = a['name'];
	  document.getElementById('ship-address').value = a['address'];
	  document.getElementById('ship-city').value = a['city'];
	  document.getElementById('ship-zipcode').value = a['zipcode'];
	  document.getElementById('ship-prov').value = a['province'];
	  document.getElementById('ship-country').value = a['countrycode'];
	  var addr = a['name']+"<br/ >"+a['address']+"<br/ >"+a['city']+(a['province'] ? " ("+a['province']+")" : "");
	  document.getElementById('shiptoaddr').innerHTML = addr;
	 }
	}
 sh.sendCommand("dynarc exec-func ext:contacts.info -params `ap=rubrica&id="+id+"`");
}

function updateReferences(a)
{
 var refSel = document.getElementById('referencelist');
 while(refSel.options.length > 1)
  refSel.removeChild(refSel.options[1]);

 if(!a || !a['references']) return;
 for(var c=0; c < a['references'].length; c++)
 {
  var opt = document.createElement('OPTION');
  opt.value = a['references'][c]['id'];
  opt.innerHTML = a['references'][c]['name']+" &nbsp;&nbsp;("+a['references'][c]['type']+")";
  refSel.appendChild(opt);
 }

}

function editShipAddr()
{
 document.getElementById('shipSpanPageBtn').onclick();
}

function printPreview()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var status = <?php echo $docInfo['status']; ?>;
	 if(status < 3)
	 {
	  switch(a['action'])
	  {
	   case 'PRINT' : {
		 var sh2 = new GShell();
		 sh2.sendCommand("dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status=1`");
		} break;

	   case 'EXPORT' : case 'EMAIL' : {
		 var sh2 = new GShell();
		 sh2.sendCommand("dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status=2`");
		} break;
	  }
	 }
	}
 sh.sendCommand("gframe -f print.preview -params `modelap=printmodels&modelct="+CAT_TAG+"&parser=commercialdocs&ap=commercialdocs&id=<?php echo $docInfo['id']; ?>` -title `<?php echo urlencode(html_entity_decode($docInfo['name'],ENT_QUOTES,'UTF-8')); ?>`");
}

function pay()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){document.location.reload();}
 sh.sendCommand("gframe -f commercialdocs/pay -params `id=<?php echo $docInfo['id']; ?>&desc=Saldo`");
}

function cut()
{
 alert('Spiacente: questa funzione non è ancora disponibile. Lo sarà presto nei prossimi aggiornamenti.');
}

function copy()
{
 alert('Spiacente: questa funzione non è ancora disponibile. Lo sarà presto nei prossimi aggiornamenti.');
}

function paste()
{
 alert('Spiacente: questa funzione non è ancora disponibile. Lo sarà presto nei prossimi aggiornamenti.');
}

function deleteSelectedItems()
{
 var list = tb.GetSelectedRows();
 if(!list.length)
  return alert("Nessuna riga è stata selezionata");

 if(!confirm("Sei sicuro di voler eliminare le righe selezionate?"))
  return;

 for(var c=0; c < list.length; c++)
  list[c].remove();

 updateTotals();

 tb.OnUnselectRow();
}

function InsertRow(type)
{
 var r = tb.AddRow();
 r.setAttribute('type',type);
 switch(type)
 {
  case 'note' : {
	 var colSpan = tb.O.rows[0].cells.length-1;
	 while(r.cells.length > 2)
	  r.deleteCell(2);
	 r.cells[1].colSpan=colSpan;
	} break;
  default : r.cell['pricelist'].setAttribute('pricelist_id',CUSTPLID); break
 }
 LAST_ELM_TYPE = type;
 r.edit();
}

function InsertMessage(li)
{
 var pos = _getObjectPosition(li);

 var msgdiv = document.getElementById("predefmsg");
 msgdiv.style.left = pos['x']-272;
 msgdiv.style.top = pos['y']+28;
 msgdiv.style.display = "";
}

function IncludeItemDesc()
{
 var selected = tb.GetSelectedRows();
 if(!selected.length)
  return alert("Nessuna riga è stata selezionata");

 var sel = new Array();
 /* Di tutte le righe selezionate filtra solamente quelle di tipo "article" o "service" */
 for(var c=0; c < selected.length; c++)
 {
  switch(selected[c].getAttribute('type'))
  {
   case 'article' : case 'service' : case 'supply' : sel.push(selected[c]); break;
  }
 }

 if(!sel.length)
  return alert("Non posso ricavare le descrizioni dalle righe di nota o dalle righe personalizzate ma solamente da quelle di tipo 'articolo' o 'servizio'.");
 
 var sh = new GShell();
 var idx = 0;
 sh.OnError = function(){idx++;}
 sh.OnOutput = function(o,a){
	 var r = tb.AddRow(sel[idx].rowIndex+1);
	 var colSpan = tb.O.rows[0].cells.length-1;
	 while(r.cells.length > 2)
	  r.deleteCell(2);
	 r.cells[1].colSpan=colSpan;
	 r.cells[1].setValue(a['desc']);
	 r.setAttribute('type','note');
	 idx++;
	}

 for(var c=0; c < sel.length; c++)
 {
  var code = sel[c].cell['code'].getValue();
  if(sel[c].getAttribute('refap'))
   sh.sendCommand("dynarc item-info -ap `"+sel[c].getAttribute('refap')+"`"+(sel[c].getAttribute('refid') ? " -id `"+sel[c].getAttribute('refid')+"`" : " -code `"+code+"`"));
  else
  {
   switch(sel[c].getAttribute('type'))
   {
    case 'article' : sh.sendCommand("dynarc item-info -ap `gmart` -code `"+code+"`"); break;
    case 'service' : sh.sendCommand("dynarc item-info -ap `gserv` -code `"+code+"`"); break;
    case 'supply' : sh.sendCommand("dynarc item-info -ap `gsupplies` -code `"+code+"`"); break;
   }
  }

 }

}

function updateTotals(r)
{
 if(r)
 {
  var qty = parseFloat(r.cell['qty'].getValue());
  var extraQty = parseFloat(r.cell['extraqty'].getValue());
  if(extraQty)
   qty = qty*extraQty;

  var unitprice = parseCurrency(r.cell['unitprice'].getValue());
  var discStr = r.cell['discount'].getValue();
  var discount = 0;
  var discount2 = 0;
  var discount3 = 0;

  if(discStr.indexOf("%") > 0)
  {
   var disc = parseFloat(discStr);
   discount = unitprice ? (unitprice/100)*disc : 0;
  }
  else
   discount = parseCurrency(discStr.substr(3));
  if(!discount) 
   discount=0;

  var disc2Str = r.cell['discount2'].getValue();
  if(disc2Str && unitprice)
  {
   var disc = parseFloat(disc2Str);
   if(disc)
    discount2 = ((unitprice-discount)/100) * disc;
  }
  
  var disc3Str = r.cell['discount3'].getValue();
  if(disc2Str && disc3Str && unitprice)
  {
   var disc = parseFloat(disc3Str);
   if(disc)
    discount3 = (((unitprice-discount)-discount2)/100) * disc;
  }

  var vat = parseFloat(r.cell['vat'].getValue());

  var total = (unitprice-discount-discount2-discount3) * qty;
  var totalPlusVat = total ? total + ((total/100)*vat) : 0;

  r.cell['price'].setValue("<em>&euro;</em>"+formatCurrency(total,2));
  r.cell['vatprice'].setValue("<em>&euro;</em>"+formatCurrency(totalPlusVat,2));
 }


 var VAT_RATES = new Array();
 var VATS = new Object();
 var totWeight = 0;
 
 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];
  if(r.getAttribute('type') == "note")
   continue;
  var qty = parseFloat(r.cell['qty'].getValue());
  var extraQty = parseFloat(r.cell['extraqty'].getValue());
  if(extraQty)
   qty = qty*extraQty;

  var discount = 0;
  var discount2 = 0;
  var discount3 = 0;
  var unitprice = parseCurrency(r.cell['unitprice'].getValue());
  var discStr = r.cell['discount'].getValue();
  if(discStr.indexOf("%") > 0)
  {
   var disc = parseFloat(discStr);
   discount = unitprice ? (unitprice/100)*disc : 0;
  }
  else
   discount = parseCurrency(discStr.substr(3));

  if(!discount) 
   discount=0;

  var disc2Str = r.cell['discount2'].getValue();
  if(disc2Str && unitprice)
  {
   var disc = parseFloat(disc2Str);
   if(disc)
    discount2 = ((unitprice-discount)/100) * disc;
  }
  
  var disc3Str = r.cell['discount3'].getValue();
  if(disc2Str && disc3Str && unitprice)
  {
   var disc = parseFloat(disc3Str);
   if(disc)
    discount3 = (((unitprice-discount)-discount2)/100) * disc;
  }

  discount = (discount+discount2+discount3) * qty;

  var amount = parseCurrency(r.cell['price'].getValue().substr(10));
  var total = amount;
  var vatRate = parseFloat(r.cell['vat'].getValue());
  var vat = total ? (total/100)*vatRate : 0;
  if(vatRate && (VAT_RATES.indexOf(vatRate) < 0))
   VAT_RATES.push(vatRate);

  if(r.getAttribute('vatid'))
  {
   if(VATS[r.getAttribute('vatid')])
	var vatInfo = VATS[r.getAttribute('vatid')];
   else
   {
	var vatInfo = {type:r.getAttribute('vattype'), rate:parseFloat(r.cell['vat'].getValue()), amount:0, expenses:0};
	VATS[r.getAttribute('vatid')] = vatInfo;
   }
   vatInfo.amount+= amount;
  }

  var weight = r.cell['weight'].getValue();
  if(weight)
  {
   if(weight.indexOf(" ") > 0)
   {
    var x = weight.split(" ");
    var n = parseFloat(x[0]);
    if(!n)
 	 weight = 0;
    else
    {
     switch(x[1].toLowerCase())
     {
	  case 'mg' : weight = n*0.000001; break;
	  case 'g' : weight = n*0.001; break;
	  case 'h' : case 'hg' : weight = n*0.1; break;
	  case 'kg' : weight = n*1; break;
	  case 'q' : weight = n*100; break;
	  case 't' : weight = n*1000; break;
     }
    }
   }
   else
	weight = parseFloat(weight);
  }
  
  if(weight)
   totWeight+= (weight*qty);
 }

 /* UPDATE SUMMARY */
 var TOTALE_MERCE = 0;
 var TOTALE_SPESE = 0;
 var MERCE_SCONTATA = 0;
 var TOTALE_IMPONIBILE = 0;
 var TOTALE_IVA = 0;
 var SCONTO_1 = document.getElementById("globaldisc_perc1").value ? parseFloat(document.getElementById("globaldisc_perc1").value) : 0;
 var SCONTO_2 = document.getElementById("globaldisc_perc2").value ? parseFloat(document.getElementById("globaldisc_perc2").value) : 0;
 var UNCONDITIONAL_DISCOUNT = document.getElementById("unconditional_discount").value ? parseCurrency(document.getElementById("unconditional_discount").value) : 0;
 var REBATE = document.getElementById("rebate").value ? parseCurrency(document.getElementById("rebate").value) : 0;
 var STAMP = document.getElementById("stamp").value ? parseCurrency(document.getElementById("stamp").value) : 0;
 var COEFF_RIPARTO = 0;
 var TOTALE_RIVALSA_INPS = 0;
 var TOTALE_RITENUTA_ACCONTO = 0;
 var TOTALE_ENASARCO = 0;
 var TOTALE_CASSA_PREV = 0;


 var vatRatesListTB = document.getElementById('totalscloud-vatlist');
 while(vatRatesListTB.rows.length > 1)
  vatRatesListTB.deleteRow(1);

 for(var c=1; c < expTB.O.rows.length; c++)
 {
  var r = expTB.O.rows[c];
  if(r.isVoid())
   continue;
  if(VATS[r.getAttribute('vatid')])
   var vatInfo = VATS[r.getAttribute('vatid')];
  else
  {
   var vatInfo = {type:r.getAttribute('vattype'), rate:parseFloat(r.cell['vat'].getValue()), amount:0, expenses:0};
   VATS[r.getAttribute('vatid')] = vatInfo;
  }
  vatInfo.expenses+= parseCurrency(r.cell['amount'].getValue());
 }

 var vid=0;
 // al primo giro calcolo l'importo lordo //
 for(vid in VATS)
 {
  var r = vatRatesListTB.insertRow(-1);
  VATS[vid].row = r;
  r.insertCell(-1).innerHTML = VATS[vid].rate+"%";
  r.insertCell(-1).innerHTML = formatCurrency(VATS[vid].amount+VATS[vid].expenses)+" &euro;";
  TOTALE_MERCE+= VATS[vid].amount;
  TOTALE_SPESE+= VATS[vid].expenses;
  r.insertCell(-1).innerHTML = "&nbsp;";
  r.insertCell(-1).innerHTML = "&nbsp;";

  r.cells[0].style.textAlign='center';
  r.cells[1].style.textAlign='right';
  r.cells[2].style.textAlign='right';
  r.cells[3].style.textAlign='right';
 }

 /* DAL TOTALE MERCE RICAVIAMO IL COEFFICIENTE DI RIPARTO */
 if(UNCONDITIONAL_DISCOUNT && TOTALE_MERCE)
  COEFF_RIPARTO = UNCONDITIONAL_DISCOUNT/TOTALE_MERCE;

 // al secondo giro calcolo l'importo scontato ed il totale dell'imponibile // 
 var vid=0;
 var c=1;
 for(vid in VATS)
 {
  var r = vatRatesListTB.rows[c];
  c++;
  var value = VATS[vid].amount;
  if(value && SCONTO_1)
   value = value - ((value/100)*SCONTO_1);
  if(value && SCONTO_2)
   value = value - ((value/100)*SCONTO_2);
  // aggiungo le spese
  value+= VATS[vid].expenses;
  // sottraggo il riparto
  if(COEFF_RIPARTO)
   value-= VATS[vid].amount*COEFF_RIPARTO;
  // aggiungo la rivalsa inps
  if((CAT_TAG == "INVOICES") && RIVALSA_INPS)
  {
   var rivinps = value ? (value/100)*RIVALSA_INPS : 0;
   TOTALE_RIVALSA_INPS+= rivinps;
   value+= rivinps;
  }
  
  TOTALE_IMPONIBILE+=value;

  r.cells[2].innerHTML = formatCurrency(value)+" &euro;";
  VATS[vid].discounted = value;

  // calcolo l'IVA
  var vat = value ? (value/100)*VATS[vid].rate : 0;
  r.cells[3].innerHTML = formatCurrency(vat)+" &euro;";
  if(VATS[vid].type == "TAXABLE")
   TOTALE_IVA+= vat;
  VATS[vid].vat = vat;
 }
 

 /* CALCOLA LA MERCE SCONTATA */
 if(SCONTO_1)
  MERCE_SCONTATA = TOTALE_MERCE - ((TOTALE_MERCE/100)*SCONTO_1);
 else
  MERCE_SCONTATA = TOTALE_MERCE;

 /* CALCOLA LA CASSA PREV */
 if((CAT_TAG == "INVOICES") && CASSA_PREV)
 {
  var imp = MERCE_SCONTATA - UNCONDITIONAL_DISCOUNT;
  TOTALE_CASSA_PREV = imp ? (imp/100)*CASSA_PREV : 0;
 }
 document.getElementById("summary-cassa-prev").innerHTML = formatCurrency(TOTALE_CASSA_PREV)+" &euro;";


 // ricarico l'imponibile lordo includendo la cassa previdenziale //
 if(CASSA_PREV_VATID)
 {
  if(VATS[CASSA_PREV_VATID])
  {
   var vatInfo = VATS[CASSA_PREV_VATID];
   // aggiorno gli importi //
   var r = vatInfo.row;
   var value = vatInfo.amount+vatInfo.expenses+TOTALE_CASSA_PREV;
   r.cells[1].innerHTML = formatCurrency(value)+" &euro;";
   value = vatInfo.discounted+TOTALE_CASSA_PREV;
   r.cells[2].innerHTML = formatCurrency(value)+" &euro;";
   var vat = value ? ((value/100)*vatInfo.rate) : 0;
   if(vatInfo.type == "TAXABLE")
   {
    TOTALE_IVA-= vatInfo.vat;
	TOTALE_IVA+= vat;
	vatInfo.vat = vat;
    r.cells[3].innerHTML = formatCurrency(vat)+" &euro;";
   }
   else
    r.cells[3].innerHTML = formatCurrency(0)+" &euro;";
  }
  else
  {
   var vatInfo = {type:CASSA_PREV_VAT_TYPE, rate:CASSA_PREV, amount:0, expenses:0};
   // aggiungo una riga //
   var r = vatRatesListTB.insertRow(-1);
   r.insertCell(-1).innerHTML = vatInfo.rate+"%";
   r.insertCell(-1).innerHTML = formatCurrency(TOTALE_CASSA_PREV)+" &euro;";
   r.insertCell(-1).innerHTML = formatCurrency(TOTALE_CASSA_PREV)+" &euro;";

   var vat = 0;
   if(CASSA_PREV_VAT_TYPE == "TAXABLE")
   {
	vat = TOTALE_CASSA_PREV ? ((TOTALE_CASSA_PREV/100)*CASSA_PREV) : 0;
	TOTALE_IVA+= vat;
    r.insertCell(-1).innerHTML = formatCurrency(vat)+" &euro;";
   }
   else
    r.insertCell(-1).innerHTML = formatCurrency(0)+" &euro;";
 
   r.cells[0].style.textAlign='center';
   r.cells[1].style.textAlign='right';
   r.cells[2].style.textAlign='right';
   r.cells[3].style.textAlign='right';
  }

  TOTALE_IMPONIBILE+= TOTALE_CASSA_PREV;
 }


 
 document.getElementById("summary-total-goods").innerHTML = formatCurrency(TOTALE_MERCE)+" &euro;";
 document.getElementById("summary-total-expenses").innerHTML = formatCurrency(TOTALE_SPESE)+" &euro;";
 document.getElementById("summary-discounted-goods").innerHTML = formatCurrency(MERCE_SCONTATA)+" &euro;";
 document.getElementById("summary-unconditional-discount").innerHTML = formatCurrency(UNCONDITIONAL_DISCOUNT)+" &euro;";
 document.getElementById("summary-taxable").innerHTML = formatCurrency(TOTALE_IMPONIBILE)+" &euro;";
 document.getElementById("summary-total-vat").innerHTML = formatCurrency(TOTALE_IVA)+" &euro;";
 document.getElementById("summary-total-invoice").innerHTML = formatCurrency(TOTALE_IMPONIBILE+TOTALE_IVA)+" &euro;";
 document.getElementById("summary-rebate").innerHTML = formatCurrency(REBATE)+" &euro;";
 document.getElementById("summary-stamp").innerHTML = formatCurrency(STAMP)+" &euro;";
 document.getElementById("summary-rivinps").innerHTML = formatCurrency(TOTALE_RIVALSA_INPS)+" &euro;";
 if(document.getElementById("doctot-rivinps"))
  document.getElementById("doctot-rivinps").innerHTML = "<em>&euro;</em>"+formatCurrency(TOTALE_RIVALSA_INPS);


 document.getElementById("doctot-amount").innerHTML = "<em>&euro;</em>"+formatCurrency(TOTALE_IMPONIBILE);
 document.getElementById("doctot-vat").innerHTML = "<em>&euro;</em>"+formatCurrency(TOTALE_IVA);
 document.getElementById("doctot-total").innerHTML = "<em>&euro;</em>"+formatCurrency(TOTALE_IMPONIBILE+TOTALE_IVA);
 document.getElementById("doctot-discount").innerHTML = "<em>&euro;</em>"+formatCurrency(TOTALE_MERCE - MERCE_SCONTATA + UNCONDITIONAL_DISCOUNT);

 /* CALCOLO LA RITENUTA D'ACCONTO */
 if((CAT_TAG == "INVOICES") && RIT_ACCONTO)
 {
  var imp = MERCE_SCONTATA - UNCONDITIONAL_DISCOUNT;
  if(RIT_ACCONTO_RIVINPSINC)
   imp = imp ? (((imp+TOTALE_RIVALSA_INPS)/100)*RIT_ACCONTO_PERCIMP) : 0;
  else
   imp = imp ? ((imp/100)*RIT_ACCONTO_PERCIMP) : 0;
  TOTALE_RITENUTA_ACCONTO = imp ? (imp/100)*RIT_ACCONTO : 0;
 }
 document.getElementById("summary-ritenuta-acconto").innerHTML = formatCurrency(TOTALE_RITENUTA_ACCONTO)+" &euro;";
 if(document.getElementById("doctot-ritacconto"))
  document.getElementById("doctot-ritacconto").innerHTML = "<em>&euro;</em>"+formatCurrency(TOTALE_RITENUTA_ACCONTO);
 else if(document.getElementById("doctot-stamp"))
  document.getElementById("doctot-stamp").innerHTML = "<em>&euro;</em>"+formatCurrency(STAMP);

 /* CALCOLO ENASARCO */
 if((CAT_TAG == "INVOICES") && RIT_ENASARCO)
 {
  var imp = MERCE_SCONTATA - UNCONDITIONAL_DISCOUNT;
  imp = imp ? ((imp/100)*RIT_ENASARCO_PERCIMP) : 0;
  TOTALE_ENASARCO = imp ? (imp/100)*RIT_ENASARCO : 0;
 }
 document.getElementById("summary-enasarco").innerHTML = formatCurrency(TOTALE_ENASARCO)+" &euro;";


 /* CALCOLO IL NETTO A PAGARE */
 NET_PAY = (TOTALE_IMPONIBILE+TOTALE_IVA) - TOTALE_RITENUTA_ACCONTO - TOTALE_ENASARCO - REBATE + STAMP;
 document.getElementById("summary-net-pay").innerHTML = formatCurrency(NET_PAY)+" &euro;";
 if(document.getElementById("doctot-netpay"))
  document.getElementById("doctot-netpay").innerHTML = "<em>&euro;</em>"+formatCurrency(NET_PAY);

 /* EOF UPDATE SUMMARY ------------------------------------------------------------------------------------------------*/
 document.getElementById('doc_tot').innerHTML = formatCurrency(NET_PAY);
 document.getElementById('summary_doc_tot').innerHTML = formatCurrency(NET_PAY);
 _paymentModeSelectChange(document.getElementById('paymentmode-select'));
}

function _paymentModeSelectChange(sel)
{
 document.getElementById("paymodemenu").getElementsByTagName('SPAN')[0].innerHTML = sel.options[sel.selectedIndex].innerHTML;
 // aggiorna le scadenze //
 var tbsched = document.getElementById('schedule-table');
 var tbschedsmall = document.getElementById('schedule-table-small');

 /* empty tbschedsmall */
 while(tbschedsmall.rows.length)
  tbschedsmall.deleteRow(0);

 var amount = cleanRates();
 var docdate = strdatetime_to_iso(document.getElementById('docdate').innerHTML).substr(0,10);

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var date = new Date();
	 if(a['deadlines'])
	 {
	  for(var c=0; c < a['deadlines'].length; c++)
	  {
	   var itm = a['deadlines'][c];
	   date.setFromISO(itm['date']);
	   var r = tbsched.insertRow(-1);
	   r.insertCell(-1).innerHTML = "<b>"+r.rowIndex+"</b> <input type='text' style='width:90px' value='"+date.printf('d/m/Y')+"' onchange='scheduleDateChange(this)'/ > <i style='font-size:10px;'>(gg/mm/aaaa)</i>";
	   r.insertCell(-1).innerHTML = "<input type='text' style='width:60px' value='"+formatCurrency(itm['amount'],2)+"' onchange='scheduleAmountChange(this)'/ ><i>&euro;</i>";
	   r.insertCell(-1).innerHTML = "<input type='checkbox' onchange='schedulePaidChange(this)'/ >Pagato&nbsp;&nbsp;&nbsp;<i style='visibility:hidden;'>data:</i> <input type='text' style='width:90px' value='' style='visibility:hidden;' onchange='schedulePaidDateChange(this)'/ >";
	   r.insertCell(-1).innerHTML = "<img src='"+ABSOLUTE_URL+"GCommercialDocs/img/delete.gif' style='cursor:pointer;' onclick='deleteScheduleItem(this)'/ >";
	   r.cells[0].style.width = "190px";
	   r.cells[2].style.width = "190px";

	   NEW_RATES.push(r);

	   // update schedule table small //
	   var r = tbschedsmall.insertRow(-1);
	   r.insertCell(-1).innerHTML = "<span class='gray11'><b>"+(r.rowIndex+1)+"</b></span>";
	   r.insertCell(-1).innerHTML = date.printf('d/m/Y'); r.cells[1].style.textAlign='center';
	   r.insertCell(-1).innerHTML = "<span class='gray11'><b>"+formatCurrency(itm['amount'],2)+" &euro;</b></span>"; r.cells[2].style.textAlign='right';
	  }
	 }
	 updateTotRates(); 	 
	}
 sh.sendCommand("accounting paymentmodeinfo -id `"+sel.value+"` -amount '"+amount+"' -from '"+docdate+"' --get-deadlines");
}

function deleteDoc()
{
 if(!confirm("Sei sicuro di voler eliminare questo documento?"))
  return;
 var sh = new GShell();
 sh.OnOutput = function(){abort();}
 sh.sendCommand("dynarc delete-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>`");
}

function abort()
{
 if(window.opener)
 {
  /*window.opener.document.location.reload();*/
  window.close();
 }
 else
  document.location.href="index.php?doctype="+CAT_TAG.toLowerCase()+"&catid=<?php echo $docInfo['cat_id']; ?>";
}

function editDocTag(tag)
{
 DOC_TAG = tag;
 var tagTitle = "";
 switch(tag)
 {
  case 'DEFERRED' : tagTitle = "Differita"; break;
  default : tagTitle = "Accompagnatoria"; break;
 }
 
 document.getElementById('doctagname').getElementsByTagName('SPAN')[0].innerHTML = tagTitle+"<img src='"+ABSOLUTE_URL+"GCommercialDocs/img/tiptop-dnarr.png' class='ddmenu'/ >";
}

function getCurrentSubjectInfo()
{
 var ret = new Array();
 if(!RubricaEdit.value)
  return;
 else if(RubricaEdit.data)
 {
  ret['id'] = RubricaEdit.data['id'];
  ret['name'] = RubricaEdit.value;
 }
 else
 {
  ret['id'] = 0;
  ret['name'] = RubricaEdit.value;
 }
 return ret;
}

function saveDoc()
{
 if(UNKNOWN_ELEMENTS.length)
 {
  var products = new Array();
  var services = new Array();
  var supplies = new Array();

  for(var c=0; c < UNKNOWN_ELEMENTS.length; c++)
  {
   switch(UNKNOWN_ELEMENTS[c].getAttribute('type'))
   {
    case 'article' : products.push(UNKNOWN_ELEMENTS[c]); break;
	case 'service' : services.push(UNKNOWN_ELEMENTS[c]); break;
	case 'supply' : supplies.push(UNKNOWN_ELEMENTS[c]); break;
	default : products.push(UNKNOWN_ELEMENTS[c]); break;
   }
  }

  if(products.length)
  {
   var xml = "<xml><items>";
   for(var c=0; c < products.length; c++)
	xml+= "<item code=\""+products[c].cell['code'].getValue()+"\" name=\""+products[c].cell['description'].getValue()+"\" qty=\""+products[c].cell['qty'].getValue()+"\" units=\""+products[c].cell['units'].getValue()+"\" unitprice=\""+products[c].cell['unitprice'].getValue()+"\" vat=\""+products[c].cell['vat'].getValue()+"\"/"+">";
   xml+= "</items></xml>";

   var sh = new GShell();
   sh.OnError = function(msg,errcode){alert(msg);}
   sh.OnOutput = function(o,newelements){
	 UNKNOWN_ELEMENTS = new Array();
	 if(newelements)
	 {
	  for(var c=0; c < newelements.length; c++)
	  {
	   products[c].setAttribute('refid',newelements[c]);
	   products[c].setAttribute('refap',"gmart");
	  }
	 }
	 return saveDoc();
	}

   var vendorId = 0;
   var vendorName = "";

   switch(CAT_TAG)
   {
	case 'VENDORORDERS' : case 'PURCHASEINVOICES' : {
		 var subjInfo = getCurrentSubjectInfo();
		 vendorId = subjInfo ? subjInfo['id'] : 0;
		 vendorName = subjInfo ? subjInfo['name'] : "";
		} break;
   }
   

   sh.sendCommand("gframe -f commercialdocs/register-unknown-elements -xpn xmlelements -xpv `"+xml+"` -xpn docct -xpv `"+CAT_TAG+"` -xpn docid -xpv `<?php echo $docInfo['id']; ?>`"+(vendorId ? " -xpn vendorid -xpv `"+vendorId+"` -xpn vendorname -xpv `"+vendorName+"`" : ""));
   return;
  }

 }

 var notes = document.getElementById('notes').value;
 var docNum = document.getElementById('docnum').innerHTML;
 var docExt = "";
 if(docNum.indexOf("/") > 0)
 {
  docExt = docNum.substr(docNum.indexOf("/")+1);
  docNum = docNum.substr(0,docNum.indexOf("/"));
 }

 var docDate = document.getElementById('docdate').innerHTML;
 var docDateISO = strdatetime_to_iso(docDate);
 var extdocrefEd = document.getElementById('extdocref');


 if(!RubricaEdit.value)
 {
  var subjectId = 0;
  var subjectName = "";
 }
 else if(RubricaEdit.data)
 {
  var subjectId = RubricaEdit.data['id'];
  var subjectName = RubricaEdit.value;
 }
 else
 {
  var subjectId = 0;
  var subjectName = RubricaEdit.value;
 }

 var pricelistId = CUSTPLID;
 var paymentMode = document.getElementById('paymentmode-select').value;
 var bankSupportId = document.getElementById('banksupport-select').value;

 /* Shipping */
 var ship_contact_id = document.getElementById('shipping-contactselect').value;
 var ship_recp = document.getElementById('ship-subject').value;
 var ship_addr = document.getElementById('ship-address').value;
 var ship_city = document.getElementById('ship-city').value;
 var ship_zip = document.getElementById('ship-zipcode').value;
 var ship_prov = document.getElementById('ship-prov').value;
 var ship_cc = document.getElementById('ship-country').value; 
 
 /* Transport */
 var trans_method = 0;
 var tmp = document.getElementById('trans-methods').getElementsByTagName('INPUT');
 for(var c=0; c < tmp.length; c++)
 {
  if(tmp[c].checked == true)
   trans_method = c;
 }
 var trans_shipper = document.getElementById('trans_shipper').value;
 var trans_numplate = document.getElementById('trans_numplate').value;
 var trans_causal = document.getElementById('trans_causal').value;
 var trans_date = strdatetime_to_iso(document.getElementById('trans_date').value+" "+document.getElementById('trans_time').value);
 var trans_aspect = document.getElementById('trans_aspect').value;
 var trans_num = document.getElementById('trans_num').value;
 var trans_weight = document.getElementById('trans_weight').value.replace(",",".");
 var trans_freight = document.getElementById('trans_freight').value;

 var referenceId = document.getElementById('referencelist').value;

 var validityDate = "";
 var charterDateFrom = "";
 var charterDateTo = "";

 if(document.getElementById('validitydate') && document.getElementById('validitydate').value)
  validityDate = strdatetime_to_iso(document.getElementById('validitydate').value);
 if(document.getElementById('charterdatefrom') && document.getElementById('charterdatefrom').value)
  charterDateFrom = strdatetime_to_iso(document.getElementById('charterdatefrom').value);
 if(document.getElementById('charterdateto') && document.getElementById('charterdateto').value)
  charterDateTo = strdatetime_to_iso(document.getElementById('charterdateto').value);

 var SCONTO_1 = document.getElementById("globaldisc_perc1").value ? parseFloat(document.getElementById("globaldisc_perc1").value) : 0;
 var SCONTO_2 = document.getElementById("globaldisc_perc2").value ? parseFloat(document.getElementById("globaldisc_perc2").value) : 0;
 var UNCONDITIONAL_DISCOUNT = document.getElementById("unconditional_discount").value ? parseCurrency(document.getElementById("unconditional_discount").value) : 0;
 var REBATE = document.getElementById("rebate").value ? parseCurrency(document.getElementById("rebate").value) : 0;
 var STAMP = document.getElementById("stamp").value ? parseCurrency(document.getElementById("stamp").value) : 0;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){
		 if(!a || !a['last_element'] || !a['last_element']['ordering'])
		  return;
		 var r = tb.O.rows[parseInt(a['last_element']['ordering'])];
		 if(!r.id)
		  r.id = a['last_element']['id'];
		}

	 sh2.OnFinish = function(o,a){
		 if(a && a['last_element'] && a['last_element']['ordering'])
		 {
		  var r = tb.O.rows[parseInt(a['last_element']['ordering'])];
		  if(!r.id)
		   r.id = a['last_element']['id'];
		 }

		 var ser = "";
		 for(var c=1; c < tb.O.rows.length; c++)
		  ser+=","+tb.O.rows[c].id;
		 if(ser != "")
		 {
		  var sh3 = new GShell();
		  sh3.OnOutput = function(){savePayments();};
		  sh3.sendCommand("dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -extset `cdelements.serialize='"+ser.substr(1)+"'`");
		 }
		 else
		  savePayments();
		}
	 /* SAVE ROWS */
	 for(var c=0; c < DELETED_ROWS.length; c++)
	  sh2.sendCommand("dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -extunset `cdelements.id="+DELETED_ROWS[c].id+"`");
	 for(var c=0; c < UPDATED_ROWS.length; c++)
	 {
	  var r = UPDATED_ROWS[c];
	  var vatId = r.getAttribute('vatid') ? r.getAttribute('vatid') : 0;
	  var vatType = r.getAttribute('vattype') ? r.getAttribute('vattype') : "";
	  saveElement(r,sh2,r.id);
	  if((r.unitpriceChanged || r.discountChanged) && (CAT_TAG != "VENDORORDERS") && (CAT_TAG != "PURCHASEINVOICES"))
	  {
	   var discount = ((r.cell['discount'].getValue().indexOf("%") > 0) ? r.cell['discount'].getValue() : parseCurrency(r.cell['discount'].getValue().substr(3)));
	   var discount2 = r.cell['discount2'].getValue() ? parseFloat(r.cell['discount2'].getValue()) : 0;
	   var discount3 = r.cell['discount3'].getValue() ? parseFloat(r.cell['discount3'].getValue()) : 0;
	   if(!discount) discount=0;
	   if(r.getAttribute('refap') && r.getAttribute('refid'))
		sh2.sendCommand("dynarc edit-item -ap `"+r.getAttribute('refap')+"` -id `"+r.getAttribute('refid')+"` -extset `custompricing."+(subjectId ? "subjectid="+subjectId : "subject='''"+subjectName+"'''")+",baseprice='"+parseCurrency(r.cell['unitprice'].getValue())+"',discount='"+discount+"',discount2='"+discount2+"',discount3='"+discount3+"'`");
	  }
	 }
	 for(var c=0; c < NEW_ROWS.length; c++)
	 {
	  var r = NEW_ROWS[c];
	  var vatId = r.getAttribute('vatid') ? r.getAttribute('vatid') : 0;
	  var vatType = r.getAttribute('vattype') ? r.getAttribute('vattype') : "";
	  saveElement(r,sh2);
	  if((r.unitpriceChanged || r.discountChanged) && (CAT_TAG != "VENDORORDERS") && (CAT_TAG != "PURCHASEINVOICES"))
	  {
	   var discount = ((r.cell['discount'].getValue().indexOf("%") > 0) ? r.cell['discount'].getValue() : parseCurrency(r.cell['discount'].getValue().substr(3)));
	   var discount2 = r.cell['discount2'].getValue() ? parseFloat(r.cell['discount2'].getValue()) : 0;
	   var discount3 = r.cell['discount3'].getValue() ? parseFloat(r.cell['discount3'].getValue()) : 0;

	   if(!discount) discount=0;
	   if(r.getAttribute('refap') && r.getAttribute('refid'))
		sh2.sendCommand("dynarc edit-item -ap `"+r.getAttribute('refap')+"` -id `"+r.getAttribute('refid')+"` -extset `custompricing."+(subjectId ? "subjectid="+subjectId : "subject='''"+subjectName+"'''")+",baseprice='"+parseCurrency(r.cell['unitprice'].getValue())+"',discount='"+discount+"',discount2='"+discount2+"',discount3='"+discount3+"'`");
	  }
	 }

	 if(!DELETED_ROWS.length && !UPDATED_ROWS.length && !NEW_ROWS.length)
	  savePayments();

	 DELETED_ROWS = new Array();
	 UPDATED_ROWS = new Array();
	 NEW_ROWS = new Array();
	}

 // includo le spese //
 var expidx = 1;
 var expqry = "";
 for(var c=1; c < expTB.O.rows.length; c++)
 {
  var r = expTB.O.rows[c];
  if(r.isVoid())
   continue;
  
  expqry+= ",exp"+expidx+"-name='''"+r.cell['name'].getValue()+"''',exp"+expidx+"-vatid='"+r.getAttribute('vatid')+"',exp"+expidx+"-amount='"+parseCurrency(r.cell['amount'].getValue())+"'";  
  expidx++;
 }
 if(expidx < 4)
 {
  for(var c=expidx; c < 4; c++)
  {
   expqry+= ",exp"+expidx+"-name='',exp"+expidx+"-vatid='0',exp"+expidx+"-amount='0'";  
   expidx++;
  }
 }


 sh.sendCommand("dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -desc `"+notes+"` -code-num `"+docNum+"` -code-ext `"+docExt+"` -ctime `"+docDateISO+"` -extset `cdinfo."+(subjectId ? "subjectid="+subjectId : "subject='''"+subjectName+"'''")+",referenceid='"+referenceId+"',tag='"+DOC_TAG+"',pricelist='"+pricelistId+"',paymentmode='"+paymentMode+"',banksupport='"+bankSupportId+"',ship-contact-id='"+ship_contact_id+"',ship-recp='''"+ship_recp+"''',ship-addr='''"+ship_addr+"''',ship-city='''"+ship_city+"''',ship-zip='"+ship_zip+"',ship-prov='"+ship_prov+"',ship-cc='"+ship_cc+"',trans-method='"+trans_method+"',trans-shipper='''"+trans_shipper+"''',trans-numplate='"+trans_numplate+"',trans-causal='''"+trans_causal+"''',trans-datetime='"+trans_date+"',trans-aspect='''"+trans_aspect+"''',trans-num='"+trans_num+"',trans-weight='''"+trans_weight+"''',trans-freight='''"+trans_freight+"''',validity-date='"+validityDate+"',charter-datefrom='"+charterDateFrom+"',charter-dateto='"+charterDateTo+"',extdocref='''"+(extdocrefEd ? extdocrefEd.value : "")+"''',discount1='"+SCONTO_1+"',discount2='"+SCONTO_2+"',uncondisc='"+UNCONDITIONAL_DISCOUNT+"',rebate='"+REBATE+"',stamp='"+STAMP+"'"+expqry+"`");
}

function saveElement(r,sh,id)
{
 var vatId = r.getAttribute('vatid') ? r.getAttribute('vatid') : 0;
 var vatType = r.getAttribute('vattype') ? r.getAttribute('vattype') : "";
 var cdelementsQry = id ? "id="+id : "type='"+r.getAttribute('type')+"'";

 switch(r.getAttribute('type'))
 {
  case 'note' : cdelementsQry+= ",desc='''"+r.cells[1].getElementsByTagName('SPAN')[0].innerHTML+"''',ordering="+r.rowIndex; break;
  default : {
	 var discount = ((r.cell['discount'].getValue().indexOf("%") > 0) ? r.cell['discount'].getValue() : parseCurrency(r.cell['discount'].getValue().substr(3)));
	 var discount2 = r.cell['discount2'].getValue() ? parseFloat(r.cell['discount2'].getValue()) : 0;
	 var discount3 = r.cell['discount3'].getValue() ? parseFloat(r.cell['discount3'].getValue()) : 0;

	 cdelementsQry+= ",refap='"+r.getAttribute('refap')+"',refid='"+r.getAttribute('refid')+"',code='"+r.cell['code'].getValue()+"',sn='"+r.cell['sn'].getValue()+"',lot='"+r.cell['lot'].getValue()+"',name='''"+r.cell['description'].getValue()+"''',qty='"+r.cell['qty'].getValue()+"',extraqty='"+r.cell['extraqty'].getValue()+"',units='"+r.cell['units'].getValue()+"',price='"+parseCurrency(r.cell['unitprice'].getValue())+"',priceadjust='"+parseCurrency(r.cell['priceadjust'].getValue())+"',discount='"+discount+"',discount2='"+discount2+"',discount3='"+discount3+"',vatrate='"+parseFloat(r.cell['vat'].getValue())+"',vatid='"+vatId+"',vattype='"+vatType+"',pricelistid='"+r.cell['pricelist'].getAttribute('pricelistid')+"'"+(!id ? ",ordering="+r.rowIndex : "");
	 <?php
	 for($c=0; $c < count($_COMPANY_PROFILE['extracolumns']); $c++)
	 {
	  $tmp = "cdelementsQry+= \",".$_COMPANY_PROFILE['extracolumns'][$c]['tag']."=";
	  switch($_COMPANY_PROFILE['extracolumns'][$c]['format'])
	  {
	   case 'number' : $tmp.= "'\"+r.cell['".$_COMPANY_PROFILE['extracolumns'][$c]['tag']."'].getValue()+\"'\";\n"; break;
	   case 'currency' : $tmp.= "'\"+parseCurrency(r.cell['".$_COMPANY_PROFILE['extracolumns'][$c]['tag']."'].getValue())+\"'\";\n"; break;
	   default : $tmp.= "'''\"+r.cell['".$_COMPANY_PROFILE['extracolumns'][$c]['tag']."'].getValue()+\"'''\";\n"; break;
	  }
	  echo $tmp;
	 }
	 ?>
	} break;
 }

 if(sh)
  sh.sendCommand("dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -extset `cdelements."+cdelementsQry+"`");
 else
  return cdelementsQry;
}

function savePayments()
{
 if(!RubricaEdit.value)
 {
  var subjectId = 0;
  var subjectName = "";
 }
 else if(RubricaEdit.data)
 {
  var subjectId = RubricaEdit.data['id'];
  var subjectName = RubricaEdit.value;
 }
 else
 {
  var subjectId = 0;
  var subjectName = RubricaEdit.value;
 }
 
 var isdebit = false;
 switch(CAT_TAG.toLowerCase())
 {
  case 'agentinvoices' : case 'vendororders' : case 'purchaseinvoices' : case 'creditsnote' : isdebit=true; break;
 }

 if(!NEW_DEPOSITS.length && !UPDATED_DEPOSITS.length && !DELETED_DEPOSITS.length && !NEW_RATES.length && !UPDATED_RATES.length && !DELETED_RATES.length)
  return saveFinish(); //alert("Il documento è stato salvato correttamente");

 var sh = new GShell();
 sh.OnFinish = function(){
	 saveFinish(); //alert('Il documento è stato salvato correttamente!');
	}

 /* CLEAN MMR */
 sh.sendCommand("mmr clean -docid `<?php echo $docInfo['id']; ?>`");

 for(var c=0; c < NEW_DEPOSITS.length; c++)
 {
  var r = NEW_DEPOSITS[c];
  var incomes = parseCurrency(r.cells[0].getElementsByTagName('INPUT')[0].value);
  var paymentDate = strdatetime_to_iso(r.cells[1].getElementsByTagName('INPUT')[0].value);
  sh.sendCommand("dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -extset `mmr."+(isdebit ? "expenses" : "incomes")+"='"+incomes+"',payment='"+paymentDate+"',description='Acconto',subject='''"+subjectName+"''',subjectid='"+subjectId+"'`");
 }

 for(var c=0; c < UPDATED_DEPOSITS.length; c++)
 {
  var r = UPDATED_DEPOSITS[c];
  var id = r.id.substr(4);
  var incomes = parseCurrency(r.cells[0].getElementsByTagName('INPUT')[0].value);
  var paymentDate = strdatetime_to_iso(r.cells[1].getElementsByTagName('INPUT')[0].value);
  sh.sendCommand("dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -extset `mmr."+(isdebit ? "expenses" : "incomes")+"='"+incomes+"',payment='"+paymentDate+"',description='Acconto',subject='''"+subjectName+"''',subjectid='"+subjectId+"'`");
 }

 for(var c=0; c < DELETED_DEPOSITS.length; c++)
 {
  var r = DELETED_DEPOSITS[c];
  var id = r.id.substr(4);
  sh.sendCommand("dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -extunset `mmr.id='"+id+"'`");
 }

 for(var c=0; c < NEW_RATES.length; c++)
 {
  var r = NEW_RATES[c];
  var expireDate = strdatetime_to_iso(r.cells[0].getElementsByTagName('INPUT')[0].value);
  var incomes = parseCurrency(r.cells[1].getElementsByTagName('INPUT')[0].value);
  if(r.cells[2].getElementsByTagName('INPUT')[0].checked == true)
   var paymentDate = strdatetime_to_iso(r.cells[2].getElementsByTagName('INPUT')[1].value);
  else
   var paymentDate = "";
  sh.sendCommand("dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -extset `mmr."+(isdebit ? "expenses" : "incomes")+"='"+incomes+"',payment='"+paymentDate+"',expire='"+expireDate+"',description='Rata n."+r.rowIndex+" scad. "+r.cells[0].getElementsByTagName('INPUT')[0].value+"',subject='''"+subjectName+"''',subjectid='"+subjectId+"'`");
 }

 for(var c=0; c < UPDATED_RATES.length; c++)
 {
  var r = UPDATED_RATES[c];
  var id = r.id.substr(4);
  var expireDate = strdatetime_to_iso(r.cells[0].getElementsByTagName('INPUT')[0].value);
  var incomes = parseCurrency(r.cells[1].getElementsByTagName('INPUT')[0].value);
  if(r.cells[2].getElementsByTagName('INPUT')[0].checked == true)
   var paymentDate = strdatetime_to_iso(r.cells[2].getElementsByTagName('INPUT')[1].value);
  else
   var paymentDate = "";
  sh.sendCommand("dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -extset `mmr."+(isdebit ? "expenses" : "incomes")+"='"+incomes+"',payment='"+paymentDate+"',expire='"+expireDate+"',description='Rata n."+r.rowIndex+" scad. "+r.cells[0].getElementsByTagName('INPUT')[0].value+"',subject='''"+subjectName+"''',subjectid='"+subjectId+"'`");
 }

 for(var c=0; c < DELETED_RATES.length; c++)
 {
  var r = DELETED_RATES[c];
  var id = r.id.substr(4);
  sh.sendCommand("dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -extunset `mmr.id='"+id+"'`");
 }
}

function saveFinish()
{
 var sh = new GShell();
 sh.OnError = function(msg){alert(msg);}
 sh.OnOutput = function(){alert('Il documento è stato salvato correttamente!');}
 sh.sendCommand("commercialdocs updatetotals -id `<?php echo $docInfo['id']; ?>`");
}

function addDeposit()
{
 var date = new Date();
 var tbdep = document.getElementById('deposits-table');
 var r = tbdep.insertRow(-1);
 r.insertCell(-1).innerHTML = "<i>&euro;</i> <input type='text' style='width:60px' value='' onchange='updateTotDeposits(this.parentNode.parentNode)'/ >";
 r.insertCell(-1).innerHTML = "<i>Data</i> <input type='text' style='width:90px' value='"+date.printf('d/m/Y')+"'/ >";
 r.insertCell(-1).innerHTML = "<img src='"+ABSOLUTE_URL+"GCommercialDocs/img/delete.gif' style='cursor:pointer;' onclick='deleteDepositItem(this)'/ >";
 r.cells[0].getElementsByTagName('INPUT')[0].focus();
}

function deleteDepositItem(img)
{
 if(!confirm("Sei sicuro di voler rimuovere questo acconto?"))
  return;
 var r = img.parentNode.parentNode;

 if(r.id)
  DELETED_DEPOSITS.push(r);
 else if(NEW_DEPOSITS.indexOf(r) >= 0)
  NEW_DEPOSITS.splice(NEW_DEPOSITS.indexOf(r),1);

 var tbdep = document.getElementById('deposits-table');
 tbdep.deleteRow(r.rowIndex);

 updateTotDeposits();
}

function updateTotDeposits(r)
{
 if(r)
 {
  if(r.id && (UPDATED_DEPOSITS.indexOf(r) < 0))
   UPDATED_DEPOSITS.push(r);
  else if(!r.id && (NEW_DEPOSITS.indexOf(r) < 0))
   NEW_DEPOSITS.push(r);
 }

 var tbdep = document.getElementById('deposits-table');
 var tot = 0;
 for(var c=1; c < tbdep.rows.length; c++)
  tot+= parseCurrency(tbdep.rows[c].cells[0].getElementsByTagName('INPUT')[0].value);
 document.getElementById('tot_deposits').innerHTML = "&euro;.&nbsp;&nbsp;&nbsp;"+formatCurrency(tot,2);
 document.getElementById('summary_deposits_tot').innerHTML = formatCurrency(tot,2)+" &euro;";

 updateRateValues();
}

function updateSummary()
{
 var totDoc = parseCurrency(document.getElementById('doc_tot').innerHTML);
 var totDeposits = parseCurrency(document.getElementById('tot_deposits').innerHTML.substr(20));
 var totPaidSchedule = parseCurrency(document.getElementById('tot_paid_schedule').innerHTML.substr(20));

 var result = totDoc - totDeposits - totPaidSchedule;

 document.getElementById('summary_results').innerHTML = formatCurrency(result,2)+" &euro;";
}

function addRate()
{
 var date = new Date();
 var tbsched = document.getElementById('schedule-table');
 var tbschedsmall = document.getElementById('schedule-table-small');

 var totDoc = parseCurrency(document.getElementById('doc_tot').innerHTML);
 var totDeposits = parseCurrency(document.getElementById('tot_deposits').innerHTML.substr(20));
 var amount = totDoc-totDeposits;

 /* Detect paid schedules */
 var unpaid = 1;
 for(var c=1; c < tbsched.rows.length; c++)
 {
  if(tbsched.rows[c].cells[2].getElementsByTagName('INPUT')[0].checked == true)
   amount-= parseCurrency(tbsched.rows[c].cells[1].getElementsByTagName('INPUT')[0].value);
  else
   unpaid++;
  date.setFromISO(strdatetime_to_iso(tbsched.rows[c].cells[0].getElementsByTagName('INPUT')[0].value));
 }

 date.NextMonth();
 var rate = amount/unpaid;

 for(var c=1; c < tbsched.rows.length; c++)
 {
  if(!tbsched.rows[c].cells[2].getElementsByTagName('INPUT')[0].checked)
  {
   tbsched.rows[c].cells[1].getElementsByTagName('INPUT')[0].value = formatCurrency(rate,2);
   if(tbsched.rows[c].id && (UPDATED_RATES.indexOf(tbsched.rows[c]) < 0))
	UPDATED_RATES.push(tbsched.rows[c]);
   tbschedsmall.rows[c-1].cells[2].innerHTML = "<span class='gray11'><b>"+formatCurrency(rate,2)+" &euro;</b></span>";
  }
 }
 
 var r = tbsched.insertRow(-1);
 r.insertCell(-1).innerHTML = "<b>"+r.rowIndex+"</b> <input type='text' style='width:90px' value='"+date.printf('d/m/Y')+"' onchange='scheduleDateChange(this)'/ > <i style='font-size:10px;'>(gg/mm/aaaa)</i>";
 r.insertCell(-1).innerHTML = "<input type='text' style='width:60px' value='"+formatCurrency(rate,2)+"' onchange='scheduleAmountChange(this)'/ ><i>&euro;</i>";
 r.insertCell(-1).innerHTML = "<input type='checkbox' onchange='schedulePaidChange(this)'/ >Pagato&nbsp;&nbsp;&nbsp;<i style='visibility:hidden;'>data:</i> <input type='text' size='8' value='' style='visibility:hidden;' onchange='schedulePaidDateChange(this)'/ >";
 r.insertCell(-1).innerHTML = "<img src='"+ABSOLUTE_URL+"GCommercialDocs/img/delete.gif' style='cursor:pointer;' onclick='deleteScheduleItem(this)'/ >";
 r.cells[0].style.width = "190px";
 r.cells[2].style.width = "190px";

 r.cells[0].getElementsByTagName('INPUT')[0].focus();

 NEW_RATES.push(r);

 var _r = tbschedsmall.insertRow(-1); 
 _r.insertCell(-1).innerHTML = "<span class='gray11'><b>"+(_r.rowIndex+1)+"</b></span>";
 _r.insertCell(-1).innerHTML = date.printf('d/m/Y'); _r.cells[1].style.textAlign='center';
 _r.insertCell(-1).innerHTML = "<span class='gray11'><b>"+formatCurrency(rate,2)+" &euro;</b></span>"; _r.cells[2].style.textAlign='right';


 updateTotRates();
}

function updateTotRates()
{
 var tbsched = document.getElementById('schedule-table');
 var amount = 0;
 var paid = 0;
 for(var c=1; c < tbsched.rows.length; c++)
 {
  if(tbsched.rows[c].cells[2].getElementsByTagName('INPUT')[0].checked == true)
   paid+= parseCurrency(tbsched.rows[c].cells[1].getElementsByTagName('INPUT')[0].value);
  amount+= parseCurrency(tbsched.rows[c].cells[1].getElementsByTagName('INPUT')[0].value);
 }
 document.getElementById('tot_schedule').innerHTML = "&euro;.&nbsp;&nbsp;&nbsp;"+formatCurrency(amount,2);
 document.getElementById('tot_paid_schedule').innerHTML = "&euro;.&nbsp;&nbsp;&nbsp;"+formatCurrency(paid,2);
 document.getElementById('summary_paid_schedule_tot').innerHTML = formatCurrency(paid,2)+" &euro;";
 updateSummary();
}

function schedulePaidChange(cb)
{
 cb.parentNode.getElementsByTagName('I')[0].style.visibility = cb.checked ? "visible" : "hidden";
 var ed = cb.parentNode.getElementsByTagName('INPUT')[1];
 ed.style.visibility = cb.checked ? "visible" : "hidden";
 if(!ed.value)
 {
  var date = new Date();
  ed.value = date.printf('d/m/Y');
 }

 var r = cb.parentNode.parentNode;
 if(r.id && (UPDATED_RATES.indexOf(r) < 0))
  UPDATED_RATES.push(r);

 updateTotRates();
}

function schedulePaidDateChange(ed)
{
 var r = ed.parentNode.parentNode;
 if(r.id && (UPDATED_RATES.indexOf(r) < 0))
  UPDATED_RATES.push(r);
}

function scheduleDateChange(ed)
{
 var r = ed.parentNode.parentNode;
 if(r.id && (UPDATED_RATES.indexOf(r) < 0))
  UPDATED_RATES.push(r);
}

function scheduleAmountChange(ed)
{
 var date = new Date();
 var r = ed.parentNode.parentNode;
 var tbsched = document.getElementById('schedule-table');
 var tbschedsmall = document.getElementById('schedule-table-small');
 tbschedsmall.rows[r.rowIndex-1].cells[2].innerHTML = "<span class='gray11'><b>"+formatCurrency(parseCurrency(ed.value),2)+" &euro;</b></span>";

 if(r.id && (UPDATED_RATES.indexOf(r) < 0))
  UPDATED_RATES.push(r);

 var totDoc = parseCurrency(document.getElementById('doc_tot').innerHTML);
 var totDeposits = parseCurrency(document.getElementById('tot_deposits').innerHTML.substr(20));
 var amount = totDoc-totDeposits;

 /* Detect paid schedules */
 var divideBy = 0;
 var fixedRates = 0;
 for(var c=1; c < tbsched.rows.length; c++)
 {
  if(tbsched.rows[c].cells[2].getElementsByTagName('INPUT')[0].checked == true)
   amount-= parseCurrency(tbsched.rows[c].cells[1].getElementsByTagName('INPUT')[0].value);
  else if(c > r.rowIndex)
   divideBy++;
  else
   fixedRates+= parseCurrency(tbsched.rows[c].cells[1].getElementsByTagName('INPUT')[0].value);
  date.setFromISO(strdatetime_to_iso(tbsched.rows[c].cells[0].getElementsByTagName('INPUT')[0].value));
 }

 amount-= fixedRates;

 if(amount > 0)
 {
  var rate = divideBy ? amount/divideBy : amount;

  for(var c=r.rowIndex+1; c < tbsched.rows.length; c++)
  {
   if(!tbsched.rows[c].cells[2].getElementsByTagName('INPUT')[0].checked)
   {
    tbsched.rows[c].cells[1].getElementsByTagName('INPUT')[0].value = formatCurrency(rate,2);
	tbschedsmall.rows[c-1].cells[2].innerHTML = "<span class='gray11'><b>"+formatCurrency(rate,2)+" &euro;</b></span>";
    if(tbsched.rows[c].id && (UPDATED_RATES.indexOf(tbsched.rows[c]) < 0))
	 UPDATED_RATES.push(tbsched.rows[c]);
	amount-= rate;
   }
  }
  
  if(amount > 0)
  {
   date.NextMonth();
   var r2 = tbsched.insertRow(-1);
   r2.insertCell(-1).innerHTML = "<b>"+r2.rowIndex+"</b> <input type='text' style='width:90px' value='"+date.printf('d/m/Y')+"'/ > <i style='font-size:10px;'>(gg/mm/aaaa)</i>";
   r2.insertCell(-1).innerHTML = "<input type='text' style='width:60px' value='"+formatCurrency(amount,2)+"' onchange='scheduleAmountChange(this)'/ ><i>&euro;</i>";
   r2.insertCell(-1).innerHTML = "<input type='checkbox' onchange='schedulePaidChange(this)'/ >Pagato&nbsp;&nbsp;&nbsp;<i style='visibility:hidden;'>data:</i> <input type='text' size='8' value='' style='visibility:hidden;' onchange='schedulePaidDateChange(this)'/ >";
   r2.insertCell(-1).innerHTML = "<img src='"+ABSOLUTE_URL+"GCommercialDocs/img/delete.gif' style='cursor:pointer;' onclick='deleteScheduleItem(this)'/ >";
   r2.cells[0].style.width = "190px";
   r2.cells[2].style.width = "190px";

   r2.cells[0].getElementsByTagName('INPUT')[0].focus();
   NEW_RATES.push(r2);

   var _r = tbschedsmall.insertRow(-1);
   _r.insertCell(-1).innerHTML = "<span class='gray11'><b>"+(_r.rowIndex+1)+"</b></span>";
   _r.insertCell(-1).innerHTML = date.printf('d/m/Y'); _r.cells[1].style.textAlign='center';
   _r.insertCell(-1).innerHTML = "<span class='gray11'><b>"+formatCurrency(amount,2)+" &euro;</b></span>"; _r.cells[2].style.textAlign='right';
  }
 }
 updateTotRates();
}

function deleteScheduleItem(img)
{
 if(!confirm("Sei sicuro di voler rimuovere questa rata?"))
  return;

 var r = img.parentNode.parentNode;
 if(r.id)
 {
  DELETED_RATES.push(r);
  if(UPDATED_RATES.indexOf(r) >= 0)
   UPDATED_RATES.splice(UPDATED_RATES.indexOf(r),1);
 }
 else if(NEW_RATES.indexOf(r) >= 0)
  NEW_RATES.splice(NEW_RATES.indexOf(r),1);

 var tbsched = document.getElementById('schedule-table');
 var tbschedsmall = document.getElementById('schedule-table-small');
 if((r.rowIndex > 0) && tbschedsmall.rows[r.rowIndex-1])
  tbschedsmall.deleteRow(r.rowIndex-1);
 tbsched.deleteRow(r.rowIndex);
 updateRateValues();
}

function cleanRates()
{
 var tbsched = document.getElementById('schedule-table');
 var tbschedsmall = document.getElementById('schedule-table-small');
 var remrows = new Array();
 for(var c=1; c < tbsched.rows.length; c++)
 {
  var r = tbsched.rows[c];
  if(r.cells[2].getElementsByTagName('INPUT')[0].checked == true)
   continue;
  if(r.id)
  {
   DELETED_RATES.push(r);
   if(UPDATED_RATES.indexOf(r) >= 0)
    UPDATED_RATES.splice(UPDATED_RATES.indexOf(r),1);
  }
  else if(NEW_RATES.indexOf(r) >= 0)
   NEW_RATES.splice(NEW_RATES.indexOf(r),1);
  remrows.push(r);
 }

 for(var c=0; c < remrows.length; c++)
 {
  if((remrows[c].rowIndex > 0) && tbschedsmall.rows[remrows[c].rowIndex-1])
   tbschedsmall.deleteRow(remrows[c].rowIndex-1);
  tbsched.deleteRow(remrows[c].rowIndex);
 }

 return updateRateValues();
}

function updateRateValues()
{
 var date = new Date();
 var tbsched = document.getElementById('schedule-table');
 var tbschedsmall = document.getElementById('schedule-table-small');

 var totDoc = parseCurrency(document.getElementById('doc_tot').innerHTML);
 var totDeposits = parseCurrency(document.getElementById('tot_deposits').innerHTML.substr(20));
 var amount = totDoc-totDeposits;

 /* Detect paid schedules */
 var unpaid = 0;
 for(var c=1; c < tbsched.rows.length; c++)
 {
  if(tbsched.rows[c].cells[2].getElementsByTagName('INPUT')[0].checked == true)
   amount-= parseCurrency(tbsched.rows[c].cells[1].getElementsByTagName('INPUT')[0].value);
  else
   unpaid++;
  tbsched.rows[c].cells[0].getElementsByTagName('B')[0].innerHTML = tbsched.rows[c].rowIndex;
  tbschedsmall.rows[c-1].cells[0].innerHTML = "<span class='gray11'><b>"+tbsched.rows[c].rowIndex+"</b></span>";
  date.setFromISO(strdatetime_to_iso(tbsched.rows[c].cells[0].getElementsByTagName('INPUT')[0].value));
 }

 date.NextMonth();
 var rate = unpaid ? amount/unpaid : amount;

 for(var c=1; c < tbsched.rows.length; c++)
 {
  if(!tbsched.rows[c].cells[2].getElementsByTagName('INPUT')[0].checked)
  {
   tbsched.rows[c].cells[1].getElementsByTagName('INPUT')[0].value = formatCurrency(rate,2);
   tbschedsmall.rows[c-1].cells[2].innerHTML = "<span class='gray11'><b>"+formatCurrency(rate,2)+" &euro;</b></span>";
   if(tbsched.rows[c].id && (UPDATED_RATES.indexOf(tbsched.rows[c]) < 0))
	UPDATED_RATES.push(tbsched.rows[c]);
  }
 }

 updateTotRates();
 return amount;
}

function depositDateChanged(ed)
{
 var r = ed.parentNode.parentNode;
 if(r.id && (UPDATED_DEPOSITS.indexOf(r) < 0))
  UPDATED_DEPOSITS.push(r);
}

function editDocNum()
{
 var date = new Date();
 date.setFromISO(strdatetime_to_iso(document.getElementById('docdate').innerHTML));

 var docNum = prompt("Modifica numero di documento",document.getElementById('docnum').innerHTML);
 if(!docNum)
  return;

 var docExt = "";
 if(docNum.indexOf("/") > 0)
 {
  docExt = docNum.substr(docNum.indexOf("/")+1);
  docNum = docNum.substr(0,docNum.indexOf("/"));
 }

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(a && a['items'])
	 {
	  if(confirm("Esiste già un documento con questo numero:\n"+a['items'][0]['name']+"\nDesideri modificarlo comunque?"))
	   document.getElementById('docnum').innerHTML = docNum+(docExt ? "/"+docExt : "");
	 }
	 else
	  document.getElementById('docnum').innerHTML = docNum+(docExt ? "/"+docExt : "");
	}
 sh.sendCommand("dynarc item-list -ap `commercialdocs` -ct `"+CAT_TAG+"` -where `ctime>='"+date.getFullYear()+"-01-01' AND ctime<'"+(date.getFullYear()+1)+"-01-01' AND code_num='"+docNum+"' AND code_ext='"+docExt+"' AND id!='<?php echo $docInfo['id']; ?>'`");
}

function editDocDate()
{
 var docDate = prompt("Modifica data documento",document.getElementById('docdate').innerHTML);
 if(!docDate)
  return;
 var date = new Date();
 var now = new Date(); // ci servirà solamente per le ore ed i minuti
 date.setFromISO(strdatetime_to_iso(docDate));
 document.getElementById('docdate').innerHTML = date.printf('d/m/Y');

 if((CAT_TAG == "INVOICES") || (CAT_TAG == "DDT"))
 {
  if(confirm("Desideri che aggiorno in automatico anche la data e l'ora del trasporto?"))
  {
   document.getElementById('trans_date').value = date.printf('d/m/Y');
   document.getElementById('trans_time').value = now.printf('H:i');
  }
 }
}

function restoreDocument()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 alert("Il documento è stato ripristinato!");
	 document.location.reload();
	}
 sh.sendCommand("dynarc trash restore -ap commercialdocs -id `<?php echo $docInfo['id']; ?>`");
}

function unlockDoc()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.reload();
	}
 sh.sendCommand("gframe -f commercialdocs/unlock -params `ap=commercialdocs&id=<?php echo $docInfo['id']; ?>`");
}

function saveColumnsSettings(ul)
{
 var tbcs = document.getElementById('doctable');
 var columns = new Array();
 for(var c=1; c < tbcs.rows[0].cells.length; c++)
  columns.push(tbcs.rows[0].cells[c].id);

 var list = ul.getElementsByTagName('LI');
 var qry = "";
 for(var c=0; c < columns.length; c++)
 {
  if(!list[c].getElementsByTagName('INPUT').length)
   continue;
  var cb = list[c].getElementsByTagName('INPUT')[0];
  qry+= " && export GCD-COL-"+columns[c].toUpperCase()+"="+(cb.checked ? "ON" : "OFF");
 }

 var sh = new GShell();
 sh.OnOutput = function(){alert("Impostazioni colonne salvate correttamente");}
 sh.sendCommand(qry.substr(4));
}

/*function makeINCDiscount()
{
 var disc = prompt("Inserisci l'importo relativo allo sconto incondizionato da applicare a tutto il documento");
 if(disc == "0")
 {
  for(var c=1; c < tb.O.rows.length; c++)
  {
   var r = tb.O.rows[c];
   if(r.getAttribute('type') == "note")
    continue;
   r.cell['discount'].setValue("0");
   if(r.id && (UPDATED_ROWS.indexOf(r) < 0))
	UPDATED_ROWS.push(r);
   updateTotals(r);
  }
  return;
 }

 if(!disc)
  return;

 if(!parseFloat(disc))
  return;

 var discount = parseFloat(disc)/((DOC_AMOUNT+DOC_DISCOUNT)/100);
 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];
  if(r.getAttribute('type') == "note")
   continue;
  r.cell['discount'].setValue(formatNumber(discount,2)+"%");
  r.cell['discount2'].setValue(0); // resetto le altre colonne degli sconti //
  r.cell['discount3'].setValue(0); // resetto le altre colonne degli sconti //
  if(r.id && (UPDATED_ROWS.indexOf(r) < 0))
   UPDATED_ROWS.push(r);
  updateTotals(r);
 }
}*/

/* ATTACHMENTS */

var activeAttachmentsForm = null;

function editAttachment(id)
{
 var div = document.createElement('DIV');
 div.className = "editform";
 div.style.visibility='hidden';
 _showScreenMask();
 document.body.appendChild(div);
 div.style.left =_getScreenWidth()/2-(div.offsetWidth/2);
 div.style.top = _getScreenHeight()/2-(div.offsetHeight/2);
 div.style.visibility='';

 NewLayer("dyn-attachments/forms","formtype=editatt&id="+id,div);
 activeAttachmentsForm = div;
}

function deleteAttachment(id)
{
 if(!confirm("Sei sicuro di voler eliminare questo allegato?"))
  return;
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 var div = document.getElementById('attachment-'+id);
	 div.parentNode.removeChild(div);
	 // update attachments counter
	 /*var nc = document.getElementById('attachments-count');
	 nc.innerHTML = parseFloat(nc.innerHTML)-1;*/
	}
 sh.sendCommand("dynattachments delete -id "+id+" -r");
}

function saveAttachment(id)
{
 var sh = new GShell();
 var nm = htmlentities(document.getElementById('edatt_'+id+'_name').value,"ENT_QUOT");
 var ty = document.getElementById('edatt_'+id+'_type').value;
 var kw = htmlentities(document.getElementById('edatt_'+id+'_keywords').value,"ENT_QUOT");
 var pu = document.getElementById('edatt_'+id+'_published').checked;
 var de = htmlentities(document.getElementById('edatt_'+id+'_desc').value,"ENT_QUOT");
 var url = document.getElementById('edatt_'+id+'_url');
 if(url)
  url = url.value; 

 sh.OnOutput = function(o,a){
	 attachmentsFormClose();
	 var div = document.getElementById('attachment-'+id);
	 var title = div.getElementsByTagName('A')[3];
	 title.innerHTML = nm;
	 if(url)
	 {
	  title.href = url;
	  div.getElementsByTagName('A')[2].href = url;
	 }
	}
 sh.sendCommand("dynattachments edit -id "+id+" -name '"+nm+"' -type '"+ty+"' -keyw '"+kw+"' -desc '"+de+"'"+(pu ? " -publish" : " -unpublish")+(url ? " -url '"+url+"'" : ""));
}

function attachmentsFormClose()
{
 if(activeAttachmentsForm)
 {
  document.body.removeChild(activeAttachmentsForm);
  _hideScreenMask();
 }
}

function selectFromServer(userpath)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a)
	  return;
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){
		 if(!a)
		  return;
		 var div = document.createElement('DIV');
		 div.className = "attachment";
		 div.id = "attachment-"+a['id'];
		 var ih = "<a href='#' class='btnedit' onclick='editAttachment("+a['id']+")' title='Modifica'><img src='"+ABSOLUTE_URL+"GCommercialDocs/img/edit_small.png' border='0'/ ></a> <a href='#' class='btndel' onclick='deleteAttachment("+a['id']+")' title='Rimuovi'><img src='"+ABSOLUTE_URL+"GCommercialDocs/img/delete_small.png' border='0'/ ></a><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank'>";
		 if(a['icons'])
		 {
		  if(a['icons']['size48x48'])
		   ih+= "<img src='"+ABSOLUTE_URL+a['icons']['size48x48']+"' border='0' title=\""+a['name']+"\"/ >";
		 }
		 else
		  ih+= "<img src='"+ABSOLUTE_URL+"share/mimetypes/48x48/file.png' border='0' title=\""+a['name']+"\"/ >";
		 ih+= "</a><br/ ><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank' title=\""+a['name']+"\">"+a['name']+"</a>";
		 div.innerHTML = ih;
		 document.getElementById('attachments-explore').appendChild(div);
		 // update attachments counter
		 /*var nc = document.getElementById('attachments-count');
		 nc.innerHTML = parseFloat(nc.innerHTML)+1;*/
		}
	 sh2.sendCommand("dynattachments add -ap 'commercialdocs' -refid <?php echo $docInfo['id']; ?> -name '"+a['name']+"' -url '"+userpath+a['url']+"'");
	}
 sh.sendCommand("gframe -f filemanager --fullspace");
}

function insertFromURL()
{
 var url = prompt("Inserisci un indirizzo valido");
 if(!url) return;
 url = "http://"+url.replace('http://','');

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var div = document.createElement('DIV');
	 div.className = "attachment";
	 div.id = "attachment-"+a['id'];
	 var ih = "<a href='#' class='btnedit' onclick='editAttachment("+a['id']+")' title='Modifica'><img src='"+ABSOLUTE_URL+"GCommercialDocs/img/edit_small.png' border='0'/ ></a> <a href='#' class='btndel' onclick='deleteAttachment("+a['id']+")' title='Rimuovi'><img src='"+ABSOLUTE_URL+"GCommercialDocs/img/delete_small.png' border='0'/ ></a><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank'>";
	 if(a['icons'])
	 {
	  if(a['icons']['size48x48'])
	   ih+= "<img src='"+ABSOLUTE_URL+a['icons']['size48x48']+"' border='0' title=\""+a['name']+"\"/ >";
	 }
	 else
	  ih+= "<img src='"+ABSOLUTE_URL+"share/mimetypes/48x48/file.png' border='0' title=\""+a['name']+"\"/ >";
	 ih+= "</a><br/ ><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank' title=\""+a['name']+"\">"+a['name']+"</a>";
	 div.innerHTML = ih;
	 document.getElementById('attachments-explore').appendChild(div);
	 // update attachments counter
	 /*var nc = document.getElementById('attachments-count');
	 nc.innerHTML = parseFloat(nc.innerHTML)+1;*/
	}
 sh.sendCommand("dynattachments add -ap 'commercialdocs' -refid `<?php echo $docInfo['id']; ?>` -name '"+url.replace('http://','')+"' -url '"+url+"'");
}

/* PREDEFINED MESSAGES */
function addNewPredefMsg()
{
 document.getElementById('predefmsg-container').style.height = "160px";
 document.getElementById('predefmsg-addmsg').style.display = "none";
 document.getElementById('predefmsg-newmsg').style.display = "";
 
 document.getElementById('predefmsg-edit').focus();
}

function predefmsgnew_abort()
{
 document.getElementById('predefmsg-newmsg').style.display = "none";
 document.getElementById('predefmsg-addmsg').style.display = "";
 document.getElementById('predefmsg-container').style.height = "230px";
 ed.value = "";
}

function predefmsgnew_submit()
{
 var ed = document.getElementById("predefmsg-edit");
 if(!ed.value)
  return predefmsgnew_abort();

 var tbpm = document.getElementById("predefmsg-list");

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var r = tbpm.insertRow(-1);
	 r.id = a['id'];
	 r.insertCell(-1).innerHTML = ed.value;
	 r.cells[0].onclick = function(){insertPredefMsg(this);}
	 r.insertCell(-1).innerHTML = "<img src='<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/delete.png' onclick='deletePredefMsg(this)' style='cursor:pointer'/"+">";
	 r.cells[1].style.width="16px";
	 ed.value = "";	 
	}
 sh.sendCommand("commercialdocs new-predefmsg -text `"+ed.value+"`");


 document.getElementById('predefmsg-newmsg').style.display = "none";
 document.getElementById('predefmsg-addmsg').style.display = "";
 document.getElementById('predefmsg-container').style.height = "230px";

 document.getElementById("predefmsg-container").scrollTop = document.getElementById("predefmsg-container").scrollHeight;
}

function deletePredefMsg(a)
{
 if(!confirm("Sei sicuro di voler eliminare questo messaggio predefinito?"))
  return;

 var tbpm = document.getElementById("predefmsg-list");
 var r = a.parentNode.parentNode;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(){tbpm.deleteRow(r.rowIndex);}
 sh.sendCommand("commercialdocs delete-predefmsg -id '"+r.id+"'");
}

function insertPredefMsg(td)
{
 var text = td.innerHTML;
 
 var r = tb.AddRow();
 r.setAttribute('type','note');
 var colSpan = tb.O.rows[0].cells.length-1;
 while(r.cells.length > 2)
 {
  r.deleteCell(2);
 }
 r.cells[1].colSpan=colSpan;
 r.cells[1].setValue(text);
 document.getElementById("predefmsg").style.display = "none";
}

function showColumnsCloud(img)
{
 var pos = _getObjectPosition(img);
 var div = document.getElementById('showcolumnscloud');
 div.style.visibility = "hidden";
 div.style.display = "";

 div.style.left = pos['x']-32;
 div.style.top = pos['y']-div.offsetHeight;

 div.style.visibility = "visible";
}

function showTotalsCloud(img)
{
 var pos = _getObjectPosition(img);
 var div = document.getElementById('showtotalscloud');
 div.style.visibility = "hidden";
 div.style.display = "";

 div.style.left = pos['x']-32;
 div.style.top = pos['y']-div.offsetHeight;

 div.style.visibility = "visible";
}

function showUnconditionalDiscountCloud(img)
{
 // clear all values //
 //document.getElementById('uncondiscperc').value = "";
 //document.getElementById('uncondiscimp').value = "";


 var pos = _getObjectPosition(img);
 var div = document.getElementById('unconditionaldiscountcloud');
 div.style.visibility = "hidden";
 div.style.display = "";

 div.style.left = pos['x']-12;
 div.style.top = pos['y']-div.offsetHeight;

 div.style.visibility = "visible";
}

function uncondiscChangePerc(ed)
{
 //if(!confirm("Applicare lo sconto del "+ed.value+"% ?"))
 // return;

 var amount = parseCurrency(document.getElementById("doctot-amount").innerHTML.substr(10));
 var perc = parseFloat(ed.value);
 var discount = (amount && perc) ? (amount/100)*perc : 0;
 
 document.getElementById("doctot-uncondisc").innerHTML = "<em>&euro;</em>"+formatCurrency(discount);

 updateTotals();

 UNCONDISC_PERC = perc;
 UNCONDISC_AMOUNT = 0;

 hideAllClouds();
}

function uncondiscChangeImp(ed)
{
 //if(!confirm("Applicare uno sconto di "+ed.value+"€ sull'imponibile?"))
 // return;

 var discount = parseFloat(ed.value);
 document.getElementById("doctot-uncondisc").innerHTML = "<em>&euro;</em>"+formatCurrency(discount);
 updateTotals();

 UNCONDISC_PERC = 0;
 UNCONDISC_AMOUNT = discount;

 hideAllClouds();
}


function showTotCol(colName,cb)
{
 document.getElementById("doctot-"+colName+"-th").style.display = cb.checked ? "" : "none";
 document.getElementById("doctot-"+colName).style.display = cb.checked ? "" : "none";

 var sh = new GShell();
 sh.sendCommand("export GCD-DOCTOTCOL-"+colName.toUpperCase()+"="+(cb.checked ? "ON" : "OFF"));
}

function preemptiveTypeChange(sel)
{
 DOC_TAG=sel.value;

 document.getElementById('charterdates').style.display = "none";

 switch(sel.value)
 {
  case "CHARTER" : document.getElementById('charterdates').style.display = ""; break;
 }
}

function deleteSelectedExpenses()
{
 var list = expTB.GetSelectedRows();
 if(!list.length)
  return alert("Nessuna riga è stata selezionata");

 for(var c=0; c < list.length; c++)
  list[c].remove();

 updateTotals();
}

function addNewExpenses()
{
 if(expTB.O.rows.length > 3)
  return alert("Si possono inserire al massimo 3 voci di spesa");
 expTB.AddRow().edit();
}
</script>

</body></html>
<?php

