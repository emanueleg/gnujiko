<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 31-08-2013
 #PACKAGE: blocknotes-module
 #DESCRIPTION: Edit note form.
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS: fckeditor
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : "blocknotes";
$ret = GShell("dynarc item-info -ap '".$_AP."' -id '".$_REQUEST['id']."'",$_REQUEST['sessid'],$_REQUEST['shellid']);
if(!$ret['error'])
 $docInfo=$ret['outarr'];

/* GET ATTACHMENTS */
$ret = GShell("dynattachments list -ap '".$_AP."' -refid ".$docInfo['id'],$_REQUEST['sessid'],$_REQUEST['shellid']);
$docInfo['attachments'] = $ret['outarr']['items'];

$imgPath = $_ABSOLUTE_URL."share/widgets/blocknotes/img/";

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Modifica appunto</title>
<?php
include_once($_BASE_PATH."var/objects/fckeditor/index.php");
include_once($_BASE_PATH."include/js/gshell.php");
?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/blocknotes/css/common.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/blocknotes/css/edit.css" type="text/css" />

</head><body>
<div class="default-widget" style="width:640px;height:480px">
 <h3 class="header"><?php echo $docInfo['name']; ?></h3> <img onclick="gframe_close();" src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/blocknotes/img/widgetclose.png" class="default-widget-close"/>

 <div class="default-widget-page">
  <table width="100%" cellspacing="0" cellpadding="0" border="0" class="todoedit-topbar">
  <tr><td>Titolo: <input type='text' class='edit' style='width:200px' id="doctitle" value="<?php echo $docInfo['name']; ?>"/></td>
	  <td>Categoria: <select id='catid' style='width:100px'><?php
		 $ret = GShell("dynarc cat-list -ap `".$_AP."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
		 $list = $ret['outarr'];
		 $intoTree = false;
		 for($c=0; $c < count($list); $c++)
		 {
		  echo "<option value='".$list[$c]['id']."'".(($list[$c]['id'] == $docInfo['cat_id']) ? " selected='selected'>" : ">")
			.$list[$c]['name']."</option>";
		  if($list[$c]['id'] == $docInfo['cat_id'])
		   $intoTree = true;
		 }
		 if(!$intoTree)
		 {
		  // get cat info //
		  $ret = GShell("dynarc cat-info -ap `".$_AP."` -id `".$docInfo['cat_id']."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
		  $docInfo['catinfo'] = $ret['outarr'];
		  echo "<option value='".$docInfo['cat_id']."' selected='selected'>".$docInfo['catinfo']['name']."</option>";
		 }
		?></select> <img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/blocknotes/img/select-cat.png" style="cursor:pointer" onclick="selectCat()"/></td>
	  <td width='150'><span class='attachbtn' style="margin-right:10px" onclick="uploadFile()">Carica un allegato</span></td></tr>
  </table>
  <table width="100%" cellspacing="0" cellpadding="0" border="0">
  <tr><td valign="top" width="500"><textarea style="width:500px;height:350px" id="description"><?php echo $docInfo['desc']; ?></textarea></td>
	  <td valign="top">
	   <div class="attachments-header">Lista degli allegati</div>
	   <div class="attachments-list" id='attachments-list'>
	   <?php 
		for($c=0; $c < count($docInfo['attachments']); $c++)
		{
		 $attachment = $docInfo['attachments'][$c];
		 $icon = $attachment['icons']['size48x48'] ? $attachment['icons']['size48x48'] : "share/mimetypes/48x48/file.png";
		 echo "<div class='attachment' id='".$attachment['id']."' filename='".str_replace($_USERS_HOMES.$_SESSION['HOMEDIR'],"",$attachment['url'])."'>";
		 echo "<img src='".$imgPath."delete.gif' class='attachment-delete' title='Elimina questo allegato' onclick='deleteAttachment(this)'/>";
		 echo "<img src='".$_ABSOLUTE_URL.$icon."' class='icon' onclick='openLink(this.parentNode)'/> <div class='title' onclick='openLink(this.parentNode)'>".$attachment['name']."</div></div>";
		}
	   ?>
	   </div>
	  </td></tr>
  </table>
 </div>

 <div class="default-widget-footer" style="clear:both;margin-top:10px">
  <span class="left-button blue" onclick="submit()">Salva</span> 
  <span class="left-button gray" onclick="gframe_close()">Chiudi</span>
  <span class="right-button red" onclick="deleteNote()">Elimina</span>  
 </div>

</div>

<script>
var AP = "<?php echo $_AP; ?>";
var ID = "<?php echo $_REQUEST['id']; ?>";
var oFCKeditor = null;
var editorIsLoaded=true;
var editorMode=0;

function bodyOnLoad()
{
 gframe_cachecontentsload(document.getElementById('description').value);

}

function gframe_cachecontentsload(contents)
{
 document.getElementById('description').innerHTML = contents;
 var sSkinPath = "<?php echo $_BASE_PATH; ?>../var/objects/fckeditor/editor/skins/office2003/";
 oFCKeditor = new FCKeditor('description') ;
 oFCKeditor.ToolbarSet = "Small";
 oFCKeditor.BasePath	= "<?php echo $_BASE_PATH; ?>var/objects/fckeditor/";
 oFCKeditor.Config['SkinPath'] = sSkinPath ;
 oFCKeditor.Config['PreloadImages'] =
				sSkinPath + 'images/toolbar.start.gif' + ';' +
				sSkinPath + 'images/toolbar.end.gif' + ';' +
				sSkinPath + 'images/toolbar.bg.gif' + ';' +
				sSkinPath + 'images/toolbar.buttonarrow.gif' ;
 oFCKeditor.Height = 360;
 oFCKeditor.ReplaceTextarea();
}

function renameTodo()
{
 var title = prompt("Rinomina",document.getElementById("todotitle").innerHTML);
 if(!title)
  return;
 document.getElementById("todotitle").innerHTML = title;
}

function submit()
{
 var oEditor = FCKeditorAPI.GetInstance('description');
 var contents = oEditor.GetXHTML();
 var title = document.getElementById("doctitle").value;
 var catId = document.getElementById("catid").value;

 var sh = new GShell();
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 sh.sendCommand("dynarc edit-item -ap '"+AP+"' -id '"+ID+"' -name `"+title+"` -cat `"+catId+"` -desc `"+contents+"`");
}

function selectCat()
{
 var sel = document.getElementById('catid');

 var sh = new GShell();
 sh.OnOutput = function(o,catId){
	 if(!catId) return;
	 while(sel.options.length)
	  sel.removeChild(sel.options[0]);

	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){
		 if(!a) return;
		 for(var c=0; c < a.length; c++)
		 {
		  var opt = document.createElement('OPTION');
		  opt.value = a[c]['id'];
		  opt.innerHTML = a[c]['name'];
		  sel.appendChild(opt);
		 }
		 sel.value = catId;
		}

	 sh2.sendCommand("dynarc cat-list -ap `"+AP+"`");
	}

 sh.sendCommand("gframe -f dynarc.categorySelect -params `ap="+AP+"`");
}

function uploadFile()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var files = a['files'];
	 var qry = "";
	 for(var c=0; c < files.length; c++)
	 {
	  var file = files[c];
	  qry+= " || dynattachments add -ap '"+AP+"' -refid `<?php echo $docInfo['id']; ?>` -name '"+file['name']+"' -url '"+file['fullname']+"'";
	 }

	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a,rarr){
		 if(!a) return;
		 if(!rarr)
		  var rarr = new Array();
		 rarr.push(a);
		 for(var c=0; c < rarr.length; c++)
		 {
		  var div = document.createElement('DIV');
	  	  div.className = "attachment";
		  div.setAttribute('filename',rarr[c]['url'].replace("<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR']; ?>",""));
	  	  div.id = rarr[c]['id'];
		  var icon = (rarr[c]['icons'] && rarr[c]['icons']['size48x48']) ? rarr[c]['icons']['size48x48'] : "share/mimetypes/48x48/file.png";
		  var html = "<img src='"+ABSOLUTE_URL+"share/widgets/blocknotes/img/delete.gif' class='attachment-delete' title='Elimina questo allegato' onclick='deleteAttachment(this)'/"+">";
		  html+= "<img src='"+ABSOLUTE_URL+icon+"' class='icon'/"+" onclick='openLink(this.parentNode)'> <div class='title' onclick='openLink(this.parentNode)'>"+rarr[c]['name']+"</div>";
		  div.innerHTML = html;
		  document.getElementById('attachments-list').appendChild(div);
		 }

		}
	 
	 sh2.sendCommand(qry.substr(4));
	}

 sh.sendCommand("gframe -f fileupload -params `allowmultiple=true&destpath=blocknotes/`");
}

function deleteAttachment(img)
{
 var div = img.parentNode;
 if(!confirm("Sei sicuro di voler rimuovere questo allegato?"))
  return;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 div.parentNode.removeChild(div);
	}

 sh.sendCommand("dynattachments delete -id '"+div.id+"' -r");
}

function openLink(div)
{
 document.location.href = ABSOLUTE_URL+"getfile.php?file="+div.getAttribute('filename');
}

function deleteNote()
{
 if(!confirm("Sei sicuro di voler eliminare questo appunto?"))
  return;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 a['trashed'] = true;
	 gframe_close(o,a);
	}

 sh.sendCommand("dynarc delete-item -ap `"+AP+"` -id `"+ID+"`");
}
</script>
</body></html>
<?php

