<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-01-2013
 #PACKAGE: gmart
 #DESCRIPTION: Edit category form.
 #VERSION: 2.2beta
 #CHANGELOG: 28-01-2013 - Bug fix vari.
			 12-01-2013 : Bug fix. 
 #DEPENDS: guploader
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$ap = $_REQUEST['ap'] ? $_REQUEST['ap'] : "gmart";
$id = $_REQUEST['id'];

if($id)
{
 $ret = GShell("dynarc cat-info -ap `".$ap."` -id `".$id."` -extget `idoc,thumbnails.mode`",$_REQUEST['sessid'],$_REQUEST['shellid']);
 if($ret['error'])
  return;

 $catInfo = $ret['outarr'];

 /* GET OWNER */
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT fullname FROM gnujiko_users WHERE id='".$catInfo['modinfo']['uid']."'");
 $db->Read();
 $Owner = $db->record['fullname'];
 $db->Close();
 $catInfo['owner'] = $Owner;
 $mod = $catInfo['modinfo']['mod'];
 $path = "";
 if($catInfo['parent_id'])
 {
  $ret = GShell("dynarc cat-info -ap $_ARCHIVE_PREFIX -id ".$catInfo['parent_id']." --include-path");
  $parentInfo = $ret['outarr'];
  if($parentInfo['pathway'])
  {
   for($c=0; $c < count($parentInfo['pathway']); $c++)
	$path.= $parentInfo['pathway'][$c]['name']."/";
  }
  $path.= $parentInfo['name'];
 }

}

$sessInfo = sessionInfo($_REQUEST['sessid']);

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Edit category</title>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>"; var USER_HOME = "<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR']; ?>/";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/edit-cat.css" type="text/css" />
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
</head><body>

<table width="567" height="567" cellspacing="0" cellpadding="0" border="0" class="edit-category-form">
<tr><td class="header-left"><span style="margin-left:20px;">Propriet&agrave; categoria:</span></td>
	<td class="header-top">
			<div class="title" id="title-outer"><span id="title" onclick="rename()"><?php echo html_entity_decode($catInfo['name'],ENT_QUOTES,'UTF-8'); ?></span></div>
			<span id="titleedit" style="display:none;" class="editinput"><span class="editinput-inner"><input type="text" id="title-ed" value="<?php echo html_entity_decode($catInfo['name'],ENT_QUOTES,'UTF-8'); ?>" style="width:240px;"/></span></span>
		</td>
	<td class="header-right"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/widget-close.png" onclick="gframe_close()" class="close-btn"/></td></tr>

