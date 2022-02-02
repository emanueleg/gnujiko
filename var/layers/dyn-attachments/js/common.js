/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 20-11-2012
 #PACKAGE: dynarc-attachments-extension
 #DESCRIPTION: Attachments support for categories and items into archives managed by Dynarc.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

var attUpld = new GUploader(null,null,"dynattachments/");
document.getElementById('gupldspace').appendChild(attUpld.O);

// file upload //
attUpld.OnUpload = function(file){
	 var sh = new GShell();
	 sh.OnOutput = function(o,a){
		 var r = document.getElementById('attachmentstable').insertRow(1);
		 r.id = a['id'];
		 if(a['icons'] && a['icons']['size22x22'])
		  r.insertCell(-1).innerHTML = "<img src='"+BASE_PATH+a['icons']['size22x22']+"' style='margin-top:2px;' valign='top' align='left'/ >";
		 else
		  r.insertCell(-1).innerHTML = "<img src='"+BASE_PATH+"share/mimetypes/22x22/file.png' style='margin-top:2px;' valign='top' align='left'/ >";
		 r.cells[0].innerHTML = r.cells[0].innerHTML+"&nbsp;<a href='"+BASE_PATH+a['url']+"' target='blank' style='line-height:1.5em;'>"+a['name']+"</a>";
		 if(a['type'] == "AUDIO")
		 {
		  var obj = "<object type='application/x-shockwave-flash' data='"+BASE_PATH+"var/layers/dyn-attachments/players/player_mp3_maxi.swf' width='200' height='20'>";
		  obj+= "<param name='movie' value='"+BASE_PATH+"var/layers/dyn-attachments/players/player_mp3_maxi.swf'/ >";
		  obj+= "<param name='bgcolor' value='#ffffff' / >";
		  obj+= "<param name='FlashVars' value='mp3="+(a['type'] != 'WEB' ? BASE_PATH : '')+a['url']+"&amp;showstop=1&amp;showinfo=1&amp;showvolume=1&amp;loadingcolor=5cfff5&amp;bgcolor1=cccccc&amp;bgcolor2=858585'/ >";
		  obj+= "</object>";
		  r.insertCell(-1).innerHTML = obj;
		 }
		 else
		  r.insertCell(-1).innerHTML = "&nbsp;";
		 r.insertCell(-1).innerHTML = a['humansize'] ? a['humansize'] : "&nbsp;";
		 var d = new Date(parseFloat(a['ctime'])*1000);
		 r.insertCell(-1).innerHTML = d.printf('d-M-Y');
		 r.insertCell(-1).innerHTML = "<img src='"+BASE_PATH+"var/layers/dyn-attachments/img/edit.gif' style='cursor:pointer;' onclick='editAttachment("+a['id']+")'/ >&nbsp;<img src='"+BASE_PATH+"var/layers/dyn-attachments/img/delete.gif' style='cursor:pointer;' onclick='deleteAttachment("+a['id']+")'/ >";
		 r.cells[2].style.textAlign='center';
		 r.cells[3].style.textAlign='center';
		 r.cells[4].style.textAlign='center';
		}
	 if(CAT_ID)
	  sh.sendCommand("dynattachments add -ap '"+ARCHIVE_PREFIX+"' -cat "+CAT_ID+" -name '"+file['name']+"' -url '"+file['fullname']+"'");
	 else
	  sh.sendCommand("dynattachments add -ap '"+ARCHIVE_PREFIX+"' -refid "+ID+" -name '"+file['name']+"' -url '"+file['fullname']+"'");
	}
