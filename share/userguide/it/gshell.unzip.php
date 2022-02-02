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
    0 => 'gshell.unzip.php',
    1 => 'unzip',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.time.php',
    1 => 'time',
  ),
  'next' =>
  array (
    0 => 'gshell.useradd.php',
    1 => 'useradd',
  ),
  'lastupdate' =>
  array (
	0 => '2012-01-11',
	1 => '',
  ),
);
$setup["toc"] = $TOC;
$setup["parents"] = $PARENTS;
$setup["ms"] = $MS;

manual_setup($setup);
manual_header(); ?>
<h3>unzip</h3> <p>Scompatta archivi compressi in formato ZIP.</p> <p>&nbsp;</p> <h4><strong>SINOSSI</strong></h4> <p style="margin-left: 40px;"><strong>unzip</strong> <u>ZIPFILE</u> <u>DESTINATIONFOLDER<br /> </u><strong>unzip</strong> -i <u>ZIPFILE</u> -o <u>DESTINATIONFOLDER<br /> <br /> </u></p> <p>&nbsp;</p> <h4>DESCRIZIONE</h4> <p>Il comando <strong>unzip</strong>, se lanciato da utente normale (non root), pu&ograve; scompattare l&lsquo;archivio solamente all&lsquo;interno della cartella home dell&lsquo;utente.</p> <p>&nbsp;</p> <h4><strong>OPZIONI</strong></h4> <p>Il comando <strong>unzip</strong> accetta le seguenti opzioni:<strong><br /> </strong></p> <p style="margin-left: 40px;"><strong>-i</strong> <span style="text-decoration: underline;">ZIPFILE</span></p> <p style="margin-left: 80px;">Indicare il file ZIP da decomprimere.</p> <p style="margin-left: 40px;"><strong>-o</strong> <span style="text-decoration: underline;">DESTINATIONFOLDER</span></p> <p style="margin-left: 80px;">Indicare la directory di destinazione.</p> <p style="margin-left: 40px;"><strong>-file</strong> <span style="text-decoration: underline;">FILENAME</span></p> <p style="margin-left: 80px;">Estrae dall&lsquo;archivio solamente il file specificato.<br /> Se omesso, invece, estrae l&lsquo;intero archivio.</p> <p style="margin-left: 40px;"><strong>&nbsp;-list</strong></p> <p style="margin-left: 80px;">Mostra solamente la lista dei file nell&lsquo;archivio senza scompattarlo. Se utilizzate questa opzione, ovviamente, non &egrave; necessario specificare il parametro <strong>-o</strong>.</p> <p>&nbsp;</p> <h4>OUTPUT</h4> <p>Ritorna un array di informazioni.</p> <p style="margin-left: 40px;"><strong>files</strong> - Array contenente tutti i nomi dei files che si trovano nell&lsquo;archivio.</p> <p style="margin-left: 80px;">Es:<br /> $outArr[&lsquo;files&lsquo;][0]&nbsp;=&nbsp;foo.pdf<br /> $outArr[&lsquo;files&lsquo;][1]&nbsp;=&nbsp;photo2.png<br /> $outArr[&lsquo;files&lsquo;][...] =&nbsp;.............</p> <p>&nbsp;</p> <h4>ERRORI</h4> <p style="margin-left: 40px;"><strong>LIBRARY_DOES_NOT_EXISTS</strong> - Manca la libreria per decomprimere i file. <br /> Verificare che esista il file <strong>var/lib/zip/unzip.lib.php</strong> e che abbia i permessi in lettura.<br /> <br /> <strong>FILE_DOES_NOT_EXISTS</strong> - Il file compresso specificato non esiste.<br /> <br /> <strong>PERMISSION_DENIED</strong> - Impossibile scompattare l&lsquo;archivio nella directory di destinazione. Verificare i permessi in scrittura di tale directory.<br /> <br /> <strong>UNABLE_TO_CONNECT_WITH_SERVER</strong> - Impossibile connettersi tramite FTP. Verificate il file <strong>config.php</strong> che le variabili <strong>$_FTP_SERVER</strong> , <strong>$_FTP_USERNAME</strong> e <strong>$_FTP_PASSWORD</strong> siano impostate correttamente.<br /> <br /> <strong>FTP_LOGIN_FAILED</strong> -  Connessione al server FTP&nbsp;fallita. Come sopra, verificate nel file di configurazione che le variabili <strong>$_FTP_SERVER</strong> , <strong>$_FTP_USERNAME</strong> e <strong>$_FTP_PASSWORD</strong> siano impostate correttamente.<br /> &nbsp;</p> <p>&nbsp;</p> <h4><strong>ESEMPI</strong></h4> <p><tt>unzip myzip.zip tmp/docs/</tt><br /> <tt>unzip -i myzip.zip -o tmp/docs/</tt><br /> <tt>unzip -i myzip.zip -o tmp/docs/ -file doc1.pdf<br /> unzip -i myzip.zip -list</tt><strong><br /> </strong></p> <p>&nbsp;</p> <h4>FILE</h4> <p style="margin-left: 40px;"><span style="font-size: medium;">gsh/unzip.php</span></p> <p>&nbsp;</p> <h4><strong>VEDERE&nbsp;ANCHE </strong></h4> <p><a href="gshell.zip.php">zip</a></p> <p>&nbsp;</p> <h4>INFORMAZIONI</h4> <p>Questo comando &egrave; incluso nel pacchetto <a href="package.zip-lib.php">zip-lib</a>.</p> <p>&nbsp;</p>

<?php manual_footer();

