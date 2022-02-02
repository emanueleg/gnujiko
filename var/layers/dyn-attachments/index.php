<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 20-11-2012
 #PACKAGE: dynarc-attachments-extension
 #DESCRIPTION: Attachments support for categories and items into archives managed by Dynarc.
 #VERSION: 2.0beta
 #CHANGELOG:
 #TODO:
 
*/
global $_BASE_PATH, $_ABSOLUTE_URL, $_SHELL_CMD_PATH, $_USERS_HOMES;

$_BASE_PATH = "../../../";

define("VALID-GNUJIKO",1);
include_once($_BASE_PATH."include/gshell.php");

include_once($_BASE_PATH."var/objects/guploader/index.php");
include_once($_BASE_PATH."var/objects/dynserppagenav/index.php");

?>
<script type="text/javascript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/layers.js"></script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/xrequest.js" type="text/javascript"></script>
<script language="JavaScript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/gshell.js" type="text/javascript"></script>

<script type="text/javascript" src="<?php echo $_ABSOLUTE_URL; ?>include/js/extendedfunc.js"></script>
<link rel="stylesheet" href="<?php echo $_ABSOLUTE_URL; ?>var/layers/dyn-attachments/css/common.css" type="text/css" />
<div style='font-family:Arial;font-size:18px;color:#000000;margin-bottom:3px;'><b>Lista degli allegati</b></div>
<div style='font-family:Arial;font-size:12px;color:#333333;'>Inserisci degli allegati su questa annotazione.<br/>Puoi caricare file immagine (.png, .gif, .jpg), oppure dei PDF, fogli di calcolo, ecc.</div>

<table border='0' style='margin-top:12px;'>
<tr><td height='40' style='font-size:12px;font-family:Arial;color:#333333;' valign='middle' width='4%' nowrap>Carica un file:</td><td><div id='gupldspace'></div></td></tr>
<tr><td colspan='2' style='font-size:12px;font-family:Arial;color:#333333;'>oppure seleziona il file direttamente dal server: <input type='button' value='Apri...' onclick='_selectFromServer("<?php echo $_USERS_HOMES.$_SESSION['HOMEDIR']."/"; ?>")'/></td></tr>
<tr><td colspan='2' style='font-size:12px;font-family:Arial;color:#333333;'>oppure inserisci link da URL: <input type='text' id='attachurl'/> <input type='button' value='Inserisci' onclick='insertFromURL()'/></td></tr>
</table>

<?php
if($_REQUEST['catid'])
 $query = "dynattachments list -ap '".$_REQUEST['archiveprefix']."' -cat ".$_REQUEST['catid'];
else if($_REQUEST['id'])
 $query = "dynattachments list -ap '".$_REQUEST['archiveprefix']."' -refid ".$_REQUEST['id'];

if($query)
{
 $ret = GShell($query);
 if(!$ret['error'])
  $list = $ret['outarr'];
}

if($_REQUEST['tableheight'])
 echo "<div style='height:".$_REQUEST['tableheight'].";overflow:auto;'>";

?>
<table width='100%' id='attachmentstable' cellspacing='0' cellpadding='0' border='0' style='margin-top:24px;'>
<tr><th align='left'>&nbsp;&nbsp;Nome allegato</th><th>&nbsp;</th><th>Dimensione</th><th>Data</th><th>&nbsp;</th></tr>
<?php
for($c=0; $c < count($list['items']); $c++)
{
 $item = $list['items'][$c];
 echo "<tr id='".$item['id']."'><td>";
 // show mimetype icon //
 if($item['icons'])
 {
  if($item['icons']['size22x22'])
   echo "<img src='".$_ABSOLUTE_URL.$item['icons']['size22x22']."' style='margin-top:2px;' valign='top' align='left'/>";
 }
 else
  echo "<img src='".$_ABSOLUTE_URL."share/mimetypes/22x22/file.png' style='margin-top:2px;' valign='top' align='left'/>";
 echo "&nbsp;<a href='".($item['type'] != "WEB" ? $_ABSOLUTE_URL : "").$item['url']."' target='blank' style='line-height:1.5em;'>".$item['name']."</a></td>";
 if($item['type'] == "AUDIO")
 {
  ?><td>
	<object type="application/x-shockwave-flash" data="<?php echo $_ABSOLUTE_URL; ?>var/layers/dyn-attachments/players/player_mp3_maxi.swf" width="200" height="20">
    <param name="movie" value="<?php echo $_ABSOLUTE_URL; ?>var/layers/dyn-attachments/players/player_mp3_maxi.swf" />
    <param name="bgcolor" value="#ffffff" />
    <param name="FlashVars" value="mp3=<?php echo ($item['type'] != 'WEB' ? $_ABSOLUTE_URL : '').$item['url']; ?>&amp;showstop=1&amp;showinfo=1&amp;showvolume=1&amp;loadingcolor=5cfff5&amp;bgcolor1=cccccc&amp;bgcolor2=858585" />
	</object>
  </td><?php
 }
 else
  echo "<td>&nbsp;</td>";
 echo "<td align='center'>".($item['humansize'] ? $item['humansize'] : "&nbsp;")."</td>";
 echo "<td align='center'>".date('d-M-Y',strtotime($item['ctime']))."</td>";
 echo "<td align='center'><img src='".$_ABSOLUTE_URL."var/layers/dyn-attachments/img/edit.gif' style='cursor:pointer;' onclick='editAttachment("
	.$item['id'].")'/>&nbsp;<img src='".$_ABSOLUTE_URL."var/layers/dyn-attachments/img/delete.gif' style='cursor:pointer;' onclick='deleteAttachment("
	.$item['id'].")'/></td></tr>";
}
if(!count($list['items']))
 $rFrom = 0;
?>
</table>
<br/>
<div class='serppagenav' align='center'><?php
	$spn = new DynSerpPageNav($rTot,$start);
	$spn->Paint();
	?></div>
<br/>
<?php
if(!count($list['items']))
{
 echo "<h4 align='center' style='font-family:Arial;color:#333333;'>Non ci sono allegati da visualizzare</h4><br/><br/>";
}
?>
<table width='100%' id='attachmentstablefooter' cellspacing='0' cellpadding='0' border='0'>
<tr><td>&nbsp;<?php /*<img src="<?php echo $_ABSOLUTE_URL; ?>var/layers/dyn-attachments/img/download.gif" align="left" valign="top"/> <a href='#' onclick='downloadAllAttachments()' style="font-size:13px;font-family:Arial;">Scarica tutti gli allegati</a>*/ ?></td>
	<?php
	if(!$list['count'])
	 echo "<td align='right' style='padding-right: 12px;font-size:12px;'><b><i>Nessun risultato</i></b></td></tr>";
	else
	 echo "<td align='right' style='padding-right: 12px;font-size:12px;'>Totale allegati: <b>".$list['count']."</b></td></tr>";
	?>
</table>
<?php
if($_REQUEST['tableheight'])
 echo "</div>";
?>
<script>
var CAT_ID = <?php echo $_REQUEST['catid'] ? $_REQUEST['catid'] : 0; ?>;
var ARCHIVE_PREFIX = "<?php echo $_REQUEST['archiveprefix']; ?>";
var ID = <?php echo $_REQUEST['id'] ? $_REQUEST['id'] : 0; ?>; 
</script>
<script language='JavaScript' src="<?php echo $_ABSOLUTE_URL; ?>var/layers/dyn-attachments/js/common.js" type='text/javascript'></script>
<?php

