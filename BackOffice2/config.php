<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-05-2017
 #PACKAGE: backoffice2
 #DESCRIPTION: Back Office 2
 #VERSION: 2.6beta
 #CHANGELOG: 28-05-2017 : Aggiunto statistiche vendita x zona e x agente.
			 16-07-2017 : Integrate statistiche e riepiloghi per ordini evasi
			 14-07-2014 : Integrato lo scadenziario passivi.
			 15-04-2014 : Ripristinato il menu.
			 06-02-2014 : Aggiunto le statistiche acquisti e di vendita.
 #TODO:
 
*/

LoadLanguage("backoffice");

$_APPLICATION_CONFIG = array(
	"appname"=>"BackOffice",
	"basepath"=>"BackOffice2/",
	"mainmenu"=>array(
	 0 => array("title"=>i18n("Schedule"), "url"=>"schedule.php"),
	 1 => array("title"=>i18n("Expenses"), "url"=>"expenses.php"),
	 2 => array("title"=>i18n("Ri.Ba."), "url"=>"riba.php"),
	)
);

if(file_exists("sales-stats.php"))
 $_APPLICATION_CONFIG['mainmenu'][] = array("title"=>i18n("Sales stats"), "url"=>"sales-stats.php");
if(file_exists("sales-stats-by-zone.php"))
 $_APPLICATION_CONFIG['mainmenu'][] = array("title"=>i18n("Sales stats by zone"), "url"=>"sales-stats-by-zone.php");
if(file_exists("sales-stats-by-agent.php"))
 $_APPLICATION_CONFIG['mainmenu'][] = array("title"=>i18n("Sales stats by agent"), "url"=>"sales-stats-by-agent.php");
if(file_exists("orders-stats.php"))
 $_APPLICATION_CONFIG['mainmenu'][] = array("title"=>"Statistiche ordini evasi", "url"=>"orders-stats.php");
if(file_exists("purchases-stats.php"))
 $_APPLICATION_CONFIG['mainmenu'][] = array("title"=>i18n("Purchases stats"), "url"=>"purchases-stats.php");
if(file_exists("topcharts.php"))
 $_APPLICATION_CONFIG['mainmenu'][] = array("title"=>i18n("Top charts"), "url"=>"topcharts.php");
if(file_exists("sales-summary.php"))
 $_APPLICATION_CONFIG['mainmenu'][] = array("title"=>i18n("Sales summary"), "url"=>"sales-summary.php");
if(file_exists("orders-summary.php"))
 $_APPLICATION_CONFIG['mainmenu'][] = array("title"=>"Riepilogo vendite da ordini evasi", "url"=>"orders-summary.php");

