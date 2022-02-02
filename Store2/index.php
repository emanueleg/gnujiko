<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 10-05-2017
 #PACKAGE: gstore
 #DESCRIPTION: Official Gnujiko Store Manager. ver.2
 #VERSION: 2.17beta
 #CHANGELOG: 10-05-2017 : Aggiunta funzione getNegativeStockQty.
			 30-04-2017 : Aggiunto colonne visualizzabili giac. fisica di ciascun magazzino.
			 09-09-2016 : Aboutconfig - magazzini predefiniti per carico e scarico.
			 07-09-2016 : Aggiunto funzione ImportQtyFromExcel.
			 18-08-2016 : Integrato con le varianti.
			 04-04-2016 : Possibilita di ordinare le colonne.
			 02-04-2016 : Bug fix storeqty.
			 25-03-2016 : Bug fix vari, possibilita di configurare colonne.
			 14-01-2016 : Aggiornata funzione esporta su excel.
			 05-10-2015 : Aggiunto filtro per categoria.
			 04-07-2015 : Bug fix prodotti in esaurimento.
			 17-02-2015 : Aggiunto menu configurazione avanzata.
			 20-10-2014 : Integrata la nuova gestione della valorizzazione del magazzino.
			 27-08-2014 : restricted access integration.
			 30-07-2014 : Integrato con prodotti finiti, componenti e materiali.
			 12-04-2014 : Bug fix vari.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_CMD, $_RESTRICTED_ACCESS, $_EXTRA_COLUMNS;

$_BASE_PATH = "../";
$_RESTRICTED_ACCESS = "gstore";
$phsearch = "";
$_AT = $_REQUEST['at'] ? $_REQUEST['at'] : 'gmart';
$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : '';
$_CATINFO = null;
$_CATID = 0;
$_SHOW = $_REQUEST['show'];
$_EXTRA_EXT = array();	// extra extension
$_STORE_INFO = array();

switch($_AT)
{
 case 'gmart' : $phsearch = "Cerca un articolo"; break;
 case 'gproducts' : $phsearch = "Cerca un prodotto finito"; break;
 case 'gpart' : $phsearch = "Cerca un componente"; break;
 case 'gmaterial' : $phsearch = "Cerca un materiale"; break;
}

include($_BASE_PATH."var/templates/glight/index.php");
if(file_exists($_BASE_PATH."Store2/config-custom.php"))
 include_once($_BASE_PATH."Store2/config-custom.php");

$template = new GLightTemplate();
$template->includeObject("gcal");
$template->includeObject("editsearch");
$template->includeInternalObject("serp");
$template->includeInternalObject("productsearch");
$template->includeObject("gmutable");
$template->includeCSS("store.css");

$template->Begin("Magazzino");

$centerContents = "<input type='text' class='edit' style='width:390px;float:left' placeholder='".$phsearch."' id='search' value=\""
	.htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\" ap='".($_AP ? $_AP : '')."' at='".$_AT."' emptyonclick='true'/>";
$centerContents.= "<input type='button' class='button-search' id='searchbtn'/>";

if(!$_REQUEST['show'])
 $_REQUEST['show'] = "all";

$config = array();
$show = array("all"=>"Tutti", "soldout"=>"Esauriti", "ums"=>"In esaurimento");
$idx = 0;
$centerContents.= "<ul class='toggles' style='margin-left:30px;float:left'>";
while(list($k,$v)=each($show))
{
 $class = "";
 if($idx == 0)
  $class = "first";
 else if($idx == (count($show)-1))
  $class = "last";
 if($k == $_REQUEST['show'])
  $class.= " selected";
 $centerContents.= "<li".($class ? " class='".$class."'" : "")." onclick=\"setShow('".$k."')\">".$v."</li>";
 $idx++;
}
$centerContents.= "</ul>";

$template->Header("search", $centerContents, "BTN_EXIT", 800);

/* PREPARE COLUMNS */
$_ITEMS_COLUMNS = array(
	"code_str" => array('title'=>'Codice', 'default'=>'true', 'width'=>70, 'sortable'=>true, 'visibled'=>true),
	"name" => array('title'=>'Descrizione articolo', 'default'=>true, 'sortable'=>true, 'visibled'=>true),
	"minimum_stock" => array('title'=>'Scorta min.', 'default'=>true, 'width'=>80, 'sortable'=>true, 'editable'=>true, 'visibled'=>true, 'align'=>'center'),
	"storeqty" => array('title'=>'Giac. fisica', 'default'=>true, 'width'=>80, 'sortable'=>true, 'visibled'=>true, 'align'=>'center'),
	"variants" => array('title'=>'Varianti', 'default'=>true, 'width'=>80, 'sortable'=>false, 'visibled'=>true),
	"booked" => array('title'=>'Prenotati', 'default'=>true, 'width'=>70, 'sortable'=>true, 'editable'=>true, 'visibled'=>true, 'align'=>'center'),
	"incoming" => array('title'=>'Ordinati', 'default'=>true, 'width'=>70, 'sortable'=>true, 'editable'=>true, 'visibled'=>true, 'align'=>'center'),
	"available" => array('title'=>'Disponibili', 'default'=>true, 'width'=>70, 'sortable'=>false, 'visibled'=>true, 'align'=>'center'),
	"enh_amount" => array('title'=>'Valorizz.', 'default'=>true, 'width'=>70, 'visibled'=>true, 'align'=>'right')
);

