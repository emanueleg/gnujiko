<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-12-2016
 #PACKAGE: gstore
 #DESCRIPTION: Official Gnujiko Store Manager
 #VERSION: 2.9beta
 #CHANGELOG: 05-12-2016 : Aggiunto processMessage su funzione Submit.
			 09-09-2016 : Possibilita di impostare magazzino predefinito scarico da aboutconfig.
			 16-12-2014 : Bug fix.
			 20-11-2014 : Bug fix.
			 19-11-2014 : Integrazione con varianti taglie e colori.
			 30-10-2014 : Aggiunto campo data.
			 25-08-2014 : Integrato con i libri.
			 30-07-2014 : Integrato con prodotti finiti, componenti e materiali.
			 08-03-2014 : Aggiunto campo note.

 #TODO: Quando si modificano coltin o sizmis bisogna modificare la funzione che aggiorna la disponibilità.
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES, $_STORE_ID, $_STORE_NAME;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include($_BASE_PATH."var/templates/glight/index.php");
include_once($_BASE_PATH."include/userfunc.php");
$template = new GLightTemplate("widget");
$template->includeInternalObject("productsearch");
$template->includeObject("gmutable");
$template->includeObject("gcal");

$template->Begin("Scaricamento manuale del magazzino");
$rightContents = "<span class='smalltext'>data:</span> <input type='text' class='calendar' value='".date('d/m/Y')."' id='cdate'/> <input type='text' class='edit' id='ctime' style='width:50px' placeholder='hh:mm' value='".date('H:i')."'/>";
$rightContents.= "<input type='button' class='button-exit' value='".i18n('Exit')."' onclick='Template.Exit()' style='margin-left:20px'/>";

$template->Header("widget", "Scaricamento manuale", $rightContents);

$archiveTypes = array();
if(_userInGroup("gmart") && file_exists($_BASE_PATH."Products/index.php"))
 $archiveTypes['gmart'] = "articoli";
if(_userInGroup("gproducts") && file_exists($_BASE_PATH."FinalProducts/index.php"))
 $archiveTypes['gproducts'] = "prodotti finiti";
if(_userInGroup("gpart") && file_exists($_BASE_PATH."Parts/index.php"))
 $archiveTypes['gpart'] = "componenti";
if(_userInGroup("gmaterial") && file_exists($_BASE_PATH."Materials/index.php"))
 $archiveTypes['gmaterial'] = "materiali";
if(_userInGroup("gbook") && file_exists($_BASE_PATH."Books/index.php"))
 $archiveTypes['gbook'] = "libri";

$_AT = $_REQUEST['at'] ? $_REQUEST['at'] : 'gmart';

// GET CONFIG
$config = array();
$ret = GShell("aboutconfig get-config -app gstore", $_REQUEST['sessid'], $_REQUEST['shellid']);
if(!$ret['error'])
{
 $config = $ret['outarr']['config'];
 if(is_array($config['options']['defaultstores']) && !$_REQUEST['storeid'])
  $_REQUEST['storeid'] = $config['options']['defaultstores']['download'];
}

//-------------------------------------------------------------------------------------------------------------------//
$template->SubHeaderBegin(0,0,10);
?>
<input type='button' class="button-blue menuwhite" value="Menu" connect='mainmenu' id='menubutton' style='float:left'/>
 <ul class='popupmenu' id='mainmenu'>
  <li onclick='DeleteSelected()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/>Elimina selezionati</li>
 </ul>

