<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-06-2016
 #PACKAGE: excel-lib
 #DESCRIPTION: Some function for load and write Excel documents.
 #VERSION: 2.11-1.7.8
 #CHANGELOG: 21-06-2016 : Aggiornata funzione write. Possibilita di esportare piu tabelle in piu fogli.
			 12-02-2016 : Aggiornata funzione excel import.
			 11-12-2014 : Bug fix su funzione write.
			 06-10-2014 : Bug fix in format currency on function write.
			 29-09-2014 : Bug fix.
			 17-09-2014 : Aggiunto formats su funzione write
			 13-06-2014 : Aggiunto id su funzione import
			 22-02-2014 : Aggiunta funzione fast-export.
			 03-02-2014 : Bug fix su funzione excel_write.
			 22-05-2013 - Aggiunta funzione write. (da completare)
 #DEPENDS: htmltable2array
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

function shell_excel($args, $sessid, $shellid=null)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;
 switch($args[0])
 {
  case "info" : return excel_info($args, $sessid, $shellid); break;
  case "read" : return excel_read($args, $sessid, $shellid); break;
  case "write" : return excel_write($args, $sessid, $shellid); break;

  case "parsers" : case "parserlist" : case "parser-list" : return excel_parserList($args, $sessid, $shellid); break;
  case "parser-info" : return excel_parserInfo($args, $sessid, $shellid); break;  

  case "import" : return excel_import($args, $sessid, $shellid); break;
  case "fast-export" : return excel_fastExport($args, $sessid, $shellid); break;

  default : return excel_invalidArguments(); break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function excel_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function excel_info($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
 require_once($_BASE_PATH."var/lib/excel.php");

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-f' : case '-file' : {$fileName=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 if(!$fileName)
  return array('message'=>"You must specify the file name. (with: -file FILE_NAME)",'error'=>"INVALID_FILE");

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] == "root")
  $basepath = $_BASE_PATH;
 else if($sessInfo['uid'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $basepath = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
  $basepath= $_BASE_PATH."tmp/";
 $fileName = $basepath.ltrim($fileName,"/");

 if(!file_exists($fileName))
  return array('message'=>"File ".$fileName." does not exists", 'error'=>"FILE_DOES_NOT_EXISTS");

 gshPreOutput($shellid, "Loading file...");
 /* READ FILE */
 /** Include PHPExcel_IOFactory */
 require_once $_BASE_PATH."var/lib/excel/PHPExcel/IOFactory.php";
 $objPHPExcel = PHPExcel_IOFactory::load($fileName);

 gshPreOutput($shellid, "done!\nGet properties...");
 /* GET PROPERTIES */
 $outArr['creator'] = $objPHPExcel->getProperties()->getCreator();
 $outArr['ctime'] = $objPHPExcel->getProperties()->getCreated();
 $outArr['mtime'] = $objPHPExcel->getProperties()->getModified();
 $outArr['modifiedby'] = $objPHPExcel->getProperties()->getLastModifiedBy();
 $outArr['title'] = $objPHPExcel->getProperties()->getTitle();
 $outArr['subject'] = $objPHPExcel->getProperties()->getSubject();
 $outArr['desc'] = $objPHPExcel->getProperties()->getDescription();
 $outArr['keywords'] = $objPHPExcel->getProperties()->getKeywords();
 $outArr['extendedproperties'] = array("category"=>$objPHPExcel->getProperties()->getCategory(), "company"=>$objPHPExcel->getProperties()->getCompany(), "manager"=>$objPHPExcel->getProperties()->getManager());
 
 /* GET SHEETS */
 $sheetCount = $objPHPExcel->getSheetCount();
 $sheetNames = $objPHPExcel->getSheetNames();
 $sheets = $objPHPExcel->getAllSheets();

 gshPreOutput($shellid, "done!\n");
 if($verbose)
 {
  $out.= "Created by: ".$outArr['creator']."\n";
  $out.= "Created on - ".date('d-m-Y',$outArr['ctime'])." at ".date('H:i:s',$outArr['ctime'])."\n";
  $out.= "Last Modified by - ".$outArr['modifiedby']."\n";
  $out.= "Last Modified on - ".date('d-m-Y',$outArr['mtime'])." at ".date('H:i:s',$outArr['mtime'])."\n";
  $out.= "Title - ".$outArr['title']."\n";
  $out.= "Subject - ".$outArr['subject']."\n";
  $out.= "Description - ".$outArr['desc']."\n";
  $out.= "Keywords: - ".$outArr['keywords']."\n";
  $out.= "\nExtended (Application) Properties:\n";
  $out.= "Category - ".$outArr['extendedproperties']['category']."\n";
  $out.= "Company - ".$outArr['extendedproperties']['company']."\n";
  $out.= "Manager - ".$outArr['extendedproperties']['manager']."\n";
  $out.= "Num of sheet - ".$sheetCount."\n";

  $out.= "\nList of WorkSheets:\n";
  for($c=0; $c < count($sheetNames); $c++)
  {
   $sheet = $sheets[$c];
   $hiRow = $sheet->getHighestRow();
   $out.= ($c+1)." - ".$sheetNames[$c]." (".$hiRow." rows)\n";
  }
 }
 else
  $out.= "done!";

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function excel_read($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
 require_once($_BASE_PATH."var/lib/excel.php");

 $out = "";
 $outArr = array('items'=>array(), 'fields'=>array(), 'info'=>array());

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-f' : case '-file' : {$fileName=$args[$c+1]; $c++;} break;
   case '-s' : case '-sheet' : {$sheetName=$args[$c+1]; $c++;} break;
   case '-keys' : {$keys=$args[$c+1]; $c++;} break;
   case '-columns' : {$columns=$args[$c+1]; $c++;} break;
   case '-from' : {$from=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;
   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 if(!$fileName)
  return array('message'=>"You must specify the file name. (with: -file FILE_NAME)",'error'=>"INVALID_FILE");

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] == "root")
  $basepath = $_BASE_PATH;
 else if($sessInfo['uid'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $basepath = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
  $basepath= $_BASE_PATH."tmp/";
 $fileName = $basepath.ltrim($fileName,"/");

 if(!file_exists($fileName))
  return array('message'=>"File ".$fileName." does not exists", 'error'=>"FILE_DOES_NOT_EXISTS");

 $_KEYS = array();
 $_COLUMNS = array();

 if($keys)
 {
  if(strpos($keys,",") !== false)
   $_KEYS = explode(",",$keys);
  else
   $_KEYS[] = $keys;
 } 

 if($columns)
 {
  if(strpos($columns,",") !== false)
   $_COLUMNS = explode(",",$columns);
  else
   $_COLUMNS[] = $columns;
 } 

 /* READ FILE */
 /** Include PHPExcel_IOFactory */
 require_once $_BASE_PATH."var/lib/excel/PHPExcel/IOFactory.php";
 $objPHPExcel = PHPExcel_IOFactory::load($fileName);

 /* GET SHEET */
 $sheets = $objPHPExcel->getAllSheets();
 if(is_numeric($sheetName))
 {
  if(count($sheets) < $sheetName)
   return array('message'=>"Worksheet #".$sheetName." does not exists into this document.",'error'=>"INVALID_WORKSHEET");
  $sheet = $sheets[$sheetName];
 }
 else if($sheetName)
  $sheet = $objPHPExcel->getSheetByName($sheetName);
 else
  $sheet = $sheets[0];

 $outArr['info']['filename'] = basename($fileName);
 $outArr['info']['sheets'] = array();
 for($c=0; $c < count($sheets); $c++)
  $outArr['info']['sheets'][] = $sheets[$c]->getTitle();

 /* GET AND FORMAT COLUMNS */
 $fields = array();
 $columnIdx = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
 $maxRowIdx = $sheet->getHighestRow();
 if(!$limit || ($limit > $maxRowIdx))
  $limit = $maxRowIdx;

 $badchars = array("|","£","$","%","&","/","(",")","=","?","'","^","[","]","@","ç","#","°","§",",",";",":","<",">");
 $replaceChars = array("à","è","é","ì","ò","ù");
 $replaceWith = array("a","e","e","i","o","u");
 $replaceWithUndescore = array(".","_","-"," ");

 if(!count($_KEYS))
 {
  for($colIdx=0; $colIdx < $columnIdx; $colIdx++)
  {
   $columnLetter = PHPExcel_Cell::stringFromColumnIndex($colIdx);
   $fieldName = $sheet->getCell($columnLetter."1")->getValue();
   $fieldName = ltrim(rtrim(strtolower($fieldName)));
   $fieldName = str_replace($badchars,"",$fieldName);
   $fieldName = str_replace($replaceChars, $replaceWith, $fieldName);
   $fieldName = str_replace($replaceWithUndescore,"_",$fieldName);
   $fieldName = str_replace("__","_",$fieldName);
   $fieldName = rtrim($fieldName, "_");
   $fieldName = ltrim($fieldName, "_");
   if(!$fieldName)
    continue;

   $_KEYS[] = $fieldName;
   $_COLUMNS[] = $columnLetter;

   $outArr['fields'][] = array('letter'=>$columnLetter, 'name'=>$fieldName, 'value'=>$sheet->getCell($columnLetter."1")->getValue());
  }
 }

 for($rowIdx=($from ? $from : 1); $rowIdx < ($limit+1); $rowIdx++)
 {
  $a = array();
  for($c=0; $c < count($_KEYS); $c++)
  {
   //$a[$_KEYS[$c]] = $sheet->getCell(strtoupper($_COLUMNS[$c]).$rowIdx)->getFormattedValue();
   $a[$_KEYS[$c]] = $sheet->getCell(strtoupper($_COLUMNS[$c]).$rowIdx)->getValue();
  }
  $outArr['items'][] = $a;
 }

 $out.= "There are ".count($outArr['items'])." items\n";
 $out.= "Max rows = ".$maxRowIdx;

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function excel_write($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
 require_once($_BASE_PATH."var/lib/excel.php");

 $out = "";
 $outArr = array();
 $formats = array();

 $sheetNames = array();
 $htmlTables = array();
 $THrowIndexes = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-f' : case '-file' : {$fileName=$args[$c+1]; $c++;} break;

   /* XLS FILE INFORMATIONS */
   case '-title' : case '-prop-title' : {$propTitle=$args[$c+1]; $c++;} break;
   case '-author' : case '-creator' : case '-prop-creator' : case '-prop-author' : {$propAuthor=$args[$c+1]; $c++;} break;
   case '-subject' : case '-prop-subject' : {$propSubject=$args[$c+1]; $c++;} break;
   case '-description' : case '-prop-description' : case '-prop-desc' : {$propDescription=$args[$c+1]; $c++;} break;
   case '-keywords' : case '-prop-keywords' : {$propKeywords=$args[$c+1]; $c++;} break;
   case '-category' : case '-prop-category' : {$propCategory=$args[$c+1]; $c++;} break;

   case '-s' : case '-sheet' : {$sheetNames[]=$args[$c+1]; $c++;} break;
   case '-htmltable' : {$htmlTables[]=$args[$c+1]; $c++;} break;
   case '-throwidx' : {$THrowIndexes[]=$args[$c+1]; $c++;} break;

   case '-formats' : {$formats=$args[$c+1]; $c++;} break;

   case '-verbose' : case '--verbose' : $verbose=true; break;
  }

 if($formats) $formats = explode(",",$formats);
 if(!count($sheetNames)) $sheetNames[] = "Foglio 1";

 if(!$fileName)
  return array('message'=>"You must specify the file name. (with: -file FILE_NAME)",'error'=>"INVALID_FILE");

 $pi = pathinfo($fileName);
 if(!$pi['extension'])
  $fileName.= ".xlsx";

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] == "root")
  $basepath = $_BASE_PATH;
 else if($sessInfo['uid'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $basepath = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
  $basepath= $_BASE_PATH."tmp/";

 $letters = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
	"AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ");

 $objPHPExcel = new PHPExcel();
 
 for($j=0; $j < count($sheetNames); $j++)
 {
  $sheetName = $sheetNames[$j] ? substr(html_entity_decode($sheetNames[$j], ENT_QUOTES, 'UTF-8'), 0, 31) : "Foglio ".($j+1);
  $htmlTable = $htmlTables[$j];
  $THrowIdx = $THrowIndexes[$j] ? $THrowIndexes[$j] : 0;

  /* GET DATA FROM HTML TABLE */
  $ret = GShell("htmltable2array -c `".$htmlTable."` --strip-tags",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $rows = $ret['outarr'];
  
  if($j > 0)
   $objPHPExcel->createSheet();
  $sheet = $objPHPExcel->setActiveSheetIndex($j);
  $objPHPExcel->getActiveSheet()->setTitle($sheetName);

  for($c=0; $c < count($rows); $c++)
  {
   $rowIdx = $c+1;
   for($i=0; $i < count($rows[$c]['cells']); $i++)
   {
	$colIdx=$i;
    $dataType = "";
    $formatCode = "";
	$value = $rows[$c]['cells'][$i];

	if($formats[$i] && ($c>$THrowIdx))
	{
	 switch($formats[$i])
	 {
	  case 'datetime' : { $dataType = PHPExcel_Cell_DataType::TYPE_STRING; } break;
	  case 'date' : { $dataType = PHPExcel_Cell_DataType::TYPE_STRING; } break;
	  case 'time' : { $dataType = PHPExcel_Cell_DataType::TYPE_STRING; } break;
	  case 'percentage' : {
		 if(!$value)
		  $value = "0%";
		 else if(is_numeric($value) || (strpos($value, "%") === false))
		  $value = $value."%";
		} break;

	  case 'number' : {
		 if(is_numeric($value))
		  $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		 else
		  $dataType = PHPExcel_Cell_DataType::TYPE_STRING;
		} break;

	  case 'currency' : {
		 if(!is_numeric($value))
		 {
		  if(strpos($value,",") && strpos($value,"."))
		   $value = str_replace(".","",$value);
		  $value = str_replace(",",".",$value);
		 }
		 if(is_numeric($value))
		 {
		  $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		  $formatCode = "€ #,##0.00";
		 }
		 else
		  $dataType = PHPExcel_Cell_DataType::TYPE_STRING;
		} break;

	  default : { $dataType = PHPExcel_Cell_DataType::TYPE_STRING; } break;
	 } /* EOF SWITCH */
	}
    if($dataType) $sheet->setCellValueExplicitByColumnAndRow($colIdx, $rowIdx, html_entity_decode($value,ENT_QUOTES,'UTF-8'), $dataType);
    else $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, html_entity_decode($value,ENT_QUOTES,'UTF-8'));
    if($formatCode) $sheet->getStyleByColumnAndRow($colIdx, $rowIdx)->getNumberFormat()->setFormatCode($formatCode);
   } // EOF - i (columns)
  } // EOF - c (rows)

 } // EOF - j (sheets)

 $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
 $objWriter->save($basepath.ltrim($fileName,"/"));

 $out = "done!\nHTML Table has been exported to Excel file: ".$fileName;
 $outArr = array("filename"=>$fileName, "fullpath"=>$_USERS_HOMES.$db->record['homedir']."/".$fileName);
 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function excel_parserInfo($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;

 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-p' : {$parser=$args[$c+1]; $c++;} break;
   default : {if(!$parser) $parser=$args[$c]; } break;
  }

 if(!$parser)
  return array('message'=>'You must specify the parser', 'error'=>'INVALID_PARSER');
 
 if(!file_exists($_BASE_PATH."etc/excel_parsers/".$parser.".php"))
  return array('message'=>"Parser $parser does not exists","error"=>"PARSER_DOES_NOT_EXISTS");

 include_once($_BASE_PATH."etc/excel_parsers/".$parser.".php");
 if(is_callable("gnujikoexcelparser_".$parser."_info",true))
 {
  $outArr = call_user_func("gnujikoexcelparser_".$parser."_info", $sessid, $shellid);
  $outArr['name'] = $parser;
  $out.= "Parser name: ".$outArr['info']['name']."\n";
  $out.= "Num. of keys: ".count($outArr['keys']);
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function excel_parserList($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;

 $out = "";
 $outArr = array();

 $files = array();
 $dir = "etc/excel_parsers/";

 $d = dir($_BASE_PATH.$dir);
 while(FALSE !== ($entry = $d->read()))
 {
  if($entry == '.' || $entry == '..')
   continue;
  if(substr($entry, -1) == "~")
   continue;
  $Entry = rtrim($dir,'/').'/'.ltrim($entry,'/');
  if(is_dir($_BASE_PATH.$Entry)) // is a directory //
   continue;
  else // is a file //
  {
   $pos = strrpos($entry,".");
   if($pos === false)
    continue;
   $ext = substr($entry,$pos+1);
   if(strtolower($ext) != "php")
    continue;
   $files[] = substr($entry,0,strlen($entry)-strlen($ext)-1);
  }
 }

 if($files) 
  sort($files);

 for($c=0; $c < count($files); $c++)
 {
  $ret = GShell("excel parser-info `".$files[$c]."`",$sessid,$shellid);
  $outArr[] = $ret['outarr'];
  $out.= $ret['outarr']['info']['name']."\n"; 
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function excel_import($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_USERS_HOMES;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-f' : case '-file' : {$fileName=$args[$c+1]; $c++;} break;
   case '-p' : case '-parser' : {$parserName=$args[$c+1]; $c++;} break;
   case '-s' : case '-sheet' : {$sheetName=$args[$c+1]; $c++;} break;
   case '-keys' : {$keys=$args[$c+1]; $c++;} break;
   case '-columns' : {$columns=$args[$c+1]; $c++;} break;
   case '-from' : {$from=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;

   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;
   case '-cat' : {$_CAT=$args[$c+1]; $c++;} break;
   case '-ct' : {$_CT=$args[$c+1]; $c++;} break;
   case '-id' : {$_ID=$args[$c+1]; $c++;} break;

   case '-fast' : $fast=true; break;
  }

 if(!$fileName)
  return array('message'=>"You must specify the file name. (with: -file FILE_NAME)",'error'=>"INVALID_FILE");

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] == "root")
  $basepath = $_BASE_PATH;
 else if($sessInfo['uid'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $basepath = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
  $basepath= $_BASE_PATH."tmp/";
 $fileFullName = $basepath.ltrim($fileName,"/");

 if(!file_exists($fileFullName))
  return array('message'=>"File ".$fileFullName." does not exists", 'error'=>"FILE_DOES_NOT_EXISTS");
 if(!file_exists($_BASE_PATH."etc/excel_parsers/".$parserName.".php"))
   return array('message'=>"Parser ".$parserName." does not exists","error"=>"PARSER_DOES_NOT_EXISTS");

 /* Load file */
 $ret = GShell("excel read -f `".$fileName."` -keys `".$keys."` -columns `".$columns."`"
	.($sheetName ? " -sheet `".$sheetName."`" : "").($from ? " -from '".$from."'" : "").($limit ? " -limit '".$limit."'" : ""),$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $list = $ret['outarr']['items'];


 $retList = array();
 $xK = explode(",",$keys);
 // remove empty rows
 for($c=0; $c < count($list); $c++)
 {
  $ok = false;
  $itm = $list[$c];
  for($i=0; $i < count($xK); $i++)
  {
   if($itm[$xK[$i]])
   {
	$ok = true;
	break;
   }
  }
  if($ok)
   $retList[] = $itm;
 }
 $ret['outarr']['items'] = $retList;

 $out.= "There are ".count($list)." elements to import.\n";

 include_once($_BASE_PATH."etc/excel_parsers/".$parserName.".php");
 if($fast)
 {
  if(is_callable("gnujikoexcelparser_".$parserName."_fastimport",true))
   return call_user_func("gnujikoexcelparser_".$parserName."_fastimport", $xK , $ret['outarr'], $sessid, $shellid, $_AP, $_CAT, $_CT, $_ID, $sessInfo);
  else
   return array("message"=>"Unable to import! Function gnujikoexcelparser_".$parserName."_fastimport does not exists into file etc/excel_parsers/".$parserName.".php","error"=>"IMPORT_FAILED");
 }
 else
 {
  if(is_callable("gnujikoexcelparser_".$parserName."_import",true))
   return call_user_func("gnujikoexcelparser_".$parserName."_import", $ret['outarr'], $sessid, $shellid, $_AP, $_CAT, $_CT, $_ID);
  else
   return array("message"=>"Unable to import! Function gnujikoexcelparser_".$parserName."_import does not exists into file etc/excel_parsers/".$parserName.".php","error"=>"IMPORT_FAILED");
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function excel_fastExport($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
 require_once($_BASE_PATH."var/lib/excel.php");

 $out = "";
 $outArr = array();
 $sheetName = "untitled";
 $_FIELDS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-xmlfields' : {$xmlFields=$args[$c+1]; $c++;} break;// configurazione delle colonne in xml. (name,tag,format,width,....)
   case '-title' : {$title=$args[$c+1]; $c++;} break;
   case '-file' : case '-f' : case '-filename' : {$fileName=$args[$c+1]; $c++;} break;
   case '-sn' : case '-sheetname' : case '-sheet' : {$sheetName=substr($args[$c+1],0,32); $c++;} break;
   case '-cmd' : {$_CMD=$args[$c+1]; $c++;} break;  		// il comando da lanciare
   case '-resfield' : {$resField=$args[$c+1]; $c++;} break; // il nome dell'array dove si trovano i risultati. (di solito 'items')
  }

 if(!$fileName)
  return array('message'=>"You must specify the file name. (with: -file FILE_NAME)",'error'=>"INVALID_FILE");

 $xml = new GXML();
 if(!$xml->LoadFromString($xmlFields))
  return array('message'=>"XML Error: Unable to load xml field configuration", "error"=>"XML_ERROR");

 $fieldList = $xml->GetElementsByTagName('field');
 for($c=0; $c < count($fieldList); $c++)
 {
  $node = $fieldList[$c];
  $field = array("name"=>$node->getString('name'), "tag"=>$node->getString('tag'), "format"=>$node->getString('format'), 
	"retvalue"=>$node->getString('retvalue'), "alternatetag"=>$node->getString('alternatetag'), 
	"dateformat"=>$node->getString('dateformat'), "timeformat"=>$node->getString('timeformat'), 
	"ext"=>$node->getString('ext'), "retidx"=>$node->getString('retidx'));
  $options = $node->GetElementsByTagName("option");
  if(count($options))
  {
   for($i=0; $i < count($options); $i++)
   {
    $optnode = $options[$i];
	$field['options'][$optnode->getString('value')] = $optnode->getString('retvalue');
   }
  }
  $_FIELDS[] = $field;
 }
 

 $letters = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
	"AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ");

 $pi = pathinfo($fileName);
 if(!$pi['extension'])
  $fileName.= ".xlsx";

 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] == "root")
  $basepath = $_BASE_PATH;
 else if($sessInfo['uid'])
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT homedir FROM gnujiko_users WHERE id='".$sessInfo['uid']."'");
  $db->Read();
  $basepath = $_BASE_PATH.$_USERS_HOMES.$db->record['homedir']."/";
  $db->Close();
 }
 else
  $basepath= $_BASE_PATH."tmp/";

 $interface = array("name"=>"progressbar","steps"=>2);
 gshPreOutput($shellid,"Inizializzazione...", "ESTIMATION", "", "PASSTHRU", $interface);

 /* EXEC - COMMAND */
 $ret = GShell($_CMD,$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $_RESULTS = $resField ? $ret['outarr'][$resField] : $ret['outarr'];

 $estimate = count($_RESULTS);
 $interface = array("name"=>"progressbar","steps"=>$estimate);
 gshPreOutput($shellid,"Esportazione in corso...", "ESTIMATION", "", "", $interface);


 /* GENERATE EXCEL FILE */
 PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );
 $objPHPExcel = new PHPExcel();
 $sheet = $objPHPExcel->setActiveSheetIndex(0);
 $objPHPExcel->getActiveSheet()->setTitle($sheetName);

 // write header
 $rowIdx = 1;
 for($c=0; $c < count($_FIELDS); $c++)
 {
  $field = $_FIELDS[$c];
  $sheet->setCellValue($letters[$c].$rowIdx, htmlspecialchars_decode($field['name'], ENT_QUOTES));
  $sheet->getStyleByColumnAndRow($c, $rowIdx)->getFont()->setBold(true);
  $sheet->getStyleByColumnAndRow($c, $rowIdx)->getFont()->setSize(10);
  $sheet->getStyleByColumnAndRow($c, $rowIdx)->getFont()->setName("Arial");
 }

 // write results
 for($c=0; $c < count($_RESULTS); $c++)
 {
  gshPreOutput($shellid,"Esportazione ".($c+1)." di ".$estimate, "PROGRESS");
  $rowIdx++;
  $item = $_RESULTS[$c];
  for($i=0; $i < count($_FIELDS); $i++)
  {
   $colIdx=$i;
   $field = $_FIELDS[$i];
   if($field['ext'])
   {
	$ext = $field['ext'];
	$extRetIdx = $field['retidx'] ? $field['retidx'] : 0;
    $value = $item[$ext][$extRetIdx][$field['tag']];
	if(!$value && $field['alternatetag'])
	 $value = $item[$ext][$extRetIdx][$field['alternatetag']];
   }
   else
    $value = $item[$field['tag']];
   if(!$value && ($field['alternatetag']))
    $value = $item[$field['alternatetag']];

   if($field['retvalue'] == "option")
    $value = $field['options'][$value];

   $dataType = "";
   $formatCode = "";

   switch($field['format'])
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
		  $value = "0%";
		 else if(is_numeric($value) || (strpos($value, "%") === false))
		  $value = $value."%";
		} break;

	case 'number' : {
		 $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		} break;

	case 'currency' : {
		 $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		 $formatCode = "€ #,##0.00";
		} break;

	default : {
		 $dataType = PHPExcel_Cell_DataType::TYPE_STRING;
		} break;

   }
   
   if($dataType)
    $sheet->setCellValueExplicitByColumnAndRow($colIdx, $rowIdx, html_entity_decode($value,ENT_QUOTES,'UTF-8'), $dataType);
   else
    $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, html_entity_decode($value,ENT_QUOTES,'UTF-8'));
   if($formatCode)
    $sheet->getStyleByColumnAndRow($colIdx, $rowIdx)->getNumberFormat()->setFormatCode($formatCode);
   // set font and size
   $sheet->getStyleByColumnAndRow($colIdx, $rowIdx)->getFont()->setSize(10);
   $sheet->getStyleByColumnAndRow($colIdx, $rowIdx)->getFont()->setName("Arial");
  }
 }

 $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
 $objWriter->save($basepath.ltrim($fileName,"/"));

 $out = "done!\nExcel file: ".$fileName;
 $outArr = array("filename"=>$fileName, "fullpath"=>$_USERS_HOMES.$db->record['homedir']."/".$fileName);

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//


