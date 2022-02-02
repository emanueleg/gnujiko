<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-02-2016
 #PACKAGE: printmodels-config
 #DESCRIPTION: 
 #VERSION: 2.7beta
 #CHANGELOG: 23-02-2016 : Integrazione con orientamento orrizzontale.
			 02-05-2015 : Aggiunta prima e ultima pagina.
			 20-01-2015 : Bug fix.
			 18-03-2014 : Aggiunto funzione pageWithoutTable, altrimenti non stampava tutti quei modelli privi di tabella.
			 03-12-2013 : Aggiunto urlencode su parametri con CMD.
			 23-01-2013 : Bug fix for absolute URL with images & link.
			 13-01-2013 : Bug fix with localhost images.
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_BACKGROUND_IMAGE;
$_BASE_PATH = "../../../";
define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$_MODEL_AP = $_REQUEST['modelap'];
$_MODEL_CAT = $_REQUEST['modelcat'];
$_MODEL_ID = $_REQUEST['modelid'];

$_PARSER = $_REQUEST['parser'];

$_AP = $_REQUEST['ap'];
$_ID = $_REQUEST['id'];
$_START = $_REQUEST['start'] ? $_REQUEST['start'] : 0;

include_once($_BASE_PATH."include/company-profile.php");
$_DECIMALS = $_COMPANY_PROFILE['accounting']['decimals_pricing'];

//-------------------------------------------------------------------------------------------------------------------//
function printpreview_getPageSize($modelInfo)
{
 $pageWidth = 210;	// default A4
 $pageHeight = 297; // default A4

 switch(strtoupper($modelInfo['format']))
 {
  case 'A0' : {$w=841; $h=1189;} break;
  case 'A1' : {$w=594; $h=841;} break;
  case 'A2' : {$w=420; $h=594;} break;
  case 'A3' : {$w=297; $h=420;} break;

  case 'A5' : {$w=148; $h=210;} break;
  case 'A6' : {$w=105; $h=148;} break;
  case 'A7' : {$w=74; $h=105;} break;
  case 'A8' : {$w=52; $h=74;} break;
  case 'A9' : {$w=37; $h=52;} break;
  case 'A10' : {$w=26; $h=37;} break;

  default : {$w=210; $h=297;} break; // A4
 }
 switch(strtoupper($modelInfo['orientation']))
 {
  case 'L' : {
	 $pageWidth = $h;
	 $pageHeight = $w;
	} break;

  default : {
	 $pageWidth = $w;
	 $pageHeight = $h;
	} break;
 }

 return array('width'=>$pageWidth, 'height'=>$pageHeight);
}
//-------------------------------------------------------------------------------------------------------------------//

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Gnujiko - Preview</title>
</head><body onload="bodyOnLoad()">
<?php
include_once($_BASE_PATH."include/js/gshell.php");

$contents = "";

/* Get model */
$ret = GShell("dynarc item-info -ap `".$_MODEL_AP."` -id `".$_MODEL_ID."` -extget `printmodelinfo,css`",$_REQUEST['sessid'],$_REQUEST['shellid']);
$modelInfo = $ret['outarr'];

$_PAGE_SIZE = printpreview_getPageSize($modelInfo);

