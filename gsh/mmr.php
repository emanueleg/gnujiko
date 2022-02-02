<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-09-2013
 #PACKAGE: dynarc-mmr-extension
 #DESCRIPTION: Money Movements Reports.
 #VERSION: 2.2beta
 #CHANGELOG: 24-09-2013 : Aggiunto funzione clean.
			 13-09-2013 : Bug fix sulle scadenze. 
 #DEPENDS: 
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_mmr($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'schedule' : return mmr_schedule($args, $sessid, $shellid); break;
  case 'clean' : return mmr_clean($args, $sessid, $shellid); break;

  default : return mmr_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function mmr_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function mmr_schedule($args, $sessid, $shellid)
{
 $_ARCHIVE_PREFIXES = array();
 $_ARCHIVES = array();
 $_PAYMENT_MODES = array();

 $sessInfo = sessionInfo($sessid);
 $orderBy = "expire_date ASC";

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_ARCHIVE_PREFIXES[]=$args[$c+1]; $c++;} break; // se non viene specificato alcun archivio, verranno mostrati tutti.

   case '-from' : {$dateFrom=strtotime($args[$c+1]); $c++;} break;
   case '-to' : {$dateTo=strtotime($args[$c+1]); $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : case '--limit' : {$limit=$args[$c+1]; $c++;} break;
   case '--include-expired' : $includeExpired=true; break;

   case '-verbose' : $verbose=true; break;
  }

 $mod = new GMOD();

 if(count($_ARCHIVE_PREFIXES))
 {
  $db = new AlpaDatabase();
  for($c=0; $c < count($_ARCHIVE_PREFIXES); $c++)
  {
   $db->RunQuery("SELECT * FROM dynarc_archives WHERE tb_prefix='".$_ARCHIVE_PREFIXES[$c]."' LIMIT 1");
   if(!$db->Read())
	continue;
   $mod->set($db->record['_mod'],$db->record['uid'],$db->record['gid']);
   if($sessInfo['uname'] != "root" && !$mod->canRead($sessInfo['uid']))
	continue;
   $_ARCHIVES[] = array('id'=>$db->record['id'],'prefix'=>$db->record['tb_prefix'],'type'=>$db->record['archive_type'],'name'=>$db->record['name']);
  }
  $db->Close();
 }
 else // get all archives with mmr extension installed
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT archive_id FROM dynarc_archive_extensions WHERE extension_name='mmr' ORDER BY id ASC");
  while($db->Read())
  {
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT * FROM dynarc_archives WHERE id='".$db->record['archive_id']."'");
   if(!$db2->Read())
	continue;
   $mod->set($db2->record['_mod'],$db2->record['uid'],$db2->record['gid']);
   if($sessInfo['uname'] != "root" && !$mod->canRead($sessInfo['uid']))
	continue;
   $_ARCHIVES[] = array('id'=>$db2->record['id'],'prefix'=>$db2->record['tb_prefix'],'type'=>$db2->record['archive_type'],'name'=>$db2->record['name']);
   $db2->Close();
  }
  $db->Close();
 }

 /* LISTA DI TUTTE LE MODALITA' DI PAGAMENTO */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id,name FROM payment_modes WHERE 1 ORDER BY id ASC");
 while($db->Read())
 {
  $_PAYMENT_MODES[$db->record['id']] = $db->record['name'];
 }
 $db->Close();

 /* ELENCO DEI RISULTATI */
 if(!$dateFrom)
  $dateFrom = strtotime(date('Y-m-01'));
 if(!$dateTo)
  $dateTo = strtotime("+1 month",$dateFrom);

 $totIncomes = 0; $totExpenses = 0;

 $out.= "Money Movement Report: from ".date('d/m/Y',$dateFrom)." to ".date('d/m/Y',$dateTo)."\n";

 for($i=0; $i < count($_ARCHIVES); $i++)
 {
  $archiveInfo = $_ARCHIVES[$i];
  $_CAT_TAG = array();
  /* Get all categories */
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id,parent_id,tag FROM dynarc_".$archiveInfo['prefix']."_categories WHERE 1");
  while($db->Read())
  {
   if($db->record['parent_id'])
   {
	$db2 = new AlpaDatabase();
	$db2->RunQuery("SELECT tag FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$db->record['parent_id']."'");
	$db2->Read();
    $_CAT_TAG[$db->record['id']] = $db2->record['tag'];
	$db2->Close();
   }
   else
    $_CAT_TAG[$db->record['id']] = $db->record['tag'];
  }
  $db->Close();

  $db = new AlpaDatabase();
  $qry = "(expire_date>='".date('Y-m-d',$dateFrom)."' AND expire_date<'".date('Y-m-d',$dateTo)."' AND payment_date='0000-00-00')";
  if($includeExpired)
   $qry.= " OR (payment_date='0000-00-00' AND expire_date<='".date('Y-m-d',$dateFrom)."')";
  $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_mmr WHERE ".$qry." ORDER BY ".$orderBy);
  while($db->Read())
  {
   $a = array('id'=>$db->record['id'],'doc_id'=>$db->record['item_id'],'name'=>$db->record['description'],'incomes'=>$db->record['incomes'],
	'expenses'=>$db->record['expenses'],'expire_date'=>$db->record['expire_date'],'subject_id'=>$db->record['subject_id'],
	'subject_name'=>$db->record['subject_name']);
   // detect document info
   $db2 = new AlpaDatabase();
   $db2->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$db->record['item_id']."'");
   $db2->Read();
   if($db2->record['trash'])
    continue;
   $ct = $_CAT_TAG[$db2->record['cat_id']];
   if(($ct != "INVOICES") && ($ct != "ORDERS") && ($ct != "DDT") && ($ct != "INTERVREPORTS") && ($ct != "DEBITSNOTE") && ($ct != "PAYMENTNOTICE") && ($ct != "RECEIPTS"))
	continue;

   $a['doc_name'] = $db2->record['name'];
   $a['payment_mode'] = $db2->record['payment_mode'];
   $a['payment_mode_name'] = $_PAYMENT_MODES[$a['payment_mode']];
   $a['bank_support'] = $db2->record['bank_support'];
   $db2->Close();
   
   if($a['incomes']) $totIncomes+=$a['incomes'];
   if($a['expenses']) $totExpenses+=$a['expenses'];

   if($verbose)
   {
    $amount = $a['incomes'] ? $a['incomes'] : $a['expenses'];
    $out.= date('d/m/Y',strtotime($a['expire_date']))." - ".$a['subject_name']." (EUR: ".($amount ? number_format($amount,2,',','.') : "0,00").")\n";
   }

   $outArr[] = $a;
  }
  $db->Close();
 }

 if($verbose)
 {
  $out.= "\nTot. incomes: EUR. ".($totIncomes ? number_format($totIncomes,2,',','.') : "0,00")."\n";
  $out.= "Tot. expenses: EUR. ".($totExpenses ? number_format($totExpenses,2,',','.') : "0,00")."\n";
 }

 $out.= "\n".count($outArr)." results found.";
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function mmr_clean($args, $sessid, $shellid)
{
 $out = "";
 $_AP = "commercialdocs";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-docid' : {$docId=$args[$c+1]; $c++;} break;
  }

 if(!$docId)
  return array("message"=>"You must specify the document ID. (with: -docid ID)","error"=>"INVALID_DOC_ID");

 $ret = GShell("dynarc item-info -ap `".$_AP."` -id `".$docId."`",$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $docInfo = $ret['outarr'];

 if(!$docInfo['modinfo']['can_write'])
  return array("message"=>"Permission denied!","error"=>"PERMISSION_DENIED");

 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$_AP."_mmr WHERE item_id='".$docId."'");
 $db->Close();

 $out.= "done!";

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//

