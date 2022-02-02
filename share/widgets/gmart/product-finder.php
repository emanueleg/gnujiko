<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 20-11-2016
 #PACKAGE: gmart
 #DESCRIPTION: Product Finder
 #VERSION: 2.5beta
 #CHANGELOG: 20-11-2016 : Aggiornata funzione SubmitAction, aggiunto le varianti.
			 09-01-2015 : Inserito RPP.
			 06-06-2014 : Aggiunta colonna prezzo.
			 03-06-2014 : Bug fix su searchbydivision
 #TODO:
 #DEPENDS: glight-template, gmutable, gserppagenav
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES, $_COMMERCIALDOCS_CONFIG;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include($_BASE_PATH."var/templates/glight/index.php");

$template = new GLightTemplate("widget");
$template->includeInternalObject("contactsearch");
$template->includeObject("gmutable");
$template->includeObject("gserppagenav");
$template->includeCSS("share/widgets/gmart/product-finder.css");

$template->Begin("Cerca un articolo");
$template->Header();
//-------------------------------------------------------------------------------------------------------------------//
$_RPP = 20;

$_FILTERS = array(
	"cat"=>"categoria",
	"code_str"=>"cod. art. interno", 
	"vencode"=>"cod. art. fornitore", 
	"manufacturer_code"=>"cod. art. produttore", 
	"barcode"=>"codice a barre", 
	"name"=>"descrizione", 
	"brand"=>"marca", 
	"vendor"=>"fornitore", 
	"gebinde_code"=>"cod. confezionamento", 
);

include_once($_BASE_PATH."etc/commercialdocs/config.php");
if($_COMMERCIALDOCS_CONFIG['DIVISION'])
 $_FILTERS['division'] = "divisione materiale";

$_FILTER = $_REQUEST['filter'] ? $_REQUEST['filter'] : "name";
//-------------------------------------------------------------------------------------------------------------------//
$template->SubHeaderBegin(0,0,10);
?>
<input type='button' class="button-blue menuwhite" value="Menu" connect='mainmenu' id='menubutton' style='float:left'/>
 <ul class='popupmenu' id='mainmenu'>
  <li onclick='SubmitAction()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/add-mini.png"/>Includi selezionati</li>
 </ul>

<input type='button' class="button-gray menu" value="Visualizza" connect='columnsmenu' id='columnsmenubutton' style='float:left;margin-left:10px'/>
 <ul class='popupmenu' id='columnsmenu'>
  <li><input type='checkbox' onclick="RESULTS_TB.showHideColumn('code_str',this.checked)" checked='true'/>Codice</li>
  <li><input type='checkbox' onclick="RESULTS_TB.showHideColumn('manufacturer_code',this.checked)"/>Cod. Produttore</li>
  <li><input type='checkbox' onclick="RESULTS_TB.showHideColumn('vencode',this.checked)"/>Cod. Art. Fornitore</li>
  <li><input type='checkbox' onclick="RESULTS_TB.showHideColumn('barcode',this.checked)"/>Codice a barre</li>
  <li><input type='checkbox' onclick="RESULTS_TB.showHideColumn('smalldesc',this.checked)" checked='true'/>Descrizione articolo</li>
  <li><input type='checkbox' onclick="RESULTS_TB.showHideColumn('qty',this.checked)" checked='true'/>Qt&agrave;</li>
  <li><input type='checkbox' onclick="RESULTS_TB.showHideColumn('baseprice',this.checked)" checked='true'/>Prezzo base</li>
 </ul>

<!-- INPUT SEARCH - DEFAULT FILTERS -->
<?php
echo "<input type='text' class='edit' id='search' style='width:250px;float:left;margin-left:30px;";
switch($_FILTER)
{
 case 'name' : echo "' placeholder='digita una descrizione'"; break;
 case 'code_str' : echo "' placeholder='ricerca per codice'"; break;
 case 'vencode' : echo "' placeholder='ricerca per cod. art. fornitore'"; break;
 case 'manufacturer_code' : echo "' placeholder='ricerca per codice produttore'"; break;
 case 'model' : echo "' placeholder='ricerca per modello'"; break;
 case 'barcode' : echo "' placeholder='digita il codice a barre'"; break;
 case 'gebinde_code' : echo "' placeholder='digita il cod. di confezionamento'"; break;
 default : echo "display:none'"; break;
}
echo "/>";
?>
<!-- FILTER BY BRAND -->
<input type='text' class='edit' id='searchbybrand' ap='brands' style="width:250px;float:left;margin-left:30px;<?php if($_FILTER != 'brand') echo 'display:none'; ?>"/>

