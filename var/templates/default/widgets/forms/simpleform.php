<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 04-08-2012
 #PACKAGE: gform
 #DESCRIPTION: Simple Form with buttons
 #VERSION: 2.0beta
 #CHANGELOG: 04-08-2012 : Bug fix.
			 18-07-2012 : Modifiche varie
			 20-01-2012 : Aggiunto tasto Salva.
			 02-01-2012 : Multi language.
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

include_once($_BASE_PATH."include/i18n.php");

LoadLanguage("gform");

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/templates/default/widgets/forms/css/common.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/templates/default/widgets/forms/js/common.js" type="text/javascript"></script>
<?php

function template_default_form_simpleform_begin($title="", $modal="", $width=640, $height=480, $icon="", $action="", $enctype="", $style="")
{
 global $_ABSOLUTE_URL, $_FORM_ACTION;
 if(!$width) $width=640;
 if(!$height) $height=480;
 $width-= 26;
 $height-= 26;

 if($modal)
 {
  $modals = explode("|",$modal);
 }
 if($style)
  echo "<link rel='stylesheet' href='".$_ABSOLUTE_URL."var/templates/default/widgets/forms/css/".$style.".css' type='text/css' />";
 else
  echo "<link rel='stylesheet' href='".$_ABSOLUTE_URL."var/templates/default/widgets/forms/css/orange.css' type='text/css' />";

 if($action)
  echo "<form method='POST' action='".$action."'".($enctype ? " enctype='".$enctype."'" : "")." onsubmit='return WidgetOnSubmit()' style='margin:0px;padding:0px;'>";

 $_FORM_ACTION = $action;
 ?>

 <table class='default-simple-form' width="<?php echo $width; ?>" height="<?php echo $height; ?>" cellspacing='0' cellpadding='0' border='0'>
 <tr><th class='title'><?php if($icon) echo "<img src='".$icon."' height='32'/>"; echo $title; ?></th>
	 <th class='head-btns'><?php
		if($modals && in_array("NO_CLOSE",$modals))
		 echo "&nbsp;";
		else
		 echo "<a href='#' alt='".i18n('Close')."' title='".i18n('Close')."' onclick='CloseWidget()'><img src='".$_ABSOLUTE_URL."var/templates/default/widgets/forms/img/btn_close.png'/></a>"; ?></th></tr>
 <tr><td colspan='2' valign='top' class='contents'>
	<table class='default-simple-form-tablecontents' width='100%' border='0' cellspacing='0' cellpadding='0'>
	<tr><td class='borderleft'>&nbsp;</td>
		<td valign='top'><div class='contents' style="width:<?php echo $width-12; ?>px;height:<?php echo $height-82; ?>px;overflow:auto;">
 <?php
}

function template_default_form_simpleform_end($modal="")
{
 global $_ABSOLUTE_URL, $_FORM_ACTION;
 ?>
 </div></td><td class='borderright'>&nbsp;</td></tr></table></td></tr>
 <tr><td class='footer-left'>&nbsp;</td>
	 <td class='footer-right'><?php
 $x = explode("|",$modal);
 for($c=0; $c < count($x); $c++)
 {
  switch($x[$c])
  {
   case 'MB_OK' : echo "<input type='".($_FORM_ACTION ? "submit" : "button")."' id='btn_ok' value='".i18n('OK')."'".(!$_FORM_ACTION ? " onclick='WidgetOnSubmit()'" : "")."/>"; break;
   case 'MB_SAVE' : echo "<input type='".($_FORM_ACTION ? "submit" : "button")."' id='btn_ok' value='".i18n('Save')."'".(!$_FORM_ACTION ? " onclick='WidgetOnSubmit()'" : "")."/>"; break;
   case 'MB_ABORT' : echo "<input type='button' id='btn_abort' value='".i18n('Abort')."' onclick='CloseWidget()'/>"; break;
   case 'MB_CLOSE' : echo "<input type='button' id='btn_close' value='".i18n('Close')."' onclick='CloseWidget()'/>"; break;
   default: echo "&nbsp;"; break;
  }
 }
 ?></td></tr></table>
 <?php
 if($_FORM_ACTION)
  echo "</form>";
}

