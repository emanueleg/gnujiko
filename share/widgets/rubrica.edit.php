<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-05-2017
 #PACKAGE: rubrica
 #DESCRIPTION: Rubrica edit form
 #VERSION: 2.32beta
 #CHANGELOG: 13-05-2017 : Abilitato accesso remoto anche agli agenti.
			 04-05-2017 : Bug fix nome agente.
			 18-12-2016 : Aggiunto campi scontistica predefinita.
			 17-05-2016 : Aggiunto collegamento con account Gnujiko.
			 30-04-2016 : Aggiunta aliquota iva predefinita.
			 04-02-2016 : Adattamenti grafici.
			 15-04-2015 : Bug fix attachments.
			 03-04-2015 : Aggiunta funzione checkIPAcode x verificare se il cod. IPA è di 6 caratteri.
			 20-03-2015 : Aggiunto monte ore.
			 04-03-2015 : Aggiunto pacode.
			 19-01-2015 : Aggiunto bic/swift.
			 23-10-2014 : Ridimensionati campi CAP a 8 caratteri e IBAN a 31.
			 14-10-2014 : Aggiunta email predefinita.
			 29-09-2014 : Aggiunto punti fidelity card.
			 30-07-2014 : Bug fix alla linea 493
			 03-07-2014 : Aggiunto causali predefinite documenti.
			 26-05-2014 : Aggiunta banca d'appoggio.
			 30-04-2014 : Bug fix, non riportava l'agente.
			 28-02-2014 : Bug fix doppi-accenti nelle caselle di edit.
			 04-02-2014 : Aggiunto validatore partita iva, codice fiscale e bug fix vari.
			 10-12-2013 : Integrazione con gli agenti.
			 05-11-2013 : Aggiunto note extra.
			 07-09-2013 : Aggiunto fidelity card
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

global $_BASE_PATH, $_ABSOLUTE_URL, $_USERS_HOMES, $_IDOC_ENABLED, $_COMPANY_PROFILE, $_COMMERCIALDOCS_CONFIG;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");
include_once($_BASE_PATH."include/company-profile.php");
LoadLanguage("rubrica");

$_BANKS = $_COMPANY_PROFILE['banks'];
$_CPA = $_COMPANY_PROFILE['accounting'];

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

$ret = GShell("dynarc item-info -ap `".$ap."` -id `".$id."` -extget `rubricainfo,contacts,banks,references,predefdiscount"
	.($_IDOC_ENABLED ? ",idoc" : "")."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
if($ret['error'])
{
 $_REQUEST['title'] = "ERROR";
 $_REQUEST['contents'] = $ret['message'];
 include($_BASE_PATH."share/widgets/error.php");
 exit();
}

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
$db->RunQuery("SELECT tag,parent_id FROM dynarc_".$ap."_categories WHERE id='".$itemInfo['cat_id']."'");
$db->Read();
$itemInfo['cat_tag'] = $db->record['tag'];
$itemInfo['root_cat_tag'] = $db->record['tag'];
while($db->record['parent_id'] > 0)
{
 $db->RunQuery("SELECT tag,parent_id FROM dynarc_".$ap."_categories WHERE id='".$db->record['parent_id']."'");
 $db->Read();
 $itemInfo['root_cat_tag'] = $db->record['tag'];
}
$db->Close();

$titles = array('customers'=>i18n('Customer info'), 'vendors' => i18n('Vendor info'), 'shippers' => i18n('Shipper info'),
	 'collaborators' => i18n('Employee/Collaborator'), 'employees' => i18n('Employee/Collaborator'), 'agents' => i18n('Agent info'),
	 'members' => i18n('Member info')
	);

if($itemInfo['cat_tag'] && $titles[$itemInfo['cat_tag']])
 $title = $titles[$itemInfo['cat_tag']];
else if($titles[$itemInfo['root_cat_tag']])
 $title = $titles[$itemInfo['root_cat_tag']];

if($title == "") $title = i18n('Contact info');

$_HIDE_ACCOUNT_SECTION = true;
switch(strtoupper($itemInfo['root_cat_tag']))
{
 case 'EMPLOYEES' : case 'COLLABORATORS' : case 'AGENTS' : $_HIDE_ACCOUNT_SECTION = false; break;
}

/* Get startup page */
if($_REQUEST['focus'])
{
 $x = explode('.',$_REQUEST['focus']);
 $startuppage = $x[0];
 $focus = $x[1];
}

if(file_exists($_BASE_PATH."etc/commercialdocs/config.php"))
 include_once($_BASE_PATH."etc/commercialdocs/config.php");

/* GET LIST OF VAT RATES */
$_VAT_LIST = array();
$_VAT_BY_ID = array();

$ret = GShell("dynarc item-list -ap vatrates -get `percentage,vat_type`", $_REQUEST['sessid'], $_REQUEST['shellid']);
if(!$ret['error'])
{
 $_VAT_LIST = $ret['outarr']['items'];
 for($c=0; $c < count($_VAT_LIST); $c++)
  $_VAT_BY_ID[$_VAT_LIST[$c]['id']] = $_VAT_LIST[$c];
}

//-------------------------------------------------------------------------------------------------------------------//
?>
<html><head><title>Rubrica</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/rubrica.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/vatnumbervalidator.js" type="text/javascript"></script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/taxcodevalidator.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/layers.js"></script>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";</script>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/guploader/index.php");
include_once($_BASE_PATH."var/objects/fckeditor/index.php");
include_once($_BASE_PATH."var/objects/editsearch/index.php");
include_once($_BASE_PATH."include/vatnumbervalidator.php");
include_once($_BASE_PATH."include/taxcodevalidator.php");