<input type='button' class="button-gray menu" value="Visualizza" connect='columnsmenu' id='columnsmenubutton' style='float:left;margin-left:10px'/>
 <ul class='popupmenu' id='columnsmenu'>
  <li><input type='checkbox' onclick="tb.showHideColumn('code_str',this.checked)"/>Codice</li>
  <li><input type='checkbox' onclick="tb.showHideColumn('manufacturer_code',this.checked)"/>Cod. Produttore</li>
  <li><input type='checkbox' onclick="tb.showHideColumn('barcode',this.checked)"/>Codice a barre</li>
  <li><input type='checkbox' onclick="tb.showHideColumn('smalldesc',this.checked)" checked='true'/>Descrizione articolo</li>
  <li><input type='checkbox' onclick="tb.showHideColumn('lot',this.checked)" checked='true'/>Lotto</li>
  <li><input type='checkbox' onclick="tb.showHideColumn('coltint',this.checked)" checked='true'/>Colore/Tinta</li>
  <li><input type='checkbox' onclick="tb.showHideColumn('sizmis',this.checked)" checked='true'/>Taglia/Misura</li>
  <li><input type='checkbox' onclick="tb.showHideColumn('qty',this.checked)" checked='true'/>Qt&agrave;</li>
  <li><input type='checkbox' onclick="tb.showHideColumn('avail',this.checked)" checked='true'/>Disponibilit&agrave;</li>
 </ul>

<input type='text' readonly='true' class='dropdown' id='archivetype' connect='archivetypelist' value="<?php echo $archiveTypes[$_AT]; ?>" retval="<?php echo $_AT; ?>" style='width:100px;float:left;margin-left:30px'/>
<ul class='popupmenu' id='archivetypelist'>
<?php
$db = new AlpaDatabase();
while(list($k,$v)=each($archiveTypes))
{
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_archives WHERE archive_type='".$k."' AND trash='0'");
 if($db->Read())
 {
  echo "<li value='".$k."'>".$v."</li>";
  if(!$_AT) $_AT = $k;
 }
}
$db->Close();
$phsearch = "";
switch($_AT)
{
 case 'gmart' : $phsearch = "Cerca un articolo"; break;
 case 'gproducts' : $phsearch = "Cerca un prodotto finito"; break;
 case 'gpart' : $phsearch = "Cerca un componente"; break;
 case 'gmaterial' : $phsearch = "Cerca un materiale"; break;
 case 'gbook' : $phsearch = "Cerca un libro"; break;
}

?>
</ul>
<input type='text' class='edit' style='width:290px;float:left' placeholder="<?php echo $phsearch; ?>" id='search' emptyonclick='true' at="<?php echo $_AT; ?>"/>
<input type='button' class='button-search' id='searchbtn'/>
<?php
$template->SubHeaderEnd();
//-------------------------------------------------------------------------------------------------------------------//
$template->Body("widget",800);
//-------------------------------------------------------------------------------------------------------------------//
?>
<div class='gmutable' style="height:400px;width:760px;margin:10px;border:0px">
<table id="itemlist" class="gmutable" cellspacing='0' cellpadding='0' border='0'>
<tr><th width='32' style='text-align:center'><input type='checkbox' onclick='tb.selectAll(this.checked)'/></th>
    <th width='80' id='code_str' editable='true' style='text-align:center;display:none'>CODICE</th>
    <th width='80' id='manufacturer_code' editable='true' style='text-align:center;display:none'>COD.PROD.</th>
    <th width='100' id='barcode' editable='true' style='text-align:center;display:none'>BARCODE</th>
    <th width='100' id='name' editable='true' style='display:none'>ARTICOLO</th>
	<th minwidth='200' id='smalldesc' xlsexport='false'>ARTICOLO</th>
	<th width='60' id='lot' editable='true' style='text-align:center'>LOTTO</th>
	<th width='100' id='coltint' editable='true' format='dropdown' style='text-align:center'>COLORE/TINTA</th>
	<th width='100' id='sizmis' editable='true' format='dropdown' style='text-align:center'>TAGLIA/MISURA</th>
	<th width='60' id='qty' editable='true' style='text-align:center' format='number'>QTA'</th>
	<th width='60' id='avail' editable='true' style='text-align:center' format='number'>DISP.</th>
	<th width='18'>&nbsp;</th>
