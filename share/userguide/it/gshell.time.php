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
    0 => 'gshell.time.php',
    1 => 'time',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.system.php',
    1 => 'system',
  ),
  'next' =>
  array (
    0 => 'gshell.unzip.php',
    1 => 'unzip',
  ),
  'lastupdate' =>
  array (
	0 => '2012-01-11',
	1 => 'Administrator',
  ),
);
$setup["toc"] = $TOC;
$setup["parents"] = $PARENTS;
$setup["ms"] = $MS;

manual_setup($setup);
manual_header(); ?>
<h3>time</h3><p>Mostra la data e l&lsquo;ora di sistema.</p><p>&nbsp;</p><h4><strong>SINOSSI</strong></h4><p style="margin-left: 40px;"><strong>time</strong><u><br /></u></p><p>&nbsp;</p><h4><strong>DESCRIZIONE</strong></h4><p>Il comando <strong>time</strong> mostra a video la data di sistema nel formato <strong>gg/mm/aaaa hh:mm</strong>.</p><p>&nbsp;</p><h4>OUTPUT</h4><p>Ritorna un array di informazioni.</p><p style="margin-left: 40px;"><strong>date</strong> - Mostra la data nel formato gg/mm/aaaa.<br /><strong>time</strong> - Mostra l&lsquo;ora nel formato hh:mm. </p><p>&nbsp;</p><h4>ERRORI</h4><p>Il comando <strong>time</strong> non genera alcun tipo di errore.</p><p>&nbsp;</p><h4>FILE</h4><p style="margin-left: 40px;"><span style="font-size: medium;">gsh/time.php</span></p><p>&nbsp;</p><h4>INFORMAZIONI</h4><p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-base.php">gnujiko-base</a>.</p><p>&nbsp;</p>

<?php manual_footer();

