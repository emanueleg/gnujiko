<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-03-2013
 #PACKAGE: idoc-config
 #DESCRIPTION: Edit Gnujiko IDOCS.
 #VERSION: 2.1beta
 #CHANGELOG: 03-03-2013 : Risolto probema sul caricamento dei contenuti (se i contenuti contenevano delle textarea, quest'ultime venivano modificate dal fckeditor, causando disastri html.)
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DESKTOP_SHOW_TOOLBAR, $_DESKTOP_TITLE, $_AP;
$_DESKTOP_TITLE = "iDoc";
$_DESKTOP_SHOW_TOOLBAR = false;
$_DESKTOP_BACKGROUND = "#ffffff";

$_BASE_PATH = "../";

define("VALID_GNUJIKO",1);
include($_BASE_PATH.'init/init1.php');
include($_BASE_PATH.'include/session.php');
include($_BASE_PATH.'include/gshell.php');

//-------------------------------------------------------------------------------------------------------------------//
?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>iDoc - Interactive Documents</title></head>
<link rel='shortcut icon' href='share/images/favicon.png' />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>iDoc/edit.css" type="text/css" />
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

$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : "idoc";
$id = $_REQUEST['id'];
$ret = GShell("dynarc item-info -ap `".$_AP."` -id `".$id."` -extget javascript,css -get params");
$docInfo = $ret['outarr'];

?>
<table width='100%' border='0' cellspacing='0' cellpadding='10' class='idoc-master-table' height='100%'>
<tr><td valign='middle' height='48' width='150'><img src="<?php echo $_ABSOLUTE_URL; ?>iDoc/img/idoc-logo.png"/></td>
	<td class='idoc-title' align='center'><?php echo $docInfo['name']; ?></td>
	<td width='200' align='right' valign='middle'>
		<a href='#' onclick='idoc_delete()'><img src="<?php echo $_ABSOLUTE_URL; ?>iDoc/img/delete-button.png" border="0"/></a>
		<a href='#' onclick='idoc_save()'><img src="<?php echo $_ABSOLUTE_URL; ?>iDoc/img/save-button.png" border="0"/></a>
	</td>
	<td rowspan='2' width='200' class='idoc-right-space' valign='top' align='left'>
	 <a href='#' onclick='idoc_preview()'><img src="<?php echo $_ABSOLUTE_URL; ?>iDoc/img/preview-button.png" border="0"/></a>
	 <br/>

	 <h3 class='idoc-section'>Inserisci oggetti</h3>
	 <p>
	 <table width='100%' class='idoc-smalltable' border='0'>
	 <tr><td><img src="<?php echo $_ABSOLUTE_URL; ?>iDoc/obj-icons/edit.png"/></td>
		 <td><a href='#' onclick="idoc_insert('edit')">Casella di edit</a></td></tr>
	 <tr><td><img src="<?php echo $_ABSOLUTE_URL; ?>iDoc/obj-icons/checkbox.png"/></td>
		 <td><a href='#'  onclick="idoc_insert('checkbox')">Checkbox</a></td></tr>
	 <tr><td><img src="<?php echo $_ABSOLUTE_URL; ?>iDoc/obj-icons/radio-button.png"/></td>
		 <td><a href='#' onclick="idoc_insert('radio')">Radio button</a></td></tr>
	 <tr><td><img src="<?php echo $_ABSOLUTE_URL; ?>iDoc/obj-icons/combobox.png"/></td>
		 <td><a href='#' onclick="idoc_insert('combobox')">Combobox</a></td></tr>
	 <tr><td><img src="<?php echo $_ABSOLUTE_URL; ?>iDoc/obj-icons/textarea.png"/></td>
		 <td><a href='#' onclick="idoc_insert('textarea')">Textarea</a></td></tr>
	 </table>
	 </p>

	 <br/>

	 <h3 class='idoc-section'>Inserisci bottoni</h3>
	 <p>
	 <table width='100%' class='idoc-smalltable' border='0'>
	 <tr><td><img src="<?php echo $_ABSOLUTE_URL; ?>iDoc/btn-icons/submit-button.png"/></td>
		 <td><a href='#' onclick="idoc_insert('submit-button')">Bottone "Conferma"</a></td></tr>
	 <tr><td><img src="<?php echo $_ABSOLUTE_URL; ?>iDoc/btn-icons/cancel-button.jpg"/></td>
		 <td><a href='#' onclick="idoc_insert('abort-button')">Bottone "Annulla"</a></td></tr>
	 <!-- <tr><td><img src="<?php echo $_ABSOLUTE_URL; ?>iDoc/btn-icons/upload-button.gif"/></td>
		 <td><a href='#' onclick="idoc_insert('upload-button')">Bottone "Upload file"</a></td></tr> -->
	 </table>
	 </p>

	 <h3 class='idoc-section'>Includi CSS esterni<img src="<?php echo $_ABSOLUTE_URL; ?>iDoc/img/add-btn.png" onclick="addCSS()" style="float:right;cursor:pointer;margin:3px;"/></h3>
	 <p>
	 <table width='100%' class='idoc-smalltable' border='0' id='css-list'>
	 <?php
	 for($c=0; $c < count($docInfo['css']); $c++)
	 {
	  if(!$docInfo['css'][$c]['src'])
	   continue;
	  echo "<tr id='css-".$docInfo['css'][$c]['id']."'><td><input type='text' value='".$docInfo['css'][$c]['src']."' style='width:160px'/></td>";
	  echo "<td width='22'><img src='".$_ABSOLUTE_URL."iDoc/img/delete_small.png' onclick='deleteCSS(this)'/></td></tr>";
	 }
	 ?>
	 </table>
	 </p>

	 <h3 class='idoc-section'>Includi JS esterni<img src="<?php echo $_ABSOLUTE_URL; ?>iDoc/img/add-btn.png" onclick="addJS()" style="float:right;cursor:pointer;margin:3px;"/></h3>
	 <p>
	 <table width='100%' class='idoc-smalltable' border='0' id='javascript-list'>
	 <?php
	 for($c=0; $c < count($docInfo['javascript']); $c++)
	 {
	  if(!$docInfo['javascript'][$c]['src'])
	   continue;
	  echo "<tr id='javascript-".$docInfo['javascript'][$c]['id']."'><td><input type='text' value='".$docInfo['javascript'][$c]['src']."' style='width:160px'/></td>";
	  echo "<td width='22'><img src='".$_ABSOLUTE_URL."iDoc/img/delete_small.png' onclick='deleteJS(this)'/></td></tr>";
	 }
	 ?>
	 </table>
	 </p>

	</td></tr>

