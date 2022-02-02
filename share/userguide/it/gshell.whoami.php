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
    0 => 'gshell.whoami.php',
    1 => 'whoami',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.users.php',
    1 => 'users',
  ),
  'next' =>
  array (
    0 => 'gshell.zip.php',
    1 => 'zip',
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
<h3>whoami</h3><p>Mostra a video il nome utente.</p><p>&nbsp;</p><h4><strong>SINOSSI</strong></h4><p style="margin-left: 40px;"><strong>whoami</strong><u><br /></u></p><p>&nbsp;</p><h4><strong>DESCRIZIONE</strong></h4><p>Il comando <strong>whoami</strong> mostra a video l&lsquo;effettivo nome utente dell&lsquo;account collegato.</p><p>&nbsp;</p><h4>OUTPUT</h4><p>Ritorna un array di informazioni.</p><p style="margin-left: 40px;"><strong>uid</strong> - ID utente.<br /><strong>gid</strong> - ID&nbsp;del gruppo principale di appartenenza.<br /><strong>uname</strong> - Nome dell&lsquo;account.</p><p>&nbsp;</p><h4>ERRORI</h4><p>Il comando <strong>whoami</strong> non genera alcun tipo di errore.<strong><br /></strong></p><p>&nbsp;</p><h4>FILE</h4><p style="margin-left: 40px;"><span style="font-size: medium;">gsh/whoami.php</span></p><p>&nbsp;</p><h4>INFORMAZIONI</h4><p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-base.php">gnujiko-base</a>.</p><p>&nbsp;</p>

<?php manual_footer();

