<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-10-2016
 #PACKAGE: rubrica
 #DESCRIPTION: Banks extension for Dynarc archives.
 #VERSION: 2.6beta
 #CHANGELOG: 24-10-2016 : MySQLi integration.
			 23-05-2016 : Bug fix intestatario su funzione set con ID=0
			 19-01-2015 : Aggiunto campo bicswift.
			 23-10-2014 : Aggiornata dimensione IBAN a 31 caratteri.
			 14-03-2013 : Completato funzioni sync import & export.
			 03-12-2012 : Completamento delle funzioni principali.
			 18-01-2012 : Completate funzioni copia ed elimina.
 #TODO: Rifare funzione import & export e completare funzioni syncimport & syncexport.
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

function dynarcextension_banks_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_banks` (
`id` INT(11) NOT NULL AUTO_INCREMENT ,
`item_id` INT(11) NOT NULL ,
`holder` VARCHAR(80) NOT NULL ,
`name` VARCHAR(80) NOT NULL ,
`abi` VARCHAR(5) NOT NULL ,
`cab` VARCHAR(5) NOT NULL ,
`cin` VARCHAR(1) NOT NULL ,
`cc` VARCHAR(12) NOT NULL ,
`iban` VARCHAR(31) NOT NULL ,
`bic_swift` VARCHAR(11) NOT NULL ,
`isdefault` TINYINT(1) NOT NULL ,
PRIMARY KEY (`id`), INDEX ( `item_id` , `isdefault` ))");
 $db->Close();
 return array("message"=>"Banks extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_banks`");
 $db->Close();

 return array("message"=>"Banks extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_set($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'holder' : {$holder=$args[$c+1]; $c++;} break;
   case 'name' : {$name=$args[$c+1]; $c++;} break;
   case 'abi' : {$abi=$args[$c+1]; $c++;} break;
   case 'cab' : {$cab=$args[$c+1]; $c++;} break;
   case 'cin' : {$cin=$args[$c+1]; $c++;} break;
   case 'cc' : {$cc=$args[$c+1]; $c++;} break;
   case 'iban' : {$iban=$args[$c+1]; $c++;} break;
   case 'bic' : case 'swift' : case 'bicswift' : {$bicSwift=$args[$c+1]; $c++;} break;
   case 'isdefault' : {$isdefault=$args[$c+1]; $c++;} break;
  }

 if($id)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_banks WHERE id='$id'");
  if(!$db->Read())
   return array('message'=>"Bank #$id does not exists!","error"=>"BANK_DOES_NOT_EXISTS");
  if($isdefault)
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_banks SET isdefault=0 WHERE item_id=".$itemInfo['id']);

  $q = "";
  if(isset($holder)) 			$q.=",holder='".$db->Purify($holder)."'";
  if(isset($name)) 				$q.=",name='".$db->Purify($name)."'";
  if(isset($abi)) 				$q.=",abi='".$abi."'";
  if(isset($cab)) 				$q.=",cab='".$cab."'";
  if(isset($cin)) 				$q.=",cin='".$cin."'";
  if(isset($cc)) 				$q.=",cc='".$cc."'";
  if(isset($bicSwift)) 			$q.=",bic_swift='".$bicSwift."'";
  if(isset($iban)) 				$q.=",iban='".$iban."'";
  if(isset($isdefault)) 		$q.=",isdefault='".$isdefault."'";

  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_banks SET ".ltrim($q,",")." WHERE id='".$id."'");
  $db->Close();
  return $itemInfo;
 }

 $db = new AlpaDatabase();
 if($isdefault)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_banks SET isdefault=0 WHERE item_id='".$itemInfo['id']."'");
 else
 {
  $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_banks WHERE item_id='".$itemInfo['id']."' AND isdefault='1'");
  if(!$db->Read())
   $isdefault = true;
 }
 $db->Close();

 if(!$name)
  $name = $itemInfo['name'];

 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_banks(item_id,holder,name,abi,cab,cin,cc,iban,bic_swift,isdefault) VALUES('"
	.$itemInfo['id']."','".$db->Purify($holder ? $holder : $itemInfo['name'])."','".$db->Purify($name)."','".$abi."','".$cab."','".$cin."','".$cc."','".$iban."','"
	.$bicSwift."','".$isdefault."')");
 $recid = $db->GetInsertId();
 $itemInfo['last_bank'] = array('id'=>$recid,'isdefault'=>$isdefault);
 $db->Close();
 return $itemInfo; 
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'all' : $all=true; break;
  }
 
 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_banks WHERE id='$id'");
 else
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_banks WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_get($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'holder' : $holder=true; break;
   case 'name' : $name=true; break;
   case 'abi' : $abi=true; break;
   case 'cab' : $cab=true; break;
   case 'cin' : $cin=true; break;
   case 'cc' : $cc=true; break;
   case 'iban' : $iban=true; break;
   case 'bic' : case 'swift' : case 'bicswift' : $bicSwift=true; break;
  }

 if(!count($args))
  $all = true;

 $itemInfo['banks'] = array();
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_banks WHERE item_id='".$itemInfo['id']."' ORDER BY isdefault DESC,id ASC");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id']);
  if($holder || $all) 	$a['holder'] = $db->record['holder'];
  if($name || $all) 	$a['name'] = $db->record['name'];
  if($abi || $all) 		$a['abi'] = $db->record['abi'];
  if($cab || $all) 		$a['cab'] = $db->record['cab'];
  if($cin || $all) 		$a['cin'] = $db->record['cin'];
  if($cc || $all) 		$a['cc'] = $db->record['cc'];
  if($iban || $all) 	$a['iban'] = $db->record['iban'];
  if($bicSwift || $all)	$a['bic_swift'] = $db->record['bic_swift'];
  $a['isdefault'] = $db->record['isdefault'];
  $itemInfo['banks'][] = $a;
 }
 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_info($params, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 /* ARRAYZE PARAMS */
 $tmp = explode("&",$params);
 $params = array();
 for($c=0; $c < count($tmp); $c++)
 {
  $x = explode("=",$tmp[$c]);
  $params[$x[0]] = $x[1];
 }
 /* EOF - ARRAYZE PARAMS */

 /* CHECK FOR ARCHIVE */
 if($params['ap'])
  $ret = GShell("dynarc archive-info -prefix '".$params['ap']."'",$sessid,$shellid);
 else if($params['aid'])
  $ret = GShell("dynarc archive-info -id '".$params['aid']."'",$sessid,$shellid);
 else
  return array("message"=>"You must specify the archive into arguments!","error"=>"INVALID_ARCHIVE");

 if($ret['error'])
  return $ret;
 $archiveInfo = $ret['outarr'];
 
 /* CHECK IF EXTENSION IS INSTALLED */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='banks' LIMIT 1");
 if(!$db->Read())
  return array("message"=>"Extension banks is not installed into archive ".$archiveInfo['name']."!\nYou can install banks extension by typing: dynarc install-extension banks -ap ".$archiveInfo['prefix']."\nRemember: Only the owner of archive (or root) can install/uninstall extensions!");
 $db->Close();

 /* CHECK FOR ITEM */
 if(!$params['id'])
  return array("message"=>"You must specify the id of contact record into params","error"=>"INVALID_ITEM_ID");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_banks WHERE id='".$params['id']."'");
 if(!$db->Read())
  return array("message"=>"Bank #".$params['id']." does not exists into archive ".$archiveInfo['prefix'],"error"=>"BANK_DOES_NOT_EXISTS");
 $a = $db->record;
 $itemId = $db->record['item_id'];
 $db->Close();

 if($itemId)
 {
  $ret = GShell("dynarc item-info -ap '".$archiveInfo['prefix']."' -id '$itemId'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  /* CHECK FOR PERMISSIONS */
  if(!$ret['outarr']['modinfo']['can_read'])
   return array("message"=>"Permission denied!, you have not permission to read this bank.","error"=>"ITEM_PERMISSION_DENIED");
 }

 $outArr = array('id'=>$a['id'],'item_id'=>$a['item_id'],'holder'=>$a['holder'],'name'=>$a['name'],'abi'=>$a['abi'],'cab'=>$a['cab'],
	'cin'=>$a['cin'],'cc'=>$a['cc'],'iban'=>$a['iban'],'bic_swift'=>$a['bic_swift'],'isdefault'=>$a['isdefault']);
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_export($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_banks WHERE item_id='".$itemInfo['id']."'");
 $db->Read();
 if(!$db->record[0])
 {
  $db->Close();
  return true;
 }
 $db->Close();

 $xml = "<banks>\n";

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_banks WHERE item_id='".$itemInfo['id']."' ORDER BY isdefault DESC,id ASC");
 while($db->Read())
 {
  $xml.= '<item holder="'.sanitize($db->record['holder']).'" name="'.sanitize($db->record['name']).'" abi="'
	.$db->record['abi'].'" cab="'.$db->record['cab'].'" cin="'.$db->record['cin'].'" cc="'
	.$db->record['cc'].'" iban="'.$db->record['iban'].'" bicswift="'.$db->record['bic_swift'].'"';
  $xml.="/>\n";
 }
 $db->Close();
 $xml.= "</banks>\n";

 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_import($sessid, $shellid, $archiveInfo, $itemInfo, $extNode, $isCategory=false)
{
 if($isCategory)
  return true;

 $list = $extNode->GetElementsByTagName('item');
 $fields = array('holder','name','abi','cab','cin','cc','iban','bicswift');
 for($c=0; $c < count($list); $c++)
 {
  $n = $list[$c];
  $extQ = "";
  for($i=0; $i < count($fields); $i++)
   $extQ.= ",".$fields[$i]."=\"".$n->getString($fields[$i])."\"";
  GShell("dynarc edit-item -ap `".$archiveInfo['prefix']."` -id `".$itemInfo['id']."` -extset `banks.".ltrim($extQ,",")."`",$sessid, $shellid);
 }
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_banks WHERE item_id='".$itemInfo['id']."'");
 $db->Close();
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_banks WHERE item_id='".$srcInfo['id']."' ORDER BY id ASC");
 while($db->Read())
 {
  $db2->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_banks(item_id,holder,name,abi,cab,cin,cc,iban,bic_swift,isdefault) VALUES('"
	.$cloneInfo['id']."','".$db2->Purify($db->record['holder'])."','".$db2->Purify($db->record['name'])."','".$db->record['abi']."','"
	.$db->record['cab']."','".$db->record['cin']."','".$db->record['cc']."','".$db->record['iban']."','".$db->record['bic_swift']."','".$db->record['isdefault']."')");
 }
 $db->Close();
 $db2->Close();
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 if($isCategory)
  return;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_banks WHERE item_id='".$itemInfo['id']."'");
 $db->Read();
 if(!$db->record[0])
 {
  $db->Close();
  return true;
 }
 $db->Close();

 $xml = "<banks>\n";

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_banks WHERE item_id='".$itemInfo['id']."' ORDER BY isdefault DESC,id ASC");
 while($db->Read())
 {
  $xml.= '<item holder="'.sanitize($db->record['holder']).'" name="'.sanitize($db->record['name']).'" abi="'
	.$db->record['abi'].'" cab="'.$db->record['cab'].'" cin="'.$db->record['cin'].'" cc="'
	.$db->record['cc'].'" iban="'.$db->record['iban'].'" bicswift="'.$db->record['bic_swift'].'"';
  $xml.="/>\n";
 }
 $db->Close();
 $xml.= "</banks>\n";

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_banks_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $extNode, $isCategory=false)
{
 global $_USERS_HOMES;

 if($isCategory)
  return true;

 $list = $extNode->GetElementsByTagName('item');
 $fields = array('holder','name','abi','cab','cin','cc','iban','bicswift');
 for($c=0; $c < count($list); $c++)
 {
  $n = $list[$c];
  $extQ = "";
  for($i=0; $i < count($fields); $i++)
   $extQ.= ",".$fields[$i]."=\"".$n->getString($fields[$i])."\"";
  GShell("dynarc edit-item -ap `".$archiveInfo['prefix']."` -id `".$itemInfo['id']."` -extset `banks.".ltrim($extQ,",")."`",$sessid, $shellid);
 }
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

