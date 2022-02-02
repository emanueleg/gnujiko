<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 09-12-2014
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Gnujiko Commercial Documents.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_CMD, $_RESTRICTED_ACCESS, $_DECIMALS, $_COMPANY_PROFILE, $_COMMERCIALDOCS_CONFIG;

$_BASE_PATH = "../";
$_RESTRICTED_ACCESS = "commercialdocs";
$_AP = "commercialdocs";
$_DECIMALS = 2;

include($_BASE_PATH."var/templates/glight/index.php");

include_once($_BASE_PATH."include/company-profile.php");
include_once($_BASE_PATH."etc/commercialdocs/config.php");
$_BANKS = $_COMPANY_PROFILE['banks'];
$_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'];
$_TOTALS = array(
 'amount' => 0,
 'vat' => 0,
 'total' => 0,
 'ritacc' => 0,
 'ccp' => 0,
 'rinps' => 0,
 'enasarco' => 0,
 'netpay' => 0,
 'rebate' => 0,
 'stamp' => 0,
 'expenses' => 0,
 'discount' => 0,
 'cartage' => 0,
 'packingcharges' => 0,
 'collectioncharges' => 0,
 'agentcommiss' => 0,
);

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
 $_REQUEST['dtfilt'] = 'thismonth';

switch($_REQUEST['dtfilt'])
{
 case 'thismonth' : {$_REQUEST['from'] = date('Y-m-d',$dt); $_REQUEST['to'] = date('Y-m-t',$dt); } break;
 case 'lastmonth' : {$_REQUEST['from'] = date('Y-m-d',strtotime("-1 month",$dt)); $_REQUEST['to'] = date('Y-m-t',strtotime("-1 month",$dt)); } break;
 case 'lastquarter' : {$_REQUEST['from'] = date('Y-m-d',strtotime("-3 month",$dt)); $_REQUEST['to'] = date('Y-m-d',strtotime("-1 day",$dt)); } break;
 case 'lastyear' : {$_REQUEST['from'] = date('Y-m-d',strtotime("-1 year",$dt)); $_REQUEST['to'] = date('Y',strtotime("-1 year",$dt))."-12-31"; } break;
 case 'thisyear' : {$_REQUEST['from'] = date('Y',$dt)."-01-01"; $_REQUEST['to'] = date('Y',$dt)."-12-31"; } break;
}

$dateFrom = $_REQUEST['from'] ? $_REQUEST['from'] : date('Y')."-01-01";
$dateTo = $_REQUEST['to'] ? $_REQUEST['to'] : date('Y-m-d');

$_DEF_RPP = 25;

switch($_CAT_TAG)
{
 case 'VENDORORDERS' : case 'PURCHASEINVOICES' : case 'DDTIN' : $_SUBJTYPE_NAME = "Fornitore"; break;
 case 'AGENTINVOICES' : $_SUBJTYPE_NAME = "Agente"; break;
 case 'MEMBERINVOICES' : $_SUBJTYPE_NAME = "Socio"; break;
}

