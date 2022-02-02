<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-11-2012
 #PACKAGE: dynserppagenav
 #DESCRIPTION: Dynamic SERP page navigator. [this is a obsolete object. use gserppagenav.]
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/objects/dynserppagenav/dynserppagenav.css" type="text/css" />
<?php

class DynSerpPageNav
{
 function DynSerpPageNav($resultsCount=0,$start=0,$resultsPerPage=10)
 {
  $this->ResultsCount = $resultsCount;
  $this->Start = $start;
  $this->ResultsPerPage = $resultsPerPage;
 }

 function mkQry($var,$val)
 {
  $queryArray = $_GET;
  $queryArray[$var] = $val;
  $ret = "";
  while(list($k,$v) = each($queryArray)) 
  {
   $ret.= "&".$k."=".$v;
  }
  return ltrim($ret,"&");
 }

 function Paint($varForStart='start',$func=null)
 {
  $pages = ceil($this->ResultsCount/$this->ResultsPerPage);
  $currentPage = ceil($this->Start/$this->ResultsPerPage)+1;
  if($pages < 2)
   return;
  // back button //
  if($this->Start > 1)
  {
   $back = (floor($this->Start/$this->ResultsPerPage)*$this->ResultsPerPage)-$this->ResultsPerPage;
   if($func)
    echo "<a href='#' onclick='$func(".($back > 0 ? $back : 0).")'>&laquo; Indietro</a>";
   else
    echo "<a href='?".$this->mkQry($varForStart,($back > 0 ? $back : 0))."'>&laquo; Indietro</a>";
  }
  // serp buttons //
  if($currentPage == 1) {$s=0; $e = ($pages < 10 ? $pages : 10);}
  else if($currentPage < 12) {$s=0; $e = ($pages < (9+$currentPage) ? $pages : (9+$currentPage));}
  else if(($currentPage+10) < $pages) {$s=$currentPage-11; $e=$currentPage+9;}
  else {$e=$pages; $s = $e-17;}

  for($c=$s; $c < $e; $c++)
   echo ($c == ($currentPage-1) ? " <span class='selected'>".($c+1)."</span>" : ($func ? " <a href='#' onclick='$func("
	.($c*$this->ResultsPerPage).")'>".($c+1)."</a>" : " <a href='?".$this->mkQry($varForStart,$c*$this->ResultsPerPage)."'>".($c+1)."</a>"));

  // forward button //
  if($currentPage < $pages)
   echo $func ? " <a href='#' onclick='$func(".($currentPage*$this->ResultsPerPage).")'>Avanti &raquo;</a>" : " <a href='?".$this->mkQry($varForStart,$currentPage*$this->ResultsPerPage)."'>Avanti &raquo;</a>";
 }
}

