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
    0 => 'gshell.useradd.php',
    1 => 'useradd',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.unzip.php',
    1 => 'unzip',
  ),
  'next' =>
  array (
    0 => 'gshell.userdel.php',
    1 => 'userdel',
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
<h3>useradd</h3><p>Aggiunge un nuovo utente al sistema.</p><p>&nbsp;</p><h4><strong>SINOSSI</strong></h4><p style="margin-left: 40px;"><strong>useradd</strong> [<u>opzioni</u>] <u>LOGIN</u></p><p>&nbsp;</p><h4><strong>OPZIONI</strong></h4><p>Il comando <strong>useradd</strong> accetta le seguenti opzioni:</p><p style="margin-left: 40px;"><strong>-home</strong> <u>HOMEFOLDER</u></p><p style="margin-left: 80px;">Specifica la directory home per l&lsquo;utente. Se omesso il sistema crea automaticamente la directory con lo stesso nome dell&lsquo;utente.</p><p style="margin-left: 40px;"><strong>-password</strong> <u>PASSWORD</u></p><p style="margin-left: 80px;">Specifica una password. (lunga quanto vuoi, non ci sono restrizioni sulla lunghezza della password)</p><p style="margin-left: 40px;"><strong>-fullname</strong> <u>NOME COMPLETO</u></p><p style="margin-left: 80px;">Specifica   il nome completo da assegnare al nuovo utente. (pu&ograve;  contenere spazi,   l&lsquo;importante che racchiudete il nome tra virgolette.<br />(Es. --full-name &lsquo;Mario Rossi&lsquo;)</p><p style="margin-left: 40px;"><strong>-group</strong> <u>GRUPPO</u></p><p style="margin-left: 80px;">Specifica il gruppo principale di appartenenza. Se omesso, verr&agrave; creato un nuovo gruppo con lo stesso nome dell&lsquo;utente.</p><p style="margin-left: 40px;"><strong>--in-group</strong> <u>GRUPPO</u></p><p style="margin-left: 80px;">Aggiunge l&lsquo;utente al gruppo assegnato. E&lsquo; possibile assegnare l&lsquo;utente ad altri gruppi utilizzando il comando <strong>groupadd</strong>.</p><p style="margin-left: 40px;"><strong>-email</strong> <u>EMAIL UTENTE</u></p><p style="margin-left: 80px;">Specifica l&lsquo;email dell&lsquo;utente.</p><p style="margin-left: 40px;"><strong>--no-create-home</strong></p><p style="margin-left: 80px;">Con questa opzione il sistema non creer&agrave; la directory home per l&lsquo;utente. <br />Utile in situazioni restrittive quando si utilizzano ad esempio degli account Guest.</p><p style="margin-left: 40px;"><strong>--enable-shell</strong></p><p style="margin-left: 80px;">Abilita  l&lsquo;utente all&lsquo;utilizzo  del terminale a riga di comando. (si  consiglia  di abilitare questa  opzione a meno utenti possibile. Diciamo  solo agli  amministratori.)</p><p style="margin-left: 40px;"><strong>--disabled-password</strong></p><p style="margin-left: 80px;">Non assegna nessuna password all&lsquo;utente. Utile per creare utenti demo.</p><p style="margin-left: 40px;"><strong>-privileges</strong> <u>PRIVILEGES</u></p><p style="margin-left: 80px;">Assegna le variabili relative ai privilegi.<br />Es: mkdir_enable=1,edit_account_info=0,run_sudo_commands=0<br />Le variabili con il loro valore devono essere separate con una virgola (,)<br />I nomi delle variabili equivalgono ai nomi nei campi della tabella gnujiko_user_privileges.</p><p>&nbsp;</p><h4>OUTPUT</h4><p>Ritorna un array di informazioni relative all&lsquo;utente appena creato:</p><p style="margin-left: 40px;"><strong>uid</strong> - ID utente.<br /><strong>gid</strong> - ID&nbsp;del gruppo principale di appartenenza.<br /><strong>name</strong> - Nome dell&lsquo;account.<br /><strong>email</strong> - Email.<br /><strong>fullname</strong> - Nome completo. Es: Mario Rossi.<br /><strong>homedir</strong> - Cartella utente.<br /><strong>regtime</strong> - Data registrazione.<br />&nbsp;</p><p>&nbsp;</p><h4>ERRORI</h4><p style="margin-left: 40px;"><strong>INVALID_USER_NAME</strong> - Hai dimenticato di inserire il nome utente.<br /><strong>USER_ALREADY_EXISTS</strong> - Indica che esiste gi&agrave; un utente registrato con lo stesso nome indicato.<br /><strong>UID_ALREADY_EXISTS</strong> - L&lsquo;ID utente che volete assegnare tramite il parametro -uid esiste gi&agrave;.<br /><strong>GID_DOES_NOT_EXISTS</strong> - Il gruppo di appartenenza indicato tramite il parametro -gid non esiste.</p><p>&nbsp;</p><h4><strong>ESEMPI</strong></h4><div class="indent"><dl><dt>Attenzione:</dt><dd>Per inserire nuovi utenti al sistema &egrave; necessario avere i privilegi di <b>root</b> (il Super Utente), quindi occorre anteporre il comando <a href="gshell.sudo.php"><b>sudo</b></a>.</dd></dl> <p><tt>sudo useradd pippo -password paperino</tt><br /> <tt>sudo useradd pippo -password paperino -home pippohome</tt><br /> <tt>sudo useradd mario -password topsecret -fullname &lsquo;Mario Rossi&lsquo; -email mariorossi@gmail.com -group admin --enable-shell</tt><strong><br /></strong></p></div><p>&nbsp;</p><h4>FILE</h4><p style="margin-left: 40px;"><span style="font-size: medium;">gsh/adduser.php</span></p><p>&nbsp;</p><h4><strong>VEDERE&nbsp;ANCHE </strong></h4><p><a href="gshell.userdel.php">userdel</a> , <a href="gshell.usermod.php">usermod</a> , <a href="gshell.users.php">users</a> , <a href="gshell.groupadd.php">groupadd</a> , <a href="gshell.groupdel.php">groupdel</a> , <a href="gshell.groupmod.php">groupmod</a> , <a href="gshell.groups.php">groups</a></p><p>&nbsp;</p><h4>INFORMAZIONI</h4><p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-accounts.php">gnujiko-accounts</a>.</p><p>&nbsp;</p>

<?php manual_footer();

