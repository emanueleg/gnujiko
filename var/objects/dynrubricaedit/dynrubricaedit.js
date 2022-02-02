/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 03-03-2014
 #PACKAGE: dynrubricaedit
 #DESCRIPTION: Basic edit object with rubrica property
 #VERSION: 2.2beta
 #CHANGELOG: 03-02-2014 : Aggiunto refid
			 13-10-2014 : Possibilità di ricerca anche per codice.
			 05-07-2012 : Some bug fixed.
			 13-06-2012 : Gbox deprecated bug fix.
 #TODO:
 
*/

function DynRubricaEdit(obj, ct, ap, extraQry)
{
 var archivePrefix = ap ? ap : "rubrica";
 var catTag = ct;
 
 obj.valueChanged = obj.onchange;

 var mE = EditSearch.init(obj,
	"dynarc search -ap `"+archivePrefix+"`"+(catTag ? " -ct `"+catTag+"`" : "")+" -fields name,code_str `","` -limit 10 --order-by 'name ASC'"+(extraQry ? " "+extraQry : ""),
	"id","name","items",true,"name",function(items,resArr,retVal){
		 for(var c=0; c < items.length; c++)
		 {
		  resArr.push(items[c]['code_str']+" - "+items[c]['name']);
		  retVal.push(items[c]['id']);
		 } 
		});

 mE.ap = archivePrefix;
 mE.ct = catTag;
 mE.infoButton.ap = archivePrefix;
 mE.infoButton.ct = catTag;
 mE.oldValue = mE.value;

 mE.onchange = function(){
	 if(!this.value) return;
	 if(this.data)
	 {
	  var oThis = this;
	  mE.infoButton.src = ABSOLUTE_URL+"share/icons/16x16/rubrica-contact-info.gif";
	  mE.infoButton.onclick = function(){
		 var ap = this.ap;
		 var sh = new GShell();
		 sh.OnOutput = function(o,a){
			 if(!a) return;
			 mE.value = a['name'];
			 if(oThis.valueChanged)
			  oThis.valueChanged();
			}
		 sh.sendCommand("gframe -f rubrica.edit -params `ap="+ap+"&id="+mE.data['id']+"`");
		}
	  mE.infoButton.style.display = "";
	 }
	 else
	 {
	  var ap = this.ap;
	  var sh = new GShell();
	  var oThis = this;
	  sh.OnOutput = function(o,a){
		 mE.data = a;
	 	 mE.infoButton.src = ABSOLUTE_URL+"share/icons/16x16/rubrica-contact-info.gif";
	 	 mE.infoButton.onclick = function(){
			 var ap = this.ap;
			 var sh = new GShell();
		 	 sh.OnOutput = function(o,a){
			 	  if(!a) return;
			 	  mE.value = a['name'];
			 	  if(oThis.valueChanged)
			  	   oThis.valueChanged();
				 }
			 sh.sendCommand("gframe -f rubrica.edit -params `ap="+ap+"&id="+mE.data['id']+"`");
			}
	 	 mE.infoButton.style.display = "";
		}
	  sh.OnError = function(){
	 	 mE.infoButton.src = ABSOLUTE_URL+"share/icons/16x16/rubrica-contact-add.png";
	 	 mE.infoButton.onclick = function(){
			 var ap = this.ap;
			 var ct = this.ct;
			 var sh = new GShell();
			 sh.OnOutput = function(o,a){
				  if(!a) return;
				  mE.data = a;
				  mE.value = a['name'];
		 		  mE.infoButton.src = ABSOLUTE_URL+"share/icons/16x16/rubrica-contact-info.gif";
		 		  mE.infoButton.onclick = function(){
					 var ap = this.ap;
			 	  	 var sh = new GShell();
		 			 sh.OnOutput = function(o,a){
					 	 if(!a) return;
					 	 mE.value = a['name'];
			 			 if(oThis.valueChanged)
			  			  oThis.valueChanged();
						}
			 	  	 sh.sendCommand("gframe -f rubrica.edit -params `ap="+ap+"&id="+mE.data['id']+"`");
				 	}
				  mE.infoButton.onclick();
				}
			 sh.sendCommand("gframe -f rubrica.new -title `Vuoi inserire questo contatto in rubrica?` -contents `"+mE.value+"` -params `ap="+ap+"&ct="+ct+"`");
			}
	 	 mE.infoButton.style.display = "";
		}

	  if((this.oldValue == this.value) && this.getAttribute('refid'))
	   sh.sendCommand("dynarc item-info -ap `"+ap+"` -id `"+this.getAttribute('refid')+"`");
	  else
	   sh.sendCommand("dynarc item-info -ap `"+ap+"` -name `"+this.value+"`");
	 }
	 if(this.valueChanged)
	  this.valueChanged();
	}

 mE.onchange();
 return mE;
}

