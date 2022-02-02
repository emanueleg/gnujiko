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
    0 => 'gshell.usermod.php',
    1 => 'usermod',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.userdel.php',
    1 => 'userdel',
  ),
  'next' =>
  array (
    0 => 'gshell.users.php',
    1 => 'users',
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
<h3>usermod</h3><p>Modifica gli attributi di un utente.</p><p>&nbsp;</p><h4><strong>SINOSSI</strong></h4><p style="margin-left: 40px;"><strong>usermod</strong> [<u>opzioni</u>]<br /><strong>usermod</strong> -uid <u>USERID</u> [<u>opzioni</u>]<br /><strong>usernod</strong> <u>USERNAME</u> [<u>opzioni</u>]<br />&nbsp;</p><p>&nbsp;</p><h4><strong>OPZIONI</strong></h4><p>Il comando <strong>usermod</strong> accetta le seguenti opzioni:</p><p style="margin-left: 40px;"><strong>-home</strong> <u>HOME FOLDER</u></p><p style="margin-left: 80px;">Modifica la directory home dell&lsquo;utente.</p><p style="margin-left: 40px;"><strong>-gid</strong> <u>GROUP ID</u> , <strong>-group</strong> <u>GROUP NAME</u></p><p style="margin-left: 80px;">Cambia il gruppo principale di appartenenza.</p><p style="margin-left: 40px;"><strong>-name</strong> <u>USERNAME</u></p><p style="margin-left: 80px;">Rinomina l&lsquo;account.</p><p style="margin-left: 40px;"><strong>-password</strong> <u>NEW PASSWORD</u></p><p style="margin-left: 80px;">Modifica la password.</p><p style="margin-left: 40px;"><strong>-fullname</strong> <u>FULL&nbsp;NAME</u></p><p style="margin-left: 80px;">Modifica il nome completo.</p><p style="margin-left: 40px;"><strong>-email</strong> <u>NEW EMAIL</u></p><p style="margin-left: 80px;">Modifica l&lsquo;email principale dell&lsquo;account.</p><p style="margin-left: 40px;"><strong>--enable-shell</strong></p><p style="margin-left: 80px;">Abilita l&lsquo;utilizzo del terminale a riga di comando all&lsquo;utente.</p><p style="margin-left: 40px;"><strong>--disable-shell</strong></p><p style="margin-left: 80px;">Disabilita l&lsquo;utilizzo del terminale a riga di comando all&lsquo;utente.</p><p style="margin-left: 40px;"><strong>--disabled-password</strong></p><p style="margin-left: 80px;">Indica al sistema che questo utente non necessita di password per l&lsquo;autenticazione. Es: utenti demo.</p><p style="margin-left: 40px;"><strong>-privileges</strong> <u>PRIVILEGES</u></p><p style="margin-left: 80px;">Assegna le variabili relative ai privilegi.<br />Es: mkdir_enable=1,edit_account_info=0,run_sudo_commands=0<br />Le variabili con il loro valore devono essere separate con una virgola (,)<br />I nomi delle variabili equivalgono ai nomi nei campi della tabella gnujiko_user_privileges.</p><p>&nbsp;</p><h4>OUTPUT</h4><p style="margin-left: 40px;">Non ritorna alcun messaggio sottoforma di array, solamente un messaggio di testo sull&lsquo;avvenuta operazione.<br />&nbsp;</p><p>&nbsp;</p><h4>ERRORI</h4><p style="margin-left: 40px;"><strong>PERMISSION_DENIED</strong> - Il comando necessita, in certe opzioni, di essere eseguito da amministratore (root)<br /><strong>GROUP_DOES_NOT_EXISTS</strong> - Il gruppo specificato non esiste.<br /><strong>USER_DOES_NOT_EXISTS</strong> - L&lsquo;utente specificato non esiste.<br />&nbsp;</p><p>&nbsp;</p><h4><strong>ESEMPI</strong></h4><p><tt>usermod -fullname &lsquo;Mario Rossi&lsquo; -email &lsquo;mariorossi@gmail.com&lsquo;</tt><br /><br />&nbsp;</p><div class="indent"><dl><dt>Attenzione:</dt><dd>Per modificare certi attributi agli utenti &egrave; necessario avere i privilegi di <b>root</b> (il Super Utente), quindi occorre anteporre il comando <a href="gshell.sudo.php"><b>sudo</b></a>.</dd></dl> <p><tt>sudo usermod -uid 5 -name &lsquo;Mario Rossi&lsquo; -password <span style="font-family: Arial,Verdana,sans-serif;">&lsquo;70p53cre7</span></tt>&lsquo;<br /> <tt>sudo usermod -uid 5 --disable-shell</tt><br /><tt>sudo usermod admin -name &quot;Amministratore&quot; -password &lsquo;aabbcc&lsquo;</tt><br /> <strong><br /></strong></p></div><p>&nbsp;</p><h4>FILE</h4><p style="margin-left: 40px;"><span style="font-size: medium;">gsh/usermod.php</span></p><p>&nbsp;</p><h4><strong>VEDERE&nbsp;ANCHE </strong></h4><p><a href="gshell.useradd.php">useradd</a> , <a href="gshell.userdel.php">userdel</a> , <a href="gshell.users.php">users</a> , <a href="gshell.groupadd.php">groupadd</a> , <a href="gshell.groupdel.php">groupdel</a> , <a href="gshell.groupmod.php">groupmod</a> , <a href="gshell.groups.php">groups</a></p><p>&nbsp;</p><h4>INFORMAZIONI</h4><p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-accounts.php">gnujiko-accounts</a>.</p><p>&nbsp;</p>

<?php manual_footer();

