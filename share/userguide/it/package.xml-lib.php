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
    0 => 'package.xml-lib.php',
    1 => 'xml-lib',
  ),
  'up' =>
  array (
    0 => 'packages.php',
    1 => 'Pacchetti installati',
  ),
  'prev' =>
  array (
    0 => 'package.database-lib.php',
    1 => 'database-lib',
  ),
  'next' =>
  array (
    0 => 'package.zip-lib.php',
    1 => 'zip-lib',
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
<h3>Pacchetto xml-lib</h3><p>Libreria per il parsering di file XML.</p><p>&nbsp;</p><h4><strong>Info</strong></h4><p style="margin-left: 40px;"><strong>Section:</strong> <span style="color: rgb(0, 0, 255);"><em>library</em></span></p><p style="margin-left: 40px;"><strong>Depends:</strong></p><p style="margin-left: 40px;"><strong>Maintainer:</strong> <em>Alpatech mediaware - www.alpatech.it</em></p><p style="margin-left: 40px;"><strong>Essential: </strong><span style="color: rgb(51, 153, 102);"><em>yes</em></span></p>

<?php manual_footer();

