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

function gnujikoexcelparser_rubrica_info()
{
 $info = array('name' => "Rubrica");
 $keys = array(
	/* BASIC INFO */
	"name"=>"Nome e Cognome / Ragione sociale",
	"notes"=>"Note",
	"code"=>"Codice",
	"taxcode"=>"Codice Fiscale",
	"vatnumber"=>"Partita IVA",
	"paymentmode"=>"Modalita di pagamento",
	"pricelist"=>"Listino prezzi associato",
	"distance"=>"Distanza (x rimborsi chilometrici)",
	/* CONTACTS */
	"address"=>"Indirizzo",
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
	"skype"=>"Skype"
	);

 $keydict = array(
	/* BASIC INFO */
	"name"=> array("nome","cliente","fornitore"),
	"notes"=> array("note","annotazioni"),
	"code"=> array("codice","cod."),
	"taxcode"=> array("cf","codice fiscale", "cod. fisc", "cod.fisc", "cfisc"),
	"vatnumber"=> array("piva","partita iva","p.iva"),
	"paymentmode"=> array("pagamento","modalita di pagamento","cod. pagam", "codpagamento", "cod.pagam"),
	"pricelist"=> array("listino","listino prezzi"),
	"distance"=> array("distanza","km"),
	/* CONTACTS */
	"address"=> array("indirizzo"),
	"city"=> array("città","citta"),
	"zipcode"=> array("cap","c.a.p"),
	"province"=> array("prov"),
	"countrycode"=> array("stato","nazione"),
	"phone"=> array("telefono","tel."),
	"phone2"=> array("telefono2","tel2"),
	"fax"=> array("fax"),
	"cell"=> array("cell","cellulare"),
	"email"=> array("e-mail","e_mail","mail"),
	"email2"=> array("email2"),
	"email3"=> array("email3"),
	"skype"=> array("skype")
	);

 return array('info'=>$info, 'keys'=>$keys, 'keydict'=>$keydict);
}

function gnujikoexcelparser_rubrica_import($_DATA, $sessid, $shellid, $archivePrefix="", $catId=0, $catTag="")
{
 $interface = array("name"=>"progressbar","steps"=>count($_DATA['items']));
 gshPreOutput($shellid,"Import from Excel to Rubrica", "ESTIMATION", "", "PASSTHRU", $interface);

 for($c=0; $c < count($_DATA['items']); $c++)
 {
  $itm = $_DATA['items'][$c];

  gshPreOutput($shellid,"Import: ".$itm['name'].($itm['code'] ? "<i>cod.".$itm['code']."</i>" : ""),"PROGRESS", $itm);

  $qry = "dynarc new-item -ap `".($archivePrefix ? $archivePrefix : "rubrica")."`"
	.($catId ? " -cat '".$catId."'" : ($catTag ? " -ct `".$catTag."`" : ""))." -name `".$itm['name']."`";

  $set = "";
  $extset = "";
  if($itm['notes']) $qry.= " -desc `".$itm['notes']."`";
  if($itm['code']) $qry.= " -code-str `".$itm['code']."`";
  if($itm['taxcode']) $set.= ",taxcode='".$itm['taxcode']."'";
  if($itm['vatnumber']) $set.= ",vatnumber='".$itm['vatnumber']."'";

  if($itm['paymentmode'])
  {
   $ret = GShell("accounting paymentmodeinfo `".$itm['paymentmode']."` --get-id",$sessid,$shellid);
   $set.= ",paymentmode='".$ret['outarr']['id']."'";
  }

  /*if($itm['pricelist']) $set.= ",pricelist_id='".$itm['pricelist']."'"; TODO: da vedere */

  if($itm['distance']) $set.= ",distance='".$itm['distance']."'";

  /* CONTACTS */
  if($itm['address'] || $itm['city'] || $itm['phone'] || $itm['cell'] || $itm['email'])
  {
   $extset = "contacts.name='''".$itm['name']."'''";
   if($itm['address']) $extset.= ",address='''".$itm['address']."'''";
   if($itm['city']) $extset.= ",city='''".$itm['city']."'''";
   if($itm['zipcode']) $extset.= ",zipcode='".$itm['zipcode']."'";
   if($itm['province']) $extset.= ",province='".$itm['province']."'";
   if($itm['countrycode']) $extset.= ",countrycode='".$itm['countrycode']."'";
   if($itm['phone']) $extset.= ",phone='".$itm['phone']."'";
   if($itm['phone2']) $extset.= ",phone2='".$itm['phone2']."'";
   if($itm['fax']) $extset.= ",fax='".$itm['fax']."'";
   if($itm['cell']) $extset.= ",cell='".$itm['cell']."'";
   if($itm['email']) $extset.= ",email='".$itm['email']."'";
   if($itm['email2']) $extset.= ",email2='".$itm['email2']."'";
   if($itm['email3']) $extset.= ",email3='".$itm['email3']."'";
   if($itm['skype']) $extset.= ",skype='".$itm['skype']."'";
  }
  $ret = GShell($qry.($set ? " -set `".ltrim($set,",")."`" : "").($extset ? " -extset `".$extset."`" : ""),$sessid,$shellid);
  if($ret['error'])
   return $ret;
 }

 return array('message'=>"done!");
}





