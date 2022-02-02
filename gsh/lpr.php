<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 01-08-2012
 #PACKAGE: lpr
 #DESCRIPTION: Linux Printers functions
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS: 
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

function shell_lpr($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'list' : return lpr_list($args, $sessid, $shellid); break;
  case 'print' : return lpr_print($args, $sessid, $shellid); break;

  default : return lpr_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function lpr_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function lpr_list($args, $sessid, $shellid)
{
 /* Return a list of installed printers on Linux. */
 ob_start();
 passthru('lpstat -a');
 $ret = ob_get_contents();
 ob_end_clean();
 $ret = nl2br($ret);

 $x = explode("<br />", $ret);
 for($c=0; $c < count($x); $c++)
 {
  $str = $x[$c];
  if(!$str || ($str == "\n"))
   continue;
  $xx = explode(" ",$str);
  $printerName = ltrim($xx[0]);
  $out.= $printerName."\n";
  $outArr[] = $printerName;
 }

 $out.= count($outArr)." printers found.";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function lpr_print($args, $sessid, $shellid)
{
 global $_ABSOLUTE_URL, $_BASE_PATH, $_USERS_HOMES;
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
   case '-p' : {$printer=$args[$c+1]; $c++;} break;
   case '-f' : {$file=$args[$c+1]; $c++;} break;
   case '-c' : case '-contents' : case '-content' : {$contents=$args[$c+1]; $c++;} break;
   case '-n' : case '-num' : {$numOfCopies=$args[$c+1]; $c++;} break;
  }

 if($file)
 {
  $fileName = $basepath.ltrim($file,"/");
  if(!file_exists($fileName))
   return array('message'=>"File $fileName does not exists","error"=>"FILE_DOES_NOT_EXISTS");
  $f = fopen($fileName,"r");
  $contents = fread($f, filesize($fileName));
  fclose($f);
 }

 $cmd = "lpr";
 if($printer)
  $cmd.= " -P ".$printer;
 if($numOfCopies)
  $cmd.= " -# ".$numOfCopies;
 $cmd.= " ";

 $pipe=popen($cmd , 'w' );
 if(!$pipe)
  return array('message'=>'Pipe failed!','error'=>'PIPE_FAILED');
 fputs($pipe,$contents);
 pclose($pipe);
 
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//

