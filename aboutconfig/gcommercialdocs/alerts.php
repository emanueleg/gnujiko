<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 31-05-2016
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: GCommercialDocs Alerts
 #VERSION: 2.1beta
 #CHANGELOG: 31-05-2016 : Aggiunto argomento continue.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../../";

include($_BASE_PATH."var/templates/glight/index.php");

$template = new GLightTemplate();
$template->includeObject("editsearch");
$template->includeCSS("alerts.css");
$template->IncludeInternalObject("onofftable");
$template->includeObject("fckeditor");

$template->Begin("Personalizzazione messaggi di avviso");

$centerContents = "<input type='text' class='search' style='width:400px;float:left' placeholder='Cerca nella configurazione...' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\"/><input type='button' class='button-search' id='searchbtn'/>";

$template->Header("search", $centerContents, "BTN_SAVE|BTN_EXIT", 700);

$template->Pathway();

$template->Body("default");

/* GET CONFIG */
$ret = GShell("aboutconfig get-config -app gcommercialdocs -sec alerts");
if(!$ret['error'])
{
 $config = $ret['outarr']['config'];
 $overdueInvoicesConfig = $config['overdueinvoices'] ? $config['overdueinvoices'] : array();
 $paymentRemindersConfig = $config['paymentreminders'] ? $config['paymentreminders'] : array();
 $overduePurchaseInvoicesConfig = $config['overduepurchaseinvoices'] ? $config['overduepurchaseinvoices'] : array();
}

/*-------------------------------------------------------------------------------------------------------------------*/
?>
<h1>Personalizza i messaggi di avviso</h1>
<br/>
<br/>
<?php
$table = new OnOffTable("commdocsalerts");

/* OVERDUEINVOICES */
$ret = GShell("scheduledtasks info -alias commercialdocs.overdueinvoices");
if(!$ret['error'])
{
 $info = $ret['outarr'];
 $title = $info['name'];
 $subtitle = $info['desc'];
 $status = $info['status'];
 $active = $status ? true : false;
 $overdueInvoicesTaskId = $info['id'];
}
else
{
 $info = array();
 $title = "Fatture di vendita scadute";
 $subtitle = "Invia un&lsquo;email all&lsquo;indirizzo specificato per ogni fattura di vendita scaduta.";
 $status = 0;
 $active = false;
}

if($active)
{
 $statusContent = "<div class='process-status' id='overdueinvoices-process-status'><img src='img/process-ok.png' class='process-icon'/>";
 $statusContent.= "<a href='#' onclick='editScheduledTask(".$info['id'].",this)' class='tinylink' style='float:right'>edit</a>";
 $statusContent.= "<span class='tinytext' style='float:left'>Status: <b>attivo</b></span><br/>";
 $statusContent.= "<span class='tinytext' style='float:left'>Pross. avviso: <b>".($info['next_occurrence'] ? date('d/m/Y',strtotime($info['next_occurrence'])) : '')."</b></span></div>";
}
else
{
 $statusContent = "<div class='process-status' id='overdueinvoices-process-status'><img src='img/process-info.png' class='process-icon'/>";
 $statusContent.= "<span class='tinytext' style='float:left'>Status: <b>disattivato</b></span></div>";
}

$item = $table->AddItem("overdueinvoices", $title, $subtitle, $statusContent, $active);

/* CONTENT */
$content = "<div style='margin-bottom:20px'><span class='smalltext'>Invia a: </span>";
$content.= "<input type='text' class='edit' style='width:300px' id='overdueinvoices-recp' placeholder='digita un indirizzo email' value=\"".$overdueInvoicesConfig['recp']."\"/> ";
$content.= "<input type='button' class='button-blue' value='Fai un test' style='margin-left:100px' onclick='scheduledtasksMakeTest(".$overdueInvoicesTaskId.",this)'/><br/><br/>";
$content.= "<span class='smalltext'>Mittente: </span><input type='text' class='edit' style='width:300px' id='overdueinvoices-sender' placeholder='digita un indirizzo email' value=\"".$overdueInvoicesConfig['sender']."\"/><br/><br/>";
$content.= "<span class='smalltext'>Titolo del messaggio: </span><input type='text' class='edit' style='width:300px' id='overdueinvoices-subject' placeholder='digita un titolo' value=\"".$overdueInvoicesConfig['subject']."\"/><br/><br/>";

