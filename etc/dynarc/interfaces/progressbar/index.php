<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-04-2012
 #PACKAGE: progressbar
 #DESCRIPTION: Progress bar interface for Dynarc pre-output messages.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO: 
 
*/

global $_ABSOLUTE_URL, $_BASE_PATH;

$_BASE_PATH = "../../../../";
include_once($_BASE_PATH."init/init1.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."var/objects/progressbar/index.php");
include_once($_BASE_PATH."include/i18n.php");
LoadLanguage("progressbar");


$form = new GForm(i18n('Please wait...'), "NO_CLOSE", "simpleform", "default", "orange", 420, 200);
$form->Begin($_ABSOLUTE_URL."etc/dynarc/interfaces/progressbar/progress.gif");
?>
<table width='100%' border='0'>
<tr><td valign='top'><b style='font-family:Arial;font-size:18px;color:#333333' id="progressbar-<?php echo $_REQUEST['shellid']; ?>-title">&nbsp;</b></td>
	<td align='right'><b style='font-family:Arial;font-size:18px;color:#333333' id="progressbar-<?php echo $_REQUEST['shellid']; ?>-percentage">0%</b></td></tr>
<tr><td colspan="2"><?php
$pb = new ProgressBar();
$pb->Paint();
?></td></tr>
<tr><td colspan="2" style="font-family:Arial;font-size:12px;" id="progressbar-<?php echo $_REQUEST['shellid']; ?>-message">&nbsp;</td></tr>
</table>
<?php
$form->End();

?>
<script>
var SHELL_ID = "<?php echo $_REQUEST['shellid']; ?>";
</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>etc/dynarc/interfaces/progressbar/common.js" type="text/javascript"></script>
<?php