<?php
if($_FILTERS['division'])
{
 ?>
 <!-- FILTER BY DIVISION -->
 <input type='text' class='dropdown' readonly='true' id='searchbydivision' connect='searchbydivisionlist' style="width:250px;float:left;margin-left:30px;<?php if($_FILTER != 'division') echo 'display:none'; ?>"/>
 <ul class='popupmenu' id='searchbydivisionlist'>
  <li value='' retval=''>&nbsp;</li>
  <?php
  reset($_COMMERCIALDOCS_CONFIG['DIVISION']);
  while(list($k,$v)=each($_COMMERCIALDOCS_CONFIG['DIVISION']))
  {
   echo "<li value='".$k."' retval='".$k."'>".$v."</li>";
  }
  ?>
 </ul>
 <?php
}
?>

<!-- FILTER BY VENDOR -->
<input type='text' class='edit' id='searchbyvendor' ap='rubrica' ct='vendors' style="width:250px;float:left;margin-left:30px;<?php if($_FILTER != 'vendor') echo 'display:none'; ?>"/>
<!-- FILTER BY CATEGORY -->
<input type='text' class='search' id='searchbycat' ap='gmart' style="width:250px;float:left;margin-left:30px;<?php if($_FILTER != 'cat') echo 'display:none'; ?>"/>


<input type='button' class='button-search' id='searchbtn'/>
<span class='smalltext' style='float:left;margin-left:20px;vertical-align:top;height:30px;line-height:30px'>filtra per:</span>
<input type='text' class='dropdown' style='width:160px;float:left;margin-left:5px' readonly='true' connect='filterlist' id='filterselect' placeholder="seleziona un filtro" retval='name' value='descrizione' />
<ul class='popupmenu' id='filterlist'>
<?php
 reset($_FILTERS);
 while(list($k,$v)=each($_FILTERS))
 {
  echo "<li value='".$k."'>".$v."</li>";
 }
?>
</ul>


<?php
$template->SubHeaderEnd();
//-------------------------------------------------------------------------------------------------------------------//
$template->Body("widget",800);
//-------------------------------------------------------------------------------------------------------------------//
?>
<div class='gmutable' id='itemlist-container' style="height:360px;width:760px;margin:10px;border:0px">
<table id="itemlist" class="gmutable" cellspacing='0' cellpadding='0' border='0'>
<tr><th width='32' style='text-align:center'><input type='checkbox' onclick='RESULTS_TB.selectAll(this.checked)'/></th>
    <th width='80' id='code_str' style='text-align:center'>CODICE</th>
    <th width='80' id='manufacturer_code' style='text-align:center;display:none'>COD.PROD.</th>
	<th width='80' id='vencode' style='text-align:center;display:none'>COD.ART.FORNIT.</th>
    <th width='100' id='barcode' style='text-align:center;display:none'>BARCODE</th>
    <th id='name'>ARTICOLO</th>
	<th width='60' id='qty' editable='true' style='text-align:center' format='number'>QTA'</th>
	<th width='60' id='baseprice' style='text-align:center' format='currency'>PREZZO BASE</th>
	<th width='18'>&nbsp;</th>
</tr>
</table>
</div>

<div class='serp' style='width:790'>
<table width='100%' cellspacing='0' cellpadding='0' border='0'>
<tr><td id='serp' height='40' valign='middle'><i>nessun risultato trovato</i></td>
	<td width='400'><div id='GSERPPAGENAVSPACE'></div></td>
	<td width='150'><small>Mostra</small> <input type='text' class='dropdown' id='rpp' value="<?php echo $_RPP; ?> righe" retval="<?php echo $_RPP; ?>" readonly='true' connect='rpplist' style='width:80px'/> <small>x pg.</small>
	<ul class='popupmenu' id='rpplist'>
	 <li value='10'>10 righe</li>
	 <li value='25'>25 righe</li>
	 <li value='50'>50 righe</li>
	 <li value='100'>100 righe</li>
	 <li value='250'>250 righe</li>
	 <li value='500'>500 righe</li>
	</ul>
	</td>
</tr>
</table>
</div>

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
   break;
  }
 }
}
if(!$storeInfo && count($list))
 $storeInfo = $list[0];

