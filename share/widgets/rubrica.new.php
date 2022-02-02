<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-02-2016
 #PACKAGE: rubrica
 #DESCRIPTION: Rubrica new form
 #VERSION: 2.2beta
 #CHANGELOG: 21-02-2016 : Aggiunto campi e aggiustamenti grafici.
			 12-01-2013 : Bug fix.
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
$_AGENT_ID = 0;

if($_REQUEST['email']) $_REQUEST['showemail'] = true;
if($_REQUEST['phone']) $_REQUEST['showphone'] = true;
if($_REQUEST['cell'])	$_REQUEST['showcell'] = true;
if(isset($_REQUEST['agent']) && $_REQUEST['agent'])				$_AGENT_ID = $_REQUEST['agent'];
else if(isset($_REQUEST['agentid']) && $_REQUEST['agentid'])	$_AGENT_ID = $_REQUEST['agentid'];

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
table.contact-form td {
 font-family: Arial;
 font-size: 12px;
 color: #333333;
 padding-right: 5px;
 padding-bottom: 5px;	
}

input.edit {
 height: 24px;
 background: transparent;
 border: 1px solid #c0c0c0;
 font-family: sans-serif;
 font-size: 12px;
 padding-left: 5px;
 padding-right: 5px;
}

input.button-blue, input.button-gray, input.button-red, input.button-green {
 background: #498af3;
 border: 1px solid #3079ed;
 border-radius: 2px;
 height: 29px;
 padding-left: 18px;
 padding-right: 18px;
 font-family: arial;
 font-size: 11px;
 color: #ffffff;
 font-weight: bold;
 margin-right: 10px;
 cursor: pointer;
}

input.button-blue:hover, input.button-gray:hover, input.button-red:hover, input.button-green:hover {
 -moz-box-shadow: 0px 1px 2px #cccccc;
 -webkit-box-shadow: 0px 1px 2px #cccccc;
 box-shadow: 0px 1px 2px #cccccc;
}

input.button-gray {
 background-color: #f5f5f5;
 border-color: #dddddd;
 color: #333333;
}

input.button-red {
 background-color: #f44800;
 border-color: #d40000;
 color: #ffffff;
}

input.button-green {
 background-color: #339900;
 border-color: #308f00;
 color: #ffffff;
}
</style>

<div style="border:4px solid #a4caee;background:#ffffff;width:500px">
 <table width="100%" border="0">
 <tr><td valign="top" align="center" width="138"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/rubrica/img/new-user.png"/></td>
	 <td valign="top" align="left"><h4><?php echo $title; ?></h4>
	 <small><?php echo i18n('Specify a name and choose the category in which to insert the new contact.'); ?></small><br/><br/>
	 <table cellspacing='0' cellpadding='0' border='0' class='contact-form'>
	 <tr><td><b><?php echo i18n('Name:'); ?></b> </td>
		 <td><input type='text' class='edit' style='width:200px' id='name' value="<?php echo stripslashes($contents); ?>" maxlength='64'/></td></tr>
	 <tr><td><b><?php echo i18n('Category:'); ?></b> </td>
		 <td><select id='cat' style='width:200px'><?php
	 $ret = GShell("dynarc cat-list -ap `".$ap."`");
	 $list = $ret['outarr'];
	 for($c=0; $c < count($list); $c++)
	 {
	  if($_REQUEST['cat'])
	   echo "<option value='".$list[$c]['id']."'".($list[$c]['id'] == $_REQUEST['cat'] ? " selected='selected'>" : ">").$list[$c]['name']."</option>";
	  else
	   echo "<option value='".$list[$c]['id']."'".($list[$c]['tag'] == $_REQUEST['ct'] ? " selected='selected'>" : ">").$list[$c]['name']."</option>";
	 }
	 ?></select></td></tr>

	 <?php
	  if($_REQUEST['showemail'])
	   echo "<tr><td><b>".i18n('Email:')."</b> </td><td><input type='text' class='edit' style='width:200px' id='email' value=\""
		.$_REQUEST['email']."\" maxlength='40'/></td></tr>";

	  if($_REQUEST['showphone'])
	   echo "<tr><td><b>".i18n('Phone:')."</b> </td><td><input type='text' class='edit' style='width:200px' id='phone' value=\""
		.$_REQUEST['phone']."\" maxlength='14'/></td></tr>";
	  if($_REQUEST['showcell'])
	   echo "<tr><td><b>".i18n('Cell:')."</b> </td><td><input type='text' class='edit' style='width:200px' id='cell' value=\""
		.$_REQUEST['cell']."\" maxlength='14'/></td></tr>";

	 ?>

	 </table>
	 <div style="margin-top:20px;border-top:1px solid #a4caee;padding-top:10px;">
	  <input type='button' class='button-blue' value="<?php echo i18n('OK'); ?>" onclick='widget_default_submit()'/> 
	  <input type='button' class='button-gray' value="<?php echo i18n('Abort'); ?>" onclick='widget_default_close()'/>
	 </div>
	 </td></tr>
 </table>
</div>

<script>
var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; 
var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";
var AGENT_ID = <?php echo $_AGENT_ID; ?>;

function bodyOnLoad()
{
 window.setTimeout(function(){document.getElementById('name').focus();},700);
}

function widget_default_submit()
{
 var _name = document.getElementById('name').value;
 if(!_name)
 {
  alert("<?php echo i18n('You must specify a valid name'); ?>");
  return;
 }
 var _cat = document.getElementById('cat').value;
 
 var extset = "";
 // Contacts
 var email = document.getElementById('email'); 	if(email) 	extset+= ",email='"+email.value+"'";
 var phone = document.getElementById('phone'); 	if(phone) 	extset+= ",phone='"+phone.value+"'";
 var cell = document.getElementById('cell');	if(cell)	extset+= ",cell='"+cell.value+"'";
 if(extset != "") extset = "contacts.label='Sede',name='''"+_name+"'''"+extset;

 // Rubrica info
 if(AGENT_ID) extset+= ((extset != "") ? "," : "")+"rubricainfo.agent='"+AGENT_ID+"'";

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 gframe_close(o,a);
	}
 sh.sendCommand("dynarc new-item -ap `<?php echo $_REQUEST['ap'] ? $_REQUEST['ap'] : 'rubrica'; ?>` -cat '"+_cat+"' -name `"+_name+"` -group rubrica"+((extset != "") ? " -extset `"+extset+"`" : ""));
 return false;
}

function widget_default_close()
{
 gframe_close();
}
</script>
</body></html>
<?php

