<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-07-2013
 #PACKAGE: gmart
 #DESCRIPTION: Edit catalog form.
 #VERSION: 2.2beta
 #CHANGELOG: 24-07-2013 : Aggiunto scheda parametri.
			 04-02-2013 : Bug fix remove archive.
 #DEPENDS: guploader
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$ap = $_REQUEST['ap'];
$id = $_REQUEST['id'];

 $ret = GShell("dynarc archive-info".($ap ? " -ap `".$ap."`" : " -id `".$id."`"),$_REQUEST['sessid'],$_REQUEST['shellid']);
 if($ret['error'])
  return;

 $catInfo = $ret['outarr'];
 $ap = $catInfo['prefix'];
 $id = $catInfo['id'];

 /* GET OWNER */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT fullname FROM gnujiko_users WHERE id='".$catInfo['modinfo']['uid']."'");
 $db->Read();
 $Owner = $db->record['fullname'];
 $db->Close();
 $catInfo['owner'] = $Owner;
 $mod = $catInfo['modinfo']['mod'];

$sessInfo = sessionInfo($_REQUEST['sessid']);

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Edit catalog</title>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>"; var USER_HOME = "<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR']; ?>/";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/edit-catalog.css" type="text/css" />
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
</head><body>

<table width="567" height="567" cellspacing="0" cellpadding="0" border="0" class="edit-category-form">
<tr><td class="header-left"><span style="margin-left:20px;">Propriet&agrave; catalogo:</span></td>
	<td class="header-top">
			<div class="title" id="title-outer"><span id="title" onclick="rename()"><?php echo html_entity_decode($catInfo['name'],ENT_QUOTES,'UTF-8'); ?></span></div>
			<span id="titleedit" style="display:none;" class="editinput"><span class="editinput-inner"><input type="text" id="title-ed" value="<?php echo html_entity_decode($catInfo['name'],ENT_QUOTES,'UTF-8'); ?>" style="width:240px;"/></span></span>
		</td>
	<td class="header-right"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/widget-close.png" onclick="gframe_close()" class="close-btn"/></td></tr>

