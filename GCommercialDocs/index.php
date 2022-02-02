<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 04-07-2017
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Gnujiko Commercial Documents.
 #VERSION: 2.16beta
 #CHANGELOG: 04-07-2017 : Link alla videata di modifica documento anche su campo Data.
			 20-04-2017 : Aggiunta colonna Guadagno.
			 24-02-2017 : Aggiunte opzioni su stampa massiva: pdf separati dentro uno zip, oppure tutte le stampe su un PDF unico.
			 23-02-2017 : Aggiunta funzione per stampa massiva.
			 12-02-2017 : Importazione documenti da XML.
			 05-02-2017 : Aggiunta possibilita di raggruppare ordini in fattura.
			 16-11-2016 : Importazione documenti da Excel.
			 25-07-2016 : Prima integrazione con transponder.
			 27-05-2016 : Aggiornata funzione gotoAboutConfig.
			 21-04-2016 : Aggiunta funzione esporta corpo su Excel.
			 07-03-2016 : Aggiornato status con evaso parziale.
			 16-02-2016 : Aggiunto filtra per numero di tracking.
			 12-05-2015 : Aggiunta funzione raggruppa rapporti d'intervento.
			 06-03-2015 : Aggiunto riepilogo fatture PA.
			 18-02-2015 : Aggiunto campo data-consegna.
 #TODO: completare funzione stampa.
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_CMD, $_RESTRICTED_ACCESS, $_DECIMALS, $_COMPANY_PROFILE, $_COMMERCIALDOCS_CONFIG, $template;

$_BASE_PATH = "../";
$_RESTRICTED_ACCESS = "commercialdocs";
$_AP = "commercialdocs";
$_DECIMALS = 2;

include($_BASE_PATH."var/templates/glight/index.php");

include_once($_BASE_PATH."include/company-profile.php");
include_once($_BASE_PATH."etc/commercialdocs/config.php");
$_BANKS = $_COMPANY_PROFILE['banks'];
$_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'];

$template = new GLightTemplate();
$template->includeObject("gcal");
$template->includeObject("editsearch");
$template->includeInternalObject("serp");
$template->includeInternalObject("contactsearch");
$template->includeInternalObject("labels");
$template->includeObject("printmanager");
$template->includeCSS('doclist.css');

loadLanguage("calendar");

//-------------------------------------------------------------------------------------------------------------------//
$_CAT_INFO = array();
$_ROOT_CAT = array();
$_CAT_TAG = "";
$_ROOT_CT = "";
$_EXTRA_MENU_ITEMS = array();
$_SUBJTYPE_NAME = "Cliente";
$_TRANSPONDER_SERVICE_TAGS = "";

if(($_REQUEST['show'] == "category") && $_REQUEST['catid'])
{
 $ret = GShell("dynarc cat-info -ap '".$_AP."' -id '".$_REQUEST['catid']."'");
 if(!$ret['error'])
 {
  $_CAT_INFO = $ret['outarr'];
  $ret = GShell("dynarc getrootcat -ap '".$_AP."' -id '".$_CAT_INFO['id']."'");
  if(!$ret['error'])
  {
   $_ROOT_CAT = $ret['outarr'];
   $_CAT_TAG = $_ROOT_CAT['tag'];
  }
 }
}
else if(!$_REQUEST['ct'])
{
 // get first category
 $ret = GShell("dynarc cat-list -ap '".$_AP."' -limit 1");
 if(!$ret['error'])
 {
  $_CAT_INFO = $_ROOT_CAT = $ret['outarr'][0];
  $_CAT_TAG = $_CAT_INFO['tag'];
  $_REQUEST['ct'] = strtolower($_CAT_TAG);
 }
}
else
{
 // get selected category info
 $ret = GShell("dynarc cat-info -ap '".$_AP."' -tag '".$_REQUEST['ct']."'");
 if(!$ret['error']) 
 {
  $_CAT_INFO = $_ROOT_CAT = $ret['outarr'];
  $_CAT_TAG = $_CAT_INFO['tag'];
 }
}

$k = array('tivi','dini','ture','tini','note','visi','vute','soci','scali');
$v = array('tivo','dine','tura','tino','nota','viso','vuta','socio','scale');
$singTitle = str_replace($k,$v,strtolower($_ROOT_CAT['name']));

/* GET CAUSAL */
$extraDocTypes = array();
if($_COMMERCIALDOCS_CONFIG['DOCTYPE'] && $_COMMERCIALDOCS_CONFIG['DOCTYPE'][$_CAT_TAG])
 $extraDocTypes = $_COMMERCIALDOCS_CONFIG['DOCTYPE'][$_CAT_TAG];

$ret = GShell("dynarc item-list -ap 'gcdcausal' -ct '".$_CAT_TAG."'");
if(!$ret['error'] && count($ret['outarr']['items']))
 $_CAUSALS = $ret['outarr']['items'];

$_TITLE_BAR = $_ROOT_CAT['name'].($_ROOT_CAT['id'] != $_CAT_INFO['id'] ? " - ".$_CAT_INFO['name'] : "");
$_ROOT_CT = strtolower($_ROOT_CAT['tag']);
$_GROUPNAME = "commdocs-".$_ROOT_CT;

// get labels
$_LABEL_BY_ID = array();
$_LABELS = array();
$ret = GShell("dynarc exec-func ext:labels.list -params `archiveprefix=commercialdocs`");
if(!$ret['error'])
{
 $_LABELS = $ret['outarr'];
 for($c=0; $c < count($_LABELS); $c++)
  $_LABEL_BY_ID[$_LABELS[$c]['id']] = $_LABELS[$c];
}


$template->Begin($_TITLE_BAR);
//-------------------------------------------------------------------------------------------------------------------//
$dt = strtotime(date('Y-m')."-01");
if(!$_REQUEST['dtfilt'])
 $_REQUEST['dtfilt'] = "lastquarter";

switch($_REQUEST['dtfilt'])
{
 case 'lastsemester' : {
	 $_REQUEST['from'] = date('Y-m-d',strtotime("-5 month",$dt)); 
	 $_REQUEST['to'] = date('Y-m-d');
	} break;

 case 'lastquarter' : {
	 $_REQUEST['from'] = date('Y-m-d',strtotime("-2 month",$dt)); 
	 $_REQUEST['to'] = date('Y-m-d');
	} break;

 case 'lastmonth' : {
	 $_REQUEST['from'] = date('Y-m-d',strtotime("-1 month",$dt)); 
	 $_REQUEST['to'] = date('Y-m-d');
	} break;

 case 'thismonth' : {
	 $_REQUEST['from'] = date('Y-m-d',$dt); 
	 $_REQUEST['to'] = date('Y-m-d');
	} break;

 case 'thisyear' : {
	 $_REQUEST['from'] = date('Y')."-01-01";
	 $_REQUEST['to'] = date('Y')."-12-31";
	} break;

 case 'lastyear' : {
	 $_REQUEST['from'] = date('Y',strtotime('-1 year',$dt))."-01-01";
	 $_REQUEST['to'] = date('Y',strtotime('-1 year',$dt))."-12-31";
	} break;
}

$dateFrom = $_REQUEST['from'] ? $_REQUEST['from'] : "";
$dateTo = $_REQUEST['to'] ? $_REQUEST['to'] : "";

$_DEF_RPP = 25;

switch($_CAT_TAG)
{
 case 'VENDORORDERS' : case 'PURCHASEINVOICES' : case 'DDTIN' : $_SUBJTYPE_NAME = "Fornitore"; break;
 case 'AGENTINVOICES' : $_SUBJTYPE_NAME = "Agente"; break;
 case 'MEMBERINVOICES' : $_SUBJTYPE_NAME = "Socio"; break;
}

