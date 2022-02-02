<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-12-2016
 #PACKAGE: gserv
 #DESCRIPTION: Edit service form.
 #VERSION: 2.11beta
 #CHANGELOG: 17-12-2016 : Listini prezzi, campo sconto.
			 17-03-2016 : Bug fix titolo lungo.
			 02-10-2014 : Possibilità di ricercare in tutta la rubrica (x integrazione con soci) sulla lista fornitori.
			 02-06-2014 : Sostituito -ct vendors con -into vendors
			 19-04-2014 : Bug fix.
 #DEPENDS: guploader, pricelists, gmutable
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES, $_DECIMALS, $_PRICELISTS, $_FREQ_VAT_TYPE, $_FREQ_VAT_PERC;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/company-profile.php");

$id = $_REQUEST['id'];

$_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'];

if($_COMPANY_PROFILE['accounting']['freq_vat_used'])
{
 $ret = GShell("dynarc item-info -ap vatrates -id `".$_COMPANY_PROFILE['accounting']['freq_vat_used']."` -get `vat_type,percentage`",$_REQUEST['sessid'],$_REQUEST['shellid']);
 $_FREQ_VAT_TYPE = $ret['outarr']['vat_type'];
 $_FREQ_VAT_PERC = $ret['outarr']['percentage'];
}

$ret = GShell("pricelists list",$_REQUEST['sessid'],$_REQUEST['shellid']);
$_PRICELISTS = $ret['outarr'];

$get = "";
for($c=0; $c < count($_PRICELISTS); $c++)
{
 $pid = $_PRICELISTS[$c]['id'];
 $get.= ",pricelist_".$pid."_baseprice,pricelist_".$pid."_mrate,pricelist_".$pid."_vat,pricelist_".$pid."_discount";
}

$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : "gserv";

$ret = GShell("dynarc item-info -ap `".$_AP."` -id `".$id."` -extget `gserv,thumbnails,coding,idoc,pricing,custompricing,vendorprices`"
	.($get ? " -get `".ltrim($get,",")."`" : ""),$_REQUEST['sessid'],$_REQUEST['shellid']);
$itemInfo = $ret['outarr'];
$_USE_DEFAULT_VALUES = !$itemInfo['mtime'] ? true : false;

if($itemInfo['estimated_timelength'])
{
 $h = floor($itemInfo['estimated_timelength']/60);
 $m = (int)$itemInfo['estimated_timelength'] - ($h*60);
 $timeLengthStr = $h.":".($m<10 ? "0".$m : $m);
}
else
 $timeLengthStr = "0:00";

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
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Edit service</title>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>"; var USER_HOME = "<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR']; ?>/";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/layers.js"></script>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gserv/edit-item.css" type="text/css" />
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gmutable/index.php");
include($_BASE_PATH."var/objects/htmlgutility/menu.php");
include_once($_BASE_PATH."var/objects/guploader/index.php");

$_ABBR_ART_TITLE = html_entity_decode($itemInfo['name']);
if(strlen($_ABBR_ART_TITLE) > 60) $_ABBR_ART_TITLE = substr($_ABBR_ART_TITLE, 0, 60)."...";

