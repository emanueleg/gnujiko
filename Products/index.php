<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-06-2017
 #PACKAGE: gmart
 #DESCRIPTION: GMart is a official Gnujiko products manager.
 #VERSION: 2.22beta
 #CHANGELOG: 05-06-2017 : Bugfix shell error su alcune funzioni.
			 29-04-2017 : Bugfix (di poco rilievo, scritte errate) su funzione copyCatalog.
			 04-12-2016 : Integrazione con Amazon MWS.
			 06-09-2016 : Bug fix in function importFromExcel.
			 30-07-2016 : Prima integrazione con Gnujiko Transponder.
			 02-03-2016 : Aggiornate funzioni import ed export to excel.
			 28-09-2015 : Aggiunto funzioni mostra e nascondi dal magazzino.
			 24-02-2015 : Bug fix su funzione esporta in excel.
			 19-09-2014 : Restricted access bugfix.
			 27-08-2014 : restricted access integration.
			 12-06-2014 : Integrato con bsmcompat
			 01-06-2014 : Cambiato voci menu in italiano. Fare internazionalizzazione
			 17-05-2014 : Aggiunto parametro fast su importazione da excel.
			 17-02-2014 : Aggiunto funzione copia e sposta su altro catalogo e campo ricerca in alto.
			 14-12-2013 : bug fix.
			 10-10-2013 : Bug fix prezzi sulla clipboard.
			 13-09-2013 : Aggiunto modifiche di gruppo.
			 24-07-2013 : Modifiche varie.
			 11-02-2013 : Aggiunto il cestino.
			 13-01-2013 : Bug fix.
 #TODO: Internazionalization.
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DESKTOP_SHOW_TOOLBAR, $_DESKTOP_TITLE, $_DECIMALS, $_PRICELISTS, $_CATALOGS, $_RESTRICTED_ACCESS;

$_DESKTOP_SHOW_TOOLBAR = false;
$_DESKTOP_TITLE = "Prodotti";
$_DESKTOP_BACKGROUND = "#ffffff";
$_BASE_PATH = "../";
$_RESTRICTED_ACCESS = "gmart";

include($_BASE_PATH.'init/init1.php');
include($_BASE_PATH.'include/session.php');
include($_BASE_PATH."var/templates/glight/index.php");

$template = new GLightTemplate();

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Prodotti</title></head>
<link rel='shortcut icon' href='share/images/favicon.png' />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>Products/common.css" type="text/css" />
<?php
if(file_exists($_BASE_PATH."include/headings/desktop.php"))
{
 include($_BASE_PATH.'include/headings/desktop.php');
}
else
{
 echo "<body>";
 include($_BASE_PATH.'include/headings/default.php');
}
//-------------------------------------------------------------------------------------------------------------------//

include($_BASE_PATH."var/templates/basicapp/index.php");
include($_BASE_PATH."var/objects/htmlgutility/menu.php");
include_once($_BASE_PATH."include/company-profile.php");


$template->includeInternalObject("productsearch");
$template->includeObject("editsearch");
$template->includeCoreCode();

$_TRANSPONDER_SERVICE_TAGS = is_array($template->config['transponder']) ? $template->config['transponder']['service_tags'] : "";
$_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'];

$ret = GShell("pricelists list");
$_PRICELISTS = $ret['outarr'];

$_PLID = 0;
$_PLGET = "";
$_PLINFO = array();
if(count($_PRICELISTS))
{
 $_PLINFO = $_PRICELISTS[0];
 $_PLID = $_PRICELISTS[0]['id'];
 $_PLGET = "pricelist_".$_PLID."_baseprice,pricelist_".$_PLID."_mrate,pricelist_".$_PLID."_vat";
}

$ret = GShell("dynarc archive-list -type gmart -get `thumb_img,thumb_mode` -a");
$_CATALOGS = $ret['outarr'];
if(((count($ret['outarr']) > 1) && !$_REQUEST['aid'] && !$_REQUEST['ap']) || isset($_REQUEST['showcatalogs']))
{
 include_once($_BASE_PATH."Products/catalogchoice.php");
 exit;
}
else if(isset($_REQUEST['trash']))
{
 include_once($_BASE_PATH."Products/trash.php");
 exit;
}

$ret = GShell("dynarc archive-info".($_REQUEST['aid'] ? " -id `".$_REQUEST['aid']."`" : " -ap `".($_REQUEST['ap'] ? $_REQUEST['ap'] : "gmart")."`"));
if(!$ret['error'])
{
 $archiveInfo = $ret['outarr'];
 $_AP = $archiveInfo['prefix'];
 $_AT = $archiveInfo['type'];
}

$catInfo = null;
if($_REQUEST['catid'])
{
 $ret = GShell("dynarc cat-info -ap '".$_AP."' -id '".$_REQUEST['catid']."'");
 if(!$ret['error'])
  $catInfo = $ret['outarr'];
}

basicapp_header_begin();
?>
<table width='100%' border='0' cellspacing="4" cellpadding="5">
<tr><td width='180' align='right' valign='middle'><img src="<?php echo $_ABSOLUTE_URL; ?>Products/img/logo.png"/></td>
	<td width='350'> 
	<ul class='basicbuttons' style="margin-left:40px;">
	 <li><a href='#' onclick="newCategory()"><img src="<?php echo $_ABSOLUTE_URL; ?>Products/img/new-folder.png" border='0'/>Nuova categoria</a></li>
	 <li><a href='#' onclick="newArticle()"><img src="<?php echo $_ABSOLUTE_URL; ?>Products/img/new-article.gif" border='0'/>Nuovo articolo</a></li>
	</ul>
	</td>
	<td width='450'>
	 <input type='text' class='edit' style='width:390px;float:left' placeholder="Cerca un prodotto" id='search' value="" ap="<?php echo $_AP; ?>" emptyonclick='true'/>
	 <input type='button' class='button-search' id='searchbtn'/>
	</td>
	<?php
	$ret = GShell("dynarc trash count -ap `".$_AP."`");
	$countTrash = $ret['outarr']['categories']+$ret['outarr']['items'];
	if($countTrash)
	{
	 echo "<td align='right'><a href='index.php?trash&ap=".$_AP."' style='font-size:12px;text-decoration:none'><img src='".$_ABSOLUTE_URL."Products/img/trash.png' border='0' style='vertical-align:middle'/> Cestino (".$countTrash.")</a></td>";
	}
	?>
	</tr>
</table>
<?php
basicapp_header_end();