</tr>
</table>
</div>
<textarea class="textarea" style="width:780px;height:50px;margin-bottom:5px;resize:none" placeholder="Inserisci qui eventuali note" id="notes"></textarea>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$ret = GShell("store list",$_REQUEST['sessid'],$_REQUEST['shellid']);
$list = $ret['outarr'];
$storeInfo = null;
if($_REQUEST['storeid'])
{
 for($c=0; $c < count($list); $c++)
 {
  if($list[$c]['id'] == $_REQUEST['storeid'])
  {
   $storeInfo = $list[$c];
   $_STORE_ID = $storeInfo['id'];
   $_STORE_NAME = $storeInfo['name'];
   break;
  }
 }
}
if(!$storeInfo && count($list))
{
 $storeInfo = $list[0];
 $_STORE_ID = $storeInfo['id'];
 $_STORE_NAME = $storeInfo['name'];
}

$ret = GShell("dynarc item-list -ap storemovcausals -ct DOWNLOAD",$_REQUEST['sessid'],$_REQUEST['shellid']);
$causalList = $ret['outarr']['items'];

$footer.= "<span class='smalltext'>Scarica dal magazzino: </span>";
$footer.= "<input type='text' class='dropdown' readonly='true' connect='storelist' id='storeselect' placeholder='seleziona un magazzino' retval='"
	.$_STORE_ID."' value=\"".$_STORE_NAME."\" style='width:180px'/>";
$footer.= "<ul class='popupmenu' id='storelist'>";
for($c=0; $c < count($list); $c++)
 $footer.= "<li value='".$list[$c]['id']."'><img src='".$_ABSOLUTE_URL."share/widgets/gstore/img/storeicon.png'/>".$list[$c]['name']."</li>";
$footer.= "</ul>";

$footer.= "<span class='smalltext' style='margin-left:20px'>Causale: </span>";
$footer.= "<input type='text' class='dropdown' readonly='true' connect='causallist' id='causalselect' style='width:180px'/>";
$footer.= "<ul class='popupmenu' id='causallist'>";
for($c=0; $c < count($causalList); $c++)
 $footer.= "<li value='".$causalList[$c]['code_str']."'>".$causalList[$c]['name']."</li>";
$footer.= "</ul>";


$footer.= "<input type='button' class='button-blue' value='Procedi &raquo;' style='float:right' onclick='SubmitAction()'/>";
$template->Footer($footer,true);
//-------------------------------------------------------------------------------------------------------------------//
?>
<script>
var tb = null;
var currentStoreId = 0;
var AT = "<?php echo $_AT; ?>";

