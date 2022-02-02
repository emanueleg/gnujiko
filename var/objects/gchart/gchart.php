<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 22-02-2012
 #PACKAGE: gchart
 #DESCRIPTION: Dynamic chart using pChart (http://pchart.sourceforge.net)
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO: Manca da fare la classe in JavaScript.
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

?>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/objects/gchart/gchart.js" type="text/javascript"></script>
<?php

/* THIS IS THE CLASS FOR USING GCHART WITH PHP */
class GChart
{
 var $Width;
 var $Height;
 var $Type;
 var $Labels;
 var $Sections;
 var $Values;
 var $Options;
 //------------------------------------------------------------------------------------------------------------------//
 function GChart($width=320,$height=240,$chartType="lg")
 {
  $this->Width = $width;
  $this->Height = $height;
  $this->Type = $chartType;

  $this->Labels = array();
  $this->Sections = array();
  $this->Values = array();
  $this->Options = array();
 }
 //------------------------------------------------------------------------------------------------------------------//
 function AddLabel($lab)
 {
  $this->Labels[] = $lab;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function AddSection($secName="")
 {
  $this->Sections[] = array('name'=>$secName, 'values'=>array());
 }
 //------------------------------------------------------------------------------------------------------------------//
 function AddValue($val="", $secName="")
 {
  if($secName)
  {
   for($c=0; $c < count($this->Sections); $c++)
   {
	if($this->Sections[$c]['name'] == $secName)
	{
	 $this->Sections[$c]['values'][] = $val;
	 break;
	}
   }
  }
  else
   $this->Values[] = $val;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function HideLegend()
 {
  $this->Options['chhl'] = "false";
 }
 //------------------------------------------------------------------------------------------------------------------//
 function HideLabels()
 {
  $this->Options['chlb'] = "false";
 }
 //------------------------------------------------------------------------------------------------------------------//
 function SetLegendPosition($pos)
 {
  $this->Options['chlp'] = $pos;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function HideBackground()
 {
  $this->Options['chbg'] = "false";
 }
 //------------------------------------------------------------------------------------------------------------------//
 function Paint($retURL=false)
 {
  global $_BASE_PATH, $_ABSOLUTE_URL;
  $chs = $this->Width."x".$this->Height;
  $cht = $this->Type;

  /* Labels */
  $chl = "";
  for($c=0; $c < count($this->Labels); $c++)
   $chl.= $this->Labels[$c]."|";
  $chl = rtrim($chl,"|");

  /* Data */
  $chd = "";
  if(count($this->Sections))
  {
   for($c=0; $c < count($this->Sections); $c++)
   {
	$chd.= $this->Sections[$c]['name'].":";
	for($i=0; $i < count($this->Sections[$c]['values']); $i++)
	 $chd.= $this->Sections[$c]['values'][$i].",";
	$chd = rtrim($chd,",")."|";
   }
   $chd = rtrim($chd,"|");
  }
  else
  {
   for($c=0; $c < count($this->Values); $c++)
	$chd.= $this->Values[$c].",";
   $chd = rtrim($chd,",");
  }

  $chopt = "";
  while(list($k,$v) = each($this->Options))
  {
   if($k && $v)
	$chopt.= "&".$k."=".$v;
  }

  $imgURL = $_ABSOLUTE_URL."var/objects/gchart/draw.php?cht=".$cht."&chs=".$chs."&chl=".$chl."&chd=".$chd.$chopt;

  if($retURL)
   return $imgURL;
   
  /* Paint chart */
  echo "<img src='".$imgURL."'/>";
  //echo $_ABSOLUTE_URL."var/objects/gchart/draw.php?cht=".$cht."&chs=".$chs."&chl=".$chl."&chd=".$chd;
 }

}

