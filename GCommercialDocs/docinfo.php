<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-06-2017
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Document Info
 #VERSION: 2.97beta
 #CHANGELOG: 05-06-2017 : Rimosso window.opener.document.location.href.indexOf da funzione deleteDoc.
			 22-05-2017 : Strict agent bug-fix.
			 04-05-2017 : Bug fix html_entity_decode sost. con htmlspecialchars su Agente di Rif.
			 23-04-2017 : Bug fix su funzione showService.
			 26-02-2017 : Bugfix rit.acc solo x clienti con p.iva.
			 20-11-2016 : Aggiornata funzione updateRowFromData.
			 02-10-2016 : Aggiunto DDT di reso tra le causali predefinite sui DDT. (da abilitare su etc/commercialdocs/config.php)
			 18-09-2016 : Aggiunto riquadro aggiuntivo subjinfoxl.
			 24-05-2016 : Aggiunto checkbox x cassa prev.
			 03-05-2016 : Integrato SplitPayment ed aliquota IVA predefinita per cliente/fornitore.
			 10-04-2016 : Aggiunta funzione ExportToExcel.
			 08-04-2016 : Bug fix funzione showProduct
			 31-03-2016 : Aggiornata funzione impegno articoli su commessa.
			 08-03-2016 : Arrotondato i totali ed integrata funzione per evasioni parziali.
			 07-03-2016 : Aggiornata funzione insertElementFromData
			 03-03-2016 : Aggiunto colonne cod.iva e descriz. iva, bug fix riga di nota con editor.
			 25-02-2016 : Bug fix chiusura.
			 12-02-2016 : Aggiunto numero di tracking.
			 06-02-2016 : Aggiunta unita di misura predefinita.
			 03-02-2016 : Aggiornamenti vari.
			 02-02-2016 : Bug fix sui bolli.
			 26-01-2016 : Bug fix scadenze.
			 24-01-2016 : Bug fix su errori in shell.
			 16-01-2016 : Bug fix inserimento fornitore su articolo gia esistente.
			 02-10-2015 : Integrato costi d'incasso su modalità pagamento.
			 25-09-2015 : Modificata funzione printPreview.
			 02-05-2015 : Aggiunta colonna totale costo acquisto.
			 01-05-2015 : Bug fix sugli arrotondamenti.
			 03-04-2015 : Bug fix su funzione saveColumnsSettings e sulle scadenze.
			 30-03-2015 : Bug fix deleteDoc.
			 18-03-2015 : Righe di nota editabili con htmleditor.
			 13-03-2015 : Integrato con fatture PA
			 11-03-2015 : Bug fix - aggiornamento prezzi acquisto.
			 23-02-2015 : Aggiunto raggruppamento DDT acquisto quando si seleziona il fornitore.
			 18-02-2015 : Aggiunto data consegna.
			 04-02-2015 : Aggiunto computo metrico.
			 26-01-2015 : Aggiunto location.
			 20-12-2014 : Aggiunto le lavorazioni.
			 18-12-2014 : Aggiunto sistema di personalizzazione campo descrizione art.
			 09-12-2014 : Aggiunto ticket tra i doc. di rif. interno.
			 06-12-2014 : Bug fix sulla ritenuta d'acconto.
			 10-11-2014 : Bug fix pesi articoli.
			 29-10-2014 : Aggiunta colonna cod. art. produttore.
			 15-10-2014 : Bug fix su %ricarico. Messo PR.BASE come editabile.
			 08-10-2014 : Aggiunto contributi e ritenute anche su preventivi,ordini,rapportini e ricevute.
			 07-10-2014 : Aggiunta possibilità di includere gli articoli del documento di riferimento.
			 05-10-2014 : Aggiunto DDT fornitore tra i doc. di rif. e possibilità di modificare fornitore agli articoli.
			 02-10-2014 : Integrato con Fatture Soci
			 03-09-2014 : Ora mostra i la tab contributi per fatture agente,note di credito e di debito.
			 27-08-2014 : restricted access integration.
			 21-08-2014 : Integrato con i libri.
			 02-08-2014 : Integrato con prodotti finiti, componenti e materiali.
			 14-07-2014 : Integrato impegno articoli su ordine,commessa,ecc.. e bug fix sulle scadenze.
			 03-07-2014 : Integrato causali predefinite per cliente.
			 27-06-2014 : Bug fix su plmrate. - non calcolava lo sconto.
			 23-06-2014 : Bug fix in function BrowseCatalog.
			 26-05-2014 : Note stampabili e non stampabili.
			 23-05-2014 : Aggiunto DDT Fornitore
			 19-05-2014 : Possibilita di modificare prezzo totale e totale iva inclusa con scorporo automatico.
			 08-04-2014 : Bug fix su nuovi prodotti inseriti
			 01-03-2014 : Aggiunto lista ultimi prodotti.
			 28-02-2014 : Bug fix su _paymentModeSelectChange
			 26-02-2014 : bug fix vari.
			 19-02-2014 : Bug fix sugli sconti.
			 14-02-2014 : Aggiunto titoli che mostra valori reali sulle celle.
			 10-02-2014 : Risolto bug su arrotondamenti.
			 07-02-2014 : Aggiunto variabile LOCKAUTOSAVECUSTOMPRICING che inibisce il salvataggio dei prezzi imposti.
			 04-02-2014 : Bug fix su conversioni e scadenze.

 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DESKTOP_SHOW_TOOLBAR, $_DESKTOP_TITLE, $_COMPANY_PROFILE, $_PRICELISTS, $_DEPOSITS, $_SCHEDULE, $_SHOW_SHIPPINGPAGE, $_SHOW_PAYMENTSPAGE, $_SHOW_CONTRIBANDDEDUCTS, $_COLUMNS, $_COMMERCIALDOCS_CONFIG;

$_BASE_PATH = "../";

define("VALID-GNUJIKO",1);

include($_BASE_PATH.'init/init1.php');
include($_BASE_PATH.'include/session.php');
include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/company-profile.php");
include_once($_BASE_PATH."include/countries.php");
include_once($_BASE_PATH."etc/commercialdocs/config.php");

if(!loginRequired())
 exit;

if(!$_REQUEST['strictagent'] && !restrictedAccess("commercialdocs"))
 exit();

$_CAT_ID = 0;
$_ROOT_CAT_ID = 0;
$_LOCKED = false;
$_ISVENDOR = false;
$_SUBJECT_VATNUMBER = "";
$_SUBJECT_TAXCODE = "";
$_SUBJECT_TYPE = 0;
$_BANKS = $_COMPANY_PROFILE['banks'];
$_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'];
$ret = GShell("pricelists list");
$_PRICELISTS = $ret['outarr'];
$_PRICELIST_BY_ID = array();
for($c=0; $c < count($_PRICELISTS); $c++)
 $_PRICELIST_BY_ID[$_PRICELISTS[$c]['id']] = $_PRICELISTS[$c];

$_AGENT_ID = (isset($_REQUEST['agentid']) && $_REQUEST['agentid']) ? $_REQUEST['agentid'] : 0;

/* GET LIST OF VAT RATES */
$ret = GShell("dynarc item-list -ap vatrates -get `percentage,vat_type`");
$_VAT_LIST = $ret['outarr']['items'];
$_VAT_BY_ID = array();
for($c=0; $c < count($_VAT_LIST); $c++)
 $_VAT_BY_ID[$_VAT_LIST[$c]['id']] = $_VAT_LIST[$c];


/* Get if schedule extension is installed */
$db = new AlpaDatabase();
$db->RunQuery("SELECT id FROM dynarc_archives WHERE tb_prefix='commercialdocs'");
$db->Read();
$db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$db->record['id']."' AND extension_name='schedule'");
if($db->Read())
 $scheduleExtensionInstalled = true;
else
 $scheduleExtensionInstalled = false;
$db->Close();

$ret = GShell("dynarc item-info -ap `commercialdocs` -id `".$_REQUEST['id']."` -extget `cdinfo,cdelements,mmr".($scheduleExtensionInstalled ? ",schedule" : "")."`");
$docInfo = $ret['outarr'];

$_CAT_ID = $docInfo['cat_id'];
$_ROOT_CAT_ID = $_CAT_ID;
if(!$_AGENT_ID && $docInfo['agent_id']) $_AGENT_ID=$docInfo['agent_id'];
$_STRICT_AGENT_ID = $_AGENT_ID;

$_CPA = $_COMPANY_PROFILE['accounting'];

$_CPA['rivalsa_inps'] = $docInfo['rivalsa_inps'];
$_CPA['contr_cassa_prev'] = $docInfo['contr_cassa_prev'];
$_CPA['contr_cassa_prev_vatid'] = $docInfo['contr_cassa_prev_vatid'];
$_CPA['rit_enasarco'] = $docInfo['rit_enasarco'];
$_CPA['rit_enasarco_percimp'] = $docInfo['rit_enasarco_percimp'];
$_CPA['rit_acconto'] = $docInfo['rit_acconto'];
$_CPA['rit_acconto_percimp'] = $docInfo['rit_acconto_percimp'];
$_CPA['rit_acconto_rivinpsinc'] = $docInfo['rit_acconto_rivinpsinc'];

$_DEF_VAT = 0;
$_DEF_VAT_ID = $_CPA['freq_vat_used'];
$_DEF_VAT_TYPE = "";
$_DEF_VAT_CODE = "";
$_DEF_VAT_NAME = "";
$_CASSA_PREV_PERC = $_CPA['contr_cassa_prev'];
$_CASSA_PREV_VATID = $_CPA['contr_cassa_prev_vatid'];
$_CASSA_PREV_VAT = 0;
$_CASSA_PREV_VAT_TYPE = "";

for($c=0; $c < count($_VAT_LIST); $c++)
{
 if($_VAT_LIST[$c]['id'] == $_DEF_VAT_ID)
 {
  $_DEF_VAT = $_VAT_LIST[$c]['percentage'];
  $_DEF_VAT_TYPE = $_VAT_LIST[$c]['vat_type'];
  $_DEF_VAT_CODE = $_VAT_LIST[$c]['code_str'];
  $_DEF_VAT_NAME = $_VAT_LIST[$c]['name'];
 }
 if($_VAT_LIST[$c]['id'] == $_CASSA_PREV_VATID)
 {
  $_CASSA_PREV_VAT = $_VAT_LIST[$c]['percentage'];
  $_CASSA_PREV_VAT_TYPE = $_VAT_LIST[$c]['vat_type'];
 }
}

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
   $_ROOT_CAT_ID = $db->record['parent_id'];
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
   case 'preemptives' : {
	 $_DOC_TYPENAME = "Preventivo"; 
	 $_SHOW_CONTRIBANDDEDUCTS = true;
	} break;

   case 'orders' : {
	 $_DOC_TYPENAME = "Ordine"; 
	 $_SHOW_SHIPPINGPAGE = true;
	 $_SHOW_PAYMENTSPAGE = true;
	} break;

   case 'ddt' : {
	 $_DOC_TYPENAME = "D.D.T.";
	 $_SHOW_SHIPPINGPAGE = true;
	 $_SHOW_PAYMENTSPAGE = true;
	} break;

   case 'ddtin' : {
	 $_DOC_TYPENAME = "D.D.T. Fornitore";
	 $_SHOW_SHIPPINGPAGE = true;
	 $_SHOW_PAYMENTSPAGE = true;
	 $_ISVENDOR = true;
	} break;

   case 'invoices' : {
	 $_DOC_TYPENAME = "Fattura";
	 $_SHOW_SHIPPINGPAGE = true;
	 $_SHOW_PAYMENTSPAGE = true;
	 $_SHOW_CONTRIBANDDEDUCTS = true;
	} break;

   case 'purchaseinvoices' : {
	 $_DOC_TYPENAME = "Fattura d&lsquo;acquisto";
	 $_SHOW_PAYMENTSPAGE = true;
	 $_ISVENDOR = true;
	 $_SHOW_CONTRIBANDDEDUCTS = true;
	 $_SHOW_SHIPPINGPAGE = true;
	} break;

   case 'vendororders' : {
	 $_DOC_TYPENAME = "Ordine fornitore"; 
	 $_SHOW_PAYMENTSPAGE = true;
	 $_SHOW_SHIPPINGPAGE = true;
	 $_ISVENDOR = true;
	} break;

   case 'agentinvoices' : {
	 $_DOC_TYPENAME = "Fattura agente";
	 $_SHOW_PAYMENTSPAGE = true;
	 $_SHOW_CONTRIBANDDEDUCTS = true;
	} break;

   case 'memberinvoices' : {
	 $_DOC_TYPENAME = "Fattura socio";
	 $_SHOW_PAYMENTSPAGE = true;
	 $_SHOW_CONTRIBANDDEDUCTS = true;
	} break;

   case 'intervreports' : {
	 $_DOC_TYPENAME = "Rapporto d&lsquo;intervento"; 
	 $_SHOW_CONTRIBANDDEDUCTS = true;
	} break;

   case 'creditsnote' : {
	 $_DOC_TYPENAME = "Nota di accredito";
	 $_SHOW_PAYMENTSPAGE = true;
	 $_SHOW_CONTRIBANDDEDUCTS = true;
	} break;

   case 'debitsnote' : {
	 $_DOC_TYPENAME = "Nota di debito";
	 $_SHOW_PAYMENTSPAGE = true;
	 $_SHOW_CONTRIBANDDEDUCTS = true;
	} break;

   case 'paymentnotice' : {
	 $_DOC_TYPENAME = "Avviso di pagamento";
	 $_SHOW_PAYMENTSPAGE = true;
	 $_SHOW_CONTRIBANDDEDUCTS = true;
	} break;

   case 'receipts' : {
	 $_DOC_TYPENAME = "Ricevuta Fiscale"; 
	 $_SHOW_CONTRIBANDDEDUCTS = true;
	} break;

   default : $_DOC_TYPENAME = "Documento generico"; break;
  }

}

$_SUBJECT_VAT_ID = 0;
$_SUBJECT_VAT_RATE = 0;
$_SUBJECT_VAT_TYPE = "";
$_SUBJECT_VAT_CODE = "";
$_SUBJECT_VAT_NAME = "";

