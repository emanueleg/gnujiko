<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-02-2014
 #PACKAGE: cron
 #DESCRIPTION:
 #VERSION: 2.1beta
 #CHANGELOG: 17-02-2014 : Bug fix gform.
			 06-04-2012 : Bug fix with gframe.
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

define("VALID-GNUJIKO",1);

$_BASE_PATH = "../../";
include_once($_BASE_PATH."include/gshell.php");

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $_ARCHIVE_INFO['name']; ?> - New</title>
<?php
include_once($_BASE_PATH."var/objects/gform/index.php");
include_once($_BASE_PATH."include/js/gshell.php");
?></head><body><?php

$form = new GForm("Elimina ricorrenza", "", "simpleform", "default", "", "600", "220");
$form->Begin();
?>
<h3>Desideri eliminare solo questa occorrenza, tutte le occorrenze o tutti i prossimi eventi compreso questo?</h3>
<br/>
<div align='center'>
<input type='button' value='Solo questa occorrenza' onclick="_submit('this')"/>&nbsp;
<input type='button' value='Tutti gli eventi' onclick="_submit('all')"/>&nbsp;
<input type='button' value='Tutti i seguenti' onclick="_submit('subsequent')"/>&nbsp;
<input type='button' value='Annulla' onclick="_abort()"/>
</div>
<br/>
<?php
$form->End();
?>
<script>
function _submit(val)
{
 gframe_close("",val);
}
function _abort()
{
 gframe_close();
}
</script>
</body></html>
<?php
