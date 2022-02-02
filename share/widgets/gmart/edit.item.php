<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 02-04-2017
 #PACKAGE: gmart
 #DESCRIPTION: Edit product form.
 #VERSION: 2.27beta
 #CHANGELOG: 02-04-2017 : Bug fix function: updateVariantOB. varianti - fasce prezzo x qta.
			 17-12-2016 : Listini prezzi, campo sconto.
			 24-11-2016 : Aggiunto campi SKU e SPID.
			 17-03-2016 : Bug fix titolo lungo.
			 24-01-2016 : Aggiunto tab Altro, con cronologia acquisti e vendite.
			 16-01-2016 : maxlength su campi marca e modello.
			 26-09-2015 : Aggiunto campo nascondi dal magazzino.
			 07-01-2015 : Bug fix su cartella immagini prodotti.
			 19-12-2014 : Aggiunta anteprima immagine con lightbox.
			 03-11-2014 : Bug fix su salvataggio tinte.
			 02-10-2014 : Possibilità di ricercare in tutta la rubrica (x integrazione con soci) sulla lista fornitori.
			 12-07-2014 : Possibilità di selezionare la categoria.
			 08-04-2014 : Bug fix sui valori predefiniti.
			 20-02-2014 : Aggiunta la scorta minima
			 31-01-2014 : Rimosso temporaneamente il barcode perchè su alcuni sbordava fuori.
			 31-01-2014 : Aggiunto barcode.
			 23-10-2013 : Aggiunto cod. e nome confezionamento e divisione materiale
			 19-09-2013 : Bug fix nei listini prezzi.
			 22-07-2013 : Aggiunto alert in caso di codici duplicati.
			 11-07-2013 : Aggiunto item_location per la collocazione degli articoli.
			 07-05-2013 : Aggiunto peso.
			 17-04-2013 : Aggiunto listini extra.
			 25-03-2013 : Aggiunto allegati
			 11-02-2013 : Integrazione con le varianti.
			 04-02-2013 : Nascosta temporaneamente la voce "articoli venduti".
			 31-01-2013 : Predisposizione per la scheda 'Prezzi imposti'
			 29-01-2013 : Bug fix sui listini prezzi.
			 28-01-2013 - Bug fix vari.
			 06-12-2012 : Bug fix vari.
 #DEPENDS: guploader, pricelists, gmutable
 #TODO: Da rimettere la voce "articoli venduti".

 CHANGELOG: aggiungere tra le dipendenze il pacchetto htmlgutility
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES, $_DECIMALS, $_PRICELISTS, $_FREQ_VAT_TYPE, $_FREQ_VAT_PERC, $_COMMERCIALDOCS_CONFIG;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/company-profile.php");
include_once($_BASE_PATH."etc/commercialdocs/config.php");
/* CHANGELOG: 05-11-2014 */
include_once($_BASE_PATH."var/objects/htmlgutility/optionbox.php");
include_once($_BASE_PATH."var/objects/htmlgutility/lightbox.php");
/* EOF - CHANGELOG */
include_once($_BASE_PATH."var/objects/gcal/index.php");

$id = $_REQUEST['id'];
$_ERROR_CODE = "";
$_SPID_TYPES = array('ASIN','EAN','GCID','GTIN','UPC');

/* GET DEFAULT PRICING AND FREQ.VAT USED FROM COMPANY PROFILE */
$_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'];

if($_COMPANY_PROFILE['accounting']['freq_vat_used'])
{
 $ret = GShell("dynarc item-info -ap vatrates -id `".$_COMPANY_PROFILE['accounting']['freq_vat_used']."` -get `vat_type,percentage`",$_REQUEST['sessid'],$_REQUEST['shellid']);
 $_FREQ_VAT_TYPE = $ret['outarr']['vat_type'];
 $_FREQ_VAT_PERC = $ret['outarr']['percentage'];
}

/* GET PRICELISTS */
$ret = GShell("pricelists list",$_REQUEST['sessid'],$_REQUEST['shellid']);
$_PRICELISTS = $ret['outarr'];

$get = "";
for($c=0; $c < count($_PRICELISTS); $c++)
{
 $pid = $_PRICELISTS[$c]['id'];
 $get.= ",pricelist_".$pid."_baseprice,pricelist_".$pid."_mrate,pricelist_".$pid."_vat,pricelist_".$pid."_vendorprice,pricelist_".$pid."_cm,pricelist_".$pid."_discount";
}

/* GET ARCHIVE INFO */
$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : "gmart";
$ret = GShell("dynarc archive-info -prefix '".$_AP."'",$_REQUEST['sessid'],$_REQUEST['shellid']);
$archiveInfo = $ret['outarr'];

/* GET PRICELIST COLUMNS */
if(!$archiveInfo['params']['pricelistcolumns'])
{
 // use default columns //
 $archiveInfo['params']['pricelistcolumns'] = "4,5,7,8,9";
}

 $columns = array();
 $columns[] = array(
	 "id"=>"vendorprice",
	 "width"=>80,
	 "title"=>"PR. LISTINO", 
	 "editable"=>true,
	 "format"=>"currency");

 $columns[] = array(
	 "id"=>"cm",
	 "width"=>40,
	 "title"=>"% C&M", 
	 "editable"=>true,
	 "format"=>"percentage");

 $columns[] = array(
	 "id"=>"costs",
	 "width"=>80,
	 "title"=>"COSTO", 
	 "editable"=>false,
	 "format"=>"currency");

 $columns[] = array(
	 "id"=>"costsvatincluded",
	 "width"=>80,
	 "title"=>"COSTO +IVA", 
	 "editable"=>false,
	 "format"=>"currency");

 $columns[] = array(
	 "id"=>"baseprice",
	 "width"=>80,
	 "title"=>"PREZZO BASE", 
	 "editable"=>true,
	 "format"=>"currency");

 $columns[] = array(
	 "id"=>"markuprate",
	 "width"=>70,
	 "title"=>"% RICARICO", 
	 "editable"=>true,
	 "format"=>"percentage");

 $columns[] = array(
	 "id"=>"discount",
	 "width"=>70,
	 "title"=>"% SCONTO", 
	 "editable"=>true,
	 "format"=>"percentage");

 $columns[] = array(
	 "id"=>"finalprice",
	 "width"=>80,
	 "title"=>"PREZZO FINALE", 
	 "editable"=>false,
	 "format"=>"currency");

 $columns[] = array(
	 "id"=>"vat",
	 "width"=>40,
	 "title"=>"% IVA", 
	 "editable"=>true,
	 "format"=>"percentage");

 $columns[] = array(
	 "id"=>"finalpricevatincluded",
	 "width"=>80,
	 "title"=>"PREZZO + IVA", 
	 "editable"=>false,
	 "format"=>"currency");


/* GET PRODUCT INFO */
/* CHANGELOG: 07-11-2014 - aggiunto pricesbyqty tra le estensioni */
$ret = GShell("dynarc item-info -ap `".$_AP."` -id `".$id."` -extget `gmart,thumbnails,coding,storeinfo,idoc,pricing,custompricing,vendorprices,variants,pricesbyqty,varcodes`"
	.($get ? " -get `".ltrim($get,",")."`" : ""),$_REQUEST['sessid'],$_REQUEST['shellid']);
if(!$ret['error'])
 $itemInfo = $ret['outarr'];
else
 $_ERROR_CODE = $ret['error'];

$_USE_DEFAULT_VALUES = !$itemInfo['mtime'] ? true : false;
/* EOF - CHANGELOG */

