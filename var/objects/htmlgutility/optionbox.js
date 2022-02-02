/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-11-2014
 #PACKAGE: htmlgutility
 #DESCRIPTION: Option Box
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

var OPTIONBOXPOPUPMENULIST = new Array();

function OptionBox(obj, options)
{
 this.O = obj ? obj : document.createElement('DIV');
 this.O.className = "optionbox";
 this.O.obH = this;

 this.UL = document.createElement('UL');
 this.UL.className = "optionbox-popupmenu";
 this.UL.obH = this;

 this.ULC = document.createElement('DIV');
 this.ULC.className = "optionbox-popupmenu-container";
 this.ULC.appendChild(this.UL);
 this.ULC.onmouseover = function(){this.mouseisover=true;}
 this.ULC.onmouseout = function(){this.mouseisover=false;}

 this.footer = document.createElement('DIV');
 this.footer.className = "optionbox-footer";
 this.footer.innerHTML = "<span>tutti</span> | <span>nessuno</span>";
 this.ULC.appendChild(this.footer);
 
 
 this.selectedOptions = new Array();

 /* CONFIG */
 this.options = {
	 maxheight: (options && options.maxheight) ? options.maxheight : 200,   // max menu height. 
	 separator: (options && options.separator) ? options.separator : ",",	// separator used into editor.
	 obt: (options && options.obt) ? options.obt : 40						// overflow bottom tollerance.
	};

 /* REWRITABLE EVENTS */
 this.OnChange = function(){}

 this.initialize();
}
//-------------------------------------------------------------------------------------------------------------------//
OptionBox.prototype.initialize = function()
{
 OPTIONBOXPOPUPMENULIST.push(this.UL);
 this.O.onclick = function(){this.obH.showPopupMenu();}
 document.body.appendChild(this.ULC);

 var span = this.footer.getElementsByTagName('SPAN');
 if(span && (span.length == 2))
 {
  span[0].obH = this; span[0].onclick = function(){this.obH.selectAll();}
  span[1].obH = this; span[1].onclick = function(){this.obH.unselectAll();}
 }

}
//-------------------------------------------------------------------------------------------------------------------//
//--- P U B L I C ---------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
OptionBox.prototype.addOption = function(title, value, checked)
{
 var li = document.createElement('LI');
 li.innerHTML = "<input type='checkbox'"+(checked ? " checked='true'" : "")+"/> "+title;
 li.setAttribute('retval',value ? value : title);
 this.UL.appendChild(li);
 this.initMenuItem(li);

 if(checked)
  this.selectedOptions.push(li);

 return li;
}
//-------------------------------------------------------------------------------------------------------------------//
OptionBox.prototype.addSeparator = function()
{
 var li = document.createElement('LI');
 li.className = "separator";
 this.UL.appendChild(li);
 return li;
}
//-------------------------------------------------------------------------------------------------------------------//
OptionBox.prototype.setOptions = function(optlist, maintainvalues)
{
 /* The variable 'optlist' can be:
	- an array of strings (for only titles): array('option 1', 'option 2', 'option 3', ...)
	- an array of objects (with title and value) : array({name:'option 1', value='1'}, {name:'option 2', value='2'}, ...)
	- an bidimensional array (with title and value) : arr1 = new Array(); arr1['name']='option 1'; arr1['value']='1';
													  arr2 = new Array(); arr2['name']='option 2'; arr2['value']='2';
													  arr3 = new Array(); arr3['name']='option 3'; arr3['value']='3';
													  array( arr1, arr2, arr3, ...) 
 */

 this.oldSelectedOptions = new Array();
 
 if(this.O.getAttribute('datavalues'))
 {
  var dataValues = this.O.getAttribute('datavalues').substr(1);
  dataValues = dataValues.substr(0,dataValues.length-1);
  var x = dataValues.split("],[");
  for(var c=0; c < x.length; c++)
   this.oldSelectedOptions.push({title:x[c], value:x[c]});
  this.O.setAttribute('datavalues','');
 }
 else
 {
  for(var c=0; c < this.selectedOptions.length; c++)
  {
   var tit = this.selectedOptions[c].textContent;
   var val = this.selectedOptions[c].getAttribute('retval');
   this.oldSelectedOptions.push({title:tit, value:val});
  }
 }
 
 this.resetOptions();
 
 if(!optlist || !optlist.length)
  return this._updateReturnValue();

 for(var c=0; c < optlist.length; c++)
 {
  var opt = optlist[c];
  var tit = "";
  var val = "";
  var checked = false;
  if(typeof(opt) == "string") { tit=opt; val=opt; }
  else
  {
   if(opt['name']) 		{ tit = opt['name'];	val = opt['value'] ? opt['value'] : opt['name']; }
   else if(opt.name) 	{ tit = opt.name;		val = opt.value ? opt.value : opt.name;	}
  }
  if(!tit || (tit == 0))
   this.addSeparator();
  else
  {
   for(var i=0; i < this.oldSelectedOptions.length; i++)
   {
    var oopt = this.oldSelectedOptions[i];
    if(oopt.value && (oopt.value == val)) { checked = true; break; }
    else if(oopt.title == tit) 			 { checked = true; break; }
   }
   this.addOption(tit, val, checked);
  }
 }

 this._updateReturnValue();
}
//-------------------------------------------------------------------------------------------------------------------//
OptionBox.prototype.resetOptions = function()
{
 this.selectedOptions = new Array();
 this.O.innerHTML = "";
 this.O.title = "";
 this.UL.innerHTML = "";
}
//-------------------------------------------------------------------------------------------------------------------//
OptionBox.prototype.getValue = function(boolAsArray, separator)
{
 if(boolAsArray)
 {
  var ret = new Array();
  for(var c=0; c < this.selectedOptions.length; c++)
   ret.push({title:this.selectedOptions[c].textContent, value:this.selectedOptions[c].getAttribute('retval')});
  return ret;
 }
 else
 {
  var sep = separator ? separator : this.options.separator;
  var str = "";
  for(var c=0; c < this.selectedOptions.length; c++)
   str+= sep+" "+this.selectedOptions[c].textContent;
  if(str != "") str = str.substr(2);
  return str;
 }
}
//-------------------------------------------------------------------------------------------------------------------//
OptionBox.prototype.setValue = function(value)
{
 /* TODO: da continuare ...*/
 alert(typeof(value));
}
//-------------------------------------------------------------------------------------------------------------------//
OptionBox.prototype.selectAll = function()
{
 this.selectedOptions = new Array();
 var list = this.UL.getElementsByTagName('LI');
 if(!list || !list.length) 
  return;

 for(var c=0; c < list.length; c++)
 {
  var cb = list[c].getElementsByTagName('INPUT')[0];
  if(cb && (cb.type == 'checkbox'))
  {
   cb.checked = true;
   this.selectedOptions.push(list[c]);
  }
 }

 var textValue = this.getValue();
 this.O.innerHTML = textValue;
 this.O.title = textValue;

 this.hidePopupMenu(true);
 return this.selectedOptions;
}
//-------------------------------------------------------------------------------------------------------------------//
OptionBox.prototype.unselectAll = function()
{
 this.selectedOptions = new Array();
 var list = this.UL.getElementsByTagName('LI');
 if(!list || !list.length) 
  return;

 for(var c=0; c < list.length; c++)
 {
  var cb = list[c].getElementsByTagName('INPUT')[0];
  if(cb && (cb.type == 'checkbox'))
   cb.checked = false;
 }

 var textValue = this.getValue();
 this.O.innerHTML = textValue;
 this.O.title = textValue;

 this.hidePopupMenu(true);
}
//-------------------------------------------------------------------------------------------------------------------//

