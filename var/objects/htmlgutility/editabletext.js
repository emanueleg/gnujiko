/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 27-02-2012
 #PACKAGE: htmlgutility
 #DESCRIPTION: Editable text
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function EditableText(obj, beforeInitCallback, beforeEditCallback, afterEditCallback)
{
 obj.editor = null;
 obj.editortype = "edit";

 obj.edit = function(){

	 if(this.editor)
	  return;

	 /* Get editor size */
	 var pos = EditableText_getABSPosition(this);
	 if(pos.x < 0) pos.x = 0;
	 if(pos.y < 0) pos.y = 0;
	 var edmat = {x:pos.x+3, y:pos.y+3, w:this.offsetWidth, h:this.offsetHeight};
	 var screenWidth = window.innerWidth ? window.innerWidth : document.body.clientWidth;
	 var screenHeight = window.innerHeight ? window.innerHeight : document.body.clientHeight;
	 if((pos.x + this.offsetWidth) > screenWidth)
	  edmat.w = this.offsetWidth - pos.x;
	 if((pos.y + this.offsetHeight) > screenHeight)
	  edmat.h = this.offsetHeight - pos.y;

	 if(beforeInitCallback)
	 {
	  if(beforeInitCallback(this,edmat) == false)
	   return;
	 }

	 switch(this.editortype)
	 {
	  case 'edit' : {
		 /* Create editor */	 
		 this.editor = document.createElement('INPUT');
		 this.editor.type='text';
		 this.editor.className = "editable-text-editor";
	 	 this.editor.style.left = edmat.x;
		 this.editor.style.top = edmat.y;
		 this.editor.style.width = edmat.w;
		 this.editor.style.height = edmat.h;
		 this.editor.oldvalue = this.innerHTML;
		 this.editor.value = this.innerHTML != "&nbsp;" ? this.innerHTML : "";
		 this.editor.obj = this;
		 this.editor.onchange = this.editor.onblur = function(){if(!this.onedit) return; this.exit(true);}
		 this.editor.exit = function(applyChanges){
		 	 this.onedit = false;
			 if(!applyChanges || (this.value == this.oldvalue))
			 {
			  this.parentNode.removeChild(this);
			  this.obj.editor = null;
			  return;
			 }
			 if(!this.value)
			  this.value = "&nbsp;";
			 this.obj.innerHTML = this.value;
			 if(this.parentNode)
			  this.parentNode.removeChild(this);
			 this.obj.editor = null;
			 if(afterEditCallback)
			  afterEditCallback(this,this.value);
			}
		} break;

	  case 'textarea' : {
		 /* Create editor */	 
		 this.editor = document.createElement('TEXTAREA');
		 this.editor.className = "editable-text-editor";
		 this.editor.style.left = edmat.x;
		 this.editor.style.top = edmat.y;
		 this.editor.style.width = edmat.w;
		 this.editor.style.height = edmat.h;
		 this.editor.oldvalue = this.innerHTML;
		 this.editor.innerHTML = this.innerHTML != "&nbsp;" ? this.innerHTML : "";
		 this.editor.obj = this;
		 this.editor.onchange = this.editor.onblur = function(){if(!this.onedit) return; this.exit(true);}
		 this.editor.exit = function(applyChanges){
		 	 this.onedit = false;
			 if(!applyChanges || (this.value == this.oldvalue))
			 {
			  this.parentNode.removeChild(this);
			  this.obj.editor = null;
			  return;
			 }
			 if(!this.value)
			  this.value = "&nbsp;";
			 this.obj.innerHTML = this.value;
			 if(this.parentNode)
			  this.parentNode.removeChild(this);
			 this.obj.editor = null;
			 if(afterEditCallback)
			  afterEditCallback(this,this.value);
			}
		} break;
	 }

	 this.editor.style.visibility = "hidden";

	 this.editor.onedit = true;

	 if(beforeEditCallback)
	 {
	  if(beforeEditCallback(this) == false)
	   return this.editor.exit(false);
	 }

	 document.body.appendChild(this.editor);

	 this.editor.style.visibility = "visible";
	 this.editor.focus();
	 this.editor.select();
	}

 obj.onclick = function(){this.edit();}
}

function EditableText_getABSPosition(e)
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

 left-= document.body.scrollLeft;
 top-= document.body.scrollTop;
 return {x:left, y:top};
}