$_COLUMNS = array(
 0 => array('title'=>'Documento', 		'field'=>'name', 			'width'=>180, 		'sortable'=>true, 	'visibled'=>true),
 1 => array('title'=>'Data',			'field'=>'ctime',			'width'=>80,		'sortable'=>true,	'visibled'=>false, 	'format'=>'date'),
 2 => array('title'=>'Causale',			'field'=>'causal',			'width'=>150,		'sortable'=>false,	'visibled'=>false),
 3 => array('title'=>'Cod. Cli',		'field'=>'subject_code',	'width'=>50,		'sortable'=>false,	'visibled'=>false),
 4 => array('title'=>$_SUBJTYPE_NAME,	'field'=>'subject_name',			'sortable'=>true,	'visibled'=>true),
 5 => array('title'=>'P.IVA',			'field'=>'subject_vatnumber', 	'width'=>80,	'sortable'=>false,	'visibled'=>false),
 6 => array('title'=>'Cod. Fisc.',		'field'=>'subject_taxcode', 	'width'=>80,	'sortable'=>false,	'visibled'=>false),
 7 => array('title'=>'Doc. rif. int.',	'field'=>'intdocref',		'width'=>150,		'sortable'=>false,	'visibled'=>false),
 8 => array('title'=>'Doc. rif. est.',	'field'=>'extdocref',		'width'=>150,		'sortable'=>false,	'visibled'=>false),
 9 => array('title'=>'Opzioni',			'field'=>'options',			'width'=>60,		'sortable'=>false,	'visibled'=>true),
 10 => array('title'=>'Etichette',		'field'=>'labels',			'width'=>220,		'sortable'=>false,	'visibled'=>false),
 11 => array('title'=>'Status',			'field'=>'status',			'width'=>160,		'sortable'=>false,	'visibled'=>true, 'fieldstyle'=>'text-align:center'),
 12 => array('title'=>'Status extra',	'field'=>'statusextra',		'width'=>120,		'sortable'=>false,	'visibled'=>false, 'fieldstyle'=>'text-align:center'),
 13 => array('title'=>'Div. materiale',	'field'=>'division',		'width'=>150,		'sortable'=>true,	'visibled'=>false),
 14 => array('title'=>'Data stampa',		'field'=>'printdate',		'width'=>80,		'sortable'=>true,	'visibled'=>false, 	'format'=>'date'),
 15 => array('title'=>'Data invio',		'field'=>'senddate',		'width'=>80,		'sortable'=>true,	'visibled'=>false, 	'format'=>'date'),
 16 => array('title'=>'Data validit&agrave;', 'field'=>'validitydate', 'width'=>80,	'sortable'=>true,	'visibled'=>false, 	'format'=>'date'),
 17 => array('title'=>'Inizio noleggio',	'field'=>'charterfrom',		'width'=>80,		'sortable'=>true,	'visibled'=>false, 	'format'=>'date'),
 18 => array('title'=>'Fine noleggio',	'field'=>'charterto',		'width'=>80,		'sortable'=>true,	'visibled'=>false, 	'format'=>'date'),
 19 => array('title'=>'Data trasporto',	'field'=>'transdate',		'width'=>80,		'sortable'=>true,	'visibled'=>false, 	'format'=>'date'),
 20 => array('title'=>'Spese trasp.',	'field'=>'cartage',			'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 21 => array('title'=>'Spese imballo.',	'field'=>'packingcharges',	'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 22 => array('title'=>'Spese incasso.', 'field'=>'collectioncharges', 'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 23 => array('title'=>'Riv. INPS', 		'field'=>'rivalsainps', 	'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 24 => array('title'=>'Cassa Prev.', 	'field'=>'cassaprev', 		'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 25 => array('title'=>'Enasarco', 		'field'=>'enasarco', 		'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 26 => array('title'=>'Rit. Acc.', 		'field'=>'ritacconto', 		'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 27 => array('title'=>'Abbuoni', 		'field'=>'rebate', 			'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 28 => array('title'=>'Bolli', 			'field'=>'stamp', 			'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 29 => array('title'=>'Tot. spese', 	'field'=>'expenses', 		'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 30 => array('title'=>'Imponibile',		'field'=>'amount',			'width'=>70,		'sortable'=>true,	'visibled'=>false,	'format'=>'currency'),
 31 => array('title'=>'I.V.A.',			'field'=>'vat',				'width'=>60,		'sortable'=>true,	'visibled'=>false,	'format'=>'currency'),
 32 => array('title'=>'Totale',			'field'=>'total',			'width'=>70,		'sortable'=>true,	'visibled'=>false,	'format'=>'currency'),
 33 => array('title'=>'Netto a pagare', 'field'=>'netpay', 			'width'=>70,		'sortable'=>true,	'visibled'=>true, 	'format'=>'currency'),
 34 => array('title'=>'Guadagno', 		'field'=>'profit', 			'width'=>70,		'sortable'=>true,	'visibled'=>true, 	'format'=>'currency'),
 35 => array('title'=>'Tot. pagato',	'field'=>'totpaid',			'width'=>70,		'sortable'=>true,	'visibled'=>false,	'format'=>'currency'),
 36 => array('title'=>'Da pagare',		'field'=>'resttopay',		'width'=>70,		'sortable'=>true,	'visibled'=>false,	'format'=>'currency'),
 37 => array('title'=>'Agente', 		'field'=>'agent',			'width'=>100,		'sortable'=>false,	'visibled'=>false),
 38 => array('title'=>'Commiss. Agente', 'field'=>'agentcommiss',	'width'=>70,		'sortable'=>true,	'visibled'=>false,	'format'=>'currency'),
 39 => array('title'=>'Operatore', 		'field'=>'owner',			'width'=>100,		'sortable'=>false,	'visibled'=>false),
 40 => array('title'=>'Gruppo', 		'field'=>'group',			'width'=>100,		'sortable'=>false,	'visibled'=>false),
 41 => array('title'=>'Permessi', 		'field'=>'perms',			'width'=>100,		'sortable'=>false,	'visibled'=>false),
 42 => array('title'=>'Data cons.',		'field'=>'deliverydate',	'width'=>80,		'sortable'=>true,	'visibled'=>false, 	'format'=>'date'),
);

$_FOOTER_COLUMNS = array(
 0 => array('title'=>'IMPONIBILE',		'field'=>'amount',			'width'=>80,		'visibled'=>true),
 1 => array('title'=>'I.V.A.',			'field'=>'vat',				'width'=>80,		'visibled'=>true),
 2 => array('title'=>'TOTALE',			'field'=>'total',			'width'=>80,		'visibled'=>false),
 3 => array('title'=>'RIT. ACC.',		'field'=>'ritacconto',			'width'=>80,		'visibled'=>false),
 4 => array('title'=>'CASSA PREV.',		'field'=>'cassaprev',				'width'=>100,		'visibled'=>false),
 5 => array('title'=>'RIV. INPS',		'field'=>'rivalsainps',			'width'=>80,		'visibled'=>false),
 6 => array('title'=>'ENASARCO',		'field'=>'enasarco',		'width'=>80,		'visibled'=>false),
 7 => array('title'=>'ABBUONI',			'field'=>'rebate',			'width'=>80,		'visibled'=>false),
 8 => array('title'=>'BOLLI',			'field'=>'stamp',			'width'=>80,		'visibled'=>false),
 9 => array('title'=>'SPESE',			'field'=>'expenses',		'width'=>80,		'visibled'=>false),
 10 => array('title'=>'SCONTI',			'field'=>'discount',		'width'=>80,		'visibled'=>false),
 11 => array('title'=>'SP. TRASP.',		'field'=>'cartage',			'width'=>80,		'visibled'=>false),
 12 => array('title'=>'SP. IMBALLO',	'field'=>'packingcharges',	'width'=>80,		'visibled'=>false),
 13 => array('title'=>'SP. INCASSO',	'field'=>'collectioncharges', 'width'=>80,		'visibled'=>false),
 14 => array('title'=>'COMM. AGENTE',	'field'=>'agentcommiss',	'width'=>100,		'visibled'=>false),
 15 => array('title'=>'NETTO A PAGARE',	'field'=>'netpay',			'width'=>100,		'visibled'=>true),
 16 => array('title'=>'GUADAGNO',		'field'=>'profit',			'width'=>100,		'visibled'=>true),
 17 => array('title'=>'TOT. PAGATO',	'field'=>'totpaid',			'width'=>80,		'visibled'=>false),
 18 => array('title'=>'DA PAGARE', 		'field'=>'resttopay',		'width'=>80,		'visibled'=>false)
);

/* GET COLUMN SETTINGS */
$ret = GShell("aboutconfig get -app gcommercialdocs -sec documentlist");
if(!$ret['error'])
{
 $settings = $ret['outarr']['config'];
 if(is_array($settings[$_ROOT_CT]))
 {
  $visibledColumns = explode(",",$settings[$_ROOT_CT]['visibledcolumns']);
  for($c=0; $c < count($_COLUMNS); $c++)
  {
   $col = $_COLUMNS[$c];
   if(in_array($col['field'], $visibledColumns))
	$_COLUMNS[$c]['visibled'] = true;
   else
	$_COLUMNS[$c]['visibled'] = false;
  }
  for($c=0; $c < count($_FOOTER_COLUMNS); $c++)
  {
   $col = $_FOOTER_COLUMNS[$c];
   if(in_array($col['field'], $visibledColumns))
	$_FOOTER_COLUMNS[$c]['visibled'] = true;
   else
	$_FOOTER_COLUMNS[$c]['visibled'] = false;
  }
  if($settings[$_ROOT_CT]['rpp'])
   $_DEF_RPP = $settings[$_ROOT_CT]['rpp'];
 }
}


$centerContents = "<input type='text' class='edit' style='width:290px;float:left' placeholder='Ricerca per ".strtolower($_SUBJTYPE_NAME)."' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\" modal='extended' fields='code_str,name' contactfields='phone,phone2,cell,email' disablerightbtn='true'/>";
$centerContents.= "<input type='button' class='button-search' id='searchbtn'/>";

$_DATE_FILTERS = array('lastsemester'=>'Ultimi 6 mesi', 'lastquarter'=>'Ultimi 3 mesi', 'lastmonth'=>'Ultimi 2 mesi', 'thismonth'=>'Questo mese',
	'thisyear'=>'Quest&lsquo; anno', 'lastyear'=>'Anno scorso',
	''=>'', 
	'ctime'=>'Filtra x data documento', 'validity_date'=>'Filtra x data validit&agrave;', 'charter_datefrom'=>'Filtra x data inizio noleggio', 'charter_dateto'=>'Filtra x data fine noleggio', 'trans_datetime'=>'Filtra x data trasporto', 'delivery_date'=>'Filtra x data consegna');
if(!$_REQUEST['dtfilt'])
 $_REQUEST['dtfilt'] = 'ctime';

$centerContents.= "<input type='text' class='dropdown' id='dtfilt' connect='dtfiltlist' readonly='true' style='width:150px;margin-left:30px' value='".$_DATE_FILTERS[$_REQUEST['dtfilt']]."' retval='".$_REQUEST['dtfilt']."'/>";
$centerContents.= "<ul class='popupmenu' id='dtfiltlist'>";
reset($_DATE_FILTERS);
while(list($k,$v)=each($_DATE_FILTERS))
{ 
 if(!$k || !$v)
  $centerContents.= "<li class='separator'>&nbsp;</li>";  
 else
  $centerContents.= "<li value='".$k."'>".$v."</li>"; 
}
$centerContents.= "</ul>";

$centerContents.= "<input type='text' class='calendar' value='".($dateFrom ? date('d/m/Y',strtotime($dateFrom)) : '')."' id='datefrom'/>";
$centerContents.= "<span class='smalltext'> al </span>";
$centerContents.= "<input type='text' class='calendar' value='".($dateTo ? date('d/m/Y',strtotime($dateTo)) : '')."' id='dateto'/>";
$centerContents.= " <img src='".$_ABSOLUTE_URL."share/icons/16x16/view-refresh.png' style='cursor:pointer' onclick='doSearchByDate()' title='Effettua la ricerca per data'/>";

if(!$_REQUEST['show'] && !$_REQUEST['statusextra'])
 $_REQUEST['show'] = "all";

$template->Header("search", $centerContents, "BTN_EXIT", 800);
//-------------------------------------------------------------------------------------------------------------------//
$_ORDER_BY = $_REQUEST['sortby'] ? $_REQUEST['sortby'] : "ctime,code_num";
$_ORDER_METHOD = $_REQUEST['sortmethod'] ? strtoupper($_REQUEST['sortmethod']) : "DESC";
$_RPP = $_REQUEST['rpp'] ? $_REQUEST['rpp'] : $_DEF_RPP;
$_PG = $_REQUEST['pg'] ? $_REQUEST['pg'] : 1;

$_SERP = new SERP();
$_SERP->setOrderBy($_ORDER_BY);
$_SERP->setOrderMethod($_ORDER_METHOD);
$_SERP->setResultsPerPage($_RPP);
$_SERP->setCurrentPage($_PG);

 /* RICERCA NORMALE */
 $cmd = "dynarc item-list -ap '".$_AP."'";
 if($_REQUEST['catid'])	$cmd.= " -cat '".$_CAT_INFO['id']."'";
 else 					$cmd.= " -into '".$_CAT_INFO['id']."'";
 $cmd.= " -extget 'cdinfo.all,.profits,labels'"; 
 $where = "";
 if($_REQUEST['from'] || $_REQUEST['to'])
 {
  switch($_REQUEST['dtfilt'])
  {
   case 'validity_date' : {
	 if($_REQUEST['from'])	$where.= " AND validity_date>='".$_REQUEST['from']."'";
	 if($_REQUEST['to'])	$where.= " AND validity_date<'".$_REQUEST['to']." 23:59:59'";
	} break;

   case 'charter_datefrom' : {
	 if($_REQUEST['from'])	$where.= " AND charter_datefrom>='".$_REQUEST['from']."'";
	 if($_REQUEST['to'])	$where.= " AND charter_datefrom<'".$_REQUEST['to']." 23:59:59'";
	} break;

   case 'charter_dateto' : {
	 if($_REQUEST['from'])	$where.= " AND charter_dateto>='".$_REQUEST['from']."'";
	 if($_REQUEST['to'])	$where.= " AND charter_dateto<'".$_REQUEST['to']." 23:59:59'";
	} break;

   case 'trans_datetime' : {
	 if($_REQUEST['from'])	$where.= " AND trans_datetime>='".$_REQUEST['from']."'";
	 if($_REQUEST['to'])	$where.= " AND trans_datetime<'".$_REQUEST['to']." 23:59:59'";
	} break;

   case 'delivery_date' : {
	 if($_REQUEST['from'])	$where.= " AND delivery_date>='".$_REQUEST['from']."'";
	 if($_REQUEST['to'])	$where.= " AND delivery_date<'".$_REQUEST['to']." 23:59:59'";
	} break;

   default : {	
	 if($_REQUEST['from'])	$where.= " AND ctime>='".$_REQUEST['from']."'";
	 if($_REQUEST['to'])	$where.= " AND ctime<'".$_REQUEST['to']." 23:59:59'";
	} break;

  }
 }
 
 if($_REQUEST['subjectid'] == "anonymous")	$where.= " AND subject_id='0'";
 else if($_REQUEST['subjectid'])			$where.= " AND subject_id='".$_REQUEST['subjectid']."'";

 if($_REQUEST['show'] == "trash")
 {	
  $where.= " AND trash='1'";
  $_TITLE_BAR.= " nel cestino";
 }
 else
 {
  switch($_CAT_TAG)
  {
   case 'PREEMPTIVES' : {
	 if($_REQUEST['show'] == 'tosend')
	 {
	  $where.= " AND status='0'";
	  $_TITLE_BAR.= " da inviare";
	 }
	} break;

   case 'ORDERS' : {
	 switch($_REQUEST['show'])
	 {
	  case 'working' : 			$where.= " AND status='4'"; break;
	  case 'suspended' : 		$where.= " AND status='5'"; break;
	  case 'failed' : 			$where.= " AND status='6'"; break;
	  case 'completed' : 		$where.= " AND status='7'"; break;
	 }
	 $_EXTRA_MENU_ITEMS[] = "<li onclick=\"groupSelected()\"><img src='".$_ABSOLUTE_URL."share/icons/16x16/copy.png'/>Raggruppa selezionati</li>";
	} break;

   case 'DDT' : {
	 switch($_REQUEST['show'])
	 {
	  case 'ungroupped' : {
		 $where.= " AND conv_doc_id='0' AND group_doc_id='0'"; break;
		}
	  case 'groupped' : 		$where.= " AND status='9'"; break;
	  case 'internal' : 		$where.= " AND tag='INTERNAL'"; break;
	 }	 
	 $_EXTRA_MENU_ITEMS[] = "<li onclick=\"groupSelected()\"><img src='".$_ABSOLUTE_URL."share/icons/16x16/copy.png'/>Raggruppa selezionati</li>";
	} break;

   case 'INVOICES' : {
	 switch($_REQUEST['show'])
	 {
	  case 'tobepaid' : 		$where.= " AND status<10"; break;
	 }
	} break;

   case 'VENDORORDERS' : {	 
	 switch($_REQUEST['show'])
	 {
	  case 'opened' : 			$where.= " AND status='0'"; break;
	 }
	} break;

   case 'PURCHASEINVOICES' : {
	 switch($_REQUEST['show'])
	 {
	  case 'tobepaid' : 		$where.= " AND status<10"; break;
	  case 'paid' : 			$where.= " AND status=10"; break;
	 }	 
	} break;

   case 'AGENTINVOICES' : {
	 switch($_REQUEST['show'])
	 {
	  case 'tobeemit' : 		$where.= " AND status='0'"; break;
	 }
	} break;

   case 'INTERVREPORTS' : {
	 switch($_REQUEST['show'])
	 {
	  case 'ungroupped' : 		$where.= " AND conv_doc_id=0 AND group_doc_id=0"; break;
	  case 'groupped' : 		$where.= " AND status=9"; break;
	 }
	 $_EXTRA_MENU_ITEMS[] = "<li onclick=\"groupSelected()\"><img src='".$_ABSOLUTE_URL."share/icons/16x16/copy.png'/>Raggruppa selezionati</li>";	
	} break;

   case 'CREDITSNOTE' : {
	 switch($_REQUEST['show'])
	 {
	  case 'tobepaid' : 		$where.= " AND status<10"; break;
	 }	 
	} break;

   case 'DEBITSNOTE' : {
	 switch($_REQUEST['show'])
	 {
	  case 'tobepaid' : 		$where.= " AND status<10"; break;
	 }	 
	} break;

   case 'RECEIPTS' : {
	 switch($_REQUEST['show'])
	 {
	  case 'tobepaid' : 		$where.= " AND status<10"; break;
	 }	 
	} break;

   case 'DDTIN' : {
	 switch($_REQUEST['show'])
	 {
	  case 'ungroupped' : 		$where.= " AND conv_doc_id='0' AND group_doc_id='0'"; break;
	  case 'groupped' : 		$where.= " AND status='9'"; break;
	  case 'internal' : 		$where.= " AND tag='INTERNAL'"; break;
	 }
	 $_EXTRA_MENU_ITEMS[] = "<li onclick=\"groupSelected('PURCHASEINVOICES')\"><img src='".$_ABSOLUTE_URL."share/icons/16x16/copy.png'/>Raggruppa selezionati</li>";
	} break;

   case 'MEMBERINVOICES' : {
	 switch($_REQUEST['show'])
	 {
	  case 'tobeemit' : 		$where.= " AND status='0'"; break;
	 }
	} break;

  }
 }
 if($_REQUEST['statusextra'])	$where.= " AND status_extra='".$_REQUEST['statusextra']."'";
 if($_REQUEST['label'])			$where.= " AND user_labels LIKE '%,".$_REQUEST['label'].",%'";
 else if($_REQUEST['untagged']) $where.= " AND user_labels=''";
 if($_REQUEST['tracknum'])
  $where.= " AND (tracking_number LIKE '%".$_REQUEST['tracknum']."' OR tracking_number LIKE '%".$_REQUEST['tracknum']."%' OR tracking_number LIKE '".$_REQUEST['tracknum']."%')";


 if($where)	$cmd.= " -where <![CDATA[".ltrim($where,' AND ')."]]>";
 if($_REQUEST['show'] == "trash")			$cmd.= " --include-trash";
 else $cmd.= " -totals 'amount,vat,total,tot_rit_acc,tot_ccp,tot_rinps,tot_enasarco,tot_netpay,rebate,tot_expenses"
	.",tot_discount,stamp,cartage,packing_charges,agent_commiss,collection_charges,tot_paid,rest_to_pay'";

$_CMD = $cmd;
$ret = $_SERP->SendCommand($cmd);
$_DOCUMENT_LIST = $ret['items'];
$_TOTALS = $_SERP->Return['totals'];

// TRANSPONDER
if(($_CAT_TAG == "ORDERS") && file_exists($_BASE_PATH.$_SHELL_CMD_PATH."transponder.php"))
{
 $_SERVICE_TAGS = "joomshopping,virtuemart,ebay,amazon";
 $template->config['transponder'] = array('service_tags'=>$_SERVICE_TAGS, 'servers'=>array());
 $ret = GShell("transponder server-list --service-tags '".$_SERVICE_TAGS."'");
 if(!$ret['error']) $template->config['transponder']['servers'] = $ret['outarr'];
 $_TRANSPONDER_SERVICE_TAGS = $template->config['transponder']['service_tags'];
}

//-------------------------------------------------------------------------------------------------------------------//
$template->SubHeaderBegin(0);
?>
 <?php
 if($_REQUEST['show'] == "trash")
 {
  ?>
  <input type='button' class='button-blue' value="Svuota il cestino" onclick="emptyTrash()"/>
  <?php
 }
 else
 {
  ?>
  <input type='button' class='button-blue' value="Crea <?php echo $singTitle; ?>" onclick="NewDocument()"/>
  <?php
 }
 ?>
 </td>
 <td>
	<input type='button' class="button-blue menuwhite" value="Menu" connect='mainmenu' id='mainmenubutton'/>
	<ul class='popupmenu' id='mainmenu'>
	 <?php
	 if($_REQUEST['show'] == "trash")
	 {
	  ?>
  	  <li onclick="restoreSelected()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/export2.png" height="16"/>Ripristina selezionati</li>
	  <li class='separator'>&nbsp;</li>
  	  <li onclick="deleteSelected(true)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif" height="16"/>Elimina dal cestino</li>
  	  <li onclick="emptyTrash()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/trash.gif" height="16"/>Svuota il cestino</li>
	  <?php
	 }
	 else
	 {
	 ?>
  	 <li onclick="NewDocument()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/new_file.png" height="16"/>Crea <?php echo $singTitle; ?></li>
	 <li onclick="ShowSummary()"><img src="<?php echo $_ABSOLUTE_URL.$template->config['basepath']; ?>img/summary-doc.png" height="16"/>Mostra riepilogo <?php echo strtolower($_TITLE_BAR); ?></li>
	 <?php
	 if(file_exists($_BASE_PATH.$template->config['basepath']."fatturepa.php"))
	 {
	  ?>
	  <li onclick="ShowFatturePA()"><img src="<?php echo $_ABSOLUTE_URL.$template->config['basepath']; ?>img/summary-doc.png" height="16"/>Riepilogo fatture elettroniche</li>
	  <?php
	 }
	 ?>
	 <li class='separator'>&nbsp;</li>
	 <li onclick="FilterByTracknum()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/lorry.gif" height="16"/>Cerca per numero di tracking</li>
	 <li onclick="ShowAnonymous()"><img src="<?php echo $_ABSOLUTE_URL.$template->config['basepath']; ?>img/anonymous.png" height="16"/>Mostra <?php echo strtolower($_TITLE_BAR); ?> anonimi</li>
	 <?php
	 if($_CAT_TAG == "ORDERS")
	 {
	  if(is_array($template->config['transponder']) && count($template->config['transponder']['servers']))
	  {
	   echo "<li class='separator'>&nbsp;</li>";
	   echo "<li><img src='".$_ABSOLUTE_URL."share/icons/16x16/icon_websites.gif' width='16'/>Carica dal sito";
	   echo "<ul class='popupmenu'>";
	   for($c=0; $c < count($template->config['transponder']['servers']); $c++)
	   {
	    $transponderServerInfo = $template->config['transponder']['servers'][$c];
	    echo "<li onclick='getOrdersFromWebsite(this)' data-serverid='".$transponderServerInfo['id']."' data-servicetag='"
			.$transponderServerInfo['tag']."'>".$transponderServerInfo['name']
			.($transponderServerInfo['tagname'] ? " - ".$transponderServerInfo['tagname'] : "")."</li>";
	   }
	   echo "<li class='separator'>&nbsp;</li>";
	   echo "<li onclick='getOrdersFromWebsite()'>Seleziona server</li>";
	   echo "</ul>";
	   echo "</li>";
	  }
	 }
	 ?>
	 <li class='separator'>&nbsp;</li>
	 <li onclick='ImportFromXML()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/xml.gif"/>Importa da file XML</li>
	 <?php
	  if(file_exists($_BASE_PATH."etc/excel_parsers/commdocsprec.php"))
	  {
	   echo "<li onclick='ImportFromExcelByPrecomp(this)'><img src='".$_ABSOLUTE_URL."share/icons/16x16/import.png'/>Importa da Excel (da doc. precompilati)</li>";
	  }
	  if(file_exists($_BASE_PATH."etc/excel_parsers/commdocs.php"))
	  {
	   $excelImportParsers = array();
	   if(file_exists($_BASE_PATH."etc/excel_parsers/commdocs_amazon.php"))
		$excelImportParsers[] = array('parser'=>'commdocs_amazon', 'name'=>'Amazon');
	   if(file_exists($_BASE_PATH."etc/excel_parsers/commdocs_groupon.php"))
		$excelImportParsers[] = array('parser'=>'commdocs_groupon', 'name'=>'Groupon');

	   if(count($excelImportParsers))
	   {
		echo "<li><img src='".$_ABSOLUTE_URL."share/icons/16x16/import.png'/>Importa da Excel";
		echo "<ul class='popupmenu'>";
		for($c=0; $c < count($excelImportParsers); $c++)
		 echo "<li onclick='ImportFromExcel(this,\"".$excelImportParsers[$c]['parser']."\")'><img src='".$_ABSOLUTE_URL."share/icons/16x16/import.png'/>importa da file Excel formato ".$excelImportParsers[$c]['name']."</li>";
		echo "<li class='separator'></li>";
		echo "<li onclick='ImportFromExcel(this)'><img src='".$_ABSOLUTE_URL."share/icons/16x16/import.png'/>altro...</li>";
		echo "</ul>";
		echo "</li>";
	   }
	   else
	    echo "<li onclick='ImportFromExcel(this)'><img src='".$_ABSOLUTE_URL."share/icons/16x16/import.png'/>Importa da Excel</li>";
	  }
	 ?>
	 <li onclick="ExportToExcel(this)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/excel.png"/>Esporta su file Excel</li>
	 <li onclick="ExportBodyToExcel(this)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/excel.png"/>Esporta corpo su file Excel</li>
	 <!-- <li onclick="Print(this)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/printer.gif"/>Stampa</li> -->
	 <?php
	 if(count($_EXTRA_MENU_ITEMS))
	 {
	  echo "<li class='separator'>&nbsp;</li>";
	  for($c=0; $c < count($_EXTRA_MENU_ITEMS); $c++)
	   echo $_EXTRA_MENU_ITEMS[$c];
	 }
	 ?>
	 <li class='separator'>&nbsp;</li>
	 <li><img src="<?php echo $_ABSOLUTE_URL.$template->config['basepath']; ?>img/status-icon.png"/>Modifica status
	  <ul class='popupmenu'>
	   <li onclick="editStatus(0)">Aperto</li>
	   <li onclick="editStatus(3)">In attesa</li>
	   <li onclick="editStatus(4)">In lavorazione</li>
	   <li onclick="editStatus(5)">Sospeso</li>
	   <li onclick="editStatus(6)">Fallito</li>
	   <li onclick="editStatus(7)">Completato</li>
	  </ul>
	 </li>
	 <?php
	 /* STATUS EXTRA */
	 $_STATUSEXTRA = array();
	 if($_COMMERCIALDOCS_CONFIG['STATUSEXTRA'] && $_COMMERCIALDOCS_CONFIG['STATUSEXTRA'][$_CAT_TAG])
	 {
	  $_STATUSEXTRA = $_COMMERCIALDOCS_CONFIG['STATUSEXTRA'][$_CAT_TAG];
	  reset($_STATUSEXTRA);
	  ?>
	  <li><img src="<?php echo $_ABSOLUTE_URL.$template->config['basepath']; ?>img/status-icon.png"/>Modifica extra-status
	   <ul class='popupmenu'>
	   <?php
		 while(list($k,$v) = each($_STATUSEXTRA))
		 {
		  echo "<li onclick='editStatusExtra(\"".$k."\")'>".($v['tabtitle'] ? $v['tabtitle'] : $v['title'])."</li>";
		 }
	   ?>
	   </ul>
	  </li>
	  <?php
	 }
	 ?>
	 <li class='separator'>&nbsp;</li>
	 <li onclick="deleteSelected()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/trash.gif"/>Elimina selezionati</li>
	 <li class='separator'>&nbsp;</li>
	 <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/printer.gif"/>Stampa documenti selezionati
	  <ul class='popupmenu'>
	   <li onclick="PrintSelected()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/page_white_compressed.gif"/>su file PDF separati (compressi in un file .ZIP)</li>
	   <li onclick="PrintSelected(true)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/doc_group.gif"/>su un unico PDF</li>
	  </ul>
	 </li>
	 <li onclick="ConfigureLabels()"><img src="img/tag.png"/>Configura etichette</li>
	 <li onclick="gotoAboutConfig()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/cog.gif"/>Configurazione avanzata</li>
	 <?php
	 }
	 ?>
	</ul>

	<input type='button' class="button-gray menu" value="Visualizza" connect="viewmenu" id="viewmenubutton"/>
	<ul class='popupmenu' id='viewmenu' style='width:320px'>
	<?php
	for($c=0; $c < count($_COLUMNS); $c++)
	{
	 $col = $_COLUMNS[$c];
	 $checked = $col['visibled'] ? true : false;
	 echo "<li style='width:150px;float:left'><input type='checkbox'".($checked ? " checked='true'" : "")." onchange=\"showColumn('".$col['field']."',this)\"/>".$col['title']."</li>";
	}
	if($_REQUEST['show'] != "trash")
	{
	 ?>
	 <li class='separator' style='clear:both'>&nbsp;</li>
	 <li onclick="saveGlobalSettings()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/save.gif"/>Salva configurazione</li>
	 <?php
	}
	?>
	</ul>

 <input type='button' class="button-tag" title="Etichetta" id="tagbutton" ap="commercialdocs" style="visibility:hidden;margin-top:1px"/>

 <?php
 echo "<input type='text' class='dropdown' id='labelfilter' connect='labellist' style='width:200px' placeholder='Filtra per etichetta' value='".($_REQUEST['label'] ? $_LABEL_BY_ID[$_REQUEST['label']]['name'] : "")."' retval='".$_REQUEST['label']."' readonly='true'/>";
 echo "<ul class='popupmenu' id='labellist'>";
 echo "<li value=''>mostra tutti</li>";
 for($c=0; $c < count($_LABELS); $c++)
 {
  echo "<li value='".$_LABELS[$c]['id']."'>".$_LABELS[$c]['name']."</li>";
 }
 echo "<li class='separator'>&nbsp;</li>";
 echo "<li value='0'>tutti quelli senza etichette</li>";
 echo "</ul>";
 ?>
 </td>
 <td>
  &nbsp;
 </td>
 <td width='130'>
	<span class='smalltext'>Mostra</span>
	<input type='text' class='dropdown' id='rpp' value="<?php echo $_RPP; ?> righe" retval="<?php echo $_RPP; ?>" readonly='true' connect='rpplist' style='width:80px'/>
	<ul class='popupmenu' id='rpplist'>
	 <li value='10'>10 righe</li>
	 <li value='25'>25 righe</li>
	 <li value='50'>50 righe</li>
	 <li value='100'>100 righe</li>
	 <li value='250'>250 righe</li>
	 <li value='500'>500 righe</li>
	</ul>
 </td>
 <td width='223' align='right'>
	<?php $_SERP->DrawSerpButtons(true);
 
$template->SubHeaderEnd();
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
$template->Body("fullspace");
//-------------------------------------------------------------------------------------------------------------------//
?>
<table width="100%" height="80%" cellspacing="0" cellpadding="0" border="0" id="template-outer-mask">
 <tr><td class="bg-lightgray" style="width:270px;<?php if($_REQUEST['hideleftsection']) echo 'display:none'; ?>" valign="top" id="template-left-section">
	<div class='advanced-search-title'><span id='advanced-search-title'>DOCUMENTI COMMERCIALI</span>
	 <img src="<?php echo $_ABSOLUTE_URL.$template->config['basepath']; ?>img/hidearrow.png" style="float:right;margin-top:12px;cursor:pointer" title="Nascondi barra laterale" onclick="HideLeftSection()"/>
	</div>
	<div class='doctypelist' id='doctypelist' style='display:none'>
	<?php
	showMainMenu($template);
	?>
	</div>
    <?php
	 if(file_exists($_BASE_PATH."GCommercialDocs/precdocinfo.php"))
	 {
	  ?>
	  <div class='doctypelist' id='precdoctypelist' style='display:none'>
	   <?php
		echo "<ul class='glight-main-menu'>";

		$ret = GShell("dynarc cat-list -ap commercialdocsprec -where 'parent_id=0 AND published=1'");
		$list = $ret['outarr'];
		for($c=0; $c < count($list); $c++)
		{
		 $catInfo = $list[$c];
		 $ct = strtolower($catInfo['tag']);
		 switch($ct)
		 {
		  case 'preemptives' : $icon = "icons/doc-blue.png"; break;
		  case 'orders' : $icon = "icons/doc-orange.png"; break;
		  case 'ddt' : $icon = "icons/doc-violet.png"; break;
		  case 'invoices' : $icon = "icons/doc-green.png"; break;
		  case 'vendororders' : $icon = "icons/doc-red.png"; break;
		  case 'purchaseinvoices' : case 'paymentnotice' : $icon = "icons/doc-yellow.png"; break;
		  case 'intervreports' : $icon = "icons/doc-maroon.png"; break;
		  case 'creditsnote' : $icon = "icons/doc-sky.png"; break;
		  case 'debitsnote' : $icon = "icons/doc-red.png"; break;

		  default : $icon = "icons/doc-gray.png"; break;
		 }
		 echo "<a href='".$_ABSOLUTE_URL.$template->config['basepath']."precdoclist.php?ct=".$ct."'><li class='item'>";
		 echo "<img src='".$_ABSOLUTE_URL.$template->config['basepath'].$icon."'/>";
		 echo "<span class='item-title-singleline'>".$catInfo['name']."</span>";
		 echo "</li></a>";
		}
		echo "</ul>";
	   ?>
	  </div>

	  <div class='precdoc-button-outer'>
	   <div class='precdoc-button' id='precdoc-button' onclick="showPrecDocTypeList()">
		Documenti pre-compilati &raquo;
	   </div>
	   <div class='standarddoc-button' id='standarddoc-button' onclick="hidePrecDocTypeList()" style='display:none'>
		&laquo; torna ai documenti commerciali
	   </div>
	  </div>
	  <?php
	 }
    ?>
	</td>
	<td style="width:8px" valign="top"><div class="vertical-gray-separator" id="template-left-bar" <?php if($_REQUEST['hideleftsection']) echo "style='cursor:pointer' onclick='ShowLeftSection()' title='Mostra barra laterale'"; ?>></div></td>
	<td class="page-contents" valign="top">
	  <div class="simpletab-centered" style="margin-top:5px">
	  	<ul class='simpletab-centered'>
	   	 <li <?php if($_REQUEST['show'] == 'all') echo "class='selected'"; ?>><a href='#' onclick="setShow('all')">Tutti</a></li>
		 <?php
		 $_EXTRA_TABS = array();
		 switch($_CAT_TAG)
		 {
		  case 'PREEMPTIVES' : {
			 echo "<li class='separator'></li>";
			 echo "<li".($_REQUEST['show'] == 'tosend' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('tosend')\">Da inviare</a></li>";
			} break;

		  case 'DDT' : {
			 echo "<li class='separator'></li>";
			 echo "<li".($_REQUEST['show'] == 'ungroupped' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('ungroupped')\">Da raggruppare</a></li>";
			 echo "<li".($_REQUEST['show'] == 'groupped' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('groupped')\">Raggruppati</a></li>";
			 echo "<li".($_REQUEST['show'] == 'internal' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('internal')\">Bolle movim. int.</a></li>";
			} break;

		  case 'INVOICES' : {
			 echo "<li class='separator'></li>";
			 echo "<li".($_REQUEST['show'] == 'tobepaid' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('tobepaid')\">Da pagare</a></li>";
			} break;

		  case 'VENDORORDERS' : {
			 echo "<li class='separator'></li>";
			 echo "<li".($_REQUEST['show'] == 'opened' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('opened')\">Aperti</a></li>";
			} break;

		  case 'PURCHASEINVOICES' : {
			 echo "<li class='separator'></li>";
			 echo "<li".($_REQUEST['show'] == 'tobepaid' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('tobepaid')\">Da pagare</a></li>";
			 echo "<li".($_REQUEST['show'] == 'paid' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('paid')\">Pagate</a></li>";
			} break;

		  case 'AGENTINVOICES' : {
			 echo "<li class='separator'></li>";
			 echo "<li".($_REQUEST['show'] == 'tobeemit' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('tobeemit')\">Da emettere</a></li>";
			} break;

		  case 'INTERVREPORTS' : {
			 echo "<li class='separator'></li>";
			 echo "<li".($_REQUEST['show'] == 'ungroupped' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('ungroupped')\">Da raggruppare</a></li>";
			 echo "<li".($_REQUEST['show'] == 'groupped' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('groupped')\">Raggruppati</a></li>";
			} break;

		  case 'CREDITSNOTE' : {
			 echo "<li class='separator'></li>";
			 echo "<li".($_REQUEST['show'] == 'tobepaid' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('tobepaid')\">Da pagare</a></li>";
			} break;

		  case 'DEBITSNOTE' : {
			 echo "<li class='separator'></li>";
			 echo "<li".($_REQUEST['show'] == 'tobepaid' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('tobepaid')\">Da pagare</a></li>";
			} break;

		  case 'RECEIPTS' : {
			 echo "<li class='separator'></li>";
			 echo "<li".($_REQUEST['show'] == 'tobepaid' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('tobepaid')\">Da pagare</a></li>";
			} break;

		  case 'DDTIN' : {
			 echo "<li class='separator'></li>";
			 echo "<li".($_REQUEST['show'] == 'ungroupped' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('ungroupped')\">Da raggruppare</a></li>";
			 echo "<li".($_REQUEST['show'] == 'groupped' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('groupped')\">Raggruppati</a></li>";
			 echo "<li".($_REQUEST['show'] == 'internal' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('internal')\">Bolle movim. int.</a></li>";
			} break;

		  case 'MEMBERINVOICES' : {
			 echo "<li class='separator'></li>";
			 echo "<li".($_REQUEST['show'] == 'tobeemit' ? " class='selected'>" : ">")."<a href='#' onclick=\"setShow('tobeemit')\">Da emettere</a></li>";
			} break;

		 }
		 /* SUBCATEGORIES */
		 $ret = GShell("dynarc cat-list -ap '".$_AP."' -pt '".$_CAT_TAG."' --order-by 'name ASC'");
		 if(!$ret['error'])
		 {
		  $list = $ret['outarr'];
		  if(count($list)) echo "<li class='separator'></li>";
		  for($c=0; $c < count($list); $c++)
		  {
		   echo "<li".((($_REQUEST['show'] == 'category') && ($_CAT_INFO['id'] == $list[$c]['id'])) ? " class='selected'>" : ">")
			."<a href='#' onclick='showCat(".$list[$c]['id'].")'>".$list[$c]['name']."</a></li>";
		  }
		 }

		 /* EXTRA TABS */
	 	 if($_COMMERCIALDOCS_CONFIG['TABS'] && $_COMMERCIALDOCS_CONFIG['TABS'][$_CAT_TAG])
	  	  $_EXTRA_TABS = $_COMMERCIALDOCS_CONFIG['TABS'][$_CAT_TAG];
		 if(count($_EXTRA_TABS))
		  echo "<li class='separator'></li>";
		 for($c=0; $c < count($_EXTRA_TABS); $c++)
		   echo "<li".(($_REQUEST['show'] == $_EXTRA_TABS[$c]['tag']) ? " class='selected'>" : ">")
			."<a href='#' onclick='setShow(\"".$_EXTRA_TABS[$c]['tag']."\")'>".$_EXTRA_TABS[$c]['title']."</a></li>";

		 /* STATUS EXTRA */
		 $_STATUSEXTRA = array();
		 if($_COMMERCIALDOCS_CONFIG['STATUSEXTRA'] && $_COMMERCIALDOCS_CONFIG['STATUSEXTRA'][$_CAT_TAG])
		  $_STATUSEXTRA = $_COMMERCIALDOCS_CONFIG['STATUSEXTRA'][$_CAT_TAG];
		 if(count($_STATUSEXTRA))
		  echo "<li class='separator'></li>";
		 reset($_STATUSEXTRA);
		 while(list($k,$v) = each($_STATUSEXTRA))
		 {
		  if(!$v['showtab'])
		   continue;
		  echo "<li".(($_REQUEST['statusextra'] == $k) ? " class='selected'>" : ">")
			."<a href='#' onclick='showStatusExtra(\"".$k."\",true)'>".($v['tabtitle'] ? $v['tabtitle'] : $v['title'])."</a></li>";
		 }
		 ?>
	  	 <li class='separator'></li>
	  	 <li <?php if($_REQUEST['show'] == 'trash') echo "class='selected'"; ?>><a href='#' onclick="setShow('trash')">Cestino</a></li>
	  	</ul>
	  </div>

	 <div class="page-contents-body">
	  <!-- START OF PAGE ------------------------------------------------------------------------->
	  <div class="titlebar blue-bar"><span class="titlebar blue-bar"><?php echo $_TITLE_BAR; ?></span></div>

<div id='documentlist-container' style="height:100%;overflow:auto">
<table width='100%' cellspacing='0' cellpadding='0' border='0' class='sortable-table' id='documentlist' noprinthidden='true'>
<tr><th width='16'><input type='checkbox'/></th>
	<?php
	for($c=0; $c < count($_COLUMNS); $c++)
	{
	 $col = $_COLUMNS[$c];
	 $style = $col['fieldstyle'];
	 $visibled = $col['visibled'] ? true : false;
     if(($col['format'] == "currency") && !$style)
      $style = "text-align:right;";
     if(!$visibled) 
	   $style = "display:none;".$style;
	 echo "<th".($style ? " style='".$style."'" : "");
	 if($col['width'])			echo " width='".$col['width']."'";
	 if($col['field'])			echo " field='".$col['field']."'";
	 if($col['format'])			echo " format='".$col['format']."'";
	 if($col['sortable'])		echo " sortable='true'";
	 echo ">".$col['title']."</th>";
	}
	?>
</tr>
<?php
$row = 0;
$mod = new GMOD();
$lastdate = "";
$today = date('Y-m-d');
$todayT = strtotime($today);
$futureISOdate = null;

// verifica che non ci siano documenti con data futura (post data ordierna)
$db = new AlpaDatabase();
$db->RunQuery("SELECT COUNT(*) FROM dynarc_commercialdocs_items WHERE cat_id='".$_CAT_INFO['id']."' AND ctime>'".$today."' AND trash='0'");
$db->Read();
if($db->record[0] > 0)
{
 $futureCount = $db->record[0];
 $db->RunQuery("SELECT ctime FROM dynarc_commercialdocs_items WHERE cat_id='".$_CAT_INFO['id']."' AND ctime>'".$today."' AND trash='0' ORDER BY ctime DESC LIMIT 1");
 $db->Read();
 $futureISOdate = substr($db->record['ctime'], 0, 10);
 echo "<tr class='label'><td colspan='".(count($_COLUMNS)+1)."' style='background:#ffffcf'><span style='color:#f31903'>Ci sono ".$futureCount." documenti con data post-odierna (data maggiore di oggi ".date('d/m/Y').")</span>";
 if($dateTo && (strtotime($dateTo) < strtotime($futureISOdate)))
  echo " &nbsp;&nbsp; <a href='#' onclick='showFutureDocs(\"".$futureISOdate."\")'>visualizza</a></td></tr>";
 else
  echo "</td></tr><tr class='label'><td colspan='".(count($_COLUMNS)+1)."'><i>Documenti con data futura</i></td></tr>";
}
$db->Close();

$_TOTALS['profit'] = 0;
for($z=0; $z < count($_DOCUMENT_LIST); $z++)
{
 $item = $_DOCUMENT_LIST[$z];
 $hint = $item['name']." - ".$item['subject_name'];
 if(date('m/Y',$item['ctime']) != $lastdate)
 {
  if(!$futureISOdate || ($item['ctime'] <= $todayT))
  {
   $lastdate = date('m/Y',$item['ctime']);
   $_LDTIT = $_DICTIONARY['MONTH-'.date('n',$item['ctime'])]." ".date('Y',$item['ctime']);
   echo "<tr class='label'><td colspan='".(count($_COLUMNS)+1)."'><i>".$_LDTIT."</i></td></tr>";
   $row = 0;
  }
 }

 echo "<tr class='row".$row."' id='".$item['id']."' subjectid='".$item['subject_id']."' title=\"".$hint."\"><td><input type='checkbox'/></td>";
 for($i=0; $i < count($_COLUMNS); $i++)
 {
  $col = $_COLUMNS[$i];
  $visibled = $col['visibled'] ? true : false;
  $style = $col['style'];
  if(($col['format'] == "currency") && !$style)
   $style = "text-align:right;";
  if(!$visibled) 
	$style = "display:none;".$style;
  echo "<td".($style ? " style='".$style."'" : "").">";
  switch($col['field'])
  {
   case 'name' : echo "<a class='link blue' href='".$_ABSOLUTE_URL.$template->config['basepath']."docinfo.php?id=".$item['id']."' target='GCD-".$item['id']."'>".$item['name']."</a>"; break;

   case 'labels' : {
	 $labels = $item['user_labels'];
	 for($j=0; $j < count($labels); $j++)
	 {
	  $lab = $_LABEL_BY_ID[$labels[$j]];
	  if(!$lab)
	   echo "&nbsp;";
	  else
	   echo "<span class='label' style='background-color:".$lab['bgcolor'].";color:".$lab['color'].";'>".$lab['name']."</span>";
	 }
	} break;

   case 'ctime' : echo "<a class='link blue' href='".$_ABSOLUTE_URL.$template->config['basepath']."docinfo.php?id=".$item['id']."' target='GCD-".$item['id']."'>".date('d/m/Y',$item['ctime'])."</a>"; break;

   case 'causal' : {
	 $docCausal = "";
	 if($item['tag'])
	 {
	  if(is_numeric($item['tag']))
	  {
	   for($c=0; $c < count($_CAUSALS); $c++)
	   {
		if($item['tag'] == $_CAUSALS[$c]['id'])
		{
		 $docCausal = $_CAUSALS[$c]['name'];
		 break;
		}
	   }
	  }
	  else if($extraDocTypes[$item['tag']])
	   $docCausal = $extraDocTypes[$item['tag']];
	 }
	 else
	  $docCausal = $_CAUSALS[0]['name'];
	 echo $docCausal ? $docCausal : "&nbsp;";
	} break;

   case 'subject_code' : echo $item['subject_code'] ? $item['subject_code'] : "&nbsp;"; break;
   case 'subject_name' : echo $item['subject_name'] ? $item['subject_name'] : "&nbsp;"; break;
   case 'subject_vatnumber' : echo $item['subject_vatnumber'] ? $item['subject_vatnumber'] : "&nbsp;"; break;
   case 'subject_taxcode' : echo $item['subject_taxcode'] ? $item['subject_taxcode'] : "&nbsp;"; break;
   case 'intdocref' : {
	 if($item['docref_ap'] && $item['docref_id'])
	 {
	  switch($item['docref_ap'])
	  {
	   case 'commercialdocs' : echo "<span class='status-xsmall'><a href='".$_ABSOLUTE_URL.$template->config['basepath']."docinfo.php?id="
		.$item['docref_id']."' target='GCD-".$item['docref_id']."'>".$item['docref_name']."</a></span>"; break;

	   case 'commesse' : echo "<span class='status-xsmall'><a href='".$_ABSOLUTE_URL."Commesse/edit.php?id="
		.$item['docref_id']."' target='COMMESSA-".$item['docref_id']."'>".$item['docref_name']."</a></span>"; break;

	   case 'tickets' : echo "<span class='status-xsmall'><a href='".$_ABSOLUTE_URL."Tickets/ticketinfo.php?id="
		.$item['docref_id']."' target='TKT-".$item['docref_id']."'>".$item['docref_name']."</a></span>"; break;

	   default : echo "<span class='status-xsmall'>".$item['docref_name']."</span>"; break;
	  }
	 }
	 else
	  echo "<span class='status-small'>".$item['aliasname']."</span>";
	} break;

   case 'extdocref' : echo $item['ext_docref'] ? $item['ext_docref'] : "&nbsp;"; break;

   case 'options' : {
	 if($_REQUEST['show'] == "trash")
	  echo "<span class='smallroundbtn' onclick='restoreDocument(".$item['id'].")'>ripristina</span>";
	 else
	  echo "<span class='smallroundbtn' onclick='showDocumentOptions(".$item['id'].",this)'>opzioni</span>"; 
	} break;

   case 'status' : echo getDocumentStatus($item); break;

   case 'statusextra' : echo $_STATUSEXTRA[$item['status_extra']] ? "<span class='status-small' style='color:"
	.$_STATUSEXTRA[$item['status_extra']]['color']."'>".$_STATUSEXTRA[$item['status_extra']]['title']."</span>" : "&nbsp;"; break;

   case 'division' : echo $item['division'] ? $item['division'] : "&nbsp;"; break;
   case 'printdate' : echo ($item['print_date'] && ($item['print_date'] != "0000-00-00") && ($item['print_date'] != "1970-01-01")) ? date('d/m/Y',strtotime($item['print_date'])) : '&nbsp;'; break;

   case 'senddate' : echo ($item['send_date'] && ($item['send_date'] != "0000-00-00") && ($item['send_date'] != "1970-01-01")) ? date('d/m/Y',strtotime($item['send_date'])) : '&nbsp;'; break;

   case 'validitydate' : echo ($item['validity_date'] && ($item['validity_date'] != "0000-00-00") && ($item['validity_date'] != "1970-01-01")) ? date('d/m/Y',strtotime($item['validity_date'])) : '&nbsp;'; break;

   case 'charterfrom' : echo ($item['charter_datefrom'] && ($item['charter_datefrom'] != "0000-00-00") && ($item['charter_datefrom'] != "1970-01-01")) ? date('d/m/Y',strtotime($item['charter_datefrom'])) : '&nbsp;'; break;

   case 'charterto' : echo ($item['charter_dateto'] && ($item['charter_dateto'] != "0000-00-00") && ($item['charter_dateto'] != "1970-01-01")) ? date('d/m/Y',strtotime($item['charter_dateto'])) : '&nbsp;'; break;

   case 'transdate' : echo $item['trans_datetime'] ? date('d/m/Y',$item['trans_datetime']) : '&nbsp;'; break;

   case 'cartage' : echo number_format($item['cartage'],$_DECIMALS,',','.'); break;
   case 'packingcharges' : echo number_format($item['packing_charges'],$_DECIMALS,',','.'); break;
   case 'collectioncharges' : echo number_format($item['collection_charges'],$_DECIMALS,',','.'); break;
   case 'rivalsainps' : echo number_format($item['tot_rinps'],$_DECIMALS,',','.'); break;
   case 'cassaprev' : echo number_format($item['tot_ccp'],$_DECIMALS,',','.'); break;
   case 'enasarco' : echo number_format($item['tot_enasarco'],$_DECIMALS,',','.'); break;
   case 'ritacconto' : echo number_format($item['tot_rit_acc'],$_DECIMALS,',','.'); break;
   case 'rebate' : echo number_format($item['rebate'],$_DECIMALS,',','.'); break;
   case 'stamp' : echo number_format($item['stamp'],$_DECIMALS,',','.'); break;
   case 'expenses' : echo number_format($item['tot_expenses'],$_DECIMALS,',','.'); break;

   case 'amount' : echo number_format($item['amount'],$_DECIMALS,',','.'); break;
   case 'vat' : echo number_format($item['vat'],$_DECIMALS,',','.'); break;
   case 'total' : echo number_format($item['total'],$_DECIMALS,',','.'); break;
   case 'netpay' : echo number_format($item['tot_netpay'],$_DECIMALS,',','.'); break;
   case 'totpaid' : echo number_format($item['tot_paid'],$_DECIMALS,',','.'); break;
   case 'resttopay' : echo number_format($item['rest_to_pay'],$_DECIMALS,',','.'); break;
   case 'agent' : echo $item['agent_name'] ? $item['agent_name'] : "&nbsp;"; break;
   case 'agentcommiss' : echo number_format($item['agent_commiss'],$_DECIMALS,',','.'); break;
   case 'owner' : echo _getUserName($item['modinfo']['uid']); break;
   case 'group' : echo _getGroupName($item['modinfo']['gid']); break;
   case 'perms' : {
	 $mod->set($item['modinfo']['mod'], $item['modinfo']['uid'], $item['modinfo']['gid']);
	 echo $mod->toString();
	} break;
   case 'deliverydate' : echo ($item['delivery_date'] && ($item['delivery_date'] != "0000-00-00") && ($item['delivery_date'] != "1970-01-01")) ? date('d/m/Y',strtotime($item['delivery_date'])) : '&nbsp;'; break;
   case 'profit' : echo number_format($item['profit'], 2, ',', '.'); break;
  }
  echo "</td>";
 }
 echo "</tr>";
 $row = $row ? 0 : 1;

 $_TOTALS['profit']+= $item['profit'];
}
?>
</table>
<div id='nuvcontainer' style="position:absolute;left:0px;top:0px;width:200px;display:block;visibility:hidden;"></div>
</div>
<!-- TOTALI -->
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="footer-totals" style="margin-top:20px;<?php if($_REQUEST['show'] == 'trash') echo 'display:none'; ?>">
<tr><th rowspan='2' class='blue'>&nbsp;</th>
	<?php
	for($c=0; $c < count($_FOOTER_COLUMNS); $c++)
	{
	 $col = $_FOOTER_COLUMNS[$c];
	 $style = "";
	 if(!$col['visibled'])
	  $style = "display:none";
	 $class = "";
	 switch($col['field'])
	 {
	  case 'total' : case 'netpay' : $class='green'; break;
	  default : $class = "blue"; break;
	 }
	 echo "<th class='".$class."' width='".$col['width']."' id='footer-".$col['field']."-title'".($style ? " style='".$style."'" : "").">";
	 echo $col['title'];
	 if($col['field'] == "profit")
	 {
	  if($_SERP->Results['count'] > count($_DOCUMENT_LIST))
	   echo " <img src='icons/info.png' style='float:right;margin:2px;width:10px;height:10px' title=\"Totale guadagno ricavato solo dai risultati di questa videata\"/>";
	 }
	 echo "</th>";
	}
	?></tr>
<tr><?php
	for($c=0; $c < count($_FOOTER_COLUMNS); $c++)
	{
	 $col = $_FOOTER_COLUMNS[$c];
	 $style = "";
	 if(!$col['visibled'])
	  $style = "display:none";
	 echo "<td id='footer-".$col['field']."-value'".($style ? " style='".$style."' " : " ");
	 switch($col['field'])
	 {
	  case 'amount' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['amount'] ? $_TOTALS['amount'] : 0,$_DECIMALS,',','.')."</td>"; break;
	  case 'vat' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['vat'] ? $_TOTALS['vat'] : 0 ,$_DECIMALS,',','.')."</td>"; break;
	  case 'total' : echo "class='green' align='right'><em>&euro;</em>".number_format($_TOTALS['total'] ? $_TOTALS['total'] : 0 ,$_DECIMALS,',','.')."</td>"; break;
	  case 'profit' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['profit'] ? $_TOTALS['profit'] : 0,$_DECIMALS,',','.')."</td>"; break;
	  case 'ritacconto' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['tot_rit_acc'] ? $_TOTALS['tot_rit_acc'] : 0 ,$_DECIMALS,',','.')."</td>"; break;
	  case 'cassaprev' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['tot_ccp'] ? $_TOTALS['tot_ccp'] : 0 ,$_DECIMALS,',','.')."</td>"; break;
	  case 'rivalsainps' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['tot_rinps'] ? $_TOTALS['tot_rinps'] : 0 ,$_DECIMALS,',','.')."</td>"; break;
	  case 'enasarco' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['tot_enasarco'] ? $_TOTALS['tot_enasarco'] : 0 ,$_DECIMALS,',','.')."</td>"; break;
	  case 'netpay' : echo "class='green' align='right'><em>&euro;</em>".number_format($_TOTALS['tot_netpay'] ? $_TOTALS['tot_netpay'] : 0 ,$_DECIMALS,',','.')."</td>"; break;
	  case 'rebate' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['rebate'] ? $_TOTALS['rebate'] : 0 ,$_DECIMALS,',','.')."</td>"; break;
	  case 'stamp' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['stamp'] ? $_TOTALS['stamp'] : 0 ,$_DECIMALS,',','.')."</td>"; break;
	  case 'expenses' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['tot_expenses'] ? $_TOTALS['tot_expenses'] : 0 ,$_DECIMALS,',','.')."</td>"; break;
	  case 'discount' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['tot_discount'] ? $_TOTALS['tot_discount'] : 0 ,$_DECIMALS,',','.')."</td>"; break;
	  case 'cartage' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['cartage'] ? $_TOTALS['cartage'] : 0 ,$_DECIMALS,',','.')."</td>"; break;
	  case 'packingcharges' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['packing_charges'] ? $_TOTALS['packing_charges'] : 0 ,$_DECIMALS,',','.')."</td>"; break;
	  case 'collectioncharges' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['collection_charges'] ? $_TOTALS['collection_charges'] : 0 ,$_DECIMALS,',','.')."</td>"; break;
	  case 'agentcommiss' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['agent_commiss'] ? $_TOTALS['agent_commiss'] : 0 ,$_DECIMALS,',','.')."</td>"; break;
	  case 'totpaid' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['tot_paid'] ? $_TOTALS['tot_paid'] : 0 ,$_DECIMALS,',','.')."</td>"; break;
	  case 'resttopay' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['rest_to_pay'] ? $_TOTALS['rest_to_pay'] : 0 ,$_DECIMALS,',','.')."</td>"; break;
	 }
	}
	?></tr>
</table>


	  <!-- END OF PAGE --------------------------------------------------------------------------->
	 </div>
	</td>
 </tr>
</table>

<?php
/*-------------------------------------------------------------------------------------------------------------------*/
$template->Footer();
/*-------------------------------------------------------------------------------------------------------------------*/
function getDocumentStatus($itm)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $template;

 if($itm['partial_proc'] > 0)
  $output.= "<span class='status-orange'>evaso parziale</span>";
 else
 {
  switch($itm['status'])
  {
   case 1 : $output = "<img src='".$_ABSOLUTE_URL.$template->config['basepath']."img/status-print.png' class='status-icon'/><span class='status-small'>stampato il<br/>".date('d/m/Y',strtotime($itm['print_date']))."</span>"; break;

   case 2 : $output = "<img src='".$_ABSOLUTE_URL.$template->config['basepath']."img/status-send.gif' class='status-icon'/><span class='status-small'>inviato il<br/>"
	.date('d/m/Y',strtotime($itm['send_date']))."</span>"; break;

   case 3 : $output = "<img src='".$_ABSOLUTE_URL.$template->config['basepath']."img/status-pending.gif' class='status-icon'/><span class='status-normal'><b>in attesa</b></span>"; break;

   case 4 : $output = "<img src='".$_ABSOLUTE_URL.$template->config['basepath']."img/status-working.gif' class='status-icon'/><span class='status-normal'><b style='color:#013397'>in lavorazione</b></span>"; break;

   case 5 : $output = "<img src='".$_ABSOLUTE_URL.$template->config['basepath']."img/status-suspended.gif' class='status-icon'/><span class='status-normal'><b style='color:#f44800'>sospeso</b></span>"; break;

   case 6 : $output = "<img src='".$_ABSOLUTE_URL.$template->config['basepath']."img/status-failed.gif' class='status-icon'/><span class='status-normal'><b style='color:#d40000'>fallito</b></span>"; break;

   case 7 : $output = "<img src='".$_ABSOLUTE_URL.$template->config['basepath']."img/status-completed.png' class='status-icon'/><span class='status-normal'><b style='color:#015a01'>completato</b></span>"; break;

   case 8 : {
	 $output = "<img src='".$_ABSOLUTE_URL.$template->config['basepath']."img/status-converted.png' class='status-icon'/>";
	 if($itm['conv_doc_id'] && $itm['conv_doc_name'])
	  $output.= "<span class='status-xsmall'>convertito in<br/><a href='#' onclick='OpenDocument(".$itm['conv_doc_id'].")'>"
		.$itm['conv_doc_name']."</a></span>";
	 else
	  $output.= "<span class='status-xsmall'>convertito in<br/>documento sconosciuto</span>";
	} break;

   case 9 : {
	 $output = "<img src='".$_ABSOLUTE_URL.$template->config['basepath']."img/status-groupped.png' class='status-icon'/>";
	 if($itm['group_doc_id'] && $itm['group_doc_name'])
	  $output.= "<span class='status-xsmall'>raggruppato in<br/><a href='#' onclick='OpenDocument(".$itm['group_doc_id'].")'>"
		.$itm['group_doc_name']."</a></span>";
	 else
	  $output.= "<span class='status-xsmall'>raggruppato in<br/>documento sconosciuto</span>";
	} break;

   case 10 : $output = "<span class='status-green'>pagato</span>"; break;

   default : {
	 if($itm['entirely_proc'] > 0)
	  $output.= "<span class='status-green'>evaso</span>";
	 else
	  $output = "<span class='status-open'><i>aperto</i></span>";
	} break;
  } // eof - switch
 } // eof - if partial_proc

 return $output;
}
/*-------------------------------------------------------------------------------------------------------------------*/
?>
<script>

var AP = "<?php echo $_AP; ?>";
var ON_PRINTING = false;
var ON_EXPORT = false;
var CAT_ID = "<?php echo $_CAT_INFO['id']; ?>";
var ROOT_CT = "<?php echo $_ROOT_CT; ?>";
var GROUPNAME = "<?php echo $_GROUPNAME; ?>";
var SUBJTYPE_NAME = "<?php echo $_SUBJTYPE_NAME; ?>";
var DTFILT = "<?php echo $_REQUEST['dtfilt']; ?>";
var TRANSPONDER_SERVICE_TAGS = "<?php echo $_TRANSPONDER_SERVICE_TAGS; ?>";

function ShowSummary()
{
 document.location.href = ABSOLUTE_URL+"<?php echo $template->config['basepath']; ?>summary.php?ct="+ROOT_CT;
}

function ShowFatturePA()
{
 document.location.href = ABSOLUTE_URL+"<?php echo $template->config['basepath']; ?>fatturepa.php?ct="+ROOT_CT;
}

function ShowAnonymous()
{
 Template.SERP.setVar("subjectid","anonymous");
 Template.SERP.reload(0);
}

Template.OnExit = function(){
	document.location.href = ABSOLUTE_URL;
	return false;
}

function doSearchByDate()
{
 document.getElementById("dateto").OnDateChange(document.getElementById("dateto").isodate);
}

function showFutureDocs(isodate)
{
 Template.SERP.setVar("dtfilt","ctime");
 Template.SERP.setVar("from",document.getElementById("datefrom").isodate);
 Template.SERP.setVar("to",isodate);
 Template.SERP.reload();
}

Template.OnInit = function(){
 /* AUTORESIZE */
 var sH = this.getScreenHeight();
 var tb = document.getElementById("template-outer-mask");
 if(tb.offsetHeight < (sH-115))
  tb.style.height = (sH-115)+"px";
 document.getElementById('documentlist-container').style.height = (sH-240)+"px";

 var div = document.getElementById('doctypelist');
 div.style.width = div.parentNode.offsetWidth + "px";
 div.style.height = (div.parentNode.offsetHeight-94) + "px";
 div.style.display = "block";

 var div = document.getElementById('precdoctypelist');
 if(div)
 {
  div.style.width = div.parentNode.offsetWidth + "px";
  div.style.height = (div.parentNode.offsetHeight-94) + "px";
 }

 /* EOF - AUTORESIZE */

	this.initEd(document.getElementById('rpp'), "dropdown").onchange = function(){
		 Template.SERP.RPP = this.getValue();
		 Template.SERP.reload(0);
		}


	this.initEd(document.getElementById("search"), "contactextended").OnSearch = function(){
			 Template.SERP.setVar("refap","");
			 Template.SERP.setVar("refid",0);
			 if(this.value && this.data)
			 {
			  Template.SERP.setVar("search",this.value);
			  Template.SERP.setVar("subjectid",this.data['id']);
			 }
			 else
			 {
			  Template.SERP.setVar("search",this.value);
			  Template.SERP.setVar("subjectid",0);
			 }
			 Template.SERP.reload(0);
			};
	this.initBtn(document.getElementById("searchbtn")).onclick = function(){document.getElementById("search").OnSearch();}

	this.initBtn(document.getElementById('mainmenubutton'), 'popupmenu');
	this.initBtn(document.getElementById('viewmenubutton'), 'popupmenu');

	/*this.initEd(document.getElementById('show'), "dropdown").onchange = function(){
		 if(this.getValue() == "")
		  Template.SERP.unsetVar("status");
		 else
		  Template.SERP.setVar("status",this.getValue());
		 Template.SERP.reload(0);
		};*/


	this.initEd(document.getElementById("datefrom"), "date");
	this.initEd(document.getElementById("dateto"), "date").OnDateChange = function(date){
		 switch(DTFILT)
		 {
		  case 'lastsemester' : case 'lastquarter' : case 'lastmonth' : case 'thismonth' : case 'thisyear' : case 'lastyear' : Template.SERP.setVar("dtfilt","ctime");
		 }
		 Template.SERP.setVar("from",document.getElementById("datefrom").isodate);
		 Template.SERP.setVar("to",date);
		 Template.SERP.reload();
		};

	this.initEd(document.getElementById('dtfilt'), 'dropdown').onchange = function(){
		 Template.SERP.setVar('dtfilt',this.getValue());
		 switch(this.getValue())
		 {
		  case 'lastsemester' : case 'lastquarter' : case 'lastmonth' : case 'thismonth' : case 'thisyear' : case 'lastyear' : Template.SERP.reload(0); break;
		 }
		};

	this.SERP = new SERP("<?php echo $_SERP->OrderBy; ?>", "<?php echo $_SERP->OrderMethod; ?>", "<?php echo $_SERP->RPP; ?>", "<?php echo $_SERP->PG; ?>");
	var tb = this.initSortableTable(document.getElementById("documentlist"), this.SERP.OrderBy, this.SERP.OrderMethod);
	tb.OnSort = function(field, method){
		 Template.SERP.OrderBy = field;
	     Template.SERP.OrderMethod = method;
		 Template.SERP.reload(0);
		}
	tb.OnSelect = function(list){
	 if(!list.length)
	  document.getElementById("tagbutton").style.visibility = "hidden";
	 else
	 {
	  document.getElementById("tagbutton").style.visibility = "visible";
	  var id = 0;
	  if(list.length == 1)
	   id = list[0].id;
	  document.getElementById("tagbutton").UpdateLabels(id);
	 }
	}

	this.initBtn(document.getElementById("tagbutton"), "labels").OnSubmit = function(ret){
		 var tb = document.getElementById("documentlist");
		 var sel = tb.getSelectedRows();
		 if(!sel.length)
		  return alert("Nessun document selezionato");
		 
		 var cmd = "";
		 for(var c=0; c < sel.length; c++)
		  cmd+= " && dynarc edit-item -ap commercialdocs -id '"+sel[c].id+"' -extset `labels.userlabels='"+ret+"'`";

		 var sh = new GShell();
		 sh.OnError = function(err){alert(err);}
		 sh.OnOutput = function(){Template.SERP.reload();}
		 sh.sendCommand(cmd.substr(4));
		};

	this.initEd(document.getElementById('labelfilter'), "dropdown").onchange = function(){
		 var val = this.getValue();
		 if(val == "0")
		 {
		  Template.SERP.setVar("untagged","1");
		  Template.SERP.unsetVar("label");
		 }
		 else if(val)
		  Template.SERP.setVar("label",val);
		 else
		 {
		  Template.SERP.unsetVar("label");
		  Template.SERP.unsetVar("untagged");
		 }
		 Template.SERP.reload(0);
		};


	
  document.addEventListener ? document.addEventListener("mouseup",hideDocumentOptions,false) : document.attachEvent("onmouseup",hideDocumentOptions);
}

function NewDocument()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 /* TODO: cambiare se necessario il path */
	 window.open(ABSOLUTE_URL+"<?php echo $template->config['basepath']; ?>docinfo.php?id="+a['id'],"_blank");
	}
 sh.sendCommand("dynarc new-item -ap `commercialdocs` -cat '"+CAT_ID+"' -group '"+GROUPNAME+"'");
}

function showDocumentOptions(id, obj)
{
 var pos = _getObjectPosition(obj);
 var nuv = document.getElementById('nuvcontainer');
 var container = document.getElementById('documentlist-container');

 var lay = new Layer("commercialdocs/docopt", "id="+id, nuv, true, function(){
	 nuv.style.left = pos['x'] + obj.offsetWidth;
	 nuv.style.top = (pos['y'] + Math.floor(obj.offsetHeight/2)) - Math.floor(nuv.offsetHeight/2) - (container.scrollTop*2);
	 nuv.style.visibility = "visible";
	});
}

function hideDocumentOptions()
{
 if(document.getElementById('nuvcontainer'))
  document.getElementById('nuvcontainer').style.visibility = "hidden";
}

function restoreDocument(docId)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(o.indexOf("Warning: only owner can restore item") > -1)
	  alert("Impossibile ripristinare questo documento perchè non ne sei il proprietario. Prova ad eliminarlo da utente root.");
	 else
	  alert("Il documento è stato ripristinato!");
	 Template.reload();
	}
 sh.sendCommand("dynarc trash restore -ap '"+AP+"' -id `"+docId+"`");
}

