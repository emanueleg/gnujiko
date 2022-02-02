<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 16-04-2013
 #PACKAGE: paymentmodes-config
 #DESCRIPTION: Payment modes configuration form
 #VERSION: 2.2
 #CHANGELOG: 16-04-2013 : Nuova gestione delle modalità di pagamento. 
			 13-01-2013 : Aggiunto 'type' (BB=Bonifico bancario, RB=RiBa, RD=Rimessa diretta)
 #TODO: Alcune frasi sono da fare il multi-lingua.
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("config-paymentmodes");

$sessInfo = sessionInfo($_REQUEST['sessid']);
if($sessInfo['uname'] != "root")
{
 $msg = "You must be root";
 ?>
 <script>
 function bodyOnLoad()
 {
  alert("<?php echo $msg; ?>");
  gframe_close();
 }
 </script>
 <?php
 return;
}
//----------------------------------------------------------------------------------------------------------------------//
?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Payment modes</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");

include($_BASE_PATH."var/objects/gextendedtable/index.php");

?>
<style type='text/css'>
span {
	font-family: Arial;
	font-size: 12px;
	color: #666666;
}

hr.separator {
	background: #cccccf;
	border: 0px;
	height: 1px;
}

table.configugman th {
	font-size: 12px;
	color: #666666;
	text-align: left;
	border-bottom: 1px solid #cccccf;
}

table.configugman td {
	font-size: 12px;
	color: #0169c9;
	border-bottom: 1px solid #cccccf;
}

ul.toolbar {
	margin: 0px;
	padding: 0px;
	list-style: none;
	padding-left: 20px;
	padding-top: 6px;
}

ul.toolbar li {
	float: left;
}

ul.toolbar li.item {
	float: left;
	height: 20px;
	background: #6699cc;
	cursor: pointer;
	font-family: Arial;
	font-size: 12px;
	color: #ffffff;
	margin-left: 1px;
	margin-right: 1px;
	margin-top: 1px;
	padding-left: 4px;
	padding-right: 4px;
	line-height: 1.5em;
	font-weight: bold;
}

ul.toolbar li.separator {
	background: transparent;
	width: 10px;
}

ul.toolbar li.roundleft-btn {
	background: #aaccee url(config/img/roundbtn_left.png) top left no-repeat;
	height: 22px;
	font-family: Arial;
	font-size: 12px;
	color: #ffffff;
	padding-left: 8px;
	padding-right: 8px;
	margin-right: 1px;
	margin-top: 0px;
}

ul.toolbar li.roundleft-btn a {
	font-family: Arial;
	font-size: 12px;
	color: #ffffff;
	text-decoration: none;
	line-height: 1.7em;
	font-weight: bold;
}

ul.toolbar li.roundleft-btn-active {
	background: #0169c9 url(config/img/roundbtn_left.png) top left no-repeat;
	height: 22px;
	font-family: Arial;
	font-size: 14px;
	color: #ffffff;
	padding-left: 8px;
	padding-right: 8px;
	margin-right: 1px;
	margin-top: 0px;
}

ul.toolbar li.roundleft-btn-active span {
	font-family: Arial;
	font-size: 14px;
	color: #ffffff;
	line-height: 1.5em;
	font-weight: bold;
}

ul.toolbar li.roundright-btn {
	background: #aaccee url(config/img/roundbtn_right.png) top right no-repeat;
	height: 22px;
	font-family: Arial;
	font-size: 12px;
	color: #ffffff;
	padding-left: 8px;
	padding-right: 8px;
	margin-top: 0px;
}

ul.toolbar li.roundright-btn a {
	font-family: Arial;
	font-size: 12px;
	color: #ffffff;
	text-decoration: none;
	line-height: 1.7em;
	font-weight: bold;
}

ul.toolbar li.roundright-btn-active {
	background: #0169c9 url(config/img/roundbtn_right.png) top right no-repeat;
	height: 22px;
	font-family: Arial;
	font-size: 14px;
	color: #ffffff;
	padding-left: 8px;
	padding-right: 8px;
	margin-top: 0px;
}

ul.toolbar li.roundright-btn-active span {
	font-family: Arial;
	font-size: 14px;
	color: #ffffff;
	line-height: 1.5em;
	font-weight: bold;
}

