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
 #TODO:
 
*/

// Standard inclusions   
 include("pChart/pData.class");
 include("pChart/pChart.class");

 // DEFAULT VARIABLES //
 $chWidth = 700;
 $chHeight = 230;
 $chType = "cc"; // cubic curve //
 $_MIN_WIDTH = 250;
 $_MIN_HEIGHT = 120;

 // get chart size //
 if($_REQUEST['chs'])
 {
  $wx = explode("x",$_REQUEST['chs']);
  if(count($wx))
  {
   $chWidth = $wx[0];
   $chHeight = $wx[1];
  }
 }

 // get chart type //
 if($_REQUEST['cht'])
  $chType = $_REQUEST['cht'];

 // get chart data //
 if($_REQUEST['chd'])
  $chData = $_REQUEST['chd'];

 // get chart labels //
 if($_REQUEST['chl'])
  $chLabels = $_REQUEST['chl'];
 

 $chOptions = array('legendpos'=>'TR', 'showlegend'=>true, 'showbackground'=>true, 'showlabels'=>true);
 /* GET CHART OPTIONS */
 if($_REQUEST['chlp']) // legend position: (TL = Top Left, TR = Top Right, BL = Bottom Left, BR = Bottom Right).
  $chOptions['legendpos'] = $_REQUEST['chlp'];
 if(isset($_REQUEST['chhl'])) // hide legend 
  $chOptions['showlegend'] = false;
 if(isset($_REQUEST['chlb'])) // hide labels
  $chOptions['showlabels'] = false;
 if($_REQUEST['chbg'] == "false")
  $chOptions['showbackground'] = false;

 /* EOF - GET CHART OPTIONS */


 // Dataset definition 
 $DataSet = new pData;
 if($chLabels)
 {
  $DataSet->AddPoint(explode("|",$chLabels),"Labels");
  $DataSet->SetAbsciseLabelSerie("Labels");
 } 
 if($chData)
 {
  switch(strtoupper($chType))
  {
   case 'BPG' : case 'FPG' : case 'PIE' : {
     /* Single section */
     $labels = explode("|",$chLabels);
     $values = explode(",",$chData);
     $DataSet->AddPoint($values,"PIE CHART");
     $DataSet->AddSerie("PIE CHART");
     for($c=0; $c < count($labels); $c++)
	  $DataSet->SetSerieName($labels[$c], $labels[$c]);
	} break;
  
   default : {
     /* Multi-sections */
     $series = explode("|",$chData);
     if(!count($series))
     {
      $series = array();
      $series[] = $chData;
     }

     for($c=0; $c < count($series); $c++)
     {
      $x = explode(":",$series[$c]);
      $sN = $x[0];
      $sD = explode(",",$x[1]);
      $DataSet->AddPoint($sD,$sN);
      $DataSet->AddSerie($sN);
      $DataSet->SetSerieName($sN,$sN);
     }
	} break;
  }
 }

 // Initialise the graph
 if($chWidth < $_MIN_WIDTH)
  $chWidth = $_MIN_WIDTH;
 if($chHeight < $_MIN_HEIGHT)
  $chHeight = $_MIN_HEIGHT;

 $datasetData = $DataSet->GetData();
 $datasetDesc = $DataSet->GetDataDescription();

 switch(strtoupper($chType))
 {
  case 'BPG' : case 'FPG' : case 'PIE' : GChartDrawPie($chType, $chWidth, $chHeight, $chOptions, $DataSet); break;
  case 'PG' : case 'LG' : case 'FLG' : case 'CC' : case 'FCC' : case 'OBG' : case 'BG' : case 'SBG' : case 'LIG' : GChartDrawBarGraph($chType, $chWidth, $chHeight, $chOptions, $DataSet); break; 
  default : {
	 /* DRAW ALL OTHER */
	 $chart = new pChart($chWidth,$chHeight);
	 $chart->setFontProperties("Fonts/tahoma.ttf",8);
	 $chart->setGraphArea(50,10,$chWidth-125,$chHeight-30);
	 $chart->drawFilledRoundedRectangle(0,0,$chWidth,$chHeight,8,240,240,240);
	 $chart->drawGraphArea(255,255,255,TRUE);
	 $chart->drawScale($datasetData,$datasetDesc,SCALE_NORMAL,150,150,150,TRUE,0,2);
	 $chart->drawGrid(4,TRUE,230,230,230,50);

	 // Draw the 0 line
	 $chart->setFontProperties("Fonts/tahoma.ttf",6);
	 //$chart->drawTreshold(100,143,55,72,TRUE,TRUE);
	 switch(strtoupper($chType))
	 {
	  case "RAD" : $chart->drawRadar($datasetData,$datasetDesc); break;
	  case "FRAD" : $chart->drawFilledRadar($datasetData,$datasetDesc); break;
	 }
	 // Finish the graph
	 $chart->setFontProperties("Fonts/tahoma.ttf",8);
	 $chart->drawLegend($chWidth-110,10,$datasetDesc,255,255,255);
	 $chart->Stroke();
	} break;
 }

