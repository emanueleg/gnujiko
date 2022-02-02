<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-12-2012
 #PACKAGE: companyprofile-config
 #DESCRIPTION: Company Profile Configuration Panel
 #VERSION: 2.2beta
 #CHANGELOG: 18-12-2012 : Cash resources included.
			 21-07-2012 : Chart of accounts included.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/company-profile.php");
include_once($_BASE_PATH."include/i18n.php");
LoadLanguage("config-companyprofile");

//-------------------------------------------------------------------------------------------------------------------//
?>
<html><head><title><?php echo i18n('Company profile'); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/companyprofile.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";</script>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
</head><body>

<table width='800' height='700' class='widget' cellspacing='0' cellpadding='0' border='0'>
<tr><td class='nw' width='16'>&nbsp;</td>
	<td class='n'><?php echo i18n('Company profile configuration'); ?></td>
	<td class='ne' width='16'><a href='#' onclick='gframe_close()'><b>X</b></a></td></tr>

<tr><td colspan='3' class='contents' valign='top'>
	 <div class='tabs'>
	  <ul class='tabs'>
		<?php
		$page = $_REQUEST['show'] ? $_REQUEST['show'] : "generality";
		$arr = array('generality'=>i18n('Generality'), 'addresses'=>i18n('Addresses'), 'accounting'=>i18n('Accounting'), 'banks'=>i18n('Banks'), 'vatrates'=>i18n('VAT rates'),'pricelists'=>i18n('Pricelists'));

		if(file_exists($_BASE_PATH."share/widgets/config-companyprofile/stores.php"))
		 $arr['stores']=i18n('Stores');

		if(file_exists($_BASE_PATH."share/widgets/config-companyprofile/cashresources.php"))
		 $arr['cashresources']=i18n('Resources');

		/*if(file_exists($_BASE_PATH."share/widgets/config-companyprofile/chartofaccounts.php"))
		 $arr['chartofaccounts']=i18n('Chart of accounts');*/

		while(list($k,$v) = each($arr))
		{
		 echo "<li".($page == $k ? " class='active'>" : ">")."<a href='?sessid=".$_REQUEST['sessid']."&shellid=".$_REQUEST['shellid']."&show=".$k."'>".$v."</a></li>";
		}
		?>
	  </ul>
	 </div>
	 <div class='contents'>
	  <?php
	  switch($page)
	  {
	   case 'addresses' : include($_BASE_PATH."share/widgets/config-companyprofile/addresses.php"); break;
	   case 'accounting' : include($_BASE_PATH."share/widgets/config-companyprofile/accounting.php"); break;
	   case 'banks' : include($_BASE_PATH."share/widgets/config-companyprofile/banks.php"); break;
	   case 'vatrates' : include($_BASE_PATH."share/widgets/config-companyprofile/vatrates.php"); break;
	   case 'pricelists' : include($_BASE_PATH."share/widgets/config-companyprofile/pricelists.php"); break;
	   case 'chartofaccounts' : include($_BASE_PATH."share/widgets/config-companyprofile/chartofaccounts.php"); break;
	   case 'stores' : include($_BASE_PATH."share/widgets/config-companyprofile/stores.php"); break;
	   case 'cashresources' : include($_BASE_PATH."share/widgets/config-companyprofile/cashresources.php"); break;
	   default : include($_BASE_PATH."share/widgets/config-companyprofile/generality.php"); break;
	  }
	  ?>
	 </div>
	</td></tr>

<tr><td class='sw'>&nbsp;</td>
	<td class='s'>&nbsp;</td>
	<td class='se'>&nbsp;</td></tr>
</table>

</body></html>
<?php

