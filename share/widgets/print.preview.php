<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 19-04-2013
 #PACKAGE: printmodels-config
 #DESCRIPTION: Default preview widget for print models.
 #VERSION: 2.3beta
 #CHANGELOG: 19-04-2013 : Bug fix in sendEmail.
			 06-03-2013 : Bug fix.
			 23-01-2013 : Bug fix for absolute URL with images & link.
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_USERS_HOMES;
$_BASE_PATH = "../../";
define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : "";
$_ID = $_REQUEST['id'] ? $_REQUEST['id'] : 0;

if($_AP && $_ID)
{
 // Get doc info //
 $ret = GShell("dynarc item-info -ap `".$_AP."` -id `".$_ID."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
 $docInfo = $ret['outarr'];
}

$_MODEL_AP = $_REQUEST['modelap'] ? $_REQUEST['modelap'] : "printmodels";
$_TITLE = $_REQUEST['title'] ? $_REQUEST['title'] : ($docInfo ? $docInfo['name'] : "untitled");
$_DESTFOLDER = $_REQUEST['destfolder'] ? $_REQUEST['destfolder'] : "tmp/";

$_TITLE = htmlentities($_TITLE);
$_TITLE = str_replace("&Acirc;&deg;",".",$_TITLE);
$_TITLE = str_replace(array("&Acirc;","&deg;"),".",$_TITLE);
$_TITLE = str_replace("&amp;deg;",".",$_TITLE);


$_OUTPUT_FILE = $_DESTFOLDER.str_replace("/","-",$_TITLE).".pdf";

if($_REQUEST['modelct'] || $_REQUEST['modelcat'])
{
 $ret = GShell("dynarc cat-info -ap `".$_MODEL_AP."`".($_REQUEST['modelcat'] ? " -id `".$_REQUEST['modelcat']."`" : " -tag `".$_REQUEST['modelct']."`"), $_REQUEST['sessid'], $_REQUEST['shellid']);
 $_MODEL_CAT = $ret['outarr'];
}
else
 $_MODEL_CAT = "";

$_MODEL_ID = $_REQUEST['modelid'] ? $_REQUEST['modelid'] : "";

$_PARSER = $_REQUEST['parser'] ? $_REQUEST['parser'] : "";


$ret = GShell("dynarc item-list -ap `".$_MODEL_AP."`".($_MODEL_CAT ? " -cat `".$_MODEL_CAT['id']."`" : "")." -extget printmodelinfo",$_REQUEST['sessid'],$_REQUEST['shellid']);
$PrintModelList = $ret['outarr']['items'];

if(!$_MODEL_ID)
 $_MODEL_ID = $PrintModelList[0]['id'];

/* Get params */
$privateParams = array("sessid","shellid","modelap","modelcat","modelid","parser","ap","id","title","destfolder","page");
$extraParams = "";
while(list($k,$v) = each($_REQUEST))
{
 if(!in_array($k,$privateParams))
  $extraParams.= "&".$k."=".$v;
}

$iframeSRC = $_ABSOLUTE_URL."share/widgets/printpreview/preview.php?sessid=".$_REQUEST['sessid']."&shellid=".$_REQUEST['shellid']."&modelap="
	.$_MODEL_AP."&modelcat=".($_MODEL_CAT ? $_MODEL_CAT['id'] : "")."&modelid=".$_MODEL_ID."&parser=".$_PARSER."&ap=".$_AP."&id=".$_ID.$extraParams;

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title><?php echo $_TITLE; ?></title>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/printpreview/preview.css" type="text/css" />
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
</head><body>
<table class="previewform" width="550" height="550" cellspacing="0" cellpadding="0" border="0">
<tr><td class="title" height="40">Anteprima di stampa</td>
	<td class="pagelist"><span style="float:left;white-space:nowrap;margin-top:20px;">Pag.</span>
		<ul class="pages" id="pagebuttons">
		 <li class="selected" onclick="showPage(this)">1</li>
		</ul>
	</td><td width="180"><ul class='basicbuttons' style="float:left;"><li><a href='#' onclick="printPreview()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/printpreview/img/print.png" border='0'/>Stampa</a></li></ul> <a href='#' class="closebtn" onclick="gframe_close(CLOSE_MSG,CLOSE_RET)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/printpreview/img/close.png" border="0" title="Chiudi"/></a></td></tr>

<tr><td colspan="2" valign="top">
	 <div class="sheet-background" id="framelist">
	  <iframe id="sheetframe-0" class="sheetframe" src="<?php echo $iframeSRC; ?>" style="width:220mm;height:307mm;"></iframe><!-- Aggiungere 10mm di bordo alle dimensioni -->
	 </div>
	</td>
	<td valign="top"><br/>
	<span class='link' onclick="exportToPDF()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/printpreview/img/pdf_export.gif"/> Salva in PDF</span>
	<span class='link' onclick="sendEmail()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/printpreview/img/email_send.png"/> Invia x email</span><br/>

	<div class="available-models"><i>Modelli disponibili</i></div>
	<div class="model-list">
	<?php
	if($_MODEL_AP && $_MODEL_CAT)
	{
	 for($c=0; $c < count($PrintModelList); $c++)
	 {
	  if($PrintModelList[$c]['thumbdata'])
	  {
	   if(strpos($PrintModelList[$c]['thumbdata'],"data:") !== false)
	    echo "<img src='".$PrintModelList[$c]['thumbdata']."' width='96' style='border:1px solid #dadada;'/><br/>";
	   else
		echo "<img src='".$_ABSOLUTE_URL.$PrintModelList[$c]['thumbdata']."' width='96' style='border:1px solid #dadada;' id='model-"
			.$PrintModelList[$c]['id']."-bgimage'/><br/>";
	  }
	  else
	   echo "<img src='".$_ABSOLUTE_URL."share/widgets/printpreview/img/image_not_available.png' style='border:1px solid #dadada;'/><br/>";
	  echo "<input id='model-".$PrintModelList[$c]['id']."' type='radio' name='model' onclick='changeModel(this)' class='model'".($_MODEL_ID == $PrintModelList[$c]['id'] ? " checked='true'" : "")."/>".$PrintModelList[$c]['name']."<br/>";
	 }
	}
	?>
	</div>
	</td></tr>

</table>
<script>
var MODEL_AP = "<?php echo $_MODEL_AP ? $_MODEL_AP : 'printmodels'; ?>";
var MODEL_ID = <?php echo $_MODEL_ID ? $_MODEL_ID : "0"; ?>;
var PAGES = new Array();

var CLOSE_MSG = null;
var CLOSE_RET = null;

function bodyOnLoad()
{

}

function cachecontentsload(contents)
{

}

function printPreview()
{
 var q = "";
 if(PAGES.length)
 {
  for(var c=0; c < PAGES.length; c++)
   q+= " -c `"+PAGES[c].contents+"`";
 }

 var BACKGROUND_IMAGE = "";
 if(document.getElementById("model-"+MODEL_ID+"-bgimage"))
  BACKGROUND_IMAGE = document.getElementById("model-"+MODEL_ID+"-bgimage").src.replace(ABSOLUTE_URL,"");


 var sh = new GShell();
 sh.OnPreOutput = function(){}; // <--- Questa funzione bisogna crearla altrimenti non appare la progress bar
 sh.OnOutput = function(o,a){
	 //document.location.href = ABSOLUTE_URL+"<?php echo $_USERS_HOMES.'/'.$_SESSION['HOMEDIR'].'/'.$_OUTPUT_FILE; ?>";
	 window.open(ABSOLUTE_URL+"<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR'].'/'.$_OUTPUT_FILE; ?>");
	 var ret = new Array();
	 ret['action'] = "PRINT";
	 ret['type'] = "PDF";
	 ret['filename'] = "<?php echo $_OUTPUT_FILE; ?>";
	 window.setTimeout(function(){gframe_close("The document has been printed",ret);},1000);
	}
 sh.sendCommand("pdf export -o `<?php echo $_OUTPUT_FILE; ?>`"+(BACKGROUND_IMAGE ? " -background `"+BACKGROUND_IMAGE+"`" : "")+q);
}

function showPage(li)
{
 var idx = 0;
 var list = li.parentNode.getElementsByTagName('LI');
 for(var c=0; c < list.length; c++)
 {
  list[c].className = (list[c] == li) ? "selected" : "";
  if(list[c] == li)
   idx = c;
 }
 var list = document.getElementById('framelist').getElementsByTagName('IFRAME');
 for(var c=0; c < list.length; c++)
 {
  list[c].style.display = (c==idx) ? "" : "none";
 }
}

function changeModel(inp)
{
 var ul = document.getElementById('pagebuttons');
 var framelist = document.getElementById('framelist');
 var list = ul.getElementsByTagName('LI');
 for(var c=1; c < list.length; c++)
  framelist.removeChild(document.getElementById('sheetframe-'+c));
 while(ul.getElementsByTagName('LI').length > 1)
  ul.removeChild(ul.getElementsByTagName('LI')[1]);

 var modelId = inp.id.substr(6);
 var href = document.getElementById('sheetframe-0').src;
 if(href.indexOf("&modelid=") > 0)
  href = href.replace("&modelid="+MODEL_ID, "&modelid="+modelId);
 else
  href+= "&modelid="+modelId;
 
 MODEL_ID = modelId;

 document.getElementById('sheetframe-0').src = href;
 document.getElementById('sheetframe-0').style.display = "";
 document.getElementById('pagebuttons').getElementsByTagName('LI')[0].className = "selected";

 PAGES = new Array();
}

function exportToPDF()
{
 var q = "";
 if(PAGES.length)
 {
  for(var c=0; c < PAGES.length; c++)
   q+= " -c `"+PAGES[c].contents+"`";
 }

 var BACKGROUND_IMAGE = "";
 if(document.getElementById("model-"+MODEL_ID+"-bgimage"))
  BACKGROUND_IMAGE = document.getElementById("model-"+MODEL_ID+"-bgimage").src.replace(ABSOLUTE_URL,"");

 var sh = new GShell();
 sh.OnPreOutput = function(){}; // <--- Questa funzione bisogna crearla altrimenti non appare la progress bar
 sh.OnOutput = function(o,a){
	 document.location.href = ABSOLUTE_URL+"getfile.php?file=<?php echo $_OUTPUT_FILE; ?>";
	 var ret = new Array();
	 ret['action'] = "EXPORT";
	 ret['type'] = "PDF";
	 ret['filename'] = "<?php echo $_OUTPUT_FILE; ?>";
	 CLOSE_MSG = "The document has been exported to PDF";
	 CLOSE_RET = ret;
	}
 sh.sendCommand("pdf export -o `<?php echo $_OUTPUT_FILE; ?>`"+(BACKGROUND_IMAGE ? " -background `"+BACKGROUND_IMAGE+"`" : "")+q);
}

function sendEmail()
{
 var q = "";
 if(PAGES.length)
 {
  for(var c=0; c < PAGES.length; c++)
   q+= " -c `"+PAGES[c].contents+"`";
 }

 var sh = new GShell();
 sh.OnPreOutput = function(){}; // <--- Questa funzione bisogna crearla altrimenti non appare la progress bar
 sh.OnOutput = function(o,a){
	 var sh2 = new GShell();
	 sh2.OnOutput = function(){
		 var ret = new Array();
		 ret['action'] = "EMAIL";
		 ret['type'] = "PDF";
		 ret['filename'] = "<?php echo $_OUTPUT_FILE; ?>";
		 window.setTimeout(function(){gframe_close("The document has been send to email",ret);},1000);
		}
	 sh2.sendCommand("gframe -f sendmail -params `attachment=<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR'].'/'.$_OUTPUT_FILE; ?>`");
	}
 sh.sendCommand("pdf export -o `<?php echo $_OUTPUT_FILE; ?>`"+q);
}

function previewMessage(msg, info)
{
 switch(msg)
 {
  case 'PAGEBREAK' : {
	 var list = document.getElementById('framelist').getElementsByTagName('IFRAME');
	 var iframe = document.createElement('IFRAME');
	 iframe.id = "sheetframe-"+list.length;
	 iframe.className = "sheetframe";
	 iframe.style.width = "220mm";
	 iframe.style.height = "307mm";
	 document.getElementById('framelist').appendChild(iframe);

	 var href = "<?php echo $iframeSRC; ?>";
	 if(href.indexOf("&modelid=") > 0)
	  href = href.replace("&modelid=<?php echo $_MODEL_ID; ?>", "&modelid="+MODEL_ID);
	 else
	  href+= "&modelid="+MODEL_ID;
	 href+= "&start="+(info.start + info.elements)+"&page="+list.length;
	 iframe.src = href;

	 var ul = document.getElementById('pagebuttons');
	 var li = document.createElement('LI');
	 li.innerHTML = list.length;
	 li.onclick = function(){showPage(this);}
	 ul.appendChild(li);
	} break;

  case 'PAGEINFO' : {
	 PAGES.push(info);
	} break;
 }

}
</script>
</body></html>
<?php