if($itemInfo['cat_id'])
{
 /* get cat info */
 $ret = GShell("dynarc cat-info -ap `".$_AP."` -id `".$itemInfo['cat_id']."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
 $catInfo = $ret['outarr'];
}

for($c=0; $c < count($itemInfo['idocs']); $c++)
{
 $ret = GShell("dynarc item-info -aid `".$itemInfo['idocs'][$c]['aid']."` -id `".$itemInfo['idocs'][$c]['id']."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
 $itemInfo['idocs'][$c]['name'] = $ret['outarr']['name'];
}

/* GET ATTACHMENTS */
$ret = GShell("dynattachments list -ap '".$_AP."' -refid ".$itemInfo['id'],$_REQUEST['sessid'],$_REQUEST['shellid']);
$itemInfo['attachments'] = $ret['outarr']['items'];

/* GET ACTIVE PRICELISTS */
$plListIDS = array();
if($itemInfo['pricelists'])
{
 if(strpos($itemInfo['pricelists'],",") !== false)
  $plListIDS = explode(",",$itemInfo['pricelists']);
 else
  $plListIDS = array(0=>$itemInfo['pricelists']);
}
for($c=0; $c < count($_PRICELISTS); $c++)
{
 if(!count($plListIDS) && !$_PRICELISTS[$c]['isextra'])
  $_PRICELISTS[$c]['enabled'] = true;
 else if(in_array($_PRICELISTS[$c]['id'],$plListIDS))
  $_PRICELISTS[$c]['enabled'] = true;
}

/* GET CUSTOMER AND VENDOR DOCTYPES */
$_VENDOR_DOCTYPES = array();
$_CUSTOMER_DOCTYPES = array();

$ret = GShell("dynarc cat-list -ap commercialdocs",$_REQUEST['sessid'],$_REQUEST['shellid']);
if(!$ret['error'])
{
 for($c=0; $c < count($ret['outarr']); $c++)
 {
  $data = $ret['outarr'][$c];
  switch($data['tag'])
  {
   case 'VENDORORDERS' : case 'PURCHASEINVOICES' : case 'DDTIN' : {
	 switch($data['tag'])
	 {
	  case 'DDTIN' : case 'PURCHASEINVOICES' : $data['crondef'] = true; break;
	  default : $data['crondef'] = false; break;
	 }
	 $_VENDOR_DOCTYPES[] = array('name'=>$data['name'], 'tag'=>$data['tag'], 'crondef'=>$data['crondef']);
	} break;

   case 'INVOICES' : case 'PREEMPTIVES' : case 'ORDERS' : case 'DDT' : case 'AGENTINVOICES' : case 'INTERVREPORTS' : case 'CREDITSNOTE' : case 'DEBITSNOTE' : case 'PAYMENTNOTICE' : case 'RECEIPTS' : case 'MEMBERINVOICES' : {
	 switch($data['tag'])
	 {
	  case 'DDT' : case 'INVOICES' : case 'RECEIPTS' : $data['crondef'] = true; break;
	  default : $data['crondef'] = false; break;
	 }
	 $_CUSTOMER_DOCTYPES[] = array('name'=>$data['name'], 'tag'=>$data['tag'], 'crondef'=>$data['crondef']);
	} break;
  }

 }
}

function fullescape($in)
{
 /*Thanks to omid@omidsakhi.com that his code gave me an idea. */
 /* Full escape function without % sign */
  $out = '';
  for ($i=0;$i<strlen($in);$i++)
  {
    $hex = dechex(ord($in[$i]));
    if ($hex=='')
       $out = $out.urlencode($in[$i]);
    else
       $out = $out.((strlen($hex)==1) ? ('0'.strtoupper($hex)):(strtoupper($hex)));
  }
  $out = str_replace('+','20',$out);
  $out = str_replace('_','5F',$out);
  $out = str_replace('.','2E',$out);
  $out = str_replace('-','2D',$out);
  return $out;
}

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Edit product</title>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>"; var USER_HOME = "<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR']; ?>/";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/layers.js"></script>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/edit-item.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/variant.css" type="text/css" />
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/editsearch/index.php");
include_once($_BASE_PATH."var/objects/gmutable/index.php");
include_once($_BASE_PATH."var/objects/htmlgutility/menu.php");
include_once($_BASE_PATH."var/objects/guploader/index.php");

$_ABBR_ART_TITLE = html_entity_decode($itemInfo['name']);
if(strlen($_ABBR_ART_TITLE) > 60) $_ABBR_ART_TITLE = substr($_ABBR_ART_TITLE, 0, 60)."...";

?>
</head><body>
<div class="edit-product-form">
 <!-- HEADER -->
 <table width='100%' cellspacing='0' cellpadding='0' border='0'>
 <tr><td width='600' height='25'><div class='header'>Articolo: <span class='itemcode'><?php echo $itemInfo['code_str']; ?></span> <span class='title'><?php echo $_ABBR_ART_TITLE; ?></span><?php if($_USE_DEFAULT_VALUES) echo " *"; ?></div></td>
	<td align='left' valign='middle'>
	 <div class='header-right'>
	  <select id='catid' style="width:200px;float:left;vertical-align:top;margin-left:30px">
	   <option value='0'></option>
	  <?php 
	   $ret = GShell("dynarc cat-list -ap '".$_AP."'".($catInfo && $catInfo['parent_id'] ? " -parent '".$catInfo['parent_id']."'" : ""),$_REQUEST['sessid'], $_REQUEST['shellid']);
	   $list = $ret['outarr'];
	   for($c=0; $c < count($list); $c++)
	    echo "<option value='".$list[$c]['id']."'".($itemInfo['cat_id'] == $list[$c]['id'] ? " selected='selected'>" : ">")
			.$list[$c]['name']."</option>";
	  ?>
	  </select>
	  <img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/magnifier.gif" style="cursor:pointer;margin-top:3px;" title="Sfoglia categorie" onclick="browseCategory()"/>
	 </div>
	</td>
 </tr>
 </table>
 <!-- EOF HEADER -->

 <table width='880' cellspacing='0' cellpadding='0' border='0' style="margin-top:20px;border-bottom: 1px solid #dadada;">
 <tr><td valign='top' width='700' style="padding-right:10px;">
	<!-- CONTENTS -->
	<ul class='maintab' style="margin-left:8px;">
	 <li class='selected'><span class='title' onclick="showPage('details',this)">DETTAGLI</span></li>
	 <li><span class='title' onclick="showPage('extended',this)">SCHEDE</span></li>
	 <li><span class='title' onclick="showPage('attachments',this)">ALLEGATI</span></li>
	 <li><span class='title' onclick="showPage('vendors',this)">FORNITORI</span></li>
	 <li><span class='title' onclick="showPage('pricelists',this)">LISTINI PREZZI</span></li>
     <li><span class='title' onclick="showPage('custompricing',this)">PREZZI IMPOSTI</span></li>
	 <li><span class='title' onclick="showPage('variant',this)">VARIANTI</span></li>
	 <li><span class='title' onclick="showPage('other',this)">ALTRO</span></li>
	</ul>

	<!-- DETAILS PAGE -->
	<div class='tabpage' id="details-page">
	 <table width='100%' height='200' border='0' cellspacing='0' cellpadding='0'>
	  <tr><td valign='top' width='200'>
		   <ul class='thumb-buttons' id='thumb-buttons'>
			<?php
			for($c=0; $c < count($itemInfo['thumbnails']); $c++)
			{
			 $fileName = $itemInfo['thumbnails'][$c];
			 echo "<li onclick='selectThumb(this)' id='".fullescape($fileName)."'";
			 $x = strpos($fileName, ".", strlen($fileName)-5);
			 if($x > 0)
			 {
			  $ext = substr($fileName, $x+1);
			  $thumb = substr($fileName, 0, $x)."-thumb.".$ext;
			 }
			 else
			  $thumb = $fileName."-thumb";
 			 if($thumb && file_exists($_BASE_PATH.$thumb))
			  echo " icon='".fullescape($thumb)."'";
			 echo ($c==0 ? " class='selected'>" : ">").($c+1)."</li>";
			}

		    $thumb = "share/widgets/gmart/img/photo.png";
			if($itemInfo['thumbnails'][0])
			{
			 $fileName = $itemInfo['thumbnails'][0];
			 $x = strpos($fileName, ".", strlen($fileName)-5);
			 if($x > 0)
			 {
			  $ext = substr($fileName, $x+1);
			  $thumb = substr($fileName, 0, $x)."-thumb.".$ext;
			 }
			 else
			  $thumb = $fileName."-thumb";
 			 if(!file_exists($_BASE_PATH.$thumb))
			  $thumb = $fileName;
			}

			?>
		   </ul>
		   <div class='thumb-preview' id='thumb-preview' filename="<?php echo $fileName; ?>" style="background-image: url(<?php echo $_ABSOLUTE_URL.$thumb; ?>);" onclick="showImagePreview(this)">
			<img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/icon_delete.gif" class="delete-button" onclick="deleteSelectedThumb(event)"/>
		   </div>

		   <ul class='basicbuttons' style="clear:both;float:left;margin-top:5px;margin-left:22px;">
  			<li><span onclick="uploadImage()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/add.gif" border='0'/>Carica immagine</span></li>
 		   </ul>

		  <!-- <div class='barcode'>
		  <?php
		  if(file_exists($_BASE_PATH."share/gd/barcode.php"))
		  {
		   echo "<img id='barcode-img' src='".$_ABSOLUTE_URL."share/gd/barcode.php?barcode=".$itemInfo['barcode']."'"
			.(!$itemInfo['barcode'] ? " style='visibility:hidden'" : "")."/>";
		  }
		  ?>
		  </div> -->

		  <div style="clear:both;height:20px;border-bottom:1px solid #dadada"></div>
		  <div style="clear:both;padding-top:10px">
		   <table width='100%' cellspacing='2' cellpadding='0' border='0'>
		   <tr><td width='70' valign='middle'><span class='smallh3'>SKU: </span></td>
			   <td><input type='text' class='edit' style='width:110px' maxlength='40' id='sku' value="<?php echo $itemInfo['sku']; ?>"/></td></tr>

		   <tr><td><select style='width:70px' id='spid_type'>
				<?php
				 for($c=0; $c < count($_SPID_TYPES); $c++)
				  echo "<option value='".$_SPID_TYPES[$c]."'"
					.(($itemInfo['spid_type'] == $_SPID_TYPES[$c]) ? " selected='selected'>" : ">").$_SPID_TYPES[$c]."</option>";
				?>
		   		</select></td>
			   <td><input type='text' class='edit' style='width:110px' maxlength='40' id='spid_code' value="<?php echo $itemInfo['spid_code']; ?>"/></td></tr>
		   </table>
		  </div>

		  </td>
		  <td valign='top' style="border-left:1px solid #dadada;padding-left:10px;">
			<!-- INFO -->
			<table border='0' class='infotableform'>
			 <tr><td class='field'>CODICE:</td>
				 <td class='value'><input type='text' id='item_code' style='width:130px;' value="<?php echo $itemInfo['code_str']; ?>" onchange="codeCheck(this)"/></td></tr>
			 <tr><td class='field'>MARCA:</td>
				 <td class='value'><input type='text' id='item_brand' style='width:150px;' refid="<?php echo $itemInfo['brand_id']; ?>" value="<?php echo $itemInfo['brand']; ?>" maxlength='32'/></td></tr>
			 <tr><td class='field'>MODELLO:</td>
				 <td class='value'><input type='text' id='item_model' style='width:250px;' value='<?php echo $itemInfo['model']; ?>' maxlength='64'/></td></tr>
			 <tr><td class="field small">PREZZO DI BASE:</td>
				 <td class='value' style='font-size:10px;'><input type='text' id='item_baseprice' style='width:70px;' value="<?php echo number_format($itemInfo['baseprice'],$_DECIMALS,',','.'); ?>" onchange="_basepriceChange(this)"/> &euro; 
					<span class="field small" style="margin-left:60px">Unit&agrave; di Misura: <input type='text' id='item_units' style='width:30px;' value="<?php echo $itemInfo['units'] ? $itemInfo['units'] : 'PZ'; ?>"/></td></tr>
			 <tr><td class="field small">I.V.A:</td>
				 <td class='value' style='font-size:10px;'><input type='text' id='item_vat' style='width:50px;' value="<?php echo $itemInfo['vat'] ? $itemInfo['vat'] : ($_USE_DEFAULT_VALUES ? $_FREQ_VAT_PERC : 0); ?>" onchange="_vatChange(this)"/> %
					<span class="field small" style="margin-left:60px">Peso: <input type='text' id='item_weight' style='width:40px' value="<?php echo $itemInfo['weight']; ?>"/> <select id='item_weight_units' style='width:50px'>
						   <?php
							if(!$itemInfo['weightunits'])
							 $itemInfo['weightunits'] = "kg";
							$weightunits = array(
								"mg"=>"mg &nbsp;&nbsp;&nbsp;&nbsp;(milligrammi)",
								"g"=>"g &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(grammi)",
								"hg"=>"h &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(etti / 100g)",
								"kg"=>"Kg &nbsp;&nbsp;&nbsp;&nbsp;(kilogrammi)",
								"q"=>"q &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(quintali)",
								"t"=>"t &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(tonnellate)");
							while(list($k,$v) = each($weightunits))
							{
							 echo "<option value='".$k."'".($itemInfo['weightunits'] == $k ? " selected='selected'>" : ">").$v."</option>";
							}
						   ?>
						  </select></td></tr>
			 <tr><td class="field small">BARCODE:</td>
				 <td class='value'><input type='text' id='item_barcode' style='width:150px;' value="<?php echo $itemInfo['barcode']; ?>" onchange="barcodeUpdate(this)"/></td></tr>
			 <tr><td class="field small lightblue">COD.ART. PROD.</td>
				 <td class='value'><input type='text' id='item_manufcode' style='width:150px;' value="<?php echo $itemInfo['manufacturer_code']; ?>"/></td></tr>
			 <tr><td class="field small lightblue">COLLOCAZ. ART.</td>
				 <td class='value'><input type='text' id='item_location' style='width:150px;' value="<?php echo $itemInfo['item_location']; ?>"/></td></tr>
			 <tr><td class="field small lightblue">CONFEZIONAMENTO</td>
				 <td class='value'><span class="field small">cod.</span> <input type='text' id='gebinde_code' style='width:50px;' value="<?php echo $itemInfo['gebinde_code']; ?>"/> <span class="field small">descr:</span> <input type='text' id='gebinde' style='width:100px;' value="<?php echo $itemInfo['gebinde']; ?>"/></td></tr>
			 <tr><td class="field small lightblue">DIVISIONE MAT.</td>
				 <td class='value'><select id='division' style='width:150px'><option value=''>&nbsp;</option>
				 <?php
				 if(isset($_COMMERCIALDOCS_CONFIG['DIVISION']))
				 {
				  reset($_COMMERCIALDOCS_CONFIG['DIVISION']);
				  while(list($k,$v) = each($_COMMERCIALDOCS_CONFIG['DIVISION']))
				  {
				   echo "<option value='".$k."'".($k == $itemInfo['division'] ? " selected='selected'>" : ">").$v."</option>";
				  }
				 }
				 ?></select></td></tr>
			</table>
			<!-- EOF INFO -->

			<span class='smallh3'>BREVE DESCRIZIONE</span><br/>
			<textarea class="item-description" id="item_description"><?php echo $itemInfo['desc']; ?></textarea>

		  </td></tr>
	 </table>

	</div>
	<!-- EOF DETAILS PAGE -->

	<!-- EXTENDED DETAILS PAGE -->
	<div class='tabpage' id="extended-page" style="display:none;">
	 <ul class='bluenuv' id='idoc-bluenuv' style='float:left;'>
	 <?php
	 for($c=0; $c < count($itemInfo['idocs']); $c++)
	 {
	  echo "<li".($c==0 ? " class='selected'" : "")." id='idoc-".$itemInfo['idocs'][$c]['aid']."_".$itemInfo['idocs'][$c]['id']."' onclick='idocShow(this)'><img src='".$_ABSOLUTE_URL."share/widgets/gmart/img/delete-btn.gif' title='Elimina scheda' onclick='idocRemove(this)'/><span>".$itemInfo['idocs'][$c]['name']."</span></li>";
	 }
	 ?>
	 </ul>
	 <span class='link-green' style='float:left;' onclick="idocAdd()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/add-btn.png"/> Aggiungi</span>
	 <?php
	 for($c=0; $c < count($itemInfo['idocs']); $c++)
	 {
	  echo "<div class='idocpage' id='idocspace-".$itemInfo['idocs'][$c]['aid']."_".$itemInfo['idocs'][$c]['id']."'".($c==0 ? ">" : " style='display:none;'>")."</div>";
	 }
	 ?>
	</div>
	<!-- EOF EXTENDED DETAILS PAGE -->

	<!-- ATTACHMENTS PAGE -->
	<div class='tabpage' id="attachments-page" style="display:none;">
	 	<div class='attachments-toolbar'>
		 <table border='0' cellspacing='0' cellpadding='0' width='680' height='40'>
		  <tr><td width='120' style='padding-left:10px'><span class='smallblue'>Carica un file dal PC</span></td>
			 <td><div id='gupldspace'></div></td>
			 <td width='40' class='attachments-tb-buttons'><a href='#' onclick='selectFromServer("<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR']."/"; ?>")'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/load-from-server.png" border="0" title="Carica dal server"/></a></td>
			 <td width='40' class='attachments-tb-buttons'><a href='#' onclick='insertFromURL()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/link.png" border="0" title="Inserisci un link da URL"/></a></td>
		  </tr>
		 </table>
	    </div>
		<div id='attachments-explore' class='attachments-explore'>
		 <?php
		 /* LIST OF ATTACHMENTS */
		 if(!$ret['error'])
		 {
		  for($c=0; $c < count($itemInfo['attachments']); $c++)
		  {
		   $item = $itemInfo['attachments'][$c];
		   echo "<div class='attachment' id='attachment-".$item['id']."'>";
		   echo "<a href='#' class='btnedit' onclick='editAttachment(".$item['id'].")' title='Modifica'><img src='".$_ABSOLUTE_URL."share/widgets/gmart/img/edit_small.png' border='0'/></a>";
		   echo "<a href='#' class='btndel' onclick='deleteAttachment(".$item['id'].")' title='Rimuovi'><img src='".$_ABSOLUTE_URL."share/widgets/gmart/img/delete_small.png' border='0'/></a>";
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
	</div>
	<!-- EOF ATTACHMENTS PAGE -->

	<!-- VENDORS PAGE -->
	<div class='tabpage' id="vendors-page" style="display:none;">
	 <h3 class="orangebar">FORNITORI</h3>
	 <div class="gmutable" style="width:686px;height:300px;margin-left:10px;background:#ffffff;border:0px;">
	 <table id="vendors-table" class="pricelists" width="100%" cellspacing="2" cellpadding="2" border="0">
	 <tr><th width='20'><input type="checkbox" onchange="VENDORSTB.selectAll(this.checked)"/></th>
		 <th width='60' id='code' editable='true' style="text-align:left;">COD.ART</th>
		 <th id='vendor' editable='true'>FORNITORE</th>
		 <th width='70' id='shipcosts' editable='true' format='currency'>SP. SPEDIZ.</th>
		 <th width='80' id='price' editable='true' format='currency' decimals="<?php echo $_DECIMALS; ?>">PREZZO</th>
		 <th width='40' id='vatrate' editable='true' format='percentage'>% IVA</th>
		 <th width='80' id='total'>PREZZO + IVA</th></tr>

	 <?php
	 $list = $itemInfo['vendorprices'];
	 for($c=0; $c < count($list); $c++)
	 {
	  $vat = $list[$c]['vatrate'];
	  $price = $list[$c]['price'];
	  $shipCosts = $list[$c]['shipcosts'];
	  $total = $price+$shipCosts;
	  $PriceVI = $total ? $total + (($total/100)*$vat) : 0;

	  echo "<tr id='vendorprice-".$list[$c]['id']."'>";
	  echo "<td align='center'><input type='checkbox'/></td>";
	  echo "<td align='center'>".$list[$c]['code']."</td>";
	  echo "<td>".$list[$c]['vendor_name']."</td>";
	  echo "<td align='right'>".number_format($shipCosts,2,",",".")."</td>";
	  echo "<td align='right'>".number_format($price,$_DECIMALS,",",".")."</td>";
	  echo "<td align='center'>".($vat ? $vat."%" : "0%")."</td>";
	  echo "<td align='right'>".number_format($PriceVI,$_DECIMALS,",",".")."</td></tr>";
	 }
	 ?>
	 </table>
	 <div class="pricelists-table-footer">
	  <span class='btn-add' onclick="addVendor()">Aggiungi fornitore</span>
	  <span class='btn-del' onclick="deleteSelectedVendors()">Rimuovi selezionati</span>
	 </div>
	 </div>
	</div>
	<!-- EOF VENDORS PAGE -->

	<!-- PRICE LISTS PAGE -->
	<div class='tabpage' id="pricelists-page" style="display:none;">
	 <h3 class="orangebar">LISTINI PREZZI DI RIVENDITA 
	  <img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/edit_small.png" style="float:right;margin-right:5px;margin-top:2px;cursor:pointer" title="Personalizza" onclick="customizePricelists()"/>
	 </h3>
	 <div class="gmutable" style="width:686px;height:300px;margin-left:10px;background:#ffffff;border:0px;">
	 <table id="pricelists-table" class="pricelists" width="100%" cellspacing="2" cellpadding="2" border="0">
	 <tr><th width='20'>INC.</th>
		 <th id='pricelistname' style="text-align:left;">LISTINO</th>
		 <?php
		 /* GET COLUMNS */
		 $x = explode(",",$archiveInfo['params']['pricelistcolumns']);
		 for($c=0; $c < count($columns); $c++)
		 {
		  $col = $columns[$c];
		  $selected = in_array($c,$x);
		  echo "<th width='".$col['width']."' id='".$col['id']."'"
			.($col['editable'] ? " editable='true' format='".$col['format']."'" : "")
			.(!$selected ? " style='display:none'" : "").">".$col['title']."</th>";
		 }
		 ?>
	  </tr>

	 <?php
	 $list = $_PRICELISTS;
	 $row = 0;
	 $starImg = $_ABSOLUTE_URL."share/widgets/gmart/img/star.png";
	 for($c=0; $c < count($list); $c++)
	 {
	  // get costs //
	  $vendorprice = isset($itemInfo["pricelist_".$list[$c]['id']."_vendorprice"]) ? $itemInfo["pricelist_".$list[$c]['id']."_vendorprice"] : 0;
	  $cm = isset($itemInfo["pricelist_".$list[$c]['id']."_cm"]) ? $itemInfo["pricelist_".$list[$c]['id']."_cm"] : 0;
	  $costs = $vendorprice ? ($vendorprice - (($vendorprice/100)*$cm)) : 0;

	  $vat = $itemInfo["pricelist_".$list[$c]['id']."_vat"] ? $itemInfo["pricelist_".$list[$c]['id']."_vat"] : ($_USE_DEFAULT_VALUES ? $list[$c]['vat'] : 0);

	  $costsVatIncluded = $costs ? ($costs + (($costs/100)*$vat)) : 0;

	  $baseprice = $itemInfo["pricelist_".$list[$c]['id']."_baseprice"] ? $itemInfo["pricelist_".$list[$c]['id']."_baseprice"] : ($_USE_DEFAULT_VALUES ? $itemInfo['baseprice'] : 0);
	  $markuprate = $itemInfo["pricelist_".$list[$c]['id']."_mrate"] ? $itemInfo["pricelist_".$list[$c]['id']."_mrate"] : ($_USE_DEFAULT_VALUES ? $list[$c]['markuprate'] : 0);

	  $discount = $itemInfo["pricelist_".$list[$c]['id']."_discount"] ? $itemInfo["pricelist_".$list[$c]['id']."_discount"] : ($_USE_DEFAULT_VALUES ? $list[$c]['discount'] : 0);

	  $finalPrice = $baseprice ? $baseprice + (($baseprice/100)*$markuprate) : 0;
	  $finalPrice = $finalPrice ? $finalPrice - (($finalPrice/100)*$discount) : 0;
	  $finalPriceVI = $finalPrice ? $finalPrice + (($finalPrice/100)*$vat) : 0;

	  echo "<tr id='pricelist-".$list[$c]['id']."' class='".($list[$c]['enabled'] ? 'row'.$row : 'unselected')."'>";
	  echo "<td><input type='checkbox'".($list[$c]['enabled'] ? " checked='true'" : "")." onclick='pricelistCheckChange(this)'/></td>";
	  echo "<td>".($list[$c]['isextra'] ? "<img src='".$starImg."' title='Listino extra'/> " : "").$list[$c]['name']."</td>";

	  for($i=0; $i < count($columns); $i++)
	  {
	   switch($columns[$i]['id'])
	   {
		case 'vendorprice' : echo "<td align='right'>".number_format($vendorprice,$_DECIMALS,",",".")."</td>"; break;
		case 'cm' : echo "<td align='center'>".($cm ? $cm."%" : "0%")."</td>"; break;
		case 'costs' : echo "<td align='right'><em>&euro;</em>".number_format($costs,$_DECIMALS,",",".")."</td>"; break;
		case 'costsvatincluded' : echo "<td align='right'><em>&euro;</em>".number_format($costsVatIncluded,$_DECIMALS,",",".")."</td>"; break;
		case 'baseprice' : echo "<td align='right'>".number_format($baseprice,$_DECIMALS,",",".")."</td>"; break;
		case 'markuprate' : echo "<td align='center'>".($markuprate ? $markuprate."%" : "0%")."</td>"; break;
		case 'discount' : echo "<td align='center'>".($discount ? $discount."%" : "0%")."</td>"; break;
		case 'finalprice' : echo "<td align='right'><em>&euro;</em>".number_format($finalPrice,$_DECIMALS,",",".")."</td>"; break;
		case 'vat' : echo "<td align='center'>".($vat ? $vat."%" : "0%")."</td>"; break;
		case 'finalpricevatincluded' : echo "<td align='right'><em>&euro;</em>".number_format($finalPriceVI,$_DECIMALS,",",".")."</td></tr>"; break;
	   }
	  }

	  echo "</tr>";
	  $row = $row ? 0 : 1;
	 }
	 ?>
	 </table>
	 </div>
	</div>
	<!-- EOF PRICE LISTS PAGE -->

	<!-- CUSTOM PRICING PAGE -->
	<div class='tabpage' id="custompricing-page" style="display:none;">
	 <h3 class="orangebar">PREZZI IMPOSTI PER CLIENTE</h3>
	 <div class="gmutable" style="width:686px;height:300px;margin-left:10px;background:#ffffff;border:0px;">
	 <table id="custompricing-table" class="pricelists" width="100%" cellspacing="2" cellpadding="2" border="0">
	 <tr><th width='20'><input type="checkbox" onchange="CPTB.selectAll(this.checked)"/></th>
		 <th id='subject' editable='true' style="text-align:left;">CLIENTE</th>
		 <th width='80' id='baseprice' editable='true' format='currency' decimals="<?php echo $_DECIMALS; ?>">PREZZO BASE</th>
		 <th width='70' id='discount' editable='true' format="currency percentage">SCONTO</th>
		 <th width='80' id='finalprice'>PREZZO FINALE</th>
		 <th width='40' id='vat' format='percentage'>% IVA</th>
		 <th width='80' id='finalpricevatincluded'>PREZZO + IVA</th></tr>

	 <?php
	 $list = $itemInfo['custompricing'];
	 $row = 0;
	 for($c=0; $c < count($list); $c++)
	 {
	  $baseprice = $list[$c]['baseprice'];
	  $discountPerc = $list[$c]['discount_perc'];
	  $discountInc = $list[$c]['discount_inc'];
	  $vat = $itemInfo["vat"];

	  echo "<tr id='custompricing-".$list[$c]['id']."' class='row".$row."'>";
	  echo "<td align='center'><input type='checkbox'/></td>";
	  echo "<td>".$list[$c]['subject_name']."</td>";
	  echo "<td align='right'>".number_format($baseprice,$_DECIMALS,",",".")."</td>";
	  if($discountInc)
	   echo "<td align='center'>".number_format($discountInc,$_DECIMALS,",",".")."</td>";
	  else
	   echo "<td align='center'>".($discountPerc ? $discountPerc."%" : "0%")."</td>";
	  if($discountInc)
	   $finalPrice = $baseprice ? $baseprice - $discountInc : 0;
	  else
	   $finalPrice = $baseprice ? $baseprice - (($baseprice/100)*$discountPerc) : 0;
	  echo "<td align='right'><em>&euro;</em>".number_format($finalPrice,$_DECIMALS,",",".")."</td>";
	  echo "<td align='center'>".($vat ? $vat."%" : "0%")."</td>";
	  $finalPriceVI = $finalPrice ? $finalPrice + (($finalPrice/100)*$vat) : 0;
	  echo "<td align='right'><em>&euro;</em>".number_format($finalPriceVI,$_DECIMALS,",",".")."</td></tr>";
	  $row = $row ? 0 : 1;
	 }
	 ?>
	 </table>
	 <div class="pricelists-table-footer">
	  <span class='btn-add' onclick="addCP()">Aggiungi</span>
	  <span class='btn-del' onclick="deleteSelectedCP()">Rimuovi selezionati</span>
	 </div>
	 </div>
	</div>
	<!-- EOF CUSTOM PRICING PAGE -->

	<!-- VARIANT PAGE -->
	<div class='tabpage' id="variant-page" style="display:none;">
	 <div class="variant-page">
	  <table width="100%" cellspacing="0" cellpadding="0" border="0">
	  <tr><td valign="top" width="165">

			<ul id="variant-menu">
			 <li class="selected" onclick="showVariantPage('colors',this)">Colori</li>
			 <li onclick="showVariantPage('tint',this)">Tinte / fantasie</li>
			 <li onclick="showVariantPage('sizes',this)">Taglie</li>
			 <li onclick="showVariantPage('dim',this)">Misure</li>
			 <li onclick="showVariantPage('other',this)">Altro...</li>
			 <!-- CHANGELOG: 05-11-2014 -->
			 <li onclick="showVariantPage('codesandprices',this)" style="margin-top:160px;position:relative"><img src="img/variant-barcode.png" width='24' style='position:absolute;left:3px;top:-5px'/><small style='margin-left:16px;font-size:12px;'><b>Codici e Prezzi</b></small></li>
			 <li onclick="showVariantPage('pricesbyqty',this)" style="margin-top:10px;position:relative"><img src="img/balance.png" width='24' style='position:absolute;left:3px;top:-5px'/><small style='margin-left:16px;font-size:12px;'><b>Fasce prezzo x qt&agrave;</b></small></li>
			 <!-- EOF - CHANGELOG -->
			</ul>

		  </td><td valign="top">

			<!-- COLORS - PAGE -->
			<div id="variant-colors-page">
			 <div class="variant-page-header"><table width="100%" cellspacing="0" cellpadding="0" border="0">
			  <tr><td class="title"><i>Colori disponibili</i></td>	<td>&nbsp;</td></tr> </table>
			 </div>

			 <div id="variant-colors-container">
			 <?php
			  for($c=0; $c < count($itemInfo['variants']['colors']); $c++)
			  {
			   $color = $itemInfo['variants']['colors'][$c];
			   echo "<div class='coloritem'>";
			   echo "<a href='#' class='deletecolor' onclick='deleteVariantColor(this)'><img src='".
					$_ABSOLUTE_URL."share/widgets/gmart/img/small-delete.png' title='Elimina'/></a>";
			   echo "<div class='colorblock' style='background:".$color['hexvalue']."' onclick='renameVariantColor(this)' title='Clicca per rinominare'>&nbsp;</div>";
			   echo "<div class='colortitle' onclick='renameVariantColor(this)' title='Clicca per rinominare'>".$color['name']."</div>";
			   echo "</div>";
			  }
			 ?>
			 </div>

			 <table width="100%" cellspacing="0" cellpadding="0" border="0">
			 <tr><td valign="middle"><span class="smallgray"><i>Clicca su un colore per aggiungerlo alla lista</i></span></td>
				 <td><div class="variant-colors-palette">
					 <?php
					 $palette = array("Bianco"=>"#ffffff", "Azzurro"=>"#19aeff", "Blu scuro"=>"#005c94", "Rosso"=>"#ff0000", "Giallo"=>"#ffff3e",
						"Arancione"=>"#ff6600", "Castano scuro"=>"#804d00", "Verde chiaro"=>"#9ade00", "Rosa chiaro"=>"#f1caff", 
						"Viola"=>"#ba00ff", "Metallo chiaro"=>"#9eabb0", "Grigio chiaro"=>"#cccccc", "Nero"=>"#000000", "Oro"=>"#e0c03c",
						"Panna"=>"#f9f9f9", "Blu"=>"#0000ff", "Rosa"=>"#ff8080", "Rosso scuro"=>"#b50000", "Mandarino"=>"#ff9900",
						"Castano chiaro"=>"#b88100", "Verde oliva"=>"#556b2f", "Verde"=>"#009100", "Viola chiaro"=>"#d76cff",
						"Metallo"=>"#bdcdd4", "Metallo scuro"=>"#364e59", "Grigio"=>"#999999", "Grigio scuro"=>"#666666", "Argento"=>"#f2f2f2");

					 //ksort($palette);
					 while(list($title,$bgColor) = each($palette))
					 {
					  echo "<div class='colorpal' style='background:".$bgColor."' title='".$title."' onclick='addVariantColor(this)'>&nbsp;</div>";
					 }
					 ?>
					 </div></td></tr>
			 </table>

			</div>
			<!-- EOF - COLORS - PAGE -->

			<!-- TINT - PAGE -->
			<div id="variant-tint-page" style="display:none">
			 <div class="variant-page-header">
			  <table width="100%" cellspacing="0" cellpadding="0" border="0">
			  <tr><td class="title"><i>Elenco delle tinte / fantasie</i></td>	
				  <td width='32'><a href='#' title="Aggiungi" onclick="addVariantTint()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/add-btn-green.png" border="0"/></a></td></tr>
 			  </table>
			 </div>

			 <div style="height:320px;overflow:auto;padding-left:10px">
			 <table id="variant-tint-container" width="100%" cellspacing="0" cellpadding="0" border="0">
			 <?php
			 for($c=0; $c < count($itemInfo['variants']['tint']); $c++)
			 {
			  $tint = $itemInfo['variants']['tint'][$c];
			  echo "<tr><td width='60'>";
			  echo "<div class='tint-thumb' title=\"Clicca per modificare l'immagine\" onclick='changeTintImg(this)'>";
			  echo "<div class='tint-thumb-img' style='background-image:url(".$_ABSOLUTE_URL.$tint['thumb'].")'>&nbsp;</div>";
			  echo "</div></td>";
			  echo "<td class='tint-title' onclick='renameVariantTint(this)'>".$tint['name']."</td>";
			  echo "<td width='60' align='center'><a href='#' title='Elimina' class='tint-delete' onclick='deleteVariantTint(this)'><img src='"
				.$_ABSOLUTE_URL."share/widgets/gmart/img/red_delete.png' border='0'/></a></td></tr>";
			 }
			 ?>
			 </table>
			 </div>

			</div>
			<!-- EOF - TINT - PAGE -->

			<!-- SIZES - PAGE -->
			<div id="variant-sizes-page" style="display:none">
			 <div class="variant-page-header">
			  <table width="100%" cellspacing="0" cellpadding="0" border="0">
			  <tr><td class="title"><i>Taglie disponibili</i></td>	
				  <td align="right"><span class="smallgray"><i>Tipologia:</i></span> 
					<select id="variant-sizes-available" style="width:146px" onchange="variantSizeChoice(this)">
						<option value=""></option>
						<?php
						$ret = GShell("dynarc exec-func ext:variants.getSizeTypes",$_REQUEST['sessid'],$_REQUEST['shellid']);
						$list = $ret['outarr'];
						for($c=0; $c < count($list); $c++)
						{
						 if($itemInfo['variants']['sizid'] == $list[$c]['id'])
						  $sizeTypeInfo = $list[$c];
						 echo "<option value='".$list[$c]['id']."'".($itemInfo['variants']['sizid'] == $list[$c]['id'] ? " selected='selected'>" : ">")
							.$list[$c]['name']."</option>";
						}
						?>
					</select></td></tr>
			  </table>
			 </div>

			 <div id="variant-sizes-container">
			 <?php
			 if($sizeTypeInfo)
			 {
			  $tmp = $itemInfo['variants']['sizes'];
			  for($c=0; $c < count($sizeTypeInfo['items']); $c++)
			  {
			   for($i=0; $i < count($tmp); $i++)
			   {
				if($sizeTypeInfo['items'][$c]['name'] == $tmp[$i]['name'])
				{
				 $sizeTypeInfo['items'][$c]['checked'] = true;
				 array_splice($tmp, $i, 1);
				 break;
				}
			   }
 			  }

			  for($c=0; $c < count($sizeTypeInfo['items']); $c++)
			  {
			   echo "<div class='sizeitem'>";
			   echo "<a href='#' class='deletesize' onclick='deleteVariantSize(this)'><img src='"
				.$_ABSOLUTE_URL."share/widgets/gmart/img/small-delete.png' title='Elimina'/></a>";
			   echo "<div class='sizetitle' onclick='renameVariantSize(this)'>".$sizeTypeInfo['items'][$c]['name']."</div>";
			   echo "<div class='sizecheck'><input type='checkbox'".($sizeTypeInfo['items'][$c]['checked'] ? " checked='true'" : "")."/></div>";
			   echo "</div>";
			  }
			  for($c=0; $c < count($tmp); $c++)
			  {
			   echo "<div class='sizeitem'>";
			   echo "<a href='#' class='deletesize' onclick='deleteVariantSize(this)'><img src='"
				.$_ABSOLUTE_URL."share/widgets/gmart/img/small-delete.png' title='Elimina'/></a>";
			   echo "<div class='sizetitle' onclick='renameVariantSize(this)'>".$tmp[$c]['name']."</div>";
			   echo "<div class='sizecheck'><input type='checkbox' checked='true'/></div>";
			   echo "</div>";
			  }		  
			 }
			 else if(count($itemInfo['variants']['sizes']))
			 {
			  for($c=0; $c < count($itemInfo['variants']['sizes']); $c++)
			  {
			   echo "<div class='sizeitem'>";
			   echo "<a href='#' class='deletesize' onclick='deleteVariantSize(this)'><img src='"
				.$_ABSOLUTE_URL."share/widgets/gmart/img/small-delete.png' title='Elimina'/></a>";
			   echo "<div class='sizetitle' onclick='renameVariantSize(this)'>".$itemInfo['variants']['sizes'][$c]['name']."</div>";
			   echo "<div class='sizecheck'><input type='checkbox' checked='true'/></div>";
			   echo "</div>";
			  }		  
			 }
			 ?>
			 </div>

			<div style="text-align:center;margin-top:10px"><span class='smallgray'><i>
			Scegli la tipologia di taglia e poi seleziona le misure disponibili.<br/>Nel caso mancasse qualche misura poi aggiungerla manualmente,<br/>
			oppure configurare a piacimento le tipologie disponibili o crearne di nuove.</i>
			</span></div>
			 <div class="variant-sizes-footer">
				<a href='#' class="smalllink" style="float:left;text-decoration:none" onclick="addVariantSize()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/add-blue.png"/>Aggiungi misura</a>
				<a href='#' onclick="deleteVariantSizeType()" class="smalllink" style="float:right">Elimina</a>
				<a href='#' onclick="saveAsVariantSizeType()" class="smalllink" style="float:right">Salva come...</a>
				<a href='#' onclick="saveVariantSizeType()" class="smalllink" style="float:right">Salva</a>
				<a href='#' onclick="renameVariantSizeType()" class="smalllink" style="float:right">Rinomina</a>
			 </div>
			</div>
			<!-- EOF - SIZES - PAGE -->

			<!-- DIM - PAGE -->
			<div id="variant-dim-page" style="display:none">
			 <div class="variant-page-header">
			  <table width="100%" cellspacing="0" cellpadding="0" border="0">
			  <tr><td class="title"><i>Misure disponibili</i></td>	
				  <td align="right"><span class="smallgray"><i>Standard:</i></span> 
					<select id="variant-dim-available" style="width:146px" onchange='variantDimChoice(this)'>
						<option value=""></option>
						<?php
						$ret = GShell("dynarc exec-func ext:variants.getDimTypes",$_REQUEST['sessid'],$_REQUEST['shellid']);
						$list = $ret['outarr'];
						for($c=0; $c < count($list); $c++)
						{
						 echo "<option value='".$list[$c]['id']."'".($itemInfo['variants']['dimid'] == $list[$c]['id'] ? " selected='selected'>" : ">")
							.$list[$c]['name']."</option>";
						}
						?>
					</select></td></tr>
			  </table>
			 </div>

			 <div id="variant-dim-container">
			 <?php
			 for($c=0; $c < count($itemInfo['variants']['dim']); $c++)
			 {
			  echo "<div class='dimitem'>";
			  echo "<a href='#' onclick='deleteVariantDim(this)' class='deletedim'><img src='"
				.$_ABSOLUTE_URL."share/widgets/gmart/img/small-delete.png' title='Elimina'/></a>";
			  echo "<div class='dimtitle' onclick='renameVariantDim(this)'>".$itemInfo['variants']['dim'][$c]['name']."</div>";
			  echo "</div>";
			 }
			 ?>
			 </div>
			 <div class="variant-dim-footer">
				<a href='#' onclick="addVariantDim()" class="smalllink" style="float:left;text-decoration:none"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/add-blue.png"/>Aggiungi misura</a>
				<a href='#' onclick="deleteVariantDimType()" class="smalllink" style="float:right">Elimina</a>
				<a href='#' onclick="saveAsVariantDimType()" class="smalllink" style="float:right">Salva come...</a>
				<a href='#' onclick="saveVariantDimType()" class="smalllink" style="float:right">Salva</a>
				<a href='#' onclick="renameVariantDimType()" class="smalllink" style="float:right">Rinomina</a>
			 </div>
			</div>
			<!-- EOF - DIM - PAGE -->

			<!-- OTHER - PAGE -->
			<div id="variant-other-page" style="display:none">
			 <div class="variant-page-header">
			  <table width="100%" cellspacing="0" cellpadding="0" border="0">
			  <tr><td class="title"><i>Altre varianti</i></td>	
				  <td width='32'><a href='#' title="Aggiungi" onclick="addVariantOther()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/add-btn-green.png" border="0"/></a></td></tr>
			  </table>
			 </div>

			 <div id="variant-other-container">
			 <?php
			 for($c=0; $c < count($itemInfo['variants']['other']); $c++)
			 {
			  echo "<div class='otheritem'>";
			  echo "<a href='#' onclick='deleteVariantOther(this)' class='deleteother'><img src='"
				.$_ABSOLUTE_URL."share/widgets/gmart/img/small-delete.png' title='Elimina'/></a>";
			  echo "<div class='othertitle' onclick='renameVariantOther(this)'>".$itemInfo['variants']['other'][$c]['name']."</div>";
			  echo "</div>";
			 }
			 ?>
			 </div>
			</div>
			<!-- EOF - OTHER - PAGE -->

			<!-- CHANGELOG: 05-11-2014 -->

			<!-- CODES AND PRICES - PAGE -->
			<div id="variant-codesandprices-page" style="display:none">
			 <div class="variant-page-header">
			  <table width="100%" cellspacing="0" cellpadding="0" border="0">
			  <tr><td class="title"><i>Elenco dei codici e dei prezzi per variante</i></td>	
				  <td width='32'>&nbsp;</td></tr>
 			  </table>
			 </div>

			 <div style="width:515px;height:320px;overflow:auto;padding-left:10px">
			 <table id="variant-codesandprices-list" width='1055' class="basiclist" cellspacing="0" cellpadding="0" border="0">
			 <tr><th width='130'>VARIANTE</th>
				 <th width='110'>CODICE</th>
				 <th width='110'>BARCODE</th>
				 <th width='55'>RICARICO</th>
				 <th width='110' style='text-align:left;padding-left:5px'>PREZZO FINALE</th>
				 <th width='90'>SKU</th>
				 <th width='90'>ASIN</th>
				 <th width='90'>EAN</th>
				 <th width='90'>GCID</th>
				 <th width='90'>GTIN</th>
				 <th width='90'>UPC</th>
			 </tr>
			 <?php
			  for($c=0; $c < count($itemInfo['varcodes']); $c++)
			  {
			   $el = $itemInfo['varcodes'][$c];

			   echo "<tr id='".$el['id']."' type='".$el['type']."'><td><input type='text' class='edit' style='width:120px' value=\"".$el['name']."\" readonly='true'/></td>";
			   echo "<td><input type='text' class='edit' style='width:100px' value='".$el['code']."'/></td>";
			   echo "<td><input type='text' class='edit' style='width:100px' value='".$el['barcode']."'/></td>";
			   echo "<td><input type='text' class='edit' style='width:30px' value='".$el['mrate']."'/> %</td>";
			   echo "<td><input type='text' class='edit' style='width:70px;text-align:right' onchange='this.value=formatCurrency(parseCurrency(this.value))' value='".number_format($el['finalprice'],2,',','.')."'/> &euro;</td>";
			   echo "<td><input type='text' class='edit' style='width:80px' value='".$el['sku']."' maxlength='40'/></td>";
			   echo "<td><input type='text' class='edit' style='width:80px' value='".$el['asin']."' maxlength='10'/></td>";
			   echo "<td><input type='text' class='edit' style='width:80px' value='".$el['ean']."' maxlength='13'/></td>";
			   echo "<td><input type='text' class='edit' style='width:80px' value='".$el['gcid']."' maxlength='40'/></td>";
			   echo "<td><input type='text' class='edit' style='width:80px' value='".$el['gtin']."' maxlength='14'/></td>";
			   echo "<td><input type='text' class='edit' style='width:80px' value='".$el['upc']."' maxlength='10'/></td>";
			   echo "</tr>";
			  }
			 ?>
			 </table>
			 </div>

			</div>
			<!-- EOF - CODES AND PRICES - PAGE -->

			<!-- PRICES BY QTY - PAGE -->
			<div id="variant-pricesbyqty-page" style="display:none">
			 <div class="variant-page-header">
			  <table width="100%" cellspacing="0" cellpadding="0" border="0">
			  <tr><td class="title"><i>Fasce di prezzo per quantit&agrave;</i></td>	
				  <td width='32'><a href='#' title="Aggiungi" onclick="addVariantPriceForQty()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/add-btn-green.png" border="0"/></a></td></tr>
 			  </table>
			 </div>

			 <div style="height:320px;overflow:auto;padding-left:10px">
			 <table id="variant-pricesbyqty-list" class="basiclist" width="100%" cellspacing="0" cellpadding="0" border="0">
			 <tr><th>COLORE / TINTA</th>
				 <th width='100'>TAGLIA / MISURA</th>
				 <th width='150'>QUANTITA&lsquo;</th>
				 <th width='55'>SCONTO</th>
				 <th width='90'>PREZZO FINALE</th>
				 <th width='20'>&nbsp;</th>
			 </tr>
			 <?php
			  for($c=0; $c < count($itemInfo['pricesbyqty']); $c++)
			  {
			   $el = $itemInfo['pricesbyqty'][$c];
			   $dataValues = "";
			   for($i=0; $i < count($el['colors']); $i++)
				$dataValues.= ",[".$el['colors'][$i]."]";
			   if($dataValues)	$dataValues = substr($dataValues,1);

			   echo "<tr id='".$el['id']."'><td><div class='optionbox' style='width:90px' datavalues=\"".$dataValues."\"></div></td>";

			   $dataValues = "";
			   for($i=0; $i < count($el['sizes']); $i++)
				$dataValues.= ",[".$el['sizes'][$i]."]";
			   if($dataValues)	$dataValues = substr($dataValues,1);

			   echo "<td><div class='optionbox' style='width:90px' datavalues=\"".$dataValues."\"></div></td>";
			   echo "<td align='center'>da: <input type='text' class='edit' style='width:40px;text-align:center' value='".$el['qtyfrom']."'/> a: <input type='text' class='edit' style='width:40px;text-align:center' value='".$el['qtyto']."'/></td>";
			   echo "<td align='center'><input type='text' class='edit' style='width:30px;text-align:center' value='".$el['discperc']."'/> %</td>";
			   echo "<td><input type='text' class='edit' style='width:70px;text-align:right' value='".number_format($el['finalprice'],2,',','.')."'/> &euro;</td>";
			   echo "<td><img src='".$_ABSOLUTE_URL."share/widgets/gmart/img/icon_delete.gif' style='cursor:pointer' title='Rimuovi' onclick='deletePricesByQty(this)'/></td>";
			   echo "</tr>";
			  }
			  if($c < 10)
			  for($i=$c; $i < 10; $i++)
			  {
			   echo "<tr><td><div class='optionbox' style='width:90px'></div></td>";
			   echo "<td><div class='optionbox' style='width:90px'></div></td>";
			   echo "<td align='center'>da: <input type='text' class='edit' style='width:40px;text-align:center'/> a: <input type='text' class='edit' style='width:40px;text-align:center'/></td>";
			   echo "<td align='center'><input type='text' class='edit' style='width:30px;text-align:center'/> %</td>";
			   echo "<td><input type='text' class='edit' style='width:70px;text-align:right'/> &euro;</td>";
			   echo "<td><img src='".$_ABSOLUTE_URL."share/widgets/gmart/img/icon_delete.gif' style='cursor:pointer' title='Rimuovi' onclick='deletePricesByQty(this)'/></td>";
			   echo "</tr>";
			  }
			 ?>
			 </table>
			 </div>

			</div>
			<!-- EOF - PRICES BY QTY - PAGE -->

			<!-- EOF CHANGELOG: 05-11-2014 -->

		  </td></tr>
	  </table>
	 </div>
	</div>
	<!-- EOF - VARIANT PAGE -->

	<!-- OTHER PAGE -->
	<div class='tabpage' id="other-page" style="display:none;">
	 <ul class='bluenuv' id='other-bluenuv' style='float:left;'>
	  <li class='selected' id='other-purchcron-tab' onclick='otherShow(this)' connect='other-purchcron-page'><span>Cronologia acquisti</span></li>
	  <li id='other-salecron-tab' onclick='otherShow(this)' connect='other-salecron-page'><span>Cronologia vendite</span></li>
	 </ul>

	 <!-- CRONOLOGIA ACQUISTI -->
	 <div class='otherpage' id='other-purchcron-page'>
	  <h3 class='orangebar'>CRONOLOGIA ACQUISTI</h3>
	  <div style='margin-left:10px;padding:5px'>
	   <span class='smalltext'>Filtra per fornitore:</span> 
		<select id='purchcron-vendor-select' style='width:200px' onchange="updatePurchaseCron()">
		 <option value='0'>tutti i fornitori</option>
		 <?php
		  for($c=0; $c < count($itemInfo['vendorprices']); $c++)
		   echo "<option value='".$itemInfo['vendorprices'][$c]['vendor_id']."'>".$itemInfo['vendorprices'][$c]['vendor_name']."</option>";
		 ?>
		</select>
	   <span class='smalltext' style='margin-left:50px'>dal: </span>
		<input type='text' class="gcal" id='purchcron-datefrom' onchange='updatePurchaseCron()'/>
	   <span class='smalltext'>al: </span>
		<input type='text' class="gcal" id='purchcron-dateto' onchange='updatePurchaseCron()'/>
	  </div>
	  <div style="width:686px;height:240px;overflow:auto;border:1px solid #dadada;margin-left:10px;margin-top:5px">
	  <table class="pricelists" id='purchcron-table' style="width:100%" cellspacing="2" cellpadding="2" border="0">
	   <tr>
		<th width='80'>Data</th>
		<th>Fornitore</th>
		<th width='220'>Documento di riferimento</th>
		<th width='80'>Pr. Acquisto</th>
	   </tr>
	  
	  </table>
	  <div id='purchcron-nextpage-button' class='cron-nextpage-button-outer' style='display:none'>
		<span class='cron-nextpage-button' onclick='updatePurchaseCron(this)'>Mostra altri risultati</span>
	  </div>
	  <img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/loading.gif" class="cronloading-icon" style="top:210px;left:276px" id="purchcron-loading-icon"/>
	  </div>
	  <div style='margin-left:10px;' id='purchcron-docselect'>
	   <span class='smalltext'>Cerca in: </span>
		<?php
		 for($c=0; $c < count($_VENDOR_DOCTYPES); $c++)
		  echo "&nbsp;&nbsp;<span class='smalltext'><input type='checkbox' data-tag='".$_VENDOR_DOCTYPES[$c]['tag']."'"
			.($_VENDOR_DOCTYPES[$c]['crondef'] ? " checked='true'" : "")
			." onchange='updatePurchaseCron()'/>".$_VENDOR_DOCTYPES[$c]['name']."</span>";
		?>
	  </div>
	 </div>
	 <!-- EOF - CRONOLOGIA ACQUISTI -->

	 <!-- CRONOLOGIA VENDITE -->
	 <div class='otherpage' id='other-salecron-page' style='display:none'>
	  <h3 class='orangebar'>CRONOLOGIA VENDITE</h3>
	  <div style='margin-left:10px;padding:5px'>
	   <span class='smalltext'>Filtra per cliente:</span> 
		<input type='text' class='edit' id='salecron-customer-select' placeholder="digita il nome del cliente" style='width:300px'/>
	   <span class='smalltext' style='margin-left:50px'>dal: </span>
		<input type='text' class="gcal" id='salecron-datefrom' onchange='updateSaleCron()'/>
	   <span class='smalltext'>al: </span>
		<input type='text' class="gcal" id='salecron-dateto' onchange='updateSaleCron()'/>
	  </div>
	  <div style="width:686px;height:200px;overflow:auto;border:1px solid #dadada;margin-left:10px;margin-top:5px">
	  <table class="pricelists" id='salecron-table' style="width:100%" cellspacing="2" cellpadding="2" border="0">
	   <tr>
		<th width='80'>Data</th>
		<th>Cliente</th>
		<th width='220'>Documento di riferimento</th>
		<th width='80'>Pr. Vendita</th>
	   </tr>
	  
	  </table>
	  <div id='salecron-nextpage-button' class='cron-nextpage-button-outer' style='display:none'>
		<span class='cron-nextpage-button' onclick='updateSaleCron(this)'>Mostra altri risultati</span>
	  </div>
	  <img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/loading.gif" class="cronloading-icon" style="top:206px;left:289px;width:170px;height:170px" id="salecron-loading-icon"/>
	  </div>
	  <div style='margin-left:10px;margin-top:4px'><span class='smalltext'>Cerca in: </span></div>
	  <div id='salecron-docselect' style='margin-left:10px;height:50px;width:688px;overflow:auto'>
		<?php
		 for($c=0; $c < count($_CUSTOMER_DOCTYPES); $c++)
		  echo "<span class='smalltext' style='white-space:nowrap'><input type='checkbox' data-tag='".$_CUSTOMER_DOCTYPES[$c]['tag']."'"
			.($_CUSTOMER_DOCTYPES[$c]['crondef'] ? " checked='true'" : "")
			." onchange='updateSaleCron()'/>".$_CUSTOMER_DOCTYPES[$c]['name']."</span>&nbsp;&nbsp;";
		?>
	  </div>
	 </div>
	 <!-- EOF - CRONOLOGIA VENDITE -->

	</div>
	<!-- EOF OTHER PAGE -->



	<!-- EOF CONTENTS -->
	</td><td valign='top' style="border-left:1px solid #dadada;padding-left:10px;">
	<!-- RIGHT SPACE -->

	<h3 class='rightsec-blue'><span class='title'>STRUMENTI</span></h3>
	<div class='right-section'>
		<a href='#' id='copy-to-clipboard-link'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/clipboard.png" border='0'/>Copia negli appunti</a><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/blue-dnarr.png"/>
		<ul class="submenu" id="clipboards-list">
		<?php
		$ret = GShell("dynarc clipboard-list");
		$list = $ret['outarr'];
		for($c=0; $c < count($list); $c++)
		 echo "<li onclick='copyToClipboard(".$list[$c]['id'].")'>".$list[$c]['name']."</li>";
		?>
		<li class='separator'>&nbsp;</li>
		<li onclick='copyToClipboard()'>Nuovo...</li>
		</ul>
	</div>

	<!-- <div class='right-section'>
		<a href='#'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/similar.png" border='0'/>Cerca articoli simili</a>
	</div> -->

	<br/>
	<br/>
	
	<h3 class='rightsec-blue'><span class='title'>INFORMAZIONI</span></h3>
	<span class='gray11'>ID univoco: </span><span class='black11'><b><?php echo $itemInfo['id']; ?></b></span><br/>
	<span class='gray11'>Data creazione: </span><span class='black11'><?php echo date('d/m/Y',$itemInfo['ctime']); ?></span><br/>
	<?php
	if($itemInfo['mtime'])
	 echo "<span class='gray11'>Ultima modifica: </span><span class='black11'>".date('d/m/Y',$itemInfo['mtime'])."</span><br/>";
	/* CHANGELOG: 20-11-2014 */
	$mod = new GMOD($itemInfo['modinfo']['mod'],$itemInfo['modinfo']['uid'],$itemInfo['modinfo']['gid']);
	echo "<br/><span class='gray11'>Permessi: </span><span class='black11' id='permstring'>".$mod->toString()."</span>";
	echo "&nbsp;&nbsp;<a href='#' style='font-family:arial,sans-serif;font-size:10px' onclick='changePerms()'>modifica</a><br/>";
	/* EOF - CHANGELOG */
	?>
	<br/>
	<!-- <span class='gray11'>Articoli venduti: </span><span class='black11'><?php echo $itemInfo['sold']; ?></span><br/> -->


	<h3 class='rightsec-blue'><span class='title'>MAGAZZINO</span></h3>
	<?php
	$dis = $itemInfo['storeqty']-$itemInfo['booked'];
	$inc = $itemInfo['incoming'];
	if($dis < 0)
	 $inc+= $dis;
	?>
    <div style='margin-bottom:3px'><input type='checkbox' class='checkbox' id='hideinstore' <?php if(!$itemInfo['hide_in_store']) echo "checked='true'"; ?>/><span class='gray11'>Mostra in magazzino</span></div>
	<span class='gray11'>Disponibili: </span>
		<?php
		if($dis <= 0)
		 echo "<span class='red11'><b>0</b></span>";
		else
		 echo "<span class='black11'><b>".$dis."</b></span>";
		if($inc > 0)
		 echo "&nbsp;<span class='blue11'><b>+".$inc."</b></span>";
		else if($inc < 0)
		 echo "&nbsp;<span class='red11'><b>-".$inc."</b></span>";
		?>
	<br/>
	<br/>
	<span class='gray11'>Giacenza fisica: </span><span class='black11'><b><?php echo $itemInfo['storeqty']; ?></b></span><br/>
	<span class='gray11'>Prenotati: </span><span class='black11'><b><?php echo $itemInfo['booked']; ?></b></span><br/>
	<span class='gray11'>Ordinati al fornit.: </span><span class='black11'><b><?php echo $itemInfo['incoming']; ?></b></span><br/>
	<br/>
	<span class='gray11'>Scorta minima: </span><input type='text' class='edit' style='width:50px' value="<?php echo $itemInfo['minstock']; ?>" id='minstock'/><br/>

	<!-- EOF RIGHT SPACE -->
	</td></tr>
 </table>
 
 <ul class='basicbuttons' style="margin-left:4px;margin-top:6px;float:left;">
  <li><span onclick='submit()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/save.gif" border='0'/>Salva</span></li>
  <li><span onclick="gframe_close()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/exit.png" border='0'/>Chiudi</span></li>
 </ul>

 <ul class='basicbuttons' style="float:right;margin-top:6px;margin-right:20px;">
  <li><span onclick='deleteItem()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/delete.png" border='0'/>Elimina</span></li>
 </ul>

</div>

<script>
var ERROR_CODE = "<?php echo $_ERROR_CODE; ?>";
var AP = "<?php echo $_AP ? $_AP : 'gmart'; ?>";
var ACTIVE_TAB_PAGE = "details";
var ACTIVE_VARIANT_TAB_PAGE = "colors";
var extendedLayerLoaded = false;
var extendedLayerOFrame = null;
var DECIMALS = <?php echo $_DECIMALS ? $_DECIMALS : "2"; ?>;

var IDOCS = new Array();

var PRICELISTSTB = null;
var VENDORSTB = null;
var CPTB = null;

var NEW_VENDORPRICES = new Array();
var UPDATED_VENDORPRICES = new Array();
var DELETED_VENDORPRICES = new Array();

/* CHANGELOG: 08-11-2014 */
var DELETED_PRICESBYQTY = new Array();
var DELETED_VARCODES = new Array();
/* EOF - CHANGELOG */

var NEW_CP = new Array();
var UPDATED_CP = new Array();
var DELETED_CP = new Array();
var attUpld = null;

var LightBox = new GLightBox();

var purchcronLoaded = false;
var salecronLoaded = false;

var GCalObj = new GCal();

<?php
for($c=0; $c < count($itemInfo['idocs']); $c++)
 echo "IDOCS.push({aid:".$itemInfo['idocs'][$c]['aid'].",id:".$itemInfo['idocs'][$c]['id'].",isdefault:".($itemInfo['idocs'][$c]['default'] ? "true" : "false")."});\n";
?>

function bodyOnLoad()
{
 if(ERROR_CODE)
 {
  switch(ERROR_CODE)
  {
   case 'ITEM_DOES_NOT_EXISTS' : alert("Spiacente!, articolo inesistente, probabilmente è stato eliminato dal catalogo."); break;
   case 'PERMISSION_DENIED' : alert("Permesso negato!, spiacente ma non disponi dei privilegi necessari per poter visualizzare questo articolo."); break;
  }
  return gframe_close(ERROR_CODE);
 }

 PRICELISTSTB = new GMUTable(document.getElementById('pricelists-table'), {autoresize:false, autoaddrows:false});
 PRICELISTSTB.OnCellEdit = function(r,cell,value){
	 _pricelistsUpdateTotals(r);
	}

 /* VENDORS TABLE */
 VENDORSTB = new GMUTable(document.getElementById('vendors-table'), {autoresize:false, autoaddrows:false});
 VENDORSTB.OnCellEdit = function(r,cell,value){
	 /* Update total */
	 var price = parseCurrency(r.cell['price'].getValue());
	 var vatrate = parseFloat(r.cell['vatrate'].getValue());
	 var shipcosts = parseCurrency(r.cell['shipcosts'].getValue());

	 if(!price) price=0;
	 if(!vatrate) vatrate=0;
	 if(!shipcosts) shipcosts=0;

	 var total = price+shipcosts;
	 if(total)
	  total = total + ((total/100)*vatrate);
	 r.cell['total'].setValue(total ? formatCurrency(total,DECIMALS) : "&nbsp;");

	 if(r.id && (UPDATED_VENDORPRICES.indexOf(r) < 0))
	  UPDATED_VENDORPRICES.push(r);
	}

 VENDORSTB.OnBeforeAddRow = function(r){
	 r.cells[0].innerHTML = "<input type='checkbox'/ >"; r.cells[0].style.textAlign='center';
	 r.cells[3].style.textAlign='right';
	 r.cells[4].style.textAlign='right';
	 r.cells[5].style.textAlign='center';
	 r.cells[6].style.textAlign='right';
	 NEW_VENDORPRICES.push(r);
	}

 VENDORSTB.OnDeleteRow = function(r){
	 if(r.id)
	 {
	  if(UPDATED_VENDORPRICES.indexOf(r) >= 0)
	   UPDATED_VENDORPRICES.splice(UPDATED_VENDORPRICES.indexOf(r),1);
	  DELETED_VENDORPRICES.push(r);
	 }
	 else
	  NEW_VENDORPRICES.splice(NEW_VENDORPRICES.indexOf(r),1);
	}

 VENDORSTB.FieldByName['vendor'].enableSearch("dynarc item-find -ap rubrica -field name `","` -limit 10 --order-by 'name ASC'","id","name","items",true);

 /* CUSTOM PRICING TABLE */
 CPTB = new GMUTable(document.getElementById('custompricing-table'), {autoresize:false, autoaddrows:false});
 CPTB.OnCellEdit = function(r,cell,value,data){
	 if(r.id && (UPDATED_CP.indexOf(r) < 0))
	  UPDATED_CP.push(r);
	 cell.data = data;
	 _custompricingUpdateTotals(r);
	}

 CPTB.OnBeforeAddRow = function(r){
	 r.cells[0].innerHTML = "<input type='checkbox'/ >"; r.cells[0].style.textAlign='center';
	 r.cells[2].innerHTML = formatCurrency("<?php echo $itemInfo['baseprice']; ?>");
	 r.cells[3].innerHTML = "0%";
	 r.cells[4].innerHTML = formatCurrency("<?php echo $itemInfo['baseprice']; ?>");
	 r.cells[5].innerHTML = "<?php echo $itemInfo['vat'] ? $itemInfo['vat'] : $_FREQ_VAT_PERC; ?>%";

	 r.cells[2].style.textAlign='right';
	 r.cells[3].style.textAlign='center';
	 r.cells[4].style.textAlign='right';
	 r.cells[5].style.textAlign='center';
	 r.cells[6].style.textAlign='right';

	 NEW_CP.push(r);
	}

 CPTB.OnDeleteRow = function(r){
	 if(r.id)
	 {
	  if(UPDATED_CP.indexOf(r) >= 0)
	   UPDATED_CP.splice(UPDATED_SN.indexOf(r),1);
	  DELETED_CP.push(r);
	 }
	 else
	  NEW_CP.splice(NEW_CP.indexOf(r),1);
	}

 CPTB.FieldByName['subject'].enableSearch("dynarc item-find -ap rubrica -field name `","` -limit 10 --order-by 'name ASC'","id","name","items",true);


 new GPopupMenu(document.getElementById('copy-to-clipboard-link'), document.getElementById('clipboards-list'));

 /* ATTACHMENTS */
 attUpld = new GUploader(null,null,"attachments/gmart/");
 document.getElementById('gupldspace').appendChild(attUpld.O);
 attUpld.OnUpload = function(file){
	 var sh = new GShell();
	 sh.OnOutput = function(o,a){
		 var div = document.createElement('DIV');
		 div.className = "attachment";
		 div.id = "attachment-"+a['id'];
		 var ih = "<a href='#' class='btnedit' onclick='editAttachment("+a['id']+")' title='Modifica'><img src='"+ABSOLUTE_URL+"share/widgets/gmart/img/edit_small.png' border='0'/ ></a> <a href='#' class='btndel' onclick='deleteAttachment("+a['id']+")' title='Rimuovi'><img src='"+ABSOLUTE_URL+"share/widgets/gmart/img/delete_small.png' border='0'/ ></a><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank'>";
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
		}
	 sh.sendCommand("dynattachments add -ap `"+AP+"` -refid `<?php echo $itemInfo['id']; ?>` -name '"+file['name']+"' -url '"+file['fullname']+"'");
	}

 /* BRAND */
 EditSearch.init(document.getElementById('item_brand'), "dynarc item-find -ap brands -ct gmart -field name `","` -limit 20 --order-by 'name ASC'","id","name","items",true);
 document.getElementById('item_brand').onchange = function(){
	 if(this.value && this.data)
	  this.setAttribute('refid',this.data['id']);
	 else
	  this.setAttribute('refid',0);
	}

 /* CHANGELOG: 05-11-2014 */
 var PBQTB = document.getElementById('variant-pricesbyqty-list');
 for(var c=1; c < PBQTB.rows.length; c++)
 {
  var r = PBQTB.rows[c];
  var ed1 = r.cells[0].getElementsByTagName('DIV')[0];			var ob1 = new OptionBox(ed1);
  var ed2 = r.cells[1].getElementsByTagName('DIV')[0];			var ob2 = new OptionBox(ed2);
  
  r.cells[2].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}
  r.cells[2].getElementsByTagName('INPUT')[1].onchange = function(){this.parentNode.parentNode.changed = true;}
  r.cells[3].getElementsByTagName('INPUT')[0].onchange = function(){
	 this.parentNode.parentNode.changed = true;
	 this.parentNode.parentNode.cells[4].getElementsByTagName('INPUT')[0].value = "";
	}
  r.cells[4].getElementsByTagName('INPUT')[0].onchange = function(){
	 this.parentNode.parentNode.changed = true; 
	 this.value = formatCurrency(parseCurrency(this.value));
	 this.parentNode.parentNode.cells[3].getElementsByTagName('INPUT')[0].value = "";
	}
  
 }

 var VARCODESTB = document.getElementById('variant-codesandprices-list');
 for(var c=1; c < VARCODESTB.rows.length; c++)
 {
  var r = VARCODESTB.rows[c];
  r.cells[1].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}
  r.cells[2].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}
  r.cells[3].getElementsByTagName('INPUT')[0].onchange = function(){
	 this.parentNode.parentNode.changed = true;
	 this.parentNode.parentNode.cells[4].getElementsByTagName('INPUT')[0].value = "";
	}
  r.cells[4].getElementsByTagName('INPUT')[0].onchange = function(){
	 this.parentNode.parentNode.changed = true; 
	 this.value = formatCurrency(parseCurrency(this.value));
	 this.parentNode.parentNode.cells[3].getElementsByTagName('INPUT')[0].value = "";
	}
  r.cells[5].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}
  r.cells[6].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}
  r.cells[7].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}
  r.cells[8].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}
  r.cells[9].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}
  r.cells[10].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}
 }

 /* EOF - CHANGELOG */

 
 EditSearch.init(document.getElementById('salecron-customer-select'), "fastfind contacts -fields code_str,name `","` -limit 10","id","name","results",true);
 document.getElementById('salecron-customer-select').onchange = function(){
	 if(!this.value) this.data = null;
	  updateSaleCron();
	}

 document.getElementById('salecron-datefrom').onclick = function(){showGCal(this);}
 document.getElementById('salecron-dateto').onclick = function(){showGCal(this);}
 document.getElementById('purchcron-datefrom').onclick = function(){showGCal(this);}
 document.getElementById('purchcron-dateto').onclick = function(){showGCal(this);}
}

