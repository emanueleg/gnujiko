<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-10-2014
 #PACKAGE: companyprofile-config
 #DESCRIPTION: 
 #VERSION: 2.1beta
 #CHANGELOG: 23-10-2014 : Ridimensionato il campo CAP a 8 caratteri.
 #TODO:
 
*/

global $_COMPANY_PROFILE;

$regoff = $_COMPANY_PROFILE['addresses']['registered_office'];
$hq = $_COMPANY_PROFILE['addresses']['headquarters'];

$regoffPhones = $_COMPANY_PROFILE['addresses']['registered_office']['phones'];
$regoffFax = $_COMPANY_PROFILE['addresses']['registered_office']['fax'];
$regoffCells = $_COMPANY_PROFILE['addresses']['registered_office']['cells'];
$regoffEmails = $_COMPANY_PROFILE['addresses']['registered_office']['emails'];

$hqPhones = $_COMPANY_PROFILE['addresses']['headquarters']['phones'];
$hqFax = $_COMPANY_PROFILE['addresses']['headquarters']['fax'];
$hqCells = $_COMPANY_PROFILE['addresses']['headquarters']['cells'];
$hqEmails = $_COMPANY_PROFILE['addresses']['headquarters']['emails'];

$OL = $_COMPANY_PROFILE['addresses']['other'];
?>

<style type='text/css'>
table.contacts td {
	border-bottom: 1px solid #dadada;
}
</style>

<table class='section' width='100%' cellspacing='0' cellpadding='0' border='0' id='mastertable'>
<!-- SEDE LEGALE -->
<tr><td class='icon' rowspan='2' valign='top'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/registered_office.png"/></td>
	<td valign='top' colspan='2'><span class='title'><?php echo i18n('Registered office'); ?></span></td></tr>
<tr><td valign='top' class='left-block'>
	 <div class='orangebar'><?php echo i18n('ADDRESS'); ?></div>
	 <?php echo i18n('Address:'); ?> <input type='text' class='text' size='20' id='regoff_address' value="<?php echo $regoff['address']; ?>"/><br/>
	 <?php echo i18n('City:'); ?> <input type='text' class='text' size='20' id='regoff_city' value="<?php echo $regoff['city']; ?>"/><br/>
	 <?php echo i18n('Zip:'); ?> <input type='text' class='text' size='8' maxlength='8' id='regoff_zip' value="<?php echo $regoff['zip']; ?>"/> &nbsp; <?php echo i18n('Prov.:'); ?> <input type='text' class='text' size='2' maxlength='2' id='regoff_prov' value="<?php echo $regoff['prov']; ?>"/><br/>
	 <?php echo i18n('Country:'); ?> <input type='text' class='text' size='2' maxlength='2' id='regoff_country' value="<?php echo $regoff['country']; ?>"/><br/>
	 <br/>
	</td>
	<td width='360' valign='top' class='right-block'>
	 <div class='orangebar' style='margin-right:0px;'><?php echo i18n('CONTACTS'); ?></div>
	 
	 <table width='100%' class='contacts' cellspacing='0' cellpadding='0' border='0' id='regoff-contacts-list' <?php if(!count($regoffPhones) && !count($regoffFax) && !count($regoffCells) && !count($regoffEmails)) echo "style='display:none;'"; ?>>
	 <!-- PHONES -->
	 <tr><td width='68' align='center' valign='top'><a href="#" onclick="addPhone('regoff')" title="<?php echo i18n('Add phone'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-phone.png" border="0"/></a></td>
		 <td valign='top' id='regoff-contacts-phones'>
		 <?php
		 if(count($regoffPhones))
		 {
		  for($c=0; $c < count($regoffPhones); $c++)
		  {
		   ?>
		   <div>
			<input type='text' class='text' style='width:132px;' value="<?php echo $regoffPhones[$c]['name']; ?>"/> 
			<input type='text' class='text' style='width:132px;' value="<?php echo $regoffPhones[$c]['number']; ?>"/>
			<?php
			if($c > 0)
			 echo "<a href='#' onclick=\"deleteContactItem(this,'phone')\"><img src=\"".$_ABSOLUTE_URL."share/widgets/config-companyprofile/img/delete-small.png\" border=\"0\"/></a>";
			?>
		   </div>
		   <?php
		  }
		  if($c < 2)
		  {
		   ?>
		   <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Business, Home, ...)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'phone')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		   <?php
		  }
		 }
		 else
		 {
		  ?>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Business, Home, ...)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/></div>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Business, Home, ...)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'phone')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		  <?php
		 }
		 ?>
		 </td></tr>

	 <!-- FAX -->
	 <tr><td width='68' align='center' valign='top' style='padding-top:8px;'><a href="#" onclick="addFax('regoff')" title="<?php echo i18n('Add fax'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-fax.png" border="0"/></a></td>
		 <td valign='top' style='padding-top:8px;' id='regoff-contacts-fax'>
		 <?php
		 if(count($regoffFax))
		 {
		  for($c=0; $c < count($regoffFax); $c++)
		  {
		   ?>
		   <div>
			<input type='text' class='text' style='width:132px;' value="<?php echo $regoffFax[$c]['name']; ?>"/> 
			<input type='text' class='text' style='width:132px;' value="<?php echo $regoffFax[$c]['number']; ?>"/>
			<?php
			if($c > 0)
			 echo "<a href='#' onclick=\"deleteContactItem(this,'fax')\"><img src=\"".$_ABSOLUTE_URL."share/widgets/config-companyprofile/img/delete-small.png\" border=\"0\"/></a>";
			?>
		   </div>
		   <?php
		  }
		  if($c < 2)
		  {
		   ?>
		   <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Office Fax)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'fax')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		   <?php
		  }
		 }
		 else
		 {
		  ?>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Office Fax)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/></div>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Office Fax)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'fax')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		  <?php
		 }
		 ?>
		 </td></tr>

	 <!-- CELLS -->
	 <tr><td width='68' align='center' valign='top' style='padding-top:8px;'><a href="#" onclick="addCell('regoff')" title="<?php echo i18n('Add cell phone'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-cell.png" border="0"/></a></td>
		 <td valign='top' style='padding-top:8px;' id='regoff-contacts-cells'>
		 <?php
		 if(count($regoffCells))
		 {
		  for($c=0; $c < count($regoffCells); $c++)
		  {
		   ?>
		   <div>
			<input type='text' class='text' style='width:132px;' value="<?php echo $regoffCells[$c]['name']; ?>"/> 
			<input type='text' class='text' style='width:132px;' value="<?php echo $regoffCells[$c]['number']; ?>"/>
			<?php
			if($c > 0)
			 echo "<a href='#' onclick=\"deleteContactItem(this,'cell')\"><img src=\"".$_ABSOLUTE_URL."share/widgets/config-companyprofile/img/delete-small.png\" border=\"0\"/></a>";
			?>
		   </div>
		   <?php
		  }
		  if($c < 2)
		  {
		   ?>
		   <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Mario cell phone)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'cell')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		   <?php
		  }
		 }
		 else
		 {
		  ?>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Mario cell phone)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/></div>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Mario cell phone)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'cell')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		  <?php
		 }
		 ?>
		 </td></tr>

	 <!-- EMAILS -->
	 <tr><td width='68' align='center' valign='top' style='padding-top:8px;border-bottom:0px;'><a href="#" onclick="addEmail('regoff')" title="<?php echo i18n('Add email'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-email.png" border="0"/></a></td>
		 <td valign='top' style='padding-top:8px;border-bottom:0px;' id='regoff-contacts-emails'>
		 <?php
		 if(count($regoffEmails))
		 {
		  for($c=0; $c < count($regoffEmails); $c++)
		  {
		   ?>
		   <div>
			<input type='text' class='text' style='width:132px;' value="<?php echo $regoffEmails[$c]['name']; ?>"/> 
			<input type='text' class='text' style='width:132px;' value="<?php echo $regoffEmails[$c]['email']; ?>"/>
			<?php
			if($c > 0)
			 echo "<a href='#' onclick=\"deleteContactItem(this,'email')\"><img src=\"".$_ABSOLUTE_URL."share/widgets/config-companyprofile/img/delete-small.png\" border=\"0\"/></a>";
			?>
		   </div>
		   <?php
		  }
		  if($c < 2)
		  {
		   ?>
		   <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Administration)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('enter email'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'email')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		   <?php
		  }
		 }
		 else
		 {
		  ?>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Administration)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('enter email'); ?>" isempty="true"/></div>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Administration)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('enter email'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'email')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		  <?php
		 }
		 ?>
		 </td></tr>

	 </table>

	 <div align='center' id='regoff-contacts-empty-message' style="padding:12px;<?php if(count($regoffPhones) || count($regoffFax) || count($regoffCells) || count($regoffEmails)) echo 'display:none;'; ?>"><b><?php echo i18n('no address provided'); ?></b></div>

	 <div align="center" id='regoff-contacts-empty-buttons' style="padding:20px;<?php if(count($regoffPhones) || count($regoffFax) || count($regoffCells) || count($regoffEmails)) echo 'display:none;'; ?>">
	  <a href="#" onclick="addPhone('regoff')" title="<?php echo i18n('Add phone'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-phone.png" border="0"/></a>&nbsp;
	  <a href="#" onclick="addFax('regoff')" title="<?php echo i18n('Add fax'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-fax.png" border="0"/></a>&nbsp;
	  <a href="#" onclick="addCell('regoff')" title="<?php echo i18n('Add cell phone'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-cell.png" border="0"/></a>&nbsp;
	  <a href="#" onclick="addEmail('regoff')" title="<?php echo i18n('Add email'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-email.png" border="0"/></a>
	 </div>

	</td></tr>

