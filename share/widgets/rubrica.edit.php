<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-09-2013
 #PACKAGE: rubrica
 #DESCRIPTION: Rubrica edit form
 #VERSION: 2.8beta
 #CHANGELOG: 07-09-2013 : Aggiunto fidelity card
			 08-05-2013 : Aggiunto i riferimenti
			 25-03-2013 : Aggiunto gli allegati
			 13-02-2013 : Integrato con iDoc
			 04-02-2013 : Bug fix with special chars.
			 31-01-2013 : Aggiunto campo 'distance'
			 12-01-2013 : UI adjusted.
			 21-06-2012 : Pricelist added.
			 18-01-2012 : Integration with gframe.
			 16-09-2011 : Aggiunto tag Collaboratore per integrare sistema gestione orario di lavoro.
			 30-07-2011 : Aggiunta possibilità di selezionare la pagina all'avvio e fare un focus ad una determinata casella.
			 26-02-2011 : Bug fix with special chars
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_USERS_HOMES, $_IDOC_ENABLED;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");
LoadLanguage("rubrica");

$ap = $_REQUEST['ap'] ? $_REQUEST['ap'] : "rubrica";
$id = $_REQUEST['id'];


/* Get if idoc extension is enabled */
$db = new AlpaDatabase();
$db->RunQuery("SELECT id FROM dynarc_archives WHERE tb_prefix='".$ap."'");
$db->Read();
$db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE extension_name='idoc' AND archive_id='".$db->record['id']."' LIMIT 1");
if($db->Read())
 $_IDOC_ENABLED = true;
$db->Close();

