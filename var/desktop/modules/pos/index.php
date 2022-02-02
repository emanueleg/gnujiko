<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 12-11-2016
 #PACKAGE: pos-module
 #DESCRIPTION: POS - Module for Gnujiko Desktop.
 #VERSION: 2.5beta
 #CHANGELOG: 12-11-2016 : Aggiunta possibilita di ridimensionare il modulo.
			 05-09-2014 : Aggiunta la possibilità di scegliere il tipo di documento da emettere e di scegliere il cliente.
			 27-11-2013 : Aggiunta la modalità di stampa su file.
			 18-10-2013 : Bug fix.
			 07-09-2013 : Aggiunto fidelity card e listini ed altre aggiunte e modifiche varie.
 #DEPENDS: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_INTERNAL_LOAD, $_MODULE_INFO, $_COMPANY_PROFILE;

//-- PRELIMINARY ----------------------------------------------------------------------------------------------------//
if(!$_INTERNAL_LOAD) // this script is loaded into a layer
{
 define("VALID-GNUJIKO",1);
 $_BASE_PATH = "../../../../";
 include_once($_BASE_PATH."include/gshell.php");
 include_once($_BASE_PATH."include/js/gshell.php");
}
//-------------------------------------------------------------------------------------------------------------------//
$_MODULE_INFO['handle'] = $_MODULE_INFO['id']."-handle";
$_MODULE_INFO['front'] = $_MODULE_INFO['id']."-front";
$_MODULE_INFO['back'] = $_MODULE_INFO['id']."-back";
$_MODULE_INFO['defaultmodulewidth'] = 500;

$_MODULE_INFO['plugs'][] = $_MODULE_INFO['id']."-plug1";

$from = time();

include_once($_BASE_PATH."include/i18n.php");
/*LoadLanguage("posmodule"); TODO: da fare... */
include_once($_BASE_PATH."var/objects/editsearch/index.php");
include_once($_BASE_PATH."include/company-profile.php");
include_once($_BASE_PATH."var/objects/gmutable/index.php");

/* GET DEFAULT VATRATE */
$db = new AlpaDatabase();
$db->RunQuery("SELECT percentage,vat_type FROM dynarc_vatrates_items WHERE id='".$_COMPANY_PROFILE['accounting']['freq_vat_used']."'");
$db->Read();
echo "<input type='hidden' id='".$_MODULE_INFO['id']."-defaultvatrate' value='".$db->record['percentage']."'/>";
echo "<input type='hidden' id='".$_MODULE_INFO['id']."-defaultvatid' value='".$_COMPANY_PROFILE['accounting']['freq_vat_used']."'/>";
echo "<input type='hidden' id='".$_MODULE_INFO['id']."-defaultvattype' value='".$db->record['vat_type']."'/>";
$db->Close();

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/pos/pos.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/pos/pos.js" type="text/javascript"></script>

<!-- FRONT PANEL -->
<div id="<?php echo $_MODULE_INFO['front']; ?>" class="gnujiko-desktop-module-front-panel posmod-frontpanel" onload="posmodule_load('<?php echo $_MODULE_INFO['id']; ?>')" onrename="posmodule_rename('<?php echo $_MODULE_INFO['id']; ?>')" style="width:<?php echo $_MODULE_INFO['params']['config']['modulewidth'] ? $_MODULE_INFO['params']['config']['modulewidth'] : $_MODULE_INFO['defaultmodulewidth']; ?>px">
<div class="posmod-header">
<table width="100%" height='28' cellspacing="0" cellpadding="0" border="0">
<tr><td align='right' valign='middle' width='34'>&nbsp;</td>
	<td align='center' valign='middle' class='posmod-handle' id="<?php echo $_MODULE_INFO['handle']; ?>"><?php echo ($_MODULE_INFO['title'] != "storesearch") ? $_MODULE_INFO['title'] : "POS"; ?></td>
	<td align='left' valign='middle' width='34'>&nbsp;</td></tr>
</table>
<table width="100%" height='6' cellspacing='0' cellpadding='0' border='0' class="posmod-refdate">
<tr><td>Vendita al banco tramite barcode</td></tr>
</table>
</div>