<tr><td colspan='3' height='30'>&nbsp;</td></tr>

<!-- SEDE PRINCIPALE -->
<tr><td class='icon' rowspan='2' valign='top'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/headquarters.png"/></td>
	<td valign='top' colspan='2'><span class='title'><?php echo i18n('Operating Location / Headquarters'); ?> <input type="button" onclick="copyFromRegOff()" value="<?php echo i18n('copy from Registered office'); ?>" style='font-size:12px;color:#000000;margin-left:10px;'/></span> </td></tr>
<tr><td valign='top' class='left-block'>
	 <div class='orangebar'><?php echo i18n('ADDRESS'); ?></div>
	 <?php echo i18n('Address:'); ?> <input type='text' class='text' size='20' id='hq_address' value="<?php echo $hq['address']; ?>"/><br/>
	 <?php echo i18n('City:'); ?> <input type='text' class='text' size='20' id='hq_city' value="<?php echo $hq['city']; ?>"/><br/>
	 <?php echo i18n('Zip:'); ?> <input type='text' class='text' size='8' maxlength='8' id='hq_zip' value="<?php echo $hq['zip']; ?>"/> &nbsp; <?php echo i18n('Prov.:'); ?> <input type='text' class='text' size='2' maxlength='2' id='hq_prov' value="<?php echo $hq['prov']; ?>"/><br/>
	 <?php echo i18n('Country:'); ?> <input type='text' class='text' size='2' maxlength='2' id='hq_country' value="<?php echo $hq['country']; ?>"/><br/>
	 <br/>
	</td>
	<td width='360' valign='top' class='right-block'>
	 <div class='orangebar' style='margin-right:0px;'><?php echo i18n('CONTACTS'); ?></div>

	 <table width='100%' class='contacts' cellspacing='0' cellpadding='0' border='0' id='hq-contacts-list' <?php if(!count($hqPhones) && !count($hqFax) && !count($hqCells) && !count($hqEmails)) echo "style='display:none;'"; ?>>
	 <!-- PHONES -->
	 <tr><td width='68' align='center' valign='top'><a href="#" onclick="addPhone('hq')" title="<?php echo i18n('Add phone'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-phone.png" border="0"/></a></td>
		 <td valign='top' id='hq-contacts-phones'>
		 <?php
		 if(count($hqPhones))
		 {
		  for($c=0; $c < count($hqPhones); $c++)
		  {
		   ?>
		   <div>
			<input type='text' class='text' style='width:132px;' value="<?php echo $hqPhones[$c]['name']; ?>"/> 
			<input type='text' class='text' style='width:132px;' value="<?php echo $hqPhones[$c]['number']; ?>"/>
			<?php
			if($c > 0)
			 echo "<a href='#' onclick=\"deleteContactItem(this,'phone')\"><img src=\"".$_ABSOLUTE_URL."share/widgets/config-companyprofile/img/delete-small.png\" border=\"0\"/></a>";
			?>
		   </div>
		   <?php
		  }
		  if($c < 2)
		  {
		   ?>
		   <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Business, Home, ...)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'phone')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		   <?php
		  }
		 }
		 else
		 {
		  ?>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Business, Home, ...)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/></div>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Business, Home, ...)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'phone')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		  <?php
		 }
		 ?>
		 </td></tr>

	 <!-- FAX -->
	 <tr><td width='68' align='center' valign='top' style='padding-top:8px;'><a href="#" onclick="addFax('hq')" title="<?php echo i18n('Add fax'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-fax.png" border="0"/></a></td>
		 <td valign='top' style='padding-top:8px;' id='hq-contacts-fax'>
		 <?php
		 if(count($hqFax))
		 {
		  for($c=0; $c < count($hqFax); $c++)
		  {
		   ?>
		   <div>
			<input type='text' class='text' style='width:132px;' value="<?php echo $hqFax[$c]['name']; ?>"/> 
			<input type='text' class='text' style='width:132px;' value="<?php echo $hqFax[$c]['number']; ?>"/>
			<?php
			if($c > 0)
			 echo "<a href='#' onclick=\"deleteContactItem(this,'fax')\"><img src=\"".$_ABSOLUTE_URL."share/widgets/config-companyprofile/img/delete-small.png\" border=\"0\"/></a>";
			?>
		   </div>
		   <?php
		  }
		  if($c < 2)
		  {
		   ?>
		   <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Office Fax)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'fax')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		   <?php
		  }
		 }
		 else
		 {
		  ?>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Office Fax)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/></div>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Office Fax)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'fax')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		  <?php
		 }
		 ?>
		 </td></tr>

	 <!-- CELLS -->
	 <tr><td width='68' align='center' valign='top' style='padding-top:8px;'><a href="#" onclick="addCell('hq')" title="<?php echo i18n('Add cell phone'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-cell.png" border="0"/></a></td>
		 <td valign='top' style='padding-top:8px;' id='hq-contacts-cells'>
		 <?php
		 if(count($hqCells))
		 {
		  for($c=0; $c < count($hqCells); $c++)
		  {
		   ?>
		   <div>
			<input type='text' class='text' style='width:132px;' value="<?php echo $hqCells[$c]['name']; ?>"/> 
			<input type='text' class='text' style='width:132px;' value="<?php echo $hqCells[$c]['number']; ?>"/>
			<?php
			if($c > 0)
			 echo "<a href='#' onclick=\"deleteContactItem(this,'cell')\"><img src=\"".$_ABSOLUTE_URL."share/widgets/config-companyprofile/img/delete-small.png\" border=\"0\"/></a>";
			?>
		   </div>
		   <?php
		  }
		  if($c < 2)
		  {
		   ?>
		   <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Mario cell phone)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'cell')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		   <?php
		  }
		 }
		 else
		 {
		  ?>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Mario cell phone)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/></div>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Mario cell phone)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'cell')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		  <?php
		 }
		 ?>
		 </td></tr>

	 <!-- EMAILS -->
	 <tr><td width='68' align='center' valign='top' style='padding-top:8px;border-bottom:0px;'><a href="#" onclick="addEmail('hq')" title="<?php echo i18n('Add email'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-email.png" border="0"/></a></td>
		 <td valign='top' style='padding-top:8px;border-bottom:0px;' id='hq-contacts-emails'>
		 <?php
		 if(count($hqEmails))
		 {
		  for($c=0; $c < count($hqEmails); $c++)
		  {
		   ?>
		   <div>
			<input type='text' class='text' style='width:132px;' value="<?php echo $hqEmails[$c]['name']; ?>"/> 
			<input type='text' class='text' style='width:132px;' value="<?php echo $hqEmails[$c]['email']; ?>"/>
			<?php
			if($c > 0)
			 echo "<a href='#' onclick=\"deleteContactItem(this,'email')\"><img src=\"".$_ABSOLUTE_URL."share/widgets/config-companyprofile/img/delete-small.png\" border=\"0\"/></a>";
			?>
		   </div>
		   <?php
		  }
		  if($c < 2)
		  {
		   ?>
		   <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Administration)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('enter email'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'email')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		   <?php
		  }
		 }
		 else
		 {
		  ?>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Administration)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('enter email'); ?>" isempty="true"/></div>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Administration)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('enter email'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'email')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		  <?php
		 }
		 ?>
		 </td></tr>

	 </table>

	 <div align='center' id='hq-contacts-empty-message' style="padding:12px;<?php if(count($hqPhones) || count($hqFax) || count($hqCells) || count($hqEmails)) echo 'display:none;'; ?>"><b><?php echo i18n('no address provided'); ?></b></div>

	 <div align="center" id='hq-contacts-empty-buttons' style="padding:20px;<?php if(count($hqPhones) || count($hqFax) || count($hqCells) || count($hqEmails)) echo 'display:none;'; ?>">
	  <a href="#" onclick="addPhone('hq')" title="<?php echo i18n('Add phone'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-phone.png" border="0"/></a>&nbsp;
	  <a href="#" onclick="addFax('hq')" title="<?php echo i18n('Add fax'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-fax.png" border="0"/></a>&nbsp;
	  <a href="#" onclick="addCell('hq')" title="<?php echo i18n('Add cell phone'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-cell.png" border="0"/></a>&nbsp;
	  <a href="#" onclick="addEmail('hq')" title="<?php echo i18n('Add email'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-email.png" border="0"/></a>
	 </div>


	</td></tr>