$footer = "<div class='basket' id='basket' style='float:left;width:500px'></div>";
$footer.= "<input type='button' class='button-blue' value='Includi selezionati e chiudi &raquo;' style='float:right' onclick='SubmitAction()'/>";
$template->Footer($footer,true);
//-------------------------------------------------------------------------------------------------------------------//
?>
<script>
var RPP = <?php echo $_RPP ? $_RPP : '20'; ?>;
var RESULTS_TB = null;
var SERP = new GSERPPageNav(0,RPP);
var CMD = "";

var SELECTED = new Array();
var SELECTED_BY_ID = new Array();

Template.OnInit = function(){
 	this.initBtn(document.getElementById('menubutton'), "popupmenu");
 	this.initBtn(document.getElementById('columnsmenubutton'), "popupmenu");

	document.getElementById('search').onchange = function(){runQuery();}
	this.initEd(document.getElementById('searchbybrand'), "itemfind").onchange = function(){runQuery();};
    if(document.getElementById('searchbydivision'))
	 this.initEd(document.getElementById('searchbydivision'), "dropdown").onselect = function(){runQuery();};
	this.initEd(document.getElementById('searchbyvendor'), "itemfind").onchange = function(){runQuery();};
	this.initEd(document.getElementById('searchbycat'), "catfind").onchange = function(){runQuery();};

	this.initBtn(document.getElementById("searchbtn")).onclick = function(){runQuery();};
	this.initEd(document.getElementById('filterselect'), "dropdown").onselect = function(){
		 /* DEFAULT FILTERS */
		 switch(this.getAttribute('retval'))
		 {
		  case 'name' : {
			 var ed = document.getElementById("search");
			 ed.placeholder = "digita una descrizione"; 
			 ed.style.display = "";
			 ed.value = "";
			} break;

		  case 'code_str' : {
			 var ed = document.getElementById("search");
			 ed.placeholder = "ricerca per codice";
			 ed.style.display = "";
			 ed.value = "";
			} break;

		  case 'vencode' : {
			 var ed = document.getElementById("search");
			 ed.placeholder = "ricerca per cod. art. fornitore";
			 ed.style.display = "";
			 ed.value = "";
			} break;

		  case 'manufacturer_code' : {
			 var ed = document.getElementById("search");
			 ed.placeholder = "ricerca per codice produttore";
			 ed.style.display = "";
			 ed.value = "";
			} break;

		  case 'model' : {
			 var ed = document.getElementById("search");
			 ed.placeholder = "ricerca per modello";
			 ed.style.display = "";
			 ed.value = "";
			} break;

		  case 'barcode' : {
			 var ed = document.getElementById("search");
			 ed.placeholder = "digita il codice a barre";
			 ed.style.display = "";
			 ed.value = "";
			} break;

		  case 'gebinde_code' : {
			 var ed = document.getElementById("search");
			 ed.placeholder = "digita il cod. di confezionamento";
			 ed.style.display = "";
			 ed.value = "";
			} break;

		  default : document.getElementById("search").style.display = "none"; break;
		 }

		 /* FILTER BY BRAND */
		 if(this.getAttribute('retval') == "brand")
		 {
		  document.getElementById("searchbybrand").style.display = "";
		  document.getElementById("searchbybrand").value = "";
		 }
		 else
		  document.getElementById("searchbybrand").style.display = "none";

		 /* FILTER BY DIVISION */
		 if(document.getElementById("searchbydivision"))
		 {
		  if(this.getAttribute('retval') == "division")
		   document.getElementById("searchbydivision").style.display = "";
		  else
		   document.getElementById("searchbydivision").style.display = "none";
		 }

		 /* FILTER BY VENDOR */
		 if(this.getAttribute('retval') == "vendor")
		 {
		  document.getElementById("searchbyvendor").style.display = "";
		  document.getElementById("searchbyvendor").value = "";
		 }
		 else
		  document.getElementById("searchbyvendor").style.display = "none";

		 /* FILTER BY CAT */
		 if(this.getAttribute('retval') == "cat")
		 {
		  document.getElementById("searchbycat").style.display = "";
		  document.getElementById("searchbycat").value = "";
		 }
		 else
		  document.getElementById("searchbycat").style.display = "none";
		};


	RESULTS_TB = new GMUTable(document.getElementById('itemlist'), {autoresize:true, autoaddrows:false});
	RESULTS_TB.OnBeforeAddRow = function(r){
		 r.cells[0].innerHTML = "<input type='checkbox'/ >"; r.cells[0].style.textAlign='center';
		 r.cells[1].style.textAlign='center'; r.cells[1].innerHTML = "<span class='graybold'></span>";	// code_str
		 r.cells[2].style.textAlign='center'; r.cells[2].innerHTML = "<span class='graybold'></span>";	// manufacturer_code
		 r.cells[3].style.textAlign='center'; r.cells[3].innerHTML = "<span class='graybold'></span>";	// vencode
		 r.cells[4].style.textAlign='center'; r.cells[4].innerHTML = "<span class='graybold'></span>";	// barcode

		 r.cells[5].innerHTML = "<span class='graybold'></span>";										// name
		 r.cells[6].style.textAlign='center'; r.cells[6].innerHTML = "<span class='graybold'></span>";	// qty
		 r.cells[7].style.textAlign='right'; r.cells[7].innerHTML = "<span class='graybold'></span>";	// baseprice

		}
	RESULTS_TB.OnDeleteRow = function(r){}

	RESULTS_TB.OnSelectRow = function(r){
		 var value = r.cell['qty'].getValue()
		 if(!parseFloat(value))
		  r.cell['qty'].setValue(1);
		 if(!SELECTED_BY_ID[r.data['id']])
		 {
		  var ret = {data:r.data,qty:parseFloat(value)};
		  SELECTED.push(ret);
		  SELECTED_BY_ID[r.data['id']] = ret;
		 }
		 else
		  SELECTED_BY_ID[r.data['id']].qty = parseFloat(value)
		 updateBasket();
		}

	RESULTS_TB.OnUnselectRow = function(r){
		 r.cell['qty'].setValue("");
		 r.cell['qty'].getElementsByTagName('SPAN')[0].innerHTML = "";
		 var ret = SELECTED_BY_ID[r.data['id']];
		 if(ret)
		 {
		  SELECTED.splice(SELECTED.indexOf(ret),1);
		  SELECTED_BY_ID[r.data['id']] = null;
		 }
		 updateBasket();
		}

	RESULTS_TB.OnCellEdit = function(r,cell,value){
		 switch(cell.tag)
		 {
		  case 'qty' : {
			 if(parseFloat(value))
			  r.select(true);
			 else
			 {
			  cell.getElementsByTagName('SPAN')[0].innerHTML = "";
			  r.select(false);
			 }
			} break; /* EOF CASE QTY */
		 }
		}


	/* SERP */
	document.getElementById('GSERPPAGENAVSPACE').appendChild(SERP.O);
	SERP.autoupdate = false;
	SERP.OnChange = function(currPage, start, rpp){

	 var sh = new GShell();
	 sh.showProcessMessage("Caricamento in corso", "Attendere prego! E' in corso la ricerca nei cataloghi.");
	 sh.OnError = function(err){this.hideProcessMessage(); alert(err);}
	 sh.OnOutput = function(o,a){
		 RESULTS_TB.EmptyTable();
		 var container = document.getElementById("itemlist-container");
		 container.scrollTop = 0;
		 this.hideProcessMessage();
		 if(!a || !a['results']) 
		  return updateSERP();
		 for(var c=0; c < a['results'].length; c++)
		  insertRow(a['results'][c]);
		 updateSERP(a['serp']);
		}
	 sh.sendCommand(CMD+" -limit '"+start+","+rpp+"'");
	}

	this.initEd(document.getElementById('rpp'), "dropdown").onchange = function(){
		 SERP.ResultsPerPage = this.getValue();
		 RPP = this.getValue();
		 runQuery();
		}

}

