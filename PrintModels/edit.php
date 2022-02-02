<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-01-2013
 #PACKAGE: printmodels-config
 #DESCRIPTION: Editor for print models.
 #VERSION: 2.1beta
 #CHANGELOG: 23-01-2013 : Bug fix for absolute URL with images & link.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DESKTOP_SHOW_TOOLBAR, $_DESKTOP_TITLE, $_AP, $_COMPANY_PROFILE;
$_DESKTOP_TITLE = "Print Models Editor";
$_DESKTOP_SHOW_TOOLBAR = false;
$_DESKTOP_BACKGROUND = "#ffffff";

$_BASE_PATH = "../";

define("VALID_GNUJIKO",1);
include($_BASE_PATH.'init/init1.php');
include($_BASE_PATH.'include/session.php');
include($_BASE_PATH.'include/gshell.php');
include_once($_BASE_PATH."include/company-profile.php");

$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : "printmodels";
$id = $_REQUEST['id'];
$ret = GShell("dynarc item-info -ap `".$_AP."` -id `".$id."` -extget css -get thumbdata");
$docInfo = $ret['outarr'];

$_BACKGROUND_IMAGE = "";
if($docInfo['thumbdata'] && (strpos($docInfo['thumbdata'],"data:") === false))
 $_BACKGROUND_IMAGE = $docInfo['thumbdata'];

//-------------------------------------------------------------------------------------------------------------------//
?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Edit print model: <?php echo $docInfo['name']; ?></title></head>
<link rel='shortcut icon' href='share/images/favicon.png' />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>PrintModels/edit.css" type="text/css" />
<?php
if(file_exists($_BASE_PATH."include/headings/desktop.php"))
{
 include($_BASE_PATH.'include/headings/desktop.php');
}
else
{
 echo "<body onload='desktopOnLoad()'>";
 include($_BASE_PATH.'include/headings/default.php');
}
//-------------------------------------------------------------------------------------------------------------------//
include($_BASE_PATH."var/objects/fckeditor/index.php");
include($_BASE_PATH."var/objects/htmlgutility/screenshot.php");
?>
<table width='100%' border='0' cellspacing='0' cellpadding='10' class='idoc-master-table' height='100%'>
<tr><td valign='middle' height='48' width='200'><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/logo.png"/></td>
	<td width='370' class='idoc-title' align='center' onclick='renameModel(this)' title="Clicca per rinominare"><?php echo $docInfo['name']; ?></td>
	<td align='right' valign='middle' width='200'>
		<a href='#' onclick='idoc_delete()'><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/delete-button.png" border="0"/></a>
		<a href='#' onclick='idoc_save()'><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/save-button.png" border="0"/></a>
	</td>
	<td rowspan='2' class='idoc-right-space' valign='top' align='left'>
	 <a href='#' onclick='idoc_preview()'><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/preview-button.png" border="0"/></a>
	 <br/>
	 <h3 class='idoc-section'>Inserisci oggetti</h3>
	 <div class='tool-objects'>
	  <div onclick="selectBackgroundImage()"><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/image.png" title="Seleziona immagine di sfondo"/></div>
	  <div onclick="insertImage('<?php echo $_COMPANY_PROFILE['logo']; ?>')"><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/company-logo.png" title="Inserisci logo aziendale"/></div>
	  <div onclick="insertKey()"><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/key.png" title="Inserisci chiave"/></div>
	  <div onclick="insertImage()"><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/image.png" title="Inserisci un immagine"/></div>
	  <div onclick="insertTable()"><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/table.png" title="Inserisci una tabella"/></div>
	 </div>

	 <br/>

	 <!-- TOOL KEY -->
	 <div class='tool-section' id='tool-key-page' style='display:none;'>
	  <div class='tool-title'><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/key.png"/> <span><i>Nuova chiave</i></span></div>
	  <p class='param'>KEY: <input type='text' class='text' style='width:80%' id='key' onchange='keyChanged(this)'/><br/> 
		<input type='radio' class='radio' name='key-type' id='keytypetext' checked='true' onclick="keyTypeChanged('text')"/><i>Chiave testuale</i><br/>
		<input type='radio' class='radio' name='key-type' id='keytypeid' onclick="keyTypeChanged('id')"/><i>Chiave identificativa</i> &nbsp;<a href='#'><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/help.png" border='0'/></a>
	  </p>

	  <p><select id='parser' style='width:80%' onchange='parserChange(this)'>
		 <option value='0'>Tipi di chiave</option>
		 <?php
		 $ret = GShell("parserize parserlist");
		 for($c=0; $c < count($ret['outarr']); $c++)
		 {
		  echo "<option value='".$ret['outarr'][$c]['name']."'>".$ret['outarr'][$c]['info']['name']."</option>";
		 }
		 ?>
		</select></p>

	  <p><select id='parserkeys' multiple='multiple' size='10' style='width:80%' onchange='parserKeyChange(this)'>
		 
		 </select></p>
	 </div>

	 <!-- TOOL IMAGE -->
	 <div class='tool-section' id='tool-image-page' style='display:none;'>
	  <div class='tool-title'><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/image.png"/> <span><i>Nuova immagine</i></span></div>
	  <div id='tool-image-thumbnail'>&nbsp;</div>
		<ul class='basicbuttons' >
  		 <li><span onclick="uploadImage()"><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/image-icon.png" border='0'/>Sfoglia / carica</span></li>
 		</ul>
	 </div>

	 <!-- TOOL TABLE -->
	 <div class='tool-section' id='tool-table-page' style='display:none;'>
	  <div class='tool-title'><img src="<?php echo $_ABSOLUTE_URL; ?>PrintModels/img/table.png"/> <span><i>Nuova tabella</i></span></div>
	  <!-- <p class='param'>TIPO DI TABELLA<br/>
		<select id='tabletype' style='width:150px'>
		 <option value=''>Personalizzata</option>
		 <option value='itemlist'>Lista di elementi</option>
		</select>
	  </p> -->
	  <p class='param'>COLONNE<br/>
	   <div id='table-column-container'>
		
	   </div>
	  </p>
	 </div>

	</td></tr>

