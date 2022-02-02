<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 12-01-2013
 #PACKAGE: rubrica
 #DESCRIPTION: Rubrica new form
 #VERSION: 2.1beta
 #CHANGELOG: 12-01-2013 : Bug fix.
			 18-01-2012 - Integration with gframe.
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_USERS_HOMES;
$_BASE_PATH = "../../";
include_once($_BASE_PATH."init/init1.php");
include_once($_BASE_PATH."include/session.php");
define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."include/i18n.php");
LoadLanguage("rubrica");


$title = $_REQUEST['title'] ? $_REQUEST['title'] : i18n('Enter a new contact in address book.');
$contents = $_REQUEST['contents'] ? $_REQUEST['contents'] : "";
$ap = $_REQUEST['ap'] ? $_REQUEST['ap'] : "rubrica";

?>
<html><head><title>Rubrica</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/rubrica.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
</head><body>

<style type='text/css'>
h4 {
	font-family: Arial;
	font-size: 16px;
	color: #333333;
	margin: 0px;
	padding: 0px;
}
small {
	font-family: Arial;
	font-size: 12px;
	color: #666666;
}
p {
	font-family: Arial;
	font-size: 12px;
	color: #333333;
	padding: 0px;
	padding-left: 20px;
	margin: 2px;
}
</style>

<div style="border:4px solid #a4caee;background:#ffffff;width:500px;height:200px;">
 <table width="100%" border="0">
 <tr><td valign="top" align="center" width="138"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/new-user.png"/></td>
	 <td valign="top" align="left"><h4><?php echo $title; ?></h4>
	 <small><?php echo i18n('Specify a name and choose the category in which to insert the new contact.'); ?></small><br/><br/>
	 <p><b><?php echo i18n('Name:'); ?></b> <input type='text' size='20' id='name' value="<?php echo stripslashes($contents); ?>"/></p>
	 <p><b><?php echo i18n('Category:'); ?></b> <select id='cat'><?php
	 $ret = GShell("dynarc cat-list -ap `".$ap."`");
	 $list = $ret['outarr'];
	 for($c=0; $c < count($list); $c++)
	 {
	  if($_REQUEST['cat'])
	   echo "<option value='".$list[$c]['id']."'".($list[$c]['id'] == $_REQUEST['cat'] ? " selected='selected'>" : ">").$list[$c]['name']."</option>";
	  else
	   echo "<option value='".$list[$c]['id']."'".($list[$c]['tag'] == $_REQUEST['ct'] ? " selected='selected'>" : ">").$list[$c]['name']."</option>";
	 }
	 ?></select></p>
	 <div style="margin-top:20px;border-top:1px solid #a4caee;padding-top:10px;">
	  <input type='button' value="<?php echo i18n('OK'); ?>" onclick='widget_default_submit()'/> <input type='button' value="<?php echo i18n('Abort'); ?>" onclick='widget_default_close()'/>
	 </div>
	 </td></tr>
 </table>
</div>

<script>
var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; 
var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";

function widget_default_submit()
{
 var _name = document.getElementById('name').value;
 if(!_name)
 {
  alert("<?php echo i18n('You must specify a valid name'); ?>");
  return;
 }
 var _cat = document.getElementById('cat').value;
 
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 gframe_close(o,a);
	}
 sh.sendCommand("dynarc new-item -ap `<?php echo $_REQUEST['ap'] ? $_REQUEST['ap'] : 'rubrica'; ?>` -cat '"+_cat+"' -name `"+_name+"` -group rubrica");
 return false;
}

function widget_default_close()
{
 gframe_close();
}
</script>
</body></html>
<?php

