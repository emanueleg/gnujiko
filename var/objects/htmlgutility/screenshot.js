/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 09-07-2012
 #PACKAGE: htmlgutility
 #DESCRIPTION: Make a screen shot on a object.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function ScreenShot (object, callback)
{
 if(!object)
  object = document.body;
 var preload = html2canvas.Preload(object, {
     "complete": function (images) {
         var queue = html2canvas.Parse(object, images);
         var canvas = $(html2canvas.Renderer(queue, null, object));
		 var canvasElement = canvas[0];
		 var a = canvasElement.toDataURL();
		
		 if(callback)
		  callback(a);
    	}
	});
}