$_COLUMNS = array(
 0 => array('title'=>'Cod. Cli',		'field'=>'subject_code',	'width'=>50,		'sortable'=>false,	'visibled'=>false),
 1 => array('title'=>$_SUBJTYPE_NAME,	'field'=>'subject_name',			'sortable'=>true,	'visibled'=>true),
 2 => array('title'=>'P.IVA',			'field'=>'subject_vatnumber', 	'width'=>80,	'sortable'=>false,	'visibled'=>false),
 3 => array('title'=>'Cod. Fisc.',		'field'=>'subject_taxcode', 	'width'=>80,	'sortable'=>false,	'visibled'=>false),
 4 => array('title'=>'Spese trasp.',	'field'=>'cartage',			'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 5 => array('title'=>'Spese imballo.',	'field'=>'packingcharges',	'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 6 => array('title'=>'Spese incasso.', 'field'=>'collectioncharges', 'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 7 => array('title'=>'Riv. INPS', 		'field'=>'rivalsainps', 	'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 8 => array('title'=>'Cassa Prev.', 	'field'=>'cassaprev', 		'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 9 => array('title'=>'Enasarco', 		'field'=>'enasarco', 		'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 10 => array('title'=>'Rit. Acc.', 		'field'=>'ritacconto', 		'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 11 => array('title'=>'Abbuoni', 		'field'=>'rebate', 			'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 12 => array('title'=>'Bolli', 			'field'=>'stamp', 			'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 13 => array('title'=>'Tot. spese', 	'field'=>'expenses', 		'width'=>60,		'sortable'=>true,	'visibled'=>false, 	'format'=>'currency'),
 14 => array('title'=>'Imponibile',		'field'=>'amount',			'width'=>70,		'sortable'=>true,	'visibled'=>false,	'format'=>'currency'),
 15 => array('title'=>'I.V.A.',			'field'=>'vat',				'width'=>60,		'sortable'=>true,	'visibled'=>false,	'format'=>'currency'),
 16 => array('title'=>'Totale',			'field'=>'total',			'width'=>70,		'sortable'=>true,	'visibled'=>false,	'format'=>'currency'),
 17 => array('title'=>'Netto a pagare', 'field'=>'netpay', 			'width'=>70,		'sortable'=>true,	'visibled'=>true, 	'format'=>'currency'),
 18 => array('title'=>'Commiss. agente', 'field'=>'agentcommiss',	'width'=>70,		'sortable'=>true,	'visibled'=>false,	'format'=>'currency'),
);

$_FOOTER_COLUMNS = array(
 0 => array('title'=>'IMPONIBILE',		'field'=>'amount',			'width'=>80,		'visibled'=>false),
 1 => array('title'=>'I.V.A.',			'field'=>'vat',				'width'=>80,		'visibled'=>false),
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
);

/* GET COLUMN SETTINGS */
$ret = GShell("aboutconfig get -app gcommercialdocs -sec summarydocumentlist");
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

/* GET CONFIG */
/*$config = array();
$ret = GShell("aboutconfig get-config -app gcommercialdocs");
if(!$ret['error'])
 $config = $ret['outarr']['config'];*/

$centerContents = "<input type='text' class='edit' style='width:290px;float:left' placeholder='Ricerca per ".strtolower($_SUBJTYPE_NAME)."' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\" modal='extended' fields='code_str,name' contactfields='phone,phone2,cell,email' disablerightbtn='true'/>";
$centerContents.= "<input type='button' class='button-search' id='searchbtn'/>";

$_DATE_FILTERS = array('thisyear'=>'Quest&lsquo;anno', 'thismonth'=>'Questo mese', 'lastmonth'=>'Mese scorso', 'lastquarter'=>'Ultimo trimestre', 'lastyear'=>'Anno scorso');

$centerContents.= "<input type='edit' class='dropdown' id='dtfilt' connect='dtfiltlist' readonly='true' style='width:150px;margin-left:30px' value='".$_DATE_FILTERS[$_REQUEST['dtfilt']]."' retval='".$_REQUEST['dtfilt']."' placeholder='filtra per data'/>";
$centerContents.= "<ul class='popupmenu' id='dtfiltlist'>";
reset($_DATE_FILTERS);
while(list($k,$v)=each($_DATE_FILTERS)){ $centerContents.= "<li value='".$k."'>".$v."</li>"; }
$centerContents.= "</ul>";

$centerContents.= "<input type='text' class='calendar' value='".($dateFrom ? date('d/m/Y',strtotime($dateFrom)) : '')."' id='datefrom' style='margin-left:10px'/><span class='smalltext'> al </span><input type='text' class='calendar' value='".($dateTo ? date('d/m/Y',strtotime($dateTo)) : '')."' id='dateto'/>";

if(!$_REQUEST['show'] && !$_REQUEST['statusextra'])
 $_REQUEST['show'] = "all";

$template->Header("search", $centerContents, "BTN_EXIT", 800);
//-------------------------------------------------------------------------------------------------------------------//
//$_ORDER_BY = $_REQUEST['sortby'] ? $_REQUEST['sortby'] : "ordering";
//$_ORDER_METHOD = $_REQUEST['sortmethod'] ? strtoupper($_REQUEST['sortmethod']) : "DESC";
$_RPP = $_REQUEST['rpp'] ? $_REQUEST['rpp'] : $_DEF_RPP;
$_PG = $_REQUEST['pg'] ? $_REQUEST['pg'] : 1;

$_SERP = new SERP();
//$_SERP->setOrderBy($_ORDER_BY);
//$_SERP->setOrderMethod($_ORDER_METHOD);
$_SERP->setResultsPerPage($_RPP);
$_SERP->setCurrentPage($_PG);

 /* RICERCA NORMALE */
 $cmd = "commercialdocs summary-by-subject";
 if($_REQUEST['catid'])	$cmd.= " -cat '".$_CAT_INFO['id']."'";
 else 					$cmd.= " -into '".$_CAT_INFO['id']."'";
 if($_REQUEST['subjectid'])	$cmd.= " -subjid '".$_REQUEST['subjectid']."'";
 if($dateFrom)			$cmd.= " -from '".$dateFrom."'";
 if($dateTo)			$cmd.= " -to '".$dateTo."'";

 if($_REQUEST['show'])
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
	} break;

   case 'MEMBERINVOICES' : {
	 switch($_REQUEST['show'])
	 {
	  case 'tobeemit' : 		$where.= " AND status='0'"; break;
	 }
	} break;

  }
 }

 if($where)	$cmd.= " -where <![CDATA[".ltrim($where,' AND ')."]]>";

$_CMD = $cmd;
$ret = $_SERP->SendCommand($cmd);
$_DOCUMENT_LIST = $ret['items'];

//-------------------------------------------------------------------------------------------------------------------//
$template->SubHeaderBegin(0);
?>
 <input type='button' class='button-blue' value="&laquo; torna alla lista <?php echo strtolower($_TITLE_BAR); ?>" onclick="comeBack()"/>
 </td>
 <td>
	<input type='button' class="button-blue menuwhite" value="Menu" connect='mainmenu' id='mainmenubutton'/>
	<ul class='popupmenu' id='mainmenu'>
	 <li onclick="ExportToExcel(this)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/excel.png"/>Esporta su file Excel</li>
	 <!-- <li onclick="Print(this)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/printer.gif"/>Stampa</li> -->
	 <li class='separator'>&nbsp;</li>
	 <li onclick="comeBack()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/import2.png"/>Torna alla lista <?php echo strtolower($_TITLE_BAR); ?></li>
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
	<div class='advanced-search-title'>DOCUMENTI COMMERCIALI
	 <img src="<?php echo $_ABSOLUTE_URL.$template->config['basepath']; ?>img/hidearrow.png" style="float:right;margin-top:12px;cursor:pointer" title="Nascondi barra laterale" onclick="HideLeftSection()"/>
	</div>
	<?php
	/* SHOW MAIN MENU */
	$ret = GShell("dynarc cat-list -ap commercialdocs");
	$list = $ret['outarr'];
	echo "<ul class='glight-main-menu'>";
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

	 $url = $template->config['basepath'];
	 $active = ($ct == $_ROOT_CT) ? true : false;
	 $url.= $active ? "summary.php?ct=".$ct : "index.php?ct=".$ct;
	 echo "<a href='".$_ABSOLUTE_URL.$url."'><li class='item".($active ? " selected" : "")."'>";
	 echo "<img src='".$_ABSOLUTE_URL.$template->config['basepath'].$icon."'/>";
	 echo "<span class='item-title-singleline'>".$catInfo['name']."</span>";
	 echo "</li></a>";
	}
	echo "</ul>";
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
	  	</ul>
	  </div>

	 <div class="page-contents-body">
	  <!-- START OF PAGE ------------------------------------------------------------------------->
	  <div class="titlebar blue-bar"><span class="titlebar orange-bar">Riepilogo <?php echo $_TITLE_BAR; ?></span></div>

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
	<th width='22'>&nbsp;</th>
</tr>
<?php
$row = 0;
$mod = new GMOD();
$lastdate = "";
for($z=0; $z < count($_DOCUMENT_LIST); $z++)
{
 $item = $_DOCUMENT_LIST[$z];
 $hint = $item['subject_code']." - ".$item['subject_name'];

 echo "<tr class='row".$row."' id='".$item['subject_id']."' title=\"".$hint."\"><td><input type='checkbox'/></td>";
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
   case 'subject_code' : echo $item['subject_code'] ? $item['subject_code'] : "&nbsp;"; break;
   case 'subject_name' : echo $item['subject_name'] ? $item['subject_name'] : "&nbsp;"; break;
   case 'subject_vatnumber' : echo $item['subject_vatnumber'] ? $item['subject_vatnumber'] : "&nbsp;"; break;
   case 'subject_taxcode' : echo $item['subject_taxcode'] ? $item['subject_taxcode'] : "&nbsp;"; break;
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
   case 'agentcommiss' : echo number_format($item['agent_commiss'],$_DECIMALS,',','.'); break;
  }
  echo "</td>";
 }
 echo "<td><img src='".$_ABSOLUTE_URL.$template->config['basepath']."img/next-orange.png' title='Mostra tutti i documenti di questo "
	.strtolower($_SUBJTYPE_NAME)."' style='cursor:pointer' onclick='showAllDocsFor(".$item['subject_id'].")'/></td>";
 echo "</tr>";
 $row = $row ? 0 : 1;

 $_TOTALS['amount']+= 		$item['amount'];
 $_TOTALS['vat']+= 			$item['vat'];
 $_TOTALS['total']+= 		$item['total'];
 $_TOTALS['ritacc']+=		$item['tot_rit_acc'];
 $_TOTALS['ccp']+=			$item['tot_ccp'];
 $_TOTALS['rinps']+=		$item['tot_rinps'];
 $_TOTALS['enasarco']+=		$item['tot_enasarco'];
 $_TOTALS['netpay']+=		$item['tot_netpay'];
 $_TOTALS['rebate']+=		$item['rebate'];
 $_TOTALS['stamp']+=		$item['stamp'];
 $_TOTALS['expenses']+=		$item['tot_expenses'];
 $_TOTALS['discount']+=		$item['tot_discount'];
 $_TOTALS['cartage']+=		$item['cartage'];
 $_TOTALS['packingcharges']+=	$item['packing_charges'];
 $_TOTALS['collectioncharges']+=  $item['collection_charges'];
 $_TOTALS['agentcommiss']+=	$item['agent_commiss'];
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
	 echo "<th class='".$class."' width='".$col['width']."' id='footer-".$col['field']."-title'".($style ? " style='".$style."'" : "").">".$col['title']."</th>";
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
	  case 'amount' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['amount'],$_DECIMALS,',','.')."</td>"; break;
	  case 'vat' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['vat'],$_DECIMALS,',','.')."</td>"; break;
	  case 'total' : echo "class='green' align='right'><em>&euro;</em>".number_format($_TOTALS['total'],$_DECIMALS,',','.')."</td>"; break;
	  case 'ritacconto' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['ritacc'],$_DECIMALS,',','.')."</td>"; break;
	  case 'cassaprev' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['ccp'],$_DECIMALS,',','.')."</td>"; break;
	  case 'rivalsainps' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['rinps'],$_DECIMALS,',','.')."</td>"; break;
	  case 'enasarco' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['enasarco'],$_DECIMALS,',','.')."</td>"; break;
	  case 'netpay' : echo "class='green' align='right'><em>&euro;</em>".number_format($_TOTALS['netpay'],$_DECIMALS,',','.')."</td>"; break;
	  case 'rebate' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['rebate'],$_DECIMALS,',','.')."</td>"; break;
	  case 'stamp' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['stamp'],$_DECIMALS,',','.')."</td>"; break;
	  case 'expenses' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['expenses'],$_DECIMALS,',','.')."</td>"; break;
	  case 'discount' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['discount'],$_DECIMALS,',','.')."</td>"; break;
	  case 'cartage' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['cartage'],$_DECIMALS,',','.')."</td>"; break;
	  case 'packingcharges' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['packingcharges'],$_DECIMALS,',','.')."</td>"; break;
	  case 'collectioncharges' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['collectioncharges'],$_DECIMALS,',','.')."</td>"; break;
	  case 'agentcommiss' : echo "class='blue' align='right'><em>&euro;</em>".number_format($_TOTALS['agentcommiss'],$_DECIMALS,',','.')."</td>"; break;
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