//-------------------------------------------------------------------------------------------------------------------//
//--- P R I V A T E -------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
OptionBox.prototype._updateReturnValue = function()
{
 var list = this.UL.getElementsByTagName('LI');
 if(list && list.length)
 {
  for(var c=0; c < list.length; c++)
  {
   var li = list[c];
   var cb = li.getElementsByTagName('INPUT')[0];
   if(cb && (cb.type == "checkbox"))
   {
    if((cb.checked == true) && (this.selectedOptions.indexOf(li) < 0))
 	 this.selectedOptions.push(li);
    else if(!cb.checked && (this.selectedOptions.indexOf(li) > -1))
	 this.selectedOptions.splice(this.selectedOptions.indexOf(li),1);
   }
  }
 }
 var textValue = this.getValue();
 this.O.innerHTML = textValue;
 this.O.title = textValue;
}
//-------------------------------------------------------------------------------------------------------------------//
OptionBox.prototype.initMenuItem = function(_LI)
{
 var oThis = this;

 var cb = _LI.getElementsByTagName('INPUT')[0];
 if(cb && (cb.type == "checkbox"))
 {
  cb.obH = this;
  cb.onclick = function(){this.obH._updateReturnValue();}
 }
}
//-------------------------------------------------------------------------------------------------------------------//
OptionBox.prototype.getAbsPos = function(e)
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
//-------------------------------------------------------------------------------------------------------------------//
OptionBox.prototype.showPopupMenu = function()
{
 this.O.className = this.O.className.replace(" optionbox-hover", "")+" optionbox-hover";
 var pos = this.getAbsPos(this.O);
 this.ULC.style.height = "20px";
 this.ULC.style.visibility = "hidden";
 this.ULC.style.display = "";
 this.ULC.style.width = (this.O.offsetWidth-2)+"px";
 this.ULC.style.left = pos.x+"px";
 this.ULC.style.top = (pos.y+this.O.offsetHeight)+"px";
 var screenWidth = window.innerWidth ? window.innerWidth : document.body.clientWidth;
 var screenHeight = window.innerHeight ? window.innerHeight : document.body.clientHeight;
 if((parseFloat(this.ULC.style.left)+this.ULC.offsetWidth) > screenWidth)
  this.ULC.style.left = screenWidth - this.ULC.offsetWidth;

 var height = (this.options.maxheight && ((this.UL.offsetHeight + this.footer.offsetHeight) > this.options.maxheight)) ? this.options.maxheight : this.UL.offsetHeight+this.footer.offsetHeight;
 if((parseFloat(this.ULC.style.top)+height+this.options.obt) > screenHeight)
  this.ULC.style.top = (screenHeight - height - this.options.obt)+"px";
 this.ULC.style.height = height+"px";
 this.ULC.style.visibility = "visible";
}
//-------------------------------------------------------------------------------------------------------------------//
OptionBox.prototype.hidePopupMenu = function(boolForce)
{
 if(this.ULC.mouseisover && !boolForce) 
  return;

 this.O.className = this.O.className.replace(" optionbox-hover", "");
 this.ULC.style.visibility="hidden";
 this.ULC.style.display = "none";
}
//-------------------------------------------------------------------------------------------------------------------//
function OptionBox_hideAllPopupMenu()
{
 for(var c=0; c < OPTIONBOXPOPUPMENULIST.length; c++)
  OPTIONBOXPOPUPMENULIST[c].obH.hidePopupMenu();
}
//-------------------------------------------------------------------------------------------------------------------//
document.addEventListener ? document.addEventListener("mouseup",OptionBox_hideAllPopupMenu,false) : document.attachEvent("onmouseup",OptionBox_hideAllPopupMenu);
//-------------------------------------------------------------------------------------------------------------------//