basicapp_contents_begin();
//-------------------------------------------------------------------------------------------------------------------//
?>
<table width='100%' height='100%' cellspacing='0' cellpadding='0' border='0'>
<tr><td valign='top' width='160' style="border-right:1px solid #dadada;padding-right:10px;">
	<?php
	$thumbnail = "img/generic-product.jpg";
	if($archiveInfo['thumb_img'])
	 $thumbnail = $_ABSOLUTE_URL.$archiveInfo['thumb_img'];
	?>
	<div class="catalog <?php echo ($archiveInfo['params']['gmart-theme'] ? $archiveInfo['params']['gmart-theme'] : 'light-green'); ?>">
	 <div class='headtit'>CATALOGO</div>
	 <div class="title"><i><?php echo $archiveInfo['name']; ?></i></div>
	 <div class="label"><i><?php echo $archiveInfo['name']; ?></i></div>
	 <div class="thumbnail" style="background-image:url(<?php echo $thumbnail; ?>)">&nbsp;</div>
	</div>
	<br/>
	<ul class='basictree-blue' id='catlist-tree'>
	<?php
	$ret = GShell("dynarc cat-list -ap `".$_AP."`");
	$list = $ret['outarr'];
	for($c=0; $c < count($list); $c++)
	{
	 echo "<li id='".$list[$c]['id']."'><span class='item' onclick='openNode(this.parentNode)'>".$list[$c]['name']."</span></li>";
	}
	?>
	</ul>

	<br/>
	<br/>
	<br/>

	<div id='clipboards-space'>
	<?php
	// CLIPBOARDS //
	$ret = GShell("dynarc clipboard-list -tag gmart");
	$clipboardList = $ret['outarr'];
	$archiveTypes = array();
	for($c=0; $c < count($clipboardList); $c++)
	{
	 echo "<div class='clipboard' id='clipboard-".$clipboardList[$c]['id']."'>";
	 echo "<div class='header-maroon'><span class='title'>".stripslashes($clipboardList[$c]['name'])."</span> <a href='#' class='infobtn' onclick='editClipboard(".$clipboardList[$c]['id'].")'><img src='".$_ABSOLUTE_URL."Products/img/clipboard-info.png' border='0'/></a></div>";
	 $total = 0;
	 for($i=0; $i < count($clipboardList[$c]['elements']); $i++)
	 {
	  $el = $clipboardList[$c]['elements'][$i];
	  if(!$archiveTypes[$el['ap']])
	  {
	   $ret = GShell("dynarc archive-info -ap `".$el['ap']."`");
	   if(!$ret['error'])
		$archiveTypes[$el['ap']] = $ret['outarr']['type'];
	  }
  	  $ret = GShell("commercialdocs getfullinfo -ap `".$el['ap']."` -id `".$el['id']."` -get `thumb_img`");
	  if($ret['error'])
	   continue;
	  $itm = $ret['outarr'];
	  echo "<div class='item'>";
	  if($itm['thumb_img'])
	   echo "<div class='thumbnail' style='background-image: url(".$_ABSOLUTE_URL.$itm['thumb_img'].");'>&nbsp;</div>";
 	  else
  	   echo "<div class='thumbnail' style='background-image: url(".$_ABSOLUTE_URL."share/widgets/gmart/img/photo.png);'>&nbsp;</div>";
	  echo "<span class='title' onclick=\"editItem(".$itm['id'].",'".$el['ap']."','".$archiveTypes[$el['ap']]."')\">".$itm['name']."</span>";
	  echo "<div class='desc'>".$itm['desc']."</div>";
	  echo "</div>";

	  $finalPriceVI = $itm['finalpricevatincluded'];
	  $total+= ($finalPriceVI*$el['qty']);
	 }

	 echo "<div class='footer' id='clipboard-".$clipboardList[$c]['id']."-footer'><span class='green-left'><i>Totale:</i></span><span class='green-right'><b><i>".number_format($total,$_DECIMALS,",",".")."</i></b></span></div>";
	 echo "</div>";
	}
	?>
	</div>
</td><td valign='top' style="padding-left:10px;">

<!-- PATHWAY -->
<ul class='pathbar' style='margin-top:0px;'>
 <li class='first'><a href="<?php echo $_ABSOLUTE_URL; ?>Products/index.php?showcatalogs">Tutti i cataloghi</a></li>
 <li class='last'><a href="<?php echo $_ABSOLUTE_URL; ?>Products/index.php?aid=<?php echo $archiveInfo['id']; ?>"><?php echo $archiveInfo['name']; ?></a></li>
</ul>
<ul class='pathway' id='pathway' style="float:left;margin-top:3px;margin-left:10px;"></ul>

<br/>

<?php
/* FA APPARIRE LA RICERCA PER PART-NUMBER O MARCA-SERIE-MODELLO */
$db = new AlpaDatabase();
$db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='bsmcompat'");
if($db->Read())
{
 ?>
 <div class='bsmcompat-search-container' style='height:70px'>
  <table width='100%' cellspacing='3' cellpadding='0' border='0' class='bsmcompat-search-table'>
   <tr><th width='100' style='text-align:right'>RICERCA</th>
	   <th width='200' style='text-align:center' class='bg-gray'>PART NUMBER</th>
	   <th style='text-align:right'>RICERCA GUIDATA</th>
	   <th width='120' style='text-align:center' class='bg-gray'>MARCA</th>
	   <th width='120' style='text-align:center' class='bg-gray'>SERIE</th>
	   <th width='180' style='text-align:center' class='bg-gray'>MODELLO</th>
   </tr>
   <tr>
	   <td>&nbsp;</td>
	   <td><input type='text' class='edit' style='width:200px' id='bsmcompat-search-partnumber' ap="<?php echo $_AP; ?>" ext="partnumbers" retvalfield="item_id" rettxtfield="partnumber" retarrname="results"/></td>
	   <td>&nbsp;</td>
	   <td><select style='width:120px' id='bsmcompat-select-brand' onchange="bsmcompatBrandChanged(this)">
			<option value=''></option>
			<?php
			$ret = GShell("dynarc cat-list -ap '".$_AP."_bsm'");
			$list = $ret['outarr'];
			for($c=0; $c < count($list); $c++)
			 echo "<option value='".$list[$c]['id']."'>".$list[$c]['name']."</option>";
			?>
		   </select></td>
	   <td><select style='width:120px' id='bsmcompat-select-serie' onchange="bsmcompatSerieChanged(this)"><option value=''></option></select></td>
	   <td><select style='width:180px' id='bsmcompat-select-model' onchange="bsmcompatModelChanged(this)"><option value=''></option></select></td>
   </tr>
  </table>
 </div>
 <?php
}
$db->Close();
?>

<div class='catblock-container' style="height:138px;clear:both;" id='catblock-container'>
<?php
$ret = GShell("dynarc cat-list -ap `".$_AP."` -extget `gmart.subcatcount,.totitemscount,thumbnails`");
$list = $ret['outarr'];
if(count($list) > 5)
 $small=true;
for($c=0; $c < count($list); $c++)
{
 $cat = $list[$c];
 echo "<div class='catblock".($small ? "-small" : "")."'><img src='".$_ABSOLUTE_URL."Products/img/cat-block-btn.png' class='button' onclick='editCat(".$cat['id'].")'/>";
 if($cat['thumb_img'])
  echo "<div class='thumbnail' style=\"background-image: url(".$_ABSOLUTE_URL.$cat['thumb_img'].");\">"; 
 else
  echo "<div class='thumbnail' style=\"background-image: url(".$_ABSOLUTE_URL."Products/img/photo.png);\">";
 echo "<span class='title' onclick='openNodeId(".$cat['id'].")'>".$cat['name']."</span></div>";
 echo "<div class='footer'>";
 echo "<span>Sottocategorie</span> <em>".$cat['subcatcount']."</em>";
 echo "<span>Articoli</span> <em>".$cat['totitemscount']."</em>";
 echo "</div>";
 echo "</div>";
 
}
?>

<!-- <div class='catblock-empty'></div> -->

</div>

<br/>
<br/>