Template.OnInit = function(){
 	this.initBtn(document.getElementById('menubutton'), "popupmenu");
 	this.initBtn(document.getElementById('columnsmenubutton'), "popupmenu");
	this.initEd(document.getElementById('archivetype'), "dropdown").onchange = function(){
		 var sE = document.getElementById("search");
		 switch(this.getValue())
		 {
		  case 'gmart' : {sE.placeholder = "Cerca un articolo";} break;
		  case 'gproducts' : {sE.placeholder = "Cerca un prodotto finito";} break;
		  case 'gpart' : {sE.placeholder = "Cerca un componente";} break;
		  case 'gmaterial' : {sE.placeholder = "Cerca un materiale";} break;
		  case 'gbook' : {sE.placeholder = "Cerca un libro";} break;
		 }
		 sE.setAT(this.getValue());
		};

	this.initEd(document.getElementById("search"), AT, "barcode").OnSearch = function(){
		 if(this.value && this.data)
		  insertRow(this.data);
		};
	this.initBtn(document.getElementById("searchbtn")).onclick = function(){document.getElementById("search").OnSearch();}
	this.initEd(document.getElementById('storeselect'), "dropdown").onchange = function(){
		 if(currentStoreId && (currentStoreId != this.getValue()) && (tb.O.rows.length > 1))
		 {
		  if(!confirm("Se cambi magazzino devo ricalcolare le disponibilità di tutti gli articoli nella lista. Procedo?"))
		  {
		   /* ripristina il vecchio magazzino */
		   this.restoreOldValue();
		   return;
		  }
		  else
		  {
		   /* ricalcola le giacenze di tutti gli articoli */
		   checkAvailability();
		  }
		 }
		 currentStoreId = this.getValue();
		};
    currentStoreId = document.getElementById('storeselect').getValue();
	this.initEd(document.getElementById('causalselect'), "dropdown").onchange = function(){};

	this.initEd(document.getElementById('cdate'), 'date');

	tb = new GMUTable(document.getElementById('itemlist'), {autoresize:true, autoaddrows:false});
	tb.OnCellEdit = function(r,cell,value){
		 switch(cell.tag)
		 {
		  case 'qty' : checkAvailability(r,value); break;
		  case 'avail' : {
			 if(!confirm("Sei sicuro di voler forzare la giacenza fisica di questo articolo?"))
			  return cell.restoreOldValue();
			 else
			 {
			  var sh = new GShell();
			  sh.OnError = function(err){alert(err);}
			  sh.OnOutput = function(o,a){
				 r.data['store_'+currentStoreId+'_qty'] = value;
				 alert("La giacenza di questo articolo è stata modificata");
				}
			  sh.sendCommand("store update-qty -ap '"+r.data['ap']+"' -id '"+r.data['id']+"' -store '"+currentStoreId+"' -qty '"+value+"' -coltint `"+r.cell['coltint'].getValue()+"` -sizmis `"+r.cell['sizmis'].getValue()+"`");
			 }
			} break;
		 case 'coltint' : {
		 	 var sh = new GShell();
		 	 sh.OnOutput = function(o,a){
				 if(a && a['code']) r.cell['code_str'].setValue(a['code']); 
				 var storeQty = a['store_'+currentStoreId+'_qty'] ? a['store_'+currentStoreId+'_qty'] : 0;
				 r.data['store_'+currentStoreId+'_qty'] = storeQty;
				 r.cell['avail'].setValue(value ? storeQty : a['avail']);
				 checkAvailability(r);
				}
		 	 sh.sendCommand("dynarc ext-find -ap '"+r.getAttribute('refap')+"' -itemid '"+r.getAttribute('refid')+"' -ext varcodes -types color,tint -name `"+value+"` --get-availability -coltint `"+value+"` -sizmis `"+r.cell['sizmis'].getValue()+"`");
			} break;
	  	 case 'sizmis' : {
		 	 var sh = new GShell();
		 	 sh.OnOutput = function(o,a){
				 if(a && a['code']) r.cell['code_str'].setValue(a['code']); 
				 var storeQty = a['store_'+currentStoreId+'_qty'] ? a['store_'+currentStoreId+'_qty'] : 0;
				 r.data['store_'+currentStoreId+'_qty'] = storeQty;
				 r.cell['avail'].setValue(value ? storeQty : a['avail']);
				 checkAvailability(r);
				}
		 	 sh.sendCommand("dynarc ext-find -ap '"+r.getAttribute('refap')+"' -itemid '"+r.getAttribute('refid')+"' -ext varcodes -types size,dim,other -name `"+value+"` --get-availability -coltint `"+r.cell['coltint'].getValue()+"` -sizmis `"+value+"`");
			} break;
		 }
		}
	tb.OnBeforeAddRow = function(r){
		 r.cells[0].innerHTML = "<input type='checkbox'/ >"; r.cells[0].style.textAlign='center';
		 r.cells[1].style.textAlign='center'; r.cells[1].innerHTML = "<span class='graybold'></span>";
		 r.cells[2].style.textAlign='center'; r.cells[2].innerHTML = "<span class='graybold'></span>";
		 r.cells[3].style.textAlign='center'; r.cells[3].innerHTML = "<span class='graybold'></span>";
		 r.cells[4].innerHTML = "<span class='graybold'></span>";

		 r.cells[6].style.textAlign='center'; r.cells[6].innerHTML = "<span class='graybold'></span>";
		 r.cells[7].style.textAlign='center'; r.cells[7].innerHTML = "<span class='graybold'></span>";
		 r.cells[8].style.textAlign='center'; r.cells[8].innerHTML = "<span class='graybold'></span>";
		 r.cells[9].style.textAlign='center'; r.cells[9].innerHTML = "<span class='graybold'></span>";
		 r.cells[10].style.textAlign='center'; r.cells[10].innerHTML = "<span class='graybold'></span>";
		}
	tb.OnDeleteRow = function(r){}
	document.getElementById("search").focus();
}

