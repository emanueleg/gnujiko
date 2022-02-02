<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-03-2016
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Gnujiko official shell PHP class
 #VERSION: 2.11beta
 #CHANGELOG: 15-03-2016 : Modificata funzione GShell, bug fix su REDIRECT_OUTPUT, testare prima di pubblicare.
			 04-02-2016 : Aggiunta funzione gshSaveDebugLog.
			 23-10-2014 : Bug fix.
			 17-07-2014 : Bug fix con CDATA nel commandLineParser su riga 87
			 23-04-2013 : Bug fix on line 336 gshPreOutput on chmod
			 11-04-2013 : Sistemato i permessi ai files.
			 04-02-2013 : Extra argument "+" added.
			 13-12-2012 - Added function gshCommandLog.
			 03-11-2012 - Bug fix in function gshPreOutput.
			 01-06-2012 - Bug fix.
			 31-01-2012 - Fixed parserize into args shell result pointers [from row 147]
			 30-08-2011 - Bug fix on mkdir function [row 209]
			 26-02-2011 - Bug fix with special chars
 #TODO:
  
*/
global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH;
include_once($_BASE_PATH."config.php");
include_once($_BASE_PATH."var/lib/database.php");
include_once($_BASE_PATH."include/session.php");
include_once($_BASE_PATH."var/lib/xmllib.php");
//-------------------------------------------------------------------------------------------------------------------//
function commandLineParser($string)
{
 $ret = array();
 
 $chunks = array();
 $str = "";
 $oQ = null; // open quot //
 
 // --- chunkerize string --- //
 for($cidx=0; $cidx < strlen($string); $cidx++)
 {
  switch($string[$cidx])
  {
   case " " : {
	 if(!$oQ)
	 {
	  if($str!="")
	   $chunks[] = $str;
	  $str = "";
	 }
	 else
	  $str.= $string[$cidx];
	} break;
   case "+" : {
	 if(!$oQ)
	 {
	  if($str!="")
	   $chunks[] = $str;
	  $chunks[] = "+";
	  $str = "";
	 }
	 else
	  $str.= $string[$cidx]; 
	} break;
   case '"' : case "'" : case "`" : {
	 if($oQ && ($oQ == $string[$cidx]))
	 {
	  $oQ = null;
	  $chunks[] = $str;
	  $str = "";
	 }
	 else if(!$oQ)
	  $oQ = $string[$cidx];
	 else
	  $str.= $string[$cidx];
	} break;
   default : {
	 if(substr($string,$cidx,9) == "<![CDATA[")
	 {
	  $qp = strpos($string, "]]>", $cidx+9);
	  //$str.= substr($string, $cidx, ($qp+3)-$cidx);
	  $str.= substr($string, $cidx+9, ($qp-9)-$cidx);
	  $cidx = $qp+2; 
	 }
	 else
	  $str.= $string[$cidx];
	} break;
  }
 }
 if($oQ)
 {
  $ret['success'] = false;
  $ret['errors'] = "UNTERMINATED_STRING";
  $ret['charpos'] = count($string);
  return $ret;
 }
 // close last chunk
 if($str!="")
  $chunks[] = $str;
 // --- EOF Chunkerize string --- //

 // Parserize chunks //
 for($c=0; $c < count($chunks); $c++)
 {
  switch($chunks[$c])
  {
   case '&&' : $ret['commands'][] = array(); break;
   case '|' : {
	 if($ret['commands'][count($ret['commands'])-1])
	  $ret['commands'][count($ret['commands'])-1]['method'] = "REDIRECT_OUTPUT";
	 $ret['commands'][] = array();
	} break;
   case '||' : {
	 if($ret['commands'][count($ret['commands'])-1])
	  $ret['commands'][count($ret['commands'])-1]['method'] = "REDIRECT_OUTARR";
	 $ret['commands'][] = array();
	} break;
   case '>' : {
	 if($ret['commands'][count($ret['commands'])-1])
	 {
	  $ret['commands'][count($ret['commands'])-1]['method'] = "REDIRECT_TO_FILE";
	  $ret['commands'][count($ret['commands'])-1]['filename'] = $chunks[$c+1];
	  $c++;
	 }
	} break;
   default : {
	 if(!isset($ret['commands']) || !count($ret['commands']))
	 {
	  $ret['commands'][] = array('name'=>$chunks[$c],'args'=>array());
	 }
	 else if(!$ret['commands'][count($ret['commands'])-1]['name'])
	  $ret['commands'][count($ret['commands'])-1]['name'] = $chunks[$c];
	 else
	  $ret['commands'][count($ret['commands'])-1]['args'][] = stripslashes($chunks[$c]); 
	} break;
  }
 }
 // --- EOF Parserize chunks --- //
 $ret['success'] = true;
 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//
function GShell($cmdstr, $sessid=null, $shellid=0, $extra=null)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES, $_DEFAULT_FILE_PERMS;
 $clp = commandLineParser($cmdstr);
 if(!$clp['success'])
  return false;

 $messages = "";
 $lastOutarr = array();
 $lastOutputMessage = "";
 $lastMethod = null;

 // execute commands //
 for($c=0; $c < count($clp['commands']); $c++)
 {
  $cmd = $clp['commands'][$c]['name'];
  $args = $clp['commands'][$c]['args'];
  $method = isset($clp['commands'][$c]['method']) ? $clp['commands'][$c]['method'] : null;
  $extraVar = null;

  if($c > 0)
  {
   $lastMethod = isset($clp['commands'][$c-1]['method']) ? $clp['commands'][$c-1]['method'] : null;
   // parserize into args shell result pointers //
   $addToArg=-1;
   for($i=0; $i < count($args); $i++)
   {
    $arg = $args[$i];

	if($arg == "+")
	{
	 $addToArg = $i-1;
	 array_splice($args,$i,1);
	 $i--;
	 continue;
	}

    if($arg[0] == "*") // check for last results pointer
    {
     if($arg[1] == "[")
      $num = (int)substr($arg,2,strpos($arg,"]")-2);
     else if($arg[1] == ".")
      $num = 1;
     else if($arg[1] == "*")
	 {
	  if(strpos($arg,".") !== false)
       $num = strpos($arg,".");
	  else
	   $num = 2; 
	  /* Per indicare il penultimo risultato è sufficiente digitare 2 asterischi (**), dal terzultimo risultato in poi è necessario indicarlo
		 tra parentesi. Es: *[2] per indicare il terzultimo risultato, *[3] per il quartultimo, ecc... */
	 }

	 if(strpos($arg,".") !== false)
	 {
      $var = substr($arg, strpos($arg,".")+1, strlen($arg));
	  /* check if $var contains [ ] */
	  if(($pos = strpos($var,"[")) !== false)
	  {
	   $arrName = substr($var,0,$pos);
	   $keys = explode(",",ltrim(rtrim(str_replace("][",",",substr($var,$pos)),"]"),"["));
	   if($lastOutarr[$num-1])
	   {
	    $retarg = $lastOutarr[$num-1][$arrName];
	    for($j=0; $j < count($keys); $j++)
		 $retarg = $retarg[$keys[$j]];
	    $args[$i] = $retarg; 
	   }
	  }
	  else if($lastOutarr[$num-1])
	  {
	   if(is_array($lastOutarr[$num-1][$var]))
		$extraVar  = $lastOutarr[$num-1][$var];
	   else
        $args[$i] = $lastOutarr[$num-1][$var];
	  }
	  else
	   $args[$i] = "";
	 }
	 else
	  $extraVar = $lastOutarr[$num ? ($num-1) : 0];
    } // EOF - check for last results pointer

    if($addToArg > -1)
	{
	 $args[$addToArg] = $args[$addToArg].$args[$i];
	 array_splice($args, $i, 1);
	 $i--;
	 $addToArg = -1;
	}

   } // EOF - for args
  } // EOF if ($c > 0)
  // EOF parserize into args shell result pointers //

  if($lastMethod == "REDIRECT_OUTPUT")
   $args[] = $lastOutputMessage;

  if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH.$cmd.".php"))
  {
   include_once($_BASE_PATH.$_SHELL_CMD_PATH.$cmd.".php");
   $ret = call_user_func("shell_".$cmd, $args, $sessid, $shellid, $extraVar ? $extraVar : $extra);
   if($method == "REDIRECT_TO_FILE")
   {
    /* detect home dir */
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
    $fileName = $clp['commands'][$c]['filename'];
    $fileName = $basepath.ltrim($fileName,"/");
    $f = @fopen($fileName,"w");
    if($f)
     @fwrite($f,$ret['htmloutput'] ? $ret['htmloutput'] : $ret['message']);
    @fclose($f);
    @chmod($fileName,$_DEFAULT_FILE_PERMS);
   }
   else if($method == "REDIRECT_OUTARR")
   {
    if($ret['outarr'])
     $lastOutarr[] = $ret['outarr'];
   }
   else if($method == "REDIRECT_OUTPUT")
   {
	//$clp['commands'][$c+1]['args'][] = $ret['htmloutput'] ? $ret['htmloutput'] : $ret['message'];
	$lastOutputMessage = $ret['htmloutput'] ? $ret['htmloutput'] : $ret['message'];
   }
   else
    $messages.= $ret['message']."\n";
  } // EOF - if file exists
  else if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH.$cmd.".js"))
  {
   $ret = array('includejs'=>$_SHELL_CMD_PATH.$cmd.".js",'command'=>$cmd,'arguments'=>$args);
  }
 } // EOF - for $c
 if($ret)
 {
  $ret['message'] = $messages;
  if(count($lastOutarr))
   $ret['redirected_outarr'] = $lastOutarr;

  /* if shell-log is enabled... */
  if(isset($_COOKIE['GNUJIKO-ENABLE-SHELL-LOG']) && $_COOKIE['GNUJIKO-ENABLE-SHELL-LOG'])
   gshCommandLog($cmd, $args, $ret);
  else if(isset($_COOKIE['GNUJIKO-ENABLE-DEBUG']) && $_COOKIE['GNUJIKO-ENABLE-DEBUG'])
   gshSaveDebugLog($cmd, $args, $ret);
  return $ret;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function gshPreOutput($shellid, $output="", $msgType="", $msgRef="", $mode="DEFAULT", $outArr=null)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_DEFAULT_FILE_PERMS;
 if(!file_exists($_BASE_PATH."tmp/shell-$shellid"))
 {
  if(!@mkdir($_BASE_PATH."tmp/shell-$shellid"))
   return false;
  @chmod($_BASE_PATH."tmp/shell-$shellid",$_DEFAULT_FILE_PERMS);
 }
 $xml = "<output>";
 if($output)
 {
  $output = xml_purify($output);
  $xml.= "<message text=\"".str_replace("\n", "&lt;br/&gt;",$output)."\" mode=\"$mode\" type=\"$msgType\" ref=\"$msgRef\"/>";
 }
 if($outArr)
  $xml.= array_to_xml($outArr, 'output_array');
 $xml.= "</output>";

 $fileName = $_BASE_PATH."tmp/shell-$shellid/preoutput.xml";
 $f = @fopen($fileName,"a");
 if($f)
 {
  if(!@fwrite($f,$xml))
  {
   // Try with FTP //
   if($_FTP_USERNAME)
   {
    $conn = @ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
    if($conn && @ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
    {
     if($_FTP_PATH)
     {
	  if(!@ftp_chdir($conn, $_FTP_PATH))
	   return false;
     }
	 $fp = tmpfile();
	 if(!ftp_fput($conn, $fileName, $fp, FTP_BINARY))
	  return false;
	 $f = @fopen($fileName,"a");
	 if(!@fwrite($f,$xml))
	  return false;
	}
	else
	 return false;
   }	
   else
	return false;
  }
  @fclose($f);
  return true;
 }
 else
 {
   // Try with FTP //
   if($_FTP_USERNAME)
   {
    $conn = @ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
    if($conn && @ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
    {
     if($_FTP_PATH)
     {
	  if(!@ftp_chdir($conn, $_FTP_PATH))
	   return false;
     }
	 $fp = tmpfile();
	 if(!@ftp_fput($conn, $fileName, $fp, FTP_BINARY))
	  return false;
	 @ftp_chmod($conn, $_DEFAULT_FILE_PERMS, $fileName);
	 $f = @fopen($fileName,"a");
	 if(!@fwrite($f,$xml))
	  return false;
	}
	else
	 return false;
   }	
   else
	return false;
 }
 return false;
}
//-------------------------------------------------------------------------------------------------------------------//
function gshSecureString($string)
{
 $res = htmlentities($string,ENT_NOQUOTES,"UTF-8");
 $res = str_replace(array("&lt;","&gt;","&quot;","&amp;","\r\n"), array("<",">",'"','&',"<br/>"),$res);
 $what = array("'",'"');
 $with = array("&lsquo;","&quot;");
 return str_replace($what, $with, $res);
}
//-------------------------------------------------------------------------------------------------------------------//
function gshCommandLog($cmd, $args, $ret)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_FTP_SERVER, $_FTP_USERNAME, $_FTP_PASSWORD, $_FTP_PATH, $_DEFAULT_FILE_PERMS;

 $message = xml_purify(substr($ret['message'],0,255));
 $message = str_replace("\n", "",$message);

 $arguments = xml_purify(substr(implode(" ",$args),0,255));
 $arguments = str_replace("\n", "",$arguments);

 $xml = "<shcmd command=\"".$cmd."\" arguments=\"".$arguments."\" time=\"".date('Y-m-d H:i:s')."\" success=\"".($ret['error'] ? "false" : "true")."\" message=\"".$message."\"".($ret['error'] ? " error=\"".$ret['error']."\"" : "")."/>\n";

 $fileName = $_BASE_PATH."tmp/shell-log.xml";

 $f = @fopen($fileName,"a");
 if($f)
 {
  if(!@fwrite($f,$xml))
  {
   // Try with FTP //
   if($_FTP_USERNAME)
   {
    $conn = @ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
    if($conn && @ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
    {
     if($_FTP_PATH)
     {
	  if(!@ftp_chdir($conn, $_FTP_PATH))
	   return false;
     }
	 $fp = tmpfile();
	 if(!ftp_fput($conn, $fileName, $fp, FTP_BINARY))
	  return false;
	 $f = @fopen($fileName,"a");
	 if(!@fwrite($f,$xml))
	  return false;
	}
	else
	 return false;
   }	
   else
	return false;
  }
  @fclose($f);
  return true;
 }
 else
 {
   // Try with FTP //
   if($_FTP_USERNAME)
   {
    $conn = @ftp_connect($_FTP_SERVER ? $_FTP_SERVER : $_SERVER['SERVER_NAME']);
    if($conn && @ftp_login($conn,$_FTP_USERNAME,$_FTP_PASSWORD))
    {
     if($_FTP_PATH)
     {
	  if(!@ftp_chdir($conn, $_FTP_PATH))
	   return false;
     }
	 $fp = tmpfile();
	 if(!ftp_fput($conn, $fileName, $fp, FTP_BINARY))
	  return false;
	 @ftp_chmod($conn, $_DEFAULT_FILE_PERMS, $fileName);
	 $f = @fopen($fileName,"a");
	 if(!@fwrite($f,$xml))
	  return false;
	}
	else
	 return false;
   }	
   else
	return false;
 }
 return true;
}
//-------------------------------------------------------------------------------------------------------------------//
function gshSaveDebugLog($cmd, $args, $ret)
{
 if(!$ret['error'] && (!isset($_COOKIE['GNUJIKO-ENABLE-DEBUG-ALLGSHCMD']) || !$_COOKIE['GNUJIKO-ENABLE-DEBUG-ALLGSHCMD']))
  return;

 if($args && is_array($args))
  $cmd = $cmd." ".str_replace("\n", "",implode(" ",$args));

 $ctime = date('Y-m-d H:i:s');
 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO system_debug(ctime,log_type,log_query,ret_message,ret_errcode,success) VALUES('"
	.$ctime."','GSH','".$db->Purify($cmd)."','".$db->Purify($ret['message'])."','".$ret['error']."','".($ret['error'] ? '0' : '1')."')");
 $db->Close();
}
//-------------------------------------------------------------------------------------------------------------------//