<div class="posmod-container" id="<?php echo $_MODULE_INFO['id'].'-container'; ?>">
 <div style="margin-bottom:5px">
  <div id="<?php echo $_MODULE_INFO['id']; ?>-subjectcontainer" class="postablecontainer" <?php if($_MODULE_INFO['params']['config']['showcustomer'] != "true") echo "style='display:none'"; ?>>
  <table width="100%" cellspacing="0" cellpadding="0" border="0">
  <tr><td width='90'><span class="barcodetext">CLIENTE: </span></td>
	  <td><input type="text" class="subjectedit" id="<?php echo $_MODULE_INFO['id']; ?>-subject"/></td></tr>
  </table>
  </div>

  <div id="<?php echo $_MODULE_INFO['id']; ?>-fidelitycardcontainer" class="postablecontainer" <?php if($_MODULE_INFO['params']['config']['fidelitycardenabled'] != "true") echo "style='display:none'"; ?>>
  <table width="100%" cellspacing="0" cellpadding="0" border="0">
  <tr><td width='90'><span class="barcodetext">FIDELITY CARD: </span></td>
	  <td width='110'><input type="text" class="fidcarded" id="<?php echo $_MODULE_INFO['id']; ?>-fidelitycardcode"/></td>
	  <td><span class="fidcardcustname" id="<?php echo $_MODULE_INFO['id']; ?>-fidelitycardcustomer">&nbsp;</span></td>
	  <td width='20'><img src="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/pos/img/delete.png" style="cursor:pointer;display:none" title="Annulla" id="<?php echo $_MODULE_INFO['id']; ?>-fidelitycardcustomer-canc" onclick="posmodule_fidelitycanc('<?php echo $_MODULE_INFO['id']; ?>')"/></td></tr>
  </table>
  </div>

  <div id="<?php echo $_MODULE_INFO['id']; ?>-pricelistcontainer" class="postablecontainer" <?php if($_MODULE_INFO['params']['config']['showpricelist'] != "true") echo "style='display:none'"; ?>>
  <table width="100%" cellspacing="0" cellpadding="0" border="0">
  <tr><td><span class="barcodetext">LISTINO PREZZI: </span>
		  <select id="<?php echo $_MODULE_INFO['id']; ?>-pricelist">
		  <?php
		  $ret = GShell("pricelists list");
		  $list = $ret['outarr'];
		  for($c=0; $c < count($list); $c++)
		   echo "<option value='".$list[$c]['id']."'>".$list[$c]['name']."</option>";
		  ?>
		  </select></td></tr>
  </table>
  </div>

  <table width="100%" cellspacing="0" cellpadding="0" border="0">
  <tr><td colspan="2"></td></tr>
  <tr><td><span class="barcodetext">BARCODE: </span><input type="text" class="barcodeedit" id="<?php echo $_MODULE_INFO['id']; ?>-barcode"/></td>
	  <td width='45'><img src="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/pos/img/add.png" style="cursor:pointer" title="Aggiungi un articolo manualmente" onclick="posmodule_manualinsert('<?php echo $_MODULE_INFO['id']; ?>')"/></td></tr>
  </table>
 </div>
 
 <div class="posmod-itemlist-container">
  
  <table width='100%' cellspacing='0' cellpadding='0' border='0' class="gmutable" id="<?php echo $_MODULE_INFO['id']; ?>-itemlist">
   <tr><th style='text-align:left;padding-left:3px;' editable='true' id="<?php echo $_MODULE_INFO['id']; ?>-description">ARTICOLO</th>
	<th style='width:40px;text-align:center' editable='true' id="<?php echo $_MODULE_INFO['id']; ?>-qty">QTA</th>
	<th style='width:60px;text-align:center' format="currency" editable='true' id="<?php echo $_MODULE_INFO['id']; ?>-unitprice">PR.UNIT</th>
	<th style='width:60px;text-align:center' format="percentage" editable='true' id="<?php echo $_MODULE_INFO['id']; ?>-vat">IVA</th>
	<th style='width:60px;text-align:center' format="currency percentage" editable='true' id="<?php echo $_MODULE_INFO['id']; ?>-discount">SCONTO</th>
	<th style='width:60px;text-align:center' format="currency" editable='true' id="<?php echo $_MODULE_INFO['id']; ?>-total">TOTALE</th>
	<th style='width:20px;'>&nbsp;</th>
   </tr>

  </table>
  
 </div>

</div>

<div class="posmod-footer">
  <table width='100%' cellspacing='0' cellpadding='0' border='0'>
   <tr><td><span class='posmod-button' onclick="posmodule_print('<?php echo $_MODULE_INFO['id']; ?>')">Stampa</span></td>
	   <td align='center'><a href='#' style='font-size:13px' onclick="posmodule_dailyclosing('<?php echo $_MODULE_INFO['id']; ?>')">Chiusura cassa</a></td>
	   <td align='right'><span class='posmod-subtot' id="<?php echo $_MODULE_INFO['id']; ?>-subtot">&euro;&nbsp;&nbsp;0,00</span></td></tr>
  </table>
