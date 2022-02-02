<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 02-10-2016
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: File di configurazione per i documenti commerciali
 #VERSION: 2.1beta
 #CHANGELOG: 02-10-2016 : Aggiunto Reso a fornitore tra le causali predefinite nei DDT.
 #TODO:
 
*/

global $_COMMERCIALDOCS_CONFIG;

$_COMMERCIALDOCS_CONFIG = array();
$_COMMERCIALDOCS_CONFIG['DOCTYPE'] = array(
	"DDT" => array(
		 "DDR" => "Reso a fornitore"
		)
);

$_COMMERCIALDOCS_CONFIG['EVENTS'] = array();
$_COMMERCIALDOCS_CONFIG['EXTOPT'] = array();


