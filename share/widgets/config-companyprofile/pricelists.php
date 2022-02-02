<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-12-2016
 #PACKAGE: companyprofile-config
 #DESCRIPTION: 
 #VERSION: 2.3beta
 #CHANGELOG: 17-12-2016 : Aggiunto campo discount.
			 05-12-2015 : Bug fix su icona delete listini extra.
			 07-11-2012 : Bug fix.
 #TODO: 
 
*/

include_once($_BASE_PATH."var/objects/gextendedtable/index.php");

?>
<!-- LISTINI BASE -->
<div style='height:200px;overflow:auto;'>
<table width='100%' id='pricelisttable' class='gextendedtable' cellspacing='0' cellpadding='0' border='0'>
<tr><th id='column-name'>LISTINI BASE</th>
    <th id='column-markuprate' width='100'>% <?php echo i18n('MARKUP'); ?></th>
    <th id='column-discount' width='100'>% <?php echo i18n('DISCOUNT'); ?></th>
    <th id='column-vat' width='100'>% <?php echo i18n('VAT'); ?></th>
	<th width='32'>&nbsp;</th>
</tr>

<?php
$ret = GShell("pricelists list -where 'isextra=0'",$_REQUEST['sessid'],$_REQUEST['shellid']);
$list = $ret['outarr'];
for($c=0; $c < count($list); $c++)
{
 echo "<tr id='".$list[$c]['id']."'><td>".$list[$c]['name']."</td>";
 echo "<td>".$list[$c]['markuprate']."</td>";
 echo "<td>".$list[$c]['discount']."</td>";
 echo "<td>".$list[$c]['vat']."</td>";
 echo "<td><a href='#' onclick='deleteRow(this.parentNode.parentNode)'><img src='".$_ABSOLUTE_URL."share/widgets/config-companyprofile/img/delete-small.png' border='0'/></a></td></tr>";
}
?>

<tr><td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td></tr>
</table>
</div>

<!-- LISTINI EXTRA -->
<div style='height:200px;overflow:auto;'>
<table width='100%' id='pricelisttable-extra' class='gextendedtable' cellspacing='0' cellpadding='0' border='0'>
<tr><th id='column-name'>LISTINI EXTRA</th>
    <th id='column-markuprate' width='100'>% <?php echo i18n('MARKUP'); ?></th>
    <th id='column-discount' width='100'>% <?php echo i18n('DISCOUNT'); ?></th>
    <th id='column-vat' width='100'>% <?php echo i18n('VAT'); ?></th>
	<th width='32'>&nbsp;</th>
</tr>

<?php
$ret = GShell("pricelists list -where 'isextra=1'",$_REQUEST['sessid'],$_REQUEST['shellid']);
$list = $ret['outarr'];
for($c=0; $c < count($list); $c++)
{
 echo "<tr id='".$list[$c]['id']."'><td>".$list[$c]['name']."</td>";
 echo "<td>".$list[$c]['markuprate']."</td>";
 echo "<td>".$list[$c]['discount']."</td>";
 echo "<td>".$list[$c]['vat']."</td>";
 echo "<td><a href='#' onclick='deleteRow2(this.parentNode.parentNode)'><img src='".$_ABSOLUTE_URL."share/widgets/config-companyprofile/img/delete-small.png' border='0'/></a></td></tr>";
}
?>

<tr><td>&nbsp;</td>
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

var tb = new GExtendedTable(document.getElementById('pricelisttable'));
tb.options.autoAddRow = true;
tb.options.leaveLastRowEmpty = true;

tb.Fields[0].options.editable = true;
tb.Fields[1].options.editable = true;
tb.Fields[2].options.editable = true;
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

var tb2 = new GExtendedTable(document.getElementById('pricelisttable-extra'));
tb2.options.autoAddRow = true;
tb2.options.leaveLastRowEmpty = true;

tb2.Fields[0].options.editable = true;
tb2.Fields[1].options.editable = true;
tb2.Fields[2].options.editable = true;
tb2.Fields[3].options.editable = true;

tb2.OnNewRow = function(r)
{
 r.cells[4].innerHTML = "<a href='#' onclick='deleteRow2(this.parentNode.parentNode)'><img src='<?php echo $_ABSOLUTE_URL; ?>share/widgets/config-companyprofile/img/delete-small.png' border='0'/ ></a>";
}

tb2.OnDeleteRow = function(list)
{
 for(var c=0; c < list.length; c++)
 {
  if(list[c].id)
   REMOVED_ROWS.push(list[c].id);
 }
}

function deleteRow(r){tb.DeleteRow(r.rowIndex,true);}
function deleteRow2(r){tb2.DeleteRow(r.rowIndex,true);}

function formSubmit(close)
{
 var cmd = "";

 if(REMOVED_ROWS.length)
 {
  for(var c=0; c < REMOVED_ROWS.length; c++)
   cmd+= " && pricelists delete -id `"+REMOVED_ROWS[c]+"`";
 }

 for(var c=1; c < tb.O.rows.length; c++)
 {
  var r = tb.O.rows[c];
  if(r.isVoid()) continue;
  if(r.id)
   cmd+= " && pricelists edit -id `"+r.id+"`";
  else
   cmd+= " && pricelists new";
  cmd+= " -name `"+r.cells[0].innerHTML+"` -markuprate `"+parseFloat(r.cells[1].innerHTML)+"` -discount `"+parseFloat(r.cells[2].innerHTML)+"` -vat `"+parseFloat(r.cells[3].innerHTML)+"`";
 }

 for(var c=1; c < tb2.O.rows.length; c++)
 {
  var r = tb2.O.rows[c];
  if(r.isVoid()) continue;
  if(r.id)
   cmd+= " && pricelists edit -id `"+r.id+"`";
  else
   cmd+= " && pricelists new --extra";
  cmd+= " -name `"+r.cells[0].innerHTML+"` -markuprate `"+parseFloat(r.cells[1].innerHTML)+"` -discount `"+parseFloat(r.cells[2].innerHTML)+"` -vat `"+parseFloat(r.cells[3].innerHTML)+"`";
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

