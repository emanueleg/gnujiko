<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-12-2016
 #PACKAGE: gmart
 #DESCRIPTION: Edit category form.
 #VERSION: 2.5beta
 #CHANGELOG: 23-12-2016 : Integrato con scontistica predefinita per cliente.
			 30-07-2016 : Bug fix parentid al salvataggio ed integrazione con transponder.
			 07-01-2015 : Bug fix su cartella immagini categorie.
			 28-01-2013 - Bug fix vari.
			 12-01-2013 : Bug fix. 
 #DEPENDS: guploader
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : "gmart";
$_ID = $_REQUEST['id'];

if($_ID)
{
 $ret = GShell("dynarc cat-info -ap `".$_AP."` -id `".$_ID."` -extget `idoc,thumbnails.mode`",$_REQUEST['sessid'],$_REQUEST['shellid']);
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


// TRANSPONDER
$_TRANSPONDER_SERVERS = null;
if(file_exists($_BASE_PATH.$_SHELL_CMD_PATH."transponder.php"))
{
 $_SERVICE_TAGS = "joomshopping,virtuemart,ebay,amazon";
 $ret = GShell("transponder server-list --service-tags '".$_SERVICE_TAGS."'", $_REQUEST['sessid'], $_REQUEST['shellid']);
 if(!$ret['error']) $_TRANSPONDER_SERVERS = $ret['outarr'];

 $_AUTOSYNC_RULES = array();
 $ret = GShell("transponder get-autosync-rules -ap '".$_AP."' -cat '".$_ID."'", $_REQUEST['sessid'], $_REQUEST['shellid']);
 if(!$ret['error'])	$_AUTOSYNC_RULES = $ret['outarr'];

 for($c=0; $c < count($_TRANSPONDER_SERVERS); $c++)
 {
  $serverId = $_TRANSPONDER_SERVERS[$c]['id'];
  $serviceTag = strtoupper($_TRANSPONDER_SERVERS[$c]['tag']);
  $_TRANSPONDER_SERVERS[$c]['rule'] = 0;

  for($i=0; $i < count($_AUTOSYNC_RULES); $i++)
  {
   if(($_AUTOSYNC_RULES[$i]['server_id'] == $serverId) && (strtoupper($_AUTOSYNC_RULES[$i]['service_tag']) == $serviceTag))
   {
	$_TRANSPONDER_SERVERS[$c]['rule'] = $_AUTOSYNC_RULES[$i]['rule'];
	break;
   }
  }
 }
}


?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Edit category</title>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>"; var USER_HOME = "<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR']; ?>/";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/edit-cat.css" type="text/css" />
<?php
include_once($_BASE_PATH."include/js/gshell.php");
include_once($_BASE_PATH."var/objects/gmutable/index.php");
?>
</head><body>

<table width="567" height="567" cellspacing="0" cellpadding="0" border="0" class="edit-category-form">
<tr><td class="header-left"><span style="margin-left:20px;">Propriet&agrave; categoria:</span></td>
	<td class="header-top">
			<div class="title" id="title-outer"><span id="title" onclick="rename()"><?php echo html_entity_decode($catInfo['name'],ENT_QUOTES,'UTF-8'); ?></span></div>
			<span id="titleedit" style="display:none;" class="spaneditinput"><span class="spaneditinput-inner"><input type="text" id="title-ed" value="<?php echo html_entity_decode($catInfo['name'],ENT_QUOTES,'UTF-8'); ?>" style="width:240px;"/></span></span>
		</td>
	<td class="header-right"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/widget-close.png" onclick="gframe_close()" class="close-btn"/></td></tr>

<tr><td colspan="3" valign="top" class="contents"><div class="contents">
	<ul class="nav" id="navmenu">
	 <li class="selected" id="nav-properties" onclick="selectPage(this)"><span>Propriet&agrave;</span></li>
	 <li id="nav-thumbnail" onclick="selectPage(this)"><span>Immagine di anteprima</span></li>
	 <li id="nav-idocs" onclick="selectPage(this)"><span>Schede prodotti</span></li>
	 <li id="nav-discount" onclick="selectPage(this)"><span>Scontistica</span></li>
	 <?php
	  if($_TRANSPONDER_SERVERS) // da modificare
	  {
	   ?>
	 	<li id="nav-transponder" onclick="selectPage(this)"><span>Pubblica</span></li>
	   <?php
	  }
	  ?>
	</ul>

	<!-- PROPERTIES -->
	<div class="page" id="page-properties" style="background:url(img/folder-bg.png) center center no-repeat;">
	 <table class="prop-table" width='100%' height='100%' cellspacing="0" cellpadding="5" border="0">
	  <tr><td colspan="2" style="border-bottom:1px solid #cccccf;height:40px;" valign="middle">
		   <span class="tit">Codice:</span> 
		   <span class="spaneditinput" style="width:130px;"><span class="spaneditinput-inner"><input type="text" id="code" value="<?php echo $catInfo['code']; ?>" style="width:100px;"/></span></span>
		   <span class="tit" style="margin-left:40px;">Categoria di app.:</span>&nbsp;<select id="parent-cat-select" style="width:120px;" onchange="parentCatSelectChange(this)">
			 <?php
			  if($catInfo['parent_id'])
			  {
			   $db = new AlpaDatabase();
			   $db->RunQuery("SELECT parent_id,name FROM dynarc_".$_AP."_categories WHERE id='".$catInfo['parent_id']."'");
			   if($db->Read() && $db->record['parent_id'])
				echo "<option value='".$catInfo['parent_id']."' selected='selected'>".$db->record['name']."</option>";
			   $db->Close();
			  }
			 ?>
			 <option value='0' <?php if(!$catInfo['parent_id']) echo "selected='selected'"; ?>>Cartella principale</option>
			 <option value='other'>Altro...</option>
			 <optgroup label="Categorie principali">
			 <?php
			 $ret = GShell("dynarc cat-list -ap `".$_AP."`",$_REQUEST['sessid'], $_REQUEST['shellid']);
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
	<!-- EOF - PROPERTIES -->

	<!-- THUMBNAIL -->
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
	<!-- EOF - THUMBNAIL -->

	<!-- IDOCS -->
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
	<!-- EOF - IDOCS -->

	<!-- DISCOUNT -->
	<div class="page" id="page-discount" style="display:none;">
	 <div>
	  <table width='100%' border='0' cellspacing='0' cellpadding='10'>
	   <tr><td><h3 class='lightblue'>Scontistiche per cliente</h3></td>
		   <td align='right'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/add-btn-orange.png" class="add-idoc-btn" onclick="discountAdd()" title="Aggiungi"/></td>
	   </tr>
	  </table>
	 </div>

	 <div class="gmutable" style="width:500px;height:320px;border:0px;">
	  <table id="predefdiscount-table" class='gmutable' width='492' cellspacing="0" cellpadding="0" border="0">
	   <tr><th width='20'><input type="checkbox" onchange="PREDEFDISCTB.selectAll(this.checked)"/></th>
		   <th id='predefdiscount-subject' editable='true' style="text-align:left;">CLIENTE</th>
		   <th width='70' id='predefdiscount-percentage' editable='true' format="percentage">SCONTO</th>
	   </tr>

	   <?php
		$db = new AlpaDatabase();
		$qry = "SELECT d.id, d.percentage, d.item_id, r.name FROM dynarc_rubrica_predefdiscount AS d";
		$qry.= " LEFT JOIN dynarc_rubrica_items AS r ON r.id=d.item_id";
		$qry.= " WHERE d.ap='".$_AP."' AND d.cat_id='".$_ID."' ORDER BY r.name ASC";
		$db->RunQuery($qry);
		while($db->Read())
		{
		 echo "<tr id='".$db->record['id']."'><td align='center'><input type='checkbox'/></td>";
		 echo "<td><span class='graybold'>".$db->record['name']."</span></td>";
		 echo "<td><span class='graybold'>".($db->record['percentage'] ? $db->record['percentage'] : '0')."%</span></td></tr>";
		}
		$db->Close();
	   ?>

	  </table>
	 </div>
	 <div style="border-top:1px solid #dadada;height:20px;line-height:20px">
	  <img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/trash.gif" style='cursor:pointer;margin-top:2px;vertical-align:top'/> 
	  <span class='smalltext' style='cursor:pointer' onclick='discountDeleteSelected()'>Elimina selezionati</span>
	 </div>
	</div>
	<!-- EOF - DISCOUNT -->

	<!-- TRANSPONDER -->
	<?php
	if($_TRANSPONDER_SERVERS)
	{
	 ?>
	 <div class="page" id="page-transponder" style="display:none;">
	  <h3 class='lightblue'>Seleziona su quali server pubblicare questa cartella</h3>
	  <br/>
	  <div style="overflow:auto;height:350px">
	   <table id='transponder-rules' width='100%' class='transponder-list' cellspacing='0' cellpadding='2' border='0'>
	    <tr><th>SERVER</th>
		    <th width='230'>SERVIZIO E AZIONI</th>
	    </tr>
	    <?php
		 for($c=0; $c < count($_TRANSPONDER_SERVERS); $c++)
		 {
		  $serverInfo = $_TRANSPONDER_SERVERS[$c];
		  echo "<tr data-serverid='".$serverInfo['id']."' data-servicetag='".$serverInfo['tag']."' data-defrule='".$serverInfo['rule']."'>";
		  echo "<td><div style='width:250px;height:60px;overflow:hidden;vertical-align:middle;display:table-cell'><b>".$serverInfo['name']."</b><br/><span class='smalltext' style='color:#666666'>".$serverInfo['host']."</span></div></td>";
		  echo "<td><b>".$serverInfo['tagname']."</b><br/>";
		  echo "<input type='radio' name='transp-".$serverInfo['id']."-ruleinherit'".(!$serverInfo['rule'] ? " checked='true'" : "")."/>eredita ";
		  echo "<input type='radio' name='transp-".$serverInfo['id']."-ruleinherit'".(($serverInfo['rule'] == 1) ? " checked='true'" : "")."/>pubblica ";
		  echo "<input type='radio' name='transp-".$serverInfo['id']."-ruleinherit'".(($serverInfo['rule'] == 2) ? " checked='true'" : "")."/>escludi</td>";
		  echo "</tr>";
		 }
	    ?>
	   </table>
	  </div>
	 </div>
    <?php
	}
	?>
	<!-- EOF - TRANSPONDER -->


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
var ARCHIVE_PREFIX = "<?php echo $_AP; ?>";
var CAT_ID = <?php echo $_ID ? $_ID : "0"; ?>;
var PARENT_ID = <?php echo $catInfo['parent_id'] ? $catInfo['parent_id'] : '0'; ?>;
var OLD_PARENT_ID = <?php echo $catInfo['parent_id'] ? $catInfo['parent_id'] : '0'; ?>;
var LAST_UPLOADED_FILENAME = "";
var TRANSPONDER = <?php echo $_TRANSPONDER_SERVERS ? 'true' : 'false'; ?>;
var PREDEFDISCTB = null;

function bodyOnLoad()
{
 /* CUSTOM PRICING TABLE */
 PREDEFDISCTB = new GMUTable(document.getElementById('predefdiscount-table'), {autoresize:false, autoaddrows:false});
 PREDEFDISCTB.NEW_ROWS = new Array();
 PREDEFDISCTB.UPDATED_ROWS = new Array();
 PREDEFDISCTB.DELETED_ROWS = new Array();


 PREDEFDISCTB.OnBeforeAddRow = function(r){
	 r.cells[0].innerHTML = "<input type='checkbox'/"+">"; r.cells[0].style.textAlign='center';
	 r.cells[1].innerHTML = "<span class='graybold'></span>";
	 r.cells[2].innerHTML = "<span class='graybold'></span>";
	 r.cells[2].style.textAlign='center';

	 this.NEW_ROWS.push(r);
	}

 PREDEFDISCTB.OnCellEdit = function(r,cell,value,data){
	 if(r.id && (this.UPDATED_ROWS.indexOf(r) < 0))
	  this.UPDATED_ROWS.push(r);
	 cell.data = data;
	}

 PREDEFDISCTB.OnDeleteRow = function(r){
	 if(r.id)
	 {
	  if(this.UPDATED_ROWS.indexOf(r) >= 0)
	   this.UPDATED_ROWS.splice(this.UPDATED_ROWS.indexOf(r),1);
	  this.DELETED_ROWS.push(r);
	 }
	 else
	  this.NEW_ROWS.splice(this.NEW_ROWS.indexOf(r),1);
	}


 PREDEFDISCTB.FieldByName['predefdiscount-subject'].enableSearch("dynarc item-find -ap rubrica -field name `","` -limit 10 --order-by 'name ASC'","id","name","items",true);

}

function discountAdd()
{
 var r = PREDEFDISCTB.AddRow();
 r.edit();
}

function discountDeleteSelected()
{
 var sel = PREDEFDISCTB.GetSelectedRows();
 if(!sel.length) return alert("Nessun contatto selezionato");
 if(!confirm("Sei sicuro di voler rimuovere i contatti selezionati dalla lista delle scontistiche di questa categoria?"))
  return;

 PREDEFDISCTB.DeleteSelectedRows();
}

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

	 var dstPath = "image/"+ARCHIVE_PREFIX+"/categories/thumbnails/";

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

 /*var parentId = document.getElementById('parent-cat-select').value;
 if((parentId != "other") && (parentId != <?php echo $catInfo['parent_id'] ? $catInfo['parent_id'] : "0"; ?>))*/
 if(PARENT_ID != OLD_PARENT_ID)
  qry+= " -parent `"+PARENT_ID+"`";

 var set = new Array();

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

 if(PREDEFDISCTB.DELETED_ROWS.length)
 {
  var ids = "";
  for(var c=0; c < PREDEFDISCTB.DELETED_ROWS.length; c++)
   ids+= ","+PREDEFDISCTB.DELETED_ROWS[c].id;

  qry+= " && dynarc exec-func ext:predefdiscount.delete -params `id="+ids.substr(1)+"`";
 }
 if(PREDEFDISCTB.NEW_ROWS.length)
 {
  var subjIds = "";
  var percs = "";
  for(var c=0; c < PREDEFDISCTB.NEW_ROWS.length; c++)
  {
   var r = PREDEFDISCTB.NEW_ROWS[c];
   if(!r.cell['predefdiscount-subject'].data) continue;
   subjIds+= ","+r.cell['predefdiscount-subject'].data['id'];
   percs+= ","+parseFloat(r.cell['predefdiscount-percentage'].getValue());
  }
  if(subjIds)
   qry+= " && dynarc exec-func ext:predefdiscount.newbycat -params `ap="+ARCHIVE_PREFIX+"&cat=<?php echo $catInfo['id']; ?>&subjid="+subjIds.substr(1)+"&perc="+percs.substr(1)+"`";
 }
 if(PREDEFDISCTB.UPDATED_ROWS.length)
 {
  var subjIds = "";
  var percs = "";
  var ids = "";
  for(var c=0; c < PREDEFDISCTB.UPDATED_ROWS.length; c++)
  {
   var r = PREDEFDISCTB.UPDATED_ROWS[c];
   ids+= ","+r.id;
   subjIds+= ","+(r.cell['predefdiscount-subject'].data ? r.cell['predefdiscount-subject'].data['id'] : 0);
   percs+= ","+parseFloat(r.cell['predefdiscount-percentage'].getValue());
  }

  qry+= " && dynarc exec-func ext:predefdiscount.edit -params `id="+ids.substr(1)+"&subjid="+subjIds.substr(1)+"&perc="+percs.substr(1)+"`";
 }


 /* SAVE TRANSPONDER RULES */
 if(TRANSPONDER)
 {
  var tQ = "";
  var tb = document.getElementById('transponder-rules');
  for(var c=1; c < tb.rows.length; c++)
  {
   var serverId = tb.rows[c].getAttribute('data-serverid');
   var serviceTag = tb.rows[c].getAttribute('data-servicetag');
   var defRule = parseFloat(tb.rows[c].getAttribute('data-defrule'));
   var rule = 0;
   var radiolist = tb.rows[c].cells[1].getElementsByTagName('input');
   if(radiolist[1].checked == true) rule=1;
   else if(radiolist[2].checked == true) rule=2;
   if(rule != defRule)
	tQ+= " -serverid '"+serverId+"' -servicetag '"+serviceTag+"' -rule '"+rule+"'";
  }
 }

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(TRANSPONDER && tQ)
	 {
	  var sh2 = new GShell();
	  sh2.OnError = function(err){alert(err);}
	  sh2.OnOutput = function(){gframe_close(o,a);}
	  sh2.sendCommand("transponder set-autosync-rules -at gmart -ap '"+ARCHIVE_PREFIX+"' -cat '<?php echo $catInfo['id']; ?>'"+tQ);
	 }
	 else
	  gframe_close(o,a);
	}

 sh.sendCommand(qry);
}

