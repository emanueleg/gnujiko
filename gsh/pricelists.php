<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 19-09-2013
 #PACKAGE: companyprofile-config
 #DESCRIPTION: Official Gnujiko Pricelists manager.
 #VERSION: 2.2beta
 #CHANGELOG: 19-09-2013 : Aggiunto prezzo fornitore, % C&M, % sconto
			 17-04-2013 : Aggiunto i listini extra.
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_pricelists($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'new' : return pricelists_new($args, $sessid, $shellid); break;
  case 'edit' : return pricelists_edit($args, $sessid, $shellid); break;
  case 'delete' : return pricelists_delete($args, $sessid, $shellid); break;
  case 'list' : return pricelists_list($args, $sessid, $shellid); break;

  case 'include' : return pricelists_include($args, $sessid, $shellid); break;
  case 'exclude' : return pricelists_exclude($args, $sessid, $shellid); break;

  default : return pricelists_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function pricelists_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function pricelists_new($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-markuprate' : {$markupRate=$args[$c+1]; $c++;} break;
   case '-vat' : {$vat=$args[$c+1]; $c++;} break;
   case '--default' : $isDefault=true; break;
   case '--extra' : case '-isextra' : $isExtra=true; break;
  }

 if(!$name) return array('message'=>"You must specify a valid name","error"=>"INVALID_NAME");
 
 
 if($isDefault)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE pricelists SET isdefault='0' WHERE isdefault='1'");
  $db->Close();
 }

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id FROM pricelists WHERE isdefault='1'");
 if(!$db->Read())
  $isDefault = true;
 $db->Close();
 
 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO pricelists(name,markuprate,vat,isdefault,isextra) VALUES('"
	.$db->Purify($name)."','".$markupRate."','".$vat."','".$isDefault."','".$isExtra."')");
 $id = mysql_insert_id();
 $db->Close();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT archive_id FROM dynarc_archive_extensions WHERE extension_name='pricing'");
 while($db->Read())
 {
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE id='".$db->record['archive_id']."'");
  if($db2->Read())
   $db2->RunQuery("ALTER TABLE dynarc_".$db2->record['tb_prefix']."_items ADD `pricelist_".$id."_baseprice` FLOAT NOT NULL , ADD `pricelist_".$id."_mrate` FLOAT NOT NULL , ADD `pricelist_".$id."_vat` FLOAT NOT NULL, ADD `pricelist_".$id."_vendorprice` FLOAT NOT NULL, ADD `pricelist_".$id."_cm` FLOAT NOT NULL, ADD `pricelist_".$id."_discount` FLOAT NOT NULL");
  $db2->Close();
 }
 $db->Close();

 $outArr = array('id'=>$id,'name'=>$name,'markuprate'=>$markupRate,'vat'=>$vat,'default'=>$isDefault,'extra'=>$isExtra);
 $out = "done!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function pricelists_edit($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-markuprate' : {$markupRate=$args[$c+1]; $c++;} break;
   case '-vat' : {$vat=$args[$c+1]; $c++;} break;
   case '--default' : $isDefault = true; break;
   case '-isextra' : {$isExtra=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify a valid pricelist id","error"=>"INVALID_ITEM");

 if($isDefault)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("UPDATE pricelists SET isdefault='0' WHERE isdefault='1'");
  $db->Close();
 }

 $db = new AlpaDatabase();
 $q = "";
 if($name)
  $q.= ",name='".$db->Purify($name)."'";
 if(isset($markupRate))
  $q.= ",markuprate='$markupRate'";
 if(isset($vat))
  $q.= ",vat='".$vat."'";
 if($isDefault)
  $q.= ",isdefault='1'";
 if(isset($isExtra))
  $q.= ",isextra='".$isExtra."'";

 $db->RunQuery("UPDATE pricelists SET ".ltrim($q,",")." WHERE id='$id'");
 $db->Close();

 $out = "Pricelist has been updated!";

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function pricelists_delete($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 if($id)
  $db->RunQuery("SELECT * FROM pricelists WHERE id='$id'");
 else if($name)
  $db->RunQuery("SELECT * FROM pricelists WHERE name='$name'");
 else
  return array('message'=>"You must specify the pricelist id. (with -id PRICELIST_ID || -name PRICELIST_NAME)","error"=>"INVALID_ITEM");
 if(!$db->Read())
  return array("message"=>"Pricelist ".($id ? "#$id" : $name)." does not exists", "error"=>"ITEM_DOES_NOT_EXISTS");

 $isDefault = $db->record['isdefault'];

 $id = $db->record['id'];
 $db->RunQuery("DELETE FROM pricelists WHERE id='$id'");
 $db->Close();

 if($isDefault)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT id FROM pricelists WHERE 1 ORDER BY id ASC LIMIT 1");
  if($db->Read())
   $db->RunQuery("UPDATE pricelists SET isdefault='1' WHERE id='".$db->record['id']."'");
  $db->Close();
 } 

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT archive_id FROM dynarc_archive_extensions WHERE extension_name='pricing'");
 while($db->Read())
 {
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT tb_prefix FROM dynarc_archives WHERE id='".$db->record['archive_id']."'");
  if($db2->Read())
   $db2->RunQuery("ALTER TABLE dynarc_".$db2->record['tb_prefix']."_items DROP `pricelist_".$id."_baseprice` , DROP `pricelist_".$id."_mrate` , DROP `pricelist_".$id."_vat`, DROP `pricelist_".$id."_vendorprice`, DROP `pricelist_".$id."_cm`, DROP `pricelist_".$id."_discount`");
  $db2->Close();
 }
 $db->Close();

 $out.= "Pricelist ".($id ? "#$id" : $name)." has been removed.";
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function pricelists_list($args, $sessid, $shellid)
{
 $orderBy = "isdefault DESC,id ASC";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
  }

 $out = "";
 $outArr = array();

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM pricelists WHERE ".($where ? $where : "1")." ORDER BY $orderBy");
 while($db->Read())
 {
  $outArr[] = array('id'=>$db->record['id'],'name'=>$db->record['name'],'markuprate'=>$db->record['markuprate'],'vat'=>$db->record['vat'],'default'=>$db->record['isdefault'],'isextra'=>$db->record['isextra']);
  $out.= "#".$db->record['id']." - ".$db->record['name']."\n";
 }
 $db->Close();
 $out.= "\n".count($outArr)." pricelists found.\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function pricelists_include($args, $sessid, $shellid)
{
 global $_ABSOLUTE_URL, $_BASE_PATH;

 $out = "";
 $outArr = array();

 $plIds = array(); // pricelists id
 $ids = array(); // items id
 $catIds = array(); // categories id

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-plid' : {$plIds[]=$args[$c+1]; $c++;} break;
   case '-plids' : {$plIds = explode(",",$args[$c+1]); $c++;} break;
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$ids[]=$args[$c+1]; $c++;} break;
   case '-ids' : {$ids = explode(",",$args[$c+1]); $c++;} break;
   case '-cat' : {$catIds[]=$args[$c+1]; $c++;} break;
   case '-cats' : {$catIds = explode(",",$args[$c+1]); $c++;} break;
  }

 if(!$_AP)
  return array('message'=>'You must specify the archive. (with: -ap ARCHIVE_PREFIX)', 'error'=>'INVALID_ARCHIVE');

 $_PRICELISTS = array();

 // GET INFO FOR ALL SELECTED PRICELIST //
 $db = new AlpaDatabase();
 for($c=0; $c < count($plIds); $c++)
 {
  $db->RunQuery("SELECT * FROM pricelists WHERE id='".$plIds[$c]."'");
  $db->Read();
  $_PRICELISTS[] = array('id'=>$plIds[$c], 'markuprate'=>$db->record['markuprate'], 'vatrate'=>$db->record['vat']);
 }
 $db->Close();

 $plidsS = implode(",",$plIds);

 /* UPDATE PRICELIST FOR ALL SELECTED ITEMS */
 $db = new AlpaDatabase();
 for($c=0; $c < count($ids); $c++)
 {
  $qry = "UPDATE dynarc_".$_AP."_items SET pricelists='".$plidsS."'";
  for($j=0; $j < count($_PRICELISTS); $j++)
   $qry.= ",pricelist_".$_PRICELISTS[$j]['id']."_baseprice=baseprice"
	.",pricelist_".$_PRICELISTS[$j]['id']."_vat='".$_PRICELISTS[$j]['vatrate']."'"
	.",pricelist_".$_PRICELISTS[$j]['id']."_mrate='".$_PRICELISTS[$j]['markuprate']."'";
	

  $db->RunQuery($qry." WHERE id='".$ids[$c]."'");	
 }
 $db->Close();

 /* UPDATE PRICELIST FOR ALL ITEMS INTO THE SELECTED CATEGORIES */
 $db = new AlpaDatabase();
 for($c=0; $c < count($catIds); $c++)
 {
  $qry = "UPDATE dynarc_".$_AP."_items SET pricelists='".$plidsS."'";
  for($j=0; $j < count($_PRICELISTS); $j++)
   $qry.= ",pricelist_".$_PRICELISTS[$j]['id']."_baseprice=baseprice"
	.",pricelist_".$_PRICELISTS[$j]['id']."_vat='".$_PRICELISTS[$j]['vatrate']."'"
	.",pricelist_".$_PRICELISTS[$j]['id']."_mrate='".$_PRICELISTS[$j]['markuprate']."'";


  $db->RunQuery($qry." WHERE cat_id='".$catIds[$c]."'");
 }
 $db->Close();

 $out.= "done!";

 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function pricelists_exclude($args, $sessid, $shellid)
{
 global $_ABSOLUTE_URL, $_BASE_PATH;

 $out = "";
 $outArr = array();

 $plIds = array(); // pricelists id
 $ids = array(); // items id
 $catIds = array(); // categories id

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-plid' : {$plIds[]=$args[$c+1]; $c++;} break;
   case '-plids' : {$plIds = explode(",",$args[$c+1]); $c++;} break;
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$ids[]=$args[$c+1]; $c++;} break;
   case '-ids' : {$ids = explode(",",$args[$c+1]); $c++;} break;
   case '-cat' : {$catIds[]=$args[$c+1]; $c++;} break;
   case '-cats' : {$catIds = explode(",",$args[$c+1]); $c++;} break;
  }

 if(!$_AP)
  return array('message'=>'You must specify the archive. (with: -ap ARCHIVE_PREFIX)', 'error'=>'INVALID_ARCHIVE');

 $_PRICELISTS = array();

 // GET INFO FOR ALL SELECTED PRICELIST //
 $db = new AlpaDatabase();
 for($c=0; $c < count($plIds); $c++)
 {
  $db->RunQuery("SELECT * FROM pricelists WHERE id='".$plIds[$c]."'");
  $db->Read();
  $_PRICELISTS[] = array('id'=>$plIds[$c], 'markuprate'=>$db->record['markuprate'], 'vatrate'=>$db->record['vat']);
 }
 $db->Close();

 $plidsS = implode(",",$plIds);

 /* UPDATE PRICELIST FOR ALL SELECTED ITEMS */
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 for($c=0; $c < count($ids); $c++)
 {
  $db->RunQuery("SELECT pricelists FROM dynarc_".$_AP."_items WHERE id='".$ids[$c]."'");
  $db->Read();
  $itmPricelists = $db->record['pricelists'];
  if(!$itmPricelists)
   continue;
  $x = explode(",",$itmPricelists);
  $tmp = ",".ltrim($itmPricelists,",").",";
  for($i=0; $i < count($_PRICELISTS); $i++)
  {
   if(in_array($_PRICELISTS[$i]['id'],$x))
	$tmp = str_replace(",".$_PRICELISTS[$i]['id'].",",",",$tmp);
  }
  $tmp = ltrim($tmp,",");
  if(strrpos($tmp,","))
   $tmp = substr($tmp,0,-1);
  

  $qry = "UPDATE dynarc_".$_AP."_items SET pricelists='".$tmp."'";
  for($j=0; $j < count($_PRICELISTS); $j++)
   $qry.= ",pricelist_".$_PRICELISTS[$j]['id']."_baseprice=baseprice"
	.",pricelist_".$_PRICELISTS[$j]['id']."_vat='0'"
	.",pricelist_".$_PRICELISTS[$j]['id']."_mrate='0'"
	.",pricelist_".$_PRICELISTS[$j]['id']."_vendorprice='0'"
	.",pricelist_".$_PRICELISTS[$j]['id']."_cm='0'"
	.",pricelist_".$_PRICELISTS[$j]['id']."_discount='0'";
	

  $db2->RunQuery($qry." WHERE id='".$ids[$c]."'");	
 }
 $db->Close();
 $db2->Close();

 /* UPDATE PRICELIST FOR ALL ITEMS INTO THE SELECTED CATEGORIES */
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 for($c=0; $c < count($catIds); $c++)
 {
  $db->RunQuery("SELECT id,pricelists FROM dynarc_".$_AP."_items WHERE cat_id='".$catIds[$c]."'");
  while($db->Read())
  {
   $itmId = $db->record['id'];
   $itmPricelists = $db->record['pricelists'];
   if(!$itmPricelists)
    continue;
   $x = explode(",",$itmPricelists);
   $tmp = ",".ltrim($itmPricelists,",").",";
   for($i=0; $i < count($_PRICELISTS); $i++)
   {
    if(in_array($_PRICELISTS[$i]['id'],$x))
	 $tmp = str_replace(",".$_PRICELISTS[$i]['id'].",",",",$tmp);
   }
   $tmp = ltrim($tmp,",");
   if(strrpos($tmp,","))
    $tmp = substr($tmp,0,-1);
  
   $qry = "UPDATE dynarc_".$_AP."_items SET pricelists='".$tmp."'";
   for($j=0; $j < count($_PRICELISTS); $j++)
    $qry.= ",pricelist_".$_PRICELISTS[$j]['id']."_baseprice=baseprice"
	.",pricelist_".$_PRICELISTS[$j]['id']."_vat='0'"
	.",pricelist_".$_PRICELISTS[$j]['id']."_mrate='0'"
	.",pricelist_".$_PRICELISTS[$j]['id']."_vendorprice='0'"
	.",pricelist_".$_PRICELISTS[$j]['id']."_cm='0'"
	.",pricelist_".$_PRICELISTS[$j]['id']."_discount='0'";

   $db2->RunQuery($qry." WHERE id='".$itmId."'");	
  }
 }
 $db->Close();
 $db2->Close();

 $out.= "done!";

 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

