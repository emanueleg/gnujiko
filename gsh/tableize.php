<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-12-2014
 #PACKAGE: tableize
 #DESCRIPTION: Intabella i risultati di una precedente query (comando gshell).
 #VERSION: 2.2beta
 #CHANGELOG: 11-12-2014 : Aggiunta funzione include-totals.
			 27-10-2014 : Bug fix vari.
 #TODO: 
 
*/
//-------------------------------------------------------------------------------------------------------------------//
function shell_tableize($args, $sessid, $shellid, $extraVar)
{
 $out = "";
 $_KEYS = array();
 $_NAMES = array();
 $_FORMATS = array();
 $_RET_FORMATS = array();
 $_TOTALS = array();

 $showHeaders = true;		// Mostra la prima riga con le intestazioni delle colonne
 $fillEmptyCell = true;		// Riempie le celle vuote con un &nbsp;
 $twoColors = true;			// Usa colori di sfondo alternati per una facile lettura.
 $ignoreZeroValues = true;	// Evita di mostrare i valori nulli o uguali a zero.

 $bg0 = "#777777";		$col0 = "#ffffff";
 $bg1 = "#ffffff";		$col1 = "#333333";
 $bg2 = "#dadada";		$col2 = "#000000";

 for($c=0; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-k' : {
	 $val = $args[$c+1];
	 if(strpos($val, "|") !== false)
	  $_KEYS = explode("|",$val);
	 else if(strpos($val, ";") !== false)
	  $_KEYS = explode(";",$val);
	 else if(strpos($val, ",") !== false)
	  $_KEYS = explode(",",$val);
	 else
	  $_KEYS[] = $val;
	} break;

   case '-n' : {
	 $val = $args[$c+1];
	 if(strpos($val, "|") !== false)
	  $_NAMES = explode("|",$val);
	 else if(strpos($val, ";") !== false)
	  $_NAMES = explode(";",$val);
	 else if(strpos($val, ",") !== false)
	  $_NAMES = explode(",",$val);
	 else
	  $_NAMES[] = $val;
	} break;


   case '-f' : {
	 $val = $args[$c+1];
	 if(strpos($val, "|") !== false)
	  $list = explode("|",$val);
	 else if(strpos($val, ";") !== false)
	  $list = explode(";",$val);
	 else if(strpos($val, ",") !== false)
	  $list = explode(",",$val);
	 else
	  $list[] = $val;
	 for($i=0; $i < count($list); $i++)
	 {
	  switch($list[$i])
	  {
	   case 'time' : 		{$_F = array('type'=>'time'); 		$_FORMATS[] = $_F; $_RET_FORMATS[] = $_F['type']; } break;
	   case 'date' : 		{$_F = array('type'=>'date'); 		$_FORMATS[] = $_F; $_RET_FORMATS[] = $_F['type']; } break;
	   case 'datetime' : 	{$_F = array('type'=>'datetime'); 	$_FORMATS[] = $_F; $_RET_FORMATS[] = $_F['type']; } break;
	   case 'bool' : 		{$_F = array('type'=>'bool'); 		$_FORMATS[] = $_F; $_RET_FORMATS[] = $_F['type']; } break;
	   case 'number' : 		{$_F = array('type'=>'number'); 	$_FORMATS[] = $_F; $_RET_FORMATS[] = $_F['type']; } break;
	   case 'currency' : 	{$_F = array('type'=>'currency'); 	$_FORMATS[] = $_F; $_RET_FORMATS[] = $_F['type']; } break;
	   case 'string' : 		{$_F = array('type'=>'string'); 	$_FORMATS[] = $_F; $_RET_FORMATS[] = $_F['type']; } break;

	   default : {
		 if(substr($list[$i], 0, strlen('reference')) == 'reference')
		 {
		  if(($s = strpos($list[$i], "{")) === false)
		   return array('message'=>'Invalid format reference. Missing brachets. {...}\n'.$list[$i], 'error'=>'INVALID_FORMAT_REFERENCE');
		  if(($e = strpos($list[$i], "}", $s)) === false)
		   return array('message'=>'Invalid format reference. Missing closure brachet. "}"\n'.$list[$i], 'error'=>'INVALID_FORMAT_REFERENCE');
		  $tmp = substr($list[$i], $s+1, ($e-$s)-1);
		  $chunks = explode(";",$tmp); // gli argomenti all'interno dei reference vanno separati con un punto e virgola.
		  $F = array('type'=>'reference');
		  for($j=0; $j < count($chunks); $j++)
		  {
		   $tmp = explode("=",$chunks[$j]);
		   $F[trim($tmp[0])] = trim($tmp[1]);
		  }
		  $_FORMATS[] = $F;
		  $_RET_FORMATS[] = "string";
		 }
		 else if(substr($list[$i], 0, strlen('option')) == 'option')
		 {
		  if(($s = strpos($list[$i], "{")) === false)
		   return array('message'=>'Invalid format reference. Missing brachets. {...}\n'.$list[$i], 'error'=>'INVALID_FORMAT_REFERENCE');
		  if(($e = strpos($list[$i], "}", $s)) === false)
		   return array('message'=>'Invalid format reference. Missing closure brachet. "}"\n'.$list[$i], 'error'=>'INVALID_FORMAT_REFERENCE');
		  $tmp = substr($list[$i], $s+1, ($e-$s)-1);
		  $chunks = explode(";",$tmp); // gli argomenti all'interno degli options vanno separati con un punto e virgola.
		  $F = array('type'=>'option', 'values'=>array());
		  for($j=0; $j < count($chunks); $j++)
		  {
		   $tmp = explode("=",$chunks[$j]);
		   $F['values'][trim($tmp[0])] = trim($tmp[1]);
		  }
		  $_FORMATS[] = $F;
		  $_RET_FORMATS[] = "string";
		 }
		 else
		  return array('message'=>'Invalid format type: '.$list[$i], 'error'=>'INVALID_FORMAT_TYPE');
		} break;

	  } // eof - switch
	 } // eof - for
	} break;

   case '--include-totals' : case '--show-totals' : $includeTotals=true; break;
  }

 if(!$extraVar && !count($_KEYS) && !count($_NAMES))
  return tableize_invalidArguments();

 if(!$extraVar) return array('message'=>'no data found.', 'error'=>'NO_DATA_FOUND');

 if(!count($_KEYS))
 {
  // Se le chiavi non vengono specificate verranno riportate tutte quelle disponibili prelevandole dal primo risultato.
  $_NAMES = array();
  $item = $extraVar[0];
  if(is_array($item))
  {
   reset($item);
   while(list($k,$v) = each($item))
   {
    $_KEYS[] = $k;
    $_NAMES[] = $k;
   }
  }
 }

 // Nel caso siano titolati solo i primi campi e non tutti, gli altri prendono il nome dalla chiave.
 if(count($_KEYS) > count($_NAMES))
 {
  $s = count($_NAMES);
  for($c=$s; $c < count($_KEYS); $c++)
   $_NAMES[$c] = $_KEYS[$c];
 }
 

 /* --- START OUTPUT --- */
 $out.= "<table border='0'>";

 // HEADERS //
 if($showHeaders)
 {
  $out.= "<tr>";
  for($c=0; $c < count($_KEYS); $c++)
   $out.= "<th style='background:".$bg0.";color:".$col0."' format='".$_RET_FORMATS[$c]."'>".$_NAMES[$c]."</th>";
  $out.= "</tr>";
 }

 // ELEMENTS //
 $row = 0; // <-- serve solo per il colore
 for($c=0; $c < count($extraVar); $c++)
 {
  $item = $extraVar[$c];
  $style = "";
  $out.= "<tr>";
  for($i=0; $i < count($_KEYS); $i++)
  {
   if($c == 0)
    $_TOTALS[$i] = 0;
   $val = $item[$_KEYS[$i]];
   $value = "";
   if(is_array($val))
   {
	reset($val);
	while(list($k,$v)=each($val))
	{
	 if(!is_array($v))
	 {
	  if(!$ignoreZeroValues || $v) $value.= "<br/>".$k.": ".$v;
	 }
	 else
	 {
	  reset($v);
	  $value.= "<br/>";
	  while(list($k2,$v2)=each($v))
	  {
	   if(!$ignoreZeroValues || $v2) $value.= $k2.": ".$v2." ";
	  }
	 }
	}
	$value = ltrim($value,"<br/>");
   }
   else
   {
    $value = is_array($_FORMATS[$i]) ? tableize_formatValue($val, $_FORMATS[$i]) : $val;
	switch($_FORMATS[$i]['type'])
	{
	 case 'number' : case 'currency' : {
		 $tval = $value;
		 if(!is_numeric($tval))
		 {
		  if(strpos($tval,",") && strpos($tval,"."))
		   $tval = str_replace(".","",$tval);
		  $tval = str_replace(",",".",$tval);
		 } 

		 if($_TOTALS[$i])
		  $_TOTALS[$i]+= $tval;
		 else
		  $_TOTALS[$i]=$tval; 
		} break;
	}
   }
   $style = $twoColors ? "background:".($row ? $bg2 : $bg1).";color:".($row ? $col2 : $col1).";" : "";
   $out.= "<td".($style ? " style=\"".$style."\"" : "").">".($value ? $value : ($fillEmptyCell ? '&nbsp;' : ''))."</td>";
  }
  $out.= "</tr>";
  $row = $row ? 0 : 1; // <-- serve solo per il colore
 }

 if($includeTotals)
 {
  $out.= "<tr>";
  for($c=0; $c < count($_KEYS); $c++)
   $out.= "<td>&nbsp;</td>";
  $out.= "</tr>";

  $out.= "<tr>";
  for($c=0; $c < count($_KEYS); $c++)
  {
   if($_FORMATS[$c]['type'] == "number")
	$out.= "<td align='center'>".$_NAMES[$c]."</td>";
   else if($_FORMATS[$c]['type'] == "currency")
	$out.= "<td align='right'>".$_NAMES[$c]."</td>";
   else
    $out.= "<td>&nbsp;</td>";
  }
  $out.= "</tr>";

  $out.= "<tr>";
  for($c=0; $c < count($_KEYS); $c++)
  {
   if($_FORMATS[$c]['type'] == "number")
	$out.= "<td align='center'>".$_TOTALS[$c]."</td>";
   else if($_FORMATS[$c]['type'] == "currency")
	$out.= "<td align='right'>".number_format($_TOTALS[$c],2,',','.')."</td>";
   else
    $out.= "<td>&nbsp;</td>";  
  }
  $out.= "</tr>"; 
 }

 $out.= "</table>";

 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function tableize_formatValue($val, $format)
{
 if(!is_array($format))
  return $val;
 switch($format['type'])
 {
  case 'time' : case 'date' : case 'datetime' : {
	 if(is_numeric($val)) $time = $val;
	 else $time = strtotime($val);
	 if(!$time) return "";
	 switch($format['type'])
	 {
	  case 'time' : return date('H:i',$time); break;
	  case 'date' : return date('d/m/Y',$time); break;
	  case 'datetime' : return date('d/m/Y H:i',$time); break;
	 }
	} break;

  case 'bool' : return $val ? "SI" : "NO"; break;
  case 'number' : return $val; break;
  case 'currency' : return number_format($val,2,',','.'); break;

  case 'reference' : {
	 if($format['ap'] && $format['ext'])
	 {
	  $ap = $format['ap'];	$ext = $format['ext'];	
	  $retField = $format['retfield'] ? $format['retfield'] : 'name';
	  $retFields = $format['retfields'];
	  $sk = $format['searchfield'] ? $format['searchfield'] : 'id';
	  $db = new AlpaDatabase();
	  $db->RunQuery("SELECT ".($retFields ? $retFields : $retField)." FROM dynarc_".$ap."_".($ext ? $ext : 'items')." WHERE ".$sk."='".$val."'");
	  $db->Read();
	  if($retFields)
	  {
	   $x = explode(",",$retFields);
	   for($c=0; $c < count($x); $c++)
	   {
		if(!$db->record[$x[$c]]) continue;
		$value.= " - ".$db->record[$x[$c]];
	   }
	   $value = ltrim($value, " - ");
	  }
	  else
	   $value = $db->record[$retField];
	  $db->Close();
	  return $value;
	 }

	} break;

  case 'option' : return $format['values'][$val]; break;
  default : return $val; break;
 }
 return $val;
}
//-------------------------------------------------------------------------------------------------------------------//
function tableize_invalidArguments()
{
 $out = "Usage: tableize [options] *\n";
 $out.= "&nbsp;OPTIONS:\n";
 $out.= "&nbsp;-k KEYS\n";
 $out.= "&nbsp;&nbsp;&nbsp;&nbsp;The keys (return fields) must be separated by comma (,) ,semicolon (;) or vertical bar (|).\n\n";
 $out.= "&nbsp;-n NAMES\n";
 $out.= "&nbsp;&nbsp;&nbsp;&nbsp;The names (column titles) must be separated by comma (,) ,semicolon (;) or vertical bar (|).\n\n";
 $out.= "&nbsp;*\n";
 $out.= "&nbsp;&nbsp;&nbsp;&nbsp;The asterisk is to identify the results of a previous command.\n\n";
 $out.= "&nbsp;EXAMPLE:\n";
 $out.= "dynarc item-list -ap rubrica -ct customers -extget rubricainfo -limit 10 --order-by `name ASC` || tableize -k `code_str,name,taxcode,vatnumber` -n `COD.|DENOMINAZIONE|COD. FISCALE|P. IVA` *.items\n";

 return array('message'=>$out, 'error'=>'INVALID_ARGUMENTS');
}
//-------------------------------------------------------------------------------------------------------------------//

