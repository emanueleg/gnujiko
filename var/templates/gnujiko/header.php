<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 22-05-2017
 #PACKAGE: gnujiko-template
 #DESCRIPTION: Header class for Gnujiko Template.
 #VERSION: 2.1beta
 #CHANGELOG: 22-05-2017 : Aggiunto parametro agentid su funzione addObjectSearch.
 #TODO:
 
*/

class GnujikoTemplateHeader
{
 var $template, $type, $Sections, $JavaScript;
 //------------------------------------------------------------------------------------------------------------------//
 function GnujikoTemplateHeader($tplH, $type='default')
 {
  $this->template = $tplH;
  $this->type = $type;
  $this->Sections = array();
  $this->height = 60;
  $this->JavaScript = "";

  // INIT
  switch($this->type)
  {
   case 'default' : {
	 $this->AddSection(240)->addObject('logo');
	 $this->AddSection();
	 $this->AddSection(250, 'right')->addObject('button-exit');
	 $this->AddSection(40, 'center')->addObject('appmenu');
	} break;


  }

 }
 //------------------------------------------------------------------------------------------------------------------//
 function AddSection($width=0, $align='left')
 {
  $sec = new GnujikoTemplateHeaderSection($this, $width, $align);
  $this->Sections[] = $sec;

  return $sec;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function Paint()
 {
  echo "<div class='gnujiko-template-header'>";
  echo "<table width='100%' cellspacing='0' cellpadding='0' border='0'>";
  echo "<tr>";
  for($c=0; $c < count($this->Sections); $c++)
  {
   $sec = $this->Sections[$c];
   echo "<td valign='".($sec->valign ? $sec->valign : 'middle')."' height='".$this->height."'";
   if($sec->width)	echo " width='".$sec->width."'";
   if($sec->align)	echo " align='".$sec->align."'";
   echo ">";
   $content = $sec->Paint(true);
   echo $content ? $content : "&nbsp;";
   echo "</td>";
  }
  echo "</tr></table></div>";
 }
 //------------------------------------------------------------------------------------------------------------------//
 //------------------------------------------------------------------------------------------------------------------//
 //------------------------------------------------------------------------------------------------------------------//
}
//-------------------------------------------------------------------------------------------------------------------//
//--- H E A D E R - S E C T I O N -----------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
class GnujikoTemplateHeaderSection
{
 var $template, $header, $width, $align, $valign, $contents;
 function GnujikoTemplateHeaderSection($hdrH, $width=0, $align='left')
 {
  $this->header = $hdrH;
  $this->template = $hdrH->template;
  $this->width = $width;
  $this->align = $align;
  $this->valign = 'middle';
  $this->contents = ""; 
 }
 //------------------------------------------------------------------------------------------------------------------//
 function Paint($retAsString=false)
 {
  if($retAsString) return $this->contents;
  else echo $this->contents;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function setContent($content="")
 {
  $this->contents = $content;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function addObject($type, $data=null)
 {
  switch($type)
  {
   case 'logo' : return $this->addObjectLogo($data); break;
   case 'search' : return $this->addObjectSearch($data); break;
   case 'exit' : case 'button-exit' : return $this->addObjectButton('exit',$data); break;
   case 'appmenu' : return $this->addObjectAppMenu($data); break;
  }
 }
 //------------------------------------------------------------------------------------------------------------------//
 function addObjectLogo($data=null)
 {
  global $_BASE_PATH, $_ABSOLUTE_URL;

  $href = (is_array($data) && $data['href']) ? $data['href'] : $_ABSOLUTE_URL;
  $icon = (is_array($data) && $data['icon']) ? $data['icon'] : $_ABSOLUTE_URL."var/templates/gnujiko/img/small-logo.png";

  $this->contents.= "<a href='".$href."'><img src='".$icon."'/></a>";
 }
 //------------------------------------------------------------------------------------------------------------------//
 function addObjectSearch($data=null)
 {
  $className = (is_array($data) && $data['class']) ? $data['class'] : "edit-search";
  $id = (is_array($data) && $data['id']) ? $data['id'] : "search";
  $width = (is_array($data) && $data['width']) ? $data['width'] : '100%';
  $value = (is_array($data) && $data['value']) ? $data['value'] : ((isset($_REQUEST['search']) && $_REQUEST['search']) ? $_REQUEST['search'] : '');
  $placeholder = (is_array($data) && ($data['title'] || $data['placeholder'])) ? ($data['title'] ? $data['title'] : $data['placeholder']) : "";
  $disableRightButton = (is_array($data) && isset($data['disablerightbtn'])) ? $data['disablerightbutton'] : true;

  $content = "<input type='text' class='".$className."' id='".$id."' style='width:".$width."'";
  if($disableRightButton) 					$content.= " disablerightbtn='".($disableRightButton ? 'true' : 'false')."'";
  if($value) 								$content.= " value=\"".$value."\"";
  if($placeholder)							$content.= " placeholder=\"".$placeholder."\"";

  if(is_array($data))
  {
   if($data['ct'])				$content.= " ct='".$data['ct']."'";
   if($data['into'])			$content.= " into='".$data['into']."'";
   if($data['field'])			$content.= " field='".$data['field']."'";
   if($data['fields'])			$content.= " fields='".$data['fields']."'";
   if($data['limit'])			$content.= " limit='".$data['limit']."'";
   if($data['get'])				$content.= " get='".$data['get']."'";
   if($data['extget'])			$content.= " extget='".$data['extget']."'";

   switch($data['type'])
   {
	case 'contactextended' : {
		 if($data['agentid'])	$content.= " agentid='".$data['agentid']."'";
		} break;

   }
  }

  $content.= "/>";
  $this->contents.= $content;

  if(is_array($data) && $data['type'])
  {
   switch($data['type'])
   {
	case 'contactextended' : {
		 $js = "Template.initEd(document.getElementById('".$id."'), 'contactextended').OnSearch = function(){Template.OnSearch(this);}\n";
		 $this->header->JavaScript.= $js;
		 $this->template->includeInternalObject("contactsearch");
		} break;

	default : {
		 $js = "Template.initEd(document.getElementById('".$id."'), 'search').onchange = function(){Template.OnSearch(this);}\n";
		 $this->header->JavaScript.= $js;
		} break;
   }
  }

 }
 //------------------------------------------------------------------------------------------------------------------//
 function addObjectAppMenu($data=null)
 {
  global $_BASE_PATH, $_ABSOLUTE_URL, $_TPLDIR;
  
  $icon = (is_array($data) && $data['icon']) ? $data['icon'] : $_ABSOLUTE_URL.$_TPLDIR."img/appmenu.png";
  $this->contents.= "<img src='".$icon."' onclick='Template.ShowApplicationMenu(this)' style='cursor:pointer'/>";
 }
 //------------------------------------------------------------------------------------------------------------------//
 function addObjectButton($type='default', $data=null)
 {
  $className = (is_array($data) && $data['class']) ? $data['class'] : "";
  if(!$className)
  {
   switch($type)
   {
    case 'exit' : $className = "button-exit"; break;
	default : $className = "button-gray"; break;
   }
  }

  $value = (is_array($data) && $data['value']) ? $data['value'] : i18n('Exit');
  $title = (is_array($data) && $data['title']) ? $data['title'] : i18n('Back to home');
  $onClick = (is_array($data) && $data['onclick']) ? $data['onclick'] : "Template.Exit()";

  $this->contents.= "<input type='button' class='".$className."' value=\"".$value."\" onclick=\"".$onClick."\" title=\"".$title."\"/>";
 }
 //------------------------------------------------------------------------------------------------------------------//

 //------------------------------------------------------------------------------------------------------------------//
 //------------------------------------------------------------------------------------------------------------------//
 //------------------------------------------------------------------------------------------------------------------//
}




