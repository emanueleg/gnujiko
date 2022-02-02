<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 25-07-2012
 #PACKAGE: pettycashbook
 #DESCRIPTION: Edit Petty Cash Book record
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$archivePrefix = $_REQUEST['ap'] ? $_REQUEST['ap'] : "pettycashbook";

$ret = GShell("dynarc item-info -ap `".$archivePrefix."` -id `".$_REQUEST['id']."` -get `res_in,res_out,incomes,expenses`",$_REQUEST['sessid'], $_REQUEST['shellid']);
if(!$ret['error'])
{
 if($ret['outarr']['res_in'] && $ret['outarr']['res_out'])
  include_once($_BASE_PATH."share/widgets/pettycashbook/edit.transfer.php");
 else if($ret['outarr']['res_in'] || $ret['outarr']['incomes'])
  include_once($_BASE_PATH."share/widgets/pettycashbook/edit.credit.php");
 else if($ret['outarr']['res_out'] || $ret['outarr']['expenses'])
  include_once($_BASE_PATH."share/widgets/pettycashbook/edit.debit.php");
}