/* DETECT SUBJECT INFO */
if($docInfo['subject_id'])
{
 $ret = GShell("dynarc item-info -ap `rubrica` -id `".$docInfo['subject_id']."` -extget `contacts,banks,references` -get `distance,vatnumber,iscompany,vat_id`");
 if(!$ret['error'])
 {
  $subjectInfo = $ret['outarr'];
  $_SUBJECT_VATNUMBER = $subjectInfo['vatnumber'];
  $_SUBJECT_TAXCODE = $subjectInfo['taxcode'];
  $_SUBJECT_TYPE = $subjectInfo['iscompany'];

  if($subjectInfo['vat_id'])
  {
   $subjectInfo['vat_info'] = $_VAT_BY_ID[$subjectInfo['vat_id']];
   if($subjectInfo['vat_info'])
   {
	$_SUBJECT_VAT_ID = $subjectInfo['vat_id'];
	$_SUBJECT_VAT_RATE = $subjectInfo['vat_info']['percentage'];
	$_SUBJECT_VAT_TYPE = $subjectInfo['vat_info']['vat_type'];
	$_SUBJECT_VAT_CODE = $subjectInfo['vat_info']['code_str'];
	$_SUBJECT_VAT_NAME = $subjectInfo['vat_info']['name'];
   }
  }

 }
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


/* GET CONFIG - INTERFACE */
$config = array();
$_CFG_DOCSECTIONS = array();
$_CFG_COLUMNS = array();
$_CFG_DOCINFO = array();
$_CFG_OPTIONS = array();

$ret = GShell("aboutconfig get-config -app gcommercialdocs -sec interface");
if(!$ret['error'])
{
 $config['interface'] = $ret['outarr']['config'];
 if(is_array($config['interface']['docsections']) && $config['interface']['docsections'][strtolower($_CAT_TAG)])
  $_CFG_DOCSECTIONS = $config['interface']['docsections'][strtolower($_CAT_TAG)];
 if(is_array($config['interface']['docinfo']))
  $_CFG_DOCINFO = $config['interface']['docinfo'];
 if(is_array($config['interface']['options']))
  $_CFG_OPTIONS = $config['interface']['options'];
}

/* GET CONFIG - COLUMN SETTINGS */
$ret = GShell("aboutconfig get-config -app gcommercialdocs -sec columns");
if(!$ret['error'])
{
 $config['columns'] = $ret['outarr']['config'];
 if(is_array($config['columns']) && $config['columns'][strtolower($_CAT_TAG)])
  $_CFG_COLUMNS = $config['columns'][strtolower($_CAT_TAG)];
}

$_INSART_SCHEMA = "{BRAND} {MODEL}";
$_INSART_KEYS = array('{CODE}','{BRAND}','{MODEL}','{BARCODE}','{MANCODE}','{VENCODE}',
	'{LOCATION}','{DIVISION}','{GEBINDECODE}','{GEBINDE}','{UNITS}','{WEIGHT}');

if($_CFG_DOCINFO["insart_".strtolower($_CAT_TAG)."_schema"])
 $_INSART_SCHEMA = $_CFG_DOCINFO["insart_".strtolower($_CAT_TAG)."_schema"];

/* GET CONFIG - OTHER */
$config['other'] = array();
$ret = GShell("aboutconfig get-config -app gcommercialdocs -sec other");
if(!$ret['error'])
{
 $config['other'] = $ret['outarr']['config'];
}


if($docInfo['tag'] == "DDR")
 $_ISVENDOR = true;

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

$extraDocTypes = array();
if($_COMMERCIALDOCS_CONFIG['DOCTYPE'] && $_COMMERCIALDOCS_CONFIG['DOCTYPE'][$_CAT_TAG])
 $extraDocTypes = $_COMMERCIALDOCS_CONFIG['DOCTYPE'][$_CAT_TAG];

?>
<body onload="bodyOnLoad()">
<?php
basicapp_header_begin();
?>
<table width='100%' border='0' cellspacing="0" cellpadding="0" id="header-bar">
<tr><td style="padding-left:10px;">
	 <ul class='tiptop' id="doctypemenu">
	  <li><span><?php echo $_DOC_TYPENAME; ?></span></li>
	  <?php
	  $ret = GShell("dynarc item-list -ap 'gcdcausal' -ct '".$_CAT_TAG."'");
	  if(!$ret['error'] && count($ret['outarr']['items']))
	  {
	   $causals = $ret['outarr']['items'];
	   $causalName = "";
	   $causalID = 0;
	   for($c=0; $c < count($causals); $c++)
	   {
		if($docInfo['tag'] == $causals[$c]['id'])
		{
		 $causalName = $causals[$c]['name'];
		 $causalID = $causals[$c]['id'];
		 break;
		}
	   }
	   if(!$causalID && $extraDocTypes[$docInfo['tag']])
	   {
		$causalID = $docInfo['tag'];
		$causalName = $extraDocTypes[$docInfo['tag']];
	   }
	   if(!$causalID)
	   {
		$causalID = $causals[0]['id'];
		$causalName = $causals[0]['name'];
	   }
	   echo "<li id='doctagname' refid='".$causalID."' style='cursor:pointer;' title='Clicca per modificare la causale del documento'><span style='font-size:14px;'>".$causalName."<img src='".$_ABSOLUTE_URL."GCommercialDocs/img/tiptop-dnarr.png' class='ddmenu'/></span>";
	   echo "<ul class='submenu' id='doctagsubmenu'>";
	   for($c=0; $c < count($causals); $c++)
	    echo "<li refid='".$causals[$c]['id']."' onclick='editDocTag(this)'>".$causals[$c]['name']."</li>";
	   if(count($extraDocTypes))
	   {
		// include extra doctype
		echo "<li class='separator'>&nbsp;</li>";
		reset($extraDocTypes);
		while(list($k,$v)=each($extraDocTypes))
		 echo "<li refid='".$k."' onclick='editDocTag(this)'>".$v."</li>";
	   }
	   echo "</ul>";
	   echo "</li>";
	  }

	  ?>
	  <li onclick='editDocNum()' style='cursor:pointer;' title="Clicca per modificare il numero di documento"><span>N. <b id='docnum'><?php echo $docInfo['code_num'].($docInfo['code_ext'] ? "/".$docInfo['code_ext'] : ""); ?></b></span></li>
	  <li onclick='editDocDate()' style='cursor:pointer;' title="Clicca per modificare la data del documento"><span><small style='font-size:14px;'>del</small> <b id='docdate'><?php echo date('d/m/Y',$docInfo['ctime']); ?></b></span></li>
	 </ul>
	</td>

	<?php
	if(($_CAT_TAG == "ORDERS") && $scheduleExtensionInstalled)
	{
	 ?>
	 <td><small>Ripetibile:</small> <select id='freq' onchange='repeatChanged(this)'><?php
		 $options = array(0=>"no", 12=>"ogni anno", 6=>"ogni 6 mesi", "4"=>"ogni 4 mesi", "3"=>"ogni 3 mesi", "2"=>"ogni 2 mesi", "1"=>"ogni mese");
		 while(list($k,$v)=each($options))
		  echo "<option value='".$k."'".($k == $docInfo['freq'] ? " selected='selected'>" : ">").$v."</option>";
		?></select></td>
	 <td id='repeat-finishdate-container' <?php if(!$docInfo['freq']) echo "style='visibility:hidden'"; ?>><small>data termine: <input type='text' class='edit' id='finish_date' style='width:80px' placeholder='dd/mm/aaaa' title="Data termine ordine / contratto" value="<?php if($docInfo['finish_date'] != '0000-00-00') echo date('d/m/Y',strtotime($docInfo['finish_date'])); ?>"/></small></td>
	 <?php
	}
	?>

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
		 if($docInfo['conv_doc_id'] || $docInfo['group_doc_id'] || ($docInfo['status'] >= 7))
		 {
		  $_LOCKED = true;
		  echo "<li><a href='#' onclick='unlockDoc()'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/unlock.png' border='0'/>Sblocca</a></li>";
		 }
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
	if($_ISVENDOR) echo "FORNITORE";
	else if(strtolower($_CAT_TAG) == "agentinvoices") echo "AGENTE";
	else if(strtolower($_CAT_TAG) == "memberinvoices") echo "SOCIO";
	else echo "CLIENTE";
	?></span></h3>
<div class="minisecgray"><i>Spett.le</i> <span class="rightlink" onclick="subjectChange()"><i>Cambia</i></span></div>
<div id="subjectname" class="titlename" style="margin-bottom:10px;" onclick="subjectInfo()"><?php echo html_entity_decode($docInfo['subject_name'],ENT_QUOTES,'UTF-8'); ?></div>
<div id="rubricaedit-container" <?php if($docInfo['subject_name']) echo "style='display:none;'"; ?>><input type="text" size="14" id="rubricaedit" value="<?php echo htmlspecialchars($docInfo['subject_name'],ENT_QUOTES); ?>" refid="<?php echo $docInfo['subject_id']; ?>"/></div>

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
	 echo $docInfo['ship_recp']."<br/>".$docInfo['ship_addr']."<br/>".$docInfo['ship_city']." (".$docInfo['ship_prov'].")";
	else
	 echo $subjectInfo['contacts'][0]['address']."<br/>".$subjectInfo['contacts'][0]['city']." (".$subjectInfo['contacts'][0]['province'].")";
	?>
	</div>
<span class="link" onclick="editShipAddr()"><i>Modifica</i></span><br/>

<br/>
<br/>
</div>

<div <?php if(!$_CFG_DOCSECTIONS['agent']) echo "style='display:none'"; ?>>
<h3 class='rightsec-blue' style='margin-bottom:2px'><span class='title'>AGENTE DI RIF.</span></h3>
<input type='text' class='edit' id='agent' style='width:156px' value="<?php echo htmlspecialchars($docInfo['agent_name'],ENT_QUOTES); ?>"/>
<br/><br/>
</div>

<?php
if(($_CAT_TAG == "VENDORORDERS") || ($_CAT_TAG == "PURCHASEINVOICES") || ($_CAT_TAG == "DDTIN"))
{
 $extDocRefPlaceholder = "Es: Fattura n. XXX del gg/mm/aaaa";
 ?>
 <div>
 <h3 class='rightsec-blue' style='margin-bottom:2px'><span class='title'>RIF. DOC. FORNIT.</span></h3>
 <input type='text' class='edit' id='extdocref' style='width:156px' value="<?php echo ($docInfo['ext_docref'] != $extDocRefPlaceholder) ? $docInfo['ext_docref'] : ''; ?>" placeholder="<?php echo $extDocRefPlaceholder; ?>"/>
 <br/><br/>
 </div>
 <?php
}
?>

<div <?php if(!$_CFG_DOCSECTIONS['reference']) echo "style='display:none'"; ?>>
<h3 class='rightsec-blue' style='margin-bottom:2px'><span class='title'>CONTATTO DI RIF.</span></h3>
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

<div <?php if(!$_CFG_DOCSECTIONS['intdocref']) echo "style='display:none'"; ?>>
<h3 class='rightsec-blue' style='margin-bottom:2px'><span class='title'>DOC. DI RIF. INTERNO</span></h3>
 <div class="internaldocref" id="internaldocrefmenu"><span><?php
	if($docInfo['docref_ap'] && $docInfo['docref_id'])
	{
     switch($docInfo['docref_ap'])
	 {
	  case 'commercialdocs' : {
		 switch(strtolower($docInfo['docref_ct']))
	 	 {
		  case 'preemptives' : echo "Preventivo"; break;
		  case 'orders' : echo "Ordine"; break;
		  case 'intervreports' : echo "Rapp. d&lsquo;intervento"; break;
		  case 'ddtin' : echo "DDT Fornitore"; break;
		 }
		} break;

	  case 'commesse' : echo "Commessa"; break;

	  case 'tickets' : echo "Ticket"; break;

	  default : echo "seleziona il tipo di documento"; break;
	 }
	}
	else
	 echo "seleziona il tipo di documento";
	?></span> <img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/blue-dnarr.png"/>
  <ul class="submenu" id="internaldocrefmenu-list">
   <li ap='commercialdocs' ct='preemptives' onclick='setInternalDocRef(this)'>Preventivo</li>
   <li ap='commercialdocs' ct='orders' onclick='setInternalDocRef(this)'>Ordine</li>
   <li ap='commercialdocs' ct='intervreports' onclick='setInternalDocRef(this)'>Rapp. d&lsquo;intervento</li>
   <li ap='commercialdocs' ct='ddtin' onclick='setInternalDocRef(this)'>DDT Fornitore</li>
   <?php
   if(file_exists($_BASE_PATH."Commesse/index.php"))
	echo "<li ap='commesse' onclick='setInternalDocRef(this)'>Commessa</li>";
   if(file_exists($_BASE_PATH."Tickets/index.php"))
	echo "<li ap='tickets' onclick='setInternalDocRef(this)'>Ticket</li>";
   ?>
   <li class="separator">&nbsp;</li>
   <li onclick='setInternalDocRef(this)'>Altro...</li>
  </ul>
 </div>   
<input type='text' class='edit' id='internaldocref' style="width:156px;<?php if(!$docInfo['docref_id']) echo 'display:none'; ?>" refap="<?php echo $docInfo['docref_ap']; ?>" refid="<?php echo $docInfo['docref_id']; ?>" value="<?php echo $docInfo['docref_name']; ?>"/>
<input type='text' class='edit' id='aliasname' style="width:156px;<?php if($docInfo['docref_ap'] && $docInfo['docref_id']) echo 'display:none'; ?>" value="<?php echo $docInfo['aliasname']; ?>" onchange="DOCISCHANGED=true;"/>
<br/><br/>
</div>
<?php

 /* DOCUMENT DIVISION */
 if(isset($_COMMERCIALDOCS_CONFIG['DIVISION']))
 {
  reset($_COMMERCIALDOCS_CONFIG['DIVISION']);
  ?>
 <div <?php if(!$_CFG_DOCSECTIONS['divmat']) echo "style='display:none'"; ?>>
 <h3 class='rightsec-blue' style='margin-bottom:2px'><span class='title'>DIVISIONE MATERIALE</span></h3>
 <select id='documentdivision' style='width:156px' onchange="documentDivisionChange(this)">
  <option value=''>&nbsp;</option>
  <?php
  while(list($k,$v) = each($_COMMERCIALDOCS_CONFIG['DIVISION']))
  {
   echo "<option value='".$k."'".($k == $docInfo['division'] ? " selected='selected'>" : ">").$v."</option>";
  }
  ?>
 </select><br/><br/>
 </div>
 <?php 
 }

/* LOCATION */
?>
<div <?php if(!$_CFG_DOCSECTIONS['location']) echo "style='display:none'"; ?>>
<h3 class='rightsec-blue' style='margin-bottom:2px'><span class='title'>LOCATION</span></h3>
<input type='text' class='edit' style='width:156px' id='location' value="<?php echo $docInfo['location']; ?>"/>
<br/><br/>
</div>
<?php

/* DELIVERY */
?>
<div <?php if(!$_CFG_DOCSECTIONS['delivery']) echo "style='display:none'"; ?>>
<h3 class='rightsec-blue' style='margin-bottom:2px'><span class='title'>DATA CONS.</span></h3>
<input type='text' class='edit' style='width:156px' id='deliverydate' value="<?php echo ($docInfo['delivery_date'] != '0000-00-00') ? date('d/m/Y',strtotime($docInfo['delivery_date'])) : ''; ?>"/>
<br/><br/>
</div>
<?php


/* EXTRA STATUS */
if(isset($_COMMERCIALDOCS_CONFIG['STATUSEXTRA']) && $_COMMERCIALDOCS_CONFIG['STATUSEXTRA'][$_CAT_TAG])
{
 reset($_COMMERCIALDOCS_CONFIG['STATUSEXTRA'][$_CAT_TAG]);
 ?>
 <div>
 <h3 class='rightsec-blue' style='margin-bottom:2px'><span class='title'>STATUS</span></h3>
 <select id='statusextra' style='width:156px' onchange="statusExtraChange(this)">
  <option value=''>&nbsp;</option>
  <?php
  while(list($k,$v) = each($_COMMERCIALDOCS_CONFIG['STATUSEXTRA'][$_CAT_TAG]))
  {
   echo "<option value='".$k."'".($k == $docInfo['status_extra'] ? " selected='selected'>" : ">").$v['title']."</option>";
  }
  ?>
 </select><br/><br/>
 </div>
 <?php 
}

if($_CAT_TAG == "PREEMPTIVES")
{
 ?>
 <div id='charterdates'>
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

<div <?php if(!$_CFG_DOCSECTIONS['payments']) echo "style='display:none'"; ?>>
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

<div class="minisecgray" <?php if(!$_CFG_DOCSECTIONS['advances']) echo "style='display:none'"; ?>><i>Acconti</i></div>
<table width='100%' border='0' cellspacing='0' cellpadding='0' class='smalltable' style="margin-top:3px;<?php if(!$_CFG_DOCSECTIONS['advances']) echo 'display:none'; ?>">
<?php
for($c=0; $c < count($_DEPOSITS); $c++)
{
 switch(strtolower($_CAT_TAG))
 {
  case 'agentinvoices' : case 'memberinvoices' : case 'vendororders' : case 'purchaseinvoices' : case 'creditsnote' : case 'ddtin' : echo "<tr><td><span class='gray11'><b>".number_format($_DEPOSITS[$c]['expenses'],2,',','.')." &euro;</b></span>"; break;
  default : echo "<tr><td><span class='gray11'><b>".number_format($_DEPOSITS[$c]['incomes'],2,',','.')." &euro;</b></span>"; break;
 }
 echo "<td align='right'>".date('d/m/Y',strtotime($_DEPOSITS[$c]['payment_date']))."</td></tr>";
}
?>
</table>

<br/>

<div class="minisecgray" <?php if(!$_CFG_DOCSECTIONS['deadlines']) echo "style='display:none'"; ?>><i>Scadenze</i></span></div>
<table id="schedule-table-small" width='100%' border='0' cellspacing='0' cellpadding='0' class='smalltable' style="margin-top:3px;<?php if(!$_CFG_DOCSECTIONS['deadlines']) echo 'display:none'; ?>">
<?php
for($c=0; $c < count($_SCHEDULE); $c++)
{
 $tmp = false;
 if($_SCHEDULE[$c]['payment_date'] != "0000-00-00")
  $tmp = true;
 echo "<tr><td><span class='gray11'><b".($tmp ? " style='color:green'" : "").">".($c+1)."</b></span></td>";
 if($tmp)
  echo "<td align='center' title='Saldata il ".date('d/m/Y',strtotime($_SCHEDULE[$c]['payment_date']))."' style='color:green'>saldata</td>";
 else
  echo "<td align='center'>".date('d/m/Y',strtotime($_SCHEDULE[$c]['expire_date']))."</td>";
 switch($_CAT_TAG)
 {
  case 'PURCHASEINVOICES' : case 'DDTIN' : case 'VENDORORDERS' : echo "<td align='right'><span class='gray11'".($tmp ? " style='text-decoration:line-through;color:green'" : "")."><b>".number_format($_SCHEDULE[$c]['expenses'],2,',','.')." &euro;</b></span></td></tr>"; break;
  default : echo "<td align='right'><span class='gray11'".($tmp ? " style='text-decoration:line-through;color:green'" : "")."><b>".number_format($_SCHEDULE[$c]['incomes'],2,',','.')." &euro;</b></span></td></tr>"; break;
 }
 
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
 <li class='selected'><span class='title' id='hometabspan' onclick="showPage('home',this)">RIGHE DOCUMENTO</span></li>
 <li <?php if(!$_SHOW_SHIPPINGPAGE) echo "style='display:none;'"; ?>><span class='title' id='shipSpanPageBtn' onclick="showPage('shipping',this)">DESTINAZIONE MERCI</span></li>
 <li <?php if(!$_SHOW_PAYMENTSPAGE) echo "style='display:none;'"; ?>><span class='title' onclick="showPage('payments',this)">MODALITA&rsquo; DI PAGAMENTO</span></li>
 <li><span class='title' onclick="showPage('attachments',this)">ALLEGATI E NOTE</span></li>
 <?php
 if($_SHOW_CONTRIBANDDEDUCTS)
  echo "<li><span class='title' onclick=\"showPage('contribanddeduct',this)\">CONTRIBUTI E RITENUTE</span></li>";
 ?>
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
	 {
	  echo "<li onclick=\"InsertRow('article')\"><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/add-product.png' width='16'/>Aggiungi nuovo articolo</li>";
	  echo "<li onclick=\"BrowseCatalog()\"><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/browse-catalog.png' width='16'/>Cerca articolo in catalogo</li>";
	 }
	 if(_userInGroup("gserv") && file_exists($_BASE_PATH."Services/index.php"))
	  echo "<li onclick=\"InsertRow('service')\"><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/add-service.png' width='16'/>Aggiungi nuovo servizio</li>";
	 if(_userInGroup("gproducts") && file_exists($_BASE_PATH."FinalProducts/index.php"))
	  echo "<li onclick=\"InsertRow('finalproduct')\"><img src='".$_ABSOLUTE_URL."FinalProducts/icon.png' width='16'/>Aggiungi prodotto finito</li>";
	 if(_userInGroup("gpart") && file_exists($_BASE_PATH."Parts/index.php"))
	  echo "<li onclick=\"InsertRow('component')\"><img src='".$_ABSOLUTE_URL."Parts/icon.png' width='16'/>Aggiungi componente/semilavorato</li>";
	 if(_userInGroup("gmaterial") && file_exists($_BASE_PATH."Materials/index.php"))
	  echo "<li onclick=\"InsertRow('material')\"><img src='".$_ABSOLUTE_URL."Materials/icon.png' width='16'/>Aggiungi materiale</li>";
	 if(_userInGroup("glabor") && file_exists($_BASE_PATH."Labors/index.php"))
	  echo "<li onclick=\"InsertRow('labor')\"><img src='".$_ABSOLUTE_URL."Labors/icon.png' width='16'/>Aggiungi lavorazione</li>";
	 if(_userInGroup("gbook") && file_exists($_BASE_PATH."Books/index.php"))
	  echo "<li onclick=\"InsertRow('book')\"><img src='".$_ABSOLUTE_URL."Books/icon.png' width='16'/>Aggiungi libro</li>";

	 if(_userInGroup("gsupplies") && file_exists($_BASE_PATH."Supplies/index.php"))
	  echo "<li onclick=\"InsertRow('supply')\"><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/add-supply.png' width='16'/>Aggiungi altro tipo di fornitura</li>";

	 if(file_exists($_BASE_PATH."share/widgets/pictureframe/insertframe.php"))
	  echo "<li onclick=\"InsertPictureFrame()\"><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/add-pictureframe.png' width='16'/>Inserisci cornice</li>";


	 ?>
	 <li onclick="InsertRow('custom')"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/add-custom.png" width='16'/>Aggiungi riga personalizzata</li>
	 <li onclick="InsertRow('note')"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/add-note.png" width='16'/>Aggiungi riga di nota</li>
	 <li class="separator">&nbsp;</li>
	 <li onclick="Duplicate()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/copy.png" width='16'/>Duplica questo documento</li>
	 <?php
	 if(file_exists($_BASE_PATH."share/widgets/commercialdocs/saveasprecomp.php"))
	 {
	  ?>
	 <li onclick="SaveAsPrecompiled()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/revert.gif" width='16'/>Salva come documento precompilato</li>
	 <?php
	 }
	 ?>
	 <li onclick="ExportToExcel()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/excel.png" width='16'/>Esporta corpo su file Excel</li>
	 <li class="separator">&nbsp;</li>
	 <li onclick="saveDoc()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/save.gif" width='16'/>Salva</li>
	 <li onclick="printPreview()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/printer.gif" width='16'/>Stampa</li>
	 <li class="separator">&nbsp;</li>
	 <li onclick="deleteDoc()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/trash.gif" width='16'/>Elimina</li>
	</ul>
  </li>

  <li class='lightgray'><span>Modifica</span>
	<ul class='submenu'>
	 <!-- <li id='cutmenubtn' class='disabled' onclick="cut()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/cut.gif"/><?php echo i18n("cut"); ?></li> -->
	 <li id='copymenubtn' class='disabled' onclick="copy()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/copy.png"/><?php echo i18n("Copia"); ?></li>
	 <li id='pastemenubtn' class='disabled' onclick="paste()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/paste.gif"/><?php echo i18n("Incolla"); ?></li>
	 <li class='separator'>&nbsp;</li>
	 <li id='deletemenubtn' class='disabled' onclick="deleteSelectedItems()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/><?php echo i18n("Elimina selezionati"); ?></li>
	</ul>
  </li>

  <li class='lightgray'><span>Visualizza</span>
	<ul class="submenu" style="width:320px">
	 <?php
	 $_SHOW_COLUMN = array();
	 $defaultColumns = array(
		"code"=>array("title"=>"Codice", "default"=>true, "width"=>70, "sortable"=>true, "editable"=>true),
		"vencode"=>array("title"=>"Cod. art. forn.", "default"=>$_ISVENDOR, "width"=>70, "sortable"=>true, "editable"=>true),
		"mancode"=>array("title"=>"Cod. art. produttore.", "width"=>70, "sortable"=>true, "editable"=>true),
		"sn"=>array("title"=>"S.N.", "width"=>100, "sortable"=>true, "editable"=>true),
		"lot"=>array("title"=>"Lotto", "width"=>100, "sortable"=>true, "editable"=>true),
		"account"=>array("title"=>"Conto", "width"=>60, "sortable"=>true, "editable"=>true),
		"brand"=>array("title"=>"Marca", "default"=>false, "width"=>100, "sortable"=>true, "editable"=>true),
		"description"=>array("title"=>"Articolo / Descrizione", "default"=>true, "minwidth"=>250, "sortable"=>true, "editable"=>true),
		"metric"=>array("title"=>"Computo metrico"),
		"qty"=>array("title"=>"Qta", "default"=>true, "width"=>40, "editable"=>true, "style"=>"text-align:center"),
		"qty_sent"=>array("title"=>"Qta inv.", "default"=>false, "width"=>60, "editable"=>true, "style"=>"text-align:center"),
		"qty_downloaded"=>array("title"=>"Qta scaric.", "default"=>false, "width"=>60, "editable"=>false, "style"=>"text-align:center"),
		"units"=>array("title"=>"U.M.", "default"=>true, "width"=>40, "editable"=>true, "style"=>"text-align:center"),
		"coltint"=>array("title"=>"Colore/Tinta", "width"=>100, "editable"=>true, "format"=>"dropdown", "style"=>"text-align:center"),
		"sizmis"=>array("title"=>"Taglia/Misura", "width"=>100, "editable"=>true, "format"=>"dropdown", "style"=>"text-align:center"),
		"plbaseprice"=>array("title"=>"Pr. base", "width"=>60, "format"=>"currency", "decimals"=>$_DECIMALS, "editable"=>true),
		"plmrate"=>array("title"=>"% ric.", "width"=>60, "format"=>"percentage", "editable"=>true),
		"pldiscperc"=>array("title"=>"% sconto", "width"=>60, "format"=>"percentage"),
		"vendorprice"=>array("title"=>"Pr. Acq.", "default"=>$_ISVENDOR, "width"=>60, "editable"=>true, "format"=>"currency", "decimals"=>$_DECIMALS),
		"unitprice"=>array("title"=>"Pr. Unit", "default"=>true, "width"=>60, "editable"=>true, "format"=>"currency", "decimals"=>$_DECIMALS),
		"weight"=>array("title"=>"Peso unit.", "width"=>60, "style"=>"text-align:center", "editable"=>true),
		"discount"=>array("title"=>"Sconto", "default"=>true, "width"=>60, "editable"=>true, "format"=>"currency percentage"),
		"discount2"=>array("title"=>"Sconto2", "width"=>60, "editable"=>true, "format"=>"percentage"),
		"discount3"=>array("title"=>"Sconto3", "width"=>60, "editable"=>true, "format"=>"percentage"),
		"vat"=>array("title"=>"I.V.A.", "default"=>true, "width"=>40, "editable"=>true, "format"=>"percentage", "style"=>"text-align:left"),
		"vatcode"=>array("title"=>"Cod. IVA", "default"=>false, "width"=>100, "style"=>"text-align:center"),
		"vatname"=>array("title"=>"Descr. IVA", "default"=>false, "width"=>120, "style"=>"text-align:center"),
		"price"=>array("title"=>"Totale", "default"=>true, "width"=>120, "minwidth"=>100, "editable"=>true, "format"=>"currency", "decimals"=>$_DECIMALS, "style"=>"text-align:center"),
		"profit"=>array("title"=>"Guadagno", "width"=>60, "editable"=>false, "format"=>"currency", "decimals"=>$_DECIMALS),
		"margin"=>array("title"=>"% Margine", "width"=>60, "editable"=>false, "format"=>"percentage", "style"=>"text-align:center"),
		"vatprice"=>array("title"=>"Tot. + IVA", "width"=>120, "minwidth"=>100, "editable"=>true, "format"=>"currency", "decimals"=>$_DECIMALS, "style"=>"text-align:center"),
		"pricelist"=>array("title"=>"Listino", "width"=>120, "minwidth"=>100, "style"=>"text-align:center"),
		"docref"=>array("title"=>"Doc. di rif.", "width"=>180, "minwidth"=>180),
		"vendorname"=>array("title"=>"Fornitore", "width"=>120, "minwidth"=>100, "editable"=>true),
		"ritaccapply"=>array("title"=>"Rit. acc.", "width"=>60, "format"=>"checkbox"),
		"ccpapply"=>array("title"=>"Cassa prev.", "width"=>60, "format"=>"checkbox")
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

	 for($c=0; $c < count($_CFG_COLUMNS); $c++)
	 {
	  $col = $_CFG_COLUMNS[$c];
	  if(!$_COLUMNS[$col['tag']]) continue;
	  $_COLUMNS[$col['tag']]['title'] = $col['title'];
	  $_COLUMNS[$col['tag']]['width'] = $col['width'];
	  $_COLUMNS[$col['tag']]['default'] = true;
	 }

	 reset($_COLUMNS);
	 while(list($k,$v) = each($_COLUMNS))
	 {
	  echo "<li style='width:150px;float:left'><input type='checkbox' datatag='".$k."' onclick=\"showHideColumn('".$k."',this.checked)\"";
	  /*if($_COOKIE['GCD-COL-'.strtoupper($k)] == "ON")
	  {
	   echo " checked='true'";
	   $_SHOW_COLUMN[$k] = true;
	  }
	  else if(($_COOKIE['GCD-COL-'.strtoupper($k)] != "OFF") && ($v['default'] == true))
	  {
	   echo " checked='true'";
	   $_SHOW_COLUMN[$k] = true;
	  }*/
	  if($v['default'])
	  {
	   echo " checked='true'";
	   $_SHOW_COLUMN[$k] = true;
	  }
	  echo "/>".$v['title']."</li>";
	 }
 
	 ?>
	 <li class='separator' style='clear:both'>&nbsp;</li>
	 <li onclick="saveColumnsSettings(this.parentNode)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/save.gif"/><?php echo i18n("Save columns settings"); ?></li>
	</ul>
  </li>

  <li class='blue' id='selectionmenu' style='visibility:hidden;'><span><img src="<?php echo $_ABSOLUTE_URL; ?>var/templates/basicapp/img/checkbox.png" border='0'/>Selezionati</span>
	<ul class="submenu">
	 <li onclick="tb.unselectAll()">Annulla selezione</li>
	 <li class='separator'></li>
	 <li onclick="predefmsgAddFromSelected()" id='addpredefmsgmenuitem' style='display:none'>Aggiungi nel elenco dei messaggi predefiniti</li>
	 <li class='separator' id='addpredefmsgmenusep' style='display:none'></li>
	 <li onclick="engagesItemsOn('order')">Impegna su ordine</li>
	 <li onclick="engagesItemsOn('intervreport')">Impegna su rapp. d&lsquo;intervento</li>
     <?php
     if(file_exists($_BASE_PATH."Commesse/index.php"))
	  echo "<li onclick=\"engagesItemsOn('commessa')\">Impegna su commessa</li>";
	 ?>
	 <li class='separator'></li>
	 <li onclick="unengagesSelectedItems()">Disimpegna articoli selezionati</li>
	 <li class='separator'></li>
	 <li onclick="IncludeItemDesc()">Includi descrizione articolo</li>
	 <li class='separator'></li>
	 <li onclick="deleteSelectedItems()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/>Elimina selezionati</li>

	</ul>
  </li>
 </ul>

 </td><td width='600'>
 <ul class="hotbtns">
  <li class="left-brachet">&nbsp;</li>
  <?php
  if(_userInGroup("gmart") && file_exists($_BASE_PATH."Products/index.php") && !$config['interface']['braketbuttons']['hide_article_btn'])
   echo "<li onclick=\"InsertRow('article')\"><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/add-product.png' title='Aggiungi articolo'/></li>";
  if(_userInGroup("gserv") && file_exists($_BASE_PATH."Services/index.php") && !$config['interface']['braketbuttons']['hide_service_btn'])
   echo "<li onclick=\"InsertRow('service')\"><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/add-service.png' title='Aggiungi servizio'/></li>";
  if(_userInGroup("gproducts") && file_exists($_BASE_PATH."FinalProducts/index.php") && !$config['interface']['braketbuttons']['hide_finalproduct_btn'])
   echo "<li onclick=\"InsertRow('finalproduct')\"><img src='".$_ABSOLUTE_URL."FinalProducts/icon.png' title='Aggiungi prodotto finito'/></li>";
  if(_userInGroup("gpart") && file_exists($_BASE_PATH."Parts/index.php") && !$config['interface']['braketbuttons']['hide_component_btn'])
   echo "<li onclick=\"InsertRow('component')\"><img src='".$_ABSOLUTE_URL."Parts/icon.png' title='Aggiungi componente'/></li>";
  if(_userInGroup("gmaterial") && file_exists($_BASE_PATH."Materials/index.php") && !$config['interface']['braketbuttons']['hide_material_btn'])
   echo "<li onclick=\"InsertRow('material')\"><img src='".$_ABSOLUTE_URL."Materials/icon.png' title='Aggiungi materiale'/></li>";
  if(_userInGroup("glabor") && file_exists($_BASE_PATH."Labors/index.php") && !$config['interface']['braketbuttons']['hide_labor_btn'])
   echo "<li onclick=\"InsertRow('labor')\"><img src='".$_ABSOLUTE_URL."Labors/icon.png' title='Aggiungi lavorazione'/></li>";
  if(_userInGroup("gbook") && file_exists($_BASE_PATH."Books/index.php") && !$config['interface']['braketbuttons']['hide_book_btn'])
   echo "<li onclick=\"InsertRow('book')\"><img src='".$_ABSOLUTE_URL."Books/icon.png' title='Aggiungi libro'/></li>";
  if(_userInGroup("gsupplies") && file_exists($_BASE_PATH."Supplies/index.php") && !$config['interface']['braketbuttons']['hide_supply_btn'])
   echo "<li onclick=\"InsertRow('supply')\"><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/add-supply.png' title='Aggiungi altre forniture'/></li>";
  ?>
  <li onclick="InsertRow('custom')"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/add-custom.png" title="Aggiungi riga personalizzata"/></li>
  <li class="separator">&nbsp;</li>
  <li onclick="InsertRow('note')"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/add-note.png" title="Aggiungi riga di nota"/></li>
  <li onclick="InsertMessage(this)"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/message.png" title="Aggiungi messaggio predefinito"/></li>
  <li onclick="InsertLastProduct(this)"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/lastproducts.png" title="Articoli venduti a questo cliente"/></li>
  <li class="right-brachet">&nbsp;</li>
 </ul>
 </td></tr></table>

 <div class="gmutable" style="height:90%;margin-top:5px;">
 <table id='doctable' class="gmutable" width="100%" cellspacing="0" cellpadding="0" border="0" style="display:none;">
 <tr><th width='70'><input type="checkbox" onchange="tb.selectAll(this.checked)"/></th>
	<?php
	reset($_COLUMNS);
	while(list($k,$v) = each($_COLUMNS))
	{
	 if($k == "metric")
	 {
	  $vis = $_SHOW_COLUMN['metric'];
	  echo "<th id='metriceqp' width='60' editable='true'".(!$vis ? " style='display:none'" : "").">Par. Ug.</th>";
	  echo "<th id='metriclength' width='60' editable='true'".(!$vis ? " style='display:none'" : "").">Lung.</th>";
	  echo "<th id='metricwidth' width='60' editable='true'".(!$vis ? " style='display:none'" : "").">Larg.</th>";
	  echo "<th id='metrichw' width='60' editable='true'".(!$vis ? " style='display:none'" : "").">H/Peso</th>";
	  continue;
	 }

	 echo "<th id='".$k."'";
	 if($v['width']) echo " width='".$v['width']."'";
	 if($v['sortable']) echo " sortable='true'";
	 if($v['editable']) echo " editable='true'";
	 /*if($v['minwidth']) echo " minwidth='".$v['minwidth']."'";*/

	 if($k == "description")
	  $v['title'].="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"; 

	 if($v['format']) echo " format='".$v['format']."'";
	 if($v['decimals']) echo " decimals='".$v['decimals']."'";
	 if(!$_SHOW_COLUMN[$k])
	  echo " style='display:none;".$v['style']."'";
	 else if($v['style'])
	  echo " style='".$v['style']."'";
	 echo ">".($v['title'])."</th>";
	}
    // colonne extra_qty e price_adjust //
	echo "<th id='extraqty' width='40' style='display:none;'>XQTY</th>";
	echo "<th id='priceadjust' width='40' style='display:none;'>PRICEADJ</th>";
	?>
 </tr>

 <?php
 $totWeight = 0;
 $weightMul = array('mg'=>0.000001, 'g'=>0.001, 'hg'=>0.1, 'kg'=>1, 'q'=>100, 't'=>1000);
 $docInfo['profit'] = 0;

 $_VAT_RATES = array();
 $_TOT_PURCHASECOSTS = 0;

 for($c=0; $c < count($docInfo['elements']); $c++)
 {
  $itm = $docInfo['elements'][$c];
  echo "<tr id='".$itm['id']."' type='".$itm['type']."' refap='".$itm['ref_ap']."' refid='".$itm['ref_id']."' vatid='".$itm['vatid']."' vattype='"
	.$itm['vattype']."' vendorid='".$itm['vendor_id']."' weightunits='".$itm['weightunits']."' row_ref_docap='"
	.$itm['row_ref_docap']."' row_ref_docid='".$item['row_ref_docid']."' row_ref_id='".$item['row_ref_id']."'><td width='70'><input type='checkbox'/>";
  switch(strtolower($itm['type']))
  {
   case 'note' : echo "<img src='img/print-mini.png' title='Clicca per trasformare questa riga di nota in un messaggio non stampabile' onclick='switchNoteMessage(this)' style='margin-left:5px'/><img src='img/edit-black.png' style='margin-left:5px' title='Clicca per editare questa nota' onclick='editNote(this)'/>"; break;

   case 'message' : echo "<img src='img/message-mini.png' title='Clicca per trasformare questo messaggio in una riga di nota stampabile' onclick='switchNoteMessage(this)' style='margin-left:5px'/><img src='img/edit-black.png' style='margin-left:5px' title='Clicca per editare questa nota' onclick='editNote(this)'/>"; break;

   case 'article' : echo "<img src='img/article-mini.png' title='Clicca per vedere la scheda di questo articolo' onclick='showProduct(this)' style='margin-left:5px'/>"; break;

   case 'finalproduct' : echo "<img src='img/finalproduct-mini.png' title='Clicca per vedere la scheda di questo prodotto' onclick='showFinalProduct(this)' style='margin-left:5px'/>"; break;

   case 'component' : echo "<img src='img/component-mini.png' title='Clicca per vedere la scheda di questo componente' onclick='showComponent(this)' style='margin-left:5px'/>"; break;

   case 'material' : echo "<img src='img/material-mini.png' title='Clicca per vedere la scheda di questo materiale' onclick='showMaterial(this)' style='margin-left:5px'/>"; break;

   case 'labor' : echo "<img src='img/labor-mini.png' title='Clicca per vedere la scheda di questa lavorazione' onclick='showLabor(this)' style='margin-left:5px'/>"; break;

   case 'book' : echo "<img src='img/book-mini.png' title='Clicca per vedere la scheda di questo libro' onclick='showBook(this)' style='margin-left:5px'/>"; break;

   case 'service' : echo "<img src='img/service-mini.png' title='Clicca per vedere la scheda di questo servizio' onclick='showService(this)' style='margin-left:5px'/>"; break;

   case 'pictureframe' : echo "<img src='img/pictureframe-mini.png' title='Clicca per vedere i dettagli di questa cornice' onclick='showPictureFrame(this)' style='margin-left:5px'/>"; break;

  } 
  echo "</td>";

  if((strtolower($itm['type']) == "note") || (strtolower($itm['type']) == "message"))
   echo "<td colspan='100'><span class='graybold'>".strip_tags($itm['desc'])."</span><span style='display:none'>".$itm['desc']."</span></td>";
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

   $amount = round(((($itm['price']-$discount)-$discount2)-$discount3) * $qty, 5);
   $total = $amount;

   $totWeight+= (($itm['weight']*$qty)*$weightMul[$itm['weightunits']]);

   $docInfo['profit']+= $itm['profit'];

   reset($_COLUMNS);
   while(list($k,$v) = each($_COLUMNS))
   {
	switch($k)
	{
	 case 'code' : echo "<td><span class='graybold'>".$itm['code']."</span></td>"; break;
	 case 'vencode' : echo "<td><span class='graybold'>".$itm['vencode']."</span></td>"; break;
	 case 'mancode' : echo "<td><span class='graybold'>".$itm['mancode']."</span></td>"; break;
	 case 'sn' : case 'serialnumber' : echo "<td><span class='graybold'>".$itm['serialnumber']."</span></td>"; break;
	 case 'lot' : echo "<td><span class='graybold'>".$itm['lot']."</span></td>"; break;
	 case 'account' : echo "<td><span class='graybold'>&nbsp;</span></td>"; break; /* TODO: da sistemare */
	 case 'brand' : echo "<td refid='".$itm['brand_id']."'><span class='graybold'>".$itm['brand_name']."</span></td>"; break; 
	 case 'description' : echo "<td><span class='graybold doubleline'>".$itm['name']."</span></td>"; break;
	 case 'qty' : echo "<td><span class='graybold 13 center'>".$itm['qty']."</span></td>"; break;
	 case 'qty_sent' : echo "<td><span class='graybold 13 center'>".$itm['qty_sent']."</span></td>"; break;
	 case 'qty_downloaded' : echo "<td><span class='graybold 13 center'>".$itm['qty_downloaded']."</span></td>"; break;

	 case 'metric' : {
		 echo "<td><span class='graybold 13 center'>".$itm['metric_eqp']."</span></td>"; 
		 echo "<td><span class='graybold 13 center'>".$itm['metric_length']."</span></td>";
		 echo "<td><span class='graybold 13 center'>".$itm['metric_width']."</span></td>";
		 echo "<td><span class='graybold 13 center'>".$itm['metric_hw']."</span></td>";
		} break;

	 case 'units' : echo "<td><span class='graybold 13 center'>".$itm['units']."</span></td>"; break;
	 case 'coltint' : echo "<td><span class='graybold center'>".$itm['variant_coltint']."</span></td>"; break;
	 case 'sizmis' : echo "<td><span class='graybold center'>".$itm['variant_sizmis']."</span></td>"; break;
	 case 'vendorprice' : {
		 echo "<td realvalue='".$itm['vendor_price']."'><span class='graybold' title='Valore reale: "
		 .number_format($itm['vendor_price'],4,',','.')."'>".number_format($itm['vendor_price'],$_DECIMALS,',','.')."</span></td>";
		 $_TOT_PURCHASECOSTS+= ($itm['vendor_price'] * $itm['qty']);
		} break;

	 case 'unitprice' : echo "<td realvalue='".($_ISVENDOR ? $itm['sale_price'] : $itm['price'])."'><span class='graybold' title='Valore reale: "
		.number_format($_ISVENDOR ? $itm['sale_price'] : $itm['price'],4,',','.')."'>".number_format($_ISVENDOR ? $itm['sale_price'] : $itm['price'],$_DECIMALS,',','.')."</span></td>"; break;

	 case 'plbaseprice' : echo "<td realvalue='".$itm['plbaseprice']."'><span class='graybold' title='Valore reale: "
		.number_format($itm['plbaseprice'],4,',','.')."'>".number_format($itm['plbaseprice'],$_DECIMALS,',','.')."</span></td>"; break;
	 case 'plmrate' : echo "<td><span class='graybold'>".$itm['plmrate']."%</span></td>"; break;
	 case 'pldiscperc' : echo "<td><span class='graybold'>".$itm['pldiscperc']."%</span></td>"; break;

	 case 'discount' : {
	     if($itm['discount_perc'] > 0)
	      echo "<td realvalue='".$itm['discount_perc']."%'><span class='graybold'>".$itm['discount_perc']."%</span></td>";
	     else if($itm['discount_inc'] > 0)
	      echo "<td realvalue='".$itm['discount_inc']."'><span class='graybold'>&euro;. ".number_format($itm['discount_inc'],$_DECIMALS,',','.')."</span></td>";
	     else
	      echo "<td><span class='graybold'></span></td>";
		} break;
	 case 'discount2' : echo "<td><span class='graybold'>".(($itm['discount2'] > 0) ? $itm['discount2']."%" : "")."</span></td>"; break;
	 case 'discount3' : echo "<td><span class='graybold'>".(($itm['discount3'] > 0) ? $itm['discount3']."%" : "")."</span></td>"; break;
	 case 'vat' : echo "<td><span class='graybold center'>".$itm['vatrate']."%</span></td>"; break;
	 
	 case 'vatcode' : echo "<td><span class='graybold center'>".($itm['vatid'] ? $_VAT_BY_ID[$itm['vatid']]['code_str'] : '')."</span></td>"; break;
	 case 'vatname' : echo "<td><span class='graybold center'>".($itm['vatid'] ? $_VAT_BY_ID[$itm['vatid']]['name'] : '')."</span></td>"; break;
	 
	 case 'price' : echo "<td realvalue='".$amount."'><span class='eurogreen' title='Valore reale: "
		.number_format($amount,4,',','.')."'><em>&euro;</em>".number_format($amount,2,',','.')."</span></td>"; break;

	 case 'profit' : echo "<td realvalue='".$itm['profit']."'><span class='graybold' title='Valore reale: "
		.number_format($itm['profit'],4,',','.')."'>".number_format($itm['profit'],$_DECIMALS,',','.')."</span></td>"; break;
	 case 'margin' : echo "<td><span class='graybold center'>".($itm['margin'] ? sprintf("%.2f",$itm['margin']) : "0")."%</span></td>"; break;

	 case 'vatprice' : echo "<td realvalue='".($amount+$itm['vat'])."'><span class='eurogreen' title='Valore reale: ".number_format($amount+$itm['vat'],4,',','.')."'><em>&euro;</em>".number_format($amount+$itm['vat'],2,',','.')."</span></td>"; break;
	 case 'pricelist' : echo "<td pricelistid='".$itm['pricelist_id']."'><span class='graybold'>".($itm['pricelist_id'] ? $_PRICELIST_BY_ID[$itm['pricelist_id']]['name'] : $_PLINFO['name'])."</span></td>"; break;
	 case 'weight' : echo "<td><span class='graybold 13 center'>".$itm['weight']." ".$itm['weightunits']."</span></td>"; break;
	 case 'docref' : {
		 echo "<td><span class='graybold'>";
		 if($itm['doc_ref_ap'] && $itm['doc_ref_id'])
		 {
		  $db = new AlpaDatabase();
		  $db->RunQuery("SELECT name FROM dynarc_".$itm['doc_ref_ap']."_items WHERE id='".$itm['doc_ref_id']."'");
		  if($db->Read())
		   echo $db->record['name'];
		  $db->Close();
		 }
		 echo "</span></td>";
		} break;
	 case 'vendorname' : echo "<td><span class='graybold'>".$itm['vendor_name']."</span></td>"; break;
	 case 'ritaccapply' : echo "<td><input type='checkbox'".($itm['ritaccapply'] ? " checked='true'/>" : "/>")."</td>"; break;
	 case 'ccpapply' : echo "<td><input type='checkbox'".($itm['ccpapply'] ? " checked='true'/>" : "/>")."</td>"; break;
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

 <!-- RIF. PA -->
 <div id="rifpa-container" <?php if($_SUBJECT_TYPE != 2) echo "style='display:none'"; ?>>
  <table width='100%' cellspacing='0' cellpadding='0' border='0'>
   <tr><td>Riferimenti x la PA</td>
	   <td>N. Doc.: <input type='text' class='edit' style='width:100px' maxlength='20' id="pa-docnum" value="<?php echo $docInfo['pa_docnum']; ?>"/></td>
	   <td>CIG: <input type='text' class='edit' style='width:100px' maxlength='15' id="pa-cig" value="<?php echo $docInfo['pa_cig']; ?>" title="Digita il codice CIG (Codice Identificativo Gara)"/></td>
	   <td>CUP: <input type='text' class='edit' style='width:100px' maxlength='15' id="pa-cup" value="<?php echo $docInfo['pa_cup']; ?>" title="Digita il codice CUP (Codice Univoco Progetto)"/></td>
   </tr>
  </table>
 </div>
 <!-- EOF - RIF. PA -->

 <table class="docfooter-results" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:12px;">
  <tr><th class="blue" style="text-align:left" rowspan="2"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/discount-button.png" style="cursor:pointer;margin-left:3px" onclick="showUnconditionalDiscountCloud(this)"/></th>
	  <th class="blue" width="22" valign="middle" rowspan="2"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/eye.png" style="cursor:pointer" onclick="showColumnsCloud(this)"/></th>
	  <th class="blue" width="22" valign="middle" rowspan="2"><img src="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/calc.png" style="cursor:pointer" onclick="showTotalsCloud(this)"/></th>
	  <th class="blue" width="70" id="doctot-weight-th" <?php if($_COOKIE['GCD-DOCTOTCOL-WEIGHT'] != "ON") echo "style='display:none'"; ?>>PESO (kg)</th>
	  <th class="blue" width="110">IMPONIBILE</th>
	  <th class="blue" width="80">TOT. SCONTI</th>
	  <th class='blue' width='90' id='docfoot-th-rivinps' <?php 
		if(!$_CPA['rivalsa_inps'] || !$_SHOW_CONTRIBANDDEDUCTS)
		 echo "style='display:none'";
		?>>RIV. INPS</th>
	  <th class="blue" width="90">I.V.A.</th>
	  <th class="red" id='doctot-purchasecosts-th' width="90" <?php if(!$_COLUMNS['vendorprice']['default']) echo "style='display:none'"; ?>>Costo acqisto</th>
	  <th class="green" width="110">TOTALE</th>
	  <th class='blue' width='110' id='docfoot-th-ritacc' <?php
		if(!$_CPA['rit_acconto'] || !$_SHOW_CONTRIBANDDEDUCTS)
		 echo "style='display:none'";
		?>>RIT. ACCONTO</th>
	  <th class='blue' width='90' id='docfoot-th-stamp'>BOLLI</th>
	  <th class='blue' width='90'>SP. INCASSO</th>
	  <th class='blue' id='doctot-profit-th' width='90' <?php if(!$_COLUMNS['profit']['default']) echo "style='display:none'"; ?>>GUADAGNO</th>
  	  <th class="orange" id='doctot-notpayvat-th' width="90">I.V.A. non dovuta</th>
	  <th class='green' width='110'>NETTO A PAGARE</th>
  </tr>
  <tr>
	  <td class="blue" id="doctot-weight" style="font-size:13px;text-align:center;<?php if($_COOKIE['GCD-DOCTOTCOL-WEIGHT'] != 'ON') echo 'display:none'; ?>"><?php echo sprintf("%.2f",$totWeight); ?></td>
	  <td class="blue" id="doctot-amount"><em>&euro;</em><?php echo number_format($docInfo['amount'],2,',','.'); ?></td>
	  <td class="blue" id="doctot-discount"><em>&euro;</em><?php echo number_format(0,2,',','.'); ?></td>
	  <td class='blue' id='doctot-rivinps' <?php
		if(!$_CPA['rivalsa_inps'] || !$_SHOW_CONTRIBANDDEDUCTS)
		 echo "style='display:none'";
		?>><em>&euro;</em><?php echo number_format($docInfo['tot_rinps'],2,',','.'); ?></td>
	  <td class="blue" id="doctot-vat"><em>&euro;</em><?php echo number_format($docInfo['vat'],2,',','.'); ?></td>
	  <td class="red" id="doctot-purchasecosts" <?php if(!$_COLUMNS['vendorprice']['default']) echo "style='display:none'"; ?>><em>&euro;</em><?php echo number_format($_TOT_PURCHASECOSTS,2,',','.'); ?></td>
	  <td class="green" id="doctot-total"><em>&euro;</em><?php echo number_format($docInfo['total'],2,',','.'); ?></td>
	  <td class='blue' id='doctot-ritacconto' <?php
		if(!$_CPA['rit_acconto'] || !$_SHOW_CONTRIBANDDEDUCTS)
		 echo "style='display:none'";
		?>><em>&euro;</em><?php echo number_format($docInfo['tot_rit_acc'],2,',','.'); ?></td>
	  <td class='blue' id='doctot-stamp'><em>&euro;</em><?php echo number_format($docInfo['stamp'],2,',','.'); ?></td>
	  <td class='blue' id='doctot-collectioncharges'><em>&euro;</em><?php echo number_format($docInfo['collection_charges'],2,',','.'); ?></td>
	  <td class='blue' id='doctot-profit' <?php if(!$_COLUMNS['profit']['default']) echo "style='display:none'"; ?>><em>&euro;</em><?php echo number_format($docInfo['profit'],2,',','.'); ?></td>
	  <td class="orange" id="doctot-notpayvat"><em>&euro;</em><?php echo number_format(0,2,',','.'); ?></td> <!-- TODO: da modificare -->
	  <td class='green' id='doctot-netpay'><em>&euro;</em><?php echo number_format($docInfo['tot_netpay'],2,',','.'); ?></td>
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
	 $stripmsg = strip_tags($list[$c]['text']);
	 if(strlen($stripmsg) > 100)
	  $stripmsg = substr($stripmsg,0,100)."...";
	 echo "<tr id='".$list[$c]['id']."'><td onclick='insertPredefMsg(this)'>".$stripmsg."</td><td width='16'><img src='".$_ABSOLUTE_URL."GCommercialDocs/img/delete.png' onclick='deletePredefMsg(this)' style='cursor:pointer'/></td><td style='display:none'>".$list[$c]['text']."</td></tr>";
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

 <!-- LAST PRODUCTS -->
 <div id="lastproducts" class="predefmsg" style="display:none">
  <div class="predefmsg-header">PRODOTTI VENDUTI RECENTEMENTE</div>
  <div id="lastproducts-container" class="predefmsg-container">
   <table cellspacing="0" cellpadding="0" border="0" class="predefmsg-list" id="lastproducts-list">
	<tr><th style='width:22px'><input type='checkbox' onclick='lastproductsSelectAll(this)'/></th>
		<th style='width:60px;text-align:left'>Codice</th>
		<th style='width:158px;text-align:left'>Descrizione</th>
		<th style='width:60px'>Qt&agrave; vendute</th></tr>
	<?php
	$ret = GShell("commercialdocs get-last-products".($subjectInfo ? " -subjectid '".$subjectInfo['id']."'" : ""));
	$list = $ret['outarr']['items'];
	for($c=0; $c < count($list); $c++)
	{
	 echo "<tr refap='".$list[$c]['ap']."' refid='".$list[$c]['id']."'><td><input type='checkbox'/></td>";
	 echo "<td><small>".$list[$c]['code']."</small></td>";
	 echo "<td><small>".$list[$c]['name']."</small></td>";
	 echo "<td align='center'><small>".$list[$c]['qty']."</small></td></tr>";
	}
	?>
   </table>
  </div>
  <div>&nbsp;<a href='#' class='addmsgbtn' onclick="lastproductsInsertSelected()">Inserisci i prodotti selezionati</a></div>
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
	<tr><td><b>Spese d&lsquo;incasso:</b> </td>
		<td><input type='text' class='edit' style='width:50px' id='collection_charges' value="<?php echo number_format($docInfo['collection_charges'],2,',','.'); ?>" onfocus="ACTIVE_GMUTABLE=null" onchange="_setCollectionCharges(this)"/> <b>&euro;</b></td></tr>
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

	<tr><td>IVA non dovuta</td>			<td align='right' id='summary-notpay-vat'>0,00 &euro;</td>
		<td width='20'>&nbsp;</td>
		<td>Spese d&lsquo;incasso</td>	<td align='right' id='summary-collection-charges'>0,00 &euro;</td></tr>

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
		 if($docInfo['ship_subject_id'])
		  echo "<option value='".$docInfo['ship_contact_id']."' selected='selected'>altro destinatario</option>";
		 echo "<option value=''".(!$docInfo['ship_contact_id'] ? " selected='selected'>" : ">")."altra destinazione...</option>";
		 ?>
		</select></td></tr>
	 <tr><td colspan="2">
		<i>Destinatario (cognome e nome / ragione sociale)</i><br/>
		<input type="text" class='edit' id="ship-subject" style="width:90%" value="<?php echo $docInfo['ship_recp'] ? $docInfo['ship_recp'] : $subjectInfo['contacts'][0]['name']; ?>"/></td></tr>
	 <tr><td colspan="2">
		<i>Indirizzo (via, piazza, ...)</i><br/>
		<input type="text" class='edit' id="ship-address" style="width:70%" value="<?php echo $docInfo['ship_addr'] ? $docInfo['ship_addr'] : $subjectInfo['contacts'][0]['address']; ?>"/></td></tr>
	 <tr><td><i>Citt&agrave;</i><br/><input type="text" class='edit' id="ship-city" style="width:80%" value="<?php echo $docInfo['ship_city'] ? $docInfo['ship_city'] : $subjectInfo['contacts'][0]['city']; ?>"/></td>
		 <td><i>C.A.P.</i><i style='margin-left:30px;'>Prov.</i><br/>
		<input type="text" class='edit' style='width:50px' id="ship-zipcode" value="<?php echo $docInfo['ship_zip'] ? $docInfo['ship_zip'] : $subjectInfo['contacts'][0]['zipcode']; ?>"/>&nbsp;&nbsp;<input type="text" class='edit' style='width:30px' id="ship-prov" value="<?php echo $docInfo['ship_prov'] ? $docInfo['ship_prov'] : $subjectInfo['contacts'][0]['province']; ?>"/></td></tr>
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
	<tr><td><i class='blue'>Vettore / Conducente</i><br/>
		<input type='text' class='edit' id="trans_shipper" style="width:90%" value="<?php echo $docInfo['trans_shipper']; ?>"/></td>
		<td><i class='blue'>Numero di tracking</i><br/>
		<input type='text' class='edit' style='width:140px' maxlength='32' id='tracking_number' value="<?php echo $docInfo['tracking_number']; ?>"/></td>
		<td width="100"><i class='blue'>Targa</i><br/><input type='text' class='edit' style='width:80px' id="trans_numplate" value="<?php echo $docInfo['trans_numplate']; ?>"/></td></tr>
	<tr><td><i class='blue'>Data e ora consegna</i><br/>
		<input type='text' class='edit' style='width:90px' id="trans_date" value="<?php if($docInfo['trans_datetime']) echo date('d/m/Y',$docInfo['trans_datetime']); ?>"/> <input type='text' class='edit' style='width:50px' id="trans_time" value="<?php if($docInfo['trans_datetime']) echo date('H:i',$docInfo['trans_datetime']); ?>"/></td>
		<td colspan='2'><i class='blue'>Causale</i><br/><input type='text' class='edit' id="trans_causal" style='width:90%' value="<?php echo $docInfo['trans_causal']; ?>"/></td></tr>
	<tr><td><i class='blue'>Aspetto esteriore dei beni</i><br/><input type='text' class='edit' id="trans_aspect" style='width:80%' value="<?php echo $docInfo['trans_aspect']; ?>"/></td>
		<td width="145"><i class='blue'>N. colli</i><i class='blue' style="margin-left:30px;">Peso</i><br/>
		<input type='text' class='edit' style='width:50px' id="trans_num" value="<?php echo $docInfo['trans_num']; ?>"/> <input type='text' class='edit' id="trans_weight" style='width:50px' value="<?php echo $docInfo['trans_weight']; ?>"/>Kg</td>
		<td><i class='blue'>Porto</i><br/><input type='text' class='edit' style='width:80px' id="trans_freight" value="<?php echo $docInfo['trans_freight']; ?>"/></td></tr>
	<tr><td colspan='3'>
		 <table cellspacing='0' cellpadding='0' border='0'>
		  <tr><td><i class='blue'>Spese di trasporto: </i></td>
			  <td>&nbsp;&nbsp;<input type='text' class='edit' style='width:60px' id="cartage" value="<?php echo number_format($docInfo['cartage'],2,',','.'); ?>" onchange="updateTotals()"/> &euro;</td>
			  <td>&nbsp;&nbsp;IVA: <select style='width:120px' id="cartage_vatid" onchange="updateTotals()">
				<?php
				if(!$docInfo['cartage_vatid']) $docInfo['cartage_vatid'] = $_SUBJECT_VAT_ID ? $_SUBJECT_VAT_ID : $_DEF_VAT_ID;
				for($c=0; $c < count($_VAT_LIST); $c++)
				 echo "<option value='".$_VAT_LIST[$c]['id']."'".(($docInfo['cartage_vatid'] == $_VAT_LIST[$c]['id']) ? " selected='selected'>" : ">")
					.$_VAT_LIST[$c]['name']."</option>";
				?>
				</select>
			  </td></tr>
		   <tr><td><i class='blue'>Spese di imballaggio: </i></td>
			   <td>&nbsp;&nbsp;<input type='text' class='edit' style='width:60px' id="packing_charges" value="<?php echo number_format($docInfo['packing_charges'],2,',','.'); ?>" onchange="updateTotals()"/> &euro;</td>
			   <td>&nbsp;&nbsp;IVA: <select style='width:120px' id="packing_charges_vatid" onchange="updateTotals()">
				<?php
				if(!$docInfo['packing_charges_vatid']) $docInfo['packing_charges_vatid'] = $_SUBJECT_VAT_ID ? $_SUBJECT_VAT_ID : $_DEF_VAT_ID;
				for($c=0; $c < count($_VAT_LIST); $c++)
				 echo "<option value='".$_VAT_LIST[$c]['id']."'".(($docInfo['packing_charges_vatid'] == $_VAT_LIST[$c]['id']) ? " selected='selected'>" : ">")
					.$_VAT_LIST[$c]['name']."</option>";
				?>
				</select>
			   </td></tr>
		  </table>
		</td>
	</tr>	
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
		<select id="paymentmode-select" style="width:180px;" onchange="_paymentModeSelectChange(this,null,true)">
		<?php
		$ret = GShell("paymentmodes list");
		$list = $ret['outarr'];
		for($c=0; $c < count($list); $c++)
		 echo "<option value='".$list[$c]['id']."' collcharges='".$list[$c]['collection_charges']."'".($docInfo['paymentmode'] == $list[$c]['id'] ? " selected='selected'>" : ">").$list[$c]['name']."</option>";
		?>
		</select>&nbsp;&nbsp;&nbsp;<i>Banca d&lsquo;appoggio:</i> <select id="ourbanksupport-select" style="width:250px;">
		<?php
		for($c=0; $c < count($_BANKS); $c++)
		 echo "<option value='".$c."'".($docInfo['ourbanksupport_id'] == $c ? " selected='selected'>" : ">").$_BANKS[$c]['name']."</option>";
		?>
		</select></td></tr>
	<tr><td colspan="2" style="border-bottom:1px solid #dadada;"><i>Banca del cliente:</i> <select id="banksupport-select" style="width:250px;">
		<option value='0'>&nbsp;</option>
		<?php
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
		  case 'agentinvoices' : case 'memberinvoices' : case 'vendororders' : case 'purchaseinvoices' : case 'creditsnote' : case 'ddtin' : {
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
		 switch(strtolower($_CAT_TAG))
		 {
		  case 'agentinvoices' : case 'memberinvoices' : case 'vendororders' : case 'purchaseinvoices' : case 'creditsnote' : case 'ddtin' : echo "<td><input type='text' style='width:60px' value='".number_format($_SCHEDULE[$c]['expenses'],2,',','.')."' onchange='scheduleAmountChange(this)'/><i>&euro;</i></td>"; break;
		  default : echo "<td><input type='text' style='width:60px' value='".number_format($_SCHEDULE[$c]['incomes'],2,',','.')."' onchange='scheduleAmountChange(this)'/><i>&euro;</i></td>"; break;
		 }
		 
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
<table width='800' align='center' cellspacing='0' cellpadding='0' border='0'>
<tr><td>
<div style="font-family:Arial,sans-serif;font-size:12px;color:#555555;text-align:center;"><i>Gli acconti e le scadenze saldate non vengono automaticamente registrate,<br/>pertanto devono essere annotate manualmente nel registro della Prima Nota.</i>
</div></td>
<?php
if($_LOCKED)
{
 ?>
 <td align='center' width='140'><input type='button' class='button-blue' value="Salva pagamenti" onclick="savePayments()"/></td>
 <?php
}
?>
</tr>
</table>

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
	  case 'agentinvoices' : case 'memberinvoices' : case 'vendororders' : case 'purchaseinvoices' : case 'ddtin' : case 'creditsnote' : echo "<td>".number_format($itm['expenses'],2,',','.')." &euro;</td>"; break;
	  default : echo "<td>".number_format($itm['incomes'],2,',','.')." &euro;</td>"; break;
	 }
	 if($itm['res_id'])
	 {
	  echo "<td width='200'><span class='fixed' style='width:200px;'>&nbsp;</span></td></tr>";
	 }
	 else
	  echo "<td width='200'><span class='fixed' style='width:200px;'>&nbsp;</span></td></tr>";
	}

	$summaryResults = $docInfo['tot_netpay'] - $totDeposits - $totPaidSchedule;

	?>
	</table>
	</td><td valign="top">
	<div class="riepilogodiv">
	 <div class='row'>Totale documento <span class="blueblock" id='summary_doc_tot'><?php echo number_format($subtotNetPay ? $subtotNetPay : $subtotVI,3,',','.'); ?> &euro;</span></div>
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
		<div id='attachments-explore' class='attachments-explore' <?php if($config['other']['subjinfoxl']['enabled']) echo "style='height:140px'"; ?>>
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

		<div<?php if(!$config['other']['subjinfoxl']['enabled']) echo " style='display:none';" ?>>
		 <div class='subjinfoxl-title'>DETTAGLI CLIENTE</div>
		 <div id='subjinfoxl-container' style="height:270px">
		 <?php
		  if($config['other']['subjinfoxl']['enabled'] && $config['other']['subjinfoxl']['contentap'] && $config['other']['subjinfoxl']['contentid'])
		  {
		   $ret = GShell("parserize -ap '".$config['other']['subjinfoxl']['contentap']."' -id '".$config['other']['subjinfoxl']['contentid']."' -p contactinfo -params 'id=".$docInfo['subject_id']."'");
		   if(!$ret['error'])
		   {
			echo $ret['message'];
		   }
		  }
		 ?>
		 </div>
		</div>

	    <div class="docnotes">
		 <h3>ANNOTAZIONI</h3>
		 <textarea id="notes"><?php echo $docInfo['desc']; ?></textarea>
		</div>

</div>
<!-- EOF - PAGE ATTACHMENTS -->

<!-- PAGE - CONTRIBUTIONS AND DEDUCTIONS -->
<div class='tabpage' id="contribanddeduct-page" style="display:none;padding-left:40px">
 <h3 style="font-family: Arial;font-size: 16px;color: #0169c9;">CONTIBUTI E RITENUTE</h3>
 <table border='0' class='sectiontable' style="border-bottom:1px solid #d8d8d8">
  <tr><td><input type='checkbox' id='riv_inps_enabled' <?php if($_CPA['rivalsa_inps']) echo "checked='true'"; ?>/> Rivalsa INPS</td>
	  <td><input type='text' class='edit' id='riv_inps' style='width:30px' value="<?php echo $_CPA['rivalsa_inps']; ?>"/>%</td></tr>
  <tr><td><input type='checkbox' id='cassa_prev_enabled' <?php if($_CPA['contr_cassa_prev']) echo "checked='true'"; ?>/> Contr. cassa prev.</td>
	  <td><input type='text' class='edit' id='cassa_prev' style='width:30px' value="<?php echo $_CPA['contr_cassa_prev']; ?>"/>%</td>
	  <td>IVA cassa: <select id='cassa_prev_vat'><?php
		for($c=0; $c < count($_VAT_LIST); $c++)
		 echo "<option value='".$_VAT_LIST[$c]['id']."' vattype='".$_VAT_LIST[$c]['vat_type']."'"
			.($_CPA['contr_cassa_prev_vatid'] == $_VAT_LIST[$c]['id'] ? " selected='selected'>" : ">").$_VAT_LIST[$c]['name']."</option>";
		?></select></td></tr>
  <tr><td><input type='checkbox' id='rit_enasarco_enabled' <?php if($_CPA['rit_enasarco']) echo "checked='true'"; ?>/> Rit. Enasarco</td>
	  <td><input type='text' class='edit' id='rit_enasarco' style='width:30px' value="<?php echo $_CPA['rit_enasarco']; ?>"/>%</td>
	  <td>sul <input type='text' class='edit' id='rit_enasarco_percimp' style='width:30px' value="<?php echo $_CPA['rit_enasarco_percimp']; ?>"/>% dell&lsquo;imponibile</td></tr>
  <tr><td><input type='checkbox' id='rit_acconto_enabled' <?php if($_CPA['rit_acconto']) echo "checked='true'"; ?>/> Rit. d&lsquo;acconto</td>
	  <td><input type='text' class='edit' id='rit_acconto' style='width:30px' value="<?php echo $_CPA['rit_acconto']; ?>"/>%</td>
	  <td>sul <input type='text' class='edit' id='rit_acconto_percimp' style='width:30px' value="<?php echo $_CPA['rit_acconto_percimp']; ?>"/>% dell&lsquo;imponibile
	  <input type='radio' name='include_rivinps' <?php if($_CPA['rit_acconto_rivinpsinc']) echo "checked='true'"; ?>/>inclusa Riv. INPS
	  <input type='radio' name='include_rivinps' <?php if(!$_CPA['rit_acconto_rivinpsinc']) echo "checked='true'"; ?>/>solo del netto</td></tr>
 </table>
 <br/>
 <input type='button' class='button-blue' value="Applica le modifiche" onclick="applyContribAndDeduct()"/>
</div>
<!-- EOF - PAGE CONTRIBUTIONS AND DEDUCTIONS -->

<!-- EOF - CONTENTS -->
</td></tr>
</table>
<?php
//-------------------------------------------------------------------------------------------------------------------//
basicapp_contents_end();
?>
<div class="docinfo-footer">&nbsp;</div>

<script>
var LOCKED = <?php echo $_LOCKED ? 'true' : 'false'; ?>;
var IS_VENDOR = <?php echo $_ISVENDOR ? 'true' : 'false'; ?>;
var tb = null;
var expTB = null; // expenses table
var MainMenu = null;
var ACTIVE_TAB_PAGE = "home";
var PayModeMenu = null;
var InternalDocRef = null;
var InternalDocRefMenu = null;
var RubricaEdit = null;
var LastRUBID = 0;
var SHIP_SUBJECT_ID = <?php echo $docInfo['ship_subject_id'] ? $docInfo['ship_subject_id'] : "0"; ?>;
var AGENT_ID = <?php echo $_AGENT_ID ? $_AGENT_ID : "0"; ?>;
var STRICT_AGENT = <?php echo $_REQUEST['strictagent'] ? "true" : "false"; ?>;
var STRICT_AGENT_ID = AGENT_ID;
var DECIMALS = <?php echo $_DECIMALS ? $_DECIMALS : "2"; ?>;
var PLID = <?php echo $_PLID ? $_PLID : "0"; ?>;
var CUSTPLID = PLID;
var CUSTDISTANCE = <?php echo $subjectInfo['distance'] ? $subjectInfo['distance'] : 0; ?>;
var DEF_MARKUP_RATE = <?php echo $_PLINFO ? $_PLINFO['markuprate'] : "0"; ?>;
var DEF_VAT = <?php echo $_DEF_VAT; ?>;
var DEF_VAT_ID = <?php echo $_DEF_VAT_ID ? $_DEF_VAT_ID : '0'; ?>;
var DEF_VAT_TYPE = "<?php echo $_DEF_VAT_TYPE; ?>";
var DEF_VAT_CODE = "<?php echo $_DEF_VAT_CODE; ?>";
var DEF_VAT_NAME = "<?php echo $_DEF_VAT_NAME; ?>";

var SUBJECT_VATID = <?php echo $_SUBJECT_VAT_ID ? $_SUBJECT_VAT_ID : '0'; ?>;
var SUBJECT_VATRATE = "<?php echo $_SUBJECT_VAT_RATE; ?>";
var SUBJECT_VATTYPE = "<?php echo $_SUBJECT_VAT_TYPE; ?>";
var SUBJECT_VATCODE = "<?php echo $_SUBJECT_VAT_CODE; ?>";
var SUBJECT_VATNAME = "<?php echo $_SUBJECT_VAT_NAME; ?>";

var DOC_TAG = "<?php echo $docInfo['tag']; ?>";
var DOC_DIVISION = "<?php echo $docInfo['division']; ?>";
var STATUS_EXTRA = "<?php echo $docInfo['status_extra']; ?>";
var CAT_TAG = "<?php echo $_CAT_TAG; ?>";
var CAT_ID = "<?php echo $_CAT_ID; ?>";
var ROOT_CAT_ID = "<?php echo $_ROOT_CAT_ID; ?>";
var AUTO_CLOSE_DDT = <?php echo $_CFG_OPTIONS['autocloseddt'] ? "true" : "false"; ?>;
var SCHEDULE_EXTENSION_INSTALLED = <?php echo $scheduleExtensionInstalled ? "true" : "false"; ?>;

var NEW_ROWS = new Array();
var UPDATED_ROWS = new Array();
var DELETED_ROWS = new Array();

var NEW_DEPOSITS = new Array();
var UPDATED_DEPOSITS = new Array();
var DELETED_DEPOSITS = new Array();

var NEW_RATES = new Array();
var UPDATED_RATES = new Array();
var DELETED_RATES = new Array();

var RIVALSA_INPS = <?php echo $_CPA['rivalsa_inps'] ? $_CPA['rivalsa_inps'] : 0; ?>;
var RIT_ENASARCO = <?php echo $_CPA['rit_enasarco'] ? $_CPA['rit_enasarco'] : 0; ?>;
var RIT_ENASARCO_PERCIMP = <?php echo $_CPA['rit_enasarco_percimp'] ? $_CPA['rit_enasarco_percimp'] : 0; ?>;
var CASSA_PREV = <?php echo $_CASSA_PREV_PERC ? $_CASSA_PREV_PERC : "0"; ?>;
var CASSA_PREV_VATID = <?php echo $_CASSA_PREV_VATID ? $_CASSA_PREV_VATID : "0"; ?>;
var CASSA_PREV_VAT_TYPE = "<?php echo $_CASSA_PREV_VAT_TYPE; ?>";
var RIT_ACCONTO = <?php echo $_CPA['rit_acconto'] ? $_CPA['rit_acconto'] : 0; ?>;
var RIT_ACCONTO_PERCIMP = <?php echo $_CPA['rit_acconto_percimp'] ? $_CPA['rit_acconto_percimp'] : 0; ?>;
var RIT_ACCONTO_RIVINPSINC = <?php echo $_CPA['rit_acconto_rivinpsinc'] ? $_CPA['rit_acconto_rivinpsinc'] : 0; ?>;

var UNCONDISC_PERC = <?php echo $uncondiscPerc ? $uncondiscPerc : 0; ?>;
var UNCONDISC_AMOUNT = <?php echo $uncondiscAmount ? $uncondiscAmount : 0; ?>;

var attUpld = null;

var UNKNOWN_ELEMENTS = new Array();
var LAST_ELM_TYPE = "";

var SAVED = false;
var DOCISCHANGED = false;
var POSTSAVEACTION = "<?php echo $_REQUEST['postsaveaction']; ?>";
var SAVE_SH = null;
var LOCK_AUTOSAVE_CUSTOMPRICING = <?php echo $_COMMERCIALDOCS_CONFIG['LOCKAUTOSAVECUSTOMPRICING'] ? 'true' : 'false'; ?>;
var CUSTOM_PRICES = new Array();
var VENDOR_PRICES = new Array();

var COPY_ROWS = new Array();
var ON_SAVE = false;
var PMSC_ONPROC = false;	// Payment Mode select change
var PMSC_ONPROC_T = null;	// Payment mode select change timer

var SHOW_CONTRIBANDDEDUCTS = <?php echo $_SHOW_CONTRIBANDDEDUCTS ? 'true' : 'false'; ?>;
var SUBJECT_VATNUMBER = "<?php echo $_SUBJECT_VATNUMBER; ?>";
var SUBJECT_TAXCODE = "<?php echo $_SUBJECT_TAXCODE; ?>";
var SUBJECT_TYPE = <?php echo ($_SUBJECT_TYPE ? $_SUBJECT_TYPE : '0'); ?>;

var INSART_SCHEMA = "<?php echo $_INSART_SCHEMA; ?>";
var INSART_KEYS_STR = "<?php echo implode(',',$_INSART_KEYS); ?>";
var INSART_KEYS = INSART_KEYS_STR.split(',');

var SUBJINFOXL_ENABLED = <?php echo $config['other']['subjinfoxl']['enabled'] ? 'true' : 'false'; ?>;

var VAT_BY_ID = new Array();
<?php
for($c=0; $c < count($_VAT_LIST); $c++)
{
 echo "VAT_BY_ID[".$_VAT_LIST[$c]['id']."] = {code:\"".$_VAT_LIST[$c]['code_str']."\",name:\"".$_VAT_LIST[$c]['name']."\", percentage:".$_VAT_LIST[$c]['percentage'].", type:'".$_VAT_LIST[$c]['vat_type']."'};\n";
}
?>

var DEFAULT_UMIS = new Array();
<?php
if($config && $config['interface'] && $config['interface']['defaultumis'])
{
 reset($config['interface']['defaultumis']);
 while(list($k,$v) = each($config['interface']['defaultumis']))
  echo "DEFAULT_UMIS['".$k."'] = \"".$v."\";\n";
}
?>

function bodyOnLoad()
{
 var div = document.getElementById('doctable').parentNode;
 if(SUBJECT_TYPE == 2)
  div.style.height = div.parentNode.offsetHeight-150;
 else
  div.style.height = div.parentNode.offsetHeight-120;
 div.style.width = div.offsetWidth;

 document.getElementById('doctable').style.display="";

 tb = new GMUTable(document.getElementById('doctable'));
 tb.FieldByName['code'].enableSearch("dynarc search -at `gmart` -fields code_str,name `", "` -limit 20 --order-by `code_str ASC`", "code_str","code_str","items",true,"code_str",codeQueryResults);

 tb.FieldByName['brand'].enableSearch("dynarc item-find -ap brands -ct gmart -field name `","` -limit 20 --order-by `name ASC`", "id","name","items",true);

 tb.FieldByName['vencode'].enableSearch("fastfind products -vencode `", "` -limit 20 --order-by `code_str ASC`", "vencode","vencode","results",true,"vencode",codeQueryResults);

 tb.FieldByName['description'].enableSearch("dynarc search -at `gmart` -fields name,brand,model `", "` -limit 20 --order-by `name ASC`", "name","name","items",true,"name",nameQueryResults);

 tb.FieldByName['vat'].enableSearch("dynarc search -ap `vatrates` -fields code_str,name `", "` -limit 5 --order-by `code_str ASC` -get percentage,vat_type", "percentage","name","items",true,"percentage");

 tb.FieldByName['vendorname'].enableSearch("dynarc search -ap `rubrica` -fields code_str,name `", "` -limit 20 --order-by `name ASC`", "id","name","items",true);


 tb.OnBeforeAddRow = function(r){
	if(!r.getAttribute('type') || (r.getAttribute('type') == "null"))
	 r.setAttribute('type',LAST_ELM_TYPE);
	if(!r.getAttribute('weightunits') || (r.getAttribute('weightunits') == "null"))
	 r.setAttribute('weightunits','kg');
	r.cells[0].innerHTML = "<input type='checkbox'/ >";
	r.cells[0].style.width = "70px";
	r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : DEF_VAT_ID);
	r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : DEF_VAT_TYPE);

   <?php
   reset($_COLUMNS);
   $idx = 1;
   while(list($k,$v) = each($_COLUMNS))
   {
	switch($k)
	{
	 case 'description' : echo "r.cells[".$idx."].innerHTML = \"<span class='graybold doubleline'></span>\";\n"; break;
	 case 'metric' : {
		 echo "r.cells[".$idx."].innerHTML = \"<span class='graybold 13 center'></span>\";\n";
		 $idx++;
		 echo "r.cells[".$idx."].innerHTML = \"<span class='graybold 13 center'></span>\";\n";
		 $idx++;
		 echo "r.cells[".$idx."].innerHTML = \"<span class='graybold 13 center'></span>\";\n";
		 $idx++;
		 echo "r.cells[".$idx."].innerHTML = \"<span class='graybold 13 center'></span>\";\n";
		} break;
	 case 'qty' : case 'qty_sent' : case 'qty_downloaded' : case 'units' : case 'weight' : echo "r.cells[".$idx."].innerHTML = \"<span class='graybold 13 center'></span>\";\n"; break;
	 case 'vat' : echo "r.cells[".$idx."].innerHTML = \"<span class='graybold center'>\"+(SUBJECT_VATID ? SUBJECT_VATRATE : DEF_VAT)+\"%</span>\";\n"; break;
	 case 'vatcode' : echo "r.cells[".$idx."].innerHTML = \"<span class='graybold center'>\"+(SUBJECT_VATID ? SUBJECT_VATCODE : DEF_VAT_CODE)+\"</span>\";\n"; break;
	 case 'vatname' : echo "r.cells[".$idx."].innerHTML = \"<span class='graybold center'>\"+(SUBJECT_VATID ? SUBJECT_VATNAME : DEF_VAT_NAME)+\"</span>\";\n"; break;
	 case 'price' : case 'vatprice' : echo "r.cells[".$idx."].innerHTML = \"<span class='eurogreen'></span>\";\n"; break;
	 case 'coltint' : case 'sizmis' : echo "r.cells[".$idx."].innerHTML = \"<span class='graybold center'></span>\";\n"; break;
	 case 'ritaccapply' : echo "r.cells[".$idx."].innerHTML = \"<input type='checkbox'/\"+\">\";\n"; break;
	 case 'ccpapply' : echo "r.cells[".$idx."].innerHTML = \"<input type='checkbox'/\"+\">\";\n"; break;
	 default : echo "r.cells[".$idx."].innerHTML = \"<span class='graybold'></span>\";\n"; break;
	}
    $idx++;
   }
   ?>
	 NEW_ROWS.push(r);
	}

 tb.OnSelectRow = function(r){
	 document.getElementById('selectionmenu').style.visibility = "visible";
	 //document.getElementById('cutmenubtn').className = "";
	 document.getElementById('copymenubtn').className = "";
	 document.getElementById('deletemenubtn').className = "";

	 var sel = this.GetSelectedRows();
	 if(sel.length == 1)
	 {
	  switch(sel[0].getAttribute('type'))
	  {
	   case 'note' : case 'message' : {
		 document.getElementById('addpredefmsgmenuitem').style.display = "";
		 document.getElementById('addpredefmsgmenusep').style.display = "";
		} break;

	   default : {
		 document.getElementById('addpredefmsgmenuitem').style.display = "none";
		 document.getElementById('addpredefmsgmenusep').style.display = "none";
		} break;
	  }
	 }
	 else
	 {
	  document.getElementById('addpredefmsgmenuitem').style.display = "none";
	  document.getElementById('addpredefmsgmenusep').style.display = "none";
	 }
	}

 tb.OnUnselectRow = function(r){
	 if(!tb.GetSelectedRows().length)
	 {
	  document.getElementById('selectionmenu').style.visibility = "hidden";
	  //document.getElementById('cutmenubtn').className = "disabled";
	  document.getElementById('copymenubtn').className = "disabled";
	  document.getElementById('deletemenubtn').className = "disabled";
	 }
	 else
	 {
	  var sel = this.GetSelectedRows();
	  if(sel.length == 1)
	  {
	   switch(sel[0].getAttribute('type'))
	   {
	    case 'note' : case 'message' : {
		 document.getElementById('addpredefmsgmenuitem').style.display = "";
		 document.getElementById('addpredefmsgmenusep').style.display = "";
		} break;

	    default : {
		 document.getElementById('addpredefmsgmenuitem').style.display = "none";
		 document.getElementById('addpredefmsgmenusep').style.display = "none";
		} break;
	   }
	  }
	  else
	  {
	   document.getElementById('addpredefmsgmenuitem').style.display = "none";
	   document.getElementById('addpredefmsgmenusep').style.display = "none";
	  }
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
	 DOCISCHANGED = true;
	}

 tb.OnBeforeCellEdit = function(r,cell,value){
	 switch(cell.tag)
	 {
	  case 'code' : {
		 switch(r.getAttribute('type'))
		 {
		  case 'article' : this.FieldByName['code'].enableSearch("dynarc search -at `gmart` -fields code_str,name `", "` -limit 20 --order-by `code_str ASC`", "code_str","code_str","items",true,"code_str",codeQueryResults); break;
		  case 'service' : this.FieldByName['code'].enableSearch("dynarc search -at `gserv` -fields code_str,name `", "` -limit 20 --order-by `code_str ASC`", "code_str","code_str","items",true,"code_str",codeQueryResults); break;
		  case 'finalproduct' : this.FieldByName['code'].enableSearch("dynarc search -at `gproducts` -fields code_str,name `", "` -limit 20 --order-by `code_str ASC`", "code_str","code_str","items",true,"code_str",codeQueryResults); break;
		  case 'component' : this.FieldByName['code'].enableSearch("dynarc search -at `gpart` -fields code_str,name `", "` -limit 20 --order-by `code_str ASC`", "code_str","code_str","items",true,"code_str",codeQueryResults); break;
		  case 'material' : this.FieldByName['code'].enableSearch("dynarc search -at `gmaterial` -fields code_str,name `", "` -limit 20 --order-by `code_str ASC`", "code_str","code_str","items",true,"code_str",codeQueryResults); break;
		  case 'labor' : this.FieldByName['code'].enableSearch("dynarc search -at `glabor` -fields code_str,name `", "` -limit 20 --order-by `code_str ASC`", "code_str","code_str","items",true,"code_str",codeQueryResults); break;
		  case 'book' : this.FieldByName['code'].enableSearch("dynarc search -at `gbook` -fields code_str,name `", "` -limit 20 --order-by `code_str ASC`", "code_str","code_str","items",true,"code_str",codeQueryResults); break;
		  case 'supply' : this.FieldByName['code'].enableSearch("dynarc search -at `gsupplies` -fields code_str,name `", "` -limit 20 --order-by `code_str ASC`", "code_str","code_str","items",true,"code_str",codeQueryResults); break;
		  default : this.FieldByName['code'].disableSearch(); break;
		 }
		} break;

	  case 'vencode' : {
		 switch(r.getAttribute('type'))
		 {
		  case 'article' : this.FieldByName['vencode'].enableSearch("fastfind products -vencode `", "` -limit 20 --order-by `code_str ASC`", "vencode","vencode","results",true,"vencode",codeQueryResults); break;
			/* TODO: da fare component, material, supply */

		  default : this.FieldByName['vencode'].disableSearch(); break;
		 }
		} break;

	  case 'description' : {
		 if(!r.cell['code'].getValue())
		 {
		  switch(r.getAttribute('type'))
		  {
		   case 'article' : this.FieldByName['description'].enableSearch("dynarc search -at `gmart` -fields name,brand,model `", "` -limit 20 --order-by `name ASC`", "name","name","items",true,"name",nameQueryResults); break;
		   case 'service' : this.FieldByName['description'].enableSearch("dynarc search -at `gserv` -fields name `", "` -limit 20 --order-by `name ASC`", "name","name","items",true,"name",nameQueryResults); break;
		   case 'finalproduct' : this.FieldByName['description'].enableSearch("dynarc search -at `gproducts` -fields name `", "` -limit 20 --order-by `name ASC`", "name","name","items",true,"name",nameQueryResults); break;
		   case 'component' : this.FieldByName['description'].enableSearch("dynarc search -at `gpart` -fields name,brand,model `", "` -limit 20 --order-by `name ASC`", "name","name","items",true,"name",nameQueryResults); break;
		   case 'material' : this.FieldByName['description'].enableSearch("dynarc search -at `gmaterial` -fields name,brand,model `", "` -limit 20 --order-by `name ASC`", "name","name","items",true,"name",nameQueryResults); break;
		   case 'labor' : this.FieldByName['description'].enableSearch("dynarc search -at `glabor` -fields name `", "` -limit 20 --order-by `name ASC`", "name","name","items",true,"name",nameQueryResults); break;
		   case 'book' : this.FieldByName['description'].enableSearch("dynarc search -at `gbook` -fields name `", "` -limit 20 --order-by `name ASC`", "name","name","items",true,"name",nameQueryResults); break;
		   case 'supply' : this.FieldByName['description'].enableSearch("dynarc search -at `gsupplies` -fields name `", "` -limit 20 --order-by `name ASC`", "name","name","items",true,"name",nameQueryResults); break;
		   default : this.FieldByName['description'].disableSearch(); break;
		  }
		 }
		 else
		  this.FieldByName['description'].disableSearch();
		} break;

	  case 'coltint' : case 'sizmis' : {
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
		 var refAP = r.getAttribute('refap');
		 var refID = r.getAttribute('refid');
		 var refVendorID = r.getAttribute('vendorid');
		 if(!r.itemInfo && refAP && refID)
		 {
		  var oThis = this;
		  var sh = new GShell();
		  sh.OnOutput = function(o,a){
		  	// variants //
			r.itemInfo = a;
		  	if(a && a['variants'])
		  	{
		  	 // colori e tinte //
		  	 var options = new Array();
		  	 if(a['variants']['colors'])
		  	 {
			  var arr = new Array();
			  for(var c=0; c < a['variants']['colors'].length; c++)
			   arr.push(a['variants']['colors'][c]['name']);
			  options.push(arr);
		  	 }
		   	 if(a['variants']['tint'])
		   	 {
			  var arr = new Array();
			  for(var c=0; c < a['variants']['tint'].length; c++)
			   arr.push(a['variants']['tint'][c]['name']);
			  options.push(arr);
		   	 }
		   	 r.cell['coltint'].setOptions(options);

		   	 // taglie, misure e altro //
		   	 var options = new Array();
		   	 if(a['variants']['sizes'])
		   	 {
			  var arr = new Array();
			  for(var c=0; c < a['variants']['sizes'].length; c++)
			   arr.push(a['variants']['sizes'][c]['name']);
			  options.push(arr);
		   	 }
		   	 if(a['variants']['dim'])
		   	 {
			  var arr = new Array();
			  for(var c=0; c < a['variants']['dim'].length; c++)
			   arr.push(a['variants']['dim'][c]['name']);
			  options.push(arr);
		   	 }
		   	 if(a['variants']['other'])
		   	 {
			  var arr = new Array();
			  for(var c=0; c < a['variants']['other'].length; c++)
			   arr.push(a['variants']['other'][c]['name']);
			  options.push(arr);
		   	 }
		   	 r.cell['sizmis'].setOptions(options);
		  	}

			// show popup menu
			oThis.editCell(cell);
		   }

		  sh.sendCommand("commercialdocs getfullinfo -ap `"+refAP+"` -id `"+refID+"` -subjectid `"+subjectId+"` -pricelistid `"+CUSTPLID+"` -vendorid '"+refVendorID+"' --get-variants -qty '"+r.cell['qty'].getValue()+"'");
		 }
		} break;

	 }

	}

 tb.OnCellEdit = function(r,cell,value,data){
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
	
	 DOCISCHANGED = true;

	 switch(cell.tag)
	 {
	  case 'code' : {
		 if(cell.colSpan > 1)
		 {
		  // è una riga di nota
		  var spanlist = cell.getElementsByTagName('SPAN');
		  if(spanlist[1])
		   spanlist[1].innerHTML = value;
		  break;
		 }


		 var sh = new GShell();
		 sh.OnError = function(msg,errcode){
			 if(UNKNOWN_ELEMENTS.indexOf(r) < 0)
			  UNKNOWN_ELEMENTS.push(r);
			}

		 sh.OnOutput = function(o,a){
		  if(!a)
		  {
		   r.setAttribute('refid','');
		   r.setAttribute('weightunits','kg');
		   return;
		  }
		  r.itemInfo = a;
		  r.setAttribute('weightunits',a['weightunits']);
		  if(IS_VENDOR)	//IF IS VENDOR
		  {
		   if(a['vendor_id'] == subjectId)
		   {
		    r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']);
		    r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : a['vendor_vattype']);
		   }
		   else
		   {
		   r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : a['vatid']);
		   r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : a['vattype']);
		   }
		  }
		  else    // IF IS CUSTOMER
		  {
		   r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : a['vatid']);
		   r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : a['vattype']);
		  }
		  r.setAttribute('vendorid',a['vendor_id']);
		  r.cell['code'].setValue(a['variant_code'] ? a['variant_code'] : a['code_str']);
		  r.cell['vencode'].setValue(a['vencode']);
		  r.cell['mancode'].setValue(a['manufacturer_code']);
		  r.setAttribute('refid',a['id']);
		  r.setAttribute('refap',a['tb_prefix']);

		  r.cell['brand'].setValue(a['brand'] ? a['brand'] : "");
		  r.cell['brand'].setAttribute('refid',a['brand_id']);

		  if(r.getAttribute('type') == 'article')
		   r.cell['description'].setValue(getInsArtDescription(a));
		  else
		   r.cell['description'].setValue(a['name']);

		  r.cell['qty'].setValue(1);
		  r.cell['weight'].setValue(a['weight'] ? a['weight']+" "+a['weightunits'] : "");

		  if(!IS_VENDOR && a['custompricing'])
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

		  if(IS_VENDOR)
		  {
		   if(a['vendor_id'] == subjectId)
		   {
		    r.cell['vendorprice'].setValue(a['vendor_price']);
		    r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
		   }
		   else
		   {
		    r.cell['vendorprice'].setValue(0);
		    r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(0,4);
		   }
		  }
		  else
		  {
		   r.cell['vendorprice'].setValue(a['vendor_price']);
		   r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
		  }
		  
		  r.cell['unitprice'].setValue(a['variant_type'] ? a['variant_finalprice'] : a['finalprice']);
		  r.cell['unitprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['variant_type'] ? a['variant_finalprice'] : a['finalprice'],4);
		  r.cell['plbaseprice'].setValue(a['baseprice']);
		  r.cell['plbaseprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['baseprice'],4);
		  r.cell['plmrate'].setValue(a['pricelist_'+CUSTPLID+'_mrate'] ? a['pricelist_'+CUSTPLID+'_mrate'] : 0);
		  r.cell['pldiscperc'].setValue(a['pricelist_'+CUSTPLID+'_discount'] ? a['pricelist_'+CUSTPLID+'_discount'] : 0);

		  if(IS_VENDOR)
		  {
		   if(a['vendor_id'] == subjectId)
		   {
		    r.cell['vat'].setValue(SUBJECT_VATID ? SUBJECT_VATRATE : a['vendor_vatrate']);
			if(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']])
			{
			 r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']]['code']);
			 r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']]['name']);
			}
		    r.cell['price'].setValue(a['vendor_price']);
		    r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
		   }
		   else
		   {
		    r.cell['vat'].setValue(a['vat']);
			if(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']])
			{
			 r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['code']);
			 r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['name']);
			}
		    r.cell['price'].setValue(0);
		    r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(0,4);
		   }
		  }
		  else
		  {
		   r.cell['vat'].setValue(SUBJECT_VATID ? SUBJECT_VATRATE : a['vat']);
			if(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']])
			{
			 r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['code']);
			 r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['name']);
			}
		   r.cell['price'].setValue(a['variant_type'] ? a['variant_finalprice'] : a['finalprice']);
		   r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['variant_type'] ? a['variant_finalprice'] : a['finalprice'],4);
		  }
		  r.cell['pricelist'].setValue(a['pricelist_name']);
		  r.cell['pricelist'].setAttribute("pricelistid",a['pricelist_id']);
		  r.cell['vendorname'].setValue(a['vendor_name']);

		  // variants //
		  if(a && a['variants'])
		  {
		   // colori e tinte //
		   var options = new Array();
		   if(a['variants']['colors'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['colors'].length; c++)
			 arr.push(a['variants']['colors'][c]['name']);
			options.push(arr);
		   }
		   if(a['variants']['tint'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['tint'].length; c++)
			 arr.push(a['variants']['tint'][c]['name']);
			options.push(arr);
		   }
		   r.cell['coltint'].setOptions(options);

		   // taglie, misure e altro //
		   var options = new Array();
		   if(a['variants']['sizes'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['sizes'].length; c++)
			 arr.push(a['variants']['sizes'][c]['name']);
			options.push(arr);
		   }
		   if(a['variants']['dim'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['dim'].length; c++)
			 arr.push(a['variants']['dim'][c]['name']);
			options.push(arr);
		   }
		   if(a['variants']['other'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['other'].length; c++)
			 arr.push(a['variants']['other'][c]['name']);
			options.push(arr);
		   }
		   r.cell['sizmis'].setOptions(options);

		   switch(a['variant_type'])
		   {
			case 'color' : case 'tint' : 				r.cell['coltint'].setValue(a['variant_name']); break;
			case 'size' : case 'dim' : case 'other' : 	r.cell['sizmis'].setValue(a['variant_name']); break;
		   }

		  }

		  updateTotals(r);
		 }


		 if(data)
		  sh.sendCommand("commercialdocs getfullinfo -ap `"+data['tb_prefix']+"` -id `"+data['id']+"` -subjectid `"+subjectId+"` -pricelistid `"+CUSTPLID+"` --get-variants -qty '"+r.cell['qty'].getValue()+"'");
		 else
		 {
		  var _ap = "";
		  switch(r.getAttribute('type'))
		  {
		   case 'article' : _ap = "gmart"; break;
		   case 'service' : _ap = "gserv"; break;
		   case 'finalproduct' : _ap = "gproducts"; break;
		   case 'component' : _ap = "gpart"; break;
		   case 'material' : _ap = "gmaterial"; break;
		   case 'labor' : _ap = "glabor"; break;
		   case 'gbook' : _ap = "gbook"; break;
		   case 'supply' : _ap = "gsupplies"; break;
		   default : _ap = ""; break;
		  }
		  if(_ap != "")
		   sh.sendCommand("commercialdocs getfullinfo -type `"+_ap+"` -code `"+value+"` -subjectid `"+subjectId+"` -pricelistid `"+CUSTPLID+"` --get-variants -qty '"+r.cell['qty'].getValue()+"'");
		 }
		} break;

	  case 'metriclength' : case 'metricwidth' : case 'metrichw' : case 'metriceqp' : {
		 var metricEqP = parseFloat(r.cell['metriceqp'].getValue());
		 var metricLength = parseFloat(r.cell['metriclength'].getValue());
		 var metricWidth = parseFloat(r.cell['metricwidth'].getValue());
		 var metricHW = parseFloat(r.cell['metrichw'].getValue());

		 var qty = (metricEqP > 0) ? metricEqP : 1;
		 if(metricLength)	qty = qty*metricLength;
		 if(metricWidth)	qty = qty*metricWidth;
		 if(metricHW)		qty = qty*metricHW;

		 qty = roundup(qty,2);

		 r.cell['qty'].setValue(qty);
		 updatePriceByQty(r,qty);
		} break;

	  case 'qty' : updatePriceByQty(r,value); break;

	  case 'weight' : {
		 weightUnits = data ? data['weightunits'] : r.getAttribute('weightunits');
		 if(value)
		  cell.setValue(parseFloat(value)+" "+weightUnits);
		 else
		  cell.setValue("0 "+weightUnits);
		 updateTotals(r);
		} break;

	  case 'brand' : {
		 if(value && data)
		  cell.setAttribute('refid',data['id']);
		 else
		  cell.setAttribute('refid',0);
		} break;

	  case 'vencode' : {
		 var sh = new GShell();
		 sh.OnError = function(msg,errcode){
			 if(IS_VENDOR && r.itemInfo)
			 {
			  r.vendorpriceChanged=true;
			  return;
			 }
			 if(UNKNOWN_ELEMENTS.indexOf(r) < 0)
			  UNKNOWN_ELEMENTS.push(r);
			}

		 sh.OnOutput = function(o,a){
		  if(!a)
		  {
		   r.setAttribute('refid','');
		   r.setAttribute('weightunits','kg');
		   return;
		  }
		  r.itemInfo = a;
		  r.setAttribute('weightunits',a['weightunits']);
		  if(a['vendor_id'] == subjectId)
		  {
		   r.setAttribute('vatid', SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']);
		   r.setAttribute('vattype', SUBJECT_VATID ? SUBJECT_VATTYPE : a['vendor_vattype']);
		  }
		  else
		  {
		   r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : a['vatid']);
		   r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : a['vattype']);
		  }
		  r.setAttribute('vendorid',a['vendor_id']);
		  r.cell['code'].setValue(a['code_str']);
		  r.cell['vencode'].setValue(a['vencode']);
		  r.cell['mancode'].setValue(a['manufacturer_code']);
		  r.setAttribute('refid',a['id']);
		  r.setAttribute('refap',a['tb_prefix']);

		  r.cell['brand'].setValue(a['brand'] ? a['brand'] : "");
		  r.cell['brand'].setAttribute('refid',a['brand_id']);

		  if(r.getAttribute('type') == 'article')
		   r.cell['description'].setValue(getInsArtDescription(a));
		  else
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

		  if(IS_VENDOR)
		  {
		   if(a['vendor_id'] == subjectId)
		   {
		    r.cell['vendorprice'].setValue(a['vendor_price']);
		    r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
		   }
		   else
		   {
		    r.cell['vendorprice'].setValue(0);
		    r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(0,4);
		   }
		  }
		  else
		  {
		   r.cell['vendorprice'].setValue(a['vendor_price']);
		   r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
		  }
		  r.cell['unitprice'].setValue(a['finalprice']);
		  r.cell['unitprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['finalprice'],4);
		  r.cell['plbaseprice'].setValue(a['baseprice']);
		  r.cell['plbaseprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['baseprice'],4);
		  r.cell['plmrate'].setValue(a['pricelist_'+CUSTPLID+'_mrate'] ? a['pricelist_'+CUSTPLID+'_mrate'] : 0);
		  r.cell['pldiscperc'].setValue(a['pricelist_'+CUSTPLID+'_discount'] ? a['pricelist_'+CUSTPLID+'_discount'] : 0);

		  if(a['vendor_id'] == subjectId)
		  {
		   r.cell['vat'].setValue(SUBJECT_VATID ? SUBJECT_VATRATE : a['vendor_vatrate']);
			if(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']])
			{
			 r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']]['code']);
			 r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']]['name']);
			}
		   r.cell['price'].setValue(a['vendor_price']);
		   r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
		  }
		  else
		  {
		   r.cell['vat'].setValue(SUBJECT_VATID ? SUBJECT_VATRATE : a['vat']);
			if(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']])
			{
			 r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['code']);
			 r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['name']);
			}

		   r.cell['price'].setValue(a['finalprice']);
		   r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['finalprice'],4);
		  }
		  r.cell['pricelist'].setValue(a['pricelist_name']);
		  r.cell['pricelist'].setAttribute("pricelistid",a['pricelist_id']);
		  r.cell['vendorname'].setValue(a['vendor_name']);

		  // variants //
		  if(a && a['variants'])
		  {
		   // colori e tinte //
		   var options = new Array();
		   if(a['variants']['colors'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['colors'].length; c++)
			 arr.push(a['variants']['colors'][c]['name']);
			options.push(arr);
		   }
		   if(a['variants']['tint'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['tint'].length; c++)
			 arr.push(a['variants']['tint'][c]['name']);
			options.push(arr);
		   }
		   r.cell['coltint'].setOptions(options);

		   // taglie, misure e altro //
		   var options = new Array();
		   if(a['variants']['sizes'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['sizes'].length; c++)
			 arr.push(a['variants']['sizes'][c]['name']);
			options.push(arr);
		   }
		   if(a['variants']['dim'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['dim'].length; c++)
			 arr.push(a['variants']['dim'][c]['name']);
			options.push(arr);
		   }
		   if(a['variants']['other'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['other'].length; c++)
			 arr.push(a['variants']['other'][c]['name']);
			options.push(arr);
		   }
		   r.cell['sizmis'].setOptions(options);
		  }

		  updateTotals(r);
		 }


		 if(data)
		  sh.sendCommand("commercialdocs getfullinfo -ap `"+data['ap']+"` -id `"+data['id']+"` -subjectid `"+subjectId+"` -pricelistid `"+CUSTPLID+"` -vendorid '"+data['vendor_id']+"' --get-variants -qty '"+r.cell['qty'].getValue()+"'");
		 else
		 {
		  var _ap = "";
		  switch(r.getAttribute('type'))
		  {
		   case 'article' : _ap = "gmart"; break;
		   case 'service' : _ap = "gserv"; break;
		   case 'finalproduct' : _ap = "gproducts"; break;
		   case 'component' : _ap = "gpart"; break;
		   case 'material' : _ap = "gmaterial"; break;
		   case 'labor' : _ap = "glabor"; break;
		   case 'book' : _ap = "gbook"; break;
		   case 'supply' : _ap = "gsupplies"; break;
		   default : _ap = ""; break;
		  }
		  if(_ap != "")
		   sh.sendCommand("commercialdocs getfullinfo -type `"+_ap+"` -vencode `"+value+"` -subjectid `"+subjectId+"` -pricelistid `"+CUSTPLID+"` --get-variants -qty '"+r.cell['qty'].getValue()+"'");
		 }
		} break;

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
		   r.setAttribute('weightunits','kg');
		   return;
		  }
		  r.itemInfo = a;
		  r.setAttribute('weightunits',a['weightunits']);
		  if(a['vendor_id'] == subjectId)
		  {
		   r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']);
		   r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : a['vendor_vattype']);
		  }
		  else
		  {
		   r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : a['vatid']);
		   r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : a['vattype']);
		  }
		  r.setAttribute('vendorid',a['vendor_id']);
		  r.cell['code'].setValue(a['code_str']);
		  r.cell['vencode'].setValue(a['vencode']);
		  r.cell['mancode'].setValue(a['manufacturer_code']);
		  r.setAttribute('refid',a['id']);
		  r.setAttribute('refap',a['tb_prefix']);

		  r.cell['brand'].setValue(a['brand'] ? a['brand'] : "");
		  r.cell['brand'].setAttribute('refid',a['brand_id']);

		  if(r.getAttribute('type') == 'article')
		   r.cell['description'].setValue(getInsArtDescription(a));
		  else
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

		  if(IS_VENDOR)
		  {
		   if(a['vendor_id'] == subjectId)
		   {
		    r.cell['vendorprice'].setValue(a['vendor_price']);
		    r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
		   }
		   else
		   {
		    r.cell['vendorprice'].setValue(0);
		    r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(0,4);
		   }
		  }
		  else
		  {
		   r.cell['vendorprice'].setValue(a['vendor_price']);
		   r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
		  }
		  r.cell['unitprice'].setValue(a['finalprice']);
		  r.cell['unitprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['finalprice'],4);
		  r.cell['plbaseprice'].setValue(a['baseprice']);
		  r.cell['plbaseprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['baseprice'],4);
		  r.cell['plmrate'].setValue(a['pricelist_'+CUSTPLID+'_mrate'] ? a['pricelist_'+CUSTPLID+'_mrate'] : 0);
		  r.cell['pldiscperc'].setValue(a['pricelist_'+CUSTPLID+'_discount'] ? a['pricelist_'+CUSTPLID+'_discount'] : 0);

		  if(a['vendor_id'] == subjectId)
		  {
		   r.cell['vat'].setValue(SUBJECT_VATID ? SUBJECT_VATRATE : a['vendor_vatrate']);
			if(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']])
			{
			 r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']]['code']);
			 r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']]['name']);
			}

		   r.cell['price'].setValue(a['vendor_price']);
		   r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
		  }
		  else
		  {
		   r.cell['vat'].setValue(SUBJECT_VATID ? SUBJECT_VATRATE : a['vat']);
			if(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']])
			{
			 r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['code']);
			 r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['name']);
			}

		   r.cell['price'].setValue(a['finalprice']);
		   r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['finalprice'],4);
		  }
		  r.cell['pricelist'].setValue(a['pricelist_name']);
		  r.cell['pricelist'].setAttribute("pricelistid",a['pricelist_id']);
		  r.cell['vendorname'].setValue(a['vendor_name']);

		  // variants //
		  if(a && a['variants'])
		  {
		   // colori e tinte //
		   var options = new Array();
		   if(a['variants']['colors'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['colors'].length; c++)
			 arr.push(a['variants']['colors'][c]['name']);
			options.push(arr);
		   }
		   if(a['variants']['tint'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['tint'].length; c++)
			 arr.push(a['variants']['tint'][c]['name']);
			options.push(arr);
		   }
		   r.cell['coltint'].setOptions(options);

		   // taglie, misure e altro //
		   var options = new Array();
		   if(a['variants']['sizes'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['sizes'].length; c++)
			 arr.push(a['variants']['sizes'][c]['name']);
			options.push(arr);
		   }
		   if(a['variants']['dim'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['dim'].length; c++)
			 arr.push(a['variants']['dim'][c]['name']);
			options.push(arr);
		   }
		   if(a['variants']['other'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['other'].length; c++)
			 arr.push(a['variants']['other'][c]['name']);
			options.push(arr);
		   }
		   r.cell['sizmis'].setOptions(options);
		  }

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
		  sh.sendCommand("commercialdocs getfullinfo -ap `"+data['tb_prefix']+"` -id `"+data['id']+"` -subjectid `"+subjectId+"` -pricelistid `"+CUSTPLID+"` --get-variants -qty '"+r.cell['qty'].getValue()+"'");
		 else
		 {
		  var _ap = "";
		  switch(r.getAttribute('type'))
		  {
		   case 'article' : _ap = "gmart"; break;
		   case 'finalproduct' : _ap = "gproducts"; break;
		   case 'component' : _ap = "gpart"; break;
		   case 'material' : _ap = "gmaterial"; break;
		   case 'labor' : _ap = "glabor"; break;
		   case 'book' : _ap = "gbook"; break;
		   case 'service' : _ap = "gserv"; break;
		   case 'supply' : _ap = "gsupplies"; break;
		   default : _ap = ""; break;
		  }
		  if(_ap != "")
		   sh.sendCommand("commercialdocs getfullinfo -type `"+_ap+"` -name `"+value+"` -subjectid `"+subjectId+"` -pricelistid `"+CUSTPLID+"` --get-variants -qty '"+r.cell['qty'].getValue()+"'");
		 }
		} break;

	  case 'vendorprice' : {
		 if(r.getAttribute('refid'))
		  r.vendorpriceChanged=true;
		 cell.getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(parseCurrency(value),4);
		 updateTotals(r); 
		} break;

	  case 'vendorname' : {
		 if(data && value)
		  r.setAttribute('vendorid',data['id']);
		 else
		  r.setAttribute('vendorid',0);
		} break;

	  case 'unitprice' : {
		 if(r.getAttribute('refid'))
		  r.unitpriceChanged=true;
		 cell.getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(parseCurrency(value),4);
		 updateTotals(r); 
		} break;

	  case 'plmrate' : {
		 var baseprice = parseFloat(r.cell['plbaseprice'].getValue());
		 if(!baseprice)
		  return alert("Impossibile effettuare il ricarico perchè il prezzo base è a zero. Cliccare sul menu 'Visualizza' per mostrare la colonna PR.BASE in modo da poterlo impostare.");
		 var mrate = parseFloat(value);
		 var disc = parseFloat(r.cell['pldiscperc'].getValue());
		 if(baseprice && mrate)
		  baseprice = baseprice+((baseprice/100)*mrate);
		 if(baseprice && disc)
		  baseprice = baseprice-((baseprice/100)*disc);

		 r.cell['unitprice'].setValue(formatCurrency(baseprice));
		 r.cell['unitprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(baseprice,4);
		 updateTotals(r); 
		} break;

	  case 'plbaseprice' : {
		 var baseprice = parseFloat(value);
		 var mrate = parseFloat(r.cell['plmrate'].getValue());
		 var disc = parseFloat(r.cell['pldiscperc'].getValue());
		 if(baseprice && mrate)
		  baseprice = baseprice+((baseprice/100)*mrate);
		 if(baseprice && disc)
		  baseprice = baseprice-((baseprice/100)*disc);

		 r.cell['unitprice'].setValue(formatCurrency(baseprice));
		 r.cell['unitprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(baseprice,4);
		 updateTotals(r); 
		} break;

	  case 'discount' : {
		 if(!value || (value == "") || (value == "0"))
		 {
		  cell.setValue(0,0);
		  cell.getElementsByTagName('SPAN')[0].innerHTML = "";
		 }
		 else if(value.indexOf("%") < 0)
		 {
		  value = value.replace(/\u20ac/g, "");
		  value = value.replace(". ","");
		  cell.getElementsByTagName('SPAN')[0].innerHTML = "&euro;. "+formatCurrency(parseCurrency(value),DECIMALS);
		 }
		 if(r.getAttribute('refid'))
		  r.discountChanged=true;
		 updateTotals(r);
		} break;

	  case 'discount2' : {
		 if(!value || (value == ""))
		 {
		  cell.setValue(0,0);
		  cell.getElementsByTagName('SPAN')[0].innerHTML = "";
		 }
		 else if(value)
		 {
		  /* check scontistica 100% */
		  var rv = parseFloat(value);
		  if(rv >= 100)
		  {
		   value = cell.oldValue;
		   cell.setValue(cell.oldValue);
		   alert("Nella seconda scontistica non puoi inserire uno sconto maggiore o uguale al 100%");
		  }
		 }
		 if(r.getAttribute('refid'))
		  r.discountChanged=true;
		 updateTotals(r);
		} break;

	  case 'discount3' : {
		 if(!value || (value == ""))
		 {
		  cell.setValue(0,0);
		  cell.getElementsByTagName('SPAN')[0].innerHTML = "";
		 }
		 else if(value)
		 {
		  /* check scontistica 100% */
		  var rv = parseFloat(value);
		  if(rv >= 100)
		  {
		   value = cell.oldValue;
		   cell.setValue(cell.oldValue);
		   alert("Nella terza scontistica non puoi inserire uno sconto maggiore o uguale al 100%");
		  }
		 }
		 if(r.getAttribute('refid'))
		  r.discountChanged=true;
		 updateTotals(r);
		} break;

	  case 'vat' : {
		 if(data)
		 {
		  r.setAttribute('vatid',data['id']);
		  r.setAttribute('vattype',data['vat_type']);
		  r.cell['vatcode'].setValue(data['code_str']);
		  r.cell['vatname'].setValue(data['name']);
		  updateTotals(r);
		 }
		 else
		 {
		  var sh = new GShell();
		  sh.OnOutput = function(o,a){
			 if(!a)
			 {
		  	  r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : DEF_VAT_ID);
		  	  r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : DEF_VAT_TYPE);
		 	  r.cell['vatcode'].setValue(SUBJECT_VATID ? SUBJECT_VATCODE : DEF_VAT_CODE);
		  	  r.cell['vatname'].setValue(SUBJECT_VATID ? SUBJECT_VATNAME : DEF_VAT_NAME);
			  updateTotals(r);
			  return;
			 }
			 r.setAttribute('vatid',a['items'][0]['id']);
			 r.setAttribute('vattype',a['items'][0]['vat_type']);
			 r.cell['vat'].setValue(a['items'][0]['percentage']);
		  	 r.cell['vatcode'].setValue(a['items'][0]['code_str']);
		  	 r.cell['vatname'].setValue(a['items'][0]['name']);
			 updateTotals(r);
			}
		  sh.sendCommand("dynarc search -ap `vatrates` -fields code_str,name `"+value+"` -limit 1 --order-by `code_str ASC` -get percentage,vat_type");
		 }
		} break;

	  case 'price' : {
		 var unitPrice = 0;
		 var qty = parseFloat(r.cell['qty'].getValue());
		 if(!qty)
		  alert("Impossibile effettuare il calcolo con quantità a zero"); 
		 var disc1 = r.cell['discount'].getValue();
		 var disc2 = r.cell['discount2'].getValue();
		 var disc3 = r.cell['discount3'].getValue();
		 if(disc2) disc2 = parseFloat(disc2);
		 if(disc3) disc3 = parseFloat(disc3);

		 var amount = parseCurrency(value);
		 if(amount && qty)
		 {
		  if(disc3)
		   amount = (amount/(100-disc3))*100;
		  if(disc2)
		   amount = (amount/(100-disc2))*100;
		  if(disc1)
		  {
		   if(disc1.indexOf("%") < 0)
		   {
			disc1 = disc1.replace(/\u20ac/g, "");
			disc1 = disc1.replace(". ","");
			disc1 = parseCurrency(disc1);
			amount = amount+disc1;			
		   }
		   else
		   {
			disc1 = parseFloat(disc1);
			amount = (amount/(100-disc1))*100;
		   }
		  }
		  if(amount)
		   unitPrice = amount/qty;
		 }
		 r.cell['unitprice'].setValue(formatCurrency(unitPrice),unitPrice);
		 if(r.getAttribute('refid'))
		  r.unitpriceChanged=true;
		 r.cell['unitprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(unitPrice,4);
		 cell.getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(unitPrice,4);
		 updateTotals(r); 
		} break;

	  case 'vatprice' : {
		 var unitPrice = 0;
		 var qty = parseFloat(r.cell['qty'].getValue());
		 if(!qty)
		  alert("Impossibile effettuare il calcolo con quantità a zero"); 
		 var vat = parseFloat(r.cell['vat'].getValue());

		 var disc1 = r.cell['discount'].getValue();
		 var disc2 = r.cell['discount2'].getValue();
		 var disc3 = r.cell['discount3'].getValue();
		 if(disc2) disc2 = parseFloat(disc2);
		 if(disc3) disc3 = parseFloat(disc3);

		 var amount = parseCurrency(value);
		 if(vat)
		  amount = (amount/(100+vat))*100;
		 if(amount && qty)
		 {
		  if(disc3)
		   amount = (amount/(100-disc3))*100;
		  if(disc2)
		   amount = (amount/(100-disc2))*100;
		  if(disc1)
		  {
		   if(disc1.indexOf("%") < 0)
		   {
			disc1 = disc1.replace(/\u20ac/g, "");
			disc1 = disc1.replace(". ","");
			disc1 = parseCurrency(disc1);
			amount = amount+disc1;			
		   }
		   else
		   {
			disc1 = parseFloat(disc1);
			amount = (amount/(100-disc1))*100;
		   }
		  }
		  if(amount)
		   unitPrice = amount/qty;
		 }
		 r.cell['unitprice'].setValue(formatCurrency(unitPrice),unitPrice);
		 if(r.getAttribute('refid'))
		  r.unitpriceChanged=true;
		 r.cell['unitprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(unitPrice,4);
		 cell.getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(unitPrice,4);
		 updateTotals(r); 
		} break;

	  case 'coltint' : {
		 var sh = new GShell();
		 sh.OnOutput = function(o,a){
			 if(!a)
			 {
			  if(r.itemInfo)
			  {
			   r.cell['code'].setValue(r.itemInfo['code_str']);
			  }
			 }
			 else
			 {
			  if(a['code'])
			   r.cell['code'].setValue(a['code']);
			 }
			 updatePriceByQty(r, r.cell['qty'].getValue());
			}

		 sh.sendCommand("dynarc ext-find -ap '"+r.getAttribute('refap')+"' -itemid '"+r.getAttribute('refid')+"' -ext varcodes -types color,tint -name `"+value+"` -plid '"+CUSTPLID+"'");
		} break;

	  case 'sizmis' : {
		 var sh = new GShell();
		 sh.OnOutput = function(o,a){
			 if(!a)
			 {
			  if(r.itemInfo)
			  {
			   r.cell['code'].setValue(r.itemInfo['code_str']);
			  }
			 }
			 else
			 {
			  if(a['code'])
			   r.cell['code'].setValue(a['code']);
			 }
			 updatePriceByQty(r, r.cell['qty'].getValue());
			}

		 sh.sendCommand("dynarc ext-find -ap '"+r.getAttribute('refap')+"' -itemid '"+r.getAttribute('refid')+"' -ext varcodes -types size,dim,other -name `"+value+"` -plid '"+CUSTPLID+"'");
		} break;

	  case 'ritaccapply' : case 'ccpapply' : updateTotals(r); break;

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

	 // verifica icona
	 switch(r.getAttribute('type'))
	 {
	  case 'article' : {
		 // create icon
		 var icon = r.cells[0].getElementsByTagName('IMG')[0];
		 if(!icon)
		 {
		  var icon = document.createElement("IMG");
		  icon.src = ABSOLUTE_URL+"GCommercialDocs/img/article-mini.png";
		  icon.title = "Clicca per vedere la scheda di questo articolo";
		  icon.onclick = function(){showProduct(this);}
		  icon.style.marginLeft = "5px";
		  r.cells[0].appendChild(icon);
		 }
		} break;

	  case 'finalproduct' : {
		 // create icon
		 var icon = r.cells[0].getElementsByTagName('IMG')[0];
		 if(!icon)
		 {
		  var icon = document.createElement("IMG");
		  icon.src = ABSOLUTE_URL+"GCommercialDocs/img/finalproduct-mini.png";
		  icon.title = "Clicca per vedere la scheda di questo prodotto";
		  icon.onclick = function(){showFinalProduct(this);}
		  icon.style.marginLeft = "5px";
		  r.cells[0].appendChild(icon);
		 }
		} break;

	  case 'component' : {
		 // create icon
		 var icon = r.cells[0].getElementsByTagName('IMG')[0];
		 if(!icon)
		 {
		  var icon = document.createElement("IMG");
		  icon.src = ABSOLUTE_URL+"GCommercialDocs/img/component-mini.png";
		  icon.title = "Clicca per vedere la scheda di questo componente";
		  icon.onclick = function(){showComponent(this);}
		  icon.style.marginLeft = "5px";
		  r.cells[0].appendChild(icon);
		 }
		} break;

	  case 'material' : {
		 // create icon
		 var icon = r.cells[0].getElementsByTagName('IMG')[0];
		 if(!icon)
		 {
		  var icon = document.createElement("IMG");
		  icon.src = ABSOLUTE_URL+"GCommercialDocs/img/material-mini.png";
		  icon.title = "Clicca per vedere la scheda di questo materiale";
		  icon.onclick = function(){showMaterial(this);}
		  icon.style.marginLeft = "5px";
		  r.cells[0].appendChild(icon);
		 }
		} break;

	  case 'labor' : {
		 // create icon
		 var icon = r.cells[0].getElementsByTagName('IMG')[0];
		 if(!icon)
		 {
		  var icon = document.createElement("IMG");
		  icon.src = ABSOLUTE_URL+"GCommercialDocs/img/labor-mini.png";
		  icon.title = "Clicca per vedere la scheda di questa lavorazione";
		  icon.onclick = function(){showLabor(this);}
		  icon.style.marginLeft = "5px";
		  r.cells[0].appendChild(icon);
		 }
		} break;

	  case 'book' : {
		 // create icon
		 var icon = r.cells[0].getElementsByTagName('IMG')[0];
		 if(!icon)
		 {
		  var icon = document.createElement("IMG");
		  icon.src = ABSOLUTE_URL+"GCommercialDocs/img/book-mini.png";
		  icon.title = "Clicca per vedere la scheda di questo libro";
		  icon.onclick = function(){showBook(this);}
		  icon.style.marginLeft = "5px";
		  r.cells[0].appendChild(icon);
		 }
		} break;

	  case 'service' : {
		 // create icon
		 var icon = r.cells[0].getElementsByTagName('IMG')[0];
		 if(!icon)
		 {
		  var icon = document.createElement("IMG");
		  icon.src = ABSOLUTE_URL+"GCommercialDocs/img/service-mini.png";
		  icon.title = "Clicca per vedere la scheda di questo servizio";
		  icon.onclick = function(){showService(this);}
		  icon.style.marginLeft = "5px";
		  r.cells[0].appendChild(icon);
		 }
		} break;

	  case 'note' : {
		 var icon = r.cells[0].getElementsByTagName('IMG')[0];
		 var icon2 = r.cells[0].getElementsByTagName('IMG')[1];
		 if(!icon)
		 {
		  var icon = document.createElement("IMG");
		  icon.src = ABSOLUTE_URL+"GCommercialDocs/img/print-mini.png";
		  icon.title = "Clicca per trasformare questa riga di nota in un messaggio non stampabile";
		  icon.onclick = function(){switchNoteMessage(this);}
		  icon.style.marginLeft = "5px";
		  r.cells[0].appendChild(icon);
		 }	 
		 if(!icon2)
		 {
		  var icon = document.createElement("IMG");
		  icon.src = ABSOLUTE_URL+"GCommercialDocs/img/edit-black.png";
		  icon.title = "Clicca per editare questa nota";
		  icon.onclick = function(){editNote(this);}
		  icon.style.marginLeft = "5px";
		  r.cells[0].appendChild(icon);
		 }
		} break;

	  case 'pictureframe' : {
		 // create icon
		 var icon = r.cells[0].getElementsByTagName('IMG')[0];
		 if(!icon)
		 {
		  var icon = document.createElement("IMG");
		  icon.src = ABSOLUTE_URL+"GCommercialDocs/img/pictureframe-mini.png";
		  icon.title = "Clicca per vedere i dettagli di questa cornice";
		  icon.onclick = function(){showPictureFrame(this);}
		  icon.style.marginLeft = "5px";
		  r.cells[0].appendChild(icon);
		 }
		} break;

	 }

	}

 tb.OnRowMove = function(r){
	 if(r.id && (UPDATED_ROWS.indexOf(r) < 0)) UPDATED_ROWS.push(r);
	 else if(!r.id && (NEW_ROWS.indexOf(r) < 0)) NEW_ROWS.push(r);
	 DOCISCHANGED = true;
	}


 /* EXPENSES TABLE */
 expTB = new GMUTable(document.getElementById('expensestable'));
 expTB.FieldByName['vat'].enableSearch("dynarc search -ap `vatrates` -fields code_str,name `", "` -limit 5 --order-by `code_str ASC` -get percentage,vat_type", "percentage","name","items",true,"percentage");

 expTB.OnBeforeAddRow = function(r){
	 r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : DEF_VAT_ID);
	 r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : DEF_VAT_TYPE);
	 r.cells[0].innerHTML = "<input type='checkbox'/ >";
	 r.cells[1].innerHTML = "<span class='graybold'></span>";
	 r.cells[2].innerHTML = "<span class='graybold 13 center'>"+(SUBJECT_VATID ? SUBJECT_VATRATE : DEF_VAT)+"%</span>";
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
	 DOCISCHANGED = true;
	 updateTotals();
	}


 /* MAIN MENU */

 MainMenu = new GMenu(document.getElementById('mainmenu'));
 new GMenu(document.getElementById('doctypemenu'));
 PayModeMenu = new GPopupMenu(document.getElementById('paymodemenu'), document.getElementById('paymodemenu-list'));
 InternalDocRefMenu = new GPopupMenu(document.getElementById('internaldocrefmenu'), document.getElementById('internaldocrefmenu-list'));
 document.getElementById('rubricaedit').onblur = function(){hideRubricaEdit();}
 RubricaEdit = new DynRubricaEdit(document.getElementById('rubricaedit'), "", "", " -extget 'rubricainfo' -get 'agent_id'"+(STRICT_AGENT ? " -where `agent_id='"+AGENT_ID+"'`" : ""));
 document.getElementById('rubricaedit').onchange = function(){subjectChanged(this);}

 if(SHIP_SUBJECT_ID)
 {
  new DynRubricaEdit(document.getElementById('ship-subject'));
  document.getElementById('ship-subject').onchange = function(){shipSubjectChanged(this);}
 }

 var agED = EditSearch.init(document.getElementById('agent'),"dynarc item-find -ap rubrica -ct agents -field name `","` -limit 10 --order-by 'name ASC'","id","name","items",true);
 //new DynRubricaEdit(document.getElementById('agent'), "agents");
 document.getElementById('agent').onchange = function(){
	 if(this.value && this.data)
	 {
	  AGENT_ID = this.data['id'];
	 }
	 else
	  AGENT_ID = 0;
	}

 /* INTERNAL DOC REF */
 InternalDocRef = EditSearch.init(document.getElementById('internaldocref'),"dynarc search -ap commercialdocs -fields code_num,name `","` -limit 20 --order-by 'ctime ASC'","id","name","items",true);
 InternalDocRef.onchange = function(){
	 if(this.value && this.data)
	 {
	  this.setAttribute('refap',this.data['tb_prefix']);
	  this.setAttribute('refid',this.data['id']);
	  if(this.data['tb_prefix'] == "commercialdocs")
	  {
	   if(!confirm("Desideri importare tutti gli articoli dal documento di riferimento?"))
		return;
	   var sh = new GShell();
	   sh.docrefID = this.data['id'];
	   sh.OnError = function(err){alert(err);}
	   sh.OnOutput = function(o,a){
		 if(!a['elements']) return;
		 for(var c=0; c < a['elements'].length; c++)
		 {
		  var el = a['elements'][c];
		  insertElementFromData(el, 'commercialdocs', this.docrefID);
		 }
		}
	   sh.sendCommand("dynarc item-info -ap commercialdocs -id '"+this.data['id']+"' -extget `cdelements`");
	  }
	 }
	 else
	 {
	  this.setAttribute('refap','');
	  this.setAttribute('refid',0);
	 }
	}

 /* SUBJECT UPDATE */
 if(!document.getElementById('rubricaedit').value)
  subjectChange();

 /* ATTACHMENTS */
 attUpld = new GUploader(null,null,"commercialdocs/");
 document.getElementById('gupldspace').appendChild(attUpld.O);
 attUpld.OnUpload = function(file){
	 var sh = new GShell();
	 sh.OnError = function(err){alert(err);}
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

 /* PREDEFINED MESSAGES */
 var lastproducts = document.getElementById("lastproducts");
 lastproducts.onmouseover =  function(){this.mouseisover=true;}
 lastproducts.onmouseout = function(){this.mouseisover=false;}
 lastproductsInit();

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


 document.addEventListener ? document.addEventListener("mouseup", function(){hideAllClouds();},false) : document.attachEvent("onmouseup",function(){hideAllClouds();});

 updateTotals(null, false, function(){
	 if(POSTSAVEACTION && (POSTSAVEACTION != ""))
	 {
	  if(window.opener && window.opener.document && (window.opener.document.location.href.indexOf("GCommercialDocs/") > 0))
	   window.opener.document.location.reload();
	 }
	 switch(POSTSAVEACTION)
	 {
	  case 'printpreview' : printPreview(); break;
	  case 'duplicate' : Duplicate(); break;
	  case 'saveasprecomp' : SaveAsPrecompiled(); break;
	  case 'abort' : abort(); break;
	 }
	 POSTSAVEACTION = "";
	});
}

