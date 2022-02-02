/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 19-12-2013
 #PACKAGE: hacktvsearch-common
 #DESCRIPTION: 
 #VERSION: 2.1beta
 #CHANGELOG:  19-12-2013 : Aggiunta funzione per importare da tabella HTML
 #TODO:
 
*/


function hacktvform_emptytable_printPreview(layerId, formId)
{
 /*var data = document.getElementById("xtb-"+layerId+"-doctable").data;
 var sh = new GShell();
 var params = "&qry="+encodeURI(data['query'].replace(/\`/g,"'''"));
 sh.sendCommand("gframe -f print.preview -params `modelap=printmodels&modelct=COMMDOCS-LIST&parser=commdocslist"+params+"` -title `Stampa lista`");*/
}

function hacktvform_emptytable_excelImport(layerId, formId)
{
 var tb = document.getElementById("xtb-"+layerId+"-doctable");
 var tbfooter = document.getElementById("xtb-"+layerId+"-docfooter");

 var sh = new GShell();
 sh.OnOutput = function(o,files){
	 if(!files) return;
	 var file = files['files'][0];
	 if((file['extension'] != "xls") && (file['extension'] != "xlsx"))
	  return alert("Formato del file non valido. Sono supportati solamente file xls e xlsx.");

	 var fileName = file['fullname'].replace(HOME_PATH,"");

	 var sh2 = new GShell();
	 sh2.OnError = function(msg,err){alert(msg);}
	 sh2.OnOutput = function(o,a){
		 if(!a) return;
		 var fields = a['fields'];
		 var items = a['items'];

		 while(tb.gmutable.Fields.length)
		  tb.gmutable.DeleteField(tb.gmutable.Fields[0]['name']);

		 while(tb.rows.length > 1)
		  tb.gmutable.DeleteRow(1);

		 /* import fields */
		 for(var c=0; c < fields.length; c++)
		 {
		  var opt = {format:"text", width:100};
	      var field = tb.gmutable.AddField(fields[c]['name'], fields[c]['value'], opt);
		 }

		 /* import data */
		 for(var c=1; c < items.length; c++) /* <-- la prima riga viene utilizzata come intestazioni */
		 {
		  var item = items[c];
		  var r = tb.gmutable.AddRow();
		  for(var i=0; i < fields.length; i++)
		  {
		   r.cell[fields[i]['name']].setValue(items[c][fields[i]['name']]);
		   
		  }

		 }

		 if(tb.gmutable.Options.autoresize)
		  tb.gmutable.autoResize();
		}

	 sh2.sendCommand("excel read -f `"+fileName+"`");

	}

 sh.sendCommand("gframe -f fileupload");
}


function hacktvform_emptytable_excelExport(layerId, formId)
{
 var tb = document.getElementById("xtb-"+layerId+"-doctable");
 var title = document.getElementById(formId+"-title").innerHTML;

 var fileName = "";
 for(var c=0; c < title.length; c++)
 {
  switch(title.charAt(c))
  {
   case "/" : case "\\" : case " " : case "." : case "?" : case "#" : case "'" : case '"' : case "`" : case "~" : case ">" : case "<" : case "@" : case "&" : case "%" : case "+" : case "*" : fileName+= "-"; break;
   default : fileName+= title.charAt(c); break;
  }
  if(fileName)
   fileName = fileName.replace("--","");
 }
 fileName = fileName.toLowerCase();

 var sh = new GShell();
 sh.sendCommand("gframe -f excel/export -title `"+title+"` -params `file="+fileName+".xlsx` --use-cache-contents -contents `"+tb.parentNode.innerHTML+"`");
}

function hacktvform_emptytable_sendEmail(layerId, formId)
{
 var tb = document.getElementById("xtb-"+layerId+"-doctable");
 var tbf = document.getElementById("xtb-"+layerId+"-docfooter");
 var title = document.getElementById(formId+"-title").innerHTML;

 var div = document.createElement('DIV');
 div.innerHTML = tb.parentNode.innerHTML;
 var tb = div.getElementsByTagName('TABLE')[0];
 for(var c=0; c < tb.rows.length; c++)
  tb.rows[c].deleteCell(0);

 var div2 = document.createElement('DIV');
 div2.innerHTML = tbf.parentNode.innerHTML;

 div.appendChild(div2);

 var sh = new GShell();
 sh.sendCommand("gframe -f sendmail -pn subject -pv `"+title+"` --use-cache-contents -contents `"+div.innerHTML+"`");
}