<tr><td colspan="3" valign="top" class="contents"><div class="contents">
	<ul class="nav" id="navmenu">
	 <li class="selected" id="nav-properties" onclick="selectPage(this)"><span>Propriet&agrave;</span></li>
	 <li id="nav-thumbnail" onclick="selectPage(this)"><span>Immagine di anteprima</span></li>
	 <li id="nav-settings" onclick="selectPage(this)"><span>Settaggi</span></li>
	</ul>

	<div class="page" id="page-properties" style="background:url(img/catalog-bg.png) center center no-repeat;">
	 <table class="prop-table" width='100%' height='100%' cellspacing="0" cellpadding="5" border="0">
	  <tr><td width='50%' style="border-right:1px solid #cccccf;border-bottom:1px solid #cccccf;" valign="top">
			<span class='tit'>Informazioni</span><br/>
			<div class="catinfo">
			ID: <b><?php echo $catInfo['id']; ?></b><br/>
			Creato da: <b><?php echo $Owner; ?></b><br/>
			<br/>
			<span class='tit'>Colore</span>&nbsp;<select id='theme-color'>
			 <?php
			 $options = array("light-green"=>"Verde chiaro", "green"=>"Verde", "red"=>"Rosso", "orange"=>"Arancione", "light-orange"=>"Arancione chiaro",
				"yellow"=>"Giallo", "blue"=>"Blu", "light-blue"=>"Azzurro", "violet"=>"Viola", "pink"=>"Rosa", "maroon"=>"Marrone",
				"skin"=>"Rosa chiaro", "light-gray"=>"Grigio chiaro", "gray"=>"Grigio", "black"=>"Nero");
			 while(list($k,$v) = each($options))
			  echo "<option value='".$k."'".($catInfo['params']['gmart-theme'] == $k ? " selected='selected'>" : ">").$v."</option>";
			 ?>
			</select>
			</div>
		  </td><td style="border-bottom:1px solid #cccccf;" valign="top">
			<span class='tit'>Permessi predefiniti</span><br/>
		    <div class='permshead'>CARTELLE</div>
			<table width='100%' cellspacing='0' cellpadding='0' border='0' class='permstable'>
			<tr><th>&nbsp;</th><th>Proprietario</th><th>Gruppo</th><th>Altri</th></tr>
			<tr><td>Leggere</td>
				<td align='center'><input type='checkbox' id='cr1' <?php echo ($catInfo['def_cat_perms'][0] & 4) ? "checked='true'" : ""; ?>/></td>
				<td align='center'><input type='checkbox' id='cr2' <?php echo ($catInfo['def_cat_perms'][1] & 4) ? "checked='true'" : ""; ?>/></td>
				<td align='center'><input type='checkbox' id='cr3' <?php echo ($catInfo['def_cat_perms'][2] & 4) ? "checked='true'" : ""; ?>/></td></tr>
			<tr><td>Scrivere</td>
				<td align='center'><input type='checkbox' id='cw1' <?php echo ($catInfo['def_cat_perms'][0] & 2) ? "checked='true'" : ""; ?>/></td>
				<td align='center'><input type='checkbox' id='cw2' <?php echo ($catInfo['def_cat_perms'][1] & 2) ? "checked='true'" : ""; ?>/></td>
				<td align='center'><input type='checkbox' id='cw3' <?php echo ($catInfo['def_cat_perms'][2] & 2) ? "checked='true'" : ""; ?>/></td></tr>
			</table>
			
		    <div class='permshead' style='margin-top:20px;'>ARTICOLI</div>
			<table width='100%' cellspacing='0' cellpadding='0' border='0' class='permstable'>
			<tr><th>&nbsp;</th><th>Proprietario</th><th>Gruppo</th><th>Altri</th></tr>
			<tr><td>Leggere</td>
				<td align='center'><input type='checkbox' id='ir1' <?php echo ($catInfo['def_item_perms'][0] & 4) ? "checked='true'" : ""; ?>/></td>
				<td align='center'><input type='checkbox' id='ir2' <?php echo ($catInfo['def_item_perms'][1] & 4) ? "checked='true'" : ""; ?>/></td>
				<td align='center'><input type='checkbox' id='ir3' <?php echo ($catInfo['def_item_perms'][2] & 4) ? "checked='true'" : ""; ?>/></td></tr>
			<tr><td>Scrivere</td>
				<td align='center'><input type='checkbox' id='iw1' <?php echo ($catInfo['def_item_perms'][0] & 2) ? "checked='true'" : ""; ?>/></td>
				<td align='center'><input type='checkbox' id='iw2' <?php echo ($catInfo['def_item_perms'][1] & 2) ? "checked='true'" : ""; ?>/></td>
				<td align='center'><input type='checkbox' id='iw3' <?php echo ($catInfo['def_item_perms'][2] & 2) ? "checked='true'" : ""; ?>/></td></tr>
			</table>

		  </td></tr>
	  <tr><td colspan='2'>
			<span class='tit'>Permessi di accesso</span><br/>
			<div class='catinfo'>
			 <table width='100%' border='0' class='permissiontable'>
			  <tr><td><b>PROPRIETARIO</b></td>
				  <td><b class='black'><?php echo $Owner; ?></b></td>
				  <td align='right'><i>accesso:</i> <select id='owner_access'><?php
									echo "<option value='4'".($mod[0] == 4 ? " selected='selected'>" : ">")."Leggere soltanto</option>";
									echo "<option value='6'".($mod[0] == 6 ? " selected='selected'>" : ">")."Leggere e scrivere</option>";
									?></select></td></tr>
			  <tr><td><b>GRUPPO</b></td>
				  <td><select id='group_id' style='width:150px;'><?php
									$db = new AlpaDatabase();
									$db->RunQuery("SELECT name FROM gnujiko_groups WHERE id='".$catInfo['modinfo']['gid']."'");
									$db->Read();
									echo "<option value='".$catInfo['modinfo']['gid']."' selected='selected'>".$db->record['name']."</option>";

									if($catInfo['modinfo']['gid'] != $_SESSION['GID'])
									{
									 $db->RunQuery("SELECT name FROM gnujiko_groups WHERE id='".$_SESSION['GID']."'");
									 $db->Read();
									 echo "<option value='".$_SESSION['GID']."'>".$db->record['name']."</option>";
									}
									$db->Close();

									if($sessInfo['uname'] == "root")
									{
									 $userGroups = array();
									 $db = new AlpaDatabase();
									 $db->RunQuery("SELECT id,name FROM gnujiko_groups WHERE 1 ORDER BY name ASC");
									 while($db->Read())
									 {
									  $userGroups[] = array('id'=>$db->record['id'], 'name'=>$db->record['name']);
									 }
									 $db->Close();
									}
									else
									 $userGroups = _userGroups();
									for($c=0; $c < count($userGroups); $c++)
									{
									 if($userGroups[$c]['id'] == $catInfo['modinfo']['gid'])
									  continue;
									 echo "<option value='".$userGroups[$c]['id']."'>".$userGroups[$c]['name']."</option>";
									}
									?></select></td>
				  <td align='right'><i>accesso:</i> <select id='group_access'><?php
									echo "<option value='0'".($mod[1] == 0 ? " selected='selected'>" : ">")."Nessuno</option>";
									echo "<option value='4'".($mod[1] == 4 ? " selected='selected'>" : ">")."Leggere soltanto</option>";
									echo "<option value='6'".($mod[1] == 6 ? " selected='selected'>" : ">")."Leggere e scrivere</option>";
									?></select></td></tr>
			  <tr><td><b>TUTTI GLI ALTRI</b></td>
				  <td>&nbsp;</td>
				  <td align='right'><i>accesso:</i> <select id='other_access'><?php
									echo "<option value='0'".($mod[2] == 0 ? " selected='selected'>" : ">")."Nessuno</option>";
									echo "<option value='4'".($mod[2] == 4 ? " selected='selected'>" : ">")."Leggere soltanto</option>";
									echo "<option value='6'".($mod[2] == 6 ? " selected='selected'>" : ">")."Leggere e scrivere</option>";
									?></select></td></tr>
			 </table>
			</div>
		  </td></tr>
	 </table>
	</div>

	<div class="page" id="page-thumbnail" style="display:none;">
	 <h3 class='lightblue'>Scegli un&lsquo;immagine da utilizzare come anteprima di questa categoria.</h3>
	 <br/>
	 <table width='100%' border='0' class='thumbtable'>
	  <tr><td valign='top' align='center' width='20%'><input type='radio' name='thumbnail' <?php if(!$catInfo['thumb_img'] || !$catInfo['thumb_mode']) echo "checked='true'"; ?> id='thumbnail-no'/><i>Nessuna</i></td>
		  <td valign='top' align='center'><input type='radio' name='thumbnail' id='thumbnail-first' <?php if($catInfo['thumb_mode']) echo "checked='true'"; ?>/>Utilizza quella del primo articolo</td>
		  <td valign='top' width='140'><input type='radio' name='thumbnail' id='thumbnail-custom' <?php if(!$catInfo['thumb_mode'] && $catInfo['thumb_img']) echo "checked='true'"; ?>/>Personalizzata</td></tr>
	  <tr><td align='center' valign='middle'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/no-thumbnail.png"/></td>
		  <td align='center' valign='middle'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/photo.png"/></td>
		  <td>
		   <?php
			if($catInfo['thumb_img'])
			 $thumb = $catInfo['thumb_img'];
		   ?>
		   <div class='thumb-preview' id='thumb-preview' <?php if($thumb) echo "style=\"background-image: url(".$_ABSOLUTE_URL.$thumb.");\""; ?>></div>

		   <ul class='basicbuttons' style="clear:both;float:left;margin-top:5px;">
  			<li><span onclick="uploadImage()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/add.gif" border='0'/>Carica...</span></li>
 		   </ul>
		  </td></tr>
	 </table>
	</div>

	<div class="page" id="page-settings" style="display:none;">
	 <h3 class='lightblue'>Parametri archivio personalizzabili</h3>
	 <br/>
	 <table border="0" class="simpletable">
	 <tr><td class='title'>Vista predefinita:</td>
		 <td><input type='radio' name='defaultview' value='thumbnails' <?php if(!$catInfo['params']['defaultview'] || ($catInfo['params']['defaultview'] == "thumbnails")) echo "checked='true'"; ?>/>anteprime 
			 <input type='radio' name='defaultview' value='smallthumb' <?php if($catInfo['params']['defaultview'] == "smallthumb") echo "checked='true'"; ?>/>miniature 
			 <input type='radio' name='defaultview' value='list' <?php if($catInfo['params']['defaultview'] == "list") echo "checked='true'"; ?>/>lista</td></tr>

	 <tr><td colspan='2'><hr/></td></tr>

	 <tr><td class='title'>Immagini di anteprima:</td>
		 <td><input type='radio' name='thumbmode' value='always' <?php if(!$catInfo['params']['thumbmode'] || ($catInfo['params']['thumbmode'] == "always")) echo "checked='true'"; ?>/>mostra sempre 
			 <input type='radio' name='thumbmode' value='notall' <?php if($catInfo['params']['thumbmode'] == "notall") echo "checked='true'"; ?>/>solo articoli con immagine
			 <input type='radio' name='thumbmode' value='never' <?php if($catInfo['params']['thumbmode'] == "never") echo "checked='true'"; ?>/>nascondi</td></tr>

	 </table>
	</div>

	</div></td></tr>