function showHideColumn(tag, bool)
{
 if(tag == "metric")
 {
  tb.showHideColumn("metriclength",bool);
  tb.showHideColumn("metricwidth",bool);
  tb.showHideColumn("metrichw",bool);
  tb.showHideColumn("metriceqp",bool);
 }
 else
  tb.showHideColumn(tag,bool);

 switch(tag)
 {
  case 'profit' : {
	 document.getElementById('doctot-profit-th').style.display = bool ? "" : "none";
	 document.getElementById('doctot-profit').style.display = bool ? "" : "none";
	} break;

  case 'vendorprice' : {
	 document.getElementById('doctot-purchasecosts-th').style.display = bool ? "" : "none";
	 document.getElementById('doctot-purchasecosts').style.display = bool ? "" : "none";
	} break;
 }
}

function hideAllClouds()
{
 /* HIDE PREDEFINED MESSAGES */
 var predefmsg = document.getElementById("predefmsg");
 if(!predefmsg.mouseisover)
  predefmsg.style.display = "none";

 /* HIDE LAST PRODUCTS */
 var lastproducts = document.getElementById("lastproducts");
 if(!lastproducts.mouseisover)
  lastproducts.style.display = "none";

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
  _paymentModeSelectChange(document.getElementById('paymentmode-select'), null, true);
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
  sh.OnOutput = function(o,a){
	 if(!a) return; 
	 RubricaEdit.data = a;
	 subjectChanged(document.getElementById('rubricaedit'));
	}
  sh.sendCommand("dynlaunch -ap `rubrica` -id `"+subjectId+"`");
 }
 else
 {
  sh.OnOutput = function(o,a){
	 if(!a) return;
	 RubricaEdit.data = a;
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){
		 RubricaEdit.data = a;
		 subjectChanged(document.getElementById('rubricaedit'));
		}
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
 DOCISCHANGED = true;
 if(ed.data)
 {
  SUBJECT_VATNUMBER = ed.data['vatnumber'] ? ed.data['vatnumber'] : "";
  SUBJECT_TAXCODE = ed.data['taxcode'] ? ed.data['taxcode'] : "";
  SUBJECT_TYPE = ed.data['iscompany'];
  if(ed.data['vat_id'] && (parseFloat(ed.data['vat_id']) > 0))
  {
   SUBJECT_VATID = ed.data['vat_id'];
   if(VAT_BY_ID[SUBJECT_VATID])
   {
	SUBJECT_VATRATE = VAT_BY_ID[SUBJECT_VATID]['percentage'];
	SUBJECT_VATTYPE = VAT_BY_ID[SUBJECT_VATID]['type'];
	SUBJECT_VATCODE = VAT_BY_ID[SUBJECT_VATID]['code'];
	SUBJECT_VATNAME = VAT_BY_ID[SUBJECT_VATID]['name'];
   }
   else
   {
	SUBJECT_VATID = 0;
	SUBJECT_VATRATE = 0;
	SUBJECT_VATTYPE = "";
	SUBJECT_VATCODE = "";
	SUBJECT_VATNAME = "";
   }
  }
  _updateContacts(ed.data['id']);
  document.getElementById('ourbanksupport-select').value = ed.data['ourbanksupport_id'];
  LastRUBID = ed.data['id'];
  if(ed.data['agent_id'])
  {
   AGENT_ID = ed.data['agent_id'];
   // get agent name //
   var sh = new GShell();
   sh.OnOutput = function(o,a){
	 document.getElementById('agent').value = a ? a['name'] : "";
	}
   sh.sendCommand("dynarc item-info -ap rubrica -id '"+ed.data['agent_id']+"'");
  }
 }
 else
 {
  SUBJECT_VATNUMBER = "";
  SUBJECT_TAXCODE = "";
  SUBJECT_TYPE = 0;
  AGENT_ID = STRICT_AGENT_ID;

  SUBJECT_VATID = 0;
  SUBJECT_VATRATE = 0;
  SUBJECT_VATTYPE = "";
  SUBJECT_VATCODE = "";
  SUBJECT_VATNAME = "";

  /* RESET PAYMENT MODE */
  document.getElementById('paymentmode-select').value = 0;
  _paymentModeSelectChange(document.getElementById('paymentmode-select'));
  document.getElementById('ourbanksupport-select').value = 0;
  updateReferences();
 }
 updateLastProducts(LastRUBID);

 if((CAT_TAG == "PURCHASEINVOICES") && ed.data && ed.data['id'])
  getUngroupedDDT(ed.data['id']);
 else if((CAT_TAG == "DDTIN") && ed.data && ed.data['id'])
  getOpenVendorOrders(ed.data['id']);

 if(SUBJECT_TYPE == 2)
  showRIFPAcontainer();
 else
  hideRIFPAcontainer();

 if(SUBJINFOXL_ENABLED)
  updateSubjinfoXL();

 if(tb.O.rows.length > 1)
 {
  if(confirm("Il "+(IS_VENDOR ? "fornitore" : "cliente")+" è stato cambiato. Desideri aggiornare i prezzi degli articoli?"))
   updateAllItemPrices();
 }
}

