<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-04-2017
 #PACKAGE: gstore
 #DESCRIPTION: Store - Column settings.
 #VERSION: 2.3beta
 #CHANGELOG: 30-04-2017 : Bugfix Exit.
			 06-08-2016 : Aggiunti varianti nella lista delle colonne.
			 04-04-2016 : Possibilita di ordinare le colonne.
 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_EXTRA_COLUMNS;

$_BASE_PATH = "../../";

include($_BASE_PATH."var/templates/glight/index.php");
include_once($_BASE_PATH."include/userfunc.php");

if(file_exists($_BASE_PATH."Store2/config-custom.php"))
 include_once($_BASE_PATH."Store2/config-custom.php");

$template = new GLightTemplate();
$template->includeObject("editsearch");
$template->includeObject("gmutable");
$template->includeCSS("../aboutconfig.css");

$template->Begin("Colonne personalizzabili");

$centerContents = "<input type='text' class='search' style='width:400px;float:left' placeholder='Cerca nella configurazione...' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\"/><input type='button' class='button-search' id='searchbtn'/>";

$template->Header("search", $centerContents, "BTN_SAVE|BTN_EXIT", 700);

$template->Pathway();

$template->Body("default");

$_DEF_DOC_COLUMNS = array(
	"code_str" => array('title'=>'Codice', 'width'=>70),
	"name" => array('title'=>'Descrizione articolo'),
	"minimum_stock" => array('title'=>'Scorta min.', 'width'=>80),
	"storeqty" => array('title'=>'Giac. fisica', 'width'=>80),
	"variants" => array('title'=>'Varianti', 'width'=>80),
	"booked" => array('title'=>'Prenotati', 'width'=>70),
	"incoming" => array('title'=>'Ordinati', 'width'=>70),
	"available" => array('title'=>'Disponibili', 'width'=>70),
	"enh_amount" => array('title'=>'Valorizz.', 'width'=>70)
);

/* GET CONFIG */
$ret = GShell("aboutconfig get-config -app gstore -sec columns");
if(!$ret['error'])
 $config = $ret['outarr']['config'];
else
 $configErr = $ret['message'];

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

/*-------------------------------------------------------------------------------------------------------------------*/
?>
<h1>Personalizza colonne</h1>
<?php
reset($archiveTypes);
$idx = 0;
$db = new AlpaDatabase();
while(list($ap,$archiveTitle) = each($archiveTypes))
{
 $tbid = "tb-".$ap;
 $_TBIDS[] = $tbid;
 $_COLUMNS = $_DEF_DOC_COLUMNS;

 if(is_array($_EXTRA_COLUMNS) && count($_EXTRA_COLUMNS))
 {
  for($j=0; $j < count($_EXTRA_COLUMNS); $j++)
  {
   $extraColConfig = $_EXTRA_COLUMNS[$j];
   $extension = $extraColConfig['extension'];
   if($extraColConfig['extension'])
   {
    $db->RunQuery("SELECT ext.id FROM dynarc_archives AS arc INNER JOIN dynarc_archive_extensions AS ext ON ext.archive_id=arc.id AND ext.extension_name='"
	 .$extraColConfig['extension']."' WHERE arc.tb_prefix='".$ap."'");
    if(!$db->Read())
	 continue;
   }
   $list = $extraColConfig['columns'] ? $extraColConfig['columns'] : $extraColConfig;

   reset($list);
   while(list($k,$v) = each($list))
   {
    $_COLUMNS[$k] = $v;
   }
  }
 }

 ?>
 <h3>Disposizione colonne in Situazione <?php echo $archiveTitle; ?></h3>
 <small>Seleziona le colonne che desideri mostrare. Puoi anche rinominarle, modificare le dimensioni e l&lsquo;ordinamento semplicemente trascinandole su e giu.</small>
 <div class="gmutable" style="height:300px;margin-top:10px;padding:0px">
  <table id="<?php echo $tbid; ?>" class="gmutable" width="100%" cellspacing="0" cellpadding="0" border="0">
   <tr>
	<th width='32'><input type='checkbox' onchange="TABLES[<?php echo $idx; ?>].selectAll(this.checked)"/></th>
	<th id='tag' width='100'>TAG</th>
	<th id='title' editable='true'>TITOLO</th>
	<th id='width' width='80' editable='true' format='number'>DIMENSIONE</th>
   </tr>
   <?php
   $_USER_COLUMNS = array();
   if(is_array($config[$ap]))
   {
	for($i=0; $i < count($config[$ap]); $i++)
	{
	 $col = $config[$ap][$i];
	 echo "<tr class='selected'><td><input type='checkbox' checked='true'/></td><td>".$col['tag']."</td><td><span class='graybold'>".$col['title']."</span></td><td><span class='graybold'>".$col['width']."</span></td></tr>";
	 $_USER_COLUMNS[$col['tag']] = true;
	}
   }
   reset($_COLUMNS);
   while(list($k,$v)=each($_COLUMNS))
   {
	if($_USER_COLUMNS[$k]) continue;
	echo "<tr><td><input type='checkbox'/></td><td>".$k."</td><td><span class='graybold'>".$v['title']."</span></td><td><span class='graybold'>"
		.$v['width']."</span></td></tr>";
   }
   ?>
  </table>
 </div>
 <?php
 $idx++;
}
$db->Close();
?>

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
var TABLES = new Array();
var tbids = "<?php echo implode(',',$_TBIDS); ?>";

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
 var tblist = tbids.split(',');
 for(var c=0; c < tblist.length; c++)
 {
  var TB = new GMUTable(document.getElementById(tblist[c]), {autoresize:true, autoaddrows:false, orderable:true});
  TB.tag = tblist[c].substr(3);
  TABLES.push(TB);
 }
}

function saveConfig(callback)
{
 var xml = "";
 for(var c=0; c < TABLES.length; c++)
 {
  var TB = TABLES[c];
  xml+= "<"+TB.tag+">";
  var sel = TB.GetSelectedRows();
  for(var i=0; i < sel.length; i++)
   xml+= "<column tag=\""+sel[i].cell['tag'].getValue()+"\" title=\""+sel[i].cell['title'].getValue()+"\" width=\""+sel[i].cell['width'].getValue()+"\"/"+">";
  xml+= "</"+TB.tag+">";
 }

 saveFinish(xml,callback);
}

function saveFinish(xml,callback)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(){
	 if(callback) return callback();
	 alert("Salvataggio completato!");
	}
 sh.sendCommand("aboutconfig set-config -app gstore -sec columns -xml-config `"+xml+"`");
}

</script>
<?php

$template->End();

?>