function insertFromURL()
{
 var url = document.getElementById('attachurl').value;
 if(!url)
 {
  alert("Inserisci un indirizzo valido");
  return;
 }
 url = "http://"+url.replace('http://','');

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 var r = document.getElementById('attachmentstable').insertRow(1);
	 r.id = a['id'];
	 if(a['icons'] && a['icons']['size22x22'])
	  r.insertCell(-1).innerHTML = "<img src='"+BASE_PATH+a['icons']['size22x22']+"' style='margin-top:2px;' valign='top' align='left'/ >";
	 else
	  r.insertCell(-1).innerHTML = "<img src='"+BASE_PATH+"share/mimetypes/22x22/file.png' style='margin-top:2px;' valign='top' align='left'/ >";
	 r.cells[0].innerHTML = r.cells[0].innerHTML+"&nbsp;<a href='"+a['url']+"' target='blank' style='line-height:1.5em;'>"+a['name']+"</a>";
	 r.insertCell(-1).innerHTML = "&nbsp;";
	 r.insertCell(-1).innerHTML = a['humansize'] ? a['humansize'] : "&nbsp;";
	 var d = new Date(parseFloat(a['ctime'])*1000);
	 r.insertCell(-1).innerHTML = d.printf('d-M-Y');
	 r.insertCell(-1).innerHTML = "<img src='"+BASE_PATH+"var/layers/dyn-attachments/img/edit.gif' style='cursor:pointer;' onclick='editAttachment("+a['id']+")'/ >&nbsp;<img src='"+BASE_PATH+"var/layers/dyn-attachments/img/delete.gif' style='cursor:pointer;' onclick='deleteAttachment("+a['id']+")'/ >";
	 r.cells[2].style.textAlign='center';
	 r.cells[3].style.textAlign='center';
	 r.cells[4].style.textAlign='center';
	 document.getElementById('attachurl').value = "";
	}
 sh.sendCommand("dynattachments add -ap '"+ARCHIVE_PREFIX+"' -refid "+ID+" -name '"+url+"' -url '"+url+"'");
}

function _selectFromServer(userpath)
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a)
	  return;
	 var sh2 = new GShell();
	 sh2.OnOutput = function(o,a){
		 if(!a)
		  return;
		 var r = document.getElementById('attachmentstable').insertRow(1);
		 r.id = a['id'];
		 if(a['icons'] && a['icons']['size22x22'])
		  r.insertCell(-1).innerHTML = "<img src='"+BASE_PATH+a['icons']['size22x22']+"' style='margin-top:2px;' valign='top' align='left'/ >";
		 else
		  r.insertCell(-1).innerHTML = "<img src='"+BASE_PATH+"share/mimetypes/22x22/file.png' style='margin-top:2px;' valign='top' align='left'/ >";
		 r.cells[0].innerHTML = r.cells[0].innerHTML+"&nbsp;<a href='"+BASE_PATH+a['url']+"' target='blank' style='line-height:1.5em;'>"+a['name']+"</a>";
		 if(a['type'] == "AUDIO")
		 {
		  var obj = "<object type='application/x-shockwave-flash' data='"+BASE_PATH+"var/layers/dyn-attachments/players/player_mp3_maxi.swf' width='200' height='20'>";
		  obj+= "<param name='movie' value='"+BASE_PATH+"var/layers/dyn-attachments/players/player_mp3_maxi.swf'/ >";
		  obj+= "<param name='bgcolor' value='#ffffff' / >";
		  obj+= "<param name='FlashVars' value='mp3="+(a['type'] != 'WEB' ? BASE_PATH : '')+a['url']+"&amp;showstop=1&amp;showinfo=1&amp;showvolume=1&amp;loadingcolor=5cfff5&amp;bgcolor1=cccccc&amp;bgcolor2=858585'/ >";
		  obj+= "</object>";
		  r.insertCell(-1).innerHTML = obj;
		 }
		 else
		  r.insertCell(-1).innerHTML = "&nbsp;";
		 r.insertCell(-1).innerHTML = a['humansize'] ? a['humansize'] : "&nbsp;";
		 var d = new Date(parseFloat(a['ctime'])*1000);
		 r.insertCell(-1).innerHTML = d.printf('d-M-Y');
		 r.insertCell(-1).innerHTML = "<img src='"+BASE_PATH+"var/layers/dyn-attachments/img/edit.gif' style='cursor:pointer;' onclick='editAttachment("+a['id']+")'/ >&nbsp;<img src='"+BASE_PATH+"var/layers/dyn-attachments/img/delete.gif' style='cursor:pointer;' onclick='deleteAttachment("+a['id']+")'/ >";
		 r.cells[2].style.textAlign='center';
		 r.cells[3].style.textAlign='center';
		 r.cells[4].style.textAlign='center';
		}
	 sh2.sendCommand("dynattachments add -ap '"+ARCHIVE_PREFIX+"' -refid "+ID+" -name '"+a['name']+"' -url '"+userpath+a['url']+"'");
	}
 sh.sendCommand("gframe -f filemanager");
}

