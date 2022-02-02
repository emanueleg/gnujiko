<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-11-2013
 #PACKAGE: gnujiko-desktop-base
 #DESCRIPTION: Gnujiko Desktop custom page.
 #VERSION: 2.3beta
 #CHANGELOG: 13-11-2013 : Sostituito SDD_HANDLER con SIMPDD_HANDLER.
			 16-06-2013 : Bug fix vari
			 29-04-2013 : Rimosso temporaneamente dal menu modulo il tasto configura.
 #TODO:
 
*/

global $_SECTION_CONTAINERS, $_MODULES, $_PAGE_INFO;
include_once($_BASE_PATH."var/objects/simpledraganddrop/index.php");
include_once($_BASE_PATh."var/objects/simplecable/index.php");
?>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/desktop/desktop.js" type="text/javascript"></script>
<?php
$_SECTION_CONTAINERS = array();
$_MODULES = array();

$ret = GShell("desktop page-info -id `".$_REQUEST['desktop']."`");
$_PAGE_INFO = $ret['outarr'];
//-------------------------------------------------------------------------------------------------------------------//
function loadSection($section)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_XML_PARAMS, $_INTERNAL_LOAD, $_SECTION_CONTAINERS;
 $_INTERNAL_LOAD = true;
 if(!file_exists($_BASE_PATH."var/desktop/sections/".$section."/index.php"))
  return false;

 include_once($_BASE_PATH."var/desktop/sections/".$section."/index.php");
}
//-------------------------------------------------------------------------------------------------------------------//
function loadModule($moduleInfo)
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_XML_PARAMS, $_INTERNAL_LOAD, $_MODULE_INFO, $_MODULES;
 $_INTERNAL_LOAD = true;
 $_MODULE_INFO = array('id'=>"gjkdskmod-".$moduleInfo['id'],'section_id'=>$moduleInfo['section_id'],'title'=>$moduleInfo['title'],'htmlcontents'=>$moduleInfo['htmlcontents'], 'javascript'=>$moduleInfo['javascript'], 'css'=>$moduleInfo['css'], 'params'=>$moduleInfo['params']);
 echo "<div class='gnujiko-desktop-module' id='gjkdskmod-".$moduleInfo['id']."' moduletitle='".$moduleInfo['title']."' style='display:none'>";
 if(file_exists($_BASE_PATH."var/desktop/modules/".$moduleInfo['name']."/index.php"))
  include($_BASE_PATH."var/desktop/modules/".$moduleInfo['name']."/index.php");
 else
  echo "<h3 style='color:#f31903;'>Error: module ".$moduleInfo['name']." (".$moduleInfo['title'].") does not exists!</h3>";
 echo "</div>";
 $_MODULES[] = $_MODULE_INFO;
}
//-------------------------------------------------------------------------------------------------------------------//
?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>include/desktop/common.css" type="text/css" />
<div class="gnujiko-desktop-page" id="desktop-page-container">

 <img src="<?php echo $_ABSOLUTE_URL; ?>include/desktop/img/mod-menu-btn.png" id="gnujiko-desktop-module-menu-button" style="display:none"/>
 <table id="gnujiko-desktop-module-menu" cellspacing="0" cellpadding="0" border="0" style="visibility:hidden">
 <tr><th>Menu modulo</th></tr>
 <!-- <tr><td onclick="__desktop_configureSelectedModule()"><img src="<?php echo $_ABSOLUTE_URL; ?>include/desktop/img/module-configure.png"/>Configura...</td></tr> -->
 <tr><td onclick="__desktop_renameSelectedModule()"><img src="<?php echo $_ABSOLUTE_URL; ?>include/desktop/img/module-move.png"/>Rinomina</td></tr>
 <tr><td onclick="__desktop_moveSelectedModule()"><img src="<?php echo $_ABSOLUTE_URL; ?>include/desktop/img/module-move.png"/>Sposta in un altra scheda</td></tr>
 <tr><td onclick="__desktop_deleteSelectedModule()"><img src="<?php echo $_ABSOLUTE_URL; ?>include/desktop/img/module-delete.png"/>Elimina</td></tr>
 </table>
