/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 20-10-2013
 #PACKAGE: gcal
 #DESCRIPTION: Small calendar object
 #VERSION: 2.2beta
 #CHANGELOG: 20-10-2013 : Bug fix in function getObjectPosition
			 27-09-2013 : Bug fix in getObjectPosition.
 #DEPENDS:
 #TODO:
 
*/

function GCal()
{
 this.O = document.createElement('DIV');
 this.O.className = "gcal";

 var html = "<table class='gcalheader' cellspacing='0' cellpadding='0' border='0'><tr><td><div class='gcalprevbtn'>&nbsp;</div></td>";
 html+= "<td><div class='gcaltext'>2012<br/>AGOSTO</div></td>";
 html+= "<td align='right'><div class='gcalnextbtn'>&nbsp;</div></td></tr></table>";

 html+= "<table class='gcalgrid' width='180' height='140' cellspacing='0' cellpadding='0' border='0'>";
 html+= "<tr><th class='week'>&nbsp;</th> <th>L</th> <th>M</th> <th>M</th> <th>G</th> <th>V</th> <th>S</th> <th>D</th> </tr>";
 for(var c=0; c < 6; c++)
  html+= "<tr><td class='week'>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
 html+= "</table>";

 this.O.innerHTML = html;
 
 this.O.style.left = 0;
 this.O.style.top = 0;
 this.O.style.position = "absolute";
 this.O.style.display = "block";

 this.O.onmouseover = function(){this.mouseOverCalendar=true;}
 this.O.onmouseout = function(){this.mouseOverCalendar=false;}

 this.months = new Array("Gennaio","Febbraio","Marzo","Aprile","Maggio","Giugno","Luglio","Agosto","Settembre","Ottobre","Novembre","Dicembre");

 this.date = new Date();
 this.selectedDayTD = null;

 /* EVENTS */
 this.OnChange = null;
}

GCal.prototype.Show = function(obj, date, correctX, correctY)
{
 var oThis = this;
 this.O.style.visibility = "hidden";
 document.body.appendChild(this.O);
 if(date)
  this.date = date;

 var xy = this.getObjectPosition(obj); 
 if(correctX)
  xy.x+= correctX;
 if(correctY)
  xy.y+= correctY;

 this.O.style.top = xy.y + obj.offsetHeight;
 this.O.style.left = (xy.x + Math.floor(obj.offsetWidth/2)) - Math.floor(this.O.offsetWidth/2);
 if(!correctX && !correctY)
 {
  var screenWidth = window.innerWidth ? window.innerWidth : document.body.clientWidth;
  //var screenHeight = window.innerHeight ? window.innerHeight : document.body.clientHeight;
  if((parseFloat(this.O.style.left)+this.O.offsetWidth) > screenWidth)
   this.O.style.left = screenWidth - this.O.offsetWidth;
 }
 this.O.style.visibility = "visible";

 var r = this.O.getElementsByTagName('TABLE')[0].rows[0];
 var prevBtn = r.cells[0].getElementsByTagName('DIV')[0];
 this.textSpace = r.cells[1].getElementsByTagName('DIV')[0];
 var nextBtn = r.cells[2].getElementsByTagName('DIV')[0];

 prevBtn.onclick = function(){oThis.prevMonth();}
 nextBtn.onclick = function(){oThis.nextMonth();}

 this.Update();
}

GCal.prototype.Hide = function(forced)
{
 if(this.O.mouseOverCalendar && !forced)
  return;
 this.O.style.visibility = "hidden";
}

GCal.prototype.Update = function()
{
 var oThis = this;
 var tb = this.O.getElementsByTagName('TABLE')[1];
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 oThis.textSpace.innerHTML = oThis.date.printf('Y')+"<br/>"+oThis.months[oThis.date.getMonth()];
	 var rIdx = 1;
	 for(var c=0; c < a.length; c++)
	 {
	  tb.rows[rIdx].cells[0].innerHTML = a[c]['week'];
	  for(var i=0; i < 7; i++)
	  {
	   tb.rows[rIdx].cells[i+1].innerHTML = a[c]['days'][i];
	   if(a[c]['dates'][i] == oThis.date.printf('Y-m-d'))
	   {
		tb.rows[rIdx].cells[i+1].className = "selected";
		oThis.selectedDayTD = tb.rows[rIdx].cells[i+1];
	   }
	   else
		tb.rows[rIdx].cells[i+1].className = "";
	   tb.rows[rIdx].cells[i+1].onclick = function(){oThis._daySelect(this);}
	   tb.rows[rIdx].cells[i+1].id = a[c]['dates'][i];
	  }
	  rIdx++;
	 }
	}
 sh.sendCommand("calendar print -month "+(this.date.getMonth()+1)+" -year "+this.date.getFullYear());
}

GCal.prototype.prevMonth = function()
{
 this.date.PrevMonth();
 this.Update();
}

GCal.prototype.nextMonth = function()
{
 this.date.NextMonth();
 this.Update();
}

GCal.prototype._daySelect = function(td)
{
 if(this.selectedDayTD)
  this.selectedDayTD.className = "";
 td.className = "selected";
 this.selectedDayTD = td;
 this.date.setFromISO(td.id);
 if(this.OnChange)
  this.OnChange(this.date);
 this.Hide(true);
}

GCal.prototype.getObjectPosition = function(e)
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

