<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 20-02-2017
 #PACKAGE: pdf-lib
 #DESCRIPTION: Some function for export documents to PDF format.
 #VERSION: 2.7beta
 #CHANGELOG: 20-02-2017 : Aggiunto parametro bypass-preoutput su funzione export.
			 24-12-2014 : Aggiunto parametro csscontent su funzione export e fast-export.
			 07-03-2014 : Aggiunto possibilità di bypassare gli errori.
			 22-02-2014 : Aggiunta funzione fast-export.
			 10-02-2014 : Abilitato gli errori.
			 05-07-2013 : Aggiunta immagine di background.
			 19-04-2013 : Bug fix with ABSOLUTE URL.
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

function shell_pdf($args, $sessid, $shellid=null)
{
 global $_BASE_PATH, $_ABSOLUTE_URL;
 switch($args[0])
 {
  case "export" : return pdf_export($args, $sessid, $shellid); break;
  case "fast-export" : return pdf_fastExport($args, $sessid, $shellid); break;
  default : return pdf_invalidArguments(); break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function pdf_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function pdf_export($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES, $_SESSID, $_SHELLID, $_CHUNK_SIZE, $_PROGRESS, $_BYPASS_PDF_ERRORS, $_BYPASS_PREOUTPUT;
 $_SESSID = $sessid;
 $_SHELLID = $shellid;
 $_CHUNK_SIZE = 0;
 $_PROGRESS = 0;

 $out = "";
 $outArr = array();

 $orientation = "P";
 $format = "A4";
 $contents = array();
 $css = array(); // css files
 $_CSS_CONTENT = "";

 $_BYPASS_PDF_ERRORS = true;
 $_BYPASS_PREOUTPUT = false;

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-c' : case '-contents' : {$contents[]=$args[$c+1]; $c++;} break;
   case '-o' : case '-output' : case '-f' : case '-file' : {$outputFile=$args[$c+1]; $c++;} break;
   
   case '-orientation' : {$orientation=$args[$c+1]; $c++;} break; /* P=portrait , L=landscape */
   case '-format' : {$format=$args[$c+1]; $c++;} break; /* A4, A5, or 100x200, ... */
   case '-background' : {$_BACKGROUND_IMAGE=$args[$c+1]; $c++;} break;
   case '-css' : {$css[]=$args[$c+1]; $c++;} break;
   case '-csscontent' : {$_CSS_CONTENT=$args[$c+1]; $c++;} break;

   /* Dynarc documents */
   case '-ap' : {$ap=$args[$c+1]; $c++;} break;
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '--include-css' : $includeCSS=true; break;

   /* OPTIONS */
   case '--bypass-preoutput' : $_BYPASS_PREOUTPUT=true; break;
  }

 if($outputFile)
 {
  if(strtolower(substr($outputFile, -4))!='.pdf')
   $outputFile.=".pdf";
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
  $fileName = $basepath.ltrim($outputFile,"/");

  $dir = dirname(ltrim($outputFile,"/"));

  if(!is_dir($basepath.$dir))
   GShell("mkdir `".$dir."`",$sessid,$shellid);
 }


 if($ap && $id)
 {
  $ret = GShell("dynarc item-info -ap `".$ap."` -id `".$id."`".($includeCSS ? " -extget css" : ""),$sessid,$shellid);
  if(!$ret['error'])
  {
   $cnts = "<style type='text/css'>".$ret['outarr']['css'][0]['content']."</style>";
   $cnts.= str_replace("{ABSOLUTE_URL}",$_ABSOLUTE_URL,$ret['outarr']['desc']);
   $contents[] = $cnts;
  }
 }

 if(count($css))
 {
  for($c=0; $c < count($css); $c++)
  {
   ob_start();
   include($_BASE_PATH.$css[$c]);
   $_CSS_CONTENT.= ob_get_contents()."\n";
   ob_end_clean();
  }
 }

 //----------------------------------------------------------------------------------------------//
 require_once($_BASE_PATH."var/lib/html2pdf/html2pdf.class.php");

 switch(strtoupper($orientation))
 {
  case 'L' : case 'LANDSCAPE' : $orientation="L"; break;
  default : $orientation="P"; break;
 }

 $orientations = array("P"=>"portrait", "L"=>"landscape");

 $start = "<page format='".$format."' orientation='".$orientations[$orientation]."' style='font:arial;'>";

 if($_CSS_CONTENT)
  $start.= "<style type='text/css'>\n".$_CSS_CONTENT."</style>\n";

 if($_BACKGROUND_IMAGE)
 {
  $start.= "<div style='width:210mm;height:297mm;background:url(".$_ABSOLUTE_URL.$_BACKGROUND_IMAGE.") center center no-repeat;position:absolute;top:0px;left:0px;'></div>";
 }
 $end = "</page>";

 $content = "";

 for($c=0; $c < count($contents); $c++)
  $content.= $start.$contents[$c].$end;

 try
 {
  $html2pdf = new HTML2PDF($orientation, $format, 'en', true, 'UTF-8', array(0,0,0,0));
  $html2pdf->pdf->SetDisplayMode('fullpage');
  $html2pdf->writeHTML($content);
  $html2pdf->Output($fileName, "F");
  //----------------------------------------------------------------------------------------------//
  $out = "PDF has been created! Destination file is $fileName";
  $outArr['fullpath'] = $fileName;
  $outArr['filename'] = ltrim($outputFile,"/");
 }
 catch(HTML2PDF_exception $err) 
 {
  return array("message"=>$err, "error"=>"PDF_ERROR");
 }

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function pdf_fastExport($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES, $_SESSID, $_SHELLID, $_CHUNK_SIZE, $_PROGRESS;

 $out = "";
 $outArr = array();

 $_SESSID = $sessid;
 $_SHELLID = $shellid;
 $_CHUNK_SIZE = 0;
 $_PROGRESS = 0;

 $orientation = "P";
 $format = "A4";
 $margin = 0;
 $contents = array();
 $css = array(); // css files
 $_CSS_CONTENT = "";
 $_DEFAULT_CSS_CONTENT = "table.itemlist td {font-family:Arial, sans-serif; font-size:7pt; height:8mm; border-bottom:1px solid #d8d8d8; vertical-align:middle;}\n";
 $_DEFAULT_CSS_CONTENT.= "table.itemlist th {font-family:Arial, sans-serif; font-size:7pt; height:8mm; border-top:1px solid #d8d8d8; border-bottom:1px solid #d8d8d8; vertical-align:middle;}\n";

 $_HEADER_CONTENT = "";
 $_FOOTER_CONTENT = "";

 $rowsPerPage = 20;

 $_FIELDS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-xmlfields' : {$xmlFields=$args[$c+1]; $c++;} break;// configurazione delle colonne in xml. (name,tag,format,width,....)
   case '-title' : {$title=$args[$c+1]; $c++;} break;
   case '-file' : case '-f' : case '-filename' : case '-o' : case '-output' : {$fileName=$args[$c+1]; $c++;} break;
   case '-cmd' : {$_CMD=$args[$c+1]; $c++;} break;  		// il comando da lanciare
   case '-resfield' : {$resField=$args[$c+1]; $c++;} break; // il nome dell'array dove si trovano i risultati. (di solito 'items')
   case '-rpp' : {$rowsPerPage=$args[$c+1]; $c++;} break; 	// Righe per pagina

   case '-header' : case '-headercontent' : {$_HEADER_CONTENT=$args[$c+1]; $c++;} break;
   case '-footer' : case '-footercontent' : {$_FOOTER_CONTENT=$args[$c+1]; $c++;} break;
 
   case '-orientation' : {$orientation=$args[$c+1]; $c++;} break; /* P=portrait , L=landscape */
   case '-format' : {$format=$args[$c+1]; $c++;} break; /* A4, A5, or 100x200, ... */
   case '-background' : {$_BACKGROUND_IMAGE=$args[$c+1]; $c++;} break;
   case '-css' : {$css[]=$args[$c+1]; $c++;} break;
   case '-csscontent' : {$_CSS_CONTENT=$args[$c+1]; $c++;} break;
   case '-margin' : {$margin=$args[$c+1]; $c++;}
  }

 if(!$fileName)
  return array('message'=>"You must specify the file name. (with: -file FILE_NAME)",'error'=>"INVALID_FILE");

 $xml = new GXML();
 if(!$xml->LoadFromString($xmlFields))
  return array('message'=>"XML Error: Unable to load xml field configuration", "error"=>"XML_ERROR");

 /* Load fields configuration */
 $fieldList = $xml->GetElementsByTagName('field');
 for($c=0; $c < count($fieldList); $c++)
 {
  $node = $fieldList[$c];
  $field = array("name"=>$node->getString('name'), "tag"=>$node->getString('tag'), "format"=>$node->getString('format'), 
	"retvalue"=>$node->getString('retvalue'), "alternatetag"=>$node->getString('alternatetag'), 
	"dateformat"=>$node->getString('dateformat'), "timeformat"=>$node->getString('timeformat'), "width"=>$node->getString('width'),
	"align"=>$node->getString('align'));
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

 /* Check filename */
 $pi = pathinfo($fileName);
 if(!$pi['extension'])
  $fileName.= ".pdf";

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

 /* Init */
 $interface = array("name"=>"progressbar","steps"=>1);
 gshPreOutput($shellid,"Inizializzazione...", "ESTIMATION", "", "PASSTHRU", $interface);

 /* EXEC - COMMAND */
 $ret = GShell($_CMD,$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $_RESULTS = $resField ? $ret['outarr'][$resField] : $ret['outarr'];

 $pages = ceil(count($_RESULTS)/$rowsPerPage);
 //$interface = array("name"=>"progressbar","steps"=>$pages);
 //gshPreOutput($shellid,"Esportazione in corso...", "ESTIMATION", "", "PASSTHRU", $interface);


 require_once($_BASE_PATH."var/lib/html2pdf/html2pdf.class.php");

 $orientations = array("P"=>"portrait", "L"=>"landscape");
 switch(strtoupper($format))
 {
  case 'A0' : {$w=841; $h=1189;} break;
  case 'A1' : {$w=594; $h=841;} break;
  case 'A2' : {$w=420; $h=594;} break;
  case 'A3' : {$w=297; $h=420;} break;
  case 'A4' : {$w=210; $h=297;} break;
  case 'A5' : {$w=148; $h=210;} break;
  case 'A6' : {$w=105; $h=148;} break;
  case 'A7' : {$w=74; $h=105;} break;
  case 'A8' : {$w=52; $h=74;} break;
  case 'A9' : {$w=37; $h=52;} break;
  case 'A10' : {$w=26; $h=37;} break;
 }
 switch(strtoupper($orientation))
 {
  case 'L' : case 'LANDSCAPE' : {
	 $orientation="L";
	 $pageWidth = $h;
	 $pageHeight = $w;
	} break;
  default : {
	 $orientation="P"; 
	 $pageWidth = $w;
	 $pageHeight = $h;
	}break;
 }

 $_START = "<page format='".$format."' orientation='".$orientations[$orientation]."' style='font:arial;'>";

 if($_CSS_CONTENT)
  $_START.= "<style type='text/css'>\n".$_CSS_CONTENT."</style>\n";
 else
  $_START.= "<style type='text/css'>\n".$_DEFAULT_CSS_CONTENT."</style>\n";

 if($_BACKGROUND_IMAGE)
  $_START.= "<div style='width:".$pageWidth."mm;height:".$pageHeight."mm;background:url(".$_ABSOLUTE_URL.$_BACKGROUND_IMAGE.") center center no-repeat;position:absolute;top:0px;left:0px;'></div>";

 $_START.= $_HEADER_CONTENT;

 $_START.= "<table cellspacing='0' cellpadding='0' border='0' class='itemlist'>";
 // colgroup
 $_START.= "<colgroup>";
 for($c=0; $c < count($_FIELDS); $c++)
 {
  $field = $_FIELDS[$c];
  $_START.= "<col style='width:".($field['width'] ? $field['width'] : '20')."mm'/>";
 }
 $_START.= "</colgroup>";
 $_START.= "<tbody>";
 // header
 $_START.= "<tr>";
 for($c=0; $c < count($_FIELDS); $c++)
 {
  $field = $_FIELDS[$c]; $style = "";
  if($field['align']) $style.= "text-align:".$field['align'].";";
  $_START.= "<th".($style ? " style='".$style."'" : "").">".$field['name']."</th>";
 }
 $_START.= "</tr>";

 $_END = "</tbody></table>";
 $_END.= $_FOOTER_CONTENT;
 $_END.= "</page>";

 /* ESPORTING */
 $_CONTENT = $_START;
 $idx = 0;
 for($c=0; $c < count($_RESULTS); $c++)
 {
  $item = $_RESULTS[$c];
  if($idx == $rowsPerPage)
  {
   $_CONTENT.= $_END.$_START;
   $idx = 0;
  }

  $_CONTENT.= "<tr>";
  for($i=0; $i < count($_FIELDS); $i++)
  {
   $field = $_FIELDS[$i]; $style = "";
   if($field['align']) $style.= "text-align:".$field['align'].";";

   $value = $item[$field['tag']];
   if(!$value && ($field['alternatetag']))
    $value = $item[$field['alternatetag']];

   if($field['retvalue'] == "option")
    $value = $field['options'][$value];

   switch($field['format'])
   {
	case 'datetime' : {
		 if($value && is_numeric($value))
		  $value = date($field['dateformat'] ? $field['dateformat'] : 'd/m/Y H:i', $value);
		 else if($value)
		  $value = date($field['dateformat'] ? $field['dateformat'] : 'd/m/Y H:i', strtotime($value));
		} break;

	case 'date' : {
		 if($value && is_numeric($value))
		  $value = date($field['dateformat'] ? $field['dateformat'] : 'd/m/Y', $value);
		 else if($value)
		  $value = date($field['dateformat'] ? $field['dateformat'] : 'd/m/Y', strtotime($value));
		} break;

	case 'time' : {
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
		 if(!$value)
		  $value = "0";
		} break;

	case 'currency' : {
		 $value = number_format($value,2,",",".");
		} break;

   }
   $_CONTENT.= "<td".($style ? " style='".$style."'" : "").">".(($value!='') ? $value : '&nbsp;')."</td>";   
  }
  $_CONTENT.= "</tr>";
  $idx++;
 }
 $_CONTENT.= $_END;

 try
 {
  $html2pdf = new HTML2PDF($orientation, $format, 'en', true, 'UTF-8', array($margin,$margin,$margin,$margin));
  $html2pdf->pdf->SetDisplayMode('fullpage');
  $html2pdf->writeHTML($_CONTENT);
  $html2pdf->Output($basepath.ltrim($fileName,"/"), "F");
  //----------------------------------------------------------------------------------------------//
  $out = "PDF has been created! Destination file is $fileName";
  $outArr = array("filename"=>$fileName, "fullpath"=>$_USERS_HOMES.$db->record['homedir']."/".$fileName);
 }
 catch(HTML2PDF_exception $err) 
 {
  return array("message"=>$err, "error"=>"PDF_ERROR");
 }

 return array('message'=>$out, 'outarr'=>$outArr);

}
//-------------------------------------------------------------------------------------------------------------------//