<tr><td valign='top' colspan='3'>
	<ul class='idoc-tab'>
	 <li id='idoc-tab-html' class='selected'><a href='#' onclick='idocTab_showHTML()'>HTML</a></li>
	 <li id='idoc-tab-css'><a href='#' onclick='idocTab_showCSS()'>CSS</a></li>
	 <li id='idoc-tab-prop' style='display:none;'><a href='#' onclick='idocTab_showProp()'>Properties</a></li>
	</ul>

	<!-- HTML -->
	<div id='idoc-tab-html-div' style='height:100%;'>
	<textarea style="width:100%;height:90%;" id="idoc-html-editor"><?php echo str_replace("{ABSOLUTE_URL}",$_ABSOLUTE_URL,$docInfo['desc']); ?></textarea>
	</div>

	<!-- CSS -->
	<div id='idoc-tab-css-div' style='display:none;height:100%'>
	<textarea style="width:100%;height:90%;" id="idoc-css-editor"><?php echo $docInfo['css'][0]['content']; ?></textarea>
	</div>

	<!-- PROPERTIES -->
	<div id='idoc-tab-prop-div' style='display:none;height:100%;font-family:trebuchet,arial,serif;font-size:80%;'>
	 <h3>Proprietà del documento</h3>
	 <?php
	 // arrayze params //
	 /*$params = array();
	 if($docInfo['params'])
	 {
	  $x = explode("&",$docInfo['params']);
	  for($c=0; $c < count($x); $c++)
	  {
	   $xx = explode("=",$x[$c]);
	   if($xx[0])
		$params[$xx[0]] = $xx[1];
	  }
	 }*/
	 ?>
	 Titolo: <input type='text' id='idoc_title' value="<?php echo $docInfo['name']; ?>"/><br/>
	 Alias: <input type='text' id='idoc_alias' value="<?php echo $docInfo['aliasname']; ?>"/><br/>
	 Dimensioni: W:<input type='text' size='3' id='idoc_width' value="<?php echo $params['width']; ?>"/> H:<input type='text' size='3' id='idoc_height' value="<?php echo $params['height']; ?>"/><br/>
	</div>

	</td></tr>
</table>

<div id='tablerowprop' style='display:none;'>
 <p class='param'>ID: <input type='text' class='text' style='width:100px' id='columninfo_id'/></p>
 <p class='param'>LARGHEZZA: <input type='text' class='text' style='width:60px' id='columninfo_width'/></p>
 <p class='param'>FORMATTAZIONE<br/>
  <select style='width:150px' id='columninfo_format' onchange='columnFormatChanged(this)'>
   <option value='text'>Testo</option>
   <option value='number'>Numerico</option>
   <option value='percentage'>Percentuale</option>
   <option value='currency'>Valuta</option>
   <option value="percentage currency">Percentuale / Valuta</option>
   <option value="date">Data</option>
   <option value="time">Ora</option>
   <option value="datetime">Data e ora</option>
  </select>
 </p>
 <p class='param' id='columninfo_decimals_p' style='display:none;'>N. DECIMALI: <input type='text' class='text' size='2' id='columninfo_decimals'/></p>
</div>