<table width='100%' cellspacing='0' cellpadding='0' border='0'>
<tr><td valign='bottom'>
	
	 <ul class='basicmenu' id='mainmenu'>
	  <li class='gray'><span>Menu</span>
		<ul class="submenu">
		 <li onclick="newArticle()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/new_file.png"/>Nuovo articolo</li>
		 <li onclick="newCategory()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/new_folder.png"/>Nuova categoria</li>
		 <li class="separator">&nbsp;</li>
		 <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/import_orange.gif"/>Importa
			<ul class="submenu">
			 <li onclick="importFromXML()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/xml.png"/>da file XML</li>
			 <li onclick="importFromExcel()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/page_white_excel.gif"/>da file Excel</li>
			</ul></li>
		 <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/export2.png"/>Esporta
			<ul class="submenu">
			 <li onclick="exportToXML()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/xml.png"/>su file XML</li>
			 <li onclick="exportToExcel()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/page_white_excel.gif"/>su file Excel</li>
			</ul></li>
		 <?php
		 if(is_array($template->config['transponder']) && count($template->config['transponder']['servers']))
		 {
		  ?>
		  <li class="separator">&nbsp;</li>
		  <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/icon_websites.gif"/>Pubblica categoria su server
			<ul class="submenu">
			 <?php
			  for($c=0; $c < count($template->config['transponder']['servers']); $c++)
			  {
			   $transponderServerInfo = $template->config['transponder']['servers'][$c];
			   echo "<li onclick='addCatToTransponderBasket(this)' data-serverid='".$transponderServerInfo['id']."' data-servicetag='"
				.$transponderServerInfo['tag']."'>".$transponderServerInfo['name']
				.($transponderServerInfo['tagname'] ? " - ".$transponderServerInfo['tagname'] : "")."</li>";
			  }
			 ?>
			 <li class="separator">&nbsp;</li>
			 <li onclick='addCatToTransponderBasket()'>Seleziona server</li>
			</ul>
		  </li>	 
		  <?php
		 }
		 ?>
		 <li class="separator">&nbsp;</li>
		 <li onclick="editCat(SELECTED_CAT_ID)">Propriet&agrave; categoria</li>
		</ul>
	  </li>

	  <li class='lightgray'><span>Modifica</span>
		<ul class='submenu'>
		 <li id='cutmenubtn' class='disabled' onclick="cut()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/cut.gif"/><?php echo i18n("taglia"); ?></li>
		 <li id='copymenubtn' class='disabled' onclick="copy()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/copy.png"/><?php echo i18n("copia"); ?></li>
		 <li id='pastemenubtn' class='disabled' onclick="paste()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/paste.gif"/><?php echo i18n("incolla"); ?></li>
		 <li class='separator'>&nbsp;</li>
		 <?php
		 if(count($_CATALOGS) > 1)
		 {
		  echo "<li>Copia su altro catalogo... <ul class='submenu'>";
		  for($c=0; $c < count($_CATALOGS); $c++)
		  {
		   if($_CATALOGS[$c]['id'] == $archiveInfo['id'])
			continue;
		   echo "<li onclick='copyToCatalog(\"".$_CATALOGS[$c]['prefix']."\",this)'>".$_CATALOGS[$c]['name']."</li>";
		  }
		  echo "</ul></li>";
		  echo "<li>Sposta su altro catalogo... <ul class='submenu'>";
		  for($c=0; $c < count($_CATALOGS); $c++)
		  {
		   if($_CATALOGS[$c]['id'] == $archiveInfo['id'])
			continue;
		   echo "<li onclick='moveToCatalog(\"".$_CATALOGS[$c]['prefix']."\",this)'>".$_CATALOGS[$c]['name']."</li>";
		  }
		  echo "</ul></li>";
		  echo "<li class='separator'>&nbsp;</li>";
		 }
		 ?>
		 <li onclick="bulkActions()">Modifiche di gruppo</li>
		 <li>Listini
		  <ul class='submenu'>
		   <li onclick="bulkIncludePricelists()">Includi listini</li>
		   <li onclick="bulkExcludePricelists()">Escludi listini</li>
		  </ul>
		 </li>
		 <li class='separator'>&nbsp;</li>
		 <li id='deletemenubtn' class='disabled' onclick="deleteSelectedItems()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/>Elimina selezionati</li>
		</ul>
	  </li>

	  <!-- <li class='lightgray'><span>Visualizza</span></li> -->
	  <li class='blue' id='selectionmenu' style='visibility:hidden;'><span><img src="<?php echo $_ABSOLUTE_URL; ?>var/templates/basicapp/img/checkbox.png" border='0'/>Selezionati</span>
		<ul class="submenu">
		 <li onclick="unselectAll(true)">Annulla selezione</li>
		 <li class='separator'></li>
		 <li id='cutmenubtn' onclick="cut()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/cut.gif"/>Taglia</li>
		 <li id='copymenubtn' onclick="copy()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/copy.png"/>Copia</li>
		 <li id='pastemenubtn' onclick="paste()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/paste.gif"/>Incolla</li>
		 <li class='separator'>&nbsp;</li>
		 <li>Copia negli appunti
		   <ul class="submenu">
			<?php
			for($c=0; $c < count($clipboardList); $c++)
			 echo "<li onclick='copyToClipboard(\"".$clipboardList[$c]['id']."\")'>".$clipboardList[$c]['name']."</li>";
			?>
			<li id='new-clipboard-link' onclick='copyToClipboard()'>Nuovo...</li>
		   </ul>
		 </li>

		 <li>Modifiche di gruppo
		  <ul class="submenu">
		   <li onclick="editSelectedItems('brand')">Marca</li>
		   <li onclick="editSelectedItems('baseprice')">Prezzo di base</li>
		   <li onclick="editSelectedItems('units')">Unit&agrave; di misura</li>
		   <li onclick="editSelectedItems('vatrate')">Aliquota IVA</li>
		   <li onclick="editSelectedItems('vendor')">Fornitore</li>
		   <li onclick="editSelectedItems('pricelists')">Listini prezzi</li>
		   <li class='separator'>&nbsp;</li>
		   <li onclick="showInStore()">Mostra in magazzino</li>
		   <li onclick="hideInStore()">Nascondi dal magazzino</li>
		  </ul>
		 </li>

		 <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/export2.png"/>Esporta
			<ul class="submenu">
			 <li onclick="exportToXML()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/xml.png"/>su file XML</li>
			 <li onclick="exportToExcel()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/page_white_excel.gif"/>su file Excel</li>
			</ul></li>

		 <!-- PUBBLICA -->
		 <?php
		 if(is_array($template->config['transponder']) && count($template->config['transponder']['servers']))
		 {
		  ?>
		  <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/icon_websites.gif"/>Pubblica su server
			<ul class="submenu">
			 <?php
			  for($c=0; $c < count($template->config['transponder']['servers']); $c++)
			  {
			   $transponderServerInfo = $template->config['transponder']['servers'][$c];
			   echo "<li onclick='addToTransponderBasket(this)' data-serverid='".$transponderServerInfo['id']."' data-servicetag='"
				.$transponderServerInfo['tag']."'>".$transponderServerInfo['name']
				.($transponderServerInfo['tagname'] ? " - ".$transponderServerInfo['tagname'] : "")."</li>";
			  }
			 ?>
			 <li class="separator">&nbsp;</li>
			 <li onclick='addToTransponderBasket()'>Seleziona server</li>
			</ul>
		  </li>	 
		  <?php
		 }

		 if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."amazonmws.php"))
		 {
		  $_AMAZON_STORES = array();
		  $ret = GShell("aboutconfig get-config -app amazon");
		  if(!$ret['error'])
		  {
		   $amazonAboutConfig = $ret['outarr']['config'];
		   $amazonMarketplaceCountries = array('it'=>'Italia', 'uk'=>'Regno Unito', 'de'=>'Germania', 'es'=>'Spagna', 'fr'=>'Francia');
		   $amazonMarketplaceIcons = array('it'=>'ITA.png', 'uk'=>'GBR.png', 'de'=>'DEU.png', 'es'=>'ESP.png', 'fr'=>'FRA.png');
		   if(is_array($amazonAboutConfig['mwsconfig']))
		   {
			reset($amazonMarketplaceCountries);
			while(list($cc,$cname) = each($amazonMarketplaceCountries))
			{
			 if($amazonAboutConfig['mwsconfig']['marketplace_id_'.$cc])
			  $_AMAZON_STORES[] = array('title'=>$cname, 'cc'=>$cc);
			}
		   }
		  }

		  if(count($_AMAZON_STORES))
		  {
		   ?>
		   <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/amazon.png"/>Pubblica su Amazon
			<ul class="submenu">
			 <?php
			  for($c=0; $c < count($_AMAZON_STORES); $c++)
			   echo "<li onclick='publishOnAmazon(\"".$_AMAZON_STORES[$c]['cc']."\")'><img src='".$_ABSOLUTE_URL."share/icons/countries/"
				.$amazonMarketplaceIcons[$_AMAZON_STORES[$c]['cc']]."'/>pubblica in ".$_AMAZON_STORES[$c]['title']."</li>";
			 ?>
			 <li class="separator">&nbsp;</li>
			 <li onclick='publishOnAmazon()'>pubblica su tutti gli store</li>
			</ul>
		   </li>
		   <?php
		  }
		 }

		 ?>
		 <li class="separator">&nbsp;</li>
		 <li onclick="deleteSelectedItems()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/><?php echo i18n("Elimina selezionati"); ?></li>
		</ul>
	  </li>
	 </ul>

	</td><td align='right' valign='top'><?php
	 if($_COOKIE['GMART_TRANSPONDER_BASKET_COUNT'])
	 {
	  echo "<div class='transp-basket-pubbtn' onclick='transponderBasketPublish()'>";
	  echo "<span class='transp-basket-pubbtn-title'>Pubblica su internet</span><br/>";
	  echo "<span class='transp-basket-pubbtn-subtitle'>ci sono <b>".$_COOKIE['GMART_TRANSPONDER_BASKET_COUNT']."</b> elementi da pubblicare</span>";
	  echo "</div>";
	 }
	 else
	  echo "&nbsp;";
	?></td>
