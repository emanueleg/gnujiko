<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-10-2016
 #PACKAGE: rubrica
 #DESCRIPTION: Contacts extension for Dynarc archives.
 #VERSION: 2.9beta
 #CHANGELOG: 24-10-2016 : MySQLi integration.
			 23-10-2014 : Aggiornata dimensione CAP a 8 caratteri.
			 03-07-2014 : Aggiunto campo codice.
			 01-05-2014 : Bug fix su dynarcextension_contacts_import
			 14-03-2013 : Completato funzioni sync import & export.
			 04-02-2013 : Bug fix with special chars.
			 03-12-2012 : Completamento delle funzioni principali.
			 27-06-2012 : Portato country code a larghezza 3 caratteri.
			 18-01-2012 : Completate funzioni copia ed elimina.
 #TODO: Rifare funzione import & export e completare funzioni syncimport & syncexport.
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

function dynarcextension_contacts_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_contacts` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`item_id` INT( 11 ) NOT NULL ,
`label` VARCHAR( 40 ) NOT NULL ,
`name` VARCHAR( 80 ) NOT NULL ,
`code` VARCHAR( 32 ) NOT NULL ,
`address` VARCHAR( 80 ) NOT NULL ,
`city` VARCHAR( 40 ) NOT NULL ,
`zipcode` VARCHAR( 8 ) NOT NULL ,
`province` VARCHAR( 2 ) NOT NULL ,
`countrycode` VARCHAR( 3 ) NOT NULL ,
`latitude` FLOAT( 10, 6 ) NOT NULL ,
`longitude` FLOAT( 10, 6 ) NOT NULL ,
`phone` VARCHAR( 14 ) NOT NULL ,
`phone2` VARCHAR( 14 ) NOT NULL ,
`fax` VARCHAR( 14 ) NOT NULL ,
`cell` VARCHAR( 14 ) NOT NULL ,
`email` VARCHAR( 40 ) NOT NULL ,
`email2` VARCHAR( 40 ) NOT NULL ,
`email3` VARCHAR( 40 ) NOT NULL ,
`skype` VARCHAR( 40 ) NOT NULL ,
`isdefault` TINYINT( 1 ) NOT NULL ,
PRIMARY KEY (`id`), INDEX ( `item_id` , `isdefault` ))");
 $db->Close();
 return array("message"=>"Contacts extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_contacts`");
 $db->Close();

 return array("message"=>"Contacts extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_set($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'label' : {$label=$args[$c+1]; $c++;} break;
   case 'name' : {$name=$args[$c+1]; $c++;} break;
   case 'code' : {$code=$args[$c+1]; $c++;} break;
   case 'address' : {$address=$args[$c+1]; $c++;} break;
   case 'city' : {$city=$args[$c+1]; $c++;} break;
   case 'zipcode' : {$zipcode=$args[$c+1]; $c++;} break;
   case 'province' : {$province=$args[$c+1]; $c++;} break;
   case 'countrycode' : {$countrycode=$args[$c+1]; $c++;} break;
   case 'latitude' : {$latitude=$args[$c+1]; $c++;} break; 
   case 'longitude' : {$longitude=$args[$c+1]; $c++;} break; 
   case 'phone' : {$phone=$args[$c+1]; $c++;} break; 
   case 'phone2' : {$phone2=$args[$c+1]; $c++;} break;
   case 'fax' : {$fax=$args[$c+1]; $c++;} break;
   case 'cell' : {$cell=$args[$c+1]; $c++;} break;
   case 'email' : {$email=$args[$c+1]; $c++;} break;
   case 'email2' : {$email2=$args[$c+1]; $c++;} break;
   case 'email3' : {$email3=$args[$c+1]; $c++;} break;
   case 'skype' : {$skype=$args[$c+1]; $c++;} break;
   case 'isdefault' : {$isdefault=$args[$c+1]; $c++;} break;
  }

 if($id)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_contacts WHERE id='$id'");
  if(!$db->Read())
   return array('message'=>"Contact #$id does not exists!","error"=>"CONTACTS_DOES_NOT_EXISTS");
  if($isdefault)
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_contacts SET isdefault=0 WHERE item_id=".$itemInfo['id']);

  $q = "";
  if($label) $q.=",label='".$db->Purify($label)."'";
  if($name) $q.=",name='".$db->Purify($name)."'";
  if(isset($code)) $q.=",code='".$db->Purify($code)."'";
  if($address) $q.=",address='".$db->Purify($address)."'";
  if($city) $q.= ",city='".$db->Purify($city)."'";
  if($zipcode) $q.=",zipcode='$zipcode'";
  if($province) $q.=",province='$province'";
  if($countrycode) $q.=",countrycode='$countrycode'";
  if($latitude) $q.=",latitude='$latitude'";
  if($longitude) $q.=",longitude='$longitude'";
  if(isset($phone)) $q.=",phone='$phone'";
  if(isset($phone2)) $q.=",phone2='$phone2'";
  if(isset($fax)) $q.=",fax='$fax'";
  if(isset($cell)) $q.=",cell='$cell'";
  if(isset($email)) $q.=",email='$email'";
  if(isset($email2)) $q.=",email2='$email2'";
  if(isset($email3)) $q.=",email3='$email3'";
  if(isset($skype)) $q.=",skype='$skype'";
  if(isset($isdefault)) $q.=",isdefault='$isdefault'";

  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_contacts SET ".ltrim($q,",")." WHERE id='$id'");
  $db->Close();
  return $itemInfo;
 }

 $db = new AlpaDatabase();
 if($isdefault)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_contacts SET isdefault=0 WHERE item_id='".$itemInfo['id']."'");
 else
 {
  $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_contacts WHERE item_id='".$itemInfo['id']."' AND isdefault='1'");
  if(!$db->Read())
   $isdefault = true;
 }
 $db->Close();

 if(!$name)
  $name = $itemInfo['name'];

 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_contacts(item_id,label,name,code,address,city,zipcode,province,countrycode,latitude,longitude,phone,phone2,fax,cell,email,email2,email3,skype,isdefault) VALUES('"
	.$itemInfo['id']."','".$db->Purify($label)."','".$db->Purify($name)."','".$db->Purify($code)."','".$db->Purify($address)."','"
	.$db->Purify($city)."','".$zipcode."','".$province."','".$countrycode."','".$latitude."','".$longitude."','"
	.$phone."','".$phone2."','".$fax."','".$cell."','".$email."','".$email2."','".$email3."','".$skype."','".$isdefault."')");
 $recid = $db->GetInsertId();
 $itemInfo['last_contact'] = array('id'=>$recid,'isdefault'=>$isdefault);
 $db->Close();
 return $itemInfo; 
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'all' : $all=true; break;
  }
 
 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_contacts WHERE id='$id'");
 else
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_contacts WHERE item_id='".$itemInfo['id']."'");
 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_get($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'label' : $label=true; break;
   case 'name' : $name=true; break;
   case 'code' : $code=true; break;
   case 'address' : $address=true; break;
   case 'city' : $city=true; break;
   case 'zipcode' : $zipcode=true; break;
   case 'province' : $province=true; break;
   case 'countrycode' : $countrycode=true; break;
   case 'latitude' : $latitude=true; break;
   case 'longitude' : $longitude=true; break;
   case 'phone' : $label=true; break;
   case 'phone2' : $phone2=true; break;
   case 'fax' : $fax=true; break;
   case 'cell' : $cell=true; break;
   case 'email' : $label=true; break;
   case 'email2' : $email2=true; break;
   case 'email3' : $email3=true; break;
   case 'skype' : $skype=true; break;
  }

 if(!count($args))
  $all = true;

 $itemInfo['contacts'] = array();
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_contacts WHERE item_id='".$itemInfo['id']."' ORDER BY isdefault DESC,id ASC");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id']);
  if($label || $all) $a['label'] = $db->record['label'];
  if($name || $all) $a['name'] = $db->record['name'];
  if($code || $all) $a['code'] = $db->record['code'];
  if($address || $all) $a['address'] = $db->record['address'];
  if($city || $all) $a['city'] = $db->record['city'];
  if($zipcode || $all) $a['zipcode'] = $db->record['zipcode'];
  if($province || $all) $a['province'] = $db->record['province'];
  if($countrycode || $all) $a['countrycode'] = $db->record['countrycode'];
  if($latitude || $all) $a['latitude'] = $db->record['latitude'];
  if($longitude || $all) $a['longitude'] = $db->record['longitude'];
  if($phone || $all) $a['phone'] = $db->record['phone'];
  if($phone2 || $all) $a['phone2'] = $db->record['phone2'];
  if($fax || $all) $a['fax'] = $db->record['fax'];
  if($cell || $all) $a['cell'] = $db->record['cell'];
  if($email || $all) $a['email'] = $db->record['email'];
  if($email2 || $all) $a['email2'] = $db->record['email2'];
  if($email3 || $all) $a['email3'] = $db->record['email3'];
  if($skype || $all) $a['skype'] = $db->record['skype'];
  $a['isdefault'] = $db->record['isdefault'];
  $itemInfo['contacts'][] = $a;
 }
 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_info($params, $sessid, $shellid)
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
 $db->RunQuery("SELECT id FROM dynarc_archive_extensions WHERE archive_id='".$archiveInfo['id']."' AND extension_name='contacts' LIMIT 1");
 if(!$db->Read())
  return array("message"=>"Extension Contacts is not installed into archive ".$archiveInfo['name']."!\nYou can install Contacts extension by typing: dynarc install-extension contacts -ap ".$archiveInfo['prefix']."\nRemember: Only the owner of archive (or root) can install/uninstall extensions!");
 $db->Close();

 /* CHECK FOR ITEM */
 if(!$params['id'])
  return array("message"=>"You must specify the id of contact record into params","error"=>"INVALID_ITEM_ID");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_contacts WHERE id='".$params['id']."'");
 if(!$db->Read())
  return array("message"=>"Contact #".$params['id']." does not exists into archive ".$archiveInfo['prefix'],"error"=>"CONTACT_DOES_NOT_EXISTS");
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
   return array("message"=>"Permission denied!, you have not permission to read this contact.","error"=>"ITEM_PERMISSION_DENIED");
 }

 $outArr = array('id'=>$a['id'],'item_id'=>$a['item_id'],'label'=>$a['label'],'name'=>$a['name'],'code'=>$a['code'],'address'=>$a['address'],
	'city'=>$a['city'],'zipcode'=>$a['zipcode'],'province'=>$a['province'],'countrycode'=>$a['countrycode'],'latitude'=>$a['latitude'],
	'longitude'=>$a['longitude'],'phone'=>$a['phone'],'phone2'=>$a['phone2'],'fax'=>$a['fax'],'cell'=>$a['cell'],'email'=>$a['email'],
	'email2'=>$a['email2'],'email3'=>$a['email3'],'skype'=>$a['skype'],'isdefault'=>$a['isdefault']);
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_export($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_contacts WHERE item_id='".$itemInfo['id']."'");
 $db->Read();
 if(!$db->record[0])
 {
  $db->Close();
  return true;
 }
 $db->Close();

 $xml = "<contacts>\n";

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_contacts WHERE item_id='".$itemInfo['id']."' ORDER BY isdefault DESC,id ASC");
 while($db->Read())
 {
  $xml.= '<item label="'.sanitize($db->record['label']).'" name="'.sanitize($db->record['name']).'" code="'.sanitize($db->record['code'])
	.'" address="'.sanitize($db->record['address']).'" city="'.sanitize($db->record['city']).'" zipcode="'.$db->record['zipcode'].'" province="'
	.$db->record['province'].'" countrycode="'.$db->record['countrycode'].'" phone="'.$db->record['phone'].'"';

  if($db->record['phone2']) $xml.= ' phone2="'.$db->record['phone2'].'"';
  if($db->record['fax']) $xml.= ' fax="'.$db->record['fax'].'"';
  if($db->record['cell']) $xml.= ' cell="'.$db->record['cell'].'"';
  if($db->record['email']) $xml.= ' email="'.$db->record['email'].'"';
  if($db->record['email2']) $xml.= ' email2="'.$db->record['email2'].'"';
  if($db->record['email3']) $xml.= ' email3="'.$db->record['email3'].'"';
  if($db->record['skype']) $xml.= ' skype="'.$db->record['skype'].'"';

  $xml.="/>\n";
 }
 $db->Close();
 $xml.= "</contacts>\n";

 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_import($sessid, $shellid, $archiveInfo, $itemInfo, $node, $isCategory=false)
{
 if($isCategory)
  return true;

 if(!$node)
  return;

 $list = $node->GetElementsByTagName('item');
 $fields = array('label','name','code','address','city','zipcode','prov','countrycode','phone','phone2','fax','cell','email','email2','email3','skype');
 for($c=0; $c < count($list); $c++)
 {
  $n = $list[$c];
  $extQ = "";
  for($i=0; $i < count($fields); $i++)
   $extQ.= ",".$fields[$i]."=\"".$n->getString($fields[$i])."\"";
  GShell("dynarc edit-item -ap `".$archiveInfo['prefix']."` -id `".$itemInfo['id']."` -extset `contacts.".ltrim($extQ,",")."`",$sessid, $shellid);
 }
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_contacts WHERE item_id='".$itemInfo['id']."'");
 $db->Close();
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_contacts WHERE item_id='".$srcInfo['id']."' ORDER BY id ASC");
 while($db->Read())
 {
  $db2->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_contacts(item_id,label,name,code,address,city,zipcode,province,countrycode,latitude,longitude,phone,phone2,fax,cell,email,email2,email3,skype,isdefault) VALUES('"
	.$cloneInfo['id']."','".$db2->Purify($db->record['label'])."','".$db2->Purify($db->record['name'])."','"
	.$db2->Purify($db->record['code'])."','".$db2->Purify($db->record['address'])."','"
	.$db2->Purify($db->record['city'])."','".$db->record['zipcode']."','".$db->record['province']."','".$db->record['countrycode']."','"
	.$db->record['latitude']."','".$db->record['longitude']."','".$db->record['phone']."','".$db->record['phone2']."','"
	.$db->record['fax']."','".$db->record['cell']."','".$db->record['email']."','".$db->record['email2']."','"
	.$db->record['email3']."','".$db->record['skype']."','".$db->record['isdefault']."')");
 }
 $db->Close();
 $db2->Close();
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 if($isCategory)
  return;

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_contacts WHERE item_id='".$itemInfo['id']."'");
 $db->Read();
 if(!$db->record[0])
 {
  $db->Close();
  return true;
 }
 $db->Close();

 $xml = "<contacts>\n";

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_contacts WHERE item_id='".$itemInfo['id']."' ORDER BY isdefault DESC,id ASC");
 while($db->Read())
 {
  $xml.= '<item label="'.sanitize($db->record['label']).'" name="'.sanitize($db->record['name']).'" code="'.sanitize($db->record['code']).'" address="'
	.sanitize($db->record['address']).'" city="'.sanitize($db->record['city']).'" zipcode="'.$db->record['zipcode'].'" province="'
	.$db->record['province'].'" countrycode="'.$db->record['countrycode'].'" phone="'.$db->record['phone'].'"';

  if($db->record['phone2']) $xml.= ' phone2="'.$db->record['phone2'].'"';
  if($db->record['fax']) $xml.= ' fax="'.$db->record['fax'].'"';
  if($db->record['cell']) $xml.= ' cell="'.$db->record['cell'].'"';
  if($db->record['email']) $xml.= ' email="'.$db->record['email'].'"';
  if($db->record['email2']) $xml.= ' email2="'.$db->record['email2'].'"';
  if($db->record['email3']) $xml.= ' email3="'.$db->record['email3'].'"';
  if($db->record['skype']) $xml.= ' skype="'.$db->record['skype'].'"';

  $xml.="/>\n";
 }
 $db->Close();
 $xml.= "</contacts>\n";


 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_contacts_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $extNode, $isCategory=false)
{
 global $_USERS_HOMES;

 if($isCategory)
  return true;

 $list = $node->GetElementsByTagName('item');
 $fields = array('label','name','code','address','city','zipcode','prov','countrycode','phone','phone2','fax','cell','email','email2','email3','skype');
 for($c=0; $c < count($list); $c++)
 {
  $n = $list[$c];
  $extQ = "";
  for($i=0; $i < count($fields); $i++)
   $extQ.= ",".$fields[$i]."=\"".$n->getString($fields[$i])."\"";
  GShell("dynarc edit-item -ap `".$archiveInfo['prefix']."` -id `".$itemInfo['id']."` -extset `contacts.".ltrim($extQ,",")."`",$sessid, $shellid);
 }

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//


