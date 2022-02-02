/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 27-10-2014
 #PACKAGE: 
 #DESCRIPTION: 
 #VERSION: 2.0beta
 #CHANGELOG: 27-10-2014 : Aggiunta funzione importFromCommand.
 #TODO:
 
*/

function HackTVFormOnLoad(layer, data)
{
 new GMenu(document.getElementById("xtb-"+layer.id+"-mainmenu"));

 var div = document.getElementById("xtb-"+layer.id+"-doctable").parentNode;
 div.style.height = div.parentNode.offsetHeight-70;
 div.style.width = div.offsetWidth;

 document.getElementById("xtb-"+layer.id+"-doctable").style.display="";

 var tb = new GMUTable(document.getElementById("xtb-"+layer.id+"-doctable"));
 tb.O.rows[0].cells[0].getElementsByTagName('INPUT')[0].onclick = function(){
	 tb.selectAll(this.checked);
	}

 tb.OnBeforeAddRow = function(r){
	 r.cells[0].innerHTML = "<input type='checkbox'/ >";
	 for(var c=1; c < r.cells.length; c++)
	  r.cells[c].innerHTML = "<span class='graybold'></span>";
	}

 tb.OnCellEdit = function(){hacktvform_emptytable_updateTotals(layer.id);}

 document.getElementById("xtb-"+layer.id+"-doctable").data = data;

 var totAmount = 0;
 var totVat = 0;
 var subTot = 0;

 if(data['query'])
 {
  var sh = new GShell();
  sh.OnOutput = function(o,a){
	 if(!a || !a['items'])
	  return;
	 
	 for(var c=0; c < a['items'].length; c++)
	 {
	  var item = a['items'][c];
	  var r = tb.AddRow();
	  r.id = item['id'];
	  for(var i=0; i < tb.Fields.length; i++)
	   r.cell[tb.Fields[i].name].setValue(item[tb.Fields[i].name]);

	  totAmount+= parseFloat(item['amount']);
	  totVat+= parseFloat(item['vat']);
	  subTot+= parseFloat(item['total']);
	 }
	 document.getElementById("xtb-"+layer.id+"-totamount").innerHTML = "<em>&euro;</em>"+formatCurrency(totAmount,2);
	 document.getElementById("xtb-"+layer.id+"-totvat").innerHTML = "<em>&euro;</em>"+formatCurrency(totVat,2);
	 document.getElementById("xtb-"+layer.id+"-subtot").innerHTML = "<em>&euro;</em>"+formatCurrency(subTot,2);    
	}

  sh.sendCommand(data['query']);
 }
 else if(data['results'])
 {
  for(var c=0; c < data['results'].length; c++)
  {
   var r = tb.AddRow();
   r.id = data['results'][c]['id'];
   for(var i=0; i < tb.Fields.length; i++)
	r.cell[tb.Fields[i].name].setValue(data['results'][c][tb.Fields[i].name]);
  }
 }
}

function hacktvform_emptytable_showColumn(layerId, colIdx, cb)
{
 var tb = document.getElementById("xtb-"+layerId+"-doctable").gmutable;
 tb.showColumn(colIdx,cb.checked);
}

function hacktvform_emptytable_updateTotals(layerId)
{
 var tb = document.getElementById("xtb-"+layerId+"-doctable").gmutable;
 var tbfooter = document.getElementById("xtb-"+layerId+"-docfooter");

 var TOTALS_COLUMNS_IDS = new Array();
 var TOTALS = new Array();

 for(var c=1; c < tbfooter.rows[0].cells.length; c++)
 {
  TOTALS[tbfooter.rows[0].cells[c].id] = 0;
  TOTALS_COLUMNS_IDS.push(tbfooter.rows[0].cells[c].id);
 }

 for(var c=1; c < tb.O.rows.length; c++)
 {
  for(var i=0; i < TOTALS_COLUMNS_IDS.length; i++)
  {
   var cell = tb.O.rows[c].cell[TOTALS_COLUMNS_IDS[i]];
   var field = tb.O.rows[0].cells[cell.cellIndex];
   var value = cell.getValue();
   if(field.getAttribute('format') == 'currency')
	value = parseCurrency(value);
   else if(field.getAttribute('format') == 'number')
	value = parseFloat(value);
   else
	value = 0;
   TOTALS[TOTALS_COLUMNS_IDS[i]]+= value;
  }
 }

 for(var c=0; c < TOTALS_COLUMNS_IDS.length; c++)
 {
  var th = document.getElementById("xtb-"+layerId+"-"+TOTALS_COLUMNS_IDS[c]);
  if(th.getAttribute('format') == 'currency')
   th.innerHTML = formatCurrency(TOTALS[TOTALS_COLUMNS_IDS[c]], th.getAttribute('decimals') ? parseFloat(th.getAttribute('decimals')) : 2);
  else
   th.innerHTML = formatNumber(TOTALS[TOTALS_COLUMNS_IDS[c]], th.getAttribute('decimals') ? parseFloat(th.getAttribute('decimals')) : 2);
 }

}

