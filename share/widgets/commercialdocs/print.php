<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
HackTVT Project
copyright(C) 2017 Alpatech mediaware - www.alpatech.it
license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
Gnujiko 10.1 is free software released under GNU/GPL license
developed by D. L. Alessandro (alessandro@alpatech.it)

#DATE: 24-02-2017
#PACKAGE: gcommercialdocs
#DESCRIPTION: Print multiple documents.
#VERSION: 2.1beta
#CHANGELOG: 24-02-2017 : Aggiunta opzione allinone.
#TODO: 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = '../../../';

include($_BASE_PATH.'var/templates/glight/index.php');
include_once($_BASE_PATH."include/userfunc.php");

$template = new GLightTemplate('widget');

$_IDS = array();
$_DOCUMENTS = array();
$_PRINTMODEL_LIST = array();
$_MODEL_AP = "printmodels";
$_MODEL_CAT = null;
$_MODEL_ID = 0;	$_MODEL_ALIAS = "";
$_MODEL_FORMAT = "A4";
$_MODEL_ORIENTATION = "P";
$_PARSER = (isset($_REQUEST['parser']) && $_REQUEST['parser']) ? $_REQUEST['parser'] : "";
$_OUTPUT_FILENAME = "Documents";
$_ALLINONE = (isset($_REQUEST['allinone']) && $_REQUEST['allinone']) ? true : false;

if((isset($_REQUEST['modelct']) && $_REQUEST['modelct']) || (isset($_REQUEST['modelcat']) && $_REQUEST['modelcat']))
{
 $ret = GShell("dynarc cat-info -ap `".$_MODEL_AP."`".($_REQUEST['modelcat'] ? " -id `".$_REQUEST['modelcat']."`" : " -tag `".$_REQUEST['modelct']."`"), $_REQUEST['sessid'], $_REQUEST['shellid']);
 $_MODEL_CAT = $ret['outarr'];
}
if(isset($_REQUEST['modelid']) && $_REQUEST['modelid'])			 	$_MODEL_ID = $_REQUEST['modelid'];
else if(isset($_REQUEST['modelalias']) && $_REQUEST['modelalias'])	$_MODEL_ALIAS = $_REQUEST['modelalias'];

// GET PRINTMODEL LIST
$ret = GShell("dynarc item-list -ap `".$_MODEL_AP."`"
	.($_MODEL_CAT ? " -cat `".$_MODEL_CAT['id']."`" : "")." -extget printmodelinfo",$_REQUEST['sessid'],$_REQUEST['shellid']);
if(!$ret['error'])	$_PRINTMODEL_LIST = $ret['outarr']['items'];

// GET PRINTMODEL INFO
if($_MODEL_ID || $_MODEL_ALIAS)
{
 $ret = GShell("dynarc item-info -ap `".$_MODEL_AP."`"
	.($_MODEL_ALIAS ? " -alias `".$_MODEL_ALIAS."`" : " -id '".$_MODEL_ID."'")
	." -extget printmodelinfo",$_REQUEST['sessid'],$_REQUEST['shellid']);
 if(!$ret['error'])
 {
  $modelInfo = $ret['outarr'];
  $_MODEL_ID = $modelInfo['id'];
  if($modelInfo['format'])			$_MODEL_FORMAT = $modelInfo['format'];
  if($modelInfo['orientation'])		$_MODEL_ORIENTATION = $modelInfo['orientation'];
 }
}
else if(count($_PRINTMODEL_LIST))
{
 $modelInfo = $_PRINTMODEL_LIST[0];
 $_MODEL_ID = $modelInfo['id'];
 if($modelInfo['format'])			$_MODEL_FORMAT = $modelInfo['format'];
 if($modelInfo['orientation'])		$_MODEL_ORIENTATION = $modelInfo['orientation'];
}


if(isset($_REQUEST['ids']) && $_REQUEST['ids'])
 $_IDS = explode(",",$_REQUEST['ids']);

