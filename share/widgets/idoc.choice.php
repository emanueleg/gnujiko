<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2013 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 13-02-2013
 #PACKAGE: idoc-config
 #DESCRIPTION: IDoc Choice form.
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO: 
 
*/

global $_BASE_PATH, $_ABSOLUTE_URL;
$_BASE_PATH = "../../";
define("VALID-GNUJIKO",1);

include_once($_BASE_PATH."include/gshell.php");

$_IDOC_AP = $_REQUEST['idocap'] ? $_REQUEST['idocap'] : "idoc";

$ret = GShell("dynarc archive-info -prefix `".$_IDOC_AP."`",$_REQUEST['sessid'],$_REQUEST['shellid']);
if(!$ret['error'])
 $_IDOC_ARCHIVE = $ret['outarr'];

if($_REQUEST['idocct'] || $_REQUEST['idoccat'])
{
 $ret = GShell("dynarc cat-info -ap `".$_IDOC_AP."`".($_REQUEST['idoccat'] ? " -id `".$_REQUEST['idocat']."`" : " -tag `".$_REQUEST['idocct']."`"), $_REQUEST['sessid'], $_REQUEST['shellid']);
 $_IDOC_CAT = $ret['outarr'];
}
else
 $_IDOC_CAT = "";

$_IDOC_ID = $_REQUEST['idocid'] ? $_REQUEST['idocid'] : 0;

$ret = GShell("dynarc item-list -ap `".$_IDOC_AP."`".($_IDOC_CAT ? " -cat `".$_IDOC_CAT['id']."`" : "")." -get thumbdata",$_REQUEST['sessid'],$_REQUEST['shellid']);
$IDOCList = $ret['outarr']['items'];

if(!count($IDOCList))
{
 $_IDOC_PARENT = $_IDOC_CAT;
 // get first sub-category //
 $ret = GShell("dynarc cat-list -ap idoc -parent `".$_IDOC_CAT['id']."` -limit 1",$_REQUEST['sessid'],$_REQUEST['shellid']);
 $list = $ret['outarr'];
 if(count($list))
 {
  $_IDOC_CAT = $list[0];
  $ret = GShell("dynarc item-list -ap `".$_IDOC_AP."` -cat `".$_IDOC_CAT['id']."` -get thumbdata",$_REQUEST['sessid'],$_REQUEST['shellid']);
  $IDOCList = $ret['outarr']['items'];
 }
}

if(!$_IDOC_ID)
 $_IDOC_ID = $IDOCList[0]['id'];


$iframeSRC = $_ABSOLUTE_URL."share/widgets/idoc/choice-preview.php?sessid=".$_REQUEST['sessid']."&shellid=".$_REQUEST['shellid']."&idocap="
	.$_IDOC_AP."&idoccat=".($_IDOC_CAT ? $_IDOC_CAT['id'] : "")."&idocid=".$_IDOC_ID;

?>
<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Gnujiko - IDoc preview</title>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>share/widgets/idoc/choice-preview.css" type="text/css" />
<?php
include_once($_BASE_PATH."include/js/gshell.php");
?>
</head><body>
<table class="previewform" width="550" height="442" cellspacing="0" cellpadding="0" border="0">
<tr><td class="title" height="40" colspan='2'>Seleziona un modello&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-size:12px;color:#000;">Categoria:</span><select style="width:180px;" onchange="categoryChange(this)">
	 <?php
	 if($_IDOC_PARENT)
	  echo "<option value='".$_IDOC_PARENT['id']."'>".$_IDOC_PARENT['name']."</option>";

	 if($_IDOC_PARENT)
	  $ret = GShell("dynarc cat-list -ap `".$_IDOC_AP."` -parent ".$_IDOC_PARENT['id'],$_REQUEST['sessid'], $_REQUEST['shellid']);
	 else
	  $ret = GShell("dynarc cat-list -ap `".$_IDOC_AP."`".($_IDOC_CAT ? " -parent ".$_IDOC_CAT['id'] : ""),$_REQUEST['sessid'], $_REQUEST['shellid']);
	 $list = $ret['outarr'];
	 for($c=0; $c < count($list); $c++)
	 {
	  echo "<option value='".$list[$c]['id']."'".(($list[$c]['id'] == $_IDOC_CAT['id']) ? " selected='selected'>" : ">").$list[$c]['name']."</option>";
	 }
	 ?>
	 <option value='0' style='color:#333333;'>altro...</option>
		</select></td>
	<td width="50"><a href='#' class="closebtn" onclick="gframe_close()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/idoc/img/close.png" border="0" title="Chiudi"/></a></td></tr>

