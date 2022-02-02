/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-08-2010
 #PACKAGE: gtabmenu
 #DESCRIPTION: Official Gnujiko tab menu
 #VERSION: 2.0
 #CHANGELOG:
 #TODO:
 
*/

function GTabMenu(_obj)
{
 this.O = _obj;
}

GTabMenu.prototype.select = function(idx)
{
 var li = this.O.getElementsByTagName('LI');
 for(var c=0; c < li.length; c++)
 {
  if(idx == c)
   li[c].className = "selected";
  else if(c==0)
   li[c].className = "first";
  else if(c == (li.length-1))
   li[c].className = "last";
  else if(c < idx)
   li[c].className = "prior";
  else if(c > idx)
   li[c].className = "next";
 }
}
