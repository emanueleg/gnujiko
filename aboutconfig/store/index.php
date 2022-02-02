<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-04-2017
 #PACKAGE: gstore
 #DESCRIPTION: Store configuration panel
 #VERSION: 2.4beta
 #CHANGELOG: 30-04-2017 : Bugfix Exit.
			 09-09-2016 : Aggiunto magazzini predefiniti per carico e scarico.
			 17-02-2014 : Aggiunto opzioni.
			 30-10-2014 : Sospeso temporaneamente il metodo LIFO e FIFO.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../../";

include($_BASE_PATH."var/templates/glight/index.php");

$template = new GLightTemplate();
$template->includeObject("editsearch");
$template->includeCSS("../aboutconfig.css");

$template->Begin("Settaggi principali");

/*$centerContents = "<input type='text' class='search' style='width:400px;float:left' placeholder='Cerca nella configurazione...' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\"/><input type='button' class='button-search' id='searchbtn'/>";*/
$centerContents = "<span class='glight-template-hdrtitle'>PANNELLO DI CONFIGURAZIONE - MAGAZZINO</span>";

$template->Header("search", $centerContents, "BTN_EXIT", 800);

$template->Pathway();

$template->Body("default",800);

/* GET STORE LIST */
$ret = GShell("store list");
$storeList = $ret['outarr'];

/* GET CONFIG */
$ret = GShell("aboutconfig get-config -app gstore");
if(!$ret['error'])
 $config = $ret['outarr']['config'];

/*-------------------------------------------------------------------------------------------------------------------*/
?>
<style type="text/css">
table.keylist {
 background: #fafafa;
 border: 1px solid #d8d8d8;
}

table.keylist th {
 font-family: arial, sans-serif;
 font-size: 10px;
 color: #777;
 border-bottom: 1px solid #d8d8d8;
}

table.keylist td.key {
 font-family: arial, sans-serif;
 font-size: 10px;
 color: #444;
 font-weight: bold;
}

table.keylist td.desc {
 font-family: arial, sans-serif;
 font-size: 10px;
 color: #333;
}

table.gmutable th {
 background: #999;
}

div.gmutable {
 border: 0px;
}

</style>

<h1>Configurazione del magazzino</h1>
<hr/>

<h2>VALORIZZAZIONE</h2>

<h3>Metodo di valorizzazione degli scarichi</h3>
<p>
Scegli il metodo di valorizzazione delle merci scaricate.
<br/>
<br/>

 <table cellspacing='0' cellpadding='3' border='0'>
  <tr><td valign='middle' width='200'><input type='radio' name='enhmethod' <?php if(!$config['enhancement']['method'] || (strtoupper($config['enhancement']['method']) == 'WAC')) echo "checked='true'"; ?>/> Metodo del costo medio ponderato</td>
	  <td valign='top'><small>Con il metodo del costo medio ponderato il valore di scarico dal magazzino viene ottenuto calcolando la media aritmetica ponderata dei valori di carico, senza distinguere tra i diversi lotti ricevuti. Tale metodo consente di equilibrare eventuali differenze nei prezzi d&lsquo;acquisto, per esempio tra le quantit&agrave; acquistate a inizio anno e quelle di fine esercizio.</small></td></tr>

  <!-- <tr><td valign='middle'><input type='radio' name='enhmethod' <?php if(strtoupper($config['enhancement']['method']) == 'LIFO') echo "checked='true'"; ?>/> Metodo LIFO</td>
	  <td valign='top'><small>Il metodo LIFO (<i>Last In First Out</i>) ipotizza che le materie o le merci entrate per ultime (<i>Last In</i>) siano le prime a essere prelevate dal magazzino (<i>First Out</i>). Con questo metodo gli scarichi sono valorizzati utilizzando gli ultimi prezzi pagati.
La scorta di magazzino viene valorizzata ai costi storici, perch&egrave; il metodo considera in rimanenza i pezzi entrati nei periodi più lontani.</small></td></tr> -->

  <!-- <tr><td valign='middle'><input type='radio' name='enhmethod' <?php if(strtoupper($config['enhancement']['method']) == 'FIFO') echo "checked='true'"; ?>/> Metodo FIFO</td>
	  <td valign='top'><small>Il metodo FIFO (<i>First In First Out</i>) ipotizza che le materie o le merci entrate per prime (<i>First In</i>) siano le prime a essere prelevate dal magazzino (<i>First Out</i>). Con questo metodo gli scarichi sono valorizzati utilizzando i prezzi delle partite
acquistate per prime fino alloro esaurimento. La scorta di magazzino viene invece valorizzata a costi correnti, perch&egrave; il metodo considera in rimanenza i pezzi entrati più recentemente. Il metodo FIFO &egrave; applicato in modo continuo, cio&egrave; aggiornando scorte e scarichi a
ogni variazione di magazzino.</small></td></tr> -->

 </table>

</p>

