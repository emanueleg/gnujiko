<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 19-11-2016
 #PACKAGE: gmart
 #DESCRIPTION: GMart - Functions
 #VERSION: 2.7beta
 #CHANGELOG: 19-11-2016 : Aggiunta funzione find-product.
			 02-03-2016 : Aggiornata funzione export.
			 26-02-2016 : Rifatta funzione export-to-excel.
			 16-01-2016 : Bug fix virgolette su esporta in excel.
			 17-02-2015 : Aggiunto colonne su esporta in excel.
			 02-10-2014 : Aggiunta funzione fix-pricing.
			 05-06-2014 : Bug fix su bulkedit.
			 30-09-2013 : Bug fix bulkedit.
 #DEPENDS:
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_gmart($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'bulkedit' : return gmart_bulkEdit($args, $sessid, $shellid); break;
  case 'export-to-excel' : return gmart_exportToExcel($args, $sessid, $shellid); break;
  case 'clean-all-custompricing' : return gmart_cleanAllCustomPricing($args, $sessid, $shellid); break;

  case 'fix-pricing' : return gmart_fixPricing($args, $sessid, $shellid); break;
  case 'fix-counters' : case 'update-counters' : return gmart_updateCounters($args, $sessid, $shellid); break;
  case 'increment-counter' : return gmart_increment_counter($args, $sessid, $shellid); break;
  case 'decrement-counter' : return gmart_decrement_counter($args, $sessid, $shellid); break;

  case 'find-product' : return gmart_findProduct($args, $sessid, $shellid); break;

  default : return gmart_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function gmart_invalidArguments()
{
 $out = "Usage:\n";
 $out.= "gmart ACTION [parameters]\n\n";
 $out.= "List of gmart actions:\n";
 $out.= "bulkedit: Modification bulk products.\n";

 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function gmart_bulkEdit($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;
 
 $out = "";
 $outArr = array();

 $_AP = "gmart";
 $catIds = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-cat' : {$catIds[] = $args[$c+1]; $c++;} break;
   case '-cats' : case '-categories' : {$cats=$args[$c+1]; $c++;} break; // Le categorie devono essere divise da una (,) virgola.
   case '--entire-archive' : $entireArchive=true; break;
   case '-brand' : {$brand=$args[$c+1]; $c++;} break;
   case '-brandid' : {$brandId=$args[$c+1]; $c++;} break;
   case '-vendorid' : {$vendorId=$args[$c+1]; $c++;} break;
   case '-vendorname' : {$vendorName=$args[$c+1]; $c++;} break;
   case '-vendorprice' : {$vendorPrice=$args[$c+1]; $c++;} break;
   case '-cm' : {$cm=$args[$c+1]; $c++;} break;
   case '-baseprice' : {$basePrice=$args[$c+1]; $c++;} break;
   case '-increase-baseprice' : {$increaseBasePrice=$args[$c+1]; $c++;} break;
   case '-discount' : {$discount=$args[$c+1]; $c++;} break;
   case '-discount-pricelist' : {$discountPricelist=$args[$c+1]; $c++;} break;
   case '-markuprate' : {$markupRate=$args[$c+1]; $c++;} break;
   case '-markuprate-pricelist' : {$markupRatePricelist=$args[$c+1]; $c++;} break;
   case '-vatrate' : {$vatRate=$args[$c+1]; $c++;} break;
   case '-units' : {$units=$args[$c+1]; $c++;} break;
   case '-where' : {$where=$args[$c+1]; $c++;} break;
  }

 if($cats)
  $catIds = explode(",",$cats);

 if(!count($catIds) && !$entireArchive)
  return array("message"=>"You must specify at least one category.","error"=>"INVALID_CATEGORY");

 if($vendorId)
 {
  // get vendor name //
  $ret = GShell("dynarc item-info -ap rubrica -id '".$vendorId."'",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $vendorName = $ret['outarr']['name'];
 }

 /* Get pricelists */
 $ret = GShell("pricelists list",$sessid,$shellid);
 if($ret['error'])
  return $ret;
 $pricelists = $ret['outarr'];

 $_ITEMS = array();
 /* GET ALL ITEMS INFO */
 $db = new AlpaDatabase();
 if($entireArchive)
 {
  $db->RunQuery("SELECT id,name,baseprice,model FROM dynarc_".$_AP."_items WHERE trash='0'".($where ? " AND (".$where.")" : ""));
  while($db->Read())
  {
   $_ITEMS[] = array('id'=>$db->record['id'], 'name'=>$db->record['name'], 'baseprice'=>$db->record['baseprice'], 'model'=>$db->record['model']);
  }
 }
 else
 {
  for($c=0; $c < count($catIds); $c++)
  {
   $db->RunQuery("SELECT id,name,baseprice,model FROM dynarc_".$_AP."_items WHERE cat_id='".$catIds[$c]."' AND trash='0'".($where ? " AND (".$where.")" : ""));
   while($db->Read())
   {
    $_ITEMS[] = array('id'=>$db->record['id'], 'name'=>$db->record['name'], 'baseprice'=>$db->record['baseprice'], 'model'=>$db->record['model']);
   }
  }
 }
 $db->Close();

 $estimate = count($_ITEMS);
 $interface = array("name"=>"progressbar","steps"=>$estimate);
 gshPreOutput($shellid,count($_ITEMS)." will be updated...", "ESTIMATION", "", "PASSTHRU", $interface);

 // prepare query //
 $commonQry = "";
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 if($brand)
  $brand = $db->Purify($brand);
 if($vendorName)
  $vendorName = $db->Purify($vendorName);

 for($c=0; $c < count($pricelists); $c++)
 {
  if(isset($vendorPrice))
   $commonQry.= ",pricelist_".$pricelists[$c]['id']."_vendorprice='".$vendorPrice."'";
  if(isset($cm))
   $commonQry.= ",pricelist_".$pricelists[$c]['id']."_cm='".$cm."'";
  if(isset($basePrice))
   $commonQry.= ",pricelist_".$pricelists[$c]['id']."_baseprice='".$basePrice."'";
  if(isset($discount) && !$discountPricelist)
   $commonQry.= ",pricelist_".$pricelists[$c]['id']."_discount='".$discount."'";
  if(isset($markupRate) && !$markupRatePricelist)
   $commonQry.= ",pricelist_".$pricelists[$c]['id']."_mrate='".$markupRate."'";
  if(isset($vatRate))
   $commonQry.= ",pricelist_".$pricelists[$c]['id']."_vat='".$vatRate."'";
 }

 if(isset($basePrice))
  $commonQry.= ",baseprice='".$basePrice."'";
 if(isset($discount) && $discountPricelist)
  $commonQry.= ",pricelist_".$discountPricelist."_discount='".$discount."'";
 if(isset($markupRate) && $markupRatePricelist)
  $commonQry.= ",pricelist_".$markupRatePricelist."_mrate='".$markupRate."'";
 if(isset($vatRate))
  $commonQry.= ",vat='".$vatRate."'";
 if(isset($units))
  $commonQry.= ",units='".$units."'";

 $mtime = date('Y-m-d H:i:s');

 for($c=0; $c < count($_ITEMS); $c++)
 {
  $itm = $_ITEMS[$c];
  $qry = "mtime='".$mtime."'";
  if(isset($brand))
   $qry.= ",brand='".$brand."',name='".$brand." ".$db->Purify($itm['model'])."'";
  if(isset($brandId))
   $qry.= ",brand_id='".$brandId."'";
  if(isset($increaseBasePrice))
  {
   $bp = $itm['baseprice'] ? ($itm['baseprice'] + (($itm['baseprice']/100)*$increaseBasePrice)) : 0;
   $qry.= ",baseprice='".$bp."'";
   for($i=0; $i < count($pricelists); $i++)
	$qry.= ",pricelist_".$pricelists[$i]['id']."_baseprice='".$bp."'";
  }
  
  $db->RunQuery("UPDATE dynarc_".$_AP."_items SET ".ltrim($qry.$commonQry,",")." WHERE id='".$itm['id']."'");

  if($vendorId || $vendorName)
  {
   $db2->RunQuery("DELETE FROM dynarc_".$_AP."_vendorprices WHERE item_id='".$itm['id']."'");
   $db2->RunQuery("INSERT INTO dynarc_".$_AP."_vendorprices(item_id,vendor_id,vendor_name) VALUES('".$itm['id']."','".$vendorId."','".$vendorName."')");
  }

  gshPreOutput($shellid,"Item ".$itm['name']." has been updated.", "PROGRESS", $itm['id']);
 }
 $db->Close();
 $db2->Close();

 $out.= "\ndone!\n".count($_ITEMS)." has been updated!";
 //$out.= "Last query:\n";
 //$out.= $qry.$commonQry;
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gmart_exportToExcel($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
 require_once($_BASE_PATH."var/lib/excel.php");

 $out = "";
 $outArr = array();

 $_AP = "gmart";
 $ids = array();
 $catIds = array();
 $includeSubCategories = false;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$ids[] = $args[$c+1]; $c++;} break;
   case '-cat' : {$catIds[] = $args[$c+1]; $c++;} break;
   case '-cats' : case '-categories' : {$cats=$args[$c+1]; $c++;} break; // Le categorie devono essere divise da una (,) virgola.

   case '-f' : case '-file' : {$fileName=$args[$c+1]; $c++;} break;
   case '-t' : case '-title' : case '-sn' : {$sheetName=$args[$c+1]; $c++;} break;

   case '--entire-archive' : $entireArchive=true; break;	/* TODO: da fare */
   case '--include-subcat' : case '--include-subcategories' : $includeSubCategories=true; break;
  }

 if(!$fileName)
  return array('message'=>"You must specify the file name. (with: -file FILE_NAME)",'error'=>"INVALID_FILE");
 $pi = pathinfo($fileName);
 if(!$pi['extension'])
  $fileName.= ".xlsx";
 if(!$sheetName)
  $sheetName = "Foglio 1";
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] == "root")
  $_FILE_PATH = "tmp/";
 else if($sessInfo['uid'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $_FILE_PATH = $_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
  $_FILE_PATH = "tmp/";

 if($cats)
  $catIds = explode(",",$cats);


 /* INIZIO ESPORTAZIONE */
 $GX = new GMartExcel($_AP, $sessid, $shellid);
 if(!$GX->init())
  return array('message'=>$GX->debug, 'error'=>$GX->errCode);

 if($entireArchive)
 {
  /* TODO: da fare */
 }
 else if(count($ids))
 {
  $GX->createSheet($sheetName ? $sheetName : "Foglio 1");
  if(!$GX->ExportElements($ids))
   return array('message'=>$GX->debug, 'error'=>$GX->errCode);
 }
 else if(count($catIds))
 {
  if(!$GX->ExportCategories($catIds, $includeSubCategories))
   return array('message'=>$GX->debug, 'error'=>$GX->errCode);  
 }

 $GX->writeToFile($_BASE_PATH.$_FILE_PATH.ltrim($fileName,"/"));
 $out = $GX->debug."done!\nExcel file: ".$fileName;
 $outArr = array('filename'=>$fileName, "fullpath"=>$_FILE_PATH.ltrim($fileName,"/"));
 /* EOF - ESPORTAZIONE */


 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
//--- GMART EXCEL CLASS ---------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
class GMartExcel
{
 var $AP;
 var $sessid;
 var $shellid;
 var $objPHPExcel;
 var $Sheets;
 var $letters;

 var $archiveInfo;
 var $ExcelFields;
 var $sheet;		// active sheet
 var $rowIdx;
 var $CAT_BY_ID;	// list of categories by id
 var $catCount;
 var $itemCount;

 var $debug;
 var $errCode;

 function GMartExcel($_AP='gmart', $sessid=0, $shellid=0)
 {
  $this->AP = $_AP;
  $this->sessid = $sessid;
  $this->shellid = $shellid;
  $this->Sheets = array();
  $this->archiveInfo = null;
  $this->objPHPExcel = null;
  $this->debug = "";
  $this->errCode = "";
  $this->CAT_BY_ID = array();
  $this->catCount = 0;
  $this->itemCount = 0;

  $this->sheet = null;		// active sheet
  $this->rowIdx = 1;		// current row index


  $this->letters = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
	"AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ");

  $this->ExcelFields = array(
	 0=> array('name'=>'code_str', 			'title'=>'CODICE', 			'format'=>'string'),
	 1=> array('name'=>'brand',				'title'=>'MARCA',			'format'=>'string'),
	 2=> array('name'=>'model',				'title'=>'MODELLO',			'format'=>'string'),
	 3=> array('name'=>'baseprice',			'title'=>'PR. BASE',		'format'=>'currency'),
	 4=> array('name'=>'units',				'title'=>'U.MIS',			'format'=>'string'),
	 5=> array('name'=>'vat',				'title'=>'ALIQ. IVA',		'format'=>'percentage'),
	 6=> array('name'=>'weight',			'title'=>'PESO',			'format'=>'number'),
	 7=> array('name'=>'weightunits',		'title'=>'U.M. PESO',		'format'=>'string'),
	 8=> array('name'=>'barcode',			'title'=>'BARCODE',			'format'=>'string'),
	 9=> array('name'=>'manufacturer_code',	'title'=>'COD. ART. PROD.',	'format'=>'string'),
	 10=> array('name'=>'item_location',	'title'=>'COLLOC. ART',		'format'=>'string'),
	 11=> array('name'=>'gebinde_code',		'title'=>'COD. CONF.',		'format'=>'string'),
	 12=> array('name'=>'gebinde',			'title'=>'DESCR. CONF.', 	'format'=>'string'),
	 13=> array('name'=>'division',			'title'=>'DIVIS. MAT.',		'format'=>'string'),
	 14=> array('name'=>'minimum_stock',	'title'=>'SCORTA MIN.',		'format'=>'number'),
	 15=> array('name'=>'storeqty',			'title'=>'GIAC. FISICA',	'format'=>'number'),
	 16=> array('name'=>'booked',			'title'=>'PRENOTATI',		'format'=>'number'),
	 17=> array('name'=>'incoming',			'title'=>'ORDINATI',		'format'=>'number'),
	 18=> array('name'=>'available',		'title'=>'DISPONIBILI',		'format'=>'number',	'notdbfield'=>true),
	 19=> array('name'=>'description',		'title'=>'BREVE DESCRIZ.',	'format'=>'string'),

	 20=> array('name'=>'vendorname_1',		'title'=>'FORNIT. 1',			'format'=>'string', 'notdbfield'=>true),
	 21=> array('name'=>'vencode_1',		'title'=>'COD. ART. FORN. 1',	'format'=>'string', 'notdbfield'=>true),
	 22=> array('name'=>'venprice_1',		'title'=>'PR. ACQ. FORN. 1',	'format'=>'currency', 'notdbfield'=>true),

	 23=> array('name'=>'vendorname_2',		'title'=>'FORNIT. 2',			'format'=>'string', 'notdbfield'=>true),
	 24=> array('name'=>'vencode_2',		'title'=>'COD. ART. FORN. 2',	'format'=>'string', 'notdbfield'=>true),
	 25=> array('name'=>'venprice_2',		'title'=>'PR. ACQ. FORN. 2',	'format'=>'currency', 'notdbfield'=>true),

	 26=> array('name'=>'vendorname_3',		'title'=>'FORNIT. 3',			'format'=>'string', 'notdbfield'=>true),
	 27=> array('name'=>'vencode_3',		'title'=>'COD. ART. FORN. 3',	'format'=>'string', 'notdbfield'=>true),
	 28=> array('name'=>'venprice_3',		'title'=>'PR. ACQ. FORN. 3',	'format'=>'currency', 'notdbfield'=>true)
	);
 }
 //----------------------------------------------------------------------------------------------//
 function init()
 {
  /* GET ARCHIVE INFO */
  $out = "Get archive informations...";
  $ret = GShell("dynarc archive-info -prefix '".$this->AP."'",$this->sessid,$this->shellid);
  if($ret['error']) return $this->returnError($out."failed!\n".$ret['message'], $ret['error']);
  $this->archiveInfo = $ret['outarr'];
  $out.= "done!\n";
  $this->debug.= $out;

  PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());
  $this->objPHPExcel = new PHPExcel();

  /* SET DEFAULT FONT */
  $this->objPHPExcel->getDefaultStyle()->getFont()
    ->setName('Arial')
    ->setSize(10);

  

  return true;
 }
 //----------------------------------------------------------------------------------------------//
 function returnError($message, $errcode)
 {
  $this->debug.= $message;
  $this->errCode = $errcode;
  return false;
 }
 //----------------------------------------------------------------------------------------------//
 function createSheet($sheetName)
 {
  $sheetName = htmlspecialchars_decode($sheetName,ENT_QUOTES);
  if(!count($this->Sheets))
   $sheet = $this->objPHPExcel->setActiveSheetIndex(0);
  else
  {
   $this->objPHPExcel->createSheet();
   $sheet = $this->objPHPExcel->setActiveSheetIndex(count($this->Sheets));
  }
  $this->objPHPExcel->getActiveSheet()->setTitle($sheetName);

  $this->Sheets[] = $sheet;
  $this->sheet = $sheet;		// set active sheet
  $this->rowIdx = 1;			// set current row index

  return $sheet; 
 }
 //----------------------------------------------------------------------------------------------//
 function prepareFields()
 {
  $fields = "id";
  for($c=0; $c < count($this->ExcelFields); $c++)
  {
   $xf = $this->ExcelFields[$c];
   switch($xf['name'])
   {
    default : {
	  if(!$xf['notdbfield'])
	   $fields.= ",".$xf['name'];
	 } break;
   }
  }
  return $fields;
 }
 //----------------------------------------------------------------------------------------------//
 function prepareCatList($ids, $getSubCat=false)
 {
  $db = new AlpaDatabase();
  for($c=0; $c < count($ids); $c++)
  {
   if($this->CAT_BY_ID[$ids[$c]])
	continue;
   $ret = $this->getCatInfo($ids[$c], $getSubCat, $db);
   if(!$ret) return false;
   $this->itemCount+= $ret['totitems_count'];
  }
  $db->Close();
  return true;
 }
 //----------------------------------------------------------------------------------------------//
 function getCatInfo($id, $getSubCat=false, $_db=null, $outArr=null)
 {
  $db = $_db ? $_db : new AlpaDatabase();

  $fields = "id,uid,gid,_mod,parent_id,code,name,subcat_count,items_count,totitems_count";

  if(!$outArr)
  {
   $db->RunQuery("SELECT ".$fields." FROM dynarc_".$this->AP."_categories WHERE id='".$id."'");
   if($db->Error) return $this->returnError("Unable to get cat #".$id.", MYSQL Error:".$db->Error, 'MYSQL_ERROR');
   $db->Read();
   $outArr = $db->record;
   $this->CAT_BY_ID[$id] = $outArr;
   $this->catCount++;
  }
 
  if($getSubCat)
  {
   if($outArr['subcat_count'])
   {
	$outArr['subcategories'] = array();
	$db->RunQuery("SELECT ".$fields." FROM dynarc_".$this->AP."_categories WHERE parent_id='".$id."' ORDER BY ordering ASC");
	if($db->Error) return $this->returnError("Unable to get subcategories of #".$id.", MYSQL Error:".$db->Error, 'MYSQL_ERROR');
	while($db->Read())
	{
	 $this->CAT_BY_ID[$db->record['id']] = $db->record;
	 $this->catCount++;
	 $ret = $this->getCatInfo($db->record['id'], true, $db, $db->record);
	 if(!$ret) return false;
	 $outArr['subcategories'][] = $ret;
	}

   }
  }

  if(!$_db) $db->Close();
  return $outArr;
 }
 //----------------------------------------------------------------------------------------------//
 function writeSheetHeaders()
 {
  for($c=0; $c < count($this->ExcelFields); $c++)
   $this->sheet->setCellValueByColumnAndRow($c, $this->rowIdx, $this->ExcelFields[$c]['title']);

  $this->rowIdx++;
 }
 //----------------------------------------------------------------------------------------------//
 function writeItem($data)
 {
  $colIdx = 0;
  for($c=0; $c < count($this->ExcelFields); $c++)
  {
   $xf = $this->ExcelFields[$c];
   if($xf['notdbfield'])
   {
	switch($xf['name'])
	{
	 case 'vendorname_1' : $value = (is_array($data['vendors']) && $data['vendors'][0]) ? $data['vendors'][0]['vendor_name'] : ""; break;
	 case 'vendorname_2' : $value = (is_array($data['vendors']) && $data['vendors'][1]) ? $data['vendors'][1]['vendor_name'] : ""; break;
	 case 'vendorname_3' : $value = (is_array($data['vendors']) && $data['vendors'][2]) ? $data['vendors'][2]['vendor_name'] : ""; break;

	 case 'vencode_1' : $value = (is_array($data['vendors']) && $data['vendors'][0]) ? $data['vendors'][0]['code'] : ""; break;
	 case 'vencode_2' : $value = (is_array($data['vendors']) && $data['vendors'][1]) ? $data['vendors'][1]['code'] : ""; break;
	 case 'vencode_3' : $value = (is_array($data['vendors']) && $data['vendors'][2]) ? $data['vendors'][2]['code'] : ""; break;

	 case 'venprice_1' : $value = (is_array($data['vendors']) && $data['vendors'][0]) ? $data['vendors'][0]['price'] : ""; break;
	 case 'venprice_2' : $value = (is_array($data['vendors']) && $data['vendors'][1]) ? $data['vendors'][1]['price'] : ""; break;
	 case 'venprice_3' : $value = (is_array($data['vendors']) && $data['vendors'][2]) ? $data['vendors'][2]['price'] : ""; break;

	 case 'available' : {
		 $sq = ($data['storeqty'] > 0) ? $data['storeqty'] : 0;
		 $b = $data['booked'] > 0 ? $data['booked'] : 0;
		 $i = $data['incoming'] > 0 ? $data['incoming'] : 0;
		 $value = ($sq+$i)-$b;
		} break;
	}
   } // EOF - notdbfield
   else
   {
	switch($xf['name'])
	{
	 default : $value = $data[$xf['name']]; break;
	}
   }

   $dataType = "";
   $formatCode = "";

   switch($xf['format'])
   {
	case 'datetime' : {
		 $dataType = PHPExcel_Cell_DataType::TYPE_STRING;
		 if($value && is_numeric($value))
		  $value = date($field['dateformat'] ? $field['dateformat'] : 'd/m/Y H:i', $value);
		 else if($value)
		  $value = date($field['dateformat'] ? $field['dateformat'] : 'd/m/Y H:i', strtotime($value));
		} break;

	case 'date' : {
		 $dataType = PHPExcel_Cell_DataType::TYPE_STRING;
		 if($value && is_numeric($value))
		  $value = date($field['dateformat'] ? $field['dateformat'] : 'd/m/Y', $value);
		 else if($value)
		  $value = date($field['dateformat'] ? $field['dateformat'] : 'd/m/Y', strtotime($value));
		} break;

	case 'time' : {
		 $dataType = PHPExcel_Cell_DataType::TYPE_STRING;
		 if($value && is_numeric($value))
		  $value = date($field['timeformat'] ? $field['timeformat'] : 'H:i', $value);
		 else if($value)
		  $value = date($field['timeformat'] ? $field['timeformat'] : 'H:i', strtotime($value));
		} break;

	case 'percentage' : {
		 if(!$value)
		  $value = "0";
		 else if(is_numeric($value) || (strpos($value, "%") === false))
		  $value = $value;
		} break;

	case 'number' : {
		 if($value < 0) $value = 0;
		 $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		} break;

	case 'currency' : {
		 $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		 $formatCode = "€ #,##0.00";
		} break;

	default : {
		 $dataType = PHPExcel_Cell_DataType::TYPE_STRING;
		 $value = html_entity_decode($value,ENT_QUOTES,'UTF-8');
		} break;

   } // EOF - switch data format

   if($dataType)
    $this->sheet->setCellValueExplicitByColumnAndRow($colIdx, $this->rowIdx, $value, $dataType);
   else
    $this->sheet->setCellValueByColumnAndRow($colIdx, $this->rowIdx, $value);
   if($formatCode)
    $this->sheet->getStyleByColumnAndRow($colIdx, $this->rowIdx)->getNumberFormat()->setFormatCode($formatCode);

   $colIdx++;
  } // EOF for


  $this->rowIdx++;
 }
 //----------------------------------------------------------------------------------------------//
 function ExportElements($ids)
 {
  if(!is_array($ids) || !count($ids))
   return $this->returnError("GMart Excel export error!You must specify at least one element to be export.","NO_ELEMENTS_SELECTED");

  $fields = $this->prepareFields();
  $this->writeSheetHeaders();

  $db = new AlpaDatabase();
  for($c=0; $c < count($ids); $c++)
  {
   $db->RunQuery("SELECT ".$fields." FROM dynarc_".$this->AP."_items WHERE id='".$ids[$c]."'");
   if($db->Error)
	return $this->returnError("GMart Excel export failed! MySQL Error: ".$db->Error, 'MYSQL_ERROR');
   if(!$db->Read())
	return $this->returnError("GMart Excel export failed! Item #".$ids[$c]." does not exists.", 'ITEM_DOES_NOT_EXISTS');
   $data = $db->record;

   // get vendors
   $db->RunQuery("SELECT code,vendor_id,vendor_name,ship_costs,price,vatrate FROM dynarc_".$this->AP."_vendorprices WHERE item_id='"
	.$data['id']."' ORDER BY id ASC LIMIT 3");
   if($db->Error) return $this->returnError("GMart Excel export failed! MySQL Error: ".$db->Error, 'MYSQL_ERROR');
   $data['vendors'] = array();
   while($db->Read())
   {
	$data['vendors'][] = $db->record;
   }

   $this->writeItem($data);
  }
  $db->Close();
  return true;
 }
 //----------------------------------------------------------------------------------------------//
 function ExportCategories($ids, $includeSubCategories=false)
 {
  if(!is_array($ids) || !count($ids))
   return $this->returnError("GMart Excel export error!You must specify at least one category to be export.","NO_CATEGORIES_SELECTED");
  

  // ESTIMATION
  $ret = $this->prepareCatList($ids, $includeSubCategories);
  if(!$ret) return false;

  reset($this->CAT_BY_ID);

  $estimate = $this->itemCount;
  $interface = array("name"=>"progressbar","steps"=>$estimate);
  gshPreOutput($this->shellid,count($ids)." categories will be exported (".$estimate." articles)", "ESTIMATION", "", "PASSTHRU", $interface);

  $fields = $this->prepareFields();

  $db = new AlpaDatabase();
  while(list($catId,$catInfo) = each($this->CAT_BY_ID))
  {
   if($catInfo['items_count'] > 0)
   {
    $this->createSheet($catInfo['name']);
    $this->writeSheetHeaders();
   
    $db->RunQuery("SELECT ".$fields." FROM dynarc_".$this->AP."_items WHERE cat_id='".$catId."' AND trash='0'");
    if($db->Error) return $this->returnError("GMart Excel export failed! MySQL Error: ".$db->Error, 'MYSQL_ERROR');
	$itemList = array();
    while($db->Read())
    {
	 $itemList[] = $db->record;
	}

    for($c=0; $c < count($itemList); $c++)
	{
	 $item = $itemList[$c];
	 // get vendors
	 $db->RunQuery("SELECT code,vendor_id,vendor_name,ship_costs,price,vatrate FROM dynarc_".$this->AP."_vendorprices WHERE item_id='"
		.$item['id']."' ORDER BY id ASC LIMIT 3");
	 if($db->Error) return $this->returnError("GMart Excel export failed! MySQL Error: ".$db->Error, 'MYSQL_ERROR');
	 $item['vendors'] = array();
	 while($db->Read())
	 {
	  $item['vendors'][] = $db->record;
	 }

	 gshPreOutput($this->shellid,"Export: ".$item['name']."...", "PROGRESS");
	 $this->writeItem($item);
    }
   }

  }
  $db->Close();

  return true;
 }
 //----------------------------------------------------------------------------------------------//
 //----------------------------------------------------------------------------------------------//
 //----------------------------------------------------------------------------------------------//
 function writeToFile($fileName)
 {
  $this->objPHPExcel->getProperties()->setCreator("");
  $this->objPHPExcel->getProperties()->setCreated("");
  $this->objPHPExcel->getProperties()->setModified("");
  $this->objPHPExcel->getProperties()->setLastModifiedBy("");
  $this->objPHPExcel->getProperties()->setTitle("");
  $this->objPHPExcel->getProperties()->setSubject("");
  $this->objPHPExcel->getProperties()->setDescription("");
  $this->objPHPExcel->getProperties()->setKeywords("");
  $this->objPHPExcel->getProperties()->setCategory("");
  $this->objPHPExcel->getProperties()->setCompany("");
  $this->objPHPExcel->getProperties()->setManager("");

  $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
  $objWriter->save($fileName);
  return true;
 }
 //----------------------------------------------------------------------------------------------//

}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
function gmart_cleanAllCustomPricing($args, $sessid, $shellid)
{
 $out = "";

 // Se non viene specificato alcun argomento verranno rimossi i prezzi imposti da tutti gli articoli di tutti i cataloghi
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$ap=$args[$c+1]; $c++;} break; // Rimuove tutti i prezzi imposti da questo catalogo
   case '-subjectid' : {$subjectId=$args[$c+1]; $c++;} break; // Rimuove tutti i prezzi imposti per questo cliente
   case '-subject' : {$subjectName=$args[$c+1]; $c++;} break; // Rimuove tutti i prezzi imposti per questo cliente
   case '-prodid' : {$prodId=$args[$c+1]; $c++;} break; // Rimuove tutti i prezzi imposti su questo prodotto
  }

 //--------------------------------------------------------------------------------------//
 if(!$ap)
 {
  $ret = GShell("dynarc archive-list -type gmart",$sessid,$shellid);
  $list = $ret['outarr'];
  $db = new AlpaDatabase();
  for($c=0; $c < count($list); $c++)
  {
   $db->RunQuery("TRUNCATE TABLE `dynarc_".$list[$c]['prefix']."_custompricing`");
   $out.= "Custom pricing into archive '".$list[$c]['name']."' has been cleaned.\n";
  }
  $db->Close();
  $out.= "done!";
  return array("message"=>$out);
 }
 //--------------------------------------------------------------------------------------//
 if($ap)
 {
  $db = new AlpaDatabase();
  $qry = "";
  if($subjectId)
   $qry.= " AND subject_id='".$subjectId."'";
  else if($subjectName)
   $qry.= " AND subject='".$db->Purify($subjectName)."'";
  if($prodId)
   $qry.= " AND item_id='".$prodId."'";

  if(!$qry)
   $db->RunQuery("TRUNCATE TABLE `dynarc_".$ap."_custompricing`");
  else
   $db->RunQuery("DELETE FROM dynarc_".$ap."_custompricing WHERE ".ltrim($qry, " AND "));
  $db->Close();
 }
 $out.= "done!";
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function gmart_fixPricing($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 $out = "";

 $ret = GShell("pricelists list",$sessid,$shellid);
 $pricelists = $ret['outarr'];
 $query = "";
 for($c=0; $c < count($pricelists); $c++)
 {
  $id = $pricelists[$c]['id'];
  $query.= ", ADD `pricelist_".$id."_discount` FLOAT NOT NULL, ADD `pricelist_".$id."_vendorprice` DECIMAL(10,4) NOT NULL, ADD `pricelist_"
	.$id."_cm` FLOAT NOT NULL";
 }
 $query = ltrim($query,",");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT archive_id FROM dynarc_archive_extensions WHERE extension_name='pricing'");
 while($db->Read())
 {
  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT name,tb_prefix FROM dynarc_archives WHERE id='".$db->record['archive_id']."'");
  if($db2->Read())
  {
   $_AP = $db2->record['tb_prefix'];
   $archName = $db2->record['name'];
   $db2->RunQuery("ALTER TABLE `dynarc_".$_AP."_items`".$query);
   if(!$db2->Error)	$out.= "Archive ".$archName." has been fixed!\n";
  }
  $db2->Close();
 }
 $db->Close();
 if(!$out)
  $out.= "All archives are affixed.";
 
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function gmart_updateCounters($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $_AP = "";
 $catId = 0;
 $catTag = "";
 $catInfo = null;

 $itemCount = 0;
 $subcatCount = 0;
 $totItemsCount = 0;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : { $_AP=$args[$c+1]; $c++;} break;
   case '-cat' : { $catId=$args[$c+1]; $c++;} break;
   case '-ct' : { $catTag=$args[$c+1]; $c++;} break;
  }
 
 if(!$_AP) return array('message'=>"GMart update counters failed!\nYou must specify the archive prefix", 'error'=>"INVALID_AP");
 if($catTag || $catId)
 {
  // Get category info
  $ret = GShell("dynarc cat-info -ap '".$_AP."'".($catId ? " -id '".$catId."'" : " -tag '".$catTag."'"), $sessid, $shellid);
  if($ret['error']) return array('message'=>"GMart update counters failed!\nUnable to get category info: ".$ret['message'], 'error'=>$ret['error']);
  $catInfo = $ret['outarr'];
  $catId = $catInfo['id'];
  $out.= "Update counters for category ".$catInfo['name']."\n";

  if($catInfo['parent_id'])
  {
   // get root cat
   $db = new AlpaDatabase();
   $catId = $catInfo['parent_id'];
   $parentId = $catInfo['parent_id'];
   while($parentId > 0)
   {
    $db->RunQuery("SELECT parent_id FROM dynarc_".$_AP."_categories WHERE id='".$parentId."'");
	if($db->Read())
	{
	 if($db->record['parent_id'])
	  $catId = $db->record['parent_id'];
	 $parentId = $db->record['parent_id'];
	}
	else
	 $parentId = 0;
   }
   $db->Close();
  }
 }


 $out.= "Items count: ";
 $db = new AlpaDatabase();
 // get items count into this category
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$_AP."_items WHERE cat_id='".$catId."' AND trash='0'");
 if($db->Error) return array('message'=>"GMart update counters failed!\nMySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
 $db->Read();
 $itemCount = $db->record[0];
 $totItemsCount = $db->record[0];
 $out.= ($itemCount ? $itemCount : '0')."\n";

 // get subcat count
 $out.= "Sub-categories count: ";
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$_AP."_categories WHERE parent_id='".$catId."' AND trash='0'");
 if($db->Error) return array('message'=>"GMart update counters failed!\nMySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
 $db->Read();
 $subcatCount = $db->record[0];
 $out.= ($subcatCount ? $subcatCount : '0')."\n";

 if($subcatCount)
 {
  $list = array();
  $ids = array();
  $db->RunQuery("SELECT id FROM dynarc_".$_AP."_categories WHERE parent_id='".$catId."' AND trash='0'");
  while($db->Read())
  {
   $ids[] = $db->record['id'];
  }

  for($c=0; $c < count($ids); $c++)
  {
   $ret = gmart_update_cat_counters($_AP, $ids[$c], $db);
   if($ret['error']) return $ret;
   if($ret['totitems_count'] > 0)
    $totItemsCount+= $ret['totitems_count'];
   $list[] = $ret;
  }
 }

 $out.= "Tot items: ".$totItemsCount."\n";
 if($catId)
 {
  // update info
  $db->RunQuery("UPDATE dynarc_".$_AP."_categories SET items_count='".$itemCount."',subcat_count='"
	.$subcatCount."',totitems_count='".$totItemsCount."' WHERE id='".$catId."'");
  if($db->Error) return array('message'=>"GMart update counters failed!\nMySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
 }

 $db->Close(); 

 

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gmart_update_cat_counters($_AP, $id, $db, $parent=null)
{
 $retInfo = array('id'=>$id, 'subcat_count'=>0, 'items_count'=>0, 'totitems_count'=>0, 'subcategories'=>array());

 $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$_AP."_items WHERE cat_id='".$id."' AND trash='0'");
 if($db->Error) return array('message'=>"GMart update counters failed!\nMySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
 $db->Read();
 $retInfo['items_count'] = $db->record[0];
 $retInfo['totitems_count'] = $db->record[0];

 // get subcat count
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_".$_AP."_categories WHERE parent_id='".$id."' AND trash='0'");
 if($db->Error) return array('message'=>"GMart update counters failed!\nMySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');
 $db->Read();
 $retInfo['subcat_count'] = $db->record[0];

 if($retInfo['subcat_count'])
 {
  $ids = array();
  $db->RunQuery("SELECT id FROM dynarc_".$_AP."_categories WHERE parent_id='".$id."' AND trash='0'");
  while($db->Read())
  {
   $ids[] = $db->record['id'];
  }
  for($c=0; $c < count($ids); $c++)
  {
   $ret = gmart_update_cat_counters($_AP, $ids[$c], $db, $retInfo);
   if($ret['error']) return $ret;
   $retInfo['totitems_count']+= $ret['totitems_count'];
   $retInfo['subcategories'][] = $ret;
  }
 }
 

 // update info
 $db->RunQuery("UPDATE dynarc_".$_AP."_categories SET items_count='".$retInfo['items_count']."',subcat_count='"
	.$retInfo['subcat_count']."',totitems_count='".$retInfo['totitems_count']."' WHERE id='".$id."'");
 if($db->Error) return array('message'=>"GMart update counters failed!\nMySQL Error: ".$db->Error, 'error'=>'MYSQL_ERROR');

 return $retInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function gmart_increment_counter($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $_AP = "";
 $catId = 0;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : { $_AP=$args[$c+1]; $c++;} break;
   case '-cat' : { $catId=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
  }
 
 if(!$_AP) return array('message'=>"GMart increment counters failed!\nYou must specify the archive prefix", 'error'=>"INVALID_AP");
 if(!$catId) return array('message'=>"done!");


 $db = new AlpaDatabase();
 $db->RunQuery("SELECT name,parent_id,items_count,totitems_count FROM dynarc_".$_AP."_categories WHERE id='".$catId."'");
 $db->Read();
 if($verbose)
  $out.= "Cat #".$catId." - ".$db->record['name']." (items count = ".($db->record['totitems_count']+1).")\n";

 $parentId = $db->record['parent_id'];
 $db->RunQuery("UPDATE dynarc_".$_AP."_categories SET items_count=items_count+1, totitems_count=totitems_count+1 WHERE id='".$catId."'");
 while($parentId)
 {
  $db->RunQuery("SELECT id,name,parent_id,items_count,totitems_count FROM dynarc_".$_AP."_categories WHERE id='".$parentId."'");
  $db->Read();

  if($verbose)
   $out.= "Cat #".$db->record['id']." - ".$db->record['name']." (items count = ".($db->record['totitems_count']+1).")\n";

  $parentId = $db->record['parent_id'];
  $db->RunQuery("UPDATE dynarc_".$_AP."_categories SET totitems_count=totitems_count+1 WHERE id='".$db->record['id']."'");
 }
 $db->Close();

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function gmart_decrement_counter($args, $sessid, $shellid)
{
 $out = "";
 $outArr = array();

 $_AP = "";
 $catId = 0;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : { $_AP=$args[$c+1]; $c++;} break;
   case '-cat' : { $catId=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
  }
 
 if(!$_AP) return array('message'=>"GMart decrement counters failed!\nYou must specify the archive prefix", 'error'=>"INVALID_AP");
 if(!$catId) return array('message'=>"done!");


 $db = new AlpaDatabase();
 $db->RunQuery("SELECT name,parent_id,items_count,totitems_count FROM dynarc_".$_AP."_categories WHERE id='".$catId."'");
 $db->Read();
 $count = $db->record['items_count']-1;
 $totcount = $db->record['totitems_count']-1;
 if($verbose)
  $out.= "Cat #".$catId." - ".$db->record['name']." (items count = ".(($count > 0) ? ($db->record['totitems_count']-1) : '0').")\n";

 $parentId = $db->record['parent_id'];
 
 $db->RunQuery("UPDATE dynarc_".$_AP."_categories SET items_count=".(($count > 0) ? "items_count-1" : "0").", totitems_count=".(($totcount > 0) ? "totitems_count-1" : "0")." WHERE id='".$catId."'");
 if($count >= 0)
 {
  while($parentId > 0)
  {
   $db->RunQuery("SELECT id,name,parent_id,items_count,totitems_count FROM dynarc_".$_AP."_categories WHERE id='".$parentId."'");
   $db->Read();

   $count = $db->record['items_count']-1;
   $totcount = $db->record['totitems_count']-1;

   if($verbose)
    $out.= "Cat #".$db->record['id']." - ".$db->record['name']." (items count = ".(($totcount > 0) ? ($db->record['totitems_count']-1) : '0').")\n";

   $parentId = $db->record['parent_id'];
   $db->RunQuery("UPDATE dynarc_".$_AP."_categories SET totitems_count=".(($totcount > 0) ? "totitems_count-1" : "0")." WHERE id='".$db->record['id']."'");
  }
 }

 $db->Close();

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function gmart_findProduct($args, $sessid=0, $shellid=0)
{
 $out = "";
 $outArr = array();

 $_AT = "gmart";
 $_AP = "";
 $_ARCHIVES = array();
 $retFields = "code_str,name";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : { $_AP=$args[$c+1]; $c++;} break;
   case '-code' : {$prodCode=$args[$c+1]; $c++;} break;
   case '-sku' : {$prodSKU=$args[$c+1]; $c++;} break;
   case '-skuref' : case '-skureferrer' : {$prodSKUreferrer=$args[$c+1]; $c++;} break;
   case '-asin' : {$prodASIN=$args[$c+1]; $c++;} break;
   case '-ean' : {$prodEAN=$args[$c+1]; $c++;} break;
   case '-gcid' : {$prodGCID=$args[$c+1]; $c++;} break;
   case '-gtin' : {$prodGTIN=$args[$c+1]; $c++;} break;
   case '-upc' : {$prodUPC=$args[$c+1]; $c++;} break;
   case '-name' : {$prodName=$args[$c+1]; $c++;} break;

   case '-retfields' : case '-ret-fields' : case '--ret-fields' : {$retFields=$args[$c+1]; $c++;} break; // return fields separated by comma (,)

   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 $_RET_FIELDS = explode(",",$retFields);

 $dbresult = null;
 /* SEARCH BY ASIN,EAN,GCID,GTIN or UPC */
 if($prodASIN || $prodEAN || $prodGCID || $prodGTIN || $prodUPC)
 {
  $db = new AlpaDatabase();
  $qry = "";
  if($prodASIN)		$qry.= " OR asin='".$prodASIN."'";
  if($prodEAN)		$qry.= " OR ean='".$prodEAN."'";
  if($prodGCID)		$qry.= " OR gcid='".$prodGCID."'";
  if($prodGTIN)		$qry.= " OR gtin='".$prodGTIN."'";
  if($prodUPC)		$qry.= " OR upc='".$prodUPC."'";
  
  $qry = "SELECT ref_ap,ref_id,variant_id,coltint,sizmis FROM product_spid WHERE ".ltrim($qry, " OR ")." AND trash='0' LIMIT 1";
  $db->RunQuery($qry); if($db->Read()) $dbresult = $db->record;
  $db->Close();
 }

 /* SEARCH BY SKU */
 if(!$dbresult && $prodSKU)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT ref_ap,ref_id,variant_id,coltint,sizmis FROM product_sku WHERE sku='".$prodSKU."'"
	.(isset($prodSKUreferrer) ? " AND referrer='".$prodSKUreferrer."'" : "")." AND trash='0' LIMIT 1");
  if($db->Read()) $dbresult = $db->record;
  $db->Close();
 }

 if($dbresult)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT ".$retFields." FROM dynarc_".$dbresult['ref_ap']."_items WHERE id='".$dbresult['ref_id']."'");
  $db->Read();
  $outArr = array("id"=>$dbresult['ref_id'], "ap"=>$dbresult['ref_ap'], "at"=>$_AT, 'variant_id'=>$dbresult['variant_id'], 'coltint'=>$dbresult['coltint'], 'sizmis'=>$dbresult['sizmis']);
  for($c=0; $c < count($_RET_FIELDS); $c++)
   $outArr[$_RET_FIELDS[$c]] = $db->record[$_RET_FIELDS[$c]];
  $db->Close();
 }
 else if($prodName || $prodCode)
 {
  /* SEARCH BY code or name */
  if($_AP) $_ARCHIVES[] = array("prefix"=>$_AP);
  else
  {
   $ret = GShell("dynarc archive-list -a -type '".$_AT."'",$sessid,$shellid);
   if($ret['error']) 
	return array('message'=>"gmart find-product failed! Unable to get archive list by type '".$_AT."'\nError: ".$ret['message'], 'error'=>$ret['error']);

   for($c=0; $c < count($ret['outarr']); $c++)
    $_ARCHIVES[] = $ret['outarr'][$c];
  }

  $_RESULTS = array();
  $db = new AlpaDatabase();

  $where="";
  if($prodCode)		$where = " OR code_str='".$prodCode."')";
  if($prodName)		$where = " OR (name='".$db->Purify($prodName)."' OR name LIKE '".$db->Purify($prodName)."%')";

  $where = ltrim($where," OR ");

  // Prepare query
  $finalQry = "";
  for($c=0; $c < count($_ARCHIVES); $c++)
  {
   $_AP = $_ARCHIVES[$c]['prefix'];
   $finalQry.= " UNION SELECT '".$_AP."' AS tb_prefix,id,".$retFields." FROM dynarc_".$_AP."_items WHERE trash='0' AND ".$where;
  }
  $finalQry = "SELECT * FROM (".ltrim($finalQry," UNION ").") AS qryelements ORDER BY id ASC LIMIT 10";

  // Run query
  $db->RunQuery($finalQry);
  if($db->Error) return array('message'=>"gmart find-product failed!\nMySQL Error: ".$db->Error."\nQRY: ".$db->lastQuery, 'error'=>'MYSQL_ERROR');
  while($db->Read())
  {
   $a = array("id"=>$db->record['id'], "ap"=>$db->record['tb_prefix'], "at"=>$_AT);
   for($c=0; $c < count($_RET_FIELDS); $c++)
    $a[$_RET_FIELDS[$c]] = $db->record[$_RET_FIELDS[$c]];
   $_RESULTS[] = $a;
  }
  $db->Close();
 
  if(!count($_RESULTS))
   return array('message'=>"No results found.");

  // Match results
  for($c=0; $c < count($_RESULTS); $c++)
  {
   if($c == 0)
   {
    $outArr = $_RESULTS[$c];
    $outArr['otherresults'] = array();
   }
   else
    $outArr['otherresults'][] = $_RESULTS[$c];   
  }
 } 
 else
  return array('message'=>"No results found.");


 // Verbose
 if($verbose)
 {
  $out = "AP: ".$outArr['ap']."\n";
  $out.= "ID: ".$outArr['id']."\n";
  $out.= "Code: ".$outArr['code_str']."\n";
  $out.= "Name: ".$outArr['name']."\n";
  if($outArr['variant_id'])
   $out.= "Variant: ".($outArr['coltint'] ? $outArr['coltint'] : $outArr['sizmis'])."\n";

  if(count($outArr['otherresults']))
  {
   $out.= "\n other results:\n";
   for($c=0; $c < count($outArr['otherresults']); $c++)
   {
	$out.= "#".($c+1)."\n";
	$out = " AP: ".$outArr['otherresults'][$c]['ap']."\n";
	$out.= " ID: ".$outArr['otherresults'][$c]['id']."\n";
	$out.= " Code: ".$outArr['otherresults'][$c]['code_str']."\n";
	$out.= " Name: ".$outArr['otherresults'][$c]['name']."\n";
	if($outArr['otherresults'][$c]['variant_id'])
	 $out.= " Variant: ".($outArr['otherresults'][$c]['coltint'] ? $outArr['otherresults'][$c]['coltint'] : $outArr['otherresults'][$c]['sizmis'])."\n";
    $out.= "\n";
   }
  }
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