function insertRow(data)
{
 for(var c=1; c < tb.O.rows.length; c++)
 {
  if((tb.O.rows[c].data['ap'] == data['ap']) && (tb.O.rows[c].data['id'] == data['id']))
  {
   var r = tb.O.rows[c];
   if(r.cell['coltint'].getValue() || r.cell['sizmis'].getValue())
	continue;
   var storeQty = parseFloat(r.cell['avail'].getValue());
   var qty = parseFloat(r.cell['qty'].getValue())+1;
   if(storeQty < qty)
   {
	alert("Disponibilità insufficiente!\nLa giacenza fisica in questo magazzino dell'articolo "+(r.data['code_str'] ? "cod. "+r.data['code_str']+" - " : "")+r.data['name']+" è di "+storeQty+" pezzi");
	return false;
   }
   r.cell['qty'].setValue(qty);
   return;
  }
 }

 var storeId = document.getElementById('storeselect').getValue();
 if(!storeId)
  return alert("Devi selezionare prima il magazzino da cui scaricare");

 var r = tb.AddRow();
 r.data = data;
 r.setAttribute('refap',data['ap']);
 r.setAttribute('refid',data['id']);
 var html = "<div class='glproductinfo'>";
 html+= "<div class='glproduct-name'><span class='glproduct-code'>"+data['code_str']+"</span> - "+data['name']+"</div>";
 html+= "<div class='glproduct-barcode'>barcode:"+data['barcode']+"</div>";
 html+= "</div>";
 r.cell['code_str'].setValue(data['code_str']);
 r.cell['manufacturer_code'].setValue(data['manufacturer_code']);
 r.cell['barcode'].setValue(data['barcode']);
 r.cell['name'].setValue(data['name']);
 r.cell['smalldesc'].setValue(html);
 r.cell['qty'].setValue("1");
 var storeQty = parseFloat(data['store_'+storeId+'_qty']);
 r.cell['avail'].setValue(storeQty);
 if(storeQty < 1)
 {
  alert("L'articolo "+(data['code_str'] ? "cod. "+data['code_str']+" - " : "")+data['name']+" non c'è in questo magazzino");
  r.select();
 }

 if(data)
 {
  var sh = new GShell();
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,a){
	 if(!a) return;
	 //r.data = a;
	 if(a['variants'])
	 {
	  var options = new Array();
	  if(a['variants']['colors'])
	  {
	   var arr = new Array();
	   for(var c=0; c < a['variants']['colors'].length; c++)
	    arr.push(a['variants']['colors'][c]['name']);
	   options.push(arr);
	  }
	  if(a['variants']['tint'])
	  {
	   var arr = new Array();
	   for(var c=0; c < a['variants']['tint'].length; c++)
	    arr.push(a['variants']['tint'][c]['name']);
	   options.push(arr);
	  }
	  r.cell['coltint'].setOptions(options);
	  var options = new Array();
	  if(a['variants']['sizes'])
	  {
	   var arr = new Array();
	   for(var c=0; c < a['variants']['sizes'].length; c++)
	    arr.push(a['variants']['sizes'][c]['name']);
	   options.push(arr);
	  }
	  if(a['variants']['dim'])
	  {
	   var arr = new Array();
	   for(var c=0; c < a['variants']['dim'].length; c++)
	    arr.push(a['variants']['dim'][c]['name']);
	   options.push(arr);
	  }
	  if(a['variants']['other'])
	  {
	   var arr = new Array();
	   for(var c=0; c < a['variants']['other'].length; c++)
	    arr.push(a['variants']['other'][c]['name']);
	   options.push(arr);
	  }
	  r.cell['sizmis'].setOptions(options);
	 } 
	}
  sh.sendCommand("commercialdocs getfullinfo -ap '"+data['ap']+"' -id '"+data['id']+"' --get-variants");
 }

}

