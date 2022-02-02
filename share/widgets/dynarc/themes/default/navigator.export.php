<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 14-01-2012
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Default theme for dynarc.navigator - Export form
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_ARCHIVE_PREFIX, $_ARCHIVE_INFO, $_CAT_INFO;

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");
LoadLanguage("dynarc");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $_ARCHIVE_INFO['name']; ?> - Export</title>
<?php
include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/progressbar/index.php");
?></head><body><?php

$q = "";
if($_REQUEST['id'])
{
 $x = explode(",",$_REQUEST['id']);
 for($c=0; $c < count($x); $c++)
  $q.= " -id ".$x[$c];
 $fileName = $_ARCHIVE_INFO['name']." - ".i18n('Individual elements');
}
else if($_REQUEST['cat'])
{
 $q = " -cat ".$_REQUEST['cat'];
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT name FROM dynarc_".$_ARCHIVE_INFO['prefix']."_categories WHERE id='".$_REQUEST['cat']."'");
 $db->Read();
 $fileName = $_ARCHIVE_INFO['name']." - ".$db->record['name'];
 $db->Close();
}
else if(isset($_REQUEST['exportall']))
{
 $q = " -all";
 $fileName = "Backup ".$_ARCHIVE_INFO['name']." del ".date('d.m.Y H:i');
}



$form = new GForm(i18n('Export to file'), "MB_ABORT", "simpleform", "default", "orange", 420, 180);
$form->Begin($_ABSOLUTE_URL."share/widgets/dynarc/themes/default/img/export.png");
?>
<input type='hidden' name='action' value='export'/>
<input type='hidden' name='archiveprefix' value="<?php echo $_ARCHIVE_PREFIX; ?>"/>

<table width='100%' border='0'>
<tr><td valign='top'><b style='font-family:Arial;font-size:18px;color:#333333' id='title'><?php echo i18n('Export in progress... wait!'); ?></b></td>
	<td align='right'><b style='font-family:Arial;font-size:18px;color:#333333' id='percentage'>0%</b></td></tr>
<tr><td colspan="2"><?php
$pb = new ProgressBar();
$pb->Paint();
?></td></tr>
<tr><td colspan="2" style="font-family:Arial;font-size:12px;" id='message'>&nbsp;</td></tr>
</table>
<?php
$form->End();

?>
<script>
var bar = new ProgressBar();
var steps = 1;
var step = 0;

function bodyOnLoad()
{
 var sh = new GShell();
 sh.OnPreOutput = function(o,a, msgType, msgRef){
	 switch(msgType)
	 {
	  case 'ESTIMATION' : steps = a['estimated_elements']; break;
	  case 'PROGRESS' : {
		 document.getElementById('message').innerHTML = o;
		 bar.setValue(bar.value + (100/steps));
		 document.getElementById('percentage').innerHTML = bar.value+"%";
		} break;
	 }
	}
 sh.OnFinish = function(o,a){
	 bar.setValue(100);
	 document.getElementById('percentage').innerHTML = "100%";
	 document.getElementById('title').innerHTML = "<?php echo i18n('Export complete!'); ?>";
	 document.getElementById('message').innerHTML = "<?php echo i18n('OK! You can now close this window.'); ?>";
	 document.getElementById('btn_abort').value = "<?php echo i18n('Close'); ?>";
	 document.location.href = "<?php echo $_ABSOLUTE_URL; ?>getfile.php?file="+a['filename'];
	}
 sh.sendCommand("dynarc export -ap `<?php echo $_ARCHIVE_PREFIX; ?>`<?php echo $q; ?> -f `<?php echo $fileName; ?>`");
}
</script>

</body></html>

