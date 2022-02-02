<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 20-11-2012
 #PACKAGE: dynarc-attachments-extension
 #DESCRIPTION: Attachments support for categories and items into archives managed by Dynarc. Sheet for dynarc.navigator.
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_ARCHIVE_PREFIX, $_ARCHIVE_INFO, $_ITEM_INFO, $_PARENT_INFO, $_PATHWAY;

?>
<script>
var layerAttachmentsIsLoaded = false;
Navigator.registerPage("attachments");

function dynarc_edititem_plugin_attachments_showPage()
{
 if(!layerAttachmentsIsLoaded)
  NewLayer("dyn-attachments","archiveprefix=<?php echo $_ARCHIVE_PREFIX; ?>&id=<?php echo $_ITEM_INFO['id']; ?>&tableheight=340px",document.getElementById('attachments'),true,function(){layerAttachmentsIsLoaded=true; Navigator.showPage("attachments");});
 else
  Navigator.showPage("attachments");
}
</script>
<?php


function dynarc_edititem_plugin_attachments_injectTab()
{
 global $_ITEM_INFO;
 return "<span onclick='dynarc_edititem_plugin_attachments_showPage()'>Allegati (".count($_ITEM_INFO['attachments']).")</span>";
}

function dynarc_edititem_plugin_attachments_pageContents()
{
 echo "<div id='attachments' style='display:none;padding:8px;'></div>";
}
