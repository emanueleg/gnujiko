<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 12-02-2017
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: GCommercialDocs - Interface
 #VERSION: 2.13beta
 #CHANGELOG: 12-02-2017 : Aggiunto opzioni importazione da XML.
			 07-12-2016 : Aggiunto opzioni importazione da Excel.
			 31-05-2016 : Aggiunto argomento continue.
			 27-05-2016 : Aggiunto parametro continue al salvataggio.
			 25-02-2016 : Aggiunto numero progressivo fattura elettronica.
			 01-02-2016 : Aggiunta opzione auto-chiusura DDT.
			 14-01-2016 : Aggiunta unita di misura predefinita nelle stampe.
			 23-03-2015 : Bug fix inizializzazione fckeditor su funzione InstallPackages.
			 14-03-2015 : Integrazione con fatture elettroniche.
			 18-02-2015 : Aggiunto data consegna.
			 26-01-2015 : Aggiunto location.
			 20-12-2014 : Aggiunto bottoni da mostrare nel documento.
			 18-12-2014 : Aggiunto sistema di personalizzazione campo descrizione art.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../../";

include($_BASE_PATH."var/templates/glight/index.php");

$template = new GLightTemplate();
$template->includeObject("editsearch");
$template->includeObject("gmutable");
$template->includeObject("fckeditor");
$template->includeCSS("../aboutconfig.css");

$template->Begin("Personalizzazione interfaccia");

$centerContents = "<input type='text' class='search' style='width:400px;float:left' placeholder='Cerca nella configurazione...' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\"/><input type='button' class='button-search' id='searchbtn'/>";

$template->Header("search", $centerContents, "BTN_SAVE|BTN_EXIT", 700);

$template->Pathway();

$template->Body("default");

/* GET CONFIG */
$ret = GShell("aboutconfig get-config -app gcommercialdocs -sec interface");
if(!$ret['error'])
 $config = $ret['outarr']['config'];

/* GET CATEGORIES */
$ret = GShell("dynarc cat-list -ap commercialdocs");
$_CATEGORIES = $ret['outarr'];

/* DOCINFO - BACKEDBUTTONS */
$_BRAKET_BUTTONS = array();
if(file_exists($_BASE_PATH."Products/index.php"))
 $_BRAKET_BUTTONS[] = array('type'=>'article', 'icon'=>'GCommercialDocs/img/add-product.png', 'title'=>'Aggiungi articolo', 'name'=>'Articolo', 'hide'=>$config['braketbuttons']['hide_article_btn'] ? true : false);
if(file_exists($_BASE_PATH."Services/index.php"))
 $_BRAKET_BUTTONS[] = array('type'=>'service', 'icon'=>'GCommercialDocs/img/add-service.png', 'title'=>'Aggiungi servizio', 'name'=>'Servizio', 'hide'=>$config['braketbuttons']['hide_service_btn'] ? true : false);
if(file_exists($_BASE_PATH."FinalProducts/index.php"))
 $_BRAKET_BUTTONS[] = array('type'=>'finalproduct', 'icon'=>'FinalProducts/icon.png', 'title'=>'Aggiungi prodotto finito', 'name'=>'Prodotto finito', 'hide'=>$config['braketbuttons']['hide_finalproduct_btn'] ? true : false);
if(file_exists($_BASE_PATH."Parts/index.php"))
 $_BRAKET_BUTTONS[] = array('type'=>'component', 'icon'=>'Parts/icon.png', 'title'=>'Aggiungi componente', 'name'=>'Componente', 'hide'=>$config['braketbuttons']['hide_component_btn'] ? true : false);
if(file_exists($_BASE_PATH."Materials/index.php"))
 $_BRAKET_BUTTONS[] = array('type'=>'material', 'icon'=>'Materials/icon.png', 'title'=>'Aggiungi materiale', 'name'=>'Materiale', 'hide'=>$config['braketbuttons']['hide_material_btn'] ? true : false);
if(file_exists($_BASE_PATH."Labors/index.php"))
 $_BRAKET_BUTTONS[] = array('type'=>'labor', 'icon'=>'Labors/icon.png', 'title'=>'Aggiungi lavorazione', 'name'=>'Lavorazione', 'hide'=>$config['braketbuttons']['hide_labor_btn'] ? true : false);
