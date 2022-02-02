<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2015 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-11-2015
 #PACKAGE: pos
 #DESCRIPTION: Some functions for POS
 #VERSION: 2.5beta
 #CHANGELOG: 23-11-2015 : Bug fix in function daily-closure.
			 10-09-2014 : Creata funzione pos search.
			 05-12-2013 : Bug fix.
			 27-11-2013 : Aggiunta modalità di stampa su file.
			 11-09-2013 : Aggiunta funzione daily-closure
 #DEPENDS: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_pos($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'print-order' : return pos_printOrder($args, $sessid, $shellid); break;
  case 'daily-closure' : return pos_dailyClosure($args, $sessid, $shellid); break;
  case 'get-protocols' : case 'protocols-list' : return pos_protocolsList($args, $sessid, $shellid); break;
  case 'protocol-info' : return pos_protocolInfo($args, $sessid, $shellid); break;

  case 'search' : return pos_search($args, $sessid, $shellid); break;

  default : return pos_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function pos_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function pos_printOrder($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;
 $_AP = "commercialdocs";
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break; /* Default archive is commercialdocs */
   case '-id' : {$id=$args[$c+1]; $c++;} break; /* ID of order */
   case '-modelid' : case '-modid' : case '-model' : {$modelId=$args[$c+1]; $c++;} break; /* ID of print model */
   case '-printer' : {$printerName=$args[$c+1]; $c++;} break; /* The name of the printer */
   case '-protocol' : {$protocol=$args[$c+1]; $c++;} break; /* Print to file */
  }

 if(!$id)
  return array('message'=>'You must specify the order id. (with: -id ORDER_ID)', 'error'=>'INVALID_ORDER');

 /* Get order info */
 $ret = GShell("dynarc item-info -ap `".$_AP."` -id `".$id."` -extget `cdinfo`",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 $docInfo = $ret['outarr'];

 if($protocol)
 {
  $ret = GShell("pos protocol-info '".$protocol."'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $protInfo = $ret['outarr'];
  include_once($_BASE_PATH.$protInfo['file']);
  if(is_callable("gnujikoposprotocol_".$protocol."_parse",true))
   $ret = call_user_func("gnujikoposprotocol_".$protocol."_parse",$_AP,$id,$sessid,$shellid);
  else
   return array("message"=>"Unable to call function 'gnujikoposprotocol_".$protocol."_parse' into file ".$protInfo['file'].".", "error"=>"UNKNOWN_ERROR");
  $ret['message'].= "done!\nThe file has been saved into ".$ret['filename'].".";
  return $ret;
 }
 else
 {
  /* Get print model info */
  $ret = GShell("dynarc item-info -ap `printmodels` -id `".$modelId."` -extget `css`",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $modelInfo = $ret['outarr'];

  /* Generate contents */
  $contents = "<html><head><meta http-equiv='content-type' content='text/html; charset=UTF-8'><title>Stampa scontrino</title></head><body>";
  $contents.= "<style type='text/css'>".$modelInfo['css'][0]['content']."</style>";
  $ret = GShell("dynarc item-info -ap `printmodels` -id `".$modelInfo['id']."` || parserize -p posreceipts -c *.desc -params `id=".$docInfo['id']."`",$sessid,$shellid);
  $contents.= $ret['message'];
  $contents.= "</body></html>";

  GShell("pdf export -o tmp/posreceipt.pdf -c `".$contents."` || lpr print -p `".$printerName."` -f *.filename",$sessid,$shellid);
 }

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function pos_dailyClosure($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-date' : {$date=$args[$c+1]; $c++;} break;
   case '-cat' : {$catId=$args[$c+1]; $c++;} break;
   case '-ct' : {$catTag=$args[$c+1]; $c++;} break;
  }

 if(!$date)
  return array("message"=>"You must specify a valid date.","error"=>"INVALID_DATE");

 if($catId)
 {
  $ret = GShell("dynarc cat-info -ap commercialdocs -id '".$catId."'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
 }
 else if($catTag)
 {
  $ret = GShell("dynarc cat-info -ap commercialdocs -tag '".$catTag."'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
  $catId = $catInfo['id'];
 }

 $date = strtotime($date);

 $ret = GShell("dynarc item-list -ap commercialdocs".($catId ? " -cat '".$catId."'" : "")." -where `ctime >= '"
	.date('Y-m-d',$date)."' AND ctime < '".date('Y-m-d',strtotime("+1 day",$date))."'` -extget cdinfo",$sessid,$shellid);
 $list = $ret['outarr']['items'];
 
 $amount = 0;
 $vat = 0;
 $total = 0;

 $first = null;
 $last = null;
 
 for($c=0; $c < count($list); $c++)
 {
  $itm = $list[$c];
  $amount+= $itm['amount'];
  $vat+= $itm['vat'];
  $total+= $itm['tot_netpay'];

  if($c == 0)
   $first = $itm;
  if($c == (count($list)-1))
   $last = $itm;

  // segna l'ordine come pagato
  GShell("dynarc edit-item -ap commercialdocs -id `".$itm['id']."` -extset `cdinfo.status=10,payment-date='"
	.date('Y-m-d',$date)."',mmr.incomes='".$itm['tot_netpay']."',payment='".date('Y-m-d',$date)."',description='Saldo',subject='''"
	.$itm['subject_name']."''',subjectid='".$itm['subject_id']."'`",$sessid,$shellid);
 }

 $description = "Chiusura cassa".($catId ? " ".$catInfo['name'] : "");

 if(!$first)
  $description.= " (nessuna ricevuta emessa)";
 else if($first == $last)
  $description.= " (".$first['name'].")";
 else
  $description.= " (ric.fisc. dalla "
	.$first['code_num'].($first['code_ext'] ? "/".$first['code_ext'] : "")." alla "
	.$last['code_num'].($last['code_ext'] ? "/".$last['code_ext'] : "").")";

 // registra il movimento in prima nota //
 $ret = GShell("dynarc new-item -ap pettycashbook -group pettycashbook -ctime `".date('Y-m-d',$date)."` -name `"
	.$description."` -extset `pettycashbook.in='".$total."'`",$sessid,$shellid);

 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//
function pos_protocolsList($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-verbose' : case '--verbose' : $verbose=true; break;
  }


 $dirs = array();
 $dir = "etc/pos/protocols/";
 $d = dir($_BASE_PATH.$dir);
 while(FALSE !== ($entry = $d->read()))
 {
  if(($entry == '.') || ($entry == '..') || ($entry == 'index.php'))
   continue;
  if(substr($entry, -1) == "~")
   continue;
  $Entry = rtrim($dir,'/').'/'.ltrim($entry,'/');
  if(!is_dir($_BASE_PATH.$Entry))
  {
   $pathinfo = pathinfo($entry);
   $protname = basename($entry, '.'.$pathinfo['extension']);
   $ret = GShell("pos protocol-info '".$protname."'",$sessid,$shellid);
   if($ret['error'])
	return $ret;
   $outArr[] = $ret['outarr'];
   if($verbose)
	$out.= $protname." (".$ret['outarr']['description'].") - [".$Entry."]\n";
  }
 }

 $out.= "\n".count($outArr)." protocols found.";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function pos_protocolInfo($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-verbose' : case '--verbose' : $verbose=true; break;
   default : $protocol=$args[$c]; break;
  }

 $protocolFile = $_BASE_PATH."etc/pos/protocols/".$protocol.".php";
 if(!file_exists($protocolFile))
  return array('message'=>"Error: the protocol ".$protocol." does not exists!","error"=>"INVALID_PROTOCOL");

 include_once($_BASE_PATH.$protocolFile);
 if(is_callable("gnujikoposprotocol_".$protocol."_info",true))
  $outArr = call_user_func("gnujikoposprotocol_".$protocol."_info");
 else
  return array("message"=>"Unable to call function 'gnujikoposprotocol_".$protocol."_info' into file ".$protocolFile.".", "error"=>"UNKNOWN_ERROR");

 $outArr['name'] = $protocol;
 $outArr['file'] = $protocolFile;

 $out.= "Protocol informations:\n";
 $out.= "Name: ".$outArr['name']."\n";
 $out.= "Description: ".$outArr['description']."\n";
 $out.= "File: ".$outArr['file']."\n";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function pos_search($args, $sessid, $shellid)
{
 global $_BASE_PATH;

 $out = "";
 $outArr = array('result'=>null, 'results'=>array(), 'items'=>array());

 /*
  result : Contiene l'elemento che soddisfa a pieno i criteri di ricerca (query completa).
  results: Contiene la lista di tutti gli altri elementi che possono soddisfare la ricerca.
  items: contiene sia result che results in un unica lista.
 */ 

 $query = "";
 $limit = 25;
 $orderBy = "name ASC";
 $_FIELDS = array();
 $fields = "code_str,name";
 $_ARCHIVES = array();
 $get = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-at' : {$_AT=$args[$c+1]; $c++;} break;	// Filter by archive type
   case '-ats' : {$_ATS=$args[$c+1]; $c++;} break;	// Filter by archive types separated by comma ','
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;	// Filter by archive prefix
   case '-aps' : {$_APS=$args[$c+1]; $c++;} break;	// Filter by archive prefixes separated by comma ','

   case '-fields' : {$fields=$args[$c+1]; $c++;} break;
   case '-get' : {$get=$args[$c+1]; $c++;} break;

   case '--order-by' : case '-orderby' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;

   default : {if(!$query) $query=$args[$c]; } break;
  }

 if(!$query)
  return array("message"=>"Query is empty.", "error"=>"QUERY_IS_EMPTY");

 include_once($_BASE_PATH."include/userfunc.php");

 /* SEARCH BY BARCODE */
 $ret = GShell("barcode search -at '".$_AT."' -ats '".$_ATS."' -ap '".$_AP."' -aps '".$_APS."' -limit '".$limit."' --order-by '".$orderBy."' `"
	.$query."`",$sessid,$shellid);
 if(!$ret['error'])
 {
  if(is_array($ret['outarr']['packsearch']) && $ret['outarr']['packsearch']['result'])
  {
   $res = $ret['outarr']['packsearch']['result'];
   $outArr['result'] = array('id'=>$res['id'], 'pack_id'=>$res['pack_id'], 'refap'=>$res['refap'], 'refid'=>$res['refid'], 'name'=>$res['refname'],
	'barcode'=>$res['barcode'], 'fullbarcode'=>$res['fullbarcode'], 'status'=>$res['status']);
   $outArr['items'][] = $outArr['result'];
  }
  else if(is_array($ret['outarr']['othersearch']) && is_array($ret['outarr']['othersearch']['matches']) && count($ret['outarr']['othersearch']['matches']))
  {
   if(count($ret['outarr']['othersearch']['matches']) == 1)
   {
	$res = $ret['outarr']['othersearch']['matches'][0];
	$outArr['result'] = array('ap'=>$res['ap'], 'id'=>$res['id'], 'code_str'=>$res['code_str'], 'name'=>$res['name'], 'barcode'=>$res['barcode']);
	$outArr['items'][] = $outArr['result'];
   }
  }
  else if(is_array($ret['outarr']['aboutbarcode']['results']) && count($ret['outarr']['aboutbarcode']['results']))
  {
   if(count($ret['outarr']['aboutbarcode']['results']) == 1)
   {
	$outArr['aboutbarcode'] = $ret['outarr']['aboutbarcode']['results'][0];
   }
  }
 }

 /* SEARCH BY FIELDS */
 if($_ATS)
 {
  $tmp = explode(",", $_ATS);
  $db = new AlpaDatabase();
  for($c=0; $c < count($tmp); $c++)
  {
   $db->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE archive_type='".$tmp[$c]."' AND trash='0'");
   while($db->Read()){$_ARCHIVES[] = $db->record['tb_prefix'];}
  }
  $db->Close();
 }
 else if($_AT)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE archive_type='".$_AT."' AND trash='0'");
  while($db->Read()){$_ARCHIVES[] = $db->record['tb_prefix'];}
  $db->Close();
 }
 else if($_APS)
  $_ARCHIVES = explode(",",$_APS);
 else if($_AP)
  $_ARCHIVES[] = $_AP;

 //----------------------------------------------------------------------------------------------//

 $_FIELDS = explode(",",$fields);
 $qry = "";
 $db = new AlpaDatabase();
 $queryP = $db->Purify($query);
 for($c=0; $c < count($_FIELDS); $c++)
 {
  $field = $_FIELDS[$c];
  $qry.= " OR ((".$field."=\"".$queryP."\") OR (".$field." LIKE \"".$queryP."%\") OR (".$field." LIKE \"%"
	.$queryP."%\") OR (".$field." LIKE \"%".$queryP."\"))";
 }
 if($qry)
  $qry = " AND (".ltrim($qry," OR ").")";
 $qry.= " AND trash='0'";
 $db->Close();

 //----------------------------------------------------------------------------------------------//

 $m = new GMOD();
 $finalQry = "";
 for($c=0; $c < count($_ARCHIVES); $c++)
 {
  $uQry = $m->userQuery($sessid,null,"dynarc_".$_ARCHIVES[$c]."_items");
  $finalQry.= " UNION SELECT '".$_ARCHIVES[$c]."' AS tb_prefix,id,code_str,name".($get ? ",".$get : "")." FROM dynarc_".$_ARCHIVES[$c]."_items WHERE (".$uQry.")".$qry;
 }
 $finalQry = "SELECT * FROM (".ltrim($finalQry," UNION ").") AS qryelements";
 if($orderBy)
  $finalQry.= " ORDER BY ".$orderBy;
 if($limit)
  $finalQry.= " LIMIT $limit";

 //----------------------------------------------------------------------------------------------//

 if($get) $gets = explode(",",$get);
 $db = new AlpaDatabase();
 $db->RunQuery($finalQry);
 while($db->Read())
 {
  $a = array('ap'=>$db->record['tb_prefix'], 'id'=>$db->record['id'], 'code_str'=>$db->record['code_str'], 'name'=>$db->record['name']);
  for($c=0; $c < count($gets); $c++)
   $a[$gets[$c]] = $db->record[$gets[$c]];
  $outArr['results'][] = $a;
  $outArr['items'][] = $a;
 }
 $db->Close();

 if($verbose)
 {
  if($outArr['result'])
  {
   $res = $outArr['result'];
   $out.= "Found one result that match all query:\n";
   $out.= "#".$res['id']." - ".$res['name']." [".$res['barcode']."]\n";
  }
  else
  {
   $list = $outArr['results'];
   $out.= count($list)." results found for '".$query."':\n";
   for($c=0; $c < count($list); $c++)
    $out.= "#".$list[$c]['id']." cod. ".$list[$c]['code_str']." - ".$list[$c]['name']."\n";
  }
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//