$_INVALID_TAXCODE = $itemInfo['taxcode'] ? !validateTaxCode($itemInfo['taxcode']) : false;
$_INVALID_VATNUMBER = $itemInfo['vatnumber'] ? !validateVatNumber($itemInfo['vatnumber']) : false;

if(is_numeric($itemInfo['taxcode']))
 $_INVALID_TAXCODE = !validateVatNumber($itemInfo['taxcode']);

$_USER_LOGO = ($itemInfo['iscompany'] == 2) ? "share/widgets/rubrica/img/palogo.jpg" : "share/widgets/rubrica/img/user.png";

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

var USER_ID = <?php echo $itemInfo['user_id'] ? $itemInfo['user_id'] : '0'; ?>;
var CAT_TAG = "<?php echo $itemInfo['cat_tag']; ?>";
var ROOT_CT = "<?php echo $itemInfo['root_cat_tag']; ?>";

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
 <tr><td valign='bottom' width='220' height='35'><span class='title'><?php echo $title; ?></span><div class='subtitle' id='subtitle'><?php echo $itemInfo['name']; ?></div></td>
	 <td valign='bottom' class='toolbar'> 
		<ul class='rubrica-tab' id='rubrica-tabs'>
		 <li id='rubrica-details-tab' class='selected'><span onclick='_showPage("details")'><?php echo i18n('Details'); ?></span></li>
		 <li id='rubrica-contacts-tab'><span onclick='_showPage("contacts")'><?php echo i18n('Addresses'); ?></span></li>
		 <li id='rubrica-banks-tab'><span onclick='_showPage("banks")'><?php echo i18n('Banks'); ?></span></li>
		 <?php if($_IDOC_ENABLED)
		  echo "<li id='rubrica-extended-tab'><span onclick=\"_showPage('extended')\">".i18n('Sheets')."</span></li>";
		 ?>
		 <li id='rubrica-notes-tab'><span onclick='_showPage("notes")'>Note</span></li>
		 <li id='rubrica-attachments-tab'><span onclick='_showPage("attachments")'><?php echo i18n('Allegati'); ?></span></li>
		 <li id='rubrica-other-tab'><span href='#' onclick='_showPage("other")'><?php echo i18n('Other...'); ?></span></li>
		</ul>
	 </td><td valign='middle' width='20' align='right'><a href='#' title="<?php echo i18n('Close'); ?>" onclick='_abort()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/btn_close.png" border='0'/></a></td></tr>
 </table>

 <div class='rubrica-container'>

 <!-- DETAILS -->
 <div id='rubrica-details-page'>
  <table width="100%" border="0">
   <tr><td valign="middle"><img src="<?php echo $_ABSOLUTE_URL.$_USER_LOGO; ?>" id="userlogo"/><br/><br/>
		<span style='font-size:12px;'>Codice: </span><input type='text' size='5' id="code" class="edit" value="<?php echo $itemInfo['code_str']; ?>" onchange="codecheck(this)"/>
		<div id='pacode_container' style="padding-top:2px;<?php if($itemInfo['iscompany'] != 2) echo 'display:none'; ?>">
		 <span style='font-size:12px;'>Cod. IPA: </span><input type='text' class="edit" maxlength='6' style='width:60px;text-transform:uppercase' id="pacode" value="<?php echo $itemInfo['pacode']; ?>" onchange="checkIPAcode(this)"/>
		</div>
	   </td>
	   <td valign="top">
		<table width="100%" border="0">
		 <tr><td class='orange'><?php echo i18n('Name and surname / Company name'); ?></td>
			 <td class='orange'>&nbsp;</td></tr>
		 <tr><td class='gray'><input type="text" class="edit" style="width:360px;" id="name" value="<?php echo htmlspecialchars($itemInfo['name'],ENT_QUOTES); ?>"/></td>
			 <td class='gray' width='190'>
				<input type='radio' name='usertype' checked='true' id='iscompany' <?php if($itemInfo['iscompany'] == 1) echo "checked='true'"; ?> onclick='hidePACode()'><?php echo i18n('Company'); ?></input> 
				<input type='radio' name='usertype' value='0' <?php if(!$itemInfo['iscompany']) echo "checked='true'"; ?> onclick='hidePACode()'><?php echo i18n('Private'); ?></input>
				<input type='radio' name='usertype' value='2' id='ispa' <?php if($itemInfo['iscompany'] == 2) echo "checked='true'"; ?> onclick='showPACode()'><?php echo i18n('P.A.'); ?></input>
		 	 </td></tr>
  		</table>

  		<table width="100%" border="0" style="margin-top:25px">
		 <tr><td class='orange' width='150'><?php echo i18n('Tax Code'); ?></td> 
			 <td class='orange' width='150'><?php echo i18n('VAT number'); ?></td>
			 <td class='orange' width='120'><?php echo i18n("Fidelity card number"); ?></td>
			 <td class='orange'><?php if($itemInfo['cat_tag'] == "agents") echo i18n("Boss Agent"); else echo i18n("Agent"); ?></td></tr>
		 <tr><td class='gray'><input type="text" class="taxcodeedit<?php if($_INVALID_TAXCODE) echo ' error'; ?>" id="taxcode" value="<?php echo $itemInfo['taxcode']; ?>" onchange="_validateTaxCode(this)" maxlength='16'/></td>
			 <td class='gray'><input type="text" class="vatnumberedit<?php if($_INVALID_VATNUMBER) echo ' error'; ?>" id="vatnum" value="<?php echo $itemInfo['vatnumber']; ?>" onchange="_validateVatNumber(this)" maxlength='11'/></td>
			 <td class='gray'><input type="text" class="edit" style="width:130px" id="fidelitycard" value="<?php echo $itemInfo['fidelitycard']; ?>" onchange="fidelitycardcheck(this)"/></td>
			 <td class='gray'><input type="text" class="edit" style="width:130px" agentid="<?php echo $itemInfo['agent_id']; ?>" id="agent" value="<?php echo htmlspecialchars($itemInfo['agent_name'], ENT_QUOTES); ?>"/></td>
		 </tr>
 		 </table>

  		<table width="100%" border="0" style="margin-top:20px">
		 <tr><td class='orange' width='276'><?php echo i18n('Pricelist'); ?></td> 
			 <td class='orange'><?php echo i18n('Payment mode'); ?> &nbsp;&nbsp;<a href='#' onclick='showPMCfg()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/edit.png" border='0' style='text-align:right;vertical-align:middle;'/></a></td>
		 <tr><td class='gray'><select id='pricelist' style='width:280'><?php
				$ret = GShell("pricelists list",$_REQUEST['sessid'],$_REQUEST['shellid']);
				$list = $ret['outarr'];
				for($c=0; $c < count($list); $c++)
				 echo "<option value='".$list[$c]['id']."'".($list[$c]['id']==$itemInfo['pricelist_id'] ? " selected='selected'>" : ">")
					.$list[$c]['name']."</option>";
				?></select></td>
			 <td class='gray'><select id='paymentmode' style='width:250px'><?php
				$ret = GShell("paymentmodes list");
				$list = $ret['outarr'];
				if(!count($list))
				 echo "<option value='0'></option>";
				for($c=0; $c < count($list); $c++)
				 echo "<option value='".$list[$c]['id']."'".($list[$c]['id'] == $itemInfo['paymentmode'] ? " selected='selected'" : "").">".$list[$c]['name']."</option>";
				?></select></td>
		  </tr>

		 <tr><td colspan='2'>&nbsp;</td></tr>

		 </tr>
			 <td class='orange'>Banca d&lsquo;appoggio</td>
			 <td class='orange'>Aliq. IVA predef.</td>
		 </tr>
		 <tr><td class='gray'><select id='ourbanksupport' style='width:280px'><?php
				 for($c=0; $c < count($_BANKS); $c++)
				  echo "<option value='".$c."'".($itemInfo['ourbanksupport_id'] == $c ? " selected='selected'>" : ">").$_BANKS[$c]['name']."</option>";
				?></select></td>
			 <td class='gray'><select id='predefvat' style='width:250'>
				<option value='0' <?php if(!$itemInfo['vat_id']) echo "selected='selected'"; ?>>non definito</option>
				<?php
				 for($c=0; $c < count($_VAT_LIST); $c++)
				  echo "<option value='".$_VAT_LIST[$c]['id']."'"
					.(($_VAT_LIST[$c]['id'] == $itemInfo['vat_id']) ? " selected='selected'>" : ">")
					.$_VAT_LIST[$c]['code_str']." - ".$_VAT_LIST[$c]['name']."</option>";
				?>
			  </select>
			 </td>
			</tr>
		</table>
	   </td></tr>
  </table>
  <hr/>
  <table width="100%" cellspacing="0" cellpadding="0" border="0">
  <tr><td valign="top" width="450" style='font-size:10px'><?php echo i18n('Referrers'); ?>:<br/>
 		<table class="referlist" id="referlist" width="100%" cellspacing="1" cellpadding="0" border="0">
		 <tr><th width='150'><?php echo i18n('Name and Surname'); ?></th><th><?php echo i18n('Type of ref.'); ?></th><th><?php echo i18n('Phone'); ?></th><th><?php echo i18n('Email'); ?></th></tr>
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

 </div> <!-- EOF DETAILS

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
		<span class='rubrica-item-name'><?php echo i18n('Name:'); ?> </span>
		<input type='text' class='edit' style='width:250px' value="<?php echo htmlspecialchars($contact['name'],ENT_QUOTES); ?>" id='rubrica-item-name'/> 
		<span class='rubrica-item-name'><?php echo i18n('Codice:'); ?> </span>
		<input type='text' class='edit' style='width:50px' value="<?php echo htmlspecialchars($contact['code'],ENT_QUOTES); ?>" id='rubrica-item-code'/> 
		<input type='checkbox' id='rubrica-item-isdefault' checked="<?php if($contact['isdefault']) echo 'true'; else echo 'false'; ?>">
		<small><?php echo i18n('Def.'); ?></small></td>
		<td width='80' align='left' valign='middle'><a href='#' onclick='_contactSave()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/save-item-btn.png" border='0'/></a> <a href='#' onclick='_contactDelete()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/delete-item-btn.png" border='0'/></a></td></tr>
   </table>
  </div>
  <table width='100%' border='0' id='rubrica-contact-form' class='rubrica-item-info' <?php if(!$contact['id']) echo "style='visibility:hidden;'"; ?>>
  <tr><td align='right'><?php echo i18n('Address:'); ?></td>
	  <td><input type='text' class='edit' id='address' value="<?php echo $contact['address']; ?>" tabindex='1' style="width:200px"/></td>
	  <td align='right'><?php echo i18n('Phone:'); ?></td>
	  <td><input type='text' class='edit' id='phone' value="<?php echo $contact['phone']; ?>" tabindex='6' style="width:120px"/></td>
	  <td align='right'><?php echo i18n('Email:'); ?></td>
	  <td><input type='text' class='edit' id='email' value="<?php echo $contact['email']; ?>" tabindex='10' style="width:150px"/></td></tr>

  <tr><td align='right'><?php echo i18n('City:'); ?></td>
	  <td><input type='text' class='edit' id='city' value="<?php echo $contact['city']; ?>" tabindex='2' style="width:200px"/></td>
	  <td align='right'><?php echo i18n('Phone 2:'); ?></td>
	  <td><input type='text' class='edit' id='phone2' value="<?php echo $contact['phone2']; ?>" tabindex='7' style="width:120px"/></td>
	  <td align='right'><?php echo i18n('Email 2:'); ?></td>
	  <td><input type='text' class='edit' id='email2' value="<?php echo $contact['email2']; ?>" tabindex='11' style="width:150px"/></td></tr>

  <tr><td align='right'><?php echo i18n('ZIP:'); ?></td>
	  <td><input type='text' class='edit' id='zipcode' value="<?php echo $contact['zipcode']; ?>" tabindex='3' style="width:60px" maxlength='8'/> <?php echo i18n('Prov.:'); ?> <input type='text' class='edit' id='province' value="<?php echo $contact['province']; ?>" tabindex='4' style="width:30px;text-transform:uppercase;" maxlength="2"/></td>
	  <td align='right'><?php echo i18n('Fax:'); ?></td>
	  <td><input type='text' class='edit' id='fax' value="<?php echo $contact['fax']; ?>" tabindex='8' style="width:120px"/></td>
	  <td align='right'><?php echo i18n('Email 3:'); ?></td>
	  <td><input type='text' class='edit' id='email3' value="<?php echo $contact['email3']; ?>" tabindex='12' style="width:150px"/></td></tr>

  <tr><td align='right'><?php echo i18n('Country:'); ?></td>
	  <?php
	   // get country name by code
	   $ret = GShell("dynarc cat-info -ap countries -code '".$contact['countrycode']."'",$_REQUEST['sessid'],$_REQUEST['shellid']);
	   $countryName = $ret['outarr']['name'];
	  ?>
	  <td><input type='text' class='edit' style='width:100px' id='countrycode' retval="<?php echo $contact['countrycode']; ?>" value="<?php echo $countryName; ?>"/></td>
	  <td align='right'><?php echo i18n('Cell.:'); ?></td>
	  <td><input type='text' class='edit' id='cell' value="<?php echo $contact['cell']; ?>" tabindex='9' style="width:120px"/></td>
	  <td align='right'><?php echo i18n('Skype:'); ?></td>
	  <td><input type='text' class='edit' id='skype' value="<?php echo $contact['skype']; ?>" tabindex='13' style="width:150px"/></td></tr>
  </table>
  
 </div> <!-- EOF - CONTACTS -->

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
  <b>IBAN:</b> <input type='text' class='iban' style='text-transform:uppercase' size='4' maxlength='4' id='bank_iban_1' value="<?php echo substr($bank['iban'],0,4); ?>"/> - 
	<input type='text' class='iban' size='4' style='text-transform:uppercase' maxlength='4' id='bank_iban_2' value="<?php echo substr($bank['iban'],4,4); ?>"/> - 
	<input type='text' class='iban' size='4' style='text-transform:uppercase' maxlength='4' id='bank_iban_3' value="<?php echo substr($bank['iban'],8,4); ?>"/> - 
	<input type='text' class='iban' size='4' style='text-transform:uppercase' maxlength='4' id='bank_iban_4' value="<?php echo substr($bank['iban'],12,4); ?>"/> - 
	<input type='text' class='iban' size='4' style='text-transform:uppercase' maxlength='4' id='bank_iban_5' value="<?php echo substr($bank['iban'],16,4); ?>"/> - 
	<input type='text' class='iban' size='4' style='text-transform:uppercase' maxlength='4' id='bank_iban_6' value="<?php echo substr($bank['iban'],20,4); ?>"/> - 
	<input type='text' class='iban' size='7' style='width:60px;text-transform:uppercase' maxlength='7' id='bank_iban_7' value="<?php echo substr($bank['iban'],24); ?>"/><br/>
  <b>BIC/SWIFT:</b> <input type='text' class='iban' maxlength='11' style='width:85px' id='bank_bicswift' value="<?php echo $bank['bic_swift']; ?>"/>
  </div>