if(file_exists($_BASE_PATH."Books/index.php"))
 $_BRAKET_BUTTONS[] = array('type'=>'book', 'icon'=>'Books/icon.png', 'title'=>'Aggiungi libro', 'name'=>'Libro', 'hide'=>$config['braketbuttons']['hide_book_btn'] ? true : false);
if(file_exists($_BASE_PATH."Supplies/index.php"))
 $_BRAKET_BUTTONS[] = array('type'=>'supply', 'icon'=>'GCommercialDocs/img/add-supply.png', 'title'=>'Aggiungi altre forniture', 'name'=>'Altre forniture', 'hide'=>$config['braketbuttons']['hide_supply_btn'] ? true : false);


$_FATTUREPA_INSTALLED = file_exists($_BASE_PATH."etc/commercialdocs/protocols/paxml.php") ? true : false;

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

<h1>Personalizza l&lsquo;interfaccia dei documenti</h1>
<h2>SCEGLI QUALI DOCUMENTI VISUALIZZARE</h2>
<small>Seleziona i tipi di documento che desideri visualizzare nascondendo quelli che non utilizzerai mai. Quelli che nasconderai non verranno disattivati e nessun documento di quella categoria verr&agrave; rimosso, ma solamente nascosto per comodit&agrave; dalla lista dei documenti commerciali.</small>
<p>
<table cellspacing='0' cellpadding='3' border='0' id='visibleddocs'>
<?php
for($c=0; $c < count($_CATEGORIES); $c++)
{
 $cat = $_CATEGORIES[$c];
 echo "<tr><td valign='middle'><input type='checkbox' data-catid='".$cat['id']."'".($cat['published'] ? " checked='true'/>" : "/>")."</td>";
 echo "<td valign='middle'>".$cat['name']."</td></tr>";
}
?>
</table>
</p>

<h2>SEZIONI DA MOSTRARE NELLA SCHEDA DOCUMENTO</h2>
<small>Seleziona, per ogni tipo di documento, le sezioni di informazioni che desideri mostrare (e quindi impostare).</small>
<p>
<table cellspacing='0' cellpadding='3' border='0' id='fieldstb'>
<tr><th>&nbsp;</th>
	<th class='small'>Agente di riferimento</th>
	<th class='small'>Contatto di riferimento</th>
	<th class='small'>Doc. di rif. interno</th>
	<th class='small'>Divisione materiale</th>
	<th class='small'>Location</th>
	<th class='small'>Data cons.</th>
	<th class='small'>Modalit&agrave; e pagamenti</th>
	<th class='small'>Acconti</th>
	<th class='small'>Scadenze</th>
</tr>
<?php
for($c=0; $c < count($_CATEGORIES); $c++)
{
 $tag = strtolower($_CATEGORIES[$c]['tag']);
 if(is_array($config['docsections']) && $config['docsections'][$tag])
  $cfg = $config['docsections'][$tag];
 else
  $cfg = array();
 echo "<tr id='cdcat-".$tag."'>";
 echo "<td class='borderbottom'>".$_CATEGORIES[$c]['name']."</td>";
 echo "<td align='center' class='borderbottom'><input type='checkbox'".($cfg['agent'] ? " checked='true'/>" : "/>")."</td>";
 echo "<td align='center' class='borderbottom'><input type='checkbox'".($cfg['reference'] ? " checked='true'/>" : "/>")."</td>";
 echo "<td align='center' class='borderbottom'><input type='checkbox'".($cfg['intdocref'] ? " checked='true'/>" : "/>")."</td>";
 echo "<td align='center' class='borderbottom'><input type='checkbox'".($cfg['divmat'] ? " checked='true'/>" : "/>")."</td>";
 echo "<td align='center' class='borderbottom'><input type='checkbox'".($cfg['location'] ? " checked='true'/>" : "/>")."</td>";
 echo "<td align='center' class='borderbottom'><input type='checkbox'".($cfg['delivery'] ? " checked='true'/>" : "/>")."</td>";
 echo "<td align='center' class='borderbottom'><input type='checkbox'".($cfg['payments'] ? " checked='true'/>" : "/>")."</td>";
 echo "<td align='center' class='borderbottom'><input type='checkbox'".($cfg['advances'] ? " checked='true'/>" : "/>")."</td>";
 echo "<td align='center' class='borderbottom'><input type='checkbox'".($cfg['deadlines'] ? " checked='true'/>" : "/>")."</td>";
 echo "</tr>";
}
?>
</table>
</p>

