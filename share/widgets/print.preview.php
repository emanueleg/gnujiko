<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 23-02-2016
 #PACKAGE: printmodels-config
 #DESCRIPTION: Default preview widget for print models.
 #VERSION: 2.16beta
 #CHANGELOG: 23-02-2016 : Integrazione con orientamento orrizzontale.
			 22-01-2016 : Bug fix su funzione changeModel.
			 21-01-2016 : Bug fix caricamento frames per connessioni lente.
			 25-09-2015 : Aggiornata funzione stampa.
			 21-05-2015 : Bug fix generazione anteprima.
			 02-05-2015 : Integrato con prima e ultima pagina.
			 23-03-2015 : Aggiunto campo format.
			 20-01-2015 : Bug fix su multipagine.
			 14-10-2014 : Aggiunto fullname che ritorna il nome del file completo del PDF.
			 07-03-2014 : Rimosso spazi dal nome del file perchè su Chromium altrimenti non apre il PDF.
			 10-02-2014 : Abilitato gli errori del PDF.
			 03-12-2013 : Aggiunto urlencode su parametri con CMD.
			 14-11-2013 : Aggiunto alert che avvisa che l'utente root non puo stampare.
			 19-04-2013 : Bug fix in sendEmail.
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
$_RECP = $_REQUEST['recp'] ? $_REQUEST['recp'] : "";
$_SUBJID = $_REQUEST['subjid'] ? $_REQUEST['subjid'] : 0;

$_TITLE = htmlentities($_TITLE);
$_TITLE = str_replace("&Acirc;&deg;",".",$_TITLE);
$_TITLE = str_replace(array("&Acirc;","&deg;"),".",$_TITLE);
$_TITLE = str_replace("&amp;deg;",".",$_TITLE);


$_OUTPUT_FILE = $_DESTFOLDER.str_replace("/","-",$_TITLE).".pdf";

$_OUTPUT_FILE = str_replace(" ","_",$_OUTPUT_FILE);

if($_REQUEST['modelct'] || $_REQUEST['modelcat'])
{
 $ret = GShell("dynarc cat-info -ap `".$_MODEL_AP."`".($_REQUEST['modelcat'] ? " -id `".$_REQUEST['modelcat']."`" : " -tag `".$_REQUEST['modelct']."`"), $_REQUEST['sessid'], $_REQUEST['shellid']);
 $_MODEL_CAT = $ret['outarr'];
}
else
 $_MODEL_CAT = "";

$_MODEL_ID = $_REQUEST['modelid'] ? $_REQUEST['modelid'] : "";
$_MODEL_ALIAS = $_REQUEST['modelalias'] ? $_REQUEST['modelalias'] : "";
$_MODEL_FORMAT = "A4";
$_MODEL_ORIENTATION = "P";

$_PARSER = $_REQUEST['parser'] ? $_REQUEST['parser'] : "";

if($_MODEL_ALIAS)
{
 $ret = GShell("dynarc item-info -ap `".$_MODEL_AP."` -alias `".$_MODEL_ALIAS."` -extget printmodelinfo",$_REQUEST['sessid'],$_REQUEST['shellid']);
 if(!$ret['error'])
 {
  $modelInfo = $ret['outarr'];
  $_MODEL_ID = $modelInfo['id'];
  $_MODEL_FORMAT = $modelInfo['format'] ? $modelInfo['format'] : "A4";
  $_MODEL_ORIENTATION = $modelInfo['orientation'] ? $modelInfo['orientation'] : "P";
 }
}
else if($_MODEL_ID)
{
 $ret = GShell("dynarc item-info -ap `".$_MODEL_AP."` -id `".$_MODEL_ID."` -extget printmodelinfo",$_REQUEST['sessid'],$_REQUEST['shellid']);
 if(!$ret['error'])
 {
  $modelInfo = $ret['outarr'];
  $_MODEL_FORMAT = $modelInfo['format'] ? $modelInfo['format'] : "A4";
  $_MODEL_ORIENTATION = $modelInfo['orientation'] ? $modelInfo['orientation'] : "P";
 }
}

