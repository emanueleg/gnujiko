<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 16-10-2013
 #PACKAGE: stats
 #DESCRIPTION: Official Gnujiko statistics class and utility
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
include_once($_BASE_PATH."include/i18n.php");
LoadLanguage("calendar");

class GnujikoStats
{
 var $tableName;
 var $indexFields;
 var $retValFields;

 function GnujikoStats($tableName)
 {
  $this->tableName = $tableName;
 }

 function setIndexFields($fields)
 {
  $this->indexFields = $fields;
 }

 function setRetValFields($fields)
 {
  $this->retValFields = $fields;
 }

 function getDailyResults($dateFrom, $dateTo, $where="")
 {
  $dateTo = strtotime("+1 day",$dateTo);
  $yearFrom = date("Y",$dateFrom);
  $yearTo = date("Y",$dateTo);
  $results = array();
  $sortByRelevance = array("archives"=>array(), "sections"=>array(), "categories"=>array(), "items"=>array());
  $itemsByCat = array();
  $itemsBySec = array();
  $catBySec = array();

  for($j=0; $j < (($yearFrom != $yearTo) ? 2 : 1); $j++)
  {
   $year = ($j == 0) ? $yearFrom : $yearTo;
   $query = "SELECT * FROM stats_".$this->tableName."_daily_".$year." WHERE";
   $query.= " date>='".date('Y-m-d',$dateFrom)."' AND date<'".date('Y-m-d',$dateTo)."'";
   if($where)
    $query.= " AND (".$where.")";
   $query.= " ORDER BY date ASC";

   $db = new AlpaDatabase();
   $db->RunQuery($query);
   while($db->Read())
   {
    $ap = $db->record['ref_ap'];
    if(!$results[$ap])
     $results[$ap] = array("sec"=>array(), "cat"=>array(), "itm"=>array());

    $date = $db->record['date'];
    $sec = $db->record['ref_sec'];
    $cat = $db->record['ref_cat'];
    $itm = $db->record['ref_id'];

	if(!$itemsByCat[$ap][$cat]) $itemsByCat[$ap][$cat] = array();
	if(!in_array($itm, $itemsByCat[$ap][$cat]))	$itemsByCat[$ap][$cat][] = $itm;
	if(!$itemsBySec[$ap][$sec]) $itemsBySec[$ap][$sec] = array();
	if(!in_array($itm, $itemsBySec[$ap][$sec]))	$itemsBySec[$ap][$sec][] = $itm;
	if(!$catBySec[$ap][$sec]) $catBySec[$ap][$sec] = array();
	if(!in_array($cat, $catBySec[$ap][$sec])) $catBySec[$ap][$sec][] = $cat;

    if(!$results[$ap]['sec'][$sec]) $results[$ap]['sec'][$sec] = array("dates"=>array(), "totals"=>array());
    if(!$results[$ap]['cat'][$cat]) $results[$ap]['cat'][$cat] = array("dates"=>array(), "totals"=>array());
    if(!$results[$ap]['itm'][$itm]) $results[$ap]['itm'][$itm] = array("dates"=>array(), "totals"=>array());

    if(!$results[$ap]['sec'][$sec]['dates'][$date]) $results[$ap]['sec'][$sec]['dates'][$date] = array();
    if(!$results[$ap]['cat'][$cat]['dates'][$date]) $results[$ap]['cat'][$cat]['dates'][$date] = array();
    if(!$results[$ap]['itm'][$itm]['dates'][$date]) $results[$ap]['itm'][$itm]['dates'][$date] = array();

    for($c=0; $c < count($this->retValFields); $c++)
    {
	 $f = $this->retValFields[$c];

	 $sortByRelevance['archives'][$f][$ap] = $sortByRelevance['archives'][$f][$ap] ? $sortByRelevance['archives'][$f][$ap]+$db->record[$f] : $db->record[$f];

	 $results[$ap]['sec'][$sec]['dates'][$date][$f] = $results[$ap]['sec'][$sec]['dates'][$date][$f] ? $results[$ap]['sec'][$sec]['dates'][$date][$f]+$db->record[$f] : $db->record[$f];
	 $results[$ap]['sec'][$sec]['totals'][$f] = $results[$ap]['sec'][$sec]['totals'][$f] ? $results[$ap]['sec'][$sec]['totals'][$f]+$db->record[$f] : $db->record[$f];
	 $sortByRelevance['sections'][$f][$ap."-".$sec] = $sortByRelevance['sections'][$f][$ap."-".$sec] ? $sortByRelevance['sections'][$f][$ap."-".$sec]+$db->record[$f] : $db->record[$f];

	 $results[$ap]['cat'][$cat]['dates'][$date][$f] = $results[$ap]['cat'][$cat]['dates'][$date][$f] ? $results[$ap]['cat'][$cat]['dates'][$date][$f]+$db->record[$f] : $db->record[$f];
	 $results[$ap]['cat'][$cat]['totals'][$f] = $results[$ap]['cat'][$cat]['totals'][$f] ? $results[$ap]['cat'][$cat]['totals'][$f]+$db->record[$f] : $db->record[$f];
	 $sortByRelevance['categories'][$f][$ap."-".$cat] = $sortByRelevance['categories'][$f][$ap."-".$cat] ? $sortByRelevance['categories'][$f][$ap."-".$cat]+$db->record[$f] : $db->record[$f];

	 $results[$ap]['itm'][$itm]['dates'][$date][$f] = $results[$ap]['itm'][$itm]['dates'][$date][$f] ? $results[$ap]['itm'][$itm]['dates'][$date][$f]+$db->record[$f] : $db->record[$f];
	 $results[$ap]['itm'][$itm]['totals'][$f] = $results[$ap]['itm'][$itm]['totals'][$f] ? $results[$ap]['itm'][$itm]['totals'][$f]+$db->record[$f] : $db->record[$f];
	 $sortByRelevance['items'][$f][$ap."-".$itm] = $sortByRelevance['items'][$f][$ap."-".$itm] ? $sortByRelevance['items'][$f][$ap."-".$itm]+$db->record[$f] : $db->record[$f];
    }
   }
   $db->Close();
  }

  /* SORT BY RELEVANCE */
  for($c=0; $c < count($this->retValFields); $c++)
  {
   $f = $this->retValFields[$c];
   if($sortByRelevance['archives'][$f])
    arsort($sortByRelevance['archives'][$f]);
   if($sortByRelevance['sections'][$f])
    arsort($sortByRelevance['sections'][$f]);
   if($sortByRelevance['categories'][$f])
    arsort($sortByRelevance['categories'][$f]);
   if($sortByRelevance['items'][$f])
    arsort($sortByRelevance['items'][$f]);
  }
  /* RETURN */
  return array("results"=>$results, "sortbyrelevance"=>$sortByRelevance, "itemsbycat"=>$itemsByCat, "itemsbysec"=>$itemsBySec, "catbysec"=>$catBySec);
 }

