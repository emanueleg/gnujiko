/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 04-02-2014
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Vatnumber validator.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

function validateVatNumber(pi)
{
 if(pi == '')
  return false;
 if(pi.length != 11)
  return false;
 validi = "0123456789";
 for(i = 0; i < 11; i++)
 {
  if(validi.indexOf(pi.charAt(i)) == -1)
   return false;
 }
 s = 0;
 for(i = 0; i <= 9; i += 2)
  s+= pi.charCodeAt(i) - '0'.charCodeAt(0);
 for(i = 1; i <= 9; i += 2)
 {
  c = 2*(pi.charCodeAt(i) - '0'.charCodeAt(0));
  if(c > 9)
   c = c - 9;
  s+= c;
 }
 if(( 10 - s%10 )%10 != pi.charCodeAt(10) - '0'.charCodeAt(0))
  return false;
 return true;
}
