<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-02-2013
 #PACKAGE: gserv
 #DESCRIPTION: Official Gnujiko services manager.
 #VERSION: 2.2beta
 #CHANGELOG: 11-02-2013 : Aggiunto il cestino.
			 13-01-2013 : Bug fix.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DESKTOP_SHOW_TOOLBAR, $_DESKTOP_TITLE, $_DECIMALS, $_PRICELISTS, $_CATALOGS;

$_DESKTOP_SHOW_TOOLBAR = false;
$_DESKTOP_TITLE = "Servizi";
$_DESKTOP_BACKGROUND = "#ffffff";
$_BASE_PATH = "../";

include($_BASE_PATH.'init/init1.php');
include($_BASE_PATH.'include/session.php');

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Servizi</title></head>
<link rel='shortcut icon' href='share/images/favicon.png' />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>Services/common.css" type="text/css" />
<?php
if(file_exists($_BASE_PATH."include/headings/desktop.php"))
{
 include($_BASE_PATH.'include/headings/desktop.php');
}
else
{
 echo "<body>";
 include($_BASE_PATH.'include/headings/default.php');
}
//-------------------------------------------------------------------------------------------------------------------//

include($_BASE_PATH."var/templates/basicapp/index.php");
include($_BASE_PATH."var/objects/htmlgutility/menu.php");
include_once($_BASE_PATH."include/company-profile.php");

$_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'];

$ret = GShell("pricelists list");
$_PRICELISTS = $ret['outarr'];

$_PLID = 0;
$_PLGET = "";
$_PLINFO = array();
if(count($_PRICELISTS))
{
 $_PLINFO = $_PRICELISTS[0];
 $_PLID = $_PRICELISTS[0]['id'];
 $_PLGET = "pricelist_".$_PLID."_baseprice,pricelist_".$_PLID."_mrate,pricelist_".$_PLID."_vat";
}

$ret = GShell("dynarc archive-list -type gserv -get `thumb_img,thumb_mode` -a");
if(((count($ret['outarr']) > 1) && !$_REQUEST['aid'] && !$_REQUEST['ap']) || isset($_REQUEST['showcatalogs']))
{
 $_CATALOGS = $ret['outarr'];
 include_once($_BASE_PATH."Services/catalogchoice.php");
 exit;
}
else if(isset($_REQUEST['trash']))
{
 include_once($_BASE_PATH."Services/trash.php");
 exit;
}

$ret = GShell("dynarc archive-info".($_REQUEST['aid'] ? " -id `".$_REQUEST['aid']."`" : " -ap `".($_REQUEST['ap'] ? $_REQUEST['ap'] : "gserv")."`"));
if(!$ret['error'])
{
 $archiveInfo = $ret['outarr'];
 $_AP = $archiveInfo['prefix'];
 $_AT = $archiveInfo['type'];
}

basicapp_header_begin();

?>
<table width='100%' border='0' cellspacing="4" cellpadding="5">
<tr><td width='180' align='right' valign='middle'><img src="<?php echo $_ABSOLUTE_URL; ?>Services/img/logo.png"/></td>
	<td>
	<ul class='basicbuttons' style="margin-left:40px;">
	 <li><a href='#' onclick="newCategory()"><img src="<?php echo $_ABSOLUTE_URL; ?>Services/img/new-folder.png" border='0'/>Nuova categoria</a></li>
	 <li><a href='#' onclick="newService()"><img src="<?php echo $_ABSOLUTE_URL; ?>Services/img/new-article.gif" border='0'/>Nuovo servizio</a></li>
	</ul>
	</td>
	<?php
	$ret = GShell("dynarc trash count -ap `".$_AP."`");
	$countTrash = $ret['outarr']['categories']+$ret['outarr']['items'];
	if($countTrash)
	{
	 echo "<td align='right'><a href='index.php?trash&ap=".$_AP."' style='font-size:12px;text-decoration:none'><img src='".$_ABSOLUTE_URL."Services/img/trash.png' border='0' style='vertical-align:middle'/> Cestino (".$countTrash.")</a></td>";
	}
	?>
</tr>
</table>
<?php
basicapp_header_end();