function hacktvform_emptytable_runCommands(layerId, formId)
{
 var tb = document.getElementById("xtb-"+layerId+"-doctable");

 var sh = new GShell();
 sh.sendCommand("gframe -f powershell/runfromtable --use-cache-contents -contents `"+tb.parentNode.innerHTML+"`");
}

function hacktvform_emptytable_putonDesktop(layerId, formId)
{
 /* Funzione disabilitata fino a che non troviamo la soluzione per caricare gli elementi della lista. Una soluzione sarebbe quella di salvarli all'interno dei parametri XML. */
 /*var title = document.getElementById(formId+"-title").innerHTML;
 var form = HACKTVFORM_HANDLER.FormById[formId];

 HACKTVFORM_HANDLER.makeScreenShot(formId, function(data){
	 var sh = new GShell();
	 sh.OnOutput = function(o,pageId){
		 if(!pageId) return;
		 var sh2 = new GShell();
		 sh2.OnError = function(msg,err){alert(msg);}
		 sh2.OnOutput = function(){
			 alert("Operazione completata! Il modulo è stato inserito sul desktop, aggiorna la pagina.");
			}

		 var query = "";

		 var xmlParams = "<form title=\""+title+"\" width=\""+form.Width+"\" height=\""+form.Height+"\" layername=\""+form._layerName+"\" arguments=\""+form._layerArgs.replace(/&/g,"&amp;")+"\" query=\""+query+"\"/>";
		 
		 var contents = "<a href='#' onclick='idocmodule_runForm(this.parentNode.getAttribute(\"modid\"))'><img src='"+data+"' width='250'/></a>";

		 sh2.sendCommand("desktop add-module -name idoc -title `"+title+"` -page "+pageId+" -contents `"+contents+"` -params <![CDATA["+xmlParams+"]]>");
		}

	 sh.sendCommand("gframe -f desktop/page.select -title `Su quale scheda la inserisco?`");
	});*/
}

function hacktvform_emptytable_manageColumns(layerId, formId)
{
 var tb = document.getElementById("xtb-"+layerId+"-doctable");
 var tbfooter = document.getElementById("xtb-"+layerId+"-docfooter");
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a) return;

	 while(tbfooter.rows[0].cells.length > 1)
	 {
	  tbfooter.rows[0].deleteCell(1);
	  tbfooter.rows[1].deleteCell(1);
	 }

	 if(a['UPDATED_COLUMNS'] && a['UPDATED_COLUMNS'].length)
	 {
	  for(var c=0; c < a['UPDATED_COLUMNS'].length; c++)
	  {
	   var column = a['UPDATED_COLUMNS'][c];
	   var field = tb.gmutable.FieldByName[column['id']];
	   if(!field)
		continue;
	   var th = field.O;
	   th.innerHTML = column['title'];
	   th.setAttribute('format',column['format']);
	   th.setAttribute('width',column['width']);
	   if(column['includeintototals'])
	   {
		var td = document.createElement('TH');
		td.className = "blue";
		td.style.width = "100px";
		td.id = column['id'];
		td.innerHTML = column['title'];
		tbfooter.rows[0].appendChild(td);

		var td = tbfooter.rows[1].insertCell(-1);
		td.className = "blue";
		td.id = "xtb-"+layerId+"-"+column['id'];
		td.setAttribute('format',column['format']);
		td.setAttribute('decimals',column['decimals']);
		if(column['format'] == "currency")
		 td.innerHTML = "<em>&euro;</em>0,00";
		else
		 td.innerHTML = "0";
	   }

	  }
	 }

	 if(a['NEW_COLUMNS'] && a['NEW_COLUMNS'].length)
	 {
	  for(var c=0; c < a['NEW_COLUMNS'].length; c++)
	  {
	   var column = a['NEW_COLUMNS'][c];
	   var opt = {format:column['format'], width:column['width']};
	   var field = tb.gmutable.AddField(column['id'], column['title'], opt);
	   if(column['includeintototals'])
	   {
		var td = document.createElement('TH');
		td.className = "blue";
		td.style.width = "100px";
		td.id = column['id'];
		td.innerHTML = column['title'];
		tbfooter.rows[0].appendChild(td);

		var td = tbfooter.rows[1].insertCell(-1);
		td.className = "blue";
		td.id = "xtb-"+layerId+"-"+column['id'];
		td.setAttribute('format',column['format']);
		td.setAttribute('decimals',column['decimals']);
		if(column['format'] == "currency")
		 td.innerHTML = "<em>&euro;</em>0,00";
		else
		 td.innerHTML = "0";
	   }
	  }
	 }

	 if(a['REMOVED_COLUMNS'] && a['REMOVED_COLUMNS'].length)
	 {
	  for(var c=0; c < a['REMOVED_COLUMNS'].length; c++)
	  {
	   tb.gmutable.DeleteField(a['REMOVED_COLUMNS'][c]);
	  }
	 }

	 if(tb.gmutable.Options.autoresize)
	  tb.gmutable.autoResize();

	 hacktvform_emptytable_updateTotals(layerId);

	}

 sh.sendCommand("gframe -f hacktvforms/managetablecolumns --use-cache-contents -contents `"+tb.parentNode.innerHTML+"`");
}

