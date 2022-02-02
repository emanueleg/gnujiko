<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 06-08-2016
 #PACKAGE: gstore
 #DESCRIPTION: Official Gnujiko Store Manager. ver.2
 #VERSION: 2.8beta
 #CHANGELOG: 06-08-2016 : Bug fix totali qta caricata,scaricata e movimentata.
			 09-04-2016 : Bug fix ricerca per fornitore.
			 08-04-2016 : Bug fix ricerca per data aggiunta icona e funzione doSearchByDate.
			 25-03-2016 : Possibilita di editare un movimento.
			 27-08-2014 : restricted access integration.
			 30-07-2014 : Integrato con prodotti finiti, componenti e materiali.
			 16-05-2014 : Aggiunto note su colonna Doc. di riferimento/notes
			 08-04-2014 : Bug fix vari.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_CMD, $_RESTRICTED_ACCESS;

$_BASE_PATH = "../";
$_RESTRICTED_ACCESS = "gstore";

include($_BASE_PATH."var/templates/glight/index.php");

$template = new GLightTemplate();
$template->includeObject("gcal");
$template->includeObject("editsearch");
$template->includeInternalObject("serp");
$template->includeInternalObject("productsearch");
$template->includeInternalObject("contactsearch");
$template->Begin("Magazzino");

/* GET CONFIG */
$ret = GShell("aboutconfig get-config -app gstore");
if(!$ret['error'])
 $config = $ret['outarr']['config'];

$_HIDE_ALLSTOREMENU = false;

if($config['options']['movements']['hideallmovmenu'])
{
 $_HIDE_ALLSTOREMENU = true;
 if($_SESSION['UNAME'] == 'root')
  $_HIDE_ALLSTOREMENU = false;
 else if($config['options']['movements']['hideallmovmenuexceptusrid'] && ($_SESSION['UID'] == $config['options']['movements']['hideallmovmenuexceptusrid']))
  $_HIDE_ALLSTOREMENU = false;
}

$_FILTERS = array("product"=>i18n('product'), "vendor"=>i18n('vendor'));
$_FILTER = $_REQUEST['filter'] ? $_REQUEST['filter'] : "product";

$dateFrom = $_REQUEST['from'] ? $_REQUEST['from'] : date("Y-m")."-01";
$dateTo = $_REQUEST['to'] ? $_REQUEST['to'] : date("Y-m-d",strtotime("+1 month",strtotime($dateFrom)));

$centerContents = "<span class='smalltext' style='float:left;height:30px;line-height:30px;margin-right:5px'>".i18n('Filter by:')."</span> ";
$centerContents.= "<input type='text' class='dropdown' style='width:100px;float:left' readonly='true' connect='filterlist' id='filterselect' retval='"
	.$_FILTER."' value='".$_FILTERS[$_FILTER]."'/>";
$centerContents.= "<ul class='popupmenu' id='filterlist'>";
while(list($k,$v) = each($_FILTERS))
{
 $centerContents.= "<li value='".$k."'>".$v."</li>";
}
$centerContents.= "</ul>";
switch($_FILTER)
{
 case 'product' : $centerContents.= "<input type='text' class='edit' style='width:300px;float:left' placeholder='".i18n('Find a product')."' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\" emptyonclick='true' ap='".($_REQUEST['refap'] ? $_REQUEST['refap'] : '')."'/>"; break;
 case 'vendor' : $centerContents.= "<input type='text' class='contact' style='width:300px;float:left' placeholder='".i18n('Find a vendor')."' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\" modal='extended' fields='code_str,name' contactfields='phone,phone2,cell,email' ct='vendors'/>"; break;
}

$centerContents.= "<input type='button' class='button-search' id='searchbtn'/>";
$centerContents.= "<input type='text' class='calendar' value='".date('d/m/Y',strtotime($dateFrom))."' id='datefrom' style='margin-left:30px'/>";
$centerContents.= "<span class='smalltext'> al </span> <input type='text' class='calendar' value='".date('d/m/Y',strtotime($dateTo))."' id='dateto'/>";
$centerContents.= " <img src='".$_ABSOLUTE_URL."share/icons/16x16/view-refresh.png' style='cursor:pointer' onclick='doSearchByDate()' title='Effettua la ricerca per data'/>";