<tr><td colspan='3' height='30'>&nbsp;</td></tr>
<tr><td colspan='3' valign='top' style="border-bottom:1px solid #dadada;">
	 <span class='title' style='float:left;margin-top:4px;'><?php echo i18n('Other locations'); ?></span>
		<a href='#' onclick="addNewLocation()" style='font-size:12px;color:#3364c3;font-weight:bold;text-decoration:none;float:left;white-space:nowrap;margin-left:50px;'>
		<img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/add.png" border="0" style="text-align:left;vertical-align:top;float:left;"/><?php echo i18n('add new<br/>location'); ?></a></td></tr>

<tr id="init-of-other-loc"><td colspan='3' height='10'>&nbsp;</td></tr>
<?php
for($c=0; $c < count($OL); $c++)
{
 ?>
<!-- ALTRA SEDE -->
<tr id="otherloc_<?php echo $c; ?>_masterrow"><td class='icon' rowspan='2' valign='top'>
		<img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/other_location.png"/><br/>
		<span style="font-size:100px;font-family:Arial;color:#eeeeee;"><?php echo ($c+1); ?></span></td>
	<td valign='top' colspan='2'><span style="font-family:Arial;font-size:14px;font-weight:bold;color:#013397;"><?php echo i18n('Name:'); ?> </span><input type='text' size='40' class='text' id="otherloc_<?php echo $c; ?>_name" value="<?php echo $OL[$c]['name']; ?>"/> <a href='#' style='float:right;margin-bottom:6px;' onclick="deleteOL(<?php echo $c; ?>)"><img src="<?php echo $_ABSOLUTE_URL; ?>/share/widgets/config-companyprofile/img/btn_del.png" border='0'/></a></td></tr>
<tr><td valign='top' class='left-block'>
	 <div class='orangebar'><?php echo i18n('ADDRESS'); ?></div>
	 <?php echo i18n('Address:'); ?> <input type='text' class='text' size='20' id="otherloc_<?php echo $c; ?>_address" value="<?php echo $OL[$c]['address']; ?>"/><br/>
	 <?php echo i18n('City:'); ?> <input type='text' class='text' size='20' id="otherloc_<?php echo $c; ?>_city" value="<?php echo $OL[$c]['city']; ?>"/><br/>
	 <?php echo i18n('Zip:'); ?> <input type='text' class='text' size='8' maxlength='8' id="otherloc_<?php echo $c; ?>_zip" value="<?php echo $OL[$c]['zip']; ?>"/> &nbsp; <?php echo i18n('Prov.:'); ?> <input type='text' class='text' size='2' maxlength='2' id="otherloc_<?php echo $c; ?>_prov" value="<?php echo $OL[$c]['prov']; ?>"/><br/>
	 <?php echo i18n('Country:'); ?> <input type='text' class='text' size='2' maxlength='2' id="otherloc_<?php echo $c; ?>_country" value="<?php echo $OL[$c]['country']; ?>"/><br/>
	 <br/>
	</td>
	<td width='360' valign='top' class='right-block'>
	 <div class='orangebar' style='margin-right:0px;'><?php echo i18n('CONTACTS'); ?></div>

	 <table width='100%' class='contacts' cellspacing='0' cellpadding='0' border='0' id="otherloc-<?php echo $c; ?>-contacts-list" <?php if(!count($OL[$c]['phones']) && !count($OL[$c]['fax']) && !count($OL[$c]['cells']) && !count($OL[$c]['emails'])) echo "style='display:none;'"; ?>>
	 <!-- PHONES -->
	 <tr><td width='68' align='center' valign='top'><a href="#" onclick="addPhone('otherloc-<?php echo $c; ?>')" title="<?php echo i18n('Add phone'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-phone.png" border="0"/></a></td>
		 <td valign='top' id="otherloc-<?php echo $c; ?>-contacts-phones">
		 <?php
		 if(count($OL[$c]['phones']))
		 {
		  for($i=0; $i < count($OL[$c]['phones']); $i++)
		  {
		   ?>
		   <div>
			<input type='text' class='text' style='width:132px;' value="<?php echo $OL[$c]['phones'][$i]['name']; ?>"/> 
			<input type='text' class='text' style='width:132px;' value="<?php echo $OL[$c]['phones'][$i]['number']; ?>"/>
			<?php
			if($i > 0)
			 echo "<a href='#' onclick=\"deleteContactItem(this,'phone')\"><img src=\"".$_ABSOLUTE_URL."share/widgets/config-companyprofile/img/delete-small.png\" border=\"0\"/></a>";
			?>
		   </div>
		   <?php
		  }
		  if($i < 2)
		  {
		   ?>
		   <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Business, Home, ...)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'phone')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		   <?php
		  }
		 }
		 else
		 {
		  ?>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Business, Home, ...)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/></div>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Business, Home, ...)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'phone')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		  <?php
		 }
		 ?>
		 </td></tr>

	 <!-- FAX -->
	 <tr><td width='68' align='center' valign='top' style='padding-top:8px;'><a href="#" onclick="addFax('otherloc-<?php echo $c; ?>')" title="<?php echo i18n('Add fax'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-fax.png" border="0"/></a></td>
		 <td valign='top' style='padding-top:8px;' id="otherloc-<?php echo $c; ?>-contacts-fax">
		 <?php
		 if(count($OL[$c]['fax']))
		 {
		  for($i=0; $i < count($OL[$c]['fax']); $i++)
		  {
		   ?>
		   <div>
			<input type='text' class='text' style='width:132px;' value="<?php echo $OL[$c]['fax'][$i]['name']; ?>"/> 
			<input type='text' class='text' style='width:132px;' value="<?php echo $OL[$c]['fax'][$i]['number']; ?>"/>
			<?php
			if($i > 0)
			 echo "<a href='#' onclick=\"deleteContactItem(this,'fax')\"><img src=\"".$_ABSOLUTE_URL."share/widgets/config-companyprofile/img/delete-small.png\" border=\"0\"/></a>";
			?>
		   </div>
		   <?php
		  }
		  if($i < 2)
		  {
		   ?>
		   <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Office Fax)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'fax')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		   <?php
		  }
		 }
		 else
		 {
		  ?>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Office Fax)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/></div>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('Title: (eg: Office Fax)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'fax')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		  <?php
		 }
		 ?>
		 </td></tr>

	 <!-- CELLS -->
	 <tr><td width='68' align='center' valign='top' style='padding-top:8px;'><a href="#" onclick="addCell('otherloc-<?php echo $c; ?>')" title="<?php echo i18n('Add cell phone'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-cell.png" border="0"/></a></td>
		 <td valign='top' style='padding-top:8px;' id="otherloc-<?php echo $c; ?>-contacts-cells">
		 <?php
		 if(count($OL[$c]['cells']))
		 {
		  for($i=0; $i < count($OL[$c]['cells']); $i++)
		  {
		   ?>
		   <div>
			<input type='text' class='text' style='width:132px;' value="<?php echo $OL[$c]['cells'][$i]['name']; ?>"/> 
			<input type='text' class='text' style='width:132px;' value="<?php echo $OL[$c]['cells'][$i]['number']; ?>"/>
			<?php
			if($i > 0)
			 echo "<a href='#' onclick=\"deleteContactItem(this,'cell')\"><img src=\"".$_ABSOLUTE_URL."share/widgets/config-companyprofile/img/delete-small.png\" border=\"0\"/></a>";
			?>
		   </div>
		   <?php
		  }
		  if($i < 2)
		  {
		   ?>
		   <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Mario cell phone)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'cell')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		   <?php
		  }
		 }
		 else
		 {
		  ?>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Mario cell phone)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/></div>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Mario cell phone)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('insert number'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'cell')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		  <?php
		 }
		 ?>
		 </td></tr>

	 <!-- EMAILS -->
	 <tr><td width='68' align='center' valign='top' style='padding-top:8px;border-bottom:0px;'><a href="#" onclick="addEmail('otherloc-<?php echo $c; ?>')" title="<?php echo i18n('Add email'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-email.png" border="0"/></a></td>
		 <td valign='top' style='padding-top:8px;border-bottom:0px;' id="otherloc-<?php echo $c; ?>-contacts-emails">
		 <?php
		 if(count($OL[$c]['emails']))
		 {
		  for($i=0; $i < count($OL[$c]['emails']); $i++)
		  {
		   ?>
		   <div>
			<input type='text' class='text' style='width:132px;' value="<?php echo $OL[$c]['emails'][$i]['name']; ?>"/> 
			<input type='text' class='text' style='width:132px;' value="<?php echo $OL[$c]['emails'][$i]['email']; ?>"/>
			<?php
			if($i > 0)
			 echo "<a href='#' onclick=\"deleteContactItem(this,'email')\"><img src=\"".$_ABSOLUTE_URL."share/widgets/config-companyprofile/img/delete-small.png\" border=\"0\"/></a>";
			?>
		   </div>
		   <?php
		  }
		  if($i < 2)
		  {
		   ?>
		   <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Administration)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('enter email'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'email')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		   <?php
		  }
		 }
		 else
		 {
		  ?>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Administration)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('enter email'); ?>" isempty="true"/></div>
		  <div><input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('title: (eg: Administration)'); ?>" isempty="true"/> 
			   <input type='text' class='text-empty' style='width:132px;' onfocus="inpFocus(this)" onblur="inpBlur(this)" value="<?php echo i18n('enter email'); ?>" isempty="true"/> <a href='#' onclick="deleteContactItem(this,'email')"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png" border="0"/></a></div>
		  <?php
		 }
		 ?>
		 </td></tr>

	 </table>


	 <div align='center' id="otherloc-<?php echo $c; ?>-contacts-empty-message" style="padding:12px;<?php if(count($OL[$c]['phones']) || count($OL[$c]['fax']) || count($OL[$c]['cells']) || count($OL[$c]['emails'])) echo 'display:none;'; ?>"><b><?php echo i18n('no address provided'); ?></b></div>

	 <div align="center" id="otherloc-<?php echo $c; ?>-contacts-empty-buttons" style="padding:20px;<?php if(count($OL[$c]['phones']) || count($OL[$c]['fax']) || count($OL[$c]['cells']) || count($OL[$c]['emails'])) echo 'display:none;'; ?>">
	  <a href="#" onclick="addPhone('otherloc-<?php echo $c; ?>')" title="<?php echo i18n('Add phone'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-phone.png" border="0"/></a>&nbsp;
	  <a href="#" onclick="addFax('otherloc-<?php echo $c; ?>')" title="<?php echo i18n('Add fax'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-fax.png" border="0"/></a>&nbsp;
	  <a href="#" onclick="addCell('otherloc-<?php echo $c; ?>')" title="<?php echo i18n('Add cell phone'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-cell.png" border="0"/></a>&nbsp;
	  <a href="#" onclick="addEmail('otherloc-<?php echo $c; ?>')" title="<?php echo i18n('Add email'); ?>"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-email.png" border="0"/></a>
	 </div>



	</td></tr>

