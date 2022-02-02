<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-06-2014
 #PACKAGE: gmart
 #DESCRIPTION: Bulk edit - vendor
 #VERSION: 2.1beta
 #CHANGELOG: 05-06-2014 : Bug fix mtime
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$selected = explode(",",$_REQUEST['ids']);
$mtime = date('Y-m-d H:i:s');

if($_POST['action'] == "edit")
{
 $_AP = $_POST['ap'];
 $vendorId = $_POST['vendorid'];
 if($vendorId)
 {
  $db = new AlpaDatabase();
  $db->RunQuery("SELECT name FROM dynarc_rubrica_items WHERE id='".$vendorId."'");
  $db->Read();
  $_POST['vendorname'] = $db->record['name'];
  $db->Close();
 }
 $db = new AlpaDatabase();
 $vendorName = $db->Purify($_POST['vendorname']);
 for($c=0; $c < count($selected); $c++)
 {
  $id = $selected[$c];
  $db->RunQuery("DELETE FROM dynarc_".$_AP."_vendorprices WHERE item_id='".$id."'");
  $db->RunQuery("INSERT INTO dynarc_".$_AP."_vendorprices(item_id,vendor_id,vendor_name) VALUES('".$id."','".$vendorId."','".$vendorName."')");
  $db->RunQuery("UPDATE dynarc_".$_AP."_items SET mtime='".$mtime."' WHERE id='".$id."'");
 }
 $db->Close();
}

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>BulkEdit - Vendor</title>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<?php
include_once($_BASE_PATH."var/templates/standardwidget/index.php");
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/editsearch/index.php");
?>
</head><body>

<div class='standardwidget' style='width:360px;'>
 <?php
 if($_POST['action'] == "edit")
 {
  ?>
  <h2>Azioni di gruppo</h2>
  <hr/>
  <h3 style="color:green">Operazione completata!</h4>
  <hr/>
  <input type='button' class='button-gray' value='Chiudi' onclick='submit(1)'/>
  <?php
 }
 else
 {
 ?>
 <form method="POST" id="builkeditform">
 <input type="hidden" name="action" value="edit"/>
 <input type="hidden" name="ap" value="<?php echo $_REQUEST['ap'] ? $_REQUEST['ap'] : 'gmart'; ?>"/>
 <input type="hidden" name="ids" value="<?php echo $_REQUEST['ids']; ?>"/>
 <input type="hidden" name="vendorid" value="0" id="vendorid"/>
 <h2>Azioni di gruppo</h2>
 <hr/>
 <h4>Cambia il fornitore ai <?php echo count($selected); ?> prodotti selezionati</h4>
  <p>
   <input type='text' name="vendorname" id="vendorname" class='edit' style='width:300px' placeholder="Digita il nome o le iniziali del fornitore"/>
  </p>
 <hr/>
  <div style="font-size:12px;font-family:Helvetica, Arial, sans-serif;display:none" id="wait">
    <b>Attendere prego!</b> 
	<img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/loadingbar.gif" style="height:20px"/>
  </div>
  <div id="footer">
   <input type='button' class='button-blue' value='Procedi' onclick='submit()'/> 
   <input type='button' class='button-gray' value='Annulla' onclick='abort()'/>
  </div>
 </form>
 <?php
 }
 ?>
</div>

<script>
function bodyOnLoad()
{
 if(document.getElementById("vendorname"))
 {
  var es = EditSearch.init(document.getElementById("vendorname"), "dynarc search -ap 'rubrica' -ct VENDORS -fields code_str,name `","` --order-by `name ASC` -limit 10","id","name","items",true);
  es.onchange = function(){
	 if(!this.value)
	 {
	  document.getElementById("vendorid").value = 0;
	  return;
	 }
	 if(this.data && this.data['id'])
	  document.getElementById("vendorid").value = this.data['id'];
	}
 }
}

function submit(close)
{
 if(close)
  return gframe_close("done",1);

 document.getElementById("footer").style.display = "none";
 document.getElementById("wait").style.display = "";
 document.getElementById("builkeditform").submit();
}

function abort()
{
 gframe_close();
}

function close()
{
 gframe_close("done",1);
}
</script>

</body></html>
