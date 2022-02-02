<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2010 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 08-05-2010
 #PACKAGE: gterminal
 #DESCRIPTION: Official Gnujiko terminal shell.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/js/gshell.php");

?>
<link rel="stylesheet" href="<?php echo $_BASE_PATH; ?>var/objects/gterminal/console.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_BASE_PATH; ?>var/objects/gterminal/gterminal.js" type="text/javascript"></script>
<?php
