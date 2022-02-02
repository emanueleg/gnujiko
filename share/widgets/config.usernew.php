<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-04-2013
 #PACKAGE: system-config-gui
 #DESCRIPTION: User new form
 #VERSION: 2.3beta
 #CHANGELOG: 30-04-2013 : Alcuni bug fix.
			 03-02-2013 : Ora è possibile assegnare subito il nuovo utente a più gruppi.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");
include_once($_BASE_PATH."include/userfunc.php");

LoadLanguage("config-usergroups");

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
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>New User</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
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

div#contents {
	background: url(config/img/edit-user-bg.png) bottom left no-repeat;
	height: 296px;
}

ul.usermenu {
	margin: 0px;
	padding: 0px;
	list-style: none;
	padding-left: 15px;
	padding-top: 15px;
	width: 146px;
	float: left;
}

ul.usermenu li {
	height: 34px;
	background: transparent;
	padding-left: 12px;
	width: 130px;
}

ul.usermenu li a {
	font-family: Arial;
	font-size: 14px;
	color: #013397;
	line-height: 2.3em;
}

ul.usermenu li.active {
	background: url(config/img/edit-user-tab.png) top left no-repeat;
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
	float: left;
}

h4 {
	font-size: 14px;
	margin-top: 12px;
	margin-bottom: 4px;
}
</style>
</head><body>
<?php
$form = new GForm(i18n("New user"), "MB_OK|MB_ABORT", "simpleform", "default", "blue", 480, 404);
$form->Begin($_ABSOLUTE_URL."share/widgets/config/icons/user-edit.png");
echo "<div id='contents'>";
?> 
<ul class='usermenu'>
 <li id='details-tab' class='active'><a href='#' onclick="showPage('details')"><?php echo i18n("Contact details"); ?></a></li>
 <li id='privilege-tab'><a href='#' onclick="showPage('privilege')"><?php echo i18n("Privileges"); ?></a></li>
 <li id='advanced-tab'><a href='#' onclick="showPage('advanced')"><?php echo i18n("Advanced"); ?></a></li>
</ul>
<!-- DETAILS PAGE -->
<div class='page' id='details-page'>
<h4><?php echo i18n("Username"); ?></h4>
<input type='text' id='username' size='20' value="" onchange="usernameChange(this)"/>
<h4><?php echo i18n("Password"); ?> &nbsp;&nbsp;&nbsp;<input type='checkbox' checked='true' id='enablepassword' onclick='enablepasswdChange(this)'/><small><?php echo i18n("enabled"); ?></small></h4>
<input type='text' id='password' size='20' value=""/>
<h4><?php echo i18n("Fullname"); ?></h4>
<input type='text' id='fullname' size='20' value=""/>
<h4><?php echo i18n("Email"); ?></h4>
<input type='text' id='email' size='20' value=""/>
</div>

