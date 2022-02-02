<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-04-2016
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Export body elements to Excel.
 #VERSION: 2.0beta
 #CHANGELOG: 21-04-2016 : Aggiornata funzione export-elements.
 #TODO:
 #DEPENDS: glight-template
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES, $_COMMERCIALDOCS_CONFIG;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

$_ERROR_CODE = "";

include($_BASE_PATH."var/templates/glight/index.php");

$_IDS = array();
$docInfo = null;
$_CAT_TAG = "";
$config = array();
$_SHOW_COLUMNS = array();

if(isset($_REQUEST['ids']) && $_REQUEST['ids']) 	$_IDS = explode(",", $_REQUEST['ids']);
else if(isset($_REQUEST['id']) && $_REQUEST['id']) 	$_IDS[] = $_REQUEST['id'];

if(count($_IDS))
{
 /* Get document info */
 $ret = GShell("dynarc item-info -ap commercialdocs -id '".$_IDS[0]."' -extget cdelements", $_REQUEST['sessid'], $_REQUEST['shellid']);
 if($ret['error']) $_ERROR_CODE = $ret['error'];
 else $docInfo = $ret['outarr'];
}

 $_FIELDS = array(
	"code"=>"Codice",
	"vencode"=>"Cod. art. forn.",
	"mancode"=>"Cod. art. produttore.",
	"sn"=>"S.N.",
	"lot"=>"Lotto",
	"account"=>"Conto",
	"brand"=>"Marca",
	"description"=>"Articolo / Descrizione",
	"metric"=>"Computo metrico",
	"qty"=>"Qta",
	"qty_sent"=>"Qta inv.",
	"qty_downloaded"=>"Qta scaric.",
	"units"=>"U.M.",
	"coltint"=>"Colore/Tinta",
	"sizmis"=>"Taglia/Misura",
	"plbaseprice"=>"Pr. base",
	"plmrate"=>"% ric.",
	"pldiscperc"=>"% sconto",
	"vendorprice"=>"Pr. Acq.",
	"unitprice"=>"Pr. Unit",
	"weight"=>"Peso unit.",
	"discount"=>"Sconto",
	"discount2"=>"Sconto2",
	"discount3"=>"Sconto3",
	"vat"=>"I.V.A.",
	"vatcode"=>"Cod. IVA",
	"vatname"=>"Descr. IVA",
	"price"=>"Totale",
	"profit"=>"Guadagno",
	"margin"=>"% Margine",
	"vatprice"=>"Tot. + IVA",
	"pricelist"=>"Listino",
	"docref"=>"Doc. di rif.",
	"vendorname"=>"Fornitore"
	);

/* DETECT DOC TYPE */
if($docInfo && $docInfo['cat_id'])
{
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT tag,parent_id FROM dynarc_commercialdocs_categories WHERE id='".$docInfo['cat_id']."'");
 if($db->Read())
 {
  if($db->record['parent_id'])
  {
   $_ROOT_CAT_ID = $db->record['parent_id'];
   $db->RunQuery("SELECT tag FROM dynarc_commercialdocs_categories WHERE id='".$db->record['parent_id']."'");
   $db->Read();
   $_CAT_TAG = $db->record['tag']; 
  }
  else
   $_CAT_TAG = $db->record['tag'];
 }
 $db->Close();
}

/* GET CONFIG */
$ret = GShell("aboutconfig get-config -app gcommercialdocs -sec excelbodycols", $_REQUEST['sessid'], $_REQUEST['shellid']);
if(!$ret['error'])
 $config = $ret['outarr']['config'];

if(is_array($config[$_CAT_TAG]) && count($config[$_CAT_TAG]))
{
 for($c=0; $c < count($config[$_CAT_TAG]); $c++)
  $_SHOW_COLUMNS[$config[$_CAT_TAG][$c]['tag']] = true;
}
else
{
 /* GET CONFIG - COLUMN SETTINGS */
 $config = array();
 $_CFG_COLUMNS = array();
 $ret = GShell("aboutconfig get-config -app gcommercialdocs -sec columns", $_REQUEST['sessid'], $_REQUEST['shellid']);
 if(!$ret['error'])
 {
  $config = $ret['outarr']['config'];
  if(is_array($config) && $config[strtolower($_CAT_TAG)])
  {
   $list = $config[strtolower($_CAT_TAG)];
   for($c=0; $c < count($list); $c++)
	$_SHOW_COLUMNS[$list[$c]['tag']] = true;
  }
 }
}

if(!count($_SHOW_COLUMNS))
{
 reset($_FIELDS);
 while(list($k,$v) = each($_FIELDS))
  $_SHOW_COLUMNS[$k] = true;
}


$template = new GLightTemplate("widget");
$template->includeCSS("share/widgets/commercialdocs/exportbodyelements.css");

$template->Begin("Esporta elementi su file Excel");
$template->Header();
//-------------------------------------------------------------------------------------------------------------------//
include_once($_BASE_PATH."etc/commercialdocs/config.php");
//-------------------------------------------------------------------------------------------------------------------//
$template->SubHeaderBegin(0,0,10);
?>
<span class='mediumtext'><?php
if(count($_IDS) > 1)
 echo "Esporta corpo di ".count($_IDS)." documenti su file Excel.";
else if($docInfo)
 echo "Esporta corpo ".$docInfo['name']." su file Excel.";
