<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-09-2016
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: GCommercialDocs Other
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../../";

include($_BASE_PATH."var/templates/glight/index.php");

$template = new GLightTemplate();
$template->includeObject("editsearch");
$template->includeObject("fckeditor");
$template->includeCSS("../aboutconfig.css");

$template->Begin("Altro...");

$centerContents = "<input type='text' class='search' style='width:400px;float:left' placeholder='Cerca nella configurazione...' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\"/><input type='button' class='button-search' id='searchbtn'/>";

$template->Header("search", $centerContents, "BTN_SAVE|BTN_EXIT", 700);

$template->Pathway();

$template->Body("default");

/* GET CONFIG */
$config = array();
$ret = GShell("aboutconfig get-config -app gcommercialdocs -sec other");
if(!$ret['error'])
{
 $config = $ret['outarr']['config'];
}

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

<h1>Altre impostazioni</h1>
<br/>
<br/>
<h3>Visualizza riquadro informazioni cliente</h3>
<p>Nella videata di stesura di un documento (preventivo, fattura, ecc...) &egrave; possibile visualizzare un riquadro aggiuntivo, all&lsquo;interno della tab &quot;Allegati e Note&quot;, dove vengono mostrate informazioni sul cliente/fornitore selezionato.<br/><br/>
Il layout del riquadro e tutte le informazioni mostrate al suo interno possono essere personalizzate seguendo uno schema di chiavi riportato qui sotto.<br/><br/>
<br/>
<input type='checkbox' id='subjinfoxl-enabled' <?php if($config['subjinfoxl']['enabled']) echo "checked='true'"; ?>/><b>Abilita visualizzazione riquadro aggiuntivo.</b>
</p>

<p>
 <small>Elenco delle chiavi disponibili</small>
 <table class='keylist' style='width:600px;margin-top0px' border='0' cellspacing='0' cellpadding='3'>
  <tr><th style='text-align:left;width:100px'>CHIAVE</th>
	 <th style='text-align:left'>DESCRIZIONE</th>
	 <th style='text-align:left;width:100px'>CHIAVE</th>
	 <th style='text-align:left'>DESCRIZIONE</th>
  </tr>

  <?php
  $ret = GShell("parserize parserinfo -p contactinfo");
  if(!$ret['error'])
  {
   $keys = $ret['outarr']['keys'];
   reset($keys);
   $idx = 0;
   while(list($k,$v) = each($keys))
   {
    if($idx == 0) echo "<tr>";
    echo "<td class='key'>{".$k."}</td> <td class='desc'>".$v."</td>";
    if($idx == 1) { echo "</tr>"; $idx = 0; } else $idx++;
   }
   if($idx == 1) echo "<td class='key'>&nbsp;</td> <td class='desc'>&nbsp;</td></tr>"; 
  }
  ?>
 </table>
</p>