<tr><td colspan='3' height='10'>&nbsp;</td></tr>
<?php
}
?>

</table>

<div align='right' style='padding-top:5px;'>
<input type='button' value="<?php echo i18n('Abort'); ?>" onclick="gframe_close()"/> <input type='button' value="<?php echo i18n('Apply'); ?>" onclick='formSubmit()'/> <input type='button' value="<?php echo i18n('Save and close'); ?>" onclick="formSubmit(true)"/>
</div>

<script>
var OL_NUM = <?php echo count($OL); ?>;

function addPhone(section)
{
 document.getElementById(section+'-contacts-empty-message').style.display='none';
 document.getElementById(section+'-contacts-empty-buttons').style.display='none';
 document.getElementById(section+'-contacts-list').style.display='';

 var list = document.getElementById(section+'-contacts-phones').getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
 {
  var imp = list[c].getElementsByTagName('INPUT')[0];
  if((imp.getAttribute("isempty") == "true") || (imp.value == ""))
  {
   imp.focus();
   return;
  }
 }

 var div = document.createElement('DIV');
 div.innerHTML = "<input type='text' class='text' style='width:132px;'/ > <input type='text' class='text' style='width:132px;'/ > <a href='#' onclick='deleteContactItem(this,\"phone\")'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png' border='0'/ ></a>";
 document.getElementById(section+'-contacts-phones').appendChild(div);

 div.getElementsByTagName('INPUT')[0].focus();
}

