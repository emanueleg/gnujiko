<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 16-09-2016
 #PACKAGE: sendmail-gui
 #DESCRIPTION: Simply widget form for send mail
 #VERSION: 2.6beta
 #CHANGELOG: 16-09-2016 : Nuova versione GUI di sendmail.
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

include_once($_BASE_PATH."include/gshell.php");
$config = array();
$ret = GShell("aboutconfig get-config -app sendmail",$_REQUEST['sessid'],$_REQUEST['shellid']);
if(!$ret['error'])
{
 $config = $ret['outarr']['config'];
 if(!is_array($config['options']) || !$config['options']['useoldguiversion'])
 {
  if(file_exists($_BASE_PATH."share/widgets/sendmail2.php"))
  {
   include($_BASE_PATH."share/widgets/sendmail2.php");
   exit();
  }
 }

}


?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Send Mail</title>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/sendmail/sendmail.css" type="text/css" />
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."var/objects/fckeditor/index.php");
include_once($_BASE_PATH."var/objects/editsearch/index.php");

$form = new GForm("Invia email", "MB_OK|MB_ABORT", "simpleform", "default", "orange", 700, 500);
$form->Begin($_ABSOLUTE_URL."share/widgets/sendmail/img/icon.png");
echo "<div id='contents' style='padding:5px'>";

/* PARAMETERS */
$_SENDMAIL_SENDER = $_REQUEST['sender'] ? $_REQUEST['sender'] : $_SESSION['FULLNAME']." <".$_SESSION['EMAIL'].">";
$_SENDMAIL_RECP = $_REQUEST['recp'];
$_SENDMAIL_SUBJID = $_REQUEST['subjid'];
$_SENDMAIL_RECPS = array();

