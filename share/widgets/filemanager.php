<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-11-2012
 #PACKAGE: filemanager
 #DESCRIPTION: Official Gnujiko File Manager
 #VERSION: 2.0beta
 #CHANGELOG: 05-11-2012 : Some bug fix.
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_USERS_HOMES;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");
LoadLanguage("filemanager");
//-------------------------------------------------------------------------------------------------------------------//
function jstree_recursiveInsert($node)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_USERS_HOMES;
 echo "<li id='".fullescape($node['path'])."' name='".fullescape($node['name'])."'><a href='#'><ins>&nbsp;</ins>"
	.html_entity_decode($node['name'],ENT_QUOTES,"UTF-8")."</a>";
 if(count($node['subdirs']))
 {
  echo "<ul>";
  for($c=0; $c < count($node['subdirs']); $c++)
   jstree_recursiveInsert($node['subdirs'][$c]);
  echo "</ul>";
 }
 echo "</li>";
}
//-------------------------------------------------------------------------------------------------------------------//
function fullescape($in)
{
 /*Thanks to omid@omidsakhi.com that his code gave me an idea. */
 /* Full escape function without % sign */
  $out = '';
  for ($i=0;$i<strlen($in);$i++)
  {
    $hex = dechex(ord($in[$i]));
    if ($hex=='')
       $out = $out.urlencode($in[$i]);
    else
       $out = $out.((strlen($hex)==1) ? ('0'.strtoupper($hex)):(strtoupper($hex)));
  }
  $out = str_replace('+','20',$out);
  $out = str_replace('_','5F',$out);
  $out = str_replace('.','2E',$out);
  $out = str_replace('-','2D',$out);
  return $out;
}
//-------------------------------------------------------------------------------------------------------------------//
?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Gnujiko - File Manager</title>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/default.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/filemanager/filemanager.css" type="text/css" />
<?php

include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."var/objects/jstree/index.php");
?>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>"; var FILTER = "<?php echo $_REQUEST['filter']; ?>";</script>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<script>
var SESSID = "<?php echo $_REQUEST['sessid']; ?>";
var SHELLID = "<?php echo $_REQUEST['shellid']; ?>";

/* Language */
var i18n = new Array();
i18n['User folder'] = "<?php echo i18n('User folder'); ?>";
i18n['Enter the new folder name'] = "<?php echo i18n('Enter the new folder name'); ?>";
i18n['Rename folder'] = "<?php echo i18n('Rename folder'); ?>";
i18n['Are you sure you want to delete the folder %s ?'] = "<?php echo i18n('Are you sure you want to delete the folder %s ?'); ?>";
i18n['Are you sure you want to delete %s ?'] = "<?php echo i18n('Are you sure you want to delete %s ?'); ?>";
i18n['Are you sure you want to remove the selected?'] = "<?php echo i18n('Are you sure you want to remove the selected?'); ?>";
i18n['You must select at least one element'] = "<?php echo i18n('You must select at least one element'); ?>";
i18n['Nothing to be pasted'] = "<?php echo i18n('Nothing to be pasted'); ?>";
</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/filemanager/filemanager.js" type="text/javascript"></script>
</head><body style='background:transparent;'>
<?php
$form = new GForm("File Manager", null, "simpleform", "default", "blue", 800, 612);
$form->Begin($_ABSOLUTE_URL."share/widgets/filemanager/img/icon.png");
?>
<input type='hidden' id='path' value="<?php echo $_REQUEST['path'] ? fullescape($_REQUEST['path']) : ''; ?>"/>
<table width='100%' height='400' cellspacing='0' cellpadding='0' border='0'>
<tr><td valign='top' align='left' width='200' id='menuspace'>
	<div style='padding:0px;'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/filemanager/img/home.png" style='vertical-align:middle;'/> <a id='hometitle' href='#' onclick='_selectDir(null)'><?php echo i18n('Home'); ?></span></div>
	<div class='treediv'>
	 <div class="demo source" id="tree_div">
	  <ul>
	  <?php
	  $ret = GShell("ls -tree", $_REQUEST['sessid'], $_REQUEST['shellid']);
	  $nodes = $ret['outarr'];
	  for($c=0; $c < count($nodes); $c++)
	   jstree_recursiveInsert($nodes[$c]);
	  ?>
	  </ul>
	 </div>
	</div></td>

	<td valign='top' align='left'>
	<div id='pathway'>&nbsp;</div>
	<div id='dirname'><?php echo i18n('User folder'); ?></div>
	<div id='iframespace'></div>
	</td></tr>
</table>

<?php
$form->End();
?>
<iframe style='border:0px;width:100%;height:416px;display:none;' src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/filemanager/index.php?sessid=<?php echo $_REQUEST['sessid']; ?>&shellid=<?php echo $_REQUEST['shellid']; if($_REQUEST['filter']) echo '&filter='.$_REQUEST['filter']; ?>path=<?php echo urlencode($_REQUEST['path']); ?>" onload="iframelist_OnLoad()" id='fmiframe'/>
</body></html>
<?php