$get = "";
$db = new AlpaDatabase();
if(is_array($_EXTRA_COLUMNS) && count($_EXTRA_COLUMNS))
{
 for($j=0; $j < count($_EXTRA_COLUMNS); $j++)
 {
  $extraColConfig = $_EXTRA_COLUMNS[$j];
  if($extraColConfig['extension'])
  {
   $db->RunQuery("SELECT ext.id FROM dynarc_archives AS arc INNER JOIN dynarc_archive_extensions AS ext ON ext.archive_id=arc.id AND ext.extension_name='"
	.$extraColConfig['extension']."' WHERE arc.tb_prefix='".($_AP ? $_AP : $_AT)."'");
   if(!$db->Read())
	continue;
  }
  $list = $extraColConfig['columns'] ? $extraColConfig['columns'] : $extraColConfig;

  reset($list);
  while(list($k,$v) = each($list))
  {
   if($_ITEMS_COLUMNS[$k]) continue;
   $_ITEMS_COLUMNS[$k] = $v;
   $_ITEMS_COLUMNS[$k]['visibled'] = true;
   $_ITEMS_COLUMNS[$k]['ext'] = $extraColConfig['extension'] ? $extraColConfig['extension'] : "";
   if($v['dbfield'])	$get.= ",".$v['dbfield'];
  }
 }
}
$db->Close();

if($get) $get = ltrim($get,",");

// Prepare columns
$_COLUMNS = array();
$colcfgOK = false;
/* GET CONFIG - COLUMN SETTINGS */
$ret = GShell("aboutconfig get-config -app gstore -sec columns");
if(!$ret['error'])
{
 $config['columns'] = $ret['outarr']['config'];
 if(is_array($config['columns']) && $config['columns'][$_AT] && count($config['columns'][$_AT]))
 {
  $colcfgOK = true;
  $columns = array();
  for($c=0; $c < count($config['columns'][$_AT]); $c++)
  {
   $col = $config['columns'][$_AT][$c];
   if((strpos($col['tag'], "store_") == 0) && (strpos($col['tag'], "_qty") !== false))
	continue;
   $_COLUMNS[$col['tag']] = $_ITEMS_COLUMNS[$col['tag']];
   $_COLUMNS[$col['tag']]['title'] = $col['title'];
   $_COLUMNS[$col['tag']]['width'] = $col['width'];
  }

  reset($_ITEMS_COLUMNS);
  while(list($k,$v) = each($_ITEMS_COLUMNS))
  {
   if($_COLUMNS[$k]) continue;
   $_COLUMNS[$k] = $v;
   $_COLUMNS[$k]['visibled'] = false;
  }
  $_ITEMS_COLUMNS = $_COLUMNS;
 }
}

if(!$colcfgOK)
 $_COLUMNS = $_ITEMS_COLUMNS;


/* PREPARE SERP */
$_ORDER_BY = $_REQUEST['sortby'] ? $_REQUEST['sortby'] : "name";
$_ORDER_METHOD = $_REQUEST['sortmethod'] ? strtoupper($_REQUEST['sortmethod']) : "ASC";
$_RPP = $_REQUEST['rpp'] ? $_REQUEST['rpp'] : 25;
$_PG = $_REQUEST['pg'] ? $_REQUEST['pg'] : 1;

$_SERP = new SERP();
$_SERP->setOrderBy($_ORDER_BY);
$_SERP->setOrderMethod($_ORDER_METHOD);
$_SERP->setResultsPerPage($_RPP);
$_SERP->setCurrentPage($_PG);

/* EXEC */
$cmd = "store product-list -at '".$_AT."'";
if($_REQUEST['storeid'])			$cmd.= " -store '".$_REQUEST['storeid']."'";
if($_AP)							$cmd.= " -ap '".$_AP."'";
if($_REQUEST['catid'])				$cmd.= " -cat '".$_REQUEST['catid']."'";

if($_REQUEST['prodid'])				$cmd.= " -id '".$_REQUEST['prodid']."'";
else if($_REQUEST['search'])		$cmd.= " -find `".$_REQUEST['search']."` -findfields 'code_str,name,barcode'";

if(isset($_REQUEST['action']) && ($_REQUEST['action'] == "getNegativeStockQty"))
 $cmd.= " -where 'storeqty<0'";
else
{
 switch($_REQUEST['show'])
 {
  case 'soldout' : 					$cmd.= " -where 'storeqty<=0'"; break;
  case 'ums' : 						$cmd.= " -where '(minimum_stock>0) AND (storeqty<=minimum_stock)'"; break;
 }
}

$cmd.= " --get-stock-enhancement --get-variants".($get ? " -get `".$get."`" : "");

