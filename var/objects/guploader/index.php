<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 14-11-2012
 #PACKAGE: guploader
 #DESCRIPTION: Gnujiko uploader utility
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH,$_ABSOLUTE_URL;

?>
<script language='JavaScript' src="<?php echo $_ABSOLUTE_URL; ?>var/objects/guploader/guploader.js" type='text/javascript'></script>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/objects/guploader/guploader.css" type="text/css" />
<?php
echo "<iframe class='GUploader_iframe' id='GUPLOADER_IFRAME' name='GUPLOADER_IFRAME' src='".$_ABSOLUTE_URL."var/objects/guploader/blank.html'></iframe>";