<!-- PRIVILEGE PAGE -->
<div class='page' id='privilege-page' style='display:none;padding-top:12px;'>
 <input type='checkbox' id='mkdir_enable'/> <?php echo i18n("create folders"); ?><br/>
 <input type='checkbox' id='editaccount_enable' checked='true'/> <?php echo i18n("edit account info"); ?><br/>
 <input type='checkbox' id='run_sudo_commands'/> <?php echo i18n("run sudo commands"); ?><br/><br/>
 Accessi alle varie applicazioni<br/>
 <div style="height:170px;width:270px;overflow:auto;border-top:1px solid #dadada" id="applications_access">
  <?php

  if(_getGID("rubrica"))
   echo "<input type='checkbox' id='group_rubrica'/> ".i18n("Access to address book")."<br/>";
  if(_getGID("gmart"))
   echo "<input type='checkbox' id='group_gmart'/> ".i18n("Access to products")."<br/>";
  if(_getGID("gserv"))
   echo "<input type='checkbox' id='group_gserv'/> ".i18n("Access to services")."<br/>";
  if(_getGID("gsupplies"))
   echo "<input type='checkbox' id='group_gsupplies'/> ".i18n("Access to other supplies catalog")."<br/>";
  if(_getGID("gstore"))
   echo "<input type='checkbox' id='group_gstore'/> ".i18n("Access to store manager")."<br/>";
  if(_getGID("bookkeeping"))
   echo "<input type='checkbox' id='group_bookkeeping'/> ".i18n("Access to book keeping")."<br/>";
  if(_getGID("pettycashbook"))
   echo "<input type='checkbox' id='group_pettycashbook'/> ".i18n("Access to petty cash book")."<br/>";
  if(_getGID("printmodels"))
   echo "<input type='checkbox' id='group_printmodels'/> ".i18n("Printing documents")."<br/>";
  if(_getGID("idoc"))
   echo "<input type='checkbox' id='group_idoc'/> ".i18n("Access to technical docs")."<br/>";
  if(_getGID("dynarc"))
   echo "<input type='checkbox' id='group_dynarc'/> ".i18n("Access to archives")."<br/>";

  if(_getGID("commercialdocs"))
   echo "<input type='checkbox' id='group_commercialdocs'/> ".i18n("Access to commercial documents")."<br/>";
  if(_getGID("commdocs-preemptives"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-preemptives'/> ".i18n("Create preemptives")."<br/>";
  if(_getGID("commdocs-orders"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-orders'/> ".i18n("Create orders")."<br/>";
  if(_getGID("commdocs-ddt"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-ddt'/> ".i18n("Create transport documents")."<br/>";
  if(_getGID("commdocs-invoices"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-invoices'/> ".i18n("Create invoices")."<br/>";
  if(_getGID("commdocs-creditsnote"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-creditsnote'/> ".i18n("Create credits note")."<br/>";
  if(_getGID("commdocs-debitsnote"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-debitsnote'/> ".i18n("Create debits note")."<br/>";
  if(_getGID("commdocs-purchaseinvoices"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-purchaseinvoices'/> ".i18n("Create purchase invoices")."<br/>";
  if(_getGID("commdocs-paymentnotice"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-paymentnotice'/> ".i18n("Create payments notice")."<br/>";
  if(_getGID("commdocs-vendororders"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-vendororders'/> ".i18n("Create vendor orders")."<br/>";
  if(_getGID("commdocs-agentinvoices"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-agentinvoices'/> ".i18n("Create agent invoices")."<br/>";
  if(_getGID("commdocs-intervreports"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-intervreports'/> ".i18n("Create intervent reports")."<br/>";
  ?>
 </div>
</div>

<!-- ADVANCED PAGE -->
<div class='page' id='advanced-page' style='display:none;padding-top:12px;'>
<input type='checkbox' id='enableshell'/><?php echo i18n("Enable shell"); ?><br/>
<hr class='separator'/>
<p><?php echo i18n("Main group"); ?> <select id='maingroup' style="width:150px">
	<option value='0' selected='selected' id='defaultgroup'>&nbsp;</option>
	 <?php
	 $ret = GShell("groups --orderby name",$_REQUEST['sessid'], $_REQUEST['shellid']);
	 if(!$ret['error'])
	 {
	  for($c=0; $c < count($ret['outarr']); $c++)
	   echo "<option value='".$ret['outarr'][$c]['id']."'>".$ret['outarr'][$c]['name']."</option>";
	 }
	?></select>
</p>
<hr class='separator'/>
<p><?php echo i18n("Home directory"); ?> <input type='text' style='width:150px' id='homedir' value=""/><br/>
<input type='checkbox' id='nocreatehome' onchange='nocreatehomeChange(this)'/><?php echo i18n("do not create the home directory"); ?></span>
</p>
<hr class='separator'/>
<p><span style='float:left;'><?php echo i18n("User ID"); ?><br/>
<input type='radio' name='userid' checked='true' id='autouserid' onchange='autouseridChange()'/><?php echo i18n("automatic"); ?>
<input type='radio' name='userid' onchange='autouseridChange()'/><?php echo i18n("specify"); ?>: <input type='text' id='userid' size='3' value="" disabled='disabled'/></span></p>
</div>

<?php
echo "</div>";
$form->End();
?>
<script>
var ACTIVE_PAGE_NAME = "details";

function bodyOnLoad()
{
 window.setTimeout(function(){document.getElementById('username').focus();},500);
}

function showPage(pagename)
{
 if(pagename == ACTIVE_PAGE_NAME)
  return;
 document.getElementById(ACTIVE_PAGE_NAME+"-tab").className = "";
 document.getElementById(ACTIVE_PAGE_NAME+"-page").style.display='none';
 ACTIVE_PAGE_NAME = pagename;
 document.getElementById(ACTIVE_PAGE_NAME+"-tab").className = "active";
 document.getElementById(ACTIVE_PAGE_NAME+"-page").style.display='';
}

function usernameChange(ed)
{
 document.getElementById('defaultgroup').innerHTML = ed.value;
 document.getElementById('homedir').value = ed.value;
}

function nocreatehomeChange(cb)
{
 document.getElementById('homedir').disabled = cb.checked;
}

function autouseridChange()
{
 document.getElementById('userid').disabled = document.getElementById('autouserid').checked;
}

function enablepasswdChange(cb)
{
 document.getElementById('password').disabled = !cb.checked;
}

function OnFormSubmit()
{
 var UserName = document.getElementById('username').value;
 var Password = document.getElementById('password').value;
 var FullName = document.getElementById('fullname').value;
 var Email = document.getElementById('email').value;
 var shEnable = document.getElementById('enableshell').checked;
 var MainGroup = document.getElementById('maingroup').value;
 var HomeDir = document.getElementById('homedir').value;
 var UserID = document.getElementById('userid').value;

 /* Privileges */
 var mkdirEnable = document.getElementById('mkdir_enable').checked;
 var editaccountEnable = document.getElementById('editaccount_enable').checked;
 var runsudocommands = document.getElementById('run_sudo_commands').checked;

 /* Applications access */
 var div = document.getElementById('applications_access');
 var list = div.getElementsByTagName('INPUT');
 var aaQ = "";
 for(var c=0; c < list.length; c++)
 {
  if(list[c].checked == true)
   aaQ+= ","+list[c].id.substr(6);
 }

 if(!UserName)
 {
  alert("<?php echo i18n('You must specify the username'); ?>");
  return false;
 }

 var sh = new GShell();
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 sh.OnError = function(msg,s){alert(msg);}
 var cmd = "useradd `"+UserName+"`";
 if(UserID && !document.getElementById('autouserid').checked)
  cmd+= " -uid "+UserID;
 if(MainGroup && (MainGroup != "0")) cmd+= " -gid "+MainGroup;
 if(document.getElementById('enablepassword').checked)
  cmd+= " -password `"+Password+"`";
 else
  cmd+= " --disabled-password";
 if(FullName) cmd+= " -fullname `"+FullName+"`";
 if(Email) cmd+= " -email `"+Email+"`";
 if(shEnable) cmd+= " --enable-shell";
 if(document.getElementById('nocreatehome').checked)
  cmd+= " --no-create-home";
 else if(HomeDir)
  cmd+= " -home `"+HomeDir+"`";

 cmd+= " -privileges `mkdir_enable="+(mkdirEnable ? 1 : 0)+",edit_account_info="+(editaccountEnable ? 1 : 0)+",run_sudo_commands="+(runsudocommands ? 1 : 0)+"`";

 if(aaQ != "")
  cmd+= " --insert-into-groups `"+aaQ.substr(1)+"`";

 sh.sendCommand(cmd);
 return false;
}
</script>
</body></html>
<?php

