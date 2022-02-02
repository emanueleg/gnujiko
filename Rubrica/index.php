<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-05-2017
 #PACKAGE: rubrica
 #DESCRIPTION: 
 #VERSION: 2.13beta
 #CHANGELOG: 23-05-2017 : Integrazione con agenti.
			 20-05-2017 : Aggiunto parametro --inherit-parent-sharing su NewSubCategory.
			 23-05-2016 : Aggiornata funzione ExportToExcel.
			 21-02-2016 : Aggiustamenti grafici.
			 13-02-2016 : Bug fix e aggiunto funzioni.
			 19-02-2015 : Ricerca avanzata.
			 14-12-2014 : Aggiunta funzione printContact
			 29-09-2014 : Aggiunto punti fidelity card.
			 27-08-2014 : restricted access integration.
			 16-07-2014 : Riattivato optbox su campo di ricerca.
			 02-06-2014 : Possibilità di creare categorie.
			 15-05-2014 : prima integrazione con aboutconfig.
			 03-05-2014 : Possibilità di filtrare anche per fidelitycard.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_CMD, $_RESTRICTED_ACCESS, $_STRICT_AGENT, $_AGENT_ID;

$_BASE_PATH = "../";
$_RESTRICTED_ACCESS = "rubrica";
$_AGENT_ID = 0;
$_STRICT_AGENT = false;

if(($_COOKIE['gnujiko_ui_devtype'] == "phone") && file_exists($_BASE_PATH."Rubrica/index-mobi.php"))
{
 include($_BASE_PATH."Rubrica/index-mobi.php");
 exit();
}

include($_BASE_PATH."var/templates/glight/index.php");

include_once($_BASE_PATH."include/userfunc.php");
if(_getGroupName($_SESSION['GID']) == "agents")
{
 // get agent id
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT rubrica_id FROM gnujiko_users WHERE id='".$_SESSION['UID']."'");
 if($db->Read())
 {
  $_AGENT_ID = $db->record['rubrica_id'];
  $_STRICT_AGENT = true;
 }
 $db->Close();
}


$template = new GLightTemplate();
$template->includeObject("gcal");
$template->includeObject("editsearch");
$template->includeInternalObject("serp");
$template->includeInternalObject("contactsearch");
$template->includeInternalObject("labels");
$template->includeCSS("rubrica.css");

$_FILTERS = array(
 'taxcode' => 'Cod. fiscale',
 'vatnumber' => 'Partita IVA',
 'address' => 'Indirizzo',
 'city' => 'Citt&agrave;',
 'province' => 'Provincia',
 'phone' => 'Telefono',
 'email' => 'Email',
 'fidelitycard' => 'Fidelity Card'
);


// get cat info
$ret = GShell("dynarc cat-info -ap rubrica -id '".$_REQUEST['cat']."'");
if(!$ret['error'])
 $_CAT_INFO = $ret['outarr'];


// divide columns by field
$_COL_BY_FIELD = array();
for($c=0; $c < count($template->config['columns']); $c++)
 $_COL_BY_FIELD[$template->config['columns'][$c]['field']] = $template->config['columns'][$c];

// get pricelist by id
$ret = GShell("pricelists list");
$pricelists = $ret['outarr'];
$pricelistById = array();
for($c=0; $c < count($pricelists); $c++)
 $pricelistById[$pricelists[$c]['id']] = $pricelists[$c]['name'];

// get paymentmodes by id 
$ret = GShell("paymentmodes list");
$paymentmodes = $ret['outarr'];
$paymentmodeById = array();
for($c=0; $c < count($paymentmodes); $c++)
 $paymentmodeById[$paymentmodes[$c]['id']] = $paymentmodes[$c]['name'];

// get labels
$_LABEL_BY_ID = array();
$_LABELS = array();
$ret = GShell("dynarc exec-func ext:labels.list -params `archiveprefix=rubrica`");
if(!$ret['error'])
{
 $_LABELS = $ret['outarr'];
 for($c=0; $c < count($_LABELS); $c++)
  $_LABEL_BY_ID[$_LABELS[$c]['id']] = $_LABELS[$c];
}

/* ABOUTCONFIG */

$_RESTRICTIONS = array();
if($template->config['aboutconfig'] && $template->config['aboutconfig']['config']['restrictions'])
 $_RESTRICTIONS = $template->config['aboutconfig']['config']['restrictions'];

$template->Begin("Anagrafica - ".$_CAT_INFO['name']);

$centerContents = "<input type='text' class='contact' style='width:390px;float:left' placeholder='Cerca un contatto' id='search' value=\""
	.(!$_REQUEST['filter'] ? htmlspecialchars($_REQUEST['search'],ENT_QUOTES) : '')."\" modal='extended' fields='code_str,name,fidelitycard' contactfields='phone,phone2,cell,email' into='".$_CAT_INFO['tag']."' />";
