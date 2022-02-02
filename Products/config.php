<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 
 #PACKAGE: 
 #DESCRIPTION: 
 #VERSION: 
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_SHELL_CMD_PATH, $_APPLICATION_CONFIG;

$_APPLICATION_CONFIG = array(
	"appname"=>"Prodotti",
	"basepath"=>"Products/",
	"mainmenu"=>array()
);

if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."transponder.php"))
{
 $_SERVICE_TAGS = "joomshopping,virtuemart,ebay,amazon";

 $_APPLICATION_CONFIG['transponder'] = array('service_tags'=>$_SERVICE_TAGS, 'servers'=>array());
 $ret = GShell("transponder server-list --service-tags '".$_SERVICE_TAGS."'");
 if(!$ret['error']) $_APPLICATION_CONFIG['transponder']['servers'] = $ret['outarr'];

 // Get basket items count
 if(!isset($_COOKIE['GMART_TRANSPONDER_BASKET_COUNT']))
 {
  $ret = GShell("transponder basket-list -at gmart --get-count || export -var GMART_TRANSPONDER_BASKET_COUNT -value *.count");
  if(!$ret['error'])
  {
   $_COOKIE['GMART_TRANSPONDER_BASKET_COUNT'] = $ret['redirected_outarr'][0]['count'];
  }

 }

}
