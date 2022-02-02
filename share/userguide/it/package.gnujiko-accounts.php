<?php

include_once("../include/shared-manual.inc");
include_once("ms.inc");
$TOC = array();
$PARENTS = array();
include_once("./toc/packages.inc");

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
    0 => 'package.gnujiko-accounts.php',
    1 => 'gnujiko-accounts',
  ),
  'up' =>
  array (
    0 => 'packages.php',
    1 => 'Pacchetti installati',
  ),
  'prev' =>
  array (
    0 => NULL,
    1 => NULL,
  ),
  'next' =>
  array (
    0 => 'package.database-lib.php',
    1 => 'database-lib',
  ),
  'lastupdate' =>
  array (
	0 => '2011-12-17',
	1 => 'Administrator',
  ),
);
$setup["toc"] = $TOC;
$setup["parents"] = $PARENTS;
$setup["ms"] = $MS;

manual_setup($setup);
manual_header(); ?>
<h3>Pacchetto gnujiko-accounts</h3><p>Gestisce gli account di sistema.</p><p>&nbsp;</p><h4><strong>Info</strong></h4><p style="margin-left: 40px;"><strong>Section:</strong> <span style="color: rgb(0, 0, 255);"><em>system</em></span></p><p style="margin-left: 40px;"><strong>Depends:</strong></p><p style="margin-left: 40px;"><strong>Maintainer:</strong> <em>Alpatech mediaware - www.alpatech.it</em></p><p style="margin-left: 40px;"><strong>Essential:</strong> <span style="color: rgb(51, 153, 102);"><span style="font-style: italic;">yes</span></span></p>

<?php manual_footer();

