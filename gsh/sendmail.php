<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-06-2016
 #PACKAGE: sendmail
 #DESCRIPTION: Command line send mail.
 #VERSION: 2.8beta
 #CHANGELOG: 11-06-2016 : Possibilita di specificare piu destinatari su argomento -recp separati da un ; (punto e virgola)
			 22-02-2016 : Encode subject
			 12-03-2015 : Possibilità di allegare file fuori dalle home solo su specifiche dir.
			 14-10-2014 : Aggiunto extraVar per opzioni varie.
			 01-09-2014 : Bug fix vari.
			 02-04-2014 - Abilitato error tracking.
			 05-06-2013 - Ora si possono specificare più email separate da una virgola.
			 31-05-2013 - Bug fix in passbyreference.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_USERS_HOMES;
include_once($_BASE_PATH."include/userfunc.php");

function shell_sendmail($args, $sessid, $shellid=null, $extraVar=null)
{
 global $_BASE_PATH, $_USERS_HOMES;

 $cc = array();
 $bcc = array();
 $attachments = array();

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$mailFrom = $args[$c+1]; $c++;} break;
   case '-fromname' : {$fromName = $args[$c+1]; $c++;} break;
   case '-to' : case '-recp' : {$recp = $args[$c+1]; $c++;} break;
   case '-cc' : {$cc[] = $args[$c+1]; $c++;} break;
   case '-bcc' : {$bcc[] = $args[$c+1]; $c++;} break;
   case '--reply-to' : {$replyTo = $args[$c+1]; $c++;} break;
   case '--reply-to-name' : {$replyToName = $args[$c+1]; $c++;} break;
   case '-subject' : {$subject = $args[$c+1]; $c++;} break;
   case '-message' : {$message = $args[$c+1]; $c++;} break;
   case '-attachment' : {$attachments[] = ltrim($args[$c+1],"/"); $c++;} break;
   default: {if(!$recp) $recp = $args[$c];} break;
  }
 if(!file_exists($_BASE_PATH."etc/mail.php"))
 {
  $err = "MAIL_PHP_NOT_FOUND";
  $out.= "Unable to send email. Miss library (/etc/mail.php)\n";
  return array('message'=>$out,'error'=>$err);
 }

 if($recp)
 {
  $sep = ";";
  $x = explode($sep, $recp);

  for($c=0; $c < count($x); $c++)
  {
   $str = $x[$c];
   $email = trim($str);

   // 1 - if into <  >
   if((($s = strpos($str, "<")) !== false) && (($e = strpos($str, ">")) !== false)) { $l = $e-$s; $email = trim(substr($str, $s+1, $l-1)); }
   // 2 - Email check
   if((strpos($email, "@") === false) || (strpos($email, ".") === false)) continue;
   // 3 - Process
   if($c == 0) $recp = $email; else $cc[] = $email;
  }

 }


 $_ACCESS_DIR = array("share/");			/* Lista directory accessibili */
 $_BLACKLIST = array("share/widgets/");		/* Lista directory non accessibili */
 $_BLACKEXT = array(".php", ".js");			/* Lista estensioni non ammesse */

 if(count($attachments))
 {
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
  
  for($c=0; $c < count($attachments); $c++)
  {
   if(substr($attachments[$c],0,strlen($basepath)) == $basepath)
   {
	$attachments[$c] = $attachments[$c];
   }
   else
   {
	if(file_exists($basepath.$attachments[$c]))
     $attachments[$c] = $basepath.$attachments[$c];
	else
	{
	 $filename = basename($attachments[$c]);
	 $basedir = substr($attachments[$c], 0, -strlen($filename));

 	 for($i=0; $i < count($_BLACKLIST); $i++)
	 {
  	  $dir = $_BLACKLIST[$i];
  	  $pos = strpos($basedir,$dir);
  	  if(($pos !== false) && ($pos == 0))
   	   return array('message'=>"Unable to attach file: ".$attachments[$c].". Permission Denied!","error"=>"ATTACH_PERMISSION_DENIED");
 	 }
 	 $_OK = false;
 	 for($i=0; $i < count($_ACCESS_DIR); $i++)
 	 {
  	  $dir = $_ACCESS_DIR[$i];
  	  $pos = strpos($basedir,$dir);
  	  if(($pos !== false) && ($pos == 0))
  	  {
   	   $_OK = true;
   	   break;
  	  }
 	 }

 	 if(!$_OK)
  	  return array('message'=>"Unable to attach file: ".$attachments[$c].". Permission Denied!","error"=>"ATTACH_PERMISSION_DENIED");

 	 for($i=0; $i < count($_BLACKEXT); $i++)
 	 {
  	  if(strrpos($fileName, $_BLACKEXT[$i]) !== false)
   	   return array('message'=>"Unable to attach file: ".$attachments[$c].". Permission Denied!","error"=>"ATTACH_PERMISSION_DENIED");
 	 }
 	 if(file_exists($_BASE_PATH.$attachments[$c]))
	  $attachments[$c] = $_BASE_PATH.$attachments[$c];
	 else
	  unset($attachments[$c]);
	}

   }
   $out.= $attachments[$c]."\n";
  }
 }

 if(is_array($extraVar) && count($extraVar['attachments']))
 {
  // inserisce allegati esterni 
  // Tramite extraVar è possibile specificare gli allegati da includere nella mail anche se non interni alla propria home.
  for($c=0; $c < count($extraVar['attachments']); $c++)
   $attachments[] = $_BASE_PATH.$extraVar['attachments'][$c];
 }

 include_once($_BASE_PATH."etc/mail.php");

 $recpArr = array();
 $x = explode(",",$recp);
 if(!count($x))
  $recpArr[] = $recp;
 else
 {
  for($c=0; $c < count($x); $c++)
   $recpArr[] = trim($x[$c]);
 }
 
 if(!$mailFrom)
  $mailFrom = $_SESSION['EMAIL'];
 if(!$fromName)
  $fromName = $_SESSION['FULLNAME'] ? $_SESSION['FULLNAME'] : $_SESSION['UNAME'];
 if(!$mailFrom)
  return array("message"=>"Sendmail error: Devi specificare l'indirizzo email del mittente.", "error"=>"INVALID_SENDER");

 // encode subject
 $preferences = array("input-charset" => "UTF-8", "output-charset"=>"UTF-8");
 $encodedSubject = ltrim(iconv_mime_encode("Subject", $subject, $preferences), "Subject:");

 $ret = mosMail($mailFrom, $fromName, $recpArr, $encodedSubject, $message, true, $cc, $bcc, $attachments, $replyTo, $replyToName);
 if($ret['error'])
 {
  // Try without encoded subject
  $ret = mosMail($mailFrom, $fromName, $recpArr, $subject, $message, true, $cc, $bcc, $attachments, $replyTo, $replyToName);
 }

 if($ret['error'])
 {
  $err = "MAIL_SEND_FAILED";
  $out = "Unable to send mail.\nPHPMailer error: ".$ret['message'];
  return array('message'=>$out,'error'=>$err);
 }
 $out.= "Email has been sent to ".$recp."!\n";
 return array('message'=>$out);
}