<script>
var sSkinPath = "<?php echo $_ABSOLUTE_URL; ?>var/objects/fckeditor/editor/skins/office2003/";
var oFCKeditor = null;
var ACTIVE_TOOL = null;
var _lastTCA = null;
var NEW_NAME = null;
var BACKGROUND_IMAGE = "<?php echo $_BACKGROUND_IMAGE; ?>";

function desktopOnLoad()
{
 oFCKeditor = new FCKeditor('idoc-html-editor') ;
 oFCKeditor.BasePath = "<?php echo $_ABSOLUTE_URL; ?>var/objects/fckeditor/";
 oFCKeditor.Config['SkinPath'] = sSkinPath ;
 oFCKeditor.Config['PreloadImages'] =
				sSkinPath + 'images/toolbar.start.gif' + ';' +
				sSkinPath + 'images/toolbar.end.gif' + ';' +
				sSkinPath + 'images/toolbar.bg.gif' + ';' +
				sSkinPath + 'images/toolbar.buttonarrow.gif' ;
 oFCKeditor.Config['EditorAreaStyles'] = document.getElementById('idoc-css-editor').value;
 oFCKeditor.Width = "814px";
 oFCKeditor.Height = document.getElementById('idoc-html-editor').offsetHeight;
 oFCKeditor.ReplaceTextarea();

 oFCKeditor.setBackgroundImage = function(filename){
	 var oFCKIFrame = window.frames[0].window.frames[0];
	 oFCKIFrame.document.body.style.background = "#ffffff url("+ABSOLUTE_URL+filename+") center center no-repeat";
	}

}

function desktopOnUnload()
{
 window.opener.document.location.reload();
}

function FCKeditor_OnComplete(editorInstance )
{
 editorInstance.Events.AttachEvent( 'OnSelectionChange' , fckOnSelectionChange ) ;
 if(BACKGROUND_IMAGE)
  oFCKeditor.setBackgroundImage(BACKGROUND_IMAGE);
} 

function fckOnSelectionChange(oFCK)
{
 var selectedElement = oFCK.Selection.GetSelectedElement();
 var parentElement = oFCK.Selection.GetBoundaryParentElement(true);
 var selection = (oFCK.EditorWindow.getSelection ? oFCK.EditorWindow.getSelection() : oFCK.EditorDocument.selection);
 //-----------------------------------------------------------//
 if(selection)
  selection = selection.toString();

 /* Check if is a key */
 if(selection && (selection.length > 2))
 {
  if((selection.indexOf("{") >= 0) && (selection.indexOf("}") > 0))
   return toolEdit("key", selectedElement, parentElement, selection);
 }

 if(selectedElement)
 {
  if((selectedElement.id.indexOf("{") >= 0) && (selectedElement.id.indexOf("}") > 0))
   return toolEdit("key", selectedElement, parentElement, selection);
 }

 if(parentElement)
 {
  if((parentElement.id.indexOf("{") >= 0) && (parentElement.id.indexOf("}") > 0))
   return toolEdit("key", selectedElement, parentElement, selection);
 }

 /* Check if is a image */
 if(selectedElement && (selectedElement.tagName == "IMG"))
  return toolEdit("image", selectedElement, parentElement, selection);
 else if(parentElement && (parentElement.tagName == "IMG"))
  return toolEdit("image", selectedElement, parentElement, selection);

 /* Check if is a table */
 if(selectedElement && ((selectedElement.tagName == "TH") || (selectedElement.tagName == "TD")))
  return toolEdit("table", selectedElement, parentElement, selection);
 else if(parentElement && ((parentElement.tagName == "TH") || (parentElement.tagName == "TD")))
  return toolEdit("table", selectedElement, parentElement, selection);

 /* Unknown */
 if(ACTIVE_TOOL)
  document.getElementById("tool-"+ACTIVE_TOOL+"-page").style.display='none';
 ACTIVE_TOOL = null;
 
}

/* IDOC-TAB */
function idocTab_showHTML()
{
 document.getElementById('idoc-tab-css-div').style.display='none';
 document.getElementById('idoc-css-editor').style.display='none';

 document.getElementById('idoc-tab-html-div').style.display='';

 document.getElementById('idoc-tab-prop-div').style.display='none';

 document.getElementById('idoc-tab-html').className = "selected";
 document.getElementById('idoc-tab-css').className = "";
 document.getElementById('idoc-tab-prop').className = "";

}

