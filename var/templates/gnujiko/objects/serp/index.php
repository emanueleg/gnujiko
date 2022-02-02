<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2017 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 17-04-2017
 #PACKAGE: gnujiko-template
 #DESCRIPTION: SERP Object.
 #VERSION: 2.1beta
 #CHANGELOG: 17-04-2017 : Bugfix serp.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;

?>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/templates/gnujiko/objects/serp/serp.css" type="text/css" />
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>var/templates/gnujiko/objects/serp/serp.js" type="text/javascript"/></script>
<?php

class SERP
{
 // public
 /*var $ORDER_BY, $ORDER_METHOD, $RPP, $PG;
 var $Results, $MaxPG;*/
 // private
 var $OrderBy, $OrderMethod, $RPP, $PG, $MaxPG;
 var $FixedOrderBy, $FixedOrderMethod;
 var $lastErrorCode, $lastErrorMsg;
 var $Return;


 function SERP()
 {
  $this->OrderBy = "";
  $this->OrderMethod = "ASC";
  $this->RPP = 0;
  $this->PG = 1;
  $this->MaxPG = 1;
  $this->Return = null;

  $this->Results = array("from"=>0, "to"=>0, "count"=>0, "items"=>array());

  // PRIVATE //
  $this->lastErrorCode = "";
  $this->lastErrorMsg = "";
 }

 function setFixedOrder($orderBy, $orderMethod='ASC')
 {
  $this->FixedOrderBy = $orderBy;
  $this->FixedOrderMethod = $orderMethod;
 }
 function setOrderBy($orderBy){$this->OrderBy=$orderBy;}
 function setOrderMethod($orderMethod){$this->OrderMethod=$orderMethod;}
 function setResultsPerPage($rpp){$this->RPP=$rpp;}
 function setCurrentPage($page){$this->PG=$page;}
 function getErrorCode(){return $this->lastErrorCode;}
 function getErrorMessage(){return $this->lastErrorMsg;}
 function getSerpResultsString(){
	 if($this->Results['count'] < 1) return "0-0 di 0";
	 return $this->Results['from']."-".$this->Results['to']." di ".$this->Results['count'];
	}

 /* PRIVATE FUNCTIONS ---------------------------------------------------------------------------------------------- */
 function returnError($ret){$this->lastErrorCode=$ret['error']; $this->lastErrorMsg=$ret['message']; return false;}

 /* PUBLIC FUNCTIONS ----------------------------------------------------------------------------------------------- */
 function SendCommand($cmd,$retArrName="items",$secondretArrName="")
 {
  $orderBy = "";
  if($this->FixedOrderBy)
   $orderBy = $this->FixedOrderBy." ".$this->FixedOrderMethod;

  if($this->OrderBy)
  {
   if(strpos($this->OrderBy, ",") !== false)
    $x = explode(",",$this->OrderBy);
   else
	$x = array($this->OrderBy);

   for($c=0; $c < count($x); $c++)
	$orderBy.= ",".$x[$c]." ".($this->OrderMethod ? $this->OrderMethod : "ASC");
  }

  if($orderBy && ($orderBy != ""))
   $cmd.= " --order-by '".ltrim($orderBy,",")."'";  

  if($this->RPP)
  {
   if($this->PG > 1)
   {
    $this->Results['from'] = ($this->RPP*($this->PG-1))+1;
    $limit = ($this->RPP*($this->PG-1)).",".$this->RPP;
   }
   else
   {
    $this->Results['from'] = 1;
    $limit = $this->RPP;
   }
   $cmd.= " -limit '".$limit."'";
  }

  $ret = GShell($cmd);
  if($ret['error'])
   return $this->returnError($ret);

  $this->Return = $ret['outarr'];

  $this->Results['to'] = ($this->Results['from']-1)+$this->RPP;
  $this->Results['count'] = $ret['outarr']['count'];
  
  if($this->Results['to'] > $this->Results['count'])
   $this->Results['to'] = $this->Results['count'];

  if($this->Results['count'])
   $this->MaxPG = ceil($this->Results['count']/$this->RPP);
  
  $this->Results[$retArrName] = $ret['outarr'][$retArrName];
  if($secondretArrName)
   $this->Results[$secondretArrName] = $ret['outarr'][$secondretArrName];

  return $this->Results;
 }
 //------------------------------------------------------------------------------------------------------------------//
 function DrawSerpButtons($includeResultsString=false, $serpHistName="SERP")
 {
  echo "<ul class='toggles' orderby='".$this->OrderBy."' ordermethod='".$this->OrderMethod."' rpp='".$this->RPP."' pg='".$this->PG."' count='".$this->Results['count']."' style='float:right'>";
  if($includeResultsString)
   echo "<li class='label' id='".$serpHistName."-serpstring'>".$this->getSerpResultsString()."</li>";
  if($this->PG < 2)
   echo "<li class='first disabled'>";
  else
   echo "<li class='first' onclick='Template.".$serpHistName.".onPriorBtnClick(this, \"".$serpHistName."\")'>";
  echo "<span class='serpbtntext'><</span></li>";
  if($this->PG == $this->MaxPG)
   echo "<li class='last disabled'>";
  else
   echo "<li class='last' onclick='Template.".$serpHistName.".onNextBtnClick(this, \"".$serpHistName."\")'>";
  echo "<span class='serpbtntext'>></span></li>";
  echo "</ul>";
 }
 //------------------------------------------------------------------------------------------------------------------//

}

