<?php

include_once("../include/shared-manual.inc");
include_once("ms.inc");
$TOC = array();
$PARENTS = array();
include_once("./toc/usermanual.inc");

$setup = array (
  'section' =>
  array (
	0 => 'usermanual.php',
	1 => 'Manuale Utente',
  ),
  'home' =>
  array (
    0 => 'index.php',
    1 => 'Gnujiko 10.1 - User Guide',
  ),
  'this' =>
  array (
    0 => 'accountmanager.php',
    1 => 'Gestione account',
  ),
  'up' =>
  array (
    0 => 'usermanual.php',
    1 => 'Manuale Utente',
  ),
  'prev' =>
  array (
    0 => NULL,
    1 => NULL,
  ),
  'next' =>
  array (
    0 => 'terminaledicomando.php',
    1 => 'Terminale di comando',
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
<h3>Gestione account</h3><p>Il pacchetto Gnujiko base &egrave; fornito di un semplice e basilare gestore degli account.</p><p><img align="right" width="256" height="175" alt="" src="image/account-manager.png" />Tramite questo strumento potrere modificare solamente il vostro account.</p><p>Per informazioni, invece, sulla gestione degli utenti e gruppi da parte dell&lsquo;amministratore senza scaricare alcun pacchetto aggiuntivo &egrave; possibile farlo tramite il terminale di comando vedere: <a href="gshell.useradd.php">useradd</a> , <a href="gshell.userdel.php">userdel</a> , <a href="gshell.usermod.php">usermod</a>, <a href="gshell.users.php">users</a> , <a href="gshell.groupadd.php">groupadd</a> , <a href="gshell.groupdel.php">groupdel</a> , <a href="gshell.groupmod.php">groupmod</a> , <a href="gshell.groups.php">groups</a>.</p><p>&nbsp;</p><p>&nbsp;Accedendo all&lsquo;<a href="../../../accounts/index.php">account manager</a> potrete inserire:</p><ul><li><strong><span style="font-size: small;">Nome completo utente</span></strong><span style="font-size: small;"><br />Specificate il vostro nome completo. Es: Mario Rossi</span></li><li><strong><span style="font-size: small;">Nome utente</span></strong><span style="font-size: small;">&nbsp;&nbsp; <em>(non modificabile)</em><br />Questo &egrave; il vostro username con cui avete accesso al sistema. Non &egrave; possibile modificarlo.</span></li><li><strong><span style="font-size: small;">Email</span></strong><span style="font-size: small;"><br />Specificate una vostra email valida. In base alla configurazione delle varie applicazioni di Gnujiko questa email viene utilizzata per l&lsquo;invio di notifiche, messaggi, ecc.</span></li></ul><p>&nbsp;</p><h3>Cambio password</h3><p><img align="right" width="256" height="178" alt="" src="image/change-passwd.png" />Sempre all&lsquo;interno dell&lsquo;account manager vi &egrave; il <a href="../../../accounts/ChangePassword.php">link di accesso</a> alla schermata per il cambio della vostra password utente.</p><p>Per poter effettuare il cambio della password, il sistema necessita che voi inseriate la password corrente (quella vecchia). </p><p>Per le password dimenticate, invece, non esiste nel pacchetto Gnujiko base uno strumento per il ripristino delle password. Per&ograve; &egrave; possibile tramite il terminale di comando (facendolo da amministratore) lanciando il comando: </p><p><tt>sudo </tt><a href="gshell.usermod.php"><tt>usermod</tt></a><tt> <u>USERNAME</u> -password <u>NUOVA&nbsp;PASSWORD</u></tt></p><p>&nbsp;</p>

<?php manual_footer();

