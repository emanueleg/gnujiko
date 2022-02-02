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
    0 => 'gshell.mkdir.php',
    1 => 'mkdir',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.man.php',
    1 => 'man',
  ),
  'next' =>
  array (
    0 => 'gshell.mv.php',
    1 => 'mv',
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
<h3>mkdir</h3> <p>Crea directory.</p> <p>&nbsp;</p> <h4><strong>SINOSSI</strong></h4> <p style="margin-left: 40px;"><strong>mkdir</strong> <u>DIR NAME</u><u><br /> </u></p> <p>&nbsp;</p> <h4><strong>DESCRIZIONE</strong></h4> <p>Il comando <strong>mkdir</strong> , se lanciato da utente normale (non root), crea directory all&lsquo;interno della cartella <strong>home</strong> dell&lsquo;utente loggato, altrimenti verr&agrave; creata all&lsquo;interno della directory radice di Gnujiko.</p> <p>&nbsp;</p> <h4>OUTPUT</h4> <p>Ritorna un array di informazioni.</p> <p style="margin-left: 40px;"><strong>path</strong> - Percorso completo della cartella appena creata.</p> <p>&nbsp;</p> <h4>ERRORI</h4> <p style="margin-left: 40px;"><strong>INVALID_USER</strong> -  E&lsquo; necessario essere loggati nel sistema. Probabilmente stai lanciando il comando con l&lsquo;utente www-data.<br /> <br /> <strong>UNABLE_TO_CHANGE_PATH</strong> - Nel caso abbiate abilitato l&lsquo;uso dell&lsquo;<strong>FTP</strong>&nbsp;e riscontraste questo errore, verificate nel file <strong>config.php</strong> che la variabile <strong>$_FTP_PATH</strong> sia impostata correttamente.<br /> <br /> <strong>UNABLE_TO_CREATE_FOLDER</strong> - Impossibile creare la cartella, verificate i permessi di accesso.<br /> <br /> <strong>UNABLE_TO_CHANGE_PERMISSION</strong> - La cartella &egrave; stata creata con successo, ma non &egrave; possibile cambiare automaticamente i permessi. Potrebbe non essere un problema nel caso utilizziate l&lsquo;FTP, perch&egrave; comunque il sistema invece che arrestarsi continua la sua naturale procedura, nel caso invece che non utilizziate l&lsquo;FTP&nbsp;il sistema terminer&agrave; con un errore e quindi eventuali comandi shell concatenati non verranno eseguiti.<br /> <br /> <strong>FTP_CONNECTION_FAILED</strong> - Impossibile connettersi tramite FTP. Verificate il file <strong>config.php</strong> che le variabili <strong>$_FTP_SERVER</strong> , <strong>$_FTP_USERNAME</strong> e <strong>$_FTP_PASSWORD</strong> siano impostate correttamente.<br /> &nbsp;</p> <p>&nbsp;</p> <h4><strong>ESEMPI</strong></h4> <p><tt>mkdir images/photos/</tt><br /> <tt>sudo mkdir /tmp/foo/bar/</tt><strong><br /> </strong></p>  <p>&nbsp;</p> <h4>FILE</h4> <p style="margin-left: 40px;"><span style="font-size: medium;">gsh/mkdir.php</span></p> <p>&nbsp;</p> <h4><strong>VEDERE&nbsp;ANCHE </strong></h4> <p><a href="gshell.cp.php">cp</a> , <a href="gshell.rm.php">rm</a> , <a href="gshell.mv.php">mv</a> , <a href="gshell.ls.php">ls</a></p> <p>&nbsp;</p> <h4>INFORMAZIONI</h4> <p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-base.php">gnujiko-base</a>.</p> <p>&nbsp;</p>

<?php manual_footer();

