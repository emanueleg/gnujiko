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
    0 => 'gshell.echo.php',
    1 => 'echo',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.dockbar.php',
    1 => 'dockbar',
  ),
  'next' =>
  array (
    0 => 'gshell.exit.php',
    1 => 'exit',
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
<h3>echo</h3> <p>Mostra un messaggio a video.</p> <p>&nbsp;</p> <h4><strong>SINOSSI</strong></h4> <p style="margin-left: 40px;"><strong>echo</strong> [<u>STRING</u>]...<u><br /> </u></p> <p>&nbsp;</p> <h4><strong>DESCRIZIONE</strong></h4> <p>Il comando <strong>echo</strong> mostra semplicemente un messaggio a video.<br /> E&lsquo; possibile utilizzare e convertire variabili ambiente, precedentemente registrate, anteponendo il carattere &quot;$&quot; al nome della variabile.<br />Per l&lsquo;utilizzo delle variabili ambiente consultare le guide ai comandi <a href="gshell.export.php">export</a> e <a href="gshell.printenv.php">printenv</a>.<br /> &nbsp;</p> <p>&nbsp;</p> <h4>OUTPUT</h4> <p>Oltre al messaggio di testo ritorna un array di informazioni.</p> <p style="margin-left: 40px;"><strong>elements</strong> - Lista di ogni singola parola.</p> <p>&nbsp;</p> <h4>ERRORI</h4> <p>Il comando echo non ritorna alcun tipo di errore.</p> <p>&nbsp;</p> <h4><strong>ESEMPI</strong></h4> <p><tt>echo &quot;Hello, my name is Peter&quot;</tt> <tt>&quot;What&lsquo;s your name?&quot;</tt><br /> <tt>export -var MYNAME -value &quot;Mario Rossi&quot;</tt><br /> <tt>echo &quot;Welcome to Gnujiko. My name is&quot; $MYNAME &quot;how are you?&quot;</tt><strong><br /> </strong></p>  <p>&nbsp;</p> <h4>FILE</h4> <p style="margin-left: 40px;"><span style="font-size: medium;">gsh/echo.php</span></p> <p>&nbsp;</p> <h4><strong>VEDERE&nbsp;ANCHE </strong></h4> <p><a href="gshell.printenv.php">printenv</a> , <a href="gshell.export.php">export</a><u><br /></u></p> <p>&nbsp;</p> <h4>INFORMAZIONI</h4> <p>Questo comando &egrave; incluso nel pacchetto link e <a href="package.gnujiko-base.php">gnujiko-base</a>.</p> <p>&nbsp;</p>

<?php manual_footer();