<p><b>MESSAGGIO</b><br/><br/>Definisci il contenuto che deve apparire all&lsquo;interno del riquadro aggiuntivo.<br/>
	<?php
	$subjinfoXLContent = "";
	if($config['subjinfoxl']['contentap'] && $config['subjinfoxl']['contentid'])
	{
 	 $ret = GShell("dynarc item-info -ap '".$config['subjinfoxl']['contentap']."' -id '".$config['subjinfoxl']['contentid']."'");
	 if(!$ret['error'])
	 {
	  $subjinfoXLAP = $config['subjinfoxl']['contentap'];
	  $subjinfoXLID = $config['subjinfoxl']['contentid'];
	  $subjinfoXLContent = $ret['outarr']['desc'];
	 }
	}
	else
	{
	 // default message //
	 $subjinfoXLContent = '<div style="font-family:arial,sans-serif;font-size:12px;padding:10px">
<table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tbody>
        <tr>
            <td width="100"><span style="font-size: small;">COD:</span></td>
            <td><strong><span style="font-size: small;">{SUBJ_CODE}</span></strong></td>
            <td rowspan="7" valign="top"><span style="font-size: small;">{SUBJ_NOTE}</span></td>
        </tr>
        <tr>
            <td><span style="font-size: small;">TELEFONO:</span></td>
            <td><strong><span style="font-size: small;">{SUBJ_PHONE}</span></strong></td>
        </tr>
        <tr>
            <td><span style="font-size: small;">TELEFONO2:</span></td>
            <td><strong><span style="font-size: small;">{SUBJ_PHONE2}</span></strong></td>
        </tr>
        <tr>
            <td><span style="font-size: small;">FAX:</span></td>
            <td><strong><span style="font-size: small;">{SUBJ_FAX}</span></strong></td>
        </tr>
        <tr>
            <td><span style="font-size: small;">CELLULARE:</span></td>
            <td><strong><span style="font-size: small;">{SUBJ_CELL}</span></strong></td>
        </tr>
        <tr>
            <td><span style="font-size: small;">EMAIL:</span></td>
            <td><strong><span style="font-size: small;">{SUBJ_EMAIL}</span></strong></td>
        </tr>
        <tr>
            <td><span style="font-size: small;">&nbsp;</span></td>
            <td><span style="font-size: small;">&nbsp;</span></td>
        </tr>
    </tbody>
</table>
</div>';
	}
	?>
 <textarea id='subjinfoxl-content' style='height:440px' refap="<?php echo $subjinfoXLAP; ?>" refid="<?php echo $subjinfoXLID; ?>"><?php echo $subjinfoXLContent; ?></textarea>
</p>



<hr/>
<input type='button' class='button-blue' value="Salva le modifiche apportate" onclick="Template.SaveAndExit()"/>
<br/><br/><br/><br/>
<?php
/*-------------------------------------------------------------------------------------------------------------------*/
$template->Footer();

?>
<script>
Template.OnExit = function(){
	document.location.href = ABSOLUTE_URL+"accounts/Logout.php"+(this.getVar('continue') ? "?continue="+this.getVar('continue') : "");
	return false;
}

Template.OnSave = function(){
 var xml = "";

 saveSubjinfoXL(xml, function(xml){
  saveFinish(xml);
 });

 return false;
}

Template.OnInit = function(){
 this.initEd(document.getElementById("search"), "search").OnSearch = function(){};
 this.initBtn(document.getElementById("searchbtn")).onclick = function(){document.getElementById("search").OnSearch();}
 this.initEd(document.getElementById("subjinfoxl-content"),"fckeditor","Optimized");
}

function saveSubjinfoXL(xml, callback)
{
 var enabled = (document.getElementById('subjinfoxl-enabled').checked == true) ? '1' : '0';
 var subjinfoXLContent = document.getElementById('subjinfoxl-content').initialized ? document.getElementById('subjinfoxl-content').getValue() : document.getElementById('subjinfoxl-content').value;
 var subjinfoXLAp = document.getElementById('subjinfoxl-content').getAttribute('refap');
 var subjinfoXLId = document.getElementById('subjinfoxl-content').getAttribute('refid');
 
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 xml+= "<subjinfoxl enabled='"+enabled+"' contentap='aboutconfig_htmlparms' contentid='"+a['id']+"'/"+">";
	 document.getElementById('subjinfoxl-content').setAttribute('refid',a['id']);
	 callback(xml);
	}
 if(subjinfoXLAp && subjinfoXLId)
  sh.sendCommand("dynarc edit-item -ap '"+subjinfoXLAp+"' -id '"+subjinfoXLId+"' -desc `"+subjinfoXLContent+"`");
 else
  sh.sendCommand("dynarc new-item -ap aboutconfig_htmlparms -ct gcommercialdocs -name `Layout riquadro aggiuntivo` -desc `"+subjinfoXLContent+"`");
}

function saveFinish(xml)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 alert('Salvataggio completato');
	 Template.Exit();
	}
 sh.sendCommand("aboutconfig set-config -app gcommercialdocs -sec other -xml-config `"+xml+"`");
}

</script>
<?php

$template->End();

?>
