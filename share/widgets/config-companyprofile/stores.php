<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 29-11-2012
 #PACKAGE: gstore
 #DESCRIPTION: 
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO: 
 
*/

include_once($_BASE_PATH."var/objects/gextendedtable/index.php");

?>
<div style='height:400px;overflow:auto;'>
<table width='100%' id='storetable' class='gextendedtable' cellspacing='0' cellpadding='0' border='0'>
<tr><th id='column-name'>MAGAZZINO</th>
	<th id='column-ext' width='64'>EXT</th>
	<th id='column-access' width='80'>ACCESSO</th>
	<th width='32'>&nbsp;</th>
</tr>

<?php
$groups = array(0=>"Tutti");

$ret = GShell("groups",$_REQUEST['sessid'],$_REQUEST['shellid']);
if(!$ret['error'])
{
 for($c=0; $c < count($ret['outarr']); $c++)
  $groups[$ret['outarr'][$c]['id']] = $ret['outarr'][$c]['name'];
}

$ret = GShell("store list",$_REQUEST['sessid'],$_REQUEST['shellid']);
$list = $ret['outarr'];
for($c=0; $c < count($list); $c++)
{
 echo "<tr id='".$list[$c]['id']."'><td>".$list[$c]['name']."</td>";
 echo "<td>".$list[$c]['doc_ext']."</td>";
 echo "<td value='".$list[$c]['gid']."'>".$groups[$list[$c]['gid']]."</td>";
 echo "<td><a href='#' onclick='deleteRow(this.parentNode.parentNode)'><img src='".$_ABSOLUTE_URL."share/widgets/config-companyprofile/img/delete-small.png' border='0'/></a></td></tr>";
}
?>

<tr><td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
	<td>&nbsp;</td></tr>
</table>
</div>

<div>
NOTA: Per ogni magazzino &egrave; possibile assegnare una diversa numerazione ai DDT che verranno emessi per conto di quel magazzino, inoltre &egrave; possibile assegnare un gruppo di utenti in modo che l&lsquo;utente (o gli utenti) appartenente a quel gruppo abbiano l&lsquo;accesso a visionare,caricare e scaricare la merce da quel magazzino.<br/><br/>
<b>EXT</b> : Indica l&lsquo;estensione da utilizzare nei DDT. (Es: A, B, C, ...)<br/>
<b>ACCESSO</b> : Puoi scegliere il gruppo di utenti che avranno accesso a quel magazzino. Ricorda per&ograve;,tu che sei l&lsquo;amministratore devi appartenere a quel dato gruppo, altrimenti una volta che avrai salvato queste impostazioni quel magazzino scompar&agrave; dalla tua vista perch&egrave; non avrai pi&ugrave; il permesso per poterlo vedere. PS: l&lsquo;utente root ne &egrave; esente, non serve che appartenga a nessun gruppo, lui pu&ograve; vedere e fare tutto.
</div>

<div align='right' style='padding-top:5px;'>
<input type='button' value="<?php echo i18n('Abort'); ?>" onclick="gframe_close()"/> <input type='button' value="<?php echo i18n('Apply'); ?>" onclick='formSubmit()'/> <input type='button' value="<?php echo i18n('Save and close'); ?>" onclick="formSubmit(true)"/>
</div>

<script>
var REMOVED_ROWS = new Array();

var tb = new GExtendedTable(document.getElementById('storetable'));
tb.options.autoAddRow = true;
tb.options.leaveLastRowEmpty = true;

tb.Fields[0].options.editable = true;
tb.Fields[1].options.editable = true;
tb.Fields[2].options.editable = true;
tb.Fields[2].options.format.type = "combobox";
<?php
reset($groups);
while(list($k,$v) = each($groups))
{
 echo "tb.Fields[2].options.comboitems.push({'name':\"".$v."\", 'value':\"".$k."\"});\n";
}
?>

tb.OnNewRow = function(r)
{
 r.cells[3].innerHTML = "<a href='#' onclick='deleteRow(this.parentNode.parentNode)'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png' border='0'/ ></a>";
}

tb.OnDeleteRow = function(list)
{
 for(var c=0; c < list.length; c++)
 {
  if(list[c].id)
   REMOVED_ROWS.push(list[c].id);
 }
}

function deleteRow(r)
{
 tb.DeleteRow(r.rowIndex,true);
}

function formSubmit(close)
{
 var cmd = "";

 if(REMOVED_ROWS.length)
 {
  for(var c=0; c < REMOVED_ROWS.length; c++)
   cmd+= " && store delete -id `"+REMOVED_ROWS[c]+"`";
 }

 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];
  if(r.isVoid()) continue;
  if(r.id)
  {
   cmd+= " && store edit -id `"+r.id+"`";
  }
  else
   cmd+= " && store new";
  
  cmd+= " -name `"+r.cells[0].innerHTML+"` -docext `"+r.cells[1].innerHTML.replace("&nbsp;","")+"` -gid `"+r.cells[2].getAttribute('value')+"`";
 }

 if(cmd == "")
  return;

 var sh = new GShell();
 sh.OnFinish = function(){
	 if(!close)
	 {
	  alert("<?php echo i18n('Saved!'); ?>");
	  document.location.reload();
	 }
	 else
	  gframe_close();
	}
 sh.sendCommand(cmd.substr(4));
}
</script>
<?php

