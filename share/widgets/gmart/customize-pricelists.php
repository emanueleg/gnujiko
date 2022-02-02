<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 19-09-2013
 #PACKAGE: gmart
 #DESCRIPTION: Customize pricelists
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Personalizza listini prezzi</title>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/customize-pricelists.css" type="text/css" />
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<?php
include_once($_BASE_PATH."var/templates/standardwidget/index.php");
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gmutable/index.php");

/* Get archive info */
$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : "gmart";
$ret = GShell("dynarc archive-info -prefix '".$_AP."'",$_REQUEST['sessid'],$_REQUEST['shellid']);
$archiveInfo = $ret['outarr'];

if(!$archiveInfo['params']['pricelistcolumns'])
{
 // set default columns //
 $archiveInfo['params']['pricelistcolumns'] = "4,5,7,8,9";
}

?>
</head><body>

<div class='standardwidget' style='width:586px;'>
 <h2>Personalizza le colonne da visualizzare nei listini prezzi</h2>
 <hr/>
 <div class="gmutable" style="width:586px;height:300px;border:0px;">
	<table id="pricelists-table" class="pricelists" width="100%" cellspacing="2" cellpadding="2" border="0">
	 <tr><th width='20'>INC.</th>
		 <th id='column-name' width='100' editable='true'>TITOLO</th>
		 <th style="text-align:left;">DESCRIZIONE</th></tr>

	 <?php
	 $columns = array();
	 $columns[] = array(
		 "title"=>"PR. LISTINO", 
		 "description"=>"Prezzo di listino che vi viene segnalato dal vostro fornitore.");
	 $columns[] = array(
		 "title"=>"% C&M", 
		 "description"=>"Costi e margini.Percentuale di sconto che vi riserva il fornitore.");
	 $columns[] = array(
		 "title"=>"COSTO", 
		 "description"=>"Costo del prodotto. = (Pr. listino - C&M%)");
	 $columns[] = array(
		 "title"=>"COSTO +IVA", 
		 "description"=>"Costo del prodotto IVA inclusa");

	 $columns[] = array(
		 "title"=>"PREZZO BASE", 
		 "description"=>"Prezzo di base o al dettaglio.");

	 $columns[] = array(
		 "title"=>"% RICARICO", 
		 "description"=>"Percentuale di ricarico.");

	 $columns[] = array(
		 "title"=>"% SCONTO", 
		 "description"=>"Percentuale di sconto.");

	 $columns[] = array(
		 "title"=>"PREZZO FINALE", 
		 "description"=>"Prezzo finale.");

	 $columns[] = array(
		 "title"=>"% IVA", 
		 "description"=>"Percentuale IVA.");

	 $columns[] = array(
		 "title"=>"PREZZO + IVA", 
		 "description"=>"Prezzo finale IVA inclusa.");



	 $row = 0;
	 $x = explode(",",$archiveInfo['params']['pricelistcolumns']);
	 for($c=0; $c < count($columns); $c++)
	 {
	  $selected = in_array($c,$x);
	  echo "<tr class='".($selected ? "selected" : "unselected")."'>";
	  echo "<td><input type='checkbox'".($selected ? " checked='true'" : "")."/></td>";
	  echo "<td>".$columns[$c]['title']."</td>";
	  echo "<td>".$columns[$c]['description']."</td>";
	  echo "</tr>";
	  $row = $row ? 0 : 1;
	 }
	 
	 ?>
	</table>
 </div>
 <hr/>
 <input type='button' class='button-blue' value='Applica' onclick='saveAndApply()'/> 
 <input type='button' class='button-gray' value='Annulla' onclick='abort()'/> 
</div>

<script>
var AP = "<?php echo $_AP; ?>";
var TB = null;
function bodyOnLoad()
{
 TB = new GMUTable(document.getElementById('pricelists-table'), {autoresize:false, autoaddrows:false});
 TB.OnSelectRow = function(r){r.className = "selected";}
 TB.OnUnselectRow = function(r){r.className = "unselected";}
}

function saveAndApply()
{
 var pricelistcolumns = "";
 for(var c=1; c < TB.O.rows.length; c++)
 {
  if(TB.O.rows[c].cells[0].getElementsByTagName('INPUT')[0].checked)
   pricelistcolumns+=","+(c-1);
 }

 var sh = new GShell();
 sh.OnError = function(msg){alert(msg);}
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 sh.sendSudoCommand("dynarc edit-archive -ap '"+AP+"' -params `pricelistcolumns="+(pricelistcolumns ? pricelistcolumns.substr(1) : "")+"`");
}

function abort()
{
 gframe_close();
}
</script>

</body></html>
