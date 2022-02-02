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
    0 => 'useradd.php',
    1 => 'usermod',
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
    0 => NULL,
    1 => NULL,
  ),
  'lastupdate' =>
  array (
	0 => '2011-12-04',
	1 => 'Administrator',
  ),
);
$setup["toc"] = $TOC;
$setup["parents"] = $PARENTS;
$setup["ms"] = $MS;

manual_setup($setup);
manual_header(); ?>
<h3>useradd</h3><p>Aggiunge un nuovo utente al sistema.</p><p>&nbsp;</p><h4><strong>SINOSSI</strong></h4><p style="margin-left: 40px;"><strong>useradd</strong> [<u>opzioni</u>] <u>LOGIN</u></p><p>&nbsp;</p><h4><strong>OPZIONI</strong></h4><p style="margin-left: 40px;">Il comando <strong>useradd</strong> accetta le seguenti opzioni:</p><p style="margin-left: 40px;"><strong>&nbsp;&nbsp;&nbsp; -home</strong> <u>HOMEFOLDER</u><br />&nbsp;&nbsp;&nbsp; Specifica la directory home per l&lsquo;utente. Se omesso il sistema crea automaticamente la directory con lo stesso nome dell&lsquo;utente.</p><p style="margin-left: 40px;">&nbsp;&nbsp;&nbsp; <strong>-password</strong> <u>PASSWORD</u><br />&nbsp;&nbsp;&nbsp; Specifica una password. (lunga quanto vuoi, non ci sono restrizioni sulla lunghezza della password)</p><p style="margin-left: 40px;">&nbsp;&nbsp;&nbsp; <strong>-fullname</strong> <u>NOME COMPLETO</u><br />&nbsp;&nbsp;&nbsp; Specifica   il nome completo da assegnare al nuovo utente. (pu&ograve;  contenere spazi,   l&lsquo;importante che racchiudete il nome tra virgolette.<br />(Es. --full-name &lsquo;Mario Rossi&lsquo;)</p><p style="margin-left: 40px;">&nbsp;&nbsp;&nbsp; <strong>-group</strong> <u>GRUPPO</u><br />&nbsp;&nbsp;&nbsp; Specifica il gruppo principale di appartenenza. Se omesso, verr&agrave; creato un nuovo gruppo con lo stesso nome dell&lsquo;utente.</p><p style="margin-left: 40px;">&nbsp;&nbsp;&nbsp; <strong>--in-group</strong> <u>GRUPPO</u><br />&nbsp;&nbsp;&nbsp; Aggiunge l&lsquo;utente al gruppo assegnato. E&lsquo; possibile assegnare l&lsquo;utente ad altri gruppi utilizzando il comando <strong>groupadd</strong>.</p><p style="margin-left: 40px;">&nbsp;&nbsp;&nbsp;<strong> -email</strong> <u>EMAIL UTENTE</u><br />&nbsp;&nbsp;&nbsp; Specifica l&lsquo;email dell&lsquo;utente.</p><p style="margin-left: 40px;">&nbsp;&nbsp; <strong>--no-create-home</strong><br />&nbsp;&nbsp;&nbsp; Con questa opzione il sistema non creer&agrave; la directory home per l&lsquo;utente. <br />&nbsp; &nbsp; Utile in situazioni restrittive quando si utilizzano ad esempio degli account Guest.</p><p style="margin-left: 40px;">&nbsp;&nbsp;&nbsp; <strong>--enable-shell</strong><br />&nbsp;&nbsp;&nbsp; Abilita  l&lsquo;utente all&lsquo;utilizzo  del terminale a riga di comando. (si  consiglia  di abilitare questa  opzione a meno utenti possibile. Diciamo  solo agli  amministratori.)</p><p style="margin-left: 40px;">&nbsp;&nbsp;&nbsp; <strong>--disabled-password</strong><br />&nbsp;&nbsp;&nbsp; Non assegna nessuna password all&lsquo;utente. Utile per creare utenti demo.</p><p>&nbsp;</p><h4><strong>ESEMPI</strong></h4><div class="indent"><dl><dt>Attenzione:</dt><dd>Per inserire nuovi utenti al sistema &egrave; necessario avere i privilegi di <b>root</b> (il Super Utente), quindi occorre anteporre il comando <a href="gshell.sudo.php"><b>sudo</b></a>.</dd></dl> <p><tt>sudo useradd pippo -password paperino</tt><br /> <tt>sudo useradd pippo -password paperino -home pippohome</tt><br /> <tt>sudo useradd mario -password topsecret -fullname &lsquo;Mario Rossi&lsquo; -email mariorossi@gmail.com -group admin --enable-shell</tt><strong><br /></strong></p></div><p>&nbsp;</p><h4><strong>VEDERE&nbsp;ANCHE </strong></h4><p><a href="gshell.userdel.php">userdel</a>, <a href="gshell.usermod.php">usermod</a>, <a href="gshell.users.php">users</a>, <a href="gshell.groupadd.php">groupadd</a>, <a href="gshell.groups.php">groups</a></p>

<?php manual_footer();

