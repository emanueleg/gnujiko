<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-06-2013
 #PACKAGE: sendmail
 #DESCRIPTION: Command line send mail.
 #VERSION: 2.2beta
 #CHANGELOG: 05-06-2013 - Ora si possono specificare più email separate da una virgola.
			 31-05-2013 - Bug fix in passbyreference.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_USERS_HOMES;
include_once($_BASE_PATH."include/userfunc.php");

function shell_sendmail($args, $sessid, $shellid=null)
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
	$attachments[$c] = $attachments[$c];
   else
    $attachments[$c] = $basepath.$attachments[$c];
   $out.= $attachments[$c]."\n";
  }
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
 

 if(!mosMail($mailFrom, $fromName, $recpArr, $subject, $message, true, $cc, $bcc, $attachments, $replyTo, $replyToName))
 {
  $err = "MAIL_SEND_FAILED";
  $out = "Unable to send mail.\n";
  return array('message'=>$out,'error'=>$err);
 }
 $out.= "done!\n";
 return array('message'=>$out);
}


