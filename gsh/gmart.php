<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-09-2013
 #PACKAGE: gmart
 #DESCRIPTION: GMart - Functions
 #VERSION: 2.0beta
 #CHANGELOG: 30-09-2013 : Bug fix bulkedit.
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

 for($c=0; $c < count($_ITEMS); $c++)
 {
  $itm = $_ITEMS[$c];
  $qry = "";
  if(isset($brand))
   $qry.= ",brand='".$brand."',name='".$brand." ".$db->Purify($itm['model'])."'";
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
 
 $out = "";
 $outArr = array();

 $_AP = "gmart";
 $ids = array();
 $catIds = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-id' : {$ids[] = $args[$c+1]; $c++;} break;
   case '-cat' : {$catIds[] = $args[$c+1]; $c++;} break;
   case '-cats' : case '-categories' : {$cats=$args[$c+1]; $c++;} break; // Le categorie devono essere divise da una (,) virgola.

   case '-f' : case '-file' : {$fileName=$args[$c+1]; $c++;} break;
   case '-t' : case '-title' : case '-sn' : {$sheetName=$args[$c+1]; $c++;} break;

   case '--entire-archive' : $entireArchive=true; break;
  }

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] == "root")
 {
  $basepath = $_BASE_PATH."tmp/";
  $fullbasepath = "tmp/";
 }
 else if($sessInfo['uid'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $basepath = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
  $fullbasepath = $_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
 {
  $basepath= $_BASE_PATH."tmp/";
  $fullbasepath = "tmp/";
 }

 if($cats)
  $catIds = explode(",",$cats);


 /* GET ARCHIVE INFO */
 $out.= "Get archive informations...";
 $ret = GShell("dynarc archive-info -prefix '".$_AP."'",$sessid,$shellid);
 if($ret['error'])
  return array("message"=>$out."failed!\n".$ret['message'], "error"=>$ret['error']);
 $archiveInfo = $ret['outarr'];
 $out.= "done!\n";

 if($entireArchive)
 {
  // export entire archive
  $out.= "I'm preparing to export the entire archive...";
 }
 else if(count($ids))
 {
  // export selected elements
  if(!$fileName)
   $fileName = "products";
  if(!$sheetName)
   $sheetName = "single elements";

  return gmart_exportToExcel_elements($args, $archiveInfo, $sessid, $shellid, $basepath, $fullbasepath, $fileName, $sheetName, $ids);
 }
 else if(count($catIds))
 {
  if(count($catIds) == 1)
  {
   // export single category
   $ret = GShell("dynarc cat-info -ap `".$_AP."` -id `".$catIds[0]."`",$sessid,$shellid);
   if($ret['error'])
	return array("message"=>$out."Error:".$ret['message'], "error"=>$ret['error']);
   $catInfo = $ret['outarr'];
   if(!$fileName)
	$fileName = $catInfo['name'].".xlsx";
   $out.= "Will be exported the entire category '".$catInfo['name']."'\n";
  }
  else
  {
   // export selected categories
   $fileName = "categories.xlsx";
   $out.= "Will be exported ".count($catIds)." categories.\n";
  }
  return gmart_exportToExcel_categories($args, $archiveInfo, $sessid, $shellid, $basepath, $fullbasepath, $fileName, $catIds);
 }


 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gmart_exportToExcel_elements($args, $archiveInfo, $sessid, $shellid, $basepath, $fullbasepath, $fileName, $sheetName, $ids)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
 require_once($_BASE_PATH."var/lib/excel.php");

 $out = "";
 $outArr = array();
 $_AP = $archiveInfo['prefix'];

 $out.= "Will be exported ".count($ids)." items.\n";

 /* Get pricelists */
 $ret = GShell("pricelists list",$sessid,$shellid);
 $_PRICELISTS = $ret['outarr'];

 $estimate = count($ids);
 $interface = array("name"=>"progressbar","steps"=>$estimate);
 gshPreOutput($shellid,count($ids)." will be exported...", "ESTIMATION", "", "PASSTHRU", $interface);

 PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );

 $objPHPExcel = new PHPExcel();
 $sheet = $objPHPExcel->setActiveSheetIndex(0);
 $objPHPExcel->getActiveSheet()->setTitle($sheetName);

 $_STANDARD_COLUMNS = array();
 $_STANDARD_COLUMNS[] = array("title"=>"Codice", "name"=>"code");
 $_STANDARD_COLUMNS[] = array("title"=>"Descrizione", "name"=>"name");
 $_STANDARD_COLUMNS[] = array("title"=>"Fornitore", "name"=>"vendorname");
 $_STANDARD_COLUMNS[] = array("title"=>"Prezzo di listino", "name"=>"vendorprice");
 $_STANDARD_COLUMNS[] = array("title"=>"% C&M", "name"=>"cm");
 $_STANDARD_COLUMNS[] = array("title"=>"Costo", "name"=>"costs");
 $_STANDARD_COLUMNS[] = array("title"=>"Aliq. IVA", "name"=>"vatrate");
 $_STANDARD_COLUMNS[] = array("title"=>"Costo + IVA", "name"=>"costvatincluded");
 $_STANDARD_COLUMNS[] = array("title"=>"Prezzo base", "name"=>"baseprice");

 $_PRICELIST_COLUMNS = array();
 $_PRICELIST_COLUMNS[] = array("title"=>"% Ricarico", "name"=>"markuprate");
 $_PRICELIST_COLUMNS[] = array("title"=>"% Sconto", "name"=>"discount");
 $_PRICELIST_COLUMNS[] = array("title"=>"Prezzo finale", "name"=>"finalprice");
 $_PRICELIST_COLUMNS[] = array("title"=>"% IVA", "name"=>"vatrate");
 $_PRICELIST_COLUMNS[] = array("title"=>"Pr. finale +IVA", "name"=>"finalpricevatincluded");

 $rowIdx = 1;
 $colIdx = 0;
 for($c=0; $c < count($_STANDARD_COLUMNS); $c++)
 {
  $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx+1, htmlspecialchars_decode($_STANDARD_COLUMNS[$c]['title'],ENT_QUOTES));
  $sheet->getStyleByColumnAndRow($colIdx, $rowIdx+1)->getFont()->setBold(true);
  $colIdx++;
 }
 for($c=0; $c < count($_PRICELISTS); $c++)
 {
  $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, htmlspecialchars_decode($_PRICELISTS[$c]['name'],ENT_QUOTES));
  $sheet->getStyleByColumnAndRow($colIdx, $rowIdx)->getFont()->setBold(true);
  for($i=0; $i < count($_PRICELIST_COLUMNS); $i++)
  {
   $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx+1, htmlspecialchars_decode($_PRICELIST_COLUMNS[$i]['title'],ENT_QUOTES));
   $sheet->getStyleByColumnAndRow($colIdx, $rowIdx+1)->getFont()->setBold(true);
   $colIdx++;
  }
 }
 $rowIdx++;
 

 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 for($c=0; $c < count($ids); $c++)
 {
  $id = $ids[$c];
  $pricelists = array();
  $firstPLID = 0;
  $rowIdx++;
  $colIdx = 0;
  $db->RunQuery("SELECT * FROM dynarc_".$_AP."_items WHERE id='".$id."'");
  $db->Read();

  if($db->record['pricelists'])
   $pricelists = explode(",",$db->record['pricelists']);
  if(count($pricelists))
   $firstPLID = $pricelists[0];

  for($i=0; $i < count($_STANDARD_COLUMNS); $i++)
  {
   $value = "";
   $dataType = "";
   $formatCode = "";
   switch($_STANDARD_COLUMNS[$i]['name'])
   {
	case 'code' : {
		 $value = $db->record['code_str'];
		 $dataType = PHPExcel_Cell_DataType::TYPE_STRING;
		} break;
	case 'name' : $value = $db->record['name']; break;
	case 'vatrate' : {
		 $value = $db->record['vat']."%";
		 //$dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		 //$formatCode = "0.00%";
		} break;
	case 'baseprice' : {
		 $value = $db->record['baseprice'];
		 $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		 $formatCode = "€ #,##0.00";
		} break;

    case 'vendorname' : {
		 $db2->RunQuery("SELECT vendor_name FROM dynarc_".$_AP."_vendorprices WHERE item_id='".$id."' ORDER BY id ASC LIMIT 1");
		 $db2->Read();
		 $value = $db2->record['vendor_name'];
		} break;

	case 'vendorprice' : {
		 // detect vendor price from the first pricelist //
		 if($firstPLID)
		  $value = $db->record['pricelist_'.$firstPLID.'_vendorprice'];
		 $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		 $formatCode = "€ #,##0.00";
		} break;

	case 'cm' : {
		 // detect costs and margins from the first pricelist //
		 if($firstPLID)
		  $value = $db->record['pricelist_'.$firstPLID.'_cm']."%";
		 //$dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		 //$formatCode = "0.00%";
		} break;

    case 'costs' : {
		 $vendorPrice = $firstPLID ? $db->record['pricelist_'.$firstPLID.'_vendorprice'] : 0;
		 $cm = $firstPLID  ? $db->record['pricelist_'.$firstPLID.'_cm'] : 0;
		 $costs = $vendorPrice ? $vendorPrice - (($vendorPrice/100)*$cm) : 0;
		 $value = $costs;
		 $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		 $formatCode = "€ #,##0.00";
		} break;

	case 'costvatincluded' : {
		 $vendorPrice = $firstPLID ? $db->record['pricelist_'.$firstPLID.'_vendorprice'] : 0;
		 $cm = $firstPLID  ? $db->record['pricelist_'.$firstPLID.'_cm'] : 0;
		 $costs = $vendorPrice ? $vendorPrice - (($vendorPrice/100)*$cm) : 0;
		 $vatRate = $db->record['vat'];
		 $costsVatIncluded = $costs ? $costs + (($costs/100)*$vatRate) : 0;
		 $value = $costsVatIncluded;
		 $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		 $formatCode = "€ #,##0.00";
		} break;


   }

   if($dataType)
    $sheet->setCellValueExplicitByColumnAndRow($colIdx, $rowIdx, htmlspecialchars_decode($value,ENT_QUOTES), $dataType);
   else
    $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, htmlspecialchars_decode($value,ENT_QUOTES));

   if($formatCode)
	$sheet->getStyleByColumnAndRow($colIdx, $rowIdx)->getNumberFormat()->setFormatCode($formatCode);

   $colIdx++;
  }

  for($i=0; $i < count($_PRICELISTS); $i++)
  {
   $plid = $_PRICELISTS[$i]['id'];
   // calculate prices
   $baseprice = $db->record['baseprice'];
   $mrate = $db->record['pricelist_'.$plid.'_mrate'];
   $discount = $db->record['pricelist_'.$plid.'_discount'];
   $finalPrice = $baseprice ? $baseprice + (($baseprice/100)*$mrate) : 0;
   $finalPrice = $finalPrice ? $finalPrice - (($finalPrice/100)*$discount) : 0;
   $vatRate = $db->record['pricelist_'.$plid.'_vat'];
   $finalPriceVatIncluded = $finalPrice ? $finalPrice + (($finalPrice/100)*$vatRate) : 0;

   for($j=0; $j < count($_PRICELIST_COLUMNS); $j++)
   {
    $value = "";
    $dataType = "";
    $formatCode = "";
	switch($_PRICELIST_COLUMNS[$j]['name'])
	{
	 case 'markuprate' : {$value = $db->record['pricelist_'.$plid.'_mrate']."%";} break;
	 case 'discount' : {$value = $db->record['pricelist_'.$plid.'_discount']."%";} break;

	 case 'finalprice' : {
		 $value = $finalPrice;
		 $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		 $formatCode = "€ #,##0.00";
		} break;

	 case 'vatrate' : {$value = $db->record['pricelist_'.$plid.'_vat']."%";} break;

	 case 'finalpricevatincluded' : {
		 $value = $finalPriceVatIncluded;
		 $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		 $formatCode = "€ #,##0.00";
		} break;
	}
	
    if($dataType)
     $sheet->setCellValueExplicitByColumnAndRow($colIdx, $rowIdx, htmlspecialchars_decode($value,ENT_QUOTES), $dataType);
    else
     $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, htmlspecialchars_decode($value,ENT_QUOTES));

    if($formatCode)
	 $sheet->getStyleByColumnAndRow($colIdx, $rowIdx)->getNumberFormat()->setFormatCode($formatCode);

	$colIdx++;
   }
  }
  gshPreOutput($shellid,$db->record['name']." has been exported.", "PROGRESS", $db->record['id']);
 }
 $db->Close();
 $db2->Close();

 /* WRITE TO FILE */
 $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
 $objWriter->save($basepath.ltrim($fileName,"/"));

 $out = "done!\nHTML Elements has been exported to Excel file: ".$fileName;
 $outArr = array("filename"=>$fileName, "fullpath"=>$fullbasepath.$fileName);

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gmart_exportToExcel_categories($args, $archiveInfo, $sessid, $shellid, $basepath, $fullbasepath, $fileName, $catIds)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
 require_once($_BASE_PATH."var/lib/excel.php");

 $out = "";
 $outArr = array();
 $_AP = $archiveInfo['prefix'];

 /* Get pricelists */
 $ret = GShell("pricelists list",$sessid,$shellid);
 $_PRICELISTS = $ret['outarr'];

 $estimate = count($catIds);
 $interface = array("name"=>"progressbar","steps"=>$estimate);
 gshPreOutput($shellid,count($ids)." will be exported...", "ESTIMATION", "", "PASSTHRU", $interface);

 $Excel = new GMartExcel($_AP, $sessid, $shellid);

 $_STANDARD_COLUMNS = array();
 $_STANDARD_COLUMNS[] = array("title"=>"Codice", "name"=>"code", "width"=>15, "format"=>"string", "background"=>"0000ff", "color"=>"ffffff");
 $_STANDARD_COLUMNS[] = array("title"=>"Descrizione", "name"=>"name", "width"=>30, "background"=>"0000ff", "color"=>"ffffff");
 $_STANDARD_COLUMNS[] = array("title"=>"Fornitore", "name"=>"vendorname", "width"=>20, "background"=>"0000ff", "color"=>"ffffff");
 $_STANDARD_COLUMNS[] = array("title"=>"Prezzo di listino", "name"=>"vendorprice", "width"=>15, "format"=>"currency", "background"=>"0000ff", "color"=>"ffffff");
 $_STANDARD_COLUMNS[] = array("title"=>"% C&M", "name"=>"cm", "format"=>"percentage", "width"=>10, "background"=>"0000ff", "color"=>"ffffff");
 $_STANDARD_COLUMNS[] = array("title"=>"Costo", "name"=>"costs", "format"=>"currency", "width"=>15, "background"=>"0000ff", "color"=>"ffffff");
 $_STANDARD_COLUMNS[] = array("title"=>"Aliq. IVA", "name"=>"vatrate", "format"=>"percentage", "width"=>10, "background"=>"0000ff", "color"=>"ffffff");
 $_STANDARD_COLUMNS[] = array("title"=>"Costo + IVA", "name"=>"costsvatincluded", "format"=>"currency", "width"=>15, "background"=>"0000ff", "color"=>"ffffff");
 $_STANDARD_COLUMNS[] = array("title"=>"Prezzo base", "name"=>"baseprice", "format"=>"currency", "width"=>15, "background"=>"0000ff", "color"=>"ffffff");

 $_PRICELIST_COLUMNS = array();
 $_PRICELIST_COLUMNS[] = array("title"=>"% Ricarico", "name"=>"markuprate", "format"=>"percentage", "width"=>10, "background"=>"0000ff", "color"=>"ffffff");
 $_PRICELIST_COLUMNS[] = array("title"=>"% Sconto", "name"=>"discount", "format"=>"percentage", "width"=>10, "background"=>"0000ff", "color"=>"ffffff");
 $_PRICELIST_COLUMNS[] = array("title"=>"Prezzo finale", "name"=>"finalprice", "format"=>"currency", "width"=>15, "background"=>"0000ff", "color"=>"ffffff");
 $_PRICELIST_COLUMNS[] = array("title"=>"% IVA", "name"=>"vatrate", "format"=>"percentage", "width"=>10, "background"=>"0000ff", "color"=>"ffffff");
 $_PRICELIST_COLUMNS[] = array("title"=>"Pr. finale +IVA", "name"=>"finalpricevatincluded", "format"=>"currency", "width"=>15, "background"=>"0000ff", "color"=>"ffffff");

 
 /* Create one sheet for each macro-category */
 for($c=0; $c < count($catIds); $c++)
 {
  $catId = $catIds[$c];
  $ret = GShell("dynarc cat-info -ap `".$_AP."` -id `".$catId."` --get-items-count",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $catInfo = $ret['outarr'];
  $Sheet = $Excel->createSheet($catInfo['name']);

  $Sheet->addColumnGroup("Categoria ".$catInfo['name'],$_STANDARD_COLUMNS);
  for($i=0; $i < count($_PRICELISTS); $i++)
   $Sheet->addColumnGroup($_PRICELISTS[$i]['name'],$_PRICELIST_COLUMNS,$_PRICELISTS[$i]['id']);

  if($catInfo['items_count'])
  {
   // First, paint items into this category //
   $Sheet->drawHeader();
   $Sheet->drawItems($catInfo['id']);
  }

  gshPreOutput($shellid,$catName." has been exported.", "PROGRESS", $catId);
 }
 

 $Excel->writeToFile($basepath.ltrim($fileName,"/"));

 $out = "done!\nHTML Elements has been exported to Excel file: ".$fileName;
 $outArr = array("filename"=>$fileName, "fullpath"=>$fullbasepath.$fileName);

 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
class GMartExcel
{
 var $AP;
 var $sessid;
 var $shellid;
 var $objPHPExcel;
 var $Sheets;

 function GMartExcel($_AP='gmart', $sessid=0, $shellid=0)
 {
  $this->AP = $_AP;
  $this->sessid = $sessid;
  $this->shellid = $shellid;
  $this->Sheets = array();

  PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());
  $this->objPHPExcel = new PHPExcel();

  /* SET DEFAULT FONT */
  $this->objPHPExcel->getDefaultStyle()->getFont()
    ->setName('Arial')
    ->setSize(10);
 }

 function createSheet($sheetName)
 {
  $sheetName = htmlspecialchars_decode($sheetName,ENT_QUOTES);
  $sheet = new GMartExcelSheet($this, $this->objPHPExcel, $sheetName);
  $this->Sheets[] = $sheet;
  return $sheet;
 }

 function writeToFile($fileName)
 {
  $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
  $objWriter->save($fileName);
 }
}
//-------------------------------------------------------------------------------------------------------------------//
class GMartExcelSheet
{
 var $SheetH;
 var $GMartExcelH;
 var $objPHPExcel;
 var $ROW_IDX;
 var $COL_IDX;
 var $HEADER_COLUMNS_GROUPS;

