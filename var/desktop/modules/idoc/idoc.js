/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 30-05-2013
 #PACKAGE: idoc-module
 #DESCRIPTION: Interactive content module for Gnujiko Desktop.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

function idocmodule_load(modId)
{
 var moduleId = modId.replace("gjkdskmod-","");
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.getElementById(modId).data = a;
	}

 sh.sendCommand("desktop module-info -id "+moduleId);
}

function idocmodule_edit(modId)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 document.getElementById(modId+"-container").innerHTML = a['contents'];
	}

 sh.sendCommand("gframe -f desktop/htmleditor -params `modid="+modId.replace("gjkdskmod-","")+"&autosave=true`");
}

function idocmodule_runForm(modId)
{
 var data = document.getElementById(modId).data;
 if(!data)
  return;

 idocmodule_load_form(data['params']['form']['title'], data['params']['form']['width'], data['params']['form']['height'], data['params']['form']['layername'], data['params']['form']['arguments'], data['params']['form']['query']);
}

function idocmodule_load_form(title, width, height, formName, arguments, query)
{
 var f = new HackTVForm("bluewidget",width,height);
 var data = new Array(); 
 data['query'] = query;
 f.LoadLayer(formName,arguments,data);
 f.Show(title);
}