function restoreSelected()
{
 var TB = document.getElementById("documentlist");
 var sel = TB.getSelectedRows();

 if(!sel.length)
  return alert("Devi selezionare almeno un documento");

 var sh = new GShell();
 sh.OnFinish = function(){
	 alert("I documenti sono stati ripristinati!");
	 document.location.reload();
	}
 for(var c=0; c < sel.length; c++)
  sh.sendCommand("dynarc trash restore -ap '"+AP+"' -id `"+sel[c].id+"`");
}

function deleteSelected(forever)
{
 var tb = document.getElementById("documentlist");
 var sel = tb.getSelectedRows();
 if(!sel.length)
  return alert("Nessun documento selezionato");
 if(!confirm("Sei sicuro di voler eliminare i documenti selezionati?"))
  return;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){Template.SERP.reload(0);}
 var q = "";
 for(var c=0; c < sel.length; c++)
  q+= " -id '"+sel[c].id+"'";
 sh.sendCommand("dynarc delete-item -ap '"+AP+"'"+(forever ? " -r" : "")+q);
}

function setShow(show)
{
 Template.SERP.unsetVar("statusextra");
 Template.SERP.setVar("show",show);
 Template.SERP.unsetVar("catid");
 Template.SERP.reload(0);
}

function showCat(catId)
{
 Template.SERP.setVar("show","category");
 Template.SERP.setVar("catid",catId);
 Template.SERP.reload(0);
}

