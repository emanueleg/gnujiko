/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-05-2014
 #PACKAGE: gform
 #DESCRIPTION: Common javascript functions for template orange
 #VERSION: 2.1beta
 #CHANGELOG: 28-05-2014 : Aggiunto WidgetOnAbort
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

function WidgetOnAbort()
{
 if(typeof(OnFormAbort) == "function") // default callback for GForm //
  return OnFormAbort();
 else if(typeof(widget_close) == "function") // deprecated //
  return widget_close();
 else
  gframe_close();
}

