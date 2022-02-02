<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 
 #PACKAGE: 
 #DESCRIPTION: 
 #VERSION: 
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

$_BASE_PATH = "../";

include($_BASE_PATH."var/templates/glight/index.php");

$template = new GLightTemplate();
$template->includeObject("gcal");
$template->includeObject("editsearch");
$template->includeObject("gorganizer");
$template->includeInternalObject("serp");

$template->Begin("HelpDesk - Organizer");

$centerContents = "<input type='text' class='contact' style='width:390px;float:left' placeholder='Cerca nelle attività...' id='search' value=\"".$_REQUEST['search']."\"/><input type='button' class='button-search' id='searchbtn' connect='search'/>";

$template->Header("search", $centerContents, "BTN_EXIT");
$_SERP = new SERP();
//-------------------------------------------------------------------------------------------------------------------//
$template->SubHeaderBegin();
$imgPath = $_ABSOLUTE_URL.$template->config['basepath']."img/";
?>
 &nbsp;</td>
 <td width='480'><ul class='toggles'><?php
	  $show = array(
		 "calendar"=>array("title"=>"Mostra calendario", "icon"=>"calendar-view.png"),
		 "list"=>array("title"=>"Mostra lista", "icon"=>"list-view.png"),
		 
		);
	  $idx = 0;
	  while(list($k,$v)=each($show))
	  {
	   $class = "";
	   if($idx == 0)
		$class = "first";
	   else if($idx == (count($show)-1))
		$class = "last";
	   if($k == $_REQUEST['show'])
		$class.= " selected";
	   echo "<li".($class ? " class='".$class."'" : "")." onclick=\"setShow('".$k."')\" title=\"".$v['title']."\"><img src='".$imgPath.$v['icon']."' class='largebutton'/></li>";
	   $idx++;
	  }
 	 ?></ul>
 </td><td>
<?php
$template->SubHeaderEnd();
//-------------------------------------------------------------------------------------------------------------------//
$template->Body("default",700);

?>
<div id='gorganizer' style='width:700px;height:480px;'></div>
<?php

$template->Footer();

?>
<script>
var GOrg = null;
Template.OnExit = function(){
 return true;
}

