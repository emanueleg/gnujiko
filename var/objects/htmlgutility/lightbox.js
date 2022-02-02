/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 19-12-2014
 #PACKAGE: htmlgutility
 #DESCRIPTION: Gnujiko LightBox
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO: Manca possibilita di visionare piu immagini.
 
*/

function GLightBox(options)
{
 this.canvasWidth = (options && options.width) ? options.width : 640;
 this.canvasHeight = (options && options.height) ? options.height : 480;

 this.currentFileName = "";

 this.initialize();
}
//-------------------------------------------------------------------------------------------------------------------//
GLightBox.prototype.initialize = function()
{
 var oThis = this;

 this.O = document.createElement('DIV');
 this.O.className = "glightbox";
 this.O.style.visibility = "hidden";

 this.btnClose = document.createElement('IMG');
 this.btnClose.src = ABSOLUTE_URL+"var/objects/htmlgutility/img/lightbox-btn-close.png";
 this.btnClose.className = "glightbox-btnclose";
 this.btnClose.title = "Chiudi";
 this.btnClose.onclick = function(){oThis.hide();}
 this.O.appendChild(this.btnClose);

 this.C = document.createElement('DIV');
 this.C.className = "glightbox-canvas";
 this.C.style.width = this.canvasWidth+"px";
 this.C.style.height = this.canvasHeight+"px";

 this.F = document.createElement('DIV');
 this.F.className = "glightbox-footer";

 this.photoTitleO = document.createElement('SPAN');
 this.photoTitleO.innerHTML = "";

 this.F.appendChild(this.photoTitleO);

 this.downloadBtn = document.createElement('IMG');
 this.downloadBtn.src = ABSOLUTE_URL+"var/objects/htmlgutility/img/lightbox-btn-download.png";
 this.downloadBtn.className = "glightbox-btndownload";
 this.downloadBtn.title = "Scarica";
 this.downloadBtn.onclick = function(){oThis.download();}
 this.F.appendChild(this.downloadBtn);

 this.O.appendChild(this.C);
 this.O.appendChild(this.F);

 document.body.appendChild(this.O);
}
//-------------------------------------------------------------------------------------------------------------------//
//--- P U B L I C ---------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
GLightBox.prototype.show = function(fileName, title)
{
 this.currentFileName = fileName;

 var scrW = window.innerWidth ? window.innerWidth : (document.all ? document.body.clientWidth : 800);
 var scrH = window.innerHeight ? window.innerHeight : (document.all ? document.body.clientHeight : 600);

 this.C.style.backgroundImage = "url("+ABSOLUTE_URL+fileName+")";

 this.O.style.left = (Math.floor(scrW/2) - Math.floor(this.O.offsetWidth/2))+"px";
 this.O.style.top = (Math.floor(scrH/2) - Math.floor(this.O.offsetHeight/2) + document.body.scrollTop)+"px";

 this.photoTitleO.innerHTML = title ? title : "";

 this.O.style.visibility = "visible";
}
//-------------------------------------------------------------------------------------------------------------------//
GLightBox.prototype.hide = function()
{
 this.O.style.visibility = "hidden";
}
//-------------------------------------------------------------------------------------------------------------------//
GLightBox.prototype.download = function()
{
 if(this.currentFileName)
 {
  document.location.href = ABSOLUTE_URL+"getfile.php?file="+this.currentFileName;
  this.hide();
 }
}
//-------------------------------------------------------------------------------------------------------------------//
//--- P R I V A T E -------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//