<h2>OPZIONI</h2>
<h3>Bottoni da mostrare nel documento</h3>
<p>
Seleziona i bottoni da mostrare all&lsquo;interno del documento (per l&lsquo;inserimento degli articoli, servizi, ecc...).
<br/>
<br/>
<table cellspacing='0' cellpadding='3' border='0' id='braketbuttons'>
 <?php
 for($c=0; $c < count($_BRAKET_BUTTONS); $c++)
 {
  $btn = $_BRAKET_BUTTONS[$c];
  echo "<tr><td valign='middle'><input type='checkbox' id='hide_".$btn['type']."_btn'".(!$btn['hide'] ? " checked='true'/>" : "/>")."</td>";
  echo "<td valign='middle'><img src='".$_ABSOLUTE_URL.$btn['icon']."' width='22'/></td>";
  echo "<td valign='middle'>".$btn['title']."</td></tr>";
 }
 ?>
</table>
</p>

<h3>Unit&agrave; di misura predefinita nelle stampe</h3>
<p>Definisci l&lsquo;unit&agrave; di misura predefinita da mostrare in stampa nel caso non sia stata specificata.
<br/><br/>
<table cellspacing='0' cellpadding='3' border='0' id='defaultumistable'>
 <tr><th>TIPOLOGIA</th><th>U.M.</th></tr>
 <?php
 for($c=0; $c < count($_BRAKET_BUTTONS); $c++)
 {
  $btn = $_BRAKET_BUTTONS[$c];
  echo "<tr><td>".$btn['name']."</td>";
  echo "<td><input type='text' class='edit' style='width:50px' id='default_".$btn['type']."_umis' value=\"".$config['defaultumis'][$btn['type']]."\"/></td></tr>";
 }
 ?>
</table>
</p>

<h3>Chiusura automatica dei DDT</h3>
<p>Decidi se chiudere automaticamente il DDT una volta salvato.<br/>Un messaggio di conferma verr&agrave; mostrato prima di chiudere.
<br/><br/>
<input type='checkbox' id='autocloseddt' <?php if($config['options']['autocloseddt']) echo "checked='true'"; ?>/>Applica la chiusura automatica dei DDT al salvataggio (previa conferma dell&lsquo;utente)
</p>

<h3>Importazione documenti da file Excel</h3>
<?php
 $excelimpDefArcName = "";
 $excelimpDefCatName = "";
 $ret = GShell("dynarc archive-list -type gmart -a");
 $list = $ret['outarr'];
 if(!$config['options']['excelimp_defap'])
  $config['options']['excelimp_defap'] = $list[0]['prefix'];
 for($c=0; $c < count($list); $c++)
 {
  if($list[$c]['prefix'] == $config['options']['excelimp_defap'])
  {
   $excelimpDefArcName = $list[$c]['name'];
   break;
  }
 }

 if($config['options']['excelimp_defcat'])
 {
  $ret = GShell("dynarc cat-info -ap '".$config['options']['excelimp_defap']."' -id '".$config['options']['excelimp_defcat']."'");
  if(!$ret['error'])
   $excelimpDefCatName = $ret['outarr']['name'];
 }
?>
<p>
 <input type='checkbox' id='excelimp_regnewprod' <?php if($config['options']['excelimp_regnewprod']) echo "checked='true'"; ?>/>Registra automaticamente prodotti sconosciuti (non in catalogo)<br/>
 <table cellspacing='0' cellpadding='3' border='0'>
  <tr><td><input type='text' class='dropdown' id='excelimp_defap' style='width:200px' connect='excelimp_defaplist' retval="<?php echo $config['options']['excelimp_defap']; ?>" value="<?php echo $excelimpDefArcName; ?>"/>
		<ul class='popupmenu' id='excelimp_defaplist'>
		 <?php
		  for($c=0; $c < count($list); $c++)
		   echo "<li value='".$list[$c]['prefix']."'>".$list[$c]['name']."</li>";
		 ?>
		</ul>
	  </td>
	  <td>Archivio predefinito dove registrare i nuovi prodotti</td></tr>
  <tr><td><input type='text' class='search' id='excelimp_defcat' style='width:200px' ap="<?php echo $config['options']['excelimp_defap']; ?>" catid="<?php echo $config['options']['excelimp_defcat']; ?>" value="<?php echo $excelimpDefCatName; ?>"/></td>
	  <td>Categoria predefinita dove registrare i nuovi prodotti</td></tr>
 </table>
