<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 16-06-2013
 #PACKAGE: gnujiko-desktop-base
 #DESCRIPTION: Default desktop home page
 #VERSION: 2.2beta
 #CHANGELOG: 16-06-2013 : Bug fix vari.
			 28-10-2012 : Bug fix into line 172
			 04-04-2012 : Bug fix into line 150.
			 24-01-2012 : Aggiunta funzione adaptDesktopElements() che adatta gli elementi in base all'altezza dello schermo.
 #TODO: Traduzione in inglese degli strumenti utente.
 
*/

$_DESKTOP_TITLE = $_SOFTWARE_NAME;

$_DESKTOP_SHOW_TOOLBAR = true;

if(!isset($_REQUEST['desktop']))
{
 include_once($_BASE_PATH."init/init1.php");
 include_once($_BASE_PATH."include/gshell.php");
 $ret = GShell("desktop page-list");
 $_DESKTOP_PAGE_LIST = $ret['outarr'];

 if(count($_DESKTOP_PAGE_LIST))
  $_REQUEST['desktop'] = $_DESKTOP_PAGE_LIST[0]['id'];
}

function desktopToolbar()
{
 $ret = GShell("desktop page-list");
 $list = $ret['outarr'];
 ?>
 <ul class="tabs">
  <li<?php if(!$_REQUEST['desktop']) echo " class='active'"; ?>><a href='index.php?desktop=0'>Home page</a></li>
  <?php
  for($c=0; $c < count($list); $c++)
   echo "<li".($_REQUEST['desktop'] == $list[$c]['id'] ? " class='active'>" : ">")."<a href='index.php?desktop=".$list[$c]['id']."'>".$list[$c]['name']."</a></li>";
  ?>
  <li>
	<div style="float:left;vertical-align:top;width:80px;height:16px;margin-top:4px">
	<a href="#" onclick="desktop_addNewPage()" style="float:left"><img src="<?php echo $_ABSOLUTE_URL; ?>include/desktop/img/add-blue.png" border='0' title="Aggiungi nuova pagina"/></a>
	<?php
	if($_REQUEST['desktop'])
	{
	 ?>&nbsp;
	<a href="#" onclick="desktop_deletePage()" style="float:left"><img src="<?php echo $_ABSOLUTE_URL; ?>include/desktop/img/module-delete.png" border='0' title="Elimina questa pagina"/></a>
	 <?php
	}
	?></li>
 </ul>
 
 <img src="<?php echo $_ABSOLUTE_URL; ?>include/desktop/img/front.png" id="desktop-frontback-button" style="float:right;cursor:pointer;display:none"/>
 <span id="desktop-add-module-button" class="desktop-toolbar-button" style="display:none"><img src="<?php echo $_ABSOLUTE_URL; ?>include/desktop/img/add-blue.png"/>Inserisci</span>
 <?php
}

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $_SOFTWARE_NAME; ?></title></head>
<link rel='shortcut icon' href='share/images/favicon.png' />
<?php
if($_REQUEST['desktop'])
{
 $_DESKTOP_BACKGROUND = "#fafafa";
 include($_BASE_PATH.'include/headings/desktop.php');
 include($_BASE_PATH."include/desktop/custom.php");
 include($_BASE_PATH.'include/footers/desktop.php');
 exit();
}
?>
<style type='text/css'>
body {
	font-family: Arial;
}

span.installbtn {
	width: 234px;
	height: 30px;
	background: url(share/images/install_new_apps_btn.png) top left no-repeat;
	color: #ffffff;
	font-size: 14px;
	font-weight: bold;
	padding-left: 40px;
	display: block;
	float: left;
	margin-left: 20px;
	line-height: 2em;
	font-family: Arial;
	cursor: pointer;
}

a.blue {
	font-family: Arial;
	font-size: 16px;
	font-weight: bold;
	color: #0169c9;
}

a.blue-small {
	font-family: Arial;
	font-size: 14px;
	color: blue;
}

a.green {
	font-family: Arial;
	font-size: 12px;
	color: #015a01;
}

p, li {
	font-size: 12px;
	font-family: Arial;
}

p {
	margin-top: 4px;
}

div.usertool {
	background: #f9f9f9;
	margin: 0px;
	margin-bottom:2px;
	padding: 4px;
}

div.section {
	font-size: 14px;
	color: #ffffff;
	font-weight: bold;
	height: 28px;
	margin-bottom: 0px;
	text-align: center;
	line-height: 2em;
}

div.userapp {
	background: #ffffff;
	margin: 0px;
	margin-bottom:2px;
	padding: 4px;
}
</style>

<?php
include($_BASE_PATH.'include/headings/desktop.php');
/* --- CONTENTS HERE ------------------------------------------------------------------------------------------------ */
?>

