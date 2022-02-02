<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-05-2017
 #PACKAGE: rubrica
 #DESCRIPTION: 
 #VERSION: 2.6beta
 #CHANGELOG: 23-05-2017 : Integrazione con agenti.
			 02-03-2016 : recursive main menu
			 13-02-2016 : Aggiunto il cestino.
			 14-12-2014 : Aggiunto tasto stampa.
			 29-09-2014 : Aggiunto punti fidelity card.
			 15-05-2014 : Prima integrazione con aboutconfig per restrizioni import export.
 #TODO:
 
*/

global $_BASE_PATH, $_STRICT_AGENT, $_AGENT_ID;

LoadLanguage("rubrica");

$_APPLICATION_CONFIG = array(
	"appname"=>"Rubrica",
	"basepath"=>"Rubrica/",
	"mainmenu"=>array(),
);

/* Get aboutconfig */
$ret = GShell("aboutconfig get -app rubrica");
if(!$ret['error'])
 $_APPLICATION_CONFIG['aboutconfig'] = $ret['outarr'];

// Get categories
$ret = GShell("dynarc cat-tree -ap rubrica -where 'published=1'");
if(!$ret['error'])
{
 for($c=0; $c < count($ret['outarr']); $c++)
 {
  $catInfo = $ret['outarr'][$c];
  if(!$_REQUEST['cat'] && ($c == 0))
   $_REQUEST['cat'] = $catInfo['id'];

  $_APPLICATION_CONFIG['mainmenu'][] = rubrica_generateCatTree($catInfo);
 }

 if(!$_STRICT_AGENT)
 {
  $_APPLICATION_CONFIG['mainmenu'][] = array('type'=>'separator');
  $_APPLICATION_CONFIG['mainmenu'][] = array('title'=>"Cestino", 'url'=>"trash.php", 'icon'=>'img/trash-icon.png');
 }
}

/* STANDARD COLUMNS */
$_APPLICATION_CONFIG['standardcolumns'] = array(
 0 => array("title"=>"ID", "field"=>"id", "width"=>32, "sortable"=>true),
 1 => array("title"=>i18n('Code'), "field"=>"code_str", "width"=>60, "sortable"=>true),
 2 => array("title"=>i18n('Name and surname / Company name'), "field"=>"name", "sortable"=>true),
 3 => array("title"=>i18n('Labels'), "field"=>"labels", "width"=>200),
 4 => array("title"=>i18n('Address'), "field"=>"address", "width"=>200),
 5 => array("title"=>i18n('Phone'), "field"=>"phone", "width"=>80),
 6 => array("title"=>i18n('Email'), "field"=>"email", "width"=>120),
 7 => array("title"=>"Tipologia", "field"=>"iscompany", "width"=>120, "sortable"=>true),
 8 => array("title"=>"Codice fiscale", "field"=>"taxcode", "width"=>120, "sortable"=>true),
 9 => array("title"=>"Partita IVA", "field"=>"vatnumber", "width"=>120, "sortable"=>true),
 10 => array("title"=>"Fidelity Card", "field"=>"fidelitycard", "width"=>120, "sortable"=>true),
 11 => array("title"=>"Punti", "field"=>"fidelitycard_points", "width"=>120, "sortable"=>true),
 12 => array("title"=>"Listino associato", "field"=>"pricelist_id", "width"=>120, "sortable"=>true),
 13 => array("title"=>"Modalit&agrave; di pagamento", "field"=>"paymentmode", "width"=>150, "sortable"=>true),
 14 => array("title"=>"Contatto Skype", "field"=>"skype", "width"=>120),
 15 => array("title"=>"Agente di riferimento", "field"=>"agent", "width"=>150)
);

/* STANDARD BUTTONS */
$_APPLICATION_CONFIG['standardbuttons'] = array(
 "sendmail" => array("title"=>"Invia email", "icon"=>"email.gif","action"=>"sendmail", "visibled"=>false),
);

if(file_exists($_BASE_PATH."share/widgets/appointment/__appointments/new.php"))
 $_APPLICATION_CONFIG['standardbuttons']['makeappointment'] = array("title"=>"Fissa un'appuntamento", "icon"=>"calendar.gif", "action"=>"makeappointment", "visibled"=>false, "ap"=>"appointments");
if(file_exists($_BASE_PATH."share/widgets/appointment/__agentappointments/new.php"))
 $_APPLICATION_CONFIG['standardbuttons']['makeagentappointment'] = array("title"=>"Fissa un'appuntamento (x agenti)", "icon"=>"calendar.gif", "action"=>"makeappointment", "visibled"=>false, "ap"=>"agentappointments");

$_APPLICATION_CONFIG['standardbuttons']['print'] = array("title"=>"Stampa", "icon"=>"printer.gif", "action"=>"printcontact", "visibled"=>false);


/* DEFAULT COLUMNS */
$_APPLICATION_CONFIG['columns'] = array(
 0 => array("title"=>i18n('Code'), "field"=>"code_str", "width"=>60, "sortable"=>true),
 1 => array("title"=>i18n('Name and surname / Company name'), "field"=>"name", "sortable"=>true),
 2 => array("title"=>i18n('Address'), "field"=>"address", "width"=>200),
 3 => array("title"=>i18n('Phone'), "field"=>"phone", "width"=>80),
 4 => array("title"=>i18n('Email'), "field"=>"email", "width"=>120)
);

/* DEFAULT BUTTONS */


/* LOAD COLUMN AND BUTTONS PREFERENCES */
$ret = GShell("aboutconfig get -app rubrica");
if(!$ret['error'])
{
 $config = $ret['outarr'];
 if($config['usersettings'] && $config['usersettings']['columns'])
  $_APPLICATION_CONFIG['columns'] = $config['usersettings']['columns'];
 else if($config['defaultsettings'] && $config['defaultsettings']['columns'])
  $_APPLICATION_CONFIG['columns'] = $config['defaultsettings']['columns'];

 if($config['usersettings'] && $config['usersettings']['buttons'])
 {
  while(list($k,$v)=each($config['usersettings']['buttons']))
  {
   $act = $v['action'];
   if($_APPLICATION_CONFIG['standardbuttons'][$act])
	$_APPLICATION_CONFIG['standardbuttons'][$act]['visibled'] = true;
  }
 }
 else if($config['defaultsettings'] && $config['defaultsettings']['buttons'])
 {
  /*for($c=0; $c < count($config['defaultsettings']['buttons']); $c++)
   $_APPLICATION_CONFIG['standardbuttons'][$config['defaultsettings']['buttons'][$c]['action']]['visibled'] = true;*/
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function rubrica_generateCatTree($catInfo)
{
 $ret = array("title"=>$catInfo['name'], "url"=>"index.php?cat=".$catInfo['id']."&view=default", "subitems"=>array());
 if(is_array($catInfo['subcategories']) && count($catInfo['subcategories']))
 {
  for($c=0; $c < count($catInfo['subcategories']); $c++)
   $ret['subitems'][] = rubrica_generateCatTree($catInfo['subcategories'][$c]);
 }

 return $ret;
}
//-------------------------------------------------------------------------------------------------------------------//