function deleteContactItem(aObj,type)
{
 var msg = "";
 switch(type)
 {
  case 'phone' : msg = "<?php echo i18n('Are you sure you want to remove this phone number?'); ?>"; break;
  case 'fax' : msg = "<?php echo i18n('Are you sure you want to remove this fax number?'); ?>"; break;
  case 'cell' : msg = "<?php echo i18n('Are you sure you want to remove this cell phone number?'); ?>"; break;
  case 'email' : msg = "<?php echo i18n('Are you sure you want to remove this email?'); ?>"; break;
 }

 if(!confirm(msg))
  return;

 aObj.parentNode.parentNode.removeChild(aObj.parentNode);
}

function addFax(section)
{
 document.getElementById(section+'-contacts-empty-message').style.display='none';
 document.getElementById(section+'-contacts-empty-buttons').style.display='none';
 document.getElementById(section+'-contacts-list').style.display='';

 var list = document.getElementById(section+'-contacts-fax').getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
 {
  var imp = list[c].getElementsByTagName('INPUT')[0];
  if((imp.getAttribute("isempty") == "true") || (imp.value == ""))
  {
   imp.focus();
   return;
  }
 }

 var div = document.createElement('DIV');
 div.innerHTML = "<input type='text' class='text' style='width:132px;'/ > <input type='text' class='text' style='width:132px;'/ > <a href='#' onclick='deleteContactItem(this,\"fax\")'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png' border='0'/ ></a>";
 document.getElementById(section+'-contacts-fax').appendChild(div);

 div.getElementsByTagName('INPUT')[0].focus();
}

function addCell(section)
{
 document.getElementById(section+'-contacts-empty-message').style.display='none';
 document.getElementById(section+'-contacts-empty-buttons').style.display='none';
 document.getElementById(section+'-contacts-list').style.display='';

 var list = document.getElementById(section+'-contacts-cells').getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
 {
  var imp = list[c].getElementsByTagName('INPUT')[0];
  if((imp.getAttribute("isempty") == "true") || (imp.value == ""))
  {
   imp.focus();
   return;
  }
 }

 var div = document.createElement('DIV');
 div.innerHTML = "<input type='text' class='text' style='width:132px;'/ > <input type='text' class='text' style='width:132px;'/ > <a href='#' onclick='deleteContactItem(this,\"cell\")'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png' border='0'/ ></a>";
 document.getElementById(section+'-contacts-cells').appendChild(div);

 div.getElementsByTagName('INPUT')[0].focus();

}

function addEmail(section)
{
 document.getElementById(section+'-contacts-empty-message').style.display='none';
 document.getElementById(section+'-contacts-empty-buttons').style.display='none';
 document.getElementById(section+'-contacts-list').style.display='';

 var list = document.getElementById(section+'-contacts-emails').getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
 {
  var imp = list[c].getElementsByTagName('INPUT')[0];
  if((imp.getAttribute("isempty") == "true") || (imp.value == ""))
  {
   imp.focus();
   return;
  }
 }

 var div = document.createElement('DIV');
 div.innerHTML = "<input type='text' class='text' style='width:132px;'/ > <input type='text' class='text' style='width:132px;'/ > <a href='#' onclick='deleteContactItem(this,\"email\")'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png' border='0'/ ></a>";
 document.getElementById(section+'-contacts-emails').appendChild(div);

 div.getElementsByTagName('INPUT')[0].focus();

}