<tr><td valign='top' colspan='3'>
	<ul class='idoc-tab'>
	 <li id='idoc-tab-html' class='selected'><a href='#' onclick='idocTab_showHTML()'>HTML</a></li>
	 <li id='idoc-tab-js'><a href='#' onclick='idocTab_showJS()'>Javascript</a></li>
	 <li id='idoc-tab-css'><a href='#' onclick='idocTab_showCSS()'>CSS</a></li>
	 <li id='idoc-tab-prop'><a href='#' onclick='idocTab_showProp()'>Properties</a></li>
	</ul>

	<!-- HTML -->
	<div id='idoc-tab-html-div' style='height:100%;'>
	<textarea style="width:100%;height:90%;" id="idoc-html-editor">&nbsp;</textarea>
	</div>

	<!-- JAVASCRIPT -->
	<div id='idoc-tab-js-div' style='display:none;height:100%;'>
	<textarea style="width:100%;height:90%;" id="idoc-js-editor"><?php echo $docInfo['javascript'][0]['content']; ?></textarea>
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
	 $params = array();
	 if($docInfo['params'])
	 {
	  $x = explode("&",$docInfo['params']);
	  for($c=0; $c < count($x); $c++)
	  {
	   $xx = explode("=",$x[$c]);
	   if($xx[0])
		$params[$xx[0]] = $xx[1];
	  }
	 }
	 ?>
	 Titolo: <input type='text' id='idoc_title' value="<?php echo $docInfo['name']; ?>"/><br/>
	 Alias: <input type='text' id='idoc_alias' value="<?php echo $docInfo['aliasname']; ?>"/><br/>
	 Dimensioni: W:<input type='text' size='3' id='idoc_width' value="<?php echo $params['width']; ?>"/> H:<input type='text' size='3' id='idoc_height' value="<?php echo $params['height']; ?>"/><br/>
	</div>

	</td></tr>