<tr><td colspan="3" valign="top" class="contents"><div class="contents">
	<ul class="nav" id="navmenu">
	 <li class="selected" id="nav-properties" onclick="selectPage(this)"><span>Propriet&agrave;</span></li>
	 <li id="nav-thumbnail" onclick="selectPage(this)"><span>Immagine di anteprima</span></li>
	 <li id="nav-idocs" onclick="selectPage(this)"><span>Schede prodotti</span></li>
	</ul>

	<div class="page" id="page-properties" style="background:url(img/folder-bg.png) center center no-repeat;">
	 <table class="prop-table" width='100%' height='100%' cellspacing="0" cellpadding="5" border="0">
	  <tr><td colspan="2" style="border-bottom:1px solid #cccccf;height:40px;" valign="middle">
		   <span class="tit">Codice:</span> 
		   <span class="editinput" style="width:130px;"><span class="editinput-inner"><input type="text" id="code" value="<?php echo $catInfo['code']; ?>" style="width:100px;"/></span></span>
		   <span class="tit" style="margin-left:40px;">Categoria di app.:</span>&nbsp;<select id="parent-cat-select" style="width:120px;" onchange="parentCatSelectChange(this)">
			 <option value='0'>Cartella principale</option>
			 <option value='other'>Altro...</option>
			 <optgroup label="Categorie principali">
			 <?php
			 $ret = GShell("dynarc cat-list -ap `".$ap."`",$_REQUEST['sessid'], $_REQUEST['shellid']);
			 for($c=0; $c < count($ret['outarr']); $c++)
			  echo "<option value='".$ret['outarr'][$c]['id']."'".($ret['outarr'][$c]['id'] == $catInfo['parent_id'] ? " selected='selected'>" : ">").$ret['outarr'][$c]['name']."</option>";
			 ?>
			 </optgroup>
			</select>
		  </td></tr>
	  <tr><td width='50%' style="border-right:1px solid #cccccf;border-bottom:1px solid #cccccf;" valign="top">
			<span class='tit'>Informazioni</span><br/>
			<div class="catinfo">
			ID: <b><?php echo $catInfo['id']; ?></b><br/>
			Creato da: <b><?php echo $Owner; ?></b><br/>
			Data creazione: <b><?php echo date('d/m/Y H:i',$catInfo['ctime']); ?></b><br/>
			<?php
			if($catInfo['mtime'])
			 echo "Ultima modifica: <b>".date('d/m/Y H:i',$catInfo['mtime'])."</b><br/>";
			?>
			</div>
		  </td><td style="border-bottom:1px solid #cccccf;" valign="top">
			<span class='tit'>Ordinamento predefinito</span><br/>
			<div class="catinfo" id='deforderinglist'>
			 <input type='radio' name='defordering' value='' <?php if(!$catInfo['def_order_field']) echo "checked='true'"; ?>/>Manuale<br/>
	  		 <?php
	  		 $orderings = array('id'=>"ID",'name'=>"Nome",'ctime'=>"Data creazione",'mtime'=>"Ultima modifica");
	  		 while(list($k,$v) = each($orderings))
	   		  echo "<input type='radio' name='defordering' value='".$k."' ".(($catInfo['def_order_field'] == $k) ? "checked='true'/> " : "/> ").$v."<br/>";
	  		 ?>
			</div>
			<select id='defordermethod'>
			 <option value='ASC' <?php if($catInfo['def_order_method'] == "ASC") echo "selected='selected'"; ?>>A-Z</option>
			 <option value='DESC' <?php if($catInfo['def_order_method'] != "ASC") echo "selected='selected'"; ?>>Z-A</option>
	  		</select>

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
									$db->RunQuery("SELECT name FROM gnujiko_groups WHERE id='".$_SESSION['GID']."'");
									$db->Read();
									echo "<option value='".$_SESSION['GID']."'>".$db->record['name']."</option>";
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
									 echo "<option value='".$userGroups[$c]['id']."'".($userGroups[$c]['id'] == $catInfo['modinfo']['gid'] ? " selected='selected'>" : ">").$userGroups[$c]['name']."</option>";
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

	<div class="page" id="page-idocs" style="display:none;">
	 <div class="idoc-list-container" style="height:390px;overflow:auto;">
	 <table width='100%' border='0' class="idoc-list" cellspacing='0' cellpadding='10' id='idoc-list'>
	 <tr><td colspan='2'><h3 class='lightblue'>Schede interattive predefinite</h3></td>
		 <td align='right'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/add-btn-orange.png" class="add-idoc-btn" onclick="idocAdd()" title="Aggiungi"/></td></tr>
	 <?php
	 for($c=0; $c < count($catInfo['def_item_idocs']); $c++)
	 {
	  $idoc = $catInfo['def_item_idocs'][$c];
	  $ret = GShell("dynarc item-info -aid `".$idoc['aid']."` -id `".$idoc['id']."` -get thumbdata",$_REQUEST['sessid'], $_REQUEST['shelli']);
	  if(!$ret['error'])
	  {
	   $docInfo = $ret['outarr'];
	   echo "<tr id='idoc-".$idoc['aid']."_".$idoc['id']."'><td><div class='idoc-thumb'>";
	   if($docInfo['thumbdata'])
		echo "<img src='".$docInfo['thumbdata']."'/>";
	   echo "</div></td>";
	   echo "<td valign='top'><span class='tit'>".$docInfo['name']."</span><br/>";
	   echo "<input type='radio' name='idoc-".$idoc['aid']."_".$idoc['id']."-mode' ".(!$idoc['all'] ? "checked='true'" : "")." onclick='idocApply(this)'/>Applica solo agli articoli di questa categoria.<br/>";
	   echo "<input type='radio' name='idoc-".$idoc['aid']."_".$idoc['id']."-mode' ".($idoc['all'] ? "checked='true'" : "")." onclick='idocApply(this)'/>Applica a tutti gli articoli compresi quelli nelle sottocategorie.";
	   echo "</td><td align='center' valign='middle'><img src='".$_ABSOLUTE_URL."share/widgets/gmart/img/red_delete.png' onclick='idocRemove(".$idoc['aid'].",".$idoc['id'].")' style='cursor:pointer;'/></td></tr>";
	  }
	 }
	 ?>
	 </table>
	 </div>
	</div>

	</div></td></tr>