function showStatusExtra(status,clean)
{
 Template.SERP.unsetVar("show");
 if(clean)
  Template.SERP.unsetVar("subjectid");
 Template.SERP.setVar("statusextra",status);
 Template.SERP.reload(0);
}


function showColumn(field,cb)
{
 var tb = document.getElementById("documentlist");
 if(cb.checked == true)
  tb.showColumn(field);
 else
  tb.hideColumn(field);

 switch(field)
 {
  case 'amount' : case 'vat' : case 'total' : case 'netpay' : case 'rebate' : case 'expenses' : case 'cartage' : case 'packingcharges' : case 'collectioncharges' : case 'agentcommiss' : case 'resttopay' : case 'enasarco' : case 'stamp' : case 'ritacconto' : case 'cassaprev' : case 'rivalsainps' : case 'totpaid' : case 'profit' : showFooterColumn(field,cb.checked); break;
  /*case 'ritacconto' : showFooterColumn("ritacc",cb.checked); break;
  case 'cassaprev' : showFooterColumn("ccp",cb.checked); break;
  case 'rivalsainps' : showFooterColumn("rinps",cb.checked); break;
  case 'totpaid' : showFooterColumn("paid",cb.checked); break;*/
 }
}

function showFooterColumn(field, bool)
{
 document.getElementById("footer-"+field+"-title").style.display = bool ? "" : "none";
 document.getElementById("footer-"+field+"-value").style.display = bool ? "" : "none";
}

