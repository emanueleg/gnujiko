<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-10-2016
 #PACKAGE: paymentmodes-config
 #DESCRIPTION: Manage payment modes.
 #VERSION: 2.6beta
 #CHANGELOG: 24-10-2016 : MySQLi integration.
			 02-10-2015 : Aggiunto costi d'incasso.
			 02-03-2015 : Aggiunto campo pa_mode.
			 16-04-2013 : Nuova gestione delle modalità di pagamento.
			 13-01-2013 : Aggiunto 'type' (BB=Bonifico bancario, RB=RiBa, RD=Rimessa diretta)
			 27-06-2012 : Bug fix.
 #DEPENDS: gnujiko-accounting-base
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_paymentmodes($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'new' : return paymentmodes_new($args, $sessid, $shellid); break;
  case 'edit' : return paymentmodes_edit($args, $sessid, $shellid); break;
  case 'delete' : return paymentmodes_delete($args, $sessid, $shellid); break;
  case 'list' : return paymentmodes_list($args, $sessid, $shellid); break;
  case 'info' : return paymentmodes_info($args, $sessid, $shellid); break;
  default : return paymentmodes_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function paymentmodes_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function paymentmodes_new($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-type' : {$type=$args[$c+1]; $c++;} break;
   case '-dateterms' : {$dateTerms=$args[$c+1]; $c++;} break;
   case '-terms' : {$terms=$args[$c+1]; $c++;} break;
   case '-dayafter' : {$dayAfter=$args[$c+1]; $c++;} break;
   case '-collectioncharges' : {$collectionCharges=$args[$c+1]; $c++;} break;
   case '-pamode' : {$paMode=$args[$c+1]; $c++;} break;
   case '--autodetect' : $autodetect=true; break;

   default : {if(!$name) $name=$args[$c];} break;
  }

 if(!$name) return array('message'=>"You must specify a valid name","error"=>"INVALID_NAME");

 if($autodetect)
 {
  $ret = GShell("accounting paymentmodeinfo `".$name."`",$sessid,$shellid);
  if(!$ret['error'])
  {
   $info = $ret['outarr'];
   if(!isset($type)) 				$type = $info['type'];
   if(!isset($terms))				$terms = $info['termstring'];
   if(!isset($dateTerms))			$dateTerms = $info['date_terms'];
   if(!isset($dayAfter))			$dayAfter = $info['day_after'];
   if(!isset($collectionCharges))	$collectionCharges = $info['collection_charges'];
   if(!isset($paMode))				$paMode = $info['pa_mode'];
  }
 }
 
 $db = new AlpaDatabase();
 $db->RunQuery("INSERT INTO payment_modes(name,type,date_terms,deadlines,day_after,pa_mode,collection_charges) VALUES ('"
	.$db->Purify($name)."','".$type."','".$dateTerms."','".$terms."','".$dayAfter."','".$paMode."','".$collectionCharges."')");
 $outArr = array('id'=>$db->GetInsertId(), 'name'=>$name, 'type'=>$type, 'date_terms'=>$dateTerms, 'terms'=>$terms, 'day_after'=>$dayAfter,
	'pa_mode'=>$paMode, 'collection_charges'=>$collectionCharges);
 $db->Close();
 $out = "done!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function paymentmodes_edit($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
   case '-name' : {$name=$args[$c+1]; $c++;} break;
   case '-type' : {$type=$args[$c+1]; $c++;} break;
   case '-dateterms' : {$dateTerms=$args[$c+1]; $c++;} break;
   case '-terms' : {$terms=$args[$c+1]; $c++;} break;
   case '-pamode' : {$paMode=$args[$c+1]; $c++;} break;
   case '-collectioncharges' : {$collectionCharges=$args[$c+1]; $c++;} break;
   case '-dayafter' : {$dayAfter=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify a valid payment mode id","error"=>"INVALID_ITEM");

 $db = new AlpaDatabase();

 $q = "";
 if($name)						$q.= ",name='".$db->Purify($name)."'";
 if(isset($type))				$q.= ",type='".$type."'";
 if(isset($dateTerms))			$q.= ",date_terms='".$dateTerms."'";
 if(isset($terms))				$q.= ",deadlines='".$terms."'";
 if(isset($dayAfter))			$q.= ",day_after='".$dayAfter."'";
 if(isset($paMode))				$q.= ",pa_mode='".$paMode."'";
 if(isset($collectionCharges))	$q.= ",collection_charges='".$collectionCharges."'";

 $db->RunQuery("UPDATE payment_modes SET ".ltrim($q,',')." WHERE id='$id'");
 $outArr = array('id'=>$id, 'name'=>$name, 'type'=>$type, 'date_terms'=>$dateTerms, 'terms'=>$terms, 'day_after'=>$dayAfter, 
	'pa_mode'=>$paMode, 'collection_charges'=>$collectionCharges);
 $db->Close();

 $out = "Payment mode has been updated!";
 return array('message'=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function paymentmodes_delete($args, $sessid, $shellid)
{
 $sessInfo = sessionInfo($sessid);
 if($sessInfo['uname'] != "root")
  return array("message"=>"You must be root", "error"=>"YOU_MUST_BE_ROOT");

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify a valid payment mode id","error"=>"INVALID_ITEM");

 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM payment_modes WHERE id='$id'");
 $db->Close();

 $out.= "Payment mode #".$id." has been removed.";
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function paymentmodes_list($args, $sessid, $shellid)
{
 $orderBy = "id ASC";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
  }

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM payment_modes WHERE 1 ORDER BY ".$orderBy);
 while($db->Read())
 {
  $a = array('id'=>$db->record['id'],'name'=>$db->record['name'],'type'=>$db->record['type'],
	'date_terms'=>$db->record['date_terms'],'terms'=>$db->record['deadlines'],'day_after'=>$db->record['day_after'],
	'pa_mode'=>$db->record['pa_mode'], 'collection_charges'=>$db->record['collection_charges']);

  if(!$a['pa_mode'])
  {
   switch($a['type'])
   {
    case 'RB' : $a['pa_mode'] = "MP12"; break;
    case 'BB' : $a['pa_mode'] = "MP05"; break;
    default : $a['pa_mode'] = "MP01"; break;
   }
  }

  $outArr[] = $a;
 }
 $db->Close();

 $out.= "\n".count($outArr)." payment modes found.\n";
 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function paymentmodes_info($args, $sessid, $shellid)
{
 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$id=$args[$c+1]; $c++;} break;
  }

 if(!$id) return array('message'=>"You must specify a valid payment mode id","error"=>"INVALID_ITEM");

 $db = new AlpaDatabase();
 $db->RunQuery("SELECT * FROM payment_modes WHERE id='".$id."'");
 if(!$db->Read())
  return array("message"=>"Payment mode #".$id." does not exists.","error"=>"ITEM_DOES_NOT_EXISTS");

 $outArr = array('id'=>$db->record['id'],'name'=>$db->record['name'],'type'=>$db->record['type'],
	'date_terms'=>$db->record['date_terms'],'terms'=>$db->record['deadlines'],'day_after'=>$db->record['day_after'],
	'pa_mode'=>$db->record['pa_mode'], 'collection_charges'=>$db->record['collection_charges']);

  if(!$outArr['pa_mode'])
  {
   switch($outArr['type'])
   {
    case 'RB' : $outArr['pa_mode'] = "MP12"; break;
    case 'BB' : $outArr['pa_mode'] = "MP05"; break;
    default : $outArr['pa_mode'] = "MP01"; break;
   }
  }


 $db->Close();

 return array("message"=>"Done!","outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

