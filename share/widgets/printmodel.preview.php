<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2015 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 01-05-2015
 #PACKAGE: printmodels-config
 #DESCRIPTION: Default preview widget for print models.
 #VERSION: 2.2beta
 #CHANGELOG: 01-05-2015 : Possibilità di generare l'anteprima anche della prima e ultima pagina.
			 23-01-2013 : Bug fix for absolute URL with images & link.
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");

$ap = $_REQUEST['ap'] ? $_REQUEST['ap'] : "printmodels";
$id = $_REQUEST['id'];
$alias = $_REQUEST['alias'];
$ret = GShell("dynarc item-info -ap `".$ap."` ".($alias ? "-alias `".$alias."`" : "-id `".$id."`")." -extget `printmodelinfo,css`");
$docInfo = $ret['outarr'];
$id = $_REQUEST['id'] = $docInfo['id'];

switch($_REQUEST['preview'])
{
 case 'firstpage' : $_CONTENTS = $docInfo['firstpage_content']; break;
 case 'lastpage' : $_CONTENTS = $docInfo['lastpage_content']; break;
 default : $_CONTENTS = $docInfo['desc'];
}

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Print Model - Preview</title>
<?php
if($_REQUEST['screenshot'])
 include($_BASE_PATH."var/objects/htmlgutility/screenshot.php");
?>
</head><body>
<style type='text/css'>
table.previewtable {
	background: #ffffff;
	border:1px solid #dadada;
	position: relative;
}
<?php echo $docInfo['css'][0]['content']; ?>
</style>
<input type="button" style="float:right;" value="Chiudi" onclick="abort()"/>
<table class="previewtable" align='center' valign='middle' cellspacing="0" cellpadding="0" border="0" style="width:210mm;height:297mm;">
<tr><td valign="top" id='preview-contents'><?php echo str_replace("{ABSOLUTE_URL}",$_ABSOLUTE_URL,$_CONTENTS); ?></td></tr>
</table>

<script>
var makeScreenShot = <?php echo $_REQUEST['screenshot'] ? "true" : "false"; ?>;

function abort()
{
 if(makeScreenShot)
  ScreenShot(document.getElementById('preview-contents'), function(a){gframe_close("Screenshot has been generated!",a);});
 else
  gframe_close();
}
</script>
</body></html>
<?php

