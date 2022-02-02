/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 02-05-2014
 #PACKAGE: dynarc-label-extension
 #DESCRIPTION: GLight Template - Labels
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

function GLLabels(btn)
{
 var oThis = this;
 this.btn = btn;
 this.AP = btn.getAttribute('ap');

 this.O = document.createElement('DIV');
 this.O.className = "gllabels-container";
 this.O.innerHTML = "<div class='gllabels-title'>Etichetta come:</div>";
 this.O.onmouseup = function(evt){
	 if(typeof(evt.stopPropagation) != "undefined")
	  evt.stopPropagation();
	 else
	  evt.cancelBubble=true;
	}

 this.C = document.createElement('DIV');
 this.C.className = "gllabels-list-container";

 this.UL = document.createElement('UL');
 this.UL.className = "gllabels-ul";

 this.C.appendChild(this.UL);

 // button apply //
 this.baDiv = document.createElement('DIV');
 this.baDiv.className = "gllabels-footersec";
 var btnApply = document.createElement('DIV');
 btnApply.className = "gllabels-button-link";
 btnApply.innerHTML = "Applica";
 btnApply.onclick = function(){oThis.Submit();}
 this.baDiv.appendChild(btnApply);
 
 // footer options //
 this.foDiv = document.createElement('DIV');
 this.foDiv.className = "gllabels-footersec";
 var newBtn = document.createElement('DIV');
 newBtn.className = "gllabels-button-link";
 newBtn.innerHTML = "Crea nuova etichetta";
 newBtn.onclick = function(){oThis.NewLabel();}
 this.foDiv.appendChild(newBtn);
 var cfgBtn = document.createElement('DIV');
 cfgBtn.className = "gllabels-button-link";
 cfgBtn.innerHTML = "Gestisci etichette";
 cfgBtn.onclick = function(){oThis.ShowConfig();}
 this.foDiv.appendChild(cfgBtn);
 
 this.O.appendChild(this.C);
 this.O.appendChild(this.baDiv);
 this.O.appendChild(this.foDiv);
 
 document.body.appendChild(this.O);

 this.init();

 this.btn.onclick = function(){oThis.show(this);}
}
//--------------------------------------------------------------------------------------------------------------------//
GLLabels.prototype.init = function()
{
 this.reload();
}
//--------------------------------------------------------------------------------------------------------------------//
GLLabels.prototype.show = function(btn)
{
 ACTIVE_GLLABELS = this;

 var pos = this.getObjectPosition(btn);
 this.O.style.left = pos.x+"px";
 this.O.style.top = (pos.y+btn.offsetHeight)+"px";
 this.O.style.visibility = "visible";
}
//--------------------------------------------------------------------------------------------------------------------//
GLLabels.prototype.hide = function()
{
 this.O.style.visibility = "hidden";
 ACTIVE_GLLABELS = null;
}
//--------------------------------------------------------------------------------------------------------------------//
GLLabels.prototype.reload = function(refId)
{
 var oThis = this;
 this.UL.innerHTML = "";

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 for(var c=0; c < a.length; c++)
	 {
	  var li = document.createElement('LI');
	  li.id = a[c]['id'];
	  li.innerHTML = "<input type='checkbox'"+(a[c]['selected'] ? " checked='true'" : "")+"/> "+a[c]['name'];
	  oThis.UL.appendChild(li);
	 }
	}
 sh.sendCommand("dynarc exec-func ext:labels.list -params `archiveprefix="+this.AP+(refId ? "&itemid="+refId : "")+"`");
}
//--------------------------------------------------------------------------------------------------------------------//
GLLabels.prototype.NewLabel = function()
{
 var oThis = this;
 var title = prompt("Digita un titolo da assegnare alla nuova etichetta");
 if(!title) return;

 title = title.replace('"','');
 title = encodeURI(title.E_QUOT());

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){oThis.reload();}
 sh.sendCommand("dynarc exec-func ext:labels.new -params `ap="+this.AP+"&name="+title+"`");
}
//--------------------------------------------------------------------------------------------------------------------//
GLLabels.prototype.ShowConfig = function()
{
 var oThis = this;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){oThis.reload();}
 sh.sendCommand("gframe -f config.labels -params `ap="+this.AP+"`");
}
//--------------------------------------------------------------------------------------------------------------------//
GLLabels.prototype.Submit = function()
{
 this.hide();
 var ret = "";
 var list = this.UL.getElementsByTagName('LI');
 if(list && list.length)
 {
  for(var c=0; c < list.length; c++)
  {
   var li = list[c];
   var cb = li.getElementsByTagName('INPUT')[0];
   if(cb.checked)
	ret+= ","+li.id;
  }
  ret = ret.substr(1);
 }

 if(this.btn.OnSubmit)
  this.btn.OnSubmit(ret);
}
//--------------------------------------------------------------------------------------------------------------------//
GLLabels.prototype.getObjectPosition = function(e)
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

 return {x:left, y:top};
}
//--------------------------------------------------------------------------------------------------------------------//

var ACTIVE_GLLABELS = null;
function hideActiveGLLabels()
{
 if(ACTIVE_GLLABELS)
  ACTIVE_GLLABELS.hide();
}
document.addEventListener ? document.addEventListener("mouseup",hideActiveGLLabels,false) : document.attachEvent("mouseup",hideActiveGLLabels);