</p>

<h3>Importazione documenti da file XML</h3>
<?php
 $XMLimpDefArcName = "";
 $XMLimpDefCatName = "";
 $ret = GShell("dynarc archive-list -type gmart -a");
 $list = $ret['outarr'];
 if(!$config['options']['xmlimp_defap'])
  $config['options']['xmlimp_defap'] = $list[0]['prefix'];
 for($c=0; $c < count($list); $c++)
 {
  if($list[$c]['prefix'] == $config['options']['xmlimp_defap'])
  {
   $XMLimpDefArcName = $list[$c]['name'];
   break;
  }
 }

 if($config['options']['xmlimp_defcat'])
 {
  $ret = GShell("dynarc cat-info -ap '".$config['options']['xmlimp_defap']."' -id '".$config['options']['xmlimp_defcat']."'");
  if(!$ret['error'])
   $XMLimpDefCatName = $ret['outarr']['name'];
 }
?>
<p>
 <input type='checkbox' id='xmlimp_regnewprod' <?php if($config['options']['xmlimp_regnewprod']) echo "checked='true'"; ?>/>Registra automaticamente prodotti sconosciuti (non in catalogo)<br/>
 <table cellspacing='0' cellpadding='3' border='0'>
  <tr><td><input type='text' class='dropdown' id='xmlimp_defap' style='width:200px' connect='xmlimp_defaplist' retval="<?php echo $config['options']['xmlimp_defap']; ?>" value="<?php echo $XMLimpDefArcName; ?>"/>
		<ul class='popupmenu' id='xmlimp_defaplist'>
		 <?php
		  for($c=0; $c < count($list); $c++)
		   echo "<li value='".$list[$c]['prefix']."'>".$list[$c]['name']."</li>";
		 ?>
		</ul>
	  </td>
	  <td>Archivio predefinito dove registrare i nuovi prodotti</td></tr>
  <tr><td><input type='text' class='search' id='xmlimp_defcat' style='width:200px' ap="<?php echo $config['options']['xmlimp_defap']; ?>" catid="<?php echo $config['options']['xmlimp_defcat']; ?>" value="<?php echo $XMLimpDefCatName; ?>"/></td>
	  <td>Categoria predefinita dove registrare i nuovi prodotti</td></tr>
 </table>
</p>

<h3>Inserimento articoli all&lsquo;interno del documento commerciale. (preventivo, fattura, ddt, ecc...)</h3>
<p>
Quando inserisci un&lsquo;articolo all&lsquo;interno di un documento commerciale di default nel campo &quot;articolo/descrizione&quot; viene riportata la marca ed il modello del prodotto selezionato. 
<br/>Per ogni tipologia di documento (preventivo, ordine, ddt, fattura, ecc...) &egrave; possibile definire uno schema personalizzato utilizzando le chiavi disponibili qui sotto.<br/>
<br/>
<small>Elenco delle chiavi disponibili</small>
<table class='keylist' style='width:600px;margin-top0px' border='0' cellspacing='0' cellpadding='3'>
<tr><th style='text-align:left;width:100px'>CHIAVE</th>
	<th style='text-align:left'>DESCRIZIONE</th>
	<th style='text-align:left;width:100px'>CHIAVE</th>
	<th style='text-align:left'>DESCRIZIONE</th></tr>

<tr><td class='key'>{CODE}</td>			<td class='desc'>Cod. articolo</td>
	<td class='key'>{LOCATION}</td>		<td class='desc'>Collocaz. articolo</td></tr>

<tr><td class='key'>{BRAND}</td>		<td class='desc'>Marca</td>
	<td class='key'>{DIVISION}</td>		<td class='desc'>Divisione materiale</td></tr>

<tr><td class='key'>{MODEL}</td>		<td class='desc'>Modello</td>
	<td class='key'>{GEBINDECODE}</td>	<td class='desc'>Cod. confezionamento</td></tr>