 function getWeeklyResults($dateFrom, $dateTo, $where="")
 {
  $yearFrom = date("Y",$dateFrom);
  $yearTo = date("Y",$dateTo);

  $dateTo = strtotime("+1 day",$dateTo);
  $results = array();
  $sortByRelevance = array("archives"=>array(), "sections"=>array(), "categories"=>array(), "items"=>array());
  $itemsByCat = array();
  $itemsBySec = array();
  $catBySec = array();

  for($j=0; $j < (($yearFrom != $yearTo) ? 2 : 1); $j++)
  {
   $year = ($j == 0) ? $yearFrom : $yearTo;
   $query = "SELECT * FROM stats_".$this->tableName."_daily_".$year." WHERE";
   $query.= " date>='".date('Y-m-d',$dateFrom)."' AND date<'".date('Y-m-d',$dateTo)."'";
   if($where)
    $query.= " AND (".$where.")";
   $query.= " ORDER BY date ASC";
  
   $db = new AlpaDatabase();
   $db->RunQuery($query);
   while($db->Read())
   {
    $ap = $db->record['ref_ap'];
    if(!$results[$ap])
     $results[$ap] = array("sec"=>array(), "cat"=>array(), "itm"=>array());

    $week = date("W",strtotime($db->record['date']));
    $sec = $db->record['ref_sec'];
    $cat = $db->record['ref_cat'];
    $itm = $db->record['ref_id'];

	if(!$itemsByCat[$ap][$cat]) $itemsByCat[$ap][$cat] = array();
	if(!in_array($itm, $itemsByCat[$ap][$cat]))	$itemsByCat[$ap][$cat][] = $itm;
	if(!$itemsBySec[$ap][$sec]) $itemsBySec[$ap][$sec] = array();
	if(!in_array($itm, $itemsBySec[$ap][$sec]))	$itemsBySec[$ap][$sec][] = $itm;
	if(!$catBySec[$ap][$sec]) $catBySec[$ap][$sec] = array();
	if(!in_array($cat, $catBySec[$ap][$sec])) $catBySec[$ap][$sec][] = $cat;


    if(!$results[$ap]['sec'][$sec]) $results[$ap]['sec'][$sec] = array("weeks"=>array(), "totals"=>array());
    if(!$results[$ap]['cat'][$cat]) $results[$ap]['cat'][$cat] = array("weeks"=>array(), "totals"=>array());
    if(!$results[$ap]['itm'][$itm]) $results[$ap]['itm'][$itm] = array("weeks"=>array(), "totals"=>array());

    if(!$results[$ap]['sec'][$sec]['weeks'][$week]) $results[$ap]['sec'][$sec]['weeks'][$week] = array();
    if(!$results[$ap]['cat'][$cat]['weeks'][$week]) $results[$ap]['cat'][$cat]['weeks'][$week] = array();
    if(!$results[$ap]['itm'][$itm]['weeks'][$week]) $results[$ap]['itm'][$itm]['weeks'][$week] = array();

    for($c=0; $c < count($this->retValFields); $c++)
    {
	 $f = $this->retValFields[$c];

	 $sortByRelevance['archives'][$f][$ap] = $sortByRelevance['archives'][$f][$ap] ? $sortByRelevance['archives'][$f][$ap]+$db->record[$f] : $db->record[$f];

	 $results[$ap]['sec'][$sec]['weeks'][$week][$f] = $results[$ap]['sec'][$sec]['weeks'][$week][$f] ? $results[$ap]['sec'][$sec]['weeks'][$week][$f]+$db->record[$f] : $db->record[$f];
	 $results[$ap]['sec'][$sec]['totals'][$f] = $results[$ap]['sec'][$sec]['totals'][$f] ? $results[$ap]['sec'][$sec]['totals'][$f]+$db->record[$f] : $db->record[$f];
	 $sortByRelevance['sections'][$f][$ap."-".$sec] = $sortByRelevance['sections'][$f][$ap."-".$sec] ? $sortByRelevance['sections'][$f][$ap."-".$sec]+$db->record[$f] : $db->record[$f];

	 $results[$ap]['cat'][$cat]['weeks'][$week][$f] = $results[$ap]['cat'][$cat]['weeks'][$week][$f] ? $results[$ap]['cat'][$cat]['weeks'][$week][$f]+$db->record[$f] : $db->record[$f];
	 $results[$ap]['cat'][$cat]['totals'][$f] = $results[$ap]['cat'][$cat]['totals'][$f] ? $results[$ap]['cat'][$cat]['totals'][$f]+$db->record[$f] : $db->record[$f];
	 $sortByRelevance['categories'][$f][$ap."-".$cat] = $sortByRelevance['categories'][$f][$ap."-".$cat] ? $sortByRelevance['categories'][$f][$ap."-".$cat]+$db->record[$f] : $db->record[$f];

	 $results[$ap]['itm'][$itm]['weeks'][$week][$f] = $results[$ap]['itm'][$itm]['weeks'][$week][$f] ? $results[$ap]['itm'][$itm]['weeks'][$week][$f]+$db->record[$f] : $db->record[$f];
	 $results[$ap]['itm'][$itm]['totals'][$f] = $results[$ap]['itm'][$itm]['totals'][$f] ? $results[$ap]['itm'][$itm]['totals'][$f]+$db->record[$f] : $db->record[$f];
	 $sortByRelevance['items'][$f][$ap."-".$itm] = $sortByRelevance['items'][$f][$ap."-".$itm] ? $sortByRelevance['items'][$f][$ap."-".$itm]+$db->record[$f] : $db->record[$f];
    }
   }
   $db->Close();
  }

  /* SORT BY RELEVANCE */
  for($c=0; $c < count($this->retValFields); $c++)
  {
   $f = $this->retValFields[$c];
   if($sortByRelevance['archives'][$f])
    arsort($sortByRelevance['archives'][$f]);
   if($sortByRelevance['sections'][$f])
    arsort($sortByRelevance['sections'][$f]);
   if($sortByRelevance['categories'][$f])
    arsort($sortByRelevance['categories'][$f]);
   if($sortByRelevance['items'][$f])
    arsort($sortByRelevance['items'][$f]);
  }
  /* RETURN */
  return array("results"=>$results, "sortbyrelevance"=>$sortByRelevance, "itemsbycat"=>$itemsByCat, "itemsbysec"=>$itemsBySec, "catbysec"=>$catBySec);
 }

