<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-02-2013
 #PACKAGE: idoc-config
 #DESCRIPTION: IDoc configuration form
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_ABSOLUTE_URL, $_BASE_PATH;

LoadLanguage("config-idoc");

?>
<table class='item' border='0' cellspacing='0' cellpadding='0'>
  <tr><td valign='top' width='60'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/config/icons/idoc.png" height='48'/></td>
 <td><a href='#' class='item-title' onclick="runIDocConfig()"><?php echo i18n("IDoc editor"); ?></a><br/>
 <span class='item-desc'><?php echo i18n("Customize your own interactive documents."); ?></span>
 <p> 
 <?php
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT COUNT(*) FROM dynarc_idoc_items WHERE trash='0'");
 $db->Read();
 $count = $db->record[0];
 $db->Close();

 if(!$count)
 {
  echo "<img src='".$_ABSOLUTE_URL."share/widgets/config/img/warning.png' style='float:left; margin:2px 2px;'/>";
  echo "<span class='warningtext'>".i18n("No document found.")."</span>";
 }
 else
 {
  echo "<img src='".$_ABSOLUTE_URL."share/widgets/config/img/info.jpg' style='float:left; margin:2px 2px;'/>";
  echo "<span class='infotext'>".sprintf(i18n("%d document inserted."),$count)."</span>";
 }
 ?>
 </p>
 </td></tr>
</table>

<script>
function runIDocConfig()
{
 window.top.location.href = "<?php echo $_ABSOLUTE_URL; ?>iDoc/index.php";
}
</script>
<?php