 function GMartExcelSheet($GMartExcelH,$objPHPExcel,$sheetName)
 {
  $this->GMartExcelH = $GMartExcelH;
  $this->objPHPExcel = $objPHPExcel;
  $sheetCount = count($this->GMartExcelH->Sheets);

  if(!$sheetName)
   $sheetName = "Sheet ".$sheetCount;

  if(!$sheetCount)
   $this->SheetH = $this->objPHPExcel->setActiveSheetIndex(0);
  else
  {
   $this->objPHPExcel->createSheet();
   $this->SheetH = $this->objPHPExcel->setActiveSheetIndex($sheetCount);
  }
  $this->objPHPExcel->getActiveSheet()->setTitle($sheetName);

  $this->ROW_IDX = 1;
  $this->COL_IDX = 0;

  $this->HEADER_COLUMNS_GROUPS = array();
 }

 function addColumnGroup($groupName="",$columns, $refId=0)
 {
  $this->HEADER_COLUMNS_GROUPS[] = array('name'=>$groupName, 'columns'=>$columns, 'refid'=>$refId);
 }

 function drawHeader()
 {
  $colIdx = 0;
  $rowIdx = $this->ROW_IDX;

  for($c=0; $c < count($this->HEADER_COLUMNS_GROUPS); $c++)
  {
   $groupName = $this->HEADER_COLUMNS_GROUPS[$c]['name'];
   $columns = $this->HEADER_COLUMNS_GROUPS[$c]['columns'];
   if($c>0)
	$colIdx++; /* Lasciamo una colonna di spazio tra un gruppo di colonne e l'altro */

   if($groupName)
   {
    $this->SheetH->setCellValueByColumnAndRow($colIdx, $rowIdx, $groupName);
    $this->SheetH->getStyleByColumnAndRow($colIdx, $rowIdx)->getFont()->setBold(true);
   }
   for($i=0; $i < count($columns); $i++)
   {
    $this->SheetH->setCellValueByColumnAndRow($colIdx, $rowIdx+1, $columns[$i]['title']);
	$styleArray = array(
    	'font'  => array(
        	'bold'  => true,
        	'color' => array('rgb' => $columns[$i]['color']),
        	'size'  => 10,
        	'name'  => 'Arial'
    	));
    $this->SheetH->getStyleByColumnAndRow($colIdx, $rowIdx+1)->applyFromArray($styleArray);
	if($columns[$i]['background'])
	{
	 $this->SheetH->getStyleByColumnAndRow($colIdx, $rowIdx+1)->getFill()->applyFromArray(array('type'=>PHPExcel_Style_Fill::FILL_SOLID, 'startcolor'=>array('rgb'=>$columns[$i]['background'])));
	}
	if($columns[$i]['width'])
	 $this->SheetH->getColumnDimensionByColumn($colIdx)->setWidth($columns[$i]['width']);

	$colIdx++;
   }
  }
  $this->ROW_IDX+=2;
 }