function comeBack()
{
 document.location.href = ABSOLUTE_URL+"<?php echo $template->config['basepath']; ?>index.php?ct="+ROOT_CT;
}

function showAllDocsFor(subjId)
{
 var subjName = document.getElementById(subjId).cells[2].innerHTML;
 var url = ABSOLUTE_URL+"<?php echo $template->config['basepath']; ?>index.php?ct="+ROOT_CT+"&subjectid="+(subjId ? subjId : 'anonymous')+"&from=<?php echo $dateFrom; ?>&to=<?php echo $dateTo; ?>&dtfilt=ctime&search="+escape(decodeURIComponent(subjName));
 window.open(url,"_blank");
}

Template.OnExit = function(){
	document.location.href = ABSOLUTE_URL;
	return false;
}

Template.OnInit = function(){
 /* AUTORESIZE */
 var sH = this.getScreenHeight();
 var tb = document.getElementById("template-outer-mask");
 if(tb.offsetHeight < (sH-115))
  tb.style.height = (sH-115)+"px";
 //if(document.getElementById('documentlist-container').offsetHeight < (sH-240))
  document.getElementById('documentlist-container').style.height = (sH-240)+"px";
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


	this.initEd(document.getElementById("datefrom"), "date").OnDateChange = function(date){
		 Template.SERP.setVar("from",document.getElementById("datefrom").isodate);
		 Template.SERP.setVar("dtfilt","custom");
		};
	this.initEd(document.getElementById("dateto"), "date").OnDateChange = function(date){
		 Template.SERP.setVar("from",document.getElementById("datefrom").isodate);
		 Template.SERP.setVar("to",date);
		 Template.SERP.setVar("dtfilt","custom");
		 Template.SERP.reload();
		};

	this.initEd(document.getElementById('dtfilt'), 'dropdown').onchange = function(){
		 Template.SERP.setVar('dtfilt',this.getValue());
		 Template.SERP.reload();
		};

	this.SERP = new SERP("<?php echo $_SERP->OrderBy; ?>", "<?php echo $_SERP->OrderMethod; ?>", "<?php echo $_SERP->RPP; ?>", "<?php echo $_SERP->PG; ?>");
	var tb = this.initSortableTable(document.getElementById("documentlist"), this.SERP.OrderBy, this.SERP.OrderMethod);
	tb.OnSort = function(field, method){
		 //Template.SERP.OrderBy = field;
	     //Template.SERP.OrderMethod = method;
		 Template.SERP.reload(0);
		}
	tb.OnSelect = function(list){}

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
  case 'amount' : case 'vat' : case 'total' : case 'netpay' : case 'rebate' : case 'expenses' : case 'cartage' : case 'packingcharges' : case 'collectioncharges' : case 'agentcommiss' : case 'resttopay' : case 'enasarco' : case 'stamp' : case 'ritacconto' : case 'cassaprev' : case 'rivalsainps' : case 'totpaid' : showFooterColumn(field,cb.checked); break;
 }
}

