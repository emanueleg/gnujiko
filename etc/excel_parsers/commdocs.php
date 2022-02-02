<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-12-2016
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Parser per importare documenti da Excel.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

function gnujikoexcelparser_commdocs_info()
{
 $info = array('name' => "Indirizzi");
 $keys = array(
	/* BASIC INFO */
	"subject"=>"Cliente / Intestatario del documento",
	"docdate"=>"Data doc.",

	// Shipping
	"recp"=>"Destinatario",
	"address"=>"Indirizzo (completo di n. civico ed eventuali altri dati)",
	"address1"=>"Indirizzo 1",
	"address2"=>"Indirizzo 2",
	"address3"=>"Indirizzo 3",
	"city"=>"Citta",
	"zipcode"=>"C.A.P.",
	"province"=>"Provincia (sigla)",
	"countrycode"=>"Stato / Paese (sigla)",
	"phone"=>"Telefono",
	"phone2"=>"Telefono 2",
	"fax"=>"Fax",
	"cell"=>"Cellulare",
	"email"=>"Email",
	"email2"=>"Email 2",
	"email3"=>"Email 3",

	// Elements
	"code"=>"Codice articolo",
	"vencode"=>"Cod. art. fornitore",
	"sn"=>"Serial number",
	"lot"=>"Lotto",
	"brand"=>"Marca",
	"name"=>"Modello / nome articolo",
	"description"=>"Breve descrizione articolo",
	"qty"=>"Qta",
	"coltint"=>"Colore / tinta",
	"sizmis"=>"Taglia / misura",
	"saleprice"=>"Prezzo di vendita (IVA esclusa)",
	"finalpricevatincluded"=>"Prezzo di vendita IVA inclusa",
	"vendorprice"=>"Prezzo d&lsquo;acquisto (IVA esclusa)",
	"vatrate"=>"Aliquota IVA",

	// Spese
	"cartage"=>"Spese di trasporto",

	// Altri dati utili al tracciamento dell'articolo
	"sku"=>"SKU",
	"asin"=>"Codice ASIN",
	"ean"=>"Codice EAN",
	"gcid"=>"Codice GCID",
	"gtin"=>"Codice GTIN",
	"upc"=>"Codice UPC"
	
	);

 $keydict = array(
	/* BASIC INFO */
	"subject"=> array('recipient-name','cliente'),
	"docdate"=> array('purchase-date','data doc'),

	// Shipping
	"recp"=> array('recipient-name', 'destinatario','dest. merce'),
	"address"=> array('indirizzo'),
	"address1"=> array('ship-address-1'),
	"address2"=> array('ship-address-2'),
	"address3"=> array('ship-address-3'),
	"city"=> array('ship-city', 'citta'),
	"zipcode"=> array('ship-postal-code', 'cap','c.a.p'),
	"province"=> array('ship-state', 'prov'),
	"countrycode"=> array('ship-country'),
	"phone"=> array('buyer-phone-number', 'telefono'),
	"phone2"=> array('ship-phone-number'),
	"fax"=> array('fax'),
	"cell"=> array('cellulare'),
	"email"=> array('e-mail','e_mail','email'),
	"email2"=> array('email2'),
	"email3"=> array('email3'),

	//Elements
	"code"=> array('product-code','cod. art'),
	"vencode"=> array('product-vendor-code','cod. art. fornit'),
	"sn"=> array('product-serial-number','serial-number','sn'),
	"lot"=> array('lotto'),
	"brand"=> array('product-brand','brand','marca'),
	"name"=> array('product-name','model','modello'),
	"description"=> array('product-description','description','descrizione'),
	"qty"=> array('quantity-purchased','qty','qta'),
	"coltin"=> array('product-color','variant','colore','tinta'),
	"sizmis"=> array('product-size','size','dimension','taglia'),
	"saleprice"=> array('product-price'),
	"finalpricevatincluded"=> array('item-price','prezzo','pr. vendita'),
	"vendorprice"=> array('pr. acq'),
	"vatrate"=> array('aliq. iva'),

	// Spese
	"cartage"=> array('shipping-price','sp. trasporto'),

	// Altri dati utili al tracciamento dell'articolo
	"sku"=>array('sku'),
	"asin"=>array('asin'),
	"ean"=>array('ean'),
	"gcid"=>array('gcid'),
	"gtin"=> array('gtin'),
	"upc"=>array('upc')
	);

 return array('info'=>$info, 'keys'=>$keys, 'keydict'=>$keydict);
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikoexcelparser_commdocs_import($_DATA, $sessid, $shellid, $archivePrefix="commercialdocs", $catId=0, $catTag="", $id=0)
{
 global $_BASE_PATH, $_COMPANY_PROFILE;

 include_once($_BASE_PATH."include/extendedfunc.php");
 include_once($_BASE_PATH."include/company-profile.php");

 $_DEF_VATID = $_COMPANY_PROFILE['accounting']['freq_vat_used'];
 // Get default vat rate
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT vat_type,percentage FROM dynarc_vatrates_items WHERE id='".$_DEF_VATID."'");
 $db->Read();
 $_DEF_VATTYPE = $db->record['vat_type'];
 $_DEF_VATRATE = $db->record['percentage'];
 $db->Close();

 /* GET CONFIG */
 $ret = GShell("aboutconfig get-config -app gcommercialdocs -sec interface");
 if(!$ret['error'])
  $config = $ret['outarr']['config'];


 // Get document root cat
 $ret = GShell("dynarc getrootcat -ap '".$archivePrefix."'".($catId ? " -id '".$catId."'" : " -tag '".$catTag."'"), $sessid, $shellid);
 if($ret['error']) return array('message'=>"Unable to get document root cat.\n".$ret['message'], 'error'=>$ret['error']);
 $_GROUP = "commdocs-".strtolower($ret['outarr']['tag']);

 $interface = array("name"=>"progressbar","steps"=>count($_DATA['items']));
 gshPreOutput($shellid,"Generating documents from Excel", "ESTIMATION", "", "PASSTHRU", $interface);

 // ordina gli elementi per data
 $_DATES = array();
 $_ITEMS = array();
 for($c=0; $c < count($_DATA['items']); $c++)
 {
  $item = $_DATA['items'][$c];
  $time = $item['docdate'] ? $item['docdate'] : $c;
  if(!$_DATES[$time])
   $_DATES[$time] = array();
  $_DATES[$time][] = $item;
 }
 ksort($_DATES);
 foreach($_DATES as $key => $val)
 {
  for($c=0; $c < count($val); $c++)
   $_ITEMS[] = $val[$c];
 }

 for($c=0; $c < count($_ITEMS); $c++)
 {
  $data = $_ITEMS[$c];
  gshPreOutput($shellid,"Import: #".($c+1)." - ".$data['subject'],"PROGRESS", $data);

  // Prepare XML
  $xmlArr = array('elements'=>array());
  $xmlArr['subject'] = array('name'=>($data['subject'] ? $data['subject'] : $data['recp']), 'address'=>'', 'city'=>$data['city'], 'zip'=>$data['zipcode'], 
	'province'=>$data['province'], 'countrycode'=>$data['countrycode'], 'phone'=>$data['phone'], 'phone2'=>$data['phone2'],
	'fax'=>$data['fax'], 'cell'=>$data['cell'], 'email'=>$data['email'], 'email2'=>$data['email2'], 'email3'=>$data['email3'], 
	'skype'=>$data['skype']);

  // detect shipping address
  $xaddr = array();
  if($data['address']) $xaddr[] = $data['address'];
  if($data['address1'] || $data['address2'] || $data['address3'])
  {
   if($data['address1'])	$xaddr[] = $data['address1'];
   if($data['address2'])	$xaddr[] = $data['address2'];
   if($data['address3'])	$xaddr[] = $data['address3'];
  }
  $address = implode(" ",$xaddr);

  $xmlArr['subject']['address'] = $address; //se il cliente non è registrato in rubrica verrà creato e gli sarà assegnato come indirizzo principale (sede) quello di destinazione merci.

  $xmlArr['shipping'] = array('recp'=>$data['recp'], 'address'=>$address, 'city'=>$data['city'], 'zip'=>$data['zipcode'], 
	'prov'=>$data['province'], 'cc'=>$data['countrycode']);

  // Elements
  $price = 0;
  $vat = 0;

  if($data['finalpricevatincluded'])
   $price = ($data['finalpricevatincluded']/(100+$_DEF_VATRATE)) * 100;
  else
   $price = $data['saleprice'];

  $el = array('code'=>$data['code'], 'sku'=>$data['sku'], 'asin'=>$data['asin'], 'ean'=>$data['ean'], 'gcid'=>$data['gcid'], 
	'gtin'=>$data['gtin'], 'upc'=>$data['upc'], 'vencode'=>$data['vencode'], 'sn'=>$data['sn'], 'lot'=>$data['lot'], 
	'brand'=>$data['brand'], 'name'=>$data['name'], 'description'=>$data['description'], 'qty'=>$data['qty'], 'units'=>$data['units'], 
	'coltint'=>$data['coltint'], 'sizmis'=>$data['sizmis'], 'price'=>$price, 'vat'=>$_DEF_VATRATE, 'vatid'=>$_DEF_VATID, 'vattype'=>$_DEF_VATTYPE);

  $xmlArr['elements'][] = $el;
  
  // Generate document order
  $_CMD = "commercialdocs generate-fast-document -ctime `".$data['docdate']."`".($catId ? " -cat '".$catId."'" : " -type '".$catTag."'")." -group '".$_GROUP."' -xml `".array_to_xml($xmlArr)."` --auto-find-products";

  if($config['options']['excelimp_regnewprod'])
  {
   $_CMD.= " --auto-register-products";
   if($config['options']['excelimp_defap'])		$_CMD.= " --default-gmart-ap '".$config['options']['excelimp_defap']."'";
   if($config['options']['excelimp_defcat'])	$_CMD.= " --default-gmart-cat '".$config['options']['excelimp_defcat']."'";
  }

  $ret = GShell($_CMD, $sessid, $shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'], 'error'=>$ret['error']);

 } // EOF - for(data['items'])

 return array('message'=>"done!");
}
//-------------------------------------------------------------------------------------------------------------------//