<tr><td class='key'>{BARCODE}</td>		<td class='desc'>Codice a barre</td>
	<td class='key'>{GEBINDE}</td>		<td class='desc'>Confezionamento</td></tr>

<tr><td class='key'>{MANCODE}</td>		<td class='desc'>Cod. art. produttore</td>
	<td class='key'>{UNITS}</td>		<td class='desc'>Unit&agrave; di misura</td></tr>

<tr><td class='key'>{VENCODE}</td>		<td class='desc'>Cod. art. fornitore</td>
	<td class='key'>{WEIGHT}</td>		<td class='desc'>Peso</td></tr>

</table>
<br/>
<br/>
<?php
$_INSARTINPIDS = array();
$ret = GShell("dynarc cat-list -ap commercialdocs");
$list = $ret['outarr'];
for($c=0; $c < count($list); $c++)
{
 $catInfo = $list[$c];
 $inpid = "insart_".strtolower($catInfo['tag'])."_schema";
 $_INSARTINPIDS[] = $inpid;
 echo "<b>".$catInfo['name']."</b><br/>";
 echo "<input type='text' class='edit' id='".$inpid."' style='width:600px;margin-bottom:0px;background:#feffcb;' placeholder='Es: {BRAND} {MODEL}' value=\"".$config['docinfo'][$inpid]."\"/><br/><br/>";
}
?>
</p>

