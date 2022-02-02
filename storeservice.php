<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-03-2017
 #PACKAGE: gstore
 #DESCRIPTION: External webservice functions
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SOFTWARE_NAME;

include('init/init1.php');
include('include/session.php');
include('include/gshell.php');

define("VALID-GNUJIKO-SHELLREQUEST",1);

switch($_REQUEST['action'])
{
 case 'inventory' : return storeservice_inventory(); break;
}

//-------------------------------------------------------------------------------------------------------------------//
function storeservice_inventory()
{
 $out = "<xml encoding=\"utf-8\">";

 $_ARCHIVES = array();

 $db = new AlpaDatabase();
 // Get archives
 $db->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE archive_type='gmart' AND trash='0'");
 while($db->Read())
 {
  $_ARCHIVES[] = $db->record['tb_prefix'];
 }

 $q = "";
 for($c=0; $c < count($_ARCHIVES); $c++)
 {
  $q.= " UNION SELECT p.code_str,p.name,p.storeqty,s.sku FROM dynarc_".$_ARCHIVES[$c]."_items AS p";
  $q.= " LEFT JOIN product_sku AS s ON s.ref_ap='".$_ARCHIVES[$c]."' AND s.ref_id=p.id AND s.variant_id=0";
  $q.= " WHERE p.trash=0 AND p.storeqty>0";
 }

 $_QRY = "SELECT * FROM (".ltrim($q, " UNION ").") AS list ORDER BY code_str ASC";
 $db->RunQuery($_QRY);
 if($db->Error)
 {
  echo "MYSQL ERROR: ".$db->Error;
  exit();
 }
 while($db->Read())
 {
  $out.= "<item sku=\"".$db->record['sku']."\" code=\"".$db->record['code_str']."\" name=\"".xml_purify(trim($db->record['name']))."\" qty=\"".$db->record['storeqty']."\"/>";
 }
 $db->Close();

 $out.= "</xml>";

 header('Content-Type: application/xml; charset:UTF-8');
 echo $out;
}
//-------------------------------------------------------------------------------------------------------------------//

