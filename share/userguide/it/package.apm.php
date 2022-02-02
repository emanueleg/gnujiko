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
    0 => 'package.apm.php',
    1 => 'apm',
  ),
  'up' =>
  array (
    0 => 'packages.php',
    1 => 'Pacchetti installati',
  ),
  'prev' =>
  array (
    0 => 'package.gpkg.php',
    1 => 'gpkg',
  ),
  'next' =>
  array (
    0 => 'package.gnujiko-base.php',
    1 => 'gnujiko-base',
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
<h3>Pacchetto apm</h3><p>Tools per l&lsquo;amministrazione dei repository.</p><p>&nbsp;</p><h4><strong>Info</strong></h4><p style="margin-left: 40px;"><strong>Section:</strong> <span style="color: rgb(0, 0, 255);"><em>system</em></span></p><p style="margin-left: 40px;"><strong>Depends:</strong></p><p style="margin-left: 40px;"><strong>Maintainer:</strong> <em>Alpatech mediaware - www.alpatech.it</em></p><p style="margin-left: 40px;"><strong>Essential: </strong><span style="color: rgb(51, 153, 102);"><em>yes</em></span></p>

<?php manual_footer();

