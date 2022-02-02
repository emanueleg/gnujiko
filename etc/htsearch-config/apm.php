<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 26-05-2013
 #PACKAGE: apm
 #DESCRIPTION: Default htsearch-config file for APM.
 #VERSION: 2.1beta
 #CHANGELOG: 
 #TODO:
 
*/

function gnujikohtsearch_apm_info($sessid=0, $shellid=0)
{
 /* LIST OF DEFAULT COMMANDS,FUNCTIONS AND VARIABLES */
 $retInfo = array("commands"=>array(), "functions"=>array(), "variables"=>array());

 $retInfo['functions'][] = array(
	 "name" => "Aggiorna i pacchetti software di Gnujiko",
	 "keywords" => array("aggiorna gnujiko","fai gli aggiornamenti"),
	 "action" => array("title"=>"esegui &raquo;", "sudocommand"=>"gframe -f apm")
	);

 return $retInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_apm_varsearch($varName, $query="", $sessid=0, $shellid=0)
{
 $retInfo = array("result"=>"", "suggested"=>array(), "query"=>$query);

 return $retInfo;
}
//-------------------------------------------------------------------------------------------------------------------//

