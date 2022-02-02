<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 
 #PACKAGE: 
 #DESCRIPTION: 
 #VERSION: 2.0beta
 #CHANGELOG: 
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
	"mancode"=>"Cod. Art. Produttore",
	"qty_sold"=>"N. articoli venduti",
	"units"=>"Unita di misura",
	"storeqty"=>"Quantita a magazzino",
	"booked"=>"N. art. prenotati",
	"incoming"=>"N. art. ordinati a fornit.",
	"loaded"=>"Tot. qta caricata a magazz.",
	"downloaded"=>"Tot. qta scaricata dai magazz.",
	"baseprice"=>"Prezzo di base",
	"vat"=>"Aliquota IVA",
	"vendorname"=>"Fornitore",
	"catname"=>"Categoria articolo",
	"vendorprice"=>"Prezzo acquisto"
	);

 $ret = GShell("pricelists list");
 $list = $ret['outarr'];
 for($c=0; $c < count($list); $c++)
  $keys["pricelist_".$list[$c]['id']] = $list[$c]['name'];

 $keydict = array(
	/* BASIC INFO */
	"name"=> array("nome","titolo","articolo"),
	"desc"=> array("descrizione","desc."),
	"code"=> array("codice","cod."),
	"brand"=> array("marca"),
	"model"=> array("modello","mod."),
	"barcode"=> array("barcode","codice a barre","cod. a barre"),
	"mancode"=> array("codice produttore","cod. prod"),
	"qty_sold"=> array("qta venduta","qtà venduta","articoli venduti","venduti","merce venduta"),
	"units"=> array("um","u.m.","unità di mis","unita di mis","unita_mis"),
	"storeqty"=> array("giacenza fisica","giac. fisica","a magazzino","articoli a magazzino","qta a magaz","qtà a magaz","qta"),
	"booked"=> array("prenotati","prenotata"),
	"incoming"=> array("ordinati","ordinata"),
	"loaded"=> array("caricati","caricata"),
	"downloaded"=> array("scaricati","scaricata"),
	"baseprice"=> array("prezzo","pr. vendita","pr vendita"),
	"vat"=> array("iva","aliquota iva"),
	"vendorname"=> array("fornitore","forn."),
	"catname"=> array("categoria","cat.","gruppo merce","gruppo_mer"),
	"vendorprice"=> array("prezzo acq","pr acq")
	);

 for($c=0; $c < count($list); $c++)
  $keydict["pricelist_".$list[$c]['id']] = array(0=>strtolower($list[$c]['name']));

 return array('info'=>$info, 'keys'=>$keys, 'keydict'=>$keydict);
}

function gnujikoexcelparser_gmart_import($_DATA, $sessid, $shellid, $archivePrefix="", $catId=0, $catTag="")
{
 $ret = GShell("pricelists list",$sessid,$shellid);
 $pllist = $ret['outarr'];


 $interface = array("name"=>"progressbar","steps"=>count($_DATA['items']));
 gshPreOutput($shellid,"Import from Excel to Products", "ESTIMATION", "", "PASSTHRU", $interface);

 for($c=0; $c < count($_DATA['items']); $c++)
 {
  $itm = $_DATA['items'][$c];

  gshPreOutput($shellid,"Import: ".$itm['name'].($itm['code'] ? "<i>cod.".$itm['code']."</i>" : ""),"PROGRESS", $itm);

  $qry = "dynarc new-item -ap `".($archivePrefix ? $archivePrefix : "gmart")."`"
	.($catId ? " -cat '".$catId."'" : ($catTag ? " -ct `".$catTag."`" : ""))." -name `".$itm['name']."`";

  $set = "";
  $extset = "";
  if($itm['desc']) $qry.= " -desc `".$itm['desc']."`";
  if($itm['code']) $qry.= " -code-str `".$itm['code']."`";

  if($itm['storeqty']) $set.= ",storeqty='".$itm['storeqty']."'";
  if($itm['booked']) $set.= ",booked='".$itm['booked']."'";
  if($itm['incoming']) $set.= ",incoming='".$itm['incoming']."'";
  if($itm['loaded']) $set.= ",loaded='".$itm['loaded']."'";
  if($itm['downloaded']) $set.= ",downloaded='".$itm['downloaded']."'";  
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

  if($itm['brand']) $extset.= ",brand='''".$itm['brand']."'''";
  if($itm['model']) $extset.= ",model='''".$itm['model']."'''";
  if($itm['barcode']) $extset.= ",barcode='".$itm['barcode']."'";
  if($itm['mancode']) $extset.= ",mancode='".$itm['mancode']."'";
  if($itm['qty_sold']) $extset.= ",qtysold='".$itm['qty_sold']."'";
  if($itm['units']) $extset.= ",units='".$itm['units']."'";

  if($extset)
   $extset = "gmart.".ltrim($extset,",");

  /* DETECT VENDOR AND VENDOR PRICE */
  if($itm['vendorname'] || $itm['vendorprice'])
  {
   if($itm['vendorname'])
   {
	$ret = GShell("dynarc item-find -ap rubrica -ct vendors `".$itm['vendorname']."`",$sessid,$shellid);
	if(count($ret['outarr']['items']))
	 $extset.=",vendorprices.vendorid='".$ret['outarr']['items'][0]['id']."',vendor='''".$ret['outarr']['items'][0]['name']."'''";
	else
	 $extset.=",vendorprices.vendor='''".$itm['vendorname']."'''";
   }
   else
	 $extset.=",vendorprices.vendor=''";
   $extset.=",price='".(str_replace(",",".",$itm['vendorprice']))."',vat='".$itm['vat']."'";
  }

  $ret = GShell($qry.($set ? " -set `".ltrim($set,",")."`" : "").($extset ? " -extset `".ltrim($extset,",")."`" : ""),$sessid,$shellid);
  if($ret['error'])
   return $ret;

 }

 return array('message'=>"done!");
}