$centerContents.= "<input type='button' class='button-search' id='searchbtn'/>";
$centerContents.= "<input type='text' class='dropdown' id='labelfilter' connect='labellist' style='width:200px;float:left;margin-left:40px' placeholder='Filtra per etichetta' value='".($_REQUEST['label'] ? $_LABEL_BY_ID[$_REQUEST['label']]['name'] : "")."' retval='"
	.$_REQUEST['label']."' readonly='true'/>";
$centerContents.= "<ul class='popupmenu' id='labellist'>";
$centerContents.= "<li value=''>mostra tutti</li>";
for($c=0; $c < count($_LABELS); $c++)
{
 $centerContents.= "<li value='".$_LABELS[$c]['id']."'>".$_LABELS[$c]['name']."</li>";
}
$centerContents.= "<li class='separator'>&nbsp;</li>";
$centerContents.= "<li value='0'>tutti quelli senza etichette</li>";
$centerContents.= "</ul>";

$template->Header("search", $centerContents, "BTN_EXIT", 700);

$_ORDER_BY = $_REQUEST['sortby'] ? $_REQUEST['sortby'] : "name";
$_ORDER_METHOD = $_REQUEST['sortmethod'] ? strtoupper($_REQUEST['sortmethod']) : "ASC";
$_RPP = $_REQUEST['rpp'] ? $_REQUEST['rpp'] : 25;
$_PG = $_REQUEST['pg'] ? $_REQUEST['pg'] : 1;

$_SERP = new SERP();
$_SERP->setOrderBy($_ORDER_BY);
$_SERP->setOrderMethod($_ORDER_METHOD);
$_SERP->setResultsPerPage($_RPP);
$_SERP->setCurrentPage($_PG);

if(($_REQUEST['filter'] == "address") || ($_REQUEST['filter'] == "city") || ($_REQUEST['filter'] == "province") || ($_REQUEST['filter'] == "phone") || ($_REQUEST['filter'] == "email"))
{
 /* RICERCA PER INDIRIZZO */
 $cmd = "dynarc cross-search -ap rubrica -ext contacts -ap2 rubrica -get `code_str` -extget `rubricainfo,contacts,labels,banks`";
 $where = "";
 if(!$_REQUEST['allcat'])
  $where = "item.cat_id='".$_CAT_INFO['id']."'";
 if($_AGENT_ID)
  $where.= " AND item.agent_id='".$_AGENT_ID."'";

 switch($_REQUEST['filter'])
 {
  case 'province' : $where.= " AND ext.province='".$_REQUEST['search']."'"; break;

  case 'address' : $where.= " AND (ext.address='".$_REQUEST['search']."' OR ext.address LIKE '".$_REQUEST['search']."' OR ext.address LIKE '".$_REQUEST['search']."%' OR ext.address LIKE '%".$_REQUEST['search']."' OR ext.address LIKE '%".$_REQUEST['search']."%')"; break;

  case 'city' : $where.= " AND (ext.city='".$_REQUEST['search']."' OR ext.city LIKE '".$_REQUEST['search']."' OR ext.city LIKE '".$_REQUEST['search']."%' OR ext.city LIKE '%".$_REQUEST['search']."' OR ext.city LIKE '%".$_REQUEST['search']."%')"; break;

  case 'phone' : $where.= " AND (ext.phone='".$_REQUEST['search']."' OR ext.phone LIKE '".$_REQUEST['search']."' OR ext.phone LIKE '".$_REQUEST['search']."%' OR ext.phone LIKE '%".$_REQUEST['search']."' OR ext.phone LIKE '%".$_REQUEST['search']."%'"
	." OR ext.phone2='".$_REQUEST['search']."' OR ext.phone2 LIKE '".$_REQUEST['search']."' OR ext.phone2 LIKE '".$_REQUEST['search']."%' OR ext.phone2 LIKE '%".$_REQUEST['search']."' OR ext.phone2 LIKE '%".$_REQUEST['search']."%'"
	." OR ext.fax='".$_REQUEST['search']."' OR ext.fax LIKE '".$_REQUEST['search']."' OR ext.fax LIKE '".$_REQUEST['search']."%' OR ext.fax LIKE '%".$_REQUEST['search']."' OR ext.fax LIKE '%".$_REQUEST['search']."%'"
	." OR ext.cell='".$_REQUEST['search']."' OR ext.cell LIKE '".$_REQUEST['search']."' OR ext.cell LIKE '".$_REQUEST['search']."%' OR ext.cell LIKE '%".$_REQUEST['search']."' OR ext.cell LIKE '%".$_REQUEST['search']."%')"; break;

  case 'email' : $where.= " AND (ext.email='".$_REQUEST['search']."' OR ext.email LIKE '".$_REQUEST['search']."' OR ext.email LIKE '".$_REQUEST['search']."%' OR ext.email LIKE '%".$_REQUEST['search']."' OR ext.email LIKE '%".$_REQUEST['search']."%'"
	." OR ext.email2='".$_REQUEST['search']."' OR ext.email2 LIKE '".$_REQUEST['search']."' OR ext.email2 LIKE '".$_REQUEST['search']."%' OR ext.email2 LIKE '%".$_REQUEST['search']."' OR ext.email2 LIKE '%".$_REQUEST['search']."%'"
	." OR ext.email3='".$_REQUEST['search']."' OR ext.email3 LIKE '".$_REQUEST['search']."' OR ext.email3 LIKE '".$_REQUEST['search']."%' OR ext.email3 LIKE '%".$_REQUEST['search']."' OR ext.email3 LIKE '%".$_REQUEST['search']."%')"; break;
 }

 $cmd.= " -where `".ltrim($where, ' AND ')."`";
}
else
{
 /* RICERCA NORMALE */
 $cmd = "dynarc item-list -ap rubrica".($_REQUEST['allcat'] ? " --all-cat" : " -into '".$_CAT_INFO['id']."'");

 $where = "";
 if($_AGENT_ID)
  $where = "agent_id='".$_AGENT_ID."'";

 if($_REQUEST['search'])
 {
  switch($_REQUEST['filter'])
  {
   case 'taxcode' : $where.= " AND (taxcode='".$_REQUEST['search']."' OR taxcode LIKE '".$_REQUEST['search']."' OR taxcode LIKE '".$_REQUEST['search']."%')"; break;

   case 'vatnumber' : $where.= " AND (vatnumber='".$_REQUEST['search']."' OR vatnumber LIKE '".$_REQUEST['search']."' OR vatnumber LIKE '".$_REQUEST['search']."%')"; break;

   case 'fidelitycard' : $where.= " AND fidelitycard='".$_REQUEST['search']."'"; break;

   default : $where.= " AND (name='".$_REQUEST['search']."' OR name LIKE '".$_REQUEST['search']."' OR name LIKE '".$_REQUEST['search']."%' OR name LIKE '%".$_REQUEST['search']."' OR name LIKE '%".$_REQUEST['search']."%')"; break;
  }
 }

 if($_REQUEST['label'])
  $where.= " AND user_labels LIKE '%,".$_REQUEST['label'].",%'";
 else if($_REQUEST['untagged'])
  $where.= " AND user_labels=''";

 $cmd.= " -extget 'rubricainfo,contacts,labels,banks'";
 $cmd.= " -where `".ltrim($where, ' AND ')."`";
}