 function getMonthlyResults($monthFrom, $yearFrom, $monthTo, $yearTo, $where="")
 {
  $results = array();
  $sortByRelevance = array("archives"=>array(), "sections"=>array(), "categories"=>array(), "items"=>array());
  $itemsByCat = array();
  $itemsBySec = array();
  $catBySec = array();

  $mFrom = $monthFrom;
  $mTo = $monthTo;

  for($j=0; $j < (($yearFrom != $yearTo) ? 2 : 1); $j++)
  {
   if($yearFrom != $yearTo){if($j == 0) {$mFrom=$monthFrom; $mTo=12;} else {$mFrom=1; $mTo=$monthTo;}}
   $year = ($j == 0) ? $yearFrom : $yearTo;
   $query = "SELECT ";
   $q = "";
   $whq = "";
   for($c=0; $c < count($this->indexFields); $c++)
    $q.= ",".$this->indexFields[$c];
   for($m=$mFrom; $m < ($mTo)+1; $m++)
   {
    $whq.= " OR (";
    for($c=0; $c < count($this->retValFields); $c++)
    {
	 $q.= ",m".$m."_".$this->retValFields[$c];
     $whq.= ($c>0 ? " OR " : "")."m".$m."_".$this->retValFields[$c]."!=0";
    }
    $whq.= ")";
   }
   $query.= ltrim($q,",");

   $query.= " FROM stats_".$this->tableName."_".$year." WHERE ".ltrim($whq," OR ").($where ? " AND (".$where.")" : "");

   $db = new AlpaDatabase();
   $db->RunQuery($query);
   while($db->Read())
   {
    $ap = $db->record['ref_ap'];
    if(!$results[$ap])
     $results[$ap] = array("sec"=>array(), "cat"=>array(), "itm"=>array());

    $sec = $db->record['ref_sec'];
    $cat = $db->record['ref_cat'];
    $itm = $db->record['ref_id'];

	if(!$itemsByCat[$ap][$cat]) $itemsByCat[$ap][$cat] = array();
	if(!in_array($itm, $itemsByCat[$ap][$cat]))	$itemsByCat[$ap][$cat][] = $itm;
	if(!$itemsBySec[$ap][$sec]) $itemsBySec[$ap][$sec] = array();
	if(!in_array($itm, $itemsBySec[$ap][$sec]))	$itemsBySec[$ap][$sec][] = $itm;
	if(!$catBySec[$ap][$sec]) $catBySec[$ap][$sec] = array();
	if(!in_array($cat, $catBySec[$ap][$sec])) $catBySec[$ap][$sec][] = $cat;


    if(!$results[$ap]['sec'][$sec]) $results[$ap]['sec'][$sec] = array("months"=>array(), "totals"=>array());
    if(!$results[$ap]['cat'][$cat]) $results[$ap]['cat'][$cat] = array("months"=>array(), "totals"=>array());
    if(!$results[$ap]['itm'][$itm]) $results[$ap]['itm'][$itm] = array("months"=>array(), "totals"=>array());

    for($m=$mFrom; $m < ($mTo)+1; $m++)
    {
     if(!$results[$ap]['sec'][$sec]['months'][$m]) $results[$ap]['sec'][$sec]['months'][$m] = array();
     if(!$results[$ap]['cat'][$cat]['months'][$m]) $results[$ap]['cat'][$cat]['months'][$m] = array();
     if(!$results[$ap]['itm'][$itm]['months'][$m]) $results[$ap]['itm'][$itm]['months'][$m] = array();
     for($c=0; $c < count($this->retValFields); $c++)
     {
	  $f = $this->retValFields[$c];
	  $field = "m".$m."_".$f;
	  $sortByRelevance['archives'][$f][$ap] = $sortByRelevance['archives'][$f][$ap] ? $sortByRelevance['archives'][$f][$ap]+$db->record[$field] : $db->record[$field];

	  $results[$ap]['sec'][$sec]['months'][$m][$f] = $results[$ap]['sec'][$sec]['months'][$m][$f] ? $results[$ap]['sec'][$sec]['months'][$m][$f]+$db->record[$field] : $db->record[$field];
	  $results[$ap]['sec'][$sec]['totals'][$f] = $results[$ap]['sec'][$sec]['totals'][$f] ? $results[$ap]['sec'][$sec]['totals'][$f]+$db->record[$field] : $db->record[$field];
	  $sortByRelevance['sections'][$f][$ap."-".$sec] = $sortByRelevance['sections'][$f][$ap."-".$sec] ? $sortByRelevance['sections'][$f][$ap."-".$sec]+$db->record[$field] : $db->record[$field];

	  $results[$ap]['cat'][$cat]['months'][$m][$f] = $results[$ap]['cat'][$cat]['months'][$m][$f] ? $results[$ap]['cat'][$cat]['months'][$m][$f]+$db->record[$field] : $db->record[$field];
	  $results[$ap]['cat'][$cat]['totals'][$f] = $results[$ap]['cat'][$cat]['totals'][$f] ? $results[$ap]['cat'][$cat]['totals'][$f]+$db->record[$field] : $db->record[$field];
	  $sortByRelevance['categories'][$f][$ap."-".$cat] = $sortByRelevance['categories'][$f][$ap."-".$cat] ? $sortByRelevance['categories'][$f][$ap."-".$cat]+$db->record[$field] : $db->record[$field];

	  $results[$ap]['itm'][$itm]['months'][$m][$f] = $results[$ap]['itm'][$itm]['months'][$m][$f] ? $results[$ap]['itm'][$itm]['months'][$m][$f]+$db->record[$field] : $db->record[$field];
	  $results[$ap]['itm'][$itm]['totals'][$f] = $results[$ap]['itm'][$itm]['totals'][$f] ? $results[$ap]['itm'][$itm]['totals'][$f]+$db->record[$field] : $db->record[$field];
	  $sortByRelevance['items'][$f][$ap."-".$itm] = $sortByRelevance['items'][$f][$ap."-".$itm] ? $sortByRelevance['items'][$f][$ap."-".$itm]+$db->record[$field] : $db->record[$field];
     }
    }
   }
   $db->Close();
  }

  /* SORT BY RELEVANCE */
  for($c=0; $c < count($this->retValFields); $c++)
  {
   $f = $this->retValFields[$c];
   if($sortByRelevance['archives'][$f])
    arsort($sortByRelevance['archives'][$f]);
   if($sortByRelevance['sections'][$f])
    arsort($sortByRelevance['sections'][$f]);
   if($sortByRelevance['categories'][$f])
    arsort($sortByRelevance['categories'][$f]);
   if($sortByRelevance['items'][$f])
    arsort($sortByRelevance['items'][$f]);
  }
  /* RETURN */
  return array("results"=>$results, "sortbyrelevance"=>$sortByRelevance, "itemsbycat"=>$itemsByCat, "itemsbysec"=>$itemsBySec, "catbysec"=>$catBySec);
 }

