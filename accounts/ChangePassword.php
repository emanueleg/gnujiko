<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 08-01-2012
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION:
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../";

include($_BASE_PATH.'init/init1.php');
include($_BASE_PATH.'include/session.php');

LoadLanguage("accounts-changepassword");

if(!isLogged())
{
 header("Location:Login.php");
 return;
}

if($_POST['action'] == "changepassword")
{
 $oldPassword = mysql_escape_string(trim($_POST['oldpasswd']));
 $newPassword = mysql_escape_string(trim($_POST['newpasswd']));
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT password,regtime FROM gnujiko_users WHERE id='".$_SESSION['UID']."'");
 $db->Read();
 $cryptpass = md5($oldPassword.$db->record['regtime']);
 if($db->record['password'] != $cryptpass)
 {
  $modifyOK = false;
  $error = "OLD_PASSWORD_WRONG";
  $errorMsg = i18n("The old password is wrong");
 }
 else
 {
  $cryptpass = md5($newPassword.$db->record['regtime']);
  $db->RunQuery("UPDATE gnujiko_users SET password='$cryptpass' WHERE id='".$_SESSION['UID']."'");
  $modifyOK = true;
 }
 $db->Close();
}


?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $_SOFTWARE_NAME; ?> - <?php echo i18n("ACCOUNT MANAGER"); ?></title></head>
<link rel='shortcut icon' href='share/images/favicon.png' />
<body>
<style type='text/css'>
div.accountbox {
	background: url(images/background.png) top left no-repeat;
	width: 500px;
	height: 240px;
	margin: 20px;
	padding: 10px;
	margin-top: 40px;
}
table.accountboxtable {
	margin-left: 90px;
}

table.accountboxtable td {
	font-family: Arial;
	font-size: 12px;
}

table.accountboxtable td.title {
	font-family: Verdana;
	font-size: 12px;
}
table.accountboxtable td.value {
	font-family: Arial;
	font-size: 12px;
}
div.message {
	margin-top:10px;
	background: #ffff99;
	font-family: Arial;
	font-size: 14px;
}
div.error {
	padding:4px;
	margin-top:10px;
	background: #f31903;
	font-family: Arial;
	font-size: 14px;
	color: #ffffff;
	font-weight: bold;
}
</style>
<?php
include($_BASE_PATH.'include/headings/default.php');
?>
<br/><br/><br/>
<form id='accountsform' method='POST' onsubmit="return _changePasswdSubmit();">
<input type='hidden' name='action' value='changepassword'/>
<table width='540' align='center' border='0'>
<tr><td valign='top' width='50%'><img src="<?php echo $_ABSOLUTE_URL; ?>accounts/images/gnujiko_logo.png"/></td>
	<td valign='middle'><img src="<?php echo $_ABSOLUTE_URL; ?>accounts/images/account_manager.png"/></td></tr>
<tr><td valign='top' colspan='2'>
	<?php
	if($modifyOK)
	 echo "<div class='message'>".i18n("The password has been updated!")."</div>";
	else if($error)
	 echo "<div class='error'>".i18n("Error").": $errorMsg</div>";

	/* Check privileges for edit account info */
	$db = new AlpaDatabase();
	$db->RunQuery("SELECT * FROM gnujiko_user_privileges WHERE uid='".$_SESSION['UID']."'");
	$db->Read();
	if(!$db->record['edit_account_info'])
	{
	 $db->Close();
	 ?>
	 <div class='accountbox'>
	  <div style='margin-left:100px;'>
	   <h3 style='color:#f31903;'><?php echo i18n("You don't have the correct permission to access to this account information"); ?></h3>
	   <input type='button' value="<?php echo i18n('Abort'); ?>" onclick='history.go(-1)'/>
	  </div>
	 </div>
	 <?php
	}
	else
	{
	?>
	<div class='accountbox'>
	<table border='0' class='accountboxtable' border='0'>
	<tr><td align='right' class='title'><?php echo i18n("Old password"); ?>: </td>
		<td align='left' class='value'><input type='password' size='24' name='oldpasswd' id ='oldpasswd' value=""/></td></tr>
	<tr><td>&nbsp;</td><td valign='top'><small>(<?php echo i18n("Type your old password"); ?>)</small></td></tr>

	<tr><td align='right' class='title'><?php echo i18n("New password"); ?>: </td>
		<td align='left' class='value'><input type='password' size='24' name='newpasswd' id='newpasswd' value=""/></td></tr>

	<tr><td align='right' class='title'><?php echo i18n("Retype password"); ?>: </td>
		<td align='left' class='value'><input type='password' size='24' name='passwdrt' id='passwdrt' value=""/></td></tr>

	<tr><td colspan='2'><hr style='width:280px;' align='center'/></td></tr>
	<tr><td>&nbsp;</td><td valign='top' align='right'><br/><input type='button' value="<?php echo i18n('Abort'); ?>" onclick='history.go(-1)'/> <input type='submit' value="<?php echo i18n('Save'); ?>"/></td></tr>
	</table>
	</div>
	<?php
	}
	?>
	</td></tr>
</table>
</form>
<?php
include($_BASE_PATH.'include/footers/default.php');
?>
<script>
function _changePasswdSubmit()
{
 if(!document.getElementById('oldpasswd').value)
 {
  alert("<?php echo i18n('You must type your old password'); ?>");
  return false;
 }
 if(!document.getElementById('newpasswd').value)
 {
  alert("<?php echo i18n('You must type the new password'); ?>");
  return false;
 }
 if(document.getElementById('newpasswd').value == document.getElementById('oldpasswd').value)
 {
  alert("<?php echo i18n('The new password must be different from the old'); ?>");
  return false;
 }
 if(document.getElementById('newpasswd').value != document.getElementById('passwdrt').value)
 {
  alert("<?php echo i18n('The new password and the retyped password not match'); ?>");
  return false;
 }
 return true;
}
</script>
</body></html>

