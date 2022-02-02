<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 26-05-2013
 #PACKAGE: company-profile
 #DESCRIPTION: Default htsearch-config file for CompanyProfile.
 #VERSION: 2.1beta
 #CHANGELOG: 
 #TODO:
 
*/

function gnujikohtsearch_companyprofile_info($sessid=0, $shellid=0)
{
 /* LIST OF DEFAULT COMMANDS,FUNCTIONS AND VARIABLES */
 $retInfo = array("commands"=>array(), "functions"=>array(), "variables"=>array());

 $retInfo['functions'][] = array(
	 "name"=>"Configurazione delle aliquote IVA",
	 "keywords"=>array("modifica aliquote IVA","configura aliquote iva"),
	 "action"=>array("title"=>"esegui &raquo;", "sudocommand"=>"gframe -f config.companyprofile -params `show=vatrates`")
	);
 $retInfo['functions'][] = array(
	 "name"=>"Configurazione dei listini prezzi",
	 "keywords"=>array("configura listini prezzi","aggiungi listino prezzi"),
	 "action"=>array("title"=>"esegui &raquo;", "sudocommand"=>"gframe -f config.companyprofile -params `show=pricelists`")
	);
 $retInfo['functions'][] = array(
	 "name"=>"Configurazione delle banche aziendali",
	 "keywords"=>array("conti correnti aziendali","aggiungi conto corrente aziendale","modifica conto corrente aziendale"),
	 "action"=>array("title"=>"esegui &raquo;", "sudocommand"=>"gframe -f config.companyprofile -params `show=banks`")
	);
 $retInfo['functions'][] = array(
	 "name"=>"Personalizzazione del profilo aziendale",
	 "keywords"=>array("modifica il profilo aziendale","cambia logo ditta","modifica intestazione aziendale"),
	 "action"=>array("title"=>"esegui &raquo;", "sudocommand"=>"gframe -f config.companyprofile")
	);
 $retInfo['functions'][] = array(
	 "name"=>"Configurazione delle modalità di pagamento",
	 "keywords"=>array("configura modalità di pagamento","aggiungi modalità di pagamento"),
	 "action"=>array("title"=>"esegui &raquo;", "sudocommand"=>"gframe -f config.paymentmodes")
	);

 return $retInfo;
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikohtsearch_companyprofile_varsearch($varName, $query="", $sessid=0, $shellid=0)
{
 $retInfo = array("result"=>"", "suggested"=>array(), "query"=>$query);

 return $retInfo;
}
//-------------------------------------------------------------------------------------------------------------------//