<h2>FATTURE ELETTRONICHE</h2>
 <p <?php if($_FATTUREPA_INSTALLED) echo "style='display:none'"; ?> id='install-fatturepa-p'>
  <table width='100%' border='0'>
   <tr><td>Per abilitare la gestione delle fatture elettroniche per le Pubbliche Amministrazioni &egrave; necessario installare il pacchetto <b>fatturapa</b>.</td>
	   <td><input type='button' class='button-blue' value="Installa pacchetto" onclick="InstallPackage('fatturepa')"/></td>
   </tr>
  </table>
 </p>

 <div <?php if(!$_FATTUREPA_INSTALLED) echo "style='display:none'"; ?> id='fatturepa-installed-div'>
  <h3>Progressivo Invio</h3>
  <p>Indica il numero <a href='http://www.fatturapa.gov.it/export/fatturazione/it/c-11.htm' target='_blank'>progessivo univoco del file</a> che dovr&agrave; avere la prossima fattura elettronica che farai.<br/><br/>
	<?php
	$nextSeqNum = 1;
	if($_FATTUREPA_INSTALLED)
	{
	 $ret = GShell("dynarc archive-info -ap fatturepa");
	 if(!$ret['error'])
	 {
	  if(is_array($ret['outarr']['params']) && $ret['outarr']['params']['nextseqnum'])
	   $nextSeqNum = $ret['outarr']['params']['nextseqnum'];
	  else
	  {
	   $db = new AlpaDatabase();
	   $db->RunQuery("SELECT MAX(ordering) AS ordering FROM dynarc_fatturepa_items");
	   if($db->Read())
		$nextSeqNum = $db->record['ordering']+1;
	   $db->Close();
	  }
	 }
	}	
	?>
	Numero progressivo invio della prossima fattura: <input type='text' class='edit' id='fatturepa-nextseqnum' style='width:60px' value="<?php echo $nextSeqNum; ?>" maxlength='5'/>
  </p>

  <h3>Invio dei file XML via email alle Pubbliche Amministrazioni</h3>
  <p><b>OGGETTO</b><br/><br/>Definisci, utilizzando le chiavi disponibili, l&lsquo;oggetto del messaggio.<br/>
 	<input type='text' class='edit' id='fatturepa-email-subject' style='width:600px;margin-bottom:0px;background:#feffcb;' value="<?php echo $config['fatturepa']['emailsubject']; ?>" placeholder="Es: invio fattura elettronica n. {PROG}"/>
 	<br/>
 	<br/>
 	<small>Elenco delle chiavi disponibili</small>
	 <table class='keylist' style='width:600px;margin-top0px' border='0' cellspacing='0' cellpadding='3'>
	 <tr><th style='text-align:left;width:100px'>CHIAVE</th>
		 <th style='text-align:left'>DESCRIZIONE</th>
		 <th style='text-align:left;width:100px'>CHIAVE</th>
		 <th style='text-align:left'>DESCRIZIONE</th></tr>

	 <tr><td class='key'>{PROG}</td>				<td class='desc'>Codice progressivo invio</td>
		 <td class='key'>{CUSTOMER}</td>			<td class='desc'>Cliente/Amministrazione</td></tr>

	 <tr><td class='key'>{DOCREF}</td>				<td class='desc'>Ns. fatt. di riferimento</td>
		 <td class='key'>{CIG}</td>					<td class='desc'>Codice Identificativo Gara</td></tr>

	 <tr><td class='key'>{DOCDATE}</td>				<td class='desc'>Ns. fatt. di rif. - data</td>
		 <td class='key'>{CUP}</td>					<td class='desc'>Codice Univoco Progetto</td></tr>

	 <tr><td class='key'>{DOCNUM}</td>				<td class='desc'>Ns. fatt. di rif. - numero</td>
		 <td class='key'>{DOCID}</td>				<td class='desc'>ID Documento</td></tr>

	 <tr><td class='key'>{TOTAL}</td>				<td class='desc'>Importo totale documento</td>
		 <td class='key'>&nbsp;</td>				<td class='desc'>&nbsp;</td></tr>

	 </table>
  </p>

  <p><b>MESSAGGIO</b><br/><br/>Definisci il messaggio che deve apparire sul&lsquo; email da inviare alla Pubblica Amministrazione.<br/>Anche qui puoi utilizzare le stesse chiavi cha hai usato per definire l&lsquo;oggetto del messaggio riportate qui sopra.<br/>
	<?php
	if($config['fatturepa']['messageap'] && $config['fatturepa']['messageid'])
	{
 	 $ret = GShell("dynarc item-info -ap '".$config['fatturepa']['messageap']."' -id '".$config['fatturepa']['messageid']."'");
	 if(!$ret['error'])
	 {
	  $fatturepaEmailRefAP = $config['fatturepa']['messageap'];
	  $fatturepaEmailRefID = $config['fatturepa']['messageid'];
	  $fatturepaEmailMessage = $ret['outarr']['desc'];
	 }
	}
	else
	{
	 // default message //
	 $fatturepaEmailMessage = "Gentile {CUSTOMER}<br/><br/>";
	 $fatturepaEmailMessage.= "In allegato Vi invio file XML della fattura elettronica in riferimento a ns. {DOCREF}: <br/>";
	 $fatturepaEmailMessage.= "Importo totale: <b>{TOTAL}</b><br/>";
	}
	?>
	<textarea id='fatturepa-email-message' style='width:600px;height:300px' refap="<?php echo $fatturepaEmailRefAP; ?>" refid="<?php echo $fatturepaEmailRefID; ?>"><?php echo $fatturepaEmailMessage; ?></textarea>
 </p>
</div>
<!-- EOF - FATTURE ELETTRONICHE -->

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
var insartinpidsStr = "<?php echo implode(',',$_INSARTINPIDS); ?>";
var INSARTINPIDS = (insartinpidsStr != "") ? insartinpidsStr.split(',') : new Array();
var FATTUREPA_INSTALLED = <?php echo $_FATTUREPA_INSTALLED ? 'true' : 'false'; ?>;

var SH_PROC = new GShell();

Template.OnExit = function(){
	document.location.href = ABSOLUTE_URL+"accounts/Logout.php"+(this.getVar('continue') ? "?continue="+this.getVar('continue') : "");
	return false;
}

Template.OnInit = function(){
	if(FATTUREPA_INSTALLED)	this.initEd(document.getElementById("fatturepa-email-message"),"fckeditor","Optimized");
	this.initEd(document.getElementById("excelimp_defcat"), "catfind");
	this.initEd(document.getElementById("excelimp_defap"),"dropdown").onchange = function(){
		 document.getElementById("excelimp_defcat").setAttribute('ap',this.getValue());
		 document.getElementById("excelimp_defcat").setAttribute('catid',"");
		 document.getElementById("excelimp_defcat").value = "";
		 document.getElementById("excelimp_defcat").data = null;
		 Template.initEd(document.getElementById("excelimp_defcat"), "catfind");
		}

	this.initEd(document.getElementById("xmlimp_defcat"), "catfind");
	this.initEd(document.getElementById("xmlimp_defap"),"dropdown").onchange = function(){
		 document.getElementById("xmlimp_defcat").setAttribute('ap',this.getValue());
		 document.getElementById("xmlimp_defcat").setAttribute('catid',"");
		 document.getElementById("xmlimp_defcat").value = "";
		 document.getElementById("xmlimp_defcat").data = null;
		 Template.initEd(document.getElementById("xmlimp_defcat"), "catfind");
		}
}

