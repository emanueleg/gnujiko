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
    0 => 'gshell.groupmod.php',
    1 => 'groupmod',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.groupdel.php',
    1 => 'groupdel',
  ),
  'next' =>
  array (
    0 => 'gshell.groups.php',
    1 => 'groups',
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
<h3>groupmod</h3><p>Modifica le propriet&agrave; relative ad un dato gruppo di utenti registrato nel sistema.</p><p>&nbsp;</p><h4><strong>SINOSSI</strong></h4><p style="margin-left: 40px;"><strong>groupmod</strong> [<u>opzioni</u>] <u>GROUPNAME<br /></u></p><p>&nbsp;</p><h4><strong>OPZIONI</strong></h4><p>Il comando <strong>groupmod</strong> accetta le seguenti opzioni:</p><p style="margin-left: 40px;"><strong>-name</strong> <span style="text-decoration: underline;">NEW NAME</span></p><p style="margin-left: 80px;">Rinomina il gruppo.</p><p>&nbsp;</p><h4>OUTPUT</h4><p>Ritorna un array di informazioni.</p><p style="margin-left: 40px;"><strong>gid</strong> - ID&nbsp;del gruppo modificato.<br /><strong>name</strong> - Nome del gruppo.<br /><br />&nbsp;</p><p>&nbsp;</p><h4>ERRORI</h4><p style="margin-left: 40px;"><strong>PERMISSION_DENIED</strong> - Hai lanciato il comando groupmod senza avere i privilegi di amministratore (root).<br /><strong>INVALID_GROUP_NAME</strong> - Hai dimenticato di specificare il nome del gruppo da modificare.<br /><strong>GROUP_DOES_NOT_EXISTS</strong> - Il gruppo specificato non esiste.<br /><strong>INVALID_ARGUMENTS</strong> - Non hai specificato alcuna opzione. Praticamente non hai modificato nulla.<br />&nbsp;</p><p>&nbsp;</p><h4><strong>ESEMPI</strong></h4><div class="indent"><dl><dt>Attenzione:</dt><dd>Per modificare utenti e gruppi &egrave; necessario avere i privilegi di <b>root</b> (il Super Utente), quindi occorre anteporre il comando <a href="gshell.sudo.php"><b>sudo</b></a>.</dd></dl> <p><tt>sudo groupmod mygroup -name newgroup</tt><strong><br /></strong></p></div><p>&nbsp;</p><h4>FILE</h4><p style="margin-left: 40px;"><span style="font-size: medium;">gsh/groupmod.php</span></p><p>&nbsp;</p><h4><strong>VEDERE&nbsp;ANCHE </strong></h4><p><a href="gshell.useradd.php">useradd</a> , <a href="gshell.userdel.php">userdel</a> , <a href="gshell.usermod.php">usermod</a> , <a href="gshell.users.php">users</a> ,&nbsp;<a href="gshell.groupadd.php">groupadd</a> , <a href="gshell.groupdel.php">groupdel</a> , <a href="gshell.groups.php">groups</a></p><p>&nbsp;</p><h4>INFORMAZIONI</h4><p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-accounts.php">gnujiko-accounts</a>.</p><p>&nbsp;</p>

<?php manual_footer();

