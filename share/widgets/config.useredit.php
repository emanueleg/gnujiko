<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 27-03-2017
 #PACKAGE: system-config-gui
 #DESCRIPTION: User edit form
 #VERSION: 2.5beta
 #CHANGELOG: 27-03-2017 : Aggiunto accesso a commesse (CommGest)
			 25-02-2016 : Bug fix accesso al gruppo tickets.
			 07-10-2014 : Aggiunto ricevute fiscali e DDT fornitore.
			 03-10-2014 : Aggiunto tickets e memberinvoices sui privilegi.
			 10-04-2013 : Bug fix.
			 25-01-2012 : Possibilità di modificare gli attributi anche dell'utente root. Prima era disabilitata.
 #TODO: Aggiungere tutti gli altri gruppi mancanti (tickets, lottomatica, gmaterials, gparts, gproducts, ...)
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

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

$db = new AlpaDatabase();
$db->RunQuery("SELECT * FROM gnujiko_users WHERE id='".$_REQUEST['uid']."'");
$db->Read();
$userInfo = $db->record;
$db->RunQuery("SELECT * FROM gnujiko_user_privileges WHERE uid='".$_REQUEST['uid']."'");
$db->Read();
$userPrivileges = $db->record;
$db->Close();
//----------------------------------------------------------------------------------------------------------------------//
?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Edit User</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."include/userfunc.php");
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
	width: 260px;
}

h4 {
	font-size: 14px;
	margin-top: 12px;
	margin-bottom: 4px;
}
</style>
</head><body>
<?php
$form = new GForm(i18n("Edit user")." - ".$userInfo['username'], "MB_OK|MB_ABORT", "simpleform", "default", "blue", 480, 404);
$form->Begin($_ABSOLUTE_URL."share/widgets/config/icons/user-edit.png");
echo "<div id='contents'>";
?>
<ul class='usermenu'>
 <li id='details-tab' class='active'><a href='#' onclick="showPage('details')"><?php echo i18n("Contact details"); ?></a></li>
 <?php if($userInfo['username'] != "root")
 {
  ?>
 <li id='privilege-tab'><a href='#' onclick="showPage('privilege')"><?php echo i18n("Privileges"); ?></a></li>
 <li id='advanced-tab'><a href='#' onclick="showPage('advanced')"><?php echo i18n("Advanced"); ?></a></li>
  <?php
 }
 ?>
 <li><a href='#' onclick="changePassword()"><?php echo i18n("Change password"); ?></a></li>
 <?php
 if($userInfo['username'] != "root") { ?>
 <li id='remove-tab'><a href='#' onclick="showPage('remove')" style='color:#f31903;'><?php echo i18n("Delete account"); ?></a></li>
 <?php } ?>
</ul>

<div class='page' id='details-page'>
<h4><?php echo i18n("Fullname"); ?></h4>
<input type='text' id='fullname' size='20' value="<?php echo $userInfo['fullname']; ?>"/>
<h4><?php echo i18n("Email"); ?></h4>
<input type='text' id='email' size='20' value="<?php echo $userInfo['email']; ?>"/>
<p style='font-size:12px;margin-top:12px;'>
<?php echo i18n("Creation date"); ?>: <b><?php echo date('d/m/Y H:i',$userInfo['regtime']); ?></b>
<?php if($userInfo['last_time_access'])
{
 echo "<br/>".i18n("Last time access").": <b>".date('d/m/Y H:i',$userInfo['last_time_access'])."</b>";
}
?>
</p>
</div>