function submit()
{
 if(extendedLayerLoaded)
 {
  for(var c=0; c < IDOCS.length; c++)
  {
   if(IDOCS[c].frame && !IDOCS[c].frame.saved)
   {
    if(typeof(IDOCS[c].frame.idocAutoSave) == "function")
	{
	 var oFrame = IDOCS[c].frame;
	 IDOCS[c].frame.idocAutoSave(function(){oFrame.saved=true; submit();});
	 return;
	}
   }
  }
 } 
 return saveAndClose();
}

function saveAndClose()
{
 var catId = document.getElementById("catid").value;
 var code = document.getElementById('item_code').value;
 var brand = document.getElementById('item_brand').value.E_QUOT();
 var brandId = document.getElementById('item_brand').getAttribute('refid');
 var model = document.getElementById('item_model').value.E_QUOT();
 var barcode = document.getElementById('item_barcode').value;
 var manufcode = document.getElementById('item_manufcode').value;
 var location = document.getElementById('item_location').value;
 var gebindeCode = document.getElementById('gebinde_code').value;
 var gebinde = document.getElementById('gebinde').value;
 var division = document.getElementById('division').value;
 var desc = document.getElementById('item_description').value;
 var baseprice = parseCurrency(document.getElementById('item_baseprice').value);
 var vat = document.getElementById('item_vat').value;
 var units = document.getElementById('item_units').value;
 var weight = document.getElementById('item_weight').value;
 var weightUnits = document.getElementById('item_weight_units').value;
 var minstock = document.getElementById('minstock').value;
 var hideinstore = (document.getElementById('hideinstore').checked == true) ? '0' : '1';
 var sku = document.getElementById('sku').value;
 var spidType = document.getElementById('spid_type').value;
 var spidCode = document.getElementById('spid_code').value;

 // verifica lunghezza campi su marca e modello
 if(brand.length > 32)
  return alert("Attenzione!, il campo 'marca' è troppo lungo perchè probabilmente contiene delle virgolette,accenti o caratteri speciali.");
 if(model.length > 64)
  return alert("Attenzione!, il campo 'modello' è troppo lungo perchè probabilmente contiene delle virgolette,accenti o caratteri speciali.");

 /* Save pricelists */
 var pricelists = "";
 var set = ",minimum_stock='"+minstock+"'";
 for(var c=1; c < PRICELISTSTB.O.rows.length; c++)
 {
  var r = PRICELISTSTB.O.rows[c];
  var pid = r.id.substr(10);
  if(r.cells[0].getElementsByTagName('INPUT')[0].checked)
   pricelists+= ","+pid;
  set+= ",pricelist_"+pid+"_baseprice='"+parseCurrency(r.cell['baseprice'].getValue())+"',pricelist_"+pid+"_mrate='"+parseFloat(r.cell['markuprate'].getValue())+"',pricelist_"+pid+"_vat='"+parseFloat(r.cell['vat'].getValue())+"',pricelist_"+pid+"_vendorprice='"+parseCurrency(r.cell['vendorprice'].getValue())+"',pricelist_"+pid+"_cm='"+parseFloat(r.cell['cm'].getValue())+"',pricelist_"+pid+"_discount='"+parseFloat(r.cell['discount'].getValue())+"'";
 }
 if(pricelists)
  pricelists = pricelists.substr(1);

 var sh = new GShell();
 sh.OnFinish = function(o,a){gframe_close(o,a);}
 sh.OnError = function(err){alert(err);}
 sh.sendCommand("dynarc edit-item -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>` -cat '"+catId+"' -name `"+brand+" "+model+"` -code-str `"+code+"` -extset `gmart.brand='''"+brand+"''',brandid='"+brandId+"',model='''"+model+"''',barcode='"+barcode+"',mancode='"+manufcode+"',location='''"+location+"''',units='"+units+"',weight='"+weight+"',weightunits='"+weightUnits+"',gebinde='''"+gebinde+"''',gebinde_code='"+gebindeCode+"',division='"+division+"',hideinstore='"+hideinstore+"',sku='"+sku+"',spidtype='"+spidType+"',spidcode='"+spidCode+"',pricing.baseprice='"+baseprice+"',vat='"+vat+"',pricelists='"+pricelists+"'` -desc `"+desc+"`"+(set ? " -set `"+set.substr(1)+"`" : ""));

 /* SAVE VENDOR PRICES */
 for(var c=0; c < DELETED_VENDORPRICES.length; c++)
  sh.sendCommand("dynarc edit-item -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>` -extunset `vendorprices.id="+DELETED_VENDORPRICES[c].id.substr(12)+"`");
 for(var c=0; c < UPDATED_VENDORPRICES.length; c++)
 {
  var r = UPDATED_VENDORPRICES[c];
  sh.sendCommand("dynarc edit-item -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>` -extset `vendorprices.id='"+r.id.substr(12)+"',code='"+r.cell['code'].getValue()+"',vendor='''"+r.cell['vendor'].getValue()+"''',shipcosts='"+parseFloat(r.cell['shipcosts'].getValue())+"',price='"+parseCurrency(r.cell['price'].getValue())+"',vatrate='"+parseFloat(r.cell['vatrate'].getValue())+"'`");
 }
 for(var c=0; c < NEW_VENDORPRICES.length; c++)
 {
  var r = NEW_VENDORPRICES[c];
  sh.sendCommand("dynarc edit-item -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>` -extset `vendorprices.code='"+r.cell['code'].getValue()+"',vendor='''"+r.cell['vendor'].getValue()+"''',shipcosts='"+parseFloat(r.cell['shipcosts'].getValue())+"',price='"+parseCurrency(r.cell['price'].getValue())+"',vatrate='"+parseFloat(r.cell['vatrate'].getValue())+"'`");
 }

 /* SAVE CUSTOM PRICING */
 for(var c=0; c < DELETED_CP.length; c++)
  sh.sendCommand("dynarc edit-item -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>` -extunset `custompricing.id="+DELETED_CP[c].id.substr(14)+"`");
 for(var c=0; c < UPDATED_CP.length; c++)
 {
  var r = UPDATED_CP[c];
  var subjectId = r.cell['subject'].data ? r.cell['subject'].data['id'] : 0;
  sh.sendCommand("dynarc edit-item -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>` -extset `custompricing.id='"+r.id.substr(14)+"',subject='''"+r.cell['subject'].getValue()+"''',baseprice='"+parseCurrency(r.cell['baseprice'].getValue())+"',discount='"+r.cell['discount'].getValue()+"'"+(subjectId ? ",subjectid='"+subjectId+"'" : "")+"`");
 }
 for(var c=0; c < NEW_CP.length; c++)
 {
  var r = NEW_CP[c];
  var subjectId = r.cell['subject'].data ? r.cell['subject'].data['id'] : 0;
  sh.sendCommand("dynarc edit-item -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>` -extset `custompricing.subject='''"+r.cell['subject'].getValue()+"''',baseprice='"+parseCurrency(r.cell['baseprice'].getValue())+"',discount='"+r.cell['discount'].getValue()+"'"+(subjectId ? ",subjectid='"+subjectId+"'" : "")+"`");
 }

 saveVariants(sh);
}