function emptyTrash()
{
 if(!confirm("Una volta svuotato il cestino i documenti saranno rimossi permanentemente pertanto non sarà più possibile recuperarli. Sei sicuro di voler procedere?"))
  return;
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(){
	 alert("Il cestino è stato svuotato!");
	 Template.reload(0);
	}
 sh.sendCommand("dynarc trash empty -ap '"+AP+"' -cat '"+CAT_ID+"'");
}

function ExportToExcel(btn)
{
 document.getElementById('mainmenubutton').popupmenu.hide();
 var cmd = "<?php echo $_CMD; ?> --order-by \"<?php echo $_SERP->OrderBy.' '.$_SERP->OrderMethod; ?>\"";
 cmd = cmd.replace("<![CDATA[",'"');
 cmd = cmd.replace("]]>",'"');

 var keys = "name,ctime,subject_code,subject_name,subject_vatnumber,subject_taxcode,docref_name,ext_docref,division,print_date,send_date,validity_date";
 keys+= ",charter_datefrom,charter_dateto,trans_datetime,delivery_date,cartage,packing_charges,collection_charges,tot_rinps,tot_ccp,tot_enasarco,tot_rit_acc";
 keys+= ",rebate,stamp,tot_expenses,amount,vat,total,tot_netpay,tot_paid,rest_to_pay,agent_name,agent_commiss";

 var titles = "DOCUMENTO|DATA|COD. CLI|"+SUBJTYPE_NAME.toUpperCase()+"|P.IVA|COD.FISC.|DOC. RIF. INT.|DOC. RIF. EST.|DIVISIONE MAT.|DATA STAMPA|DATA INVIO|DATA VALIDITA";
 titles+= "|NOLEGGIO DAL|NOLEGGIO AL|DATA TRASP.|DATA CONS.|SP. TRASP|SP. IMBALLO|SP. INCASSO|RIV. INPS|CASSA PREV.|ENASARCO|RIT.ACC.";
 titles+= "|ABBUONI|BOLLI|SPESE|IMPONIBILE|IVA|TOTALE|NETTO A PAGARE|TOT. PAGATO|RESTO DA PAGARE|AGENTE|COMMISS. AGENTE";

 var formats = "string|date|string|string|string|string|string|string|string|date|date|date";
 formats+= "|date|date|date|date|currency|currency|currency|currency|currency|currency|currency";
 formats+= "|currency|currency|currency|currency|currency|currency|currency|currency|currency|string|currency"; 

 cmd+= " || tableize *.items";
 cmd+= " -k '"+keys+"'";
 cmd+= " -n '"+titles+"'";
 cmd+= " -f '"+formats+"'";
 cmd+= " --include-totals";


 var sh = new GShell();
 sh.showProcessMessage("Esportazione in Excel", "Attendere prego, è in corso l'esportazione dei documenti su file Excel.");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(o,a){
	 this.hideProcessMessage();
	 if(!a) return;
	 var fileName = a['filename'];
	 document.location.href = ABSOLUTE_URL+"getfile.php?file="+fileName;
	}

 var finalCommand = "gframe -f excel/export -params `file=lista_documenti` -command `"+cmd+"`";

 sh.sendCommand(finalCommand);
}