if(count($_IDS))
{
 $mod = new GMOD();
 $db = new AlpaDatabase();
 for($c=0; $c < count($_IDS); $c++)
 {
  $db->RunQuery("SELECT uid,gid,_mod,name,cat_id FROM dynarc_commercialdocs_items WHERE id='".$_IDS[$c]."'");
  if($db->Read())
  {
   $mod->set($db->record['_mod'], $db->record['uid'], $db->record['gid']);
   if($mod->canRead())
    $_DOCUMENTS[] = array('id'=>$_IDS[$c], 'name'=>$db->record['name'], 'cat_id'=>$db->record['cat_id']);
  }
 }
 $db->Close();

 if(count($_DOCUMENTS))
 {
  // Detect document type from first document, to use for detect filename
  $ret = GShell("dynarc get-root-cat -ap commercialdocs -id '".$_DOCUMENTS[0]['cat_id']."'", $_REQUEST['sessid'], $_REQUEST['shellid']);
  if(!$ret['error'])
   $_OUTPUT_FILENAME = $ret['outarr']['name'];
 }

}

$template->Begin('Stampa documenti selezionati');
?>
<div class="glight-widget-header bg-blue"><h3>Stampa documenti selezionati</h3></div>
<?php
$template->Body('widget',640);

//-------------------------------------------------------------------------------------------------------------------//
?>
<style type='text/css'>
table#doclist th {
 background: #666666;
}

table#doclist td {
 border-bottom: 1px solid #dadada;
}
</style>

<div class='glight-widget-body' style='width:624px;height:360px'>
 <table width='100%' cellspacing='0' cellpadding='0' border='0'>
  <tr><td valign='top'>
	  <!-- DOCUMENT LIST TABLE -->
		<div style="width:460px;height:350px;overflow:auto;border-right:1px solid #dadada">
		 <table width='100%' cellspacing='0' cellpadding='0' border='0' class='glight-standard-table' id='doclist'>
		  <thead>
		   <tr>
			<th width='32' align='center'><input type='checkbox' checked='true' onchange='selectAllDocs(this)'/></th>
			<th align='left'><span class='smalltext'>DOCUMENTO</span></th>
			<th width='32'>&nbsp;</th>
		   </tr>
		  </thead>
		  <tbody>
		   <?php
			for($c=0; $c < count($_DOCUMENTS); $c++)
			{
			 echo "<tr id='".$_DOCUMENTS[$c]['id']."'><td align='center'><input type='checkbox' checked='true'/></td>";
			 echo "<td><span class='smalltext'>".$_DOCUMENTS[$c]['name']."</span></td>";
			 echo "<td align='center'>&nbsp;</td></tr>";
			}
		   ?>
		  </tbody>
		 </table>
		</div>
	  <!-- EOF - DOCUMENT LIST TABLE -->
	  </td><td width='140' valign='top'>
	   <span class="mediumtext blue" style='font-size:12px'>MODELLI DISPONIBILI</span>
	   <div style="height:340px;overflow:auto;margin-top:10px">
		<?php
		 for($c=0; $c < count($_PRINTMODEL_LIST); $c++)
		 {
		  $modelInfo = $_PRINTMODEL_LIST[$c];
		  $selected = ($_MODEL_ID==$modelInfo['id']) ? true : false;
		  if($modelInfo['thumbdata'])
		  {
		   if(strpos($modelInfo['thumbdata'], "data:") !== false)	$src = $modelInfo['thumbdata'];
		   else	$src = $_ABSOLUTE_URL.$modelInfo['thumbdata'];
		  }
		  else $src = $_ABSOLUTE_URL."share/widgets/printpreview/img/image_not_available.png";

		  echo "<div>";
		  echo "<img src='".$src."' style='width:96px;border:1px solid #dadada'/><br/>";
		  echo "<div style='height:20px;margin-bottom:20px'>";
		  echo "<input type='radio' name='model' style='float:left'".($selected ? " checked='true'" : "")." data-id='".$modelInfo['id']."' data-format='"
			.$modelInfo['format']."' data-orientation='".$modelInfo['orientation']."'/><span class='smalltext' style='line-height:20px'>"
			.$modelInfo['name']."</span>";
		  echo "</div>";
		  echo "</div>";

		 }
		?>
	   </div>
	  </td>
  </tr>
 </table>
