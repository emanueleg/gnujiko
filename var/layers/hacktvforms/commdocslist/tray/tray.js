/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-05-2013
 #PACKAGE: gcommercialdocs
 #DESCRIPTION: 
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/


function hacktvform_commdocslist_printPreview(layerId, formId)
{
 var title = document.getElementById(formId+"-title").innerHTML;

 var data = document.getElementById("xtb-"+layerId+"-doctable").data;

 var totAmount = document.getElementById("xtb-"+layerId+"-totamount").getAttribute('value');
 var totVat = document.getElementById("xtb-"+layerId+"-totvat").getAttribute('value');
 var subTot = document.getElementById("xtb-"+layerId+"-subtot").getAttribute('value');

 var sh = new GShell();
 var params = "&doctitle="+title+"&totamount="+totAmount+"&totvat="+totVat+"&subtot="+subTot;
 params+= "&qry="+encodeURI(data['query'].replace(/\`/g,"'''"));
 sh.sendCommand("gframe -f print.preview -params `modelap=printmodels&modelct=COMMDOCS-LIST&parser=commdocslist"+params+"` -title `Stampa lista`");
}

function hacktvform_commdocslist_excelExport(layerId, formId)
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

function hacktvform_commdocslist_sendEmail(layerId, formId)
{
 var tb = document.getElementById("xtb-"+layerId+"-doctable");
 var tbf = document.getElementById("xtb-"+layerId+"-footertable");
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

function hacktvform_commdocslist_runCommands(layerId, formId)
{
 var tb = document.getElementById("xtb-"+layerId+"-doctable");

 var sh = new GShell();
 sh.sendCommand("gframe -f powershell/runfromtable --use-cache-contents -contents `"+tb.parentNode.innerHTML+"`");
}

function hacktvform_commdocslist_putonDesktop(layerId, formId)
{
 var title = document.getElementById(formId+"-title").innerHTML;
 var form = HACKTVFORM_HANDLER.FormById[formId];

 HACKTVFORM_HANDLER.makeScreenShot(formId, function(data){
	 var sh = new GShell();
	 sh.OnOutput = function(o,pageId){
		 if(!pageId) return;
		 var sh2 = new GShell();
		 sh2.OnError = function(msg,err){alert(msg);}
		 sh2.OnOutput = function(){
			 alert("Operazione completata! Il modulo è stato inserito sul desktop");
			}

		 var query = form._layerData['query'].replace(/</g,"&lt;");
		 query = query.replace(/>/g,"&gt;");

		 var xmlParams = "<form title=\""+title+"\" width=\""+form.Width+"\" height=\""+form.Height+"\" layername=\""+form._layerName+"\" arguments=\""+form._layerArgs.replace(/&/g,"&amp;")+"\" query=\""+query+"\"/>";
		 
		 var contents = "<a href='#' onclick='idocmodule_runForm(this.parentNode.getAttribute(\"modid\"))'><img src='"+data+"' width='250'/></a>";

		 sh2.sendCommand("desktop add-module -name idoc -title `"+title+"` -page "+pageId+" -contents `"+contents+"` -params <![CDATA["+xmlParams+"]]>");
		}

	 sh.sendCommand("gframe -f desktop/page.select -title `Su quale scheda la inserisco?`");
	});
}