$_CMD = $cmd;
$ret = $_SERP->SendCommand($cmd);
$list = $_SERP->Results['items'];

$template->SubHeaderBegin(10);
?>
 <?php
 if(!$_STRICT_AGENT)
 {
  ?>
  <input type='button' class="button-blue" value="Nuovo contatto" onclick="NewItem()"/>
  <?php
 }
 else
  echo "&nbsp;";
 ?>
 </td>
 <td>
 <input type='button' class="button-blue menuwhite" value="Menu" connect='mainmenu' id='menubutton' style='float:left;margin-right:5px'/>
 <ul class='popupmenu' id='mainmenu'>
  <?php
  if(!$_STRICT_AGENT)
  {
   ?>
  <li onclick="NewItem()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/rubrica-contact-add.png" height="16"/>Nuovo contatto</li>
  <li onclick="NewSubCategory(<?php echo $_CAT_INFO['parent_id'] ? $_CAT_INFO['parent_id'] : $_CAT_INFO['id']; ?>)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/folder.gif" height="16"/>Nuova categoria</li>
  <?php
   $importRes = $_RESTRICTIONS['import'];
   if(!$importRes['group'] ||  _userInGroup($importRes['group']))
   {
	?>
    <li class='separator'>&nbsp;</li>
    <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/import_orange.gif"/>Importa
	<ul class='popupmenu'>
	 <li onclick='importFromXML()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/xml.png"/>da file XML</li>
	 <li onclick='importFromExcel()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/excel.png"/>da Excel</li>
	</ul>
    </li>
	<?php
   }

   $exportRes = $_RESTRICTIONS['export'];
   if(!$exportRes['group'] ||  _userInGroup($exportRes['group']))
   {
	?>
    <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/export2.png"/>Esporta
	<ul class='popupmenu'>
	 <li onclick='exportToXML()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/xml.png"/>su file XML</li>
	 <li onclick='exportToExcel()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/excel.png"/>su Excel</li>
	</ul>
    </li>
    <?php
   }
  ?>
  <li class='separator'>&nbsp;</li>
  <?php
  } // EOF - IF !STRICT AGENT
  ?>
  <li onclick="showAdvSearch()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/Icon_search_16.png"/>Mostra ricerca avanzata</li>
  <?php
  if(!$_STRICT_AGENT)
  {
   ?>
   <li class='separator'>&nbsp;</li>
   <li onclick="ConfigureLabels()"><img src="img/tag.png"/>Configura etichette</li>
   <li class='separator'>&nbsp;</li>
  
   <?php
   echo "<li onclick='EditCatProperties(".($_CAT_INFO['modinfo']['can_write'] ? 'true' : 'false').",".$_CAT_INFO['modinfo']['uid'].",".$_SESSION['UID'].")'><img src='".$_ABSOLUTE_URL."share/icons/16x16/info.gif'/>Propriet&agrave; categoria</li>";
   if($_CAT_INFO['parent_id'])
   {
    echo "<li onclick='DeleteCategory(".($_CAT_INFO['modinfo']['can_write'] ? 'true' : 'false').",".$_CAT_INFO['modinfo']['uid'].",".$_SESSION['UID'].")'><img src='".$_ABSOLUTE_URL."share/icons/16x16/delete.gif'/>Elimina categoria</li>";
   }
  } // EOF - IF !STRICT_AGENT
  ?>
 </ul>

 <?php
 if(!$_STRICT_AGENT)
 {
  ?>
  <input type='button' class="button-gray menu" value="Modifica" connect='editmenu' id='editmenubutton' style='float:left;margin-right:5px'/>
  <ul class='popupmenu' id='editmenu'>
   <li onclick="CopySelected()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/copy.png"/>Copia</li>
   <li onclick="MoveSelected()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/paste.gif"/>Sposta</li>
   <li class='separator'>&nbsp;</li>
   <li onclick="DeleteSelected()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/>Elimina selezionati</li>
  </ul>
  <?php
 }

 if(!$_STRICT_AGENT)
 {
  ?>
  <input type='button' class="button-gray menu" value="Visualizza" connect='showmenu' id='showmenubutton' style='float:left;margin-right:5px'/>
  <ul class='popupmenu' id='showmenu'>
   <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/column.gif"/>Colonne 
	<ul class='popupmenu'>
	  <?php
	   for($c=0; $c < count($template->config['standardcolumns']); $c++)
	   {
		$col = $template->config['standardcolumns'][$c];
		$checked = $_COL_BY_FIELD[$col['field']] ? true : false;
		echo "<li><input type='checkbox'".($checked ? " checked='true'" : "")." onchange=\"showColumn('".$col['field']."',this)\"/>".$col['title']."</li>";
	   }
	  ?>
	</ul>
   </li>
   <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/insert_many_objects.gif"/>Bottoni
	<ul class='popupmenu'>
	 <?php
	  reset($template->config['standardbuttons']);
	  while(list($k,$btn)=each($template->config['standardbuttons']))
	  {
	   echo "<li><input type='checkbox'".($btn['visibled'] ? " checked='true'" : "")." onchange=\"showButton('".$k."',this)\"/>".$btn['title']."</li>";
	  }
	 ?>
	</ul>
   </li>
   <li class='separator'>&nbsp;</li>
   <li onclick="saveColumnSettings()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/save.gif"/>Salva configurazione</li>
  </ul>
  <?php
 }
 ?>

 <input type='button' class="button-tag" title="Etichetta" id="tagbutton" ap="rubrica" style="float:left;display:none"/>
 </td>
 <td width='150'>
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
 <td width='200' style='padding-right:20px'>
	<?php $_SERP->DrawSerpButtons(true);
 
