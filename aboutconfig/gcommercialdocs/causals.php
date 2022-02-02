<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 31-05-2016
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: GCommercialDocs - Causals.
 #VERSION: 2.1beta
 #CHANGELOG: 31-05-2016 : Aggiunto argomento continue.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../../";

include($_BASE_PATH."var/templates/glight/index.php");

$template = new GLightTemplate();
$template->includeObject("editsearch");
$template->includeObject("gmutable");
$template->includeCSS("../aboutconfig.css");

$template->Begin("Causali documenti");

$centerContents = "<input type='text' class='search' style='width:400px;float:left' placeholder='Cerca nella configurazione...' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\"/><input type='button' class='button-search' id='searchbtn'/>";

$template->Header("search", $centerContents, "BTN_SAVE|BTN_EXIT", 700);

$template->Pathway();

$template->Body("default");


/*-------------------------------------------------------------------------------------------------------------------*/
?>
<h1>Personalizza causali documenti</h1>
<?php
$ret = GShell("dynarc cat-list -ap gcdcausal");
$_CATEGORIES = $ret['outarr'];
$_TBIDS = array();

for($c=0; $c < count($_CATEGORIES); $c++)
{
 $catInfo = $_CATEGORIES[$c];
 $tbid = strtolower($catInfo['tag'])."-list";
 $_TBIDS[] = $tbid;
 ?>
 <h3><?php echo $catInfo['name']; ?> <img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/button.png" title="Aggiungi" style="cursor:pointer" onclick="AddCausal(<?php echo $c; ?>)"/></h3>
 <div class="gmutable" style="height:300px;margin-top:10px;padding:0px">
  <table id="<?php echo $tbid; ?>" class="gmutable" width="100%" cellspacing="0" cellpadding="0" border="0" catid="<?php echo $catInfo['id']; ?>">
   <tr>
	<th id='name' editable='true'>Causale</th>
	<th width='22'>&nbsp;</th>
   </tr>
   <?php
   	$ret = GShell("dynarc item-list -ap gcdcausal -cat '".$catInfo['id']."'");
	$list = $ret['outarr']['items'];
	for($i=0; $i < count($list); $i++)
	{
	 $itm = $list[$i];
	 echo "<tr id='".$itm['id']."'>";
	 echo "<td><span class='graybold'>".$itm['name']."</span></td>";
	 echo "<td align='center'><img src='".$_ABSOLUTE_URL."share/icons/16x16/delete.gif' style='cursor:pointer' title='Elimina' onclick='DeleteCausal(this.parentNode.parentNode)'/></td>";
	 echo "</tr>";
	}
   ?>
  </table>
 </div>
 <?php
}
?>

<hr/>
<input type='button' class='button-blue' value="Esci dalla configurazione" onclick="saveConfig()"/>
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
	document.location.href = ABSOLUTE_URL+"accounts/Logout.php?continue="+this.getVar('continue');
	return false;
}

Template.OnInit = function(){
 var tblist = tbids.split(',');
 for(var c=0; c < tblist.length; c++)
 {
  var TB = new GMUTable(document.getElementById(tblist[c]), {autoresize:true, autoaddrows:false});
  TB.catId = document.getElementById(tblist[c]).getAttribute('catid');
  TB.OnBeforeAddRow = function(r){
		 r.cells[0].innerHTML = "<span class='graybold'></span>";
		 r.cells[1].innerHTML = "<img src='"+ABSOLUTE_URL+"share/icons/16x16/delete.gif' style='cursor:pointer' title='Elimina' onclick='DeleteCausal(this.parentNode.parentNode)'/"+">";
		}

  TB.OnCellEdit = function(r, cell, value, data){
		 var sh = new GShell();
		 sh.OnError = function(err){alert(err);}
		 sh.OnOutput = function(o,a){r.id=a['id'];}
		 sh.sendCommand("dynarc "+(r.id ? "edit-item -id '"+r.id+"'" : "new-item -group commercialdocs -cat '"+this.catId+"'")+" -ap gcdcausal -name `"+r.cell['name'].getValue()+"`");
		}

  TB.OnRowMove = function(r){
		 var ser = "";
		 for(var c=1; c < this.O.rows.length; c++)
		  ser+= ","+this.O.rows[c].id;
		 var sh = new GShell();
		 sh.OnError = function(err){alert(err);}
		 sh.sendCommand("dynarc item-sort -ap gcdcausal -serialize `"+ser.substr(1)+"`");
		}
  TABLES.push(TB);
 }
}

function AddCausal(tbidx)
{
 var TB = TABLES[tbidx];
 var r = TB.AddRow();
 r.edit();
}

function DeleteCausal(r)
{
 if(!confirm("Sei sicuro di voler eliminare questa causale documento?"))
  return;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){r.remove();}
 sh.sendCommand("dynarc delete-item -ap gcdcausal -id '"+r.id+"' -r");
}

function saveConfig()
{
 Template.Exit();
}

</script>
<?php

$template->End();

?>
