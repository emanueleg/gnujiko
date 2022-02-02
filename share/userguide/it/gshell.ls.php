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
    0 => 'gshell.ls.php',
    1 => 'ls',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.login.php',
    1 => 'login',
  ),
  'next' =>
  array (
    0 => 'gshell.mainmenu.php',
    1 => 'mainmenu',
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
<h3>ls</h3> <p>Lista di file e directory.</p> <p>&nbsp;</p> <h4><strong>SINOSSI</strong></h4> <p style="margin-left: 40px;"><strong>ls</strong> [<u>opzioni</u>] [<u>BASEDIR</u>]</p> <p>&nbsp;</p> <h4><strong>OPZIONI</strong></h4> <p>Il comando <strong>ls</strong> accetta le seguenti opzioni:</p> <p style="margin-left: 40px;"><strong>--order-by</strong> &quot;name ASC&quot; , <strong>--order-by</strong> &quot;name DESC&quot;</p> <p style="margin-left: 80px;">Ordina alfabeticamente i risultati. Usando il parametro &quot;name ASC&quot; ordina dalla A alla Z, mentre il parametro &quot;name DESC&quot; dalla Z alla A. E&lsquo; obbligatorio, siccome tra i due argomenti vi &egrave; uno spazio, racchiuderli tra virgolette &quot; &quot;.</p> <p style="margin-left: 40px;"><strong>-tree</strong></p> <p style="margin-left: 80px;">Aggiungendo questo parametro verr&agrave; mostrata l&lsquo;intero albero della directory.</p> <p>&nbsp;</p> <p>&nbsp;</p> <h4>OUTPUT</h4> <p>Ritorna un array di informazioni.</p> <p style="margin-left: 40px;"><strong>dirs</strong> - Elenco delle directory sottoforma di array dove al suo interno, per ogni directory, troverete un array di informazioni:</p> <p style="margin-left: 80px;"><strong>name</strong> - Nome della directory.<br /> <strong>path</strong> - Intero percorso della directory.<br /> <strong>subdirs</strong> - Eventuali sottodirectory. (solamente se utilizzate l&lsquo;opzione -tree)</p> <p style="margin-left: 40px;"><strong>files</strong> - Elenco dei files sottoforma di array dove al suo interno, per ogni file, troverete un array di informazioni:</p> <p style="margin-left: 80px;"><strong>name</strong> - Nome del file.<br /> <strong>path</strong> - Intero percorso del file.<br /> <strong>size</strong> - Dimensione del file espresso in bytes. Es: 1240.<br /> <strong>humansize</strong> - Dimensione del file in formato testuale. Es: 1.24 kB.<br /> <strong>mtime</strong> - Data del file.</p> <p>&nbsp;</p> <h4>ERRORI</h4> <p style="margin-left: 40px;"><strong>INVALID_USER</strong> - E&lsquo; necessario essere loggati nel sistema. Probabilmente stai lanciando il comando con l&lsquo;utente www-data.<br /> <br /> <strong>INVALID_DIRECTORY</strong> - La directory specificata non esiste.<br /> &nbsp;</p> <p>&nbsp;</p> <h4><strong>ESEMPI</strong></h4> <p><tt>ls --order-by &lsquo;name ASC&lsquo;</tt><br /> <tt>ls myimages/myphotos/ --order-by &quot;name DESC&quot;</tt><strong><br /> </strong></p>  <p>&nbsp;</p> <h4>FILE</h4> <p style="margin-left: 40px;"><span style="font-size: medium;">gsh/ls.php</span></p> <p>&nbsp;</p> <h4><strong>VEDERE&nbsp;ANCHE </strong></h4> <p><a href="gshell.cp.php">cp</a> , <a href="gshell.rm.php">rm</a> , <a href="gshell.mv.php">mv</a> , <a href="gshell.mkdir.php">mkdir</a></p> <p>&nbsp;</p> <h4>INFORMAZIONI</h4> <p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-base.php">gnujiko-base</a>.</p> <p>&nbsp;</p>

<?php manual_footer();

