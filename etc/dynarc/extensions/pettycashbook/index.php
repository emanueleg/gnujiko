<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 19-12-2012
 #PACKAGE: bookkeeping
 #DESCRIPTION: Petty Cash Book
 #VERSION: 2.1beta
 #CHANGELOG: 
 #TODO:Rifare funzione import & export e completare funzioni syncimport & syncexport.
 #DEPENDS: cashresources
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` ADD `res_in` INT( 11 ) NOT NULL ,
ADD `res_out` INT( 11 ) NOT NULL ,
ADD `incomes` FLOAT NOT NULL ,
ADD `expenses` FLOAT NOT NULL ,
ADD `doc_ap` VARCHAR( 64 ) NOT NULL ,
ADD `doc_id` INT( 11 ) NOT NULL ,
ADD `doc_ref` VARCHAR( 64 ) NOT NULL ,
ADD `subject_id` INT( 11 ) NOT NULL ,
ADD `subject_name` VARCHAR( 80 ) NOT NULL ,
ADD INDEX ( `res_in` , `res_out` , `doc_ap` , `doc_id` , `doc_ref` , `subject_id` )");
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_totals` (
  `ref_date` date NOT NULL,
  `incomes` float NOT NULL,
  `expenses` float NOT NULL,
  `transfers` float NOT NULL,
  PRIMARY KEY (`ref_date`))");
 $db->Close();
 return array("message"=>"PettyCashBook extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` DROP `res_in`, DROP `res_out`, DROP `incomes`,
  DROP `expenses`, DROP `doc_ap`, DROP `doc_id`, DROP `doc_ref`, DROP `subject_id`, DROP `subject_name`");
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_totals`");
 $db->Close();

 return array("message"=>"PettyCashBook extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_catset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_pettycashbook_catset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'resin' : case 'resource-in' : {$resIn=$args[$c+1]; $c++;} break;
   case 'resout' : case 'resource-out' : {$resOut=$args[$c+1]; $c++;} break;
   case 'incomes' : case 'in' : {$incomes=$args[$c+1]; $c++;} break;
   case 'expenses' : case 'out' : {$expenses=$args[$c+1]; $c++;} break;
   case 'docap' : {$docAp=$args[$c+1]; $c++;} break;
   case 'docid' : {$docId=$args[$c+1]; $c++;} break;
   case 'docref' : {$docRef=$args[$c+1]; $c++;} break;
   case 'subject' : {$subjectName=$args[$c+1]; $c++;} break;
   case 'subjectid' : {$subjectId=$args[$c+1]; $c++;} break;
  }

 $refDate = date('Y-m-01',$itemInfo['ctime']);

 /* Get old amounts */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT ctime,incomes,expenses,res_in,res_out FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 $oldIncomes = $db->record['incomes'];
 $oldExpenses = $db->record['expenses'];
 if(!$resIn) $resIn = $db->record['res_in'];
 if(!$resOut) $resOut = $db->record['res_out'];
 $db->Close();

 /* Get record in table totals */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_totals WHERE ref_date='".$refDate."'");
 if($db->Read())
 {
  $totIncomes = $db->record['incomes'];
  $totExpenses = $db->record['expenses'];
  $totTransfers = $db->record['transfers'];
  $recExists=true;
 }
 else
  $recExists=false;
 $db->Close();

 $q = "";
 if(isset($resIn))
  $q.= ",res_in='".$resIn."'";
 if(isset($resOut))
  $q.= ",res_out='".$resOut."'";
 if(isset($incomes))
  $q.= ",incomes='".$incomes."'";
 if(isset($expenses))
  $q.= ",expenses='".$expenses."'";
 if(isset($docAp))
  $q.= ",doc_ap='".$docAp."'";
 if(isset($docId))
  $q.= ",doc_id='".$docId."'";
 if(isset($docRef))
  $q.= ",doc_ref='".$docRef."'";

 if($subjectId)
 {
  $ret = GShell("dynarc item-info -ap `rubrica` -id `".$subjectId."`",$sessid,$shellid);
  $db = new AlpaDatabase();
  if(!$ret['error'])
   $q.= ",subject_id='".$subjectId."',subject_name='".$db->Purify($ret['outarr']['name'])."'";
  else
   $q.= ",subject_id='0',subject_name='".$db->Purify($subjectName)."'";
 }
 else if($subjectName)
 {
  $ret = GShell("dynarc item-info -ap `rubrica` -name `".$subjectName."`",$sessid,$shellid);
  $db = new AlpaDatabase();
  if(!$ret['error'])
   $q.= ",subject_id='".$ret['outarr']['id']."',subject_name='".$db->Purify($ret['outarr']['name'])."'";
  else
   $q.= ",subject_id='0',subject_name='".$db->Purify($subjectName)."'";
 }

 $db = new AlpaDatabase();
 if($q)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");
 $db->Close();

 if($resIn && $resOut)
 {
  if($incomes > $oldIncomes)
  {
   $db = new AlpaDatabase();
   if($recExists)
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_totals SET transfers='".($totTransfers+($incomes-$oldIncomes))."' WHERE ref_date='".$refDate."'");
   else
	$db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_totals(ref_date,transfers) VALUES('".$refDate."','".$incomes."')");
   $db->Close();
  }
  else if($incomes < $oldIncomes)
  {
   $db = new AlpaDatabase();
   if($recExists)
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_totals SET transfers='".($totTransfers-($oldIncomes-$incomes))."' WHERE ref_date='".$refDate."'");
   else
	$db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_totals(ref_date,transfers) VALUES('".$refDate."','".$incomes."')");
   $db->Close();
  }
 }
 else if($resIn)
 {
  if($incomes > $oldIncomes)
  {
   $db = new AlpaDatabase();
   if($recExists)
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_totals SET incomes='".($totIncomes+($incomes-$oldIncomes))."' WHERE ref_date='".$refDate."'");
   else
	$db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_totals(ref_date,incomes) VALUES('".$refDate."','".$incomes."')");
   $db->Close();
  }
  else if($incomes < $oldIncomes)
  {
   $db = new AlpaDatabase();
   if($recExists)
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_totals SET incomes='".($totIncomes-($oldIncomes-$incomes))."' WHERE ref_date='".$refDate."'");
   else
	$db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_totals(ref_date,incomes) VALUES('".$refDate."','".$incomes."')");
   $db->Close();
  }
 }
 else if($resOut)
 {
  if($expenses > $oldExpenses)
  {
   $db = new AlpaDatabase();
   if($recExists)
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_totals SET expenses='".($totExpenses+($expenses-$oldExpenses))."' WHERE ref_date='".$refDate."'");
   else
	$db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_totals(ref_date,expenses) VALUES('".$refDate."','".$expenses."')");
   $db->Close();
  }
  else if($expenses < $oldExpenses)
  {
   $db = new AlpaDatabase();
   if($recExists)
	$db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_totals SET expenses='".($totExpenses-($oldExpenses-$expenses))."' WHERE ref_date='".$refDate."'");
   else
	$db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_totals(ref_date,expenses) VALUES('".$refDate."','".$expenses."')");
   $db->Close();
  }
 }

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_catget($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_pettycashbook_catget($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'resin' : case 'resource-in' : $resIn=true; break;
   case 'resout' : case 'resource-out' : $resOut=true; break;
   case 'incomes' : case 'in' : $incomes=true; break;
   case 'expenses' : case 'out' : $expenses=true; break;
   case 'docap' : $docAp=true; break;
   case 'docid' : $docId=true; break;
   case 'docref' : $docRef=true; break;
   case 'subject' : $subjectName=true; break;
   case 'subjectid' : $subjectId=true; break;
  }

 if(!count($args))
  $all=true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();

 if($resIn || $all)
 {
  if($db->record['res_in'])
  {
   $ret = GShell("cashresources info -id `".$db->record['res_in']."`",$sessid,$shellid);
   if(!$ret['error'])
    $itemInfo['res_in'] = $ret['outarr'];
  }
 }

 if($resOut || $all)
 {
  if($db->record['res_out'])
  {
   $ret = GShell("cashresources info -id `".$db->record['res_out']."`",$sessid,$shellid);
   if(!$ret['error'])
    $itemInfo['res_out'] = $ret['outarr'];
  }
 }
 
 if($incomes || $all) $itemInfo['incomes'] = $db->record['incomes'];
 if($expenses || $all) $itemInfo['expenses'] = $db->record['expenses'];
 
 if($docAp || $docId || $all)
 {
  if($db->record['doc_ap'])
  {
   $ret = GShell("dynarc item-info -ap `".$db->record['doc_ap']."` -id `".$db->record['doc_id']."`",$sessid,$shellid);
   if(!$ret['error'])
   {
	$itemInfo['doc_ap'] = $db->record['doc_ap'];
	$itemInfo['doc_id'] = $db->record['doc_id'];
	$itemInfo['doc_info'] = $ret['outarr'];
   }
  }
 }

 if($docRef || $all) $itemInfo['doc_ref'] = $db->record['doc_ref'];
 if($subjectId || $all) $itemInfo['subject_id'] = $db->record['subject_id'];
 if($subjectName || $all) $itemInfo['subject_name'] = $db->record['subject_name'];

 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 if($itemInfo['trash'])
  return true;

 $refDate = date('Y-m-01',$itemInfo['ctime']);
 
 /* Get old amounts */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT incomes,expenses,res_in,res_out FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 $oldIncomes = $db->record['incomes'];
 $oldExpenses = $db->record['expenses'];
 $resIn = $db->record['res_in'];
 $resOut = $db->record['res_out'];
 $db->Close();

 /* Update totals */
 $db = new AlpaDatabase();
 if($resIn && $resOut)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_totals SET transfers=transfers-".$oldIncomes." WHERE ref_date='".$refDate."'");
 else if($resIn)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_totals SET incomes=incomes-".$oldIncomes." WHERE ref_date='".$refDate."'");
 else if($resOut)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_totals SET expenses=expenses-".$oldExpenses." WHERE ref_date='".$refDate."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 $refDate = date('Y-m-01',$itemInfo['ctime']);
 
 /* Get old amounts */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT incomes,expenses,res_in,res_out FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 $oldIncomes = $db->record['incomes'];
 $oldExpenses = $db->record['expenses'];
 $resIn = $db->record['res_in'];
 $resOut = $db->record['res_out'];
 $db->Close();

 /* Update totals */
 $db = new AlpaDatabase();
 if($resIn && $resOut)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_totals SET transfers=transfers-".$oldIncomes." WHERE ref_date='".$refDate."'");
 else if($resIn)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_totals SET incomes=incomes-".$oldIncomes." WHERE ref_date='".$refDate."'");
 else if($resOut)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_totals SET expenses=expenses-".$oldExpenses." WHERE ref_date='".$refDate."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{

 /* Get old amounts */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT ctime,incomes,expenses,res_in,res_out FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 $refDate = date('Y-m-01',strtotime($db->record['ctime']));
 $oldIncomes = $db->record['incomes'];
 $oldExpenses = $db->record['expenses'];
 $resIn = $db->record['res_in'];
 $resOut = $db->record['res_out'];
 $db->Close();

 /* Update totals */
 $db = new AlpaDatabase();
 if($resIn && $resOut)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_totals SET transfers=transfers+".$oldIncomes." WHERE ref_date='".$refDate."'");
 else if($resIn)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_totals SET incomes=incomes+".$oldIncomes." WHERE ref_date='".$refDate."'");
 else if($resOut)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_totals SET expenses=expenses+".$oldExpenses." WHERE ref_date='".$refDate."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_export($sessid, $shellid, $archiveInfo, $itemInfo)
{
 $xml = "";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_import($sessid, $shellid, $archiveInfo, $itemInfo, $node)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_pettycashbook_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

