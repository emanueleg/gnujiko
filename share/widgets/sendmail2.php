<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 12-09-2016
 #PACKAGE: sendmail-gui
 #DESCRIPTION: Simply widget form for send mail
 #VERSION: 2.6beta
 #CHANGELOG: 12-09-2016 : Remake grafico con gnujiko template ed aggiunto funzionalita.
			 25-09-2015 : Lista email selezionabili su campo destinatario.
			 13-03-2015 : Aggiunto parser.
			 28-05-2014 : Aggiunto firma.
			 12-07-2013 : Possibilità di inviare email a più contatti.
			 19-04-2013 : Aggiornato un po di tutto: finestra più larga, editor word integrato, ecc...
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);
include($_BASE_PATH.'var/templates/gnujiko/index.php');

$_TITLE = (isset($_REQUEST['title']) && $_REQUEST['title']) ? $_REQUEST['title'] : "Invia email";
$_ATTACH_PATH = "sendmail/tmp/";
$_ATTACH_COUNT = 0;

$_SENDMAIL_SENDER = (isset($_REQUEST['sender']) && $_REQUEST['sender']) ? $_REQUEST['sender'] : $_SESSION['FULLNAME']." <".$_SESSION['EMAIL'].">";
$_SENDMAIL_RECP = (isset($_REQUEST['recp']) && $_REQUEST['recp']) ? $_REQUEST['recp'] : "";
$_SENDMAIL_SUBJID = (isset($_REQUEST['subjid']) && $_REQUEST['subjid']) ? $_REQUEST['subjid'] : 0;
$_SENDMAIL_RECPS = array();
$_SENDMAIL_SUBJECT = (isset($_REQUEST['subject']) && $_REQUEST['subject']) ? $_REQUEST['subject'] : "";
if(!$_SENDMAIL_SUBJECT)	$_SENDMAIL_SUBJECT = (isset($_REQUEST['title']) && $_REQUEST['title']) ? $_REQUEST['title'] : "";
$_SENDMAIL_CC = (isset($_REQUEST['cc']) && $_REQUEST['cc']) ? $_REQUEST['cc'] : "";
$_SENDMAIL_BCC = (isset($_REQUEST['bcc']) && $_REQUEST['bcc']) ? $_REQUEST['bcc'] : "";
$_SENDMAIL_SIGNATURE = "";

$_SENDMAIL_MSGAP = (isset($_REQUEST['msgap']) && $_REQUEST['msgap']) ? $_REQUEST['msgap'] : "";
$_SENDMAIL_MSGID = (isset($_REQUEST['msgid']) && $_REQUEST['msgid']) ? $_REQUEST['msgid'] : 0;
$_SENDMAIL_PARSER = (isset($_REQUEST['parser']) && $_REQUEST['parser']) ? $_REQUEST['parser'] : "";


$template = new GnujikoTemplate("default","default",null);
$template->includeObject('fckeditor');
$template->includeInternalObject("attachments");

$config = array();
$config['options'] = array();
$ret = GShell("aboutconfig get-config -app sendmail",$_REQUEST['sessid'],$_REQUEST['shellid']);
if(!$ret['error'])
{
 $config = $ret['outarr']['config'];
 if(!is_array($config['options'])) $config['options'] = array();

 if(!$_SENDMAIL_CC && $config['options']['default_cc'])		$_SENDMAIL_CC = $config['options']['default_cc'];
 if(!$_SENDMAIL_BCC && $config['options']['default_bcc'])	$_SENDMAIL_BCC = $config['options']['default_bcc'];
}