function idocTab_showCSS()
{
 document.getElementById('idoc-tab-html-div').style.display='none';
 document.getElementById('idoc-html-editor').style.display='none';

 document.getElementById('idoc-tab-css-div').style.display='';
 document.getElementById('idoc-css-editor').style.display='';

 document.getElementById('idoc-tab-prop-div').style.display='none';

 document.getElementById('idoc-tab-html').className = "";
 document.getElementById('idoc-tab-css').className = "selected";
 document.getElementById('idoc-tab-prop').className = "";

 document.getElementById('idoc-css-editor').focus();
}

function idocTab_showProp()
{
 document.getElementById('idoc-tab-html-div').style.display='none';
 document.getElementById('idoc-html-editor').style.display='none';

 document.getElementById('idoc-tab-css-div').style.display='none';
 document.getElementById('idoc-css-editor').style.display='none';

 document.getElementById('idoc-tab-html').className = "";
 document.getElementById('idoc-tab-css').className = "";
 document.getElementById('idoc-tab-prop').className = "selected";

 document.getElementById('idoc-tab-prop-div').style.display='';
}

function idoc_delete()
{
 if(!confirm("Sei sicuro di voler eliminare questo modello?"))
  return;
 var sh = new GShell();
 sh.OnOutput = function(){document.location.href="index.php";}
 sh.sendCommand("dynarc delete-item -ap `<?php echo $_AP; ?>` -id `<?php echo $docInfo['id']; ?>`");
}

function urlencode(str) 
{
 str = escape(str);
 str = str.replace('+', '%2B');
 str = str.replace('%20', '+');
 str = str.replace('*', '%2A');
 str = str.replace('/', '%2F');
 str = str.replace('@', '%40');
 return str;
}

function idoc_save()
{
 var htmlContents = FCKeditorAPI.GetInstance('idoc-html-editor').GetXHTML();
 var cssContents = document.getElementById('idoc-css-editor').value;
 var cssID = <?php echo $docInfo['css'][0]['id'] ? $docInfo['css'][0]['id'] : "0"; ?>;
 
 var title = NEW_NAME ? NEW_NAME : document.getElementById('idoc_title').value;
 var alias = document.getElementById('idoc_alias').value;
 //var params = "width="+document.getElementById('idoc_width').value+"&height="+document.getElementById('idoc_height').value;


 /* REPLACE ABSOLUTE URL */
 var URL = "<?php echo $_ABSOLUTE_URL; ?>";
 htmlContents = htmlContents.replace(new RegExp(URL, 'g'), "{ABSOLUTE_URL}");

 var sh = new GShell();
 sh.OnOutput = function(){
	 if(BACKGROUND_IMAGE)
	 {
	  document.location.reload();
	  return;
	 }
	 /* MAKE SCREENSHOT */
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){
		 var sh3 = new GShell();
		 sh3.OnOutput = function(){alert('Salvataggio completato!');}
		 sh3.sendCommand("dynarc edit-item -ap `<?php echo $_AP; ?>` -id `<?php echo $docInfo['id']; ?>` -set `thumbdata='"+a+"'`");
		}
	 sh2.sendCommand("gframe -f printmodel.screenshot -params `ap=<?php echo $_AP; ?>&id=<?php echo $docInfo['id']; ?>` --fullscreen");
	}

 sh.sendCommand("dynarc edit-item -ap `<?php echo $_AP; ?>` -id `<?php echo $docInfo['id']; ?>` -name `"+title+"` -alias `"+alias+"` -desc `"+htmlContents+"` -extset `css."+(cssID ? "id="+cssID+"," : "")+"content='''"+cssContents+"'''`"+(BACKGROUND_IMAGE ? " -set `thumbdata='"+BACKGROUND_IMAGE+"'`" : "")); 
}

function idoc_preview()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var sh2 = new GShell();
	 sh2.sendCommand("dynarc edit-item -ap `<?php echo $_AP; ?>` -id `<?php echo $docInfo['id']; ?>` -set `thumbdata='"+a+"'`");
	}
 sh.sendCommand("gframe -f printmodel.preview -params `ap=<?php echo $_AP; ?>&id=<?php echo $docInfo['id']; ?>` --fullscreen");
}

function selectBackgroundImage()
{
 var dstPath = "image/printmodels/";
 if(BACKGROUND_IMAGE)
  oFCKeditor.setBackgroundImage(BACKGROUND_IMAGE);

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var file = a['files'][0];
	 oFCKeditor.setBackgroundImage(file['fullname']);
	 BACKGROUND_IMAGE = file['fullname'];
	}
 sh.sendCommand("gframe -f imageupload -params `destpath="+dstPath+"`");
}

function insertImage(path)
{
 if(!path)
  return showToolPage("image");

 var oFCK = FCKeditorAPI.GetInstance('idoc-html-editor');
 var html = "<img src='"+ABSOLUTE_URL+path+"'/\>";
 oFCK.InsertHtml(html);
}

