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
    0 => 'gshell.sudo.php',
    1 => 'sudo',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.rm.php',
    1 => 'rm',
  ),
  'next' =>
  array (
    0 => 'gshell.system.php',
    1 => 'system',
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
<h3>sudo</h3><p>Esegue un comando con i privilegi di amministratore.</p><p>&nbsp;</p><h4><strong>SINOSSI</strong></h4><p style="margin-left: 40px;"><strong>sudo</strong> <span style="text-decoration: underline;">COMMAND</span><u><br /></u></p><p>&nbsp;</p><h4><strong>DESCRIZIONE</strong></h4><p>Questo comando serve ad un utente normale (non root) per eseguire dei comandi che richiedono i privilegi di amministratore.<br />Se il comando <strong>sudo</strong> viene lanciato da utente <strong>root</strong> non verr&agrave; richiesto l&lsquo;inserimento della password.</p><p>&nbsp;</p><h4>FILE</h4><p style="margin-left: 40px;"><span style="font-size: medium;">Il comando <strong>sudo</strong>, come per il comando <a href="gshell.exit.php">exit</a>, &egrave; direttamente integrato nella classe <a href="class.gshell.php">GShell</a>, quindi non troverete il relativo file nella cartella <strong>gsh/</strong>.<br /></span></p><p>&nbsp;</p><h4>VEDERE&nbsp;ANCHE</h4><p><a href="gshell.exit.php">exit</a></p><h4>INFORMAZIONI</h4><p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-base.php">gnujiko-base</a>.</p><p>&nbsp;</p>

<?php manual_footer();

