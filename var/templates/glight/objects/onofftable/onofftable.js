/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-08-2014
 #PACKAGE: glight-template
 #DESCRIPTION: GLight Template - OnOffTable
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

function OnOffTable(obj, template)
{
 if(!template)
  return alert("OnOffTable error: GLight Template not initialized.");
 this.O = obj;
 this.template = template;

 this.onexpand = function(){};
 this.oncollapse = function(){};

 this.init();
}
//-------------------------------------------------------------------------------------------------------------------//
OnOffTable.prototype.init = function()
{
 var oThis = this;
 var table = this.template.initCollapseTable(this.O);
 for(var c=0; c < table.rows.length; c++)
 {
  var r = table.rows[c];
  var ul = r.cells[0].getElementsByTagName("UL")[0];
  var onBtn = ul.getElementsByTagName("LI")[0];
  var offBtn = ul.getElementsByTagName("LI")[1];
  
  r.onBtn = onBtn;
  r.offBtn = offBtn;
  onBtn.r = r;
  offBtn.r = r;

  onBtn.onclick = function(){
	 this.r.expand();
	 this.className = "first blue";
	 this.r.offBtn.className = "last";
	 oThis.onexpand(this.r);
	}

  offBtn.onclick = function(){
	 this.r.collapse();
	 this.className = "last gray";
	 this.r.onBtn.className = "first";
	 oThis.oncollapse(this.r);
	}

  this.template.initCollapseTableRow(r,table);
  c++;
 }

}
//-------------------------------------------------------------------------------------------------------------------//

