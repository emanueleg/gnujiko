<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 24-02-2014
 #PACKAGE: stats
 #DESCRIPTION: Sales statistics service.
 #VERSION: 2.0beta
 #CHANGELOG: 24-02-2014 : Aggiornato makeTables e makeIndex
 #TODO: 
 
*/
//-------------------------------------------------------------------------------------------------------------------//
function gnujikostatservice_sales_info($sessid,$shellid)
{
 $info = array(
	 "name"=>"Statistiche sul venduto"
	);
 return $info;
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikostatservice_sales_enable($sessid,$shellid)
{
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikostatservice_sales_disable($sessid,$shellid)
{

}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikostatservice_sales_makeTables($year)
{
 global $_DATABASE_NAME;

 $db = new AlpaDatabase();
 $qry = "CREATE TABLE IF NOT EXISTS `stats_prodsold_".$year."` (
	`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`ref_ap` VARCHAR(32) NOT NULL,
	`ref_id` INT(11) NOT NULL,
	`ref_cat` INT(11) NOT NULL,
	`ref_sec` INT(11) NOT NULL,";
 for($c=1; $c < 13; $c++)
  $qry.= "`m".$c."_qty` FLOAT NOT NULL,`m".$c."_amount` FLOAT NOT NULL,";
 
 $qry.= "INDEX (`ref_ap`,`ref_id`,`ref_cat`,`ref_sec`))";
 $ret = $db->RunQuery($qry);
 if(!$ret) return array("message"=>"MySQL Error: ".$db->Error, "error"=>"MYSQL_ERROR");

 $ret = $db->RunQuery("CREATE TABLE IF NOT EXISTS `stats_prodsold_daily_".$year."` (
 `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
 `date` DATE NOT NULL ,
 `ref_ap` VARCHAR(32) NOT NULL ,
 `ref_id` INT(11) NOT NULL ,
 `ref_cat` INT(11) NOT NULL ,
 `ref_sec` INT(11) NOT NULL ,
 `qty` FLOAT NOT NULL ,
 `amount` FLOAT NOT NULL ,
 INDEX (`date`,`ref_ap`,`ref_id`,`ref_cat`,`ref_sec`)
 )");
 if(!$ret) return array("message"=>"MySQL Error: ".$db->Error, "error"=>"MYSQL_ERROR");

 $ret = $db->RunQuery("CREATE TABLE IF NOT EXISTS `stats_prodsold_indexes` (
 `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `mtime` DATETIME NOT NULL ,
 `date_from` DATE NOT NULL ,
 `date_to` DATE NOT NULL)");
 if(!$ret) return array("message"=>"MySQL Error: ".$db->Error, "error"=>"MYSQL_ERROR");

 $db->Close();
 return array('message'=>'Done. Main tables has been created!');
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikostatservice_sales_makeIndex($args, $sessid, $shellid)
{
 global $_BASE_PATH, $_DATABASE_NAME;
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$dateFrom=strtotime($args[$c+1]); $c++;} break;
   case '-to' : {$dateTo=strtotime($args[$c+1]); $c++;} break;
  }

 if(!$dateFrom || !$dateTo)
  return array("message"=>"Invalid date range", "error"=>"INVALID_DATE_RANGE");

 $out.= "Make index of sales from ".date('d/m/Y',$dateFrom)." to ".date('d/m/Y',$dateTo)." ...";
 $year = date('Y',$dateFrom);
 $toYear = date('Y',$dateTo);
 if($toYear > ($year+1))
  return array("message"=>"You can indexing a maximum of one year at time","error"=>"DATE_RANGE_EXCEDED");

 /* Empty daily tables */
 $db = new AlpaDatabase();
 $db->RunQuery("DELETE FROM stats_prodsold_daily_".$year." WHERE date>='".date('Y-m-d',$dateFrom)."' AND date<'".date('Y-m-d',$dateTo)."'");
 $db->RunQuery("DELETE FROM stats_prodsold_daily_".$toYear." WHERE date>='".date('Y-m-d',$dateFrom)."' AND date<'".date('Y-m-d',$dateTo)."'");
 $db->Close();

 /* CHECK OR MAKE TABLES */
 $ret = gnujikostatservice_sales_makeTables($year);
 if($ret['error']) return $ret;
 if($toYear != $year)
 {
  $ret = gnujikostatservice_sales_makeTables($toYear);
  if($ret['error']) return $ret;
 }

 /* GET CATEGORIES */
 $catTags = array("INVOICES","DDT","RECEIPTS");
 $catIds = array();
 $catQry = "";
 $db = new AlpaDatabase();
 for($c=0; $c < count($catTags); $c++)
 {
  $db->RunQuery("SELECT id FROM dynarc_commercialdocs_categories WHERE tag='".$catTags[$c]."' AND parent_id='0' AND trash='0'");
  if($db->Read())
  {
   $catIds[] = $db->record['id'];
   $catQry.= " OR cat_id='".$db->record['id']."'";
   $db->RunQuery("SELECT id FROM dynarc_commercialdocs_categories WHERE parent_id='".$db->record['id']."' AND trash='0'");
   while($db->Read())
   {
	$catIds[] = $db->record['id'];
    $catQry.= " OR cat_id='".$db->record['id']."'";
   }
  }
 }
 $db->Close();

 /* MAKE QUERY */
 $query = "SELECT id,ctime FROM dynarc_commercialdocs_items WHERE ctime>='".date('Y-m-d',$dateFrom)."' AND ctime<'".date('Y-m-d',$dateTo)."'";
 $query.= " AND (".ltrim($catQry," OR ").") AND trash='0' AND conv_doc_id='0' AND group_doc_id='0'";

 $db = new AlpaDatabase();
 $db->RunQuery($query);
 $data = array();
 $data2 = array();
 while($db->Read())
 {
  $docInfo = $db->record;
  $docInfo['ctime'] = strtotime($docInfo['ctime']);
  $date = date('Y-m-d',$docInfo['ctime']);
  $year = date('Y',$docInfo['ctime']);
  $month = date('n',$docInfo['ctime']);
  if(!$data[$date])
   $data[$date] = array();
  if(!$data2[$year])
   $data2[$year] = array();

  $db2 = new AlpaDatabase();
  $db2->RunQuery("SELECT ref_ap,ref_id,qty,price,vat_rate,vat_type,discount_perc,discount_inc,discount2,discount3,extra_qty,price_adjust FROM dynarc_commercialdocs_elements WHERE item_id='".$docInfo['id']."'");
  while($db2->Read())
  {
   if(!$db2->record['qty'])
	continue;
   /* detect price */
   if($db2->record['price'])
   {
	$amount = $db2->record['price'];
	if($amount)
	{
	 if($db2->record['discount_perc'])
      $amount-= (($amount/100)*$db2->record['discount_perc']);
     else if($db2->record['discount_inc'])
	  $amount-= $db2->record['discount_inc'];
     if($db2->record['discount2'])
	  $amount-= (($amount/100)*$db2->record['discount2']);
     if($db2->record['discount2'] && $db2->record['discount3'])
	  $amount-= (($amount/100)*$db2->record['discount3']);
	}
    $amount*= ($db2->record['qty'] * ($db2->record['extra_qty'] ? $db2->record['extra_qty'] : 1));
   }
   $ap = $db2->record['ref_ap'];
   $id = $db2->record['ref_id'];
   if(!$ap || !$id)
   {
	$ap = "UNDEFINED";
	$id = 0;
   }
   $qty = $db2->record['qty'];
   if(!$data[$date][$ap])
	$data[$date][$ap] = array();
   if(!$data2[$year][$ap])
	$data2[$year][$ap] = array();
   if(!$data[$date][$ap][$id])
   {
	// get cat-id and section-id //
	$db3 = new AlpaDatabase();
	$db3->RunQuery("SELECT cat_id,hierarchy FROM dynarc_".$ap."_items WHERE id='".$id."'");
	$db3->Read();
	$catId = $db3->record['cat_id'];
	$x = explode(",",ltrim(substr($db3->record['hierarchy'],0,-1),","));
	$db3->Close();
	$secId = $x[0];
	$data[$date][$ap][$id] = array("qty"=>$qty, "amount"=>$amount, "cat_id"=>$catId, "sec_id"=>$secId);
   }
   else
   {
	$data[$date][$ap][$id]['qty']+= $qty;
	$data[$date][$ap][$id]['amount']+= $amount;
   }
   if(!$data2[$year][$ap][$id])
   {
	$data2[$year][$ap][$id] = array("cat_id"=>$data[$date][$ap][$id]['cat_id'], "sec_id"=>$data[$date][$ap][$id]['sec_id'], "months"=>array());
   }
   if(!$data2[$year][$ap][$id]['months'][$month])
    $data2[$year][$ap][$id]['months'][$month] = array("qty"=>$qty, "amount"=>$amount);
   else
   {
	$data2[$year][$ap][$id]['months'][$month]['qty']+= $qty;
	$data2[$year][$ap][$id]['months'][$month]['amount']+= $amount;
   }
  }
  $db2->Close();
 }
 $db->Close();

 /* WRITE TO DATABASE */
 $db = new AlpaDatabase();
 reset($data);
 while(list($date,$arr)=each($data))
 {
  reset($arr);
  while(list($ap,$items)=each($arr))
  {
   reset($items);
   while(list($id,$info)=each($items))
   {
    $db->RunQuery("INSERT INTO stats_prodsold_daily_".date('Y',strtotime($date))."(date,ref_ap,ref_id,ref_cat,ref_sec,qty,amount) VALUES('"
	.$date."','".$ap."','".$id."','".$info['cat_id']."','".$info['sec_id']."','".$info['qty']."','".$info['amount']."')");
   }
  }
 }
 $db->Close();

 $db = new AlpaDatabase();
 reset($data2);
 while(list($year,$arr)=each($data2))
 {
  reset($arr);
  while(list($ap,$items)=each($arr))
  {
   reset($items);
   while(list($id,$info)=each($items))
   {
	reset($info['months']);
    $qry = "INSERT INTO stats_prodsold_".$year."(ref_ap,ref_id,ref_cat,ref_sec";
	while(list($m,$monthInfo)=each($info['months']))
	{
	 $qry.= ",m".$m."_qty,m".$m."_amount";
	}
	$qry.= ") VALUES('".$ap."','".$id."','".$info['cat_id']."','".$info['sec_id']."'";
	reset($info['months']);
	while(list($m,$monthInfo)=each($info['months']))
	{
	 $qry.= ",'".$monthInfo['qty']."','".$monthInfo['amount']."'";
	}
	$qry.= ")";
	$db->RunQuery($qry);
   }
  }
 }
 $db->Close();

 /* UPDATE INDEX */
 $now = date('Y-m-d H:i:s');
 $dFrom = date('Y-m-d',$dateFrom);
 $dTo = date('Y-m-d',$dateTo);
 $db = new AlpaDatabase();
 $db->RunQuery("SELECT id,date_from FROM stats_prodsold_indexes WHERE date_from<='".$dFrom."' AND date_to>'".$dFrom."' AND date_to<='".$dTo."' LIMIT 1");
 if($db->Read())
 {
  $leftId = $db->record['id'];
  $dFrom = date('Y-m-d',strtotime($db->record['date_from']));
  $db->RunQuery("DELETE FROM stats_prodsold_indexes WHERE id='".$leftId."'");
 }
 $db->RunQuery("SELECT id,date_to FROM stats_prodsold_indexes WHERE date_from>='".$dFrom."' AND date_from<'".$dTo."' AND date_to>'".$dTo."' LIMIT 1");
 if($db->Read())
 {
  $rightId = $db->record['id'];
  $dTo = date('Y-m-d',strtotime($db->record['date_to']));
  $db->RunQuery("DELETE FROM stats_prodsold_indexes WHERE id='".$rightId."'");
 }

 // rimuovo tutti gli indici che hanno un range di date all'interno di questo
 $db->RunQuery("DELETE FROM stats_prodsold_indexes WHERE date_from>'".$dFrom."' AND date_to<'".$dTo."'");

 $db->RunQuery("SELECT id FROM stats_prodsold_indexes WHERE date_from>='".$dFrom."' AND date_to<='".$dTo."' LIMIT 1");
 if($db->Read())
  $db->RunQuery("UPDATE stats_prodsold_indexes SET mtime='".$now."',date_from='".$dFrom."',date_to='".$dTo."' WHERE id='".$db->record['id']."'");
 else
  $db->RunQuery("INSERT INTO stats_prodsold_indexes(mtime,date_from,date_to) VALUES('".$now."','".$dFrom."','".$dTo."')");
 $db->Close();

 $out.= "done!";
 return array("message"=>$out);
}
//-------------------------------------------------------------------------------------------------------------------//
function gnujikostatservice_sales_get($args, $sessid, $shellid)
{
 global $_BASE_PATH;
 $out = "";
 $outArr = array();

 for($c=1; $c < count($args); $c++)
  switch($args[$c])
  {
   case '-from' : {$dateFrom=strtotime($args[$c+1]); $c++;} break;
   case '-to' : {$dateTo=strtotime($args[$c+1]); $c++;} break;
   case '-return' : {$retValField=$args[$c+1]; $c++;} break;
  }

 if(!$dateFrom || !$dateTo)
  return array("message"=>"Invalid date range", "error"=>"INVALID_DATE_RANGE");

 $out.= "Get statistics of sales from ".date('d/m/Y',$dateFrom)." to ".date('d/m/Y',$dateTo)." ...";
 $year = date('Y',$dateFrom);
 $toYear = date('Y',$dateTo);
 if($toYear > ($year+1))
  return array("message"=>"You can get the statistics for a maximum of one year at time","error"=>"DATE_RANGE_EXCEDED");


 $stats = new GnujikoStats("prodsold");
 $stats->setIndexFields(array("ref_ap","ref_id","ref_cat","ref_sec"));
 $stats->setRetValFields(array("qty","amount"));
 $ret = $stats->exec($dateFrom, $dateTo, $retValField);

 $out.= "done!";
 $outArr = $ret;

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------//

