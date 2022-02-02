<?php

include_once("../include/shared-manual.inc");
include_once("ms.inc");
$TOC = array();
$PARENTS = array();
include_once("./toc/gshellcommands.inc");

$setup = array (
  'section' =>
  array (
	0 => 'gshellcommands.php',
	1 => 'Comandi GShell',
  ),
  'home' =>
  array (
    0 => 'index.php',
    1 => 'Gnujiko 10.1 - User Guide',
  ),
  'this' =>
  array (
    0 => 'gshell.exit.php',
    1 => 'exit',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.echo.php',
    1 => 'echo',
  ),
  'next' =>
  array (
    0 => 'gshell.export.php',
    1 => 'export',
  ),
  'lastupdate' =>
  array (
	0 => '2011-12-19',
	1 => 'Administrator',
  ),
);
$setup["toc"] = $TOC;
$setup["parents"] = $PARENTS;
$setup["ms"] = $MS;

manual_setup($setup);
manual_header(); ?>
<h3>exit</h3><p>Termina la sessione corrente, e ripristina quella precedente.</p><p>&nbsp;</p><h4><strong>SINOSSI</strong></h4><p style="margin-left: 40px;"><strong>exit</strong><u><br /></u></p><p>&nbsp;</p><h4><strong>DESCRIZIONE</strong></h4><p>Il comando <strong>exit</strong> lo si usa principalmente dopo aver effettuato <a href="gshell.login.php">login</a> nella stessa shell con altri utenti.</p><p>&nbsp;</p><p>&nbsp; </p><h4>FILE</h4><p style="margin-left: 40px;"><span style="font-size: medium;">Il comando <strong>exit</strong>, come per il comando <a href="gshell.sudo.php">sudo</a>, &egrave; direttamente integrato nella classe <a href="../../../../share/userguide/it/class.gshell.php">GShell</a>, quindi non troverete il relativo file nella cartella <strong>gsh/</strong>.</span></p><p>&nbsp;</p><h4><strong>VEDERE&nbsp;ANCHE </strong></h4><p><a href="gshell.sudo.php">sudo</a> , <a href="gshell.login.php">login</a></p><p>&nbsp;</p><h4>INFORMAZIONI</h4><p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-base.php">gnujiko-base</a>.</p><p>&nbsp;</p>

<?php manual_footer();

