/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-12-2014
 #PACKAGE: htmlgutility
 #DESCRIPTION: Gnujiko Content Preview
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO: 
 
*/

function GContentPreview(title, options)
{
 this.title = title ? title : "senza titolo";
 this.canvasWidth = (options && options.width) ? options.width : 210;
 this.canvasHeight = (options && options.height) ? options.height : 297;

 this.HTML_CONTENT = "";
 this.CSS_CONTENT = "";
 this.cssO = null;

 this.screenMask = null;
 
 this.initialize();
}
//-------------------------------------------------------------------------------------------------------------------//
GContentPreview.prototype.initialize = function()
{
 var oThis = this;

 this.O = document.createElement('DIV');
 this.O.className = "gcontentpreview";
 this.O.style.visibility = "hidden";

 this.btnClose = document.createElement('IMG');
 this.btnClose.src = ABSOLUTE_URL+"var/objects/htmlgutility/img/lightbox-btn-close.png";
 this.btnClose.className = "gcontentpreview-btnclose";
 this.btnClose.title = "Chiudi";
 this.btnClose.onclick = function(){oThis.hide();}
 this.O.appendChild(this.btnClose);

 this.CO = document.createElement('DIV');
 this.CO.className = "gcontentpreview-canvas-outer";

 this.C = document.createElement('DIV');
 this.C.className = "gcontentpreview-canvas";
 this.C.style.width = this.canvasWidth+"mm";
 this.C.style.height = this.canvasHeight+"mm";

 this.CO.appendChild(this.C);

 this.F = document.createElement('DIV');
 this.F.className = "gcontentpreview-footer";

 this.photoTitleO = document.createElement('SPAN');
 this.photoTitleO.innerHTML = "";

 this.F.appendChild(this.photoTitleO);

 this.sendmailBtn = document.createElement('IMG');
 this.sendmailBtn.src = ABSOLUTE_URL+"var/objects/htmlgutility/img/sendmail-gray.png";
 this.sendmailBtn.className = "gcontentpreview-button";
 this.sendmailBtn.title = "Invia per email";
 this.sendmailBtn.onclick = function(){oThis.sendMail();}
 this.F.appendChild(this.sendmailBtn);

 this.printBtn = document.createElement('IMG');
 this.printBtn.src = ABSOLUTE_URL+"var/objects/htmlgutility/img/print-gray.png";
 this.printBtn.className = "gcontentpreview-button";
 this.printBtn.title = "Stampa";
 this.printBtn.onclick = function(){oThis.exportToPDF();}
 this.F.appendChild(this.printBtn);

 this.O.appendChild(this.CO);
 this.O.appendChild(this.F);

 document.body.appendChild(this.O);
}
//-------------------------------------------------------------------------------------------------------------------//
//--- P U B L I C ---------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
GContentPreview.prototype.setTitle = function(title)
{
 this.title = title;
 this.photoTitleO.innerHTML = "Anteprima di: "+this.title;
}
//-------------------------------------------------------------------------------------------------------------------//
GContentPreview.prototype.setContent = function(content)
{
 this.HTML_CONTENT = content;
 this.C.innerHTML = content;
}
//-------------------------------------------------------------------------------------------------------------------//
GContentPreview.prototype.setCSS = function(cssContent)
{
 this.CSS_CONTENT = cssContent;
 if(this.cssO)
  this.cssO.parentNode.removeChild(this.cssO);
 this.cssO = document.createElement('STYLE');
 this.cssO.innerHTML = cssContent;
 document.body.appendChild(this.cssO);
}
//-------------------------------------------------------------------------------------------------------------------//
GContentPreview.prototype.show = function()
{
 this.showScreenMask();
 var scrW = window.innerWidth ? window.innerWidth : (document.all ? document.body.clientWidth : 800);
 var scrH = window.innerHeight ? window.innerHeight : (document.all ? document.body.clientHeight : 600);

 var height = this.C.offsetHeight;
 if(height > (scrH+90))
 {
  this.CO.style.height = (scrH - 90)+"px";
  this.CO.style.width = (this.CO.offsetWidth+10)+"px";
 }

 var left = (Math.floor(scrW/2) - Math.floor(this.O.offsetWidth/2));
 var top = (Math.floor(scrH/2) - Math.floor(this.O.offsetHeight/2) + document.body.scrollTop);
 if(left < 0) left = 0;
 if(top < 20) top  = 20;

 this.O.style.left = left+"px";
 this.O.style.top = top+"px";

 this.photoTitleO.innerHTML = "Anteprima di: "+this.title;

 this.O.style.visibility = "visible";
}
//-------------------------------------------------------------------------------------------------------------------//
GContentPreview.prototype.hide = function()
{
 this.O.style.visibility = "hidden";
 this.hideScreenMask();
}
//-------------------------------------------------------------------------------------------------------------------//
GContentPreview.prototype.exportToPDF = function(fileName)
{
 if(!fileName) var fileName = this.title;
 fileName = fileName.replace(/ /g,"_");

 var sh = new GShell();
 sh.showProcessMessage("Esportazione in PDF", "Attendere prego, è in corso l'esportazione del documento in PDF.");
 sh.OnError = function(err){this.processMessage.error(err);}
 sh.OnOutput = function(o,a){
	 this.hideProcessMessage();
	 if(!a) return;
	 window.open(ABSOLUTE_URL+a['fullpath'],"_blank");
	}
 sh.sendCommand("pdf export -contents `"+this.HTML_CONTENT+"` -csscontent `"+this.CSS_CONTENT+"` -o `"+fileName+"`");
}
//-------------------------------------------------------------------------------------------------------------------//
GContentPreview.prototype.sendMail = function(email, subject)
{
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	}
 sh.sendCommand("gframe -f sendmail -c `"+this.HTML_CONTENT+"` --use-cache-contents");
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
//--- P R I V A T E -------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
GContentPreview.prototype.showScreenMask = function()
{
 if(!this.screenMask)
 {
  this.screenMask = document.createElement('DIV');
  this.screenMask.className = "gcontentpreview-screen-mask";
 }
 this.screenMask.style.height = (document.body.scrollHeight ? document.body.scrollHeight+"px" : "100%");
 document.body.appendChild(this.screenMask);

}
//-------------------------------------------------------------------------------------------------------------------//
GContentPreview.prototype.hideScreenMask = function()
{
 if(this.screenMask && this.screenMask.parentNode)
  this.screenMask.parentNode.removeChild(this.screenMask);
}
//-------------------------------------------------------------------------------------------------------------------//