function inpFocus(inp)
{
 if(inp.getAttribute("isempty") == "true")
 {
  inp.className = "text";
  inp.value = "";
 }
}

function inpBlur(inp)
{
 if(inp.getAttribute("isempty") == "true")
 {
  if(inp.value == "")
  {
   inp.className = "text-empty";
   inp.value = inp.defaultValue;
  }
  else
  {
   inp.setAttribute("isempty","false");
  }
 }
}

//-------------------------------------------------------------------------------------------------------------------//
function addNewLocation()
{
 var idx = OL_NUM;
 OL_NUM++;

 var r = document.getElementById('mastertable').insertRow(-1);
 r.id = "otherloc_"+idx+"_masterrow";

 r.insertCell(-1).innerHTML = "<img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/other_location.png'/ ><br/ > <span style='font-size:100px;font-family:Arial;color:#eeeeee;'>"+(idx+1)+"</span>";
 r.cells[0].className = "icon"; r.cells[0].rowSpan=2; r.cells[0].style.verticalAlign='top';

 r.insertCell(-1).innerHTML = "<span style='font-family:Arial;font-size:14px;font-weight:bold;color:#013397;'><?php echo i18n('Name:'); ?> </span><input type='text' size='40' class='text' id='otherloc_"+idx+"_name' value=''/ > <a href='#' style='float:right;margin-bottom:6px;' onclick='deleteOL("+idx+")'><img src='<?php echo $_ABSOLUTE_URL; ?>/share/widgets/config-companyprofile/img/btn_del.png' border='0'/ ></a>";
 r.cells[1].colSpan=2; r.cells[1].style.verticalAlign='top';


 var r = document.getElementById('mastertable').insertRow(-1);
 
 r.insertCell(-1).innerHTML = "<div class='orangebar'><?php echo i18n('ADDRESS'); ?></div> <?php echo i18n('Address:'); ?> <input type='text' class='text' size='20' id='otherloc_"+idx+"_address' value=''/ ><br/ > <?php echo i18n('City:'); ?> <input type='text' class='text' size='20' id='otherloc_"+idx+"_city' value=''/ ><br/ > <?php echo i18n('Zip:'); ?> <input type='text' class='text' size='8' maxlength='8' id='otherloc_"+idx+"_zip' value=''/ > &nbsp; <?php echo i18n('Prov.:'); ?> <input type='text' class='text' size='2' maxlength='2' id='otherloc_"+idx+"_prov' value=''/ ><br/ > <?php echo i18n('Country:'); ?> <input type='text' class='text' size='2' maxlength='2' id='otherloc_"+idx+"_country' value=''/ ><br/ ><br/ >";
 r.cells[0].className = "left-block"; r.cells[0].style.verticalAlign='top';

 r.insertCell(-1).innerHTML = "<div class='orangebar' style='margin-right:0px;'><?php echo i18n('CONTACTS'); ?></div> <table width='100%' class='contacts' cellspacing='0' cellpadding='0' border='0' id='otherloc-"+idx+"-contacts-list' style='display:none;'> <tr><td width='68' align='center' valign='top'><a href='#' onclick='addPhone(\"otherloc-"+idx+"\")' title='<?php echo i18n('Add phone'); ?>'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-phone.png' border='0'/ ></a></td><td valign='top' id='otherloc-"+idx+"-contacts-phones'> <div><input type='text' class='text-empty' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' value='<?php echo i18n('Title: (eg: Business, Home, ...)'); ?>' isempty='true'/ > <input type='text' class='text-empty' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' value='<?php echo i18n('insert number'); ?>' isempty='true'/ ></div> <div><input type='text' class='text-empty' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' value='<?php echo i18n('Title: (eg: Business, Home, ...)'); ?>' isempty='true'/ > <input type='text' class='text-empty' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' value='<?php echo i18n('insert number'); ?>' isempty='true'/ > <a href='#' onclick='deleteContactItem(this,\"phone\")'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png' border='0'/ ></a></div></td></tr> <tr><td width='68' align='center' valign='top' style='padding-top:8px;'><a href='#' onclick='addFax(\"otherloc-"+idx+"\")' title='<?php echo i18n('Add fax'); ?>'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-fax.png' border='0'/ ></a></td> <td valign='top' style='padding-top:8px;' id='otherloc-"+idx+"-contacts-fax'> <div><input type='text' class='text-empty' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' value='<?php echo i18n('Title: (eg: Office Fax)'); ?>' isempty='true'/ > <input type='text' class='text-empty' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' value='<?php echo i18n('insert number'); ?>' isempty='true'/ ></div> <div><input type='text' class='text-empty' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' value='<?php echo i18n('Title: (eg: Office Fax)'); ?>' isempty='true'/ > <input type='text' class='text-empty' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' value='<?php echo i18n('insert number'); ?>' isempty='true'/ > <a href='#' onclick='deleteContactItem(this,\"fax\")'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png' border='0'/ ></a></div></td></tr> <tr><td width='68' align='center' valign='top' style='padding-top:8px;'><a href='#' onclick='addCell(\"otherloc-"+idx+"\")' title='<?php echo i18n('Add cell phone'); ?>'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-cell.png' border='0'/ ></a></td> <td valign='top' style='padding-top:8px;' id='otherloc-"+idx+"-contacts-cells'> <div><input type='text' class='text-empty' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' value='<?php echo i18n('title: (eg: Mario cell phone)'); ?>' isempty='true'/ > <input type='text' class='text-empty' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' value='<?php echo i18n('insert number'); ?>' isempty='true'/ ></div> <div><input type='text' class='text-empty' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' value='<?php echo i18n('title: (eg: Mario cell phone)'); ?>' isempty='true'/ > <input type='text' class='text-empty' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' value='<?php echo i18n('insert number'); ?>' isempty='true'/ > <a href='#' onclick='deleteContactItem(this,\"cell\")'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png' border='0'/ ></a></div> </td></tr> <tr><td width='68' align='center' valign='top' style='padding-top:8px;border-bottom:0px;'><a href='#' onclick='addEmail(\"otherloc-"+idx+"\")' title='<?php echo i18n('Add email'); ?>'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-email.png' border='0'/ ></a></td> <td valign='top' style='padding-top:8px;border-bottom:0px;' id='otherloc-"+idx+"-contacts-emails'> <div><input type='text' class='text-empty' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' value='<?php echo i18n('title: (eg: Administration)'); ?>' isempty='true'/ > <input type='text' class='text-empty' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' value='<?php echo i18n('enter email'); ?>' isempty='true'/ ></div> <div><input type='text' class='text-empty' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' value='<?php echo i18n('title: (eg: Administration)'); ?>' isempty='true'/ > <input type='text' class='text-empty' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' value='<?php echo i18n('enter email'); ?>' isempty='true'/ > <a href='#' onclick='deleteContactItem(this,\"email\")'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png' border='0'/ ></a></div> </td></tr></table> <div align='center' id='otherloc-"+idx+"-contacts-empty-message' style='padding:12px;'><b><?php echo i18n('no address provided'); ?></b></div> <div align='center' id='otherloc-"+idx+"-contacts-empty-buttons' style='padding:20px;'> <a href='#' onclick='addPhone(\"otherloc-"+idx+"\")' title='<?php echo i18n('Add phone'); ?>'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-phone.png' border='0'/ ></a>&nbsp; <a href='#' onclick='addFax(\"otherloc-"+idx+"\")' title='<?php echo i18n('Add fax'); ?>'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-fax.png' border='0'/ ></a>&nbsp; <a href='#' onclick='addCell(\"otherloc-"+idx+"\")' title='<?php echo i18n('Add cell phone'); ?>'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-cell.png' border='0'/ ></a>&nbsp; <a href='#' onclick='addEmail(\"otherloc-"+idx+"\")' title='<?php echo i18n('Add email'); ?>'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/btn-add-email.png' border='0'/ ></a> </div>";

 r.cells[1].className = "right-block"; r.cells[1].style.verticalAlign='top'; r.cells[1].style.width = 360;

 var r = document.getElementById('mastertable').insertRow(-1);
 r.insertCell(-1).innerHTML = "&nbsp;";
 r.cells[0].colSpan=3; r.cells[0].style.height=10;
 document.getElementById('otherloc_'+idx+'_name').focus();
}
//-------------------------------------------------------------------------------------------------------------------//

