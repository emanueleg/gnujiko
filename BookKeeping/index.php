<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-07-2012
 #PACKAGE: bookkeeping
 #DESCRIPTION: Book Keeping and Petty Cash Book manager.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_DESKTOP_SHOW_TOOLBAR, $_DESKTOP_TITLE, $_ARCHIVE_INFO;

$_DESKTOP_SHOW_TOOLBAR = false;
$_DESKTOP_BACKGROUND = "#eeeeee";
$_DESKTOP_TITLE = "Contabilità";

$_BASE_PATH = "../";
define("VALID-GNUJIKO",1);

include($_BASE_PATH.'init/init1.php');
include($_BASE_PATH.'include/session.php');
include_once($_BASE_PATH."include/gshell.php");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $_DESKTOP_TITLE; ?></title></head>
<link rel='shortcut icon' href='share/images/favicon.png' />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/common.css" type="text/css" />
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
?>
<table width='100%' height='100%' cellspacing='0' cellpadding='0' border='0'>
<tr><td valign='top' width='190'>
	<img src="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/img/logo.png" style="margin-top:20px;margin-left:30px;margin-bottom:20px;"/><br/>
	<div class="<?php echo !$_REQUEST['page'] ? 'storetab-selected' : 'storetab'; ?>" onclick="document.location.href='index.php'">
	 <img src="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/img/pettycashbook.gif"/>
	 <span>Prima nota</span>
	 <div class="storetab-contents">
	  <div class='hr'>&nbsp;</div>
	  <ul class='basicmenu'>
	   <li <?php if(!$_REQUEST['filter'] || ($_REQUEST['filter'] == "all")) echo "class='selected'"; ?>><a href='?filter=all'>Tutti i movimenti</a></li>
	   <li <?php if($_REQUEST['filter'] == "in") echo "class='selected'"; ?>><a href='?filter=in'>Entrate / Ricavi</a></li>
	   <li <?php if($_REQUEST['filter'] == "out") echo "class='selected'"; ?>><a href='?filter=out'>Uscite / Spese</a></li>
	   <li <?php if($_REQUEST['filter'] == "transfers") echo "class='selected'"; ?>><a href='?filter=transfers'>Giroconti</a></li>
	  </ul>
     </div>
	</div>

	<div class="<?php echo ($_REQUEST['page'] == 'vatbook') ? 'storetab-selected' : 'storetab'; ?>" onclick="document.location.href='index.php?page=vatbook'">
	 <img src="<?php echo $_ABSOLUTE_URL; ?>BookKeeping/img/vatbook.png"/>
	 <span>Registro IVA</span>
	 <div class="storetab-contents">
	  <div class='hr'>&nbsp;</div>
	  <ul class='basicmenu'>
	   <li <?php if(!$_REQUEST['show'] || ($_REQUEST['show'] == "purchasesregister")) echo "class='selected'"; ?>><a href='?page=vatbook&show=purchasesregister'>Registro IVA acquisti</a></li>
	   <li <?php if($_REQUEST['show'] == "salesregister") echo "class='selected'"; ?>><a href='?page=vatbook&show=salesregister'>Registro IVA vendite</a></li>
	   <!-- <li <?php if($_REQUEST['show'] == "summary") echo "class='selected'"; ?>><a href='?page=vatbook&show=summary'>Versamenti</a></li> -->
	  </ul>
	 </div>
	</div>

	</td><td valign='top'>
	<!-- CONTENTS -->
	<div class="storepage" id='storepagecontainer'>
	<?php
	switch($_REQUEST['page'])
	{
	 case 'vatbook' : include($_BASE_PATH."BookKeeping/vatbook.php"); break;
	 default : include($_BASE_PATH."BookKeeping/pettycashbook.php"); break;
	}
	?>
	</div>
	<!-- EOF CONTENTS -->
	</td></tr>
</table>
<?php
//-------------------------------------------------------------------------------------------------------------------//
if(file_exists($_BASE_PATH."include/footers/desktop.php"))
 include($_BASE_PATH.'include/footers/desktop.php');
else
 include($_BASE_PATH.'include/footers/default.php');
?>
</body></html>
<?php