function deleteItem()
{
 if(gframe_shotmessage("Are you sure you want remove this article?","<?php echo $itemInfo['id']; ?>","DELETE") == false)
  return;

 if(!confirm("Sei sicuro di voler eliminare questo articolo?"))
  return;

 var sh = new GShell();
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 sh.sendCommand("dynarc delete-item -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>`"); 
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

 if((page == "extended") && !extendedLayerLoaded)
 {
  loadIDOC(IDOCS[0]);
  extendedLayerLoaded=true;
 }
 else if((page == "other") && !purchcronLoaded)
 {
  updatePurchaseCron();
  purchcronLoaded = true;
 }
}

function browseCategory()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,catId){
	 if(!catId) return;
	 var sh2 = new GShell();
	 sh2.OnError = function(err){alert(err);}
	 sh2.OnOutput = function(o,a){refreshCatList(a['parent_id'], a['id']);}
	 sh2.sendCommand("dynarc cat-info -ap '"+AP+"' -id '"+catId+"'");
	}
 sh.sendCommand("gframe -f dynarc.categorySelect -params `ap="+AP+"`");
}

function refreshCatList(parentId, selectedId)
{
 var sel = document.getElementById("catid");
 while(sel.options.length > 1)
 {
  sel.removeChild(sel.options[1]);
 }
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 for(var c=0; c < a.length; c++)
	 {
	  var opt = document.createElement('OPTION');
	  opt.value = a[c]['id'];
	  opt.innerHTML = a[c]['name'];
	  sel.appendChild(opt);
	 }
	 sel.value = selectedId;
	}
 sh.sendCommand("dynarc cat-list -ap '"+AP+"' -parent '"+(parentId ? parentId : "0")+"'");
}

