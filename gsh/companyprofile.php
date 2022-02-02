<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2015 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 25-03-2015
 #PACKAGE: companyprofile-config 
 #DESCRIPTION: Company profile manager
 #VERSION: 2.8beta
 #CHANGELOG: 25-03-2015 : Aggiornata funz. editExtraColumns (su valuta messo decimal al posto di float).
			 27-02-2015 : Aggiunto regime fiscale su edit-accounting.
			 20-01-2015 : Aggiunto bic-swift su edit bank.
			 29-01-2014 : Aggiunto codice SIA su conti correnti e funzione get-banks
			 04-10-2013 : Aggiunto -contr-cassa-prev-vatid su edit-accounting
			 05-07-2013 : Aggiunta riv.inps,rit.acconto,rit.enasarco,contr.cassa.prev, ecc..
			 11-04-2013 : Sistemato i permessi ai files ora impostabili dal file di configurazione.
			 10-04-2013 : Predisposto per le colonne extra.
 #TODO: Da fare funzione companyprofile info per leggere la configurazione anche da JavaScript.
 
*/

function shell_companyprofile($args, $sessid, $shellid=0)
{
 if(count($args) == 0)
  return companyprofile_invalidArguments();

 switch($args[0])
 {
  case 'edit-generality' : return companyprofile_editGenerality($args, $sessid, $shellid); break;
  case 'edit-contacts' : return companyprofile_editContacts($args, $sessid, $shellid); break;
  case 'edit-other-locations' : return companyprofile_editOtherLocations($args, $sessid, $shellid); break;
  case 'edit-banks' : return companyprofile_editBanks($args, $sessid, $shellid); break;
  case 'edit-accounting' : return companyprofile_editAccounting($args, $sessid, $shellid); break;
  case 'edit-extracolumns' : return companyprofile_editExtraColumns($args, $sessid, $shellid); break;

  case 'get-banks' : case 'bank-list' : return companyprofile_bankList($args, $sessid, $shellid); break;

  default : return companyprofile_invalidArguments(); break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function companyprofile_invalidArguments()
{
 return array("message"=>"Invalid arguments.", "error"=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function companyprofile_editGenerality($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_DEFAULT_FILE_PERMS;

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-legal-representative' : {$legalRepresentative=$args[$c+1]; $c++;} break;
   case '-taxcode' : {$taxCode=$args[$c+1]; $c++;} break;
   case '-vatnumber' : {$vatNumber=$args[$c+1]; $c++;} break;
   case '-rea' : {$rea=$args[$c+1]; $c++;} break;
   case '-companycode' : {$companyCode=$args[$c+1]; $c++;} break;
   case '-website' : {$webSite=$args[$c+1]; $c++;} break;
   case '-logo' : {$logo=$args[$c+1]; $c++;} break;
  }

 //--- update include/company-profile.php ---//
 $var = array(); $val = array();

 if(isset($name)){ $var[] = "_COMPANY_PROFILE['name']";	$val[] = $name;	}
 if(isset($legalRepresentative)){ $var[] = "_COMPANY_PROFILE['legal_representative']";	$val[] = $legalRepresentative;	}
 if(isset($taxCode)){ $var[] = "_COMPANY_PROFILE['taxcode']";	$val[] = $taxCode;	}
 if(isset($vatNumber)){ $var[] = "_COMPANY_PROFILE['vatnumber']";	$val[] = $vatNumber;	}
 if(isset($rea)){ $var[] = "_COMPANY_PROFILE['rea']";  $val[] = $rea; }
 if(isset($companyCode)){ $var[] = "_COMPANY_PROFILE['companycode']";	$val[] = $companyCode;	}
 if(isset($webSite)){ $var[] = "_COMPANY_PROFILE['website']";	$val[] = $webSite;	}
 if(isset($logo)){ $var[] = "_COMPANY_PROFILE['logo']";	$val[] = $logo;	}

 $ret = ReplaceConfValue($_BASE_PATH."include/company-profile.php",$var,$val,false,$_DEFAULT_FILE_PERMS);
 if($ret['error'])
  return $ret;

 $out = "Company profile has been updated."; 

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function companyprofile_editContacts($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_DEFAULT_FILE_PERMS;

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $outArr = array();

 $regoffPhones = array();
 $regoffFax = array();
 $regoffCells = array();
 $regoffEmails = array();

 $hqPhones = array();
 $hqFax = array();
 $hqCells = array();
 $hqEmails = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   /* Registered Office */
   case '-regoff-addr' : {$regoffAddress=$args[$c+1]; $c++;} break;
   case '-regoff-city' : {$regoffCity=$args[$c+1]; $c++;} break;
   case '-regoff-zip' : {$regoffZip=$args[$c+1]; $c++;} break;
   case '-regoff-prov' : {$regoffProv=$args[$c+1]; $c++;} break;
   case '-regoff-cc' : {$regoffCC=$args[$c+1]; $c++;} break;
   case '-regoff-addphone' : {$regoffPhones[] = array('name'=>$args[$c+1], 'number'=>$args[$c+2]); $c+=2;} break;
   case '-regoff-addfax' : {$regoffFax[] = array('name'=>$args[$c+1], 'number'=>$args[$c+2]); $c+=2;} break;
   case '-regoff-addcell' : {$regoffCells[] = array('name'=>$args[$c+1], 'number'=>$args[$c+2]); $c+=2;} break;
   case '-regoff-addemail' : {$regoffEmails[] = array('name'=>$args[$c+1], 'email'=>$args[$c+2]); $c+=2;} break;


   /* Headquarters */
   case '-hq-addr' : {$hqAddress=$args[$c+1]; $c++;} break;
   case '-hq-city' : {$hqCity=$args[$c+1]; $c++;} break;
   case '-hq-zip' : {$hqZip=$args[$c+1]; $c++;} break;
   case '-hq-prov' : {$hqProv=$args[$c+1]; $c++;} break;
   case '-hq-cc' : {$hqCC=$args[$c+1]; $c++;} break;
   case '-hq-addphone' : {$hqPhones[] = array('name'=>$args[$c+1], 'number'=>$args[$c+2]); $c+=2;} break;
   case '-hq-addfax' : {$hqFax[] = array('name'=>$args[$c+1], 'number'=>$args[$c+2]); $c+=2;} break;
   case '-hq-addcell' : {$hqCells[] = array('name'=>$args[$c+1], 'number'=>$args[$c+2]); $c+=2;} break;
   case '-hq-addemail' : {$hqEmails[] = array('name'=>$args[$c+1], 'email'=>$args[$c+2]); $c+=2;} break;
  }

 //--- update include/company-profile.php ---//
 $var = array(); $val = array();

 /* Registered Office */
 if(isset($regoffAddress)){ $var[] = "_COMPANY_PROFILE['addresses']['registered_office']['address']";	$val[] = $regoffAddress;	}
 if(isset($regoffCity)){ $var[] = "_COMPANY_PROFILE['addresses']['registered_office']['city']";	$val[] = $regoffCity;	}
 if(isset($regoffZip)){ $var[] = "_COMPANY_PROFILE['addresses']['registered_office']['zip']";	$val[] = $regoffZip;	}
 if(isset($regoffProv)){ $var[] = "_COMPANY_PROFILE['addresses']['registered_office']['prov']";	$val[] = $regoffProv;	}
 if(isset($regoffCC)){ $var[] = "_COMPANY_PROFILE['addresses']['registered_office']['country']";	$val[] = $regoffCC;	}

 $var[] = "_COMPANY_PROFILE['addresses']['registered_office']['phones']";
 $val[] = $regoffPhones;
 $var[] = "_COMPANY_PROFILE['addresses']['registered_office']['fax']";
 $val[] = $regoffFax;
 $var[] = "_COMPANY_PROFILE['addresses']['registered_office']['cells']";
 $val[] = $regoffCells;
 $var[] = "_COMPANY_PROFILE['addresses']['registered_office']['emails']";
 $val[] = $regoffEmails;

 /* Headquarters */
 if(isset($hqAddress)){ $var[] = "_COMPANY_PROFILE['addresses']['headquarters']['address']";	$val[] = $hqAddress;	}
 if(isset($hqCity)){ $var[] = "_COMPANY_PROFILE['addresses']['headquarters']['city']";	$val[] = $hqCity;	}
 if(isset($hqZip)){ $var[] = "_COMPANY_PROFILE['addresses']['headquarters']['zip']";	$val[] = $hqZip;	}
 if(isset($hqProv)){ $var[] = "_COMPANY_PROFILE['addresses']['headquarters']['prov']";	$val[] = $hqProv;	}
 if(isset($hqCC)){ $var[] = "_COMPANY_PROFILE['addresses']['headquarters']['country']";	$val[] = $hqCC;	}

 $var[] = "_COMPANY_PROFILE['addresses']['headquarters']['phones']";
 $val[] = $hqPhones;
 $var[] = "_COMPANY_PROFILE['addresses']['headquarters']['fax']";
 $val[] = $hqFax;
 $var[] = "_COMPANY_PROFILE['addresses']['headquarters']['cells']";
 $val[] = $hqCells;
 $var[] = "_COMPANY_PROFILE['addresses']['headquarters']['emails']";
 $val[] = $hqEmails;

 $ret = ReplaceConfValue($_BASE_PATH."include/company-profile.php",$var,$val,false,$_DEFAULT_FILE_PERMS);
 if($ret['error'])
  return $ret;

 $out = "Company profile has been updated."; 

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function companyprofile_editOtherLocations($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_DEFAULT_FILE_PERMS;

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $outArr = array();

 $OL = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   /* Registered Office */
   case '-name' : {$OL[] = array('name'=>$args[$c+1]); $c++;} break;
   case '-addr' : {$OL[count($OL)-1]['address']=$args[$c+1]; $c++;} break;
   case '-city' : {$OL[count($OL)-1]['city']=$args[$c+1]; $c++;} break;
   case '-zip' : {$OL[count($OL)-1]['zip']=$args[$c+1]; $c++;} break;
   case '-prov' : {$OL[count($OL)-1]['prov']=$args[$c+1]; $c++;} break;
   case '-cc' : {$OL[count($OL)-1]['country']=$args[$c+1]; $c++;} break;
   case '-addphone' : {$OL[count($OL)-1]['phones'][] = array('name'=>$args[$c+1], 'number'=>$args[$c+2]); $c+=2;} break;
   case '-addfax' : {$OL[count($OL)-1]['fax'][] = array('name'=>$args[$c+1], 'number'=>$args[$c+2]); $c+=2;} break;
   case '-addcell' : {$OL[count($OL)-1]['cells'][] = array('name'=>$args[$c+1], 'number'=>$args[$c+2]); $c+=2;} break;
   case '-addemail' : {$OL[count($OL)-1]['emails'][] = array('name'=>$args[$c+1], 'email'=>$args[$c+2]); $c+=2;} break;
  }

 //--- update include/company-profile.php ---//
 $var = array(); $val = array();

 $var[] = "_COMPANY_PROFILE['addresses']['other']";
 $val[] = $OL;

 $ret = ReplaceConfValue($_BASE_PATH."include/company-profile.php",$var,$val,false,$_DEFAULT_FILE_PERMS);
 if($ret['error'])
  return $ret;

 $out = "Company profile has been updated."; 

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function companyprofile_editBanks($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_DEFAULT_FILE_PERMS;

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $outArr = array();

 $_BANKS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   /* Account details */
   case '-name' : {$_BANKS[] = array('name'=>$args[$c+1]); $c++;} break;
   case '-holder' : {$_BANKS[count($_BANKS)-1]['holder']=$args[$c+1]; $c++;} break;
   case '-abi' : {$_BANKS[count($_BANKS)-1]['abi']=$args[$c+1]; $c++;} break;
   case '-cab' : {$_BANKS[count($_BANKS)-1]['cab']=$args[$c+1]; $c++;} break;
   case '-cc' : {$_BANKS[count($_BANKS)-1]['cc']=$args[$c+1]; $c++;} break;
   case '-iban' : {$_BANKS[count($_BANKS)-1]['iban']=$args[$c+1]; $c++;} break;
   case '-sia' : {$_BANKS[count($_BANKS)-1]['sia']=$args[$c+1]; $c++;} break;
   case '-bic' : case '-swift' : case '-bicswift' : {$_BANKS[count($_BANKS)-1]['bicswift']=$args[$c+1]; $c++;} break;
   case '-start-balance' : {$_BANKS[count($_BANKS)-1]['start_balance']=$args[$c+1]; $c++;} break;
   case '-current-balance' : {$_BANKS[count($_BANKS)-1]['current_balance']=$args[$c+1]; $c++;} break;

   /* Bank contacts */
   case '-addr' : {$_BANKS[count($_BANKS)-1]['address']=$args[$c+1]; $c++;} break;
   case '-city' : {$_BANKS[count($_BANKS)-1]['city']=$args[$c+1]; $c++;} break;
   case '-zip' : {$_BANKS[count($_BANKS)-1]['zip']=$args[$c+1]; $c++;} break;
   case '-prov' : {$_BANKS[count($_BANKS)-1]['prov']=$args[$c+1]; $c++;} break;
   case '-country' : {$_BANKS[count($_BANKS)-1]['country']=$args[$c+1]; $c++;} break;

  }

 //--- update include/company-profile.php ---//
 $var = array(); $val = array();

 $var[] = "_COMPANY_PROFILE['banks']";
 $val[] = $_BANKS;

 $ret = ReplaceConfValue($_BASE_PATH."include/company-profile.php",$var,$val,false,$_DEFAULT_FILE_PERMS);
 if($ret['error'])
  return $ret;

 $out = "Banks has been updated."; 

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function companyprofile_editAccounting($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_DEFAULT_FILE_PERMS, $_COMPANY_PROFILE;

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 include_once($_BASE_PATH."include/company-profile.php");

 $out = "";
 $outArr = $_COMPANY_PROFILE['accounting'];

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   /* Account details */
   case '-type' : {$outArr['type']=$args[$c+1]; $c++;} break;
   case '-vat-payment-freq' : {$outArr['vat_payment_freq']=$args[$c+1]; $c++;} break;
   case '-ir-vat-quarterly' : {$outArr['ir_vat_quarterly']=$args[$c+1]; $c++;} break;
   case '-decimals-pricing' : {$outArr['decimals_pricing']=$args[$c+1]; $c++;} break;
   case '-freq-vat-used' : {$outArr['freq_vat_used']=$args[$c+1]; $c++;} break;
   case '-tax-regime' : {$outArr['tax_regime']=$args[$c+1]; $c++;} break;
   case '-perc-tax-payment' : {$outArr['perc_tax_payment']=$args[$c+1]; $c++;} break;
   case '-amount-stamp-receipt' : {$outArr['amount_stamp_receipt']=$args[$c+1]; $c++;} break;
   case '-rate-stamp-routes' : {$outArr['rate_stamp_routes']=$args[$c+1]; $c++;} break;
   case '-rounding-stamps' : {$outArr['rounding_stamps']=$args[$c+1]; $c++;} break;
   case '-riba-costs-tobecharged' : {$outArr['riba_costs_tobecharged']=$args[$c+1]; $c++;} break;

   case '-invoice-type' : {$outArr['invoice_type']=$args[$c+1]; $c++;} break;
   case '-rivalsa-inps' : {$outArr['rivalsa_inps']=$args[$c+1]; $c++;} break;
   case '-contr-cassa-prev' : {$outArr['contr_cassa_prev']=$args[$c+1]; $c++;} break;
   case '-contr-cassa-prev-vatid' : {$outArr['contr_cassa_prev_vatid']=$args[$c+1]; $c++;} break;
   case '-ritenuta-enasarco' : case '-rit-enasarco' : case '-enasarco' : {$outArr['rit_enasarco']=$args[$c+1]; $c++;} break;
   case '-rit-enasarco-percimp' : {$outArr['rit_enasarco_percimp']=$args[$c+1]; $c++;} break;
   case '-ritenuta-acconto' : case '-rit-acconto' : {$outArr['rit_acconto']=$args[$c+1]; $c++;} break;
   case '-rit-acconto-percimp' : {$outArr['rit_acconto_percimp']=$args[$c+1]; $c++;} break;
   case '-rit-acconto-rivinpsinc' : {$outArr['rit_acconto_rivinpsinc']=$args[$c+1]; $c++;} break;
  }

 //--- update include/company-profile.php ---//
 $var = array(); $val = array();

 $var[] = "_COMPANY_PROFILE['accounting']";
 $val[] = $outArr;

 $ret = ReplaceConfValue($_BASE_PATH."include/company-profile.php",$var,$val,false,$_DEFAULT_FILE_PERMS);
 if($ret['error'])
  return $ret;

 $out = "Accounting has been updated."; 

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function companyprofile_editExtraColumns($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_DEFAULT_FILE_PERMS;

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $outArr = array();

 $_EXTRA_COLUMNS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   /* Account details */
   case '-name' : case '-title' : {$_EXTRA_COLUMNS[] = array('title'=>$args[$c+1]); $c++;} break;
   case '-tag' : {$_EXTRA_COLUMNS[count($_EXTRA_COLUMNS)-1]['tag']=$args[$c+1]; $c++;} break;
   case '-format' : {$_EXTRA_COLUMNS[count($_EXTRA_COLUMNS)-1]['format']=$args[$c+1]; $c++;} break;
   case '-after' : {$_EXTRA_COLUMNS[count($_EXTRA_COLUMNS)-1]['after']=$args[$c+1]; $c++;} break;
   case '-formula' : {$_EXTRA_COLUMNS[count($_EXTRA_COLUMNS)-1]['formula']=$args[$c+1]; $c++;} break;
  }

 //--- update include/company-profile.php ---//
 $var = array(); $val = array();

 $var[] = "_COMPANY_PROFILE['extracolumns']";
 $val[] = $_EXTRA_COLUMNS;

 $ret = ReplaceConfValue($_BASE_PATH."include/company-profile.php",$var,$val,false,$_DEFAULT_FILE_PERMS);
 if($ret['error'])
  return $ret;

 /* add fields to dynarc_commercialdocs_elements table */
 $db = new AlpaDatabase();
 $fields = $db->FieldsInfo("dynarc_commercialdocs_elements");
 for($c=0; $c < count($_EXTRA_COLUMNS); $c++)
 {
  $column = $_EXTRA_COLUMNS[$c];
  switch($column['format'])
  {
   case 'number' : $formatQry="INT(11) NOT NULL"; break;
   case 'currency' : $formatQry="DECIMAL (10,5) NOT NULL"; break;
   case 'text' : $formatQry="VARCHAR(255) NOT NULL"; break;
   case 'longtext' : $formatQry="TEXT NOT NULL"; break;
   default : $formatQry="VARCHAR(32) NOT NULL"; break;
  }

  if($fields[$column['tag']])
   $db->RunQuery("ALTER TABLE `dynarc_commercialdocs_elements` CHANGE `".$column['tag']."` `".$column['tag']."` ".$formatQry); 
  else
   $db->RunQuery("ALTER TABLE `dynarc_commercialdocs_elements` ADD `".$column['tag']."` ".$formatQry);
 }
 $db->Close();

 $out = "ExtraColumns has been updated."; 

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function companyprofile_bankList($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_COMPANY_PROFILE;
 include_once($_BASE_PATH."include/company-profile.php");

 $out = "";
 $outArr = $_COMPANY_PROFILE['banks'];

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--verbose' : case '-verbose' : $verbose=true; break;
  }

 if($verbose)
 {
  $out = "List of company banks:\n";
  for($c=0; $c < count($outArr); $c++)
  {
   $bank = $outArr[$c];
   $out.= ($c+1).". ".$bank['name']." (ABI: ".$bank['abi'].", CAB: ".$bank['cab'].", CC: ".$bank['cc'].")\n";
  }
 }
 $out.= count($_COMPANY_PROFILE['banks'])." banks found.";

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function ReplaceConfValue($strCfgFile,$strCfgVar,$strCfgVal,$backup=false,$mod=null)
{
 $strOldContent = array();
 $oldContents = file($strCfgFile);
 $str = "";

 while(list($c,$line) = each($oldContents))
 {
  $pos = strrpos($line, ";");
  if(($pos !== false) && ($pos > (strlen($line)-4)))
  {
   $strOldContent[] = $str.$line;
   $str = "";
  }
  else
   $str.= $line;
 }


 $strNewContent = "";
 while (list ($intLineNum, $strLine) = each ($strOldContent)) 
 {
  if(is_array($strCfgVar))
  {
   for($c=0; $c < count($strCfgVar); $c++)
   {
	if(strpos($strLine, "$".$strCfgVar[$c]) !== false)
	{
	 $strLineParts = explode("=",$strLine);
	 $strLineParts[0] = trim($strLineParts[0]);
	 if(is_array($strCfgVal[$c]))
	 {
	  
	  $strLineParts[1] = var_export($strCfgVal[$c],true);
	  $strLine = $strLineParts[0]." = ".$strLineParts[1].";\r\n";
	 }
	 else
	 {
	  $strLineParts[1] = "\"".$strCfgVal[$c]."\"";
	  $strLine = implode(" = ",$strLineParts).";\r\n";
	 }
	}
   }
  }
  else if(preg_match("/^\\$".$strCfgVar."( |.)*=/i",$strLine))	// show any line beginning with a $
  {
	if(strpos($strLine, "$".$strCfgVar) !== false)
	{
	 $strLineParts = explode("=",$strLine);
	 $strLineParts[0] = trim($strLineParts[0]);
	 if(is_array($strCfgVal))
	 {
	  $strLineParts[1] = var_export($strCfgVal,true);
	  $strLine = $strLineParts[0]." = ".$strLineParts[1].";\r\n";
	 }
	 else
	 {
	  $strLineParts[1] = "\"".$strCfgVal."\"";
	  $strLine = implode(" = ",$strLineParts).";\r\n";
	 }
	}
  }
  $strNewContent .= $strLine;
  if($backup)
   $fp = fopen($strCfgFile."_new", "w");
  else if(is_writable($strCfgFile))
   $fp = fopen($strCfgFile,"w");
  else
   return array('message'=>"Unable to open file $strCfgFile in write mode. Permission denied", 'error'=>"CFG_FILE_PERMISSION_DENIED");

  fputs($fp,$strNewContent);
  fclose($fp);
  if($mod)
  {
   if($backup)
    @chmod($strCfgFile."_new",$mod);
   else
    @chmod($strCfgFile,$mod);
  }
 }
 if($backup)
 {
  if(!rename($strCfgFile,$strCfgFile.".bak")) return array('message'=>"Error: Could not rename old file!",'error'=>"COULD_NOT_RENAME_OLD_FILE");
  if(!rename($strCfgFile."_new",$strCfgFile)) return array('message'=>"Backup failed!: Unable to copy file!",'error'=>"BACKUP_FAILED");
 }
 return array('message'=>"Configuration file has been updated.");
}
//----------------------------------------------------------------------------------------------------------------------//

