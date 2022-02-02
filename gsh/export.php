<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 25-05-2013
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Register environment variable
 #VERSION: 2.1beta
 #CHANGELOG: 25-05-2013 : Aggiunto parametro -time per impostare il termine della scadenza.
 #TODO:
 
*/

function shell_export($args, $sessid)
{
 $output = "";
 $outArr = array();

 $time = "1 week";

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-var' : {$var=$args[$c+1]; $c++;} break;
   case '-value' : {$val=$args[$c+1]; $c++;} break;
   case '-time' : {$time=$args[$c+1]; $c++;} break;
   default : {
	 $x = explode("=",$args[$c]);
	 $var = strtoupper(trim($x[0]));
	 $val = trim($x[1]);
	 if($val)
	 {
	  setcookie($var,$val,strtotime("+".$time));
	  $output.= "Variable has been registered: $var=$val\n";
	 }
	 else
	 {
	  setcookie($var,false);
	  $output.= "Variable $var has been removed!\n";
	 }
	 $var = null;
	 $val = null;
	} break;
  }

 if($var)
 {
  if($val)
  {
   setcookie($var,$val,strtotime("+1 week"));
   $output.= "Variable $var has been registered\n";
  }
  else
  {
   setcookie($var,false);
   $output.= "Variable $var has been removed!\n";
  }
 }
 return array('message'=>$output, 'outarr'=>$outArr);
}

