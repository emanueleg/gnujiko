/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 04-02-2014
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Taxcode validator.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

function validateTaxCode(cf)
{
 var validi, i, s, set1, set2, setpari, setdisp;
 if(cf == '')
  return false;
 cf = cf.toUpperCase();
 if(cf.length != 16)
  return false;
 validi = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
 for(i = 0; i < 16; i++ )
 {
  if(validi.indexOf(cf.charAt(i)) == -1)
   return false;
 }
 set1 = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
 set2 = "ABCDEFGHIJABCDEFGHIJKLMNOPQRSTUVWXYZ";
 setpari = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
 setdisp = "BAKPLCQDREVOSFTGUHMINJWZYX";
 s = 0;
 for( i = 1; i <= 13; i += 2 )
  s += setpari.indexOf( set2.charAt( set1.indexOf( cf.charAt(i) )));
 for( i = 0; i <= 14; i += 2 )
  s += setdisp.indexOf( set2.charAt( set1.indexOf( cf.charAt(i) )));
 if( s%26 != cf.charCodeAt(15)-'A'.charCodeAt(0) )
  return false;
 return true;
}
