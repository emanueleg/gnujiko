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
$template->includeInternalObject("serp");

$template->Begin("BackOffice - Dashboard");

$centerContents = "<input type='text' class='contact' style='width:390px;float:left' placeholder='Cerca ...' id='search' value=\"".$_REQUEST['search']."\"/><input type='button' class='button-search' id='searchbtn' connect='search'/>";
/*$centerContents.= "<input type='text' class='calendar' value='".date('d/m/Y')."' id='datefrom' style='margin-left:30px'/>";
$centerContents.= "<span class='smalltext'> al </span> <input type='text' class='calendar' value='".date('d/m/Y')."' id='dateto'/>";*/

$template->Header("search", $centerContents, "BTN_EXIT");
$_SERP = new SERP();
//-------------------------------------------------------------------------------------------------------------------//
$template->SubHeaderBegin();
$imgPath = $_ABSOLUTE_URL.$template->config['basepath']."img/";
/*?>
 &nbsp;</td>
 <td width='480'><ul class='toggles'><?php
	  $show = array(
		 "list"=>array("title"=>"Mostra lista", "icon"=>"list-view.png"),
		 "calendar"=>array("title"=>"Mostra calendario", "icon"=>"calendar-view.png"),
		 "requests"=>array("title"=>"Esamina richieste per categoria", "icon"=>"request-view.png")
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
<?php*/
$template->SubHeaderEnd();
//-------------------------------------------------------------------------------------------------------------------//
$template->Body("default",700);

echo "Applicazione ancora in fase di sviluppo...";

$template->Footer();

?>
<script>
Template.OnExit = function(){
 return true;
}

Template.OnInit = function(){
 this.SERP = new SERP();
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
