<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-02-2013
 #PACKAGE: idoc-config
 #DESCRIPTION: IDoc choice preview.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";
define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$_IDOC_AP = $_REQUEST['idocap'];
$_IDOC_CAT = $_REQUEST['idoccat'];
$_IDOC_ID = $_REQUEST['idocid'];

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Gnujiko - IDoc preview</title>
</head><body>
<?php
/* Get model */
if($_IDOC_AP && $_IDOC_ID)
{
 $ret = GShell("dynarc item-info -ap `".$_IDOC_AP."` -id `".$_IDOC_ID."` -extget css -get params",$_REQUEST['sessid'],$_REQUEST['shellid']);
 if(!$ret['error'])
 {
  echo "<style type='text/css'>".$ret['outarr']['css'][0]['content']."</style>";
  echo $ret['outarr']['desc'];
 }
}
?>
</body></html>
<?php