Template.OnInit = function(){
 this.SERP = new SERP();
  GOrg = new GOrganizer(document.getElementById('gorganizer'), null, 14);
 
 GOrg.OnBlockMove = function(block){
		 var sh = new GShell();
	 	 sh.OnOutput = function(o,a){
		 	 if(!a) return;
			}
		 if(block.data)
		 {
		  if(block.data['archive'] == "simpletask")
		   sh.sendCommand("dynarc edit-item -ap `"+block.data['archive']+"` -id `"+block.data['item_id']+"` -extset `taskinfo.exec-datetime='"+block.dateFrom.printf('Y-m-d H:i')+"',estimated-timelength='"+timelength_to_str(block.timeLength*60)+"'`");
		  else if(block.data['is_recurrence'])
		   sh.sendCommand("cron recurrence2event -ap `"+block.data['archive']+"` -id `"+block.data['id']+"` -from `"+block.dateFrom.printf('Y-m-d H:i')+"` -to `"+block.dateTo.printf('Y-m-d H:i')+"` -exception `"+block.oldDateFrom.printf('Y-m-d H:i')+"`");
		  else
		   sh.sendCommand("cron edit-event -ap `"+block.data['archive']+"` -id `"+block.data['id']+"` -from `"+block.dateFrom.printf('Y-m-d H:i')+"` -to `"+block.dateTo.printf('Y-m-d H:i')+"`");
		 }
	 	 else if(block.id && block.archive)
		 {
		  switch(block.archive)
		  {
		   case 'simpletask' : sh.sendCommand("dynarc edit-item -ap `"+block.archive+"` -id `"+block.id+"` -extset `cronevents.from='"+block.dateFrom.printf('Y-m-d H:i')+"',to='"+block.dateTo.printf('Y-m-d H:i')+"',taskinfo.exec-datetime='"+block.dateFrom.printf('Y-m-d H:i')+"',estimated-timelength='"+timelength_to_str(block.timeLength*60)+"'`"); break;
		   default : sh.sendCommand("dynarc edit-item -ap `"+block.archive+"` -id `"+block.id+"` -extset `cronevents.from='"+block.dateFrom.printf('Y-m-d H:i')+"',to='"+block.dateTo.printf('Y-m-d H:i')+"'`"); break;
		  }
		 }
		}

GOrg.OnBlockResize = function(block){
	 	 if(!block.data) return;
	 	 var sh = new GShell();
		 if(block.data['archive'] == "simpletask")
		  sh.sendCommand("dynarc edit-item -ap `"+block.data['archive']+"` -id `"+block.data['item_id']+"` -extset `taskinfo.exec-datetime='"+block.dateFrom.printf('Y-m-d H:i')+"',estimated-timelength='"+timelength_to_str(block.timeLength*60)+"'`");
		 else
		  sh.sendCommand("cron edit-event -ap `"+block.data['archive']+"` -id `"+block.data['id']+"` -from `"+block.dateFrom.printf('Y-m-d H:i')+"` -to `"+block.dateTo.printf('Y-m-d H:i')+"`");
		}

GOrg.OnBlockClick = function(block){
	 	 if(!block.data) return;
		 switch(block.data['archive'])
		 {
		  case 'simpletask' : {
			 var sh = new GShell();
			 sh.sendCommand("gframe -f simpletask -params `ap="+block.data['archive']+"&id="+block.data['item_id']+"`"); 
			} break;

		  default : {
			 var sh = new GShell();
			 sh.OnOutput = function(o,a){GOrg.update();}
			 sh.sendCommand("dynlaunch -ap `"+block.data['archive']+"` -id `"+block.data['item_id']+"`");
			} break;
		 }
		}


GOrg.OnUpdateRequest = function(dateFrom, dateTo){
	  	 var from = new Date(dateFrom);
	 	 var to = new Date(dateTo);

		 var sh = new GShell();
		 sh.OnError = function(e,s){}
		 sh.OnOutput = function(o,a){
			 if(!a)
			  return;
			 for(var c=0; c < a.length; c++)
			 {
			  var data = a[c];
			  var opt = {};
			  if(data['tag'] == "WORKING_AREA")
			   opt.type = "workingarea";
			  else if(data['tag'] == "NONWORKING_AREA")
			   opt.type = "nonworkingarea";
			  switch(data['archive'])
			  {
			   case 'appointments' : opt.color = "orange"; break;
			   case 'todo' : opt.color = "sky"; break;
			  }
			  var block = GOrg.addBlock(data['dtfrom'], data['dtto'], data['name'], opt);
			  block.data = data;
			  if((data['archive'] == "simpletask") || (data['archive'] == "todo") || (data['archive'] == "appointments"))
		   	  {
			   var sh2 = new GShell();
			   sh2.block = block;
			   sh2.OnError = function(e,s){}
			   sh2.OnOutput = function(o,a){
					 switch(a['status'])
					 {
					  case '2' : this.block.setColor("blue"); break;
					  case '3' : this.block.setColor("orange"); break;
					  case '4' : this.block.setColor("red"); break;
					  case '5' : this.block.setColor("green"); break;
					  default : this.block.setColor("sky"); break;
					 }
					}

			   sh2.sendCommand("dynarc item-info -ap `"+data['archive']+"` -id "+data['item_id']+" -get status");
		      }
			 }
			}
		 sh.sendCommand("cron list -from "+from.printf('Y-m-d H:i')+" -to "+to.printf('Y-m-d H:i'));
		 return true;
		}

 GOrg.update();
}

function setShow(value)
{
 Template.SERP.setVar("show",value);
 Template.SERP.reload(0);
}
</script>
<?php

$template->End();

?>