<?php
if($userInfo['username'] != "root")
{
 ?>
<div class='page' id='privilege-page' style='display:none;padding-top:12px;'>
 <input type='checkbox' id='mkdir_enable' <?php if($userPrivileges['mkdir_enable']) echo "checked='true'"; ?>/> <?php echo i18n("create folders"); ?><br/>
 <input type='checkbox' id='editaccount_enable' <?php if($userPrivileges['edit_account_info']) echo "checked='true'"; ?>/> <?php echo i18n("edit account info"); ?><br/>
 <input type='checkbox' id='run_sudo_commands' <?php if($userPrivileges['run_sudo_commands']) echo "checked='true'"; ?>/> <?php echo i18n("run sudo commands"); ?><br/><br/>
 Accessi alle varie applicazioni<br/>
 <div style="height:170px;width:270px;overflow:auto;border-top:1px solid #dadada" id="applications_access">
  <?php
  if(_getGID("rubrica"))
   echo "<input type='checkbox' id='group_rubrica'".(_userInGroup("rubrica",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Accedere alla rubrica")."<br/>";
  if(_getGID("gmart"))
   echo "<input type='checkbox' id='group_gmart'".(_userInGroup("gmart",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Accedere al catalogo prodotti")."<br/>";
  if(_getGID("gserv"))
   echo "<input type='checkbox' id='group_gserv'".(_userInGroup("gserv",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Accedere al catalogo servizi")."<br/>";
  if(_getGID("gsupplies"))
   echo "<input type='checkbox' id='group_gsupplies'".(_userInGroup("gsupplies",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Accedere al catalogo altre forniture")."<br/>";
  if(_getGID("gstore"))
   echo "<input type='checkbox' id='group_gstore'".(_userInGroup("gstore",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Accesso al magazzino")."<br/>";
  if(_getGID("bookkeeping"))
   echo "<input type='checkbox' id='group_bookkeeping'".(_userInGroup("bookkeeping",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Accesso alla contabilità")."<br/>";
  if(_getGID("pettycashbook"))
   echo "<input type='checkbox' id='group_pettycashbook'".(_userInGroup("pettycashbook",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Accesso al registro della Prima Nota")."<br/>";
  if(_getGID("printmodels"))
   echo "<input type='checkbox' id='group_printmodels'".(_userInGroup("printmodels",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Stampare documenti")."<br/>";
  if(_getGID("idoc"))
   echo "<input type='checkbox' id='group_idoc'".(_userInGroup("idoc",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Accesso alle schede tecniche")."<br/>";
  if(_getGID("dynarc"))
   echo "<input type='checkbox' id='group_dynarc'".(_userInGroup("dynarc",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Accesso agli archivi")."<br/>";
  if(_getGID("tickets"))
   echo "<input type='checkbox' id='group_tickets'".(_userInGroup("tickets",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Accesso ai ticket")."<br/>";
  if(_getGID("commgest"))
   echo "<input type='checkbox' id='group_commgest'".(_userInGroup("commgest",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Accesso alle commesse (CommGest)")."<br/>";

  if(_getGID("commercialdocs"))
   echo "<input type='checkbox' id='group_commercialdocs'".(_userInGroup("commercialdocs",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Accesso ai documenti commerciali")."<br/>";
  if(_getGID("commdocs-preemptives"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-preemptives'".(_userInGroup("commdocs-preemptives",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Creare preventivi")."<br/>";
  if(_getGID("commdocs-orders"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-orders'".(_userInGroup("commdocs-orders",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Creare ordini")."<br/>";
  if(_getGID("commdocs-ddt"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-ddt'".(_userInGroup("commdocs-ddt",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Creare documenti di trasporto")."<br/>";
  if(_getGID("commdocs-ddtin"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-ddtin'".(_userInGroup("commdocs-ddtin",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Registrare DDT Fornitore")."<br/>";
  if(_getGID("commdocs-invoices"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-invoices'".(_userInGroup("commdocs-invoices",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Creare fatture")."<br/>";
  if(_getGID("commdocs-receipts"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-receipts'".(_userInGroup("commdocs-receipts",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Creare ricevute fiscali")."<br/>";
  if(_getGID("commdocs-creditsnote"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-creditsnote'".(_userInGroup("commdocs-creditsnote",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Creare note di credito")."<br/>";
  if(_getGID("commdocs-debitsnote"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-debitsnote'".(_userInGroup("commdocs-debitsnote",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Creare note di debito")."<br/>";
  if(_getGID("commdocs-purchaseinvoices"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-purchaseinvoices'".(_userInGroup("commdocs-purchaseinvoices",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Registrare fatture d'acquisto")."<br/>";
  if(_getGID("commdocs-paymentnotice"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-paymentnotice'".(_userInGroup("commdocs-paymentnotice",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Creare avvisi di pagamento")."<br/>";
  if(_getGID("commdocs-vendororders"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-vendororders'".(_userInGroup("commdocs-vendororders",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Creare ordini fornitore")."<br/>";
  if(_getGID("commdocs-agentinvoices"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-agentinvoices'".(_userInGroup("commdocs-agentinvoices",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Creare fatture agente")."<br/>";
  if(_getGID("commdocs-memberinvoices"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-memberinvoices'".(_userInGroup("commdocs-memberinvoices",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Creare fatture socio")."<br/>";
  if(_getGID("commdocs-intervreports"))
   echo "&nbsp;&nbsp;<input type='checkbox' id='group_commdocs-intervreports'".(_userInGroup("commdocs-intervreports",$userInfo['id']) ? ' checked=\"true\"' : '')."/> ".i18n("Creare rapporti di intervento")."<br/>";
  ?>
 </div>
</div>

<div class='page' id='advanced-page' style='display:none;padding-top:12px;'>
<input type='checkbox' <?php if($userInfo['disabled']) echo "checked='true'"; ?> id='accountdisable'/><?php echo i18n("Disable account"); ?><br/>
<input type='checkbox' <?php if($userInfo['enableshell']) echo "checked='true'"; ?> id='enableshell'/><?php echo i18n("Enable shell"); ?><br/>
<p><?php echo i18n("Main group"); ?> <select id='maingroup'><?php
	 $ret = GShell("groups --orderby name",$_REQUEST['sessid'], $_REQUEST['shellid']);
	 if(!$ret['error'])
	 {
	  for($c=0; $c < count($ret['outarr']); $c++)
	   echo "<option value='".$ret['outarr'][$c]['id']."'".($ret['outarr'][$c]['id'] == $userInfo['group_id'] ? " selected='selected'>" : ">")
		.$ret['outarr'][$c]['name']."</option>";
	 }
	?></select>
</p>
<p><?php echo i18n("Home directory"); ?> <input type='text' size='10' id='homedir' value="<?php echo $userInfo['homedir']; ?>"/></p>
<p>
<?php
$db = new AlpaDatabase();
$db->RunQuery("SELECT session_id FROM gnujiko_session WHERE uid='".$userInfo['id']."' LIMIT 1");
if($db->Read())
 $editUIDdisabled = true;
$db->Close();
?>
<span style='float:left;'><?php echo i18n("User ID"); ?> <input type='text' size='3' value="<?php echo $userInfo['id']; ?>" <?php if($editUIDdisabled) echo "disabled='disabled'"; ?>/></span>
<?php
if($editUIDdisabled)
{
 ?>
 <span style='font-size:10px;width:150px;display:block;float:left;margin-left:12px;'><i><?php echo i18n('You can not change the user ID when it is connected.'); ?></i></span>
 <?php
}
?>
</p>
</div>

<div class='page' id='remove-page' style='display:none;padding-top:12px;'>
<h3 style='color:#f31903'><?php echo i18n('Are you sure you want to delete this account?'); ?></h3>
<p><input type='checkbox' checked='true' id='removehomedir'/><?php echo i18n('remove home dir'); ?></p>
<hr class='separator'/>
<p><input type='button' value="<?php echo i18n('Proceed'); ?>" onclick='removeAccount()'/></p>
</div>

<?php
}

echo "</div>";
$form->End();
?>
<script>
var ACTIVE_PAGE_NAME = "details";
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

function OnFormSubmit()
{
 var FullName = document.getElementById('fullname').value;
 var Email = document.getElementById('email').value;
 <?php
 if($userInfo['username'] != "root")
 {
  ?>
 var accDis = document.getElementById('accountdisable').checked;
 var shEnable = document.getElementById('enableshell').checked;
 var MainGroup = document.getElementById('maingroup').value;
 var HomeDir = document.getElementById('homedir').value;

 /* Privileges */
 var mkdirEnable = document.getElementById('mkdir_enable').checked;
 var editaccountEnable = document.getElementById('editaccount_enable').checked;
 var runsudocommands = document.getElementById('run_sudo_commands').checked;

 /* Applications access */
 var div = document.getElementById('applications_access');
 var list = div.getElementsByTagName('INPUT');
 var aaQ = "";
 var aarQ = "";
 for(var c=0; c < list.length; c++)
 {
  if(list[c].checked == true)
   aaQ+= ","+list[c].id.substr(6);
  else
   aarQ+= ","+list[c].id.substr(6);
 }

  <?php
 }
 ?>

 var sh = new GShell();
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 <?php
 if($userInfo['username'] == "root")
 {
  ?>
 sh.sendCommand("usermod `<?php echo $userInfo['username']; ?>` -fullname `"+FullName+"` -email `"+Email+"`");
  <?php
 }
 else
 {
  ?>
  var cmd = "usermod `<?php echo $userInfo['username']; ?>` -gid `"+MainGroup+"` -fullname `"+FullName+"` -email `"+Email+"`"+(shEnable ? " --enable-shell" : " --disable-shell")+(accDis ? " --disable-account" : " --enable-account")+" -home `"+HomeDir+"` -privileges `mkdir_enable="+(mkdirEnable ? 1 : 0)+",edit_account_info="+(editaccountEnable ? 1 : 0)+",run_sudo_commands="+(runsudocommands ? 1 : 0)+"`";
  if(aaQ != "")
   cmd+= " --insert-into-groups `"+aaQ.substr(1)+"`";
  if(aarQ != "")
   cmd+= " --remove-from-groups `"+aarQ.substr(1)+"`";

  sh.sendCommand(cmd);
  <?php
 }
 ?>
 return false;
}

function removeAccount()
{
 var msg = "<?php echo i18n('Are you sure you want to delete the account %s ?'); ?>";
 if(!confirm(msg.replace("%s","<?php echo $userInfo['username']; ?>")))
  return;
 var sh = new GShell();
 sh.OnOutput = function(){gframe_close();}
 sh.sendCommand("userdel <?php echo $userInfo['username']; ?>"+(document.getElementById('removehomedir').checked ? " -all" : ""));
}

function changePassword()
{
 var msg = "<?php echo i18n('Change password for %s'); ?>";
 var passwd = prompt(msg.replace("%s","<?php echo $userInfo['username']; ?>"));
 if(!passwd)
  return;
 var sh = new GShell();
 sh.OnOutput = function(o,a){alert("<?php echo i18n('Password has been changed'); ?>");}
 sh.sendCommand("usermod `<?php echo $userInfo['username']; ?>` -password `"+passwd+"`");
}
</script>
</body></html>
<?php

