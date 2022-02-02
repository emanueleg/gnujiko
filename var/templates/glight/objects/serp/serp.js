/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-03-2016
 #PACKAGE: glight-template
 #DESCRIPTION: SERP Object
 #VERSION: 2.2beta
 #CHANGELOG: 17-03-2016 : Aggiunto evento OnBeforeReload.
			 11-11-2014 : Bug fix in function reload. Aggiunto escape(decodeURIComponent(...))
 #TODO:
 
*/

//-------------------------------------------------------------------------------------------------------------------//
function SERP(orderBy, orderMethod, rpp, pg)
{
 this.OrderBy = orderBy ? orderBy : "";
 this.OrderMethod = orderMethod ? orderMethod : "ASC";
 this.RPP = rpp ? rpp : 0;
 this.PG = pg ? pg : 1;

 this.URLVARS = this.getVars();
 
 /* EVENTS */
 this.OnBeforeReload = null;	// function(pg){}

}
//-------------------------------------------------------------------------------------------------------------------//
SERP.prototype.setVar = function(variable, value)
{
 this.URLVARS[variable] = value;
}
//-------------------------------------------------------------------------------------------------------------------//
SERP.prototype.unsetVar = function(variable)
{
 delete this.URLVARS[variable];
}
//-------------------------------------------------------------------------------------------------------------------//
SERP.prototype.getVars = function() 
{
 var vars = {};
 var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value){vars[key] = value;});
 return vars;
}
//-------------------------------------------------------------------------------------------------------------------//
SERP.prototype.reload = function(setPg)
{
 if(this.OnBeforeReload)
 {
  if(this.OnBeforeReload(setPg) == false)
   return;
 }

 if((typeof(setPg) == "number") && (setPg == 0))
  this.PG = 1;
 else if(setPg == -1)
  this.PG = parseInt(this.PG)-1;
 else if(setPg == 1)
  this.PG = parseInt(this.PG)+1;

 var href = document.location.href;
 var startHREF = href;
 var x = href.indexOf("?");
 if(x > -1)
 {
  startHREF = href.substr(0,x);
 }

 this.URLVARS['sortby'] = this.OrderBy;
 this.URLVARS['sortmethod'] = this.OrderMethod;
 this.URLVARS['rpp'] = this.RPP;
 this.URLVARS['pg'] = this.PG;

 var endHREF = "";
 for(var k in this.URLVARS)
  endHREF+= "&"+k+"="+escape(decodeURIComponent(this.URLVARS[k]));
 if(endHREF) endHREF = endHREF.substr(1);

 document.location.href = startHREF+"?"+endHREF;
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//

