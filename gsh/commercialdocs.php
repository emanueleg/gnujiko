<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-05-2017
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Some functions for GCommercialDocs
 #VERSION: 2.87beta
 #CHANGELOG: 27-05-2017 : Aggiunta funzione stats-by-zone , stats-by-agent e fix-doctype.
			 09-05-2017 : Aggiunta funzione summary-by-agent.
			 05-05-2017 : Aggiornata funzione generateAgentInvoice.
			 10-03-2017 : Aggiunta sezione other su XML su funzione generate-fast-document.
			 26-02-2017 : Bugfix su funzione upload-good-delivered.
			 24-02-2017 : Aggiunta opzione allinone su funzione print.
			 23-02-2017 : Aggiunta funzione print per stampa massiva.
			 19-12-2016 : Aggiornata funzione getfullinfo integrata con scontistica predefinita cliente.
			 05-12-2016 : Aggiornata funzione generate-fast-document, aggiunto parametro --auto-find-products
			 21-11-2016 : Aggiornate funzione getFullInfo e getPricesByQty, bug fix su selezione variante.
			 24-10-2016 : MySQLi integration.
			 10-09-2016 : Bug fix su funzione updateTotals - sul calcolo della rit.acc e cassa prev. RG:2880
			 25-07-2016 : Aggiornata funzione generate-fast-document.
			 24-05-2016 : Aggiunto campo per cassa prev. (ccpapply) ed aggiornate funzioni updateTotals.
			 02-05-2016 : Aggiornata funzione update-totals.
			 21-04-2016 : Aggiornata funzione export-elements.
			 11-04-2016 : Aggiunta funzione export-elements.
			 08-04-2016 : Bugfix commercialdocs_uploadGoodsDeliveredFromXML, aggiunto vendorid su comando store upload
			 08-03-2016 : Arrotondato i totali su funzione updateTotals.
			 07-03-2016 : Aggiunto colonne row_ref_docap, row_ref_docid, row_ref_id
			 25-02-2016 : Aggiornata funzione convert.
			 16-02-2016 : Aggiornata funzione generate-from-precompiled-doc.
			 26-01-2016 : Creata funzione generate-precompiled-document e generate-from-precompiled-doc.
			 23-01-2016 : Aggiornato funzioni get-last-vendorprice e get-last-saleprice.
			 18-01-2016 : Aggiunto funzioni get-last-vendorprice e get-last-saleprice.
			 27-07-2015 : Bug fix in function generate-fast-document
			 18-06-2015 : Creata funzione fast-insert-elements.
			 01-05-2015 : Bug fix sugli arrotondamenti.
			 11-04-2015 : Aggiunta funzione generate-from-contract.
			 31-03-2015 : Aggiunta funzione fix-book-inc-arts.
			 30-03-2015 : Aggiornata funzione generate-vendor-orders
			 23-02-2015 : Aggiunta funzione ddtinGroup.
			 23-01-2015 : Aggiunta funzione get-docs-by-intdocref.
			 21-01-2015 : Aggiunto spese trasporto e imballaggio su funzione updatetotals.
			 16-01-2015 : Aggiunta funzione summary-by-subject.
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
  case 'generate-agent-invoice' : return commercialdocs_generateAgentInvoice($args, $sessid, $shellid); break;
  case 'generate-fast-document' : return commercialdocs_generateFastDocument($args, $sessid, $shellid); break;
  case 'fast-insert-elements' : return commercialdocs_fastInsertElements($args, $sessid, $shellid); break;
  case 'generate-from-contract' : return commercialdocs_generateFromContract($args, $sessid, $shellid); break;
  case 'renews-order' : return commercialdocs_renewsOrder($args, $sessid, $shellid); break;
  case 'convert' : return commercialdocs_convert($args, $sessid, $shellid); break;
  case 'duplicate' : return commercialdocs_duplicate($args, $sessid, $shellid); break;
  case 'book-articles' : return commercialdocs_bookArticles($args, $sessid, $shellid); break;
  case 'upload-goods-delivered' : return commercialdocs_uploadGoodsDelivered($args, $sessid, $shellid); break;
  case 'new-predefmsg' : return commercialdocs_newPredefMsg($args, $sessid, $shellid); break;
  case 'edit-predefmsg' : return commercialdocs_editPredefMsg($args, $sessid, $shellid); break;
  case 'delete-predefmsg' : return commercialdocs_deletePredefMsg($args, $sessid, $shellid); break;
  case 'predefmsg-list' : return commercialdocs_predefMsgList($args, $sessid, $shellid); break;

  case 'getfullinfo' : case 'get-full-info' : return commercialdocs_getFullInfo($args, $sessid, $shellid); break;
  case 'getpricebyqty' : case 'get-price-by-qty' : return commercialdocs_getPriceByQty($args, $sessid, $shellid); break;
  case 'updatetotals' : return commercialdocs_updateTotals($args, $sessid, $shellid); break;
  case 'fix-totals' : return commercialdocs_fixTotals($args, $sessid, $shellid); break;
  case 'fix-mmr' : return commercialdocs_fixMMR($args, $sessid, $shellid); break;
  case 'fix-mmr-docdates' : return commercialdocs_fixMMRdocDates($args, $sessid, $shellid); break;
  case 'fix-mmr-docref' : return commercialdocs_fixMMRdocReferences($args, $sessid, $shellid); break;
  case 'fix-vendors' : return commercialdocs_fixVendorsIntoElements($args, $sessid, $shellid); break;
  case 'fix-doctype' : case 'fix-rootct' : return commercialdocs_fixRootCT($args, $sessid, $shellid); break;

  case 'generate-file' : return commercialdocs_generateFile($args, $sessid, $shellid); break;
  case 'emit-signal' : return commercialdocs_emitSignal($args, $sessid, $shellid); break;

  case 'get-last-products' : return commercialdocs_getLastProducts($args, $sessid, $shellid); break;
  case 'get-ddt-tobegroup' : return commercialdocs_getDDTToBeGroup($args, $sessid, $shellid); break;
  case 'ddt-group' : return commercialdocs_ddtGroup($args, $sessid, $shellid); break;
  case 'ddtin-group' : return commercialdocs_ddtinGroup($args, $sessid, $shellid); break;
  case 'get-docs-by-intdocref' : return commercialdocs_getDocsByIntDocRef($args, $sessid, $shellid); break;

  case 'summary-by-subject' : case 'summary-by-customer' : return commercialdocs_summaryBySubject($args, $sessid, $shellid); break;
  case 'summary-by-agent' : return commercialdocs_summaryByAgent($args, $sessid, $shellid); break;
  case 'stats-by-zone' : return commercialdocs_statsByZone($args, $sessid, $shellid); break;
  case 'stats-by-agent' : return commercialdocs_statsByAgent($args, $sessid, $shellid); break;

  case 'fix-book-inc-arts' : return commercialdocs_fixBookIncArts($args, $sessid, $shellid); break;

  case 'get-last-vendorprice' : return commercialdocs_getLastVendorPrice($args, $sessid, $shellid); break;
  case 'get-last-saleprice' : return commercialdocs_getLastSalePrice($args, $sessid, $shellid); break;

  case 'generate-precompiled-document' : return commercialdocs_generatePrecompiledDocument($args, $sessid, $shellid); break;
  case 'generate-from-precompiled-doc' : case 'generate-from-precompiled-document' : return commercialdocs_generateFromPrecompiledDocument($args, $sessid, $shellid); break;
  case 'update-predoc-totals' : return commercialdocs_updatePredocTotals($args, $sessid, $shellid); break;
  case 'duplicate-predoc' : return commercialdocs_duplicatePredoc($args, $sessid, $shellid); break;

  case 'get-last-doc' : return commercialdocs_getLastDocument($args, $sessid, $shellid); break;
  case 'export-elements' : return commercialdocs_exportElements($args, $sessid, $shellid); break;

  case 'export-to-xml' : return commercialdocs_exportToXML($args, $sessid, $shellid); break;
  case 'import-from-xml' : return commercialdocs_importFromXML($args, $sessid, $shellid); break;
  case 'print' : return commercialdocs_print($args, $sessid, $shellid); break;

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
 $_DOC_TYPE = "INVOICES";
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$_IDS[]=$args[$c+1]; $c++;} break; // ID dei documenti da raggruppare //
   case '-type' : {$_DOC_TYPE=$args[$c+1]; $c++;} break;	// tipo di documento
  }

 if(!count($_IDS))
  return array("message"=>"You must specify at least one document to be grouped.", "error"=>"INVALID_DOCUMENT_ID");

 $docId = 0;

 for($c=0; $c < count($_IDS); $c++)
 {
  $ret = GShell("dynarc item-info -ap `commercialdocs` -id `".$_IDS[$c]."` -extget `cdinfo,cdelements,mmr`",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $docInfo = $ret['outarr'];
  if($c==0)
  {
   $query = "dynarc new-item -ap `commercialdocs` -ct `".$_DOC_TYPE."` -set `tag='DEFERRED'` -group commdocs-invoices -extset `cdinfo.subjectid='"
	.$docInfo['subject_id']."',subject=\"".$docInfo['subject_name']."\"";
   if($docInfo['reference_id'])
    $query.= ",referenceid='".$docInfo['reference_id']."'";
   if($docInfo['agent_id'])
    $query.= ",agentid='".$docInfo['agent_id']."'";
   if($docInfo['agent_commiss'])
    $query.= ",agentcommiss='".$docInfo['agent_commiss']."'";
   if($docInfo['paymentmode'])
    $query.= ",paymentmode='".$docInfo['paymentmode']."'";
   if($docInfo['banksupport_id'])
    $query.= ",banksupport='".$docInfo['banksupport_id']."'";
   if($docInfo['pricelist_id'])
    $query.= ",pricelist='".$docInfo['pricelist_id']."'";
   if($docInfo['division'])
    $query.= ",division='".$docInfo['division']."'";
   if($docInfo['location'])
	$query.= ",location=\"".$docInfo['location']."\"";

   /* Shipping */
   $query.= ",ship-subject-id='".$docInfo['ship_subject_id']."'";
   $query.= ",ship-contact-id='".$docInfo['ship_contact_id']."'";
   $query.= ",ship-recp=\"".$docInfo['ship_recp']."\"";
   $query.= ",ship-addr=\"".$docInfo['ship_addr']."\"";
   $query.= ",ship-city=\"".$docInfo['ship_city']."\"";
   $query.= ",ship-zip='".$docInfo['ship_zip']."'";
   $query.= ",ship-prov='".$docInfo['ship_prov']."'";
   $query.= ",ship-cc='".$docInfo['ship_cc']."'";

   $query.= "`";

   $ret = GShell($query,$sessid,$shellid);
   if($ret['error'])
	return $ret;
   $docId = $ret['outarr']['id'];
   $outArr = $ret['outarr'];

   // UPDATE TRANSPORT AND OTHER INFORMATIONS
   $query = "dynarc edit-item -ap commercialdocs -id '".$docId."' -extset `cdinfo.";
   /* Transport */
   $query.= "trans-method='".$docInfo['trans_method']."'";
   $query.= ",trans-shipper=\"".$docInfo['trans_shipper']."\"";
   $query.= ",trans-numplate='".$docInfo['trans_numplate']."'";
   $query.= ",trans-causal=\"".$docInfo['trans_causal']."\"";
   if($docInfo['trans_datetime'])
    $query.= ",trans-date='".date('Y-m-d H:i',$docInfo['trans_datetime'])."'";
   $query.= ",trans-aspect=\"".$docInfo['trans_aspect']."\"";
   $query.= ",trans-num='".$docInfo['trans_num']."'";
   $query.= ",trans-weight='".$docInfo['trans_weight']."'";
   $query.= ",trans-freight='".$docInfo['trans_freight']."'";
   $query.= ",cartage='".$docInfo['cartage']."'";
   $query.= ",packing-charges='".$docInfo['packing_charges']."'";
   $query.= ",collection-charges='".$docInfo['collection_charges']."'";

   /* Expenses */
   if($docInfo['exp1name'])
    $query.= ",exp1-name=\"".$docInfo['exp1name']."\",exp1-vatid='".$docInfo['exp1vatid']."',exp1-amount='".$docInfo['exp1amount']."'";
   if($docInfo['exp2name'])
    $query.= ",exp2-name=\"".$docInfo['exp2name']."\",exp2-vatid='".$docInfo['exp2vatid']."',exp2-amount='".$docInfo['exp2amount']."'";
   if($docInfo['exp3name'])
    $query.= ",exp3-name=\"".$docInfo['exp3name']."\",exp3-vatid='".$docInfo['exp3vatid']."',exp3-amount='".$docInfo['exp3amount']."'";

   /* Discounts, rebate and stamp */
   if($docInfo['discount'])
    $query.= ",discount='".$docInfo['discount']."'";
   if($docInfo['discount2'])
    $query.= ",discount2='".$docInfo['discount2']."'";
   if($docInfo['uncondisc'])
    $query.= ",uncondisc='".$docInfo['uncondisc']."'";
   if($docInfo['rebate'])
    $query.= ",rebate='".$docInfo['rebate']."'";
   if($docInfo['stamp'])
    $query.= ",stamp='".$docInfo['stamp']."'";

   $query.= "`";
   GShell($query,$sessid,$shellid);

  }

  GShell("dynarc edit-item -ap `commercialdocs` -id `".$docId."` -extset `cdelements.type='note',desc='''Rif. ".$docInfo['name']."'''`",$sessid,$shellid);
  for($i=0; $i < count($docInfo['elements']); $i++)
  {
   $el = $docInfo['elements'][$i];
   GShell("dynarc edit-item -ap `commercialdocs` -id `".$docId."` -extset `cdelements.type='".$el['type']."',refap='".$el['ref_ap']."',refid='"
	.$el['ref_id']."',code='".$el['code']."',vencode='".$el['vencode']."',sn='".$el['serialnumber']."',lot='".$el['lot']."',accountid='"
	.$el['account_id']."',name='''".$el['name']."''',desc='''".$el['desc']."''',qty='".$el['qty']."',extraqty='".$el['extraqty']."',price='"
	.$el['price']."',discount='".$el['discount']."',discount2='".$el['discount2']."',discount3='".$el['discount3']."',vatrate='"
	.$el['vatrate']."',vatid='".$el['vatid']."',vattype='".$el['vattype']."',units='".$el['units']."',pricelistid='"
	.$el['pricelist_id']."',brandid='".$el['brand_id']."',vendorid='".$el['vendor_id']."',vendorprice='"
	.$el['vendor_price']."',coltint='''".$el['variant_coltint']."''',sizmis='''".$el['variant_sizmis']."''',ritaccapply='"
	.$el['ritaccapply']."',ccpapply='".$el['ccpapply']."',xmldata='''".$el['xmldata']."''',rowdocap='commercialdocs',rowdocid='".$docInfo['id']."',rowrefid='".$el['id']."',bypass-vatregister-update=true`",$sessid,$shellid);
  }
 }

 /* UPDATING TOTALS */
 $out.= "\nUpdating totals...";
 $ret = GShell("commercialdocs updatetotals -id '".$docId."'",$sessid,$shellid);
 if($ret['error'])
  $out.= "failed\n".$ret['message'];
 else
 {
  $out.= "\ndone!\n".$ret['message'];
  $outArr = $ret['outarr'];
 }

 /* AGGIORNA LE SCADENZE */
 $ret = GShell("accounting paymentmodeinfo -id '".$outArr['paymentmode']."' -amount '".$outArr['tot_netpay']."' -from '".date('Y-m-d',$docInfo['ctime'])."' --get-deadlines",$sessid,$shellid);
 if(!$ret['error'])
 {
  $list = $ret['outarr']['deadlines'];
  for($c=0; $c < count($list); $c++)
  {
   $mmr = $list[$c];
   GShell("dynarc edit-item -ap 'commercialdocs' -id '".$docId."' -extset `mmr.desc='Rata n.".($c+1)." scad. "
	.date('d/m/Y',strtotime($mmr['date']))."',incomes='".$mmr['amount']."',expire='".$mmr['date']."',subjectid='"
	.$docInfo['subject_id']."',subject_name=\"".$docInfo['subject_name']."\"`",$sessid,$shellid);
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
   case '-docid' : {$docId=$args[$c+1]; $c++;} break;
   default : $tmp=$args[$c]; break;
  }

 if($docId)
  return commercialdocs_generateVendorOrdersFromDoc($args, $sessid, $shellid);

 $elements = array();
 $vendorElements = array();

 /* Raggruppa gli elementi in base al tipo di archivio */
 $db = new AlpaDatabase();
 $x = explode(",",$tmp);
 for($c=0; $c < count($x); $c++)
 {
  $xx = explode(":",$x[$c]);
  $ap = $xx[0];
  $at = "";
  $xxx = explode("X",$xx[1]);
  $id = $xxx[0];
  $qty = $xxx[1];

  if(!$elements[$ap])
   $elements[$ap] = array();

  // get archive type
  $db->RunQuery("SELECT archive_type FROM dynarc_archives WHERE tb_prefix='".$ap."' AND trash='0' LIMIT 1");
  $db->Read();
  $at = $db->record['archive_type'];
  $type = "";
  switch($at)
  {
   case 'gmart' : 		$type='article'; break;
   case 'gproducts' : 	$type='finalproduct'; break;
   case 'gpart' : 		$type='component'; break;
   case 'gmaterial' : 	$type='material'; break;
   case 'lottomatica' : $type='lottomatica'; break;
   case 'gbook' : 		$type='book'; break;
  }

  $elements[$ap][] = array('id'=>$id, 'qty'=>$qty, 'type'=>$type);
 }
 $db->Close();

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
   $vendorElements[$vendorName][] = array('ap'=>$k, 'type'=>$v[$c]['type'], 'id'=>$v[$c]['id'], 'qty'=>$v[$c]['qty'], 'code'=>$db->record['code'], 'price'=>$db->record['price'], 'vat'=>$db->record['vatrate'], 'name'=>$db2->record['name']);
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
	 $db->RunQuery("UPDATE dynarc_commercialdocs_elements SET qty='".($db->record['qty']+$itm['qty'])."' WHERE id='".$db->record['id']."'");
	 $db->Close();
	 continue;
	}
	$db->Close();
   }
   $ret = GShell("dynarc edit-item -ap commercialdocs -id `".$docInfo['id']."` -extset `cdelements.type='".$itm['type']."',refap='".$itm['ap']."',refid='"
	.$itm['id']."',code='".$itm['code']."',vencode='".$itm['vencode']."',name='".$itm['name']."',qty='".$itm['qty']."',price='".$itm['price']."',vat='".$itm['vat']."'`",$sessid,$shellid);
   if($ret['error'])
	return $ret;
  }
 }

 $out.= "done!";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_generateVendorOrdersFromDoc($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $_AP = "commercialdocs";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-docap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-docid' : {$docId=$args[$c+1]; $c++;} break;
   default : $tmp=$args[$c]; break;
  }

 if(!$docId) return array('message'=>"Generate vendor orders failed! You must specify the document id", "error"=>"INVALID_DOC_ID");

 // verifica se esiste il documento e se si hanno i permessi in lettura.
 $ret = GShell("dynarc item-info -ap '".$_AP."' -id '".$docId."'",$sessid,$shellid);
 if($ret['error']) return array('message'=>"Generate vendor orders failed!\n".$ret['message'], 'error'=>$ret['error']);

 $vendorElements = array(); // lista elementi divisi per fornitore

 $_FIELDS = "elem_type,ref_ap,ref_id,code,vencode,name,vendor_id,vendor_price";
 $_FIELDS.= ",vat_rate,vat_id,vat_type,units,brand_id,variant_coltint,variant_sizmis";
 $db = new AlpaDatabase();
 $x = explode(",",$tmp);
 for($c=0; $c < count($x); $c++)
 {
  $xx = explode("X",$x[$c]);
  $elID = $xx[0];
  $qty = $xx[1];

  $db->RunQuery("SELECT ".$_FIELDS." FROM dynarc_".$_AP."_elements WHERE id='".$elID."'");
  $db->Read();

  $vendorId = $db->record['vendor_id'];
  if(!$vendorElements[$vendorId])
   $vendorElements[$vendorId] = array();

  $db->record['qty'] = $qty;

  $vendorElements[$vendorId][] = $db->record;
 }
 $db->Close();
 

 /* Inserisce gli elementi per ogni fornitore verificando se esiste già un ordine fornitore aperto oppure se è necessario crearne uno nuovo */
 reset($vendorElements);
 while(list($vendorId,$elements) = each($vendorElements))
 {
  // verifica se esiste già un ordine aperto per questo fornitore, altrimenti ne crea uno nuovo.
  $ret = GShell("dynarc item-find -ap commercialdocs -ct vendororders -field subject_id `".$vendorId."` -where `status=0` -limit 1",$sessid,$shellid);
  if($ret['error'] || !count($ret['outarr']['items']))
  {
   // crea un nuovo ordine fornitore //
   $ret = GShell("dynarc new-item -ap commercialdocs -ct vendororders -group commdocs-vendororders -extset `cdinfo.subjectid='".$vendorId."'`",$sessid,$shellid);
   if($ret['error']) return array('message'=>"Unable to generate vendor order.\n".$ret['message'], 'error'=>$ret['error']);
   $vendorOrderInfo = $ret['outarr'];
  }
  else
  {
   $vendorOrderInfo = $ret['outarr']['items'][0];
   $vendorOrderInfo['already_exists'] = true;
  }

  // Inserisco gli articoli nell'ordine fornitore.
  for($c=0; $c < count($elements); $c++)
  {
   $el = $elements[$c];
   // se l'articolo è già stato inserito aggiorna solo la quantità 
   if($vendorOrderInfo['already_exists'])
   {
    $db = new AlpaDatabase();
	$db->RunQuery("SELECT id,qty FROM dynarc_commercialdocs_elements WHERE item_id='".$vendorOrderInfo['id']."' AND ref_ap='"
		.$el['ref_ap']."' AND ref_id='".$el['ref_id']."' AND variant_coltint='".$el['variant_coltint']."' AND variant_sizmis='"
		.$el['variant_sizmis']."' LIMIT 1");
	if($db->Read())
	{
	 $db->RunQuery("UPDATE dynarc_commercialdocs_elements SET qty='".($db->record['qty']+$el['qty'])."' WHERE id='".$db->record['id']."'");
	 $db->RunQuery("UPDATE dynarc_".$el['ref_ap']."_items SET incoming=incoming+".$el['qty']." WHERE id='".$el['ref_id']."'");
	 $db->Close();
	 continue;
	}
	$db->Close();
   }

   $ret = GShell("dynarc edit-item -ap commercialdocs -id `".$vendorOrderInfo['id']."` -extset `cdelements.type='".$el['elem_type']."',refap='"
	.$el['ref_ap']."',refid='".$el['ref_id']."',code='".$el['code']."',vencode='".$el['vencode']."',name='''".$el['name']."''',qty='"
	.$el['qty']."',vendorprice='".$el['vendor_price']."',vat='".$el['vat_rate']."',vatid='".$el['vat_id']."',vattype='"
	.$el['vat_type']."',units='".$el['units']."',brandid='".$el['brand_id']."',coltint='".$el['variant_coltint']."',sizmis='"
	.$el['variant_sizmis']."',price='".$el['vendor_price']."',vendorid='".$vendorId."'`",$sessid,$shellid);

   if($ret['error']) return array('message'=>"Unable to insert element into ".$vendorOrderInfo['name']."\n".$ret['message'], 'error'=>$ret['error']);

   /*$db = new AlpaDatabase();
   $db->RunQuery("UPDATE dynarc_".$el['ap']."_items SET incoming=incoming+".$el['qty']." WHERE id='".$el['id']."'");
   $db->Close();*/

  } /* EOF - FOR */

 } /* EOF - WHILE */

 // update totals
 $ret = GShell("commercialdocs updatetotals -id '".$vendorOrderInfo['id']."' --fix-mmr",$sessid,$shellid);

 $out.= "done!";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_generateAgentInvoice($args, $sessid, $shellid)
{
 $_AP = "commercialdocs";

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-agent' : case '-agentid' : {$agentId=$args[$c+1]; $c++;} break;
   case '-docs' : {$docs=$args[$c+1]; $c++;} break; // documents ID, separated by comma.

  }

 if(!$agentId)
  return array("message"=>"You must specify the agent id. (with: -agent AGENT_ID)", "error"=>"INVALID_AGENT");

 $ret = GShell("dynarc new-item -ap '".$_AP."' -ct AGENTINVOICES -group commdocs-agentinvoices -extset `cdinfo.subjectid='".$agentId."'`",$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $agentInvoiceInfo = $ret['outarr'];

 $_DOC_IDS = explode(",",$docs);
 $totCommiss = 0;
 
 for($c=0; $c < count($_DOC_IDS); $c++)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT agent_commiss FROM dynarc_".$_AP."_items WHERE id='".$_DOC_IDS[$c]."'");
  $db->Read();
  $totCommiss+= $db->record['agent_commiss'];
  // update docs status //
  /*GShell("dynarc edit-item -ap `commercialdocs` -id `".$_DOC_IDS[$c]."` -extset `cdinfo.group-id='".$agentInvoiceInfo['id']."',status='9'`",$sessid,$shellid);*/
  $db->Close();
 }
 
 $ret = GShell("dynarc edit-item -ap '".$_AP."' -id '".$agentInvoiceInfo['id']."' -extset `cdelements.type='commissions',name='Totale provvigioni',qty=1,price='".$totCommiss."',vatrate=0`",$sessid,$shellid);
 if($ret['error'])
  return $ret;

 // Update totals
 GShell("commercialdocs updatetotals -id '".$agentInvoiceInfo['id']."'",$sessid,$shellid);

 $out = "done!\nAgent invoice ID=".$agentInvoiceInfo['id'];
 $outArr = $agentInvoiceInfo;

 // For each document set agent invoice ID
 $db = new AlpaDatabase();
 $out.= "Update documents agent invoice reference...";
 for($c=0; $c < count($_DOC_IDS); $c++)
 {
  $db->RunQuery("UPDATE dynarc_".$_AP."_items SET agent_invoice='".$agentInvoiceInfo['id']."' WHERE id='".$_DOC_IDS[$c]."'");
  if($db->Error) return array('message'=>$out."failed!\nMySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, 'error'=>'MYSQL_ERROR');
 }
 $db->Close();
 $out.= "done!\n";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_generateFastDocument($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $cdinfo = "";
 $_SHIPPING = null;
 $_TRANSPORT = null;
 $_OTHER_FEES = null;
 $_WITHHOLDINGTAX = null;
 $_SUBJECT_INFO = null;
 $_OTHER_CDINFO = null;
 $autoFindProducts = false;
 $autoRegisterProducts = false;
 $defaultGMartAP = "gmart";
 $defaultGMartCatId = 0;

 $ctime = date('Y-m-d H:i:s');

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ctime' : {$ctime=$args[$c+1]; $c++;} break;
   case '-type' : case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-group' : {$group=$args[$c+1]; $c++;} break;
   case '-status' : {$status=$args[$c+1]; $c++;} break;
   case '-xml' : {$xmlData=$args[$c+1]; $c++;} break; // import from xml data
   case '-subject' : case '-subjectid' : {$subjectId=$args[$c+1]; $c++;} break;
   case '--download-from-store' : {$downloadFromStoreId=$args[$c+1]; $c++;} break;
   case '--auto-find-products' : $autoFindProducts=true; break;
   case '--auto-register-products' : $autoRegisterProducts=true; break;
   case '--default-gmart-ap' : {$defaultGMartAP=$args[$c+1]; $c++;} break;
   case '--default-gmart-cat' : {$defaultGMartCatId=$args[$c+1]; $c++;} break;

   default : $tmp=$args[$c]; break; // ap : id X qty , ...
  }

 $elements = array();

 if($tmp)
 {
  $x = explode(",",$tmp);
  $db = new AlpaDatabase();
  for($c=0; $c < count($x); $c++)
  {
   $xx = explode(":",$x[$c]);
   $ap = $xx[0];
   $at = "";
   $xxx = explode("X",$xx[1]);
   $id = $xxx[0];
   $qty = $xxx[1];

   // get archive type
   $db->RunQuery("SELECT archive_type FROM dynarc_archives WHERE tb_prefix='".$ap."' AND trash='0' LIMIT 1");
   $db->Read();
   $at = $db->record['archive_type'];
   $type = "";
   switch($at)
   {
    case 'gmart' : 		$type='article'; break;
    case 'gproducts' : 	$type='finalproduct'; break;
    case 'gpart' : 		$type='component'; break;
    case 'gmaterial' : 	$type='material'; break;
    case 'lottomatica' : $type='lottomatica'; break;
    case 'gbook' : 		$type='book'; break;
   }
   $elements[] = array('ap'=>$ap, 'at'=>$at, 'type'=>$type, 'id'=>$id, 'qty'=>$qty);
  }
  $db->Close();
 }
 else if($xmlData)
 {
  $xmlData = ltrim(rtrim($xmlData));
  if(strpos($xmlData, "<xml") === false)
   $xmlData = "<xml>".$xmlData."</xml>";
  $xml = new GXML();
  if($xml->LoadFromString($xmlData))
  {
   $nodes = $xml->GetElementsByTagName("elements");
   if(count($nodes)) $elements = $nodes[0]->toArray();
   else
   {
    $list = $xml->GetElementsByTagName("item");
    for($c=0; $c < count($list); $c++)
	 $elements[] = $list[$c]->toArray();
   }

   // Get subject info from XML
   $nodes = $xml->GetElementsByTagName("subject");
   if(count($nodes)) $_SUBJECT_INFO = $nodes[0]->toArray();

   // Get shipping info from XML
   $nodes = $xml->GetElementsByTagName("shipping");
   if(count($nodes)) $_SHIPPING = $nodes[0]->toArray();

   // Get transport info from XML
   $nodes = $xml->GetElementsByTagName("transport");
   if(count($nodes)) $_TRANSPORT = $nodes[0]->toArray();

   // Get other fees from XML
   $nodes = $xml->GetElementsByTagName("otherfees");
   if(count($nodes)) $_OTHER_FEES = $nodes[0]->toArray();

   // Get withholding taxes info from XML
   $nodes = $xml->GetElementsByTagName("withholdingtax");
   if(count($nodes)) $_WITHHOLDINGTAX = $nodes[0]->toArray();

   // Get document info from XML
   $nodes = $xml->GetElementsByTagName("other");
   if(count($nodes)) $_OTHER_CDINFO = $nodes[0]->toArray();
   
  }
 }

 if(is_array($_SUBJECT_INFO))
 {
  if(!$subjectId)
  {
   // Check if customer (or vendor) already exists...
   $ret = GShell("fastfind contacts -fields name `".$_SUBJECT_INFO['name']."`",$sessid, $shellid);
   if(!$ret['error'] && count($ret['outarr']['results'])) 
	$subjectId = $ret['outarr']['results'][0]['id'];
   else
   {
	// Register new customer (or vendor)...
    $_CT = "customers";
    switch(strtoupper($catTag))
    {
	 case 'VENDORORDERS' : case 'DDTIN' : case 'PURCHASEINVOICES' : $_CT = "vendors"; break;
     default : $_CT = "customers"; break;
    }

    // Register subject
    $cmd = "dynarc new-item -ap rubrica -ct '".$_CT."' -group rubrica -name `".$_SUBJECT_INFO['name']."` -extset `contacts.name='''"
		.$_SUBJECT_INFO['name']."'''";
    if($_SUBJECT_INFO['address'])		$cmd.= ",address='''".$_SUBJECT_INFO['address']."'''";
    if($_SUBJECT_INFO['city'])			$cmd.= ",city='''".$_SUBJECT_INFO['city']."'''";
    if($_SUBJECT_INFO['zip'])			$cmd.= ",zipcode='".$_SUBJECT_INFO['zip']."'";
    else if($_SUBJECT_INFO['zipcode'])	$cmd.= ",zipcode='".$_SUBJECT_INFO['zipcode']."'";
    if($_SUBJECT_INFO['province'])		$cmd.= ",province='".$_SUBJECT_INFO['province']."'";
    if($_SUBJECT_INFO['countrycode'])	$cmd.= ",countrycode='".$_SUBJECT_INFO['countrycode']."'";
    if($_SUBJECT_INFO['phone'])			$cmd.= ",phone='".$_SUBJECT_INFO['phone']."'";
    if($_SUBJECT_INFO['phone2'])		$cmd.= ",phone2='".$_SUBJECT_INFO['phone2']."'";
    if($_SUBJECT_INFO['fax'])			$cmd.= ",fax='".$_SUBJECT_INFO['fax']."'";
    if($_SUBJECT_INFO['cell'])			$cmd.= ",cell='".$_SUBJECT_INFO['cell']."'";
    if($_SUBJECT_INFO['email'])			$cmd.= ",email='".$_SUBJECT_INFO['email']."'";
    if($_SUBJECT_INFO['email2'])		$cmd.= ",email2='".$_SUBJECT_INFO['email2']."'";
    if($_SUBJECT_INFO['email3'])		$cmd.= ",email3='".$_SUBJECT_INFO['email3']."'";
    if($_SUBJECT_INFO['skype'])			$cmd.= ",skype='".$_SUBJECT_INFO['skype']."'";

	$rubinfo = "";
	if($_SUBJECT_INFO['taxcode'])		$rubinfo.= ",taxcode='".$_SUBJECT_INFO['taxcode']."'";
	if($_SUBJECT_INFO['vatnumber'])		$rubinfo.= ",vatnumber='".$_SUBJECT_INFO['vatnumber']."',iscompany='1'";

	if($rubinfo) 						$cmd.= ",rubricainfo.".ltrim($rubinfo,",");
    $cmd.= "`";

    $ret = GShell($cmd, $sessid, $shellid);
    if(!$ret['error'])
	 $subjectId = $ret['outarr']['id'];
   }
  }
 }


 /* Crea il documento */
 $out.= "Creating document...";
 $command = "dynarc new-item -ap `commercialdocs` -ctime `".$ctime."`";
 if($catId)
  $command.= " -cat `".$catId."`";
 else if($catTag)
  $command.= " -ct `".$catTag."`";
 if($group)
  $command.= " -group `".$group."`";

 if($subjectId)  	$cdinfo.= ",subjectid='".$subjectId."'";
 if($status)		$cdinfo.= ",status='".$status."'";

 if($cdinfo)
  $command.= " -extset `cdinfo.".ltrim($cdinfo,",")."`";

 $ret = GShell($command,$sessid,$shellid);
 if($ret['error']){$out.= "failed!\n".$ret['message']; return array('message'=>$out,'error'=>$ret['error']); }
 $docInfo = $ret['outarr'];
 $out.= "done!\n";

 /* IMPORT OTHER INFO FROM XML */
 $cdinfo = "";
 // SHIPPING
 if(is_array($_SHIPPING))
 {
  if($_SHIPPING['cartage'])				$cdinfo.= ",cartage='".$_SHIPPING['cartage']."'";
  if($_SHIPPING['cartage_vatid'])		$cdinfo.= ",cartage-vatid='".$_SHIPPING['cartage_vatid']."'";
  if($_SHIPPING['recp'])				$cdinfo.= ",ship-recp='''".$_SHIPPING['recp']."'''";
  if($_SHIPPING['address'])				$cdinfo.= ",ship-addr='''".$_SHIPPING['address']."'''";
  if($_SHIPPING['city'])				$cdinfo.= ",ship-city='''".$_SHIPPING['city']."'''";
  if($_SHIPPING['zip'])					$cdinfo.= ",ship-zip='''".$_SHIPPING['zip']."'''";
  else if($_SHIPPING['zipcode'])		$cdinfo.= ",ship-zip='''".$_SHIPPING['zipcode']."'''";
  if($_SHIPPING['prov'])				$cdinfo.= ",ship-prov='''".$_SHIPPING['prov']."'''";
  else if($_SHIPPING['province'])		$cdinfo.= ",ship-prov='''".$_SHIPPING['province']."'''";
  if($_SHIPPING['cc'])					$cdinfo.= ",ship-cc='''".$_SHIPPING['cc']."'''";
  else if($_SHIPPING['countrycode'])	$cdinfo.= ",ship-cc='''".$_SHIPPING['countrycode']."'''";

 }

 if(is_array($_TRANSPORT))
 {
  if($_TRANSPORT['method'])				$cdinfo.= ",trans-method='".$_TRANSPORT['method']."'";
  if($_TRANSPORT['shipper'])			$cdinfo.= ",trans-shipper='".$_TRANSPORT['shipper']."'";
  if($_TRANSPORT['numplate'])			$cdinfo.= ",trans-numplate='".$_TRANSPORT['numplate']."'";
  if($_TRANSPORT['causal'])				$cdinfo.= ",trans-causal='".$_TRANSPORT['causal']."'";
  if($_TRANSPORT['datetime'])			$cdinfo.= ",trans-datetime='".$_TRANSPORT['datetime']."'";
  if($_TRANSPORT['aspect'])				$cdinfo.= ",trans-aspect='".$_TRANSPORT['aspect']."'";
  if($_TRANSPORT['num'])				$cdinfo.= ",trans-num='".$_TRANSPORT['num']."'";
  if($_TRANSPORT['weight'])				$cdinfo.= ",trans-weight='".$_TRANSPORT['weight']."'";
  if($_TRANSPORT['freight'])			$cdinfo.= ",trans-freight='".$_TRANSPORT['freight']."'";
  if($_TRANSPORT['cartage'])			$cdinfo.= ",cartage='".$_TRANSPORT['cartage']."'";
  if($_TRANSPORT['packingcharges'])		$cdinfo.= ",packing-charges='".$_TRANSPORT['packingcharges']."'";
  if($_TRANSPORT['trackingnumber'])		$cdinfo.= ",trackingnumber='".$_TRANSPORT['trackingnumber']."'";
 }

 if(is_array($_OTHER_FEES))
 {
  if($_OTHER_FEES['exp_1_name'])		$cdinfo.= ",exp1-name='''".$_OTHER_FEES['exp_1_name']."'''";
  if($_OTHER_FEES['exp_1_amount'])		$cdinfo.= ",exp1-amount='".$_OTHER_FEES['exp_1_amount']."'";
  if($_OTHER_FEES['exp_1_vatid'])		$cdinfo.= ",exp1-vatid='".$_OTHER_FEES['exp_1_vatid']."'";

  if($_OTHER_FEES['exp_2_name'])		$cdinfo.= ",exp2-name='''".$_OTHER_FEES['exp_2_name']."'''";
  if($_OTHER_FEES['exp_2_amount'])		$cdinfo.= ",exp2-amount='".$_OTHER_FEES['exp_2_amount']."'";
  if($_OTHER_FEES['exp_2_vatid'])		$cdinfo.= ",exp2-vatid='".$_OTHER_FEES['exp_2_vatid']."'";

  if($_OTHER_FEES['exp_3_name'])		$cdinfo.= ",exp3-name='''".$_OTHER_FEES['exp_3_name']."'''";
  if($_OTHER_FEES['exp_3_amount'])		$cdinfo.= ",exp3-amount='".$_OTHER_FEES['exp_3_amount']."'";
  if($_OTHER_FEES['exp_3_vatid'])		$cdinfo.= ",exp3-vatid='".$_OTHER_FEES['exp_3_vatid']."'";

  if($_OTHER_FEES['collectioncharges'])	$cdinfo.= ",collection-charges='".$_OTHER_FEES['collectioncharges']."'";
  if($_OTHER_FEES['stamp'])				$cdinfo.= ",stamp='".$_OTHER_FEES['stamp']."'";
  if($_OTHER_FEES['rebate'])			$cdinfo.= ",rebate='".$_OTHER_FEES['rebate']."'";
 }

 if(is_array($_WITHHOLDINGTAX))
 {
  if($_WITHHOLDINGTAX['cassaprev'])				$cdinfo.= ",contr-cassa-prev='".$_WITHHOLDINGTAX['cassaprev']."'";
  /* TODO: bisogna ricavare id aliquota iva di cassa prev */
  if($_WITHHOLDINGTAX['enasarco'])				$cdinfo.= ",rit-enasarco='".$_WITHHOLDINGTAX['enasarco']."'";
  if($_WITHHOLDINGTAX['enasarcopercimp'])		$cdinfo.= ",rit-enasarco-percimp='".$_WITHHOLDINGTAX['enasarcopercimp']."'";
  if($_WITHHOLDINGTAX['ritacconto'])			$cdinfo.= ",rit-acconto='".$_WITHHOLDINGTAX['ritacconto']."'";
  if($_WITHHOLDINGTAX['ritaccontopercimp'])		$cdinfo.= ",rit-acconto-percimp='".$_WITHHOLDINGTAX['ritaccontopercimp']."'";
  if($_WITHHOLDINGTAX['ritaccontorivinpsinc'])	$cdinfo.= ",rit-acconto-rivinpsinc='".$_WITHHOLDINGTAX['ritaccontorivinpsinc']."'";
 }

 if(is_array($_OTHER_CDINFO))
 {
  if($_OTHER_CDINFO['agentid'])					$cdinfo.= ",agentid='".$_OTHER_CDINFO['agentid']."'";
  if($_OTHER_CDINFO['agentcommiss'])			$cdinfo.= ",agentcommiss='".$_OTHER_CDINFO['agentcommiss']."'";
  if($_OTHER_CDINFO['paymentmode'])				$cdinfo.= ",paymentmode='".$_OTHER_CDINFO['paymentmode']."'";
  if($_OTHER_CDINFO['banksupport'])				$cdinfo.= ",banksupport='".$_OTHER_CDINFO['banksupport']."'";
  if($_OTHER_CDINFO['ourbanksupport'])			$cdinfo.= ",ourbanksupport='".$_OTHER_CDINFO['ourbanksupport']."'";
  if($_OTHER_CDINFO['status'])					$cdinfo.= ",status='".$_OTHER_CDINFO['status']."'";
  if($_OTHER_CDINFO['statusextra'])				$cdinfo.= ",statusextra='".$_OTHER_CDINFO['statusextra']."'";
  if($_OTHER_CDINFO['tag'])						$cdinfo.= ",tag='".$_OTHER_CDINFO['tag']."'";
  if($_OTHER_CDINFO['division'])				$cdinfo.= ",division='".$_OTHER_CDINFO['division']."'";
  if($_OTHER_CDINFO['docrefap'])				$cdinfo.= ",docrefap='".$_OTHER_CDINFO['docrefap']."'";
  if($_OTHER_CDINFO['docrefid'])				$cdinfo.= ",docrefid='".$_OTHER_CDINFO['docrefid']."'";
  if($_OTHER_CDINFO['extdocref'])				$cdinfo.= ",extdocref='".$_OTHER_CDINFO['extdocref']."'";
 }

 if($cdinfo)
 {
  $ret = GShell("dynarc edit-item -ap 'commercialdocs' -id '".$docInfo['id']."' -extset `cdinfo.".ltrim($cdinfo,',')."`", $sessid, $shellid);
  if($ret['error']){$out.= "Unable to update document info!\n".$ret['message']; return array('message'=>$out,'error'=>$ret['error']); }
 }
 /* EOF - IMPORT OTHER INFO FROM XML */


 /* IMPORT ELEMENTS */
 $out.= "Import elements...";
 reset($elements);
 while(list($c, $el) = each($elements))
 {
  if(!$el['id'] && ($el['type'] != 'note') && ($el['type'] != 'message') && $autoFindProducts)
  {
   $fields = array('code'=>$el['code'], 'sku'=>$el['sku'], 'asin'=>$el['asin'], 'ean'=>$el['ean'], 'gcid'=>$el['gcid'], 'gtin'=>$el['gtin'],
	'upc'=>$el['upc'], 'name'=>$el['name']);

   $cmd = "gmart find-product";
   reset($fields);
   while(list($k,$v)=each($fields))
	$cmd.= " -".$k." `".$v."`";

   $ret = GShell($cmd, $sessid, $shellid);
   if(!$ret['error'])
   {
	$el['ap'] = $ret['outarr']['ap'];
	$el['id'] = $ret['outarr']['id'];
   }

   if(!$el['id'] && ($el['type'] != 'note') && ($el['type'] != 'message') && $autoRegisterProducts)
   {
	/* REGISTER PRODUCT */
	$el['at'] = "gmart";
	$el['ap'] = $defaultGMartAP;
	$cmd = "dynarc new-item -ap '".$el['ap']."'".($defaultGMartCatId ? " -cat '".$defaultGMartCatId."'" : "")." -group gmart -name `".$el['name']."`";
	if($el['code'])			$cmd.= " -code-str `".$el['code']."`";
	if($el['description'])	$cmd.= " -desc `".$el['description']."`";
	
    $extset = "";

	$gmartSet = ",model='''".($el['model'] ? $el['model'] : $el['name'])."'''";
	if($el['brand'])	$gmartSet.= ",brand='''".$el['brand']."'''";
	if($el['barcode'])	$gmartSet.= ",barcode='".$el['barcode']."'";
	if($el['mancode'])	$gmartSet.= ",mancode='".$el['mancode']."'";
	if($el['location'])	$gmartSet.= ",location='''".$el['location']."'''";
	if($el['units'])	$gmartSet.= ",units='".$el['units']."'";
	if($el['weight'])	$gmartSet.= ",weight='".$el['weight']."'";
	if($el['weightunits'])	$gmartSet.= ",weightunits='".$el['weightunits']."'";	
	if($el['division'])	$gmartSet.= ",division='".$el['division']."'";
	if($el['sku'])		$gmartSet.= ",sku='".$el['sku']."'";
	if($el['skuref'])	$gmartSet.= ",skuref='".$el['skuref']."'";
	if($el['asin'])		$gmartSet.= ",asin='".$el['asin']."'";
	if($el['ean'])		$gmartSet.= ",ean='".$el['ean']."'";
	if($el['gcid'])		$gmartSet.= ",gcid='".$el['gcid']."'";
	if($el['gtin'])		$gmartSet.= ",gtin='".$el['gtin']."'";
	if($el['upc'])		$gmartSet.= ",upc='".$el['upc']."'";

	if($gmartSet)		$extset.= ",gmart.".ltrim($gmartSet,",");

	if($el['price'])	$extset.= ",pricing.baseprice='".$el['price']."'".($el['vat'] ? ",vat='".$el['vat']."'" : "").",autosetpricelists=1";

    if($extset)			$cmd.= " -extset `".ltrim($extset,",")."`";

	
	$ret = GShell($cmd, $sessid, $shellid);
	if(!$ret['error'])
	 $el['id'] = $ret['outarr']['id'];

    /* EOF - REGISTER PRODUCT */
   }
  }

  
  if($el['ap'] && $el['id'])
  {
   $ret = GShell("commercialdocs get-full-info -type '".$el['at']."' -ap `".$el['ap']."` -id `".$el['id']."`",$sessid,$shellid);
   if($ret['error'])
    return $ret;
   $itemInfo = $ret['outarr'];

   
   $qry = ",refap='".$el['ap']."'";
   $qry.= ",refid='".$el['id']."'";
   $qry.= ",code='".($el['code'] ? $el['code'] : $itemInfo['code_str'])."'";
   $qry.= ",vencode='".($el['vencode'] ? $el['vencode'] : '')."'";
   $qry.= ",name='''".($el['name'] ? $el['name'] : $itemInfo['name'])."'''";
   $qry.= ",qty='".$el['qty']."'";
   $qry.= ",extraqty='".$el['extraqty']."'";
   $qry.= ",price='".($el['price'] ? $el['price'] : $itemInfo['finalprice'])."'";
   $qry.= ",vatid='".($el['vatid'] ? $el['vatid'] : $itemInfo['vatid'])."'";
   $qry.= ",vattype='".($el['vattype'] ? $el['vattype'] : $itemInfo['vattype'])."'";
   $qry.= ",vat='".(isset($el['vat']) ? $el['vat'] : $itemInfo['vat'])."'";

   if($el['discount'])			$qry.= ",discount='".$el['discount']."'";
   if($el['brand_id'])			$qry.= ",brandid='".$el['brand_id']."'";
   if($el['variant_coltint'])	$qry.= ",coltint='''".$el['variant_coltint']."'''";
   if($el['variant_sizmis'])	$qry.= ",sizmis='''".$el['variant_sizmis']."'''";
   if($el['ritaccapply'])		$qry.= ",ritaccapply='".$el['ritaccapply']."'";
   if($el['ccpapply'])			$qry.= ",ccpapply='".$el['ccpapply']."'";
   if($el['xmldata'])			$qry.= ",xmldata='''".$el['xmldata']."'''";

   $ret = GShell("dynarc edit-item -ap `commercialdocs` -id `".$docInfo['id']."` -extset `cdelements.type='".($el['type'] ? $el['type'] : 'article')."'".$qry."`",$sessid,$shellid);
  }
  else
  {
   if(!$el['type'] && ( $el['sku'] || $el['asin'] || $el['ean'] || $el['gcid'] || $el['gtin'] || $el['upc'] || $el['code'] ))
	$el['type'] = 'article';

   $ret = GShell("dynarc edit-item -ap `commercialdocs` -id `".$docInfo['id']."` -extset `cdelements.type='"
	.($el['type'] ? $el['type'] : 'custom')."',code='".$el['code']."',name='''".$el['name']."''',desc='''".$el['description']."''',qty='"
	.$el['qty']."',price='".$el['price']."',vat='".$el['vat']."',discount='".$el['discount']."',ritaccapply='".$el['ritaccapply']."',ccpapply='"
	.$el['ccpapply']."',xmldata='''".$el['xmldata']."'''`",$sessid,$shellid);
  }
  if($ret['error'])
   return $ret;

  // scarico automatico del magazzino //
  if($el['packid'] && $el['packitemid'])
   GShell("pack download -pack '".$el['packid']."' -id '".$el['packitemid']."' -docap 'commercialdocs' -docid '".$docInfo['id']."'",$sessid,$shellid);
  else if($el['ap'] && $el['id'] && $downloadFromStoreId)
   GShell("store download -ap `".$el['ap']."` -id `".$el['id']."` -qty `".$el['qty']."` -store `".$downloadFromStoreId."` -docap commercialdocs -docid `"
	.$docInfo['id']."`",$sessid,$shellid);
 }
 $out.= "done!\n".count($elements)." elements has been inserted!";
 /* EOF - IMPORT ELEMENTS */

 /* UPDATING TOTALS */
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
function commercialdocs_fastInsertElements($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 
 $_AP = "commercialdocs";
 $_ID = 0;

 $_MODE = "APPEND";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-docid' : {$_ID=$args[$c+1]; $c++;} break;
   case '-xml' : {$xmlData=$args[$c+1]; $c++;} break; // import from xml data
   case '--download-from-store' : {$downloadFromStoreId=$args[$c+1]; $c++;} break;

   case '--append' : $_MODE = "APPEND"; break;
   case '--sum' : $_MODE = "SUM"; break;
   case '--overwrite' : $_MODE = "OVERWRITE"; break;

   default : $tmp=$args[$c]; break; // ap : id X qty , ...
  }

 $elements = array();

 if(!$_ID) return array('message'=>"fast-insert-elements error: You must specify the document id. (with: -docid DOC_ID)", 'error'=>"INVALID_DOC_ID");

 $ret = GShell("dynarc item-info -ap '".$_AP."' -id '".$_ID."'",$sessid,$shellid);
 if($ret['error']) return array('message'=>"fast-insert-elements error: Unable to get document info.\n".$ret['message'], 'error'=>$ret['error']);
 $docInfo = $ret['outarr'];
 if(!$docInfo['modinfo']['can_write']) return array('message'=>"Permission denied!, Document is not writable.",'error'=>'PERMISSION_DENIED');


 if($tmp)
 {
  $x = explode(",",$tmp);
  $db = new AlpaDatabase();
  for($c=0; $c < count($x); $c++)
  {
   $xx = explode(":",$x[$c]);
   $ap = $xx[0];
   $at = "";
   $xxx = explode("X",$xx[1]);
   $id = $xxx[0];
   $qty = $xxx[1];

   // get archive type
   $db->RunQuery("SELECT archive_type FROM dynarc_archives WHERE tb_prefix='".$ap."' AND trash='0' LIMIT 1");
   $db->Read();
   $at = $db->record['archive_type'];
   $type = "";
   switch($at)
   {
    case 'gmart' : 		$type='article'; break;
    case 'gproducts' : 	$type='finalproduct'; break;
    case 'gpart' : 		$type='component'; break;
    case 'gmaterial' : 	$type='material'; break;
    case 'lottomatica' : $type='lottomatica'; break;
    case 'gbook' : 		$type='book'; break;
   }
   $elements[] = array('ap'=>$ap, 'at'=>$at, 'type'=>$type, 'id'=>$id, 'qty'=>$qty);
  }
  $db->Close();
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


 $out.= "Import elements...";
 for($c=0; $c < count($elements); $c++)
 {
  $el = $elements[$c];
  if($el['ap'] && $el['id'])
  {
   $ret = GShell("commercialdocs get-full-info -type '".$el['at']."' -ap `".$el['ap']."` -id `".$el['id']."`",$sessid,$shellid);
   if($ret['error'])
    return $ret;
   $itemInfo = $ret['outarr'];

   $qry = ",refap='".$el['ap']."'";
   $qry.= ",refid='".$el['id']."'";
   $qry.= ",code='".($el['code'] ? $el['code'] : $itemInfo['code_str'])."'";
   $qry.= ",vencode='".($el['vencode'] ? $el['vencode'] : '')."'";
   $qry.= ",name='''".($el['name'] ? $el['name'] : $itemInfo['name'])."'''";
   $qry.= ",qty='".$el['qty']."'";
   $qry.= ",extraqty='".$el['extraqty']."'";
   $qry.= ",price='".($el['price'] ? $el['price'] : $itemInfo['finalprice'])."'";
   $qry.= ",vatid='".($el['vatid'] ? $el['vatid'] : $itemInfo['vatid'])."'";
   $qry.= ",vattype='".($el['vattype'] ? $el['vattype'] : $itemInfo['vattype'])."'";
   $qry.= ",vat='".(isset($el['vat']) ? $el['vat'] : $itemInfo['vat'])."'";
   if($el['discount'])    			$qry.= ",discount='".$el['discount']."'";
   if($el['brand_id'])				$qry.= ",brandid='".$el['brand_id']."'";
   if($el['variant_coltint'])		$qry.= ",coltint='''".$el['variant_coltint']."'''";
   if($el['variant_sizmis'])		$qry.= ",sizmis='''".$el['variant_sizmis']."'''";
   if($el['ritaccapply'])			$qry.= ",ritaccapply='".$el['ritaccapply']."'";
   if($el['ccpapply'])				$qry.= ",ccpapply='".$el['ccpapply']."'";
   if($el['xmldata'])				$qry.= ",xmldata='''".$el['xmldata']."'''";

   if(($_MODE == "SUM") || ($_MODE == "OVERWRITE"))
   {
	$db = new AlpaDatabase();
	$db->RunQuery("SELECT id,qty FROM dynarc_".$_AP."_elements WHERE item_id='".$docInfo['id']."' AND ref_ap='".$el['ap']."' AND ref_id='".$el['id']."'");
	if($db->Read())
	{
	 switch($_MODE)
	 {
	  case 'SUM' : $db->RunQuery("UPDATE dynarc_".$_AP."_elements SET qty='".($db->record['qty']+$el['qty'])."' WHERE id='".$db->record['id']."'"); break;
	  case 'OVERWRITE' : $db->RunQuery("UPDATE dynarc_".$_AP."_elements SET qty='".$el['qty']."' WHERE id='".$db->record['id']."'"); break;
	 }
	}
	else
	 $ret = GShell("dynarc edit-item -ap `commercialdocs` -id `".$docInfo['id']."` -extset `cdelements.type='".$el['type']."'".$qry."`",$sessid,$shellid);
	$db->Close();
   } // EOF - if mode = SUM or OVERWRITE
   else // if mode = APPEND
   {
    $ret = GShell("dynarc edit-item -ap `commercialdocs` -id `".$docInfo['id']."` -extset `cdelements.type='".$el['type']."'".$qry."`",$sessid,$shellid);
    if($ret['error']) return $ret;
   }
  } // EOF - if el['ap'] && el['id']
  else
  {
   if(($_MODE == "SUM") || ($_MODE == "OVERWRITE"))
   {
	$db = new AlpaDatabase();
	$qry = "";
	if($el['code'])				$qry.= " OR code='".$db->Purify($el['code'])."'";
	if($el['name'])				$qry.= " OR name='".$db->Purify($el['name'])."'";
	if($qry)
	{
	 $db->RunQuery("SELECT id,qty FROM dynarc_".$_AP."_elements WHERE item_id='".$docInfo['id']."' AND (".ltrim($qry,' OR ').") ORDER BY ordering DESC LIMIT 1");
	 if($db->Read())
	 {
	  switch($_MODE)
	  {
	   case 'SUM' : $db->RunQuery("UPDATE dynarc_".$_AP."_elements SET qty='".($db->record['qty']+$el['qty'])."' WHERE id='".$db->record['id']."'"); break;
	   case 'OVERWRITE' : $db->RunQuery("UPDATE dynarc_".$_AP."_elements SET qty='".$el['qty']."' WHERE id='".$db->record['id']."'"); break;
	  }
	 }
	 else
	 {
	  $ret = GShell("dynarc edit-item -ap `commercialdocs` -id `".$docInfo['id']."` -extset `cdelements.type='custom',code='''"
		.$el['code']."''',name='''".$el['name']."''',qty='".$el['qty']."',price='".$el['price']."',vat='".$el['vat']."',discount='"
		.$el['discount']."',ritaccapply='".$el['ritaccapply']."',ccpapply='".$el['ccpapply']."',xmldata='''".$el['xmldata']."'''`",$sessid,$shellid);
      if($ret['error']) return $ret;
	 }
	} // EOF - if $qry
	else
	{
     $ret = GShell("dynarc edit-item -ap `commercialdocs` -id `".$docInfo['id']."` -extset `cdelements.type='note',desc='''"
		.$el['name']."'''`",$sessid,$shellid);
     if($ret['error']) return $ret;
	}
	$db->Close();
   } // EOF - if mode = SUM or OVERWRITE
   else
   {	   
    $ret = GShell("dynarc edit-item -ap `commercialdocs` -id `".$docInfo['id']."` -extset `cdelements.type='custom',code='''"
	.$el['code']."''',name='''".$el['name']."''',qty='".$el['qty']."',price='".$el['price']."',vat='".$el['vat']."',discount='"
	.$el['discount']."',ritaccapply='".$el['ritaccapply']."',ccpapply='".$el['ccpapply']."',xmldata='''".$el['xmldata']."'''`",$sessid,$shellid);
    if($ret['error']) return $ret;
   }
  }

  // scarico automatico del magazzino //
  if($el['packid'] && $el['packitemid'])
   GShell("pack download -pack '".$el['packid']."' -id '".$el['packitemid']."' -docap 'commercialdocs' -docid '".$docInfo['id']."'",$sessid,$shellid);
  else if($el['ap'] && $el['id'] && $downloadFromStoreId)
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
function commercialdocs_generateFromContract($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 include_once($_BASE_PATH."include/i18n.php");
 loadLanguage("calendar");

 // Genera fattura da contratto tipo nuovo.
 $contractAp = "contracts";
 $destDocType = "INVOICES";
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$contractAp=$args[$c+1]; $c++;} break;
   case '-id' : {$contractId=$args[$c+1]; $c++;} break;
   case '-scheduleid' : {$scheduleId=$args[$c+1]; $c++;} break;
   case '-destdoctype' : {$destDocType=$args[$c+1]; $c++;} break;
   
   case '--include-elements' : $includeElements=true; break;	/* TODO: se include gli elementi deve aggiustare il canone */
  }

 // Get contract info
 $ret = GShell("dynarc item-info -ap '".$contractAp."' -id '".$contractId."' -extget `contractinfo,cdelements`",$sessid,$shellid);
 if($ret['error']) return array('message'=>"Generate invoice from contract failed!\n".$ret['message'], 'error'=>$ret['error']);
 $contractInfo = $ret['outarr'];

 // Get schedule info
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$contractAp."_schedule WHERE id='".$scheduleId."'");
 $db->Read();
 $scheduleInfo = $db->record;
 // get next schedule expiry
 $db->RunQuery("SELECT expiry FROM dynarc_".$contractAp."_schedule WHERE item_id='".$contractId."' AND expiry>'".$scheduleInfo['expiry']."' ORDER BY expiry ASC LIMIT 1");
 if($db->Read())
  $nextExpiry = $db->record['expiry'];
 $db->Close();

 $db = new AlpaDatabase();
 if($scheduleInfo['canon_vatid'])
 {
  // get canon vat type
  $db->RunQuery("SELECT vat_type FROM dynarc_vatrates_items WHERE id='".$scheduleInfo['canon_vatid']."'");
  $db->Read();
  $scheduleInfo['canon_vattype'] = $db->record['vat_type'];
 }
 if($scheduleInfo['commiss_vatid'])
 {
  // get commiss vat type
  $db->RunQuery("SELECT vat_type FROM dynarc_vatrates_items WHERE id='".$scheduleInfo['commiss_vatid']."'");
  $db->Read();
  $scheduleInfo['commiss_vattype'] = $db->record['vat_type'];
 }
 $db->Close();


 // Generate invoice
 $ret = GShell("dynarc new-item -ap commercialdocs -ct '".strtoupper($destDocType)."' -group 'commdocs-".strtolower($destDocType)."' -extset `cdinfo.subjectid='".$contractInfo['subject_id']."',subject='''".$docInfo['subject_name']."''',docrefap='".$contractAp."',docrefid='"
	.$contractId."'`",$sessid,$shellid);
 if($ret['error']) return array('message'=>"Generate invoice from contract failed!\n".$ret['message'], 'error'=>$ret['error']);
 $docInfo = $ret['outarr'];

 // Insert reference
 $text1 = "Da contratto stipulato in data ".date('d/m/Y',strtotime($contractInfo['start_date']));
 $text2 = "Periodo: mese di ".i18n("MONTH-".date('n',strtotime($scheduleInfo['expiry'])))." ".date('Y',strtotime($scheduleInfo['expiry']))." - ";
 $text2.= $nextExpiry ? i18n("MONTH-".date('n',strtotime($nextExpiry)))." ".date('Y',strtotime($nextExpiry)) : "fine contratto";

 GShell("dynarc edit-item -ap commercialdocs -id '".$docInfo['id']."' -extset `cdelements.type='note',desc='''".$text1."'''`",$sessid,$shellid);
 GShell("dynarc edit-item -ap commercialdocs -id '".$docInfo['id']."' -extset `cdelements.type='custom',name='''".$text2."''',qty='1',price='"
	.$scheduleInfo['canon']."',vatid='".$scheduleInfo['canon_vatid']."',vatrate='".$scheduleInfo['canon_vatrate']."',vattype='"
	.$scheduleInfo['canon_vattype']."',bypass-vatregister-update=true`",$sessid,$shellid);
 // insert commissions
 if($scheduleInfo['commiss'] > 0)
  GShell("dynarc edit-item -ap commercialdocs -id '".$docInfo['id']."' -extset `cdelements.type='custom',name='''"
	.($contractInfo['commiss_title'] ? $contractInfo['commiss_title'] : 'commissione bancaria')."''',qty='1',price='"
	.$scheduleInfo['commiss']."',vatid='".$scheduleInfo['commiss_vatid']."',vatrate='".$scheduleInfo['commiss_vatrate']."',vattype='"
	.$scheduleInfo['commiss_vattype']."',bypass-vatregister-update=true`",$sessid,$shellid);

 if($includeElements)
 {
  // Insert elements
  for($c=0; $c < count($contractInfo['elements']); $c++)
  {
   $el = $contractInfo['elements'][$c];
   GShell("dynarc edit-item -ap `commercialdocs` -id `".$docInfo['id']."` -extset `cdelements.type='".$el['type']."',refap='".$el['ref_ap']."',refid='"
	.$el['ref_id']."',code='".$el['code']."',vencode='".$el['vencode']."',sn='".$el['serialnumber']."',lot='".$el['lot']."',accountid='"
	.$el['account_id']."',name='''".$el['name']."''',desc='''".$el['desc']."''',qty='".$el['qty']."',extraqty='".$el['extraqty']."',price='"
	.$el['price']."',discount='".$el['discount']."',vatrate='".$el['vatrate']."',vatid='".$el['vatid']."',vattype='"
	.$el['vattype']."',units='".$el['units']."',pricelistid='".$el['pricelist_id']."',coltint='''".$el['variant_coltint']."''',sizmis='''"
	.$el['variant_sizmis']."''',ritaccapply='".$el['ritaccapply']."',ccpapply='".$el['ccpapply']."',xmldata='''".$el['xmldata']."''',bypass-vatregister-update=true`");
  }
 }

 $out.= "The invoice has been created! ID=".$docInfo['id']."\n";
 
 // Update totals 
 $out.= "Updating totals...";
 $ret = GShell("commercialdocs updatetotals -id '".$docInfo['id']."'",$sessid,$shellid);
 if($ret['error']) $out.= "failed\n".$ret['message'];
 else $out.= "\ndone!\n".$ret['message'];

 $outArr = $docInfo;

 // Update schedule info
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$contractAp."_schedule SET invoice_id='".$docInfo['id']."',invoice_name='".$db->Purify($docInfo['name'])."' WHERE id='"
	.$scheduleId."'");
 $db->Close();

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_renewsOrder($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$orderId=$args[$c+1]; $c++;} break;
  }

 if(!$orderId)
  return array("message"=>"You must specify the document id. (with: -id DOC_ID)","error"=>"INVALID_DOCUMENT");

 // Get document info
 $ret = GShell("dynarc item-info -ap commercialdocs -id '".$orderId."' -extget `cdinfo,cdelements` -get `freq,next_expiry`",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 $orderInfo = $ret['outarr'];

 // Generate new order
 $cmd = "dynarc new-item -ap commercialdocs -cat '".$orderInfo['cat_id']."' -alias `".$orderInfo['aliasname']."` -extset `cdinfo.";
 if($orderInfo['subject_id'])
  $cmd.= "subjectid='".$orderInfo['subject_id']."'";
 else
  $cmd.= "subject='''".$orderInfo['subject_name']."'''";
 $cmd.= ",referenceid='".$orderInfo['reference_id']."'";
 $cmd.= ",paymentmode='".$orderInfo['paymentmode']."'";
 $cmd.= ",banksupport='".$orderInfo['banksupport_id']."'";
 $cmd.= ",pricelist='".$orderInfo['pricelist_id']."'";
 $cmd.= ",tag='".$orderInfo['tag']."'";
 $cmd.= ",division='".$orderInfo['division']."'";
 $cmd.= ",validity-date='".$orderInfo['validity_date']."'";
 $cmd.= ",charter-datefrom='".$orderInfo['charter_datefrom']."'";
 $cmd.= ",charter-dateto='".$orderInfo['charter_dateto']."'";

 // Shipping //
 $cmd.= ",ship-subject-id='".$orderInfo['ship_subject_id']."'";
 $cmd.= ",ship-contact-id='".$orderInfo['ship_contact_id']."'";
 $cmd.= ",ship-recp='''".$orderInfo['ship_recp']."'''";
 $cmd.= ",ship-addr='''".$orderInfo['ship_addr']."'''";
 $cmd.= ",ship-city='''".$orderInfo['ship_city']."'''";
 $cmd.= ",ship-zip='".$orderInfo['ship_zip']."'";
 $cmd.= ",ship-prov='".$orderInfo['ship_prov']."'";
 $cmd.= ",ship-cc='".$orderInfo['ship_cc']."'";

 // Other //
 $cmd.= ",cartage='".$orderInfo['cartage']."'";
 $cmd.= ",packing-charges='".$orderInfo['packing_charges']."'";
 $cmd.= ",location='''".$orderInfo['location']."'''";
 $cmd.= "`";

 // calculate next expiry
 $nextExpiry = "";
 if($orderInfo['freq'] && $orderInfo['next_expiry'])
 {
  if($orderInfo['freq'] == 12)
   $nextExpiry = date('Y-m-d',strtotime("+1 year",strtotime($orderInfo['next_expiry'])));
  else
   $nextExpiry = date('Y-m-d',strtotime("+".$orderInfo['freq']." months",strtotime($orderInfo['next_expiry'])));
 }
 $cmd.= " -set `freq='".$orderInfo['freq']."',next_expiry='".$nextExpiry."'`";

 $ret = GShell($cmd,$sessid,$shellid);
 if($ret['error'])
  return $ret;
 $docInfo = $ret['outarr'];

 // Copy elements // 
 for($i=0; $i < count($orderInfo['elements']); $i++)
 {
  $el = $orderInfo['elements'][$i];
  $ret = GShell("dynarc edit-item -ap `commercialdocs` -id `".$docInfo['id']."` -extset `cdelements.type='"
	.$el['type']."',refap='".$el['ref_ap']."',refid='".$el['ref_id']."',code='".$el['code']."',vencode='"
	.$el['vencode']."',sn='".$el['serialnumber']."',lot='".$el['lot']."',accountid='".$el['account_id']."',name='''"
	.$el['name']."''',desc='''".$el['desc']."''',qty='".$el['qty']."',extraqty='".$el['extraqty']."',price='"
	.$el['price']."',discount='".$el['discount']."',discount2='".$el['discount2']."',discount3='".$el['discount3']."',vatrate='"
	.$el['vatrate']."',vatid='".$el['vatid']."',vattype='".$el['vattype']."',units='".$el['units']."',pricelistid='"
	.$el['pricelist_id']."',brandid='".$el['brand_id']."',vendorid='".$el['vendor_id']."',vendorprice='"
	.$el['vendor_price']."',coltint='''".$el['variant_coltint']."''',sizmis='''".$el['variant_sizmis']."''',ritaccapply='"
	.$el['ritaccapply']."',ccpapply='".$el['ccpapply']."',xmldata='''".$el['xmldata']."''',bypass-vatregister-update=true`");
  if($ret['error'])
   return $ret;
 }

 // Update totals //
 $ret = GShell("commercialdocs updatetotals -id '".$docInfo['id']."'",$sessid,$shellid);
 if($ret['error'])
  return $ret;

 // Aggiorna il vecchio ordine //
 $ret = GShell("dynarc edit-item -ap commercialdocs -id '".$orderInfo['id']."' -set `renewal_date='".date('Y-m-d',$orderInfo['ctime'])."',ren_doc_id='"
	.$docInfo['id']."'`",$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $outArr = $docInfo;
 $out.= "done! Order has been renewed. id=".$docInfo['id'];

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_convert($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 
 $_INHERIT_FIELDS = array('subject','shipping','reference','agent','intdocref','paymentmode','description','attachments','expenses','discrebatestamp');
 $_DOC_REF_TITLE = "";
 $_INTERNAL_DOC_REF_AP = "commercialdocs";
 $_INTERNAL_DOC_REF_ID = 0;

 $destCatId = 0;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-type' : {$type=$args[$c+1]; $c++;} break;		// tipologia di documento
   case '-cat' : case '-destcat' : {$destCatId=$args[$c+1]; $c++; } break; // si può specificare la categoria (o sottocategoria) di dest.
   case '-status' : {$status=$args[$c+1]; $c++;} break;

   /* options for convert from other document kinds */
   case '-from' : {$from=$args[$c+1]; $c++;} break; // <-- specify archive prefix
   case '-inherit' : {$_INHERIT_FIELDS=explode(",",$args[$c+1]); $c++;} break;
   case '-docreftitle' : {$_DOC_REF_TITLE=$args[$c+1]; $c++;} break;
   case '-docrefap' : case '-intdocrefap' : {$_INTERNAL_DOC_REF_AP=$args[$c+1]; $c++;} break;
   case '-docrefid' : case '-intdocrefid' : {$_INTERNAL_DOC_REF_ID=$args[$c+1]; $c++;} break;
  }

 if(!$id)
  return array('message'=>"Convert failed! You must specify the ID of the document to be convert. (with -id DOCUMENT_ID)",'error'=>"INVALID_DOCUMENT");

 if(!$type && !$destCatId)
  return array('message'=>"Convert failed! You must specify the type of document to be convert to. (with -type DOCUMENT_TYPE)",'error'=>"INVALID_DOCUMENT_TYPE");

 switch($from)
 {
  case 'contracts' : return commercialdocs_convertFromContract($args, $sessid, $shellid); break; // contratto tipo vecchio
 }


 /* CONVERT FROM COMMERCIALDOCS DOCUMENTS */
 $ret = GShell("dynarc item-info -ap commercialdocs -id `".$id."` -extget `cdinfo,cdelements,mmr`",$sessid, $shellid);
 if($ret['error'])
  return array('message'=>"Convert failed! ".$ret['message'], $ret['error']);

 $docInfo = $ret['outarr'];
 

 if(in_array("attachments",$_INHERIT_FIELDS))
 {
  // get attachments
  $ret = GShell("dynattachments list -ap 'commercialdocs' -refid ".$docInfo['id'],$sessid,$shellid);
  $docInfo['attachments'] = $ret['outarr']['items'];
 }


 if(!$destCatId && $docInfo['code_ext'])
 {
  // verifica se esiste una cartella di destinazione con lo stesso tag //
  $ret = GShell("dynarc cat-info -ap commercialdocs -pt '".strtoupper($type)."' -tag '".$docInfo['code_ext']."'",$sessid,$shellid);
  if(!$ret['error'])
   $destCatId = $ret['outarr']['id'];
 }

 if($destCatId && !$type)
 {
  // get the root cat tag from destCatId
  $ret = GShell("dynarc get-root-cat -ap commercialdocs -id '".$destCatId."'", $sessid, $shellid);
  if($ret['error']) return array('message'=>"Commercialdocs convert failed! Unable to get root category info for #"
	.$destCatId."\n".$ret['message'], 'error'=>$ret['error']);
  $type = $ret['outarr']['tag'];
 }

 $query = "dynarc new-item -ap `commercialdocs` ".($destCatId ? "-cat '".$destCatId."'" : "-ct `".strtoupper($type)."`")
	." -group commdocs-".strtolower($type).(in_array("description",$_INHERIT_FIELDS) ? " -desc `".$docInfo['desc']."`" : "")
	." -extset `cdinfo.subjectid='".(in_array("subject",$_INHERIT_FIELDS) ? $docInfo['subject_id'] : '0')."'";

 if(in_array("paymentmode",$_INHERIT_FIELDS))
 {
  if($docInfo['payment_date'] != "0000-00-00")
   $query.= ",payment-date='".$docInfo['payment_date']."',status='".($status ? $status : "10")."'";
  else if($status)
   $query.= ",status='".$status."'";
  if($docInfo['paymentmode'])
   $query.= ",paymentmode='".$docInfo['paymentmode']."'";
  if($docInfo['banksupport_id'])
   $query.= ",banksupport='".$docInfo['banksupport_id']."'";
 }
 else if($status)
  $query.= ",status='".$status."'";

 if(in_array("subject",$_INHERIT_FIELDS))
  $query.= ",subject=\"".$docInfo['subject_name']."\"";
 if(in_array("reference",$_INHERIT_FIELDS) && $docInfo['reference_id'])
  $query.= ",referenceid='".$docInfo['reference_id']."'";
 if(in_array("agent",$_INHERIT_FIELDS))
 {
  if($docInfo['agent_id'])
   $query.= ",agentid='".$docInfo['agent_id']."'";
  if($docInfo['agent_commiss'])
   $query.= ",agentcommiss='".$docInfo['agent_commiss']."'";
 }
 if($docInfo['pricelist_id'])
  $query.= ",pricelist='".$docInfo['pricelist_id']."'";
 if($docInfo['division'])
  $query.= ",division='".$docInfo['division']."'";
 if($docInfo['location'])
  $query.= ",location=\"".$docInfo['location']."\"";

 if($_INTERNAL_DOC_REF_AP && $_INTERNAL_DOC_REF_ID)
  $query.= ",docrefap='".$_INTERNAL_DOC_REF_AP."',docrefid='".$_INTERNAL_DOC_REF_ID."'";
 else if(in_array("intdocref",$_INHERIT_FIELDS))
  $query.= ",docrefap='".$docInfo['docref_ap']."',docrefid='".$docInfo['docref_id']."'";


 if(in_array("shipping",$_INHERIT_FIELDS))
 {
  /* Shipping */
  $query.= ",ship-subject-id='".$docInfo['ship_subject_id']."'";
  $query.= ",ship-contact-id='".$docInfo['ship_contact_id']."'";
  $query.= ",ship-recp=\"".$docInfo['ship_recp']."\"";
  $query.= ",ship-addr=\"".$docInfo['ship_addr']."\"";
  $query.= ",ship-city=\"".$docInfo['ship_city']."\"";
  $query.= ",ship-zip='".$docInfo['ship_zip']."'";
  $query.= ",ship-prov='".$docInfo['ship_prov']."'";
  $query.= ",ship-cc='".$docInfo['ship_cc']."'";
 }

 $query.= "`";

 $ret = GShell($query,$sessid,$shellid);

 if($ret['error'])
  return $ret;

 $docId = $ret['outarr']['id'];
 $outArr = $ret['outarr'];

 if(in_array("shipping",$_INHERIT_FIELDS))
 {
  // UPDATE TRANSPORT AND OTHER INFORMATIONS
  $query = "dynarc edit-item -ap commercialdocs -id '".$docId."' -extset `cdinfo.";
  /* Transport */
  $query.= "trans-method='".$docInfo['trans_method']."'";
  $query.= ",trans-shipper=\"".$docInfo['trans_shipper']."\"";
  $query.= ",trans-numplate='".$docInfo['trans_numplate']."'";
  $query.= ",trans-causal=\"".$docInfo['trans_causal']."\"";
  if($docInfo['trans_datetime'])
   $query.= ",trans-date='".date('Y-m-d H:i',$docInfo['trans_datetime'])."'";
  $query.= ",trans-aspect=\"".$docInfo['trans_aspect']."\"";
  $query.= ",trans-num='".$docInfo['trans_num']."'";
  $query.= ",trans-weight='".$docInfo['trans_weight']."'";
  $query.= ",trans-freight='".$docInfo['trans_freight']."'";
  $query.= ",cartage='".$docInfo['cartage']."'";
  $query.= ",packing-charges='".$docInfo['packing_charges']."'";
  $query.= ",collection-charges='".$docInfo['collection_charges']."'";
  $query.= "`";
  GShell($query,$sessid,$shellid);
 }

 if(in_array("expenses",$_INHERIT_FIELDS))
 {
  $query = "dynarc edit-item -ap commercialdocs -id '".$docId."' -extset `cdinfo.";
  /* Expenses */
  if($docInfo['exp1name'])
   $query.= ",exp1-name=\"".$docInfo['exp1name']."\",exp1-vatid='".$docInfo['exp1vatid']."',exp1-amount='".$docInfo['exp1amount']."'";
  if($docInfo['exp2name'])
   $query.= ",exp2-name=\"".$docInfo['exp2name']."\",exp2-vatid='".$docInfo['exp2vatid']."',exp2-amount='".$docInfo['exp2amount']."'";
  if($docInfo['exp3name'])
   $query.= ",exp3-name=\"".$docInfo['exp3name']."\",exp3-vatid='".$docInfo['exp3vatid']."',exp3-amount='".$docInfo['exp3amount']."'";
  $query.= "`";
  GShell($query,$sessid,$shellid);
 }

 if(in_array("discrebatestamp",$_INHERIT_FIELDS))
 {
  $query = "dynarc edit-item -ap commercialdocs -id '".$docId."' -extset `cdinfo.";
  /* Discounts, rebate and stamp */
  if($docInfo['discount'])
   $query.= ",discount='".$docInfo['discount']."'";
  if($docInfo['discount2'])
   $query.= ",discount2='".$docInfo['discount2']."'";
  if($docInfo['uncondisc'])
   $query.= ",uncondisc='".$docInfo['uncondisc']."'";
  if($docInfo['rebate'])
   $query.= ",rebate='".$docInfo['rebate']."'";
  if($docInfo['stamp'])
   $query.= ",stamp='".$docInfo['stamp']."'";
  $query.= "`";
  GShell($query,$sessid,$shellid);
 }

 if(in_array("paymentmode",$_INHERIT_FIELDS))
 {
  /* MMR */
  if(count($docInfo['mmr']))
  {
   for($c=0; $c < count($docInfo['mmr']); $c++)
   {
    $mmr = $docInfo['mmr'][$c];
    GShell("dynarc edit-item -ap 'commercialdocs' -id '".$docId."' -extset `mmr.date='".$mmr['date']."',desc=\""
	.$mmr['description']."\",incomes='".$mmr['incomes']."',expenses='".$mmr['expenses']."',expire='".$mmr['expire_date']."'"
	.((($mmr['payment_date'] != "0000-00-00") && ($mmr['payment_date'] != "1970-01-01")) ? ",payment='".$mmr['payment_date']."'" : "")
	.",subjectid='".$docInfo['subject_id']."',subject_name=\"".$docInfo['subject_name']."\",resid='".$mmr['res_id']."',riba='"
	.$mmr['riba_id']."'`",$sessid,$shellid);
   }
  }
 }

 if($_DOC_REF_TITLE)
  GShell("dynarc edit-item -ap `commercialdocs` -id `".$docId."` -extset `cdelements.type='note',desc='''".$_DOC_REF_TITLE."'''`",$sessid,$shellid);

 /* INSERT ELEMENTS */
 for($i=0; $i < count($docInfo['elements']); $i++)
 {
  $el = $docInfo['elements'][$i];
  GShell("dynarc edit-item -ap `commercialdocs` -id `".$docId."` -extset `cdelements.type='".$el['type']."',refap='".$el['ref_ap']."',refid='"
	.$el['ref_id']."',code='".$el['code']."',vencode='".$el['vencode']."',sn='".$el['serialnumber']."',lot='".$el['lot']."',accountid='"
	.$el['account_id']."',name='''".$el['name']."''',desc='''".$el['desc']."''',qty='".$el['qty']."',extraqty='".$el['extraqty']."',qtysent='"
	.$el['qty_sent']."',qtydl='".$el['qty_downloaded']."',price='".$el['price']."',discount='".$el['discount']."',discount2='"
	.$el['discount2']."',discount3='".$el['discount3']."',vatrate='".$el['vatrate']."',vatid='".$el['vatid']."',vattype='"
	.$el['vattype']."',units='".$el['units']."',pricelistid='".$el['pricelist_id']."',brandid='".$el['brand_id']."',vendorid='"
	.$el['vendor_id']."',vendorprice='".$el['vendor_price']."',coltint='''".$el['variant_coltint']."''',sizmis='''"
	.$el['variant_sizmis']."''',ritaccapply='".$el['ritaccapply']."',ccpapply='".$el['ccpapply']."',xmldata='''"
	.$el['xmldata']."''',rowdocap='commercialdocs',rowdocid='".$docInfo['id']."',rowrefid='".$el['id']."',bypass-vatregister-update=true`",$sessid,$shellid);
 }
 
 $out.= "The document has been converted! ID=".$docId;

 /* adjust store */
 GShell("commercialdocs fix-book-inc-arts -docid '".$docId."' -action delete",$sessid,$shellid);

 /* UPDATING TOTALS */
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
 
 /* copy attachments */
 for($c=0; $c < count($docInfo['attachments']); $c++)
  GShell("dynattachments add -ap commercialdocs -id '".$docId."' -type `".$docInfo['attachments'][$c]['type']."` -name `"
	.$docInfo['attachments'][$c]['name']."` -url `".$docInfo['attachments'][$c]['url']."` -tag `".$docInfo['attachments'][$c]['tag']."` -tbtag `"
	.$docInfo['attachments'][$c]['tbtag']."` -desc `".$docInfo['attachments'][$c]['description']."`",$sessid,$shellid);

 /* EOF - CONVERT FROM COMMERCIALDOCS DOCUMENTS */

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_duplicate($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-status' : {$status=$args[$c+1]; $c++;} break;
   /* options */
   case '-destcat' : {$destCatId=$args[$c+1]; $c++;} break;
   case '-destct' : {$destCatTag=$args[$c+1]; $c++;} break;
  }

 if(!$id)
  return array('message'=>"Duplicate failed! You must specify the ID of the document to be duplicate. (with -id DOCUMENT_ID)",'error'=>"INVALID_DOCUMENT");


 /* GET SOURCE DOC INFO */
 $ret = GShell("dynarc item-info -ap commercialdocs -id `".$id."` -extget `cdinfo,cdelements,mmr`",$sessid, $shellid);
 if($ret['error'])
  return array('message'=>"Duplicate failed! ".$ret['message'], $ret['error']);
 $docInfo = $ret['outarr'];
 $docType = "";

 // get doc type //
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT tag,parent_id FROM dynarc_commercialdocs_categories WHERE id='".$docInfo['cat_id']."'");
 if($db->Read())
 {
  if($db->record['parent_id'])
  {
   $db->RunQuery("SELECT tag FROM dynarc_commercialdocs_categories WHERE id='".$db->record['parent_id']."'");
   $db->Read();
   $docType = $db->record['tag']; 
  }
  else
   $docType = $db->record['tag'];
 }
 $db->Close();

 if(!$destCatId && !$destCatTag)
  $destCatId = $docInfo['cat_id'];


 /*if($docInfo['code_ext'])
 {
  // verifica se esiste una cartella di destinazione con lo stesso tag //
  $ret = GShell("dynarc cat-info -ap commercialdocs -pt '".strtoupper($type)."' -tag '".$docInfo['code_ext']."'",$sessid,$shellid);
  if(!$ret['error'])
   $destCatId = $ret['outarr']['id'];
 }*/

 $query = "dynarc new-item -ap `commercialdocs` ".($destCatId ? "-cat '".$destCatId."'" : "-ct `".strtoupper($destCatTag)."`")
	." -group commdocs-".strtolower($docType)." -extset `cdinfo.subjectid='".$docInfo['subject_id']."',tag='".$docInfo['tag']."'";
 if($status)
  $query.= ",status='".$status."'";
 $query.= ",subject=\"".$docInfo['subject_name']."\"";
 if($docInfo['reference_id'])
  $query.= ",referenceid='".$docInfo['reference_id']."'";
 if($docInfo['agent_id'])
  $query.= ",agentid='".$docInfo['agent_id']."'";
 if($docInfo['agent_commiss'])
  $query.= ",agentcommiss='".$docInfo['agent_commiss']."'";
 if($docInfo['paymentmode'])
  $query.= ",paymentmode='".$docInfo['paymentmode']."'";
 if($docInfo['banksupport_id'])
  $query.= ",banksupport='".$docInfo['banksupport_id']."'";
 if($docInfo['pricelist_id'])
  $query.= ",pricelist='".$docInfo['pricelist_id']."'";
 if($docInfo['division'])
  $query.= ",division='".$docInfo['division']."'";
 if($docInfo['location'])
  $query.= ",location=\"".$docInfo['location']."\"";

 /* Shipping */
 $query.= ",ship-subject-id='".$docInfo['ship_subject_id']."'";
 $query.= ",ship-contact-id='".$docInfo['ship_contact_id']."'";
 $query.= ",ship-recp=\"".$docInfo['ship_recp']."\"";
 $query.= ",ship-addr=\"".$docInfo['ship_addr']."\"";
 $query.= ",ship-city=\"".$docInfo['ship_city']."\"";
 $query.= ",ship-zip='".$docInfo['ship_zip']."'";
 $query.= ",ship-prov='".$docInfo['ship_prov']."'";
 $query.= ",ship-cc='".$docInfo['ship_cc']."'";

 $query.= "`";

 $ret = GShell($query,$sessid,$shellid);

 if($ret['error'])
  return $ret;

 $docId = $ret['outarr']['id'];
 $outArr = $ret['outarr'];

 // UPDATE TRANSPORT AND OTHER INFORMATIONS
 $query = "dynarc edit-item -ap commercialdocs -id '".$docId."' -extset `cdinfo.";
 /* Transport */
 $query.= "trans-method='".$docInfo['trans_method']."'";
 $query.= ",trans-shipper=\"".$docInfo['trans_shipper']."\"";
 $query.= ",trans-numplate='".$docInfo['trans_numplate']."'";
 $query.= ",trans-causal=\"".$docInfo['trans_causal']."\"";
 $query.= ",trans-aspect=\"".$docInfo['trans_aspect']."\"";
 $query.= ",trans-num='".$docInfo['trans_num']."'";
 $query.= ",trans-weight='".$docInfo['trans_weight']."'";
 $query.= ",trans-freight='".$docInfo['trans_freight']."'";
 $query.= ",cartage='".$docInfo['cartage']."'";
 $query.= ",packing-charges='".$docInfo['packing_charges']."'";
 $query.= ",collection-charges='".$docInfo['collection_charges']."'";

 /* Expenses */
 if($docInfo['exp1name'])
  $query.= ",exp1-name=\"".$docInfo['exp1name']."\",exp1-vatid='".$docInfo['exp1vatid']."',exp1-amount='".$docInfo['exp1amount']."'";
 if($docInfo['exp2name'])
  $query.= ",exp2-name=\"".$docInfo['exp2name']."\",exp2-vatid='".$docInfo['exp2vatid']."',exp2-amount='".$docInfo['exp2amount']."'";
 if($docInfo['exp3name'])
  $query.= ",exp3-name=\"".$docInfo['exp3name']."\",exp3-vatid='".$docInfo['exp3vatid']."',exp3-amount='".$docInfo['exp3amount']."'";

 /* Discounts, rebate and stamp */
 if($docInfo['discount'])
  $query.= ",discount='".$docInfo['discount']."'";
 if($docInfo['discount2'])
  $query.= ",discount2='".$docInfo['discount2']."'";
 if($docInfo['uncondisc'])
  $query.= ",uncondisc='".$docInfo['uncondisc']."'";
 if($docInfo['rebate'])
  $query.= ",rebate='".$docInfo['rebate']."'";
 if($docInfo['stamp'])
  $query.= ",stamp='".$docInfo['stamp']."'";

 $query.= "`";
 GShell($query,$sessid,$shellid);

 /* INSERT ELEMENTS */
 for($i=0; $i < count($docInfo['elements']); $i++)
 {
  $el = $docInfo['elements'][$i];
  GShell("dynarc edit-item -ap `commercialdocs` -id `".$docId."` -extset `cdelements.type='".$el['type']."',refap='".$el['ref_ap']."',refid='"
	.$el['ref_id']."',code='".$el['code']."',vencode='".$el['vencode']."',sn='".$el['serialnumber']."',lot='".$el['lot']."',accountid='"
	.$el['account_id']."',name='''".$el['name']."''',desc='''".$el['desc']."''',qty='".$el['qty']."',extraqty='".$el['extraqty']."',price='"
	.$el['price']."',discount='".$el['discount']."',discount2='".$el['discount2']."',discount3='".$el['discount3']."',vatrate='"
	.$el['vatrate']."',vatid='".$el['vatid']."',vattype='".$el['vattype']."',units='".$el['units']."',pricelistid='"
	.$el['pricelist_id']."',brandid='".$el['brand_id']."',vendorid='".$el['vendor_id']."',vendorprice='"
	.$el['vendor_price']."',coltint='''".$el['variant_coltint']."''',sizmis='''".$el['variant_sizmis']."''',ritaccapply='"
	.$el['ritaccapply']."',ccpapply='".$el['ccpapply']."',xmldata='''".$el['xmldata']."''',bypass-vatregister-update=true`");
 }
 
 $out.= "The document has been converted! ID=".$docId;

 /* UPDATING TOTALS */
 $out.= "\nUpdating totals...";
 $ret = GShell("commercialdocs updatetotals -id '".$docId."' --fix-mmr",$sessid,$shellid);
 if($ret['error'])
  $out.= "failed\n".$ret['message'];
 else
 {
  $out.= "\ndone!\n".$ret['message'];
  $outArr = $ret['outarr'];
 }


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
   case '-xml' : {$xml=$args[$c+1]; $c++;} break;
   default : $tmp = $args[$c]; break;
  }

 if($xml) return commercialdocs_uploadGoodsDeliveredFromXML($args, $sessid, $shellid);

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
  $at = "";
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
  // get archive type
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT archive_type FROM dynarc_archives WHERE tb_prefix='".$ap."' AND trash='0' LIMIT 1");
  $db->Read();
  $at = $db->record['archive_type'];
  $db->Close();
  $type = "";
  switch($at)
  {
   case 'gmart' : 		$type='article'; break;
   case 'gproducts' : 	$type='finalproduct'; break;
   case 'gpart' : 		$type='component'; break;
   case 'gmaterial' : 	$type='material'; break;
   case 'lottomatica' : $type='lottomatica'; break;
   case 'gbook' : 		$type='book'; break;
  }

  $ret = GShell("store upload -store ".$storeId." -ap `".$ap."` -id `".$id."` -qty `".$qty."` -lot `".$lot."` -docap `".$docAP."` -docid `".$docID."`",$sessid,$shellid);
  if($ret['error'])
   $out.= "Warning:".$ret['message']."\n";
  else
   $outArr['items'][] = array('ap'=>$ap, 'id'=>$id, 'qty'=>$qty);

  // decrementa le qtà ordinate
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_".$ap."_items SET incoming=incoming-".$qty." WHERE id='".$id."'");
  $db->Close();

  if($ddtID)
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT * FROM dynarc_".$ap."_items WHERE id='".$id."'");
   $db->Read();
   $ret = GShell("dynarc edit-item -ap `commercialdocs` -id `".$ddtID."` -extset `cdelements.type='".$type."',refap='".$ap."',refid='"
	.$id."',code='".$db->record['code_str']."',name='''".$db->record['name']."''',desc='''".$db->record['description']."''',qty='"
	.$qty."',lot='".$lot."',price='".$db->record['baseprice']."',vatrate='".$db->record['vat']."',units='".$db->record['units']."'`");
   $db->Close();
  }
 }
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_uploadGoodsDeliveredFromXML($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array('ddtlist'=>array());
 $docRefAP = "commercialdocs";
 
 $_ELEMENTS = array();
 $archiveTypes = array();
 $genDDTStores = array();
 $vendorId = 0;
 $_DOC_CT = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-refap' : case '-docrefap' : {$docRefAP=$args[$c+1]; $c++;} break;
   case '-refid' : case '-docrefid' : {$docRefID=$args[$c+1]; $c++;} break;
   case '-vendorid' : {$vendorId=$args[$c+1]; $c++;} break;
   case '-xml' : {$xmlData=$args[$c+1]; $c++;} break;

   case '-gen-ddt-storeid' : {$genDDTStores[$args[$c+1]] = true; $c++;} break;	// genera DDT solo sui magazzini specificati
   case '--auto-gen-ddt' : $autoGenerateDDT = true; break; // genera DDT su tutti i magazzini
  }

 if($docRefID)
 {
  $ret = GShell("dynarc item-info -ap '".$docRefAP."' -id '".$docRefID."' || dynarc get-root-cat -ap '".$docRefAP."' -id *.cat_id", $sessid, $shellid);
  if(!$ret['error']) $_DOC_CT = $ret['outarr']['tag'];
 }

 $xmlData = ltrim(rtrim($xmlData));
 $xml = new GXML();
 if($xml->LoadFromString("<xml>".$xmlData."</xml>"))
 {
  $list = $xml->GetElementsByTagName("item");
  // divido gli articoli per magazzino
  $db = new AlpaDatabase();
  for($c=0; $c < count($list); $c++)
  {
   $a = $list[$c]->toArray();
   if(!$archiveTypes[$a['ap']])
   {
	// get archive type
	$db->RunQuery("SELECT archive_type FROM dynarc_archives WHERE tb_prefix='".$a['ap']."' AND trash='0' LIMIT 1");
    $db->Read();
    $at = $db->record['archive_type'];
    $type = "";
    switch($at)
    {
     case 'gmart' : 		$type='article'; break;
     case 'gproducts' : 	$type='finalproduct'; break;
     case 'gpart' : 		$type='component'; break;
     case 'gmaterial' : 	$type='material'; break;
     case 'lottomatica' :   $type='lottomatica'; break;
     case 'gbook' : 		$type='book'; break;
    }
	$archiveTypes[$a['ap']] = $type;
   }

   $a['type'] = $archiveTypes[$a['ap']];

   // prelevo alcune info dell'articolo
   $db->RunQuery("SELECT code_str,name,description,baseprice,vat,units FROM dynarc_".$a['ap']."_items WHERE id='".$a['id']."'");
   $db->Read();
   $a['code'] = $db->record['code_str'];
   $a['name'] = $db->record['name'];
   $a['desc'] = $db->record['description'];
   $a['price'] = $db->record['baseprice'];
   $a['vatrate'] = $db->record['vat'];
   $a['units'] = $db->record['units'];

   $_ELEMENTS[$a['storeid']][] = $a;
  }
  $db->Close();
 }
 
 reset($_ELEMENTS);
 while(list($storeId,$elements) = each($_ELEMENTS))
 {
  $ddtID = 0;
  $ddtInfo = array();

  if($autoGenerateDDT || $genDDTStores[$storeId])
  {
   // get store name //
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT name FROM stores WHERE id='".$storeId."'");
   $db->Read();
   $storeName = $db->record['name'];
   $db->Close();

   $ret = GShell("dynarc new-item -ap commercialdocs -ct DDT -group commdocs-ddt -set `tag='INTERNAL'` -extset `cdelements.type='note',desc='''Materiale da movimentare a magazzino di ".$storeName."'''`",$sessid,$shellid);
   if($ret['error']) return array('message'=>"Upload goods delivered failed! Unable to generate DDT.\n".$ret['message'], 'error'=>$ret['error']);
   $ddtInfo = $ret['outarr'];
   $ddtID = $ddtInfo['id'];
   $outArr['ddtlist'][] = $ddtInfo;
  }
  
  for($c=0; $c < count($elements); $c++)
  {
   $el = $elements[$c];
   $ret = GShell("store upload -store ".$storeId." -ap `".$el['ap']."` -id `".$el['id']."` -qty `".$el['qty']."` -lot `".$el['lot']."` -coltint `"
	.$el['coltint']."` -sizmis `".$el['sizmis']."` -docap `".$docRefAP."` -docid `".$docRefID."` -vendorid '".$vendorId."'",$sessid,$shellid);
   if($ret['error'])
    $out.= "Warning:".$ret['message']."\n";

   if($_DOC_CT == "VENDORORDERS")
   {
    // decrementa le qtà ordinate
    $db = new AlpaDatabase();
    $db->RunQuery("UPDATE dynarc_".$el['ap']."_items SET incoming=incoming-".$el['qty']." WHERE id='".$el['id']."'");
    $db->Close();
   }

   if($ddtID)
   {
	// prezzo e iva a zero perchè si tratta di un ddt movim. merci.
    $ret = GShell("dynarc edit-item -ap `commercialdocs` -id `".$ddtID."` -extset `cdelements.type='".$el['type']."',refap='"
		.$el['ap']."',refid='".$el['id']."',code='".$el['code']."',name='''".$el['name']."''',desc='''"
		.$el['desc']."''',qty='".$el['qty']."',lot='".$el['lot']."',price='0',vatrate='0',units='"
		.$el['units']."',coltint='".$el['coltint']."',sizmis='".$el['sizmis']."'`");
   }
  }


 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_convertFromContract($args, $sessid, $shellid)
{
 /* Contratto tipo vecchio */
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
	.$el['ref_id']."',code='".$el['code']."',vencode='".$el['vencode']."',sn='".$el['serialnumber']."',lot='".$el['lot']."',accountid='"
	.$el['account_id']."',name='''".$el['name']."''',desc='''".$el['desc']."''',qty='".$el['qty']."',extraqty='".$el['extraqty']."',price='"
	.$el['price']."',discount='".$el['discount']."',vatrate='".$el['vatrate']."',vatid='".$el['vatid']."',vattype='"
	.$el['vattype']."',units='".$el['units']."',pricelistid='".$el['pricelist_id']."',coltint='''".$el['variant_coltint']."''',sizmis='''"
	.$el['variant_sizmis']."''',ritaccapply='".$el['ritaccapply']."',ccpapply='".$el['ccpapply']."',xmldata='''".$el['xmldata']."''',bypass-vatregister-update=true`");
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
 $id = $db->GetInsertId();
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
 
 $_VARIANT = null;
 $_QTY = 1;

 $_PREDEFDISCOUNT_CATPERC = array();
 $_PREDEF_DISCOUNT = 0;

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
   case '-vencode' : {$vencode=$args[$c+1]; $c++;} break;
   case '-vendorid' : {$_VENDORID=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-subjectid' : {$subjectID=$args[$c+1]; $c++;} break;
   case '-pricelistid' : {$pricelistID=$args[$c+1]; $c++;} break;
   case '-get' : {$extraGet=$args[$c+1]; $c++;} break;
   case '-qty' : {$_QTY=$args[$c+1]; $c++;} break;

   case '--get-variants' : $getVariants=true; break;						// Ricava le varianti solo ove possibile
   case '-varname' : case '-variantname' : {$variantName=$args[$c+1]; $c++;} break;
   case '-vartype' : case '-verianttype' : {$variantType=$args[$c+1]; $c++;} break;

   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 if(!$_QTY) $_QTY=1;

 if($subjectID)
 {
  /* returns the price list associated with this customer (or any other subject) */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT cat_id,name,pricelist_id FROM dynarc_rubrica_items WHERE id='".$subjectID."'");
  if(!$db->Read())
   $out.= "Subject #".$subjectID." not found.\n";
  else
  {
   $subjectName = $db->record['name'];
   $ret = GShell("dynarc getrootcat -ap rubrica -id '".$db->record['cat_id']."'",$sessid,$shellid);
   if(!$ret['error'])
	$subjCatInfo = $ret['outarr'];
   if($subjCatInfo['tag'] == "vendors")
	$isVendor = true;
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
   if($vencode)
   {
    // ricerca tramite cod.art. fornitore
    $db->RunQuery("SELECT item_id,vendor_id FROM dynarc_".$ap."_vendorprices WHERE code='".$vencode."'");
    if($db->Read())
    {
     $_AP = $ap;
	 $_ID = $db->record['item_id'];
	 $_VENDORID = $db->record['vendor_id'];
    }
   }
   else
   {
    // ricerca tramite codice interno, nome, barcode
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
	else if($code || $barcode || $codeOrBarcode)
	{
	 // ricerca tra le varianti
	 $query = "SELECT item_id,variant_name,variant_type,code,barcode,mrate,final_price FROM dynarc_".$ap."_varcodes WHERE ";
	 if($code)			$query.= "code='".$code."'";
	 else if($barcode)	$query.= "barcode='".$barcode."'"; 
	 else if($codeOrBarcode)	$query.= "code='".$codeOrBarcode."' OR barcode='".$codeOrBarcode."'";
	 $db->RunQuery($query." LIMIT 1");
	 if($db->Read())
	 {
	  $_AP = $ap;
	  $_ID = $db->record['item_id'];
	  $_VARIANT = array('name'=>$db->record['variant_name'], 'type'=>$db->record['variant_type'], 
		'code'=>$db->record['code'], 'barcode'=>$db->record['barcode'], 
		'mrate'=>$db->record['mrate'], 'finalprice'=>$db->record['final_price']);
	 }
	}

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
   case 'gmart' : 		$_AT = "gmart"; break;
   case 'gproducts' : 	$_AT = "gproducts"; break;
   case 'gpart' : 		$_AT = "gpart"; break;
   case 'gmaterial' : 	$_AT = "gmaterial"; break;
   case 'lottomatica' : $_AT = "lottomatica"; break;
   case 'gserv' : 		$_AT = "gserv"; break;
   case 'gsupplies' : 	$_AT = "gsupplies"; break;
   case 'gbook' : 		$_AT = "gbook"; break;
   default : {
	 $db = new AlpaDatabase();
	 $db->RunQuery("SELECT archive_type FROM dynarc_archives WHERE tb_prefix='".$_AP."'");
	 $db->Read();
	 $_AT = $db->record['archive_type'];
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

  if($getVariants)
  {
   // verifica se su questo archivio è installata l'estensione variants
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT id FROM dynarc_archives WHERE tb_prefix='".$_AP."'");
   $db->Read();
   $db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$db->record['id']."' AND extension_name='variants'");
   if($db->Read())
	$command.= ",variants";
   $db->Close();
  }

  if($subjectID)
   $command.= ",custompricing.subjectid=".$subjectID;
  $command.= "`".($get ? " -get `".ltrim($get,",")."`" : "");
  $ret = GShell($command,$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $itemInfo = $ret['outarr'];

  if($subjectID)
  {
   // Get predefined discount
   $predefDiscountSet = false;
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT cat_id,percentage FROM dynarc_rubrica_predefdiscount WHERE item_id='".$subjectID."' AND ap='".$_AP."'");
   while($db->Read())
   {
	$_PREDEFDISCOUNT_CATPERC[$db->record['cat_id']] = $db->record['percentage'];
   }
   $db->Close();

   $hl = explode(",",ltrim(rtrim($itemInfo['hierarchy'],","),","));
   $hlc = count($hl);
   for($c=0; $c < $hlc; $c++)
   {
	if(isset($_PREDEFDISCOUNT_CATPERC[$hl[$hlc-$c-1]]))
	{
	 $_PREDEF_DISCOUNT = $_PREDEFDISCOUNT_CATPERC[$hl[$hlc-$c-1]];
	 $predefDiscountSet = true;
	 break;
	}
   }
   if(!$predefDiscountSet && $_PREDEFDISCOUNT_CATPERC[0])
	$_PREDEF_DISCOUNT = $_PREDEFDISCOUNT_CATPERC[0];
  }
  

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
  if($_PREDEF_DISCOUNT)
   $discount = $_PREDEF_DISCOUNT;
  else
   $discount = $itemInfo["pricelist_".$pricelistID."_discount"] ? $itemInfo["pricelist_".$pricelistID."_discount"] : 0;
  $finalPrice = $basePrice ? $basePrice + (($basePrice/100)*$markupRate) : 0;
  $finalPrice = $finalPrice ? $finalPrice - (($finalPrice/100)*$discount) : 0;
  $vat = $itemInfo["pricelist_".$pricelistID."_vat"] ? $itemInfo["pricelist_".$pricelistID."_vat"] : $itemInfo['vat'];
 }
 else
 {
  $basePrice = $itemInfo['baseprice'];
  $finalPrice = $basePrice;
  if($_PREDEF_DISCOUNT)
   $finalPrice = $finalPrice ? $finalPrice - (($finalPrice/100)*$_PREDEF_DISCOUNT) : 0;
  $vat = $itemInfo['vat'];
 }

 $itemInfo['pricelist_id'] = $pricelistID;
 $itemInfo['pricelist_name'] = $pricelistName;

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

 /* GET VENDOR CODE AND PRICE */
 $db = new AlpaDatabase();
 if($_VENDORID)
  $db->RunQuery("SELECT code,vendor_id,vendor_name,ship_costs,price,vatrate FROM dynarc_".$_AP."_vendorprices WHERE item_id='".$itemInfo['id']."' AND vendor_id='".$_VENDORID."'");
 else if($isVendor) // ricava solo il prezzo di quel fornitore //
  $db->RunQuery("SELECT code,vendor_id,vendor_name,ship_costs,price,vatrate FROM dynarc_".$_AP."_vendorprices WHERE item_id='".$itemInfo['id']."' AND (vendor_id='".$subjectID."' OR (vendor_id='0' AND vendor_name='".$subjectName."'))");
 else // ricava il prezzo dal primo fornitore che trova
  $db->RunQuery("SELECT code,vendor_id,vendor_name,ship_costs,price,vatrate FROM dynarc_".$_AP."_vendorprices WHERE item_id='".$itemInfo['id']."' ORDER BY id ASC LIMIT 1");
 if($db->Read())
 {
  $itemInfo['vencode'] = $db->record['code'];
  $itemInfo['vendor_id'] = $db->record['vendor_id'];
  $itemInfo['vendor_name'] = $db->record['vendor_name'];
  $itemInfo['ship_costs'] = $db->record['ship_costs'];
  $itemInfo['vendor_price'] = $db->record['price'];
  $itemInfo['vendor_vatrate'] = $db->record['vatrate'];

  /* GET VAT TYPE AND VAT ID */
  $db->RunQuery("SELECT id FROM dynarc_vatrates_items WHERE percentage='".$itemInfo['vendor_vatrate']."' AND vat_type='TAXABLE' AND trash='0' LIMIT 1");
  if(!$db->Read())
  {
   $db->RunQuery("SELECT vat_type,id FROM dynarc_vatrates_items WHERE percentage='".$itemInfo['vendor_vatrate']."' AND trash='0' LIMIT 1");
   if($db->Read())
   {
    $itemInfo['vendor_vatid'] = $db->record['id'];
    $itemInfo['vendor_vattype'] = $db->record['vat_type'];
   }
  }
  else
  {
   $itemInfo['vendor_vatid'] = $db->record['id'];
   $itemInfo['vendor_vattype'] = "TAXABLE";
  }
 }
 $db->Close();

 if($_VARIANT)
 {
  $itemInfo['variant_name'] = $_VARIANT['name'];
  $itemInfo['variant_type'] = $_VARIANT['type'];
  $itemInfo['variant_code'] = $_VARIANT['code'];
  $itemInfo['variant_barcode'] = $_VARIANT['barcode'];
  $itemInfo['variant_mrate'] = $_VARIANT['mrate'];
  $itemInfo['variant_finalprice'] = $_VARIANT['finalprice'];
  if($itemInfo['variant_mrate'])
   $itemInfo['variant_finalprice'] = $itemInfo['finalprice'] + (($itemInfo['finalprice']/100)*$itemInfo['variant_mrate']);
  if($itemInfo['variant_finalprice'] > 0)
   $finalPrice = $itemInfo['variant_finalprice'];	// Bug fix del 21-11-2016.
 }
 else if($variantType && $variantName)
 {
  $db = new AlpaDatabase();
  $query = "SELECT mrate,final_price FROM dynarc_".$_AP."_varcodes WHERE item_id='".$itemInfo['id']."' AND variant_name='".$db->Purify($variantName)."' AND ";
  switch($variantType)
  {
   case 'color' : $query.= "(variant_type='color' OR variant_type='tint')"; break;
   case 'size' : $query.= "(variant_type='size' OR variant_type='dim' OR variant_type='other')"; break;
   default : $query.= "variant_type='".$variantType."'"; break;
  }
  $db->RunQuery($query);
  if(!$db->Error)
  {
   if($db->Read())
   {
	if($db->record['mrate'] > 0)
	 $finalPrice = $finalPrice+(($finalPrice/100)*$db->record['mrate']);
	else if($db->record['final_price'] > 0)
	 $finalPrice = $db->record['final_price'];
   }
  }
  $db->Close();

 }

 /* VERIFICA PREZZO PER QUANTITA */
 /* PRELEVO LA LISTA DI TUTTI I PREZZI LE CUI QTA' STIANO NEL RANGE SPECIFICATO */
 if(!$variantType) $variantType = $_VARIANT ? $_VARIANT['type'] : "";
 if(!$variantName) $variantName = $_VARIANT ? $_VARIANT['name'] : "";
 $list = array();
 $db = new AlpaDatabase();
 $query = "SELECT * FROM dynarc_".$_AP."_pricesbyqty WHERE item_id='".$_ID."' AND qty_from<='".$_QTY."' AND (qty_to=0 OR qty_to>='".$_QTY."')";
 $db->RunQuery($query);
 if(!$db->Error)
 {
  while($db->Read())
  {
   $list[] = array('id'=>$db->record['id'], 'colors'=>$db->record['colors'], 'sizes'=>$db->record['sizes'], 'disc_perc'=>$db->record['disc_perc'],
	'finalprice'=>$db->record['final_price']);
  }
 }
 $db->Close();

 /* DOPODICHE' PRELEVO SOLAMENTE QUELLO CON LA VARIANTE SPECIFICATA */
 if(count($list))
 {
  $ok = false;
  for($c=0; $c < count($list); $c++)
  {
   if($ok) break;
   switch($variantType)
   {
    case 'color' : case 'tint' : {
	 $colors = explode("],[", substr($list[$c]['colors'], 1, -1));
	 if(in_array($variantName, $colors))
	 {
	  if($list[$c]['disc_perc'])
	   $finalPrice = $finalPrice - (($finalPrice/100)*$list[$c]['disc_perc']);
	  else if($list[$c]['finalprice'])
	   $finalPrice = $list[$c]['finalprice'];
	  $ok = true;
	 }
	} break;

    case 'size' : case 'dim' : case 'other' : {
	 $sizes = explode("],[", substr($list[$c]['sizes'], 1, -1));
	 if(in_array($variantName, $sizes))
	 {
	  if($list[$c]['disc_perc'])
	   $finalPrice = $finalPrice - (($finalPrice/100)*$list[$c]['disc_perc']);
	  else if($list[$c]['finalprice'])
	   $finalPrice = $list[$c]['finalprice'];
	  $ok = true;
	 }
	} break;

    default : {
	  if($list[$c]['disc_perc'])
	   $finalPrice = $finalPrice - (($finalPrice/100)*$list[$c]['disc_perc']);
	  else if($list[$c]['finalprice'])
	   $finalPrice = $list[$c]['finalprice'];
	  $ok = true;
	} break;
   }
  }
 }

 if($variantType)
 {
  $itemInfo['variant_type'] = $variantType;
  $itemInfo['variant_name'] = $variantName;
  $itemInfo['variant_finalprice'] = $finalPrice;
 }
 else
 {
  $itemInfo['finalprice'] = $finalPrice;
  $itemInfo['finalpricevatincluded'] = $itemInfo['finalprice']+(($itemInfo['finalprice']/100)*$itemInfo['vat']);
 }
 /* EOF - VERIFICA PREZZO PER QUANTITA */

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
  $out.= "\n";
  $out.= "Vendor: ".($itemInfo['vendor_id'] ? "#".$itemInfo['vendor_id']." - ".$itemInfo['vendor_name'] : $itemInfo['vendor_name'])."\n";
  $out.= "Vendor art. code: ".$itemInfo['vencode']."\n";
  $out.= "Vendor price: ".number_format($itemInfo['vendor_price'],2,",",".")."\n";
  $out.= "Vendor vatrate: ".($itemInfo['vendor_vatrate'] ? $itemInfo['vendor_vatrate']."%" : "")."\n";
  $vpvi = $itemInfo['vendor_price'] ? $itemInfo['vendor_price'] + (($itemInfo['vendor_price']/100)*$itemInfo['vendor_vatrate']) : 0;
  $out.= "Vendor price vat included: ".number_format($vpvi,2,',','.')."\n";

  if($_VARIANT)
  {
   $out.= "\nVariant name: ".$itemInfo['variant_name']."\n";
   $out.= "Variant code: ".$itemInfo['variant_code']."\n";
   $out.= "Variant barcode: ".$itemInfo['variant_barcode']."\n";
   $out.= "Variant markup rate: ".$itemInfo['variant_mrate']."%\n";
   $out.= "Final price: ".number_format($itemInfo['variant_finalprice'],2,',','.')."\n";
  }
  else if($variantType && $variantName)
  {
   $out.= "\nSelected variant name: ".$variantName."\n";
   $out.= "Variant final price: ".number_format($itemInfo['variant_finalprice'],2,',','.')."\n";
  }

 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_getPriceByQty($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $qty = 1;
 $_PLID = 0;
 $_SUBJID = 0;
 $_PLNAME = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;
   case '-qty' : {$qty=$args[$c+1]; $c++;} break;
   case '-plid' : case '-pricelistid' : {$_PLID=$args[$c+1]; $c++;} break;
   case '-subjid' : case '-subjectid' : {$_SUBJID=$args[$c+1]; $c++;} break;
   case '-varname' : {$variantName=$args[$c+1]; $c++;} break;
   case '-vartype' : {$variantType=$args[$c+1]; $c++;} break;

   case '-verbose' : case '--verbose' : $verbose=true; break;
   case '-debug' : case '--debug' : $debug=true; break;
  }

 if(!$_AP || !$_ID) return array('message'=>"You must specify the archive prefix and the item id.", "error"=>"INVALID_ARGUMENTS");
 if(!$qty)			return array('message'=>"Error: the quantity can not be zero.", 'error'=>"ZERO_QTY");

 if(!$_PLID)
 {
  // se non viene specificato il tipo di listino verrà utilizzato quello predefinito.
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id,name FROM pricelists WHERE isdefault=1 LIMIT 1");
  if($db->Read())
  {
   $_PLID = $db->record['id'];
   $_PLNAME = $db->record['name'];
  }
  else
  {
   $db->RunQuery("SELECT id,name FROM pricelists WHERE 1 ORDER BY id ASC LIMIT 1");
   if($db->Read())
   {
	$_PLID = $db->record['id'];
	$_PLNAME = $db->record['name'];
   }
  }
  $db->Close();

  if($verbose)
   $out.= "Nessun listino prezzi selezionato. Verr&agrave; utilizzato quello predefinito".($_PLID ? " (".$_PLNAME.")." : ".")."\n";
 }

 /* RICAVO LE INFORMAZIONI DI BASE PER QUESTO ARTICOLO */
 $db = new AlpaDatabase();
 $query = "SELECT name,code_str,baseprice,vat";
 if($_PLID)	$query.= ",pricelist_".$_PLID."_baseprice,pricelist_".$_PLID."_mrate,pricelist_".$_PLID."_discount,pricelist_".$_PLID."_vat";
 $query.= " FROM dynarc_".$_AP."_items WHERE id='".$_ID."'";
 $db->RunQuery($query);
 if($db->Error)			return array('message'=>'MySQL Error: '.$db->Error, 'error'=>'MYSQL_ERROR');
 if(!$db->Read())		return array('message'=>"Item #".$_ID." does not exists into selected archive.", "error"=>"ITEM_DOES_NOT_EXISTS");

 $itemInfo = array('id'=>$_ID, 'name'=>$db->record['name'], 'code_str'=>$db->record['code_str'], 
	'baseprice'=>$db->record['baseprice'], 'vat'=>$db->record['vat']);

 if($verbose)
 {
  $out.= "Dettagli sull&lsquo;articolo:\n";
  $out.= "ID: ".$itemInfo['id']."\n";
  $out.= "Codice: ".$itemInfo['code_str']."\n";
  $out.= "Descrizione: ".$itemInfo['name']."\n";
  $out.= "Pr. base: ".number_format($itemInfo['baseprice'],2,',','.')."\n";
  $out.= "Aliq. IVA: ".$itemInfo['vat']."%\n";
  $finP = $itemInfo['baseprice'] + (($itemInfo['baseprice']/100)*$itemInfo['vat']);
  $out.= "Pr. IVA inclusa: ".number_format($finP,2,',','.')."\n\n";
 }

 if($_PLID)
 {
  $itemInfo['pl_baseprice'] = $db->record['pricelist_'.$_PLID.'_baseprice'];
  $itemInfo['pl_mrate'] = $db->record['pricelist_'.$_PLID.'_mrate'];
  $itemInfo['pl_discount'] = $db->record['pricelist_'.$_PLID.'_discount'];
  $itemInfo['pl_vat'] = $db->record['pricelist_'.$_PLID.'_vat'];
 }
 $db->Close();

 /* CALCULATE PRICING */
 $markupRate = 0;
 $vat = 0;
 if($itemInfo['custompricing'])
 {
  $basePrice = isset($itemInfo['custompricing']['baseprice']) ? $itemInfo['custompricing']['baseprice'] : 0;
  $finalPrice = $basePrice ? $basePrice : 0;
  $vat = $itemInfo['vat'];
 }
 else if($_PLID)
 {
  $basePrice = $itemInfo['pl_baseprice'] ? $itemInfo['pl_baseprice'] : $itemInfo['baseprice'];
  $markupRate = $itemInfo['pl_mrate'] ? $itemInfo['pl_mrate'] : 0;
  $discount = $itemInfo['pl_discount'] ? $itemInfo['pl_discount'] : 0;
  $finalPrice = $basePrice ? $basePrice + (($basePrice/100)*$markupRate) : 0;
  $finalPrice = $finalPrice ? $finalPrice - (($finalPrice/100)*$discount) : 0;
  $vat = $itemInfo['pl_vat'] ? $itemInfo['pl_vat'] : $itemInfo['vat'];

  $out.= "Prezzo di listino: ".number_format($finalPrice,2,',','.')." IVA esclusa.\n";

  if($_SUBJID)
  {
   // verifica prezzi imposti per cliente, l'aliquota iva viene prelevata dal listino associato
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT baseprice,discount_perc,discount_inc,discount2,discount3 FROM dynarc_".$_AP."_custompricing WHERE item_id='".$_ID."' AND subject_id='".$_SUBJID."' LIMIT 1");
   if($db->Read())
   {
	$basePrice = $db->record['baseprice'];
	$discount = 0;
  	$discount2 = 0;
  	$discount3 = 0;
  	if($db->record['discount_perc'] && $basePrice)
   	 $discount = ($basePrice/100)*$db->record['discount_perc'];
  	else if($db->record['discount_inc'])
   	 $discount = $db->record['discount_inc'];
  	if($db->record['discount2'])
   	 $discount2 = (($basePrice-$discount)/100) * $db->record['discount2'];
  	if($db->record['discount2'] && $db->record['discount3'])
   	 $discount3 = (($basePrice-$discount-$discount2)/100) * $db->record['discount3'];

  	$finalPrice = ((($basePrice-$discount)-$discount2)-$discount3);
   }
   $db->Close();
  }
 }
 else
 {
  $basePrice = $itemInfo['baseprice'];
  $finalPrice = $basePrice;
  $vat = $itemInfo['vat'];
 }

 /* RICAVO IL PREZZO FINALE DALLA VARIANTE SELEZIONATA */
 if($variantType && $variantName)
 {
  $db = new AlpaDatabase();
  $query = "SELECT mrate,final_price FROM dynarc_".$_AP."_varcodes WHERE item_id='".$_ID."' AND variant_name='".$db->Purify($variantName)."' AND ";
  switch($variantType)
  {
   case 'color' : $query.= "(variant_type='color' OR variant_type='tint')"; break;
   case 'size' : $query.= "(variant_type='size' OR variant_type='dim' OR variant_type='other')"; break;
   default : $query.= "variant_type='".$variantType."'"; break;
  }
  $db->RunQuery($query);
  if(!$db->Error)
  {
   if($db->Read())
   {
	if($db->record['mrate'] > 0)
	 $finalPrice = $finalPrice+(($finalPrice/100)*$db->record['mrate']);
	else if($db->record['final_price'] > 0)
	 $finalPrice = $db->record['final_price'];
   }
  }
  $db->Close();
 }
 
 /* PRELEVO LA LISTA DI TUTTI I PREZZI LE CUI QTA' STIANO NEL RANGE SPECIFICATO */
 $list = array();
 $db = new AlpaDatabase();
 $query = "SELECT * FROM dynarc_".$_AP."_pricesbyqty WHERE item_id='".$_ID."' AND qty_from<='".$qty."' AND (qty_to=0 OR qty_to>='".$qty."')";
 $db->RunQuery($query);
 if(!$db->Error)
 {
  while($db->Read())
  {
   $list[] = array('id'=>$db->record['id'], 'colors'=>$db->record['colors'], 'sizes'=>$db->record['sizes'], 'disc_perc'=>$db->record['disc_perc'],
	'finalprice'=>$db->record['final_price']);
  }
  if(!count($list) && $verbose)
  {
   $out.= "Non ci sono prezzi differenti per quantit&agrave; per questo articolo.\n";
  }
 }
 else
 {
  if($debug)
  {
   $out.= "Error in query: ".$query."\n";
   $out.= "MySQL Error: ".$db->Error."\n";
  }
 }
 $db->Close();

 /* DOPODICHE' PRELEVO SOLAMENTE QUELLO CON LA VARIANTE SPECIFICATA */
 if(count($list))
 {
  $ok = false;
  for($c=0; $c < count($list); $c++)
  {
   if($ok) break;
   switch($variantType)
   {
    case 'color' : case 'tint' : {
	 $colors = explode("],[", substr($list[$c]['colors'], 1, -1));
	 if(in_array($variantName, $colors))
	 {
	  if($list[$c]['disc_perc'])
	   $finalPrice = $finalPrice - (($finalPrice/100)*$list[$c]['disc_perc']);
	  else if($list[$c]['finalprice'])
	   $finalPrice = $list[$c]['finalprice'];
	  $ok = true;
	 }
	} break;

    case 'size' : case 'dim' : case 'other' : {
	 $sizes = explode("],[", substr($list[$c]['sizes'], 1, -1));
	 if(in_array($variantName, $sizes))
	 {
	  if($list[$c]['disc_perc'])
	   $finalPrice = $finalPrice - (($finalPrice/100)*$list[$c]['disc_perc']);
	  else if($list[$c]['finalprice'])
	   $finalPrice = $list[$c]['finalprice'];
	  $ok = true;
	 }
	} break;

    default : {

	} break;
   }
  }
 }

 $outArr = array('finalprice'=>$finalPrice, 'vat'=>$vat);

 $out.= "\nPrezzo finale: ".number_format($finalPrice,2,',','.')." IVA esclusa.\n";

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
 $_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'] ? $_COMPANY_PROFILE['accounting']['decimals_pricing'] : 2;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
   case '--fix-mmr' : $fixMMR=true; break;
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

 switch(strtoupper($_CAT_TAG))
 {
  case 'INVOICES' : case 'PURCHASEINVOICES' : case 'AGENTINVOICES' : case 'MEMBERINVOICES' : case 'CREDITSNOTE' : case 'DEBITSNOTE' : case 'PAYMENTNOTICE' : case 'PREEMPTIVES' : case 'ORDERS' : case 'INTERVREPORTS' : case 'RECEIPTS' : $_SHOW_CONTRIBANDDEDUCTS = true; break;
  default : $_SHOW_CONTRIBANDDEDUCTS = false; break;
 }


 $_CPA = $_COMPANY_PROFILE['accounting'];

 $_CPA['rivalsa_inps'] = $docInfo['rivalsa_inps'];
 $_CPA['contr_cassa_prev'] = $docInfo['contr_cassa_prev'];
 $_CPA['contr_cassa_prev_vatid'] = $docInfo['contr_cassa_prev_vatid'];
 $_CPA['rit_enasarco'] = $docInfo['rit_enasarco'];
 $_CPA['rit_enasarco_percimp'] = $docInfo['rit_enasarco_percimp'];
 $_CPA['rit_acconto'] = $docInfo['rit_acconto'];
 $_CPA['rit_acconto_percimp'] = $docInfo['rit_acconto_percimp'];
 $_CPA['rit_acconto_rivinpsinc'] = $docInfo['rit_acconto_rivinpsinc'];


 /*----------------------------------------------------------------------------*/
 $_RIVALSA_INPS = $_CPA['rivalsa_inps'] ? $_CPA['rivalsa_inps'] : 0;
 $_RIT_ENASARCO = $_CPA['rit_enasarco'] ? $_CPA['rit_enasarco'] : 0;
 $_RIT_ENASARCO_PERCIMP = $_CPA['rit_enasarco_percimp'] ? $_CPA['rit_enasarco_percimp'] : 0;

 $_CASSA_PREV = $_CPA['contr_cassa_prev'] ? $_CPA['contr_cassa_prev'] : 0;
 $_CASSA_PREV_VATID = $_CPA['contr_cassa_prev_vatid'] ? $_CPA['contr_cassa_prev_vatid'] : 0;
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

 $_RIT_ACCONTO = $_CPA['rit_acconto'] ? $_CPA['rit_acconto'] : 0;
 $_RIT_ACCONTO_PERCIMP = $_CPA['rit_acconto_percimp'] ? $_CPA['rit_acconto_percimp'] : 0;
 $_RIT_ACCONTO_RIVINPSINC = $_CPA['rit_acconto_rivinpsinc'] ? $_CPA['rit_acconto_rivinpsinc'] : 0;
 /*----------------------------------------------------------------------------*/
 $_VATS = array();
 $agentCommiss = 0;
 $agentInfo = null;

 if($docInfo['agent_id'])
 {
  /* GET AGENT INFO */
  $ret = GShell("dynarc item-info -ap rubrica -id '".$docInfo['agent_id']."' -extget `catcommiss`",$sessid,$shellid);
  if(!$ret['error'])
   $agentInfo = $ret['outarr'];
  else
   $out.= "Warning: Unable to get agent info.\n";
 }

 $_TOT_IMP_RITACC = 0;
 $_TOT_IMP_CCP = 0;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT elem_type,ref_ap,ref_id,qty,extra_qty,price,price_adjust,discount_perc,discount_inc,discount2,discount3,vat_rate,vat_id,vat_type,rit_acc_apply,ccp_apply FROM dynarc_".$_AP."_elements WHERE item_id='".$docInfo['id']."'");
 while($db->Read())
 {
  if(($db->record['elem_type'] == "note") || ($db->record['elem_type'] == "message"))
   continue;
  $qty = $db->record['qty'];
  $extraQty = $db->record['extra_qty'];
  if($extraQty)
   $qty = $qty*$extraQty;
  
  $amount = $db->record['price'];
  if(!$qty || !$amount)
   continue;

  /* CALCOLO DELLE PROVVIGIONI ALL'AGENTE */
  if($agentInfo)
  {
   switch($_CAT_TAG)
   {
    case 'INVOICES' : case 'ORDERS' : case 'DDT' : case 'RECEIPTS' : {
	 $agentCommiss+= commercialdocs_getAgentCommiss($agentInfo, $db->record['ref_ap'], $db->record['ref_id'], $amount*$qty);
	} break;
   }
  }
  /* EOF - CALCOLO PROVV. AGENTE */
  
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

  
  /* TODO: BUG FIX - 10-09-2016 - calcolo della rit. acc e ccp da testare */
  if($_SHOW_CONTRIBANDDEDUCTS && $_RIT_ACCONTO)
  {
   /*if(($db->record['elem_type'] == "service") || $db->record['rit_acc_apply'])
    $_TOT_IMP_RITACC+= $amount;*/
   if($db->record['rit_acc_apply'])
    $_TOT_IMP_RITACC+= $amount;
  }
  if($_SHOW_CONTRIBANDDEDUCTS && $_CASSA_PREV)
  {
   /*if(($db->record['elem_type'] == "service") || $db->record['ccp_apply'])
    $_TOT_IMP_CCP+= $amount;*/
   if($db->record['ccp_apply'])
    $_TOT_IMP_CCP+= $amount;
  }

  if($db->record['vat_id'])
  {
   if($_VATS[$db->record['vat_id']])
	$_VATS[$db->record['vat_id']]['amount']+= round($amount, 5);
   else
	$_VATS[$db->record['vat_id']] = array("type"=>$db->record['vat_type'], "rate"=>$db->record['vat_rate'], "amount"=>round($amount, 5), "expenses"=>0);
  }
 }
 $db->Close();
 /*----------------------------------------------------------------------------*/
 $_TOTALE_MERCE = 0;
 $_TOTALE_SPESE = 0;
 $_MERCE_SCONTATA = 0;
 $_TOTALE_IMPONIBILE = 0;
 $_TOTALE_IVA = 0;
 $_TOTALE_IVA_ND = 0;
 $_SCONTO_1 = $docInfo['discount'];
 $_SCONTO_2 = $docInfo['discount2'];
 $_UNCONDITIONAL_DISCOUNT = $docInfo['uncondisc'];
 $_REBATE = $docInfo['rebate'];
 $_STAMP = $docInfo['stamp'];
 $_COLLECTION_CHARGES = $docInfo['collection_charges'];
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
 // calcolo spese trasporto e imballaggio
 if($docInfo['cartage'])
 {
  if($_VATS[$docInfo["cartage_vatid"]])
   $_VATS[$docInfo["cartage_vatid"]]['expenses']+= $docInfo["cartage"];
  else
  {
   // detect vat-type and vat-rate //
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT vat_type,percentage FROM dynarc_vatrates_items WHERE id='".$docInfo["cartage_vatid"]."'");
   $db->Read();
   $_VATS[$docInfo["cartage_vatid"]] = array("type"=>$db->record['vat_type'], "rate"=>$db->record['percentage'], "amount"=>0, "expenses"=>$docInfo["cartage"]);
   $db->Close();
  }
 }

 if($docInfo['packing_charges'])
 {
  if($_VATS[$docInfo["packing_charges_vatid"]])
   $_VATS[$docInfo["packing_charges_vatid"]]['expenses']+= $docInfo["packing_charges"];
  else
  {
   // detect vat-type and vat-rate //
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT vat_type,percentage FROM dynarc_vatrates_items WHERE id='".$docInfo["cartage_vatid"]."'");
   $db->Read();
   $_VATS[$docInfo["packing_charges_vatid"]] = array("type"=>$db->record['vat_type'], "rate"=>$db->record['percentage'], "amount"=>0, "expenses"=>$docInfo["packing_charges"]);
   $db->Close();
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
  if($_SHOW_CONTRIBANDDEDUCTS && $_RIVALSA_INPS)
  {
   $rivinps = $value ? ($value/100)*$_RIVALSA_INPS : 0;
   $_TOTALE_RIVALSA_INPS+= $rivinps;
   $value+= $rivinps;
  }

  // arrotondo alla cifra decimale //
  $value = round($value, 5);

  $_TOTALE_IMPONIBILE+= $value;

  $_VATS[$vid]['discounted'] = $value;

  // calcolo l'IVA
  $vat = $value ? round(($value/100)*$vatInfo['rate'], 5) : 0;
  switch($vatInfo['type'])
  {
   case 'TAXABLE' : $_TOTALE_IVA+= $vat; break;
   case 'PURCH_EXEUR' : case 'PURCH_INEUR' : case 'SPLIT_PAYMENT' : case 'REVERSE_CHARGE' : {
	 $_TOTALE_IVA+= $vat;
	 $_TOTALE_IVA_ND+= $vat;
	} break;
  }

  $_VATS[$vid]['vat'] = $vat;
 }
 /*----------------------------------------------------------------------------*/
 // calcola la merce scontata
 if($_SCONTO_1)
  $_MERCE_SCONTATA = $_TOTALE_MERCE - (($_TOTALE_MERCE/100)*$_SCONTO_1);
 else
  $_MERCE_SCONTATA = $_TOTALE_MERCE;

 $_MERCE_SCONTATA = round($_MERCE_SCONTATA, 5);

 // calcola la cassa prev.
 if($_SHOW_CONTRIBANDDEDUCTS && $_CASSA_PREV)
 {
  //$imp = $_MERCE_SCONTATA - $_UNCONDITIONAL_DISCOUNT;
  $imp = $_TOT_IMP_CCP;
  $_TOTALE_CASSA_PREV = $imp ? round(($imp/100)*$_CASSA_PREV, $_DECIMALS) : 0;
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
   $vat = $value ? round(($value/100)*$vatInfo['rate'], $_DECIMALS) : 0;
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
	$vat = $_TOTALE_CASSA_PREV ? round(($_TOTALE_CASSA_PREV/100)*$_CASSA_PREV, $_DECIMALS) : 0;
	$_TOTALE_IVA+= $vat;
    $vatInfo['vat'] = $vat;
   }
   $_VATS[$_CASSA_PREV_VATID] = $vatInfo;
  }
  $_TOTALE_IMPONIBILE+= $_TOTALE_CASSA_PREV;
 }
 /*----------------------------------------------------------------------------*/
 // calcolo la ritenuta d'acconto
 if($_SHOW_CONTRIBANDDEDUCTS && $_RIT_ACCONTO)
 {
  //$imp = $_MERCE_SCONTATA - $_UNCONDITIONAL_DISCOUNT;
  $imp = $_TOT_IMP_RITACC;
  if($_RIT_ACCONTO_RIVINPSINC)
   $imp = $imp ? round((($imp+$_TOTALE_RIVALSA_INPS)/100)*$_RIT_ACCONTO_PERCIMP, $_DECIMALS) : 0;
  else
   $imp = $imp ? round(($imp/100)*$_RIT_ACCONTO_PERCIMP,$_DECIMALS) : 0;
  $_TOTALE_RITENUTA_ACCONTO = $imp ? round(($imp/100)*$_RIT_ACCONTO, $_DECIMALS) : 0;
 }

 // calcolo l'enasarco
 if($_SHOW_CONTRIBANDDEDUCTS && $_RIT_ENASARCO)
 {
  $imp = $_MERCE_SCONTATA - $_UNCONDITIONAL_DISCOUNT;
  $imp = $imp ? round(($imp/100)*$_RIT_ENASARCO_PERCIMP, $_DECIMALS) : 0;
  $_TOTALE_ENASARCO = $imp ? round(($imp/100)*$_RIT_ENASARCO, $_DECIMALS) : 0;
 }

 /* ARROTONDO I TOTALI */
 $_TOTALE_MERCE = round($_TOTALE_MERCE, 2);
 $_MERCE_SCONTATA = round($_MERCE_SCONTATA, 2);
 $_TOTALE_IMPONIBILE = round($_TOTALE_IMPONIBILE, 2);
 $_TOTALE_IVA = round($_TOTALE_IVA, 2);
 $_TOTALE_IVA_ND = round($_TOTALE_IVA_ND, 2);
 $_TOTALE_SPESE = round($_TOTALE_SPESE, 2);
 $_TOTALE_RITENUTA_ACCONTO = round($_TOTALE_RITENUTA_ACCONTO, 2);
 $_TOTALE_ENASARCO = round($_TOTALE_ENASARCO, 2);
 $_TOTALE_CASSA_PREV = round($_TOTALE_CASSA_PREV, 2); 
 $_REBATE = round($_REBATE, 2);
 $_STAMP = round($_STAMP, 2);
 $_COLLECTION_CHARGES = round($_COLLECTION_CHARGES, 2);
 $_TOTALE_RIVALSA_INPS = round($_TOTALE_RIVALSA_INPS, 2);
 $_UNCONDITIONAL_DISCOUNT = round($_UNCONDITIONAL_DISCOUNT, 2);


 /* CALCOLO IL NETTO A PAGARE */
 $_NET_PAY = round(($_TOTALE_IMPONIBILE+$_TOTALE_IVA-$_TOTALE_IVA_ND) - $_TOTALE_RITENUTA_ACCONTO - $_TOTALE_ENASARCO - $_REBATE + $_STAMP + $_COLLECTION_CHARGES, 2);
 
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
 $docInfo['tot_netpay'] = $_NET_PAY;
 /*----------------------------------------------------------------------------*/
 /* INSERISCE LE SCADENZE */
 if($fixMMR)
 {
  $isDebit = false;
  switch(strtolower($_CAT_TAG))
  {
   case 'agentinvoices' : case 'vendororders' : case 'purchaseinvoices' : case 'creditsnote' : case 'ddtin' : $isDebit=true; break;
  }

  // Clean MMR
  $db = new AlpaDatabase();
  $db->RunQuery("DELETE FROM dynarc_commercialdocs_mmr WHERE item_id='".$docInfo['id']."' AND payment_date='0000-00-00'");
  $db->Close();

  // calculate and insert MMR
  $ret = GShell("accounting paymentmodeinfo -id '".$docInfo['paymentmode']."' -amount '".$docInfo['tot_netpay']."' -from '".date('Y-m-d',$docInfo['ctime'])."' --get-deadlines",$sessid,$shellid);
  if(!$ret['error'])
  {
   $list = $ret['outarr']['deadlines'];
   for($c=0; $c < count($list); $c++)
   {
	$mmr = $list[$c];
	GShell("dynarc edit-item -ap 'commercialdocs' -id '".$docInfo['id']."' -extset `mmr.desc='Rata n.".($c+1)." scad. "
		.date('d/m/Y',strtotime($mmr['date']))."',".($isDebit ? "expenses" : "incomes")."='".$mmr['amount']."',expire='".$mmr['date']."',subjectid='"
		.$docInfo['subject_id']."',subject_name=\"".$docInfo['subject_name']."\"`",$sessid,$shellid);
   }
  }
 }
 /*----------------------------------------------------------------------------*/
 /* Get tot paid and rest to pay */
 $totPaid = 0;
 $restToPay = 0; //$itemInfo['tot_netpay'];
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT incomes,expenses,expire_date,payment_date FROM dynarc_commercialdocs_mmr WHERE item_id='".$docInfo['id']."'");
 while($db->Read())
 {
  if($db->record['payment_date'] != "0000-00-00")
   $totPaid+= $db->record['incomes'] ? $db->record['incomes'] : $db->record['expenses'];
  else
   $restToPay+= $db->record['incomes'] ? $db->record['incomes'] : $db->record['expenses'];
 }
 $db->Close();

 $docInfo['tot_paid'] = $totPaid;
 $docInfo['rest_to_pay'] = $restToPay;
 /*----------------------------------------------------------------------------*/
 $out.= "Updating db...";
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$_AP."_items SET amount='".$_TOTALE_IMPONIBILE."',vat='".$_TOTALE_IVA."',vat_nd='".$_TOTALE_IVA_ND."',total='"
	.($_TOTALE_IMPONIBILE + $_TOTALE_IVA)."',tot_rit_acc='".$_TOTALE_RITENUTA_ACCONTO."',tot_ccp='".$_TOTALE_CASSA_PREV."',tot_rinps='"
	.$_TOTALE_RIVALSA_INPS."',tot_enasarco='".$_TOTALE_ENASARCO."',tot_netpay='".$_NET_PAY."',tot_goods='".$_TOTALE_MERCE."',discounted_goods='"
	.$_MERCE_SCONTATA."',tot_expenses='".$_TOTALE_SPESE."',tot_discount='".$_TOTALE_SCONTO."',agent_commiss='".$agentCommiss."',tot_paid='"
	.$docInfo['tot_paid']."',rest_to_pay='".$docInfo['rest_to_pay']."'".$vatsQry." WHERE id='".$docInfo['id']."'");

 if($db->Error) return array('message'=>$out."failed!\nMySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
 $out.= "done!\n";

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
  $out.= "Tot. IVA non dovuta: ".number_format($_TOTALE_IVA_ND,2,",",".")." &euro;\n";
  $out.= "Totale fattura: ".number_format($_TOTALE_IMPONIBILE + $_TOTALE_IVA,2,",",".")." &euro;\n";
  $out.= "Totale spese: ".number_format($_TOTALE_SPESE,2,",",".")." &euro;\n";
  $out.= "Rit. d&lsquo;acconto: ".number_format($_TOTALE_RITENUTA_ACCONTO,2,",",".")." &euro;\n";
  $out.= "Enasarco: ".number_format($_TOTALE_ENASARCO,2,",",".")." &euro;\n";
  $out.= "Cassa prev.: ".number_format($_TOTALE_CASSA_PREV,2,",",".")." &euro;\n";
  $out.= "Abbuoni: ".number_format($_REBATE,2,",",".")." &euro;\n";
  $out.= "Bolli: ".number_format($_STAMP,2,",",".")." &euro;\n";
  $out.= "Spese incasso: ".number_format($_COLLECTION_CHARGES,2,",",".")." &euro;\n";
  $out.= "Netto a pagare: ".number_format($_NET_PAY,2,",",".")." &euro;\n";

  if($agentInfo)
  {
   $out.= "Agent: ".$agentInfo['name']."\n";
   $out.= "Commissions: ".number_format($agentCommiss,2,",",".")." &euro;\n";
  }
 }
 else
  $out = "done!\n";

 $out.= "\nUpdating VAT register...";
 $ret = commercialdocs_fixUpdateVatRegister($sessid, $shellid, $_AP, $docInfo);
 if($ret['error'])
  $out.= "failed!\n".$ret['message'];
 else
  $out.= "done!\n".$ret['message'];

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
   case 'INVOICES' : case 'ORDERS' : case 'DDT' : case 'DEBITSNOTE' : case 'RECEIPTS' : $recType=2; break;
   case 'VENDORORDERS' : case 'PURCHASEINVOICES' : case 'AGENTINVOICES' : case 'MEMBERINVOICES' : case 'CREDITSNOTE' : case 'DDTIN' : $recType=1; break;
   default : return array("message"=>"This type of document has nothing to do with the VAT register"); break;
  }
 }


 /* Get some document info */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT subject_id,subject_name,status FROM dynarc_".$_AP."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 /*switch($catTag)
 {
  case 'INVOICES' : {
	 if($db->record['status'] == 0)
	  return array("message"=>"This document is not set as paid!");
	} break;

  default : {
	 if($db->record['status'] != 10)
	  return array("message"=>"This document is not set as paid!");
	} break;
 }*/
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
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;
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

 $qry = "trash='0'";
 if($dateFrom)
  $qry.= " AND ctime>='".$dateFrom."'";
 if($dateTo)
  $qry.= " AND ctime<'".$dateTo."'";
 if($where)
  $qry.= " AND (".$where.")";

 // get count
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$_AP."_items WHERE ".$qry);
 $db->Read();
 $count = $db->record[0];
 $db->Close();
 
 $interface = array("name"=>"progressbar","steps"=>$count);
 gshPreOutput($shellid,"Estimated documents to scan: ".$count, "ESTIMATION", "", "PASSTHRU", $interface);

 // exec query
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id,name FROM dynarc_".$_AP."_items WHERE ".$qry);
 while($db->Read())
 {
  gshPreOutput($shellid, "Scan for: <i>".$db->record['name']."</i>","PROGRESS", "");
  $ret = GShell("commercialdocs updatetotals -ap '".$_AP."' -id '".$db->record['id']."'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
 }
 $db->Close();

 $out.= "Tot. documents fixed: ".$count;

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_generateFile($args, $sessid, $shellid)
{
 global $_BASE_PATH;

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-protocol' : {$protocol=$args[$c+1]; $c++;} break;
  }

 if(!$protocol)
  return array("message"=>"You must specify the protocol.", "error"=>"INVALID_PROTOCOL");

 if(!file_exists($_BASE_PATH."etc/commercialdocs/protocols/".$protocol.".php"))
  return array("message"=>"Error: Protocol ".$protocol." does not exists!","error"=>"PROTOCOL_NOT_FOUND");

 include_once($_BASE_PATH."etc/commercialdocs/protocols/".$protocol.".php");
 if(is_callable("commercialdocs_".$protocol."_generatefile",true))
 {
  return call_user_func("commercialdocs_".$protocol."_generatefile",$args, $sessid, $shellid);
 }
 else
 {
  $out = "Unable to call function 'commercialdocs_".$protocol."_generatefile' into file etc/commercialdocs/protocols/".$protocol.".php";
  return array("message"=>$out,"error"=>"UNKNOWN_ERROR");
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_emitSignal($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_COMMERCIALDOCS_CONFIG;
 include_once($_BASE_PATH."etc/commercialdocs/config.php");

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-event' : {$event=$args[$c+1]; $c++;} break;
   default : {if(!$event) $event=$args[$c];} break;
  }

 if(!isset($_COMMERCIALDOCS_CONFIG['EVENTS'][$event]) || !count($_COMMERCIALDOCS_CONFIG['EVENTS'][$event]))
  return array("message"=>"Event ".$event." not configured.");

 $out.= "Number of commands to be execute = ".count($_COMMERCIALDOCS_CONFIG['EVENTS'][$event]);
 $outArr['commands'] = $_COMMERCIALDOCS_CONFIG['EVENTS'][$event];

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_getAgentCommiss($agentInfo, $_AP="", $_ID=0, $amount=0)
{
 // se l'imponibile è a zero, oppure $_ID=0, ritorna subito zero.
 if(!$_AP || !$_ID || !$amount)
  return 0;

 // verifica subito se catcommiss contiene questo archivio altrimenti ritorna zero
 if(!$agentInfo['catcommiss'][$_AP])
  return 0;

 // verifica se catcommiss contiene almeno una categoria, altrimenti fa il calcolo con la percentuale di default
 if(!count($agentInfo['catcommiss'][$_AP]['categories']) && $agentInfo['catcommiss'][$_AP]['defaultperc'])
  return ($amount/100)*$agentInfo['catcommiss'][$_AP]['defaultperc'];


 $db = new AlpaDatabase();
 // get catid and hierarchy
 $db->RunQuery("SELECT cat_id,hierarchy FROM dynarc_".$_AP."_items WHERE id='".$_ID."'");
 $db->Read();
 $catId = $db->record['cat_id'];
 if($catId)
  $hierarchy = substr($db->record['hierarchy'],1,-1);
 $db->Close();

 if($catId)
 {
  $x = explode(",",$hierarchy);
  $hierarchy = array_reverse($x);

  // verifica se il prodotto rientra nelle categorie // 
  for($j=0; $j < count($hierarchy); $j++)
  {
   $catId = $hierarchy[$j];
   for($c=0; $c < count($agentInfo['catcommiss'][$_AP]['categories']); $c++)
   {
    if($agentInfo['catcommiss'][$_AP]['categories'][$c]['cat_id'] == $catId)
     return ($amount/100) * $agentInfo['catcommiss'][$_AP]['categories'][$c]['perc'];
   }
  }
 }
 // ...altrimenti utilizza la commissione predefinita per questo archivio //
 return ($amount/100) * $agentInfo['catcommiss'][$_AP]['defaultperc'];
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_fixMMR($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_COMPANY_PROFILE;

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
   case '--overwrite-all' : $overwriteAll=true; break;
  }

 /* Get DDT category */
 $ret = GShell("dynarc cat-info -ap commercialdocs -tag 'DDT'",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 $ddtCatInfo = $ret['outarr'];

 /* Get Invoice category */
 $ret = GShell("dynarc cat-info -ap commercialdocs -tag 'INVOICES'",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 $invoiceCatInfo = $ret['outarr'];

 /* Seleziona tutti i DDT non raggruppati ne convertiti */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_commercialdocs_items WHERE cat_id='".$ddtCatInfo['id']."' AND trash='0' AND conv_doc_id='0' AND group_doc_id='0'".($dateFrom ? " AND ctime>='".$dateFrom."'" : "").($dateTo ? " AND ctime<'".$dateTo."'" : ""));
 $db->Read();
 $ddtCount = $db->record[0];

 /* Seleziona tutte le fatture non pagate */
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_commercialdocs_items WHERE cat_id='".$invoiceCatInfo['id']."' AND trash=0 AND status<10"
	.($dateFrom ? " AND ctime>='".$dateFrom."'" : "").($dateTo ? " AND ctime<'".$dateTo."'" : ""));
 $db->Read();
 $invoiceCount = $db->record[0];
 $db->Close();

 $totDocCount = $ddtCount+$invoiceCount;
 $out.= "Tot of documents to scan: ".$totDocCount."\n";
 $interface = array("name"=>"progressbar","steps"=>$totDocCount);
 gshPreOutput($shellid,"Estimated documents to scan: ".$totDocCount, "ESTIMATION", "", "PASSTHRU", $interface);


 $totDocFixed=0;

 /* FIX DDT */
 if($ddtCount)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id,name,ctime,subject_id,subject_name,payment_mode,tot_netpay FROM dynarc_commercialdocs_items WHERE cat_id='"
	.$ddtCatInfo['id']."' AND trash='0' AND conv_doc_id='0' AND group_doc_id='0'"
	.($dateFrom ? " AND ctime>='".$dateFrom."'" : "").($dateTo ? " AND ctime<'".$dateTo."'" : ""));
  while($db->Read())
  {
   $docId = $db->record['id'];
   $docName = $db->record['name'];
   $paymentMode = $db->record['payment_mode'];
   $ctime = strtotime($db->record['ctime']);
   $subjectId = $db->record['subject_id'];
   $subjectName = $db->record['subject_name'];
   $totNetPay = $db->record['tot_netpay'];
   $hasMMR = false;
   gshPreOutput($shellid, "Scan for doc. <i>".($docName ? $docName : 'untitled')."</i>","PROGRESS", "");
   if(!$overwriteAll)
   {
    // verifica se contiene le scadenze
	$db2 = new AlpaDatabase();
	$db2->RunQuery("SELECT id FROM dynarc_commercialdocs_mmr WHERE item_id='".$docId."' LIMIT 1");
	if($db2->Read())
	 $hasMMR = true;
	$db2->Close();
   }
   if($overwriteAll)
   {
	// clean all mmr
	$db2 = new AlpaDatabase();
	$db2->RunQuery("DELETE FROM dynarc_commercialdocs_mmr WHERE item_id='".$docId."'");
	$db2->Close();
	$hasMMR=false;
   }
   if(!$hasMMR)
   {
	/* INSERISCE LE SCADENZE */
	$ret = GShell("accounting paymentmodeinfo -id '".$paymentMode."' -amount '".$totNetPay."' -from '".date('Y-m-d',$ctime)."' --get-deadlines",$sessid,$shellid);
	if(!$ret['error'])
	{
	 $list = $ret['outarr']['deadlines'];
	 for($c=0; $c < count($list); $c++)
	 {
	  $mmr = $list[$c];
	  GShell("dynarc edit-item -ap 'commercialdocs' -id '".$docId."' -extset `mmr.desc='Rata n.".($c+1)." scad. "
		.date('d/m/Y',strtotime($mmr['date']))."',incomes='".$mmr['amount']."',expire='".$mmr['date']."',subjectid='"
		.$subjectId."',subject_name=\"".$subjectName."\"`",$sessid,$shellid);
	 }
	 $totDocFixed++;
	}
   }
  }
  $db->Close();
 }


 /* FIX INVOICES */
 if($invoiceCount)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id,name,ctime,subject_id,subject_name,payment_mode,tot_netpay FROM dynarc_commercialdocs_items WHERE cat_id='"
	.$invoiceCatInfo['id']."' AND trash=0 AND status<10"
	.($dateFrom ? " AND ctime>='".$dateFrom."'" : "").($dateTo ? " AND ctime<'".$dateTo."'" : ""));
  while($db->Read())
  {
   $docId = $db->record['id'];
   $docName = $db->record['name'];
   $paymentMode = $db->record['payment_mode'];
   $ctime = strtotime($db->record['ctime']);
   $subjectId = $db->record['subject_id'];
   $subjectName = $db->record['subject_name'];
   $totNetPay = $db->record['tot_netpay'];
   $hasMMR = false;
   gshPreOutput($shellid, "Scan for doc. <i>".($docName ? $docName : 'untitled')."</i>","PROGRESS", "");
   if(!$overwriteAll)
   {
    // verifica se contiene le scadenze
	$db2 = new AlpaDatabase();
	$db2->RunQuery("SELECT id FROM dynarc_commercialdocs_mmr WHERE item_id='".$docId."' LIMIT 1");
	if($db2->Read())
	 $hasMMR = true;
	$db2->Close();
   }
   if($overwriteAll)
   {
	// clean all mmr
	$db2 = new AlpaDatabase();
	$db2->RunQuery("DELETE FROM dynarc_commercialdocs_mmr WHERE item_id='".$docId."'");
	$db2->Close();
	$hasMMR = false;
   }
   if(!$hasMMR)
   {
	/* INSERISCE LE SCADENZE */
	$ret = GShell("accounting paymentmodeinfo -id '".$paymentMode."' -amount '".$totNetPay."' -from '".date('Y-m-d',$ctime)."' --get-deadlines",$sessid,$shellid);
	if(!$ret['error'])
	{
	 $list = $ret['outarr']['deadlines'];
	 for($c=0; $c < count($list); $c++)
	 {
	  $mmr = $list[$c];
	  GShell("dynarc edit-item -ap 'commercialdocs' -id '".$docId."' -extset `mmr.desc='Rata n.".($c+1)." scad. "
		.date('d/m/Y',strtotime($mmr['date']))."',incomes='".$mmr['amount']."',expire='".$mmr['date']."',subjectid='"
		.$subjectId."',subject_name=\"".$subjectName."\"`",$sessid,$shellid);
	 }
	 $totDocFixed++;
	}
   }
  }
  $db->Close();
 }

 $out.= "Tot documents fixed: ".$totDocFixed;

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_fixMMRdocDates($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_COMPANY_PROFILE;

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_commercialdocs_mmr WHERE 1");
 $db->Read();
 $mmrCount = $db->record[0];
 $db->Close();


 $out.= "Tot rates to scan: ".$mmrCount."\n";
 $interface = array("name"=>"progressbar","steps"=>$mmrCount);
 gshPreOutput($shellid,"Estimated rates to scan: ".$mmrCount, "ESTIMATION", "", "PASSTHRU", $interface);

 $_DOC_BY_ID = array();

 $lastItemId = 0;
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id,item_id FROM dynarc_commercialdocs_mmr WHERE 1 ORDER BY item_id ASC");
 while($db->Read())
 {
  if($db->record['item_id'] != $lastItemId)
  {
   $lastItemId = $db->record['item_id'];
   // get doc info
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT ctime FROM dynarc_commercialdocs_items WHERE id='".$lastItemId."'");
   $db2->Read();
   $_DOC_BY_ID[$lastItemId] = array("ctime"=>$db2->record['ctime']);
   $db2->RunQuery("UPDATE dynarc_commercialdocs_mmr SET doc_date='".$db2->record['ctime']."' WHERE item_id='".$lastItemId."'");
   $db2->Close();
  }
 }
 $db->Close();
 
 $out.= "done!";
 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_fixMMRdocReferences($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_COMPANY_PROFILE;

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-docid' : {$docId=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 /* Get all payment modes */
 $ret = GShell("paymentmodes list",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 $paymentModes = $ret['outarr'];

 $_PM_BY_ID = array();
 for($c=0; $c < count($paymentModes); $c++)
  $_PM_BY_ID[$paymentModes[$c]['id']] = $paymentModes[$c];

 /* Get all type of documents */
 $_DOCTYPE_BY_CATID = array();
 $ret = GShell("dynarc cat-list -ap commercialdocs",$sessid,$shellid);
 $catList = $ret['outarr'];
 for($c=0; $c < count($catList); $c++)
 {
  $docType = $catList[$c]['tag'];
  $_DOCTYPE_BY_CATID[$catList[$c]['id']] = $docType;
  $ret = GShell("dynarc cat-list -ap commercialdocs -parent '".$catList[$c]['id']."'",$sessid,$shellid);
  $list = $ret['outarr'];
  for($i=0; $i < count($list); $i++)
   $_DOCTYPE_BY_CATID[$list[$i]['id']] = $docType;
 }
 

 $db = new AlpaDatabase();
 if($docId)
  $db->RunQuery("SELECT COUNT(*) FROM dynarc_commercialdocs_mmr WHERE item_id='".$docId."'");
 else
  $db->RunQuery("SELECT COUNT(*) FROM dynarc_commercialdocs_mmr WHERE 1");
 $db->Read();
 $mmrCount = $db->record[0];
 $db->Close();

 $out.= "Tot rates to scan: ".$mmrCount."\n";
 $interface = array("name"=>"progressbar","steps"=>$mmrCount);
 gshPreOutput($shellid,"Estimated rates to scan: ".$mmrCount, "ESTIMATION", "", "PASSTHRU", $interface);


 $lastItemId = 0;
 $db = new AlpaDatabase();
 if($docId)
  $db->RunQuery("SELECT id,item_id FROM dynarc_commercialdocs_mmr WHERE item_id='".$docId."' ORDER BY id ASC");
 else
  $db->RunQuery("SELECT id,item_id FROM dynarc_commercialdocs_mmr WHERE 1 ORDER BY item_id ASC");
 while($db->Read())
 {
  if($db->record['item_id'] != $lastItemId)
  {
   $lastItemId = $db->record['item_id'];
   // get doc info
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT ctime,cat_id,name,code_num,code_ext,tag,payment_mode,bank_support FROM dynarc_commercialdocs_items WHERE id='".$lastItemId."'");
   $db2->Read();

   $docType = $_DOCTYPE_BY_CATID[$db2->record['cat_id']];
   $paymentType = $_PM_BY_ID[$db2->record['payment_mode']]['type'];
   
   $set = "doc_date='".$db2->record['ctime']."',doc_type='".$docType."',doc_num='".$db2->record['code_num']."',doc_num_ext='"
	.$db2->record['code_ext']."',doc_name='".$db2->record['name']."',doc_tag='".$db2->record['tag']."',doc_bank='"
	.$db2->record['bank_support']."',payment_type='".$paymentType."',payment_mode='".$db2->record['payment_mode']."'";
   
   $db2->RunQuery("UPDATE dynarc_commercialdocs_mmr SET ".$set." WHERE item_id='".$lastItemId."'");
   $db2->Close();
  }
 }
 $db->Close();
 
 $out.= "done!";
 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_fixVendorsIntoElements($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $outArr = array();
 $_AP = "commercialdocs";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
   case '-into' : {$into=$args[$c+1]; $c++;} break;
   //case '-where' : {$where=$args[$c+1]; $c++;} break;
  }

 $qry = "";
 /* CHECK FOR PARENT */
 if($catId)
 {
  $ret = GShell("dynarc cat-info -ap '".$_AP."' -id '".$catId."'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
  $qry = "item.cat_id='".$catInfo['id']."'";
 }
 else if($catTag)
 {
  $ret = GShell("dynarc cat-info -ap '".$_AP."' -tag '".$catTag."'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
  $catId = $catInfo['id'];
  $qry = "item.cat_id='".$catInfo['id']."'";
 }
 else if($into)
 {
  if(is_numeric($into))
	$intoId = $into;
  else
  {
   $ret = GShell("dynarc cat-info -ap '".$_AP."' -tag '".$into."'",$sessid,$shellid);
   if($ret['error'])
    return $ret;
   $intoId = $ret['outarr']['id'];
  }
  $qry = "(item.hierarchy=',".$intoId.",' OR item.hierarchy LIKE ',".$intoId.",%' OR item.hierarchy LIKE '%,".$intoId.",' OR item.hierarchy LIKE '%,".$intoId.",%')";
 }

 /*if($where)
  $qry.= " AND (".$where.")";*/

 /* COUNT */
 $query = "SELECT COUNT(*) FROM dynarc_".$_AP."_elements AS el 
	INNER JOIN dynarc_".$_AP."_items AS item
	ON el.item_id = item.id
	WHERE ".$qry." AND el.ref_ap!='' AND el.ref_id>0 AND el.vendor_id=0";

 $db = new AlpaDatabase();
 $db->RunQuery($query);
 $db->Read();
 $count = $db->record[0];
 $db->Close();

 $estimate = $count;
 $interface = array("name"=>"progressbar","steps"=>$estimate);
 gshPreOutput($shellid,"Fix vendors in progress...", "ESTIMATION", "", "PASSTHRU", $interface);


 $query = "SELECT el.id,el.ref_ap,el.ref_id,el.vendor_price,item.name FROM dynarc_".$_AP."_elements AS el 
	INNER JOIN dynarc_".$_AP."_items AS item
	ON el.item_id = item.id
	WHERE ".$qry." AND el.ref_ap!='' AND el.ref_id>0 AND el.vendor_id=0 ORDER BY item_id ASC";

 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db->RunQuery($query);
 while($db->Read())
 {
  $db2->RunQuery("SELECT vendor_id,price FROM dynarc_".$db->record['ref_ap']."_vendorprices WHERE item_id='".$db->record['ref_id']."' ORDER BY id ASC LIMIT 1");
  $db2->Read();
  $db2->RunQuery("UPDATE dynarc_".$_AP."_elements SET vendor_id='".$db2->record['vendor_id']."'"
	.(!$db->record['vendor_price'] ? ",vendor_price='".$db2->record['price']."'" : "")." WHERE id='".$db->record['id']."'");
  gshPreOutput($shellid,"Updating ".$db->record['name'], "PROGRESS", $db->record['id']);
 }
 $db->Close();
 $db2->Close();

 
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_fixRootCT($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $outArr = array();
 $_AP = "commercialdocs";
 $_CAT_BY_ID = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--verbose' : $verbose=true; break;
  }

 // Get category tree
 $db = new AlpaDatabase();
 $out.= "Get category tree...";
 $db->RunQuery("SELECT id,parent_id,tag,name FROM dynarc_".$_AP."_categories WHERE trash=0 ORDER BY parent_id ASC, id ASC");
 if($db->Error) return array('message'=>$out."failed!\nMySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, 'error'=>'MYSQL_ERROR');
 while($db->Read())
 {
  $a = array('id'=>$db->record['id'], 'parent_id'=>$db->record['parent_id'], 'tag'=>$db->record['tag'], 'root_ct'=>$db->record['tag'], 
	'name'=>$db->record['name']);

  if($a['parent_id'] && $_CAT_BY_ID[$a['parent_id']])
   $a['root_ct'] = $_CAT_BY_ID[$a['parent_id']]['root_ct'];

  $_CAT_BY_ID[$a['id']] = $a;
 }
 $out.= "done!\n";

 /*if($verbose)
 {
  reset($_CAT_BY_ID);
  $out.= "<table cellspacing='5' cellpadding='0' border='0'>";
  $out.= "<tr><th>ID</th><th>TAG</th><th>NAME</th><th>ROOT CAT TAG</th></tr>";
  while(list($catId,$catInfo) = each($_CAT_BY_ID))
  {
   $out.= "<tr><td>".$catId."</td><td>".$catInfo['tag']."</td><td>".$catInfo['name']."</td><td>".$catInfo['root_ct']."</td></tr>";
  }
  $out.= "</table>";
 }*/

 $out.= "Update root cat tag for each document...";
 reset($_CAT_BY_ID);
 while(list($catId,$catInfo) = each($_CAT_BY_ID))
 {
  $db->RunQuery("UPDATE dynarc_".$_AP."_items SET root_ct='".$catInfo['root_ct']."' WHERE cat_id='".$catId."'");
  if($db->Error) return array('message'=>$out."failed!\nMySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, 'error'=>'MYSQL_ERROR');
 }
 $db->Close();
 $out.= "done!\n";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_getLastProducts($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $_PRODBYID = array();

 $_AT = "gmart";
 $orderBy = "name ASC";
 $limit = 20;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-subjectid' : {$subjectId=$args[$c+1]; $c++;} break;
   case '-subject' : case '-subjectname' : {$subjectName=$args[$c+1]; $c++;} break;

   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;

   case '--verbose' : case '-verbose' : $verbose=true; break;
  }

 $db = new AlpaDatabase();
 $_QRY = "SELECT id FROM dynarc_commercialdocs_items WHERE trash='0'";
 if($subjectId) $_QRY.= " AND subject_id='".$subjectId."'";
 else if($subjectName) $_QRY.= " AND subject_name='".$db->Purify($subjectName)."'";
 $_QRY.= " ORDER BY ctime DESC";

 $ret = $db->RunQuery($_QRY);
 if(!$ret) return array("message"=>"MySQL Error: ".$db->Error, "error"=>"MYSQL_ERROR");

 while($db->Read())
 {
  if(count($outArr['items']) >= $limit)
   break;
  $db2 = new AlpaDatabase();
  $_QRY = "SELECT ref_ap,ref_id,code,name,qty FROM dynarc_commercialdocs_elements WHERE item_id='".$db->record['id']."' AND elem_type='article'";
  if($_AP) $_QRY.= " AND ref_ap='".$_AP."'"; else $_QRY.= " AND ref_ap!='' AND ref_ap!='null'";
  $_QRY.= " AND ref_id!='0' ORDER BY ".$orderBy;
  
  $ret = $db2->RunQuery($_QRY);
  if(!$ret) return array("message"=>"MySQL Error: ".$db2->Error, "error"=>"MYSQL_ERROR");
  while($db2->Read())
  {
   if(count($outArr['items']) >= $limit)
    break;

   // verifiy if product exists
   $db3 = new AlpaDatabase();
   $db3->RunQuery("SELECT trash FROM dynarc_".$db2->record['ref_ap']."_items WHERE id='".$db2->record['ref_id']."'");
   if(!$db3->Read() || $db3->record['trash'])
	continue;
   $db3->Close();

   $a = array("ap"=>$db2->record['ref_ap'], "id"=>$db2->record['ref_id'], "code"=>$db2->record['code'], "name"=>$db2->record['name'], 
	"qty"=>$db2->record['qty']);
   if(!$_PRODBYID[$a['ap']])
	$_PRODBYID[$a['ap']] = array();
   if($_PRODBYID[$a['ap']][$a['id']])
	$_PRODBYID[$a['ap']][$a['id']]['qty']+= $a['qty'];
   else
   {
	$_PRODBYID[$a['ap']][$a['id']] = $a;
	$outArr['items'][] = $a;
   }
  }
  $db2->Close();
 }
 $db->Close();

 if($verbose)
  $out.= "List of products sold recently\n";

 /* Aggiorno le quantità di tutti i prodotti */
 for($c=0; $c < count($outArr['items']); $c++)
 {
  $ap = $outArr['items'][$c]['ap'];
  $id = $outArr['items'][$c]['id'];
  $outArr['items'][$c]['qty'] = $_PRODBYID[$ap][$id]['qty'];
  
  if($verbose)
   $out.= "[".$ap."][#".$id."] - ".$outArr['items'][$c]['code']." ".$outArr['items'][$c]['name']." (qty:".$outArr['items'][$c]['qty'].")\n";
 }

 if(!$verbose)
  $out.= count($outArr['items'])." results found.";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_getDDTToBeGroup($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array('count'=>0, 'items'=>array());

 $orderBy = "subject_name ASC,name ASC";
 $limit = 25;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : case '-datefrom' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : case '-dateto' : {$dateTo=$args[$c+1]; $c++;} break;
   case '-subjid' : case '-subjectid' : {$subjectId=$args[$c+1]; $c++;} break;
   case '-cat' : case '-catid' : {$catId=$args[$c+1]; $c++;} break;

   case '--get-vendors' : $getVendors=true; break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
   case '--order-by' : case '-orderby' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
  }

 $where = "conv_doc_id=0 AND group_doc_id=0";
 if($dateFrom)				$where.= " AND ctime>='".$dateFrom."'";
 if($dateTo)				$where.= " AND ctime<'".$dateTo."'";
 if($subjectId)				$where.= " AND subject_id='".$subjectId."'";

 $cmd = "dynarc item-list -ap `commercialdocs`".($catId ? " -cat '".$catId."'" : " -into DDT")." -where `"
	.ltrim($where," AND ")."`";
 $cmd.= " -get `subject_id,subject_name,amount,vat,total,tot_netpay,ext_docref,docref_ap,docref_id`";
 $cmd.= " --order-by '".$orderBy."' -limit ".$limit;
 
 $ret = GShell($cmd,$sessid,$shellid);
 if($ret['error']) return $ret;

 $count = $ret['outarr']['count'];
 $items = $ret['outarr']['items'];

 if($getVendors)
 {
  $db = new AlpaDatabase();
  for($c=0; $c < count($items); $c++)
  {
   $item = $items[$c];
   $item['vendors'] = array();
   $db->RunQuery("SELECT DISTINCT doc.vendor_id, vendor.name FROM dynarc_commercialdocs_elements AS doc INNER JOIN dynarc_rubrica_items AS vendor ON doc.vendor_id = vendor.id WHERE item_id='".$item['id']."'");
   while($db->Read())
   {
	$item['vendors'][] = array('id'=>$db->record['vendor_id'], 'name'=>$db->record['name']);
   }
   $outArr['items'][] = $item;
  }
  $db->Close();
 }
 else
  $outArr['items'] = $ret['outarr']['items'];
 $outArr['count'] = $count;
 

 if($verbose)
 {
  $out.= "List of DDT to be grouped";
  if($subjectId)
  {
   if(count($outArr['items']))
	$subjectName = $outArr['items'][0]['subject_name'];
   else
   {
	// get subject name
	$db = new AlpaDatabase();
	$db->RunQuery("SELECT name FROM dynarc_rubrica_items WHERE id='".$subjectId."'");
	$db->Read();
	$subjectName = $db->record['name'];
	$db->Close();
   }
   $out.= " for customer '".$subjectName."'";
  }
  if($dateFrom)			$out.= " from ".date('d/m/Y',strtotime($dateFrom));
  if($dateTo)			$out.= " to ".date('d/m/Y',strtotime($dateTo));
  $out.= "\n";

  for($c=0; $c < count($outArr['items']); $c++)
  {
   $item = $outArr['items'][$c];
   $out.= $item['name']." - ".$item['subject_name']." - ".number_format($item['tot_netpay'],2,',','.')." &euro;\n";
  }
 }

 if(!$outArr['count'])
  $out.= "No results found.";
 else
  $out.= $outArr['count']." results found.";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_ddtGroup($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array('invoices'=>array(), 'memberinvoices'=>array());

 $_IDS = array();
 $_DOC_TYPE = "INVOICES";

 $_DDT_BY_CUSTOMER = array();
 $_DDT_BY_MEMBER = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$_IDS[]=$args[$c+1]; $c++;} break; // ID dei documenti da raggruppare //
   case '-type' : {$_DOC_TYPE=$args[$c+1]; $c++;} break;	// tipo di documento
   case '--generate-member-invoice' : case '--generate-members-invoices' : $generateMembersInvoices=true; break;
  }

 if(!count($_IDS))
  return array("message"=>"You must specify at least one document to be grouped.", "error"=>"INVALID_DOCUMENT_ID");

 $_REF_SCHEMA = "Rif. DDT n.{NUM} del {DATE}";
 $_REF_SCHEMA2 = "Rif. DDT n.{NUM} del {DATE}";
 $_REF_KEYS = array('{NUM}','{DATE}','{WEIGHT}','{IDR_NUM}','{IDR_DATE}','{IDR_WEIGHT}','{DOCREF}','{IDR_DOCREF}');

 /* GET CONFIG */
 $ret = GShell("aboutconfig get-config -app coopgest",$sessid,$shellid);
 if(!$ret['error'])
 {
  $config = $ret['outarr']['config'];
  if($config['options']['ddtrefschema'])
   $_REF_SCHEMA = $config['options']['ddtrefschema'];
  if($config['options']['ddtrefschemamember'])
   $_REF_SCHEMA2 = $config['options']['ddtrefschemamember'];
 }


 // Group DDT by customers 
 $out.= "Get list of DDT by customer...";
 for($c=0; $c < count($_IDS); $c++)
 {
  $ret = GShell("dynarc item-info -ap `commercialdocs` -id `".$_IDS[$c]."` -extget `cdinfo,cdelements,mmr`",$sessid,$shellid);
  if($ret['error']) return array("message"=>$out."failed.\n".$ret['message'], "error"=>$ret['error']);
  $ddtInfo = $ret['outarr'];
  if(!$_DDT_BY_CUSTOMER[$ddtInfo['subject_id']]) $_DDT_BY_CUSTOMER[$ddtInfo['subject_id']] = array();
  $_DDT_BY_CUSTOMER[$ddtInfo['subject_id']][] = $ddtInfo;

  // ricava i soci conferenti dalla lista degli elementi
  for($i=0; $i < count($ddtInfo['elements']); $i++)
  {
   $el = $ddtInfo['elements'][$i];
   if(!$el['vendor_id']) continue;
   if(!$_DDT_BY_MEMBER[$el['vendor_id']])	$_DDT_BY_MEMBER[$el['vendor_id']] = array();
   if(!in_array($ddtInfo, $_DDT_BY_MEMBER[$el['vendor_id']]))
    $_DDT_BY_MEMBER[$el['vendor_id']][] = $ddtInfo;
  }
 }
 $out.= "done. ".count($_DDT_BY_CUSTOMER)." customer found.\n";

 $count = count($_DDT_BY_CUSTOMER);
 if($generateMembersInvoices)
  $count+= count($_DDT_BY_MEMBER);

 $estimate = $count;
 $interface = array("name"=>"progressbar","steps"=>$estimate);
 gshPreOutput($shellid,"Raggruppamento DDT in corso...", "ESTIMATION", "", "PASSTHRU", $interface);

 $weightMul = array('mg'=>0.000001, 'g'=>0.001, 'hg'=>0.1, 'kg'=>1, 'q'=>100, 't'=>1000);

 /* GENERATE INVOICES FOR EACH CUSTOMER */
 reset($_DDT_BY_CUSTOMER);
 while(list($subjectId,$ddtList) = each($_DDT_BY_CUSTOMER))
 {
  $docInfo = $ddtList[0];

  $query = "dynarc new-item -ap `commercialdocs` -ct `".$_DOC_TYPE."` -set `tag='DEFERRED'` -group commdocs-invoices -extset `cdinfo.subjectid='"
	.$docInfo['subject_id']."',subject=\"".$docInfo['subject_name']."\"";
  if($docInfo['reference_id'])				$query.= ",referenceid='".$docInfo['reference_id']."'";
  if($docInfo['agent_id'])					$query.= ",agentid='".$docInfo['agent_id']."'";
  if($docInfo['agent_commiss'])				$query.= ",agentcommiss='".$docInfo['agent_commiss']."'";
  if($docInfo['paymentmode'])				$query.= ",paymentmode='".$docInfo['paymentmode']."'";
  if($docInfo['banksupport_id'])			$query.= ",banksupport='".$docInfo['banksupport_id']."'";
  if($docInfo['pricelist_id'])				$query.= ",pricelist='".$docInfo['pricelist_id']."'";
  if($docInfo['division'])					$query.= ",division='".$docInfo['division']."'";
  if($docInfo['location'])					$query.= ",location=\"".$docInfo['location']."\"";

  /* Shipping */
  $query.= ",ship-subject-id='".$docInfo['ship_subject_id']."'";
  $query.= ",ship-contact-id='".$docInfo['ship_contact_id']."'";
  $query.= ",ship-recp=\"".$docInfo['ship_recp']."\"";
  $query.= ",ship-addr=\"".$docInfo['ship_addr']."\"";
  $query.= ",ship-city=\"".$docInfo['ship_city']."\"";
  $query.= ",ship-zip='".$docInfo['ship_zip']."'";
  $query.= ",ship-prov='".$docInfo['ship_prov']."'";
  $query.= ",ship-cc='".$docInfo['ship_cc']."'";

  $query.= "`";

  $ret = GShell($query,$sessid,$shellid);
  if($ret['error'])	return array('message'=>$out."\n".$ret['message'], 'error'=>$ret['error']);
  $docId = $ret['outarr']['id'];
  $invoiceInfo = $ret['outarr'];
  $outArr['invoices'][] = $ret['outarr'];
  $out.= "Invoice n.".$invoiceInfo['code_num']." has been generated for customer ".$docInfo['subject_name']."\n";
  gshPreOutput($shellid,"Invoice n.".$invoiceInfo['code_num']." has been generated for customer ".$docInfo['subject_name'], "PROGRESS", $docInfo['id']);

  // UPDATE TRANSPORT AND OTHER INFORMATIONS
  $query = "dynarc edit-item -ap commercialdocs -id '".$docId."' -extset `cdinfo.";
  /* Transport */
  $query.= "trans-method='".$docInfo['trans_method']."'";
  $query.= ",trans-shipper=\"".$docInfo['trans_shipper']."\"";
  $query.= ",trans-numplate='".$docInfo['trans_numplate']."'";
  $query.= ",trans-causal=\"".$docInfo['trans_causal']."\"";
  if($docInfo['trans_datetime'])				$query.= ",trans-date='".date('Y-m-d H:i',$docInfo['trans_datetime'])."'";
  $query.= ",trans-aspect=\"".$docInfo['trans_aspect']."\"";
  $query.= ",trans-num='".$docInfo['trans_num']."'";
  $query.= ",trans-weight='".$docInfo['trans_weight']."'";
  $query.= ",trans-freight='".$docInfo['trans_freight']."'";
  $query.= ",cartage='".$docInfo['cartage']."'";
  $query.= ",packing-charges='".$docInfo['packing_charges']."'";
  $query.= ",collection-charges='".$docInfo['collection_charges']."'";

  /* Expenses */
  if($docInfo['exp1name'])
   $query.= ",exp1-name=\"".$docInfo['exp1name']."\",exp1-vatid='".$docInfo['exp1vatid']."',exp1-amount='".$docInfo['exp1amount']."'";
  if($docInfo['exp2name'])
   $query.= ",exp2-name=\"".$docInfo['exp2name']."\",exp2-vatid='".$docInfo['exp2vatid']."',exp2-amount='".$docInfo['exp2amount']."'";
  if($docInfo['exp3name'])
   $query.= ",exp3-name=\"".$docInfo['exp3name']."\",exp3-vatid='".$docInfo['exp3vatid']."',exp3-amount='".$docInfo['exp3amount']."'";

  /* Discounts, rebate and stamp */
  if($docInfo['discount'])				$query.= ",discount='".$docInfo['discount']."'";
  if($docInfo['discount2'])				$query.= ",discount2='".$docInfo['discount2']."'";
  if($docInfo['uncondisc'])				$query.= ",uncondisc='".$docInfo['uncondisc']."'";
  if($docInfo['rebate'])				$query.= ",rebate='".$docInfo['rebate']."'";
  if($docInfo['stamp'])					$query.= ",stamp='".$docInfo['stamp']."'";

  $query.= "`";
  GShell($query,$sessid,$shellid);

  /* INSERT ELEMENTS */
  for($c=0; $c < count($ddtList); $c++)
  {
   $ddtInfo = $ddtList[$c];
   // get doc ref //
   if(($ddtInfo['docref_ap'] == "commercialdocs") && $ddtInfo['docref_id'])
   {
    $ret = GShell("dynarc item-info -ap commercialdocs -id '".$ddtInfo['docref_id']."' -extget `cdinfo,cdelements`",$sessid,$shellid);
	if(!$ret['error'])
	{
     $ddtInfo['docref'] = $ret['outarr'];
	 // get tot weight //
     $totWeight = 0;
     for($i=0; $i < count($ddtInfo['docref']['elements']); $i++)
     {
	  $el = $ddtInfo['docref']['elements'][$i];
	  $qty = $el['qty'] * ($el['extraqty'] ? $el['extraqty'] : 1);
	  $totWeight+= (($el['weight']*$qty)*$weightMul[$el['weightunits']]);
     }
     $ddtInfo['docref']['tot_weight'] = sprintf("%.2f",$totWeight);
	}
   }

   // get tot weight //
   $totWeight = 0;
   for($i=0; $i < count($ddtInfo['elements']); $i++)
   {
	$el = $ddtInfo['elements'][$i];
	$qty = $el['qty'] * ($el['extraqty'] ? $el['extraqty'] : 1);
	$totWeight+= (($el['weight']*$qty)*$weightMul[$el['weightunits']]);
   }
   $ddtInfo['tot_weight'] = sprintf("%.2f",$totWeight);

   // get reference //
   $_REF_VALUES = array(
	 $ddtInfo['code_num'].($ddtInfo['code_ext'] ? "/".$ddtInfo['code_ext'] : ''),
	 date('d/m/Y',$ddtInfo['ctime']),
	 $ddtInfo['tot_weight'],

	 $ddtInfo['docref'] ? $ddtInfo['docref']['code_num'].($ddtInfo['docref']['code_ext'] ? "/".$ddtInfo['docref']['code_ext'] : '') : "",
	 $ddtInfo['docref'] ? date('d/m/Y',$ddtInfo['docref']['ctime']) : "",
	 $ddtInfo['docref'] ? $ddtInfo['docref']['tot_weight'] : 0,
	 $ddtInfo['ext_docref'] ? $ddtInfo['ext_docref'] : '',
	 $ddtInfo['docref'] ? $ddtInfo['docref']['ext_docref'] : '',
	);
   $reference = str_replace($_REF_KEYS, $_REF_VALUES, $_REF_SCHEMA);

   // verifica se tutti gli elementi hanno la stessa aliquota IVA, altrimenti è necessario inserire ogni elemento //
   $ok = true;
   $vatId = null;
   $vatRate = 0;
   $vatType = "";
   for($i=0; $i < count($ddtInfo['elements']); $i++)
   {
    $el = $ddtInfo['elements'][$i];
	if(!$el['price'])
	 continue;
	if(!isset($vatId))
	{
	 $vatId = $el['vatid'];
	 $vatRate = $el['vatrate'];
	 $vatType = $el['vattype'];
	 continue;
	}
	if($el['vatid'] != $vatId)
	{
	 $ok = false;
	 break;
	}
   }

   if($ok)
   {
	GShell("dynarc edit-item -ap `commercialdocs` -id `".$docId."` -extset `cdelements.type='custom',name='''"
		.$reference."''',qty='1',price='".$ddtInfo['amount']."',vatrate='".$vatRate."',vatid='".$vatId."',vattype='"
		.$vatType."',bypass-vatregister-update=true`",$sessid,$shellid);
   }
   else
   {
    GShell("dynarc edit-item -ap `commercialdocs` -id `".$docId."` -extset `cdelements.type='note',desc='''Rif. "
		.$reference."''',bypass-vatregister-update=true`",$sessid,$shellid);
    for($i=0; $i < count($ddtInfo['elements']); $i++)
    {
     $el = $ddtInfo['elements'][$i];
     GShell("dynarc edit-item -ap `commercialdocs` -id `".$docId."` -extset `cdelements.type='".$el['type']."',refap='".$el['ref_ap']."',refid='"
	 .$el['ref_id']."',code='".$el['code']."',vencode='".$el['vencode']."',sn='".$el['serialnumber']."',lot='".$el['lot']."',accountid='"
	 .$el['account_id']."',name='''".$el['name']."''',desc='''".$el['desc']."''',qty='".$el['qty']."',qtysent='".$el['qty_sent']."',qtydl='"
	 .$el['qty_downloaded']."',extraqty='".$el['extraqty']."',price='".$el['price']."',discount='".$el['discount']."',discount2='"
	 .$el['discount2']."',discount3='".$el['discount3']."',vatrate='".$el['vatrate']."',vatid='".$el['vatid']."',vattype='"
	 .$el['vattype']."',units='".$el['units']."',pricelistid='".$el['pricelist_id']."',brandid='".$el['brand_id']."',vendorid='"
	 .$el['vendor_id']."',vendorprice='".$el['vendor_price']."',coltint='''".$el['variant_coltint']."''',sizmis='''"
	 .$el['variant_sizmis']."''',ritaccapply='".$el['ritaccapply']."',ccpapply='".$el['ccpapply']."',xmldata='''"
	 .$el['xmldata']."''',rowdocap='commercialdocs',rowdocid='".$ddtInfo['id']."',rowrefid='".$el['id']."',bypass-vatregister-update=true`");
    }
   }
   // update ddt status
   $out.= "Updating status for DDT n.".$ddtInfo['code_num']."...";
   $ret = GShell("dynarc edit-item -ap `commercialdocs` -id `".$ddtInfo['id']."` -extset `cdinfo.group-id='".$docId."',status='9'`",$sessid,$shellid);
   if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'], 'error'=>$ret['error']);
   $out.= "done!\n";
  }

  /* UPDATING TOTALS */
  $out.= "Updating totals for ".$invoiceInfo['name']."...";
  $ret = GShell("commercialdocs updatetotals -id '".$docId."'",$sessid,$shellid);
  if($ret['error']) return array('message'=>$out."failed.\n".$ret['message'], 'error'=>$ret['error']);
  $out.= "done!\n";
  $updtret = $ret['outarr'];

  /* AGGIORNA LE SCADENZE */
  $ret = GShell("accounting paymentmodeinfo -id '".$updtret['paymentmode']."' -amount '".$updtret['tot_netpay']."' -from '".date('Y-m-d',$docInfo['ctime'])."' --get-deadlines",$sessid,$shellid);
  if(!$ret['error'])
  {
   $list = $ret['outarr']['deadlines'];
   for($c=0; $c < count($list); $c++)
   {
    $mmr = $list[$c];
    GShell("dynarc edit-item -ap 'commercialdocs' -id '".$docId."' -extset `mmr.desc='Rata n.".($c+1)." scad. "
	 .date('d/m/Y',strtotime($mmr['date']))."',incomes='".$mmr['amount']."',expire='".$mmr['date']."',subjectid='"
	 .$docInfo['subject_id']."',subject_name=\"".$docInfo['subject_name']."\"`",$sessid,$shellid);
   }
  }
 } /* EOF GENERATE INVOICES FOR CUSTOMERS */

 if($generateMembersInvoices)
 {
  /* GENERATE INVOICES FOR EACH MEMBER */
  reset($_DDT_BY_MEMBER);
  while(list($memberId,$ddtList) = each($_DDT_BY_MEMBER))
  {
   $ret = GShell("dynarc new-item -ap commercialdocs -ct MEMBERINVOICES -group commdocs-memberinvoices -extset `cdinfo.subjectid='"
	.$memberId."'`",$sessid,$shellid);
   if($ret['error']) return array('message'=>$out."\n".$ret['message'], 'error'=>$ret['error']);
   $docInfo = $ret['outarr'];
   $outArr['memberinvoices'][] = $docInfo;
   $out.= "Invoice n.".$docInfo['code_num']." has been generated for member ".$docInfo['subject_name']."\n";
   gshPreOutput($shellid,"Invoice n.".$docInfo['code_num']." has been generated for member ".$docInfo['subject_name'], "PROGRESS", $docInfo['id']);
   for($c=0; $c < count($ddtList); $c++)
   {
    $ddtInfo = $ddtList[$c];

    // get doc ref //
    if(($ddtInfo['docref_ap'] == "commercialdocs") && $ddtInfo['docref_id'])
    {
     $ret = GShell("dynarc item-info -ap commercialdocs -id '".$ddtInfo['docref_id']."' -extget `cdinfo,cdelements`",$sessid,$shellid);
	 if(!$ret['error'])
	 {
      $ddtInfo['docref'] = $ret['outarr'];
	  // get tot weight //
      $totWeight = 0;
      for($i=0; $i < count($ddtInfo['docref']['elements']); $i++)
      {
	   $el = $ddtInfo['docref']['elements'][$i];
	   $qty = $el['qty'] * ($el['extraqty'] ? $el['extraqty'] : 1);
	   $totWeight+= (($el['weight']*$qty)*$weightMul[$el['weightunits']]);
      }
      $ddtInfo['docref']['tot_weight'] = sprintf("%.2f",$totWeight);
	 }
    }

    // get tot weight //
    $totWeight = 0;
    for($i=0; $i < count($ddtInfo['elements']); $i++)
    {
	 $el = $ddtInfo['elements'][$i];
	 $qty = $el['qty'] * ($el['extraqty'] ? $el['extraqty'] : 1);
	 $totWeight+= (($el['weight']*$qty)*$weightMul[$el['weightunits']]);
    }
    $ddtInfo['tot_weight'] = sprintf("%.2f",$totWeight);

    // get reference //
    $_REF_VALUES = array(
	 $ddtInfo['code_num'].($ddtInfo['code_ext'] ? "/".$ddtInfo['code_ext'] : ''),
	 date('d/m/Y',$ddtInfo['ctime']),
	 $ddtInfo['tot_weight'],

	 $ddtInfo['docref'] ? $ddtInfo['docref']['code_num'].($ddtInfo['docref']['code_ext'] ? "/".$ddtInfo['docref']['code_ext'] : '') : "",
	 $ddtInfo['docref'] ? date('d/m/Y',$ddtInfo['docref']['ctime']) : "",
	 $ddtInfo['docref'] ? $ddtInfo['docref']['tot_weight'] : 0,
	 $ddtInfo['ext_docref'] ? $ddtInfo['ext_docref'] : '',
	 $ddtInfo['docref'] ? $ddtInfo['docref']['ext_docref'] : '',
	);
    $reference = str_replace($_REF_KEYS, $_REF_VALUES, $_REF_SCHEMA2);

    // verifica se tutti gli elementi hanno la stessa aliquota IVA, altrimenti è necessario inserire ogni elemento //
    $ok = true;
    $vatId = null;
    $vatRate = 0;
    $vatType = "";
    for($i=0; $i < count($ddtInfo['elements']); $i++)
    {
     $el = $ddtInfo['elements'][$i];
	 if(!$el['price']) continue;
	 if(!isset($vatId)){$vatId = $el['vatid']; $vatRate = $el['vatrate']; $vatType = $el['vattype']; continue;}
	 if($el['vatid'] != $vatId){$ok = false; break;}
    }
    if($ok)
    {
	 GShell("dynarc edit-item -ap `commercialdocs` -id `".$docInfo['id']."` -extset `cdelements.type='custom',name='''"
		.$reference."''',qty='1',price='".$ddtInfo['amount']."',vatrate='".$vatRate."',vatid='".$vatId."',vattype='"
		.$vatType."',bypass-vatregister-update=true`",$sessid,$shellid);
    }
    else
    {
     GShell("dynarc edit-item -ap `commercialdocs` -id `".$docInfo['id']."` -extset `cdelements.type='note',desc='''Rif. "
		.$reference."''',bypass-vatregister-update=true`",$sessid,$shellid);
     for($i=0; $i < count($ddtInfo['elements']); $i++)
     {
      $el = $ddtInfo['elements'][$i];
	  if($el['vendor_id'] != $memberId) continue;
      GShell("dynarc edit-item -ap `commercialdocs` -id `".$docInfo['id']."` -extset `cdelements.type='".$el['type']."',refap='".$el['ref_ap']."',refid='"
	  .$el['ref_id']."',code='".$el['code']."',vencode='".$el['vencode']."',sn='".$el['serialnumber']."',lot='".$el['lot']."',accountid='"
	  .$el['account_id']."',name='''".$el['name']."''',desc='''".$el['desc']."''',qty='".$el['qty']."',extraqty='"
	  .$el['extraqty']."',qtysent='".$el['qty_sent']."',qtydl='".$el['qty_downloaded']."',price='".$el['vendor_price']."',vatrate='"
	  .$el['vatrate']."',vatid='".$el['vatid']."',vattype='".$el['vattype']."',units='".$el['units']."',brandid='"
	  .$el['brand_id']."',vendorid='".$el['vendor_id']."',coltint='''".$el['variant_coltint']."''',sizmis='''"
	  .$el['variant_sizmis']."''',ritaccapply='".$el['ritaccapply']."',ccpapply='".$el['ccpapply']."',xmldata='''"
	  .$el['xmldata']."''',rowdocap='commercialdocs',rowdocid='".$ddtInfo['id']."',rowrefid='".$el['id']."',bypass-vatregister-update=true`");
     }
	}
    GShell("commercialdocs updatetotals -id '".$docInfo['id']."'",$sessid,$shellid);
   }
  }
 }
 
 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_ddtinGroup($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $_IDS = array();
 $_DOC_TYPE = "PURCHASEINVOICES";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$_IDS[]=$args[$c+1]; $c++;} break; 		// ID dei documenti da raggruppare //
   case '-type' : {$_DOC_TYPE=$args[$c+1]; $c++;} break;	// tipo di documento
   case '-destid' : {$docId=$args[$c+1]; $c++;} break;		// ID doc. di destinazione, se omesso verrà creato.
  }

 if(!count($_IDS))
  return array("message"=>"You must specify at least one document to be grouped.", "error"=>"INVALID_DOCUMENT_ID");

 for($c=0; $c < count($_IDS); $c++)
 {
  $ret = GShell("dynarc item-info -ap `commercialdocs` -id `".$_IDS[$c]."` -extget `cdinfo,cdelements`",$sessid,$shellid);
  if($ret['error']) return $ret;
  $docInfo = $ret['outarr'];

  /* CREATE DOCUMENT */
  if($c==0)
  {
   $query = "dynarc ".($docId ? "edit-item -ap commercialdocs -id '".$docId."'" : "new-item -ap commercialdocs -ct '".$_DOC_TYPE."' -group commdocs-purchaseinvoices")." -extset `cdinfo.subjectid='".$docInfo['subject_id']."',subject=\"".$docInfo['subject_name']."\"";
   if($docInfo['paymentmode'])			$query.= ",paymentmode='".$docInfo['paymentmode']."'";
   if($docInfo['banksupport_id'])		$query.= ",banksupport='".$docInfo['banksupport_id']."'";
   if($docInfo['pricelist_id'])			$query.= ",pricelist='".$docInfo['pricelist_id']."'";
   if($docInfo['division'])				$query.= ",division='".$docInfo['division']."'";
   if($docInfo['location'])				$query.= ",location=\"".$docInfo['location']."\"";

   /* Shipping */
   $query.= ",ship-subject-id='".$docInfo['ship_subject_id']."'";
   $query.= ",ship-contact-id='".$docInfo['ship_contact_id']."'";
   $query.= ",ship-recp=\"".$docInfo['ship_recp']."\"";
   $query.= ",ship-addr=\"".$docInfo['ship_addr']."\"";
   $query.= ",ship-city=\"".$docInfo['ship_city']."\"";
   $query.= ",ship-zip='".$docInfo['ship_zip']."'";
   $query.= ",ship-prov='".$docInfo['ship_prov']."'";
   $query.= ",ship-cc='".$docInfo['ship_cc']."'";

   $query.= "`";
   $ret = GShell($query,$sessid,$shellid);
   if($ret['error']) return $ret;
   if(!$docId)	$docId = $ret['outarr']['id'];
   $outArr = $ret['outarr'];
  }

  /* INSERT ELEMENTS */
  $reference = $docInfo['ext_docref'] ? $docInfo['ext_docref'] : $docInfo['name'];
  GShell("dynarc edit-item -ap `commercialdocs` -id `".$docId."` -extset `cdelements.type='note',desc='''Rif. ".$reference."'''`",$sessid,$shellid);
  for($i=0; $i < count($docInfo['elements']); $i++)
  {
   $el = $docInfo['elements'][$i];
   GShell("dynarc edit-item -ap `commercialdocs` -id `".$docId."` -extset `cdelements.type='".$el['type']."',refap='".$el['ref_ap']."',refid='"
	.$el['ref_id']."',code='".$el['code']."',vencode='".$el['vencode']."',sn='".$el['serialnumber']."',lot='".$el['lot']."',accountid='"
	.$el['account_id']."',name='''".$el['name']."''',desc='''".$el['desc']."''',qty='".$el['qty']."',extraqty='".$el['extraqty']."',price='"
	.$el['price']."',discount='".$el['discount']."',discount2='".$el['discount2']."',discount3='".$el['discount3']."',vatrate='"
	.$el['vatrate']."',vatid='".$el['vatid']."',vattype='".$el['vattype']."',units='".$el['units']."',pricelistid='"
	.$el['pricelist_id']."',brandid='".$el['brand_id']."',vendorid='".$el['vendor_id']."',vendorprice='"
	.$el['vendor_price']."',coltint='''".$el['variant_coltint']."''',sizmis='''".$el['variant_sizmis']."''',ritaccapply='"
	.$el['ritaccapply']."',ccpapply='".$el['ccpapply']."',xmldata='''"
	.$el['xmldata']."''',rowdocap='commercialdocs',rowdocid='".$docInfo['id']."',rowrefid='".$el['id']."',bypass-vatregister-update=true`",$sessid,$shellid);
  }
 }

 /* UPDATING TOTALS */
 $out.= "\nUpdating totals...";
 $ret = GShell("commercialdocs updatetotals -id '".$docId."'",$sessid,$shellid);
 if($ret['error'])
  $out.= "failed\n".$ret['message'];
 else
 {
  $out.= "\ndone!\n".$ret['message'];
  $outArr = $ret['outarr'];
 }

 /* AGGIORNA LE SCADENZE */
 $ret = GShell("accounting paymentmodeinfo -id '".$outArr['paymentmode']."' -amount '".$outArr['tot_netpay']."' -from '".date('Y-m-d',$docInfo['ctime'])."' --get-deadlines",$sessid,$shellid);
 if(!$ret['error'])
 {
  $list = $ret['outarr']['deadlines'];
  for($c=0; $c < count($list); $c++)
  {
   $mmr = $list[$c];
   GShell("dynarc edit-item -ap 'commercialdocs' -id '".$docId."' -extset `mmr.desc='Rata n.".($c+1)." scad. "
	.date('d/m/Y',strtotime($mmr['date']))."',expenses='".$mmr['amount']."',expire='".$mmr['date']."',subjectid='"
	.$docInfo['subject_id']."',subject_name=\"".$docInfo['subject_name']."\"`",$sessid,$shellid);
  }
 }

 /* Update documents status */
 for($c=0; $c < count($_IDS); $c++)
 {
  GShell("dynarc edit-item -ap `commercialdocs` -id `".$_IDS[$c]."` -extset `cdinfo.group-id='".$docId."',status='9'`",$sessid,$shellid);
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_summaryBySubject($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array('count'=>0, 'items'=>array());

 $_AP = "commercialdocs";
 $_ORDER_BY = "subject_name ASC";
 $_LIMIT = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;

   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-into' : {$into=$args[$c+1]; $c++;} break;

   case '-subjid' : case '-subjectid' : {$subjectId=$args[$c+1]; $c++;} break; // se non viene specificato ritorna la lista di tutti i clienti
   case '-status' : {$status=$args[$c+1]; $c++;} break;
   case '-division' : {$division=$args[$c+1]; $c++;} break;
   case '-agentid' : {$agentId=$args[$c+1]; $c++;} break;
   case '-statusextra' : {$statusExtra=$args[$c+1]; $c++;} break;
   case '-label' : {$label=$args[$c+1]; $c++;} break;

   case '-where' : {$where=$args[$c+1]; $c++;} break;

   case '--order-by' : {$_ORDER_BY=$args[$c+1]; $c++;} break;
   case '-limit' : {$_LIMIT=$args[$c+1]; $c++;} break;

   case '--verbose' : $verbose=true; break;
  }

 $intoId = 0;
 if($into)
 {
  if(is_numeric($into))
   $intoId = $into;
  else
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT id FROM dynarc_".$_AP."_categories WHERE tag='".$into."' LIMIT 1");
   $db->Read();
   $intoId = $db->record['id'];
   $db->Close();
  }
 }


 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db3 = new AlpaDatabase();

 $_WHERE = " AND trash='0'";
 if($dateFrom)					$_WHERE.= " AND ctime>='".$dateFrom."'"; 
 if($dateTo)					$_WHERE.= " AND ctime<'".$dateTo." 23:59:59'";
 if($catId)						$_WHERE.= " AND cat_id='".$catId."'";
 else if($intoId)				$_WHERE.= " AND (hierarchy=',".$intoId.",' OR hierarchy LIKE ',".$intoId.",%' OR hierarchy LIKE '%,".$intoId.",' OR hierarchy LIKE '%,".$intoId.",%')";
 if($subjectId)					$_WHERE.= " AND subject_id='".$subjectId."'";
 if(isset($status))				$_WHERE.= " AND status='".$status."'";
 if(isset($division))			$_WHERE.= " AND division='".$division."'";
 if(isset($agentId))			$_WHERE.= " AND agent_id='".$agentId."'";
 if(isset($statusExtra))		$_WHERE.= " AND status_extra='".$statusExtra."'";
 if($label)						$_WHERE.= " AND user_labels LIKE '%,".$label.",%'";
 if($where)						$_WHERE.= " AND ".$where;

 // COUNT QUERY //
 $countqry = "SELECT COUNT(DISTINCT subject_id) FROM dynarc_".$_AP."_items WHERE ".ltrim($_WHERE ? $_WHERE : '1'," AND ");
 $db->RunQuery($countqry);
 if($db->Error)	return array('message'=>"MySQL Error: ".$db->Error."\nQRY:".$countqry, 'error'=>"MYSQL_ERROR");
 $db->Read();
 $outArr['count'] = $db->record[0];
 
 // EXEC QUERY //
 $_SUM_COLUMNS = array('amount','vat','total','tot_rit_acc','tot_ccp','tot_rinps','tot_enasarco','tot_netpay','rebate',
	'tot_expenses','tot_discount','stamp','cartage','packing_charges','agent_commiss','collection_charges','tot_paid','rest_to_pay');

 $sumcolumnsQryStr = "";
 for($c=0; $c < count($_SUM_COLUMNS); $c++)
  $sumcolumnsQryStr.= ",SUM(".$_SUM_COLUMNS[$c].")";

 $execqry = "SELECT DISTINCT subject_id FROM dynarc_".$_AP."_items WHERE ".ltrim($_WHERE ? $_WHERE : '1'," AND ")." ORDER BY ".$_ORDER_BY
	.($_LIMIT ? " LIMIT ".$_LIMIT : "");
 $db->RunQuery($execqry);
 if($db->Error)	return array('message'=>"MySQL Error: ".$db->Error."\nQRY:".$execqry, 'error'=>"MYSQL_ERROR");


 while($db->Read())
 {
  $subjectId = $db->record['subject_id'];
  $db3->RunQuery("SELECT name,code_str,taxcode,vatnumber FROM dynarc_rubrica_items WHERE id='".$subjectId."'");
  $db3->Read();
  $a = array('subject_id'=>$subjectId, 'subject_name'=>$db3->record['name'], 'subject_code'=>$db3->record['code_str'], 
	'subject_taxcode'=>$db3->record['taxcode'], 'subject_vatnumber'=>$db3->record['vatnumber']);

  $db2->RunQuery("SELECT subject_name".$sumcolumnsQryStr." FROM dynarc_".$_AP."_items WHERE subject_id='".$subjectId."'".($_WHERE ? $_WHERE : ''));
  $db2->Read();
  for($i=0; $i < count($_SUM_COLUMNS); $i++)
   $a[$_SUM_COLUMNS[$i]] = $db2->record['SUM('.$_SUM_COLUMNS[$i].')'];
  if(!$a['subject_name']) $a['subject_name'] = $db2->record['subject_name'];
  $outArr['items'][] = $a;
 }
 $db2->Close();
 $db->Close();

 if($verbose)
 {
  $out.= "<table border='0'>";
  $out.= "<tr><th>ID</th><th>CODE</th><th>SUBJECT</th><th>AMOUNT</th><th>VAT</th><th>TOTAL</th></tr>";
  for($c=0; $c < count($outArr['items']); $c++)
  {
   $item = $outArr['items'][$c];
   $out.= "<tr><td>".$item['subject_id']."</td>";
   $out.= "<td>".$item['subject_code']."</td>";
   $out.= "<td>".$item['subject_name']."</td><td>".number_format($item['amount'],2,',','.')."</td>";
   $out.= "<td>".number_format($item['vat'],2,',','.')."</td>";
   $out.= "<td>".number_format($item['total'],2,',','.')."</td>";
   $out.= "</tr>";
  }
  $out.= "</table>";
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_summaryByAgent($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array('count'=>0, 'items'=>array());

 $_AP = "commercialdocs";
 $_ORDER_BY = "agent_id ASC";
 $_LIMIT = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;

   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-into' : {$into=$args[$c+1]; $c++;} break;
   case '-agentid' : {$agentId=$args[$c+1]; $c++;} break;
   case '-status' : {$status=$args[$c+1]; $c++;} break;
   case '-statusextra' : {$statusExtra=$args[$c+1]; $c++;} break;
   case '-label' : {$label=$args[$c+1]; $c++;} break;

   case '-where' : {$where=$args[$c+1]; $c++;} break;

   case '--order-by' : {$_ORDER_BY=$args[$c+1]; $c++;} break;
   case '-limit' : case '--limit' : {$_LIMIT=$args[$c+1]; $c++;} break;

   case '--verbose' : $verbose=true; break;
  }

 $intoId = 0;
 if($into)
 {
  if(is_numeric($into))
   $intoId = $into;
  else
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT id FROM dynarc_".$_AP."_categories WHERE tag='".$into."' LIMIT 1");
   $db->Read();
   $intoId = $db->record['id'];
   $db->Close();
  }
 }


 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db3 = new AlpaDatabase();

 $_WHERE = " AND agent_id>0 AND trash='0'";
 if($dateFrom)					$_WHERE.= " AND ctime>='".$dateFrom."'"; 
 if($dateTo)					$_WHERE.= " AND ctime<'".$dateTo." 23:59:59'";
 if($catId)						$_WHERE.= " AND cat_id='".$catId."'";
 else if($intoId)				$_WHERE.= " AND (hierarchy=',".$intoId.",' OR hierarchy LIKE ',".$intoId.",%' OR hierarchy LIKE '%,".$intoId.",' OR hierarchy LIKE '%,".$intoId.",%')";

 if(isset($status))				$_WHERE.= " AND status='".$status."'";
 if(isset($division))			$_WHERE.= " AND division='".$division."'";
 if(isset($agentId))			$_WHERE.= " AND agent_id='".$agentId."'";
 if(isset($statusExtra))		$_WHERE.= " AND status_extra='".$statusExtra."'";
 if($label)						$_WHERE.= " AND user_labels LIKE '%,".$label.",%'";
 if($where)						$_WHERE.= " AND ".$where;

 // COUNT QUERY //
 $countqry = "SELECT COUNT(DISTINCT agent_id) FROM dynarc_".$_AP."_items WHERE ".ltrim($_WHERE ? $_WHERE : '1'," AND ");
 $db->RunQuery($countqry);
 if($db->Error)	return array('message'=>"MySQL Error: ".$db->Error."\nQRY:".$countqry, 'error'=>"MYSQL_ERROR");
 $db->Read();
 $outArr['count'] = $db->record[0];
 
 // EXEC QUERY //
 $_SUM_COLUMNS = array('amount','vat','total','tot_rit_acc','tot_ccp','tot_rinps','tot_enasarco','tot_netpay','rebate',
	'tot_expenses','tot_discount','stamp','cartage','packing_charges','agent_commiss','collection_charges','tot_paid','rest_to_pay');

 $sumcolumnsQryStr = "";
 for($c=0; $c < count($_SUM_COLUMNS); $c++)
  $sumcolumnsQryStr.= ",SUM(".$_SUM_COLUMNS[$c].")";

 $execqry = "SELECT DISTINCT agent_id FROM dynarc_".$_AP."_items WHERE ".ltrim($_WHERE ? $_WHERE : '1'," AND ")." ORDER BY ".$_ORDER_BY
	.($_LIMIT ? " LIMIT ".$_LIMIT : "");
 $db->RunQuery($execqry);
 if($db->Error)	return array('message'=>"MySQL Error: ".$db->Error."\nQRY:".$execqry, 'error'=>"MYSQL_ERROR");


 while($db->Read())
 {
  $agentId = $db->record['agent_id'];
  $db3->RunQuery("SELECT name,code_str,taxcode,vatnumber FROM dynarc_rubrica_items WHERE id='".$agentId."'");
  $db3->Read();
  $a = array('agent_id'=>$agentId, 'agent_name'=>$db3->record['name'], 'agent_code'=>$db3->record['code_str'], 
	'agent_taxcode'=>$db3->record['taxcode'], 'agent_vatnumber'=>$db3->record['vatnumber']);

  $db2->RunQuery("SELECT ".ltrim($sumcolumnsQryStr,',')." FROM dynarc_".$_AP."_items WHERE agent_id='".$agentId."'".($_WHERE ? $_WHERE : ''));
  $db2->Read();
  for($i=0; $i < count($_SUM_COLUMNS); $i++)
   $a[$_SUM_COLUMNS[$i]] = $db2->record['SUM('.$_SUM_COLUMNS[$i].')'];
  $outArr['items'][] = $a;
 }
 $db2->Close();
 $db->Close();

 if($verbose)
 {
  $out.= "<table border='0'>";
  $out.= "<tr><th>ID</th><th>CODE</th><th>AGENT</th><th>AMOUNT</th><th>VAT</th><th>TOTAL</th><th>COMMISS</th></tr>";
  for($c=0; $c < count($outArr['items']); $c++)
  {
   $item = $outArr['items'][$c];
   $out.= "<tr><td>".$item['agent_id']."</td>";
   $out.= "<td>".$item['agent_code']."</td>";
   $out.= "<td>".$item['agent_name']."</td>";
   $out.= "<td>".number_format($item['amount'],2,',','.')."</td>";
   $out.= "<td>".number_format($item['vat'],2,',','.')."</td>";
   $out.= "<td>".number_format($item['total'],2,',','.')."</td>";
   $out.= "<td>".number_format($item['agent_commiss'],2,',','.')."</td>";
   $out.= "</tr>";
  }
  $out.= "</table>";
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_statsByZone($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array('count'=>0, 'items'=>array());

 $_AP = "commercialdocs";
 $_LIMIT = "";
 $_CTS = "INVOICES,RECEIPTS";	// cat tag ORDERS is already included
 $_RVF = "qty";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;
   case '-rvf' : {$_RVF=$args[$c+1]; $c++;} break;

   case '-limit' : case '--limit' : {$_LIMIT=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
  }

 // MAKE QUERY
 $out.= "Get stats by zone...";
 $db = new AlpaDatabase();
 $_WHERE = "i.trash=0 AND (i.root_ct='ORDERS'";
 $x = explode(",",$_CTS);
 for($c=0; $c < count($x); $c++)
  $_WHERE.= " OR (i.root_ct='".$x[$c]."' AND el.row_ref_docap!='commercialdocs')";
 $_WHERE.= ")";

 if($dateFrom)		$_WHERE.= " AND i.ctime>='".$dateFrom."'";
 if($dateTo)		$_WHERE.= " AND i.ctime<='".$dateTo."'";

 $qry = "SELECT SUM(el.qty) AS tot_qty, SUM(el.price) AS tot_amount, i.ship_prov FROM dynarc_".$_AP."_elements AS el";
 $qry.= " LEFT JOIN dynarc_".$_AP."_items AS i ON (el.item_id=i.id AND elem_type!='note' AND elem_type!='message')";
 $qry.= " WHERE ".$_WHERE;
 $qry.= " GROUP BY ship_prov";
 switch($_RVF)
 {
  case 'qty' : $qry.= " ORDER BY tot_qty DESC"; break;
  case 'amount' : $qry.= " ORDER BY tot_amount DESC";
 }
 

 // RUN QUERY
 $db->RunQuery($qry);
 if($db->Error) return array('message'=>$out."failed!\nMySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, 'error'=>'MYSQL_ERROR');
 while($db->Read())
 {
  $a = array('province'=>strtoupper($db->record['ship_prov']), 'qty'=>$db->record['tot_qty'], 'amount'=>$db->record['tot_amount']);
  $outArr['items'][] = $a;
 }
 $db->Close();

 $outArr['count'] = count($outArr['items']);
 $out.= "done!\n".count($outArr['items'])." results found.";



 // VERBOSE
 if($verbose)
 {
  $out.= "<table cellspacing='5' cellpadding='0' border='0'>";
  $out.= "<tr><th>PROVINCE</th><th>QTY</th><th>AMOUNT</th></tr>";
  for($c=0; $c < count($outArr['items']); $c++)
  {
   $item = $outArr['items'][$c];
   $out.= "<tr><td>".($item['province'] ? $item['province'] : '&nbsp;')."</td>";
   $out.= "<td align='center'>".$item['qty']."</td>";
   $out.= "<td align='right'>".number_format($item['amount'],2,',','.')."</td></tr>";
  }
  $out.= "</table>";
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_statsByAgent($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array('count'=>0, 'items'=>array());

 $_AP = "commercialdocs";
 $_LIMIT = "";
 $_CTS = "INVOICES,RECEIPTS";	// cat tag ORDERS is already included
 $_RVF = "agent";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;
   case '-rvf' : {$_RVF=$args[$c+1]; $c++;} break;

   case '-limit' : case '--limit' : {$_LIMIT=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
  }

 // MAKE QUERY
 $out.= "Get stats by agent...";
 $db = new AlpaDatabase();
 $_WHERE = "i.trash=0 AND i.agent_id>0 AND (i.root_ct='ORDERS'";
 $x = explode(",",$_CTS);
 for($c=0; $c < count($x); $c++)
  $_WHERE.= " OR (i.root_ct='".$x[$c]."' AND el.row_ref_docap!='commercialdocs')";
 $_WHERE.= ")";

 if($dateFrom)		$_WHERE.= " AND i.ctime>='".$dateFrom."'";
 if($dateTo)		$_WHERE.= " AND i.ctime<='".$dateTo."'";

 $qry = "SELECT SUM(el.qty) AS tot_qty, SUM(el.price) AS tot_amount, i.agent_id, agent.name AS agent_name FROM dynarc_".$_AP."_elements AS el";
 $qry.= " LEFT JOIN dynarc_".$_AP."_items AS i ON (el.item_id=i.id AND elem_type!='note' AND elem_type!='message')";
 $qry.= " LEFT JOIN dynarc_rubrica_items AS agent ON i.agent_id=agent.id";
 $qry.= " WHERE ".$_WHERE;
 $qry.= " GROUP BY agent_name";
 switch($_RVF)
 {
  case 'agent' : $qry.= " ORDER BY agent_name ASC"; break;
  case 'qty' : $qry.= " ORDER BY tot_qty DESC"; break;
  case 'amount' : $qry.= " ORDER BY tot_amount DESC";
 }
 

 // RUN QUERY
 $db->RunQuery($qry);
 if($db->Error) return array('message'=>$out."failed!\nMySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, 'error'=>'MYSQL_ERROR');
 while($db->Read())
 {
  $a = array('agent_id'=>$db->record['agent_id'], 'agent_name'=>$db->record['agent_name'], 'qty'=>$db->record['tot_qty'], 
	'amount'=>$db->record['tot_amount']);
  $outArr['items'][] = $a;
 }
 $db->Close();

 $outArr['count'] = count($outArr['items']);
 $out.= "done!\n".count($outArr['items'])." results found.";



 // VERBOSE
 if($verbose)
 {
  $out.= "<table cellspacing='5' cellpadding='0' border='0'>";
  $out.= "<tr><th>AGENT</th><th>QTY</th><th>AMOUNT</th></tr>";
  for($c=0; $c < count($outArr['items']); $c++)
  {
   $item = $outArr['items'][$c];
   $out.= "<tr><td>".($item['agent_name'] ? $item['agent_name'] : '&nbsp;')."</td>";
   $out.= "<td align='center'>".$item['qty']."</td>";
   $out.= "<td align='right'>".number_format($item['amount'],2,',','.')."</td></tr>";
  }
  $out.= "</table>";
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_getDocsByIntDocRef($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array('count'=>0, 'items'=>array());

 $_AP = "commercialdocs";
 $_ORDER_BY = "ctime ASC, code_num ASC";
 $_LIMIT = "";

 $_RET_FIELDS = array("id","uid","gid","_mod","cat_id","name","ctime","hierarchy","aliasname","code_num","code_ext","tag");

 $_WHERE = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-refap' : {$docRefAP=$args[$c+1]; $c++;} break;
   case '-refid' : {$docRefID=$args[$c+1]; $c++;} break;

   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;

   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '-get' : {$get=$args[$c+1]; $c++;} break;

   case '-limit' : {$_LIMIT=$args[$c+1]; $c++;} break;
   case '--order-by' : {$_ORDER_BY=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 if(!$docRefAP) return array('message'=>"You must specify the reference archive prefix. (with: -refap DOC_REF_AP)", 'error'=>'INVALID_DOC_REF_AP');
 if(!$docRefID) return array('message'=>"You must specify the reference id. (with: -refid DOC_REF_ID)", 'error'=>'INVALID_DOC_REF_ID');

 //--------------------------------------------------------------------------//
 $_WHERE = "docref_ap='".$docRefAP."' AND docref_id='".$docRefID."'";
 if($dateFrom)			$_WHERE.= " AND ctime>='".$dateFrom."'";
 if($dateTo)			$_WHERE.= " AND ctime<'".$dateTo."'";

 if($where)				$_WHERE.= " AND ".$where;
 //--------------------------------------------------------------------------//

 if($get)
 {
  $x = explode(",",$get);
  for($c=0; $c < count($x); $c++)
  {
   if(!in_array($x[$c], $_RET_FIELDS))
	$_RET_FIELDS[] = $x[$c];
  }
 }

 $_GET = implode(",",$_RET_FIELDS);
 $list = array();
 $_LIST_BY_CAT = array();

 $db = new AlpaDatabase();
 /* COUNT QUERY */
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$_AP."_items WHERE ".$_WHERE);
 if($db->Error)	return array('message'=>"MySQL Error: ".$db->Error."\nQUERY: ".$db->lastQuery, 'error'=>'MYSQL_ERROR');
 $db->Read();
 $outArr['count'] = $db->record[0];

 /* EXEC QUERY */
 $db->RunQuery("SELECT ".$_GET." FROM dynarc_".$_AP."_items WHERE ".$_WHERE." ORDER BY ".$_ORDER_BY
	.($_LIMIT ? " LIMIT ".$_LIMIT : ""));
 if($db->Error)	return array('message'=>"MySQL Error: ".$db->Error."\nQUERY: ".$db->lastQuery, 'error'=>'MYSQL_ERROR');
 while($db->Read())
 {
  $a = array();
  for($c=0; $c < count($_RET_FIELDS); $c++)
   $a[$_RET_FIELDS[$c]] = $db->record[$_RET_FIELDS[$c]];

  // get root cat id
  if($a['hierarchy'])
  {
   $x = explode(",",ltrim($a['hierarchy'],","));
   $rootCatId = $x[0];
  }
  else
   $rootCatId = $a['cat_id'];

  if(!$_LIST_BY_CAT[$rootCatId])
   $_LIST_BY_CAT[$rootCatId] = array('id'=>$rootCatId, 'name'=>"", 'items'=>array());
  $_LIST_BY_CAT[$rootCatId]['items'][] = $a;

  $list[] = $a;
 }
 $db->Close();

 /* ORDER BY TYPE */
 reset($_LIST_BY_CAT);
 $db = new AlpaDatabase();
 while(list($k,$v) = each($_LIST_BY_CAT))
 {
  $db->RunQuery("SELECT name FROM dynarc_".$_AP."_categories WHERE id='".$k."'");
  $db->Read();
  $_LIST_BY_CAT[$k]['name'] = $db->record['name'];
 }
 $db->Close();

 reset($_LIST_BY_CAT);
 while(list($k,$v) = each($_LIST_BY_CAT))
 {
  for($c=0; $c < count($v['items']); $c++)
  {
   $a = $v['items'][$c];
   $a['root_cat_id'] = $k;
   $a['root_cat_name'] = $v['name'];
   $outArr['items'][] = $a;
  }
 }
 
 if($verbose)
 {
  $out.= "<table border='0'>";
  $out.= "<tr>";
  for($c=0; $c < count($_RET_FIELDS); $c++)
   $out.= "<th>".$_RET_FIELDS[$c]."</th>";
  $out.= "</tr>";

  for($c=0; $c < count($outArr['items']); $c++)
  {
   $out.= "<tr>";
   for($i=0; $i < count($_RET_FIELDS); $i++)
	$out.= "<td>".$outArr['items'][$c][$_RET_FIELDS[$i]]."</td>";
   $out.= "</tr>";
  }
  $out.= "</table>";
 }

 $out.= "\n".$outArr['count']." results found.";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_fixBookIncArts($args, $sessid, $shellid)
{
 // Questa funzione serve per aggiustare il num. di articoli impegnati o ordinati (in base al tipo di doc.)
 $out = "";
 $outArr = array();

 $_AP = "commercialdocs";
 $_ACTION = "";
 $_ELEMENTS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : case '-docap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-docid' : {$docID=$args[$c+1]; $c++;} break;	// Se viene specificato il doc. esegue l'operazione su tutti gli elementi
   case '-elid' : {$elID=$args[$c+1]; $c++;} break;		// ... altrimenti è possibile specificare solo l'elemento interessato
   case '-action' : {$action=$args[$c+1]; $c++;} break;	// delete, restore, new
  }

 if(!$action) return array('message'=>"Fix book/inc arts failed!You must specify the action.",'error'=>"INVALID_ACTION");
 if(!$docID && !$elID) return array('message'=>"Fix book/inc arts failed!You must specify the document ID or the element ID.",'error'=>"INVALID_DOC_OR_EL");

 if($elID)
 {
  // ricava l'ID del documento e verifica subito se si tratta di un articolo
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT item_id,elem_type,ref_ap,ref_id,qty FROM dynarc_".$_AP."_elements WHERE id='".$elID."'");
  $db->Read();
  $docID = $db->record['item_id'];
  $ok = false;
  switch($db->record['elem_type'])
  {
   case 'article' : case 'finalproduct' : case 'component' : case 'material' : case 'book' : $ok=true; break;
   default : return array('message'=>"No change to do"); break;
  }
  $_ELEMENTS[] = array('ap'=>$db->record['ref_ap'], 'id'=>$db->record['ref_id'], 'qty'=>$db->record['qty']);
  $db->Close();
 }

 /* VERIFICA CHE TIPO DI DOCUMENTO E' */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT cat_id,hierarchy,status FROM dynarc_".$_AP."_items WHERE id='".$docID."'");
 if($db->Error) return array('message'=>"MySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
 $db->Read();
 if($db->record['hierarchy'])
 {
  $x = explode(",",ltrim($db->record['hierarchy'],","));
  $catId = $x[0] ? $x[0] : $db->record['cat_id'];
 }
 else
  $catId = $db->record['cat_id'];
 $status = $db->record['status'];

 $db->RunQuery("SELECT tag FROM dynarc_".$_AP."_categories WHERE id='".$catId."'");
 if($db->Error) return array('message'=>"MySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
 $db->Read();
 $_CAT_TAG = $db->record['tag'];
 $db->Close();

 /* IN BASE ALLA CATEGORIA E ALLO STATUS DEL DOCUMENTO DECIDE IL DA FARSI */
 switch(strtoupper($_CAT_TAG))
 {
  case 'PREEMPTIVES' : case 'ORDERS' : case 'DDT' : case 'INTERVREPORTS' : {
	 if($status == 3)
	 {
	  switch($action)
	  {
	   case 'new' : case 'restore' : $_ACTION = "BOOK"; break;	// impegna/re-impegna gli articoli
	   case 'delete' : $_ACTION = "UNBOOK"; break;				// disimpegna gli articoli
	  }
	 }
	 else return array('message'=>"No change to do, because status=".$status);
	} break;

  case 'VENDORORDERS' : {
	 if($status <= 3)
	 {
	  switch($action)
	  {
	   case 'new' : case 'restore' : $_ACTION = "ORDER"; break;	// ordina/ri-ordina gli articoli
	   case 'delete' : $_ACTION = "UNORDER"; break;				// dis-ordina gli articoli
	  }
	 }
	 else return array('message'=>"No change to do, because status=".$status);
	} break;

  default : return array('message'=>"No change to do, because doctype is not preemptive,order,ddt,intervreport or vendororder."); break;
 }

 if(!$_ACTION) return array('message'=>"Fix book/inc arts failed! Invalid action.",'error'=>"INVALID_ACTION");

 /* RICAVA LA LISTA DEGLI ELEMENTI */
 if(!$elID)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT elem_type,ref_ap,ref_id,qty FROM dynarc_".$_AP."_elements WHERE item_id='".$docID."'");
  while($db->Read())
  {
   switch($db->record['elem_type'])
   {
    case 'article' : case 'finalproduct' : case 'component' : case 'material' : case 'book' : $_ELEMENTS[] = array('ap'=>$db->record['ref_ap'], 'id'=>$db->record['ref_id'], 'qty'=>$db->record['qty']); break;
   }
  }
  $db->Close();
 }

 if(!count($_ELEMENTS)) return array('message'=>"No change to do, because no elements found.");

 $db = new AlpaDatabase();
 for($c=0; $c < count($_ELEMENTS); $c++)
 {
  $el = $_ELEMENTS[$c];
  switch($_ACTION)
  {
   case 'BOOK' : {
	 $db->RunQuery("UPDATE dynarc_".$el['ap']."_items SET booked=booked+".$el['qty']." WHERE id='".$el['id']."'");
	 if($db->Error) return array('message'=>"MySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
	} break;

   case 'UNBOOK' : {
	 $db->RunQuery("UPDATE dynarc_".$el['ap']."_items SET booked=booked-".$el['qty']." WHERE id='".$el['id']."'");
	 if($db->Error) return array('message'=>"MySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
	} break;

   case 'ORDER' : {
	 $db->RunQuery("UPDATE dynarc_".$el['ap']."_items SET incoming=incoming+".$el['qty']." WHERE id='".$el['id']."'");
	 if($db->Error) return array('message'=>"MySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
	} break;

   case 'UNORDER' : {
	 $db->RunQuery("UPDATE dynarc_".$el['ap']."_items SET incoming=incoming-".$el['qty']." WHERE id='".$el['id']."'");
	 if($db->Error) return array('message'=>"MySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
	} break;
  }
 }
 $db->Close();

 $out.= "done!";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_getLastVendorPrice($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array("count"=>0, "items"=>array());

 $_ITEM_FIELDS = "ctime,id,name,subject_id,subject_name";
 $_ELM_FIELDS = "vendor_price";

 $_CAT_TAGS = "DDTIN,PURCHASEINVOICES";
 $_CAT_IDS = array();

 $_LIMIT = 10;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;

   case '-vendor' : case '-vendorid' : case '-subject' : case '-subjectid' : case '-subjid' : {$vendorId=$args[$c+1]; $c++;} break;
   case '-from' : case '-datefrom' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : case '-dateto' : {$dateTo=$args[$c+1]; $c++;} break;
   case '-cat-tags' : {$_CAT_TAGS=$args[$c+1]; $c++;} break; // cat tag separated by comma (,)

   case '-limit' : {$_LIMIT=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
  }

 if(!$_AP) return array('message'=>"commercialdocs:get-last-vendorprice error! You must specify the product archive prefix.", 'error'=>'INVALID_ARCHIVE_PREFIX');
 if(!$_ID) return array('message'=>"commercialdocs:get-last-vendorprice error! You must specify the product id.", 'error'=>'INVALID_PRODUCT_ID');

 $ret = GShell("dynarc item-info -ap '".$_AP."' -id '".$_ID."'", $sessid, $shellid);
 if($ret['error']) return array('message'=>"commercialdocs:get-last-vendorprice error!\n".$ret['message'], 'error'=>$ret['error']);

 $_ITEM_FIELDS_X = explode(",", $_ITEM_FIELDS);
 $_ELM_FIELDS_X = explode(",", $_ELM_FIELDS);

 // get categories
 $x = explode(",", $_CAT_TAGS);
 for($c=0; $c < count($x); $c++)
 {
  $catTag = $x[$c];
  $ret = GShell("dynarc cat-info -ap 'commercialdocs' -tag '".$catTag."'",$sessid,$shellid);
  if($ret['error']) return array('message'=>"commercialdocs:get-last-vendorprice error!\n".$ret['message'], 'error'=>$ret['error']);
  $_CAT_IDS[] = $ret['outarr']['id'];
  // get subcategories
  $ret = GShell("dynarc cat-list -ap 'commercialdocs' -parent '".$ret['outarr']['id']."'",$sessid,$shellid);
  if($ret['error']) return array('message'=>"commercialdocs:get-last-vendorprice error!\n".$ret['message'], 'error'=>$ret['error']);
  for($i=0; $i < count($ret['outarr']); $i++)
   $_CAT_IDS[] = $ret['outarr'][$i]['id'];
 }
 
 // make query
 $_QRY = "SELECT ";
 $_COUNT_QRY = "SELECT COUNT(*) ";

 $q = "";
 for($c=0; $c < count($_ITEM_FIELDS_X); $c++)
  $q.= ",i.".$_ITEM_FIELDS_X[$c];
 $_QRY.= ltrim($q,",");

 $q = "";
 for($c=0; $c < count($_ELM_FIELDS_X); $c++)
  $q.= ",e.".$_ELM_FIELDS_X[$c];
 $_QRY.= $q;

 $_QRY.= " FROM dynarc_commercialdocs_items AS i LEFT JOIN dynarc_commercialdocs_elements AS e ON e.item_id=i.id";
 $_COUNT_QRY.= " FROM dynarc_commercialdocs_items AS i LEFT JOIN dynarc_commercialdocs_elements AS e ON e.item_id=i.id";

 $_QRY.= " WHERE e.ref_ap='".$_AP."' AND e.ref_id='".$_ID."' AND i.trash='0'";
 $_COUNT_QRY.= " WHERE e.ref_ap='".$_AP."' AND e.ref_id='".$_ID."' AND i.trash='0'";

 if($vendorId)
 {
  $_QRY.= " AND i.subject_id='".$vendorId."'";
  $_COUNT_QRY.= " AND i.subject_id='".$vendorId."'";
 }
 if($dateFrom)
 {
  $_QRY.= " AND i.ctime>='".$dateFrom."'";
  $_COUNT_QRY.= " AND i.ctime>='".$dateFrom."'";
 }
 if($dateTo)
 {
  $_QRY.= " AND i.ctime<'".$dateTo." 23:59:59'";
  $_COUNT_QRY.= " AND i.ctime<'".$dateTo." 23:59:59'";
 }

 // filter by category
 $q = "";
 for($c=0; $c < count($_CAT_IDS); $c++)
  $q.= " OR i.cat_id='".$_CAT_IDS[$c]."'";
 $_QRY.= " AND (".ltrim($q, ' OR ').")";
 $_COUNT_QRY.= " AND (".ltrim($q, ' OR ').")";

 $_QRY.= " ORDER BY i.ctime DESC, i.ordering DESC";
 if($_LIMIT) $_QRY.= " LIMIT ".$_LIMIT;



 $db = new AlpaDatabase();

 // count qry
 $db->RunQuery($_COUNT_QRY);
 if($db->Error) return array('message'=>"commercialdocs:get-last-saleprice MySQL Error:\n".$db->Error, 'error'=>'MYSQL_ERROR');
 $db->Read();
 $_COUNT = $db->record[0];
 $outArr['count'] = $_COUNT;

 // exec qry
 $db->RunQuery($_QRY);
 if($db->Error) return array('message'=>"commercialdocs:get-last-vendorprice MySQL Error:\n".$db->Error, 'error'=>'MYSQL_ERROR');
 while($db->Read())
 {
  $a = array();
  for($c=0; $c < count($_ITEM_FIELDS_X); $c++)
   $a[$_ITEM_FIELDS_X[$c]] = $db->record[$_ITEM_FIELDS_X[$c]];
  for($c=0; $c < count($_ELM_FIELDS_X); $c++)
   $a[$_ELM_FIELDS_X[$c]] = $db->record[$_ELM_FIELDS_X[$c]];
  $outArr['items'][] = $a;
 }
 $db->Close();

 if($verbose)
 {
  $out.= "<table border='0'><tr>";
  for($c=0; $c < count($_ITEM_FIELDS_X); $c++)
   $out.= "<th>".$_ITEM_FIELDS_X[$c]."</th>";
  for($c=0; $c < count($_ELM_FIELDS_X); $c++)
   $out.= "<th>".$_ELM_FIELDS_X[$c]."</th>";
  $out.= "</tr>";

  for($c=0; $c < count($outArr['items']); $c++)
  {
   $data = $outArr['items'][$c];
   $out.= "<tr>";
   for($i=0; $i < count($_ITEM_FIELDS_X); $i++)
    $out.= "<td>".$data[$_ITEM_FIELDS_X[$i]]."</td>";
   for($i=0; $i < count($_ELM_FIELDS_X); $i++)
    $out.= "<td>".$data[$_ELM_FIELDS_X[$i]]."</td>";
   $out.= "</tr>";
  }
  $out.= "</table>";
 }

 $out.= "\ncount: ".$_COUNT;

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_getLastSalePrice($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array("count"=>0, "items"=>array());

 $_ITEM_FIELDS = "ctime,id,name,subject_id,subject_name";
 $_ELM_FIELDS = "price";

 $_CAT_TAGS = "DDT,INVOICES,RECEIPTS";
 $_CAT_IDS = array();

 $_LIMIT = 10;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;

   case '-subject' : case '-subjectid' : case '-subjid' : {$subjectId=$args[$c+1]; $c++;} break;
   case '-from' : case '-datefrom' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : case '-dateto' : {$dateTo=$args[$c+1]; $c++;} break;
   case '-cat-tags' : {$_CAT_TAGS=$args[$c+1]; $c++;} break; // cat tag separated by comma (,)

   case '-limit' : {$_LIMIT=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
  }

 if(!$_AP) return array('message'=>"commercialdocs:get-last-saleprice error! You must specify the product archive prefix.", 'error'=>'INVALID_ARCHIVE_PREFIX');
 if(!$_ID) return array('message'=>"commercialdocs:get-last-saleprice error! You must specify the product id.", 'error'=>'INVALID_PRODUCT_ID');

 $ret = GShell("dynarc item-info -ap '".$_AP."' -id '".$_ID."'", $sessid, $shellid);
 if($ret['error']) return array('message'=>"commercialdocs:get-last-saleprice error!\n".$ret['message'], 'error'=>$ret['error']);

 $_ITEM_FIELDS_X = explode(",", $_ITEM_FIELDS);
 $_ELM_FIELDS_X = explode(",", $_ELM_FIELDS);

 // get categories
 $x = explode(",", $_CAT_TAGS);
 for($c=0; $c < count($x); $c++)
 {
  $catTag = $x[$c];
  $ret = GShell("dynarc cat-info -ap 'commercialdocs' -tag '".$catTag."'",$sessid,$shellid);
  if($ret['error']) return array('message'=>"commercialdocs:get-last-saleprice error!\n".$ret['message'], 'error'=>$ret['error']);
  $_CAT_IDS[] = $ret['outarr']['id'];
  // get subcategories
  $ret = GShell("dynarc cat-list -ap 'commercialdocs' -parent '".$ret['outarr']['id']."'",$sessid,$shellid);
  if($ret['error']) return array('message'=>"commercialdocs:get-last-saleprice error!\n".$ret['message'], 'error'=>$ret['error']);
  for($i=0; $i < count($ret['outarr']); $i++)
   $_CAT_IDS[] = $ret['outarr'][$i]['id'];
 }
 
 // make query
 $_QRY = "SELECT ";
 $_COUNT_QRY = "SELECT COUNT(*) ";

 $q = "";
 for($c=0; $c < count($_ITEM_FIELDS_X); $c++)
  $q.= ",i.".$_ITEM_FIELDS_X[$c];
 $_QRY.= ltrim($q,",");

 $q = "";
 for($c=0; $c < count($_ELM_FIELDS_X); $c++)
  $q.= ",e.".$_ELM_FIELDS_X[$c];
 $_QRY.= $q;

 $_QRY.= " FROM dynarc_commercialdocs_items AS i LEFT JOIN dynarc_commercialdocs_elements AS e ON e.item_id=i.id";
 $_COUNT_QRY.= " FROM dynarc_commercialdocs_items AS i LEFT JOIN dynarc_commercialdocs_elements AS e ON e.item_id=i.id";

 $_QRY.= " WHERE e.ref_ap='".$_AP."' AND e.ref_id='".$_ID."' AND i.trash='0'";
 $_COUNT_QRY.= " WHERE e.ref_ap='".$_AP."' AND e.ref_id='".$_ID."' AND i.trash='0'";

 if($subjectId) 
 {
  $_QRY.= " AND i.subject_id='".$subjectId."'";
  $_COUNT_QRY.= " AND i.subject_id='".$subjectId."'";
 }
 if($dateFrom)
 {
  $_QRY.= " AND i.ctime>='".$dateFrom."'";
  $_COUNT_QRY.= " AND i.ctime>='".$dateFrom."'";
 }
 if($dateTo)
 {
  $_QRY.= " AND i.ctime<'".$dateTo." 23:59:59'";
  $_COUNT_QRY.= " AND i.ctime<'".$dateTo." 23:59:59'";
 }

 // filter by category
 $q = "";
 for($c=0; $c < count($_CAT_IDS); $c++)
  $q.= " OR i.cat_id='".$_CAT_IDS[$c]."'";
 $_QRY.= " AND (".ltrim($q, ' OR ').")";
 $_COUNT_QRY.= " AND (".ltrim($q, ' OR ').")";


 $_QRY.= " ORDER BY i.ctime DESC, i.ordering DESC";
 if($_LIMIT) $_QRY.= " LIMIT ".$_LIMIT;


 $db = new AlpaDatabase();

 // count qry
 $db->RunQuery($_COUNT_QRY);
 if($db->Error) return array('message'=>"commercialdocs:get-last-saleprice MySQL Error:\n".$db->Error, 'error'=>'MYSQL_ERROR');
 $db->Read();
 $_COUNT = $db->record[0];
 $outArr['count'] = $_COUNT;

 // exec qry
 $db->RunQuery($_QRY);
 if($db->Error) return array('message'=>"commercialdocs:get-last-saleprice MySQL Error:\n".$db->Error, 'error'=>'MYSQL_ERROR');
 while($db->Read())
 {
  $a = array();
  for($c=0; $c < count($_ITEM_FIELDS_X); $c++)
   $a[$_ITEM_FIELDS_X[$c]] = $db->record[$_ITEM_FIELDS_X[$c]];
  for($c=0; $c < count($_ELM_FIELDS_X); $c++)
   $a[$_ELM_FIELDS_X[$c]] = $db->record[$_ELM_FIELDS_X[$c]];
  $outArr['items'][] = $a;
 }
 $db->Close();

 if($verbose)
 {
  $out.= "<table border='0'><tr>";
  for($c=0; $c < count($_ITEM_FIELDS_X); $c++)
   $out.= "<th>".$_ITEM_FIELDS_X[$c]."</th>";
  for($c=0; $c < count($_ELM_FIELDS_X); $c++)
   $out.= "<th>".$_ELM_FIELDS_X[$c]."</th>";
  $out.= "</tr>";

  for($c=0; $c < count($outArr['items']); $c++)
  {
   $data = $outArr['items'][$c];
   $out.= "<tr>";
   for($i=0; $i < count($_ITEM_FIELDS_X); $i++)
    $out.= "<td>".$data[$_ITEM_FIELDS_X[$i]]."</td>";
   for($i=0; $i < count($_ELM_FIELDS_X); $i++)
    $out.= "<td>".$data[$_ELM_FIELDS_X[$i]]."</td>";
   $out.= "</tr>";
  }
  $out.= "</table>";
 }

 $out.= "\ncount: ".$_COUNT;

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_generatePrecompiledDocument($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $_AP = "commercialdocsprec";
 $_ID = 0;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;
   case '-name' : case '-title' : {$title=$args[$c+1]; $c++;} break;
   case '-xmldata' : case '-xml' : {$xmlData=$args[$c+1]; $c++;} break;
  }

 if(!$title) return array('message'=>"Generate precompiled document failed!\nInvalid title.", "error"=>"INVALID_TITLE");
 if(!$_ID) return array('message'=>"Generate precompiled document failed!\nInvalid source document ID.", "error"=>"INVALID_DOCUMENT_ID");
 if(!$xmlData) return array('message'=>"Generate precompiled document failed!\nXML data is empty", "error"=>"EMPTY_XMLDATA");

 // Get source document info
 $ret = GShell("dynarc item-info -ap 'commercialdocs' -id '".$_ID."' -extget `cdinfo,cdelements`", $sessid, $shellid);
 if($ret['error']) return array('message'=>"Generate precompiled document failed!\n".$ret['message'], 'error'=>$ret['error']);
 $docInfo = $ret['outarr'];

 // Get source category info
 $ret = GShell("dynarc cat-info -ap 'commercialdocs' -id '".$docInfo['cat_id']."'", $sessid, $shellid);
 if($ret['error']) return array('message'=>"Generate precompiled document failed! Unable to detect source category info.\n".$ret['message'], 'error'=>$ret['error']);
 $docCatInfo = $ret['outarr'];

 $xmlData = ltrim(rtrim($xmlData));
 $xml = new GXML();
 if(!$xml->LoadFromString("<xml>".$xmlData."</xml>"))
  return array('message'=>"Generate precompiled document failed!\nXML data parse failed!", "error"=>"XML_DATA_PARSE_FAILED");

 $f = $xml->GetElementsByTagName("fields");
 $_FIELDS = $f[0]->toArray();

 // Check if destination category exists, otherwise it will be created.
 $ret = GShell("dynarc cat-info -ap '".$_AP."' -tag '".$docCatInfo['tag']."'", $sessid, $shellid);
 if($ret['error'])
 {
  switch($ret['error'])
  {
   case 'CATEGORY_DOES_NOT_EXISTS' : {
	 // generate category
	 $ret = GShell("dynarc new-cat -ap '".$_AP."' -name `".$docCatInfo['name']."` -tag `".$docCatInfo['tag']."` -group commercialdocs --publish", $sessid, $shellid);
	 if($ret['error'])
	  return array('message'=>"Generate precompiled document failed! Unable to create destination category.\n".$ret['message'], 'error'=>$ret['error']);
	 $destCatInfo = $ret['outarr'];
	} break;

   default : return array('message'=>"Generate precompiled document failed!\n".$ret['message'], 'error'=>$ret['error']); break;
  }
 }
 else
  $destCatInfo = $ret['outarr'];
 
 $_CMD = "dynarc new-item -ap '".$_AP."' -name `".$title."` -cat '".$destCatInfo['id']."' -alias `".$docInfo['aliasname']."`";
 $_EXTSET = "";
 $cdinfo = ",tag='".$docInfo['tag']."'";		// <-- cdinfo extset
 
 if($_FIELDS)
 {
  reset($_FIELDS);
  while(list($k,$v) = each($_FIELDS))
  {
   switch($k)
   {
	// GENERIC
	case 'subject' : if($v){
		 $cdinfo.= ",subject='''".$docInfo['subject_name']."''',subjectid='".$docInfo['subject_id']."'";
		 $cdinfo.= ",paymentmode='".$docInfo['paymentmode']."',bank='".$docInfo['banksupport_id']."',ourbank='"
			.$docInfo['ourbanksupport_id']."',pricelist='".$docInfo['pricelist_id']."'";
		} break;

	case 'shipping' : if($v){
		 $cdinfo.= ",ship-subject-id='".$docInfo['ship_subject_id']."',ship-contact-id='".$docInfo['ship_contact_id']."',ship-recp='''"
			.$docInfo['ship_recp']."''',ship-addr='''".$docInfo['ship_addr']."''',ship-city='''".$docInfo['ship_city']."''',ship-zip='"
			.$docInfo['ship_zip']."',ship-prov='".$docInfo['ship_prov']."',ship-cc='".$docInfo['ship_cc']."'";
		} break;

	// TRANSPORT
    case 'trans_method' : if($v){$cdinfo.= ",trans-method='".$docInfo['trans_method']."'";} break;
    case 'trans_shipper' : if($v){$cdinfo.= ",trans-shipper='".$docInfo['trans_shipper']."'";} break;
    case 'trans_numplate' : if($v){$cdinfo.= ",trans-numplate='".$docInfo['trans_numplate']."'";} break;
    case 'trans_causal' : if($v){$cdinfo.= ",trans-causal='".$docInfo['trans_causal']."'";} break;
    case 'trans_aspect' : if($v){$cdinfo.= ",trans-aspect='".$docInfo['trans_aspect']."'";} break;
    case 'trans_num' : if($v){$cdinfo.= ",trans-num='".$docInfo['trans_num']."'";} break;
    case 'trans_weight' : if($v){$cdinfo.= ",trans-weight='".$docInfo['trans_weight']."'";} break;
    case 'trans_freight' : if($v){$cdinfo.= ",trans-freight='".$docInfo['trans_freight']."'";} break;
    case 'cartage' : if($v){$cdinfo.= ",cartage='".$docInfo['cartage']."',cartage-vatid='".$docInfo['cartage_vatid']."'";} break;
    case 'packing_charges' : if($v){
		 $cdinfo.= ",packing-charges='".$docInfo['packing_charges']."',packing-charges-vatid='".$docInfo['packing_charges_vatid']."'";
		} break;


	// OTHER
	case 'note' : $_CMD.= " -desc `".$docInfo['desc']."`"; break;
	case 'attachments' : { /* TODO: da fare */ } break;
	case 'agent' : {$cdinfo.= ",agentid='".$docInfo['agent_id']."'";} break;
	case 'internal_docref' : {$cdinfo.= ",docrefap='".$docInfo['docref_ap']."',docrefid='".$docInfo['docref_id']."'";} break;

   }
  }
 }
 

 if($cdinfo) $_EXTSET.= ",cdprecinfo.".ltrim($cdinfo, ",");
 if($_EXTSET) $_CMD.= " -extset `".ltrim($_EXTSET, ",")."`";

 // GENERATE DOC
 $ret = GShell($_CMD, $sessid, $shellid);
 if($ret['error']) return array('message'=>"Generate precompiled document failed! Unable to generate the document:\n".$ret['message'], 'error'=>$ret['error']);
 $destDocInfo = $ret['outarr'];

 $outArr = $destDocInfo;


 /* INSERT ELEMENTS */
 for($i=0; $i < count($docInfo['elements']); $i++)
 {
  $el = $docInfo['elements'][$i];
  $ret = GShell("dynarc edit-item -ap `".$_AP."` -id `".$destDocInfo['id']."` -extset `cdprecelements.type='".$el['type']."',refap='".$el['ref_ap']."',refid='"
	.$el['ref_id']."',code='".$el['code']."',vencode='".$el['vencode']."',sn='".$el['serialnumber']."',lot='".$el['lot']."',accountid='"
	.$el['account_id']."',name='''".$el['name']."''',desc='''".$el['desc']."''',qty='".$el['qty']."',extraqty='".$el['extraqty']."',price='"
	.$el['price']."',discount='".$el['discount']."',discount2='".$el['discount2']."',discount3='".$el['discount3']."',vatrate='"
	.$el['vatrate']."',vatid='".$el['vatid']."',vattype='".$el['vattype']."',units='".$el['units']."',pricelistid='"
	.$el['pricelist_id']."',brandid='".$el['brand_id']."',vendorid='".$el['vendor_id']."',vendorprice='"
	.$el['vendor_price']."',coltint='''".$el['variant_coltint']."''',sizmis='''".$el['variant_sizmis']."''',ritaccapply='"
	.$el['ritaccapply']."',ccpapply='".$el['ccpapply']."',xmldata='''".$el['xmldata']."'''`");
  if($ret['error']) return $ret;
 }

 /* UPDATING TOTALS */
 $out.= "\nUpdating totals...";
 $ret = GShell("commercialdocs update-predoc-totals -id '".$destDocInfo['id']."'",$sessid,$shellid);
 if($ret['error'])
  $out.= "failed\n".$ret['message'];
 else
 {
  $out.= "\ndone!\n".$ret['message'];
  $outArr = $ret['outarr'];
 }


 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_generateFromPrecompiledDocument($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $_ID = 0;
 $_SUBJID = 0;
 $_SUBJNAME = "";
 $_SHIP_SUBJID = 0;
 $subjectInfo = null;
 $shipSubjectInfo = null;

 $ctime = date('Y-m-d');

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;
   case '-subjid' : {$_SUBJID=$args[$c+1]; $c++;} break;
   case '-ctime' : {$ctime=$args[$c+1]; $c++;} break;

   case '-ship-subjid' : {$shipSubjId=$args[$c+1]; $c++;} break;

   case '-ship-recp' : {$shipRecp=$args[$c+1]; $c++;} break;
   case '-ship-addr' : {$shipAddr=$args[$c+1]; $c++;} break;
   case '-ship-city' : {$shipCity=$args[$c+1]; $c++;} break;
   case '-ship-zip' : {$shipZip=$args[$c+1]; $c++;} break;
   case '-ship-prov' : {$shipProv=$args[$c+1]; $c++;} break;
   case '-ship-cc' : {$shipCC=$args[$c+1]; $c++;} break;

   case '-tracknum' : {$trackingNumber=$args[$c+1]; $c++;} break;
  }

 // Get pre-compiled document info
 $ret = GShell("dynarc item-info -ap 'commercialdocsprec' -id '".$_ID."' -extget `cdprecinfo,cdprecelements`", $sessid, $shellid);
 if($ret['error']) return array('message'=>"Generate from precompiled document failed!\n".$ret['message'], 'error'=>$ret['error']);
 $precDocInfo = $ret['outarr'];

 // Get pre-compiled category info
 $ret = GShell("dynarc cat-info -ap 'commercialdocsprec' -id '".$precDocInfo['cat_id']."'", $sessid, $shellid);
 if($ret['error']) return array('message'=>"Generate from precompiled document failed! Unable to detect source category info.\n".$ret['message'], 'error'=>$ret['error']);
 $precDocCatInfo = $ret['outarr'];
 
 // Get destination category info
 $ret = GShell("dynarc cat-info -ap 'commercialdocs' -tag '".$precDocCatInfo['tag']."'", $sessid, $shellid);
 if($ret['error']) return array('message'=>"Generate from precompiled document failed! Unable to detect destination category info.\n".$ret['message'], 'error'=>$ret['error']);
 $destCatInfo = $ret['outarr'];

 if($shipSubjId)
 {
  // get ship subject info
  $ret = GShell("dynarc item-info -ap rubrica -id '".$shipSubjId."' -extget `rubricainfo,contacts,banks`",$sessid,$shellid);
  if($ret['error']) return array('message'=>"Generate from precompiled document failed! Unable to detect ship subject.\n".$ret['message'], 'error'=>$ret['error']);
  $shipSubjectInfo = $ret['outarr'];

  if(!$shipRecp)	
   $shipRecp = ($shipSubjectInfo['contacts'] && $shipSubjectInfo['contacts'][0]) ? $shipSubjectInfo['contacts'][0]['name'] : $shipSubjectInfo['name'];
  if(!$shipAddr)
  {
   $shipAddr = ($shipSubjectInfo['contacts'] && $shipSubjectInfo['contacts'][0]) ? $shipSubjectInfo['contacts'][0]['address'] : "";
   $shipCity = ($shipSubjectInfo['contacts'] && $shipSubjectInfo['contacts'][0]) ? $shipSubjectInfo['contacts'][0]['city'] : "";
   $shipZip = ($shipSubjectInfo['contacts'] && $shipSubjectInfo['contacts'][0]) ? $shipSubjectInfo['contacts'][0]['zipcode'] : "";
   $shipProv = ($shipSubjectInfo['contacts'] && $shipSubjectInfo['contacts'][0]) ? $shipSubjectInfo['contacts'][0]['province'] : "";
   $shipCC = ($shipSubjectInfo['contacts'] && $shipSubjectInfo['contacts'][0]) ? $shipSubjectInfo['contacts'][0]['countrycode'] : "";
  }
 }

 if($_SUBJID)
 {
  // get subject info
  $ret = GShell("dynarc item-info -ap rubrica -id '".$_SUBJID."' -extget `rubricainfo,contacts,banks`",$sessid,$shellid);
  if($ret['error']) return array('message'=>"Generate from precompiled document failed! Unable to detect subject.\n".$ret['message'], 'error'=>$ret['error']);
  $subjectInfo = $ret['outarr'];

  if(!$shipRecp)	$shipRecp = ($subjectInfo['contacts'] && $subjectInfo['contacts'][0]) ? $subjectInfo['contacts'][0]['name'] : $subjectInfo['name'];
  if(!$shipAddr)
  {
   $shipAddr = ($subjectInfo['contacts'] && $subjectInfo['contacts'][0]) ? $subjectInfo['contacts'][0]['address'] : "";
   $shipCity = ($subjectInfo['contacts'] && $subjectInfo['contacts'][0]) ? $subjectInfo['contacts'][0]['city'] : "";
   $shipZip = ($subjectInfo['contacts'] && $subjectInfo['contacts'][0]) ? $subjectInfo['contacts'][0]['zipcode'] : "";
   $shipProv = ($subjectInfo['contacts'] && $subjectInfo['contacts'][0]) ? $subjectInfo['contacts'][0]['province'] : "";
   $shipCC = ($subjectInfo['contacts'] && $subjectInfo['contacts'][0]) ? $subjectInfo['contacts'][0]['countrycode'] : "";
  }
 }


 $_CMD = "dynarc new-item -ap 'commercialdocs' -cat '".$destCatInfo['id']."' -ctime '".$ctime."' -alias `"
	.$precDocInfo['aliasname']."` -desc `".$precDocInfo['desc']."`";

 $_EXTSET = "";
 $cdinfo = "";

 if($_SUBJID)
 {
  $_SUBJNAME = $subjectInfo['name'];
 }
 else
 {
  $_SUBJID = $precDocInfo['subject_id'];
  $_SUBJNAME = $precDocInfo['subject_name'];
 }

 $cdinfo.= ",subject='''".$_SUBJNAME."''',subjectid='".$_SUBJID."',tag='".$precDocInfo['tag']."'";

 if($subjectInfo)
 {
  $cdinfo.= ",paymentmode='".$subjectInfo['paymentmode']."'";
  if($subjectInfo['banks'] && $subjectInfo['banks'][0])
   $cdinfo.= ",bank='".$subjectInfo['banks'][0]['id']."'";
  if($subjectInfo['ourbanksupport_id'])
   $cdinfo.= ",ourbank='".$subjectInfo['ourbanksupport_id']."'";
  else if($precDocInfo['ourbanksupport_id'])
   $cdinfo.= ",ourbank='".$precDocInfo['ourbanksupport_id']."'";
  if($subjectInfo['pricelist_id'])
   $cdinfo.= ",pricelist='".$subjectInfo['pricelist_id']."'";
  else if($precDocInfo['pricelist_id'])
   $cdinfo.= ",pricelist='".$precDocInfo['pricelist_id']."'";
 }
 else if($precDocInfo['subject_id'])
 {
  $cdinfo.= ",paymentmode='".$precDocInfo['paymentmode']."',bank='".$precDocInfo['banksupport_id']."',ourbank='"
	.$precDocInfo['ourbanksupport_id']."',pricelist='".$precDocInfo['pricelist_id']."'";
 }

 if(!$shipRecp)		$shipRecp = $precDocInfo['ship_recp'];
 if(!$shipAddr)
 {
  $shipAddr = $precDocInfo['ship_addr'];
  $shipCity = $precDocInfo['ship_city'];
  $shipZip = $precDocInfo['ship_zip'];
  $shipProv = $precDocInfo['ship_prov'];
  $shipCC = $precDocInfo['ship_cc'];
 }

 if($shipSubjectInfo)
 {
  $_SHIP_SUBJID = $shipSubjectInfo['id'];
  $_SHIP_CONTACTID = 0;		/* TODO: da vedere */
 }
 else
 {
  $_SHIP_SUBJID = $precDocInfo['ship_subject_id'];
  $_SHIP_CONTACTID = $precDocInfo['ship_contact_id'];
 }

 $cdinfo.= ",ship-subject-id='".$_SHIP_SUBJID."',ship-contact-id='".$_SHIP_CONTACTID."',ship-recp='''"
			.$shipRecp."''',ship-addr='''".$shipAddr."''',ship-city='''".$shipCity."''',ship-zip='"
			.$shipZip."',ship-prov='".$shipProv."',ship-cc='".$shipCC."'";
 $cdinfo.= ",trans-method='".$precDocInfo['trans_method']."'";
 $cdinfo.= ",trans-shipper='".$precDocInfo['trans_shipper']."'";
 $cdinfo.= ",trans-numplate='".$precDocInfo['trans_numplate']."'";
 if($trackingNumber) $cdinfo.= ",tracknum='".$trackingNumber."'";
 $cdinfo.= ",trans-causal='".$precDocInfo['trans_causal']."'";
 $cdinfo.= ",trans-aspect='".$precDocInfo['trans_aspect']."'";
 $cdinfo.= ",trans-num='".$precDocInfo['trans_num']."'"; 
 $cdinfo.= ",trans-weight='".$precDocInfo['trans_weight']."'";
 $cdinfo.= ",trans-freight='".$precDocInfo['trans_freight']."'";
 $cdinfo.= ",cartage='".$precDocInfo['cartage']."',cartage-vatid='".$precDocInfo['cartage_vatid']."'";
 $cdinfo.= ",packing-charges='".$precDocInfo['packing_charges']."',packing-charges-vatid='".$precDocInfo['packing_charges_vatid']."'";
 $cdinfo.= ",agentid='".$precDocInfo['agent_id']."'";
 $cdinfo.= ",docrefap='".$precDocInfo['docref_ap']."',docrefid='".$precDocInfo['docref_id']."'"; 

 if($cdinfo) $_EXTSET.= ",cdinfo.".ltrim($cdinfo, ",");
 if($_EXTSET) $_CMD.= " -extset `".ltrim($_EXTSET, ",")."`";


 // GENERATE DOC
 $ret = GShell($_CMD, $sessid, $shellid);
 if($ret['error']) return array('message'=>"Generate from precompiled document failed! Unable to generate the document:\n".$ret['message'], 'error'=>$ret['error']);
 $destDocInfo = $ret['outarr'];


 /* INSERT ELEMENTS */
 for($i=0; $i < count($precDocInfo['elements']); $i++)
 {
  $el = $precDocInfo['elements'][$i];

  if($subjectInfo && $el['ref_ap'] && $el['ref_id'])
  {
   // aggiornamento prezzi
   $el = commercialdocs_getElementPriceBySubject($el, $subjectInfo, $sessid, $shellid);
  }

  $ret = GShell("dynarc edit-item -ap `commercialdocs` -id `".$destDocInfo['id']."` -extset `cdelements.type='".$el['type']."',refap='".$el['ref_ap']."',refid='"
	.$el['ref_id']."',code='".$el['code']."',vencode='".$el['vencode']."',sn='".$el['serialnumber']."',lot='".$el['lot']."',accountid='"
	.$el['account_id']."',name='''".$el['name']."''',desc='''".$el['desc']."''',qty='".$el['qty']."',extraqty='".$el['extraqty']."',price='"
	.$el['price']."',discount='".$el['discount']."',discount2='".$el['discount2']."',discount3='".$el['discount3']."',vatrate='"
	.$el['vatrate']."',vatid='".$el['vatid']."',vattype='".$el['vattype']."',units='".$el['units']."',pricelistid='"
	.$el['pricelist_id']."',brandid='".$el['brand_id']."',vendorid='".$el['vendor_id']."',vendorprice='"
	.$el['vendor_price']."',coltint='''".$el['variant_coltint']."''',sizmis='''".$el['variant_sizmis']."''',ritaccapply='"
	.$el['ritaccapply']."',ccpapply='".$el['ccpapply']."',xmldata='''".$el['xmldata']."''',bypass-vatregister-update=true`");
  if($ret['error']) return $ret;
 }

 $out.= "The document has been created! ID=".$destDocInfo['id'];

 /* UPDATING TOTALS */
 $out.= "\nUpdating totals...";
 $ret = GShell("commercialdocs updatetotals -id '".$destDocInfo['id']."' --fix-mmr",$sessid,$shellid);
 if($ret['error'])
  $out.= "failed\n".$ret['message'];
 else
 {
  $out.= "\ndone!\n".$ret['message'];
  $outArr = $ret['outarr'];
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_getElementPriceBySubject($element, $subjectInfo, $sessid, $shellid)
{
 $el = $element;
 $pricelistID = $subjectInfo['pricelist_id'];
 $_AP = $el['ref_ap'];
 $_ID = $el['ref_id'];
 $_CUSTOM_PRICING = null;

 $_VAT = $el['vat'];
 $plBasePrice = 0;
 $plMRate = 0;
 $plDiscount = 0;

 // verifico prima i prezzi imposti
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT baseprice,discount_perc,discount_inc,discount2,discount3 FROM dynarc_".$_AP."_custompricing WHERE item_id='"
	.$_ID."' AND subject_id='".$subjectInfo['id']."' LIMIT 1");
 if($db->Read())
  $_CUSTOM_PRICING = $db->record;
 $db->Close();

 $db = new AlpaDatabase();
 if(!$_CUSTOM_PRICING)
 {
  if($pricelistID)
  {
   // ricavo i prezzi del listino selezionato
   $db->RunQuery("SELECT pricelist_".$pricelistID."_baseprice,pricelist_".$pricelistID."_mrate,pricelist_"
	.$pricelistID."_vat,pricelist_".$pricelistID."_discount FROM dynarc_".$_AP."_items WHERE id='".$_ID."'");
   if($db->Read())
   {
    $plBasePrice = $db->record["pricelist_".$pricelistID."_baseprice"];
    $plMRate = $db->record["pricelist_".$pricelistID."_mrate"];
    $_VAT = $db->record["pricelist_".$pricelistID."_vat"];
    $plDiscount = $db->record["pricelist_".$pricelistID."_discount"];
   }
  }
 }
 else
 {
  // ricavo solo l'aliquota IVA del listino selezionato
  $db->RunQuery("SELECT pricelist_".$pricelistID."_vat FROM dynarc_".$_AP."_items WHERE id='".$_ID."'");
  if($db->Read())
   $_VAT = $db->record["pricelist_".$pricelistID."_vat"];
 }
 $db->Close();

 $variantType = $el['variant_coltint'] ? "color" : ($el['variant_sizmis'] ? "size" : "");
 $variantName = $el['variant_coltint'] ? $el['variant_coltint'] : $el['variant_sizmis'];

 if($variantType && ($variantType != ""))
 {
  // Prelevo la lista di tutti i prezzi le cui qtà stiano nel range specificato
  $list = array();
  $db = new AlpaDatabase();
  $query = "SELECT * FROM dynarc_".$_AP."_pricesbyqty WHERE item_id='".$_ID."' AND qty_from<='".$el['qty']."' AND (qty_to=0 OR qty_to>='".$el['qty']."')";
  $db->RunQuery($query);
  if(!$db->Error)
  {
   while($db->Read())
   {
    $list[] = array('id'=>$db->record['id'], 'colors'=>$db->record['colors'], 'sizes'=>$db->record['sizes'], 'disc_perc'=>$db->record['disc_perc'],
	'finalprice'=>$db->record['final_price']);
   }
  }
  $db->Close();

  // dopodichè prelevo solamente quello con la variante specifica
  if(count($list))
  {
   $ok = false;
   for($c=0; $c < count($list); $c++)
   {
    if($ok) break;
    switch($variantType)
    {
     case 'color' : case 'tint' : {
	  $colors = explode("],[", substr($list[$c]['colors'], 1, -1));
	  if(in_array($variantName, $colors))
	  {
	   if($list[$c]['disc_perc'])
	    $finalPrice = $finalPrice - (($finalPrice/100)*$list[$c]['disc_perc']);
	   else if($list[$c]['finalprice'])
	    $finalPrice = $list[$c]['finalprice'];
	   $ok = true;
	  }
	 } break;

     case 'size' : case 'dim' : case 'other' : {
	  $sizes = explode("],[", substr($list[$c]['sizes'], 1, -1));
	  if(in_array($variantName, $sizes))
	  {
	   if($list[$c]['disc_perc'])
	    $finalPrice = $finalPrice - (($finalPrice/100)*$list[$c]['disc_perc']);
	   else if($list[$c]['finalprice'])
	    $finalPrice = $list[$c]['finalprice'];
	   $ok = true;
	  }
	 } break;

     default : {

	 } break;
    }
   }
  }
 }

 /* CALCULATE PRICING */
 $markupRate = 0;

 if($_CUSTOM_PRICING)
 {
  $basePrice = $_CUSTOM_PRICING['baseprice'];
  $finalPrice = $basePrice ? $basePrice : 0;
 }
 else if($pricelistID)
 {
  $basePrice = $plBasePrice;
  $markupRate = $plMRate;
  $discount = $plDiscount;
  $finalPrice = $basePrice ? $basePrice + (($basePrice/100)*$markupRate) : 0;
  $finalPrice = $finalPrice ? $finalPrice - (($finalPrice/100)*$discount) : 0;
 }
 else
 {
  $basePrice = $el['baseprice'];
  $finalPrice = $basePrice;
 }


 /* FINAL */
 $el['price'] = $finalPrice;
 $el['vatrate'] = $_VAT;

 if($_VAT != $element['vatrate'])
 {
  // get vat type and vat id
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_vatrates_items WHERE percentage='".$_VAT."' AND vat_type='TAXABLE' AND trash='0' LIMIT 1");
  if($db->Read())
  {
   $el['vatid'] = $db->record['id'];
   $el['vattype'] = "TAXABLE";
  }
  else
  {
   $db->RunQuery("SELECT vat_type,id FROM dynarc_vatrates_items WHERE percentage='".$_VAT."' AND trash='0' LIMIT 1");
   if($db->Read())
   {
    $el['vatid'] = $db->record['id'];
    $el['vattype'] = $db->record['vat_type'];
   }
  }
  $db->Close();
 }

 return $el;
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_updatePredocTotals($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_COMPANY_PROFILE;
 include_once($_BASE_PATH."include/company-profile.php");

 $out = "";
 $outArr = array();

 $_AP = "commercialdocsprec";
 $_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'] ? $_COMPANY_PROFILE['accounting']['decimals_pricing'] : 2;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
   case '--fix-mmr' : $fixMMR=true; break;
  }

 if(!$_ID)
  return array("message"=>"You must specify the document ID","error"=>"INVALID_DOCUMENT");

 /* GET DOCUMENT INFO */
 $ret = GShell("dynarc item-info -ap `".$_AP."` -id '".$_ID."' -extget `cdprecinfo`",$sessid,$shellid);
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

 switch(strtoupper($_CAT_TAG))
 {
  case 'INVOICES' : case 'PURCHASEINVOICES' : case 'AGENTINVOICES' : case 'MEMBERINVOICES' : case 'CREDITSNOTE' : case 'DEBITSNOTE' : case 'PAYMENTNOTICE' : case 'PREEMPTIVES' : case 'ORDERS' : case 'INTERVREPORTS' : case 'RECEIPTS' : $_SHOW_CONTRIBANDDEDUCTS = true; break;
  default : $_SHOW_CONTRIBANDDEDUCTS = false; break;
 }


 $_CPA = $_COMPANY_PROFILE['accounting'];

 $_CPA['rivalsa_inps'] = $docInfo['rivalsa_inps'];
 $_CPA['contr_cassa_prev'] = $docInfo['contr_cassa_prev'];
 $_CPA['contr_cassa_prev_vatid'] = $docInfo['contr_cassa_prev_vatid'];
 $_CPA['rit_enasarco'] = $docInfo['rit_enasarco'];
 $_CPA['rit_enasarco_percimp'] = $docInfo['rit_enasarco_percimp'];
 $_CPA['rit_acconto'] = $docInfo['rit_acconto'];
 $_CPA['rit_acconto_percimp'] = $docInfo['rit_acconto_percimp'];
 $_CPA['rit_acconto_rivinpsinc'] = $docInfo['rit_acconto_rivinpsinc'];


 /*----------------------------------------------------------------------------*/
 $_RIVALSA_INPS = $_CPA['rivalsa_inps'] ? $_CPA['rivalsa_inps'] : 0;
 $_RIT_ENASARCO = $_CPA['rit_enasarco'] ? $_CPA['rit_enasarco'] : 0;
 $_RIT_ENASARCO_PERCIMP = $_CPA['rit_enasarco_percimp'] ? $_CPA['rit_enasarco_percimp'] : 0;

 $_CASSA_PREV = $_CPA['contr_cassa_prev'] ? $_CPA['contr_cassa_prev'] : 0;
 $_CASSA_PREV_VATID = $_CPA['contr_cassa_prev_vatid'] ? $_CPA['contr_cassa_prev_vatid'] : 0;
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

 $_RIT_ACCONTO = $_CPA['rit_acconto'] ? $_CPA['rit_acconto'] : 0;
 $_RIT_ACCONTO_PERCIMP = $_CPA['rit_acconto_percimp'] ? $_CPA['rit_acconto_percimp'] : 0;
 $_RIT_ACCONTO_RIVINPSINC = $_CPA['rit_acconto_rivinpsinc'] ? $_CPA['rit_acconto_rivinpsinc'] : 0;
 /*----------------------------------------------------------------------------*/
 $_VATS = array();
 $agentCommiss = 0;
 $agentInfo = null;

 if($docInfo['agent_id'])
 {
  /* GET AGENT INFO */
  $ret = GShell("dynarc item-info -ap rubrica -id '".$docInfo['agent_id']."' -extget `catcommiss`",$sessid,$shellid);
  if(!$ret['error'])
   $agentInfo = $ret['outarr'];
  else
   $out.= "Warning: Unable to get agent info.\n";
 }

 $_TOT_IMP_RITACC = 0;
 $_TOT_IMP_CCP = 0;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT elem_type,ref_ap,ref_id,qty,extra_qty,price,price_adjust,discount_perc,discount_inc,discount2,discount3,vat_rate,vat_id,vat_type,rit_acc_apply,ccp_apply FROM dynarc_".$_AP."_elements WHERE item_id='".$docInfo['id']."'");
 while($db->Read())
 {
  if(($db->record['elem_type'] == "note") || ($db->record['elem_type'] == "message"))
   continue;
  $qty = $db->record['qty'];
  $extraQty = $db->record['extra_qty'];
  if($extraQty)
   $qty = $qty*$extraQty;
  
  $amount = $db->record['price'];
  if(!$qty || !$amount)
   continue;

  /* CALCOLO DELLE PROVVIGIONI ALL'AGENTE */
  if($agentInfo)
  {
   switch($_CAT_TAG)
   {
    case 'INVOICES' : case 'ORDERS' : case 'DDT' : case 'RECEIPTS' : {
	 $agentCommiss+= commercialdocs_getAgentCommiss($agentInfo, $db->record['ref_ap'], $db->record['ref_id'], $amount);
	} break;
   }
  }
  /* EOF - CALCOLO PROVV. AGENTE */
  
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

  if($_SHOW_CONTRIBANDDEDUCTS && $_RIT_ACCONTO)
  {
   //if(($db->record['elem_type'] == "service") || $db->record['rit_acc_apply'])
   if($db->record['rit_acc_apply'])
    $_TOT_IMP_RITACC+= $amount;
  }
  if($_SHOW_CONTRIBANDDEDUCTS && $_CASSA_PREV)
  {
   //if(($db->record['elem_type'] == "service") || $db->record['ccp_apply'])
   if($db->record['ccp_apply'])
    $_TOT_IMP_CCP+= $amount;
  }

  if($db->record['vat_id'])
  {
   if($_VATS[$db->record['vat_id']])
	$_VATS[$db->record['vat_id']]['amount']+= round($amount, 5);
   else
	$_VATS[$db->record['vat_id']] = array("type"=>$db->record['vat_type'], "rate"=>$db->record['vat_rate'], "amount"=>round($amount, 5), "expenses"=>0);
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
 $_COLLECTION_CHARGES = $docInfo['collection_charges'];
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
 // calcolo spese trasporto e imballaggio
 if($docInfo['cartage'])
 {
  if($_VATS[$docInfo["cartage_vatid"]])
   $_VATS[$docInfo["cartage_vatid"]]['expenses']+= $docInfo["cartage"];
  else
  {
   // detect vat-type and vat-rate //
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT vat_type,percentage FROM dynarc_vatrates_items WHERE id='".$docInfo["cartage_vatid"]."'");
   $db->Read();
   $_VATS[$docInfo["cartage_vatid"]] = array("type"=>$db->record['vat_type'], "rate"=>$db->record['percentage'], "amount"=>0, "expenses"=>$docInfo["cartage"]);
   $db->Close();
  }
 }

 if($docInfo['packing_charges'])
 {
  if($_VATS[$docInfo["packing_charges_vatid"]])
   $_VATS[$docInfo["packing_charges_vatid"]]['expenses']+= $docInfo["packing_charges"];
  else
  {
   // detect vat-type and vat-rate //
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT vat_type,percentage FROM dynarc_vatrates_items WHERE id='".$docInfo["cartage_vatid"]."'");
   $db->Read();
   $_VATS[$docInfo["packing_charges_vatid"]] = array("type"=>$db->record['vat_type'], "rate"=>$db->record['percentage'], "amount"=>0, "expenses"=>$docInfo["packing_charges"]);
   $db->Close();
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
  if($_SHOW_CONTRIBANDDEDUCTS && $_RIVALSA_INPS)
  {
   $rivinps = $value ? ($value/100)*$_RIVALSA_INPS : 0;
   $_TOTALE_RIVALSA_INPS+= $rivinps;
   $value+= $rivinps;
  }

  // arrotondo alla cifra decimale //
  $value = round($value, 5);

  $_TOTALE_IMPONIBILE+= $value;

  $_VATS[$vid]['discounted'] = $value;

  // calcolo l'IVA
  $vat = $value ? round(($value/100)*$vatInfo['rate'], 5) : 0;
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

 $_MERCE_SCONTATA = round($_MERCE_SCONTATA, 5);

 // calcola la cassa prev.
 if($_SHOW_CONTRIBANDDEDUCTS && $_CASSA_PREV)
 {
  //$imp = $_MERCE_SCONTATA - $_UNCONDITIONAL_DISCOUNT;
  $imp = $_TOT_IMP_CCP;
  $_TOTALE_CASSA_PREV = $imp ? round(($imp/100)*$_CASSA_PREV, $_DECIMALS) : 0;
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
   $vat = $value ? round(($value/100)*$vatInfo['rate'], $_DECIMALS) : 0;
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
	$vat = $_TOTALE_CASSA_PREV ? round(($_TOTALE_CASSA_PREV/100)*$_CASSA_PREV, $_DECIMALS) : 0;
	$_TOTALE_IVA+= $vat;
    $vatInfo['vat'] = $vat;
   }
   $_VATS[$_CASSA_PREV_VATID] = $vatInfo;
  }
  $_TOTALE_IMPONIBILE+= $_TOTALE_CASSA_PREV;
 }
 /*----------------------------------------------------------------------------*/
 // calcolo la ritenuta d'acconto
 if($_SHOW_CONTRIBANDDEDUCTS && $_RIT_ACCONTO)
 {
  //$imp = $_MERCE_SCONTATA - $_UNCONDITIONAL_DISCOUNT;
  $imp = $_TOT_IMP_RITACC;
  if($_RIT_ACCONTO_RIVINPSINC)
   $imp = $imp ? round((($imp+$_TOTALE_RIVALSA_INPS)/100)*$_RIT_ACCONTO_PERCIMP, $_DECIMALS) : 0;
  else
   $imp = $imp ? round(($imp/100)*$_RIT_ACCONTO_PERCIMP,$_DECIMALS) : 0;
  $_TOTALE_RITENUTA_ACCONTO = $imp ? round(($imp/100)*$_RIT_ACCONTO, $_DECIMALS) : 0;
 }

 // calcolo l'enasarco
 if($_SHOW_CONTRIBANDDEDUCTS && $_RIT_ENASARCO)
 {
  $imp = $_MERCE_SCONTATA - $_UNCONDITIONAL_DISCOUNT;
  $imp = $imp ? round(($imp/100)*$_RIT_ENASARCO_PERCIMP, $_DECIMALS) : 0;
  $_TOTALE_ENASARCO = $imp ? round(($imp/100)*$_RIT_ENASARCO, $_DECIMALS) : 0;
 }
 
 /* CALCOLO IL NETTO A PAGARE */
 $_NET_PAY = ($_TOTALE_IMPONIBILE+$_TOTALE_IVA) - $_TOTALE_RITENUTA_ACCONTO - $_TOTALE_ENASARCO - $_REBATE + $_STAMP + $_COLLECTION_CHARGES;
 
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
 $docInfo['tot_netpay'] = $_NET_PAY;
 /*----------------------------------------------------------------------------*/
 /* Get tot paid and rest to pay */
 $totPaid = 0;
 $restToPay = $itemInfo['tot_netpay'];

 $docInfo['tot_paid'] = $totPaid;
 $docInfo['rest_to_pay'] = $restToPay;
 /*----------------------------------------------------------------------------*/
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE dynarc_".$_AP."_items SET amount='".$_TOTALE_IMPONIBILE."',vat='".$_TOTALE_IVA."',total='"
	.($_TOTALE_IMPONIBILE + $_TOTALE_IVA)."',tot_rit_acc='".$_TOTALE_RITENUTA_ACCONTO."',tot_ccp='".$_TOTALE_CASSA_PREV."',tot_rinps='"
	.$_TOTALE_RIVALSA_INPS."',tot_enasarco='".$_TOTALE_ENASARCO."',tot_netpay='".$_NET_PAY."',tot_goods='".$_TOTALE_MERCE."',discounted_goods='"
	.$_MERCE_SCONTATA."',tot_expenses='".$_TOTALE_SPESE."',tot_discount='".$_TOTALE_SCONTO."',agent_commiss='".$agentCommiss."',tot_paid='"
	.$docInfo['tot_paid']."',rest_to_pay='".$docInfo['rest_to_pay']."'".$vatsQry." WHERE id='".$docInfo['id']."'");
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
  $out.= "Spese incasso: ".number_format($_COLLECTION_CHARGES,2,",",".")." &euro;\n";
  $out.= "Netto a pagare: ".number_format($_NET_PAY,2,",",".")." &euro;\n";

  if($agentInfo)
  {
   $out.= "Agent: ".$agentInfo['name']."\n";
   $out.= "Commissions: ".number_format($agentCommiss,2,",",".")." &euro;\n";
  }
 }
 else
  $out = "done!\n";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_duplicatePredoc($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : case '-title' : {$title=$args[$c+1]; $c++;} break;
   /* options */
   case '-destcat' : {$destCatId=$args[$c+1]; $c++;} break;
   case '-destct' : {$destCatTag=$args[$c+1]; $c++;} break;
  }

 if(!$id)
  return array('message'=>"Duplicate failed! You must specify the ID of the document to be duplicate. (with -id DOCUMENT_ID)",'error'=>"INVALID_DOCUMENT");


 /* GET SOURCE DOC INFO */
 $ret = GShell("dynarc item-info -ap commercialdocsprec -id `".$id."` -extget `cdprecinfo,cdprecelements`",$sessid, $shellid);
 if($ret['error'])
  return array('message'=>"Duplicate failed! ".$ret['message'], $ret['error']);
 $docInfo = $ret['outarr'];
 $docType = "";

 // get doc type //
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT tag,parent_id FROM dynarc_commercialdocsprec_categories WHERE id='".$docInfo['cat_id']."'");
 if($db->Read())
 {
  if($db->record['parent_id'])
  {
   $db->RunQuery("SELECT tag FROM dynarc_commercialdocsprec_categories WHERE id='".$db->record['parent_id']."'");
   $db->Read();
   $docType = $db->record['tag']; 
  }
  else
   $docType = $db->record['tag'];
 }
 $db->Close();

 if(!$destCatId && !$destCatTag)
  $destCatId = $docInfo['cat_id'];


 $query = "dynarc new-item -ap `commercialdocsprec` ".($destCatId ? "-cat '".$destCatId."'" : "-ct `".strtoupper($destCatTag)."`")
	." -name `".($title ? $title : $docInfo['name'])."` -alias `".$docInfo['alias_name']."` -group commdocs-".strtolower($docType)." -extset `cdprecinfo.subjectid='".$docInfo['subject_id']."'";
 $query.= ",subject=\"".$docInfo['subject_name']."\",tag='".$docInfo['tag']."'";
 if($docInfo['reference_id'])
  $query.= ",referenceid='".$docInfo['reference_id']."'";
 if($docInfo['agent_id'])
  $query.= ",agentid='".$docInfo['agent_id']."'";
 if($docInfo['agent_commiss'])
  $query.= ",agentcommiss='".$docInfo['agent_commiss']."'";
 if($docInfo['paymentmode'])
  $query.= ",paymentmode='".$docInfo['paymentmode']."'";
 if($docInfo['banksupport_id'])
  $query.= ",banksupport='".$docInfo['banksupport_id']."'";
 if($docInfo['pricelist_id'])
  $query.= ",pricelist='".$docInfo['pricelist_id']."'";
 if($docInfo['division'])
  $query.= ",division='".$docInfo['division']."'";
 if($docInfo['location'])
  $query.= ",location=\"".$docInfo['location']."\"";

 /* Shipping */
 $query.= ",ship-subject-id='".$docInfo['ship_subject_id']."'";
 $query.= ",ship-contact-id='".$docInfo['ship_contact_id']."'";
 $query.= ",ship-recp=\"".$docInfo['ship_recp']."\"";
 $query.= ",ship-addr=\"".$docInfo['ship_addr']."\"";
 $query.= ",ship-city=\"".$docInfo['ship_city']."\"";
 $query.= ",ship-zip='".$docInfo['ship_zip']."'";
 $query.= ",ship-prov='".$docInfo['ship_prov']."'";
 $query.= ",ship-cc='".$docInfo['ship_cc']."'";

 $query.= "`";

 $ret = GShell($query,$sessid,$shellid);

 if($ret['error'])
  return $ret;

 $docId = $ret['outarr']['id'];
 $outArr = $ret['outarr'];

 // UPDATE TRANSPORT AND OTHER INFORMATIONS
 $query = "dynarc edit-item -ap commercialdocsprec -id '".$docId."' -extset `cdprecinfo.";
 /* Transport */
 $query.= "trans-method='".$docInfo['trans_method']."'";
 $query.= ",trans-shipper=\"".$docInfo['trans_shipper']."\"";
 $query.= ",trans-numplate='".$docInfo['trans_numplate']."'";
 $query.= ",trans-causal=\"".$docInfo['trans_causal']."\"";
 $query.= ",trans-aspect=\"".$docInfo['trans_aspect']."\"";
 $query.= ",trans-num='".$docInfo['trans_num']."'";
 $query.= ",trans-weight='".$docInfo['trans_weight']."'";
 $query.= ",trans-freight='".$docInfo['trans_freight']."'";
 $query.= ",cartage='".$docInfo['cartage']."'";
 $query.= ",packing-charges='".$docInfo['packing_charges']."'";
 $query.= ",collection-charges='".$docInfo['collection_charges']."'";

 /* Expenses */
 if($docInfo['exp1name'])
  $query.= ",exp1-name=\"".$docInfo['exp1name']."\",exp1-vatid='".$docInfo['exp1vatid']."',exp1-amount='".$docInfo['exp1amount']."'";
 if($docInfo['exp2name'])
  $query.= ",exp2-name=\"".$docInfo['exp2name']."\",exp2-vatid='".$docInfo['exp2vatid']."',exp2-amount='".$docInfo['exp2amount']."'";
 if($docInfo['exp3name'])
  $query.= ",exp3-name=\"".$docInfo['exp3name']."\",exp3-vatid='".$docInfo['exp3vatid']."',exp3-amount='".$docInfo['exp3amount']."'";

 /* Discounts, rebate and stamp */
 if($docInfo['discount'])
  $query.= ",discount='".$docInfo['discount']."'";
 if($docInfo['discount2'])
  $query.= ",discount2='".$docInfo['discount2']."'";
 if($docInfo['uncondisc'])
  $query.= ",uncondisc='".$docInfo['uncondisc']."'";
 if($docInfo['rebate'])
  $query.= ",rebate='".$docInfo['rebate']."'";
 if($docInfo['stamp'])
  $query.= ",stamp='".$docInfo['stamp']."'";

 $query.= "`";
 $ret = GShell($query,$sessid,$shellid);
 if($ret['error'])
  return $ret;


 /* INSERT ELEMENTS */
 for($i=0; $i < count($docInfo['elements']); $i++)
 {
  $el = $docInfo['elements'][$i];
  $ret = GShell("dynarc edit-item -ap `commercialdocsprec` -id `".$docId."` -extset `cdprecelements.type='".$el['type']."',refap='".$el['ref_ap']."',refid='"
	.$el['ref_id']."',code='".$el['code']."',vencode='".$el['vencode']."',sn='".$el['serialnumber']."',lot='".$el['lot']."',accountid='"
	.$el['account_id']."',name='''".$el['name']."''',desc='''".$el['desc']."''',qty='".$el['qty']."',extraqty='".$el['extraqty']."',price='"
	.$el['price']."',discount='".$el['discount']."',discount2='".$el['discount2']."',discount3='".$el['discount3']."',vatrate='"
	.$el['vatrate']."',vatid='".$el['vatid']."',vattype='".$el['vattype']."',units='".$el['units']."',pricelistid='"
	.$el['pricelist_id']."',brandid='".$el['brand_id']."',vendorid='".$el['vendor_id']."',vendorprice='"
	.$el['vendor_price']."',coltint='''".$el['variant_coltint']."''',sizmis='''".$el['variant_sizmis']."''',ritaccapply='"
	.$el['ritaccapply']."',ccpapply='".$el['ccpapply']."',xmldata='''".$el['xmldata']."'''`");
  if($ret['error']) return $ret;
 }
 
 $out.= "The document has been duplicated! ID=".$docId;

 /* UPDATING TOTALS */
 $out.= "\nUpdating totals...";
 $ret = GShell("commercialdocs update-predoc-totals -id '".$docId."'",$sessid,$shellid);
 if($ret['error'])
  $out.= "failed\n".$ret['message'];
 else
 {
  $out.= "\ndone!\n".$ret['message'];
  $outArr = $ret['outarr'];
 }


 /* EOF - DUPLICATE FROM COMMERCIALDOCS PRE-COMPILED DOCUMENTS */

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_getLastDocument($args, $sessid, $shellid)
{
 $_AP = "commercialdocs";
 $ctime = time();

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
  }

 if($catTag)
 {
  $ret = GShell("dynarc cat-info -ap '".$_AP."' -tag '".$catTag."'", $sessid, $shellid);
  if($ret['error']) return $ret;
  $catId = $ret['outarr']['id'];
 }

 $prefix = "";
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT tag,parent_id FROM dynarc_".$_AP."_categories WHERE id='".$catId."'");
 if($db->Read())
 {
  if($db->record['parent_id'])
  {
   $prefix = $db->record['tag'];
   $db->RunQuery("SELECT tag FROM dynarc_".$_AP."_categories WHERE id='".$db->record['parent_id']."'");
   $db->Read();
   $catTag = $db->record['tag']; 
  }
  else
   $catTag = $db->record['tag'];
 }
 $db->Close();

 /* Get last document and adjust document number */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id,code_num,name FROM dynarc_".$_AP."_items WHERE cat_id='".$catId."' AND trash=0 AND ctime>='"
	.date('Y',$ctime)."-01-01' AND ctime<'".date('Y',strtotime("+1 Year",$ctime))."-01-01' ORDER BY code_num DESC LIMIT 1");
 if($db->Read())
 {
  $out.= "Last document is: #".$db->record['id']." - ".$db->record['name']."\n";
  $out.= "Last code num = ".$db->record['code_num']."\n";
  $out.= "Next code num = ".($db->record['code_num']+1)."\n";
  $outArr['last_code_num'] = $db->record['code_num'];
  $outArr['next_code_num'] = ($db->record['code_num']+1); 
 }
 else
 {
  $out.= "Next code num = 1\n";
  $outArr['last_code_num'] = 0;
  $outArr['next_code_num'] = 1; 
 }
 $db->Close();

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_exportElements($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES, $_EXTRA_COLUMNS;
 require_once($_BASE_PATH."var/lib/excel.php");

 $_AP = "commercialdocs";
 $_VAT_BY_ID = array();
 $_PRICELIST_BY_ID = array();
 $out = "";
 $outArr = array();

 $sheetName = "untitled";
 $letters = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
	"AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ");

 $fileName = "";
 $_FILE_PATH = "";
 $options = array();

 $_IDS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$_IDS[]=$args[$c+1]; $c++;} break;
   case '-ids' : {$_IDS=explode(',',$args[$c+1]); $c++;} break; // elenco id separati da una virgola
   case '-f' : case '-file' : case '-filename' : {$fileName=$args[$c+1]; $c++;} break;
   case '-s' : case '-sheet' : {$sheetName=substr($args[$c+1],0,32); $c++;} break;
   case '-fields' : {$fields = $args[$c+1]; $c++;} break; // campi da esportare

   case '--exclude-notes' : $options['excludenotes']=true; break;
   case '--exclude-comments' : $options['excludecomments']=true; break;
  }

 $sessInfo = sessionInfo($sessid);

 // GET PRICELISTS
 $out.= "Get pricelists...";
 $ret = GShell("pricelists list", $sessid, $shellid);
 if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'], 'error'=>$ret['error']);
 $_PRICELISTS = $ret['outarr'];
 $_PRICELIST_BY_ID = array();
 for($c=0; $c < count($_PRICELISTS); $c++)
  $_PRICELIST_BY_ID[$_PRICELISTS[$c]['id']] = $_PRICELISTS[$c];
 $out.= "done!\n";

 // GET LIST OF VAT RATES
 $out.= "Get vat rates...";
 $ret = GShell("dynarc item-list -ap vatrates -get `percentage,vat_type`", $sessid, $shellid);
 if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'], 'error'=>$ret['error']);
 $_VAT_LIST = $ret['outarr']['items'];
 for($c=0; $c < count($_VAT_LIST); $c++)
  $_VAT_BY_ID[$_VAT_LIST[$c]['id']] = $_VAT_LIST[$c];
 $out.= "done!\n";

 // PREPARE FIELDS
 $_EXCEL_FIELDS = array(
	"code"=>array("title"=>"Codice"),
	"vencode"=>array("title"=>"Cod. art. forn."),
	"mancode"=>array("title"=>"Cod. art. produttore."),
	"sn"=>array("title"=>"S.N."),
	"lot"=>array("title"=>"Lotto"),
	"account"=>array("title"=>"Conto"),
	"brand"=>array("title"=>"Marca"),
	"description"=>array("title"=>"Articolo / Descrizione"),
	"metric"=>array("title"=>"Computo metrico"),
	"qty"=>array("title"=>"Qta", "format"=>"number"),
	"qty_sent"=>array("title"=>"Qta inv.", "format"=>"number"),
	"qty_downloaded"=>array("title"=>"Qta scaric.", "format"=>"number"),
	"units"=>array("title"=>"U.M."),
	"coltint"=>array("title"=>"Colore/Tinta"),
	"sizmis"=>array("title"=>"Taglia/Misura"),
	"plbaseprice"=>array("title"=>"Pr. base", "format"=>"currency"),
	"plmrate"=>array("title"=>"% ric.", "format"=>"percentage"),
	"pldiscperc"=>array("title"=>"% sconto", "format"=>"percentage"),
	"vendorprice"=>array("title"=>"Pr. Acq.", "format"=>"currency"),
	"unitprice"=>array("title"=>"Pr. Unit", "format"=>"currency"),
	"weight"=>array("title"=>"Peso unit.", "format"=>"number"),
	"discount"=>array("title"=>"Sconto", "format"=>"currency percentage"),
	"discount2"=>array("title"=>"Sconto2", "format"=>"percentage"),
	"discount3"=>array("title"=>"Sconto3", "format"=>"percentage"),
	"vat"=>array("title"=>"I.V.A.", "format"=>"percentage"),
	"vatcode"=>array("title"=>"Cod. IVA"),
	"vatname"=>array("title"=>"Descr. IVA"),
	"price"=>array("title"=>"Totale", "format"=>"currency"),
	"profit"=>array("title"=>"Guadagno", "format"=>"currency"),
	"margin"=>array("title"=>"% Margine", "format"=>"percentage"),
	"vatprice"=>array("title"=>"Tot. + IVA", "format"=>"currency"),
	"pricelist"=>array("title"=>"Listino"),
	"docref"=>array("title"=>"Doc. di rif."),
	"vendorname"=>array("title"=>"Fornitore")
	);

 if($fields)
 {
  $x = explode(",", $fields);
  $fields = array();
  if(count($x))
  {
   for($c=0; $c < count($x); $c++)
    $fields[$x[$c]] = $_EXCEL_FIELDS[$x[$c]];
   $_EXCEL_FIELDS = $fields;
  }
 }

 PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );
 $objPHPExcel = new PHPExcel();


 for($c=0; $c < count($_IDS); $c++)
 {
  $_ID = $_IDS[$c];
  $_ISVENDOR = false;
  $_ROOT_CAT_NAME = "";

  // GET DOCUMENT INFO
  $out.= "Get document info...";
  $ret = GShell("dynarc item-info -ap '".$_AP."' -id '".$_ID."' -extget `cdinfo,cdelements`",$sessid, $shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'], 'error'=>$ret['error']);
  $out.= "done!\n";
  $docInfo = $ret['outarr'];

  // DETECT DOC TYPE
  if($docInfo && $docInfo['cat_id'])
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT name,tag,parent_id FROM dynarc_commercialdocs_categories WHERE id='".$docInfo['cat_id']."'");
   if($db->Read())
   {
    if($db->record['parent_id'])
    {
     $_ROOT_CAT_ID = $db->record['parent_id'];
     $db->RunQuery("SELECT tag,name FROM dynarc_commercialdocs_categories WHERE id='".$db->record['parent_id']."'");
     $db->Read();
     $_CAT_TAG = $db->record['tag'];
	 $_ROOT_CAT_NAME = $db->record['name']; 
    }
    else
	{
	 $_ROOT_CAT_ID = $docInfo['cat_id'];
     $_CAT_TAG = $db->record['tag'];
	 $_ROOT_CAT_NAME = $db->record['name'];
	}
   }
   $db->Close();

   switch(strtolower($_CAT_TAG))
   {
    case 'ddtin' : case 'purchaseinvoices' : case 'vendororders' : $_ISVENDOR = true; break;
   }
  }

  if($c == 0)
  {
   $out.= "Generate Excel file...";

   if(!$fileName) $fileName = (count($_IDS) == 1) ? $docInfo['name'] : ($_ROOT_CAT_NAME ? $_ROOT_CAT_NAME : 'documents');
   $fileName = html_entity_decode(str_replace(array('/', '&deg;', '&lsquo;'),array('-','.','.'),$fileName), ENT_QUOTES, 'UTF-8');
   $pi = pathinfo($fileName);
   if(!$pi['extension'] || (strlen($pi['extension']) > 4)) 		$fileName.= ".xlsx";  
   if($sessInfo['uname'] == "root") $_FILE_PATH = "tmp/";
   else if($sessInfo['uid'])
   {
    $db = new AlpaDatabase();
    $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
    $db->Read();
    $_FILE_PATH = $_USERS_HOMES.$db->record['homedir']."/";
    $db->Close();
   }
   else $_FILE_PATH = "tmp/";
  }

  if($c > 0) $objPHPExcel->createSheet();
  $sheet = $objPHPExcel->setActiveSheetIndex($c);
  if(count($_IDS) > 1) $sheetName = $docInfo['code_num'].($docInfo['code_ext'] ? "/".$docInfo['code_ext'] : "")."-".date('Y',$docInfo['ctime']);
  if($sheetName) $objPHPExcel->getActiveSheet()->setTitle($sheetName);
  $rowIdx = 1;

  $ret = commercialdocs_exportSingleDocumentBody($docInfo, $sheet, $_EXCEL_FIELDS, $rowIdx, $_ISVENDOR, $_VAT_BY_ID, $_PRICELIST_BY_ID, $options);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'], 'error'=>$ret['error']);
  $out.= $ret['message'];
 }
 
 /* GENERATE EXCEL FILE */
 $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
 $objWriter->save($_BASE_PATH.$_FILE_PATH.ltrim($fileName,"/"));

 $out = "done!\nExcel file: ".$fileName;
 $outArr = array('filename'=>$fileName, "fullpath"=>$_FILE_PATH.ltrim($fileName,"/"));


 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_exportSingleDocumentBody($docInfo, $sheet, $_EXCEL_FIELDS, $rowIdx, $_ISVENDOR, $_VAT_BY_ID, $_PRICELIST_BY_ID, $options)
{
 $out.= "Export ".$docInfo['name']."...";

 $sheet->setCellValueByColumnAndRow(0, $rowIdx, html_entity_decode($docInfo['name']." - ".$docInfo['subject_name'], ENT_QUOTES, 'UTF-8'));
 $rowIdx++;

 reset($_EXCEL_FIELDS);
 $c = 0;
 while(list($k,$v) = each($_EXCEL_FIELDS))
 {
  $sheet->setCellValueByColumnAndRow($c, $rowIdx, $v['title']);
  $c++;
 }
 $rowIdx++;
 for($c=0; $c < count($docInfo['elements']); $c++)
 {
  $el = $docInfo['elements'][$c];
  if($options['excludenotes'] && (strtolower($el['type']) == "note"))
   continue;
  if($options['excludecomments'] && (strtolower($el['type']) == "message"))
   continue;

  commercialdocs_exportSingleElement($el, $sheet, $_EXCEL_FIELDS, $rowIdx, $_ISVENDOR, $_VAT_BY_ID, $_PRICELIST_BY_ID);
  $rowIdx++;
 }
 $out.= "done!\n";

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_exportSingleElement($data, $sheet, $_EXCEL_FIELDS, $rowIdx, $_ISVENDOR, $_VAT_BY_ID, $_PRICELIST_BY_ID)
{
 if(strtolower($data['type']) == "note")
 {
  $value = html_entity_decode(strip_tags($data['desc']), ENT_QUOTES, 'UTF-8');
  $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, $value);
 }
 else if(strtolower($data['type']) == "message")
 {
  $value = html_entity_decode(strip_tags($data['desc']), ENT_QUOTES, 'UTF-8');
  $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, $value);
 }
 else
 {
  $qty = $data['qty'] * ($data['extraqty'] ? $data['extraqty'] : 1);
  $discount = $data['discount_inc'] ? $data['discount_inc'] : 0;
  $discount2 = 0;
  $discount3 = 0;

  if($data['discount_perc'])    									$discount = $data['price'] ? ($data['price']/100)*$data['discount_perc'] : 0;
  if($data['discount2'] && $data['price'])							$discount2 = (($data['price']-$discount)/100) * $data['discount2'];
  if($data['discount2'] && $data['discount3'] && $data['price'])	$discount3 = ((($data['price']-$discount)-$discount2)/100) * $data['discount3'];

  $amount = round(((($data['price']-$discount)-$discount2)-$discount3) * $qty, 5);
  $total = $amount;

  $colIdx = 0;
  reset($_EXCEL_FIELDS);
  while(list($k,$field) = each($_EXCEL_FIELDS))
  {
   $value = "";
   $dataType = "";
   $formatCode = "";

   switch($k)
   {
    case 'code' : 				$value = $data['code']; break;
	case 'vencode' : 			$value = $data['vencode']; break;
	case 'mancode' :			$value = $data['mancode']; break;
	case 'sn' : 				$value = $data['serialnumber']; break;
	case 'lot' : 				$value = $data['lot']; break;
	case 'brand' : 				$value = $data['brand']; break;
	case 'description' : 		$value = $data['name']; break;
	case 'qty' : 				$value = $data['qty']; break;
	case 'qty_sent' : 			$value = $data['qty_sent']; break;
	case 'qty_downloaded' : 	$value = $data['qty_downloaded']; break;
	case 'units' : 				$value = $data['units']; break;
	case 'coltint' : 			$value = $data['variant_coltint']; break;
	case 'sizmis' : 			$value = $data['variant_sizmis']; break;
	case 'vendorprice' : 		$value = $data['vendor_price']; break;
	case 'unitprice' : 			$value = $_ISVENDOR ? $data['sale_price'] : $data['price']; break;
	case 'plbaseprice' : 		$value = $data['plbaseprice']; break;
	case 'plmrate' : 			$value = $data['plmrate']; break;
	case 'pldiscperc' : 		$value = $data['pldiscperc']; break;
	case 'discount' : {
		 if($data['discount_perc'] > 0)	$value = $data['discount_perc']."%";
		 else if($data['discount_inc'] > 0)	$value = $data['discount_inc'];
		 else $value = 0;
		} break;
	case 'discount2' : 			$value = $data['discount2']; break;
	case 'discount3' : 			$value = $data['discount3']; break;
	case 'vat' : 				$value = $data['vatrate']; break;
	case 'vatcode' : 			$value = $data['vatid'] ? $_VAT_BY_ID[$data['vatid']]['code_str'] : ''; break;
	case 'vatname' : 			$value = $data['vatid'] ? $_VAT_BY_ID[$data['vatid']]['name'] : ''; break;
	case 'price' : 				$value = $amount; break;
	case 'profit' : 			$value = $data['profit']; break;
	case 'margin' : 			$value = $data['margin']; break;
	case 'vatprice' : 			$value = $amount+$data['vat']; break;
	case 'pricelist' : 			$value = $data['pricelist_id'] ? $_PRICELIST_BY_ID[$data['pricelist_id']]['name'] : ''; break;
	case 'weight' : 			$value = $data['weight']." ".$data['weightunits']; break;
	case 'docref' : 			{
		 if($data['doc_ref_ap'] && $data['doc_ref_id'])
		 {
		  $db = new AlpaDatabase();
		  $db->RunQuery("SELECT name FROM dynarc_".$data['doc_ref_ap']."_items WHERE id='".$data['doc_ref_id']."'");
		  if($db->Read())
		   $value = $db->record['name'];
		  $db->Close();
		 }
		} break;
	case 'vendorname' : 		$value = $data['vendor_name']; break;
   } // eof - switch

   switch($field['format'])
   {
	case 'datetime' : {
		 $dataType = PHPExcel_Cell_DataType::TYPE_STRING;
		 if($value && is_numeric($value))
		  $value = date($field['dateformat'] ? $field['dateformat'] : 'd/m/Y H:i', $value);
		 else if($value)
		  $value = date($field['dateformat'] ? $field['dateformat'] : 'd/m/Y H:i', strtotime($value));
		} break;

	case 'date' : {
		 $dataType = PHPExcel_Cell_DataType::TYPE_STRING;
		 if($value && is_numeric($value))
		  $value = date($field['dateformat'] ? $field['dateformat'] : 'd/m/Y', $value);
		 else if($value)
		  $value = date($field['dateformat'] ? $field['dateformat'] : 'd/m/Y', strtotime($value));
		} break;

	case 'time' : {
		 $dataType = PHPExcel_Cell_DataType::TYPE_STRING;
		 if($value && is_numeric($value))
		  $value = date($field['timeformat'] ? $field['timeformat'] : 'H:i', $value);
		 else if($value)
		  $value = date($field['timeformat'] ? $field['timeformat'] : 'H:i', strtotime($value));
		} break;

	case 'percentage' : {
		 if(!$value)
		  $value = "0%";
		 else if(is_numeric($value) || (strpos($value, "%") === false))
		  $value = $value."%";
		} break;

	case 'number' : {
		 $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		} break;

	case 'currency' : {
		 $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		 $formatCode = "€ #,##0.00";
		} break;

	case 'currency percentage' : {
		 if(is_numeric($value) || (strpos($value, "%") === false))
		 {
		  $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		  $formatCode = "€ #,##0.00";
		 }
		} break;

	default : {
		 $dataType = PHPExcel_Cell_DataType::TYPE_STRING;
		 $value = html_entity_decode($value,ENT_QUOTES,'UTF-8');
		} break;
   } // eof - switch

   if($dataType)
    $sheet->setCellValueExplicitByColumnAndRow($colIdx, $rowIdx, $value, $dataType);
   else
    $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, $value);
   if($formatCode)
    $sheet->getStyleByColumnAndRow($colIdx, $rowIdx)->getNumberFormat()->setFormatCode($formatCode);

   $colIdx++;
  } // eof - while

 } // eof - else

}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_print($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;
 $out = "";
 $outArr = array();

 $_IDS = array();
 $_CSS_CONTENT = "";
 $_PARSER = "";
 $_FORMAT = "";
 $_ORIENTATION = "";
 $_DIRNAME = "tmp/";
 $_ZIP_FILENAME = "";
 $_ALLINONE = false;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$_IDS[]=$args[$c+1]; $c++;} break;
   case '-ids' : {$_IDS = explode(",",$args[$c+1]); $c++;} break;

   case '-modelid' : {$modelId = $args[$c+1]; $c++;} break;
   case '-parser' : {$_PARSER = $args[$c+1]; $c++;} break;
   case '-format' : {$_FORMAT = $args[$c+1]; $c++;} break;
   case '-orientation' : {$_ORIENTATION = $args[$c+1]; $c++;} break;

   case '-dir' : case '-directory' : {$_DIRNAME = $args[$c+1]; $c++;} break; // Directory dove vengono salvati i PDF.
   case '-f' : case '-file' : {$_ZIP_FILENAME = $args[$c+1]; $c++;} break;	 // Nome file ZIP dove salvarci dentro tutti i PDF.
   case '--allinone' : case '--all-in-one' : $_ALLINONE=true; break; 
  }

 // VERIFY IF DIRECTORY EXISTS
 if($_DIRNAME)
 {
  if(substr($_DIRNAME, -1) != "/")
   $_DIRNAME.="/";
  $dir = _getUserHomedir(null, $sessid).$_DIRNAME;
  if(!file_exists($_BASE_PATH.$dir))
   return array('message'=>"commercialdocs print failed! Directory ".$dir." does not exists!", 'error'=>'DIRECTORY_DOES_NOT_EXISTS');
 }


 // GET MODEL ID
 $out.= "Get print-model info...";
 $ret = GShell("dynarc item-info -ap 'printmodels' -id '".$modelId."' -extget 'printmodelinfo,css'", $sessid, $shellid);
 if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'], 'error'=>$ret['error']);
 $out.= "done!\n";
 $printModelInfo = $ret['outarr'];

 // GET CSS, FORMAT AND ORIENTATION
 if(count($printModelInfo['css']))				$_CSS_CONTENT = "<style type='text/css'>".$printModelInfo['css'][0]['content']."</style>";
 if(!$_FORMAT)			$_FORMAT = $printModelInfo['format'];
 if(!$_ORIENTATION)		$_ORIENTATION = $printModelInfo['orientation'];

 $_FILE_NAMES = array();
 $_ALL_PAGES = array();
 for($i=0; $i < count($_IDS); $i++)
 {
  $_ID = $_IDS[$i];
  $_PAGES = array();
  $_FILENAME = "";

  // GET DOCUMENT INFO
  $out.= "Get document info #".$_ID."...";
  $ret = GShell("dynarc item-info -ap 'commercialdocs' -id '".$_ID."'", $sessid, $shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'], 'error'=>$ret['error']);
  $out.= "done!\n";
  $docInfo = $ret['outarr'];

  gshPreOutput($shellid, ($i+1)."/".count($_IDS)." - ".$docInfo['name'], "PROGRESS", "", "DEFAULT", array('id'=>$docInfo['id']));

  // DETECT FILE NAME
  $title = htmlentities($docInfo['name']);
  $title = str_replace("&Acirc;&deg;",".",$title);
  $title = str_replace(array("&Acirc;","&deg;"),".",$title);
  $title = str_replace("&amp;deg;",".",$title);
  $title = str_replace("&amp;lsquo;","",$title);

  $_FILENAME = $_DIRNAME.str_replace(array("/"," ","."), array("-","_","-"), $title).".pdf";


  // PARSERIZE FIRST PAGE
  if($printModelInfo['firstpage_content'])
  {
   $out.= "Parserize first page for document #".$_ID."...";
   $ret = GShell("parserize -p '".$_PARSER."' -params `ap=commercialdocs&id=".$_ID."` `".$printModelInfo['firstpage_content']."`", $sessid, $shellid);
   if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'], 'error'=>$ret['error']);
   $out.= "done!\n";
   $pgContent = str_replace("{ABSOLUTE_URL}",$_ABSOLUTE_URL,$ret['message']);
   $_PAGES[] = $pgContent;
   if($_ALLINONE) $_ALL_PAGES[] = $pgContent;
  }

  // PARSERIZE DOCUMENT
  $out.= "Parserize document #".$_ID."...";
  $ret = GShell("dynarc item-info -ap 'printmodels' -id '".$modelId."' || parserize -p '".$_PARSER."' -params `ap=commercialdocs&id="
	.$_ID."&parseelements=true` *.desc", $sessid, $shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'], 'error'=>$ret['error']);
  $out.= "done!\n";
  $pgContent = str_replace(array("{ABSOLUTE_URL}", urlencode("{ABSOLUTE_URL}")),$_ABSOLUTE_URL,$ret['message']);
  $_PAGES[] = $pgContent;
  if($_ALLINONE) $_ALL_PAGES[] = $pgContent;
  
  // PARSERIZE LAST PAGE
  if($printModelInfo['lastpage_content'])
  {
   $out.= "Parserize last page for document #".$_ID."...";
   $ret = GShell("parserize -p '".$_PARSER."' -params `ap=commercialdocs&id=".$_ID."` `".$printModelInfo['lastpage_content']."`", $sessid, $shellid);
   if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'], 'error'=>$ret['error']);
   $out.= "done!\n";
   $pgContent = str_replace("{ABSOLUTE_URL}",$_ABSOLUTE_URL,$ret['message']);
   $_PAGES[] = $pgContent;
   if($_ALLINONE) $_ALL_PAGES[] = $pgContent;
  }

  
  if(!$_ALLINONE)
  {  
   // GENERATE PDF
   $_CMD = "pdf export -format '".$_FORMAT."' -orientation '".$_ORIENTATION."' -o '".$_FILENAME."' --bypass-preoutput";
   for($c=0; $c < count($_PAGES); $c++)
    $_CMD.= " -c `".$_CSS_CONTENT.$_PAGES[$c]."`";

   $out.= "Generate PDF file for document #".$_ID."...";
   $ret = GShell($_CMD, $sessid, $shellid);
   if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'], 'error'=>$ret['error']);
   $out.= "done!\n";

   $_FILE_NAMES[] = $ret['outarr']['filename'];
   $outArr[] = $ret['outarr'];
  }
 }

 if($_ALLINONE)
 {
  // GENERATE PDF
  $_FILENAME = $_ZIP_FILENAME ? $_ZIP_FILENAME : "Documents";
  if(substr($_FILENAME, -4) != ".pdf")
   $_FILENAME.= ".pdf";

  $_CMD = "pdf export -format '".$_FORMAT."' -orientation '".$_ORIENTATION."' -o '".$_FILENAME."' --bypass-preoutput";
  for($c=0; $c < count($_ALL_PAGES); $c++)
   $_CMD.= " -c `".$_CSS_CONTENT.$_ALL_PAGES[$c]."`";

  $out.= "Generate PDF file ...";
  $ret = GShell($_CMD, $sessid, $shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'], 'error'=>$ret['error']);
  $out.= "done!\n";

  $outArr = $ret['outarr'];
 }
 else if($_ZIP_FILENAME)
 {
  if(substr($_ZIP_FILENAME, -4) != ".zip") $_ZIP_FILENAME.= ".zip";
  $_CMD = "zip -o '".$_ZIP_FILENAME."'";
  for($c=0; $c < count($_FILE_NAMES); $c++)
   $_CMD.= " -i '".$_FILE_NAMES[$c]."' -dest '".basename($_FILE_NAMES[$c])."'";

  $out.= "Compress all PDF file into zip...";
  $ret = GShell($_CMD, $sessid, $shellid);
  if($ret['error']) return array('message'=>$out."failed!\n".$ret['message'], 'error'=>$ret['error']);
  return $ret;
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_exportToXML($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $_IDS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$_IDS[] = $args[$c+1]; $c++;} break;
   case '-ids' : {$_IDS = explode(",",$args[$c+1]); $c++;} break;

   case '-f' : {$fileName=$args[$c+1]; $c++;} break;
  }

 if(!$fileName) return array('message'=>"CommercialDocs ExportToXML failed! Invalid filename.", 'error'=>'INVALID_FILENAME');
 if(!count($_IDS))	return array('message'=>"CommercialDocs ExportToXML failed! No documents to export.", 'error'=>'INVALID_ID');
 if(substr($fileName, -4) != ".xml")
  $fileName.= ".xml";

 $helper = new GDCImportExport($sessid, $shellid);

 $_XML = "<xml>";
 for($c=0; $c < count($_IDS); $c++)
 {
  $xml = $helper->exportToXML($_IDS[$c]);
  if(!$xml) return array('message'=>"CommercialDocs ExportToXML failed!\n".$helper->debug, 'error'=>$helper->error);
  $_XML.= $xml;
 }
 $_XML.= "</xml>";

 $ret = GShell("echo `".$_XML."` > `".$fileName."`", $sessid, $shellid);
 if($ret['error']) return array('message'=>"CommercialDocs ExportToXML failed!\n".$ret['message'], 'error'=>$ret['error']);

 $out.= "done!\nDocuments has been exported into file ".$fileName."\n";
 $outArr['filename'] = $fileName;

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function commercialdocs_importFromXML($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $options = array('type'=>'PREEMPTIVES');

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-f' : {$fileName=$args[$c+1]; $c++;} break;
   case '-type' : case '-ct' : {$options['type'] = $args[$c+1]; $c++;} break;
   case '--preview' : $options['previewonly']=true; break;
   case '--exclude-idx' : {$options['exclude_idx'] = explode(",",$args[$c+1]); $c++;} break; //first=1, second=2, ...
  }

 if(!$fileName) return array('message'=>"CommercialDocs ImportFromXML failed! Invalid filename.", 'error'=>'INVALID_FILENAME');

 $helper = new GDCImportExport($sessid, $shellid);
 $ret = $helper->ImportFromXMLFile($fileName, $options);
 if(!$ret) return array('message'=>$helper->debug, 'error'=>$helper->error);

 $out = $helper->debug;
 $outArr = $ret;

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
//--- CLASS - GCDIMPORTEXPORT ---------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
class GDCImportExport
{
 var $sessid, $shellid, $debug, $error;
 var $VAT_BY_ID;

 function GDCImportExport($sessid=0, $shellid=0)
 {
  $this->sessid = $sessid;
  $this->shellid = $shellid;
  $this->debug = "";
  $this->error = "";
  $this->VAT_BY_ID = null;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function exportToXML($id)
 {
  $doc = $this->loadDocument($id);
  if(!$doc) return false;

  // DETECT DOC TYPE
  $ret = GShell("dynarc getrootcat -ap 'commercialdocs' -id '".$doc['cat_id']."'", $sessid, $shellid);
  if($ret['error']) return $this->returnError("Unable to get document root cat.\n".$ret['message'], $ret['error']);
  $_DOC_TYPE = $ret['outarr']['tag'];

  $xml = "<document num='".xml_purify($doc['code_num'])."' date='".date('Y-m-d',$doc['ctime'])."' name='"
	.xml_purify($doc['name'])."' type='".$_DOC_TYPE."'>";
  // SUBJECT
  $xml.= "<subject name='".xml_purify($doc['subject_name'])."' code='".$doc['subject_code']."' taxcode='"
	.$doc['subject_taxcode']."' vatnumber='".$doc['subject_vatnumber']."'/>"; 

  // SHIPPING
  $xml.= "<shipping recp='".xml_purify($doc['ship_recp'])."' address='".xml_purify($doc['ship_addr'])."' city='"
	.xml_purify($doc['ship_city'])."' zipcode='".$doc['ship_zip']."' province='".strtoupper($doc['ship_prov'])."' countrycode='".$doc['ship_cc']."'/>";

  // TRANSPORT
  $xml.= "<transport method='".$doc['trans_method']."' shipper='".xml_purify($doc['trans_shipper'])."' numplate='"
	.$doc['trans_numplate']."' causal='".$doc['trans_causal']."' datetime='"
	.($doc['trans_datetime'] ? date('Y-m-d H:i',$doc['trans_datetime']) : "")."' aspect='"
	.xml_purify($doc['trans_aspect'])."' num='".$doc['trans_num']."' weight='".$doc['trans_weight']."' freight='"
	.$doc['trans_freight']."' cartage='".$doc['cartage']."' packingcharges='".$doc['packing_charges']."' trackingnumber='"
	.$doc['tracking_number']."'/>";

  // OTHER FEES
  $xml.= "<otherfees collectioncharges='".$doc['collection_charges']."' stamp='".$doc['stamp']."'/>";

  // WITHHOLDING TAXES
  $doc['contr_cassa_prev_vatrate'] = $this->getVatById($doc['contr_cassa_prev_vatid'], 'percentage');
  $xml.= "<withholdingtax cassaprev='".$doc['contr_cassa_prev']."' cassaprevvatrate='"
	.$doc['contr_cassa_prev_vatrate']."' enasarco='".$doc['rit_enasarco']."' enasarcopercimp='"
	.$doc['rit_enasarco_percimp']."' ritacconto='".$doc['rit_acconto']."' ritaccontopercimp='"
	.$doc['rit_acconto_percimp']."' ritaccontorivinpsinc='".$doc['rit_acconto_rivinpsinc']."'/>";

  // TOTALS
  $xml.= "<totals amount='".$doc['amount']."' vat='".$doc['vat']."' vatnd='".$doc['vatnd']."' total='"
	.$doc['total']."' ritacc='".$doc['tot_rit_acc']."' ccp='".$doc['tot_ccp']."' rivinps='"
	.$doc['tot_rinps']."' enasarco='".$doc['tot_enasarco']."' netpay='".$doc['tot_netpay']."' paid='"
	.$doc['tot_paid']."' resttopay='".$doc['rest_to_pay']."' discount='".$doc['tot_discount']."' goods='"
	.$doc['tot_goods']."' discountedgoods='".$doc['discounted_goods']."' expenses='".$doc['tot_expenses']."'/>";


  // ELEMENTS
  if(count($doc['elements']))
  {
   $xml.= "<elements>";

   for($c=0; $c < count($doc['elements']); $c++)
   {
    $el = $doc['elements'][$c];
	switch($el['type'])
	{
	 case 'note' : case 'message' : $xml.= "<item type='".$el['type']."' content='".xml_purify($el['desc'])."'/>"; break;
	
	 default : {
	    $xml.= "<item type='".$el['type']."' code='".xml_purify($el['code'])."' sku='".$el['sku']."' asin='"
		 .$el['asin']."' ean='".$el['ean']."' gcid='".$el['gcid']."' gtin='".$el['gtin']."' upc='"
		 .$el['upc']."' vencode='".$el['vencode']."' serialnumber='".$el['serialnumber']."' lot='"
		 .$el['lot']."' brand='".xml_purify($el['brand_name'])."' name='".xml_purify($el['name'])."' description='"
		 .xml_purify($el['desc'])."' qty='".$el['qty']."' units='".$el['units']."' coltint='"
		 .xml_purify($el['variant_coltint'])."' sizmis='".xml_purify($el['variant_sizmis'])."' price='"
		 .$el['price']."' vat='".$el['vatrate']."' vattype='".$el['vattype']."'/>";
		} break;
	}
   }

   $xml.= "</elements>";
  }

  $xml.= "</document>";

  return $xml;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function ImportFromXMLFile($fileName, $options=array())
 {
  global $_BASE_PATH, $_ABSOLUTE_URL;
  $debug = "";
  $outArr = array();

  $_HOME_DIR = _getUserHomedir(null,$this->sessid);
  if($_HOME_DIR === false)
   return $this->returnError("Unable to import documents from XML file '".$fileName."'. Permission denied!","PERMISSION_DENIED");

  $file = $_BASE_PATH.$_HOME_DIR.str_replace($_HOME_DIR,"",$fileName);
  $debug = "Import documents from XML file '".$_HOME_DIR.$fileName."' ...";
  if(!file_exists($file)) return $this->returnError($debug."failed!\nFile ".$_HOME_DIR.$fileName." does not exists.","FILE_DOES_NOT_EXISTS");

  // LOAD XML FILE
  $xml = new GXML();
  if(!$xml->LoadFromFile($file)) return $this->returnError($debug."failed!\nUnable to parse xml file.","XML_PARSE_FAILED");
  $nodes = $xml->GetElementsByTagName('document');

  /* GET CONFIG */
  $ret = GShell("aboutconfig get-config -app gcommercialdocs -sec interface");
  if(!$ret['error'])
   $options['aboutconfig'] = $ret['outarr']['config'];

  for($c=0; $c < count($nodes); $c++)
  {
   if(is_array($options['exclude_idx']) && in_array($c+1, $options['exclude_idx']))
	continue;
   $ret = $this->importFromXML(null, $nodes[$c], $options);
   if(!$ret) return $this->returnError($debug."failed!\n".$this->debug);
   $outArr[] = $ret;
  }

  $this->debug = $debug."done!\n";
  return $outArr;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function importFromXML($xmlData=null, $xmlDocumentNode=null, $options=array())
 {
  $this->debug = "Import from XML data...";

  if($xmlDocumentNode)
   $docNode = $xmlDocumentNode;
  else if($xmlData)
  {
   $xmlData = ltrim(rtrim($xmlData));
   if(strpos($xmlData, "<xml") === false)
    $xmlData = "<xml>".$xmlData."</xml>";

   $xml = new GXML();
   if(!$xml->LoadFromString($xmlData)) return $this->returnError("failed!\nUnable to parse XML data","XML_PARSE_ERROR");
   $nodes = $xml->GetElementsByTagName("document");
   if(!count($nodes)) return $this->returnError("failed!\nNode 'DOCUMENT' missing into xml data","NODE_DOCUMENT_MISSING");
   $docNode = $nodes[0];
  }
  else
   return $this->returnError("failed!\nXML data is empty.","XML_DATA_EMPTY");

  // GET DOCUMENT INFO
  $docInfo = $docNode->toArray();	// num, date, name, type
  $_GROUP = "commdocs-".strtolower($docInfo['type']);

  $_XML = "";

  // GET SUBJECT INFO
  if(is_array($docInfo['subject']))
  {
   $fields = array('code','name','address','city','zipcode','province','countrycode','phone','phone2','fax','cell','email','email2','email3',
	'skype','taxcode','vatnumber');

   $_XML.= "<subject";
   for($c=0; $c < count($fields); $c++)
    $_XML.= " ".$fields[$c]."='".xml_purify($docInfo['subject'][$fields[$c]])."'";
   $_XML.= "/>";
  }

  // GET SHIPPING
  if(is_array($docInfo['shipping']))
  {
   $fields = array('recp','address','city','zipcode','province','countrycode');

   $_XML.= "<shipping";
   for($c=0; $c < count($fields); $c++)
    $_XML.= " ".$fields[$c]."='".xml_purify($docInfo['shipping'][$fields[$c]])."'";
   $_XML.= "/>";
  }

  // GET TRANSPORT 
  if(is_array($docInfo['transport']))
  {
   $fields = array('method','shipper','numplate','causal','datetime','aspect','num','weight','freight','cartage','packingcharges','trackingnumber');

   $_XML.= "<transport";
   for($c=0; $c < count($fields); $c++)
    $_XML.= " ".$fields[$c]."='".xml_purify($docInfo['transport'][$fields[$c]])."'";
   $_XML.= "/>";
  }

  // GET OTHERFEES 
  if(is_array($docInfo['otherfees']))
  {
   $fields = array('collectioncharges','stamp');

   $_XML.= "<otherfees";
   for($c=0; $c < count($fields); $c++)
    $_XML.= " ".$fields[$c]."='".xml_purify($docInfo['otherfees'][$fields[$c]])."'";
   $_XML.= "/>";
  }

  // GET WITHHOLDING TAXES 
  if(is_array($docInfo['withholdingtax']))
  {
   $fields = array('cassaprev','cassaprevvatrate','enasarco','enasarcopercimp','ritacconto','ritaccontopercimp','ritaccontorivinpsinc');

   $_XML.= "<withholdingtax";
   for($c=0; $c < count($fields); $c++)
    $_XML.= " ".$fields[$c]."='".xml_purify($docInfo['withholdingtax'][$fields[$c]])."'";
   $_XML.= "/>";
  }

  // ELEMENTS 
  if(count($docInfo['elements']))
  {
   $_XML.= "<elements>";

   reset($docInfo['elements']);
   while(list($k,$data) = each($docInfo['elements']))
   {
    $fields = array('type','code','sku','asin','ean','gcid','gtin','upc','vencode','serialnumber','lot','brand','name','description',
	 'qty','units','coltint','sizmis','price','vat','vattype');

    $_XML.= "<item";
    for($c=0; $c < count($fields); $c++)
	{
	 switch($fields[$c])
	 {
	  case 'description' : $val = strip_tags($data['content']); break;
	  default : $val = $data[$fields[$c]]; break;
	 }
     $_XML.= " ".$fields[$c]."='".xml_purify($val)."'";
	}
    $_XML.= "/>";
   }

   $_XML.= "</elements>";
  }


  if($options['previewonly'])
  {
   // MAKE PREVIEW (return some data)
   $ret = array('date'=>$docInfo['date'], 'type'=>$docInfo['type'] ? $docInfo['type'] : $options['type'], 'num'=>$docInfo['num']);
   $ret['subject'] = array('code'=>$docInfo['subject']['code'], 'name'=>$docInfo['subject']['name'], 'address'=>$docInfo['subject']['address'],
	'city'=>$docInfo['subject']['city'], 'province'=>$docInfo['subject']['province']);
   $ret['shipping'] = array('recp'=>$docInfo['shipping']['recp'], 'address'=>$docInfo['shipping']['address'], 
	'city'=>$docInfo['shipping']['city'], 'province'=>$docInfo['shipping']['province']);
   $numart = 0;
   $tot_amount = 0;
   $tot_vat = 0;
   reset($docInfo['elements']);
   while(list($k,$el) = each($docInfo['elements']))
   {
	$type = $el['type'];
	if(($type != 'note') && ($type != 'message'))
	{
	 $numart+= $el['qty'];
	 $amount = $el['price'] * $el['qty'];
	 $vat = ($amount/100)*$el['vat'];
	 $tot_amount+= $amount;
	 $tot_vat+= $vat;
	}
   }
   $ret['num_art'] = $numart;
   $ret['amount'] = $tot_amount;
   $ret['vat'] = $tot_vat;
   $ret['total'] = $tot_amount + $tot_vat;
  }
  else
  {
   // EXEC COMMAND
   $_CMD = "commercialdocs generate-fast-document -group '".$_GROUP."' -xml `".$_XML."` -type `"
	.($docInfo['type'] ? $docInfo['type'] : $options['type'])."`";
   if($docInfo['date'])		$_CMD.= " -ctime `".$docInfo['date']."`";

   $_CMD.= " --auto-find-products";

   // auto-register new products into predefined archive (by aboutconfig)
   if(is_array($options['aboutconfig']) && $options['aboutconfig']['options']['xmlimp_regnewprod'])
   {
    $_CMD.= " --auto-register-products";
    if($options['aboutconfig']['options']['xmlimp_defap'])	$_CMD.= " --default-gmart-ap '".$options['aboutconfig']['options']['xmlimp_defap']."'";
    if($options['aboutconfig']['options']['xmlimp_defcat'])	$_CMD.= " --default-gmart-cat '".$options['aboutconfig']['options']['xmlimp_defcat']."'";
   }

   $ret = GShell($_CMD, $this->sessid, $this->shellid);
   if($ret['error']) return $this->returnError("failed!\n".$ret['message'], $ret['error']);
  }

  // RETURN
  $this->debug.= "done!\n";
  return $ret;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function loadDocument($id)
 {
  $_AP = "commercialdocs";
  $this->debug.= "Load document #".$id."...";
  $ret = GShell("dynarc item-info -ap '".$_AP."' -id '".$id."' -extget `cdinfo,cdelements`", $this->sessid, $this->shellid);
  if($ret['error']) return $this->returnError("failed!\n".$ret['message'], $ret['error']);
  $this->debug.= "done!\n";

  return $ret['outarr'];
 }
 //------------------------------------------------------------------------------------------------------------------//
 function getVatById($id, $retVal=null)
 {
  if(is_array($this->VAT_BY_ID))
   return $retVal ? $this->VAT_BY_ID[$id][$retVal] : $this->VAT_BY_ID[$id];

  $this->debug.= "Get vat rates...";
  $ret = GShell("dynarc item-list -ap vatrates -get `vat_type,percentage`",$this->sessid,$this->shellid);
  if($ret['error']) return $this->returnError("failed!\n".$ret['message'], $ret['error']);
  $this->debug.= "done!\n";

  $list = $ret['outarr']['items'];

  $this->VAT_BY_ID = array();

  for($c=0; $c < count($list); $c++)
   $this->VAT_BY_ID[$list[$c]['id']] = $list[$c];

  return $retVal ? $this->VAT_BY_ID[$id][$retVal] : $this->VAT_BY_ID[$id];
 }
 //------------------------------------------------------------------------------------------------------------------//
 function returnError($message="", $errCode="")
 {
  if($message) $this->debug.= $message;
  if($errCode) $this->error = $errCode;

  return false;
 }
 //------------------------------------------------------------------------------------------------------------------//
}
//-------------------------------------------------------------------------------------------------------------------//