 function exec($dateFrom, $dateTo, $sortByField="", $where="")
 {
  $dateFrom = strtotime(date('Y-m-d',$dateFrom));
  $dateTo = strtotime(date('Y-m-d',$dateTo));
  $sbField = $sortByField ? $sortByField : $this->retValFields[0];

  $dfMonth = date('n',$dateFrom);	$dtMonth = date('n',$dateTo);
  $dfYear = date('Y',$dateFrom);	$dtYear = date('Y',$dateTo);

  /* calcola le differenze */
  $dayDiff = ($dateTo-$dateFrom)/86400;
  $monthDiff = ($dtMonth-$dfMonth) + (12*($dtYear-$dfYear));
  $yearDiff = $dtYear-$dfYear;

  /* determina il tipo di statistica */
  if($dayDiff < 8)
   $statType = "DAILY";
  else if($dayDiff < 32)
   $statType = "WEEKLY";
  else
   $statType = "MONTHLY";

  $_COLUMNS = array();
  $_SECTIONS = array();

  
  if($statType == "DAILY")  /* --- STATISTICA GIORNALIERA ----------------------------------*/
  {
   $date = $dateFrom;
   while($date <= $dateTo)
   {
	$_COLUMNS[] = array("title"=>i18n("DAY-".date('N',$date))." ".date('d',$date)."/".i18n("MONTHABB-".date('n',$date)), "value"=>date('Y-m-d',$date));
    $date = strtotime("+1 day",$date);
   }
   $ret = $this->getDailyResults($dateFrom, $dateTo, $where);
   $results = $ret['results'];
   $sortByRelevance = $ret['sortbyrelevance'];
   $itemsByCat = $ret['itemsbycat'];
   $itemsBySec = $ret['itemsbysec'];
   $catBySec = $ret['catbysec'];
   
   if(!$sortByRelevance['sections'][$sbField])
    return array("sections"=>$_SECTIONS, "columns"=>$_COLUMNS);
   $db = new AlpaDatabase();
   while(list($k,$v)=each($sortByRelevance['sections'][$sbField]))
   {
    $x = explode("-",$k);
    $ap = $x[0];
    $sec = $x[1];
    if($ap != "UNDEFINED")
    {
     if($sec)
     {
      $db->RunQuery("SELECT name FROM dynarc_".$ap."_categories WHERE id='".$sec."'");
      $db->Read();
      $sectionName = $db->record['name'];
     }
     else
      $sectionName = "Fuori dalle categorie";
    }
    else
     $sectionName = "Non definiti";
    $secInfo = array("ap"=>$ap, "id"=>$sec, "name"=>$sectionName, "values"=>array(), "totals"=>0, "trend"=>0);
    for($c=0; $c < count($_COLUMNS); $c++)
	 $secInfo["values"][] = $results[$ap]['sec'][$sec]['dates'][$_COLUMNS[$c]['value']][$sbField];
	$secInfo["totals"] = $results[$ap]['sec'][$sec]['totals'][$sbField];

	/* Calcolo del trend */
	$startVal = $secInfo["values"][0];	// prendo come riferimento il giorno di partenza //
	$divisor = count($_COLUMNS);
    if(!$startVal) // Se il primo valore è zero, calcola il sucessivo, ma diminuisce di 1 il divisore
	{
	 for($c=1; $c < count($secInfo["values"]); $c++)
	 {
	  $startVal = $secInfo["values"][$c];
	  $divisor--;
	  if($startVal)
	   break;
	 }
	}
	if($startVal)
	{
	 $average = $secInfo["totals"] ? ($secInfo["totals"]/$divisor) : 0;
	 $secInfo["trend"] = (($average-$startVal) / $startVal) * 100;
	}
	$_SECTIONS[] = $secInfo;
   }
   $db->Close();
   return array("sections"=>$_SECTIONS, "columns"=>$_COLUMNS, "results"=>$results, "itemsbycat"=>$itemsByCat, "itemsbysec"=>$itemsBySec, "catbysec"=>$catBySec);
  }
  else if($statType == "WEEKLY")  /* --- STATISTICA SETTIMANALE ----------------------------------*/
  {
   $weekFrom = date("W",$dateFrom);
   $weekTo = date("W",$dateTo);
   
   $date = $dateFrom;
   while($date < $dateTo)
   {
	$_COLUMNS[] = array("title"=>"Week ".date('W',$date), "value"=>date('W',$date));
    $date = strtotime("+1 week",$date);
   }
   $ret = $this->getWeeklyResults($dateFrom, $dateTo, $where);
   $results = $ret['results'];
   $sortByRelevance = $ret['sortbyrelevance'];
   $itemsByCat = $ret['itemsbycat'];
   $itemsBySec = $ret['itemsbysec'];
   $catBySec = $ret['catbysec'];
   
   if(!$sortByRelevance['sections'][$sbField])
    return array("sections"=>$_SECTIONS, "columns"=>$_COLUMNS);
   $db = new AlpaDatabase();
   while(list($k,$v)=each($sortByRelevance['sections'][$sbField]))
   {
    $x = explode("-",$k);
    $ap = $x[0];
    $sec = $x[1];
    if($ap != "UNDEFINED")
    {
     if($sec)
     {
      $db->RunQuery("SELECT name FROM dynarc_".$ap."_categories WHERE id='".$sec."'");
      $db->Read();
      $sectionName = $db->record['name'];
     }
     else
      $sectionName = "Fuori dalle categorie";
    }
    else
     $sectionName = "Non definiti";
    $secInfo = array("ap"=>$ap, "id"=>$sec, "name"=>$sectionName, "values"=>array(), "totals"=>0, "trend"=>0);
    for($c=0; $c < count($_COLUMNS); $c++)
	 $secInfo["values"][] = $results[$ap]['sec'][$sec]['weeks'][$_COLUMNS[$c]['value']][$sbField];
	$secInfo["totals"] = $results[$ap]['sec'][$sec]['totals'][$sbField];

	/* Calcolo del trend */
	$startVal = $secInfo["values"][0];	// prendo come riferimento il giorno di partenza //
	$divisor = count($_COLUMNS);
    if(!$startVal) // Se il primo valore è zero, calcola il sucessivo, ma diminuisce di 1 il divisore
	{
	 for($c=1; $c < count($secInfo["values"]); $c++)
	 {
	  $startVal = $secInfo["values"][$c];
	  $divisor--;
	  if($startVal)
	   break;
	 }
	}
	if($startVal)
	{
	 $average = $secInfo["totals"] ? ($secInfo["totals"]/$divisor) : 0;
	 $secInfo["trend"] = (($average-$startVal) / $startVal) * 100;
	}
	$_SECTIONS[] = $secInfo;
   }
   $db->Close();
   return array("sections"=>$_SECTIONS, "columns"=>$_COLUMNS, "results"=>$results, "itemsbycat"=>$itemsByCat, "itemsbysec"=>$itemsBySec, "catbysec"=>$catBySec);
  }
  else if($statType == "MONTHLY") /* --- STATISTICA MENSILE --------------------------------------*/
  {
   $monthFrom = date("n",$dateFrom);
   $monthTo = date("n",$dateTo);
   $yearFrom = date("Y",$dateFrom);
   $yearTo = date("Y",$dateTo);

   $date = $dateFrom;
   while($date < $dateTo)
   {
	$_COLUMNS[] = array("title"=>i18n("MONTH-".date('n',$date)), "value"=>date('n',$date));
    $date = strtotime("+1 month",$date);
   }
   $ret = $this->getMonthlyResults(date("n",$dateFrom), $yearFrom, date("n",$dateTo), $yearTo, $where);
   $results = $ret['results'];
   $sortByRelevance = $ret['sortbyrelevance'];
   $itemsByCat = $ret['itemsbycat'];
   $itemsBySec = $ret['itemsbysec'];
   $catBySec = $ret['catbysec'];
   
   if(!$sortByRelevance['sections'][$sbField])
    return array("sections"=>$_SECTIONS, "columns"=>$_COLUMNS);
   $db = new AlpaDatabase();
   while(list($k,$v)=each($sortByRelevance['sections'][$sbField]))
   {
    $x = explode("-",$k);
    $ap = $x[0];
    $sec = $x[1];
    if($ap != "UNDEFINED")
    {
     if($sec)
     {
      $db->RunQuery("SELECT name FROM dynarc_".$ap."_categories WHERE id='".$sec."'");
      $db->Read();
      $sectionName = $db->record['name'];
     }
     else
      $sectionName = "Fuori dalle categorie";
    }
    else
     $sectionName = "Non definiti";
    $secInfo = array("ap"=>$ap, "id"=>$sec, "name"=>$sectionName, "values"=>array(), "totals"=>0, "trend"=>0);
    for($c=0; $c < count($_COLUMNS); $c++)
	 $secInfo["values"][] = $results[$ap]['sec'][$sec]['months'][$_COLUMNS[$c]['value']][$sbField];
	$secInfo["totals"] = $results[$ap]['sec'][$sec]['totals'][$sbField];

	/* Calcolo del trend */
	$startVal = $secInfo["values"][0];	// prendo come riferimento il giorno di partenza //
	$divisor = count($_COLUMNS);
    if(!$startVal) // Se il primo valore è zero, calcola il sucessivo, ma diminuisce di 1 il divisore
	{
	 for($c=1; $c < count($secInfo["values"]); $c++)
	 {
	  $startVal = $secInfo["values"][$c];
	  $divisor--;
	  if($startVal)
	   break;
	 }
	}
	if($startVal)
	{
	 $average = $secInfo["totals"] ? ($secInfo["totals"]/$divisor) : 0;
	 $secInfo["trend"] = (($average-$startVal) / $startVal) * 100;
	}
	$_SECTIONS[] = $secInfo;
   }
   $db->Close();
   return array("sections"=>$_SECTIONS, "columns"=>$_COLUMNS, "results"=>$results, "itemsbycat"=>$itemsByCat, "itemsbysec"=>$itemsBySec, "catbysec"=>$catBySec);
  }
  else if($statType == "YEARLY")  /* --- STATISTICA ANNUALE --------------------------------------*/
  {
   /* TODO: in futuro vedere se fare anche statistica annuale */
  }
 }
}
