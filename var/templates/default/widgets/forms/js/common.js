/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 01-12-2012
 #PACKAGE: gform
 #DESCRIPTION: Common javascript functions for template orange
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS:
 #TODO:
 
*/

function WidgetOnSubmit()
{
 if(typeof(OnFormSubmit) == "function") // default callback for GForm //
  return OnFormSubmit();
 else if(typeof(widget_submit) == "function") // deprecated //
  return widget_submit();
 else
  gframe_close();
}

