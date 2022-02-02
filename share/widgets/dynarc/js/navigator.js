/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 12-01-2013
 #PACKAGE: dynarc-gui
 #DESCRIPTION: Navigator for Dynarc
 #VERSION: 2.1beta
 #CHANGELOG: 12-01-2013 : Aggiunto actionPerms.
			 21-01-2012 : Removed "Map View"
			 06-09-2011 : Sistemazioni varie.
			 23-05-2011 : bug fix in _returnSelected function.
 #TODO:
 
*/

var ACT_SELITEMS = new Array();
var ACT_LASTACT = null;
var PLUGINS_FUNCTIONS = new Array();
var LAYER_MAP = null;

$(function () { 
	$("#tree_div").tree({
	 callback : {
	  onmove : function(node,refnode,type,treeobj,rb){
		 var catId = $(node).attr('id');
		 var refId = $(refnode).attr('id');
		 var sh = new GShell();
		 sh.OnError = function(e,s){alert(s);}
		 switch(type)
		 {
		  case 'inside' : sh.sendCommand("dynarc cat-move -ap `"+ARCHIVE_PREFIX+"` -id "+catId+" -into "+refId); break;
		  case 'before' : sh.sendCommand("dynarc cat-move -ap `"+ARCHIVE_PREFIX+"` -id "+catId+" -before "+refId); break;
		  case 'after' : sh.sendCommand("dynarc cat-move -ap `"+ARCHIVE_PREFIX+"` -id "+catId+" -after "+refId); break;
		 }
		}, 
	  onselect : function(node,treeobj){
		 document.getElementById('search').value = "";
 		 var catId = $(node).attr('id');
		 var sh = new GShell();
		 sh.OnOutput = function(o,a){
			 IN_TRASH = false;
			 document.getElementById('trashlist').style.display='none';
			 document.getElementById('resultlist').style.display='';
			 MainMenu.UL.style.display = "";
			 CATID = catId;
			 _updateList(a);
			 _updatePathway(catId);
			 if(a['catinfo'])
			 {
			  CATNAME = html_entity_decode(a['catinfo']['name']);
			  document.getElementById('catname').innerHTML = i18n['Category']+" "+html_entity_decode(a['catinfo']['name']);
			  document.getElementById('catname').title = "CAT.ID: "+a['catinfo']['id'];
			 }
			}
		 sh.sendCommand("dynarc item-list -ap `"+ARCHIVE_PREFIX+"` -cat "+catId+" -extget labels"+(ARCH_EXTENSIONS ? ","+ARCH_EXTENSIONS : "")+" --order-by '"+ORDERBY+"' -limit 10 --return-cat-info --return-serp-info");
		}
	 }
	});
});

function bodyOnLoad()
{
 var mytree = jQuery.tree.reference("#tree_div");
 if(CATID != 0)
  $.tree.focused().select_branch("#"+CATID);
}