$_CMD = $cmd;
$ret = $_SERP->SendCommand($cmd, 'items', 'hidden');
$list = $ret['items'];
$hiddenCount = $ret['hidden'];

//echo $_CMD;

//print_r($list);

if($_SERP->lastErrorMsg)
 echo "<h3>ERRORE: ".$_SERP->lastErrorMsg."</h3>";

/* GET STORE LIST */
$ret = GShell("store list");
$storelist = $ret['outarr'];
$storelistById = array();

for($c=0; $c < count($storelist); $c++)
 $storelistById[$storelist[$c]['id']] = $storelist[$c];

// GET STORE COLUMN CONFIG 
if(is_array($config['columns']) && $config['columns'][$_AT] && count($config['columns'][$_AT]))
{
 for($c=0; $c < count($config['columns'][$_AT]); $c++)
 {
  $col = $config['columns'][$_AT][$c];
  $storeId = str_replace(array('store_', '_qty'), "", $col['tag']);
  $storelistById[$storeId]['visibled'] = true;
 }
}

if($_REQUEST['storeid'])
{
 for($c=0; $c < count($storelist); $c++)
 {
  if($storelist[$c]['id'] == $_REQUEST['storeid'])
  {
   $_STORE_INFO = $storelist[$c];
   break;
  }
 }
}

/* GET CAT INFO */
if($_AP)
{
 $ret = GShell("dynarc archive-info -prefix '".$_AP."'");
 $archiveInfo = $ret['outarr'];

 if($_REQUEST['catid'])
 {
  /* get cat info */
  $ret = GShell("dynarc cat-info -ap '".$_AP."' -id '".$_REQUEST['catid']."'");
  $_CATINFO = $ret['outarr'];
  $_CATID = $ret['outarr']['id'];
 }
}

//-------------------------------------------------------------------------------------------------------------------//
$template->SubHeaderBegin(20);
?>
 <input type='button' class="button-blue menuwhite" value="Menu" connect='mainmenu' id='menubutton' style="float:left"/>
 <img src="img/manual-load-btn.png" style="float:left;margin-left:15px;margin-top:1px;cursor:pointer" title="Carico manuale" onclick="ManualUpload()"/>
 <img src="img/manual-download-btn.png" style="float:left;margin-left:5px;margin-top:1px;cursor:pointer" title="Scarico manuale" onclick="ManualDownload()"/>
 <img src="img/manual-move-btn.png" style="float:left;margin-left:5px;margin-top:1px;cursor:pointer" title="Movimenta" onclick="ManualTransfer()"/>
 <ul class='popupmenu' id='mainmenu'>
  <li onclick="ManualUpload()"><img src="img/upload.png" height="16"/>Carica magazzino</li>
  <li onclick="ManualDownload()"><img src="img/download.png" height="16"/>Scarica dal magazzino</li>
  <li onclick="ManualTransfer()"><img src="img/transfer.png" height="16"/>Movimenta a magazzino</li>
  <li class='separator'>&nbsp;</li>
  <li class='subitem'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/column.gif"/>Visualizza colonne
	<ul class='popupmenu' style='width:320px'>
	 <?php
	 reset($_COLUMNS);
	 while(list($k,$v) = each($_COLUMNS))
	 {
	  echo "<li style='width:150px;float:left'><input type='checkbox' data-field='".$k."' id='col_".$k."_checkbox' onclick=\"showHideColumn('".$k."',this.checked)\"";
	  if($v['visibled'])	echo " checked='true'";
	  echo "/>".$v['title']."</li>";
	 }
	 ?>
	 <li class='separator' style='clear:both'>&nbsp;</li>
	 <?php
	 // Store list
	 for($c=0; $c < count($storelist); $c++)
	 {
	  $storeInfo = $storelistById[$storelist[$c]['id']];
	  $k = "store_".$storeInfo['id']."_qty";

	  echo "<li style='width:150px;float:left'><input type='checkbox' data-field='".$k."' id='col_".$k."_checkbox' onclick=\"showHideColumn('".$k."',this.checked)\"";
	  if($storeInfo['visibled'])	echo " checked='true'";
	  echo "/>Giac. a mag: ".$storeInfo['name']."</li>";
	 }
	 ?>
	 <li class='separator' style='clear:both'>&nbsp;</li>
	 <li onclick="saveColumnsSettings(this.parentNode)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/save.gif"/><?php echo i18n("Save columns settings"); ?></li>
	</ul>
  </li>
  <li onclick="showHideLeftSection()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/change_window.gif" height="16"/>Mostra / Nascondi barra laterale</li>
  <li class='separator'>&nbsp;</li>
  <li onclick="EditStock()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/pencil.gif" height="16"/>Modifica giacenze articoli selezionati</li>
  <li onclick="HideInStore()"><img src="<?php echo $_ABSOLUTE_URL; ?>Store2/img/hide.png" height="16"/>Nascondi articoli selezionati</li>

  <li class='subitem'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/start_application.gif" height="16"/>Altro...
   <ul class='popupmenu'>
	<li onclick="getNegativeStockQty()">Mostra articoli con giacenza in negativo</li>
   </ul>
  </li>

  <li class='separator'>&nbsp;</li>
  <li onclick="ImportQtyFromExcel(this)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/page_white_excel.gif"/>Importa giacenze da Excel</li>
  <li onclick="ExportToExcel(this)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/excel.png"/>Salva su Excel</li>
  <li onclick="Print(this)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/printer.gif"/>Stampa</li>
  <li class='separator'>&nbsp;</li>
  <li onclick="gotoAboutConfig()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/cog.gif"/>Configurazione avanzata</li>

 </ul>
 </td>
 <td width='200'>
  <input type='button' class="button-blue menuwhite" value="<?php echo $_STORE_INFO ? $_STORE_INFO['name'] : 'Tutti gli articoli'; ?>" connect='storeselmenu' id='storeselbutton'/>
		<ul class='popupmenu' id='storeselmenu'>
		<?php
		for($c=0; $c < count($storelist); $c++)
		 echo "<li onclick='selectStore(".$storelist[$c]['id'].",this)'><img src='img/storeicon.png'/>".$storelist[$c]['name']."</li>";
		if(count($storelist))
		 echo "<li class='separator'>&nbsp;</li>";
		echo "<li onclick='selectStore(0,this)'><img src='img/allstores.png'/>Tutti gli articoli</li>";
		?>
		</ul></td>
 <td width='400'>
	<span class='smalltext'>Filtra per catalogo: </span>
	<input type='text' class='dropdown' id='catalog' value="<?php echo $archiveInfo ? $archiveInfo['name'] : ''; ?>" at="<?php echo $_AT; ?>" ap="<?php echo $archiveInfo ? $archiveInfo['prefix'] : ''; ?>" style="width:150px"/>
	&nbsp;&nbsp;
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
 <td>
	<?php $_SERP->DrawSerpButtons(true);
 
