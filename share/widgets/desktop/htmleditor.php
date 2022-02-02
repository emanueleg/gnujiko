<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 04-06-2013
 #PACKAGE: gnujiko-desktop-base
 #DESCRIPTION: HTML Editor
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_FCKEDITOR_DEFAULT_IMAGE_PATH;
$_BASE_PATH = "../../../";
include($_BASE_PATH."init/init1.php");

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

include_once($_BASE_PATH."var/objects/fckeditor/index.php");

if($_REQUEST['modid'])
{
 $ret = GShell("desktop module-info -id '".$_REQUEST['modid']."'",$_REQUEST['sessid'],$_REQUEST['shellid']);
 if(!$ret['error'])
  $moduleInfo = $ret['outarr'];
}

$_CONTENTS = $moduleInfo ? $moduleInfo['htmlcontents'] : $_REQUEST['contents'];
$_CSS = $moduleInfo ? $moduleInfo['css'] : $_REQUEST['css'];
$_JS = $moduleInfo ? $moduleInfo['javascript'] : $_REQUEST['js'];

$_CONTENTS = str_replace("{ABSOLUTE_URL}",$_ABSOLUTE_URL,$_CONTENTS);

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Gnujiko Desktop - HTML Editor</title>
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/desktop/css/templatedefault.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/desktop/css/htmleditor.css" type="text/css" />
<script>

var oFCKeditor = null;
var editorIsLoaded=true;
var editorMode=0;
</script>
</head><body>
<div class="default-widget" style="width:800px">
 <table class="editorheader" cellspacing="0" cellpadding="0" border="0" width="100%">
 <tr><td><?php echo $_REQUEST['title'] ? $_REQUEST['title'] : "WebPage Editor"; ?></td>
	 <td><ul class='roundmenu'>
		  <li class='selected' onclick='showPage("text",this)'>Testo</li>
		  <li onclick='showPage("css",this)'>CSS</li>
		  <li onclick='showPage("javascript",this)'>Javascript</li>
		 </ul></td>
	 <td align='right'><img onclick="gframe_close();" src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/desktop/img/templatedefault/widgetclose.png" class="default-widget-close"/></td></tr>
 </table>

 <div class="default-widget-page" id="page-text">
 <textarea style="width:800px;height:570px" id="contents"><?php echo $_CONTENTS; ?></textarea>
 </div>

 <div class="default-widget-page" id="page-css" style="display:none">
 <textarea style="width:800px;height:530px" id="css-editor"><?php echo $_CSS; ?></textarea>
 </div>

 <div class="default-widget-page" id="page-javascript" style="display:none">
 <textarea style="width:800px;height:530px" id="javascript-editor"><?php echo $_JS; ?></textarea>
 </div>

 <div class="default-widget-footer">
  <span class="left-button blue" onclick="submit()">Salva</span> 
  <span class="left-button gray" onclick="gframe_close()">Annulla</span> 
 </div>

</div>

<script>
var ACTIVE_PAGE = "text";
var AUTOSAVE = <?php echo ($_REQUEST['autosave'] == 'true') ? 'true' : 'false'; ?>;

function bodyOnLoad(extraParams)
{
 gframe_cachecontentsload(document.getElementById('contents').value);
 if(extraParams)
 {
  if(extraParams['css'])
   document.getElementById('css-editor').value = extraParams['css'];
  if(extraParams['js'])
   document.getElementById('javascript-editor').value = extraParams['js'];
 }
}

function submit()
{
 var oEditor = FCKeditorAPI.GetInstance('contents');
 var contents = oEditor.GetXHTML();
 var css = document.getElementById('css-editor').value;
 var js = document.getElementById('javascript-editor').value;

 var ret = new Array();
 ret['contents'] = contents;
 ret['css'] = css;
 ret['js'] = js;

 if(AUTOSAVE)
 {
  var sh = new GShell();
  sh.OnError = function(msg,err){alert(msg);}
  sh.OnOutput = function(o,a){gframe_close(o,ret);}
  sh.sendCommand("desktop edit-module -id `<?php echo $_REQUEST['modid']; ?>` -contents `"+contents+"` -css `"+css+"` -js `"+js+"`");
 }
 else
  gframe_close(null,ret);
}

function gframe_cachecontentsload(contents)
{
 document.getElementById('contents').innerHTML = contents.replace(/{ABSOLUTE_URL}/g,ABSOLUTE_URL);
 var sSkinPath = "<?php echo $_BASE_PATH; ?>../var/objects/fckeditor/editor/skins/office2003/";
 oFCKeditor = new FCKeditor('contents') ;
 oFCKeditor.ToolbarSet = "Optimized";
 oFCKeditor.BasePath	= "<?php echo $_BASE_PATH; ?>var/objects/fckeditor/";
 oFCKeditor.Config['SkinPath'] = sSkinPath ;
 oFCKeditor.Config['PreloadImages'] =
				sSkinPath + 'images/toolbar.start.gif' + ';' +
				sSkinPath + 'images/toolbar.end.gif' + ';' +
				sSkinPath + 'images/toolbar.bg.gif' + ';' +
				sSkinPath + 'images/toolbar.buttonarrow.gif' ;
 oFCKeditor.Width = 800;
 oFCKeditor.Height = 530;
 oFCKeditor.ReplaceTextarea();
}

function showPage(page,li)
{
 var ul = li.parentNode;
 var list = ul.getElementsByTagName('LI');
 for(var c=0; c < list.length; c++)
  list[c].className = list[c]==li ? "selected" : "";
 document.getElementById("page-"+ACTIVE_PAGE).style.display = "none";
 ACTIVE_PAGE = page;
 document.getElementById("page-"+ACTIVE_PAGE).style.display = "";
}

</script>
</body></html>
<?php