function checkAvailability(r,qty)
{
 var storeId = document.getElementById('storeselect').getValue();
 var ok = true;
 if(r)
 {
  var storeQty = parseFloat(r.data['store_'+storeId+'_qty']);
  if(!qty)
   qty = parseFloat(r.cell['qty'].getValue());
  if(storeQty < qty)
  {
   alert("Disponibilità insufficiente!\nLa giacenza fisica in questo magazzino dell'articolo "+(r.data['code_str'] ? "cod. "+r.data['code_str']+" - " : "")+r.data['name']+" è di "+storeQty+" pezzi");
   return false;
  }
  return true;
 }
 else
 {
  tb.selectAll(false);
  for(var c=1; c < tb.O.rows.length; c++)
  {
   var r = tb.O.rows[c];
   var storeQty = parseFloat(r.data['store_'+storeId+'_qty']);
   r.cell['avail'].setValue(storeQty);
   var qty = parseFloat(r.cell['qty'].getValue());
   if(storeQty < qty)
   {
    r.select();
    ok = false;
   }
  }
  if(!ok)
   alert("Ci sono degli articoli con disponibilità insufficente");
 }
 return ok;
}

function SubmitAction()
{
 var ctime = document.getElementById('cdate').isodate;
 if(document.getElementById('ctime').value != "")
  ctime+= " "+document.getElementById('ctime').value;

 var storeId = document.getElementById('storeselect').getValue();
 if(!storeId)
  return alert("Devi selezionare un magazzino");

 var causal = document.getElementById('causalselect').getValue();

 if(tb.O.rows.length < 2)
  return alert("Nessun articolo da scaricare.");

 var ok = checkAvailability();
 if(!ok)
  return;

 var q = "";
 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];
  q+= " -ap '"+(r.data ? r.data['ap'] : '')+"'";
  q+= " -id '"+(r.data ? r.data['id'] : '0')+"'";
  q+= " -qty '"+r.cell['qty'].getValue()+"'";
  q+= " -lot '"+r.cell['lot'].getValue()+"'";
  q+= " -code `"+r.cell['code_str'].getValue()+"`";
  q+= " -mancode `"+r.cell['manufacturer_code'].getValue()+"`";
  q+= " -barcode `"+r.cell['barcode'].getValue()+"`";
  q+= " -name `"+r.cell['name'].getValue()+"`";
  q+= " -coltint '"+r.cell['coltint'].getValue().E_QUOT()+"'";
  q+= " -sizmis '"+r.cell['sizmis'].getValue().E_QUOT()+"'";
 }
 
 var notes = document.getElementById("notes").value;

 var sh = new GShell();
 sh.showProcessMessage("Scaricamento del magazzino", "Attendere prego, &egrave; in corso l&lsquo;aggiornamento delle scorte di magazzino.");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(o,a){this.hideProcessMessage(); gframe_close(o,a);}
 sh.sendCommand("store download -ctime '"+ctime+"' -store `"+storeId+"`"+q+" -notes `"+notes+"` -causal '"+causal+"'");
}

function ImportFromExcel()
{
}

function ExportToExcel()
{
 var date = new Date();
 var fileName = "scarico-magazzino-"+date.printf("dmYHi");
 tb.ExportToExcel(fileName);
}

function SendMail()
{
}

function PrintTable()
{
}

function DeleteSelected()
{
 var list = tb.GetSelectedRows();
 if(!list.length)
  return alert("Nessun articolo è stato selezionato");
 if(!confirm("Sei sicuro di voler rimuovere le righe selezionate?"))
  return;
 tb.DeleteSelectedRows();
}
</script>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$template->End();
//-------------------------------------------------------------------------------------------------------------------//

