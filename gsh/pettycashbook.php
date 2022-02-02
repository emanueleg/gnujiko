<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 10-10-2013
 #PACKAGE: pettycashbook
 #DESCRIPTION: Official Gnujiko Petty Cash Book manager.
 #VERSION: 2.1beta
 #CHANGELOG: 10-10-2013 : Possibilità di filtrare anche per risorsa.
			 19-09-2013 : Bug fix nella funzione pettycashbook list. 
 #DEPENDS:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/userfunc.php");

function shell_pettycashbook($args, $sessid, $shellid=null)
{
 switch($args[0])
 {
  case 'new' : case 'add' : return pettycashbook_new($args, $sessid, $shellid); break;
  case 'edit' : return pettycashbook_edit($args, $sessid, $shellid); break;
  case 'delete' : return pettycashbook_delete($args, $sessid, $shellid); break;
  case 'list' : return pettycashbook_list($args, $sessid, $shellid); break;
  default : return pettycashbook_invalidArguments(); break;
 }

}
//-------------------------------------------------------------------------------------------------------------------//
function pettycashbook_invalidArguments()
{
 return array('message'=>"Invalid arguments",'error'=>"INVALID_ARGUMENTS");
}
//-------------------------------------------------------------------------------------------------------------------//
function pettycashbook_new($args, $sessid, $shellid)
{

}
//-------------------------------------------------------------------------------------------------------------------//
function pettycashbook_edit($args, $sessid, $shellid)
{
}
//-------------------------------------------------------------------------------------------------------------------//
function pettycashbook_delete($args, $sessid, $shellid)
{
 $archivePrefix = "pettycashbook";
 $out = "";
 $outArr = array();
 $_IDS = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-id' : {$_IDS[]=$args[$c+1]; $c++;} break;
  }

 for($c=0; $c < count($_IDS); $c++)
 {
  $ret = GShell("dynarc delete-item -ap `".$archivePrefix."` -id `".$_IDS[$c]."`",$sessid,$shellid);
  if($ret['error'])
   return $ret;
  $outArr['removed'][] = $ret['outarr'];
 }
 
 $out.= "done!";
 return array('message'=>$out,'outarr'=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function pettycashbook_list($args, $sessid, $shellid)
{
 $archivePrefix = "pettycashbook";
 $orderBy = "ctime DESC";
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-ap' : {$archivePrefix=$args[$c+1]; $c++;} break; // The default archive prefix is 'pettycashbook' //

   case '-from' : {$from=strtotime($args[$c+1]); $c++;} break;
   case '-to' : {$to=strtotime($args[$c+1]); $c++;} break;
   case '-subject' : {$subject=$args[$c+1]; $c++;} break;
   case '-subjectid' : {$subjectId=$args[$c+1]; $c++;} break;
   case '-desc' : case '-description' : {$description=$args[$c+1]; $c++;} break;
   case '-cat' : case '-catid' : {$catId=$args[$c+1]; $c++;} break;
   case '-filter' : {$filter=$args[$c+1]; $c++;} break; // must be: in, out or transfers //
   case '-resid' : {$resId=$args[$c+1]; $c++;} break; // resource id, in or out //
   case '-resin' : {$resIn=$args[$c+1]; $c++;} break;
   case '-resout' : {$resOut=$args[$c+1]; $c++;} break;
   case '--order-by' : {$orderBy=$args[$c+1]; $c++;} break;
   case '-limit' : {$limit=$args[$c+1]; $c++;} break;

   case '--get-totals' : $getTotals=true; break;
  }

 $where = "";
 switch($filter)
 {
  case 'in' : $where.= " AND incomes>0 AND expenses=0"; break;
  case 'out' : $where.= " AND expenses>0 AND incomes=0"; break;
  case 'transfers' : $where.= " AND res_in!=0 AND res_out!=0"; break;
 }
 
 if($from)
  $where.= " AND ctime>='".date('Y-m-d H:i',$from)."'";
 if($to)
  $where.= " AND ctime<'".date('Y-m-d H:i',$to)."'";
 if($resId)
  $where.= " AND (res_in='".$resId."' OR res_out='".$resId."')";
 if($subjectId)
  $where.= " AND subject_id='".$subjectId."'";
 else if($subject)
 {
  $where.= " AND (subject_name='".$subject."' OR subject_name LIKE '".$subject."%' OR subject_name LIKE '%"
	.$subject."' OR subject_name LIKE '%".$subject."%')";
 }
 else if($description)
 {
  $where.= " AND (name='".$description."' OR name LIKE '".$description."%' OR name LIKE '%"
	.$description."' OR name LIKE '%".$description."%')";
 }


 // MAKE QUERY //
 $ret = GShell("dynarc item-list -ap `".$archivePrefix."`".($catId ? " -cat `".$catId."`" : " --all-cat")
	.($where ? " -where `".ltrim($where, " AND ")."`" : "")." -extget pettycashbook --order-by `".$orderBy."`"
	.($limit ? " -limit ".$limit : "")." --return-serp-info", $sessid, $shellid);

 $out = $ret['message'];
 $outArr = $ret['outarr'];

 if($getTotals && ($subject || $subjectId || $description || $resIn || $resOut || $catId || $resId))
 {
  $totIncomes = 0;
  $totExpenses = 0;
  $totTransfers = 0;

  // GET FILTERED TOTALS //
  $ret = GShell("dynarc item-list -ap `".$archivePrefix."`".($catId ? " -into `".$catId."`" : " --all-cat")
	.($where ? " -where `".ltrim($where, " AND ")."`" : "")." -get `res_in,res_out,incomes,expenses`", $sessid, $shellid);
  $list = $ret['outarr']['items'];
  for($c=0; $c < count($list); $c++)
  {
   $itm = $list[$c];
   if($itm['res_in'] && $itm['res_out'])
    $totTransfers+= $itm['incomes'];
   else
   {
	$totIncomes+= $itm['incomes'];
	$totExpenses+= $itm['expenses'];
   }
  }

  $outArr['tot_incomes'] = (!$filter || ($filter=='in')) ? $totIncomes : 0;
  $outArr['tot_expenses'] = (!$filter || ($filter=='out')) ? $totExpenses : 0;
  $outArr['tot_transfers'] = (!$filter || ($filter=='transfers')) ? $totTransfers : 0;
 }
 else if($getTotals)
 {
  // GET ALL TOTALS //
  $totIncomes = 0;
  $totExpenses = 0;
  $totTransfers = 0;

  $db = new AlpaDatabase();
  if(!$from && !$to) // get all records //
  {
   $db->RunQuery("SELECT * FROM dynarc_".$archivePrefix."_totals WHERE 1");
   while($db->Read())
   {
    $totIncomes+= $db->record['incomes'];
    $totExpenses+= $db->record['expenses'];
    $totTransfers+= $db->record['transfers'];
   }
   $db->Close();
  }
  else
  {
   $fromDayNum = date('z',$from);
   $fromDay = date('j',$from);
   $fromMonth = date('n',$from);
   $fromYear = date('Y',$from);

   $toDayNum = date('z',$from);
   $toDay = date('j',$from);
   $toMonth = date('n',$from);
   $toYear = date('Y',$from);

   $monthDiff = ($toMonth-$fromMonth) + (12*($toYear-$fromYear));

   if($monthDiff > 1)
   {
	if($fromDay == 1)
	{
	 $db = new AlpaDatabase();
	 $db->RunQuery("SELECT * FROM dynarc_".$archivePrefix."_totals WHERE ref_date='".date('Y-m-01',$from)."'");
	 $db->Read();
     $totIncomes+= $db->record['incomes'];
     $totExpenses+= $db->record['expenses'];
     $totTransfers+= $db->record['transfers'];
	 $db->Close();
	}
	else
	{
	 $db = new AlpaDatabase();
	 $db->RunQuery("SELECT res_in,res_out,incomes,expenses FROM dynarc_".$archivePrefix."_items WHERE ctime>='".date('Y-m-d',$from)."' AND ctime<'"
		.date('Y-m-01',strtotime("+1 month",$from))."'");
	 while($db->Read())
	 {
	  if($db->record['res_in'] && $db->record['res_out'])
	   $totTransfers+= $db->record['incomes'];
	  else
	  {
	   $totIncomes+= $db->record['incomes'];
	   $totExpenses+= $db->record['expenses'];
	  }
	 }
	 $db->Close();
	}

	$db = new AlpaDatabase();
	$db->RunQuery("SELECT * FROM dynarc_".$archivePrefix."_totals WHERE ref_date>='".date('Y-m-01',strtotime("+1 month",$from))."' AND ref_date<'"
		.date('Y-m-01',$to)."'");
	while($db->Read())
	{
     $totIncomes+= $db->record['incomes'];
     $totExpenses+= $db->record['expenses'];
     $totTransfers+= $db->record['transfers'];
	}
	$db->Close();

	if($toDay > 1)
    {
	 $db = new AlpaDatabase();
	 $db->RunQuery("SELECT res_in,res_out,incomes,expenses FROM dynarc_".$archivePrefix."_items WHERE ctime>='".date('Y-m-01',$to)."' AND ctime<'"
		.date('Y-m-d',$to)."'");
	 while($db->Read())
	 {
	  if($db->record['res_in'] && $db->record['res_out'])
	   $totTransfers+= $db->record['incomes'];
	  else
	  {
	   $totIncomes+= $db->record['incomes'];
	   $totExpenses+= $db->record['expenses'];
	  }
	 }
	 $db->Close(); 
    }
   }
   else
   {
    $db = new AlpaDatabase();
	$db->RunQuery("SELECT res_in,res_out,incomes,expenses FROM dynarc_".$archivePrefix."_items WHERE ctime>='".date('Y-m-d',$from)."' AND ctime<'"
		.date('Y-m-d',$to)."'");
	while($db->Read())
	{
	 if($db->record['res_in'] && $db->record['res_out'])
	  $totTransfers+= $db->record['incomes'];
	 else
	 {
	  $totIncomes+= $db->record['incomes'];
	  $totExpenses+= $db->record['expenses'];
	 }
	}
	$db->Close();
   }
  }
  $outArr['tot_incomes'] = (!$filter || ($filter=='in')) ? $totIncomes : 0;
  $outArr['tot_expenses'] = (!$filter || ($filter=='out')) ? $totExpenses : 0;
  $outArr['tot_transfers'] = (!$filter || ($filter=='transfers')) ? $totTransfers : 0;
 }

 return array("message"=>$out,"outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