//---------------------------------------------//
$template->SubHeaderEnd();

//$template->Body("default", 800, "", "80%");
$template->Body("fullspace");
?>
<!-- START BODY TABLE -->
<table width='100%' height='80%' cellspacing='0' cellpadding='0' border='0'>
 <tr><td width='280' valign='top' id='glight-template-left-section' <?php if($_REQUEST['hideleftsection']) echo "style='display:none'"; ?>>
	 <!-- LEFT SECTION -->
	 <?php
	  echo $template->generateMainMenu();
	 ?></td><td width='20'>&nbsp;</td><!-- spazio vuoto da 20px -->
	 <td valign='top'>

<!-- START OF CONTENT -->

<!-- FILTER-CONTAINER -->
<div id="filter-container">
 <!-- FILTER BY CATEGORY -->
 <div style="margin-bottom:20px;<?php if(!$_AP) echo 'display:none'; ?>">
  <span class='smalltext'>Filtra per categoria: </span>
  <input type='text' class='dropdown' id='cat' style='width:200px' ap="<?php echo $_AP; ?>" value="<?php echo $_CATINFO ? $_CATINFO['name'] : ''; ?>"/>
 </div>

</div> 
<!-- EOF - FILTER-CONTAINER -->

<!-- RESULTS -->
<div class="gmutable" style='width:100%' id='gmutable-container'>
 <table id='productlist' class="gmutable" width='100%' cellspacing="0" cellpadding="0" border="0" style='display:none'>
 <tr><th width='32'><input type="checkbox" onchange="TB.selectAll(this.checked)"/></th>
	<?php
	reset($_COLUMNS);
	while(list($k,$v) = each($_COLUMNS))
	{
	 $style = "";
	 if($v['align'])		$style.= "text-align:".$v['align'].";";
	 if(!$v['visibled'])	$style.= "display: none;";

	 echo "<th id='".$k."'".($style ? " style=\"".$style."\"" : "");

	 if($v['width']) 	echo " width='".$v['width']."'";
	 if($v['sortable']) echo " sortable='true'";
	 if($v['editable']) echo " editable='true'";
	 if($v['format']) 	echo " format='".$v['format']."'";
	 if($v['decimals']) echo " decimals='".$v['decimals']."'";
	 if($v['ext'])		echo " data-ext='".$v['ext']."'";
	 if($v['sum'])		echo " sum='".$v['sum']."'";

	 echo ">".$v['title']."</th>";	 
	}

	// Store list
	for($c=0; $c < count($storelist); $c++)
	{
	 $style = "text-align:center;";
	 $storeInfo = $storelistById[$storelist[$c]['id']];
	 if(!$storeInfo['visibled'])	$style.= "display:none;";
	 
	 echo "<th id='store_".$storeInfo['id']."_qty' style='".$style."' width='60'>".$storeInfo['name']."</th>";
	}
    ?>
 </tr>
 <?php

 for($c=0; $c < count($list); $c++)
 {
  $item = $list[$c];
  $sq = $_REQUEST['storeid'] ? $item['store_'.$_REQUEST['storeid'].'_qty'] : $item['storeqty'];
  $ava = $item['storeqty']-$item['booked'];
  $inc = $item['incoming'];
  if($ava < 0)
   $inc+= $ava;

  echo "<tr id='".$item['id']."' refap='".$item['tb_prefix']."'><td><input type='checkbox'/></td>";
  reset($_COLUMNS);
  while(list($k,$v) = each($_COLUMNS))
  {
   $style = "";
   if($v['align'])		$style.= "text-align:".$v['align'].";";

   echo "<td".($style ? " style=\"".$style."\">" : ">");
   switch($k)
   {
    case 'code_str' : echo "<span class='link blue' onclick='showItemInfo(\"".$item['tb_prefix']."\",\"".$item['id']."\")'>".$item['code_str']."</span>"; break;
    case 'name' : echo "<span class='link blue' onclick='showItemInfo(\"".$item['tb_prefix']."\",\"".$item['id']."\")'>".$item['name']."</span>"; break;
    case 'minimum_stock' : echo "<span class='graybold'>".$item['minimum_stock']."</span>"; break;
    case 'storeqty' : echo "<span class='graybold'>".$sq."</span>"; break;

	case 'variants' : {
	 if(is_array($item['variants']) && count($item['variants']))
	 {
	  reset($item['variants']);
	  while(list($vIdx, $variant) = each($item['variants']))
	  {
	   $vname = ($variant['coltint'] && $variant['sizmis']) ? $variant['coltint']." ".$variant['sizmis'] : ($variant['coltint'] ? $variant['coltint'] : $variant['sizmis']);
	   echo "<span class='smalltext'>".$variant['storeqty']." - ".$vname."</span><br/>";
	  }
	 }
	 else
	  echo "&nbsp;";
	} break;

    case 'booked' : echo "<span class='graybold'>".$item['booked']."</span>"; break;
    case 'incoming' : echo "<span class='graybold'>".$item['incoming']."</span>"; break;
    case 'available' : {
		 if($ava <= 0)  	echo "<span class='smalltext red'><b>0</b></span>";
		 else  		 		echo "<span class='smalltext blue'><b>".$ava."</b></span>";
		 if($inc > 0)   	echo "&nbsp;<span class='smalltext blue'><b>+".$inc."</b></span>";
		 else if($inc < 0)  echo "&nbsp;<span class='smalltext red'><b>-".$inc."</b></span>";
		} break;
	case 'enh_amount' : echo "<span class='graybold'>".number_format($item['enh_amount'],2,',','.')."</span>"; break;

	default : echo "<span class='graybold'>".$item[$v['dbfield']]."</span>"; break;
   }
   echo "</td>";
  }

  // Store list
  for($i=0; $i < count($storelist); $i++)
   echo "<td align='center'><span class='graybold'>".$item['store_'.$storelist[$i]['id'].'_qty']."</span></td>";

  echo "</tr>";
 }
 ?>
 </table>