<?php
if($_PAGE_INFO['section_type'] && file_exists($_BASE_PATH."var/desktop/sections/".$_PAGE_INFO['section_type']."/index.php"))
 loadSection($_PAGE_INFO['section_type']);
else
 loadSection("default");

/* LOAD MODULES */
$ret = GShell("desktop module-list -page `".$_PAGE_INFO['id']."`");
if(!$ret['error'])
{
 for($c=0; $c < count($ret['outarr']); $c++)
  loadModule($ret['outarr'][$c]);
}
?>
</div>

<script>
var GNUJIKO_DESKTOP = null;
var GNUJIKO_DESKTOP_PAGEID = "<?php echo $_PAGE_INFO['id']; ?>";

var DESKTOP_SECTION_CONTAINERS = new Array();
<?php
for($c=0; $c < count($_SECTION_CONTAINERS); $c++)
 echo "DESKTOP_SECTION_CONTAINERS.push('".$_SECTION_CONTAINERS[$c]."');\n";
?>

var MODULES_PRELOAD = new Array();
<?php
for($c=0; $c < count($_MODULES); $c++)
{
 $jsmod = "MODULES_PRELOAD.push({id:'".$_MODULES[$c]['id']."',secid:'".$_MODULES[$c]['section_id']."',handle:'".$_MODULES[$c]['handle']."',front:'".$_MODULES[$c]['front']."',back:'".$_MODULES[$c]['back']."',plugs: new Array(";
 $q = "";
 for($i=0; $i < count($_MODULES[$c]['plugs']); $i++)
  $q.= ",'".$_MODULES[$c]['plugs'][$i]."'";
 $jsmod.= ltrim($q,",").")});\n";
 echo $jsmod;
}
?>

function desktop_addNewPage()
{
 var title = prompt("Digita un titolo da assegnare alla nuova pagina","Nuova pagina");
 if(!title)
  return;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.location.href="index.php?desktop="+a['id'];
	}
 sh.sendCommand("desktop new-page -name `"+title+"` -section default");
}

function desktop_deletePage()
{
 if(!confirm("Sei sicuro di voler eliminare completamente questa pagina?"))
  return;

 var sh = new GShell();
 sh.OnError = function(msg,errcode){alert(msg);}
 sh.OnOutput = function(){document.location.href = ABSOLUTE_URL;}
 sh.sendCommand("desktop delete-page -id `<?php echo $_REQUEST['desktop']; ?>`");
}

function desktopOnLoad()
{
 /* Startup Gnujiko Desktop handler */
 GNUJIKO_DESKTOP = new GnujikoDesktopHandler();

 /* RESIZE DESKTOP PAGE CONTAINER */
 var container = document.getElementById('desktop-page-container');
 var width = container.parentNode.offsetWidth;
 var height = container.parentNode.offsetHeight;
 container.style.width = width+"px";
 container.style.height = height+"px";

 for(var c=0; c < DESKTOP_SECTION_CONTAINERS.length; c++)
  __desktop_registerSection(document.getElementById(DESKTOP_SECTION_CONTAINERS[c]));

 /* LOAD MODULES */
 for(var c=0; c < MODULES_PRELOAD.length; c++)
 {
  var module = MODULES_PRELOAD[c];
  var modObj = document.getElementById(module.id);
  if(document.getElementById(module.secid))
  {
   if(document.getElementById(module.secid).innerHTML == "&nbsp;")
	document.getElementById(module.secid).innerHTML = "";
   document.getElementById(module.secid).appendChild(modObj);
  }
  else if(DESKTOP_SECTION_CONTAINERS.length)
  {
   if(document.getElementById(DESKTOP_SECTION_CONTAINERS[0]).innerHTML == "&nbsp;")
	document.getElementById(DESKTOP_SECTION_CONTAINERS[0]).innerHTML = "";
   document.getElementById(DESKTOP_SECTION_CONTAINERS[0]).appendChild(modObj);
  }
  else
  {
   alert("Desktop Error: Unable to load module '"+modObj.getAttribute('moduletitle')+"'. No section containers found.");
   continue;
  }
  modObj.style.display = "";

  GNUJIKO_DESKTOP.registerModule(modObj, module);
 }

 /* LOAD CONNECTIONS */
 var sh = new GShell();
 sh.OnOutput = function(o,connlist){
	 if(!connlist) return;
	 for(var c=0; c < connlist.length; c++)
	 {
	  var srcMod = document.getElementById("gjkdskmod-"+connlist[c]['src_mod']);
	  var destMod = document.getElementById("gjkdskmod-"+connlist[c]['dest_mod']);
	  if(srcMod && destMod)
	  {
	   var srcPlug = srcMod.plugByName[connlist[c]['src_port']];
	   var destPlug = destMod.plugByName[connlist[c]['dest_port']];
	   if(srcPlug && destPlug)
		srcPlug.connectTo(destPlug);
	  }
	 }
	 SIMPLE_CABLE_HANDLER.hideCables();
	}
 sh.sendCommand("desktop connections -page `"+GNUJIKO_DESKTOP_PAGEID+"`");

 var frontBackBtn = document.getElementById('desktop-frontback-button');
 frontBackBtn.style.display = "";
 frontBackBtn.onclick = function(){__desktop_switchBackPanel(this);};
}