//---------------------------------------------//
$template->SubHeaderEnd();

$template->Body("bisection");
?>
<div style="margin-bottom:30px;<?php if(!$_REQUEST['filter']) echo 'display:none;'; ?>" id='advsearch-container'><h3 style="border-bottom:1px solid #dadada">Ricerca avanzata</h3>
 <table cellspacing='0' cellpadding='3' border='0'>
  <tr><td><span class='smalltext'>Cerca per: </span></td>
	  <td><input type='text' class='dropdown' id='advsearch-filter' connect='filterlist' retval="<?php echo $_REQUEST['filter'] ? $_REQUEST['filter'] : 'address'; ?>" value="<?php echo $_REQUEST['filter'] ? $_FILTERS[$_REQUEST['filter']] : 'Indirizzo'; ?>"/>
		  <ul class='popupmenu' id='filterlist'>
 		  <?php
 		   reset($_FILTERS);
 		   while(list($k,$v) = each($_FILTERS))
 		   {
  			echo "<li value='".$k."'>".$v."</li>";
 		   }
 		  ?> 
		  </ul> 
		  <input type='text' class='search' id='advsearch' value="<?php echo $_REQUEST['filter'] ? $_REQUEST['search'] : ''; ?>"/></td></tr>
  <tr><td>&nbsp;</td>
	  <td><input type='checkbox' id='advallcat' <?php if($_REQUEST['allcat']) echo "checked='true'"; ?>/> <span class='smalltext'>in tutte le categorie</span></td></tr>

  <tr><td colspan='2'>&nbsp;</td></tr>

  <tr><td>&nbsp;</td>
	  <td><input type='button' class='button-blue' value='Cerca' id='advsearchbtn'/>
		  <input type='button' class='button-gray' value='Annulla' onclick='hideAdvSearch()'/></td></tr>

 </table>
</div>

