<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-05-2013
 #PACKAGE: blocknotes-module
 #DESCRIPTION: New note form.
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : "blocknotes";
$imgPath = $_ABSOLUTE_URL."share/widgets/blocknotes/img/";
$_CT = $_REQUEST['ct'] ? $_REQUEST['ct'] : "";
$_CATID = $_REQUEST['cat'] ? $_REQUEST['cat'] : 0;

if($_CT || $_CATID)
{
 $ret = GShell("dynarc cat-info -ap `".$_AP."`".($_CT ? " -tag '".$_CT."'" : " -id '".$_CATID."'"),$_REQUEST['sessid'],$_REQUEST['shellid']);
 if(!$ret['error'])
  $catInfo = $ret['outarr'];
}


?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Crea un nuovo appunto</title>
<?php
include_once($_BASE_PATH."var/objects/fckeditor/index.php");
include_once($_BASE_PATH."include/js/gshell.php");
?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/blocknotes/css/common.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/blocknotes/css/edit.css" type="text/css" />

</head><body>
<div class="default-widget" style="width:480px;height:110px">
 <h3 class="header">Crea un nuovo appunto</h3> <img onclick="gframe_close();" src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/blocknotes/img/widgetclose.png" class="default-widget-close"/>

 <div class="default-widget-page">
  <table width="100%" cellspacing="0" cellpadding="0" border="0" class="todoedit-topbar">
  <tr><td>Titolo: <input type='text' class='edit' style='width:200px' id="doctitle" value="<?php echo $docInfo['name']; ?>"/></td>
	  <td>Categoria: <select id='catid' style='width:100px'><?php
		 $ret = GShell("dynarc cat-list -ap `".$_AP."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
		 $list = $ret['outarr'];
		 for($c=0; $c < count($list); $c++)
		  echo "<option value='".$list[$c]['id']."'".(($list[$c]['id'] == $catInfo['id']) ? " selected='selected'>" : ">")
			.$list[$c]['name']."</option>";
		?></select> <img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/blocknotes/img/select-cat.png" style="cursor:pointer" onclick="selectCat()"/></td>
	  </tr>
  </table>
 </div>

 <div class="default-widget-footer" style="clear:both;margin-top:10px">
  <span class="left-button gray" onclick="gframe_close()">Annulla</span> 
  <span class="right-button blue" onclick="submit()">Avanti &raquo;</span> 
 </div>

</div>

<script>
var AP = "<?php echo $_AP; ?>";

function bodyOnLoad()
{
 window.setTimeout(function(){document.getElementById('doctitle').focus();}, 500);
}

function submit()
{
 var title = document.getElementById("doctitle").value;
 var catId = document.getElementById("catid").value;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var sh2 = new GShell();
	 sh2.OnOutput = function(oo,aa){gframe_close(oo,aa);}
	 sh2.sendCommand("gframe -f blocknotes/edit -params `id="+a['id']+"`");
	}

 sh.sendCommand("dynarc new-item -ap '"+AP+"' -name `"+title+"` -cat `"+catId+"`");
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
	  qry+= " || dynattachments add -ap '"+AP+"' -id `<?php echo $docInfo['id']; ?>` -name '"+file['name']+"' -url '"+file['fullname']+"'";
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
</script>
</body></html>
<?php