function _updateList(a)
{
 if(IN_TRASH)
  return _updateTrashList(a);
 var sel = document.getElementById('visualmode');
 if(sel.value == "map")
 {
  LAYER_MAP.reload("ap="+ARCHIVE_PREFIX+"&catid="+CATID);
  return;
 }
 TB.empty();

 if(!a || !a['items'])
 {
  SERP.O.style.display='none';
  document.getElementById('pagenum').innerHTML = "0-0";
  document.getElementById('pagetot').innerHTML = "0";
  return;
 }
 var d = new Date();

 var extensions = ARCH_EXTENSIONS.split(",");

 for(var c=0; c < a['items'].length; c++)
 {
  var itm = a['items'][c];
  var r = TB.insertRow(-1);
  r.id = itm['id'];
  r.title = "ID: "+itm['id'];
  r.getCell('column-name').innerHTML = "<a href='#' onclick='_show("+itm['id']+")'>"+itm['name']+"</a>";
  d.setTime(parseFloat(itm['ctime'])*1000);
  r.getCell('column-ctime').innerHTML = d.printf('d/m/Y');
  
  r.getCell('column-publish').innerHTML = "<a href='#' onclick='_publish(this,"+itm['id']+","+(itm['published']==1 ? "false" : "true")+")'><img src='"+ABSOLUTE_URL+"share/widgets/dynarc/img/"+(itm['published']==1 ? "published.gif" : "unpublished.gif")+"' border='0'/ ></a>";
  r.getCell('column-buttons-edit').innerHTML = "<a href='#' onclick='_edit("+itm['id']+")'><img src='"+ABSOLUTE_URL+"share/widgets/dynarc/img/edit.gif' border='0'/ ></a> <a href='#' onclick='_delete("+itm['id']+")'><img src='"+ABSOLUTE_URL+"share/widgets/dynarc/img/delete.gif' border='0'/ ></a>";

  if(PLUGINS_FUNCTIONS['iteminfo'] && PLUGINS_FUNCTIONS['iteminfo'].injectRow)
   PLUGINS_FUNCTIONS['iteminfo'].injectRow(r,itm,ARCHIVE_PREFIX);
  
  if(extensions && extensions.length)
  {
   for(var i=0; i < extensions.length; i++)
   {
	if(PLUGINS_FUNCTIONS[extensions[i]] && PLUGINS_FUNCTIONS[extensions[i]].injectRow)
	 PLUGINS_FUNCTIONS[extensions[i]].injectRow(r,itm,ARCHIVE_PREFIX);
   }
  }
 }
 if(a['serpinfo'])
 {
  SERP.Update(a['count'],a['serpinfo']['resultsperpage'],a['serpinfo']['currentpage']-1);
  SERP.O.style.display='';
  var from = parseFloat(a['serpinfo']['datafrom'])+1;
  var to = (from-1) + parseFloat(a['serpinfo']['resultsperpage']);
  if(to > a['count'])
   to = a['count'];
  document.getElementById('pagenum').innerHTML = from+"-"+to;
  document.getElementById('pagetot').innerHTML = a['count'];
 }
}

function _updateTrashList(a)
{
 TBTRASH.empty();
 if(!a || (!a['categories'] && !a['items']))
 {
  SERP.O.style.display='none';
  document.getElementById('pagenum').innerHTML = "0-0";
  document.getElementById('pagetot').innerHTML = "0";
  return;
 }
 var d = new Date();
 if(a['categories'])
 {
  for(var c=0; c < a['categories'].length; c++)
  {
   var itm = a['categories'][c];
   var r = TBTRASH.insertRow(-1);
   r.id = itm['id'];
   r.title = "ID: "+itm['id'];
   r.isCategory = true;
   r.cells[1].innerHTML = "<img src='"+BASE_PATH+"share/widgets/dynarc/img/mini-folder.gif'/ >";
   r.cells[2].innerHTML = "<a href='#'>"+itm['name']+"</a>";
   d.setTime(parseFloat(itm['ctime'])*1000);
   r.cells[3].innerHTML = d.printf('d/m/Y');
  }
 }
 if(a['items'])
 {
  for(var c=0; c < a['items'].length; c++)
  {
   var itm = a['items'][c];
   var r = TBTRASH.insertRow(-1);
   r.id = itm['id'];
   r.title = "ID: "+itm['id'];
   r.isCategory = false;
   r.cells[1].innerHTML = "<img src='"+BASE_PATH+"share/widgets/dynarc/img/mini-item.gif'/ >";
   r.cells[2].innerHTML = "<a href='#'>"+itm['name']+"</a>";
   d.setTime(parseFloat(itm['ctime'])*1000);
   r.cells[3].innerHTML = d.printf('d/m/Y');
  }
 }
 
 if(a['serpinfo'])
 {
  SERP.Update(a['count'],a['serpinfo']['resultsperpage'],a['serpinfo']['currentpage']-1);
  SERP.O.style.display='';
  var from = parseFloat(a['serpinfo']['datafrom'])+1;
  var to = (from-1) + parseFloat(a['serpinfo']['resultsperpage']);
  if(to > a['count'])
   to = a['count'];
  document.getElementById('pagenum').innerHTML = from+"-"+to;
  document.getElementById('pagetot').innerHTML = a['count'];
 }
}