if(!$_SENDMAIL_RECP && $_REQUEST['subjid'])
{
 /* Get subject info */
 $ret = GShell("dynarc item-info -ap rubrica -id '".$_REQUEST['subjid']."' -extget `rubricainfo,contacts`",$_REQUEST['sessid'],$_REQUEST['shellid']);
 if(!$ret['error'])
 {
  $subjectInfo = $ret['outarr'];
  if($subjectInfo['default_email'])
   $_SENDMAIL_RECP = $subjectInfo['default_email'];
  else if($subjectInfo['contacts'][0])
   $_SENDMAIL_RECP = $subjectInfo['contacts'][0]['email'];

  if($subjectInfo['default_email'])
   $_SENDMAIL_RECPS[$subjectInfo['default_email']] = $subjectInfo['default_email'];
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

$_SENDMAIL_SUBJECT = $_REQUEST['subject'] ? $_REQUEST['subject'] : $_REQUEST['title'];

$_SENDMAIL_MSGAP = $_REQUEST['msgap'];
$_SENDMAIL_MSGID = $_REQUEST['msgid'];
$_SENDMAIL_PARSER = $_REQUEST['parser']; 

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

?>
<table width='100%' cellspacing='0' cellpadding='0' border='0'>
<tr><td valign='top'>
	 <div class='emailbg'>
	  <p><span class='title'>Mittente:</span><input type='text' class='text' style="width:280px" id='sender' value="<?php echo $_SENDMAIL_SENDER; ?>"/></p>
	  <p><span class='title'>Destinatario:</span><input type='text' class="text dropdown" style="width:230px" id='recp' value="<?php echo $_SENDMAIL_RECP; ?>" retval="<?php echo $_SENDMAIL_RECP; ?>" connect='recp-list'/>
		<img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/sendmail/img/contact-add.png" class="contactaddbtn" title="Cerca o seleziona più contatti" onclick="showContactFinder()"/>
		<ul class='popupmenu' id='recp-list'>
		 <?php
		  reset($_SENDMAIL_RECPS);
		  while(list($k,$v) = each($_SENDMAIL_RECPS))
		   echo "<li value='".$k."'>".$k."</li>";
		 ?>
		</ul>
	  
	  </p>
	 </div>
	  <p style="padding:5px;border-bottom:1px solid #dadada;margin-bottom:5px"><span class='title'>Oggetto:</span> <input type='text' class='text' style="width:400px" id='subject' value="<?php echo $_SENDMAIL_SUBJECT; ?>"/></p>
	 <span class='title'>Messaggio:</span><br/><textarea style="width:100%;height:260px;" id='message'><?php echo $_SENDMAIL_MESSAGE; ?></textarea>
	  <?php
		if($config['firm'] && $config['firm']['ap'] && $config['firm']['id'])
		{
		 $ret = GShell("dynarc item-info -ap '".$config['firm']['ap']."' -id '".$config['firm']['id']."'",$_REQUEST['sessid'],$_REQUEST['shellid']);
		 if(!$ret['error'])
		 {
		  $firmMessage = $ret['outarr'];
		  echo "<br/><br/><span class='smalltext'>Firma</span>";
		  echo "<div class='firm' id='firm-message'>".$firmMessage['desc']."</div>";
		 }
		}
	  ?>

	</td>
	<td valign='top' width='150' style="border-left:1px solid #dadada;background:#fafafa">
	 <div class='attachments-outer'>
	  <span class='title'><b>Allegati</b></span>
	  <div id='attachments-list'>
	  <?php
	  if($_REQUEST['attachment'])
	  {
	   include_once($_BASE_PATH."etc/mimetypes.php");
	   $file = $_REQUEST['attachment'];
	   $ext = strtolower(substr($file, strrpos($file, '.')+1));
	   if($mimetypes[$ext])
   		$type = $mimetypes[$ext];
	   $icon = getMimetypeIcons($type);
	   echo "<div class='attachment' path=\"".$file."\">";
	   echo "<img src='".$_ABSOLUTE_URL."share/widgets/sendmail/img/delete.png' class='removebtn' onclick='deleteAttachment(this.parentNode)'/>";
	   if($icon && $icon['size22x22'])
		echo "<img src='".$_ABSOLUTE_URL.$icon['size22x22']."'/>";
	   else
		echo "<img src='".$_ABSOLUTE_URL."share/mimetypes/22x22/file.png'/>";
	   echo "<a href='".($type != "WEB" ? $_ABSOLUTE_URL : "").$file."' target='blank'>".basename($file)."</a>";
	   echo "</div>";
	  }
	  else if($_REQUEST['attcount'])
	  {
	   include_once($_BASE_PATH."etc/mimetypes.php");
	   for($c=0; $c < $_REQUEST['attcount']; $c++)
	   {
	    $file = $_REQUEST["att".($c+1)];
	    $ext = strtolower(substr($file, strrpos($file, '.')+1));
	    if($mimetypes[$ext])
   		 $type = $mimetypes[$ext];
	    $icon = getMimetypeIcons($type);
	    echo "<div class='attachment' path=\"".$file."\">";
	    echo "<img src='".$_ABSOLUTE_URL."share/widgets/sendmail/img/delete.png' class='removebtn' onclick='deleteAttachment(this.parentNode)'/>";
	    if($icon && $icon['size22x22'])
		 echo "<img src='".$_ABSOLUTE_URL.$icon['size22x22']."'/>";
	    else
		 echo "<img src='".$_ABSOLUTE_URL."share/mimetypes/22x22/file.png'/>";
	    echo "<a href='".($type != "WEB" ? $_ABSOLUTE_URL : "").$file."' target='blank'>".basename($file)."</a>";
	    echo "</div>";	    
	   }
	  }
	  ?>
	  </div>
	  <br/>
	  <input type='button' value='Carica' onclick='uploadFile()'/>
	 </div>
	</td></tr>
</table>

<!-- CONTACT FINDER -->
<div id="contactfinder" style="display:none">
 <table width='490' style="margin-left:5px;margin-top:20px;border-bottom:1px solid #dadada" cellspacing='0' cellpadding='0' border='0'>
  <tr><td align='center'><span class='searchspan'>Cerca:</span> <input type='text' class='text' style='width:280px;' id='contact-finder-search' onchange='contactFinderChange(this)'/></td></tr>
  <tr><td align='center'><div id='contact-finder-catlist'><?php
		 $ret = GShell("dynarc cat-list -ap rubrica",$_REQUEST['sessid'],$_REQUEST['shellid']);
		 $list = $ret['outarr'];
		 for($c=0; $c < count($list); $c++)
		  echo "<input type='radio' name='rubcat' value=\"".$list[$c]['name']."\" id='".$list[$c]['id']."'".($c==0 ? " checked='true'/>" : "/>").$list[$c]['name']."&nbsp;&nbsp;";
		?></div></td></tr>
 </table>
 <div style="width:250px;overflow:hidden;height:25px;white-space:nowrap;float:left;padding-left:10px;margin-top:10px">
  <span class='bigblue'>Categoria:</span> <span id='rubcat-title'><?php echo $list[0]['name']; ?></span>
 </div>
 <div style="width:230px;overflow:hidden;height:25px;white-space:nowrap;float:right;padding-right:10px;margin-top:10px">
  <span class='smallblue' style='cursor:pointer' onclick="selectAllContacts()">Seleziona tutti i contatti di questa categoria</span>
 </div>

 <div style="height:150px;overflow:auto;width:490px;margin-left:5px;">
 <table id="contact-list" width='100%' cellspacing='0' cellpadding='0' border='0'>
  <tr><th width='32' style='text-align:center'><input type='checkbox'/></th>
	  <th width='270'>Nome e Cognome / Rag. sociale</th>
	  <th>Email</th></tr>
 </table>
 </div>

 <table class="contact-list-footer" cellspacing="0" cellpadding="0" border="0">
  <tr><th>Selezionati: <b id='selected-contacts-count'>0</b></th></tr>
 </table>

 <div style="margin-top:10px">
  <span class="buttongray" onclick="closeContactFinder()">Annulla</span>
  <span class="buttonblue" onclick="submitContactFinder()">Conferma</span>
 </div>

</div>
<!-- EOF - CONTACT FINDER -->

<?php
echo "</div>";
$form->End();
?>
<script>
var sSkinPath = "<?php echo $_ABSOLUTE_URL; ?>var/objects/fckeditor/editor/skins/office2003/";
var oFCKeditor = null;
var contactFinder = null;

var SEARCHINPROGRESS = false;
var SENDINPROGRESS = false;

var overPopupMenu = false;
var activePopupMenu = null;

function bodyOnLoad()
{
 document.getElementById('recp').focus();
 gframe_cachecontentsload(document.getElementById('message').innerHTML);

 contactFinder = EditSearch.init(document.getElementById('contact-finder-search'),
	"dynarc item-find -ap `rubrica` -field name `","` -extget `contacts` -limit 10 --order-by 'name ASC'",
	"id","name","items",true); 
 contactFinder.onfocus = function(){
	 if(this.value == this.defaultValue)
	 {
	  this.value = "";
	  this.className = "edit";
	 }
	}

 // init recp
 var recpED = document.getElementById('recp');
 var popupmenu = document.getElementById(recpED.getAttribute('connect'));
 sendmail_initPopupMenu(popupmenu, recpED).OnSelect = function(li){
	 recpED.value = li.textContent;
	 recpED.setAttribute('retval',li.getAttribute('value'));
	}
 recpED.onchange = function(){this.popupmenu.hide();}

 document.addEventListener ? document.addEventListener("mouseup",sendmail_MouseUp,false) : document.attachEvent("onmouseup",sendmail_MouseUp);
}

function sendmail_MouseUp(ev)
{
 if(activePopupMenu && !overPopupMenu) 
  activePopupMenu.hide();
}

function sendmail_getAbsPos(e)
{
 var left = e.offsetLeft;
 var top  = e.offsetTop;
 var obj = e;
 while(e = e.offsetParent)
 {
  left+= e.offsetLeft-e.scrollLeft;
  top+= e.offsetTop-e.scrollTop;
 }

 while(obj = obj.parentNode)
 {
  left-= obj.scrollLeft ? obj.scrollLeft : 0;
  top-= obj.scrollTop ? obj.scrollTop : 0;
 }

 return {x:left, y:top};
}

function sendmail_initPopupMenu(ul, btn)
{
 ul.selectedItem = null;

 /* RE-WRITABLE EVENTS */
 ul.OnSelect = function(selLI){};


 /* PRIVATE */
 ul.onmouseover = function(){overPopupMenu=true;}
 ul.onmouseout = function(){overPopupMenu=false;}
 if(btn)
 {
  ul.btn = btn;
  btn.popupmenu = ul;
  btn.onclick = function(){this.popupmenu.show(this);}
 }
 

 ul.show = function(btnObj, corrX, corrY){
	 var pos = sendmail_getAbsPos(btnObj);
	 if(!isNaN(corrX)) pos.x+= corrX;
	 if(!isNaN(corrY)) pos.y+= corrY;
	 this.style.left = pos.x+"px";
	 this.style.top = (pos.y+btnObj.offsetHeight)+"px";

	 var screenWidth = window.innerWidth ? window.innerWidth : document.body.clientWidth;
	 var screenHeight = window.innerHeight ? window.innerHeight : document.body.clientHeight;

     if((parseFloat(this.style.left)+this.offsetWidth) > screenWidth)
      this.style.left = screenWidth - this.offsetWidth - 20;

	 if((parseFloat(this.style.top)+this.offsetHeight) > screenHeight)
	  this.style.top = (pos.y-this.offsetHeight)+"px";

	 this.style.visibility = "visible";
	 activePopupMenu = this;
	}

 ul.hide = function(){
	 this.style.visibility="hidden";
	 activePopupMenu = null;
	}

 ul.setSelectedItem = function(li){
	 if(this.selectedItem) this.selectedItem.className = this.selectedItem.className.replace(" selected","");
	 this.selectedItem = li;
	 li.className = li.className+" selected";
	 if(ul.OnSelect) ul.OnSelect(li);
	 this.hide();
	}

 ul.getItemByValue = function(value){
	 var list = this.getElementsByTagName('LI');
	 for(var c=0; c < list.length; c++)
	 {
	  if(list[c].getAttribute('value') == value)
	   return list[c];
	 }
	}

 var list = new Array();
 var tmp = ul.getElementsByTagName('LI');
 for(var c=0; c < tmp.length; c++)
 {
  if(tmp[c].parentNode == ul)
   list.push(tmp[c]);
 }
 
 for(var c=0; c < list.length; c++)
 {
  var li = list[c];
  li.onmouseover = function(){
	 /*if(oThis.lastSubmenuActive && (oThis.lastSubmenuActive != this.subMenu))
	  oThis.lastSubmenuActive.hide();*/
	 if(this.parentNode.subMenuT)
	  window.clearTimeout(this.parentNode.subMenuT);
	}

  li.hideAll = function(){
	 var pLI = this.parentNode.parentNode;
	 if((pLI.tagName == "LI") && pLI.hideAll)
	  pLI.hideAll();
	 else
	  this.parentNode.hide();
	}

  li.rename = function(title){
	 var span = this.getElementsByTagName('SPAN')[0];
	 if(span)
	  span.innerHTML = title;
	}

  li.onclick = function(){
	 this.parentNode.setSelectedItem(this);
	}

 }

 return ul;
}

function gframe_cachecontentsload(contents)
{
 document.getElementById('message').innerHTML = contents;
 oFCKeditor = new FCKeditor('message') ;
 oFCKeditor.ToolbarSet = "Small";
 oFCKeditor.BasePath = "<?php echo $_ABSOLUTE_URL; ?>var/objects/fckeditor/";
 oFCKeditor.Config['SkinPath'] = sSkinPath ;
 oFCKeditor.Config['PreloadImages'] =
				sSkinPath + 'images/toolbar.start.gif' + ';' +
				sSkinPath + 'images/toolbar.end.gif' + ';' +
				sSkinPath + 'images/toolbar.bg.gif' + ';' +
				sSkinPath + 'images/toolbar.buttonarrow.gif' ;
 oFCKeditor.Config['EditorAreaStyles'] = document.getElementById('message').value;
 oFCKeditor.Height = 260;
 oFCKeditor.ReplaceTextarea();
}

function OnFormAbort()
{
 if(SENDINPROGRESS)
 {
  if(confirm("C'è un invio email ancora in corso... Vuoi chiudere la finestra ed annullare l'operazione?\nPremi 'annulla' per attendere che finisca."))
   gframe_close();
 }
 else
  gframe_close();
}

function OnFormSubmit()
{
 if(SENDINPROGRESS)
  return alert("Attendere! invio dell'email in corso... (per connessioni lente potrebbe richiedere anche un minuto)");

 var sender = document.getElementById('sender').value;
 var senderName = sender;

 if(sender.indexOf("<") > 0)
 {
  var sp = sender.indexOf("<");
  var ep = sender.indexOf(">");
  senderName = sender.substr(0,sp).trim();
  sender = sender.substr(sp+1, (ep-sp)-1);
 }

 var recp = document.getElementById('recp').value;
 var subject = document.getElementById('subject').value;
 var msg = FCKeditorAPI.GetInstance('message').GetXHTML();
 var firm = document.getElementById("firm-message") ? document.getElementById("firm-message").innerHTML : "";
 var attachments = "";
 
 var tmp = document.getElementById('attachments-list');
 var list = tmp.getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
  attachments+= " -attachment `"+list[c].getAttribute('path')+"`";

 if(firm)
  msg = msg+"<br/"+"><br/"+">"+firm;

 var sh = new GShell();
 sh.showProcessMessage("Invio email in corso", "Attendere prego, è in corso l'invio della email");
 sh.OnError = function(err,errcode){this.processMessage.error(err);}
 sh.OnOutput = function(o,a){
	 this.hideProcessMessage();
	 alert("Il messaggio è stato inviato!");
	 gframe_close(o,a);
	}
 SENDINPROGRESS = true;
 sh.sendCommand("sendmail -from `"+sender+"` -fromname `"+senderName+"` -to `"+recp+"` -subject `"+subject+"` -message `"+msg+"`"+attachments);
}

function uploadFile()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a || !a['files'])
	  return;
	 for(var c=0; c < a['files'].length; c++)
	 {
	  var div = document.createElement('DIV');
	  div.setAttribute('path',a['files'][c]['fullname']);
	  div.className = "attachment";
	  div.innerHTML = "<img src='"+ABSOLUTE_URL+"share/widgets/sendmail/img/delete.png' class='removebtn' onclick='deleteAttachment(this.parentNode)'/ ><img src='"+a['files'][c]['icon'].replace("48x48","22x22")+"'/ > "+a['files'][c]['name']+"."+a['files'][c]['extension'];
	  document.getElementById('attachments-list').appendChild(div);
	 }
	}
 sh.sendCommand("gframe -f fileupload");
}

