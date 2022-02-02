<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 19-06-2013
 #PACKAGE: todo-module
 #DESCRIPTION: Edit TODO form.
 #VERSION: 2.4beta
 #CHANGELOG: 19-06-2013 : Rimpostato FCKEditor in modalità predefinita.
			 17-06-2013 : Bug fix nei salvataggi
			 16-06-2013 : Bug fix vari.
 #DEPENDS: gcal,fckeditor
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");
$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : "todo";
$ret = GShell("dynarc item-info -ap '".$_AP."' -id '".$_REQUEST['id']."' -get `status,priority,date_from,date_to,all_day`",$_REQUEST['sessid'],$_REQUEST['shellid']);
if(!$ret['error'])
 $todoInfo=$ret['outarr'];


$todost = 0;
$todoet = 0;
if($todoInfo['date_from'])
 $todost = strtotime($todoInfo['date_from']);
if($todoInfo['date_to'])
 $todoet = strtotime($todoInfo['date_to']);

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Edit ToDo</title>
<?php
include_once($_BASE_PATH."var/objects/fckeditor/index.php");
include_once($_BASE_PATH."var/objects/gcal/index.php");
include_once($_BASE_PATH."include/js/gshell.php");
?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/todo/css/common.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/todo/css/edit.css" type="text/css" />

</head><body>
<div class="default-widget" style="width:640px;height:480px">
 <h3 class="header" onclick="renameTodo()" title="Clicca per rinominare" id="todotitle"><?php echo $todoInfo['name']; ?></h3> <img onclick="gframe_close();" src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/todo/img/widgetclose.png" class="default-widget-close"/>

 <div class="default-widget-page">
  <table width="100%" cellspacing="0" cellpadding="0" border="0" class="todoedit-topbar">
  <tr><td>Priorit&agrave;: <select id='priority' onchange="someChanges()"><?php
	 $pr = array("appena possibile","bassa","media","urgente","priorità assoluta");
	 for($c=0; $c < count($pr); $c++)
	  echo "<option value='".$c."'".($todoInfo['priority'] == $c ? " selected='selected'>" : ">").$pr[$c]."</option>";
	?></select></td>
	  <td>Data: <input type='text' class='text' style='width:84px' id='date_from' value="<?php if($todost) echo date('d/m/Y',$todost); ?>" onchange="someChanges()"/></td>
	  <td>dalle: <input type='text' class='text' style='width:50px' id='time_from' value="<?php if($todost) echo date('H:i',$todost); ?>" onchange="someChanges()"/></td>
	  <td>alle: <input type='text' class='text' style='width:50px' id='time_to' value="<?php if($todoet) echo date('H:i',$todoet); ?>" onchange="someChanges()"/></td>
	  <td><input type='checkbox' id='all_day' <?php if($todoInfo['all_day']) echo "checked='true'"; ?> onchange="someChanges()"/><small>Tutto il giorno</small></td></tr>
  </table>
  <?php
  if(!$_REQUEST['showeditor'])
  {
   ?>
   <div id="preview-contents" style="height:360px;overflow:auto"><?php echo $todoInfo['desc']; ?></div>
   <?php
  }
  ?>
  <textarea style="width:100%;height:350px;<?php if(!$_REQUEST['showeditor']) echo 'display:none'; ?>" id="description"><?php echo $todoInfo['desc']; ?></textarea>
 </div>

 <div class="default-widget-footer" style="clear:both;margin-top:10px">
  <?php
  if(!$_REQUEST['showeditor'])
  {
   ?>
   <span class="left-button blue" id="edit-button" onclick="edit()" style="margin-right:20px">Edita</span>
   <?php
  }
  ?>
  <span class="left-button blue" id="save-button" style="<?php if(!$_REQUEST['showeditor']) echo 'display:none'; ?>" onclick="submit()">Salva</span> 
  <span class="left-button gray" onclick="gframe_close()">Chiudi</span> 
 </div>

</div>

<script>
var AP = "<?php echo $_AP; ?>";
var ID = "<?php echo $_REQUEST['id']; ?>";
var oFCKeditor = null;
var editorIsLoaded=false;
var editorMode=0;
var miniCalendar = null;

function bodyOnLoad()
{
 miniCalendar = new GCal();
 miniCalendar.OnChange = function(date){
	 document.getElementById('date_from').value = date.printf("d/m/Y");
	}
 document.getElementById('date_from').onclick = function(){
	 var date = new Date();
	 if(this.value)
	  date.setFromISO(strdatetime_to_iso(this.value));
	 miniCalendar.Show(this,date);
	}
 
 <?php
 if($_REQUEST['showeditor'])
  echo "edit();";
 ?>
}

function someChanges()
{
 document.getElementById("save-button").style.display = "";
}

function gframe_cachecontentsload(contents)
{
 document.getElementById('description').innerHTML = contents;
 var sSkinPath = "<?php echo $_BASE_PATH; ?>../var/objects/fckeditor/editor/skins/office2003/";
 oFCKeditor = new FCKeditor('description') ;
 /*oFCKeditor.ToolbarSet = "Small";*/
 oFCKeditor.BasePath	= "<?php echo $_BASE_PATH; ?>var/objects/fckeditor/";
 oFCKeditor.Config['SkinPath'] = sSkinPath ;
 oFCKeditor.Config['PreloadImages'] =
				sSkinPath + 'images/toolbar.start.gif' + ';' +
				sSkinPath + 'images/toolbar.end.gif' + ';' +
				sSkinPath + 'images/toolbar.bg.gif' + ';' +
				sSkinPath + 'images/toolbar.buttonarrow.gif' ;
 oFCKeditor.Height = 360;

 if(document.getElementById("preview-contents"))
  document.getElementById("preview-contents").style.display = "none";

 oFCKeditor.ReplaceTextarea();

 editorIsLoaded = true;
}

function renameTodo()
{
 var title = prompt("Rinomina",document.getElementById("todotitle").innerHTML);
 if(!title)
  return;
 document.getElementById("todotitle").innerHTML = title;
}

function edit()
{
 if(document.getElementById("edit-button"))
  document.getElementById("edit-button").style.display = "none";
 document.getElementById("save-button").style.display = "";

 gframe_cachecontentsload(document.getElementById('description').value); 
}

function submit()
{
 if(editorIsLoaded)
 {
  var oEditor = FCKeditorAPI.GetInstance('description');
  var contents = oEditor.GetXHTML();
 }
  
 var title = document.getElementById("todotitle").innerHTML;
 var priority = document.getElementById('priority').value;
 var from = document.getElementById('date_from').value;
 var timeFrom = document.getElementById('time_from').value;
 var timeTo = document.getElementById('time_to').value;
 var allDay =  document.getElementById('all_day').checked ? "1" : "0";

 var dateFrom = "";
 var dateTo = "";
 if(from)
 {
  if((allDay == "1") && (timeTo == "00:00"))
   timeTo = "23:59";
  dateFrom = strdatetime_to_iso(from).substr(0,10)+" "+timeFrom;
  dateTo = strdatetime_to_iso(from).substr(0,10)+" "+timeTo;
 }

 var sh = new GShell();
 sh.OnOutput = function(o,a){gframe_close(o,a);}
 sh.sendCommand("dynarc edit-item -ap '"+AP+"' -id '"+ID+"' -name `"+title+"`"+(editorIsLoaded ? " -desc `"+contents+"`" : "")+" -set `priority="+priority+",date_from='"+dateFrom+"',date_to='"+dateTo+"',all_day="+allDay+"`");
 
}
</script>
</body></html>
<?php