//-------------------------------------------------------------------------------------------------------------------//
function GChartDrawPie($chType, $chWidth, $chHeight, $chOptions, $DataSet)
{
 /* DRAW A PIE GRAPH */
 $chart = new pChart($chWidth,$chHeight);
 $chart->setFontProperties("Fonts/tahoma.ttf",8);
 $chart->setGraphArea(50,10,$chWidth-125,$chHeight-30);
 if($chOptions['showbackground'] == true)
  $chart->drawFilledRoundedRectangle(0,0,$chWidth,$chHeight,8,240,240,240);
 $chart->setFontProperties("Fonts/tahoma.ttf",6);

 if($chOptions['showlegend'] == true)
 {
  // Detect width and height for legend //
  $LegendMaxWidth = 0; $LegendMaxHeight = 8;
  $DataDescription = $DataSet->GetDataDescription();
  $chart->validateDataDescription("drawLegend",$DataDescription);
  foreach($DataDescription["Description"] as $Key => $Value)
  {
   $Position   = imageftbbox($chart->FontSize,0,$chart->FontName,$Value);
   $TextWidth  = $Position[2]-$Position[0];
   $TextHeight = $Position[1]-$Position[7];
   if ( $TextWidth > $LegendMaxWidth) { $LegendMaxWidth = $TextWidth; }
   $LegendMaxHeight = $LegendMaxHeight + $TextHeight + 4;
  }
  $LegendMaxHeight = $LegendMaxHeight - 5;
  $LegendMaxWidth  = $LegendMaxWidth + 32;
  $LegendMaxWidth = floor($LegendMaxWidth + (($LegendMaxWidth/100)*25));
  $LegendMaxHeight = floor($LegendMaxHeight + (($LegendMaxHeight/100)*25));
 }

 switch(strtoupper($chType))
 {
  case "BPG" : case "FPG" : {
	 /* Detect radius from sizes */
	 if($chOptions['showlegend'])
	 {
	  switch(strtoupper($chOptions['legendpos']))
	  {
	   case 'TL' : {
		 $d = $chWidth-$LegendMaxWidth-20-80-80;
		 $r = floor($d/2);
		 $cX = 10+$LegendMaxWidth+80+$r;
		 $cY = $chHeight-30-$r;

		 if((($r*2)+60) > $chHeight)
		 {
		  $d = $chHeight-60;
		  $r = floor($d/2);
		  $cX = 10+$LegendMaxWidth+floor(($chWidth-$LegendMaxWidth-20)/2); // centrato
		  $cY = $chHeight-30-$r;
		 }
		} break;
	   case 'TR' : {
		 $d = $chWidth-$LegendMaxWidth-20-80-80;
		 $r = floor($d/2);
		 $cX = 80+$r;
		 $cY = $chHeight-30-$r;

		 if((($r*2)+60) > $chHeight)
		 {
		  $d = $chHeight-60;
		  $r = floor($d/2);
		  $cX = floor(($chWidth-$LegendMaxWidth-20)/2); // centrato
		  $cY = $chHeight-30-$r;
		 }
		} break;
	   case 'BL' : {
		 $d = $chWidth-$LegendMaxWidth-20-80-80;
		 $r = floor($d/2);
		 $cX = 10+$LegendMaxWidth+80+$r;
		 $cY = 30+$r;

		 if((($r*2)+60) > $chHeight)
		 {
		  $d = $chHeight-60;
		  $r = floor($d/2);
		  $cX = 10+$LegendMaxWidth+floor(($chWidth-$LegendMaxWidth-20)/2); // centrato
		  $cY = 30+$r;
		 }
		} break;
	   case 'BR' : {
		 $d = $chWidth-$LegendMaxWidth-20-80-80;
		 $r = floor($d/2);
		 $cX = 80+$r;
		 $cY = 30+$r;

		 if((($r*2)+60) > $chHeight)
		 {
		  $d = $chHeight-60;
		  $r = floor($d/2);
		  $cX = floor(($chWidth-$LegendMaxWidth-20)/2); // centrato
		  $cY = 30+$r;
		 }
		} break;
	  }
	 }
	 else
	 {
	  $d = $chWidth-80-80;
	  $r = floor($d/2);
	  if((($r*2)+60) > $chHeight)
	  {
	   $d = $chHeight-60;
	   $r = floor($d/2);
	  }
	  $cX = floor($chWidth/2); // centrato
	  $cY = floor($chHeight/2); // centrato
	 }

	 $datasetData = $DataSet->GetData();
	 $datasetDesc = $DataSet->GetDataDescription();

	 if(strtoupper($chType) == "BPG")
	  $chart->drawBasicPieGraph($datasetData,$datasetDesc,$cX, $cY, $r, $chOptions['showlabels'] ? PIE_LABELS : PIE_NOLABEL);
	 else
	  $chart->drawFlatPieGraph($datasetData,$datasetDesc,$cX, $cY, $r, $chOptions['showlabels'] ? PIE_LABELS : PIE_NOLABEL);
	} break;

  case "PIE" : {
	 /* Detect radius from sizes */
	 if($chOptions['showlegend'])
	 {
	  switch(strtoupper($chOptions['legendpos']))
	  {
	   case 'TL' : {
		 $d1 = $chWidth-$LegendMaxWidth-20-80-80;
		 $d2 = floor(($d1/100)*60);
		 $r = floor($d1/2);
		 $r2 = floor($d2/2);
		 $cX = 10+$LegendMaxWidth+80+$r;
		 $cY = $chHeight-30-$r2;

		 if((($r2*2)+60) > $chHeight)
		 {
		  $d2 = $chHeight-60;
		  $d1 = floor(($d2/60)*100);
		  $r = floor($d1/2);
		  $r2 = floor($d2/2);
		  $cX = 10+$LegendMaxWidth+floor(($chWidth-$LegendMaxWidth-20)/2); // centrato
		  $cY = $chHeight-30-$r2;
		 }
		} break;
	   case 'TR' : {
		 $d1 = $chWidth-$LegendMaxWidth-20-80-80;
		 $d2 = floor(($d1/100)*60);
		 $r = floor($d1/2);
		 $r2 = floor($d2/2);
		 $cX = 80+$r;
		 $cY = $chHeight-30-$r2;

		 if((($r2*2)+60) > $chHeight)
		 {
		  $d2 = $chHeight-60;
		  $d1 = floor(($d2/60)*100);
		  $r = floor($d1/2);
		  $r2 = floor($d2/2);
		  $cX = floor(($chWidth-$LegendMaxWidth-20)/2); // centrato
		  $cY = $chHeight-30-$r2;
		 }
		} break;
	   case 'BL' : {
		 $d1 = $chWidth-$LegendMaxWidth-20-80-80;
		 $d2 = floor(($d1/100)*60);
		 $r = floor($d1/2);
		 $r2 = floor($d2/2);
		 $cX = 10+$LegendMaxWidth+80+$r;
		 $cY = 30+$r2;

		 if((($r2*2)+60) > $chHeight)
		 {
		  $d2 = $chHeight-60;
		  $d1 = floor(($d2/60)*100);
		  $r = floor($d1/2);
		  $r2 = floor($d2/2);
		  $cX = 10+$LegendMaxWidth+floor(($chWidth-$LegendMaxWidth-20)/2); // centrato
		  $cY = 30+$r2;
		 }
		} break;
	   case 'BR' : {
		 $d1 = $chWidth-$LegendMaxWidth-20-80-80;
		 $d2 = floor(($d1/100)*60);
		 $r = floor($d1/2);
		 $r2 = floor($d2/2);
		 $cX = 80+$r;
		 $cY = 30+$r2;

		 if((($r2*2)+60) > $chHeight)
		 {
		  $d2 = $chHeight-60;
		  $d1 = floor(($d2/60)*100);
		  $r = floor($d1/2);
		  $r2 = floor($d2/2);
		  $cX = floor(($chWidth-$LegendMaxWidth-20)/2); // centrato
		  $cY = 30+$r2;
		 }
		} break;
	  }
	 }
	 else
	 {
	  $d1 = $chWidth-80-80;
	  $d2 = floor(($d1/100)*60);
	  $r = floor($d1/2);
	  $r2 = floor($d2/2);

	  if((($r2*2)+60) > $chHeight)
	  {
	   $d2 = $chHeight-60;
	   $d1 = floor(($d2/60)*100);
	   $r = floor($d1/2);
	   $r2 = floor($d2/2);
	  }
	  $cX = floor($chWidth/2); // centrato
	  $cY = floor($chHeight/2)-10; // centrato
	 }
	 $datasetData = $DataSet->GetData();
	 $datasetDesc = $DataSet->GetDataDescription();
	 $chart->drawPieGraph($datasetData,$datasetDesc,$cX, $cY, $r, $chOptions['showlabels'] ? PIE_LABELS : PIE_NOLABEL); 
	} break;
 }

 $chart->setFontProperties("Fonts/tahoma.ttf",8);
 $datasetData = $DataSet->GetData();
 $datasetDesc = $DataSet->GetDataDescription();
 if($chOptions['showlegend'] == true)
 {
  switch(strtoupper($chOptions['legendpos']))
  {
   case 'TL' : $chart->drawLegend(10,10,$datasetDesc,255,255,255); break;
   case 'TR' : $chart->drawLegend($chWidth-$LegendMaxWidth-10,10,$datasetDesc,255,255,255); break;
   case 'BL' : $chart->drawLegend(10,$chHeight-$LegendMaxHeight-10,$datasetDesc,255,255,255); break;
   case 'BR' : $chart->drawLegend($chWidth-$LegendMaxWidth-10,$chHeight-$LegendMaxHeight-10,$datasetDesc,255,255,255); break;
  }
 }
 //$chart->drawTitle(50,22,$r." x ".$r2." : ".$r3,50,50,50,585);
 $chart->Stroke();

}
//-------------------------------------------------------------------------------------------------------------------//
function GChartDrawBarGraph($chType, $chWidth, $chHeight, $chOptions, $DataSet)
{
 $chart = new pChart($chWidth,$chHeight); //$Test = new pChart(700,230);
 $chart->setFontProperties("Fonts/tahoma.ttf",8);

 $LegendMaxWidth = 0; $LegendMaxHeight = 8;
 if($chOptions['showlegend'] == true)
 {
  // Detect width and height for legend //
  $DataDescription = $DataSet->GetDataDescription();
  $chart->validateDataDescription("drawLegend",$DataDescription);
  foreach($DataDescription["Description"] as $Key => $Value)
  {
   $Position   = imageftbbox($chart->FontSize,0,$chart->FontName,$Value);
   $TextWidth  = $Position[2]-$Position[0];
   $TextHeight = $Position[1]-$Position[7];
   if ( $TextWidth > $LegendMaxWidth) { $LegendMaxWidth = $TextWidth; }
   $LegendMaxHeight = $LegendMaxHeight + $TextHeight + 4;
  }
  $LegendMaxHeight = $LegendMaxHeight - 5;
  $LegendMaxWidth  = $LegendMaxWidth + 32;
  $LegendMaxWidth = floor($LegendMaxWidth + (($LegendMaxWidth/100)*25));
  $LegendMaxHeight = floor($LegendMaxHeight + (($LegendMaxHeight/100)*25));
 }

 $chart->setGraphArea(50,10,$chWidth-$LegendMaxWidth-10,$chHeight-30);
 if($chOptions['showbackground'] == true)
  $chart->drawFilledRoundedRectangle(0,0,$chWidth,$chHeight,8,240,240,240);
 
 $chart->drawGraphArea(255,255,255,TRUE);
 $datasetData = $DataSet->GetData();
 $datasetDesc = $DataSet->GetDataDescription();
 switch(strtoupper($chType))
 {
  case 'PG' : case 'LG' : case 'FLG' : case 'CC' : case 'FCC' : $chart->drawScale($datasetData,$datasetDesc,SCALE_NORMAL,150,150,150,TRUE,0,2, false); break;
  default : $chart->drawScale($datasetData,$datasetDesc,SCALE_NORMAL,150,150,150,TRUE,0,2,true); break;
 }
 $chart->drawGrid(4,TRUE,230,230,230,50);

 switch(strtoupper($chType))
 {
  case "PG" : $chart->drawPlotGraph($datasetData,$datasetDesc); break;
  case "LG" : $chart->drawLineGraph($datasetData,$datasetDesc); break;
  case "FLG" : $chart->drawFilledLineGraph($datasetData,$datasetDesc); break;
  case "CC" : $chart->drawCubicCurve($datasetData,$datasetDesc); break;
  case "FCC" : $chart->drawFilledCubicCurve($datasetData,$datasetDesc); break;
  case "OBG" : $chart->drawOverlayBarGraph($datasetData,$datasetDesc); break;
  case "BG" : $chart->drawBarGraph($datasetData,$datasetDesc,true, 100); break;
  case "SBG" : $chart->drawStackedBarGraph($datasetData,$datasetDesc); break;
  case "LIG" : $chart->drawLimitsGraph($datasetData,$datasetDesc); break;
 }
 // Finish the graph
 $chart->setFontProperties("Fonts/tahoma.ttf",8);
 $datasetData = $DataSet->GetData();
 $datasetDesc = $DataSet->GetDataDescription();
 if($chOptions['showlegend'] == true)
 {
  switch(strtoupper($chOptions['legendpos']))
  {
   case 'TL' : $chart->drawLegend(10,10,$datasetDesc,255,255,255); break;
   case 'TR' : $chart->drawLegend($chWidth-$LegendMaxWidth,10,$datasetDesc,255,255,255); break;
   case 'BL' : $chart->drawLegend(10,$chHeight-$LegendMaxHeight-10,$datasetDesc,255,255,255); break;
   case 'BR' : $chart->drawLegend($chWidth-$LegendMaxWidth-10,$chHeight-$LegendMaxHeight-10,$datasetDesc,255,255,255); break;
  }  
 }
 $chart->Stroke();
}
//-------------------------------------------------------------------------------------------------------------------//