</table>


<script>
var sSkinPath = "<?php echo $_ABSOLUTE_URL; ?>var/objects/fckeditor/editor/skins/office2003/";
var oFCKeditor = null;
var CACHE_CONTENTS = "";
var REMOVED_CSS = new Array();
var REMOVED_JS = new Array();

function desktopOnLoad()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 gframe_cachecontentsload(a['desc'])
	}
 sh.sendCommand("dynarc item-info -ap `idoc` -id `<?php echo $docInfo['id']; ?>`");
}

function desktopOnUnload()
{
 if(window.opener)
  window.opener.document.location.reload();
}

function gframe_cachecontentsload(contents)
{
 CACHE_CONTENTS = contents.replace("{ABSOLUTE_URL}",ABSOLUTE_URL);
 oFCKeditor = new FCKeditor('idoc-html-editor') ;
 oFCKeditor.BasePath	= "<?php echo $_BASE_PATH; ?>var/objects/fckeditor/";
 oFCKeditor.Config['SkinPath'] = sSkinPath ;
 oFCKeditor.Config['PreloadImages'] =
				sSkinPath + 'images/toolbar.start.gif' + ';' +
				sSkinPath + 'images/toolbar.end.gif' + ';' +
				sSkinPath + 'images/toolbar.bg.gif' + ';' +
				sSkinPath + 'images/toolbar.buttonarrow.gif' ;
 oFCKeditor.Config['EditorAreaStyles'] = document.getElementById('idoc-css-editor').value;
 oFCKeditor.Height = 536;
 oFCKeditor.ReplaceTextarea();
}

function FCKeditor_OnComplete(editorInstance )
{
 editorInstance.Events.AttachEvent( 'OnSelectionChange' , idoc_OnSelectionChange ) ;
 editorInstance.SetHTML(CACHE_CONTENTS);
} 

function idoc_OnSelectionChange(oFCK)
{
 //idoc_updateObjectInfo(selectedElement,oFCK.Selection.GetBoundaryParentElement(true));
}

/* IDOC-TAB */
function idocTab_showHTML()
{
 document.getElementById('idoc-tab-js-div').style.display='none';
 document.getElementById('idoc-js-editor').style.display='none';

 document.getElementById('idoc-tab-css-div').style.display='none';
 document.getElementById('idoc-css-editor').style.display='none';

 document.getElementById('idoc-tab-html-div').style.display='';

 document.getElementById('idoc-tab-prop-div').style.display='none';

 document.getElementById('idoc-tab-html').className = "selected";
 document.getElementById('idoc-tab-css').className = "";
 document.getElementById('idoc-tab-js').className = "";
 document.getElementById('idoc-tab-prop').className = "";
}

function idocTab_showJS()
{
 document.getElementById('idoc-tab-html-div').style.display='none';
 document.getElementById('idoc-html-editor').style.display='none';

 document.getElementById('idoc-tab-css-div').style.display='none';
 document.getElementById('idoc-css-editor').style.display='none';

 document.getElementById('idoc-tab-js-div').style.display='';
 document.getElementById('idoc-js-editor').style.display='';

 document.getElementById('idoc-tab-prop-div').style.display='none';

 document.getElementById('idoc-tab-html').className = "";
 document.getElementById('idoc-tab-css').className = "";
 document.getElementById('idoc-tab-js').className = "selected";
 document.getElementById('idoc-tab-prop').className = "";

 document.getElementById('idoc-js-editor').focus();
}

function idocTab_showCSS()
{
 document.getElementById('idoc-tab-html-div').style.display='none';
 document.getElementById('idoc-html-editor').style.display='none';

 document.getElementById('idoc-tab-js-div').style.display='none';
 document.getElementById('idoc-js-editor').style.display='none';

 document.getElementById('idoc-tab-css-div').style.display='';
 document.getElementById('idoc-css-editor').style.display='';

 document.getElementById('idoc-tab-prop-div').style.display='none';

 document.getElementById('idoc-tab-html').className = "";
 document.getElementById('idoc-tab-css').className = "selected";
 document.getElementById('idoc-tab-js').className = "";
 document.getElementById('idoc-tab-prop').className = "";

 document.getElementById('idoc-css-editor').focus();
}