basicapp_contents_begin();
//-------------------------------------------------------------------------------------------------------------------//
?>
<table width='100%' height='100%' cellspacing='0' cellpadding='0' border='0'>
<tr><td valign='top' width='160' style="border-right:1px solid #dadada;padding-right:10px;">
	<?php
	$thumbnail = "img/generic-product.jpg";
	if($archiveInfo['thumb_img'])
	 $thumbnail = $_ABSOLUTE_URL.$archiveInfo['thumb_img'];
	?>
	<div class="catalog <?php echo ($archiveInfo['params']['gserv-theme'] ? $archiveInfo['params']['gserv-theme'] : 'light-green'); ?>">
	 <div class='headtit'>CATALOGO</div>
	 <div class="title"><i><?php echo $archiveInfo['name']; ?></i></div>
	 <div class="label"><i><?php echo $archiveInfo['name']; ?></i></div>
	 <div class="thumbnail" style="background-image:url(<?php echo $thumbnail; ?>)">&nbsp;</div>
	</div>
	<br/>
	<ul class='basictree-blue' id='catlist-tree'>
	<?php
	$ret = GShell("dynarc cat-list -ap `".$_AP."`");
	$list = $ret['outarr'];
	for($c=0; $c < count($list); $c++)
	{
	 echo "<li id='".$list[$c]['id']."'><span class='item' onclick='openNode(this.parentNode)'>".$list[$c]['name']."</span></li>";
	}
	?>
	</ul>

	<br/>
	<br/>
	<br/>

	<div id='clipboards-space'>
	<?php
	// CLIPBOARDS //
	$ret = GShell("dynarc clipboard-list -tag gmart");
	$clipboardList = $ret['outarr'];
	$archiveTypes = array();
	for($c=0; $c < count($clipboardList); $c++)
	{
	 echo "<div class='clipboard' id='clipboard-".$clipboardList[$c]['id']."'>";
	 echo "<div class='header-maroon'><span class='title'>".stripslashes($clipboardList[$c]['name'])."</span> <a href='#' class='infobtn' onclick='editClipboard(".$clipboardList[$c]['id'].")'><img src='".$_ABSOLUTE_URL."Services/img/clipboard-info.png' border='0'/></a></div>";
	 $total = 0;
	 for($i=0; $i < count($clipboardList[$c]['elements']); $i++)
	 {
	  $el = $clipboardList[$c]['elements'][$i];
	  if(!$archiveTypes[$el['ap']])
	  {
	   $ret = GShell("dynarc archive-info -ap `".$el['ap']."`");
	   if(!$ret['error'])
		$archiveTypes[$el['ap']] = $ret['outarr']['type'];
	  }
  	  $ret = GShell("dynarc item-info -ap `".$el['ap']."` -id `".$el['id']."` -extget `thumbnails,pricing`".($_PLGET ? " -get `".$_PLGET."`" : ""));
	  if($ret['error'])
	   continue;
	  $itm = $ret['outarr'];
	  echo "<div class='item'>";
	  if($itm['thumbnails'][0])
	   echo "<div class='thumbnail' style='background-image: url(".$_ABSOLUTE_URL.$itm['thumbnails'][0].");'>&nbsp;</div>";
 	  else
  	   echo "<div class='thumbnail' style='background-image: url(".$_ABSOLUTE_URL."share/widgets/gserv/img/photo.png);'>&nbsp;</div>";
	  echo "<span class='title' onclick=\"editItem(".$itm['id'].",'".$el['ap']."','".$archiveTypes[$el['ap']]."')\">".$itm['name']."</span>";
	  echo "<div class='desc'>".$itm['desc']."</div>";
	  echo "</div>";

	  $baseprice = $itm["pricelist_".$_PLID."_baseprice"] ? $itm["pricelist_".$_PLID."_baseprice"] : $itm['baseprice'];
	  $markuprate = $itm["pricelist_".$_PLID."_mrate"] ? $itm["pricelist_".$_PLID."_mrate"] : $_PLINFO['markuprate'];
	  $vat = $itm["pricelist_".$_PLID."_vat"] ? $itm["pricelist_".$_PLID."_vat"] : $_PLINFO['vat'];
 	  $finalPrice = $baseprice ? $baseprice + (($baseprice/100)*$markuprate) : 0;
 	  $finalPriceVI = $finalPrice ? $finalPrice + (($finalPrice/100)*$vat) : 0;
	  $total+= ($finalPriceVI*$el['qty']);
	 }

	 echo "<div class='footer' id='clipboard-".$clipboardList[$c]['id']."-footer'><span class='green-left'><i>Totale:</i></span><span class='green-right'><b><i>".number_format($total,$_DECIMALS,",",".")."</i></b></span></div>";
	 echo "</div>";
	}
	?>
	</div>
</td><td valign='top' style="padding-left:10px;">

<!-- PATHWAY -->
<ul class='pathbar' style='margin-top:0px;'>
 <li class='first'><a href="<?php echo $_ABSOLUTE_URL; ?>Services/index.php?showcatalogs">Servizi</a></li>
 <li class='last'><a href="<?php echo $_ABSOLUTE_URL; ?>Services/index.php?aid=<?php echo $archiveInfo['id']; ?>"><?php echo $archiveInfo['name']; ?></a></li>
</ul>
<ul class='pathway' id='pathway' style="float:left;margin-top:3px;margin-left:10px;"></ul>

<br/>

