<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-12-2016
 #PACKAGE: bookkeeping
 #DESCRIPTION: Official Gnujiko Petty Cash Book manager.
 #VERSION: 2.8beta
 #CHANGELOG: 17-12-2016 : Re bug-fix sui totali nella funzione pettycashbook_list filtrando per risorsa.
			 21-10-2016 : Bug fix sui totali nella funzione pettycashbook_list filtrando per risorsa.
			 19-08-2016 : Bug fix sui totali nella funzione pettycashbook_list.
			 30-01-2016 : Aggiunta funzione export-to-excel
			 14-01-2015 : Aggiunta funzione reset.
			 11-04-2014 : Bug fix vari.
			 10-10-2013 : Possibilità di filtrare anche per risorsa.
			 19-09-2013 : Bug fix nella funzione pettycashbook list. 
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_pettycashbook($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'new' : case 'add' : return pettycashbook_new($args, $sessid, $shellid); break;
  case 'edit' : return pettycashbook_edit($args, $sessid, $shellid); break;
  case 'delete' : return pettycashbook_delete($args, $sessid, $shellid); break;
  case 'list' : return pettycashbook_list($args, $sessid, $shellid); break;
  case 'export-to-excel' : return pettycashbook_exportToExcel($args, $sessid, $shellid); break;

  case 'reset' : return pettycashbook_reset($args, $sessid, $shellid); break;

  default : return pettycashbook_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function pettycashbook_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function pettycashbook_new($args, $sessid, $shellid)
{

}
//-------------------------------------------------------------------------------------------------------------------//
function pettycashbook_edit($args, $sessid, $shellid)
{
}
//-------------------------------------------------------------------------------------------------------------------//
function pettycashbook_delete($args, $sessid, $shellid)
{
 $archivePrefix = "pettycashbook";
 $out = "";
 $outArr = array();
 $_IDS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$_IDS[]=$args[$c+1]; $c++;} break;
  }

 for($c=0; $c < count($_IDS); $c++)
 {
  $ret = GShell("dynarc delete-item -ap `".$archivePrefix."` -id `".$_IDS[$c]."`",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $outArr['removed'][] = $ret['outarr'];
 }
 
 $out.= "done!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function pettycashbook_list($args, $sessid, $shellid)
{
 $archivePrefix = "pettycashbook";
 $orderBy = "ctime DESC";
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$archivePrefix=$args[$c+1]; $c++;} break; // The default archive prefix is 'pettycashbook' //

   case '-from' : {$from=strtotime($args[$c+1]); $c++;} break;
   case '-to' : {$to=strtotime($args[$c+1]); $c++;} break;
   case '-subject' : {$subject=$args[$c+1]; $c++;} break;
   case '-subjectid' : {$subjectId=$args[$c+1]; $c++;} break;
   case '-desc' : case '-description' : {$description=$args[$c+1]; $c++;} break;
   case '-cat' : case '-catid' : {$catId=$args[$c+1]; $c++;} break;
   case '-filter' : {$filter=$args[$c+1]; $c++;} break; // must be: in, out or transfers //
   case '-resid' : {$resId=$args[$c+1]; $c++;} break; // resource id, in or out //
   case '-resin' : {$resIn=$args[$c+1]; $c++;} break;
   case '-resout' : {$resOut=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;

   case '--get-totals' : $getTotals=true; break;
  }

 $where = "";
 switch($filter)
 {
  case 'in' : $where.= " AND incomes>0 AND expenses=0"; break;
  case 'out' : $where.= " AND expenses>0 AND incomes=0"; break;
  case 'transfers' : $where.= " AND res_in!=0 AND res_out!=0"; break;
 }
 
 if($from)
  $where.= " AND ctime>='".date('Y-m-d H:i',$from)."'";
 if($to)
  $where.= " AND ctime<'".date('Y-m-d H:i',$to)."'";
 if($resId)
  $where.= " AND (res_in='".$resId."' OR res_out='".$resId."')";
 if($subjectId)
  $where.= " AND subject_id='".$subjectId."'";
 else if($subject)
 {
  $where.= " AND (subject_name='".$subject."' OR subject_name LIKE '".$subject."%' OR subject_name LIKE '%"
	.$subject."' OR subject_name LIKE '%".$subject."%')";
 }
 else if($description)
 {
  $where.= " AND (name='".$description."' OR name LIKE '".$description."%' OR name LIKE '%"
	.$description."' OR name LIKE '%".$description."%')";
 }


 // MAKE QUERY //
 $ret = GShell("dynarc item-list -ap `".$archivePrefix."`".($catId ? " -cat `".$catId."`" : " --all-cat")
	.($where ? " -where `".ltrim($where, " AND ")."`" : "")." -extget pettycashbook --order-by `".$orderBy."`"
	.($limit ? " -limit ".$limit : "")." --return-serp-info -gettotals 'incomes,expenses'", $sessid, $shellid);

 $out = $ret['message'];
 $outArr = $ret['outarr'];

 $outArr['tot_incomes'] = $outArr['totals']['incomes'] ? $outArr['totals']['incomes'] : 0;		// transfers included
 $outArr['tot_expenses'] = $outArr['totals']['expenses'] ? $outArr['totals']['expenses'] : 0;	// transfers included

 // RE-MAKE QUERY for get transfers only
 $ret = GShell("dynarc item-list -ap `".$archivePrefix."`".($catId ? " -cat `".$catId."`" : " --all-cat")." -where `incomes=expenses"
	.$where."` -limit 1 -gettotals 'incomes,expenses'", $sessid, $shellid);
 $outArr['tot_transfers'] = (is_array($ret['outarr']['totals']) && $ret['outarr']['totals']['incomes']) ? $ret['outarr']['totals']['incomes'] : 0;
 

 if(/*($filter == "transfers") &&*/ $resId)
 {
  // Aggiusta il totale entrate ed il totale uscite in base alla risorsa selezionata.
  $where = str_replace(" AND (res_in='".$resId."' OR res_out='".$resId."')", "", $where);
  $ret = GShell("dynarc item-list -ap `".$archivePrefix."`".($catId ? " -cat `".$catId."`" : " --all-cat")." -where `res_in='".$resId."' AND res_out>0"
	.$where."` -limit 1 -gettotals 'expenses'", $sessid, $shellid);
  $outArr['tot_expenses']-= $ret['outarr']['totals']['expenses'];

  $ret = GShell("dynarc item-list -ap `".$archivePrefix."`".($catId ? " -cat `".$catId."`" : " --all-cat")." -where `res_out='".$resId."' AND res_in>0"
	.$where."` -limit 1 -gettotals 'incomes'", $sessid, $shellid);
  $outArr['tot_incomes']-= $ret['outarr']['totals']['incomes'];
 }
 

 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function pettycashbook_exportToExcel($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
 require_once($_BASE_PATH."var/lib/excel.php");

 $sheetName = "untitled";
 $letters = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
	"AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ");

 $fileName = "";
 $_FILE_PATH = "";

 $_AP = "pettycashbook";
 $orderBy = "ctime DESC";
 $out = "";
 $outArr = array();

 $_FIELDS = "name,ctime,res_in,res_out,incomes,expenses,doc_ap,doc_id,doc_ref,subject_id,subject_name";
 $_EXCEL_FIELDS = array(
	 0=> array('name'=>'ctime',			'title'=>'DATA',		 'format'=>'date', 		'visibled'=>true),
	 1=> array('name'=>'subject_name',	'title'=>'SOGGETTO',	 'format'=>'string', 	'visibled'=>true),
	 2=> array('name'=>'name',			'title'=>'DESCRIZIONE',	 'format'=>'string', 	'visibled'=>true),
	 3=> array('name'=>'incomes',		'title'=>'ENTRATE',		 'format'=>'currency',	'total'=>0,	'showtotal'=>true, 'visibled'=>true),
	 4=> array('name'=>'expenses',		'title'=>'USCITE',		 'format'=>'currency',	'total'=>0, 'showtotal'=>true, 'visibled'=>true),
	 5=> array('name'=>'docref',		'title'=>'DOC. DI RIF:', 'format'=>'string',	'notdbfield'=>true, 'visibled'=>true),
	);

 $_RES_BY_ID = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break; // The default archive prefix is 'pettycashbook' //

   case '-from' : {$from=strtotime($args[$c+1]); $c++;} break;
   case '-to' : {$to=strtotime($args[$c+1]); $c++;} break;
   case '-subject' : {$subject=$args[$c+1]; $c++;} break;
   case '-subjectid' : {$subjectId=$args[$c+1]; $c++;} break;
   case '-desc' : case '-description' : {$description=$args[$c+1]; $c++;} break;
   case '-cat' : case '-catid' : {$catId=$args[$c+1]; $c++;} break;
   case '-filter' : {$filter=$args[$c+1]; $c++;} break; // must be: in, out or transfers //
   case '-resid' : {$resId=$args[$c+1]; $c++;} break; // resource id, in or out //
   case '-resin' : {$resIn=$args[$c+1]; $c++;} break;
   case '-resout' : {$resOut=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;

   case '-f' : case '-file' : {$fileName=$args[$c+1]; $c++;} break;
   case '-s' : case '-sheet' : {$sheetName=substr($args[$c+1],0,32); $c++;} break;

   case '--get-totals' : $getTotals=true; break;
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

 //----------------------------------------------------------------------------------------------//

 $where = "trash=0";
 switch($filter)
 {
  case 'in' : 			{ $where.= " AND incomes>0 AND expenses=0"; $_EXCEL_FIELDS[4]['visibled'] = false; } break;
  case 'out' : 			{ $where.= " AND expenses>0 AND incomes=0"; $_EXCEL_FIELDS[3]['visibled'] = false; } break;
  case 'transfers' : 	$where.= " AND res_in!=0 AND res_out!=0"; break;
 }
 
 if($catId)				$where.= " AND cat_id='".$catId."'";
 if($from) 				$where.= " AND ctime>='".date('Y-m-d H:i',$from)."'";
 if($to)				$where.= " AND ctime<'".date('Y-m-d H:i',$to)."'";
 if($resId)				$where.= " AND (res_in='".$resId."' OR res_out='".$resId."')";
 if($subjectId)			$where.= " AND subject_id='".$subjectId."'";
 else if($subject)
 {
  $where.= " AND (subject_name='".$subject."' OR subject_name LIKE '".$subject."%' OR subject_name LIKE '%"
	.$subject."' OR subject_name LIKE '%".$subject."%')";
 }
 else if($description)
 {
  $where.= " AND (name='".$description."' OR name LIKE '".$description."%' OR name LIKE '%"
	.$description."' OR name LIKE '%".$description."%')";
 }

 //----------------------------------------------------------------------------------------------//

 // GET LIST OF RESOURCES
 if($resId)
 {
  // get only the selected resource info
  $ret = GShell("cashresources info -id '".$resId."'",$sessid,$shellid);
  if($ret['error']) return array('message'=>"Export to Excel failed!\n".$ret['message'], 'error'=>$ret['error']);
  $_RES_BY_ID[$ret['outarr']['id']] = $ret['outarr'];
 }
 else
 {
  // get list of all resources
  $ret = GShell("cashresources list",$sessid,$shellid);
  if($ret['error']) return array('message'=>"Export to Excel failed!\n".$ret['message'], 'error'=>$ret['error']);
  for($c=0; $c < count($ret['outarr']); $c++)
   $_RES_BY_ID[$ret['outarr'][$c]['id']] = $ret['outarr'][$c];
 }


 // MAKE QUERY
 $db = new AlpaDatabase();
 $db2 = new AlpaDatabase();
 $db->RunQuery("SELECT ".$_FIELDS." FROM dynarc_".$_AP."_items WHERE ".$where." ORDER BY ".$orderBy.($limit ? " LIMIT ".$limit : ""));
 if($db->Error) return array('message'=>"Export to Excel failed! MySQLError:\n".$db->Error, 'error'=>'MYSQL_ERROR');

 /* PREPARE EXCEL FILE */
 PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );
 $objPHPExcel = new PHPExcel();
 $sheet = $objPHPExcel->setActiveSheetIndex(0);
 $objPHPExcel->getActiveSheet()->setTitle($sheetName);
 $rowIdx = 1;

 $colIdx = 0;
 for($c=0; $c < count($_EXCEL_FIELDS); $c++)
 {
  if(!$_EXCEL_FIELDS[$c]['visibled']) continue;
  $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, $_EXCEL_FIELDS[$c]['title']);
  $colIdx++;
 }

 $rowIdx++;

 while($db->Read())
 {
  $ret = pettycashbook_exportToExcel_singleElement($db->record, $sheet, $_EXCEL_FIELDS, $_RES_BY_ID, $rowIdx, $db2);
  for($c=0; $c < count($_EXCEL_FIELDS); $c++)
  {
   $f = $_EXCEL_FIELDS[$c];
   if(!$f['showtotal']) continue;
   $_EXCEL_FIELDS[$c]['total']+= $ret[$f['name']];
  }
  $rowIdx++;
 }

 $db2->Close();
 $db->Close();

 // show totals
 $rowIdx+= 1;
 $colIdx=0;
 $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
 for($c=0; $c < count($_EXCEL_FIELDS); $c++)
 {
  $f = $_EXCEL_FIELDS[$c];
  if(!$f['visibled']) continue;
  if(!$f['showtotal'])
  {
   $colIdx++;
   continue;
  }
  if($f['format'] == "currency")
   $formatCode = "€ #,##0.00";
  else
   $formatCode = "";

  $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, "TOT. ".$f['title']);
  $sheet->setCellValueExplicitByColumnAndRow($colIdx, $rowIdx+1, $f['total'], $dataType);
  if($formatCode)
   $sheet->getStyleByColumnAndRow($colIdx, $rowIdx+1)->getNumberFormat()->setFormatCode($formatCode);

  $colIdx++;
 }


 $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
 $objWriter->save($_BASE_PATH.$_FILE_PATH.ltrim($fileName,"/"));

 $out = "done!\nExcel file: ".$fileName;
 $outArr = array('filename'=>$fileName, "fullpath"=>$_FILE_PATH.ltrim($fileName,"/"));

 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function pettycashbook_exportToExcel_singleElement($data, $sheet, $fields, $_RES_BY_ID, $rowIdx, $db2)
{
 $ret = array();
 $colIdx = 0;
 for($c=0; $c < count($fields); $c++)
 {
  $field = $fields[$c];
  if(!$field['visibled']) continue;
  
  if($field['notdbfield'])
  {
   switch($field['name'])
   {
	case 'docref' : {
		 // detect doc ref
		 if($data['doc_ap'] && $data['doc_id'])
		 {
		  $db2->RunQuery("SELECT name FROM dynarc_".$data['doc_ap']."_items WHERE id='".$data['doc_id']."'");
		  if($db2->Read())
		   $value = $db2->record['name'];
		  else if($db2->Error)
		  {
		   $db2->Close();
		   $value = "";
		   $db2 = new AlpaDatabase();
		  }
		 }
		 else
		  $value = $data['doc_ref'];
		} break;
   } // eof switch
  }
  else
   $value = $data[$field['name']];

  $dataType = "";
  $formatCode = "";

  if($field['showtotal'])
   $ret[$field['name']] = ($value > 0) ? $value : 0;

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

  }

  if($dataType)
   $sheet->setCellValueExplicitByColumnAndRow($colIdx, $rowIdx, $value, $dataType);
  else
   $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, $value);
  if($formatCode)
   $sheet->getStyleByColumnAndRow($colIdx, $rowIdx)->getNumberFormat()->setFormatCode($formatCode);

  $colIdx++;
 } // EOF - FOR

 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//