function hacktvform_emptytable_htmlTableImport(layerId, formId)
{
 var tb = document.getElementById("xtb-"+layerId+"-doctable");
 var tbfooter = document.getElementById("xtb-"+layerId+"-docfooter");

 var sh = new GShell();
 sh.OnOutput = function(o,html){
	 if(!html) return;

	 var div = document.createElement('DIV');
	 div.style.display = "none";
	 div.innerHTML = html;
	 document.body.appendChild(div);

	 var htb = div.getElementsByTagName("TABLE")[0];
	 if(!htb)
	 {
	  if(confirm("Errore: Nessuna tabella html trovata.\nDevi copiare una tabella da una qualsiasi pagina web e incollarla nell'editor html.\nVuoi riprovare"))
	   return hacktvform_emptytable_htmlTableImport(layerId, formId);
	  else
	  return;
	 }

	 var thR = null; // prima riga, header row
	 var fields = new Array();
	 var items = new Array();

	 for(var c=0; c < htb.rows.length; c++)
	 {
	  if(!htb.rows[c].cells.length)
	   continue;
	  if(!thR)
	  {
	   thR = htb.rows[c];
	   for(var j=0; j < thR.cells.length; j++)
	   {
		fields.push(thR.cells[j].textContent.ltrim());
	   }
	  }
	  else
	  {
	   var item = new Array();
	   for(var j=0; j < htb.rows[c].cells.length; j++)
	   {
		item[j] = htb.rows[c].cells[j].textContent.ltrim();
	   }
	   items.push(item);
	  }
	 }


	 while(tbfooter.rows[0].cells.length > 1)
	 {
	  tbfooter.rows[0].deleteCell(1);
	  tbfooter.rows[1].deleteCell(1);
	 }
	 tb.gmutable.EmptyTable();
	 while(tb.gmutable.Fields.length)
	 {
	  tb.gmutable.DeleteField(tb.gmutable.Fields[0].name);
	 }

	 for(var c=0; c < fields.length; c++)
	 {
	  var f = tb.gmutable.AddField("field-"+(c+1), fields[c]);
	  f.O.style.paddingLeft = "10px";
	  f.O.style.paddingRight = "10px";
	 }

	 for(var c=0; c < items.length; c++)
	 {
	  var item = items[c];
	  var r = tb.gmutable.AddRow();
	  for(var j=0; j < item.length; j++)
	  {
	   r.cells[j+1].setValue(item[j]);
	   r.cells[j+1].style.paddingLeft = "10px";
	   r.cells[j+1].style.paddingRight = "10px";
	  }
	 }

	 if(tb.gmutable.Options.autoresize)
	  tb.gmutable.autoResize();

	}

 sh.sendCommand("gframe -f hacktvforms/htmleditor");
}

