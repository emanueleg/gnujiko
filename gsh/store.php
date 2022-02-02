<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 27-11-2016
 #PACKAGE: gstore
 #DESCRIPTION: Official Gnujiko Store Manager.
 #VERSION: 2.28beta
 #CHANGELOG: 27-11-2016 : Integrate funzioni upload e download con Amazon MWS
			 24-10-2016 : MySQLi integration.
			 05-09-2016 : Create funzioni reset-qty e reset-stock-enh
			 31-08-2016 : Bug fix ricerca articolo su funzione list.
			 22-08-2016 : Aggiornata funzione esporta su excel, aggiunte colonne pr. acq. e valore merce (giac.fisica x pr.acq)
			 09-04-2016 : Aggiunto vendorid su funzione edit movement.
			 30-03-2016 : Aggiunta funzione fix.
			 29-03-2016 : Bug-fix, sostituito $_GET con $_GETF.
			 22-03-2016 : Rifatte funzioni con classe GStore e aggiunte funzioni get-qty e movement-info.
			 01-02-2016 : Bug fix in product-list con valori negativi di booking e incoming.
			 14-01-2016 : Creata funzione export-to-excel.
			 05-10-2015 : Aggiunto argomento -cat su funzione store_productList.
			 26-09-2015 : Aggiunto campo nascondi dal magazzino su funzione product-list e funzione hideinstore.
			 18-02-2015 : Bug fix su funzione store_productList.
			 23-12-2014 : Aggiunto parametro --bypass-preoutput su funzione re-evaluate-stock.
			 19-12-2014 : Bug fix delete movement.
			 16-12-2014 : Bug fix store move.
			 12-12-2014 : Bug fix.
			 19-11-2014 : Aggiunto le varianti nei carichi,scarichi e movimentazioni.
			 01-11-2014 : Completata funzione re-evaluate-stock per la rivalutazione delle scorte di magazzino.
			 20-10-2014 : Aggiunta funzione stock-enhancement per la gestione della valorizzazione dei magazzini.
			 30-07-2014 : Integrato con materiali,componenti e prod. finiti.
			 12-04-2014 : Aggiunto possibilita di filtrare per prodotto su funzione store_productList
			 08-03-2014 : Aggiunto notes su funzione move.
			 20-02-2014 : Aggiunta funzione update-qty per aggiornare la giac. fisic a magazzino di un articolo.
			 17-02-2014 : Aggiunta funzione product-list
			 14-09-2013 : Aggiunta funzione store move.
			 24-07-2013 : Aggiunto funzione find.
			 17-12-2012 : Bug fix vari.
 
 #TODO: Completare la funzione getStockValue e lo scarico tramite LIFO e FIFO.
 #TODO: store reset non resetta variantstock. 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_store($args, $sessid, $shellid=null, $extraVar=null)
{
 switch($args[0])
 {
  case 'new' : return store_new($args, $sessid, $shellid); break;
  case 'edit' : return store_edit($args, $sessid, $shellid); break;
  case 'delete' : return store_delete($args, $sessid, $shellid); break;
  case 'list' : return store_list($args, $sessid, $shellid); break;
  case 'info' : return store_info($args, $sessid, $shellid); break;

  case 'upload' : return store_upload($args, $sessid, $shellid); break;
  case 'download' : return store_download($args, $sessid, $shellid); break;
  case 'move' : return store_move($args, $sessid, $shellid); break;

  case 'find' : return store_find($args, $sessid, $shellid); break;

  case 'movements' : return store_movements($args, $sessid, $shellid); break;
  case 'movement-info' : case 'movinfo' : case 'get-movement-info' : return store_movementInfo($args, $sessid, $shellid); break;
  case 'edit-movement' : return store_editMovement($args, $sessid, $shellid); break;
  case 'delete-movement' : return store_deleteMovement($args, $sessid, $shellid); break;

  case 'product-list' : return store_productList($args, $sessid, $shellid); break;
  case 'update-qty' : return store_updateQty($args, $sessid, $shellid); break;
  case 'reset-qty' : return store_resetQty($args, $sessid, $shellid); break;
  case 'get-qty' : return store_getQty($args, $sessid, $shellid); break;
  case 'fix-qty' : return store_fixQty($args, $sessid, $shellid); break;
  case 'fix' : return store_fix($args, $sessid, $shellid); break;

  case 'get-status' : return store_aboutStore($args, $sessid, $shellid); break;
  case 'stock-enhancement' : case 'enhancement' : return store_enhancement($args, $sessid, $shellid); break;
  case 'print-enhancement' : case 'enhancement-report' : return store_printEnhancementReport($args, $sessid, $shellid); break;

  case 'calculate-stock-value' : case 'get-stock-value' : return store_getStockValue($args, $sessid, $shellid, $extraVar); break;
  case 're-evaluate-stock' : return store_reEvaluateStock($args, $sessid, $shellid, $extraVar); break; // rivalutazione in base ai movimenti
  case 'reset-stock-enh' : case 'reset-stock-enhancement' : return store_resetStockEnhancement($args, $sessid, $shellid, $extraVar); break; // rivalutazione in base alle qta (giac. fisica)

  case 'reset' : case 'empty' : return store_reset($args, $sessid, $shellid); break;

  case 'showinstore' : case 'show-in-store' : return store_showInStore($args, $sessid, $shellid); break;
  case 'hideinstore' : case 'hide-in-store' : return store_hideInStore($args, $sessid, $shellid); break;

  case 'export-to-excel' : return store_exportToExcel($args, $sessid, $shellid); break;
  
  default : return store_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function store_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function store_new($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-group' : {$groupName=$args[$c+1]; $c++;} break;
   case '-gid' : case '-groupid' : {$groupId=$args[$c+1]; $c++;} break;
   case '-docext' : {$docExt=$args[$c+1]; $c++;} break;
  }

 if(!$name) return array('message'=>"You must specify a valid name","error"=>"INVALID_NAME");
 
 if($groupName)
  $groupId = _getGID($groupName);
 
 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO stores(name,doc_ext,gid) VALUES('".$db->Purify($name)."','".$docExt."','".$groupId."')");
 $id = $db->GetInsertId();
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT archive_id FROM dynarc_archive_extensions WHERE extension_name='storeinfo'");
 while($db->Read())
 {
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE id='".$db->record['archive_id']."'");
  if($db2->Read())
  {
   $db2->RunQuery("ALTER TABLE dynarc_".$db2->record['tb_prefix']."_items ADD `store_".$id."_qty` FLOAT NOT NULL");
   $db2->RunQuery("ALTER TABLE dynarc_".$db2->record['tb_prefix']."_stockenhcat ADD `store_".$id."_amount` DECIMAL(10,4) NOT NULL, ADD `store_".$id."_vat` DECIMAL(10,4) NOT NULL, ADD `store_".$id."_total` DECIMAL(10,4) NOT NULL");
   $db2->RunQuery("ALTER TABLE dynarc_".$db2->record['tb_prefix']."_stockenhitm ADD `store_".$id."_qty` FLOAT NOT NULL, ADD `store_".$id."_amount` DECIMAL(10,4) NOT NULL, ADD `store_".$id."_vat` DECIMAL(10,4) NOT NULL, ADD `store_".$id."_total` DECIMAL(10,4) NOT NULL");
  }
  $db2->Close();
 }
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT archive_id FROM dynarc_archive_extensions WHERE extension_name='variantstock'");
 while($db->Read())
 {
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE id='".$db->record['archive_id']."'");
  if($db2->Read())
   $db2->RunQuery("ALTER TABLE dynarc_".$db2->record['tb_prefix']."_variantstock ADD `store_".$id."_qty` FLOAT NOT NULL");
  $db2->Close();
 }
 $db->Close();


 if($docExt)
 {
  // verifica se esiste già la cartella con quell'estensione nei ddt, altrimenti la crea //
  $ret = GShell("dynarc cat-info -ap commercialdocs -tag `".$docExt."` -pt DDT",$sessid,$shellid);
  if($ret['error'])
  {
   $ret = GShell("dynarc new-cat -ap commercialdocs -name `".$name."` -pt DDT -tag `".strtoupper($docExt)."`"
	.($groupId ? " -groupid '".$groupId."'" : ""),$sessid,$shellid);
   if($ret['error'])
	return $ret;
   $docExtCatId = $ret['outarr']['id'];
  }
  else
  {
   $docExtCatId = $ret['outarr']['id'];
   // verifica che la categoria non sia già stata assegnata a qualche altro magazzino //
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT id FROM stores WHERE ext_refcat='".$docExtCatId."'");
   if($db->Read())
   {
	$db->Close();
	// la categoria è gia stata assegnata ad un altro magazzino, quindi ne crea una nuova (anche se ha lo stesso tag) //
    $ret = GShell("dynarc new-cat -ap commercialdocs -name `".$name."` -pt DDT -tag `".strtoupper($docExt)."`"
	 .($groupId ? " -groupid '".$groupId."'" : ""),$sessid,$shellid);
    if($ret['error'])
	 return $ret;
    $docExtCatId = $ret['outarr']['id'];
   }
   else
    $db->Close();
  }

  // aggiorna il catid del magazzino //
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE stores SET ext_refcat='".$docExtCatId."' WHERE id='".$id."'");
  $db->Close();
 }

 $outArr = array('id'=>$id,'name'=>$name,'docext'=>$docExt,'ext_refcat'=>$docExtCatId,'gid'=>$groupId);
 $out = "done!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_edit($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-group' : {$groupName=$args[$c+1]; $c++;} break;
   case '-gid' : case '-groupid' : {$groupId=$args[$c+1]; $c++;} break;
   case '-docext' : {$docExt=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify a valid store id","error"=>"INVALID_ITEM");

 /* Preleva le vecchie informazioni */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM stores WHERE id='".$id."'");
 if(!$db->Read())
 {
  $db->Close();
  return array("message"=>"The store #".$id." does not exists!","error"=>"STORE_DOES_NOT_EXISTS");
 }
 $oldGID = $db->record['gid'];
 $oldExt = $db->record['doc_ext'];
 $oldExtCatId = $db->record['ext_refcat'];
 $oldName = $db->record['name'];
 $db->Close();

 if($oldName && !isset($name))
  $name = $oldName;
 if($oldGID && !isset($groupId))
  $groupId = $oldGID;

 if($groupName)
  $groupId = _getGID($groupName);

 if($oldExt && !$oldExtCatId)
 {
  $ret = GShell("dynarc cat-info -ap commercialdocs -tag `".$oldExt."` -pt DDT",$sessid,$shellid);
  if(!$ret['error'])
  {
   // verifica che la categoria non sia già stata assegnata a qualche altro magazzino //
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT id FROM stores WHERE ext_refcat='".$ret['outarr']['id']."'");
   if(!$db->Read())
	$oldExtCatId = $ret['outarr']['id'];
   $db->Close();
  }
  if(!$oldExtCatId && $docExt)
  {
   // crea una nuova cartella //
   $ret = GShell("dynarc new-cat -ap commercialdocs -name `".$name."` -pt DDT -tag `".strtoupper($docExt)."`"
	.($groupId ? " -groupid '".$groupId."'" : (isset($groupId) ? " -group commdocs-ddt" : "")),$sessid,$shellid);
   if($ret['error'])
	return $ret;
   $oldExtCatId = $ret['outarr']['id'];
  }
 }


 if($oldExtCatId)
 {
  // get informations about DDT folder //
  $ret = GShell("dynarc cat-info -ap commercialdocs -id '".$oldExtCatId."'",$sessid,$shellid);
  if(!$ret['error'])
   $oldExtCatInfo = $ret['outarr'];

  if(!$docExt)
  {
   // rimuove la cartella e sposta tutto il suo contenuto nella cartella di base dei DDT //
   $ret = GShell("dynarc item-list -ap commercialdocs -cat '".$oldExtCatId."'",$sessid,$shellid);
   if(!$ret['error'])
   {
	$list = $ret['outarr']['items'];
	$q = "";
	for($c=0; $c < count($list); $c++)
	 $q.= " -id ".$list[$c]['id'];
	// move items //
	$ret = GShell("dynarc item-move -ap commercialdocs -ct DDT".$q,$sessid,$shellid);
	if($ret['error'])
	 return $ret;
   }
   // rimuove la cartella //
   $ret = GShell("dynarc delete-cat -ap commercialdocs -id '".$oldExtCatId."'",$sessid,$shellid);
   $oldExtCatId = 0;
  }
 }
 else if($docExt)
 {
  // prima verifica che la cartella non sia già stata creata manualmente e che non sia gia assegnata a qualche magazzino //
  $ret = GShell("dynarc cat-info -ap commercialdocs -tag `".$docExt."` -pt DDT",$sessid,$shellid);
  if(!$ret['error'])
  {
   // verifica che la categoria non sia già stata assegnata a qualche altro magazzino //
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT id FROM stores WHERE ext_refcat='".$ret['outarr']['id']."'");
   if(!$db->Read())
	$oldExtCatId = $ret['outarr']['id'];
   $db->Close();
  }
  if(!$oldExtCatId)
  {
   // crea una nuova cartella //
   $ret = GShell("dynarc new-cat -ap commercialdocs -name `".$name."` -pt DDT -tag `".strtoupper($docExt)."`"
		.($groupId ? " -groupid '".$groupId."'" : (isset($groupId) ? " -group commdocs-ddt" : "")),$sessid,$shellid);
   if($ret['error'])
	return $ret;
   $oldExtCatId = $ret['outarr']['id'];
  }
 }

 if($oldExtCatId && $docExt)
 {
  // aggiorna i dati della cartella //
  $ret = GShell("dynarc edit-cat -ap commercialdocs -id '".$oldExtCatId."' -tag '".$docExt."' -name `".$name."`"
		.($groupId ? " -groupid '".$groupId."'" : (isset($groupId) ? " -group commdocs-ddt" : "")),$sessid,$shellid);
 }


 $db = new AlpaDatabase();
 $q = "";
 if($name)
  $q.= ",name='".$db->Purify($name)."'";
 if(isset($docExt))
  $q.= ",doc_ext='".$docExt."'";
 if(isset($groupId))
  $q.= ",gid='".$groupId."'";
 if(isset($oldExtCatId))
  $q.= ",ext_refcat='".$oldExtCatId."'";

 $db->RunQuery("UPDATE stores SET ".ltrim($q,",")." WHERE id='$id'");
 $db->Close();

 $out = "Store has been updated!";

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_delete($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("SELECT * FROM stores WHERE id='$id'");
 else if($name)
  $db->RunQuery("SELECT * FROM stores WHERE name='$name'");
 else
  return array('message'=>"You must specify the store id. (with -id STORE_ID || -name STORE_NAME)","error"=>"INVALID_ITEM");
 if(!$db->Read())
  return array("message"=>"Store ".($id ? "#$id" : $name)." does not exists", "error"=>"ITEM_DOES_NOT_EXISTS");

 $extCatId = $db->record['ext_refcat'];
 $id = $db->record['id'];
 $db->RunQuery("DELETE FROM stores WHERE id='$id'");
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT archive_id FROM dynarc_archive_extensions WHERE extension_name='storeinfo'");
 while($db->Read())
 {
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE id='".$db->record['archive_id']."'");
  if($db2->Read())
  {
   $db2->RunQuery("ALTER TABLE dynarc_".$db2->record['tb_prefix']."_items DROP `store_".$id."_qty`");
   $db2->RunQuery("ALTER TABLE dynarc_".$db2->record['tb_prefix']."_stockenhcat DROP `store_".$id."_amount`, DROP `store_".$id."_vat`, DROP `store_".$id."_total`");
   $db2->RunQuery("ALTER TABLE dynarc_".$db2->record['tb_prefix']."_stockenhitm DROP `store_".$id."_qty`, DROP `store_".$id."_amount`, DROP `store_".$id."_vat`, DROP `store_".$id."_total`");
  }
  $db2->Close();
 }
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT archive_id FROM dynarc_archive_extensions WHERE extension_name='variantstock'");
 while($db->Read())
 {
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE id='".$db->record['archive_id']."'");
  if($db2->Read())
   $db2->RunQuery("ALTER TABLE dynarc_".$db2->record['tb_prefix']."_variantstock DROP `store_".$id."_qty`");
  $db2->Close();
 }
 $db->Close();

 if($extCatId)
 {
  // rimuove la cartella e sposta tutto il suo contenuto nella cartella di base dei DDT //
  $ret = GShell("dynarc item-list -ap commercialdocs -cat '".$extCatId."'",$sessid,$shellid);
  if(!$ret['error'])
  {
   $list = $ret['outarr']['items'];
   $q = "";
   for($c=0; $c < count($list); $c++)
	$q.= " -id ".$list[$c]['id'];
   // move items //
   $ret = GShell("dynarc item-move -ap commercialdocs -ct DDT".$q,$sessid,$shellid);
   if($ret['error'])
    return $ret;
  }
  // rimuove la cartella //
  $ret = GShell("dynarc delete-cat -ap commercialdocs -id '".$extCatId."'",$sessid,$shellid);
 }

 $out.= "Store ".($id ? "#$id" : $name)." has been removed.";
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_list($args, $sessid, $shellid)
{
 $orderBy = "id ASC";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
  }

 $out = "";
 $outArr = array();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM stores WHERE 1 ORDER BY $orderBy");
 while($db->Read())
 {
  if($db->record['gid'] && !_userInGroupId($db->record['gid']))
   continue;
  $outArr[] = array('id'=>$db->record['id'],'name'=>$db->record['name'],'doc_ext'=>$db->record['doc_ext'],'ext_refcat'=>$db->record['ext_refcat'],'gid'=>$db->record['gid']);
  $out.= "#".$db->record['id']." - ".$db->record['name']."\n";
 }
 $db->Close();
 $out.= "\n".count($outArr)." stores found.\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_info($args, $sessid, $shellid)
{
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify a valid store id","error"=>"INVALID_ITEM");

 $out = "";
 $outArr = array();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM stores WHERE id='".$id."'");
 if(!$db->Read())
  return array("message"=>"Store #".$id." does not exists", "error"=>"ITEM_DOES_NOT_EXISTS");
 if($db->record['gid'] && !_userInGroupId($db->record['gid']))
  return array("message"=>"Permission denied! You can't access to informations about this store.","error"=>"PERMISSION_DENIED");
 $outArr = array('id'=>$db->record['id'],'name'=>$db->record['name'],'doc_ext'=>$db->record['doc_ext'],'ext_refcat'=>$db->record['ext_refcat'],'gid'=>$db->record['gid']);
 $out.= "#".$db->record['id']." - ".$db->record['name']."\n";
 $db->Close();

 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_upload($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_SHELL_CMD_PATH;

 $sessInfo = sessionInfo($sessid);
 $out = "";

 $apList = array();
 $qtyList = array();
 $lotList = array();
 $codeList = array();
 $mancodeList = array();
 $vencodeList = array();
 $barcodeList = array();
 $snList = array();
 $unitsList = array();
 $locList = array();
 $nameList = array();
 $vendorList = array();
 $_COLTINT = array();
 $_SIZMIS = array();

 $ids = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-store' : {$storeId=$args[$c+1]; $c++;} break;
   case '-ap' : {$apList[]=$args[$c+1]; $c++;} break;
   case '-id' : {$ids[]=$args[$c+1]; $c++;} break;
   case '-qty' : {$qtyList[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-lot' : {$lotList[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-code' : {$codeList[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-mancode' : {$mancodeList[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-vencode' : {$vencodeList[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-barcode' : {$barcodeList[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-name' : {$nameList[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-serialnumber' : case '-sn' : {$snList[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-units' : {$unitsList[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-location' : case '-loc' : {$locList[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-coltint' : case '-color' : case '-tint' : {$_COLTINT[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-sizmis' : case '-size' : case '-misure' : {$_SIZMIS[count($ids)-1]=$args[$c+1]; $c++;} break;

   case '-ctime' : {$ctime=strtotime($args[$c+1]); $c++; } break;
   case '-action' : {$action=$args[$c+1]; $c++;} break;
   case '-causal' : {$causal=$args[$c+1]; $c++;} break; 

   case '-vendorid' : {$vendorList[count($ids)-1]=$args[$c+1]; $c++;} break;
   
   case '-accountid' : {$accountId=$args[$c+1]; $c++;} break;
   case '-notes' : case '-note' : {$notes=$args[$c+1]; $c++;} break;

   case '-docap' : {$docAp=$args[$c+1]; $c++;} break;
   case '-docid' : {$docId=$args[$c+1]; $c++;} break;
   case '-docref' : {$docRef=$args[$c+1]; $c++;} break;

   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 $out.= "Upload store...";
 $STORE = new GStore($sessid, $shellid);
 for($c=0; $c < count($ids); $c++)
 {
  $ap = $apList[$c] ? $apList[$c] : "";
  $id = $ids[$c];
  $qty = $qtyList[$c] ? $qtyList[$c] : 1;

  $opt = array();
  if(isset($lotList[$c]))		$opt['lot'] = $lotList[$c];
  if(isset($codeList[$c]))		$opt['code'] = $codeList[$c];
  if(isset($mancodeList[$c]))	$opt['mancode'] = $mancodeList[$c];
  if(isset($barcodeList[$c]))	$opt['barcode'] = $barcodeList[$c];
  if(isset($nameList[$c]))		$opt['name'] = $nameList[$c];
  if(isset($snList[$c]))		$opt['serial_number'] = $snList[$c];
  if(isset($unitsList[$c]))		$opt['units'] = $unitsList[$c];
  if(isset($locList[$c]))		$opt['location'] = $locList[$c];
  if(isset($_COLTINT[$c]))		$opt['coltint'] = $_COLTINT[$c];
  if(isset($_SIZMIS[$c]))		$opt['sizmis'] = $_SIZMIS[$c];
  if(isset($vendorList[$c]))	$opt['vendor_id'] = $vendorList[$c];
  if(isset($vencodeList[$c]))	$opt['vencode'] = $vencodeList[$c];
  if(isset($ctime))				$opt['ctime'] = $ctime;
  if(isset($action))			$opt['action'] = $action;
  if(isset($causal))			$opt['causal'] = $causal;
  if(isset($accountId))			$opt['account_id'] = $accountId;
  if(isset($notes))				$opt['note'] = $notes;
  if(isset($docAp))				$opt['doc_ap'] = $docAp;
  if(isset($docId))				$opt['doc_id'] = $docId;
  if(isset($docRef))			$opt['doc_ref'] = $docRef;

  $ret = $STORE->Upload($storeId, $ap, $id, $qty, $opt);
  if(!$ret) return array('message'=>$out."failed!\n".$STORE->debug, 'error'=>$STORE->error);

  // shot event
  $STORE->ShotEvent("OnUpload", array('ap'=>$ap, 'id'=>$id, 'qty'=>$qty, 'options'=>$opt));
 }

 // TRANSPONDER
 if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."transponder.php"))
 {
  $serverList = array();
  $db = new AlpaDatabase();
  for($c=0; $c < count($ids); $c++)
  {
   $ap = $apList[$c] ? $apList[$c] : "";
   $id = $ids[$c];
   $qty = $qtyList[$c] ? $qtyList[$c] : 1;
   
   $db->RunQuery("SELECT t.ref_id,t.server_id,t.service_tag,i.storeqty,i.booked,i.incoming FROM dynarc_".$ap."_transpref AS t INNER JOIN dynarc_".$ap."_items AS i ON i.id=".$id." WHERE t.item_id='".$id."'");
   if($db->Error) { $db->Close(); $db = new AlpaDatabase(); continue; }
   else
   {
	while($db->Read())
	{
	 if(!is_array($serverList[$db->record['server_id']."-".$db->record['service_tag']]))
	  $serverList[$db->record['server_id']."-".$db->record['service_tag']] = array();
	 $serverList[$db->record['server_id']."-".$db->record['service_tag']][] = array('ap'=>$ap, 'id'=>$id, 'qty'=>$qty, 'ref_id'=>$db->record['ref_id'], 'storeqty'=>$db->record['storeqty'], 'booked'=>$db->record['booked'], 'incoming'=>$db->record['incoming']);
	}
   }
  }
  $db->Close();

  reset($serverList);
  while(list($k,$v) = each($serverList))
  {
   $serverId = $k;
   $serviceTag = "";
   if(($p = strpos($k, "-")) !== false)
   {
	$serverId = substr($k, 0, $p);
	$serviceTag = substr($k, $p+1);
   }

   if($verbose) $out.= "\ntransponder update-store-qty -serverid '".$serverId."' -servicetag '".$serviceTag."' -action UPLOAD... ";
   $cmd = "transponder update-store-qty -serverid '".$serverId."' -servicetag '".$serviceTag."' -action UPLOAD";
   for($c=0; $c < count($v); $c++)
	$cmd.= " -ap '".$v[$c]['ap']."' -id '".$v[$c]['id']."' -qty '".$v[$c]['qty']."' -refid '".$v[$c]['ref_id']."' -storeqty '"
		.$v[$c]['storeqty']."' -booked '".$v[$c]['booked']."' -incoming '".$v[$c]['incoming']."'";

   $ret = GShell($cmd, $sessid, $shellid);
   if($verbose) $out.= ($ret['error'] ? "failed!\n" : "done!\n").$ret['message'];
  }

 }
 // EOF - TRANSPONDER

 // AMAZON
 if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."amazonmws.php"))
 {
  $items = array();
  $db = new AlpaDatabase();
  for($c=0; $c < count($ids); $c++)
  {
   $ap = $apList[$c] ? $apList[$c] : "";
   $id = $ids[$c];
   $qty = $qtyList[$c] ? $qtyList[$c] : 1;
   $colTint = isset($_COLTINT[$c]) ? $_COLTINT[$c] : "";
   $sizMis = isset($_SIZMIS[$c]) ? $_SIZMIS[$c] : "";

   $qry = "SELECT sku FROM product_sku WHERE ref_ap='".$ap."' AND ref_id='".$id."' AND trash='0' AND (referrer='amazon' OR referrer='')";
   if($colTint)		$qry.= " AND coltint='".$colTint."'";
   if($sizMis)		$qry.= " AND sizmis='".$sizMis."'";

   $db->RunQuery($qry);
   if($db->Read() && $db->record['sku'])
	$items[] = array('ap'=>$ap, 'id'=>$id, 'coltint'=>$colTint, 'sizmis'=>$sizMis, 'sku'=>$db->record['sku']);
  }
  $db->Close();

  $_CMD = "";
  if(count($items))
  {
   for($c=0; $c < count($items); $c++)
   {
	$ret = GShell("store get-qty -ap '".$items[$c]['ap']."' -id '".$items[$c]['id']."' -coltint `".$items[$c]['coltint']."` -sizmis `".$items[$c]['sizmis']."`", $sessid, $shellid);
	if(!$ret['error']) $_CMD.= " -sku '".$items[$c]['sku']."' -qty '".$ret['outarr']['tot_qty']."'";
   }

  }

  if($_CMD)
  {
   if($verbose) $out.= "\namazonmws update-store-qty".$_CMD." ...";
   $ret = GShell("amazonmws update-store-qty".$_CMD, $sessid, $shellid);
   if($verbose) $out.= ($ret['error'] ? "failed!\n" : "done!\n").$ret['message'];
  }

 }
 // EOF - AMAZON

 $out.= "done!\n";

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_download($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_SHELL_CMD_PATH;

 $sessInfo = sessionInfo($sessid);

 $apList = array();
 $qtyList = array();
 $lotList = array();
 $snList = array();
 $ids = array();
 $codeList = array();
 $_COLTINT = array();
 $_SIZMIS = array();
 $vendoridList=array(); $vendorpriceList=array(); $vendorvatrateList=array();
 $priceList=array(); $vatrateList=array(); $discountList=array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-store' : {$storeId=$args[$c+1]; $c++;} break;
   case '-ap' : {$apList[]=$args[$c+1]; $c++;} break;
   case '-id' : {$ids[]=$args[$c+1]; $c++;} break;
   case '-qty' : {$qtyList[count($ids)-1]=$args[$c+1]; $c++;} break;

   case '-ctime' : {$ctime=strtotime($args[$c+1]); $c++; } break;
   case '-action' : {$action=$args[$c+1]; $c++;} break;
   case '-causal' : {$causal=$args[$c+1]; $c++;} break;
   case '-serialnumber' : case '-sn' : {$snList[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-lot' : {$lotList[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-code' : {$codeList[count($ids)-1]=$args[$c+1]; $c++;} break;

   case '-vendorid' : {$vendoridList[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-vendorprice' : {$vendorpriceList[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-vendorvat' : case '-vendorvatrate' : {$vendorvatrateList[count($ids)-1]=$args[$c+1]; $c++;} break;

   case '-coltint' : case '-color' : case '-tint' : {$_COLTINT[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-sizmis' : case '-size' : case '-misure' : {$_SIZMIS[count($ids)-1]=$args[$c+1]; $c++;} break;
   
   case '-price' : {$priceList[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-vat' : case '-vatrate' : {$vatrateList[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-discount' : {$discountList[count($ids)-1]=$args[$c+1]; $c++;} break;

   case '-accountid' : {$accountId=$args[$c+1]; $c++;} break;
   case '-notes' : case '-note' : {$notes=$args[$c+1]; $c++;} break;

   case '-docap' : {$docAp=$args[$c+1]; $c++;} break;
   case '-docid' : {$docId=$args[$c+1]; $c++;} break;
   case '-docref' : {$docRef=$args[$c+1]; $c++;} break;

   case '--unbook' : $unbook=true; break;
   case '--force-unbook' : $forceUnbook=true; break;
  }

 $out.= "Download store...";
 $STORE = new GStore($sessid, $shellid);
 for($c=0; $c < count($ids); $c++)
 {
  $ap = $apList[$c] ? $apList[$c] : "";
  $id = $ids[$c];
  $qty = $qtyList[$c] ? $qtyList[$c] : 1;

  $opt = array();
  if(isset($lotList[$c]))		$opt['lot'] = $lotList[$c];
  if(isset($codeList[$c]))		$opt['code'] = $codeList[$c];
  //if(isset($mancodeList[$c]))	$opt['mancode'] = $mancodeList[$c];
  //if(isset($barcodeList[$c]))	$opt['barcode'] = $barcodeList[$c];
  //if(isset($nameList[$c]))		$opt['name'] = $nameList[$c];
  if(isset($snList[$c]))		$opt['serial_number'] = $snList[$c];
  //if(isset($unitsList[$c]))		$opt['units'] = $unitsList[$c];
  //if(isset($locList[$c]))		$opt['location'] = $locList[$c];
  if(isset($_COLTINT[$c]))		$opt['coltint'] = $_COLTINT[$c];
  if(isset($_SIZMIS[$c]))		$opt['sizmis'] = $_SIZMIS[$c];
  //if(isset($vendorList[$c]))	$opt['vendor_id'] = $vendorList[$c];
  //if(isset($vencodeList[$c]))	$opt['vencode'] = $vencodeList[$c];
  if(isset($vendoridList[$c]))	$opt['vendor_id'] = $vendoridList[$c];
  if(isset($vendorpriceList[$c]))	$opt['vendor_price'] = $vendorpriceList[$c];
  if(isset($vendorvatrateList[$c]))	$opt['vendor_vatrate'] = $vendorvatrateList[$c];

  if(isset($priceList[$c]))		$opt['price'] = $priceList[$c];
  if(isset($vatrateList[$c]))	$opt['vatrate'] = $vatrateList[$c];
  if(isset($discountList[$c]))	$opt['discount'] = $discountList[$c];

  if(isset($ctime))				$opt['ctime'] = $ctime;
  if(isset($action))			$opt['action'] = $action;
  if(isset($causal))			$opt['causal'] = $causal;
  if(isset($accountId))			$opt['account_id'] = $accountId;
  if(isset($notes))				$opt['note'] = $notes;
  if(isset($docAp))				$opt['doc_ap'] = $docAp;
  if(isset($docId))				$opt['doc_id'] = $docId;
  if(isset($docRef))			$opt['doc_ref'] = $docRef;

  if($unbook)					$opt['unbook'] = true;
  if($forceUnbook)				$opt['forceunbook'] = true;

  $ret = $STORE->Download($storeId, $ap, $id, $qty, $opt);
  if(!$ret) return array('message'=>$out."failed!\n".$STORE->debug, 'error'=>$STORE->error);

  // shot event
  $STORE->ShotEvent("OnDownload", array('ap'=>$ap, 'id'=>$id, 'qty'=>$qty, 'options'=>$opt));
 }

 // TRANSPONDER
 if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."transponder.php"))
 {
  $serverList = array();
  $db = new AlpaDatabase();
  for($c=0; $c < count($ids); $c++)
  {
   $ap = $apList[$c] ? $apList[$c] : "";
   $id = $ids[$c];
   $qty = $qtyList[$c] ? $qtyList[$c] : 1;
   
   $db->RunQuery("SELECT t.ref_id,t.server_id,t.service_tag,i.storeqty,i.booked,i.incoming FROM dynarc_".$ap."_transpref AS t INNER JOIN dynarc_".$ap."_items AS i ON i.id=".$id." WHERE t.item_id='".$id."'");
   if($db->Error) { $db->Close(); $db = new AlpaDatabase(); continue; }
   else
   {
	while($db->Read())
	{
	 if(!is_array($serverList[$db->record['server_id']."-".$db->record['service_tag']]))
	  $serverList[$db->record['server_id']."-".$db->record['service_tag']] = array();
	 $serverList[$db->record['server_id']."-".$db->record['service_tag']][] = array('ap'=>$ap, 'id'=>$id, 'qty'=>$qty, 'ref_id'=>$db->record['ref_id'], 'storeqty'=>$db->record['storeqty'], 'booked'=>$db->record['booked'], 'incoming'=>$db->record['incoming']);
	}
   }
  }
  $db->Close();

  reset($serverList);
  while(list($k,$v) = each($serverList))
  {
   $serverId = $k;
   $serviceTag = "";
   if(($p = strpos($k, "-")) !== false)
   {
	$serverId = substr($k, 0, $p);
	$serviceTag = substr($k, $p+1);
   }

   $cmd = "transponder update-store-qty -serverid '".$serverId."' -servicetag '".$serviceTag."' -action DOWNLOAD";
   for($c=0; $c < count($v); $c++)
	$cmd.= " -ap '".$v[$c]['ap']."' -id '".$v[$c]['id']."' -qty '".$v[$c]['qty']."' -refid '".$v[$c]['ref_id']."' -storeqty '"
		.$v[$c]['storeqty']."' -booked '".$v[$c]['booked']."' -incoming '".$v[$c]['incoming']."'";

   GShell($cmd, $sessid, $shellid); 
  }

 }
 // EOF - TRANSPONDER

 // AMAZON
 if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."amazonmws.php"))
 {
  $items = array();
  $db = new AlpaDatabase();
  for($c=0; $c < count($ids); $c++)
  {
   $ap = $apList[$c] ? $apList[$c] : "";
   $id = $ids[$c];
   $qty = $qtyList[$c] ? $qtyList[$c] : 1;
   $colTint = isset($_COLTINT[$c]) ? $_COLTINT[$c] : "";
   $sizMis = isset($_SIZMIS[$c]) ? $_SIZMIS[$c] : "";

   $qry = "SELECT sku FROM product_sku WHERE ref_ap='".$ap."' AND ref_id='".$id."' AND trash='0' AND (referrer='amazon' OR referrer='')";
   if($colTint)		$qry.= " AND coltint='".$colTint."'";
   if($sizMis)		$qry.= " AND sizmis='".$sizMis."'";

   $db->RunQuery($qry);
   if($db->Read() && $db->record['sku'])
	$items[] = array('ap'=>$ap, 'id'=>$id, 'coltint'=>$colTint, 'sizmis'=>$sizMis, 'sku'=>$db->record['sku']);
  }
  $db->Close();

  $_CMD = "";
  if(count($items))
  {
   for($c=0; $c < count($items); $c++)
   {
	$ret = GShell("store get-qty -ap '".$items[$c]['ap']."' -id '".$items[$c]['id']."' -coltint `".$items[$c]['coltint']."` -sizmis `".$items[$c]['sizmis']."`", $sessid, $shellid);
	if(!$ret['error']) $_CMD.= " -sku '".$items[$c]['sku']."' -qty '".$ret['outarr']['tot_qty']."'";
   }

  }

  if($_CMD)
  {
   if($verbose) $out.= "\namazonmws update-store-qty".$_CMD." ...";
   $ret = GShell("amazonmws update-store-qty".$_CMD, $sessid, $shellid);
   if($verbose) $out.= ($ret['error'] ? "failed!\n" : "done!\n").$ret['message'];
  }

 }
 // EOF - AMAZON

 $out.= "done!\n";

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_move($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);

 $apList = array();
 $qtyList = array();
 $ids = array();
 $_COLTINT = array();
 $_SIZMIS = array();

 $out = "";
 $outArr = array();

 $action = 3; // TRANSFER
 $ctime = time();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$storeFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$storeTo=$args[$c+1]; $c++;} break;
   case '-ap' : {$apList[]=$args[$c+1]; $c++;} break;
   case '-id' : {$ids[]=$args[$c+1]; $c++;} break;
   case '-qty' : {$qtyList[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-coltint' : case '-color' : case '-tint' : {$_COLTINT[count($ids)-1]=$args[$c+1]; $c++;} break;
   case '-sizmis' : case '-size' : case '-misure' : {$_SIZMIS[count($ids)-1]=$args[$c+1]; $c++;} break;

   case '-ctime' : {$ctime=strtotime($args[$c+1]); $c++; } break;
   case '-action' : {$action=$args[$c+1]; $c++;} break;
   case '-causal' : {$causal=$args[$c+1]; $c++;} break;
   case '-notes' : case '-note' : {$notes=$args[$c+1]; $c++;} break;

   case '--generate-ddt' : $generateDDT=true; break;
  }


 $STORE = new GStore($sessid, $shellid);
 $items = array();
 for($c=0; $c < count($ids); $c++)
 {
  $ap = $apList[$c] ? $apList[$c] : $apList[0];
  $id = $ids[$c];
  $qty = $qtyList[$c] ? $qtyList[$c] : 1;

  $opt = array('ctime'=>$ctime, 'action'=>$action);
  if($causal)		$opt['causal'] = $causal;
  if($notes)		$opt['note'] = $notes;
  if($_COLTINT[$c]) $opt['coltint'] = $_COLTINT[$c];
  if($_SIZMIS[$c]) 	$opt['sizmis'] = $_SIZMIS[$c];

  $ret = $STORE->Move($storeFrom, $storeTo, $ap, $id, $qty, $opt);
  if(!$ret) return array('message'=>"Store move error!\n".$STORE->debug, 'error'=>$STORE->error);

  $items[] = $ret;

  // shot event
  $STORE->ShotEvent("OnMove", array('ap'=>$ap, 'id'=>$id, 'qty'=>$qty, 'storefrom'=>$storeFrom, 'storeto'=>$storeTo, 'options'=>$opt));
 }

 /* GENERA DDT */
 $docAp = "";
 $docId = 0;
 if($generateDDT)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT name,doc_ext FROM stores WHERE id='".$storeTo."'");
  $db->Read();
  $storeName = $db->record['name'];
  $docExt = $db->record['doc_ext'];
  $db->Close();

  $catId = 0;
  if($docExt)
  {
   $ret = GShell("dynarc cat-find -ap commercialdocs -pt DDT -tag '".$docExt."' -limit 1",$sessid,$shellid);
   if(!$ret['error'])
	$catId = $ret['outarr'][0]['id'];
  }

  $ret = GShell("dynarc new-item -ap commercialdocs".($catId ? " -cat '".$catId."'" : " -ct DDT")." -group commdocs-ddt -set `tag='INTERNAL'"
	.($docExt ? ",code_ext='".$docExt."'" : "")."` -extset `cdelements.type='note',desc='''Materiale da movimentare a magazzino di "
	.$storeName."'''`",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $ddtInfo = $ret['outarr'];
  $docAp = "commercialdocs";
  $docId = $ddtInfo['id'];
  $outArr['ddtinfo'] = $ddtInfo;

  for($c=0; $c < count($items); $c++)
  {
   $itm = $items[$c];
   $itmType = '';
   switch($itm['at'])
   {
	case 'gmart' : 		$itmType = 'article'; break;
    case 'gproducts' : 	$itmType = 'product'; break;
	case 'gpart' : 		$itmType = 'component'; break;
	case 'gmaterial' : 	$itmType = 'material'; break;
	default : $itmType = 'article'; break;
   }
   $ret = GShell("dynarc edit-item -ap `commercialdocs` -id `".$docId."` -extset `cdelements.type='".$itmType."',refap='".$itm['ap']."',refid='"
	.$itm['id']."',code='".$itm['code_str']."',name='''".$itm['name']."''',desc='''".$itm['description']."''',qty='"
	.$itm['qty']."',price='".$itm['baseprice']."',vatrate='".$itm['vat']."',units='".$itm['units']."'`");
   if($ret['error']) return $ret;
  }

 }

 $out.= "done!";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_movements($args, $sessid, $shellid)
{
 $orderBy = "op_time DESC";
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-store' : {$storeId=$args[$c+1]; $c++;} break;

   case '-from' : {$from=strtotime($args[$c+1]); $c++;} break;
   case '-to' : {$to=strtotime($args[$c+1]); $c++;} break;
   case '-action' : {$action=$args[$c+1]; $c++;} break;
   case '-causal' : {$causal=$args[$c+1]; $c++;} break;

   case '-sn' : case '-serialnumber' : {$serialNumber=$args[$c+1]; $c++;} break;
   case '-lot' : {$lot=$args[$c+1]; $c++;} break;
   case '-refat' : {$refAt=$args[$c+1]; $c++;} break;
   case '-refap' : {$refAp=$args[$c+1]; $c++;} break;
   case '-refid' : {$refId=$args[$c+1]; $c++;} break;
   case '-refcode' : {$refCode=$args[$c+1]; $c++;} break;
   case '-refvendorcode' : {$refVendorCode=$args[$c+1]; $c++;} break;
   case '-refname' : {$refName=$args[$c+1]; $c++;} break;
   case '-refvendorid' : case '-refvendor' : {$refVendorId=$args[$c+1]; $c++;} break;
   
   case '-account' : case '-accountid' : {$accountId=$args[$c+1]; $c++;} break;
   
   case '-docap' : {$docAp=$args[$c+1]; $c++;} break;
   case '-docid' : {$docId=$args[$c+1]; $c++;} break;
   case '-docref' : {$docRef=$args[$c+1]; $c++;} break;

   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '--return-serp-info' : $returnSERPInfo=true; break;
  }
 
 $db = new AlpaDatabase();
 /* COUNT QRY */
 $qry = "";
 if($storeId) 		$qry.= " AND store_id='".$storeId."'";
 if($from) 			$qry.= " AND op_time>='".date('Y-m-d H:i',$from)."'";
 if($to) 			$qry.= " AND op_time<'".date('Y-m-d H:i',$to)."'";
 if($action) 		$qry.= " AND mov_act='".$action."'";
 if($causal) 		$qry.= " AND mov_causal='".$causal."'";
 if($serialNumber) 	$qry.= " AND serial_number='".$serialNumber."'";
 if($lot) 			$qry.= " AND lot='".$lot."'";
 if($refAt) 		$qry.= " AND ref_at='".$refAt."'";
 if($refAp) 		$qry.= " AND ref_ap='".$refAp."'";
 if($refId) 		$qry.= " AND ref_id='".$refId."'";
 if($refCode) 		$qry.= " AND ref_code='".$refCode."'";
 if($refVendorCode) $qry.= " AND ref_vendor_code='".$refVendorCode."'";
 if($refVendorId) 	$qry.= " AND ref_vendor_id='".$refVendorId."'";
 if($refName)
 {
  // da fare //
 }
 if($accountId) 	$qry.= " AND account_id='".$accountId."'";
 if($docAp) 		$qry.= " AND doc_ap='".$docAp."'";
 if($docId) 		$qry.= " AND doc_id='".$docId."'";
 if($docRef) 		$qry.= " AND doc_ref='".$docRef."'";

 if($where)
  $qry.= " AND (".$where.")";
 if(!$qry)
  $qry = "1";
 
 $db->RunQuery("SELECT COUNT(*) FROM store_movements WHERE ".ltrim($qry," AND "));
 $db->Read();
 $outArr['count'] = $db->record[0];

 // CHECK LIMIT //
 if($limit && $outArr['count'])
 {
  $x = explode(",",$limit);
  if($x[1])
  {
   $serpRPP = $x[1];
   $serpFrom = $x[0];
  }
  else
  {
   $serpRPP = $x[0];
   $serpFrom = 0;
  }
  if($serpFrom >= $outArr['count'])
   $serpFrom-= $serpRPP;
  if($serpFrom < 0)
   $serpFrom = 0;
  $limit = $serpFrom ? "$serpFrom,$serpRPP" : $serpRPP;
  if($returnSERPInfo)
  {
   $outArr['serpinfo']['resultsperpage'] = $serpRPP;
   $outArr['serpinfo']['currentpage'] = $serpFrom ? floor($serpFrom/$serpRPP)+1 : 1;
   $outArr['serpinfo']['datafrom'] = $serpFrom;
  }
 }

 /* SELECT QRY */
 $qry.= " ORDER BY ".$orderBy;
 if($limit)
  $qry.= " LIMIT $limit";

 $db->RunQuery("SELECT * FROM store_movements WHERE ".ltrim($qry," AND "));
 while($db->Read())
 {
  $a = array('id'=>$db->record['id'], 'store_id'=>$db->record['store_id'], 'ctime'=>strtotime($db->record['op_time']), 
	'uid'=>$db->record['uid'], 'action'=>$db->record['mov_act'], 
	'causal'=>$db->record['mov_causal'], 'qty'=>$db->record['qty'], 'units'=>$db->record['units'], 'serialnumber'=>$db->record['serial_number'],
	'lot'=>$db->record['lot'], 'ref_at'=>$db->record['ref_at'], 'ref_ap'=>$db->record['ref_ap'], 'ref_id'=>$db->record['ref_id'], 
	'code'=>$db->record['ref_code'], 'name'=>$db->record['ref_name'], 'vendor_id'=>$db->record['ref_vendor_id'], 
	'vendor_code'=>$db->record['ref_vendor_code'], 'vendor_price'=>$db->record['vendor_unitprice'], 'vendor_vatrate'=>$db->record['vendor_vatrate'],
	'price'=>$db->record['price'], 'vatrate'=>$db->record['vatrate'], 'discount_perc'=>$db->record['discount_perc'], 
	'discount_inc'=>$db->record['discount_inc'], 'account_id'=>$db->record['account_id'], 'notes'=>$db->record['notes'], 'doc_ap'=>$db->record['doc_ap'],
	'doc_id'=>$db->record['doc_id'], 'doc_ref'=>$db->record['doc_ref'], 'variant_coltint'=>$db->record['variant_coltint'], 
	'variant_sizmis'=>$db->record['variant_sizmis']);

  if($a['doc_ap'] && $a['doc_id'])
  {
   // retrieve document name //
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT name FROM dynarc_".$a['doc_ap']."_items WHERE id='".$a['doc_id']."'");
   $db2->Read();
   $a['doc_name'] = $db2->record['name'];
   $db2->Close();
  }

  $outArr['items'][] = $a;
 }
 $db->Close();

 $out.= count($outArr['items'])." of ".$outArr['count']." record founds";
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_movementInfo($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;
  }

 if(!$_ID) return array('message'=>"Store movement-info error: You must specify the movement ID.", 'error'=>'INVALID_ID');

 $out.= "Store: Get movement info...";

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM store_movements WHERE id='".$_ID."'");
 if($db->Error) return array('message'=>"failed!\nMySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
 if(!$db->Read()) return array('message'=>"failed!\nMovement #".$_ID." does not exists!", 'error'=>'MOVEMENT_DOES_NOT_EXISTS');

 $outArr = array('id'=>$db->record['id'], 'store_id'=>$db->record['store_id'], 'ctime'=>strtotime($db->record['op_time']), 
	'uid'=>$db->record['uid'], 'action'=>$db->record['mov_act'], 
	'causal'=>$db->record['mov_causal'], 'qty'=>$db->record['qty'], 'units'=>$db->record['units'], 'serialnumber'=>$db->record['serial_number'],
	'lot'=>$db->record['lot'], 'ref_at'=>$db->record['ref_at'], 'ref_ap'=>$db->record['ref_ap'], 'ref_id'=>$db->record['ref_id'], 
	'code'=>$db->record['ref_code'], 'name'=>$db->record['ref_name'], 'vendor_id'=>$db->record['ref_vendor_id'], 
	'vendor_code'=>$db->record['ref_vendor_code'], 'vendor_price'=>$db->record['vendor_unitprice'], 'vendor_vatrate'=>$db->record['vendor_vatrate'],
	'price'=>$db->record['price'], 'vatrate'=>$db->record['vatrate'], 'discount_perc'=>$db->record['discount_perc'], 
	'discount_inc'=>$db->record['discount_inc'], 'account_id'=>$db->record['account_id'], 'notes'=>$db->record['notes'], 'doc_ap'=>$db->record['doc_ap'],
	'doc_id'=>$db->record['doc_id'], 'doc_ref'=>$db->record['doc_ref'], 'variant_coltint'=>$db->record['variant_coltint'], 
	'variant_sizmis'=>$db->record['variant_sizmis']);

 $db->Close();
 $out.= "done!\n";

 return array('message'=>$out, 'outarr'=>$outArr); 
}
//-------------------------------------------------------------------------------------------------------------------//
function store_editMovement($args, $sessid, $shellid)
{
 $out = "";
 $opt = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : 	{$_ID=$args[$c+1]; $c++;} break;
   case '-ctime' : 	{$opt['ctime']=$args[$c+1]; $c++;} break;
   case '-store' : 	{$opt['store_id']=$args[$c+1]; $c++;} break;
   case '-store2' : {$opt['store_2_id']=$args[$c+1]; $c++;} break;	// only for transfers
   case '-causal' : {$opt['causal']=$args[$c+1]; $c++;} break;
   case '-qty' : 	{$opt['qty']=$args[$c+1]; $c++;} break;
   case '-units' : 	{$opt['units']=$args[$c+1]; $c++;} break;
   case '-lot' : 	{$opt['lot']=$args[$c+1]; $c++;} break;
   case '-sn' : case '-serialnumber' : {$opt['serialnumber']=$args[$c+1]; $c++;} break;
   case '-coltint' : {$opt['coltint']=$args[$c+1]; $c++;} break;
   case '-sizmis' : {$opt['sizmis']=$args[$c+1]; $c++;} break;
   case '-note' : 	{$opt['note']=$args[$c+1]; $c++;} break;
   case '-vendorid' : {$opt['vendor_id']=$args[$c+1]; $c++;} break;

   case '-docap' :	{$opt['doc_ap']=$args[$c+1]; $c++;} break;
   case '-docid' :	{$opt['doc_id']=$args[$c+1]; $c++;} break;
   case '-docref' :	{$opt['doc_ref']=$args[$c+1]; $c++;} break;

   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 if(!$_ID) return array('message'=>"Store edit-movement error: You must specify the movement ID.", 'error'=>'INVALID_ID');
 
 $STORE = new GStore($sessid, $shellid);
 $ret = $STORE->EditMovement($_ID, $opt);
 if(!$ret) return array('message'=>$STORE->debug, 'error'=>$STORE->error);

 $out.= $verbose ? $STORE->debug : "done!\n";

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_deleteMovement($args, $sessid, $shellid)
{
 $out = "";
 $_IDS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$_IDS[]=$args[$c+1]; $c++;} break;
  }

 if(!count($_IDS)) return array('message'=>'Store delete-movement failed! You must specify the movement ID', 'error'=>'INVALID_MOVEMENT_ID');

 $STORE = new GStore($sessid, $shellid);
 for($c=0; $c < count($_IDS); $c++)
 {
  $out.= "Delete store movement #".$_IDS[$c]." ...";
  $ret = $STORE->DeleteMovement($_IDS[$c]);
  if(!$ret) return array('message'=>$out."failed!\n".$STORE->debug, 'error'=>$STORE->error);
  $out.= "done!\n";
 }

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_find($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $html = "";

 $_AT = "gmart";
 $_AP = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-at' : {$_AT=$args[$c+1]; $c++;} break;
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break; // se viene specificato l'archivio (-ap) e l'elemento (-id), ricava le info solamente di quell'articolo
   case '-id' : {$_ID=$args[$c+1]; $c++;} break; // se viene specificato l'archivio (-ap) e l'elemento (-id), ricava le info solamente di quell'articolo

   case '-field' : {$field=$args[$c+1]; $c++;} break;
   case '-fields' : {$fields=$args[$c+1]; $c++;} break;

   case '--verbose' : case '-verbose' : $verbose=true; break;

   default : $query = $args[$c]; break;
  }

 // get store list //
 $ret = GShell("store list",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 $storeList = $ret['outarr'];
 $get = "storeqty,booked,incoming,thumb_img";
 for($c=0; $c < count($storeList); $c++)
  $get.= ",store_".$storeList[$c]['id']."_qty";

 if($_AP && $_ID)
 {
  $ret = GShell("dynarc item-info -ap `".$_AP."` -id `".$_ID."` -get `".$get."`",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $ret['outarr']['tb_prefix'] = $_AP;
  $results = array('count'=>1, 'items'=>array());
  $results['items'][] = $ret['outarr'];
  $outArr = $results;
 }
 else
 {
  $qry = "dynarc search".($_AP ? " -ap `".$_AP."`" : " -at `".$_AT."`");

  if(!$field && !$fields)
   $qry.= " -fields code_str,name";
  else if($field)
   $qry.= " -field `".$field."`";
  else if($fields)
   $qry.= " -fields `".$fields."`";

  $qry.= " `".$query."` -get `".$get."`";

  $ret = GShell($qry,$sessid,$shellid);
  if($ret['error'])
   return $ret;

  $results = $ret['outarr'];

  if(!$results['count'])
   return $ret;

  $outArr = $results;
 }

 $outArr['stores'] = $storeList;

 if($verbose)
 {
  $html = "<table border='1' frame='vsides' style='border-color:#dadada'><tr>";
  $html.= "<th>cod.</th><th>descrizione</th><th>tot. qt&agrave; a magazz.</th><th>prenotati</th><th>ordinati</th><th>disponibili</th>";
  for($c=0; $c < count($storeList); $c++)
   $html.= "<th>disp. a ".$storeList[$c]['name']."</th>";
  $html.= "</tr>";
  for($c=0; $c < count($results['items']); $c++)
  {
   $item = $results['items'][$c];
   $available = $item['storeqty'] - $item['booked'];
   $html.= "<tr><td>".$item['code_str']."</td><td>".$item['name']."</td><td align='center'>"
	.($item['storeqty'] ? $item['storeqty'] : "&nbsp;")."</td><td align='center'>"
	.($item['booked'] ? $item['booked'] : "&nbsp;")."</td><td align='center'>"
	.($item['incoming'] ? $item['incoming'] : "&nbsp;")."</td><td align='center'><b>".$available.($item['incoming'] ? " + ".$item['incoming'] : "")."</b></td>";
   for($i=0; $i < count($storeList); $i++)
	$html.= "<td align='center'>".$item["store_".$storeList[$i]['id']."_qty"]."</td>";
   $html.= "</tr>";
  }
  $html.= "</table>";
 }

 return array('message'=>$out, 'outarr'=>$outArr, 'htmloutput'=>$html);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_productList($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $_AT = "gmart";
 $_AP = "";

 $orderBy = "name ASC";
 $limit = 0;
 $getStockEnhancement = false;
 $getVariantStock = false;
 $get = "";
 $find = "";
 $findFields = "name";

 $_STORES = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-at' : {$_AT=$args[$c+1]; $c++;} break;
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;
   case '-cat' : {$_CATID=$args[$c+1]; $c++;} break;

   case '-store' : case '-storeid' : {$storeId=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '-find' : {$find=$args[$c+1]; $c++;} break; // find article by name (or other fields)
   case '-findfields' : {$findFields=$args[$c+1]; $c++;} break; // fields separated by comma (,)

   case '--get-stock-enhancement' : $getStockEnhancement=true; break;
   case '--get-variants' : $getVariantStock=true; break;
   case '-get' : {$get=$args[$c+1]; $c++;} break;	// ulteriori campi da ritornare

   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
  }

 $opt = array('at'=>$_AT, 'ap'=>$_AP, 'id'=>$_ID, 'cat'=>$_CATID, 'store'=>$storeId, 'orderby'=>$orderBy, 'limit'=>$limit,
	'getstockenh'=>$getStockEnhancement, 'getvariants'=>$getVariantStock, 'get'=>$get);

 $STORE = new GStore($sessid, $shellid);

 if($_AP && $_ID)
 {
  $ret = $STORE->GetProductInfo($_AP, $_ID, $get, $opt);
  if(!$ret) return array('message'=>$STORE->debug, 'error'=>$STORE->error);
  $outArr['count'] = 1;
  $outArr['items'] = array($ret);
  return array('message'=>$STORE->debug, 'outarr'=>$outArr);
 }
 else if($find)
 {
  $ret = GShell("fastfind products -at '".$_AT."' -ap '".$_AP."' -fields '".$findFields."' -limit '".$limit."' `".$find."`", $sessid, $shellid);
  if($ret['error']) return $ret;

  $list = $ret['outarr']['results'];
  $outArr['count'] = $ret['outarr']['count'];
  for($c=0; $c < count($list); $c++)
  {
   $ret = $STORE->GetProductInfo($list[$c]['ap'], $list[$c]['id'], $get, $opt);
   if(!$ret) return array('message'=>$STORE->debug, 'error'=>$STORE->error);
   $outArr['items'][] = $ret;
  }
  return array('message'=>$STORE->debug, 'outarr'=>$outArr);
 }
 else
 {
  $ret = $STORE->GetProductList($where, $opt);
  if(!$ret) return array('message'=>$STORE->debug, 'error'=>$STORE->error);
  return array('message'=>$STORE->debug, 'outarr'=>$ret);
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function store_updateQty($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-store' : {$storeId=$args[$c+1]; $c++;} break;
   case '-qty' : {$qty=$args[$c+1]; $c++;} break;
  }

 $STORE = new GStore($sessid, $shellid);
 $ret = $STORE->SetStoreQty($storeId, $_AP, $id, $qty);
 if($ret['error']) return array('message'=>$STORE->debug, 'error'=>$STORE->error);

 // shot event
 $STORE->ShotEvent("OnUpdateQty", array('ap'=>$_AP, 'id'=>$id, 'qty'=>$qty, 'storeid'=>$storeId));

 return array('message'=>$STORE->debug);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_resetQty($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;
 require_once($_BASE_PATH."var/lib/excel.php");

 $out = "";
 $outArr = array();

 $_AT = "gmart";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-at'  : {$_AT=$args[$c+1]; $c++;} break;
   case '-xml' : {$xmlData=$args[$c+1]; $c++;} break;
  }

 $STORE = new GStore($sessid, $shellid);

 // LOAD DATA FROM XML
 if($xmlData)
 {
  $xml = new GXML();
  if(!$xml->LoadFromString("<xml>".$xmlData."</xml>"))
  return array('message'=>"Reset store qty failed!\nXML Error: Unable to load xml data", "error"=>"XML_ERROR");

  $_ARCHIVES = array();
  $_STORES = array();
  $_DATA = array();

  $db = new AlpaDatabase();

  // Get archive list with type gmart
  $db->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE archive_type='".$_AT."' AND trash='0' ORDER BY id ASC");
  while($db->Read()) { $_ARCHIVES[] = $db->record['tb_prefix']; }

  // Get store list
  $db->RunQuery("SELECT id FROM stores WHERE 1 ORDER BY id ASC");
  while($db->Read()) { $_STORES[] = $db->record['id']; }

  $items = $xml->GetElementsByTagName('item');
  for($c=0; $c < count($items); $c++)
  {
   $node = $items[$c];
   if(!$node->getString('code')) continue;
   // get product id by code
   $id=0; $variantId=0; $variantType=""; $variantName=""; $colTint=""; $sizMis="";
   for($i=0; $i < count($_ARCHIVES); $i++)
   {
	$ap = $_ARCHIVES[$i];
	$db->RunQuery("SELECT id FROM dynarc_".$ap."_items WHERE code_str='".$node->getString('code')."' AND trash='0' LIMIT 1");
	if($db->Read())
	{
	 $id = $db->record['id'];
	 break;
	}
    else
    {
	 // find into variants
	 $db->RunQuery("SELECT id,item_id,variant_name,variant_type FROM dynarc_".$ap."_varcodes WHERE code='".$node->getString('code')."' LIMIT 1");
	 if($db->Error) { $db->Close(); $db = new AlpaDatabase(); }
	 else if($db->Read())
	 {
	  $id = $db->record['item_id'];
	  $variantId = $db->record['id'];
	  $variantType = $db->record['variant_type'];
	  $variantName = $db->record['variant_name'];
	  break;
	 }
    }
   } // EOF - for i 

   if(!$id) continue;
   switch($variantType)
   {
	case 'color' : case 'tint' : $colTint=$variantName; break;
	case 'size' : case 'dim' : case 'other' : $sizMis=$variantName; break;
   }
   
   $data = array('ap'=>$ap, 'id'=>$id, 'variant_id'=>$variantId, 'variant_type'=>$variantType, 'variant_name'=>$variantName, 'coltint'=>$colTint, 'sizmis'=>$sizMis, 'qtybystore'=>array());
   for($i=0; $i < count($_STORES); $i++)
	$data['qtybystore'][$_STORES[$i]] = $node->getString('store_'.$_STORES[$i].'_qty');

   $_DATA[] = $data;
  }

  $db->Close();

  $ret = $STORE->ResetStoreQty($_DATA);
  if($ret['error']) return array('message'=>$STORE->debug, 'error'=>$STORE->error);
 }

 return array('message'=>$STORE->debug);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_getQty($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-store' : {$storeId=$args[$c+1]; $c++;} break;
   case '-coltint' : {$colTint=$args[$c+1]; $c++;} break;
   case '-sizmis' : {$sizMis=$args[$c+1]; $c++;} break;

   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 $opt = array();
 if($colTint)	$opt['coltint'] = $colTint;
 if($sizMis)	$opt['sizmis'] = $sizMis;

 $STORE = new GStore($sessid, $shellid);
 $ret = $STORE->GetStoreQty($_AP, $id, $storeId, $opt);
 if($ret['error']) return array('message'=>$STORE->debug, 'error'=>$STORE->error);

 /* VERBOSE */
 if($verbose)
 {
  //$storelist = $STORE->GetStoreList();
  if($storeId && ($colTint || $sizMis))
  {
   $storeInfo = $STORE->GetStoreById($storeId);
   $out.= "Total qty (into all stores): ".$ret['tot_qty']."\n";
   $out.= "Total qty into store ".$storeInfo['name'].": ".$ret['store_qty']."\n";
   if($colTint && $sizMis)
	$out.= "Tot. ".$colTint." ".$sizMis.": ".$ret['var_qty']."\n";
   else if($colTint) $out.= "Tot. ".$colTint.": ".$ret['var_qty']."\n";
   else if($sizMis)	 $out.= "Tot. ".$sizMis.": ".$ret['var_qty']."\n";
  }
  else if($storeId)
  {
   $storeInfo = $STORE->GetStoreById($storeId);
   $out.= "Total qty (into all stores): ".$ret['tot_qty']."\n";
   $out.= "Total qty into store ".$storeInfo['name'].": ".$ret['store_qty']."\n";
   if(is_array($ret['variants']) && count($ret['variants']))
   {
	for($c=0; $c < count($ret['variants']); $c++)
    {
	 if($ret['variants'][$c]['coltint'])	$out.= " ".$ret['variants'][$c]['coltint'];
	 if($ret['variants'][$c]['sizmis'])		$out.= " ".$ret['variants'][$c]['sizmis'];
	 $out.= ": ".$ret['variants'][$c]['store_qty']."\n";
	}
   }
  }
  else
  {
   $storelist = $STORE->GetStoreList();
   $out.= "Total qty (into all stores): ".$ret['tot_qty']."\n";
   for($c=0; $c < count($storelist); $c++)
   {
    if($ret['store_'.$storelist[$c]['id'].'_qty'])
	 $out.= "Total qty into store ".$storelist[$c]['name'].": ".$ret['store_'.$storelist[$c]['id'].'_qty']."\n";
   }

   if(is_array($ret['variants']) && count($ret['variants']))
   {
	$out.= "\nQty in store by variant:\n";
	for($c=0; $c < count($ret['variants']); $c++)
	{
	 $var = $ret['variants'][$c];
	 if($var['coltint'])	$out.= " ".$ret['variants'][$c]['coltint'];
	 if($var['sizmis'])		$out.= " ".$ret['variants'][$c]['sizmis'];
	 $out.= ":";

	 for($i=0; $i < count($storelist); $i++)
	 {
	  if($var['store_'.$storelist[$i]['id'].'_qty'])
	   $out.= " ".$storelist[$i]['name']."=".$var['store_'.$storelist[$i]['id'].'_qty'];
	 }
	 $out.= "\n";
	}
   }
  }
 } /* EOF - VERBOSE */

 return array('message'=>$out, 'outarr'=>$ret);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_fixQty($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $_ARCHIVES = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
  }

 if(!$_AP)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE archive_type='gmart'");
  while($db->Read())
  {
   $_ARCHIVES[] = $db->record['tb_prefix'];
  }
  $db->Close();
 }
 else
  $_ARCHIVES[] = $_AP;

 /* get stores */
 $_STORES = array();
 $getQry = "";
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM stores WHERE 1");
 while($db->Read())
 {
  $_STORES[] = $db->record['id'];
  $getQry.= ",store_".$db->record['id']."_qty";
 }
 $db->Close();

 for($c=0; $c < count($_ARCHIVES); $c++)
 {
  $_AP = $_ARCHIVES[$c];
  $db = new AlpaDatabase();
  $db2 = new AlpaDatabase();
  $db->RunQuery("SELECT id".$getQry.",booked,incoming,loaded,downloaded FROM dynarc_".$_AP."_items WHERE 1");
  while($db->Read())
  {
   $id = $db->record['id'];
   $setQry = "";
   $storeQty = 0;
   for($i=0; $i < count($_STORES); $i++)
   {
    if($db->record['store_'.$_STORES[$i].'_qty'] < 0)
	 $setQry.= ",store_".$_STORES[$i]."_qty='0'";
	else
	 $storeQty+= $db->record['store_'.$_STORES[$i].'_qty'];
    /* some checks */
	if($db->record['booked'] < 0)
	 $setQry.= ",booked='0'";
	if($db->record['incoming'] < 0)
	 $setQry.= ",incoming='0'";
	if($db->record['loaded'] < 0)
	 $setQry.= ",loaded='0'";
	if($db->record['downloaded'] < 0)
	 $setQry.= ",downloaded='0'";
   }
   $db2->RunQuery("UPDATE dynarc_".$_AP."_items SET storeqty='".$storeQty."'".$setQry." WHERE id='".$id."'");
  }
  $db2->Close();
  $db->Close();
 }

 $out.= "done!";
 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_fix($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root") return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $opt = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$opt['ap']=$args[$c+1]; $c++;} break;
   case '-at' : {$opt['at']=$args[$c+1]; $c++;} break;
  }

 $STORE = new GStore($sessid, $shellid);
 $ret = $STORE->CheckAndFix($opt);
 if(!$ret) return array('message'=>$STORE->debug, 'error'=>$STORE->error);
 
 return array('message'=>$STORE->debug);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_aboutStore($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $_ARCHIVES = array();
 $_AT = "gmart";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : case '-storeid' : case '-store' : {$storeId=$args[$c+1]; $c++;} break;
   case '-at' : {$_AT=$args[$c+1]; $c++;} break;
   case '-ap' : case '-refap' : {$_AP=$args[$c+1]; $c++;} break;

   // about movements //
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;
   //case '-refap' : {$refAp=$args[$c+1]; $c++;} break; // optional
   case '-refid' : {$refId=$args[$c+1]; $c++;} break; // optional
   
   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 $STORE = new GStore($sessid, $shellid);
 $opt = array('at'=>$_AT, 'ap'=>$_AP, 'from'=>$dateFrom, 'to'=>$dateTo, 'refid'=>$refId);
 $ret = $STORE->AboutStore($storeId, $opt);
 if(!$ret) return array('message'=>$STORE->debug, 'error'=>$STORE->error);
 $outArr = $ret;

 if($verbose)
 {
  $out.= "Cataloghi scansionati: ".$outArr['archives_count']."\n";
  $out.= "N. tot. di articoli: ".$outArr['totitems_count']."\n";
  $out.= "N. di articoli esauriti: ".$outArr['soldout_count']."\n";
  $out.= "N. di articoli in esaurimento: ".$outArr['underminstock_count']."\n";
  $out.= "Valore netto merce in tutti i magazzini: ".number_format($outArr['stock_value'],2,",",".")." &euro;\n";
  if($storeId)
  {
   $out.= "\n";
   $out.= "Tot. articoli a magazzino '".$outArr['store']['name']."': ".$outArr['store']['items_count']."\n";
   $out.= "Valore netto merce a magazzino '".$outArr['store']['name']."': ".number_format($outArr['store']['stock_value'],2,",",".")." &euro;\n";
  }

  $out.= "\n";
  $out.= "N. operaz. caricamento: ".$outArr['upload_count']."\n";
  $out.= "Tot. qt&agrave; caricata: ".$outArr['upload_qty']."\n";
  
  $out.= "N. operaz. scaricamento: ".$outArr['download_count']."\n";
  $out.= "Tot. qt&agrave; scaricata: ".$outArr['download_qty']."\n";

  $out.= "N. operaz. movimentaz.: ".$outArr['transfer_count']."\n";
  $out.= "Tot. qt&agrave; movimentata: ".$outArr['transfer_qty']."\n"; 

 }

 return array('message'=>$out, 'outarr'=>$outArr);

 /* FINE tutto il resto del codice qui sotto va eliminato */

 $getFields = "id,code_str,name,storeqty,minimum_stock";
 if($storeId)
  $getFields.= ",store_".$storeId."_qty";
 $x = explode(",",$getFields);

 if(!$_AP)
 {
  /* Get archives */
  $ret = GShell("dynarc archive-list -type '".$_AT."' -a",$sessid,$shellid);
  if($ret['error']) return $ret;
  $_ARCHIVES = $ret['outarr'];
 }
 else
 {
  $ret = GShell("dynarc archive-info -prefix '".$_AP."'",$sessid,$shellid);
  if($ret['error']) return $ret;
  $_ARCHIVES[] = $ret['outarr'];
 }


 $outArr['items_count'] = 0;			// num. totale di articoli con prezzo d'acquisto
 $outArr['soldout_count'] = 0;			// num. di articoli esauriti
 $outArr['underminstock_count'] = 0;	// num. di articoli in esaurimento
 $outArr['stock_value'] = 0;			// valore netto merce in tutti i magazzini
 $outArr['missprice_count'] = 0;		// num. di articoli senza prezzo d'acquisto
 $outArr['totitems_count'] = 0;			// num. totale di articoli

 if($storeId)
 {
  $ret = GShell("store info -id '".$storeId."'",$sessid,$shellid);
  if($ret['error']) return $ret;
  $outArr['store'] = array("id"=>$storeId, "name"=>$ret['outarr']['name'], "items_count"=>0, "stock_value"=>0);
 }

 for($c=0; $c < count($_ARCHIVES); $c++)
 {
  $_AP = $_ARCHIVES[$c]['prefix'];

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$_AP."_items WHERE trash='0'");
  $db->Read();
  $outArr['totitems_count']+= $db->record[0];
  $db->Close();

  $qry = "SELECT ";
  $gets = "";
  for($i=0; $i < count($x); $i++)
   $gets.= ",dynarc_".$_AP."_items.".$x[$i];
  $qry.= ltrim($gets,","); 

  if(($_AP == "gmart") || ($_AP == "gpart") || ($_AP == "gmaterial"))
  {
   $qry.= ",dynarc_".$_AP."_vendorprices.price FROM dynarc_".$_AP."_items JOIN dynarc_".$_AP."_vendorprices ON dynarc_".$_AP."_items.id = dynarc_"
	.$_AP."_vendorprices.item_id";
  }
  else
   $qry.= " FROM dynarc_".$_AP."_items";
  $qry.= " WHERE trash='0'";

  $db = new AlpaDatabase();
  $ret = $db->RunQuery($qry);
  if(!$ret)
   return array("message"=>"MySQL error: ".$db->Error, "error"=>"MYSQL_ERROR");
  while($db->Read())
  {
   $outArr['items_count']++;
   $outArr['stock_value']+= ($db->record['storeqty'] * $db->record['price']);

   if(!$db->record['storeqty'])
	$outArr['soldout_count']++;
   else if($db->record['storeqty'] <= $db->record['minimum_stock'])
    $outArr['underminstock_count']++;

   if($storeId)
   {
	$outArr['store']['items_count']++;
	$outArr['store']['stock_value']+= ($db->record['store_'.$storeId.'_qty'] * $db->record['price']);
   }
  }
  $db->Close();
 }

 $outArr['missed_price'] = $outArr['totitems_count'] - $outArr['items_count'];

 if($verbose)
 {
  $out.= "Cataloghi scansionati: ".count($_ARCHIVES)."\n";
  $out.= "N. tot. di articoli: ".$outArr['totitems_count']."\n";
  $out.= "N. di articoli con prezzo d'acquisto: ".$outArr['items_count']."\n";
  $out.= "N. di articoli senza prezzo d'acquisto: ".$outArr['missed_price']."\n";
  $out.= "N. di articoli esauriti: ".$outArr['soldout_count']."\n";
  $out.= "N. di articoli in esaurimento: ".$outArr['underminstock_count']."\n";
  $out.= "Valore netto merce in tutti i magazzini: ".number_format($outArr['stock_value'],2,",",".")." &euro;\n";
  if($storeId)
  {
   $out.= "\n";
   $out.= "Tot. articoli a magazzino '".$outArr['store']['name']."': ".$outArr['store']['items_count']."\n";
   $out.= "Valore netto merce a magazzino '".$outArr['store']['name']."': ".number_format($outArr['store']['stock_value'],2,",",".")." &euro;\n";
  }
 }

 /* ABOUT MOVEMENTS */
 $qry = "SELECT store_id,op_time,mov_act,qty FROM store_movements";
 $where = "";
 if($dateFrom) $where.= " AND op_time>='".$dateFrom."'";
 if($dateTo) $where.= " AND op_time<'".$dateTo."'";
 if($storeId) $where.= " AND store_id='".$storeId."'";
 if($refAp) $where.= " AND ref_ap='".$refAp."'";
 if($refId) $where.= " AND ref_id='".$refId."'";

 $qry.= $where ? " WHERE ".ltrim($where," AND ") : " WHERE 1";

 $outArr['upload_count'] = 0;
 $outArr['download_count'] = 0;
 $outArr['transfer_count'] = 0;

 $outArr['tot_upload_qty'] = 0;
 $outArr['tot_download_qty'] = 0;
 $outArr['tot_transfer_qty'] = 0;

 $db = new AlpaDatabase();
 $ret = $db->RunQuery($qry);
 if(!$ret)
  return array("message"=>"MySQL Error: ".$db->Error, "error"=>"MYSQL_ERROR");
 while($db->Read())
 {
  switch($db->record['mov_act'])
  {
   case 1 : {
	 $outArr['upload_count']++;
	 $outArr['tot_upload_qty']+= $db->record['qty']; 
	} break;
   case 2 : {
	 $outArr['download_count']++;
	 $outArr['tot_download_qty']+= $db->record['qty'];
	} break;
   case 3 : {
	 $outArr['transfer_count']++;
	 $outArr['tot_transfer_qty']+= $db->record['qty']; 
	} break;
  }
 }
 $db->Close();

 if($verbose)
 {
  $out.= "\n";
  $out.= "N. operaz. caricamento: ".$outArr['upload_count']."\n";
  $out.= "Tot. qt&agrave; caricata: ".$outArr['tot_upload_qty']."\n";
  
  $out.= "N. operaz. scaricamento: ".$outArr['download_count']."\n";
  $out.= "Tot. qt&agrave; scaricata: ".$outArr['tot_download_qty']."\n";

  $out.= "N. operaz. movimentaz.: ".$outArr['transfer_count']."\n";
  $out.= "Tot. qt&agrave; movimentata: ".$outArr['tot_transfer_qty']."\n";
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_enhancement($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array('results'=>array(), 'amount'=>0, 'vat'=>0, 'total'=>0);

 $_STORES = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-store' : case '-storeid' : {$storeId=$args[$c+1]; $c++;} break;	// Ricava i totali x magazzino
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;							// Ricava i totali x catalogo
   case '-cat' : {$_CATID=$args[$c+1]; $c++;} break;						// Ricava i totali x categoria
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;							// Ricava i totali x articolo

   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 if(!$storeId)
 {
  $ret = GShell("store list",$sessid,$shellid);
  $storeList = $ret['outarr'];
  $storeQ = "";
  for($c=0; $c < count($storeList); $c++)
  {
   $storeQ.= ",store_".$storeList[$c]['id']."_amount,store_".$storeList[$c]['id']."_vat,store_".$storeList[$c]['id']."_total";
   $_STORES[$storeList[$c]['id']] = $storeList[$c]['name'];
  }
  $storeQ = ltrim($storeQ,",");
 }
 else
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT name FROM stores WHERE id='".$storeId."'");
  $db->Read();
  $storeQ = "store_".$storeId."_amount,store_".$storeId."_vat,store_".$storeId."_total";
  $_STORES[$storeId] = $db->record['name'];
  $db->Close();
 }
 
 if($_AP && $_ID)
 {
  /* RICAVA I TOTALI X ARTICOLO */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT ".$storeQ." FROM dynarc_".$_AP."_stockenhitm WHERE item_id='".$_ID."'");
  if($db->Error)
   return array('message'=>'MySQL Error: '.$db->Error, 'error'=>'MYSQL_ERROR');
  $db->Read();
  reset($_STORES);
  while(list($sid,$v)=each($_STORES))
  {
   $outArr['results'][] = array('store_id'=>$sid, 'ap'=>$_AP, 
	'amount'=>$db->record['store_'.$sid.'_amount'], 'vat'=>$db->record['store_'.$sid.'_vat'], 'total'=>$db->record['store_'.$sid.'_total']);
   $outArr['amount']+=$db->record['store_'.$sid.'_amount'];
   $outArr['vat']+=$db->record['store_'.$sid.'_vat'];
   $outArr['total']+=$db->record['store_'.$sid.'_total'];
  }
  $db->Close();

  if($verbose)
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT name FROM dynarc_".$_AP."_items WHERE id='".$_ID."'");
   $db->Read();
   $out.= "Valorizzazione magazzino per l&lsquo;articolo: ".$db->record['name']."\n";
   $db->Close();
   $out.= "<table cellspacing='0' cellpadding='3' border='0'>";
   $out.= "<tr><th>Magazzino</th><th>Imponibile</th><th>IVA</th><th>Totale</th></tr>";
   for($c=0; $c < count($outArr['results']); $c++)
   {
	$res = $outArr['results'][$c]; 
	if($res['amount'] == 0)
	 continue;
	$out.= "<tr><td>".$_STORES[$res['store_id']]."</td>";
	$out.= "<td align='right'>".number_format($res['amount'],2,',','.')." &euro;</td>";
	$out.= "<td align='right'>".number_format($res['vat'],2,',','.')." &euro;</td>";
	$out.= "<td align='right'>".number_format($res['total'],2,',','.')." &euro;</td></tr>";
   }
   $out.= "<tr><td colspan='4'><hr/></td></tr>";
   $out.= "<tr><td align='right'><i>Totali:</i></td>";
   $out.= "<td align='right'>".number_format($outArr['amount'],2,',','.')." &euro;</td>";
   $out.= "<td align='right'>".number_format($outArr['vat'],2,',','.')." &euro;</td>";
   $out.= "<td align='right'>".number_format($outArr['total'],2,',','.')." &euro;</td></tr>";
   $out.= "</table>";
  }
 }
 else if($_AP && $_CATID)
 {
  /* RICAVA I TOTALI X CATEGORIA */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT ".$storeQ." FROM dynarc_".$_AP."_stockenhcat WHERE cat_id='".$_CATID."'");
  $db->Read();
  reset($_STORES);
  while(list($sid,$v)=each($_STORES))
  {
   $outArr['results'][] = array('store_id'=>$sid, 'ap'=>$_AP, 
	'amount'=>$db->record['store_'.$sid.'_amount'], 'vat'=>$db->record['store_'.$sid.'_vat'], 'total'=>$db->record['store_'.$sid.'_total']);
   $outArr['amount']+=$db->record['store_'.$sid.'_amount'];
   $outArr['vat']+=$db->record['store_'.$sid.'_vat'];
   $outArr['total']+=$db->record['store_'.$sid.'_total'];
  }
  $db->Close();

  if($verbose)
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT name FROM dynarc_".$_AP."_categories WHERE id='".$_CATID."'");
   $db->Read();
   $out.= "Valorizzazione magazzino per la categoria: ".$db->record['name']."\n";
   $db->Close();
   $out.= "<table cellspacing='0' cellpadding='3' border='0'>";
   $out.= "<tr><th>Magazzino</th><th>Imponibile</th><th>IVA</th><th>Totale</th></tr>";
   for($c=0; $c < count($outArr['results']); $c++)
   {
	$res = $outArr['results'][$c]; 
	if($res['amount'] == 0)
	 continue;
	$out.= "<tr><td>".$_STORES[$res['store_id']]."</td>";
	$out.= "<td align='right'>".number_format($res['amount'],2,',','.')." &euro;</td>";
	$out.= "<td align='right'>".number_format($res['vat'],2,',','.')." &euro;</td>";
	$out.= "<td align='right'>".number_format($res['total'],2,',','.')." &euro;</td></tr>";
   }
   $out.= "<tr><td colspan='4'><hr/></td></tr>";
   $out.= "<tr><td align='right'><i>Totali:</i></td>";
   $out.= "<td align='right'>".number_format($outArr['amount'],2,',','.')." &euro;</td>";
   $out.= "<td align='right'>".number_format($outArr['vat'],2,',','.')." &euro;</td>";
   $out.= "<td align='right'>".number_format($outArr['total'],2,',','.')." &euro;</td></tr>";
   $out.= "</table>";
  }
 }
 else if($_AP)
 {
  /* RICAVA I TOTALI X CATALOGO */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT store_id,ap,amount,vat,total FROM stock_enhancement WHERE ap='".$_AP."'".($storeId ? " AND store_id='".$storeId."'" : ""));
  while($db->Read())
  {
   $outArr['results'][] = array('store_id'=>$db->record['store_id'], 'ap'=>$db->record['ap'],
	'amount'=>$db->record['amount'], 'vat'=>$db->record['vat'], 'total'=>$db->record['total']);
   $outArr['amount']+= $db->record['amount'];
   $outArr['vat']+= $db->record['vat'];
   $outArr['total']+= $db->record['total'];
  }
  $db->Close();

  if($verbose)
  {
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT name FROM dynarc_archives WHERE tb_prefix='".$_AP."'");
   $db->Read();
   $out.= "Valorizzazione magazzino per il catalogo: ".$db->record['name']."\n";
   $db->Close();
   $out.= "<table cellspacing='0' cellpadding='3' border='0'>";
   $out.= "<tr><th>Magazzino</th><th>Imponibile</th><th>IVA</th><th>Totale</th></tr>";
   for($c=0; $c < count($outArr['results']); $c++)
   {
	$res = $outArr['results'][$c]; 
	if($res['amount'] == 0)
	 continue;
	$out.= "<tr><td>".$_STORES[$res['store_id']]."</td>";
	$out.= "<td align='right'>".number_format($res['amount'],2,',','.')." &euro;</td>";
	$out.= "<td align='right'>".number_format($res['vat'],2,',','.')." &euro;</td>";
	$out.= "<td align='right'>".number_format($res['total'],2,',','.')." &euro;</td></tr>";
   }
   $out.= "<tr><td colspan='4'><hr/></td></tr>";
   $out.= "<tr><td align='right'><i>Totali:</i></td>";
   $out.= "<td align='right'>".number_format($outArr['amount'],2,',','.')." &euro;</td>";
   $out.= "<td align='right'>".number_format($outArr['vat'],2,',','.')." &euro;</td>";
   $out.= "<td align='right'>".number_format($outArr['total'],2,',','.')." &euro;</td></tr>";
   $out.= "</table>";
  }
 }
 else
 {
  /* RICAVA I TOTALI X MAGAZZINO */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT store_id,ap,amount,vat,total FROM stock_enhancement WHERE ".($storeId ? "store_id='".$storeId."'" : "1"));
  while($db->Read())
  {
   $outArr['results'][] = array('store_id'=>$db->record['store_id'], 'ap'=>$db->record['ap'],
	'amount'=>$db->record['amount'], 'vat'=>$db->record['vat'], 'total'=>$db->record['total']);
   $outArr['amount']+= $db->record['amount'];
   $outArr['vat']+= $db->record['vat'];
   $outArr['total']+= $db->record['total'];
  }
  $db->Close();  

  if($verbose)
  {
   $out.= "Valorizzazione magazzini\n";
   $out.= "<table cellspacing='0' cellpadding='3' border='0'>";
   $out.= "<tr><th>Magazzino</th><th>Imponibile</th><th>IVA</th><th>Totale</th></tr>";
   for($c=0; $c < count($outArr['results']); $c++)
   {
	$res = $outArr['results'][$c]; 
	if($res['amount'] == 0)
	 continue;
	$out.= "<tr><td>".$_STORES[$res['store_id']]."</td>";
	$out.= "<td align='right'>".number_format($res['amount'],2,',','.')." &euro;</td>";
	$out.= "<td align='right'>".number_format($res['vat'],2,',','.')." &euro;</td>";
	$out.= "<td align='right'>".number_format($res['total'],2,',','.')." &euro;</td></tr>";
   }
   $out.= "<tr><td colspan='4'><hr/></td></tr>";
   $out.= "<tr><td align='right'><i>Totali:</i></td>";
   $out.= "<td align='right'>".number_format($outArr['amount'],2,',','.')." &euro;</td>";
   $out.= "<td align='right'>".number_format($outArr['vat'],2,',','.')." &euro;</td>";
   $out.= "<td align='right'>".number_format($outArr['total'],2,',','.')." &euro;</td></tr>";
   $out.= "</table>";
  }
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_printEnhancementReport($args, $sessid, $shellid)
{
 $out = "";
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;

   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;	// Se non viene specificato -from e -to , verranno mostrati gli ultimi $limit risultati.

   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 $db = new AlpaDatabase();
 if(!$dateFrom && !$dateTo)
 {
  $query = "SELECT * FROM (SELECT * FROM store_movements WHERE ref_ap='".$_AP."' AND ref_id='".$_ID."' ORDER BY op_time DESC LIMIT "
	.($limit ? $limit : 10).") SUB ORDER BY op_time ASC";
 }
 else
 {
  $query = "SELECT * FROM store_movements WHERE ";
  $where = "ref_ap='".$_AP."' AND ref_id='".$_ID."'";

  if($dateFrom)			$where.= " AND op_time>='".$dateFrom."'";
  if($dateTo)			$where.= " AND op_time<'".$dateTo."'";

  $query.= $where." ORDER BY op_time ASC".($limit ? " LIMIT ".$limit : "");
 }

 $db->RunQuery($query);
 if($db->Error) return array('message'=>"MySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
 while($db->Read())
 {
  $outArr[] = array('id'=>$db->record['id'], 'store_id'=>$db->record['store_id'], 'op_time'=>$db->record['op_time'],
	'action'=>$db->record['mov_act'], 'causal'=>$db->record['mov_causal'], 'qty'=>$db->record['qty'], 
	'units'=>$db->record['units'], 'vendor_price'=>$db->record['vendor_unitprice'], 'vatrate'=>$db->record['vendor_vatrate'],
	'amount'=>($db->record['vendor_unitprice'] * $db->record['qty']),
	'stock_qty'=>$db->record['stock_qty'], 'stock_amount'=>$db->record['stock_amount'], 'stock_vat'=>$db->record['stock_vat'], 
	'stock_total'=>$db->record['stock_total']);
 }
 $db->Close();

 if($verbose)
 {
  $out.= "<table cellspacing='0' cellpadding='3' border='0'>";
  $out.= "<tr><th rowspan='2'>Data</th> <th rowspan='2'>Movimento</th> <th rowspan='2'>Quantit&agrave;</th>";
  $out.= "<th colspan='2'>Prezzo unitario</th> <th rowspan='2'>Importi</th></tr>";
  $out.= "<tr><th>di carico</th> <th>di scarico</th></tr>";

  $restAmount = 0;
  $restQty = 0;

  for($c=0; $c < count($outArr); $c++)
  {
   $itm = $outArr[$c];
   if($c == 0)
   {
	$out.= "<tr><td>&nbsp;</td><td style='background:#dadada'>Scorta iniziale</td> <td align='center' style='background:#dadada'>"
	.$itm['stock_qty']."</td> <td>&nbsp;</td> <td>&nbsp;</td> <td align='center' style='background:#dadada'>"
	.number_format($itm['stock_amount'],2,',','.')."</td></tr>";
	$restAmount = $itm['stock_amount'];
	$restQty = $itm['stock_qty'];
   }


   $out.= "<tr><td align='center'>".date('d/m/Y',strtotime($itm['op_time']))."</td>";
   switch($itm['action'])
   {
	case 1 : {
		 $out.= "<td>Acquisto</td> <td align='center'>".$itm['qty']."</td> <td align='center'>"
		 	.number_format($itm['vendor_price'],2,',','.')."</td> <td>&nbsp;</td> <td align='center'>"
		 	.number_format($itm['amount'],2,',','.')."</td>";
		 $restAmount+= $itm['amount'];
		 $restQty+= $itm['qty'];
		} break;

	case 2 : {
		 $out.= "<td>Scarico</td> <td align='center'>- ".$itm['qty']."</td> <td>&nbsp;</td> <td align='center'>"
			.number_format($itm['vendor_price'], 2,',','.')."</td> <td align='center'>- "
			.number_format($itm['amount'],2,',','.')."</td>"; 
		 $restAmount-= $itm['amount'];
		 $restQty-= $itm['qty'];
		} break;

	case 3 : { /* TODO: da fare... */ } break;
   }
   $out.= "</tr>";
   $out.= "<tr><td>&nbsp;</td><td style='background:#dadada'>Scorta</td> <td style='background:#dadada' align='center'>"
		.$restQty."</td> <td>&nbsp;</td> <td>&nbsp;</td> <td style='background:#dadada' align='center'>"
		.number_format($restAmount,2,',','.')."</td></tr>";
  }
  $out.= "</table>";
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_getStockValue($args, $sessid, $shellid, $extraVar=null)
{
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;
   case '-qty' : {$_QTY=$args[$c+1]; $c++;} break;
   case '-vendorid' : {$vendorId=$args[$c+1]; $c++;} break;
   case '-ctime' : {$ctime=strtotime($args[$c+1]); $c++;} break;

   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 if(!$_AP && !$_ID) return array('message'=>"You must specify the archive (-ap ARCHIVE_PREFIX) and the item id (-id ITEM_ID).", 'error'=>"INVALID_ITEM");
 if(!$ctime) $ctime = time();
 if(!$_QTY) $_QTY = 1;

 if(is_array($extraVar) && $extraVar['aboutconfig'])
  $config = $extraVar['aboutconfig'];
 else
 {
  /* GET CONFIG */
  $ret = GShell("aboutconfig get-config -app gstore",$sessid,$shellid);
  if(!$ret['error'])
   $config = $ret['outarr']['config'];
 }

 if(is_array($extraVar) && $extraVar['stores'])
  $_STORES = $extraVar['stores'];
 else
 {
  /* GET STORES */
  $_STORES = array();
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id,name FROM stores WHERE 1");
  while($db->Read()) { $_STORES[$db->record['id']] = $db->record['name']; }
  $db->Close();
 }

 /* GET INFO ABOUT ITEM */
 $vendorName = "";
 $vendorCode = "";
 $vendorPrice = 0;
 $vendorVatRate = 0;
 $db = new AlpaDatabase();

 $query = "SELECT a.id,a.code,a.vendor_id,a.vendor_name,a.price,a.vatrate, b.code_str,b.name FROM dynarc_".$_AP."_vendorprices AS a
	INNER JOIN dynarc_".$_AP."_items AS b
	ON b.id = ".$_ID." 
	WHERE item_id='".$_ID."'"
	.($vendorId ? " AND vendor_id='".$vendorId."'" : " ORDER BY id ASC LIMIT 1");

 $db->RunQuery($query);

 if($db->Read())
 {
  $vendorId = $db->record['vendor_id'];
  $vendorName = $db->record['vendor_name'];
  $vendorCode = $db->record['code'];
  $vendorPrice = $db->record['price'];
  $vendorVatRate = $db->record['vatrate'];
  $itemInfo = array('id'=>$_ID, 'code_str'=>$db->record['code_str'], 'name'=>$db->record['name']);
 }
 else
 {
  $db->RunQuery("SELECT code_str,name FROM dynarc_".$_AP."_items WHERE id='".$_ID."'");
  $db->Read();
  $itemInfo = array('id'=>$_ID, 'code_str'=>$db->record['code_str'], 'name'=>$db->record['name']);
 }
 $db->Close();

 // Ricavo il prezzo d'acquisto dall'ultimo movimento di carico
 if($verbose)
  $out.= "Ricavo il prezzo d&lsquo;acquisto per questo articolo dall&lsquo;ultimo movimento di carico.\n";
 $db = new AlpaDatabase();
 $query = "SELECT op_time,vendor_unitprice,vendor_vatrate FROM store_movements WHERE ref_ap='".$_AP."' AND ref_id='"
	.$_ID."' AND mov_act='1' AND op_time<='".date('Y-m-d H:i:s',$ctime)."' ORDER BY op_time DESC LIMIT 1";
 $db->RunQuery($query);
 if($db->Error)	return array('message'=>$out."failed!\nMySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
 if($db->Read())
 {
  $vendorPrice = $db->record['vendor_unitprice'];
  $vendorVatRate = $db->record['vendor_vatrate'];
  if($verbose)
   $out.= "Ok, il prezzo d&lsquo;acquisto rilevato in data ".date('d/m/Y',strtotime($db->record['op_time']))." &egrave; di: "
	.number_format($vendorPrice,2,',','.')." &euro; IVA esclusa.\n";
 }
 else
  $out.= "Nessun movimento di carico rilevato per questo articolo precedente alla data ".date('d/m/Y',$ctime).", pertanto verrà preso in considerazione il prezzo attuale d'acquisto che &egrave; di: ".number_format($vendorPrice,2,',','.')." &euro; IVA esclusa.\n";
 $db->Close();
 
 $out.= "VendorPrice = ".$vendorPrice."\n";

 /* Determino la scorta iniziale dall'ultimo movimento */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT op_time,mov_act,qty,vendor_unitprice,vendor_vatrate,stock_qty,stock_amount,stock_vat,stock_total FROM store_movements WHERE ref_ap='".$_AP."' AND ref_id='".$_ID."' AND op_time<'".date('Y-m-d H:i:s',$ctime)."' ORDER BY op_time DESC LIMIT 1");
 if($db->Read())
 {
  switch($db->record['mov_act'])
  {
	case 1 : {
		 $stockQty = $db->record['stock_qty'] + $db->record['qty'];
		 $stockAmount = $db->record['vendor_unitprice'] * $db->record['qty'];
		 $stockVat = $stockAmount ? ($stockAmount/100)*$db->record['vendor_vatrate'] : 0;
		 $stockTotal = $stockAmount+$stockVat;

		 $stockAmount+= $db->record['stock_amount'];
		 $stockVat+= $db->record['stock_vat'];
		 $stockTotal+= $db->record['stock_total'];

		 //if($db->record['vendor_unitprice']) 	$vendorPrice = $db->record['vendor_unitprice'];
		 //if($db->record['vendor_vatrate'])		$vendorVatRate = $db->record['vendor_vatrate'];
		} break;
	case 2 : {
		 $stockQty = $db->record['stock_qty'] - $db->record['qty'];
		 $stockAmount = $db->record['vendor_unitprice'] * $db->record['qty'];
		 $stockVat = $stockAmount ? ($stockAmount/100)*$db->record['vendor_vatrate'] : 0;
		 $stockTotal = $stockAmount+$stockVat;

		 $stockAmount = $db->record['stock_amount'] - $stockAmount;
		 $stockVat = $db->record['stock_vat'] - $stockVat;
		 $stockTotal = $db->record['stock_total'] - $stockTotal;

		 //if($db->record['vendor_unitprice']) 	$vendorPrice = $db->record['vendor_unitprice'];
		 //if($db->record['vendor_vatrate'])		$vendorVatRate = $db->record['vendor_vatrate'];
		} break;
  }
 }
 $db->Close();

 /* Determino il valore di scarico */
 switch(strtoupper($config['enhancement']['method']))
 {
  case 'LIFO' : {
	 /* TODO: da fare... */
	} break;

  case 'FIFO' : {
	 /* TODO: da fare... */
	} break;

  default : {
	 $db = new AlpaDatabase();
	 $db->RunQuery("SELECT * FROM dynarc_".$_AP."_stockenhitm WHERE item_id='".$_ID."'");
	 $db->Read();
	 $enhTotQty=0; $enhTotAmount = 0; $enhTotVat=0; $enhTotTotal=0;
	 reset($_STORES);
	 while(list($sid,$v)=each($_STORES))
	 {
	  $enhTotQty+= $db->record['store_'.$sid.'_qty'];
	  $enhTotAmount+= $db->record['store_'.$sid.'_amount'];
	  $enhTotVat+= $db->record['store_'.$sid.'_vat'];
	  $enhTotTotal+= $db->record['store_'.$sid.'_total'];
	 }
	 $cmp = $enhTotQty ? ($enhTotAmount / $enhTotQty) : $vendorPrice;
	 $vendorPrice = $cmp;
	 $enhAmount = $cmp * $_QTY;
  	 $enhVat = $enhAmount ? ($enhAmount/100)*$vendorVatRate : 0;
  	 $enhTotal = $enhAmount+$enhVat;

	 $mov = array('qty'=>$_QTY, 'vendor_price'=>$vendorPrice, 'vendor_vatrate'=>$vendorVatRate, 
		'stock_qty'=>$stockQty, 'stock_amount'=>$stockAmount, 'stock_vat'=>$stockVat, 'stock_total'=>$stockTotal);
	 $outArr[] = $mov;
	} break;
 }

 $out.= "VendorPrice 2 = ".$vendorPrice."\n";

 if($verbose)
 {
  $out.= "Lista dei movimenti per l&lsquo;articolo ".($itemInfo['code_str'] ? $itemInfo['code_str']." - " : '')
	.$itemInfo['name']." con le relative valorizzazioni:\n";

  $out.= "<table border='0' cellspacing='0' cellpadding='3'>";
  $out.= "<tr><th>QTA</th><th>Valore di scarico</th><th>Importi</th></tr>";
  for($c=0; $c < count($outArr); $c++)
  {
   $out.= "<tr><td align='center'>".$outArr[$c]['qty']."</td>";
   $out.= "<td align='center'>".number_format($outArr[$c]['vendor_price'], 2, ',','.')."</td>";
   $out.= "<td align='center'>".number_format($outArr[$c]['vendor_price'] * $outArr[$c]['qty'], 2, ',','.')."</td></tr>";
  }
  $out.= "</table>";
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_reEvaluateStock($args, $sessid, $shellid, $extraVar=null)
{
 /* RIVALUTAZIONE DEL MAGAZZINO */
 $sessInfo = sessionInfo($sessid);
 if(is_array($extraVar))
 {
  if($extraVar['at']) $_AT = $extraVar['at'];
  if($extraVar['ap']) $_AP = $extraVar['ap'];
  if($extraVar['from']) $dateFrom = $extraVar['from'];
 }
 else
 {
  if($sessInfo['uname'] != "root")
   return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");
 }

 $out = "";
 $outArr = array('count'=>0);

 $_RPC = 50; 		// numero di records da valutare per chunk
 $_ARCHIVES = array();

 if(count($args) > 1)
 {
  for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-at' : {$_AT=$args[$c+1]; $c++;} break;		// rivaluta tutti gli articoli di tutti gli archivi del tipo specificato
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;		// rivaluta tutti gli articoli dell'archivio specificato
   /*case '-id' : {$_ID=$args[$c+1]; $c++;} break;		TODO: non si può filtrare x articolo perchè senno è un casino per la tabella stockenhcat */

   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;

   case '-limit' : {$_LIMIT=$args[$c+1]; $c++;} break;
   case '--bypass-preoutput' : $bypassPreOutput=true; break;
  }
 }
 
 if($_AP) $_ARCHIVES[] = $_AP;
 if($_AT)
 {
  // Get archives by type
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE archive_type='".$_AT."' AND trash='0'");
  while($db->Read())
   $_ARCHIVES[] = $db->record['tb_prefix'];
  $db->Close();
 }
 else
 {
  // Get all archive with storeinfo extension installed
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT ext.archive_id,archive.tb_prefix FROM dynarc_archive_extensions AS ext
	INNER JOIN dynarc_archives AS archive
	ON ext.archive_id = archive.id
	WHERE ext.extension_name='storeinfo'");
  while($db->Read()) { $_ARCHIVES[] = $db->record['tb_prefix']; }
  $db->Close();
 }


 /* CONTA QUANTI MOVIMENTI BISOGNA RIVALUTARE */
 $db = new AlpaDatabase();
 $where = "";

 if($_AP)			$where.= " AND ref_ap='".$_AP."'";
 else if($_AT)		$where.= " AND ref_at='".$_AT."'";

 if($dateFrom)		$where.= " AND op_time>='".$dateFrom."'";
 if($dateTo)		$where.= " AND op_time<'".$dateTo."'";

 if(!$where)		$where = "1";

 $query = "SELECT COUNT(*) FROM store_movements WHERE ".ltrim($where,' AND ');
 $db->RunQuery($query);
 $db->Read();
 if($db->Error)		return array('message'=>$out."\nMySQL error: ".$db->Error, 'error'=>'MYSQL_ERROR');

 $_COUNT = $db->record[0];
 $outArr['count'] = $_COUNT;
 $out.= "There are ".$_COUNT." records to be re-evaluate.\n";

 if(!$_COUNT) return array('message'=>$out, 'outarr'=>$outArr);

 /* --- Procedura di rivalutazione dei movimenti di magazzino --- */

 /* GET CONFIG */
 $ret = GShell("aboutconfig get-config -app gstore",$sessid,$shellid);
 if(!$ret['error'])
  $config = $ret['outarr']['config'];

 /* GET STORES */
 $_STORES = array();
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id,name FROM stores WHERE 1");
 while($db->Read()) { $_STORES[$db->record['id']] = $db->record['name']; }
 $db->Close();

 if(!$_LIMIT && ($_COUNT > $_RPC))
  $_PHASES = ceil($_COUNT / $_RPC);
 else
  $_PHASES = 1;

 if(!$bypassPreOutput)
 {
  if($_PHASES > 1)
  {
   $interface = array("name"=>"progressbar","steps"=>$_PHASES);
   gshPreOutput($shellid,"Re-evaluating stock", "ESTIMATION", "", "PASSTHRU", $interface);
  }
 }

 // svuoto la tabella stock_enhancement //
 $db = new AlpaDatabase();
 if(count($_ARCHIVES))
 {
  for($c=0; $c < count($_ARCHIVES); $c++)
   $db->RunQuery("DELETE FROM stock_enhancement WHERE ap='".$_ARCHIVES[$c]."'");
 }
 else
  $db->RunQuery("TRUNCATE TABLE `stock_enhancement`");
 $db->Close();

 // svuoto le tabelle stockenhitm //
 $db = new AlpaDatabase();
 for($c=0; $c < count($_ARCHIVES); $c++)
  $db->RunQuery("TRUNCATE TABLE `dynarc_".$_ARCHIVES[$c]."_stockenhitm`");
 $db->Close();

 // svuoto le tabelle stockenhcat //
 $db = new AlpaDatabase();
 $db->RunQuery("TRUNCATE TABLE `dynarc_".$_ARCHIVES[$c]."_stockenhcat`");
 $db->Close();

 $extraVar = array('aboutconfig'=>$config, 'stores'=>$_STORES);  

 // Ri-valutazione
 $out.= "Re-evaluation in progress...";
 for($ph=0; $ph < $_PHASES; $ph++)
 {
  if(!$bypassPreOutput)
  {
   if($_PHASES > 1)
    gshPreOutput($shellid,"Phase ".($ph+1)." of ".$_PHASES,"PROGRESS");
  }
  if(!$_LIMIT)
   $limit = $ph ? ($ph*$_RPC).",".$_RPC : $_RPC;
  else
   $limit = $_LIMIT;

  $db = new AlpaDatabase();
  $query = "SELECT * FROM store_movements WHERE ".ltrim($where,' AND ')." ORDER BY op_time ASC LIMIT ".$limit;
  $db->RunQuery($query);
  if($db->Error) return array('message'=>$out."failed.\nMySQL Error:".$db->Error, 'error'=>'MYSQL_ERROR');
  while($db->Read())
  {
   switch($db->record['mov_act'])
   {
    case 1 : {
		 $vendorPrice = $db->record['vendor_unitprice'];
		 $vendorVatRate = $db->record['vendor_vatrate'];

		 $cmd = "store get-stock-value -ap '".$db->record['ref_ap']."' -id '".$db->record['ref_id']."' -qty '"
			.$db->record['qty']."' -ctime '".$db->record['op_time']."'".($db->record['ref_vendor_id'] ? " -vendorid '".$db->record['ref_vendor_id']."'" : '');

		 $ret = GShell($cmd,$sessid,$shellid,$extraVar);
		 if($ret['error'])	return array('message'=>$out."failed!\n".$ret['message'], 'error'=>$ret['error']);
		 $stockQty = 0;
		 $stockAmount = 0;
		 $stockVat = 0;
		 $stockTotal = 0;
		 for($c=0; $c < count($ret['outarr']); $c++)
		 {
		  $stockQty+= 		$ret['outarr'][$c]['stock_qty'];
		  $stockAmount+= 	$ret['outarr'][$c]['stock_amount'];
		  $stockVat+= 		$ret['outarr'][$c]['stock_vat'];
		  $stockTotal+= 	$ret['outarr'][$c]['stock_total'];
		 }

		 $db2 = new AlpaDatabase();
		 $db2->RunQuery("UPDATE store_movements SET stock_qty='".$stockQty."',stock_amount='".$stockAmount."',stock_vat='"
			.$stockVat."',stock_total='".$stockTotal."' WHERE id='".$db->record['id']."'");
		 if($db2->Error) return array('message'=>$out."failed.\nMySQL Error:".$db2->Error, 'error'=>'MYSQL_ERROR');
		 $db2->Close();

		} break;

    case 2 : {
		 $cmd = "store get-stock-value -ap '".$db->record['ref_ap']."' -id '".$db->record['ref_id']."' -qty '"
			.$db->record['qty']."' -ctime '".$db->record['op_time']."'".($db->record['ref_vendor_id'] ? " -vendorid '".$db->record['ref_vendor_id']."'" : '');

		 $ret = GShell($cmd,$sessid,$shellid,$extraVar);
		 if($ret['error'])	return array('message'=>$out."failed!\n".$ret['message'], 'error'=>$ret['error']);
		 $stockQty = 0;
		 $stockAmount = 0;
		 $stockVat = 0;
		 $stockTotal = 0;
		 $vendorPrice = 0;
		 $vendorVatRate = 0;
		 for($c=0; $c < count($ret['outarr']); $c++)
		 {
		  $stockQty+= 		$ret['outarr'][$c]['stock_qty'];
		  $stockAmount+= 	$ret['outarr'][$c]['stock_amount'];
		  $stockVat+= 		$ret['outarr'][$c]['stock_vat'];
		  $stockTotal+= 	$ret['outarr'][$c]['stock_total'];
		  $vendorPrice = 	$ret['outarr'][$c]['vendor_price'];
		  $vendorVatRate = 	$ret['outarr'][$c]['vendor_vatrate'];
		 }

		 $db2 = new AlpaDatabase();
		 $db2->RunQuery("UPDATE store_movements SET vendor_unitprice='".$vendorPrice."',vendor_vatrate='".$vendorVatRate."',stock_qty='"
			.$stockQty."',stock_amount='".$stockAmount."',stock_vat='".$stockVat."',stock_total='".$stockTotal."' WHERE id='".$db->record['id']."'");
		 if($db2->Error) return array('message'=>$out."failed.\nMySQL Error:".$db2->Error, 'error'=>'MYSQL_ERROR');
		 $db2->Close();

		} break;

	case 3 : {
		} break;
   }

   // update stock_enhancement
   $qty = $db->record['qty'];
   $enhAmount = $vendorPrice * $qty;
   $enhVat = $enhAmount ? ($enhAmount/100)*$vendorVatRate : 0;
   $enhTotal = $enhAmount+$enhVat;

   $storeId = $db->record['store_id'];
   $ap = $db->record['ref_ap'];
   $id = $db->record['ref_id'];

   $itemInfo = array('id'=>$id, 'ap'=>$ap);
   // get cat id
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT cat_id FROM dynarc_".$ap."_items WHERE id='".$id."'");
   $db2->Read();
   $itemInfo['cat_id'] = $db2->record['cat_id'];

   switch($db->record['mov_act'])
   {
    case 1 : {
	   $db2->RunQuery("SELECT id,amount,vat,total FROM stock_enhancement WHERE store_id='".$storeId."' AND ap='".$ap."'");
	   if($db2->Read())
	    $db2->RunQuery("UPDATE stock_enhancement SET amount='".($db2->record['amount']+$enhAmount)."',vat='"
			.($db2->record['vat']+$enhVat)."',total='".($db2->record['total']+$enhTotal)."' WHERE id='".$db2->record['id']."'");
	   else
	    $db2->RunQuery("INSERT INTO stock_enhancement (store_id,ap,amount,vat,total) VALUES('".$storeId."','".$ap."','".$enhAmount."','"
			.$enhVat."','".$enhTotal."')");

	   $db2->RunQuery("INSERT INTO dynarc_".$ap."_stockenhitm (item_id,store_".$storeId."_qty,store_".$storeId."_amount,store_"
		.$storeId."_vat,store_".$storeId."_total) VALUES('".$id."','".$qty."','".$enhAmount."','".$enhVat."','".$enhTotal."')
ON DUPLICATE KEY UPDATE store_".$storeId."_qty=store_".$storeId."_qty+".$qty.", store_".$storeId."_amount=store_".$storeId."_amount+".$enhAmount.", store_".$storeId."_vat=store_".$storeId."_vat+".$enhVat.", store_".$storeId."_total=store_".$storeId."_total+".$enhTotal);
	   if($db2->Error) return array('message'=>$out."\nMySQL Error: ".$db2->Error, 'error'=>'MYSQL_ERROR');

	   $db2->RunQuery("INSERT INTO dynarc_".$ap."_stockenhcat (cat_id,store_".$storeId."_amount,store_".$storeId."_vat,store_".$storeId."_total) VALUES('"
		.$itemInfo['cat_id']."','".$enhAmount."','".$enhVat."','".$enhTotal."')
ON DUPLICATE KEY UPDATE store_".$storeId."_amount=store_".$storeId."_amount+".$enhAmount.", store_".$storeId."_vat=store_".$storeId."_vat+"
		.$enhVat.", store_".$storeId."_total=store_".$storeId."_total+".$enhTotal);
	   if($db2->Error) return array('message'=>$out."\nMySQL Error: ".$db2->Error, 'error'=>'MYSQL_ERROR');
	   $db2->Close();
	} break;

	case 2 : {
	 // aggiorna valorizzazione magazzini
  	 $db2->RunQuery("SELECT id,amount,vat,total FROM stock_enhancement WHERE store_id='".$storeId."' AND ap='".$ap."'");
  	 if($db2->Read())
   	  $db2->RunQuery("UPDATE stock_enhancement SET amount='".($db2->record['amount']-$enhAmount)."',vat='"
		.($db2->record['vat']-$enhVat)."',total='".($db2->record['total']-$enhTotal)."' WHERE id='".$db2->record['id']."'");

	 // aggiorna valorizzazione per articolo
	 $db2->RunQuery("SELECT store_".$storeId."_qty,store_".$storeId."_amount,store_".$storeId."_vat,store_"
		.$storeId."_total FROM dynarc_".$ap."_stockenhitm WHERE item_id='".$id."'");
	 if($db2->Read())
	  $db2->RunQuery("UPDATE dynarc_".$ap."_stockenhitm SET store_".$storeId."_qty=store_".$storeId."_qty-".$qty.", store_"
		.$storeId."_amount=store_".$storeId."_amount-".$enhAmount.", store_".$storeId."_vat=store_"
		.$storeId."_vat-".$enhVat.", store_".$storeId."_total=store_".$storeId."_total-".$enhTotal." WHERE item_id='".$id."'");

	 // aggiorna valorizzazione per categoria
	 $db2->RunQuery("SELECT store_".$storeId."_amount, store_".$storeId."_vat, store_".$storeId."_total FROM dynarc_"
		.$ap."_stockenhcat WHERE cat_id='".$itemInfo['cat_id']."'");
	 if($db2->Read())
	  $db2->RunQuery("UPDATE dynarc_".$ap."_stockenhcat SET store_".$storeId."_amount=store_".$storeId."_amount-"
		.$enhAmount.", store_".$storeId."_vat=store_".$storeId."_vat-".$enhVat.", store_".$storeId."_total=store_"
		.$storeId."_total-".$enhTotal." WHERE cat_id='".$itemInfo['cat_id']."'");
	} break;
   }
   $db2->Close();
  }
  $db->Close();
 }

 $out.= "done!";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_resetStockEnhancement($args, $sessid, $shellid)
{
 // Rivalutazione del magazzino in base alle quantita (giac. fisica) degli articoli ed al prezzo d'acquisto attuale.
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
  }

 $STORE = new GStore($sessid, $shellid);
 $ret = $STORE->ResetStockEnhancement($_AP);
 if(!$ret) return array('message'=>$STORE->debug, 'error'=>$STORE->error);

 return array('message'=>$STORE->debug); 
}
//-------------------------------------------------------------------------------------------------------------------//
function store_reset($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";
 $_STORES = array();
 $_ARCHIVES = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-at' : {$_AT=$args[$c+1]; $c++;} break;
  }

 if($_AP) $_ARCHIVES[] = $_AP;
 if($_AT)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE archive_type='".$_AT."' AND trash='0'");
  while($db->Read())
   $_ARCHIVES[] = $db->record['tb_prefix'];
  $db->Close();
 }
 else
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT ext.archive_id,archive.tb_prefix FROM dynarc_archive_extensions AS ext
	INNER JOIN dynarc_archives AS archive
	ON ext.archive_id = archive.id
	WHERE ext.extension_name='storeinfo'");
  while($db->Read())
  {
   $_ARCHIVES[] = $db->record['tb_prefix'];
  }
  $db->Close();
 }

 // get all stores
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM stores WHERE 1 ORDER BY id ASC");
 while($db->Read())
  $_STORES[] = $db->record['id'];

 // Empty table store_movements
 $out.= "Empty table store movements...";
 if($_AP)			$db->RunQuery("DELETE FROM store_movements WHERE ref_ap='".$_AP."'");
 else if($_AT)		$db->RunQuery("DELETE FROM store_movements WHERE ref_at='".$_AT."'");
 else 				$db->RunQuery("TRUNCATE TABLE `store_movements`");
 if($db->Error) return array('message'=>$out."failed.\nMySQL Error:".$db->Error, 'error'=>'MYSQL_ERROR');
 $out.= "done!\n";

 // Reset qty
 $out.= "Reset qty...";
 $resetStoreQry = "storeqty='0',booked='0',incoming='0',loaded='0',downloaded='0'";
 for($c=0; $c < count($_STORES); $c++)
  $resetStoreQry.= ",store_".$_STORES[$c]."_qty='0'";
 if($_AP)			$db->RunQuery("UPDATE dynarc_".$_AP."_items SET ".$resetStoreQry." WHERE 1");
 else
 {
  for($c=0; $c < count($_ARCHIVES); $c++)
   $db->RunQuery("UPDATE dynarc_".$_ARCHIVES[$c]."_items SET ".$resetStoreQry." WHERE 1");
 }
 if($db->Error) return array('message'=>$out."failed.\nMySQL Error:".$db->Error, 'error'=>'MYSQL_ERROR');
 $out.= "done!\n";
 $db->Close();

 // Reset store enhancement //
 $out.= "Reset store enhancement...";
 $db = new AlpaDatabase();
 if($_AP)
 {
  $db->RunQuery("TRUNCATE TABLE `dynarc_".$_AP."_stockenhcat`");
  $db->RunQuery("TRUNCATE TABLE `dynarc_".$_AP."_stockenhitm`");
 }
 else
 {
  for($c=0; $c < count($_ARCHIVES); $c++)
  {
   $db->RunQuery("TRUNCATE TABLE `dynarc_".$_ARCHIVES[$c]."_stockenhcat`");
   $db->RunQuery("TRUNCATE TABLE `dynarc_".$_ARCHIVES[$c]."_stockenhitm`");
  }
 }
 $out.= "done!\n";
 $db->Close();

 if(!$_AP && !$_AT)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("TRUNCATE TABLE `stock_enhancement`");
  $db->Close();
 }
 
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_showInStore($args, $sessid, $shellid)
{
 $out = "";
 $_IDS = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ids' : {$_IDS=$args[$c+1]; $c++;} break;
  }

 if(!$_IDS) return array('message'=>"You must specify at least one article.", 'error'=>"NO_ITEM_SPECIFIED");

 $x = explode(",",$_IDS);

 $db = new AlpaDatabase();
 for($c=0; $c < count($x); $c++)
 {
  $xx = explode(":",$x[$c]);
  $_AP = $xx[0];
  $_ID = $xx[1];

  if($_AP && $_ID)
   $db->RunQuery("UPDATE dynarc_".$_AP."_items SET hide_in_store='0' WHERE id='".$_ID."'");
 }
 $db->Close();

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_hideInStore($args, $sessid, $shellid)
{
 $out = "";
 $_IDS = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ids' : {$_IDS=$args[$c+1]; $c++;} break;
  }

 if(!$_IDS) return array('message'=>"You must specify at least one article.", 'error'=>"NO_ITEM_SPECIFIED");

 $x = explode(",",$_IDS);

 $db = new AlpaDatabase();
 for($c=0; $c < count($x); $c++)
 {
  $xx = explode(":",$x[$c]);
  $_AP = $xx[0];
  $_ID = $xx[1];

  if($_AP && $_ID)
   $db->RunQuery("UPDATE dynarc_".$_AP."_items SET hide_in_store='1' WHERE id='".$_ID."'");
 }
 $db->Close();

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_exportToExcel($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES, $_EXTRA_COLUMNS;
 require_once($_BASE_PATH."var/lib/excel.php");

 $out = "";
 $outArr = array();

 $_AT = "gmart";
 $_AP = "";
 $_CATID = 0;
 $_ARCHIVES = array();
 $_STORES = array();

 $orderBy = "name ASC";
 $limit = 0;

 $sheetName = "untitled";
 $letters = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
	"AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ");

 $fileName = "";
 $_FILE_PATH = "";

 $_WHERE = "trash='0' AND hide_in_store='0'";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-at' : {$_AT=$args[$c+1]; $c++;} break;
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-cat' : {$_CATID=$args[$c+1]; $c++;} break;

   case '-store' : case '-storeid' : {$storeId=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;

   case '-f' : case '-file' : {$fileName=$args[$c+1]; $c++;} break;
   case '-s' : case '-sheet' : {$sheetName=substr($args[$c+1],0,32); $c++;} break;

   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;

   case '--soldout' : $onlySoldOut=true; break;		// filtra solo gli articoli esauriti
   case '--ums' : $onlyUMS=true; break;				// filtra solo gli articoli in esaurimento
   case '--get-stock-enhancement' : $getStockEnhancement=true; break; // ricava la valorizzazione
   case '--include-extra-columns' : $includeExtraColumns=true; break; // include colonne extra
  }

 if(!$fileName)
  return array('message'=>"You must specify the file name. (with: -file FILE_NAME)",'error'=>"INVALID_FILE");
 $pi = pathinfo($fileName);
 if(!$pi['extension'])
  $fileName.= ".xlsx";
 if(!$sheetName)
  $sheetName = "Foglio 1";
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] == "root")
  $_FILE_PATH = "tmp/";
 else if($sessInfo['uid'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $_FILE_PATH = $_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
  $_FILE_PATH = "tmp/";


 if(!$storeId)
 {
  // get store list
  $ret = GShell("store list",$sessid,$shellid);
  if($ret['error']) return $ret;
  $_STORES = $ret['outarr'];
 }
 else
 {
  $ret = GShell("store info -id '".$storeId."'", $sessid, $shellid);
  if($ret['error']) return $ret;
  $_STORES[] = $ret['outarr'];
  $_WHERE.= " AND i.store_".$ret['outarr']['id']."_qty>0";
 }

 if(!$_AP)
 {
  $_CATID = 0;
  $ret = GShell("dynarc archive-list -a -type `".$_AT."`",$sessid,$shellid);
  if($ret['error']) return array('message'=>"Excel export failed!\n".$ret['message'], 'error'=>$ret['error']);
  for($c=0; $c < count($ret['outarr']); $c++)
   $_ARCHIVES[] = $ret['outarr'][$c];
 }
 else
 {
  $ret = GShell("dynarc archive-info -ap '".$_AP."'",$sessid,$shellid);
  if($ret['error']) return array('message'=>"Excel export failed!\n".$ret['message'], 'error'=>$ret['error']);
  $_ARCHIVES[] = $ret['outarr'];

  if($_CATID)
  {
   // get cat info
   $ret = GShell("dynarc cat-info -ap '".$_AP."' -id '".$_CATID."'",$sessid,$shellid);
   if($ret['error']) return array('message'=>"Excel export failed!\n".$ret['message'], 'error'=>$ret['error']);
   $_WHERE.= " AND i.cat_id='".$_CATID."'";
  }
 }

 if($onlySoldOut)	$_WHERE.= " AND i.storeqty<=0";
 else if($onlyUMS)	$_WHERE.= " AND (i.minimum_stock>0 AND i.storeqty<=i.minimum_stock)";

 // PREPARE FIELDS
 $_EXCEL_FIELDS = array(
	 0=> array('name'=>'code_str', 'title'=>'CODICE', 'format'=>'string'),
	 1=> array('name'=>'name', 'title'=>'DESCRIZIONE', 'format'=>'string'),
	 2=> array('name'=>'minimum_stock', 'title'=>'SCORTA MIN.', 'format'=>'string', 'showtotal'=>true, 'total'=>0),
	 3=> array('name'=>'storeqty', 'title'=>'GIAC. FISICA', 'format'=>'number', 'showtotal'=>true, 'total'=>0),
	 4=> array('name'=>'vendor_price', 'title'=>'PR. ACQ.', 'format'=>'currency', 'notdbfield'=>true),
	 5=> array('name'=>'stock_value', 'title'=>'VAL. MERCE', 'format'=>'currency', 'notdbfield'=>true, 'showtotal'=>true, 'total'=>0),
	 6=> array('name'=>'booked', 'title'=>'PRENOTATI', 'format'=>'number', 'showtotal'=>true, 'total'=>0),
	 7=> array('name'=>'incoming', 'title'=>'ORDINATI', 'format'=>'number', 'showtotal'=>true, 'total'=>0),
	 8=> array('name'=>'available', 'title'=>'DISPONIBILI', 'format'=>'number', 'showtotal'=>true, 'total'=>0, 'notdbfield'=>true)
	);

 if($includeExtraColumns)
 {
  if(file_exists($_BASE_PATH."Store2/config-custom.php"))
  {
   include($_BASE_PATH."Store2/config-custom.php");
   if(is_array($_EXTRA_COLUMNS) && count($_EXTRA_COLUMNS))
   {
	$db = new AlpaDatabase();
	for($j=0; $j < count($_EXTRA_COLUMNS); $j++)
	{
	 $extraColConfig = $_EXTRA_COLUMNS[$j];
  	 if($extraColConfig['extension'])
  	 {
   	  $db->RunQuery("SELECT ext.id FROM dynarc_archives AS arc INNER JOIN dynarc_archive_extensions AS ext ON ext.archive_id=arc.id AND ext.extension_name='".$extraColConfig['extension']."' WHERE arc.tb_prefix='".($_AP ? $_AP : $_AT)."'");
   	  if(!$db->Read())
	   continue;
  	 }

	 $list = $extraColConfig['columns'] ? $extraColConfig['columns'] : $extraColConfig;
	
	 reset($list);
	 while(list($k,$v) = each($list))
	 {
	  $f = array('name'=>($v['dbfield'] ? $v['dbfield'] : $k), 'title'=>$v['title'], 'format'=>($v['format'] ? $v['format'] : 'string'));
	  if(($f['format'] == "number") || ($f['format'] == "currency"))
	  {
	   $f['showtotal'] = true;
	   $f['total'] = 0;
	  }
	  $_EXCEL_FIELDS[] = $f;
	 }
	}
	$db->Close();
   }
  } 
 }


 // make query
 //$_FIELDS = "id,code_str,name,minimum_stock,storeqty,booked,incoming";
 $_FIELDS = "id";
 for($c=0; $c < count($_EXCEL_FIELDS); $c++)
 {
  $xf = $_EXCEL_FIELDS[$c];
  if($xf['notdbfield']) continue;
  $_FIELDS.= ",".$xf['name'];
 }
 $_F = explode(",",$_FIELDS);
 if($where) $_WHERE.= " AND (".$where.")";
 $qry = "";
 for($c=0; $c < count($_ARCHIVES); $c++)
 {
  $ap = $_ARCHIVES[$c]['prefix'];
  $qry.= " UNION SELECT '".$ap."' AS tb_prefix";
  for($i=0; $i < count($_F); $i++)
   $qry.= ",i.".$_F[$i];

  if($storeId) $qry.= ",i.store_".$storeId."_qty";

  if($getStockEnhancement)
  {
   for($i=0; $i < count($_STORES); $i++)
    $qry.= ",s.store_".$_STORES[$i]['id']."_amount,s.store_".$_STORES[$i]['id']."_vat,s.store_".$_STORES[$i]['id']."_total";
   $qry.= " FROM dynarc_".$ap."_items AS i LEFT JOIN dynarc_".$ap."_stockenhitm AS s ON s.item_id=i.id WHERE ".$_WHERE;
  }
  else
   $qry.= " FROM dynarc_".$ap."_items AS i WHERE ".$_WHERE;
 }
 $_QRY = "SELECT * FROM (".ltrim($qry, " UNION ").") AS qryelements ORDER BY ".$orderBy;
 if($limit) $_QRY.= " LIMIT ".$limit;

 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db->RunQuery($_QRY);
 if($db->Error) return array('message'=>"MySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');

 /* GENERATE EXCEL FILE */
 PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );
 $objPHPExcel = new PHPExcel();
 $sheet = $objPHPExcel->setActiveSheetIndex(0);
 $objPHPExcel->getActiveSheet()->setTitle($sheetName);
 $rowIdx = 1;

 for($c=0; $c < count($_EXCEL_FIELDS); $c++)
  $sheet->setCellValueByColumnAndRow($c, $rowIdx, $_EXCEL_FIELDS[$c]['title']);

 if($getStockEnhancement)
 {
  $sheet->setCellValueByColumnAndRow($c, $rowIdx, "VALORIZZ. IMP.");
  $sheet->setCellValueByColumnAndRow($c+1, $rowIdx, "VALORIZZ. IVA");
  $sheet->setCellValueByColumnAndRow($c+2, $rowIdx, "VALORIZZ. TOT.");
 }

 $rowIdx++;
 $enhAmount = 0;
 $enhVat = 0;
 $enhTotal = 0;
 while($db->Read())
 {
  $data = $db->record;
  /* Get other info about product */
  $db2->RunQuery("SELECT price FROM dynarc_".$data['tb_prefix']."_vendorprices WHERE item_id='".$data['id']."' LIMIT 1");
  if($db2->Error) { $db2->Close(); $db2 = new AlpaDatabase(); }
  else if($db2->Read())
   $data['vendor_price'] = $db2->record['price'];
  else
   $data['vendor_price'] = 0;
  $data['stock_value'] = $data['storeqty'] * $data['vendor_price'];

  $ret = store_exportToExcelSingleElement($data, $sheet, $_EXCEL_FIELDS, $_STORES, $getStockEnhancement, $rowIdx);
  for($c=0; $c < count($_EXCEL_FIELDS); $c++)
  {
   $f = $_EXCEL_FIELDS[$c];
   if(!$f['showtotal']) continue;
   $_EXCEL_FIELDS[$c]['total']+= $ret[$f['name']];
  }
  if($getStockEnhancement)
  {
   $enhAmount+= $ret['enh_amount'];
   $enhVat+= $ret['enh_vat'];
   $enhTotal+= $ret['enh_total'];
  }
  $rowIdx++;
 }
 $db2->Close();
 $db->Close();

 // show totals
 $rowIdx+= 1;
 $colIdx=0;
 $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
 for($c=0; $c < count($_EXCEL_FIELDS); $c++)
 {
  $f = $_EXCEL_FIELDS[$c];
  if(!$f['showtotal'])
  {
   $colIdx++;
   continue;
  }
  $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, $f['title']);
  $sheet->setCellValueExplicitByColumnAndRow($colIdx, $rowIdx+1, $f['total'], $dataType);
  switch($f['format'])
  {
   case 'currency' : {
	 $sheet->getStyleByColumnAndRow($colIdx, $rowIdx+1)->getNumberFormat()->setFormatCode("€ #,##0.00");
	} break;
  }
  $colIdx++;
 }

 if($getStockEnhancement)
 {
  $formatCode = "€ #,##0.00";
  $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, "VALORIZZ. IMP.");
  $sheet->setCellValueExplicitByColumnAndRow($colIdx, $rowIdx+1, $enhAmount, $dataType);
  $sheet->getStyleByColumnAndRow($colIdx, $rowIdx+1)->getNumberFormat()->setFormatCode($formatCode);

  $sheet->setCellValueByColumnAndRow($colIdx+1, $rowIdx, "VALORIZZ. IVA");
  $sheet->setCellValueExplicitByColumnAndRow($colIdx+1, $rowIdx+1, $enhVat, $dataType);
  $sheet->getStyleByColumnAndRow($colIdx+1, $rowIdx+1)->getNumberFormat()->setFormatCode($formatCode);

  $sheet->setCellValueByColumnAndRow($colIdx+2, $rowIdx, "VALORIZZ. TOT.");
  $sheet->setCellValueExplicitByColumnAndRow($colIdx+2, $rowIdx+1, $enhTotal, $dataType);
  $sheet->getStyleByColumnAndRow($colIdx+2, $rowIdx+1)->getNumberFormat()->setFormatCode($formatCode);
 }
  
 $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
 $objWriter->save($_BASE_PATH.$_FILE_PATH.ltrim($fileName,"/"));

 $out = "done!\nExcel file: ".$fileName;
 $outArr = array('filename'=>$fileName, "fullpath"=>$_FILE_PATH.ltrim($fileName,"/"));
 
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function store_exportToExcelSingleElement($data, $sheet, $fields, $_STORES, $getStockEnhancement, $rowIdx)
{
 $ret = array();
 for($c=0; $c < count($fields); $c++)
 {
  $colIdx = $c;
  $field = $fields[$c];
  if((count($_STORES) == 1) && ($field['name'] == 'storeqty'))
   $value = $data['store_'.$_STORES[0]['id'].'_qty']; //se viene selezionato un magazzino ricava solo le qta di quel magazzino
  else if($field['notdbfield'])
  {
   switch($field['name'])
   {
    case 'available' : {
		 $sq = $data['storeqty'] > 0 ? $data['storeqty'] : 0; // qta di tutti i magazzini, anche se viene selezionato un magazz. specifico.
		 $b = $data['booked'] > 0 ? $data['booked'] : 0;
		 $i = $data['incoming'] > 0 ? $data['incoming'] : 0;
		 $value = ($sq+$i)-$b;
		} break;

    case 'vendor_price' : $value = $data['vendor_price']; break;
    case 'stock_value' :  $value = $data['stock_value']; break;
   }
  }
  else
   $value = $data[$field['name']];
  $dataType = "";
  $formatCode = "";

  if($field['showtotal'])
   $ret[$field['name']] = $value;

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

	default : {
		 $dataType = PHPExcel_Cell_DataType::TYPE_STRING;
		 $value = html_entity_decode($value,ENT_QUOTES,'UTF-8');
		} break;

  }

  if($dataType)
   $sheet->setCellValueExplicitByColumnAndRow($colIdx, $rowIdx, $value, $dataType);
  else
   $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, $value);
  if($formatCode)
   $sheet->getStyleByColumnAndRow($colIdx, $rowIdx)->getNumberFormat()->setFormatCode($formatCode);

 }

 if($getStockEnhancement)
 {
  $enhAmount = 0;
  $enhVat = 0;
  $enhTotal = 0;

  for($c=0; $c < count($_STORES); $c++)
  {
   $sid = $_STORES[$c]['id'];
   if($data['store_'.$sid.'_amount'])	$enhAmount+= $data['store_'.$sid.'_amount'];
   if($data['store_'.$sid.'_vat'])		$enhVat+= $data['store_'.$sid.'_vat'];
   if($data['store_'.$sid.'_total'])		$enhTotal+= $data['store_'.$sid.'_total'];
  }

  $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
  $formatCode = "€ #,##0.00";

  $colIdx = count($fields);
  $sheet->setCellValueExplicitByColumnAndRow($colIdx, $rowIdx, $enhAmount, $dataType);
  $sheet->getStyleByColumnAndRow($colIdx, $rowIdx)->getNumberFormat()->setFormatCode($formatCode);
  $colIdx++;
  $sheet->setCellValueExplicitByColumnAndRow($colIdx, $rowIdx, $enhVat, $dataType);
  $sheet->getStyleByColumnAndRow($colIdx, $rowIdx)->getNumberFormat()->setFormatCode($formatCode);
  $colIdx++;
  $sheet->setCellValueExplicitByColumnAndRow($colIdx, $rowIdx, $enhTotal, $dataType);
  $sheet->getStyleByColumnAndRow($colIdx, $rowIdx)->getNumberFormat()->setFormatCode($formatCode);

  $ret['enh_amount'] = $enhAmount;
  $ret['enh_vat'] = $enhVat;
  $ret['enh_total'] = $enhTotal;
 }

 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
//--- C L A S S  - G S T O R E --------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
class GStore
{
 var $debug, $error;
 private $sessid, $shellid, $eventListeners, $aboutconfig;
 private $storelist, $storeById, $archiveByType, $archiveById, $archiveByPrefix, $pricelists, $pricelistById;

 function GStore($sessid=0, $shellid=0)
 {
  $this->sessid = $sessid;
  $this->shellid = $shellid;
  $this->eventListeners = null;
  $this->aboutconfig = null;
  $this->debug = "";
  $this->error = "";
  $this->storelist = null;
  $this->storeById = array();
  $this->archiveByType = array();
  $this->archiveById = array();
  $this->archiveByPrefix = array();
  $this->pricelists = null;
  $this->pricelistById = array();
 }
 //------------------------------------------------------------------------------------------------------------------//
 function GetConfig()
 {
  if($this->aboutconfig) return $this->aboutconfig;
  $ret = GShell("aboutconfig get-config -app gstore", $this->sessid, $this->shellid);
  if(!$ret['error']) $this->aboutconfig = $ret['outarr']['config'];
  return $this->aboutconfig ? $this->aboutconfig : array();
 }
 //------------------------------------------------------------------------------------------------------------------//
 function GetStoreList()
 {
  if($this->storelist) return $this->storelist;
  $this->debug.= "Get store list...";
  $ret = GShell("store list", $this->sessid, $this->shellid);
  if($ret['error']) return $this->returnError("failed!\nGStore Error: Unable to get store list.\n".$ret['message'], $ret['error']);
  $this->debug.= "done!\n";

  $this->storelist = $ret['outarr'];
  for($c=0; $c < count($this->storelist); $c++)
   $this->storeById[$this->storelist[$c]['id']] = $this->storelist[$c];

  return $this->storelist;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function GetStoreById($id)
 {
  if($this->storeById[$id]) return $this->storeById[$id];
  $this->debug.= "Get store info by id #".$id."...";
  $ret = GShell("store info -id '".$id."'", $this->sessid, $this->shellid);
  if($ret['error']) return $this->returnError("failed!\nGStore Error: Unable to get store info.\n".$ret['message'], $ret['error']);
  $this->debug.= "done!\n";

  if(!$this->storelist) $this->storelist = array();
  $this->storelist[] = $ret['outarr'];
  $this->storeById[$id] = $ret['outarr'];

  return $ret['outarr'];
 }
 //------------------------------------------------------------------------------------------------------------------//
 function GetArchiveList($archiveType)
 {
  if($this->archiveByType[$archiveType]) return $this->archiveByType[$archiveType];
  $this->archiveByType[$archiveType] = array();

  $this->debug.= "Get archive list by type '".$archiveType."'...";
  $ret = GShell("dynarc archive-list -a -type '".$archiveType."'", $this->sessid, $this->shellid);
  if($ret['error']) return $this->returnError("failed!\nGStore Error: Unable to get archive list.\n".$ret['message'], $ret['error']);
  $this->debug.= "done!\n";
  $list = $ret['outarr'];

  for($c=0; $c < count($list); $c++)
  {
   $this->archiveByType[$archiveType][] = $list[$c];
   $this->archiveById[$list[$c]['id']] = $list[$c];
   $this->archiveByPrefix[$list[$c]['prefix']] = $list[$c];
  }

  return $this->archiveByType[$archiveType];
 }
 //------------------------------------------------------------------------------------------------------------------//
 function GetArchiveInfo($apORid)
 {
  $_AP = ""; $_ID=0;
  if(is_numeric($apORid)) $_ID = $apORid;
  else $_AP = $apORid;

  if($_AP) { if($this->archiveByPrefix[$_AP]) return $this->archiveByPrefix[$_AP]; }
  else { if($this->archiveById[$_ID]) return $this->archiveById[$_ID]; }

  $this->debug.= "Get archive info by ".($_AP ? "prefix '".$_AP."'" : "id #".$_ID)."...";
  $ret = GShell("dynarc archive-info".($_AP ? " -ap '".$_AP."'" : " -id '".$_ID."'"), $this->sessid, $this->shellid);
  if($ret['error']) return $this->returnError("failed!\nGStore Error: Unable to get archive info.\n".$ret['message'], $ret['error']);
  $this->debug.= "done!\n";

  $this->archiveById[$_ID] = $ret['outarr'];
  $this->archiveByPrefix[$_AP] = $ret['outarr'];
  if(!$this->archiveByType[$ret['outarr']['type']])
   $this->archiveByType[$ret['outarr']['type']] = array();
  $this->archiveByType[$ret['outarr']['type']][] = $ret['outarr'];

  return $ret['outarr'];
 }
 //------------------------------------------------------------------------------------------------------------------//
 function GetPricelists()
 {
  if($this->pricelists) return $this->pricelists;
  $this->debug.= "Get pricelists...";
  $ret = GShell("pricelists list",$this->sessid, $this->shellid);
  if($ret['error']) return $this->returnError("failed!\n".$ret['message'], $ret['error']);
  $this->pricelists = $ret['outarr'];

  for($c=0; $c < count($ret['outarr']); $c++)
   $this->pricelistById[$ret['outarr'][$c]['id']] = $ret['outarr'][$c];

  return $this->pricelists;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function GetPricelistById($plid)
 {
  if($this->pricelistById[$plid]) return $this->pricelistById[$plid];
  $this->GetPricelists();
  return $this->pricelistById[$plid];
 }
 //------------------------------------------------------------------------------------------------------------------//
 function GetProductList($where="", $options=array())
 {
  $_AT = $options['at'] ? $options['at'] : 'gmart';
  $_AP = $options['ap'] ? $options['ap'] : '';
  $_ID = $options['id'] ? $options['id'] : 0;
  $_CAT_ID = $options['cat'] ? $options['cat'] : 0;
  $_CAT_TAG = $options['ct'] ? $options['ct'] : "";
  $_STORE_ID = $options['store'] ? $options['store'] : 0;
  $_WHERE = "";
  $_WHERE2 = "";
  $_ORDER_BY = $options['orderby'] ? $options['orderby'] : "name ASC";
  $_LIMIT = $options['limit'] ? $options['limit'] : 0;
  $_GET_STOCK_ENH = $options['getstockenh'] ? true : false;
  $_GET_VARIANTS = $options['getvariants'] ? true : false;
  /*$_FIND = $options['find'] ? $options['find'] : "";
  $_FIND_FIELDS = $options['findfields'] ? $options['findfield'] : "";*/
  $_GETF = $options['get'] ? $options['get'] : "";

  $ret = array('count'=>0, 'items'=>array());

  /* PREPARE FIELDS */
  $_FIELDS = "id,uid,gid,_mod,cat_id,name,code_num,code_str,brand,model,manufacturer_code,qty_sold,units,
	weight,weightunits,storeqty,booked,incoming,loaded,downloaded,item_location,minimum_stock";
  if($_GETF) $_FIELDS.= ",".$_GETF;

  $stores = $this->GetStoreList();
  $varStoreQ = "";	// for option get variants
  if(!$stores) return $this->returnError();
  for($c=0; $c < count($stores); $c++)
  {
   $_FIELDS.= ",store_".$stores[$c]['id']."_qty";
   $varStoreQ.= ",store_".$stores[$c]['id']."_qty";
  }

  $archives = $this->GetArchiveList($_AT);
  if(!$archives) return $this->returnError();

  $db = new AlpaDatabase();
  if($_GET_VARIANTS) $db2 = new AlpaDatabase();

  $_WHERE = "trash='0'";
  if($_STORE_ID)	$_WHERE.= " AND store_".$_STORE_ID."_qty>0";
  /*if($_FIND)
  {
   $find = $db->Purify($_FIND);
   $_WHERE.= " AND (name='".$find."' OR name LIKE '%".$find."' OR name LIKE '%".$find."%' OR name LIKE '".$find."%')";
  }*/
  if($where)		$_WHERE.= " AND (".$where.")";

  if($_GET_STOCK_ENH)
  {
   $_WHERE2 = $_WHERE;
   for($c=0; $c < count($stores); $c++)
    $_WHERE2 = str_replace("store_".$stores[$c]['id']."_qty", "i.store_".$stores[$c]['id']."_qty", $_WHERE2);
  }


  /* PREPARE QUERY */
  $qry = "";
  $countQry = "";
  $storeAmountSum = "";
  $storeVatSum = "";
  $storeTotSum = "";

  if(!$_STORE_ID)
  {
   for($c=0; $c < count($stores); $c++)
   {
    $storeAmountSum.= "+stock.store_".$stores[$c]['id']."_amount";
    $storeVatSum.= "+stock.store_".$stores[$c]['id']."_vat";
    $storeTotSum.= "+stock.store_".$stores[$c]['id']."_total";
   }
   $storeAmountSum = substr($storeAmountSum,1);
   $storeVatSum = substr($storeVatSum,1);
   $storeTotSum = substr($storeTotSum,1);
  }

  if($_AP)
  {
   if($_CAT_ID)
   {
	$_WHERE.= " AND cat_id='".$_CAT_ID."'";
	if($_WHERE2)	$_WHERE2.= " AND i.cat_id='".$_CAT_ID."'";
   }

   // get if all fields exists into table
   $table = "dynarc_".$_AP."_items";
   $tbFields = $db->FieldsInfo($table);
   $x = explode(",",$_FIELDS);
   $_ADJFIELDS = "";
   for($c=0; $c < count($x); $c++)
   {
    if($tbFields[$x[$c]])
	 $_ADJFIELDS.= ",".$x[$c];
   }
   if(!$_ADJFIELDS) return $this->returnError("Store get-product-list failed! No fields found into table ".$table, 'NO_FIELDS_FOUND');
   $_ADJFIELDS = ltrim($_ADJFIELDS, ",");

   if($_GET_STOCK_ENH)
   {
    $qry = "SELECT '".$_AP."' AS tb_prefix,i.".str_replace(",",",i.",$_ADJFIELDS).", ";
    if($_STORE_ID)
     $qry.= "stock.store_".$_STORE_ID."_amount AS enh_amount, stock.store_".$_STORE_ID."_vat AS enh_vat, stock.store_".$_STORE_ID."_total AS enh_total";
    else
	 $qry.= $storeAmountSum." AS enh_amount, ".$storeVatSum." AS enh_vat, ".$storeTotSum." AS enh_total";

    $qry.= " FROM dynarc_".$_AP."_items AS i LEFT JOIN dynarc_".$_AP."_stockenhitm AS stock ON stock.item_id = i.id WHERE "
	 .$_WHERE2." AND hide_in_store='0'";
   }
   else
	$qry = "SELECT '".$_AP."' AS tb_prefix,".$_ADJFIELDS." FROM dynarc_".$_AP."_items WHERE ".$_WHERE." AND hide_in_store='0'";

   $countQry = "SELECT '".$_AP."' AS tb_prefix,id FROM dynarc_".$_AP."_items WHERE ".$_WHERE." AND hide_in_store='0'";
   $hiddenCountQry = "SELECT '".$_AP."' AS tb_prefix,id FROM dynarc_".$_AP."_items WHERE ".$_WHERE." AND hide_in_store='1'";
  }
  else
  {
   for($c=0; $c < count($archives); $c++)
   {
    $ap = $archives[$c]['prefix'];
    // get if all fields exists into table
    $table = "dynarc_".$ap."_items";
    $tbFields = $db->FieldsInfo($table);
    $x = explode(",",$_FIELDS);
    $_ADJFIELDS = "";
    for($i=0; $i < count($x); $i++)
    {
     if($tbFields[$x[$i]])
	  $_ADJFIELDS.= ",".$x[$i];
    }
    if(!$_ADJFIELDS) return $this->returnError("Store get-product-list failed! No fields found into table ".$table, 'NO_FIELDS_FOUND');
    $_ADJFIELDS = ltrim($_ADJFIELDS, ",");

    if($_GET_STOCK_ENH)
	{
     $qry.= " UNION SELECT '".$ap."' AS tb_prefix,i.".str_replace(",",",i.",$_ADJFIELDS).", ";
     if($_STORE_ID)
      $qry.= "stock.store_".$_STORE_ID."_amount AS enh_amount, stock.store_".$_STORE_ID."_vat AS enh_vat, stock.store_".$_STORE_ID."_total AS enh_total";
     else
	  $qry.= $storeAmountSum." AS enh_amount, ".$storeVatSum." AS enh_vat, ".$storeTotSum." AS enh_total";

     $qry.= " FROM dynarc_".$ap."_items AS i LEFT JOIN dynarc_".$ap."_stockenhitm AS stock ON stock.item_id = i.id WHERE "
	  .$_WHERE2." AND hide_in_store='0'";
	}
	else
	 $qry.= " UNION SELECT '".$ap."' AS tb_prefix,".$_ADJFIELDS." FROM dynarc_".$ap."_items WHERE ".$_WHERE." AND hide_in_store='0'";

    $countQry.= " UNION SELECT '".$ap."' AS tb_prefix,id FROM dynarc_".$ap."_items WHERE ".$_WHERE." AND hide_in_store='0'";
	$hiddenCountQry.= " UNION SELECT '".$ap."' AS tb_prefix,id FROM dynarc_".$ap."_items WHERE ".$_WHERE." AND hide_in_store='1'";
   }
  }

  $_FIELD_LIST = explode(",",$_FIELDS);

  /* GET COUNT */
  $this->debug.= "Get count...";
  $db->RunQuery("SELECT COUNT(*) FROM (".ltrim($countQry, " UNION ").") AS qryelements");
  if($db->Error) return $this->returnError("failed!\nGStore Error: Unable to get product list. MySQL Error:\n".$db->Error, "MYSQL_ERROR");
  $db->Read();
  $ret['count'] = $db->record[0];
  
  /* GET HIDDEN COUNT */
  $db->RunQuery("SELECT COUNT(*) FROM (".ltrim($hiddenCountQry, " UNION ").") AS qryelements");
  if($db->Error) return $this->returnError("failed!\nGStore Error: Unable to get hidden product list. MySQL Error:\n".$db->Error, "MYSQL_ERROR");
  $db->Read();
  $ret['hidden'] = $db->record[0];

  $this->debug.= "done!\n";
  if($ret['hidden']) $this->debug.= "There are ".$ret['hidden']." products found hidden from store view!\n";
  $this->debug.= $ret['count']." results found.\n";

  /* EXEC QRY */
  if($ret['count'])
  {
   $this->debug.= "Run query...";
   $qry = "SELECT * FROM (".ltrim($qry, " UNION ").") AS qryelements ORDER BY ".$_ORDER_BY.($_LIMIT ? " LIMIT ".$_LIMIT : "");
   $db->RunQuery($qry);
   if($db->Error) return $this->returnError("failed!\nGStore Error: Unable to get product list. MySQL Error:\n".$db->Error."\nMySQL QRY:\n"
	.$db->lastQuery, "MYSQL_ERROR");
   $this->debug.= "done!\n";
   //$this->debug.= "MYSQL QRY:\n\n".$db->lastQuery."\n\n";
   while($db->Read())
   {
    $a = array('tb_prefix'=>$db->record['tb_prefix']);
	if($_GET_VARIANTS) $a['variants'] = array();
    for($c=0; $c < count($_FIELD_LIST); $c++)
 	 $a[$_FIELD_LIST[$c]] = $db->record[$_FIELD_LIST[$c]];
   
	if($_GET_STOCK_ENH)
	{
	 $a['enh_amount'] = $db->record['enh_amount'] ? $db->record['enh_amount'] : 0;
	 $a['enh_vat'] = $db->record['enh_vat'] ? $db->record['enh_vat'] : 0;
	 $a['enh_total'] = $db->record['enh_total'] ? $db->record['enh_total'] : 0;
	}

	if($_GET_VARIANTS)
	{
	 $db2->RunQuery("SELECT coltint,sizmis".$varStoreQ." FROM dynarc_".$a['tb_prefix']."_variantstock WHERE item_id='".$a['id']."'");
	 if($db2->Error) { $db2->Close(); $db2 = new AlpaDatabase(); }
	 else
	 {
	  while($db2->Read())
	  {
	   $vData = array('coltint'=>$db2->record['coltint'], 'sizmis'=>$db2->record['sizmis'], 'storeqty'=>0);
	   for($c=0; $c < count($stores); $c++)
	   {
		$vData['store_'.$stores[$c]['id'].'_qty'] = $db2->record['store_'.$stores[$c]['id'].'_qty'];
	    if($vData['store_'.$stores[$c]['id'].'_qty'] > 0)
		 $vData['storeqty']+= $vData['store_'.$stores[$c]['id'].'_qty'];
	   }
	   $a['variants'][] = $vData;
	  }
	 }
	}

    $ret['items'][] = $a;
   }
  }

  $db->Close();
  if($_GET_VARIANTS) $db2->Close();

  return $ret;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function GetProductInfo($_AP, $_ID, $_GETF="", $options=array())
 {
  $_STORE_ID = $options['store'] ? $options['store'] : 0;
  $_GET_STOCK_ENH = $options['getstockenh'] ? true : false;
  $_GET_VARIANTS = $options['getvariants'] ? true : false;

  /* PREPARE FIELDS */
  $_FIELDS = "id,uid,gid,_mod,cat_id,name,code_num,code_str,brand,model,manufacturer_code,qty_sold,units,
	weight,weightunits,storeqty,booked,incoming,loaded,downloaded,item_location,minimum_stock";
  if($_GETF) $_FIELDS.= ",".$_GETF;

  $stores = $this->GetStoreList();
  if(!$stores) return $this->returnError();
  for($c=0; $c < count($stores); $c++)
   $_FIELDS.= ",store_".$stores[$c]['id']."_qty";


  $this->debug.= "Get product info...";
  $db = new AlpaDatabase();

  // get if all fields exists into table
  $table = "dynarc_".$_AP."_items";
  $tbFields = $db->FieldsInfo($table);
  $x = explode(",",$_FIELDS);
  $_FIELDS = "";
  for($c=0; $c < count($x); $c++)
  {
   if($tbFields[$x[$c]])
	$_FIELDS.= ",".$x[$c];
  }
  if(!$_FIELDS) return $this->returnError("Store get-product-info failed! No fields found into table ".$table, 'NO_FIELDS_FOUND');
  $_FIELDS = ltrim($_FIELDS, ",");

  $_FIELD_LIST = explode(",",$_FIELDS);

  $qry = "SELECT '".$_AP."' AS tb_prefix,".$_FIELDS." FROM dynarc_".$_AP."_items WHERE id='".$_ID."'";
  $db->RunQuery($qry);
  if($db->Error) return $this->returnError("failed!\nGStore Error: Unable to get product info. MySQL Error:\n".$db->Error, "MYSQL_ERROR");
  if(!$db->Read()) return $this->returnError("failed!\nGStore Error: Unable to get product info. Product #"
	.$_ID." does not exists into archive ".$_AP, "ITEM_DOES_NOT_EXISTS");

  $mod = new GMOD($db->record['_mod'], $db->record['uid'], $db->record['gid']);
  if(!$mod->canRead()) return $this->returnError("failed!\nGStore Error: Unable to get product info. Permission denied!", "PERMISSION_DENIED");

  $ret = array('tb_prefix'=>$db->record['tb_prefix']);
  for($c=0; $c < count($_FIELD_LIST); $c++)
   $ret[$_FIELD_LIST[$c]] = $db->record[$_FIELD_LIST[$c]];

  $db->Close();

  if($_GET_STOCK_ENH)
  {
   $db = new AlpaDatabase();
   $this->debug.= "Get stock enhancement...";
   $enhAmountQ=""; $enhVatQ=""; $enhTotQ="";
   if($_STORE_ID)
   {
    $enhAmountQ = "store_".$_STORE_ID."_amount";
    $enhVatQ = "+store_".$_STORE_ID."_vat";
    $enhTotQ = "+store_".$_STORE_ID."_total";
   }
   else
   {
    for($c=0; $c < count($stores); $c++)
    {
     $enhAmountQ.= "+store_".$stores[$c]['id']."_amount";
     $enhVatQ.= "+store_".$stores[$c]['id']."_vat";
     $enhTotQ.= "+store_".$stores[$c]['id']."_total";
    }
    $enhAmountQ = substr($enhAmountQ,1);
    $enhVatQ = substr($enhVatQ,1);
    $enhTotQ = substr($enhTotQ,1);
   }

   $db->RunQuery("SELECT ".$enhAmountQ." AS enh_amount, ".$enhVatQ." AS enh_vat, ".$enhTotQ." AS enh_total FROM dynarc_".
	$_AP."_stockenhitm WHERE item_id='".$_ID."'");
   $db->Read();
   $ret['enh_amount'] = $db->record['enh_amount'];
   $ret['enh_vat'] = $db->record['enh_vat'];
   $ret['enh_total'] = $db->record['enh_total'];

   $this->debug.= "done!\n";
   $db->Close();
  } // EOF get stock enhancement

  if($_GET_VARIANTS)
  {
   $ret['variants'] = array();

   $db = new AlpaDatabase();
   $this->debug.= "Get variants...";

   $q = "";
   if($_STORE_ID)	$q = "store_".$_STORE_ID."_qty";
   else
   {
    for($c=0; $c < count($stores); $c++)
	 $q.= "+store_".$stores[$c]['id']."_qty";
    $q = substr($q,1);
   }

   $db->RunQuery("SELECT coltint,sizmis, ".$q." AS storeqty FROM dynarc_".$_AP."_variantstock WHERE item_id='".$_ID."'"); 
   while($db->Read())
   {
	$ret['variants'][] = array('coltint'=>$db->record['coltint'], 'sizmis'=>$db->record['sizmis'], 'storeqty'=>$db->record['storeqty']);
   }

   $this->debug.= "done!\n";
   $db->Close();
  } // EOF get variants

  return $ret;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function AboutStore($storeId=0, $options=array())
 {
  $_AT = $options['at'] ? $options['at'] : 'gmart';
  $_AP = $options['ap'] ? $options['ap'] : '';
  $_DATE_FROM = $options['from'] ? $options['from'] : '';
  $_DATE_TO = $options['to'] ? $options['to'] : '';
  //$_REF_AP = $options['refap'] ? $options['refap'] : '';
  $_REF_ID = $options['refid'] ? $options['refid'] : '';

  $outArr = array('totitems_count'=>0, 'soldout_count'=>0, 'underminstock_count'=>0, 'stock_value'=>0, 
	'missprice_count'=>0, 'archives_count'=>0);

  $archives = array();
  if($_AP)
  {
   $archiveInfo = $this->GetArchiveInfo($_AP);
   if(!$archiveInfo) return $this->returnError();
   $archives[] = $archiveInfo;
  }
  else
  {
   $archives = $this->GetArchiveList($_AT);
   if(!$archives) return $this->returnError();
  }

  $outArr['archives_count'] = count($archives);

  // Get store info
  if($storeId)
  {
   $storeInfo = $this->GetStoreById($storeId);
   if(!$storeInfo) return $this->returnError();
   $outArr['store'] = array('id'=>$storeInfo['id'], 'name'=>$storeInfo['name'], 'items_count'=>0, 'stock_value'=>0);
  }

  $db = new AlpaDatabase();
  for($c=0; $c < count($archives); $c++)
  {
   $this->debug.= "Get counter for archive ".$archives[$c]['name']."...";
   // Get count of all items
   $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archives[$c]['prefix']."_items WHERE trash='0'");
   if($db->Error) return $this->returnError("failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
   $db->Read();
   $outArr['totitems_count']+= $db->record[0];

   if($storeInfo)
   {
    // Get count of items into store
    $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archives[$c]['prefix']."_items WHERE trash='0' AND store_".$storeId."_qty>0");
    if($db->Error) return $this->returnError("failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
    $db->Read();
    $outArr['store']['items_count']+= $db->record[0];
   }

   // Get soldout count
   $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archives[$c]['prefix']."_items WHERE trash='0' AND storeqty<=0");
   if($db->Error) return $this->returnError("failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
   $db->Read();
   $outArr['soldout_count']+= $db->record[0];

   // Get under min stock count
   $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archives[$c]['prefix']."_items WHERE trash='0' AND minimum_stock>0 AND storeqty<=minimum_stock");
   if($db->Error) return $this->returnError("failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
   $db->Read();
   $outArr['underminstock_count']+= $db->record[0];

   if($storeId)
   {
    // Get store stock value
    $qry = "SELECT SUM(stock_value) AS tot_stock_value FROM (";
    $qry.= "SELECT SUM(i.store_".$storeId."_qty * v.price) AS stock_value FROM `dynarc_".$archives[$c]['prefix']."_items` AS i";
    $qry.= " INNER JOIN dynarc_".$archives[$c]['prefix']."_vendorprices AS v";
    $qry.= " ON v.item_id=i.id";
    $qry.= " WHERE trash='0' AND store_".$storeId."_qty>0 GROUP BY i.id";
    $qry.= ") AS qryelements";

    $db->RunQuery($qry);
    if($db->Error) return $this->returnError("failed!\nUnable to get stock value for store ".$storeInfo['name'].", archive "
	 .$archives[$c]['name'].".\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
    if($db->Read())
	 $outArr['store']['stock_value']+= $db->record['tot_stock_value'];
   }

   // Get stock value
   $qry = "SELECT SUM(stock_value) AS tot_stock_value FROM (";
   $qry.= "SELECT SUM(i.storeqty * v.price) AS stock_value FROM `dynarc_".$archives[$c]['prefix']."_items` AS i";
   $qry.= " INNER JOIN dynarc_".$archives[$c]['prefix']."_vendorprices AS v";
   $qry.= " ON v.item_id=i.id";
   $qry.= " WHERE trash='0' AND storeqty>0 GROUP BY i.id";
   $qry.= ") AS qryelements";

   $db->RunQuery($qry);
   if($db->Error) return $this->returnError("failed!\nUnable to get stock value for archive "
	.$archives[$c]['name'].".\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
   if($db->Read())
	$outArr['stock_value']+= $db->record['tot_stock_value'];

   $this->debug.= "done!\n";
   /* EOF - GET COUNTERS */

   /* ABOUT STORE MOVEMENTS */
   $this->debug.= "Get store movements about archive ".$archives[$c]['name']."...";
   $countQry = "SELECT COUNT(*) FROM store_movements WHERE ref_ap='".$archives[$c]['prefix']."'";
   if($_REF_ID) $countQry.= " AND ref_id='".$_REF_ID."'";
   $qry = "SELECT SUM(qty) AS tot_qty FROM store_movements WHERE ref_ap='".$archives[$c]['prefix']."'";
   if($_REF_ID) $qry.= " AND ref_id='".$_REF_ID."'";

   if($storeId)
   {
	$qry.= " AND store_id='".$storeId."'";
	$countQry.= " AND store_id='".$storeId."'";
   }
   if($_DATE_FROM)
   {
	$qry.= " AND op_time>='".$_DATE_FROM."'";
	$countQry.= " AND op_time>='".$_DATE_FROM."'";
   }
   if($_DATE_TO)
   {
	$qry.= " AND op_time<'".$_DATE_TO."'";
	$countQry.= " AND op_time<'".$_DATE_TO."'";
   }

   // get upload count and qry
   $db->RunQuery($countQry." AND mov_act=1");
   if($db->Error) return $this->returnError("failed!\nUnable to get movements from archive "
	.$archives[$c]['name'].".\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
   $db->Read();
   $outArr['upload_count']+= $db->record[0];

   $db->RunQuery($qry." AND mov_act=1");
   if($db->Error) return $this->returnError("failed!\nUnable to get movements from archive "
	.$archives[$c]['name'].".\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
   $db->Read();
   $outArr['upload_qty']+= $db->record['tot_qty'];


   // get download count and qty
   $db->RunQuery($countQry." AND mov_act=2");
   if($db->Error) return $this->returnError("failed!\nUnable to get movements from archive "
	.$archives[$c]['name'].".\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
   $db->Read();
   $outArr['download_count']+= $db->record[0];

   $db->RunQuery($qry." AND mov_act=2");
   if($db->Error) return $this->returnError("failed!\nUnable to get movements from archive "
	.$archives[$c]['name'].".\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
   $db->Read();
   $outArr['download_qty']+= $db->record['tot_qty'];


   // get transfer count
   $db->RunQuery($countQry." AND mov_act=3");
   if($db->Error) return $this->returnError("failed!\nUnable to get movements from archive "
	.$archives[$c]['name'].".\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
   $db->Read();
   $outArr['transfer_count']+= $db->record[0];

   $db->RunQuery($qry." AND mov_act=3");
   if($db->Error) return $this->returnError("failed!\nUnable to get movements from archive "
	.$archives[$c]['name'].".\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
   $db->Read();
   $outArr['transfer_qty']+= $db->record['tot_qty'];
   

   $this->debug.= "done!\n";
  }
  $db->Close();

  return $outArr;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function Upload($storeId, $_AP, $_ID, $qty, $options=array())
 {
  $ctime = $options['ctime'] ? $options['ctime'] : time();
  $action = $options['action'] ? $options['action'] : 1;	// DEFAULT = 1: Upload
  $vendorId = $options['vendor_id'] ? $options['vendor_id'] : 0;
  $causal = $options['causal'] ? $options['causal'] : "";
  $serialNumber = $options['serial_number'] ? $options['serial_number'] : "";
  $lot = $options['lot'] ? $options['lot'] : "";
  $accountId = $options['account_id'] ? $options['account_id'] : 0;

  $archiveInfo = $this->GetArchiveInfo($_AP);
  $pricelists = $this->GetPricelists();
  $_PLID = count($pricelists) ? $pricelists[0]['id'] : 0;


  $db = new AlpaDatabase();
  // RICAVO ALCUNI DATI SULL'ARTICOLO
  $_FIELDS = "code_str,name,cat_id";
  switch($archiveInfo['type'])
  {
   case 'gmart' : case 'gproducts' : case 'gpart' : case 'gmaterial' : {
	 $_FIELDS.= ",barcode,item_location,units,baseprice,vat";
	 if($_PLID)
	  $_FIELDS.= ",pricelist_".$_PLID."_baseprice, pricelist_".$_PLID."_mrate, pricelist_".$_PLID."_vat";
	 if($archiveInfo['type'] != "gproducts")
	  $_FIELDS.= ",manufacturer_code";

	 $qry = "SELECT i.".str_replace(',',',i.',$_FIELDS);
	 if($archiveInfo['type'] != "gproducts")
	  $qry.= ",v.code AS vencode, v.vendor_id, v.price AS vendor_price, v.vatrate AS vendor_vatrate";

	 $qry.= " FROM dynarc_".$_AP."_items AS i";

	 if($archiveInfo['type'] != "gproducts")
	  $qry.= " LEFT JOIN dynarc_".$_AP."_vendorprices AS v ON v.item_id=i.id"
		.($vendorId ? " AND v.vendor_id='".$vendorId."'" : "")." WHERE i.id='".$_ID."'";

	} break;

   default : $qry = "SELECT ".$_FIELDS." FROM dynarc_".$_AP."_items WHERE id='".$_ID."'"; break;
  }

  $this->debug.= "Get item info...";
  $db->RunQuery($qry);
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  $this->debug.= "done!\n";
  if(!$db->Read())
   return $this->returnError("failed!\n Unable to get item #".$_ID." into archive ".$archiveInfo['name'], 'ITEM_DOES_NOT_EXISTS');
  $itemInfo = $db->record;
  

  $code = $options['code'] ? $options['code'] : $itemInfo['code_str'];
  $mancode = $options['mancode'] ? $options['mancode'] : ($itemInfo['manufacturer_code'] ? $itemInfo['manufacturer_code'] : "");
  $barcode = $options['barcode'] ? $options['barcode'] : ($itemInfo['barcode'] ? $itemInfo['barcode'] : "");
  $name = $options['name'] ? $options['name'] : $itemInfo['name'];
  $units = $options['units'] ? $options['units'] : ($itemInfo['units'] ? $itemInfo['units'] : "");
  $location = $options['location'] ? $options['location'] : ($itemInfo['item_location'] ? $itemInfo['item_location'] : "");
  $vencode = $itemInfo['vencode'] ? $itemInfo['vencode'] : "";
  $vendorPrice = $itemInfo['vendor_price'] ? $itemInfo['vendor_price'] : 0;
  $vendorVatRate = $itemInfo['vendor_vatrate'] ? $itemInfo['vendor_vatrate'] : 0;
  if(!$vendorId) $vendorId = $itemInfo['vendor_id'];
  $price = ($_PLID && isset($itemInfo['pricelist_'.$_PLID.'_baseprice'])) ? $itemInfo['pricelist_'.$_PLID.'_baseprice'] : ($itemInfo['baseprice'] ? $itemInfo['baseprice'] : 0);
  $vatRate = ($_PLID && isset($itemInfo['pricelist_'.$_PLID.'_vat'])) ? $itemInfo['pricelist_'.$_PLID.'_vat'] : ($itemInfo['vat'] ? $itemInfo['vat'] : 0);

  $stockQty=0;
  $stockAmount=0;
  $stockVat=0;
  $stockTotal=0;


  // determino la scorta iniziale dall'ultimo movimento 
  $this->debug.= "Get info about last store movement...";
  $db->RunQuery("SELECT op_time,mov_act,qty,vendor_unitprice,vendor_vatrate,stock_qty,stock_amount,stock_vat,stock_total FROM store_movements WHERE ref_ap='".$_AP."' AND ref_id='".$_ID."' AND op_time<'".date('Y-m-d H:i:s',$ctime)."' ORDER BY op_time DESC LIMIT 1");
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  if($db->Read())
  {
   switch($db->record['mov_act'])
   {
	case 1 : {
		 $stockQty = $db->record['stock_qty'] + $db->record['qty'];
		 $stockAmount = $db->record['vendor_unitprice'] * $db->record['qty'];
		 $stockVat = $stockAmount ? ($stockAmount/100)*$db->record['vendor_vatrate'] : 0;
		 $stockTotal = $stockAmount+$stockVat;

		 $stockAmount+= $db->record['stock_amount'];
		 $stockVat+= $db->record['stock_vat'];
		 $stockTotal+= $db->record['stock_total'];
		} break;

    case 2 : {
		 $stockQty = $db->record['stock_qty'] - $db->record['qty'];
		 $stockAmount = $db->record['vendor_unitprice'] * $db->record['qty'];
		 $stockVat = $stockAmount ? ($stockAmount/100)*$db->record['vendor_vatrate'] : 0;
		 $stockTotal = $stockAmount+$stockVat;

		 $stockAmount = $db->record['stock_amount'] - $stockAmount;
		 $stockVat = $db->record['stock_vat'] - $stockVat;
		 $stockTotal = $db->record['stock_total'] - $stockTotal;
		} break;
   }
  }
  $this->debug.= "done!\n";

  // UPDATE STORE STATUS
  $this->debug.= "Update store status...";
  $db->RunQuery("UPDATE dynarc_".$_AP."_items SET store_".$storeId."_qty=store_".$storeId."_qty+".
	$qty.",storeqty=storeqty+".$qty." WHERE id='".$_ID."'");
  if($db->Error) return $this->returnError("failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
  $this->debug.= "done!\n";

  // UPDATE VARIANTSTOCK
  if($options['coltint'] || $options['sizmis'])
  {
   $this->debug.= "Update variant stock...";
   $where = "";
   if($options['coltint'] && $options['sizmis'])
	$where = "coltint='".$db->Purify($options['coltint'])."' AND sizmis='".$db->Purify($options['sizmis'])."'";
   else if($options['coltint'])	$where = "coltint='".$db->Purify($options['coltint'])."'";
   else $where = "sizmis='".$db->Purify($options['sizmis'])."'";

   $db->RunQuery("SELECT id,store_".$storeId."_qty FROM dynarc_".$_AP."_variantstock WHERE item_id='".$_ID."' AND ".$where);
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
   if($db->Read())
	$db->RunQuery("UPDATE dynarc_".$_AP."_variantstock SET store_".$storeId."_qty='"
		.($db->record['store_'.$storeId.'_qty']+$qty)."' WHERE id='".$db->record['id']."'");
   else
	$db->RunQuery("INSERT INTO dynarc_".$_AP."_variantstock (item_id,coltint,sizmis,store_".$storeId."_qty) VALUES('"
		.$_ID."','".$db->Purify($options['coltint'])."','".$db->Purify($options['sizmis'])."','".$qty."')");
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
   $this->debug.= "done!\n";
  }

  // REGISTER MOVEMENT
  $this->debug.= "Register movement...";
  $_STORE_FIELDS = "store_id, op_time, uid, mov_act, mov_causal, qty, units, serial_number, lot, 
	ref_at, ref_ap, ref_id, ref_code, ref_name, ref_vendor_id, ref_vendor_code, vendor_unitprice, vendor_vatrate, 
	price, vatrate, account_id, notes, doc_ap, doc_id, doc_ref, barcode, location, 
	stock_qty, stock_amount, stock_vat, stock_total, variant_coltint, variant_sizmis";

  $qry = "INSERT INTO store_movements(".$_STORE_FIELDS.") VALUES('".$storeId."','".date('Y-m-d H:i',$ctime)."','"
	.$sessInfo['uid']."','".$action."','".$db->Purify($causal)."','".$qty."','".$units."','"
	.$serialNumber."','".$lot."','".$archiveInfo['type']."','".$_AP."','".$_ID."','".$code."','".$db->Purify($name)."','"
	.$vendorId."','".$vencode."','".$vendorPrice."','".$vendorVatRate."','".$price."','".$vatRate."','".$accountId."','"
	.($options['note'] ? $db->Purify($options['note']) : '')."','"
	.$options['doc_ap']."','".$options['doc_id']."','".($options['doc_ref'] ? $db->Purify($options['doc_ref']) : '')."','"
	.$barcode."','".$db->Purify($location)."','"
	.$stockQty."','".$stockAmount."','".$stockVat."','".$stockTotal."','"
	.($options['coltint'] ? $db->Purify($options['coltint']) : '')."','"
	.($options['sizmis'] ? $db->Purify($options['sizmis']) : '')."')";

  $db->RunQuery($qry);
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');

  $movId = $db->GetInsertId();
  $this->debug.= "done! Movement has been registered. ID=".$movId."\n";

  //update stock enhancement
  $this->debug.= "Updating stock enhancement...";

  $enhAmount = $vendorPrice * $qty;
  $enhVat = $enhAmount ? ($enhAmount/100)*$vendorVatRate : 0;
  $enhTotal = $enhAmount+$enhVat;
  
  $db->RunQuery("SELECT id,amount,vat,total FROM stock_enhancement WHERE store_id='".$storeId."' AND ap='".$_AP."'");
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  if($db->Read())
   $db->RunQuery("UPDATE stock_enhancement SET amount='".($db->record['amount']+$enhAmount)."',vat='"
	.($db->record['vat']+$enhVat)."',total='".($db->record['total']+$enhTotal)."' WHERE id='".$db->record['id']."'");
  else
   $db->RunQuery("INSERT INTO stock_enhancement (store_id,ap,amount,vat,total) VALUES('".$storeId."','".$_AP."','".$enhAmount."','"
	.$enhVat."','".$enhTotal."')");
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');

  // - update stockenhitm
  $db->RunQuery("INSERT INTO dynarc_".$_AP."_stockenhitm (item_id,store_".$storeId."_qty,store_".$storeId."_amount,store_"
	.$storeId."_vat,store_".$storeId."_total) VALUES('".$_ID."','".$qty."','".$enhAmount."','".$enhVat."','".$enhTotal."')
ON DUPLICATE KEY UPDATE store_".$storeId."_qty=store_".$storeId."_qty+".$qty.", store_".$storeId."_amount=store_".$storeId."_amount+".$enhAmount.", store_".$storeId."_vat=store_".$storeId."_vat+".$enhVat.", store_".$storeId."_total=store_".$storeId."_total+".$enhTotal);
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  
  // - update stockenhcat
  $db->RunQuery("INSERT INTO dynarc_".$_AP."_stockenhcat (cat_id,store_".$storeId."_amount,store_".$storeId."_vat,store_".$storeId."_total) VALUES('"
	.$itemInfo['cat_id']."','".$enhAmount."','".$enhVat."','".$enhTotal."')
ON DUPLICATE KEY UPDATE store_".$storeId."_amount=store_".$storeId."_amount+".$enhAmount.", store_".$storeId."_vat=store_".$storeId."_vat+"
	.$enhVat.", store_".$storeId."_total=store_".$storeId."_total+".$enhTotal);
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');

  $this->debug.= "done!\n"; // EOF - update stock enhancement

  return true;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function Download($storeId, $_AP, $_ID, $qty, $options=array())
 {
  $ctime = $options['ctime'] ? $options['ctime'] : time();
  $action = $options['action'] ? $options['action'] : 2;	// DEFAULT = 2: Download
  $vendorId = $options['vendor_id'] ? $options['vendor_id'] : 0;
  $vendorPrice = $options['vendor_price'] ? $options['vendor_price'] : 0;
  $vendorVatRate = $options['vendor_vatrate'] ? $options['vendor_vatrate'] : 0;

  $price = $options['price'] ? $options['price'] : 0;
  $vatRate = $options['vatrate'] ? $options['vatrate'] : 0;
  $discount = $options['discount'] ? $options['discount'] : 0;
  $discountInc = 0; $discountPerc = 0;
  if($discount)
  {
   if(is_numeric($discount))
	$discountInc = $discount;
   else if(strpos($discount,"%") !== false)
    $discountPerc = str_replace("%","",$discount);
  }

  $causal = $options['causal'] ? $options['causal'] : "";
  $serialNumber = $options['serial_number'] ? $options['serial_number'] : "";
  $lot = $options['lot'] ? $options['lot'] : "";
  $accountId = $options['account_id'] ? $options['account_id'] : 0;

  $unbook = $options['unbook'] ? $options['unbook'] : false;
  $forceUnbook = $options['forceunbook'] ? $options['forceunbook'] : false;

  $config = $this->GetConfig();
  $archiveInfo = $this->GetArchiveInfo($_AP);
  $pricelists = $this->GetPricelists();
  $storelist = $this->GetStoreList();
  $_PLID = count($pricelists) ? $pricelists[0]['id'] : 0;

  $db = new AlpaDatabase();

  // UPDATE STORE STATUS
  $this->debug.= "Update store status..."; 
  if($unbook && $options['doc_ap'] && $options['doc_id'] && !$forceUnbook)
  {
   $db->RunQuery("SELECT id FROM dynarc_".$options['doc_ap']."_items WHERE conv_doc_id='".$options['doc_id']."' OR group_doc_id='".$options['doc_id']."' LIMIT 1");
   if($db->Error) return $this->returnError("failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
   if(!$db->Read()) $unbook = false;
  }
  if($forceUnbook) $unbook=true;
  

  // verifica giacenza fisica
  if($options['coltint'] || $options['sizmis'])
  {
   $qry = "SELECT i.store_".$storeId."_qty AS store_qty, i.booked, vs.id AS vs_id, vs.store_".$storeId."_qty AS vs_store_qty";
   $qry.= " FROM dynarc_".$_AP."_items AS i LEFT JOIN dynarc_".$_AP."_variantstock AS vs";
   $qry.= " ON vs.item_id=".$_ID." AND ";
   if($options['coltint'] && $options['sizmis'])
	$qry.= "(vs.coltint='".$db->Purify($options['coltint'])."' AND vs.sizmis='".$db->Purify($options['sizmis'])."')";
   else if($options['coltint']) $qry.= "vs.coltint='".$db->Purify($options['coltint'])."'";
   else $qry.= "vs.sizmis='".$db->Purify($options['sizmis'])."'";
   $qry.= " WHERE i.id=".$_ID;
  }
  else
   $qry = "SELECT store_".$storeId."_qty AS store_qty FROM dynarc_".$_AP."_items WHERE id='".$_ID."'";


  $db->RunQuery($qry);
  if($db->Error) return $this->returnError("failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
  $db->Read();
  if($db->record['store_qty'] < $qty)
   return $this->returnError("failed!\nCould not download the item from store because the physical stock is less than that required.", "INSUFFICIENT_STORAGE");
  if(($options['coltint'] || $options['sizmis']) && ($db->record['vs_store_qty'] < $qty))
   return $this->returnError("failed!\nCould not download the item from store because the physical stock is less than that required.", "INSUFFICIENT_STORAGE");

  $vsId = $db->record['vs_id']; // variantstock id
  $booked = $db->record['booked'];

  $db->RunQuery("UPDATE dynarc_".$_AP."_items SET store_".$storeId."_qty=store_".$storeId."_qty-".
	$qty.",storeqty=storeqty-".$qty.($unbook ? ",booked=booked-".$qty : "")." WHERE id='".$_ID."'");
  if($db->Error) return $this->returnError("failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
  $this->debug.= "done!\n";

  // UPDATE VARIANTSTOCK
  if(($options['coltint'] || $options['sizmis']) && $vsId)
  {
   $this->debug.= "Update variant stock...";
   $db->RunQuery("UPDATE dynarc_".$_AP."_variantstock SET store_".$storeId."_qty=store_".$storeId."_qty-".$qty." WHERE id='".$vsId."'");
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
   $this->debug.= "done!\n";
  }

  // RICAVO ALCUNI DATI SULL'ARTICOLO
  $_FIELDS = "code_str,name,cat_id";
  switch($archiveInfo['type'])
  {
   case 'gmart' : case 'gproducts' : case 'gpart' : case 'gmaterial' : {
	 $_FIELDS.= ",barcode,item_location,units,baseprice,vat";
	 if($_PLID)
	  $_FIELDS.= ",pricelist_".$_PLID."_baseprice, pricelist_".$_PLID."_mrate, pricelist_".$_PLID."_vat";
	 if($archiveInfo['type'] != "gproducts")
	  $_FIELDS.= ",manufacturer_code";

	 $qry = "SELECT i.".str_replace(',',',i.',$_FIELDS);
	 if($archiveInfo['type'] != "gproducts")
	 {
	  // vendorprices
	  $qry.= ",v.code AS vencode, v.vendor_id, v.price AS vendor_price, v.vatrate AS vendor_vatrate";
	  // stockenhitm
	  $qtyQ=""; $amountQ="";
	  for($c=0; $c < count($storelist); $c++)
	  {
	   $qtyQ.= "+enh.store_".$storelist[$c]['id']."_qty";
	   $amountQ.= "+enh.store_".$storelist[$c]['id']."_amount";
	  }
	  $qry.= ", ".substr($qtyQ,1)." AS enh_tot_qty, ".substr($amountQ,1)." AS enh_tot_amount";
	 }

	 $qry.= " FROM dynarc_".$_AP."_items AS i";

	 if($archiveInfo['type'] != "gproducts")
	 {
	  // vendorprices
	  $qry.= " LEFT JOIN dynarc_".$_AP."_vendorprices AS v ON v.item_id=i.id"
		.($vendorId ? " AND v.vendor_id='".$vendorId."'" : "");
	  // stockenhitm
	  $qry.= " LEFT JOIN dynarc_".$_AP."_stockenhitm AS enh ON enh.item_id=i.id";
	 }

	 $qry.= " WHERE i.id='".$_ID."'";

	} break;

   default : $qry = "SELECT ".$_FIELDS." FROM dynarc_".$_AP."_items WHERE id='".$_ID."'"; break;
  }

  $this->debug.= "Get item info...";
  $db->RunQuery($qry);
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  $this->debug.= "done!\n";
  if(!$db->Read())
   return $this->returnError("failed!\n Unable to get item #".$_ID." into archive ".$archiveInfo['name'], 'ITEM_DOES_NOT_EXISTS');
  $itemInfo = $db->record;
  

  $code = $options['code'] ? $options['code'] : $itemInfo['code_str'];
  $mancode = $options['mancode'] ? $options['mancode'] : ($itemInfo['manufacturer_code'] ? $itemInfo['manufacturer_code'] : "");
  $barcode = $options['barcode'] ? $options['barcode'] : ($itemInfo['barcode'] ? $itemInfo['barcode'] : "");
  $name = $options['name'] ? $options['name'] : $itemInfo['name'];
  $units = $options['units'] ? $options['units'] : ($itemInfo['units'] ? $itemInfo['units'] : "");
  $location = $options['location'] ? $options['location'] : ($itemInfo['item_location'] ? $itemInfo['item_location'] : "");
  $vencode = $itemInfo['vencode'] ? $itemInfo['vencode'] : "";
  $vendorPrice = $options['vendor_price'] ? $options['vendor_price'] : ($itemInfo['vendor_price'] ? $itemInfo['vendor_price'] : 0);
  $vendorVatRate = $options['vendor_vatrate'] ? $options['vendor_vatrate'] : ($itemInfo['vendor_vatrate'] ? $itemInfo['vendor_vatrate'] : 0);
  if(!$vendorId) $vendorId = $itemInfo['vendor_id'];

  if(!$price)
   $price = ($_PLID && isset($itemInfo['pricelist_'.$_PLID.'_baseprice'])) ? $itemInfo['pricelist_'.$_PLID.'_baseprice'] : ($itemInfo['baseprice'] ? $itemInfo['baseprice'] : 0);
  if(!$vatRate)
   $vatRate = ($_PLID && isset($itemInfo['pricelist_'.$_PLID.'_vat'])) ? $itemInfo['pricelist_'.$_PLID.'_vat'] : ($itemInfo['vat'] ? $itemInfo['vat'] : 0);

  $enhTotQty = $itemInfo['enh_tot_qty'] ? $itemInfo['enh_tot_qty'] : 0;
  $enhTotAmount = $itemInfo['enh_tot_amount'] ? $itemInfo['enh_tot_amount'] : 0;

  // Determina il valore di scarico attraverso il metodo standard.
  /* TODO: da fare metodo LIFO e FIFO */
  if($enhTotQty)
   $vendorPrice = $enhTotAmount / $enhTotQty;
  $enhAmount = $vendorPrice * $qty;
  $enhVat = $enhAmount ? ($enhAmount/100)*$vendorVatRate : 0;
  $enhTotal = $enhAmount+$enhVat;


  // determino la scorta iniziale dall'ultimo movimento 
  $stockQty=0; $stockAmount=0; $stockVat=0; $stockTotal=0;
  $this->debug.= "Get info about last store movement...";
  $db->RunQuery("SELECT op_time,mov_act,qty,vendor_unitprice,vendor_vatrate,stock_qty,stock_amount,stock_vat,stock_total FROM store_movements WHERE ref_ap='".$_AP."' AND ref_id='".$_ID."' AND op_time<'".date('Y-m-d H:i:s',$ctime)."' ORDER BY op_time DESC LIMIT 1");
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  if($db->Read())
  {
   switch($db->record['mov_act'])
   {
	case 1 : {
		 $stockQty = $db->record['stock_qty'] + $db->record['qty'];
		 $stockAmount = $db->record['vendor_unitprice'] * $db->record['qty'];
		 $stockVat = $stockAmount ? ($stockAmount/100)*$db->record['vendor_vatrate'] : 0;
		 $stockTotal = $stockAmount+$stockVat;

		 $stockAmount+= $db->record['stock_amount'];
		 $stockVat+= $db->record['stock_vat'];
		 $stockTotal+= $db->record['stock_total'];
		} break;

    case 2 : {
		 $stockQty = $db->record['stock_qty'] - $db->record['qty'];
		 $stockAmount = $db->record['vendor_unitprice'] * $db->record['qty'];
		 $stockVat = $stockAmount ? ($stockAmount/100)*$db->record['vendor_vatrate'] : 0;
		 $stockTotal = $stockAmount+$stockVat;

		 $stockAmount = $db->record['stock_amount'] - $stockAmount;
		 $stockVat = $db->record['stock_vat'] - $stockVat;
		 $stockTotal = $db->record['stock_total'] - $stockTotal;
		} break;
   }
  }
  $this->debug.= "done!\n";

  // REGISTER MOVEMENT
  $this->debug.= "Register movement...";
  $_STORE_FIELDS = "store_id, op_time, uid, mov_act, mov_causal, qty, units, serial_number, lot, 
	ref_at, ref_ap, ref_id, ref_code, ref_name, ref_vendor_id, ref_vendor_code, vendor_unitprice, vendor_vatrate, 
	price, vatrate, discount_perc, discount_inc, account_id, notes, doc_ap, doc_id, doc_ref, 
	stock_qty, stock_amount, stock_vat, stock_total, variant_coltint, variant_sizmis";

  $qry = "INSERT INTO store_movements(".$_STORE_FIELDS.") VALUES('".$storeId."','".date('Y-m-d H:i',$ctime)."','"
	.$sessInfo['uid']."','".$action."','".$db->Purify($causal)."','".$qty."','".$units."','"
	.$serialNumber."','".$lot."','".$archiveInfo['type']."','".$_AP."','".$_ID."','".$code."','".$db->Purify($name)."','"
	.$vendorId."','".$vencode."','".$vendorPrice."','".$vendorVatRate."','".$price."','".$vatRate."','"
	.$discountPerc."','".$discountInc."','".$accountId."','".($options['note'] ? $db->Purify($options['note']) : '')."','"
	.$options['doc_ap']."','".$options['doc_id']."','".($options['doc_ref'] ? $db->Purify($options['doc_ref']) : '')."','"
	.$stockQty."','".$stockAmount."','".$stockVat."','".$stockTotal."','"
	.($options['coltint'] ? $db->Purify($options['coltint']) : '')."','"
	.($options['sizmis'] ? $db->Purify($options['sizmis']) : '')."')";

  $db->RunQuery($qry);
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');

  $movId = $db->GetInsertId();
  $this->debug.= "done! Movement has been registered. ID=".$movId."\n";

  //update stock enhancement
  $this->debug.= "Updating stock enhancement...";
 
  $db->RunQuery("SELECT id,amount,vat,total FROM stock_enhancement WHERE store_id='".$storeId."' AND ap='".$_AP."'");
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  if($db->Read())
   $db->RunQuery("UPDATE stock_enhancement SET amount='".($db->record['amount']-$enhAmount)."',vat='"
	.($db->record['vat']-$enhVat)."',total='".($db->record['total']-$enhTotal)."' WHERE id='".$db->record['id']."'");
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');

  // - update stockenhitm
  $db->RunQuery("UPDATE dynarc_".$_AP."_stockenhitm SET store_".$storeId."_qty=store_".$storeId."_qty-".$qty.", store_".$storeId."_amount=store_".$storeId."_amount-".$enhAmount.", store_".$storeId."_vat=store_".$storeId."_vat-".$enhVat.", store_".$storeId."_total=store_".$storeId."_total-".$enhTotal." WHERE item_id='".$_ID."'");
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  
  // - update stockenhcat
  $db->RunQuery("UPDATE dynarc_".$_AP."_stockenhcat SET store_".$storeId."_amount=store_".$storeId."_amount-".$enhAmount.", store_".$storeId."_vat=store_".$storeId."_vat-".$enhVat.", store_".$storeId."_total=store_".$storeId."_total-".$enhTotal." WHERE cat_id='".$itemInfo['cat_id']."'");
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');

  $this->debug.= "done!\n"; // EOF - update stock enhancement

  return true;  
 }
 //------------------------------------------------------------------------------------------------------------------//
 function Move($storeFrom, $storeTo, $_AP, $_ID, $qty, $options=array())
 {
  $ctime = $options['ctime'] ? $options['ctime'] : time();
  $action = $options['action'] ? $options['action'] : 3;	// DEFAULT = 3: Transfer
  $causal = $options['causal'] ? $options['causal'] : "";
  $serialNumber = $options['serial_number'] ? $options['serial_number'] : "";
  $lot = $options['lot'] ? $options['lot'] : "";
  $accountId = $options['account_id'] ? $options['account_id'] : 0;

  $archiveInfo = $this->GetArchiveInfo($_AP);

  $db = new AlpaDatabase();

  $qry = "SELECT i.code_str,i.name,i.description,i.baseprice,i.vat,i.units";
  $qry.= ",a.archive_type AS at, vs.id AS vs_id FROM dynarc_".$_AP."_items AS i";
  $qry.= " LEFT JOIN dynarc_".$_AP."_variantstock AS vs";

  $qry.= " ON vs.item_id=".$_ID." AND ";
  if($options['coltint'] && $options['sizmis'])
   $qry.= "(vs.coltint='".$db->Purify($options['coltint'])."' AND vs.sizmis='".$db->Purify($options['sizmis'])."')";
  else if($options['coltint']) $qry.= "vs.coltint='".$db->Purify($options['coltint'])."'";
  else $qry.= "vs.sizmis='".$db->Purify($options['sizmis'])."'";

  $qry.= " LEFT JOIN dynarc_archives AS a ON a.tb_prefix='".$_AP."' WHERE i.id='".$_ID."'";
  
  $this->debug.= "Get item info...";
  $db->RunQuery($qry);
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  if(!$db->Read()) return $this->returnError("failed!\n Item #".$_ID." does not exists into archive ".$_AP.".", 'MYSQL_ERROR');
  $this->debug.= "done!\n";

  $itemInfo = $db->record;
  $vsId = $db->record['vs_id']; // variantstock id
  $_AT = $db->record['at'];

  $this->debug.= "Update store qty...";
  // download from storeFrom
  $db->RunQuery("UPDATE dynarc_".$_AP."_items SET store_".$storeFrom."_qty=store_".$storeFrom."_qty-".
	$qty.",storeqty=storeqty-".$qty." WHERE id='".$_ID."'");
  if($db->Error) return $this->returnError("failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
  if(($options['coltint'] || $options['sizmis']) && $vsId)
  {
   $db->RunQuery("UPDATE dynarc_".$_AP."_variantstock SET store_".$storeFrom."_qty=store_".$storeFrom."_qty-".$qty." WHERE id='".$vsId."'");
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  }

  // upload to storeTo
  $db->RunQuery("UPDATE dynarc_".$_AP."_items SET store_".$storeTo."_qty=store_".$storeTo."_qty+".
	$qty.",storeqty=storeqty+".$qty." WHERE id='".$_ID."'");
  if($db->Error) return $this->returnError("failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
  if($options['coltint'] || $options['sizmis'])
  {
   if($vsId)
    $db->RunQuery("UPDATE dynarc_".$_AP."_variantstock SET store_".$storeTo."_qty=store_".$storeTo."_qty+".$qty." WHERE id='".$vsId."'");
   else
    $db->RunQuery("INSERT INTO dynarc_".$_AP."_variantstock (item_id,coltint,sizmis,store_".$storeTo."_qty) VALUES('"
		.$_ID."','".$db->Purify($options['coltint'])."','".$db->Purify($options['sizmis'])."','".$qty."')");
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  }

  $this->debug.= "done!\n";

  // REGISTER MOVEMENT
  $this->debug.= "Register movement...";
  $_STORE_FIELDS = "store_id, store_2_id, op_time, uid, mov_act, mov_causal, qty, units, serial_number, lot, 
	ref_at, ref_ap, ref_id, ref_code, ref_name, account_id, notes, doc_ap, doc_id, doc_ref, variant_coltint, variant_sizmis";

  $qry = "INSERT INTO store_movements(".$_STORE_FIELDS.") VALUES('".$storeFrom."','".$storeTo."','".date('Y-m-d H:i',$ctime)."','"
	.$sessInfo['uid']."','".$action."','".$db->Purify($causal)."','".$qty."','".$itemInfo['units']."','"
	.$serialNumber."','".$lot."','".$_AT."','".$_AP."','".$_ID."','".$itemInfo['code_str']."','".$db->Purify($itemInfo['name'])."','"
	.$accountId."','".($options['note'] ? $db->Purify($options['note']) : '')."','"
	.$options['doc_ap']."','".$options['doc_id']."','".($options['doc_ref'] ? $db->Purify($options['doc_ref']) : '')."','"
	.($options['coltint'] ? $db->Purify($options['coltint']) : '')."','"
	.($options['sizmis'] ? $db->Purify($options['sizmis']) : '')."')";

  $db->RunQuery($qry);
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');

  $movId = $db->GetInsertId();
  $this->debug.= "done! Movement has been registered. ID=".$movId."\n";

  $db->Close();

  return $itemInfo;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function SetStoreQty($storeId, $_AP, $_ID, $qty, $colTint="", $sizMis="")
 {
  $storelist = $this->GetStoreList();
  $archiveInfo = $this->GetArchiveInfo($_AP);
  $pricelists = $this->GetPricelists();
  $_PLID = count($pricelists) ? $pricelists[0]['id'] : 0;

  $db = new AlpaDatabase();
  $this->debug.= "Get store qty...";
  $db->RunQuery("SELECT i.cat_id,i.store_".$storeId."_qty AS store_qty, enh.store_".$storeId."_amount AS enh_amount, enh.store_"
	.$storeId."_vat AS enh_vat, store_".$storeId."_total AS enh_total FROM dynarc_".$_AP."_items AS i LEFT JOIN dynarc_"
	.$_AP."_stockenhitm AS enh ON enh.item_id=".$_ID." WHERE id='".$_ID."'");
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  $db->Read();
  $catId = $db->record['cat_id'];
  $oldStoreQty = $db->record['store_qty'];
  $enhAmount = $db->record['enh_amount'] ? $db->record['enh_amount'] : 0;
  $enhVat = $db->record['enh_vat'] ? $db->record['enh_vat'] : 0;
  $enhTotal = $db->record['enh_total'] ? $db->record['enh_total'] : 0;

  $this->debug.= "done! There are ".$oldStoreQty." items.\n";
  
  if($qty && ($qty == $oldStoreQty))
  {
   $db->Close();
   $this->debug.= "No changes is made.\n";
   return true;
  }

  $this->debug.= "Update store qty...";
  $qry = "UPDATE dynarc_".$_AP."_items SET store_".$storeId."_qty=".$qty.", storeqty=".$qty;
  for($c=0; $c < count($storelist); $c++)
  {
   $sid = $storelist[$c]['id'];
   if($sid == $storeId)
    continue;
   $qry.= "+store_".$sid."_qty";
  }
  $qry.= " WHERE id='".$_ID."'";

  $db->RunQuery($qry);
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');

  // update coltint and sizmis
  if($colTint || $sizMis || !$qty)
  {
   $qry = "UPDATE dynarc_".$_AP."_variantstock SET store_".$storeId."_qty='".$qty."' WHERE item_id='".$_ID."'";
   if($colTint && $sizMis) $qry.= " AND coltint='".$db->Purify($colTint)."' AND sizmis='".$db->Purify($sizMis)."'";
   else if($colTint) $qry.= " AND coltint='".$db->Purify($colTint)."'";
   else $qry.= " AND sizmis='".$db->Purify($sizMis)."'";

   $db->RunQuery($qry);
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  }
  $this->debug.= "done!\n";

  if(!$qty)
  {
   // reset
   $this->debug.= "Reset store...";
   $db->RunQuery("UPDATE dynarc_".$_AP."_stockenhitm SET store_".$storeId."_qty=0, store_".$storeId."_amount=0, store_"
		.$storeId."_vat=0, store_".$storeId."_total=0 WHERE item_id='".$_ID."'");
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');

   // get cat tot amount,vat,total
   $db->RunQuery("SELECT a.store_".$storeId."_amount AS cat_amount, a.store_".$storeId."_vat AS cat_vat, a.store_".$storeId."_total AS cat_total, b.id AS enh_id, b.amount AS tot_amount, b.vat AS tot_vat, b.total AS tot_total FROM dynarc_".$_AP."_stockenhcat AS a LEFT JOIN stock_enhancement AS b ON b.ap='".$_AP."' AND b.store_id=".$storeId." WHERE cat_id='".$catId."'");
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
   $db->Read();
   $enhCatAmount = $db->record['cat_amount'] ? $db->record['cat_amount'] : 0;
   $enhCatVat = $db->record['cat_vat'] ? $db->record['cat_vat'] : 0;
   $enhCatTotal = $db->record['cat_total'] ? $db->record['cat_total'] : 0;

   $enhID = $db->record['enh_id'] ? $db->record['enh_id'] : 0;
   $enhTotAmount = $db->record['tot_amount'] ? $db->record['tot_amount'] : 0;
   $enhTotVat = $db->record['tot_vat'] ? $db->record['tot_vat'] : 0;
   $enhTotTotal = $db->record['tot_total'] ? $db->record['tot_total'] : 0;

   $enhCatAmount-= $enhAmount;  $enhCatVat-= $enhVat;  $enhCatTotal-= $enhTotal;
   $enhTotAmount-= $enhAmount;  $enhTotVat-= $enhVat;  $enhTotTotal-= $enhTotal;

   // update stockenhcat
   $db->RunQuery("UPDATE dynarc_".$_AP."_stockenhcat SET store_".$storeId."_amount="
	.($enhCatAmount>0 ? $enhCatAmount : 0).", store_".$storeId."_vat="
	.($enhCatVat>0 ? $enhCatVat : 0).", store_".$storeId."_total=".($enhCatTotal>0 ? $enhCatTotal : 0)." WHERE cat_id='".$catId."'");
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');


   // update stock_enhancement
   if($enhID)
    $db->RunQuery("UPDATE stock_enhancement SET amount='".($enhTotAmount>0 ? $enhTotAmount : 0)."', vat='"
		.($enhTotVat>0 ? $enhTotVat : 0)."', total='".($enhTotTotal>0 ? $enhTotTotal : 0)."' WHERE id='".$enhID."'");
   else
    $db->RunQuery("INSERT INTO stock_enhancement(store_id,ap,amount,vat,total) VALUES('".$storeId."','".$_AP."','"
		.($enhTotAmount>0 ? $enhTotAmount : 0)."','".($enhTotVat>0 ? $enhTotVat : 0)."','".($enhTotTotal>0 ? $enhTotTotal : 0)."')");
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');

   $this->debug.= "done!\n";

   $db->Close();
   return true;
  } // EOF - if $qty == 0

  // Get item info
   $_FIELDS = "cat_id";
   switch($archiveInfo['type'])
   {
	case 'gmart' : case 'gproducts' : case 'gpart' : case 'gmaterial' : {
	 $_FIELDS.= ",baseprice,vat";
	 if($_PLID) $_FIELDS.= ",pricelist_".$_PLID."_baseprice,pricelist_".$_PLID."_mrate,pricelist_".$_PLID."_vat";

	 $qry = "SELECT i.".str_replace(',',',i.',$_FIELDS);

	 if($archiveInfo['type'] != "gproducts")
	 {
	  $qry.= ",v.price AS vendor_price, v.vatrate AS vendor_vatrate";
	  // stockenhitm
	  $qtyQ=""; $amountQ="";
	  for($c=0; $c < count($storelist); $c++)
	  {
	   $qtyQ.= "+enh.store_".$storelist[$c]['id']."_qty";
	   $amountQ.= "+enh.store_".$storelist[$c]['id']."_amount";
	  }
	  $qry.= ", ".substr($qtyQ,1)." AS enh_tot_qty, ".substr($amountQ,1)." AS enh_tot_amount";
	 }

	 $qry.= " FROM dynarc_".$_AP."_items AS i";

	 if($archiveInfo['type'] != "gproducts")
	 {
	  // vendorprices
	  $qry.= " LEFT JOIN dynarc_".$_AP."_vendorprices AS v ON v.item_id=i.id";
	  // stockenhitm
	  $qry.= " LEFT JOIN dynarc_".$_AP."_stockenhitm AS enh ON enh.item_id=i.id";
	 }

	 $qry.= " WHERE i.id='".$_ID."'";

	} break;

    default : $qry = "SELECT ".$_FIELDS." FROM dynarc_".$_AP."_items WHERE id='".$_ID."'"; break;
   } 

   $this->debug.= "Get item info...";
   $db->RunQuery($qry);
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
   $this->debug.= "done!\n";
   if(!$db->Read())
    return $this->returnError("failed!\n Unable to get item #".$_ID." into archive ".$archiveInfo['name'], 'ITEM_DOES_NOT_EXISTS');
   $itemInfo = $db->record;

   $vendorPrice = $itemInfo['vendor_price'] ? $itemInfo['vendor_price'] : 0;
   $vendorVatRate = $itemInfo['vendor_vatrate'] ? $itemInfo['vendor_vatrate'] : 0;

   if(!$price)
    $price = ($_PLID && isset($itemInfo['pricelist_'.$_PLID.'_baseprice'])) ? $itemInfo['pricelist_'.$_PLID.'_baseprice'] : ($itemInfo['baseprice'] ? $itemInfo['baseprice'] : 0);
   if(!$vatRate)
    $vatRate = ($_PLID && isset($itemInfo['pricelist_'.$_PLID.'_vat'])) ? $itemInfo['pricelist_'.$_PLID.'_vat'] : ($itemInfo['vat'] ? $itemInfo['vat'] : 0);

  // EOF - get item info

  if($qty < $oldStoreQty)
  {
   // DOWNLOAD
   $dlqty = $oldStoreQty-$qty;

   $enhTotQty = $itemInfo['enh_tot_qty'] ? $itemInfo['enh_tot_qty'] : 0;
   $enhTotAmount = $itemInfo['enh_tot_amount'] ? $itemInfo['enh_tot_amount'] : 0;

   // Determina il valore di scarico attraverso il metodo standard.
   /* TODO: da fare metodo LIFO e FIFO */
   if($enhTotQty)
    $vendorPrice = $enhTotAmount / $enhTotQty;
   $enhAmount = $vendorPrice * $dlqty;
   $enhVat = $enhAmount ? ($enhAmount/100)*$vendorVatRate : 0;
   $enhTotal = $enhAmount+$enhVat;

   //update stock enhancement
   $this->debug.= "Updating stock enhancement...";
 
   $db->RunQuery("SELECT id,amount,vat,total FROM stock_enhancement WHERE store_id='".$storeId."' AND ap='".$_AP."'");
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
   if($db->Read())
    $db->RunQuery("UPDATE stock_enhancement SET amount='".($db->record['amount']-$enhAmount)."',vat='"
		.($db->record['vat']-$enhVat)."',total='".($db->record['total']-$enhTotal)."' WHERE id='".$db->record['id']."'");
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');

   // - update stockenhitm
   $db->RunQuery("UPDATE dynarc_".$_AP."_stockenhitm SET store_".$storeId."_qty=store_".$storeId."_qty-".$dlqty.", store_".$storeId."_amount=store_".$storeId."_amount-".$enhAmount.", store_".$storeId."_vat=store_".$storeId."_vat-".$enhVat.", store_".$storeId."_total=store_".$storeId."_total-".$enhTotal." WHERE item_id='".$_ID."'");
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  
   // - update stockenhcat
   $db->RunQuery("UPDATE dynarc_".$_AP."_stockenhcat SET store_".$storeId."_amount=store_".$storeId."_amount-".$enhAmount.", store_".$storeId."_vat=store_".$storeId."_vat-".$enhVat.", store_".$storeId."_total=store_".$storeId."_total-".$enhTotal." WHERE cat_id='".$itemInfo['cat_id']."'");
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');

   $this->debug.= "done!\n"; // EOF - update stock enhancement
  } // EOF - DOWNLOAD
  else
  {
   // UPLOAD
   $ulqty = $qty-$oldStoreQty;

   $enhAmount = $vendorPrice * $ulqty;
   $enhVat = $enhAmount ? ($enhAmount/100)*$vendorVatRate : 0;
   $enhTotal = $enhAmount+$enhVat;

   $enhTotAmount = $vendorPrice * $qty;
   $enhTotVat = $enhTotAmount ? ($enhTotAmount/100)*$vendorVatRate : 0;
   $enhTotTotal = $enhTotAmount+$enhTotVat;

   //update stock enhancement
   $this->debug.= "Updating stock enhancement...";

   $db->RunQuery("SELECT id,amount,vat,total FROM stock_enhancement WHERE store_id='".$storeId."' AND ap='".$_AP."'");
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
   if($db->Read())
    $db->RunQuery("UPDATE stock_enhancement SET amount='".($db->record['amount']+$enhAmount)."',vat='"
		.($db->record['vat']+$enhVat)."',total='".($db->record['total']+$enhTotal)."' WHERE id='".$db->record['id']."'");
   else
    $db->RunQuery("INSERT INTO stock_enhancement (store_id,ap,amount,vat,total) VALUES('".$storeId."','".$_AP."','".$enhAmount."','"
		.$enhVat."','".$enhTotal."')");
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');

   // - update stockenhitm
   $db->RunQuery("INSERT INTO dynarc_".$_AP."_stockenhitm (item_id,store_".$storeId."_qty,store_".$storeId."_amount,store_"
	.$storeId."_vat,store_".$storeId."_total) VALUES('".$_ID."','".$qty."','".$enhTotAmount."','".$enhTotVat."','".$enhTotTotal."')
ON DUPLICATE KEY UPDATE store_".$storeId."_qty=store_".$storeId."_qty+".$ulqty.", store_".$storeId."_amount=store_".$storeId."_amount+".$enhAmount.", store_".$storeId."_vat=store_".$storeId."_vat+".$enhVat.", store_".$storeId."_total=store_".$storeId."_total+".$enhTotal);
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  
   // - update stockenhcat
   $db->RunQuery("INSERT INTO dynarc_".$_AP."_stockenhcat (cat_id,store_".$storeId."_amount,store_".$storeId."_vat,store_".$storeId."_total) VALUES('"
	.$itemInfo['cat_id']."','".$enhTotAmount."','".$enhTotVat."','".$enhTotTotal."')
ON DUPLICATE KEY UPDATE store_".$storeId."_amount=store_".$storeId."_amount+".$enhAmount.", store_".$storeId."_vat=store_".$storeId."_vat+"
	.$enhVat.", store_".$storeId."_total=store_".$storeId."_total+".$enhTotal);
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');

   $this->debug.= "done!\n"; // EOF - update stock enhancement
  } // EOF - UPLOAD

  return true;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function ResetStoreQty($_DATA)
 {
  global $_BASE_PATH, $_SHELL_CMD_PATH;

  $storelist = $this->GetStoreList();

  $_ARCHIVES = array();		// lista degli archivi coinvolti per effettuare alla fine il ResetStockEnhancement

  $amazonExists = false;
  if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."amazonmws.php"))
   $amazonExists = true;
  $amazonItems = array();

  $db = new AlpaDatabase(); 
  for($c=0; $c < count($_DATA); $c++)
  {
   $_AP = $_DATA[$c]['ap'];
   $_ID = $_DATA[$c]['id'];
   $_QTY_BY_STORE = $_DATA[$c]['qtybystore'];

   if(!$_ARCHIVES[$_AP]) $_ARCHIVES[$_AP] = true;

   $totQty = 0;
   // Get total quantity
   reset($_QTY_BY_STORE);
   while(list($storeId,$qty) = each($_QTY_BY_STORE)) { $totQty+= $qty; }

   // Set store qty
   $qry = "UPDATE dynarc_".$_AP."_items SET storeqty='".$totQty."'";
   for($i=0; $i < count($storelist); $i++)
   {
    $sid = $storelist[$i]['id'];
    $qry.= ",store_".$sid."_qty='".($_QTY_BY_STORE[$sid] ? $_QTY_BY_STORE[$sid] : 0)."'";
   }
   $qry.= " WHERE id='".$_ID."'";

   $db->RunQuery($qry);
   if($db->Error) return $this->returnError("Unable to reset store qty for article #".$_ID."!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');

   if($amazonExists)
   {
    $qry = "SELECT sku FROM product_sku WHERE ref_ap='".$_AP."' AND ref_id='".$_ID."' AND trash='0' AND (referrer='amazon' OR referrer='')";
    if($_DATA[$c]['coltint'])	$qry.= " AND coltint='".$_DATA[$c]['coltint']."'";
    if($_DATA[$c]['sizmis'])	$qry.= " AND sizmis='".$_DATA[$c]['sizmis']."'";

    $db->RunQuery($qry);
    if($db->Read() && $db->record['sku'])
	 $amazonItems[] = array('ap'=>$_AP, 'id'=>$_ID, 'coltint'=>$_DATA[$c]['coltint'], 'sizmis'=>$_DATA[$c]['sizmis'], 'sku'=>$db->record['sku']);
   }

  }
  $db->Close();

  // Reset stock enhancement
  reset($_ARCHIVES);
  while(list($_AP,$v) = each($_ARCHIVES))
  {
   $ret = $this->ResetStockEnhancement($_AP);
   if(!$ret) return $this->returnError();
  }

  // AMAZON
  if($amazonExists)
  {
   $_CMD = "";
   if(count($amazonItems))
   {
    for($c=0; $c < count($amazonItems); $c++)
    {
	 $ret = GShell("store get-qty -ap '".$amazonItems[$c]['ap']."' -id '".$amazonItems[$c]['id']."' -coltint `".$amazonItems[$c]['coltint']."` -sizmis `".$amazonItems[$c]['sizmis']."`", $this->sessid, $this->shellid);
	 if(!$ret['error']) $_CMD.= " -sku '".$amazonItems[$c]['sku']."' -qty '".$ret['outarr']['tot_qty']."'";
    }
   }

   if($_CMD)
    GShell("amazonmws update-store-qty".$_CMD, $this->sessid, $this->shellid);
  }
  // EOF - AMAZON

  return true;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function ResetStockEnhancement($_AP)
 {
  $storelist = $this->GetStoreList();
  $archiveInfo = $this->GetArchiveInfo($_AP);

  $_STOCK_ENH_ITM = array();
  $_STOCK_ENH_CAT = array();
  $_STOCK_ENH = array();

  $db = new AlpaDatabase();
  $this->debug = "Reset all stock enhancement for archive ".$archiveInfo['name']."...";
  // Empty stock enhancement
  $db->RunQuery("DELETE FROM stock_enhancement WHERE ap='".$_AP."'");
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  $db->RunQuery("TRUNCATE TABLE dynarc_".$_AP."_stockenhcat");
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  $db->RunQuery("TRUNCATE TABLE dynarc_".$_AP."_stockenhitm");
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  $this->debug.= "done!\n";

  // Get products info
  $this->debug.= "Get informations for all products...";
  $qry = "SELECT i.id,i.cat_id,i.storeqty";
  for($c=0; $c < count($storelist); $c++)
   $qry.= ",store_".$storelist[$c]['id']."_qty";

  $qry.= ", v.price AS vendor_price, v.vatrate AS vendor_vatrate FROM dynarc_".$_AP."_items AS i";
  $qry.= " LEFT JOIN dynarc_".$_AP."_vendorprices AS v ON v.item_id=i.id";
  $qry.= " WHERE i.trash='0' ORDER BY i.cat_id ASC";

  $db->RunQuery($qry);
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR'); 
  $this->debug.= "done!\n";

  // Get enhancement
  while($db->Read())
  {
   $_ID = $db->record['id'];
   $catId = $db->record['cat_id'];
   if(!$db->record['storeqty'])
	continue;

   $_STOCK_ENH_ITM[$_ID] = array();
   if(!is_array($_STOCK_ENH_CAT[$catId]))	$_STOCK_ENH_CAT[$catId] = array();

   for($c=0; $c < count($storelist); $c++)
   {
	$sid = $storelist[$c]['id'];
	$qty = $db->record['store_'.$sid.'_qty'];
	$amount = $qty ? $qty*$db->record['vendor_price'] : 0;
    $vat = $amount ? ($amount/100)*$db->record['vendor_vatrate'] : 0;
    $total = $amount+$vat;

	if(!is_array($_STOCK_ENH[$sid]))				$_STOCK_ENH[$sid] = array('amount'=>0, 'vat'=>0, 'total'=>0);
	if(!is_array($_STOCK_ENH_CAT[$catId][$sid]))	$_STOCK_ENH_CAT[$catId][$sid] = array('amount'=>0, 'vat'=>0, 'total'=>0);

	$_STOCK_ENH_ITM[$_ID][$sid] = array('qty'=>$qty, 'amount'=>$amount, 'vat'=>$vat, 'total'=>$total);
	$_STOCK_ENH_CAT[$catId][$sid]['amount']+= $amount;
	$_STOCK_ENH_CAT[$catId][$sid]['vat']+= $vat;
	$_STOCK_ENH_CAT[$catId][$sid]['total']+= $total;

	$_STOCK_ENH[$sid]['amount']+= $amount;
	$_STOCK_ENH[$sid]['vat']+= $vat;
	$_STOCK_ENH[$sid]['total']+= $total;
   }
  }

  // Save stock-enh-itm
  $this->debug.= "Save stock-enh-itm...";
  reset($_STOCK_ENH_ITM);
  while(list($_ID,$stores) = each($_STOCK_ENH_ITM))
  {
   $qry = "INSERT INTO dynarc_".$_AP."_stockenhitm (item_id";
   $values = "'".$_ID."'";

   reset($stores);
   while(list($sid,$enh) = each($stores))
   {
	$qry.= ",store_".$sid."_qty,store_".$sid."_amount,store_".$sid."_vat,store_".$sid."_total";
	$values.= ",'".$enh['qty']."','".$enh['amount']."','".$enh['vat']."','".$enh['total']."'";
   }

   $qry.= ") VALUES(".$values.")";
   $db->RunQuery($qry);
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR'); 
  }
  $this->debug.= "done!\n";

  // Save stock-enh-cat
  $this->debug.= "Save stock-enh-cat...";
  reset($_STOCK_ENH_CAT);
  while(list($catId,$stores) = each($_STOCK_ENH_CAT))
  {
   $qry = "INSERT INTO dynarc_".$_AP."_stockenhcat (cat_id";
   $values = "'".$catId."'";

   reset($stores);
   while(list($sid,$enh) = each($stores))
   {
	$qry.= ",store_".$sid."_amount,store_".$sid."_vat,store_".$sid."_total";
	$values.= ",'".$enh['amount']."','".$enh['vat']."','".$enh['total']."'";
   }

   $qry.= ") VALUES(".$values.")";
   $db->RunQuery($qry);
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR'); 
  }
  $this->debug.= "done!\n";

  // Save stock enhancement
  $this->debug.= "Save stock-enhancement...";
  reset($_STOCK_ENH);
  while(list($sid,$data) = each($_STOCK_ENH))
  {
   $db->RunQuery("INSERT INTO stock_enhancement (store_id,ap,amount,vat,total) VALUES('".$sid."','".$_AP."','"
	.$data['amount']."','".$data['vat']."','".$data['total']."')");
   if($db->Error) return $this->returnError("Unable to update stock-enhancement!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR'); 
  }
  $this->debug.= "done!\n";

  $db->Close();
  return true;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function GetStoreQty($_AP, $_ID, $storeId=0, $options=array(), $_DB=null)
 {
  if(!$storeId)
  {
   $storelist = $this->GetStoreList();
   if(!$storelist) return $this->returnError();

   $_STORE_FIELDS = "";
   for($c=0; $c < count($storelist); $c++)
	$_STORE_FIELDS.= ",store_".$storelist[$c]['id']."_qty";
  }    

  $this->debug.= "Get store qty...";

  $db = $_DB ? $_DB : new AlpaDatabase();
  if($storeId && ($options['coltint'] || $options['sizmis']))
  {
   $qry = "SELECT i.storeqty AS tot_qty, i.store_".$storeId."_qty AS store_qty ,vs.store_".$storeId."_qty AS var_qty"
	." FROM dynarc_".$_AP."_items AS i LEFT JOIN dynarc_".$_AP."_variantstock AS vs ON vs.item_id=".$_ID;
   if($options['coltint'] && $options['sizmis'])	
	$qry.= " AND vs.coltint='".$db->Purify($options['coltint'])."' AND vs.sizmis='".$db->Purify($options['sizmis'])."'";
   else if($options['coltint']) $qry.= " AND vs.coltint='".$db->Purify($options['coltint'])."'";
   else $qry.= " AND vs.sizmis='".$db->Purify($options['sizmis'])."'";

   $db->RunQuery($qry);
   if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
   if(!$db->Read()) return $this->returnError("failed!\n Item #".$_ID." does not exists into archive ".$_AP.".", 'ITEM_DOES_NOT_EXISTS');
   $ret = array('tot_qty'=>$db->record['tot_qty'], 'store_qty'=>$db->record['store_qty'], 'var_qty'=>$db->record['var_qty']);
   $db->Close();
   return $ret;
  }
  
  $qry = "SELECT storeqty AS tot_qty".($storeId ? ",store_".$storeId."_qty AS store_qty" : $_STORE_FIELDS)
	." FROM dynarc_".$_AP."_items WHERE id='".$_ID."'";
  $db->RunQuery($qry);
  if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  if(!$db->Read()) return $this->returnError("failed!\n Item #".$_ID." does not exists into archive ".$_AP.".", 'ITEM_DOES_NOT_EXISTS');
  $ret = $db->record;
  $ret['variants'] = array();

  $qry = "SELECT coltint,sizmis".($storeId ? ",store_".$storeId."_qty AS store_qty" : $_STORE_FIELDS)
	." FROM dynarc_".$_AP."_variantstock WHERE item_id='".$_ID."'";
  if($options['coltint'] && $options['sizmis'])	
   $qry.= " AND coltint='".$db->Purify($options['coltint'])."' AND sizmis='".$db->Purify($options['sizmis'])."'";
  else if($options['coltint']) 	$qry.= " AND coltint='".$db->Purify($options['coltint'])."'";
  else if($options['sizmis'])	$qry.= " AND sizmis='".$db->Purify($options['sizmis'])."'";
  $qry.= " ORDER BY coltint ASC, sizmis ASC";
  $db->RunQuery($qry);
  //if($db->Error) return $this->returnError("failed!\n MySQL Error: ".$db->Error, 'MYSQL_ERROR');
  while($db->Read())
  {
   $ret['variants'][] = $db->record;   
  }

  if(!$_DB) $db->Close();
  $this->debug.= "done!\n";
  return $ret;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function EditMovement($_ID, $opt=array())
 {
  $this->debug.= "Get movement info...";

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM store_movements WHERE id='".$_ID."'");
  if($db->Error) return $this->returnError("failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
  if(!$db->Read()) return $this->returnError("failed!\nMovement #".$_ID." does not exists!", 'MOVEMENT_DOES_NOT_EXISTS');
  $movInfo = $db->record;
  $db->Close();

  $this->debug.= "done!\n";

  $oldQty = $movInfo['qty'];
  $qty = $movInfo['qty'];
  if(isset($opt['qty'])) $qty = $opt['qty'];

  $oldStoreId = $movInfo['store_id'];
  $newStoreId = $opt['store_id'];
  $oldStore2Id = $movInfo['store_2_id'];	// only for transfers
  $newStore2Id = $opt['store_2_id'];		// only for transfers

  if($movInfo['ref_ap'] && $movInfo['ref_id'])
  {
   $ap = $movInfo['ref_ap']; $id = $movInfo['ref_id'];
	
   switch($movInfo['mov_act'])
   {
    case 1 : { // UPLOAD
		 if(($qty == $oldQty) && ($newStoreId == $oldStoreId) && ($movInfo['variant_coltint'] == $opt['coltint']) && ($movInfo['variant_sizmis'] == $opt['sizmis']))
		  break;

		 /* RESTORE */
	     // get totqty from old store
	     $ret = $this->GetStoreQty($ap, $id, $oldStoreId, array('coltint'=>$movInfo['variant_coltint'], 'sizmis'=>$movInfo['variant_sizmis']));
	     if(!$ret) return $this->returnError();
	   	 $oldStore = array('coltint'=>$movInfo['variant_coltint'], 'sizmis'=>$movInfo['variant_sizmis']);
		 $oldStore['qty'] = ($oldStore['coltint'] || $oldStore['sizmis']) ? $ret['var_qty'] : $ret['store_qty'];
	     // download from old store
	     $ret = $this->SetStoreQty($oldStoreId, $ap, $id, $oldStore['qty']-$movInfo['qty'], $oldStore['coltint'], $oldStore['sizmis']);
	     if(!$ret) return $this->returnError();

		 /* UPDATE */
		 // get totqty from new store
		 $ret = $this->GetStoreQty($ap, $id, $newStoreId, array('coltint'=>$opt['coltint'], 'sizmis'=>$opt['sizmis']));
		 if(!$ret) return $this->returnError();
		 $newStore = array('coltint'=>$opt['coltint'], 'sizmis'=>$opt['sizmis']);
		 $newStore['qty'] = ($newStore['coltint'] || $newStore['sizmis']) ? $ret['var_qty'] : $ret['store_qty'];
	     // upload into new store
	   	 $ret = $this->SetStoreQty($newStoreId, $ap, $id, $newStore['qty']+$qty, $newStore['coltint'], $newStore['sizmis']);
		 if(!$ret) return $this->returnError();
		} break;

	case 2 : { // DOWNLOAD

		 if(($qty == $oldQty) && ($newStoreId == $oldStoreId) && ($movInfo['variant_coltint'] == $opt['coltint']) && ($movInfo['variant_sizmis'] == $opt['sizmis']))
		   break;

		 /* RESTORE */
	     // get totqty from old store
	     $ret = $this->GetStoreQty($ap, $id, $oldStoreId, array('coltint'=>$movInfo['variant_coltint'], 'sizmis'=>$movInfo['variant_sizmis']));
	     if(!$ret) return $this->returnError();
	   	 $oldStore = array('coltint'=>$movInfo['variant_coltint'], 'sizmis'=>$movInfo['variant_sizmis']);
		 $oldStore['qty'] = ($oldStore['coltint'] || $oldStore['sizmis']) ? $ret['var_qty'] : $ret['store_qty'];
	     // upload into old store
	     $ret = $this->SetStoreQty($oldStoreId, $ap, $id, $oldStore['qty']+$movInfo['qty'], $oldStore['coltint'], $oldStore['sizmis']);
	     if(!$ret) return $this->returnError();

		 /* UPDATE */
		 // get totqty from new store
		 $ret = $this->GetStoreQty($ap, $id, $newStoreId, array('coltint'=>$opt['coltint'], 'sizmis'=>$opt['sizmis']));
		 if(!$ret) return $this->returnError();
		 $newStore = array('coltint'=>$opt['coltint'], 'sizmis'=>$opt['sizmis']);
		 $newStore['qty'] = ($newStore['coltint'] || $newStore['sizmis']) ? $ret['var_qty'] : $ret['store_qty'];
	     // download from new store
	   	 $ret = $this->SetStoreQty($newStoreId, $ap, $id, $newStore['qty']-$qty, $newStore['coltint'], $newStore['sizmis']);
		 if(!$ret) return $this->returnError();
		} break;

    case 3 : { // TRANSFER

		 if(($qty == $oldQty) && ($newStoreId == $oldStoreId) && ($newStore2Id == $oldStore2Id) && ($movInfo['variant_coltint'] == $opt['coltint']) && ($movInfo['variant_sizmis'] == $opt['sizmis']))
		   break;

		 /* RESTORE */
	     // get totqty from old store
	     $ret = $this->GetStoreQty($ap, $id, $oldStoreId, array('coltint'=>$movInfo['variant_coltint'], 'sizmis'=>$movInfo['variant_sizmis']));
	     if(!$ret) return $this->returnError();
	   	 $oldStore = array('coltint'=>$movInfo['variant_coltint'], 'sizmis'=>$movInfo['variant_sizmis']);
		 $oldStore['qty'] = ($oldStore['coltint'] || $oldStore['sizmis']) ? $ret['var_qty'] : $ret['store_qty'];
	     // upload into old store
	     $ret = $this->SetStoreQty($oldStoreId, $ap, $id, $oldStore['qty']+$movInfo['qty'], $oldStore['coltint'], $oldStore['sizmis']);
	     if(!$ret) return $this->returnError();

		 if($oldStore2Id)
		 {
		  // get totqty from old store 2
	      $ret = $this->GetStoreQty($ap, $id, $oldStore2Id, array('coltint'=>$movInfo['variant_coltint'], 'sizmis'=>$movInfo['variant_sizmis']));
	      if(!$ret) return $this->returnError();
	      $oldStore2 = array('coltint'=>$movInfo['variant_coltint'], 'sizmis'=>$movInfo['variant_sizmis']);
	      $oldStore2['qty'] = ($oldStore2['coltint'] || $oldStore2['sizmis']) ? $ret['var_qty'] : $ret['store_qty'];
	      // download from old store 2
	      $ret = $this->SetStoreQty($oldStore2Id, $ap, $id, $oldStore2['qty']-$movInfo['qty'], $oldStore2['coltint'], $oldStore2['sizmis']);
	      if(!$ret) return $this->returnError();
		 }


		 /* UPDATE */
		 // get totqty from new store
		 $ret = $this->GetStoreQty($ap, $id, $newStoreId, array('coltint'=>$opt['coltint'], 'sizmis'=>$opt['sizmis']));
		 if(!$ret) return $this->returnError();
		 $newStore = array('coltint'=>$opt['coltint'], 'sizmis'=>$opt['sizmis']);
		 $newStore['qty'] = ($newStore['coltint'] || $newStore['sizmis']) ? $ret['var_qty'] : $ret['store_qty'];
	     // download from new store
	   	 $ret = $this->SetStoreQty($newStoreId, $ap, $id, $newStore['qty']-$qty, $newStore['coltint'], $newStore['sizmis']);
		 if(!$ret) return $this->returnError();

		 if($newStore2Id)
		 {
	      // get totqty from new store 2
	      $ret = $this->GetStoreQty($ap, $id, $newStore2Id, array('coltint'=>$opt['coltint'], 'sizmis'=>$opt['sizmis']));
	      if(!$ret) return $this->returnError();
	      $newStore2 = array('coltint'=>$opt['coltint'], 'sizmis'=>$opt['sizmis']);
	      $newStore2['qty'] = ($newStore2['coltint'] || $newStore2['sizmis']) ? $ret['var_qty'] : $ret['store_qty'];
	      // upload into new store 2
	   	  $ret = $this->SetStoreQty($newStore2Id, $ap, $id, $newStore2['qty']+$qty, $newStore2['coltint'], $newStore2['sizmis']);
		  if(!$ret) return $this->returnError();
		 }
		} break;
   }
  }
  else
  {
   /* TODO: nel caso non ci sia il riferimento all'articolo */
  }

  

  // Aggiornamento dati

  $this->debug.= "Updating data...";
  $db = new AlpaDatabase();
  $q = "";
  if($opt['ctime'])					$q.= ",op_time='".$opt['ctime']."'";
  if($opt['store_id'])				$q.= ",store_id='".$opt['store_id']."'";
  if(isset($opt['causal']))			$q.= ",mov_causal='".$opt['causal']."'";
  if($opt['qty'])					$q.= ",qty='".$opt['qty']."'";
  if($opt['units'])					$q.= ",units='".$opt['units']."'";
  if(isset($opt['lot']))			$q.= ",lot='".$db->Purify($opt['lot'])."'";
  if(isset($opt['serialnumber']))	$q.= ",serial_number='".$db->Purify($opt['serialnumber'])."'";
  if(isset($opt['coltint']))		$q.= ",variant_coltint='".$db->Purify($opt['coltint'])."'";
  if(isset($opt['sizmis']))			$q.= ",variant_sizmis='".$db->Purify($opt['sizmis'])."'";
  if(isset($opt['note']))			$q.= ",notes='".$db->Purify($opt['note'])."'";
  if(isset($opt['vendor_id']))		$q.= ",ref_vendor_id='".$opt['vendor_id']."'";
  if(isset($opt['doc_ap']))			$q.= ",doc_ap='".$opt['doc_ap']."'";
  if(isset($opt['doc_id']))			$q.= ",doc_id='".$opt['doc_id']."'";
  if(isset($opt['doc_ref']))		$q.= ",doc_ref='".$opt['doc_ref']."'";

  $qry = "UPDATE store_movements SET ".ltrim($q,",")." WHERE id='".$_ID."'";
  $db->RunQuery($qry);
  if($db->Error) return $this->returnError("failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
  $this->debug.= "done!\n";
  $db->Close();

  return true;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function DeleteMovement($_ID, $opt=array())
 {
  $this->debug.= "Get movement info...";

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM store_movements WHERE id='".$_ID."'");
  if($db->Error) return $this->returnError("failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
  if(!$db->Read()) return $this->returnError("failed!\nMovement #".$_ID." does not exists!", 'MOVEMENT_DOES_NOT_EXISTS');
  $movInfo = $db->record;
  $db->Close();

  $this->debug.= "done!\n";

  
  if($movInfo['ref_ap'] && $movInfo['ref_id'])
  {
   $ap = $movInfo['ref_ap']; $id = $movInfo['ref_id'];
	
   switch($movInfo['mov_act'])
   {
    case 1 : { // UPLOAD
		 /* RESTORE */
	     // get totqty from store
	     $ret = $this->GetStoreQty($ap, $id, $movInfo['store_id'], array('coltint'=>$movInfo['variant_coltint'], 'sizmis'=>$movInfo['variant_sizmis']));
	     if(!$ret) return $this->returnError();
	   	 $storeInfo = array('coltint'=>$movInfo['variant_coltint'], 'sizmis'=>$movInfo['variant_sizmis']);
		 $storeInfo['qty'] = ($storeInfo['coltint'] || $storeInfo['sizmis']) ? $ret['var_qty'] : $ret['store_qty'];
	     // download from store
	     $ret = $this->SetStoreQty($movInfo['store_id'], $ap, $id, $storeInfo['qty']-$movInfo['qty'], $storeInfo['coltint'], $storeInfo['sizmis']);
	     if(!$ret) return $this->returnError();
		} break;

	case 2 : { // DOWNLOAD
		 /* RESTORE */
	     // get totqty from store
	     $ret = $this->GetStoreQty($ap, $id, $movInfo['store_id'], array('coltint'=>$movInfo['variant_coltint'], 'sizmis'=>$movInfo['variant_sizmis']));
	     if(!$ret) return $this->returnError();
	   	 $storeInfo = array('coltint'=>$movInfo['variant_coltint'], 'sizmis'=>$movInfo['variant_sizmis']);
		 $storeInfo['qty'] = ($storeInfo['coltint'] || $storeInfo['sizmis']) ? $ret['var_qty'] : $ret['store_qty'];
	     // upload into store
	     $ret = $this->SetStoreQty($movInfo['store_id'], $ap, $id, $storeInfo['qty']+$movInfo['qty'], $storeInfo['coltint'], $storeInfo['sizmis']);
	     if(!$ret) return $this->returnError();
		} break;

    case 3 : { // TRANSFER
		 /* RESTORE */
	     // get totqty from store
	     $ret = $this->GetStoreQty($ap, $id, $movInfo['store_id'], array('coltint'=>$movInfo['variant_coltint'], 'sizmis'=>$movInfo['variant_sizmis']));
	     if(!$ret) return $this->returnError();
	   	 $storeInfo = array('coltint'=>$movInfo['variant_coltint'], 'sizmis'=>$movInfo['variant_sizmis']);
		 $storeInfo['qty'] = ($storeInfo['coltint'] || $storeInfo['sizmis']) ? $ret['var_qty'] : $ret['store_qty'];
	     // upload into store
	     $ret = $this->SetStoreQty($movInfo['store_id'], $ap, $id, $storeInfo['qty']+$movInfo['qty'], $storeInfo['coltint'], $storeInfo['sizmis']);
	     if(!$ret) return $this->returnError();

		 if($movInfo['store_2_id'])
		 {
	      // get totqty from store 2
	      $ret = $this->GetStoreQty($ap, $id, $movInfo['store_2_id'], array('coltint'=>$movInfo['variant_coltint'], 'sizmis'=>$movInfo['variant_sizmis']));
	      if(!$ret) return $this->returnError();
	   	  $storeInfo = array('coltint'=>$movInfo['variant_coltint'], 'sizmis'=>$movInfo['variant_sizmis']);
		  $storeInfo['qty'] = ($storeInfo['coltint'] || $storeInfo['sizmis']) ? $ret['var_qty'] : $ret['store_qty'];
	      // download from store 2
	      $ret = $this->SetStoreQty($movInfo['store_2_id'], $ap, $id, $storeInfo['qty']-$movInfo['qty'], $storeInfo['coltint'], $storeInfo['sizmis']);
	      if(!$ret) return $this->returnError();
		 }
		} break;
   }
  }
  else
  {
   /* TODO: nel caso non ci sia il riferimento all'articolo */
  }

  $this->debug.= "Delete movement...";
  $db = new AlpaDatabase();
  $db->RunQuery("DELETE FROM store_movements WHERE id='".$_ID."'");
  if($db->Error) return $this->returnError("failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
  $db->Close();
  $this->debug.= "done!\n";

  return true;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function CheckAndFix($opt=array())
 {
  $this->debug.= "Check and fix store functions:\n";

  $_ARCHIVES = array();
  if($opt['ap'])
  {
   $ret = $this->GetArchiveInfo($opt['ap']);
   if(!$ret) return $this->returnError();
   $_ARCHIVES[] = $ret;
  }
  else if($opt['at'])
  {
   $ret = $this->GetArchiveList($opt['at']);
   if(!$ret) return $this->returnError();
   $_ARCHIVES = $ret;
  }
  else
  {
   $ret = GShell("dynarc extension-info storeinfo",$this->sessid, $this->shellid);
   if($ret['error']) return $this->returnError($ret['message'], $ret['error']);
   $_ARCHIVES = $ret['outarr']['archives'];
  }

  if(!count($_ARCHIVES))
  {
   $this->debug.= "No archives found.\n";
   return true;
  }

  for($c=0; $c < count($_ARCHIVES); $c++)
  {
   $ret = $this->fixArchive($_ARCHIVES[$c]['ap']);
   if(!$ret) return $this->returnError();
  }

  return true;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function fixArchive($_AP)
 {
  $storelist = $this->GetStoreList();
  if(!$storelist) return $this->returnError();

  $changed = false;

  $db = new AlpaDatabase();
  $tb = "dynarc_".$_AP."_items";
  $ret = $db->FieldsInfo($tb);
  
  $q = "";
  for($c=0; $c < count($storelist); $c++)
  {
   $sid = $storelist[$c]['id'];
   if(!$ret['store_'.$sid.'_qty'])
	$q.= ", ADD `store_".$sid."_qty` FLOAT NOT NULL";
  }
  if($q != "")
  {
   $db->RunQuery("ALTER TABLE `".$tb."`".substr($q,1));
   if($db->Error) return $this->returnError("Fix table ".$tb."...failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
   $changed=true;
  }

  //---------------------------------------------------------------------------------------------//
  $tb = "dynarc_".$_AP."_stockenhcat";
  $ret = $db->FieldsInfo($tb);
  if(!$ret || !is_array($ret))
  {
   $query = "CREATE TABLE IF NOT EXISTS `".$tb."` (`cat_id` INT(11) NOT NULL PRIMARY KEY";
   for($c=0; $c < count($storelist); $c++)
   {
	$sid = $storelist[$c]['id'];
    $query.= ", `store_".$sid."_amount` DECIMAL(10,5) NOT NULL, `store_".$sid."_vat` DECIMAL(10,5) NOT NULL, `store_".$sid."_total` DECIMAL(10,5) NOT NULL";
   }
   $query.= ")";
   $db->RunQuery($query);
   if($db->Error) return $this->returnError("Create table ".$tb."...failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
   $changed=true;
  }
  else
  {
   $q = "";
   for($c=0; $c < count($storelist); $c++)
   {
    $sid = $storelist[$c]['id'];
    if(!$ret['store_'.$sid.'_amount'])		$q.= ", ADD `store_".$sid."_amount` DECIMAL(10,5) NOT NULL";
    if(!$ret['store_'.$sid.'_vat'])			$q.= ", ADD `store_".$sid."_vat` DECIMAL(10,5) NOT NULL";
    if(!$ret['store_'.$sid.'_total'])		$q.= ", ADD `store_".$sid."_total` DECIMAL(10,5) NOT NULL";
   }
   if($q != "")
   {
    $db->RunQuery("ALTER TABLE `".$tb."`".substr($q,1));
    if($db->Error) return $this->returnError("Fix table ".$tb."...failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
    $changed=true;
   }
  }


  //---------------------------------------------------------------------------------------------//
  $tb = "dynarc_".$_AP."_stockenhitm";
  $ret = $db->FieldsInfo($tb);
  if(!$ret || !is_array($ret))
  {
   $query = "CREATE TABLE IF NOT EXISTS `".$tb."` (`item_id` INT(11) NOT NULL PRIMARY KEY";
   for($c=0; $c < count($storelist); $c++)
   {
	$sid = $storelist[$c]['id'];
    $query.= ", `store_".$sid."_qty` FLOAT NOT NULL, `store_".$sid."_amount` DECIMAL(10,5) NOT NULL, `store_"
		.$sid."_vat` DECIMAL(10,5) NOT NULL, `store_".$sid."_total` DECIMAL(10,5) NOT NULL";
   }
   $query.= ")";
   $db->RunQuery($query);
   if($db->Error) return $this->returnError("Create table ".$tb."...failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
   $changed=true;
  }
  else
  {
   $q = "";
   for($c=0; $c < count($storelist); $c++)
   {
    $sid = $storelist[$c]['id'];
    if(!$ret['store_'.$sid.'_qty'])			$q.= ", ADD `store_".$sid."_qty` FLOAT NOT NULL";
    if(!$ret['store_'.$sid.'_amount'])		$q.= ", ADD `store_".$sid."_amount` DECIMAL(10,5) NOT NULL";
    if(!$ret['store_'.$sid.'_vat'])			$q.= ", ADD `store_".$sid."_vat` DECIMAL(10,5) NOT NULL";
    if(!$ret['store_'.$sid.'_total'])		$q.= ", ADD `store_".$sid."_total` DECIMAL(10,5) NOT NULL";
   }
   if($q != "")
   {
    $db->RunQuery("ALTER TABLE `".$tb."`".substr($q,1));
    if($db->Error) return $this->returnError("Fix table ".$tb."...failed!\nMySQL Error: ".$db->Error, 'MYSQL_ERROR');
    $changed=true;
   }
  }


  $db->Close();
  if($changed) $this->debug.= "Table ".$tb." has been fixed!\n";
  else $this->debug.= "Archive ".$_AP." is ok!\n";
  return true;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function ShotEvent($event, $args)
 {
  if(!$this->eventListeners) $this->getEventListeners();
  if(is_array($this->eventListeners) && $this->eventListeners[$event])
  {
   $func = $this->eventListeners[$event];
   $ret = $func($args);
   if($ret && is_array($ret) && $ret['error'])
	return $this->returnError($ret['message'], $ret['error']);
  }
  return true;
 }
 //------------------------------------------------------------------------------------------------------------------//

 //------------------------------------------------------------------------------------------------------------------//
 //--- PRIVATE ------------------------------------------------------------------------------------------------------//
 //------------------------------------------------------------------------------------------------------------------//
 private function returnError($message="", $error="")
 {
  if($message)  $this->debug.= $message;
  if($error)	$this->error = $error;
  return false;
 }
 //------------------------------------------------------------------------------------------------------------------//
 private function getEventListeners()
 {
  global $_BASE_PATH, $_STORE_EVENTS;

  if($this->eventListeners) return $this->eventListeners;

  // get event listeners on config-custom file
  if(file_exists($_BASE_PATH."Store2/config-custom.php"))
   include($_BASE_PATH."Store2/config-custom.php");

  $this->eventListeners = $_STORE_EVENTS ? $_STORE_EVENTS : array();
  return $this->eventListeners;
 }
 //------------------------------------------------------------------------------------------------------------------//

}
//-------------------------------------------------------------------------------------------------------------------//
?>