function updateSubjinfoXL()
{
 /* GET SUBJECT */
 if(!RubricaEdit.value) var subjectId = 0; else if(RubricaEdit.data) var subjectId = RubricaEdit.data['id']; else var subjectId = 0;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(htmlContent,a){
	 document.getElementById('subjinfoxl-container').innerHTML = htmlContent;
	}
 sh.sendCommand("parserize -ap '<?php echo $config['other']['subjinfoxl']['contentap']; ?>' -id '<?php echo $config['other']['subjinfoxl']['contentid']; ?>' -p contactinfo -params 'id="+subjectId+"'");
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

	 if(document.getElementById('rubricaedit').getAttribute('refid') != id)
	  CUSTPLID = a['pricelist_id'];
	 CUSTDISTANCE = a['distance'];

	 if(document.getElementById('rubricaedit').getAttribute('refid') != id)
	 {
	  /* UPDATE PAYMENT MODE */
	  document.getElementById('paymentmode-select').value = a['paymentmode'];
	  _paymentModeSelectChange(document.getElementById('paymentmode-select'));
	 }


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

	 /* Update document causal */
	 if(a['gcdcausals'] && a['gcdcausals'][CAT_TAG])
	 {
	  var docTagNameLI = document.getElementById("doctagname");
	  var ul = document.getElementById("doctagsubmenu");
	  if(ul)
	  {
	   var list = ul.getElementsByTagName('LI');
	   for(var c=0; c < list.length; c++)
	   {
	    if(list[c].getAttribute('refid') == a['gcdcausals'][CAT_TAG])
	    {
		 editDocTag(list[c]);
		 break;
	    }
	   }
	  }
	 }	  

	 if(!a['contacts'] || !a['contacts'].length)
	  return _updateContacts(0);

	 var html = a['contacts'][0]['address']+"<br/ >"+a['contacts'][0]['city'];
	 if(a['contacts'][0]['province'])
	  html+= " ("+a['contacts'][0]['province']+")";
	 document.getElementById('subjdefcontact').innerHTML = html;

	 /* UPDATE SHIPPING PAGE */
	 var sel = document.getElementById('shipping-contactselect');
	 var oldselval = sel.value;
	 var shipContactInfo = null;
	 while(sel.options.length > 0)
	  sel.removeChild(sel.options[0]);
	
	 for(var c=0; c < a['contacts'].length; c++)
	 {
	  var opt = document.createElement('OPTION');
	  opt.value = a['contacts'][c]['id'];
	  opt.innerHTML = a['contacts'][c]['label'];
	  sel.appendChild(opt);
	  if(opt.value == oldselval)
	   shipContactInfo = a['contacts'][c];
	 }
	 var opt = document.createElement('OPTION');
	 opt.value = "";
	 opt.innerHTML = "altra destinazione...";
	 sel.appendChild(opt);

	 if(document.getElementById('rubricaedit').getAttribute('refid') == id)
	  sel.value = oldselval;

	 if(!shipContactInfo)
	  shipContactInfo = a['contacts'][0];

	 var html = shipContactInfo['name']+"<br/ >"+shipContactInfo['address']+"<br/ >"+shipContactInfo['city'];
	 if(shipContactInfo['province'])
	  html+= " ("+shipContactInfo['province']+")";
	 document.getElementById('shiptoaddr').innerHTML = html;

	 if(shipContactInfo)
	 {
	  document.getElementById('ship-subject').value = shipContactInfo['name'];
	  document.getElementById('ship-address').value = shipContactInfo['address'];
	  document.getElementById('ship-city').value = shipContactInfo['city'];
	  document.getElementById('ship-zipcode').value = shipContactInfo['zipcode'];
	  document.getElementById('ship-prov').value = shipContactInfo['province'];
	  document.getElementById('ship-country').value = shipContactInfo['countrycode'];
	 }
	}
 sh.sendCommand("dynarc item-info -ap `rubrica` -id `"+id+"` -extget `rubricainfo.gcdcausals,contacts,banks,references` -get `paymentmode,pricelist_id,distance`");
}

