<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2011 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-12-2011
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Login access
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../";

include($_BASE_PATH.'init/init1.php');
include($_BASE_PATH.'include/session.php');
LoadLanguage("accounts-login");

if(isLogged())
{
 header("Location:ManageAccount.php");
 return;
}
?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $_SOFTWARE_NAME; ?> <?php echo i18n("Accounts"); ?></title></head>
<link rel='shortcut icon' href='<?php echo $_BASE_PATH; ?>share/images/favicon.png' />

<body onload="bodyOnLoad()">

<div style="width:200px;height:250px;position:absolute;top:50%;left:50%;margin-top:-125;margin-left:-100px;">
<?php
 include($_BASE_PATH."include/forms/login.php");
 $loginForm = new LoginForm();
 $loginForm->Paint();
?>
</div>

</body></html>