function _updatePathway(catId)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a)
	  return;
	 var path = "";
	 if(a['pathway'])
	 {
	  for(var c=0; c < a['pathway'].length; c++)
	   path+= "<a href='#' onclick='_selectCat("+a['pathway'][c]['id']+",true)'>"+html_entity_decode(a['pathway'][c]['name'])+"</a> &rarr; ";
	 }
	 document.getElementById('pathway').innerHTML = path+"<span class='selected'>"+html_entity_decode(a['name'])+"</span>";
	}
 sh.sendCommand("dynarc cat-info -ap `"+ARCHIVE_PREFIX+"` --include-path -id "+catId);
}

function _selectCat(catId, fromPathway)
{
 IN_TRASH = false;
 document.getElementById('trashlist').style.display='none';
 document.getElementById('resultlist').style.display='';
 MainMenu.UL.style.display = "";

 var mytree = jQuery.tree.reference("#tree_div");
 if(!catId)
 {
  if(mytree.selected)
   mytree.deselect_branch($(mytree.selected));
  document.getElementById('catname').innerHTML = i18n['Elements out of folders'];
  document.getElementById('pathway').innerHTML = "&nbsp;";
  CATNAME = "";
 }
 if(fromPathway)
 {
  if(mytree.selected)
  {
   $.tree.focused().select_branch("#"+catId);
   return;
  }
  else
   _selectCat(0);
 }

 CATID = catId;

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 _updateList(a);
	}
 sh.sendCommand("dynarc item-list -ap `"+ARCHIVE_PREFIX+"` -cat "+CATID+" -extget labels"+(ARCH_EXTENSIONS ? ","+ARCH_EXTENSIONS : "")+" -limit 10 --return-serp-info");
}

function _publish(a,id,publish)
{
 var sh = new GShell();
 sh.OnOutput = function(){
	 a.innerHTML = "<img src='"+BASE_PATH+"share/widgets/dynarc/img/"+(publish ? "published.gif" : "unpublished.gif")+"' border='0'/ >";
	 a.onclick = function(){_publish(this,id,publish ? false : true);}
	}
 sh.OnPreOutput = function(){}
 sh.sendCommand("dynarc edit-item -ap `"+ARCHIVE_PREFIX+"` -id "+id+" "+(publish ? "--publish" : "--unpublish"));
}

function _update(pg)
{
 var _start = isNaN(pg) ? SERP.CurrentPage * SERP.ResultsPerPage : pg * SERP.ResultsPerPage;
 var qry = "";

 if(document.getElementById('search').value)
 {
  var s = htmlentities(document.getElementById('search').value);
  if(!IN_TRASH)
   qry = " -where \"(name LIKE '%"+s+"%' OR description LIKE '%"+s+"%' OR keywords LIKE '%"+s+"%')\"";
  else
   qry = " -where \"(name LIKE '%"+s+"%' OR description LIKE '%"+s+"%')\"";
 }
 else if(!IN_TRASH)
  qry = " -cat "+CATID;

 var sh = new GShell();
 sh.OnOutput = function(o,a){_updateList(a);}
 if(IN_TRASH)
  sh.sendCommand("dynarc trash list -ap `"+ARCHIVE_PREFIX+"`"+qry+" --return-serp-info --order-by '"+ORDERBY+"' -limit "+_start+","+SERP.ResultsPerPage);
 else
  sh.sendCommand("dynarc item-list -ap `"+ARCHIVE_PREFIX+"`"+qry+" -extget labels"+(ARCH_EXTENSIONS ? ","+ARCH_EXTENSIONS : "")+" --return-serp-info --order-by '"+ORDERBY+"' -limit "+_start+","+SERP.ResultsPerPage);
}

function _delete(id)
{
 if(!confirm(i18n['Are you sure you want to delete this document?']))
  return;
 var sh = new GShell();
 sh.OnOutput = function(){
	 _update();
	}
 sh.sendCommand("dynarc delete-item -ap `"+ARCHIVE_PREFIX+"` -id "+id);
}

