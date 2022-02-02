<?php

include_once("../include/shared-manual.inc");
include_once("ms.inc");
$TOC = array();
$PARENTS = array();
include_once("./toc/usermanual.inc");

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
    0 => 'terminaledicomando.php',
    1 => 'Terminale di comando',
  ),
  'up' =>
  array (
    0 => 'usermanual.php',
    1 => 'Manuale Utente',
  ),
  'prev' =>
  array (
    0 => 'accountmanager.php',
    1 => 'Gestione account',
  ),
  'next' =>
  array (
    0 => NULL,
    1 => NULL,
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
<h3>Terminale di comando</h3><p><img align="right" src="image/gterminal.png" style="border: 1px solid rgb(51, 100, 195);" alt="" />Attraverso il terminale a riga di comando &egrave; possibile svolgere gran parte delle mansioni sul framework di Gnujiko: amministrare utenti e gruppi, creare archivi dinamici, creare file e cartelle, e tante altre cose.</p><p>&nbsp;</p><p>&nbsp;</p><p>Tante delle azioni sopra elencate possono essere svolte tramite interfaccia grafica, tuttavia il terminale pu&ograve; essere utile:</p><ul><li><span style="font-size: small;">In casi di emergenza.</span></li><li><span style="font-size: small;">qualora sussistano dei malfunzionamenti nell&lsquo;interfaccia grafica di un applicazione.</span></li><li><span style="font-size: small;">in un sistema di clustering o sharing dove &egrave; possibile interagire sulle shell Gnujiko dei server remoti.</span></li><li><span style="font-size: small;">Per un interazione pi&ugrave; comoda di tutte le applicazioni.</span></li></ul><p>&nbsp;</p><p>E&lsquo; possibile consultare la lista di tutti i comandi disponibili su questo sistema visitando la sezione <a href="gshellcommands.php">Comandi GShell</a> di questa guida.</p>

<?php manual_footer();

