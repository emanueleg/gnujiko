<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-08-2014
 #PACKAGE: glight-template
 #DESCRIPTION: GLight - OnOffTable
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/templates/glight/objects/onofftable/onofftable.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/templates/glight/objects/onofftable/onofftable.js" type="text/javascript"/></script>
<?php

class OnOffTableItem
{
 var $id, $title, $subtitle, $rightHdrContent, $active, $content, $rightContent;

 function OnOffTableItem($id, $title="Untitled", $subtitle="", $rightHdrContent="", $active=false)
 {
  $this->id = $id;
  $this->title = $title;
  $this->subtitle = $subtitle;
  $this->rightHdrContent = $rightHdrContent;
  $this->active = $active;
  $this->content = "";
  $this->contentExtended = false;
  $this->rightContent = "";
 }

 function setTitle($title=""){$this->title = $title;}
 function setSubTitle($subtitle=""){$this->subtitle = $subtitle;}
 function setRightHdrContent($content=""){$this->rightHdrContent = $content;}
 function setContent($content="", $extended=false){$this->content = $content; $this->contentExtended=$extended;}
 function setRightContent($content=""){$this->rightContent = $content;}
 
 function Paint()
 {
  echo "<tr id='".$this->id."' class='".($this->active ? "expanded" : "collapsed")."'><td width='110'><ul class='toggles'>";
  echo $this->active ? "<li class='first blue'>ON</li><li class='last'>OFF</li>" : "<li class='first'>ON</li><li class='last gray'>OFF</li>";
  echo "</ul></td>";
  echo "<td><div class='title'>".$this->title."</div><div class='subtitle'>".$this->subtitle."</div></td>";
  echo "<td width='180'>".$this->rightHdrContent."</td></tr>";

  echo "<tr class='container'><td>&nbsp;</td>";
  if($this->contentExtended)
   echo "<td colspan='2' valign='top'>".$this->content."</td></tr>";
  else
   echo "<td valign='top'>".$this->content."</td><td valign='top'>".$this->rightContent."</td></tr>";
 }

}
//-------------------------------------------------------------------------------------------------------------------//
class OnOffTable
{
 var $Items, $ID;

 function OnOffTable($id="onofftable")
 {
  $this->Items = array();
  $this->ID = $id;
 }

 function AddItem($id, $title="Untitled", $subtitle="", $rightHdrContent="", $active=false)
 {
  $item = new OnOffTableItem($id, $title, $subtitle, $rightHdrContent, $active);
  $this->Items[] = $item;
  return $item;
 }

 function Paint()
 {
  global $_ABSOLUTE_URL;
  
  echo "<table class='collapsetable' width='100%' cellspacing='0' cellpadding='0' border='0' id='".$this->ID."'>";
  for($c=0; $c < count($this->Items); $c++)
  {
   $item = $this->Items[$c];
   $item->Paint();
  }
  echo "</table>";
 }


}

?>

