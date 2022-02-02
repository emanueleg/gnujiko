<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-09-2013
 #PACKAGE: gmart
 #DESCRIPTION: Bulk edit - Units
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$selected = explode(",",$_REQUEST['ids']);

if($_POST['action'] == "edit")
{
 $db = new AlpaDatabase();
 $units = $_POST['units'];
 $_AP = $_POST['ap'];
 for($c=0; $c < count($selected); $c++)
 {
  $id = $selected[$c];
  $db->RunQuery("UPDATE dynarc_".$_AP."_items SET units='".$units."' WHERE id='".$id."'");
 }
 $db->Close();
}

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>BulkEdit - Units</title>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<?php
include_once($_BASE_PATH."var/templates/standardwidget/index.php");
include_once($_BASE_PATH."include/js/gshell.php");



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
 <h2>Azioni di gruppo</h2>
 <hr/>
 <h4>Cambia l&lsquo;unit&agrave; di misura ai <?php echo count($selected); ?> prodotti selezionati</h4>
  <p>
   <label>Digita l&lsquo;unit&agrave; di misura:</label> <input type='text' name="units" class='edit' style='width:30px'/>
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
