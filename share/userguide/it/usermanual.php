<?php

include_once("../include/shared-manual.inc");
include_once("ms.inc");
$TOC = array();
$PARENTS = array();

$setup = array (
  'section' =>
  array (
	0 => 'usermanual.php',
	1 => 'Manuale Utente',
  ),
  'home' =>
  array (
    0 => 'index.php',
    1 => 'Gnujiko 10.1 - User Guide',
  ),
  'this' =>
  array (
    0 => 'usermanual.php',
    1 => 'Manuale Utente',
  ),
  'up' =>
  array (
    0 => NULL,
    1 => NULL,
  ),
  'prev' =>
  array (
    0 => NULL,
    1 => NULL,
  ),
  'next' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
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
<h3>Manuale utente di Gnujiko 10.1</h3><p>In questa guida troverete solamente le informazioni sulle applicazioni di base installate in questo sistema.<br /><strong>Gnujiko 10.1 base</strong> &egrave; una versione minimizzata adatta pi&ugrave; agli utenti e programmatori esperti per l&lsquo;installazione autonoma dei pacchetti o per la creazione di una distribuzione personalizzata.</p><p>Consultando il sito <a href="http://gnujiko.alpatech.it">http://gnujiko.alpatech.it</a> potete trovare pacchetti di soluzioni gi&agrave; pronti, completi ed ottimizzati ad esempio per:</p><ul><li><span style="font-size: small;">La gestione di una piccola impresa. (Gestione fatturazione, magazzino, centri di costo)</span></li><li><span style="font-size: small;">Saloni, parruchieri.</span></li><li><span style="font-size: small;">Affittacamere, locande con gestore delle prenotazioni.</span></li><li><span style="font-size: small;">Cantieri.</span></li><li><span style="font-size: small;">e tanti altri.</span></li></ul><p>&nbsp;</p><p><ul><li><a href='accountmanager.php'>Gestione account</a></li><li><a href='terminaledicomando.php'>Terminale di comando</a></li></ul></p>

<?php manual_footer();