function Print()
{
 /* TODO: funzione da implementare. */
}

function groupSelected(destDocType)
{
 var tb = document.getElementById("documentlist");
 var sel = tb.getSelectedRows();
 if(!sel.length)
  return alert("Nessun documento selezionato");
 if(!confirm("Sei sicuro di voler raggruppare i documenti selezionati?"))
  return;

 var list_by_subject = {};
 for(var c=0; c < sel.length; c++)
 {
  var subjId = sel[c].getAttribute('subjectid');
  if(!list_by_subject[subjId]) list_by_subject[subjId] = new Array();
  list_by_subject[subjId].push(sel[c]);
 }

 var sh = new GShell();
 sh.showProcessMessage("Raggruppamento documenti", "Attendere prego, è in corso il raggruppamento dei documenti selezionati.");
 sh.OnError = function(err){alert(err);}
 sh.OnFinish = function(o,a){
	 this.hideProcessMessage();
	 if(!a) return;
	 if(list_by_subject.length == 1)
	  OpenDocument(a['id']);
	 Template.reload();
	}

 for(k in list_by_subject)
 {
  var sel = list_by_subject[k];
  var q = "";
  for(var i=0; i < sel.length; i++)
   q+= " -id '"+sel[i].id+"'";

  var cmd = "commercialdocs group"+(destDocType ? " -type '"+destDocType+"'" : "")+q;
  sh.sendCommand(cmd);
 }

}