<table width='100%' cellspacing='0' cellpadding='0' border='0' class='sortable-table' id='contactlist'>
<tr><th width='16'><input type='checkbox'/></th>
	<?php
	/* COLUMNS */
	for($c=0; $c < count($template->config['standardcolumns']); $c++)
	{
	 $col = $template->config['standardcolumns'][$c];
	 $visibled = $_COL_BY_FIELD[$col['field']] ? true : false;
	 echo "<th".(!$visibled ? " style='display:none'" : "");
	 if($col['width'])		echo " width='".$col['width']."'";
	 if($col['field'])		echo " field='".$col['field']."'";
	 if($col['sortable'])	echo " sortable='true'";
	 echo ">".$col['title']."</th>";
	}
	/* BUTTONS */
	reset($template->config['standardbuttons']);
	while(list($k,$btn)=each($template->config['standardbuttons']))
	{
	 echo "<th coltype='button' field='btn-".$k."' width='22'".(!$btn['visibled'] ? " style='display:none'" : "").">&nbsp;</th>";
	}
	?>
</tr>
<?php
$count = $_SERP->Results['count'];
for($c=0; $c < count($list); $c++)
{
 $item = $list[$c];
 $address = "";
 $phone = "";
 $email = "";
 if(count($item['contacts']))
 {
  $address = $item['contacts'][0]['address']." ".$item['contacts'][0]['city'];
  $phone = $item['contacts'][0]['phone'] ? $item['contacts'][0]['phone'] : $item['contacts'][0]['cell'];
  $email = $item['contacts'][0]['email'];
 }
 echo "<tr id='".$item['id']."'><td><input type='checkbox'/></td>";
 /* COLUMNS */
 for($i=0; $i < count($template->config['standardcolumns']); $i++)
 {
  $col = $template->config['standardcolumns'][$i];
  $visibled = $_COL_BY_FIELD[$col['field']] ? true : false;
  echo "<td".(!$visibled ? " style='display:none'>" : ">");
  switch($col['field'])
  {
   case 'id' : echo $item['id']; break;
   case 'code_str' : echo "<span class='link blue' onclick='EditItem(\"".$item['id']."\")'>".$item['code_str']."</span>"; break;
   case 'name' : echo "<span class='link blue' onclick='EditItem(\"".$item['id']."\")'>".$item['name']."</span>"; break;
   case 'labels' : {
	 $labels = $item['user_labels'];
	 for($j=0; $j < count($labels); $j++)
	 {
	  $lab = $_LABEL_BY_ID[$labels[$j]];
	  if(!$lab)
	   echo "&nbsp;";
	  else
	   echo "<span class='label' style='background-color:".$lab['bgcolor'].";color:".$lab['color'].";'>".$lab['name']."</span>";
	 }
	} break;
   case 'address' : echo "<span class='tinytext'>".$address."</span>"; break;
   case 'phone' : echo $phone ? $phone : "&nbsp;"; break;
   case 'email' : echo $email ? $email : "&nbsp;"; break;
   case 'iscompany' : echo $item['iscompany'] ? "azienda" : "privato"; break;
   case 'taxcode' : echo $item['taxcode'] ? $item['taxcode'] : "&nbsp;"; break;
   case 'vatnumber' : echo $item['vatnumber'] ? $item['vatnumber'] : "&nbsp;"; break;
   case 'fidelitycard' : echo "<span class='tinytext'>".$item['fidelitycard']."</span>"; break;
   case 'fidelitycard_points' : echo "<span class='tinytext'>".$item['fidelitycard_points']."</span>"; break;
   case 'pricelist_id' : echo "<span class='tinytext'>".(($item['pricelist_id'] && $pricelistById[$item['pricelist_id']]) ? $pricelistById[$item['pricelist_id']] : "&nbsp;")."</span>"; break;
   case 'paymentmode' : echo "<span class='tinytext'>".(($item['paymentmode'] && $paymentmodeById[$item['paymentmode']]) ? $paymentmodeById[$item['paymentmode']] : "&nbsp;")."</span>"; break;
   case 'skype' : echo "<span class='tinytext'>".$item['skype']."</span>"; break;
   case 'agent' : echo $item['agent_id'] ? "<span class='link blue' onclick='EditItem(\"".$item['agent_id']."\")'>".$item['agent_name']."</span>" : "&nbsp;"; break;

   default : echo "&nbsp;"; break;
  }
  echo "</td>";
 }

 /* BUTTONS */
 reset($template->config['standardbuttons']);
 while(list($k,$btn)=each($template->config['standardbuttons']))
 {
  echo "<td".(!$btn['visibled'] ? " style='display:none'>" : ">");
  echo "<img src='img/".$btn['icon']."' title=\"".$btn['title']."\" style='cursor:pointer' ";
  switch($btn['action'])
  {
   case 'sendmail' : echo "onclick='sendMail(\"".$email."\")'"; break;
   case 'makeappointment' : echo "onclick='makeAppointment(\"".$item['id']."\",\"".$btn['ap']."\")'"; break;
   case 'printcontact' : echo "onclick='printContact(\"".$item['id']."\")'"; break;
  }
  echo "/>";
  echo "</td>";
 } 
 
 echo "</tr>";
}
?>
</table>
<div style='height:100px'></div>
<?php
/*-------------------------------------------------------------------------------------------------------------------*/
$template->Footer();
?>
<script>
var ON_PRINTING = false;
var ON_EXPORT = false;
var CAT_ID = "<?php echo $_CAT_INFO['id']; ?>";
var STRICT_AGENT = <?php echo $_STRICT_AGENT ? "true" : "false"; ?>;
var AGENT_ID = <?php echo $_AGENT_ID ? $_AGENT_ID : '0'; ?>;

