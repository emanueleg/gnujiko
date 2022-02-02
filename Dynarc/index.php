<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-01-2012 
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Main file
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DESKTOP_SHOW_TOOLBAR, $_DESKTOP_TITLE;

$_BASE_PATH = "../";

include($_BASE_PATH.'init/init1.php');
include($_BASE_PATH.'include/session.php');
include_once($_BASE_PATH."include/i18n.php");
LoadLanguage("dynarc");

$_DESKTOP_SHOW_TOOLBAR = false;
$_DESKTOP_TITLE = i18n("Archive manager");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $_SOFTWARE_NAME; ?></title></head>
<link rel='shortcut icon' href='share/images/favicon.png' />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>Dynarc/dynarc.css" type="text/css" />
<?php
if(file_exists($_BASE_PATH."include/headings/desktop.php"))
{
 include($_BASE_PATH.'include/headings/desktop.php');
}
else
{
 echo "<body>";
 include($_BASE_PATH.'include/headings/default.php');
}
//-------------------------------------------------------------------------------------------------------------------//
$colors = array("red","orange","yellow","lightgreen","green","lightblue","blue","violet","lightmaroon","maroon");
$page = $_REQUEST['pg'] ? $_REQUEST['pg'] : 1;

$ret = GShell("dynarc archive-list --get-count -limit ".($page>1 ? (($page-1)*14)."," : "")."14 --order-by `name ASC`");
$count = $ret['outarr']['count'];
$list = $ret['outarr']['items'];

?>
<table width='100%' height='100%' border='0' cellspacing='0' cellpadding='0'>
<tr><td valign='middle' align='center'><a href="?pg=<?php echo $page-1; ?>" <?php if($page < 2) echo "style='visibility:hidden;'"; ?>><img src="<?php echo $_ABSOLUTE_URL; ?>Dynarc/img/previous.png" border='0'/></a></td>
	<td valign='middle' width='742'>
	 <div class='dynarc-container'>
	  <?php
	  $colIdx=0;
	  for($c=0; $c < count($list); $c++)
	  {
	   $archive = $list[$c];
	   echo "<div class='archive ".$colors[$colIdx]."' onclick='showArchive(\"".$archive['prefix']."\")'>";
	   echo "<div class='title'>".$archive['name']."</div>";
	   echo "</div>";
	   $colIdx = ($colIdx == (count($colors)-1)) ? 0 : $colIdx+1;
	  }
	 ?>
	 </div>
	 <?php
	 $cc = ceil($count/14);
	 if($cc > 2)
	 {
	  echo "<div class='serp' align='center'>";
	  for($c=0; $c < $cc; $c++)
	   echo "<a href='?pg=".($c+1)."'".($page == ($c+1) ? "class='active'>" : ">").($c+1)."</a>";
	  echo "</div>";
	 }
	 ?>
	</td>
	<td valign='middle' align='center'><a href="?pg=<?php echo $page+1; ?>" <?php if($count <= ($page*14)) echo "style='visibility:hidden;'"; ?>><img src="<?php echo $_ABSOLUTE_URL; ?>Dynarc/img/next.png" border='0'/></a></td></tr>
</table>
<?php
//-------------------------------------------------------------------------------------------------------------------//

if(file_exists($_BASE_PATH."include/footers/desktop.php"))
 include($_BASE_PATH.'include/footers/desktop.php');
else
 include($_BASE_PATH.'include/footers/default.php');
?>

<script>
function showArchive(ap)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var sh2 = new GShell();
	 sh2.sendCommand("dynlaunch -ap `"+ap+"` -id `"+a+"`");
	}
 sh.sendCommand("gframe -f dynarc.navigator -params `ap="+ap+"&fullextensions`");
}
</script>

</body></html>
<?php