$ret = GShell("dynarc item-list -ap `".$_MODEL_AP."`".($_MODEL_CAT ? " -cat `".$_MODEL_CAT['id']."`" : "")." -extget printmodelinfo",$_REQUEST['sessid'],$_REQUEST['shellid']);

$PrintModelList = $ret['outarr']['items'];

if(!$_MODEL_ID)
{
 $modelInfo = $PrintModelList[0];
 $_MODEL_ID = $PrintModelList[0]['id'];
 $_MODEL_FORMAT = $PrintModelList[0]['format'] ? $PrintModelList[0]['format'] : "A4";
 $_MODEL_ORIENTATION = $PrintModelList[0]['orientation'] ? $PrintModelList[0]['orientation'] : "P";
}

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
<tr><td class="title" height="40">Anteprima</td>
	<td class="pagelist"><span style="float:left;white-space:nowrap;margin-top:20px;">Pag.</span>
		<ul class="pages" id="pagebuttons">
		 <?php
		  echo "<li onclick='showPage(this)' title='Prima pagina / copertina' id='firstpage_tabbtn' frameid='first'";
		  if($modelInfo['firstpage_content'] == "")
		   echo " style='display:none'";
		  echo ">P</li>";
		 ?>
		 <li class='selected' onclick="showPage(this)" frameid='0'>1</li>
		 <?php
		  echo "<li onclick='showPage(this)' title='Ultima pagina' id='lastpage_tabbtn' frameid='last'";
		  if($modelInfo['lastpage_content'] == "")
		   echo " style='display:none'";
		  echo ">U</li>";
		 ?>
		</ul>
	</td><td width="180"><ul class='basicbuttons' style="float:left;"><li><a href='#' onclick="printPreview()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/printpreview/img/print.png" border='0'/>Stampa</a></li></ul> <a href='#' class="closebtn" onclick="gframe_close(CLOSE_MSG,CLOSE_RET)"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/printpreview/img/close.png" border="0" title="Chiudi"/></a></td></tr>

<tr><td colspan="2" valign="top">
	 <div class="sheet-background">
	  <div>
	  <?php
	  $firstPageFrameSRC = "";
	   $firstPageFrameSRC = $_ABSOLUTE_URL."share/widgets/printpreview/preview.php?sessid=".$_REQUEST['sessid']."&shellid=".$_REQUEST['shellid']."&modelap=".$_MODEL_AP."&modelcat=".($_MODEL_CAT ? $_MODEL_CAT['id'] : "")."&modelid=".$_MODEL_ID."&parser=".$_PARSER."&ap=".$_AP."&id=".$_ID."&preview=firstpage".$extraParams;
	   echo "<iframe id='sheetframe-first' class='sheetframe' data-src='".$firstPageFrameSRC."' style='width:220mm;height:307mm;display:none'></iframe>";
	  ?>
	  </div>

	  <div id="framelist">
	   <iframe id="sheetframe-0" class="sheetframe" data-src="<?php echo $iframeSRC; ?>" style="width:220mm;height:307mm;"></iframe><!-- Aggiungere 10mm di bordo alle dimensioni -->
	  </div>

	  <div>
	  <?php
	  $lastPageFrameSRC = "";
	   $lastPageFrameSRC = $_ABSOLUTE_URL."share/widgets/printpreview/preview.php?sessid=".$_REQUEST['sessid']."&shellid=".$_REQUEST['shellid']."&modelap=".$_MODEL_AP."&modelcat=".($_MODEL_CAT ? $_MODEL_CAT['id'] : "")."&modelid=".$_MODEL_ID."&parser=".$_PARSER."&ap=".$_AP."&id=".$_ID."&preview=lastpage".$extraParams;
	   echo "<iframe id='sheetframe-last' class='sheetframe' data-src='".$lastPageFrameSRC."' style='width:220mm;height:307mm;display:none'></iframe>";
	  ?>
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
	  $hasFirstPage = $PrintModelList[$c]['firstpage_content'] ? 'true' : 'false';
	  $hasLastPage = $PrintModelList[$c]['lastpage_content'] ? 'true' : 'false';
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
	  echo "<input id='model-".$PrintModelList[$c]['id']."' type='radio' name='model' onclick='changeModel(this)' class='model'".($_MODEL_ID == $PrintModelList[$c]['id'] ? " checked='true'" : "")." modelformat='".$PrintModelList[$c]['format']."' modelorientation='".$PrintModelList[$c]['orientation']."' hasfirstpage='".$hasFirstPage."' haslastpage='".$hasLastPage."'/>".$PrintModelList[$c]['name']."<br/>";
	 }
	}
	?>
	</div>
	</td></tr>

