<?php

include_once("../include/shared-manual.inc");
include_once("ms.inc");
$TOC = array();
$PARENTS = array();

$setup = array (
  'section' =>
  array (
	0 => '.php',
	1 => '',
  ),
  'home' =>
  array (
    0 => 'index.php',
    1 => 'Gnujiko 10.1 - User Guide',
  ),
  'this' =>
  array (
    0 => 'index.php',
    1 => 'home',
  ),
  'up' =>
  array (
    0 => NULL,
    1 => NULL,
  ),
  'prev' =>
  array (
    0 => NULL,
    1 => NULL,
  ),
  'next' =>
  array (
    0 => NULL,
    1 => NULL,
  ),
  'lastupdate' =>
  array (
	0 => '2011-12-03',
	1 => 'Administrator',
  ),
);
$setup["toc"] = $TOC;
$setup["parents"] = $PARENTS;
$setup["ms"] = $MS;

manual_setup($setup);
manual_header(); ?>
<h3>Introduzione a Gnujiko 10.1 Framework</h3><p>Nato come  software per la gestione di piccole aziende e negozi, Gnujiko nel corso  degli anni &egrave; diventato un framework per la realizzazione di qualsiasi  tipo di applicazione web-based, non solo gestionali aziendali.</p><p>Dalla  versione 7.07 che comprendeva una gestione base dei documenti  commerciali, alla versione 8.01 personalizzata in pi&ugrave; soluzioni  (Negozianti, Parrucchieri, gestione camere d&lsquo;albergo, e tanti altri) che  includevano molte pi&ugrave; applicazioni, c&lsquo;&egrave; stato un cambiamento quasi  radicale nella nuova versione 10.1.</p><p>Come gi&agrave; utilizzato dalla  versione precedente, Gnujiko 10.1 offre una shell integrata molto pi&ugrave;  efficiente con la possibilit&agrave; di un interfaccia grafica a layers (o  comunemente chiamate widgets o forms) per una maggior usabilit&agrave; delle  applicazioni da parte dell&lsquo;utente, ed una pi&ugrave; comoda e veloce  programmazione da parte dello sviluppatore.</p><p>&nbsp;</p><h4>Browser compatibili</h4><p>Gnujiko 10.1 attualmente &egrave; compatibile con i seguenti browser:</p><table cellspacing="1" cellpadding="1" border="0" width="200"><tbody><tr><td><img width="48" height="48" alt="" src="image/firefox.png" /></td><td><a href="http://www.mozilla.org"><span style="font-size: large;">Mozilla Firefox</span></a></td></tr><tr><td><img width="48" height="48" alt="" src="image/google_chrome.png" /></td><td><a href="http://www.google.com/chrome"><span style="font-size: large;">Google Chrome</span></a></td></tr><tr><td><img width="48" height="48" src="image/opera.png" alt="" /></td><td><a href="http://www.opera.com"><span style="font-size: large;">Opera Browser</span></a></td></tr></tbody></table><p>&nbsp;</p><p><a href="usermanual.php"><span style="font-size: medium;">Consulta il manuale utente delle applicazioni installate su questo sistema.</span></a><span style="font-size: medium;"> &raquo;</span></p>

<?php manual_footer();

