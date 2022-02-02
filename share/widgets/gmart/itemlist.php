<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-05-2012
 #PACKAGE: gmart
 #DESCRIPTION: Article list for GMart - Products.
 #VERSION: 2.0
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES, $_DECIMALS, $_PRICELISTS;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/company-profile.php");

if(!$_REQUEST['view'])
 $_REQUEST['view'] = "thumbnails";

$_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'];
$ret = GShell("pricelists list",$_REQUEST['sessid'],$_REQUEST['shellid']);
$_PRICELISTS = $ret['outarr'];

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Edit task</title>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
</head><body>
<style type='text/css'>
small {
	font-family: Arial;
	font-size: 10px;
	color: #000000;
}

b.small {
	font-family: Arial;
	font-size: 11px;
	color: #333333;
}
</style>

<div>
<b class='small'>Visualizza:</b> 
		<input type='radio' class='small' name='show' checked='true' onclick="changeView('thumbnails')"/><small>Anteprime</small>
		<input type='radio' class='small' name='show' <?php if($_REQUEST['view'] == "smallthumb") echo "checked='true'"; ?> onclick="changeView('smallthumb')"/><small>Miniature</small> 
		<input type='radio' class='small' name='show' <?php if($_REQUEST['view'] == "list") echo "checked='true'"; ?> onclick="changeView('list')"/><small>Lista</small>
</div>

<?php
include_once($_BASE_PATH."include/js/gshell.php");
switch($_REQUEST['view'])
{
 case 'smallthumb' : include($_BASE_PATH."share/widgets/gmart/smallthumb-view.php"); break;
 case 'list' : include($_BASE_PATH."share/widgets/gmart/list-view.php"); break;
 default : include($_BASE_PATH."share/widgets/gmart/thumbnails-view.php"); break;
}
?>

<script>
function OnSelectCategory(catId) // external function //
{
 var href = document.location.href.replace('#','');
 if(href.indexOf("&catid=") > 0)
  href = href.replace("&catid=<?php echo $_REQUEST['catid']; ?>","&catid="+catId);
 else
  href = href+"&catid="+catId;

 if(href.indexOf("&pg=") > 0)
  href = href.replace("&pg=<?php echo $_REQUEST['pg']; ?>","&pg=1");

 document.location.href=href;
}

function changeView(view)
{
 gframe_shotmessage("View changed.", view, "VIEW_CHANGED");
 gframe_close();
}
</script>
</body></html>
<?php