<tr><td class="footer-left" valign="top">
	 <ul class='basicbuttons' style="margin-left:15px;margin-top:4px;float:left;">
	  <li><span onclick='submit()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/save.gif" border='0'/>Salva</span></li>
	  <li><span onclick='deleteCategory()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/delete.png" border='0'/>Elimina</span></li>
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
var CAT_ID = <?php echo $id ? $id : "0"; ?>;
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
	 //sh2.sendCommand("dynarc edit-cat -ap `gmart` -id `<?php echo $catInfo['id']; ?>` -extset `thumbnails.src='"+USER_HOME+dstFileName+"'`");
	}
 sh.sendCommand("gframe -f imageupload -params `destpath=tmp`");
}

function idocAdd()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 gframe_opacity(100);
	 if(!a) return;
	 /* Check if idoc is already installed */
	 if(document.getElementById('idoc-'+a['aid']+"_"+a['id']))
	  return alert("Scheda già esistente. Non puoi aggiungere due schede dello stesso tipo.");

	 var sh2 = new GShell();
	 sh2.OnOutput = function(){
		 var r = document.getElementById('idoc-list').insertRow(-1);
		 r.insertCell(-1).innerHTML = "<div class='idoc-thumb'>"+(a['thumbdata'] ? "<img src='"+a['thumbdata']+"'/ >" : "")+"</div>";
		 r.cells[0].id = "idoc-"+a['aid']+"_"+a['id'];
		 var html = "<span class='tit'>"+a['name']+"</span><br/ >";
		 html+= "<input type='radio' name='idoc-"+a['aid']+"_"+a['id']+"-mode' checked='true' onclick='idocApply(this)'/ >Applica solo agli articoli di questa categoria.<br/ >";
		 html+= "<input type='radio' name='idoc-"+a['aid']+"_"+a['id']+"-mode' onclick='idocApply(this)'/ >Applica a tutti gli articoli compresi quelli nelle sottocategorie.";
		 r.insertCell(-1).innerHTML = html;
		 r.cells[1].style.verticalAlign="top";
		 r.insertCell(-1).innerHTML = "<img src='"+ABSOLUTE_URL+"share/widgets/gmart/img/red_delete.png' onclick='idocRemove("+a['aid']+","+a['id']+")' style='cursor:pointer;'/ >";
		 r.cells[2].style.textAlign="center";
		 r.cells[2].style.verticalAlign="middle";
		}

	 sh2.sendCommand("dynarc exec-func ext:idoc.add -params `ap="+ARCHIVE_PREFIX+"&cat=<?php echo $catInfo['id']; ?>&idocap=idoc&idocid="+a['id']+"&default=true`");
	}
 sh.sendCommand("gframe -f idoc.choice -params `idocct=GMART`");
 gframe_opacity(40);
}

function idocRemove(aid,id)
{
 var r = document.getElementById('idoc-'+aid+"_"+id);
 var idocName = r.cells[1].getElementsByTagName('SPAN')[0].innerHTML;
 if(!confirm("Sei sicuro di voler rimuovere la scheda '"+idocName+"' ?"))
  return;

 var sh = new GShell();
 sh.OnOutput = function(){
	 document.getElementById('idoc-list').deleteRow(r.rowIndex);
	}
 sh.sendCommand("dynarc exec-func ext:idoc.remove -params `ap="+ARCHIVE_PREFIX+"&idocaid="+aid+"&idocid="+id+"&cat=<?php echo $catInfo['id']; ?>&default=true`");
}

