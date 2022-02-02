<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 19-12-2012
 #PACKAGE: bookkeeping
 #DESCRIPTION: Official Gnujiko VAT register.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_vatregister($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'new-register' : return vatregister_newRegister($args, $sessid, $shellid); break;
  case 'register-list' : return vatregister_registerList($args, $sessid, $shellid); break;
  case 'new' : case 'add' : case 'insert' : return vatregister_insert($args, $sessid, $shellid); break;
  case 'edit' : return vatregister_edit($args, $sessid, $shellid); break;
  case 'delete' : return vatregister_delete($args, $sessid, $shellid); break;
  case 'list' : return vatregister_list($args, $sessid, $shellid); break;
  default : return vatregister_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function vatregister_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function vatregister_newRegister($args, $sessid, $shellid)
{
 global $_DATABASE_NAME;
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-year' : {$year=$args[$c+1]; $c++;} break;
   case '--overwrite' : $overWrite=true; break;
  }

 /* Check if register already exists */
 if($overWrite)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("DROP TABLE IF EXISTS `vat_register_".$year."`");
  $db->Close();
 }
 else
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='".$_DATABASE_NAME."' AND table_name='vat_register_".$year."'");
  $db->Read();
  if($db->record[0])
  {
   $db->Close();
   return array('message'=>"The VAT register for ".$year." already exists!");
  }
 }

 /* Create table */
 $qry = "CREATE TABLE `vat_register_".$year."` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`ctime` DATE NOT NULL ,
