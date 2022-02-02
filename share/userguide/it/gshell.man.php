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
    0 => 'gshell.man.php',
    1 => 'man',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.mainmenu.php',
    1 => 'mainmenu',
  ),
  'next' =>
  array (
    0 => 'gshell.mkdir.php',
    1 => 'mkdir',
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
<h3>man</h3><p>Manuale dei comandi gshell.</p><p>&nbsp;</p><h4><strong>SINOSSI</strong></h4><p style="margin-left: 40px;"><strong>man</strong> <span style="text-decoration: underline;">COMMAND</span><u><br /></u></p><p>&nbsp;</p><h4><strong>DESCRIZIONE</strong></h4><p>Il comando <span style="font-weight: bold;">man</span> , seguito dal nome del comando, mostra a video la guida al suo utilizzo.<br />Tutti i manuali dei comandi si trovano nella cartella <strong>etc/man/</strong>.</p><p>&nbsp;</p><h4>OUTPUT</h4><p>Mostra semplicemente a video il manuale in formato HTML.<strong><br /></strong></p><p>&nbsp;</p><h4>ERRORI</h4><p style="margin-left: 40px;"><strong>INVALID_MANUAL_NAME</strong> - Hai dimenticato di inserire il nome del comando.<br /><strong>MANUAL_NOT_FOUND</strong> - Il manuale del comando specificato non esiste.<br />&nbsp;</p><p>&nbsp;</p><h4>FILE</h4><p style="margin-left: 40px;"><span style="font-size: medium;">gsh/man.php</span></p><p>&nbsp;</p><h4>INFORMAZIONI</h4><p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-base.php">gnujiko-base</a>.</p><p>&nbsp;</p>

<?php manual_footer();