<div class='catblock-container' style="height:138px;clear:both;" id='catblock-container'>
<?php
$ret = GShell("dynarc cat-list -ap `".$_AP."` -extget `gserv.subcatcount,.totitemscount,thumbnails`");
$list = $ret['outarr'];
if(count($list) > 5)
 $small=true;
for($c=0; $c < count($list); $c++)
{
 $cat = $list[$c];
 echo "<div class='catblock".($small ? "-small" : "")."'><img src='".$_ABSOLUTE_URL."Services/img/cat-block-btn.png' class='button' onclick='editCat(".$cat['id'].")'/>";
 if($cat['thumb_img'])
  echo "<div class='thumbnail' style=\"background-image: url(".$_ABSOLUTE_URL.$cat['thumb_img'].");\">"; 
 else
  echo "<div class='thumbnail' style=\"background-image: url(".$_ABSOLUTE_URL."Services/img/photo.png);\">";
 echo "<span class='title' onclick='openNodeId(".$cat['id'].")'>".$cat['name']."</span></div>";
 echo "<div class='footer'>";
 echo "<span>Sottocategorie</span> <em>".$cat['subcatcount']."</em>";
 echo "<span>Articoli</span> <em>".$cat['totitemscount']."</em>";
 echo "</div>";
 echo "</div>";
 
}
?>

<!-- <div class='catblock-empty'></div> -->

</div>

<br/>
<br/>

<table width='100%' cellspacing='0' cellpadding='0' border='0'>
<tr><td>
	
	 <ul class='basicmenu' id='mainmenu'>
	  <li class='gray'><span>Menu</span>
		<ul class="submenu">
		 <li onclick="newService()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/new_file.png"/><?php echo i18n('New article'); ?></li>
		 <li onclick="newCategory()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/new_folder.png"/><?php echo i18n('New subcategory'); ?></li>
		 <!-- <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/import_orange.gif"/><?php echo i18n('Import'); ?>
			<ul class="submenu">
			 <li onclick="_importFromFile()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/xml.png"/><?php echo i18n('from file (.xml)'); ?></li>
			 <li onclick="_importFromArchive()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/archive.png"/><?php echo i18n('from archive'); ?></li>
			</ul></li>
		 <li><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/export2.png"/><?php echo i18n('Export'); ?>
			<ul class="submenu">
			 <li onclick="_exportToFile()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/xml.png"/><?php echo i18n('into file (.xml)'); ?></li>
			 <li onclick="_exportToArchive()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/archive.png"/><?php echo i18n('into another archive'); ?></li>
			</ul></li> -->
		 <li class="separator">&nbsp;</li>
		 <li onclick="editCat(SELECTED_CAT_ID)"><?php echo i18n('Edit folder properties'); ?></li>
		</ul>
	  </li>

	  <li class='lightgray'><span>Modifica</span>
		<ul class='submenu'>
		 <li id='cutmenubtn' class='disabled' onclick="cut()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/cut.gif"/><?php echo i18n("cut"); ?></li>
		 <li id='copymenubtn' class='disabled' onclick="copy()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/copy.png"/><?php echo i18n("copy"); ?></li>
		 <li id='pastemenubtn' class='disabled' onclick="paste()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/paste.gif"/><?php echo i18n("paste"); ?></li>
		 <li class='separator'>&nbsp;</li>
		 <li id='deletemenubtn' class='disabled' onclick="deleteSelectedItems()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/icons/16x16/delete.gif"/><?php echo i18n("Delete selected"); ?></li>
		</ul>
	  </li>

	  <!-- <li class='lightgray'><span>Visualizza</span></li> -->
	  <li class='blue' id='selectionmenu' style='visibility:hidden;'><span><img src="<?php echo $_ABSOLUTE_URL; ?>var/templates/basicapp/img/checkbox.png" border='0'/>Selezionati</span>
		<ul class="submenu">
		 <li onclick="unselectAll(true)">Annulla selezione</li>
		 <li class='separator'></li>
		 <li>Copia negli appunti
		   <ul class="submenu">
			<?php
			for($c=0; $c < count($clipboardList); $c++)
			 echo "<li onclick='copyToClipboard(\"".$clipboardList[$c]['id']."\")'>".$clipboardList[$c]['name']."</li>";
			?>
			<li id='new-clipboard-link' onclick='copyToClipboard()'>Nuovo...</li>
		   </ul>
		 </li>
		 <!-- <li>Aggiungi al preventivo</li>
		 <li>Sposta in un altra categoria</li>
		 <li>Crea una copia</li> -->
		</ul>
	  </li>
	 </ul>

	</td><td align='right'>&nbsp;</td>
</tr>
</table>

<hr style="height:1px;border:0px;background:#3364c3;margin-bottom:0px;"/>

<div id="ITEMLIST_SPACE"></div>

</td></tr>
</table>

<?php
//-------------------------------------------------------------------------------------------------------------------//
basicapp_contents_end();

