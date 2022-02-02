<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-08-2014
 #PACKAGE: scheduledtasks
 #DESCRIPTION: Impostazioni operazione pianificata
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include($_BASE_PATH."var/templates/glight/index.php");

$template = new GLightTemplate("widget");
$template->includeObject("gcal");
$template->includeCSS("share/widgets/scheduledtask/edit.css");
$template->Begin("Operazione pianificata");

$imodes = array("","giorni","settimane","mesi","anni");

$itemInfo = array();
if($_REQUEST['id'])
{
 $ret = GShell("scheduledtasks info -id '".$_REQUEST['id']."'",$_REQUEST['sessid'], $_REQUEST['shellid']);
 if(!$ret['error'])
  $itemInfo = $ret['outarr'];
}

//-------------------------------------------------------------------------------------------------------------------//
?>
<div class="glight-widget-header bg-blue">
 <table width='100%' cellspacing='0' cellpadding='0' border='0'>
 <tr><td><h3>Operazione pianificata</h3></td>
	 <td align='right' valign='top' style='padding-top:10px;padding-right:10px'>
	  <span class='tinytext' style='color:#ffffff'>Status:</span> <span class='tinytext' style='color:#ffffff'><b><?php
		switch($itemInfo['status'])
		{
		 case 1 : echo "attivo"; break;
		 default : echo "disattivato"; break;
		}
		?></b></span><br/>
	  <span class='tinytext' style='color:#ffffff'>Pross. ricorrenza:</span> <span class='tinytext' style='color:#ffffff'><b><?php if($itemInfo['next_occurrence']) echo date('d/m/Y',strtotime($itemInfo['next_occurrence'])); ?></b></span>
	 </td></tr>
 </table>
</div>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$template->Body("widget",520);
//-------------------------------------------------------------------------------------------------------------------//
?>
<div class="glight-widget-body" style="width:520px;height:380px">
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="standardform">
<tr><td valign='top' rowspan='3' width='130'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/scheduledtask/img/scheduled-tasks-icon.png"/></td>
	<td><span class='smalltext'>Titolo: </span>
		<input type='text' class='edit' style='width:320px' id="title" value="<?php echo $itemInfo['name']; ?>"/></td></tr>
<tr><td><span class='smalltext'>Esegui ogni: </span>
		<input type='text' id='freq' class='edit' style='width:40px' value="<?php echo $itemInfo['freq']; ?>"/>
		<input type='text' class='dropdown' readonly='true' id='imode' connect='imode-list' value="<?php echo $imodes[$itemInfo['imode']]; ?>" retval="<?php echo $itemInfo['imode']; ?>"/>
		<ul class='popupmenu' id='imode-list'>
		 <li value='1'>giorni</li>
		 <li value='2'>settimane</li>
		 <li value='3'>mesi</li>
		 <li value='4'>anni</li>
		</ul>
	</td></tr>
<tr><td>
	<!-- WEEK SECTION -->
	<div id='weeksection' <?php if($itemInfo['imode'] != 2) echo "style='display:none'"; ?>>
	<span class='smalltext' style='float:left'>I giorni: </span>
	<table cellspacing='0' cellpadding='0' border='0' class='smalltable' style='float:left'>
	 <?php
	 $arr = array('D','L','M','M','G','V','S');
	 echo "<tr>";
	 while(list($k,$v) = each($arr))
	  echo "<th>".$v."</th>";
	 echo "</tr><tr>";
	 for($c=0; $c < 7; $c++)
	  echo "<td><input type='checkbox' class='checkbox' id='day_".$c."'".((pow(2,$c) & $itemInfo['dayflag']) ? " checked='true'/>" : "/>")."</td>";
	 echo "</tr>";
	 ?>
	</table>
	</div>
	<!-- MONTH SECTION -->
	<div id='monthsection' style="clear:both;<?php if($itemInfo['imode'] != 3) echo 'display:none'; ?>">
	<span class='smalltext'>Ripeti il giorno 
	<input type='radio' id='dayofmonth' name='repday' <?php if(!$itemInfo['daypos']) echo "checked='true'"; ?>/>del mese 
	<input type='radio' name='repday' <?php if($itemInfo['daypos']) echo "checked='true'"; ?>/>della settimana
	</div>
	</td></tr>