function ConfigureLabels()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){Template.SERP.reload();}
 sh.sendCommand("gframe -f config.labels -params `ap=commercialdocs`");
}

function PrintDocument(docId, pdfFileName)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var sh2 = new GShell();
	 sh2.OnError = function(err){alert(err);}
	 sh2.sendCommand("dynarc edit-item -ap `"+AP+"` -id `"+docId+"` -extset `ticketinfo.printdate='now',printfile='"+a['fullname']+"'`");
	}
 sh.sendCommand("dynarc item-info -ap `"+AP+"` -id `"+docId+"` || gframe -f print.preview -params `modelap=printmodels&modelct=tickets&parser=ticket&id="+docId+"` -title `"+pdfFileName+"`");
}

function PrintSelected(allInOne)
{
 var tb = document.getElementById("documentlist");
 var sel = tb.getSelectedRows();
 if(!sel.length)
  return alert("Nessun documento selezionato");

 var ids = "";
 for(var c=0; c < sel.length; c++)
  ids+= ","+sel[c].id;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(a['filename'])
	  document.location.href = ABSOLUTE_URL+"getfile.php?file="+a['filename'];
	}

 sh.sendCommand("gframe -f commercialdocs/print -params 'ids="+ids.substr(1)+"&modelct="+ROOT_CT+(allInOne ? "&allinone=true" : "")+"'");

}

