<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-02-2014
 #PACKAGE: aboutconfig
 #DESCRIPTION: 
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_APPLICATION_CONFIG;

$_BASE_PATH = "../";

include($_BASE_PATH."var/templates/glight/index.php");

$template = new GLightTemplate();

$_APPLICATION_CONFIG = array('mainmenu'=>array());
$dir = "aboutconfig";
$d = dir($_BASE_PATH.$dir);
while(FALSE !== ($entry = $d->read()))
{
 if($entry == '.' || $entry == '..')
  continue;
 if(substr($entry, -1) == "~")
  continue;
 $fullentry = rtrim($dir,'/').'/'.ltrim($entry,'/');
 if(is_dir($_BASE_PATH.$fullentry)) // is a directory //
 {
  if(file_exists($_BASE_PATH.$fullentry."/toc.php"))
   include($_BASE_PATH.$fullentry."/toc.php");
 }
}
for($c=0; $c < count($_APPLICATION_CONFIG['mainmenu']); $c++)
 $template->config['mainmenu'][] = $_APPLICATION_CONFIG['mainmenu'][$c];

$template->includeObject("editsearch");

$template->Begin("Benvenuti nel pannello di configurazione");

/*$centerContents = "<input type='text' class='search' style='width:400px;float:left' placeholder='Cerca nella configurazione...' id='search' value=\"".htmlspecialchars($_REQUEST['search'],ENT_QUOTES)."\"/><input type='button' class='button-search' id='searchbtn'/>";*/
$centerContents = "<span class='glight-template-hdrtitle'>PANNELLO DI CONFIGURAZIONE</span>";

$template->Header("search", $centerContents, "BTN_EXIT", 800);

$template->Pathway();

$template->Body("default",800);

/*-------------------------------------------------------------------------------------------------------------------*/
?>
<h1>Pannello di configurazione di Gnujiko</h1>
<?php
/*-------------------------------------------------------------------------------------------------------------------*/
$template->Footer();

?>
<script>
Template.OnExit = function(){
	document.location.href = ABSOLUTE_URL+"accounts/Logout.php";
	return false;
}

Template.OnInit = function(){
	/*this.initEd(document.getElementById("search"), "search").OnSearch = function(){
		};
	this.initBtn(document.getElementById("searchbtn")).onclick = function(){document.getElementById("search").OnSearch();}*/

}


</script>
<?php

$template->End();

?>