function _updateShippingContact(id)
{
 if(!id)
 {
  /* Selezionata altra destinazione */
  document.getElementById("ship-subject").value = "";
  document.getElementById("ship-address").value = "";
  document.getElementById("ship-city").value = "";
  document.getElementById("ship-zipcode").value = "";
  document.getElementById("ship-prov").value = "";

  new DynRubricaEdit(document.getElementById('ship-subject'));
  document.getElementById('ship-subject').onchange = function(){shipSubjectChanged(this);}
  document.getElementById('ship-subject').focus();
  return;
 }

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a)
	  return;
	 document.getElementById('ship-subject').value = a['name'];
	 document.getElementById('ship-address').value = a['address'];
	 document.getElementById('ship-city').value = a['city'];
	 document.getElementById('ship-zipcode').value = a['zipcode'];
	 document.getElementById('ship-prov').value = a['province'];
	 document.getElementById('ship-country').value = a['countrycode'];
	 var addr = a['name']+"<br/ >"+a['address']+"<br/ >"+a['city']+(a['province'] ? " ("+a['province']+")" : "");
	 document.getElementById('shiptoaddr').innerHTML = addr;
	}
 sh.sendCommand("dynarc exec-func ext:contacts.info -params `ap=rubrica&id="+id+"`");
}

function shipSubjectChanged(ed)
{
 if(!ed.value || !ed.data)
  return;
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 var sel = document.getElementById('shipping-contactselect');
	 if(!a['contacts'] || !a['contacts'].length)
	 {
	  sel.options[sel.selectedIndex].value = "";
	  return;
	 }
	 document.getElementById("ship-address").value = a['contacts'][0]['address'];
	 document.getElementById("ship-city").value = a['contacts'][0]['city'];
	 document.getElementById("ship-zipcode").value = a['contacts'][0]['zipcode'];
	 document.getElementById("ship-prov").value = a['contacts'][0]['province'];
	 sel.options[sel.selectedIndex].value = a['contacts'][0]['id'];
	}
 sh.sendCommand("dynarc item-info -ap `rubrica` -id `"+ed.data['id']+"` -extget `contacts`");
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
 if(DOCISCHANGED && !LOCKED)
 {
  if(confirm("Il documento è stato modificato. Salvare le modifiche prima di stampare?"))
  {
   POSTSAVEACTION = "printpreview";
   return saveDoc();
  }
 }

 if(!RubricaEdit.value)
  var subjectId = 0;
 else if(RubricaEdit.data)
  var subjectId = RubricaEdit.data['id'];
 else
  var subjectId = 0;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var status = <?php echo $docInfo['status'] ? $docInfo['status'] : '0'; ?>;
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
 sh.sendCommand("gframe -f print.preview -params `modelap=printmodels&modelct="+CAT_TAG+"&parser=commercialdocs&ap=commercialdocs&id=<?php echo $docInfo['id']; ?>&subjid="+subjectId+"` -title `<?php echo urlencode(html_entity_decode($docInfo['name'],ENT_QUOTES,'UTF-8')); ?>`");
}

function pay()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){document.location.reload();}
 sh.sendCommand("gframe -f commercialdocs/pay -params `id=<?php echo $docInfo['id']; ?>&desc=Saldo`");
}

function copy()
{
 var list = tb.GetSelectedRows();
 if(!list.length)
  return alert("Nessuna riga è stata selezionata");

 COPY_ROWS = new Array();
 for(var c=0; c < list.length; c++)
  COPY_ROWS.push(list[c]);

 document.getElementById('pastemenubtn').className = "";
}

function paste()
{
 if(!COPY_ROWS.length)
  return alert("Niente da copiare");

 for(var c=0; c < COPY_ROWS.length; c++)
 {
  var cR = COPY_ROWS[c];
  var r = tb.AddRow();
  r.setAttribute("type",cR.getAttribute('type'));
  r.setAttribute("weightunits",cR.getAttribute('weightunits'));
  if(cR.getAttribute('type') == "note")
  {
   var colSpan = tb.O.rows[0].cells.length-1;
   while(r.cells.length > 2)
	r.deleteCell(2);
   r.cells[1].colSpan=colSpan;
   r.cells[1].setValue(cR.cells[1].getValue());

   var spanlist = r.cells[1].getElementsByTagName('SPAN');
   var spanlist2 = cR.cells[1].getElementsByTagName('SPAN');

   if(spanlist2[1])
   {
    if(spanlist.length < 2)
    {
     var span2 = document.createElement('SPAN'); span2.style.display = "none";
     r.cells[1].appendChild(span2);
     span2.innerHTML = spanlist2[1].innerHTML;
    }
    else
     spanlist[1].innerHTML = spanlist2[1].innerHTML;
   }

   // create icon
   var icon = document.createElement("IMG");
   icon.src = ABSOLUTE_URL+"GCommercialDocs/img/print-mini.png";
   icon.title = "Clicca per trasformare questa riga di nota in un messaggio non stampabile";
   icon.onclick = function(){switchNoteMessage(this);}
   icon.style.marginLeft = "5px";
   r.cells[0].appendChild(icon);
   var icon2 = document.createElement("IMG");
   icon2.src = ABSOLUTE_URL+"GCommercialDocs/img/edit-black.png";
   icon2.title = "Clicca per editare questa nota";
   icon2.onclick = function(){editNote(this);}
   icon2.style.marginLeft = "5px";
   r.cells[0].appendChild(icon2);
  }
  else if(cR.getAttribute('type') == "message")
  {
   var colSpan = tb.O.rows[0].cells.length-1;
   while(r.cells.length > 2)
	r.deleteCell(2);
   r.cells[1].colSpan=colSpan;
   r.cells[1].setValue(cR.cells[1].getValue());

   var spanlist = r.cells[1].getElementsByTagName('SPAN');
   var spanlist2 = cR.cells[1].getElementsByTagName('SPAN');

   if(spanlist2[1])
   {
    if(spanlist.length < 2)
    {
     var span2 = document.createElement('SPAN'); span2.style.display = "none";
     r.cells[1].appendChild(span2);
     span2.innerHTML = spanlist2[1].innerHTML;
    }
    else
     spanlist[1].innerHTML = spanlist2[1].innerHTML;
   }

   // create icon
   var icon = document.createElement("IMG");
   icon.src = ABSOLUTE_URL+"GCommercialDocs/img/message-mini.png";
   icon.title = "Clicca per trasformare questo messaggio in una riga di nota stampabile";
   icon.onclick = function(){switchNoteMessage(this);}
   icon.style.marginLeft = "5px";
   r.cells[0].appendChild(icon);
   var icon2 = document.createElement("IMG");
   icon2.src = ABSOLUTE_URL+"GCommercialDocs/img/edit-black.png";
   icon2.title = "Clicca per editare questa nota";
   icon2.onclick = function(){editNote(this);}
   icon2.style.marginLeft = "5px";
   r.cells[0].appendChild(icon2);
  }
  else
  {
   switch(r.getAttribute('type'))
   {
	case 'article' : {
		 // create icon
		 var icon = document.createElement("IMG");
		 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/article-mini.png";
		 icon.title = "Clicca per vedere la scheda di questo articolo";
		 icon.onclick = function(){showProduct(this);}
		 icon.style.marginLeft = "5px";
		 r.cells[0].appendChild(icon);
		} break;

	case 'finalproduct' : {
		 // create icon
		 var icon = document.createElement("IMG");
		 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/finalproduct-mini.png";
		 icon.title = "Clicca per vedere la scheda di questo prodotto";
		 icon.onclick = function(){showFinalProduct(this);}
		 icon.style.marginLeft = "5px";
		 r.cells[0].appendChild(icon);
		} break;

	case 'component' : {
		 // create icon
		 var icon = document.createElement("IMG");
		 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/component-mini.png";
		 icon.title = "Clicca per vedere la scheda di questo componente";
		 icon.onclick = function(){showComponent(this);}
		 icon.style.marginLeft = "5px";
		 r.cells[0].appendChild(icon);
		} break;

	case 'material' : {
		 // create icon
		 var icon = document.createElement("IMG");
		 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/material-mini.png";
		 icon.title = "Clicca per vedere la scheda di questo materiale";
		 icon.onclick = function(){showMaterial(this);}
		 icon.style.marginLeft = "5px";
		 r.cells[0].appendChild(icon);
		} break;

	case 'labor' : {
		 // create icon
		 var icon = document.createElement("IMG");
		 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/labor-mini.png";
		 icon.title = "Clicca per vedere la scheda di questa lavorazione";
		 icon.onclick = function(){showLabor(this);}
		 icon.style.marginLeft = "5px";
		 r.cells[0].appendChild(icon);
		} break;

	case 'book' : {
		 // create icon
		 var icon = document.createElement("IMG");
		 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/book-mini.png";
		 icon.title = "Clicca per vedere la scheda di questo libro";
		 icon.onclick = function(){showBook(this);}
		 icon.style.marginLeft = "5px";
		 r.cells[0].appendChild(icon);
		} break;

	case 'service' : {
		 // create icon
		 var icon = document.createElement("IMG");
		 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/service-mini.png";
		 icon.title = "Clicca per vedere la scheda di questo servizio";
		 icon.onclick = function(){showService(this);}
		 icon.style.marginLeft = "5px";
		 r.cells[0].appendChild(icon);
		} break;

	case 'pictureframe' : {
		 // create icon
		 var icon = document.createElement("IMG");
		 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/pictureframe-mini.png";
		 icon.title = "Clicca per vedere i dettagli di questa cornice";
		 icon.onclick = function(){showPictureFrame(this);}
		 icon.style.marginLeft = "5px";
		 r.cells[0].appendChild(icon);
		} break;

   }

   r.setAttribute('vatid',cR.getAttribute('vatid'));
   r.setAttribute('vattype',cR.getAttribute('vattype'));
   r.setAttribute('refap',cR.getAttribute('refap'));
   r.setAttribute('refid',cR.getAttribute('refid'));
   r.setAttribute('vendorid',cR.getAttribute('vendor_id'));

   r.cell['code'].setValue(cR.cell['code'].getValue());
   r.cell['vencode'].setValue(cR.cell['vencode'].getValue());
   r.cell['mancode'].setValue(cR.cell['mancode'].getValue());
   r.cell['description'].setValue(cR.cell['description'].getValue());
   r.cell['qty'].setValue(cR.cell['qty'].getValue());
   r.cell['weight'].setValue(cR.cell['weight'].getValue());   

   r.cell['discount'].setValue(cR.cell['discount'].getValue());
   r.cell['discount2'].setValue(cR.cell['discount2'].getValue());
   r.cell['discount3'].setValue(cR.cell['discount3'].getValue());
   r.cell['units'].setValue(cR.cell['units'].getValue());
   r.cell['vendorprice'].setValue(cR.cell['vendorprice'].getValue());
   r.cell['unitprice'].setValue(cR.cell['unitprice'].getValue());
   r.cell['unitprice'].getElementsByTagName('SPAN')[0].title = cR.cell['unitprice'].getElementsByTagName('SPAN')[0].title;

   r.cell['plbaseprice'].setValue(cR.cell['plbaseprice'].getValue());
   r.cell['plbaseprice'].getElementsByTagName('SPAN')[0].title = cR.cell['plbaseprice'].getElementsByTagName('SPAN')[0].title;

   r.cell['plmrate'].setValue(cR.cell['plmrate'].getValue());
   r.cell['pldiscperc'].setValue(cR.cell['pldiscperc'].getValue());
   r.cell['vat'].setValue(cR.cell['vat'].getValue());
   r.cell['vatcode'].setValue(cR.cell['vatcode'].getValue());
   r.cell['vatname'].setValue(cR.cell['vatname'].getValue());
   r.cell['price'].setValue(cR.cell['price'].getValue());
   r.cell['price'].getElementsByTagName('SPAN')[0].title = cR.cell['price'].getElementsByTagName('SPAN')[0].title;
   r.cell['pricelist'].setValue(cR.cell['pricelist'].getValue());
   r.cell['pricelist'].setAttribute("pricelistid",cR.cell['pricelist'].getAttribute('pricelistid'));

   // variants //
   if(cR.itemInfo && cR.itemInfo['variants'])
   {
	// colori e tinte //
	var options = new Array();
	if(cR.itemInfo['variants']['colors'])
	{
	 var arr = new Array();
	 for(var i=0; i < cR.itemInfo['variants']['colors'].length; i++)
	  arr.push(cR.itemInfo['variants']['colors'][i]['name']);
	 options.push(arr);
	}
	if(cR.itemInfo['variants']['tint'])
	{
	 var arr = new Array();
	 for(var i=0; i < cR.itemInfo['variants']['tint'].length; i++)
	  arr.push(cR.itemInfo['variants']['tint'][i]['name']);
	 options.push(arr);
	}
	r.cell['coltint'].setOptions(options);

	// taglie, misure e altro //
	var options = new Array();
	if(cR.itemInfo['variants']['sizes'])
	{
	 var arr = new Array();
	 for(var i=0; i < cR.itemInfo['variants']['sizes'].length; i++)
	  arr.push(cR.itemInfo['variants']['sizes'][i]['name']);
	 options.push(arr);
	}
	if(cR.itemInfo['variants']['dim'])
	{
	 var arr = new Array();
	 for(var i=0; i < cR.itemInfo['variants']['dim'].length; i++)
	  arr.push(cR.itemInfo['variants']['dim'][i]['name']);
	 options.push(arr);
	}
	if(cR.itemInfo['variants']['other'])
	{
	 var arr = new Array();
	 for(var i=0; i < cR.itemInfo['variants']['other'].length; i++)
	  arr.push(cR.itemInfo['variants']['other'][i]['name']);
	 options.push(arr);
	}
	r.cell['sizmis'].setOptions(options);
   }

   r.cell['coltint'].setValue(cR.cell['coltint'].getValue());
   r.cell['sizmis'].setValue(cR.cell['sizmis'].getValue());

   r.cell['ritaccapply'].setValue(cR.cell['ritaccapply'].getValue());
   r.cell['ccpapply'].setValue(cR.cell['ccpapply'].getValue());

   updateTotals(r);
  }
 }
 tb.unselectAll();
 DOCISCHANGED = true;
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
 DOCISCHANGED = true;
}

function InsertRow(type, content)
{
 var r = tb.AddRow();
 r.setAttribute('type',type);
 switch(type)
 {
  case 'article' : {
	 // create icon
	 var icon = document.createElement("IMG");
	 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/article-mini.png";
	 icon.title = "Clicca per vedere la scheda di questo articolo";
	 icon.onclick = function(){showProduct(this);}
	 icon.style.marginLeft = "5px";
	 r.cells[0].appendChild(icon);
	 if(DEFAULT_UMIS['article']) r.cell['units'].setValue(DEFAULT_UMIS['article']);
	} break;

  case 'finalproduct' : {
	 // create icon
	 var icon = document.createElement("IMG");
	 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/finalproduct-mini.png";
	 icon.title = "Clicca per vedere la scheda di questo prodotto";
	 icon.onclick = function(){showFinalProduct(this);}
	 icon.style.marginLeft = "5px";
	 r.cells[0].appendChild(icon);
 	 if(DEFAULT_UMIS['finalproduct']) r.cell['units'].setValue(DEFAULT_UMIS['finalproduct']);
	} break;

  case 'component' : {
	 // create icon
	 var icon = document.createElement("IMG");
	 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/component-mini.png";
	 icon.title = "Clicca per vedere la scheda di questo componente";
	 icon.onclick = function(){showComponent(this);}
	 icon.style.marginLeft = "5px";
	 r.cells[0].appendChild(icon);
	 if(DEFAULT_UMIS['component']) r.cell['units'].setValue(DEFAULT_UMIS['component']);
	} break;

  case 'material' : {
	 // create icon
	 var icon = document.createElement("IMG");
	 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/material-mini.png";
	 icon.title = "Clicca per vedere la scheda di questo materiale";
	 icon.onclick = function(){showMaterial(this);}
	 icon.style.marginLeft = "5px";
	 r.cells[0].appendChild(icon);
	 if(DEFAULT_UMIS['material']) r.cell['units'].setValue(DEFAULT_UMIS['material']);
	} break;

  case 'labor' : {
	 // create icon
	 var icon = document.createElement("IMG");
	 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/labor-mini.png";
	 icon.title = "Clicca per vedere la scheda di questa lavorazione";
	 icon.onclick = function(){showLabor(this);}
	 icon.style.marginLeft = "5px";
	 r.cells[0].appendChild(icon);
	 if(DEFAULT_UMIS['labor']) r.cell['units'].setValue(DEFAULT_UMIS['labor']);
	} break;


  case 'book' : {
	 // create icon
	 var icon = document.createElement("IMG");
	 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/book-mini.png";
	 icon.title = "Clicca per vedere la scheda di questo libro";
	 icon.onclick = function(){showBook(this);}
	 icon.style.marginLeft = "5px";
	 r.cells[0].appendChild(icon);
	 if(DEFAULT_UMIS['book']) r.cell['units'].setValue(DEFAULT_UMIS['book']);
	} break;

  case 'service' : {
	 // create icon
	 var icon = document.createElement("IMG");
	 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/service-mini.png";
	 icon.title = "Clicca per vedere la scheda di questo servizio";
	 icon.onclick = function(){showService(this);}
	 icon.style.marginLeft = "5px";
	 r.cells[0].appendChild(icon);
	 if(DEFAULT_UMIS['service']) r.cell['units'].setValue(DEFAULT_UMIS['service']);
	} break;

  case 'note' : {
	 var colSpan = tb.O.rows[0].cells.length-1;
	 while(r.cells.length > 2)
	  r.deleteCell(2);
	 r.cells[1].colSpan=colSpan;
	 if(content)
	  r.cells[1].setValue(content);
	 else
	 {
	  var spanlist = r.cells[1].getElementsByTagName('SPAN');
	  if(spanlist.length < 2)
	  {
	   var span = document.createElement('SPAN'); span.style.display = "none";
	   r.cells[1].appendChild(span);
	  }
     }

	 var icon = document.createElement("IMG");
	 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/print-mini.png";
	 icon.title = "Clicca per trasformare questa riga di nota in un messaggio non stampabile";
	 icon.onclick = function(){switchNoteMessage(this);}
	 icon.style.marginLeft = "5px";
	 r.cells[0].appendChild(icon);

	 var icon2 = document.createElement("IMG");
	 icon2.src = ABSOLUTE_URL+"GCommercialDocs/img/edit-black.png";
	 icon2.title = "Clicca per editare questa nota";
	 icon2.onclick = function(){editNote(this);}
	 icon2.style.marginLeft = "5px";
	 r.cells[0].appendChild(icon2);
	  
	} break;

  case 'pictureframe' : {
	 // create icon
	 var icon = document.createElement("IMG");
	 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/pictureframe-mini.png";
	 icon.title = "Clicca per vedere i dettagli di questa cornice";
	 icon.onclick = function(){showPictureFrame(this);}
	 icon.style.marginLeft = "5px";
	 r.cells[0].appendChild(icon);
	} break;

  default : r.cell['pricelist'].setAttribute('pricelist_id',CUSTPLID); break
 }
 LAST_ELM_TYPE = type;

 if(SHOW_CONTRIBANDDEDUCTS && RIT_ACCONTO)
 {
  if(type == "service")
   r.cell['ritaccapply'].setValue(1);
 }
 if(SHOW_CONTRIBANDDEDUCTS && CASSA_PREV)
 {
  if(type == "service")
   r.cell['ccpapply'].setValue(1);
 }

 if(!content)
  r.edit();
}

function InsertPictureFrame()
{
 /* GET SUBJECT */
 if(!RubricaEdit.value)
  var subjectId = 0;
 else if(RubricaEdit.data)
  var subjectId = RubricaEdit.data['id'];
 else
  var subjectId = 0;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var r = tb.AddRow();
	 r.id = a['id'];
 	 r.setAttribute('type',"pictureframe");
	 r.data = a;
	 r.xmldata = o;

	 if(NEW_ROWS.indexOf(r) > -1) NEW_ROWS.splice(NEW_ROWS.indexOf(r),1);
	 
	 // create icon
	 var icon = document.createElement("IMG");
	 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/pictureframe-mini.png";
	 icon.title = "Clicca per vedere la scheda di questa cornice";
	 icon.onclick = function(){showPictureFrame(this);}
	 icon.style.marginLeft = "5px";
	 r.cells[0].appendChild(icon);

     r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : a['vatid']);
     r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : a['vattype']);
	 r.setAttribute('refap',a['frame_ap']);
	 r.setAttribute('refid',a['frame_id']);

	 r.cell['code'].setValue(r.data['frame_code']);
	 r.cell['description'].setValue(r.data['frame_name']);
	 r.cell['qty'].setValue(1);
	 r.cell['units'].setValue("PZ");

	 r.cell['unitprice'].setValue(a['finalprice']);
	 r.cell['unitprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['finalprice'],4);
	 r.cell['price'].setValue(a['finalprice']);
	 r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['finalprice'],4);
     r.cell['vat'].setValue(SUBJECT_VATID ? SUBJECT_VATRATE : a['vatrate']);

	 if(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']])
	 {
	  r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['code']);
	  r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['name']);
	 }

	 updateTotals(r);
	 DOCISCHANGED = true;
	}
 sh.sendCommand("gframe -f pictureframe/insertframe -params `subjid="+subjectId+"&plid="+CUSTPLID+"&docap=commercialdocs&docid=<?php echo $docInfo['id']; ?>&autosave=true`");
}

function updateRowFromData(r,data,qty)
{
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
  if(IS_VENDOR)	//IF IS VENDOR
  {
   if(a['vendor_id'] == subjectId)
   {
    r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']);
    r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : a['vendor_vattype']);
   }
   else
   {
    r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : a['vatid']);
    r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : a['vattype']);
   }
  }
  else    // IF IS CUSTOMER
  {
   r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : a['vatid']);
   r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : a['vattype']);
  }
  if(data['variant_type'] && data['variant_name'] && data['code_str'])
   r.cell['code'].setValue(data['code_str']);
  else
   r.cell['code'].setValue(a['code_str']);
  r.cell['vencode'].setValue(a['vencode']);
  r.cell['mancode'].setValue(a['manufacturer_code']);
  r.setAttribute('refid',a['id']);
  r.setAttribute('refap',a['tb_prefix']);
  r.setAttribute('vendorid',a['vendor_id']);

  r.cell['brand'].setValue(a['brand'] ? a['brand'] : "");
  r.cell['brand'].setAttribute('refid',a['brand_id']);
  
  if(r.getAttribute('type') == 'article')
   r.cell['description'].setValue(getInsArtDescription(a));
  else
   r.cell['description'].setValue(a['name']);

  r.cell['qty'].setValue(qty ? qty : 1);
  r.cell['weight'].setValue(a['weight'] ? a['weight']+" "+a['weightunits'] : "");

  if(!IS_VENDOR && a['custompricing'])
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

  if(IS_VENDOR)
  {
   if(a['vendor_id'] == subjectId)
   {
    r.cell['vendorprice'].setValue(a['vendor_price']);
    r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
   }
   else
   {
    r.cell['vendorprice'].setValue(0);
    r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(0,4);
   }
  }
  else
  {
   r.cell['vendorprice'].setValue(a['vendor_price']);
   r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
  }
  r.cell['unitprice'].setValue(a['variant_type'] ? a['variant_finalprice'] : a['finalprice']);
  r.cell['unitprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['variant_type'] ? a['variant_finalprice'] : a['finalprice'],4);
  r.cell['plbaseprice'].setValue(a['baseprice']);
  r.cell['plbaseprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['baseprice'],4);
  r.cell['plmrate'].setValue(a['pricelist_'+CUSTPLID+'_mrate'] ? a['pricelist_'+CUSTPLID+'_mrate'] : 0);
  r.cell['pldiscperc'].setValue(a['pricelist_'+CUSTPLID+'_discount'] ? a['pricelist_'+CUSTPLID+'_discount'] : 0);

  if(IS_VENDOR)
  {
   if(a['vendor_id'] == subjectId)
   {
    r.cell['vat'].setValue(SUBJECT_VATID ? SUBJECT_VATRATE : a['vendor_vatrate']);
	if(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']])
	{
	 r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']]['code']);
	 r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']]['name']);
	}

    r.cell['price'].setValue(a['vendor_price']);
    r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
   }
   else
   {
    r.cell['vat'].setValue(SUBJECT_VATID ? SUBJECT_VATRATE : a['vat']);
	if(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']])
	{
	 r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['code']);
	 r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['name']);
	}
    r.cell['price'].setValue(0);
    r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(0,4);
   }
  }
  else
  {
   r.cell['vat'].setValue(SUBJECT_VATID ? SUBJECT_VATRATE : a['vat']);
   if(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']])
   {
	r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['code']);
	r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['name']);
   }
   r.cell['price'].setValue(a['variant_type'] ? a['variant_finalprice'] : a['finalprice']);
   r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['variant_type'] ? a['variant_finalprice'] : a['finalprice'],4);
  }
  r.cell['pricelist'].setValue(a['pricelist_name']);
  r.cell['pricelist'].setAttribute("pricelistid",a['pricelist_id']);
  r.cell['vendorname'].setValue(a['vendor_name']);

  // variants //
  if(a && a['variants'])
  {
   // colori e tinte //
   var options = new Array();
   if(a['variants']['colors'])
   {
	var arr = new Array();
	for(var c=0; c < a['variants']['colors'].length; c++)
	 arr.push(a['variants']['colors'][c]['name']);
	options.push(arr);
   }
   if(a['variants']['tint'])
   {
	var arr = new Array();
	for(var c=0; c < a['variants']['tint'].length; c++)
	 arr.push(a['variants']['tint'][c]['name']);
	options.push(arr);
   }
   r.cell['coltint'].setOptions(options);

   // taglie, misure e altro //
   var options = new Array();
   if(a['variants']['sizes'])
   {
	var arr = new Array();
	for(var c=0; c < a['variants']['sizes'].length; c++)
	 arr.push(a['variants']['sizes'][c]['name']);
	options.push(arr);
   }
   if(a['variants']['dim'])
   {
	var arr = new Array();
	for(var c=0; c < a['variants']['dim'].length; c++)
	 arr.push(a['variants']['dim'][c]['name']);
	options.push(arr);
   }
   if(a['variants']['other'])
   {
	var arr = new Array();
	for(var c=0; c < a['variants']['other'].length; c++)
	 arr.push(a['variants']['other'][c]['name']);
	options.push(arr);
   }
   r.cell['sizmis'].setOptions(options);
  } 

  if(data['variant_type'] && data['variant_name'])
  {
   switch(data['variant_type'])
   {
	case 'color' : case 'tint' : r.cell['coltint'].setValue(data['variant_name']); break;
	case 'size' : case 'dim' : case 'other' : r.cell['sizmis'].setValue(data['variant_name']); break;
   }
  }

  updateTotals(r);
  DOCISCHANGED = true;
 }

 var cmd = "commercialdocs getfullinfo -ap `"+data['tb_prefix']+"` -id `"+data['id']+"` -subjectid `"+subjectId+"` -pricelistid `"+CUSTPLID+"` --get-variants -qty '"+r.cell['qty'].getValue()+"'";

 if(data['variant_name'] && data['variant_type'])
  cmd+= " -varname `"+data['variant_name']+"` -vartype '"+data['variant_type']+"'";

 sh.sendCommand(cmd);
}

function BrowseCatalog()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a || !a.length) return;
	 for(var c=0; c < a.length; c++)
	 {
	  var r = tb.AddRow();
	  r.setAttribute('type','article');

	  // create icon
	  var icon = document.createElement("IMG");
	  icon.src = ABSOLUTE_URL+"GCommercialDocs/img/article-mini.png";
	  icon.title = "Clicca per vedere la scheda di questo articolo";
	  icon.onclick = function(){showProduct(this);}
	  icon.style.marginLeft = "5px";
	  r.cells[0].appendChild(icon);

	  updateRowFromData(r,a[c],a[c]['qty']);
	 }

	}
 sh.sendCommand("gframe -f gmart/product-finder");
}

function InsertMessage(li)
{
 var pos = _getObjectPosition(li);

 var msgdiv = document.getElementById("predefmsg");
 msgdiv.style.left = pos['x']-272;
 msgdiv.style.top = pos['y']+28;
 msgdiv.style.display = "";
}

function InsertLastProduct(li)
{
 var pos = _getObjectPosition(li);

 var msgdiv = document.getElementById("lastproducts");
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
   case 'article' : case 'finalproduct' : case 'component' : case 'material' : case 'labor' : case 'book' : case 'service' : case 'supply' : sel.push(selected[c]); break;
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
	 var description = a['desc'] ? a['desc'] : "";
	 r.cells[1].colSpan=colSpan;
	 r.cells[1].setValue(description);
	 r.setAttribute('type','note');
	 idx++;
	 DOCISCHANGED = true;
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
    case 'finalproduct' : sh.sendCommand("dynarc item-info -ap `gproducts` -code `"+code+"`"); break;
    case 'component' : sh.sendCommand("dynarc item-info -ap `gpart` -code `"+code+"`"); break;
    case 'material' : sh.sendCommand("dynarc item-info -ap `gmaterial` -code `"+code+"`"); break;
    case 'labor' : sh.sendCommand("dynarc item-info -ap `glabor` -code `"+code+"`"); break;
    case 'book' : sh.sendCommand("dynarc item-info -ap `gbook` -code `"+code+"`"); break;
    case 'service' : sh.sendCommand("dynarc item-info -ap `gserv` -code `"+code+"`"); break;
    case 'supply' : sh.sendCommand("dynarc item-info -ap `gsupplies` -code `"+code+"`"); break;
   }
  }

 }

}

