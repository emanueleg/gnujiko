<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 29-09-2014
 #PACKAGE: excel-parsers-collection
 #DESCRIPTION: Rubrica excel parser 
 #VERSION: 2.3beta
 #CHANGELOG: 29-09-2014 : Aggiunto fidelity card e punteggio.
			 13-06-2014 : Aggiunto id su import.
			 13-04-2014 : Aggiunto campo note.
 #TODO:
 
*/

function gnujikoexcelparser_rubrica_info()
{
 $info = array('name' => "Rubrica");
 $keys = array(
	/* BASIC INFO */
	"id"=>"ID",
	"name"=>"Nome e Cognome / Ragione sociale",
	"desc"=>"Note",
	"notes"=>"Extra Notes",
	"code"=>"Codice",
	"taxcode"=>"Codice Fiscale",
	"vatnumber"=>"Partita IVA",
	"paymentmode"=>"Modalita di pagamento",
	"paymentmodeid"=>"ID mod. di pagam.",
	"pricelist"=>"Listino prezzi associato",
	"distance"=>"Distanza (x rimborsi chilometrici)",
	"agentid"=>"ID Agente",
	"fidelitycard"=>"Fidelity Card",
	"fidelitycardpoints"=>"Punti accumulati",
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
	"skype"=>"Skype",
	/* BANKS */
	"bnkname"=>"Nome banca",
	"bnkabi"=>"Banca - ABI",
	"bnkcab"=>"Banca - CAB",
	"bnkcin"=>"Banca - CIN",
	"bnkcc"=>"Banca - Conto Corrente",
	"bnkiban"=>"Banca - IBAN"
	);

 $keydict = array(
	/* BASIC INFO */
	"id"=> array("id"),
	"name"=> array("nome","cliente","fornitore"),
	"desc"=> array("note","annotazioni"),
	"notes"=> array("extra note","annotazioni extra"),
	"code"=> array("codice","cod."),
	"taxcode"=> array("cf","codice fiscale", "cod. fisc", "cod.fisc", "cfisc"),
	"vatnumber"=> array("piva","partita iva","p.iva"),
	"paymentmode"=> array("pagamento","modalita di pagamento","cod. pagam", "codpagamento", "cod.pagam"),
	"paymentmodeid"=> array("id pagamento", "id mod. di pagam.", "id cod. pagam"),
	"pricelist"=> array("listino","listino prezzi"),
	"distance"=> array("distanza","km"),
	"agentid"=> array("agent","agentid"),
	"fidelitycard"=> array("fidelity card","cod. fidelity"),
	"fidelitycardpoints"=> array("punti","punteggio"),
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
	"skype"=> array("skype"),
	/* BANKS */
	"bnkname"=> array("banca"),
	"bnkabi"=> array("abi"),
	"bnkcab"=> array("cab"),
	"bnkcin"=> array("cin"),
	"bnkcc"=> array("cc","conto corrente"),
	"bnkiban"=> array("iban")
	);

 return array('info'=>$info, 'keys'=>$keys, 'keydict'=>$keydict);
}

function gnujikoexcelparser_rubrica_import($_DATA, $sessid, $shellid, $archivePrefix="", $catId=0, $catTag="", $id=0)
{
 $interface = array("name"=>"progressbar","steps"=>count($_DATA['items']));
 gshPreOutput($shellid,"Import from Excel to Rubrica", "ESTIMATION", "", "PASSTHRU", $interface);

 for($c=0; $c < count($_DATA['items']); $c++)
 {
  $itm = $_DATA['items'][$c];

  gshPreOutput($shellid,"Import: ".$itm['name'].($itm['code'] ? "<i>cod.".$itm['code']."</i>" : ""),"PROGRESS", $itm);

  $qry = "dynarc new-item -ap `".($archivePrefix ? $archivePrefix : "rubrica")."`"
	.($catId ? " -cat '".$catId."'" : ($catTag ? " -ct `".$catTag."`" : ""))." -name `".$itm['name']."`";
  if($itm['id'])
   $qry.= " -id '".$itm['id']."'";

  $set = "";
  $extset = "";
  if($itm['desc']) $qry.= " -desc `".$itm['desc']."`";
  if($itm['code']) $qry.= " -code-str `".$itm['code']."`";

  /* INFO */
  $extset = "rubricainfo.taxcode='".$itm['taxcode']."',vatnumber='".$itm['vatnumber']."',paymentmode='".$itm['paymentmodeid']."',pricelist='"
	.$itm['pricelistid']."',distance='".$itm['distance']."',fidelitycard='".$itm['fidelitycard']."',fidelitycardpoints='"
	.$itm['fidelitycardpoints']."',agentid='".$itm['agentid']."',extranotes='''".$itm['extranotes']."'''";

  /* CONTACTS */
  if($itm['address'] || $itm['city'] || $itm['phone'] || $itm['cell'] || $itm['email'])
  {
   $extset.= ",contacts.name='''".$itm['name']."'''";
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
  
  /* BANKS */
  if($itm['bnkname'] || $itm['bnkabi'] || $itm['bnkcab'] || $itm['bnkcc'] || $itm['bnkiban'])
  {
   $extset.= ",banks.name='''".$itm['bnkname']."''',abi='".$itm['bnkabi']."',cab='".$itm['bnkcab']."',cin='".$itm['bnkcin']."',cc='"
	.$itm['bnkcc']."',iban='".$itm['bnkiban']."'";
  }

  $ret = GShell($qry.($set ? " -set `".ltrim($set,",")."`" : "").($extset ? " -extset `".$extset."`" : ""),$sessid,$shellid);
  if($ret['error'])
   return $ret;
 }

 return array('message'=>"done!");
}