$template->Header("search", $centerContents, "BTN_EXIT", 800);

/* Get store list */
$ret = GShell("store list");
$storelist = $ret['outarr'];
if($_REQUEST['storeid'])
{
 for($c=0; $c < count($storelist); $c++)
 {
  if($storelist[$c]['id'] == $_REQUEST['storeid'])
  {
   $storeInfo = $storelist[$c];
   break;
  }
 }
}

if($_HIDE_ALLSTOREMENU && !$storeInfo)
{
 if(count($storelist))
  $storeInfo = $storelist[0];
}

if(!$_REQUEST['show'])
 $_REQUEST['show'] = "all";

$_ORDER_BY = $_REQUEST['sortby'] ? $_REQUEST['sortby'] : "op_time";
$_ORDER_METHOD = $_REQUEST['sortmethod'] ? strtoupper($_REQUEST['sortmethod']) : "ASC";
$_RPP = $_REQUEST['rpp'] ? $_REQUEST['rpp'] : 10;
$_PG = $_REQUEST['pg'] ? $_REQUEST['pg'] : 1;

$_SERP = new SERP();
$_SERP->setOrderBy($_ORDER_BY);
$_SERP->setOrderMethod($_ORDER_METHOD);
$_SERP->setResultsPerPage($_RPP);
$_SERP->setCurrentPage($_PG);

$cmd = "store movements -from '".$dateFrom."' -to '".$dateTo."'";
if($storeInfo) $cmd.= " -store '".$storeInfo['id']."'";
switch($_REQUEST['show'])
{
 case 'upload' : $cmd.= " -action 1"; break;
 case 'download' : $cmd.= " -action 2"; break;
 case 'transfer' : $cmd.= " -action 3"; break;
}
if($_REQUEST['refap'] && $_REQUEST['refid']) 
 $cmd.= " -refap '".$_REQUEST['refap']."' -refid '".$_REQUEST['refid']."'";
else if($_REQUEST['vendorid'])
 $cmd.= " -refvendorid '".$_REQUEST['vendorid']."'";
else if(($_REQUEST['filter'] == "vendor") && $_REQUEST['search'])
{
 $ret = GShell("dynarc fast-search -ap rubrica `".$_REQUEST['search']."`");
 if(!$ret['error'] && $ret['outarr'])
  $cmd.= " -refvendorid '".$ret['outarr']['id']."'";
}

$_CMD = $cmd;
$ret = $_SERP->SendCommand($cmd);
$list = $ret['items'];

