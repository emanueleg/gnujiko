<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 03-12-2012
 #PACKAGE: dynarc-mmr-extension
 #DESCRIPTION: Money Movements Reports, extension for Dynarc,
 #VERSION: 2.0beta
 #CHANGELOG: 03-12-2012 : Completamento delle funzioni principali.
 #TODO:Rifare funzione import & export e completare funzioni syncimport & syncexport.
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_mmr_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_mmr` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`item_id` INT( 11 ) NOT NULL ,
`op_time` DATETIME NOT NULL ,
`description` VARCHAR( 128 ) NOT NULL ,
`incomes` FLOAT NOT NULL ,
`expenses` FLOAT NOT NULL ,
`expire_date` DATE NOT NULL ,
`payment_date` DATE NOT NULL ,
`subject_id` INT( 11 ) NOT NULL ,
`subject_name` VARCHAR( 64 ) NOT NULL ,
`res_id` INT( 11 ) NOT NULL ,
INDEX ( `item_id` , `op_time` , `expire_date` , `payment_date` , `subject_id` , `res_id` )
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
   case 'date' : case 'time' : case 'optime' : {$opTime=strtotime($args[$c+1]); $c++;} break;
   case 'desc' : case 'description' : {$description=$args[$c+1]; $c++;} break;
   case 'in' : case 'incomes' : {$incomes=$args[$c+1]; $c++;} break;
   case 'out' : case 'expenses' : {$expenses=$args[$c+1]; $c++;} break;
   case 'expire' : case 'expiredate' : {$expireDate=$args[$c+1]; $c++;} break;
   case 'payment' : case 'paymentdate' : {$paymentDate=$args[$c+1]; $c++;} break;
   case 'subjectid' : {$subjectID=$args[$c+1]; $c++;} break;
   case 'subject' : {$subjectName=$args[$c+1]; $c++;} break;
   case 'resource' : case 'resid' : case 'resourceid' : {$resourceId=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 if($id)
 {
  $q = "";
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
  
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_mmr SET ".ltrim($q,",")." WHERE id='$id'");
  $db->Close();
 }
 else
 {
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_mmr(item_id,op_time,description,incomes,expenses,expire_date,payment_date,
	subject_id,subject_name,res_id) VALUES('".$itemInfo['id']."','".date('Y-m-d H:i',$opTime ? $opTime : time())."','".$db->Purify($description)."','"
	.$incomes."','".$expenses."','".($expireDate ? date('Y-m-d',strtotime($expireDate)) : "")."','"
	.($paymentDate ? date('Y-m-d',strtotime($paymentDate)) : "")."','".$subjectID."','".$db->Purify($subjectName)."','".$resourceId."')");
  $id = mysql_insert_id();
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
  $a['date'] = $db->record['op_time'];
  $a['description'] = $db->record['description'];
  $a['incomes'] = $db->record['incomes'];
  $a['expenses'] = $db->record['expenses'];
  $a['expire_date'] = $db->record['expire_date'];
  $a['payment_date'] = $db->record['payment_date'];
  $a['subjectid'] = $db->record['subject_id'];
  $a['subject'] = $db->record['subject_name'];
  $a['res_id'] = $db->record['res_id'];
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