</table>
<hr class='hrforwidget'/>
<span class='smalltext'>Intervallo: inizia il:</span> <input type='text' class='calendar' id='startdate' value="<?php if($itemInfo['startdate']) echo date('d/m/Y',strtotime($itemInfo['startdate'])); ?>"/> &nbsp;&nbsp;
<span class='smalltext'>termina: </span> <input type='radio' name='endat' id='infinite' <?php if(!$itemInfo['enddate'] || ($itemInfo['enddate'] == "0000-00-00")) echo "checked='true'"; ?> onclick="resetEndDate()"/>mai 
<input type='radio' name='endat' <?php if($itemInfo['enddate'] && ($itemInfo['enddate'] != "0000-00-00")) echo "checked='true'"; ?>/>il: <input type='text' class='calendar' id='enddate' value="<?php if($itemInfo['enddate'] && ($itemInfo['enddate'] != '0000-00-00')) echo date('d/m/Y',strtotime($itemInfo['enddate'])); ?>"/>
<hr class='hrforwidget'/>
<span class='smalltext'>Comando post-esecuzione:</span><br/>
<textarea id='postcommand' class='textarea' style='width:510px;height:170px;'><?php echo $itemInfo['postcommand']; ?></textarea>
</div>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$footer = "<input type='button' class='button-blue' value='Salva' style='float:left' onclick='SubmitAction()'/>";
$footer.= "<input type='button' class='button-gray' value='Chiudi' style='float:left;margin-left:10px' onclick='Template.Exit()'/>";
$footer.= "<input type='button' class='button-red' value='Elimina' style='float:right' onclick='DeleteSchedule()'/>";
$template->Footer($footer,true);
//-------------------------------------------------------------------------------------------------------------------//
?>
<script>
Template.OnInit = function(){
 this.initEd(document.getElementById('startdate'), 'date');
 this.initEd(document.getElementById('enddate'), 'date').onchange = function(){
		 if(this.value)
		  document.getElementsByName("endat")[1].checked = true;
		 else
		  document.getElementById("infinite").checked = true;
		};
 this.initEd(document.getElementById('imode'), 'dropdown').onselect = function(){
	 switch(this.getValue())
	 {
	  case '2' : {
		 document.getElementById("weeksection").style.display = "";
		 document.getElementById("monthsection").style.display = "none";
		} break;
	  case '3' : {
		 document.getElementById("weeksection").style.display = "none";
		 document.getElementById("monthsection").style.display = "";
		} break;
	  default : {
		 document.getElementById("weeksection").style.display = "none";
		 document.getElementById("monthsection").style.display = "none";
		} break;
	 }
	};
 
}

function SubmitAction()
{
 var title = document.getElementById("title").value;
 var startDate = document.getElementById("startdate").isodate;
 var endDate = (document.getElementById("infinite").checked == false) ? document.getElementById("enddate").isodate : "";
 var imode = document.getElementById("imode").getValue();
 var freq = document.getElementById("freq").value;
 var postCommand = document.getElementById("postcommand").value;
 
 var cmd = "scheduledtasks edit -id '<?php echo $itemInfo['id']; ?>' -title `"+title+"` -imode '"+imode+"' -freq '"+freq+"' -startdate '"+startDate+"' -enddate '"+endDate+"' -postcommand "+(postCommand ? "<![CDATA["+postCommand+"]]>" : "''");

 var dayFlag = 0; var dayNum = 0; var dayPos = 0;
 switch(imode)
 {
   case '2' : {
	 for(var c=0; c < 7; c++)
	  dayFlag+= (document.getElementById('day_'+c).checked) ? Math.pow(2,c) : 0;
	} break;

   case '3' : {
	 var dtFrom = new Date();
	 dtFrom.setFromISO(startDate);
	 if(document.getElementById('dayofmonth').checked)
	  dayNum = dtFrom.getDate();
	 else
	 {
	  dayPos = Math.floor(dtFrom.getDate()/7);
	  dayFlag = Math.pow(2,dtFrom.getDay());
	 }
	} break;
 }

 cmd+= " -dayflag '"+dayFlag+"' -daynum '"+dayNum+"' -daypos '"+dayPos+"'";
 
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 sh.sendCommand(cmd);
}

function resetEndDate()
{
 document.getElementById("enddate").value = "";
}

</script>
<?php
//-------------------------------------------------------------------------------------------------------------------//
$template->End();
//-------------------------------------------------------------------------------------------------------------------//