$template->SubHeaderBegin(20);
?>
 <input type='button' class="button-blue menuwhite" value="Menu" connect='mainmenu' id='menubutton' style="float:left"/>
 <img src="img/manual-load-btn.png" style="float:left;margin-left:15px;margin-top:1px;cursor:pointer" title="Carico manuale" onclick="ManualUpload()"/>
 <img src="img/manual-download-btn.png" style="float:left;margin-left:5px;margin-top:1px;cursor:pointer" title="Scarico manuale" onclick="ManualDownload()"/>
 <img src="img/manual-move-btn.png" style="float:left;margin-left:5px;margin-top:1px;cursor:pointer" title="Movimenta" onclick="ManualTransfer()"/>
 <ul class='popupmenu' id='mainmenu'>
  <li onclick="ManualUpload()"><img src="img/upload.png" height="16"/>Carica magazzino</li>
  <li onclick="ManualDownload()"><img src="img/download.png" height="16"/>Scarica dal magazzino</li>
  <li onclick="ManualTransfer()"><img src="img/transfer.png" height="16"/>Movimenta a magazzino</li>
  <li class='separator'>&nbsp;</li>
  <li onclick="DeleteSelectedMovements()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif" height="16"/>Elimina movimenti selezionati</li>
  <li class='separator'>&nbsp;</li>
  <li onclick="ExportToExcel(this)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/excel.png"/>Salva su Excel</li>
  <li onclick="Print(this)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/printer.gif"/>Stampa</li>
  <li class='separator'>&nbsp;</li>
  <li onclick="gotoAboutConfig()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/cog.gif"/>Configurazione avanzata</li>
 </ul>
 </td>
 <td width='180'>
  <input type='button' class="button-blue menuwhite" value="<?php echo $storeInfo ? $storeInfo['name'] : 'Tutti i magazzini'; ?>" connect='storeselmenu' id='storeselbutton' style="width:160px"/>
		<ul class='popupmenu' id='storeselmenu'>
		<?php
		for($c=0; $c < count($storelist); $c++)
		 echo "<li onclick='selectStore(".$storelist[$c]['id'].",this)'><img src='img/storeicon.png'/>".$storelist[$c]['name']."</li>";
		if(count($storelist))
		{
		 if(!$_HIDE_ALLSTOREMENU)
		 {
		  echo "<li class='separator'>&nbsp;</li>";
		  echo "<li onclick='selectStore(0,this)'><img src='img/allstores.png'/>Tutti i magazzini</li>";
		 }
		}
		?>
		</ul></td>
 <td width='410'>
	<?php
	$show = array("all"=>"Tutti", "upload"=>"Carico", "download"=>"Scarico", "transfer"=>"Movim.");
	$idx = 0;
	echo "<ul class='toggles' style='margin-left:30px;float:left'>";
	while(list($k,$v)=each($show))
	{
	 $class = "";
	 if($idx == 0)
	  $class = "first";
	 else if($idx == (count($show)-1))
	  $class = "last";
	 if($k == $_REQUEST['show'])
	  $class.= " selected";
	 echo "<li".($class ? " class='".$class."'" : "")." onclick=\"setShow('".$k."')\">".$v."</li>";
 	 $idx++;
	}
	echo "</ul>";
	?>
	&nbsp;&nbsp;
	<span class='smalltext'>Mostra</span>
	<input type='text' class='dropdown' id='rpp' value="<?php echo $_RPP; ?> righe" retval="<?php echo $_RPP; ?>" readonly='true' connect='rpplist' style='width:80px'/>
	<ul class='popupmenu' id='rpplist'>
	 <li value='10'>10 righe</li>
	 <li value='25'>25 righe</li>
	 <li value='50'>50 righe</li>
	 <li value='100'>100 righe</li>
	 <li value='250'>250 righe</li>
	 <li value='500'>500 righe</li>
	</ul>
 </td>
 <td width='180'>
	<?php $_SERP->DrawSerpButtons(true); ?>
 </td>
 <td>&nbsp; <?php
 
//---------------------------------------------//
$template->SubHeaderEnd();

$template->Body("default", 0);
?>
<table width='100%' cellspacing='0' cellpadding='0' border='0' class='sortable-table' id='productlist'>
<tr><th width='16'><input type='checkbox'/></th>
	<th width='70' style='text-align:center' sortable='true' field='op_time'>Data</th>
	<th width='50' style='text-align:center'>Ora</th>
	<th width='90' style='text-align:center' sortable='true' field='mov_act'>Operazione</th>
	<th width='60' sortable='true' field='ref_code'>Codice</th>
	<th sortable='true' field='ref_name'>Articolo</th>
	<th width='70' style='text-align:center' sortable='true' field='qty'>Qta</th>
	<th width='200'>Doc. di riferimento / Notes</th>
	<th width='100'>Causale</th>
</tr>
<?php
$db = new AlpaDatabase();
for($c=0; $c < count($list); $c++)
{
 $item = $list[$c];

 echo "<tr id='".$item['id']."' refap='".$item['ref_ap']."'><td><input type='checkbox'/></td>";
 echo "<td align='center'><span class='link blue' onclick='editMovement(".$item['id'].",this)'>".date('d/m/Y',$item['ctime'])."</span></td>";
 echo "<td align='center'>".date('H:i',$item['ctime'])."</td>";
 echo "<td align='center'>";
 switch($item['action'])
 {
  case 1 : echo "<span class='blue'>CARICO</span>"; break;
  case 2 : echo "<span class='green'>SCARICO</span>"; break;
  case 3 : echo "<span class='darkblue'>MOVIMENTA</span>"; break;
 }
 echo "</td>";
 echo "<td><span class='tinytext'>".$item['code']."</span></td>";
 echo "<td><span class='link blue' style='font-size:10px' onclick=\"showItemInfo('".$item['ref_at']."','".$item['ref_ap']."','".$item['ref_id']."')\">".$item['name']."</span></td>";
 echo "<td align='center'>".$item['qty']."</td>";
 echo "<td style='font-size:10px'>";
 $docandnotes = "";
 if($item['doc_ap'] && $item['doc_id'])
  $docandnotes = "<span class='link blue' onclick=\"showDocInfo('".$item['doc_ap']."','".$item['doc_id']."')\">".$item['doc_name']."</span>";
 else if($item['doc_ref'])
  $docandnotes = $item['doc_ref'];
 if($item['notes'])
  $docandnotes = $docandnotes ? $docandnotes."<br/><span class='tinytext'>".$item['notes']."</span>" : "<span class='tinytext'>".$item['notes']."</span>";
 echo $docandnotes ? $docandnotes : "&nbsp;";
 echo "</td>";
 if($item['causal'])
 {
  $db->RunQuery("SELECT name FROM dynarc_storemovcausals_items WHERE code_str='".$item['causal']."' AND trash='0'");
  if($db->Read())
   echo "<td>".$db->record['name']."</td>";
  else
   echo "<td>&nbsp;</td>";
 }
 else echo "<td>&nbsp;</td>";
 echo "</tr>";
}
$db->Close();
?>
</table>