/* THUMBNAILS */

function uploadImage()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return gframe_opacity(100);

	 var tbUL = document.getElementById('thumb-buttons');
	 var d = new Date();
	 var tbIDX = d.getTime();

	 var dstPath = "image/"+AP+"/";

	 var sh2 = new GShell();
	 sh2.OnFinish = function(){
		 var list = tbUL.getElementsByTagName('LI');
		 var baseNum = list.length+1;

		 for(var c=0; c < list.length; c++)
		  list[c].className = "";
 
		 for(var c=0; c < a['files'].length; c++)
		 {
		  var li = document.createElement('LI');
		  li.innerHTML = baseNum+c;
		  li.fileName = USER_HOME+dstPath+"product-<?php echo $itemInfo['id']; ?>-"+(tbIDX+c)+"."+a['files'][c]['extension'];
		  li.thumbName = USER_HOME+dstPath+"product-<?php echo $itemInfo['id']; ?>-"+(tbIDX+c)+"-thumb."+a['files'][c]['extension'];
		  li.onclick = function(){selectThumb(this);}
		  tbUL.appendChild(li);
		  if(c == (a['files'].length-1))
		  {
		   li.className = "selected";
		   document.getElementById('thumb-preview').style.backgroundImage = "url(<?php echo $_ABSOLUTE_URL; ?>"+li.thumbName+")";
		   document.getElementById('thumb-preview').setAttribute('filename',li.fileName);
		  }
		 }
		 gframe_opacity(100);
		}

	 for(var c=0; c < a['files'].length; c++)
	 {
	  switch(a['mode'])
	  {
	   case 'UPLOAD' : {
		 var fileName = a['files'][c]['fullname'].replace(USER_HOME,"");
		 var dstFileName = dstPath+"product-<?php echo $itemInfo['id']; ?>-"+(tbIDX+c)+"."+a['files'][c]['extension'];
		 sh2.sendCommand("mv `"+fileName+"` `"+dstFileName+"`");
		} break;

	   case 'FROM_SERVER' : {
		 var fileName = a['files'][c]['fullname'].replace(USER_HOME,"");
		 var dstFileName = dstPath+"product-<?php echo $itemInfo['id']; ?>-"+(tbIDX+c)+"."+a['files'][c]['extension'];
		 if(fileName != dstFileName)
		  sh2.sendCommand("cp `"+fileName+"` `"+dstFileName+"`");
		} break;

	  }
	  
	  sh2.sendCommand("gd resize -i `"+dstFileName+"` -o `"+dstPath+"product-<?php echo $itemInfo['id']; ?>-"+(tbIDX+c)+"-thumb."+a['files'][c]['extension']+"` -w 128");
	  sh2.sendCommand("dynarc edit-item -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>` -extset `thumbnails.add='"+USER_HOME+dstFileName+"'`");
	 }

	}
 sh.sendCommand("gframe -f imageupload -params `destpath=tmp&allowmultiple=true`");
 gframe_opacity(80);
}

function selectThumb(li)
{
 var ul = li.parentNode;
 var list = ul.getElementsByTagName('LI');
 for(var c=0; c < list.length; c++)
 {
  if(list[c] == li)
  {
   var fileName = li.fileName ? li.fileName : decodeFID(li.id);
   var thumbName = li.thumbName ? li.thumbName : fileName;

   /*if(li.getAttribute('icon') && !li.fileName)
	fileName = decodeFID(li.getAttribute('icon'));*/

   document.getElementById('thumb-preview').style.backgroundImage = "url(<?php echo $_ABSOLUTE_URL; ?>"+thumbName+")";
   document.getElementById('thumb-preview').setAttribute('filename',fileName);
  }
  else
   list[c].className = "";
 }
 li.className = "selected";
}

function deleteSelectedThumb(ev)
{
 var event = ev ? ev : window.event;
 if(event.stopPropagation) event.stopPropagation();
 else event.cancelBubble = true;

 var tbUL = document.getElementById('thumb-buttons');
 var list = tbUL.getElementsByTagName('LI');
 for(var c=0; c < list.length; c++)
 {
  if(list[c].className == "selected")
  {
   var idx = c;
   if(!confirm("Sei sicuro di voler rimuovere questa immagine?"))
	return ;
   var sh = new GShell();
   sh.OnOutput = function(){
	 var list = tbUL.getElementsByTagName('LI');
	 tbUL.removeChild(list[idx]);
	 for(var c=0; c < list.length; c++)
	  list[c].innerHTML = (c+1);
	 if(list[idx])
	  selectThumb(list[idx]);
	 else if(list[0])
	  selectThumb(list[0]);
	 else
	 {
	  document.getElementById('thumb-preview').style.backgroundImage = "url(<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/photo.png)";
	  document.getElementById('thumb-preview').setAttribute('filename','');
	 }
	}
   sh.sendCommand("dynarc edit-item -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>` -extunset `thumbnails."+c+"`");
   break;
  }
 }

}

function decodeFID(fid)
{
 var str = "";
 var p = 0;
 while(p < fid.length)
 {
  str+= "%"+fid.substr(p,2);
  p+= 2;
 }
 str = unescape(str);
 return str;
}

function urlencode(str) 
{
 str = escape(str);
 str = str.replace('+', '%2B');
 str = str.replace('%20', '+');
 str = str.replace('*', '%2A');
 str = str.replace('/', '%2F');
 str = str.replace('@', '%40');
 return str;
}

/* IDOCS */

function loadIDOC(idoc)
{
 if(!idoc) return;
 var sh = new GShell();
 var divName = "idocspace-"+idoc.aid+"_"+idoc.id;
 sh.OnPreOutput = function(o,a,msgType){
	 switch(msgType)
	 {
	  case 'LOADED' : {
		 idoc.frame = a; 
		 if(typeof(idoc.frame.idocAutoLoad) == "function")
		  idoc.frame.idocAutoLoad();
		} break;
	  /*case 'SAVED' : {
		 idoc.frame.saved = true;
		 submit(); 
		} break;*/
	 }

	}
 sh.sendCommand("gframe -f idoc.exec -params `idocaid="+idoc.aid+"&idocid="+idoc.id+"&ap="+AP+"&id=<?php echo $itemInfo['id']; ?>` --append-to `"+divName+"`");
}

function idocShow(li)
{
 var ul = document.getElementById("idoc-bluenuv");
 var list = ul.getElementsByTagName('LI');
 for(var c=0; c < list.length; c++)
 {
  list[c].className = (list[c] == li) ? "selected" : "";
  document.getElementById("idocspace-"+list[c].id.substr(5)).style.display = (list[c] == li) ? "" : "none";
  if(list[c] == li)
  {
   if(!IDOCS[c].frame)
	loadIDOC(IDOCS[c]);
  }

 } 
}

function idocRemove(img)
{
 var strid = img.parentNode.id.substr(5);
 var sheetName = img.parentNode.getElementsByTagName('SPAN')[0].innerHTML;

 var tmp = strid.split("_");
 var aid = tmp[0];
 var id = tmp[1];

 if(!confirm("Sei sicuro di voler rimuovere la scheda '"+sheetName+"' da questo articolo?"))
  return;

 /* Check for default idoc */
 for(var c=0; c < IDOCS.length; c++)
 {
  if((IDOCS[c].aid == aid) && (IDOCS[c].id == id) && IDOCS[c].isdefault)
   return alert("Questa è una scheda predefinita, è possibile rimuoverla soltanto attraverso il pannello delle proprietà di questa categoria.");
 }

 var sh = new GShell();
 sh.OnOutput = function(){
	 var li = document.getElementById('idoc-'+aid+"_"+id);
	 li.parentNode.removeChild(li);
	 var div = document.getElementById('idocspace-'+aid+"_"+id);
	 div.parentNode.removeChild(div);
	 for(var c=0; c < IDOCS.length; c++)
	 {
	  if((IDOCS[c]['aid'] == aid) && (IDOCS[c]['id'] == id))
	  {
	   IDOCS.splice(c,1);
	   break;
	  }
	 }
	 var ul = document.getElementById('idoc-bluenuv');
	 var li = ul.getElementsByTagName('LI')[0];
	 if(li)
	  idocShow(li);
	}
 sh.sendCommand("dynarc exec-func ext:idoc.remove -params `ap="+AP+"&id=<?php echo $itemInfo['id']; ?>&idocaid="+aid+"&idocid="+id+"`");
}