`rec_type` TINYINT ( 1 ) NOT NULL ,
`subject_id` INT( 11 ) NOT NULL ,
`subject_name` VARCHAR( 80 ) NOT NULL ,
`doc_ap` VARCHAR( 64 ) NOT NULL ,
`doc_ct` VARCHAR( 64 ) NOT NULL ,
`doc_id` INT( 11 ) NOT NULL ,
`doc_ref` VARCHAR( 64 ) NOT NULL ,
`notes` VARCHAR( 255 ) NOT NULL,
INDEX ( `rec_type` )";

 /* Get list of vat rates */
 $vatIds = array();
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_vatrates_items WHERE 1");
 while($db->Read())
 {
  $qry.= ", `vr_".$db->record['id']."_amount` FLOAT NOT NULL , `vr_".$db->record['id']."_vat` FLOAT NOT NULL";
  $vatIds[] = $db->record['id'];
 }
 $db->Close();

 $qry.=")";

 $db = new AlpaDatabase();
 $db->RunQuery($qry);
 $db->Close();

  // Check for totvatreg_purchases and totvatreg_sales table //
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='".$_DATABASE_NAME."' AND table_name='totvatreg_purchases'");
  $db->Read();
  if(!$db->record[0])
  {
   // Create totvatreg_purchases table //
   $qry = "CREATE TABLE `totvatreg_purchases` ( `ref_date` DATE NOT NULL PRIMARY KEY";
   for($c=0; $c < count($vatIds); $c++)
	$qry.= ", `vr_".$vatIds[$c]."_amount` FLOAT NOT NULL , `vr_".$vatIds[$c]."_vat` FLOAT NOT NULL";
   $qry.= ")";
   $db->RunQuery($qry);
   // Create totvatreg_sales table //
   $qry = "CREATE TABLE `totvatreg_sales` ( `ref_date` DATE NOT NULL PRIMARY KEY";
   for($c=0; $c < count($vatIds); $c++)
	$qry.= ", `vr_".$vatIds[$c]."_amount` FLOAT NOT NULL , `vr_".$vatIds[$c]."_vat` FLOAT NOT NULL";
   $qry.= ")";
   $db->RunQuery($qry);
  }
  $db->Close();


 return array('message'=>"The VAT register for ".$year." has been created!");
}
//-------------------------------------------------------------------------------------------------------------------//
function vatregister_registerList($args, $sessid, $shellid)
{
 global $_DATABASE_NAME;
 $out = "";
 $outArr = array();
 $orderBy = "table_name ASC";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--sort-asc' : $orderBy = "table_name ASC"; break;
   case '--sort-desc' : $orderBy = "table_name DESC"; break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT table_name FROM information_schema.tables WHERE table_schema='".$_DATABASE_NAME."' AND table_name LIKE 'vat_register_%' ORDER BY ".$orderBy);
 while($db->Read())
 {
  $year = substr($db->record['table_name'], 13);
  $outArr[] = array('year'=>$year);
  $out.= $year."\n";
 }
 $db->Close();
 $out.= "\n".count($outArr)." registers found.";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function vatregister_insert($args, $sessid, $shellid)
{
 global $_DATABASE_NAME;
 $out = "";
 $outArr = array();

 $_VATS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ctime' : {$ctime=strtotime($args[$c+1]); $c++;} break;
   case '-type' : {$type=$args[$c+1]; $c++;} break; // 1= purchases, 2= sales
   case '-subject' : {$subjectName=$args[$c+1]; $c++;} break;
   case '-subjectid' : {$subjectId=$args[$c+1]; $c++;} break;
   case '-docap' : {$docAp=$args[$c+1]; $c++;} break;
   case '-docct' : {$docCt=$args[$c+1]; $c++;} break;
   case '-docid' : {$docId=$args[$c+1]; $c++;} break;
   case '-docref' : {$docRef=$args[$c+1]; $c++;} break;
   case '-note' : case '-notes' : case '-description' : {$notes=$args[$c+1]; $c++;} break;
   case '-vatid' : {
		 $lastVatId = $args[$c+1]; $c++;
		 if($lastVatId && !$_VATS[$lastVatId])
		  $_VATS[$lastVatId] = array('amount'=>0,'vat'=>0);
		} break;
   case '-amount' : {if($lastVatId) $_VATS[$lastVatId]['amount']+= $args[$c+1]; $c++;} break;
   case '-vat' : {if($lastVatId) $_VATS[$lastVatId]['vat']+= $args[$c+1]; $c++;} break;
  }

 if(!$ctime)
  $ctime = time();
 $year = date('Y',$ctime);

 /* Check if register exists */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='".$_DATABASE_NAME."' AND table_name='vat_register_".$year."'");
 $db->Read();
 if(!$db->record[0])
 {
  $db->Close();
  /* Create table */
  $qry = "CREATE TABLE `vat_register_".$year."` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`ctime` DATE NOT NULL ,
`rec_type` TINYINT ( 1 ) NOT NULL ,
`subject_id` INT( 11 ) NOT NULL ,
`subject_name` VARCHAR( 80 ) NOT NULL ,
`doc_ap` VARCHAR( 64 ) NOT NULL ,
`doc_ct` VARCHAR( 64 ) NOT NULL ,
`doc_id` INT( 11 ) NOT NULL ,
`doc_ref` VARCHAR( 64 ) NOT NULL ,
`notes` VARCHAR( 255 ) NOT NULL,
INDEX ( `rec_type` )";

  /* Get list of vat rates */
  $vatIds = array();
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_vatrates_items WHERE 1");
  while($db->Read())
  {
   $qry.= ", `vr_".$db->record['id']."_amount` FLOAT NOT NULL , `vr_".$db->record['id']."_vat` FLOAT NOT NULL";
   $vatIds[] = $db->record['id'];
  }
  $db->Close();

  $qry.=")";

  $db = new AlpaDatabase();
  $db->RunQuery($qry);
  $db->Close();

  // Check for totvatreg_purchases and totvatreg_sales table //
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='".$_DATABASE_NAME."' AND table_name='totvatreg_purchases'");
  $db->Read();
  if(!$db->record[0])
  {
   // Create totvatreg_purchases table //
   $qry = "CREATE TABLE `totvatreg_purchases` ( `ref_date` DATE NOT NULL PRIMARY KEY";
   for($c=0; $c < count($vatIds); $c++)
	$qry.= ", `vr_".$vatIds[$c]."_amount` FLOAT NOT NULL , `vr_".$vatIds[$c]."_vat` FLOAT NOT NULL";
   $qry.= ")";
   $db->RunQuery($qry);
   // Create totvatreg_sales table //
   $qry = "CREATE TABLE `totvatreg_sales` ( `ref_date` DATE NOT NULL PRIMARY KEY";
   for($c=0; $c < count($vatIds); $c++)
	$qry.= ", `vr_".$vatIds[$c]."_amount` FLOAT NOT NULL , `vr_".$vatIds[$c]."_vat` FLOAT NOT NULL";
   $qry.= ")";
   $db->RunQuery($qry);
  }
  $db->Close();
 }
 else
  $db->Close();



 $db = new AlpaDatabase();
 $qry = "INSERT INTO vat_register_".$year."(ctime,rec_type,subject_id,subject_name,doc_ap,doc_ct,doc_id,doc_ref,notes";
 while(list($k,$v) = each($_VATS))
 {
  $qry.= ",vr_".$k."_amount,vr_".$k."_vat";
 }
 $qry.= ") VALUES('".date('Y-m-d',$ctime)."','".$type."','".$subjectId."','".$db->Purify($subjectName)."','".$docAp."','".$docCt."','".$docId."','"
	.$db->Purify($docRef)."','".$db->Purify($notes)."'";
 reset($_VATS);
 while(list($k,$v) = each($_VATS))
 {
  $qry.= ",'".$v['amount']."','".$v['vat']."'";
 }
 $qry.= ")";

 $db->RunQuery($qry);
 $recId = mysql_insert_id();
 $db->Close();

 $out.= "Record #".$recId." has been inserted into VAT register ".$year;

 // Update totals //
 reset($_VATS);
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM totvatreg_".($type==1 ? "purchases" : "sales")." WHERE ref_date='".date('Y-m-01',$ctime)."'");
 if(!$db->Read())
 {
  $qry = "INSERT INTO totvatreg_".($type==1 ? "purchases" : "sales")."(ref_date";
  while(list($k,$v) = each($_VATS))
  {
   $qry.= ",vr_".$k."_amount,vr_".$k."_vat";
  }
  $qry.= ") VALUES('".date('Y-m-01',$ctime)."'";
  reset($_VATS);
  while(list($k,$v) = each($_VATS))
  {
   $qry.= ",'".$v['amount']."','".$v['vat']."'";
  }
  $qry.= ")";
  $db->RunQuery($qry);
  $db->Close();
 }
 else
 {
  $qry = "";
  while(list($k,$v) = each($_VATS))
  {
   $qry.= ",vr_".$k."_amount='".($db->record['vr_'.$k.'_amount']+$v['amount'])."'";
   $qry.= ",vr_".$k."_vat='".($db->record['vr_'.$k.'_vat']+$v['vat'])."'";
  }
  $db->RunQuery("UPDATE totvatreg_".($type==1 ? "purchases" : "sales")." SET ".ltrim($qry,",")." WHERE ref_date='".$db->record['ref_date']."'");
  $db->Close();
 }
 
 $outArr = array('id'=>$recId,'ctime'=>$ctime,'type'=>$type,'subject_id'=>$subjectId,'subject_name'=>$subjectName,'doc_ap'=>$docAp,'doc_ct'=>$docCt,'doc_id'=>$docId,'doc_ref'=>$docRef,	'notes'=>$notes);

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function vatregister_edit($args, $sessid, $shellid)
{
 global $_DATABASE_NAME;
 $out = "";
 $outArr = array();

 $_VATS = array();
 $_OLD_VATS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-year' : {$year=$args[$c+1]; $c++;} break;
   case '-ctime' : {$ctime=strtotime($args[$c+1]); $c++;} break;
   case '-subject' : {$subjectName=$args[$c+1]; $c++;} break;
   case '-subjectid' : {$subjectId=$args[$c+1]; $c++;} break;
   case '-docap' : {$docAp=$args[$c+1]; $c++;} break;
   case '-docct' : {$docCt=$args[$c+1]; $c++;} break;
   case '-docid' : {$docId=$args[$c+1]; $c++;} break;
   case '-docref' : {$docRef=$args[$c+1]; $c++;} break;
   case '-note' : case '-notes' : case '-description' : {$notes=$args[$c+1]; $c++;} break;
   case '-vatid' : {
		 $lastVatId = $args[$c+1]; $c++;
		 if($lastVatId && !$_VATS[$lastVatId])
		  $_VATS[$lastVatId] = array('amount'=>0,'vat'=>0);
		} break;
   case '-amount' : {if($lastVatId) $_VATS[$lastVatId]['amount']+= $args[$c+1]; $c++;} break;
   case '-vat' : {if($lastVatId) $_VATS[$lastVatId]['vat']+= $args[$c+1]; $c++;} break;
  }

 if(!$id)
  return array('message'=>'You must specify the record id','error'=>'INVALID_RECORD_ID');

 if(!$year)
 {
  if(!$ctime)
   return array('message'=>'You must specify the year of the register','error'=>'INVALID_YEAR');
  $year = date('Y',$ctime);
 }

 /* Check if record exists */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM vat_register_".$year." WHERE id='".$id."'");
 if(!$db->Read())
  return array('message'=>"Record #".$id." does not exists!",'error'=>"RECORD_DOES_NOT_EXISTS");
 $oldCtime = strtotime($db->record['ctime']);
 $type = $db->record['rec_type'];
 /* Get VAT fields */
 $fields_count = @mysql_num_fields($db->lastQueryResult);
 for ($c = 0; $c < $fields_count; $c++)
 {
  $name = @mysql_field_name($db->lastQueryResult,$c);
  if((substr($name,0,3) == "vr_") && (substr($name,-7) == "_amount"))
  {
   $k = substr($name,3,strlen($name)-10);
   $_OLD_VATS[$k] = array('amount'=>$db->record['vr_'.$k.'_amount'], 'vat'=>$db->record['vr_'.$k.'_vat']);
   if(!$_VATS[$k])
   {
	$_VATS[$k] = array('amount'=>0,'vat'=>0);
   }
  }
 }
 $db->Close();

 $db = new AlpaDatabase();
 $q = "";
 if($ctime)
  $q.= ",ctime='".date('Y-m-d',$ctime)."'";
 if(isset($subjectId))
  $q.= ",subject_id='".$subjectId."'";
 if(isset($subjectName))
  $q.= ",subject_name='".$db->Purify($subjectName)."'";
 if(isset($docAp))
  $q.= ",doc_ap='".$docAp."'";
 if(isset($docCt))
  $q.= ",doc_ct='".$docCt."'";
 if(isset($docId))
  $q.= ",doc_id='".$docId."'";
 if(isset($docRef))
  $q.= ",doc_ref='".$db->Purify($docRef)."'";
 if(isset($notes))
  $q.= ",notes='".$db->Purify($notes)."'";
 
 while(list($k,$v) = each($_VATS))
 {
  $q.= ",vr_".$k."_amount='".$v['amount']."',vr_".$k."_vat='".$v['vat']."'";
 }

 $db->RunQuery("UPDATE vat_register_".$year." SET ".ltrim($q,",")." WHERE id='".$id."'");
 $db->Close();

 $out.= "Record #".$id." has been updated.";

 // Update totals //
 reset($_VATS);
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM totvatreg_".($type==1 ? "purchases" : "sales")." WHERE ref_date='".date('Y-m-01',$ctime ? $ctime : $oldCtime)."'");
 if(!$db->Read())
 {
  $qry = "INSERT INTO totvatreg_".($type==1 ? "purchases" : "sales")."(ref_date";
  while(list($k,$v) = each($_VATS))
  {
   $qry.= ",vr_".$k."_amount,vr_".$k."_vat";
  }
  $qry.= ") VALUES('".date('Y-m-01',$ctime ? $ctime : $oldCtime)."'";
  reset($_VATS);
  while(list($k,$v) = each($_VATS))
  {
   $qry.= ",'".$v['amount']."','".$v['vat']."'";
  }
  $qry.= ")";
  $db->RunQuery($qry);
  $db->Close();
 }
 else
 {
  $qry = "";
  while(list($k,$v) = each($_VATS))
  {
   $qry.= ",vr_".$k."_amount='".($db->record['vr_'.$k.'_amount']+($v['amount']-$_OLD_VATS[$k]['amount']))."'";
   $qry.= ",vr_".$k."_vat='".($db->record['vr_'.$k.'_vat']+($v['vat']-$_OLD_VATS[$k]['vat']))."'";
  }
  $db->RunQuery("UPDATE totvatreg_".($type==1 ? "purchases" : "sales")." SET ".ltrim($qry,",")." WHERE ref_date='".$db->record['ref_date']."'");
  $db->Close();
 }

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function vatregister_delete($args, $sessid, $shellid)
{
 global $_DATABASE_NAME;
 $out = "";
 $outArr = array();

 $_VATS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-year' : {$year=$args[$c+1]; $c++;} break;
   case '-docap' : {$docAp=$args[$c+1]; $c++;} break;
   case '-docid' : {$docId=$args[$c+1]; $c++;} break;
  }

 /* Check if record exists */
 if($id)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM vat_register_".$year." WHERE id='".$id."'");
  if(!$db->Read())
   return array('message'=>"Record #".$id." does not exists!",'error'=>"RECORD_DOES_NOT_EXISTS");
 }
 else if($docAp && $docId)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM vat_register_".$year." WHERE doc_ap='".$docAp."' AND doc_id='".$docId."' LIMIT 1");
  if(!$db->Read())
   return array('message'=>"The record associated with the document #".$docId." does not exists!",'error'=>"RECORD_DOES_NOT_EXISTS");
  $id = $db->record['id'];
 }
 else
  return array('message'=>"You must specify the record to delete",'error'=>'INVALID_RECORD_ID');
 
 $ctime = strtotime($db->record['ctime']);
 $type = $db->record['rec_type'];
 /* Get VAT fields */
 $fields_count = @mysql_num_fields($db->lastQueryResult);
 for ($c = 0; $c < $fields_count; $c++)
 {
  $name = @mysql_field_name($db->lastQueryResult,$c);
  if((substr($name,0,3) == "vr_") && (substr($name,-7) == "_amount"))
  {
   $k = substr($name,3,strlen($name)-10);
   $_VATS[$k] = array('amount'=>$db->record['vr_'.$k.'_amount'], 'vat'=>$db->record['vr_'.$k.'_vat']);
  }
 }

 $db->RunQuery("DELETE FROM vat_register_".$year." WHERE id='".$id."'");
 $db->Close();

 // Update totals //
 reset($_VATS);
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM totvatreg_".($type==1 ? "purchases" : "sales")." WHERE ref_date='".date('Y-m-01',$ctime)."'");
 if($db->Read())
 {
  $qry = "";
  while(list($k,$v) = each($_VATS))
  {
   $qry.= ",vr_".$k."_amount='".($db->record['vr_'.$k.'_amount']-$v['amount'])."'";
   $qry.= ",vr_".$k."_vat='".($db->record['vr_'.$k.'_vat']-$v['vat'])."'";
  }
  $db->RunQuery("UPDATE totvatreg_".($type==1 ? "purchases" : "sales")." SET ".ltrim($qry,",")." WHERE ref_date='".$db->record['ref_date']."'");
 }
 $db->Close();

 return array('message'=>"Record #".$id." has been removed.");
}
//-------------------------------------------------------------------------------------------------------------------//
function vatregister_list($args, $sessid, $shellid)
{
 global $_DATABASE_NAME;
 $orderBy = "ctime DESC";
 $out = "";
 $outArr = array();

 $_VAT_IDS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$from=strtotime($args[$c+1]); $c++;} break;
   case '-to' : {$to=strtotime($args[$c+1]); $c++;} break;
   case '-type' : {$type=$args[$c+1]; $c++;} break; // 1= purchases , 2=sales

   case '-docap' : {$docAp=$args[$c+1]; $c++;} break;
   case '-docct' : {$docCt=$args[$c+1]; $c++;} break;
   case '-docid' : {$docId=$args[$c+1]; $c++;} break;
   case '-docref' : {$docRef=$args[$c+1]; $c++;} break;
   case '-notes' : {$notes=$args[$c+1]; $c++;} break;
   case '-subject' : {$subjectName=$args[$c+1]; $c++;} break;
   case '-subjectid' : {$subjectId=$args[$c+1]; $c++;} break;

   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '--get-totals' : $getTotals=true; break;
  }

 if(!$from && !$to)
  $year = date('Y');
 else
  $year = date('Y',$from ? $from : $to);

 $db = new AlpaDatabase();
 $qry = "";
 if($from)
  $qry.= " AND ctime>='".date('Y-m-d',$from)."'";
 if($to)
  $qry.= " AND ctime<'".date('Y-m-d',$to)."'";
 if($type)
  $qry.= " AND rec_type='".$type."'";
 if($subjectId)
  $qry.= " AND subject_id='".$subjectId."'";
 else if($subjectName)
 {
  $subjectName = $db->Purify($subjectName);
  $qry.= " AND (subject_name='".$subjectName."' OR subject_name LIKE '".$subjectName."%' OR subject_name LIKE '%".$subjectName."' OR subject_name LIKE '%"
	.$subjectName."%')";
 }
 if($docCt)
  $qry.= " AND doc_ct='".$docCt."'";
 if($docAp && $docId)
  $qry.= " AND doc_ap='".$docAp."' AND doc_id='".$docId."'";
 if($docRef)
 {
  $docRef = $db->Purify($docRef);
  $qry.= " AND (doc_ref='".$docRef."' OR doc_ref LIKE '".$docRef."%' OR doc_ref LIKE '%".$docRef."' OR doc_ref LIKE '%".$docRef."%')";  
 }
 if($notes)
 {
  $notes = $db->Purify($notes);
  $qry.= " AND (notes='".$notes."' OR notes LIKE '".$notes."%' OR notes LIKE '%".$notes."' OR notes LIKE '%".$notes."%')";  
 }

 $db->RunQuery("SELECT COUNT(*) FROM vat_register_".$year." WHERE ".ltrim($qry," AND "));
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
  $outArr['serpinfo']['resultsperpage'] = $serpRPP;
  $outArr['serpinfo']['currentpage'] = $serpFrom ? floor($serpFrom/$serpRPP)+1 : 1;
  $outArr['serpinfo']['datafrom'] = $serpFrom;
 }

 /* SELECT QRY */
 $db->RunQuery("SELECT * FROM vat_register_".$year." WHERE ".ltrim($qry," AND ")." ORDER BY ".$orderBy.($limit ? " LIMIT $limit" : ""));
 while($db->Read())
 {
  $a = array('id'=>$db->record['id'], 'ctime'=>strtotime($db->record['ctime']), 'type'=>$db->record['rec_type'], 'subject_id'=>$db->record['subject_id'],
	'subject_name'=>$db->record['subject_name'], 'doc_ap'=>$db->record['doc_ap'], 'doc_ct'=>$db->record['doc_ct'], 'doc_id'=>$db->record['doc_id'],
	'doc_ref'=>$db->record['doc_ref'], 'notes'=>$db->record['notes']);

  if($a['doc_ap'] && $a['doc_id'])
  {
   $ret = GShell("dynarc item-info -ap `".$a['doc_ap']."` -id `".$a['doc_id']."`",$sessid,$shellid);
   $a['doc_info'] = $ret['outarr'];
  }

  if(!$outArr['items']) // if is the first cicle ...
  {
   /* Get VAT fields */
   $fields_count = @mysql_num_fields($db->lastQueryResult);
   for ($c = 0; $c < $fields_count; $c++)
   {
    $fname = @mysql_field_name($db->lastQueryResult,$c);
    if((substr($fname,0,3) == "vr_") && (substr($fname,-7) == "_amount"))
    {
     $k = substr($fname,3,strlen($fname)-10);
	 $_VAT_IDS[] = $k;
    }
   }
  }

  $a['amount'] = 0;
  $a['vat'] = 0;

  for($c=0; $c < count($_VAT_IDS); $c++)
  {
   $k = $_VAT_IDS[$c];
   if($db->record["vr_".$k."_amount"])
   {
	$a['vatrates'][] = array('id'=>$k, 'amount'=>$db->record["vr_".$k."_amount"], 'vat'=>$db->record["vr_".$k."_vat"]);
	$a['amount']+= $db->record["vr_".$k."_amount"];
	$a['vat']+= $db->record["vr_".$k."_vat"];
   }
  }

  $a['total'] = $a['amount']+$a['vat'];

  $outArr['items'][] = $a;
 }
 $db->Close();

 if($getTotals && ($docAp || $docCt || $docId || $docRef || $subjectName || $subjectId))
 {
  // GET FILTERED TOTALS //
  $outArr['tot_purchases'] = array();
  $outArr['tot_sales'] = array();

  if(!count($_VAT_IDS))
  {
   /* Get VAT fields */
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT * FROM totvatreg_purchases WHERE 1 LIMIT 1");
   $db->Read();
   /* Get VAT fields */
   $fields_count = @mysql_num_fields($db->lastQueryResult);
   for ($c = 0; $c < $fields_count; $c++)
   {
    $fname = @mysql_field_name($db->lastQueryResult,$c);
    if((substr($fname,0,3) == "vr_") && (substr($fname,-7) == "_amount"))
    {
     $k = substr($fname,3,strlen($fname)-10);
 	 $_VAT_IDS[] = $k;
	 $outArr['tot_purchases'][] = array('id'=>$k, 'amount'=>0, 'vat'=>0);
	 $outArr['tot_sales'][] = array('id'=>$k, 'amount'=>0, 'vat'=>0);
    }
   }
   $db->Close();
  }

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM vat_register_".$year." WHERE ".ltrim($qry," AND "));
  while($db->Read())
  {
   if($db->record['rec_type'] == 1)
   {
	for($c=0; $c < count($_VAT_IDS); $c++)
	{
	 $k = $_VAT_IDS[$c];
	 $outArr['tot_purchases'][$c]['amount']+= $db->record['vr_'.$k.'_amount'];
	 $outArr['tot_purchases'][$c]['vat']+= $db->record['vr_'.$k.'_vat'];
	}
   }
   else
   {
	for($c=0; $c < count($_VAT_IDS); $c++)
	{
	 $k = $_VAT_IDS[$c];
	 $outArr['tot_sales'][$c]['amount']+= $db->record['vr_'.$k.'_amount'];
	 $outArr['tot_sales'][$c]['vat']+= $db->record['vr_'.$k.'_vat'];
	}
   } 
  }
  $db->Close();
 }
 else if($getTotals)
 {
  // GET ALL TOTALS //
  $outArr['tot_purchases'] = array();
  $outArr['tot_sales'] = array();

  if(!count($_VAT_IDS))
  {
   /* Get VAT fields */
   $db = new AlpaDatabase();
   $db->RunQuery("SELECT * FROM totvatreg_purchases WHERE 1 LIMIT 1");
   $db->Read();
   /* Get VAT fields */
   $fields_count = @mysql_num_fields($db->lastQueryResult);
   for ($c = 0; $c < $fields_count; $c++)
   {
    $fname = @mysql_field_name($db->lastQueryResult,$c);
    if((substr($fname,0,3) == "vr_") && (substr($fname,-7) == "_amount"))
    {
     $k = substr($fname,3,strlen($fname)-10);
 	 $_VAT_IDS[] = $k;
	 $outArr['tot_purchases'][] = array('id'=>$k, 'amount'=>0, 'vat'=>0);
	 $outArr['tot_sales'][] = array('id'=>$k, 'amount'=>0, 'vat'=>0);
    }
   }
   $db->Close();
  }

  if(!$from && !$to)
  {
   if(!$type || ($type == 1))
   {
    $db = new AlpaDatabase();
    $db->RunQuery("SELECT * FROM totvatreg_purchases WHERE 1");
	while($db->Read())
	{
	 for($c=0; $c < count($_VAT_IDS); $c++)
	 {
	  $k = $_VAT_IDS[$c];
	  if(!$outArr['tot_purchases'][$c])
	   $outArr['tot_purchases'][$c] = array('id'=>$k, 'amount'=>0, 'vat'=>0);
	  $outArr['tot_purchases'][$c]['amount']+= $db->record['vr_'.$k.'_amount'];
	  $outArr['tot_purchases'][$c]['vat']+= $db->record['vr_'.$k.'_vat'];
	 }
	}
	$db->Close();
   }
   if(!$type || ($type == 2))
   {
    $db = new AlpaDatabase();
    $db->RunQuery("SELECT * FROM totvatreg_sales WHERE 1");
	while($db->Read())
	{
	 for($c=0; $c < count($_VAT_IDS); $c++)
	 {
	  $k = $_VAT_IDS[$c];
	  if(!$outArr['tot_sales'][$c])
	   $outArr['tot_sales'][$c] = array('id'=>$k, 'amount'=>0, 'vat'=>0);
	  $outArr['tot_sales'][$c]['amount']+= $db->record['vr_'.$k.'_amount'];
	  $outArr['tot_sales'][$c]['vat']+= $db->record['vr_'.$k.'_vat'];
	 }
	}
	$db->Close();
   }
  }
  else
  {
   // FILTER BY DATE //
   $fromDayNum = date('z',$from);
   $fromDay = date('j',$from);
   $fromMonth = date('n',$from);
   $fromYear = date('Y',$from);

   $toDayNum = date('z',$from);
   $toDay = date('j',$from);
   $toMonth = date('n',$from);
   $toYear = date('Y',$from);

   $monthDiff = ($toMonth-$fromMonth) + (12*($toYear-$fromYear));
   
   if($monthDiff > 1)
   {
	if($fromDay == 1)
	{
	 if(!$type || ($type == 1))
	 {
	  $db = new AlpaDatabase();
	  $db->RunQuery("SELECT * FROM totvatreg_purchases WHERE ref_date='".date('Y-m-01',$from)."'");
	  $db->Read();
	  for($c=0; $c < count($_VAT_IDS); $c++)
	  {
	   $k = $_VAT_IDS[$c];
	   if(!$outArr['tot_purchases'][$c])
	    $outArr['tot_purchases'][$c] = array('id'=>$k, 'amount'=>0, 'vat'=>0);
	   $outArr['tot_purchases'][$c]['amount']+= $db->record['vr_'.$k.'_amount'];
	   $outArr['tot_purchases'][$c]['vat']+= $db->record['vr_'.$k.'_vat'];
	  }
	  $db->Close();
	 }
	 if(!$type || ($type == 2))
	 {
	  $db = new AlpaDatabase();
	  $db->RunQuery("SELECT * FROM totvatreg_sales WHERE ref_date='".date('Y-m-01',$from)."'");
	  $db->Read();
	  for($c=0; $c < count($_VAT_IDS); $c++)
	  {
	   $k = $_VAT_IDS[$c];
	   if(!$outArr['tot_sales'][$c])
	    $outArr['tot_sales'][$c] = array('id'=>$k, 'amount'=>0, 'vat'=>0);
	   $outArr['tot_sales'][$c]['amount']+= $db->record['vr_'.$k.'_amount'];
	   $outArr['tot_sales'][$c]['vat']+= $db->record['vr_'.$k.'_vat'];
	  }
	  $db->Close();
	 }
	}
	else
	{
	 $db = new AlpaDatabase();
	 $db->RunQuery("SELECT * FROM vat_register_".$year." WHERE ctime>='".date('Y-m-d',$from)."' AND ctime<'".date('Y-m-01',strtotime("+1 month",$from))."'".($type ? ($type == 2 ? " AND rec_type=2" : " AND rec_type=1") : ""));
	 while($db->Read())
	 { 
	  if($db->record['rec_type'] == 2)
	  { 
	   for($c=0; $c < count($_VAT_IDS); $c++)
	   {
	    $k = $_VAT_IDS[$c];
	    if(!$outArr['tot_sales'][$c])
	     $outArr['tot_sales'][$c] = array('id'=>$k, 'amount'=>0, 'vat'=>0);
	    $outArr['tot_sales'][$c]['amount']+= $db->record['vr_'.$k.'_amount'];
	    $outArr['tot_sales'][$c]['vat']+= $db->record['vr_'.$k.'_vat'];
	   }
	  }
	  else
	  {
	   for($c=0; $c < count($_VAT_IDS); $c++)
	   {
	    $k = $_VAT_IDS[$c];
	    if(!$outArr['tot_purchases'][$c])
	     $outArr['tot_purchases'][$c] = array('id'=>$k, 'amount'=>0, 'vat'=>0);
	    $outArr['tot_purchases'][$c]['amount']+= $db->record['vr_'.$k.'_amount'];
	    $outArr['tot_purchases'][$c]['vat']+= $db->record['vr_'.$k.'_vat'];
	   }
	  }
	 }
	 $db->Close();
    }

    // secondo step //
	 if(!$type || ($type == 1))
	 {
	  $db = new AlpaDatabase();
	  $db->RunQuery("SELECT * FROM totvatreg_purchases WHERE ref_date>='".date('Y-m-01',strtotime("+1 month",$from))."' AND ref_date<'"
		.date('Y-m-01',$to)."'");
	  while($db->Read())
	  {
	   for($c=0; $c < count($_VAT_IDS); $c++)
	   {
	    $k = $_VAT_IDS[$c];
	    if(!$outArr['tot_purchases'][$c])
	     $outArr['tot_purchases'][$c] = array('id'=>$k, 'amount'=>0, 'vat'=>0);
	    $outArr['tot_purchases'][$c]['amount']+= $db->record['vr_'.$k.'_amount'];
	    $outArr['tot_purchases'][$c]['vat']+= $db->record['vr_'.$k.'_vat'];
	   }
	  }
	  $db->Close();
	 }

	 if(!$type || ($type == 2))
	 {
	  $db = new AlpaDatabase();
	  $db->RunQuery("SELECT * FROM totvatreg_sales WHERE ref_date>='".date('Y-m-01',strtotime("+1 month",$from))."' AND ref_date<'"
		.date('Y-m-01',$to)."'");
	  while($db->Read())
	  {
	   for($c=0; $c < count($_VAT_IDS); $c++)
	   {
	    $k = $_VAT_IDS[$c];
	    if(!$outArr['tot_sales'][$c])
	     $outArr['tot_sales'][$c] = array('id'=>$k, 'amount'=>0, 'vat'=>0);
	    $outArr['tot_sales'][$c]['amount']+= $db->record['vr_'.$k.'_amount'];
	    $outArr['tot_sales'][$c]['vat']+= $db->record['vr_'.$k.'_vat'];
	   }
	  }
	  $db->Close();
	 }

	// terzo step //
	if($toDay > 1)
    {
	 $db = new AlpaDatabase();
	 $db->RunQuery("SELECT * FROM vat_register_".$year." WHERE ctime>='".date('Y-m-01',$to)."' AND ctime<'".date('Y-m-d',$to)."'"
		.($type ? ($type == 2 ? " AND rec_type=2" : " AND rec_type=1") : ""));
	 while($db->Read())
	 { 
	  if($db->record['rec_type'] == 2)
	  { 
	   for($c=0; $c < count($_VAT_IDS); $c++)
	   {
	    $k = $_VAT_IDS[$c];
	    if(!$outArr['tot_sales'][$c])
	     $outArr['tot_sales'][$c] = array('id'=>$k, 'amount'=>0, 'vat'=>0);
	    $outArr['tot_sales'][$c]['amount']+= $db->record['vr_'.$k.'_amount'];
	    $outArr['tot_sales'][$c]['vat']+= $db->record['vr_'.$k.'_vat'];
	   }
	  }
	  else
	  {
	   for($c=0; $c < count($_VAT_IDS); $c++)
	   {
	    $k = $_VAT_IDS[$c];
	    if(!$outArr['tot_purchases'][$c])
	     $outArr['tot_purchases'][$c] = array('id'=>$k, 'amount'=>0, 'vat'=>0);
	    $outArr['tot_purchases'][$c]['amount']+= $db->record['vr_'.$k.'_amount'];
	    $outArr['tot_purchases'][$c]['vat']+= $db->record['vr_'.$k.'_vat'];
	   }
	  }
	 }
	 $db->Close();
	}
	// fine //
   }
   else
   {
	$db = new AlpaDatabase();
	$db->RunQuery("SELECT * FROM vat_register_".$year." WHERE ctime>='".date('Y-m-d',$from)."' AND ctime<'".date('Y-m-d',$to)."'"
		.($type ? ($type == 2 ? " AND rec_type=2" : " AND rec_type=1") : ""));
	while($db->Read())
	{ 
	 if($db->record['rec_type'] == 2)
	 { 
	  for($c=0; $c < count($_VAT_IDS); $c++)
	  {
	   $k = $_VAT_IDS[$c];
	   if(!$outArr['tot_sales'][$c])
	    $outArr['tot_sales'][$c] = array('id'=>$k, 'amount'=>0, 'vat'=>0);
	   $outArr['tot_sales'][$c]['amount']+= $db->record['vr_'.$k.'_amount'];
	   $outArr['tot_sales'][$c]['vat']+= $db->record['vr_'.$k.'_vat'];
	  }
	 }
	 else
	 {
	  for($c=0; $c < count($_VAT_IDS); $c++)
	  {
	   $k = $_VAT_IDS[$c];
	   if(!$outArr['tot_purchases'][$c])
	    $outArr['tot_purchases'][$c] = array('id'=>$k, 'amount'=>0, 'vat'=>0);
	   $outArr['tot_purchases'][$c]['amount']+= $db->record['vr_'.$k.'_amount'];
	   $outArr['tot_purchases'][$c]['vat']+= $db->record['vr_'.$k.'_vat'];
	  }
	 }
	}
	$db->Close();
   }
   // fine //
  }
 }

 $out.= count($outArr['items'])." of ".$outArr['count']." record founds";
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

