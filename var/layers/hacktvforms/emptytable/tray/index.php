<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 03-06-2013
 #PACKAGE: 
 #DESCRIPTION: 
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;

$_BASE_PATH = "../../../../../";

define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

$imgPath = $_ABSOLUTE_URL."var/layers/hacktvforms/emptytable/tray/img/";

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/emptytable/tray/tray.css" type="text/css" />
<script type="text/javascript" src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/emptytable/tray/tray.js"></script>
<script>
var HOME_PATH = "<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR'].'/'; ?>";
</script>

<div class="hacktvforms-emptytable-tray">
 <!-- <div class="hacktvforms-emptytable-tray-button" onclick="hacktvform_emptytable_printPreview(<?php echo $_REQUEST['layerid']; ?>)"><img src="<?php echo $imgPath; ?>print.png"/><br/>Stampa</div> -->
 <div class="hacktvforms-emptytable-tray-button" onclick="hacktvform_emptytable_excelImport(<?php echo $_REQUEST['layerid'].',\''.$_REQUEST['hacktvformid'].'\''; ?>)"><img src="<?php echo $imgPath; ?>excel.png"/><br/>Importa da Excel</div>
 <div class="hacktvforms-emptytable-tray-button" onclick="hacktvform_emptytable_excelExport(<?php echo $_REQUEST['layerid'].',\''.$_REQUEST['hacktvformid'].'\''; ?>)"><img src="<?php echo $imgPath; ?>excel.png"/><br/>Esporta in Excel</div>
 <div class="hacktvforms-emptytable-tray-button" onclick="hacktvform_emptytable_sendEmail(<?php echo $_REQUEST['layerid'].',\''.$_REQUEST['hacktvformid'].'\''; ?>)"><img src="<?php echo $imgPath; ?>sendmail.png"/><br/>Invia per email</div>
 <div class="hacktvforms-emptytable-tray-button" onclick="hacktvform_emptytable_runCommands(<?php echo $_REQUEST['layerid'].',\''.$_REQUEST['hacktvformid'].'\''; ?>)"><img src="<?php echo $imgPath; ?>gsh.png"/><br/>Lancia comandi</div>
 <!-- <div class="hacktvforms-emptytable-tray-button" onclick="hacktvform_emptytable_putonDesktop(<?php echo $_REQUEST['layerid'].',\''.$_REQUEST['hacktvformid'].'\''; ?>)"><img src="<?php echo $imgPath; ?>desktop.png"/><br/>Fissa sul desktop</div> -->
</div>

