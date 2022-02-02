<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 14-11-2012
 #PACKAGE: companyprofile-config
 #DESCRIPTION: This is the company profile configuration file.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_COMPANY_PROFILE;

$_COMPANY_PROFILE = array();
/* GENERALITY */
$_COMPANY_PROFILE['name'] = "La mia ditta";
$_COMPANY_PROFILE['legal_representative'] = "";
$_COMPANY_PROFILE['taxcode'] = "";
$_COMPANY_PROFILE['vatnumber'] = "12345678901";
$_COMPANY_PROFILE['rea'] = "";
$_COMPANY_PROFILE['companycode'] = "";
$_COMPANY_PROFILE['website'] = "";
$_COMPANY_PROFILE['logo'] = "share/images/linux_logo.png";
/* ADDRESSES */
$_COMPANY_PROFILE['addresses']['registered_office']['address'] = "Via Roma, 11";
$_COMPANY_PROFILE['addresses']['registered_office']['city'] = "Pordenone";
$_COMPANY_PROFILE['addresses']['registered_office']['zip'] = "33078";
$_COMPANY_PROFILE['addresses']['registered_office']['prov'] = "PN";
$_COMPANY_PROFILE['addresses']['registered_office']['country'] = "IT";
$_COMPANY_PROFILE['addresses']['registered_office']['phones'] = array (
  0 => 
  array (
    'name' => 'Ufficio',
    'number' => '0434 - 123456',
  ),
);
$_COMPANY_PROFILE['addresses']['registered_office']['fax'] = array (
  0 => 
  array (
    'name' => 'Ufficio',
    'number' => '0434 - 123456',
  ),
);
$_COMPANY_PROFILE['addresses']['registered_office']['cells'] = array (
);
$_COMPANY_PROFILE['addresses']['registered_office']['emails'] = array (
  0 => 
  array (
    'name' => 'Amministrazione',
    'email' => 'info@lamiaditta.it',
  ),
);
$_COMPANY_PROFILE['addresses']['headquarters']['address'] = "Via Roma, 11";
$_COMPANY_PROFILE['addresses']['headquarters']['city'] = "Pordenone";
$_COMPANY_PROFILE['addresses']['headquarters']['zip'] = "33078";
$_COMPANY_PROFILE['addresses']['headquarters']['prov'] = "PN";
$_COMPANY_PROFILE['addresses']['headquarters']['country'] = "IT";
$_COMPANY_PROFILE['addresses']['headquarters']['phones'] = array (
  0 => 
  array (
    'name' => 'Ufficio',
    'number' => '0434 - 123456',
  ),
);
$_COMPANY_PROFILE['addresses']['headquarters']['fax'] = array (
  0 => 
  array (
    'name' => 'Ufficio',
    'number' => '0434 - 123456',
  ),
);
$_COMPANY_PROFILE['addresses']['headquarters']['cells'] = array (
);
$_COMPANY_PROFILE['addresses']['headquarters']['emails'] = array (
  0 => 
  array (
    'name' => 'Amministrazione',
    'email' => 'info@lamiaditta.it',
  ),
);
$_COMPANY_PROFILE['addresses']['other'] = array (
);
$_COMPANY_PROFILE['banks'] = array (
);
$_COMPANY_PROFILE['accounting'] = array (
  'type' => 'simplified',
  'vat_payment_freq' => '1',
  'ir_vat_quarterly' => '',
  'decimals_pricing' => '2',
  'freq_vat_used' => '21',
  'invoice_type' => 'NORMAL',
  'contr_cassa_prev' => '0',
  'rit_enasarco' => '0',
  'rit_enasarco_percimp' => '0',
  'rit_acconto' => '0',
  'rit_acconto_percimp' => '0',
  'rit_acconto_rivinpsinc' => '0',
  'rivalsa_inps' => '0',
  'contr_cassa_prev_vatid' => '0',
);
$_COMPANY_PROFILE['extracolumns'] = array();