function deleteCategory()
{
 if(!confirm("Sei sicuro di voler eliminare questa categoria?"))
  return;
 
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
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
  sh.OnError = function(err){alert(err);}
  sh.OnOutput = function(o,catId){
	 if(!catId) return;
	 if(catId == CAT_ID)
	 {
	  sel.value = OLD_PARENT_ID;
	  return alert("Non puoi inserire questa categoria all'interno di se stessa! Seleziona un'altra categoria di appartenenza.");
	 }

	 PARENT_ID = catId;
	 for(var c=0; c < sel.options.length; c++)
	 {
	  if(sel.options[c].value == catId)
	  {
	   sel.value = catId;
	   return;
	  }
	 }

	 var sh2 = new GShell();
     sh2.OnError = function(err){alert(err);}
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
 else
 {
  if(sel.value == CAT_ID)
  {
   sel.value = OLD_PARENT_ID;
   return alert("Non puoi inserire questa categoria all'interno di se stessa! Seleziona un'altra categoria di appartenenza.");
  }
  PARENT_ID = sel.value;
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
 sh.OnError = function(err){alert(err);}
 sh.sendCommand("dynarc exec-func ext:idoc.editdefault -params `ap="+ARCHIVE_PREFIX+"&cat=<?php echo $catInfo['id']; ?>&idocaid="+aid+"&idocid="+id+"&all="+(all ? "true" : "false")+"`");
}
</script>
</body></html>
<?php