function idocTab_showProp()
{
 document.getElementById('idoc-tab-html-div').style.display='none';
 document.getElementById('idoc-html-editor').style.display='none';

 document.getElementById('idoc-tab-js-div').style.display='none';
 document.getElementById('idoc-js-editor').style.display='none';

 document.getElementById('idoc-tab-css-div').style.display='none';
 document.getElementById('idoc-css-editor').style.display='none';

 document.getElementById('idoc-tab-html').className = "";
 document.getElementById('idoc-tab-css').className = "";
 document.getElementById('idoc-tab-js').className = "";
 document.getElementById('idoc-tab-prop').className = "selected";

 document.getElementById('idoc-tab-prop-div').style.display='';
}

function idoc_delete()
{
 if(!confirm("Sei sicuro di voler eliminare questo documento?"))
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
 var htmlContents = FCKeditorAPI.GetInstance('idoc-html-editor').GetXHTML().replace(ABSOLUTE_URL,"{ABSOLUTE_URL}");
 var jsContents = document.getElementById('idoc-js-editor').value;
 var cssContents = document.getElementById('idoc-css-editor').value;
 var jsID = <?php echo $docInfo['javascript'][0]['id'] ? $docInfo['javascript'][0]['id'] : "0"; ?>;
 var cssID = <?php echo $docInfo['css'][0]['id'] ? $docInfo['css'][0]['id'] : "0"; ?>;
 
 var title = document.getElementById('idoc_title').value;
 var alias = document.getElementById('idoc_alias').value;
 var params = "width="+document.getElementById('idoc_width').value+"&height="+document.getElementById('idoc_height').value;
 
 var sh = new GShell();
 sh.OnFinish = function(){
	 alert('Salvataggio completato!');
	}
 sh.sendCommand("dynarc edit-item -ap `<?php echo $_AP; ?>` -id `<?php echo $docInfo['id']; ?>` -name `"+title+"` -alias `"+alias+"` -desc `"+htmlContents+"` -extset `javascript."+(jsID ? "id="+jsID+"," : "")+"content=<![CDATA["+jsContents+"]]>,css."+(cssID ? "id="+cssID+"," : "")+"content='''"+cssContents+"'''` -set `params='"+params+"'`");

 /* Save external css */
 var cssTB = document.getElementById('css-list');
 for(var c=0; c < cssTB.rows.length; c++)
 {
  var r = cssTB.rows[c];
  var cssFile = r.cells[0].getElementsByTagName('INPUT')[0].value;
  if(!cssFile) continue;
  sh.sendCommand("dynarc edit-item -ap `<?php echo $_AP; ?>` -id `<?php echo $docInfo['id']; ?>` -extset `css."+(r.id ? "id="+r.id.substr(4)+"," : "")+"src='"+cssFile+"'`");
 }
 for(var c=0; c < REMOVED_CSS.length; c++)
  sh.sendCommand("dynarc edit-item -ap `<?php echo $_AP; ?>` -id `<?php echo $docInfo['id']; ?>` -extunset `css.id="+REMOVED_CSS[c].id.substr(4)+"`");

 /* Save external javascripts */
 var jsTB = document.getElementById('javascript-list');
 for(var c=0; c < jsTB.rows.length; c++)
 {
  var r = jsTB.rows[c];
  var jsFile = r.cells[0].getElementsByTagName('INPUT')[0].value;
  if(!jsFile) continue;
  sh.sendCommand("dynarc edit-item -ap `<?php echo $_AP; ?>` -id `<?php echo $docInfo['id']; ?>` -extset `javascript."+(r.id ? "id="+r.id.substr(11)+"," : "")+"src='"+jsFile+"'`");
 }
 for(var c=0; c < REMOVED_JS.length; c++)
  sh.sendCommand("dynarc edit-item -ap `<?php echo $_AP; ?>` -id `<?php echo $docInfo['id']; ?>` -extunset `javascript.id="+REMOVED_JS[c].id.substr(11)+"`");
}