<?php
/* GET STORE STATUS */
$cmd = "store get-status -from '".$dateFrom."' -to '".$dateTo."'";
if($storeInfo) $cmd.= " -store '".$storeInfo['id']."'";
if($_REQUEST['refap'] && $_REQUEST['refid']) $cmd.= " -refap '".$_REQUEST['refap']."' -refid '".$_REQUEST['refid']."'";

$ret = GShell($cmd,$sessid,$shellid);
$statusInfo = $ret['outarr'];

?>

<div class="totals-footer">
 <table width="100%" cellspacing="0" cellpadding="0" border="0">
  <tr><td rowspan='2' valign='middle'><input type='button' class='button-blue' value='Stampa' onclick="Print(this)"/></td>
	  <td align='center'><span class='smalltext'>qt&agrave; caricata</span></td>
	  <td align='center'><span class='smalltext'>qt&agrave; scaricata</span></td>
	  <td align='center'><span class='smalltext'>qt&agrave; movimentata</span></td></tr>
  <tr><td align='center'><span class='smalltext'><?php echo $statusInfo['upload_qty']; ?></span></td>
	  <td align='center'><span class='smalltext'><?php echo $statusInfo['download_qty']; ?></span></td>
	  <td align='center'><span class='smalltext'><?php echo $statusInfo['transfer_qty']; ?></span></td></tr>
 </table>
</div>

<?php

/*-------------------------------------------------------------------------------------------------------------------*/
$template->Footer();
?>
<script>
var ON_PRINTING = false;
var ON_EXPORT = false;
var FILTER = "<?php echo $_FILTER; ?>";

Template.OnExit = function(){
	document.location.href = ABSOLUTE_URL;
	return false;
}

function gotoAboutConfig()
{
 window.open(ABSOLUTE_URL+"aboutconfig/store/");
}

