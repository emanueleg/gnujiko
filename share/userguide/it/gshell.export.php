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
    0 => 'gshell.export.php',
    1 => 'export',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.exit.php',
    1 => 'exit',
  ),
  'next' =>
  array (
    0 => 'gshell.gpkg.php',
    1 => 'gpkg',
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
<h3>export</h3> <p>Registra / rimuove una variabile ambiente.</p> <p>&nbsp;</p> <h4><strong>SINOSSI</strong></h4> <p style="margin-left: 40px;"><strong>export</strong> -var <u>VARIABLE</u> -value <u>VALUE</u><br /> <strong>export</strong> <u>VARIABLE</u>=<u>VALUE</u><br /> &nbsp;</p> <p>&nbsp;</p> <h4><strong>OPZIONI</strong></h4> <p>Il comando <strong>export</strong> accetta le seguenti opzioni:</p> <p style="margin-left: 40px;"><strong>-var</strong> <span style="text-decoration: underline;">VARIABLE</span></p> <p style="margin-left: 80px;">Nome della variabile da registrare.</p> <p style="margin-left: 40px;"><strong>-value</strong> <span style="text-decoration: underline;">VALUE</span></p> <p style="margin-left: 80px;">Valore da registrare. Per eliminare una variabile ambiente lasciare questo parametro vuoto.</p> <p>&nbsp;</p> <h4>OUTPUT</h4> <p>Questo comando non ritorna alcun array di informazioni, solamente un messaggio sull&lsquo;esito dell&lsquo;operazione.<strong><br /> </strong></p> <p>&nbsp;</p> <h4>ERRORI<strong><br /> </strong></h4> <p>Questo comando non genera alcun tipo di errore.</p> <p>&nbsp;</p> <h4><strong>ESEMPI</strong></h4> <p><tt>export MYNAME=Mario</tt><br /> <tt>export -var MYNAME -value &quot;Mario Rossi&quot;</tt><strong><br /> </strong></p>  <p>&nbsp;</p> <h4>FILE</h4> <p style="margin-left: 40px;"><span style="font-size: medium;">gsh/export.php</span></p> <p>&nbsp;</p> <h4><strong>VEDERE&nbsp;ANCHE </strong></h4> <p><a href="gshell.echo.php">echo</a> , <a href="gshell.printenv.php">printenv</a></p> <p>&nbsp;</p> <h4>INFORMAZIONI</h4> <p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-base.php">gnujiko-base</a>.</p> <p>&nbsp;</p>

<?php manual_footer();