if(file_exists($_BASE_PATH."include/footers/desktop.php"))
 include($_BASE_PATH.'include/footers/desktop.php');
else
 include($_BASE_PATH.'include/footers/default.php');
?>

<script>
var MainMenu = null;
var SELECTED_IDS = new Array();
var SELECTED_CAT_ID = 0;
var ITEMLISTIFRAME = null;
var MODSEL = new Array();
var MODACT = "";
var CURRENT_VIEW = "";
var PLID = <?php echo $_PLID ? $_PLID : "0"; ?>;
var PLGET = "<?php echo $_PLGET; ?>";
var DECIMALS = <?php echo $_DECIMALS ? $_DECIMALS : "2"; ?>;
var AP = "<?php echo $_AP ? $_AP : 'gserv'; ?>";
var AT = "<?php echo $_AT ? $_AT : 'gserv'; ?>";

function loadFrameView(view,params)
{
 CURRENT_VIEW = view;
 var sh = new GShell();
 sh.OnPreOutput = function(o,a,msgType){
	 switch(msgType)
	 {
	  case "LOADED" : {
		 document.getElementById('ITEMLIST_SPACE').style.height = "";
		 ITEMLISTIFRAME = a;
		} break;
	  case "SELECT" : {
		 SELECTED_IDS.push(a);
		 document.getElementById('selectionmenu').style.visibility = SELECTED_IDS.length ? "visible" : "hidden";
		 if(SELECTED_IDS.length)
		 {
		  document.getElementById('cutmenubtn').className = "";
		  document.getElementById('copymenubtn').className = "";
		  document.getElementById('deletemenubtn').className = "";
		 }
		} break;

	  case "UNSELECT" : {
		 SELECTED_IDS.splice(SELECTED_IDS.indexOf(a),1);
		 document.getElementById('selectionmenu').style.visibility = SELECTED_IDS.length ? "visible" : "hidden";
		 if(SELECTED_IDS.length)
		 {
		  document.getElementById('cutmenubtn').className = "";
		  document.getElementById('copymenubtn').className = "";
		  document.getElementById('deletemenubtn').className = "";
		 }
		 else
		 {
		  document.getElementById('cutmenubtn').className = "disabled";
		  document.getElementById('copymenubtn').className = "disabled";
		  document.getElementById('deletemenubtn').className = "disabled";
		 }
		} break;

	  case "VIEW_CHANGED" : {
		 document.getElementById('ITEMLIST_SPACE').style.height = document.getElementById('ITEMLIST_SPACE').offsetHeight;
		 var parms = "";
		 for(var c=0; c < SELECTED_IDS.length; c++)
		  parms+=","+SELECTED_IDS[c];
		 if(parms != "")
		  parms = "selected="+parms.substr(1);
		 loadFrameView(a,parms);
		} break;

	  case "EDIT_ITEM" : {
		 editItem(a);
		} break;

	  case "JUMP_TO_PAGE" : {
		 var el = ITEMLISTIFRAME.document.getElementById('itemlist-'+(parseInt(a['page'])-1));
		 if(el)
		 {
		  var pos = el.offsetTop+document.getElementById('ITEMLIST_SPACE').offsetTop+110;
		  document.body.scrollTop = pos;
		 }
		 else
		 {
		  ITEMLISTIFRAME.gframe_close();
		  var parms = "";
		  for(var c=0; c < SELECTED_IDS.length; c++)
		   parms+=","+SELECTED_IDS[c];
		  if(parms != "")
		   parms = "selected="+parms.substr(1);
		  loadFrameView(CURRENT_VIEW,"pg="+a['page']+"&limit="+a['limit']+(parms != "" ? "&"+parms : ""));
		 }

		} break;

	 }
	}

 sh.sendCommand("gframe -f gserv/itemlist --append-to ITEMLIST_SPACE -h 100% -params `ap="+AP+"&catid="+SELECTED_CAT_ID+"&view="+view+(params ? "&"+params : "")+"`");
}

function desktopOnLoad()
{
 MainMenu = new GMenu(document.getElementById('mainmenu'));

 var div = document.getElementById('catblock-container');
 div.onmouseover = function(){
	 if(this.tim)
	  clearTimeout(this.tim);
	 if(this.scrollHeight > this.offsetHeight)
	  this.tim = window.setTimeout(function(){
		 var oDiv = document.getElementById('catblock-container');
		 oDiv.style.height = (oDiv.scrollHeight < 300) ? oDiv.scrollHeight : 300;
		},800);
	}
 div.onmouseout = function(){
	 if(this.tim)
	  clearTimeout(this.tim);
	 this.tim = window.setTimeout(function(){document.getElementById('catblock-container').style.height = 138;},200);
	}
 loadFrameView();
}

