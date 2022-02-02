<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-01-2012
 #PACKAGE: progressbar
 #DESCRIPTION: Simple progress bar object
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/objects/progressbar/css/progressbar.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/objects/progressbar/progressbar.js" type="text/javascript"></script>
<?php

class ProgressBar
{
 var $ID;
 var $Color;
 function ProgressBar($id="progressbar", $color="blue")
 {
  $this->ID = $id;
  $this->Color = $color;
 }

 function Paint()
 {
  ?>
  <div class="progressbar" id="<?php echo $this->ID; ?>"><div class="<?php echo $this->Color; ?>" style="width:1%">&nbsp;</div></div>
  <?php
 }
}
