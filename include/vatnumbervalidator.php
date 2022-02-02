<?php
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


/**
 * Controlla partita IVA.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version 2012-05-12
 * @param string $pi  Partita IVA costituita da 11 cifre. Non sono ammessi
 * caratteri di spazio, per cui i campi di input dell'utente dovrebbero
 * essere trimmati preventivamente. La stringa vuota e' ammessa, cioe'
 * il dato viene considerato opzionale.
 * @return string Stringa vuota se il codice di controllo della partita IVA
 * e' corretto oppure la stringa di input e' vuota. Altrimenti ritorna un
 * messaggio che descrive il problema.
 */

function validateVatNumber($pi)
{
 if($pi === '')
  return false;
 if(strlen($pi) != 11)
  return false;
 if(preg_match("/^[0-9]+\$/", $pi) != 1)
  return false;

 $s = 0;
 for( $i = 0; $i <= 9; $i += 2 )
  $s += ord($pi[$i]) - ord('0');
 for( $i = 1; $i <= 9; $i += 2 )
 {
  $c = 2*( ord($pi[$i]) - ord('0') );
  if( $c > 9 )  $c = $c - 9;
   $s += $c;
 }
 if((10 - $s%10 )%10 != ord($pi[10]) - ord('0'))
  return false;
 return true;
}
?>