function insertKey()
{
 showToolPage("key");
}

function insertTable()
{
 showToolPage("table");
}

function parserChange(sel)
{
 var list = document.getElementById('parserkeys');
 while(list.options.length)
  list.options[0].parentNode.removeChild(list.options[0]);

 if(!sel.value)
  return;

 var selectedKey = document.getElementById('key').value;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a || !a['keys']) return;
	 
	 for(k in a['keys'])
	 {
	  var o = document.createElement('OPTION');
	  o.value = k;
	  o.innerHTML = a['keys'][k];
	  list.appendChild(o);
	 }
	 list.value = selectedKey;
	}
 sh.sendCommand("parserize parserinfo `"+sel.value+"`");
}

function parserKeyChange(sel)
{
 document.getElementById('key').value = sel.value;
 keyChanged(document.getElementById('key'));
}

function keyChanged(ed)
{
 var oFCK = FCKeditorAPI.GetInstance('idoc-html-editor');
 var selectedElement = oFCK.Selection.GetSelectedElement();
 var parentElement = oFCK.Selection.GetBoundaryParentElement(true);
 var selection = (oFCK.EditorWindow.getSelection ? oFCK.EditorWindow.getSelection() : oFCK.EditorDocument.selection);

 if(document.getElementById('keytypeid').checked == true)
 {
  if(selectedElement)
   selectedElement.id = "{"+ed.value+"}";
  else if(parentElement)
   parentElement.id = "{"+ed.value+"}";
 }
 else
 {
  oFCK.InsertHtml("{"+ed.value+"}");
 }

}

function keyTypeChanged(type)
{
 var key = document.getElementById('key').value;

 var oFCK = FCKeditorAPI.GetInstance('idoc-html-editor');
 var selectedElement = oFCK.Selection.GetSelectedElement();
 var parentElement = oFCK.Selection.GetBoundaryParentElement(true);
 if(!selectedElement)
  selectedElement = parentElement;
 var selection = (oFCK.EditorWindow.getSelection ? oFCK.EditorWindow.getSelection() : oFCK.EditorDocument.selection);
 if(selection)
  selection = selection.toString();

 switch(type)
 {
  case 'text' : {
	 if(selectedElement && (selectedElement.id.indexOf("{") >= 0) && (selectedElement.id.indexOf("}") > 0))
	 {
	  selectedElement.id = "";
	  oFCK.InsertHtml(key ? "{"+key+"}" : "&nbsp;");
	 }
	 else if(selection && (selection.indexOf("{") >= 0) && (selection.indexOf("}") > 0))
	 {
	  oFCK.InsertHtml(key ? "{"+key+"}" : "&nbsp;");
	 }
	} break;

  case 'id' : {
	 if(selection && (selection.indexOf("{") >= 0) && (selection.indexOf("}") > 0))
	 {
	  oFCK.InsertHtml("&nbsp;");
	 }
	 if(selectedElement)
	  selectedElement.id = key ? "{"+key+"}" : "";
	} break;
 }

}

function showToolPage(tool)
{
 if(ACTIVE_TOOL)
  document.getElementById("tool-"+ACTIVE_TOOL+"-page").style.display='none';
 ACTIVE_TOOL = tool;

 switch(tool)
 { 
  case 'key' : {
	 document.getElementById('tool-key-page').getElementsByTagName('DIV')[0].getElementsByTagName('SPAN')[0].innerHTML = "<i>Nuova chiave</i>";
	 document.getElementById('key').value = "";
	} break;

  case 'image' : {
	 document.getElementById('tool-image-page').getElementsByTagName('DIV')[0].getElementsByTagName('SPAN')[0].innerHTML = "<i>Nuova immagine</i>";
	 document.getElementById('tool-image-thumbnail').style.backgroundImage = "none";
	} break;

  case 'table' : {
	 document.getElementById('tool-table-page').getElementsByTagName('DIV')[0].getElementsByTagName('SPAN')[0].innerHTML = "<i>Nuova tabella</i>";
	} break;
 }

 document.getElementById("tool-"+ACTIVE_TOOL+"-page").style.display='';
}