function idocAdd()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 /* Check if idoc is already installed */
	 for(var c=0; c < IDOCS.length; c++)
	 {
	  if((IDOCS[c].aid == a['aid']) && (IDOCS[c].id == a['id']))
	   return alert("Scheda già esistente. Non puoi aggiungere due schede dello stesso tipo.");
	 }

	 var sh2 = new GShell();
	 sh2.OnOutput = function(){
		 var ul = document.getElementById('idoc-bluenuv');
		 var li = document.createElement('LI');
		 li.id = "idoc-"+a['aid']+"_"+a['id'];
		 li.innerHTML = "<img src='"+ABSOLUTE_URL+"share/widgets/gmart/img/delete-btn.gif' title='Elimina scheda' onclick='idocRemove(this)'/ ><span>"+a['name']+"</span>";
		 li.onclick = function(){idocShow(this);}
		 ul.appendChild(li);

		 var div = document.createElement('DIV');
		 div.className = "idocpage";
		 div.id = "idocspace-"+a['aid']+"_"+a['id'];
		 div.style.display='none';
		 document.getElementById('extended-page').appendChild(div);

		 IDOCS.push({aid:a['aid'], id:a['id'], isdefault:false});

		 idocShow(li);
		}
	 sh2.sendCommand("dynarc exec-func ext:idoc.add -params `ap="+AP+"&id=<?php echo $itemInfo['id']; ?>&idocap=idoc&idocid="+a['id']+"`");
	}
 sh.sendCommand("gframe -f idoc.choice -params `idocct=GMART`");
}

/* PRICELISTS */
function _pricelistsUpdateTotals(r)
{
 var vendorprice = parseCurrency(r.cell['vendorprice'].getValue());
 var cm = parseFloat(r.cell['cm'].getValue());
 var costs = vendorprice ? (vendorprice - ((vendorprice/100)*cm)) : 0;

 var vat = parseFloat(r.cell['vat'].getValue());
 var costsVatIncluded = costs ? (costs + ((costs/100)*vat)) : 0;

 var baseprice = parseCurrency(r.cell['baseprice'].getValue());
 var markuprate = parseFloat(r.cell['markuprate'].getValue());
 var discount = parseFloat(r.cell['discount'].getValue());

 var finalPrice = baseprice ? baseprice + ((baseprice/100)*markuprate) : 0;
 finalPrice = finalPrice ? finalPrice - ((finalPrice/100)*discount) : 0;
 var finalPriceVatIncluded = finalPrice ? finalPrice + ((finalPrice/100)*vat) : 0;

 r.cell['costs'].setValue("<em>&euro;</em>"+formatCurrency(costs,DECIMALS));
 r.cell['costsvatincluded'].setValue("<em>&euro;</em>"+formatCurrency(costsVatIncluded,DECIMALS));

 r.cell['finalprice'].setValue("<em>&euro;</em>"+formatCurrency(finalPrice,DECIMALS));
 r.cell['finalpricevatincluded'].setValue("<em>&euro;</em>"+formatCurrency(finalPriceVatIncluded,DECIMALS));
}

function _basepriceChange(ed)
{
 for(var c=1; c < PRICELISTSTB.O.rows.length; c++)
 {
  PRICELISTSTB.O.rows[c].cell['baseprice'].setValue(ed.value);
  _pricelistsUpdateTotals(PRICELISTSTB.O.rows[c]);
 }
}

function _vatChange(ed)
{
 for(var c=1; c < PRICELISTSTB.O.rows.length; c++)
 {
  PRICELISTSTB.O.rows[c].cell['vat'].setValue(ed.value);
  _pricelistsUpdateTotals(PRICELISTSTB.O.rows[c]);
 }
}

function pricelistCheckChange(cb)
{
 var r = cb.parentNode.parentNode;
 r.className = cb.checked ? ((r.rowIndex-1) %2 ? "row0" : "row1") : "unselected";
}

function copyToClipboard(clipboardId)
{
 if(!clipboardId)
 {
  var cpName = prompt("Inserisci un titolo per gli appunti");
  if(!cpName)
   return;
 }

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 gframe_shotmessage("Copy to clipboard",a,"COPYTOCLIPBOARD");
	}
 if(clipboardId)
  sh.sendCommand("dynarc copy-to-clipboard -clipboardid `"+clipboardId+"` -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>`");
 else
  sh.sendCommand("dynarc copy-to-clipboard -clipboard `"+cpName+"` -tag products -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>`");
}

function addVendor()
{
 var r = VENDORSTB.AddRow();
 r.edit();
}

function deleteSelectedVendors()
{
 var list = VENDORSTB.GetSelectedRows();
 if(!list.length)
  return alert("Nessuna riga è stata selezionata");

 if(!confirm("Sei sicuro di voler rimuovere i fornitori selezionati dalla lista?"))
  return;

 for(var c=0; c < list.length; c++)
  list[c].remove();
}

function addCP()
{
 var r = CPTB.AddRow();
 r.edit();
}

function deleteSelectedCP()
{
 var list = CPTB.GetSelectedRows();
 if(!list.length)
  return alert("Nessuna riga è stata selezionata");

 if(!confirm("Sei sicuro di voler rimuovere le righe selezionate?"))
  return;

 for(var c=0; c < list.length; c++)
  list[c].remove();
}

function _custompricingUpdateTotals(r)
{
 var baseprice = parseCurrency(r.cell['baseprice'].getValue());
 var discount = r.cell['discount'].getValue();
 var discountPerc = 0;
 var discountInc = 0;

 if(!discount)
  discount = 0;
 else
 {
  if(discount.indexOf("%") > 0)
   discountPerc = parseFloat(discount);
  else
   discountInc = parseFloat(discount);
 }

 var vat = parseFloat(r.cell['vat'].getValue());

 if(discountInc)
  var finalPrice = baseprice ? baseprice - discountInc : 0;
 else
  var finalPrice = baseprice ? baseprice - ((baseprice/100)*discountPerc) : 0;
 var finalPriceVatIncluded = finalPrice ? finalPrice + ((finalPrice/100)*vat) : 0;

 r.cell['finalprice'].setValue("<em>&euro;</em>"+formatCurrency(finalPrice,DECIMALS));
 r.cell['finalpricevatincluded'].setValue("<em>&euro;</em>"+formatCurrency(finalPriceVatIncluded,DECIMALS));
}

function customizePricelists()
{
 var sh = new GShell();
 sh.sendCommand("gframe -f gmart/customize-pricelists -params `ap="+AP+"`");
}

/*** VARIANTS ***/
function showVariantPage(page,obj)
{
 if(page == ACTIVE_VARIANT_TAB_PAGE)
  return;

 var li = obj;
 var ul = li.parentNode;
 var list = ul.getElementsByTagName('LI');
 for(var c=0; c < list.length; c++)
 {
  list[c].className = (list[c] == li) ? "selected" : "";
 }
 document.getElementById("variant-"+ACTIVE_VARIANT_TAB_PAGE+"-page").style.display = "none";
 document.getElementById("variant-"+page+"-page").style.display = "";
 ACTIVE_VARIANT_TAB_PAGE = page;

 /* CHANGELOG: 06-11-2014 */
 if(page == "pricesbyqty")
  updateVariantOB();
 else if(page == "codesandprices")
  updateVariantCodes();
 /* EOF - CHANGELOG */
}

/* CHANGELOG: 08-11-2014 */
function updateVariantOB(r)
{
 // aggiorna la lista delle varianti sui menu a tendina
 var PBQTB = document.getElementById('variant-pricesbyqty-list');
 if(r)
 {
  var ed1 = r.cells[0].getElementsByTagName('DIV')[0];
  var ed2 = r.cells[1].getElementsByTagName('DIV')[0];
  ed1.oldvalue = ed1.obH.getValue();
  ed2.oldvalue = ed2.obH.getValue();
  var opts = new Array();
  // Colors
  var list = getVariantColors();
  for(var i=0; i < list.length; i++)
   opts.push(list[i].name);

  if(opts.length)
   opts.push(0); // separator

  // Tint
  var list = getVariantTint();
  for(var i=0; i < list.length; i++)
   opts.push(list[i].name);

  ed1.obH.setOptions(opts,true);

  // --- TAGLIE E MISURE --- //
  var opts = new Array();
  // Taglie
  var list = getVariantSize();
  for(var i=0; i < list.length; i++)
   opts.push(list[i].name);

  if(opts.length)
   opts.push(0); // separator

  // Misure
  var list = getVariantDim();
  for(var i=0; i < list.length; i++)
   opts.push(list[i].name);

  if(opts.length)
   opts.push(0); // separator

  // Altro
  var list = getVariantOther();
  for(var i=0; i < list.length; i++)
   opts.push(list[i].name);

  ed2.obH.setOptions(opts,true);

  if(opts.length && (ed1.obH.getValue() == "") && (ed2.obH.getValue() == ""))
  {
   /* Elimina la riga */
   if(r.cells[2].getElementsByTagName('INPUT')[0].value || r.cells[2].getElementsByTagName('INPUT')[1].value)
   {
    if(r.id)
     DELETED_PRICESBYQTY.push(r.id);
    PBQTB.deleteRow(r.rowIndex)
   }
  }
  else if((ed1.oldvalue != ed1.obH.getValue()) || (ed2.oldvalue != ed2.obH.getValue()))
   r.changed = true;
 }
 else
 {
  for(var c=1; c < PBQTB.rows.length; c++)
   updateVariantOB(PBQTB.rows[c]);
 }
}

function updateVariantCodes()
{
 var opts = new Array();
 // Colors
 var list = getVariantColors();
 for(var i=0; i < list.length; i++)
  opts.push({name:list[i].name, type:'color'});
 // Tint
 var list = getVariantTint();
 for(var i=0; i < list.length; i++)
  opts.push({name:list[i].name, type:'tint'});
 // Taglie
 var list = getVariantSize();
 for(var i=0; i < list.length; i++)
  opts.push({name:list[i].name, type:'size'});
 // Misure
 var list = getVariantDim();
 for(var i=0; i < list.length; i++)
  opts.push({name:list[i].name, type:'dim'});
 // Altro
 var list = getVariantOther();
 for(var i=0; i < list.length; i++)
  opts.push({name:list[i].name, type:'other'});

 var optnames = new Array();
 var VARCODESTB = document.getElementById('variant-codesandprices-list');
 for(var c=0; c < opts.length; c++)
 {
  var opt = opts[c];
  optnames.push(opt.name);
  var exists = false;
  for(var i=1; i < VARCODESTB.rows.length; i++)
  {
   var r = VARCODESTB.rows[i];
   if(r.cells[0].getElementsByTagName('INPUT')[0].value == opt.name)
   {
	exists = true;
	break;
   }
  }
  if(!exists)
  {
   var r = VARCODESTB.insertRow(-1);
   r.setAttribute('type',opt.type);
   r.insertCell(-1).innerHTML = "<input type='text' class='edit' style='width:140px' value=\""+opt.name+"\" readonly='true'/"+">";
   r.insertCell(-1).innerHTML = "<input type='text' class='edit' style='width:100px'/"+">";
   r.insertCell(-1).innerHTML = "<input type='text' class='edit' style='width:100px'/"+">";
   r.insertCell(-1).innerHTML = "<input type='text' class='edit' style='width:30px'/"+"> %";
   r.insertCell(-1).innerHTML = "<input type='text' class='edit' style='width:70px;text-align:right'/"+"> &euro;";
   r.insertCell(-1).innerHTML = "<input type='text' class='edit' style='width:80px'/"+" maxlength='40'>";
   r.insertCell(-1).innerHTML = "<input type='text' class='edit' style='width:80px'/"+" maxlength='10'>";
   r.insertCell(-1).innerHTML = "<input type='text' class='edit' style='width:80px'/"+" maxlength='13'>";
   r.insertCell(-1).innerHTML = "<input type='text' class='edit' style='width:80px'/"+" maxlength='40'>";
   r.insertCell(-1).innerHTML = "<input type='text' class='edit' style='width:80px'/"+" maxlength='14'>";
   r.insertCell(-1).innerHTML = "<input type='text' class='edit' style='width:80px'/"+" maxlength='10'>";

   r.cells[1].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}
   r.cells[2].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}
   r.cells[3].getElementsByTagName('INPUT')[0].onchange = function(){
	 this.parentNode.parentNode.changed = true;
	 this.parentNode.parentNode.cells[4].getElementsByTagName('INPUT')[0].value = "";
	}
   r.cells[4].getElementsByTagName('INPUT')[0].onchange = function(){
	 this.parentNode.parentNode.changed = true; 
	 this.value = formatCurrency(parseCurrency(this.value));
	 this.parentNode.parentNode.cells[3].getElementsByTagName('INPUT')[0].value = "";
	}
   r.cells[5].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}
   r.cells[6].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}
   r.cells[7].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}
   r.cells[8].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}
   r.cells[9].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}
   r.cells[10].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}

  }
 }

 // Elimina tutte le righe delle varianti eliminate //
 for(var i=1; i < VARCODESTB.rows.length; i++)
 {
  var r = VARCODESTB.rows[i];
  var val = r.cells[0].getElementsByTagName('INPUT')[0].value;
  if(optnames.indexOf(val) < 0)
  {
   if(r.id)
    DELETED_VARCODES.push(r.id);
   VARCODESTB.deleteRow(r.rowIndex);
  }
 }
}

/* EOF - CHANGELOG */

/* VARIANT - COLORS */
function addVariantColor(obj)
{
 var container = document.getElementById('variant-colors-container');
 var div = document.createElement('DIV');
 div.className = "coloritem";
 var html = "<a href='#' class='deletecolor' onclick='deleteVariantColor(this)'><img src='"+ABSOLUTE_URL+"share/widgets/gmart/img/small-delete.png' title='Elimina'/ ></a>";
 html+= "<div class='colorblock' style='background:"+obj.style.background+"' onclick='renameVariantColor(this)' title='Clicca per rinominare'>&nbsp;</div>";
 html+= "<div class='colortitle' onclick='renameVariantColor(this)' title='Clicca per rinominare'>"+obj.title+"</div>";
 div.innerHTML = html;
 container.appendChild(div);
 /* CHANGELOG: 17-11-2014 */
 updateVariantOB();
 updateVariantCodes();
 /* EOF - CHANGELOG */
}

function renameVariantColor(obj)
{
 var div = obj.parentNode;
 var titO = div.getElementsByTagName('DIV')[1];
 var title = prompt("Rinomina questo colore",titO.innerHTML);
 if(!title)
  return;
 titO.innerHTML = title;
 /* CHANGELOG: 17-11-2014 */
 updateVariantOB();
 updateVariantCodes();
 /* EOF - CHANGELOG */
}

function deleteVariantColor(obj)
{
 if(!confirm("Sei sicuro di voler rimuovere questo colore?"))
  return;
 var div = obj.parentNode;
 div.parentNode.removeChild(div);
 /* CHANGELOG: 17-11-2014 */
 updateVariantOB();
 updateVariantCodes();
 /* EOF - CHANGELOG */
}

/* VARIANT - TINT */
function addVariantTint()
{
 var title = prompt("Inserisci un titolo","nuova variante");
 if(!title)
  return;

 var tb = document.getElementById('variant-tint-container');
 var r = tb.insertRow(-1);
 r.insertCell(-1).innerHTML = "<div class='tint-thumb' title=\"Clicca per modificare l'immagine\" onclick='changeTintImg(this)'><div class='tint-thumb-img' style='background-image:url(img/photo.png)'>&nbsp;</div></div>";
 r.insertCell(-1).innerHTML = title;
 r.insertCell(-1).innerHTML = "<a href='#' title='Elimina' class='tint-delete' onclick='deleteVariantTint(this)'><img src='"+ABSOLUTE_URL+"share/widgets/gmart/img/red_delete.png' border='0'/ ></a>";

 r.cells[0].style.width = "60px";
 r.cells[1].className = "tint-title";
 r.cells[1].onclick = function(){renameVariantTint(this);}
 r.cells[2].style.width = "60px";
 r.cells[2].style.textAlign = "center";

 /* CHANGELOG: 17-11-2014 */
 updateVariantOB();
 updateVariantCodes();
 /* EOF - CHANGELOG */
}

function renameVariantTint(td)
{
 var title = prompt("Rinomina",td.innerHTML);
 if(!title)
  return;
 td.innerHTML = title;
 /* CHANGELOG: 17-11-2014 */
 updateVariantOB();
 updateVariantCodes();
 /* EOF - CHANGELOG */
}

function deleteVariantTint(img)
{
 if(!confirm("Sei sicuro di voler rimuovere questa tinta/fantasia ?"))
  return;
 var r = img.parentNode.parentNode;
 var tb = document.getElementById('variant-tint-container');
 tb.deleteRow(r.rowIndex);
 /* CHANGELOG: 17-11-2014 */
 updateVariantOB();
 updateVariantCodes();
 /* EOF - CHANGELOG */
}

function changeTintImg(div)
{
 var div = div.getElementsByTagName('DIV')[0];

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a || !a['files']) return gframe_opacity(100);

	 var dstPath = "image/gmart/";
	 var d = new Date();
	 var thumbIDX = d.getTime();
	 var file = a['files'][0];
	 var orig = "tmp/"+file['name']+"."+file['extension'];
	 var dest = dstPath+"tint-<?php echo $itemInfo['id']; ?>-"+thumbIDX+"."+file['extension'];

	 var sh2 = new GShell();
	 sh2.OnFinish = function(oo,aa){
		 if(!aa) return;
		 div.style.backgroundImage="url(<?php echo $_ABSOLUTE_URL; ?>"+aa['url']+")";
		}

     switch(a['mode'])
	 {
	  case 'UPLOAD' : sh2.sendCommand("mv `"+orig+"` `"+dest+"` && gd resize -i `"+dest+"` -w 128"); break;
	  case 'FROM_SERVER' : sh2.sendCommand("gd resize -i `"+orig+"` -o `"+dest+"` -w 128"); break;
	 }
	}

 sh.sendCommand("gframe -f imageupload -params `destpath=tmp`");
}

/* VARIANT - SIZES */
function variantSizeChoice(sel)
{
 document.getElementById('variant-sizes-container').innerHTML = "";
 if(!sel.value || (sel.value == ""))
  return;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a || !a['items']) return;
	 for(var c=0; c < a['items'].length; c++)
	 {
	  var div = document.createElement('DIV');
	  div.className = "sizeitem";
	  var html = "<a href='#' class='deletesize' onclick='deleteVariantSize(this)'><img src='"+ABSOLUTE_URL+"share/widgets/gmart/img/small-delete.png' title='Elimina'/ ></a>";
	  html+= "<div class='sizetitle' onclick='renameVariantSize(this)'>"+a['items'][c]['name']+"</div>";
	  html+= "<div class='sizecheck'><input type='checkbox' checked='true'/ ></div>";
	  div.innerHTML = html;
	  document.getElementById('variant-sizes-container').appendChild(div);
	 }
	}
 sh.sendCommand("dynarc exec-func ext:variants.getSizeTypeInfo -params `id="+sel.value+"`");
}

