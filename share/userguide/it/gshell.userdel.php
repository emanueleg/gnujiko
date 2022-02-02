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
    0 => 'gshell.userdel.php',
    1 => 'userdel',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.useradd.php',
    1 => 'useradd',
  ),
  'next' =>
  array (
    0 => 'gshell.usermod.php',
    1 => 'usermod',
  ),
  'lastupdate' =>
  array (
	0 => '2012-01-11',
	1 => 'Administrator',
  ),
);
$setup["toc"] = $TOC;
$setup["parents"] = $PARENTS;
$setup["ms"] = $MS;

manual_setup($setup);
manual_header(); ?>
<h3>userdel</h3><p>Rimuove un utente da un gruppo, o da l&lsquo;intero sistema.</p><p>&nbsp;</p><h4><strong>SINOSSI</strong></h4><p style="margin-left: 40px;"><strong>userdel</strong> [<u>opzioni</u>] <u>USER</u><br /><strong>userdel</strong> [opzioni] <u>USER</u> <u>GROUP</u><br />&nbsp;</p><p>&nbsp;</p><h4><strong>OPZIONI</strong></h4><p>Nel primo caso (specificando solo l&lsquo;utente) viene rimosso l&lsquo;account dal sistema.<br />Nel secondo caso invece, viene rimosso l&lsquo;utente solamente dal gruppo specificato.</p><p style="margin-left: 40px;"><strong>-home</strong></p><p style="margin-left: 80px;">Rimuove la home directory dell&lsquo;utente dal sistema.</p><p style="margin-left: 40px;"><strong>-group</strong></p><p style="margin-left: 80px;">Rimuove anche il gruppo principale. (solamente nel caso non vi siano altri utenti membri di quel gruppo.)</p><p style="margin-left: 40px;"><strong>-all</strong></p><p style="margin-left: 80px;">Rimuove tutte le informazioni relative all&lsquo;utente inclusa la sua home directory.</p><p>&nbsp;</p><p>&nbsp;</p><h4>OUTPUT</h4><p style="margin-left: 40px;">Ritorna un semplice messaggio</p><p>&nbsp;</p><h4>ERRORI</h4><p style="margin-left: 40px;"><strong>PERMISSION_DENIED</strong> - Per eliminare un account utente bisogna avere i privilegi di <strong>root</strong>.<br /><strong>INVALID_USER_NAME</strong> - Hai dimenticato di specificare il nome utente.<br /><strong>USER_DOES_NOT_EXISTS</strong>&nbsp;- L&lsquo;utente specificato non esiste.<br /><strong>GROUP_DOES_NOT_EXISTS</strong> - Il gruppo specificato non esiste.<br />&nbsp;</p><p>&nbsp;</p><h4><strong>ESEMPI</strong></h4><div class="indent"><dl><dt>Attenzione:</dt><dd>Per rimuovere gli utenti dal sistema &egrave; necessario avere i privilegi di <b>root</b> (il Super Utente), quindi occorre anteporre il comando <a href="gshell.sudo.php"><b>sudo</b></a>.</dd></dl> <p><tt>sudo userdel pippo</tt><br /> <tt>sudo userdel pippo </tt><tt>disney</tt><br /><tt>sudo userdel -all pippo </tt><tt>disney</tt><br /> <strong><br /></strong></p></div><p>&nbsp;</p><h4>FILE</h4><p style="margin-left: 40px;"><span style="font-size: medium;">gsh/userdel.php</span></p><p>&nbsp;</p><h4><strong>VEDERE&nbsp;ANCHE </strong></h4><p><a href="gshell.useradd.php">useradd</a> , <a href="gshell.usermod.php">usermod</a> , <a href="gshell.users.php">users</a> , <a href="gshell.groupadd.php">groupadd</a> , <a href="gshell.groupdel">groupdel</a> , <a href="gshell.groupmod.php">groupmod</a> , <a href="gshell.groups.php">groups</a></p><p>&nbsp;</p><h4>INFORMAZIONI</h4><p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-accounts.php">gnujiko-accounts</a>.</p><p>&nbsp;</p>

<?php manual_footer();

