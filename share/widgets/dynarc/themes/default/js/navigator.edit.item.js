/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2010 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 26-10-2010
 #PACKAGE: dynarc
 #DESCRIPTION: Default theme for dynarc.navigator - Edit item form
 #VERSION: 2.0
 #CHANGELOG:
 #DEPENDS:
 #TODO:
 
*/
var PLUGINS_FUNCTIONS = new Array();

function DynarcNavigator()
{
 this.pages = new Array();
}

DynarcNavigator.prototype.registerPage = function(pg)
{
 this.pages.push(pg);
}

DynarcNavigator.prototype.showPage = function(pg)
{
 var idx = this.pages.indexOf(pg);
 TabMenu.select(idx);

 for(var c=0; c < this.pages.length; c++)
  document.getElementById(this.pages[c]).style.display = "none";

 document.getElementById(this.pages[idx]).style.display="";
}

//-------------------------------------------------------------------------------------------------------------------//
var Navigator = new DynarcNavigator();
Navigator.registerPage("generality");

