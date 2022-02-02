<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2015 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-03-2015
 #PACKAGE: htmleditor
 #DESCRIPTION: HTML Editor
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";
include($_BASE_PATH."init/init1.php");
include($_BASE_PATH."var/objects/fckeditor/index.php");

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/htmleditor/htmleditor.css" type="text/css" />
<div class='htmleditor-widget'>
 <table width='100%' cellspacing='4' cellpadding='0' border='0'>
 <tr><td class='htmleditor-title'><?php echo $_REQUEST['title'] ? $_REQUEST['title'] : "Html-editor"; ?></td></tr>
 <tr><td><textarea style="width:680px;height:570px" id="contents"><?php echo $_REQUEST['contents']; ?></textarea></td></tr>
 <tr><td>
	<span class='button'><a href='#' onclick='submit()'>Salva</a></span> 
	<span class='button'><a href='#' onclick='gframe_close()'>Chiudi</a></span>
	</td></tr>
 </table>
</div>

<script>

var sSkinPath = "<?php echo $_BASE_PATH; ?>../../var/objects/fckeditor/editor/skins/office2003/";
var oFCKeditor = null;
var editorIsLoaded=true;
var editorMode=0;
var TOOLBAR_SET = "<?php echo $_REQUEST['editorstyle']; ?>";

function bodyOnLoad()
{
 gframe_cachecontentsload(document.getElementById('contents').innerHTML);
}

function widget_default_apply()
{
 var oEditor = FCKeditorAPI.GetInstance('contents');
 var contents = oEditor.GetXHTML(true);
 gframe_shotmessage(contents);
}

function submit()
{
 var oEditor = FCKeditorAPI.GetInstance('contents');
 var contents = oEditor.GetXHTML(true);
 gframe_close(null,contents);
}

function gframe_cachecontentsload(contents)
{
 document.getElementById('contents').innerHTML = contents;

 oFCKeditor = new FCKeditor('contents') ;
 oFCKeditor.ToolbarSet = TOOLBAR_SET ? TOOLBAR_SET : "Default";
 oFCKeditor.BasePath	= "<?php echo $_BASE_PATH; ?>var/objects/fckeditor/";
 oFCKeditor.Config['SkinPath'] = sSkinPath ;
 oFCKeditor.Config['PreloadImages'] =
				sSkinPath + 'images/toolbar.start.gif' + ';' +
				sSkinPath + 'images/toolbar.end.gif' + ';' +
				sSkinPath + 'images/toolbar.bg.gif' + ';' +
				sSkinPath + 'images/toolbar.buttonarrow.gif' ;
 oFCKeditor.Width = 680;
 oFCKeditor.Height = 570 - 34;
 oFCKeditor.ReplaceTextarea();
 
}
</script>
<?php
