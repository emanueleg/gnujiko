<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-02-2013
 #PACKAGE: makedist
 #DESCRIPTION: Official Gnujiko Distro Maker.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_ABSOLUTE_URL, $_SOFTWARE_VERSION, $_DISTRO_NAME;

include_once($_BASE_PATH."config.php");
include_once($_BASE_PATH."var/lib/database.php"); // enable and load database access //
include_once($_BASE_PATH."include/i18n.php"); // enable language support //

include($_BASE_PATH.'include/session.php');
include($_BASE_PATH.'include/gshell.php');

LoadLanguage("install", $_REQUEST['lang']);

function installer_begin($title="&nbsp;",$step="&nbsp;")
{
 global $_BASE_PATH, $_ABSOLUTE_URL, $_SOFTWARE_VERSION, $_DISTRO_NAME;
 ?>
 <html><head><link rel='shortcut icon' href='share/images/favicon.png' /><title>Gnujiko Installer</title>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>installation/install.css" type="text/css" />
 </head>
 <body>
 <div class="install-form">
  <div class="header"><img class='logo' src="<?php echo $_ABSOLUTE_URL; ?>installation/img/gnujiko-logo.png"/>
	<em><?php echo $_SOFTWARE_VERSION; ?></em>
	<span class="distro-title"><?php echo $_DISTRO_NAME; ?></span>
  </div>
 <table width="100%" class="toolbar" cellspacing="0" cellpadding="0" border="0">
 <tr><td class="toolbar-title"><?php echo $title; ?></td><td class="toolbar-step"><?php echo $step; ?></td></tr>
 </table>
 <?php
}

function installer_startContents(){echo "<div class='contents'>";}
function installer_endContents(){echo "</div>";}

function installer_end()
{
 ?>
 </div>
 </body></html>
 <?php
}
