<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-10-2014
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Gnujiko official shell
 #VERSION: 2.1beta
 #CHANGELOG: 24-10-2014 : Aggiunto CSS per GShell.
			 22-01-2012 : Aggiunto SESSION_ID come variabile javascript.
			 02-01-2012 : Fatto il multilingua
 #TODO:
 
*/

if(!defined("VALID-GNUJIKO"))
 return;

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/js/xrequest.php");
include_once($_BASE_PATH."include/i18n.php");
LoadLanguage("gshell");

?>
<script>
var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; 
var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";
var SESSION_ID = "<?php echo $_SESSION['SESSID']; ?>";
</script>
<script>
/* Load GShell Language */
var GSHLANG_ACCESS_AS_SU = "<?php echo i18n('Access as Super User'); ?>";
var GSHLANG_ENTER_PWD = "<?php echo i18n('You must enter the password of the Super User (root), which allows you to perform administrative actions.'); ?>";
var GSHLANG_PWD = "<?php echo i18n('Password'); ?>";
var GSHLANG_CANC = "<?php echo i18n('Cancel'); ?>";
var GSHLANG_OK = "<?php echo i18n('OK'); ?>";
</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/gshell.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>include/gshell.css" type="text/css" />
<?php

