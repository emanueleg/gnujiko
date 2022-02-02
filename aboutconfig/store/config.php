<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-03-2016
 #PACKAGE: gstore
 #DESCRIPTION: Store configuration panel
 #VERSION: 2.1beta
 #CHANGELOG: 17-03-2016 : Aggiunto colonne personalizzabili.
 #TODO:
 
*/

$_APPLICATION_CONFIG = array(
	"appname"=>"Configurazione Magazzino",
	"basepath"=>"aboutconfig/store/",
	"pathway"=>array("title"=>"Magazzino", "url"=>"index.php"),
	"restrictedaccess" => true,
	"mainmenu"=>array(
	 0 => array("title"=>"Settaggi Magazzino", "url"=>"index.php"),
 	 1 => array("title"=>"Colonne personalizzabili", "url"=>"columnsettings.php")
	)
);
