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
    0 => 'gshell.login.php',
    1 => 'login',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.groups.php',
    1 => 'groups',
  ),
  'next' =>
  array (
    0 => 'gshell.ls.php',
    1 => 'ls',
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
<h3>login</h3> <p>Effettua l&lsquo;accesso come altro utente.</p> <p>&nbsp;</p> <h4><strong>SINOSSI</strong></h4> <p style="margin-left: 40px;"><strong>login</strong> <u>USERNAME</u></p> <p>&nbsp;</p> <h4><strong>DESCRIZIONE</strong></h4> <p>Il comando <strong>login</strong> effettua l&lsquo;accesso con un altro account all&lsquo;interno di una shell di comando.<br /> Per effettuare il logout, e tornare all&lsquo;account precedente, digitare il comando <a href="gshell.exit.php"><strong>exit</strong></a>.</p> <p>&nbsp;</p> <h4>FILE</h4> <p style="margin-left: 40px;"><span style="font-size: medium;">gsh/login.js</span></p> <p>&nbsp;</p> <h4><strong>VEDERE&nbsp;ANCHE </strong></h4> <p><a href="gshell.passwd.php">passwd</a> , <a href="gshell.exit.php">exit</a></p> <p>&nbsp;</p> <h4>INFORMAZIONI</h4> <p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-accounts.php">gnujiko-accounts</a>.</p> <p>&nbsp;</p>

<?php manual_footer();

