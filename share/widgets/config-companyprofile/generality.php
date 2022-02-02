<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2015 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-02-2015
 #PACKAGE: companyprofile-config
 #DESCRIPTION: 
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_COMPANY_PROFILE;

include_once($_BASE_PATH."var/objects/guploader/index.php");

?>
<table class='form' width='100%' border='0' cellspacing='0' cellpadding='12'>
<tr><td><b><?php echo i18n('Company name:'); ?></b></td>
	<td><input type='text' size='40' id='companyname' value="<?php echo $_COMPANY_PROFILE['name']; ?>"/></td></tr>

<tr><td><b><?php echo i18n('Legal representative:'); ?></b></td>
	<td><input type='text' size='30' id='legal_representative' value="<?php echo $_COMPANY_PROFILE['legal_representative']; ?>"/></td></tr>

<tr><td><b><?php echo i18n('Tax code:'); ?></b></td>
	<td>
	 <input type='text' size='16' id='taxcode' maxlength='28' style='font-size:monospace;text-transform:uppercase;' value="<?php echo $_COMPANY_PROFILE['taxcode']; ?>"/>
	</td></tr>

<tr><td><b><?php echo i18n('VAT number:'); ?></b></td>
	<td>
	 <input type='text' size='11' id='vatnumber' maxlength='28' style='font-size:monospace;' value="<?php echo $_COMPANY_PROFILE['vatnumber']; ?>"/>
	</td></tr>

<tr><td><b><?php echo i18n('R.E.A.:'); ?></b></td>
	<td><input type='text' size='16' id='rea' maxlength='16' style='font-size:monospace;' value="<?php echo $_COMPANY_PROFILE['rea']; ?>"/></td></tr>

<tr><td><b><?php echo i18n('Company code (ATECOFIN):'); ?></b></td>
	<td><input type='text' size='16' id='companycode' maxlength='16' style='font-size:monospace;' value="<?php echo $_COMPANY_PROFILE['companycode']; ?>"/></td></tr>

<tr><td><b><?php echo i18n('Web site:'); ?></b></td>
	<td>http:// <input type='text' size='40' id='website' value="<?php echo $_COMPANY_PROFILE['website']; ?>"/></td></tr>

<tr><td valign='top'><b><?php echo i18n('Company logo:'); ?></b></td>
	<td><div style="width:132px;border:1px solid #3364c3;vertical-align:middle;text-align:center;float:left;">
	 <?php
	 if($_COMPANY_PROFILE['logo'] && file_exists($_BASE_PATH.ltrim($_COMPANY_PROFILE['logo'],"/")))
	  echo "<img src='".$_ABSOLUTE_URL.ltrim($_COMPANY_PROFILE['logo'],"/")."' width='128' id='logoimg'/>";
	 else
	  echo "<img src='".$_ABSOLUTE_URL."share/images/dot.gif' width='128' id='logoimg'/>";
	 ?>
	 <br/>
	</div>
	<div style="width:200px;float:left;margin-left:20px;" id='UPLDRSPACE'></div>
	<input type='hidden' id='logo' value="<?php echo $_COMPANY_PROFILE['logo']; ?>"/>
	</td></tr>
</table>
<div align='right' style='padding-top:5px;'>
<input type='button' value="<?php echo i18n('Abort'); ?>" onclick="gframe_close()"/> <input type='button' value="<?php echo i18n('Apply'); ?>" onclick='formSubmit()'/> <input type='button' value="<?php echo i18n('Save and close'); ?>" onclick="formSubmit(true)"/>
</div>

<script>
var GUpld = new GUploader(null,null,null,null,"tmp");
document.getElementById('UPLDRSPACE').appendChild(GUpld.O);
GUpld.OnUpload = function(fileInfo){
	 switch(fileInfo['extension'])
	 {
	  case 'jpg' : case 'bmp' : case 'png' : case 'gif' : break;
	  default : return alert("<?php echo i18n('Invalid file format. Only these extensions are allowed: jpg, bmp, png and gif.'); ?>"); break;
	 }
	 document.getElementById('logoimg').src = "<?php echo $_ABSOLUTE_URL; ?>"+fileInfo['fullname'];
	 document.getElementById('logo').value = "share/images/"+fileInfo['name']+"."+fileInfo['extension'];
	}

function formSubmit(close)
{
 var _name = document.getElementById('companyname').value;
 var legalRep = document.getElementById('legal_representative').value;
 var taxcode = document.getElementById('taxcode').value.toUpperCase();
 var vatnumber = document.getElementById('vatnumber').value;
 var rea = document.getElementById('rea').value;
 var companycode = document.getElementById('companycode').value;
 var website = document.getElementById('website').value;
 var logo = document.getElementById('logo').value;

 var sh = new GShell();
 sh.OnFinish = function(o,a){
	 if(close)
	  gframe_close();
	 else
	  alert("<?php echo i18n('The changes have been made with success!'); ?>");
	}
 if(GUpld.UploadedFile['name'])
  sh.sendCommand("mv `"+GUpld.UploadedFile['fullname']+"` `share/images/"+GUpld.UploadedFile['name']+"."+GUpld.UploadedFile['extension']+"`");
 sh.sendCommand("companyprofile edit-generality -name `"+_name+"` -legal-representative `"+legalRep+"` -taxcode `"+taxcode+"` -vatnumber `"+vatnumber+"` -rea `"+rea+"` -companycode `"+companycode+"` -website `"+website+"` -logo `"+logo+"`");
}
</script>

