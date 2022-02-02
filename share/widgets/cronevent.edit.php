<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2011 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 16-05-2011
 #PACKAGE: cron
 #DESCRIPTION: 
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_USERS_HOMES;
$_BASE_PATH = "../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$id = $_REQUEST['id'] ? $_REQUEST['id'] : $_REQUEST['rid'];
if($_REQUEST['rid'])
 $isRecurrence = true;
$ap = $_REQUEST['ap'];

if($isRecurrence)
 $ret = GShell("cron recurrence-info -ap $ap -id $id");
else
 $ret = GShell("dynarc exec-func ext:cronevents.info -params `ap=$ap&id=$id`");

$itemInfo = $ret['outarr'];

$dtFrom = strtotime($itemInfo['from'] ? $itemInfo['from'] : $itemInfo['startdate']." ".$itemInfo['timefrom']);
$dtTo = strtotime($itemInfo['to'] ? $itemInfo['to'] : $itemInfo['startdate']." ".$itemInfo['timeto']);
$title = $_REQUEST['title'] ? $_REQUEST['title'] : "Modifica evento";
$notes = $itemInfo['notes'];
$allDay = $itemInfo['allday'];
//-------------------------------------------------------------------------------------------------------------------//
?>
<html><head><title>Cron</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/cron/cron.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";</script>
<?php
include_once($_BASE_PATH."include/js/gshell.php");

?>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/cron/cron.js" type="text/javascript"></script>
</head><body>
<input type='hidden' id='id' value="<?php echo $id; ?>"/>
<input type='hidden' id='ap' value="<?php echo $ap; ?>"/>
<input type='hidden' id='from' value="<?php echo $_REQUEST['from']; ?>"/>
<input type='hidden' id='isrecurrence' value="<?php echo $isRecurrence ? 1 : 0; ?>"/>

<div class="cron"><div style="padding:10px 18px 10px 18px;">
 <!-- TOOLBAR -->
 <table width='100%' cellspacing='0' cellpadding='0' border='0'>
 <tr><td valign='bottom' width='240' height='35'><span class='title'><?php echo $title; ?></small></span><div class='subtitle' id='subtitle'><?php echo $itemInfo['name']; ?></div></td>
	 <td valign='bottom' class='toolbar'> 
		<ul class='cron-tab' id='cron-tabs'>
		 <li id='cron-details-tab' class='selected'><a href='#' onclick='_showPage("details")'>Dettagli</a></li>
		 
		</ul>
	 </td><td valign='middle' width='40' align='right'><a href='#' title='Chiudi' onclick='_abort()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/cron/img/btn_close.png" border='0'/></a></td></tr>
 </table>

 <div class='cron-container'>

 <!-- DETAILS -->
 <div id='cron-details-page'>
  <table width="100%" border="0">
   <tr><td valign="middle"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/cron/img/edit-event.png"/></td>
	   <td valign="top">
		<table width="100%" border="0">
		 <tr><td class='orange' colspan='2'>TITOLO</td></tr>
		 <tr><td class='gray' colspan='2'><input type="text" size="40" id="title" value="<?php echo $itemInfo['name']; ?>"/></td></tr>
		 <tr><td>&nbsp;</td></tr>
		 <tr><td class='orange'>DAL</td> <td class='orange' width='209'>AL</td></tr>
		 <tr><td class='gray'><input type='text' size='8' id='datefrom' value="<?php echo date('d/m/Y',$dtFrom); ?>"/> <input type='text' size='3' id='timefrom' value="<?php echo date('H:i',$dtFrom); ?>"/></td>
			 <td class='gray'><input type='text' size='3' id='timeto' value="<?php echo date('H:i',$dtTo); ?>"/> <input type='text' size='8' id='dateto' value="<?php echo date('d/m/Y',$dtTo); ?>"/></td></tr>
		 <tr><td>&nbsp;</td></tr>
		 <tr><td class='orange'>OPZIONI</td> <td class='orange'>RIPETIZIONI</td></tr>
		 <tr><td class='gray'><input type='checkbox' id='allday' <?php if($allDay) echo "checked='true'"; ?>>Tutto il giorno</input></td>
			 <td class='gray'><select id='imode' onchange='_repChange(this)'><?php
				$modes = array("non si ripete","ogni giorno","ogni settimana","ogni mese","ogni anno");
				for($c=0; $c < count($modes); $c++)
				 echo "<option value='$c'".($c == $itemInfo['imode'] ? " selected='selected'>" : ">").$modes[$c]."</option>";
				?></select>
<div class='cronrepeatbox'>
<p id='p0' style='display:none;'>Ripeti ogni: <input type='text' id='frequency' size='3' value="<?php echo $itemInfo['freq'] ? $itemInfo['freq'] : '1'; ?>"/> <span id='repspan'>giorni</span></p>
<p id='p1' style='display:none;'>
	<table cellspacing='0' cellpadding='0' border='0'>
	<tr><td valign='middle'>I giorni: </td><?php
	$arr = array('D','L','M','M','G','V','S');
	while(list($k,$v) = each($arr))
	 echo "<td>".$v."</td>";
	echo "</tr><tr><td>&nbsp;</td>";
	for($c=0; $c < 7; $c++)
	 echo "<td><input type='checkbox' id='day_".$c."'".((pow(2,$c) & $itemInfo['dayflag']) ? " checked='true'/>" : "/>")."</td>";
	echo "</tr></table>";
	?></p>
<p id='p2' style='display:none;'>Ripeti il giorno 
	<input type='radio' id='dayOfMonth' name='repday' <?php if(!$itemInfo['daypos']) echo "checked='true'"; ?>>del mese</input><br/> 
	<input type='radio' style='margin-left:89px;' name='repday' <?php if($itemInfo['daypos']) echo "checked='true'"; ?>>della settimana</input></p>
<p id='p3' style='display:none;'>Intervallo: inizia il <input type='text' id='startdate' size='8' value="<?php if($itemInfo['startdate']) echo date('d/m/Y',strtotime($itemInfo['startdate'])); ?>"/></p>
<p id='p4' style='display:none;'>Termina: 
	<input type='radio' name='endat' id='infinite' <?php if(!$itemInfo['enddate'] || ($itemInfo['enddate'] == "0000-00-00")) echo "checked='true'"; ?>>Mai</input><br/>
	<input type='radio' style='margin-left:58px;' name='endat' <?php if($itemInfo['enddate'] && ($itemInfo['enddate'] != "0000-00-00")) echo "checked='true'"; ?>>il </input> <input type='text' id='enddate' size='8' value="<?php if($itemInfo['enddate'] && ($itemInfo['enddate'] != '0000-00-00')) echo date('d/m/Y',strtotime($itemInfo['enddate'])); ?>"/></p>
</div>

			</td></tr>
		</table>
	   </td></tr>
  </table>
  <hr/>
 Note:<br/>
 <textarea id="notes" style="width:100%;height:120px;"><?php echo $notes; ?></textarea>
<div class='cron-footer' align='left'>
 <input type='button' value='Salva' onclick="_save()"/> <input type='button' value='Chiudi' onclick="_abort()"/> <input type='button' style='margin-left:50px;color:#f31903;' onclick='_deleteEvent()' value='Elimina'/>
</div>

  </div>
 </div>
</div>

</body></html>
<?php