</tr>
</table>

<hr style="height:1px;border:0px;background:#3364c3;margin-bottom:0px;"/>

<div id="ITEMLIST_SPACE"></div>

</td></tr>
</table>

<?php

//-------------------------------------------------------------------------------------------------------------------//
basicapp_contents_end();

if(file_exists($_BASE_PATH."include/footers/desktop.php"))
 include($_BASE_PATH.'include/footers/desktop.php');
else
 include($_BASE_PATH.'include/footers/default.php');
?>

<script>
var MainMenu = null;
var SELECTED_IDS = new Array();
var SELECTED_CAT_ID = <?php echo $_REQUEST['catid'] ? $_REQUEST['catid'] : "0"; ?>;
var SELECTED_CAT_NAME = "<?php echo $catInfo ? $catInfo['name'] : ''; ?>";
var ITEMLISTIFRAME = null;
var MODSEL = new Array();
var MODACT = "";
var CURRENT_VIEW = "";
var PLID = <?php echo $_PLID ? $_PLID : "0"; ?>;
var PLGET = "<?php echo $_PLGET; ?>";
var DECIMALS = <?php echo $_DECIMALS ? $_DECIMALS : "2"; ?>;
var AP = "<?php echo $_AP ? $_AP : 'gmart'; ?>";
var AT = "<?php echo $_AT ? $_AT : 'gmart'; ?>";
var TRANSPONDER_SERVICE_TAGS = "<?php echo $_TRANSPONDER_SERVICE_TAGS; ?>";

function loadFrameView(view,params)
{
 CURRENT_VIEW = view;
 var sh = new GShell();
 sh.OnPreOutput = function(o,a,msgType){
	 switch(msgType)
	 {
	  case "LOADED" : {
		 document.getElementById('ITEMLIST_SPACE').style.height = "";
		 ITEMLISTIFRAME = a;
		} break;
	  case "SELECT" : {
		 SELECTED_IDS.push(a);
		 document.getElementById('selectionmenu').style.visibility = SELECTED_IDS.length ? "visible" : "hidden";
		 if(SELECTED_IDS.length)
		 {
		  document.getElementById('cutmenubtn').className = "";
		  document.getElementById('copymenubtn').className = "";
		  document.getElementById('deletemenubtn').className = "";
		 }
		} break;

	  case "UNSELECT" : {
		 SELECTED_IDS.splice(SELECTED_IDS.indexOf(a),1);
		 document.getElementById('selectionmenu').style.visibility = SELECTED_IDS.length ? "visible" : "hidden";
		 if(SELECTED_IDS.length)
		 {
		  document.getElementById('cutmenubtn').className = "";
		  document.getElementById('copymenubtn').className = "";
		  document.getElementById('deletemenubtn').className = "";
		 }
		 else
		 {
		  document.getElementById('cutmenubtn').className = "disabled";
		  document.getElementById('copymenubtn').className = "disabled";
		  document.getElementById('deletemenubtn').className = "disabled";
		 }
		} break;

	  case "VIEW_CHANGED" : {
		 document.getElementById('ITEMLIST_SPACE').style.height = document.getElementById('ITEMLIST_SPACE').offsetHeight;
		 var parms = "";
		 for(var c=0; c < SELECTED_IDS.length; c++)
		  parms+=","+SELECTED_IDS[c];
		 if(parms != "")
		  parms = "selected="+parms.substr(1);
		 parms+= (parms ? "&" : "")+"thumbmode=<?php echo $archiveInfo['params']['thumbmode']; ?>";
		 loadFrameView(a,parms);
		} break;

	  case "EDIT_ITEM" : {
		 editItem(a);
		} break;

	  case "JUMP_TO_PAGE" : {
		 var el = ITEMLISTIFRAME.document.getElementById('itemlist-'+(parseInt(a['page'])-1));
		 if(el)
		 {
		  var pos = el.offsetTop+document.getElementById('ITEMLIST_SPACE').offsetTop+110;
		  document.body.scrollTop = pos;
		 }
		 else
		 {
		  ITEMLISTIFRAME.gframe_close();
		  var parms = "";
		  for(var c=0; c < SELECTED_IDS.length; c++)
		   parms+=","+SELECTED_IDS[c];
		  if(parms != "")
		   parms = "selected="+parms.substr(1);
		  parms+= (parms ? "&" : "")+"thumbmode=<?php echo $archiveInfo['params']['thumbmode']; ?>";
		  loadFrameView(CURRENT_VIEW,"pg="+a['page']+"&limit="+a['limit']+(parms != "" ? "&"+parms : ""));
		 }

		} break;

	 }
	}

 sh.sendCommand("gframe -f gmart/itemlist --append-to ITEMLIST_SPACE -h 100% -params `ap="+AP+"&catid="+SELECTED_CAT_ID+"&view="+view+(params ? "&"+params : "")+"`");
}

function desktopOnLoad()
{
 MainMenu = new GMenu(document.getElementById('mainmenu'));

 var div = document.getElementById('catblock-container');
 div.onmouseover = function(){
	 if(this.tim)
	  clearTimeout(this.tim);
	 if(this.scrollHeight > this.offsetHeight)
	  this.tim = window.setTimeout(function(){
		 var oDiv = document.getElementById('catblock-container');
		 oDiv.style.height = (oDiv.scrollHeight < 300) ? oDiv.scrollHeight : 300;
		},800);
	}
 div.onmouseout = function(){
	 if(this.tim)
	  clearTimeout(this.tim);
	 this.tim = window.setTimeout(function(){document.getElementById('catblock-container').style.height = 138;},200);
	}
 loadFrameView("<?php echo $_REQUEST['view'] ? $_REQUEST['view'] : $archiveInfo['params']['defaultview']; ?>","thumbmode=<?php echo $archiveInfo['params']['thumbmode']; ?>");

 /* GLIGHT TEMPLATE */
 Template.OnInit = function(){
	this.initEd(document.getElementById("search"), "gmart").OnSearch = function(){
		 if(this.value && this.data)
		  editItem(this.data['id']);
		};
	this.initBtn(document.getElementById("searchbtn")).onclick = function(){document.getElementById("search").OnSearch();}
	// search partnumber
	var ed = document.getElementById("bsmcompat-search-partnumber");
	if(ed)
	{
	 this.initEd(ed, "extfind", {startqry: "-partnumber `", endqry: "` -limit 10 --order-by 'partnumber ASC' --distinct"});
	 ed.onchange = function(){
		 if(this.value && this.data)
		  editItem(this.data['item_id'],AP,AT);
		}
	}
 }
 Template.init();
}