</div>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$footer = "<input type='button' class='button-blue' value='Stampa' style='float:left' onclick='SubmitAction()'/>";
$footer.= "<input type='button' class='button-gray' value='Annulla' style='float:left;margin-left:10px' onclick='abort()'/>";
$template->Footer($footer,true);
//-------------------------------------------------------------------------------------------------------------------//

$ka = array('"',	"'",	"`",	"~",	"^",	"#",	"/",	"\\",	"°",	"&lsquo;",	"&rsquo;",	"&quot;",	"&amp;");
$kb = array("",		"",		"",		"_",	"",		"",		"",		"",		"_",	"",			"",			"",			"");
$_OUTPUT_FILENAME = str_replace($ka,$kb,$_OUTPUT_FILENAME);

?>
<script>
var OUTPUT_FILENAME = "<?php echo $_OUTPUT_FILENAME; ?>";
var ALLINONE = <?php echo $_ALLINONE ? 'true' : 'false'; ?>;

function abort(){Template.Exit();}
function selectAllDocs(cb)
{
 var tb = document.getElementById('doclist');
 for(var c=1; c < tb.rows.length; c++)
  tb.rows[c].cells[0].getElementsByTagName('INPUT')[0].checked = cb.checked;
}

Template.OnInit = function()
{

}

function SubmitAction()
{
 // GET SELECTED DOCUMENTS
 var sel = new Array();
 var tb = document.getElementById('doclist');
 var IDS = "";
 for(var c=1; c < tb.rows.length; c++)
 {
  var r = tb.rows[c];
  var cb = r.cells[0].getElementsByTagName('INPUT')[0];
  if(cb.checked == true)
   IDS+= ","+r.id;
 }
 if(!IDS || (IDS == "")) return alert("Nessun documento selezionato");
 IDS = IDS.substr(1);

 // GET SELECTED MODEL ID
 var MODEL_ID = 0;
 var list = document.getElementsByName('model');
 for(var c=0; c < list.length; c++)
 {
  if(list[c].checked == true)
  {
   MODEL_ID = list[c].getAttribute('data-id');
   break;
  }
 }

 // RUN COMMAND
 var IDX = 0;
 var lastRow = null;
 var sh = new GShell();
 sh.showProcessMessage("Generazione delle stampe PDF in corso", "Attendere prego, &egrave; in corso la generazione delle stampe in PDF");
 sh.processMessage.titleO.parentNode.style.width = "360px";
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnPreOutput = function(o,a,type,ref,mode){
	 if(a && a['id'])
	 {
	  if(lastRow) lastRow.cells[2].innerHTML = "<img src='img/status-completed.png'/"+">";
	  this.processMessage.subtitleO.innerHTML = "Documento "+o;
	  var r = document.getElementById(a['id']);
	  if(!r) return;
	  r.cells[2].innerHTML = "<img src='img/status-working.gif'/"+">";
	  lastRow = r;
	 }
	}

 sh.OnOutput = function(o,a){
	 if(lastRow) lastRow.cells[2].innerHTML = "<img src='img/status-completed.png'/"+">";
	 this.hideProcessMessage();
	 gframe_close(o,a);
	}

 if(ALLINONE)
  sh.sendCommand("commercialdocs print -ids '"+IDS+"' -modelid '"+MODEL_ID+"' -parser 'commercialdocs' -f 'tmp/"+OUTPUT_FILENAME+"' --all-in-one");
 else
  sh.sendCommand("commercialdocs print -ids '"+IDS+"' -modelid '"+MODEL_ID+"' -parser 'commercialdocs' -f 'tmp/"+OUTPUT_FILENAME+".zip'");

}

</script>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$template->End();
//-------------------------------------------------------------------------------------------------------------------//
?>
