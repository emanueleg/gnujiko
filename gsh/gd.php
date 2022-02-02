<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 09-03-2016
 #PACKAGE: gd-lib
 #DESCRIPTION: GD (Graphic Library) command-line tool
 #VERSION: 2.3beta
 #CHANGELOG: 09-03-2016 : Aggiunta funzione save-from-dataurl.
			 12-02-2014 : Aggiunta funzione get-image-size
			 08-02-2013 : Bug fix.

 #TODO: Fare le funzioni no-stretch e no-cut su gd resize.
 #TODO: Save via FTP su funzione save-from-dataurl.
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_gd($args, $sessid, $shellid=0)
{
 $output = "";
 $outArr = array();

 if(count($args) == 0)
  return gjkgd_invalidArguments();

 switch($args[0])
 {
  // ACTION //
  case 'info' : return gjkgd_info($args, $sessid, $shellid); break;
  case 'resize' : return gjkgd_resize($args, $sessid, $shellid); break;
  case 'get-image-size' : return gjkgd_getImageSize($args, $sessid, $shellid); break;
  case 'save-from-dataurl' : return gjkgd_saveFromDataURL($args, $sessid, $shellid); break;

  default : return gjkgd_invalidArguments(); break;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function gjkgd_invalidArguments()
{
 return array("message"=>"Invalid arguments.", "error"=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function gjkgd_info($args, $sessid, $shellid)
{
 global $_BASE_PATH;

 $info = gd_info();
 $out = "<table border='0' style='font-size:12px;'>";
 $k = array_keys($info);
 for($c=0; $c < count($k); $c++)
  $out.= "<tr><td>".$k[$c].":</td><td>".(is_bool($info[$k[$c]]) ? ($info[$k[$c]] ? 'enabled' : 'disabled') : $info[$k[$c]])."</td></tr>";
 $out.= "</table>";

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function gjkgd_resize($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_USERS_HOMES;
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
  return array("message"=>"Unable to create file: you don't have a valid account!","error"=>"INVALID_USER");


 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-i' : case '-src' : {$fileInput = $args[$c+1]; $c++;} break;
   case '-o' : case '-dst' : {$fileOutput = $args[$c+1]; $c++;} break;
   case '-w' : case '-width' : {$width=$args[$c+1]; $c++;} break;
   case '-h' : case '-height' : {$height=$args[$c+1]; $c++;} break;

   /* TODO: fare funzioni no-stretch e no-cut */
   //case '--no-stretch' : $noStretch=true; break;
   //case '--no-cut' : case '--entire' : $noCut=true; break;

   default : {
	 if(!$fileInput)
	  $fileInput = $args[$c];
	 else if(!$fileOutput)
	  $fileOutput = $args[$c];
	} break;
  }

 if(!$fileInput)
  return array('message'=>"You must specify source file. (with: -src FILENAME)", "error"=>"INVALID_SRC_FILE");

 if(!$fileOutput)
  $fileOutput = $fileInput;

 if(!file_exists($basepath.$fileInput))
  return array('message'=>"File $fileInput does not exists.","error"=>"SRC_FILE_DOES_NOT_EXISTS");
 
 $out = "Detecting image type ...";
 $fileExtension = strtolower(substr($fileInput, strrpos($fileInput, '.')+1));
 if(strrpos($fileOutput, '.'))
  $outFileExtension = strtolower(substr($fileOutput, strrpos($fileOutput, '.')+1));
 if(!$outFileExtension)
 {
  $outFileExtension = $fileExtension;
  $fileOutput.= ".".$outFileExtension;
 }
 switch($fileExtension)
 {
  case 'jpeg' : case 'jpg' : {$out.= "jpeg\n"; $srcImg = @imagecreatefromjpeg($basepath.$fileInput);} break;
  case 'png' : {$out.= "png\n"; $srcImg = @imagecreatefrompng($basepath.$fileInput);} break;
  case 'bmp' : {$out.= "bmp\n"; $srcImg = @imagecreatefromwbmp($basepath.$fileInput);} break;
  case 'gif' : {$out.= "gif\n"; $srcImg = @imagecreatefromgif($basepath.$fileInput);} break;
  default : {$out.= "unknown\n"; $err = "UNKNOWN_FILE_TYPE"; return false;} break;
 }
 if(!$srcImg)
  return array('message'=>$out."\nUnable to create image from $fileInput.\n","error"=>"UNKNOWN_ERROR");

 $imgW = imagesx($srcImg);
 $imgH = imagesy($srcImg);
 $qX = $imgH/$imgW;
 $qY = $imgW/$imgH;

 $retW = $width ? $width : ($height ? $height*$qY : $imgW);
 $retH = $height ? $height : ($width ? $width*$qX : $imgH);

 $out.= "Resizing image from ".$imgW."x".$imgH." to ".$retW."x".$retH." ...";

 $dstImg = @imagecreatetruecolor($retW,$retH);
 if(!$dstImg)
  return array('message'=>$out."failed! (Cannot initialize new GD image stream)\n", 'error'=>"UNKNOWN_ERROR");

 if(!@imagecopyresampled($dstImg,$srcImg,0,0,0,0,$retW,$retH,$imgW,$imgH))
  return array('message'=>$out."failed! (Unable to create copy resampled)\n", 'error'=>"UNKNOWN_ERROR");

 $out.= "done\n";
 $out.= "Exporting image to format ";
 switch($outFileExtension)
 {
  case 'jpeg' : case 'jpg' : {
	 $out.= "JPEG to file $fileOutput ...";
	 if(!@imagejpeg($dstImg,$basepath.$fileOutput))
	 {
	  $out.= "failed! (Unable to export into JPEG format, or you have bad file permission\n";
	  @imagedestroy($srcImg);
	  @imagedestroy($dstImg);
	  return array('message'=>$out, 'error'=>"EXPORT_ERROR");
	 }
	} break;
  case 'png' : {
	 $out.= "PNG to file $fileOutput ...";
	 if(!@imagepng($dstImg,$basepath.$fileOutput))
	 {
	  $out.= "failed! (Unable to export into PNG format, or you have bad file permission\n";
	  @imagedestroy($srcImg);
	  @imagedestroy($dstImg);
	  return array('message'=>$out, 'error'=>"EXPORT_ERROR");
	 }
	} break;
  case 'bmp' : {
	 $out.= "BMP to file $fileOutput ...";
	 if(!@imagewbmp($dstImg,$basepath.$fileOutput))
	 {
	  $out.= "failed! (Unable to export into BMP format, or you have bad file permission\n";
	  @imagedestroy($srcImg);
	  @imagedestroy($dstImg);
	  return array('message'=>$out, 'error'=>"EXPORT_ERROR");
	 }
	} break;
  case 'gif' : {
	 $out.= "GIF to file $fileOutput ...";
	 if(!@imagegif($dstImg,$basepath.$fileOutput))
	 {
	  $out.= "failed! (Unable to export into GIF format, or you have bad file permission\n";
	  @imagedestroy($srcImg);
	  @imagedestroy($dstImg);
	  return array('message'=>$out, 'error'=>"EXPORT_ERROR");
	 }
	} break;
  default : return array('message'=>$out."failed! (bad file extension)\n", 'error'=>"BAD_FILE_EXTENSION"); break;
 }
 $out.= "done\n";
 $outArr = array('url'=>$basepath.$fileOutput, 'width'=>$retW, 'height'=>$retH, 'type'=>$outFileExtension);
 @imagedestroy($srcImg);
 @imagedestroy($dstImg);

 return array('message'=>$out, 'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gjkgd_getImageSize($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_USERS_HOMES;
 $out = "";
 $outArr = array();

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

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-file' : case '-f' : {$fileName=$args[$c+1]; $c++;} break;
   case '-url' : {$url=$args[$c+1]; $c++;} break;
   default : $url=$args[$c]; break;
  }

 if($fileName)
 {
  $file = $basepath.$fileName;
  if(!file_exists($file))
   return array("message"=>"Error: file ".$fileName." does not exists!", "error"=>"FILE_DOES_NOT_EXISTS");
  $input = $file;
 }
 else if($url)
  $input = $url;
 else
  return array("message"=>"You must specify the file name. (with: -file FILENAME || -url URL)", "error"=>"INVALID_FILE_NAME");

 $size = @getimagesize($input);
 if(!$size || !count($size))
  return array("message"=>"Error: unable to detect size for image: ".$input, "error"=>"UNABLE_TO_DETECT_IMAGE_SIZE");

 $out.= "Size = ".$size[0]."x".$size[1];
 $outArr = array("width"=>$size[0], "height"=>$size[1]);

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function gjkgd_saveFromDataURL($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_USERS_HOMES;
 include_once($_BASE_PATH."include/filesfunc.php");

 $out = "";
 $outArr = array();

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
  return array("message"=>"Unable to create file: you don't have a valid account!","error"=>"INVALID_USER");

 $DATA = "";

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-f' : case '-file' : {$fileName=$args[$c+1]; $c++;} break;
   case '-data' : {$DATA = $args[$c+1]; $c++;} break;

   default : { if(!$DATA) $DATA=$args[$c]; } break;
  }

 $filteredData = substr($DATA, strpos($DATA, ",")+1);
 $unencodedData = base64_decode($filteredData);

 $fp = fwopen($fileName, 'wb', $basepath, $sessid, $shellid);
 if(!$fp) return array('message'=>"Unable to create file ".$fileName.".\n", 'error'=>'UNABLE_TO_CREATE_FILE');
 if(!@fwrite($fp, $unencodedData)) return array('message'=>"Unable to write to file ".$fileName.".\n", 'error'=>'UNABLE_TO_WRITE_TO_FILE');
 @fclose($fp);
 /* TODO: da fare in caso di scrittura via FTP. */


 $outArr['filename'] = $fileName;
 $outArr['absoluteurl'] = $outArr['fullfilename'] = $basepath.$fileName;

 $out.= "done!\nImage has been saved into file ".$fileName."\n";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//


