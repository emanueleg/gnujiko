<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-02-2013
 #PACKAGE: dynarc-variants-extension
 #DESCRIPTION: Variants extension for Dynarc archives.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO: Manca da fare l'importazione,l'esportazione ed il sync.
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

function dynarcextension_variants_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_variants` (
	`item_id` INT( 11 ) NOT NULL , 
	`var_col` TEXT NOT NULL , 
	`var_tint` TEXT NOT NULL , 
	`var_siz` TEXT NOT NULL ,
	`siz_id` INT( 11 ) NOT NULL , 
	`var_dim` TEXT NOT NULL , 
	`dim_id` INT( 11 ) NOT NULL , 
	`var_other` TEXT NOT NULL , 
	PRIMARY KEY ( `item_id` )
)");
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `variant_sizes` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 32 ) NOT NULL ,
`xml_params` TEXT NOT NULL ,
INDEX ( `name` )
)");
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `variant_dim` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 32 ) NOT NULL ,
`xml_params` TEXT NOT NULL ,
INDEX ( `name` )
)");
 $db->Close();


 $db = new AlpaDatabase(); $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_items WHERE 1 ORDER BY id ASC");
 while($db->Read())
 {
  $db2->RunQuery("SELECT item_id FROM dynarc_".$archiveInfo['prefix']."_variants WHERE item_id='".$db->record['id']."'");
  if(!$db2->Read())
   $db2->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_variants(item_id) VALUES('".$db->record['id']."')");
 }
 $db2->Close();
 $db->Close();

 return array("message"=>"Variants extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_variants`");
 $db->Close();

 return array("message"=>"Variants extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_set($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'col' : case 'colors' : {$colors=$args[$c+1]; $c++;} break;
   case 'tint' : case 'tints' : {$tint=$args[$c+1]; $c++;} break;
   case 'siz' : case 'sizes' : case 'size' : {$sizes=$args[$c+1]; $c++;} break;
   case 'sizid' : {$sizId=$args[$c+1]; $c++;} break;
   case 'dim' : case 'dimensions' : {$dim=$args[$c+1]; $c++;} break;
   case 'dimid' : {$dimId=$args[$c+1]; $c++;} break;
   case 'other' : {$other=$args[$c+1]; $c++;} break;
  }


 $db = new AlpaDatabase();
 $q = "";
 if($colors) $q.=",var_col='".$db->Purify($colors)."'";
 if($tint) $q.=",var_tint='".$db->Purify($tint)."'";
 if($sizes) $q.=",var_siz='".$db->Purify($sizes)."'";
 if(isset($sizId)) $q.= ",siz_id='".$sizId."'";
 if($dim) $q.=",var_dim='".$db->Purify($dim)."'";
 if(isset($dimId)) $q.= ",dim_id='".$dimId."'";
 if($other) $q.=",var_other='".$db->Purify($other)."'";
 if($q)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_variants SET ".ltrim($q,",")." WHERE item_id='".$itemInfo['id']."'");
 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_get($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'col' : case 'colors' : $colors=true; break;
   case 'tint' : case 'tints' : $tint=true; break;
   case 'siz' : case 'size' : case 'sizes' : $sizes=true; break;
   case 'dim' : case 'dimensions' : $dim=true; break;
   case 'other' : $other=true; break;
  }

 if(!count($args))
  $all = true;

 $itemInfo['variants'] = array();
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_variants WHERE item_id='".$itemInfo['id']."'");
 $db->Read();
 
 if($colors || $all)
 {
  $xmlContents = ltrim(rtrim($db->record['var_col']));
  if($xmlContents != "<xml></xml>")
  {
   $xml = new GXML();
   if($xml->LoadFromString($xmlContents))
   {
	$tmp = $xml->toArray();
	$itemInfo['variants']['colors'] = (count($tmp) == 1) ? array(0=>$tmp['color']) : $tmp;
   }
  }
 }

 if($tint || $all)
 {
  $xmlContents = ltrim(rtrim($db->record['var_tint']));
  if($xmlContents != "<xml></xml>")
  {
   $xml = new GXML();
   if($xml->LoadFromString($xmlContents))
   {
	$tmp = $xml->toArray();
	$itemInfo['variants']['tint'] = (count($tmp) == 1) ? array(0=>$tmp['tint']) : $tmp;
   }
  }
 }

 if($sizes || $all)
 {
  $itemInfo['variants']['sizid'] = $db->record['siz_id'];
  $xmlContents = ltrim(rtrim($db->record['var_siz']));
  if($xmlContents != "<xml></xml>")
  {
   $xml = new GXML();
   if($xml->LoadFromString($xmlContents))
   {
	$tmp = $xml->toArray();
	$itemInfo['variants']['sizes'] = (count($tmp) == 1) ? array(0=>$tmp['size']) : $tmp;
   }
  }
 }

 if($dim || $all)
 {
  $itemInfo['variants']['dimid'] = $db->record['dim_id'];
  $xmlContents = ltrim(rtrim($db->record['var_dim']));
  if($xmlContents != "<xml></xml>")
  {
   $xml = new GXML();
   if($xml->LoadFromString($xmlContents))
   {
	$tmp = $xml->toArray();
	$itemInfo['variants']['dim'] = (count($tmp) == 1) ? array(0=>$tmp['dim']) : $tmp;
   }
  }
 }

 if($other || $all)
 {
  $xmlContents = ltrim(rtrim($db->record['var_other']));
  if($xmlContents != "<xml></xml>")
  {
   $xml = new GXML();
   if($xml->LoadFromString($xmlContents))
   {
	$tmp = $xml->toArray();
	$itemInfo['variants']['other'] = (count($tmp) == 1) ? array(0=>$tmp['variant']) : $tmp;
   }
  }
 }

 $db->Close();
 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_export($sessid, $shellid, $archiveInfo, $itemInfo)
{
 /*$db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$archiveInfo['prefix']."_variants WHERE item_id='".$itemInfo['id']."'");
 $db->Read();
 if(!$db->record[0])
 {
  $db->Close();
  return true;
 }
 $db->Close();

 $xml = "<variants>\n";

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_variants WHERE item_id='".$itemInfo['id']."' ORDER BY isdefault DESC,id ASC");
 while($db->Read())
 {
  $xml.= '<item holder="'.sanitize($db->record['holder']).'" name="'.sanitize($db->record['name']).'" abi="'
	.$db->record['abi'].'" cab="'.$db->record['cab'].'" cin="'.$db->record['cin'].'" cc="'
	.$db->record['cc'].'" iban="'.$db->record['iban'].'"';
  $xml.="/>\n";
 }
 $db->Close();
 $xml.= "</variants>\n";*/

 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_import($sessid, $shellid, $archiveInfo, $itemInfo, $node)
{
 /*$list = $node->GetElementsByTagName('item');
 $fields = array('holder','name','abi','cab','cin','cc','iban');
 for($c=0; $c < count($list); $c++)
 {
  $n = $list[$c];
  $extQ = "";
  for($i=0; $i < count($fields); $i++)
   $extQ.= ",".$fields[$i]."=\"".$n->getString($fields[$i])."\"";
  GShell("dynarc edit-item -ap `".$archiveInfo['prefix']."` -id `".$itemInfo['id']."` -extset `variants.".ltrim($extQ,",")."`",$sessid, $shellid);
 }*/
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_variants WHERE item_id='".$itemInfo['id']."'");
 $db->Close();
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_variants WHERE item_id='".$srcInfo['id']."'");
 $db->Read();
 $db2->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_variants(item_id,var_col,var_tint,var_siz,var_dim,var_other) VALUES('"
	.$cloneInfo['id']."','".$db2->Purify($db->record['var_col'])."','".$db2->Purify($db->record['var_tint'])."','".$db2->Purify($db->record['var_siz'])."','".$db2->Purify($db->record['var_dim'])."','".$db2->Purify($db->record['var_other'])."')");
 $db2->Close();
 $db->Close();
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_variants(item_id) VALUES('".$itemInfo['id']."')");
 $db->Close();
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//

//									F U N C T I O N S																 

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_addSizeType($params, $sessid, $shellid, $extraParams=null)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

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

 $name = $params['name'];
 $xml = $extraParams['xml'];

 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO variant_sizes(name,xml_params) VALUES('".$db->Purify($name)."','".$db->Purify($xml)."')");
 $id = mysql_insert_id();
 $db->Close();

 $out.= "done! ID=".$id;
 $outArr = array('id'=>$id,'name'=>$name);

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_editSizeType($params, $sessid, $shellid, $extraParams=null)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

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

 if(!$params['id'])
  return array('message'=>"You must specify size type id.", 'error'=>"INVALID_SIZE_TYPE_ID");

 $db = new AlpaDatabase();
 $q = "";
 if($params['name'])
  $q.= ",name='".$db->Purify($params['name'])."'";
 if($extraParams['xml'])
  $q.= ",xml_params='".$db->Purify($extraParams['xml'])."'";
 if($q)
  $db->RunQuery("UPDATE variant_sizes SET ".ltrim($q,",")." WHERE id='".$params['id']."'");
 $db->Close();
 
 $out.= "done!";
 $outArr = array('id'=>$id,'name'=>$name);

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_deleteSizeType($params, $sessid, $shellid, $extraParams=null)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

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

 if(!$params['id'])
  return array('message'=>"You must specify size type id.", 'error'=>"INVALID_SIZE_TYPE_ID");

 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM variant_sizes WHERE id='".$params['id']."'");
 $db->Close();
 
 $out = "Size #".$params['id']." has been removed!";
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_getSizeTypes($params, $sessid, $shellid, $extraParams=null)
{
 $out = "";
 $outArr = array();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM variant_sizes WHERE 1 ORDER BY name ASC");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id'], 'name'=>$db->record['name']);
  $xmlContents = ltrim(rtrim($db->record['xml_params']));
  if($xmlContents != "<xml></xml>")
  {
   $xml = new GXML();
   if($xml->LoadFromString($xmlContents))
   {
	$tmp = $xml->toArray();
	$a['items'] = (count($tmp) == 1) ? array(0=>$tmp['size']) : $tmp;
   }
  }
  $outArr[] = $a;
 }
 $db->Close();

 $out.= count($outArr)." sizes found.";
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_getSizeTypeInfo($params, $sessid, $shellid, $extraParams=null)
{
 /* ARRAYZE PARAMS */
 $tmp = explode("&",$params);
 $params = array();
 for($c=0; $c < count($tmp); $c++)
 {
  $x = explode("=",$tmp[$c]);
  $params[$x[0]] = $x[1];
 }
 /* EOF - ARRAYZE PARAMS */

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM variant_sizes WHERE id='".$params['id']."'");
 $db->Read();

  $outArr = array('id'=>$db->record['id'], 'name'=>$db->record['name']);
  $xmlContents = ltrim(rtrim($db->record['xml_params']));
  if($xmlContents != "<xml></xml>")
  {
   $xml = new GXML();
   if($xml->LoadFromString($xmlContents))
   {
	$tmp = $xml->toArray();
	$outArr['items'] = (count($tmp) == 1) ? array(0=>$tmp['size']) : $tmp;
   }
  }

 $db->Close();

 $out.= "done!";
 return array('message'=>$out, 'outarr'=>$outArr);

}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_addDimType($params, $sessid, $shellid, $extraParams=null)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

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

 $name = $params['name'];
 $xml = $extraParams['xml'];

 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO variant_dim(name,xml_params) VALUES('".$db->Purify($name)."','".$db->Purify($xml)."')");
 $id = mysql_insert_id();
 $db->Close();

 $out.= "done! ID=".$id;
 $outArr = array('id'=>$id,'name'=>$name);

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_editDimType($params, $sessid, $shellid, $extraParams=null)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

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

 if(!$params['id'])
  return array('message'=>"You must specify dim type id.", 'error'=>"INVALID_DIM_TYPE_ID");

 $db = new AlpaDatabase();
 $q = "";
 if($params['name'])
  $q.= ",name='".$db->Purify($params['name'])."'";
 if($extraParams['xml'])
  $q.= ",xml_params='".$db->Purify($extraParams['xml'])."'";
 if($q)
  $db->RunQuery("UPDATE variant_dim SET ".ltrim($q,",")." WHERE id='".$params['id']."'");
 $db->Close();
 
 $out.= "done!";
 $outArr = array('id'=>$id,'name'=>$name);

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_deleteDimType($params, $sessid, $shellid, $extraParams=null)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

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

 if(!$params['id'])
  return array('message'=>"You must specify dim type id.", 'error'=>"INVALID_DIM_TYPE_ID");

 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM variant_dim WHERE id='".$params['id']."'");
 $db->Close();
 
 $out = "Dim #".$params['id']." has been removed!";
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_getDimTypes($params, $sessid, $shellid, $extraParams=null)
{
 $out = "";
 $outArr = array();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM variant_dim WHERE 1 ORDER BY name ASC");
 while($db->Read())
 {
  $a = array('id'=>$db->record['id'], 'name'=>$db->record['name']);
  $xmlContents = ltrim(rtrim($db->record['xml_params']));
  if($xmlContents != "<xml></xml>")
  {
   $xml = new GXML();
   if($xml->LoadFromString($xmlContents))
   {
	$tmp = $xml->toArray();
	$a['items'] = (count($tmp) == 1) ? array(0=>$tmp['dim']) : $tmp;
   }
  }
  $outArr[] = $a;
 }
 $db->Close();

 $out.= count($outArr)." dimensions found.";
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_variants_getDimTypeInfo($params, $sessid, $shellid, $extraParams=null)
{
 /* ARRAYZE PARAMS */
 $tmp = explode("&",$params);
 $params = array();
 for($c=0; $c < count($tmp); $c++)
 {
  $x = explode("=",$tmp[$c]);
  $params[$x[0]] = $x[1];
 }
 /* EOF - ARRAYZE PARAMS */

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM variant_dim WHERE id='".$params['id']."'");
 $db->Read();

  $outArr = array('id'=>$db->record['id'], 'name'=>$db->record['name']);
  $xmlContents = ltrim(rtrim($db->record['xml_params']));
  if($xmlContents != "<xml></xml>")
  {
   $xml = new GXML();
   if($xml->LoadFromString($xmlContents))
   {
	$tmp = $xml->toArray();
	$outArr['items'] = (count($tmp) == 1) ? array(0=>$tmp['dim']) : $tmp;
   }
  }

 $db->Close();

 $out.= "done!";
 return array('message'=>$out, 'outarr'=>$outArr);

}
//-------------------------------------------------------------------------------------------------------------------//