function _deleteSelected(forever)
{
 var catq = "";
 var itmq = "";

 if(IN_TRASH)
  var tb = TBTRASH;
 else
  var tb = TB;

 var sel = tb.getSelectedRows();
 for(var c=0; c < sel.length; c++)
 {
  if(sel[c].isCategory)
   catq+= " -id "+sel[c].id;
  else
   itmq+= " -id "+sel[c].id;
 }
 if(catq=="" && itmq=="")
 {
  alert(i18n['You must select at least one element']);
  return;
 }
 if(!confirm(i18n['Are you sure you want to delete selected items?']))
  return;
 
 var sh = new GShell();
 sh.OnOutput = function(){
     _update();
	}
 var qry = "";
 if(itmq!="")
  qry = "dynarc delete-item -ap `"+ARCHIVE_PREFIX+"`"+itmq+(forever ? " -r" : "");
 if(catq!="")
  qry+= (itmq ? " && " : "")+"dynarc delete-cat -ap `"+ARCHIVE_PREFIX+"`"+catq+(forever ? " -r" : "");
 sh.sendCommand(qry);
}

function _orderby(a)
{
 var __orderMethod = "";
 var img = document.getElementById('orderby_arrow');
 if(img.parentNode == a)
 {
  if(img.src.substr(img.src.length-19,19) == "documents/img/darrow.png")
  {
   img.src = BASE_PATH+"share/widgets/dynarc/img/uarrow.png";
   __orderMethod = "ASC";
  }
  else
  {
   img.src = BASE_PATH+"share/widgets/dynarc/img/darrow.png";
   __orderMethod = "DESC";
  }
 }
 else
 {
  img.src = BASE_PATH+"share/widgets/dynarc/img/uarrow.png";
  __orderMethod = "ASC";
  a.appendChild(img);
 }
 var __orderBy = a.id.substr(8,a.id.length);
 
 ORDERBY = __orderBy+" "+__orderMethod;
 _update(0);
}

function _close()
{
 gframe_close();
}

function _search()
{
 if(!document.getElementById('search').value && !IN_TRASH)
  return _selectCat(CATID,true);
 document.getElementById('catname').innerHTML = i18n["Search for %s into all documents"].replace("%s", "<b><i>"+document.getElementById('search').value+"</i></b>");
 document.getElementById('pathway').innerHTML = "&nbsp;";
 _update(1);
}

function _newCat(catId, callback)
{
 var nm = prompt(i18n['Enter the name of the new category']);
 if(!nm)
  return;
 nm = htmlentities(nm,"E_QUOT");
 var mytree = jQuery.tree.reference("#tree_div");
 if(!catId)
 {
  if(mytree.selected)
  {
   var catId = $(mytree.selected).attr('id');
   var node = $(mytree.selected);
  }
 }
 else
 {
  var node = $("#"+catId);
 }

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(mytree.selected && (node == mytree.selected))
	  mytree.create({ data: nm, attributes : { id : a['id'] }});
	 else if(node)
	  mytree.create({ data: nm, attributes : { id : a['id'] }}, node);
	 else
	  mytree.create({ data: nm, attributes : { id : a['id'] }},-1);
	 if(callback)
	  callback(a);
	}
 sh.sendCommand("dynarc new-cat -ap `"+ARCHIVE_PREFIX+"` -name '"+htmlentities(nm,"E_QUOT")+"'"+(catId ? " -parent "+catId : ""));
}

function _editCat()
{
 var mytree = jQuery.tree.reference("#tree_div");
 if(!mytree.selected)
 {
  alert(i18n['You must select a category']);
  return;
 }
 var nm = prompt(i18n['Rename this category'], mytree.get_text($(mytree.selected)));
 if(!nm)
  return;

 var sh = new GShell();
 //sh.OnPreOutput = function(){} /* Enable pre-output for some interfaces */
 sh.OnOutput = function(o,a){
	 $.tree.focused().rename(null,nm);
	 document.getElementById('catname').innerHTML = i18n['Category']+" "+html_entity_decode(nm);
	 document.getElementById('pathway').getElementsByTagName('SPAN')[0].innerHTML = html_entity_decode(nm);
	}
 sh.OnError = function(e,s){alert(s);}
 sh.sendCommand("dynarc edit-cat -ap `"+ARCHIVE_PREFIX+"` -id "+$(mytree.selected).attr('id')+" -name '"+htmlentities(nm,"E_QUOT")+"'");
}