Template.OnSave = function()
{
 saveConfig(function(){Template.Exit();});
 return false;
}


function saveConfig(callback)
{
 SH_PROC.showProcessMessage("Salvataggio in corso", "Attendere prego, è in corso il salvataggio della configurazione");
 var xml = "<docsections>";
 var tb = document.getElementById('fieldstb');
 for(var c=1; c < tb.rows.length; c++)
 {
  var r = tb.rows[c];
  var tag = r.id.substr(6);
  var agent = r.cells[1].getElementsByTagName('INPUT')[0].checked ? '1' : '0';
  var reference = r.cells[2].getElementsByTagName('INPUT')[0].checked ? '1' : '0';
  var intdocref = r.cells[3].getElementsByTagName('INPUT')[0].checked ? '1' : '0';
  var divmat = r.cells[4].getElementsByTagName('INPUT')[0].checked ? '1' : '0';
  var location = r.cells[5].getElementsByTagName('INPUT')[0].checked ? '1' : '0';
  var delivery = r.cells[6].getElementsByTagName('INPUT')[0].checked ? '1' : '0';
  var payments = r.cells[7].getElementsByTagName('INPUT')[0].checked ? '1' : '0';
  var advances = r.cells[8].getElementsByTagName('INPUT')[0].checked ? '1' : '0';
  var deadlines = r.cells[9].getElementsByTagName('INPUT')[0].checked ? '1' : '0';


  xml+= "<"+tag+" agent='"+agent+"' reference='"+reference+"' intdocref='"+intdocref+"' divmat='"+divmat+"' location='"+location+"' delivery='"+delivery+"' payments='"+payments+"' advances='"+advances+"' deadlines='"+deadlines+"'/"+">";
 }
 xml+= "</docsections>";

 /* DOC INFO OPTIONS */
 xml+= "<docinfo";
 for(var c=0; c < INSARTINPIDS.length; c++)
 {
  var ed = document.getElementById(INSARTINPIDS[c]);
  if(!ed) continue;
  xml+= " "+ed.id+"=\""+xml_purify(ed.value.E_QUOT())+"\"";
 }
 xml+= "/"+">";

 /* DEFAULT UMIS */
 xml+= "<defaultumis";
 var tb = document.getElementById('defaultumistable');
 for(var c=1; c < tb.rows.length; c++)
 {
  var ed = tb.rows[c].cells[1].getElementsByTagName('INPUT')[0];
  var id = ed.id.substr(8, ed.id.length-13);
  xml+= " "+id+"=\""+xml_purify(ed.value)+"\"";
 }
 xml+= "/"+">";

 /* OTHER OPTIONS */
 xml+= "<options";
 var cb = document.getElementById('excelimp_regnewprod');
 xml+= " excelimp_regnewprod='"+(cb.checked ? '1' : '0')+"' excelimp_defap='"+document.getElementById("excelimp_defap").getValue()+"' excelimp_defcat='"+document.getElementById('excelimp_defcat').getId()+"'";

 var cb = document.getElementById('xmlimp_regnewprod');
 xml+= " xmlimp_regnewprod='"+(cb.checked ? '1' : '0')+"' xmlimp_defap='"+document.getElementById("xmlimp_defap").getValue()+"' xmlimp_defcat='"+document.getElementById('xmlimp_defcat').getId()+"'";

 var cb = document.getElementById('autocloseddt');
 if(cb) xml+= " autocloseddt='"+(cb.checked ? '1' : '0')+"'";
 xml+= "/"+">";


 /* BRAKET BUTTONS */
 xml+= "<braketbuttons";
 var tb = document.getElementById('braketbuttons');
 for(var c=0; c < tb.rows.length; c++)
 {
  var cb = tb.rows[c].cells[0].getElementsByTagName('INPUT')[0];
  if(!cb || !cb.id) return;
  xml+= " "+cb.id+"='"+(cb.checked ? '0' : '1')+"'";
 }
 xml+= "/"+">";

 /* FATTURE PA - SENDMAIL */
 if(FATTUREPA_INSTALLED)
 {
  saveFatturePASendmail(xml, function(xml){
	 saveTheRest(xml,callback);
	});
 }
 else
  saveTheRest(xml,callback);
}