function editItem(id,archivePrefix,archiveType)
{
 var EDITITEMIFRAME = null;

 if(!archivePrefix)
  archivePrefix = AP;
 if(!archiveType)
  archiveType = AT;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 ITEMLISTIFRAME.document.location.reload();
	}

 sh.OnPreOutput = function(o,a,msgType){
	 switch(msgType)
	 {
	  case 'LOADED' : {EDITITEMIFRAME = a;} break;
	  case 'DELETE' : {
		 if(confirm("Sei sicuro di voler eliminare questo servizio?"))
		 {
		  var sh2 = new GShell();
		  sh2.OnOutput = function(){
			 EDITITEMIFRAME.gframe_close();
			 ITEMLISTIFRAME.document.location.reload();
			}
		  sh2.sendCommand("dynarc delete-item -ap `"+archivePrefix+"` -id `"+a+"`");
		 }
		 return false;
		} break;
	  case 'COPYTOCLIPBOARD' : {
		 var clipboardId = a['clipboard']['id'];
		 if(!document.getElementById('clipboard-'+a['clipboard']['id']))
	 	 {
	  	  // create new clipboard ... //
	  	  var div = document.createElement('DIV');
	  	  div.className = "clipboard";
	  	  div.id = "clipboard-"+a['clipboard']['id'];
		  var html = "<div class='header-maroon'><span class='title'>"+a['clipboard']['name']+"</span> <a href='#' class='infobtn' onclick='editClipboard("+a['clipboard']['id']+")'><img src='"+ABSOLUTE_URL+"Services/img/clipboard-info.png' border='0'/ ></a></div>";
		  html+= "<div class='footer' id='clipboard-"+a['clipboard']['id']+"-footer'><span class='green-left'><i>Totale:</i></span><span class='green-right'><b><i>"+formatCurrency(0,DECIMALS)+"</i></b></span></div>";
		  div.innerHTML = html;
		  document.getElementById('clipboards-space').appendChild(div);
		  id = a['clipboard']['id'];
		  var link = document.getElementById('new-clipboard-link');
		  var li = document.createElement('LI');
		  li.cbid = id;
		  li.innerHTML = a['clipboard']['name'];
		  li.onclick = function(){copyToClipboard(this.cbid);}
		  link.parentNode.insertBefore(li,link);
		 }

	 	 var sh2 = new GShell();
	 	 sh2.OnOutput = function(o,a){
		 	 var div = document.createElement('DIV');
		 	 div.className = "item";
		 	 var html = "";
		 	 if(a['thumbnails'])
		  	  html+= "<div class='thumbnail' style='background-image: url("+ABSOLUTE_URL+a['thumbnails'][0]+");'>&nbsp;</div>";
		 	 else
		  	  html+= "<div class='thumbnail' style='background-image: url("+ABSOLUTE_URL+"share/widgets/gserv/img/photo.png);'>&nbsp;</div>";
		 	 html+= "<span class='title' onclick=\"editItem("+a['id']+",'"+archivePrefix+"','"+archiveType+"')\">"+a['name']+"</span>";
		 	 html+= "<div class='desc'>"+a['desc']+"</div>";
		 
		 	 var baseprice = parseFloat(a["pricelist_"+PLID+"_baseprice"] ? a["pricelist_"+PLID+"_baseprice"] : a['baseprice']);
 	     	 var markuprate = parseFloat(a["pricelist_"+PLID+"_mrate"] ? a["pricelist_"+PLID+"_mrate"] : <?php echo $_PLINFO['markuprate']; ?>);
	     	 var vat = parseFloat(a["pricelist_"+PLID+"_vat"] ? a["pricelist_"+PLID+"_vat"] : <?php echo $_PLINFO['vat']; ?>);
 	     	 var finalPrice = baseprice ? baseprice + ((baseprice/100)*markuprate) : 0;
 	     	 var finalPriceVI = finalPrice ? finalPrice + ((finalPrice/100)*vat) : 0;

		 	 div.innerHTML = html;
		 	 document.getElementById('clipboard-'+clipboardId).insertBefore(div, document.getElementById('clipboard-'+clipboardId+'-footer'));

		 	 /* Update final price */
		 	 var fpO = document.getElementById('clipboard-'+clipboardId+'-footer').getElementsByTagName('SPAN')[1].getElementsByTagName('I')[0];
		 	 var fp = parseCurrency(fpO.innerHTML);
		 	 fp+= finalPriceVI;
		 	 fpO.innerHTML = formatCurrency(fp,DECIMALS);
		 	 alert("Il servizio è stato copiato negli appunti!");
			}
	 	 sh2.sendCommand("dynarc item-info -ap `"+a['element']['ap']+"` -id `"+a['element']['id']+"` -extget `thumbnails,pricing`"+(PLGET ? " -get `"+PLGET+"`" : ""));
		}
	 }
	}

 switch(archiveType)
 {
  case 'gmart' : sh.sendCommand("gframe -f gmart/edit.item -params `ap="+archivePrefix+"&id="+id+"`"); break;
  case 'gserv' : sh.sendCommand("gframe -f gserv/edit.item -params `ap="+archivePrefix+"&id="+id+"`"); break;
 }
}

