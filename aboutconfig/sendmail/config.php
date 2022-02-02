<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-05-2014
 #PACKAGE: sendmail-config
 #DESCRIPTION: Sendmail configuration panel
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

$_APPLICATION_CONFIG = array(
	"appname"=>"Configurazione della posta",
	"basepath"=>"aboutconfig/sendmail/",
	"pathway"=>array("title"=>"SendMail", "url"=>"index.php"),
	"restrictedaccess" => true,
	"mainmenu"=>array(
	 0 => array("title"=>"Settaggi", "url"=>"index.php"),
	 
	)
);