</table>
<script>
var MODEL_AP = "<?php echo $_MODEL_AP ? $_MODEL_AP : 'printmodels'; ?>";
var MODEL_ID = <?php echo $_MODEL_ID ? $_MODEL_ID : "0"; ?>;
var MODEL_FORMAT = "<?php echo $_MODEL_FORMAT; ?>";
var MODEL_ORIENTATION = "<?php echo $_MODEL_ORIENTATION; ?>";
var RECP = "<?php echo $_RECP; ?>";
var SUBJID = "<?php echo $_SUBJID; ?>";
var PAGES = new Array();
var FIRSTPAGE = null;
var LASTPAGE = null;

var CLOSE_MSG = null;
var CLOSE_RET = null;

var CAN_PRINT = false;

var PREVSH = new GShell();
function bodyOnLoad()
{
 var sff = document.getElementById("sheetframe-first");
 if(sff) sff.src = sff.getAttribute('data-src');

 var sf = document.getElementById("sheetframe-0");
 if(sf) sf.src = sf.getAttribute('data-src');

 var sfl = document.getElementById("sheetframe-last");
 if(sfl) sfl.src = sfl.getAttribute('data-src');


 if(!CAN_PRINT)
  PREVSH.showProcessMessage("Generazione delle anteprime in corso", "Attendere prego, è in corso la generazione delle anteprime delle pagine.");
}

function cachecontentsload(contents)
{

}

function printPreview()
{
 if(!CAN_PRINT)
  return alert("Generazione delle pagine di anteprima in corso... Attendere!");

 if("<?php echo $_SESSION['UNAME']; ?>" == "root")
  return alert("Attenzione!, l'utente root non può stampare. Devi uscire ed effettuare il login come utente normale");
 var q = "";
 if(FIRSTPAGE && (document.getElementById('firstpage_tabbtn').style.display != "none"))
  q+= " -c `"+FIRSTPAGE.contents+"`";

 if(PAGES.length)
 {
  for(var c=0; c < PAGES.length; c++)
  {
   PAGES[c].contents = PAGES[c].contents.replace("{PG_COUNT}",PAGES.length); // <--- sost. PG_COUNT con il nr. di pagine su ogni pagina
   PAGES[c].contents = PAGES[c].contents.replace("{DOC_PGC}",(c+1)+"/"+PAGES.length); // <--- sost. DOC_PGC con il nr di pagina / tot pagine su ogni pagina.
   q+= " -c `"+PAGES[c].contents+"`";
  }
 }

 if(LASTPAGE && (document.getElementById('lastpage_tabbtn').style.display != "none"))
  q+= " -c `"+LASTPAGE.contents+"`";

 var BACKGROUND_IMAGE = "";
 if(document.getElementById("model-"+MODEL_ID+"-bgimage"))
  BACKGROUND_IMAGE = document.getElementById("model-"+MODEL_ID+"-bgimage").src.replace(ABSOLUTE_URL,"");


 var sh = new GShell();
 sh.OnError = function(err){alert(err.striptags(true));}
 sh.OnPreOutput = function(){}; // <--- Questa funzione bisogna crearla altrimenti non appare la progress bar
 sh.OnOutput = function(o,a){
	 window.open(ABSOLUTE_URL+"<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR'].'/'.$_OUTPUT_FILE; ?>");
	 var ret = new Array();
	 ret['action'] = "PRINT";
	 ret['type'] = "PDF";
	 ret['filename'] = "<?php echo $_OUTPUT_FILE; ?>";
	 ret['fullname'] = "<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR'].'/'.$_OUTPUT_FILE; ?>";
	 window.setTimeout(function(){gframe_close("The document has been printed",ret);},1000);
	}
 var cmd = "pdf export -format '"+MODEL_FORMAT+"' -orientation '"+MODEL_ORIENTATION+"' -o `<?php echo $_OUTPUT_FILE; ?>`"+(BACKGROUND_IMAGE ? " -background `"+BACKGROUND_IMAGE+"`" : "")+q;
 sh.sendCommand(cmd);
}