function deleteAttachment(div)
{
 div.parentNode.removeChild(div);
}

function contactFinderChange(ed)
{
 var email = "";
 var tb = document.getElementById('contact-list');

 if(ed.value && ed.data)
 {
  if(!ed.data['contacts'])
  {
   alert("Questo contatto non ha neanche un recapito in rubrica, ne tantomeno un'email\nOra io ti apro la scheda di questo contatto, aggiungi un recapito e ci metti l'email, dopodichè salvi e ripeti la ricerca.");
   var sh = new GShell();
   sh.OnError = function(msg,errcode){alert(msg);}
   sh.OnOutput = function(o,a){
 	 ed.value = "";
	 ed.focus();
	}

   sh.sendCommand("gframe -f rubrica.edit -params `id="+ed.data['id']+"`");
   return;
  }

  if(!ed.data['contacts'][0]['email'] && !ed.data['contacts'][0]['email2'] && !ed.data['contacts'][0]['email3'])
  {
   email = prompt("Questo contatto non è provvisto di email. Inserisci pertanto un email valida!");
   if(!email)
	return;
   var sh = new GShell();
   sh.OnError = function(msg,errcode){alert(msg);}
   sh.sendCommand("dynarc edit-item -ap rubrica -id `"+ed.data['id']+"` -extset `contacts.id="+ed.data['contacts'][0]['id']+",email='"+email+"'`");
  }
  else
  {
   if(ed.data['contacts'][0]['email'])
	email = ed.data['contacts'][0]['email'];
   else if(ed.data['contacts'][0]['email2'])
	email = ed.data['contacts'][0]['email2'];
   else if(ed.data['contacts'][0]['email3'])
	email = ed.data['contacts'][0]['email3'];
  }
  
  var r = tb.insertRow(-1);
  r.insertCell(-1).innerHTML = "<input type='checkbox' checked='true' onchange='updateSelectedCounter()'/"+">";
  r.insertCell(-1).innerHTML = ed.data['name'];
  r.insertCell(-1).innerHTML = email;
  
  ed.value = "";
  ed.focus();

  updateSelectedCounter();
 }
}

