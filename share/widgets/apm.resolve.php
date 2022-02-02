<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-02-2013
 #PACKAGE: apm-gui
 #DESCRIPTION: APM resolve gui
 #VERSION: 2.2eta
 #CHANGELOG: 05-02-2013 : Bug fix on check depends.
			 19-12-2012 : Graphical bug fix.
			 02-01-2012 : Multi language and bug fix
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("apm-gui");


$x = explode(",",$_REQUEST['packages']);
$ret = GShell("gpkg resolve ".trim(implode(" ",$x)),$_REQUEST['sessid'], $_REQUEST['shellid']);

if($ret['error'])
{
 $msg = str_replace("\n","",$ret['message']);
 ?>
 <html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>APM-Resolve</title></head><body>
 <?php echo $msg; ?>
 <script>
 function bodyOnLoad(){gframe_close("<?php echo $msg; ?>");}
 </script>
 </body></html>
 <?php
 return;
}

$list = $ret['outarr'];

if(!count($list['TO_BE_REMOVE']) && !count($list['OUTDATED']) && !count($list['UNAVAILABLE']))
{
 if(!count($list['AVAILABLE']) || (count($list['AVAILABLE']) == count($x)))
 {
  ?>
  <html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>APM-Resolve</title></head><body>
  <script>
  function bodyOnLoad()
  {
   var a = new Array();
   a['AVAILABLE'] = new Array();
   <?php
   for($c=0; $c < count($x); $c++)
    echo "a['AVAILABLE'].push('".$x[$c]."');";
   ?>
   gframe_close("Done!",a);
  }
  </script>
  </body></html>
  <?php
  return;
 }
}

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>APM-Resolve</title>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/resolve.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/objects/gtabmenu/simple-blue.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
</head><body>

<div class="widget">
<table class='header' width='480' border='0' cellspacing='0' cellpadding='0'>
<tr><td width='46' valign='middle' rowspan='2'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/apm/icons/lamp.png"/></td>
	<td valign='middle'><h2><?php echo i18n("Select the additional required changes?"); ?></h4><?php echo i18n("The chosen action also affects other packages."); ?><br/><?php echo i18n("Please allow the following changes."); ?></td></tr>
</table>
<div class='contents' style="height:200px;overflow:auto">
<?php
$a = "";
if(count($list['TO_BE_REMOVE']))
{
 echo "<ul class='list'>".i18n('To be remove').":";
 for($c=0; $c < count($list['TO_BE_REMOVE']); $c++)
 {
  echo "<li>".$list['TO_BE_REMOVE'][$c]."</li>";
  $a.= "a['TO_BE_REMOVE'].push('".$list['TO_BE_REMOVE'][$c]."');";
 }
 echo "</ul>";
}
if(count($list['OUTDATED']))
{
 echo "<ul class='list'>".i18n('To be update').":";
 for($c=0; $c < count($list['OUTDATED']); $c++)
 {
  echo "<li>".$list['OUTDATED'][$c]."</li>";
  $a.= "a['OUTDATED'].push('".$list['OUTDATED'][$c]."');";
 }
 echo "</ul>";
}
if(count($list['AVAILABLE']))
{
 echo "<ul class='list'>".i18n('To be install').":";
 for($c=0; $c < count($list['AVAILABLE']); $c++)
 {
  echo "<li>".$list['AVAILABLE'][$c]."</li>";
  $a.= "a['AVAILABLE'].push('".$list['AVAILABLE'][$c]."');";
 }
 echo "</ul>";
}
if(count($list['UNAVAILABLE']))
{
 echo "<ul class='list'>".i18n('Unavailable').":";
 for($c=0; $c < count($list['UNAVAILABLE']); $c++)
 {
  echo "<li style='color:red;'>".$list['UNAVAILABLE'][$c]."</li>";
  $a.= "a['UNAVAILABLE'].push('".$list['UNAVAILABLE'][$c]."');";
 }
 echo "</ul>";
}
for($c=0; $c < count($list['execordering']); $c++)
 $a.= "a['execordering'].push('".$list['execordering'][$c]."');";
?>
</div>
</div>
<div class='widget-footer'>
<table width='100%' border='0'><tr><td>&nbsp;</td><td width="240">
<ul class='simple-blue-buttons'>
	<li><span onclick="widget_apmresolve_close()"><?php echo i18n("Abort"); ?></span></li>
	<?php
	if(!count($list['UNAVAILABLE']))
	{
	 ?>
	 <li><span onclick="widget_apmresolve_submit()"><?php echo i18n("Select"); ?></span></li>
	 <?php
	}
	?>
</ul></td></tr></table>
</div>

<script>
function widget_apmresolve_close()
{
 gframe_close("aborted.");
}

function widget_apmresolve_submit()
{
 var a = new Array();
 a['TO_BE_REMOVE'] = new Array();
 a['OUTDATED'] = new Array();
 a['AVAILABLE'] = new Array();
 a['UNAVAILABLE'] = new Array();
 a['execordering'] = new Array();
 <?php echo $a; ?>

 gframe_close("Done",a);
}
</script>
</body></html>
<?php

