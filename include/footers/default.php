<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2010 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 14-11-2009
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Default page footer
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH;

?>
<style type='text/css'>
div.gnujiko-footer {
	color: #777777;
	font-size: 13px;
	font-family: Arial;
}
</style>
<div class='gnujiko-footer' align='center'>&copy;<?php echo date('Y'); ?> Alpatech mediaware - <a href="<?php echo $_BASE_PATH; ?>">Torna alla homepage</a></div>
