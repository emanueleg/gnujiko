<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-02-2012
 #PACKAGE: companyprofile-config
 #DESCRIPTION: 
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_COMPANY_PROFILE;

$_BANKS = $_COMPANY_PROFILE['banks'];

?>
<style type='text/css'>
hr {
	background: #dadada;
	height: 1px;
	border: 0px;
}
</style>

<table class='section' width='100%' cellspacing='0' cellpadding='0' border='0' id='mastertable'>
<tr><td colspan='3' valign='top' style="border-bottom:1px solid #dadada;">
	 <a href='#' onclick="addNewBank()" style='font-size:12px;color:#3364c3;font-weight:bold;text-decoration:none;float:left;white-space:nowrap;'> 
	 <img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/add.png" border="0" style="text-align:left;vertical-align:top;float:left;"/><?php echo i18n('add new<br/>bank'); ?></a></td></tr>

<tr><td colspan='3' height='10'>&nbsp;</td></tr>

<?php
for($c=0; $c < count($_BANKS); $c++)
{
 $iban = $_BANKS[$c]['iban'];
 ?>
<tr id="bank_<?php echo $c; ?>_masterrow"><td class='icon' rowspan='2' valign='top'>
		<img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/bank.png"/><br/>
		<span style="font-size:100px;font-family:Arial;color:#eeeeee;"><?php echo ($c+1); ?></span></td>
	<td valign='top' colspan='2'><span style="font-family: Trebuchet, Arial;font-size: 16px;color: #013397;"><?php echo i18n('Bank:'); ?> </span><input type='text' size='40' class='text' id="bank_<?php echo $c; ?>_name" value="<?php echo $_BANKS[$c]['name']; ?>"/> <a href='#' style='float:right;margin-bottom:6px;' onclick="deleteBank(<?php echo $c; ?>)"><img src="<?php echo $_ABSOLUTE_URL; ?>/share/widgets/config-companyprofile/img/btn_del.png" border='0'/></a>
	<br/><span style="font-family: Trebuchet, Arial;font-size: 14px;color: #000000;"><?php echo i18n('Holder:'); ?> </span><input type='text' size='40' class='text' id="bank_<?php echo $c; ?>_holder" value="<?php echo $_BANKS[$c]['holder']; ?>"/>
	</td></tr>

<tr><td valign='top' class='left-block'>
	 <div class='orangebar'><?php echo i18n('ACCOUNT DETAILS'); ?></div>
	 <?php echo i18n('ABI:'); ?> <input type='text' class='text' size='5' maxlength='5' id="bank_<?php echo $c; ?>_abi" value="<?php echo $_BANKS[$c]['abi']; ?>"/>
	 <?php echo i18n('CAB:'); ?> <input type='text' class='text' size='5' maxlength='5' id="bank_<?php echo $c; ?>_cab" value="<?php echo $_BANKS[$c]['cab']; ?>"/>
	 <?php echo i18n('C/A:'); ?> <input type='text' class='text' size='12' maxlength='12' id="bank_<?php echo $c; ?>_cc" value="<?php echo $_BANKS[$c]['cc']; ?>"/><br/>
	 <?php echo i18n('IBAN:'); ?> <input type='text' class='text' style='width:40px;' maxlength='4' id="bank_<?php echo $c; ?>_iban_1" value="<?php echo substr($iban,0,4); ?>"/>
		   <input type='text' class='text' style='width:40px;' maxlength='4' id="bank_<?php echo $c; ?>_iban_2" value="<?php echo substr($iban,4,4); ?>"/>
		   <input type='text' class='text' style='width:40px;' maxlength='4' id="bank_<?php echo $c; ?>_iban_3" value="<?php echo substr($iban,8,4); ?>"/>
		   <input type='text' class='text' style='width:40px;' maxlength='4' id="bank_<?php echo $c; ?>_iban_4" value="<?php echo substr($iban,12,4); ?>"/>
		   <input type='text' class='text' style='width:40px;' maxlength='4' id="bank_<?php echo $c; ?>_iban_5" value="<?php echo substr($iban,16,4); ?>"/>
		   <input type='text' class='text' style='width:40px;' maxlength='4' id="bank_<?php echo $c; ?>_iban_6" value="<?php echo substr($iban,20,4); ?>"/>
		   <input type='text' class='text' style='width:30px;' maxlength='3' id="bank_<?php echo $c; ?>_iban_7" value="<?php echo substr($iban,24); ?>"/><br/>
	 <hr/>
	 <br/>
     <span style='font-family:Arial;font-size:14px;color:#666666;'><i><?php echo i18n('Start balance:'); ?></i></span> 
	 <input type='text' class='text' size='10' id="bank_<?php echo $c; ?>_startbalance" value="<?php echo $_BANKS[$c]['start_balance']; ?>"/><br/>
     <span style='font-family:Arial;font-size:14px;color:#666666;'><i><?php echo i18n('Current balance:'); ?></i></span> 
	 <input type='text' class='text' size='10' id="bank_<?php echo $c; ?>_currentbalance" value="<?php echo $_BANKS[$c]['current_balance']; ?>"/>
 
	</td>
	<td width='280' valign='top' class='right-block'>
	 <div class='orangebar' style='margin-right:0px;'><?php echo i18n('BANK CONTACTS'); ?></div>
	 &nbsp;<?php echo i18n('Address:'); ?> <input type='text' class='text' size='20' id="bank_<?php echo $c; ?>_address" value="<?php echo $_BANKS[$c]['address']; ?>"/><br/>
	 &nbsp;<?php echo i18n('City:'); ?> <input type='text' class='text' size='20' id="bank_<?php echo $c; ?>_city" value="<?php echo $_BANKS[$c]['city']; ?>"/><br/>
	 &nbsp;<?php echo i18n('Zip:'); ?> <input type='text' class='text' size='5' maxlength='5' id="bank_<?php echo $c; ?>_zip" value="<?php echo $_BANKS[$c]['zip']; ?>"/> &nbsp; Prov.: <input type='text' class='text' size='2' maxlength='2' id="bank_<?php echo $c; ?>_prov" value="<?php echo $_BANKS[$c]['prov']; ?>"/><br/>
	 &nbsp;<?php echo i18n('Country:'); ?> <input type='text' class='text' size='2' maxlength='2' id="bank_<?php echo $c; ?>_country" value="<?php echo $_BANKS[$c]['country']; ?>"/><br/>
	 <br/>
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
var BANK_NUM = <?php echo count($_BANKS); ?>;

function formSubmit(close)
{
 var cmd = "companyprofile edit-banks";

 var tb = document.getElementById('mastertable');
 var rowidx = 2;
 while(tb.rows[rowidx])
 {
  var r = tb.rows[rowidx];

  if(r.id.substr(0,5) != "bank_")
   break;
  var tmp = r.id.substr(5);
  var idx = tmp.replace("_masterrow","");
  var _name = document.getElementById('bank_'+idx+'_name').value;
  var _holder = document.getElementById('bank_'+idx+'_holder').value;
  cmd+= " -name `"+_name+"` -holder `"+_holder+"`";

  /* Acount details */
  var abi = document.getElementById('bank_'+idx+'_abi').value;
  var cab = document.getElementById('bank_'+idx+'_cab').value;
  var cc = document.getElementById('bank_'+idx+'_cc').value;

  var iban = document.getElementById('bank_'+idx+'_iban_1').value;
  iban+= document.getElementById('bank_'+idx+'_iban_2').value;
  iban+= document.getElementById('bank_'+idx+'_iban_3').value;
  iban+= document.getElementById('bank_'+idx+'_iban_4').value;
  iban+= document.getElementById('bank_'+idx+'_iban_5').value;
  iban+= document.getElementById('bank_'+idx+'_iban_6').value;
  iban+= document.getElementById('bank_'+idx+'_iban_7').value;

  var startBalance = document.getElementById('bank_'+idx+'_startbalance').value;
  var currentBalance = document.getElementById('bank_'+idx+'_currentbalance').value;
  cmd+= " -abi `"+abi+"` -cab `"+cab+"` -cc `"+cc+"` -iban `"+iban+"` -start-balance `"+startBalance+"` -current-balance `"+currentBalance+"`";

  /* Bank contacts */
  var _addr = document.getElementById('bank_'+idx+'_address').value;
  var _city = document.getElementById('bank_'+idx+'_city').value;
  var _zip = document.getElementById('bank_'+idx+'_zip').value;
  var _prov = document.getElementById('bank_'+idx+'_prov').value;
  var _cc = document.getElementById('bank_'+idx+'_country').value;
  cmd+= " -addr `"+_addr+"` -city `"+_city+"` -zip `"+_zip+"` -prov `"+_prov+"` -country `"+_cc+"`";
  rowidx+= 3;
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

function deleteBank(idx)
{
 if(!confirm("<?php echo i18n('Are you sure you want to remove this bank?'); ?>"))
  return;
 
 var r = document.getElementById('bank_'+idx+'_masterrow');
 r.parentNode.deleteRow(r.rowIndex+1);
 r.parentNode.deleteRow(r.rowIndex+1);
 r.parentNode.deleteRow(r.rowIndex);
}

function addNewBank()
{
 var idx = BANK_NUM;
 BANK_NUM++;

 var r = document.getElementById('mastertable').insertRow(-1);
 r.id = "bank_"+idx+"_masterrow";

 r.insertCell(-1).innerHTML = "<img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/bank.png'/ ><br/ > <span style='font-size:100px;font-family:Arial;color:#eeeeee;'>"+(idx+1)+"</span>";
 r.cells[0].className = "icon"; r.cells[0].rowSpan=2; r.cells[0].style.verticalAlign='top';

 r.insertCell(-1).innerHTML = "<span style='font-family: Trebuchet, Arial;font-size: 16px;color: #013397;'><?php echo i18n('Bank:'); ?> </span><input type='text' size='40' class='text' id='bank_"+idx+"_name'/ > <a href='#' style='float:right;margin-bottom:6px;' onclick='deleteBank("+idx+")'><img src='<?php echo $_ABSOLUTE_URL; ?>/share/widgets/config-companyprofile/img/btn_del.png' border='0'/ ></a> <br/ ><span style='font-family: Trebuchet, Arial;font-size: 14px;color: #000000;'><?php echo i18n('Holder:'); ?> </span><input type='text' size='40' class='text' id='bank_"+idx+"_holder' / >";
 r.cells[1].colSpan=2; r.cells[1].style.verticalAlign='top';


 var r = document.getElementById('mastertable').insertRow(-1);
 
 r.insertCell(-1).innerHTML = "<div class='orangebar'><?php echo i18n('ACCOUNT DETAILS'); ?></div> <?php echo i18n('ABI:'); ?> <input type='text' class='text' size='5' maxlength='5' id='bank_"+idx+"_abi'/ > <?php echo i18n('CAB:'); ?> <input type='text' class='text' size='5' maxlength='5' id='bank_"+idx+"_cab' / > <?php echo i18n('C/A:'); ?> <input type='text' class='text' size='12' maxlength='12' id='bank_"+idx+"_cc'/ ><br/ > <?php echo i18n('IBAN:'); ?> <input type='text' class='text' style='width:40px;' maxlength='4' id='bank_"+idx+"_iban_1'/ > <input type='text' class='text' style='width:40px;' maxlength='4' id='bank_"+idx+"_iban_2'/ > <input type='text' class='text' style='width:40px;' maxlength='4' id='bank_"+idx+"_iban_3'/ > <input type='text' class='text' style='width:40px;' maxlength='4' id='bank_"+idx+"_iban_4'/ > <input type='text' class='text' style='width:40px;' maxlength='4' id='bank_"+idx+"_iban_5'/ > <input type='text' class='text' style='width:40px;' maxlength='4' id='bank_"+idx+"_iban_6'/ > <input type='text' class='text' style='width:30px;' maxlength='3' id='bank_"+idx+"_iban_7'/ ><br/ > <hr/ > <br/ > <span style='font-family:Arial;font-size:14px;color:#666666;'><i><?php echo i18n('Start balance:'); ?></i></span> <input type='text' class='text' size='10' id='bank_"+idx+"_startbalance'/ ><br/ > <span style='font-family:Arial;font-size:14px;color:#666666;'><i><?php echo i18n('Current balance:'); ?></i></span> <input type='text' class='text' size='10' id='bank_"+idx+"_currentbalance'/ >";
 r.cells[0].className = "left-block"; r.cells[0].style.verticalAlign='top';

 r.insertCell(-1).innerHTML = "<div class='orangebar' style='margin-right:0px;'><?php echo i18n('BANK CONTACTS'); ?></div>&nbsp;<?php echo i18n('Address:'); ?> <input type='text' class='text' size='20' id='bank_"+idx+"_address'/ ><br/ >&nbsp;<?php echo i18n('City:'); ?> <input type='text' class='text' size='20' id='bank_"+idx+"_city'/ ><br/ >&nbsp;<?php echo i18n('Zip:'); ?> <input type='text' class='text' size='5' maxlength='5' id='bank_"+idx+"_zip'/ > &nbsp; Prov.: <input type='text' class='text' size='2' maxlength='2' id='bank_"+idx+"_prov'/ ><br/ > &nbsp;<?php echo i18n('Country:'); ?> <input type='text' class='text' size='2' maxlength='2' id='bank_"+idx+"_country'/ ><br/ ><br/ >";
 r.cells[1].className = "right-block"; r.cells[1].style.verticalAlign='top'; r.cells[1].style.width = 280;

 var r = document.getElementById('mastertable').insertRow(-1);
 r.insertCell(-1).innerHTML = "&nbsp;";
 r.cells[0].colSpan=3; r.cells[0].style.height=10;
 document.getElementById('bank_'+idx+'_name').focus();
}

</script>
<?php