<tr><td class="footer-left" valign="top">
	 <ul class='basicbuttons' style="margin-left:15px;margin-top:4px;float:left;">
	  <li><span onclick='submit()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/save.gif" border='0'/>Salva</span></li>
	  <li><span onclick='deleteCatalog()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/delete.png" border='0'/>Elimina</span></li>
	 </ul>
	</td>
	<td class="footer-right" colspan="2" valign="top">
	 <ul class='basicbuttons' style="float:right;margin-top:4px;margin-right:5px;">
	  <li><span onclick="gframe_close()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/exit.png" border='0'/>Chiudi</span></li>
	 </ul>
	</td></tr>

</table>

<script>
var ARCHIVE_PREFIX = "<?php echo $ap; ?>";
var ARCHIVE_ID = "<?php echo $id; ?>";

var LAST_UPLOADED_FILENAME = "";

function selectPage(li)
{
 var ul = document.getElementById('navmenu');
 var list = ul.getElementsByTagName('LI');
 for(var c=0; c < list.length; c++)
 {
  if(list[c] != li)
  {
   list[c].className = "";
   document.getElementById("page-"+list[c].id.substr(4)).style.display = "none";
  }
 }
 li.className = "selected";
 document.getElementById("page-"+li.id.substr(4)).style.display = "";
}