Template.OnExit = function(){
	document.location.href = ABSOLUTE_URL;
	return false;
}

Template.OnInit = function(){
	if(document.getElementById('menubutton'))					this.initBtn(document.getElementById('menubutton'), "popupmenu");
	if(document.getElementById('editmenubutton'))				this.initBtn(document.getElementById('editmenubutton'), "popupmenu");
	if(document.getElementById('showmenubutton'))				this.initBtn(document.getElementById('showmenubutton'), "popupmenu");
	if(document.getElementById('advsearch-filter'))				this.initEd(document.getElementById('advsearch-filter'), "dropdown");

    document.getElementById("advsearch").onchange = function(){
		 Template.setVar("filter",document.getElementById('advsearch-filter').getValue());
		 Template.setVar("search",this.value);
		 if(document.getElementById('advallcat').checked == true)
		  Template.setVar("allcat","true");
		 else
		  Template.unsetVar("allcat");
		 Template.reload(0);
		}
	document.getElementById('advsearchbtn').onclick = function(){document.getElementById("advsearch").onchange();}

	this.initEd(document.getElementById("search"), "contactextended").OnSearch = function(){
		 if(this.value && this.data)
		  EditItem(this.data['id']);
		 else
		 {
		  Template.setVar("search",this.value);
		  Template.unsetVar("filter");
		  Template.unsetVar("allcat");
		  Template.reload(0);
		 }
		};
	this.initBtn(document.getElementById("searchbtn")).onclick = function(){document.getElementById("search").OnSearch();}

	this.SERP = new SERP("<?php echo $_SERP->OrderBy; ?>", "<?php echo $_SERP->OrderMethod; ?>", "<?php echo $_SERP->RPP; ?>", "<?php echo $_SERP->PG; ?>");
	var tb = this.initSortableTable(document.getElementById("contactlist"), this.SERP.OrderBy, this.SERP.OrderMethod);
	tb.OnSort = function(field, method){
		Template.SERP.OrderBy = field;
	    Template.SERP.OrderMethod = method;
		Template.SERP.reload(0);
	}
	tb.OnSelect = function(list){
	 if(!list.length)
	  document.getElementById("tagbutton").style.display = "none";
	 else
	 {
	  document.getElementById("tagbutton").style.display = "";
	  var id = 0;
	  if(list.length == 1)
	   id = list[0].id;
	  document.getElementById("tagbutton").UpdateLabels(id);
	 }
	}
    
	this.initEd(document.getElementById('rpp'), "dropdown").onchange = function(){
		 Template.SERP.RPP = this.getValue();
		 Template.SERP.reload(0);
		}

	this.initBtn(document.getElementById("tagbutton"), "labels").OnSubmit = function(ret){
		 var tb = document.getElementById("contactlist");
		 var sel = tb.getSelectedRows();
		 if(!sel.length)
		  return alert("Nessun contatto è stato selezionato");
		 
		 var cmd = "";
		 for(var c=0; c < sel.length; c++)
		  cmd+= " && dynarc edit-item -ap rubrica -id '"+sel[c].id+"' -extset `labels.userlabels='"+ret+"'`";

		 var sh = new GShell();
		 sh.OnError = function(err){alert(err);}
		 sh.OnOutput = function(){Template.SERP.reload();}
		 sh.sendCommand(cmd.substr(4));
		};

	this.initEd(document.getElementById('labelfilter'), "dropdown").onchange = function(){
		 var val = this.getValue();
		 if(val == "0")
		 {
		  Template.SERP.setVar("untagged","1");
		  Template.SERP.unsetVar("label");
		 }
		 else if(val)
		  Template.SERP.setVar("label",val);
		 else
		 {
		  Template.SERP.unsetVar("label");
		  Template.SERP.unsetVar("untagged");
		 }
		 Template.SERP.reload(0);
		};
}

function NewItem()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 document.location.reload();
	}

 sh.sendCommand("dynarc new-item -ap rubrica -group rubrica -cat '"+CAT_ID+"' || gframe -f rubrica.edit -params 'id='+*.id");
}

function NewSubCategory(parentId)
{
 var title = prompt("Digita il nome della nuova sottocategoria");
 if(!title) return;
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){document.location.href = ABSOLUTE_URL+"Rubrica/index.php?cat="+a['id']+"&view=default";}
 sh.sendCommand("dynarc new-cat -ap rubrica -parent '"+parentId+"' -name `"+title+"` -group rubrica --inherit-parent-sharing");
}