function deleteOL(idx)
{
 if(!confirm("<?php echo i18n('Are you sure you want to remove this location?'); ?>"))
  return;
 
 var r = document.getElementById('otherloc_'+idx+'_masterrow');
 r.parentNode.deleteRow(r.rowIndex+1);
 r.parentNode.deleteRow(r.rowIndex+1);
 r.parentNode.deleteRow(r.rowIndex);
}

function copyFromRegOff()
{
 document.getElementById('hq_address').value = document.getElementById('regoff_address').value;
 document.getElementById('hq_city').value = document.getElementById('regoff_city').value;
 document.getElementById('hq_zip').value = document.getElementById('regoff_zip').value;
 document.getElementById('hq_prov').value = document.getElementById('regoff_prov').value;
 document.getElementById('hq_country').value = document.getElementById('regoff_country').value;

 // get phones //
 var list = document.getElementById('regoff-contacts-phones').getElementsByTagName('DIV');
 var listr = document.getElementById('hq-contacts-phones').getElementsByTagName('DIV');
 while(listr.length)
  listr[0].parentNode.removeChild(listr[0]);
 for(var c=0; c < list.length; c++)
 {
  var inps = list[c].getElementsByTagName('INPUT');
  if((inps[0].getAttribute("isempty") == "true") || (inps[0].value == ""))
   continue;
  var div = document.createElement('DIV');
  div.innerHTML = "<input type='text' class='text' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)'/ > <input type='text' class='text' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' / > <a href='#' onclick='deleteContactItem(this,\"phone\")'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png' border='0'/ ></a>";
  document.getElementById('hq-contacts-phones').appendChild(div);
  div.getElementsByTagName('INPUT')[0].value = inps[0].value;
  div.getElementsByTagName('INPUT')[1].value = inps[1].value;
 }

 // get fax //
 var list = document.getElementById('regoff-contacts-fax').getElementsByTagName('DIV');
 var listr = document.getElementById('hq-contacts-fax').getElementsByTagName('DIV');
 while(listr.length)
  listr[0].parentNode.removeChild(listr[0]);
 for(var c=0; c < list.length; c++)
 {
  var inps = list[c].getElementsByTagName('INPUT');
  if((inps[0].getAttribute("isempty") == "true") || (inps[0].value == ""))
   continue;
  var div = document.createElement('DIV');
  div.innerHTML = "<input type='text' class='text' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)'/ > <input type='text' class='text' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' / > <a href='#' onclick='deleteContactItem(this,\"fax\")'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png' border='0'/ ></a>";
  document.getElementById('hq-contacts-fax').appendChild(div);
  div.getElementsByTagName('INPUT')[0].value = inps[0].value;
  div.getElementsByTagName('INPUT')[1].value = inps[1].value;
 }

 // get cells //
 var list = document.getElementById('regoff-contacts-cells').getElementsByTagName('DIV');
 var listr = document.getElementById('hq-contacts-cells').getElementsByTagName('DIV');
 while(listr.length)
  listr[0].parentNode.removeChild(listr[0]);
 for(var c=0; c < list.length; c++)
 {
  var inps = list[c].getElementsByTagName('INPUT');
  if((inps[0].getAttribute("isempty") == "true") || (inps[0].value == ""))
   continue;
  var div = document.createElement('DIV');
  div.innerHTML = "<input type='text' class='text' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)'/ > <input type='text' class='text' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' / > <a href='#' onclick='deleteContactItem(this,\"cell\")'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png' border='0'/ ></a>";
  document.getElementById('hq-contacts-cells').appendChild(div);
  div.getElementsByTagName('INPUT')[0].value = inps[0].value;
  div.getElementsByTagName('INPUT')[1].value = inps[1].value;
 }

 // get emails //
 var list = document.getElementById('regoff-contacts-emails').getElementsByTagName('DIV');
 var listr = document.getElementById('hq-contacts-emails').getElementsByTagName('DIV');
 while(listr.length)
  listr[0].parentNode.removeChild(listr[0]);
 for(var c=0; c < list.length; c++)
 {
  var inps = list[c].getElementsByTagName('INPUT');
  if((inps[0].getAttribute("isempty") == "true") || (inps[0].value == ""))
   continue;
  var div = document.createElement('DIV');
  div.innerHTML = "<input type='text' class='text' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)'/ > <input type='text' class='text' style='width:132px;' onfocus='inpFocus(this)' onblur='inpBlur(this)' / > <a href='#' onclick='deleteContactItem(this,\"email\")'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png' border='0'/ ></a>";
  document.getElementById('hq-contacts-emails').appendChild(div);
  div.getElementsByTagName('INPUT')[0].value = inps[0].value;
  div.getElementsByTagName('INPUT')[1].value = inps[1].value;
 }

}