a.disabled {
	color: #f31903;
}

div#contents {
	background: url(config/img/edit-paymentmodes-bg.png) bottom left no-repeat;
	height: 372px;
}

ul.usermenu {
	margin: 0px;
	padding: 0px;
	list-style: none;
	padding-left: 15px;
	padding-top: 15px;
	width: 190px;
	float: left;
}

ul.usermenu li {
	height: 34px;
	background: transparent;
	padding-left: 12px;
	width: 174px;
	overflow: hidden;
	white-space: nowrap;
}

ul.usermenu li a {
	font-family: Arial;
	font-size: 14px;
	color: #013397;
	line-height: 2.3em;
}

ul.usermenu li.active {
	background: url(config/img/edit-paymentmode-tab.png) top left no-repeat;
}

ul.usermenu li.active a {
	font-family: Arial;
	font-size: 14px;
	color: #013397;
	font-weight: bold;
	text-decoration: none;
	line-height: 2.3em;
}

div.page {

}

h4 {
	font-size: 14px;
	margin-top: 12px;
	margin-bottom: 4px;
}

table.pmtable td {
	font-size: 12px;
	vertical-align: top;
	border-bottom: 1px solid #999999;
	padding-top: 4px;
	padding-bottom: 4px;
}

</style>
</head><body>
<?php
$form = new GForm(i18n("Payment modes configuration"), "MB_SAVE|MB_CLOSE", "simpleform", "default", "blue", 880, 480);
$form->Begin($_ABSOLUTE_URL."share/widgets/config/icons/paymentmodes-icon.png");
echo "<div id='contents'>";
?>
<table width='100%' cellspacing='0' cellpadding='0' border='0'>
<tr><td valign='top' style='background:transparent;width:218px;'>
<div style='height:370px;overflow:auto;overflow-x:hidden;'>
<ul class='usermenu'>
 <?php
 $ret = GShell("paymentmodes list");
 $list = $ret['outarr'];
 for($c=0; $c < count($list); $c++)
 {
  $item = $list[$c];
  echo "<li id='pmtab-".$item['id']."'";
  if(!$_REQUEST['id'] && ($c == 0))
  {
   echo " class='active'";
   $itm = $item;
  }
  else if($_REQUEST['id'] && ($item['id'] == $_REQUEST['id']))
  {
   echo " class='active'";
   $itm = $item;
  }
  echo "><a href='#' onclick='show(".$item['id'].")'>".$item['name']."</a></li>";
 }
 ?>
</ul>
</div>
</td><td valign='top'>

<div style='height:32px;'>
 <ul class='toolbar'>
  <li onclick='addItem()' class='item'><?php echo i18n("Add"); ?></li>
  <li onclick="deleteItem()" class='item' style='background:red;margin-left:4px;'><?php echo i18n("Delete"); ?></li>
 </ul>
</div>

<!-- PAGE -->
<div class='page' id='mainmenu-page' style="padding:12px;">
 <div style="border-top:1px solid #666666;border-bottom:1px solid #333333;padding:4px;font-size:12px;margin-bottom:12px;">
  <b><?php echo i18n('Title:'); ?> </b> <input type='text' size='40' id='title' value="<?php echo $itm['name']; ?>"/> &nbsp;
  <select id='type'><?php
   $types = array('RD'=>'Rimessa diretta', 'BB'=>'Bonifico Bancario', 'RB'=>'Ri.Ba.');
   while(list($k,$v) = each($types))
	echo "<option value='".$k."'".($itm['type'] == $k ? " selected='selected'>" : ">").$v."</option>";
   echo "<option value=''".(!$itm['type'] ? " selected='selected'" : "").">altro</option>";
   ?>
  </select>
 </div>

 <div style="border-bottom:1px solid #666666;padding:4px;font-size:12px;margin-bottom:12px;">
  <b>Scadenze: </b> <input type='text' size='10' id='terms' value="<?php echo $itm['terms']; ?>"/> &nbsp;
  <small><i>elenca le scadenze separate da una (,) virgola. Es: 30,60,90</i></small>
 </div>
 
 <div style="border-bottom:1px solid #666666;padding:4px;font-size:12px;margin-bottom:12px;">
  <b>Data inizio scadenze: </b> 
	<input type='radio' name='date_terms' id='date_terms_1' <?php if(!$itm['date_terms']) echo "checked='true'"; ?>/>data fattura &nbsp;
	<input type='radio' name='date_terms' id='date_terms_2' <?php if($itm['date_terms']) echo "checked='true'"; ?>/>fine mese
 </div>

 <div style="border-bottom:1px solid #666666;padding:4px;font-size:12px;margin-bottom:12px;">
  <b>Giorno fisso scadenza: </b> <input type='text' size='10' id='day_after' value="<?php echo $itm['day_after']; ?>"/>
 </div>

