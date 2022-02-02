<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-11-2016
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Login access
 #VERSION: 2.1beta
 #CHANGELOG: 13-11-2016 : If is already logged, goto home page.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../";

include($_BASE_PATH.'init/init1.php');
include($_BASE_PATH.'include/session.php');
LoadLanguage("accounts-login");

if(isLogged())
{
 //header("Location:ManageAccount.php");
 header("Location:".$_BASE_PATH);
 return;
}


if(($_COOKIE['gnujiko_ui_devtype'] == "phone") && file_exists($_BASE_PATH."include/forms/mobi_login.php"))
{
 /* PHONE */
 ?>
 <html>
  <head>
   <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
   <meta name="HandheldFriendly" content="true"/>
   <meta name="format-detection" content="telephone=no"/>
   <meta name="viewport" content="width=device-width, height=device-height, user-scalable=0, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0"/>
   <meta name="mobile-web-app-capable" content="yes"/>
   <title><?php echo $_SOFTWARE_NAME; ?> <?php echo i18n("Accounts"); ?></title>
  </head>
  <link rel="shortcut icon" href="<?php echo $_BASE_PATH; ?>share/images/favicon.png"/>
  <body onload="bodyOnLoad()">
    <?php
    include($_BASE_PATH."include/forms/login.php");
    $loginForm = new LoginForm();
    $loginForm->Paint();
    ?>
  </body>
 </html>
 <?php
}
else
{
 /* COMPUTER */
 ?>
 <html>
  <head>
   <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
   <title><?php echo $_SOFTWARE_NAME; ?> <?php echo i18n("Accounts"); ?></title>
  </head>
  <link rel="shortcut icon" href="<?php echo $_BASE_PATH; ?>share/images/favicon.png"/>
  <body onload="bodyOnLoad()">
   <div style="width:200px;height:250px;position:absolute;top:50%;left:50%;margin-top:-125;margin-left:-100px;">
    <?php
    include($_BASE_PATH."include/forms/login.php");
    $loginForm = new LoginForm();
    $loginForm->Paint();
    ?>
   </div>
  </body>
 </html>
 <?php
}

