<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 16-09-2016
 #PACKAGE: sendmail-config
 #DESCRIPTION: Sendmail configuration panel
 #VERSION: 2.1beta
 #CHANGELOG: 16-09-2016 : Aggiunto opzione per prediligere vecchia versione di sendmail.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../../";

include($_BASE_PATH."var/templates/glight/index.php");

$template = new GLightTemplate();
$template->includeObject("editsearch");
$template->includeObject("fckeditor");

$template->Begin("Settaggi principali");

/*$centerContents = "<input type='text' class='search' style='width:400px;float:left' placeholder='Cerca nella configurazione...' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\"/><input type='button' class='button-search' id='searchbtn'/>";*/
$centerContents = "<span class='glight-template-hdrtitle'>PANNELLO DI CONFIGURAZIONE - SENDMAIL</span>";

$template->Header("search", $centerContents, "BTN_EXIT", 800);

$template->Pathway();

$template->Body("default",800);



/* GET CONFIG */
$config = array();
$config['options'] = array();

$ret = GShell("aboutconfig get-config -app sendmail");
if(!$ret['error'])
{
 $config = $ret['outarr']['config'];
 if($config['firm'])
 {
  // get html param contents //
  $ret = GShell("dynarc item-info -ap '".$config['firm']['ap']."' -id '".$config['firm']['id']."'");
  if(!$ret['error'])
   $firmMessage = $ret['outarr'];
 }

}
else
 echo $ret['message'];

/*-------------------------------------------------------------------------------------------------------------------*/
?>
<h1>Configurazione della posta</h1>
<hr/>

<h2>Firma</h2>
<br/>
<small>(aggiungi in calce a tutti i messaggi in uscita)</small><br/>
<textarea style="width:800px;height:200px" id="firm-contents" refap="<?php echo $firmMessage ? $config['firm']['ap'] : ''; ?>" refid="<?php echo $firmMessage ? $config['firm']['id'] : ''; ?>"><?php echo $firmMessage ? $firmMessage['desc'] : ''; ?></textarea>


<h2>Opzioni</h2>
<p>
 <input type='checkbox' id='useoldguiver' <?php if($config['options']['useoldguiversion']) echo "checked='true'"; ?>/> Prediligi vecchia versione. (<small>se spuntato utilizza la vecchia versione di sendmail</small>).
</p>
<br/>
<h3>Campi predefiniti</h3>
<p>
 <table cellspacing='3' cellpadding='0' border='0' width='100%'>
  <tr><td><b>Cc:</b> </td><td><input type='text' class='edit' style='width:360px' id='default_cc' value="<?php echo $config['options']['default_cc']; ?>"/></td><td><small>specifica l&lsquo;indirizzo email (o pi&ugrave; indirizzi separati da un &quot;;&quot; punto e virgola) del destinatario per la copia carbone.</small></td></tr>
  <tr><td><b>Ccn:</b> </td><td><input type='text' class='edit' style='width:360px' id='default_bcc' value="<?php echo $config['options']['default_bcc']; ?>"/></td><td><small>specifica l&lsquo;indirizzo email (o pi&ugrave; indirizzi separati da un &quot;;&quot; punto e virgola) del destinatario per la copia carbone nascosta.</small></td></tr>
 </table>
</p>



<hr/>
<input type='button' class='button-blue' value="Salva configurazione" onclick="saveConfig()"/>
<?php
/*-------------------------------------------------------------------------------------------------------------------*/
$template->Footer();

?>
<script>
Template.OnExit = function(){
	document.location.href = ABSOLUTE_URL+"accounts/Logout.php"+(this.getVar('continue') ? "?continue="+this.getVar('continue') : "");
	return false;
}

Template.OnInit = function(){
	this.initEd(document.getElementById("firm-contents"),"fckeditor","Optimized");
}

function saveConfig()
{
 var xml = "";

 // OPTIONS
 xml+= "<options useoldguiversion=\""+((document.getElementById('useoldguiver').checked == true) ? '1' : '0')+"\"";
 xml+= " default_cc=\""+document.getElementById('default_cc').value+"\" default_bcc=\""+document.getElementById('default_bcc').value+"\"";
 xml+= "/"+">";



 saveFirm(xml,function(xml){
	 saveFinish(xml);
	});
}

function saveFirm(xml,callback)
{
 var firm = document.getElementById("firm-contents");
 refAP = firm.getAttribute('refap') ? firm.getAttribute('refap') : "aboutconfig_htmlparms";
 refID = firm.getAttribute('refid') ? firm.getAttribute('refid') : 0;
 xml+= "<firm ";
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 xml+= "ap='aboutconfig_htmlparms' id='"+a['id']+"'";
	 xml+= "/"+">";  
	 callback(xml);
	}
 if(!refID)
  sh.sendCommand("dynarc new-item -ap aboutconfig_htmlparms -ct sendmail -name 'Firma in calce su tutti i messaggi in uscita' -desc `"+firm.getValue()+"`");
 else
  sh.sendCommand("dynarc edit-item -ap aboutconfig_htmlparms -id '"+refID+"' -desc `"+firm.getValue()+"`");
}

function saveFinish(xml)
{
 var sh2 = new GShell();
 sh2.OnError = function(err){alert(err);}
 sh2.OnOutput = function(){alert("Salvataggio completato!"); Template.Exit();}
 sh2.sendCommand("aboutconfig set-config -app sendmail -xml-config `"+xml+"`");
}

</script>
<?php

$template->End();

?>
