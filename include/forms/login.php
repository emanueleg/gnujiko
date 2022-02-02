<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-02-2017
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Login form
 #VERSION: 2.4beta
 #CHANGELOG: 17-02-2017 : Maxlength password aumentato a 32.
			 03-10-2016 : Mobile integration.
			 05-02-2016 : Bug fix su cookies.
			 29-11-2014 : Bug fix.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SOFTWARE_NAME, $_DESKTOP_TEMPLATE;

class LoginForm
{
 function LoginForm()
 {
  global $_ABSOLUTE_URL;
  $this->ServiceName = "";
  $this->Arguments = array("continue"=>($_REQUEST['continue'] ? $_REQUEST['continue'] : $_ABSOLUTE_URL));
  $this->BackgroundColor = "#e5ffd5";
  $this->BorderColor = "#5aa02c";
 }

 function Paint()
 {
  global $_BASE_PATH, $_ABSOLUTE_URL, $_SOFTWARE_NAME, $_DESKTOP_TEMPLATE;
  if(($_COOKIE['gnujiko_ui_devtype'] == "phone") && file_exists($_BASE_PATH."include/forms/mobi_login.css"))
   echo "<link rel='stylesheet' href='".$_ABSOLUTE_URL."include/forms/mobi_login.css' type='text/css'/>";
  else if($_DESKTOP_TEMPLATE && file_exists($_BASE_PATH."include/forms/".$_DESKTOP_TEMPLATE."_login.css"))
   echo "<link rel='stylesheet' href='".$_ABSOLUTE_URL."include/forms/".$_DESKTOP_TEMPLATE."_login.css' type='text/css'/>";
  else
   echo "<link rel='stylesheet' href='".$_ABSOLUTE_URL."include/forms/login.css' type='text/css'/>";

  if($_REQUEST['username'])
   $_LOGIN_NAME = $_REQUEST['username'];
  else if(isset($_COOKIE['stayconnected']) && ($_COOKIE['stayconnected'] == 'on'))
   $_LOGIN_NAME = isset($_COOKIE['username']) ? $_COOKIE['username'] : "";
  else
   $_LOGIN_NAME = "";

  if($_REQUEST['password'])
   $_LOGIN_PASSWD = $_REQUEST['password'];
  else if(isset($_COOKIE['stayconnected']) && ($_COOKIE['stayconnected'] == 'on'))
   $_LOGIN_PASSWD = isset($_COOKIE['password']) ? $_COOKIE['password'] : "";
  else
   $_LOGIN_PASSWD = "";

  ?>
  <form id="gnujiko-login-form" action="<?php echo $_ABSOLUTE_URL.'accounts/LoginAuth.php?'; ?>" method="POST" onsubmit="return(gnujiko_onLoginSubmit());">
   <?php
   if(($_COOKIE['gnujiko_ui_devtype'] == "phone") && file_exists($_BASE_PATH."include/forms/mobi_login.php"))
	include($_BASE_PATH."include/forms/mobi_login.php");
   else if($_DESKTOP_TEMPLATE && file_exists($_BASE_PATH."include/forms/".$_DESKTOP_TEMPLATE."_login.php"))
    include($_BASE_PATH."include/forms/".$_DESKTOP_TEMPLATE."_login.php");
   else
   {
    ?>
   <div id="gnujiko-login-box">
	<div class="gnujiko-login-header">
	 <img src="<?php echo $_ABSOLUTE_URL; ?>include/forms/img/gnujiko-logo.png" style="float:left; vertical-align:top;margin-right:10px;"/>
	 <img src="<?php echo $_ABSOLUTE_URL; ?>include/forms/img/gnujiko-login.png" style="float:left; vertical-align:top;"/><br/>
	 <span class='small' style='float:left;line-height:0.5em;'><?php echo i18n("Make login"); ?></span>
	</div>

	<div class="gnujiko-login-contents">
	 <p>
	  <span class='small'><?php echo i18n("Username"); ?></span><br/>
	  <input type='text' class='roundedit' name="Username" id="Username" value="<?php echo $_LOGIN_NAME; ?>" maxlength="40"/>
	 </p>
	 <p>
	  <span class='small'><?php echo i18n("Password"); ?></span><br/>
	  <input type='password' class='roundedit' name="Password" id="Password" value="<?php echo $_LOGIN_PASSWD; ?>" maxlength="32"/>
	 </p>
     <?php
	 if($_REQUEST['err'] && $_REQUEST['errmsg'])
	 {
	  echo "<p style='width:180px;color:#f31903;font-size:12px;text-align:center;'>".i18n($_REQUEST['errmsg'])."</p>";
	 }
	 ?>
	 <div>
	  <input type="checkbox" name="StayConnected" id="StayConnected" <?php if(isset($_COOKIE['stayconnected']) && ($_COOKIE['stayconnected'] == 'on')) echo "value=\"yes\" checked=\"checked\""; ?>/> 
	  <span class='small'><?php echo i18n("Stay connected"); ?></span>
	  <input type="submit" class="submit" name="signIn" value="<?php echo i18n('Sign in'); ?>"/>
	 </div>
	</div>
   </div>
  <?php
  }
  while(list($k,$v) = each($this->Arguments))
   echo "<input type='hidden' name='".$k."' value='".urlencode($v)."'/>";
  ?>
  </form>

  <script>
  function bodyOnLoad()
  {
   document.getElementById('Username').focus();
  }

  function gnujiko_onLoginSubmit()
  {
   return true;
  }
  </script>
  <?php
 }
}

