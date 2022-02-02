<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-10-2013
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Some functions for GCommercialDocs
 #VERSION: 2.18beta
 #CHANGELOG: 18-10-2013 : Aggiunto i bolli.
			 15-10-2013 : Bug fix su generate-fast-document
			 08-10-2013 : Sistemata funzione updateTotals e updateVatRegister.
			 30-09-2013 : Aggiornato per lo sconto incondizionato.
			 24-09-2013 : Bug fix.
			 21-09-2013 : Creata funzione updateTotals (fix-totals) per problemi con rit. enasarco,rit.acconto, ecc...
			 20-09-2013 : Aggiornata funzione getfullinfo.
			 10-09-2013 : Aggiornata funzione generate-fast-document.
			 22-08-2013 : Aggiunta funzione generate-fast-document per creare documenti al volo con molti elementi.
			 13-08-2013 : Risolto problema su funzione commercialdocs_generateVendorOrders.
			 31-07-2013 : Aggiunto il lotto di produzione.
			 27-07-2013 : Aggiunto possibilità di creare automaticamente bolle di movim. interna.
			 24-07-2013 : Bug fix with convert and group (non manteneva le 3 scontistiche, ma solamente la prima)
			 04-05-2013 : Aggiunto funzioni per gestire i messaggi predefiniti.
			 25-01-2013 : Aggiunto possibilità di convertire da altri tipi di documenti.
			 13-01-2013 : Bug fix assign group at every new documents.
 #DEPENDS: 
 #TODO: Rimane da risolvere quel problema sulla funzione commercialdocs_generateVendorOrders perchè se un ordine viene confermato automaticamente inserisce la giusta quantità da ordinare a fornitore, e fin qui ci siamo, però se quell'ordine poi viene modificato (es: invece che ordinare 5 articoli, ne vuole 3) non aggiorna la quantità giusta, anzi, ne aggiunge altri 3 da ordinare perchè il sistema non traccia gli ordini fornitore con gli ordini clienti.

 #TODO-2: Aggiungere serial-number su funzione commercialdocs_uploadGoodsDelivered
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_commercialdocs($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'group' : return commercialdocs_group($args, $sessid, $shellid); break;
  case 'generate-vendor-orders' : return commercialdocs_generateVendorOrders($args, $sessid, $shellid); break;
  case 'generate-fast-document' : return commercialdocs_generateFastDocument($args, $sessid, $shellid); break;
  case 'convert' : return commercialdocs_convert($args, $sessid, $shellid); break;
  case 'book-articles' : return commercialdocs_bookArticles($args, $sessid, $shellid); break;
  case 'upload-goods-delivered' : return commercialdocs_uploadGoodsDelivered($args, $sessid, $shellid); break;
  case 'new-predefmsg' : return commercialdocs_newPredefMsg($args, $sessid, $shellid); break;
  case 'edit-predefmsg' : return commercialdocs_editPredefMsg($args, $sessid, $shellid); break;
  case 'delete-predefmsg' : return commercialdocs_deletePredefMsg($args, $sessid, $shellid); break;
  case 'predefmsg-list' : return commercialdocs_predefMsgList($args, $sessid, $shellid); break;

  case 'getfullinfo' : case 'get-full-info' : return commercialdocs_getFullInfo($args, $sessid, $shellid); break;
  case 'updatetotals' : return commercialdocs_updateTotals($args, $sessid, $shellid); break;
  case 'fix-totals' : return commercialdocs_fixTotals($args, $sessid, $shellid); break;

  default : return commercialdocs_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_group($args, $sessid, $shellid)
{
 $_IDS = array();
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$_IDS[]=$args[$c+1]; $c++;} break; // ID dei documenti da raggruppare //
  }

 if(!count($_IDS))
  return array("message"=>"You must specify at least one document to be grouped.", "error"=>"INVALID_DOCUMENT_ID");

 $docId = 0;

 for($c=0; $c < count($_IDS); $c++)
 {
  $ret = GShell("dynarc item-info -ap `commercialdocs` -id `".$_IDS[$c]."` -extget `cdinfo,cdelements`",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $docInfo = $ret['outarr'];
  if($c==0)
  {
   $ret = GShell("dynarc new-item -ap `commercialdocs` -ct `INVOICES` -set `tag='DEFERRED'` -group commdocs-invoices -extset `cdinfo.subjectid='".$docInfo['subject_id']."',subject='''".$docInfo['subject_name']."'''`",$sessid,$shellid);
   if($ret['error'])
	return $ret;
   $docId = $ret['outarr']['id'];
   $outArr = $ret['outarr'];
  }

  GShell("dynarc edit-item -ap `commercialdocs` -id `".$docId."` -extset `cdelements.type='note',desc='''Rif. ".$docInfo['name']."'''`");
  for($i=0; $i < count($docInfo['elements']); $i++)
  {
   $el = $docInfo['elements'][$i];
   GShell("dynarc edit-item -ap `commercialdocs` -id `".$docId."` -extset `cdelements.type='".$el['type']."',refap='".$el['ref_ap']."',refid='"
	.$el['ref_id']."',code='".$el['code']."',sn='".$el['serialnumber']."',lot='".$el['lot']."',accountid='".$el['account_id']."',name='''"
	.$el['name']."''',desc='''".$el['desc']."''',qty='".$el['qty']."',price='".$el['price']."',discount='".$el['discount']."',discount2='"
	.$el['discount2']."',discount3='".$el['discount3']."',vatrate='".$el['vatrate']."',vatid='".$el['vatid']."',vattype='"
	.$el['vattype']."',units='".$el['units']."',pricelistid='".$el['pricelist_id']."'`");
  }
 }

 /* Update documents status */
 for($c=0; $c < count($_IDS); $c++)
 {
  GShell("dynarc edit-item -ap `commercialdocs` -id `".$_IDS[$c]."` -extset `cdinfo.group-id='".$docId."',status='9'`",$sessid,$shellid);
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_generateVendorOrders($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   default : $tmp=$args[$c]; break;
  }

 $elements = array();
 $vendorElements = array();

 /* Raggruppa gli elementi in base al tipo di archivio */
 $x = explode(",",$tmp);
 for($c=0; $c < count($x); $c++)
 {
  $xx = explode(":",$x[$c]);
  $ap = $xx[0];
  $xxx = explode("X",$xx[1]);
  $id = $xxx[0];
  $qty = $xxx[1];

  if(!$elements[$ap])
   $elements[$ap] = array();
  $elements[$ap][] = array('id'=>$id, 'qty'=>$qty);
 }

 /* Raggruppa gli elementi in base al fornitore */
 while(list($k,$v) = each($elements))
 {
  for($c=0; $c < count($v); $c++)
  {
   $db = new AlpaDatabase();
   $db2 = new AlpaDatabase();
   $db->RunQuery("SELECT code,vendor_id,vendor_name,price,vatrate FROM dynarc_".$k."_vendorprices WHERE item_id='".$v[$c]['id']."' ORDER BY id ASC LIMIT 1");
   if(!$db->Read())
   {
    $excluded[] = array('ap'=>$k, 'id'=>$v[$c]['id'], 'qty'=>$v[$c]['qty']);
    $db->Close();
    continue;
   }
   if(!$db->record['vendor_name'])
   {
    $excluded[] = array('ap'=>$k, 'id'=>$v[$c]['id'], 'qty'=>$v[$c]['qty']);
    $db->Close();
    continue;
   }
   $vendorName = $db->record['vendor_name'];
   // get item info //
   $db2->RunQuery("SELECT name FROM dynarc_".$k."_items WHERE id='".$v[$c]['id']."'");
   $db2->Read();

   if(!$vendorElements[$vendorName])
    $vendorElements[$vendorName] = array();
   $vendorElements[$vendorName][] = array('ap'=>$k, 'id'=>$v[$c]['id'], 'qty'=>$v[$c]['qty'], 'code'=>$db->record['code'], 'price'=>$db->record['price'], 'vat'=>$db->record['vatrate'], 'name'=>$db2->record['name']);
   $db2->Close(); $db->Close();
  }
 }

 /* Inserisce gli elementi per ogni fornitore verificando se esiste già un ordine fornitore aperto oppure se è necessario crearne uno nuovo */
 while(list($k,$v) = each($vendorElements))
 {
  $ret = GShell("dynarc item-find -ap commercialdocs -ct vendororders -field subject_name `".$k."` -where `status=0` -limit 1",$sessid,$shellid);
  if($ret['error'] || !count($ret['outarr']['items']))
  {
   // crea un nuovo documento //
   $ret = GShell("dynarc new-item -ap commercialdocs -ct vendororders -group commdocs-vendororders -extset `cdinfo.subject='''".$k."'''`",$sessid,$shellid);
   if($ret['error'])
	return $ret;
   $docInfo = $ret['outarr'];
  }
  else
  {
   $docInfo = $ret['outarr']['items'][0];
   $docInfo['already_exists'] = true;
  }

  for($c=0; $c < count($v); $c++)
  {
   $itm = $v[$c];
   if($docInfo['already_exists'])
   {
    // se l'articolo è già stato inserito aggiorna solo la quantità 
    $db = new AlpaDatabase();
	$db->RunQuery("SELECT id,qty FROM dynarc_commercialdocs_elements WHERE item_id='".$docInfo['id']."' AND ref_ap='".$itm['ap']."' AND ref_id='"
		.$itm['id']."' LIMIT 1");
	if($db->Read())
	{
	 $db->RunQuery("UPDATE dynarc_commercialdocs_elements SET qty='".$itm['qty']."' WHERE id='".$db->record['id']."'");
	 $db->Close();
	 continue;
	}
	$db->Close();
   }
   $ret = GShell("dynarc edit-item -ap commercialdocs -id `".$docInfo['id']."` -extset `cdelements.type='article',refap='".$itm['ap']."',refid='"
	.$itm['id']."',code='".$itm['code']."',name='".$itm['name']."',qty='".$itm['qty']."',price='".$itm['price']."',vat='".$itm['vat']."'`",$sessid,$shellid);
   if($ret['error'])
	return $ret;
  }
 }

 $out.= "done!";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_generateFastDocument($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-type' : case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-status' : {$status=$args[$c+1]; $c++;} break;
   case '-xml' : {$xmlData=$args[$c+1]; $c++;} break; // import from xml data
   case '-subject' : case '-subjectid' : {$subjectId=$args[$c+1]; $c++;} break;
   case '--download-from-store' : {$downloadFromStoreId=$args[$c+1]; $c++;} break;
   default : $tmp=$args[$c]; break; // ap : id X qty , ...
  }

 $elements = array();

 if($tmp)
 {
  $x = explode(",",$tmp);
  for($c=0; $c < count($x); $c++)
  {
   $xx = explode(":",$x[$c]);
   $ap = $xx[0];
   $xxx = explode("X",$xx[1]);
   $id = $xxx[0];
   $qty = $xxx[1];

   $elements[] = array('ap'=>$ap,'id'=>$id, 'qty'=>$qty);
  }
 }
 else if($xmlData)
 {
  $xmlData = ltrim(rtrim($xmlData));
  $xml = new GXML();
  if($xml->LoadFromString("<xml>".$xmlData."</xml>"))
  {
   $list = $xml->GetElementsByTagName("item");
   for($c=0; $c < count($list); $c++)
	$elements[] = $list[$c]->toArray();
  }
 }

 /* Crea il documento */
 $out.= "Creating document...";
 $command = "dynarc new-item -ap `commercialdocs`";
 if($catId)
  $command.= " -cat `".$catId."`";
 else if($catTag)
  $command.= " -ct `".$catTag."`";
 if($group)
  $command.= " -group `".$group."`";

 $_cdinfo = "";
 if($subjectId)
  $_cdinfo.= ",subjectid='".$subjectId."'";
 if($status)
  $_cdinfo.= ",status='".$status."'";

 if($_cdinfo)
  $command.= " -extset `cdinfo.".ltrim($_cdinfo,",")."`";

 $ret = GShell($command,$sessid,$shellid);
 if($ret['error']){$out.= "failed!\n".$ret['message']; return array('message'=>$out,'error'=>$ret['error']); }
 $docInfo = $ret['outarr'];
 $out.= "done!\n";

 $out.= "Import elements...";
 for($c=0; $c < count($elements); $c++)
 {
  $el = $elements[$c];
  if($el['ap'] && $el['id'])
  {
   $ret = GShell("commercialdocs get-full-info -type gmart -ap `".$el['ap']."` -id `".$el['id']."`",$sessid,$shellid);
   if($ret['error'])
    return $ret;
   $itemInfo = $ret['outarr'];

   $qry = ",refap='".$el['ap']."'";
   $qry.= ",refid='".$el['id']."'";
   $qry.= ",code='".($el['code'] ? $el['code'] : $itemInfo['code_str'])."'";
   $qry.= ",name='''".($el['name'] ? $el['name'] : $itemInfo['name'])."'''";
   $qry.= ",qty='".$el['qty']."'";
   $qry.= ",price='".($el['price'] ? $el['price'] : $itemInfo['finalprice'])."'";
   $qry.= ",vatid='".($el['vatid'] ? $el['vatid'] : $itemInfo['vatid'])."'";
   $qry.= ",vattype='".($el['vattype'] ? $el['vattype'] : $itemInfo['vattype'])."'";
   $qry.= ",vat='".(isset($el['vat']) ? $el['vat'] : $itemInfo['vat'])."'";
   if($el['discount'])
    $qry.= ",discount='".$el['discount']."'";

   $ret = GShell("dynarc edit-item -ap `commercialdocs` -id `".$docInfo['id']."` -extset `cdelements.type='article'".$qry."`",$sessid,$shellid);
  }
  else
  {
   $ret = GShell("dynarc edit-item -ap `commercialdocs` -id `".$docInfo['id']."` -extset `cdelements.type='article',name='''".$el['name']."''',qty='".$el['qty']."',price='".$el['price']."',vat='".$el['vat']."',discount='".$el['discount']."'`",$sessid,$shellid);
  }
  if($ret['error'])
   return $ret;

  // scarico automatico del magazzino //
  if($el['ap'] && $el['id'] && $downloadFromStoreId)
   GShell("store download -ap `".$el['ap']."` -id `".$el['id']."` -qty `".$el['qty']."` -store `".$downloadFromStoreId."` -docap commercialdocs -docid `"
	.$docInfo['id']."`",$sessid,$shellid);
 }
 $out.= "done!\n".count($elements)." elements has been inserted!";

 $out.= "\nUpdating totals...";
 $ret = GShell("commercialdocs updatetotals -id '".$docInfo['id']."'",$sessid,$shellid);
 if($ret['error'])
 {
  $out.= "failed\n".$ret['message'];
  $outArr = $docInfo;
 }
 else
 {
  $out.= "\ndone!\n".$ret['message'];
  $outArr = $ret['outarr'];
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_convert($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-type' : {$type=$args[$c+1]; $c++;} break;
   case '-status' : {$status=$args[$c+1]; $c++;} break;

   /* options for convert from other document kinds */
   case '-from' : {$from=$args[$c+1]; $c++;} break; // <-- specify archive prefix
  }

 if(!$id)
  return array('message'=>"Convert failed! You must specify the ID of the document to be convert. (with -id DOCUMENT_ID)",'error'=>"INVALID_DOCUMENT");

 if(!$type)
  return array('message'=>"Convert failed! You must specify the type of document to be convert to. (with -type DOCUMENT_TYPE)",'error'=>"INVALID_DOCUMENT_TYPE");

 switch($from)
 {
  case 'contracts' : return commercialdocs_convertFromContract($args, $sessid, $shellid); break;
 }


 /* CONVERT FROM COMMERCIALDOCS DOCUMENTS */
 $ret = GShell("dynarc item-info -ap commercialdocs -id `".$id."` -extget `cdinfo,cdelements`",$sessid, $shellid);
 if($ret['error'])
  return array('message'=>"Convert failed! ".$ret['message'], $ret['error']);

 $docInfo = $ret['outarr'];
 $destCatId = 0;

 if($docInfo['code_ext'])
 {
  // verifica se esiste una cartella di destinazione con lo stesso tag //
  $ret = GShell("dynarc cat-info -ap commercialdocs -pt '".strtoupper($type)."' -tag '".$docInfo['code_ext']."'",$sessid,$shellid);
  if(!$ret['error'])
   $destCatId = $ret['outarr']['id'];
 }

 $query = "dynarc new-item -ap `commercialdocs` ".($destCatId ? "-cat '".$destCatId."'" : "-ct `".strtoupper($type)."`")
	." -group commdocs-".strtolower($type)." -extset `cdinfo.subjectid='".$docInfo['subject_id']."'";
 if($docInfo['payment_date'] != "0000-00-00")
  $query.= ",payment-date='".$docInfo['payment_date']."',status='".($status ? $status : "10")."'";
 else if($status)
  $query.= ",status='".$status."'";
 $query.= ",subject='''".$docInfo['subject_name']."'''`";

 $ret = GShell($query,$sessid,$shellid);

 if($ret['error'])
  return $ret;

 $docId = $ret['outarr']['id'];
 $outArr = $ret['outarr'];

 for($i=0; $i < count($docInfo['elements']); $i++)
 {
  $el = $docInfo['elements'][$i];
  GShell("dynarc edit-item -ap `commercialdocs` -id `".$docId."` -extset `cdelements.type='".$el['type']."',refap='".$el['ref_ap']."',refid='"
	.$el['ref_id']."',code='".$el['code']."',sn='".$el['serialnumber']."',lot='".$el['lot']."',accountid='".$el['account_id']."',name='''"
	.$el['name']."''',desc='''".$el['desc']."''',qty='".$el['qty']."',price='".$el['price']."',discount='".$el['discount']."',discount2='"
	.$el['discount2']."',discount3='".$el['discount3']."',vatrate='".$el['vatrate']."',vatid='".$el['vatid']."',vattype='"
	.$el['vattype']."',units='".$el['units']."',pricelistid='".$el['pricelist_id']."',bypass-vatregister-update=true`");
 }
 
 $out.= "The document has been converted! ID=".$docId;

 $out.= "\nUpdating totals...";
 $ret = GShell("commercialdocs updatetotals -id '".$docId."'",$sessid,$shellid);
 if($ret['error'])
  $out.= "failed\n".$ret['message'];
 else
 {
  $out.= "\ndone!\n".$ret['message'];
  $outArr = $ret['outarr'];
 }


 /* change doc status and remove vat register records */
 $ret = GShell("dynarc edit-item -ap commercialdocs -id `".$docInfo['id']."` -extset `cdinfo.conv-id='".$docId."',status='8'` && vatregister delete -year `".date('Y',$docInfo['ctime'])."` -docap `commercialdocs` -docid `".$docInfo['id']."`",$sessid,$shellid);
 
 /* EOF - CONVERT FROM COMMERCIALDOCS DOCUMENTS */

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_bookArticles($args, $sessid, $shellid)
{
 $_AP = "commercialdocs";
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$id=$args[$c+1]; $c++;} break;
  }

 if(!$id)
  return array('message'=>"You must specify the document ID. (with -id DOC_ID)", 'error'=>"INVALID_DOCUMENT");
 $ret = GShell("dynarc item-info -ap `".$_AP."` -id `".$id."` -extget cdelements",$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $docInfo = $ret['outarr'];

 $count = 0;
 $db = new AlpaDatabase();
 for($c=0; $c < count($docInfo['elements']); $c++)
 {
  $itm = $docInfo['elements'][$c];
  if($itm['type'] != "article")
   continue;
  $db->RunQuery("UPDATE dynarc_".$itm['ref_ap']."_items SET booked=booked+".$itm['qty']." WHERE id='".$itm['ref_id']."'");
  $count++;
 }
 $db->Close();

 return array('message'=>$count." articles has been booked!");
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_uploadGoodsDelivered($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array("storeid"=>0, "items"=>array());
 $docAP = "commercialdocs";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-store' : {$storeId=$args[$c+1]; $c++;} break;
   case '-refap' : {$docAP=$args[$c+1]; $c++;} break;
   case '-refid' : {$docID=$args[$c+1]; $c++;} break;
   case '--auto-gen-ddt' : $autoGenerateDDT = true; break;
   default : $tmp = $args[$c]; break;
  }

 $outArr['storeid'] = $storeId;

 $ddtID = 0;

 if($autoGenerateDDT)
 {
  // get store name //
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT name FROM stores WHERE id='".$storeId."'");
  $db->Read();
  $storeName = $db->record['name'];
  $db->Close();


  $ret = GShell("dynarc new-item -ap commercialdocs -ct DDT -group commdocs-ddt -set `tag='INTERNAL'` -extset `cdelements.type='note',desc='''Materiale da movimentare a magazzino di ".$storeName."'''`",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $ddtInfo = $ret['outarr'];
  $ddtID = $ddtInfo['id'];
  $outArr['ddtinfo'] = $ddtInfo;
 }

 /* Raggruppa gli elementi in base al tipo di archivio */
 $x = explode(",",$tmp);
 for($c=0; $c < count($x); $c++)
 {
  $xx = explode(":",$x[$c]);
  $ap = $xx[0];
  $xxx = explode("X",$xx[1]);
  $id = $xxx[0];
  if(strpos($xxx[1], "~") !== false)
  {
   $xxxx = explode("~",$xxx[1]);
   $qty = $xxxx[0];
   $lot = $xxxx[1];
  }
  else
  {
   $qty = $xxx[1];
   $lot = "";
  }

  $ret = GShell("store upload -store ".$storeId." -ap `".$ap."` -id `".$id."` -qty `".$qty."` -lot `".$lot."` -docap `".$docAP."` -docid `".$docID."`",$sessid,$shellid);
  if($ret['error'])
   $out.= "Warning:".$ret['message']."\n";
  else
   $outArr['items'][] = array('ap'=>$ap, 'id'=>$id, 'qty'=>$qty);

  if($ddtID)
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT * FROM dynarc_".$ap."_items WHERE id='".$id."'");
   $db->Read();
   $ret = GShell("dynarc edit-item -ap `commercialdocs` -id `".$ddtID."` -extset `cdelements.type='article',refap='".$ap."',refid='"
	.$id."',code='".$db->record['code_str']."',name='''".$db->record['name']."''',desc='''".$db->record['description']."''',qty='"
	.$qty."',lot='".$lot."',price='".$db->record['baseprice']."',vatrate='".$db->record['vat']."',units='".$db->record['units']."'`");
   $db->Close();
  }
 }
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_convertFromContract($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-type' : {$type=$args[$c+1]; $c++;} break;
   case '-status' : {$status=$args[$c+1]; $c++;} break;
  }

 /* CONVERT FROM CONTRACT */
 $ret = GShell("dynarc item-info -ap contracts -id `".$id."` -extget `contractinfo,contractelements`",$sessid, $shellid);
 if($ret['error'])
  return array('message'=>"Convert failed! ".$ret['message'], $ret['error']);

 $docInfo = $ret['outarr'];

 $query = "dynarc new-item -ap `commercialdocs` -ct `".strtoupper($type)."` -group commdocs-".strtolower($type)." -extset `cdinfo.subjectid='".$docInfo['subject_id']."'";
 if($status)
  $query.= ",status='".$status."'";
 $query.= ",subject='''".$docInfo['subject']."'''`";

 $ret = GShell($query,$sessid,$shellid);

 if($ret['error'])
  return $ret;

 $docId = $ret['outarr']['id'];
 $outArr = $ret['outarr'];

 for($i=0; $i < count($docInfo['elements']); $i++)
 {
  $el = $docInfo['elements'][$i];
  GShell("dynarc edit-item -ap `commercialdocs` -id `".$docId."` -extset `cdelements.type='".$el['type']."',refap='".$el['ref_ap']."',refid='"
	.$el['ref_id']."',code='".$el['code']."',sn='".$el['serialnumber']."',lot='".$el['lot']."',accountid='".$el['account_id']."',name='''"
	.$el['name']."''',desc='''".$el['desc']."''',qty='".$el['qty']."',price='".$el['price']."',discount='".$el['discount']."',vatrate='"
	.$el['vatrate']."',vatid='".$el['vatid']."',vattype='".$el['vattype']."',units='".$el['units']."',pricelistid='"
	.$el['pricelist_id']."',bypass-vatregister-update=true`");
 }
 
 $out.= "The document has been converted! ID=".$docId;

 $out.= "\nUpdating totals...";
 $ret = GShell("commercialdocs updatetotals -id '".$docId."'",$sessid,$shellid);
 if($ret['error'])
  $out.= "failed\n".$ret['message'];
 else
 {
  $out.= "\ndone!\n".$ret['message'];
  $outArr = $ret['outarr'];
 }


 /* change some stuff */
 $ret = GShell("dynarc edit-item -ap contracts -id `".$docInfo['id']."` -extset `contractinfo.conv-id='".$docId."'`",$sessid,$shellid);
 
 /* EOF - CONVERT FROM CONTRACT */

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
//--------PREDEFINED MESSAGES----------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_newPredefMsg($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-text' : case '-desc' : {$text=$args[$c+1]; $c++;} break;
   default : $text=$args[$c]; break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO dynarc_commercialdocs_predefmsg(description) VALUES('".$db->Purify($text)."')");
 $id = mysql_insert_id();
 $db->Close();

 $out.= "done! ID=".$id;
 $outArr = array('id'=>$id);
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_editPredefMsg($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-text' : case '-desc' : {$text=$args[$c+1]; $c++;} break;
   default : $text=$args[$c]; break;
  }

 if(!$id)
  return array('message'=>"You must specify a message ID. (with: -id MSG_ID)", "error"=>"INVALID_MESSAGE_ID");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_commercialdocs_predefmsg WHERE id='".$id."'");
 if(!$db->Read())
  return array('message'=>"Message #".$id." does not exists.", "error"=>"MESSAGE_DOES_NOT_EXISTS");

 $db->RunQuery("UPDATE dynarc_commercialdocs_predefmsg SET description='".$db->Purify($text)."' WHERE id='".$id."'");
 $db->Close();

 $out.= "done! Message #".$id." has been updated!";
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_deletePredefMsg($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
  }

 if(!$id)
  return array('message'=>"You must specify a message ID. (with: -id MSG_ID)", "error"=>"INVALID_MESSAGE_ID");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_commercialdocs_predefmsg WHERE id='".$id."'");
 if(!$db->Read())
  return array('message'=>"Message #".$id." does not exists.", "error"=>"MESSAGE_DOES_NOT_EXISTS");
 $db->RunQuery("DELETE FROM dynarc_commercialdocs_predefmsg WHERE id='".$id."'");
 $db->Close();

 $out.= "done! Message #".$id." has been removed!";
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_predefMsgList($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $orderBy = "id ASC";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '--verbose' : case '-verbose' : $verbose=true; break;
  }

 if($verbose) 
  $out = "List of predefined messages:\n";

 $idx = 1;
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_commercialdocs_predefmsg WHERE 1 ORDER BY ".$orderBy);
 while($db->Read())
 {
  $a = array('id'=>$db->record['id'], 'text'=>$db->record['description']);
  $outArr[] = $a;
  if($verbose)
   $out.= $idx.") ".$a['text']."\n";
  $idx++;
 }
 $db->Close();

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_getFullInfo($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $pricelistID = 0;
 $pricelistName = "";
 $subjectID = 0;
 $subjectName = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-type' : {$type=$args[$c+1]; $c++;} break;
   case '-at' : {$_AT=$args[$c+1]; $c++;} break;
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;
   case '-code' : {$code=$args[$c+1]; $c++;} break;
   case '-barcode' : {$barcode=$args[$c+1]; $c++;} break;
   case '--code-or-barcode' : {$codeOrBarcode=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-subjectid' : {$subjectID=$args[$c+1]; $c++;} break;
   case '-pricelistid' : {$pricelistID=$args[$c+1]; $c++;} break;
   case '-get' : {$extraGet=$args[$c+1]; $c++;} break;

   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 if($subjectID)
 {
  /* returns the price list associated with this customer (or any other subject) */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT name,pricelist_id FROM dynarc_rubrica_items WHERE id='".$subjectID."'");
  if(!$db->Read())
   $out.= "Subject #".$subjectID." not found.\n";
  else
  {
   $subjectName = $db->record['name'];
   if(!$pricelistID)
    $pricelistID = $db->record['pricelist_id'];
  }
  $db->Close();
 }


 if(!$pricelistID)
 {
  $out.= "Checking for pricelist...";
  // se nessun listino prezzi viene specificato, allora viene utilizzato quello predefinito
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id,name,markuprate,vat FROM pricelists WHERE isdefault=1 LIMIT 1");
  if(!$db->Read())
  {
   $db->RunQuery("SELECT id,name,markuprate,vat FROM pricelists WHERE 1 ORDER BY id ASC LIMIT 1");
   if(!$db->Read())
	$out.= "no pricelists found!\n";
   else
   {
    $pricelistID = $db->record['id'];
	$pricelistName = $db->record['name'];
    $out.= "no default pricelist found. I will use the first pricelist that I find\n";
   }
  }
  else
  {
   $pricelistID = $db->record['id'];
   $pricelistName = $db->record['name'];
   $out.= "no pricelist set. I will use the default pricelist\n";
  }
  $db->Close();
 }
 else
 {
  // get pricelist name
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT name FROM pricelists WHERE id='".$pricelistID."'");
  $db->Read();
  $pricelistName = $db->record['name'];
  $db->Close();
 }

 if($type && !$_ID)
 {
  $ret = GShell("dynarc archive-list -a -type `".$type."`",$sessid,$shellid);
  if($ret['error'])
   return $ret;

  $db = new AlpaDatabase();
  for($i=0; $i < count($ret['outarr']); $i++)
  {
   $ap = $ret['outarr'][$i]['prefix'];
   $query = "SELECT id FROM dynarc_".$ap."_items WHERE ";
   if($code)
	$query.= "code_str='".$code."'";
   else if($name)
	$query.= "name='".$db->Purify($name)."'";
   else if($barcode)
	$query.= "barcode='".$barcode."'";
   else if($codeOrBarcode)
	$query.= "(barcode='".$codeOrBarcode."' OR code_str='".$codeOrBarcode."')";
   $db->RunQuery($query." AND trash='0' ORDER BY id DESC LIMIT 1");
   if($db->Read())
   {
    $_AP = $ap;
	$_ID = $db->record['id'];
   }
  }
  $db->Close();
  if(!$_ID)
   return array('message'=>"No item found with ".($code ? "code '".$code."'" : ($barcode ? "barcode '".$barcode."'" : "name '".$name."'")), 'error'=>"ITEM_NOT_FOUND");
 }

 if($_AP && $_ID)
 {
  // get archive type
  switch($_AP)
  {
   case 'gmart' : $_AT = "gmart"; break;
   case 'gserv' : $_AT = "gserv"; break;
   case 'gsupplies' : $_AT = "gsupplies"; break;
   default : {
	 $db = new AlpaDatabase();
	 $db->RunQuery("SELECT archive_type FROM dynarc_archives WHERE tb_prefix='".$_AP."'");
	 $db->Read();
	 $_AT = $db->record['archive_prefix'];
	 $db->Close();
	} break;
  }

  $get = "";
  if($pricelistID)
   $get.= ",pricelist_".$pricelistID."_baseprice,pricelist_".$pricelistID."_mrate,pricelist_".$pricelistID."_vat,pricelist_".$pricelistID."_discount";
  if($_AT == "gserv")
   $get.= ",service_type";
  if($extraGet)
   $get.= ",".$extraGet;

  $command = "dynarc item-info -ap `".$_AP."` -id `".$_ID."` -extget `".$_AT.",pricing";
  if($subjectID)
   $command.= ",custompricing.subjectid=".$subjectID;
  $command.= "`".($get ? " -get `".ltrim($get,",")."`" : "");
  $ret = GShell($command,$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $itemInfo = $ret['outarr'];
 }
 else
  return array('message'=>"You must specify archive prefix and item id, or item type and search by code/name.","error"=>"UNDEFINED_ITEM");

 /* CALCULATE PRICING */
 $markupRate = 0;
 $vat = 0;
 if($itemInfo['custompricing'])
 {
  $basePrice = isset($itemInfo['custompricing']['baseprice']) ? $itemInfo['custompricing']['baseprice'] : 0;
  $finalPrice = $basePrice ? $basePrice : 0;
  $vat = $itemInfo['vat'];
 }
 else if($pricelistID)
 {
  $basePrice = $itemInfo["pricelist_".$pricelistID."_baseprice"] ? $itemInfo["pricelist_".$pricelistID."_baseprice"] : $itemInfo['baseprice'];
  $markupRate = $itemInfo["pricelist_".$pricelistID."_mrate"] ? $itemInfo["pricelist_".$pricelistID."_mrate"] : 0;
  $discount = $itemInfo["pricelist_".$pricelistID."_discount"] ? $itemInfo["pricelist_".$pricelistID."_discount"] : 0;
  $finalPrice = $basePrice ? $basePrice + (($basePrice/100)*$markupRate) : 0;
  $finalPrice = $finalPrice ? $finalPrice - (($finalPrice/100)*$discount) : 0;
  $vat = $itemInfo["pricelist_".$pricelistID."_vat"] ? $itemInfo["pricelist_".$pricelistID."_vat"] : $itemInfo['vat'];
 }
 else
 {
  $basePrice = $itemInfo['baseprice'];
  $finalPrice = $basePrice;
  $vat = $itemInfo['vat'];
 }

 $itemInfo['baseprice'] = $basePrice;
 $itemInfo['finalprice'] = $finalPrice;
 $itemInfo['vat'] = $vat;
 $itemInfo['finalpricevatincluded'] = $itemInfo['finalprice'] ? $itemInfo['finalprice']+(($itemInfo['finalprice']/100)*$itemInfo['vat']) : 0;

 /* GET VAT TYPE AND VAT ID */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_vatrates_items WHERE percentage='".$itemInfo['vat']."' AND vat_type='TAXABLE' AND trash='0' LIMIT 1");
 if(!$db->Read())
 {
  $db->RunQuery("SELECT vat_type,id FROM dynarc_vatrates_items WHERE percentage='".$itemInfo['vat']."' AND trash='0' LIMIT 1");
  if($db->Read())
  {
   $itemInfo['vatid'] = $db->record['id'];
   $itemInfo['vattype'] = $db->record['vat_type'];
  }
 }
 else
 {
  $itemInfo['vatid'] = $db->record['id'];
  $itemInfo['vattype'] = "TAXABLE";
 }
 $db->Close();

 $outArr = $itemInfo;
 $outArr['tb_prefix'] = $_AP;

 if($verbose)
 {
  if($pricelistID && $pricelistName)
   $out.= "Pricelist: ".$pricelistName."\n";
  if($subjectID && $subjectName)
   $out.= "Subject: ".$subjectName."\n";

  $out.= "Code: ".$itemInfo['code_str']."\n";
  $out.= "Name: ".$itemInfo['name']."\n";
  $out.= "Base price: ".number_format($itemInfo['baseprice'],2,",",".")."\n";

  if(isset($itemInfo['custompricing']))
  {
   if($itemInfo['custompricing']['discount_perc'])
    $out.= "Discount : ".$itemInfo['custompricing']['discount_perc']."%\n";
   if($itemInfo['custompricing']['discount2'])
    $out.= "Discount 2: ".$itemInfo['custompricing']['discount2']."%\n";
   if($itemInfo['custompricing']['discount3'])
    $out.= "Discount 3: ".$itemInfo['custompricing']['discount3']."%\n";
  }
  if($markupRate)
   $out.= "Markup rate: ".$markupRate."%\n";
  if($discount)
   $out.= "Global discount: ".$discount."%\n";

  $out.= "Final price: ".number_format($itemInfo['finalprice'],2,",",".")."\n";
  $out.= "VAT: ".$itemInfo['vat']."%\n";
  $out.= "End price: ".number_format($itemInfo['finalpricevatincluded'],2,",",".")." (VAT included)\n";
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_updateTotals($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_COMPANY_PROFILE;
 include_once($_BASE_PATH."include/company-profile.php");

 $out = "";
 $outArr = array();

 $_AP = "commercialdocs";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 if(!$_ID)
  return array("message"=>"You must specify the document ID","error"=>"INVALID_DOCUMENT");

 /* GET DOCUMENT INFO */
 $ret = GShell("dynarc item-info -ap `".$_AP."` -id '".$_ID."' -extget `cdinfo`",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 $docInfo = $ret['outarr'];

 /* GET CAT TAG */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT tag,parent_id FROM dynarc_".$_AP."_categories WHERE id='".$docInfo['cat_id']."'");
 if($db->Read())
 {
  if($db->record['parent_id'])
  {
   $db->RunQuery("SELECT tag FROM dynarc_".$_AP."_categories WHERE id='".$db->record['parent_id']."'");
   $db->Read();
   $_CAT_TAG = $db->record['tag']; 
  }
  else
   $_CAT_TAG = $db->record['tag'];
 }
 $db->Close();

 /*----------------------------------------------------------------------------*/
 $_RIVALSA_INPS = $_COMPANY_PROFILE['accounting']['rivalsa_inps'] ? $_COMPANY_PROFILE['accounting']['rivalsa_inps'] : 0;
 $_RIT_ENASARCO = $_COMPANY_PROFILE['accounting']['rit_enasarco'] ? $_COMPANY_PROFILE['accounting']['rit_enasarco'] : 0;
 $_RIT_ENASARCO_PERCIMP = $_COMPANY_PROFILE['accounting']['rit_enasarco_percimp'] ? $_COMPANY_PROFILE['accounting']['rit_enasarco_percimp'] : 0;

 $_CASSA_PREV = $_COMPANY_PROFILE['accounting']['contr_cassa_prev'] ? $_COMPANY_PROFILE['accounting']['contr_cassa_prev'] : 0;
 $_CASSA_PREV_VATID = $_COMPANY_PROFILE['accounting']['contr_cassa_prev_vatid'] ? $_COMPANY_PROFILE['accounting']['contr_cassa_prev_vatid'] : 0;
 $_CASSA_PREV_VAT_RATE = 0;
 $_CASSA_PREV_VAT_TYPE = "";
 if($_CASSA_PREV_VATID)
 {
  // get vat type //
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT vat_type,percentage FROM dynarc_vatrates_items WHERE id='".$_CASSA_PREV_VATID."'");
  $db->Read();
  $_CASSA_PREV_VAT_TYPE = $db->record['vat_type'];
  $_CASSA_PREV_VAT_RATE = $db->record['percentage'];
  $db->Close();
 }

 $_RIT_ACCONTO = $_COMPANY_PROFILE['accounting']['rit_acconto'] ? $_COMPANY_PROFILE['accounting']['rit_acconto'] : 0;
 $_RIT_ACCONTO_PERCIMP = $_COMPANY_PROFILE['accounting']['rit_acconto_percimp'] ? $_COMPANY_PROFILE['accounting']['rit_acconto_percimp'] : 0;
 $_RIT_ACCONTO_RIVINPSINC = $_COMPANY_PROFILE['accounting']['rit_acconto_rivinpsinc'] ? $_COMPANY_PROFILE['accounting']['rit_acconto_rivinpsinc'] : 0;
 /*----------------------------------------------------------------------------*/
 $_VATS = array();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT elem_type,qty,extra_qty,price,price_adjust,discount_perc,discount_inc,discount2,discount3,vat_rate,vat_id,vat_type FROM dynarc_".$_AP."_elements WHERE item_id='".$docInfo['id']."'");
 while($db->Read())
 {
  if($db->record['elem_type'] == "note")
   continue;
  $qty = $db->record['qty'];
  $extraQty = $db->record['extra_qty'];
  if($extraQty)
   $qty = $qty*$extraQty;
  
  $amount = $db->record['price'];
  if(!$qty || !$amount)
   continue;
  
  $discount = 0;
  $discount2 = 0;
  $discount3 = 0;

  if($db->record['discount_perc'] && $amount)
   $discount = ($amount/100)*$db->record['discount_perc'];
  else if($db->record['discount_inc'])
   $discount = $db->record['discount_inc'];
  if($db->record['discount2'])
   $discount2 = (($amount-$discount)/100) * $db->record['discount2'];
  if($db->record['discount2'] && $db->record['discount3'])
   $discount3 = (($amount-$discount-$discount2)/100) * $db->record['discount3'];

  $amount = ((($amount-$discount)-$discount2)-$discount3) * $qty;

  if($db->record['vat_id'])
  {
   if($_VATS[$db->record['vat_id']])
	$_VATS[$db->record['vat_id']]['amount']+= $amount;
   else
	$_VATS[$db->record['vat_id']] = array("type"=>$db->record['vat_type'], "rate"=>$db->record['vat_rate'], "amount"=>$amount, "expenses"=>0);
  }
 }
 $db->Close();
 /*----------------------------------------------------------------------------*/
 $_TOTALE_MERCE = 0;
 $_TOTALE_SPESE = 0;
 $_MERCE_SCONTATA = 0;
 $_TOTALE_IMPONIBILE = 0;
 $_TOTALE_IVA = 0;
 $_SCONTO_1 = $docInfo['discount'];
 $_SCONTO_2 = $docInfo['discount2'];
 $_UNCONDITIONAL_DISCOUNT = $docInfo['uncondisc'];
 $_REBATE = $docInfo['rebate'];
 $_STAMP = $docInfo['stamp'];
 $_COEFF_RIPARTO = 0;
 $_TOTALE_RIVALSA_INPS = 0;
 $_TOTALE_RITENUTA_ACCONTO = 0;
 $_TOTALE_ENASARCO = 0;
 $_TOTALE_CASSA_PREV = 0;

 if($verbose)
 {
  $out.= "Sconto 1: ".$_SCONTO_1."%\n";
  $out.= "Sconto 2: ".$_SCONTO_2."%\n";
  $out.= "Sconto inc: ".number_format($_UNCONDITIONAL_DISCOUNT,2,",",".")." &euro;\n";
 }
 /*----------------------------------------------------------------------------*/
 // calcolo delle spese
 for($c=1; $c < 4; $c++) // limite max 3 voci di spesa
 {
  if($docInfo["exp".$c."vatid"] && $docInfo["exp".$c."amount"])
  {
   if($_VATS[$docInfo["exp".$c."vatid"]])
	$_VATS[$docInfo["exp".$c."vatid"]]['expenses']+= $docInfo["exp".$c."amount"];
   else
   {
	// detect vat-type and vat-rate //
	$db = new AlpaDatabase();
	$db->RunQuery("SELECT vat_type,percentage FROM dynarc_vatrates_items WHERE id='".$docInfo["exp".$c."vatid"]."'");
	$db->Read();
	$_VATS[$docInfo["exp".$c."vatid"]] = array("type"=>$db->record['vat_type'], "rate"=>$db->record['percentage'], "amount"=>0, "expenses"=>$docInfo["exp".$c."amount"]);
	$db->Close();
   }
  }
 } 
 /*----------------------------------------------------------------------------*/
 reset($_VATS);
 // al primo giro calcolo l'importo lordo //
 while(list($vid,$vatInfo)=each($_VATS))
 {
  $_TOTALE_MERCE+= $vatInfo['amount'];
  $_TOTALE_SPESE+= $vatInfo['expenses'];
 }
 
 // dal totale merce ricaviamo il coefficiente di riparto //
 if($_UNCONDITIONAL_DISCOUNT && $_TOTALE_MERCE)
  $_COEFF_RIPARTO = $_UNCONDITIONAL_DISCOUNT/$_TOTALE_MERCE;

 reset($_VATS);
 // al secondo giro calcolo l'importo scontato ed il totale dell'imponibile //
 while(list($vid,$vatInfo)=each($_VATS))
 {
  $value = $vatInfo['amount'];
  if($value && $_SCONTO_1)
   $value = $value - (($value/100)*$_SCONTO_1);
  if($value && $_SCONTO_2)
   $value = $value - (($value/100)*$_SCONTO_2);

  // aggiungo le spese
  $value+= $vatInfo['expenses'];

  // sottraggo il riparto
  if($_COEFF_RIPARTO)
   $value-= $vatInfo['amount']*$_COEFF_RIPARTO;

  // aggiungo la rivalsa inps
  if(($_CAT_TAG == "INVOICES") && $_RIVALSA_INPS)
  {
   $rivinps = $value ? ($value/100)*$_RIVALSA_INPS : 0;
   $_TOTALE_RIVALSA_INPS+= $rivinps;
   $value+= $rivinps;
  }


  $_TOTALE_IMPONIBILE+= $value;

  $_VATS[$vid]['discounted'] = $value;

  // calcolo l'IVA
  $vat = $value ? ($value/100)*$vatInfo['rate'] : 0;
  if($vatInfo['type'] == "TAXABLE")
   $_TOTALE_IVA+= $vat;
  $_VATS[$vid]['vat'] = $vat;
 }
 /*----------------------------------------------------------------------------*/
 // calcola la merce scontata
 if($_SCONTO_1)
  $_MERCE_SCONTATA = $_TOTALE_MERCE - (($_TOTALE_MERCE/100)*$_SCONTO_1);
 else
  $_MERCE_SCONTATA = $_TOTALE_MERCE;

 // calcola la cassa prev.
 if(($_CAT_TAG == "INVOICES") && $_CASSA_PREV)
 {
  $imp = $_MERCE_SCONTATA - $_UNCONDITIONAL_DISCOUNT;
  $_TOTALE_CASSA_PREV = $imp ? ($imp/100)*$_CASSA_PREV : 0;
 }
 /*----------------------------------------------------------------------------*/
 if($verbose)
  $out.= "Totale cassa prev.: ".number_format($_TOTALE_CASSA_PREV,2,",",".")." &euro;\n";
 // ricarico l'imponibile lordo includendo la cassa previdenziale //
 if($_CASSA_PREV_VATID)
 {
  if($_VATS[$_CASSA_PREV_VATID])
  {
   $vatInfo = $_VATS[$_CASSA_PREV_VATID];
   // aggiorno gli importi //
   $_VATS[$_CASSA_PREV_VATID]['amount'] = $vatInfo['amount']+$vatInfo['expenses']+$_TOTALE_CASSA_PREV;
   $value = $vatInfo['discounted']+$_TOTALE_CASSA_PREV;
   $_VATS[$_CASSA_PREV_VATID]['discounted'] = $vatInfo['discounted']+$_TOTALE_CASSA_PREV;
   $vat = $value ? (($value/100)*$vatInfo['rate']) : 0;
   if($vatInfo['type'] == "TAXABLE")
   {
	$_TOTALE_IVA-= $vatInfo['vat'];
	$_TOTALE_IVA+= $vat;
	$_VATS[$_CASSA_PREV_VATID]['vat'] = $vat;
   }
  }
  else
  {
   $vatInfo = array("type"=>$_CASSA_PREV_VAT_TYPE, "rate"=>$_CASSA_PREV_VAT_RATE, "amount"=>0, "expenses"=>0);
   if($_CASSA_PREV_VAT_TYPE == "TAXABLE")
   {
	$vat = $_TOTALE_CASSA_PREV ? (($_TOTALE_CASSA_PREV/100)*$_CASSA_PREV) : 0;
	$_TOTALE_IVA+= $vat;
    $vatInfo['vat'] = $vat;
   }
   $_VATS[$_CASSA_PREV_VATID] = $vatInfo;
  }
  $_TOTALE_IMPONIBILE+= $_TOTALE_CASSA_PREV;
 }
 /*----------------------------------------------------------------------------*/
 // calcolo la ritenuta d'acconto
 if(($_CAT_TAG == "INVOICES") && $_RIT_ACCONTO)
 {
  $imp = $_MERCE_SCONTATA - $_UNCONDITIONAL_DISCOUNT;
  if($_RIT_ACCONTO_RIVINPSINC)
   $imp = $imp ? ((($imp+$_TOTALE_RIVALSA_INPS)/100)*$_RIT_ACCONTO_PERCIMP) : 0;
  else
   $imp = $imp ? (($imp/100)*$_RIT_ACCONTO_PERCIMP) : 0;
  $_TOTALE_RITENUTA_ACCONTO = $imp ? ($imp/100)*$_RIT_ACCONTO : 0;
 }

 // calcolo l'enasarco
 if(($_CAT_TAG == "INVOICES") && $_RIT_ENASARCO)
 {
  $imp = $_MERCE_SCONTATA - $_UNCONDITIONAL_DISCOUNT;
  $imp = $imp ? (($imp/100)*$_RIT_ENASARCO_PERCIMP) : 0;
  $_TOTALE_ENASARCO = $imp ? ($imp/100)*$_RIT_ENASARCO : 0;
 }
 
 /* CALCOLO IL NETTO A PAGARE */
 $_NET_PAY = ($_TOTALE_IMPONIBILE+$_TOTALE_IVA) - $_TOTALE_RITENUTA_ACCONTO - $_TOTALE_ENASARCO - $_REBATE + $_STAMP;
 
 /*----------------------------------------------------------------------------*/
 reset($_VATS);
 $c=1;
 $vatsQry = "";
 while(list($vid,$vatInfo)=each($_VATS))
 {
  if($c > 3) // max 3 aliquote iva
   break;
  $vatsQry.=",vat_".$c."_id='".$vid."',vat_".$c."_taxable='".$vatInfo['discounted']."',vat_".$c."_tax='".$vatInfo['vat']."'";
  $docInfo["vat_".$c."_id"] = $vid;
  $docInfo["vat_".$c."_taxable"] = $vatInfo['discounted'];
  $docInfo["vat_".$c."_tax"] = $vatInfo['vat'];
  $c++;
 }
 if($c < 4)
 {
  while($c < 4)
  {
   $vatsQry.=",vat_".$c."_id='0',vat_".$c."_taxable='0',vat_".$c."_tax='0'";
   $docInfo["vat_".$c."_id"] = 0;
   $docInfo["vat_".$c."_taxable"] = 0;
   $docInfo["vat_".$c."_tax"] = 0;
   $c++;  
  }
 }
 /*----------------------------------------------------------------------------*/
 $_TOTALE_SCONTO = $_TOTALE_MERCE - $_MERCE_SCONTATA + $_UNCONDITIONAL_DISCOUNT;
 $docInfo['tot_discount'] = $_TOTALE_SCONTO;
 /*----------------------------------------------------------------------------*/
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$_AP."_items SET amount='".$_TOTALE_IMPONIBILE."',vat='".$_TOTALE_IVA."',total='"
	.($_TOTALE_IMPONIBILE + $_TOTALE_IVA)."',tot_rit_acc='".$_TOTALE_RITENUTA_ACCONTO."',tot_ccp='".$_TOTALE_CASSA_PREV."',tot_rinps='"
	.$_TOTALE_RIVALSA_INPS."',tot_enasarco='".$_TOTALE_ENASARCO."',tot_netpay='".$_NET_PAY."',tot_goods='".$_TOTALE_MERCE."',discounted_goods='"
	.$_MERCE_SCONTATA."',tot_expenses='".$_TOTALE_SPESE."',tot_discount='".$_TOTALE_SCONTO."'".$vatsQry." WHERE id='".$docInfo['id']."'");
 $db->Close();
 /*----------------------------------------------------------------------------*/
 $outArr = $docInfo;
 /*----------------------------------------------------------------------------*/
 if($verbose)
 {
  $out.= "Riepilogo per aliquote:\n";
  $out.= "<table><tr><th>aliq.</th><th>imp. lordo</th><th>scontato</th><th>imposta</th></tr>";
  reset($_VATS);
  while(list($vid,$vatInfo)=each($_VATS))
  {
   $out.= "<tr><td>".$vatInfo['rate']."%</td>";
   $out.= "<td>".number_format($vatInfo['amount'],2,",",".")." &euro;</td>";
   $out.= "<td>".number_format($vatInfo['discounted'],2,",",".")." &euro;</td>";
   $out.= "<td>".number_format($vatInfo['vat'],2,",",".")." &euro;</td></tr>";
  }
  $out.= "</table>";

  $out.= "Totale merce: ".number_format($_TOTALE_MERCE,2,",",".")." &euro;\n";
  $out.= "Merce scontata: ".number_format($_MERCE_SCONTATA,2,",",".")." &euro;\n";
  $out.= "Imponibile: ".number_format($_TOTALE_IMPONIBILE,2,",",".")." &euro;\n";
  $out.= "Tot. IVA: ".number_format($_TOTALE_IVA,2,",",".")." &euro;\n";
  $out.= "Totale fattura: ".number_format($_TOTALE_IMPONIBILE + $_TOTALE_IVA,2,",",".")." &euro;\n";
  $out.= "Totale spese: ".number_format($_TOTALE_SPESE,2,",",".")." &euro;\n";
  $out.= "Rit. d&lsquo;acconto: ".number_format($_TOTALE_RITENUTA_ACCONTO,2,",",".")." &euro;\n";
  $out.= "Enasarco: ".number_format($_TOTALE_ENASARCO,2,",",".")." &euro;\n";
  $out.= "Cassa prev.: ".number_format($_TOTALE_CASSA_PREV,2,",",".")." &euro;\n";
  $out.= "Abbuoni: ".number_format($_REBATE,2,",",".")." &euro;\n";
  $out.= "Bolli: ".number_format($_STAMP,2,",",".")." &euro;\n";
  $out.= "Netto a pagare: ".number_format($_NET_PAY,2,",",".")." &euro;\n";
 }
 else
  $out = "done!\n";

 $out.= "\nUpdating VAT register...";
 $ret = commercialdocs_fixUpdateVatRegister($sessid, $shellid, $_AP, $docInfo);
 if($ret['error'])
  $out.= "failed!\n".$ret['message'];
 else
  $out.= "done!\n";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_fixUpdateVatRegister($sessid, $shellid, $_AP, $itemInfo)
{
 if(!$itemInfo['cat_id'])
  return array("message"=>"Item has not category");

 global $_BASE_PATH, $_COMPANY_PROFILE;
 include_once($_BASE_PATH."include/company-profile.php");

 $out = "";

 /* get cat tag */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT tag,parent_id FROM dynarc_".$_AP."_categories WHERE id='".$itemInfo['cat_id']."'");
 if($db->Read())
 {
  if($db->record['parent_id'])
  {
   $db->RunQuery("SELECT tag FROM dynarc_".$_AP."_categories WHERE id='".$db->record['parent_id']."'");
   $db->Read();
   $catTag = $db->record['tag']; 
  }
  else
   $catTag = $db->record['tag'];
 }
 $db->Close();


 if($itemInfo['oldctime'] && ($itemInfo['oldctime'] != $itemInfo['ctime']))
 {
  // Remove record from the last register //
  GShell("vatregister delete -year `".date('Y',$itemInfo['oldctime'])."` -docap `".$_AP."` -docid `".$itemInfo['id']."`",$sessid,$shellid);
 }

 /* REMOVE OLD RECORD */
 $out.= "Remove old record from the vat register\n";
 GShell("vatregister delete -year `".date('Y',$itemInfo['ctime'])."` -docap `".$_AP."` -docid `".$itemInfo['id']."`",$sessid,$shellid);

 if(!$recId)
 {
  $recType = 0;
  switch($catTag)
  {
   case 'INVOICES' : case 'ORDERS' : case 'DDT' : case 'DEBITSNOTE' : $recType=2; break;
   case 'VENDORORDERS' : case 'PURCHASEINVOICES' : case 'AGENTINVOICES' : case 'CREDITSNOTE' : $recType=1; break;
   default : return array("message"=>"This type of document has nothing to do with the VAT register"); break;
  }
 }


 /* Get some document info */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT subject_id,subject_name,status FROM dynarc_".$_AP."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 switch($catTag)
 {
  case 'INVOICES' : {
	 if($db->record['status'] == 0)
	  return array("message"=>"This document is not set as paid!");
	} break;

  default : {
	 if($db->record['status'] != 10)
	  return array("message"=>"This document is not set as paid!");
	} break;
 }
 $subjectId = $db->record['subject_id'];
 $subjectName = $db->record['subject_name'];
 $db->Close();


 /* preleva gli importi con le aliquote iva separate */
 $_VATS = array();
 for($c=1; $c < 4; $c++) // limit max 3 vats 
  $_VATS[$itemInfo["vat_".$c."_id"]] = array("amount"=>$itemInfo["vat_".$c."_taxable"], "vat"=>$itemInfo["vat_".$c."_tax"]);

 if($recId)
  $qry = "vatregister edit -id `".$recId."` -year ".date('Y',$itemInfo['ctime']);
 else
  $qry = "vatregister insert -type '".$recType."' -docap `".$_AP."` -docid `".$itemInfo['id']."` -docct `".$catTag."`";
 $qry.= " -ctime `".date('Y-m-d',$itemInfo['ctime'])."` -subjectid `".$subjectId."` -subject `".$subjectName."`";

 reset($_VATS);
 while(list($k,$v) = each($_VATS))
 {
  $qry.= " -vatid ".$k." -amount `".$v['amount']."` -vat `".$v['vat']."`";
 }

 $ret = GShell($qry,$sessid,$shellid);

 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_fixTotals($args, $sessid, $shellid)
{
 /* E' simile alla funzione updateTotals solo che lo fa in maniera massiva */
 global $_BASE_PATH, $_COMPANY_PROFILE;

 $_AP = "commercialdocs";
 $_IDS = array();

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$_IDS[]=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 if(count($_IDS))
 {
  for($c=0; $c < count($_IDS); $c++)
  {
   $ret = GShell("commercialdocs updatetotals -ap '".$_AP."' -id '".$_IDS[$c]."'",$sessid,$shellid);
   if($ret['error'])
	return $ret;
  }
  return array("message"=>"done!\n".count($_IDS)." documents has been updated!");
 }
 else if($where)
 {
  $count = 0;
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".$_AP."_items WHERE ".$where);
  while($db->Read())
  {
   $ret = GShell("commercialdocs updatetotals -ap '".$_AP."' -id '".$db->record['id']."'",$sessid,$shellid);
   if($ret['error'])
	return $ret;
   $count++;
  }
  $db->Close();
  return array("message"=>"done!\n".$count." documents has been updated!");
 }
 else
 {
  // all documents //
  $count = 0;
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".$_AP."_items WHERE trash='0'");
  while($db->Read())
  {
   $ret = GShell("commercialdocs updatetotals -ap '".$_AP."' -id '".$db->record['id']."'",$sessid,$shellid);
   if($ret['error'])
	return $ret;
   $count++;
  }
  $db->Close();
  return array("message"=>"done!\n".$count." documents has been updated!");
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

