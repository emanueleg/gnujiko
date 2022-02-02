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
    0 => 'gshell.rm.php',
    1 => 'rm',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.printenv.php',
    1 => 'printenv',
  ),
  'next' =>
  array (
    0 => 'gshell.sudo.php',
    1 => 'sudo',
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
<h3>rm</h3> <p>Rimuove file e directory.</p> <p>&nbsp;</p> <h4><strong>SINOSSI</strong></h4> <p style="margin-left: 40px;"><strong>rm</strong> <span style="text-decoration: underline;">FILE</span> [<u>FILE 2</u>] [<u>FILE 3</u>] [<u>...</u>]</p> <p>&nbsp;</p> <h4><strong>DESCRIZIONE</strong></h4> <p>Il comando <strong>rm</strong> , se lanciato da utente normale (non root) rimuove file e directory dalla cartella home dell&lsquo;utente.<br /> E&lsquo; possibile specificare pi&ugrave; file o directory semplicemente separandoli con uno spazio.<br /> Se viene specificata una directory, il sistema rimuover&agrave; ricorsivamente tutti i file e le sottocartelle in essa contenute.</p> <p>&nbsp;</p> <h4>OUTPUT</h4> <p>Non viene ritornato alcun messaggio sottoforma di array di informazioni,  ma solamente un messaggio relativo all&lsquo;esito dell&lsquo;operazione.<br /> &nbsp;</p> <p>&nbsp;</p> <h4>ERRORI</h4> <p style="margin-left: 40px;"><strong>INVALID_USER</strong> - E&lsquo; necessario essere loggati nel sistema. Probabilmente stai lanciando il comando con l&lsquo;utente www-data.<br /><br /> <strong>INVALID_FILE</strong> - Hai dimenticato di specificare i/il file o directory da eliminare.<br /><br /> <strong>FILE_DOES_NOT_EXISTS</strong> - Il file o la cartella specificata non esiste.<br /><br /> <strong>PERMISSION_DENIED</strong> - Impossibile rimuovere il file o la cartella, verificare i diritti di accesso in scrittura.<br /><br /> <strong>INVALID_DIRECTORY_HANDLE</strong> - Il sistema non riesce a leggere i file/directory da eliminare all&lsquo;interno della cartella specificata. E&lsquo; necessario verificare i permessi di lettura a quella cartella.<br /> &nbsp;</p> <p>&nbsp;</p> <h4><strong>ESEMPI</strong></h4> <p><tt>rm mydoc.pdf</tt><br /> <tt>rm myimages/mylogo.png mydocs/mydir/</tt><strong><br /> </strong><tt>sudo </tt><tt>rm /tmp/test.txt</tt><strong><br /> </strong><tt>sudo </tt><tt>rm /home/admin/foobar.pdf</tt><br /> <strong> <br /> </strong></p> <p>&nbsp;</p> <h4>FILE</h4> <p style="margin-left: 40px;"><span style="font-size: medium;">gsh/rm.php</span></p> <p>&nbsp;</p> <h4><strong>VEDERE&nbsp;ANCHE </strong></h4> <p><a href="gshell.cp.php">cp</a> , <a href="gshell.mv.php">mv</a> , <a href="gshell.ls.php">ls</a> , <a href="gshell.mkdir.php">mkdir</a></p> <p>&nbsp;</p> <h4>INFORMAZIONI</h4> <p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-base.php">gnujiko-base</a>.</p> <p>&nbsp;</p>

<?php manual_footer();