function saveFatturePASendmail(xml, callback)
{
 var ed = document.getElementById('fatturepa-email-message');
 var sendmailSubject = document.getElementById('fatturepa-email-subject').value;
 var refAP = ed.getAttribute('refap') ? ed.getAttribute('refap') : "aboutconfig_htmlparms";
 var refID = ed.getAttribute('refid') ? ed.getAttribute('refid') : 0;

 var sh = new GShell();
 sh.OnError = function(err){SH_PROC.processMessage.error(err);}
 sh.OnOutput = function(o,a){
	 xml+= "<fatturepa emailsubject=\""+sendmailSubject.E_QUOT()+"\" messageap=\""+refAP+"\" messageid=\""+a['id']+"\"/"+">";
	 // save next sequence number
	 var nsn = document.getElementById('fatturepa-nextseqnum').value;
	 if(nsn != document.getElementById('fatturepa-nextseqnum').defaultValue)
	 {
	  var sh2 = new GShell();
	  sh2.OnError = function(err){SH_PROC.processMessage.error(err);}
	  sh2.OnOutput = function(){callback(xml);}
	  sh2.sendCommand("dynarc edit-archive -ap fatturepa -params `nextseqnum="+nsn+"`");
	 }
	 else
	  callback(xml);
	}
 if(!refID)
  sh.sendCommand("dynarc new-cat -ap aboutconfig_htmlparms -name `Documenti commerciali` -tag gcommercialdocs --if-not-exists && dynarc new-item -ap aboutconfig_htmlparms -ct gcommercialdocs -name 'Messaggio email x PA' -desc `"+ed.getValue()+"`");
 else
  sh.sendCommand("dynarc edit-item -ap '"+refAP+"' -id '"+refID+"' -desc `"+ed.getValue()+"`");
}

function saveTheRest(xml, callback)
{
 /* Inserire qui il salvataggio di tutto il resto, prima del saveFinish */
 saveVisibledDocs(function(){
	 saveFinish(xml, callback);
	}); 
}

function saveVisibledDocs(callback)
{
 var sh = new GShell();
 sh.OnError = function(err){SH_PROC.processMessage.error(err);}
 sh.OnFinish = function(){callback();}

 var tb = document.getElementById('visibleddocs');
 for(var c=0; c < tb.rows.length; c++)
 {
  var r = tb.rows[c];
  var cb = r.cells[0].getElementsByTagName('INPUT')[0];
  sh.sendCommand("dynarc edit-cat -ap commercialdocs -id '"+cb.getAttribute('data-catid')+"'"+((cb.checked == true) ? " --publish" : " --unpublish"));
 }
}

function saveFinish(xml,callback)
{
 var sh = new GShell();
 sh.OnError = function(err){SH_PROC.processMessage.error(err);}
 sh.OnOutput = function(){
	 SH_PROC.hideProcessMessage();
	 if(callback) return callback();
	 alert("Salvataggio completato!\nSiccome sei sotto utente root (super-amministratore) verrà effettuato un log-out, in modo che tu possa loggarti come utente normale.");
	 Template.Exit();
	}
 sh.sendCommand("aboutconfig set-config -app gcommercialdocs -sec interface -xml-config `"+xml+"`");
}

function InstallPackage(pkgname)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 window.setTimeout(function(){
		 alert("Il pacchetto è stato installato!");
		 FATTUREPA_INSTALLED = true;
		 document.getElementById('install-fatturepa-p').style.display = "none";
		 document.getElementById('fatturepa-installed-div').style.display = "";
		 Template.initEd(document.getElementById("fatturepa-email-message"),"fckeditor","Optimized");
		}, 1000);
	}
 sh.sendCommand("gframe -f apm -params `installpackages="+pkgname+"`");
}
</script>
<?php

$template->End();

?>