?>
</head><body>
<div class="edit-product-form">
 <!-- HEADER -->
 <table width='100%' cellspacing='0' cellpadding='0' border='0'>
 <tr><td width='600' height='25'><div class='header'>Servizio: <span class='itemcode'><?php echo $itemInfo['code_str']; ?></span> <span class='title'><?php echo $_ABBR_ART_TITLE; ?></span><?php if($_USE_DEFAULT_VALUES) echo " *"; ?></div></td>
	<td align='center'><div class='header-right'><?php echo $catInfo['name']; ?></div></td>
 </tr>
 </table>
 <!-- EOF HEADER -->

 <table width='780' cellspacing='0' cellpadding='0' border='0' style="margin-top:20px;border-bottom: 1px solid #dadada;">
 <tr><td valign='top' width='600' style="padding-right:10px;">
	<!-- CONTENTS -->
	<ul class='maintab' style="margin-left:8px;">
	 <li class='selected'><span class='title' onclick="showPage('details',this)">DETTAGLI</span></li>
	 <li><span class='title' onclick="showPage('extended',this)">SCHEDE</span></li>
	 <li><span class='title' onclick="showPage('attachments',this)">ALLEGATI</span></li>
	 <li><span class='title' onclick="showPage('vendors',this)">FORNITORI</span></li>
	 <li><span class='title' onclick="showPage('pricelists',this)">LISTINI PREZZI</span></li>
     <li><span class='title' onclick="showPage('custompricing',this)">PREZZI IMPOSTI</span></li>
	</ul>

	<!-- DETAILS PAGE -->
	<div class='tabpage' id="details-page">
	 <table width='100%' height='200' border='0' cellspacing='0' cellpadding='0'>
	  <tr><td valign='top' width='200'>
		   <ul class='thumb-buttons' id='thumb-buttons'>
			<?php
			for($c=0; $c < count($itemInfo['thumbnails']); $c++)
			{
			 echo "<li onclick='selectThumb(this)' id='".fullescape($itemInfo['thumbnails'][$c])."'";
			 $x = strpos($itemInfo['thumbnails'][$c], ".", strlen($itemInfo['thumbnails'][$c])-5);
			 if($x > 0)
			 {
			  $ext = substr($itemInfo['thumbnails'][$c], $x+1);
			  $thumb = substr($itemInfo['thumbnails'][$c], 0, $x)."-thumb.".$ext;
			 }
			 else
			  $thumb = $itemInfo['thumbnails'][$c]."-thumb";
 			 if($thumb && file_exists($_BASE_PATH.$thumb))
			  echo " icon='".fullescape($thumb)."'";
			 echo ($c==0 ? " class='selected'>" : ">").($c+1)."</li>";
			}

		    $thumb = "share/widgets/gserv/img/photo.png";
			if($itemInfo['thumbnails'][0])
			{
			 $x = strpos($itemInfo['thumbnails'][0], ".", strlen($itemInfo['thumbnails'][0])-5);
			 if($x > 0)
			 {
			  $ext = substr($itemInfo['thumbnails'][0], $x+1);
			  $thumb = substr($itemInfo['thumbnails'][0], 0, $x)."-thumb.".$ext;
			 }
			 else
			  $thumb = $itemInfo['thumbnails'][0]."-thumb";
 			 if(!file_exists($_BASE_PATH.$thumb))
			  $thumb = $itemInfo['thumbnails'][0];
			}

			?>
		   </ul>
		   <div class='thumb-preview' id='thumb-preview' style="background-image: url(<?php echo $_ABSOLUTE_URL.$thumb; ?>);">
			<img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gserv/img/icon_delete.gif" class="delete-button" onclick="deleteSelectedThumb()"/>
		   </div>

		   <ul class='basicbuttons' style="clear:both;float:left;margin-top:5px;margin-left:22px;">
  			<li><span onclick="uploadImage()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gserv/img/add.gif" border='0'/>Carica immagine</span></li>
 		   </ul>

		  </td>
		  <td valign='top' style="border-left:1px solid #dadada;padding-left:10px;">
			<!-- INFO -->
			<table border='0' class='infotableform'>
			 <tr><td class='field'>CODICE:</td>
				 <td class='value'><input type='text' id='item_code' style='width:80px;' value="<?php echo $itemInfo['code_str']; ?>"/></td></tr>
			 <tr><td class='field'>NOME:</td>
				 <td class='value'><input type='text' id='item_name' style='width:250px;' value="<?php echo $itemInfo['name']; ?>"/></td></tr>
			 <tr><td class="field small">PREZZO DI BASE:</td>
				 <td class='value' style='font-size:10px;'><input type='text' id='item_baseprice' style='width:70px;' value="<?php echo number_format($itemInfo['baseprice'],$_DECIMALS,',','.'); ?>" onchange="_basepriceChange(this)"/> &euro;<span class="field small" style="margin-left:50px">Unit&agrave; di Misura: <input type='text' id='item_units' style='width:40px;' value="<?php echo $itemInfo['units'] ? $itemInfo['units'] : 'PZ'; ?>"/></td></tr>
			 <tr><td class="field small">I.V.A:</td>
				 <td class='value' style='font-size:10px;'><input type='text' id='item_vat' style='width:30px;' value="<?php echo $itemInfo['vat'] ? $itemInfo['vat'] : ($_USE_DEFAULT_VALUES ? $_FREQ_VAT_PERC : 0); ?>" onchange="_vatChange(this)"/> %</td></tr>


			 <tr><td class="field small">TIPO DI SERVIZIO:</td>
				 <td class="value"><select id="service-type" style="width:250px" onchange="serviceTypeChange(this)"><?php
					$servTypes = array("FIXED-PRICE"=>"Servizio a prezzo fisso", "HOURLY-RATE"=>"Servizio a tariffa oraria", "MILEAGE-REIMBURSEMENT-ONE-TRIP"=>"Rimborso chilometrico (solo andata)", "MILEAGE-REIMBURSEMENT-ROUND-TRIP"=>"Rimborso chilometrico (andata e ritorno)");
					while(list($k,$v) = each($servTypes))
					 echo "<option value='".$k."'".($k == $itemInfo['type'] ? " selected='selected'>" : ">").$v."</option>";
					?></td></tr>

			 <!-- <tr><td class="field small">MODALITA&lsquo; PREZZO:</td>
				 <td class='value' style="font-size:10px;">
					<input type='radio' name='pricemode' value='0' <?php if($itemInfo['pricemode'] == 0) echo "checked='true'"; ?>/>Prezzo fisso <input type='radio' name='pricemode' value='1' id='pricemode1' <?php if($itemInfo['pricemode'] == 1) echo "checked='true'"; ?>/>Tariffa oraria</td></tr>-->
			 <tr><td class="field small lightblue">STIMA TEMPO<br/>ESECUZIONE</td>
				 <td class='value'><input type='text' id='estimated_timelength' size='5' value="<?php echo $timeLengthStr; ?>"/></td></tr>

			</table>
			<!-- EOF INFO -->
		  </td></tr>
	 </table>
	 <br/>
	 <span class='smallh3'>BREVE DESCRIZIONE</span><br/>
	 <textarea class="item-description" id="item_description"><?php echo $itemInfo['desc']; ?></textarea>

	</div>
	<!-- EOF DETAILS PAGE -->

	<!-- EXTENDED DETAILS PAGE -->
	<div class='tabpage' id="extended-page" style="display:none;">
	 <ul class='bluenuv' id='idoc-bluenuv' style='float:left;'>
	 <?php
	 for($c=0; $c < count($itemInfo['idocs']); $c++)
	 {
	  echo "<li".($c==0 ? " class='selected'" : "")." id='idoc-".$itemInfo['idocs'][$c]['aid']."_".$itemInfo['idocs'][$c]['id']."' onclick='idocShow(this)'><img src='".$_ABSOLUTE_URL."share/widgets/gserv/img/delete-btn.gif' title='Elimina scheda' onclick='idocRemove(this)'/><span>".$itemInfo['idocs'][$c]['name']."</span></li>";
	 }
	 ?>
	 </ul>
	 <span class='link-green' style='float:left;' onclick="idocAdd()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gserv/img/add-btn.png"/> Aggiungi</span>
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
		 <table border='0' cellspacing='0' cellpadding='0' width='580' height='40'>
		  <tr><td width='120' style='padding-left:10px'><span class='smallblue'>Carica un file dal PC</span></td>
			 <td><div id='gupldspace'></div></td>
			 <td width='40' class='attachments-tb-buttons'><a href='#' onclick='selectFromServer("<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR']."/"; ?>")'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gserv/img/load-from-server.png" border="0" title="Carica dal server"/></a></td>
			 <td width='40' class='attachments-tb-buttons'><a href='#' onclick='insertFromURL()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gserv/img/link.png" border="0" title="Inserisci un link da URL"/></a></td>
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
		   echo "<a href='#' class='btnedit' onclick='editAttachment(".$item['id'].")' title='Modifica'><img src='".$_ABSOLUTE_URL."share/widgets/gserv/img/edit_small.png' border='0'/></a>";
		   echo "<a href='#' class='btndel' onclick='deleteAttachment(".$item['id'].")' title='Rimuovi'><img src='".$_ABSOLUTE_URL."share/widgets/gserv/img/delete_small.png' border='0'/></a>";
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
	 <div class="gmutable" style="width:586px;height:300px;margin-left:10px;background:#ffffff;border:0px;">
	 <table id="vendors-table" class="pricelists" width="100%" cellspacing="2" cellpadding="2" border="0">
	 <tr><th width='20'><input type="checkbox" onchange="VENDORSTB.selectAll(this.checked)"/></th>
		 <th width='60' id='code' editable='true' style="text-align:left;">COD.ART</th>
		 <th width='190' id='vendor' editable='true'>FORNITORE</th>
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
	 <h3 class="orangebar">LISTINI PREZZI DI RIVENDITA</h3>
	 <div class="gmutable" style="width:586px;height:300px;margin-left:10px;background:#ffffff;border:0px;">
	 <table id="pricelists-table" class="pricelists" width="100%" cellspacing="2" cellpadding="2" border="0">
	 <tr><th width='20'>INC.</th>
		 <th id='pricelistname' style="text-align:left;">LISTINO</th>
		 <th width='80' id='baseprice' editable='true' format='currency' decimals="<?php echo $_DECIMALS; ?>">PREZZO BASE</th>
		 <th width='70' id='markuprate' editable='true' format='percentage'>% RICARICO</th>
		 <th width='70' id='discount' editable='true' format='percentage'>% SCONTO</th>
		 <th width='80' id='finalprice'>PREZZO FINALE</th>
		 <th width='40' id='vat' editable='true' format='percentage'>% IVA</th>
		 <th width='80' id='finalpricevatincluded'>PREZZO + IVA</th></tr>

	 <?php
	 $list = $_PRICELISTS;
	 $row = 0;
	 $starImg = $_ABSOLUTE_URL."share/widgets/gserv/img/star.png";
	 for($c=0; $c < count($list); $c++)
	 {
	  $baseprice = $itemInfo["pricelist_".$list[$c]['id']."_baseprice"] ? $itemInfo["pricelist_".$list[$c]['id']."_baseprice"] : ($_USE_DEFAULT_VALUES ? $itemInfo['baseprice'] : 0);
	  $markuprate = $itemInfo["pricelist_".$list[$c]['id']."_mrate"] ? $itemInfo["pricelist_".$list[$c]['id']."_mrate"] : ($_USE_DEFAULT_VALUES ? $list[$c]['markuprate'] : 0);
	  $discount = $itemInfo["pricelist_".$list[$c]['id']."_discount"] ? $itemInfo["pricelist_".$list[$c]['id']."_discount"] : ($_USE_DEFAULT_VALUES ? $list[$c]['discount'] : 0);
	  $vat = $itemInfo["pricelist_".$list[$c]['id']."_vat"] ? $itemInfo["pricelist_".$list[$c]['id']."_vat"] : ($_USE_DEFAULT_VALUES ? $list[$c]['vat'] : 0);

	  $finalPrice = $baseprice ? $baseprice + (($baseprice/100)*$markuprate) : 0;
	  $finalPrice = $finalPrice ? $finalPrice - (($finalPrice/100)*$discount) : 0;
	  $finalPriceVI = $finalPrice ? $finalPrice + (($finalPrice/100)*$vat) : 0;

	  echo "<tr id='pricelist-".$list[$c]['id']."' class='".($list[$c]['enabled'] ? 'row'.$row : 'unselected')."'>";
	  echo "<td><input type='checkbox'".($list[$c]['enabled'] ? " checked='true'" : "")." onclick='pricelistCheckChange(this)'/></td>";
	  echo "<td>".($list[$c]['isextra'] ? "<img src='".$starImg."' title='Listino extra'/> " : "").$list[$c]['name']."</td>";
	  echo "<td align='right'>".number_format($baseprice,$_DECIMALS,",",".")."</td>";
	  echo "<td align='center'>".($markuprate ? $markuprate."%" : "0%")."</td>";
	  echo "<td align='center'>".($discount ? $discount."%" : "0%")."</td>";

	  echo "<td align='right'><em>&euro;</em>".number_format($finalPrice,$_DECIMALS,",",".")."</td>";
	  echo "<td align='center'>".($vat ? $vat."%" : "0%")."</td>";
	  echo "<td align='right'><em>&euro;</em>".number_format($finalPriceVI,$_DECIMALS,",",".")."</td></tr>";
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
	 <div class="gmutable" style="width:586px;height:300px;margin-left:10px;background:#ffffff;border:0px;">
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

	<!-- EOF CONTENTS -->
	</td><td valign='top' style="border-left:1px solid #dadada;padding-left:10px;">
	<!-- RIGHT SPACE -->

	<h3 class='rightsec-blue'><span class='title'>STRUMENTI</span></h3>
	<div class='right-section'>
		<a href='#' id='copy-to-clipboard-link'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gserv/img/clipboard.png" border='0'/>Copia negli appunti</a><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gserv/img/blue-dnarr.png"/>
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
		<a href='#'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gserv/img/similar.png" border='0'/>Cerca articoli simili</a>
	</div> -->

	<br/>
	<br/>
	
	<h3 class='rightsec-blue'><span class='title'>INFORMAZIONI</span></h3>
	<span class='gray11'>ID univoco: </span><span class='black11'><b><?php echo $itemInfo['id']; ?></b></span><br/>
	<span class='gray11'>Data creazione: </span><span class='black11'><?php echo date('d/m/Y',$itemInfo['ctime']); ?></span><br/>
	<?php
	if($itemInfo['mtime'])
	 echo "<span class='gray11'>Ultima modifica: </span><span class='black11'>".date('d/m/Y',$itemInfo['mtime'])."</span><br/>";
	?>

	<!-- EOF RIGHT SPACE -->
	</td></tr>
 </table>
 
 <ul class='basicbuttons' style="margin-left:4px;margin-top:6px;float:left;">
  <li><span onclick='submit()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gserv/img/save.gif" border='0'/>Salva</span></li>
  <li><span onclick="gframe_close()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gserv/img/exit.png" border='0'/>Chiudi</span></li>
 </ul>

 <ul class='basicbuttons' style="float:right;margin-top:6px;margin-right:20px;">
  <li><span onclick='deleteItem()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gserv/img/delete.png" border='0'/>Elimina</span></li>
 </ul>

</div>

<script>
var AP = "<?php echo $_AP ? $_AP : 'gserv'; ?>";
var ACTIVE_TAB_PAGE = "details";
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

var NEW_CP = new Array();
var UPDATED_CP = new Array();
var DELETED_CP = new Array();
var attUpld = null;
<?php
for($c=0; $c < count($itemInfo['idocs']); $c++)
 echo "IDOCS.push({aid:".$itemInfo['idocs'][$c]['aid'].",id:".$itemInfo['idocs'][$c]['id'].",isdefault:".($itemInfo['idocs'][$c]['default'] ? "true" : "false")."});\n";
?>

function bodyOnLoad()
{
 PRICELISTSTB = new GMUTable(document.getElementById('pricelists-table'), {autoresize:false, autoaddrows:false});
 PRICELISTSTB.OnCellEdit = function(r,cell,value){
	 _pricelistsUpdateTotals(r);
	}

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
 attUpld = new GUploader(null,null,"attachments/gserv/");
 document.getElementById('gupldspace').appendChild(attUpld.O);
 attUpld.OnUpload = function(file){
	 var sh = new GShell();
	 sh.OnOutput = function(o,a){
		 var div = document.createElement('DIV');
		 div.className = "attachment";
		 div.id = "attachment-"+a['id'];
		 var ih = "<a href='#' class='btnedit' onclick='editAttachment("+a['id']+")' title='Modifica'><img src='"+ABSOLUTE_URL+"share/widgets/gserv/img/edit_small.png' border='0'/ ></a> <a href='#' class='btndel' onclick='deleteAttachment("+a['id']+")' title='Rimuovi'><img src='"+ABSOLUTE_URL+"share/widgets/gserv/img/delete_small.png' border='0'/ ></a><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank'>";
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
 var _name = document.getElementById('item_name').value;
 var code = document.getElementById('item_code').value;
 var desc = document.getElementById('item_description').value;
 var baseprice = parseCurrency(document.getElementById('item_baseprice').value);
 var vat = document.getElementById('item_vat').value;
 var units = document.getElementById('item_units').value;
 var priceMode = 0;
 var _tl =  document.getElementById('estimated_timelength').value;
 var type = document.getElementById('service-type').value;

 switch(type)
 {
  case 'HOURLY-RATE' : priceMode=1; break;
 }

 if(_tl)
 {
  var x = _tl.split(":");
  if(x[1])
   _tl = (parseFloat(x[0])*60)+parseFloat(x[1]);
  else
   _tl = parseFloat(_tl);
 }
 else
  _tl = 0;


 /* Save pricelists */
 var set = "";
 var pricelists = "";
 for(var c=1; c < PRICELISTSTB.O.rows.length; c++)
 {
  var r = PRICELISTSTB.O.rows[c];
  var pid = r.id.substr(10);
  if(r.cells[0].getElementsByTagName('INPUT')[0].checked)
   pricelists+= ","+pid;

  set+= ",pricelist_"+pid+"_baseprice='"+parseCurrency(r.cell['baseprice'].getValue())+"',pricelist_"+pid+"_mrate='"+parseFloat(r.cell['markuprate'].getValue())+"',pricelist_"+pid+"_discount='"+parseFloat(r.cell['discount'].getValue())+"',pricelist_"+pid+"_vat='"+parseFloat(r.cell['vat'].getValue())+"'";
 }
 if(pricelists)
  pricelists = pricelists.substr(1);


 var sh = new GShell();
 sh.OnFinish = function(o,a){gframe_close(o,a);}
 sh.sendCommand("dynarc edit-item -ap `"+AP+"` -id `<?php echo $itemInfo['id']; ?>` -name `"+_name+"` -code-str `"+code+"` -extset `gserv.pricemode='"+priceMode+"',type='"+type+"',units='"+units+"',tl='"+_tl+"',pricing.baseprice='"+baseprice+"',vat='"+vat+"',pricelists='"+pricelists+"'` -desc `"+desc+"`"+(set ? " -set `"+set.substr(1)+"`" : ""));

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

}

function deleteItem()
{
 if(gframe_shotmessage("Are you sure you want remove this service?","<?php echo $itemInfo['id']; ?>","DELETE") == false)
  return;

 if(!confirm("Sei sicuro di voler eliminare questo servizio?"))
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

	 var dstPath = "image/services/";

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
		  li.fileName = USER_HOME+dstPath+"service-<?php echo $itemInfo['id']; ?>-"+(tbIDX+c)+"-thumb."+a['files'][c]['extension'];
		  li.onclick = function(){selectThumb(this);}
		  tbUL.appendChild(li);
		  if(c == (a['files'].length-1))
		  {
		   li.className = "selected";
		   document.getElementById('thumb-preview').style.backgroundImage = "url(<?php echo $_ABSOLUTE_URL; ?>"+li.fileName+")";
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
		 var dstFileName = dstPath+"service-<?php echo $itemInfo['id']; ?>-"+(tbIDX+c)+"."+a['files'][c]['extension'];
		 sh2.sendCommand("mv `"+fileName+"` `"+dstFileName+"`");
		} break;

	   case 'FROM_SERVER' : {
		 var fileName = a['files'][c]['fullname'].replace(USER_HOME,"");
		 var dstFileName = dstPath+"service-<?php echo $itemInfo['id']; ?>-"+(tbIDX+c)+"."+a['files'][c]['extension'];
		 if(fileName != dstFileName)
		  sh2.sendCommand("cp `"+fileName+"` `"+dstFileName+"`");
		} break;

	  }
	  
	  sh2.sendCommand("gd resize -i `"+dstFileName+"` -o `"+dstPath+"service-<?php echo $itemInfo['id']; ?>-"+(tbIDX+c)+"-thumb."+a['files'][c]['extension']+"` -w 128");
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
   if(li.getAttribute('icon'))
	fileName = decodeFID(li.getAttribute('icon'));
   document.getElementById('thumb-preview').style.backgroundImage = "url(<?php echo $_ABSOLUTE_URL; ?>"+fileName+")";
  }
  else
   list[c].className = "";
 }
 li.className = "selected";
}