function __desktop_registerSection(sec)
{
 SIMPDD_HANDLER.setDropableContainer(sec);
}

function __desktop_switchBackPanel(btn)
{
 btn.onclick = function(){__desktop_switchFrontPanel(this);}
 btn.src = ABSOLUTE_URL+"include/desktop/img/back.png";

 for(var c=0; c < MODULES_PRELOAD.length; c++)
 {
  if(!MODULES_PRELOAD[c].back)
   continue;
  if(document.getElementById(MODULES_PRELOAD[c].front))
  {
   if(document.getElementById(MODULES_PRELOAD[c].back)) /* adjust height */
    document.getElementById(MODULES_PRELOAD[c].back).style.height = document.getElementById(MODULES_PRELOAD[c].front).offsetHeight;
   document.getElementById(MODULES_PRELOAD[c].front).style.display = "none";
  }
  if(document.getElementById(MODULES_PRELOAD[c].back))
   document.getElementById(MODULES_PRELOAD[c].back).style.display = "";
 }
 SIMPLE_CABLE_HANDLER.showCables();
}

function __desktop_switchFrontPanel(btn)
{
 btn.onclick = function(){__desktop_switchBackPanel(this);}
 btn.src = ABSOLUTE_URL+"include/desktop/img/front.png";

 for(var c=0; c < MODULES_PRELOAD.length; c++)
 {
  if(!MODULES_PRELOAD[c].front)
   continue;
  if(document.getElementById(MODULES_PRELOAD[c].back))
   document.getElementById(MODULES_PRELOAD[c].back).style.display = "none";
  if(document.getElementById(MODULES_PRELOAD[c].front))
   document.getElementById(MODULES_PRELOAD[c].front).style.display = "";
 }
 SIMPLE_CABLE_HANDLER.hideCables();
}

function __desktop_configureSelectedModule()
{
 var module = GNUJIKO_DESKTOP.SelectedModule;
 GNUJIKO_DESKTOP.configureModule(module);
}

function __desktop_moveSelectedModule()
{
 var module = GNUJIKO_DESKTOP.SelectedModule;
 GNUJIKO_DESKTOP.moveModule(module);
}

function __desktop_deleteSelectedModule()
{
 var module = GNUJIKO_DESKTOP.SelectedModule;
 if(!confirm("Sei sicuro di voler eliminare questo modulo?"))
  return;
 GNUJIKO_DESKTOP.deleteModule(module);
}

function __desktop_renameSelectedModule()
{
 var module = GNUJIKO_DESKTOP.SelectedModule;
 GNUJIKO_DESKTOP.renameModule(module);
}
</script>
<?php


