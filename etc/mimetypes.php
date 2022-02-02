<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-04-2013
 #PACKAGE: filemanager
 #DESCRIPTION: This file contains various mimetypes extensions
 #VERSION: 2.2beta
 #CHANGELOG: 18-04-2013 : Aggiunto XLSX nella categoria Spreadsheet.
			 31-01-2013 : Incluso $_MIME_TYPES e funzione getFileMimeType(FILE_NAME)
 #TODO: Bisogna completare tutto il resto dei mimetypes.
 
*/

global $_BASE_PATH, $_MIME_TYPES;

$mimetypes = array(
	'jpeg'=>'IMAGE',
	'jpg'=>'IMAGE',
	'png'=>'IMAGE',
	'gif'=>'IMAGE',
	'bmp'=>'IMAGE',
	'pdf'=>'PDF',
	'mp3'=>'AUDIO',
	'mpeg'=>'VIDEO',
	'wma'=>'AUDIO',
	'wmv'=>'VIDEO',
	'flv'=>'VIDEO',
	'zip'=>'ZIP',
	'ods'=>'SPREADSHEET',
	'xls'=>'SPREADSHEET',
	'xlsx'=>'SPREADSHEET',
	'svg'=>'SVG');

$_MIME_TYPES = array(
	"avi"=>"video/avi",
	"bmp"=>"image/bmp",
	"css"=>"text/css",
	"doc"=>"application/msword",
	"gz"=>"application/x-compressed", "zip"=>"application/x-compressed",
	"html"=>"text/html",
	"ico"=>"image/x-icon",
	"jpg"=>"image/jpeg", "jpeg"=>"image/jpeg",
	"pdf"=>"application/pdf",
	"png"=>"image/png",
	"gif"=>"image/gif",
	"xml"=>"application/xml");

function getMimetypeIcons($tag,$size="")
{
 global $_BASE_PATH;

 $sizes = array('22x22','48x48');
 $exts = array('gif','png','jpg','bmp');
 $ret = array();
 for($c=0; $c < count($sizes); $c++)
 {
  for($e=0; $e < count($exts); $e++)
  {
   if(file_exists($_BASE_PATH."share/mimetypes/".$sizes[$c]."/".strtolower($tag).".".$exts[$e]))
   {
	$ret["size".$sizes[$c]] = "share/mimetypes/".$sizes[$c]."/".strtolower($tag).".".$exts[$e];
	$e = count($exts);
   }
  }
 }
 return $ret;
}

function getFileMimeType($fileName="")
{
 global $_MIME_TYPES;
 $ext = strtolower(substr($fileName,strrpos($fileName,".")+1));
 if($_MIME_TYPES[$ext])
  return $_MIME_TYPES[$ext];
 return "";
}