function deleteSelectedThumb()
{
 var tbUL = document.getElementById('thumb-buttons');
 var list = tbUL.getElementsByTagName('LI');
 for(var c=0; c < list.length; c++)
 {
  if(list[c].className == "selected")
  {
   var idx = c;
   if(!confirm("Sei sicuro di voler rimuovere questa immagine?"))
	return;
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
	  document.getElementById('thumb-preview').style.backgroundImage = "url(<?php echo $_ABSOLUTE_URL; ?>share/widgets/gserv/img/photo.png)";
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

 if(!confirm("Sei sicuro di voler rimuovere la scheda '"+sheetName+"' da questo servizio?"))
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
		 li.innerHTML = "<img src='"+ABSOLUTE_URL+"share/widgets/gserv/img/delete-btn.gif' title='Elimina scheda' onclick='idocRemove(this)'/ ><span>"+a['name']+"</span>";
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
 sh.sendCommand("gframe -f idoc.choice -params `idocct=GSERV`");
}

/* PRICELISTS */
function _pricelistsUpdateTotals(r)
{
 var baseprice = parseCurrency(r.cell['baseprice'].getValue());
 var markuprate = parseFloat(r.cell['markuprate'].getValue());
 var discount = parseFloat(r.cell['discount'].getValue());
 var vat = parseFloat(r.cell['vat'].getValue());

 var finalPrice = baseprice ? baseprice + ((baseprice/100)*markuprate) : 0;
 finalPrice = finalPrice ? finalPrice - ((finalPrice/100)*discount) : 0;
 var finalPriceVatIncluded = finalPrice ? finalPrice + ((finalPrice/100)*vat) : 0;

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

function serviceTypeChange(sel)
{
 switch(sel.value)
 {
  case 'FIXED-PRICE' : document.getElementById('item_units').value = "PZ"; break;
  case 'HOURLY-RATE' : document.getElementById('item_units').value = "H"; break;
  case 'MILEAGE-REIMBURSEMENT-ONE-TRIP' : case 'MILEAGE-REIMBURSEMENT-ROUND-TRIP' : document.getElementById('item_units').value = "KM"; break;
 }
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
		 var ih = "<a href='#' class='btnedit' onclick='editAttachment("+a['id']+")' title='Modifica'><img src='"+ABSOLUTE_URL+"share/widgets/gserv/img/edit_small.png' border='0'/ ></a> <a href='#' class='btndel' onclick='deleteAttachment("+a['id']+")' title='Rimuovi'><img src='"+ABSOLUTE_URL+"share/widgets/gserv/img/delete_small.png' border='0'/ ></a><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank'>";
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
	 var ih = "<a href='#' class='btnedit' onclick='editAttachment("+a['id']+")' title='Modifica'><img src='"+ABSOLUTE_URL+"share/widgets/gserv/img/edit_small.png' border='0'/ ></a> <a href='#' class='btndel' onclick='deleteAttachment("+a['id']+")' title='Rimuovi'><img src='"+ABSOLUTE_URL+"share/widgets/gserv/img/delete_small.png' border='0'/ ></a><a href='"+(a['type'] != "WEB" ? ABSOLUTE_URL : "")+a['url']+"' target='blank'>";
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

</script>
</body></html>
<?php