function pettycashbook_reset($args, $sessid, $shellid)
{
 $_AP = "pettycashbook";
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$_AP=$args[$c+1]; $c++;} break;			// The default archive prefix is pettycashbook //
   case '-from' : {$dateFrom=$args[$c+1]; $c++;} break;
   case '-to' : {$dateTo=$args[$c+1]; $c++;} break;

   case '-all' : $cleanAll=true; break;
  }

 if(!$dateFrom && !$dateTo && !$cleanAll)
  return array('message'=>"You must specify the date range. -from DATEFROM -to DATETO, or -all for clean all.",'error'=>'INVALID_RANGE');

 $db = new AlpaDatabase();
 if($cleanAll)
 {
  $db->RunQuery("TRUNCATE TABLE dynarc_".$_AP."_items");
  $db->RunQuery("TRUNCATE TABLE dynarc_".$_AP."_totals");
 }
 else
 {
  if($dateFrom && $dateTo)
  {
   $db->RunQuery("DELETE FROM dynarc_".$_AP."_items WHERE ctime>='".$dateFrom."' AND ctime<'".$dateTo."'");
   $db->RunQuery("DELETE FROM dynarc_".$_AP."_totals WHERE ref_date>='".$dateFrom."' AND ref_date<'".$dateTo."'");
  }
  else if($dateFrom)
  {
   $db->RunQuery("DELETE FROM dynarc_".$_AP."_items WHERE ctime>='".$dateFrom."'");
   $db->RunQuery("DELETE FROM dynarc_".$_AP."_totals WHERE ref_date>='".$dateFrom."'");
  }
  else if($dateTo)
  {
   $db->RunQuery("DELETE FROM dynarc_".$_AP."_items WHERE ctime<'".$dateTo."'");
   $db->RunQuery("DELETE FROM dynarc_".$_AP."_totals WHERE ref_date<'".$dateTo."'");
  }
 }
 $db->Close();

 $out.= "done!";

 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//