function updateSelectedCounter()
{
 var count = 0;
 var tb = document.getElementById('contact-list');
 for(var c=1; c < tb.rows.length; c++)
 {
  if(tb.rows[c].cells[0].getElementsByTagName('INPUT')[0].checked)
   count++;
 }
 document.getElementById('selected-contacts-count').innerHTML = count;
}

function showContactFinder()
{
 document.getElementById("contactfinder").style.display = "";
 document.getElementById("contact-finder-search").focus();
}

function submitContactFinder()
{
 setSelectedContacts();
 closeContactFinder();
}

function closeContactFinder()
{
 emptyContactList();
 document.getElementById("contact-finder-search").value = "";
 document.getElementById("contactfinder").style.display = "none";
}

function selectAllContacts()
{
 if(SEARCHINPROGRESS)
 {
  alert("C'è un altra operazione in corso. Devi attendere un attimo che finisca!");
  return;
 }

 /* get cat */
 var catId = 0;
 var catName = "";

 var list = document.getElementsByName("rubcat");
 for(var c=0; c < list.length; c++)
 {
  if(!list[c].checked) continue;
  catId = list[c].id;
  catName = list[c].value;
  break;
 }


 if(!confirm("Ora cercherò in tutti i contatti della categoria "+catName+" scartando quelli privi di email. Se avete molti contatti (tipo 1000) potrebbero volerci anche una decina di secondi. Procedo?"))
  return;

 emptyContactList();
 count = 0;
 removed = 0;

 var sh = new GShell();
 sh.OnError = function(msg,errcode){
	 alert(msg);
	 SEARCHINPROGRESS=false;
	}
 sh.OnOutput = function(o,a){
	 if(!a || !a['items'])
	 {
	  SEARCHINPROGRESS=false;
	  alert("La categoria "+catName+" è vuota, non ha nemmeno un contatto!");
	  return;
	 }

	 for(var c=0; c < a['items'].length; c++)
	 {
	  if(addContact(a['items'][c]))
	   count++;
	  else
	   removed++;
	 }
	 document.getElementById('selected-contacts-count').innerHTML = count+"</b> &nbsp;&nbsp;&nbsp; scartati: <b>"+removed;
	 SEARCHINPROGRESS = false;
	 if(count == 0)
	  alert("Spiacente, ma nessun contatto della categoria "+catName+" ha un email"+(removed ? ", ne ho scartati "+removed+"." : "."));
	}

 sh.sendCommand("dynarc item-list -ap rubrica -cat `"+catId+"` -extget `contacts`");
 SEARCHINPROGRESS = true;
}