function toolEdit(tool, selectedElement, parentElement, selection)
{
 showToolPage(tool);
 switch(tool)
 {
  case 'key' : {
	 document.getElementById('tool-key-page').getElementsByTagName('DIV')[0].getElementsByTagName('SPAN')[0].innerHTML = "<i>Modifica chiave</i>";
	 if(selectedElement && (selectedElement.id.indexOf("{") >= 0) && (selectedElement.id.indexOf("}") > 0))
	 {
	  document.getElementById('key').value = selectedElement.id.replace("{","").replace("}","").trim();
	  document.getElementById('keytypeid').checked = true;
	 }
	 else if(parentElement && (parentElement.id.indexOf("{") >= 0) && (parentElement.id.indexOf("}") > 0))
	 {
	  document.getElementById('key').value = parentElement.id.replace("{","").replace("}","").trim();
	  document.getElementById('keytypeid').checked = true;
	 }
	 else
	 {
	  document.getElementById('key').value = selection.replace("{","").replace("}","").trim();
	  document.getElementById('keytypetext').checked = true;
	 }

	 document.getElementById('parserkeys').value = document.getElementById('key').value;
	} break;

  case 'image' : {
	 document.getElementById('tool-image-page').getElementsByTagName('DIV')[0].getElementsByTagName('SPAN')[0].innerHTML = "<i>Modifica immagine</i>";
	 if(selectedElement && (selectedElement.tagName == "IMG"))
	  document.getElementById('tool-image-thumbnail').style.backgroundImage = "url("+selectedElement.src+")";
	 else if(parentElement && (parentElement.tagName == "IMG"))
	  document.getElementById('tool-image-thumbnail').style.backgroundImage = "url("+parentElement.src+")";
	} break;

  case 'table' : {
	 var tb = null;
	 document.getElementById('tool-table-page').getElementsByTagName('DIV')[0].getElementsByTagName('SPAN')[0].innerHTML = "<i>Modifica tabella</i>";
	 if(selectedElement && ((selectedElement.tagName == "TH") || (selectedElement.tagName == "TD")))
	  tb = selectedElement.parentNode.parentNode;
	 else if(parentElement && ((parentElement.tagName == "TH") || (parentElement.tagName == "TD")))
	  tb = parentElement.parentNode.parentNode;
	 if(tb.tagName != "TABLE")
	  tb = tb.parentNode;

	 var container = document.getElementById('table-column-container');
	 var divInfo = document.getElementById('tablerowprop');
	 divInfo.style.display='none';
	 document.body.appendChild(divInfo);

	 container.innerHTML = "";
	 for(var c=0; c < tb.rows[0].cells.length; c++)
	 {
	  var div = document.createElement('DIV');
	  div.className = "column";
	  div.O = tb.rows[0].cells[c];
	  if(tb.rows[0].cells[c].style.display == "none")
	  {
	   div.className = "column disabled";
	   var html = "<img class='icon' src='"+ABSOLUTE_URL+"PrintModels/img/object-hidden.png' onclick='showHideColumn(this)'/ >";
	  }
	  else
	   var html = "<img class='icon' src='"+ABSOLUTE_URL+"PrintModels/img/object-visible.png' onclick='showHideColumn(this)'/ >";
	  html+= " <a href='#' onclick='toggleColumnInfo(this)'>"+tb.rows[0].cells[c].innerHTML+"</a>";
	  html+= "<div class='arrows'><div class='arrowdown' onclick='moveColumnRight(this)'>&nbsp;</div><div class='arrowup' onclick='moveColumnLeft(this)'>&nbsp;</div></div>";
	  div.innerHTML = html;
	  container.appendChild(div);
	 }

	} break;
 }

}

/* THUMBNAIL */
function uploadImage()
{
 var dstPath = "image/printmodels/";

 var oFCK = FCKeditorAPI.GetInstance('idoc-html-editor');
 var selectedElement = oFCK.Selection.GetSelectedElement();
 var parentElement = oFCK.Selection.GetBoundaryParentElement(true);
 if(!selectedElement)
  selectedElement = parentElement;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var file = a['files'][0];
     document.getElementById('tool-image-thumbnail').style.backgroundImage = "url("+ABSOLUTE_URL+file['fullname']+")";

	 if(selectedElement && (selectedElement.tagName == "IMG"))
	 {
	  oFCK.InsertHtml("<img src='"+ABSOLUTE_URL+file['fullname']+"'/ >");
	 }
	 else
	  oFCK.InsertHtml("<img src='"+ABSOLUTE_URL+file['fullname']+"'/ >");
	}
 sh.sendCommand("gframe -f imageupload -params `destpath="+dstPath+"`");
}

