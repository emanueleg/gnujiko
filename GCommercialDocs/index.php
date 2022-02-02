<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 01-12-2012
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: Official Gnujiko commercial documents manager.
 #VERSION: 
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DESKTOP_SHOW_TOOLBAR, $_DESKTOP_TITLE;

$_DESKTOP_SHOW_TOOLBAR = false;
$_DESKTOP_BACKGROUND = "#ffffff";
$_DESKTOP_TITLE = "Documenti commerciali";

$_BASE_PATH = "../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>DOCUMENTI COMMERCIALI</title></head>
<link rel='shortcut icon' href='share/images/favicon.png' />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>GCommercialDocs/common.css" type="text/css" />
<?php
if(file_exists($_BASE_PATH."include/headings/desktop.php"))
{
 include($_BASE_PATH.'include/headings/desktop.php');
}
else
{
 echo "<body onload='desktopOnLoad()'>";
 include($_BASE_PATH.'include/headings/default.php');
 include_once($_BASE_PATH."include/js/gshell.php");
}
//-------------------------------------------------------------------------------------------------------------------//

$_DOC_ICONS = array('preemptives'=>'doc-blue.png', 'orders'=>'doc-orange.png', 'ddt'=>'doc-violet.png', 'invoices'=>'doc-green.png',
	'vendororders'=>'doc-red.png', 'purchaseinvoices'=>'doc-gray.png', 'agentinvoices'=>'doc-sky.png', 'intervreports'=>'doc-maroon.png');

?>
<table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
<tr><td style="background: #f3f8fd;width:200px;" valign="top">
	<ul class='docmenu' id='docmenu' style="margin-top:10px;">
	<?php
	$ret = GShell("dynarc cat-list -ap `commercialdocs`");
	$list = $ret['outarr'];
	for($c=0; $c < count($list); $c++)
	{
	 $icon = $_DOC_ICONS[strtolower($list[$c]['tag'])] ? $_DOC_ICONS[strtolower($list[$c]['tag'])] : "doc-sky.png";
	 if(($c==0) && !$_REQUEST['doctype'])
	  $_REQUEST['doctype'] = strtolower($list[$c]['tag']);
	 echo "<li onclick='doctypeChange(\"".strtolower($list[$c]['tag'])."\")'".(strtolower($list[$c]['tag']) == $_REQUEST['doctype'] ? " class='selected'>" : ">")."<img src='".$_ABSOLUTE_URL."GCommercialDocs/img/".$icon."'/><span>".$list[$c]['name']."</span></li>";
	}
	?>
	</ul>
	</td><td style="background: #ffffff;" valign="top" id="IFRAMESPACE">
	&nbsp;
	</td></tr>
</table>
<?php
//-------------------------------------------------------------------------------------------------------------------//
if(file_exists($_BASE_PATH."include/footers/desktop.php"))
 include($_BASE_PATH.'include/footers/desktop.php');
else
 include($_BASE_PATH.'include/footers/default.php');
?>
<script>
var ITEMLISTIFRAME = null;
function desktopOnLoad()
{
 var sh = new GShell();
 sh.OnPreOutput = function(o,a,msgType){
	 switch(msgType)
	 {
	  case "LOADED" : {
		 ITEMLISTIFRAME = a;
		} break;
	 }
	}

 sh.sendCommand("gframe -f commdocs.list --append-to IFRAMESPACE -h 100% -params `doctype=<?php echo $_REQUEST['doctype']; ?>&frameheight="+document.getElementById('IFRAMESPACE').offsetHeight+"&catid=<?php echo $_REQUEST['catid']; ?>`");

 window.onresize = function(){
	 if(ITEMLISTIFRAME)
	 {
	  //ITEMLISTIFRAME.AutoResize();
	  ITEMLISTIFRAME.adjustResize(document.getElementById('IFRAMESPACE').offsetHeight);
	 }

	}
}

function doctypeChange(tag)
{
 document.location.href = ABSOLUTE_URL+"GCommercialDocs/index.php?doctype="+tag;
}
</script>
</body></html>
<?php

