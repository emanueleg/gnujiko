<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 02-10-2014
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Lista dei documenti commerciali
 #VERSION: 2.2beta
 #CHANGELOG: 02-10-2014 : Integrato con Fatture Soci
			 23-05-2014 : Aggiunto DDT in entrata
			 08-07-2013 - Aggiunte le ricevute fiscali.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES, $_DECIMALS;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/company-profile.php");

$_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'];

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Commercial Documents List</title>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>"; var USER_HOME = "<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR']; ?>/";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gmutable/index.php");
?>
</head><body>
<?php
switch($_REQUEST['doctype'])
{
 case 'preemptives' : include($_BASE_PATH."share/widgets/commercialdocs/list-preemptives.php"); break;
 case 'orders' : include($_BASE_PATH."share/widgets/commercialdocs/list-orders.php"); break;
 case 'ddt' : include($_BASE_PATH."share/widgets/commercialdocs/list-ddt.php"); break;
 case 'ddtin' : include($_BASE_PATH."share/widgets/commercialdocs/list-ddtin.php"); break;
 case 'invoices' : include($_BASE_PATH."share/widgets/commercialdocs/list-invoices.php"); break;
 case 'vendororders' : include($_BASE_PATH."share/widgets/commercialdocs/list-vendororders.php"); break;
 case 'purchaseinvoices' : include($_BASE_PATH."share/widgets/commercialdocs/list-purchaseinvoices.php"); break;
 case 'agentinvoices' : include($_BASE_PATH."share/widgets/commercialdocs/list-agentinvoices.php"); break;
 case 'memberinvoices' : include($_BASE_PATH."share/widgets/commercialdocs/list-memberinvoices.php"); break;
 case 'intervreports' : include($_BASE_PATH."share/widgets/commercialdocs/list-intervreports.php"); break;
 case 'creditsnote' : include($_BASE_PATH."share/widgets/commercialdocs/list-creditsnote.php"); break;
 case 'debitsnote' : include($_BASE_PATH."share/widgets/commercialdocs/list-debitsnote.php"); break;
 case 'paymentnotice' : include($_BASE_PATH."share/widgets/commercialdocs/list-paymentnotice.php"); break;
 case 'receipts' : include($_BASE_PATH."share/widgets/commercialdocs/list-receipts.php"); break;
}
?>
</body></html>
<?php