function editItem(id,archivePrefix,archiveType)
{
 var EDITITEMIFRAME = null;

 if(!archivePrefix)
  archivePrefix = AP;
 if(!archiveType)
  archiveType = AT;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 ITEMLISTIFRAME.document.location.reload();
	}

 sh.OnPreOutput = function(o,a,msgType){
	 switch(msgType)
	 {
	  case 'LOADED' : {EDITITEMIFRAME = a;} break;
	  case 'DELETE' : {
		 if(confirm("Sei sicuro di voler eliminare questo articolo?"))
		 {
		  var sh2 = new GShell();
		  sh2.OnOutput = function(){
			 EDITITEMIFRAME.gframe_close();
			 ITEMLISTIFRAME.document.location.reload();
			}
		  sh2.sendCommand("dynarc delete-item -ap `"+archivePrefix+"` -id `"+a+"`");
		 }
		 return false;
		} break;
	  case 'COPYTOCLIPBOARD' : {
		 var clipboardId = a['clipboard']['id'];
		 if(!document.getElementById('clipboard-'+a['clipboard']['id']))
	 	 {
	  	  // create new clipboard ... //
	  	  var div = document.createElement('DIV');
	  	  div.className = "clipboard";
	  	  div.id = "clipboard-"+a['clipboard']['id'];
		  var html = "<div class='header-maroon'><span class='title'>"+a['clipboard']['name']+"</span> <a href='#' class='infobtn' onclick='editClipboard("+a['clipboard']['id']+")'><img src='"+ABSOLUTE_URL+"Products/img/clipboard-info.png' border='0'/ ></a></div>";
		  html+= "<div class='footer' id='clipboard-"+a['clipboard']['id']+"-footer'><span class='green-left'><i>Totale:</i></span><span class='green-right'><b><i>"+formatCurrency(0,DECIMALS)+"</i></b></span></div>";
		  div.innerHTML = html;
		  document.getElementById('clipboards-space').appendChild(div);
		  id = a['clipboard']['id'];
		  var link = document.getElementById('new-clipboard-link');
		  var li = document.createElement('LI');
		  li.cbid = id;
		  li.innerHTML = a['clipboard']['name'];
		  li.onclick = function(){copyToClipboard(this.cbid);}
		  link.parentNode.insertBefore(li,link);
		 }

	 	 var sh2 = new GShell();
	 	 sh2.OnOutput = function(o,a){
		 	 var div = document.createElement('DIV');
		 	 div.className = "item";
		 	 var html = "";
		 	 if(a['thumbnails'])
		  	  html+= "<div class='thumbnail' style='background-image: url("+ABSOLUTE_URL+a['thumbnails'][0]+");'>&nbsp;</div>";
		 	 else
		  	  html+= "<div class='thumbnail' style='background-image: url("+ABSOLUTE_URL+"share/widgets/gmart/img/photo.png);'>&nbsp;</div>";
		 	 html+= "<span class='title' onclick=\"editItem("+a['id']+",'"+archivePrefix+"','"+archiveType+"')\">"+a['name']+"</span>";
		 	 html+= "<div class='desc'>"+a['desc']+"</div>";
		 
		 	 var baseprice = parseFloat(a["pricelist_"+PLID+"_baseprice"] ? a["pricelist_"+PLID+"_baseprice"] : a['baseprice']);
 	     	 var markuprate = parseFloat(a["pricelist_"+PLID+"_mrate"] ? a["pricelist_"+PLID+"_mrate"] : <?php echo $_PLINFO['markuprate']; ?>);
	     	 var vat = parseFloat(a["pricelist_"+PLID+"_vat"] ? a["pricelist_"+PLID+"_vat"] : <?php echo $_PLINFO['vat']; ?>);
 	     	 var finalPrice = baseprice ? baseprice + ((baseprice/100)*markuprate) : 0;
 	     	 var finalPriceVI = finalPrice ? finalPrice + ((finalPrice/100)*vat) : 0;

		 	 div.innerHTML = html;
		 	 document.getElementById('clipboard-'+clipboardId).insertBefore(div, document.getElementById('clipboard-'+clipboardId+'-footer'));

		 	 /* Update final price */
		 	 var fpO = document.getElementById('clipboard-'+clipboardId+'-footer').getElementsByTagName('SPAN')[1].getElementsByTagName('I')[0];
		 	 var fp = parseCurrency(fpO.innerHTML);
		 	 fp+= finalPriceVI;
		 	 fpO.innerHTML = formatCurrency(fp,DECIMALS);
		 	 alert("L'articolo è stato copiato negli appunti!");
			}
	 	 sh2.sendCommand("dynarc item-info -ap `"+a['element']['ap']+"` -id `"+a['element']['id']+"` -extget `thumbnails,pricing`"+(PLGET ? " -get `"+PLGET+"`" : ""));
		} break;
	 }
	}

 switch(archiveType)
 {
  case 'gmart' : sh.sendCommand("gframe -f gmart/edit.item -params `ap="+archivePrefix+"&id="+id+"`"); break;
  case 'gserv' : sh.sendCommand("gframe -f gserv/edit.item -params `ap="+archivePrefix+"&id="+id+"`"); break;
 }
}

/* CATEGORY TREE */
var SELECTED_CAT_TREE_NODE = null;

function openNode(li)
{
 var parent = li.parentNode;
 var list = parent.getElementsByTagName('LI');
 var oldSelectedCatTreeNode = SELECTED_CAT_TREE_NODE;
 for(var c=0; c < list.length; c++)
 {
  if(list[c] != li)
  {
   if(oldSelectedCatTreeNode == list[c])
	oldSelectedCatTreeNode = null;
   if(list[c].className == "expanded")
	closeNode(list[c]);
  }
 }
 if(oldSelectedCatTreeNode && (oldSelectedCatTreeNode != parent.parentNode))
  closeNode(oldSelectedCatTreeNode);

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 var ul = document.createElement('UL');
	 ul.className = "basictree-blue";
	 var cbc = document.getElementById('catblock-container');
	 cbc.innerHTML = "";
	 if(!li.getElementsByTagName('UL').length)
	 {
	  if(a)
	  {
	   for(var c=0; c < a.length; c++)
	   {
	    var _li = document.createElement('LI');
	    _li.id = a[c]['id'];
	    _li.innerHTML = "<span class='item' onclick='openNode(this.parentNode)'>"+a[c]['name']+"</span>";
	    ul.appendChild(_li);
	   }
	   li.appendChild(ul);
	  }
	 }
	 else
	  li.getElementsByTagName('UL')[0].style.display='';
	 if(!a)
	 {
	  // hide cat-block-container //
	  cbc.style.display = 'none';
	  return;
	 }
	 cbc.style.display = '';
	 //----------------------------------------//
	 for(var c=0; c < a.length; c++)
	 {
	  var div = document.createElement('DIV');
	  div.className = "catblock"+((a.length > 5) ? "-small" : "");
	  var html = "<img src='"+ABSOLUTE_URL+"Products/img/cat-block-btn.png' class='button' onclick='editCat("+a[c]['id']+")'/ >";
	  if(a[c]['thumb_img'])
	   html+= "<div class='thumbnail' style=\"background-image: url("+ABSOLUTE_URL+a[c]['thumb_img']+");\">";
	  else
	   html+= "<div class='thumbnail' style=\"background-image: url("+ABSOLUTE_URL+"Products/img/photo.png);\">";
	  html+= "<span class='title' onclick='openNodeId("+a[c]['id']+")'>"+a[c]['name']+"</span></div>";
	  html+= "<div class='footer'><span>Sottocategorie</span> <em>"+a[c]['subcatcount']+"</em>";
	  html+= "<span>Articoli</span> <em>"+a[c]['totitemscount']+"</em></div>";
	  div.innerHTML = html;
	  cbc.appendChild(div);
	 }
	}
 sh.sendCommand("dynarc cat-list -ap `"+AP+"` -parent `"+li.id+"` -extget `gmart.subcatcount,.totitemscount,thumbnails`");
  

 li.getElementsByTagName('SPAN')[0].className = 'active';
 var liname = li.getElementsByTagName('SPAN')[0].innerHTML;
 SELECTED_CAT_TREE_NODE = li;

 if(li.className != "expanded")
 {
  var pw = document.getElementById('pathway');
  if(pw.getElementsByTagName('LI').length)
   pw.getElementsByTagName('LI')[pw.getElementsByTagName('LI').length-1].className = "";
  var pwLI = document.createElement('LI');
  pwLI.innerHTML = liname;
  pwLI.id = "pw-"+li.id;
  pwLI.className = "last";
  pwLI.onclick = function(){openNodeId(this.id.substr(3));}
  pw.appendChild(pwLI);
 }

 if(ITEMLISTIFRAME)
  ITEMLISTIFRAME.OnSelectCategory(li.id);

 SELECTED_CAT_ID = li.id;
 SELECTED_CAT_NAME = li.textContent;

 unselectAll();

 li.className = "expanded";
}

