<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-02-2013
 #PACKAGE: makedist
 #DESCRIPTION: Database import.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
//-------------------------------------------------------------------------------------------------------------------//
installer_begin(i18n("Installation completed"), "<b>".i18n("completed")."</b>");

installer_startContents();

?>
<div style="font-family:Arial;font-size:13px;padding-bottom:10px;color:#005c94;"><i><?php echo i18n("Installation Complete!"); ?></i></div>
<hr/>
<div style="font-family:Arial;font-size:18px;text-align:center;padding-top:50px;coolor:#005c94;"><?php echo i18n("The installation was completed! <br/> You can close this window"); ?></div>

<?php
installer_endContents();
?>
<div class="footer">
 <a href="<?php echo $_ABSOLUTE_URL; ?>" id='submit-button' class='right-button'><span><?php echo i18n("Finish"); ?> &raquo;</span></a>
</div>
<?php
installer_end();

