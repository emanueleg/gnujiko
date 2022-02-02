<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 08-02-2012
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Error message
 #VERSION: 2.1beta
 #CHANGELOG: 08-02-2012 : Sistemata la finestra.
 #TODO:
 
*/

?>
<style type='text/css'>
table.default-widget {
	background: #ffffff;
	height: 240px;
	border: 2px dashed #f31903;
}
</style>

<table width="480" cellspacing="0" cellpadding="4" class="default-widget"><tr>
	<td valign="top" width="91"><img src="icons/error.png"/></td>
	<td valign="top" style="padding-top:12px;"><h4><?php echo $_REQUEST['title']; ?></h4>
	<div class="contents"><?php echo $_REQUEST['contents']; ?></div>
	<br/><br/>
	<input type="button" onclick="gframe_close()" value="Chiudi"/>
	</td></tr>
</table>
<?php

