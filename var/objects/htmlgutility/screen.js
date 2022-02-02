/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-05-2012
 #PACKAGE: htmlgutility
 #DESCRIPTION: Screen functions
 #VERSION: 2.1beta
 #CHANGELOG: 24-05-2012 : Bug fix in function getObjectPosition.
 #TODO:
 
*/

function getScreenWidth()
{
 if(window.innerWidth)
  return window.innerWidth;
 else if(document.all)
  return document.body.clientWidth;
 return 0;
}

function getScreenHeight()
{
 if(window.innerHeight)
  return window.innerHeight;
 else if(document.all)
  return document.body.clientHeight;
 return 0;
}

function getObjectPosition(e)
{
 var left = e.offsetLeft;
 var top  = e.offsetTop;
 var obj = e;
 while(e = e.offsetParent)
 {
  left+= e.offsetLeft-e.scrollLeft;
  top+= e.offsetTop-e.scrollTop;
 }

 while(obj = obj.parentNode)
 {
  left+= obj.scrollLeft ? obj.scrollLeft : 0;
  top+= obj.scrollTop ? obj.scrollTop : 0;
 }

 //left-= document.body.scrollLeft;
 //top-= document.body.scrollTop;
 return {x:left, y:top};
}
//-------------------------------------------------------------------------------------------------------------------//

