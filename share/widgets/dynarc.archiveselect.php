<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 14-01-2012
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Select an archive
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("dynarc");

$ret = GShell("dynarc archive-list");
if(!$ret['error'])
 $list = $ret['outarr'];

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo i18n("Dynarc - Select archive"); ?></title>
<?php
include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."include/js/gshell.php");
?></head><body><?php
$form = new GForm(i18n("Select archive"), "MB_OK|MB_ABORT", "simpleform", "default", "orange", $_REQUEST['width'] ? $_REQUEST['width'] : 400, $_REQUEST['height'] ? $_REQUEST['height'] : 200);
$form->Begin();

$ret = GShell("dynarc archive-list --order-by 'name ASC'");
if(!$ret['error'])
 $list = $ret['outarr'];

echo "<div style='padding:10px;font-family:Arial,sans-serif;font-size:13px;text-align:center'>";
echo "<span style='font-size:14px;color:#3364C3'><b>".i18n('Select archive')."</b></span><br/><br/><select id='ap'>";
for($c=0; $c < count($list); $c++)
 echo "<option value='".$list[$c]['prefix']."'>".$list[$c]['name']."</option>";
echo "</select>";
echo "</div>";

$form->End();
?>
<script>
function widget_submit()
{
 var ap = document.getElementById('ap').value;
 gframe_close("Archive "+ap+" selected.",ap);
}
</script>

</body></html>
<?php

