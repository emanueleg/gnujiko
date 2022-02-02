/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-07-2013
 #PACKAGE: guploader
 #DESCRIPTION: Gnujiko uploader utility
 #VERSION: 2.2beta
 #CHANGELOG: 23-07-2013 : Bug fix.
			 17-05-2013 : Aggiunto allow file types.
 #TODO:
 
*/

function GUploader(iSubmitButton, _maxFileSize, _baseDir, _filename, _absPath, _btnsize, _allowFileTypes)
{
 var oThis = this;
 this.UploadedFile = new Array();
 this.MaxFileSize = _maxFileSize ? _maxFileSize : (128*1024)*1024;
 this.BaseDir = _baseDir ? _baseDir : "";
 this.FileName = _filename ? _filename : null;
 this.AbsPath = _absPath ? _absPath : null;
 this.AllowFileTypes = _allowFileTypes ? _allowFileTypes : "";

 this.O = document.createElement('DIV');
 this.browseEdit = document.createElement('INPUT'); this.browseEdit.type='file'; this.browseEdit.name='upldFile';
 if(_btnsize)
  this.browseEdit.size = _btnsize;

 if(!iSubmitButton)
  this.browseEdit.onchange = function(){if(oThis.OnBeginUpload)oThis.OnBeginUpload();oThis.form.submit();};
 else
  iSubmitButton.onclick = function(){if(oThis.OnBeginUpload)oThis.OnBeginUpload();oThis.form.submit();};

 this.iFrame = document.createElement('IFRAME');
 this.iFrame.className = "GUploader_iframe";
 this.iFrame.src = BASE_PATH+"var/objects/guploader/blank.html";
 this.iFrame.name = this.iFrame.id = this.iFrame.uniqueID ? this.iFrame.uniqueID : "GUPLOADER_IFRAME_"+window.frames.length;
 this.iFrame.style.width='1px';
 this.iFrame.style.height='1px';
 this.O.appendChild(this.iFrame);

 this.form = document.createElement('FORM');
 this.form.method="POST";
 this.form.enctype="multipart/form-data";
 this.form.encoding="multipart/form-data";
 this.form.action=BASE_PATH+"var/objects/guploader/upload.php";
 this.form.target=this.iFrame.name;
 this.form.id = this.form.uniqueID ? this.form.uniqueID : "GUPLOADER_FORM_"+window.frames.length;
 this.form.style.width='1px';
 this.form.style.height='1px';

 this.form.appendChild(this.createHidden('MAX_FILE_SIZE',this.MaxFileSize));
 this.form.appendChild(this.createHidden('frmId',this.form.id));
 this.form.appendChild(this.createHidden('allowfiletypes',this.AllowFileTypes));
 this._iBaseDir = this.createHidden('basedir',this.BaseDir);
 this.form.appendChild(this._iBaseDir);
 this._iFileName = this.createHidden('filename',this.FileName);
 this.form.appendChild(this._iFileName);
 this._iAbsPath = this.createHidden('abspath',this.AbsPath);
 this.form.appendChild(this._iAbsPath);
 this.form.appendChild(this.browseEdit);
 
 this.O.appendChild(this.form);

 this.form.uploadResponse = function(res,err,msg){
	 if(!res)
	 {
	  if(!oThis.OnError)
	  {
	   if(msg)
		alert(msg);
	   else
	   {
	    switch(err)
	    {
	     case 'BAD_EXTENSION' : alert('You cannot upload .'+this.FileExtension+' files'); break;
		 case 'BAD_SIZE' : alert('Your file size is too long. max '+oThis.MaxFileSize); break;
		 case 'BAD_FILE' : alert('Invalid or corrupted file'); break;
		 case 'UNKNOWN_ERROR' : alert('Cannot upload file. Unknown error.'); break;
		 default : alert(err); break;
	    }
	   }
	  }
	  else
	   oThis.OnError(err,msg);
	  return;
	 }
	 oThis.UploadedFile['name'] = this.FileName;
	 oThis.UploadedFile['size'] = this.FileSize;
	 oThis.UploadedFile['extension'] = this.FileExtension;
	 oThis.UploadedFile['path'] = this.FilePath;
	 oThis.UploadedFile['fullname'] = this.FilePath+this.FileName+'.'+this.FileExtension;
	 oThis.UploadedFile['icon'] = this.FileIcon;
	 if(oThis.OnUpload)
	  setTimeout(function(){oThis.OnUpload(oThis.UploadedFile);},300);
	};

 //--- EVENTS ---//
 this.OnError = null;
 this.OnBeginUpload = null;
 this.OnUpload = null;
}

GUploader.prototype.createHidden = function(_name, _value)
{
 var o = document.createElement('INPUT');
 o.type='hidden';
 o.name = _name;
 o.value = _value;
 return o;
}

GUploader.prototype.setBaseDir = function(basedir)
{
 this._iBaseDir.value = basedir;
}

GUploader.prototype.setFileName = function(filename)
{
 this._iFileName.value = filename;
}

GUploader.prototype.setAbsPath = function(abspath)
{
 this._iAbsPath.value = abspath;
}

