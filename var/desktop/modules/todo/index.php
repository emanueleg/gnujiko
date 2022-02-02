<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 18-04-2013
 #PACKAGE: todo-module
 #DESCRIPTION: TODO module for Gnujiko Desktop.
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_INTERNAL_LOAD, $_MODULE_INFO;

//-- PRELIMINARY ----------------------------------------------------------------------------------------------------//
if(!$_INTERNAL_LOAD) // this script is loaded into a layer
{
 define("VALID-GNUJIKO",1);
 $_BASE_PATH = "../../../../";
 include_once($_BASE_PATH."include/gshell.php");
 include_once($_BASE_PATH."include/js/gshell.php");
}
//-------------------------------------------------------------------------------------------------------------------//
$_MODULE_INFO['handle'] = $_MODULE_INFO['id']."-handle";
$_MODULE_INFO['front'] = $_MODULE_INFO['id']."-front";
$_MODULE_INFO['back'] = $_MODULE_INFO['id']."-back";

$_MODULE_INFO['plugs'][] = $_MODULE_INFO['id']."-plug1";

$from = time();

include_once($_BASE_PATH."include/i18n.php");
LoadLanguage("todo");

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/todo/todo.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/desktop/modules/todo/todo.js" type="text/javascript"></script>

<!-- FRONT PANEL -->
<div id="<?php echo $_MODULE_INFO['front']; ?>" class="gnujiko-desktop-module-front-panel todomod-frontpanel" onload="todomodule_load('<?php echo $_MODULE_INFO['id']; ?>')">
<div class="todomod-header">
<table width="100%" height='28' cellspacing="0" cellpadding="0" border="0">
<tr><td align='right' valign='middle' width='34'>&nbsp;</td>
	<td align='center' valign='middle' class='todomod-handle' id="<?php echo $_MODULE_INFO['handle']; ?>">COSE DA FARE</td>
	<td align='left' valign='middle' width='34'>&nbsp;</td></tr>
</table>
<table width="100%" height='6' cellspacing='0' cellpadding='0' border='0' class="todomod-refdate">
<tr><td>Annota qui gli appuntamenti e le cose da fare</td></tr>
</table>
</div>

<div class="todomod-container" id="<?php echo $_MODULE_INFO['id'].'-container'; ?>">
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="todolist" id="todolist">
<?php
 $imgFolder = $_ABSOLUTE_URL."var/desktop/modules/todo/img/";
 $ret = GShell("dynarc item-list -ap 'todo' --all-cat -get `status,priority,date_from,date_to,all_day` --order-by `priority DESC`");
 $list = $ret['outarr']['items'];
 for($c=0; $c < count($list); $c++)
 {
  echo "<tr id='".$list[$c]['id']."'><td width='40' align='center' valign='middle'>";
  if($list[$c]['status'])
   echo "<img src='".$imgFolder."cb_checked.png' onclick='todomodule_setTodoStatus(this,0)'/></td>";
  else
   echo "<img src='".$imgFolder."cb_unchecked.png' onclick='todomodule_setTodoStatus(this,1)'/></td>";
  echo "<td valign='middle'".($list[$c]['status'] ? " class='completed'" : ($list[$c]['priority'] > 2 ? " class='urgent'" : ""))."><a href='#' onclick='todomodule_editTodo(this)'>".$list[$c]['name']."</a></td>";
  echo "<td width='32' align='center' valign='middle'><img src='".$imgFolder."delete.png' onclick='todomodule_deleteTodo(this)'/></td></tr>";
 }
?>
</table>
</div>

<div class="todomod-footer">
 <input type='text' class='todolist-text' style='width:90%' id="todolist-newtodoedit" onchange="todomodule_newTodo()"/> <img src="<?php echo $imgFolder; ?>add.png" class="todoadd" onclick="todomodule_newTodo()"/>
</div>

</div>
<!-- EOF - FRONT PANEL -->

<!-- BACK PANEL -->
<div id="<?php echo $_MODULE_INFO['back']; ?>" class="gnujiko-desktop-module-back-panel todomod-backpanel" style="display:none">
<table width='100%' cellspacing='0' cellpadding='0' border='0' height='100%'>
<tr><td class='header'><?php echo $_MODULE_INFO['title']; ?></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td height='32'>
	 &nbsp;
	</td></tr>
</table>
</div>

<!-- EOF - BACK PANEL -->
<script>

</script>

