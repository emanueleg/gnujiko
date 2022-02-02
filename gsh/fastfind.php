<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-05-2017
 #PACKAGE: gnujiko-fastfind
 #DESCRIPTION: Gnujiko fast search engine
 #VERSION: 2.18beta
 #CHANGELOG: 21-05-2017 : Aggiunto parametro agentid su funzione contacts.
			 08-12-2016 : Aggiornata funzione productSearch, effettua ricerca anche per cod. variante.
			 19-08-2016 : Aggiornata funzione productsearch (effettua una ricerca anche nelle varianti quanto si fa una ricerca tramite barcode).
			 26-05-2016 : Aggiunto campo vat_id su fastfind contacts
			 20-03-2015 : Aggiunto campi di ritorno su funzione fastfind contacts.
			 11-03-2015 : Bug fix su fastfind products
			 01-12-2014 : AddressSearch bug fix.
			 26-09-2014 : Integrato con address.
			 16-07-2014 : Filtri con asterisco su funzione fastfind contacts.
			 23-06-2014 : Filtri con asterisco su funzione fastfind products.
			 06-06-2014 : Aggiunto campi di ritorno su productSearch
			 31-03-2014 : Aggiunto services
			 13-03-2014 : Aggiunto vehicles.
			 08-03-2014 : Aggiornata funzione fastfind_contactSearch
			 27-02-2014 : Aggiunto users.
			 24-02-2014 : Bug fix su funzione fastfind products
 #DEPENDS: 
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_fastfind($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'contacts' : return fastfind_contactSearch($args, $sessid, $shellid); break;
  case 'products' : return fastfind_productSearch($args, $sessid, $shellid); break;
  case 'services' : return fastfind_serviceSearch($args, $sessid, $shellid); break;
  case 'vehicles' : return fastfind_vehicleSearch($args, $sessid, $shellid); break;
  case 'users' : return fastfind_userSearch($args, $sessid, $shellid); break;
  case 'address' : case 'addresses' : return fastfind_addressSearch($args, $sessid, $shellid); break;

  default : return fastfind_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function fastfind_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function fastfind_contactSearch($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $sessInfo = sessionInfo($sessid);

 $_AP = "rubrica";
 $_FIELDS = array();
 $_CONTACT_FIELDS = array();
 $matchResCount = 0;
 $limit = 10;
 $orderBy = "name ASC";
 $_RESULTS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
   case '-agentid' : {$agentId=$args[$c+1]; $c++;} break;
   case '-fields' : {$fields=$args[$c+1]; $c++;} break;
   case '--contact-fields' : {$contactFields=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
   default : $query=$args[$c]; break;
  }

 $_FIELDS = explode(",",$fields);
 $_CONTACT_FIELDS = explode(",",$contactFields);

 if($catTag)
 {
  // get cat id
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".$_AP."_categories WHERE tag='".$catTag."' AND trash='0' LIMIT 1");
  if($db->Read())
   $catId = $db->record['id'];
  $db->Close();
  $out.= "Filter by cat: #".$catId."\n";
 }

 $m = new GMOD();
 $uQry = $m->userQuery($sessid,null,"dynarc_".$_AP."_items");

 $db = new AlpaDatabase();
 $query = $db->Purify($query);
 if(count($_FIELDS))
 {
  $qry = "";
  for($c=0; $c < count($_FIELDS); $c++)
  {
   $field = $_FIELDS[$c]; 
   if(strpos($query, "*") !== false)
   {
    $modquery = str_replace("*","%",$query);
	$qry.= " OR (".$field." LIKE \"".$modquery."\")";
   }
   else
    $qry.= " OR ((".$field."=\"".$query."\") OR (".$field." LIKE \"".$query."%\") OR (".$field." LIKE \"%"
	.$query."%\") OR (".$field." LIKE \"%".$query."\"))";
  }
  if($qry)				$qry = " AND (".ltrim($qry," OR ").")";
  if($catId)			$qry.= " AND cat_id='".$catId."'";
  if(isset($agentId))	$qry.= " AND agent_id='".$agentId."'";
  if($where)			$qry.= " AND (".$where.")";
  $qry.= " AND trash='0'";

  $cmd = "SELECT COUNT(*) FROM dynarc_".$_AP."_items WHERE (".$uQry.")".$qry;
  $db->RunQuery($cmd);
  $db->Read();
  $matchResCount = $db->record[0];

  $cmd = "SELECT * FROM dynarc_".$_AP."_items WHERE (".$uQry.")".$qry." ORDER BY ".$orderBy." LIMIT ".$limit;
  $db->RunQuery($cmd);
  $db2 = new AlpaDatabase();
  while($db->Read())
  {
   $a = array("id"=>$db->record['id'], "name"=>$db->record['name'], "description"=>$db->record['description'],"ctime"=>$db->record['ctime'],
	"code_str"=>$db->record['code_str'], "taxcode"=>$db->record['taxcode'], "vatnumber"=>$db->record['vatnumber'], 
	"paymentmode"=>$db->record['paymentmode'], "pricelist_id"=>$db->record['pricelist_id'], "distance"=>$db->record['distance'], 
	"fidelitycard"=>$db->record['fidelitycard'], "extranotes"=>$db->record['extranotes'], "agent_id"=>$db->record['agent_id'],
	"user_id"=>$db->record['user_id'], "login"=>$db->record['login'], "pacode"=>$db->record['pa_code'], 
	"assist_avail_hours"=>$db->record['assist_avail_hours'], "default_email"=>$db->record['default_email'], "vat_id"=>$db->record['vat_id']);
   $a['contacts'] = array();
   // get contacts //
   $db2->RunQuery("SELECT * FROM dynarc_".$_AP."_contacts WHERE item_id='".$a['id']."' ORDER BY isdefault DESC,id ASC");
   while($db2->Read())
   {
	$a['contacts'][] = array("id"=>$db2->record['id'], "label"=>$db2->record['label'], "name"=>$db2->record['name'], "address"=>$db2->record['address'],
	"city"=>$db2->record['city'], "zipcode"=>$db2->record['zipcode'], "province"=>$db2->record['province'], "countrycode"=>$db2->record['countrycode'],
	"phone"=>$db2->record['phone'], "phone2"=>$db2->record['phone2'], "fax"=>$db2->record['fax'], "cell"=>$db2->record['cell'], 
	"email"=>$db2->record['email'], "email2"=>$db2->record['email2'], "email3"=>$db2->record['email3'], "skype"=>$db2->record['skype']);
   }
   $_RESULTS[] = $a;
  }
  $db2->Close();
 }
 $db->Close();
 if(count($_CONTACT_FIELDS))
 {
  $qry = "";
  for($c=0; $c < count($_CONTACT_FIELDS); $c++)
  {
   $field = $_CONTACT_FIELDS[$c];
   if(strpos($query, "*") !== false)
   {
    $modquery = str_replace("*","%",$query);
	$qry.= " OR (".$field." LIKE \"".$modquery."\")";
   }
   else
    $qry.= " OR ((".$field."=\"".$query."\") OR (".$field." LIKE \"".$query."%\") OR (".$field." LIKE \"%"
	.$query."%\") OR (".$field." LIKE \"%".$query."\"))";
  }
  if($qry)
   $qry = " AND (".ltrim($qry," OR ").")";

  $cmd = "";
  if(count($_RESULTS))
  {
   $q = "";
   for($c=0; $c < count($_RESULTS); $c++)
    $q.= " AND item_id!='".$_RESULTS[$c]['id']."'";
   $cmd.= " (".ltrim($q," AND ").")".$qry;
  }
  else
   $cmd.= " ".ltrim($qry," AND ");
  
  $db = new AlpaDatabase();
  $lastItemId = 0;
  $db->RunQuery("SELECT * FROM dynarc_".$_AP."_contacts WHERE".$cmd." ORDER BY item_id ASC");
  while($db->Read())
  {
   if($db->record['item_id'] == $lastItemId)
	continue;
   $lastItemId = $db->record['item_id'];
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT * FROM dynarc_".$_AP."_items WHERE id='".$lastItemId."'");
   $db2->Read();
   $m = new GMOD($db2->record['_mod'],$db2->record['uid'],$db2->record['gid'],$db2->record['shgrps'],$db2->record['shusrs']);
   if(!$m->canRead($sessInfo['uid']))
    continue;
   if($catId && ($catId != $db2->record['cat_id']))
	continue;
   if(isset($agentId) && ($agentId != $db2->record['agent_id']))
	continue;
   $a = array("id"=>$db2->record['id'], "name"=>$db2->record['name'], "description"=>$db2->record['description'],"ctime"=>$db2->record['ctime'],
	"code_str"=>$db2->record['code_str'], "taxcode"=>$db2->record['taxcode'], "vatnumber"=>$db2->record['vatnumber'], 
	"paymentmode"=>$db2->record['paymentmode'], "pricelist_id"=>$db2->record['pricelist_id'], "distance"=>$db2->record['distance'], 
	"fidelitycard"=>$db2->record['fidelitycard'], "extranotes"=>$db2->record['extranotes'], "agent_id"=>$db2->record['agent_id'],
	"user_id"=>$db2->record['user_id'], "login"=>$db2->record['login'], "pacode"=>$db2->record['pa_code'], 
	"assist_avail_hours"=>$db2->record['assist_avail_hours'], "default_email"=>$db2->record['default_email'], "vat_id"=>$db2->record['vat_id']);
   $a['contacts'] = array();
   // get contacts //
   $db3 = new AlpaDatabase();
   $db3->RunQuery("SELECT * FROM dynarc_".$_AP."_contacts WHERE item_id='".$a['id']."' ORDER BY isdefault DESC,id ASC");
   while($db3->Read())
   {
	$a['contacts'][] = array("id"=>$db3->record['id'], "label"=>$db3->record['label'], "name"=>$db3->record['name'], "address"=>$db3->record['address'],
	"city"=>$db3->record['city'], "zipcode"=>$db3->record['zipcode'], "province"=>$db3->record['province'], "countrycode"=>$db3->record['countrycode'],
	"phone"=>$db3->record['phone'], "phone2"=>$db3->record['phone2'], "fax"=>$db3->record['fax'], "cell"=>$db3->record['cell'], 
	"email"=>$db3->record['email'], "email2"=>$db3->record['email2'], "email3"=>$db3->record['email3'], "skype"=>$db3->record['skype']);
   }
   $db3->Close();
   $_RESULTS[] = $a;
   $matchResCount++;
   if($matchResCount >= $limit)
    break;
   $db2->Close();
  }
  $db->Close();
 }
 
 $outArr['count'] = $matchResCount;
 $outArr['results'] = $_RESULTS;

 $out.= !$matchResCount ? "Match not found for '".$query."'" : $matchResCount." results found!";

 if($verbose)
 {
  $out.= "\n";
  for($c=0; $c < count($outArr['results']); $c++)
  {
   $res = $outArr['results'][$c];
   $out.= $res['code_str']." - ".$res['name']." <i>".$res['contacts'][0]['address']." - ".$res['contacts'][0]['city']."</i>\n";
  }
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function fastfind_productSearch($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $sessInfo = sessionInfo($sessid);

 $_AT = "gmart";
 $_FIELDS = array();

 $matchResCount = 0;
 $limit = 10;
 $orderBy = "name ASC";
 $_RESULTS = array();
 $_ARCHIVES = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-at' : {$_AT=$args[$c+1]; $c++;} break;
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
   case '-fields' : {$fields=$args[$c+1]; $c++;} break;
   case '-vencode' : {$vencode=$args[$c+1]; $c++;} break; 						// ricerca tramite codice articolo fornitore
   case '-vendorid' : {$vendorId=$args[$c+1]; $c++;} break; 					// ricerca/filtra la ricerca per un determinato fornitore
   case '-vendor' : case '-vendorname' : {$vendorName=$args[$c+1]; $c++;} break;	// ricerca tramite fornitore

   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
   default : $query=$args[$c]; break;
  }

 if($vencode) // se la ricerca avviene tramite cod. art. fornitore, reindirizza la chiamata alla funzione apposita.
  return fastfind_productSearchByVencode($args, $sessid, $shellid);
 else if($vendorId || $vendorName) // ricerca tra tutti gli articoli di un determinato fornitore
  return fastfind_productSearchByVendor($args, $sessid, $shellid);

 $_FIELDS = explode(",",$fields);

 if($catTag)
 {
  if(!$_AP)
   $_AP = $_AT;
  // get cat id
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".$_AP."_categories WHERE tag='".$catTag."' AND trash='0' LIMIT 1");
  if($db->Read())
   $catId = $db->record['id'];
  $db->Close();
 }

 if($_AP)
  $_ARCHIVES[] = array("prefix"=>$_AP);
 else
 {
  $ret = GShell("dynarc archive-list -a -type '".$_AT."'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  for($c=0; $c < count($ret['outarr']); $c++)
   $_ARCHIVES[] = $ret['outarr'][$c];
 }

 /* GET STORES */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM stores WHERE 1");
 while($db->Read())
  $storeIds[] = $db->record['id'];
 $db->Close();

 /* PREPARE QUERY */
 $db = new AlpaDatabase();
 $query = $db->Purify($query);
 $qry = "";
 if($query)
 {
  for($c=0; $c < count($_FIELDS); $c++)
  {
   $field = $_FIELDS[$c]; 
   if(strpos($query, "*") !== false)
   {
    $modquery = str_replace("*","%",$query);
	$qry.= " OR (".$field." LIKE \"".$modquery."\")";
   }
   else
    $qry.= " OR ((".$field."=\"".$query."\") OR (".$field." LIKE \"".$query."%\") OR (".$field." LIKE \"%"
	.$query."%\") OR (".$field." LIKE \"%".$query."\"))";
  }
 }
 if($qry)
  $qry = " AND (".ltrim($qry," OR ").")";
 if($catId)
  $qry.= " AND cat_id='".$catId."'";
 if($where)
  $qry.= " AND (".$where.")";
 $qry.= " AND trash='0'";
 $db->Close();

 $m = new GMOD();
 if(count($_ARCHIVES) == 1)
 {
  $_AP = $_ARCHIVES[0]['prefix'];
  $uQry = $m->userQuery($sessid,null,"dynarc_".$_AP."_items");
  $cmd = "SELECT COUNT(*) FROM dynarc_".$_AP."_items WHERE (".$uQry.")".$qry;
  $db = new AlpaDatabase();
  $db->RunQuery($cmd);
  $db->Read();
  $matchResCount = $db->record[0];
  $db->Close();
 }
 else
 {
  /* PREPARE COUNT QUERY */
  $countQry = "";
  for($c=0; $c < count($_ARCHIVES); $c++)
  {
   $_AP = $_ARCHIVES[$c]['prefix'];
   $uQry = $m->userQuery($sessid,null,"dynarc_".$_AP."_items");
   $countQry.= " UNION SELECT '".$_AP."' AS tb_prefix,id FROM dynarc_".$_AP."_items WHERE (".$uQry.")".$qry;
  }
  $countQry = "SELECT COUNT(*) FROM (".ltrim($countQry," UNION ").") AS qryelements";
  $db = new AlpaDatabase();
  $db->RunQuery($countQry);
  $db->Read();
  $matchResCount = $db->record[0];
  $db->Close();
 }


 if(count($_ARCHIVES) == 1)
 {
  $_AP = $_ARCHIVES[0]['prefix'];
  $cmd = "SELECT * FROM dynarc_".$_AP."_items WHERE (".$uQry.")".$qry." ORDER BY ".$orderBy.($limit ? " LIMIT ".$limit : "");
  $db = new AlpaDatabase();
  $db->RunQuery($cmd);
  while($db->Read())
  {
   $a = array("id"=>$db->record['id'], "ap"=>$_AP, "name"=>$db->record['name'], "description"=>$db->record['description'],"ctime"=>$db->record['ctime'],
	"code_str"=>$db->record['code_str'], "barcode"=>$db->record['barcode'], "manufacturer_code"=>$db->record['manufacturer_code'], 
	"storeqty"=>$db->record['storeqty'], "booked"=>$db->record['booked'], "incoming"=>$db->record['incoming'], "location"=>$db->record['item_location'],
	"gebinde"=>$db->record['gebinde'], "gebinde_code"=>$db->record['gebinde_code'], "division"=>$db->record['division'], 
	"baseprice"=>$db->record['baseprice'], "vat"=>$db->record['vat']);
   for($c=0; $c < count($storeIds); $c++)
	$a['store_'.$storeIds[$c].'_qty'] = $db->record['store_'.$storeIds[$c].'_qty'];
   $_RESULTS[] = $a;
  }
  
  if(in_array("barcode",$_FIELDS) || in_array("code_str", $_FIELDS))
  {
   $itemFields = array('id','name','description','ctime','manufacturer_code','storeqty','booked','incoming','item_location','gebinde','gebinde_code',
	'division','baseprice','vat');
   $q = "";
   for($c=0; $c < count($itemFields); $c++)
   {
    if($itemFields[$c] == "item_location")
	 $q.= ", i.item_location AS location";
	else
	 $q.= ", i.".$itemFields[$c];
   }

   $qry = "SELECT v.variant_name, v.variant_type, v.code AS code_str, v.barcode".$q." FROM dynarc_".$_AP."_varcodes AS v";
   $qry.= " INNER JOIN dynarc_".$_AP."_items AS i ON i.id=v.item_id";
   
   $varWhere = "";
   if(in_array("barcode",$_FIELDS))		$varWhere.= " OR v.barcode='".$query."'";
   if(in_array("code_str",$_FIELDS))	$varWhere.= " OR v.code='".$query."'";

   //$qry.= " WHERE v.barcode='".$query."'";
   $qry.= " WHERE ".ltrim($varWhere, " OR ");

   $db->RunQuery($qry);
   //if($db->Error) return array('message'=>'MySQL Error: '.$db->Error, 'error'=>'MYSQL_ERROR');
   if($db->Error) { $db->Close(); $db = new AlpaDatabase(); }
   else if($db->Read())
   {
	$a = array('ap'=>$_AP, 'code_str'=>$db->record['code_str'], 'barcode'=>$db->record['barcode'], 'variant_name'=>$db->record['variant_name'], 'variant_type'=>$db->record['variant_type']);
	for($c=0; $c < count($itemFields); $c++)
	{
	 switch($itemFields[$c])
	 {
	  case 'name' : $a['name'] = $db->record['name']." - ".$db->record['variant_name']; break;
	  default : $a[$itemFields[$c]] = $db->record[$itemFields[$c]]; break;
	 }
	}
	$_RESULTS[] = $a;
	$matchResCount++;
   }
  }

  $db->Close();
 }
 else
 {
  $obFS = "";
  if($orderBy)
  {
   // get orderby fields //
   $x = explode(",",$orderBy);
   for($c=0; $c < count($x); $c++)
   {
    $f = $x[$c];
    if(!$f) continue;
    $f = str_replace(array('ASC','DESC','asc','desc'), "", $f);
    $obFS.= ",".$f;
   }
  }
  $finalQry = "";
  for($c=0; $c < count($_ARCHIVES); $c++)
  {
   $_AP = $_ARCHIVES[$c]['prefix'];
   $uQry = $m->userQuery($sessid,null,"dynarc_".$_AP."_items");
   $finalQry.= " UNION SELECT '".$_AP."' AS tb_prefix,id".str_replace(",id","",$obFS)." FROM dynarc_".$_AP."_items WHERE (".$uQry.")".$qry;
  }
  $finalQry = "SELECT * FROM (".ltrim($finalQry," UNION ").") AS qryelements";
  if($orderBy)
   $finalQry.= " ORDER BY ".$orderBy;
  if($limit)
   $finalQry.= " LIMIT ".$limit;

  $db = new AlpaDatabase();
  $db2 = new AlpaDatabase();
  $db->RunQuery($finalQry);
  while($db->Read())
  {
   $a = array("id"=>$db->record['id'], "ap"=>$db->record['tb_prefix'], "at"=>$_AT);
   $db2->RunQuery("SELECT * FROM dynarc_".$a['ap']."_items WHERE id='".$a['id']."'");
   $db2->Read();
   $a["name"] = $db2->record['name'];
   $a["description"] = $db2->record['description'];
   $a["ctime"] = $db2->record['ctime'];
   $a["code_str"] = $db2->record['code_str'];
   $a["barcode"] = $db2->record['barcode'];
   $a["manufacturer_code"] = $db2->record['manufacturer_code'];
   $a["storeqty"] = $db2->record['storeqty'];
   $a["booked"] = $db2->record['booked'];
   $a["incoming"] = $db2->record['incoming'];
   $a["location"] = $db2->record['item_location'];
   $a["gebinde"] = $db2->record['gebinde'];
   $a["gebinde_code"] = $db2->record['gebinde_code'];
   $a["division"] = $db2->record['division'];
   for($c=0; $c < count($storeIds); $c++)
	$a['store_'.$storeIds[$c].'_qty'] = $db2->record['store_'.$storeIds[$c].'_qty'];

   $_RESULTS[] = $a;
  }

  if(in_array("barcode", $_FIELDS) || in_array("code_str", $_FIELDS))
  {
   $itemFields = array('id','name','description','ctime','manufacturer_code','storeqty','booked','incoming','item_location','gebinde','gebinde_code',
	'division','baseprice','vat');
   $q = "";
   for($c=0; $c < count($itemFields); $c++)
   {
    if($itemFields[$c] == "item_location")
	 $q.= ", i.item_location AS location";
	else
	 $q.= ", i.".$itemFields[$c];
   }

   for($c=0; $c < count($_ARCHIVES); $c++)
   {
	$_AP = $_ARCHIVES[$c]['prefix'];
    $qry = "SELECT v.variant_name, v.variant_type, v.code AS code_str, v.barcode".$q." FROM dynarc_".$_AP."_varcodes AS v";
    $qry.= " INNER JOIN dynarc_".$_AP."_items AS i ON i.id=v.item_id";

    $varWhere = "";
    if(in_array("barcode",$_FIELDS))		$varWhere.= " OR v.barcode='".$query."'";
    if(in_array("code_str",$_FIELDS))		$varWhere.= " OR v.code='".$query."'";

    //$qry.= " WHERE v.barcode='".$query."'";
	$qry.= " WHERE ".ltrim($varWhere, " OR ");

    $db2->RunQuery($qry);
    //if($db2->Error) return array('message'=>'MySQL Error: '.$db2->Error, 'error'=>'MYSQL_ERROR');
	if($db2->Error) { $db2->Close(); $db2 = new AlpaDatabase(); }
    else if($db2->Read())
    {
	 $a = array('at'=>$_AT, 'ap'=>$_AP, 'code_str'=>$db2->record['code_str'], 'barcode'=>$db2->record['barcode'], 'variant_name'=>$db2->record['variant_name'], 'variant_type'=>$db2->record['variant_type']);
	 for($i=0; $i < count($itemFields); $i++)
	 {
	  switch($itemFields[$i])
	  {
	   case 'name' : $a['name'] = $db2->record['name']." - ".$db2->record['variant_name']; break;
	   default : $a[$itemFields[$i]] = $db2->record[$itemFields[$i]]; break;
	  }
	 }
	 $_RESULTS[] = $a;
	 $matchResCount++;
    }
   }
  }

  $db2->Close();
  $db->Close();
 }
 
 $outArr['count'] = $matchResCount;
 $outArr['results'] = $_RESULTS;

 /* FOR SERP */
 $outArr['serp'] = array('count'=>$matchResCount);
 if(strpos($limit,",") !== false)
 {
  $x = explode(",",$limit);
  $outArr['serp']['from'] = ($x[0]+1);
  $outArr['serp']['to'] = count($outArr['results'])+$x[0];
  $outArr['serp']['rpp'] = $x[1];
  $outArr['serp']['pgidx'] = ($x[0] && $x[1]) ? ($x[0]/$x[1]) : 0;
  $outArr['serp']['pg'] = $outArr['serp']['pgidx']+1;
 }
 else
 {
  $outArr['serp']['from'] = count($outArr['results']) ? 1 : 0;
  $outArr['serp']['to'] = count($outArr['results']);
  $outArr['serp']['rpp'] = $limit;
  $outArr['serp']['pgidx'] = 0;
  $outArr['serp']['pg'] = $outArr['serp']['pgidx']+1;
 }

 $out.= !$matchResCount ? "Match not found for '".$query."'" : $matchResCount." results found!";

 if($verbose)
 {
  $out.= "\n";
  for($c=0; $c < count($outArr['results']); $c++)
  {
   $res = $outArr['results'][$c];
   $out.= $res['code_str']." - ".$res['name']."\n";
  }
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function fastfind_productSearchByVencode($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $sessInfo = sessionInfo($sessid);

 $_AT = "gmart";
 $_FIELDS = array();

 $matchResCount = 0;
 $limit = 10;
 $orderBy = "name ASC";
 $_RESULTS = array();
 $_ARCHIVES = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
   case '-fields' : {$fields=$args[$c+1]; $c++;} break;
   case '-vencode' : {$query=$args[$c+1]; $c++;} break; // ricerca tramite codice articolo fornitore
   case '-vendorid' : {$vendorId=$args[$c+1]; $c++;} break; // filtra la ricerca per un determinato fornitore

   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
  }

 if($catTag)
 {
  if(!$_AP)
   $_AP = "gmart";
  // get cat id
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".$_AP."_categories WHERE tag='".$catTag."' AND trash='0' LIMIT 1");
  if($db->Read())
   $catId = $db->record['id'];
  $db->Close();
 }

 if($_AP)
  $_ARCHIVES[] = array("prefix"=>$_AP);
 else
 {
  $ret = GShell("dynarc archive-list -a -type '".$_AT."'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  for($c=0; $c < count($ret['outarr']); $c++)
   $_ARCHIVES[] = $ret['outarr'][$c];
 }

 /* GET STORES */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM stores WHERE 1");
 while($db->Read())
  $storeIds[] = $db->record['id'];
 $db->Close();


 /***** COUNT QUERY *****/ 
 $db = new AlpaDatabase();
 $query = $db->Purify($query);
 $qry = "(code=\"".$query."\") OR (code LIKE \"".$query."%\") OR (code LIKE \"%".$query."%\") OR (code LIKE \"%".$query."\")";
 if($vendorId) $qry.= " AND vendor_id='".$vendorId."'";

 if(count($_ARCHIVES) == 1)
 {
  // MONO ARCHIVIO
  $_AP = $_ARCHIVES[0]['prefix'];
  $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$_AP."_vendorprices WHERE ".$qry);
  $db->Read();
  $matchResCount = $db->record[0];
 }
 else
 {
  // MULTI ARCHIVIO
  $countQry = "";
  for($c=0; $c < count($_ARCHIVES); $c++)
  {
   $_AP = $_ARCHIVES[$c]['prefix'];
   $countQry.= " UNION SELECT '".$_AP."' AS tb_prefix,id FROM dynarc_".$_AP."_vendorprices WHERE ".$qry;
  }
  $countQry = "SELECT COUNT(*) FROM (".ltrim($countQry," UNION ").") AS qryelements";
  $db->RunQuery($countQry);
  $db->Read();
  $matchResCount = $db->record[0];
 }
 $db->Close();


 /**** ITEMS QUERY *****/
 if(count($_ARCHIVES) == 1)
 {
  // MONO ARCHIVIO
  $_AP = $_ARCHIVES[0]['prefix'];
  $cmd = "SELECT item_id,code,vendor_id,vendor_name,price,vatrate FROM dynarc_".$_AP."_vendorprices WHERE ".$qry." ORDER BY code ASC"
	.($limit ? " LIMIT ".$limit : "");
  $db = new AlpaDatabase();
  $db2 = new AlpaDatabase();
  $db->RunQuery($cmd);
  while($db->Read())
  {
   $db2->RunQuery("SELECT * FROM dynarc_".$_AP."_items WHERE id='".$db->record['item_id']."'");
   $db2->Read();
   $a = array("id"=>$db2->record['id'], "ap"=>$_AP, "name"=>$db2->record['name'], "description"=>$db2->record['description'],
	"ctime"=>$db2->record['ctime'],"code_str"=>$db2->record['code_str'], "barcode"=>$db2->record['barcode'], 
	"manufacturer_code"=>$db2->record['manufacturer_code'], "storeqty"=>$db2->record['storeqty'], "booked"=>$db2->record['booked'], 
	"incoming"=>$db2->record['incoming'], "location"=>$db2->record['item_location'],"gebinde"=>$db2->record['gebinde'], 
	"gebinde_code"=>$db2->record['gebinde_code'], "division"=>$db2->record['division'],
	"vencode"=>$db->record['code'], "vendor_id"=>$db->record['vendor_id'], "vendor_name"=>$db->record['vendor_name'], 
	"vendor_price"=>$db->record['price'], "vendor_vatrate"=>$db->record['vatrate']);
   for($c=0; $c < count($storeIds); $c++)
	$a['store_'.$storeIds[$c].'_qty'] = $db2->record['store_'.$storeIds[$c].'_qty'];
   $_RESULTS[] = $a;
  }
  $db2->Close();
  $db->Close();
 }
 else
 {
  // MULTI ARCHIVIO
  $finalQry = "";
  for($c=0; $c < count($_ARCHIVES); $c++)
  {
   $_AP = $_ARCHIVES[$c]['prefix'];
   $finalQry.= " UNION SELECT '".$_AP."' AS tb_prefix,id,item_id,code,vendor_id,vendor_name,price,vatrate FROM dynarc_".$_AP."_vendorprices WHERE ".$qry;
  }
  $finalQry = "SELECT * FROM (".ltrim($finalQry," UNION ").") AS qryelements ORDER BY code ASC".($limit ? " LIMIT ".$limit : "");

  $db = new AlpaDatabase();
  $db2 = new AlpaDatabase();
  $db->RunQuery($finalQry);
  while($db->Read())
  {
   $db2->RunQuery("SELECT * FROM dynarc_".$db->record['tb_prefix']."_items WHERE id='".$db->record['item_id']."'");
   $db2->Read();
   $a = array("id"=>$db2->record['id'], "ap"=>$db->record['tb_prefix'], "name"=>$db2->record['name'], "description"=>$db2->record['description'],
	"ctime"=>$db2->record['ctime'],"code_str"=>$db2->record['code_str'], "barcode"=>$db2->record['barcode'], 
	"manufacturer_code"=>$db2->record['manufacturer_code'], "storeqty"=>$db2->record['storeqty'], "booked"=>$db2->record['booked'], 
	"incoming"=>$db2->record['incoming'], "location"=>$db2->record['item_location'],"gebinde"=>$db2->record['gebinde'], 
	"gebinde_code"=>$db2->record['gebinde_code'], "division"=>$db2->record['division'],
	"vencode"=>$db->record['code'], "vendor_id"=>$db->record['vendor_id'], "vendor_name"=>$db->record['vendor_name'], 
	"vendor_price"=>$db->record['price'], "vendor_vatrate"=>$db->record['vatrate']);
   for($c=0; $c < count($storeIds); $c++)
	$a['store_'.$storeIds[$c].'_qty'] = $db2->record['store_'.$storeIds[$c].'_qty'];

   $_RESULTS[] = $a;
  }
  $db2->Close();
  $db->Close();
 }
 
 $outArr['count'] = $matchResCount;
 $outArr['results'] = $_RESULTS;

 /* FOR SERP */
 $outArr['serp'] = array('count'=>$matchResCount);
 if(strpos($limit,",") !== false)
 {
  $x = explode(",",$limit);
  $outArr['serp']['from'] = ($x[0]+1);
  $outArr['serp']['to'] = count($outArr['results'])+$x[0];
  $outArr['serp']['rpp'] = $x[1];
  $outArr['serp']['pgidx'] = ($x[0] && $x[1]) ? ($x[0]/$x[1]) : 0;
  $outArr['serp']['pg'] = $outArr['serp']['pgidx']+1;
 }
 else
 {
  $outArr['serp']['from'] = count($outArr['results']) ? 1 : 0;
  $outArr['serp']['to'] = count($outArr['results']);
  $outArr['serp']['rpp'] = $limit;
  $outArr['serp']['pgidx'] = 0;
  $outArr['serp']['pg'] = $outArr['serp']['pgidx']+1;
 }

 $out.= !$matchResCount ? "Match not found for '".$query."'" : $matchResCount." results found!";

 if($verbose)
 {
  $out.= "\n";
  for($c=0; $c < count($outArr['results']); $c++)
  {
   $res = $outArr['results'][$c];
   $out.= "cod. ".$res['vencode']." - [int.code: ".$res['code_str']."] ".$res['name']."\n";
  }
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function fastfind_productSearchByVendor($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $sessInfo = sessionInfo($sessid);

 $_AT = "gmart";
 $_FIELDS = array();

 $matchResCount = 0;
 $limit = 10;
 $orderBy = "name ASC";
 $_RESULTS = array();
 $_ARCHIVES = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
   case '-fields' : {$fields=$args[$c+1]; $c++;} break;
   case '-vendorid' : {$vendorId=$args[$c+1]; $c++;} break; // filtra la ricerca per un determinato fornitore
   case '-vendor' : case '-vendorname' : {$query=$args[$c+1]; $c++;} break;	// ricerca tramite fornitore

   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
  }

 if($catTag)
 {
  if(!$_AP)
   $_AP = "gmart";
  // get cat id
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".$_AP."_categories WHERE tag='".$catTag."' AND trash='0' LIMIT 1");
  if($db->Read())
   $catId = $db->record['id'];
  $db->Close();
 }

 if($_AP)
  $_ARCHIVES[] = array("prefix"=>$_AP);
 else
 {
  $ret = GShell("dynarc archive-list -a -type '".$_AT."'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  for($c=0; $c < count($ret['outarr']); $c++)
   $_ARCHIVES[] = $ret['outarr'][$c];
 }

 /* GET STORES */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM stores WHERE 1");
 while($db->Read())
  $storeIds[] = $db->record['id'];
 $db->Close();


 /***** COUNT QUERY *****/ 
 $db = new AlpaDatabase();
 if($vendorId)
  $qry = "vendor_id='".$vendorId."'";
 else
 {
  $query = $db->Purify($query);
  $qry = "(vendor_name=\"".$query."\") OR (vendor_name LIKE \"".$query."%\") OR (vendor_name LIKE \"%".$query."%\") OR (vendor_name LIKE \"%".$query."\")";
 }

 if(count($_ARCHIVES) == 1)
 {
  // MONO ARCHIVIO
  $_AP = $_ARCHIVES[0]['prefix'];
  $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$_AP."_vendorprices WHERE ".$qry);
  $db->Read();
  $matchResCount = $db->record[0];
 }
 else
 {
  // MULTI ARCHIVIO
  $countQry = "";
  for($c=0; $c < count($_ARCHIVES); $c++)
  {
   $_AP = $_ARCHIVES[$c]['prefix'];
   $countQry.= " UNION SELECT '".$_AP."' AS tb_prefix,id FROM dynarc_".$_AP."_vendorprices WHERE ".$qry;
  }
  $countQry = "SELECT COUNT(*) FROM (".ltrim($countQry," UNION ").") AS qryelements";
  $db->RunQuery($countQry);
  $db->Read();
  $matchResCount = $db->record[0];
 }
 $db->Close();


 /**** ITEMS QUERY *****/
 if(count($_ARCHIVES) == 1)
 {
  // MONO ARCHIVIO
  $_AP = $_ARCHIVES[0]['prefix'];
  $cmd = "SELECT item_id,code,vendor_id,vendor_name,price,vatrate FROM dynarc_".$_AP."_vendorprices WHERE ".$qry." ORDER BY code ASC"
	.($limit ? " LIMIT ".$limit : "");
  $db = new AlpaDatabase();
  $db2 = new AlpaDatabase();
  $db->RunQuery($cmd);
  while($db->Read())
  {
   $db2->RunQuery("SELECT * FROM dynarc_".$_AP."_items WHERE id='".$db->record['item_id']."'");
   $db2->Read();
   $a = array("id"=>$db2->record['id'], "ap"=>$_AP, "name"=>$db2->record['name'], "description"=>$db2->record['description'],
	"ctime"=>$db2->record['ctime'],"code_str"=>$db2->record['code_str'], "barcode"=>$db2->record['barcode'], 
	"manufacturer_code"=>$db2->record['manufacturer_code'], "storeqty"=>$db2->record['storeqty'], "booked"=>$db2->record['booked'], 
	"incoming"=>$db2->record['incoming'], "location"=>$db2->record['item_location'],"gebinde"=>$db2->record['gebinde'], 
	"gebinde_code"=>$db2->record['gebinde_code'], "division"=>$db2->record['division'],
	"vencode"=>$db->record['code'], "vendor_id"=>$db->record['vendor_id'], "vendor_name"=>$db->record['vendor_name'], 
	"vendor_price"=>$db->record['price'], "vendor_vatrate"=>$db->record['vatrate']);
   for($c=0; $c < count($storeIds); $c++)
	$a['store_'.$storeIds[$c].'_qty'] = $db2->record['store_'.$storeIds[$c].'_qty'];
   $_RESULTS[] = $a;
  }
  $db2->Close();
  $db->Close();
 }
 else
 {
  // MULTI ARCHIVIO
  $finalQry = "";
  for($c=0; $c < count($_ARCHIVES); $c++)
  {
   $_AP = $_ARCHIVES[$c]['prefix'];
   $finalQry.= " UNION SELECT '".$_AP."' AS tb_prefix,id,item_id,code,vendor_id,vendor_name,price,vatrate FROM dynarc_".$_AP."_vendorprices WHERE ".$qry;
  }
  $finalQry = "SELECT * FROM (".ltrim($finalQry," UNION ").") AS qryelements ORDER BY code ASC".($limit ? " LIMIT ".$limit : "");

  $db = new AlpaDatabase();
  $db2 = new AlpaDatabase();
  $db->RunQuery($finalQry);
  while($db->Read())
  {
   $db2->RunQuery("SELECT * FROM dynarc_".$db->record['tb_prefix']."_items WHERE id='".$db->record['item_id']."'");
   $db2->Read();
   $a = array("id"=>$db2->record['id'], "ap"=>$db->record['tb_prefix'], "name"=>$db2->record['name'], "description"=>$db2->record['description'],
	"ctime"=>$db2->record['ctime'],"code_str"=>$db2->record['code_str'], "barcode"=>$db2->record['barcode'], 
	"manufacturer_code"=>$db2->record['manufacturer_code'], "storeqty"=>$db2->record['storeqty'], "booked"=>$db2->record['booked'], 
	"incoming"=>$db2->record['incoming'], "location"=>$db2->record['item_location'],"gebinde"=>$db2->record['gebinde'], 
	"gebinde_code"=>$db2->record['gebinde_code'], "division"=>$db2->record['division'],
	"vencode"=>$db->record['code'], "vendor_id"=>$db->record['vendor_id'], "vendor_name"=>$db->record['vendor_name'], 
	"vendor_price"=>$db->record['price'], "vendor_vatrate"=>$db->record['vatrate']);
   for($c=0; $c < count($storeIds); $c++)
	$a['store_'.$storeIds[$c].'_qty'] = $db2->record['store_'.$storeIds[$c].'_qty'];

   $_RESULTS[] = $a;
  }
  $db2->Close();
  $db->Close();
 }
 
 $outArr['count'] = $matchResCount;
 $outArr['results'] = $_RESULTS;

 /* FOR SERP */
 $outArr['serp'] = array('count'=>$matchResCount);
 if(strpos($limit,",") !== false)
 {
  $x = explode(",",$limit);
  $outArr['serp']['from'] = ($x[0]+1);
  $outArr['serp']['to'] = count($outArr['results'])+$x[0];
  $outArr['serp']['rpp'] = $x[1];
  $outArr['serp']['pgidx'] = ($x[0] && $x[1]) ? ($x[0]/$x[1]) : 0;
  $outArr['serp']['pg'] = $outArr['serp']['pgidx']+1;
 }
 else
 {
  $outArr['serp']['from'] = count($outArr['results']) ? 1 : 0;
  $outArr['serp']['to'] = count($outArr['results']);
  $outArr['serp']['rpp'] = $limit;
  $outArr['serp']['pgidx'] = 0;
  $outArr['serp']['pg'] = $outArr['serp']['pgidx']+1;
 }

 $out.= !$matchResCount ? "Match not found for '".$query."'" : $matchResCount." results found!";

 if($verbose)
 {
  $out.= "\n";
  for($c=0; $c < count($outArr['results']); $c++)
  {
   $res = $outArr['results'][$c];
   $out.= "cod. ".$res['vencode']." - [int.code: ".$res['code_str']."] ".$res['name']."\n";
  }
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function fastfind_serviceSearch($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $sessInfo = sessionInfo($sessid);

 $_AT = "gserv";
 $_FIELDS = array();

 $matchResCount = 0;
 $limit = 10;
 $orderBy = "name ASC";
 $_RESULTS = array();
 $_ARCHIVES = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
   case '-fields' : {$fields=$args[$c+1]; $c++;} break;

   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
   default : $query=$args[$c]; break;
  }

 if(!$fields) $fields = "code_str,name";
 $_FIELDS = explode(",",$fields);

 if($catTag)
 {
  if(!$_AP)
   $_AP = "gserv";
  // get cat id
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".$_AP."_categories WHERE tag='".$catTag."' AND trash='0' LIMIT 1");
  if($db->Read())
   $catId = $db->record['id'];
  $db->Close();
 }

 if($_AP)
  $_ARCHIVES[] = array("prefix"=>$_AP);
 else
 {
  $ret = GShell("dynarc archive-list -a -type '".$_AT."'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  for($c=0; $c < count($ret['outarr']); $c++)
   $_ARCHIVES[] = $ret['outarr'][$c];
 }

 /* PREPARE QUERY */
 $db = new AlpaDatabase();
 $query = $db->Purify($query);
 $qry = "";
 for($c=0; $c < count($_FIELDS); $c++)
 {
  $field = $_FIELDS[$c]; 
  $qry.= " OR ((".$field."=\"".$query."\") OR (".$field." LIKE \"".$query."%\") OR (".$field." LIKE \"%"
	.$query."%\") OR (".$field." LIKE \"%".$query."\"))";
 }
 if($qry)
  $qry = " AND (".ltrim($qry," OR ").")";
 if($where)
  $qry.= " AND (".$where.")";
 $qry.= " AND trash='0'";
 $db->Close();

 $m = new GMOD();
 if(count($_ARCHIVES) == 1)
 {
  $_AP = $_ARCHIVES[0]['prefix'];
  $uQry = $m->userQuery($sessid,null,"dynarc_".$_AP."_items");
  $cmd = "SELECT COUNT(*) FROM dynarc_".$_AP."_items WHERE (".$uQry.")".$qry;
  $db = new AlpaDatabase();
  $db->RunQuery($cmd);
  $db->Read();
  $matchResCount = $db->record[0];
  $db->Close();
 }
 else
 {
  /* PREPARE COUNT QUERY */
  $countQry = "";
  for($c=0; $c < count($_ARCHIVES); $c++)
  {
   $_AP = $_ARCHIVES[$c]['prefix'];
   $uQry = $m->userQuery($sessid,null,"dynarc_".$_AP."_items");
   $countQry.= " UNION SELECT '".$_AP."' AS tb_prefix,id FROM dynarc_".$_AP."_items WHERE (".$uQry.")".$qry;
  }
  $countQry = "SELECT COUNT(*) FROM (".ltrim($countQry," UNION ").") AS qryelements";
  $db = new AlpaDatabase();
  $db->RunQuery($countQry);
  $db->Read();
  $matchResCount = $db->record[0];
  $db->Close();
 }


 if(count($_ARCHIVES) == 1)
 {
  $_AP = $_ARCHIVES[0]['prefix'];
  $cmd = "SELECT * FROM dynarc_".$_AP."_items WHERE (".$uQry.")".$qry." ORDER BY ".$orderBy.($limit ? " LIMIT ".$limit : "");
  $db = new AlpaDatabase();
  $db->RunQuery($cmd);
  while($db->Read())
  {
   $a = array("id"=>$db->record['id'], "ap"=>$_AP, "name"=>$db->record['name'], "description"=>$db->record['description'],"ctime"=>$db->record['ctime'],
	"code_str"=>$db->record['code_str'], "pricemode"=>$db->record['pricemode'], "type"=>$db->record['service_type'], 
	"etl"=>$db->record['estimated_timelength'], "baseprice"=>$db->record['baseprice'], "vat"=>$db->record['vat']);
   $_RESULTS[] = $a;
  }
  $db->Close();
 }
 else
 {
  $obFS = "";
  if($orderBy)
  {
   // get orderby fields //
   $x = explode(",",$orderBy);
   for($c=0; $c < count($x); $c++)
   {
    $f = $x[$c];
    if(!$f) continue;
    $f = str_replace(array('ASC','DESC','asc','desc'), "", $f);
    $obFS.= ",".$f;
   }
  }
  $finalQry = "";
  for($c=0; $c < count($_ARCHIVES); $c++)
  {
   $_AP = $_ARCHIVES[$c]['prefix'];
   $uQry = $m->userQuery($sessid,null,"dynarc_".$_AP."_items");
   $finalQry.= " UNION SELECT '".$_AP."' AS tb_prefix,id".str_replace(",id","",$obFS)." FROM dynarc_".$_AP."_items WHERE (".$uQry.")".$qry;
  }
  $finalQry = "SELECT * FROM (".ltrim($finalQry," UNION ").") AS qryelements";
  if($orderBy)
   $finalQry.= " ORDER BY ".$orderBy;
  if($limit)
   $finalQry.= " LIMIT ".$limit;

  $db = new AlpaDatabase();
  $db2 = new AlpaDatabase();
  $db->RunQuery($finalQry);
  while($db->Read())
  {
   $a = array("id"=>$db->record['id'], "ap"=>$db->record['tb_prefix']);
   $db2->RunQuery("SELECT * FROM dynarc_".$a['ap']."_items WHERE id='".$a['id']."'");
   $db2->Read();
   $a["name"] = $db2->record['name'];
   $a["description"] = $db2->record['description'];
   $a["ctime"] = $db2->record['ctime'];
   $a["code_str"] = $db2->record['code_str'];
   $a['pricemode'] = $db2->record['pricemode'];
   $a['type'] = $db2->record['service_type'];
   $a['etl'] = $db2->record['estimated_timelength'];
   $a['baseprice'] = $db2->record['baseprice'];
   $a['vat'] = $db2->record['vat'];

   $_RESULTS[] = $a;
  }
  $db2->Close();
  $db->Close();
 }
 
 $outArr['count'] = $matchResCount;
 $outArr['results'] = $_RESULTS;

 $out.= !$matchResCount ? "Match not found for '".$query."'" : $matchResCount." results found!";

 if($verbose)
 {
  $out.= "\n";
  for($c=0; $c < count($outArr['results']); $c++)
  {
   $res = $outArr['results'][$c];
   $out.= $res['code_str']." - ".$res['name']."\n";
  }
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function fastfind_vehicleSearch($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();
 $sessInfo = sessionInfo($sessid);

 $_AP = "vehicles";
 $_FIELDS = array();

 $matchResCount = 0;
 $limit = 10;
 $orderBy = "name ASC";
 $_RESULTS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
   case '-fields' : {$fields=$args[$c+1]; $c++;} break;

   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
   default : $query=$args[$c]; break;
  }

 $_FIELDS = explode(",",$fields);

 if($catTag)
 {
  // get cat id
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".$_AP."_categories WHERE tag='".$catTag."' AND trash='0' LIMIT 1");
  if($db->Read())
   $catId = $db->record['id'];
  $db->Close();
 }

 /* PREPARE QUERY */
 $db = new AlpaDatabase();
 $query = $db->Purify($query);
 $qry = "";
 for($c=0; $c < count($_FIELDS); $c++)
 {
  $field = $_FIELDS[$c]; 
  $qry.= " OR ((".$field."=\"".$query."\") OR (".$field." LIKE \"".$query."%\") OR (".$field." LIKE \"%"
	.$query."%\") OR (".$field." LIKE \"%".$query."\"))";
 }
 if($qry)
  $qry = " AND (".ltrim($qry," OR ").")";
 if($where)
  $qry.= " AND (".$where.")";
 $qry.= " AND trash='0'";
 $db->Close();

 $m = new GMOD();
 $uQry = $m->userQuery($sessid,null,"dynarc_".$_AP."_items");
 $cmd = "SELECT COUNT(*) FROM dynarc_".$_AP."_items WHERE (".$uQry.")".$qry;
 $db = new AlpaDatabase();
 $db->RunQuery($cmd);
 $db->Read();
 $matchResCount = $db->record[0];
 $db->Close();

 $cmd = "SELECT * FROM dynarc_".$_AP."_items WHERE (".$uQry.")".$qry." ORDER BY ".$orderBy.($limit ? " LIMIT ".$limit : "");
 $db = new AlpaDatabase();
 $db->RunQuery($cmd);
 while($db->Read())
 {
  $a = array("id"=>$db->record['id'], "name"=>$db->record['name'], "description"=>$db->record['description'],"ctime"=>$db->record['ctime'],
	"numplate"=>$db->record['numplate'], "color"=>$db->record['color'], "subject_id"=>$db->record['subject_id'], 
	"subject_name"=>$db->record['subject_name'], "vin"=>$db->record['vin'], "motor_number"=>$db->record['motor_number'], 
	"gearbox_number"=>$db->record['gearbox_number'], "matric_date"=>$db->record['matric_date']);
   $_RESULTS[] = $a;
 }
 $db->Close();

 
 $outArr['count'] = $matchResCount;
 $outArr['results'] = $_RESULTS;

 $out.= !$matchResCount ? "Match not found for '".$query."'" : $matchResCount." results found!";

 if($verbose)
 {
  $out.= "\n";
  for($c=0; $c < count($outArr['results']); $c++)
  {
   $res = $outArr['results'][$c];
   $out.= $res['numplate']." - ".$res['name']." (".$res['subject_name'].")\n";
  }
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function fastfind_userSearch($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array('count'=>0, 'items'=>array());

 $orderBy = "fullname ASC";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-search' : {$search=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '--verbose' : case '-verbose' : $verbose=true; break;
  }

 $db = new AlpaDatabase();
 $_WHERE = "";
 if($search)
 {
  $search = $db->Purify($search);
  $_WHERE = "AND (fullname='".$search."' OR fullname LIKE '".$search."%' OR fullname LIKE '%".$search."' OR fullname LIKE '%".$search."%')";
 }
 if($where)
  $_WHERE.= " AND ".$where;
 if(!$_WHERE)
  $_WHERE = "1";

 // COUNT QRY //
 $ret = $db->RunQuery("SELECT COUNT(*) FROM gnujiko_users WHERE ".ltrim($_WHERE, " AND "));
 if(!$ret) return array("message"=>"MySQL Error: ".$db->Error, "error"=>"MYSQL_ERROR");
 $db->Read();
 $outArr['count'] = $db->record[0];

 // MAKE QRY //
 $ret = $db->RunQuery("SELECT id,username,fullname,email FROM gnujiko_users WHERE ".ltrim($_WHERE, " AND ")." ORDER BY ".$orderBy
	.($limit ? " LIMIT ".$limit : ""));
 if(!$ret) return array("message"=>"MySQL Error: ".$db->Error, "error"=>"MYSQL_ERROR");
 while($db->Read())
 {
  $a = array("id"=>$db->record['id'], "username"=>$db->record['username'], "fullname"=>$db->record['fullname'], "email"=>$db->record['email']);
  $a['name'] = $a['fullname'] ? $a['fullname'] : $a['name'];
  $outArr['items'][] = $a;
  if($verbose)
   $out.= "#".$a['id']." - ".$a['name']." [".$a['username']."]\n";
 }
 $db->Close();

 $out.= $outArr['count']." results found.";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function fastfind_addressSearch($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array('count'=>0, 'results'=>array());

 $_AP = "rubrica";
 $limit = 10;
 $orderBy = "name ASC";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-refid' : case '-subjid' : case '-subjectid' : {$refId=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '--verbose' : $verbose=true; break;
   default : $query=$args[$c]; break;
  }

 $db = new AlpaDatabase();
 $q = $refId ? "item_id='".$refId."'" : "";
 if($query)
 {
  $qry = $db->Purify($query);
  $q.= " AND ((name='".$qry."' OR name LIKE '".$qry."%' OR name LIKE '%".$qry."%' OR name LIKE '%".$qry."')";
  $q.= " OR (code='".$qry."' OR code LIKE '".$qry."%')";
  $q.= " OR (address='".$qry."' OR address LIKE '".$qry."%' OR address LIKE '%".$qry."%' OR address LIKE '%".$qry."')";
  $q.= " OR (city='".$qry."' OR city LIKE '".$qry."%')";
  $q.= ")";
 }
 if($where)			$q.= " AND (".$where.")";

 $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$_AP."_addresses WHERE ".ltrim($q," AND "));
 $db->Read();
 $outArr['count'] = $db->record[0];

 $db->RunQuery("SELECT * FROM dynarc_".$_AP."_addresses WHERE ".ltrim($q," AND ")." ORDER BY ".$orderBy." LIMIT ".$limit);
 while($db->Read())
 {
  $a = array('id'=>$db->record['id'], 'code'=>$db->record['code'], 'name'=>$db->record['name'], 'address'=>$db->record['address'],
	'city'=>$db->record['city'], 'zipcode'=>$db->record['zipcode'], 'province'=>$db->record['province'], 
	'countrycode'=>$db->record['countrycode']);
  $outArr['results'][] = $a;
 }
 $db->Close();

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

