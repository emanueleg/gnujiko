/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-11-2012
 #PACKAGE: filemanager
 #DESCRIPTION: Official Gnujiko File Manager
 #VERSION: 2.0beta
 #CHANGELOG: 05-11-2012 : Some bug fix.
 #DEPENDS:
 #TODO:
 
*/

var PATH = "";
var DIRNAME = "";
var ORDERBY = "name ASC";
var ACT_SELITEMS = new Array();
var ACT_LASTACT = null;

$(function () { 
	$("#tree_div").tree({
	 callback : {
	  onselect : function(node,treeobj){
 		 PATH = decodeFID($(node).attr('id'));
		 DIRNAME = treeobj.get_text(node);
		 document.getElementById('dirname').innerHTML = DIRNAME;
		 _update();
		}
	 }
	});
});

function generateFID(path)
{
 var ret = "";
 for(var c=0; c < path.length; c++)
 {
  var code = path.charCodeAt(c);
  var hex = code.toString(16);
  ret+= hex < 10 ? "0"+hex : hex;
 }
 return ret;
}
function decodeFID(fid)
{
 var str = "";
 var p = 0;
 while(p < fid.length)
 {
  str+= "%"+fid.substr(p,2);
  p+= 2;
 }
 str = unescape(str);
 return str;
}

function _selectDir(dirName, fid)
{
 var mytree = jQuery.tree.reference("#tree_div");
 if(mytree.selected)
  mytree.deselect_branch($(mytree.selected));
 if(!dirName)
 {
  document.getElementById('dirname').innerHTML = i18n['User folder'];
  document.getElementById('pathway').innerHTML = "&nbsp;";
  PATH = "";
  DIRNAME = "";
  _update();
 }
 else if(fid)
  mytree.select_branch("#"+fid);
}

function _update()
{
 if(document.getElementById('fmiframe'))
  document.getElementById('fmiframe').src = ABSOLUTE_URL+"share/widgets/filemanager/index.php?sessid="+SESSID+"&shellid="+SHELLID+"&filter="+FILTER+"&path="+PATH;
 else if(window.frames.length)
  window.frames[0].src = ABSOLUTE_URL+"share/widgets/filemanager/index.php?sessid="+SESSID+"&shellid="+SHELLID+"&filter="+FILTER+"&path="+PATH;
}

function bodyOnLoad()
{
 document.getElementById('iframespace').appendChild(document.getElementById('fmiframe'));
 document.getElementById('fmiframe').style.display = "";
 var fid = document.getElementById('path').value;
 if(fid)
 {
  var mytree = jQuery.tree.reference("#tree_div");
  if(mytree.selected)
   mytree.deselect_branch($(mytree.selected));
  mytree.select_branch("#"+fid);
 }
}

function iframelist_OnLoad()
{
 
}

function _newDir()
{
 var nm = prompt(i18n['Enter the new folder name']);
 if(!nm)
  return;

 var sh = new GShell();
 sh.OnOutput = function(){
	 var mytree = jQuery.tree.reference("#tree_div");
	 var fid = generateFID(PATH+"/"+nm);
	 var _name = generateFID(nm);
	 if(mytree.selected)
	  var node = mytree.create({ data: nm, attributes : { id : fid }});
	 else
	  var node = mytree.create({ data: nm, attributes : { id : fid }},-1);
	 _update();
	}
 sh.sendCommand("mkdir `"+PATH+"/"+nm+"`");
}

function _renameDir(fid,_name)
{
 var path = decodeFID(fid);
 var sName = decodeFID(_name);

 var nm = prompt(i18n['Rename folder'],sName);
 if(!nm)
  return;

 var basepath = path.substr(0,path.length-sName.length);

 var sh = new GShell();
 sh.OnOutput = function(){
	 document.location.reload();
	}
 sh.sendCommand("mv `"+path+"` `"+basepath+nm+"`");
}

function _deleteDir(fid, _name)
{
 if(!confirm(i18n['Are you sure you want to delete the folder %s ?'].replace("%s",decodeFID(_name))))
  return;
 var path = decodeFID(fid);
 var sh = new GShell();
 sh.OnOutput = function(){
	 var mytree = jQuery.tree.reference("#tree_div");
	 mytree.remove($("#"+fid));
	 _update();
	}
 sh.sendCommand("rm `"+path+"`");
}

function _deleteFile(fid, _name)
{
 if(!confirm(i18n['Are you sure you want to delete %s ?'].replace("%s",decodeFID(_name))))
  return;
 var path = decodeFID(fid);
 var sh = new GShell();
 sh.OnOutput = function(){_update();}
 sh.sendCommand("rm `"+path+"`");
}

function _deleteSelected(sel)
{
 if(!confirm(i18n['Are you sure you want to remove the selected?']))
  return;
 var q = "";
 for(var c=0; c < sel.length; c++)
  q+= " `"+decodeFID(sel[c].id)+"`";

 var sh = new GShell();
 sh.OnOutput = function(){
	 var mytree = jQuery.tree.reference("#tree_div");
	 for(var c=0; c < sel.length; c++)
	  mytree.remove($("#"+sel[c].id)); 
	 _update();
	}
 sh.sendCommand("rm"+q);
}

function _selectFile(fid,_name)
{
 var file = decodeFID(fid);
 var a = new Array();
 a['name'] = _name;
 a['url'] = file;
 gframe_close("File "+file+" selected.",a);
}

function _actionCopy(sel)
{
 ACT_SELITEMS = sel;
 ACT_LASTACT = "clone";
 if(!ACT_SELITEMS.length)
  alert(i18n['You must select at least one element']);
}

function _actionCut(sel)
{
 ACT_SELITEMS = sel;
 ACT_LASTACT = "move";
 if(!ACT_SELITEMS.length)
  alert(i18n['You must select at least one element']);
}

function _actionPaste()
{
 if(!ACT_SELITEMS.length)
  alert(i18n['Nothing to be pasted']);

 var q = "";
 for(var c=0; c < ACT_SELITEMS.length; c++)
  q+= " -s `"+decodeFID(ACT_SELITEMS[c].id)+"`";

 var sh = new GShell();
 sh.OnOutput = function(){_update();}
 switch(ACT_LASTACT)
 {
  case 'clone' : sh.sendCommand("cp -d `"+PATH+"`"+q); break;
  case 'move' : sh.sendCommand("mv -d `"+PATH+"`"+q); break;
 }
}