function showFooterColumn(field, bool)
{
 document.getElementById("footer-"+field+"-title").style.display = bool ? "" : "none";
 document.getElementById("footer-"+field+"-value").style.display = bool ? "" : "none";
}

function ExportToExcel(btn)
{
 document.getElementById('mainmenubutton').popupmenu.hide();
 var cmd = "<?php echo $_CMD; ?>";
 cmd = cmd.replace("<![CDATA[",'"');
 cmd = cmd.replace("]]>",'"');

 var keys = "subject_code,subject_name,subject_vatnumber,subject_taxcode";
 keys+= ",cartage,packing_charges,collection_charges,tot_rinps,tot_ccp,tot_enasarco,tot_rit_acc";
 keys+= ",rebate,stamp,tot_expenses,amount,vat,total,tot_netpay,agent_commiss";

 var titles = "COD. CLI|"+SUBJTYPE_NAME.toUpperCase()+"|P.IVA|COD.FISC.";
 titles+= "|SP. TRASP|SP. IMBALLO|SP. INCASSO|RIV. INPS|CASSA PREV.|ENASARCO|RIT.ACC.";
 titles+= "|ABBUONI|BOLLI|SPESE|IMPONIBILE|IVA|TOTALE|NETTO A PAGARE|COMMISS. AGENTE";

 var formats = "string|string|string|string";
 formats+= "|currency|currency|currency|currency|currency|currency|currency";
 formats+= "|currency|currency|currency|currency|currency|currency|currency|currency"; 

 cmd+= " || tableize *.items";
 cmd+= " -k '"+keys+"'";
 cmd+= " -n '"+titles+"'";
 cmd+= " -f '"+formats+"'";
 cmd+= " --include-totals";


 var sh = new GShell();
 sh.showProcessMessage("Esportazione in Excel", "Attendere prego, è in corso l'esportazione del riepilogo su file Excel.");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(o,a){
	 this.hideProcessMessage();
	 if(!a) return;
	 var fileName = a['filename'];
	 document.location.href = ABSOLUTE_URL+"getfile.php?file="+fileName;
	}

 var finalCommand = "gframe -f excel/export -params `file=riepilogo_documenti` -command `"+cmd+"`";

 sh.sendCommand(finalCommand);
}

function Print()
{
 /* TODO: funzione da implementare. */
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
 sh.sendSudoCommand("aboutconfig set-config-val -app gcommercialdocs -sec summarydocumentlist -xml `"+xml+"`");
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

</script>
<?php
$template->End();