function rename()
{
 document.getElementById('title-outer').style.display = "none";
 document.getElementById('titleedit').style.display = "";

 var ed = document.getElementById('title-ed');
 ed.focus();
 ed.select();

 ed.onblur = function(){
	 document.getElementById('title').innerHTML = this.value;
	 document.getElementById('title-outer').style.display = "";
	 document.getElementById('titleedit').style.display = "none";
	}

 ed.onchange = function(){this.onblur();}
}

function uploadImage()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;

	 var dstPath = "image/gmart/categories/thumbnails/";

	 var sh2 = new GShell();
	 sh2.OnFinish = function(){
		 var fileName = USER_HOME+dstPath+"category-<?php echo $catInfo['id']; ?>-thumb."+a['files'][0]['extension'];
		 document.getElementById('thumb-preview').style.backgroundImage = "url(<?php echo $_ABSOLUTE_URL; ?>"+fileName+")";
		}

	 switch(a['mode'])
	 {
	  case 'UPLOAD' : {
		 var fileName = a['files'][0]['fullname'].replace(USER_HOME,"");
		 var dstFileName = dstPath+"category-<?php echo $catInfo['id']; ?>."+a['files'][0]['extension'];
		 sh2.sendCommand("mv `"+fileName+"` `"+dstFileName+"`");
		} break;

	  case 'FROM_SERVER' : {
		 var fileName = a['files'][0]['fullname'].replace(USER_HOME,"");
		 var dstFileName = dstPath+"category-<?php echo $catInfo['id']; ?>."+a['files'][0]['extension'];
		 if(fileName != dstFileName)
		  sh2.sendCommand("cp `"+fileName+"` `"+dstFileName+"`");
		} break;

	 }

	 LAST_UPLOADED_FILENAME = USER_HOME+dstFileName;
	  
	 sh2.sendCommand("gd resize -i `"+dstFileName+"` -o `"+dstPath+"category-<?php echo $catInfo['id']; ?>-thumb."+a['files'][0]['extension']+"` -w 128");
	}
 sh.sendCommand("gframe -f imageupload -params `destpath=tmp`");
}