function closeNode(li)
{
 if(li.getElementsByTagName('UL').length)
 {
  var ul = li.getElementsByTagName('UL')[0];
  var list = ul.getElementsByTagName('LI');
  for(var c=0; c < list.length; c++)
   closeNode(list[c]);
  ul.style.display='none';
 }

 if(li.className == "expanded")
 {
  var pw = document.getElementById('pathway');
  pw.removeChild(pw.getElementsByTagName('LI')[pw.getElementsByTagName('LI').length-1]);
  if(pw.getElementsByTagName('LI').length)
   pw.getElementsByTagName('LI')[pw.getElementsByTagName('LI').length-1].className = "last";
 }

 li.className = "";
 li.getElementsByTagName('SPAN')[0].className = 'item';
 SELECTED_CAT_TREE_NODE = null;
}

function newCategory()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(a[0]['parent_id'] && (a[0]['parent_id'] != "0"))
	 {
	  var li = document.getElementById(a[0]['parent_id']);
	  if(li.getElementsByTagName('UL').length)
	   var ul = li.getElementsByTagName('UL')[0];
	  else
	  {
	   var ul = document.createElement('UL');
	   ul.className = "basictree-blue";
	   li.appendChild(ul);
	  }
	 }
	 else
	  var ul = document.getElementById('catlist-tree');

	 for(var c=0; c < a.length; c++)
	 {
	  var li = document.createElement('LI');
	  li.id = a[c]['id'];
	  li.innerHTML = "<span class='item' onclick='openNode(this.parentNode)'>"+a[c]['name']+"</span>";
	  ul.appendChild(li);
	 }

	 switch(o)
	 {
	  case 'ENTER' : {
		 openNodeId(a[0]['id']);
		} break;
	  default : {
		 var catNames = "";
		 for(var c=0; c < a.length; c++)
		  catNames+= "#"+a[c]['id']+" - "+a[c]['name']+"\n";
		 openNodeId(SELECTED_CAT_ID);
		} break;
	 }

	}

 sh.sendCommand("gframe -f gmart/new.cat -params `ap="+AP+"&cat="+SELECTED_CAT_ID+"`");
}

function newArticle()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 editItem(a['id']);
	}
 sh.sendCommand("dynarc new-item -ap `"+AP+"` -group gmart -name `Senza nome`"+(SELECTED_CAT_ID ? " -cat "+SELECTED_CAT_ID : ""));
}

function openNodeId(id)
{
 if(!id)
  return document.location.reload();

 var li = document.getElementById(id);
 openNode(li);
}

function cut()
{
 MODSEL = new Array();
 for(var c=0; c < SELECTED_IDS.length; c++)
  MODSEL.push(SELECTED_IDS[c]);
 MODACT = "CUT";
 document.getElementById('pastemenubtn').className = "";
}

function copy()
{
 MODSEL = new Array();
 for(var c=0; c < SELECTED_IDS.length; c++)
  MODSEL.push(SELECTED_IDS[c]);
 MODACT = "COPY";
 document.getElementById('pastemenubtn').className = "";
}

function paste()
{
 if(!MODSEL.length)
  return alert("Devi selezionare almeno un prodotto");

 switch(MODACT)
 {
  case 'CUT' : {
	 var q = "";
	 for(var c=0; c < MODSEL.length; c++)
	  q+= " -id "+MODSEL[c];
	 var sh = new GShell();
	 sh.OnOutput = function(){
		 unselectAll();
		 ITEMLISTIFRAME.document.location.reload();
		}
	 sh.sendCommand("dynarc item-move -ap `"+AP+"` -cat `"+SELECTED_CAT_ID+"`"+q);
	} break;

  case 'COPY' : {
	 var q = "";
	 for(var c=0; c < MODSEL.length; c++)
	  q+= " -id "+MODSEL[c];
	 var sh = new GShell();
	 sh.OnOutput = function(){
		 unselectAll();
		 ITEMLISTIFRAME.document.location.reload();
		}
	 sh.sendCommand("dynarc item-copy -ap `"+AP+"` -cat `"+SELECTED_CAT_ID+"`"+q);
	} break;
 }
 MODSEL = new Array();
 MODACT = "";
}

function importFromXML()
{
 var sh = new GShell();
 sh.OnOutput = function(){
	 unselectAll();
	 ITEMLISTIFRAME.document.location.reload();
	}
 sh.sendCommand("gframe -f dynarc.import -params `ap="+AP+"&cat="+SELECTED_CAT_ID+"`");
}

function importFromExcel()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var fileName = a['files'][0]['fullname'];

	 var sh2 = new GShell();
	 sh2.showProcessMessage("Caricamento file","Attendere prego, &egrave; in corso il caricamento del file Excel");
	 sh2.OnError = function(err){alert(err);}
	 sh2.OnPreOutput = function(msg,data,msgType){
		 if(msgType == "LOADED")
		  this.hideProcessMessage();
		}
	 sh2.OnOutput = function(o,a){
		 if(!a) return this.hideProcessMessage();
		 this.hideProcessMessage();
		 if(a > 1)
		  document.location.reload();
		 else
		 {
		  unselectAll();
		  ITEMLISTIFRAME.document.location.reload();
		 }
		}
	 sh2.sendCommand("gframe -f excel/import -params `ap="+AP+"&cat="+SELECTED_CAT_ID+"&group=gmart&parser=gmart&fast=true&file="+fileName+"`");
	}
 sh.sendCommand("gframe -f fileupload");
}

function exportToXML()
{
 var sh = new GShell();
 sh.OnPreOutput = function(){}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.href = ABSOLUTE_URL+"getfile.php?file="+a['filename'];
	}
 var q = "";
 if(SELECTED_IDS.length)
 {
  var title = "articoli";
  for(var c=0; c < SELECTED_IDS.length; c++)
   q+= " -id "+SELECTED_IDS[c];
 }
 else if(SELECTED_CAT_ID)
 {
  var title = SELECTED_CAT_NAME;
  q = " -cat '"+SELECTED_CAT_ID+"'";
 }
 else
  alert("Devi selezionare almeno un'articolo oppure entrare in una categoria (se desideri esportarla totalmente)");

 sh.sendCommand("dynarc export -ap `"+AP+"` -f `"+title+"`"+q);
}

function exportToExcel()
{
 if(!SELECTED_IDS.length)
 {
  // export categories or the entire archive //
  var sh = new GShell();
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.href = ABSOLUTE_URL+"getfile.php?file="+a['filename'];
	}
  sh.sendCommand("gframe -f gmart/excel.export -params `ap="+AP+"`");
  return;  
 }

 var q = "";
 // export only selected elements //
 for(var c=0; c < SELECTED_IDS.length; c++)
  q+= " -id "+SELECTED_IDS[c];

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnPreOutput = function(){}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.href = ABSOLUTE_URL+"getfile.php?file="+a['filename'];
	}
 sh.sendCommand("gmart export-to-excel -ap `"+AP+"` -file `Articoli.xlsx`"+q);
}


function deleteSelectedItems()
{
 if(!SELECTED_IDS.length)
  return alert("Devi selezionare almeno un prodotto");

 if(!confirm("Sei sicuro di voler eliminare i prodotti selezionati?"))
  return;

 var q = "";
 for(var c=0; c < SELECTED_IDS.length; c++)
  q+= " -id "+SELECTED_IDS[c];

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(){
	 unselectAll();
	 ITEMLISTIFRAME.document.location.reload();
	}
 sh.sendCommand("dynarc delete-item -ap `"+AP+"`"+q);
}

function editSelectedItems(prop)
{
 if(!SELECTED_IDS.length)
  return alert("Devi selezionare almeno un prodotto");

 var cmd = "";
 var params = "";
 var ids = "";

 for(var c=0; c < SELECTED_IDS.length; c++)
  ids+= ","+SELECTED_IDS[c];
 ids = ids.substr(1);


 var sh = new GShell();
 sh.OnError = function(msg){alert(msg);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 unselectAll();
	 ITEMLISTIFRAME.document.location.reload();
	}

 switch(prop)
 {
  case 'brand' : sh.sendCommand("gframe -f gmart/bulkedit.brand -params `ap="+AP+"&ids="+ids+"`"); break;
  case 'baseprice' : sh.sendCommand("gframe -f gmart/bulkedit.baseprice -params `ap="+AP+"&ids="+ids+"`"); break;
  case 'units' : sh.sendCommand("gframe -f gmart/bulkedit.units -params `ap="+AP+"&ids="+ids+"`"); break;
  case 'vatrate' : sh.sendCommand("gframe -f gmart/bulkedit.vatrate -params `ap="+AP+"&ids="+ids+"`"); break;
  case 'vendor' : sh.sendCommand("gframe -f gmart/bulkedit.vendor -params `ap="+AP+"&ids="+ids+"`"); break;
  case 'pricelists' : sh.sendCommand("gframe -f gmart/bulkedit.pricelists -params `ap="+AP+"&ids="+ids+"`"); break;
 }

}

