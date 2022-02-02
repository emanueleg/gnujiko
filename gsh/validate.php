<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Request
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 04-02-2014
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Gnujiko validate
 #VERSION: 2.0beta
 #CHANGELOG: 
 #DEPENDS: 
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

function shell_validate($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'taxcode' : return validate_taxCode($args, $sessid, $shellid); break;
  case 'vatnumber' : return validate_vatNumber($args, $sessid, $shellid); break;

  default : return validate_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function validate_invalidArguments()
{
 $out = "Usage: validate ACTION PARAMS\n";
 $out.= "Available actions are:\n";
 $out.= " taxcode - Validate a taxcode\n";
 $out.= " vatnumber - Validate a vatnumber\n";

 return array('message'=>$out,'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function validate_taxCode($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;

 $out = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   default : $taxCode = $args[$c]; break;
  }

 if(!$taxCode)
  return array("message"=>"You must specify a taxcode.", "error"=>"INVALID_TAXCODE");

 include_once($_BASE_PATH."include/taxcodevalidator.php");

 $ret = validateTaxCode($taxCode);
 if(!$ret)
  return array("message"=>"Error: taxcode '".$taxCode."' is invalid!", "error"=>"INVALID_TAXCODE");
 $out = "done!\nThe taxcode '".$taxCode."' is ok!"; 

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function validate_vatNumber($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;

 $out = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   default : $vatNumber = $args[$c]; break;
  }

 if(!$vatNumber)
  return array("message"=>"You must specify a vatnumber.", "error"=>"INVALID_VATNUMBER");

 include_once($_BASE_PATH."include/vatnumbervalidator.php");

 $ret = validateVatNumber($vatNumber);
 if(!$ret)
  return array("message"=>"Error: vatnumber '".$vatNumber."' is invalid!", "error"=>"INVALID_VATNUMBER");
 $out = "done!\nThe vatnumber '".$vatNumber."' is ok!"; 

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//