function setFilter(filter)
{
 Template.SERP.setVar("filter",filter);
 Template.SERP.reload();
}


function saveGlobalSettings()
{
 var xml = "<"+ROOT_CT;
 var visibledColumns = "";
 var rpp = document.getElementById('rpp').getValue();

 var tb = document.getElementById("documentlist");
 for(var c=0; c < tb.fields.length; c++)
 {
  var th = tb.fields[c];
  if(th.style.display != "none")
   visibledColumns+= ","+th.getAttribute('field');
 }
 if(visibledColumns)
  xml+= " visibledcolumns='"+visibledColumns.substr(1)+"'";

 xml+= " rpp='"+rpp+"'";
 xml+= "/"+">";

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(){alert("Configurazione salvata");}
 sh.sendSudoCommand("aboutconfig set-config-val -app gcommercialdocs -sec documentlist -xml `"+xml+"`");
}

function OpenDocument(id)
{
 window.open(ABSOLUTE_URL+"<?php echo $template->config['basepath']; ?>docinfo.php?id="+id, "GCD-"+id);
}

function HideLeftSection()
{
 document.getElementById("template-left-section").style.display = "none";
 var tlb = document.getElementById('template-left-bar');
 tlb.style.cursor = "pointer";
 tlb.title = "Mostra barra laterale";
 tlb.onclick = function(){ShowLeftSection();}
 Template.SERP.setVar("hideleftsection",'true');
}

function ShowLeftSection()
{
 document.getElementById("template-left-section").style.display = "";
 var tlb = document.getElementById('template-left-bar');
 tlb.style.cursor = "default";
 tlb.title = "";
 tlb.onclick = null;
 Template.SERP.unsetVar("hideleftsection");
}

function gotoAboutConfig()
{
 document.location.href = ABSOLUTE_URL+"aboutconfig/gcommercialdocs/index.php?continue="+encodeURIComponent(document.location.href);
}

function editStatus(status)
{
 var tb = document.getElementById("documentlist");
 var sel = tb.getSelectedRows();
 if(!sel.length)
  return alert("Nessun document selezionato");
 if(!confirm("Sei sicuro di voler impostare lo status ai documenti selezionati?"))
  return;

 var cmd = "";
 for(var c=0; c < sel.length; c++)
  cmd+= " && dynarc edit-item -ap '"+AP+"' -id '"+sel[c].id+"' -extset `cdinfo.status='''"+status+"'''`";

 var sh = new GShell();
 sh.showProcessMessage("Impostazione status","Attendere prego!, è in corso l'impostazione dello status ai documenti selezionati.");
 sh.OnError = function(err){this.hideProcessMessage(); alert(err);}
 sh.OnOutput = function(o,a){this.hideProcessMessage(); Template.SERP.reload();}
 sh.sendCommand(cmd.substr(4));
}

function editStatusExtra(status)
{
 var tb = document.getElementById("documentlist");
 var sel = tb.getSelectedRows();
 if(!sel.length)
  return alert("Nessun document selezionato");
 if(!confirm("Sei sicuro di voler impostare lo status ai documenti selezionati?"))
  return;

 var cmd = "";
 for(var c=0; c < sel.length; c++)
  cmd+= " && dynarc edit-item -ap '"+AP+"' -id '"+sel[c].id+"' -extset `cdinfo.statusextra='''"+status+"'''`";

 var sh = new GShell();
 //sh.showProcessMessage("Impostazione status","Attendere prego!, è in corso l'impostazione dello status ai documenti selezionati.");
 sh.OnError = function(err){/*this.hideProcessMessage();*/ alert(err);}
 sh.OnOutput = function(o,a){/*this.hideProcessMessage();*/ Template.SERP.reload();}
 sh.sendCommand(cmd.substr(4));
}

function showPrecDocTypeList()
{
 document.getElementById('doctypelist').style.display = "none";
 document.getElementById('precdoctypelist').style.display = "";
 document.getElementById('advanced-search-title').innerHTML = "DOCUMENTI PRE-COMPILATI";
 document.getElementById('precdoc-button').style.display = "none";
 document.getElementById('standarddoc-button').style.display = "";
}

function hidePrecDocTypeList()
{
 document.getElementById('doctypelist').style.display = "";
 document.getElementById('precdoctypelist').style.display = "none";
 document.getElementById('advanced-search-title').innerHTML = "DOCUMENTI COMMERCIALI";
 document.getElementById('precdoc-button').style.display = "";
 document.getElementById('standarddoc-button').style.display = "none";
}

function ImportFromExcelByPrecomp(btn)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var fileName = a['files'][0]['fullname'];

	 var sh2 = new GShell();
	 sh2.OnError = function(err){alert(err);}
	 sh2.OnFinish = function(o){
		 if(!o) return;
		 Template.SERP.reload(0);
		}
	 sh2.sendCommand("gframe -f excel/import -params `ap=commercialdocs&parser=commdocsprec&cat="+CAT_ID+"&file="+fileName+"`");
	}
 sh.sendCommand("gframe -f fileupload");
}

function ImportFromExcel(btn, parser)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var fileName = a['files'][0]['fullname'];

	 var sh2 = new GShell();
	 sh2.OnError = function(err){alert(err);}
	 sh2.OnFinish = function(o){
		 if(!o) return;
		 Template.SERP.reload(0);
		}
	 sh2.sendCommand("gframe -f excel/import -params `ap=commercialdocs&parser="+(parser ? parser : 'commdocs')+"&cat="+CAT_ID+"&file="+fileName+"`");
	}
 sh.sendCommand("gframe -f fileupload");
}

function ImportFromXML()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var fileName = a['files'][0]['fullname'];

	 this.OnOutput = function(o,a){
		 if(a) document.location.reload();
		}
	 this.sendCommand("gframe -f commercialdocs/xmlimport -params `file="+fileName+"`");
	}

 sh.sendCommand("gframe -f fileupload");
}

function FilterByTracknum()
{
 var tracknum = prompt("Digita il numero di tracking");
 if(!tracknum) return;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a || !a['count'] || (a['count'] == '0'))
	  return alert("Nessun documento trovato con questo numero di tracking");
	 if(a['count'] > 1)
	 {
	  alert("Trovati "+a['count']+" documenti");
	  Template.SERP.setVar("tracknum",tracknum);
	  return Template.SERP.reload();
	 }
	 else if(a['items'].length == 1)
	  OpenDocument(a['items'][0]['id']);

	}
sh.sendCommand("dynarc item-list -ap 'commercialdocs' -cat '"+CAT_ID+"' -where `tracking_number='"+tracknum.E_QUOT()+"' OR tracking_number LIKE '"+tracknum.E_QUOT()+encodeURI('%')+"' OR tracking_number LIKE '"+encodeURI('%')+tracknum.E_QUOT()+"' OR tracking_number LIKE '"+encodeURI('%')+tracknum.E_QUOT()+encodeURI('%')+"'` -limit 1");
}

function ExportBodyToExcel()
{
 var tb = document.getElementById("documentlist");
 var sel = tb.getSelectedRows();
 if(!sel.length)
  return alert("Nessun documento selezionato");

 var q = "";
 for(var c=0; c < sel.length; c++)
  q+= ","+sel[c].id;
 if(q != "") q = q.substr(1);

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.href = ABSOLUTE_URL+"getfile.php?file="+a['fullpath'];
	}

 sh.sendCommand("gframe -f commercialdocs/exportbodyelements -params 'ids="+q+"'");
}

function getOrdersFromWebsite(li, serverlist)
{
 if(li)
 {
  var serverlist = new Array();
  var a = new Array();
  a['id'] = li.getAttribute('data-serverid');
  a['tag'] = li.getAttribute('data-servicetag');
  serverlist.push(a);
 }
 else if(!serverlist)
 {
  var sh = new GShell();
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,a){
	 if(!a) return;
	 return getOrdersFromWebsite(null, a);
	}

  sh.sendCommand("gframe -f transponder/select.server -params `tags="+TRANSPONDER_SERVICE_TAGS+"`");
  return;
 }

 //----------------------------------------------------------------------------------------------//
 var serverIds = "";
 var serviceTags = ""; 
 for(var c=0; c < serverlist.length; c++)
 {
  serverIds+= ","+serverlist[c]['id'];
  serviceTags+= ","+serverlist[c]['tag'];
 }

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("gframe -f transponder/order.list -params `serverids="+serverIds.substr(1)+"&servicetags="+serviceTags.substr(1)+"`");

}

</script>
<?php
$template->End();