function showPage(li)
{
 var idx = li.getAttribute('frameid');
 var list = li.parentNode.getElementsByTagName('LI');
 for(var c=0; c < list.length; c++)
 {
  list[c].className = (list[c] == li) ? "selected" : "";
 }

 if(li.getAttribute('frameid') == "first")
  document.getElementById('sheetframe-first').style.display = "";
 else
  document.getElementById('sheetframe-first').style.display = "none";

 if(li.getAttribute('frameid') == "last")
  document.getElementById('sheetframe-last').style.display = "";
 else
  document.getElementById('sheetframe-last').style.display = "none";

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
 var frames = framelist.getElementsByTagName('IFRAME');

 var c = 1;
 while(frames.length > 1)
 {
  var fr = document.getElementById('sheetframe-'+c);
  if(fr)
   framelist.removeChild(fr);
  c++;
 }

 while(ul.getElementsByTagName('LI').length > 3)
  ul.removeChild(ul.getElementsByTagName('LI')[2]);

 var modelId = inp.id.substr(6);
 var hasFirstPage = (inp.getAttribute('hasfirstpage') == 'true') ? true : false;
 var hasLastPage = (inp.getAttribute('haslastpage') == 'true') ? true : false;

 if(hasFirstPage)
 {
  // Update first page
  var href = document.getElementById('sheetframe-first').src;
  if(href.indexOf("&modelid=") > 0)
   href = href.replace("&modelid="+MODEL_ID, "&modelid="+modelId);
  else
   href+= "&modelid="+modelId;

  document.getElementById('sheetframe-first').src = href;
  document.getElementById('sheetframe-first').style.display = "none";
  document.getElementById('firstpage_tabbtn').style.display = "";
  document.getElementById('firstpage_tabbtn').className = "";
 }
 else
 {
  document.getElementById('sheetframe-first').style.display = "none";
  document.getElementById('firstpage_tabbtn').style.display = "none";
 }

 // Update page
 href = document.getElementById('sheetframe-0').src;
 if(href.indexOf("&modelid=") > 0)
  href = href.replace("&modelid="+MODEL_ID, "&modelid="+modelId);
 else
  href+= "&modelid="+modelId;
 
 document.getElementById('sheetframe-0').src = href;
 document.getElementById('sheetframe-0').style.display = "";

 if(hasLastPage)
 {
  // Update last page
  href = document.getElementById('sheetframe-last').src;
  if(href.indexOf("&modelid=") > 0)
   href = href.replace("&modelid="+MODEL_ID, "&modelid="+modelId);
  else
   href+= "&modelid="+modelId;

  document.getElementById('sheetframe-last').src = href;
  document.getElementById('sheetframe-last').style.display = "none";
  document.getElementById('lastpage_tabbtn').style.display = "";
  document.getElementById('lastpage_tabbtn').className = "";
 }
 else
 {
  document.getElementById('lastpage_tabbtn').style.display = "none";
  document.getElementById('sheetframe-last').style.display = "none";
 }

 MODEL_ID = modelId;
 MODEL_FORMAT = inp.getAttribute('modelformat') ? inp.getAttribute('modelformat') : "A4";
 MODEL_ORIENTATION = inp.getAttribute('modelorientation') ? inp.getAttribute('modelorientation') : "P";

 document.getElementById('pagebuttons').getElementsByTagName('LI')[1].className = "selected";

 CAN_PRINT = false;
 PAGES = new Array();
 FIRSTPAGE = null;
 LASTPAGE = null;

 PREVSH.showProcessMessage("Generazione delle anteprime in corso", "Attendere prego, è in corso la generazione delle anteprime delle pagine.");
}