function updateTotals(r, aggiornaScadenze, callback)
{
 if(r)
 {
  var qty = parseFloat(r.cell['qty'].getValue());
  var extraQty = parseFloat(r.cell['extraqty'].getValue());
  if(extraQty)
   qty = qty*extraQty;

  var unitprice = IS_VENDOR ? parseCurrency(r.cell['vendorprice'].getValue()) : parseCurrency(r.cell['unitprice'].getValue());
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
   discount = parseCurrency(discStr);

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

  if(discStr == "100%")
   var total = 0;
  else
   var total = (unitprice-discount-discount2-discount3) * qty;
  var totalPlusVat = total ? total + ((total/100)*vat) : 0;

  r.cell['price'].setValue("<em>&euro;</em>"+formatCurrency(total,2),total);
  r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(total,4);
  r.cell['vatprice'].setValue("<em>&euro;</em>"+formatCurrency(totalPlusVat,2),totalPlusVat);
  r.cell['vatprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(totalPlusVat,4);

  if(total > 0)
  {
   var profit = total - (parseCurrency(r.cell['vendorprice'].getValue()) * qty);
   var margin = (profit / total) * 100;
   r.cell['profit'].setValue(formatCurrency(profit,DECIMALS),profit);
   r.cell['profit'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(profit,4);
   r.cell['margin'].setValue(roundup(margin,2)+"%");
  }
  else
  {
   r.cell['profit'].setValue(0);
   r.cell['profit'].getElementsByTagName('SPAN')[0].title = "Valore reale: 0";
   r.cell['margin'].setValue("");
  }
 }


 var VAT_RATES = new Array();
 var VATS = new Object();
 var totWeight = 0;
 var TOT_IMP_RITACC = 0;
 var TOT_IMP_CCP = 0;
 var PROFIT = 0;
 var TOT_PURCHASE_COSTS = 0;
 
 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];
  if((r.getAttribute('type') == "note") || (r.getAttribute('type') == "message"))
   continue;
  var qty = parseFloat(r.cell['qty'].getValue());
  var extraQty = parseFloat(r.cell['extraqty'].getValue());
  if(extraQty)
   qty = qty*extraQty;

  var discount = 0;
  var discount2 = 0;
  var discount3 = 0;
  var unitprice = IS_VENDOR ? parseCurrency(r.cell['vendorprice'].getValue()) : parseCurrency(r.cell['unitprice'].getValue());

  var discStr = r.cell['discount'].getValue();
  if(discStr.indexOf("%") > 0)
  {
   var disc = parseFloat(discStr);
   discount = unitprice ? (unitprice/100)*disc : 0;
  }
  else
   discount = parseCurrency(discStr);

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

  if(discStr == "100%")
   var amount = 0;
  else
   var amount = parseCurrency(r.cell['price'].getValue());

  var total = amount;

  if(SHOW_CONTRIBANDDEDUCTS && RIT_ACCONTO && r.cell['ritaccapply'].getValue())
   TOT_IMP_RITACC+= total;
  if(SHOW_CONTRIBANDDEDUCTS && CASSA_PREV && r.cell['ccpapply'].getValue())
   TOT_IMP_CCP+= total;

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
   vatInfo.amount+= roundup(amount,5);
  }
  else
  {
   return alert("Perfavore, ridigita la percentuale IVA");
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

  var prof = r.cell['profit'].getValue();
  if(prof)
   PROFIT+= roundup(parseCurrency(r.cell['profit'].getValue()),5);

  var vendorprice = r.cell['vendorprice'].getValue();
  if(vendorprice)
   TOT_PURCHASE_COSTS+= roundup(parseCurrency(r.cell['vendorprice'].getValue() * r.cell['qty'].getValue()),5);
 }

 /* UPDATE SUMMARY */
 var TOTALE_MERCE = 0;
 var TOTALE_SPESE = 0;
 var MERCE_SCONTATA = 0;
 var TOTALE_IMPONIBILE = 0;
 var TOTALE_IVA = 0;
 var TOTALE_IVA_ND = 0;
 var SCONTO_1 = document.getElementById("globaldisc_perc1").value ? parseFloat(document.getElementById("globaldisc_perc1").value) : 0;
 var SCONTO_2 = document.getElementById("globaldisc_perc2").value ? parseFloat(document.getElementById("globaldisc_perc2").value) : 0;
 var UNCONDITIONAL_DISCOUNT = document.getElementById("unconditional_discount").value ? parseCurrency(document.getElementById("unconditional_discount").value) : 0;
 var REBATE = document.getElementById("rebate").value ? parseCurrency(document.getElementById("rebate").value) : 0;
 var STAMP = document.getElementById("stamp").value ? parseCurrency(document.getElementById("stamp").value) : 0;
 var COLLECTION_CHARGES = document.getElementById("collection_charges").value ? parseCurrency(document.getElementById("collection_charges").value) : 0;
 var COEFF_RIPARTO = 0;
 var TOTALE_RIVALSA_INPS = 0;
 var TOTALE_RITENUTA_ACCONTO = 0;
 var TOTALE_ENASARCO = 0;
 var TOTALE_CASSA_PREV = 0;


 var vatRatesListTB = document.getElementById('totalscloud-vatlist');
 while(vatRatesListTB.rows.length > 1)
  vatRatesListTB.deleteRow(1);

 /* Calcola spese */
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

 /* Calcola spese trasporto e imballaggio */
 var cartage = parseCurrency(document.getElementById('cartage').value);
 var cartageVatId = parseCurrency(document.getElementById('cartage_vatid').value);
 var packingCharges = parseCurrency(document.getElementById('packing_charges').value);
 var packingChargesVatId = parseCurrency(document.getElementById('packing_charges_vatid').value);

 if(cartage)
 {
  if(VATS[cartageVatId])
   var vatInfo = VATS[cartageVatId];
  else
  {
   var vatInfo = {type:VAT_BY_ID[cartageVatId]['type'], rate:parseFloat(VAT_BY_ID[cartageVatId]['percentage']), amount:0, expenses:0};
   VATS[cartageVatId] = vatInfo;
  }
  vatInfo.expenses+= parseCurrency(cartage);
 }

 if(packingCharges)
 {
  if(VATS[packingChargesVatId])
   var vatInfo = VATS[packingChargesVatId];
  else
  {
   var vatInfo = {type:VAT_BY_ID[packingChargesVatId]['type'], rate:parseFloat(VAT_BY_ID[packingChargesVatId]['percentage']), amount:0, expenses:0};
   VATS[packingChargesVatId] = vatInfo;
  }
  vatInfo.expenses+= parseCurrency(packingCharges);
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
  if(SHOW_CONTRIBANDDEDUCTS && RIVALSA_INPS)
  {
   var rivinps = value ? (value/100)*RIVALSA_INPS : 0;
   TOTALE_RIVALSA_INPS+= rivinps;
   value+= rivinps;
  }

  // arrotondo alla cifra decimale //
  value = roundup(value,5);
  
  TOTALE_IMPONIBILE+=value;

  r.cells[2].innerHTML = formatCurrency(value)+" &euro;";
  VATS[vid].discounted = value;

  // calcolo l'IVA
  var vat = value ? (value/100)*VATS[vid].rate : 0;
  if(vat)
   vat = vat+0.00001;
  vat = roundup(vat,5);
  r.cells[3].innerHTML = formatCurrency(vat)+" &euro;";
  switch(VATS[vid].type)
  {
   case 'TAXABLE' : TOTALE_IVA+= vat; break;
   case 'PURCH_EXEUR' : case 'PURCH_INEUR' : case 'SPLIT_PAYMENT' : case 'REVERSE_CHARGE' : {
	 TOTALE_IVA+= vat;
	 TOTALE_IVA_ND+= vat; 
	} break;
  }

  VATS[vid].vat = vat;
 }
 
 /* CALCOLA LA MERCE SCONTATA */
 if(SCONTO_1)
  MERCE_SCONTATA = TOTALE_MERCE - ((TOTALE_MERCE/100)*SCONTO_1);
 else
  MERCE_SCONTATA = TOTALE_MERCE;

 MERCE_SCONTATA = roundup(MERCE_SCONTATA,5);

 /* CALCOLA LA CASSA PREV */
 if(SHOW_CONTRIBANDDEDUCTS && CASSA_PREV)
 {
  //var imp = MERCE_SCONTATA - UNCONDITIONAL_DISCOUNT;
  var imp = TOT_IMP_CCP;
  TOTALE_CASSA_PREV = imp ? (imp/100)*CASSA_PREV : 0;
  TOTALE_CASSA_PREV = roundup(TOTALE_CASSA_PREV,DECIMALS);
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
   vat = roundup(vat,DECIMALS);
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
    vat = roundup(vat,DECIMALS);
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


 /* CALCOLO LA RITENUTA D'ACCONTO */
 if(SHOW_CONTRIBANDDEDUCTS && RIT_ACCONTO)
 {
  //var imp = MERCE_SCONTATA - UNCONDITIONAL_DISCOUNT;
  var imp = TOT_IMP_RITACC;
  if(RIT_ACCONTO_RIVINPSINC)
   imp = imp ? (((imp+TOTALE_RIVALSA_INPS)/100)*RIT_ACCONTO_PERCIMP) : 0;
  else
   imp = imp ? ((imp/100)*RIT_ACCONTO_PERCIMP) : 0;
  TOTALE_RITENUTA_ACCONTO = imp ? (imp/100)*RIT_ACCONTO : 0;
  TOTALE_RITENUTA_ACCONTO = roundup(TOTALE_RITENUTA_ACCONTO,DECIMALS);
 }
 document.getElementById("summary-ritenuta-acconto").innerHTML = formatCurrency(TOTALE_RITENUTA_ACCONTO)+" &euro;";
 if(document.getElementById("doctot-ritacconto"))
  document.getElementById("doctot-ritacconto").innerHTML = "<em>&euro;</em>"+formatCurrency(TOTALE_RITENUTA_ACCONTO);
 else if(document.getElementById("doctot-stamp"))
  document.getElementById("doctot-stamp").innerHTML = "<em>&euro;</em>"+formatCurrency(STAMP);

 /* CALCOLO ENASARCO */
 if(SHOW_CONTRIBANDDEDUCTS && RIT_ENASARCO)
 {
  var imp = MERCE_SCONTATA - UNCONDITIONAL_DISCOUNT;
  imp = imp ? ((imp/100)*RIT_ENASARCO_PERCIMP) : 0;
  TOTALE_ENASARCO = imp ? (imp/100)*RIT_ENASARCO : 0;
  TOTALE_ENASARCO = roundup(TOTALE_ENASARCO,DECIMALS);
 }
 document.getElementById("summary-enasarco").innerHTML = formatCurrency(TOTALE_ENASARCO)+" &euro;";

 /* ARROTONDO */
 TOTALE_MERCE = roundup(TOTALE_MERCE, 2);
 MERCE_SCONTATA = roundup(MERCE_SCONTATA, 2);
 TOTALE_IMPONIBILE = roundup(TOTALE_IMPONIBILE, 2);
 TOTALE_IVA = roundup(TOTALE_IVA, 2);
 TOTALE_IVA_ND = roundup(TOTALE_IVA_ND, 2);
 TOTALE_SPESE = roundup(TOTALE_SPESE, 2);
 TOTALE_RITENUTA_ACCONTO = roundup(TOTALE_RITENUTA_ACCONTO, 2);
 TOTALE_ENASARCO = roundup(TOTALE_ENASARCO, 2);
 TOTALE_CASSA_PREV = roundup(TOTALE_CASSA_PREV, 2);
 REBATE = roundup(REBATE, 2);
 STAMP = roundup(STAMP, 2);
 COLLECTION_CHARGES = roundup(COLLECTION_CHARGES, 2);
 TOTALE_RIVALSA_INPS = roundup(TOTALE_RIVALSA_INPS, 2);
 UNCONDITIONAL_DISCOUNT = roundup(UNCONDITIONAL_DISCOUNT, 2);

 /* CALCOLO IL NETTO A PAGARE */
 NET_PAY = (TOTALE_IMPONIBILE+TOTALE_IVA-TOTALE_IVA_ND) - TOTALE_RITENUTA_ACCONTO - TOTALE_ENASARCO - REBATE + STAMP + COLLECTION_CHARGES;
 document.getElementById("summary-net-pay").innerHTML = formatCurrency(NET_PAY)+" &euro;";
 if(document.getElementById("doctot-netpay"))
  document.getElementById("doctot-netpay").innerHTML = "<em>&euro;</em>"+formatCurrency(NET_PAY);

 /* UPDATE SUMMARY ----------------------------------------------------------------------------------------------------*/
 document.getElementById("summary-total-goods").innerHTML = formatCurrency(TOTALE_MERCE)+" &euro;";
 document.getElementById("summary-total-expenses").innerHTML = formatCurrency(TOTALE_SPESE)+" &euro;";
 document.getElementById("summary-discounted-goods").innerHTML = formatCurrency(MERCE_SCONTATA)+" &euro;";
 document.getElementById("summary-unconditional-discount").innerHTML = formatCurrency(UNCONDITIONAL_DISCOUNT)+" &euro;";
 document.getElementById("summary-taxable").innerHTML = formatCurrency(TOTALE_IMPONIBILE)+" &euro;";
 document.getElementById("summary-total-vat").innerHTML = formatCurrency(TOTALE_IVA)+" &euro;";
 document.getElementById("summary-notpay-vat").innerHTML = formatCurrency(TOTALE_IVA_ND)+" &euro;";
 document.getElementById("summary-total-invoice").innerHTML = formatCurrency(TOTALE_IMPONIBILE+TOTALE_IVA)+" &euro;";
 document.getElementById("summary-rebate").innerHTML = formatCurrency(REBATE)+" &euro;";
 document.getElementById("summary-stamp").innerHTML = formatCurrency(STAMP)+" &euro;";
 document.getElementById("summary-collection-charges").innerHTML = formatCurrency(COLLECTION_CHARGES)+" &euro;";
 if(document.getElementById("doctot-collectioncharges"))
  document.getElementById("doctot-collectioncharges").innerHTML = "<em>&euro;</em>"+formatCurrency(COLLECTION_CHARGES);
 if(document.getElementById("doctot-stamp"))
  document.getElementById("doctot-stamp").innerHTML = "<em>&euro;</em>"+formatCurrency(STAMP);

 document.getElementById("summary-rivinps").innerHTML = formatCurrency(TOTALE_RIVALSA_INPS)+" &euro;";
 if(document.getElementById("doctot-rivinps"))
  document.getElementById("doctot-rivinps").innerHTML = "<em>&euro;</em>"+formatCurrency(TOTALE_RIVALSA_INPS);


 document.getElementById("doctot-amount").innerHTML = "<em>&euro;</em>"+formatCurrency(TOTALE_IMPONIBILE);
 document.getElementById("doctot-vat").innerHTML = "<em>&euro;</em>"+formatCurrency(TOTALE_IVA);
 document.getElementById("doctot-purchasecosts").innerHTML = "<em>&euro;</em>"+formatCurrency(TOT_PURCHASE_COSTS);
 document.getElementById("doctot-total").innerHTML = "<em>&euro;</em>"+formatCurrency(TOTALE_IMPONIBILE+TOTALE_IVA);
 document.getElementById("doctot-discount").innerHTML = "<em>&euro;</em>"+formatCurrency(TOTALE_MERCE - MERCE_SCONTATA + UNCONDITIONAL_DISCOUNT);
 document.getElementById("doctot-profit").innerHTML = "<em>&euro;</em>"+formatCurrency(PROFIT);

 if(TOTALE_IVA_ND > 0)
 {
  document.getElementById("doctot-notpayvat").innerHTML = "<em>&euro;</em>"+formatCurrency(TOTALE_IVA_ND);
  document.getElementById("doctot-notpayvat").style.display = "";
  document.getElementById("doctot-notpayvat-th").style.display = "";
 }
 else
 {
  document.getElementById("doctot-notpayvat").innerHTML = "<em>&euro;</em>"+formatCurrency(0);
  document.getElementById("doctot-notpayvat").style.display = "none";
  document.getElementById("doctot-notpayvat-th").style.display = "none";
 }

 /* EOF UPDATE SUMMARY ------------------------------------------------------------------------------------------------*/
 document.getElementById('doc_tot').innerHTML = formatCurrency(NET_PAY);
 document.getElementById('summary_doc_tot').innerHTML = formatCurrency(NET_PAY);
 document.getElementById('doctot-weight').innerHTML = totWeight;

 /* AGGIORNO LE SCADENZE */
 if(aggiornaScadenze != false)
  _paymentModeSelectChange(document.getElementById('paymentmode-select'), callback);
 else if(callback)
  callback();
}

function _setCollectionCharges(ed)
{
 ed.value=formatCurrency(parseCurrency(ed.value));
 updateTotals();
}

function _paymentModeSelectChange(sel, callback, updateOtherDatas)
{
 if(sel.selectedIndex < 0)
  return;

 if(PMSC_ONPROC)
 {
  if(PMSC_ONPROC_T)
   window.clearTimeout(PMSC_ONPROC_T);
  PMSC_ONPROC_T = window.setTimeout(function(){_paymentModeSelectChange(sel);}, 2000);
  return;
 }

 DOCISCHANGED = true;

 if(updateOtherDatas)
 {
  var opt = sel.options[sel.selectedIndex];
  var collectionCharges = parseFloat(opt.getAttribute('collcharges'));
  document.getElementById('collection_charges').value = formatCurrency(collectionCharges);
 }

 updateTotals(null, false);


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
	 PMSC_ONPROC = false;
	 if(callback) callback();
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
 PMSC_ONPROC = true;
 sh.sendCommand("accounting paymentmodeinfo -id `"+sel.value+"` -amount '"+amount+"' -from '"+docdate+"' --get-deadlines");
}

function deleteDoc()
{
 if(!confirm("Sei sicuro di voler eliminare questo documento?"))
  return;
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(){
	 //if(window.opener && window.opener.document && (window.opener.document.location.href.indexOf("GCommercialDocs/") > 0))
	 if(window.opener && window.opener.document)
	 {
	  window.opener.document.location.reload();
	  window.close();
	 }
	 else
	  document.location.href="index.php?ct="+CAT_TAG.toLowerCase()+"&catid=<?php echo $docInfo['cat_id']; ?>";
	}
 sh.sendCommand("dynarc delete-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>`");
}

function abort()
{
 if(DOCISCHANGED && !LOCKED)
 {
  if(confirm("Il documento è stato modificato. Salvare le modifiche prima di uscire?"))
  {
   POSTSAVEACTION = "abort";
   return saveDoc();
  }
 }

 var DOC_STATUS = <?php echo $docInfo['status'] ? $docInfo['status'] : '0'; ?>;

 /* Auto-close DDT */
 if((CAT_TAG == "DDT") && AUTO_CLOSE_DDT && (DOC_STATUS < 7))
 {
  if(confirm("Desideri chiudere questo DDT ?"))
  {
   var sh = new GShell();
   sh.OnOutput = function(o,a){
	 var sh2 = new GShell();
	 sh2.OnError = function(err){alert(err);}
	 sh2.OnOutput = function(){
		 // close window
		 if(window.opener && window.opener.document && (window.opener.document.location.href.indexOf("GCommercialDocs/") > 0))
		 {
		  window.opener.document.location.reload();
		  window.setTimeout(function(){window.close();}, 1000);
		 }
		 else
		  document.location.href="index.php?ct="+CAT_TAG.toLowerCase()+"&catid=<?php echo $docInfo['cat_id']; ?>";
		 // eof - close window
		}
	 sh2.sendCommand("dynarc edit-item -ap commercialdocs -id `<?php echo $docInfo['id']; ?>` -extset `cdinfo.status=7,cdelements.setallsent=true`");
	}
   sh.sendCommand("gframe -f commercialdocs/downloadstore -params `id=<?php echo $docInfo['id']; ?>`");
   return;
  }
 }
 /* EOF - auto-close DDT */

 if(window.opener && window.opener.document)
 {
  if(window.opener.document.location.href.indexOf("GCommercialDocs/") > 0)
   window.close();
  else
  {
   window.opener.document.location.reload();
   window.close();
  }
 }
 else
  document.location.href="index.php?ct="+CAT_TAG.toLowerCase()+"&catid="+CAT_ID+(CAT_ID != ROOT_CAT_ID ? "&show=category" : "");
}

function editDocTag(li)
{
 var causalID = li.getAttribute('refid');
 var causalName = li.innerHTML;

 document.getElementById('doctagname').getElementsByTagName('SPAN')[0].innerHTML = causalName+"<img src='"+ABSOLUTE_URL+"GCommercialDocs/img/tiptop-dnarr.png' class='ddmenu'/ >";
 document.getElementById('doctagname').setAttribute('refid',causalID);

 DOC_TAG = causalID;
 DOCISCHANGED = true;

 if(DOC_TAG == "DDR") IS_VENDOR = true;
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
 if(ON_SAVE)
  return alert("Salvataggio in corso... attendere prego!");

 ON_SAVE = true;
 CUSTOM_PRICES = new Array();
 VENDOR_PRICES = new Array();

 if(UNKNOWN_ELEMENTS.length)
 {
  var products = new Array();
  var services = new Array();
  var supplies = new Array();

  for(var c=0; c < UNKNOWN_ELEMENTS.length; c++)
  {
   switch(UNKNOWN_ELEMENTS[c].getAttribute('type'))
   {
    case 'article' : case 'finalproduct' : case 'component' : case 'material' : case 'labor' : case 'book' : products.push(UNKNOWN_ELEMENTS[c]); break;
	case 'service' : services.push(UNKNOWN_ELEMENTS[c]); break;
	case 'supply' : supplies.push(UNKNOWN_ELEMENTS[c]); break;
   }
  }

  if(products.length)
  {
   var xml = "<xml><items>";
   for(var c=0; c < products.length; c++)
   {
	xml+= "<item type=\""+products[c].getAttribute('type')+"\" code=\""+products[c].cell['code'].getValue()+"\" name=\""+products[c].cell['description'].getValue()+"\"";
	xml+= " qty=\""+products[c].cell['qty'].getValue()+"\" units=\""+products[c].cell['units'].getValue()+"\"";
	xml+= " weight=\""+parseFloat(products[c].cell['weight'].getValue())+"\" weightunits=\""+products[c].getAttribute('weightunits')+"\"";
	xml+= " vendorprice=\""+products[c].cell['vendorprice'].getValue()+"\" unitprice=\""+products[c].cell['unitprice'].getValue()+"\" vat=\""+products[c].cell['vat'].getValue()+"\"";
	xml+= " vencode=\""+products[c].cell['vencode'].getValue()+"\" mancode=\""+products[c].cell['mancode'].getValue()+"\"";
	xml+= " brandid=\""+products[c].cell['brand'].getAttribute('refid')+"\" brand=\""+products[c].cell['brand'].getValue()+"\"";

	xml+= "/"+">"; // <-- close node
   }
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
	   if(newelements[c])
	   {
	    switch(products[c].getAttribute('type'))
	    {
	     case 'article' : products[c].setAttribute('refap',"gmart"); break;
	     case 'finalproduct' : products[c].setAttribute('refap',"gproducts"); break;
	     case 'component' : products[c].setAttribute('refap',"gpart"); break;
	     case 'material' : products[c].setAttribute('refap',"gmaterial"); break;
	     case 'labor' : products[c].setAttribute('refap',"glabor"); break;
	     case 'book' : products[c].setAttribute('refap',"gbook"); break;
	    }
	   }
	  }
	 }
	 ON_SAVE = false;
	 return saveDoc();
	}

   var vendorId = 0;
   var vendorName = "";

   switch(CAT_TAG)
   {
	case 'VENDORORDERS' : case 'PURCHASEINVOICES' : case 'DDTIN' : {
		 var subjInfo = getCurrentSubjectInfo();
		 vendorId = subjInfo ? subjInfo['id'] : 0;
		 vendorName = subjInfo ? subjInfo['name'] : "";
		} break;
   }
   

   sh.sendCommand("gframe -f commercialdocs/register-unknown-elements -xpn xmlelements -xpv `"+xml+"` -xpn docct -xpv `"+CAT_TAG+"` -xpn docid -xpv `<?php echo $docInfo['id']; ?>`"+(vendorId ? " -xpn vendorid -xpv `"+vendorId+"` -xpn vendorname -xpv `"+vendorName+"`" : ""));
   return;
  }

 }

 var alias = document.getElementById('aliasname').value;
 var docRefAP = InternalDocRef.getAttribute('refap');
 var docRefID = InternalDocRef.getAttribute('refid');
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

 var freq = document.getElementById('freq') ? document.getElementById('freq').value : 0;
 var finishDate = (document.getElementById('finish_date') && document.getElementById('finish_date').value) ? strdatetime_to_iso(document.getElementById('finish_date').value).substr(0,10) : "";

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
 var ourbankSupportId = document.getElementById('ourbanksupport-select').value;

 /* Shipping */
 if(document.getElementById("ship-subject").data)
  var ship_subject_id = document.getElementById("ship-subject").data['id'];
 else
  var ship_subject_id = 0;
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
 var cartage = parseCurrency(document.getElementById('cartage').value);
 var cartageVatId = parseCurrency(document.getElementById('cartage_vatid').value);
 var packingCharges = parseCurrency(document.getElementById('packing_charges').value);
 var packingChargesVatId = parseCurrency(document.getElementById('packing_charges_vatid').value);
 var collectionCharges = parseCurrency(document.getElementById('collection_charges').value);

 var referenceId = document.getElementById('referencelist').value;

 var validityDate = "";
 var charterDateFrom = "";
 var charterDateTo = "";
 var location = document.getElementById("location").value.E_QUOT();
 var trackingNumber = document.getElementById("tracking_number").value.E_QUOT();
 var deliveryDate = document.getElementById('deliverydate').value ? strdatetime_to_iso(document.getElementById('deliverydate').value).substr(0,10) : "";

 var pa_docType = "";
 var pa_docNum = "";
 var pa_cig = "";
 var pa_cup = "";

 if(SUBJECT_TYPE == 2)
 {
  pa_docNum = document.getElementById('pa-docnum').value;
  pa_cig = document.getElementById('pa-cig').value;
  pa_cup = document.getElementById('pa-cup').value;
 }

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
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(o,a){
	 var sh2 = new GShell();
	 sh2.OnError = function(err){SAVE_SH.processMessage.error(err);}
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
	  if((r.unitpriceChanged || r.discountChanged) && (CAT_TAG != "VENDORORDERS") && (CAT_TAG != "PURCHASEINVOICES") && (CAT_TAG != "DDTIN") && !LOCK_AUTOSAVE_CUSTOMPRICING)
	  {
	   var discount = ((r.cell['discount'].getValue().indexOf("%") > 0) ? r.cell['discount'].getValue() : parseCurrency(r.cell['discount'].getValue()));
	   var discount2 = r.cell['discount2'].getValue() ? parseFloat(r.cell['discount2'].getValue()) : 0;
	   var discount3 = r.cell['discount3'].getValue() ? parseFloat(r.cell['discount3'].getValue()) : 0;
	   if(!discount) discount=0;
	   if(r.getAttribute('refap') && r.getAttribute('refid'))
		CUSTOM_PRICES.push({
			ap: r.getAttribute('refap'),
			id: r.getAttribute('refid'),
			code: r.cell['code'].getValue(),
			desc: r.cell['description'].getValue(),
			baseprice: parseCurrency(r.cell['unitprice'].getValue()),
			disc: discount,
			disc2: discount2,
			disc3: discount3
		});
	  }
	  else if(r.vendorpriceChanged && ((CAT_TAG == "DDTIN") || (CAT_TAG == "VENDORORDERS") || (CAT_TAG == "PURCHASEINVOICES")))
	  {
	   if(r.getAttribute('refap') && r.getAttribute('refid'))
		VENDOR_PRICES.push({
			ap: r.getAttribute('refap'),
			id: r.getAttribute('refid'),
			code: r.cell['code'].getValue(),
			vencode: r.cell['vencode'].getValue(),
			desc: r.cell['description'].getValue(),
			baseprice: parseCurrency(r.cell['vendorprice'].getValue()),
			vat: parseCurrency(r.cell['vat'].getValue())
		});
	  }
	 }
	 for(var c=0; c < NEW_ROWS.length; c++)
	 {
	  var r = NEW_ROWS[c];
	  var vatId = r.getAttribute('vatid') ? r.getAttribute('vatid') : 0;
	  var vatType = r.getAttribute('vattype') ? r.getAttribute('vattype') : "";
	  saveElement(r,sh2);
	  if((r.unitpriceChanged || r.discountChanged) && (CAT_TAG != "VENDORORDERS") && (CAT_TAG != "PURCHASEINVOICES") && !LOCK_AUTOSAVE_CUSTOMPRICING)
	  {
	   var discount = ((r.cell['discount'].getValue().indexOf("%") > 0) ? r.cell['discount'].getValue() : parseCurrency(r.cell['discount'].getValue()));
	   var discount2 = r.cell['discount2'].getValue() ? parseFloat(r.cell['discount2'].getValue()) : 0;
	   var discount3 = r.cell['discount3'].getValue() ? parseFloat(r.cell['discount3'].getValue()) : 0;

	   if(!discount) discount=0;
	   if(r.getAttribute('refap') && r.getAttribute('refid'))
		CUSTOM_PRICES.push({
			ap: r.getAttribute('refap'),
			id: r.getAttribute('refid'),
			code: r.cell['code'].getValue(),
			desc: r.cell['description'].getValue(),
			baseprice: parseCurrency(r.cell['unitprice'].getValue()),
			disc: discount,
			disc2: discount2,
			disc3: discount3
		});
	  }
	  else if(r.vendorpriceChanged && ((CAT_TAG == "DDTIN") || (CAT_TAG == "VENDORORDERS") || (CAT_TAG == "PURCHASEINVOICES")))
	  {
	   if(r.getAttribute('refap') && r.getAttribute('refid'))
		VENDOR_PRICES.push({
			ap: r.getAttribute('refap'),
			id: r.getAttribute('refid'),
			code: r.cell['code'].getValue(),
			vencode: r.cell['vencode'].getValue(),
			desc: r.cell['description'].getValue(),
			baseprice: parseCurrency(r.cell['vendorprice'].getValue()),
			vat: parseCurrency(r.cell['vat'].getValue())
		});
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

 var extraqry = "";
 if((CAT_TAG == "ORDERS") && SCHEDULE_EXTENSION_INSTALLED)
 {
  var date = new Date();
  date.setFromISO(docDateISO);
  if(freq)
   date.NextMonth(freq);
  extraqry+= ",schedule.freq='"+freq+"',finish-date='"+finishDate+"',next-expiry='"+date.printf('Y-m-d')+"'";
 }

 SAVE_SH = sh;
 sh.showProcessMessage("Salvataggio in corso", "Attendere prego, è in corso il salvataggio del documento");

 sh.sendCommand("dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -desc `"+notes+"` -code-num `"+docNum+"` -code-ext `"+docExt+"` -ctime `"+docDateISO+"` -alias `"+alias+"` -extset `cdinfo."+(subjectId ? "subjectid="+subjectId : "subject='''"+subjectName+"'''")+",referenceid='"+referenceId+"',agent_id='"+AGENT_ID+"',tag='"+DOC_TAG+"',division='"+DOC_DIVISION+"',location='''"+location+"''',tracknum='''"+trackingNumber+"''',delivery-date='"+deliveryDate+"',pricelist='"+pricelistId+"',paymentmode='"+paymentMode+"',banksupport='"+bankSupportId+"',ourbanksupport='"+ourbankSupportId+"',ship-contact-id='"+ship_contact_id+"',ship-subject-id='"+ship_subject_id+"',ship-recp='''"+ship_recp+"''',ship-addr='''"+ship_addr+"''',ship-city='''"+ship_city+"''',ship-zip='"+ship_zip+"',ship-prov='"+ship_prov+"',ship-cc='"+ship_cc+"',trans-method='"+trans_method+"',trans-shipper='''"+trans_shipper+"''',trans-numplate='"+trans_numplate+"',trans-causal='''"+trans_causal+"''',trans-datetime='"+trans_date+"',trans-aspect='''"+trans_aspect+"''',trans-num='"+trans_num+"',trans-weight='''"+trans_weight+"''',trans-freight='''"+trans_freight+"''',validity-date='"+validityDate+"',charter-datefrom='"+charterDateFrom+"',charter-dateto='"+charterDateTo+"',extdocref='''"+(extdocrefEd ? extdocrefEd.value : "")+"''',discount1='"+SCONTO_1+"',discount2='"+SCONTO_2+"',uncondisc='"+UNCONDITIONAL_DISCOUNT+"',rebate='"+REBATE+"',stamp='"+STAMP+"',cartage='"+cartage+"',cartage-vatid='"+cartageVatId+"',packing-charges='"+packingCharges+"',packing-charges-vatid='"+packingChargesVatId+"',collection-charges='"+collectionCharges+"',docrefap='"+docRefAP+"',docrefid='"+docRefID+"',statusextra='"+STATUS_EXTRA+"',rivalsa-inps='"+RIVALSA_INPS+"',contr-cassa-prev='"+CASSA_PREV+"',contr-cassa-prev-vatid='"+CASSA_PREV_VATID+"',rit-enasarco='"+RIT_ENASARCO+"',rit-enasarco-percimp='"+RIT_ENASARCO_PERCIMP+"',rit-acconto='"+RIT_ACCONTO+"',rit-acconto-percimp='"+RIT_ACCONTO_PERCIMP+"',rit-acconto-rivinpsinc='"+RIT_ACCONTO_RIVINPSINC+"',padoctype='"+pa_docType+"',padocnum='"+pa_docNum+"',pacig='"+pa_cig+"',pacup='"+pa_cup+"'"+expqry+extraqry+"`");
}

function saveElement(r,sh,id)
{
 var vatId = r.getAttribute('vatid') ? r.getAttribute('vatid') : 0;
 var vatType = r.getAttribute('vattype') ? r.getAttribute('vattype') : "";
 var ritaccApply = (r.cell['ritaccapply'] && r.cell['ritaccapply'].getValue()) ? '1' : '0';
 var ccpApply = (r.cell['ccpapply'] && r.cell['ccpapply'].getValue()) ? '1' : '0';
 var cdelementsQry = id ? "id="+id+",type='"+r.getAttribute('type')+"'" : "type='"+r.getAttribute('type')+"'";

 if(r.getAttribute('row_ref_docap') && r.getAttribute('row_ref_docid') && r.getAttribute('row_ref_id'))
 {
  cdelementsQry+= ",rowdocap='"+r.getAttribute('row_ref_docap')+"',rowdocid='"+r.getAttribute('row_ref_docid')+"',rowrefid='"+r.getAttribute('row_ref_id')+"'";
 }

 switch(r.getAttribute('type'))
 {
  case 'note' : case 'message' : {
	 var spanlist = r.cells[1].getElementsByTagName('SPAN');
	 cdelementsQry+= ",desc='''"+(spanlist[1] ? spanlist[1].innerHTML : spanlist[0].innerHTML)+"''',ordering="+r.rowIndex; 
	} break;
  default : {
	 var discount = ((r.cell['discount'].getValue().indexOf("%") > 0) ? r.cell['discount'].getValue() : parseCurrency(r.cell['discount'].getValue()));
	 var discount2 = r.cell['discount2'].getValue() ? parseFloat(r.cell['discount2'].getValue()) : 0;
	 var discount3 = r.cell['discount3'].getValue() ? parseFloat(r.cell['discount3'].getValue()) : 0;
	 var plbaseprice = r.cell['plbaseprice'].getValue() ? parseFloat(r.cell['plbaseprice'].getValue()) : 0;
	 var plmrate = r.cell['plmrate'].getValue() ? parseFloat(r.cell['plmrate'].getValue()) : 0;
	 var pldiscperc = r.cell['pldiscperc'].getValue() ? parseFloat(r.cell['pldiscperc'].getValue()) : 0;
	 var vendorId = r.getAttribute('vendorid') ? r.getAttribute('vendorid') : 0;
	 var coltint = r.cell['coltint'].getValue().E_QUOT();
	 var sizmis = r.cell['sizmis'].getValue().E_QUOT();
	 var metricLength = r.cell['metriclength'].getValue();
	 var metricWidth = r.cell['metricwidth'].getValue();
	 var metricHW = r.cell['metrichw'].getValue();
	 var metricEqP = r.cell['metriceqp'].getValue();

	 cdelementsQry+= ",refap='"+r.getAttribute('refap')+"',refid='"+r.getAttribute('refid')+"',code='"+r.cell['code'].getValue()+"'";
	 cdelementsQry+= ",vencode='"+r.cell['vencode'].getValue()+"',sn='"+r.cell['sn'].getValue()+"',lot='"+r.cell['lot'].getValue()+"'";
	 cdelementsQry+= ",brand_id='"+r.cell['brand'].getAttribute('brand_id')+"',brand='''"+r.cell['brand'].getValue()+"'''";
	 cdelementsQry+= ",name='''"+r.cell['description'].getValue()+"''',qty='"+r.cell['qty'].getValue()+"'";
	 cdelementsQry+= ",qtysent='"+r.cell['qty_sent'].getValue()+"'";
	 cdelementsQry+= ",extraqty='"+r.cell['extraqty'].getValue()+"',units='"+r.cell['units'].getValue()+"'";
	 cdelementsQry+= ",vendorprice='"+parseCurrency(r.cell['vendorprice'].getValue())+"'";
	 cdelementsQry+= ",saleprice='"+parseCurrency(r.cell['unitprice'].getValue())+"'";
	 cdelementsQry+= ",price='"+(IS_VENDOR ? parseCurrency(r.cell['vendorprice'].getValue()) : parseCurrency(r.cell['unitprice'].getValue()))+"'";
	 cdelementsQry+= ",priceadjust='"+parseCurrency(r.cell['priceadjust'].getValue())+"'";
	 cdelementsQry+= ",discount='"+discount+"',discount2='"+discount2+"',discount3='"+discount3+"'";
	 cdelementsQry+= ",vatrate='"+parseFloat(r.cell['vat'].getValue())+"',vatid='"+vatId+"',vattype='"+vatType+"'";
	 cdelementsQry+= ",pricelistid='"+r.cell['pricelist'].getAttribute('pricelistid')+"'";
	 cdelementsQry+= ",plbaseprice='"+plbaseprice+"',plmrate='"+plmrate+"',pldiscperc='"+pldiscperc+"',vendorid='"+vendorId+"'";
	 cdelementsQry+= ",coltint='''"+coltint+"''',sizmis='''"+sizmis+"'''";
	 cdelementsQry+= ",ritaccapply='"+ritaccApply+"',ccpapply='"+ccpApply+"',metriclength='"+metricLength+"',metricwidth='"+metricWidth+"',metrichw='"+metricHW+"',metriceqp='"+metricEqP+"'";

	 if(r.getAttribute('docrefap') && r.getAttribute('docrefid'))
	  cdelementsQry+= ",docrefap='"+r.getAttribute('docrefap')+"',docrefid='"+r.getAttribute('docrefid')+"'";
	 else
	  cdelementsQry+= ",docrefap='',docrefid=''";

	 if(typeof(r.xmldata) == "string")
	  cdelementsQry+= ",xmldata='''"+r.xmldata+"'''";

	 cdelementsQry+= !id ? ",ordering="+r.rowIndex : "";
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
  case 'agentinvoices' : case 'vendororders' : case 'purchaseinvoices' : case 'creditsnote' : case 'ddtin' : isdebit=true; break;
 }

 if(!NEW_DEPOSITS.length && !UPDATED_DEPOSITS.length && !DELETED_DEPOSITS.length && !NEW_RATES.length && !UPDATED_RATES.length && !DELETED_RATES.length)
  return saveFinish();

 var sh = new GShell();
 sh.OnError = function(err){
	 if(SAVE_SH)
	  SAVE_SH.processMessage.error(err);
	 else
	  alert(err);
	}
 sh.OnFinish = function(){saveFinish();}


 for(var c=0; c < NEW_DEPOSITS.length; c++)
 {
  var r = NEW_DEPOSITS[c];
  var incomes = parseCurrency(r.cells[0].getElementsByTagName('INPUT')[0].value);
  var paymentDate = strdatetime_to_iso(r.cells[1].getElementsByTagName('INPUT')[0].value).substr(0,10);
  sh.sendCommand("dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -extset `mmr."+(isdebit ? "expenses" : "incomes")+"='"+incomes+"',payment='"+paymentDate+"',description='Acconto',subject='''"+subjectName+"''',subjectid='"+subjectId+"'`");
 }

 for(var c=0; c < UPDATED_DEPOSITS.length; c++)
 {
  var r = UPDATED_DEPOSITS[c];
  var id = r.id.substr(4);
  var incomes = parseCurrency(r.cells[0].getElementsByTagName('INPUT')[0].value);
  var paymentDate = strdatetime_to_iso(r.cells[1].getElementsByTagName('INPUT')[0].value).substr(0,10);
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
  var expireDate = strdatetime_to_iso(r.cells[0].getElementsByTagName('INPUT')[0].value).substr(0,10);
  var incomes = parseCurrency(r.cells[1].getElementsByTagName('INPUT')[0].value);
  if(r.cells[2].getElementsByTagName('INPUT')[0].checked == true)
   var paymentDate = strdatetime_to_iso(r.cells[2].getElementsByTagName('INPUT')[1].value).substr(0,10);
  else
   var paymentDate = "";
  sh.sendCommand("dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -extset `mmr."+(isdebit ? "expenses" : "incomes")+"='"+incomes+"',payment='"+paymentDate+"',expire='"+expireDate+"',description='Rata n."+r.rowIndex+" scad. "+r.cells[0].getElementsByTagName('INPUT')[0].value+"',subject='''"+subjectName+"''',subjectid='"+subjectId+"'`");
 }

 for(var c=0; c < UPDATED_RATES.length; c++)
 {
  var r = UPDATED_RATES[c];
  var id = r.id.substr(4);
  var expireDate = strdatetime_to_iso(r.cells[0].getElementsByTagName('INPUT')[0].value).substr(0,10);
  var incomes = parseCurrency(r.cells[1].getElementsByTagName('INPUT')[0].value);
  if(r.cells[2].getElementsByTagName('INPUT')[0].checked == true)
   var paymentDate = strdatetime_to_iso(r.cells[2].getElementsByTagName('INPUT')[1].value).substr(0,10);
  else
   var paymentDate = "";

  sh.sendCommand("dynarc edit-item -ap `commercialdocs` -id `<?php echo $docInfo['id']; ?>` -extset `mmr.id="+id+","+(isdebit ? "expenses" : "incomes")+"='"+incomes+"',payment='"+paymentDate+"',expire='"+expireDate+"',description='Rata n."+r.rowIndex+" scad. "+r.cells[0].getElementsByTagName('INPUT')[0].value+"',subject='''"+subjectName+"''',subjectid='"+subjectId+"'`");
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
 sh.OnError = function(err){
	 if(SAVE_SH)
	  SAVE_SH.processMessage.error(err);
	 else
	  alert(err);
	}
 sh.OnOutput = function(){
	 NEW_DEPOSITS = new Array();
	 UPDATED_DEPOSITS = new Array();
	 DELETED_DEPOSITS = new Array();
	 NEW_RATES = new Array();
	 UPDATED_RATES = new Array();
	 DELETED_RATES = new Array();
	 SAVED = true;
	 DOCISCHANGED = false;
	 ON_SAVE = false;

	 if(SAVE_SH) SAVE_SH.hideProcessMessage();

	 if(CUSTOM_PRICES.length)
	  return saveCustomPricing();
	 else if(VENDOR_PRICES.length)
	  return saveVendorPricing();
	 else
	  alert('Il documento è stato salvato correttamente!');
	 document.getElementById('header-bar').style.visibility = "hidden"; // nascondo la header
	 this.showPopupMessage("Salvataggio completato!, attendere il ri-caricamento della pagina",15);

	 if(!POSTSAVEACTION || (POSTSAVEACTION == ""))
	  POSTSAVEACTION = "saved";

	 document.location.href = ABSOLUTE_URL+"GCommercialDocs/docinfo.php?id=<?php echo $docInfo['id']; ?>"+(POSTSAVEACTION ? "&postsaveaction="+POSTSAVEACTION : "")+(STRICT_AGENT ? "&strictagent=true&agentid="+AGENT_ID : "");
	}
 sh.sendCommand("commercialdocs updatetotals -id `<?php echo $docInfo['id']; ?>`");
}

function saveCustomPricing()
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

 var xml = "<xml><items>";
 for(var c=0; c < CUSTOM_PRICES.length; c++)
 {
  var itm = CUSTOM_PRICES[c];
  xml+= "<item ap=\""+itm.ap+"\" id=\""+itm.id+"\" code=\""+itm.code+"\" desc=\""+itm.desc+"\" baseprice=\""+itm.baseprice+"\" discount=\""+itm.disc+"\" discount2=\""+itm.disc2+"\" discount3=\""+itm.disc3+"\"/"+">";
 }
 xml+= "</items></xml>";

 var vendorId = 0;
 var vendorName = "";
 switch(CAT_TAG)
 {
  case 'VENDORORDERS' : case 'PURCHASEINVOICES' : case 'DDTIN' : {
		 var subjInfo = getCurrentSubjectInfo();
		 vendorId = subjInfo ? subjInfo['id'] : 0;
		 vendorName = subjInfo ? subjInfo['name'] : "";
		} break;
 }


 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 alert('Il documento è stato salvato correttamente!');
	 document.getElementById('header-bar').style.visibility = "hidden"; // nascondo la header
	 SAVE_SH.showPopupMessage("Salvataggio completato!, attendere il ri-caricamento della pagina",15);
	 //document.location.reload();
	 document.location.href = ABSOLUTE_URL+"GCommercialDocs/docinfo.php?id=<?php echo $docInfo['id']; ?>"+(POSTSAVEACTION ? "&postsaveaction="+POSTSAVEACTION : "")+(STRICT_AGENT ? "&strictagent=true&agentid="+AGENT_ID : "");

	}
 sh.sendCommand("gframe -f commercialdocs/register-custom-prices -params `vendorid="+vendorId+"` -xpn xmlelements -xpv `"+xml+"` -xpn subjectid -xpv `"+subjectId+"` -xpn subjectname -xpv `"+subjectName+"`"+(vendorId ? " -xpn vendorid -xpv `"+vendorId+"` -xpn vendorname -xpv `"+vendorName+"`" : ""));
}

function saveVendorPricing()
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

 var xml = "<xml><items>";
 for(var c=0; c < VENDOR_PRICES.length; c++)
 {
  var itm = VENDOR_PRICES[c];
  xml+= "<item ap=\""+itm.ap+"\" id=\""+itm.id+"\" code=\""+itm.code+"\" vencode=\""+itm.vencode+"\" desc=\""+itm.desc+"\" baseprice=\""+itm.baseprice+"\" vat=\""+itm.vat+"\"/"+">";
 }
 xml+= "</items></xml>";

 var vendorId = 0;
 var vendorName = "";
 switch(CAT_TAG)
 {
  case 'VENDORORDERS' : case 'PURCHASEINVOICES' : case 'DDTIN' : {
		 var subjInfo = getCurrentSubjectInfo();
		 vendorId = subjInfo ? subjInfo['id'] : 0;
		 vendorName = subjInfo ? subjInfo['name'] : "";
		} break;
 }


 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 alert('Il documento è stato salvato correttamente!');
	 document.getElementById('header-bar').style.visibility = "hidden"; // nascondo la header
	 SAVE_SH.showPopupMessage("Salvataggio completato!, attendere il ri-caricamento della pagina",15);
	 //document.location.reload();
	 document.location.href = ABSOLUTE_URL+"GCommercialDocs/docinfo.php?id=<?php echo $docInfo['id']; ?>"+(POSTSAVEACTION ? "&postsaveaction="+POSTSAVEACTION : "")+(STRICT_AGENT ? "&strictagent=true&agentid="+AGENT_ID : "");
	}
 sh.sendCommand("gframe -f commercialdocs/register-vendor-prices -params `vendorid="+vendorId+"` -xpn xmlelements -xpv `"+xml+"` -xpn vendorid -xpv `"+vendorId+"` -xpn vendorname -xpv `"+vendorName+"`");
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
 DOCISCHANGED = true;
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
 DOCISCHANGED = true;
}