Template.OnInit = function(){
	this.initBtn(document.getElementById('menubutton'), "popupmenu");
	this.initBtn(document.getElementById('storeselbutton'), "popupmenu");
	this.initEd(document.getElementById('rpp'), "dropdown").onchange = function(){
		 Template.SERP.RPP = this.getValue();
		 Template.SERP.reload(0);
		}

	this.initEd(document.getElementById('filterselect'), "dropdown").onchange = function(){
		 Template.SERP.setVar("search", "");
		 Template.SERP.setVar("filter",this.getValue());
		 Template.SERP.setVar("refap","");
		 Template.SERP.setVar("refid",0);
		 Template.SERP.reload(0);
		};

	switch(FILTER)
	{
     case 'product' : {
		this.initEd(document.getElementById("search"), "gmart").OnSearch = function(){
			 if(this.value && this.data)
			 {
			  Template.SERP.setVar("search", this.data['name']);
			  Template.SERP.setVar("refap",this.data['ap']);
			  Template.SERP.setVar("refid",this.data['id']);
			  Template.SERP.setVar("vendorid",0);
			  Template.SERP.reload(0);
			 }
			 else
			 {
			  Template.SERP.setVar("search","");
			  Template.SERP.setVar("refap","");
			  Template.SERP.setVar("refid",0);
			  Template.SERP.setVar("vendorid",0);
			  Template.SERP.reload(0);
			 }
			};
		} break;
	 case 'vendor' : {
		this.initEd(document.getElementById("search"), "contactextended").OnSearch = function(){
			 Template.SERP.unsetVar("refap");
			 Template.SERP.unsetVar("refid");
			 if(this.value && this.data)
			 {
			  Template.SERP.setVar("search",this.value);
			  Template.SERP.setVar("vendorid",this.data['id']);
			 }
			 else
			 {
			  Template.SERP.setVar("search",this.value);
			  Template.SERP.setVar("vendorid",0);
			 }
			 Template.SERP.reload(0);
			};
		} break;
	}

	this.initEd(document.getElementById("datefrom"), "date").OnDateChange = function(date){
		 Template.SERP.setVar("from",date);
		};

	this.initEd(document.getElementById("dateto"), "date").OnDateChange = function(date){
		 Template.SERP.setVar("from",document.getElementById("datefrom").isodate);
		 Template.SERP.setVar("to",date);
		 Template.SERP.reload();
		};

	this.initBtn(document.getElementById("searchbtn")).onclick = function(){document.getElementById("search").OnSearch();}


	this.SERP = new SERP("<?php echo $_SERP->OrderBy; ?>", "<?php echo $_SERP->OrderMethod; ?>", "<?php echo $_SERP->RPP; ?>", "<?php echo $_SERP->PG; ?>");
	this.initSortableTable(document.getElementById("productlist"), this.SERP.OrderBy, this.SERP.OrderMethod).OnSort = function(field, method){
		Template.SERP.OrderBy = field;
	    Template.SERP.OrderMethod = method;
		Template.SERP.reload(0);
	}
}

function doSearchByDate()
{
 document.getElementById("dateto").OnDateChange(document.getElementById("dateto").isodate);
}


function showItemInfo(at,ap,id)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(a)
	  document.location.reload();
	}

 switch(at)
 {
  case 'gmart' : sh.sendCommand("gframe -f gmart/edit.item -params 'ap="+ap+"&id="+id+"'"); break;
  case 'gproducts' : sh.sendCommand("gframe -f gproducts/edit.item -params 'ap="+ap+"&id="+id+"'"); break;
  case 'gpart' : sh.sendCommand("gframe -f gpart/edit.item -params 'ap="+ap+"&id="+id+"'"); break;
  case 'gmaterial' : sh.sendCommand("gframe -f gmaterial/edit.item -params 'ap="+ap+"&id="+id+"'"); break;
  case 'gbook' : sh.sendCommand("gframe -f gbook/edit.item -params 'ap="+ap+"&id="+id+"'"); break;
 }

 
}

function showDocInfo(ap,id)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.sendCommand("dynlaunch -ap `"+ap+"` -id `"+id+"`");
}

function selectStore(id,li)
{
 Template.SERP.setVar("storeid",id);
 Template.SERP.reload(0);
}

function setShow(value)
{
 Template.SERP.setVar("show",value);
 Template.SERP.reload(0);
}

function ManualUpload()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){Template.SERP.reload();}
 sh.sendCommand("gframe -f gstore/manual.upload -params `storeid=<?php echo $_REQUEST['storeid']; ?>`");
}

function ManualDownload()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){Template.SERP.reload();}
 sh.sendCommand("gframe -f gstore/manual.download -params `storeid=<?php echo $_REQUEST['storeid']; ?>`");
}

function ManualTransfer()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){Template.SERP.reload();}
 sh.sendCommand("gframe -f gstore/manual.move -params `storeid=<?php echo $_REQUEST['storeid']; ?>`");
}