function _deleteCat()
{
 var mytree = jQuery.tree.reference("#tree_div");
 if(!mytree.selected)
 {
  alert(i18n['You must select a category']);
  return;
 }
 if(!confirm(i18n['Are you sure you want to delete the category %s ?'].replace("%s",mytree.get_text($(mytree.selected)))))
  return;

 var sh = new GShell();
 sh.OnPreOutput = function(){} /* Enable pre-output for some interfaces */
 sh.OnOutput = function(o,a){
	 $.tree.focused().remove();
	}
 sh.OnError = function(e,s){alert(s);}
 sh.sendCommand("dynarc delete-cat -ap `"+ARCHIVE_PREFIX+"` --return-cat-info -id "+$(mytree.selected).attr('id'));
}

function _permsCat()
{
 var mytree = jQuery.tree.reference("#tree_div");
 if(!mytree.selected)
 {
  alert(i18n['You must select a category']);
  return;
 }
 var sh = new GShell();
 sh.sendCommand("gframe -f dynarc.categoryPermissions -params 'archiveprefix="+ARCHIVE_PREFIX+"&cat="+$(mytree.selected).attr('id')+"'");
}

function _showTrash()
{
 document.getElementById('catname').innerHTML = i18n['Trash'];
 document.getElementById('pathway').innerHTML = "&nbsp;";
 IN_TRASH = true;
 document.getElementById('resultlist').style.display='none';
 document.getElementById('trashlist').style.display='';
 MainMenu.UL.style.display = "none";
 _update(1);
}

function _restoreSelected()
{
 var q = "";
 var sel = TBTRASH.getSelectedRows();
 for(var c=0; c < sel.length; c++)
 {
  if(sel[c].isCategory)
   q+= " -cat "+sel[c].id;
  else
   q+= " -id "+sel[c].id;
 }
 if(q=="")
 {
  alert(i18n['You must select at least one element']);
  return;
 }
 
 var sh = new GShell();
 sh.OnOutput = function(){
	 document.location.reload();
	}
 sh.sendCommand("dynarc trash restore -ap `"+ARCHIVE_PREFIX+"`"+q);
}

function _emptyTrash()
{
 if(!confirm(i18n['Are you sure you want to empty the trash?']))
  return;
 var sh = new GShell();
 sh.OnOutput = function(){
     _update();
	}
 sh.sendCommand("dynarc trash empty -ap `"+ARCHIVE_PREFIX+"`");
}

function _returnSelected()
{
 var sel = new Array();
 var list = TB.getSelectedRows();
 for(var c=0; c < list.length; c++)
  sel.push(list[c].id);
 if(!sel.length)
  return alert(i18n['You have not selected any documents']);
 gframe_close(sel.length+" items selected.",sel);
}

function _importFromFile()
{
 var sh = new GShell();
 sh.OnOutput = function(){document.location.reload();}
 sh.sendCommand("gframe -f dynarc.import -params ap="+ARCHIVE_PREFIX+(CATID ? "&cat="+CATID : "")+" --fullspace");
}

function _importFromExcel()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var fileName = a['files'][0]['fullname'];

	 var sh2 = new GShell();
	 sh2.OnFinish = function(){document.location.reload();}
	 sh2.sendCommand("gframe -f excel/import -params `ap="+ARCHIVE_PREFIX+(CATID ? "&cat="+CATID : "")+"&file="+fileName+"`");
	}
 sh.sendCommand("gframe -f fileupload");

}

function _importFromArchive()
{
 alert("Funzione da implementare");
}

