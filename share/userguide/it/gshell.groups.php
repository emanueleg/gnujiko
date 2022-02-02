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
    0 => 'gshell.groups.php',
    1 => 'groups',
  ),
  'up' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'prev' =>
  array (
    0 => 'gshell.groupmod.php',
    1 => 'groupmod',
  ),
  'next' =>
  array (
    0 => 'gshell.login.php',
    1 => 'login',
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
<h3>groups</h3><p>Mostra una lista dei gruppi registrati nel sistema.</p><p>&nbsp;</p><h4><strong>SINOSSI</strong></h4><p style="margin-left: 40px;"><strong>groups</strong> [<u>opzioni</u>]<u><br /></u></p><p>&nbsp;</p><h4><strong>OPZIONI</strong></h4><p>Il comando <strong>groups</strong> accetta le seguenti opzioni:</p><p style="margin-left: 40px;"><strong>--orderby</strong> <span style="text-decoration: underline;">FIELD</span></p><p style="margin-left: 80px;">Ordina la lista in base al campo <u>FIELD</u> specificato. (Es: id, name)</p><p style="margin-left: 40px;"><strong>-asc</strong> , <strong>-desc</strong></p><p style="margin-left: 80px;">Se si aggiunge l&lsquo;opzione <strong>-asc</strong> viene mostrata la lista per ordine alfabetico (dalla A alla Z)<br />se invece si utilizza l&lsquo;opzione <strong>-desc</strong> viene mostrata la lista dalla (Z alla A).<br />Se non si specifica alcuna opzione viene ordinata la lista in base al loro posizionamento nel database.</p><p>&nbsp;</p><p>&nbsp;</p><h4>OUTPUT</h4><p>Ritorna un array di informazioni.</p><p style="margin-left: 40px;"><strong>id</strong> - ID del gruppo.<br /><strong>name</strong> - Nome del gruppo.<br /><br />&nbsp;</p><p>&nbsp;</p><h4>ERRORI</h4><p style="margin-left: 40px;"><strong>PERMISSION_DENIED</strong> - Avete lanciato il comando <strong>groups</strong> senza avere i permessi di amministratore. (root)<br />&nbsp;</p><p>&nbsp;</p><h4><strong>ESEMPI</strong></h4><div class="indent"><dl><dt>Attenzione:</dt><dd>Per mostrare la lista degi gruppi &egrave; necessario avere i privilegi di <b>root</b> (il Super Utente), quindi occorre anteporre il comando <a href="gshell.sudo.php"><b>sudo</b></a>.</dd></dl> <p><tt>sudo groups</tt><br /> <tt>sudo groups --orderby name -desc</tt></p><p>&nbsp;</p></div><p>&nbsp;</p><h4>FILE</h4><p style="margin-left: 40px;"><span style="font-size: medium;">gsh/groups.php</span></p><p>&nbsp;</p><h4><strong>VEDERE&nbsp;ANCHE </strong></h4><p><a href="gshell.useradd.php">useradd</a> , <a href="gshell.userdel.php">userdel</a> , <a href="gshell.usermod.php">usermod</a> , <a href="gshell.users.php">users</a> ,&nbsp;<a href="gshell.groupadd.php">groupadd</a> , <a href="gshell.groupdel.php">groupdel</a> , <a href="gshell.groupmod.php">groupmod</a></p><p>&nbsp;</p><h4>INFORMAZIONI</h4><p>Questo comando &egrave; incluso nel pacchetto <a href="package.gnujiko-accounts.php">gnujiko-accounts</a>.</p><p>&nbsp;</p>

<?php manual_footer();