function downloadAllAttachments()
{
 var sh = new GShell();
 sh.OnOutput = function(o,a){alert(o); document.location.href = BASE_PATH+a['filename'];}
 sh.sendCommand("dynattachments download -ap '"+ARCHIVE_PREFIX+"' -refid "+ID+" -all -zip");
}

//-------------------------------------------------------------------------------------------------------------------//

var activeAttachmentsForm = null;

function editAttachment(id)
{
 var div = document.createElement('DIV');
 div.className = "editform";
 div.style.visibility='hidden';
 _showScreenMask();
 document.body.appendChild(div);
 div.style.left =_getScreenWidth()/2-(div.offsetWidth/2);
 div.style.top = _getScreenHeight()/2-(div.offsetHeight/2);
 div.style.visibility='';
 hideObjectApps();
 NewLayer("dyn-attachments/forms","formtype=editatt&id="+id,div);
 activeAttachmentsForm = div;
}

function deleteAttachment(id)
{
 if(!confirm("Sei sicuro di voler eliminare questo allegato?"))
  return;
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 var r = tbGetRowById(id);
	 document.getElementById('attachmentstable').deleteRow(r.rowIndex);
	}
 sh.sendCommand("dynattachments delete -id "+id+" -r");
}

function saveAttachment(id)
{
 var sh = new GShell();
 var nm = htmlentities(document.getElementById('edatt_'+id+'_name').value,"ENT_QUOT");
 var ty = document.getElementById('edatt_'+id+'_type').value;
 var kw = htmlentities(document.getElementById('edatt_'+id+'_keywords').value,"ENT_QUOT");
 var pu = document.getElementById('edatt_'+id+'_published').checked;
 var de = htmlentities(document.getElementById('edatt_'+id+'_desc').value,"ENT_QUOT");
 var url = document.getElementById('edatt_'+id+'_url');
 if(url)
  url = url.value; 

 sh.OnOutput = function(o,a){
	 attachmentsFormClose();
	 var r = tbGetRowById(id);
	 r.cells[0].getElementsByTagName('A')[0].innerHTML = nm;
	 if(url)
	  r.cells[0].getElementsByTagName('A')[0].href = url;
	}
 sh.sendCommand("dynattachments edit -id "+id+" -name '"+nm+"' -type '"+ty+"' -keyw '"+kw+"' -desc '"+de+"'"+(pu ? " -publish" : " -unpublish")+(url ? " -url '"+url+"'" : ""));
}

function attachmentsFormClose()
{
 if(activeAttachmentsForm)
 {
  document.body.removeChild(activeAttachmentsForm);
  _hideScreenMask();
 }
 showObjectApps();
}

function tbGetRowById(id)
{
 var tb = document.getElementById('attachmentstable');
 for(var c=0; c < tb.rows.length; c++)
 {
  if(tb.rows[c].id == id)
   return tb.rows[c];
 }
 return null;
}

function hideObjectApps()
{
 var tb = document.getElementById('attachmentstable');
 for(var c=0; c < tb.rows.length; c++)
 {
  tb.rows[c].cells[1].style.visibility='hidden';
 }
}

function showObjectApps()
{
 var tb = document.getElementById('attachmentstable');
 for(var c=0; c < tb.rows.length; c++)
 {
  tb.rows[c].cells[1].style.visibility='';
 }
}