</div>

</div>
<!-- EOF - FRONT PANEL -->

<!-- BACK PANEL -->
<div id="<?php echo $_MODULE_INFO['back']; ?>" class="gnujiko-desktop-module-back-panel posmod-backpanel" style="display:none;width:360px">
<table width='100%' cellspacing='0' cellpadding='0' border='0' height='100%'>
<tr><td class='header'><?php echo $_MODULE_INFO['title']; ?></td></tr>
<tr><td valign='top'>
 <div class='posmod-backpanel-params'>
  <span class='bluetitle'><input type='radio' name="<?php echo $_MODULE_INFO['id']; ?>-printmode" value='classic' <?php if(!$_MODULE_INFO['params']['config']['printmode'] || ($_MODULE_INFO['params']['config']['printmode'] == "classic")) echo "checked='true'"; ?>/> Modalit&agrave; stampa classica (PDF)</span><br/>
  <span class='bluetitle'>TIPOLOGIA: </span>
  <select id="<?php echo $_MODULE_INFO['id']; ?>-doctype" style="width:170px" onchange="posmodule_updateCatId('<?php echo $_MODULE_INFO['id']; ?>',this)"><?php
	$ret = GShell("dynarc cat-list -ap commercialdocs",$_REQUEST['sessid'],$_REQUEST['shellid']);
	$list = $ret['outarr'];
	if(!$_MODULE_INFO['params']['config']['doctype'])
	 $_MODULE_INFO['params']['config']['doctype'] = "RECEIPTS";
	for($c=0; $c < count($list); $c++)
	 echo "<option value='".$list[$c]['tag']."' refid='".$list[$c]['id']."'".(($list[$c]['tag'] == $_MODULE_INFO['params']['config']['doctype']) ? " selected='selected'>" : ">")
			.$list[$c]['name']."</option>";
	?></select>
  <hr/>
  <span class='bluetitle'><input type='radio' name="<?php echo $_MODULE_INFO['id']; ?>-printmode" value='direct' <?php if($_MODULE_INFO['params']['config']['printmode'] == "direct") echo "checked='true'"; ?>/> Modalit&agrave; stampa diretta (solo per s.o. Linux)</span><br/>
  <table width='100%' cellspacing='5' cellpadding='0' border='0'>
  <tr><td><span class='bluetitle'>STAMPANTE:</span></td>
	  <td><select id="<?php echo $_MODULE_INFO['id']; ?>-printer" style="width:170px"><?php
		 $ret = GShell("lpr list");
		 $list = $ret['outarr'];
		 for($c=0; $c < count($list); $c++)
		  echo "<option value='".$list[$c]."'".(($list[$c] == $_MODULE_INFO['params']['config']['printer']) ? " selected='selected'>" : ">")
			.$list[$c]."</option>";
		?></select></td></tr>
  <tr><td><span class='bluetitle'>MOD. DI STAMPA:</span></td>
	  <td><select id="<?php echo $_MODULE_INFO['id']; ?>-printmodel" style="width:170px"><?php
		 $ret = GShell("dynarc item-list -ap printmodels -ct POS-RECEIPTS");
		 $list = $ret['outarr']['items'];
		 for($c=0; $c < count($list); $c++)
		  echo "<option value='".$list[$c]['id']."'".(($list[$c] == $_MODULE_INFO['params']['config']['printmodel']) ? " selected='selected'>" : ">")
			.$list[$c]['name']."</option>";
		?></select></td></tr>
  </table>
  <hr/>
  <span class='bluetitle'><input type='radio' name="<?php echo $_MODULE_INFO['id']; ?>-printmode" value='file' <?php if($_MODULE_INFO['params']['config']['printmode'] == "file") echo "checked='true'"; ?>/> Modalit&agrave; stampa su file</span><br/>
  <table width='100%' cellspacing='5' cellpadding='0' border='0'>
  <tr><td><span class='bluetitle'>Protocollo:</span></td>
	  <td><select id="<?php echo $_MODULE_INFO['id']; ?>-protocol" style="width:200px"><?php
		 echo "<option value=''>Seleziona un protocollo</option>";
		 $ret = GShell("pos get-protocols");
		 $list = $ret['outarr'];
		 for($c=0; $c < count($list); $c++)
		  echo "<option value='".$list[$c]['name']."'".(($list[$c]['name'] == $_MODULE_INFO['params']['config']['protocol']) ? " selected='selected'>" : ">").$list[$c]['description']."</option>";
		?></select></td></tr>
  </table>
  <hr/>
  <span class='bluetitle'><input type='checkbox' id="<?php echo $_MODULE_INFO['id']; ?>-fidcardenabled" <?php if($_MODULE_INFO['params']['config']['fidelitycardenabled'] == 'true') echo "checked='true'"; ?>/> Abilita fidelity card</span>
  <hr/>
  <span class='bluetitle'><input type='checkbox' id="<?php echo $_MODULE_INFO['id']; ?>-showpricelist" <?php if($_MODULE_INFO['params']['config']['showpricelist'] == 'true') echo "checked='true'"; ?>/> Mostra listino prezzi</span>
  <hr/>
  <span class='bluetitle'><input type='checkbox' id="<?php echo $_MODULE_INFO['id']; ?>-showcustomer" <?php if($_MODULE_INFO['params']['config']['showcustomer'] == 'true') echo "checked='true'"; ?>/> Mostra cliente</span>
  <hr/>
  <span class='bluetitle'><input type='checkbox' id="<?php echo $_MODULE_INFO['id']; ?>-hidecalcrest" <?php if($_MODULE_INFO['params']['config']['hidecalcrest'] == 'true') echo "checked='true'"; ?>/> Nascondi videata calcola resto</span>
  <hr/>
  <!--<span class='bluetitle'><input type='checkbox' id="<?php echo $_MODULE_INFO['id']; ?>-setaspaid" <?php if($_MODULE_INFO['params']['config']['setaspaid'] == 'true') echo "checked='true'"; ?>/> Segna il documento come pagato</span>
  <hr/>-->
  <?php
  $ret = GShell("dynarc cat-list -ap commercialdocs -pt '".$_MODULE_INFO['params']['config']['doctype']."'",$_REQUEST['sessid'],$_REQUEST['shellid']);
  if(!$ret['error'])
  {
   $catList = $ret['outarr'];
   if(count($catList)) 
   {
    echo "<span class='bluetitle'>Numerazione: <select id='".$_MODULE_INFO['id']."-catid' style='width:170px'>";
	// get main cat info //
	$ret = GShell("dynarc cat-info -ap commercialdocs -tag '".$_MODULE_INFO['params']['config']['doctype']."'",$_REQUEST['sessid'],$_REQUEST['shellid']);
	$mainCatInfo = $ret['outarr'];
	echo "<option value='".$mainCatInfo['id']."'"
		.(($mainCatInfo['id'] == $_MODULE_INFO['params']['config']['catid']) ? " selected='selected'>" : ">")."predefinita</option>";

	for($c=0; $c < count($catList); $c++)
	 echo "<option value='".$catList[$c]['id']."'"
		.(($catList[$c]['id'] == $_MODULE_INFO['params']['config']['catid']) ? " selected='selected'>" : ">")
		.$catList[$c]['name']."</option>";

	echo "</select><hr/>";
   }
  }
  ?>
  <span class="bluetitle">Scarica dal magazzino:</span>
  <select id="<?php echo $_MODULE_INFO['id']; ?>-storeid" style="width:150px">
   <option value='0'>nessuno</option>
   <?php
   $ret = GShell("store list",$_REQUEST['sessid'],$_REQUEST['shellid']);
   $list = $ret['outarr'];
   for($c=0; $c < count($list); $c++)
	echo "<option value='".$list[$c]['id']."'"
	.(($list[$c]['id'] == $_MODULE_INFO['params']['config']['storeid']) ? " selected='selected'>" : ">")
	.$list[$c]['name']."</option>";
   ?>
  </select>
  <hr/>
  <span class="bluetitle">Dimensione modulo:</span>
  <input type='text' class='edit' id="<?php echo $_MODULE_INFO['id']; ?>-modulewidth" style='width:50px' value="<?php echo $_MODULE_INFO['params']['config']['modulewidth'] ? $_MODULE_INFO['params']['config']['modulewidth'] : $_MODULE_INFO['defaultmodulewidth']; ?>"/>px
  <hr/>


  <input type='button' value='Applica' onclick="posmodule_save('<?php echo $_MODULE_INFO['id']; ?>')"/>
 </div>
</td></tr>
<tr><td height='32'>
	 <div class='plugbar'><i>Output</i>
	  <span class='moduleplug'>ON SUBMIT <img src="<?php echo $_BASE_PATH; ?>include/desktop/img/plug.png" class='plug' id="<?php echo $_MODULE_INFO['plugs'][0]; ?>" plugdir="down" plugname="submit" plugtype="output"/></span>
	 </div>
	</td></tr>
</table>
</div>

<!-- EOF - BACK PANEL -->