$ret = GShell("dynarc item-info -ap `".$ap."` -id `".$id."` -get `iscompany,taxcode,vatnumber,paymentmode,pricelist_id,distance,fidelitycard` -extget `contacts,banks,references".($_IDOC_ENABLED ? ",idoc" : "")."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
$itemInfo = $ret['outarr'];

if($_IDOC_ENABLED)
{
 for($c=0; $c < count($itemInfo['idocs']); $c++)
 {
 $ret = GShell("dynarc item-info -aid `".$itemInfo['idocs'][$c]['aid']."` -id `".$itemInfo['idocs'][$c]['id']."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
 $itemInfo['idocs'][$c]['name'] = $ret['outarr']['name'];
 }
}


/* GET ATTACHMENTS */
$ret = GShell("dynattachments list -ap '".$ap."' -refid ".$id,$_REQUEST['sessid'],$_REQUEST['shellid']);
$itemInfo['attachments'] = $ret['outarr']['items'];

/* Detect cat tag */
$db = new AlpaDatabase();
$db->RunQuery("SELECT tag FROM dynarc_".$ap."_categories WHERE id='".$itemInfo['cat_id']."'");
$db->Read();
$itemInfo['cat_tag'] = $db->record['tag'];
switch($db->record['tag'])
{
 case 'customers' : $title = i18n('Customer info'); break;
 case 'vendors' : $title = i18n('Vendor info'); break;
 case 'shippers' : $title = i18n('Shipper info'); break;
 case 'collaborators' : $title = i18n('Employee/Collaborator'); break;
 case 'agents' : $title = i18n('Agent info'); break;
 default : $title = i18n('Contact info'); break;
}
$db->Close();

/* Get startup page */
if($_REQUEST['focus'])
{
 $x = explode('.',$_REQUEST['focus']);
 $startuppage = $x[0];
 $focus = $x[1];
}

//-------------------------------------------------------------------------------------------------------------------//
?>
<html><head><title>Rubrica</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/rubrica.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/layers.js"></script>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";</script>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/guploader/index.php");
?>
<script>
var i18n = new Array();
i18n['Are you sure you want to delete this contact?'] = "<?php echo i18n('Are you sure you want to delete this contact?'); ?>";
i18n['Rename contact label'] = "<?php echo i18n('Rename contact label'); ?>";
i18n['Enter the name of the new bank'] = "<?php echo i18n('Enter the name of the new bank'); ?>";
i18n['Are you sure you want to delete this bank?'] = "<?php echo i18n('Are you sure you want to delete this bank?'); ?>";
i18n['Rename this bank'] = "<?php echo i18n('Rename this bank'); ?>";
i18n['There is already a contact with this code. Assign a different code.'] = "<?php echo i18n('There is already a contact with this code. Assign a different code.'); ?>";
i18n['There is already a contact with this fidelity card. Assign a different code.'] = "<?php echo i18n('There is already a contact with this fidelity card. Assign a different code.'); ?>";

</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/rubrica.js" type="text/javascript"></script>
</head><body>
<input type='hidden' id='archiveprefix' value="<?php echo $ap; ?>"/>
<input type='hidden' id='itemid' value="<?php echo $id; ?>"/>
<input type='hidden' id='startuppage' value="<?php echo $startuppage; ?>"/>
<input type='hidden' id='startupfocus' value="<?php echo $focus; ?>"/>

<div class="rubrica"><div style="padding:10px 18px 10px 18px;">
 <!-- TOOLBAR -->
 <table width='100%' cellspacing='0' cellpadding='0' border='0'>
 <tr><td valign='bottom' width='240' height='35'><span class='title'><?php echo $title; ?></small></span><div class='subtitle' id='subtitle'><?php echo $itemInfo['name']; ?></div></td>
	 <td valign='bottom' class='toolbar'> 
		<ul class='rubrica-tab' id='rubrica-tabs'>
		 <li id='rubrica-details-tab' class='selected'><a href='#' onclick='_showPage("details")'><?php echo i18n('Details'); ?></a></li>
		 <li id='rubrica-contacts-tab'><a href='#' onclick='_showPage("contacts")'><?php echo i18n('Addresses'); ?></a></li>
		 <li id='rubrica-banks-tab'><a href='#' onclick='_showPage("banks")'><?php echo i18n('Banks'); ?></a></li>
		 <?php if($_IDOC_ENABLED)
		  echo "<li id='rubrica-extended-tab'><a href='#' onclick=\"_showPage('extended')\">".i18n('Sheets')."</a></li>";
		 ?>
		 <li id='rubrica-attachments-tab'><a href='#' onclick='_showPage("attachments")'><?php echo i18n('Allegati'); ?></a></li>
		 <li id='rubrica-other-tab'><a href='#' onclick='_showPage("other")'><?php echo i18n('Other...'); ?></a></li>
		</ul>
	 </td><td valign='middle' width='20' align='right'><a href='#' title="<?php echo i18n('Close'); ?>" onclick='_abort()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/btn_close.png" border='0'/></a></td></tr>
 </table>

 <div class='rubrica-container'>

 <!-- DETAILS -->
 <div id='rubrica-details-page'>
  <table width="100%" border="0">
   <tr><td valign="middle"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/user.png"/><br/><br/>
		<span style='font-size:12px;'>Codice: </span><input type='text' size='5' id="code" value="<?php echo $itemInfo['code_str']; ?>" onchange="codecheck(this)"/></td>
	   <td valign="top">
		<table width="100%" border="0">
		 <tr><td class='orange' colspan='4'><?php echo i18n('Name and surname / Company name'); ?></td></tr>
		 <tr><td class='gray' colspan='4'><input type="text" class="edit" style="width:280px;" id="name" value="<?php echo $itemInfo['name']; ?>"/>
		 <input type='radio' name='usertype' checked='true' id='iscompany' <?php if($itemInfo['iscompany']) echo "checked='true'"; ?>><?php echo i18n('Company'); ?></input> <input type='radio' name='usertype' value='0' <?php if(!$itemInfo['iscompany']) echo "checked='true'"; ?>><?php echo i18n('Private'); ?></input>
		 </td></tr>

		 <tr><td colspan='4'>&nbsp;</td></tr> <!-- separator -->

		 <tr><td colspan='2' class='orange' width='50%'><?php echo i18n('Tax Code'); ?></td> 
			 <td class='orange' width='25%'><?php echo i18n('VAT number'); ?></td>
			 <td class='orange' width='25%'><?php echo i18n("Fidelity card number"); ?></td></tr>
		 <tr><td class='gray' colspan='2'><input type="text" class="edit" size="16" id="taxcode" value="<?php echo $itemInfo['taxcode']; ?>"/></td>
			 <td class='gray'><input type="text" class="edit" style="width:100px" id="vatnum" value="<?php echo $itemInfo['vatnumber']; ?>"/></td>
			 <td class='gray'><input type="text" class="edit" style="width:150px" id="fidelitycard" value="<?php echo $itemInfo['fidelitycard']; ?>" onchange="fidelitycardcheck(this)"/></td></tr>

		 <tr><td colspan='4'>&nbsp;</td></tr> <!-- separator -->

		 <tr><td class='orange' colspan='2'><?php echo i18n('Pricelist'); ?></td> 
			 <td class='orange' colspan='2'><?php echo i18n('Payment mode'); ?> &nbsp;&nbsp;<a href='#' onclick='showPMCfg()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/edit.png" border='0' style='text-align:right;vertical-align:middle;'/></a></td></tr>
		 <tr><td class='gray' colspan='2'><select id='pricelist'><?php
				$ret = GShell("pricelists list",$_REQUEST['sessid'],$_REQUEST['shellid']);
				$list = $ret['outarr'];
				for($c=0; $c < count($list); $c++)
				 echo "<option value='".$list[$c]['id']."'".($list[$c]['id']==$itemInfo['pricelist_id'] ? " selected='selected'>" : ">")
					.$list[$c]['name']."</option>";
				?></select></td>
			 <td class='gray' colspan='2'><select id='paymentmode'><?php
				$ret = GShell("paymentmodes list");
				$list = $ret['outarr'];
				if(!count($list))
				 echo "<option value='0'></option>";
				for($c=0; $c < count($list); $c++)
				 echo "<option value='".$list[$c]['id']."'".($list[$c]['id'] == $itemInfo['paymentmode'] ? " selected='selected'" : "").">".$list[$c]['name']."</option>";
				?></select></td></tr>
		</table>
	   </td></tr>
  </table>
  <hr/>
  <table width="100%" cellspacing="0" cellpadding="0" border="0">
  <tr><td valign="top" width="450" style='font-size:10px'>Riferimenti:<br/>
 		<table class="referlist" id="referlist" width="100%" cellspacing="1" cellpadding="0" border="0">
		 <tr><th width='150'>Nome e Cognome</th><th>Tipo di rif.</th><th>Telefono</th><th>Email</th></tr>
		 <?php
		 for($c=0; $c < 5; $c++)
		 {
		  $refInfo = $itemInfo['references'][$c];
		  echo "<tr".($refInfo ? " id='".$refInfo['id']."'" : "")."><td><input type='text' class='edit' style='width:150px' value='"
			.($refInfo ? $refInfo['name'] : "")."'/></td>";
		  echo "<td><input type='text' class='edit' style='width:100px' value='".($refInfo ? $refInfo['type'] : "")."'/></td>";
		  echo "<td><input type='text' class='edit' style='width:100px' value='".($refInfo ? $refInfo['phone'] : "")."'/></td>";
		  echo "<td><input type='text' class='edit' style='width:100px' value='".($refInfo ? $refInfo['email'] : "")."'/></td></tr>";
		 }
		 ?>
		</table>
	  </td><td valign="top" style="font-size:10px;padding-left:5px"><?php echo i18n('Notes:'); ?><br/>
	   <textarea id="notes" style="width:100%;height:120px;"><?php echo $itemInfo['desc']; ?></textarea>
	  </td></tr>
  </table>

<div class='rubrica-footer' align='right'>
 <input type='button' value="<?php echo i18n('Save'); ?>" onclick="submit()"/> <input type='button' value="<?php echo i18n('Close'); ?>" onclick="_abort()"/>
</div>

 </div>

 <!-- CONTACTS -->
 <div id='rubrica-contacts-page' style='display:none;'>
  <table width='100%' cellspacing='0' cellpadding='0' border='0' class='rubrica-list'>
  <tr><th width='180' style="text-align:left;"><?php echo i18n('CITY'); ?></th><th style="text-align:left;"><?php echo i18n('ADDRESS'); ?></th><th width='100'><?php echo i18n('PHONE'); ?></th></tr>
  </table>
  <div class="rubrica-list-div">
   <table width='100%' cellspacing='0' cellpadding='0' border='0' id='rubrica-list' class='rubrica-list' style='margin-top:0px;'>
  <?php
   $list = $itemInfo['contacts'];
   $idx = -1;
   for($c=0; $c < count($list); $c++)
   {
    echo "<tr id='contact-".$list[$c]['id']."' class='".($idx == -1 ? "selected" : "row$idx")."' onclick='_contactSelect(this)'>";
    echo "<td width='180'>".$list[$c]['city']."</td>";
	echo "<td>".$list[$c]['address']."</td>";
	echo "<td width='100'>".$list[$c]['phone']."</td>";
    echo "</tr>";
    $idx = $idx==0 ? 1 : 0;
   }
  ?>
	<tr><td><a href='#' onclick='_contactAdd()' style='font-size:13px;line-height:1.8em;color:#0169c9;'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/add.png" width="22" height="22" valign="middle" align="left" border="0"/> <?php echo i18n('Add'); ?></a></td></tr>
   </table>
  </div>

  <?php
  $contact = $itemInfo['contacts'][0] ? $itemInfo['contacts'][0] : array();
  ?>

  <input type='hidden' id='selectedcontactid' value="<?php echo $contact['id']; ?>"/>

  <div class='rubrica-preview-header' id='rubrica-contact-header' <?php if(!$contact['id']) echo "style='visibility:hidden;'"; ?>>
   <table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><td><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/contact.gif"/></td>
		<td><span id='rubrica-item-label'><?php echo $contact['label']; ?></span> <a href='#' onclick='_editContactLabel()' style='font-size:10px;color:#013397;'>[<?php echo i18n('rename'); ?>]</a><br/>
		<span class='rubrica-item-name'><?php echo i18n('Name:'); ?> </span><input type='text' size='30' value="<?php echo $contact['name']; ?>" id='rubrica-item-name'/> <input type='checkbox' id='rubrica-item-isdefault' checked="<?php if($contact['isdefault']) echo 'true'; else echo 'false'; ?>"><small><?php echo i18n('Def.'); ?></small></td>
		<td width='80' align='left' valign='middle'><a href='#' onclick='_contactSave()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/save-item-btn.png" border='0'/></a> <a href='#' onclick='_contactDelete()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/delete-item-btn.png" border='0'/></a></td></tr>
   </table>
  </div>
  <table width='100%' border='0' id='rubrica-contact-form' class='rubrica-item-info' <?php if(!$contact['id']) echo "style='visibility:hidden;'"; ?>>
  <tr><td align='right'><?php echo i18n('Address:'); ?></td><td><input type='text' size='14' id='address' value="<?php echo $contact['address']; ?>" tabindex='1' style="width:120px"/></td>
	  <td align='right'><?php echo i18n('Phone:'); ?></td><td><input type='text' size='12' id='phone' value="<?php echo $contact['phone']; ?>" tabindex='6' style="width:120px"/></td>
	  <td align='right'><?php echo i18n('Email:'); ?></td><td><input type='text' size='14' id='email' value="<?php echo $contact['email']; ?>" tabindex='10' style="width:120px"/></td></tr>
  <tr><td align='right'><?php echo i18n('City:'); ?></td><td><input type='text' size='14' id='city' value="<?php echo $contact['city']; ?>" tabindex='2' style="width:120px"/></td>
	  <td align='right'><?php echo i18n('Phone 2:'); ?></td><td><input type='text' size='12' id='phone2' value="<?php echo $contact['phone2']; ?>" tabindex='7' style="width:120px"/></td>
	  <td align='right'><?php echo i18n('Email 2:'); ?></td><td><input type='text' size='14' id='email2' value="<?php echo $contact['email2']; ?>" tabindex='11' style="width:120px"/></td></tr>
  <tr><td align='right'><?php echo i18n('ZIP:'); ?></td><td><input type='text' size='5' id='zipcode' value="<?php echo $contact['zipcode']; ?>" tabindex='3' style="width:50px"/> <?php echo i18n('Prov.:'); ?> <input type='text' size='2' id='province' value="<?php echo $contact['province']; ?>" tabindex='4' style="width:30px;text-transform:uppercase;"/></td>
	  <td align='right'><?php echo i18n('Fax:'); ?></td><td><input type='text' size='12' id='fax' value="<?php echo $contact['fax']; ?>" tabindex='8' style="width:120px"/></td>
	  <td align='right'><?php echo i18n('Email 3:'); ?></td><td><input type='text' size='14' id='email3' value="<?php echo $contact['email3']; ?>" tabindex='12' style="width:120px"/></td></tr>
  <tr><td align='right'><?php echo i18n('Country:'); ?></td><td><input type='text' size='2' id='countrycode' value="<?php echo $contact['countrycode']; ?>" tabindex='5' maxlength='2' style="width:30px;text-transform:uppercase"/></td>
	  <td align='right'><?php echo i18n('Cell.:'); ?></td><td><input type='text' size='12' id='cell' value="<?php echo $contact['cell']; ?>" tabindex='9' style="width:120px"/></td>
	  <td align='right'><?php echo i18n('Skype:'); ?></td><td><input type='text' size='14' id='skype' value="<?php echo $contact['skype']; ?>" tabindex='13' style="width:120px"/></td></tr>
  </table>
  
<div class='rubrica-footer' align='right' style='margin-top:16px'>
 <input type='button' value="<?php echo i18n('Save'); ?>" onclick="submit()"/> <input type='button' value="<?php echo i18n('Close'); ?>" onclick="_abort()"/>
</div>

 </div>

<!-- BANKS -->
<div id='rubrica-banks-page' style='display:none;'>
  <table width='100%' cellspacing='0' cellpadding='0' border='0' class='rubrica-list'>
  <tr><th width='200' style="text-align:left;"><?php echo i18n('BANK'); ?></th><th style="text-align:left;"><?php echo i18n('HOLDER'); ?></th><th width='170'><?php echo i18n('IBAN'); ?></th></tr>
  </table>
  <div class="rubrica-list-div">
   <table width='100%' cellspacing='0' cellpadding='0' border='0' id='rubrica-banks-list' class='rubrica-list' style='margin-top:0px;'>
  <?php
   $list = $itemInfo['banks'];
   $idx = -1;
   for($c=0; $c < count($list); $c++)
   {
    echo "<tr id='bank-".$list[$c]['id']."' class='".($idx == -1 ? "selected" : "row$idx")."' onclick='_bankSelect(this)'>";
    echo "<td width='200'>".$list[$c]['name']."</td>";
	echo "<td>".$list[$c]['holder']."</td>";
	echo "<td width='170'>".$list[$c]['iban']."</td>";
    echo "</tr>";
    $idx = $idx==0 ? 1 : 0;
   }
  ?>
	<tr><td><a href='#' onclick='_bankAdd()' style='font-size:13px;line-height:1.8em;color:#0169c9;'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/add.png" width="22" height="22" valign="middle" align="left" border="0"/> <?php echo i18n('Add'); ?></a></td></tr>
   </table>
  </div>
  <?php
  $bank = $itemInfo['banks'][0] ? $itemInfo['banks'][0] : array();
  ?>
  <input type='hidden' id='selectedbankid' value="<?php echo $bank['id']; ?>"/>

  <div class='rubrica-preview-header' id='rubrica-bank-header' <?php if(!$bank['id']) echo "style='visibility:hidden;'"; ?>>
   <table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><td><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/bank.png"/></td>
		<td><span id='rubrica-bank-name'><?php echo $bank['name']; ?></span> <a href='#' onclick='_editBankName()' style='font-size:10px;color:#013397;'>[<?php echo i18n('rename'); ?>]</a><br/>
		<span class='rubrica-bank-holder'><?php echo i18n('Holder:'); ?> </span><input type='text' size='30' value="<?php echo $bank['holder']; ?>" id='rubrica-bank-holder'/> <input type='checkbox' id='rubrica-bank-isdefault' checked="<?php if($bank['isdefault']) echo 'true'; else echo 'false'; ?>"><small><?php echo i18n('Def.'); ?></small></td>
		<td width='80' align='left' valign='middle'><a href='#' onclick='_bankSave()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/save-item-btn.png" border='0'/></a> <a href='#' onclick='_bankDelete()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/delete-item-btn.png" border='0'/></a></td></tr>
   </table>
  </div>
  <div id="rubrica-bank-form" <?php if(!$bank['id']) echo "style='visibility:hidden;'"; ?>><br/>
  <b>ABI:</b> <input type='text' size='5' maxlength='5' style="width:50px" id='bank_abi' value="<?php echo $bank['abi']; ?>"/> &nbsp;&nbsp;&nbsp;
  <b>CAB:</b> <input type='text' size='5' maxlength='5' style="width:50px" id='bank_cab' value="<?php echo $bank['cab']; ?>"/> &nbsp;&nbsp;&nbsp;
  <b>CIN:</b> <input type='text' size='1' maxlength='1' style="width:20px" id='bank_cin' value="<?php echo $bank['cin']; ?>"/> &nbsp;&nbsp;&nbsp;
  <b>CC:</b> <input type='text' size='12' maxlength='12' style="width:110px" id='bank_cc' value="<?php echo $bank['cc']; ?>"/><br/><br/>
  <b>IBAN:</b> <input type='text' class='iban' size='4' maxlength='4' id='bank_iban_1' value="<?php echo substr($bank['iban'],0,4); ?>"/> - 
	<input type='text' class='iban' size='4' maxlength='4' id='bank_iban_2' value="<?php echo substr($bank['iban'],4,4); ?>"/> - 
	<input type='text' class='iban' size='4' maxlength='4' id='bank_iban_3' value="<?php echo substr($bank['iban'],8,4); ?>"/> - 
	<input type='text' class='iban' size='4' maxlength='4' id='bank_iban_4' value="<?php echo substr($bank['iban'],12,4); ?>"/> - 
	<input type='text' class='iban' size='4' maxlength='4' id='bank_iban_5' value="<?php echo substr($bank['iban'],16,4); ?>"/> - 
	<input type='text' class='iban' size='4' maxlength='4' id='bank_iban_6' value="<?php echo substr($bank['iban'],20,4); ?>"/> - 
	<input type='text' class='iban' size='3' maxlength='3' id='bank_iban_7' style="width:30px;" value="<?php echo substr($bank['iban'],24,3); ?>"/> 
  </div>

<div class='rubrica-footer' align='right' style='margin-top:60px'>
 <input type='button' value="<?php echo i18n('Save'); ?>" onclick="submit()"/> <input type='button' value="<?php echo i18n('Close'); ?>" onclick="_abort()"/>
</div>

</div>

<!-- EXTENDED (IDOCS) -->
<?php if($_IDOC_ENABLED) { ?>
<div id='rubrica-extended-page' style='display:none;'>
	 <ul class='bluenuv' id='idoc-bluenuv' style='float:left;'>
	 <?php
	 for($c=0; $c < count($itemInfo['idocs']); $c++)
	 {
	  echo "<li".($c==0 ? " class='selected'" : "")." id='idoc-".$itemInfo['idocs'][$c]['aid']."_".$itemInfo['idocs'][$c]['id']."' onclick='idocShow(this)'><img src='".$_ABSOLUTE_URL."share/widgets/rubrica/img/delete-btn.gif' title='Elimina scheda' onclick='idocRemove(this)'/><span>".$itemInfo['idocs'][$c]['name']."</span></li>";
	 }
	 ?>
	 </ul>
	 <span class='link-green' style='float:left;' onclick="idocAdd()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/add-btn.png"/> Aggiungi</span>
	 <div id='extended-page' style='height:330px;overflow:auto;clear:both;'>
	 <?php
	 for($c=0; $c < count($itemInfo['idocs']); $c++)
	 {
	  echo "<div class='idocpage' id='idocspace-".$itemInfo['idocs'][$c]['aid']."_".$itemInfo['idocs'][$c]['id']."'".($c==0 ? ">" : " style='display:none;'>")."</div>";
	 }
	 ?>
	 </div>
	 <div class='rubrica-footer' align='right'>
	  <input type='button' value="<?php echo i18n('Save'); ?>" onclick="submit()"/> <input type='button' value="<?php echo i18n('Close'); ?>" onclick="_abort()"/>
	 </div>
</div><?php } ?>

<!-- ATTACHMENTS -->
<div id='rubrica-attachments-page' style='display:none;'>
 <div style="height:340px;overflow:auto;margin-top:20px">
	    <div class='attachments-toolbar'>
		 <table border='0' cellspacing='0' cellpadding='0' width='700' height='40'>
		  <tr><td width='50' style='padding-left:10px'><span class='smallblue'>Carica un<br/>file dal PC</span></td>
			 <td><div id='gupldspace'></div></td>
			 <td width='40' class='attachments-tb-buttons'><a href='#' onclick='selectFromServer("<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR']."/"; ?>")'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/load-from-server.png" border="0" title="Carica dal server"/></a></td>
			 <td width='40' class='attachments-tb-buttons'><a href='#' onclick='insertFromURL()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/link.png" border="0" title="Inserisci un link da URL"/></a></td>
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
		   echo "<a href='#' class='btnedit' onclick='editAttachment(".$item['id'].")' title='Modifica'><img src='".$_ABSOLUTE_URL."share/widgets/rubrica/img/edit_small.png' border='0'/></a>";
		   echo "<a href='#' class='btndel' onclick='deleteAttachment(".$item['id'].")' title='Rimuovi'><img src='".$_ABSOLUTE_URL."share/widgets/rubrica/img/delete_small.png' border='0'/></a>";
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
</div>

<!-- OTHER -->
<div id='rubrica-other-page' style='display:none;'>
 <div style="height:340px;overflow:auto;border-top:1px solid #aaaaaa;margin-top:20px">
 <!-- SECTIONS -->
 <table width="100%" cellspacing="0" cellpadding="10" class='other-section-table'>
 <tr><td colspan='2' class='section-title'>Rimborsi chilometrici</td></tr>
 <tr><td><b>DISTANZA</b>: <input type='text' style='width:50px' value="<?php echo $itemInfo['distance']; ?>" id='distance'/> <span class='gray'>KM</span></td>
	 <td class='small'><i>Specifica la distanza tra te (sede operativa / luogo di partenza)<br/>e il cliente (destinazione). Questo dato verrà utilizzato nel caso<br/>vogliate includere eventuali rimborsi chilometrici nelle fatture.</i></td></tr>
 </table>
 <!-- EOF - SECTIONS -->
 </div>
<div class='rubrica-footer' align='right'>
 <input type='button' value="<?php echo i18n('Save'); ?>" onclick="submit()"/> <input type='button' value="<?php echo i18n('Close'); ?>" onclick="_abort()"/>
</div>

</div>

</div>


</div>

<script>
var IDOCS = new Array();
var attUpld = null;
<?php
for($c=0; $c < count($itemInfo['idocs']); $c++)
 echo "IDOCS.push({aid:".$itemInfo['idocs'][$c]['aid'].",id:".$itemInfo['idocs'][$c]['id'].",isdefault:".($itemInfo['idocs'][$c]['default'] ? "true" : "false")."});\n";
?>
</script>

</body></html>
<?php

