<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-11-2012
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Functions for manage errors in Gnujiko.
 #VERSION: 1.0beta
 #CHANGELOG:
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH;

define('VALID-GNUJIKO',1);
include_once($_BASE_PATH."include/gshell.php");

function gnujiko_show_error($code)
{
 $ret = GShell("dynarc item-info -ap gnujikoalerts -code `".$code."`");
 if($ret['error'])
  return "Non ci informazioni sulla guida riguardo questo tipo di errore.";
 $itm = $ret['outarr'];
 $out = "<span style='background:#f31903;color:#ffffff;'>GJK-ERR-CODE: <b>".$code."</b></span>";
 $out.= "<h4 style='margin:4px;'>".$itm['name']."</h4>".$itm['desc'];
 return $out;
}