function bulkActions()
{
 var sh = new GShell();
 sh.OnError = function(msg){alert(msg);}
 sh.OnOutput = function(o,a){
	 unselectAll();
	 ITEMLISTIFRAME.document.location.reload();
	}
 sh.sendCommand("gframe -f gmart/bulkedit.multichange -params `ap="+AP+"`");
}

function bulkIncludePricelists()
{
 var sh = new GShell();
 sh.OnError = function(msg){alert(msg);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 unselectAll();
	 ITEMLISTIFRAME.document.location.reload();
	}
 sh.sendCommand("gframe -f gmart/bulkedit.includepricelists -params `ap="+AP+"`");
}

function bulkExcludePricelists()
{
 var sh = new GShell();
 sh.OnError = function(msg){alert(msg);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 unselectAll();
	 ITEMLISTIFRAME.document.location.reload();
	}
 sh.sendCommand("gframe -f gmart/bulkedit.excludepricelists -params `ap="+AP+"`");
}

function unselectAll(reloadFrame)
{
 SELECTED_IDS = new Array();
 document.getElementById('cutmenubtn').className = "disabled";
 document.getElementById('copymenubtn').className = "disabled";
 document.getElementById('deletemenubtn').className = "disabled";
 document.getElementById('selectionmenu').style.visibility = "hidden";
 if(reloadFrame)
  ITEMLISTIFRAME.document.location.reload();
}

function editCat(id)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(a['removed'] || a['trashed'])
	 {
	  /* Da fare in caso di rimozione della categoria */
	 }

	 document.location.reload();
	}
 sh.sendCommand("gframe -f gmart/edit.cat -params `ap="+AP+"&id="+id+"`");
}

function copyToClipboard(id)
{
 if(!SELECTED_IDS.length)
  return alert("Devi selezionare almeno un prodotto");

 if(!id)
 {
  var cpName = prompt("Inserisci un titolo per gli appunti");
  if(!cpName)
   return;
 }

 var idx = 0;
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!id && (idx == 0))
	 {
	  // create new clipboard ... //
	  var div = document.createElement('DIV');
	  div.className = "clipboard";
	  div.id = "clipboard-"+a['clipboard']['id'];
	  var html = "<div class='header-maroon'><span class='title'>"+a['clipboard']['name']+"</span> <a href='#' class='infobtn' onclick='editClipboard("+a['clipboard']['id']+")'><img src='"+ABSOLUTE_URL+"Products/img/clipboard-info.png' border='0'/ ></a></div>";
	  html+= "<div class='footer' id='clipboard-"+a['clipboard']['id']+"-footer'><span class='green-left'><i>Totale:</i></span><span class='green-right'><b><i>"+formatCurrency(0,DECIMALS)+"</i></b></span></div>";
	  div.innerHTML = html;
	  document.getElementById('clipboards-space').appendChild(div);
	  id = a['clipboard']['id'];
	  var link = document.getElementById('new-clipboard-link');
	  var li = document.createElement('LI');
	  li.cbid = id;
	  li.innerHTML = a['clipboard']['name'];
	  li.onclick = function(){copyToClipboard(this.cbid);}
	  link.parentNode.insertBefore(li,link);
	 }

	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){
		 var div = document.createElement('DIV');
		 div.className = "item";
		 var html = "";
		 if(a['thumb_img'])
		  html+= "<div class='thumbnail' style='background-image: url("+ABSOLUTE_URL+a['thumb_img']+");'>&nbsp;</div>";
		 else
		  html+= "<div class='thumbnail' style='background-image: url("+ABSOLUTE_URL+"share/widgets/gmart/img/photo.png);'>&nbsp;</div>";
		 html+= "<span class='title' onclick='editItem("+a['id']+")'>"+a['name']+"</span>";
		 html+= "<div class='desc'>"+a['desc']+"</div>";
		 
		 div.innerHTML = html;
		 document.getElementById('clipboard-'+id).insertBefore(div, document.getElementById('clipboard-'+id+'-footer'));

		 /* Update final price */
		 var fpO = document.getElementById('clipboard-'+id+'-footer').getElementsByTagName('SPAN')[1].getElementsByTagName('I')[0];
		 var fp = parseCurrency(fpO.innerHTML);
		 fp+= parseFloat(a['finalpricevatincluded']);
		 fpO.innerHTML = formatCurrency(fp,DECIMALS);
		}
	 sh2.sendCommand("commercialdocs getfullinfo -ap `"+AP+"` -id `"+SELECTED_IDS[idx]+"` -get `thumb_img`");

	 idx++;
	}

 for(var c=0; c < SELECTED_IDS.length; c++)
 {
  if(!id && (c == 0))
   sh.sendCommand("dynarc copy-to-clipboard -clipboard `"+cpName+"` -tag gmart -ap `"+AP+"` -id `"+SELECTED_IDS[c]+"`");
  else if(!id)
   sh.sendCommand("dynarc copy-to-clipboard --last-clipboard -ap `"+AP+"` -id `"+SELECTED_IDS[c]+"`");
  else
   sh.sendCommand("dynarc copy-to-clipboard -clipboardid `"+id+"` -ap `"+AP+"` -id `"+SELECTED_IDS[c]+"`");
 }
}

function editClipboard(id)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnPreOutput = function(o,a,msgType){
	 switch(msgType)
	 {
	  case 'APPEND-TO-DOCUMENT' : window.open(ABSOLUTE_URL+"GCommercialDocs/docinfo.php?id="+a, "GCD-"+a); break;
	  case 'REMOVED' : document.location.reload(); break;
	 }

	}
 sh.sendCommand("gframe -f gmart/edit.clipboard -params `id="+id+"`");
}

function copyToCatalog(destAP,li)
{
 var qry = "";
 if(SELECTED_IDS.length)
 {
  if(!confirm("Sei sicuro di voler copiare tutti gli articoli selezionati nel catalogo "+li.innerHTML+" ?"))
   return;
  for(var c=0; c < SELECTED_IDS.length; c++)
   qry+= " -id "+SELECTED_IDS[c];
 }
 else if(SELECTED_CAT_ID)
 {
  if(!confirm("Sei sicuro di voler copiare tutti gli articoli di questa categoria nel catalogo "+li.innerHTML+" ?"))
   return;
  qry+= " -cat "+SELECTED_CAT_ID;
 }
 else
  return alert("Devi selezionare almeno un articolo, oppure entrare in una categoria (se desideri copiarla tutta su un altro catalogo).");

 var cmd = "dynarc export -ap '"+AP+"'"+qry+" || dynarc import -ap '"+destAP+"' -xml *.xml";

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnPreOutput = function(){}
 sh.OnOutput = function(o,a){
	 document.location.href = ABSOLUTE_URL+"Products/index.php?ap="+destAP;
	}
 sh.sendCommand(cmd);
}

function moveToCatalog(destAP,li)
{
 var qry = "";
 if(SELECTED_IDS.length)
 {
  if(!confirm("Sei sicuro di voler spostare tutti gli articoli selezionati nel catalogo "+li.innerHTML+" ?"))
   return;
  for(var c=0; c < SELECTED_IDS.length; c++)
   qry+= " -id "+SELECTED_IDS[c];
 }
 else if(SELECTED_CAT_ID)
 {
  if(!confirm("Sei sicuro di voler spostare tutti gli articoli di questa categoria nel catalogo "+li.innerHTML+" ?"))
   return;
  qry+= " -cat "+SELECTED_CAT_ID;
 }
 else
  return alert("Devi selezionare almeno un articolo, oppure entrare in una categoria (se desideri spostarla tutta su un altro catalogo).");

 var cmd = "dynarc export -ap '"+AP+"'"+qry+" || dynarc import -ap '"+destAP+"' -xml *.xml";
 if(SELECTED_IDS.length)
  cmd+= " || dynarc delete-item -ap '"+AP+"'"+qry+" -r";
 else
  cmd+= " || dynarc delete-cat -ap '"+AP+"' -id '"+SELECTED_CAT_ID+"' -r";

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnPreOutput = function(){}
 sh.OnOutput = function(o,a){
	 document.location.href = ABSOLUTE_URL+"Products/index.php?ap="+destAP;
	}
 sh.sendCommand(cmd);
}