</div>
<?php
/* GET STORE STATUS */
$cmd = "store get-status -at '".$_AT."'";
if($_STORE_INFO) $cmd.= " -store '".$_STORE_INFO['id']."'";
if($_AP) $cmd.= " -ap '".$_AP."'";

$ret = GShell($cmd,$sessid,$shellid);
$aboutStore = $ret['outarr'];
$soldoutCount = $aboutStore['soldout_count'];
$underminstockCount = $aboutStore['underminstock_count'];

$cmd = "store enhancement";
if($_STORE_INFO) 		$cmd.= " -store '".$_STORE_INFO['id']."'";
if($_AP)	$cmd.= " -ap '".$_AP."'";
$ret = GShell($cmd, $sessid, $shellid);
$stockValue = $ret['outarr']['amount'];

?>
<div class="totals-footer" style="margin-bottom:0px">
 <table width="100%" cellspacing="0" cellpadding="0" border="0">
  <tr><td rowspan='2' valign='middle'>
		<input type='button' class='button-blue' id='save-button' value="Salva" onclick="saveChanges()" style='display:none'/>
		<input type='button' class='button-blue' value='Stampa' onclick="Print(this)"/></td>
	  <td align='center'><span class='smalltext'>prod. nascosti</span></td>
	  <td align='center'><span class='smalltext'>prod. esauriti</span></td>
	  <td align='center'><span class='smalltext'>in esaurimento</span></td>
	  <td align='right'><span class='smalltext'>Valore <?php echo $_STORE_INFO ? "magazzino" : "magazzini"; ?></span></td></tr>

  <tr><td align='center'><span class='smalltext'><?php echo $hiddenCount; ?></span></td>
	  <td align='center'><span class='smalltext'><?php echo $soldoutCount; ?></span></td>
	  <td align='center'><span class='smalltext'><?php echo $underminstockCount; ?></span></td>
	  <td align='right'><span class='bigtext'><b><?php echo number_format($stockValue,2,',','.'); ?> &euro;</b></span></td></tr>
 </table>
</div>


</td><td width='30'>&nbsp;</td></tr></table> <!-- EOF BODY TABLE -->
<?php
/*-------------------------------------------------------------------------------------------------------------------*/
$template->Footer();
?>
<script>
var TB = null;
var AT = "<?php echo $_AT; ?>";
var AP = "<?php echo $_AP; ?>";
var CAT_ID = "<?php echo $_CATID; ?>";
var SHOW = "<?php echo $_SHOW; ?>";

