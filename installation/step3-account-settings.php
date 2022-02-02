<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-04-2013
 #PACKAGE: makedist
 #DESCRIPTION: Account settings form.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_LANGUAGE, $_ABSOLUTE_URL;
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
installer_begin(i18n("Install &raquo; Account settings"), sprintf(i18n("step <b>%d</b> of <b>%d</b>"),3,4));
?>
<style type='text/css'>
div.contents {background: url(img/account.png) right 30px no-repeat;}
table.form td {
	font-family:Arial;
	font-size:13px;
	color#000000;
	padding-bottom: 10px;
}

</style>
<?php
installer_startContents();

?>
<form action="index.php" method="POST" id='mainform'>
<input type='hidden' name='step' value="4"/>
<input type='hidden' name='lang' value="<?php echo $_REQUEST['lang']; ?>"/>
<input type='hidden' name='database-host' value="<?php echo $_REQUEST['database-host']; ?>"/>
<input type='hidden' name='database-name' value="<?php echo $_REQUEST['database-name']; ?>"/>
<input type='hidden' name='database-user' value="<?php echo $_REQUEST['database-user']; ?>"/>
<input type='hidden' name='database-passwd' value="<?php echo $_REQUEST['database-passwd']; ?>"/>
<input type='hidden' name='ftp-server' value="<?php echo $_REQUEST['ftp-server']; ?>"/>
<input type='hidden' name='ftp-path' value="<?php echo $_REQUEST['ftp-path']; ?>"/>
<input type='hidden' name='ftp-user' value="<?php echo $_REQUEST['ftp-user']; ?>"/>
<input type='hidden' name='ftp-passwd' value="<?php echo $_REQUEST['ftp-passwd']; ?>"/>
<input type='hidden' name='def-file-perms' value="<?php echo $_REQUEST['def-file-perms']; ?>"/>

<div style="font-family:Arial;font-size:13px;text-align:center;padding-bottom:10px;color:#005c94;"><i><?php echo i18n("Now you must choose a password for the administrator (root) and enter a name and a password to assign to the first user."); ?></i></div>
<hr/>

<table class='form' width='100%' cellspacing='0' cellpadding='0' border='0'>
<tr><td valign='middle' width='180'><b>ROOT PASSWORD:</b></td>
	<td valign='top'><span class='smallgray'><?php echo i18n("Password for the administrator"); ?></span><br/>
		<div class='edit'><input type='password' class='text' name='root-password' id='root-password' value=""/></div>
	</td>
	<td valign='top'><span class='smallgray'><?php echo i18n("Retype password"); ?></span><br/>
		<div class='edit'><input type='password' class='text' id='root-password-retype' value=""/></div>
	</td></tr>

<tr><td colspan='3'><hr/></td></tr>

<tr><td valign='middle' width='180'><b>1ST USER NAME:</b></td>
	<td valign='top' colspan='2'><span class='smallgray'><?php echo i18n("Enter a name for the primary user"); ?></span><br/>
		<div class='edit'><input type='text' class='text' name='primary-user' id='primary-user' value=""/></div>
	</td></tr>

<tr><td valign='middle'><b>1ST USER PASSWORD:</b></td>
	<td valign='top'><span class='smallgray'><?php echo i18n("Password for the primary user"); ?></span><br/>
		<div class='edit'><input type='password' class='text' name='primary-password' id='primary-password' value=""/></div>
	</td>
	<td valign='top'><span class='smallgray'><?php echo i18n("Retype password"); ?></span><br/>
		<div class='edit'><input type='password' class='text' id='primary-password-retype' value=""/></div>
	</td></tr>
</table>
</form>
<?php
installer_endContents();
?>
<div class="footer">
 <a href='#' id='submit-button' class='right-button' onclick='submit()'><span><?php echo i18n("Next"); ?> &raquo;</span></a>
</div>

<script>
function submit()
{
 var rootPasswd = document.getElementById('root-password').value;
 var rootPasswdR =  document.getElementById('root-password-retype').value;

 var firstUserName = document.getElementById('primary-user').value;
 var firstUserPasswd = document.getElementById('primary-password').value;
 var firstUserPasswdR = document.getElementById('primary-password-retype').value;

 if(!rootPasswd)
 {
  alert("<?php echo i18n('You must enter a valid password for the administrator'); ?>");
  document.getElementById('root-password').focus();
  return;
 }

 if(rootPasswd != rootPasswdR)
 {
  alert("<?php echo i18n('The administrator passwords do not match.'); ?>");
  document.getElementById('root-password').focus();
  document.getElementById('root-password').select();
  return;
 }

 if(!firstUserName)
 {
  alert("<?php echo i18n('You must specify a valid name for the first user'); ?>");
  document.getElementById('primary-user').focus();
  return;
 }

 if(!firstUserPasswd)
 {
  alert("<?php echo i18n('You must enter a valid password for the first user'); ?>");
  document.getElementById('primary-password').focus();
  return;
 }

 if(firstUserPasswd != firstUserPasswdR)
 {
  alert("<?php echo i18n('The user passwords do not match.'); ?>");
  document.getElementById('primary-password').focus();
  document.getElementById('primary-password').select();
  return;
 }

 document.getElementById('mainform').submit();
}
</script>
<?php
installer_end();

