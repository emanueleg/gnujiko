<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-01-2014
 #PACKAGE: glight-template
 #DESCRIPTION: GLight - Request List
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/templates/glight/objects/requestlist/requestlist.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/templates/glight/objects/requestlist/requestlist.js" type="text/javascript"/></script>
<?php

class GLRequestList
{
 var $Items, $ID;

 function GLRequestList()
 {
  $this->Items = array();
  $this->ID = "requestlist";
 }

 function AddItem($title="Senza titolo", $status=0, $date="", $focused=false)
 {
  $this->Items[] = array("title"=>$title, "status"=>$status, "ctime"=>$date, "focused"=>$focused);
 }

 function Paint()
 {
  global $_ABSOLUTE_URL;
  $imgPath = $_ABSOLUTE_URL."var/templates/glight/objects/requestlist/img/";

  echo "<table style='width:100%' cellspacing='0' cellpadding='0' border='0' class='collapsetable' id='".$this->ID."'>";
  
  for($c=0; $c < count($this->Items); $c++)
  {
   $item = $this->Items[$c];
   echo "<tr class='collapsed'><td class='icon'><img src='".$imgPath."task.png'/></td>";
   echo "<td class='title'><b>".$item['title']."</b></td>";
   echo "<td class='status'><span class='status-green'>completato</span></td>";
   echo "<td class='time'>10.01.2013</td>";
   echo "<td class='icon'><img src='".$imgPath."focus-off.png'/></td></tr>";

   echo "<tr class='container'>";
   echo "<td colspan='5'>";
    echo "<span class='minioption' onclick='ACTIVE_GLREQLIST.showRequestDetails(this)'>dettagli <img src='".$imgPath."options.png'/></span>";
    echo "<div class='contents'><textarea class='textarea' style='width:100%;height:200px'></textarea></div>";
	echo "<div class='footer'>";
	echo "<input type='button' class='button-blue' style='float:left' value='Salva'/>";
	echo "<ul class='iconsmenu' style='float:left;margin-top:2px'>";
	echo "<li><img src='".$imgPath."type.png'/></li>";
	echo "<li class='separator'></li>";
	echo "<li><img src='".$imgPath."upload.png'/></li>";
	echo "</ul>";
	echo "<ul class='iconsmenu' style='float:right;margin-top:2px'>";
	echo "<li><img src='".$imgPath."trash.png' title='Elimina'/></li>";
	echo "<li class='separator'></li>";
	echo "<li><img src='".$imgPath."dnarrow.png' title='Opzioni' onclick='ACTIVE_GLREQLIST.showMenuOpt(this)'/></li>";
	echo "</ul>";
	echo "</div>";
   echo "</td></tr>";
  }

  echo "<tr class='lastrow'><td class='icon'><img src='".$imgPath."add.png'/></td>";
  echo "<td colspan='4'><span class='link' onclick='ACTIVE_GLREQLIST.addNewRequest()'>Clicca per aggiungere una richiesta</span></td></tr>";

  echo "</table>";

  /* REQ. MENU */
  echo "<ul class='popupmenu' id='".$this->ID."-footmenu'>";
  echo "<li onclick='ACTIVE_GLREQLIST.DeleteItem()'><img src='".$imgPath."rename.png'/> Rinomina</li>";
  echo "</ul>";

  /* REQ. DETAILS */
  echo "<div class='popupmessage' id='".$this->ID."-details' style='width:500px;height:200px'>";
  echo "<table class='popupmsgform'>";
  echo "<tr><td class='field'>data creazione:</td>";
  echo "<td><input type='text' class='calendar' value='11.01.2014' style='width:120px' id='".$this->ID."-ctime'/></td></tr>";
  echo "<tr><td class='field'>ultima modifica:</td>";
  echo "<td><input type='text' class='calendar' value='10.01.2014' style='width:120px' id='".$this->ID."-mtime'/></td></tr>";
  echo "<tr><td class='field'>categoria:</td>";
  echo "<td><input type='text' class='dropdown' style='width:290px' placeholder='Seleziona una categoria' id='".$this->ID."-cat' ap='documents'/>";
  echo "<input type='button' class='button-folder' ap='documents' connect='".$this->ID."-cat' id='".$this->ID."-btnselcat'/></td></tr>";
  echo "</table>";
  echo "</div>";

 }


}

?>