// GET RECP SUBJECT INFO
if(!$_SENDMAIL_RECP && $_SENDMAIL_SUBJID)
{
 /* Get subject info */
 $ret = GShell("dynarc item-info -ap rubrica -id '".$_SENDMAIL_SUBJID."' -extget `rubricainfo,contacts`",$_REQUEST['sessid'],$_REQUEST['shellid']);
 if(!$ret['error'])
 {
  $subjectInfo = $ret['outarr'];
  if($subjectInfo['default_email']) 	$_SENDMAIL_RECP = $subjectInfo['default_email'];
  else if($subjectInfo['contacts'][0]) 	$_SENDMAIL_RECP = $subjectInfo['contacts'][0]['email'];
  if($subjectInfo['default_email'])		$_SENDMAIL_RECPS[$subjectInfo['default_email']] = $subjectInfo['default_email'];
  for($c=0; $c < count($subjectInfo['contacts']); $c++)
  {
   $email = $subjectInfo['contacts'][$c]['email'];
   if($email && !$_SENDMAIL_RECPS[$email])	$_SENDMAIL_RECPS[$email] = $email;
   $email = $subjectInfo['contacts'][$c]['email2'];
   if($email && !$_SENDMAIL_RECPS[$email])	$_SENDMAIL_RECPS[$email] = $email;
   $email = $subjectInfo['contacts'][$c]['email3'];
   if($email && !$_SENDMAIL_RECPS[$email])	$_SENDMAIL_RECPS[$email] = $email;
  }
 }
}

