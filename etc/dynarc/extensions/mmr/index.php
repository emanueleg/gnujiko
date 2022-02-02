<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-10-2016
 #PACKAGE: dynarc-mmr-extension
 #DESCRIPTION: Money Movements Reports, extension for Dynarc,
 #VERSION: 2.5beta
 #CHANGELOG: 24-10-2016 : MySQLi integration.
			 20-04-2014 : Aggiunto setallpaid su funzione set.
			 14-02-2014 : Float fix.
			 05-02-2014 : Aggiunto data documento.
			 31-01-2014 : Aggiunto riba.
			 03-12-2012 : Completamento delle funzioni principali.
 #TODO:
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_mmr` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`item_id` INT( 11 ) NOT NULL ,
`doc_date` DATE NOT NULL ,
`op_time` DATETIME NOT NULL ,
`description` VARCHAR( 128 ) NOT NULL ,
`incomes` DECIMAL(10,4) NOT NULL ,
`expenses` DECIMAL(10,4) NOT NULL ,
`expire_date` DATE NOT NULL ,
`payment_date` DATE NOT NULL ,
`subject_id` INT( 11 ) NOT NULL ,
`subject_name` VARCHAR( 64 ) NOT NULL ,
`res_id` INT( 11 ) NOT NULL ,
`riba_id` INT( 11 ) NOT NULL ,
`doc_type` VARCHAR( 32 ) NOT NULL ,
`doc_num` INT( 11 ) NOT NULL ,
`doc_num_ext` VARCHAR( 8 ) NOT NULL ,
`doc_name` VARCHAR( 64 ) NOT NULL ,
`doc_tag` VARCHAR( 32 ) NOT NULL ,
`doc_bank` INT( 11 ) NOT NULL ,
`payment_type` VARCHAR( 8 ) NOT NULL ,
`payment_mode` INT( 11 ) NOT NULL ,
INDEX ( `item_id` , `op_time` , `expire_date` , `payment_date` , `subject_id` , `res_id` , `riba_id` , `doc_date` , `doc_type`)
)");
 $db->Close();
 return array("message"=>"MMR extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_mmr`");
 $db->Close();

 return array("message"=>"MMR extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return $itemInfo;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'docdate' : {$docDate=$args[$c+1]; $c++;} break;
   case 'doctype' : {$docType=$args[$c+1]; $c++;} break;
   case 'docnum' : {$docNum=$args[$c+1]; $c++;} break;
   case 'docnumext' : {$docNumExt=$args[$c+1]; $c++;} break;
   case 'docname' : {$docName=$args[$c+1]; $c++;} break;
   case 'doctag' : {$docTag=$args[$c+1]; $c++;} break;
   case 'docbank' : {$docBankId=$args[$c+1]; $c++;} break;
   case 'paymenttype' : {$paymentType=$args[$c+1]; $c++;} break;
   case 'paymentmode' : {$paymentMode=$args[$c+1]; $c++;} break;
   case 'date' : case 'time' : case 'optime' : {$opTime=strtotime($args[$c+1]); $c++;} break;
   case 'desc' : case 'description' : {$description=$args[$c+1]; $c++;} break;
   case 'in' : case 'incomes' : {$incomes=$args[$c+1]; $c++;} break;
   case 'out' : case 'expenses' : {$expenses=$args[$c+1]; $c++;} break;
   case 'expire' : case 'expiredate' : {$expireDate=$args[$c+1]; $c++;} break;
   case 'payment' : case 'paymentdate' : {$paymentDate=$args[$c+1]; $c++;} break;
   case 'subjectid' : {$subjectID=$args[$c+1]; $c++;} break;
   case 'subject' : {$subjectName=$args[$c+1]; $c++;} break;
   case 'resource' : case 'resid' : case 'resourceid' : {$resourceId=$args[$c+1]; $c++;} break;
   case 'riba' : case 'ribaid' : {$ribaId=$args[$c+1]; $c++;} break;
   case 'setallpaid' : {$setAllPaid=$args[$c+1]; $c++;} ; break;
  }

 if($setAllPaid)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_mmr SET payment_date='"
	.date('Y-m-d',$paymentDate ? strtotime($paymentDate) : time())."' WHERE item_id='".$itemInfo['id']."' AND payment_date='0000-00-00'");
  $db->Close();
 }
 else if($id)
 {
  $db = new AlpaDatabase();
  $q = "";
  if($docDate)
   $q.= ",doc_date='".$docDate."'";
  if($opTime)
   $q.= ",op_time='".date('Y-m-d H:i',$opTime)."'";
  if($description)
   $q.= ",description='".$db->Purify($description)."'";
  if(isset($incomes))
   $q.= ",incomes='$incomes'";
  if(isset($expenses))
   $q.= ",expenses='$expenses'";
  if(isset($expireDate))
   $q.= ",expire_date='".($expireDate ? date('Y-m-d',strtotime($expireDate)) : "")."'";
  if(isset($paymentDate))
   $q.= ",payment_date='".($paymentDate ? date('Y-m-d',strtotime($paymentDate)) : "")."'";
  if(isset($subjectID))
   $q.= ",subject_id='$subjectID'";
  if(isset($subjectName))
   $q.= ",subject_name='".$db->Purify($subjectName)."'";
  if(isset($resourceId))
   $q.= ",res_id='".$resourceId."'";
  if(isset($ribaId))
   $q.= ",riba_id='".$ribaId."'";
  if(isset($docType))
   $q.= ",doc_type='".$docType."'";
  if(isset($docNum))
   $q.= ",doc_num='".$docNum."'";
  if(isset($docNumExt))
   $q.= ",doc_num_ext='".$docNumExt."'";
  if(isset($docName))
   $q.= ",doc_name='".$db->Purify($docName)."'";
  if(isset($docTag))
   $q.= ",doc_tag='".$docTag."'";
  if(isset($paymentType))
   $q.= ",payment_type='".$paymentType."'";
  if(isset($paymentMode))
   $q.= ",payment_mode='".$paymentMode."'";
  
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_mmr SET ".ltrim($q,",")." WHERE id='$id'");
  $db->Close();
 }
 else
 {
  if(!$docDate)
   $docDate = date("Y-m-d",$itemInfo['ctime']);
  // get other info about document //
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT code_num,code_ext,tag,payment_mode,bank_support,subject_name FROM dynarc_commercialdocs_items WHERE id='".$itemInfo['id']."'");
  $db2->Read();
  $itemInfo['code_num'] = $db2->record['code_num'];
  $itemInfo['code_ext'] = $db2->record['code_ext'];
  $itemInfo['tag'] = $db2->record['tag'];
  $itemInfo['payment_mode'] = $db2->record['payment_mode'];
  $itemInfo['bank_support'] = $db2->record['bank_support'];
  if(!$subjectName)
   $subjectName = $db2->record['subject_name'];

  // get cat tag //
  $docType = "";
  $db2->RunQuery("SELECT tag,parent_id FROM dynarc_commercialdocs_categories WHERE id='".$itemInfo['cat_id']."'");
  $db2->Read();
  if($db2->record['parent_id'])
  {
   $db2->RunQuery("SELECT tag FROM dynarc_commercialdocs_categories WHERE id='".$db2->record['parent_id']."'");
   $db2->Read();
   $docType = $db2->record['tag'];
  }
  else
   $docType = $db2->record['tag'];

  // get payment type //
  $db2->RunQuery("SELECT type FROM payment_modes WHERE id='".$itemInfo['payment_mode']."'");
  $db2->Read();
  $paymentType = $db2->record['type'];
  $db2->Close();

  $db = new AlpaDatabase();
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_mmr(item_id,doc_date,op_time,description,incomes,expenses,expire_date,payment_date,
	subject_id,subject_name,res_id,riba_id,doc_type,doc_num,doc_num_ext,doc_name,doc_tag,doc_bank,payment_type,payment_mode) VALUES('"
	.$itemInfo['id']."','".$docDate."','".date('Y-m-d H:i',$opTime ? $opTime : time())."','".$db->Purify($description)."','".$incomes."','"
	.$expenses."','".($expireDate ? date('Y-m-d',strtotime($expireDate)) : "")."','"
	.($paymentDate ? date('Y-m-d',strtotime($paymentDate)) : "")."','".$subjectID."','".$db->Purify($subjectName)."','".$resourceId."','"
	.$ribaId."','".$docType."','".$itemInfo['code_num']."','".$itemInfo['code_ext']."','".$db->Purify($itemInfo['name'])."','"
	.$itemInfo['tag']."','".$itemInfo['bank_support']."','".$paymentType."','".$itemInfo['payment_mode']."')");
  $id = $db->GetInsertId();
  $itemInfo['last_mmr_id'] = $id;
  $db->Close();
 }
 
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_mmr WHERE id='$id'");
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return $itemInfo;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_mmr WHERE item_id='".$itemInfo['id']."' ORDER BY expire_date ASC, payment_date ASC");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id']);
  $a['docdate'] = $db->record['doc_date'];
  $a['date'] = $db->record['op_time'];
  $a['description'] = $db->record['description'];
  $a['incomes'] = $db->record['incomes'];
  $a['expenses'] = $db->record['expenses'];
  $a['expire_date'] = $db->record['expire_date'];
  $a['payment_date'] = $db->record['payment_date'];
  $a['subjectid'] = $db->record['subject_id'];
  $a['subject'] = $db->record['subject_name'];
  $a['res_id'] = $db->record['res_id'];
  $a['riba_id'] = $db->record['riba_id'];
  $itemInfo['mmr'][] = $a;
 }
 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 /* REMOVE ALL MMR */
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_mmr WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_export($sessid, $shellid, $archiveInfo, $itemInfo)
{
 $xml = "";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_import($sessid, $shellid, $archiveInfo, $itemInfo, $node)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

