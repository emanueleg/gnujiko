<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 03-02-2014
 #PACKAGE: htmltable2array
 #DESCRIPTION: Convert HTML table into array for manipulation with other gshell commands.
 #VERSION: 2.1beta
 #CHANGELOG: 03-02-2014 : Aggiunto parametro --strip-tags
 #TODO:
 
*/

function strbipos($haystack="", $needle="", $offset=0) 
{
 // Search backwards in $haystack for $needle starting from $offset and return the position found or false
 $len = strlen($haystack);
 $pos = stripos(strrev($haystack), strrev($needle), $len - $offset - 1);
 return ( ($pos === false) ? false : $len - strlen($needle) - $pos );
}
//-------------------------------------------------------------------------------------------------------------------//
function shell_htmltable2array($args, $sessid, $shellid=0)
{
 global $_BASE_PATH, $_USERS_HOMES;
 $sessInfo = sessionInfo($sessid);
 $out = "";
 $outArr = array();

 $stripTags = false;
 
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

 for($c=0; $c < count($args); $c++)
 {
  switch($args[$c])
  {
   case '-f' : {$fileName=$args[$c+1]; $c++;} break;
   case '-c' : case '-content' : case '-contents' : {$contents=$args[$c+1]; $c++;} break;
   case '--strip-tags' : $stripTags=true; break;
  }
 }

 if(!$fileName && !$contents)
  return array('message'=>"You must specify the html file that contains the table. (with: -f FILENAME)","error"=>"INVALID_FILE_NAME");

 if($fileName && !file_exists($basepath.$fileName))
  return array('message'=>"File $fileName does not exists.","error"=>"FILE_DOES_NOT_EXISTS");

 if($fileName)
 {
  /* LOAD HTML TABLE FROM FILE */
  include_once($_BASE_PATH."include/filesfunc.php");

  /* LOADING FILE ... */
  $fileSize = filesize($basepath.$fileName);
  if(!$fileSize)
   return array('message'=>"File $fileName is blank.","error"=>"FILE_IS_VOID");

  $steps = ceil($fileSize/1024);
 
  $interface = array("name"=>"progressbar","steps"=>$steps);
  gshPreOutput($shellid,"Loading file ".$fileName."...", "ESTIMATION", $fileName, "PASSTHRU", $interface);

  $buffer = "";
  $fp = fopen($basepath.$fileName,"r");
  $p=0;
  $idx=0;

  while($tmp = fread($fp, 1024))
  {
   $buffer.= $tmp;
   gshPreOutput($shellid,"Extracting ".$idx." rows...", "PROGRESS", $fileName);
   $rowInfo = htmltable2array_getRow($buffer, 0, $stripTags);
   if(!$rowInfo)
    continue;

   while($rowInfo)
   {
	$outArr[] = array("cells"=>$rowInfo['items']);
    $idx++;
    $p = $rowInfo['nextpointer'];
    $buffer = substr($buffer, $p); 
    $rowInfo = htmltable2array_getRow($buffer, 0, $stripTags);
   }
  }
  fclose($fp);
 }
 else if($contents)
 {
  /* LOAD HTML TABLE FROM CONTENTS */
  $buffer = $contents;
  $p=0;

  while($rowInfo = htmltable2array_getRow($buffer, 0, $stripTags))
  {
   $outArr[] = array("cells"=>$rowInfo['items']);
   $p = $rowInfo['nextpointer'];
   $buffer = substr($buffer, $p);
  }
 }

 $out.= "Found ".count($outArr)." rows.";

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function htmltable2array_getRow($contents, $pointer=0, $stripTags=false)
{
 $pos = stripos($contents, "<TR", $pointer);
 if($pos === false)
  return false;
 $spOuter = $pos;

 $pos = stripos($contents, ">", $spOuter);
 if($pos === false)
  return false;
 $spInner = $pos+1;


 $pos = stripos($contents, "TR>", $spInner);
 if($pos === false)
  return false;
 $epOuter = $pos+3;

 $pos = strbipos($contents, "<", $epOuter-3);
 if($pos == false)
  return false;
 $epInner = $pos;

 $html = substr($contents, $spOuter, ($epOuter-$spOuter));
 $innerHTML = substr($contents, $spInner, ($epInner-$spInner));
 $nextPointer = $epOuter;

 $cells = htmltable2array_getCells($innerHTML, 0, $stripTags);

 return array('nextpointer'=>$nextPointer, 'html'=>$html, 'inner'=>$innerHTML, 'items'=>$cells);
}
//-------------------------------------------------------------------------------------------------------------------//
function htmltable2array_getCells($contents, $pointer=0, $stripTags=false)
{
 $retArr = array();
 $tag = "TD";
 $pos = stripos($contents, "<".$tag, $pointer);
 if($pos === false)
 {
  $tag = "TH";
  $pos = stripos($contents, "<".$tag, $pointer);
  if($pos === false)
   return $retArr;
 }

 $contentLength = strlen($contents);
 while($pointer < $contentLength)
 {
  $pos = stripos($contents, "<".$tag, $pointer);
  if($pos === false)
   break;
  $spOuter = $pos;
  
  $pos = stripos($contents, ">", $spOuter);
  if($pos === false)
   break;
  $spInner = $pos+1;


  $pos = stripos($contents, $tag.">", $spInner);
  if($pos === false)
   break;
  $epOuter = $pos+3;

  $pos = strbipos($contents, "<", $epOuter-3);
  if($pos == false)
   break;
  $epInner = $pos;

  $html = substr($contents, $spOuter, ($epOuter-$spOuter));
  $innerHTML = substr($contents, $spInner, ($epInner-$spInner));
  if($stripTags)
   $innerHTML = strip_tags($innerHTML);
  if((strtolower($innerHTML) == "&nbsp;") || (strtolower($innerHTML) == "<br>") || (strtolower($innerHTML) == "<br/>"))
   $innerHTML = "";
  $nextPointer = $epOuter;

  $retArr[] = $innerHTML;
  $pointer = $nextPointer;
 }

 return $retArr;
}
//-------------------------------------------------------------------------------------------------------------------//

