<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-02-2014
 #PACKAGE: dynarc-label-extension
 #DESCRIPTION: Config labels
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

$_AP = $_REQUEST['ap'];

include($_BASE_PATH."var/templates/glight/index.php");

$template = new GLightTemplate("widget",null);
$template->includeObject("gmutable");

$template->Begin("Configurazione etichette");
$template->Header();
//-------------------------------------------------------------------------------------------------------------------//
$template->SubHeaderBegin(0,0,10);
?>
<input type='button' class="button-blue menuwhite" value="Menu" connect='mainmenu' id='menubutton' style='float:left'/>
 <ul class='popupmenu' id='mainmenu'>
  <li onclick='DeleteSelected()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/>Elimina etichette selezionate</li>
 </ul>

<input type='text' class='edit' style='width:390px;float:left;margin-left:30px' placeholder="Digita un titolo e premi invio per inserire una nuova etichetta" id='labeltitle'/>
<input type='button' class='button-add' id='btnadd'/>
<?php
$template->SubHeaderEnd();
//-------------------------------------------------------------------------------------------------------------------//
$template->Body("widget",600);
//-------------------------------------------------------------------------------------------------------------------//
?>
<style type='text/css'>
div.color-block {
 width: 20px;
 height: 20px;
 cursor: pointer;
 border: 1px solid #d8d8d8;
 background-color: #ffffff;
 color: #000000;
}
</style>

<div class='gmutable' style="height:340px;width:560px;margin:10px;border:0px">
<table id="itemlist" class="gmutable" cellspacing='0' cellpadding='0' border='0'>
<tr><th width='32' style='text-align:center'><input type='checkbox' onclick='tb.selectAll(this.checked)'/></th>
    <th id='name' editable='true'>ETICHETTA</th>
    <th width='80'>COLORE</th>
	<th width='18'>&nbsp;</th>
</tr>
<?php
$ret = GShell("dynarc exec-func ext:labels.list -params `archiveprefix=".$_AP."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
if(!$ret['error'])
{
 $_LABELS = $ret['outarr'];
 for($c=0; $c < count($_LABELS); $c++)
 {
  $label = $_LABELS[$c];
  echo "<tr id='".$label['id']."'><td><input type='checkbox'/></td>";
  echo "<td><span class='graybold'>".$label['name']."</span></td>";
  echo "<td><div class='color-block' bgcolor='".$label['bgcolor']."' txtcolor='".$label['color']."' style='background-color:"
	.$label['bgcolor'].";color:".$label['color'].";' onclick='EditColor(this)'/></td>";
  echo "<td>&nbsp;</td></tr>";
 }
}
?>
</table>
</div>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$footer = "<input type='button' class='button-blue' value='Salva la configurazione' onclick='Submit()'/>";
$template->Footer($footer,true);
//-------------------------------------------------------------------------------------------------------------------//
?>
<script>
var AP = "<?php echo $_AP; ?>";
var tb = null;
var NEW_LABELS = new Array();
var UPDATED_LABELS = new Array();
var REMOVED_LABELS = new Array();


Template.OnInit = function(){
 	this.initBtn(document.getElementById('menubutton'), "popupmenu");

	/* GMUTABLE */
	tb = new GMUTable(document.getElementById('itemlist'), {autoresize:true, autoaddrows:false});
	tb.OnCellEdit = function(r,cell,value){
	 if(r.id && (UPDATED_LABELS.indexOf(r) < 0))
	  UPDATED_LABELS.push(r);
	}
	tb.OnBeforeAddRow = function(r){
		 r.cells[0].innerHTML = "<input type='checkbox'/ >"; r.cells[0].style.textAlign='center';
		 r.cells[1].innerHTML = "<span class='graybold'></span>";
		 r.cells[2].innerHTML = "<div class='color-block' bgcolor='#ffffff' txtcolor='#000000' onclick='EditColor(this)'/"+">";
		 NEW_LABELS.push(r);
		}

	tb.OnDeleteRow = function(r){
		 if(NEW_LABELS.indexOf(r) > -1)
		  NEW_LABELS.splice(NEW_LABELS.indexOf(r),1);
		 if(UPDATED_LABELS.indexOf(r) > -1)
		  UPDATED_LABELS.splice(UPDATED_LABELS.indexOf(r),1);
		 if(r.id)
		  REMOVED_LABELS.push(r);
		}

	document.getElementById("labeltitle").onchange = function(){
	 if(!this.value) return;
	 var r = tb.AddRow();
	 r.cell['name'].setValue(this.value);
	 this.value = "";
	}

}

function DeleteSelected()
{
 var list = tb.GetSelectedRows();
 if(!list.length)
  return alert("Non è stata selezionata nessuna etichetta.");
 if(!confirm("Sei sicuro di voler rimuovere le etichette selezionate?"))
  return;
 tb.DeleteSelectedRows();
}

function EditColor(div)
{
 var r = div.parentNode.parentNode;
 var bgColor = div.getAttribute('bgcolor').replace("#","");
 var color = div.getAttribute('txtcolor').replace("#","");

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 div.setAttribute('bgcolor',a[0]);
	 div.setAttribute('txtcolor',a[1]);
	 div.style.backgroundColor = a[0];
	 div.style.color = a[1];
	 if(r.id && (UPDATED_LABELS.indexOf(r) < 0))
	  UPDATED_LABELS.push(r);
	}
 sh.sendCommand("gframe -f color-picker -params `pickers=Sfondo;Testo&colors="+bgColor+";"+color+"`");
}

function Submit()
{
 var cmd = "";
 for(var c=0; c < NEW_LABELS.length; c++)
 {
  var r = NEW_LABELS[c];
  var title = r.cell['name'].getValue().replace("'","");
  title = title.replace('"','');
  title = encodeURI(title.E_QUOT());
  var block = r.cells[2].getElementsByTagName('DIV')[0];
  var bgcolor = block.getAttribute('bgcolor').replace("#","");
  var color = block.getAttribute('txtcolor').replace("#","");
  cmd+= " && dynarc exec-func ext:labels.new -params `ap="+AP+"&name="+title+"&bgcolor="+bgcolor+"&color="+color+"`";
 }

 for(var c=0; c < UPDATED_LABELS.length; c++)
 {
  var r = UPDATED_LABELS[c];
  var title = r.cell['name'].getValue().replace("'","");
  title = title.replace('"','');
  title = encodeURI(title.E_QUOT());
  var block = r.cells[2].getElementsByTagName('DIV')[0];
  var bgcolor = block.getAttribute('bgcolor').replace("#","");
  var color = block.getAttribute('txtcolor').replace("#","");
  cmd+= " && dynarc exec-func ext:labels.edit -params `ap="+AP+"&id="+r.id+"&name="+title+"&bgcolor="+bgcolor+"&color="+color+"`";
 }

 for(var c=0; c < REMOVED_LABELS.length; c++)
 {
  var r = REMOVED_LABELS[c];
  cmd+= " && dynarc exec-func ext:labels.delete -params `ap="+AP+"&id="+r.id+"`";
 }

 if(!cmd)
  return gframe_close();

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 sh.sendCommand(cmd.substr(4));
}
</script>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$template->End();
//-------------------------------------------------------------------------------------------------------------------//

