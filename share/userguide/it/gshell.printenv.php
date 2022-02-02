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
    0 => 'gshell.printenv.php',
    1 => 'printenv',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.passwd.php',
    1 => 'passwd',
  ),
  'next' =>
  array (
    0 => 'gshell.rm.php',
    1 => 'rm',
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
<h3>printenv</h3> <p>Mostra le variabili ambiente precedentemente registrate con il comando <a href="gshell.export.php">export</a>.</p> <p>&nbsp;</p> <h4><strong>SINOSSI</strong></h4> <p style="margin-left: 40px;"><strong>printenv</strong><br /> <strong>printenv</strong> -var [VARIABLE]<br /><strong>printenv</strong> [VARIABLE]... [VARIABLE]... [VARIABLE]...<br /> </p> <p>&nbsp;</p> <h4><strong>OPZIONI</strong></h4> <p>Se non viene specificata alcuna variabile, mostra tutte le variabili registrate.<br />E&lsquo; possibile specificare pi&ugrave; variabili separandole con uno spazio, oppure utilizzando il parametro <strong>-var</strong>.</p> <p style="margin-left: 40px;"><strong>-var</strong> <span style="text-decoration: underline;">VARIABLE</span></p> <p style="margin-left: 80px;">Sepcificare la variabile ambiente da ritornare.<br />Se non viene specificata alcuna variabile, mostra la lista intera di tutte le variabili registrate.</p> <p>&nbsp;</p> <h4>OUTPUT</h4> <p>Ritorna un array contenente tutte (o in parte) le variabili ed il loro valore.</p> <p>&nbsp;</p> <h4>ERRORI</h4> <p>Il comando printenv non ritorna alcun tipo di errore.<br /> &nbsp;</p> <p>&nbsp;</p> <h4><strong>ESEMPI</strong></h4> <p><tt>printenv -var MYNAME</tt><br /> <tt>printenv -var FOO -var BAR</tt><br /> <tt>printenv FOO&nbsp;BAR</tt><strong><br /> </strong></p>  <p>&nbsp;</p> <h4>FILE</h4> <p style="margin-left: 40px;"><span style="font-size: medium;">gsh/printenv.php</span></p> <p>&nbsp;</p> <h4><strong>VEDERE&nbsp;ANCHE </strong></h4> <p><a href="gshell.echo.php">echo</a> , <a href="gshell.export.php">export</a></p> <p>&nbsp;</p> <h4>INFORMAZIONI</h4> <p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-base.php">gnujiko-base</a>.</p> <p>&nbsp;</p>

<?php manual_footer();