/* CATEGORY TREE */
var SELECTED_CAT_TREE_NODE = null;

function openNode(li)
{
 var parent = li.parentNode;
 var list = parent.getElementsByTagName('LI');
 var oldSelectedCatTreeNode = SELECTED_CAT_TREE_NODE;
 for(var c=0; c < list.length; c++)
 {
  if(list[c] != li)
  {
   if(oldSelectedCatTreeNode == list[c])
	oldSelectedCatTreeNode = null;
   if(list[c].className == "expanded")
	closeNode(list[c]);
  }
 }
 if(oldSelectedCatTreeNode && (oldSelectedCatTreeNode != parent.parentNode))
  closeNode(oldSelectedCatTreeNode);

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 var ul = document.createElement('UL');
	 ul.className = "basictree-blue";
	 var cbc = document.getElementById('catblock-container');
	 cbc.innerHTML = "";
	 if(!li.getElementsByTagName('UL').length)
	 {
	  if(a)
	  {
	   for(var c=0; c < a.length; c++)
	   {
	    var _li = document.createElement('LI');
	    _li.id = a[c]['id'];
	    _li.innerHTML = "<span class='item' onclick='openNode(this.parentNode)'>"+a[c]['name']+"</span>";
	    ul.appendChild(_li);
	   }
	   li.appendChild(ul);
	  }
	 }
	 else
	  li.getElementsByTagName('UL')[0].style.display='';
	 if(!a)
	 {
	  // hide cat-block-container //
	  cbc.style.display = 'none';
	  return;
	 }
	 cbc.style.display = '';
	 //----------------------------------------//
	 for(var c=0; c < a.length; c++)
	 {
	  var div = document.createElement('DIV');
	  div.className = "catblock"+((a.length > 5) ? "-small" : "");
	  var html = "<img src='"+ABSOLUTE_URL+"Services/img/cat-block-btn.png' class='button' onclick='editCat("+a[c]['id']+")'/ >";
	  if(a[c]['thumb_img'])
	   html+= "<div class='thumbnail' style=\"background-image: url("+ABSOLUTE_URL+a[c]['thumb_img']+");\">";
	  else
	   html+= "<div class='thumbnail' style=\"background-image: url("+ABSOLUTE_URL+"Services/img/photo.png);\">";
	  html+= "<span class='title' onclick='openNodeId("+a[c]['id']+")'>"+a[c]['name']+"</span></div>";
	  html+= "<div class='footer'><span>Sottocategorie</span> <em>"+a[c]['subcatcount']+"</em>";
	  html+= "<span>Articoli</span> <em>"+a[c]['totitemscount']+"</em></div>";
	  div.innerHTML = html;
	  cbc.appendChild(div);
	 }
	}
 sh.sendCommand("dynarc cat-list -ap `"+AP+"` -parent `"+li.id+"` -extget `gserv.subcatcount,.totitemscount,thumbnails`");
  

 li.getElementsByTagName('SPAN')[0].className = 'active';
 var liname = li.getElementsByTagName('SPAN')[0].innerHTML;
 SELECTED_CAT_TREE_NODE = li;

 if(li.className != "expanded")
 {
  var pw = document.getElementById('pathway');
  if(pw.getElementsByTagName('LI').length)
   pw.getElementsByTagName('LI')[pw.getElementsByTagName('LI').length-1].className = "";
  var pwLI = document.createElement('LI');
  pwLI.innerHTML = liname;
  pwLI.id = "pw-"+li.id;
  pwLI.className = "last";
  pwLI.onclick = function(){openNodeId(this.id.substr(3));}
  pw.appendChild(pwLI);
 }

 if(ITEMLISTIFRAME)
  ITEMLISTIFRAME.OnSelectCategory(li.id);

 SELECTED_CAT_ID = li.id;

 unselectAll();

 li.className = "expanded";
}

function closeNode(li)
{
 if(li.getElementsByTagName('UL').length)
 {
  var ul = li.getElementsByTagName('UL')[0];
  var list = ul.getElementsByTagName('LI');
  for(var c=0; c < list.length; c++)
   closeNode(list[c]);
  ul.style.display='none';
 }

 if(li.className == "expanded")
 {
  var pw = document.getElementById('pathway');
  pw.removeChild(pw.getElementsByTagName('LI')[pw.getElementsByTagName('LI').length-1]);
  if(pw.getElementsByTagName('LI').length)
   pw.getElementsByTagName('LI')[pw.getElementsByTagName('LI').length-1].className = "last";
 }

 li.className = "";
 li.getElementsByTagName('SPAN')[0].className = 'item';
 SELECTED_CAT_TREE_NODE = null;
}

