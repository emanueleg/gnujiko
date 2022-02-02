<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 19-12-2013
 #PACKAGE: hacktvsearch-common 
 #DESCRIPTION: 
 #VERSION: 2.1beta
 #CHANGELOG: 19-12-2013 : Aggiunta funzione per importare da tabella HTML
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH;

$_BASE_PATH = "../../../../";

define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

include_once($_BASE_PATH."var/objects/htmlgutility/menu.php");
include_once($_BASE_PATH."var/objects/gmutable/index.php");

$imgFolder = $_ABSOLUTE_URL."var/layers/hacktvforms/emptytable/img/";

$_TOTALS_COLUMNS = array();

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/emptytable/emptytable.css" type="text/css" />
<script type="text/javascript" src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/emptytable/emptytable.js"></script>
<ul class="basicmenu" id="xtb-<?php echo $_REQUEST['layerid']; ?>-mainmenu">
 <li class='gray'><span>Menu</span>
	<ul class='submenu'>
	 <li onclick="hacktvform_emptytable_addRow(<?php echo $_REQUEST['layerid']; ?>)">
		<img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/emptytable/img/add_small.png"/> Aggiungi nuova riga</li>

	 <li onclick="hacktvform_emptytable_importFromCommand(<?php echo $_REQUEST['layerid']; ?>)">
		<img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/emptytable/img/add_small.png"/> Importa da comando GShell</li>

	 <li class="separator">&nbsp;</li>


	 <li onclick="hacktvform_emptytable_manageColumns(<?php echo $_REQUEST['layerid']; ?>)">
		<img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/emptytable/img/manage-columns.png"/> Gestisci colonne</li>

	 <li class="separator">&nbsp;</li>

	 <!-- <li onclick="hacktvform_emptytable_printPreview(<?php echo $_REQUEST['layerid']; ?>)"><img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/emptytable/img/print.gif"/> Stampa</li> -->
	 <li onclick="hacktvform_emptytable_htmlTableImport(<?php echo $_REQUEST['layerid'].',\''.$_REQUEST['hacktvformid'].'\''; ?>)"><img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/emptytable/img/html.png"/> Importa da tabella HTML</li>
	 <li onclick="hacktvform_emptytable_excelImport(<?php echo $_REQUEST['layerid'].',\''.$_REQUEST['hacktvformid'].'\''; ?>)"><img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/emptytable/img/excel.png"/> Importa da Excel</li>
	 <li onclick="hacktvform_emptytable_excelExport(<?php echo $_REQUEST['layerid'].',\''.$_REQUEST['hacktvformid'].'\''; ?>)"><img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/emptytable/img/excel.png"/> Esporta in Excel</li>
	 <li onclick="hacktvform_emptytable_sendEmail(<?php echo $_REQUEST['layerid'].',\''.$_REQUEST['hacktvformid'].'\''; ?>)"><img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/emptytable/img/sendmail.png"/> Invia per email</li>
	 <li onclick="hacktvform_emptytable_runCommands(<?php echo $_REQUEST['layerid'].',\''.$_REQUEST['hacktvformid'].'\''; ?>)"><img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/emptytable/img/gsh.png"/> Lancia comandi</li>
	 <!-- <li onclick="hacktvform_emptytable_putonDesktop(<?php echo $_REQUEST['layerid'].',\''.$_REQUEST['hacktvformid'].'\''; ?>)"><img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/emptytable/img/desktop.png"/> Fissa sul desktop</li> -->
	 
	</ul>
 </li>

 <li class='lightgray'><span>Modifica</span>
	<ul class='submenu'>
	 <li id="xtb-<?php echo $_REQUEST['layerid']; ?>-cutmenubutton" onclick="hacktvform_emptytable_cut(<?php echo $_REQUEST['layerid']; ?>)"><img src="<?php echo $imgFolder; ?>cut.gif"/><?php echo i18n("cut"); ?></li>
	 <li id="xtb-<?php echo $_REQUEST['layerid']; ?>-copymenubutton" onclick="hacktvform_emptytable_copy(<?php echo $_REQUEST['layerid']; ?>)"><img src="<?php echo $imgFolder; ?>copy.png"/><?php echo i18n("copy"); ?></li>
	 <li id="xtb-<?php echo $_REQUEST['layerid']; ?>-pastemenubutton" onclick="hacktvform_emptytable_paste(<?php echo $_REQUEST['layerid']; ?>)"><img src="<?php echo $imgFolder; ?>paste.gif"/><?php echo i18n("paste"); ?></li>
	 <li class='separator'>&nbsp;</li>
	 <li id="xtb-<?php echo $_REQUEST['layerid']; ?>-deletemenubutton" onclick="hacktvform_emptytable_deleteSelected(<?php echo $_REQUEST['layerid']; ?>)"><img src="<?php echo $imgFolder; ?>delete.gif"/><?php echo i18n("Delete selected"); ?></li>

	</ul>
 </li>

 <li class='lightgray'><span>Visualizza</span>
	<ul class='submenu'>
	 <?php
	 for($c=0; $c < $_REQUEST['fieldcount']; $c++)
	 {
	  echo "<li><input type='checkbox' onclick='hacktvform_emptytable_showColumn(".$_REQUEST['layerid'].","
		.($c+1).",this)'".(!$_REQUEST["f".$c."hidden"] ? " checked='true'/>" : "/>").$_REQUEST["f".$c."title"]."</li>";
	 }
	 ?>
	</ul>
 </li>

  <li class='blue' id="xtb-<?php echo $_REQUEST['layerid']; ?>-selectionmenu" style='visibility:hidden;'><span><img src="<?php echo $imgFolder; ?>checkbox.png" border='0'/>Selezionati</span>
	<ul class="submenu">
	 <li>Inverti selezione</li>
	 <li>Annulla selezione</li>
	 <li class='separator'></li>
	 <li onclick="deleteSelectedItems()"><img src="<?php echo $imgFolder; ?>delete.gif"/>Elimina selezionati</li>
	</ul>
  </li>

