<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-05-2013
 #PACKAGE: fileupload
 #DESCRIPTION: Form for upload from local pc.
 #VERSION: 2.3beta
 #CHANGELOG: 17-05-2013 : Aggiunto allow file types.
			 19-04-2013 : Ridimensionato ancora il bottone che con l'aggiornamento di Firefox sbordava di nuovo.
			 11-04-2013 : Il bottone sfoglia sbordava troppo. ridimensionato.
 #TODO:
 #DEPENDS: guploader
 
*/

include_once($_BASE_PATH."var/objects/guploader/index.php");

?>
<div class="fileupload-form-footer">&nbsp;&nbsp;&nbsp;<div id="GUPLOADER_SPACE"></div>
	<span class="button-blue" style="margin-left:10px;"><span id="upload-btn"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/fileupload/img/upload.png"/>Carica</span></span>
</div>

<script>
var UPLOADER = null;

function bodyOnLoad()
{
 gframe_resize(500,120);
 UPLOADER = new GUploader(document.getElementById('upload-btn'), null, <?php echo $_REQUEST['destpath'] ? "\"".$_REQUEST['destpath']."\"" : "null"; ?>, null, null, 23, "<?php echo $_REQUEST['allow']; ?>");
 document.getElementById('GUPLOADER_SPACE').appendChild(UPLOADER.O);

 UPLOADER.OnUpload = function(fileInfo){
	 var ret = new Array();
	 ret['mode'] = "UPLOAD";
	 ret['files'] = new Array();
	 ret['files'].push(fileInfo);
	 gframe_close("File uploaded: "+fileInfo['fullname'], ret);
	}

}

</script>
<?php