function runQuery()
{
 var filter = document.getElementById('filterselect').getValue();
 CMD = "fastfind products";
 switch(filter)
 {
  case 'name' : case 'code_str' : case 'manufacturer_code' : case 'model' : case 'barcode' : case 'gebinde_code' : {
	 var ed = document.getElementById("search");
	 CMD+= " -fields '"+filter+"' `"+ed.value+"`"; 
	} break;
  
  case 'brand' : {
	 var ed = document.getElementById("searchbybrand");
	 if(ed.value && ed.data)
	  CMD+= " -fields brand_id `"+ed.data['id']+"`";
	 else
	  CMD+= " -fields brand `"+ed.value+"`";
	} break;

  case 'vendor' : {
	 var ed = document.getElementById("searchbyvendor");
	 if(ed.value && ed.data)
	  CMD+= " -vendorid '"+ed.data['id']+"'";
	 else
	  CMD+= " -vendor `"+ed.value+"`";
	} break;

  case 'vencode' : {
	 var ed = document.getElementById("search");
	 CMD+= " -vencode `"+ed.value+"`"; 
	} break;

  case 'division' : {
	 if(document.getElementById("searchbydivision"))
	 {
	  var ed = document.getElementById("searchbydivision");
	  if(ed.value && ed.getValue())
	   CMD+= " -fields division `"+ed.getValue()+"`";
	  else
	   CMD+= " -fields division ``";
	 }
	} break;

  case 'cat' : {
	 var ed = document.getElementById("searchbycat");
	 if(ed.value && ed.getId())
	  CMD+= " -ap '"+ed.getAttribute('ap')+"' -cat '"+ed.getId()+"'";
	 else
	  CMD+= " -ap '"+ed.getAttribute('ap')+"'";
	} break;

 }

 RESULTS_TB.EmptyTable();
 var container = document.getElementById("itemlist-container");
 container.scrollTop = 0;

 var sh = new GShell();
 sh.showProcessMessage("Caricamento in corso", "Attendere prego! E' in corso la ricerca nei cataloghi.");
 sh.OnError = function(err){this.hideProcessMessage(); alert(err);}
 sh.OnOutput = function(o,a){
	 this.hideProcessMessage();
	 //RESULTS_TB.EmptyTable();
	 if(!a || !a['results']) 
	  return updateSERP();
	 for(var c=0; c < a['results'].length; c++)
	  insertRow(a['results'][c]);
	 updateSERP(a['serp']);
	}
 sh.sendCommand(CMD+" -limit '"+RPP+"'");
}

