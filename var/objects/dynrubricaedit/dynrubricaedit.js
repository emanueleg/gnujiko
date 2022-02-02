/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 28-11-2012
 #PACKAGE: dynrubricaedit
 #DESCRIPTION: Basic edit object with rubrica property
 #VERSION: 2.0beta
 #CHANGELOG: 05-07-2012 : Some bug fixed.
			 13-06-2012 : Gbox deprecated bug fix.
 #TODO:
 
*/

function DynRubricaEdit(obj, ct, ap)
{
 var archivePrefix = ap ? ap : "rubrica";
 var catTag = ct;
 
 obj.valueChanged = obj.onchange;

 var mE = EditSearch.init(obj,
	"dynarc item-find -ap `"+archivePrefix+"`"+(catTag ? " -ct `"+catTag+"`" : "")+" -field name `","` -limit 10 --order-by 'name ASC'",
	"id","name","items",true);

 mE.ap = archivePrefix;
 mE.ct = catTag;
 mE.infoButton.ap = archivePrefix;
 mE.infoButton.ct = catTag;

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

	  sh.sendCommand("dynarc item-info -ap `"+ap+"` -name `"+this.value+"`");
	 }
	 if(this.valueChanged)
	  this.valueChanged();
	}

 mE.onchange();
 return mE;
}

