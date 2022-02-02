<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 12-04-2013
 #PACKAGE: dynarc-custompricing-extension
 #DESCRIPTION: Custom pricing extension for Dynarc archives.
 #VERSION: 2.2beta
 #CHANGELOG: 12-04-2013 : Aggiunte le 3 colonne degli sconti.
			 04-02-2013 : Bug fix in function unset.
 #TODO:
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_custompricing` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`item_id` INT( 11 ) NOT NULL ,
`subject` VARCHAR( 40 ) NOT NULL ,
`subject_id` INT( 11 ) NOT NULL ,
`baseprice` FLOAT NOT NULL ,
`discount_perc` FLOAT NOT NULL ,
`discount_inc` FLOAT NOT NULL ,
`discount2` FLOAT NOT NULL,
`discount3` FLOAT NOT NULL,
INDEX ( `item_id` , `subject_id` )
)");

 return array("message"=>"CustomPricing extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_custompricing`");
 $db->Close();

 return array("message"=>"CustomPricing extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_catset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_custompricing_catset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'baseprice' : {$basePrice=$args[$c+1]; $c++;} break;
   case 'subject' : case 'subjectname' : case 'customer' : {$subjectName=$args[$c+1]; $c++;} break;
   case 'subjectid' : case 'customerid' : {$subjectId=$args[$c+1]; $c++;} break;
   case 'baseprice' : case 'price' : {$basePrice=$args[$c+1]; $c++;} break;
   case 'discount' : case 'disc' : {$discount=$args[$c+1]; $c++;} break;
   case 'discount2' : case 'disc2' : {$discount2=$args[$c+1]; $c++;} break;
   case 'discount3' : case 'disc3' : {$discount3=$args[$c+1]; $c++;} break;
  }

 if(!$id)
 {
  // get if already exists //
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_custompricing WHERE ".($subjectId ? "subject_id='".$subjectId."'" : "subject='".$db->Purify($subjectName)."'")." AND item_id='".$itemInfo['id']."' LIMIT 1");
  if($db->Read())
   $id = $db->record['id'];
  $db->Close();
 }

 if(isset($discount))
 {
  if(!$discount)
  {
   $discountPerc = 0;
   $discountInc = 0;
  }
  else if(strpos($discount,"%") !== false)
  {
   $discountPerc = str_replace("%","",$discount);
   $discountInc = 0;
  }
  else
  {
   $discountPerc = 0;
   $discountInc = $discount;
  }
 }

 if($id)
 {
  $db = new AlpaDatabase();
  $q = "";
  if(isset($basePrice))
   $q.= ",baseprice='".$basePrice."'";
  if(isset($subjectName))
   $q.= ",subject='".$db->Purify($subjectName)."'";
  if(isset($subjectId))
   $q.= ",subject_id='".$subjectId."'";
  if(isset($discountPerc))
   $q.= ",discount_perc='".$discountPerc."'";
  if(isset($discountInc))
   $q.= ",discount_inc='".$discountInc."'";
  if(isset($discount2))
   $q.= ",discount2='".$discount2."'";
  if(isset($discount3))
   $q.= ",discount3='".$discount3."'";
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_custompricing SET ".ltrim($q,",")." WHERE id='".$id."'");
  $db->Close();
 }
 else
 {
  if($subjectId && !$subjectName)
  {
   $ret = GShell("dynarc item-info -ap rubrica -id `".$subjectId."`",$sessid,$shellid);
   if(!$ret['error'])
	$subjectName = $ret['outarr']['name'];
  }

  $db = new AlpaDatabase();
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_custompricing(item_id,subject,subject_id,baseprice,discount_perc,discount_inc,discount2,discount3) VALUES('"
	.$itemInfo['id']."','".$db->Purify($subjectName)."','".$subjectId."','".$basePrice."','".$discountPerc."','".$discountInc."','"
	.$discount2."','".$discount3."')");
  $id = mysql_insert_id();
  $db->Close();
  $itemInfo['last_inserted'] = array('id'=>$id, 'subject_name'=>$subjectName, 'subject_id'=>$subjectId, 'baseprice'=>$basePrice, 'discount_perc'=>$discountPerc, 'discount_inc'=>$discountInc, 'discount2'=>$discount2, 'discount3'=>$discount3);
 }
 
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_custompricing WHERE id='".$id."' AND item_id='".$itemInfo['id']."'");
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_catget($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_custompricing_catget($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'subject' : $subjectName=true; break;
   case 'baseprice' : $basePrice=true; break;
   case 'discount' : $discount=true; break;
 
   default : {
	 if(substr($args[$c],0,10) == "subjectid=")
	  $subjectId = substr($args[$c],10);
	} break;
  }

 if($subjectId)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id,baseprice,discount_perc,discount_inc,discount2,discount3 FROM dynarc_".$archiveInfo['prefix']."_custompricing WHERE item_id='"
	.$itemInfo['id']."' AND subject_id='".$subjectId."' LIMIT 1");
  if($db->Read())
   $itemInfo['custompricing'] = array('id'=>$db->record['id'], 'baseprice'=>$db->record['baseprice'], 'discount_perc'=>$db->record['discount_perc'], 'discount_inc'=>$db->record['discount_inc'], 'discount2'=>$db->record['discount2'], 'discount3'=>$db->record['discount3']);
  $db->Close();
  return $itemInfo;
 }

 if(!$subjectName && !$basePrice && !$discountPerc)
  $all=true;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_custompricing WHERE item_id='".$itemInfo['id']."'");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id'],'subject_id'=>$db->record['subject_id']);

  if($subjectName || $all) $a['subject_name'] = $db->record['subject'];
  if($basePrice || $all) $a['baseprice'] = $db->record['baseprice'];
  if($discount || $all) 
  {
   $a['discount_perc'] = $db->record['discount_perc'];
   $a['discount_inc'] = $db->record['discount_inc'];
   $a['discount2'] = $db->record['discount2'];
   $a['discount3'] = $db->record['discount3'];
  }

  $itemInfo['custompricing'][] = $a;
 }
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{


 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_export($sessid, $shellid, $archiveInfo, $itemInfo)
{
 $xml = "";
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_import($sessid, $shellid, $archiveInfo, $itemInfo, $node)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_custompricing_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