function schedulePaidDateChange(ed)
{
 var r = ed.parentNode.parentNode;
 if(r.id && (UPDATED_RATES.indexOf(r) < 0))
  UPDATED_RATES.push(r);
 DOCISCHANGED = true;
}

function scheduleDateChange(ed)
{
 var r = ed.parentNode.parentNode;
 if(r.id && (UPDATED_RATES.indexOf(r) < 0))
  UPDATED_RATES.push(r);
 DOCISCHANGED = true;
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
 DOCISCHANGED = true;
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
 DOCISCHANGED = true;
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
   if(UPDATED_RATES.indexOf(r) > -1)
    UPDATED_RATES.splice(UPDATED_RATES.indexOf(r),1);
  }
  else if(NEW_RATES.indexOf(r) > -1)
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
  if(tbschedsmall.rows[c-1])
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
 DOCISCHANGED = true;
 return amount;
}

function depositDateChanged(ed)
{
 var r = ed.parentNode.parentNode;
 if(r.id && (UPDATED_DEPOSITS.indexOf(r) < 0))
  UPDATED_DEPOSITS.push(r);
 DOCISCHANGED = true;
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
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(a && a['items'])
	 {
	  if(confirm("Esiste già un documento con questo numero:\n"+a['items'][0]['name']+"\nDesideri modificarlo comunque?"))
	   document.getElementById('docnum').innerHTML = docNum+(docExt ? "/"+docExt : "");
	 }
	 else
	  document.getElementById('docnum').innerHTML = docNum+(docExt ? "/"+docExt : "");
	 DOCISCHANGED = true;
	}
 sh.sendCommand("dynarc item-list -ap `commercialdocs` -ct `"+CAT_TAG+"` -where `ctime>='"+date.getFullYear()+"-01-01' AND ctime<'"+(date.getFullYear()+1)+"-01-01' AND code_num='"+docNum+"' AND code_ext='"+docExt+"' AND id!='<?php echo $docInfo['id']; ?>'`");
}

function editDocDate()
{
 var docDate = prompt("Modifica data documento",document.getElementById('docdate').innerHTML);
 if(!docDate)
  return;
 DOCISCHANGED = true;
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
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 alert("Il documento è stato ripristinato!");
	 document.location.reload();
	}
 sh.sendCommand("dynarc trash restore -ap commercialdocs -id `<?php echo $docInfo['id']; ?>`");
}

function unlockDoc()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
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
 {
  var tag = tbcs.rows[0].cells[c].id;
  if((tag == "metriclength") || (tag == "metricwidth") || (tag == "metrichw"))
   continue;
  columns.push(tbcs.rows[0].cells[c]);
 }

 var list = ul.getElementsByTagName('LI');
 var xml = "";

 for(var c=0; c < columns.length; c++)
 {
  var col = columns[c];
  switch(col.id)
  {
   case 'metriceqp' : {
	 var cb = list[c].getElementsByTagName('INPUT')[0];
	 if(cb && cb.checked)
	  xml+= "<column tag=\"metric\" title=\"Computo metrico\"/"+">";
	} break;

   case 'metriclength' : case 'metricwidth' : case 'metrichw' : {
	} break;

   default : {
	 var cb = list[c].getElementsByTagName('INPUT')[0];
	 var col_tag = col.id;
	 var col_title = col.innerHTML;
	 var col_width = col.width;
	 if(col_tag == "description")
	  col_title = col_title.replace(/&nbsp;/g,"");
	 if(cb && cb.checked)
	  xml+= "<column tag=\""+col_tag+"\" title=\""+col_title+"\" width=\""+col_width+"\"/"+">";
	} break;
  }
 }

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(){alert("Impostazioni colonne salvate correttamente");}
 var cmd = "aboutconfig set-config-val -app gcommercialdocs -sec columns -arr `"+CAT_TAG.toLowerCase()+"` -xml `"+xml+"`";
 sh.sendSudoCommand(cmd);
}


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
 sh.OnError = function(err){alert(err);}
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

 sh.OnError = function(err){alert(err);}
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
	 sh2.OnError = function(err){alert(err);}
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
 sh.OnError = function(err){alert(err);}
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

	 var stripmsg = ed.value.striptags();
	 if(stripmsg.length > 100) stripmsg = stripmsg.substr(0,100)+"...";

	 var r = tbpm.insertRow(-1);
	 r.id = a['id'];
	 r.insertCell(-1).innerHTML = stripmsg;
	 r.cells[0].onclick = function(){insertPredefMsg(this);}
	 r.insertCell(-1).innerHTML = "<img src='<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/delete.png' onclick='deletePredefMsg(this)' style='cursor:pointer'/"+">";
	 r.cells[1].style.width="16px";
	 r.insertCell(-1).innerHTML = ed.value;
	 r.cells[2].style.display = "none";
	 ed.value = "";	
	}
 sh.sendCommand("commercialdocs new-predefmsg -text `"+ed.value+"`");


 document.getElementById('predefmsg-newmsg').style.display = "none";
 document.getElementById('predefmsg-addmsg').style.display = "";
 document.getElementById('predefmsg-container').style.height = "230px";

 document.getElementById("predefmsg-container").scrollTop = document.getElementById("predefmsg-container").scrollHeight;
}

function predefmsgAddFromSelected()
{
 var sel = tb.GetSelectedRows();
 if(!sel.length) return alert("Nessuna riga di nota selezionata");
 var r = sel[0];
 if((r.getAttribute('type') != 'note') && (r.getAttribute('type') != 'message'))
  return alert("Nessuna riga di nota selezionata");

 var tbpm = document.getElementById("predefmsg-list");
 var spanlist = r.cells[1].getElementsByTagName('SPAN');
 var msg = spanlist[1] ? spanlist[1].innerHTML : spanlist[0].innerHTML;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var r = tbpm.insertRow(-1);
	 r.id = a['id'];
	 var stripmsg = msg.striptags();
	 if(stripmsg.length > 100) stripmsg = stripmsg.substr(0,100)+"...";

	 r.insertCell(-1).innerHTML = stripmsg;
	 r.cells[0].onclick = function(){insertPredefMsg(this);}
	 r.insertCell(-1).innerHTML = "<img src='<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/img/delete.png' onclick='deletePredefMsg(this)' style='cursor:pointer'/"+">";
	 r.cells[1].style.width="16px";
	 r.insertCell(-1).innerHTML = msg;
	 r.cells[2].style.display = "none";
	 tb.unselectAll();
	}
 sh.sendCommand("commercialdocs new-predefmsg -text `"+msg+"`");
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
 var text = td.parentNode.cells[2].innerHTML;
 
 var r = tb.AddRow();
 r.setAttribute('type','note');
 var colSpan = tb.O.rows[0].cells.length-1;
 while(r.cells.length > 2)
 {
  r.deleteCell(2);
 }
 r.cells[1].colSpan=colSpan;
 r.cells[1].setValue(text.striptags());
 r.cells[0].style.width = "70px";

 var spanlist = r.cells[1].getElementsByTagName('SPAN');
 if(spanlist.length < 2)
 {
  var span2 = document.createElement('SPAN'); span2.style.display = "none";
  r.cells[1].appendChild(span2);
  span2.innerHTML = text;
 }
 else
  spanlist[1].innerHTML = text;

 var icon = document.createElement("IMG");
 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/print-mini.png";
 icon.title = "Clicca per trasformare questa riga di nota in un messaggio non stampabile";
 icon.onclick = function(){switchNoteMessage(this);}
 icon.style.marginLeft = "5px";
 r.cells[0].appendChild(icon);

 var icon2 = document.createElement("IMG");
 icon2.src = ABSOLUTE_URL+"GCommercialDocs/img/edit-black.png";
 icon2.title = "Clicca per editare questa nota";
 icon2.onclick = function(){editNote(this);}
 icon2.style.marginLeft = "5px";
 r.cells[0].appendChild(icon2);

 document.getElementById("predefmsg").style.display = "none";
 DOCISCHANGED = true;
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
 DOCISCHANGED = true;
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
 DOCISCHANGED = true;
}


function showTotCol(colName,cb)
{
 document.getElementById("doctot-"+colName+"-th").style.display = cb.checked ? "" : "none";
 document.getElementById("doctot-"+colName).style.display = cb.checked ? "" : "none";

 var sh = new GShell();
 sh.sendCommand("export GCD-DOCTOTCOL-"+colName.toUpperCase()+"="+(cb.checked ? "ON" : "OFF"));
}


function documentDivisionChange(sel)
{
 DOC_DIVISION = sel.value;
 DOCISCHANGED = true;
}

function statusExtraChange(sel)
{
 STATUS_EXTRA = sel.value;
 DOCISCHANGED = true;
}

function deleteSelectedExpenses()
{
 var list = expTB.GetSelectedRows();
 if(!list.length)
  return alert("Nessuna riga è stata selezionata");

 for(var c=0; c < list.length; c++)
  list[c].remove();

 updateTotals();
 DOCISCHANGED = true;
}

function addNewExpenses()
{
 if(expTB.O.rows.length > 3)
  return alert("Si possono inserire al massimo 3 voci di spesa");
 expTB.AddRow().edit();
}

function repeatChanged(sel)
{
 if(!sel.value || (sel.value == "0"))
  document.getElementById('repeat-finishdate-container').style.visibility = "hidden";
 else
  document.getElementById('repeat-finishdate-container').style.visibility = "visible";
}

function lastproductsInit()
{
 var lptb = document.getElementById('lastproducts-list');
 lptb.clearAll = function(){
	 while(this.rows.length > 1)
	  this.deleteRow(1);
	}
 lptb.update = function(subjectId){
	 this.clearAll();
	 var sh = new GShell();
	 sh.OnError = function(err){alert(err);}
	 sh.OnOutput = function(o,a){
		 if(!a || !a['items']) return;
		 for(var c=0; c < a['items'].length; c++)
		 {
		  var itm = a['items'][c];
		  var r = lptb.insertRow(-1);
		  r.setAttribute('refap',itm['ap']);
		  r.setAttribute('refid',itm['id']);
		  r.insertCell(-1).innerHTML = "<input type='checkbox'/"+">";
		  r.insertCell(-1).innerHTML = "<small>"+itm['code']+"</small>";
		  r.insertCell(-1).innerHTML = "<small>"+itm['name']+"</small>";
		  r.insertCell(-1).innerHTML = "<small>"+itm['qty']+"</small>";
		  r.cells[3].style.textAlign = "center";
		  lastproductsInjectRow(r);
		 }
		}
	 sh.sendCommand("commercialdocs get-last-products -subjectid '"+subjectId+"'");
	}

 lptb.unselectAll = function(){
	 for(var c=1; c < this.rows.length; c++)
	 {
	  this.rows[c].className = "";
	  this.rows[c].cells[0].getElementsByTagName('INPUT')[0].checked = false;
	 }
	}

 lptb.selectAll = function(){
	 for(var c=1; c < this.rows.length; c++)
	 {
	  this.rows[c].className = "selected";
	  this.rows[c].cells[0].getElementsByTagName('INPUT')[0].checked = true;
	 }
	}

 for(var c=1; c < lptb.rows.length; c++)
  lastproductsInjectRow(lptb.rows[c]);
}

function lastproductsInjectRow(r)
{
 r.select = function(bool){
	 this.cells[0].getElementsByTagName('INPUT')[0].checked = bool;
	 this.className = bool ? "selected" : "";
	}
 r.cells[0].getElementsByTagName('INPUT')[0].onclick = function(){this.parentNode.parentNode.select(this.checked);}
}

function lastproductsSelectAll(cb)
{
 var lptb = document.getElementById('lastproducts-list');
 if(cb.checked == true)
  lptb.selectAll();
 else
  lptb.unselectAll();
}

function lastproductsInsertSelected()
{
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

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 /*sh.OnFinish = function(o,a){
	 this.OnOutput(o,a);
	 document.getElementById("lastproducts-list").unselectAll();
	 document.getElementById("lastproducts").style.display = "none";
	}*/
 sh.OnOutput = function(o,a){  
	  if(!a)
	   return;

	  r = tb.AddRow();
	  r.setAttribute('type','article');

	  // create icon
	  var icon = document.createElement("IMG");
	  icon.src = ABSOLUTE_URL+"GCommercialDocs/img/article-mini.png";
	  icon.title = "Clicca per vedere la scheda di questo articolo";
	  icon.onclick = function(){showProduct(this);}
	  icon.style.marginLeft = "5px";
	  r.cells[0].appendChild(icon);

	  r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : a['vatid']);
	  r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : a['vattype']);
	  r.setAttribute('vendorid',a['vendor_id']);
	  r.cell['code'].setValue(a['code_str']);
	  r.setAttribute('refid',a['id']);
	  r.setAttribute('refap',a['tb_prefix']);

   	  r.cell['description'].setValue(getInsArtDescription(a));

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
	  r.cell['unitprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['finalprice'],4);
	  r.cell['plbaseprice'].setValue(a['baseprice']);
	  r.cell['plbaseprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['baseprice'],4);
	  r.cell['plmrate'].setValue(a['pricelist_'+CUSTPLID+'_mrate'] ? a['pricelist_'+CUSTPLID+'_mrate'] : 0);
	  r.cell['pldiscperc'].setValue(a['pricelist_'+CUSTPLID+'_discount'] ? a['pricelist_'+CUSTPLID+'_discount'] : 0);
	  r.cell['vat'].setValue(SUBJECT_VATID ? SUBJECT_VATRATE : a['vat']);
	  if(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']])
	  {
	   r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['code']);
	   r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['name']);
	  }

	  r.cell['price'].setValue(a['finalprice']);
	  r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['finalprice'],4);
	  r.cell['pricelist'].setValue(a['pricelist_name']);
	  r.cell['pricelist'].setAttribute("pricelistid",a['pricelist_id']);
	  r.cell['vendorname'].setValue(a['vendor_name']);

	  // variants //
	  if(a && a['variants'])
	  {
	   // colori e tinte //
	   var options = new Array();
	   if(a['variants']['colors'])
	   {
		var arr = new Array();
		for(var c=0; c < a['variants']['colors'].length; c++)
		 arr.push(a['variants']['colors'][c]['name']);
		options.push(arr);
	   }
	   if(a['variants']['tint'])
	   {
		var arr = new Array();
		for(var c=0; c < a['variants']['tint'].length; c++)
		 arr.push(a['variants']['tint'][c]['name']);
		options.push(arr);
	   }
	   r.cell['coltint'].setOptions(options);

	   // taglie, misure e altro //
	   var options = new Array();
	   if(a['variants']['sizes'])
	   {
		var arr = new Array();
		for(var c=0; c < a['variants']['sizes'].length; c++)
		 arr.push(a['variants']['sizes'][c]['name']);
		options.push(arr);
	   }
	   if(a['variants']['dim'])
	   {
		var arr = new Array();
		for(var c=0; c < a['variants']['dim'].length; c++)
		 arr.push(a['variants']['dim'][c]['name']);
		options.push(arr);
	   }
	   if(a['variants']['other'])
	   {
		var arr = new Array();
		for(var c=0; c < a['variants']['other'].length; c++)
		 arr.push(a['variants']['other'][c]['name']);
		options.push(arr);
	   }
	   r.cell['sizmis'].setOptions(options);
	  }
	  
	  updateTotals(r);
	  DOCISCHANGED = true;
	 }

 var lptb = document.getElementById('lastproducts-list');
 for(var c=1; c < lptb.rows.length; c++)
 {
  if(lptb.rows[c].getElementsByTagName('INPUT')[0].checked != true)
   continue;
  var ap = lptb.rows[c].getAttribute('refap');
  var id = lptb.rows[c].getAttribute('refid');
  var qty = 
  sh.sendCommand("commercialdocs getfullinfo -ap `"+ap+"` -id `"+id+"` -subjectid `"+subjectId+"` -pricelistid `"+CUSTPLID+"` --get-variants -qty '1'");
 }
 
 lptb.unselectAll();
 document.getElementById("lastproducts").style.display = "none";
}

function updateLastProducts()
{
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

 var lptb = document.getElementById('lastproducts-list');
 lptb.update(subjectId);
}

function Duplicate()
{
 if(DOCISCHANGED && !LOCKED)
 {
  if(confirm("Il documento è stato modificato. Salvare le modifiche prima di duplicare questo documento?"))
  {
   POSTSAVEACTION = "duplicate";
   return saveDoc();
  }
 }

 if(!confirm("Confermi di voler duplicare questo documento?"))
  return false;

 var sh = new GShell();
 sh.showProcessMessage("Duplicazione in corso", "Attendere prego, è in corso la duplicazione del documento");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(o,a){
	 this.hideProcessMessage();
	 if(window.opener && window.opener.document && (window.opener.document.location.href.indexOf("GCommercialDocs/") > 0))
	  window.opener.document.location.reload();
	 document.location.href = "docinfo.php?id="+a['id']+(STRICT_AGENT ? "&strictagent=true&agentid="+AGENT_ID : "");
	}
 sh.sendCommand("commercialdocs duplicate -id '<?php echo $docInfo['id']; ?>'");
}

function setInternalDocRef(li)
{
 var ap = li.getAttribute('ap');
 var ct = li.getAttribute('ct');

 InternalDocRef.value = "";
 
 document.getElementById("internaldocrefmenu").getElementsByTagName('SPAN')[0].innerHTML = li.innerHTML;
 switch(ap)
 {
  case 'commercialdocs' : {
	 InternalDocRef.esHinst.startQry = "dynarc search -ap commercialdocs -ct '"+ct+"' -fields code_num,name `";
	 InternalDocRef.esHinst.endQry = "` -limit 20 --order-by 'ctime DESC'";
	} break;

  case 'commesse' : {
	 InternalDocRef.esHinst.startQry = "dynarc search -ap commesse -fields code_num,name `";
	 InternalDocRef.esHinst.endQry = "` -limit 20 --order-by 'ctime DESC'";
	} break;

  case 'tickets' : {
	 InternalDocRef.esHinst.startQry = "dynarc search -ap tickets -fields code_num,name `";
	 InternalDocRef.esHinst.endQry = "` -limit 20 --order-by 'ctime DESC'";
	} break;
 }

 if(!ap)
 {
  document.getElementById("internaldocref").style.display = "none";
  document.getElementById("aliasname").style.display = "";
 }
 else
 {
  document.getElementById("internaldocref").style.display = "";
  document.getElementById("aliasname").style.display = "none";
 } 
}

function switchNoteMessage(img)
{
 var r = img.parentNode.parentNode;
 switch(r.getAttribute('type'))
 {
  case 'note' : {
	 r.setAttribute('type','message');
	 var icon = r.cells[0].getElementsByTagName('IMG')[0];
	 if(icon)
	 {
	  icon.src = ABSOLUTE_URL+"GCommercialDocs/img/message-mini.png";
	  icon.title = "Clicca per trasformare questo messaggio in una riga di nota stampabile";
	 }
	} break;
  case 'message' : {
	 r.setAttribute('type','note');
	 var icon = r.cells[0].getElementsByTagName('IMG')[0];
	 if(icon)
	 {
	  icon.src = ABSOLUTE_URL+"GCommercialDocs/img/print-mini.png";
	  icon.title = "Clicca per trasformare questa riga di nota in un messaggio non stampabile";
	 }
	} break;
 }

 if(r.id && (UPDATED_ROWS.indexOf(r) < 0))
  UPDATED_ROWS.push(r);

 DOCISCHANGED = true;
}

function editNote(img)
{
 var r = img.parentNode.parentNode;
 var spanlist = r.cells[1].getElementsByTagName('SPAN');
 var msg = spanlist[1] ? spanlist[1].innerHTML : spanlist[0].innerHTML;
 msg = msg.replace(/‘/g,"'");
 msg = msg.replace(/’/g, "'");


 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,html){
	 if(!html) return;
	 spanlist[0].innerHTML = html.striptags(true);
	 if(spanlist[1]) spanlist[1].innerHTML = html;
	 if(r.id && (UPDATED_ROWS.indexOf(r) < 0))
	  UPDATED_ROWS.push(r);
	 DOCISCHANGED = true;
	}
 
 sh.sendCommand("gframe -f htmleditor -t 'Modifica riga di nota' -c `"+msg+"` --use-cache-contents -params `editorstyle=Basic`");
}

function showProduct(img)
{
 var r = img.parentNode.parentNode;
 var ap = r.getAttribute('refap') ? r.getAttribute('refap') : "gmart";
 var id = r.getAttribute('refid');

 if(isNaN(ap) || (ap == null) || (ap == "null"))
  ap = "gmart";

 if(ap && !isNaN(id) && (id !== null) && (id != 0))
  return openProduct(ap,id,r);
 else
 {
  if(!confirm("Articolo sconosciuto! Desideri registrarlo nel catalogo prodotti?"))
   return;

  var subjInfo = getCurrentSubjectInfo();
  if(IS_VENDOR)
  {
   vendorId = subjInfo ? subjInfo['id'] : 0;
   vendorName = subjInfo ? subjInfo['name'] : "";
  }
  else
  {
   var subjectId = subjInfo ? subjInfo['id'] : 0;
   var subjectName = subjInfo ? subjInfo['name'] : "";
  }

  var code = r.cell['code'].getValue();
  var vencode = r.cell['vencode'].getValue();
  var mancode = r.cell['mancode'].getValue();
  var model = r.cell['description'].getValue();
  var brand = r.cell['brand'].getValue();
  var brandid = r.cell['brand'].getAttribute('refid');
  var units = r.cell['units'].getValue();
  var baseprice = r.cell['unitprice'].getValue();
  var vatrate = r.cell['vat'].getValue();
  var vatid = r.getAttribute('vatid');
  var weight = parseFloat(r.cell['weight'].getValue());
  var weightUnits = r.getAttribute('weightunits');
  
  var sh = new GShell();
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,a){
	 if(!a) return;
	 r.setAttribute('refap',ap);
	 r.setAttribute('refid',a['id']);

	 if(UNKNOWN_ELEMENTS.indexOf(r) >= 0)
	  UNKNOWN_ELEMENTS.splice(UNKNOWN_ELEMENTS.indexOf(r),1);

	 DOCISCHANGED = true;

	 return openProduct(ap,a['id'],r);
	}
  var cmd = "dynarc new-item -ap `"+ap+"` -group gmart -code-str '"+code+"' -name `"+(brand ? brand+" "+model : model)+"` -extset `gmart.units='"+units+"',brand='''"+brand+"''',brandid='"+brandid+"',model='''"+model+"''',mancode='"+mancode+"',weight='"+weight+"',weightunits='"+weightUnits+"'";
  if(IS_VENDOR)
   cmd+= ",vendorprices.code='"+vencode+"',vendorid='"+vendorId+"',vendorname='''"+vendorName+"''',price='"+parseCurrency(baseprice)+"',vat='"+parseFloat(vatrate)+"'";
  else
   cmd+= ",pricing.baseprice='"+parseCurrency(baseprice)+"',vat='"+parseFloat(vatrate)+"',autosetpricelists=1";
  cmd+= "`";

  sh.sendCommand(cmd);
 }
}

function openProduct(ap,id,r)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(r)
	 {
	  reloadDataRow(ap,id,r);
	 }
	}
 sh.sendCommand("gframe -f gmart/edit.item -params `ap="+ap+"&id="+id+"`");
}

function reloadDataRow(ap, id, r)
{
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

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	if(!a) return;
	r.itemInfo = a;

	r.cell['code'].setValue(a['code_str']);
    if(r.getAttribute('type') == 'article')
     r.cell['description'].setValue(getInsArtDescription(a));
    else
     r.cell['description'].setValue(a['name']);

	r.setAttribute('weightunits',a['weightunits']);
	r.cell['weight'].setValue(a['weight']+" "+a['weightunits']);


    // variants //
	if(a && a['variants'])
	{
	 // colori e tinte //
	 var options = new Array();
	 if(a['variants']['colors'])
	 {
	  var arr = new Array();
	  for(var c=0; c < a['variants']['colors'].length; c++)
	   arr.push(a['variants']['colors'][c]['name']);
	  options.push(arr);
	 }
	 if(a['variants']['tint'])
	 {
	  var arr = new Array();
	  for(var c=0; c < a['variants']['tint'].length; c++)
	   arr.push(a['variants']['tint'][c]['name']);
	  options.push(arr);
	 }
	 r.cell['coltint'].setOptions(options);

	 // taglie, misure e altro //
	 var options = new Array();
	 if(a['variants']['sizes'])
	 {
	  var arr = new Array();
	  for(var c=0; c < a['variants']['sizes'].length; c++)
	   arr.push(a['variants']['sizes'][c]['name']);
	  options.push(arr);
	 }
	 if(a['variants']['dim'])
	 {
	  var arr = new Array();
	  for(var c=0; c < a['variants']['dim'].length; c++)
	   arr.push(a['variants']['dim'][c]['name']);
	  options.push(arr);
	 }
	 if(a['variants']['other'])
	 {
	  var arr = new Array();
	  for(var c=0; c < a['variants']['other'].length; c++)
	   arr.push(a['variants']['other'][c]['name']);
	  options.push(arr);
	 }
	 r.cell['sizmis'].setOptions(options);
	}
	updateTotals(r);
	DOCISCHANGED = true;
   }

 sh.sendCommand("commercialdocs getfullinfo -ap `"+ap+"` -id `"+id+"` -subjectid `"+subjectId+"` -pricelistid `"+CUSTPLID+"` --get-variants -qty '"+r.cell['qty'].getValue()+"'");
}

function showFinalProduct(img)
{
 var r = img.parentNode.parentNode;
 var ap = r.getAttribute('refap') ? r.getAttribute('refap') : "gproducts";
 var id = r.getAttribute('refid');

 if(isNaN(ap) || (ap == null) || (ap == "null"))
  ap = "gproducts";


 if(ap && !isNaN(id) && (id !== null) && (id != 0))
  return openFinalProduct(ap,id,r);
 else
 {
  if(!confirm("Prodotto sconosciuto! Desideri registrarlo nel catalogo prodotti finiti?"))
   return;

  var subjInfo = getCurrentSubjectInfo();
  if(IS_VENDOR)
  {
   vendorId = subjInfo ? subjInfo['id'] : 0;
   vendorName = subjInfo ? subjInfo['name'] : "";
  }
  else
  {
   var subjectId = subjInfo ? subjInfo['id'] : 0;
   var subjectName = subjInfo ? subjInfo['name'] : "";
  }

  var code = r.cell['code'].getValue();
  var model = r.cell['description'].getValue();
  var units = r.cell['units'].getValue();
  var baseprice = r.cell['unitprice'].getValue();
  var vatrate = r.cell['vat'].getValue();
  var vatid = r.getAttribute('vatid');
  
  var sh = new GShell();
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,a){
	 if(!a) return;
	 r.setAttribute('refap',ap);
	 r.setAttribute('refid',a['id']);

	 if(UNKNOWN_ELEMENTS.indexOf(r) >= 0)
	  UNKNOWN_ELEMENTS.splice(UNKNOWN_ELEMENTS.indexOf(r),1);

	 DOCISCHANGED = true;

	 return openFinalProduct(ap,a['id'],r);
	}
  var cmd = "dynarc new-item -ap `"+ap+"` -group gproducts -code-str '"+code+"' -name `"+model+"` -extset `gproducts.units='"+units+"'";
  cmd+= ",pricing.baseprice='"+parseCurrency(baseprice)+"',vat='"+parseFloat(vatrate)+"',autosetpricelists=1";
  cmd+= "`";

  sh.sendCommand(cmd);
 }
}

function openFinalProduct(ap,id,r)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(r)
	 {
	  r.cell['code'].setValue(a['code_str']);
	  r.cell['description'].setValue(a['name']);
	  DOCISCHANGED = true;
	 }
	}
 sh.sendCommand("gframe -f gproducts/edit.item -params `ap="+ap+"&id="+id+"`");
}

function showComponent(img)
{
 var r = img.parentNode.parentNode;
 var ap = r.getAttribute('refap') ? r.getAttribute('refap') : "gpart";
 var id = r.getAttribute('refid');

 if(isNaN(ap) || (ap == null) || (ap == "null"))
  ap = "gpart";

 if(ap && !isNaN(id) && (id !== null) && (id != 0))
  return openComponent(ap,id,r);
 else
 {
  if(!confirm("Componente sconosciuto! Desideri registrarlo nel catalogo componenti?"))
   return;

  var subjInfo = getCurrentSubjectInfo();
  if(IS_VENDOR)
  {
   vendorId = subjInfo ? subjInfo['id'] : 0;
   vendorName = subjInfo ? subjInfo['name'] : "";
  }
  else
  {
   var subjectId = subjInfo ? subjInfo['id'] : 0;
   var subjectName = subjInfo ? subjInfo['name'] : "";
  }

  var code = r.cell['code'].getValue();
  var vencode = r.cell['vencode'].getValue();
  var mancode = r.cell['mancode'].getValue();
  var model = r.cell['description'].getValue();
  var brand = r.cell['brand'].getValue();
  var brandid = r.cell['brand'].getAttribute('refid');
  var units = r.cell['units'].getValue();
  var baseprice = r.cell['unitprice'].getValue();
  var vatrate = r.cell['vat'].getValue();
  var vatid = r.getAttribute('vatid');
  
  var sh = new GShell();
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,a){
	 if(!a) return;
	 r.setAttribute('refap',ap);
	 r.setAttribute('refid',a['id']);

	 if(UNKNOWN_ELEMENTS.indexOf(r) >= 0)
	  UNKNOWN_ELEMENTS.splice(UNKNOWN_ELEMENTS.indexOf(r),1);

	 DOCISCHANGED = true;

	 return openComponent(ap,a['id'],r);
	}
  var cmd = "dynarc new-item -ap `"+ap+"` -group gpart -code-str '"+code+"' -name `"+(brand ? brand+" "+model : model)+"` -extset `gpart.units='"+units+"',brand='''"+brand+"''',brandid='"+brandid+"',model='''"+model+"''',mancode='"+mancode+"'";
  if(IS_VENDOR)
   cmd+= ",vendorprices.code='"+vencode+"',vendorid='"+vendorId+"',vendorname='''"+vendorName+"''',price='"+parseCurrency(baseprice)+"',vat='"+parseFloat(vatrate)+"'";
  else
   cmd+= ",pricing.baseprice='"+parseCurrency(baseprice)+"',vat='"+parseFloat(vatrate)+"',autosetpricelists=1";
  cmd+= "`";

  sh.sendCommand(cmd);
 }
}

function openComponent(ap,id,r)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(r)
	 {
	  reloadDataRow(ap,id,r);
	 }
	}
 sh.sendCommand("gframe -f gpart/edit.item -params `ap="+ap+"&id="+id+"`");
}

function showMaterial(img)
{
 var r = img.parentNode.parentNode;
 var ap = r.getAttribute('refap') ? r.getAttribute('refap') : "gmaterial";
 var id = r.getAttribute('refid');

 if(isNaN(ap) || (ap == null) || (ap == "null"))
  ap = "gmaterial";


 if(ap && !isNaN(id) && (id !== null) && (id != 0))
  return openMaterial(ap,id,r);
 else
 {
  if(!confirm("Materiale sconosciuto! Desideri registrarlo nel catalogo materiali?"))
   return;

  var subjInfo = getCurrentSubjectInfo();
  if(IS_VENDOR)
  {
   vendorId = subjInfo ? subjInfo['id'] : 0;
   vendorName = subjInfo ? subjInfo['name'] : "";
  }
  else
  {
   var subjectId = subjInfo ? subjInfo['id'] : 0;
   var subjectName = subjInfo ? subjInfo['name'] : "";
  }

  var code = r.cell['code'].getValue();
  var vencode = r.cell['vencode'].getValue();
  var mancode = r.cell['mancode'].getValue();
  var model = r.cell['description'].getValue();
  var brand = r.cell['brand'].getValue();
  var brandid = r.cell['brand'].getAttribute('refid');
  var units = r.cell['units'].getValue();
  var baseprice = r.cell['unitprice'].getValue();
  var vatrate = r.cell['vat'].getValue();
  var vatid = r.getAttribute('vatid');
  
  var sh = new GShell();
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,a){
	 if(!a) return;
	 r.setAttribute('refap',ap);
	 r.setAttribute('refid',a['id']);

	 if(UNKNOWN_ELEMENTS.indexOf(r) >= 0)
	  UNKNOWN_ELEMENTS.splice(UNKNOWN_ELEMENTS.indexOf(r),1);

	 DOCISCHANGED = true;

	 return openMaterial(ap,a['id'],r);
	}
  var cmd = "dynarc new-item -ap `"+ap+"` -group gmaterial -code-str '"+code+"' -name `"+(brand ? brand+" "+model : model)+"` -extset `gmaterial.units='"+units+"',brand='''"+brand+"''',brandid='"+brandid+"',model='''"+model+"''',mancode='"+mancode+"'";
  if(IS_VENDOR)
   cmd+= ",vendorprices.code='"+vencode+"',vendorid='"+vendorId+"',vendorname='''"+vendorName+"''',price='"+parseCurrency(baseprice)+"',vat='"+parseFloat(vatrate)+"'";
  else
   cmd+= ",pricing.baseprice='"+parseCurrency(baseprice)+"',vat='"+parseFloat(vatrate)+"',autosetpricelists=1";
  cmd+= "`";

  sh.sendCommand(cmd);
 }
}

function openMaterial(ap,id,r)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(r)
	 {
	  r.cell['code'].setValue(a['code_str']);
	  r.cell['description'].setValue(a['name']);
	  DOCISCHANGED = true;
	 }
	}
 sh.sendCommand("gframe -f gmaterial/edit.item -params `ap="+ap+"&id="+id+"`");
}

function showBook(img)
{
 var r = img.parentNode.parentNode;
 var ap = r.getAttribute('refap') ? r.getAttribute('refap') : "gbook";
 var id = r.getAttribute('refid');

 if(isNaN(ap) || (ap == null) || (ap == "null"))
  ap = "gbook";

 if(ap && !isNaN(id) && (id !== null) && (id != 0))
  return openBook(ap,id,r);
 else
 {
  if(!confirm("Libro sconosciuto! Desideri registrarlo in catalogo?"))
   return;

  var subjInfo = getCurrentSubjectInfo();
  if(IS_VENDOR)
  {
   vendorId = subjInfo ? subjInfo['id'] : 0;
   vendorName = subjInfo ? subjInfo['name'] : "";
  }
  else
  {
   var subjectId = subjInfo ? subjInfo['id'] : 0;
   var subjectName = subjInfo ? subjInfo['name'] : "";
  }

  var code = r.cell['code'].getValue();
  var vencode = r.cell['vencode'].getValue();
  var title = r.cell['description'].getValue();
  var units = r.cell['units'].getValue();
  var baseprice = r.cell['unitprice'].getValue();
  var vatrate = r.cell['vat'].getValue();
  var vatid = r.getAttribute('vatid');
  
  var sh = new GShell();
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,a){
	 if(!a) return;
	 r.setAttribute('refap',ap);
	 r.setAttribute('refid',a['id']);

	 if(UNKNOWN_ELEMENTS.indexOf(r) >= 0)
	  UNKNOWN_ELEMENTS.splice(UNKNOWN_ELEMENTS.indexOf(r),1);

	 DOCISCHANGED = true;

	 return openBook(ap,a['id'],r);
	}
  var cmd = "dynarc new-item -ap `"+ap+"` -group gbook -code-str '"+code+"' -name `"+title+"` -extset `gbook.units='"+units+"'";
  if(IS_VENDOR)
   cmd+= ",vendorprices.code='"+vencode+"',vendorid='"+vendorId+"',vendorname='''"+vendorName+"''',price='"+parseCurrency(baseprice)+"',vat='"+parseFloat(vatrate)+"'";
  else
   cmd+= ",pricing.baseprice='"+parseCurrency(baseprice)+"',vat='"+parseFloat(vatrate)+"',autosetpricelists=1";
  cmd+= "`";

  sh.sendCommand(cmd);
 }
}

