<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-05-2012
 #PACKAGE: template-basicapp
 #DESCRIPTION: Standard white flat template for applications.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/templates/basicapp/structure.css" type="text/css" />
<?php

function basicapp_header_begin($continue=true)
{
 ?>
 <div class='basicapp-header'>
 <?php
}

function basicapp_header_end()
{
 echo "</div>";
}

function basicapp_contents_begin($padding="10px",$height="94%")
{
 ?>
 <table width="100%" height="<?php echo $height; ?>" cellspacing="0" cellpadding="0" border="0">
 <tr><td valign='top' class="basicapp-contents-tdtop"><div style="width:4px;height:4px;display:block;"></div></td></tr>
 <tr><td valign='top' class="basicapp-contents-td"><div style="height:100%;padding-left:<?php echo $padding; ?>;padding-right:<?php echo $padding; ?>">
 <?php
}

function basicapp_contents_end()
{
 ?>
 </div></td></tr></table>
 <?php
}

