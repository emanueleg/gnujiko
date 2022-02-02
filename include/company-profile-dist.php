<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 10-04-2013
 #PACKAGE: companyprofile-config
 #DESCRIPTION: This is the company profile configuration file.
 #VERSION: 2.1beta
 #CHANGELOG: 10-04-2013 : Predisposto per le colonne extra.
 #TODO:
 
*/

global $_COMPANY_PROFILE;

$_COMPANY_PROFILE = array();
/* GENERALITY */
$_COMPANY_PROFILE['name'] = "";
$_COMPANY_PROFILE['legal_representative'] = "";
$_COMPANY_PROFILE['taxcode'] = "";
$_COMPANY_PROFILE['vatnumber'] = "";
$_COMPANY_PROFILE['rea'] = "";
$_COMPANY_PROFILE['companycode'] = "";
$_COMPANY_PROFILE['website'] = "";
$_COMPANY_PROFILE['logo'] = "";
/* ADDRESSES */
$_COMPANY_PROFILE['addresses']['registered_office']['address'] = "";
$_COMPANY_PROFILE['addresses']['registered_office']['city'] = "";
$_COMPANY_PROFILE['addresses']['registered_office']['zip'] = "";
$_COMPANY_PROFILE['addresses']['registered_office']['prov'] = "";
$_COMPANY_PROFILE['addresses']['registered_office']['country'] = "";
$_COMPANY_PROFILE['addresses']['registered_office']['phones'] = array (
);
$_COMPANY_PROFILE['addresses']['registered_office']['fax'] = array (
);
$_COMPANY_PROFILE['addresses']['registered_office']['cells'] = array (
);
$_COMPANY_PROFILE['addresses']['registered_office']['emails'] = array (
);
$_COMPANY_PROFILE['addresses']['headquarters']['address'] = "";
$_COMPANY_PROFILE['addresses']['headquarters']['city'] = "";
$_COMPANY_PROFILE['addresses']['headquarters']['zip'] = "";
$_COMPANY_PROFILE['addresses']['headquarters']['prov'] = "";
$_COMPANY_PROFILE['addresses']['headquarters']['country'] = "";
$_COMPANY_PROFILE['addresses']['headquarters']['phones'] = array (
);
$_COMPANY_PROFILE['addresses']['headquarters']['fax'] = array (
);
$_COMPANY_PROFILE['addresses']['headquarters']['cells'] = array (
);
$_COMPANY_PROFILE['addresses']['headquarters']['emails'] = array (
);
$_COMPANY_PROFILE['addresses']['other'] = array (
);
$_COMPANY_PROFILE['banks'] = array (
);
$_COMPANY_PROFILE['accounting'] = array (
  'type' => 'simplified',
  'vat_payment_freq' => '1',
  'ir_vat_quarterly' => '1,0',
  'decimals_pricing' => '2',
  'freq_vat_used' => '1',
  'perc_tax_payment' => '88,0',
  'amount_stamp_receipt' => '1,81',
  'rate_stamp_routes' => '1,20',
  'rounding_stamps' => '3',
  'riba_costs_tobecharged' => '',
);
$_COMPANY_PROFILE['extracolumns'] = array ();