function formSubmit(close)
{
 /* Registered Office */
 var regoff_address = document.getElementById('regoff_address').value;
 var regoff_city = document.getElementById('regoff_city').value;
 var regoff_zip = document.getElementById('regoff_zip').value;
 var regoff_prov = document.getElementById('regoff_prov').value;
 var regoff_country = document.getElementById('regoff_country').value;
 
 var cmd = "companyprofile edit-contacts -regoff-addr `"+regoff_address+"` -regoff-city `"+regoff_city+"` -regoff-zip `"+regoff_zip+"` -regoff-prov `"+regoff_prov+"` -regoff-cc `"+regoff_country+"`";

 // get phones //
 var list = document.getElementById('regoff-contacts-phones').getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
 {
  var inps = list[c].getElementsByTagName('INPUT');
  if((inps[0].getAttribute("isempty") == "true") || (inps[0].value == ""))
   continue;
  cmd+= " -regoff-addphone `"+inps[0].value+"` `"+inps[1].value+"`";
 }

 // get fax //
 var list = document.getElementById('regoff-contacts-fax').getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
 {
  var inps = list[c].getElementsByTagName('INPUT');
  if((inps[0].getAttribute("isempty") == "true") || (inps[0].value == ""))
   continue;
  cmd+= " -regoff-addfax `"+inps[0].value+"` `"+inps[1].value+"`";
 }

 // get cells //
 var list = document.getElementById('regoff-contacts-cells').getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
 {
  var inps = list[c].getElementsByTagName('INPUT');
  if((inps[0].getAttribute("isempty") == "true") || (inps[0].value == ""))
   continue;
  cmd+= " -regoff-addcell `"+inps[0].value+"` `"+inps[1].value+"`";
 }

 // get emails //
 var list = document.getElementById('regoff-contacts-emails').getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
 {
  var inps = list[c].getElementsByTagName('INPUT');
  if((inps[0].getAttribute("isempty") == "true") || (inps[0].value == ""))
   continue;
  cmd+= " -regoff-addemail `"+inps[0].value+"` `"+inps[1].value+"`";
 }


 /* Headquarters */
 var hq_address = document.getElementById('hq_address').value;
 var hq_city = document.getElementById('hq_city').value;
 var hq_zip = document.getElementById('hq_zip').value;
 var hq_prov = document.getElementById('hq_prov').value;
 var hq_country = document.getElementById('hq_country').value;
 
 cmd+= "-hq-addr `"+hq_address+"` -hq-city `"+hq_city+"` -hq-zip `"+hq_zip+"` -hq-prov `"+hq_prov+"` -hq-cc `"+hq_country+"`";

 // get phones //
 var list = document.getElementById('hq-contacts-phones').getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
 {
  var inps = list[c].getElementsByTagName('INPUT');
  if((inps[0].getAttribute("isempty") == "true") || (inps[0].value == ""))
   continue;
  cmd+= " -hq-addphone `"+inps[0].value+"` `"+inps[1].value+"`";
 }

 // get fax //
 var list = document.getElementById('hq-contacts-fax').getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
 {
  var inps = list[c].getElementsByTagName('INPUT');
  if((inps[0].getAttribute("isempty") == "true") || (inps[0].value == ""))
   continue;
  cmd+= " -hq-addfax `"+inps[0].value+"` `"+inps[1].value+"`";
 }

 // get cells //
 var list = document.getElementById('hq-contacts-cells').getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
 {
  var inps = list[c].getElementsByTagName('INPUT');
  if((inps[0].getAttribute("isempty") == "true") || (inps[0].value == ""))
   continue;
  cmd+= " -hq-addcell `"+inps[0].value+"` `"+inps[1].value+"`";
 }

 // get emails //
 var list = document.getElementById('hq-contacts-emails').getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
 {
  var inps = list[c].getElementsByTagName('INPUT');
  if((inps[0].getAttribute("isempty") == "true") || (inps[0].value == ""))
   continue;
  cmd+= " -hq-addemail `"+inps[0].value+"` `"+inps[1].value+"`";
 }


 /* OTHER LOCATIONS */
 
 cmd+= " && companyprofile edit-other-locations";

 var ioolr = document.getElementById('init-of-other-loc');
 var tb = document.getElementById('mastertable');
 if(tb.rows[ioolr.rowIndex+1])
 {
  var rowidx = ioolr.rowIndex+1;

  while(tb.rows[rowidx])
  {
   var r = tb.rows[rowidx];

   if(r.id.substr(0,9) != "otherloc_")
	break;
   var tmp = r.id.substr(9);
   var idx = tmp.replace("_masterrow","");
   var ol_name = document.getElementById('otherloc_'+idx+'_name').value;
   var ol_addr = document.getElementById('otherloc_'+idx+'_address').value;
   var ol_city = document.getElementById('otherloc_'+idx+'_city').value;
   var ol_zip = document.getElementById('otherloc_'+idx+'_zip').value;
   var ol_prov = document.getElementById('otherloc_'+idx+'_prov').value;
   var ol_cc = document.getElementById('otherloc_'+idx+'_country').value;
   cmd+= " -name `"+ol_name+"` -addr `"+ol_addr+"` -city `"+ol_city+"` -zip `"+ol_zip+"` -prov `"+ol_prov+"` -cc `"+ol_cc+"`";

   // get phones //
   var list = document.getElementById('otherloc-'+idx+'-contacts-phones').getElementsByTagName('DIV');
   for(var c=0; c < list.length; c++)
   {
    var inps = list[c].getElementsByTagName('INPUT');
    if((inps[0].getAttribute("isempty") == "true") || (inps[0].value == ""))
     continue;
    cmd+= " -addphone `"+inps[0].value+"` `"+inps[1].value+"`";
   }

   // get fax //
   var list = document.getElementById('otherloc-'+idx+'-contacts-fax').getElementsByTagName('DIV');
   for(var c=0; c < list.length; c++)
   {
    var inps = list[c].getElementsByTagName('INPUT');
    if((inps[0].getAttribute("isempty") == "true") || (inps[0].value == ""))
     continue;
    cmd+= " -addfax `"+inps[0].value+"` `"+inps[1].value+"`";
   }

   // get cells //
   var list = document.getElementById('otherloc-'+idx+'-contacts-cells').getElementsByTagName('DIV');
   for(var c=0; c < list.length; c++)
   {
    var inps = list[c].getElementsByTagName('INPUT');
    if((inps[0].getAttribute("isempty") == "true") || (inps[0].value == ""))
     continue;
    cmd+= " -addcell `"+inps[0].value+"` `"+inps[1].value+"`";
   }

   // get emails //
   var list = document.getElementById('otherloc-'+idx+'-contacts-emails').getElementsByTagName('DIV');
   for(var c=0; c < list.length; c++)
   {
    var inps = list[c].getElementsByTagName('INPUT');
    if((inps[0].getAttribute("isempty") == "true") || (inps[0].value == ""))
     continue;
    cmd+= " -addemail `"+inps[0].value+"` `"+inps[1].value+"`";
   }
   rowidx+= 3;
  }
 }


 var sh = new GShell();
 sh.OnFinish = function(){
	 if(!close)
	  return alert("<?php echo i18n('Saved!'); ?>");
	 else
	  gframe_close();
	}
 sh.sendCommand(cmd);
}
</script>

