<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-07-2013
 #PACKAGE: pdf-lib
 #DESCRIPTION: Some function for export documents to PDF format.
 #VERSION: 2.2beta
 #CHANGELOG: 05-07-2013 : Aggiunta immagine di background.
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
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES, $_SESSID, $_SHELLID, $_CHUNK_SIZE, $_PROGRESS;
 $_SESSID = $sessid;
 $_SHELLID = $shellid;
 $_CHUNK_SIZE = 0;
 $_PROGRESS = 0;

 $out = "";
 $outArr = array();

 $orientation = "P";
 $format = "A4";
 $contents = array();


 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-c' : case '-contents' : {$contents[]=$args[$c+1]; $c++;} break;
   case '-o' : case '-output' : {$outputFile=$args[$c+1]; $c++;} break;
   
   case '-orientation' : {$orientation=$args[$c+1]; $c++;} break; /* P=portrait , L=landscape */
   case '-format' : {$format=$args[$c+1]; $c++;} break; /* A4, A5, or 100x200, ... */
   case '-background' : {$_BACKGROUND_IMAGE=$args[$c+1]; $c++;} break;

   /* Dynarc documents */
   case '-ap' : {$ap=$args[$c+1]; $c++;} break;
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '--include-css' : $includeCSS=true; break;
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

 //----------------------------------------------------------------------------------------------//
 require_once($_BASE_PATH."var/lib/html2pdf/html2pdf.class.php");

 switch(strtoupper($orientation))
 {
  case 'L' : case 'LANDSCAPE' : $orientation="L"; break;
  default : $orientation="P"; break;
 }

 $orientations = array("P"=>"portrait", "L"=>"landscape");

 $start = "<page format='".$format."' orientation='".$orientations[$orientation]."' style='font:arial;'>";
 if($_BACKGROUND_IMAGE)
 {
  $start.= "<div style='width:210mm;height:297mm;background:url(".$_ABSOLUTE_URL.$_BACKGROUND_IMAGE.") center center no-repeat;position:absolute;top:0px;left:0px;'></div>";
 }
 $end = "</page>";

 $content = "";

 for($c=0; $c < count($contents); $c++)
  $content.= $start.$contents[$c].$end;

 $html2pdf = new HTML2PDF($orientation, $format, 'en', true, 'UTF-8', array(0,0,0,0));
 $html2pdf->pdf->SetDisplayMode('fullpage');
 $html2pdf->writeHTML($content);
 $html2pdf->Output($fileName, "F");

 //----------------------------------------------------------------------------------------------//
 $out = "PDF has been created! Destination file is $fileName";
 $outArr['fullpath'] = $fileName;
 $outArr['filename'] = ltrim($outputFile,"/");

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