var selectedStoreId = <?php echo $_STORE_INFO ? $_STORE_INFO['id'] : '0'; ?>;
var selectedStoreName = "<?php echo $_STORE_INFO ? $_STORE_INFO['name'] : ''; ?>";

var ON_PRINTING = false;
var ON_EXPORT = false;
var UPDATED_ROWS = new Array();
var SAVED = true;

Template.OnExit = function(){
	document.location.href = ABSOLUTE_URL;
	return false;
}

function gotoAboutConfig()
{
 document.location.href = ABSOLUTE_URL+"aboutconfig/store/index.php?continue="+encodeURIComponent(document.location.href);
}

Template.OnInit = function(){
	this.initBtn(document.getElementById('menubutton'), "popupmenu");
	this.initBtn(document.getElementById('storeselbutton'), "popupmenu");
	this.initEd(document.getElementById('rpp'), "dropdown").onchange = function(){
		 Template.SERP.RPP = this.getValue();
		 Template.SERP.reload(0);
		}

	document.getElementById("search").onchange = function(){this.OnSearch();}
	this.initEd(document.getElementById("search"), AT).OnSearch = function(){
		 if(this.value && this.data)
		 {
		  Template.SERP.setVar("search",this.value);
		  Template.SERP.setVar("prodid",this.data['id']);
		  Template.SERP.setVar("ap",this.data['ap']);
		  Template.SERP.reload(0);
		 }
		 else
		 {
		  Template.SERP.setVar("search",this.value);
		  Template.SERP.unsetVar("prodid");
		  Template.SERP.reload(0);
		 }
		};
	this.initBtn(document.getElementById("searchbtn")).onclick = function(){document.getElementById("search").OnSearch();}

	this.initEd(document.getElementById("catalog"), "archivefind").onchange = function(){
		 var ap = this.value ? this.getAP() : "";
		 Template.SERP.setVar("ap",ap ? ap : "");
		 Template.SERP.unsetVar("catid");
		 Template.SERP.reload(0);
		}

	this.initEd(document.getElementById("cat"), "catfind").onchange = function(){
		 var catId = this.value ? this.getId() : 0;
		 Template.SERP.setVar("catid",catId ? catId : 0);
		 Template.SERP.reload(0);
		}

	this.SERP = new SERP("<?php echo $_SERP->OrderBy; ?>", "<?php echo $_SERP->OrderMethod; ?>", "<?php echo $_SERP->RPP; ?>", "<?php echo $_SERP->PG; ?>");

	this.SERP.OnBeforeReload = function(pg){
		 if(SAVED) return true;
		 if(!confirm("Salvare le modifiche prima di continuare?")) return true;
		 saveChanges(function(){Template.SERP.reload(pg);});
		 return false;
		}

	/* RESIZE TABLE */
	var tbContainer = document.getElementById("productlist").parentNode;
	var filterContainer = document.getElementById("filter-container");
	tbContainer.style.width = tbContainer.offsetWidth+"px";
	tbContainer.style.height = (tbContainer.parentNode.offsetHeight - filterContainer.offsetHeight - 100)+"px";
	document.getElementById("productlist").style.display = "";

	/* INIT TABLE */
	TB = new GMUTable(document.getElementById("productlist"), {autoaddrows:false, orderable:false, autoresize:true});
	TB.setActiveSortField(this.SERP.OrderBy, this.SERP.OrderMethod);
	TB.OnSort = function(field, method){
		 Template.SERP.OrderBy = field;
		 Template.SERP.OrderMethod = method;
		 Template.SERP.reload(0);
		}

	TB.OnCellEdit = function(r, cell, value, data){
		 if(UPDATED_ROWS.indexOf(r) < 0)
		  UPDATED_ROWS.push(r);
		 SAVED = false;
		 showSaveButton();
		}
}

function showSaveButton()
{
 document.getElementById('save-button').style.display = "";
}

function hideSaveButton()
{
 document.getElementById('save-button').style.display = "none";
}

function saveChanges(callback)
{
 var cmd = "";
 for(var c=0; c < UPDATED_ROWS.length; c++)
 {
  var r = UPDATED_ROWS[c];
  cmd+= " && dynarc edit-item -ap '"+r.getAttribute('refap')+"' -id '"+r.id+"'";
  var extset = "storeinfo.booked='"+r.cell['booked'].getValue()+"',incoming='"+r.cell['incoming'].getValue()+"',minstock='"+r.cell['minimum_stock'].getValue()+"'";

  var extensions = new Array();
  for(var j=0; j < TB.Fields.length; j++)
  {
   var field = TB.Fields[j];
   if(!field.editable) continue;
   if(field.O.getAttribute('data-ext'))
   {
	if(!extensions[field.O.getAttribute('data-ext')])
	 extensions[field.O.getAttribute('data-ext')] = new Array();
    extensions[field.O.getAttribute('data-ext')].push(field.name);
   }
  }

  for(k in extensions)
  {
   var q = "";
   for(var i=0; i < extensions[k].length; i++)
	q+= ","+extensions[k][i]+"='''"+r.cell[extensions[k][i]].getValue()+"'''";
   if(q != "")
	extset+= ","+k+"."+q.substr(1);
  }

  cmd+= " -extset `"+extset+"`";
 }

 if(!cmd)
 {
  if(callback) return callback();
  return alert("Niente da salvare");
 }

 var sh = new GShell();
 sh.showProcessMessage("Salvataggio in corso...", "Attendere prego, &egrave; in corso il salvataggio delle modifiche");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnFinish = function(){
	 this.hideProcessMessage();
	 UPDATED_ROWS = new Array();
	 hideSaveButton();
	 SAVED = true;
	 if(callback) return callback();
	}

 sh.sendCommand(cmd.substr(4));
}