function newCategory()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(a[0]['parent_id'] && (a[0]['parent_id'] != "0"))
	 {
	  var li = document.getElementById(a[0]['parent_id']);
	  if(li.getElementsByTagName('UL').length)
	   var ul = li.getElementsByTagName('UL')[0];
	  else
	  {
	   var ul = document.createElement('UL');
	   ul.className = "basictree-blue";
	   li.appendChild(ul);
	  }
	 }
	 else
	  var ul = document.getElementById('catlist-tree');

	 for(var c=0; c < a.length; c++)
	 {
	  var li = document.createElement('LI');
	  li.id = a[c]['id'];
	  li.innerHTML = "<span class='item' onclick='openNode(this.parentNode)'>"+a[c]['name']+"</span>";
	  ul.appendChild(li);
	 }

	 switch(o)
	 {
	  case 'ENTER' : {
		 openNodeId(a[0]['id']);
		} break;
	  default : {
		 var catNames = "";
		 for(var c=0; c < a.length; c++)
		  catNames+= "#"+a[c]['id']+" - "+a[c]['name']+"\n";
		 openNodeId(SELECTED_CAT_ID);
		} break;
	 }

	}

 sh.sendCommand("gframe -f gserv/new.cat -params `ap="+AP+"&cat="+SELECTED_CAT_ID+"`");
}

function newService()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 editItem(a['id']);
	}
 sh.sendCommand("dynarc new-item -ap `"+AP+"` -group gserv -name `Senza nome`"+(SELECTED_CAT_ID ? " -cat "+SELECTED_CAT_ID : ""));
}

function openNodeId(id)
{
 if(!id)
  return document.location.reload();

 var li = document.getElementById(id);
 openNode(li);
}

function cut()
{
 MODSEL = new Array();
 for(var c=0; c < SELECTED_IDS.length; c++)
  MODSEL.push(SELECTED_IDS[c]);
 MODACT = "CUT";
 document.getElementById('pastemenubtn').className = "";
}

function copy()
{
 MODSEL = new Array();
 for(var c=0; c < SELECTED_IDS.length; c++)
  MODSEL.push(SELECTED_IDS[c]);
 MODACT = "COPY";
 document.getElementById('pastemenubtn').className = "";
}

function paste()
{
 if(!MODSEL.length)
  return alert("Devi selezionare almeno un prodotto");

 switch(MODACT)
 {
  case 'CUT' : {
	 var q = "";
	 for(var c=0; c < MODSEL.length; c++)
	  q+= " -id "+MODSEL[c];
	 var sh = new GShell();
	 sh.OnOutput = function(){
		 unselectAll();
		 ITEMLISTIFRAME.document.location.reload();
		}
	 sh.sendCommand("dynarc item-move -ap `"+AP+"` -cat `"+SELECTED_CAT_ID+"`"+q);
	} break;

  case 'COPY' : {
	 var q = "";
	 for(var c=0; c < MODSEL.length; c++)
	  q+= " -id "+MODSEL[c];
	 var sh = new GShell();
	 sh.OnOutput = function(){
		 unselectAll();
		 ITEMLISTIFRAME.document.location.reload();
		}
	 sh.sendCommand("dynarc item-copy -ap `"+AP+"` -cat `"+SELECTED_CAT_ID+"`"+q);
	} break;
 }
 MODSEL = new Array();
 MODACT = "";
}

function deleteSelectedItems()
{
 if(!SELECTED_IDS.length)
  return alert("Devi selezionare almeno un prodotto");

 if(!confirm("Sei sicuro di voler eliminare i prodotti selezionati?"))
  return;

 var q = "";
 for(var c=0; c < SELECTED_IDS.length; c++)
  q+= " -id "+SELECTED_IDS[c];

 var sh = new GShell();
 sh.OnOutput = function(){
	 unselectAll();
	 ITEMLISTIFRAME.document.location.reload();
	}
 sh.sendCommand("dynarc delete-item -ap `"+AP+"`"+q);
}

function unselectAll(reloadFrame)
{
 SELECTED_IDS = new Array();
 document.getElementById('cutmenubtn').className = "disabled";
 document.getElementById('copymenubtn').className = "disabled";
 document.getElementById('deletemenubtn').className = "disabled";
 document.getElementById('selectionmenu').style.visibility = "hidden";
 if(reloadFrame)
  ITEMLISTIFRAME.document.location.reload();
}

function editCat(id)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(a['removed'] || a['trashed'])
	 {
	  /* Da fare in caso di rimozione della categoria */
	 }

	 document.location.reload();
	}
 sh.sendCommand("gframe -f gserv/edit.cat -params `ap="+AP+"&id="+id+"`");
}

