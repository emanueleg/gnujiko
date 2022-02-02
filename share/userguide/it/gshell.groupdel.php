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
    0 => 'gshell.groupdel.php',
    1 => 'groupdel',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.groupadd.php',
    1 => 'groupadd',
  ),
  'next' =>
  array (
    0 => 'gshell.groupmod.php',
    1 => 'groupmod',
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
<h3>groupdel</h3><p>Rimuove un gruppo dal sistema.</p><p>&nbsp;</p><h4><strong>SINOSSI</strong></h4><p style="margin-left: 40px;"><strong>groupdel</strong> <u>GROUPNAME<br /></u></p><p>&nbsp;</p><h4><strong>DESCRIZIONE</strong></h4><p>Il comando <span style="font-weight: bold;">groupdel</span> rimuove un gruppo dal sistema, ma non gli utenti che si trovano in quel gruppo.</p><p>&nbsp;</p><p>&nbsp;</p><h4>OUTPUT</h4><p>Ritorna un array di informazioni.</p><p style="margin-left: 40px;"><strong>gid</strong> - ID&nbsp;del gruppo appena rimosso.<br /><strong>name</strong> - Nome del gruppo rimosso.<br /><br />&nbsp;</p><p>&nbsp;</p><h4>ERRORI</h4><p style="margin-left: 40px;"><strong>PERMISSION_DENIED</strong> - Hai lanciato il comando groupdel senza avere i privilegi di amministratore (root).<br /><strong>INVALID_GROUP_NAME</strong> - Hai lanciato il comando senza specificare il nome del gruppo da rimuovere.<br /><strong>GROUP_DOES_NOT_EXISTS</strong> - Il gruppo che hai specificato non esiste.<br />&nbsp;</p><p>&nbsp;</p><h4><strong>ESEMPI</strong></h4><div class="indent"><dl><dt>Attenzione:</dt><dd>Per rimuovere dei gruppi dal sistema &egrave; necessario avere i privilegi di <b>root</b> (il Super Utente), quindi occorre anteporre il comando <a href="gshell.sudo.php"><b>sudo</b></a>.</dd></dl> <p><tt>sudo groupdel mygroup</tt><br /> <strong><br /></strong></p></div><p>&nbsp;</p><h4>FILE</h4><p style="margin-left: 40px;"><span style="font-size: medium;">gsh/groupdel.php</span></p><p>&nbsp;</p><h4><strong>VEDERE&nbsp;ANCHE </strong></h4><p><a href="gshell.useradd.php">useradd</a> , <a href="gshell.userdel.php">userdel</a> , <a href="gshell.usermod.php">usermod</a> , <a href="gshell.users.php">users</a> ,&nbsp;<a href="gshell.groupadd.php">groupadd</a> , <a href="gshell.groupmod.php">groupmod</a> , <a href="gshell.groups.php">groups</a></p><p>&nbsp;</p><h4>INFORMAZIONI</h4><p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-accounts.php">gnujiko-accounts</a>.</p><p>&nbsp;</p>

<?php manual_footer();

