<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-12-2011
 #PACKAGE: gform
 #DESCRIPTION: Simple form class customizable
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

?>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/objects/gform/gform.js" type="text/javascript"></script>
<?php

class GForm
{
 var $Title;
 var $Template;
 var $TemplateStyle;
 var $Modal;
 var $FormType;
 var $Width;
 var $Height;

 function GForm($title="", $modal="", $type="simpleform", $template="default", $style="", $width=null, $height=null)
 {
  global $_BASE_PATH, $_ABSOLUTE_URL;
  $this->Title = $title;
  $this->Modal = $modal;
  $this->FormType = $type;
  $this->Template = $template;
  $this->TemplateStyle = $style;
  $this->Width = $width;
  $this->Height = $height;

  if(file_exists($_BASE_PATH."var/templates/".$template."/widgets/forms/".$type.".php"))
   include_once($_BASE_PATH."var/templates/".$template."/widgets/forms/".$type.".php");
  else if(file_exists($_BASE_PATH."var/templates/default/widgets/forms/".$type.".php"))
  {
   $this->Template = "default";
   $this->FormType = $type;
   include_once($_BASE_PATH."var/templates/default/widgets/forms/".$type.".php");
  }
  else if(file_exists($_BASE_PATH."var/templates/".$template."/widgets/forms/simpleform.php"))
  {
   $this->Template = $template;
   $this->FormType = "simpleform";
   include_once($_BASE_PATH."var/templates/".$template."/widgets/forms/simpleform.php");
  }
  else if(file_exists($_BASE_PATH."var/templates/default/widgets/forms/simpleform.php"))
  {
   $this->Template = "default";
   $this->FormType = "simpleform";
   include_once($_BASE_PATH."var/templates/default/widgets/forms/simpleform.php");
  }
 }

 function Begin($icon="",$action="",$enctype="")
 {
  if(is_callable("template_".$this->Template."_form_".$this->FormType."_begin",true))
   call_user_func("template_".$this->Template."_form_".$this->FormType."_begin",$this->Title, $this->Modal, $this->Width, $this->Height, $icon, $action, $enctype, $this->TemplateStyle);
 }

 function End()
 {
  if(is_callable("template_".$this->Template."_form_".$this->FormType."_end",true))
   call_user_func("template_".$this->Template."_form_".$this->FormType."_end", $this->Modal);
 }
}
