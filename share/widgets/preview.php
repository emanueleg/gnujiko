<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-11-2012
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Simply preview form.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO: Fare funzione stampa.
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";
include($_BASE_PATH."init/init1.php");
include($_BASE_PATH."var/objects/fckeditor/index.php");

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/preview/preview.css" type="text/css" />
<div class='preview-widget'>
 <table width='100%' cellspacing='4' cellpadding='0' border='0'>
 <tr><td class='preview-title' id='preview-title' onclick='rename(this)'><?php echo $_REQUEST['title'] ? $_REQUEST['title'] : "Preview"; ?></td></tr>
 <tr><td><textarea style="width:680px;height:570px" id="contents"><?php echo $_REQUEST['contents']; ?></textarea></td></tr>
 <tr><td>
	<span class='button'><a href='#' onclick='submit()'>Salva</a></span> 
	<!-- <span class='button'><a href='#'>Stampa</a></span> -->
	<span class='button'><a href='#' onclick='gframe_close()'>Chiudi</a></span>
	</td></tr>
 </table>
</div>

<script>

var sSkinPath = "<?php echo $_BASE_PATH; ?>../../var/objects/fckeditor/editor/skins/office2003/";
var oFCKeditor = null;
var editorIsLoaded=true;
var editorMode=0;

function bodyOnLoad()
{
 gframe_cachecontentsload("");
}

function widget_default_apply()
{
 var oEditor = FCKeditorAPI.GetInstance('contents');
 var contents = oEditor.GetXHTML(true);
 gframe_shotmessage(contents);
}

function rename(td)
{
 var tit = prompt("Rinomina",td.innerHTML);
 if(!tit) return;
 td.innerHTML = tit;
}

function submit()
{
 var oEditor = FCKeditorAPI.GetInstance('contents');
 var title = document.getElementById('preview-title').innerHTML;
 var contents = oEditor.GetXHTML(true);
 gframe_close(title,contents);
}

function gframe_cachecontentsload(contents)
{
 document.getElementById('contents').innerHTML = contents;

 oFCKeditor = new FCKeditor('contents') ;
 oFCKeditor.BasePath	= "<?php echo $_BASE_PATH; ?>var/objects/fckeditor/";
 oFCKeditor.Config['SkinPath'] = sSkinPath ;
 oFCKeditor.Config['PreloadImages'] =
				sSkinPath + 'images/toolbar.start.gif' + ';' +
				sSkinPath + 'images/toolbar.end.gif' + ';' +
				sSkinPath + 'images/toolbar.bg.gif' + ';' +
				sSkinPath + 'images/toolbar.buttonarrow.gif' ;
 oFCKeditor.Width = 680;
 oFCKeditor.Height = 536;
 oFCKeditor.ReplaceTextarea();
 
}
</script>
<?php
