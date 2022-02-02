<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-09-2016
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: GCommercialDocs configuration
 #VERSION: 2.2beta
 #CHANGELOG: 17-09-2016 : Aggiunto macro-categoria Altro...
			 31-05-2016 : Aggiunto argomento continue.
			 07-12-2014 : Aggiunto sezioni: Interfaccia, Causali e Colonne personalizzabili.
 #TODO:
 
*/

$_APPLICATION_CONFIG = array(
	"appname"=>"Configurazione dei documenti commerciali",
	"basepath"=>"aboutconfig/gcommercialdocs/",
	"pathway"=>array("title"=>"Documenti Commerciali", "url"=>"index.php?continue=".$_REQUEST['continue']),
	"restrictedaccess" => true,
	"mainmenu"=>array(
	 0 => array('title'=>"Interfaccia", 'url'=>"index.php?continue=".$_REQUEST['continue']),
	 1 => array('title'=>"Causali documenti", 'url'=>"causals.php?continue=".$_REQUEST['continue']),
	 2 => array('title'=>"Colonne personalizzabili", 'url'=>"columnsettings.php?continue=".$_REQUEST['continue']),
	 3 => array("title"=>"Avvisi", "url"=>"alerts.php?continue=".$_REQUEST['continue']),
	 4 => array("title"=>"Altro...", "url"=>"other.php?continue=".$_REQUEST['continue']),
	)
);