function addContact(data)
{
 if(!data['contacts'])
  return;

 var email = "";

 if(data['contacts'][0]['email'])
  email = data['contacts'][0]['email'];
 else if(data['contacts'][0]['email2'])
  email = data['contacts'][0]['email2'];
 else if(data['contacts'][0]['email3'])
  email = data['contacts'][0]['email3'];

 if(!email)
  return;

 var tb = document.getElementById('contact-list');
 var r = tb.insertRow(-1);
 r.insertCell(-1).innerHTML = "<input type='checkbox' checked='true' onchange='updateSelectedCounter()'/"+">";
 r.insertCell(-1).innerHTML = data['name'];
 r.insertCell(-1).innerHTML = email;
 return r;
}

function emptyContactList()
{
 var tb = document.getElementById('contact-list');
 while(tb.rows.length > 1)
  tb.deleteRow(1);
}

function setSelectedContacts()
{
 var value = "";
 var tb = document.getElementById('contact-list');
 for(var c=1; c < tb.rows.length; c++)
 {
  if(!tb.rows[c].cells[0].getElementsByTagName('INPUT')[0].checked)
   continue;
  value+= ","+tb.rows[c].cells[2].innerHTML;
 }
 if(value)
  document.getElementById('recp').value = value.substr(1);
}

</script>
</body></html>
<?php