function copyToClipboard(id)
{
 if(!SELECTED_IDS.length)
  return alert("Devi selezionare almeno un prodotto");

 if(!id)
 {
  var cpName = prompt("Inserisci un titolo per gli appunti");
  if(!cpName)
   return;
 }

 var idx = 0;
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!id && (idx == 0))
	 {
	  // create new clipboard ... //
	  var div = document.createElement('DIV');
	  div.className = "clipboard";
	  div.id = "clipboard-"+a['clipboard']['id'];
	  var html = "<div class='header-maroon'><span class='title'>"+a['clipboard']['name']+"</span> <a href='#' class='infobtn' onclick='editClipboard("+a['clipboard']['id']+")'><img src='"+ABSOLUTE_URL+"Services/img/clipboard-info.png' border='0'/ ></a></div>";
	  html+= "<div class='footer' id='clipboard-"+a['clipboard']['id']+"-footer'><span class='green-left'><i>Totale:</i></span><span class='green-right'><b><i>"+formatCurrency(0,DECIMALS)+"</i></b></span></div>";
	  div.innerHTML = html;
	  document.getElementById('clipboards-space').appendChild(div);
	  id = a['clipboard']['id'];
	  var link = document.getElementById('new-clipboard-link');
	  var li = document.createElement('LI');
	  li.cbid = id;
	  li.innerHTML = a['clipboard']['name'];
	  li.onclick = function(){copyToClipboard(this.cbid);}
	  link.parentNode.insertBefore(li,link);
	 }

	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){
		 var div = document.createElement('DIV');
		 div.className = "item";
		 var html = "";
		 if(a['thumbnails'])
		  html+= "<div class='thumbnail' style='background-image: url("+ABSOLUTE_URL+a['thumbnails'][0]+");'>&nbsp;</div>";
		 else
		  html+= "<div class='thumbnail' style='background-image: url("+ABSOLUTE_URL+"share/widgets/gserv/img/photo.png);'>&nbsp;</div>";
		 html+= "<span class='title' onclick='editItem("+a['id']+")'>"+a['name']+"</span>";
		 html+= "<div class='desc'>"+a['desc']+"</div>";
		 
		 var baseprice = parseFloat(a["pricelist_"+PLID+"_baseprice"] ? a["pricelist_"+PLID+"_baseprice"] : a['baseprice']);
 	     var markuprate = parseFloat(a["pricelist_"+PLID+"_mrate"] ? a["pricelist_"+PLID+"_mrate"] : <?php echo $_PLINFO['markuprate']; ?>);
	     var vat = parseFloat(a["pricelist_"+PLID+"_vat"] ? a["pricelist_"+PLID+"_vat"] : <?php echo $_PLINFO['vat']; ?>);
 	     var finalPrice = baseprice ? baseprice + ((baseprice/100)*markuprate) : 0;
 	     var finalPriceVI = finalPrice ? finalPrice + ((finalPrice/100)*vat) : 0;

		 div.innerHTML = html;
		 document.getElementById('clipboard-'+id).insertBefore(div, document.getElementById('clipboard-'+id+'-footer'));

		 /* Update final price */
		 var fpO = document.getElementById('clipboard-'+id+'-footer').getElementsByTagName('SPAN')[1].getElementsByTagName('I')[0];
		 var fp = parseCurrency(fpO.innerHTML);
		 fp+= finalPriceVI;
		 fpO.innerHTML = formatCurrency(fp,DECIMALS);
		}
	 sh2.sendCommand("dynarc item-info -ap `"+AP+"` -id `"+SELECTED_IDS[idx]+"` -extget `thumbnails,pricing`"+(PLGET ? " -get `"+PLGET+"`" : ""));

	 idx++;
	}

 for(var c=0; c < SELECTED_IDS.length; c++)
 {
  if(!id && (c == 0))
   sh.sendCommand("dynarc copy-to-clipboard -clipboard `"+cpName+"` -tag gmart -ap `"+AP+"` -id `"+SELECTED_IDS[c]+"`");
  else if(!id)
   sh.sendCommand("dynarc copy-to-clipboard --last-clipboard -ap `"+AP+"` -id `"+SELECTED_IDS[c]+"`");
  else
   sh.sendCommand("dynarc copy-to-clipboard -clipboardid `"+id+"` -ap `"+AP+"` -id `"+SELECTED_IDS[c]+"`");
 }
}

function editClipboard(id)
{
 var sh = new GShell();
 sh.OnPreOutput = function(o,a,msgType){
	 switch(msgType)
	 {
	  case 'APPEND-TO-DOCUMENT' : window.open(ABSOLUTE_URL+"GCommercialDocs/docinfo.php?id="+a, "GCD-"+a); break;
	  case 'REMOVED' : document.location.reload(); break;
	 }

	}
 sh.sendCommand("gframe -f gserv/edit.clipboard -params `id="+id+"`");
}
</script>

</body></html>
<?php