</div> <!-- EOF - BANKS -->


<!-- EXTRA NOTES -->
<div id='rubrica-notes-page' style='display:none;'>
 <div style="height:340px;border-top:1px solid #aaaaaa;margin-top:20px">
  <textarea id="extranotes" style="width:700px;height:450px"><?php echo $itemInfo['extranotes']; ?></textarea>
 </div>
</div> <!-- EOF - EXTRA NOTES -->



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
	 <div id='extended-page' style='height:430px;overflow:auto;clear:both;'>
	 <?php
	 for($c=0; $c < count($itemInfo['idocs']); $c++)
	 {
	  echo "<div class='idocpage' id='idocspace-".$itemInfo['idocs'][$c]['aid']."_".$itemInfo['idocs'][$c]['id']."'".($c==0 ? ">" : " style='display:none;'>")."</div>";
	 }
	 ?>
	 </div>
</div><?php } ?> <!-- EOF - EXTENDED -->

<!-- ATTACHMENTS -->
<div id='rubrica-attachments-page' style='display:none;'>
 <div style="height:440px;overflow:auto;margin-top:20px">
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
		 if(count($itemInfo['attachments']))
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
</div> <!-- EOF - ATTACHMENTS -->

<!-- OTHER -->
<div id='rubrica-other-page' style='display:none;'>
 <div style="height:440px;overflow:auto;border-top:1px solid #aaaaaa;margin-top:20px">
 <!-- SECTIONS -->
 <table width="100%" cellspacing="0" cellpadding="10" class='other-section-table' <?php if($_HIDE_ACCOUNT_SECTION) echo "style='display:none'"; ?>>
 <tr><td class='section-title'>Account Gnujiko</td>
	 <td>
	  <span class='smalltext' id='account-unregistered-span' <?php if($itemInfo['user_id']) echo "style='display:none'"; ?>>Collega questo contatto con un account Gnujiko</span>
	  <span class='smalltext' id='account-registered-span' <?php if(!$itemInfo['user_id']) echo "style='display:none'"; ?>>Questo contatto &egrave; collegato con un account Gnujiko!</span>
	 </td></tr>
 <tr><td width='200'>&nbsp;</td>
	 <td>
	  <div <?php if($itemInfo['user_id']) echo "style='display:none'"; ?> id='account-unregistered-container'>
	   <small>Login (nome utente per accedere a Gnujiko)</small><br/>
	   <input type='text' class='edit' style='width:250px' id='account-login'/><br/><br/>
	   <small>Password</small><br/>
  	   <input type='text' class='edit' style='width:250px' id='account-password'/>
	   <br/><br/>
	   <input type='button' class='button-blue' value="Registra" onclick="RegisterNewAccount()"/>
	  </div>

	  <div <?php if(!$itemInfo['user_id']) echo "style='display:none'"; ?> id='account-registered-container'>
	   <input type='button' class='button-blue' value="Mostra dettagli account" onclick="EditAccount()"/>
	  </div>

	 </td></tr>
 </table>

 <div class='other-section-table-container'>
 <table cellspacing="0" cellpadding="5" class='other-section-table' id='predefdiscount-table'>
 <tr><td colspan='2' class='section-title'>Scontistica</td></tr>
 <?php
  $_DISCOUNT_AT = array("gmart"=>"prodotti", "gserv"=>"servizi", "gproducts"=>"prodotti finiti", "gpart"=>"componenti / semilavorati", "gmaterial"=>"materiali", "glabor"=>"lavorazioni", "gbook"=>"libri");

  reset($_DISCOUNT_AT);
  while(list($k,$v) = each($_DISCOUNT_AT))
  {
   $ret = GShell("dynarc archive-list -type '".$k."' -a", $_REQUEST['sessid'], $_REQUEST['shellid']);
   if(!$ret['error'] && count($ret['outarr']))
   {
    echo "<tr><td colspan='2'>&nbsp;</td></tr>";
    echo "<tr><td colspan='2'><b>Applica la scontistica predefinita per ciascun catalogo ".$v."</b></td></tr>";
    for($c=0; $c < count($ret['outarr']); $c++)
    {
 	 $arc = $ret['outarr'][$c];
	 $perc = (is_array($itemInfo['predefdiscount']) && $itemInfo['predefdiscount'][$arc['prefix']] && $itemInfo['predefdiscount'][$arc['prefix']][0]) ? $itemInfo['predefdiscount'][$arc['prefix']][0] : 0;
	 echo "<tr><td>".$arc['name']."</td><td><input type='text' class='edit' style='width:40px' maxlength='3' data-ap='".$arc['prefix']."' value='"
		.$perc."'/>%</td></tr>";
    }
   }
  }
 ?>
 </table>
 </div>

 <table width="100%" cellspacing="0" cellpadding="10" class='other-section-table'>
 <tr><td colspan='2' class='section-title'>Email predefinita</td></tr>
 <tr><td width='200'>&nbsp;</td><td><small>Specifica l&lsquo;email predefinita per l&lsquo;invio di notifiche, fatture, report, ecc...</small><br/>
	<input type='text' style='width:340px' id='defaultemail' value="<?php echo $itemInfo['default_email']; ?>"/></td></tr>
 </table>

 <table width="100%" cellspacing="0" cellpadding="10" class='other-section-table'>
 <tr><td colspan='2' class='section-title'>Rimborsi chilometrici</td></tr>
 <tr><td width='200'><b>DISTANZA</b>: <input type='text' style='width:50px' value="<?php echo $itemInfo['distance']; ?>" id='distance'/> <span class='gray'>KM</span></td>
	 <td class='small'><i>Specifica la distanza tra te (sede operativa / luogo di partenza)<br/>e il cliente (destinazione). Questo dato verrà utilizzato nel caso<br/>vogliate includere eventuali rimborsi chilometrici nelle fatture.</i></td></tr>
 </table>

 <table width="100%" cellspacing="0" cellpadding="10" class='other-section-table'>
 <tr><td colspan='2' class='section-title'>Fidelity card</td></tr>
 <tr><td width='200'><b>N. PUNTI</b>: <input type='text' style='width:50px' value="<?php echo $itemInfo['fidelitycard_points']; ?>" id='fidelitycardpoints'/></td>
	 <td class='small'><i>Specifica il numero di punti accumulati.</i></td></tr>
 </table>

 <table width="100%" cellspacing="0" cellpadding="10" class='other-section-table'>
 <tr><td colspan='2' class='section-title'>Monte ore</td></tr>
 <tr><td width='200'><b>N. ORE</b>: <input type='text' style='width:50px' value="<?php echo $itemInfo['assist_avail_hours']; ?>" id='assistavailhours'/></td>
	 <td class='small'><i>Specifica il totale ore disponibili.</i></td></tr>
 </table>

 <table width="100%" cellspacing="0" cellpadding="10" class='other-section-table' id='gcdcausal-list'>
 <tr><td colspan='2' class='section-title'>Causali predefinite documenti</td></tr>
 <?php
 $ret = GShell("dynarc cat-list -ap gcdcausal --check-if-has-items",$_REQUEST['sessid'], $_REQUEST['shellid']);
 $causals = $ret['outarr'];
 for($c=0; $c < count($causals); $c++)
 {
  //if(!$causals[$c]['has_items'])
  // continue;
  echo "<tr doctag='".$causals[$c]['tag']."'><td width='200'><b>".$causals[$c]['name']."</b></td>";
  echo "<td><select style='width:250px'><option value='0'></option>";
  $ret = GShell("dynarc item-list -ap gcdcausal -cat '".$causals[$c]['id']."'",$_REQUEST['sessid'], $_REQUEST['shellid']);
  $list = $ret['outarr']['items'];
  for($i=0; $i < count($list); $i++)
   echo "<option value='".$list[$i]['id']."'".(($itemInfo['gcdcausals'][$causals[$c]['tag']] == $list[$i]['id']) ? " selected='selected'>" : ">")
		.$list[$i]['name']."</option>";
  $extratypes = $_COMMERCIALDOCS_CONFIG['DOCTYPE'][$causals[$c]['tag']];
  if(is_array($extratypes))
  {
   reset($extratypes);
   while(list($k,$v)=each($extratypes))
   {
    echo "<option value='".$k."'".(($itemInfo['gcdcausals'][$causals[$c]['tag']] == $k) ? " selected='selected'>" : ">").$v."</option>";
   }
  }
  echo "</select></td></tr>";
 }
 ?>
 </table> 

 <!--<table width="100%" cellspacing="0" cellpadding="10" class='other-section-table'>
 <tr><td class='section-title'>Eventi</td></tr>
 <tr><td><b>DATA DI NASCITA</b>: <input type='text' style='width:100px' value="24-01-1980" id='birthdate'/></td></tr>
 <tr><td class='small'>
	 <input type='checkbox'/> invia un&lsquo;email di buon compleanno 
	 <select>
	  <option value='-1'>un giorno prima</option>
	  <option value='0'>il giorno stesso</option>
	 </select>
	 con questo messaggio: <input type='text' class='edit' style='width:120px' placeholder="seleziona un messaggio"/>
	 </td></tr>
 <tr><td class='small'>
	 <input type='checkbox'/> invia un&lsquo;email di buon natale 
	 <select>
	  <option value='-1'>un giorno prima</option>
	  <option value='0'>il giorno stesso</option>
	 </select>
	 con questo messaggio: <input type='text' class='edit' style='width:120px' placeholder="seleziona un messaggio"/>
	 </td></tr>
 <tr><td class='small'>
	 <input type='checkbox'/> invia un&lsquo;email di buon anno 
	 <select>
	  <option value='-1'>un giorno prima</option>
	  <option value='0'>il giorno stesso</option>
	 </select>
	 con questo messaggio: <input type='text' class='edit' style='width:120px' placeholder="seleziona un messaggio"/>
	 </td></tr>

 </table> -->
 <!-- EOF - SECTIONS -->
 </div>

</div>

</div> <!-- EOF - OTHER -->

<div class='rubrica-footer'>
 <table width='100%' cellspacing='0' cellpadding='0' border='0'>
 <tr><td><?php
	 if($itemInfo['trash'])
	  echo "<span style='font-size:14px;color:#f31903;font-weight:bold'>Questo contatto si trova nel cestino!</span>";
	 else
	  echo "&nbsp;";
	 ?></td>
	 <td width='250' align='right'>
	  <input type='button' class='button-blue' value="<?php echo i18n('Save'); ?>" onclick="submit()"/> 
	  <input type='button' class='button-gray' value="<?php echo i18n('Close'); ?>" onclick="_abort()"/>
 	 </td>
  </tr>
 </table>
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