$content.= "<span class='smalltext'>Contenuto dell&lsquo;email: <br/><textarea style='width:100%;height:300px' id='overdueinvoices-email-content' refap='".($overdueInvoicesConfig['emailcontentap'] ? $overdueInvoicesConfig['emailcontentap'] : 'aboutconfig_htmlparms')."' refid='".$overdueInvoicesConfig['emailcontentid']."'>";
if($overdueInvoicesConfig['emailcontentap'] && $overdueInvoicesConfig['emailcontentid'])
{
 $ret = GShell("dynarc item-info -ap '".$overdueInvoicesConfig['emailcontentap']."' -id '".$overdueInvoicesConfig['emailcontentid']."'");
 if(!$ret['error'])
  $content.= $ret['outarr']['desc'];
}
$content.= "</textarea><a href='#overdueinvoices-parser-keys' class='tinylink' onclick='showParserKeys(\"overdueinvoices-parser-keys\",this)'>mostra lista chiavi</a>";
$content.= "<div id='overdueinvoices-parser-keys' class='parserkeys' style='display:none'>";
$ret = GShell("parserize parserinfo overdueinvoices");
if(!$ret['error'])
{
 $keys = $ret['outarr']['keys'];
 while(list($k,$v) = each($keys))
 {
  $content.= "<b>{".$k."}</b> - ".$v."<br/>";
 }
}
$content.= "</div></div>";
/*$content.= "<div style='margin-bottom:20px'><input type='checkbox' class='checkbox' id='overdueinvoices-pdf-attach'".($overdueInvoicesConfig['pdfattach'] ? " checked='true'/>" : "/>")." Allega PDF della fattura (se disponibile) all&lsquo;email.</div>";*/

$item->setContent($content, true);
//---------------------------------------------------------------------------------------//

