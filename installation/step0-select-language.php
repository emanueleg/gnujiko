<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-02-2013
 #PACKAGE: makedist
 #DESCRIPTION: Select language form.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_LANGUAGE;

installer_begin(i18n("Install &raquo; Select language"), i18n("welcome"));
?>
<style type='text/css'>
table.form td.tux {background: url(img/tux-clava.png) top right no-repeat; height:200px;font-family:Arial;font-size:13px;color#000000;padding-left:50px}
</style>
<?php
installer_startContents();
?>
<h3 class='blue-comics'><?php echo i18n("Welcome to Gnujiko installation wizard"); ?></h3>
<hr/>
<table class='form' width='100%' cellspacing='0' cellpadding='0' border='0'>
<tr><td valign='middle' class='tux'><b><?php echo i18n("PLEASE SELECT YOUR LANGUAGE:"); ?></b> 
	<select id='language-select'><?php
	$langArr = array("en-GB"=>"English", "it-IT"=>"Italiano");
	while(list($k,$v) = each($langArr))
	 echo "<option value='".$k."'".($k == $_LANGUAGE ? " selected='selected'>" : ">").$v."</option>";
	?>
	</select>
</td></tr>
</table>
<?php
installer_endContents();
?>
<div class="footer">
 <a href='#' class='right-button' onclick='submit()'><span><?php echo i18n("Install"); ?> &raquo;</span></a>
</div>

<script>
function submit()
{
 var sel = document.getElementById('language-select');
 document.location.href = "index.php?lang="+sel.value+"&step=1";
}
</script>
<?php
installer_end();