function showHideColumn(img)
{
 var th = img.parentNode.O;
 var r = th.parentNode;
 var tb = r.parentNode;
 if(tb.tagName != "TABLE")
  tb = tb.parentNode;

 if(th.style.display == "none")
 {
  showColumn(th,tb);
  if(r.cells.length > (th.cellIndex+1))
  {
   var finalWidth = r.cells[th.cellIndex+1].offsetWidth-th.offsetWidth;
   if(tb.getElementsByTagName('COLGROUP').length)
	tb.getElementsByTagName('COLGROUP')[0].getElementsByTagName('COL')[th.cellIndex+1].style.width = px2mm(finalWidth)+"mm";
   else
    r.cells[th.cellIndex+1].style.width = px2mm(finalWidth)+"mm";
  }
  else if(th.cellIndex > 0)
  {
   var finalWidth = r.cells[th.cellIndex-1].offsetWidth-th.offsetWidth;
   if(tb.getElementsByTagName('COLGROUP').length)
	tb.getElementsByTagName('COLGROUP')[0].getElementsByTagName('COL')[th.cellIndex-1].style.width = px2mm(finalWidth)+"mm";
   else
    r.cells[th.cellIndex-1].style.width = px2mm(finalWidth)+"mm";
  }
  img.src = ABSOLUTE_URL+"PrintModels/img/object-visible.png";
  img.parentNode.className = "column";
 }
 else
 {
  if(r.cells.length > (th.cellIndex+1))
  {
   var finalWidth = r.cells[th.cellIndex+1].offsetWidth+th.offsetWidth;
   if(tb.getElementsByTagName('COLGROUP').length)
	tb.getElementsByTagName('COLGROUP')[0].getElementsByTagName('COL')[th.cellIndex+1].style.width = px2mm(finalWidth)+"mm";
   else
    r.cells[th.cellIndex+1].style.width = px2mm(finalWidth)+"mm";
  }
  else if(th.cellIndex > 0)
  {
   var finalWidth = r.cells[th.cellIndex-1].offsetWidth+th.offsetWidth;
   if(tb.getElementsByTagName('COLGROUP').length)
	tb.getElementsByTagName('COLGROUP')[0].getElementsByTagName('COL')[th.cellIndex-1].style.width = px2mm(finalWidth)+"mm";
   else
    r.cells[th.cellIndex-1].style.width = px2mm(finalWidth)+"mm";
  }
  hideColumn(th,tb);
  img.src = ABSOLUTE_URL+"PrintModels/img/object-hidden.png";
  img.parentNode.className = "column disabled";
 }

}

function hideColumn(th,tb)
{
 var fR = tb.rows[tb.rows.length-1];
 // update height 
 for(var c=0; c < fR.cells.length; c++)
   fR.cells[c].style.height = px2mm(fR.cells[c].offsetHeight)+"mm";

 for(var c=0; c < tb.rows.length; c++)
  tb.rows[c].cells[th.cellIndex].style.display='none';
 if(tb.getElementsByTagName('COLGROUP').length)
  tb.getElementsByTagName('COLGROUP')[0].getElementsByTagName('COL')[th.cellIndex].style.display='none';
}

function showColumn(th,tb)
{
 for(var c=0; c < tb.rows.length; c++)
  tb.rows[c].cells[th.cellIndex].style.display='';
 if(tb.getElementsByTagName('COLGROUP').length)
  tb.getElementsByTagName('COLGROUP')[0].getElementsByTagName('COL')[th.cellIndex].style.display='';
}

function toggleColumnInfo(a)
{
 var container = a.parentNode;
 var tb = container.O.parentNode;
 while(tb.tagName != "TABLE")
 {
  tb = tb.parentNode;
 }

 var divInfo = document.getElementById('tablerowprop');

 if(!container.open)
 {
  if(_lastTCA && (_lastTCA != a))
  {
   _lastTCA.parentNode.style.height = "20px";
   _lastTCA.parentNode.open = false;
  }
  _lastTCA = a;
  container.appendChild(divInfo);
  divInfo.style.display='';
  container.open = true;

  document.getElementById('columninfo_id').value = container.O.id;
  /* Get width */
  var width = "";
  if(tb.getElementsByTagName('COLGROUP').length)
  {
   var col = tb.getElementsByTagName('COLGROUP')[0].getElementsByTagName('COL')[container.O.cellIndex];
   if(col && col.style.width)
	width = col.style.width;
  }
  else
   width = container.O.style.width;

  document.getElementById('columninfo_id').cell = container.O;
  document.getElementById('columninfo_id').onchange = function(){
	 this.cell.id = this.value;
	}

  document.getElementById('columninfo_width').value = width;
  document.getElementById('columninfo_width').defaultValue = width;
  document.getElementById('columninfo_width').onchange = function(){
	 if(tb.getElementsByTagName('COLGROUP').length)
	 {
	  var col = tb.getElementsByTagName('COLGROUP')[0].getElementsByTagName('COL')[container.O.cellIndex];
	  col.style.width = this.value;
	 }
	 else
	  container.O.style.width = this.value;

	 var diff = parseFloat(this.value) - parseFloat(this.defaultValue);

	 for(var c=0; c < tb.rows[0].cells.length; c++)
	 {
	  if((tb.rows[0].cells[c].getAttribute('size') == "auto") && (c != container.O.cellIndex))
	  {
	   if(tb.getElementsByTagName('COLGROUP').length)
	   {
		var col = tb.getElementsByTagName('COLGROUP')[0].getElementsByTagName('COL')[c];
		col.style.width = (parseFloat(col.style.width)-diff)+(col.style.width.indexOf('mm') > 0 ? "mm" : "px");
	   }
	   else
	   {
		tb.rows[0].cells[c].style.width = (parseFloat(tb.rows[0].cells[c].style.width)-diff)+(tb.rows[0].cells[c].style.width.indexOf('mm') > 0 ? "mm" : "px");
	   }
	   break;
	  }
	 }
	 this.defaultValue = this.value;
	}
  
  /* Get format */
  if((tb.rows.length > 1) && (tb.rows[0].cells[0].tagName == "TH"))
   var r = tb.rows[1];
  else if(tb.rows.length)
   var r = tb.rows[0];
  var cell = r.cells[container.O.cellIndex];
  document.getElementById('columninfo_format').value = cell.getAttribute('format') ? cell.getAttribute('format') : "text";
  document.getElementById('columninfo_format').cell = cell;
  document.getElementById('columninfo_decimals').value = cell.getAttribute('decimals') ? cell.getAttribute('decimals') : "2";
  document.getElementById('columninfo_decimals').cell = cell;
  columnFormatChanged(document.getElementById('columninfo_format'));

  container.style.height = divInfo.offsetHeight+42;
 }
 else
 {
  divInfo.style.display = 'none';
  container.open = false;
  container.style.height = "20px";
 }

}