function exportToPDF()
{
 if(!CAN_PRINT)
  return alert("Generazione delle pagine di anteprima in corso... Attendere!");

 if("<?php echo $_SESSION['UNAME']; ?>" == "root")
  return alert("Attenzione!, l'utente root non può nè stampare nè generare PDF o inviarli via email. Devi uscire ed effettuare il login come utente normale");

 var q = "";
 if(PAGES.length)
 {
  for(var c=0; c < PAGES.length; c++)
  {
   PAGES[c].contents = PAGES[c].contents.replace("{PG_COUNT}",PAGES.length); // <--- sost. PG_COUNT con il nr. di pagine su ogni pagina
   PAGES[c].contents = PAGES[c].contents.replace("{DOC_PGC}",(c+1)+"/"+PAGES.length);
   q+= " -c `"+PAGES[c].contents+"`";
  }
 }

 var BACKGROUND_IMAGE = "";
 if(document.getElementById("model-"+MODEL_ID+"-bgimage"))
  BACKGROUND_IMAGE = document.getElementById("model-"+MODEL_ID+"-bgimage").src.replace(ABSOLUTE_URL,"");

 var sh = new GShell();
 sh.OnError = function(err){alert(err.striptags(true));}
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
 sh.sendCommand("pdf export -format '"+MODEL_FORMAT+"' -orientation '"+MODEL_ORIENTATION+"' -o `<?php echo $_OUTPUT_FILE; ?>`"+(BACKGROUND_IMAGE ? " -background `"+BACKGROUND_IMAGE+"`" : "")+q);
}

function sendEmail()
{
 if(!CAN_PRINT)
  return alert("Generazione delle pagine di anteprima in corso... Attendere!");

 if("<?php echo $_SESSION['UNAME']; ?>" == "root")
  return alert("Attenzione!, l'utente root non può nè stampare nè generare PDF o inviarli via email. Devi uscire ed effettuare il login come utente normale");

 var q = "";
 if(PAGES.length)
 {
  for(var c=0; c < PAGES.length; c++)
  {
   PAGES[c].contents = PAGES[c].contents.replace("{PG_COUNT}",PAGES.length); // <--- sost. PG_COUNT con il nr. di pagine su ogni pagina
   PAGES[c].contents = PAGES[c].contents.replace("{DOC_PGC}",(c+1)+"/"+PAGES.length);
   q+= " -c `"+PAGES[c].contents+"`";
  }
 }

 var sh = new GShell();
 sh.OnError = function(err){alert(err.striptags(true));}
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
	 sh2.sendCommand("gframe -f sendmail -params `recp="+RECP+"&subjid="+SUBJID+"&attachment=<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR'].'/'.$_OUTPUT_FILE; ?>`");
	}
 sh.sendCommand("pdf export -format '"+MODEL_FORMAT+"' -orientation '"+MODEL_ORIENTATION+"' -o `<?php echo $_OUTPUT_FILE; ?>`"+q);
}

function previewMessage(msg, info, completed)
{
 switch(msg)
 {
  case 'PAGEBREAK' : {
	 var list = document.getElementById('framelist').getElementsByTagName('IFRAME');
	 var iframe = document.createElement('IFRAME');
	 var frameIdx = list.length;
	 iframe.id = "sheetframe-"+frameIdx;
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
	 var lastLI = document.getElementById('lastpage_tabbtn');
	 var li = document.createElement('LI');
	 li.innerHTML = list.length;
	 li.setAttribute('frameid',frameIdx);
	 li.onclick = function(){showPage(this);}
	 ul.insertBefore(li, lastLI);
	} break;

  case 'PAGEINFO' : {
	 
	 PAGES.push(info);
	 if(completed)
	 {
	  CAN_PRINT = true;
	  PREVSH.hideProcessMessage();
	 }
	 else
	 {
	  CAN_PRINT = false;
	 }
	} break;

  case 'FIRSTPAGE' : {
	 FIRSTPAGE = info;
	} break;

  case 'LASTPAGE' : {
	 LASTPAGE = info;
	} break;
 }

}
</script>
</body></html>
<?php