<h2>OPZIONI</h2>
<h3>Movimenti di magazzino</h3>
<?php
if($config['options']['movements']['hideallmovmenuexceptusrid'])
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT username FROM gnujiko_users WHERE id='".$config['options']['movements']['hideallmovmenuexceptusrid']."'");
 $db->Read();
 $excUserName = $db->record['username'];
 $db->Close();
}
?>
<p>
 <table cellspacing='0' cellpadding='3' border='0'>
  <tr><td valign='top'width='22'><input type='checkbox' id='hide-allmov-menu' <?php if($config['options']['movements']['hideallmovmenu']) echo "checked='true'"; ?>/></td>
	  <td>Impedisci la visualizzazione dei movimenti di tutti i magazzini (tranne quelli assegnati)<br/> a tutti gli utenti eccetto: 
		<input type='text' class='dropdown' id='hide-allmov-menu-exceptusrid' connect='ham-userlist' retval="<?php echo $config['options']['movements']['hideallmovmenuexceptusrid']; ?>" value="<?php echo $excUserName; ?>"/>
	 	<ul class='popupmenu' id='ham-userlist'>
		<?php
		$ret = GShell("users --order-by username -asc");
		$list = $ret['outarr'];
		for($c=0; $c < count($list); $c++)
     	echo "<li value='".$list[$c]['id']."'>".$list[$c]['name']."</li>";
		?></ul>
	  </td>
  </tr>
 </table>
</p>

<h3>Carico e scarico</h3>
<?php
$defStoreUpldName = "";
$defStoreDownName = "";
if(is_array($config['options']['defaultstores']))
{
 if($config['options']['defaultstores']['upload'])
 {
  $ret = GShell("store info -id '".$config['options']['defaultstores']['upload']."'");
  if(!$ret['error']) $defStoreUpldName = $ret['outarr']['name'];
 }
 if($config['options']['defaultstores']['download'])
 {
  $ret = GShell("store info -id '".$config['options']['defaultstores']['download']."'");
  if(!$ret['error']) $defStoreDownName = $ret['outarr']['name'];
 }
}
?>
<p>
 <table cellspacing='0' cellpadding='3' border='0'>
  <tr><td>Magazzino predefinito per il carico merci: </td>
	  <td><input type='text' class='dropdown' id='default-store-upload' connect='dsu-storelist' retval="<?php echo $config['options']['defaultstores']['upload']; ?>" value="<?php echo $defStoreUpldName; ?>"/>
		  <ul class='popupmenu' id='dsu-storelist'>
		   <?php
			for($c=0; $c < count($storeList); $c++)
			 echo "<li value='".$storeList[$c]['id']."'>".$storeList[$c]['name']."</li>";
		   ?>
		  </ul>
	  </td>
  </tr>

  <tr><td>Magazzino predefinito per lo scarico merci: </td>
	  <td><input type='text' class='dropdown' id='default-store-download' connect='dsd-storelist' retval="<?php echo $config['options']['defaultstores']['download']; ?>" value="<?php echo $defStoreDownName; ?>"/>
		  <ul class='popupmenu' id='dsd-storelist'>
		   <?php
			for($c=0; $c < count($storeList); $c++)
			 echo "<li value='".$storeList[$c]['id']."'>".$storeList[$c]['name']."</li>";
		   ?>
		  </ul>
	  </td>
  </tr>

 </table>
</p>

<hr/>
<input type='button' class='button-blue' value="Salva configurazione" onclick="saveConfig()"/>
<br/>
<br/>
<br/>
<br/>
<?php
/*-------------------------------------------------------------------------------------------------------------------*/
$template->Footer();

?>
<script>
Template.OnExit = function(){
	document.location.href = ABSOLUTE_URL+"accounts/Logout.php"+(this.getVar('continue') ? "?continue="+this.getVar('continue') : "");
	return false;
}

Template.OnSave = function()
{
 saveConfig(function(){Template.Exit();});
 return false;
}

Template.OnInit = function(){
 this.initEd(document.getElementById('hide-allmov-menu-exceptusrid'), 'dropdown');
 this.initEd(document.getElementById('default-store-upload'), 'dropdown');
 this.initEd(document.getElementById('default-store-download'), 'dropdown');
}

function saveConfig(callback)
{
 var xml = "";
 
 /* Valorizzazione */
 var tmp = document.getElementsByName('enhmethod');
 var method = "WAC";
 if(tmp[1] && tmp[1].checked == true)		 method = "LIFO";
 else if(tmp[2] && tmp[2].checked == true) 	 method = "FIFO";

 xml+= "<enhancement method=\""+method+"\" /"+">";

 /* OPZIONI */
 xml+= "<options>";

 // Movimenti
 var hideAllMovMenu = (document.getElementById('hide-allmov-menu').checked == true) ? '1' : '0';
 var hideAllMovExceptUsrId = document.getElementById('hide-allmov-menu-exceptusrid').getValue();

 xml+= "<movements hideallmovmenu='"+hideAllMovMenu+"' hideallmovmenuexceptusrid='"+hideAllMovExceptUsrId+"'/"+">";

 // Magazzini predefiniti (carico e scarico)
 var defaultStoreUpload = document.getElementById('default-store-upload').getValue();
 var defaultStoreDownload = document.getElementById('default-store-download').getValue();

 xml+= "<defaultstores upload='"+defaultStoreUpload+"' download='"+defaultStoreDownload+"'/"+">";

 xml+= "</options>";

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(){
	 alert("Salvataggio completato!");
	 if(callback) return callback();
	 Template.Exit();
	}
 sh.sendCommand("aboutconfig set-config -app gstore -xml-config `"+xml+"`");
}

</script>
<?php

$template->End();

?>