function updateSERP(serp)
{
 var resFrom = serp ? serp['from'] : 0;
 var resTo = serp ? serp['to'] : 0;
 var count = serp ? serp['count'] : 0;
 var pgidx = serp ? serp['pgidx'] : 0;
 if(count)
 {
  document.getElementById("serp").innerHTML = "Risultati: <b>"+resFrom+"</b> - <b>"+resTo+"</b> su <b>"+count+"</b>";
  SERP.Update(count, serp['rpp'], pgidx);
  SERP.O.style.visibility = "visible";
 }
 else
 {
  document.getElementById("serp").innerHTML = "Nessun risultato trovato";
  SERP.Update(count, serp ? serp['rpp'] : 20, 0);
  SERP.O.style.visibility = "hidden";
 }
 
}

function insertRow(data)
{
 var r = RESULTS_TB.AddRow();
 r.data = data;
 /*var html = "<div class='glproductinfo'>";
 html+= "<div class='glproduct-name'><span class='glproduct-code'>"+data['code_str']+"</span> - "+data['name']+"</div>";
 html+= "<div class='glproduct-barcode'>barcode:"+data['barcode']+"</div>";
 html+= "</div>";*/
 r.cell['code_str'].setValue(data['code_str']);
 r.cell['manufacturer_code'].setValue(data['manufacturer_code']);
 r.cell['vencode'].setValue(data['vencode']);
 r.cell['barcode'].setValue(data['barcode']);
 r.cell['name'].setValue(data['name']);
 //r.cell['smalldesc'].setValue(html);
 //r.cell['qty'].setValue("1");
 r.cell['baseprice'].setValue(data['baseprice']);

 if(SELECTED_BY_ID[r.data['id']])
 {
  r.cell['qty'].setValue(SELECTED_BY_ID[r.data['id']].qty);
  r.select(true);
 }
}

function updateBasket()
{
 if(SELECTED.length)
  document.getElementById("basket").innerHTML = "articoli selezionati <b>"+SELECTED.length+"</b>";
 else
  document.getElementById("basket").innerHTML = "";
}

function SubmitAction()
{
 var ret = new Array();
 for(var c=0; c < SELECTED.length; c++)
 {
  var a = new Array();
  a['tb_prefix'] = SELECTED[c].data['ap'];
  a['ap'] = SELECTED[c].data['ap'];
  a['id'] = SELECTED[c].data['id'];
  a['name'] = SELECTED[c].data['name'];
  a['code_str'] = SELECTED[c].data['code_str'];
  a['variant_name'] = SELECTED[c].data['variant_name'];
  a['variant_type'] = SELECTED[c].data['variant_type'];
  /* TODO: inserire il resto delle colonne */

  a['qty'] = SELECTED[c].qty;
  ret.push(a);
 }
 gframe_close(ret.length+" items has been selected.",ret);
}

</script>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$template->End();
//-------------------------------------------------------------------------------------------------------------------//