function Print(printBtn)
{
 if(ON_PRINTING)
  return alert("Attendi che il processo per l'esportazione in PDF abbia terminato.");

 printBtn.disabled = true;
 ON_PRINTING = true;

 var xml = "<xml>";
 xml+= "<field name='Data e ora' tag='ctime' format='datetime' width='25' align='center'/"+">";
 xml+= "<field name='Operazione' tag='action' retvalue='option' width='20' align='center'>";
 xml+= "<option value='1' retvalue='CARICO'/"+">";
 xml+= "<option value='2' retvalue='SCARICO'/"+">";
 xml+= "<option value='3' retvalue='MOVIMENTA'/"+">";
 xml+= "</field>";
 xml+= "<field name='Codice' tag='code' width='30' align='center'/"+">";
 xml+= "<field name='Descrizione' tag='name' width='55'/"+">";
 xml+= "<field name='Qta' tag='qty' format='number' width='15' align='center'/"+">";
 xml+= "<field name='Doc. di rif.' tag='doc_name' alternatetag='doc_ref' width='40'/"+">";
 xml+= "</xml>";

 var dateFrom = new Date();
 var dateTo = new Date();

 dateFrom.setFromISO(document.getElementById("datefrom").isodate);
 dateTo.setFromISO(document.getElementById("dateto").isodate);

 var title = "Movimenti magazzino dal "+dateFrom.printf('d.m.Y')+" al "+dateTo.printf('d.m.Y');
 var fileName = "movmag-"+dateFrom.printf('Ymd')+"-"+dateTo.printf('Ymd');

 var header = "<div style='font-size:14pt;font-family:arial,sans-serif'>";
 header+= "Movimenti di magazzino dal "+dateFrom.printf('d.m.Y')+" al "+dateTo.printf('d.m.Y');
 header+= "</div>";

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnPreOutput = function(){}
 sh.OnOutput = function(o,a){
	 printBtn.disabled = false;
	 ON_PRINTING = false;
	 window.open(ABSOLUTE_URL+a['fullpath']);
	}
 sh.sendCommand("pdf fast-export -title `"+title+"` -header `"+header+"` -format A4 -rpp 27 -margin 10 -filename `"+fileName+"` -xmlfields `"+xml+"` -cmd `<?php echo $_CMD; ?>` -resfield items");
}

function ExportToExcel(exportBtn)
{
 if(ON_EXPORT)
  return alert("Attendi che il processo per l'esportazione in Excel abbia terminato.");

 exportBtn.disabled = true;
 ON_EXPORT = true;

 var xml = "<xml>";
 xml+= "<field name='Data e ora' tag='ctime' format='datetime'/"+">";
 xml+= "<field name='Operazione' tag='action' retvalue='option'>";
 xml+= "<option value='1' retvalue='CARICO'/"+">";
 xml+= "<option value='2' retvalue='SCARICO'/"+">";
 xml+= "<option value='3' retvalue='MOVIMENTA'/"+">";
 xml+= "</field>";
 xml+= "<field name='Codice' tag='code'/"+">";
 xml+= "<field name='Descrizione' tag='name'/"+">";
 xml+= "<field name='Qta' tag='qty' format='number'/"+">";
 xml+= "<field name='Doc. di rif.' tag='doc_name' alternatetag='doc_ref'/"+">";
 xml+= "</xml>";

 var dateFrom = new Date();
 var dateTo = new Date();

 dateFrom.setFromISO(document.getElementById("datefrom").isodate);
 dateTo.setFromISO(document.getElementById("dateto").isodate);

 var title = "Movimenti magazzino dal "+dateFrom.printf('d.m.Y')+" al "+dateTo.printf('d.m.Y');
 var fileName = "movmag-"+dateFrom.printf('Ymd')+"-"+dateTo.printf('Ymd');

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnPreOutput = function(){}
 sh.OnOutput = function(o,a){
	 exportBtn.disabled = false;
	 ON_EXPORT = false;
	 document.location.href = ABSOLUTE_URL+"getfile.php?file="+a['filename'];
	}
 sh.sendCommand("excel fast-export -title `"+title+"` -filename `"+fileName+"` -xmlfields `"+xml+"` -cmd `<?php echo $_CMD; ?>` -resfield items");
}

function DeleteSelectedMovements()
{
 var tb = document.getElementById("productlist");
 var sel = tb.getSelectedRows();

 if(!sel.length)
  return alert("Nessun movimento selezionato");

 if(!confirm("Sei sicuro di voler eliminare i movimenti di magazzino selezionati?"))
  return;

 var q = "";
 for(var c=0; c < sel.length; c++)
  q+= " -id "+sel[c].id;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 Template.reload(0);
	}
 sh.sendCommand("store delete-movement"+q);
}

function editMovement(id, span)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 Template.SERP.reload();
	}

 sh.sendCommand("gframe -f gstore/edit.movement -params 'id="+id+"'");
}

</script>
<?php

$template->End();

?>


