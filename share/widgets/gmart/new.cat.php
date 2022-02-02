<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-01-2013
 #PACKAGE: gmart
 #DESCRIPTION: New category form.
 #VERSION: 2.1beta
 #CHANGELOG: 13-01-2013 : Bug fix.
 #DEPENDS: 
 #TODO:
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;
$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$_AP = $_REQUEST['ap'] ? $_REQUEST['ap'] : "gmart";
$_PARENT = $_REQUEST['cat'] ? $_REQUEST['cat'] : 0;

if($_PARENT)
{
 $ret = GShell("dynarc cat-info -ap `".$_AP."` -id `".$_PARENT."` --include-path",$_REQUEST['sessid'],$_REQUEST['shellid']);
 $parentInfo = $ret['outarr'];
 $pathway = "";
 for($c=0; $c < count($parentInfo['pathway']); $c++)
  $pathway.= $parentInfo['pathway'][$c]['name']."/";
}

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>New Category</title>
<script>var BASE_PATH = "<?php echo $_BASE_PATH; ?>"; var ABSOLUTE_URL = "<?php echo $_ABSOLUTE_URL; ?>";</script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/new-cat.css" type="text/css" />
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
</head><body>
<div class="new-category-form">
 <div class="title">Nuova categoria</div>
 <div class="section">
  <span class="black"><b>SOTTOCATEGORIA: </b></span>
  <span class="blue"><b><?php echo $pathway; ?></b></span>
  <input type='hidden' name='parentid' id='parentid' value="<?php echo $parentInfo ? $parentInfo['id'] : '0'; ?>"/>
 </div>
 <div class="section" style="height:96px;overflow:auto;" id="titles">
  <div><span class="blue"><b>TITOLO: </b></span> <input type="text" id="title-0" class="text" value="" style="margin-left:15px;"/></div>
  <span class='smallgreen' style='margin-left:60px;' onclick="addTitle(this)">aggiungi un'altra</span>
 </div>

 <ul class='basicbuttons' style="margin-left:4px;margin-top:2px;float:left;">
  <li><a href='#' onclick='submit()'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/save.gif" border='0'/>Salva e torna indietro</a></li>
  <li id='submitandenter'><a href='#' onclick='submit(true)'><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/save.gif" border='0'/>Salva ed entra</a></li>
 </ul>

 <ul class='basicbuttons' style="float:right;margin-top:2px;margin-right:26px;">
  <li><a href='#' onclick="gframe_close()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/gmart/img/exit.png" border='0'/>Annulla</a></li>
 </ul>

</div>

<script>
var RETURNS = new Array();

function bodyOnLoad()
{
 document.getElementById('title-0').focus();
}

function addTitle(el)
{
 var divP = document.getElementById('titles');
 var num = divP.getElementsByTagName('DIV').length+1;
 var div = document.createElement('DIV');
 div.innerHTML = "<span class='blue'><b>TITOLO #"+num+": </b></span> <input type='text' id='title-"+(num-1)+"' class='text' value=''/ >";
 divP.insertBefore(div,el);
 document.getElementById('title-'+(num-1)).focus();
 document.getElementById('submitandenter').style.display='none';
}

function submit(enter)
{
 var parentId = document.getElementById('parentid').value;
 var titles = new Array();
 var divP = document.getElementById('titles');
 var list = divP.getElementsByTagName('DIV');
 for(var c=0; c < list.length; c++)
 {
  var inp = list[c].getElementsByTagName('INPUT')[0];
  if(inp.value)
   titles.push(inp.value);
 }

 if(!titles.length)
 {
  alert("Devi inserire un titolo valido");
  document.getElementById('title-0').focus();
  return;
 }

 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(a)
	  RETURNS.push(a);
	}
 sh.OnFinish = function(o,a){
	 if(a)
	  RETURNS.push(a);
	 if(enter && (RETURNS.length == 1))
	  gframe_close("ENTER",RETURNS);
	 else
	  gframe_close("RETURN",RETURNS);
	}

 for(var c=0; c < titles.length; c++)
  sh.sendCommand("dynarc new-cat -ap `<?php echo $_AP; ?>`"+(parentId ? " -parent `"+parentId+"`" : "")+" -name `"+titles[c]+"` -group gmart");
}

</script>

</body></html>