/* PAYMENTREMINDERS */
/* TODO: da fare il parser, per il resto è tutto già collegato. */
/*$ret = GShell("scheduledtasks info -alias commercialdocs.paymentreminders");
if(!$ret['error'])
{
 $info = $ret['outarr'];
 $title = $info['name'];
 $subtitle = $info['desc'];
 $status = $info['status'];
 $active = $status ? true : false;
 $paymentRemindersTaskId = $info['id'];
}
else
{
 $info = array();
 $title = "Solleciti di pagamento";
 $subtitle = "Invia un&lsquo;email di sollecito di pagamento al cliente.";
 $status = 0;
 $active = false;
}

if($active)
{
 $statusContent = "<div class='process-status' id='paymentreminders-process-status'><img src='img/process-ok.png' class='process-icon'/>";
 $statusContent.= "<a href='#' onclick='editScheduledTask(".$info['id'].",this)' class='tinylink' style='float:right'>edit</a>";
 $statusContent.= "<span class='tinytext' style='float:left'>Status: <b>attivo</b></span><br/>";
 $statusContent.= "<span class='tinytext' style='float:left'>Pross. avviso: <b>".($info['next_occurrence'] ? date('d/m/Y',strtotime($info['next_occurrence'])) : '')."</b></span></div>";
}
else
{
 $statusContent = "<div class='process-status' id='paymentreminders-process-status'><img src='img/process-info.png' class='process-icon'/>";
 $statusContent.= "<span class='tinytext' style='float:left'>Status: <b>disattivato</b></span></div>";
}

$item = $table->AddItem("paymentreminders", $title, $subtitle, $statusContent, $active);

$content = "<div style='margin-bottom:20px'><span class='smalltext'>Invia copia a: </span>";
$content.= "<input type='text' class='edit' style='width:300px' id='paymentreminders-recp' placeholder='digita un indirizzo email' value=\"".$paymentRemindersConfig['recp']."\"/> ";
$content.= "<input type='button' class='button-blue' value='Fai un test' style='margin-left:100px' onclick='scheduledtasksMakeTest(".$paymentRemindersTaskId.",this)'/><br/><br/>";
$content.= "<span class='smalltext'>Mittente: </span><input type='text' class='edit' style='width:300px' id='paymentreminders-sender' placeholder='digita un indirizzo email' value=\"".$paymentRemindersConfig['sender']."\"/><br/><br/>";
$content.= "<span class='smalltext'>Titolo del messaggio: </span><input type='text' class='edit' style='width:300px' id='paymentreminders-subject' placeholder='digita un titolo' value=\"".$paymentRemindersConfig['subject']."\"/><br/><br/>";
$content.= "<span class='smalltext'>Contenuto dell&lsquo;email: <br/><textarea style='width:100%;height:300px' id='paymentreminders-email-content' refap='".($paymentRemindersConfig['emailcontentap'] ? $paymentRemindersConfig['emailcontentap'] : 'aboutconfig_htmlparms')."' refid='".$paymentRemindersConfig['emailcontentid']."'>";
if($paymentRemindersConfig['emailcontentap'] && $paymentRemindersConfig['emailcontentid'])
{
 $ret = GShell("dynarc item-info -ap '".$paymentRemindersConfig['emailcontentap']."' -id '".$paymentRemindersConfig['emailcontentid']."'");
 if(!$ret['error'])
  $content.= $ret['outarr']['desc'];
}
$content.= "</textarea><a href='#paymentreminders-parser-keys' class='tinylink' onclick='showParserKeys(\"paymentreminders-parser-keys\",this)'>mostra lista chiavi</a>";
$content.= "<div id='paymentreminders-parser-keys' class='parserkeys' style='display:none'>";
$ret = GShell("parserize parserinfo paymentreminders");
if(!$ret['error'])
{
 $keys = $ret['outarr']['keys'];
 while(list($k,$v) = each($keys))
 {
  $content.= "<b>{".$k."}</b> - ".$v."<br/>";
 }
}
$content.= "</div></div>";
$content.= "<div style='margin-bottom:20px'><input type='checkbox' class='checkbox' id='paymentreminders-pdf-attach'".($paymentRemindersConfig['pdfattach'] ? " checked='true'/>" : "/>")." Allega PDF della fattura (se disponibile) all&lsquo;email.</div>";

$item->setContent($content, true);*/
//---------------------------------------------------------------------------------------//

/* OVERDUEPURCHASEINVOICES */
$ret = GShell("scheduledtasks info -alias commercialdocs.overduepurchaseinvoices");
if(!$ret['error'])
{
 $info = $ret['outarr'];
 $title = $info['name'];
 $subtitle = $info['desc'];
 $status = $info['status'];
 $active = $status ? true : false;
 $overduePurchaseInvoicesTaskId = $info['id'];
}
else
{
 $info = array();
 $title = "Fatture di acquisto scadute";
 $subtitle = "Invia un&lsquo;email all&lsquo;indirizzo specificato per ogni fattura di acquisto scaduta.";
 $status = 0;
 $active = false;
}

