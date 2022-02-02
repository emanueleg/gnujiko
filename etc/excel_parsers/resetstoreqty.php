<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 06-09-2016
 #PACKAGE: gstore
 #DESCRIPTION: Excel parser for GStore - reset store qty.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

function gnujikoexcelparser_resetstoreqty_info()
{
 $info = array('name' => "Reset store qty");
 $keys = array(
	/* BASIC INFO */
	"code"=>"Codice"
	);

 $keydict = array(
	/* BASIC INFO */
	"code"=> 			array("code","codice","cod. art")
	);

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id,name FROM stores WHERE 1 ORDER BY name ASC");
 while($db->Read())
 {
  $keys["store_".$db->record['id']."_qty"] = "Qta a mag: ".$db->record['name'];
  $keydict["store_".$db->record['id']."_qty"] = array($db->record['name']);
 }
 $db->Close();

 return array('info'=>$info, 'keys'=>$keys, 'keydict'=>$keydict);
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikoexcelparser_resetstoreqty_import($_DATA, $sessid, $shellid, $_AT="gmart", $catId=0, $catTag="", $id=0)
{
 global $_BASE_PATH;
 include_once($_BASE_PATH."include/extendedfunc.php");

 $xml = "";

 for($c=0; $c < count($_DATA['items']); $c++)
 {
  $item = $_DATA['items'][$c];
  if(!$item['code']) continue;

  $xml.= "<item";
  reset($item);
  while(list($k,$v) = each($item)) { $xml.= " ".$k."=\"".$v."\""; }
  $xml.= "/>";
 }

 return GShell("store reset-qty -at '".$_AT."' -xml `".$xml."`", $sessid, $shellid);
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikoexcelparser_resetstoreqty_fastimport($_KEYS, $_DATA, $sessid, $shellid, $_AP="", $catId=0, $catTag="", $id=0, $sessInfo)
{

 return array('message'=>"done!");
}
//-------------------------------------------------------------------------------------------------------------------//