function openBook(ap,id,r)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(r)
	 {
	  r.cell['code'].setValue(a['code_str']);
	  r.cell['description'].setValue(a['name']);
	  DOCISCHANGED = true;
	 }
	}
 sh.sendCommand("gframe -f gbook/edit.item -params `ap="+ap+"&id="+id+"`");
}

function showService(img)
{
 var r = img.parentNode.parentNode;
 var ap = r.getAttribute('refap') ? r.getAttribute('refap') : "gserv";
 var id = r.getAttribute('refid');

 //if(isNaN(ap) || (ap == null) || (ap == "null"))
 if(!ap)
  ap = "gserv";

 if(ap && !isNaN(id) && (id !== null) && (id != 0))
  return openService(ap,id,r);
 else
 {
  if(!confirm("Servizio sconosciuto! Desideri registrarlo nel catalogo servizi?"))
   return;

  var subjInfo = getCurrentSubjectInfo();
  if(IS_VENDOR)
  {
   vendorId = subjInfo ? subjInfo['id'] : 0;
   vendorName = subjInfo ? subjInfo['name'] : "";
  }
  else
  {
   var subjectId = subjInfo ? subjInfo['id'] : 0;
   var subjectName = subjInfo ? subjInfo['name'] : "";
  }

  var code = r.cell['code'].getValue();
  var vencode = r.cell['vencode'].getValue();
  var title = r.cell['description'].getValue();
  var units = r.cell['units'].getValue();
  var baseprice = r.cell['unitprice'].getValue();
  var vatrate = r.cell['vat'].getValue();
  var vatid = r.getAttribute('vatid');
  
  var sh = new GShell();
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,a){
	 if(!a) return;
	 r.setAttribute('refap',ap);
	 r.setAttribute('refid',a['id']);

	 if(UNKNOWN_ELEMENTS.indexOf(r) >= 0)
	  UNKNOWN_ELEMENTS.splice(UNKNOWN_ELEMENTS.indexOf(r),1);

	 DOCISCHANGED = true;

	 return openService(ap,a['id'],r);
	}
  var cmd = "dynarc new-item -ap `"+ap+"` -group gserv -code-str '"+code+"' -name `"+title+"` -extset `gserv.units='"+units+"',type='FIXED-PRICE'";
  if(IS_VENDOR)
   cmd+= ",vendorprices.code='"+vencode+"',vendorid='"+vendorId+"',vendorname='''"+vendorName+"''',price='"+parseCurrency(baseprice)+"',vat='"+parseFloat(vatrate)+"'";
  else
   cmd+= ",pricing.baseprice='"+parseCurrency(baseprice)+"',vat='"+parseFloat(vatrate)+"',autosetpricelists=1";
  cmd+= "`";

  sh.sendCommand(cmd);
 }
}

function openService(ap,id,r)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(r)
	 {
	  r.cell['code'].setValue(a['code_str']);
	  r.cell['description'].setValue(a['name']);
	  DOCISCHANGED = true;
	 }
	}
 sh.sendCommand("gframe -f gserv/edit.item -params `ap="+ap+"&id="+id+"`");
}

function showLabor(img)
{
 var r = img.parentNode.parentNode;
 var ap = r.getAttribute('refap') ? r.getAttribute('refap') : "glabor";
 var id = r.getAttribute('refid');

 if(isNaN(ap) || (ap == null) || (ap == "null"))
  ap = "glabor";

 if(ap && !isNaN(id) && (id !== null) && (id != 0))
  return openLabor(ap,id,r);
 else
 {
  if(!confirm("Lavorazione sconosciuta! Desideri registrarla nel catalogo lavorazioni?"))
   return;

  var subjInfo = getCurrentSubjectInfo();
  if(IS_VENDOR)
  {
   vendorId = subjInfo ? subjInfo['id'] : 0;
   vendorName = subjInfo ? subjInfo['name'] : "";
  }
  else
  {
   var subjectId = subjInfo ? subjInfo['id'] : 0;
   var subjectName = subjInfo ? subjInfo['name'] : "";
  }

  var code = r.cell['code'].getValue();
  var vencode = r.cell['vencode'].getValue();
  var title = r.cell['description'].getValue();
  var units = r.cell['units'].getValue();
  var baseprice = r.cell['unitprice'].getValue();
  var vatrate = r.cell['vat'].getValue();
  var vatid = r.getAttribute('vatid');
  
  var sh = new GShell();
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,a){
	 if(!a) return;
	 r.setAttribute('refap',ap);
	 r.setAttribute('refid',a['id']);

	 if(UNKNOWN_ELEMENTS.indexOf(r) >= 0)
	  UNKNOWN_ELEMENTS.splice(UNKNOWN_ELEMENTS.indexOf(r),1);

	 DOCISCHANGED = true;

	 return openService(ap,a['id'],r);
	}
  var cmd = "dynarc new-item -ap `"+ap+"` -group glabor -code-str '"+code+"' -name `"+title+"` -extset `glabor.units='"+units+"'";
  if(IS_VENDOR)
   cmd+= ",vendorprices.code='"+vencode+"',vendorid='"+vendorId+"',vendorname='''"+vendorName+"''',price='"+parseCurrency(baseprice)+"',vat='"+parseFloat(vatrate)+"'";
  else
   cmd+= ",pricing.baseprice='"+parseCurrency(baseprice)+"',vat='"+parseFloat(vatrate)+"',autosetpricelists=1";
  cmd+= "`";

  sh.sendCommand(cmd);
 }
}

function openLabor(ap,id,r)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(r)
	 {
	  r.cell['code'].setValue(a['code_str']);
	  r.cell['description'].setValue(a['name']);
	  DOCISCHANGED = true;
	 }
	}
 sh.sendCommand("gframe -f glabor/edit.item -params `ap="+ap+"&id="+id+"`");
}

function showPictureFrame(img)
{
 /* GET SUBJECT */
 if(!RubricaEdit.value)
  var subjectId = 0;
 else if(RubricaEdit.data)
  var subjectId = RubricaEdit.data['id'];
 else
  var subjectId = 0;

 var r = img.parentNode.parentNode;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;

	 r.data = a;
	 r.xmldata = o;
	 
     r.setAttribute('vatid',a['vatid']);
     r.setAttribute('vattype',a['vattype']);
	 r.setAttribute('refap',a['frame_ap']);
	 r.setAttribute('refid',a['frame_id']);

	 r.cell['code'].setValue(r.data['frame_code']);
	 r.cell['description'].setValue(r.data['frame_name']);

	 r.cell['unitprice'].setValue(a['finalprice']);
	 r.cell['unitprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['finalprice'],4);
	 r.cell['price'].setValue(a['finalprice']);
	 r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['finalprice'],4);
     r.cell['vat'].setValue(a['vatrate']);
	 if(VAT_BY_ID[a['vatid']])
	 {
	  r.cell['vatcode'].setValue(VAT_BY_ID[a['vatid']]['code']);
	  r.cell['vatname'].setValue(VAT_BY_ID[a['vatid']]['name']);
	 }

	 updateTotals(r);
	 DOCISCHANGED = true;
	}
 sh.sendCommand("gframe -f pictureframe/editframe -params `ap=commercialdocs&elid="+r.id+"&subjid="+subjectId+"&plid="+CUSTPLID+"&docap=commercialdocs&docid=<?php echo $docInfo['id']; ?>&autosave=true`");
}

function engagesItemsOn(docType)
{
 var list = tb.GetSelectedRows();
 if(!list.length)
  return alert("Nessuna riga è stata selezionata");

 var xml = "<xml><items>";
 for(var c=0; c < list.length; c++)
 {
  var r = list[c];
  xml+= "<item code=\""+r.cell['code'].getValue()+"\" name=\""+r.cell['description'].getValue()+"\" qty=\""+r.cell['qty'].getValue()+"\"/"+">";
 }
 xml+= "</items></xml>";

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(a['intervid'])
	  var commands = new Array();
	 for(var c=0; c < list.length; c++)
	 {
	  var r = list[c];
	  r.setAttribute('docrefap',a['docrefap']);
	  r.setAttribute('docrefid',a['docrefid']);
	  r.cell['docref'].setValue(a['docrefname']);

	  if(a['intervid'])
	  {
	   var cdelementsQry = "type='"+r.getAttribute('type')+"'";
	   switch(r.getAttribute('type'))
	   {
		case 'note' : cdelementsQry+= ",desc='''"+r.cells[0].getElementsByTagName('SPAN')[0].innerHTML+"'''"; break;
		default : {
			 var vatId = r.getAttribute('vatid') ? r.getAttribute('vatid') : 0;
			 var vatType = r.getAttribute('vattype') ? r.getAttribute('vattype') : "";
			 var discount = ((r.cell['discount'].getValue().indexOf("%") > 0) ? r.cell['discount'].getValue() : parseCurrency(r.cell['discount'].getValue()));
			 cdelementsQry+= ",refap='"+r.getAttribute('refap')+"',refid='"+r.getAttribute('refid')+"',code='"+r.cell['code'].getValue()+"',name='''"+r.cell['description'].getValue()+"''',qty='"+r.cell['qty'].getValue()+"',units='"+r.cell['units'].getValue()+"',price='"+parseCurrency(r.cell['unitprice'].getValue())+"',discount='"+discount+"',vatrate='"+parseFloat(r.cell['vat'].getValue())+"',vatid='"+vatId+"',vattype='"+vatType+"'";
			} break;
	   }
	   commands.push("dynarc edit-item -ap commesseinterv -id '"+a['intervid']+"' -extset `cdelements."+cdelementsQry+"`");
	  }
	 }
	 
	 if(a['intervid'] && commands.length)
	 {
	  var sh2 = new GShell();
	  sh2.OnError = function(err){alert(err);}
	  for(var c=0; c < commands.length; c++)
	   sh2.sendCommand(commands[c]);	   
	 }

	}

 sh.sendCommand("gframe -f commercialdocs/engages-elements -params `doctype="+docType+"` -xpn xmlelements -xpv `"+xml+"`");
}

function unengagesSelectedItems()
{
 var list = tb.GetSelectedRows();
 if(!list.length)
  return alert("Nessuna riga è stata selezionata");

 if(!confirm("Sei sicuro di voler disimpegnare gli articoli selezionati?"))
  return;

 for(var c=0; c < list.length; c++)
 {
  list[c].setAttribute('docrefap',"");
  list[c].setAttribute('docrefid',"");
  list[c].cell['docref'].setValue("");
 }

}

function applyContribAndDeduct()
{
 if(LOCKED)
  return alert("Impossibile applicare le modifiche perchè il documento è bloccato");
 /* Verifica che il cliente abbia la partita IVA */
 if((!SUBJECT_VATNUMBER || (SUBJECT_VATNUMBER == "")) && (!SUBJECT_TAXCODE || (SUBJECT_TAXCODE == "")) )
 {
  RIVALSA_INPS = 0;
  RIT_ENASARCO = 0;
  RIT_ENASARCO_PERCIMP = 0;
  CASSA_PREV = 0;
  CASSA_PREV_VATID = 0;
  CASSA_PREV_VAT_TYPE = 0;
  RIT_ACCONTO = 0;
  RIT_ACCONTO_PERCIMP = 0;
  RIT_ACCONTO_RIVINPSINC = 0;

  return alert("Non è possibile applicare contributi o ritenute a soggetti privi di Partita IVA.");
 }

 RIVALSA_INPS = (document.getElementById("riv_inps_enabled").checked == true) ? parseFloat(document.getElementById('riv_inps').value) : 0;
 RIT_ENASARCO = (document.getElementById('rit_enasarco_enabled').checked == true) ? parseFloat(document.getElementById('rit_enasarco').value) : 0;
 RIT_ENASARCO_PERCIMP = (document.getElementById('rit_enasarco_enabled').checked == true) ? parseFloat(document.getElementById('rit_enasarco_percimp').value) : 0;
 CASSA_PREV = (document.getElementById('cassa_prev_enabled').checked == true) ? parseFloat(document.getElementById('cassa_prev').value) : 0;
 CASSA_PREV_VATID = (document.getElementById('cassa_prev_enabled').checked == true) ? document.getElementById('cassa_prev_vat').value : 0;
 CASSA_PREV_VAT_TYPE = (document.getElementById('cassa_prev_enabled').checked == true) ? document.getElementById('cassa_prev_vat').options[document.getElementById('cassa_prev_vat').selectedIndex].getAttribute('vattype') : "";
 RIT_ACCONTO = (document.getElementById('rit_acconto_enabled').checked == true) ? parseFloat(document.getElementById('rit_acconto').value) : 0;
 RIT_ACCONTO_PERCIMP = (document.getElementById('rit_acconto_enabled').checked == true) ? parseFloat(document.getElementById('rit_acconto_percimp').value) : 0;
 RIT_ACCONTO_RIVINPSINC = (document.getElementById('rit_acconto_enabled').checked == true) ? (document.getElementsByName('include_rivinps')[0].checked ? 1 : 0) : 0;

 document.getElementById("docfoot-th-rivinps").style.display = RIVALSA_INPS ? "" : "none";
 document.getElementById("doctot-rivinps").style.display = RIVALSA_INPS ? "" : "none";
 
 document.getElementById("docfoot-th-ritacc").style.display = RIT_ACCONTO ? "" : "none";
 document.getElementById("doctot-ritacconto").style.display = RIT_ACCONTO ? "" : "none";


 updateTotals();
 DOCISCHANGED = true;

 alert("Modifiche applicate!");

 showPage('home',document.getElementById('hometabspan'));
}

function insertElementFromData(data, docap, docid)
{
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

 var r = tb.AddRow();
 r.setAttribute("type",data['type']);
 var docRefAP = InternalDocRef.getAttribute('refap');
 var docRefID = InternalDocRef.getAttribute('refid');
 r.setAttribute('docrefap',docRefAP);
 r.setAttribute('docrefid',docRefID);
 r.cell['docref'].setValue(InternalDocRef.value);

 if(docap && docid)
 {
  r.setAttribute('row_ref_docap',docap);
  r.setAttribute('row_ref_docid',docid);
  r.setAttribute('row_ref_id',data['id']);
 }

 if(data['type'] == "note")
 {
  var colSpan = tb.O.rows[0].cells.length-1;
  while(r.cells.length > 2)
   r.deleteCell(2);
  r.cells[1].colSpan=colSpan;
  r.cells[1].setValue(data['description']);
  // create icon
  var icon = document.createElement("IMG");
  icon.src = ABSOLUTE_URL+"GCommercialDocs/img/print-mini.png";
  icon.title = "Clicca per trasformare questa riga di nota in un messaggio non stampabile";
  icon.onclick = function(){switchNoteMessage(this);}
  icon.style.marginLeft = "5px";
  r.cells[0].appendChild(icon);

  var icon2 = document.createElement("IMG");
  icon2.src = ABSOLUTE_URL+"GCommercialDocs/img/edit-black.png";
  icon2.title = "Clicca per editare questa nota";
  icon2.onclick = function(){editNote(this);}
  icon2.style.marginLeft = "5px";
  r.cells[0].appendChild(icon2);
 }
 else if(data['type'] == "message")
 {
  var colSpan = tb.O.rows[0].cells.length-1;
  while(r.cells.length > 2)
   r.deleteCell(2);
  r.cells[1].colSpan=colSpan;
  r.cells[1].setValue(data['description']);
  // create icon
  var icon = document.createElement("IMG");
  icon.src = ABSOLUTE_URL+"GCommercialDocs/img/message-mini.png";
  icon.title = "Clicca per trasformare questo messaggio in una riga di nota stampabile";
  icon.onclick = function(){switchNoteMessage(this);}
  icon.style.marginLeft = "5px";
  r.cells[0].appendChild(icon);

  var icon2 = document.createElement("IMG");
  icon2.src = ABSOLUTE_URL+"GCommercialDocs/img/edit-black.png";
  icon2.title = "Clicca per editare questa nota";
  icon2.onclick = function(){editNote(this);}
  icon2.style.marginLeft = "5px";
  r.cells[0].appendChild(icon2);
 }
 else
 {
  switch(r.getAttribute('type'))
  {
   case 'article' : {
		 // create icon
		 var icon = document.createElement("IMG");
		 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/article-mini.png";
		 icon.title = "Clicca per vedere la scheda di questo articolo";
		 icon.onclick = function(){showProduct(this);}
		 icon.style.marginLeft = "5px";
		 r.cells[0].appendChild(icon);
		} break;

   case 'finalproduct' : {
		 // create icon
		 var icon = document.createElement("IMG");
		 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/finalproduct-mini.png";
		 icon.title = "Clicca per vedere la scheda di questo prodotto";
		 icon.onclick = function(){showFinalProduct(this);}
		 icon.style.marginLeft = "5px";
		 r.cells[0].appendChild(icon);
		} break;

   case 'component' : {
		 // create icon
		 var icon = document.createElement("IMG");
		 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/component-mini.png";
		 icon.title = "Clicca per vedere la scheda di questo componente";
		 icon.onclick = function(){showComponent(this);}
		 icon.style.marginLeft = "5px";
		 r.cells[0].appendChild(icon);
		} break;

   case 'material' : {
		 // create icon
		 var icon = document.createElement("IMG");
		 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/material-mini.png";
		 icon.title = "Clicca per vedere la scheda di questo materiale";
		 icon.onclick = function(){showMaterial(this);}
		 icon.style.marginLeft = "5px";
		 r.cells[0].appendChild(icon);
		} break;

   case 'book' : {
		 // create icon
		 var icon = document.createElement("IMG");
		 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/book-mini.png";
		 icon.title = "Clicca per vedere la scheda di questo libro";
		 icon.onclick = function(){showBook(this);}
		 icon.style.marginLeft = "5px";
		 r.cells[0].appendChild(icon);
		} break;

    case 'service' : {
		 // create icon
		 var icon = document.createElement("IMG");
		 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/service-mini.png";
		 icon.title = "Clicca per vedere la scheda di questo servizio";
		 icon.onclick = function(){showService(this);}
		 icon.style.marginLeft = "5px";
		 r.cells[0].appendChild(icon);
		} break;

   case 'pictureframe' : {
		 // create icon
		 var icon = document.createElement("IMG");
		 icon.src = ABSOLUTE_URL+"GCommercialDocs/img/pictureframe-mini.png";
		 icon.title = "Clicca per vedere i dettagli di questa cornice";
		 icon.onclick = function(){showPictureFrame(this);}
		 icon.style.marginLeft = "5px";
		 r.cells[0].appendChild(icon);
		} break;

  }

  r.setAttribute('vatid',data['vatid']);
  r.setAttribute('vattype',data['vattype']);
  r.setAttribute('refap',data['ref_ap']);
  r.setAttribute('refid',data['ref_id']);
  r.setAttribute('vendorid',data['vendor_id']);

  r.cell['code'].setValue(data['code']);
  r.cell['vencode'].setValue(data['vencode']);
  r.cell['mancode'].setValue(data['manufacturer_code']);

  r.cell['description'].setValue(data['name']);

  r.cell['qty'].setValue(parseFloat(data['qty_sent']) ? parseFloat(data['qty'])-parseFloat(data['qty_sent']) : data['qty']);
  r.cell['weight'].setValue(data['weight']);   

  r.cell['units'].setValue(data['units']);
  r.cell['vendorprice'].setValue(data['vendor_price']);
  r.cell['unitprice'].setValue(data['sale_price']);
  r.cell['unitprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(data['sale_price'],4);

  r.cell['plbaseprice'].setValue(data['plbaseprice']);
  r.cell['plbaseprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(data['plbaseprice'],4);

  r.cell['plmrate'].setValue(data['plmrate']);
  r.cell['pldiscperc'].setValue(data['pldiscperc']);
  r.cell['vat'].setValue(data['vatrate']);
  if(VAT_BY_ID[data['vatid']])
  {
   r.cell['vatcode'].setValue(VAT_BY_ID[data['vatid']]['code']);
   r.cell['vatname'].setValue(VAT_BY_ID[data['vatid']]['name']);
  }

  r.cell['vendorname'].setValue(data['vendor_name']);

  var sh = new GShell();
  sh.OnOutput = function(o,a){
		  if(!a)
		   return updateTotals(r);
		  if(IS_VENDOR)	//IF IS VENDOR
		  {
		   if(a['vendor_id'] == subjectId)
		   {
		    r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']);
		    r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : a['vendor_vattype']);
		   }
		   else
		   {
		    r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : a['vatid']);
		    r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : a['vattype']);
		   }
		  }
		  else    // IF IS CUSTOMER
		  {
		   r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : a['vatid']);
		   r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : a['vattype']);
		  }
		  r.setAttribute('vendorid',a['vendor_id']);
		  r.setAttribute('refid',a['id']);
		  r.setAttribute('refap',a['tb_prefix']);

		  r.cell['brand'].setValue(a['brand'] ? a['brand'] : "");
		  r.cell['brand'].setAttribute('refid',a['brand_id']);

		  if(!IS_VENDOR && a['custompricing'])
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


		  if(IS_VENDOR)
		  {
		   if(a['vendor_id'] == subjectId)
		   {
		    r.cell['vendorprice'].setValue(a['vendor_price']);
		    r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
		   }
		   else
		   {
		    r.cell['vendorprice'].setValue(0);
		    r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(0,4);
		   }
		  }
		  else
		  {
		   r.cell['vendorprice'].setValue(a['vendor_price']);
		   r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
		  }
		  r.cell['unitprice'].setValue(a['finalprice']);
		  r.cell['unitprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['finalprice'],4);
		  r.cell['plbaseprice'].setValue(a['baseprice']);
		  r.cell['plbaseprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['baseprice'],4);
		  r.cell['plmrate'].setValue(a['pricelist_'+CUSTPLID+'_mrate'] ? a['pricelist_'+CUSTPLID+'_mrate'] : 0);
		  r.cell['pldiscperc'].setValue(a['pricelist_'+CUSTPLID+'_discount'] ? a['pricelist_'+CUSTPLID+'_discount'] : 0);

		  if(IS_VENDOR)
		  {
		   if(a['vendor_id'] == subjectId)
		   {
		    r.cell['vat'].setValue(SUBJECT_VATID ? SUBJECT_VATRATE : a['vendor_vatrate']);
			if(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']])
			{
			 r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']]['code']);
			 r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']]['name']);
			}
		    r.cell['price'].setValue(a['vendor_price']);
		    r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
		   }
		   else
		   {
		    r.cell['vat'].setValue(SUBJECT_VATID ? SUBJECT_VATRATE : a['vat']);
			if(VAT_BY_ID[a['vatid']])
			{
			 r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['code']);
			 r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['name']);
			}
		    r.cell['price'].setValue(0);
		    r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(0,4);
		   }
		  }
		  else
		  {
		   r.cell['vat'].setValue(SUBJECT_VATID ? SUBJECT_VATRATE : a['vat']);
		   if(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']])
		   {
			r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['code']);
			r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['name']);
		   }
		   r.cell['price'].setValue(a['finalprice']);
		   r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['finalprice'],4);
		  }
		  r.cell['pricelist'].setValue(a['pricelist_name']);
		  r.cell['pricelist'].setAttribute("pricelistid",a['pricelist_id']);
		  r.cell['vendorname'].setValue(a['vendor_name']);

		  // variants //
		  if(a && a['variants'])
		  {
		   // colori e tinte //
		   var options = new Array();
		   if(a['variants']['colors'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['colors'].length; c++)
			 arr.push(a['variants']['colors'][c]['name']);
			options.push(arr);
		   }
		   if(a['variants']['tint'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['tint'].length; c++)
			 arr.push(a['variants']['tint'][c]['name']);
			options.push(arr);
		   }
		   r.cell['coltint'].setOptions(options);

		   // taglie, misure e altro //
		   var options = new Array();
		   if(a['variants']['sizes'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['sizes'].length; c++)
			 arr.push(a['variants']['sizes'][c]['name']);
			options.push(arr);
		   }
		   if(a['variants']['dim'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['dim'].length; c++)
			 arr.push(a['variants']['dim'][c]['name']);
			options.push(arr);
		   }
		   if(a['variants']['other'])
		   {
			var arr = new Array();
			for(var c=0; c < a['variants']['other'].length; c++)
			 arr.push(a['variants']['other'][c]['name']);
			options.push(arr);
		   }
		   r.cell['sizmis'].setOptions(options);
		  }

		  updateTotals(r);
		  DOCISCHANGED = true;
	}
  sh.sendCommand("commercialdocs getfullinfo -ap `"+data['ref_ap']+"` -id `"+data['ref_id']+"` -subjectid `"+subjectId+"` -pricelistid `"+CUSTPLID+"` --get-variants -qty '"+r.cell['qty'].getValue()+"'");
 }
}

function updatePriceByQty(r, value)
{
 /* GET SUBJECT */
 if(!RubricaEdit.value)
  var subjectId = 0;
 else if(RubricaEdit.data)
  var subjectId = RubricaEdit.data['id'];
 else
  var subjectId = 0;

 var ap = r.getAttribute('refap');
 var id = r.getAttribute('refid');
 var qty = value;
 var varType = "";
 var varName = "";
 if(r.cell['coltint'].getValue())
 {
  varType = "color";
  varName = r.cell['coltint'].getValue();
 }
 else if(r.cell['sizmis'].getValue())
 {
  varType = "size";
  varName = r.cell['sizmis'].getValue();
 }
 if(ap && id && qty)
 {
  var sh = new GShell();
  sh.OnError = function(){updateTotals(r);}
  sh.OnOutput = function(o,a){
	 if(!a) return updateTotals(r);
	 r.cell['unitprice'].setValue(a['finalprice']);
   	 r.cell['unitprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['finalprice'],4);
	 updateTotals(r);
	}
  var cmd = "commercialdocs get-price-by-qty -ap '"+ap+"' -id '"+id+"' -qty '"+qty+"' -vartype '"+varType+"' -varname `"+varName+"` -plid `"+CUSTPLID+"` -subjid '"+subjectId+"'";
  sh.sendCommand(cmd);
 }
 else
  updateTotals(r); 
}

function getInsArtDescription(data)
{
 /*'{CODE}','{BRAND}','{MODEL}','{BARCODE}','{MANCODE}','{VENCODE}',
	'{LOCATION}','{DIVISION}','{GEBINDECODE}','{GEBINDE}','{UNITS}','{WEIGHT}'*/

 var ret = INSART_SCHEMA;

 var weight = "";
 if(data['weight'])
  weight = data['weight']+(data['weightunits'] ? data['weightunits'] : "");

 var values = new Array(data['code_str'], data['brand'], data['model'], data['barcode'], data['manufacturer_code'], data['vencode'], data['item_location'], data['division'], data['gebinde_code'], data['gebinde'], data['units'], weight);
 
 for(var c=0; c < INSART_KEYS.length; c++)
  ret = ret.replace(INSART_KEYS[c], values[c]);

 return ret.trim();
}

function getUngroupedDDT(subjId)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a || !a['items'])
	  return;

	 var sh2 = new GShell();
	 sh2.OnError = function(err){alert(err);}
	 sh2.OnOutput = function(o,a){
		 if(!a) return;
		 document.location.reload();
		}

	 var q = "";
	 for(var c=0; c < a['items'].length; c++)
	  q+= ","+a['items'][c]['id'];

	 var title = "Seleziona i DDT da raggruppare";
	 var subtitle = "Ci sono "+a['items'].length+" DDT da raggruppare per questo fornitore. Desideri raggrupparli in questa fattura?";

	 sh2.sendCommand("gframe -f commercialdocs/selectdocs -params `destid=<?php echo $docInfo['id']; ?>&ids="+q.substr(1)+"` -t `"+title+"` -c `"+subtitle+"`");
	}
 
 sh.sendCommand("dynarc item-list -ap commercialdocs -into DDTIN -where `subject_id='"+subjId+"' AND conv_doc_id=0 AND group_doc_id=0` --order-by 'ctime,code_num ASC'");
}

function getOpenVendorOrders(subjId)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a || !a['items'])
	  return;

	 var sh2 = new GShell();
	 sh2.OnError = function(err){alert(err);}
	 sh2.OnOutput = function(o,docList){
		 if(!docList) return;

		 var sh3 = new GShell();
		 sh3.showProcessMessage("Importazione in corso", "Attendere prego, &egrave; in corso l&lsquo;importazione dei dati.");
		 sh3.OnError = function(err){this.processMessage.error(err);}
		 sh3.OnFinish = function(o,docInfo){
			 this.OnOutput(o,docInfo);
			 this.hideProcessMessage();
			}
		 sh3.OnOutput = function(o,docInfo){
		 	 if(!docInfo['elements'])
			  return;
			 InsertRow("note","Rif. "+docInfo['name']);
		 	 for(var i=0; i < docInfo['elements'].length; i++)
		 	 {
		  	  var el = docInfo['elements'][i];
			  if(parseFloat(el['qty_sent']) && (el['qty_sent'] == el['qty']))
			   continue;
		  	  insertElementFromData(el, 'commercialdocs', docInfo['id']);
		 	 }		 
			}

		 for(var c=0; c < docList.length; c++)
		  sh3.sendCommand("dynarc item-info -ap commercialdocs -id '"+docList[c]+"' -extget `cdelements`");
		}

	 var q = "";
	 for(var c=0; c < a['items'].length; c++)
	  q+= ","+a['items'][c]['id'];

	 var title = "Seleziona gli Ordini Fornitore da includere";
	 var subtitle = "Ci sono "+a['items'].length+" Ordini Fornitore per questo fornitore. Desideri inserirli in questo DDT?";

	 sh2.sendCommand("gframe -f commercialdocs/selectdocs -params `ids="+q.substr(1)+"` -t `"+title+"` -c `"+subtitle+"`");
	}
 
 sh.sendCommand("dynarc item-list -ap commercialdocs -into VENDORORDERS -where `subject_id='"+subjId+"' AND conv_doc_id=0 AND group_doc_id=0 AND entirely_proc=0` --order-by 'ctime,code_num ASC'");
}

function showRIFPAcontainer()
{
 document.getElementById('rifpa-container').style.display = "";
 var div = document.getElementById('doctable').parentNode;
 div.style.height = div.parentNode.offsetHeight-140;
}

function hideRIFPAcontainer()
{
 document.getElementById('rifpa-container').style.display = "none";
 var div = document.getElementById('doctable').parentNode;
 div.style.height = div.parentNode.offsetHeight-120;
}

function SaveAsPrecompiled()
{
 if(DOCISCHANGED && !LOCKED)
 {
  if(confirm("Il documento è stato modificato. Salvare le modifiche prima di creare il documento precompilato?"))
  {
   POSTSAVEACTION = "saveasprecomp";
   return saveDoc();
  }
 }

 var sh = new GShell();
 sh.showProcessMessage("Generazione in corso", "Attendere prego, è in corso la generazione del documento");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(o,a){
	 this.hideProcessMessage();
	 if(!a) return;
	 if(confirm("Il documento pre-compilato è stato creato con successo! Desideri visualizzarlo?"))
	  window.open(ABSOLUTE_URL+"GCommercialDocs/precdocinfo.php?id="+a['id']);
	}

 sh.sendCommand("gframe -f commercialdocs/saveasprecomp -params 'id=<?php echo $docInfo['id']; ?>'");
}

function updateAllItemPrices()
{
 var list = new Array();
 /* GET SUBJECT */
 if(!RubricaEdit.value)
  var subjectId = 0;
 else if(RubricaEdit.data)
  var subjectId = RubricaEdit.data['id'];
 else
  var subjectId = 0;

 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];
  if((r.getAttribute('type') == "note") || (r.getAttribute('type') == "message"))
   continue;
 
  var ap = r.itemInfo ? r.itemInfo['tb_prefix'] : r.getAttribute('refap');
  var id = r.itemInfo ? r.itemInfo['id'] : r.getAttribute('refid');

  if(!ap || !id) continue;
  if(id == 0) continue;

 
  list.push(r);
 }

 var sh = new GShell();
 sh.OnError = function(err){this.processMessage.error(err);}

 if(list.length)
 {
  sh.showProcessMessage("Aggiornamento in corso", "Attendere prego, è in corso l'aggiornamento dei prezzi degli articoli");
  updateNextItemPrice(list, 0, sh, subjectId);
 }
}

function updateNextItemPrice(list, idx, sh, subjectId)
{
 var r = list[idx];
 var ap = r.itemInfo ? r.itemInfo['tb_prefix'] : r.getAttribute('refap');
 var id = r.itemInfo ? r.itemInfo['id'] : r.getAttribute('refid');

 sh.OnOutput = function(o,a){
	 if(a)
	 {
	  r.itemInfo = a;
	  r.setAttribute('weightunits',a['weightunits']);
	  if(IS_VENDOR)	//IF IS VENDOR
	  {
	   if(a['vendor_id'] == subjectId)
	   {
	    r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']);
	    r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : a['vendor_vattype']);
	   }
	   else
	   {
	   r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : a['vatid']);
	   r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : a['vattype']);
	   }
	  }
	  else    // IF IS CUSTOMER
	  {
	   r.setAttribute('vatid',SUBJECT_VATID ? SUBJECT_VATID : a['vatid']);
	   r.setAttribute('vattype',SUBJECT_VATID ? SUBJECT_VATTYPE : a['vattype']);
	  }
	  r.setAttribute('vendorid',a['vendor_id']);
	  r.cell['vencode'].setValue(a['vencode']);
	  r.cell['weight'].setValue(a['weight'] ? a['weight']+" "+a['weightunits'] : "");

	  if(!IS_VENDOR && a['custompricing'])
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

	  if(IS_VENDOR)
	  {
	   if(a['vendor_id'] == subjectId)
	   {
	    r.cell['vendorprice'].setValue(a['vendor_price']);
	    r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
	   }
	   else
	   {
	    r.cell['vendorprice'].setValue(0);
	    r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(0,4);
	   }
	  }
	  else
	  {
	   r.cell['vendorprice'].setValue(a['vendor_price']);
	   r.cell['vendorprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
	  }
	  r.cell['unitprice'].setValue(a['variant_type'] ? a['variant_finalprice'] : a['finalprice']);
	  r.cell['unitprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['variant_type'] ? a['variant_finalprice'] : a['finalprice'],4);
	  r.cell['plbaseprice'].setValue(a['baseprice']);
	  r.cell['plbaseprice'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['baseprice'],4);
	  r.cell['plmrate'].setValue(a['pricelist_'+CUSTPLID+'_mrate'] ? a['pricelist_'+CUSTPLID+'_mrate'] : 0);
	  r.cell['pldiscperc'].setValue(a['pricelist_'+CUSTPLID+'_discount'] ? a['pricelist_'+CUSTPLID+'_discount'] : 0);

	  if(IS_VENDOR)
	  {
	   if(a['vendor_id'] == subjectId)
	   {
	    r.cell['vat'].setValue(SUBJECT_VATID ? SUBJECT_VATRATE : a['vendor_vatrate']);
		if(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']])
		{
		 r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']]['code']);
		 r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vendor_vatid']]['name']);
		}
	    r.cell['price'].setValue(a['vendor_price']);
	    r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['vendor_price'],4);
	   }
	   else
	   {
	    r.cell['vat'].setValue(SUBJECT_VATID ? SUBJECT_VATRATE : a['vat']);
		if(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']])
		{
		 r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['code']);
		 r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['name']);
		}
	    r.cell['price'].setValue(0);
	    r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(0,4);
	   }
	  }
	  else
	  {
	   r.cell['vat'].setValue(SUBJECT_VATID ? SUBJECT_VATRATE : a['vat']);
	   if(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']])
	   {
		r.cell['vatcode'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['code']);
		r.cell['vatname'].setValue(VAT_BY_ID[SUBJECT_VATID ? SUBJECT_VATID : a['vatid']]['name']);
	   }
	   r.cell['price'].setValue(a['variant_type'] ? a['variant_finalprice'] : a['finalprice']);
	   r.cell['price'].getElementsByTagName('SPAN')[0].title = "Valore reale: "+formatCurrency(a['variant_type'] ? a['variant_finalprice'] : a['finalprice'],4);
	  }
	  r.cell['pricelist'].setValue(a['pricelist_name']);
	  r.cell['pricelist'].setAttribute("pricelistid",a['pricelist_id']);
	  r.cell['vendorname'].setValue(a['vendor_name']);

	  updatePriceByQty(r, r.cell['qty'].getValue());

	  if(r.id && (UPDATED_ROWS.indexOf(r) < 0))
	   UPDATED_ROWS.push(r);

	 }
	 //---------------------------------------//
	 idx++;
	 if(list.length > idx)
	  updateNextItemPrice(list, idx, this);
	 else
	 {
	  /* FINISH */
	  this.hideProcessMessage();
	 }
	}

 sh.sendCommand("commercialdocs getfullinfo -ap `"+ap+"` -id `"+id+"` -subjectid `"+subjectId+"` -pricelistid `"+CUSTPLID+"` --get-variants -qty '"+r.cell['qty'].getValue()+"'");
}

function ExportToExcel()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.href = ABSOLUTE_URL+"getfile.php?file="+a['fullpath'];
	}

 sh.sendCommand("gframe -f commercialdocs/exportbodyelements -params 'id=<?php echo $docInfo['id']; ?>'");
}
</script>

</body></html>
<?php