if($active)
{
 $statusContent = "<div class='process-status' id='overduepurchaseinvoices-process-status'><img src='img/process-ok.png' class='process-icon'/>";
 $statusContent.= "<a href='#' onclick='editScheduledTask(".$info['id'].",this)' class='tinylink' style='float:right'>edit</a>";
 $statusContent.= "<span class='tinytext' style='float:left'>Status: <b>attivo</b></span><br/>";
 $statusContent.= "<span class='tinytext' style='float:left'>Pross. avviso: <b>".($info['next_occurrence'] ? date('d/m/Y',strtotime($info['next_occurrence'])) : '')."</b></span></div>";
}
else
{
 $statusContent = "<div class='process-status' id='overduepurchaseinvoices-process-status'><img src='img/process-info.png' class='process-icon'/>";
 $statusContent.= "<span class='tinytext' style='float:left'>Status: <b>disattivato</b></span></div>";
}

$item = $table->AddItem("overduepurchaseinvoices", $title, $subtitle, $statusContent, $active);

/* CONTENT */
$content = "<div style='margin-bottom:20px'><span class='smalltext'>Invia a: </span>";
$content.= "<input type='text' class='edit' style='width:300px' id='overduepurchaseinvoices-recp' placeholder='digita un indirizzo email' value=\"".$overduePurchaseInvoicesConfig['recp']."\"/> ";
$content.= "<input type='button' class='button-blue' value='Fai un test' style='margin-left:100px' onclick='scheduledtasksMakeTest(".$overduePurchaseInvoicesTaskId.",this)'/><br/><br/>";
$content.= "<span class='smalltext'>Mittente: </span><input type='text' class='edit' style='width:300px' id='overduepurchaseinvoices-sender' placeholder='digita un indirizzo email' value=\"".$overduePurchaseInvoicesConfig['sender']."\"/><br/><br/>";
$content.= "<span class='smalltext'>Titolo del messaggio: </span><input type='text' class='edit' style='width:300px' id='overduepurchaseinvoices-subject' placeholder='digita un titolo' value=\"".$overduePurchaseInvoicesConfig['subject']."\"/><br/><br/>";
$content.= "<span class='smalltext'>Contenuto dell&lsquo;email: <br/><textarea style='width:100%;height:300px' id='overduepurchaseinvoices-email-content' refap='".($overduePurchaseInvoicesConfig['emailcontentap'] ? $overduePurchaseInvoicesConfig['emailcontentap'] : 'aboutconfig_htmlparms')."' refid='".$overduePurchaseInvoicesConfig['emailcontentid']."'>";
if($overduePurchaseInvoicesConfig['emailcontentap'] && $overduePurchaseInvoicesConfig['emailcontentid'])
{
 $ret = GShell("dynarc item-info -ap '".$overduePurchaseInvoicesConfig['emailcontentap']."' -id '".$overduePurchaseInvoicesConfig['emailcontentid']."'");
 if(!$ret['error'])
  $content.= $ret['outarr']['desc'];
}
$content.= "</textarea><a href='#overduepurchaseinvoices-parser-keys' class='tinylink' onclick='showParserKeys(\"overduepurchaseinvoices-parser-keys\",this)'>mostra lista chiavi</a>";
$content.= "<div id='overduepurchaseinvoices-parser-keys' class='parserkeys' style='display:none'>";
$ret = GShell("parserize parserinfo overduepurchaseinvoices");
if(!$ret['error'])
{
 $keys = $ret['outarr']['keys'];
 while(list($k,$v) = each($keys))
 {
  $content.= "<b>{".$k."}</b> - ".$v."<br/>";
 }
}
$content.= "</div></div>";
/*$content.= "<div style='margin-bottom:20px'><input type='checkbox' class='checkbox' id='overduepurchaseinvoices-pdf-attach'".($overduePurchaseInvoicesConfig['pdfattach'] ? " checked='true'/>" : "/>")." Allega PDF della fattura (se disponibile) all&lsquo;email.</div>";*/

$item->setContent($content, true);
//---------------------------------------------------------------------------------------//
$table->Paint();

?>
<hr/>
<input type='button' class='button-blue' value="Salva le modifiche apportate" onclick="Template.SaveAndExit()"/>
<br/><br/><br/><br/>
<?php
/*-------------------------------------------------------------------------------------------------------------------*/
$template->Footer();