function EditItem(id)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 Template.SERP.reload();
	}
 sh.sendCommand("gframe -f rubrica.edit -params 'id="+id+"'");
}

function DeleteSelected()
{
 var tb = document.getElementById("contactlist");
 var sel = tb.getSelectedRows();
 if(!sel.length)
  return alert("Nessun contatto è stato selezionato");
 if(!confirm("Sei sicuro di voler eliminare i contatti selezionati?"))
  return;

 var q = "";
 for(var c=0; c < sel.length; c++)
  q+= " -id "+sel[c].id;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 Template.SERP.reload(0);
	}

 sh.sendCommand("dynarc delete-item -ap rubrica"+q);
}

function CopySelected()
{
 var tb = document.getElementById("contactlist");
 var sel = tb.getSelectedRows();
 if(!sel.length)
  return alert("Nessun contatto è stato selezionato");

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var itmq = "";
	 for(var c=0; c < sel.length; c++)
	 {
	  itmq+= " -id "+sel[c].id;
	 }

	 var sh2 = new GShell();
	 sh2.OnError = function(err){alert(err);}
	 sh2.OnOutput = function(o,a){
		 var msg = "sono stati copiati %d contatti.";
		 alert(msg.replace('%d',sel.length));
		}
	 sh2.sendCommand("dynarc item-copy -ap rubrica -cat "+a+itmq);
	}
 sh.sendCommand("gframe -f dynarc.categorySelect -params `ap=rubrica`");
}

function MoveSelected()
{
 var tb = document.getElementById("contactlist");
 var sel = tb.getSelectedRows();
 if(!sel.length)
  return alert("Nessun contatto è stato selezionato");

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var itmq = "";
	 for(var c=0; c < sel.length; c++)
	 {
	  itmq+= " -id "+sel[c].id;
	 }

	 var sh2 = new GShell();
	 sh2.OnError = function(err){alert(err);}
	 sh2.OnOutput = function(o,a){
		 var msg = "sono stati spostati %d contatti.";
		 alert(msg.replace('%d',sel.length));
		 Template.SERP.reload(0);
		}
	 sh2.sendCommand("dynarc item-move -ap rubrica -cat "+a+itmq);
	}
 sh.sendCommand("gframe -f dynarc.categorySelect -params `ap=rubrica`");
}

function ConfigureLabels()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){Template.SERP.reload();}
 sh.sendCommand("gframe -f config.labels -params `ap=rubrica`");
}

function importFromXML()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(){Template.SERP.reload(0);}
 sh.sendCommand("gframe -f dynarc.import -params `ap=rubrica&cat="+CAT_ID+"`");
}

function importFromExcel()
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var fileName = a['files'][0]['fullname'];

	 var sh2 = new GShell();
	 sh2.OnError = function(err){alert(err);}
	 sh2.OnFinish = function(){Template.SERP.reload(0);}
	 sh2.sendCommand("gframe -f excel/import -params `ap=rubrica&cat="+CAT_ID+"&file="+fileName+"`");
	}
 sh.sendCommand("gframe -f fileupload");
}

function exportToXML()
{
 var tb = document.getElementById("contactlist");
 var sel = tb.getSelectedRows();

 var q = "";
 if(!sel.length)
  q = "&cat="+CAT_ID;
 else
 {
  q = "&id=";
  for(var c=0; c < sel.length; c++)
   q+= sel[c].id+",";
  q = q.substr(0,q.length-1);
 }

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.sendCommand("gframe -f dynarc.export -params `ap=rubrica"+q+"`");
}

function exportToExcel()
{
 var tb = document.getElementById("contactlist");
 var sel = tb.getSelectedRows();

 var q = "";
 if(!sel.length)
  q = " -cat "+CAT_ID;
 else
 {
  q = " -ids '";
  for(var c=0; c < sel.length; c++)
   q+= sel[c].id+",";
  q = q.substr(0,q.length-1)+"'";
 }

 var xml = "<xml>";
 xml+= "<field name='Codice' tag='code_str'/"+">";
 xml+= "<field name='Nome e cognome / Ragione sociale' tag='name'/"+">";
 xml+= "<field name='Cod. fiscale' tag='taxcode'/"+">";
 xml+= "<field name='Partita IVA' tag='vatnumber'/"+">";
 xml+= "<field name='Fidelity card' tag='fidelitycard'/"+">";
 xml+= "<field name='Punti' tag='fidelitycard_points'/"+">";
 xml+= "<field name='Indirizzo' ext='contacts' tag='address'/"+">";
 xml+= "<field name='Città' ext='contacts' tag='city'/"+">";
 xml+= "<field name='Provincia' ext='contacts' tag='province'/"+">";
 xml+= "<field name='Paese' ext='contacts' tag='countrycode'/"+">";
 xml+= "<field name='Telefono' ext='contacts' tag='phone' alternatetag='phone2'/"+">";
 xml+= "<field name='Fax' ext='contacts' tag='fax'/"+">";
 xml+= "<field name='Cellulare' ext='contacts' tag='cell'/"+">";
 xml+= "<field name='Email' ext='contacts' tag='email'/"+">";
 xml+= "<field name='Skype' ext='contacts' tag='skype'/"+">";
 xml+= "<field name='Banca' ext='banks' tag='name'/"+">";
 xml+= "<field name='IBAN' ext='banks' tag='iban'/"+">";
 xml+= "</xml>";

 var title = "Rubrica";
 var fileName = "rubrica";

 var cmd = "<?php echo addcslashes($_CMD,'"'); ?>";

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnPreOutput = function(){}
 sh.OnOutput = function(o,a){
	 document.location.href = ABSOLUTE_URL+"getfile.php?file="+a['filename'];
	}
 sh.sendCommand("excel fast-export -title `"+title+"` -filename `"+fileName+"` -xmlfields `"+xml+"` -cmd `"+cmd+"` -resfield items");
}