function submit()
{
 var title = document.getElementById('title-ed').value;
 var code = document.getElementById('code').value;

 var qry = "dynarc edit-cat -ap `"+ARCHIVE_PREFIX+"` -id `<?php echo $catInfo['id']; ?>` -name `"+title+"` -code `"+code+"`";

 /* SAVE DEFAULT ORDERING SETTINGS */
 var defolist = document.getElementById('deforderinglist');
 var list = defolist.getElementsByTagName('INPUT');
 for(var c=0; c < list.length; c++)
 {
  if(list[c].checked)
  {
   qry+= " --def-order-field `"+list[c].value+"`";
   c = list.length;
  }
 }
 qry+= " --def-order-method `"+document.getElementById('defordermethod').value+"`";

 /* SAVE PERMISSIONS */
 var mod = "";
 mod+= document.getElementById('owner_access').value.toString();
 mod+= document.getElementById('group_access').value.toString();
 mod+= document.getElementById('other_access').value.toString();
 qry+= " -perms "+mod;
 if(document.getElementById('group_id').value)
  qry+= " -groupid "+document.getElementById('group_id').value;

 var parentId = document.getElementById('parent-cat-select').value;
 if((parentId != "other") && (parentId != <?php echo $catInfo['parent_id'] ? $catInfo['parent_id'] : "0"; ?>))
  qry+= " -parent `"+document.getElementById('parent-cat-select').value+"`";

 var set = new Array();
 var extset = new Array();

 /* SAVE THUMBNAIL */
 if(document.getElementById('thumbnail-no').checked == true)
  set.push("thumb_mode=0,thumb_img=''");
 else if(document.getElementById('thumbnail-first').checked == true)
  set.push("thumb_mode=1,thumb_img=''");
 else if((document.getElementById('thumbnail-custom').checked == true) && (LAST_UPLOADED_FILENAME != ""))
  set.push("thumb_mode=0,thumb_img='"+LAST_UPLOADED_FILENAME+"'");

 if(set.length)
 {
  var q = "";
  for(var c=0; c < set.length; c++)
   q+= ","+set[c];
  qry+= " -set `"+q.substr(1)+"`";
 }

 var sh = new GShell();
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 sh.sendCommand(qry);
}

function deleteCategory()
{
 if(!confirm("Sei sicuro di voler eliminare questa categoria?"))
  return;
 
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 gframe_close(o,a);	 
	}
 sh.sendCommand("dynarc delete-cat -ap `"+ARCHIVE_PREFIX+"` -id `<?php echo $catInfo['id']; ?>` --return-cat-info");
}

function parentCatSelectChange(sel)
{
 if(sel.value == "other")
 {
  var sh = new GShell();
  sh.OnOutput = function(o,catId){
	 if(!catId) return;
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){
		 var opt = document.createElement('OPTION');
		 opt.value = a['id'];
		 opt.innerHTML = a['name'];
		 sel.appendChild(opt);
		 sel.value = a['id'];
		}
	 sh2.sendCommand("dynarc cat-info -ap `"+ARCHIVE_PREFIX+"` -id `"+catId+"`");
	}
  sh.sendCommand("gframe -f dynarc.categorySelect -params `ap="+ARCHIVE_PREFIX+"`");
 }
}

function idocApply(inp)
{
 var str = inp.name.substr(5);
 str = str.substr(0,str.length-5);
 var x = str.split("_");
 var aid = x[0];
 var id = x[1];
 var all = 0;

 var pN = inp.parentNode;
 var list = pN.getElementsByTagName('INPUT');
 for(var c=0; c < list.length; c++)
 {
  if(list[c] == inp)
   all = c;
 }

 var sh = new GShell();
 sh.sendCommand("dynarc exec-func ext:idoc.editdefault -params `ap="+ARCHIVE_PREFIX+"&cat=<?php echo $catInfo['id']; ?>&idocaid="+aid+"&idocid="+id+"&all="+(all ? "true" : "false")+"`");
}
</script>
</body></html>
<?php