<div style='font-size:10px;color:#000000;padding:10px;'><b><?php echo i18n("Welcome to Gnujiko 10.1 Desktop"); ?></b></div>
<div style="margin:10px;border-bottom:1px solid #5a7edc;height:34px;"><span style="font-size:22px;color:#5a7edc;float:left;"><?php echo i18n("List of installed applications"); ?></span> <?php if(file_exists($_BASE_PATH."share/widgets/apm.php")) echo "<span class='installbtn' onclick='runPackageManager()'>".i18n("Install new applications")."</span>"; ?>
</div>
<table width="98%" height="80%" border="0" cellspacing="5" cellpadding="5" align="center" id='desktop-container-table'>
 <tr><td valign='top' width='25%' style="border-right:1px solid #aaccee;">
	 <div class="section" style="background:#6699cc;"><?php echo i18n("User tools"); ?></div>
	 <!-- ACCOUNT MANAGER -->
	 <div class='usertool'>
	 <img src="<?php echo $_ABSOLUTE_URL; ?>share/images/gnujikobase/account.png" style='text-align:left;float:left;vertical-align:top;margin-right:10px;margin-bottom:20px;'/>
	 <a class='blue' href='accounts/index.php'>Account Manager</a>
	 <p>Gestisci tutte le informazioni relative al tuo account.<br/>
	 Password di accesso, email e dati personali.</p>
	 </div>
	 <!-- GSHELL -->
	 <div class='usertool'>
	 <img src="<?php echo $_ABSOLUTE_URL; ?>share/images/gnujikobase/shell.png" style='text-align:left;vertical-align:top;float:left;margin-right:10px;margin-bottom:40px;'/>
	 <a class='blue' href='gshell.php'>Gnujiko Shell</a>
	 <p>E&lsquo; possibile interagire con Gnujiko anche attraverso un terminale virtuale a linea di comando.<br/><br/>
	 <a class='green' href="<?php echo $_ABSOLUTE_URL; ?>share/userguide/it/gshellcommands.php">Lista dei comandi GShell &raquo;</a></p>
	 </div>
	 <!-- USER GUIDE -->
	 <div class='usertool'>
	 <img src="<?php echo $_ABSOLUTE_URL; ?>share/images/gnujikobase/user-guide.png" style='text-align:left;vertical-align:top;float:left;margin-right:10px;margin-bottom:20px;'/>
	 <a class='blue' href="<?php echo $_ABSOLUTE_URL; ?>share/userguide/">Guida all&lsquo;uso</a>
	 <p>Manuale utente per la configurazione del sistema e l&lsquo;installazione dei pacchetti.</p>
	 </div>
	 </td><td valign='top' width='25%' style="border-right:1px solid #aaccee;">
	 <div class="section" style="background:#9ade00;"><?php echo i18n("Applications"); ?></div>
	 <?php
	 /* APPLICATIONS */
	 $ret = GShell("system app-list");
	 for($c=0; $c < count($ret['outarr']); $c++)
	 {
	  $itm = $ret['outarr'][$c];
	  echo "<div class='userapp' style='display:none;'><img src='".$_ABSOLUTE_URL.$itm['icon']."' width='36' height='36' style='text-align:left;vertical-align:top;float:left;margin-right:10px;margin-bottom:20px;'/>";
	  echo "<a class='blue-small' href='".$_ABSOLUTE_URL.$itm['url']."'>".$itm['name']."</a>";
	  echo "<p>".$itm['desc']."&nbsp;</p></div>";
	 }
	 ?></td>
		  <td valign='top' width='25%' style="border-right:1px solid #aaccee;">
		   <div class="section" style="background:#9ade00;">&nbsp;</div>
		  </td>
		  <td valign='top'>&nbsp;</td>
 </tr>
</table>

<?php
/* --- EOF CONTENTS ------------------------------------------------------------------------------------------------ */
include($_BASE_PATH.'include/footers/desktop.php');
?>
<script>
function adaptDesktopElements()
{
 var tb = document.getElementById('desktop-container-table');
 var elemList = tb.rows[0].cells[1].getElementsByTagName('DIV');

 if(elemList[1])
  elemList[1].style.display='';

 if(elemList.length == 2)
  return;

 var SCREEN_HEIGHT = desktop_getScreenHeight();
 var NEXT_CELLIDX = 1;

 for(var c=2; c < elemList.length; c++)
 {
  var div = elemList[c];
  var top = elemList[c-1].offsetTop + elemList[c-1].offsetHeight;
  if(top > (SCREEN_HEIGHT - 450))
  {
   tb.rows[0].cells[2].appendChild(div);
  }
  div.style.display = "";
 }
}
//-------------------------------------------------------------------------------------------------------------------//
function desktop_getScreenWidth()
{
 if(window.innerWidth)
  return window.innerWidth;
 else if(document.all)
  return document.body.clientWidth;
}
//-------------------------------------------------------------------------------------------------------------------//
function desktop_getScreenHeight()
{
 if(window.innerHeight)
  return window.innerHeight;
 else if(document.all)
  return document.body.clientHeight;
}
//-------------------------------------------------------------------------------------------------------------------//
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
//-------------------------------------------------------------------------------------------------------------------//
adaptDesktopElements();
</script>
</body></html>
<?php

