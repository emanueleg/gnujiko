<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-07-2012
 #PACKAGE: gnujiko-accounts
 #DESCRIPTION: Login form
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SOFTWARE_NAME;

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
  global $_ABSOLUTE_URL, $_SOFTWARE_NAME;
  ?>
  <link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>include/forms/login.css" type="text/css" />

  <form id="gnujiko-login-form" action="<?php echo $_ABSOLUTE_URL.'accounts/LoginAuth.php?'.$args; ?>" method="POST" onsubmit="return(gnujiko_onLoginSubmit());">
   <div id="gnujiko-login-box">
	<div class="gnujiko-login-header">
	 <img src="<?php echo $_ABSOLUTE_URL; ?>include/forms/img/gnujiko-logo.png" style="float:left; vertical-align:top;margin-right:10px;"/>
	 <img src="<?php echo $_ABSOLUTE_URL; ?>include/forms/img/gnujiko-login.png" style="float:left; vertical-align:top;"/><br/>
	 <span class='small' style='float:left;line-height:0.5em;'><?php echo i18n("Make login"); ?></span>
	</div>

	<div class="gnujiko-login-contents">
	 <p>
	  <span class='small'><?php echo i18n("Username"); ?></span><br/>
	  <input type='text' class='roundedit' name="Username" id="Username" value="<?php echo $_REQUEST['username']; ?>" maxlength="18" />
	 </p>
	 <p>
	  <span class='small'><?php echo i18n("Password"); ?></span><br/>
	  <input type='password' class='roundedit' name="Password" id="Password" value="<?php echo $_REQUEST['username']; ?>" maxlength="18" />
	 </p>
     <?php
	 if($_REQUEST['err'] == "badlogin")
	 {
	  echo "<p style='width:180px;color:#f31903;font-size:12px;text-align:center;'>".i18n("The username or password you entered is incorrect")."</p>";
	 }
	 ?>
	 <div>
	  <input type="checkbox" name="StayConnected" id="StayConnected" <?php if($_COOKIE['stayconnected']) echo "value=\"yes\" checked=\"checked\""; ?>/> 
	  <span class='small'><?php echo i18n("Stay connected"); ?></span>
	  <input type="submit" class="submit" name="signIn" value="<?php echo i18n('Sign in'); ?>"/>
	 </div>
	</div>
   </div>
  <?php
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

