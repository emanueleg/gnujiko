/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 16-06-2013
 #PACKAGE: todo-module
 #DESCRIPTION: TODO module for Gnujiko Desktop.
 #VERSION: 2.0beta
 #CHANGELOG: 16-06-2013 - Bug fix vari.
 #TODO:
 
*/

function todomodule_load(modId)
{
}

function todomodule_newTodo()
{
 var ed = document.getElementById("todolist-newtodoedit");
 if(!ed.value)
 {
  var tit = prompt("Inserisci un titolo");
  if(!tit)
   return;
  ed.value = tit;
 }
 
 var tb = document.getElementById('todolist');
 var imgFolder = ABSOLUTE_URL+"var/desktop/modules/todo/img/";

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var r = tb.insertRow(-1);
	 r.id = a['id'];
	 r.insertCell(-1).innerHTML = "<img src='"+imgFolder+"cb_unchecked.png' onclick='todomodule_setTodoStatus(1,this)'/>";
	 r.insertCell(-1).innerHTML = "<a href='#' onclick='todomodule_editTodo(this)'>"+a['name']+"</a>";
	 r.insertCell(-1).innerHTML = "<img src='"+imgFolder+"delete.png' onclick='todomodule_deleteTodo(this)'/>";
	 r.cells[0].style.width=40; r.cells[0].style.textAlign='center'; r.cells[0].style.verticalAlign='middle';
	 r.cells[1].style.verticalAlign='middle';
	 r.cells[2].style.width=32; r.cells[2].style.textAlign='center'; r.cells[2].style.verticalAlign='middle';
	 ed.value = "";
	 ed.focus();
	 todomodule_editTodo(r.cells[1].getElementsByTagName('A')[0],true);
	}
 sh.sendCommand("dynarc new-item -ap todo -name `"+ed.value+"`");
}

function todomodule_editTodo(aObj,showeditor)
{
 var r = aObj.parentNode.parentNode;
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 aObj.innerHTML = a['name'];
	}
 sh.sendCommand("gframe -f todo/edit -params `id="+r.id+(showeditor ? "&showeditor=true" : "")+"`");
}

function todomodule_setTodoStatus(img,status)
{
 var r = img.parentNode.parentNode;
 var imgFolder = ABSOLUTE_URL+"var/desktop/modules/todo/img/";

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 if(status > 0)
	 {
	  img.src = imgFolder+"cb_checked.png";
	  img.onclick = function(){todomodule_setTodoStatus(this,0);}
	 }
	 else
	 {
	  img.src = imgFolder+"cb_unchecked.png";
	  img.onclick = function(){todomodule_setTodoStatus(this,1);}
	 }
	}
 sh.sendCommand("dynarc edit-item -ap todo -id '"+r.id+"' -set `status="+status+"`");
}

function todomodule_deleteTodo(img)
{
 var tb = document.getElementById('todolist');
 var r = img.parentNode.parentNode;
 if(!confirm("Sei sicuro di voler rimuoverlo dalla lista?"))
  return;

 var sh = new GShell();
 sh.OnOutput = function(){tb.deleteRow(r.rowIndex);}
 sh.sendCommand("dynarc delete-item -ap todo -id '"+r.id+"' -r");
}

function todomodule_update(date,modId)
{
}

