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
    0 => 'gshell.zip.php',
    1 => 'zip',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.whoami.php',
    1 => 'whoami',
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
<h3>zip</h3> <p>Comprime file e cartelle nel formato ZIP.</p> <p>&nbsp;</p> <h4><strong>SINOSSI</strong></h4> <p style="margin-left: 40px;"><strong>zip</strong> <u>SOURCE</u> <u>DESTINATION</u><br /> <strong>zip</strong> -i <u>SOURCE</u> -o <u>DESTINATION</u><br /> &nbsp;</p> <p>&nbsp;</p> <h4>DESCRIZIONE</h4> <p>Il comando <strong>zip</strong> , se lanciato da utente normale (non  root), comprime file e cartelle solamente all&lsquo;interno della cartella  home dell&lsquo;utente.</p><p>&nbsp;</p> <h4><strong>OPZIONI</strong></h4> <p>Il comando <strong>zip</strong> accetta le seguenti opzioni:</p> <p style="margin-left: 40px;"><strong>-i</strong> <span style="text-decoration: underline;">SOURCE</span></p> <p style="margin-left: 80px;">Indicare il file o la cartella di origine da comprimere.<br /> E&lsquo; possibile specificare pi&ugrave; file contemporaneamente utilizzando il parametro <strong>-i</strong> pi&ugrave; volte; <br /> Es: -i FILE1 -i FILE2 -i FILE3 -o DESTINATION.</p> <p style="margin-left: 40px;"><strong>-o</strong> <u>DESTINATION</u></p> <p style="margin-left: 80px;">Indicare il file di destinazione.</p> <p>&nbsp;</p> <p>&nbsp;</p> <h4>OUTPUT</h4> <p>Ritorna un array di informazioni.</p> <p style="margin-left: 40px;"><strong>filename</strong> - Percorso completo del file di destinazione.<strong><br /> </strong></p> <p>&nbsp;</p> <h4>ERRORI</h4> <p style="margin-left: 40px;"><strong>INVALID_SOURCE_FILE</strong> - Hai dimenticato di inserire il file o la cartella sorgente da comprimere.<br /><br /> <strong>INVALID_DEST_FILE</strong> - Hai dimenticato di specificare il file di destinazione.<br /><br /> <strong>LIBRARY_DOES_NOT_EXISTS</strong> - Manca la libreria per comprimere i file. <br />Verificare che esista il file <strong>var/lib/zip/zip.lib.php</strong> e che abbia i permessi in lettura.<br /><br /> <strong>FILE_DOES_NOT_EXISTS</strong> - Il file sorgente specificato non esiste.<br /><br /> <strong>INVALID_FILE_NAME</strong> - Verificare i permessi in lettura del file sorgente specificato.<br /> &nbsp;</p> <p>&nbsp;</p> <h4><strong>ESEMPI</strong></h4> <p><tt>zip myimages/photo25.png tmp/photo25.zip</tt><br /> <tt>zip myimages/photos/ tmp/photos.zip</tt><br /> <tt>zip -i doc1.pdf -i doc2.doc -i myimages/logo.png -o output.zip</tt><strong><br /> </strong></p>  <p>&nbsp;</p> <h4>FILE</h4> <p style="margin-left: 40px;"><span style="font-size: medium;">gsh/zip.php</span></p> <p>&nbsp;</p> <h4><strong>VEDERE&nbsp;ANCHE </strong></h4> <p><a href="gshell.unzip.php">unzip</a></p> <p>&nbsp;</p> <h4>INFORMAZIONI</h4> <p>Questo comando &egrave; incluso nel pacchetto <a href="package.zip-lib.php">zip-lib</a>.</p> <p>&nbsp;</p>

<?php manual_footer();