// PARSERIZE MESSAGE
if($_SENDMAIL_PARSER)
{
 $cmd = "parserize -p '".$_SENDMAIL_PARSER."' -params 'id=".$_REQUEST['id']."'"
	.(($_SENDMAIL_MSGAP && $_SENDMAIL_MSGID) ? " -ap '".$_SENDMAIL_MSGAP."' -id '".$_SENDMAIL_MSGID."'" : " -c `".$_REQUEST['contents']."`");
 $ret = GShell($cmd, $_REQUEST['sessid'], $_REQUEST['shellid']);
 $_SENDMAIL_MESSAGE = $ret['message'];

 if($_SENDMAIL_SUBJECT && $_REQUEST['parsesubject'])
 {
  $cmd = "parserize -p '".$_SENDMAIL_PARSER."' -params 'id=".$_REQUEST['id']."' -c `".$_SENDMAIL_SUBJECT."`";
  $ret = GShell($cmd, $_REQUEST['sessid'], $_REQUEST['shellid']);
  $_SENDMAIL_SUBJECT = $ret['message'];
 }

}
else if($_SENDMAIL_MSGAP && $_SENDMAIL_MSGID)
{
 $ret = GShell("dynarc item-info -ap `".$_SENDMAIL_MSGAP."` -id `".$_SENDMAIL_MSGID."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
 if(!$ret['error'])
  $_SENDMAIL_MESSAGE = str_replace("{ABSOLUTE_URL}",$_ABSOLUTE_URL,$ret['outarr']['desc']);
}
else
 $_SENDMAIL_MESSAGE = $_REQUEST['contents'];

// GET SIGNATURE
if($config['firm'] && $config['firm']['ap'] && $config['firm']['id'])
{
 $ret = GShell("dynarc item-info -ap '".$config['firm']['ap']."' -id '".$config['firm']['id']."'",$_REQUEST['sessid'],$_REQUEST['shellid']);
 if(!$ret['error']) $_SENDMAIL_SIGNATURE = $ret['outarr']['desc'];
}


$template->StartWidget($_TITLE, 708);

//-------------------------------------------------------------------------------------------------------------------//
?>

<!-- HEADER -->
<div class="widget-header"><?php echo $template->config['title']; ?> <input type='button' class='widget-button-close' onclick='gframe_close()'/></div>

<!-- BOBY -->
<div class="widget-section" style="padding:0px;border-bottom:0px">
 <table width='100%' cellspacing='0' cellpadding='0' border='0' class='form'>
  <tr><td width='22'>A</td>
	  <td><input type='text' class="edit noborder nopadding" placeholder="Destinatario" id='recp' style='width:100%' value="<?php echo $_SENDMAIL_RECP; ?>" retval="<?php echo $_SENDMAIL_RECP; ?>" connect="recp-list"/>
	   <?php
		if(!empty($_SENDMAIL_RECPS))
		{
		 $menu = array();
		 reset($_SENDMAIL_RECPS);
		 while(list($k,$v) = each($_SENDMAIL_RECPS))
		  $menu[] = array('title'=>$k, 'value'=>$k);
		 echo $template->generatePopupMenu($menu, "recp-list", "select");
		}
	   ?>
	  </td>
	  <td width='22' style='padding-left:0px;padding-right:0px' id='cc-tablecell'>&nbsp;</td>
	  <td width='60'><input type='text' class="edit noborder nopadding" id="cc" value="<?php echo $_SENDMAIL_CC; ?>" style='width:100%;display:none'/></td>
	  <td valign='top' rowspan='4' style='border-bottom:0px'>
	   
	    <?php
		 $Attachments = new GLAttachments();
		 if(isset($_REQUEST['attachment']) && $_REQUEST['attachment'])
		  $Attachments->AddFile($_REQUEST['attachment']);
		 else if(isset($_REQUEST['attcount']) && $_REQUEST['attcount'])
		 {
		  for($c=0; $c < count($_REQUEST['attcount']); $c++)
		  {
		   $file = $_REQUEST["att".($c+1)];
		   if($file) $Attachments->AddFile($file);
		  }
		 }

		 $Attachments->height = "400px";
		 $_ATTACH_COUNT = count($Attachments->Items);

		 echo "<div id='attachments-container' style='width:168px;padding:0px;border-bottom:0px;height:400px;display:none'>";
		 $Attachments->Paint(false, "allegati");
		 echo "</div>";
	 
	    ?>

	  </td>
  </tr>
  <tr><td>DA</td>
	  <td><input type='text' class="edit noborder nopadding" placeholder="Mittente" id='sender' style='width:100%' value="<?php echo $_SENDMAIL_SENDER; ?>"/></td>
	  <td width='22' style='padding-left:0px;padding-right:0px' id='bcc-tablecell'>&nbsp;</td>
	  <td>
	   <div style='color:#dadada'>
		<span class="link" onclick="showCCBCC('cc')">Cc</span> | <span class="link" onclick="showCCBCC('bcc')">Ccn</span>
	   </div>
	   <input type='text' class="edit noborder nopadding" id='bcc' value="<?php echo $_SENDMAIL_BCC; ?>" style='display:none;width:100%'/>
	  </td>
  </tr>
 
  <tr><td colspan='4'><input type='text' class="edit noborder nopadding" placeholder="Oggetto" id='subject' style='width:100%' value="<?php echo $_SENDMAIL_SUBJECT; ?>"/></td></tr>

  <tr><td colspan='4' class='full' style='border-bottom:0px'>
		<textarea class="textarea noborder" placeholder="Digita qui il tuo messaggio" id='message' style="height:360px"><?php 
		 echo $_SENDMAIL_MESSAGE; 
		?></textarea>
		<div id="signature" style="border:1px solid #696969;height:100px;display:none;margin:1px;overflow:auto">
		 <?php echo $_SENDMAIL_SIGNATURE; ?>
		</div>
	  </td>
  </tr>

  <?php
  if($_SENDMAIL_SIGNATURE)
  {
   ?>
  <tr><td colspan='5' style="border-bottom:0px;border-top: 1px solid #cfcfcf">
	   <input type='checkbox' checked='true' id='includesign'/> includi <span class="link blue" onclick="showHideSignature()">firma digitale</span>
	  </td>
  </tr>
   <?php
  }
  ?>

 </table>
</div>
<!-- EOF - BODY

<!-- FOOTER -->
<?php
$_FOOTER_MENU = array();
$_FOOTER_MENU[] = array('title'=>'Carica messaggio predefinito', 'icon'=>$_ABSOLUTE_URL.'share/icons/16x16/file_open.gif', 
	'onclick'=>'loadPredefMessage()');
$_FOOTER_MENU[] = array('title'=>'Salva come messaggio predefinito', 'icon'=>$_ABSOLUTE_URL.'share/icons/16x16/save.gif', 
	'onclick'=>'saveAsPredefMessage()');

$tmp = array('title'=>'Messaggi predefiniti recenti', 'items'=>array());

$ret = GShell("dynarc item-list -ap sendmail_predmsg --all-cat --order-by 'last_time_used DESC' -limit 10", $_REQUEST['sessid'], $_REQUEST['shellid']);
if(!$ret['error'] && count($ret['outarr']['items']))
{
 for($c=0; $c < count($ret['outarr']['items']); $c++)
  $tmp['items'][] = array('title'=>$ret['outarr']['items'][$c]['name'], 'onclick'=>"loadPredefMessage(".$ret['outarr']['items'][$c]['id'].")");
}
if(count($tmp['items']))
 $_FOOTER_MENU[] = $tmp;

$_FOOTER_MENU[] = array('type'=>'separator');
$_FOOTER_MENU[] = array('title'=>'Configurazione', 'icon'=>$_ABSOLUTE_URL.'share/icons/16x16/cog.gif', 'onclick'=>'gotoAboutConfig()');
$_FOOTER_MENU[] = array('title'=>'Esci', 'icon'=>$_ABSOLUTE_URL.'share/icons/16x16/door_open.gif', 'onclick'=>'gframe_close()');

?>
<div class="widget-footer" style="border-top: 1px solid #cfcfcf">
 <input type='button' class='button-blue' value="Invia" onclick="Submit()"/>
 <ul class='footer-menu'>
  <li><input type='button' class='upload-icon' title="Carica un allegato" onclick="addAttachment()"/></li>
  <li><input type='button' class='trash-icon' title='Annulla' onclick="gframe_close()"/></li>
  <li class='separator'></li>
  <li><input type='button' class='menu-icon' connect='menu' id='menubutton'/></li>
 </ul>
 <?php
  echo $template->generatePopupMenu($_FOOTER_MENU, 'menu');
 ?>
</div>
<!-- EOF - FOOTER -->
<?php
//-------------------------------------------------------------------------------------------------------------------//
?>
<script>
var ATTACH = null;
var ATTACHPATH = "<?php echo $_ATTACH_PATH; ?>";
var ATTACH_COUNT = <?php echo $_ATTACH_COUNT ? $_ATTACH_COUNT : '0'; ?>;

Template.OnInit = function()
{
 this.initBtn(document.getElementById('menubutton'), 'menu');
 if(document.getElementById('recp-list'))
 {
  document.getElementById('recp').className = document.getElementById('recp').className.replace("edit","dropdown");
  this.initEd(document.getElementById('recp'), 'dropdown');
  document.getElementById('recp').readOnly = false;
 }

 this.initEd(document.getElementById('message'), 'fckeditor', 'Small');

 ATTACH = new GLAttachments(null, 0, 0, document.getElementById('attachments-container'));
 ATTACH.OnUpload = function(){
	 showAttachments(true);
	}

 ATTACH.OnReload = function(){
	 var list = this.getAttachments();
	 showAttachments(list.length);
	}

 ATTACH.OnDelete = function(){
	 var list = this.getAttachments();
	 showAttachments(list.length);
	}

 if(ATTACH_COUNT) showAttachments(true);

 if(document.getElementById('cc').value || document.getElementById('bcc').value)
  showCCBCC();
}

function Submit()
{
 var sender = document.getElementById('sender').value;
 var senderName = "";
 var ccList = new Array();
 var bccList = new Array();

 if(sender.indexOf("<") > 0)
 {
  var sp = sender.indexOf("<");
  var ep = sender.indexOf(">");
  senderName = sender.substr(0,sp).trim();
  sender = sender.substr(sp+1, (ep-sp)-1);
 }

 var recp = document.getElementById('recp').value.trim();
 var subject = document.getElementById('subject').value;
 var msg = document.getElementById('message').getValue();
 var includeSign = (document.getElementById('includesign') && (document.getElementById('includesign').checked == true)) ? true : false;
 var cc = document.getElementById('cc').value;
 var bcc = document.getElementById('bcc').value;

 if(cc.indexOf(",") > 0)	ccList = cc.split(','); 	else if(cc.indexOf(";") > 0)	ccList = cc.split(';');		else if(cc != "")	ccList.push(cc);
 if(bcc.indexOf(",") > 0)	bccList = bcc.split(','); 	else if(bcc.indexOf(";") > 0)	bccList = bcc.split(';');	else if(bcc != "")	bccList.push(bcc);


 // CHECKS
 if(!recp || (recp.trim() == "")) 	return alert("Devi specificare il destinatario.");
 if(!subject || (subject == ""))	return alert("Devi specificare l'oggetto del messaggio.");
 if(!msg || (msg == ""))			return alert("Il messaggio è vuoto!"); 


 // ATTACHMENTS
 var list = ATTACH.getAttachments();
 var attachQ = "";
 for(var c=0; c < list.length; c++)
  attachQ+= " -attachment `"+list[c]+"`";

 if(includeSign) msg+= document.getElementById('signature').innerHTML;

 // SEND
 var sh = new GShell();
 sh.showProcessMessage("Invio email in corso", "Attendere prego, è in corso l'invio della email");
 sh.OnError = function(err,errcode){this.processMessage.error(err);}
 sh.OnOutput = function(o,a){
	 this.hideProcessMessage();
	 alert("Il messaggio è stato inviato!");
	 gframe_close(o,a);
	}
 
 var cmd = "sendmail -from `"+sender+"` -fromname `"+senderName+"` -to `"+recp+"` -subject `"+subject+"` -message `"+msg+"`"+attachQ;
 for(var c=0; c < ccList.length; c++)	cmd+= " -cc `"+ccList[c].trim()+"`";
 for(var c=0; c < bccList.length; c++)	cmd+= " -bcc `"+bccList[c].trim()+"`";

 //alert(cmd);
 //sh.hideProcessMessage();

 sh.sendCommand(cmd);
}

function gframe_cachecontentsload(contents)
{
 document.getElementById('message').setValue(contents);
}

function addAttachment()
{
 ATTACH.upload(ATTACHPATH);
}

function showAttachments(bool)
{
 document.getElementById('attachments-container').parentNode.style.width = bool ? "168px" : "";
 document.getElementById('attachments-container').style.display = bool ? "" : "none";
 document.getElementById('message').setSize(bool ? '500px' : '100%');
}

function showHideSignature()
{
 var div = document.getElementById('signature');
 var bool = (div.style.display == "none") ? true : false;
 document.getElementById('message').setSize(null, bool ? "256px" : "360px");
 div.style.display = bool ? "" : "none";
}

function showCCBCC(focus)
{
 var cc = document.getElementById('cc');
 cc.parentNode.style.width = "auto";
 cc.style.display = "";

 var bcc = document.getElementById('bcc');
 var div = bcc.parentNode.getElementsByTagName('DIV')[0];
 div.parentNode.removeChild(div);
 bcc.style.display = "";

 document.getElementById('cc-tablecell').innerHTML = "CC";
 document.getElementById('bcc-tablecell').innerHTML = "CCN";

 if(focus)
  document.getElementById(focus).focus();
}

function gotoAboutConfig()
{
 window.open(ABSOLUTE_URL+"aboutconfig/sendmail/index.php", "_blank");
}

function loadPredefMessage(id)
{
 var subject = document.getElementById('subject').value;
 var now = new Date();
 if(id)
 {
  var sh = new GShell();
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,a){
		 document.getElementById('message').setValue(a['desc']);
		 if(!subject || (subject == ""))
		  document.getElementById('subject').value = a['name'];
		}
  sh.sendCommand("dynarc edit-item -ap sendmail_predmsg -id '"+id+"' -set `last_time_used='"+now.printf('Y-m-d H:i:s')+"'`");
 }
 else
 {
  var sh = new GShell();
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,id){
	 if(!id) return;
	 return loadPredefMessage(id);
	}
  sh.sendCommand("gframe -f dynarc.navigator -params `ap=sendmail_predmsg`");
 }
}

function saveAsPredefMessage()
{
 var subject = document.getElementById('subject').value;
 var title = prompt("Digita un titolo", subject);
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 alert('Messaggio salvato!');
	}
 sh.sendCommand("dynarc new-item -ap sendmail_predmsg -group sendmail -name `"+title+"` -desc `"+document.getElementById('message').getValue()+"`");
}

</script>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$template->EndWidget();
//-------------------------------------------------------------------------------------------------------------------//
?>
