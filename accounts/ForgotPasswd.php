<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2010 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-11-2009
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Forgot password page
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../";

include($_BASE_PATH.'init/init1.php');
LoadLanguage("accounts-forgotpassword");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $_SOFTWARE_NAME; ?> Accounts - <?php echo i18n("forgot password"); ?></title></head>
<style type='text/css'>
body { font-family: arial,sans-serif; background-color: #fff; margin: 20; }
  .c { width: 4; height: 4; } 
  a:link { color: #00c; } 
  a:visited { color: #551a8b; }
  a:active { color: #f00; }
div.contents {
  font-size: 14px;
  font-family: Arial;
}
</style>
<body>
<?php echo i18n("Service unavailable"); ?>. <a href='#' onclick='history.go(-1)'><?php echo i18n("Return"); ?></a>
</body></html>

