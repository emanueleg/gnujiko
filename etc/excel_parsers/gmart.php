<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 03-06-2017
 #PACKAGE: excel-parsers-collection
 #DESCRIPTION: Excel parser for GMart.
 #VERSION: 2.9beta
 #CHANGELOG: 03-06-2017 : Bugfix importItemFromData (sku check added).
			 03-12-2016 : Aggiunto chiavi sku,asin,ean,gcid,gtin,upc ed aggiornata funzione fast import.
			 24-10-2016 : MySQLi integration.
			 02-03-2016 : Aggiornato fast con una classe GMartExport
			 29-09-2014 : Aggiunto il resto delle colonne.
			 13-06-2014 : Aggiunto id su import e fastimport
			 06-06-2014 : Bug fix su fastImport
			 02-06-2014 : sostituito -ct vendors con -into vendors
			 17-05-2014 : Aggiunta funzione fastimport
 #TODO:
 
*/

function gnujikoexcelparser_gmart_info()
{
 $info = array('name' => "Prodotti");
 $keys = array(
	/* BASIC INFO */
	"name"=>"Nome articolo / marca e modello",
	"desc"=>"Descrizione",
	"code"=>"Codice",
	"brand"=>"Marca",
	"model"=>"Modello",
	"barcode"=>"Codice a barre",
	"mancode"=>"Cod. art. Produttore",
	//"vencode"=>"Cod. art. Fornitore",
	"units"=>"Unita di misura",
	"baseprice"=>"Prezzo di base",
	"vat"=>"Aliquota IVA",
	//"vendorname"=>"Fornitore",
	//"vendorprice"=>"Prezzo acquisto",
	"location"=>"Collocazione art.",
	"weight"=>"Peso",
	"weightunits"=>"U.M. peso",
	"gebinde_code"=>"Cod. confezionamento",
	"gebinde"=>"Descr. confezionamento",
	"division"=>"Divisione materiale",
	"minimum_stock"=>"Scorta minima",

	// vendors
	"vendorname_1"=>"Nome del primo fornitore",
	"vendorname_2"=>"Nome del secondo fornitore",
	"vendorname_3"=>"Nome del terzo fornitore",
	"vencode_1"=>"Cod. art. Fornit. #1",
	"vencode_2"=>"Cod. art. Fornit. #2",
	"vencode_3"=>"Cod. art. Fornit. #3",
	"vendorprice_1"=>"Pr. acq. Fornit. #1",
	"vendorprice_2"=>"Pr. acq. Fornit. #2",
	"vendorprice_3"=>"Pr. acq. Fornit. #3",

	// Standard product id
	"sku"=>"SKU",
	"sku_referrer"=>"SKU referente",
	"asin"=>"Codice ASIN",
	"ean"=>"Codice EAN",
	"gcid"=>"Codice GCID",
	"gtin"=>"Codice GTIN",
	"upc"=>"Codice UPC"

	);

 $ret = GShell("store list");
 $storelist = $ret['outarr'];
 for($c=0; $c < count($storelist); $c++)
  $keys["store_".$storelist[$c]['id']."_qty"] = "Qta a mag: ".$storelist[$c]['name'];

 $keydict = array(
	/* BASIC INFO */
	"name"=> array("nome","titolo","articolo"),
	"desc"=> array("descrizione","desc.","breve descriz"),
	"code"=> array("codice"),
	"brand"=> array("marca"),
	"model"=> array("modello","mod."),
	"barcode"=> array("barcode","codice a barre","cod. a barre"),
	"mancode"=> array("codice art. produttore","cod. prod","cod. art. prod"),
	//"vencode"=> array("codice art. fornitore","cod. forn","cod.art. forn"),
	"units"=> array("um","u.m.","unità di mis","unita di mis","unita_mis","u.mis"),
	"baseprice"=> array("pr. base", "prezzo","pr. vendita","pr vendita"),
	"vat"=> array("aliq. iva","iva","aliquota iva"),
	//"vendorname"=> array("fornitore","forn."),
	//"vendorprice"=> array("prezzo acq","pr acq","pr. acq"),
	"location"=> array("locazione","collocazione","colloc. art"),
	"weight"=> array("peso"),
	"weightunits"=> array("u.m. peso", "um peso"),
	"gebinde_code"=> array("cod. conf","confezionamento","imballo"),
	"gebinde"=> array("descr. confezionamento", "descr. conf."),
	"division"=> array("settore merc","div. mat","divisione mat","divis. mat"),
	"minimum_stock"=> array("scorta min"),

	// vendors
	"vendorname_1"=> array("fornitore", "fornit. 1"),
	"vendorname_2"=> array("secondo forn", "fornit. 2"),
	"vendorname_3"=> array("terzo forn", "fornit. 3"),

	"vencode_1"=> array("cod. art. forn. 1","cod. art. fornit"),
	"vencode_2"=> array("cod. art. forn. 2"),
	"vencode_3"=> array("cod. art. forn. 3"),

	"vendorprice_1"=> array("pr. acq. forn. 1","pr. acq. fornit"),
	"vendorprice_2"=> array("pr. acq. forn. 2"),
	"vendorprice_3"=> array("pr. acq. forn. 3"),

	"sku"=>array('sku'),
	"sku_referrer"=>array('sku_referrer'),
	"asin"=>array('asin'),
	"ean"=>array('ean'),
	"gcid"=>array('gcid'),
	"gtin"=> array('gtin'),
	"upc"=>array('upc')

	);

 for($c=0; $c < count($storelist); $c++)
  $keydict["store_".$storelist[$c]['id']."_qty"] = array(0=>$storelist[$c]['name']);

 return array('info'=>$info, 'keys'=>$keys, 'keydict'=>$keydict);
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikoexcelparser_gmart_import($_DATA, $sessid, $shellid, $archivePrefix="gmart", $catId=0, $catTag="", $id=0)
{
 $ret = GShell("pricelists list",$sessid,$shellid);
 $pllist = $ret['outarr'];
 $mtime = date('Y-m-d H:i:s');

 $interface = array("name"=>"progressbar","steps"=>count($_DATA['items']));
 gshPreOutput($shellid,"Import from Excel to Products", "ESTIMATION", "", "PASSTHRU", $interface);

 $db2 = new AlpaDatabase();
 for($c=0; $c < count($_DATA['items']); $c++)
 {
  $itm = $_DATA['items'][$c];

  // verifica se l'articolo esiste gia //
  $checkQry = "SELECT id FROM dynarc_".$archivePrefix."_items WHERE ";
  if($itm['code'])
   $checkQry.= "code_str='".$itm['code']."'";
  else
  {
   $name = $db2->Purify($itm['name']);
   $checkQry.= "name='".$name."' OR name LIKE '".$name."%'";
  }

  $db2->RunQuery($checkQry." AND trash='0'");
  if($db2->Read())
  {
   gshPreOutput($shellid,"Update: ".$itm['name'].($itm['code'] ? "<i>cod.".$itm['code']."</i>" : ""),"PROGRESS", $itm);
   $qry = "dynarc edit-item -ap `".($archivePrefix ? $archivePrefix : "gmart")."` -id '".$db2->record['id']."' -name `".$itm['name']."` -code-str `"
	.$itm['code']."` -mtime '".$mtime."'";
  }
  else
  {
   gshPreOutput($shellid,"Import: ".$itm['name'].($itm['code'] ? "<i>cod.".$itm['code']."</i>" : ""),"PROGRESS", $itm);
   $qry = "dynarc new-item -ap `".($archivePrefix ? $archivePrefix : "gmart")."`"
	.($catId ? " -cat '".$catId."'" : ($catTag ? " -ct `".$catTag."`" : ""))." -name `".$itm['name']."` -mtime '".$mtime."'";
  }

  $set = "";
  $extset = "";
  if($itm['desc']) $qry.= " -desc `".$itm['desc']."`";
  if($itm['code']) $qry.= " -code-str `".$itm['code']."`";
  if($itm['baseprice']) $set.= ",baseprice='".(str_replace(",",".",$itm['baseprice']))."'";  
  if($itm['vat']) $set.= ",vat='".$itm['vat']."'";

  /* DETECT PRICELISTS */
  $pricelists = "";
  for($i=0; $i < count($pllist); $i++)
  {
   if($itm["pricelist_".$pllist[$i]['id']])
   {
	if((stripos($itm["pricelist_".$pllist[$i]['id']], "s") !== false) || (stripos($itm["pricelist_".$pllist[$i]['id']], "x") !== false))
	 $pricelists.= ",".$pllist[$i]['id'];
   }
  }
  if($pricelists)
   $set.= ",pricelists='".ltrim($pricelists,",")."'";

  if(!$itm['model'])
   $itm['model'] = $itm['name'];

  if($itm['brand']) 			$extset.= ",brand='''".$itm['brand']."'''";
  if($itm['model']) 			$extset.= ",model='''".$itm['model']."'''";
  if($itm['barcode']) 			$extset.= ",barcode='".$itm['barcode']."'";
  if($itm['mancode']) 			$extset.= ",mancode='".$itm['mancode']."'";
  //if($itm['qty_sold']) 		$extset.= ",qtysold='".$itm['qty_sold']."'";
  if($itm['units']) 			$extset.= ",units='".$itm['units']."'";
  if($itm['location'])			$extset.= ",location='".$itm['location']."'";
  if($itm['weight'])			$extset.= ",weight='".$itm['weight']."'";
  if($itm['weightunits'])		$extset.= ",weightunits='".$itm['weightunits']."'";
  if($itm['gebinde_code'])		$extset.= ",gebinde_code='".$itm['gebinde_code']."'";
  if($itm['gebinde'])			$extset.= ",gebinde='".$itm['gebinde']."'";
  if($itm['division'])			$extset.= ",division='".$itm['division']."'";
  if($itm['minimum_stock'])		$extset.= ",minimum_stock='".$itm['minimum_stock']."'";

  if($extset)
   $extset = "gmart.".ltrim($extset,",");

  /* DETECT VENDOR AND VENDOR PRICE */
  if($itm['vendorname'] || $itm['vendorprice'])
  {
   if($itm['vendorname'])
   {
	$ret = GShell("dynarc item-find -ap rubrica -into vendors `".$itm['vendorname']."`",$sessid,$shellid);
	if(count($ret['outarr']['items']))
	 $extset.=",vendorprices.vendorid='".$ret['outarr']['items'][0]['id']."',vendor='''".$ret['outarr']['items'][0]['name']."'''";
	else
	 $extset.=",vendorprices.vendor='''".$itm['vendorname']."'''";
   }
   else
	 $extset.=",vendorprices.vendor=''";
   $extset.=",code='".$itm['vencode']."',price='".(str_replace(",",".",$itm['vendorprice']))."',vat='".$itm['vat']."'";
  }

  $ret = GShell($qry.($set ? " -set `".ltrim($set,",")."`" : "").($extset ? " -extset `".ltrim($extset,",")."`" : ""),$sessid,$shellid);
  if($ret['error'])
   return $ret;

 }
 $db2->Close();

 return array('message'=>"done!");
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikoexcelparser_gmart_fastimport($_KEYS, $_DATA, $sessid, $shellid, $_AP="", $catId=0, $catTag="", $id=0, $sessInfo)
{
 if(!$_AP) $_AP = "gmart";

 $_COUNT = count($_DATA['items']);
 $interface = array("name"=>"progressbar","steps"=>$_COUNT);
 gshPreOutput($shellid,"Import ".$_COUNT." products from Excel", "ESTIMATION", "", "PASSTHRU", $interface);

 $GX = new GMartExcelImport($_AP, $sessid, $shellid, $sessInfo);
 if(!$GX->init($catId, $catTag))
  return array('message'=>$GX->debug, 'error'=>$GX->errCode);

 if(!$GX->importItems($_DATA['items']))
  return array('message'=>$GX->debug, 'error'=>$GX->errCode);


 return array('message'=>$_COUNT." elements has been imported!");
}
//-------------------------------------------------------------------------------------------------------------------//
//--- CLASS GMART EXCEL IMPORT --------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
class GMartExcelImport
{
 var $AP;
 var $sessid;
 var $shellid;
 var $sessInfo;

 var $archiveInfo;
 var $catInfo;
 var $pricelists;	
 var $pricelists_ids;	// pricelists id separated by comma
 var $stores;			// store list as array
 var $DBFIELDS;			// fields as array
 var $fields;			// fields separated by comma
 var $VENDOR_BY_NAME;	// list of vendor by name
 var $ordering;			// next ordering

 var $debug = "";
 var $errCode = "";

 function GMartExcelImport($_AP, $sessid, $shellid, $sessInfo)
 {
  $this->AP = $_AP;
  $this->sessid = $sessid;
  $this->shellid = $shellid;
  $this->sessInfo = $sessInfo;

  $this->archiveInfo = null;
  $this->catInfo = null;
  $this->pricelists =  null;
  $this->pricelists_s = "";
  $this->stores = array();
  $this->VENDOR_BY_NAME = array();

  $this->ordering = 0;

  $this->DBFIELDS =  array('uid','gid','_mod','cat_id','name','description','ordering','ctime','mtime','hierarchy',
	'code_str','brand','model','units','baseprice','vat','pricelists','barcode','manufacturer_code',
	'item_location','weight','weightunits','gebinde','gebinde_code','division','minimum_stock');


  $this->debug = "";
  $this->errCode = "";
 }
 //----------------------------------------------------------------------------------------------//
 function init($catId=0, $catTag="")
 {
  $out = "Get pricelists...";
  $ret = GShell("pricelists list",$this->sessid,$this->shellid);
  if($ret['error']) return $this->returnError($out."failed!\n".$ret['message'], $ret['error']);
  $out.= "done!\n";
  $this->pricelists = $ret['outarr'];
  $plids = "";
  for($c=0; $c < count($this->pricelists); $c++)
  {
   $this->DBFIELDS[] = "pricelist_".$this->pricelists[$c]['id']."_baseprice";
   $this->DBFIELDS[] = "pricelist_".$this->pricelists[$c]['id']."_mrate";
   $this->DBFIELDS[] = "pricelist_".$this->pricelists[$c]['id']."_vat";
   $plids.= ",".$this->pricelists[$c]['id'];
  }
  $this->pricelists_ids = ltrim($plids,",");

  $out.= "Get stores...";
  $ret = GShell("store list",$this->sessid, $this->shellid);
  if($ret['error']) return $this->returnError($out."failed!\n".$ret['message'], $ret['error']);
  $out.= "done!\n";
  $this->stores = $ret['outarr'];
  for($c=0; $c < count($this->stores); $c++)
   $this->DBFIELDS[] = "store_".$this->stores[$c]['id']."_qty";
  $this->DBFIELDS[] = "storeqty";

  $this->fields = implode(",",$this->DBFIELDS);

  $out.= "Get archive info...";
  $ret = GShell("dynarc archive-info -prefix '".$this->AP."'", $this->sessid, $this->shellid);
  if($ret['error']) return $this->returnError($out."failed!\n".$ret['message'], $ret['error']);
  $out.= "done!\n";
  $this->archiveInfo = $ret['outarr'];

  if($catId || ($catTag != ""))
  {
   $out.= "Get category info...";
   $ret = GShell("dynarc cat-info -ap '".$this->AP."'".($catId ? " -id '".$catId."'" : " -tag '".$catTag."'"), $this->sessid, $this->shellid);
   if($ret['error']) return $this->returnError($out."failed!\n".$ret['message'], $ret['error']);

   $this->catInfo = $ret['outarr'];
  }

  $out.= "Get next ordering: ";
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT MAX(ordering) AS ordering FROM dynarc_".$this->AP."_items".($this->catInfo ? " WHERE cat_id='".$this->catInfo['id']."'" : ""));
  if($db->Read())
   $this->ordering = $db->record['ordering']+1;
  else
   $this->ordering = 1;
  $db->Close();
  $out.= $this->ordering."\n";

  $this->debug.= $out;
  return true;
 }
 //----------------------------------------------------------------------------------------------//
 function returnError($message, $errcode)
 {
  $this->debug.= $message;
  $this->errCode = $errcode;
  return false;
 }
 //----------------------------------------------------------------------------------------------//
 function importItems($list)
 {
  $_COUNT = count($list);
  $outArr = array();
  for($c=0; $c < count($list); $c++)
  {
   $data = $list[$c];
   // get vendors
   if($data['vendorname_1'] && $data['vendorprice_1'])
   {
    if(!$this->getVendor($data['vendorname_1']))
	 return false;
   } 
   if($data['vendorname_2'] && $data['vendorprice_2'])
   {
    if(!$this->getVendor($data['vendorname_2']))
	 return false;
   } 
   if($data['vendorname_3'] && $data['vendorprice_3'])
   {
    if(!$this->getVendor($data['vendorname_3']))
	 return false;
   } 

   $ret = $this->importItemFromData($data, ($c+1), $_COUNT);
   if(!$ret) return false;
   $outArr[] = $ret;
  }

  GShell("gmart update-counters -ap '".$this->AP."'".($this->catInfo ? " -cat '".$this->catInfo['id']."'" : ""),$this->sessid, $this->shellid);

  return $outArr;
 }
 //----------------------------------------------------------------------------------------------//
 function importItemFromData($data, $idx=0, $_COUNT=0)
 {
  $outArr = array();
  if(!$data['name'])
  {
   if($data['model'])
	$data['name'] = $data['brand'] ? $data['brand']." ".$data['model'] : $data['model'];
   else if($data['brand'])
    $data['name'] = $data['brand']." (senza nome)";
   /*else
    $data['name'] = "senza nome";*/
  }

  // verifica se l'articolo esiste gia
  $this->debug.= "Get if article already exists...";
  $id = 0;
  $variantId = 0;
  $coltint = ""; $sizmis = "";

  $db = new AlpaDatabase();
  if($data['asin'] || $data['ean'] || $data['gcid'] || $data['gtin'] || $data['upc'])
  {
   $qry = "";
   if($data['asin'])		$qry.= " OR asin='".$data['asin']."'";
   if($data['ean'])			$qry.= " OR ean='".$data['ean']."'";
   if($data['gcid'])		$qry.= " OR gcid='".$data['gcid']."'";
   if($data['gtin'])		$qry.= " OR gtin='".$data['gtin']."'";
   if($data['upc'])			$qry.= " OR upc='".$data['upc']."'";

   $db->RunQuery("SELECT ref_id,variant_id,coltint,sizmis FROM product_spid WHERE trash='0' AND ref_ap='".$this->AP."' AND (".ltrim($qry, " OR ").") LIMIT 1");
   if($db->Read())
   {
	$id = $db->record['ref_id'];
	$variantId = $db->record['variant_id'];
	$coltint = $db->record['coltint'];
	$sizmis = $db->record['sizmis'];
	$data['variant_id'] = $variantId;
	$data['coltint'] = $coltint;
	$data['sizmis'] = $sizmis;
   }
  }

  if(!$id && $data['sku'])
  {
   $db->RunQuery("SELECT ref_id,variant_id,coltint,sizmis FROM product_sku WHERE sku='".$data['sku']."' AND ref_ap='".$this->AP."' AND trash='0'");
   if($db->Read())
   {
	$id = $db->record['ref_id'];
	$variantId = $db->record['variant_id'];
	$coltint = $db->record['coltint'];
	$sizmis = $db->record['sizmis'];
	$data['variant_id'] = $variantId;
	$data['coltint'] = $coltint;
	$data['sizmis'] = $sizmis;
   }
  }

  if(!$id)
  {
   $_WHERE = "";
   if($data['code'])	$_WHERE = "code_str='".$data['code']."'";
   else
   {
    if($data['brand'] && $data['model'])
	 $_WHERE = "brand='".$db->Purify($data['brand'])."' AND model='".$db->Purify($data['model'])."'";
    else if($data['name'])
     $_WHERE = "name='".$db->Purify($data['name'])."'";
   }
   if($_WHERE)
   {
    $db->RunQuery("SELECT id FROM dynarc_".$this->AP."_items WHERE ".$_WHERE." AND trash='0' LIMIT 1");
    if($db->Error) return $this->returnError("MySQL Error: ".$db->Error, "MYSQL_ERROR");
    if($db->Read())
     $id = $db->record['id'];
   }
  }
  
  if($id)
  {
   $this->debug.= "ok exists!\nUpdate item...";
   gshPreOutput($this->shellid,"Update item #".$id.": ".$data['name'],"PROGRESS", $data);
   $ret = $this->updateItem($db, $data, $id);
  }
  else
  {
   $this->debug.= "is new!\nCreate item...";
   gshPreOutput($this->shellid,"Import ".$idx." of ".$_COUNT,"PROGRESS", $data);
   $ret = $this->createItem($db, $data);
  }

  $db->Close();

  if(!$ret) return $this->returnError("failed!\n", $this->errCode);
  $this->debug.= "done!\n";
  $outArr = $ret;

  return $outArr;
 }
 //----------------------------------------------------------------------------------------------//
 function getVendor($vendorName, $_db=null)
 {
  if($this->VENDOR_BY_NAME[$vendorName])
   return $this->VENDOR_BY_NAME[$vendorName];

  $db = $_db ? $_db : new AlpaDatabase();
  $db->RunQuery("SELECT id,name FROM dynarc_rubrica_items WHERE name='".$vendorName."' AND trash='0' LIMIT 1");
  if(!$db->Read())
  {
   $db->RunQuery("SELECT id,name FROM dynarc_rubrica_items WHERE name LIKE '".$vendorName."%' AND trash='0' LIMIT 1");
   if(!$db->Read())
   {
	$db->RunQuery("SELECT id,name FROM dynarc_rubrica_items WHERE name LIKE '%".$vendorName."%' AND trash='0' LIMIT 1");
	if($db->Read())
	 $this->VENDOR_BY_NAME[$vendorName] = array('id'=>$db->record['id'], 'name'=>$db->record['name']);
	else
	{
	 // create new contact
	 $ret = GShell("dynarc new-item -ap 'rubrica' -ct vendors -group rubrica -name `".$vendorName."`", $this->sessid, $this->shellid);
	 if($ret['error'])
	  return $this->returnError("Unable to create vendor.\n".$ret['message'], $ret['error']);
 	 $this->VENDOR_BY_NAME[$vendorName] = $ret['outarr'];
	}
   }
   else
    $this->VENDOR_BY_NAME[$vendorName] = array('id'=>$db->record['id'], 'name'=>$db->record['name']);
  }
  else
   $this->VENDOR_BY_NAME[$vendorName] = array('id'=>$db->record['id'], 'name'=>$db->record['name']);
  if(!$_db)
   $db->Close();

  return $this->VENDOR_BY_NAME[$vendorName];
 }
 //----------------------------------------------------------------------------------------------//
 function createItem($db, $data)
 {
  $now = time();
  $ctime = date('Y-m-d H:i:s',$now);
  $mtime = $ctime;
  $uid = $this->sessInfo['uid'];
  $gid = $this->sessInfo['gid'];
  $mod = $this->archiveInfo['def_item_perms'] ? $this->archiveInfo['def_item_perms'] : 660;
  $hierarchy = $this->catInfo ? $this->catInfo['hierarchy'].$this->catInfo['id'] : ",";

  $qry = "INSERT INTO dynarc_".$this->AP."_items (".$this->fields.") VALUES('".$uid."','".$gid."','".$mod."','"
	.($this->catInfo ? $this->catInfo['id'] : '0')."','".$db->Purify($data['name'] ? $data['name'] : $data['desc'])."','"
	.$db->Purify($data['desc'])."','".$this->ordering."','".$ctime."','".$mtime."','"
	.$hierarchy."','"
	.$data['code']."','".$db->Purify($data['brand'])."','".$db->Purify($data['model'] ? $data['model'] : $data['name'])."','".$data['units']."','"
	.$data['baseprice']."','".$data['vat']."','"
	.$this->pricelists_ids."','"
	.$data['barcode']."','".$data['mancode']."','".$data['location']."','"
	.$data['weight']."','".$data['weightunits']."','"
	.$data['gebinde']."','".$data['gebinde_code']."','".$data['division']."','".$data['minimum_stock']."'";

   // pricelists
   for($i=0; $i < count($this->pricelists); $i++)
    $qry.= ",'".$data['baseprice']."','".$this->pricelists[$i]['markuprate']."','".$data['vat']."'";

   // stores
   $totqty = 0;
   for($i=0; $i < count($this->stores); $i++)
   {
	$sid = $this->stores[$i]['id'];
	if($data['store_'.$sid.'_qty'] > 0)
	 $totqty+= $data['store_'.$sid.'_qty'];

	$qry.= ",'".$data['store_'.$sid.'_qty']."'";
   }
   $qry.= ",'".$totqty."'";


   $qry.= ")";

  $db->RunQuery($qry);
  if($db->Error) return $this->returnError("MySQL Error: ".$db->Error."\nQRY: ".$qry, "MYSQL_ERROR");
  $data['id'] = $db->GetInsertId();

  $this->ordering++;

  if($data['vendorname_1'] && $data['vendorprice_1'])
  {
   $vendor = $this->getVendor($data['vendorname_1'], $db);
   if(!$vendor) return false;
   $db->RunQuery("INSERT INTO dynarc_".$this->AP."_vendorprices (item_id,code,vendor_id,vendor_name,price,vatrate) VALUES('"
	.$data['id']."','".$data['vencode_1']."','".$vendor['id']."','".$db->Purify($vendor['name'])."','".$data['vendorprice_1']."','"
	.$data['vat']."')");
   if($db->Error) return $this->returnError("MySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, "MYSQL_ERROR");
  }

  if($data['vendorname_2'] && $data['vendorprice_2'])
  {
   $vendor = $this->getVendor($data['vendorname_2'], $db);
   if(!$vendor) return false;
   $db->RunQuery("INSERT INTO dynarc_".$this->AP."_vendorprices (item_id,code,vendor_id,vendor_name,price,vatrate) VALUES('"
	.$data['id']."','".$data['vencode_2']."','".$vendor['id']."','".$db->Purify($vendor['name'])."','".$data['vendorprice_2']."','"
	.$data['vat']."')");
   if($db->Error) return $this->returnError("MySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, "MYSQL_ERROR");
  }

  if($data['vendorname_3'] && $data['vendorprice_3'])
  {
   $vendor = $this->getVendor($data['vendorname_3'], $db);
   if(!$vendor) return false;
   $db->RunQuery("INSERT INTO dynarc_".$this->AP."_vendorprices (item_id,code,vendor_id,vendor_name,price,vatrate) VALUES('"
	.$data['id']."','".$data['vencode_3']."','".$vendor['id']."','".$db->Purify($vendor['name'])."','".$data['vendorprice_3']."','"
	.$data['vat']."')");
   if($db->Error) return $this->returnError("MySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, "MYSQL_ERROR");
  }

  if($data['asin'] || $data['ean'] || $data['gcid'] || $data['gtin'] || $data['upc'])
  {
   $qry = "INSERT INTO product_spid (ref_at,ref_ap,ref_id,asin,ean,gcid,gtin,upc,variant_id,coltint,sizmis) VALUES('gmart','"
	.$this->AP."','".$data['id']."','".$data['asin']."','".$data['ean']."','".$data['gcid']."','".$data['gtin']."','".$data['upc']."','"
	.$data['variant_id']."','".$db->Purify($data['coltint'])."','".$db->Purify($data['sizmis'])."')";
   $db->RunQuery($qry);
   if($db->Error) return $this->returnError("MySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, "MYSQL_ERROR");
  }

  if($data['sku'])
  {
   $qry = "INSERT INTO product_sku (sku,referrer,ref_at,ref_ap,ref_id,variant_id,coltint,sizmis) VALUES('".$data['sku']."','"
	.$data['sku_referrer']."','gmart','".$this->AP."','".$data['id']."','".$data['variant_id']."','"
	.$db->Purify($data['coltint'])."','".$db->Purify($data['sizmis'])."')";
   $db->RunQuery($qry);
   if($db->Error) return $this->returnError("MySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, "MYSQL_ERROR");
  }

  return $data;
 }
 //----------------------------------------------------------------------------------------------//
 function updateItem($db, $data, $id)
 {
  $qry = "";
  
  if($data['code'])				$qry.= ",code_str='".$data['code']."'";
  if($data['name'])				$qry.= ",name='".$db->Purify($data['name'])."'";
  if($data['desc'])				$qry.= ",description='".$db->Purify($data['desc'])."'";
  if($data['brand'])			$qry.= ",brand='".$db->Purify($data['brand'])."'";
  if($data['model'])			$qry.= ",model='".$db->Purify($data['model'])."'";
  if($data['units'])			$qry.= ",units='".$data['units']."'";
  if($data['baseprice'])		$qry.= ",baseprice='".$data['baseprice']."'";
  if($data['vat'])				$qry.= ",vat='".$data['vat']."'";
  if($data['barcode'])			$qry.= ",barcode='".$data['barcode']."'";
  if($data['mancode'])			$qry.= ",manufacturer_code='".$data['mancode']."'";
  if($data['location'])			$qry.= ",item_location='".$data['location']."'";
  if($data['weight'])			$qry.= ",weight='".$data['weight']."'";
  if($data['weightunits'])		$qry.= ",weightunits='".$data['weightunits']."'";
  if($data['gebinde'])			$qry.= ",gebinde='".$data['gebinde']."'";
  if($data['gebinde_code'])		$qry.= ",gebinde_code='".$data['gebinde_code']."'";
  if($data['division'])			$qry.= ",division='".$data['division']."'";
  if($data['minimum_stock'])	$qry.= ",minimum_stock='".$data['minimum_stock']."'";

  if($data['baseprice'] || $data['vat'])
  {
   // pricelists
   for($i=0; $i < count($this->pricelists); $i++)
   {
    if($data['baseprice'])
     $qry.= ",pricelist_".$this->pricelists[$i]['id']."_baseprice='".$data['baseprice']."'";
	if($data['vat'])
	 $qry.= ",pricelist_".$this->pricelists[$i]['id']."_vat='".$data['vat']."'";
   }
  }

  // stores
  $xqry = "";
  for($i=0; $i < count($this->stores); $i++)
  {
   $sid = $this->stores[$i]['id'];
   if($data['store_'.$sid.'_qty'])
	$qry.= ",store_".$sid."_qty='".$data['store_'.$sid.'_qty']."'";
   $xqry.= "+store_".$sid."_qty";
  }
  $qry.= ",storeqty=".ltrim($xqry,'+');

  if($qry)
  {
   $db->RunQuery("UPDATE dynarc_".$this->AP."_items SET ".ltrim($qry, ',')." WHERE id='".$id."'");
   if($db->Error) return $this->returnError("MySQL Error: ".$db->Error, "MYSQL_ERROR");
   $data['id'] = $id;
  }

  // UPDATE VENDORS
  if(($data['vendorname_1'] && $data['vendorprice_1']) ||
	 ($data['vendorname_2'] && $data['vendorprice_2']) ||
	 ($data['vendorname_3'] && $data['vendorprice_3']) )
  {
   // get vendors
   $vendorByName = array();
   $db->RunQuery("SELECT id,vendor_id,vendor_name FROM dynarc_".$this->AP."_vendorprices WHERE item_id='".$id."'");
   if($db->Error) return $this->returnError("MySQL Error: ".$db->Error, "MYSQL_ERROR");
   while($db->Read())
   {
	$vendorByName[$db->record['vendor_name']] = $db->record;
   }

   if($data['vendorname_1'] && $data['vendorprice_1'])
   {
	if($vendorByName[$data['vendorname_1']])
	{
	 // update vendor data
	 $vpID = $vendorByName[$data['vendorname_1']]['id'];
	 $set = "price='".$data['vendorprice_1']."'";
	 if($data['vencode_1'])	$set.= ",code='".$data['vencode_1']."'";
	 $db->RunQuery("UPDATE dynarc_".$this->AP."_vendorprices SET ".$set." WHERE id='".$vpID."'");
	 if($db->Error) return $this->returnError("MySQL Error: ".$db->Error, "MYSQL_ERROR");
	}
	else
	{
     $vendor = $this->getVendor($data['vendorname_1'], $db);
     if(!$vendor) return false;
     $db->RunQuery("INSERT INTO dynarc_".$this->AP."_vendorprices (item_id,code,vendor_id,vendor_name,price,vatrate) VALUES('"
		.$id."','".$data['vencode_1']."','".$vendor['id']."','".$db->Purify($vendor['name'])."','".$data['vendorprice_1']."','"
		.$data['vat']."')");
     if($db->Error) return $this->returnError("MySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, "MYSQL_ERROR");
	}
   }

   if($data['vendorname_2'] && $data['vendorprice_2'])
   {
	if($vendorByName[$data['vendorname_2']])
	{
	 // update vendor data
	 $vpID = $vendorByName[$data['vendorname_2']]['id'];
	 $set = "price='".$data['vendorprice_2']."'";
	 if($data['vencode_2'])	$set.= ",code='".$data['vencode_2']."'";
	 $db->RunQuery("UPDATE dynarc_".$this->AP."_vendorprices SET ".$set." WHERE id='".$vpID."'");
	 if($db->Error) return $this->returnError("MySQL Error: ".$db->Error, "MYSQL_ERROR");
	}
	else
	{
     $vendor = $this->getVendor($data['vendorname_2'], $db);
     if(!$vendor) return false;
     $db->RunQuery("INSERT INTO dynarc_".$this->AP."_vendorprices (item_id,code,vendor_id,vendor_name,price,vatrate) VALUES('"
		.$id."','".$data['vencode_2']."','".$vendor['id']."','".$db->Purify($vendor['name'])."','".$data['vendorprice_2']."','"
		.$data['vat']."')");
     if($db->Error) return $this->returnError("MySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, "MYSQL_ERROR");
	}
   }

   if($data['vendorname_3'] && $data['vendorprice_3'])
   {
	if($vendorByName[$data['vendorname_3']])
	{
	 // update vendor data
	 $vpID = $vendorByName[$data['vendorname_3']]['id'];
	 $set = "price='".$data['vendorprice_3']."'";
	 if($data['vencode_3'])	$set.= ",code='".$data['vencode_3']."'";
	 $db->RunQuery("UPDATE dynarc_".$this->AP."_vendorprices SET ".$set." WHERE id='".$vpID."'");
	 if($db->Error) return $this->returnError("MySQL Error: ".$db->Error, "MYSQL_ERROR");
	}
	else
	{
     $vendor = $this->getVendor($data['vendorname_3'], $db);
     if(!$vendor) return false;
     $db->RunQuery("INSERT INTO dynarc_".$this->AP."_vendorprices (item_id,code,vendor_id,vendor_name,price,vatrate) VALUES('"
		.$id."','".$data['vencode_3']."','".$vendor['id']."','".$db->Purify($vendor['name'])."','".$data['vendorprice_3']."','"
		.$data['vat']."')");
     if($db->Error) return $this->returnError("MySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, "MYSQL_ERROR");
	}
   }

  } // EOF - UPDATE VENDORS

  if($data['sku'])
  {
   $db->RunQuery("SELECT id,sku FROM product_sku WHERE referrer='".$data['sku_referrer']."' AND ref_ap='".$this->AP."' AND ref_id='"
	.$id."' AND variant_id='".$data['variant_id']."'");
   if(!$db->Read())
	$db->RunQuery("INSERT INTO product_sku (sku,referrer,ref_at,ref_ap,ref_id,variant_id,coltint,sizmis) VALUES('".$data['sku']."','"
	.$data['sku_referrer']."','gmart','".$this->AP."','".$id."','".$data['variant_id']."','"
	.$db->Purify($data['coltint'])."','".$db->Purify($data['sizmis'])."')");
   else if($db->record['sku'] != $data['sku'])
	$db->RunQuery("UPDATE product_sku SET sku='".$data['sku']."' WHERE id='".$db->record['id']."'");
   if($db->Error) return $this->returnError("MySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, "MYSQL_ERROR");
  }

  if($data['asin'] || $data['ean'] || $data['gcid'] || $data['gtin'] || $data['upc'])
  {
   $db->RunQuery("SELECT id FROM product_spid WHERE ref_ap='".$this->AP."' AND ref_id='".$id."' AND variant_id='".$data['variant_id']."'");
   if(!$db->Read())
	$db->RunQuery("INSERT INTO product_spid (ref_at,ref_ap,ref_id,asin,ean,gcid,gtin,upc,variant_id,coltint,sizmis) VALUES('gmart','"
	.$this->AP."','".$id."','".$data['asin']."','".$data['ean']."','".$data['gcid']."','".$data['gtin']."','".$data['upc']."','"
	.$data['variant_id']."','".$db->Purify($data['coltint'])."','".$db->Purify($data['sizmis'])."')");
   else
   {
	$qry = "";
	if($data['asin'])	$qry.= ",asin='".$data['asin']."'";
	if($data['ean'])	$qry.= ",ean='".$data['ean']."'";
	if($data['gcid'])	$qry.= ",gcid='".$data['gcid']."'";
	if($data['gtin'])	$qry.= ",gtin='".$data['gtin']."'";
	if($data['upc'])	$qry.= ",upc='".$data['upc']."'";
	$db->RunQuery("UPDATE product_spid SET ".ltrim($qry,",")." WHERE id='".$db->record['id']."'");
   }
   if($db->Error) return $this->returnError("MySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, "MYSQL_ERROR");
  }
 
  return $data;
 }
 //----------------------------------------------------------------------------------------------//
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//