<tr><td valign="top" width='370' height='358'>
	 <div class="sheet-background"><div style="width:330px;height:330px;overflow:hidden;">
	  <iframe id="sheetframe" src="<?php echo $iframeSRC; ?>" style="width:744px;height:1052px;"></iframe>
	 </div></div>
	</td>
	<td valign="top" colspan='2' rowspan='2'><br/>
	<div class="available-models"><i>Modelli disponibili</i></div>
	<div class="model-list" id='modellist'>
	<?php
	$list = $IDOCList;
	for($c=0; $c < count($list); $c++)
	{
	 echo "<div class='thumbnail'><img src='".($list[$c]['thumbdata'] ? $list[$c]['thumbdata'] : $_ABSOLUTE_URL."share/widgets/idoc/img/no-thumb.png")."' width='96'/></div>";
	 echo "<input id='model-".$list[$c]['id']."' type='radio' name='model' onclick='changeModel(this)' class='model'".($_IDOC_ID == $list[$c]['id'] ? " checked='true'" : "")."/><span id='model-name-".$list[$c]['id']."'>".$list[$c]['name']."</span><br/>";
	}
	?>
	</div>
	</td></tr>

<tr><td><ul class='basicbuttons' style="float:left;margin-left:10px;">
	<li><a href='#' onclick="submit()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/idoc/img/choice_select.gif" border='0'/>Seleziona</a></li>
	<li><a href='#' onclick="gframe_close()"><img src="<?php echo $_ABSOLUTE_URL; ?>share/widgets/idoc/img/exit.png" border='0'/>Annulla</a></li>
	</ul></td></tr>

</table>
<script>
var MODEL_ID = <?php echo $_IDOC_ID ? $_IDOC_ID : "''"; ?>;
var MODEL_NAME = "<?php echo $_IDOC_INFO ? $_IDOC_INFO['name'] : ""; ?>";
var MODEL_AID = <?php echo $_IDOC_ARCHIVE ? $_IDOC_ARCHIVE['id'] : "0"; ?>;
var MODEL_THUMB = "";

function bodyOnLoad()
{

}

function cachecontentsload(contents)
{
}

function submit()
{
 var ret = new Array();
 ret['id'] = MODEL_ID;
 ret['aid'] = MODEL_AID;
 ret['name'] = MODEL_NAME;
 ret['thumbdata'] = MODEL_THUMB;
 gframe_close("You have choose the model #"+MODEL_ID, ret);
}

function changeModel(inp)
{
 var modelId = inp.id.substr(6);
 var href = document.getElementById('sheetframe').src;
 if(href.indexOf("&idocid=") > 0)
  href = href.replace("&idocid="+MODEL_ID, "&idocid="+modelId);
 else
  href+= "&idocid="+modelId;
 
 MODEL_ID = modelId;
 MODEL_NAME = document.getElementById('model-name-'+modelId).innerHTML;
 MODEL_THUMB = inp.previousSibling.getElementsByTagName('IMG')[0].src;

 document.getElementById('sheetframe').src = href;
}

function categoryChange(sel)
{
 document.getElementById('modellist').innerHTML = "";
 var sh = new GShell();
 sh.OnOutput = function(o,a){
	 if(!a || !a['items']) return;
	 var html = "";
	 for(var c=0; c < a['items'].length; c++)
	 {
	  html+= "<div class='thumbnail'><img src='"+(a['items'][c]['thumbdata'] ? a['items'][c]['thumbdata'] : ABSOLUTE_URL+"share/widgets/idoc/img/no-thumb.png")+"' width='96'/ ></div>";
	  html+= "<input id='model-"+a['items'][c]['id']+"' type='radio' name='model' onclick='changeModel(this)' class='model'/ ><span id='model-name-"+a['items'][c]['id']+"'>"+a['items'][c]['name']+"</span><br/ >";
	 }
	 document.getElementById('modellist').innerHTML = html;
	}
 sh.sendCommand("dynarc item-list -ap `<?php echo $_IDOC_AP; ?>` -get thumbdata"+((sel.value != "0") ? " -cat "+sel.value : ""));
}
</script>
</body></html>
<?php