function addVariantSize()
{
 var title = prompt("Inserisci un titolo");
 if(!title)
  return;

 var div = document.createElement('DIV');
 div.className = "sizeitem";
 var html = "<a href='#' class='deletesize' onclick='deleteVariantSize(this)'><img src='"+ABSOLUTE_URL+"share/widgets/gmart/img/small-delete.png' title='Elimina'/ ></a>";
 html+= "<div class='sizetitle' onclick='renameVariantSize(this)'>"+title+"</div>";
 html+= "<div class='sizecheck'><input type='checkbox' checked='true'/ ></div>";
 div.innerHTML = html;
 
 document.getElementById('variant-sizes-container').appendChild(div);
 /* CHANGELOG: 17-11-2014 */
 updateVariantOB();
 updateVariantCodes();
 /* EOF - CHANGELOG */
}

function renameVariantSize(div)
{
 var title = prompt("Rinomina",div.innerHTML);
 if(!title)
  return;
 div.innerHTML = title;
 /* CHANGELOG: 17-11-2014 */
 updateVariantOB();
 updateVariantCodes();
 /* EOF - CHANGELOG */
}

function deleteVariantSize(a)
{
 if(!confirm("Sei sicuro di voler eliminare questa misura?"))
  return;
 var div = a.parentNode;
 div.parentNode.removeChild(div);
 /* CHANGELOG: 17-11-2014 */
 updateVariantOB();
 updateVariantCodes();
 /* EOF - CHANGELOG */
}

function saveVariantSizeType(title)
{
 var xml = "<xml>";
 var div = document.getElementById('variant-sizes-container');
 var fc = div.getElementsByTagName('DIV')[0];
 if(fc)
 {
  xml+= "<size name=\""+fc.getElementsByTagName('DIV')[0].innerHTML.replace("&","&amp;")+"\"/"+">";
  while(fc = fc.nextSibling)
  {
   if(fc && (fc.tagName == "DIV"))
    xml+= "<size name=\""+fc.getElementsByTagName('DIV')[0].innerHTML.replace("&","&amp;")+"\"/"+">";
  }
 }
 xml+= "</xml>";

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var opt = document.createElement('OPTION');
	 opt.value = a['id'];
	 opt.innerHTML = a['name'];
	 document.getElementById('variant-sizes-available').appendChild(opt);
	 document.getElementById('variant-sizes-available').value = a['id'];
	 alert("Salvataggio completato!");
	}

 if(title)
  sh.sendSudoCommand("dynarc exec-func ext:variants.addSizeType -params `name="+title+"` --extra-param xml --extra-value `"+xml+"`");
 else
 {
  var sel = document.getElementById('variant-sizes-available');
  if(sel.value && (sel.value != ""))
  {
   if(!confirm("Confermi le modifiche per la tipologia '"+sel.options[sel.selectedIndex].innerHTML+"' ?"))
	return;
   sh.sendSudoCommand("dynarc exec-func ext:variants.editSizeType -params `id="+sel.value+"` --extra-param xml --extra-value `"+xml+"`");
  }
  else
  {
   var title = prompt("Inserisci un titolo da dare alla nuova tipologia di taglia");
   if(!title)
    return;
   sh.sendSudoCommand("dynarc exec-func ext:variants.addSizeType -params `name="+title+"` --extra-param xml --extra-value `"+xml+"`");
  }  
 }
}

function saveAsVariantSizeType()
{
 var title = prompt("Inserisci un titolo da dare alla nuova tipologia di taglia");
 if(!title)
  return;
 saveVariantSizeType(title);
}

function deleteVariantSizeType()
{
 var sel = document.getElementById('variant-sizes-available');
 if(!sel.value || (sel.value == ""))
  return alert("Non è stata selezionata nessuna tipologia");

 if(!confirm("Sei sicuro di voler eliminare questa tipologia?"))
  return;
 
 var sh = new GShell();
 sh.OnOutput = function(){
	 var opt = sel.options[sel.selectedIndex];
	 sel.removeChild(opt);
	 sel.value = "";
	 document.getElementById('variant-size-container').innerHTML = "";
	}
 sh.sendSudoCommand("dynarc exec-func ext:variants.deleteSizeType -params `id="+sel.value+"`");
}

function renameVariantSizeType()
{
 var sel = document.getElementById('variant-sizes-available');
 if(!sel.value || (sel.value == ""))
  return alert("Non è stata selezionata nessuna tipologia");
 
 var title = prompt("Rinomina questa tipologia",sel.options[sel.selectedIndex].innerHTML);
 if(!title)
  return;
 var sh = new GShell();
 sh.OnOutput = function(){
	 alert("Salvataggio completato!");
	 sel.options[sel.selectedIndex].innerHTML = title;
	}
 sh.sendSudoCommand("dynarc exec-func ext:variants.editSizeType -params `id="+sel.value+"&name="+title+"`");
}

/* VARIANT - DIMENSIONS */
function variantDimChoice(sel)
{
 document.getElementById('variant-dim-container').innerHTML = "";
 if(!sel.value || (sel.value == ""))
  return;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a || !a['items']) return;
	 for(var c=0; c < a['items'].length; c++)
	 {
	  var div = document.createElement('DIV');
	  div.className = "dimitem";
	  var html = "<a href='#' class='deletedim' onclick='deleteVariantDim(this)'><img src='"+ABSOLUTE_URL+"share/widgets/gmart/img/small-delete.png' title='Elimina'/ ></a>";
	  html+= "<div class='dimtitle' onclick='renameVariantDim(this)'>"+a['items'][c]['name']+"</div>";
	  div.innerHTML = html;
	  document.getElementById('variant-dim-container').appendChild(div);
	 }
	}
 sh.sendCommand("dynarc exec-func ext:variants.getDimTypeInfo -params `id="+sel.value+"`");
}

function addVariantDim()
{
 var title = prompt("Inserisci un titolo");
 if(!title)
  return;

 var div = document.createElement('DIV');
 div.className = "dimitem";
 var html = "<a href='#' class='deletedim' onclick='deleteVariantDim(this)'><img src='"+ABSOLUTE_URL+"share/widgets/gmart/img/small-delete.png' title='Elimina'/ ></a>";
 html+= "<div class='dimtitle' onclick='renameVariantDim(this)'>"+title+"</div>";
 div.innerHTML = html;
 
 document.getElementById('variant-dim-container').appendChild(div);
 /* CHANGELOG: 17-11-2014 */
 updateVariantOB();
 updateVariantCodes();
 /* EOF - CHANGELOG */
}

function renameVariantDim(div)
{
 var title = prompt("Rinomina",div.innerHTML);
 if(!title)
  return;
 div.innerHTML = title;
 /* CHANGELOG: 17-11-2014 */
 updateVariantOB();
 updateVariantCodes();
 /* EOF - CHANGELOG */
}

function deleteVariantDim(a)
{
 if(!confirm("Sei sicuro di voler eliminare questa misura?"))
  return;
 var div = a.parentNode;
 div.parentNode.removeChild(div);
 /* CHANGELOG: 17-11-2014 */
 updateVariantOB();
 updateVariantCodes();
 /* EOF - CHANGELOG */
}

function saveVariantDimType(title)
{
 var xml = "<xml>";
 var div = document.getElementById('variant-dim-container');
 var fc = div.getElementsByTagName('DIV')[0];
 if(fc)
 {
  xml+= "<dim name=\""+fc.getElementsByTagName('DIV')[0].innerHTML.replace("&","&amp;")+"\"/"+">";
  while(fc = fc.nextSibling)
  {
   if(fc && (fc.tagName == "DIV"))
    xml+= "<dim name=\""+fc.getElementsByTagName('DIV')[0].innerHTML.replace("&","&amp;")+"\"/"+">";
  }
 }
 xml+= "</xml>";

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var opt = document.createElement('OPTION');
	 opt.value = a['id'];
	 opt.innerHTML = a['name'];
	 document.getElementById('variant-dim-available').appendChild(opt);
	 document.getElementById('variant-dim-available').value = a['id'];
	 alert("Salvataggio completato!");
	}

 if(title)
  sh.sendSudoCommand("dynarc exec-func ext:variants.addDimType -params `name="+title+"` --extra-param xml --extra-value `"+xml+"`");
 else
 {
  var sel = document.getElementById('variant-dim-available');
  if(sel.value && (sel.value != ""))
  {
   if(!confirm("Confermi le modifiche per lo standard '"+sel.options[sel.selectedIndex].innerHTML+"' ?"))
	return;
   sh.sendSudoCommand("dynarc exec-func ext:variants.editDimType -params `id="+sel.value+"` --extra-param xml --extra-value `"+xml+"`");
  }
  else
  {
   var title = prompt("Inserisci un titolo da dare al nuovo tipo di standard");
   if(!title)
    return;
   sh.sendSudoCommand("dynarc exec-func ext:variants.addDimType -params `name="+title+"` --extra-param xml --extra-value `"+xml+"`");
  }  
 }
}

function saveAsVariantDimType()
{
 var title = prompt("Inserisci un titolo da dare al nuovo tipo di standard");
 if(!title)
  return;
 saveVariantDimType(title);
}

function deleteVariantDimType()
{
 var sel = document.getElementById('variant-dim-available');
 if(!sel.value || (sel.value == ""))
  return alert("Non è stato selezionato alcun standard");

 if(!confirm("Sei sicuro di voler eliminare questo standard?"))
  return;
 
 var sh = new GShell();
 sh.OnOutput = function(){
	 var opt = sel.options[sel.selectedIndex];
	 sel.removeChild(opt);
	 sel.value = "";
	 document.getElementById('variant-dim-container').innerHTML = "";
	}
 sh.sendSudoCommand("dynarc exec-func ext:variants.deleteDimType -params `id="+sel.value+"`");
}

function renameVariantDimType()
{
 var sel = document.getElementById('variant-dim-available');
 if(!sel.value || (sel.value == ""))
  return alert("Non è stato selezionato alcun standard");
 
 var title = prompt("Rinomina questo standard",sel.options[sel.selectedIndex].innerHTML);
 if(!title)
  return;
 var sh = new GShell();
 sh.OnOutput = function(){
	 alert("Salvataggio completato!");
	 sel.options[sel.selectedIndex].innerHTML = title;
	}
 sh.sendSudoCommand("dynarc exec-func ext:variants.editDimType -params `id="+sel.value+"&name="+title+"`");
}

/* VARIANT - OTHER */
function addVariantOther()
{
 var title = prompt("Inserisci un titolo");
 if(!title)
  return;

 var div = document.createElement('DIV');
 div.className = "otheritem";
 var html = "<a href='#' class='deleteother' onclick='deleteVariantOther(this)'><img src='"+ABSOLUTE_URL+"share/widgets/gmart/img/small-delete.png' title='Elimina'/ ></a>";
 html+= "<div class='othertitle' onclick='renameVariantOther(this)'>"+title+"</div>";
 div.innerHTML = html;
 
 document.getElementById('variant-other-container').appendChild(div);

 /* CHANGELOG: 17-11-2014 */
 updateVariantOB();
 updateVariantCodes();
 /* EOF - CHANGELOG */
}

function renameVariantOther(div)
{
 var title = prompt("Rinomina",div.innerHTML);
 if(!title)
  return;
 div.innerHTML = title;
 /* CHANGELOG: 17-11-2014 */
 updateVariantOB();
 updateVariantCodes();
 /* EOF - CHANGELOG */
}

function deleteVariantOther(a)
{
 if(!confirm("Sei sicuro di voler eliminare questa variante?"))
  return;
 var div = a.parentNode;
 div.parentNode.removeChild(div);
 /* CHANGELOG: 17-11-2014 */
 updateVariantOB();
 updateVariantCodes();
 /* EOF - CHANGELOG */
}


/* VARIANT - SAVE FUNCTIONS */
function saveVariants(sh)
{
 var xmlCol = saveColors();
 var xmlTint = saveTint();
 var sizId = document.getElementById('variant-sizes-available').value;
 var xmlSiz = saveSizes();
 var dimId = document.getElementById('variant-dim-available').value;
 var xmlDim = saveDim();
 var xmlOther = saveOther();

 sh.sendCommand("dynarc edit-item -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>` -extset `variants.colors='<![CDATA["+xmlCol+"]]>',tint='<![CDATA["+xmlTint+"]]>',sizid='"+sizId+"',siz='<![CDATA["+xmlSiz+"]]>',dimid='"+dimId+"',dim='<![CDATA["+xmlDim+"]]>',other='<![CDATA["+xmlOther+"]]>'`");

 /* CHANGELOG: 07-11-2014 */
 // salvataggio codici varianti e delle fasce prezzo per quantità
 saveVarCodes(sh);
 savePricesByQty(sh);
 /* EOF - CHANGELOG */
}

/* CHANGELOG: 06-11-2014 */
function saveVarCodes(sh)
{
 var VARCODESTB = document.getElementById('variant-codesandprices-list');

 for(var c=0; c < DELETED_VARCODES.length; c++)
  sh.sendCommand("dynarc edit-item -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>` -extunset `varcodes.id='"+DELETED_VARCODES[c]+"'`");

 for(var c=1; c < VARCODESTB.rows.length; c++)
 {
  var r = VARCODESTB.rows[c];
  if(!r.changed)
   continue;
  var variantType = r.getAttribute('type');
  var variantName = r.cells[0].getElementsByTagName('INPUT')[0].value;
  var code = r.cells[1].getElementsByTagName('INPUT')[0].value;
  var barcode = r.cells[2].getElementsByTagName('INPUT')[0].value;
  var mrate = r.cells[3].getElementsByTagName('INPUT')[0].value ? parseFloat(r.cells[3].getElementsByTagName('INPUT')[0].value) : 0;
  var finalPrice = r.cells[4].getElementsByTagName('INPUT')[0].value ? parseCurrency(r.cells[4].getElementsByTagName('INPUT')[0].value) : 0;
  var sku = r.cells[5].getElementsByTagName('INPUT')[0].value;
  var codeASIN = r.cells[6].getElementsByTagName('INPUT')[0].value;
  var codeEAN = r.cells[7].getElementsByTagName('INPUT')[0].value;
  var codeGCID = r.cells[8].getElementsByTagName('INPUT')[0].value;
  var codeGTIN = r.cells[9].getElementsByTagName('INPUT')[0].value;
  var codeUPC = r.cells[10].getElementsByTagName('INPUT')[0].value;

  // se la riga è vuota la elimina
  if(r.id && !code && !barcode && !mrate && !finalPrice)
   sh.sendCommand("dynarc edit-item -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>` -extunset `varcodes.id='"+r.id+"'`");
  else
   sh.sendCommand("dynarc edit-item -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>` -extset `varcodes."
	+(r.id ? "id="+r.id+"," : "")+"variant='''"+variantName+"''',type='"+variantType+"',code='"+code+"',barcode='"+barcode+"',mrate='"+mrate+"',finalprice='"+finalPrice+"',sku='"+sku+"',asin='"+codeASIN+"',ean='"+codeEAN+"',gcid='"+codeGCID+"',gtin='"+codeGTIN+"',upc='"+codeUPC+"'`");
 }
}

function savePricesByQty(sh)
{
 var PBQTB = document.getElementById('variant-pricesbyqty-list');

 for(var c=0; c < DELETED_PRICESBYQTY.length; c++)
  sh.sendCommand("dynarc edit-item -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>` -extunset `pricesbyqty.id='"+DELETED_PRICESBYQTY[c]+"'`");

 for(var c=1; c < PBQTB.rows.length; c++)
 {
  var r = PBQTB.rows[c];
  if(!r.changed)
   continue;
  var ed1 = r.cells[0].getElementsByTagName('DIV')[0];
  var ed2 = r.cells[1].getElementsByTagName('DIV')[0];
  var qtyFrom = r.cells[2].getElementsByTagName('INPUT')[0].value ? r.cells[2].getElementsByTagName('INPUT')[0].value : 0;
  var qtyTo = r.cells[2].getElementsByTagName('INPUT')[1].value ? r.cells[2].getElementsByTagName('INPUT')[1].value : 0;
  var discPerc = parseFloat(r.cells[3].getElementsByTagName('INPUT')[0].value ? r.cells[3].getElementsByTagName('INPUT')[0].value : 0);
  var finalPrice = parseCurrency(r.cells[4].getElementsByTagName('INPUT')[0].value);

  var colors = "";
  var sizes = "";

  var list = ed1.obH.getValue(true);
  for(var i=0; i < list.length; i++)
   colors+= ",["+list[i].value+"]";
  if(colors != "") colors = colors.substr(1);

  var list = ed2.obH.getValue(true);
  for(var i=0; i < list.length; i++)
   sizes+= ",["+list[i].value+"]";
  if(sizes != "") sizes = sizes.substr(1);

  sh.sendCommand("dynarc edit-item -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>` -extset `pricesbyqty."
	+(r.id ? "id="+r.id+"," : "")+"colors='''"+colors+"''',sizes='''"+sizes+"''',qtyfrom='"+qtyFrom+"',qtyto='"+qtyTo+"',discperc='"+discPerc+"',finalprice='"+finalPrice+"'`");
 } 
}

function getVariantColors()
{
 var ret = new Array();
 var div = document.getElementById('variant-colors-container');
 var fc = div.getElementsByTagName('DIV')[0];
 if(fc)
 {
  ret.push({name:fc.getElementsByTagName('DIV')[1].innerHTML, hexvalue:fc.getElementsByTagName('DIV')[0].style.backgroundColor});
  while(fc = fc.nextSibling)
  {
   if(fc && (fc.tagName == "DIV"))
	ret.push({name:fc.getElementsByTagName('DIV')[1].innerHTML, hexvalue:fc.getElementsByTagName('DIV')[0].style.backgroundColor});
  }
 }
 return ret;
}
/* EOF - CHANGELOG */

function saveColors()
{
 var xml = "<xml>";
 var div = document.getElementById('variant-colors-container');
 var fc = div.getElementsByTagName('DIV')[0];
 if(fc)
 {
  xml+= "<color name=\""+fc.getElementsByTagName('DIV')[1].innerHTML.replace("&","&amp;")+"\" hexvalue=\""+fc.getElementsByTagName('DIV')[0].style.backgroundColor+"\"/"+">";
  while(fc = fc.nextSibling)
  {
   if(fc && (fc.tagName == "DIV"))
   xml+= "<color name=\""+fc.getElementsByTagName('DIV')[1].innerHTML.replace("&","&amp;")+"\" hexvalue=\""+fc.getElementsByTagName('DIV')[0].style.backgroundColor+"\"/"+">";
  }
 }
 xml+= "</xml>";
 return xml;
}

/* CHANGELOG: 07-11-2014 */
function getVariantTint()
{
 var ret = new Array();
 var tb = document.getElementById('variant-tint-container');
 for(var c=0; c < tb.rows.length; c++)
 {
  var r = tb.rows[c];
  var div = r.cells[0].getElementsByTagName('DIV')[0].getElementsByTagName('DIV')[0];
  var fileName = div.style.backgroundImage;
  if(fileName == 'url("img/photo.png")')
   fileName = "";
  else if(fileName.indexOf("url(") > -1)
  {
   fileName = fileName.replace('url("', '');
   fileName = fileName.replace('")', '');
   fileName = fileName.replace(ABSOLUTE_URL, "");
  }
  ret.push({name:r.cells[1].innerHTML, thumb:fileName});
 }
 return ret; 
}
/* EOF - CHANGELOG */