function submit()
{
 var title = document.getElementById('title-ed').value;
 var themeColor = document.getElementById('theme-color').value;

 var qry = "dynarc edit-archive -id `"+ARCHIVE_ID+"` -name `"+title+"` -params `gmart-theme="+themeColor;
 
 var tmp = document.getElementsByName("defaultview");
 for(var c=0; c < tmp.length; c++)
 {
  if(tmp[c].checked)
  {
   qry+= "&defaultview="+tmp[c].value;
   break;
  }
 }

 var tmp = document.getElementsByName("thumbmode");
 for(var c=0; c < tmp.length; c++)
 {
  if(tmp[c].checked)
  {
   qry+= "&thumbmode="+tmp[c].value;
   break;
  }
 }

 qry+= "`";
 

 /* SAVE PERMISSIONS */
 var mod = "";
 mod+= document.getElementById('owner_access').value.toString();
 mod+= document.getElementById('group_access').value.toString();
 mod+= document.getElementById('other_access').value.toString();
 qry+= " -perms "+mod;
 if(document.getElementById('group_id').value)
  qry+= " -groupid "+document.getElementById('group_id').value;

 /* SAVE DEFAULT CAT PERMS */
 var mod = "";
 var om = (document.getElementById('cr1').checked ? 4 : 0) + (document.getElementById('cw1').checked ? 2 : 0);
 var gm = (document.getElementById('cr2').checked ? 4 : 0) + (document.getElementById('cw2').checked ? 2 : 0);
 var am = (document.getElementById('cr3').checked ? 4 : 0) + (document.getElementById('cw3').checked ? 2 : 0);
 mod = om.toString()+gm.toString()+am.toString();
 qry+= " --default-cat-perms "+mod;
  
 /* SAVE DEFAULT ITEM PERMS */
 var mod = "";
 var om = (document.getElementById('ir1').checked ? 4 : 0) + (document.getElementById('iw1').checked ? 2 : 0);
 var gm = (document.getElementById('ir2').checked ? 4 : 0) + (document.getElementById('iw2').checked ? 2 : 0);
 var am = (document.getElementById('ir3').checked ? 4 : 0) + (document.getElementById('iw3').checked ? 2 : 0);
 mod = om.toString()+gm.toString()+am.toString();
 qry+= " --default-item-perms "+mod;

 /* SAVE THUMBNAIL */
 if(document.getElementById('thumbnail-no').checked == true)
  qry+= " -thumb-mode 0 -thumb-img ''";
 else if(document.getElementById('thumbnail-first').checked == true)
  qry+= " -thumb-mode 1 -thumb-img ''";
 else if((document.getElementById('thumbnail-custom').checked == true) && (LAST_UPLOADED_FILENAME != ""))
  qry+= " -thumb-mode 0 -thumb-img '"+LAST_UPLOADED_FILENAME+"'";

 var sh = new GShell();
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 sh.sendSudoCommand(qry);
}

function deleteCatalog()
{
 if(!confirm("Sei sicuro di voler eliminare questo catalogo?"))
  return false;

 var sh = new GShell();
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 sh.sendSudoCommand("dynarc delete-archive -id `<?php echo $catInfo['id']; ?>` -r");
}
</script>
</body></html>
<?php


