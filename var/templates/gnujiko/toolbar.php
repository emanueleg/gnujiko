<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 03-05-2017
 #PACKAGE: gnujiko-template
 #DESCRIPTION: Toolbar class for Gnujiko Template.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

class GnujikoTemplateToolbar
{
 var $Sections, $template, $height;
 function GnujikoTemplateToolbar($tplH, $height)
 {
  $this->template = $tplH;
  $this->Sections = array();
  $this->height = $height;
 }
 //----------------------------------------------------------------------------------------------//
 function AddSection($width=0, $align='left')
 {
  $sec = new GnujikoTemplateToolbarSection($this, $width, $align);
  $this->Sections[] = $sec;

  return $sec;
 }
 //----------------------------------------------------------------------------------------------//
 function Paint()
 {
  echo "<div class='gnujiko-template-toolbar'>";
  echo "<table width='100%' cellspacing='0' cellpadding='0' border='0'>";
  echo "<tr>";
  for($c=0; $c < count($this->Sections); $c++)
  {
   $sec = $this->Sections[$c];
   echo "<td valign='".($sec->valign ? $sec->valign : 'middle')."' height='".$this->height."'";
   if($sec->width)	echo " width='".$sec->width."'";
   if($sec->align)	echo " align='".$sec->align."'";
   echo ">";
   $sec->Paint();
   echo "</td>";
  }
  echo "</tr></table></div>";
 }
 //----------------------------------------------------------------------------------------------//
 //----------------------------------------------------------------------------------------------//
 //----------------------------------------------------------------------------------------------//
}
//-------------------------------------------------------------------------------------------------------------------//
//--- T O O L B A R - S E C T I O N S -------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
class GnujikoTemplateToolbarSection
{
 var $toolbar, $template, $width, $align, $contents;

 function GnujikoTemplateToolbarSection($toolbar, $width, $align)
 {
  $this->toolbar = $toolbar;
  $this->template = $toolbar->template;
  $this->width = $width;
  $this->align = $align;
  $this->contents = "";
 }
 //----------------------------------------------------------------------------------------------//
 function Paint()
 {
  echo $this->contents;
 }
 //----------------------------------------------------------------------------------------------//
 function setContent($content="")
 {
  $this->contents = $content;
 }
 //----------------------------------------------------------------------------------------------//
 function addObject($type, $data=null)
 {
  switch($type)
  {
   case 'mainmenu' : 			return $this->addObjectMainMenu($data); break;
   case 'horizontalmenu' : 		return $this->addObjectHorizontalMenu($data); break;
  }
 }
 //----------------------------------------------------------------------------------------------//
 function addObjectMainMenu($data)
 {
  global $_ABSOLUTE_URL, $_TPLDIR;

  $id = $data['id'] ? $data['id'] : "mainmenu";
  $connect = $data['connect'] ? $data['connect'] : $id."list";
  $title = $data['title'] ? $data['title'] : "Menu";
  $icon = $data['icon'] ? $data['icon'] : $_TPLDIR."buttons/homeicon.png";

  $content = "<button class='white-menu-button' id='".$id."' connect='".$connect."'>";
  $content.= "<img src='".$_ABSOLUTE_URL.$icon."'/> ".$title;
  $content.= "</button>";

  $content.= $this->template->generatePopupMenu($data['items'], $connect);

  $this->contents.= $content;
 }
 //----------------------------------------------------------------------------------------------//
 function addObjectHorizontalMenu($data)
 {
  global $_ABSOLUTE_URL, $_TPLDIR;

  $items = is_array($data['items']) ? $data['items'] : $data;
  $align = $data['align'] ? $data['align'] : 'center';
  
  $style = "";
  switch($align)
  {
   case 'left' : $style.= "margin-left:0px;margin-right:auto;"; break;
   case 'center' : $style.= "margin-left:auto;margin-right:auto;"; break;
   case 'right' : $style.= "margin-left:auto;margin-right:0px;"; break;
  }

  $content = "<ul class='horizontal-menu'".($style ? " style='".$style."'" : "").">";
  for($c=0; $c < count($items); $c++)
  {
   $item = $items[$c];
   $color = $item['color'] ? $item['color'] : 'blue';
   $selected = $item['selected'] ? $item['selected'] : false;

   if($selected)	$class = "selected ".$color."-selected";
   else 			$class = "item ".$color;

   $content.= "<a href='".$_ABSOLUTE_URL.$this->template->config['basepath'].$item['url']."'>";
   $content.= "<li class='".$class."'>".$item['title']."</li></a>";
  }
  $content.= "</ul>";

  $this->contents.= $content;
 }
 //----------------------------------------------------------------------------------------------//

}
//-------------------------------------------------------------------------------------------------------------------//