function saveTint()
{
 var xml = "<xml>";
 var tb = document.getElementById('variant-tint-container');
 for(var c=0; c < tb.rows.length; c++)
 {
  var r = tb.rows[c];
  var div = r.cells[0].getElementsByTagName('DIV')[0].getElementsByTagName('DIV')[0];
  var fileName = div.style.backgroundImage;
  if(fileName == 'url("img/photo.png")')
   fileName = "";
  else if(fileName.indexOf("url(") > -1)
  {
   fileName = fileName.replace('url("', '');
   fileName = fileName.replace('")', '');
   fileName = fileName.replace(ABSOLUTE_URL, "");
  }
  xml+= "<tint name=\""+r.cells[1].innerHTML.replace("&","&amp;")+"\" thumb=\""+fileName+"\"/"+">";
 }
 xml+= "</xml>";

 return xml;
}

/* CHANGELOG: 07-11-2014 */
function getVariantSize()
{
 var ret = new Array();
 var div = document.getElementById('variant-sizes-container');
 var fc = div.getElementsByTagName('DIV')[0];
 if(fc)
 {
  if(fc.getElementsByTagName('DIV')[1].getElementsByTagName('INPUT')[0].checked == true)
   ret.push({name:fc.getElementsByTagName('DIV')[0].innerHTML});
  while(fc = fc.nextSibling)
  {
   if(fc && (fc.tagName == "DIV") && (fc.getElementsByTagName('DIV')[1].getElementsByTagName('INPUT')[0].checked == true))
    ret.push({name:fc.getElementsByTagName('DIV')[0].innerHTML});
  }
 }
 return ret;
}
/* EOF - CHANGELOG */

function saveSizes()
{
 var xml = "<xml>";
 var div = document.getElementById('variant-sizes-container');
 var fc = div.getElementsByTagName('DIV')[0];
 if(fc)
 {
  if(fc.getElementsByTagName('DIV')[1].getElementsByTagName('INPUT')[0].checked == true)
   xml+= "<size name=\""+fc.getElementsByTagName('DIV')[0].innerHTML.replace("&","&amp;")+"\"/"+">";
  while(fc = fc.nextSibling)
  {
   if(fc && (fc.tagName == "DIV") && (fc.getElementsByTagName('DIV')[1].getElementsByTagName('INPUT')[0].checked == true))
    xml+= "<size name=\""+fc.getElementsByTagName('DIV')[0].innerHTML.replace("&","&amp;")+"\"/"+">";
  }
 }
 xml+= "</xml>";
 return xml;
}

/* CHANGELOG: 07-11-2014 */
function getVariantDim()
{
 var ret = new Array();
 var div = document.getElementById('variant-dim-container');
 var fc = div.getElementsByTagName('DIV')[0];
 if(fc)
 {
  ret.push({name:fc.getElementsByTagName('DIV')[0].innerHTML});
  while(fc = fc.nextSibling)
  {
   if(fc && (fc.tagName == "DIV"))
    ret.push({name:fc.getElementsByTagName('DIV')[0].innerHTML});
  }
 }
 return ret;
}
/* EOF - CHANGELOG */

function saveDim()
{
 var xml = "<xml>";
 var div = document.getElementById('variant-dim-container');
 var fc = div.getElementsByTagName('DIV')[0];
 if(fc)
 {
  xml+= "<dim name=\""+fc.getElementsByTagName('DIV')[0].innerHTML.replace("&","&amp;")+"\"/"+">";
  while(fc = fc.nextSibling)
  {
   if(fc && (fc.tagName == "DIV"))
    xml+= "<dim name=\""+fc.getElementsByTagName('DIV')[0].innerHTML.replace("&","&amp;")+"\"/"+">";
  }
 }
 xml+= "</xml>";
 return xml;
}

/* CHANGELOG: 07-11-2014 */
function getVariantOther()
{
 var ret = new Array();
 var div = document.getElementById('variant-other-container');
 var fc = div.getElementsByTagName('DIV')[0];
 if(fc)
 {
  ret.push({name:fc.getElementsByTagName('DIV')[0].innerHTML});
  while(fc = fc.nextSibling)
  {
   if(fc && (fc.tagName == "DIV"))
    ret.push({name:fc.getElementsByTagName('DIV')[0].innerHTML});
  }
 }
 return ret;
}
/* EOF - CHANGELOG */

function saveOther()
{
 var xml = "<xml>";
 var div = document.getElementById('variant-other-container');
 var fc = div.getElementsByTagName('DIV')[0];
 if(fc)
 {
  xml+= "<variant name=\""+fc.getElementsByTagName('DIV')[0].innerHTML.replace("&","&amp;")+"\"/"+">";
  while(fc = fc.nextSibling)
  {
   if(fc && (fc.tagName == "DIV"))
    xml+= "<variant name=\""+fc.getElementsByTagName('DIV')[0].innerHTML.replace("&","&amp;")+"\"/"+">";
  }
 }
 xml+= "</xml>";
 return xml;
}

/* CHANGELOG: 08-11-2014 */
function addVariantPriceForQty()
{
 var PBQTB = document.getElementById('variant-pricesbyqty-list');
 var r = PBQTB.insertRow(-1);
 r.insertCell(-1).innerHTML = "<div class='optionbox' style='width:90px'></div>";
 r.insertCell(-1).innerHTML = "<div class='optionbox' style='width:90px'></div>";
 r.insertCell(-1).innerHTML = "da: <input type='text' class='edit' style='width:40px;text-align:center'/"+"> a: <input type='text' class='edit' style='width:40px;text-align:center'/"+">";
 r.cells[2].style.textAlign="center";
 r.insertCell(-1).innerHTML = "<input type='text' class='edit' style='width:30px;text-align:center'/"+"> %";
 r.cells[3].style.textAlign="center";
 r.insertCell(-1).innerHTML = "<input type='text' class='edit' style='width:70px;text-align:right'/"+"> &euro;";
 r.insertCell(-1).innerHTML = "<img src='"+ABSOLUTE_URL+"share/widgets/gmart/img/icon_delete.gif' style='cursor:pointer' title='Rimuovi' onclick='deletePricesByQty(this)'/"+">";

 var ed1 = r.cells[0].getElementsByTagName('DIV')[0];			var ob1 = new OptionBox(ed1);
 var ed2 = r.cells[1].getElementsByTagName('DIV')[0];			var ob2 = new OptionBox(ed2);
  
 r.cells[2].getElementsByTagName('INPUT')[0].onchange = function(){this.parentNode.parentNode.changed = true;}
 r.cells[2].getElementsByTagName('INPUT')[1].onchange = function(){this.parentNode.parentNode.changed = true;}
 r.cells[3].getElementsByTagName('INPUT')[0].onchange = function(){
	 this.parentNode.parentNode.changed = true;
	 this.parentNode.parentNode.cells[4].getElementsByTagName('INPUT')[0].value = "";
	}
 r.cells[4].getElementsByTagName('INPUT')[0].onchange = function(){
	 this.parentNode.parentNode.changed = true; 
	 this.value = formatCurrency(parseCurrency(this.value));
	 this.parentNode.parentNode.cells[3].getElementsByTagName('INPUT')[0].value = "";
	}
 updateVariantOB(r);
}

function deletePricesByQty(img)
{
 // verifica se la riga è vuota //
 var PBQTB = document.getElementById('variant-pricesbyqty-list');
 var r = img.parentNode.parentNode;

 if(r.cells[2].getElementsByTagName('INPUT')[0].value ||
	r.cells[2].getElementsByTagName('INPUT')[1].value ||
	r.cells[3].getElementsByTagName('INPUT')[0].value ||
	r.cells[4].getElementsByTagName('INPUT')[0].value ||
	r.id)
 {
  if(!confirm("Sei sicuro di voler rimuovere questa riga?"))
   return;
 }
 if(r.id)
  DELETED_PRICESBYQTY.push(r.id);
 PBQTB.deleteRow(r.rowIndex);
}
/* EOF - CHANGELOG */


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
		 var ih = "<a href='#' class='btnedit' onclick='editAttachment("+a['id']+")' title='Modifica'><img src='"+ABSOLUTE_URL+"share/widgets/gmart/img/edit_small.png' border='0'/ ></a> <a href='#' class='btndel' onclick='deleteAttachment("+a['id']+")' title='Rimuovi'><img src='"+ABSOLUTE_URL+"share/widgets/gmart/img/delete_small.png' border='0'/ ></a><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank'>";
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
		}
	 sh2.sendCommand("dynattachments add -ap `"+AP+"` -refid `<?php echo $itemInfo['id']; ?>` -name '"+a['name']+"' -url '"+userpath+a['url']+"'");
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
	 var ih = "<a href='#' class='btnedit' onclick='editAttachment("+a['id']+")' title='Modifica'><img src='"+ABSOLUTE_URL+"share/widgets/gmart/img/edit_small.png' border='0'/ ></a> <a href='#' class='btndel' onclick='deleteAttachment("+a['id']+")' title='Rimuovi'><img src='"+ABSOLUTE_URL+"share/widgets/gmart/img/delete_small.png' border='0'/ ></a><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank'>";
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
	}
 sh.sendCommand("dynattachments add -ap `"+AP+"` -refid `<?php echo $itemInfo['id']; ?>` -name '"+url.replace('http://','')+"' -url '"+url+"'");
}

function codeCheck(ed)
{
 if(!ed.value) return;
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a || !a['items']) return;
	 for(var c=0; c < a['items'].length; c++)
	 {
	  if((a['items'][c]['code_str'] == ed.value) && (a['items'][c]['id'] != "<?php echo $itemInfo['id']; ?>"))
	  {
	   alert("Esiste già un articolo con questo codice in archivio. (cod: "+a['items'][c]['code_str']+" - "+a['items'][c]['name']+")");
	  }
	 }
	}
 sh.sendCommand("dynarc search -at gmart -field code_str `"+ed.value+"`");
}

function barcodeUpdate(ed)
{
 var img = document.getElementById('barcode-img');
 if(!img)
  return;

 if(ed.value)
 {
  img.src = ABSOLUTE_URL+"share/gd/barcode.php?barcode="+ed.value;
  img.style.visibility = "visible";
 }
 else
 {
  img.style.visibility = "hidden";
 }
}

/* CHANGELOG: 20-11-2014 */
function changePerms()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(a['modinfo'] && a['modinfo']['modstr'])
	  document.getElementById('permstring').innerHTML = a['modinfo']['modstr'];
	}
 sh.sendSudoCommand("gframe -f dynarc.itemPermissions -params `ap="+AP+"&id=<?php echo $itemInfo['id']; ?>`");
}
/* EOF - CHANGELOG */

function showImagePreview(div)
{
 var fileName = div.getAttribute('filename');
 if(!fileName) return;
 LightBox.show(fileName);
}

function otherShow(li)
{
 var ul = document.getElementById("other-bluenuv");
 var list = ul.getElementsByTagName('LI');
 for(var c=0; c < list.length; c++)
 {
  if(list[c] == li)
  {
   list[c].className = "selected";
   document.getElementById(list[c].getAttribute('connect')).style.display = "";
  }
  else
  {
   list[c].className = "";
   document.getElementById(list[c].getAttribute('connect')).style.display = "none";
  }
 }

 if((li.id == "other-salecron-tab") && !salecronLoaded)
 {
  updateSaleCron();
  salecronLoaded = true;
 }

}

function updatePurchaseCron(btn)
{
 var tb = document.getElementById('purchcron-table');
 var vendorId = document.getElementById('purchcron-vendor-select').value;
 var ID = "<?php echo $itemInfo['id']; ?>";
 var dateFrom = null;
 var dateTo = null;

 var LIMIT = 100;

 if(document.getElementById('purchcron-datefrom').value)
 {
  var date = new Date();
  date.setFromISO(strdatetime_to_iso(document.getElementById('purchcron-datefrom').value));
  dateFrom = date.printf('Y-m-d');
 }

 if(document.getElementById('purchcron-dateto').value)
 {
  var date = new Date();
  date.setFromISO(strdatetime_to_iso(document.getElementById('purchcron-dateto').value));
  dateTo = date.printf('Y-m-d');
 }

 var catTags = "";
 var div = document.getElementById('purchcron-docselect');
 var cblist = div.getElementsByTagName('INPUT');
 for(var c=0; c < cblist.length; c++)
 {
  if(cblist[c].checked == true)
   catTags+= ","+cblist[c].getAttribute('data-tag');
 }

 if(!btn)
 {
  while(tb.rows.length > 1)
   tb.deleteRow(1);
 }

 if(!catTags || (catTags == ""))
  return alert("Devi selezionare almeno una tipologia di documento");

 var sh = new GShell();
 sh.OnError = function(err){
	 //hide loading icon and nextpagebtn
	 document.getElementById('purchcron-loading-icon').style.display = "none";
	 document.getElementById('purchcron-nextpage-button').style.display = "none";
	 alert(err);
	}
 sh.OnOutput = function(o,a){
	 //hide loading icon and nextpagebtn
	 document.getElementById('purchcron-loading-icon').style.display = "none";
	 document.getElementById('purchcron-nextpage-button').style.display = "none";
	 if(!a || !a['items']) return;
	 var count = parseFloat(a['count']);
	 var date = new Date();
	 var row = 0;
	 for(var c=0; c < a['items'].length; c++)
	 {
	  var data = a['items'][c];
	  date.setFromISO(data['ctime']);
	  var r = tb.insertRow(-1);
	  r.className = row ? "row3" : "row2";
	  r.insertCell(-1).innerHTML = date.printf('d/m/Y');
	  r.insertCell(-1).innerHTML = data['subject_name'] ? data['subject_name'] : "&nbsp;";
	  r.insertCell(-1).innerHTML = "<span class='smalllinkblue' onclick='openDocument("+data['id']+")'>"+data['name']+"</span>";
	  var cell = r.insertCell(-1);
	  cell.innerHTML = formatCurrency(parseFloat(data['vendor_price']), DECIMALS)+" &euro;";
	  cell.style.textAlign = "right";
	  row = row ? 0 : 1;
	 }
	 if(count > (tb.rows.length-1))
	  document.getElementById('purchcron-nextpage-button').style.display = "block";
	}

 var cmd = "commercialdocs get-last-vendorprice -ap '"+AP+"' -id '"+ID+"' -cat-tags '"+catTags.substr(1)+"'";
 if(vendorId && (vendorId != '0')) cmd+= " -vendor '"+vendorId+"'";
 if(dateFrom)	cmd+= " -from '"+dateFrom+"'";
 if(dateTo)		cmd+= " -to '"+dateTo+"'";

 if(btn)
  cmd+= " -limit '"+(tb.rows.length-1)+","+LIMIT+"'";
 else
  cmd+= " -limit '"+LIMIT+"'";

 sh.sendCommand(cmd);

 //show loading icon
 document.getElementById('purchcron-loading-icon').style.display = "block";
}

function updateSaleCron(btn)
{
 var tb = document.getElementById('salecron-table');
 var ID = "<?php echo $itemInfo['id']; ?>";
 var subjectId = document.getElementById('salecron-customer-select').data ? document.getElementById('salecron-customer-select').data['id'] : 0;

 var dateFrom = null;
 var dateTo = null;
 var LIMIT = 100;

 if(document.getElementById('salecron-datefrom').value)
 {
  var date = new Date();
  date.setFromISO(strdatetime_to_iso(document.getElementById('salecron-datefrom').value));
  dateFrom = date.printf('Y-m-d');
 }

 if(document.getElementById('salecron-dateto').value)
 {
  var date = new Date();
  date.setFromISO(strdatetime_to_iso(document.getElementById('salecron-dateto').value));
  dateTo = date.printf('Y-m-d');
 }

 var catTags = "";
 var div = document.getElementById('salecron-docselect');
 var cblist = div.getElementsByTagName('INPUT');
 for(var c=0; c < cblist.length; c++)
 {
  if(cblist[c].checked == true)
   catTags+= ","+cblist[c].getAttribute('data-tag');
 }

 if(!btn)
 {
  while(tb.rows.length > 1)
   tb.deleteRow(1);
 }

 if(!catTags || (catTags == ""))
  return alert("Devi selezionare almeno una tipologia di documento");


 var sh = new GShell();
 sh.OnError = function(err){
	 //hide loading icon and nextpagebtn
	 document.getElementById('salecron-loading-icon').style.display = "none";
	 document.getElementById('salecron-nextpage-button').style.display = "none";
	 alert(err);
	}
 sh.OnOutput = function(o,a){
	 //hide loading icon and nextpagebtn
	 document.getElementById('salecron-loading-icon').style.display = "none";
	 document.getElementById('salecron-nextpage-button').style.display = "none";
	 if(!a || !a['items']) return;
	 var count = parseFloat(a['count']);
	 var date = new Date();
	 var row = 0;
	 for(var c=0; c < a['items'].length; c++)
	 {
	  var data = a['items'][c];
	  date.setFromISO(data['ctime']);
	  var r = tb.insertRow(-1);
	  r.className = row ? "row3" : "row2";
	  r.insertCell(-1).innerHTML = date.printf('d/m/Y');
	  r.insertCell(-1).innerHTML = data['subject_name'] ? data['subject_name'] : "&nbsp;";
	  r.insertCell(-1).innerHTML = "<span class='smalllinkblue' onclick='openDocument("+data['id']+")'>"+data['name']+"</span>";
	  var cell = r.insertCell(-1);
	  cell.innerHTML = formatCurrency(parseFloat(data['price']), DECIMALS)+" &euro;";
	  cell.style.textAlign = "right";
	  row = row ? 0 : 1;
	 }
	 if(count > (tb.rows.length-1))
	  document.getElementById('salecron-nextpage-button').style.display = "block";
	}

 var cmd = "commercialdocs get-last-saleprice -ap '"+AP+"' -id '"+ID+"' -cat-tags '"+catTags.substr(1)+"'";
 if(subjectId) 	cmd+= " -subjid '"+subjectId+"'";
 if(dateFrom)	cmd+= " -from '"+dateFrom+"'";
 if(dateTo)		cmd+= " -to '"+dateTo+"'";

 if(btn)
  cmd+= " -limit '"+(tb.rows.length-1)+","+LIMIT+"'";
 else
  cmd+= " -limit '"+LIMIT+"'";

 sh.sendCommand(cmd);

 //show loading icon
 document.getElementById('salecron-loading-icon').style.display = "block";
}

function openDocument(docId)
{
 window.open(ABSOLUTE_URL+"GCommercialDocs/docinfo.php?id="+docId, "_blank");
}

function showGCal(ed)
{
 var date = new Date();
 if(ed.value)
  date.setFromISO(strdatetime_to_iso(ed.value));

 GCalObj.Show(ed, date);
 GCalObj.OnChange = function(newdate){
	 ed.value = newdate.printf("d/m/Y");
	 ed.isodate = newdate.printf("Y-m-d");
	 ed.isodatetime = newdate.printf("Y-m-d H:i");
	 if(ed.onchange)
	  ed.onchange();
	}

 ed.onblur = function(){GCalObj.Hide();}
 ed.onkeydown = function(){GCalObj.Hide(true);}
 
}
</script>
</body></html>
<?php