 function drawItems($catId)
 {
  $_AP = $this->GMartExcelH->AP;
  $rowIdx = $this->ROW_IDX;

  $db = new AlpaDatabase();
  $db->RunQuery("SELECT * FROM dynarc_".$_AP."_items WHERE cat_id='".$catId."' ORDER BY name ASC");
  while($db->Read())
  {
   $colIdx = 0;
   if($db->record['pricelists'])
    $pricelists = explode(",",$db->record['pricelists']);
   if(count($pricelists))
    $firstPLID = $pricelists[0];

   $code = $db->record['code_str'];
   $description = $db->record['name'];
   $vendorName = $this->getVendorName($db->record['id']);
   $vendorPrice = $firstPLID ? $db->record['pricelist_'.$firstPLID.'_vendorprice'] : 0;
   $cm = $firstPLID ? $db->record['pricelist_'.$firstPLID.'_cm'] : 0;
   $costs = $vendorPrice ? $vendorPrice - (($vendorPrice/100)*$cm) : 0;
   $vatRate = $db->record['vat'];
   $costsVatIncluded = $costs ? $costs + (($costs/100)*$vatRate) : 0;
   $basePrice = $db->record['baseprice'];

   for($g=0; $g < count($this->HEADER_COLUMNS_GROUPS); $g++)
   {
	if($g>0)
	 $colIdx++; /* Lasciamo una colonna di spazio tra un gruppo di colonne e l'altro */

	$columns = $this->HEADER_COLUMNS_GROUPS[$g]['columns'];
	$plid = $this->HEADER_COLUMNS_GROUPS[$g]['refid'];
	if($plid)
	{
     $mrate = $db->record['pricelist_'.$plid.'_mrate'];
     $discount = $db->record['pricelist_'.$plid.'_discount'];
     $finalPrice = $basePrice ? $basePrice + (($basePrice/100)*$mrate) : 0;
     $finalPrice = $finalPrice ? $finalPrice - (($finalPrice/100)*$discount) : 0;
     $plvatRate = $db->record['pricelist_'.$plid.'_vat'];
     $finalPriceVatIncluded = $finalPrice ? $finalPrice + (($finalPrice/100)*$plvatRate) : 0;
	}

    for($c=0; $c < count($columns); $c++)
	{
	 $col = $columns[$c];
	 $value = "";
	 $format = $col['format'];
	 if($plid)
	 {
	  // PRICELISTS
	  switch($col['name'])
	  {
	   case 'markuprate' : $value = $mrate; break;
	   case 'discount' : $value = $discount; break;
	   case 'finalprice' : $value = $finalPrice; break;
	   case 'vatrate' : $value = $plvatRate; break;
	   case 'finalpricevatincluded' : $value = $finalPriceVatIncluded; break;
	  }
	 }
	 else
	 {
	  // STANDARD COLUMNS
	  switch($col['name'])
	  {
	   case 'code' : $value = $code; break;
	   case 'name' : $value = $description; break;
	   case 'vendorname' : $value = $vendorName; break;
	   case 'vendorprice' : $value = $vendorPrice; break;
	   case 'cm' : $value = $cm; break;
	   case 'costs' : $value = $costs; break;
	   case 'vatrate' : $value = $vatRate; break;
	   case 'costsvatincluded' : $value = $costsVatIncluded; break;
	   case 'baseprice' : $value = $basePrice; break;
	  }
	 }

	 switch($format)
	 {
	  case 'string' : $this->SheetH->setCellValueExplicitByColumnAndRow($colIdx, $this->ROW_IDX, $value, PHPExcel_Cell_DataType::TYPE_STRING); break;
	  case 'currency' : {
		 $this->SheetH->setCellValueExplicitByColumnAndRow($colIdx, $this->ROW_IDX, $value, PHPExcel_Cell_DataType::TYPE_NUMERIC);
		 $this->SheetH->getStyleByColumnAndRow($colIdx, $this->ROW_IDX)->getNumberFormat()->setFormatCode("€ #,##0.00");
		} break;
	  case 'percentage' : $this->SheetH->setCellValueByColumnAndRow($colIdx, $this->ROW_IDX, $value."%"); break;
	  default : $this->SheetH->setCellValueByColumnAndRow($colIdx, $this->ROW_IDX, $value); break;
	 }

	 $colIdx++;
	}
   }


   $this->ROW_IDX++;
  }
  $db->Close();
 }

 function getVendorName($itemId)
 {
  $_AP = $this->GMartExcelH->AP;
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT vendor_name FROM dynarc_".$_AP."_vendorprices WHERE item_id='".$itemId."' ORDER BY id ASC LIMIT 1");
  $db->Read();
  $vendorName = $db->record['vendor_name'];
  $db->Close();
  return $vendorName;
 }

}
//-------------------------------------------------------------------------------------------------------------------//

