<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-12-2014
 #PACKAGE: gmart
 #DESCRIPTION: Bulk edit - Brand
 #VERSION: 2.3beta
 #CHANGELOG: 18-12-2014 : Bug fix.
			 05-06-2014 : Bug fix mtime
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
 $db = new AlpaDatabase();
 $brand = $db->Purify($_POST['brand']);
 $brandId = $_POST['brandid'];
 $_AP = $_POST['ap'];
 for($c=0; $c < count($selected); $c++)
 {
  $id = $selected[$c];
  $db->RunQuery("SELECT model FROM dynarc_".$_AP."_items WHERE id='".$id."'");
  $db->Read();
  $model = $db->record['model'];
  $db->RunQuery("UPDATE dynarc_".$_AP."_items SET mtime='".$mtime."',name='".$brand.($model ? " ".$model : "")."',brand='".$brand."',brand_id='"
	.$brandId."' WHERE id='".$id."'");
 }
 $db->Close();
}

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>BulkEdit - Brand</title>
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
 <input type='hidden' name='brandid' value='0' id='brandid'/>
 <h2>Azioni di gruppo</h2>
 <hr/>
 <p>
  <h4>Digita la marca da assegnare ai <?php echo count($selected); ?> prodotti selezionati</h4>
  <input type='text' name="brand" id='brand' class='edit' style='width:300px' placeholder="digita una marca"/>
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
 if(document.getElementById("brand"))
  EditSearch.init(document.getElementById("brand"), "dynarc search -ap 'brands' -fields name `","` --order-by `name ASC` -limit 5","id","name","items",true);
}

function submit(close)
{
 if(close)
  return gframe_close("done",1);

 if(document.getElementById('brand').data)
  document.getElementById("brandid").value = document.getElementById('brand').data['id'];

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
