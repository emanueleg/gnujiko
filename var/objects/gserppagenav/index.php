<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-01-2012
 #PACKAGE: gserppagenav
 #DESCRIPTION: Gnujiko SERP page navigator
 #VERSION: 2.0
 #CHANGELOG:
 #TODO:
 
*/

global $_ABSOLUTE_URL;

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/objects/gserppagenav/gserppagenav.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/objects/gserppagenav/gserppagenav.js" type="text/javascript"></script>

<?php

class GSERPPageNav
{
 function GSERPPageNav($nItems, $rpp, $maxElms=10, $currPg=1)
 {
  $this->ItemsCount = $nItems;
  $this->ResultsPerPage = $rpp;
  $this->MaxElms = $maxElms;
  $this->CurrentPage = $currPg;
 }

 function Paint($urlVariable="pagenumber")
 {
  $qs = explode("&",$_SERVER['QUERY_STRING']);
  $qv = array();
  $sqs = $eqs = "";
  for($c=0; $c < count($qs); $c++)
  {
   list($n,$v) = explode("=",$qs[$c]);
   if(!$n)
	continue;
   if($n != $urlVariable)
	$eqs.= "&$n".($v ? "=$v" : "");
   else
   {
	$sqs = ltrim($eqs."&$n=","&");
	$eqs = "";
	$ok = true;
   }
  }
  if(!$ok)
  {
   if($sqs!="")
	$sqs.= "&$urlVariable=";
   else
	$sqs = $urlVariable."=";
  }
  echo "<div class='GSERPPageNav'>";
	if($this->CurrentPage <= 1)
	 echo "<span class='nextprev'>&laquo; Previous</span>";
	else
	 echo "<a href='?".$sqs.($this->CurrentPage-1).$eqs."'>&laquo; Previous</a>";
	$pgs = ceil($this->ItemsCount/$this->ResultsPerPage);
	$cc = ($this->Pages < $this->MaxElms) ? $pgs : $this->MaxElms;
	for($c=0; $c < $cc; $c++)
	{
	 if($this->CurrentPage-1 == $c)
	  echo "<span class='current'>".($c+1)."</span>";
	 else
	  echo "<a href='?".$sqs.($c+1).$eqs."'>".($c+1)."</a>";
	}
   if($this->CurrentPage >= $pgs)
    echo "<span class='nextprev'>Next &raquo;</span>";
   else
	echo "<a class='nextprev' href='?".$sqs.($this->CurrentPage+1).$eqs."'>Next &raquo;</a>";
  echo "</div>";
 }
}
