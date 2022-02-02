<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 26-03-2013
 #PACKAGE: dynarc-idoc-extension
 #DESCRIPTION: IDoc extension for Dynarc.
 #VERSION: 2.1beta
 #CHANGELOG: 26-03-2013 : Sistemate le funzioni import & export.
 #TODO:
 
*/

global $_BASE_PATH;

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_install($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_categories` ADD `idocs` VARCHAR( 255 ) NOT NULL ,
ADD `def_item_idocs` VARCHAR( 255 ) NOT NULL");
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` ADD `idocs` VARCHAR( 255 ) NOT NULL");
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_idoccatprop` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`cat_id` INT( 11 ) NOT NULL ,
`idoc_aid` INT( 11 ) NOT NULL ,
`idoc_id` INT( 11 ) NOT NULL ,
`xmlret` TEXT NOT NULL ,
INDEX ( `cat_id` , `idoc_aid` , `idoc_id` )
)");
 $db->RunQuery("CREATE TABLE IF NOT EXISTS `dynarc_".$archiveInfo['prefix']."_idocitmprop` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`item_id` INT( 11 ) NOT NULL ,
`idoc_aid` INT( 11 ) NOT NULL ,
`idoc_id` INT( 11 ) NOT NULL ,
`xmlret` TEXT NOT NULL ,
INDEX ( `item_id` , `idoc_aid` , `idoc_id` )
)");
 $db->Close();

 
 return array("message"=>"IDoc extension has been installed into archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_uninstall($params, $sessid, $shellid=0, $archiveInfo=null)
{
 $db = new AlpaDatabase();
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_categories` DROP `idocs`, DROP `def_item_idocs`");
 $db->RunQuery("ALTER TABLE `dynarc_".$archiveInfo['prefix']."_items` DROP `idocs`");
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_idoccatprop`");
 $db->RunQuery("DROP TABLE IF EXISTS `dynarc_".$archiveInfo['prefix']."_idocitmprop`");
 $db->Close();

 return array("message"=>"IDoc extension has been removed from archive ".$archiveInfo['name']."\n");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_add($params, $sessid, $shellid, $extraParams=null)
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

 $sessInfo = sessionInfo($sessid);

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


 /* CHECK FOR IDOC */
 if($params['idocap'])
 {
  $ret = GShell("dynarc archive-info -prefix `".$params['idocap']."`",$sessid,$shellid);
  $idocAID = $ret['outarr']['id'];
 }
 else if($params['idocaid'])
  $idocAID = $params['idocaid'];
 else
  return array("message"=>"You must specify IDoc archive. with(idocap or idocaid)","error"=>"INVALID_IDOC_ARCHIVE");
 
 $ret = GShell("dynarc item-info -aid `".$idocAID."`".($params['idocid'] ? " -id `".$params['idocid']."`" : " -alias `".$params['idocalias']."`"),$sessid,$shellid);
 $idocID = $ret['outarr']['id'];


 if($params['cat']) /* CHECK FOR CATEGORY */
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$params['cat']."'");
  if(!$db->Read())
  {
   $db->Close();
   return array('message'=>"Category #".$params['cat']." does not exists into archive ".$archiveInfo['name'], 'error'=>"CATEGORY_DOES_NOT_EXISTS");
  }
  /* CHECK PERMISSION TO WRITE */
  $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid'],$db->record['shgrps'],$db->record['shusrs']);
  $db->Close();
  if(!$m->canWrite($sessInfo['uid']))
   return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT idocs,def_item_idocs FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$params['cat']."'");
  $db->Read();
  $idocs = array();
  $defItemIdocs = array();
  if($db->record['idocs'])
  {
   $x = explode(",",$db->record['idocs']);
   for($c=0; $c < count($x); $c++)
   {
    $i = explode(":",$x[$c]);
    $idocs[] = array('aid'=>$i[0],'id'=>$i[1]);
   }
  }
  if($db->record['def_item_idocs'])
  {
   $x = explode(",",$db->record['def_item_idocs']);
   for($c=0; $c < count($x); $c++)
   {
    $i = explode(":",$x[$c]);
    if(strpos($i[0],"*") !== false)
	 $defItemIdocs[] = array('aid'=>substr($i[0],1),'id'=>$i[1],'all'=>true);
	else
     $defItemIdocs[] = array('aid'=>$i[0],'id'=>$i[1]);
   }
  }
  $db->Close();

  if(isset($params['default']))
   $defItemIdocs[] = array('aid'=>$idocAID,'id'=>$idocID,'all'=>($params['all'] ? true : false));
  else
   $idocs[] = array('aid'=>$idocAID,'id'=>$idocID);

  $q1 = "";
  for($c=0; $c < count($idocs); $c++)
   $q1.= ",".$idocs[$c]['aid'].":".$idocs[$c]['id'];
  $q1 = ltrim($q1,",");

  $q2 = "";
  for($c=0; $c < count($defItemIdocs); $c++)
   $q2.= ",".($defItemIdocs[$c]['all'] ? "*" : "").$defItemIdocs[$c]['aid'].":".$defItemIdocs[$c]['id'];
  $q2 = ltrim($q2,",");
  
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET idocs='".$q1."',def_item_idocs='".$q2."' WHERE id='".$params['cat']."'");
  $db->Close();
 }
 else if($params['id']) /* CHECK FOR ITEM */
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$params['id']."'");
  if(!$db->Read())
  {
   $db->Close();
   return array('message'=>"Item #".$params['id']." does not exists into archive ".$archiveInfo['name'], 'error'=>"ITEM_DOES_NOT_EXISTS");
  }
  /* CHECK PERMISSION TO WRITE */
  $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid'],$db->record['shgrps'],$db->record['shusrs']);
  $db->Close();
  if(!$m->canWrite($sessInfo['uid']))
   return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT idocs FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$params['id']."'");
  $db->Read();

  $idocs = array();
  if($db->record['idocs'])
  {
   $x = explode(",",$db->record['idocs']);
   for($c=0; $c < count($x); $c++)
   {
    $i = explode(":",$x[$c]);
    $idocs[] = array('aid'=>$i[0],'id'=>$i[1]);
   }
  }
  $db->Close();

  $idocs[] = array('aid'=>$idocAID,'id'=>$idocID);

  $q = "";
  for($c=0; $c < count($idocs); $c++)
   $q.= ",".$idocs[$c]['aid'].":".$idocs[$c]['id'];
  $q = ltrim($q,",");

  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET idocs='".$q."' WHERE id='".$params['id']."'");
  $db->Close();
 }
 return array('message'=>"done!");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_remove($params, $sessid, $shellid, $extraParams=null)
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

 $sessInfo = sessionInfo($sessid);

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


 /* CHECK FOR IDOC */
 if($params['idocap'])
 {
  $ret = GShell("dynarc archive-info -prefix `".$params['idocap']."`",$sessid,$shellid);
  $idocAID = $ret['outarr']['id'];
 }
 else if($params['idocaid'])
  $idocAID = $params['idocaid'];
 else
  return array("message"=>"You must specify IDoc archive. with(idocap or idocaid)","error"=>"INVALID_IDOC_ARCHIVE");

 $idocID = $params['idocid'];
 
 if($params['cat']) /* CHECK FOR CATEGORY */
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$params['cat']."'");
  if(!$db->Read())
  {
   $db->Close();
   return array('message'=>"Category #".$params['cat']." does not exists into archive ".$archiveInfo['name'], 'error'=>"CATEGORY_DOES_NOT_EXISTS");
  }
  /* CHECK PERMISSION TO WRITE */
  $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid'],$db->record['shgrps'],$db->record['shusrs']);
  $db->Close();
  if(!$m->canWrite($sessInfo['uid']))
   return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT idocs,def_item_idocs FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$params['cat']."'");
  $db->Read();
  $idocs = array();
  $defItemIdocs = array();
  if($db->record['idocs'])
  {
   $x = explode(",",$db->record['idocs']);
   for($c=0; $c < count($x); $c++)
   {
    $i = explode(":",$x[$c]);
	if(!isset($params['default']) && ($idocAID == $i[0]) && ($idocID == $i[1]))
	 continue;
    $idocs[] = array('aid'=>$i[0],'id'=>$i[1]);
   }
  }
  if($db->record['def_item_idocs'])
  {
   $x = explode(",",$db->record['def_item_idocs']);
   for($c=0; $c < count($x); $c++)
   {
	$all = false;
    $i = explode(":",$x[$c]);
	if(strpos($i[0],"*") !== false)
	{
	 $all = true;
	 $i[0] = substr($i[0],1);
	}
	if(isset($params['default']) && ($idocAID == $i[0]) && ($idocID == $i[1]))
	 continue;
    $defItemIdocs[] = array('aid'=>$i[0],'id'=>$i[1],'all'=>$all);
   }
  }
  $db->Close();

  $q1 = "";
  for($c=0; $c < count($idocs); $c++)
   $q1.= ",".$idocs[$c]['aid'].":".$idocs[$c]['id'];
  $q1 = ltrim($q1,",");

  $q2 = "";
  for($c=0; $c < count($defItemIdocs); $c++)
   $q2.= ",".($defItemIdocs[$c]['all'] ? "*" : "").$defItemIdocs[$c]['aid'].":".$defItemIdocs[$c]['id'];
  $q2 = ltrim($q2,",");
  
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET idocs='".$q1."',def_item_idocs='".$q2."' WHERE id='".$params['cat']."'");
  $db->Close();

  /* Remove data */
  $db = new AlpaDatabase();
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_idoccatprop WHERE cat_id='".$params['cat']."' AND idoc_aid='".$idocAID."' AND idoc_id='".$idocID."'");
  $db->Close();
 }
 else if($params['id']) /* CHECK FOR ITEM */
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$params['id']."'");
  if(!$db->Read())
  {
   $db->Close();
   return array('message'=>"Item #".$params['id']." does not exists into archive ".$archiveInfo['name'], 'error'=>"ITEM_DOES_NOT_EXISTS");
  }
  /* CHECK PERMISSION TO WRITE */
  $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid'],$db->record['shgrps'],$db->record['shusrs']);
  $db->Close();
  if(!$m->canWrite($sessInfo['uid']))
   return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT idocs FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$params['id']."'");
  $db->Read();

  $idocs = array();
  if($db->record['idocs'])
  {
   $x = explode(",",$db->record['idocs']);
   for($c=0; $c < count($x); $c++)
   {
    $i = explode(":",$x[$c]);
	if(($idocAID == $i[0]) && ($idocID == $i[1]))
	 continue;
    $idocs[] = array('aid'=>$i[0],'id'=>$i[1]);
   }
  }
  $db->Close();

  $q = "";
  for($c=0; $c < count($idocs); $c++)
   $q.= ",".$idocs[$c]['aid'].":".$idocs[$c]['id'];
  $q = ltrim($q,",");

  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET idocs='".$q."' WHERE id='".$params['id']."'");
  $db->Close();

  /* Remove data */
  $db = new AlpaDatabase();
  $db->RunQuery("DELETE FROM dynarc_".$archiveInfo['prefix']."_idocitmprop WHERE item_id='".$params['cat']."' AND idoc_aid='".$idocAID."' AND idoc_id='".$idocID."'");
  $db->Close();

 }
 return array('message'=>"done!");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_editdefault($params, $sessid, $shellid, $extraParams=null)
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

 $sessInfo = sessionInfo($sessid);

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


 /* CHECK FOR IDOC */
 if($params['idocap'])
 {
  $ret = GShell("dynarc archive-info -prefix `".$params['idocap']."`",$sessid,$shellid);
  $idocAID = $ret['outarr']['id'];
 }
 else if($params['idocaid'])
  $idocAID = $params['idocaid'];
 else
  return array("message"=>"You must specify IDoc archive. with(idocap or idocaid)","error"=>"INVALID_IDOC_ARCHIVE");

 $idocID = $params['idocid'];

 if(!$params['cat'])
  return array("message"=>"You must specify the category.","error"=>"INVALID_CATEGORY");

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$params['cat']."'");
  if(!$db->Read())
  {
   $db->Close();
   return array('message'=>"Category #".$params['cat']." does not exists into archive ".$archiveInfo['name'], 'error'=>"CATEGORY_DOES_NOT_EXISTS");
  }
  /* CHECK PERMISSION TO WRITE */
  $m = new GMOD($db->record['_mod'],$db->record['uid'],$db->record['gid'],$db->record['shgrps'],$db->record['shusrs']);
  $db->Close();
  if(!$m->canWrite($sessInfo['uid']))
   return array("message"=>"Permission denied!\n","error"=>"PERMISSION_DENIED");

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT idocs,def_item_idocs FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$params['cat']."'");
  $db->Read();
  $defItemIdocs = array();
  if($db->record['def_item_idocs'])
  {
   $x = explode(",",$db->record['def_item_idocs']);
   for($c=0; $c < count($x); $c++)
   {
    $i = explode(":",$x[$c]);
    if(strpos($i[0],"*") !== false)
	 $ret = array('aid'=>substr($i[0],1),'id'=>$i[1],'all'=>true);
	else
     $ret = array('aid'=>$i[0],'id'=>$i[1]);
	if(($idocAID == $ret['aid']) && ($idocID == $ret['id']))
	{
	 if($params['all'] == "true")
	  $ret['all'] = true;
	 else if($params['all'] == "false")
	  $ret['all'] = false;
	}
	$defItemIdocs[] = $ret;
   }
  }
  $db->Close();

  $q2 = "";
  for($c=0; $c < count($defItemIdocs); $c++)
   $q2.= ",".($defItemIdocs[$c]['all'] ? "*" : "").$defItemIdocs[$c]['aid'].":".$defItemIdocs[$c]['id'];
  $q2 = ltrim($q2,",");
  
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_categories SET def_item_idocs='".$q2."' WHERE id='".$params['cat']."'");
  $db->Close();


 return array('message'=>"done!");
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_itmpropget($params, $sessid, $shellid, $extraParams=null)
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

 $sessInfo = sessionInfo($sessid);

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

 if($params['idocap'])
 {
  $ret = GShell("dynarc archive-info -prefix `".$params['idocap']."`",$sessid,$shellid);
  $params['idocaid'] = $ret['outarr']['id'];
 }
 if($params['idocalias'])
 {
  $ret = GShell("dynarc item-info -aid `".$params['idocaid']."` -alias `".$params['idocalias']."`",$sessid,$shellid);
  $params['idocid'] = $ret['outarr']['id'];
 }

 $outArr = array();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM dynarc_".$archiveInfo['prefix']."_idocitmprop WHERE item_id='".$params['id']."' AND idoc_aid='".$params['idocaid']."' AND idoc_id='".$params['idocid']."'");
 if($db->Read())
 {
  $xmlContents = ltrim(rtrim($db->record['xmlret']));
  $xml = new GXML();
  if($xml->LoadFromString($xmlContents))
   $outArr = $xml->toArray();
 }
 $db->Close();

 return array('message'=>'done!','outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_catset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'id' : {$id=$args[$c+1]; $c++;} break;
   case 'idocaid' : {$idocAID=$args[$c+1]; $c++;} break;
   case 'idocid' : {$idocID=$args[$c+1]; $c++;} break;
   case 'xmlprop' : {$xmlProp=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 if(!$id)
 {
  $db->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_idoccatprop(cat_id,idoc_aid,idoc_id,xmlret) VALUES('".$catInfo['id']."','"
	.$idocAID."','".$idocID."','".$db->Purify($xmlProp)."')");
 }
 else
 {
  $q = "";
  if($idocAID)
   $q.= ",idoc_aid='".$idocAID."'";
  if($idocID)
   $q.= ",idoc_id='".$idocID."'";
  if($xmlProp)
   $q.= ",xmlret='".$db->Purify($xmlProp)."'";
  if($q != "")
   $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_idoccatprop SET ".ltrim($q,",")." WHERE id='".$id."'");
 }
 $db->Close();

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_set($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_idoc_catset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'ap' : {$idocAP=$args[$c+1]; $c++;} break;
   case 'aid' : {$idocAID=$args[$c+1]; $c++;} break;
   case 'id' : {$idocID=$args[$c+1]; $c++;} break;
   case 'alias' : {$idocALIAS=$args[$c+1]; $c++;} break;
   case 'xmlprop' : {$xmlProp=$args[$c+1]; $c++;} break;
  }

 if($idocAP)
 {
  $ret = GShell("dynarc archive-info -prefix `".$idocAP."`",$sessid,$shellid);
  $idocAID = $ret['outarr']['id'];
 }
 if($idocALIAS)
 {
  $ret = GShell("dynarc item-info -aid `".$idocAID."` -alias `".$idocALIAS."`",$sessid,$shellid);
  $idocID = $ret['outarr']['id'];
 }

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM dynarc_".$archiveInfo['prefix']."_idocitmprop WHERE item_id='".$itemInfo['id']."' AND idoc_aid='".$idocAID."' AND idoc_id='"
	.$idocID."'");
 if(!$db->Read())
 {
  $db2 = new AlpaDatabase();
  $db2->RunQuery("INSERT INTO dynarc_".$archiveInfo['prefix']."_idocitmprop(item_id,idoc_aid,idoc_id,xmlret) VALUES('".$itemInfo['id']."','"
	.$idocAID."','".$idocID."','".$db2->Purify($xmlProp)."')");
  $db2->Close();
 }
 else
 {
  $db2 = new AlpaDatabase();
  $q = "";
  if($xmlProp)
   $q.= ",xmlret='".$db2->Purify($xmlProp)."'";
  if($q != "")
   $db2->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_idocitmprop SET ".ltrim($q,",")." WHERE id='".$db->record['id']."'");
  $db2->Close();
 }
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_catunset($args, $sessid, $shellid, $archiveInfo, $catInfo)
{

}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_unset($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_idoc_catunset($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_catget($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 global $_BASE_PATH;

 /*for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case 'xmlprop' : 
  }*/

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT idocs,def_item_idocs FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$catInfo['id']."'");
 $db->Read();

 $catInfo['idocs'] = array();
 $catInfo['def_item_idocs'] = array();

 if($db->record['idocs'])
 {
  $x = explode(",",$db->record['idocs']);
  for($c=0; $c < count($x); $c++)
  {
   $i = explode(":",$x[$c]);
   $catInfo['idocs'][] = array('aid'=>$i[0],'id'=>$i[1]);
  }
 }
 if($db->record['def_item_idocs'])
 {
  $x = explode(",",$db->record['def_item_idocs']);
  for($c=0; $c < count($x); $c++)
  {
   $i = explode(":",$x[$c]);
   if(strpos($i[0],"*") !== false)
	$catInfo['def_item_idocs'][] = array('aid'=>substr($i[0],1),'id'=>$i[1],'all'=>true);
   else
    $catInfo['def_item_idocs'][] = array('aid'=>$i[0],'id'=>$i[1]);
  }
 }
 $db->Close();

 return $catInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_get($args, $sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return dynarcextension_idoc_catget($args, $sessid, $shellid, $archiveInfo, $itemInfo);

 global $_BASE_PATH;

 $itemInfo['idocs'] = array();

 /* First get default idocs */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT def_item_idocs FROM dynarc_".$archiveInfo['prefix']."_categories WHERE id='".$itemInfo['cat_id']."'");
 $db->Read();
 if($db->record['def_item_idocs'])
 {
  $x = explode(",",$db->record['def_item_idocs']);
  for($c=0; $c < count($x); $c++)
  {
   $i = explode(":",$x[$c]);
   if(strpos($i[0],"*") !== false)
	$itemInfo['idocs'][] = array('aid'=>substr($i[0],1),'id'=>$i[1],'default'=>true,'all'=>true);
   else
    $itemInfo['idocs'][] = array('aid'=>$i[0],'id'=>$i[1],'default'=>true);
  }
 }
 $db->Close();

 /* Get item idocs */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT idocs FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 if($db->record['idocs'])
 {
  $x = explode(",",$db->record['idocs']);
  for($c=0; $c < count($x); $c++)
  {
   $i = explode(":",$x[$c]);
   $itemInfo['idocs'][] = array('aid'=>$i[0],'id'=>$i[1]);
  }
 }
 $db->Close();

 return $itemInfo;
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_oncreateitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_oncreatecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_onedititem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_oneditcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_ondeleteitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_ondeletecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_ontrashitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_ontrashcategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_onrestoreitem($args, $sessid, $shellid, $archiveInfo, $itemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_onrestorecategory($args, $sessid, $shellid, $archiveInfo, $catInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_onmoveitem($args, $sessid, $shellid, $archiveInfo, $oldItemInfo, $newItemInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_onmovecategory($args, $sessid, $shellid, $archiveInfo, $oldCatInfo, $newCatInfo)
{
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_oncopyitem($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_oncopycategory($sessid, $shellid, $archiveInfo, $srcInfo, $cloneInfo)
{
 return $cloneInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_export($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 if($isCategory)
  return array('xml'=>"");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT thumbdata,params FROM dynarc_".$archiveInfo['prefix']."_items WHERE id='".$itemInfo['id']."'");
 $db->Read();
 $xml = "<idoc thumbdata=\"".$db->record['thumbdata']."\" params=\"".sanitize($db->record['params'])."\"/>";
 $db->Close();
 return array('xml'=>$xml);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_import($sessid, $shellid, $archiveInfo, $itemInfo, $extNode, $isCategory=false)
{
 if($isCategory)
  return true;

 $db = new AlpaDatabase();
 $q = "";
 if($extNode->getString("params"))
  $q.= ",params='".$db->Purify($extNode->getString('params'))."'";
 if($extNode->getString("thumbdata"))
  $q.= ",thumbdata='".$db->Purify($extNode->getString('thumbdata'))."'";

 if($q)
  $db->RunQuery("UPDATE dynarc_".$archiveInfo['prefix']."_items SET ".ltrim($q,",")." WHERE id='".$itemInfo['id']."'");

 $db->Close();

 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_syncexport($sessid, $shellid, $archiveInfo, $itemInfo, $isCategory=false)
{
 global $_USERS_HOMES;
 $xml = "";
 $attachments = array();

 return array('xml'=>$xml,'attachments'=>$attachments);
}
//-------------------------------------------------------------------------------------------------------------------//
function dynarcextension_idoc_syncimport($sessid, $shellid, $archiveInfo, $itemInfo, $xmlNode, $isCategory=false)
{
 global $_USER_PATH;
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//