function showHideColumn(tag, bool)
{
 TB.showHideColumn(tag,bool);
}

function showItemInfo(ap,id)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(a)
	  document.location.reload();
	}
 switch(AT)
 {
  case 'gmart' : sh.sendCommand("gframe -f gmart/edit.item -params 'ap="+ap+"&id="+id+"'"); break;
  case 'gproducts' : sh.sendCommand("gframe -f gproducts/edit.item -params 'ap="+ap+"&id="+id+"'"); break;
  case 'gpart' : sh.sendCommand("gframe -f gpart/edit.item -params 'ap="+ap+"&id="+id+"'"); break;
  case 'gmaterial' : sh.sendCommand("gframe -f gmaterial/edit.item -params 'ap="+ap+"&id="+id+"'"); break;
  case 'gbook' : sh.sendCommand("gframe -f gbook/edit.item -params 'ap="+ap+"&id="+id+"'"); break;
 }

}

function selectStore(id,li)
{
 Template.SERP.setVar("storeid",id);
 Template.SERP.reload(0);
}

function setShow(value)
{
 Template.SERP.unsetVar("search");
 Template.SERP.unsetVar("prodid");
 Template.SERP.unsetVar("ap");
 Template.SERP.setVar("show",value);
 Template.SERP.reload(0);
}

function ManualUpload()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){Template.SERP.reload();}
 sh.sendCommand("gframe -f gstore/manual.upload -params `storeid=<?php echo $_REQUEST['storeid']; ?>&at="+AT+"`");
}

function ManualDownload()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){Template.SERP.reload();}
 sh.sendCommand("gframe -f gstore/manual.download -params `storeid=<?php echo $_REQUEST['storeid']; ?>&at="+AT+"`");
}

function ManualTransfer()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){Template.SERP.reload();}
 sh.sendCommand("gframe -f gstore/manual.move -params `storeid=<?php echo $_REQUEST['storeid']; ?>&at="+AT+"`");
}

function Print(printBtn)
{
 if(ON_PRINTING)
  return alert("Attendi che il processo per l'esportazione in PDF abbia terminato.");

 printBtn.disabled = true;
 ON_PRINTING = true;

 var xml = "<xml>";
 xml+= "<field name='Codice' tag='code_str' width='25' align='center'/"+">";
 xml+= "<field name='Descrizione' tag='name' width='35'/"+">";
 xml+= "<field name='Scorta min.' tag='minstock' width='15' align='center'/"+">";
 if(selectedStoreId)
 {
  xml+= "<field name='Giac. fis. a mag.' tag='store_"+selectedStoreId+"_qty' format='number' width='20' align='center'/"+">";
  xml+= "<field name='Giac. fis. totale' tag='storeqty' format='number' width='20' align='center'/"+">";
 }
 else
  xml+= "<field name='Giac. fis.' tag='storeqty' format='number' width='30' align='center'/"+">";
 xml+= "<field name='Prenotati' tag='booked' format='number' width='15' align='center'/"+">";
 xml+= "<field name='Ordinati' tag='incoming' format='number' width='15' align='center'/"+">";
 xml+= "<field name='Disponibili' tag='available' format='number' width='20' align='center'/"+">";
 xml+= "<field name='Valorizz.' tag='enh_amount' format='currency' width='20' align='right'/"+">";
 xml+= "</xml>";

 var title = "Situazione magazzino";
 if(selectedStoreId) title+= " "+selectedStoreName;
 var fileName = "situazione-magazzino";
 if(selectedStoreId) fileName+= "-"+selectedStoreName;

 var header = "<div style='font-size:14pt;font-family:arial,sans-serif'>";
 header+= "Situazione magazzino"+(selectedStoreId ? " "+selectedStoreName : "");
 header+= "</div>";


 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnPreOutput = function(){}
 sh.OnOutput = function(o,a){
	 printBtn.disabled = false;
	 ON_PRINTING = false;
	 window.open(ABSOLUTE_URL+a['fullpath']);
	}
 sh.sendCommand("pdf fast-export -title `"+title+"` -header `"+header+"` -format A4 -rpp 27 -margin 10 -filename `"+fileName+"` -xmlfields `"+xml+"` -cmd `<?php echo $_CMD; ?>` -resfield items");
}

