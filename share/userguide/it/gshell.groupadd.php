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
    0 => 'gshell.groupadd.php',
    1 => 'groupadd',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.gpkg.php',
    1 => 'gpkg',
  ),
  'next' =>
  array (
    0 => 'gshell.groupdel.php',
    1 => 'groupdel',
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
<h3>groupadd</h3> <p>Aggiunge un nuovo gruppo di utenti al sistema, oppure aggiunge un utente ad un dato gruppo.</p> <p>&nbsp;</p> <h4><strong>SINOSSI</strong></h4> <p style="margin-left: 40px;"><strong>groupadd</strong> <u>GROUPNAME</u><br /><strong>groupadd</strong> <u>GROUPNAME</u> <u>USERNAME</u></p> <p>&nbsp;</p> <h4><strong>DESCRIZIONE</strong></h4> <p>Nel primo caso, specificando solo il nome del gruppo (GROUPNAME), il sistema si limiter&agrave; a creare un nuovo gruppo vuoto.<br />Nel secondo caso invece, specificando anche il nome di un utente (USERNAME), il sistema provveder&agrave; ad inserire quell&lsquo;utente nel dato gruppo.</p> <p>&nbsp;</p> <h4>OUTPUT</h4> <p>Ritorna un array di informazioni.</p> <p style="margin-left: 40px;"><strong>uid</strong> - ID dell utente inserito nel gruppo. (nel caso si utilizzi il comando <strong>groupadd</strong> <u>nomegruppo</u> <u>nomeutente</u>)<br /> <strong>gid</strong> - ID&nbsp;del gruppo appena creato.<br /> &nbsp;</p> <p>&nbsp;</p> <h4>ERRORI</h4> <p style="margin-left: 40px;"><strong>PERMISSION_DENIED</strong> - Hai lanciato il comando <strong>groupadd</strong> senza i privilegi di amministratore (root).<br /> <strong>USER_DOES_NOT_EXISTS</strong> - L&lsquo;utente specificato non esiste.<br /> &nbsp;</p> <p>&nbsp;</p> <h4><strong>ESEMPI</strong></h4> <div class="indent"><dl>     <dt>Attenzione:</dt>     <dd>Per inserire nuovi gruppi o inserire utenti ai gruppi &egrave; necessario avere i privilegi di <b>root</b> (il Super Utente), quindi occorre anteporre il comando <a href="gshell.sudo.php"><b>sudo</b></a>.</dd> </dl> <p><tt>sudo groupadd mygroup</tt><br /> <tt>sudo groupadd mygroup pippo</tt><br /> <strong><br /> </strong></p></div> <p>&nbsp;</p> <h4>FILE</h4> <p style="margin-left: 40px;"><span style="font-size: medium;">gsh/groupadd.php</span></p> <p>&nbsp;</p> <h4><strong>VEDERE&nbsp;ANCHE </strong></h4> <p><a href="gshell.useradd.php">useradd</a> , <a href="gshell.userdel.php">userdel</a> , <a href="gshell.usermod.php">usermod</a> , <a href="gshell.users.php">users</a> ,&nbsp;<a href="gshell.groupdel.php">groupdel</a> , <a href="gshell.groupmod.php">groupmod</a> , <a href="gshell.groups.php">groups</a></p> <p>&nbsp;</p> <h4>INFORMAZIONI</h4> <p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-accounts.php">gnujiko-accounts</a>.<span style="text-decoration: underline;"><u> </u><u> </u></span></p> <p>&nbsp;</p>

<?php manual_footer();

