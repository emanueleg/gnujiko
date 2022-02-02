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
    0 => 'gshell.passwd.php',
    1 => 'passwd',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.mv.php',
    1 => 'mv',
  ),
  'next' =>
  array (
    0 => 'gshell.printenv.php',
    1 => 'printenv',
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
<h3>passwd</h3><p>Cambia la password dell&lsquo;utente.</p><p>&nbsp;</p><h4><strong>SINOSSI</strong></h4><p style="margin-left: 40px;"><strong>passwd</strong><br /><strong>passwd</strong> <u>USERNAME<br /></u></p><p>&nbsp;</p><h4><strong>DESCRIZIONE</strong></h4><p>Il comando <strong>passwd</strong> , se non viene specificato un nome utente, modifica la password dell&lsquo;account attualmente loggato.<br />Per poter modificare la password di altri account &egrave; necessario lanciare il comando con i privilegi di amministratore (root).</p><p>&nbsp;</p><h4>OUTPUT</h4><p>Questo comando non ritorna alcun array di informazioni, solamente un messaggio sull&lsquo;esito dell&lsquo;operazione.<strong><br /></strong></p><p>&nbsp; &nbsp;</p><h4><strong>ESEMPI</strong></h4><div class="indent"><dl><dt>Attenzione:</dt><dd>Per modificare la password degli altri utenti &egrave; necessario avere i privilegi di <b>root</b> (il Super Utente), quindi occorre anteporre il comando <a href="gshell.sudo.php"><b>sudo</b></a>.</dd></dl> <p><tt>passwd<br /></tt><em>(modifica la password del proprio account. Il sistema chieder&agrave; per verifica la vecchia password)</em></p><p><tt>sudo passwd pippo<br /></tt><em>(modifica la password dell&lsquo;account pippo. Il sistema in questo caso non chieder&agrave; la vecchia password. Utile nel caso che lo strumento di ripristino della password fallisca.)</em></p></div><p>&nbsp;</p><h4>FILE</h4><p style="margin-left: 40px;"><span style="font-size: medium;">gsh/passwd.js</span></p><p>&nbsp;</p><h4><strong>VEDERE&nbsp;ANCHE </strong></h4><p><a href="gshell.login.php">login</a></p><p>&nbsp;</p><h4>INFORMAZIONI</h4><p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-accounts.php">gnujiko-accounts</a>.</p><p>&nbsp;</p>

<?php manual_footer();

