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
    0 => 'gshell.mv.php',
    1 => 'mv',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.mkdir.php',
    1 => 'mkdir',
  ),
  'next' =>
  array (
    0 => 'gshell.passwd.php',
    1 => 'passwd',
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
<h3>mv</h3><p>Sposta o rinomina file e directory.</p><p>&nbsp;</p><h4><strong>SINOSSI</strong></h4><p style="margin-left: 40px;"><strong>mv</strong> <u>SOURCE</u>&nbsp;<u>DESTINATION</u><br /><strong>mv</strong> -s <u>SOURCE</u>&nbsp;-d <u>DESTINATION<br /></u></p><p>&nbsp;</p><h4><strong>DESCRIZIONE</strong></h4><p>Il comando <strong>mv</strong> , se lanciato da utente normale (non root), rinomina o sposta file e directory all&lsquo;interno della cartella <strong>home</strong> dell&lsquo;utente loggato.</p><p>&nbsp;</p><h4>OUTPUT</h4><p>Non viene ritornato alcun messaggio sottoforma di array di informazioni,  ma solamente un messaggio relativo all&lsquo;esito dell&lsquo;operazione. </p><p>&nbsp;</p><h4>ERRORI</h4><p style="margin-left: 40px;"><strong>INVALID_USER</strong> - E&lsquo; necessario essere loggati nel sistema. Probabilmente stai lanciando il comando con l&lsquo;utente www-data.<br /><br /> <strong>INVALID_SOURCE</strong> - Hai dimenticato di specificare il file/directory da rinominare/spostare.<br /><br /> <strong>INVALID_DESTINATION</strong> - Hai dimenticato di specificare il file/directory di destinazione.<br /><br /> <strong>SRC_DOES_NOT_EXISTS</strong> - Il file/directory sorgente specificato non esiste.<br /><br /> <span style="font-weight: bold;">MOVE</span><strong>_FAILED</strong> - Impossibile rinominare/spostare il file o la directory. Controllare i diritti di accesso in scrittura alla cartella/file di destinazione.<br />&nbsp;</p><p>&nbsp;</p><h4><strong>ESEMPI</strong></h4><div class="indent"><dl><dt>Attenzione:</dt><dd>Per inserire nuovi utenti al sistema &egrave; necessario avere i privilegi di <b>root</b> (il Super Utente), quindi occorre anteporre il comando <a href="gshell.sudo.php"><b>sudo</b></a>.</dd></dl> <p><tt>mv mydoc.pdf mynewdoc.pdf</tt><br /> <tt>mv myfolder/ </tt><tt>tmp/mydocs/</tt><br /> <tt>sudo mv /tmp/test.txt</tt><tt> /home/admin/</tt></p></div><p>&nbsp;</p><h4>FILE</h4><p style="margin-left: 40px;"><span style="font-size: medium;">gsh/mv.php</span></p><p>&nbsp;</p><h4><strong>VEDERE&nbsp;ANCHE </strong></h4><p><a href="gshell.cp.php">cp</a> , <a href="gshell.rm.php">rm</a> , <a href="gshell.ls.php">ls</a> , <a href="gshell.mkdir.php">mkdir</a></p><p>&nbsp;</p><h4>INFORMAZIONI</h4><p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-base.php">gnujiko-base</a>.</p><p>&nbsp;</p>

<?php manual_footer();

