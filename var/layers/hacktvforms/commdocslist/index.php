<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 16-05-2013
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: List of commercial documents.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH;

$_BASE_PATH = "../../../../";

define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");
include_once($_BASE_PATH."include/i18n.php");

include_once($_BASE_PATH."var/objects/htmlgutility/menu.php");
include_once($_BASE_PATH."var/objects/gmutable/index.php");

$imgFolder = $_ABSOLUTE_URL."var/layers/hacktvforms/commdocslist/img/";

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/commdocslist/commdocslist.css" type="text/css" />
<script type="text/javascript" src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/commdocslist/commdocslist.js"></script>
<ul class="basicmenu" id="xtb-<?php echo $_REQUEST['layerid']; ?>-mainmenu">
 <li class='gray'><span>Menu</span>
	<ul class='submenu'>
	 <!-- <li onclick="hacktvform_commdocslist_addRow(<?php echo $_REQUEST['layerid']; ?>)">
		<img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/commdocslist/img/add_small.png"/> Aggiungi nuova riga</li>

	 <li class="separator">&nbsp;</li> -->

	 <li onclick="hacktvform_commdocslist_printPreview(<?php echo $_REQUEST['layerid'].',\''.$_REQUEST['hacktvformid'].'\''; ?>)"><img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/commdocslist/img/print.gif"/> Stampa</li>
	 <li onclick="hacktvform_commdocslist_excelExport(<?php echo $_REQUEST['layerid'].',\''.$_REQUEST['hacktvformid'].'\''; ?>)"><img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/commdocslist/img/excel.png"/> Esporta in Excel</li>
	 <li onclick="hacktvform_commdocslist_sendEmail(<?php echo $_REQUEST['layerid'].',\''.$_REQUEST['hacktvformid'].'\''; ?>)"><img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/commdocslist/img/sendmail.png"/> Invia per email</li>
	 <li onclick="hacktvform_commdocslist_runCommands(<?php echo $_REQUEST['layerid'].',\''.$_REQUEST['hacktvformid'].'\''; ?>)"><img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/commdocslist/img/gsh.png"/> Lancia comandi</li>
	 <li onclick="hacktvform_commdocslist_putonDesktop(<?php echo $_REQUEST['layerid'].',\''.$_REQUEST['hacktvformid'].'\''; ?>)"><img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/hacktvforms/commdocslist/img/desktop.png"/> Fissa sul desktop</li>
	 
	</ul>
 </li>

 <li class='lightgray'><span>Modifica</span>
	<ul class='submenu'>
	 <li id="xtb-<?php echo $_REQUEST['layerid']; ?>-cutmenubutton"><img src="<?php echo $imgFolder; ?>cut.gif"/><?php echo i18n("cut"); ?></li>
	 <li id="xtb-<?php echo $_REQUEST['layerid']; ?>-copymenubutton"><img src="<?php echo $imgFolder; ?>copy.png"/><?php echo i18n("copy"); ?></li>
	 <li id="xtb-<?php echo $_REQUEST['layerid']; ?>-pastemenubutton"><img src="<?php echo $imgFolder; ?>paste.gif"/><?php echo i18n("paste"); ?></li>
	 <li class='separator'>&nbsp;</li>
	 <li id="xtb-<?php echo $_REQUEST['layerid']; ?>-deletemenubutton"><img src="<?php echo $imgFolder; ?>delete.gif"/><?php echo i18n("Delete selected"); ?></li>

	</ul>
 </li>

 <li class='lightgray'><span>Visualizza</span>
	<ul class='submenu'>
	 <?php
	 for($c=0; $c < $_REQUEST['fieldcount']; $c++)
	 {
	  echo "<li><input type='checkbox' onclick='hacktvform_commdocslist_showColumn(".$_REQUEST['layerid'].","
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
	 if($_REQUEST["f".$c."id"]) echo " id='".$_REQUEST["f".$c."id"]."'";
	 if($_REQUEST["f".$c."width"]) echo " width='".$_REQUEST["f".$c."width"]."'";
	 if($_REQUEST["f".$c."editable"]) echo " editable='true'";
	 if($_REQUEST["f".$c."autolink"]) echo " autolink='".$_REQUEST["f".$c."autolink"]."'";
	 if($_REQUEST["f".$c."minwidth"]) echo " minwidth='".$_REQUEST["f".$c."minwidth"]."'";
	 if($_REQUEST["f".$c."format"]) echo " format='".$_REQUEST["f".$c."format"]."'";
	 if($_REQUEST["f".$c."decimals"]) echo " decimals='".$_REQUEST["f".$c."decimals"]."'";
	 echo ">".$_REQUEST["f".$c."title"]."</th>";
	}
	?>
</tr>
</table>
</div>

<div>
<table class="docfooter-results" id="xtb-<?php echo $_REQUEST['layerid']; ?>-footertable" width="100%" cellspacing="0" cellpadding="0" border="0">
<tr><th class="blue">&nbsp;</th><th class="blue" width="110">IMPONIBILE</th> <th class="blue" width="110">I.V.A.</th> <th class="green" width="110">TOTALE</th> </tr>
<tr><td>&nbsp;</td>
	<td class="blue" id="xtb-<?php echo $_REQUEST['layerid']; ?>-totamount"><em>&euro;</em>0,00</td>
	<td class="blue" id="xtb-<?php echo $_REQUEST['layerid']; ?>-totvat"><em>&euro;</em>0,00</td>
	<td class="green" id="xtb-<?php echo $_REQUEST['layerid']; ?>-subtot"><em>&euro;</em>0,00</td>
</tr>
</table>
</div>

<?php


