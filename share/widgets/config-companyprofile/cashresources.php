<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-12-2012
 #PACKAGE: cashresources
 #DESCRIPTION: Official Gnujiko Cash resources manager.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO: 
 
*/

include_once($_BASE_PATH."var/objects/gextendedtable/index.php");

?>
<div style='height:430px;overflow:auto;'>
<table width='100%' id='storetable' class='gextendedtable' cellspacing='0' cellpadding='0' border='0'>
<tr><th id='column-name'>RISORSA</th>
    <th id='column-type' width='200'><?php echo i18n('TYPE'); ?></th>
	<th width='32'>&nbsp;</th>
</tr>

<?php
$ret = GShell("cashresources list",$_REQUEST['sessid'],$_REQUEST['shellid']);
$list = $ret['outarr'];
$types = array("cash"=>i18n('Cash'), "bank"=>i18n('Bank'), "creditcard"=>i18n('Creditcard'));

for($c=0; $c < count($list); $c++)
{
 echo "<tr id='".$list[$c]['id']."'><td>".$list[$c]['name']."</td>";
 echo "<td value='".$list[$c]['type']."'>".$types[$list[$c]['type']]."</td>";
 echo "<td><a href='#' onclick='deleteRow(this.parentNode.parentNode)'><img src='".$_ABSOLUTE_URL."share/widgets/config-companyprofile/img/delete-small.png' border='0'/></a></td></tr>";
}
?>

<tr><td>&nbsp;</td>
	<td>&nbsp;</td>
    <td>&nbsp;</td></tr>
</table>
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
tb.Fields[1].options.format.type = "combobox";
tb.Fields[1].options.comboitems.push({'name':"<?php echo i18n('Cash'); ?>", 'value':'cash'});
tb.Fields[1].options.comboitems.push({'name':"<?php echo i18n('Bank'); ?>", 'value':'bank'});
tb.Fields[1].options.comboitems.push({'name':"<?php echo i18n('Creditcard'); ?>", 'value':'creditcard'});


tb.OnNewRow = function(r)
{
 r.cells[2].innerHTML = "<a href='#' onclick='deleteRow(this.parentNode.parentNode)'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png' border='0'/ ></a>";
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
   cmd+= " && cashresources delete -id `"+REMOVED_ROWS[c]+"`";
 }

 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];
  if(r.isVoid()) continue;
  if(r.id)
  {
   cmd+= " && cashresources edit -id `"+r.id+"`";
  }
  else
   cmd+= " && cashresources new";
  
  cmd+= " -name `"+r.cells[0].innerHTML+"` -type `"+r.cells[1].getAttribute('value')+"`";
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