if($_PARSER)
{
 /* Get params */
 $privateParams = array("sessid","shellid","modelap","modelcat","modelid","modelalias","parser","ap","id","title","destfolder","page");
 $extraParams = "";
 while(list($k,$v) = each($_REQUEST))
 {
  if(!in_array($k,$privateParams))
  {
   if($k == "cmd")
	$v = urlencode($v);
   $extraParams.= "&".$k."=".$v;
  }
 }

 $_PREVIEW_TYPE = $_REQUEST['preview'];

 switch($_PREVIEW_TYPE)
 {
  case 'firstpage' : {
	 $ret = GShell("parserize -p `".$_PARSER."` -params `ap=".$_AP."&id=".$_ID.$extraParams."` `".$modelInfo['firstpage_content']."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
	 $contents = $ret['message'];
	} break;

  case 'lastpage' : {
	 $ret = GShell("parserize -p `".$_PARSER."` -params `ap=".$_AP."&id=".$_ID.$extraParams."` `".$modelInfo['lastpage_content']."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
	 $contents = $ret['message'];
	} break;

  default : {
	 $ret = GShell("dynarc item-info -ap `".$_MODEL_AP."` -id `".$_MODEL_ID."` || parserize -p `".$_PARSER."` -params `ap=".$_AP."&id=".$_ID.($_REQUEST['page'] ? "&page=".$_REQUEST['page'] : "").$extraParams."` *.desc",$_REQUEST['sessid'],$_REQUEST['shellid']);
	 $contents = $ret['message'];
	} break;
 }


}
else
{
 $contents = $modelInfo['desc'];
 $firstPageContents = $modelInfo['firstpage_content'];
 $lastPageContents = $modelInfo['lastpage_content'];
}

/* UPDATE LOCALHOST IMAGES */
$contents = str_replace("{ABSOLUTE_URL}",$_ABSOLUTE_URL,$contents);

?>
<style type='text/css'>
table.___printpreviewpagetable {
	background: #ffffff;
	position: relative;
}
<?php
if(strpos($modelInfo['thumbdata'],"data:") === false)
{
 $_BACKGROUND_IMAGE = $modelInfo['thumbdata'];
 echo "td#___printpreviewpagecontents {background: url(".$_ABSOLUTE_URL.$modelInfo['thumbdata'].") center center no-repeat;}";
}
?>
</style>
<style type='text/css'>
<?php  echo $modelInfo['css'][0]['content']; ?>
</style>
<table class="___printpreviewpagetable" align='center' valign='middle' cellspacing="0" cellpadding="0" border="0" style="width:<?php echo $_PAGE_SIZE['width']; ?>mm;height:<?php echo $_PAGE_SIZE['height']; ?>mm;">
<tr><td valign="top" id="___printpreviewpagecontents"><?php echo $contents; ?></td></tr>
</table>

<?php
if($_PARSER && file_exists($_BASE_PATH."share/widgets/printpreview/parser/".$_PARSER.".php"))
 include($_BASE_PATH."share/widgets/printpreview/parser/".$_PARSER.".php");
?>

<script>
var DECIMALS = <?php echo $_DECIMALS ? $_DECIMALS : "2"; ?>;
var PREVIEW_TYPE = "<?php echo $_PREVIEW_TYPE; ?>";

function bodyOnLoad()
{
 if(PREVIEW_TYPE == "firstpage")
  firstPageLoaded();
 else if(PREVIEW_TYPE == "lastpage")
  lastPageLoaded();
 else
 {
  if(typeof(loadPreview) == "function")
   loadPreview();
  else
   pageWithoutTable();
 }
}

function autoInsertRows(itemList)
{
 var tb = document.getElementById('itemlist');
 if(!tb)
  return pageWithoutTable();

 if(!itemList)
  return pageWithoutTable();

 // use last row as footer //
 var fR = tb.rows[tb.rows.length-1];
 var cR = null;
 var startAt = 0;
 if(tb.rows[0].cells[0].tagName.toUpperCase() == "TH")
 {
  var fRH = tb.rows[1].cells[0].offsetHeight;
  startAt++;
  if(fR.rowIndex >= 2)
   fRH+= tb.rows[2].cells[0].offsetHeight;
  if(tb.rows[1] != fR)
   cR = tb.rows[1];
 }
 else
 {
  var fRH = tb.rows[0].cells[0].offsetHeight;
  if(fR.rowIndex >= 1)
   fRH+= tb.rows[1].cells[0].offsetHeight;
  if(tb.rows[0] != fR)
   cR = tb.rows[0];
 }

 var fRHstart = fRH;
 var start = <?php echo $_START ? $_START : "0"; ?>;
 var elidx = 0;
 var completed = true;

 for(var c=start; c < itemList.length; c++)
 {
  var r = tb.insertRow(startAt+c-start);
  for(var i=0; i < tb.rows[0].cells.length; i++)
  {
   var value = itemList[c][tb.rows[0].cells[i].id] ? itemList[c][tb.rows[0].cells[i].id] : "";

   var cell = r.insertCell(-1);
   if(cR)
   {
	cell.className = cR.cells[i].className;
    switch(cR.cells[i].getAttribute('format'))
	{
	 case 'currency' : cell.innerHTML = value ? formatCurrency(value,cR.cells[i].getAttribute('decimals') ? parseInt(cR.cells[i].getAttribute('decimals')) : DECIMALS) : "&nbsp;"; break;
	 case 'percentage' : cell.innerHTML = value ? parseFloat(value)+"%" : "&nbsp;"; break;
	 case 'percentage currency' : {
		 if(!parseFloat(value))
		  cell.innerHTML = "&nbsp;";
		 else if(value.indexOf("%") > 0)
		  cell.innerHTML = value;
		 else
		  cell.innerHTML = formatCurrency(value,cR.cells[i].getAttribute('decimals') ? parseInt(cR.cells[i].getAttribute('decimals')) : DECIMALS);
		} break;
	 case 'date' : case 'datetime' : case 'time' : {
		 if(!value || (value == "")){cell.innerHTML = "&nbsp;";return;}
		 if(parseFloat(value) > 0)
		 {
		  var tDate = new Date(parseFloat(value)*1000);
		 }
		 else
		 {
		  var tDate = new Date();
		  tDate.setFromISO(value);
		 }
		 switch(cR.cells[i].getAttribute('format'))
		 {
		  case 'date' : cell.innerHTML = tDate.printf('d/m/Y'); break;
		  case 'time' : cell.innerHTML = tDate.printf('H:i'); break;
		  case 'datetime' : cell.innerHTML = tDate.printf('d/m/Y H:i'); break;
		 }
		} break;

	 default : cell.innerHTML = value ? value : "&nbsp;"; break;
	}

   }
   else
    cell.innerHTML = value ? value : "&nbsp;";
   if(tb.rows[0].cells[i].style.display == "none")
	cell.style.display = "none";
  }

  fRH-= r.cells[0].offsetHeight;

  if(px2mm(fRH) < 5)
  {
   fRH+= r.cells[0].offsetHeight;
   tb.deleteRow(r.rowIndex);
   var page = {start:start, elements:elidx, freespace:px2mm(fRH)};
   if(window.parent && (typeof(window.parent.previewMessage) == "function"))
	window.parent.previewMessage("PAGEBREAK",page);
   completed = false;
   break;
  }
  elidx++;
 }

 for(var c=0; c < fR.cells.length; c++)
  fR.cells[c].style.height = px2mm(fRH)+"mm";

 /* Rimuove le colonne nascoste perchè altrimenti escono fuori quando si esporta in PDF */
 for(var i=0; i < tb.rows[0].cells.length; i++)
 {
  if(tb.rows[0].cells[i].style.display == "none")
  {
   for(var c=0; c < tb.rows.length; c++)
    tb.rows[c].deleteCell(i);
   if(tb.getElementsByTagName('COLGROUP').length)
   {
    var col = tb.getElementsByTagName('COLGROUP')[0].getElementsByTagName('COL')[i];
    col.parentNode.removeChild(col);
   }
   i--;
  }
 }
 
 var contents = "<style type='text/css'>"+document.body.getElementsByTagName('STYLE')[1].innerHTML+"</style>" + document.getElementById("___printpreviewpagecontents").innerHTML;
 var page = {start:start, elements:elidx, freespace:px2mm(fRH), contents:contents};
 if(window.parent && (typeof(window.parent.previewMessage) == "function"))
  window.parent.previewMessage("PAGEINFO",page,completed);
}

function pageWithoutTable()
{
 var contents = "<style type='text/css'>"+document.body.getElementsByTagName('STYLE')[1].innerHTML+"</style>" + document.getElementById("___printpreviewpagecontents").innerHTML;
 var page = {start:0, elements:0, freespace:0, contents:contents};
 if(window.parent && (typeof(window.parent.previewMessage) == "function"))
  window.parent.previewMessage("PAGEINFO",page, true);
}

function firstPageLoaded()
{
 var contents = "<style type='text/css'>"+document.body.getElementsByTagName('STYLE')[1].innerHTML+"</style>" + document.getElementById("___printpreviewpagecontents").innerHTML;
 var page = {start:0, elements:0, freespace:0, contents:contents, type:'firstpage'};
 if(window.parent && (typeof(window.parent.previewMessage) == "function"))
  window.parent.previewMessage("FIRSTPAGE",page, false);
}

function lastPageLoaded()
{
 var contents = "<style type='text/css'>"+document.body.getElementsByTagName('STYLE')[1].innerHTML+"</style>" + document.getElementById("___printpreviewpagecontents").innerHTML;
 var page = {start:0, elements:0, freespace:0, contents:contents, type:'lastpage'};
 if(window.parent && (typeof(window.parent.previewMessage) == "function"))
  window.parent.previewMessage("LASTPAGE",page, false);
}

function px2mm(px)
{
 return (25.4/96)*px;
}
</script>
</body></html>
<?php