function columnFormatChanged(sel)
{
 switch(sel.value)
 {
  case 'currency' : case 'percentage currency' : document.getElementById('columninfo_decimals_p').style.display=''; break;
  default : document.getElementById('columninfo_decimals_p').style.display='none'; break;
 }
 if(sel.cell)
  sel.cell.setAttribute('format',sel.value);
}

function columnDecimalsChanged(ed)
{
 if(ed.cell)
  ed.cell.setAttribute('decimals',ed.value);
}

function px2mm(px)
{
 return (25.4/96)*px;
}

function moveColumnLeft(div)
{
 var th = div.parentNode.parentNode.O;
 var tb = th.parentNode;
 while(tb.tagName != "TABLE"){tb = tb.parentNode;}
 var idx = th.cellIndex;
 if(idx > 0)
 {
  for(var c=0; c < tb.rows.length; c++)
  {
   var cell = tb.rows[c].cells[idx];
   cell.parentNode.insertBefore(cell, tb.rows[c].cells[idx-1]);
  }
  if(tb.getElementsByTagName('COLGROUP').length)
  {
   var col = tb.getElementsByTagName('COLGROUP')[0].getElementsByTagName('COL')[idx];
   col.parentNode.insertBefore(col,tb.getElementsByTagName('COLGROUP')[0].getElementsByTagName('COL')[idx-1]);
  }
 }
 var oFCK = FCKeditorAPI.GetInstance('idoc-html-editor');
 fckOnSelectionChange(oFCK);
}

function moveColumnRight(div)
{
 var th = div.parentNode.parentNode.O;
 var tb = th.parentNode;
 while(tb.tagName != "TABLE"){tb = tb.parentNode;}
 var idx = th.cellIndex;
 if(idx < (tb.rows[0].cells.length-1))
 {
  for(var c=0; c < tb.rows.length; c++)
  {
   var cell = tb.rows[c].cells[idx];
   cell.parentNode.insertBefore(tb.rows[c].cells[idx+1],cell);
  }
  if(tb.getElementsByTagName('COLGROUP').length)
  {
   var col = tb.getElementsByTagName('COLGROUP')[0].getElementsByTagName('COL')[idx];
   col.parentNode.insertBefore(tb.getElementsByTagName('COLGROUP')[0].getElementsByTagName('COL')[idx+1],col);
  }
 }
 var oFCK = FCKeditorAPI.GetInstance('idoc-html-editor');
 fckOnSelectionChange(oFCK);
}

function renameModel(el)
{
 var nm = prompt("Rinomina questo modello",el.innerHTML);
 if(!nm)
  return;
 el.innerHTML = nm;
 NEW_NAME = nm;
}
</script>
<?php
//-------------------------------------------------------------------------------------------------------------------//
if(file_exists($_BASE_PATH."include/footers/desktop.php"))
 include($_BASE_PATH.'include/footers/desktop.php');
else
 include($_BASE_PATH.'include/footers/default.php');
?>
</body></html>
<?php

