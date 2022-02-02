<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 02-01-2012
 #PACKAGE: apm-gui
 #DESCRIPTION: Edit source form for APM
 #VERSION: 2.0beta
 #CHANGELOG: 02-01-2012 : Multi language.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
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
 <script>
 function bodyOnLoad()
 {
  alert("<?php echo $msg; ?>");
  gframe_close();
 }
 </script>
 <?php
 return;
}

//-------------------------------------------------------------------------------------------------------------------//
?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>APM - Edit Source</title>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<?php
include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."include/js/gshell.php");

?></head><body><?php
$form = new GForm(i18n("Edit source"), "MB_OK|MB_ABORT", "simpleform", "default", "blue","380", "240");
$form->Begin($_ABSOLUTE_URL."share/widgets/apm/icons/settings.png");
?>
<p>URI: <input type="text" size="30" id="uri" value="<?php echo $_REQUEST['url']; ?>"/></p>
<p>Version: <input type="text" size="6" id="ver" value="<?php echo $_REQUEST['ver']; ?>"/></p>
<p>Section: <input type="text" size="12" id="sec" value="<?php echo $_REQUEST['sec']; ?>"/></p>

<?php
$form->End();
?>

<script>
function bodyOnLoad()
{
 document.getElementById('uri').focus();
}

function widget_submit()
{
 var uri = document.getElementById('uri').value;
 var ver = document.getElementById('ver').value;
 var sec = document.getElementById('sec').value;

 if(!uri) return alert("<?php echo i18n('You must enter a valid address in the URI field'); ?>");
 if(!ver) return alert("<?php echo i18n('Specify the version'); ?>");
 if(!sec) return alert("<?php echo i18n('Specify the section'); ?>");

 var sh = new GShell();
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 sh.sendCommand("apm delete-repository -url `<?php echo $_REQUEST['url']; ?>` -ver `<?php echo $_REQUEST['ver']; ?>` -sec `<?php echo $_REQUEST['sec']; ?>` && apm add-repository -url `"+uri+"` -ver `"+ver+"` -sec `"+sec+"`");
 return false;
}
</script>
</body></html>
<?php