function ExportToExcel(exportBtn)
{
 if(ON_EXPORT)
  return alert("Attendi che il processo per l'esportazione in Excel abbia terminato.");

 exportBtn.disabled = true;
 ON_EXPORT = true;

 var fileName = "situazione-magazzino";
 if(selectedStoreId) fileName+= "-"+selectedStoreName;

 var cmd = "store export-to-excel --include-extra-columns";
 if(AP)
 {
  cmd+= " -ap '"+AP+"'";
  if(CAT_ID) cmd+= " -cat '"+CAT_ID+"'";
 }
 else
  cmd+= " -at '"+AT+"'";
 if(selectedStoreId) cmd+= " -store '"+selectedStoreId+"'";
 if(AT == "gmart") cmd+= " --get-stock-enhancement";
 if(SHOW == "soldout") cmd+= " --soldout";
 else if(SHOW == "ums") cmd+= " --ums";

 cmd+= " -f `"+fileName+"`";


 var sh = new GShell();
 sh.showProcessMessage("Esportazione in Excel", "Attendere prego, è in corso l'esportazione su file Excel.");
 sh.OnError = function(err){this.processMessage.error(err); ON_EXPORT=false;}
 sh.OnOutput = function(o,a){
	 ON_EXPORT = false;
	 this.hideProcessMessage();
	 if(!a) return;
	 var fileName = a['filename'];
	 document.location.href = ABSOLUTE_URL+"getfile.php?file="+fileName;
	}

 sh.sendCommand(cmd);
 
}

function ImportQtyFromExcel(importBtn)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var fileName = a['files'][0]['fullname'];

	 var sh2 = new GShell();
	 sh2.showProcessMessage("Caricamento file","Attendere prego, &egrave; in corso il caricamento del file Excel");
	 sh2.OnError = function(err){alert(err);}
	 sh2.OnOutput = function(o,a){
		 if(!a) return this.hideProcessMessage();
		 this.hideProcessMessage();
		 alert("Importazione giacenze completato!");	 
		 document.location.reload();
		}
	 sh2.sendCommand("gframe -f excel/import -params `ap="+AT+"&parser=resetstoreqty&hideoptions=true&file="+fileName+"`");
	}
 sh.sendCommand("gframe -f fileupload");
 
}

function EditStock()
{
 if(!SAVED)
  return saveChanges(function(){EditStock();});

 var sel = TB.GetSelectedRows();
 if(!sel.length)
  return alert("Nessun articolo è stato selezionato");

 var q = "";
 for(var c=0; c < sel.length; c++)
  q+= ","+sel[c].getAttribute('refap')+":"+sel[c].id;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 Template.SERP.reload();
	}
 sh.sendCommand("gframe -f gstore/edit.movements -params `ids="+q.substr(1)+"`");
}

function HideInStore()
{
 var sel = TB.GetSelectedRows();
 if(!sel.length)
  return alert("Nessun articolo è stato selezionato");

 if(!confirm("Sei sicuro di voler nascondere gli articoli selezionati dal magazzino?"))
  return;

 var q = "";
 for(var c=0; c < sel.length; c++)
  q+= ","+sel[c].getAttribute('refap')+":"+sel[c].id;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("store hideinstore -ids `"+q.substr(1)+"`");
}

function saveColumnsSettings(ul)
{
 var columns = new Array();
 for(var c=0; c < TB.Fields.length; c++)
  columns.push(TB.Fields[c].O);

 var list = ul.getElementsByTagName('LI');
 var xml = "";

 for(var c=0; c < columns.length; c++)
 {
  var col = columns[c];
  var cb = document.getElementById('col_'+col.id+'_checkbox');
  if(!cb || !cb.checked || (cb.checked == false))
   continue;
  xml+= "<column tag=\""+col.id+"\" title=\""+col.textContent+"\" width=\""+col.width+"\"/"+">";
 }

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(){alert("Impostazioni colonne salvate correttamente");}
 var cmd = "aboutconfig set-config-val -app gstore -sec columns -arr `"+AT+"` -xml `"+xml+"`";
 sh.sendSudoCommand(cmd);
}

function showHideLeftSection()
{
 var sec = document.getElementById('glight-template-left-section');
 if(sec.style.display == "none")
 {
  sec.style.display = "";
  Template.SERP.unsetVar("hideleftsection");
 }
 else
 {
  sec.style.display = "none";
  Template.SERP.setVar("hideleftsection","1");
 }

 var tb = TB.O;
 var div = document.getElementById('gmutable-container');
 tb.style.display = "none";
 div.style.width = "100%";
 div.style.width = div.offsetWidth+"px";
 tb.style.display = "";

 TB.autoResize();
}

function getNegativeStockQty()
{
 Template.SERP.unsetVar("search");
 Template.SERP.unsetVar("prodid");
 Template.SERP.unsetVar("ap");
 Template.SERP.setVar("action","getNegativeStockQty");
 Template.SERP.reload(0);
}
</script>
<?php

$template->End();

?>


