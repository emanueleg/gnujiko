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
    0 => 'gshell.cp.php',
    1 => 'cp',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.apm.php',
    1 => 'apm',
  ),
  'next' =>
  array (
    0 => 'gshell.dockbar.php',
    1 => 'dockbar',
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
<h3>cp</h3> <p>Copia file e directory.</p> <p>&nbsp;</p> <h4><strong>SINOSSI</strong></h4> <p style="margin-left: 40px;"><strong>cp</strong> <u>SOURCE</u>&nbsp;<u>DESTINATION</u><br /> <strong>cp</strong> -s <u>SOURCE</u> -d <u>DESTINATION<br /> </u></p> <p>&nbsp;</p> <h4><strong>DESCRIZIONE</strong></h4> <p>Il comando <strong>cp</strong> , se lanciato da utente normale (non root), copia file e directory all&lsquo;interno della cartella <strong>home</strong> dell&lsquo;utente loggato.<br /> Nel file di configurazione di Gnujiko (config.php) &egrave; possibile modificare la directory di base per gli utenti (che di default &egrave; home/) impostando la variabile <strong>$_USERS_HOMES</strong>.</p> <p>&nbsp;</p> <h4>OUTPUT</h4> <p>Non viene ritornato alcun messaggio sottoforma di array di informazioni, ma solamente un messaggio relativo all&lsquo;esito dell&lsquo;operazione.</p> <p>&nbsp;</p> <h4>ERRORI</h4> <p style="margin-left: 40px;"><strong>INVALID_USER</strong> - E&lsquo; necessario essere loggati nel sistema. Probabilmente stai lanciando il comando con l&lsquo;utente www-data.<br /><br /> <strong>INVALID_SOURCE</strong> - Hai dimenticato di specificare il file/directory da copiare.<br /><br /> <strong>INVALID_DESTINATION</strong> - Hai dimenticato di specificare il file/directory di destinazione.<br /><br /> <strong>SRC_DOES_NOT_EXISTS</strong> - Il file/directory sorgente specificato non esiste.<br /><br /> <strong>PERMISSION_DENIED</strong> - Impossibile copiare il file o la directory. Controllare i diritti di accesso in scrittura alla cartella di destinazione.<br /> &nbsp;</p> <p>&nbsp;</p> <h4><strong>ESEMPI</strong></h4> <p><tt>cp mydoc.pdf mydocs/mydoc.pdf</tt><br /> <tt>cp myfolder/ myfolders/mixed/</tt><br /> <tt>sudo cp /tmp/test.txt</tt><tt> /home/admin/</tt><br /> &nbsp;</p>  <p>&nbsp;</p> <h4>FILE</h4> <p style="margin-left: 40px;"><span style="font-size: medium;">gsh/cp.php</span></p> <p>&nbsp;</p> <h4><strong>VEDERE&nbsp;ANCHE </strong></h4> <p><a href="gshell.rm.php">rm</a> , <a href="gshell.mv.php">mv</a> , <a href="gshell.ls.php">ls</a> , <a href="gshell.mkdir.php">mkdir</a></p> <p>&nbsp;</p> <h4>INFORMAZIONI</h4> <p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-base.php">gnujiko-base</a>.</p> <p>&nbsp;</p>

<?php manual_footer();