</div>
</td></tr>
</table>
<?php
echo "</div>";

$form->End();

?>
<script>
var ITEM_ID = <?php echo $itm['id'] ? $itm['id'] : 0; ?>;

function bodyOnLoad()
{
 document.getElementById('title').onchange = function(){
	 if(!this.value)
	  return;
	 /* RICAVA AUTOMATICAMENTE I PARAMETRI IN BASE AL TITOLO */
	 var sh = new GShell();
	 sh.OnOutput = function(o,a){
		 if(!a) return;
		 document.getElementById('type').value = a['type'];
		 document.getElementById('terms').value = a['termstring'];
		 if(a['date_terms'] != "0")
		  document.getElementById('date_terms_2').checked = true;
		 else
		  document.getElementById('date_terms_1').checked = true;
		 document.getElementById('day_after').value = (a['day_after'] != "0") ? a['day_after'] : "";
		}
	 sh.sendCommand("accounting paymentmodeinfo `"+this.value+"`");
	}
}

function show(id)
{
 if(id == ITEM_ID)
  return;
 document.getElementById("pmtab-"+ITEM_ID).className = "";
 ITEM_ID = id;
 document.getElementById("pmtab-"+id).className = "active";

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 document.getElementById('title').value = a['name'];
	 document.getElementById('type').value = a['type'];
	 document.getElementById('terms').value = a['terms'];
	 if(a['date_terms'] != "0")
	  document.getElementById('date_terms_2').checked = true;
	 else
	  document.getElementById('date_terms_1').checked = true;
	 document.getElementById('day_after').value = (a['day_after'] != "0") ? a['day_after'] : "";
	}
 sh.sendCommand("paymentmodes info -id "+id);
}

function OnFormSubmit()
{
 var title = document.getElementById('title').value;
 if(!title)
 {
  alert("<?php echo i18n('You must enter a valid title.'); ?>");
  document.getElementById('title').focus();
  return false;
 }
 var type = document.getElementById('type').value;
 var terms = document.getElementById('terms').value;
 var date_terms = document.getElementById('date_terms_2').checked ? 1 : 0;
 var day_after = document.getElementById('day_after').value;

 var q = " -name `"+title+"` -type `"+type+"` -terms `"+terms+"` -dateterms '"+date_terms+"' -dayafter '"+day_after+"'";

 var sh = new GShell();
 if(ITEM_ID)
 {
  sh.OnOutput = function(o,a){alert("<?php echo i18n('Payment successfully saved!'); ?>"); pageReload();}
  sh.sendCommand("paymentmodes edit -id `"+ITEM_ID+"`"+q);
 }
 else
 {
  sh.OnOutput = function(o,a){pageReload(a['id']);}
  sh.sendCommand("paymentmodes new"+q);
 }
 
 return false;
}

function pageReload(id)
{
 if(!id)
  var id = ITEM_ID;
 var href = document.location.href.replace('#','');
 if(href.indexOf("&id=") > 0)
  document.location.href = href.replace("&id=<?php echo $_REQUEST['id']; ?>","&id="+id);
 else
  document.location.href = href+"&id="+id;
}

function addItem()
{
 document.getElementById("pmtab-"+ITEM_ID).className = "";
 ITEM_ID = 0;
 document.getElementById('title').value = "";
 document.getElementById('title').focus();
}

function deleteItem()
{
 if(!ITEM_ID)
  return;
 if(!confirm("<?php echo i18n('Are you sure you want to delete this payment method?'); ?>"))
  return;
 var sh = new GShell();
 sh.OnOutput = function(){ITEM_ID = 0; pageReload();}
 sh.sendCommand("paymentmodes delete -id `"+ITEM_ID+"`");
}

</script>
</body></html>
<?php