function hacktvform_emptytable_addRow(layerId)
{
 var tb = document.getElementById("xtb-"+layerId+"-doctable").gmutable;
 return tb.AddRow();
}

function hacktvform_emptytable_importFromCommand(layerId)
{
 var cmd = prompt("Digita un comando gshell");
 if(!cmd) return;

 var tb = document.getElementById("xtb-"+layerId+"-doctable").gmutable;
 tb.EmptyTable();
 while(tb.Fields.length)
 {
  tb.DeleteField(tb.Fields[0].name);
 }

 var sh = new GShell();
 sh.OnError = function(err){alert(err);}
 sh.OnOutput = function(o,a){
	 if(!a) return alert("No data found.\n"+o);
	 var list = a['items'] ? a['items'] : a['results'];
	 if(!list) return alert(o ? o : "No results found.");
	 
	 for(var c=0; c < list.length; c++)
	 {
	  if(c == 0)
	  {
	   // preparo le colonne
	   var keys = array_keys(list[0]);
	   for(var i=0; i < keys.length; i++)
	   {
		if(typeof(a['items'][0][keys[i]]) != 'string')
		 continue;
		tb.AddField(keys[i], keys[i], {editable:true, width:100, minwidth:100});
	   }
	  }

	  var r = tb.AddRow();
	  for(var i=0; i < tb.Fields.length; i++)
	  {
	   var field = tb.Fields[i];
	   r.cell[field.name].setValue(a['items'][c][field.name]);
	  }
	 }

	}
 sh.sendCommand(cmd);

}


function hacktvform_emptytable_cut(layerId)
{

}

function hacktvform_emptytable_copy(layerId)
{
 var tb = document.getElementById("xtb-"+layerId+"-doctable").gmutable;
 var list = tb.GetSelectedRows();
 if(!list.length)
  return alert("Devi selezionare almeno una riga");

 var ret = new Array();
 ret['fields'] = new Array();
 ret['items'] = new Array(); 

 for(var c=0; c < tb.Fields.length; c++)
 {
  var field = new Array();
  field['id'] = tb.Fields[c].O.id;
  field['title'] = tb.Fields[c].O.innerText;
  field['format'] = tb.Fields[c].O.getAttribute('format');

  ret['fields'].push(field);
 }

 for(var c=0; c < list.length; c++)
 {
  var r = list[c];
  var item = new Array();
  for(var i=0; i < tb.Fields.length; i++)
   item[tb.Fields[i].O.id] = list[c].cell[tb.Fields[i].O.id].getValue();
  ret['items'].push(item);
 }

 HACKTVFORM_HANDLER.saveToClipboard("TABLE",ret);
}

function hacktvform_emptytable_paste(layerId)
{
 var data = HACKTVFORM_HANDLER.readFromClipboard("TABLE");
 if(!data)
  return alert("Niente da incollare");
 
 if(!data['fields'] || !data['items'])
  return alert("Il contenuto che stai cercando di incollare in questa tabella non è compatibile oppure ha un protocollo sconosciuto.");

 var tb = document.getElementById("xtb-"+layerId+"-doctable").gmutable;

 for(var i=0; i < data['items'].length; i++)
 {
  var r = tb.AddRow();
  for(var c=0; c < data['fields'].length; c++)
  {
   if(r.cell[data['fields'][c]['id']])
	r.cell[data['fields'][c]['id']].setValue(data['items'][i][data['fields'][c]['id']]);
  }
 }


}

function hacktvform_emptytable_deleteSelected(layerId)
{
 var tb = document.getElementById("xtb-"+layerId+"-doctable").gmutable;
 var list = tb.GetSelectedRows();
 if(!list.length)
  return alert("Devi selezionare almeno una riga");

 if(!confirm("Sei sicuro di voler eliminare le righe selezionate?"))
  return;

 for(var c=0; c < list.length; c++)
  list[c].remove();
}