else 
 echo "ERRORE: nessun documento specificato";
?></span>
<?php
$template->SubHeaderEnd();
//-------------------------------------------------------------------------------------------------------------------//
$template->Body("widget",800);
//-------------------------------------------------------------------------------------------------------------------//
?>
<div style="height:370px;padding:20px">
<table width='100%' id='checkboxtable' cellspacing='8' cellpadding='2' border='0' class='standard-table'>
<?php
 reset($_FIELDS);
 $maxcols = 4;
 $c = 0;
 while(list($k,$v) = each($_FIELDS))
 {
  if($c == 0) echo "<tr>";
  $checked = $_SHOW_COLUMNS[$k] ? true : false;
  echo "<td><input type='checkbox' data-field='".$k."'".($checked ?  " checked='true'" : "")."/>".$v."</td>";
  $c++;
  if($c == $maxcols)
  {
   echo "</tr>";
   $c = 0;
  }
 }
 if(($c > 0) && ($c < $maxcols))
 {
  for($i = $c; $i < $maxcols; $i++)
   echo "<td>&nbsp;</td>";
  echo "</tr>";
 }
?>

<tr><td colspan="<?php echo $maxcols; ?>">&nbsp;</td></tr>
<tr><td colspan="<?php echo $maxcols; ?>"><input type='checkbox' checked='true' id='includenotes'/>Includi righe di nota</td></tr>
<tr><td colspan="<?php echo $maxcols; ?>"><input type='checkbox' checked='true' id='includecomments'/>Includi commenti</td></tr>

</table>
</div>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$footer = "<table width='100%' cellspacing='0' cellpadding='0'>";
//$footer.= "<tr><td><span class='smalltext'>Titolo: </span><input type='text' class='edit' placeholder='Digita un titolo da assegnare al documento precompilato' id='title' style='width:400px'/></td>";
$footer.= "<tr><td valign='middle'><input type='checkbox' id='saveconfig' style='vertical-align:middle'/><span class='smalltext'>Salva configurazione colonne</span></td>";
$footer.= "<td align='right'><input type='button' class='button-blue' value='Esporta' onclick='SubmitAction()'/>";
$footer.= "<input type='button' class='button-gray' value='Annulla' onclick='Abort()' style='margin-left:10px'/></td></tr>";
$footer.= "</table>";
$template->Footer($footer,true);
//-------------------------------------------------------------------------------------------------------------------//
?>
<script>
var ERROR_CODE = "<?php echo $_ERROR_CODE; ?>";
var IDS = "<?php echo implode(',',$_IDS); ?>";
var ID = "<?php echo $docInfo ? $docInfo['id'] : '0'; ?>";
var CONFIG_SAVED = false;
var CAT_TAG = "<?php echo $_CAT_TAG; ?>";

Template.OnInit = function(){
 if(ERROR_CODE && (ERROR_CODE != ""))
 {
  switch(ERROR_CODE)
  {
   case 'INVALID_ITEM' : case 'ITEM_DOES_NOT_EXISTS' : alert("Errore: il documento selezionato risulta inesistente."); break;
   case 'PERMISSION_DENIED' : alert("Permesso negato!, spiacente non hai privilegi necessari per poter leggere questo documento."); break;
  }
  gframe_close(ERROR_CODE);
 }

}

function saveColumnSettings(fields, callback)
{
 if(CAT_TAG == "") return callback();
 var list = fields.split(',');
 var xml = "<"+CAT_TAG+">";
 for(var c=0; c < list.length; c++)
  xml+= "<column tag=\""+list[c]+"\"/"+">"; 
 xml+= "</"+CAT_TAG+">";

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){CONFIG_SAVED=true; callback();}
 sh.sendSudoCommand("aboutconfig set-config -app gcommercialdocs -sec excelbodycols -xml-config `"+xml+"`");
}

function SubmitAction()
{
 var fields = "";
 var tb = document.getElementById('checkboxtable');
 for(var c=0; c < tb.rows.length; c++)
 {
  var r = tb.rows[c];
  for(var i=0; i < r.cells.length; i++)
  {
   var cell = r.cells[i];
   var cblist = cell.getElementsByTagName('INPUT');
   if(!cblist || !cblist.length) continue;
   var cb = cblist[0];
   if(!cb || !cb.checked) continue;
   if((cb.checked == true) && cb.getAttribute('data-field')) fields+= ","+cb.getAttribute('data-field');
  }
 }

 if(fields != "") fields = fields.substr(1);

 var cb = document.getElementById('saveconfig');
 if((cb.checked == true) && !CONFIG_SAVED)
  return saveColumnSettings(fields, function(){SubmitAction();});

 var cmd = "commercialdocs export-elements -ids '"+IDS+"' -fields `"+fields+"`";

 var cb = document.getElementById('includenotes');
 if(cb.checked != true) cmd+= " --exclude-notes";

 var cb = document.getElementById('includecomments');
 if(cb.checked != true) cmd+= " --exclude-comments";

 var sh = new GShell();
 sh.showProcessMessage("Esportazione in Excel", "Attendere prego, &egrave; in corso l&lsquo;esportazione del corpo documento su file Excel.");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(o,a){
	 this.hideProcessMessage();
	 gframe_close(o,a);
	}
 sh.sendCommand(cmd);
}

function Abort(){gframe_close();}
</script>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$template->End();
//-------------------------------------------------------------------------------------------------------------------//