</ul>

<div class="gmutable" style="height:80%;margin-top:5px;">
<table id="xtb-<?php echo $_REQUEST['layerid']; ?>-doctable" class="gmutable" width="100%" cellspacing="0" cellpadding="0" border="0" style="display:none;">
<tr><th width='40'><input type="checkbox"/></th>
	<?php
	for($c=0; $c < $_REQUEST['fieldcount']; $c++)
	{
	 echo "<th style='padding-left:5px;padding-right:5px;".($_REQUEST["f".$c."hidden"] ? "display:none;" : "")."'";
	 if($_REQUEST["f".$c."editable"]) echo " editable='".$_REQUEST["f".$c."editable"]."'";
	 if($_REQUEST["f".$c."id"]) echo " id='".$_REQUEST["f".$c."id"]."'";
	 if($_REQUEST["f".$c."width"]) echo " width='".$_REQUEST["f".$c."width"]."'";
	 if($_REQUEST["f".$c."autolink"]) echo " autolink='".$_REQUEST["f".$c."autolink"]."'";
	 if($_REQUEST["f".$c."minwidth"]) echo " minwidth='".$_REQUEST["f".$c."minwidth"]."'";
	 if($_REQUEST["f".$c."format"]) echo " format='".$_REQUEST["f".$c."format"]."'";
	 if($_REQUEST["f".$c."decimals"]) echo " decimals='".$_REQUEST["f".$c."decimals"]."'";
	 if($_REQUEST["f".$c."includeintototals"])
	 {
	  echo " includeintototals='true'";
	  $_TOTALS_COLUMNS[] = array(
		 "title"=> $_REQUEST["f".$c."subtitle"] ? $_REQUEST["f".$c."subtitle"] : $_REQUEST["f".$c."title"],
		 "id"=> $_REQUEST["f".$c."id"],
		 "format"=> $_REQUEST["f".$c."format"],
		 "decimals"=> $_REQUEST["f".$c."decimals"]
		);
	 }
	 echo ">".$_REQUEST["f".$c."title"]."</th>";
	}
	?>
</tr>
<!-- CREATE FIRST BLANK ROW -->
<tr><td width='40'><input type='checkbox'/></td>
<?php
for($c=0; $c < $_REQUEST['fieldcount']; $c++)
{
 echo "<td><span class='graybold'></span></td>";
}
?>
</tr>
</table>
</div>

<div>
<table class="docfooter-results" id="xtb-<?php echo $_REQUEST['layerid']; ?>-docfooter" width="100%" cellspacing="0" cellpadding="0" border="0">
<tr><th class="blue">&nbsp;</th><?php
	for($c=0; $c < count($_TOTALS_COLUMNS); $c++)
	 echo "<th id='".$_TOTALS_COLUMNS[$c]['id']."' class='blue' width='100'>".$_TOTALS_COLUMNS[$c]['title']."</th>";
	?></tr>
<tr><td>&nbsp;</td><?php
	for($c=0; $c < count($_TOTALS_COLUMNS); $c++)
	 echo "<td class='blue' id='xtb-".$_REQUEST['layerid']."-".$_TOTALS_COLUMNS[$c]['id']."' format='"
		.$_TOTALS_COLUMNS[$c]['format']."' decimals='".$_TOTALS_COLUMNS[$c]['decimals']."'>"
		.($_TOTALS_COLUMNS[$c]['format'] == 'currency' ? "<em>&euro;</em>0,00" : "0")."</td>";
	?>
</tr>
</table>
</div>

<?php


