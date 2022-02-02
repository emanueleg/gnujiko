<?php

include_once("../include/shared-manual.inc");
include_once("ms.inc");
$TOC = array();
$PARENTS = array();

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
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'up' =>
  array (
    0 => NULL,
    1 => NULL,
  ),
  'prev' =>
  array (
    0 => 'usermanual.php',
    1 => 'Manuale Utente',
  ),
  'next' =>
  array (
    0 => 'packages.php',
    1 => 'Pacchetti installati',
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
<h3>Lista dei comandi GShell</h3><p>In questa sezione viene mostrata solamente la lista dei comandi gshell attualmente installati nel sistema.</p><p>Visitate il sito <a href="http://gnujiko.alpatech.it">http://gnujiko.alpatech.it</a> per consultare l&lsquo;intera lista dei comandi gshell esistenti.</p><p>&nbsp;</p><p><ul><li><a href='gshell.apm.php'>apm</a></li><li><a href='gshell.cp.php'>cp</a></li><li><a href='gshell.dockbar.php'>dockbar</a></li><li><a href='gshell.echo.php'>echo</a></li><li><a href='gshell.exit.php'>exit</a></li><li><a href='gshell.export.php'>export</a></li><li><a href='gshell.gpkg.php'>gpkg</a></li><li><a href='gshell.groupadd.php'>groupadd</a></li><li><a href='gshell.groupdel.php'>groupdel</a></li><li><a href='gshell.groupmod.php'>groupmod</a></li><li><a href='gshell.groups.php'>groups</a></li><li><a href='gshell.login.php'>login</a></li><li><a href='gshell.ls.php'>ls</a></li><li><a href='gshell.mainmenu.php'>mainmenu</a></li><li><a href='gshell.man.php'>man</a></li><li><a href='gshell.mkdir.php'>mkdir</a></li><li><a href='gshell.mv.php'>mv</a></li><li><a href='gshell.passwd.php'>passwd</a></li><li><a href='gshell.printenv.php'>printenv</a></li><li><a href='gshell.rm.php'>rm</a></li><li><a href='gshell.sudo.php'>sudo</a></li><li><a href='gshell.system.php'>system</a></li><li><a href='gshell.time.php'>time</a></li><li><a href='gshell.unzip.php'>unzip</a></li><li><a href='gshell.useradd.php'>useradd</a></li><li><a href='gshell.userdel.php'>userdel</a></li><li><a href='gshell.usermod.php'>usermod</a></li><li><a href='gshell.users.php'>users</a></li><li><a href='gshell.whoami.php'>whoami</a></li><li><a href='gshell.zip.php'>zip</a></li></ul></p>

<?php manual_footer();

