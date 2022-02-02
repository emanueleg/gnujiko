<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 07-02-2012
 #PACKAGE: companyprofile-config
 #DESCRIPTION: 
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO: 
 
*/

include_once($_BASE_PATH."var/objects/gextendedtable/index.php");

?>
<div style='height:430px;overflow:auto;'>
<table width='100%' id='vattable' class='gextendedtable' cellspacing='0' cellpadding='0' border='0'>
<tr><th id='column-code' width='100'><?php echo i18n('CODE'); ?></th>
    <th id='column-name'><?php echo i18n('DESCRIPTION'); ?></th>
    <th id='column-type' width='200'><?php echo i18n('TYPE'); ?></th>
    <th id='column-perc' width='100'><?php echo i18n('PERCENTAGE'); ?></th>
	<th width='32'>&nbsp;</th>
</tr>

<?php
$ret = GShell("dynarc item-list -ap vatrates -get vat_type,percentage",$_REQUEST['sessid'],$_REQUEST['shellid']);
$list = $ret['outarr']['items'];
$types = array("TAXABLE"=>i18n('Taxable'), "NOT_TAXABLE"=>i18n('Not taxable'), "FREE"=>i18n('Free'), "EXCLUDING"=>i18n('Excluding'), "NOT_SUBJECT"=>i18n('Not subject'), "NOT_DEDUCTIBLE"=>i18n('Not deductible'));

for($c=0; $c < count($list); $c++)
{
 echo "<tr id='".$list[$c]['id']."'><td>".($list[$c]['code_str'] ? $list[$c]['code_str'] : "&nbsp;")."</td>";
 echo "<td>".$list[$c]['name']."</td>";
 echo "<td value='".$list[$c]['vat_type']."'>".$types[$list[$c]['vat_type']]."</td>";
 echo "<td>".$list[$c]['percentage']."</td>";
 echo "<td><a href='#' onclick='deleteRow(this.parentNode.parentNode)'><img src='".$_ABSOLUTE_URL."share/widgets/config-companyprofile/img/delete-small.png' border='0'/></a></td></tr>";
}
?>

<tr><td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td></tr>
</table>
</div>

<div align='right' style='padding-top:5px;'>
<input type='button' value="<?php echo i18n('Abort'); ?>" onclick="gframe_close()"/> <input type='button' value="<?php echo i18n('Apply'); ?>" onclick='formSubmit()'/> <input type='button' value="<?php echo i18n('Save and close'); ?>" onclick="formSubmit(true)"/>
</div>

<script>
var REMOVED_ROWS = new Array();

var tb = new GExtendedTable(document.getElementById('vattable'));
tb.options.autoAddRow = true;
tb.options.leaveLastRowEmpty = true;

tb.Fields[0].options.editable = true;
tb.Fields[1].options.editable = true;
tb.Fields[2].options.editable = true;
tb.Fields[2].options.format.type = "combobox";
tb.Fields[2].options.comboitems.push({'name':"<?php echo i18n('Taxable'); ?>", 'value':'TAXABLE'});
tb.Fields[2].options.comboitems.push({'name':"<?php echo i18n('Not taxable'); ?>", 'value':'NOT_TAXABLE'});
tb.Fields[2].options.comboitems.push({'name':"<?php echo i18n('Free'); ?>", 'value':'FREE'});
tb.Fields[2].options.comboitems.push({'name':"<?php echo i18n('Excluding'); ?>", 'value':'EXCLUDING'});
tb.Fields[2].options.comboitems.push({'name':"<?php echo i18n('Not subject'); ?>", 'value':'NOT_SUBJECT'});
tb.Fields[2].options.comboitems.push({'name':"<?php echo i18n('Not deductible'); ?>", 'value':'NOT_DEDUCTIBLE'});

tb.Fields[3].options.editable = true;

tb.OnNewRow = function(r)
{
 r.cells[4].innerHTML = "<a href='#' onclick='deleteRow(this.parentNode.parentNode)'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png' border='0'/ ></a>";
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
 var ser = "";

 if(REMOVED_ROWS.length)
 {
  cmd+= " && dynarc delete-item -ap vatrates";
  for(var c=0; c < REMOVED_ROWS.length; c++)
   cmd+= " -id `"+REMOVED_ROWS[c]+"`";
 }

 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];
  if(r.isVoid()) continue;
  if(r.id)
  {
   cmd+= " && dynarc edit-item -ap vatrates -id `"+r.id+"`";
   ser+= ","+r.id;
  }
  else
   cmd+= " && dynarc new-item -ap vatrates";
  
  if(r.cells[0].innerHTML != "&nbsp;")
   cmd+= " -code-str `"+r.cells[0].innerHTML+"`";
  
  cmd+= " -name `"+r.cells[1].innerHTML+"` -set `vat_type='"+r.cells[2].getAttribute('value')+"',percentage='"+parseFloat(r.cells[3].innerHTML)+"'`";
 }

 if(cmd == "")
  return;

 if(ser != "")
  cmd+= " && dynarc item-sort -ap vatrates -serialize `"+ser.substr(1)+"`";

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

