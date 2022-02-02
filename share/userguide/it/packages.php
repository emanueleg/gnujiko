<?php

include_once("../include/shared-manual.inc");
include_once("ms.inc");
$TOC = array();
$PARENTS = array();

$setup = array (
  'section' =>
  array (
	0 => 'packages.php',
	1 => 'Pacchetti installati',
  ),
  'home' =>
  array (
    0 => 'index.php',
    1 => 'Gnujiko 10.1 - User Guide',
  ),
  'this' =>
  array (
    0 => 'packages.php',
    1 => 'Pacchetti installati',
  ),
  'up' =>
  array (
    0 => NULL,
    1 => NULL,
  ),
  'prev' =>
  array (
    0 => 'gshellcommands.php',
    1 => 'Comandi GShell',
  ),
  'next' =>
  array (
    0 => NULL,
    1 => NULL,
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
<h3>Lista dei pacchetti</h3><p>Qui di seguito viene fornita la lista dei pacchetti attualmente installati su questo sistema.</p><p><ul><li><a href='package.gnujiko-accounts.php'>gnujiko-accounts</a></li><li><a href='package.database-lib.php'>database-lib</a></li><li><a href='package.xml-lib.php'>xml-lib</a></li><li><a href='package.zip-lib.php'>zip-lib</a></li><li><a href='package.gpkg.php'>gpkg</a></li><li><a href='package.apm.php'>apm</a></li><li><a href='package.gnujiko-base.php'>gnujiko-base</a></li><li><a href='package.gterminal.php'>gterminal</a></li></ul></p><p>&nbsp;</p>

<?php manual_footer();