?>
<script>
Template.OnExit = function(){
	document.location.href = ABSOLUTE_URL+"accounts/Logout.php?continue="+this.getVar('continue');
	return false;
}

Template.OnSave = function(){
 var xml = "";
 /*saveOverdueInvoices(xml, function(xml){
  savePaymentReminders(xml, function(xml){
   saveOverduePurchaseInvoices(xml, function(xml){saveFinish(xml);});
  });
 });*/

 saveOverdueInvoices(xml, function(xml){
  saveOverduePurchaseInvoices(xml, function(xml){saveFinish(xml);});
 });

 return false;
}

Template.OnInit = function(){
	this.initEd(document.getElementById("search"), "search").OnSearch = function(){
		};
	this.initBtn(document.getElementById("searchbtn")).onclick = function(){document.getElementById("search").OnSearch();}

	var commdocsalerts = new OnOffTable(document.getElementById("commdocsalerts"), this);
	commdocsalerts.onexpand = function(r){
	 var sh = new GShell();
	 sh.OnError = function(err){alert(err);}
	 sh.OnOutput = function(o,a){scheduledTaskUpdated(a, r.id+"-process-status");}
	 switch(r.id)
	 {
	  case 'overdueinvoices' : {
		 Template.initEd(document.getElementById("overdueinvoices-email-content"), "fckeditor", "Small"); 
		 sh.sendCommand("scheduledtasks edit -alias commercialdocs.overdueinvoices -status 1");
		} break;
	  case 'paymentreminders' : {
		 Template.initEd(document.getElementById("paymentreminders-email-content"), "fckeditor", "Small");
		 sh.sendCommand("scheduledtasks edit -alias commercialdocs.paymentreminders -status 1");
		} break;
	  case 'overduepurchaseinvoices' : {
		 Template.initEd(document.getElementById("overduepurchaseinvoices-email-content"), "fckeditor", "Small"); 
		 sh.sendCommand("scheduledtasks edit -alias commercialdocs.overduepurchaseinvoices -status 1");
		} break;
	 }
	}
	commdocsalerts.oncollapse = function(r){
	 var sh = new GShell();
	 sh.OnError = function(err){alert(err);}
	 sh.OnOutput = function(o,a){scheduledTaskUpdated(a, r.id+"-process-status");}
	 switch(r.id)
	 {
	  case 'overdueinvoices' : sh.sendCommand("scheduledtasks edit -alias commercialdocs.overdueinvoices -status 0"); break;
	  case 'paymentreminders' : sh.sendCommand("scheduledtasks edit -alias commercialdocs.paymentreminders -status 0"); break;
	  case 'overduepurchaseinvoices' : sh.sendCommand("scheduledtasks edit -alias commercialdocs.overduepurchaseinvoices -status 0"); break;
	 }
	}
	if(document.getElementById('overdueinvoices').className == "expanded")
	 this.initEd(document.getElementById("overdueinvoices-email-content"), "fckeditor", "Small");
	/* TODO: codice sotto da ripristinare una volta completato il parser dei solleciti di pagamento */
	/*if(document.getElementById('paymentreminders').className == "expanded")
	 this.initEd(document.getElementById("paymentreminders-email-content"), "fckeditor", "Small");*/
	if(document.getElementById('overduepurchaseinvoices').className == "expanded")
	 this.initEd(document.getElementById("overduepurchaseinvoices-email-content"), "fckeditor", "Small");
}

