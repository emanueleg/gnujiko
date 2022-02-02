/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 12-09-2016
 #PACKAGE: gnujiko-template
 #DESCRIPTION: Gnujiko Template - Attachments manager object.
 #VERSION: 2.1beta
 #CHANGELOG: 12-09-2016 : Bug fix su funzione remove.
 #TODO:
 
*/

function GLAttachments(ap, id, cat, obj)
{
 this.refAP = ap;
 this.refID = id;
 this.refCat = cat;
 this.label = null;

 if(obj)
 {
  var firstDiv = obj.getElementsByTagName('DIV')[0];
  if(firstDiv && (firstDiv.className == "attachments"))
   this.O = firstDiv;
  else if(obj.className == "attachments")
   this.O = obj;
  this.init();
 }

 /* EVENTS */
 this.OnUpload = null; /* function(item, file) */
 this.OnReload = null; /* function() */
 this.OnBeforeDelete = null; /* function(obj) */
 this.OnDelete = null; /* function() */
 this.OnEdit = null; /* function(obj) */
}
//-------------------------------------------------------------------------------------------------------------------//
GLAttachments.prototype.init = function()
{
 var list = this.O.getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
 {
  if(list[c].className != "attachment")
   continue;
  this.injectItem(list[c]);
 }

 this.label = this.O.getElementsByTagName('LABEL')[0];
 if(this.label)
  this.label.style.lineHeight = (this.label.offsetHeight-4)+"px";
}
//-------------------------------------------------------------------------------------------------------------------//
GLAttachments.prototype.upload = function(destPath)
{
 var oThis = this;
 var sh = new GShell();
 sh.btn = this;
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a || !a['files'])
	  return;
	 for(var c=0; c < a['files'].length; c++)
	  oThis.uploadFile(a['files'][c]);
	}
 sh.sendCommand("gframe -f fileupload"+(destPath ? " -params `destpath="+destPath+"`" : ""));
}
//-------------------------------------------------------------------------------------------------------------------//
GLAttachments.prototype.uploadFile = function(file)
{
 var oThis = this;
 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return;
	 var icon = (a['icons'] && a['icons']['size48x48']) ? a['icons']['size48x48'] : "share/mimetypes/48x48/file.png";
	 var item = oThis.addItem(a['id'], a['name'], a['url'], icon, a['type']);
	 if(oThis.OnUpload)
	  oThis.OnUpload(item, file);
	}
 sh.sendCommand("dynattachments add -ap '"+this.refAP+"'"+(this.refCat ? " -cat '"+this.refCat+"'" : " -refid '"+this.refID+"'")+" -name `"+file['name']+"` -url `"+file['fullname']+"`");
}
//-------------------------------------------------------------------------------------------------------------------//
GLAttachments.prototype.addItem = function(id,fileName, URL, icon, type)
{
 if(this.label) this.label.style.display = "none";
 var O = document.createElement('DIV');
 O.className = "attachment";
 if(type == "IMAGE")
  O.style.backgroundImage = "url('"+ABSOLUTE_URL+URL+"')";
 else
  O.style.backgroundImage = "url('"+ABSOLUTE_URL+icon+"')";
 O.setAttribute('filetype',type);
 O.setAttribute('attid',id);
 O.setAttribute('href',URL);
 O.id = "attachment-"+id;

 var html = "<div class='attachbuttons'>";
 html+= "<div class='attachbtnbg'></div>";
 html+= "<div class='attachbtncont'>";
 html+= "<img src='"+ABSOLUTE_URL+"var/templates/gnujiko/objects/attachments/img/download.png' style='float:left;margin-right:3px' title='Scarica'/>";
 html+= "<img src='"+ABSOLUTE_URL+"var/templates/gnujiko/objects/attachments/img/edit.png' style='float:left;margin-left:0px' title='Modifica'/>";
 html+= "<img src='"+ABSOLUTE_URL+"var/templates/gnujiko/objects/attachments/img/trash.png' style='float:right' title='Rimuovi'/>";
 html+= "</div>";
 html+= "</div>"; // eof - attachbuttons
 html+= "<div class='attachtitle' href='"+URL+"'>"+fileName+"</div>";
 //html+= "</div>"; // eof - attachment

 O.innerHTML = html;
 this.injectItem(O);
 this.O.appendChild(O);
 return O;
}
//-------------------------------------------------------------------------------------------------------------------//
GLAttachments.prototype.injectItem = function(obj)
{
 var oThisH = this;

 obj.downloadFile = function(){
	 if(this.getAttribute('filetype') == "WEB")
	  window.open(this.getAttribute('href'));
	 else
	  document.location.href = ABSOLUTE_URL+"getfile.php?file="+this.getAttribute('href');
	}

 obj.openLink = function(){
	 if(this.getAttribute('filetype') == "WEB")
	  window.open(this.getAttribute('href'));
	 else
	  window.open(ABSOLUTE_URL+this.getAttribute('href'));
	}

 obj.edit = function(){
	 var title = prompt("Rinomina",this.titleO.innerHTML);
	 if(!title) return;
	 this.titleO.innerHTML = title;
	 if(!this.getAttribute('attid')) return;
	 var oThis = this;
	 var sh = new GShell();
	 sh.OnError = function(err){alert(err);}
	 sh.OnOutput = function(o,a){
		 if(oThisH.OnEdit)
		  oThisH.OnEdit(obj);
		}
	 sh.sendCommand("dynattachments edit -id '"+this.getAttribute('attid')+"' -name `"+title+"`");
	}

 obj.remove = function(){
	 if(!confirm("Sei sicuro di voler rimuovere questo allegato?"))
	  return;
	 var oThis = this;
	 if(!this.getAttribute('attid'))
	 {
	  this.parentNode.removeChild(this);
	  if(oThisH.OnDelete) oThisH.OnDelete();
	  return;
	 }

	 var sh = new GShell();
	 sh.OnError = function(err){alert(err);}
	 sh.OnOutput = function(){
		 if(oThisH.OnBeforeDelete) oThisH.OnBeforeDelete(oThis);
		 oThis.parentNode.removeChild(oThis);
		 if(oThisH.OnDelete) oThisH.OnDelete();
		}
	 sh.sendCommand("dynattachments delete -id '"+this.getAttribute('attid')+"'");
	}

 var div = obj.getElementsByTagName('DIV')[0];
 div = div.getElementsByTagName('DIV')[1];
 var btns = div.getElementsByTagName('IMG');
 btns[0].O = obj; btns[1].O = obj; btns[2].O = obj;
 btns[0].onclick = function(){this.O.downloadFile();}
 btns[1].onclick = function(){this.O.edit();}
 btns[2].onclick = function(){this.O.remove();}

 obj.titleO = obj.getElementsByTagName('DIV')[3];
 obj.titleO.O = obj;
 obj.titleO.onclick = function(){this.O.openLink();}
}
//-------------------------------------------------------------------------------------------------------------------//
GLAttachments.prototype.getAttachments = function(retAsXML)
{
 var ret = retAsXML ? "" : new Array();
 var list = this.O.getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
 {
  if(list[c].className != "attachment")
   continue;
  var item = list[c];
  if(retAsXML)
  {
   ret+= "<item name=\""+item.titleO.textContent+"\" type=\""+item.getAttribute('filetype')+"\" id=\""+item.getAttribute('attid')+"\" url=\""+item.getAttribute('href')+"\"/>";
  }
  else
   ret.push(item.getAttribute('href'));
 }
 return ret;
}
//-------------------------------------------------------------------------------------------------------------------//
GLAttachments.prototype.empty = function()
{
 var list = this.O.getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
 {
  var div = list[c];
  if(div.className != "attachment")
   continue;
  div.parentNode.removeChild(div);
  c--;
 }
 if(this.label) this.label.style.display = "";
}
//-------------------------------------------------------------------------------------------------------------------//
GLAttachments.prototype.reload = function(refId, callback)
{
 if(refId) this.refID = refId;
 this.empty();

 var oThis = this;

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a || !a['items'])
	 {
	  if(oThis.OnReload) oThis.OnReload();
	  if(callback) return callback();
	  return;
	 }

	 for(var c=0; c < a['items'].length; c++)
	 {
	  var data = a['items'][c];
	  var icon = (data['icons'] && data['icons']['size48x48']) ? data['icons']['size48x48'] : "share/mimetypes/48x48/file.png";
	  var item = oThis.addItem(data['id'], data['name'], data['url'], icon, data['type']);
	 }
	 if(oThis.OnReload) oThis.OnReload();
	 if(callback) return callback();
	}

 sh.sendCommand("dynattachments list -ap '"+this.refAP+"'"+(this.refCat ? " -cat '"+this.refCat+"'" : " -refid '"+this.refID+"'"));
}
//-------------------------------------------------------------------------------------------------------------------//


