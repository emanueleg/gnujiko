<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-05-2012
 #PACKAGE: htmlgutility
 #DESCRIPTION: Simple menu - loader
 #VERSION: 2.1
 #CHANGELOG: 24-05-2012 : Some bug fix in menu.js
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/objects/htmlgutility/themes/default-menu/menu.css" type="text/css" />
<script>
var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";
</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/objects/htmlgutility/screen.js" type="text/javascript"></script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/objects/htmlgutility/menu.js" type="text/javascript"></script>

