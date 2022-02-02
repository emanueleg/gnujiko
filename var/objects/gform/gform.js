/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-05-2014
 #PACKAGE: gform
 #DESCRIPTION: Common javascript functions for GForm
 #VERSION: 2.0beta
 #CHANGELOG: 28-05-2014 : Aggiunto WidgetOnAbort
			 06-11-2011 : Bug fix in CloseWidget function.
 #DEPENDS:
 #TODO:
 
*/

function CloseWidget(o,a)
{
 if(typeof(WidgetOnAbort) == "function")
  WidgetOnAbort();
 else
  gframe_close(o,a);
}