/* BSMCOMPAT FUNCTIONS */
function bsmcompatBrandChanged(sel)
{
 // reset serie select
 var serieSel = document.getElementById("bsmcompat-select-serie");
 while(serieSel.options.length > 1)
  serieSel.removeChild(serieSel.options[1]);

 // reset model select 
 var modelSel = document.getElementById("bsmcompat-select-model");
 while(modelSel.options.length > 1)
  modelSel.removeChild(modelSel.options[1]);

 if(!sel.value)
  return;

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
	  serieSel.appendChild(opt);
	 }
	}
 sh.sendCommand("dynarc cat-list -ap '"+AP+"_bsm' -parent '"+sel.value+"'");
}

function bsmcompatSerieChanged(sel)
{
 // reset model select 
 var modelSel = document.getElementById("bsmcompat-select-model");
 while(modelSel.options.length > 1)
  modelSel.removeChild(modelSel.options[1]);
 modelSel.options[0].innerHTML = "<i>attendere...</i>";

 if(!sel.value)
  return;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a || !a['items'])
	 {
	  modelSel.options[0].innerHTML = "<i>nessun risultato</i>";
	  return;
	 }
	 for(var c=0; c < a['items'].length; c++)
	 {
	  var opt = document.createElement('OPTION');
	  opt.value = a['items'][c]['id'];
	  opt.innerHTML = a['items'][c]['name'];
	  modelSel.appendChild(opt);
	 }
	 modelSel.options[0].innerHTML = "<i>seleziona un modello</i>";
	}
 sh.sendCommand("dynarc item-list -ap '"+AP+"_bsm' -cat '"+sel.value+"'");

}

function bsmcompatModelChanged(sel)
{
 if(!sel.value)
  return;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(a['link_ap'] && a['link_id'])
	  editItem(a['link_id'],a['link_ap'],AT);
	 else
	  alert("Articolo inesistente. LinkAp: "+a['link_ap']+", LinkId: "+a['link_id']);
	}
 sh.sendCommand("dynarc item-info -ap '"+AP+"_bsm' -id '"+sel.value+"'");
}

function showInStore()
{
 if(!SELECTED_IDS.length)
  return alert("Devi selezionare almeno un prodotto");

 if(!confirm("Sei sicuro di voler mostrare gli articoli selezionati in magazzino?"))
  return;

 var ids = "";

 for(var c=0; c < SELECTED_IDS.length; c++)
  ids+= ","+AP+":"+SELECTED_IDS[c];
 ids = ids.substr(1);


 var sh = new GShell();
 sh.OnError = function(msg){alert(msg);}
 sh.OnOutput = function(o,a){
	 unselectAll();
	 ITEMLISTIFRAME.document.location.reload();
	}
 sh.sendCommand("store showinstore -ids `"+ids+"`");
}

function hideInStore()
{
 if(!SELECTED_IDS.length)
  return alert("Devi selezionare almeno un prodotto");

 if(!confirm("Sei sicuro di voler nascondere gli articoli selezionati dal magazzino?"))
  return;

 var ids = "";

 for(var c=0; c < SELECTED_IDS.length; c++)
  ids+= ","+AP+":"+SELECTED_IDS[c];
 ids = ids.substr(1);


 var sh = new GShell();
 sh.OnError = function(msg){alert(msg);}
 sh.OnOutput = function(o,a){
	 unselectAll();
	 ITEMLISTIFRAME.document.location.reload();
	}
 sh.sendCommand("store hideinstore -ids `"+ids+"`");
}

// TRANSPONDER
function addToTransponderBasket(li, list)
{
 if(!SELECTED_IDS.length)
  return alert("Devi selezionare almeno un prodotto");
 
 if(li)
 {
  var list = new Array();
  var a = new Array();
  a['id'] = li.getAttribute('data-serverid');
  a['tag'] = li.getAttribute('data-servicetag');
  list.push(a);
 }
 else if(!list)
 {
  var sh = new GShell();
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,a){
	 if(!a) return;
	 return addToTransponderBasket(null, a);
	}

  sh.sendCommand("gframe -f transponder/select.server -params `tags="+TRANSPONDER_SERVICE_TAGS+"`");
  return;
 }


 var sh = new GShell();
 sh.showProcessMessage("Pubblicazione articoli", "Attendere prego, &egrave; in corso l&lsquo;inserimento degli articoli selezionati nel basket");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnFinish = function(){
	 this.hideProcessMessage();
	 document.location.reload();
	}

 for(var i=0; i < list.length; i++)
 {
  var serverId = list[i]['id'];
  var serviceTag = list[i]['tag'];

  for(var c=0; c < SELECTED_IDS.length; c++)
  {
   sh.sendCommand("transponder add-to-basket -refat gmart -refap '"+AP+"' -refid '"+SELECTED_IDS[c]+"' -serverid '"+serverId+"' -servicetag '"+serviceTag+"' -action sync-products");
  }
 }
 // reset environment variable 
 sh.sendCommand("export -var GMART_TRANSPONDER_BASKET_COUNT");

}

function addCatToTransponderBasket(li, list)
{
 if(li)
 {
  var list = new Array();
  var a = new Array();
  a['id'] = li.getAttribute('data-serverid');
  a['tag'] = li.getAttribute('data-servicetag');
  list.push(a);
 }
 else if(!list)
 {
  var sh = new GShell();
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,a){
	 if(!a) return;
	 return addCatToTransponderBasket(null, a);
	}

  sh.sendCommand("gframe -f transponder/select.server -params `tags="+TRANSPONDER_SERVICE_TAGS+"`");
  return;
 }


 var sh = new GShell();
 sh.showProcessMessage("Pubblicazione categoria", "Attendere prego, &egrave; in corso l&lsquo;inserimento di questa categoria nel basket");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnFinish = function(){
	 this.hideProcessMessage();
	 document.location.reload();
	}

 for(var i=0; i < list.length; i++)
 {
  var serverId = list[i]['id'];
  var serviceTag = list[i]['tag'];

  sh.sendCommand("transponder add-to-basket -refat gmart -refap '"+AP+"' -refcat '"+SELECTED_CAT_ID+"' -serverid '"+serverId+"' -servicetag '"+serviceTag+"' -action sync-products --include-subcat --include-items");

 }
 // reset environment variable 
 sh.sendCommand("export -var GMART_TRANSPONDER_BASKET_COUNT");
}

function transponderBasketPublish()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnFinish = function(o,a){
	 document.location.reload();
	}

 sh.sendCommand("export -var GMART_TRANSPONDER_BASKET_COUNT && gframe -f transponder/basket.publish -params `at=gmart`");
}

function publishOnAmazon(countryCode)
{
 if(!SELECTED_IDS.length)
  return alert("Devi selezionare almeno un prodotto");

 if(!confirm("I prodotti selezionati verranno pubblicati su Amazon. Desideri procedere?"))
  return;

 var ids = "";
 for(var c=0; c < SELECTED_IDS.length; c++)
  ids+= ","+SELECTED_IDS[c];
 ids = ids.substr(1);

 var sh = new GShell();
 sh.showProcessMessage("Pubblica su Amazon", "Attendere prego, &egrave; in corso il caricamento dei prodotti selezionati su Amazon");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(){
	 this.hideProcessMessage();
	 //unselectAll();
	 ITEMLISTIFRAME.document.location.reload();
	}
 sh.sendCommand("amazonmws publish -ap `"+AP+"` -ids `"+ids+"`"+(countryCode ? " -marketplace '"+countryCode+"'" : " --all-marketplace"));

}
</script>

</body></html>
<?php