function saveOverdueInvoices(xml, callback)
{
 var recp = document.getElementById("overdueinvoices-recp").value;
 var sender = document.getElementById("overdueinvoices-sender").value;
 var subject = document.getElementById("overdueinvoices-subject").value.E_QUOT();
 var emailContent = document.getElementById('overdueinvoices-email-content').initialized ? document.getElementById('overdueinvoices-email-content').getValue() : document.getElementById('overdueinvoices-email-content').value;
 var pdfAttach = false;
 /* TODO: da ripristinare una volta trovato il sistema di allegare i pdf all'email */
 //var pdfAttach = document.getElementById('overdueinvoices-pdf-attach').checked;

 var emailContentAp = document.getElementById('overdueinvoices-email-content').getAttribute('refap');
 var emailContentId = document.getElementById('overdueinvoices-email-content').getAttribute('refid');
 
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 xml+= "<overdueinvoices recp='"+recp+"' sender='"+sender+"' subject='"+subject.replace("&","&amp;")+"' pdfattach='"+(pdfAttach ? '1' : '0')+"' emailcontentap='aboutconfig_htmlparms' emailcontentid='"+a['id']+"'/"+">";
	 document.getElementById('overdueinvoices-email-content').setAttribute('refid',a['id']);
	 callback(xml);
	}
 if(emailContentAp && emailContentId)
  sh.sendCommand("dynarc edit-item -ap '"+emailContentAp+"' -id '"+emailContentId+"' -desc `"+emailContent+"`");
 else
  sh.sendCommand("dynarc new-item -ap aboutconfig_htmlparms -ct gcommercialdocs -name `Testo email fatture scadute` -desc `"+emailContent+"`");
}

function savePaymentReminders(xml, callback)
{
 var recp = document.getElementById("paymentreminders-recp").value;
 var sender = document.getElementById("paymentreminders-sender").value;
 var subject = document.getElementById("paymentreminders-subject").value.E_QUOT();
 var emailContent = document.getElementById('paymentreminders-email-content').initialized ? document.getElementById('paymentreminders-email-content').getValue() : document.getElementById('paymentreminders-email-content').value;
 var pdfAttach = false;
 /* TODO: da ripristinare una volta trovato il sistema di allegare i pdf all'email */
 //var pdfAttach = document.getElementById('paymentreminders-pdf-attach').checked;

 var emailContentAp = document.getElementById('paymentreminders-email-content').getAttribute('refap');
 var emailContentId = document.getElementById('paymentreminders-email-content').getAttribute('refid');
 
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 xml+= "<paymentreminders recp='"+recp+"' sender='"+sender+"' subject='"+subject.replace("&","&amp;")+"' pdfattach='"+(pdfAttach ? '1' : '0')+"' emailcontentap='aboutconfig_htmlparms' emailcontentid='"+a['id']+"'/"+">";
	 document.getElementById('paymentreminders-email-content').setAttribute('refid',a['id'])
	 callback(xml);
	}
 if(emailContentAp && emailContentId)
  sh.sendCommand("dynarc edit-item -ap '"+emailContentAp+"' -id '"+emailContentId+"' -desc `"+emailContent+"`");
 else
  sh.sendCommand("dynarc new-item -ap aboutconfig_htmlparms -ct gcommercialdocs -name `Testo email solleciti pagamento` -desc `"+emailContent+"`");
}

