<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 14-09-2013
 #PACKAGE: gstore
 #DESCRIPTION: Official Gnujiko Store Manager.
 #VERSION: 2.3beta
 #CHANGELOG: 14-09-2013 : Aggiunta funzione store move.
			 24-07-2013 : Aggiunto funzione find.
			 17-12-2012 : Bug fix vari.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_store($args, $sessid, $shellid=null)
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
  case 'delete-movement' : return store_deleteMovement($args, $sessid, $shellid); break;
  
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
 $id = mysql_insert_id();
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT archive_id FROM dynarc_archive_extensions WHERE extension_name='storeinfo'");
 while($db->Read())
 {
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE id='".$db->record['archive_id']."'");
  if($db2->Read())
   $db2->RunQuery("ALTER TABLE dynarc_".$db2->record['tb_prefix']."_items ADD `store_".$id."_qty` FLOAT NOT NULL");
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
   $db2->RunQuery("ALTER TABLE dynarc_".$db2->record['tb_prefix']."_items DROP `store_".$id."_qty`");
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
 $sessInfo = sessionInfo($sessid);

 $apList = array();
 $qtyList = array();
 $ids = array();

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
   case '-serialnumber' : case '-sn' : {$serialNumber=$args[$c+1]; $c++;} break;
   case '-lot' : {$lot=$args[$c+1]; $c++;} break;

   case '-vendorid' : {$vendorId=$args[$c+1]; $c++;} break;
   case '-vendorprice' : {$vendorUnitPrice=$args[$c+1]; $c++;} break;
   case '-vendorvat' : case '-vendorvatrate' : {$vendorVatRate=$args[$c+1]; $c++;} break;
   
   case '-accountid' : {$accountId=$args[$c+1]; $c++;} break;
   case '-notes' : case '-note' : {$notes=$args[$c+1]; $c++;} break;

   case '-docap' : {$docAp=$args[$c+1]; $c++;} break;
   case '-docid' : {$docId=$args[$c+1]; $c++;} break;
   case '-docref' : {$docRef=$args[$c+1]; $c++;} break;
  }

 // Aggiorna lo status del magazzino //
 $db = new AlpaDatabase();
 for($c=0; $c < count($ids); $c++)
 {
  $ap = $apList[$c] ? $apList[$c] : $apList[0];
  $id = $ids[$c];
  $qty = $qtyList[$c] ? $qtyList[$c] : 1;
  
  if($storeId)
   $db->RunQuery("UPDATE dynarc_".$ap."_items SET store_".$storeId."_qty=store_".$storeId."_qty+".$qty.",storeqty=storeqty+".$qty." WHERE id='".$id."'");
  else
   $db->RunQuery("UPDATE dynarc_".$ap."_items SET storeqty=storeqty+".$qty." WHERE id='".$id."'");
 }
 $db->Close();

 if(!$ctime)
  $ctime = time();

 $ret = GShell("pricelists list",$sessid,$shellid);
 $_PRICELISTS = $ret['outarr'];
 $_PLID = 0;
 $_PLGET = "";
 $_PLINFO = array();
 if(count($_PRICELISTS))
 {
  $_PLINFO = $_PRICELISTS[0];
  $_PLID = $_PRICELISTS[0]['id'];
  $_PLGET = "pricelist_".$_PLID."_baseprice,pricelist_".$_PLID."_mrate,pricelist_".$_PLID."_vat";
 }

 if(!$action)
  $action = 1; // UPLOAD //
 
 // Registra i relativi movimenti di magazzino //
 for($c=0; $c < count($ids); $c++)
 {
  $ap = $apList[$c] ? $apList[$c] : $apList[0];
  $id = $ids[$c];
  $qty = $qtyList[$c] ? $qtyList[$c] : 1;

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT archive_type FROM dynarc_archives WHERE tb_prefix='".$ap."' LIMIT 1");
  $db->Read();
  $at = $db->record['archive_type'];
  $db->Close();

  $ret = GShell("dynarc item-info -ap `".$ap."` -id `".$id."` -extget `gmart,vendorprices,pricing` --get-short-description".($_PLGET ? " -get `".$_PLGET."`" : ""),$sessid,$shellid);
  $itemInfo = $ret['outarr'];

  if($vendorId)
  {
   for($i=0; $i < count($itemInfo['vendorprices']); $i++)
   {
    if($itemInfo['vendorprices'][$i]['vendor_id'] != $vendorId)
	 continue;

    $vendorCode = $itemInfo['vendorprices'][$i]['code'];
	if(!$vendorUnitPrice)
     $vendorUnitPrice = $itemInfo['vendorprices'][$i]['price'];
	if(!$vendorVatRate)
     $vendorVatRate = $itemInfo['vendorprices'][$i]['vatrate'];
		
    break;
   }
  }
  else if(!$vendorId && count($itemInfo['vendorprices']))
  {
   $vendorId = $itemInfo['vendorprices'][0]['vendor_id'];
   $vendorCode = $itemInfo['vendorprices'][0]['code'];
   $vendorUnitPrice = $itemInfo['vendorprices'][0]['price'];
   $vendorVatRate = $itemInfo['vendorprices'][0]['vatrate'];
  }
  
  // Ricava il prezzo di listino //
  if($_PLID)
  {
   $baseprice = $itemInfo["pricelist_".$_PLID."_baseprice"];
   $mrate = $itemInfo["pricelist_".$_PLID."_mrate"];
   $price = $baseprice ? $baseprice + (($baseprice/100)*$mrate) : 0;
  }
  else
   $price = $itemInfo["baseprice"];

  $vatRate = $_PLID ? $itemInfo["pricelist_".$_PLID."_vat"] : $itemInfo['vat'];

  $db = new AlpaDatabase();
  $db->RunQuery("INSERT INTO store_movements(store_id, op_time, uid, mov_act, mov_causal, qty, units, serial_number, lot, ref_at, ref_ap, ref_id, ref_code, ref_name, ref_vendor_id, ref_vendor_code, vendor_unitprice, vendor_vatrate, price, vatrate, account_id, notes, doc_ap, doc_id, doc_ref) VALUES('".$storeId."','".date('Y-m-d H:i',$ctime)."','".$sessInfo['uid']."','".$action."','".$db->Purify($causal)."','".$qty."','"
	.$itemInfo['units']."','".$serialNumber."','".$lot."','".$at."','".$ap."','".$id."','".$itemInfo['code_str']."','"
	.$db->Purify($itemInfo['name'])."','".$vendorId."','".$vendorCode."','".$vendorUnitPrice."','".$vendorVatRate."','".$price."','".$vatRate."','"
	.$accountId."','".$db->Purify($notes)."','".$docAp."','".$docId."','".$db->Purify($docRef)."')");

  $movId = mysql_insert_id();

  $db->Close();
 } 

 return array('message'=>"Done!");
}
//-------------------------------------------------------------------------------------------------------------------//
function store_download($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);

 $apList = array();
 $qtyList = array();
 $ids = array();

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
   case '-serialnumber' : case '-sn' : {$serialNumber=$args[$c+1]; $c++;} break;
   case '-lot' : {$lot=$args[$c+1]; $c++;} break;

   case '-vendorid' : {$vendorId=$args[$c+1]; $c++;} break;
   case '-vendorprice' : {$vendorUnitPrice=$args[$c+1]; $c++;} break;
   case '-vendorvat' : case '-vendorvatrate' : {$vendorVatRate=$args[$c+1]; $c++;} break;
   
   case '-price' : {$price=$args[$c+1]; $c++;} break;
   case '-vat' : case '-vatrate' : {$vatRate=$args[$c+1]; $c++;} break;
   case '-discount' : {$discount=$args[$c+1]; $c++;} break;

   case '-accountid' : {$accountId=$args[$c+1]; $c++;} break;
   case '-notes' : case '-note' : {$notes=$args[$c+1]; $c++;} break;

   case '-docap' : {$docAp=$args[$c+1]; $c++;} break;
   case '-docid' : {$docId=$args[$c+1]; $c++;} break;
   case '-docref' : {$docRef=$args[$c+1]; $c++;} break;

   case '--unbook' : $unbook=true; break;
  }

 // Aggiorna lo status del magazzino //
 $db = new AlpaDatabase();
 for($c=0; $c < count($ids); $c++)
 {
  $ap = $apList[$c] ? $apList[$c] : $apList[0];
  $id = $ids[$c];
  $qty = $qtyList[$c] ? $qtyList[$c] : 1;
  
  if($storeId)
   $db->RunQuery("UPDATE dynarc_".$ap."_items SET store_".$storeId."_qty=store_".$storeId."_qty-".$qty.",storeqty=storeqty-".$qty
	.($unbook ? ",booked=booked-".$qty : "")." WHERE id='".$id."'");
  else
   $db->RunQuery("UPDATE dynarc_".$ap."_items SET storeqty=storeqty-".$qty.($unbook ? ",booked=booked-".$qty : "")." WHERE id='".$id."'");
 }
 $db->Close();


 if(!$ctime)
  $ctime = time();

 $ret = GShell("pricelists list",$sessid,$shellid);
 $_PRICELISTS = $ret['outarr'];
 $_PLID = 0;
 $_PLGET = "";
 $_PLINFO = array();
 if(count($_PRICELISTS))
 {
  $_PLINFO = $_PRICELISTS[0];
  $_PLID = $_PRICELISTS[0]['id'];
  $_PLGET = "pricelist_".$_PLID."_baseprice,pricelist_".$_PLID."_mrate,pricelist_".$_PLID."_vat";
 }

 if(!$action)
  $action = 2; // DOWNLOAD //
 
 // Registra i relativi movimenti di magazzino //
 for($c=0; $c < count($ids); $c++)
 {
  $ap = $apList[$c] ? $apList[$c] : $apList[0];
  $id = $ids[$c];
  $qty = $qtyList[$c] ? $qtyList[$c] : 1;

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT archive_type FROM dynarc_archives WHERE tb_prefix='".$ap."' LIMIT 1");
  $db->Read();
  $at = $db->record['archive_type'];
  $db->Close();

  $ret = GShell("dynarc item-info -ap `".$ap."` -id `".$id."` -extget `gmart,vendorprices,pricing` --get-short-description".($_PLGET ? " -get `".$_PLGET."`" : ""),$sessid,$shellid);
  $itemInfo = $ret['outarr'];

  if($vendorId)
  {
   for($i=0; $i < count($itemInfo['vendorprices']); $i++)
   {
    if($itemInfo['vendorprices'][$i]['vendor_id'] != $vendorId)
	 continue;

    $vendorCode = $itemInfo['vendorprices'][$i]['code'];
	if(!$vendorUnitPrice)
     $vendorUnitPrice = $itemInfo['vendorprices'][$i]['price'];
	if(!$vendorVatRate)
     $vendorVatRate = $itemInfo['vendorprices'][$i]['vatrate'];
		
    break;
   }
  }
  else if(!$vendorId && count($itemInfo['vendorprices']))
  {
   $vendorId = $itemInfo['vendorprices'][0]['vendor_id'];
   $vendorCode = $itemInfo['vendorprices'][0]['code'];
   $vendorUnitPrice = $itemInfo['vendorprices'][0]['price'];
   $vendorVatRate = $itemInfo['vendorprices'][0]['vatrate'];
  }
  
  if(!$price)
  {
   if($_PLID)
   {
    $baseprice = $itemInfo["pricelist_".$_PLID."_baseprice"];
	$mrate = $itemInfo["pricelist_".$_PLID."_mrate"];
	$price = $baseprice ? $baseprice + (($baseprice/100)*$mrate) : 0;
   }
   else
    $price = $itemInfo["baseprice"];
  }

  if(!$vatRate)
   $vatRate = $_PLID ? $itemInfo["pricelist_".$_PLID."_vat"] : $itemInfo['vat'];

  if($discount)
  {
   if(strpos($discount,"%") !== false)
    $discountPerc = str_replace("%","",$discount);
   else
    $discountInc = $discount;
  }

  $db = new AlpaDatabase();
  $db->RunQuery("INSERT INTO store_movements(store_id, op_time, uid, mov_act, mov_causal, qty, units, serial_number, lot, ref_at, ref_ap, ref_id, ref_code, ref_name, ref_vendor_id, ref_vendor_code, vendor_unitprice, vendor_vatrate, price, vatrate, discount_perc, discount_inc, account_id, notes, doc_ap, doc_id, doc_ref) VALUES('".$storeId."','".date('Y-m-d H:i',$ctime)."','".$sessInfo['uid']."','".$action."','".$db->Purify($causal)."','".$qty."','"
	.$itemInfo['units']."','".$serialNumber."','".$lot."','".$at."','".$ap."','".$id."','".$itemInfo['code_str']."','"
	.$db->Purify($itemInfo['name'])."','".$vendorId."','".$vendorCode."','".$vendorUnitPrice."','".$vendorVatRate."','".$price."','".$vatRate."','"
	.$discountPerc."','".$discountInc."','".$accountId."','".$db->Purify($notes)."','".$docAp."','".$docId."','".$db->Purify($docRef)."')");

  $movId = mysql_insert_id();

  $db->Close();
 } 


 return array('message'=>"Done!");
}
//-------------------------------------------------------------------------------------------------------------------//
function store_move($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);

 $apList = array();
 $qtyList = array();
 $ids = array();

 $out = "";
 $outArr = array();

 $action = 3; // TRANSFER

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$storeFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$storeTo=$args[$c+1]; $c++;} break;
   case '-ap' : {$apList[]=$args[$c+1]; $c++;} break;
   case '-id' : {$ids[]=$args[$c+1]; $c++;} break;
   case '-qty' : {$qtyList[count($ids)-1]=$args[$c+1]; $c++;} break;

   case '-ctime' : {$ctime=strtotime($args[$c+1]); $c++; } break;
   case '-action' : {$action=$args[$c+1]; $c++;} break;
   case '-causal' : {$causal=$args[$c+1]; $c++;} break;

   case '--generate-ddt' : $generateDDT=true; break;
  }

 $at = "gmart";
 $items = array();
 $db = new AlpaDatabase();
 for($c=0; $c < count($ids); $c++)
 {
  $ap = $apList[$c] ? $apList[$c] : $apList[0];
  $id = $ids[$c];
  $qty = $qtyList[$c] ? $qtyList[$c] : 1;

  // get product info //
  $db->RunQuery("SELECT code_str,name,description,baseprice,vat,units FROM dynarc_".$ap."_items WHERE id='".$id."'");
  $db->Read();
  $items[] = array('ap'=>$ap, 'id'=>$id, 'code'=>$db->record['code_str'], 'name'=>$db->record['name'], 'description'=>$db->record['description'], 'baseprice'=>$db->record['baseprice'], 'vat'=>$db->record['vat'], 'units'=>$db->record['units'], 'qty'=>$qty);
 }
 $db->Close();

 if(!$ctime)
  $ctime = time();

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
   $ret = GShell("dynarc edit-item -ap `commercialdocs` -id `".$docId."` -extset `cdelements.type='article',refap='".$itm['ap']."',refid='"
	.$itm['id']."',code='".$itm['code_str']."',name='''".$itm['name']."''',desc='''".$itm['description']."''',qty='"
	.$itm['qty']."',price='".$itm['baseprice']."',vatrate='".$itm['vat']."',units='".$itm['units']."'`");
  }

 }

 // Aggiorna lo status del magazzino //
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 for($c=0; $c < count($items); $c++)
 {
  $itm = $items[$c];

  // download from storefrom //
  $db->RunQuery("UPDATE dynarc_".$itm['ap']."_items SET store_".$storeFrom."_qty=store_".$storeFrom."_qty-".$itm['qty']." WHERE id='".$itm['id']."'");  

  // upload to storeto //
  $db->RunQuery("UPDATE dynarc_".$itm['ap']."_items SET store_".$storeTo."_qty=store_".$storeTo."_qty+".$itm['qty']." WHERE id='".$itm['id']."'");

  // register movements //
  $db2->RunQuery("INSERT INTO store_movements(store_id, op_time, uid, mov_act, mov_causal, qty, units, ref_at, ref_ap, ref_id, ref_code, ref_name, doc_ap, doc_id) VALUES('".$storeFrom."','".date('Y-m-d H:i',$ctime)."','".$sessInfo['uid']."','".$action."','".$db->Purify($causal)."','".$itm['qty']."','"
	.$itm['units']."','gmart','".$itm['ap']."','".$itm['id']."','".$itm['code']."','".$db->Purify($itm['name'])."','".$docAp."','".$docId."')");
 }
 $db->Close();
 $db2->Close();

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
 if($storeId) $qry.= " AND store_id='".$storeId."'";
 if($from) $qry.= " AND op_time>='".date('Y-m-d H:i',$from)."'";
 if($to) $qry.= " AND op_time<'".date('Y-m-d H:i',$to)."'";
 if($action) $qry.= " AND mov_act='".$action."'";
 if($causal) $qry.= " AND mov_causal='".$causal."'";
 if($serialNumber) $qry.= " AND serial_number='".$serialNumber."'";
 if($lot) $qry.= " AND lot='".$lot."'";
 if($refAt) $qry.= " AND ref_at='".$refAt."'";
 if($refAp) $qry.= " AND ref_ap='".$refAp."'";
 if($refId) $qry.= " AND ref_id='".$refId."'";
 if($refCode) $qry.= " AND ref_code='".$refCode."'";
 if($refVendorCode) $qry.= " AND ref_vendor_code='".$refVendorCode."'";
 if($refVendorId) $qry.= " AND ref_vendor_id='".$refVendorId."'";
 if($refName)
 {
  // da fare //
 }
 if($accountId) $qry.= " AND account_id='".$accountId."'";
 if($docAp) $qry.= " AND doc_ap='".$docAp."'";
 if($docId) $qry.= " AND doc_id='".$docId."'";
 if($docRef) $qry.= " AND doc_ref='".$docRef."'";

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
  $a = array('id'=>$db->record['id'], 'ctime'=>strtotime($db->record['op_time']), 'uid'=>$db->record['uid'], 'action'=>$db->record['mov_act'], 
	'causal'=>$db->record['mov_causal'], 'qty'=>$db->record['qty'], 'units'=>$db->record['units'], 'serialnumber'=>$db->record['serial_number'],
	'lot'=>$db->record['lot'], 'ref_at'=>$db->record['ref_at'], 'ref_ap'=>$db->record['ref_ap'], 'ref_id'=>$db->record['ref_id'], 
	'code'=>$db->record['ref_code'], 'name'=>$db->record['ref_name'], 'vendor_id'=>$db->record['ref_vendor_id'], 
	'vendor_code'=>$db->record['ref_vendor_code'], 'vendor_price'=>$db->record['vendor_unitprice'], 'vendor_vatrate'=>$db->record['vendor_vatrate'],
	'price'=>$db->record['price'], 'vatrate'=>$db->record['vatrate'], 'discount_perc'=>$db->record['discount_perc'], 
	'discount_inc'=>$db->record['discount_inc'], 'account_id'=>$db->record['account_id'], 'notes'=>$db->record['notes'], 'doc_ap'=>$db->record['doc_ap'],
	'doc_id'=>$db->record['doc_id'], 'doc_ref'=>$db->record['doc_ref']);

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
function store_deleteMovement($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $_IDS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$_IDS[]=$args[$c+1]; $c++;} break;
   case '--no-restore-qty' : $noRestoreQty=true; break;
  }

 if(!$noRestoreQty)
 {
  $db = new AlpaDatabase(); $db2 = new AlpaDatabase();
  for($c=0; $c < count($_IDS); $c++)
  {
   $db->RunQuery("SELECT * FROM store_movements WHERE id='".$_IDS[$c]."'");
   $db->Read();
   switch($db->record['mov_act'])
   {
    case 1 : $db2->RunQuery("UPDATE dynarc_".$db->record['ref_ap']."_items SET storeqty=storeqty-".$db->record['qty'].",store_".$db->record['store_id']."_qty=store_".$db->record['store_id']."_qty-".$db->record['qty']." WHERE id='".$db->record['ref_id']."'"); break;
    case 2 : $db2->RunQuery("UPDATE dynarc_".$db->record['ref_ap']."_items SET storeqty=storeqty+".$db->record['qty'].",store_".$db->record['store_id']."_qty=store_".$db->record['store_id']."_qty+".$db->record['qty']." WHERE id='".$db->record['ref_id']."'"); break;
    /* TODO: case 3 : movimenta */
   }
  }
  $db2->Close();
  $db->Close();
 }

 /* REMOVE MOVEMENTS */
 $db = new AlpaDatabase();
 for($c=0; $c < count($_IDS); $c++)
  $db->RunQuery("DELETE FROM store_movements WHERE id='".$_IDS[$c]."'");
 $db->Close();
 $out.= count($_IDS)." movements has been removed!";

 return array('message'=>$out, 'outarr'=>$outArr);
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

