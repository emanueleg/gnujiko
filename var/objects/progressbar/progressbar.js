/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 15-01-2012
 #PACKAGE: progressbar
 #DESCRIPTION: Simple progress bar obejct
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS:
 #TODO:
 
*/

function ProgressBar(id)
{
 this.O = document.getElementById(id ? id : "progressbar");
 this.value = 0;
}

ProgressBar.prototype.setValue = function(val)
{
 val = Math.floor(val);
 this.value = val > 100 ? 100 : (val < 0 ? 0 : val);
 this.O.getElementsByTagName('DIV')[0].style.width = this.value+"%";
 if(this.value == 100)
  this.O.getElementsByTagName('DIV')[0].className = "green";
 return this.value;
}