function showColumn(field,cb)
{
 var tb = document.getElementById("contactlist");
 if(cb.checked == true)
  tb.showColumn(field);
 else
  tb.hideColumn(field);
}

function showButton(action,cb)
{
 var tb = document.getElementById("contactlist");
 if(cb.checked == true)
  tb.showColumn("btn-"+action);
 else
  tb.hideColumn("btn-"+action);
}

function saveColumnSettings()
{
 var xml = "";
 var xmlColumns = "";
 var xmlButtons = "";

 var tb = document.getElementById("contactlist");
 for(var c=0; c < tb.fields.length; c++)
 {
  var th = tb.fields[c];
  if(th.style.display != "none")
  {
   if(th.getAttribute('coltype') == "button")
   {
	xmlButtons+= "<button action='"+th.getAttribute('field').substr(4)+"'/"+">";
   }
   else
   {
    xmlColumns+= "<column title='"+th.textContent+"' field='"+th.getAttribute('field')+"'";
    if(th.width) xmlColumns+= " width='"+th.width+"'";
    if(th.getAttribute('sortable')) xmlColumns+= " sortable='"+th.getAttribute('sortable')+"'";
    xmlColumns+= "/"+">";
   }
  }
 }

 if(xmlColumns != "")
  xml+= "<columns>"+xmlColumns+"</columns>";
 if(xmlButtons != "")
  xml+= "<buttons>"+xmlButtons+"</buttons>";

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(){alert("Configurazione salvata");}
 sh.sendCommand("aboutconfig set-user-settings -app rubrica -xml-settings `"+xml+"`");
}

function sendMail(email)
{
 if(!email)
  return alert("Questo contatto è sprovvisto di email");

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){}
 sh.sendCommand("gframe -f sendmail -params `recp="+email+"`");
}

function makeAppointment(id,ap)
{
 if(!ap)
  var ap = "appointments";
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 
	}
 sh.sendCommand("gframe -f appointment/new -params `ap="+ap+"&subjid="+id+"`");
}

function printContact(id)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	
	}
 sh.sendCommand("gframe -f print.preview -params `ap=rubrica&id="+id+"&parser=contactinfo&modelct=contactinfo`");
}

function showAdvSearch()
{
 document.getElementById('advsearch-container').style.display = "";
}

function hideAdvSearch()
{
 document.getElementById('advsearch-container').style.display = "none";
 Template.unsetVar("filter");
 Template.unsetVar("allcat");
}

function EditCatProperties(canwrite, ownerid, userid)
{
 if(!canwrite)
  return alert("Non disponi dei permessi necessari per poter modificare questa categoria");

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.reload();
	}

 sh.sendCommand("gframe -f dynarc.editcat -params 'ap=rubrica&id="+CAT_ID+"'");
}

function DeleteCategory(canwrite, ownerid, userid)
{
 if(!canwrite)
  return alert("Non disponi dei permessi necessari per poter eliminare questa categoria");

 if(!confirm("Sei sicuro di voler eliminare questa categoria?"))
  return;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 var count = parseFloat(a['count']);
	 var ok = true;
	 if(count > 0)
	 {
	  if(!confirm("Questa categoria non è vuota, contiene "+a['count']+" contatti. Se elimini questa categoria tutti i contatti al suo interno verranno cancellati definitivamente. Sei sicuro di voler continuare?"))
	   return;
	  ok = true;
	 }
	 else
	  ok = true;

	 if(ok)
	 {
	  var sh2 = new GShell();
	  sh2.OnError = function(err){alert(err);}
	  sh2.OnOutput = function(o,a){
		 Template.unsetVar("cat");
		 Template.reload();
		}
	  var cmd = "dynarc delete-cat -ap 'rubrica' -id '"+CAT_ID+"' -r";
	  if(ownerid != userid) sh2.sendSudoCommand(cmd);
	  else sh2.sendCommand(cmd);  
	 }
	}

 var cmd = "dynarc item-count -ap rubrica -into '"+CAT_ID+"'";
 if(ownerid != userid) sh.sendSudoCommand(cmd);
 else sh.sendCommand(cmd);

}
</script>
<?php

$template->End();

?>