function idoc_preview()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var sh2 = new GShell();
	 sh2.sendCommand("dynarc edit-item -ap `<?php echo $_AP; ?>` -id `<?php echo $docInfo['id']; ?>` -set `thumbdata='"+a+"'`");
	}
 sh.sendCommand("gframe -f idoc.preview -params `idocap=<?php echo $_AP; ?>&idocid=<?php echo $docInfo['id']; ?>&screenshot=true`");
}

function idoc_updateObjectInfo(element, bParent)
{
 if(!element)
 {
  switch(bParent.tagName.toUpperCase())
  {
   case 'A' : case 'INPUT' : case 'TEXTAREA' : case 'SELECT' : element = bParent; break;
  }
 }

 if(!element)
  return; // da cambiare
 
 switch(element.tagName.toUpperCase())
 {
  case 'INPUT' : {
	 switch(element.type.toLowerCase())
	 {
	  case 'text' : {
		} break;
	  case 'button' : {
		} break;
	  case 'radio' : {
		} break;
	  case 'checkbox' : {
		} break;
	 }
	} break;

  case 'SELECT' : {
	 
	} break;
  
 }
}

function idoc_insert(type)
{
 var oFCK = FCKeditorAPI.GetInstance('idoc-html-editor');
 switch(type)
 {
  case 'edit' : {
	 var html = "<input type='text' size='20' value=''/\>";
	 oFCK.InsertHtml(html);
	} break;
  case 'checkbox' : {
	 var html = "<input type='checkbox'/\>";
	 oFCK.InsertHtml(html);
	} break;
  case 'radio' : {
	 var html = "<input type='radio' name=''/\>";
	 oFCK.InsertHtml(html);
	} break;
  case 'combobox' : {
	 var html = "<select><option value='1'>opt1</option><option value='2'>opt2</option></select>";
	 oFCK.InsertHtml(html);
	} break;
  case 'textarea' : {
	 var html = "<textarea cols='20' rows='5'></textarea>";
	 oFCK.InsertHtml(html);
	} break;

  /* BUTTONS */
  case 'submit-button' : {
	 var html = "<input type='button' value='Conferma'/\>";
	 oFCK.InsertHtml(html); 
	} break;
  case 'abort-button' : {
	 // su onclick non bisogna mettere le vergolette (") altrimenti fckeditor le trasforma in (&lsquo;), omettendole fck se le aggiunge in automatico.
	 var html = "<input type='button' value='Annulla' onclick=gframe_close()></input>"; 
	 oFCK.InsertHtml(html); 
	} break;
 }
}

function addCSS()
{
 var tb = document.getElementById('css-list');
 var r = tb.insertRow(-1);
 r.insertCell(-1).innerHTML = "<input type='text' value='' style='width:160px'/"+">";
 r.insertCell(-1).innerHTML = "<img src='"+ABSOLUTE_URL+"iDoc/img/delete_small.png' onclick='deleteCSS(this)'/"+">";
 r.cells[1].style.width = "22px";
}

function addJS()
{
 var tb = document.getElementById('javascript-list');
 var r = tb.insertRow(-1);
 r.insertCell(-1).innerHTML = "<input type='text' value='' style='width:160px'/"+">";
 r.insertCell(-1).innerHTML = "<img src='"+ABSOLUTE_URL+"iDoc/img/delete_small.png' onclick='deleteJS(this)'/"+">";
 r.cells[1].style.width = "22px";
}

function deleteCSS(img)
{
 var r = img.parentNode.parentNode;
 if(r.id && !confirm("Sei sicuro di voler rimuovere questo CSS ?"))
  return;
 if(r.id)
  REMOVED_CSS.push(r);
 r.parentNode.removeChild(r);
}

function deleteJS(img)
{
 var r = img.parentNode.parentNode;
 if(r.id && !confirm("Sei sicuro di voler rimuovere questo JavaScript ?"))
  return;
 if(r.id)
  REMOVED_JS.push(r);
 r.parentNode.removeChild(r);
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

