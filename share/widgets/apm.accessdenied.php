<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 02-02-2016
 #PACKAGE: apm-gui
 #DESCRIPTION: Gnujiko access denied page.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_GNUJIKO_ACCOUNT, $_GNUJIKO_TOKEN;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("apm-gui");

$sessInfo = sessionInfo($_REQUEST['sessid']);
if($sessInfo['uname'] != "root")
{
 $msg = "You must be root";
 ?>
 <html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>APM-Access Denied</title></head><body>
 <?php echo $msg; ?>
 <script>
 function bodyOnLoad()
 {
  gform_close("<?php echo $msg; ?>");
 }
 </script></body></html>
 <?php
 return;
}

$errormsg = $_REQUEST['errormessage'];
$package = "";
if($errormsg)
{
 $p = strrpos($errormsg, " ");
 if($p > 0)
  $package = substr($errormsg, $p+1);
}

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>APM-Access Denied</title>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<?php
include_once($_BASE_PATH."var/templates/standardwidget/index.php");
include_once($_BASE_PATH."include/js/gshell.php");
?>
</head><body>

 <div class='standardwidget' style='width:640px;height:300px'>
 <h2>Accesso negato</h2>
 <hr/>
 <p style="font-family:arial,times;font-size:14px">
 <?php
  if($package)
   echo "Impossibile installare il pacchetto <b>".$package."</b>";
 else
  echo "Il tuo account non &egrave; abilitato all&lsquo;accesso su questo repository.";
 ?>
 </p>
 <p>
 <table border="0" cellspacing="10" style="margin-top:40px">
  <tr><td valign='middle'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/img/accessdenied.png" width='128'/></td>
	  <td valign='middle' style='font-size:14px;color:#f31903'>Spiacente!, ma il tuo account non dispone dei diritti sufficienti per accedere a questo repository pertanto non potrai installare 
		<?php 
		 if($package) echo "il pacchetto <b style='color:#333333'>".$package."</b> e nessun&lsquo;altro pacchetto proveniente da questo canale.";
		else
		 echo "alcun pacchetto proveniente da esso";
		?></td>
  </tr>
 </table>
 </p>
 <hr/>
 <input type='button' class='button-blue' value='Chiudi' onclick="abort()"/> 
 </div>

<script>
function bodyOnLoad()
{
}

function abort()
{
 gframe_close();
}

</script>
</body></html>
<?php

