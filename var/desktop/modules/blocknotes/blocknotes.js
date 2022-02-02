/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-05-2013
 #PACKAGE: blocknotes-module
 #DESCRIPTION: BlockNotes module for Gnujiko Desktop.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function blocknotesmodule_load(modId)
{
 var es = EditSearch.init(document.getElementById('blocknoteslist-search'), "dynarc search -ap 'blocknotes' -fields name,description `","` --order-by `name ASC` -limit 10","id","name","items",true,"name");
 es.onchange = function(){
	 if(this.data && this.data['id'])
	 {
	  var sh = new GShell();
	  sh.sendCommand("gframe -f blocknotes/edit -params `id="+this.data['id']+"`");
	  this.value = "";
	  this.focus();
	 }

	}

}

function blocknotesmodule_new()
{
 var tb = document.getElementById('blocknoteslist');
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var r = tb.insertRow(-1);
	 r.id = a['id'];
	 var td = r.insertCell(-1);
	 td.style.verticalAlign='middle';
	 td.innerHTML = "<a href='#' onclick='blocknotesmodule_edit(this)'>"+a['name']+"</a>";
	 var td = r.insertCell(-1);
	 td.style.textAlign='center'; td.style.verticalAlign='middle';
	 td.innerHTML = "<img src='"+ABSOLUTE_URL+"var/desktop/modules/blocknotes/img/delete.png' onclick='blocknotesmodule_delete(this)'/>";
	}

 sh.sendCommand("gframe -f blocknotes/new");
}

function blocknotesmodule_edit(aObj)
{
 var r = aObj.parentNode.parentNode;
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 aObj.innerHTML = a['name'];
	}
 sh.sendCommand("gframe -f blocknotes/edit -params `id="+r.id+"`");
}

function blocknotesmodule_delete(img)
{
 var tb = document.getElementById('blocknoteslist');
 var r = img.parentNode.parentNode;
 if(!confirm("Sei sicuro di voler eliminare questo appunto?"))
  return;

 var sh = new GShell();
 sh.OnOutput = function(){tb.deleteRow(r.rowIndex);}
 sh.sendCommand("dynarc delete-item -ap blocknotes -id '"+r.id+"'");
}


