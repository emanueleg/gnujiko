<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 08-01-2012
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Manage user account
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../";

include($_BASE_PATH.'init/init1.php');
include($_BASE_PATH.'include/session.php');
LoadLanguage("accounts-manage");
if(!isLogged())
{
 header("Location:Login.php");
 return;
}

if($_POST['action'] == "editaccount")
{
 $db = new AlpaDatabase();
 $db->RunQuery("UPDATE gnujiko_users SET fullname='".$_POST['fullname']."',email='".$_POST['email']."' WHERE id='".$_SESSION['UID']."'");
 $db->Close();
 $modifyOK = true;

 //--- UPDATE SESSION ---//
 global $_BASE_PATH, $_DATABASE_NAME;
 session_name("Gnujiko-$_DATABASE_NAME");
 session_start();
 $_SESSION['FULLNAME'] = $_POST['fullname'];
 $_SESSION['EMAIL'] = $_POST['email'];
 session_write_close();
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
	margin-left: 70px;
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
	background: #ffff99;
	font-family: Arial;
	font-size: 14px;
}
</style>
<?php
include($_BASE_PATH.'include/headings/default.php');
?>
<br/><br/><br/>
<form id='accountsform' method='POST'>
<input type='hidden' name='action' value='editaccount'/>
<table width='540' align='center' border='0'>
<tr><td valign='top' width='50%'><img src="<?php echo $_ABSOLUTE_URL; ?>accounts/images/gnujiko_logo.png"/></td>
	<td valign='middle'><img src="<?php echo $_ABSOLUTE_URL; ?>accounts/images/account_manager.png"/></td></tr>
<tr><td valign='top' colspan='2'>
	<?php
	if($modifyOK)
	 echo "<div class='message'>".i18n("The changes made ​​to your account have been successfully saved!")."</div>";
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
	<tr><td align='right' class='title'><?php echo i18n("User full name"); ?>: </td>
		<td align='left' class='value'><input type='text' size='24' name='fullname' value="<?php echo $_SESSION['FULLNAME']; ?>"/></td></tr>
	<tr><td>&nbsp;</td><td valign='top'><small>(<?php echo i18n("Enter your name and surname"); ?>)</small></td></tr>

	<tr><td align='right' class='title'><?php echo i18n("Username"); ?>: </td>
		<td align='left' class='value'><b><?php echo $_SESSION['UNAME']; ?></b></td>
		<td align='right'>&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td colspan='2' valign='top'><small>(<?php echo i18n("Asked at Login. It is not possible to change"); ?>)</small></td></tr>

	<tr><td align='right' class='title'><?php echo i18n("Email"); ?>: </td>
		<td align='left' class='value'><input type='text' size='24' name='email' value="<?php echo $_SESSION['EMAIL']; ?>"/></td></tr>
	<tr><td>&nbsp;</td><td valign='top'><small>(<?php echo i18n("Provide your contact email"); ?>)</small></td></tr>

	<tr><td colspan='2'><hr style='width:280px;' align='center'/></td></tr>
	<tr><td>&nbsp;</td><td valign='top'><a href='ChangePassword.php'><?php echo i18n("Change password"); ?></a></td></tr>
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
</body></html>