function saveOverduePurchaseInvoices(xml, callback)
{
 var recp = document.getElementById("overduepurchaseinvoices-recp").value;
 var sender = document.getElementById("overduepurchaseinvoices-sender").value;
 var subject = document.getElementById("overduepurchaseinvoices-subject").value.E_QUOT();
 var emailContent = document.getElementById('overduepurchaseinvoices-email-content').initialized ? document.getElementById('overduepurchaseinvoices-email-content').getValue() : document.getElementById('overduepurchaseinvoices-email-content').value;
 var pdfAttach = false;
 /* TODO: da ripristinare una volta trovato il sistema di allegare i pdf all'email */
 //var pdfAttach = document.getElementById('overduepurchaseinvoices-pdf-attach').checked;

 var emailContentAp = document.getElementById('overduepurchaseinvoices-email-content').getAttribute('refap');
 var emailContentId = document.getElementById('overduepurchaseinvoices-email-content').getAttribute('refid');
 
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 xml+= "<overduepurchaseinvoices recp='"+recp+"' sender='"+sender+"' subject='"+subject.replace("&","&amp;")+"' pdfattach='"+(pdfAttach ? '1' : '0')+"' emailcontentap='aboutconfig_htmlparms' emailcontentid='"+a['id']+"'/"+">";
	 document.getElementById('overduepurchaseinvoices-email-content').setAttribute('refid',a['id'])
	 callback(xml);
	}
 if(emailContentAp && emailContentId)
  sh.sendCommand("dynarc edit-item -ap '"+emailContentAp+"' -id '"+emailContentId+"' -desc `"+emailContent+"`");
 else
  sh.sendCommand("dynarc new-item -ap aboutconfig_htmlparms -ct gcommercialdocs -name `Testo email fatture acquisto scadute` -desc `"+emailContent+"`");
}

function saveFinish(xml)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 alert('Salvataggio completato');
	 Template.Exit();
	}
 sh.sendCommand("aboutconfig set-config -app gcommercialdocs -sec alerts -xml-config `"+xml+"`");
}

function editScheduledTask(id,obj)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(a['next_occurrence'])
	 {
	  var nextOccurDate = new Date();
	  nextOccurDate.setFromISO(a['next_occurrence']);
	 }
	 var div = obj.parentNode;
	 var html = "";
	 switch(a['status'])
	 {
	  case '1' : {
		 html = "<img src='img/process-ok.png' class='process-icon'/"+">";
		 html+= "<a href='#' onclick='editScheduledTask("+id+",this)' class='tinylink' style='float:right'>edit</a>";
		 html+= "<span class='tinytext' style='float:left'>Status: <b>attivo</b></span><br/"+">";
		 html+= "<span class='tinytext' style='float:left'>Pross. avviso: <b>"+(a['next_occurrence'] ? nextOccurDate.printf('d/m/Y') : '')+"</b></span>";
		} break;

	  default : {
		 html = "<img src='img/process-info.png' class='process-icon'/"+">";
		 html+= "<span class='tinytext' style='float:left'>Status: <b>disattivato</b></span>";
		} break;
	 }
	 div.innerHTML = html;
	}

 sh.sendCommand("gframe -f scheduledtask/edit -params `id="+id+"`");
}

function scheduledTaskUpdated(data, ref)
{
 if(data['next_occurrence'])
 {
  var nextOccurDate = new Date();
  nextOccurDate.setFromISO(data['next_occurrence']);
 }
 var div = document.getElementById(ref);
 var html = "";
 switch(data['status'])
 {
  case '1' : {
	 html = "<img src='img/process-ok.png' class='process-icon'/"+">";
	 html+= "<a href='#' onclick='editScheduledTask("+data['id']+",this)' class='tinylink' style='float:right'>edit</a>";
	 html+= "<span class='tinytext' style='float:left'>Status: <b>attivo</b></span><br/"+">";
	 html+= "<span class='tinytext' style='float:left'>Pross. avviso: <b>"+(data['next_occurrence'] ? nextOccurDate.printf('d/m/Y') : '')+"</b></span>";
	} break;

  default : {
	 html = "<img src='img/process-info.png' class='process-icon'/"+">";
	 html+= "<span class='tinytext' style='float:left'>Status: <b>disattivato</b></span>";
	} break;
 }
 div.innerHTML = html;
}

function scheduledtasksMakeTest(id, btn)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 btn.value = "Fai un test";
	 btn.disabled = false;
	 alert(o);
	}
 sh.sendCommand("scheduledtasks exec -id '"+id+"'");
 btn.value = "Test in corso...";
 btn.disabled = true;
}

function showParserKeys(divId, aId)
{
 document.getElementById(divId).style.display = "";
}

</script>
<?php

$template->End();

?>