function _exportToFile()
{
 var sel = TB.getSelectedRows();
 var q = "";
 if(!sel.length)
 {
  if(!CATID)
  {
   if(!confirm(i18n['Want to export the entire archive?']))
	return;
   q = "&exportall";
  }
  else
  {
   if(!confirm(i18n['Want to export the entire folder %s ?'].replace("%s",CATNAME)))
	return;
   q = "&cat="+CATID;
  }
 }
 else
 {
  q = "&id=";
  for(var c=0; c < sel.length; c++)
   q+= sel[c]['id']+",";
  q = q.substr(0,q.length-1);
 }

 var sh = new GShell();
 sh.sendCommand("gframe -f dynarc.export -params ap="+ARCHIVE_PREFIX+q+" --fullspace");
}

function _exportToArchive()
{
 alert(i18n['Function to implement']);
}

function _actionCopy()
{
 ACT_SELITEMS = TB.getSelectedRows();
 ACT_LASTACT = "clone";
 if(!ACT_SELITEMS.length)
  alert(i18n['You must select at least one element']);
}

function _actionCut()
{
 ACT_SELITEMS = TB.getSelectedRows();
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
  q+= " -id "+ACT_SELITEMS[c].id;

 var sh = new GShell();
 sh.OnOutput = function(){_update();}
 switch(ACT_LASTACT)
 {
  case 'clone' : sh.sendCommand("dynarc item-copy -ap `"+ARCHIVE_PREFIX+"`"+q+" -cat "+(CATID ? CATID : 0)); break;
  case 'move' : sh.sendCommand("dynarc item-move -ap `"+ARCHIVE_PREFIX+"`"+q+" -cat "+(CATID ? CATID : 0)); break;
 }
}

function _actionPerms()
{
 var selected = TB.getSelectedRows();
 if(!selected.length)
 {
  alert(i18n['You must select at least one element']);
  return;
 }

 var chmod = prompt("Imposta i permessi in modalità numerica. (Es: 640, 664, oppure 666 per rendere l'accessibilità a chiunque)");
 if(!chmod)
  return;

 var q = "";
 for(var c=0; c < selected.length; c++)
  q+= " -id "+selected[c].id;

 var sh = new GShell();
 sh.OnOutput = function(){_update();}
 sh.sendCommand("dynarc chmod -ap `"+ARCHIVE_PREFIX+"`"+chmod+q);
}

function _shInfo(cb,info)
{
}

function _shButtons(cb,btn)
{
 switch(btn)
 {
  case 'publish' : {
	 var idx = document.getElementById('column-publish').cellIndex;
	 TB.showColumn(idx,cb.checked);
	} break;
  case 'edit' : {
	 var idx = document.getElementById('column-buttons-edit').cellIndex;
	 TB.showColumn(idx,cb.checked);
	} break;
 }
}

function _shColumns(cb,col)
{
 if(col)
 {
  col = document.getElementById(col);
  if(col)
   TB.showColumn(col.cellIndex,cb.checked);
 }
}

function _visualmodeChange()
{
 var sel = document.getElementById('visualmode');
 switch(sel.value)
 {
  /*case 'map' : {
	 if(!LAYER_MAP)
	 {
	  LAYER_MAP = new Layer("simplemap","ap="+ARCHIVE_PREFIX+"&catid="+CATID, document.getElementById('mapview'), true, function(){
		 document.getElementById('listview').style.display='none';
		 document.getElementById('mapview').style.display='';
		 LAYER_MAP.OnNewNode = function(catId, callback){
			 _newCat(catId,callback);
			}
		 LAYER_MAP.OnNewItem = function(catId, callback){
			 _new(catId,callback);
			}
		});
	 }
	 else
	 {
	  LAYER_MAP.reload("ap="+ARCHIVE_PREFIX+"&catid="+CATID, function(){
	  	 document.getElementById('listview').style.display='none';
	  	 document.getElementById('mapview').style.display='';
		});
	 }
	} break;*/
  default : {
	 /*document.getElementById('mapview').style.display='none';*/
	 document.getElementById('listview').style.display='';
	} break;
 }
}


